<?php

require_once dirname(dirname(dirname(__FILE__))) . '/modxtalksprocessor.trait.php';

/**
 * @package post
 * @subpackage processors
 */
class postAddVoteProcessor extends modObjectUpdateProcessor
{
	use modxTalksProcessorTrait;

	public $classKey = 'modxTalksPost';
	public $languageTopics = ['modxtalks:default'];
	public $objectType = 'modxtalks.post';
	public $context = '';
	protected $voted;

	/**
	 * Before set the fields on the post
	 *
	 * @return mixed
	 */
	public function beforeSet()
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
		 * Check Context
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

		$this->properties = [];

		/**
		 * Check user permission to add a vote
		 */
		if ( ! $this->modx->user->isAuthenticated($this->context) && ! $this->app()->isModerator())
		{
			$this->failure($this->app()->lang('cant_vote'));
			$this->voted = false;

			return false;
		}

		$this->userId = $this->modx->user->id;
		$this->votes = $this->object->getVotes();

		// Remove vote
		if ($this->votes['votes'] > 0 && in_array($this->userId, $this->votes['users']))
		{
			$this->object->removeVote($this->userId);

			return parent::beforeSet();
		}

		// Add vote
		$this->object->addVote($this->userId);
		$this->voted = true;

		return parent::beforeSet();
	}

	public function cleanup()
	{
		$data = $this->_preparePostData();

		// Remove vote message
		if ( ! $this->voted)
		{
			return $this->success($this->app()->lang('successfully_un_voted'), $data);
		}

		// Add vote message
		return $this->success($this->app()->lang('successfully_voted'), $data);
	}

	/**
	 * After save
	 * Add comment to cache
	 *
	 * @return void
	 */
	public function afterSave()
	{
		if ($this->app()->mtCache === true)
		{
			if ( ! $this->app()->cacheComment($this->object))
			{
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks web/comment/vote] Cache comment error, ID ' . $this->object->id);
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
		$this->votes = $this->object->getVotes();
		$data = [
			'votes' => $this->votes['votes'],
			'html' => '',
			'btn' => $this->app()->lang('i_like'),
		];
		if (in_array($this->userId, $this->votes['users']))
		{
			$total = count($this->votes['users']) - 1;
			$data['btn'] = $this->app()->lang('not_like');
			if ($total > 0)
			{
				$data['html'] = $this->app()->decliner($total, $this->app()->lang('people_like_and_you', ['total' => $total]));
			}
			else
			{
				$data['html'] = $this->app()->lang('you_like');
			}
		}
		elseif ($this->votes['votes'] > 0)
		{
			$data['html'] = $this->app()->decliner($this->votes['votes'], $this->app()->lang('people_like', ['total' => $this->votes['votes']]));
		}

		return $data;
	}
}

return 'postAddVoteProcessor';
