<?php

/**
 * Create a comment
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalkPostCreateProcessor extends modObjectCreateProcessor
{
	public $classKey = 'modxTalksPost';
	public $languageTopics = ['modxtalks:default'];
}

return 'modxTalkPostCreateProcessor';
