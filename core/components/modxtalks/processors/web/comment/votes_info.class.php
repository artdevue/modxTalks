<?php

require_once dirname(dirname(dirname(__FILE__))) . '/modxtalksprocessor.trait.php';

/**
 * @package modxtalks
 * @subpackage processors
 */
class getVotesInfo extends modObjectGetProcessor
{
	use modxTalksProcessorTrait;

	public $classKey = 'modxTalksPost';
	public $objectType = 'modxtalks.post';
	public $languageTopics = ['modxtalks:default'];
	public $context = '';

	public function initialize()
	{
		/**
		 * Check for voting
		 */
		if ( ! $this->app()->config['voting'])
		{
			$this->failure($this->app()->lang('voting_disabled'));

			return false;
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

		return parent::initialize();
	}

	public function process()
	{
		$userId = $this->modx->user->id;
		$votes = $this->object->getVotes();
		if (in_array($userId, $votes['users']))
		{
			$key = array_search($userId, $votes['users']);
			unset($votes['users'][$key]);
		}

		$users = $this->app()->getUsers($votes['users']);
		foreach ($users as $k => & $user)
		{
			$users[$k] = [
				'name' => $user['fullname'] ? $user['fullname'] : $user['username'],
				'avatar' => $this->app()->getAvatar($user['email'], 50),
			];
		}

		return $this->success('', ['users' => $users]);
	}
}

return 'getVotesInfo';
