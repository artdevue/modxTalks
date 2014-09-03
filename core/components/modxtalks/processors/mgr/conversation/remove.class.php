<?php

/**
 * Remove Conversation
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksRemoveConversation extends modObjectRemoveProcessor
{
	public $classKey = 'modxTalksConversation';
	public $objectType = 'modxtalks.conversation';
	public $languageTopics = ['modxtalks:default'];
	public $beforeRemoveEvent = '';
	public $afterRemoveEvent = '';

	/**
	 * Remove all comments of this conversation
	 * @return bool
	 */
	public function afterRemove()
	{
		$this->modx->removeCollection('modxTalksPost', [
			'conversationId' => $this->object->id
		]);
		$this->success($this->modx->lexicon('modxtalks.conversation_removed'));

		return true;
	}
}

return 'modxTalksRemoveConversation';
