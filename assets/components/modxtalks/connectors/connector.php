<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);


header('Content-Type: application/json; charset=UTF-8');

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjax) die('Only XHR requests allowed!');

if (empty($_POST['action']) && empty($_SERVER['HTTP_ACTION'])) return;

if (!empty($_SERVER['HTTP_ACTION']))
    $action = $_SERVER['HTTP_ACTION'];
else
    $action = $_POST['action'];

switch($action) {
    case 'preview':
        $action = 'web/comment/create';
        $_POST['preview'] = 1;
        break;
    case     'add': $action = 'web/comment/create'; break;
    case    'like': $action = 'web/comment/like'; break;
    case   'quote': $action = 'web/comment/quote'; break;
    case  'delete': $action = 'web/comment/remove'; break;
    case 'restore': $action = 'web/comment/restore'; break;
    case    'edit': $action = 'web/comment/update'; break;
    case     'get': $action = 'web/comment/get'; break;
    case    'load': $action = 'web/comments/load'; break;
    default: return; break;
}

/**
 * Чистим контент комментаия от тегов MODX
 */
$tags = array('[[', ']]','<?','?>');
$rTags = array('[_[',']_]','&lt;?','?&gt;');
foreach ($_POST as $key => &$value) {
    if (!is_array($_POST[$key]))
        $_POST[$key] = str_replace($tags, $rTags, $value);
}

@session_cache_limiter('public');
define('MODX_REQP',false);

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

$requestCorePath = $modx->getOption('modxtalks.core_path',null,$modx->getOption('core_path').'components/modxtalks/');

$version = $modx->getVersionData();
if (version_compare($version['full_version'],'2.2.6-pl') >= 0) {
    if ($modx->user->hasSessionContext($modx->context->get('key'))) {
        $_SERVER['HTTP_MODAUTH'] = $_SESSION["modx.{$modx->context->get('key')}.user.token"];
    } else {
        $_SESSION["modx.{$modx->context->get('key')}.user.token"] = 0;
        $_SERVER['HTTP_MODAUTH'] = 0;
    }
} else {
    $_SERVER['HTTP_MODAUTH'] = $modx->site_id;
}
$_REQUEST['HTTP_MODAUTH'] = $_SERVER['HTTP_MODAUTH'];

/*require_once MODX_CORE_PATH.'components/modxtalks/model/modxtalks/modxtalks.class.php';
$modx->modxtalks = new modxTalks($modx);*/

/**
 * Подключаем основной класс modxTalks
 */
$modx->modxtalks = $modx->getService('modxtalks','modxTalks',$modx->getOption($requestCorePath,null,$modx->getOption('core_path').'components/modxtalks/').'model/modxtalks/',array());
if (!($modx->modxtalks instanceof modxTalks)) return '';

/**
 * Страшный метод проверки IP по черному списку в базе
 * Нужно обязательно переписать
 */
$allowedActions = array('web/comments/load');
if (!in_array($action,$allowedActions)) {
    $ip = $modx->modxtalks->get_client_ip();
    $ip = explode('.',$ip);
    $ipArr = array(
        $ip[0].'.',
        $ip[0].'.'.$ip[1].'.',
        $ip[0].'.'.$ip[1].'.'.$ip[2].'.',
        $ip[0].'.'.$ip[1].'.'.$ip[2].'.'.$ip[3]
    );
    if ($modx->getCount('modxTalksIpBlock',array('ip:IN' => $ipArr))) {
        echo $modx->toJSON(array(
            'message' => 'Ваш IP адрес находится в черном списке! Если это ошибка свяжитеь с администрацией сайта!',
            'success' => false,
        ));
        die;
    }
}

/* initiate the request. */
$path = $requestCorePath.'processors/';
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
    'action' => $action
));