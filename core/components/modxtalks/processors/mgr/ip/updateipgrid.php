<?php
/**
 * @package modxtalks
 * @subpackage processors
 */
/* parse JSON */
if (empty($scriptProperties['data'])) return $modx->error->failure($this->modx->lexicon('modxtalks.post_err_data'));
$_DATA = $modx->fromJSON($scriptProperties['data']);
if (!is_array($_DATA)) return $modx->error->failure($this->modx->lexicon('modxtalks.post_err_data'));

/* get obj */
if (empty($_DATA['id'])) return $modx->error->failure($modx->lexicon('modxtalks.post_err_ns'));
$ipblock = $modx->getObject('modxTalksIpBlock',$_DATA['id']);
if (empty($ipblock)) return $modx->error->failure($modx->lexicon('modxtalks.post_err_nf'));

$ipblock->fromArray($_DATA);

/* save */
if ($ipblock->save() == false) {
    return $modx->error->failure($modx->lexicon('modxtalks.post_err_save'));
}


return $modx->error->success('',$ipblock);