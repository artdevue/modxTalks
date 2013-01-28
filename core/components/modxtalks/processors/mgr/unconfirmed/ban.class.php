<?php
/**
 * Ban IP address from unconfirmed comment
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksTempPostBanProcessor extends modObjectRemoveProcessor {
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

        if ($this->modx->getCount('modxTalksIpBlock',array('ip' => $this->object->ip))) {
            return $this->modx->lexicon('modxtalks.ip_blocked');
        }

        $ip = $this->modx->newObject('modxTalksIpBlock',array(
            'ip' => $this->object->ip,
            'date' => time(),
            'intro' => '',
        ));


        if ($ip->save() !== true) {
            return $this->modx->lexicon('modxtalks.ip_save_error');
        }

        if ($this->object->userId > 0) {
            if ($user = $this->modx->getObject('modUser',$this->object->userId)) {
                $user->set('active',false);
                if ($user->save() !== true) {
                    return $this->modx->lexicon('modxtalks.error');
                }
            }
        }

        return parent::beforeRemove();
    }

    public function afterRemove() {
        $this->success($this->modx->lexicon('modxtalks.ip_ban_success'));
        return parent::afterRemove();
    }

}
return 'modxTalksTempPostBanProcessor';