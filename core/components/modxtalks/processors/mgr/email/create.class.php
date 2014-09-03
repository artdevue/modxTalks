<?php

require_once dirname(dirname(dirname(__FILE__))) . '/modxtalksprocessor.trait.php';

/**
 * Add Email to Block List
 *
 * @package modxTalks
 * @subpackage processors
 */
class modxTalksEmailBlockCreateProcessor extends modObjectCreateProcessor
{
	use modxTalksProcessorTrait;

	public $classKey = 'modxTalksEmailBlock';
	public $languageTopics = ['modxtalks:default'];
	public $objectType = 'modxtalks.email';

	public function beforeSet()
	{
		$email = $this->getProperty('email');
		$intro = $this->getProperty('intro');

		if ($this->doesAlreadyExist(['email' => $email]))
		{
			$this->addFieldError('email', $this->app()->lang('email_already_banned'));
		}

		$comment = $this->modx->newObject('modxTalksPost');

		if ( ! $comment->validateEmail($email))
		{
			$this->addFieldError('email', $this->app()->lang('bad_email'));
		}

		$this->properties = [
			'email' => $email,
			'date' => time(),
		];

		if ( ! empty($intro))
		{
			$this->properties['intro'] = $intro;
		}

		return parent::beforeSet();
	}
}

return 'modxTalksEmailBlockCreateProcessor';
