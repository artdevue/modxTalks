<?php
/**
 * @package post
 * @subpackage processors
 */
class postAddVoteProcessor extends modObjectUpdateProcessor {
    public $classKey = 'modxTalksPost';
    public $languageTopics = array('modxtalks:default');
    public $objectType = 'modxtalks.post';
    public $context = '';
    private $voted;

    /**
     * Before set the fields on the post
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
         * Check user permission to add a vote
         */
        if (!$this->modx->user->isAuthenticated($this->context) && !$this->modx->modxtalks->isModerator()) {
            $this->failure($this->modx->lexicon('modxtalks.cant_vote'));
            $this->voted = false;
            return false;
        }

        $this->userId = $this->modx->user->id;
        $this->votes = $this->object->getVotes();

        // Remove vote
        if ($this->votes['votes'] > 0 && in_array($this->userId, $this->votes['users'])) {
            $this->object->removeVote($this->userId);
            return parent::beforeSet();
        }

        // Add vote
        $this->object->addVote($this->userId);
        $this->voted = true;

        return parent::beforeSet();
    }

    public function cleanup() {
        $data = $this->_preparePostData();

        // Remove vote message
        if (!$this->voted) {
            return $this->success($this->modx->lexicon('modxtalks.successfully_un_voted'), $data);
        }
        // Add vote message
        return $this->success($this->modx->lexicon('modxtalks.successfully_voted'), $data);
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
                $this->modx->log(xPDO::LOG_LEVEL_ERROR,'[modxTalks web/comment/vote] Cache comment error, ID '.$this->object->id);
            }
        }
        return parent::afterSave();
    }

    /**
     * Override cleanup to send only back needed params
     * @return array|string
     */
    private function _preparePostData() {
        $this->votes = $this->object->getVotes();
        $data = array(
            'votes' => $this->votes['votes'],
            'html'  => '',
            'btn' => $this->modx->lexicon('modxtalks.i_like'),
        );
        if (in_array($this->userId, $this->votes['users'])) {
            $total = count($this->votes['users']) - 1;
            $data['btn'] = $this->modx->lexicon('modxtalks.not_like');
            if ($total > 0) {
                $data['html'] = $this->modx->modxtalks->decliner($total,$this->modx->lexicon('modxtalks.people_like_and_you', array('total' => $total)));
            }
            else {
                $data['html'] = $this->modx->lexicon('modxtalks.you_like');
            }
        }
        elseif ($this->votes['votes'] > 0) {
            $data['html'] = $this->modx->modxtalks->decliner($this->votes['votes'],$this->modx->lexicon('modxtalks.people_like', array('total' => $this->votes['votes'])));
        }
        return $data;
    }

}

return 'postAddVoteProcessor';