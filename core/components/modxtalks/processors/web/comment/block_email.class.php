<?php

require_once dirname(dirname(dirname(__FILE__))) . '/modxtalksprocessor.trait.php';

/**
 * @package modxTalks
 * @subpackage processors
 */
class blockUserEmailProcessor extends modObjectCreateProcessor
{
	use modxTalksProcessorTrait;

	public $classKey = 'modxTalksEmailBlock';
	public $languageTopics = ['modxtalks:default'];
	public $objectType = 'modxtalks.email';

	public function beforeSet()
	{
		/**
		 * @var integer Comment ID
		 */
		$id = (int) $this->getProperty('id');
		/**
		 * Check comment ID
		 */
		if ( ! $id)
		{
			$this->failure($this->modx->lexicon('modxtalks.post_err_ns'));

			return false;
		}
		/**
		 * Check for comment presents
		 */
		if ( ! $comment = $this->modx->getObject('modxTalksPost', ['id' => $id]))
		{
			$this->failure($this->modx->lexicon('modxtalks.post_err_nf'));

			return false;
		}

		$userInfo = $comment->getUserData();
		/**
		 * Email Address
		 */
		$email = $userInfo['email'];
		/**
		 * @var string Context key
		 */
		$this->context = trim($this->getProperty('ctx'));
		/**
		 * Check context
		 */
		if (empty($this->context))
		{
			$this->failure($this->modx->lexicon('modxtalks.empty_context'));

			return false;
		}
		elseif ( ! $this->modx->getCount('modContext', $this->context))
		{
			$this->failure($this->modx->lexicon('modxtalks.bad_context'));

			return false;
		}

		if ( ! $this->app()->isModerator())
		{
			$this->failure($this->modx->lexicon('modxtalks.edit_permission'));

			return false;
		}
		/**
		 * If Email Address already banned
		 */
		if ($this->doesAlreadyExist(['email' => $email]))
		{
			$this->failure($this->modx->lexicon('modxtalks.email_already_banned'));

			return false;
		}

		$this->properties = [
			'email' => $email,
			'date' => time(),
		];

		return parent::beforeSet();
	}

	/**
	 * Return the success message
	 * @return array
	 */
	public function cleanup()
	{
		return $this->success($this->modx->lexicon('modxtalks.email_ban_success'));
	}
}

return 'blockUserEmailProcessor';
