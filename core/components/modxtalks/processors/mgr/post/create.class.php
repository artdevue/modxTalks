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
    public $objectType = 'modxtalks.post';
}

return 'modxTalkCreateProcessor';
