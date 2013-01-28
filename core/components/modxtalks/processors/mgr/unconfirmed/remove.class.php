<?php
/**
 * Remove unconfirmed comment
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksTempPostRemoveProcessor extends modObjectRemoveProcessor {
    public $classKey = 'modxTalksTempPost';
    public $objectType = 'modxtalks.temppost';
    public $languageTopics = array('modxtalks:default');
    public $beforeRemoveEvent = '';
    public $afterRemoveEvent = '';

    public function beforeRemove() {
        $this->conversationId = $this->object->conversationId;

        if (!$conversation = $this->modx->getObject('modxTalksConversation',$this->conversationId)) {
            return $this->modx->lexicon('modxtalks.empty_conversation');
        }
        $cProperties = $conversation->getProperty('unconfirmed','comments',0);
        $unconfirmed = $cProperties['unconfirmed'] > 0 ? --$cProperties['unconfirmed'] : 0;
        $conversation->setProperty('unconfirmed',$unconfirmed);

        if ($conversation->save() !== true) {
            return $this->modx->lexicon('modxtalks.error');
        }

        return parent::beforeRemove();
    }

    /**
     * Show success message after comment successfully remove
     * @return bool
     */
    public function afterRemove() {
        $this->success($this->modx->lexicon('modxtalks.successfully_deleted'));
        return true;
    }

}

return 'modxTalksTempPostRemoveProcessor';