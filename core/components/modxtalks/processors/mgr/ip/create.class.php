<?php
/**
 * Add IP address to Block List
 *
 * @package modxTalks
 * @subpackage processors
 */
class modxTalksIpBlockCreateProcessor extends modObjectCreateProcessor
{
    public $classKey = 'modxTalksIpBlock';
    public $languageTopics = array('modxtalks:default');
    public $objectType = 'modxtalks.ip';

    public function beforeSet() {
        $ip = $this->getProperty('ip');
        $intro = $this->getProperty('intro');

        if ($this->doesAlreadyExist(array('ip' => $ip))) {
            $this->addFieldError('ip', $this->modx->lexicon('modxtalks.ip_already_banned'));
        }
        elseif (!preg_match("@^[0-9\.*]{3,15}$@", $ip)) {
            $this->addFieldError('ip', $this->modx->lexicon('modxtalks.err_ip_adress'));
        }

        $this->properties = array(
            'ip'   => $ip,
            'date' => time(),
        );

        if (!empty($intro)) {
            $this->properties['intro'] = $intro;
        }

        return parent::beforeSet();
    }
}

return 'modxTalksIpBlockCreateProcessor';