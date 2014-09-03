<?php

require_once dirname(dirname(dirname(__FILE__))) . '/modxtalksprocessor.trait.php';

/**
 * @package post
 * @subpackage processors
 */
class commentRemoveProcessor extends modObjectUpdateProcessor
{
	use modxTalksProcessorTrait;

	public $classKey = 'modxTalksPost';
	public $languageTopics = ['modxtalks:default'];
	public $objectType = 'modxtalks.post';
	public $afterSaveEvent = 'OnModxTalksCommentAfterRemove';
	public $beforeSaveEvent = 'OnModxTalksCommentBeforeRemove';
	public $context = '';
	/**
	 * @var modxTalksConversation
	 */
	protected $conversation;

	public function initialize()
	{
		/**
		 * Check context
		 */
		$this->context = trim($this->getProperty('ctx'));
		if (empty($this->context))
		{
			$this->failure($this->modx->lexicon('modxtalks.empty_context'));

			return false;
		}
		else if ( ! $this->modx->getCount('modContext', $this->context))
		{
			$this->failure($this->modx->lexicon('modxtalks.bad_context'));

			return false;
		}

		if ($slug = (string) $this->getProperty('slug'))
		{
			$this->app()->config['slug'] = $slug;
		}

		return parent::initialize();
	}

	public function beforeSet()
	{
		$this->conversation = $this->modx->getObject('modxTalksConversation', [
			'id' => $this->object->conversationId
		]);

		if ( ! $this->conversation)
		{
			return false;
		}

		if ($this->object->deleteTime > 0 || $this->object->deleteUserId)
		{
			$this->failure($this->modx->lexicon('modxtalks.already_deleted'));

			return false;
		}

		/**
		 * Set comments data
		 */
		$this->properties = [
			'deleteTime' => time(),
			'deleteUserId' => $this->modx->user->id,
		];

		/**
		 * If users is moderator
		 */
		if ($this->app()->isModerator())
		{
			return parent::beforeSet();
		}

		/**
		 * Check user permission to delete comment
		 */
		$userId = $this->object->userId;
		if ( ! $this->modx->user->isAuthenticated($this->context))
		{
			$this->failure($this->modx->lexicon('modxtalks.delete_permission'));

			return false;
		}
		// Check comment owner
		if ($this->modx->user->id != $userId)
		{
			$this->failure($this->modx->lexicon('modxtalks.delete_permission'));

			return false;
		}
		// Check time for delete comment
		if ((time() - $this->object->time) > $this->app()->config['edit_time'])
		{
			$this->failure($this->modx->lexicon('modxtalks.delete_timeout'));

			return false;
		}

		return parent::beforeSet();
	}

	public function beforeSave()
	{
		if ( ! $this->object->deleteUserId || ! $this->object->deleteTime)
		{
			$this->failure($this->modx->lexicon('modxtalks.delete_error'));

			return false;
		}

		$this->conversation->deleted += 1;
		$this->conversation->save();

		return parent::beforeSave();
	}

	/**
	 * After save
	 * Add comment to cache
	 *
	 * @return void
	 **/
	public function afterSave()
	{
		if ($this->app()->mtCache === true)
		{
			if ( ! $this->app()->cacheComment($this->object))
			{
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks web/comment/remove] Error cache the comment with ID ' . $this->object->id);
			}

			if ( ! $this->app()->cacheConversation($this->conversation))
			{
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks web/comment/remove] Error cache the conversation with ID ' . $this->conversation->id);
			}
		}

		return parent::afterSave();
	}

	public function cleanup()
	{
		$data = $this->_prepareData();

		return $this->success($this->modx->lexicon('modxtalks.successfully_deleted'), $data);
	}

	private function _prepareData()
	{
		$name = $this->modx->lexicon('modxtalks.guest');
		$email = 'anonym@anonym.com';

		if ( ! $deleteUser = $this->modx->user->Profile->fullname)
		{
			$deleteUser = $this->modx->user->username;
		}

		if ($this->object->userId == $this->object->deleteUserId)
		{
			$name = $deleteUser;
			$email = $this->modx->user->Profile->email;
		}
		elseif ( ! $this->object->userId)
		{
			$name = $this->object->username;
			$email = $this->object->useremail;
		}
		else
		{
			if ($user = $this->modx->getObject('modUser', $this->object->userId))
			{
				$profile = $user->getOne('Profile');
				$email = $profile->email;
				if ( ! $name = $profile->fullname)
				{
					$name = $user->username;
				}
			}
		}

		$restore = $this->modx->lexicon('modxtalks.restore');
		$idx = (int) $this->object->idx;
		$data = [
			'deleteUser' => $deleteUser,
			'delete_date' => date($this->app()->config['dateFormat'] . ' O', $this->object->deleteTime),
			'deleted_by' => $this->modx->lexicon('modxtalks.deleted_by'),
			'funny_delete_date' => $this->modx->lexicon('modxtalks.date_now'),
			'name' => $name,
			'index' => date('Ym', $this->object->time),
			'date' => date($this->app()->config['dateFormat'] . ' O', $this->object->time),
			'funny_date' => $this->app()->date_format($this->object->time),
			'id' => (int) $this->object->id,
			'idx' => (int) $idx,
			'userId' => md5($this->object->userId . $email),
			'restore' => '<a href="' . $this->app()->getLink('restore-' . $idx) . '" title="' . $restore . '" class="mt_control-restore">' . $restore . '</a>',
			'timeago' => date('c', $this->object->time),
			'link' => $this->app()->getLink($idx),
		];

		return $data;
	}

	public function process()
	{
		/* Run the beforeSet method before setting the fields, and allow stoppage */
		$canSave = $this->beforeSet();
		if ($canSave !== true)
		{
			return $this->failure($canSave);
		}

		if ($this->app()->config['fullDeleteComment'] === true)
		{
			if ($this->conversation->total)
			{
				$this->conversation->total -= 1;
			}

			$this->conversation->save();

			if ( ! $this->app()->cacheConversation($this->conversation))
			{
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks web/comment/remove] Error cache the conversation with ID ' . $this->conversation->id);
			}

			$idx = $this->object->idx;

			if ( ! $this->object->remove())
			{
				return $this->failure('Error while deleting the post');
			}

			$this->app()->deleteAllCommentsCache($this->conversation->id);

			$recalculated = $this->conversation->recalculateIndexes($idx);
			if ($recalculated !== true)
			{
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks web/comment/remove] Error recalculate comments indexes ' . $this->conversation->id);
			}

			return $this->success($this->modx->lexicon('modxtalks.successfully_deleted'));
		}

		$this->object->fromArray($this->getProperties());

		/* Run the beforeSave method and allow stoppage */
		$canSave = $this->beforeSave();
		if ($canSave !== true)
		{
			return $this->failure($canSave);
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

		/* run the before save event and allow stoppage */
		$preventSave = $this->fireBeforeSaveEvent();
		if ( ! empty($preventSave))
		{
			return $this->failure($preventSave);
		}

		if ($this->saveObject() == false)
		{
			return $this->failure($this->modx->lexicon($this->objectType . '_err_save'));
		}
		$this->afterSave();
		$this->fireAfterSaveEvent();
		$this->logManagerAction();

		return $this->cleanup();
	}
}

return 'commentRemoveProcessor';
