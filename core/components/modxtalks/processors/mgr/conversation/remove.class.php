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
    public $languageTopics = array('modxtalks:default');
    public $beforeRemoveEvent = '';
    public $afterRemoveEvent = '';
    protected $conversationId;

    /**
     * Remove all comments of this conversation
     * @return bool
     */
    public function afterRemove() {
        $this->modx->removeCollection('modxTalksPost', array(
            'conversationId' => $this->conversationId
        ));
        $this->success($this->modx->lexicon('modxtalks.conversation_removed'));
        return true;
    }

    public function beforeRemove() {
        $this->conversationId = $this->object->get('id');
        return parent::beforeRemove();
    }

}

return 'modxTalksRemoveConversation';