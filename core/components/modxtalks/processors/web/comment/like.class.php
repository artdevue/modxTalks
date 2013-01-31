<?php
/**
 * @package post
 * @subpackage processors
 */
class postUpdateProcessor extends modObjectUpdateProcessor {
    public $classKey = 'modxTalksPost';
    public $languageTopics = array('modxtalks:default');
    public $context = '';

    /**
     * Before set the fields on the post
     *
     * @return void
     */
    public function beforeSet() {
        $content = trim($this->getProperty('content'));
        $userId = $this->object->userId;
        $this->context = trim($this->getProperty('ctx'));
        if ($slug = $this->getProperty('slug')) {
            $this->modx->modxtalks->config['slug'] = $slug;
        }

        if (empty($this->context)) {
            $this->failure($this->modx->lexicon('modxtalks.empty_context'));
            return false;
        }
        elseif (!$this->modx->getCount('modContext',$this->context)) {
            $this->failure($this->modx->lexicon('modxtalks.bad_context'));
            return false;
        }
        /**
         * Check Comment Content
         */
        if (empty($content)) {
            $this->failure($this->modx->lexicon('modxtalks.empty_content'));
            return false;
        }
        elseif (!is_string($content)) {
            $this->failure($this->modx->lexicon('modxtalks.bad_content'));
            return false;
        }
        elseif (mb_strlen($content,'UTF-8') < 2) {
            $this->failure($this->modx->lexicon('modxtalks.bad_content_length',array('length' => 2)));
            return false;
        }

        /**
         * Check user permission to edit comment
         */
        if ($this->modx->user->isAuthenticated($this->context) && !$this->modx->modxtalks->isModerator()) {
            if ($this->modx->user->id != $userId) {
                $this->failure($this->modx->lexicon('modxtalks.edit_permission'));
                return false;
            }
            if ((time() - $this->object->time) > $this->modx->modxtalks->config['edit_time']) {
                $this->failure($this->modx->lexicon('modxtalks.edit_timeout',array('seconds' => $this->modx->getOption('modxtalks.edit_time'))));
                return false;
            }
        }
        elseif (!$this->modx->modxtalks->isModerator()) {
            $this->failure($this->modx->lexicon('modxtalks.edit_permission'));
            return false;
        }

        $this->properties = array(
            'editTime' => time(),
            'editUserId' => $this->modx->user->id,
            'content' => $content,
        );

        return parent::beforeSet();
    }

    public function cleanup() {
        $data = $this->_preparePostData();
        return $this->success($this->modx->lexicon('modxtalks.successfully_updated'), $data);
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
                $this->modx->log(xPDO::LOG_LEVEL_ERROR,'[modxTalks web/comment/update] Cache comment error, ID '.$this->object->id);
            }
        }
        return parent::afterSave();
    }

    /**
     * Override cleanup to send only back needed params
     * @return array|string
     */
    private function _preparePostData() {
        $name = '';
        $email = '';
        if ($user = $this->modx->getObjectGraph('modUser', '{"Profile":{}}', $this->object->userId, true)) {
            $profile = $user->getOne('Profile');
            $email = $profile->get('email');
            if (!$name = $profile->get('fullname'))
                $name = $user->get('username');
        } else {
            $name = $this->object->username;
            $email = $this->object->useremail;
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
            'avatar'          => $this->modx->modxtalks->getAvatar($email),
            'hideAvatar'      => '',
            'name'            => $name,
            'edit_name'       => $this->modx->lexicon('modxtalks.edited_by',array('name' => $edit_name)),
            'content'         => $this->modx->modxtalks->bbcode($this->object->content),
            'index'           => date('Ym',$this->object->time),
            'date'            => date($this->modx->modxtalks->config['mtDateFormat'],$this->object->time),
            'funny_date'      => $this->modx->modxtalks->date_format(array('date' => $this->object->time)),
            'funny_edit_date' => $this->modx->lexicon('modxtalks.date_now'),
            'link'            => '#comment-'.$this->object->idx,
            'id'              => (int) $this->object->id,
            'idx'             => (int) $this->object->idx,
            'user'            => $this->modx->modxtalks->userButtons($this->object->userId,$this->object->time),
            'userId'          => md5($this->object->userId.$email),
            'timeago'         => date('c',$this->object->time),
            'timeMarker'      => '',
            'user_info'       => '<div class="user_info"><span class="user_ip">IP: '.$this->object->ip.'</span><span class="user_email">Email: '.$email.'</span></div>',
        );

        return $data;
    }

}

return 'postUpdateProcessor';