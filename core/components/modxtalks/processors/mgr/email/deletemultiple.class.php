<?php

/**
 * Remove selected Email addresses from Block List
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksEmailBlockMultipleProcessor extends modObjectProcessor {
    public $classKey = 'modxTalksEmailBlock';
    public $languageTopics = array('modxtalks:default');

    public function process() {
        if (!$ids = $this->getProperty('ids', null)) {
            return $this->failure($this->modx->lexicon('modxtalks.post_err_ns_multiple'));
        }

        $ids = is_array($ids) ? $ids : explode(',', $ids);

        $addresses = $this->modx->removeCollection($this->classKey, array(
            'id:IN' => $ids
        ));

        if (!$addresses) {
            return $this->failure($this->modx->lexicon('modxtalks.post_err_ns_multiple'));
        }

        return $this->success();
    }
}

return 'modxTalksEmailBlockMultipleProcessor';
