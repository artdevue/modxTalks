<?php
/**
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksPostGetProcessor extends modObjectGetProcessor {
    public $classKey = 'modxTalksPost';
    public $objectType = 'modxtalks.post';
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

    public function process() {
        $canGet = $this->beforeOutput();
        if ($canGet !== true) {
            return $canGet;
        }
        return $this->cleanup();
    }

    public function beforeOutput() {
        $userId = $this->object->userId;
        /**
         * If users is moderator
         */
        if ($this->modx->modxtalks->isModerator()) {
            return true;
        }
        /**
         * Check user permission to edit comment
         */
        if (!$this->modx->user->isAuthenticated($this->context)) {
            return $this->failure($this->modx->lexicon('modxtalks.edit_permission'));
        }
        // Check comment owner
        if ($this->modx->user->id != $userId) {
            return $this->failure($this->modx->lexicon('modxtalks.edit_permission'));
        }
        // Check time for edit comment
        if ((time() - $this->object->time) > $this->modx->getOption('modxtalks.edit_time')) {
            return $this->failure($this->modx->lexicon('modxtalks.edit_timeout',array('seconds' => $this->modx->getOption('modxtalks.edit_time'))));
        }

        return true;
    }

    public function cleanup() {
        $tags = array('&#091;&#091;','&#093;&#093;');
        $rTags = array('[_[',']_]');

        $content = str_replace($rTags, $tags, $this->object->content);
        $output = array(
            'html' => $this->modx->modxtalks->_getEditForm($this->object->id,$content,$this->context),
        );

        return $this->success('',$output);
    }

}

return 'modxTalksPostGetProcessor';