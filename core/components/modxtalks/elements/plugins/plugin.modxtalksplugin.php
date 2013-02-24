<?php
/**
 * @var modX $modx
 * @var array $scriptProperties
 */
switch ($modx->event->name) {
    case 'OnSiteRefresh':
        $assetsUrl = $modx->getOption('modxtalks.assets_url',$config,$modx->getOption('assets_url').'components/modxtalks/');
        $ejsTemplatesPath = $_SERVER['DOCUMENT_ROOT'].$assetsUrl.'ejs/';

        if ($modx->cacheManager->deleteTree($ejsTemplatesPath, array(
            'deleteTop' => false,
            'skipDirs' => true,
            'extensions' => array('.ejs'),
        ))) {
            $modx->log(modX::LOG_LEVEL_INFO,'modxTalks clear ejs templates files . '.$modx->lexicon('refresh_success'));
        }

        if ($modx->cacheManager->refresh(array('/modxtalks'=> array()))) {
            $modx->log(modX::LOG_LEVEL_INFO,'modxTalks clear cache. '.$modx->lexicon('refresh_success'));
        }
    break;
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
    case 'OnWebPagePrerender':
        if($modx->mt_mtCount === true) {
            $corePath = $modx->getOption('modxtalks.core_path',null,$modx->getOption('core_path').'components/modxtalks/');
            require_once $corePath.'model/modxtalks/modxtalkscount.class.php';
            $counts = new modxTalksCount($modx);
            $counts->mtcount();
        }
        break;
    case 'OnModxTalksCommentAfterAdd':
        $comment =& $modx->event->params['modxtalks.post'];

        if (!$latest = $modx->getObject('modxTalksLatestPost',array('cid' => $comment->conversationId))) {
            $latest = $modx->newObject('modxTalksLatestPost');
            $latest->set('cid',$comment->conversationId);
        }

        $conversation = $comment->getConversation();
        $total = $conversation->getProperty('total');
        if (!$title = $comment->getResourceTitle($conversation)) $title = 'Не указан';

        $latest->fromArray(array(
            'pid'     => $comment->id,
            'idx'     => $comment->idx,
            'name'    => $comment->name,
            'email'   => $comment->email,
            'content' => $comment->processed_content,
            'time'    => $comment->time,
            'link'    => $comment->link,
            'userId'  => $comment->userId,
            'title'   => $title,
            'total'   => $total,
        ));
        $latest->save();
        break;
}