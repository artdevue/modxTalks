<?php
/**
 * Update blocked Email address
 *
 * @package modxTalks
 * @subpackage processors
 */
class modxTalksEmailBlockUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'modxTalksEmailBlock';
    public $languageTopics = array('modxtalks:default');
    public $objectType = 'modxtalks.email';

    public function initialize() {
        $data = $this->getProperty('data');
        if ($data = $this->modx->fromJSON($data)) {
            $this->properties = $data;
        }
        return parent::initialize();
    }

    public function beforeSave() {
        $this->properties = array(
            'email' => $this->getProperty('email'),
            'intro' => $this->getProperty('intro'),
        );
        return parent::beforeSave();
    }
}

return 'modxTalksEmailBlockUpdateProcessor';