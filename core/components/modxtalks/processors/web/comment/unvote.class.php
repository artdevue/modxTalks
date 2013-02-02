<?php
/**
 * @package post
 * @subpackage processors
 */
class postUnVoteProcessor extends modObjectUpdateProcessor {
    public $classKey = 'modxTalksPost';
    public $languageTopics = array('modxtalks:default');
    public $objectType = 'modxtalks.post';
    public $context = '';

    /**
     * Before set the fields on the comment
     *
     * @return void
     */
    public function beforeSet() {
        $this->context = trim($this->getProperty('ctx'));

        /**
         * Check Context
         */
        if (empty($this->context)) {
            $this->failure($this->modx->lexicon('modxtalks.empty_context'));
            return false;
        }
        elseif (!$this->modx->getCount('modContext',$this->context)) {
            $this->failure($this->modx->lexicon('modxtalks.bad_context'));
            return false;
        }

        $this->properties = array();

        /**
         * Check user permission to remove a vote
         */
        if (!$this->modx->user->isAuthenticated($this->context) && !$this->modx->modxtalks->isModerator()) {
            $this->failure($this->modx->lexicon('modxtalks.cant_un_vote'));
            return false;
        }

        $userId = $this->modx->user->id;
        $this->votes = $this->object->getVotes();

        if (!in_array($userId, $this->votes['users'])) {
            $this->failure($this->modx->lexicon('modxtalks.not_voted'));
            return false;
        }

        $this->object->removeVote($userId);

        return parent::beforeSet();
    }

    public function cleanup() {
        $data = $this->_preparePostData();
        return $this->success($this->modx->lexicon('modxtalks.successfully_un_voted'), $data);
    }

    /**
     * After save
     * Add comment to cache
     *
     * @return void
     */
    public function afterSave() {
        if ($this->modx->modxtalks->mtCache === true) {
            if (!$this->modx->modxtalks->cacheComment($this->object)) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR,'[modxTalks web/comment/unvote] Cache comment error, ID '.$this->object->id);
            }
        }
        return parent::afterSave();
    }

    /**
     * Override cleanup to send only back needed params
     * @return array|string
     */
    private function _preparePostData() {
        $data = $this->object->votes;
        return $data;
    }

}

return 'postUnVoteProcessor';