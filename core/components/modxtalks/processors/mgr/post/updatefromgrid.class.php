<?php
require_once (dirname(__FILE__).'/update.class.php');
/**
 * @package modxtalk
 * @subpackage processors
 */
class modxTalkUpdateFromGridProcessor extends modxTalkUpdateProcessor {
    public function initialize() {
        $data = $this->getProperty('data');
        if (empty($data)) return $this->modx->lexicon('invalid_data');
        $data = $this->modx->fromJSON($data);
        if (empty($data)) return $this->modx->lexicon('invalid_data');
        $this->setProperties($data);
        $this->unsetProperty('data');

        return parent::initialize();
    }
}
return 'modxTalkUpdateFromGridProcessor';