<?php

require_once dirname(dirname(dirname(__FILE__))) . '/modxtalksprocessor.trait.php';

/**
 * Remove unconfirmed comment
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksTempPostRemoveProcessor extends modObjectRemoveProcessor
{
	use modxTalksProcessorTrait;

	public $classKey = 'modxTalksTempPost';
	public $objectType = 'modxtalks.temppost';
	public $languageTopics = ['modxtalks:default'];
	public $beforeRemoveEvent = '';
	public $afterRemoveEvent = '';

	public function beforeRemove()
	{
		$conversation = $this->modx->getObject('modxTalksConversation', $this->object->conversationId);

		if ( ! $conversation)
		{
			return $this->app()->lang('empty_conversation');
		}

		if ($conversation->unconfirmed)
		{
			$conversation->unconfirmed -= 1;
		}

		if ($conversation->save() !== true)
		{
			return $this->app()->lang('error');
		}

		return parent::beforeRemove();
	}

	/**
	 * Show success message after comment successfully remove
	 * @return bool
	 */
	public function afterRemove()
	{
		$this->success($this->app()->lang('successfully_deleted'));

		return true;
	}
}

return 'modxTalksTempPostRemoveProcessor';
