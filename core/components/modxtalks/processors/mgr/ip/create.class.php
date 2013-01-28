<?php
/**
 * @package modxTalks
 * @subpackage processors
 */
class modxTalksIpBlockCreateProcessor extends modObjectCreateProcessor {
    public $classKey = 'modxTalksIpBlock';
    public $languageTopics = array('modxtalks:default');
    public $objectType = 'modxtalks.ip';

    public function beforeSave() {
        $ip = $this->getProperty('ip');

        if ($this->doesAlreadyExist(array('ip' => $ip))) {
            $this->addFieldError('ip',$this->modx->lexicon('modxtalks.err_ae'));
        }
        elseif (!preg_match("@^[0-9\.*]{3,15}$@",$ip)) {
            $this->addFieldError('ip',$this->modx->lexicon('modxtalks.err_ip_adress'));
        }
        $this->object->set('date',time());
        return parent::beforeSave();
    }

}
return 'modxTalksIpBlockCreateProcessor';