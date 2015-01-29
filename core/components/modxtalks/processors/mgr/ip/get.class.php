<?php

/**
 * Get blocked IP address
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksIpBlockGetProcessor extends modObjectGetProcessor {
    public $classKey = 'modxTalksIpBlock';
    public $languageTopics = array('modxtalks:default');
}

return 'modxTalksIpBlockGetProcessor';
