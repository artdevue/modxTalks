<?php

/**
 * Remove blocked IP address
 *
 * @package modxTalks
 * @subpackage processors
 */
class modxTalksIpBlockRemoveProcessor extends modObjectRemoveProcessor
{
	public $classKey = 'modxTalksIpBlock';
	public $languageTopics = ['modxtalks:default'];
}

return 'modxTalksIpBlockRemoveProcessor';
