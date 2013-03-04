<?php
/**
 * modxTalks Connector
 *
 * @package modxtalks
 */
/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/

require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

$corePath = $modx->getOption('modxtalks.core_path',null,$modx->getOption('core_path').'components/modxtalks/');
require_once $corePath.'model/modxtalks/modxtalks.class.php';
$modx->modxtalks = new modxTalks($modx);

$modx->lexicon->load('modxtalks:default');

/* handle request */
$path = $modx->getOption('processorsPath',$modx->modxtalks->config,$corePath.'processors/');
$modx->request->handleRequest(array(
    'processors_path' => $path,
    'location' => '',
));