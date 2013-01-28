<?php
/**
 * Approve unconfirmed comment
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksTempPostApproveProcessor extends modObjectRemoveProcessor {
    public $classKey = 'modxTalksTempPost';
    public $objectType = 'modxtalks.temppost';
    public $languageTopics = array('modxtalks:default');
    public $beforeRemoveEvent = '';
    public $afterRemoveEvent = '';
    protected $conversationId;

    public function beforeRemove() {
        $this->conversationId = $this->object->conversationId;

        if (!$this->conversation = $this->modx->getObject('modxTalksConversation',$this->conversationId)) {
            return $this->modx->lexicon('modxtalks.empty_conversation');
        }
        $cProperties = $this->conversation->getProperties('comments');
        $this->conversation->setProperties(array(
            'total' => ++$cProperties['total'],
            'deleted' => $cProperties['deleted'],
            'unconfirmed' => $cProperties['unconfirmed'] > 0 ? --$cProperties['unconfirmed'] : 0
        ),'comments',false);

        $q = $this->modx->newQuery('modxTalksPost',array('conversationId' => $this->conversationId));
        $q->sortby('idx','DESC');
        if (!$lastComment = $this->modx->getObject('modxTalksPost',$q)) {
            return $this->modx->lexicon('modxtalks.error');
        }

        $idx = $lastComment->get('idx');
        $this->comment = $this->modx->newObject('modxTalksPost');
        $time = time();
        $commentParams = array(
            'idx' => ++$idx,
            'conversationId' => $this->conversationId,
            'time' => $time,
            'date' => strftime('%Y%m', $time),
            'content' => $this->object->content,
            'ip' => $this->object->ip,
        );

        if ($this->object->userId > 0) {
            $commentParams['userId'] = $this->object->userId;
        }
        else {
            $commentParams['username'] = $this->object->username;
            $commentParams['useremail'] = $this->object->useremail;
        }

        $this->comment->fromArray($commentParams);
        if ($this->comment->save() !== true) {
            return $this->modx->lexicon('modxtalks.error');
        }

        if ($this->conversation->save() !== true) {
            return $this->modx->lexicon('modxtalks.error');
        }

        return parent::beforeRemove();
    }

    public function afterRemove() {
        /**
         * Обновляем кэш комментария и темы
         */
        if ($this->modx->modxtalks->mtCache === true) {
            if (!$this->modx->modxtalks->cacheComment($this->comment)) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR,'[modxTalks web/comment/restore] Error cache the comment with ID '.$this->comment->id);
            }
            if (!$this->modx->modxtalks->cacheConversation($this->conversation)) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR,'[modxTalks web/comment/restore] Error cache conversation with ID '.$this->conversation->id);
            }
        }

        /**
         * Отправляем уведомление о подтверждении комментария пользователю оставившему комментарий
         */
        if (!$this->modx->modxtalks->notifyUser($this->comment)) {
            return $this->modx->lexicon('modxtalks.error');
        }

        $this->success($this->modx->lexicon('modxtalks.comment_approved'));
        return parent::afterRemove();
    }

}
return 'modxTalksTempPostApproveProcessor';