<?php
/**
 * @package modxtalks
 */
if (!isset($modx->modxtalks) || !$modx->modxtalks instanceof modxTalks) {
    $modx->modxtalks = $modx->getService('modxtalks', 'modxTalks', $modx->getOption('modxtalks.core_path', null, $modx->getOption('core_path') . 'components/modxtalks/') . 'model/modxtalks/', $scriptProperties);
}

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json; charset=UTF-8');
    if (!empty($_SERVER['HTTP_ACTION'])) {
        $action = $_SERVER['HTTP_ACTION'];
    } else {
        $action = $_POST['action'];
    }

    switch ($action) {
        case 'preview':
            $action = 'web/comment/create';
            $_POST['preview'] = 1;
            break;
        case        'add':
            $action = 'web/comment/create';
            break;
        case       'like':
            $action = 'web/comment/like';
            break;
        case      'quote':
            $action = 'web/comment/quote';
            break;
        case     'delete':
            $action = 'web/comment/remove';
            break;
        case    'restore':
            $action = 'web/comment/restore';
            break;
        case       'edit':
            $action = 'web/comment/update';
            break;
        case        'get':
            $action = 'web/comment/get';
            break;
        case       'vote':
            $action = 'web/comment/vote';
            break;
        case     'unvote':
            $action = 'web/comment/unvote';
            break;
        case 'votes_info':
            $action = 'web/comment/votes_info';
            break;
        case  'ban_email':
            $action = 'web/comment/block_email';
            break;
        case     'ban_ip':
            $action = 'web/comment/block_ip';
            break;
        case       'load':
            $action = 'web/comments/load';
            break;
        case     'latest':
            $_POST['action'] = $action;
            $action = 'web/comments/latest';
            break;
        default:
            return;
            break;
    }

    /**
     * Чистим контент комментаия от тегов MODX
     */
    $tags = array('[[', ']]', '<?', '?>');
    $rTags = array('[_[', ']_]', '&lt;?', '?&gt;');
    foreach ($_POST as $key => &$value) {
        if (!is_array($_POST[$key]))
            $_POST[$key] = str_replace($tags, $rTags, $value);
    }

    $path = $modx->getOption('modxtalks.core_path', null, $modx->getOption('core_path') . 'components/modxtalks/') . 'processors/';

    $modx->modxtalks->ajaxInit();
    $config = array_merge($_POST, $modx->modxtalks->config);
    $response = $modx->runProcessor($action, $config, array(
        'processors_path' => $path
    ));

    if ($response->isError()) {
        $output = json_encode($response->response['message']);
    }
    if (is_array($response->response)) {
        $output = json_encode($response->response, true);
    } else {
        $output = $response->response;
    }
    echo $output;
    die;
}

$comments = $modx->modxtalks->init();

return $comments;
