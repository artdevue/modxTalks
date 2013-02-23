<?php
/**
 * @package modxtalks
 */
if (!isset($modx->modxtalks) || !($modx->modxtalks instanceof modxTalks)) {
    $modx->modxtalks = $modx->getService('modxtalks','modxTalks',$modx->getOption('modxtalks.core_path',null,$modx->getOption('core_path').'components/modxtalks/').'model/modxtalks/',$scriptProperties);
}

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
echo 'keks';die;
}
$comments = $modx->modxtalks->init();

return $comments;