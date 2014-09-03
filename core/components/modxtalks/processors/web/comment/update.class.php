<?php

require_once dirname(dirname(dirname(__FILE__))) . '/modxtalksprocessor.trait.php';

/**
 * @package post
 * @subpackage processors
 */
class postUpdateProcessor extends modObjectUpdateProcessor
{
	use modxTalksProcessorTrait;

	public $classKey = 'modxTalksPost';
	public $languageTopics = ['modxtalks:default'];
	public $context = '';

	public function beforeSet()
	{
		if ($slug = $this->getProperty('slug'))
		{
			$this->app()->config['slug'] = $slug;
		}
		/**
		 * Check context
		 */
		$this->context = trim($this->getProperty('ctx'));
		if (empty($this->context))
		{
			$this->failure($this->app()->lang('empty_context'));

			return false;
		}
		else if ( ! $this->modx->getCount('modContext', $this->context))
		{
			$this->failure($this->app()->lang('bad_context'));

			return false;
		}

		// Check Comment Content
		$content = trim($this->getProperty('content'));
		if (empty($content))
		{
			$this->failure($this->app()->lang('empty_content'));

			return false;
		}
		else if ( ! is_string($content))
		{
			$this->failure($this->app()->lang('bad_content'));

			return false;
		}
		else if (mb_strlen($content, 'UTF-8') < 2)
		{
			$this->failure($this->app()->lang('bad_content_length', ['length' => 2]));

			return false;
		}

		/**
		 * Set comments data
		 */
		$this->properties = [
			'editTime' => time(),
			'editUserId' => $this->modx->user->id,
			'content' => $content,
		];

		/**
		 * If users is moderator
		 */
		if ($this->app()->isModerator())
		{
			return parent::beforeSet();
		}

		/**
		 * Check user permission to edit comment
		 */
		$userId = $this->object->userId;
		if ( ! $this->modx->user->isAuthenticated($this->context))
		{
			$this->failure($this->app()->lang('edit_permission'));

			return false;
		}
		// Check comment owner
		if ($this->modx->user->id != $userId)
		{
			$this->failure($this->app()->lang('edit_permission'));

			return false;
		}
		// Check time for edit comment
		if ((time() - $this->object->time) > $this->app()->config['edit_time'])
		{
			$this->failure($this->app()->lang('edit_timeout', ['seconds' => $this->modx->getOption('modxtalks.edit_time')]));

			return false;
		}

		return parent::beforeSet();
	}

	public function cleanup()
	{
		$data = $this->_preparePostData();

		return $this->success($this->app()->lang('successfully_updated'), $data);
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
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks web/comment/update] Cache comment error, ID ' . $this->object->id);
			}
		}

		return parent::afterSave();
	}

	/**
	 * Override cleanup to send only back needed params
	 * @return array|string
	 */
	private function _preparePostData()
	{
		$name = $this->app()->lang('guest');
		$email = 'anonym@anonym.com';

		if ($user = $this->modx->getObjectGraph('modUser', '{"Profile":{}}', $this->object->userId, true))
		{
			$profile = $user->getOne('Profile');
			$email = $profile->get('email');
			if ( ! $name = $profile->get('fullname'))
			{
				$name = $user->get('username');
			}
		}
		else
		{
			$name = $this->object->username;
			$email = $this->object->useremail;
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
			'hideAvatar' => '',
			'name' => $name,
			'edit_name' => $this->app()->lang('edited_by', ['name' => $edit_name]),
			'content' => $this->app()->bbcode($this->object->content),
			'index' => date('Ym', $this->object->time),
			'date' => date($this->app()->config['dateFormat'], $this->object->time),
			'funny_date' => $this->app()->date_format($this->object->time),
			'funny_edit_date' => $this->app()->lang('date_now'),
			'link' => $this->app()->getLink($this->object->idx),
			'id' => (int) $this->object->id,
			'idx' => (int) $this->object->idx,
			'user' => $this->app()->userButtons($this->object->userId, $this->object->time),
			'userId' => md5($this->object->userId . $email),
			'timeago' => date('c', $this->object->time),
			'timeMarker' => '',
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

return 'postUpdateProcessor';
