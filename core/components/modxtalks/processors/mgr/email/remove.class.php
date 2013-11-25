<?php
/**
 * Remove blocked Email address
 *
 * @package modxTalks
 * @subpackage processors
 */
class modxTalksEmailBlockRemoveProcessor extends modObjectRemoveProcessor
{
    public $classKey = 'modxTalksEmailBlock';
    public $languageTopics = array('modxtalks:default');

}

return 'modxTalksEmailBlockRemoveProcessor';