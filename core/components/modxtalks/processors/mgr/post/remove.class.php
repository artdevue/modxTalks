<?php
/**
 * Remove Post
 *
 * @package modxtalk
 * @subpackage processors
 */
class modxTalkRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'Post';
    public $languageTopics = array('modxtalks:default');
    // public $objectType = 'modxtalks.modxtalk';
}

return 'modxTalkRemoveProcessor';