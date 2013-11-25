<?php
header('Content-Type: application/json; charset=UTF-8');


error_reporting(E_ALL);
ini_set("display_errors", 1);


$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjax) return;

if (empty($_POST['action']) && empty($_SERVER['HTTP_ACTION'])) return;

if (!empty($_SERVER['HTTP_ACTION'])) {
    $action = $_SERVER['HTTP_ACTION'];
}
else {
    $action = $_POST['action'];
}

switch($action) {
    case 'preview':
        $proc = 'web/comment/create';
        $_POST['preview'] = 1;
        break;
    case        'add': $proc = 'web/comment/create'; break;
    case       'like': $proc = 'web/comment/like'; break;
    case      'quote': $proc = 'web/comment/quote'; break;
    case     'delete': $proc = 'web/comment/remove'; break;
    case    'restore': $proc = 'web/comment/restore'; break;
    case       'edit': $proc = 'web/comment/update'; break;
    case        'get': $proc = 'web/comment/get'; break;
    case       'vote': $proc = 'web/comment/vote'; break;
    case 'votes_info': $proc = 'web/comment/votes_info'; break;
    case  'ban_email': $proc = 'web/comment/block_email'; break;
    case     'ban_ip': $proc = 'web/comment/block_ip'; break;
    case       'load': $proc = 'web/comments/load'; break;
    case     'latest':
        $_POST['action'] = $action;
        $proc = 'web/comments/latest';
        break;
    default: return; break;
}

/**
 * Convert MODX tags
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

$config = array();
if (!in_array($action, array('latest','vote','votes_info','ban_ip','ban_email'))) {
    if (!$config = $modx->cacheManager->get(md5('modxtalks::'.strval($_POST['conversation'])), array(xPDO::OPT_CACHE_KEY => 'modxtalks/properties', xPDO::OPT_CACHE_HANDLER => 'xPDOFileCache'))) {
        echo '{"message":"Ошибка","success":false}';
        return;
    }
}

/**
 * Initialize MODXTalks
 */
$modx->modxtalks = $modx->getService('modxtalks','modxTalks',$modx->getOption($requestCorePath,null,$modx->getOption('core_path').'components/modxtalks/').'model/modxtalks/',$config);
if (!($modx->modxtalks instanceof modxTalks)) return;

/**
 * Check IP
 */
$checkIp = $modx->modxtalks->checkIp($action, array('load','latest'));
if ($checkIp !== true) {
    echo $checkIp; return;
}

/**
 * Initiate the request
 */
$path = $requestCorePath.'processors/';
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
    'action' => $proc
));