<?php
/**
 * Remove selected Email addresses
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksEmailBlockMultipleProcessor extends modObjectProcessor {
    public $classKey = 'modxTalksEmailBlock';
    public $languageTopics = array('modxtalks:default');

    public function process() {
        if (!$ids = $this->getProperty('ids',null)) {
            return $this->failure($this->modx->lexicon('modxtalks.post_err_ns_multiple'));
        }

        $ids = is_array($ids) ? $ids : explode(',',$ids);

        if (!$addresses = $this->modx->removeCollection($this->classKey,array('id:IN' => $ids))) {
            return $this->failure($this->modx->lexicon('modxtalks.post_err_ns_multiple'));
        }

        return $this->success();
    }
}
return 'modxTalksEmailBlockMultipleProcessor';