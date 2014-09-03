<?php

require_once dirname(dirname(dirname(__FILE__))) . '/modxtalksprocessor.trait.php';

/**
 * @package comment
 * @subpackage processors
 */
class commentUpdateProcessor extends modObjectUpdateProcessor
{
	use modxTalksProcessorTrait;

	public $classKey = 'modxTalksPost';
	public $languageTopics = ['modxtalks:default'];
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
			$this->failure($this->app()->lang('empty_context'));

			return false;
		}
		elseif ( ! $this->modx->getCount('modContext', $this->context))
		{
			$this->failure($this->app()->lang('bad_context'));

			return false;
		}

		return parent::initialize();
	}

	public function beforeSet()
	{
		if ($slug = $this->getProperty('slug'))
		{
			$this->app()->config['slug'] = $slug;
		}
		/**
		 * Check user permission to restore comment
		 */
		if ( ! $this->app()->isModerator())
		{
			$this->failure($this->modx->lexicon('access_denied'));

			return false;
		}

		if ( ! $this->object->deleteTime)
		{
			$this->failure($this->app()->lang('not_deleted'));

			return false;
		}

		$this->properties = [
			'deleteTime' => null,
			'deleteUserId' => null,
			'editTime' => time(),
			'editUserId' => $this->modx->user->id,
		];

		return parent::beforeSet();
	}

	public function beforeSave()
	{
		if ($this->object->deleteUserId || $this->object->deleteTime)
		{
			$this->failure($this->app()->lang('restore_error'));

			return false;
		}
		if ($this->conversation = $this->modx->getObject('modxTalksConversation', ['id' => $this->object->conversationId]))
		{
			if ($this->conversation->deleted)
			{
				$this->conversation->deleted -= 1;
				$this->conversation->save();
			}
		}

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
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks web/comment/restore] Cache comment error, ID ' . $this->object->id);
			}
			if ( ! $this->app()->cacheConversation($this->conversation))
			{
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks web/comment/restore] Cache conversation error, ID ' . $this->conversation->id);
			}
		}

		return parent::afterSave();
	}

	public function cleanup()
	{
		$data = $this->_prepareData();

		return $this->success($this->app()->lang('successfully_restored'), $data);
	}

	private function _prepareData()
	{
		$name = $this->app()->lang('guest');
		$email = 'anonym@anonym.com';

		if ( ! $this->object->userId)
		{
			$name = $this->object->username;
			$email = $this->object->useremail;
		}
		elseif ($user = $this->modx->getObjectGraph('modUser', '{"Profile":{}}', $this->object->userId, true))
		{
			$profile = $user->getOne('Profile');
			$email = $profile->get('email');
			if ( ! $name = $profile->get('fullname'))
			{
				$name = $user->get('username');
			}
		}

		$edit_name = $name;
		if ($this->object->userId !== $this->object->editUserId)
		{
			if ($edit_user = $this->modx->getObjectGraph('modUser', '{"Profile":{}}', $this->object->editUserId, true))
			{
				$profile = $edit_user->getOne('Profile');
				if ( ! $edit_name = $profile->get('fullname'))
				{
					$edit_name = $user->get('username');
				}
			}
		}

		$data = [
			'avatar' => $this->app()->getAvatar($email),
			'hideAvatar' => ' style="display: none;"',
			'name' => $name,
			'email' => $email,
			'content' => $this->app()->bbcode($this->object->content),
			'index' => date('Ym', $this->object->time),
			'date' => date($this->app()->config['dateFormat'], $this->object->time),
			'funny_date' => $this->app()->date_format($this->object->time),
			'link' => $this->app()->getLink($this->object->idx),
			'id' => (int) $this->object->id,
			'idx' => (int) $this->object->idx,
			'userId' => md5($this->object->userId . $email),
			'user' => $this->app()->userButtons($this->object->userId, $this->object->time),
			'timeago' => date('c', $this->object->time),
			'timeMarker' => '',
			'funny_edit_date' => $this->app()->lang('date_now'),
			'edit_name' => $this->app()->lang('edited_by', ['name' => $edit_name]),
			'user_info' => '',
			'like_block' => '',
		];
		if ($this->app()->isModerator())
		{
			$data['user_info'] = $this->app()->_parseTpl($this->app()->config['user_info'], [
				'email' => $email,
				'ip' => $this->object->ip
			], true);
		}

		return $data;
	}
}

return 'commentUpdateProcessor';
