<?php
/**
 * Get list of blocked IP addresses
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksIpBlockGetProcessor extends modObjectGetProcessor {
    public $classKey = 'modxTalksIpBlock';
    public $languageTopics = array('modxtalks:default');
}
return 'modxTalksIpBlockGetProcessor';