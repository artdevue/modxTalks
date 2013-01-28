<?php
/**
 * @var modX $modx
 * @var array $scriptProperties
 */
switch ($modx->event->name) {
    case 'OnUserFormSave':
        $user =& $modx->event->params['user'];
        if (is_object($user)) {
            $modx->cacheManager->delete($user->id, array(xPDO::OPT_CACHE_KEY => 'modxtalks/users'));
        }
    break;
    case 'OnManagerPageInit':
        $mtAccets = $modx->getOption('modxtalks.assets_url',null,$modx->getOption('assets_url').'components/modxtalks/');
        $modx->regClientStartupScript($mtAccets.'js/mgr/comments/mrg.js');
        $modx->regClientCSS($mtAccets.'css/mgr/mt.css');
        break;
    case 'OnDocFormPrerender':
        $cssFile = $modx->getOption('modxtalks.assets_url',null,$modx->getOption('assets_url').'components/modxtalks/').'css/mgr/comments.css';
        $modx->regClientCSS($cssFile);
        break;
    case 'OnPageNotFound':
        // Check whether active friendly_urls, if not, then the interrupt
        if ($modx->getOption('friendly_urls') != 1) break;
        
        $corePath = $modx->getOption('modxtalks.core_path',null,$modx->getOption('core_path').'components/modxtalks/');
        require_once $corePath.'model/modxtalks/modxtalksrouter.class.php';
        $routermt = new modxTalksRouter($modx);
        $routermt->route();    
        break;
}