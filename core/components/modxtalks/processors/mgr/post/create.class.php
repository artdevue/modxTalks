<?php

/**
 * Create Post
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalkCreateProcessor extends modObjectCreateProcessor {
    public $classKey = 'Post';
    public $languageTopics = array('modxtalks:default');
    // public $objectType = 'modxtalks.modxtalk';

    /*public function beforeSave() {
        $name = $this->getProperty('name');

        if (empty($name)) {
            $this->addFieldError('name',$this->modx->lexicon('modxtalks.modxtalk_err_ns_name'));
        } else if ($this->doesAlreadyExist(array('name' => $name))) {
            $this->addFieldError('name',$this->modx->lexicon('modxtalks.modxtalk_err_ae'));
        }
        return parent::beforeSave();
    }*/
}

return 'modxTalkCreateProcessor';
