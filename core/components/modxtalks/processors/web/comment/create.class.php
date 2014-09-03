<?php

require_once dirname(dirname(dirname(__FILE__))) . '/modxtalksprocessor.trait.php';

/**
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksPostCreateProcessor extends modObjectCreateProcessor
{
	use modxTalksProcessorTrait;

	public $classKey = 'modxTalksPost';
	public $languageTopics = ['modxtalks:default'];
	public $objectType = 'modxtalks.post';
	public $afterSaveEvent = 'OnModxTalksCommentAfterAdd';
	public $beforeSaveEvent = 'OnModxTalksCommentBeforeAdd';
	protected $context;
	protected $preModarateComments;
	protected $preview;
	protected $timeout = 60;
	protected $hash = '';
	/**
	 * @var modxTalksConversation
	 */
	protected $conversation;

	/**
	 * Process the Comment create processor
	 * {@inheritDoc}
	 * @return mixed
	 */
	public function process()
	{
		/* Run the beforeSet method before setting the fields, and allow stoppage */
		$canSave = $this->beforeSet();
		if ($canSave !== true)
		{
			return $this->failure($canSave);
		}

		$this->object->fromArray($this->getProperties());

		$this->object->set('name', $this->name);
		$this->object->set('email', $this->email);
		$this->object->set('link', $this->app()->getLink($this->object->idx));
		$this->object->set('processed_content', $this->app()->bbcode($this->object->content));

		/* if Comment premodarate return custom message before save comment */
		if ($this->preModarateComments && ! $this->preview && ! $this->app()->isModerator())
		{
			$data = [
				'success' => false,
				'message' => $this->modx->lexicon('modxtalks.comment_premoderate'),
				'premoderated' => true,
			];

			return $this->modx->toJSON($data);
		}
		else if ($this->preview)
		{
			/* if Comment preview return custom message before save comment */
			$data = $this->_preparePostData();

			return $this->success($data);
		}

		/* run object validation */
		if ( ! $this->object->validate())
		{
			/** @var modValidator $validator */
			$validator = $this->object->getValidator();
			if ($validator->hasMessages())
			{
				foreach ($validator->getMessages() as $message)
				{
					$this->addFieldError($message['field'], $this->modx->lexicon($message['message']));
				}
			}
		}

		$preventSave = $this->fireBeforeSaveEvent();
		if ( ! empty($preventSave))
		{
			return $this->failure($preventSave);
		}

		/* save element */
		if ( ! $this->object->save())
		{
			$this->modx->error->checkValidation($this->object);

			return $this->failure($this->modx->lexicon($this->objectType . '_err_save'));
		}

		$this->afterSave();
		$this->fireAfterSaveEvent();
		$this->logManagerAction();

		return $this->cleanup();
	}

	/**
	 * Override in your derivative class to do functionality before the fields are set on the object
	 * @return bool
	 */
	public function beforeSet()
	{
		$this->preModarateComments = (bool) $this->modx->getOption('modxtalks.preModarateComments', null, false);
		$idx = 0;
		$time = time();
		$this->userId = 0;
		$this->ip = $this->app()->get_client_ip();
		$this->email = trim($this->getProperty('email'));
		$this->name = trim($this->getProperty('name'));
		$conversationId = 0;
		$conversation = trim($this->getProperty('conversation'));
		$content = trim($this->getProperty('content'));
		// $this->context = trim($this->getProperty('ctx'));
		$this->preview = $this->getProperty('preview');
		$this->timeout = $this->app()->config['add_timeout'];
		if ($slug = $this->getProperty('slug'))
		{
			$this->app()->config['slug'] = $slug;
		}

		/**
		 * Check Context
		 */
		$this->context = $this->app()->getContext();
		/**
		 * Check Conversation name
		 */
		if (empty($conversation))
		{
			$this->addFieldError('conversation', $this->modx->lexicon('modxtalks.id_not_defined'));
		}
		elseif ( ! is_string($conversation))
		{
			$this->addFieldError('conversation', $this->modx->lexicon('modxtalks.bad_id'));
		}
		elseif (preg_match('@[^a-zA-z-_.0-9]@i', $conversation))
		{
			$this->addFieldError('conversation', $this->modx->lexicon('modxtalks.unallowed_symbols'));
		}
		elseif (strlen($conversation) < 2 || strlen($conversation) > 63)
		{
			$this->addFieldError('conversation', $this->modx->lexicon('modxtalks.bad_id'));
		}
		elseif ( ! $this->conversation = $this->modx->getObject('modxTalksConversation', ['conversation' => $conversation]))
		{
			$this->failure($this->modx->lexicon('modxtalks.empty_conversation'));

			return false;
		}

		/**
		 * Check Comment Content
		 */
		if (empty($content))
		{
			$this->addFieldError('content', $this->modx->lexicon('modxtalks.empty_content'));
		}
		elseif ( ! is_string($content))
		{
			$this->addFieldError('content', $this->modx->lexicon('modxtalks.bad_content'));
		}
		elseif (mb_strlen($content, 'UTF-8') < 2)
		{
			$this->addFieldError('content', $this->modx->lexicon('modxtalks.bad_content_length', ['length' => 2]));
		}

		$_SESSION['comment_time'] = ! empty($_SESSION['comment_time']) ? $_SESSION['comment_time'] : 0;

		/**
		 * Check user Email
		 */
		if ($this->modx->user->isAuthenticated($this->context) || $this->app()->isModerator())
		{
			$this->userId = $this->modx->user->get('id');
			$this->email = $this->modx->user->Profile->email;
			if ( ! $this->name = $this->modx->user->Profile->fullname)
			{
				$this->name = $this->modx->user->username;
			}
		}
		else
		{
			if (empty($this->email))
			{
				$this->addFieldError('email', $this->modx->lexicon('modxtalks.empty_email'));
			}
			elseif ( ! is_string($this->email) || ! $this->object->validateEmail($this->email))
			{
				$this->addFieldError('email', $this->modx->lexicon('modxtalks.bad_email'));
			}
			if ( ! $this->hasErrors() && $this->modx->getCount('modUserProfile', ['email' => $this->email]))
			{
				$this->failure($this->modx->lexicon('modxtalks.user_exists'));

				return false;
			}
			/**
			 * Check user name
			 */
			if (empty($this->name))
			{
				$this->addFieldError('name', $this->modx->lexicon('modxtalks.empty_name'));
			}
			elseif (mb_strlen($this->name, 'UTF-8') < 2)
			{
				$this->addFieldError('name', $this->modx->lexicon('modxtalks.bad_name_length', ['length' => 2]));
			}
		}

		/**
		 * Check if user email is banned
		 */
		if ($this->modx->getCount('modxTalksEmailBlock', ['email' => $this->email]))
		{
			$this->failure($this->modx->lexicon('modxtalks.email_banned'));

			return false;
		}

		if ( ! $this->hasErrors() && ! $this->preview)
		{
			$conversationId = $this->conversation->get('id');

			$this->hash = md5($content . $this->email . $conversationId);

			/**
			 * Premoderate comment
			 */
			if ($this->preModarateComments === true && ! $this->app()->isModerator())
			{
				// Check time before for add another comment
				if ((time() - $_SESSION['comment_time']) < $this->timeout)
				{
					$seconds = $this->timeout - (time() - $_SESSION['comment_time']);
					$this->failure($this->modx->lexicon('modxtalks.add_comment_waiting', ['seconds' => $seconds]));

					return false;
				}
				if ( ! $this->hasErrors())
				{
					$this->conversation->unconfirmed += 1;
					$this->conversation->save();

					$params = [
						'conversationId' => $conversationId,
						'hash' => $this->hash,
						'time' => $time,
						'content' => $content,
						'ip' => $this->ip,
					];
					if ($this->modx->user->isAuthenticated($this->context))
					{
						$params['userId'] = $this->userId;
					}
					else
					{
						$params['useremail'] = $this->email;
						$params['username'] = $this->name;
					}
					$comment = $this->modx->newObject('modxTalksTempPost', $params);
					if ($comment->save() === false)
					{
						$this->failure($this->modx->lexicon('modxtalks.error_try_again'));

						return false;
					}

					/**
					 * Send Notify to conversation moderators
					 */
					if ( ! $this->app()->notifyModerators($comment))
					{
						$this->failure($this->modx->lexicon('modxtalks.error_try_again'));

						return false;
					}

					$_SESSION['comment_time'] = time();
				}

				return parent::beforeSet();
			}

			$q = $this->modx->newQuery($this->classKey);
			$q->where(['conversationId' => $conversationId]);
			$q->sortby('idx', 'DESC');
			$q->limit(1);
			$idx = 1;
			if ($lastComment = $this->modx->getObject($this->classKey, $q))
			{
				$idx = $lastComment->idx + 1;
			}

			/**
			 * Check for comment double
			 */
			if ((time() - $_SESSION['comment_time']) < $this->timeout && ! $this->app()->isModerator())
			{
				$post = $this->modx->getObject($this->classKey, ['hash' => $this->hash, 'conversationId' => $conversationId, 'time' => $_SESSION['comment_time']]);
				$seconds = $this->timeout - (time() - $_SESSION['comment_time']);
				if ($post && $seconds !== 0)
				{
					$this->failure($this->modx->lexicon('modxtalks.resend_comment_waiting', ['seconds' => $seconds]));

					return false;
				}
				else
				{
					$this->failure($this->modx->lexicon('modxtalks.add_comment_waiting', ['seconds' => $seconds]));

					return false;
				}
			}
			$this->conversation->total += 1;
			$this->conversation->save();
		}

		$this->properties = [
			'idx' => $idx,
			'conversationId' => $conversationId,
			'userId' => $this->userId,
			'time' => $time,
			'date' => strftime('%Y%m', $time),
			'hash' => $this->hash,
			'content' => $content,
			'username' => null,
			'useremail' => null,
			'ip' => $this->ip,
		];
		if ($this->userId === 0)
		{
			$this->properties['username'] = $this->name;
			$this->properties['useremail'] = $this->email;
		}

		return parent::beforeSet();
	}

	public function afterSave()
	{
		$_SESSION['comment_time'] = time();

		/**
		 * Refresh comment and conversation cache
		 */
		if ($this->app()->mtCache === true)
		{
			if ( ! $this->app()->cacheComment($this->object))
			{
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks web/comment/create] Error cache the comment with ID ' . $this->object->id);
			}
			if ( ! $this->app()->cacheConversation($this->conversation))
			{
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks web/comment/create] Error cache the conversation with ID ' . $this->conversation->id);
			}
		}

		/**
		 * Send Notify to conversation moderators
		 */
		$checkExec = explode(' ', ini_get('disable_functions'));
		$checkExec = array_map('trim', $checkExec);
		$success = false;
		if ( ! in_array('exec', $checkExec) && $this->app()->sendMail($this->object->id))
		{
			$success = true;
		}
		elseif ($this->app()->notifyModerators($this->object))
		{
			$success = true;
		}

		if ( ! $success)
		{
			$this->failure($this->modx->lexicon('modxtalks.error_try_again'));

			return false;
		}

		return parent::afterSave();
	}

	/**
	 * Override cleanup to send only back needed params
	 * @return array
	 */
	public function cleanup()
	{
		$data = $this->_preparePostData();

		return $this->success('', $data);
	}

	/**
	 * Override cleanup to send only back needed params
	 * @return array $data
	 */
	private function _preparePostData()
	{
		if ($this->preview)
		{
			return ['content' => $this->object->processed_content];
		}

		$data = [
			'avatar' => $this->app()->getAvatar($this->email),
			'hideAvatar' => '',
			'name' => $this->name,
			'content' => $this->object->processed_content,
			'index' => date('Ym', $this->object->time),
			'date' => date($this->app()->config['dateFormat'], $this->object->time),
			'funny_date' => $this->modx->lexicon('modxtalks.date_now'),
			'link' => $this->object->link,
			'id' => (int) $this->object->id,
			'idx' => (int) $this->object->idx,
			'user' => $this->app()->userButtons($this->userId, $this->object->time),
			'userId' => md5($this->userId . $this->email),
			'timeago' => date('c', $this->object->time),
			'timeMarker' => '',
			'funny_edit_date' => '',
			'edit_name' => '',
			'user_info' => '',
			'like_block' => '',
		];
		if ($this->app()->isModerator())
		{
			$data['user_info'] = $this->app()->_parseTpl($this->app()->config['user_info'], ['email' => $this->email, 'ip' => $this->object->ip], true);
		}

		return $data;
	}

}

return 'modxTalksPostCreateProcessor';
