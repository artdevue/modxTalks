<?php
/**
 * @package modxtalk
 * @subpackage processors
 */
class modxTalkUpdateProcessor extends modObjectUpdateProcessor {
    public $classKey = 'Post';
    public $languageTopics = array('modxtalks:default');
    // public $objectType = 'modxtalks.post';
}
return 'modxTalkUpdateProcessor';