<?php
/**
 * @package post
 * @subpackage processors
 */
class commentRemoveProcessor extends modObjectUpdateProcessor {
    public $classKey = 'modxTalksPost';
    public $languageTopics = array('modxtalks:default');
    public $objectType = 'modxtalks.post';
    public $context = '';
    protected $defaultProprties = array(
        'total' => 0,
        'deleted' => 0,
        'unconfirmed' => 0,
    );

    public function initialize() {
        /**
         * Check context
         */
        $this->context = trim($this->getProperty('ctx'));
        if (empty($this->context)) {
            $this->failure($this->modx->lexicon('modxtalks.empty_context'));
            return false;
        }
        elseif (!$this->modx->getCount('modContext',$this->context)) {
            $this->failure($this->modx->lexicon('modxtalks.bad_context'));
            return false;
        }
        return parent::initialize();
    }

    public function beforeSet() {
        if ($this->object->deleteTime > 0 || $this->object->deleteUserId) {
            $this->failure($this->modx->lexicon('modxtalks.already_deleted'));
            return false;
        }

        /**
         * Set comments data
         */
        $this->properties = array(
            'deleteTime' => time(),
            'deleteUserId' => $this->modx->user->id,
        );

        /**
         * If users is moderator
         */
        if ($this->modx->modxtalks->isModerator()) {
            return parent::beforeSet();
        }

        /**
         * Check user permission to delete comment
         */
        $userId = $this->object->userId;
        if (!$this->modx->user->isAuthenticated($this->context)) {
            $this->failure($this->modx->lexicon('modxtalks.delete_permission'));
            return false;
        }
        // Check comment owner
        if ($this->modx->user->id != $userId) {
            $this->failure($this->modx->lexicon('modxtalks.delete_permission'));
            return false;
        }
        // Check time for delete comment
        if ((time() - $this->object->time) > $this->modx->modxtalks->config['edit_time']) {
            $this->failure($this->modx->lexicon('modxtalks.delete_timeout'));
            return false;
        }

        return parent::beforeSet();
    }

    public function beforeSave() {
        if (!$this->object->deleteUserId || !$this->object->deleteTime) {
            $this->failure($this->modx->lexicon('modxtalks.delete_error'));
            return false;
        }
        if ($this->theme = $this->modx->getObject('modxTalksConversation',array('id' => $this->object->conversationId))) {
            if (!$this->theme->getProperties('comments')) {
                $this->theme->setProperties($this->defaultProprties,'comments',false);
            }
            $deleted = $this->theme->getProperty('deleted','comments',0);
            $this->theme->setProperty('deleted',++$deleted,'comments');
            $this->theme->save();
        }
        return parent::beforeSave();
    }

    /**
     * After save
     * Add comment to cache
     *
     * @return void
     **/
    public function afterSave() {
        if ($this->modx->modxtalks->mtCache === true) {
            if (!$this->modx->modxtalks->cacheComment($this->object)) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR,'[modxTalks web/comment/create] Error cache the comment with ID '.$this->object->id);
            }
            if (!$this->modx->modxtalks->cacheConversation($this->theme)) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR,'[modxTalks web/comment/create] Error cache the conversation with ID '.$this->theme->id);
            }
        }
        return parent::afterSave();
    }

    public function cleanup() {
        $data = $this->_prepareData();
        return $this->success($this->modx->lexicon('modxtalks.successfully_deleted'),$data);
    }

    private function _prepareData() {
        $name = $this->modx->lexicon('modxtalks.guest');
        $email = 'anonym@anonym.com';

        if (!$deleteUser = $this->modx->user->Profile->fullname) {
            $deleteUser = $this->modx->user->username;
        }

        if ($this->object->userId == $this->object->deleteUserId) {
            $name = $deleteUser;
            $email = $this->modx->user->Profile->email;
        }
        elseif (!$this->object->userId) {
            $name = $this->object->username;
            $email = $this->object->useremail;
        }
        else {
            if ($user = $this->modx->getObject('modUser',$this->object->userId)) {
                $profile = $user->getOne('Profile');
                $email = $profile->email;
                if (!$name = $profile->fullname) {
                    $name = $user->username;
                }
            }
        }

        $data = array(
            'deleteUser' => $deleteUser,
            'delete_date' => date($this->modx->modxtalks->config['mtDateFormat'].' O',$this->object->deleteTime),
            'deleted_by' => $this->modx->lexicon('modxtalks.deleted_by'),
            'funny_delete_date' => $this->modx->lexicon('modxtalks.date_now'),
            'name' => $name,
            'index' => date('Ym',$this->object->time),
            'date' => date($this->modx->modxtalks->config['mtDateFormat'].' O',$this->object->time),
            'funny_date' => $this->modx->modxtalks->date_format(array('date' => $this->object->time)),
            'id' => (int) $this->object->id,
            'idx' => (int) $this->object->idx,
            'userId' => md5($this->object->userId.$email),
            'restore' => $this->modx->lexicon('modxtalks.restore'),
            'timeago' => date('c',$this->object->time),
        );
        return $data;
    }

}

return 'commentRemoveProcessor';