<?php
/**
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksPostCreateProcessor extends modObjectCreateProcessor {
    public $classKey = 'modxTalksPost';
    public $languageTopics = array('modxtalks:default');
    public $objectType = 'modxtalks.post';
    public $afterSaveEvent = 'OnModxTalksCommentAfterAdd';
    public $beforeSaveEvent = 'OnModxTalksCommentBeforeAdd';
    public $context = '';
    public $preModarateComments;
    public $preview;
    public $timeout = 60;
    public $hash = '';
    protected $theme;
    protected $defaultProprties = array(
        'total' => 0,
        'deleted' => 0,
        'unconfirmed' => 0,
    );

    /**
     * Process the Comment create processor
     * {@inheritDoc}
     * @return mixed
     */
    public function process() {
        /* Run the beforeSet method before setting the fields, and allow stoppage */
        $canSave = $this->beforeSet();
        if ($canSave !== true) {
            return $this->failure($canSave);
        }

        $this->object->fromArray($this->getProperties());

        /* if Comment premodarate return custom message before save comment */
        if ($this->preModarateComments && !$this->preview && !$this->modx->modxtalks->isModerator()) {
            $data = array(
                'success' => false,
                'message' => $this->modx->lexicon('modxtalks.comment_premoderate'),
                'premoderated' => true,
            );
            return $this->modx->toJSON($data);
        }
        /* if Comment preview return custom message before save comment */
        elseif ($this->preview) {
            $data = $this->_preparePostData();
            $data['hideAvatar'] = '';
            return $this->success($data);
        }

        /* run object validation */
        if (!$this->object->validate()) {
            /** @var modValidator $validator */
            $validator = $this->object->getValidator();
            if ($validator->hasMessages()) {
                foreach ($validator->getMessages() as $message) {
                    $this->addFieldError($message['field'],$this->modx->lexicon($message['message']));
                }
            }
        }

        $preventSave = $this->fireBeforeSaveEvent();
        if (!empty($preventSave)) {
            return $this->failure($preventSave);
        }

        /* save element */
        if ($this->object->save() === false) {
            $this->modx->error->checkValidation($this->object);
            return $this->failure($this->modx->lexicon($this->objectType.'_err_save'));
        }

        $this->afterSave();

        $this->fireAfterSaveEvent();
        $this->logManagerAction();
        return $this->cleanup();
    }

    /**
     * Override in your derivative class to do functionality before the fields are set on the object
     * @return boolean
     */
    public function beforeSet() {
        $this->preModarateComments = (boolean) $this->modx->getOption('modxtalks.preModarateComments',null,false);
        $idx = 0;
        $time = time();
        $this->userId = 0;
        $this->ip = $this->modx->modxtalks->get_client_ip();
        $this->email = trim($this->getProperty('email'));
        $this->name = trim($this->getProperty('name'));
        $conversationId = 0;
        $conversation = trim($this->getProperty('conversation'));
        $content = trim($this->getProperty('content'));
        $this->context = trim($this->getProperty('ctx'));
        $this->preview = $this->getProperty('preview');
        $this->timeout = $this->modx->modxtalks->config['add_timeout'];
        if ($slug = $this->getProperty('slug')) {
            $this->modx->modxtalks->config['slug'] = $slug;
        }

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
        // Check Conversation name
        if (empty($conversation)) {
            $this->addFieldError('conversation',$this->modx->lexicon('modxtalks.id_not_defined'));
        }
        elseif (!is_string($conversation)) {
            $this->addFieldError('conversation',$this->modx->lexicon('modxtalks.bad_id'));
        }
        elseif (preg_match('@[^a-zA-z-_.0-9]@i', $conversation)) {
            $this->addFieldError('conversation',$this->modx->lexicon('modxtalks.unallowed_symbols'));
        }
        elseif (strlen($conversation) < 2 || strlen($conversation) > 63) {
            $this->addFieldError('conversation',$this->modx->lexicon('modxtalks.bad_id'));
        }
        elseif (!$this->theme = $this->modx->getObject('modxTalksConversation', array('conversation' => $conversation))) {
            $this->failure($this->modx->lexicon('modxtalks.empty_conversation'));
            return false;
        }

        // Check Comment Content
        if (empty($content)) {
            $this->addFieldError('content',$this->modx->lexicon('modxtalks.empty_content'));
        }
        elseif (!is_string($content)) {
            $this->addFieldError('content',$this->modx->lexicon('modxtalks.bad_content'));
        }
        elseif (mb_strlen($content,'UTF-8') < 2) {
            $this->addFieldError('content',$this->modx->lexicon('modxtalks.bad_content_length',array('length' => 2)));
        }

        $_SESSION['comment_time'] = !empty($_SESSION['comment_time']) ? $_SESSION['comment_time'] : 0;

        // Check user Email
        if ($this->modx->user->isAuthenticated($this->context) || $this->modx->modxtalks->isModerator()) {
            $this->userId = $this->modx->user->get('id');
            $this->email = $this->modx->user->Profile->email;
            if (!$this->name = $this->modx->user->Profile->fullname) {
                $this->name = $this->modx->user->username;
            }
        }
        else {
            if (empty($this->email)) {
                $this->addFieldError('email',$this->modx->lexicon('modxtalks.empty_email'));
            }
            elseif (!is_string($this->email) || !$this->object->validateEmail($this->email)) {
                $this->addFieldError('email',$this->modx->lexicon('modxtalks.bad_email'));
            }
            if (!$this->hasErrors() && $this->modx->getCount('modUserProfile',array('email' => $this->email))) {
                $this->failure($this->modx->lexicon('modxtalks.user_exists'));
                return false;
            }
            // Check user name
            if (empty($this->name)) {
                $this->addFieldError('name',$this->modx->lexicon('modxtalks.empty_name'));
            }
            elseif (mb_strlen($this->name,'UTF-8') < 2) {
                $this->addFieldError('name',$this->modx->lexicon('modxtalks.bad_name_length',array('length' => 2)));
            }
        }

        /**
         * Check if user email is banned
         */
        if ($this->modx->getCount('modxTalksEmailBlock',array('email' => $this->email))) {
            $this->failure($this->modx->lexicon('modxtalks.email_banned'));
            return false;
        }

        if (!$this->hasErrors() && !$this->preview) {
            $conversationId = $this->theme->get('id');
            if (!$this->theme->getProperties('comments')) {
                $this->theme->setProperties($this->defaultProprties,'comments',false);
                $this->theme->save();
            }

            $this->hash = md5($content.$this->email.$conversationId);

            // Premoderate comment
            if ($this->preModarateComments === true && !$this->modx->modxtalks->isModerator()) {
                // Check time before for add another comment
                if ((time() - $_SESSION['comment_time']) < $this->timeout) {
                    $seconds = $this->timeout - (time() - $_SESSION['comment_time']);
                    $this->failure($this->modx->lexicon('modxtalks.add_comment_waiting',array('seconds' => $seconds)));
                    return false;
                }
                if (!$this->hasErrors()) {
                    $unconfirmed = $this->theme->getProperty('unconfirmed','comments',0);
                    $this->theme->setProperty('unconfirmed',++$unconfirmed,'comments');
                    $this->theme->save();

                    $params = array(
                        'conversationId' => $conversationId,
                        'hash'           => $this->hash,
                        'time'           => $time,
                        'content'        => $content,
                        'ip'             => $this->ip,
                    );
                    if ($this->modx->user->isAuthenticated($this->context)) {
                        $params['userId'] = $this->userId;
                    }
                    else {
                        $params['useremail'] = $this->email;
                        $params['username']  = $this->name;
                    }
                    $comment = $this->modx->newObject('modxTalksTempPost',$params);
                    if (!$comment->save()) {
                        $this->failure($this->modx->lexicon('modxtalks.error_try_again'));
                        return false;
                    }

                    /**
                     * Отправляем уведомления о подтверждении комментария
                     * модераторам темы
                     */
                    if (!$this->modx->modxtalks->notifyModerators($comment)) {
                        $this->failure($this->modx->lexicon('modxtalks.error_try_again'));
                        return false;
                    }

                    $_SESSION['comment_time'] = time();
                }


                return parent::beforeSet();
            }

            $q = $this->modx->newQuery($this->classKey);
            $q->where(array('conversationId' => $conversationId));
            $q->sortby('idx','DESC');
            $q->limit(1);
            $idx = 1;
            if ($lastComment = $this->modx->getObject($this->classKey,$q)) {
                $idx = $lastComment->idx + 1;
            }

            // Check for comment double
            if ((time() - $_SESSION['comment_time']) < $this->timeout && !$this->modx->modxtalks->isModerator()) {
                $post = $this->modx->getObject($this->classKey,array('hash' => $this->hash, 'conversationId' => $conversationId, 'time' => $_SESSION['comment_time']));
                $seconds = $this->timeout - (time() - $_SESSION['comment_time']);
                if ($post && $seconds !== 0) {
                    $this->failure($this->modx->lexicon('modxtalks.resend_comment_waiting',array('seconds' => $seconds)));
                    return false;
                } else {
                    $this->failure($this->modx->lexicon('modxtalks.add_comment_waiting',array('seconds' => $seconds)));
                    return false;
                }
            }
            $total = $this->theme->getProperty('total','comments',0);
            $this->theme->setProperty('total',++$total,'comments');
            $this->theme->save();
        }

        $this->properties = array(
            'idx'            => $idx,
            'conversationId' => $conversationId,
            'userId'         => $this->userId,
            'time'           => $time,
            'date'           => strftime('%Y%m', $time),
            'hash'           => $this->hash,
            'content'        => $content,
            'username'       => NULL,
            'useremail'      => NULL,
            'ip'             => $this->ip,
        );
        if ($this->userId === 0) {
            $this->properties['username']  = $this->name;
            $this->properties['useremail'] = $this->email;
        }

        return parent::beforeSet();
    }

    public function afterSave() {
        $_SESSION['comment_time'] = time();

        /**
         * Обновляем кэш комментария и темы
         */
        if ($this->modx->modxtalks->mtCache === true) {
            if (!$this->modx->modxtalks->cacheComment($this->object)) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR,'[modxTalks web/comment/create] Error cache the comment with ID '.$this->object->id);
            }
            if (!$this->modx->modxtalks->cacheConversation($this->theme)) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR,'[modxTalks web/comment/create] Error cache the conversation with ID '.$this->theme->id);
            }
        }

        /**
         * Отправляем уведомления о добавлении нового комментария
         * модераторам темы
         */
        if (!$this->modx->modxtalks->notifyModerators($this->object)) {
            $this->failure($this->modx->lexicon('modxtalks.error_try_again'));
            return false;
        }

        return parent::afterSave();
    }

    /**
     * Override cleanup to send only back needed params
     * @return array
     */
    public function cleanup() {
        $data = $this->_preparePostData();
        $data['hideAvatar'] = '';
        return $this->success('',$data);
    }

    /**
     * Override cleanup to send only back needed params
     * @return array $data
     */
    private function _preparePostData() {
        $data = array(
            'avatar'          => $this->modx->modxtalks->getAvatar($this->email),
            'hideAvatar'      => ' style="display: none;"',
            'name'            => $this->name,
            'content'         => $this->modx->modxtalks->bbcode($this->object->content),
            'index'           => date('Ym',$this->object->time),
            'date'            => date($this->modx->modxtalks->config['mtDateFormat'],$this->object->time),
            'funny_date'      => $this->modx->lexicon('modxtalks.date_now'),
            'link'            => $this->modx->modxtalks->getLink($this->object->idx),
            'id'              => (int) $this->object->id,
            'idx'             => (int) $this->object->idx,
            'user'            => $this->modx->modxtalks->userButtons($this->userId,$this->object->time),
            'userId'          => md5($this->userId.$this->email),
            'timeago'         => date('c',$this->object->time),
            'timeMarker'      => '',
            'funny_edit_date' => '',
            'edit_name'       => '',
            'user_info'       => '',
        );

        return $data;
    }

}

return 'modxTalksPostCreateProcessor';