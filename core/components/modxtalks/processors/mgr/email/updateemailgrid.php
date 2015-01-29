<?php
/**
 * Update blocked Email address from grid
 *
 * @package modxtalks
 * @subpackage processors
 */

if (empty($scriptProperties['data'])) {
    return $modx->error->failure($this->modx->lexicon('modxtalks.post_err_data'));
}

$_DATA = $modx->fromJSON($scriptProperties['data']);

if (!is_array($_DATA)) {
    return $modx->error->failure($this->modx->lexicon('modxtalks.post_err_data'));
}

if (empty($_DATA['id'])) {
    return $modx->error->failure($modx->lexicon('modxtalks.post_err_ns'));
}

if (!$email = $modx->getObject('modxTalksEmailBlock', $_DATA['id'])) {
    return $modx->error->failure($modx->lexicon('modxtalks.post_err_nf'));
}

$email->fromArray($_DATA);

if ($email->save() === false) {
    return $modx->error->failure($modx->lexicon('modxtalks.post_err_save'));
}

return $modx->error->success('', $email);
