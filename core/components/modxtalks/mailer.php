<?php
// Подключаем API MODX'a
define('MODX_API_MODE', true);
require dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';

// Включаем обработку ошибок
$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('FILE');

/**
 * Initialize modxTalks
 */
$path = $modx->getOption('modxtalks.core_path', null, $modx->getOption('core_path') . 'components/modxtalks/');

$modx->modxtalks = $modx->getService('modxtalks', 'modxTalks', $modx->getOption($path, null, $modx->getOption('core_path') . 'components/modxtalks/') . 'model/modxtalks/', array());
if (!$modx->modxtalks instanceof modxTalks) {
    die('Error load class modxTalks!');
}

$c = $modx->newQuery('modxTalksMails');
$c->limit(10);
if (!$mails = $modx->getCollection('modxTalksMails', $c)) {
    $modx->log(xPDO::LOG_LEVEL_INFO, 'Task is empty');
    die;
}
foreach ($mails as $id => $mail) {
    if (!$comment = $modx->getObject('modxTalksPost', $mail->post_id)) {
        continue;
    }
    if (!$modx->modxtalks->notifyModerators($comment)) {
        $modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks::notifyModerators] Error send notify with comment ID ' . $mail->post_id);
        die;
    }
    $mail->remove();
}
