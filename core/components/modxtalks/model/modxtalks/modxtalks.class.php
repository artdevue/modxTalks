<?php

require_once dirname(dirname(dirname(__FILE__))) . '/helpers.php';

/**
 * This file is part of modxTalks, a simple commenting component for MODx Revolution.
 *
 * @copyright Copyright (C) 2013-2015, Artdevue Ltd, <info@artdevue.com>
 * @author    Valentin Rasulov <info@artdevue.com> && Ivan Brezhnev <brezhnev.ivan@yahoo.com>
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @package   modxtalks
 */
class modxTalks {
    /**
     * @var modX A reference to the modX object.
     */
    public $modx = null;
    /**
     * @var array An array of configuration options
     */
    public $config = array();
    /**
     * @var array An array of snippet config
     */
    public $scriptProperties = array();
    /**
     * @var array An array of chunks
     */
    public $chunks = array();
    /**
     * @var string Context key
     */
    private $context;
    /**
     * @var boolean Enable or Disable modxTalks Comments cache
     */
    public $mtCache = true;
    /**
     * @var array An array of other configuration options
     */
    private $mtConfig = array();
    /**
     * @var boolean True if FURLs Enabled
     */
    private $friendly_urls;
    /**
     * @var string Conversation Id
     */
    private $conversationId;
    /**
     * @var string Culture key
     */
    private $lang;
    /**
     * @var boolean Revers comments, newest on the top
     */
    private $revers;
    /**
     * @var boolean Display or not Scrubber
     */
    private $scrubber = true;
    /**
     * @var boolean Display or not Scrubber
     */
    private $debug = false;

    /**
     * Constructs the modxTalks object
     *
     * @param modX  &$modx A reference to the modX object
     * @param array $config An array of configuration options
     */
    function __construct(modX & $modx, array $config = array()) {
        $this->modx =& $modx;

        $basePath = $this->modx->getOption('modxtalks.core_path', $config, $this->modx->getOption('core_path') . 'components/modxtalks/');
        $assetsUrl = $this->modx->getOption('modxtalks.assets_url', $config, $this->modx->getOption('assets_url') . 'components/modxtalks/');
        $siteUrl = $this->modx->getOption('server_protocol') . '://' . $this->modx->getOption('http_host');

        $this->config = array(
            'basePath' => $basePath,
            'corePath' => $basePath,
            'modelPath' => $basePath . 'model/',
            'processorsPath' => $basePath . 'processors/',
            'templatesPath' => $basePath . 'templates/',
            'ejsTemplatesPath' => $_SERVER['DOCUMENT_ROOT'] . $assetsUrl . 'ejs/',
            'chunksPath' => $basePath . 'elements/chunks/',
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
            'assetsUrl' => $assetsUrl,
            'imgUrl' => $assetsUrl . 'img/web/',
            'connectorUrl' => $assetsUrl . 'connector.php',
            'ajaxConnectorUrl' => $assetsUrl . 'connectors/connector.php',

            'moderator' => $this->modx->getOption('modxtalks.moderator', null, 'Administrator'),
            'onlyAuthUsers' => (bool) $this->modx->getOption('modxtalks.onlyAuthUsers', null, false),
            'voting' => (bool) $this->modx->getOption('modxtalks.voting', null, false),
            'gravatar' => (bool) $this->modx->getOption('modxtalks.gravatar', null, true),
            'gravatarSize' => (int) $this->modx->getOption('modxtalks.gravatarSize', null, 64),
            'gravatarUrl' => 'http://www.gravatar.com/avatar/',
            'defaultAvatar' => $siteUrl . $assetsUrl . 'img/web/avatar.png',

            'dateFormat' => $this->modx->getOption('modxtalks.dateFormat', null, 'j-m-Y, G:i'),
            'startFrom' => 0,
            'lastRead' => 0,
            'commentsPerPage' => (int) $this->modx->getOption('modxtalks.commentsPerPage', null, 20),
            'commentsLatestLimit' => (int) $this->modx->getOption('modxtalks.commentsLatestLimit', null, 20),
            'add_timeout' => (int) $this->modx->getOption('modxtalks.add_timeout', null, 60),
            'edit_time' => (int) $this->modx->getOption('modxtalks.edit_time', null, 180),
            'lates_comments_update' => (int) $this->modx->getOption('modxtalks.lates_comments_update', null, 60),
            'commentsClosed' => (bool) $this->modx->getOption('modxtalks.commentsClosed', null, false),

            // Templates
            'commentTpl' => 'comment',
            'commentLatestTpl' => 'comment_latest',
            'commentsLatestOutTpl' => 'comments_latest_out',
            'deletedCommentTpl' => 'deleted_comment',
            'commentAddFormTpl' => 'commentform',
            'commentEditFormTpl' => 'edit_commentform',
            'commentAuthTpl' => $this->modx->getOption('modxtalks.commentAuthTpl', null, 'comment_auth_tpl'),
            'user_info' => 'user_info',
            'votes_info' => 'votes_info',
            'useChunks' => (bool) $this->modx->getOption('modxtalks.useChunks', null, false),

            // RSS Templates
            'rssItemTpl' => 'tpl_rss_item',
            'rssFeedTpl' => 'tpl_rss_feed',

            // BBCode options
            'bbcode' => (bool) $this->modx->getOption('modxtalks.bbcode', null, true),
            'editOptionsControls' => $this->modx->getOption('modxtalks.editOptionsControls', null, 'fixed,image,link,strike,header,italic,bold,video,quote'),
            'smileys' => (bool) $this->modx->getOption('modxtalks.smileys', null, true),
            'detectUrls' => (bool) $this->modx->getOption('modxtalks.detectUrls', null, true),
            'videoSize' => array(500, 350),
            'commentLength' => (int) $this->modx->getOption('modxtalks.commentLength', null, 2000),

            'slug' => '',
            'slugReplace' => '$$$',

            // Debug
            'debug' => (bool) $this->modx->getOption('modxtalks.debug', null, false),

            // Scrubber position
            'scrubber' => (bool) $this->modx->getOption('modxtalks.scrubber', null, false),
            'scrubberTop' => (bool) $this->modx->getOption('modxtalks.scrubberTop', null, false),
            'scrubberOffsetTop' => (int) $this->modx->getOption('modxtalks.scrubberOffsetTop', null, 0),

            'fullDeleteComment' => (bool) $this->modx->getOption('modxtalks.fullDeleteComment', null, false),

            'revers' => (bool) $this->modx->getOption('modxtalks.revers', null, true),
        );

        $this->scriptProperties =& $config;
        $this->config = array_merge($this->config, $config);

        // if videoSize not array
        if (!is_array($this->config['videoSize']) && strpos($this->config['videoSize'], ',')) {
            $this->config['videoSize'] = explode(',', $this->config['videoSize']);
        }

        if (isset($config['commentsPerPage']) && intval($config['commentsPerPage'])) {
            $config['commentsPerPage'] = (int) $config['commentsPerPage'];
        }

        $this->friendly_urls = (bool) $this->modx->getOption('friendly_urls');

        $this->revers = $this->config['revers'];
        $this->scrubber = $this->config['scrubber'];

        $this->debug = $this->config['debug'] && $this->modx->getOption('log_target') === 'HTML';

        $this->context = !isset($this->config['context']) ?
            $this->context = $this->modx->context->key :
            $this->config['context'];

        $this->modx->addPackage('modxtalks', $this->config['modelPath']);
        $this->lang = $this->modx->getOption('cultureKey');
        $this->modx->lexicon->load('modxtalks:default');
    }

    /**
     * Initializes modxTalks based on a specific context
     *
     * @param string $ctx The context to initialize in
     *
     * @return bool
     */
    public function initialize($ctx = 'web') {
        switch ($ctx) {
            case 'mgr':
                if (!$this->modx->loadClass('modxtalksControllerRequest', $this->config['modelPath'] . 'modxtalks/request/', true, true)) {
                    return 'Could not load controller request handler.';
                }
                $this->request = new modxtalksControllerRequest($this);

                return $this->request->handleRequest();
                break;
        }

        return true;
    }

    /**
     * Initialize the component in frontend
     *
     * @param int    $limit Commets per request
     * @param string $id Optional
     *
     * @return string HTML
     */
    public function init($limit = 0, $id = '') {
        if ($this->debug) {
            $time = microtime(true);
            $startMemory = memory_get_usage() / pow(1024, 2);
            dump(sprintf('Memory Before: %2.2f Mbytes', $startMemory));
            dump("Your IP: {$this->get_client_ip()}");
        }

        if (empty($this->config['conversation'])) {
            $this->config['conversation'] = $this->modx->resource->class_key . '-' . $this->modx->resource->id;
        }

        $conversation = $this->validateConversation($this->config['conversation']);
        if ($conversation !== true) {
            return $conversation;
        }

        if (isset($_REQUEST['rss']) && $_REQUEST['rss'] == true) {
            header('Content-Type: application/rss+xml; charset=utf-8');
            $rssFeed = $this->createRssFeed($this->config['conversation'], $limit);
            die($rssFeed);
        }

        if (isset($_REQUEST['comment'])) {
            $id = (string) $_REQUEST['comment'];
        }

        $limit = (int) $limit;
        if (!$limit)
            $limit = $this->config['commentsPerPage'];

        $comments = $this->getComments($this->config['conversation'], $limit, $id);

        $this->regStyles();

        /**
         * Enable Ajax
         */
        if ($this->modx->getOption('modxtalks.ajax', null, true)) {
            $this->regScripts();
            $this->getScriptHead();
            $this->ejsTemplates();
        }

        $scrubberClass = '';
        if ($this->scrubber) {
            $scrubberClass = ' class="mt_hasScrubber' . ($this->config['scrubberTop'] || $this->revers ? ' mt_scrubber-top' : '') . '"';
        }

        $output = '<div id="mt_conversationBody"' . $scrubberClass . '><div class="mt_mthead"></div><div>';

        if ($this->revers) {
            if ($this->scrubber)
                $output .= $this->parseTpl('scrubber_rev', $this->getScrubber(), true);
            $output .= $this->getForm();
        } else {
            if ($this->scrubber)
                $output .= $this->parseTpl('scrubber', $this->getScrubber(), true);
        }

        $output .= '<div id="mt_conversationPosts" class="mt_postList" start="' . $this->config['startFrom'] . '">';
        $output .= $comments;
        $output .= '</div>';
        if (!$this->revers) {
            $output .= $this->getForm();
        }
        $output .= '</div></div>';

        $this->cacheProperties($this->config['conversation'], $this->scriptProperties);

        if ($this->debug) {
            dump(sprintf('Time: %2.2f мс', (microtime(true) - $time) * 1000));
            dump(sprintf('Memory After: %2.2f Mbytes', memory_get_usage() / pow(1024, 2)));
        }

        // Conclusion placeholder count_talks. The total number of votes
        $this->modx->setPlaceholder('count_talks', $this->config['commentsCount']);

        return $output;
    }

    /**
     * @param string|array $buttons
     * @param string       $id
     *
     * @return string
     */
    public function generateButtons($buttons = array()) {
        if (!is_array($buttons)) {
            $buttons = explode(',', $buttons);
        }

        $result = '';
        foreach ($buttons as $btn) {
            $result .= '<a href="#" onclick="BBCode.' . $btn . '(this);return false" title="' . $this->modx->lexicon('modxtalks.' . $btn) . '" class="mt_icon mt_icon-' . $btn . '"><span>' . $this->modx->lexicon('modxtalks.' . $btn) . '</span></a>' . "\n";
        }

        return $result;
    }

    /**
     * Add an event handler to the "getEditControls" method of
     * the conversation controller to add BBCode formatting buttons
     * to the edit controls.
     *
     * @param string $id
     *
     * @return string $buttons
     */
    protected function getEditControls($id = 'mt_replay') {
        if (!$this->config['bbcode']) {
            return $this->generateButtons('quote');
        }

        $buttons = $this->generateButtons($this->config['editOptionsControls']);

        return $buttons;
    }

    /**
     * Add JavaScript language definitions and variables
     *
     * @return string true
     */
    protected function regScripts() {
        // Check the settings, turn jQquery
        $jscripts = array(
            $this->config['jsUrl'] . 'web/lib/jquery.history.js',
            $this->config['jsUrl'] . 'web/lib/jquery.autogrow.js',
            $this->config['jsUrl'] . 'web/lib/jquery.misc.js',
            $this->config['jsUrl'] . 'web/lib/jquery.scrollTo.js',
            $this->config['jsUrl'] . 'web/ejs_production.js',
            $this->config['jsUrl'] . 'web/lib/timeago.js',
            $this->config['jsUrl'] . 'web/bbcode/bbcode.js',
            $this->config['jsUrl'] . 'web/highlight.pack.js',
        );

        // Add jquery if not exist
        $this->modx->regClientHTMLBlock('<script type="text/javascript">
    if(typeof jQuery == "undefined") {
        document.write("<script src=\"' . $this->config['jsUrl'] . 'web/lib/jquery-1.9.min.js\" type=\"text/javascript\"><\/script>");
    }
</script>');

        $this->modx->regClientScript($this->config['jsUrl'] . 'web/lib/jquery.history.js');
        $this->modx->regClientScript($this->config['jsUrl'] . 'web/lib/jquery.autogrow.js');
        $this->modx->regClientScript($this->config['jsUrl'] . 'web/lib/jquery.misc.js');
        $this->modx->regClientScript($this->config['jsUrl'] . 'web/lib/jquery.scrollTo.js');
        $this->modx->regClientScript($this->config['jsUrl'] . 'web/ejs_production.js');

        if (!$this->revers) {
            $this->modx->regClientScript($this->config['jsUrl'] . 'web/modxtalks.js');
            $this->modx->regClientScript($this->config['jsUrl'] . 'web/scrubber.js');
        } else {
            $this->modx->regClientScript($this->config['jsUrl'] . 'web/modxtalks_rev.js');
        }

        $this->modx->regClientScript($this->config['jsUrl'] . 'web/lib/timeago.js');
        // Localization for timeago plugin
        $this->modx->regClientScript($this->config['jsUrl'] . 'web/lib/timeago/' . $this->lang . '.js');

        // Add a button at quoting the resource allocation in the footer
        $this->modx->regClientHTMLBlock('<div id="mt_MTpopUpBox"><span class="">' . $this->modx->lexicon('modxtalks.quote_text') . '</span></div>');

        // Check the settings, turn BBCode
        if ($this->config['bbcode']) {
            $this->modx->regClientScript($this->config['jsUrl'] . 'web/bbcode/bbcode.js');
        }

        // Check the settings, turn Highlight
        if ($this->modx->getOption('modxtalks.highlight', null, false)) {
            $this->modx->regClientScript($this->config['jsUrl'] . 'web/highlight.pack.js');
            $this->modx->regClientCSS($this->config['cssUrl'] . 'web/highlight/' . strtolower($this->modx->getOption('modxtalks.highlighttheme', null, 'GitHub')) . '.css');
        }

        return true;
    }

    /**
     * Add Styles to head
     */
    protected function regStyles() {
        $this->modx->regClientCSS($this->config['cssUrl'] . 'web/bbcode/bbcode.css');
        $this->modx->regClientCSS($this->config['cssUrl'] . 'web/styles.css');
    }

    /**
     * Get Script Head
     *
     * @return bool true
     */
    protected function getScriptHead() {
        $this->modx->regClientStartupHTMLBlock('<script>var MT = {
        "assetsPath":"' . $this->config['assetsUrl'] . '",
        "conversation":"' . $this->config['conversation'] . '",
        "ctx":"' . $this->context . '",
        "link": "' . $this->modx->getOption('site_url') . $this->modx->resource->uri . '",
        "webPath": "' . MODX_BASE_URL . '",
        "token": "",
        "lang": "' . $this->lang . '",
        "revers": ' . var_export($this->revers, true) . ',
        "debug": ' . var_export($this->config['debug'], true) . ',
        "commentTpl": "ejs/' . $this->config['commentTpl'] . '.ejs' . '",
        "deletedCommentTpl": "ejs/' . $this->config['deletedCommentTpl'] . '.ejs' . '",
        "scrubberOffsetTop": ' . $this->config['scrubberOffsetTop'] . ',
        "scrubber": ' . var_export($this->scrubber, true) . ',
        "language": {
            "message.ajaxRequestPending": "' . $this->modx->lexicon('modxtalks.ajax_request_pending') . '",
            "message.ajaxDisconnected": "' . $this->modx->lexicon('modxtalks.ajax_disconnected') . '",
            "Loading...": "' . $this->modx->lexicon('modxtalks.loading') . '...",
            "Notifications": "' . $this->modx->lexicon('modxtalks.notifications') . '",
            "newComment": "' . $this->modx->lexicon('modxtalks.new_comment') . '",
            "moreText": "' . $this->modx->lexicon('modxtalks.more_text') . '",
            "message.confirmDelete": "' . $this->modx->lexicon('modxtalks.confirm_delete') . '",
            "message.confirmRestore": "' . $this->modx->lexicon('modxtalks.confirm_restore') . '",
            "message.confirmLeave":"' . $this->modx->lexicon('modxtalks.confirmLeave') . '",
            "message.confirm_ip":"' . $this->modx->lexicon('modxtalks.confirm_ip') . '",
            "message.confirm_email":"' . $this->modx->lexicon('modxtalks.confirm_email') . '",
            "message.confirmDiscardReply":"' . $this->modx->lexicon('modxtalks.confirm_discard_reply') . '",
            "Mute conversation": "' . $this->modx->lexicon('modxtalks.mute_conversation') . '",
            "Unmute conversation": "' . $this->modx->lexicon('modxtalks.unmute_conversation') . '"
        },
        "notificationCheckInterval": 30,
        "postsPerPage": ' . $this->config['commentsPerPage'] . ',
        "conversationUpdateIntervalStart": 10,
        "conversationUpdateIntervalMultiplier": 1.5,
        "conversationUpdateIntervalLimit": 512,
        "mentions": true,
        "time": "' . time() . '",
        "fullDeleteComment": ' . var_export($this->config['fullDeleteComment'], true) . ',
        "mtconversation": {
            "conversationId": "' . $this->config['conversation'] . '",
            "slug": "' . $this->config['slug'] . '",
            "id": ' . $this->config['conversationId'] . ',
            "countPosts": ' . $this->config['commentsCount'] . ',
            "startFrom": ' . $this->config['startFrom'] . ',
            "lastRead": ' . $this->config['lastRead'] . ',
            "updateInterval": 182
        }
    }
    </script>');

        return true;
    }

    /**
     * Generate EJS Templates files
     *
     * @return bool
     */
    protected function ejsTemplates() {
        $commentTpl = $this->config['ejsTemplatesPath'] . $this->config['commentTpl'] . '.ejs';
        $deletedCommentTpl = $this->config['ejsTemplatesPath'] . $this->config['deletedCommentTpl'] . '.ejs';
        if (!file_exists($commentTpl)) {
            $tags = array(
                'index' => '<%= index %>',
                'idx' => '<%= idx %>',
                'id' => '<%= id %>',
                'avatar' => '<%= avatar %>',
                'name' => '<%= name %>',
                'edit_name' => '<%= edit_name %>',
                'link' => '<%= link %>',
                'date' => '<%= date %>',
                'funny_date' => '<%= funny_date %>',
                'funny_edit_date' => '<%= funny_edit_date %>',
                'content' => '<%= content %>',
                'user' => '<%= user %>',
                'timeMarker' => '<%= timeMarker %>',
                'hideAvatar' => '<%= hideAvatar %>',
                'userId' => '<%= userId %>',
                'timeago' => '<%= timeago %>',
                'user_info' => '<%= user_info %>',
                'like_block' => '<%= like_block %>',
                'link_reply' => '',
                'quote' => '',
            );
            $tpl = $this->getTpl($this->config['commentTpl']);
            $data = preg_replace('@\s{2,}|\n@i', '', $this->parseTpl($tpl, $tags));
            file_put_contents($commentTpl, $data);
        }
        if (!file_exists($deletedCommentTpl)) {
            $tags = array(
                'deleteUser' => '<%= deleteUser %>',
                'delete_date' => '<%= delete_date %>',
                'funny_delete_date' => '<%= funny_delete_date %>',
                'name' => '<%= name %>',
                'index' => '<%= index %>',
                'date' => '<%= date %>',
                'funny_date' => '<%= funny_date %>',
                'id' => '<%= id %>',
                'idx' => '<%= idx %>',
                'timeMarker' => '<%= timeMarker %>',
                'timeago' => '<%= timeago %>',
                'deleted_by' => '<%= deleted_by %>',
                'restore' => '<%= restore %>',
                'link' => '<%= link %>',
            );
            $tpl = $this->getTpl($this->config['deletedCommentTpl']);
            $data = preg_replace('@\s{2,}|\n@i', '', $this->parseTpl($tpl, $tags));
            file_put_contents($deletedCommentTpl, $data);
        }

        return true;
    }

    /**
     * Get Comments
     *
     * @param string $conversation Conversation Short name
     * @param int    $limit (Optional) A limit of records to retrieve in the collection
     * @param mixed  $id (Optional) Start comment ID
     *
     * @return string $output Full processed comments
     */
    protected function getComments($conversation = '', $limit = 20, $id = null) {
        $output = '';
        if (empty($conversation)) {
            return $output;
        }

        /**
         * Check the cache section
         */
        if (!$theme = $this->getConversation($conversation)) {
            return $output;
        }

        $this->config['conversationId'] = $theme->get('id');

        if ($this->debug) {
            dump('ID темы: ' . $this->config['conversationId']);
        }

        $this->config['commentsCount'] = 0;

        $this->config['slug'] = $this->generateLink($this->config['conversationId']);

        if ($this->debug) {
            dump($this->generateLink($this->config['conversationId']));
            dump($this->config['slug']);
        }

        $count = $theme->getProperty('total', 'comments');
        if ($count < 1) {
            return $output;
        }

        /**
         * Get resource URL
         */
        //$link = $this->modx->getOption('site_url') . $this->modx->resource->uri;
        $link = $this->modx->makeUrl($this->modx->resource->id, '', '', 'full');

        $page = 1;
        $totalPages = ceil($count / ($limit > 0 ? $limit : $count));
        if ($this->debug) {
            dump('Total pages: ' . $totalPages);
        }

        $offset = 0;
        $this->config['startFrom'] = $this->config['lastRead'] = 1;
        if ($id === 'last' && $limit < $count) {
            if (!$this->revers) {
                $range = range($count - $limit + 1, $count);
            } else {
                $range = range(1, $limit);
            }
        } elseif (ctype_digit($id)) {
            $id = intval($id) !== 0 ? intval($id) : 1;
            if (!$this->revers) {
                if ($id === 1 || $id <= $limit) {
                    $this->modx->sendRedirect($link);
                }
                $this->modx->sendRedirect($this->getLink('page_' . ceil($id / $limit)) . '#comment-' . $id);
            } else {
                $c = abs($count - $limit + 1);
                if ($c <= $id && $id <= $count) {
                    $this->modx->sendRedirect($link . '#comment-' . $id);
                }
                $page = ceil(($count - $id + 1) / $limit);
                $this->modx->sendRedirect($this->getLink('page_' . $page) . '#comment-' . $id);
            }
        } elseif ($id == date('Y-m', strtotime($id))) {
            $idx = $this->getDateIndex($theme->get('id'), date('Y-m', strtotime($id)));
            if (!$this->revers) {
                $range = range($idx, $idx + $limit);
            } else {
                $last = ($idx - $limit) <= 0 ? 1 : $idx - $limit;
                $range = range($idx, $last);
                unset($last);
            }
        } elseif (preg_match('/page_(\d{1,4})/', $id, $match)) {
            $page = (int) $match[1];
            if ($page === 1) {
                $this->modx->sendRedirect($link);
            } elseif ($page > $totalPages) {
                $this->modx->sendRedirect($link);
            }
            if (!$this->revers) {
                $first = ($page - 1) * $limit + 1;
                $range = range($first, $first + $limit - 1);
            } else {
                $end = $count - $limit * ($page - 1);
                $first = $count - $limit * $page + 1;
                $range = range($first, $end);
            }
        } else {
            if (!$this->revers) {
                $range = range($this->config['startFrom'], $limit);
            } else {
                $range = range($count - $limit + 1, $count);
            }
        }
        // Unset matches elements
        unset($match);

        $comments = $this->getCommentsArray($range, $theme->get('id'));

        if (!isset($comments[0]) && $count > 0) {
            $this->modx->sendRedirect($link);
        }
        /**
         * Set total comments count to config
         */
        $this->config['commentsCount'] = $count;
        /**
         * Comment template
         */
        $tpl = $this->getTpl($this->config['commentTpl']);
        /**
         * Deleted comment template
         */
        $deletedTpl = $this->getTpl($this->config['deletedCommentTpl']);

        $hideAvatarEmail = '';
        $relativeTime = '';

        $isAuthenticated = $this->modx->user->isAuthenticated($this->context) || $this->isModerator();

        $guest_name = $this->modx->lexicon('modxtalks.guest');
        $quote_text = $this->modx->lexicon('modxtalks.quote');
        $del_by = $this->modx->lexicon('modxtalks.deleted_by');

        $btn_like = '';
        $btn_unlike = '';

        if ($isAuthenticated) {
            $userId = (int) $this->modx->user->id;
            $restore = $this->modx->lexicon('modxtalks.restore');
            $btn_like = $this->modx->lexicon('modxtalks.i_like');
            $btn_unlike = $this->modx->lexicon('modxtalks.not_like');
        }

        if ($isModerator = $this->isModerator()) {
            $userInfoTpl = $this->getTpl($this->config['user_info']);
        }

        if (count($comments[0])) {
            reset($comments[0]);
            $first = current($comments[0]);
            $last = end($comments[0]);
            $this->config['startFrom'] = $first['idx'];
            $this->config['lastRead'] = $last['idx'];
            reset($comments[0]);
        }

        $usersIds =& $comments[1];
        $users = array();

        /**
         * Get registered Users
         */
        if (count($usersIds)) {
            $authUsers = $this->getUsers($usersIds);
            foreach ($authUsers as $a) {
                $users[$a['id']] = array(
                    'name' => $a['fullname'] ? $a['fullname'] : $a['username'],
                    'email' => $a['email'],
                );
            }
        }

        /**
         * Create a "more" block item which we can use below.
         */
        if ($this->revers && $page > 1) {
            $href = $page === 2 ? $link : $this->getLink('page_' . ($page - 1));
            $output .= '<div class="mt_scrubberMore mt_scrubberPrevious"><a href="' . $href . '">' . $this->modx->lexicon('modxtalks.more_text') . '</a></div>';
        } elseif (!$this->revers && $this->config['startFrom'] > 1) {
            $linkPrev = $this->config['startFrom'] <= $this->config['commentsPerPage']
                ? 1
                : $this->config['startFrom'] - $this->config['commentsPerPage'];

            $output .= '<div class="mt_scrubberMore mt_scrubberPrevious"><a href="' . $this->getLink($linkPrev) . '#mt_conversationPosts">' . $this->modx->lexicon('modxtalks.more_text') . '</a></div>';
        }

        foreach ($comments[0] as $comment) {
            $timeMarker = '';
            $date = date($this->config['dateFormat'] . ' O', $comment['time']);
            $funny_date = $this->date_format($comment['time']);
            $index = date('Ym', $comment['time']);

            /**
             * If this is registered user
             */
            if ($comment['userId'] > 0) {
                $name = $users[$comment['userId']]['name'];
                $email = $users[$comment['userId']]['email'];
            } else {
                /**
                 * If this is guest
                 */
                $name = $comment['username'] ? $comment['username'] : $guest_name;
                $email = $comment['useremail'] ? $comment['useremail'] : 'anonym@anonym.com';
            }

            /**
             * If the post before this one has a different relative
             * time string to this one, output a 'time marker'.
             */
            $relativeTimeComment = $this->date_format($comment['time'], true);
            if ($relativeTime !== $relativeTimeComment) {
                $timeMarker = '<div class="mt_timeMarker" data-now="1">' . $relativeTimeComment . '</div>';
                $relativeTime = $relativeTimeComment;
            }
            /**
             * Timeago date format
             */
            $timeago = date('c', $comment['time']);
            /**
             * Prepare data for deleted comment
             */
            if ($comment['deleteTime'] > 0 && $comment['deleteUserId'] > 0) {
                $tmp = array(
                    'deleteUser' => $users[$comment['deleteUserId']]['name'],
                    'delete_date' => date($this->config['dateFormat'] . ' O', $comment['deleteTime']),
                    'funny_delete_date' => $this->date_format($comment['deleteTime']),
                    'name' => $name,
                    'index' => $index,
                    'date' => $date,
                    'funny_date' => $funny_date,
                    'id' => $comment['id'],
                    'idx' => $comment['idx'],
                    'timeMarker' => $timeMarker,
                    'timeago' => $timeago,
                    'deleted_by' => $del_by,
                    'restore' => '',
                    'link' => $this->getLink($comment['idx']),
                );

                if ($isAuthenticated && ($isModerator === true || $comment['deleteUserId'] === $userId)) {
                    $tmp['restore'] = '<a href="#" onclick="MTConversation.commentRestore(' . $comment['id'] . ', this);return false" title="' . $restore . '" class="mt_icon mt_icon-undo">' . $restore . '</a>';
                }
            } else {
                /**
                 * Prepare data for published comment
                 */
                $tmp = array(
                    'avatar' => $this->getAvatar($email),
                    'hideAvatar' => 'style="display: none;"',
                    'name' => $name,
                    'content' => $comment['content'],
                    'index' => $index,
                    'date' => $date,
                    'funny_date' => $funny_date,
                    'link_reply' => $this->getLink('reply-' . $comment['idx']),
                    'id' => $comment['id'],
                    'idx' => $comment['idx'],
                    'quote' => $quote_text,
                    'user' => $this->userButtons($comment['userId'], $comment['time']),
                    'userId' => md5($comment['userId'] . $email),
                    'timeMarker' => $timeMarker,
                    'link' => $this->getLink($comment['idx']),
                    'timeago' => $timeago,
                    'user_info' => '',
                    'like_block' => '',
                );

                if ($isModerator === true) {
                    $tmp['user_info'] = $this->parseTpl($userInfoTpl, array(
                        'email' => $email,
                        'ip' => $comment['ip']
                    ));
                }

                /**
                 * Comment Votes
                 */
                if ($this->config['voting']) {
                    $likes = '';
                    $btn = $btn_like;

                    if ($votes = json_decode($comment['votes'], true)) {
                        if ($isAuthenticated && in_array($this->modx->user->id, $votes['users'])) {
                            $btn = $btn_unlike;
                            $total = count($votes['users']) - 1;

                            if ($total > 0) {
                                $likes = $this->decliner($total, $this->modx->lexicon('modxtalks.people_like_and_you', array('total' => $total)));
                            } else {
                                $likes = $this->modx->lexicon('modxtalks.you_like');
                            }
                        } elseif ($votes['votes'] > 0) {
                            $likes = $this->decliner($votes['votes'], $this->modx->lexicon('modxtalks.people_like', array('total' => $votes['votes'])));
                        }
                    }
                    if (!$isAuthenticated && (!isset($votes['votes']) || $votes['votes'] == 0)) {
                        $tmp['like_block'] = '';
                    } else {
                        $btn = $isAuthenticated ? '<a href="#" class="mt_like-btn">' . $btn . '</a>' : '';
                        $tmp['like_block'] = '<div class="mt_like_block">' . $btn . '<span class="mt_likes">' . $likes . '</span></div>';
                    }
                }

                /**
                 * If the post before this one is by the same member
                 * as this one, hide the avatar
                 */
                if ($email != $hideAvatarEmail) {
                    $tmp['hideAvatar'] = '';
                    $hideAvatarEmail = $email;
                }
                /**
                 * If comment edited, get edit time and user
                 */
                if ($comment['editTime'] && $comment['editUserId'] && !$comment['deleteTime']) {
                    $tmp['funny_edit_date'] = $this->date_format($comment['editTime']);
                    $tmp['edit_name'] = $this->modx->lexicon('modxtalks.edited_by', array(
                        'name' => $users[$comment['editUserId']]['name']
                    ));
                }
            }

            if ($comment['deleteTime'] && $comment['deleteUserId']) {
                $output .= $this->parseTpl($deletedTpl, $tmp);
            } else {
                $output .= $this->parseTpl($tpl, $tmp);
            }
        }
        unset($email, $name, $tmp, $tpl, $deletedTpl, $comments);

        if ($this->revers && $page < $totalPages) {
            $href = $this->getLink('page_' . ($page + 1));
            $output .= '<div class="mt_scrubberMore mt_scrubberNext"><a href="' . $href . '">' . $this->modx->lexicon('modxtalks.more_text') . '</a></div>';
        } elseif (!$this->revers && ($this->config['startFrom'] + $this->config['commentsPerPage']) <= $this->config['commentsCount']) {
            $output .= '<div class="mt_scrubberMore mt_scrubberNext"><a href="' . $this->getLink(($this->config['lastRead'] + 1)) . '#mt_mt_cf_conversationReply">' . $this->modx->lexicon('modxtalks.more_text') . '</a></div>';
        }

        return $output;
    }

    /**
     * Generate comment link
     *
     * @param string $link
     *
     * @return string
     */
    public function getLink($link = '') {
        $link = (string) $link;

        return str_replace($this->config['slugReplace'], $link, $this->config['slug']);
    }

    /**
     * Get resource alias path
     *
     * @param string $link
     * @param string $search
     *
     * @return string Resource alias
     */
    public function aliasPath($link = '', $search = '$$$') {
        if ($this->friendly_urls) {
            if (!isset($this->resource_alias_path) && $this->config['slug'] !== '') {
                $this->resource_alias_path = $this->config['slug'];
            }
            if (!isset($this->resource_alias_path)) {
                if (!isset($this->resource_alias)) {
                    $this->resource_alias = $this->modx->resource->get('alias');
                }
                $this->resource_alias_path = $this->modx->resource->getAliasPath($this->resource_alias . '/comment-' . $search . '-mt');
            }
            $path = str_replace($search, $link, $this->resource_alias_path);

            return $path;
        }

        $link = isset($_REQUEST[$this->modx->getOption('request_param_alias')]) ? $_REQUEST[$this->modx->getOption('request_param_alias')] . '&comment=' . $link : '?comment=' . $link;

        return $link;
    }

    /**
     * Get scrubber
     *
     * @param int $conversationId Conversation Id
     *
     * @return string Full rendered scrubber
     */
    public function getScrubber($conversationId = 0) {
        if ($conversationId == 0 && $this->config['conversationId'] == 0) {
            return array();
        } elseif ($conversationId == 0 && $this->config['conversationId'] != 0) {
            $conversationId = $this->config['conversationId'];
        }

        $scrubber = array(
            'key' => 0,
            'start' => !$this->revers ? $this->modx->lexicon('modxtalks.start') : $this->modx->lexicon('modxtalks.now'),
            'start_link' => $this->aliasPath('1'),
            'now' => !$this->revers ? $this->modx->lexicon('modxtalks.now') : $this->modx->lexicon('modxtalks.start'),
            'now_link' => $this->aliasPath('last'),
            'reply' => $this->modx->lexicon('modxtalks.reply'),
            'count_talks' => $this->config['commentsCount'],
            'months' => '',
            'conversation' => $this->modx->resource->id,
            'modxtalks_total' => $this->modx->lexicon('modxtalks.total'),
        );

        // Choose the topics of the month and if necessary, the topics
        $dateScrubber = '';
        $ds = $this->modx->newQuery('modxTalksPost');
        $ds->where(array(
            'conversationId' => $conversationId
        ));
        $ds->select(array('modxTalksPost.date', 'modxTalksPost.time'));
        $ds->groupby('modxTalksPost.date');

        if ($ds->prepare() && $ds->stmt->execute()) {
            $dsd = $ds->stmt->fetchAll(PDO::FETCH_ASSOC);
            $dsArray = json_decode($this->modx->lexicon('modxtalks.month_array'));
            $dsYear = array();
            foreach ($dsd as $dsp) {
                $scrYear = strftime('%Y', $dsp['time']);
                $dateScrubberm = strftime('%m', $dsp['time']);
                $dateScrubbermi = (int) $dateScrubberm;
                $dsmi = $dsArray->$dateScrubbermi;
                $dLink = $this->getLink((strftime('%Y', $dsp['time']) . '-' . $dateScrubberm));
                $dTitle = mb_convert_case($dsmi[0], MB_CASE_TITLE, "UTF-8");
                $dsYear[$scrYear] = (isset($dsYear[$scrYear]) ? $dsYear[$scrYear] : '') . '<li class="mt_scrubber-' . $dsp['date'] . '" data-index="' . $dsp['date'] . '"><a href="' . $dLink . '">' . $dTitle . '</a></li>';
            }
        }

        foreach ($dsYear as $key => $value) {
            if ($key != strftime('%Y', time())) {
                $dateScrubber .= '<li class="mt_scrubber-' . $key . '01 selected" data-index="' . $key . '01"><a href="' . $this->getLink($key . '-01') . '">' . $key . '</a><ul>' . $value . '</ul></li>';
            } else {
                $dateScrubber .= $value;
            }
        }

        $scrubber['months'] = $dateScrubber;

        return $scrubber;
    }

    /**
     * BBCode parser
     *
     * @param string $content
     *
     * @return string Parsed content
     */
    public function bbcode($content) {
        $tags = array(
            'd_1' => array('[_[', ']_]'),
            'd_2' => array('&#091;&#091;', '&#093;&#093;'),
            's_1' => array('[', ']'),
            's_2' => array('&#091;', '&#093;')
        );
        if (!$this->config['bbcode']) {
            $content = $this->quotes($content);
            $content = str_replace($tags['d_1'], $tags['d_2'], $content);
            $content = str_replace($tags['s_1'], $tags['s_2'], $content);

            return $content;
        }
        if (!isset($this->modx->bbcode) || !($this->modx->bbcode instanceof BBCode)) {
            require_once("libs/nbbc.php");
            $this->modx->bbcode = new BBCode;
            $this->modx->bbcode->SetModx($this->modx);
            $this->modx->bbcode->SetAllowAmpersand();
            /**
             * Set Video Width and Height
             */
            $this->modx->bbcode->SetVideoSize($this->config['videoSize'][0], $this->config['videoSize'][1]);
            // $this->modx->bbcode->SetSlugReplace($this->config['slugReplace']);
            $this->modx->bbcode->SetSlug($this->config['slug']);
            /**
             * Enable Smileys
             */
            if ($this->config['smileys']) {
                $this->modx->bbcode->SetEnableSmileys();
                $this->modx->bbcode->SetSmileyURL($this->config['imgUrl'] . 'smileys');
            }
            /**
             * Detect URL's
             */
            if ($this->config['detectUrls']) {
                $this->modx->bbcode->SetDetectURLs();
            }
            /**
             * Comment Length
             */
            if (isset($this->config['commentLength']) && (int) $this->config['commentLength'] > 2) {
                $this->modx->bbcode->SetLimit((int) $this->config['commentLength']);
            }
        }
        $content = str_replace($tags['d_1'], $tags['d_2'], $content);
        $content = $this->modx->bbcode->Parse($content);
        $content = str_replace($tags['s_1'], $tags['s_2'], $content);

        return $content;
    }

    /**
     * Get User Avatar
     *
     * @param string $email Email address
     *
     * @return string Gravatar image link
     */
    public function getAvatar($email = '', $size = 0) {
        if ($this->config['gravatar'] && !empty($email)) {
            $urlsep = $this->modx->context->getOption('xhtml_urls', true) ? '&amp;' : '&';

            return $this->config['gravatarUrl'] . md5($email) . '?s=' . (intval($size) > 0 ? $size : $this->config['gravatarSize']) . $urlsep . 'd=' . urlencode($this->config['defaultAvatar']);
        }

        return $this->config['imgUrl'] . 'avatar.png';
    }

    /**
     * Determines if this user is moderator in specified groups
     *
     * @return bool True if user is moderator of any groups
     */
    public function isModerator() {
        if (!isset($this->groups)) {
            $this->groups = explode(',', $this->config['moderator']);
            array_walk($this->groups, 'trim');
        }

        return $this->modx->user->isMember($this->groups);
    }

    /**
     * Get Add Comment Form
     *
     * @return string $form Full rendered add comment form
     */
    protected function getForm() {
        if ($this->config['onlyAuthUsers'] && !$this->modx->user->isAuthenticated($this->context) && !$this->isModerator()) {
            return $this->getChunk($this->config['commentAuthTpl'], array(
                'avatar' => $this->config['defaultAvatar'],
                'noLogin' => $this->modx->lexicon('modxtalks.no_login')
            ));
        }

        if ($this->config['commentsClosed'] && !$this->isModerator()) {
            return $this->parseTpl($this->config['commentAuthTpl'], null, true);
        }

        if ($this->modx->user->isAuthenticated($this->context) || $this->isModerator()) {
            $user = $this->modx->user->getOne('Profile');
            $email = $user->get('email');
            $name = $user->get('fullname');
            $tmp = array(
                'user' => !$name ? $this->modx->user->get('username') : $name,
                'avatar' => $this->getAvatar($email),
                'hidden' => ' hidden',
            );
        } else {
            $tmp = array(
                'user' => $this->modx->lexicon('modxtalks.guest'),
                'avatar' => $this->getAvatar(),
                'hidden' => '',
            );
        }

        $tmp['controlsbb'] = $this->getEditControls();
        $tmp['previewCheckbox'] = $this->modx->lexicon('modxtalks.preview_checkbox');
        $tmp['reply'] = $this->modx->lexicon('modxtalks.reply');
        $tmp['link'] = $this->modx->resource->uri;
        $tmp['write_comment'] = $this->modx->lexicon('modxtalks.write_comment');
        $tmp['your_name_pl'] = $this->modx->lexicon('modxtalks.your_name_pl');
        $tmp['your_email_pl'] = $this->modx->lexicon('modxtalks.your_email_pl');

        $tpl = $this->getTpl($this->config['commentAddFormTpl']);
        $form = $this->parseTpl($tpl, $tmp);

        return $form;
    }

    /**
     * Get Edit Comment Form
     *
     * @return string $form Full rendered add comment form
     */
    public function getEditForm($id = 0, $content = '', $ctx = 'web') {
        $user = $this->modx->user;
        $name = $user->get('username');
        $email = $user->Profile->get('email');
        $fullname = $user->Profile->get('fullname');
        $tmp = array(
            'user' => !empty($fullname) ? $fullname : $name,
            'avator' => $this->getAvatar($email),
            'controlsbb' => $this->getEditControls('comment-' . $id),
            'previewCheckbox' => $this->modx->lexicon('modxtalks.preview_checkbox'),
            'content' => $content,
            'id' => $id,
            'write_comment' => $this->modx->lexicon('modxtalks.write_comment'),
            'save_changes' => $this->modx->lexicon('modxtalks.save_changes'),
            'cancel' => $this->modx->lexicon('modxtalks.cancel'),
        );
        $tpl = $this->getTpl($this->config['commentEditFormTpl']);
        $form = $this->parseTpl($tpl, $tmp);

        return $form;
    }

    /**
     * Gets a Chunk and caches it; also falls back to file-based templates
     * for easier debugging.
     *
     * @param string $name The name of the Chunk
     * @param array  $properties The properties for the Chunk
     *
     * @return string The processed content of the Chunk
     */
    public function getChunk($name, $properties = array()) {
        $chunk = null;
        if (!isset($this->chunks[$name])) {
            $chunk = $this->getTplChunk($name);
            if (empty($chunk)) {
                if (!$chunk = $this->modx->getObject('modChunk', array('name' => $name))) {
                    return false;
                }
            }
            $this->chunks[$name] = $chunk->getContent();
        } else {
            $o = $this->chunks[$name];
            $chunk = $this->modx->newObject('modChunk');
            $chunk->setContent($o);
        }

        $chunk->setCacheable(false);

        return $chunk->process($properties);
    }

    /**
     * Returns a modChunk object from a template file.
     *
     * @param string $name The name of the Chunk. Will parse to name.$postfix
     * @param string $postfix The default postfix to search for chunks at.
     *
     * @return modChunk/boolean Returns the modChunk object if found, otherwise
     * false.
     */
    private function getTplChunk($name, $postfix = '.chunk.tpl') {
        $chunk = false;
        $f = $this->config['chunksPath'] . strtolower($name) . $postfix;
        if (file_exists($f)) {
            $o = file_get_contents($f);
            $chunk = $this->modx->newObject('modChunk');
            $chunk->set('name', $name);
            $chunk->setContent($o);
        }

        return $chunk;
    }

    /**
     * Parse template chunk
     *
     * @param string  $tpl Template file
     * @param array   $arr Array of placeholders
     * @param boolean $chunk If True get chunk, else use template from string
     * @param string  $postfix Chunk postfix if use file-based chunks
     *
     * @return string Parsed chunck file
     */
    public function parseTpl($tpl = '', $arr = array(), $chunk = false, $postfix = '.chunk.tpl') {
        if (empty($tpl) && $chunk === false) {
            return '';
        } elseif (!empty($tpl) && $chunk === true) {
            $tpl = $this->getTpl($tpl, $postfix);
        }

        if (count($arr)) {
            $tmp = array();
            foreach ($arr as $k => $v) {
                $tmp['pl'][$k] = '[[+' . $k . ']]';
                $tmp['vl'][$k] = $v;
            }

            $tpl = str_replace($tmp['pl'], $tmp['vl'], $tpl);
        }

        $tpl = preg_replace('@\[\[(.*?)\]\]@', '', $tpl);

        return $tpl;
    }

    /**
     * Get template chunk
     *
     * @param string $tpl Template file
     * @param string $postfix Chunk postfix if use file-based chunks
     *
     * @return string Empty
     */
    private function getTpl($tpl = '', $postfix = '.chunk.tpl') {
        if (empty($tpl)) {
            return '';
        }

        if (isset($this->chunks[$tpl])) {
            return $this->chunks[$tpl];
        }

        // If useChunk setting set to True, use the modx standard chunk
        if ($this->config['useChunks'] === true) {
            if ($chunk = $this->modx->getObject('modChunk', array('name' => $tpl))) {
                $this->chunks[$tpl] = $chunk->get('content');

                return $this->chunks[$tpl];
            }
        }

        // If chunk not found or useChunk set to False, use file-based chunk
        $f = $this->config['chunksPath'] . strtolower($tpl) . $postfix;
        if (file_exists($f)) {
            $this->chunks[$tpl] = file_get_contents($f);

            return $this->chunks[$tpl];
        }

        return '';
    }

    /**
     * Funny date
     *
     * @param int     $time UNIX timestamp
     * @param boolean $group
     *
     * @return string
     */
    public function date_format($time, $group = true) {
        $seconds = abs(time() - $time);
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $days = floor($hours / 24);
        $months = floor($days / 30);
        $years = floor($days / 365);
        $seconds = floor($seconds);

        if ($group === true && $minutes < 60) {
            return $this->modx->lexicon('modxtalks.date_hours_back_less');
        }
        if ($seconds < 60) {
            return $this->decliner($seconds, $this->modx->lexicon('modxtalks.date_seconds_back', array(
                'seconds' => $seconds
            )));
        }
        if ($minutes < 45) {
            return $this->decliner($minutes, $this->modx->lexicon('modxtalks.date_minutes_back', array(
                'minutes' => $minutes
            )));
        }
        if ($minutes < 60) {
            return $this->modx->lexicon('modxtalks.date_hours_back_less');
        }
        if ($hours < 24) {
            return $this->decliner($hours, $this->modx->lexicon('modxtalks.date_hours_back', array(
                'hours' => $hours
            )));
        }
        if ($days < 30) {
            return $this->decliner($days, $this->modx->lexicon('modxtalks.date_days_back', array(
                'days' => $days
            )));
        }
        if ($days < 365) {
            return $this->decliner($months, $this->modx->lexicon('modxtalks.date_month_back', array(
                'months' => $months
            )));
        }
        if ($days > 365) {
            return $this->decliner($years, $this->modx->lexicon('modxtalks.date_years_back', array(
                'years' => $years
            )));
        }

        return date($this->config['dateFormat'], $time);
    }

    /**
     * Declension of word
     *
     * @param int          $count
     * @param string|array $forms
     *
     * @return string
     */
    public function decliner($count, $forms) {
        if (!is_array($forms)) {
            $forms = explode(';', $forms);
        }

        $count = abs($count);

        if ($this->lang === 'ru') {
            $mod100 = $count % 100;
            switch ($count % 10) {
                case 1:
                    if ($mod100 == 11)
                        return $forms[2];
                    else return $forms[0];
                case 2:
                case 3:
                case 4:
                    if (($mod100 > 10) && ($mod100 < 20))
                        return $forms[2];
                    else return $forms[1];
                case 5:
                case 6:
                case 7:
                case 8:
                case 9:
                case 0:
                    return $forms[2];
            }
        } else {
            /**
             * If lang not RU
             */
            return ($count == 1) ? $forms[0] : $forms[1];
        }
    }

    /**
     * Get user buttons for comment
     *
     * @param int $userId User Id
     * @param int $time UNIX timestamp
     *
     * @return string HTML buttons
     */
    public function userButtons($userId = 0, $time = 0) {
        /**
         * If a registered user is a member of moderators, then give moderate comments.
         */
        $buttons = '<a href="#" title="' . $this->modx->lexicon('modxtalks.edit') . '" class="mt_icon mt_icon-pencil" onclick="MTConversation.commentEdit(this);return false;">' . $this->modx->lexicon('modxtalks.edit') . '</a>';
        $buttons .= '<a href="#" title="' . $this->modx->lexicon('modxtalks.delete') . '" class="mt_icon mt_icon-bin" onclick="MTConversation.commentDelete(this);return false;">' . $this->modx->lexicon('modxtalks.delete') . '</a>';

        if ($this->isModerator()) {
            return $buttons;
        } elseif ($userId != 0 && $this->modx->user->id == $userId && ($time + $this->config['edit_time']) > time()) {
            return $buttons;
        }

        if ($this->config['onlyAuthUsers'] && !$this->modx->user->isAuthenticated($this->context)) {
            return '';
        }

        return '';
    }

    /**
     * Make Quotes for modxTalks::quotes()
     *
     * @param string $text
     * @param int    $postId
     * @param string $user
     * @param string $content
     *
     * @return string Processed Content
     */
    public function makeQuote($text, $postId = 0, $user = '', $content = '') {
        $content = htmlspecialchars($this->modx->stripTags($content));
        $text = htmlspecialchars($this->modx->stripTags($text));
        $user = htmlspecialchars($this->modx->stripTags($user));
        $postId = preg_replace('#[^0-9]#i', '', $postId);

        $quote = $content . '<blockquote>';

        if (!empty($postId)) {
            $link = str_replace($this->config['slugReplace'], $postId, $this->config['slug']);
            $quote .= '<a href="' . $link . '" rel="comment" data-id="' . $postId . '" class="mt_control-search mt_postRef">' . $this->mtConfig['go_to_comment'] . '</a>';
        }

        if (!empty($user)) {
            $quote .= '<cite>' . $user . '</cite>';
        }

        $quote .= $text . '</blockquote>';

        return $quote;
    }

    /**
     * Make Quotes from BBCode
     *
     * @param string $content Comment content
     *
     * @return string Processed Content
     */
    public function quotes($content) {
        if (!isset($this->mtConfig['go_to_comment'])) {
            $this->mtConfig['go_to_comment'] = $this->modx->lexicon('modxtalks.go_to_comment');
        }

        $regexp = "/(.*?)\n?\[quote(\s?id\=(.*?))?(\s?user\=\"?(.*?)\"?)?(]?)?\]\n?(.*?)\n?\[\/quote\]\n{0,2}/ise";
        while (preg_match($regexp, $content)) {
            $content = preg_replace($regexp, "\$this->makeQuote('$7', '$3', '$5', '$1')", $content);
        }

        return $content;
    }

    /**
     * Get User IP Address
     *
     * @return string $ip
     */
    public function get_client_ip() {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = '0.0.0.0';
        }

        return $ip;
    }

    /**
     * Sends an email for conversation
     *
     * @param string $subject Email subject
     * @param string $body Email content
     * @param string $to Receiver Emails
     * @param string $body_text Optional, Plain text version of email content
     *
     * @return bool
     */
    protected function sendEmail($subject, $body = '', $to, $body_text = '', $options = array()) {
        $this->modx->getService('mail', 'mail.modPHPMailer');
        if (!$this->modx->mail) {
            return false;
        }

        $emailFrom = $this->modx->getOption('modxtalks.emailsFrom', $this->modx->getOption('emailsender'));
        $emailReplyTo = $this->modx->getOption('modxtalks.emailsReplyTo', $this->modx->getOption('emailsender'));

        /* allow multiple to addresses */
        if (!is_array($to)) {
            $to = explode(',', $to);
        }

        $success = false;
        foreach ($to as $emailAddress) {
            if (empty($emailAddress) || strpos($emailAddress, '@') === false) {
                continue;
            }

            $this->modx->mail->set(modMail::MAIL_BODY, $body);

            if (!empty($body_text)) {
                $this->modx->mail->set(modMail::MAIL_BODY_TEXT, $body_text);
            }

            $this->modx->mail->set(modMail::MAIL_FROM, $emailFrom);
            $this->modx->mail->set(modMail::MAIL_FROM_NAME, $this->modx->getOption('fromName', $options, $this->modx->getOption('site_name')));
            $this->modx->mail->set(modMail::MAIL_SENDER, $emailFrom);
            $this->modx->mail->set(modMail::MAIL_SUBJECT, $subject);
            $this->modx->mail->address('to', $emailAddress);
            $this->modx->mail->address('reply-to', $emailReplyTo);
            $this->modx->mail->setHTML(true);
            $success = $this->modx->mail->send();
            $this->modx->mail->reset();
        }

        return $success;
    }

    /**
     * Sends notification to all watchers of conversation saying a new post has been made.
     *
     * @param modxTalksPost|modxTalksTempPost $comment A reference to the actual comment
     *
     * @return bool True if successful
     */
    public function notifyModerators(&$comment) {
        if (!$comment instanceof modxTalksPost && !$comment instanceof modxTalksTempPost) {
            return false;
        }

        $this->modx->lexicon->load('modxtalks:emails');

        /**
         * Get User info
         */
        $user = $comment->getUserData();
        $images_url = $this->modx->getOption('site_url') . substr($this->config['imgUrl'], 1);

        if ($comment instanceof modxTalksPost) {
            $cid = $comment->conversationId;
            $idx = $comment->idx;
            $link = $this->generateLink($cid, $idx);
            $subject = $this->modx->lexicon('modxtalks.email_new_comment');
            $text = $this->modx->lexicon('modxtalks.email_added_new_comment', array(
                'link' => $link,
                'name' => $user['name'],
            ));
        } elseif ($comment instanceof modxTalksTempPost) {
            $subject = $this->modx->lexicon('modxtalks.email_new_premoderated_comment');
            $text = $this->modx->lexicon('modxtalks.email_user_add_premoderated_comment', array(
                'name' => $user['name'],
            ));
        }

        $params = array(
            'title' => 'Заголовок',
            'content' => $this->modx->stripTags($this->bbcode($comment->content)),
            'images_url' => $images_url,
            'avatar' => $this->getAvatar($user['email'], 50),
            'text' => $text,
            'date' => date($this->config['dateFormat'] . ' O', $comment->time),
        );

        /**
         * get email body
         */
        $body = $this->getChunk('mt_send_mail', $params);

        /**
         * send notifications
         */
        $success = false;

        $emails = $this->getUsersEmailsByGroups($this->config['moderator'], $comment);

        /**
         * send notifications to moderators
         */
        if (!empty($emails)) {
            if ($this->sendEmail($subject, $body, $emails)) {
                $success = true;
            }
        }

        return $success;
    }

    /**
     * Sends notification to users
     *
     * @param modxTalksPost $comment A reference to the actual comment
     *
     * @return bool True if successful
     */
    public function notifyUser(&$comment) {
        if (!$comment instanceof modxTalksPost) {
            return false;
        }

        $this->modx->lexicon->load('modxtalks:emails');

        /**
         * Get User info
         */
        $user = $comment->getUserData();

        $cid = $comment->conversationId;
        $idx = $comment->idx;

        $link = $this->generateLink($cid, $idx);
        $images_url = $this->modx->getOption('site_url') . substr($this->config['imgUrl'], 1);

        $subject = $this->modx->lexicon('modxtalks.email_comment_approved');
        $text = $this->modx->lexicon('modxtalks.email_user_approve_comment', array(
            'link' => $link,
        ));

        $params = array(
            'title' => 'Заголовок',
            'content' => $this->modx->stripTags($this->bbcode($comment->content)),
            'images_url' => $images_url,
            'avatar' => $this->getAvatar($user['email'], 50),
            'text' => $text,
            'date' => date($this->config['dateFormat'] . ' O', $comment->time),
        );

        /**
         * Get email body
         */
        $body = $this->getChunk('mt_send_mail', $params);

        /**
         * Send notifications to user
         */
        $success = false;
        if (!empty($user['email'])) {
            if ($this->sendEmail($subject, $body, $user['email'])) {
                $success = true;
            }
        }

        return $success;
    }

    /**
     * Get Users data by groups
     *
     * @param string|array  $groups Moderators groups
     * @param modxTalksPost $comment A reference to the actual comment
     *
     * @return array
     */
    public function getUsersEmailsByGroups($groups, &$comment) {
        if (!$comment instanceof modxTalksPost && !$comment instanceof modxTalksTempPost) {
            return false;
        }

        if (!is_array($groups)) {
            $groups = explode(',', $groups);
        }

        $usersIds = array();
        /**
         * Moderators email addresses
         */
        $emails = array();
        /**
         * Moderator ID of this comment
         */
        $userId = $comment->userId;

        $c = $this->modx->newQuery('modUserGroup');
        $c->where(array(
            'modUserGroup.name:IN' => $groups
        ));
        $c->select(array('modUserGroup.id', 'UserGroupMembers.member'));
        $c->leftJoin('modUserGroupMember', 'UserGroupMembers', 'UserGroupMembers.user_group = modUserGroup.id');

        if ($c->prepare() && $c->stmt->execute()) {
            $result = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $uid) {
                if ($uid['member'] != $userId) {
                    $usersIds[] = $uid['member'];
                }
            }
        }

        if (count($usersIds)) {
            $c = $this->modx->newQuery('modUserProfile', array(
                'internalKey:IN' => $usersIds
            ));
            $c->select(array('id', 'email'));

            if ($c->prepare() && $c->stmt->execute()) {
                $result = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($result as $u) {
                    $emails[] = $u['email'];
                }
            }
        }

        return $emails;
    }

    /**
     * Get conversation by conversation name
     *
     * @param string $name Conversation name
     *
     * @return object $conversation
     */
    public function getConversation($name = '') {
        /**
         * If Conversation name is empty or not defined return False
         */
        if (empty($name)) {
            return false;
        }
        /**
         * Conversation in cache TRUE or FALSE
         */
        $cCache = false;
        $cache = $this->modx->getCacheManager();
        // Create a key by conversation name
        if (!$keyConversation = $this->conversationHash($name)) {
            return false;
        }

        // If there is a cache, set the flag to TRUE ConversationCache, otherwise FALSE
        if ($this->mtCache && $cache) {
            if ($theme = $this->modx->cacheManager->get($keyConversation, array(
                xPDO::OPT_CACHE_KEY => 'modxtalks/conversation'))
            ) {
                $cCache = true;
                $conversation = $this->modx->newObject('modxTalksConversation', $theme);
                $conversation->set('id', $theme['id']);
            } else {
                $cCache = false;
            }
        }

        // If the flag is in ConversationCache FALSE - get data from database
        if ($cCache === false) {
            // If the key is not section, create a new
            if (!$conversation = $this->modx->getObject('modxTalksConversation', array('conversation' => $name))) {
                $conversation = $this->modx->newObject('modxTalksConversation', array('conversation' => $name));
                $properties = array(
                    'total' => 0,
                    'deleted' => 0,
                    'unconfirmed' => 0,
                );
                $conversation->setProperties($properties, 'comments', false);
                $conversation->setSingleProperty($this->modx->resource->id);
                $conversation->set('rid', $this->modx->resource->id);
                $conversation->save();
            }

            // Put to the cache
            if ($this->mtCache && $cache)
                $this->modx->cacheManager->set($keyConversation, $conversation, 0, array(
                    xPDO::OPT_CACHE_KEY => 'modxtalks/conversation'
                ));
        }

        return $conversation;
    }

    /**
     * Cache conversation
     *
     * @param object $conversation Conversation object
     *
     * @return bool true|false
     */
    public function cacheConversation(modxTalksConversation & $conversation) {
        /**
         * If $conversation is empty or not defined return False
         */
        if (empty($conversation))
            return false;

        $cache = $this->modx->getCacheManager();
        if ($this->mtCache && $cache) {
            if (!$keyConversation = $this->conversationHash($conversation->conversation)) {
                return false;
            }

            if (!$this->modx->cacheManager->set($keyConversation, $conversation, 0, array(xPDO::OPT_CACHE_KEY => 'modxtalks/conversation'))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieve comment or comments from cache if it's cached or database
     *
     * @param string|array $ids Comment idx or Comments idx'es
     * @param int          $conversationId Comment ID or Comments IDs
     *
     * @return array array[0] - comments, array[1] - users
     */
    public function getCommentsArray($ids, $conversationId) {
        if (empty($ids) || empty($conversationId)) {
            return false;
        }

        if (!is_array($ids)) {
            $ids = array($ids);
        }
        /**
         * @var array Result Comments array
         */
        $comments = array();
        /**
         * @var array Non cached comments
         */
        $nonCached = array();
        /**
         * @var boolean True if one or more comments not cached
         */
        $cached = false;
        $cache = $this->modx->getCacheManager();
        /**
         * Retrieve comments from cache
         * те которых нет пишем в массив $nonCached для дальнейшего получения из базы
         */
        if ($this->mtCache && $cache) {
            $cached = true;
            foreach ($ids as $id) {
                if ($comment = $this->modx->cacheManager->get($id, array(
                    xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/' . $conversationId
                ))
                ) {
                    $comments[$id] = $comment;
                } else {
                    $nonCached[] = $id;
                }
            }

            if (count($nonCached)) {
                $cached = false;
            }
        }

        /**
         * Get comments by idx and conversation Id
         */
        if ($cached === false) {
            $c = $this->modx->newQuery('modxTalksPost', array('conversationId' => $conversationId));
            $c->select(array('id', 'idx', 'conversationId', 'date', 'content', 'userId', 'time', 'deleteTime', 'deleteUserId', 'editTime', 'editUserId', 'username', 'useremail', 'ip', 'votes', 'properties'));
            if (count($nonCached) && $this->mtCache && $cache) {
                $c->andCondition(array(
                    'idx:IN' => $nonCached
                ));
            } else {
                $c->andCondition(array(
                    'idx:IN' => $ids
                ));
            }

            if ($c->prepare() && $c->stmt->execute()) {
                $results = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($results as $result) {
                    /**
                     * Comment raw content
                     */
                    $result['raw_content'] = $result['content'];
                    /**
                     * Comment fully processed content
                     */
                    $result['content'] = $this->bbcode($result['content']);
                    $comments[$result['idx']] = $result;
                    /**
                     * Cache the comment
                     */
                    if ($this->mtCache && $cache) {
                        $this->modx->cacheManager->set($result['idx'], $result, 0, array(
                            xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/' . $conversationId
                        ));
                    }
                }
            }
        }
        /**
         * Get id's of registered users
         */
        $users = array();
        foreach ($comments as $c) {
            if ($c['userId']) {
                $users[] = $c['userId'];
            }

            if ($c['deleteUserId']) {
                $users[] = $c['deleteUserId'];
            }

            if ($c['editUserId']) {
                $users[] = $c['editUserId'];
            }
        }
        $users = array_unique($users);

        /**
         * Sort array ascending by idx
         */
        if (!$this->revers) {
            ksort($comments);
        } else {
            krsort($comments);
        }

        return array($comments, $users);
    }

    /**
     * Cache the comment
     *
     * @param object $comment Comment object
     *
     * @return bool
     */
    public function cacheComment(modxTalksPost & $comment) {
        $cache = $this->modx->getCacheManager();
        if ($this->mtCache && $cache) {
            $tmp = $comment->toArray('', true);
            $tmp['raw_content'] = $comment->content;
            $tmp['content'] = $this->bbcode($comment->content);
            if (!$this->modx->cacheManager->set($comment->idx, $tmp, 0, array(
                xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/' . $comment->conversationId
            ))
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete comment cache
     *
     * @param object $comment Comment object
     *
     * @return bool
     */
    public function deleteCommentCache(modxTalksPost & $comment) {
        $cache = $this->modx->getCacheManager();
        if ($this->mtCache && $cache) {
            if (!$this->modx->cacheManager->delete($comment->idx, array(
                xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/' . $comment->conversationId
            ))
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete all comments cache
     *
     * @param int $id Conversation ID
     *
     * @return bool
     */
    public function deleteAllCommentsCache($id = 0) {
        $cache = $this->modx->getCacheManager();
        if ($this->mtCache && $cache) {
            if ($this->modx->cacheManager->refresh(array(
                'modxtalks/conversation/' . $id => array()
            ))
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get user or users
     *
     * @param int|array $ids User id or Users id's
     *
     * @return array $comments
     */
    public function getUsers($ids) {
        /**
         * If Users Ids is empty or not defined return False;
         */
        if (empty($ids)) {
            return false;
        }

        /**
         * @var array Result Users array
         */
        $users = array();
        /**
         * @var array Non cached comments
         */
        $nonCached = array();
        /**
         * Получаем пользователей из кэша
         * те которых нет в кэше, пишем в массив $nonCached для дальнейшего получения из базы
         */
        foreach ($ids as $id) {
            if ($user = $this->modx->cacheManager->get($id, array(xPDO::OPT_CACHE_KEY => 'modxtalks/users/'))) {
                $users[$id] = $user;
            } else {
                $nonCached[] = $id;
            }
        }

        if (count($nonCached) === 0) {
            return $users;
        }

        /**
         * Get user by id
         */
        $c = $this->modx->newQuery('modUser');
        $c->select(array('modUser.id', 'modUser.username', 'p.email', 'p.fullname'));
        $c->leftjoin('modUserProfile', 'p', 'modUser.id = p.internalKey');

        if (count($nonCached) > 1) {
            $c->andCondition(array(
                'modUser.id:IN' => $nonCached
            ));
        } else {
            $c->andCondition(array(
                'modUser.id' => array_shift($nonCached)
            ));
        }

        if ($c->prepare() && $c->stmt->execute()) {
            $cache = $this->modx->getCacheManager();
            $results = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as $result) {
                $users[$result['id']] = $result;
                /**
                 * Cache user data
                 */
                if ($this->mtCache && $cache) {
                    $this->modx->cacheManager->set($result['id'], $result, 0, array(
                        xPDO::OPT_CACHE_KEY => 'modxtalks/users'));
                }
            }
        }

        return $users;
    }

    /**
     * Cache user data
     *
     * @param modUser $user User object
     *
     * @return bool
     */
    public function cacheUser(modUser & $user) {
        $cache = $this->modx->getCacheManager();

        if ($this->mtCache && $cache) {
            $profile = $user->getOne('Profile');
            $tmp = array(
                'id' => $user->id,
                'username' => $user->username,
                'email' => $profile->email,
                'fullname' => $profile->fullname,
            );

            if (!$this->modx->cacheManager->set($user->id, $tmp, 0, array(
                xPDO::OPT_CACHE_KEY => 'modxtalks/users'
            ))
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get index of comment by date
     *
     * @param int    $conversationId Conversation ID
     * @param string $date Conversation date index
     *
     * @return bool
     */
    public function getDateIndex($conversationId, $date) {
        if (empty($conversationId) || empty($date) || $date !== date('Y-m', strtotime($date))) {
            return false;
        }

        $date = str_replace('-', '', $date);
        $index = null;

        /**
         * @var boolean True if comment not cached
         */
        $cached = false;
        $cache = $this->modx->getCacheManager();
        if ($this->mtCache && $cache) {
            if ($index = $this->modx->cacheManager->get($date, array(
                xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/' . $conversationId . '/dates'
            ))
            ) {
                $cached = true;
            }
        }

        if ($cached == false) {
            $c = $this->modx->newQuery('modxTalksPost', array(
                'conversationId' => $conversationId,
                'date' => $date
            ));
            $c->sortby('idx', 'ASC');

            if (!$index = $this->modx->getObject('modxTalksPost', $c)) {
                return false;
            }

            $index = $index->get('idx');

            if ($this->mtCache && $cache) {
                $this->modx->cacheManager->set($date, $index, 0, array(
                    xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/' . $conversationId . '/dates'
                ));
            }
        }

        return $index;
    }

    /**
     * Create RSS Feed of latest comments
     *
     * @param string $conversation Conversation name
     * @param int    $limit Comments to show
     *
     * @return string
     */
    public function createRssFeed($conversation, $limit) {
        /**
         * Check for conversation present
         */
        if (!$theme = $this->getConversation($conversation)) {
            return '';
        }

        $count = $theme->getProperty('total', 'comments');
        if ($count < 1) {
            return '';
        }

        $limit = (int) $limit;
        if (empty($limit)) {
            $limit = $this->config['commentsPerPage'];
        }

        $range = range($count - $limit + 1, $count);
        $range = array_reverse($range);
        /**
         * RSS Items template
         */
        $item_tpl = $this->config['rssItemTpl'];
        /**
         * RSS Items Wrapper template
         */
        $feed_tpl = $this->config['rssFeedTpl'];
        /**
         * Get comments
         */
        $comments = $this->getCommentsArray($range, $theme->get('id'));
        /**
         * Get resource URL
         */
        $link = $this->modx->getOption('site_url') . $this->modx->resource->uri;
        if (!$comments[0] && $count > 0) {
            $this->modx->sendRedirect($link);
        }

        $guest_name = $this->modx->lexicon('modxtalks.guest');

        $usersIds = &$comments[1];
        $users = array();

        /**
         * Get registered Users
         */
        if (count($usersIds)) {
            $authUsers = $this->getUsers($usersIds);
            foreach ($authUsers as $a) {
                $users[$a['id']] = array(
                    'name' => $a['fullname'] ? $a['fullname'] : $a['username'],
                    'email' => $a['email'],
                );
            }
        }

        $output = '';
        $comments[0] = array_reverse($comments[0]);
        foreach ($comments[0] as $comment) {
            $date = date($this->config['dateFormat'] . ' O', $comment['time']);

            // If this is registered user
            if ($comment['userId'] > 0) {
                $name = $users[$comment['userId']]['name'];
                $email = $users[$comment['userId']]['email'];
            } // If this is guest
            else {
                $name = $comment['username'] ? $comment['username'] : $guest_name;
                $email = $comment['useremail'] ? $comment['useremail'] : 'anonym@anonym.com';
            }

            $tmp = array(
                'name' => $name,
                'content' => $this->modx->stripTags($comment['content']),
                'date' => $date,
                'link' => $this->getLink($comment['idx']),
            );

            $output .= $this->getChunk($item_tpl, $tmp);

        }

        $output = $this->getChunk($feed_tpl, array(
            'content' => $output,
            'year' => date("Y")
        ));

        return $output;
    }

    /**
     * Create conversations map
     *
     * @return mixed False if cache is off
     */
    public function conversationsMap() {
        /**
         * If cache is disabled return False
         */
        if (!$this->mtCache) {
            return false;
        }
        /**
         * If conversation in cache return it
         */
        if ($map = $this->modx->cacheManager->get('conversations_map', array(
            xPDO::OPT_CACHE_KEY => 'modxtalks'
        ))
        ) {
            return $map;
        }
        /**
         * Else if get it from database and put in cache
         */
        $map = array();
        $c = $this->modx->newQuery('modxTalksConversation');
        $c->select(array('id', 'rid', 'conversation'));

        if ($c->prepare() && $c->stmt->execute()) {
            $conversations = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($conversations as $c) {
                $map[$c['rid']][$c['id']] = $c['conversation'];
            }

            $this->modx->cacheManager->set('conversations_map', $map, 0, array(
                xPDO::OPT_CACHE_KEY => 'modxtalks'
            ));

        }

        return $map;
    }

    /**
     * Check resource have conversations
     *
     * @param int $id Resource ID
     *
     * @return bool True if resource have a conversation
     */
    public function hasConversations($id) {
        if (!intval($id))
            return false;
        /**
         * Check in conversation map
         */
        if ($map = $this->conversationsMap()) {
            /**
             * If resource has more then one conversation return True
             */
            if (array_key_exists($id, $map)) {
                return true;
            }

            return false;
        } elseif ($this->modx->getCount('modxTalksConversation', array('rid' => $id))) {
            /**
             * If method conversationsMap() returned false (if cache is enabled), check in database
             */
            return true;
        }

        return false;
    }

    /**
     * Refresh comment and conversation cache
     *
     * @param modxTalksPost         $comment
     * @param modxTalksConversation $conversation
     *
     * @return bool
     */
    public function refreshCommentCache(modxTalksPost & $comment, modxTalksConversation & $conversation) {
        if ($this->mtCache === true) {
            if (!$this->cacheComment($comment)) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks::refreshCommentCache] Error cache the comment with ID ' . $comment->id);
            }

            if (!$this->cacheConversation($conversation)) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks::refreshCommentCache] Error cache the conversation with ID ' . $conversation->id);
            }
        }

        return true;
    }

    /**
     * Generate comment URL
     *
     * @param int    $cid Conversation Id
     * @param int    $idx Comment Idx
     * @param string $scheme URL scheme - full or abs
     *
     * @return string Comment URL
     */
    public function generateLink($cid = 0, $idx = 0, $scheme = 'full') {
        $conversation = $this->modx->getObject('modxTalksConversation', $cid);
        $rid = $conversation->rid;

        if ($scheme !== 'full') {
            $scheme = 'abs';
        }

        $ctx = $this->context;

        if ($this->context === 'mgr') {
            $config = $this->getProperties($conversation->conversation);
            $ctx = isset($config['context']) ? $config['context'] : $this->context;
        }

        if ($this->modx->getOption('site_start') == $rid) {
            $url = $scheme !== 'full' ? '/' : $this->modx->getOption('site_url');
        } else {
            $url = $this->modx->makeUrl($rid, $ctx, '', $scheme);
        }

        $slug = '#comment-' . $this->config['slugReplace'];

        $link = $url . $slug;

        if (intval($idx) > 0) {
            $link = str_replace($this->config['slugReplace'], $idx, $link);
        }

        return $link;
    }

    /**
     * Slice string
     *
     * @param string $input String to slice
     * @param int    $limit String limit. Default = 200
     *
     * @return string $output
     */
    public function slice($input, $limit = 200) {
        $limit = $limit ? $limit : 200;
        $enc = 'UTF-8';
        $len = mb_strlen($input, $enc);
        if ($limit > $len) {
            $limit = $len;
        }

        return trim(mb_substr($input, 0, $limit, $enc)) . ($limit == $len ? '' : '...');
    }

    /**
     * Get Latest comments
     *
     * @return string $output
     */
    public function getLatestComments() {
        $this->modx->regClientCSS($this->config['cssUrl'] . 'web/mt_cl.css');
        $this->modx->regClientScript($this->config['jsUrl'] . 'web/mt_cl.js');
        $this->modx->regClientStartupHTMLBlock('<script>var MTL = {
        connectorUrl: "' . $this->config['ajaxConnectorUrl'] . '",
        limit: ' . $this->config['commentsLatestLimit'] . ',
        updateInterval: ' . $this->config['lates_comments_update'] . "\n"
            . '};</script>');

        $comments = $this->modx->runProcessor('web/comments/latest', array(), array(
            'processors_path' => $this->config['processorsPath']
        ));
        $comments =& $comments->response['results'];

        $output = '';
        foreach ($comments as $c) {
            $output .= $this->parseTpl($this->config['commentLatestTpl'], $c, true);
        }

        $output = $this->parseTpl($this->config['commentsLatestOutTpl'], array(
            'output' => $output
        ), true);

        return $output;
    }

    /**
     * Slice string by words count
     *
     * @param string $string
     * @param string $count
     *
     * @return string
     */
    public function sliceStringByWords($string, $count) {
        $words = preg_split('@[\s\r\n]+@um', $string);
        if ($count < count($words)) {
            $words = array_slice($words, 0, $count);
        }

        return implode(' ', $words);
    }

    /**
     * Prepare data for Ajax requests
     */
    public function ajaxInit() {
        if (empty($this->config['conversation'])) {
            $this->config['conversation'] = $this->modx->resource->class_key . '-' . $this->modx->resource->id;
        }

        $conversation = $this->validateConversation($this->config['conversation']);
        if ($conversation !== true) {
            return $conversation;
        }

        $this->ejsTemplates();

        $this->context = $this->modx->context->key;
    }

    /**
     * Validate conversation name
     *
     * @param string $value Conversation name
     *
     * @return mixed True if conversation name is valid or error message
     */
    public function validateConversation($value = '') {
        if (preg_match('@[^a-zA-z-_.0-9]@i', $value)) {
            return $this->modx->lexicon('modxtalks.unallowed_symbols');
        } elseif (strlen($value) < 2 || strlen($value) > 63) {
            return $this->modx->lexicon('modxtalks.bad_id');
        }

        return true;
    }

    /**
     * Cache snippet start properties
     *
     * @param string $conversation Conversation name
     * @param array  $config Config to cache
     *
     * @return bool False if conversation name is invalid
     */
    public function cacheProperties($conversation = null, $config = array()) {
        if (!$keyConversation = $this->conversationHash($conversation)) {
            return false;
        }

        $path = 'modxtalks/properties';
        $config['context'] = $this->context;
        $this->modx->cacheManager->set($keyConversation, $config, 0, array(
            xPDO::OPT_CACHE_KEY => $path,
            xPDO::OPT_CACHE_HANDLER => 'xPDOFileCache',
        ));
    }

    /**
     * Get snippet properties from cache
     *
     * @param string $conversation Conversation name
     *
     * @return array|boolean False if conversation name is invalid
     */
    public function getProperties($conversation = null) {
        if (!$keyConversation = $this->conversationHash($conversation)) {
            return false;
        }

        $path = 'modxtalks/properties';
        $config = $this->modx->cacheManager->get($keyConversation, array(
            xPDO::OPT_CACHE_KEY => $path,
            xPDO::OPT_CACHE_HANDLER => 'xPDOFileCache',
        ));

        return is_array($config) ? $config : array();
    }

    /**
     * Generate conversation hash by conversation name
     *
     * @param string $conversation Conversation name
     *
     * @return string Hash of conversation name
     */
    public function conversationHash($conversation = null) {
        if (empty($conversation))
            return false;

        return md5('modxtalks::' . $conversation);
    }

    /**
     * Check IP for block
     *
     * @param string $action
     * @param array  $allowedActions Array of allowed actions to check
     *
     * @return mixed True if IP address not blocked
     */
    public function checkIp($action, $allowedActions = array()) {
        if (!in_array($action, $allowedActions)) {
            $ip = $this->get_client_ip();
            $ip = explode('.', $ip);
            $ipArr = array(
                $ip[0] . '.',
                $ip[0] . '.' . $ip[1] . '.',
                $ip[0] . '.' . $ip[1] . '.' . $ip[2] . '.',
                $ip[0] . '.' . $ip[1] . '.' . $ip[2] . '.' . $ip[3]
            );

            if ($this->modx->getCount('modxTalksIpBlock', array('ip:IN' => $ipArr))) {
                return '{"message":"' . $this->modx->lexicon('modxtalks.ip_blacklist_confirm') . '","success":false}';
            }
        }

        return true;
    }

    /**
     * Get Revers
     *
     * @return bool
     */
    public function getRevers() {
        return $this->revers;
    }

    /**
     * Get current Context Key
     *
     * @return string
     */
    public function getContext() {
        return $this->context;
    }

    /**
     * Send Mail
     *
     * @param int $commentId
     *
     * @return bool
     */
    public function sendMail($commentId = 0) {
        if (!intval($commentId)) {
            return false;
        }

        $mail = $this->modx->newObject('modxTalksMails', array(
            'post_id' => $commentId
        ));

        if ($mail->save() !== true) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks::sendMail] Error add mail with ID ' . $commentId);

            return false;
        }

        $mailer = $this->config['basePath'] . 'mailer.php';

        if (!$this->isDisabledFunction('exec')) {
            exec('php ' . $mailer . ' > ' . $this->config['basePath'] . 'error.log &');
        }

        return true;
    }

    /**
     * Check for function disabled or not
     *
     * @param $name
     *
     * @return bool
     */
    public function isDisabledFunction($name) {
        static $disabledFunctions;
        if (!$disabledFunctions) {
            $disabledFunctions = ini_get('disable_functions');
            $delimiter = strpos($disabledFunctions, ',') !== false ? ',' : ' ';
            $disabledFunctions = explode($delimiter, $disabledFunctions);
            $disabledFunctions = array_map('trim', $disabledFunctions);
        }

        return in_array($name, $disabledFunctions);
    }
}
