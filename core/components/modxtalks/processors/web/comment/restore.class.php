<?php
/**
 * @package comment
 * @subpackage processors
 */
class commentUpdateProcessor extends modObjectUpdateProcessor {
    public $classKey = 'modxTalksPost';
    public $languageTopics = array('modxtalks:default');
    public $context = '';

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
        if ($slug = $this->getProperty('slug')) {
            $this->modx->modxtalks->config['slug'] = $slug;
        }
        /**
         * Check user permission to restore comment
         */
        if (!$this->modx->modxtalks->isModerator()) {
            $this->failure($this->modx->lexicon('access_denied'));
            return false;
        }

        if (!$this->object->deleteTime) {
            $this->failure($this->modx->lexicon('modxtalks.not_deleted'));
            return false;
        }

        $this->properties = array(
            'deleteTime' => NULL,
            'deleteUserId' => NULL,
            'editTime' => time(),
            'editUserId' => $this->modx->user->id,
        );

        return parent::beforeSet();
    }

    public function beforeSave() {
        if ($this->object->deleteUserId || $this->object->deleteTime) {
            $this->failure($this->modx->lexicon('modxtalks.restore_error'));
            return false;
        }
        if ($this->theme = $this->modx->getObject('modxTalksConversation',array('id' => $this->object->conversationId))) {
            if (!$this->theme->getProperties('comments')) {
                $this->theme->setProperties($this->defaultProprties,'comments',false);
            }
            if ($deleted = $this->theme->getProperty('deleted','comments',0)) {
                $this->theme->setProperty('deleted',--$deleted,'comments');
                $this->theme->save();
            }
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
                $this->modx->log(xPDO::LOG_LEVEL_ERROR,'[modxTalks web/comment/restore] Cache comment error, ID '.$this->object->id);
            }
            if (!$this->modx->modxtalks->cacheConversation($this->theme)) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR,'[modxTalks web/comment/restore] Cache conversation error, ID '.$this->theme->id);
            }
        }
        return parent::afterSave();
    }

    public function cleanup() {
        $data = $this->_prepareData();
        return $this->success($this->modx->lexicon('modxtalks.successfully_restored'),$data);
    }

    private function _prepareData() {
        $name = $this->modx->lexicon('modxtalks.guest');
        $email = 'anonym@anonym.com';

        if (!$this->object->userId) {
            $name = $this->object->username;
            $email = $this->object->useremail;
        }
        elseif ($user = $this->modx->getObjectGraph('modUser', '{"Profile":{}}', $this->object->userId, true)) {
            $profile = $user->getOne('Profile');
            $email = $profile->get('email');
            if (!$name = $profile->get('fullname')) {
                $name = $user->get('username');
            }
        }
        
        $edit_name = $name;
        if ($this->object->userId !== $this->object->editUserId) {
            if ($edit_user = $this->modx->getObjectGraph('modUser', '{"Profile":{}}', $this->object->editUserId, true)) {
                $profile = $edit_user->getOne('Profile');
                if (!$edit_name = $profile->get('fullname'))
                    $edit_name = $user->get('username');
            }
        }

        $data = array(
            'avatar'     => $this->modx->modxtalks->getAvatar($email),
            'hideAvatar' => ' style="display: none;"',
            'name'       => $name,
            'email'      => $email,
            'content'    => $this->modx->modxtalks->bbcode($this->object->content),
            'index'      => date('Ym',$this->object->time),
            'date'       => date($this->modx->modxtalks->config['dateFormat'],$this->object->time),
            'funny_date' => $this->modx->modxtalks->date_format($this->object->time),
            'link'       => $this->modx->modxtalks->getLink($this->object->idx),
            'id'         => (int) $this->object->id,
            'idx'        => (int) $this->object->idx,
            'userId'     => md5($this->object->userId.$email),
            'user'       => $this->modx->modxtalks->userButtons($this->object->userId,$this->object->time),
            'timeago'    => date('c',$this->object->time),
            'timeMarker' => '',
            'funny_edit_date' => $this->modx->lexicon('modxtalks.date_now'),
            'edit_name'       => $this->modx->lexicon('modxtalks.edited_by',array('name' => $edit_name)),
            'user_info'       => '',
            'like_block'      => '',
        );
        if ($this->modx->modxtalks->isModerator() === true) {
            $data['user_info'] = $this->modx->modxtalks->_parseTpl($this->modx->modxtalks->config['user_info'], array(
                'email' => $email,
                'ip' => $this->object->ip
            ), true);
        }

        return $data;
    }

}

return 'commentUpdateProcessor';