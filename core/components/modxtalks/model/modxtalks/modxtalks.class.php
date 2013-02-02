<?php
/**
 * This file is part of modxTalks, a simple commenting component for MODx Revolution.
 *
 * @copyright Copyright (C) 2013, Artdevue Ltd, <info@artdevue.com>
 * @author Valentin Rasulov <info@artdevue.com> && Ivan Brezhnev <npobolka@gmail.com>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @package modxtalks
 *
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
     * @var array An array of chunks
     */
    public $chunks = array();
    /**
     * @var string Context key
     */
    private $context = 'web';
    /**
     * @var boolean Enable or Disable modxTalks Comments cache
     */
    public $mtCache = true;
    private $mtConfig = array();
    private $friendly_urls;
    private $slug;
    private $lang;

    /**
     * Constructs the modxTalks object
     *
     * @param modX &$modx A reference to the modX object
     * @param array $config An array of configuration options
     */
    function __construct(modX &$modx, array $config = array()) {
        $this->modx =& $modx;

        $basePath = $this->modx->getOption('modxtalks.core_path',$config,$this->modx->getOption('core_path').'components/modxtalks/');
        $assetsUrl = $this->modx->getOption('modxtalks.assets_url',$config,$this->modx->getOption('assets_url').'components/modxtalks/');
        $siteUrl = $this->modx->getOption('server_protocol').'://'.$this->modx->getOption('http_host');

        $this->config = array(
            'basePath' => $basePath,
            'corePath' => $basePath,
            'modelPath' => $basePath.'model/',
            'processorsPath' => $basePath.'processors/',
            'templatesPath' => $basePath.'templates/',
            'ejsTemplatesPath' => $_SERVER['DOCUMENT_ROOT'].$assetsUrl.'ejs/',
            'chunksPath' => $basePath.'elements/chunks/',
            'jsUrl' => $assetsUrl.'js/',
            'cssUrl' => $assetsUrl.'css/',
            'assetsUrl' => $assetsUrl,
            'imgUrl' => $assetsUrl.'img/web/',
            'connectorUrl' => $assetsUrl.'connector.php',

            'moderator' => $this->modx->getOption('modxtalks.moderator',null,'Administrator'),
            'onlyAuthUsers' => (boolean) $this->modx->getOption('modxtalks.onlyAuthUsers',null,false),
            'mtGravator' => (boolean) $this->modx->getOption('modxtalks.mtGravator',null,true),
            'mtgravatarSize' => (int) $this->modx->getOption('modxtalks.mtgravatarSize',null,64),
            'mtgravatarUrl' => 'http://www.gravatar.com/avatar/',
            'defaultAvatar' => $siteUrl.$assetsUrl.'img/web/avatar.png',

            'mtDateFormat' => $this->modx->getOption('modxtalks.mtDateFormat',null,'j-m-Y, G:i'),
            'startFrom' => 0,
            'lastRead' => 0,
            'commentsPerPage' => (int) $this->modx->getOption('modxtalks.commentsPerPage',null,20),
            'add_timeout' => (int) $this->modx->getOption('modxtalks.add_timeout',null,60),
            'edit_time' => (int) $this->modx->getOption('modxtalks.edit_time',null,180),
            'commentsClosed' => (boolean) $this->modx->getOption('modxtalks.commentsClosed',null,false),

            // Templates
            'commentTpl' => 'comment',
            'deletedCommentTpl' => 'deleted_comment',
            'commentAddFormTpl' => 'commentform',
            'commentEditFormTpl' => 'edit_commentform',
            'commentAuthTpl' => $this->modx->getOption('modxtalks.commentAuthTpl',null,'comment_auth_tpl'),
            'user_info' => 'user_info',
            'useChunks' => (boolean) $this->modx->getOption('modxtalks.useChunks',null,false),

            // RSS Templates
            'rssItemTpl' => 'tpl_rss_item',
            'rssFeedTpl' => 'tpl_rss_feed',

            // BBCode options
            'bbcode' => (boolean) $this->modx->getOption('modxtalks.bbcode',null,true),
            'editOptionsControls' => $this->modx->getOption('modxtalks.editOptionsControls',null,'fixed,image,link,strike,header,italic,bold,video,quote'),
            'smileys' => (boolean) $this->modx->getOption('modxtalks.smileys',null,true),
            'detectUrls' => (boolean) $this->modx->getOption('modxtalks.detectUrls',null,true),
            'videoSize' => array(500,350),
            'commentLength' => (int) $this->modx->getOption('modxtalks.commentLength',null,2000),

            'slug' => '',
            'slugReplace' => '$$$',

            // Debug
            'debug' => (boolean) $this->modx->getOption('modxtalks.debug',null,false),

            // Scrubber position
            'scrubberTop' => (boolean) $this->modx->getOption('modxtalks.scrubberTop',null,false),
            'scrubberOffsetTop' => (int) $this->modx->getOption('modxtalks.scrubberOffsetTop',null,0),
        );

        // if videoSize not array
        if (!is_array($this->config['videoSize']) && strpos($this->config['videoSize'], ',')) {
            $this->config['videoSize'] = explode(',', $this->config['videoSize']);
        }

        if (isset($config['commentsPerPage']) && intval($config['commentsPerPage'])) {
            $config['commentsPerPage'] = (int) $config['commentsPerPage'];
        }

        $this->config = array_merge($this->config, $config);

        $this->friendly_urls = (boolean) $this->modx->getOption('friendly_urls');

        $this->modx->addPackage('modxtalks',$this->config['modelPath']);
        $this->lang = $this->modx->getOption('cultureKey');
        $this->modx->lexicon->load('modxtalks:default');
    }

    public function setSlug($slug='') {
        $this->slug = (string) $slug;
    }

    public function getSlug() {
        return $this->slug;
    }

    /**
     * Initializes modxTalks based on a specific context
     *
     * @access public
     * @param string $ctx The context to initialize in
     * @return boolean
     */
    public function initialize($ctx = 'web') {
        switch ($ctx) {
            case 'mgr':
                if (!$this->modx->loadClass('modxtalksControllerRequest',$this->config['modelPath'].'modxtalks/request/',true,true)) {
                    return 'Could not load controller request handler.';
                }
                $this->request = new modxtalksControllerRequest($this);
                return $this->request->handleRequest();
            break;
        }
        return true;
    }

    /**
     * Initialize
     *
     * @access public
     * @param integer $limit Commets per request
     * @param string $id Optional
     * @return string HTML
     */
    public function init($limit = '', $id = '') {
        if ($this->config['debug'] && $this->modx->getOption('log_target') === 'HTML') {
            $time = microtime(true);
            $startMemory = memory_get_usage()/pow(1024,2);
            $this->pr(sprintf('Memory Before: %2.2f Mbytes',$startMemory));
            $this->pr("Your IP: {$this->get_client_ip()}");
        }

        if (empty($this->config['conversation'])) return '';
        $conversation = $this->config['conversation'];

        $this->context = $this->modx->context->key;

        if (isset($_REQUEST['rss']) && $_REQUEST['rss'] == true) {
            header('Content-Type: application/rss+xml; charset=utf-8');
            $rssFeed = $this->createRssFeed($conversation,$limit);
            die($rssFeed);
        }


        if (isset($_REQUEST['comment'])) $id = (string) $_REQUEST['comment'];

        $limit = (int) $limit;
        if (empty($limit)) {
            $limit = $this->config['commentsPerPage'];
        }

        $comments = $this->getComments($conversation, $limit, $id);

        /**
         * Create a "more" block item which we can use below.
         */
        if ($this->config['startFrom'] > 1) {
            $linkPrev = $this->config['startFrom'] <= $this->config['commentsPerPage'] ? 1 : $this->config['startFrom'] - $this->config['commentsPerPage'];
            $comments = '<div class="scrubberMore scrubberPrevious"><a href="'.$this->getLink($linkPrev).'#conversationPosts">'.$this->modx->lexicon('modxtalks.more_text').'</a></div>'.$comments;
        }
        if ($this->config['startFrom'] + $this->config['commentsPerPage'] <= $this->config['commentsCount']) {
            $comments .= '<div class="scrubberMore scrubberNext"><a href="'.$this->getLink(($this->config['lastRead']+1)).'#mt_cf_conversationReply">'.$this->modx->lexicon('modxtalks.more_text').'</a></div>';
        }

        $this->_regStyles();
        /**
         * Enable Ajax
         */
        if ($this->modx->getOption('modxtalks.ajax',null,true)) {
            $this->_regScripts();
            $this->_getScriptHead();
            $this->_ejsTemplates();
        }

        $output = '<div id="conversationBody" class="hasScrubber'.($this->config['scrubberTop']?' scrubber-top':'').'"><div class="mthead"></div><div>';
        $output .= $this->_parseTpl('scrubber', $this->getScrubber(), true);
        $output .= '<div id="conversationPosts" class="postList" start="'.$this->config['startFrom'].'">';
        $output .= $comments;
        $output .= '</div>';
        $output .= $this->_getForm();
        $output .= '</div></div>';

        if ($this->config['debug'] && $this->modx->getOption('log_target') === 'HTML') {
            $this->pr(sprintf('Time: %2.2f мс',(microtime(true) - $time)*1000));
            $this->pr(sprintf('Memory After: %2.2f Mbytes',memory_get_usage()/pow(1024,2)));
        }

        return $output;
    }

    /**
     * Add an event handler to the "getEditControls" method of the conversation controller to add BBCode
     * formatting buttons to the edit controls.
     *
     * @param string $id
     * @access public
     * @return string $buttons
     */
    public function getEditControls($id = 'reply') {
        $editControls = array(
            "fixed" => "<a href='javascript:BBCode.fixed(\"$id\");void(0)' title='' class='bbcode-fixed'><span>".$this->modx->lexicon('modxtalks.fixed')."</span></a>",
            "image" => "<a href='javascript:BBCode.image(\"$id\");void(0)' title='".$this->modx->lexicon('modxtalks.image')."' class='bbcode-img'><span>".$this->modx->lexicon('modxtalks.image')."</span></a>",
            "link" => "<a href='javascript:BBCode.link(\"$id\");void(0)' title='".$this->modx->lexicon('modxtalks.link')."' class='bbcode-link'><span>".$this->modx->lexicon('modxtalks.link')."</span></a>",
            "strike" => "<a href='javascript:BBCode.strikethrough(\"$id\");void(0)' title='".$this->modx->lexicon('modxtalks.strike')."' class='bbcode-s'><span>".$this->modx->lexicon('modxtalks.strike')."</span></a>",
            "header" => "<a href='javascript:BBCode.header(\"$id\");void(0)' title='".$this->modx->lexicon('modxtalks.header')."' class='bbcode-h'><span>".$this->modx->lexicon('modxtalks.header')."</span></a>",
            "italic" => "<a href='javascript:BBCode.italic(\"$id\");void(0)' title='".$this->modx->lexicon('modxtalks.italic')."' class='bbcode-i'><span>".$this->modx->lexicon('modxtalks.italic')."</span></a>",
            "bold" => "<a href='javascript:BBCode.bold(\"$id\");void(0)' title='".$this->modx->lexicon('modxtalks.bold')."' class='bbcode-b'><span>".$this->modx->lexicon('modxtalks.bold')."</span></a>",
            "video" => "<a href='javascript:BBCode.video(\"$id\");void(0)' title='".$this->modx->lexicon('modxtalks.video')."' class='bbcode-v'><span>".$this->modx->lexicon('modxtalks.video')."</span></a>",
            "quote" => "<a href='javascript:BBCode.quote(\"$id\");void(0)' title='".$this->modx->lexicon('modxtalks.quote')."' class='bbcode-q'><span>".$this->modx->lexicon('modxtalks.quote')."</span></a>",
            );
        if (!$this->config['bbcode']) {
            return $editControls['quote'];
        }
        $editOptionsControls = $this->config['editOptionsControls'];
        $editOptionsControlsArray = explode(',', $editOptionsControls);
        $buttons = '';
        foreach ($editOptionsControlsArray as $b) {
            $b = trim($b);
            if (array_key_exists($b, $editControls)) {
                $buttons .= $editControls[$b];
            }
        }
        return $buttons;
    }

    /**
     * Add JavaScript language definitions and variables
     *
     * @access private
     * @return string true
     */
    private function _regScripts() {
        // Check the settings, turn jQquery
        if ($this->modx->getOption('modxtalks.jquery',null,true)) {
            $this->modx->regClientScript($this->config['jsUrl'].'web/lib/jquery.js');
        }
        $this->modx->regClientScript($this->config['jsUrl'].'web/lib/jquery.history.js');
        $this->modx->regClientScript($this->config['jsUrl'].'web/lib/timeago.js');

        // Localization for timeago plugin
        $this->modx->regClientScript($this->config['jsUrl'].'web/lib/timeago/'.$this->lang.'.js');

        $this->modx->regClientScript($this->config['jsUrl'].'web/lib/jquery.autogrow.js');
        $this->modx->regClientScript($this->config['jsUrl'].'web/lib/jquery.misc.js');
        $this->modx->regClientScript($this->config['jsUrl'].'web/lib/jquery.scrollTo.js');
        $this->modx->regClientScript($this->config['jsUrl'].'web/global.js');
        $this->modx->regClientScript($this->config['jsUrl'].'web/autocomplete.js');
        $this->modx->regClientScript($this->config['jsUrl'].'web/script.js');
        $this->modx->regClientScript($this->config['jsUrl'].'web/scrubber.js');
        $this->modx->regClientScript($this->config['jsUrl'].'web/ejs_production.js');

        // Add a button at quoting the resource allocation in the footer
        $this->modx->regClientHTMLBlock('<div id="MTpopUpBox"><span class="">'.$this->modx->lexicon('modxtalks.quote_text').'</span></div>');

        // Check the settings, turn BBCode
        if ($this->config['bbcode']) {
            $this->modx->regClientScript($this->config['jsUrl'].'web/bbcode/bbcode.js');
        }
        // Check the settings, turn Highlight
        if ($this->modx->getOption('modxtalks.highlight',null,false)) {
            $this->modx->regClientScript($this->config['jsUrl'].'web/highlight.pack.js');
            $this->modx->regClientCSS($this->config['cssUrl'].'web/highlight/'.strtolower($this->modx->getOption('modxtalks.highlighttheme',null,'GitHub')).'.css');
        }
        return true;
    }

    /**
     * Add Styles to head
     *
     * @access private
     * @return string true
     */
    private function _regStyles() {
        $this->modx->regClientCSS($this->config['cssUrl'].'web/bbcode/bbcode.css');
        $this->modx->regClientCSS($this->config['cssUrl'].'web/styles.css');
        return true;
    }

    /**
     * Get Script Head
     *
     * @access private
     * @return string true and add script to head
     */
    private function _getScriptHead(){
        $this->modx->regClientStartupHTMLBlock('<script>var MT = {
        "assetsPath":"'.$this->config['assetsUrl'].'",
        "conversation":"'.$this->config['conversation'].'",
        "ctx":"'.$this->context.'",
        "link": "'.$this->modx->getOption('site_url').$this->modx->resource->uri.'",
        "webPath": "'.MODX_BASE_URL.'",
        "token": "'.md5($_COOKIE['PHPSESSID']).'",
        "debug": false,
        "commentTpl": "ejs/'.$this->config['commentTpl'].'.ejs'.'",
        "deletedCommentTpl": "ejs/'.$this->config['deletedCommentTpl'].'.ejs'.'",
        "scrubberOffsetTop": '.$this->config['scrubberOffsetTop'].',
        "language": {
            "message.ajaxRequestPending": "'.$this->modx->lexicon('modxtalks.ajax_request_pending').'",
            "message.ajaxDisconnected": "'.$this->modx->lexicon('modxtalks.ajax_disconnected').'",
            "Loading...": "'.$this->modx->lexicon('modxtalks.loading').'...",
            "Notifications": "'.$this->modx->lexicon('modxtalks.notifications').'",
            "newComment": "'.$this->modx->lexicon('modxtalks.new_comment').'",
            "moreText": "'.$this->modx->lexicon('modxtalks.more_text').'",
            "message.confirmDelete": "'.$this->modx->lexicon('modxtalks.confirm_delete').'",
            "message.confirmLeave":"'.$this->modx->lexicon('modxtalks.confirmLeave').'",
            "message.confirmDiscardReply":"'.$this->modx->lexicon('modxtalks.confirm_discard_reply').'",
            "Mute conversation": "'.$this->modx->lexicon('modxtalks.mute_conversation').'",
            "Unmute conversation": "'.$this->modx->lexicon('modxtalks.unmute_conversation').'"
        },
        "notificationCheckInterval": 30,
        "postsPerPage": '.$this->config['commentsPerPage'].',
        "conversationUpdateIntervalStart": 10,
        "conversationUpdateIntervalMultiplier": 1.5,
        "conversationUpdateIntervalLimit": 512,
        "mentions": true,
        "time": "'.time().'",
        "mtconversation": {
            "conversationId": "'.$this->config['conversation'].'",
            "slug": "'.$this->config['slug'].'",
            "id": '.$this->config['conversationId'].',
            "countPosts": '.$this->config['commentsCount'].',
            "startFrom": '.$this->config['startFrom'].',
            "lastRead": '.$this->config['lastRead'].',
            "updateInterval": 182
        }
    }
    </script>');
        return '';

    }

    public function _ejsTemplates() {
        $commentTpl = $this->config['ejsTemplatesPath'].$this->config['commentTpl'].'.ejs';
        $deletedCommentTpl = $this->config['ejsTemplatesPath'].$this->config['deletedCommentTpl'].'.ejs';
        if (!file_exists($commentTpl)) {
            $tags = array(
                'index'           => '<%= index %>',
                'idx'             => '<%= idx %>',
                'id'              => '<%= id %>',
                'avatar'          => '<%= avatar %>',
                'name'            => '<%= name %>',
                'edit_name'       => '<%= edit_name %>',
                'link'            => '<%= link %>',
                'date'            => '<%= date %>',
                'funny_date'      => '<%= funny_date %>',
                'funny_edit_date' => '<%= funny_edit_date %>',
                'content'         => '<%= content %>',
                'user'            => '<%= user %>',
                'timeMarker'      => '<%= timeMarker %>',
                'hideAvatar'      => '<%= hideAvatar %>',
                'userId'          => '<%= userId %>',
                'timeago'         => '<%= timeago %>',
                'user_info'       => '<%= user_info %>',
                'link_reply'      => '',
                'quote'           => '',
            );
            $tpl = $this->_getTpl($this->config['commentTpl']);
            $data = preg_replace('@\s{2,}|\n@i', '', $this->_parseTpl($tpl, $tags));
            file_put_contents($commentTpl, $data);
        }
        if (!file_exists($deletedCommentTpl)) {
            $tags = array(
                'deleteUser'        => '<%= deleteUser %>',
                'delete_date'       => '<%= delete_date %>',
                'funny_delete_date' => '<%= funny_delete_date %>',
                'name'              => '<%= name %>',
                'index'             => '<%= index %>',
                'date'              => '<%= date %>',
                'funny_date'        => '<%= funny_date %>',
                'id'                => '<%= id %>',
                'idx'               => '<%= idx %>',
                'link_restore'      => '<%= link_restore %>',
                'timeMarker'        => '<%= timeMarker %>',
                'timeago'           => '<%= timeago %>',
                'deleted_by'        => '<%= deleted_by %>',
            );
            $tpl = $this->_getTpl($this->config['deletedCommentTpl']);
            $data = preg_replace('@\s{2,}|\n@i', '', $this->_parseTpl($tpl, $tags));
            file_put_contents($deletedCommentTpl, $data);
        }

        return true;
    }

    /**
     * Get Comments
     *
     * @access public
     * @param string $conversation Conversation Short name
     * @param integer $limit (Optional) A limit of records to retrieve in the collection
     * @param integer $id (Optional) Start comment ID
     * @return string $output Full processed comments
     */
    public function getComments($conversation = '', $limit = 20, $id = '') {
        if (empty($conversation)) return '';
        $output = '';

        /**
         * Check the cache section
         */
        if (!$theme = $this->getConversation($conversation)) {
            return $output;
        }

        $this->config['conversationId'] = $theme->get('id');
        if ($this->config['debug'] && $this->modx->getOption('log_target') === 'HTML') {
            $this->pr('ID темы: '.$this->config['conversationId']);
        }
        $this->config['commentsCount'] = 0;

        $this->config['slug'] = $this->generateLink($this->config['conversationId'],null,'abs');
        /*
        $this->pr($this->generateLink($this->config['conversationId'],null,'full'));
        $this->pr($this->config['slug']);
        */

        $count = $theme->getProperty('total','comments');
        if ($count < 1) return $output;

        /**
         * Get resource URL
         */
        $link = $this->modx->getOption('site_url').$this->modx->resource->uri;

        $totalPages = ceil($count / ($limit > 0 ? $limit : $count));
        if ($this->config['debug'] && $this->modx->getOption('log_target') === 'HTML') {
            $this->pr('Total pages: '.$totalPages);
        }

        $offset = 0;
        $this->config['startFrom'] = $this->config['lastRead'] = 1;
        if ($id === 'last' && $limit < $count) {
            $range = range($count - $limit + 1, $count);
        }
        elseif (ctype_digit($id)) {
            $id = intval($id) !== 0 ? intval($id) : 1;
            $first = floor(($id - 1) / $limit) * $limit + 1;
            $range = range($first, $first + $limit - 1);
            if ($id === 1) {
                $this->modx->sendRedirect($link);
            }
            $this->modx->sendRedirect($this->getLink('page_'.ceil($id / $limit)).'#comment-'.$id);
        }
        elseif ($id == date('Y-m', strtotime($id))) {
            $idx = $this->getDateIndex($theme->get('id'), date('Y-m', strtotime($id)));
            $range = range($idx, $idx + $limit);
        }
        elseif (preg_match('@page_(\d{1,4})@',$id,$page)) {
            $page = (int) $page[1];
            if ($page === 1) {
                $this->modx->sendRedirect($link);
            }
            if ($page > $totalPages) {
                $this->modx->sendRedirect($link);
            }
            $first = ($page - 1) * $limit + 1;
            $range = range($first, $first + $limit - 1);
        }
        else {
            $range = range($this->config['startFrom'], $limit);
        }

        $comments = $this->getCommentsArray($range,$theme->get('id'));

        if (!$comments[0] && $count > 0) {
            $this->modx->sendRedirect($link);
        }
        $this->config['commentsCount'] = $count;
        /**
         * Comment template
         */
        $tpl = $this->_getTpl($this->config['commentTpl']);
        /**
         * Deleted comment template
         */
        $deletedTpl = $this->_getTpl($this->config['deletedCommentTpl']);

        $hideAvatarEmail = '';
        $relativeTime = '';

        $guest_name = $this->modx->lexicon('modxtalks.guest');
        $quote_text = $this->modx->lexicon('modxtalks.quote');
        $del_by = $this->modx->lexicon('modxtalks.deleted_by');
        $restore = $this->modx->lexicon('modxtalks.restore');

        $isModerator = $this->modx->modxtalks->isModerator();
        if ($isModerator === true) {
            $userInfoTpl = $this->_getTpl($this->config['user_info']);
        }

        if (count($comments[0]) > 0) {
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
                    'name'  => $a['fullname'] ? $a['fullname'] : $a['username'],
                    'email' => $a['email'],
                );
            }
        }

        foreach ($comments[0] as $comment) {
            $timeMarker = '';
            $date = date($this->config['mtDateFormat'].' O',$comment['time']);
            $funny_date = $this->date_format(array('date' => $comment['time']));
            $index = date('Ym',$comment['time']);

            /**
             * If this is registered user
             */
            if ($comment['userId'] > 0) {
                $name = $users[$comment['userId']]['name'];
                $email = $users[$comment['userId']]['email'];
            }
            /**
             * If this is guest
             */
            else {
                $name = $comment['username'] ? $comment['username'] : $guest_name;
                $email = $comment['useremail'] ? $comment['useremail'] : 'anonym@anonym.com';
            }

            /**
             * If the post before this one has a different relative
             * time string to this one, output a 'time marker'.
             */
            $relativeTimeComment = $this->relativeTime($comment['time']);
            if ($relativeTime != $relativeTimeComment) {
                $timeMarker = '<div class="timeMarker" data-now="1">'.$relativeTimeComment.'</div>';
                $relativeTime = $relativeTimeComment;
            }
            /**
             * Timeago date format
             */
            $timeago = date('c',$comment['time']);
            /**
             * Prepare data for deleted comment
             */
            if ($comment['deleteTime'] > 0 && $comment['deleteUserId'] > 0) {
                $tmp = array(
                    'deleteUser' => $users[$comment['deleteUserId']]['name'],
                    'delete_date' => date($this->config['mtDateFormat'].' O',$comment['deleteTime']),
                    'funny_delete_date' => $this->date_format(array('date' => $comment['deleteTime'])),
                    'name' => $name,
                    'index' => $index,
                    'date' => $date,
                    'funny_date' => $funny_date,
                    'id' => $comment['id'],
                    'idx' => $comment['idx'],
                    'link_restore' => $this->getLink('restore-'.$comment['idx']),
                    'timeMarker' => $timeMarker,
                    'timeago' => $timeago,
                    'deleted_by' => $del_by,
                    'restore' => $restore,
                );
            }
            /**
             * Prepare data for published comment
             */
            else {
                $tmp = array(
                    'avatar'     => $this->getAvatar($email),
                    'hideAvatar' => 'style="display: none;"',
                    'name'       => $name,
                    'content'    => $comment['content'],
                    'index'      => $index,
                    'date'       => $date,
                    'funny_date' => $funny_date,
                    'link_reply' => $this->getLink('reply-'.$comment['idx']),
                    'id'         => $comment['id'],
                    'idx'        => $comment['idx'],
                    'quote'      => $quote_text,
                    'user'       => $this->userButtons($comment['userId'],$comment['time']),
                    'userId'     => md5($comment['userId'].$email),
                    'timeMarker' => $timeMarker,
                    'link'       => $this->getLink($comment['idx']),
                    'timeago'    => $timeago,
                    'user_info'  => '',
                );
                if ($isModerator === true) {
                    $tmp['user_info'] = $this->_parseTpl($userInfoTpl, array(
                        'email' => $email,
                        'ip' => $comment['ip']
                    ));
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
                    $tmp['funny_edit_date'] = $this->date_format(array('date' => $comment['editTime']));
                    $tmp['edit_name'] = $this->modx->lexicon('modxtalks.edited_by',array('name' => $users[$comment['editUserId']]['name']));
                    ;
                }
            }

            if ($comment['deleteTime'] > 0 && $comment['deleteUserId'] > 0) {
                $output .= $this->_parseTpl($deletedTpl,$tmp);
            }
            else {
                $output .= $this->_parseTpl($tpl,$tmp);
            }
        }
        unset($email,$name,$tmp,$tpl,$deletedTpl,$comments);

        return $output;
    }

    /**
     * Generate comment link
     *
     * @access public
     * @param string $link
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
     * @return string Resource alias
     */
    public function aliasPath($link = '', $search = '$$$') {
        if ($this->friendly_urls === true) {
            if (!isset($this->resource_alias_path) && $this->config['slug'] !== '') {
                $this->resource_alias_path = $this->config['slug'];
            }
            if (!isset($this->resource_alias_path)) {
                if (!isset($this->resource_alias)) {
                    $this->resource_alias = $this->modx->resource->get('alias');
                }
                $this->resource_alias_path = $this->modx->resource->getAliasPath($this->resource_alias.'/comment-'.$search.'-mt');
            }
            $path = str_replace($search, $link, $this->resource_alias_path);
            return $path;
        }

        return $_REQUEST[$this->modx->getOption('request_param_alias')].'&comment='.$link;
    }

    /**
     * Get scrubber
     *
     * @access public
     * @param integer $conversationId Conversation Id
     * @return string Full rendered scrubber
     */
    public function getScrubber($conversationId = 0) {
        if ($conversationId == 0 && $this->config['conversationId'] == 0) {
            return array();
        }
        elseif ($conversationId == 0 && $this->config['conversationId'] != 0) {
            $conversationId = $this->config['conversationId'];
        }

        $scrubber = array(
            'key' => 0,
            'start' => $this->modx->lexicon('modxtalks.start'),
            'start_link' => $this->aliasPath('1'),
            'now' => $this->modx->lexicon('modxtalks.now'),
            'now_link' => $this->aliasPath('last'),
            'reply' => $this->modx->lexicon('modxtalks.reply'),
            'count_talks' => $this->config['commentsCount'],
            'months' => '',
            'conversation' => $this->modx->resource->id,
            'modxtalks_total' => $this->modx->lexicon('modxtalks.total'),
        );
        /**
         * Choose the topics of the month and if necessary, the topics
         */
        $dateScrubber = '';
        $ds = $this->modx->newQuery('modxTalksPost');
        $ds->where(array('conversationId' => $conversationId));
        $ds->select(array('modxTalksPost.date','modxTalksPost.time'));
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
                $dLink = $this->getLink((strftime('%Y', $dsp['time']).'-'.$dateScrubberm));
                $dTitle = mb_convert_case($dsmi[0], MB_CASE_TITLE, "UTF-8");
                $dsYear[$scrYear] = (isset($dsYear[$scrYear]) ? $dsYear[$scrYear] : '').'<li class="scrubber-'.$dsp['date'].'" data-index="'.$dsp['date'].'"><a href="'.$dLink.'">'.$dTitle.'</a></li>';
            }
        }
        foreach ($dsYear as $key => $value) {
            if ($key != strftime('%Y', time())) {
                $dateScrubber .= '<li class="scrubber-'.$key.'01 selected" data-index="'.$key.'01"><a href="'.$this->getLink($key.'-01').'">'.$key.'</a><ul>'.$value.'</ul></li>';
            }
            else {
                $dateScrubber .= $value;
            }
        }
        $scrubber['months'] = $dateScrubber;

        return $scrubber;
    }

    /**
     * BBCode parser
     *
     * @access public
     * @param string $content
     * @return string Parsed content
     */
    public function bbcode($content) {
        $tags = array(
            'd_1' => array('[_[',']_]'),
            'd_2' => array('&#091;&#091;','&#093;&#093;'),
            's_1' => array('[',']'),
            's_2' => array('&#091;','&#093;')
        );
        if (!$this->config['bbcode']) {
            // $content = $this->modx->stripTags($content);
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
            $this->modx->bbcode->SetVideoSize($this->config['videoSize'][0],$this->config['videoSize'][1]);
            // $this->modx->bbcode->SetSlugReplace($this->config['slugReplace']);
            $this->modx->bbcode->SetSlug($this->config['slug']);
            /**
             * Enable Smileys
             */
            if ($this->config['smileys']) {
                $this->modx->bbcode->SetEnableSmileys();
                $this->modx->bbcode->SetSmileyURL($this->config['imgUrl'].'smileys');
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
        $content = str_replace($tags['s_1'],$tags['s_2'],$content);
        return $content;
    }

    /**
     * Get User Avatar
     *
     * @access public
     * @param string $email Email address
     * @return string Gravatar image link
     */
    public function getAvatar($email = '', $size = 0){
        $gravatarUrl = $this->config['imgUrl'].'avatar.png';
        if ($this->config['mtGravator'] && !empty($email)) {
            $size = (int) $size;
            $md5email = md5($email);
            $gravatarSize = !empty($size) ? $size : $this->config['mtgravatarSize'];
            $urlsep = $this->modx->context->getOption('xhtml_urls',true) ? '&amp;' : '&';
            $gravatarUrl = $this->config['mtgravatarUrl'].$md5email.'?s='.$gravatarSize.$urlsep.'d='.urlencode($this->config['defaultAvatar']);
        }
        return $gravatarUrl;
    }

    /**
     * Determines if this user is moderator in specified groups
     *
     * @access public
     * @return boolean True if user is moderator of any groups
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
     * @access private
     * @return string $form Full rendered add comment form
     */
    private function _getForm() {
        if ($this->config['onlyAuthUsers'] && !$this->modx->user->isAuthenticated($this->context) && !$this->isModerator()) {
            return $this->getChunk($this->config['commentAuthTpl'],array('avatar' => $this->config['defaultAvatar'],'noLogin' => $this->modx->lexicon('modxtalks.no_login')));
        }
        if ($this->config['commentsClosed'] && !$this->isModerator()) {
            return $this->_parseTpl($this->config['commentAuthTpl'], null, true);;
        }
        if ($this->modx->user->isAuthenticated($this->context) || $this->isModerator()) {
            $user = $this->modx->user->getOne('Profile');
            $email = $user->get('email');
            $name = $user->get('fullname');
            $tmp = array(
                'user'   => !$name ? $this->modx->user->get('username') : $name,
                'avatar' => $this->getAvatar($email),
                'hidden' => ' hidden',
            );
        } else {
            $tmp = array(
                'user'   => $this->modx->lexicon('modxtalks.guest'),
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

        $tpl = $this->_getTpl($this->config['commentAddFormTpl']);
        $form = $this->_parseTpl($tpl, $tmp);
        return $form;
    }

    /**
     * Get Edit Comment Form
     *
     * @access private
     * @return string $form Full rendered add comment form
     */
    public function _getEditForm($id = 0, $content = '', $ctx = 'web') {
        $user = $this->modx->user;
        $name = $user->get('username');
        $email = $user->Profile->get('email');
        $fullname = $user->Profile->get('fullname');
        $tmp = array(
            'user'            => !empty($fullname) ? $fullname : $name,
            'avator'          => $this->getAvatar($email),
            'controlsbb'      => $this->getEditControls('comment-'.$id),
            'previewCheckbox' => $this->modx->lexicon('modxtalks.preview_checkbox'),
            'content'         => $content,
            'id'              => $id,
            'write_comment'   => $this->modx->lexicon('modxtalks.write_comment'),
            'save_changes'    => $this->modx->lexicon('modxtalks.save_changes'),
            'cancel'          => $this->modx->lexicon('modxtalks.cancel'),
        );
        $tpl = $this->_getTpl($this->config['commentEditFormTpl']);
        $form = $this->_parseTpl($tpl, $tmp);
        return $form;
    }

    /**
     * Gets a Chunk and caches it; also falls back to file-based templates
     * for easier debugging.
     *
     * @access public
     * @param string $name The name of the Chunk
     * @param array $properties The properties for the Chunk
     * @return string The processed content of the Chunk
     */
    public function getChunk($name,$properties = array()) {
        $chunk = null;
        if (!isset($this->chunks[$name])) {
            $chunk = $this->_getTplChunk($name);
            if (empty($chunk)) {
                $chunk = $this->modx->getObject('modChunk',array('name' => $name));
                if ($chunk == false) return false;
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
     * @access private
     * @param string $name The name of the Chunk. Will parse to name.$postfix
     * @param string $postfix The default postfix to search for chunks at.
     * @return modChunk/boolean Returns the modChunk object if found, otherwise
     * false.
     */
    private function _getTplChunk($name,$postfix = '.chunk.tpl') {
        $chunk = false;
        $f = $this->config['chunksPath'].strtolower($name).$postfix;
        if (file_exists($f)) {
            $o = file_get_contents($f);
            $chunk = $this->modx->newObject('modChunk');
            $chunk->set('name',$name);
            $chunk->setContent($o);
        }
        return $chunk;
    }

    /**
     * Debug function for printing vars, arrays or objects
     *
     * @param mixed $var
     * @return void
     */
    private function pr($var) {
        if (is_object($var)) {
            if (!method_exists($var, 'toArray')) return;
            $var = $var->toArray();
        }
        echo '<pre style="font:15px Consolas;padding:10px;border:1px solid #c2c2c2;border-radius:10px;background-color:f7f7f7;box-shadow:0 1px 2px #ccc;">';
        print_r($var);
        echo '</pre>';
    }


    /**
     * Parse template chunk
     *
     * @param string $tpl Template file
     * @param array $arr Array of placeholders
     * @param boolean $chunk If True get chunk, else use template from string
     * @param string $postfix Chunk postfix if use file-based chunks
     */
    public function _parseTpl($tpl = '', $arr = array(), $chunk = false, $postfix = '.chunk.tpl') {
        if (empty($tpl) && $chunk === false) return '';
        elseif (!empty($tpl) && $chunk === true) $tpl = $this->_getTpl($tpl, $postfix);

        if (count($arr)) {
            $tmp = array();
            foreach ($arr as $k => $v) {
                $tmp['pl'][$k] = '[[+'.$k.']]';
                $tmp['vl'][$k] = $v;
            }
            $tpl = str_replace($tmp['pl'],$tmp['vl'],$tpl);
        }
        $tpl = preg_replace('@\[\[(.*?)\]\]@', '', $tpl);
        return $tpl;
    }

    /**
     * Get template chunk
     *
     * @access private
     * @param string $tpl Template file
     * @param string $postfix Chunk postfix if use file-based chunks
     */
    private function _getTpl($tpl = '', $postfix = '.chunk.tpl') {
        if (!$tpl) return '';
        if (isset($this->chunks[$tpl])) {
            return $this->chunks[$tpl];
        }
        // If useChunk setting set to True, use the modx standard chunk
        if ($this->config['useChunks'] === true) {
            if ($chunk = $this->modx->getObject('modChunk',array('name' => $tpl))) {
                $this->chunks[$tpl] = $chunk->get('content');
                return $this->chunks[$tpl];
            }
        }
        // If chunk not found or useChunk set to False, use file-based chunk
        $f = $this->config['chunksPath'].strtolower($tpl).$postfix;
        if (file_exists($f)) {
            $this->chunks[$tpl] = file_get_contents($f);
            return $this->chunks[$tpl];
        }
        return '';
    }

    /**
     * Funny date
     *
     * @param array $p['date'] - (int) UNIX timestamp
     * @return string
     */
    public function date_format($p) {
        $seconds = abs(time() - $p['date']);
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $days = floor($hours / 24);
        $months = floor($days / 30);
        $years = floor($days / 365);
        $seconds = floor($seconds);

        if ($seconds < 60) {
            return $this->decliner($seconds,$this->modx->lexicon('modxtalks.date_seconds_back', array('seconds' => $seconds)));
        }
        if ($minutes < 45) {
            return $this->decliner($minutes,$this->modx->lexicon('modxtalks.date_minutes_back', array('minutes' => $minutes)));
        }
        if ($minutes < 60) {
            return $this->modx->lexicon('modxtalks.date_hours_back_less');
        }
        if ($hours < 24) {
            return $this->decliner($hours,$this->modx->lexicon('modxtalks.date_hours_back', array('hours' => $hours)));
        }
        if ($days < 30) {
            return $this->decliner($days,$this->modx->lexicon('modxtalks.date_days_back', array('days' => $days)));
        }
        if ($days < 365) {
            return $this->decliner($months,$this->modx->lexicon('modxtalks.date_month_back', array('months' => $months)));
        }
        if ($days > 365) {
            return $this->decliner($years,$this->modx->lexicon('modxtalks.date_years_back', array('years' => $years)));
        }
        return date($this->config['mtDateFormat'],$p['date']);
    }

    /**
     * Declension of word
     *
     * @param int $count
     * @param string|array $forms
     * @return string
     */
    public function decliner($count, $forms) {
        if (!is_array($forms)) {
            $forms = explode(';',$forms);
        }
        $count = abs($count);
        if ($this->lang === 'ru') {
            $mod100 = $count % 100;
            switch ($count % 10) {
                case 1:
                    if ($mod100 == 11) return $forms[2];
                    else return $forms[0];
                case 2:
                case 3:
                case 4:
                    if (($mod100 > 10) && ($mod100 < 20)) return $forms[2];
                    else return $forms[1];
                case 5:
                case 6:
                case 7:
                case 8:
                case 9:
                case 0: return $forms[2];
            }
        }
        else {
            return ($count == 1) ? $forms[0] : $forms[1];
        }
    }

    /**
     * Get user buttons for comment
     *
     * @param int $userId User Id
     * @param int $time UNIX timestamp
     * @return string HTML buttons
     */
    public function userButtons($userId = 0, $time = 0) {
        /**
         * If a registered user is a member of moderators, then give moderate comments.
         */
        $buttons = '<a href="#" title="Изменить" class="control-edit">Изменить</a>';
        $buttons .= '<a href="#" title="Удалить" class="control-delete">Удалить</a>';

        if ($this->isModerator()) {
            return $buttons;
        }
        elseif ($userId != 0 && $this->modx->user->id == $userId && ($time + $this->config['edit_time']) > time()) {
            return $buttons;
        }
        if ($this->config['onlyAuthUsers'] && !$this->modx->user->isAuthenticated($this->context)) {
            return '';
        }
        return '';
    }

    /**
     * Get a human-friendly string (eg. 1 hour ago) for
     * how much time has passed since a given time.
     *
     * @param int $then UNIX timestamp of the time to work out how much time has passed since.
     * @param bool $precise Whether or not to return "x minutes/seconds", or just "a few minutes".
     * @return string A human-friendly time string.
     */
    public function relativeTime($then, $precise = false) {
        // If there is no $then, we can only assume that whatever it is never happened...
        if (!$then) return $this->modx->lexicon('modxtalks.never');

        // Work out how many seconds it has been since $then.
        $ago = time() - $then;

        // If $then happened less than 10 minutes ago (or is yet to happen,) say "date now".
        if ($ago < 600) return $this->modx->lexicon('modxtalks.date_now');

        // If this happened over a year ago, return "x years ago".
        if ($ago >= ($period = 60 * 60 * 24 * 365.25)) {
            $years = floor($ago / $period);
            if($years == 1) return $this->modx->lexicon('modxtalks.d_year_ago');
            if($years < 5) return $this->modx->lexicon('modxtalks.d_yearago',array('d' => $years));
            return $this->modx->lexicon('modxtalks.d_year_ago',array('d' => $years)); //("%d year ago", "%d years ago", $years);
        }

        // If this happened over two months ago, return "x months ago".
        elseif ($ago >= ($period = 60 * 60 * 24 * (365.25 / 12)) * 2) {
            $months = floor($ago / $period);
            if($months == 1) return $this->modx->lexicon('modxtalks.d_month_ago');
            if($months < 5) return $this->modx->lexicon('modxtalks.d_monthsago',array('d' => $months));
            return $this->modx->lexicon('modxtalks.d_month_ago',array('d' => $months)); //Ts("%d month ago", "%d months ago", $months);
        }

        // If this happend over a week ago, return "x weeks ago".
        elseif ($ago >= ($period = 60 * 60 * 24 * 7)) {
            $weeks = floor($ago / $period);
            if($weeks == 1) return $this->modx->lexicon('modxtalks.d_week_ago');
            if($weeks < 5) return $this->modx->lexicon('modxtalks.d_weeksago',array('d' => $weeks));
            return $this->modx->lexicon('modxtalks.d_weeks_ago',array('d' => $weeks)); //Ts("%d week ago", "%d weeks ago", $weeks);
        }

        // If this happened over a day ago, return "x days ago".
        elseif ($ago >= ($period = 60 * 60 * 24)) {
            $days = floor($ago / $period);
            if($days == 1) return $this->modx->lexicon('modxtalks.d_day_ago');
            if($days <= 4) return $this->modx->lexicon('modxtalks.d_daysago',array('d' => $days));
            return $this->modx->lexicon('modxtalks.d_days_ago',array('d' => $days)); //Ts("%d day ago", "%d days ago", $days);
        }

        // If this happened over an hour ago, return "x hours ago".
        elseif ($ago >= ($period = 60 * 60)) {
            $hours = floor($ago / $period);
            if($hours > 9) return $this->modx->lexicon('modxtalks.d_hours_today');
            if($hours == 1) return $this->modx->lexicon('modxtalks.d_hour_ago');
            if($hours < 5) return $this->modx->lexicon('modxtalks.d_hoursago',array('d' => $hours));
            return $this->modx->lexicon('modxtalks.d_hours_ago',array('d' => $hours)); //Ts("%d hour ago", "%d hours ago", $hours);
        }

        // Otherwise, just return "date now".
        return $this->modx->lexicon('modxtalks.date_now'); //"just now";
    }

    /**
     * Make Quotes for modxTalks::quotes()
     *
     * @access public
     * @param string $text
     * @param integet $postId
     * @param string $user
     * @param string $content
     * @return string Processed Content
     */
    public function makeQuote($text, $postId = '', $user = '', $content = '') {
        $content = htmlspecialchars($this->modx->stripTags($content));
        $text    = htmlspecialchars($this->modx->stripTags($text));
        $user    = htmlspecialchars($this->modx->stripTags($user));
        $postId  = preg_replace('#[^0-9]#i', '', $postId);

        $quote = $content.'<blockquote>';
        if (!empty($postId)) {
            $link = str_replace($this->config['slugReplace'], $postId, $this->config['slug']);
            $quote .= '<a href="'.$link.'" rel="comment" data-id="'.$postId.'" class="control-search postRef">'.$this->mtConfig['go_to_comment'].'</a>';
        }
        if (!empty($user)) {
            $quote .= '<cite>'.$user.'</cite>';
        }
        $quote .= $text.'</blockquote>';
        return $quote;
    }

    /**
     * Make Quotes from BBCode
     *
     * @access public
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
     * @access public
     * @return string $ip
     */
    public function get_client_ip() {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];
        elseif (isset($_SERVER['REMOTE_ADDR'])) $ip = $_SERVER['REMOTE_ADDR'];
        else $ip = '0.0.0.0';
        return $ip;
    }

    /**
     * Collects element tags in a string and injects them into an array.
     *
     * @access public
     * @param string $origContent The content to collect tags from.
     * @param array &$matches An array in which the collected tags will be
     * stored (by reference)
     * @param string $prefix The characters that define the start of a tag
     * (default= "[[").
     * @param string $suffix The characters that define the end of a tag
     * (default= "]]").
     * @return integer The number of tags collected from the content.
     */
    public function collectElementTags($origContent, array &$matches, $prefix= '[[', $suffix= ']]') {
        $matchCount= 0;
        if (!empty ($origContent) && is_string($origContent) && strpos($origContent, $prefix) !== false) {
            $openCount= 0;
            $offset= 0;
            $openPos= 0;
            $closePos= 0;
            if (($startPos= strpos($origContent, $prefix)) === false) {
                return $matchCount;
            }
            $offset= $startPos +strlen($prefix);
            if (($stopPos= strrpos($origContent, $suffix)) === false) {
                return $matchCount;
            }
            $stopPos= $stopPos + strlen($suffix);
            $length= $stopPos - $startPos;
            $content= $origContent;
            while ($length > 0) {
                $openCount= 0;
                $content= substr($content, $startPos);
                $openPos= 0;
                $offset= strlen($prefix);
                if (($closePos= strpos($content, $suffix, $offset)) === false) {
                    break;
                }
                $nextOpenPos= strpos($content, $prefix, $offset);
                while ($nextOpenPos !== false && $nextOpenPos < $closePos) {
                    $openCount++;
                    $offset= $nextOpenPos + strlen($prefix);
                    $nextOpenPos= strpos($content, $prefix, $offset);
                }
                $nextClosePos= strpos($content, $suffix, $closePos + strlen($suffix));
                while ($openCount > 0 && $nextClosePos !== false) {
                    $openCount--;
                    $closePos= $nextClosePos;
                    $nextOpenPos= strpos($content, $prefix, $offset);
                    while ($nextOpenPos !== false && $nextOpenPos < $closePos) {
                        $openCount++;
                        $offset= $nextOpenPos + strlen($prefix);
                        $nextOpenPos= strpos($content, $prefix, $offset);
                    }
                    $nextClosePos= strpos($content, $suffix, $closePos + strlen($suffix));
                }
                $closePos= $closePos +strlen($suffix);

                $outerTagLength= $closePos - $openPos;
                $innerTagLength= ($closePos -strlen($suffix)) - ($openPos +strlen($prefix));

                $matches[]= substr($content, ($openPos +strlen($prefix)), $innerTagLength);
                $matchCount++;

                if ($nextOpenPos === false) {
                    $nextOpenPos= strpos($content, $prefix, $closePos);
                }
                if ($nextOpenPos !== false) {
                    $startPos= $nextOpenPos;
                    $length= $length - $nextOpenPos;
                } else {
                    $length= 0;
                }
            }
        }
        return $matchCount;
    }

    /**
     * Sends an email for conversation
     * @param string $subject
     * @param string $body
     * @param string $to
     * @return bool
     */
    protected function sendEmail($subject, $body = '', $to, $body_text = '', $options = array()) {
        $this->modx->getService('mail', 'mail.modPHPMailer');
        if (!$this->modx->mail) return false;

        $emailFrom = $this->modx->getOption('modxtalks.emailsFrom',$this->modx->getOption('emailsender'));
        $emailReplyTo = $this->modx->getOption('modxtalks.emailsReplyTo',$this->modx->getOption('emailsender'));

        /* allow multiple to addresses */
        if (!is_array($to)) {
            $to = explode(',',$to);
        }

        $success = false;
        foreach ($to as $emailAddress) {
            if (empty($emailAddress) || strpos($emailAddress,'@') === false) continue;

            $this->modx->mail->set(modMail::MAIL_BODY,$body);
            if (!empty($body_text)) {
                $this->modx->mail->set(modMail::MAIL_BODY_TEXT,$body_text);
            }
            $this->modx->mail->set(modMail::MAIL_FROM,$emailFrom);
            $this->modx->mail->set(modMail::MAIL_FROM_NAME,$this->modx->getOption('fromName',$options,$this->modx->getOption('site_name')));
            $this->modx->mail->set(modMail::MAIL_SENDER,$emailFrom);
            $this->modx->mail->set(modMail::MAIL_SUBJECT,$subject);
            $this->modx->mail->address('to',$emailAddress);
            $this->modx->mail->address('reply-to',$emailReplyTo);
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
     * @return boolean True if successful
     */
    public function notifyModerators(&$comment) {
        if (!($comment instanceof modxTalksPost) && !($comment instanceof modxTalksTempPost)) {
            return false;
        }

        $this->modx->lexicon->load('modxtalks:emails');

        /**
         * Получаем инфрмацию о пользователе
         */
        $user = $comment->getUserData();
        $images_url = $this->modx->getOption('site_url').substr($this->config['imgUrl'],1);

        if ($comment instanceof modxTalksPost) {
            $cid = $comment->conversationId;
            $idx = $comment->idx;
            $link = $this->generateLink($cid,$idx,'full');
            $subject = $this->modx->lexicon('modxtalks.email_new_comment');
            $text = $this->modx->lexicon('modxtalks.email_added_new_comment',array(
                'link' => $link,
                'name' => $user['name'],
            ));
        }
        elseif ($comment instanceof modxTalksTempPost) {
            $subject = $this->modx->lexicon('modxtalks.email_new_premoderated_comment');
            $text = $this->modx->lexicon('modxtalks.email_user_add_premoderated_comment',array(
                'name' => $user['name'],
            ));
        }

        $params = array(
            'title'      => 'Заголовок',
            'content'    => $this->modx->stripTags($this->bbcode($comment->content)),
            'images_url' => $images_url,
            'avatar'     => $this->getAvatar($user['email'],50),
            'text'       => $text,
            'date'       => date($this->config['mtDateFormat'].' O',$comment->time),
        );

        /**
         * get email body
         */
        $body = $this->getChunk('mt_send_mail',$params);

        /**
         * send notifications
         */
        $success = false;

        $emails = $this->getUsersEmailsByGroups($this->config['moderator'],$comment);

        /**
         * send notifications to moderators
         */
        if (!empty($emails)) {
            if ($this->sendEmail($subject,$body,$emails)) $success = true;
        }

        return $success;
    }

    /**
     * Sends notification to users
     *
     * @param modxTalksPost $comment A reference to the actual comment
     * @return boolean True if successful
     */
    public function notifyUser(&$comment) {
        if (!($comment instanceof modxTalksPost)) {
            return false;
        }

        $this->modx->lexicon->load('modxtalks:emails');

        /**
         * Получаем инфрмацию о пользователе
         */
        $user = $comment->getUserData();

        $cid = $comment->conversationId;
        $idx = $comment->idx;

        $link = $this->generateLink($cid,$idx,'full');
        $images_url = $this->modx->getOption('site_url').substr($this->config['imgUrl'],1);

        $subject = $this->modx->lexicon('modxtalks.email_comment_approved');
        $text = $this->modx->lexicon('modxtalks.email_user_approve_comment',array(
            'link' => $link,
        ));

        $params = array(
            'title'      => 'Заголовок',
            'content'    => $this->modx->stripTags($this->bbcode($comment->content)),
            'images_url' => $images_url,
            'avatar'     => $this->getAvatar($user['email'],50),
            'text'       => $text,
            'date'       => date($this->config['mtDateFormat'].' O',$comment->time),
        );

        /**
         * get email body
         */
        $body = $this->getChunk('mt_send_mail',$params);

        /**
         * send notifications to user
         */
        $success = false;
        if (!empty($user['email'])) {
            if ($this->sendEmail($subject,$body,$user['email'])) $success = true;
        }

        return $success;
    }

    /**
     * Get Users data by groups
     *
     * @param string|array $groups Moderators groups
     * @param modxTalksPost $comment A reference to the actual comment
     * @return array
     */
    public function getUsersEmailsByGroups($groups, &$comment) {
        if (!($comment instanceof modxTalksPost) && !($comment instanceof modxTalksTempPost)) {
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
        $c->where(array('modUserGroup.name:IN' => $groups));
        $c->select(array('modUserGroup.id','UserGroupMembers.member'));
        $c->leftJoin('modUserGroupMember','UserGroupMembers','UserGroupMembers.user_group = modUserGroup.id');
        if ($c->prepare() && $c->stmt->execute()) {
            $result = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $uid) {
                if ($uid['member'] != $userId) {
                    $usersIds[] = $uid['member'];
                }
            }
        }

        if (count($usersIds)) {
            $c = $this->modx->newQuery('modUserProfile',array('internalKey:IN' => $usersIds));
            $c->select(array('id','email'));
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
     * @return object $conversation
     **/
    public function getConversation($name = '') {
        if (empty($name)) return false;
        /**
         * Conversation in cache TRUE or FALSE
         */
        $cCache = false;
        $cache = $this->modx->getCacheManager();
        // Create a key by conversation name
        $keyConversation = md5('modxtalks::'.$name);
        // If there is a cache, set the flag to TRUE ConversationCache, otherwise FALSE
        if ($this->mtCache && $cache) {
            if ($theme = $this->modx->cacheManager->get($keyConversation,array(
                xPDO::OPT_CACHE_KEY => 'modxtalks/conversation'))) {
                $cCache = true;
                $conversation = $this->modx->newObject('modxTalksConversation',$theme);
                $conversation->set('id',$theme['id']);
            }
            else {
                $cCache = false;
            }
        }

        // If the flag is in ConversationCache FALSE - get data from database
        if ($cCache === false) {
            // If the key is not section, create a new
            if (!$conversation = $this->modx->getObject('modxTalksConversation',array('conversation'=>$name))) {
                $conversation = $this->modx->newObject('modxTalksConversation',array('conversation'=>$name));
                $properties = array(
                    'total' => 0,
                    'deleted' => 0,
                    'unconfirmed' => 0,
                );
                $conversation->setProperties($properties,'comments',false);
                $conversation->setSingleProperty($this->modx->resource->id);
                $conversation->set('rid',$this->modx->resource->id);
                $conversation->save();
            }
            // Added to the cache
            if ($this->mtCache && $cache)
                $this->modx->cacheManager->set($keyConversation,$conversation, 0, array(
                    xPDO::OPT_CACHE_KEY => 'modxtalks/conversation'
                ));
        }
        return $conversation;
    }

    /**
     * Cache conversation
     *
     * @param object $conversation Conversation object
     * @return boolean true|false
     **/
    public function cacheConversation(modxTalksConversation & $conversation) {
        if (empty($conversation)) return false;

        $cache = $this->modx->getCacheManager();
        if ($this->mtCache && $cache) {
            $keyConversation = md5('modxtalks::'.$conversation->conversation);
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
     * @param integer $ids Comment ID or Comments IDs
     * @return array [0] - comments, [1] - users
     **/
    public function getCommentsArray($ids,$conversationId) {
        if (empty($ids) || empty($conversationId)) return false;

        /**
         * Result Comments array
         */
        $comments = array();
        /**
         * Non cached comments
         */
        $nonCached = array();
        $cache = $this->modx->getCacheManager();
        $cCache = false;
        /**
         * Retrieve comments from cache
         * те которых нет пишем в массив $nonCached для дальнейшего получения из базы
         */
        if ($this->mtCache && $cache) {
            foreach ($ids as $id) {
                if ($comment = $this->modx->cacheManager->get($id, array(xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/'.$conversationId))) {
                    $comments[$id] = $comment;
                    $cCache = true;
                }
                else {
                    $nonCached[] = $id;
                    $cCache = false;
                }
            }
        }

        /**
         * Get comments by idx and conversation Id
         */
        if ($cCache === false) {
            $c = $this->modx->newQuery('modxTalksPost', array('conversationId' => $conversationId));
            $c->select(array('id','idx','content','userId','time','deleteTime','deleteUserId','editTime','editUserId','username','useremail','ip'));
            if (count($nonCached) && $this->mtCache && $cache) {
                $c->andCondition(array('idx:IN' => $nonCached));
            }
            else {
                $c->andCondition(array('idx:IN' => $ids));
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
                     * Cache comment
                     */
                    if ($this->mtCache && $cache) {
                        $this->modx->cacheManager->set($result['idx'], $result, 0, array(
                            xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/'.$conversationId
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
            if ($c['userId']) $users[] = $c['userId'];
            if ($c['deleteUserId']) $users[] = $c['deleteUserId'];
            if ($c['editUserId']) $users[] = $c['editUserId'];
        }
        $users = array_unique($users);

        /**
         * Sort array ascending by idx
         */
        ksort($comments);

        return array($comments,$users);
    }

    /**
     * Cache comment
     *
     * @param object $comment Comment object
     * @return true
     */
    public function cacheComment(modxTalksPost & $comment) {
        $cache = $this->modx->getCacheManager();
        if ($this->mtCache && $cache) {
            $tmp = $comment->toArray();
            $tmp['raw_content'] = $comment->content;
            $tmp['content'] = $this->bbcode($comment->content);
            if (!$this->modx->cacheManager->set($comment->idx, $tmp, 0, array(xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/'.$comment->conversationId))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get user or users
     *
     * @param string|array $ids User id or Users id's
     * @param integer $ids Comment ID or Comments IDs
     * @return array $comments
     **/
    public function getUsers($ids) {
        if (empty($ids)) return false;

        /**
         * Result Users array
         */
        $users = array();
        /**
         * Non cached comments
         */
        $nonCached = array();
        /**
         * Получаем пользователей из кэша
         * те которых нет в кэше, пишем в массив $nonCached для дальнейшего получения из базы
         */
        foreach ($ids as $id) {
            if ($user = $this->modx->cacheManager->get($id, array(xPDO::OPT_CACHE_KEY => 'modxtalks/users/'))) {
                $users[$id] = $user;
            }
            else {
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
        $c->select(array('modUser.id','modUser.username','p.email','p.fullname'));
        $c->leftjoin('modUserProfile','p','modUser.id = p.internalKey');
        if (count($nonCached) > 1) {
            $c->andCondition(array('modUser.id:IN' => $nonCached));
        }
        else {
            $c->andCondition(array('modUser.id' => array_shift($nonCached)));
        }

        if ($c->prepare() && $c->stmt->execute()) {
            $cache = $this->modx->getCacheManager();
            $results = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as $result) {
                $users[$result['id']] = $result;
                /**
                 * Пишем информацию пользователя в кэш
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
     * @param object $user User object
     * @return true
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
            if (!$this->modx->cacheManager->set($user->id, $tmp, 0, array(xPDO::OPT_CACHE_KEY => 'modxtalks/users'))) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get index of comment by date
     *
     * @param object $user User object
     * @return true
     */
    public function getDateIndex($conversationId,$date) {
        if (empty($conversationId) || empty($date) || $date !== date('Y-m',strtotime($date))) {
            return false;
        }
        $date = str_replace('-', '', $date);

        $cCache = false;
        $cache = $this->modx->getCacheManager();
        if ($this->mtCache && $cache) {
            if ($index = $this->modx->cacheManager->get($date, array(xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/'.$conversationId.'/dates'))) {
                $cCache = true;
            }
        }

        if ($cCache == false) {
            $c = $this->modx->newQuery('modxTalksPost',array('conversationId' => $conversationId, 'date' => $date));
            $c->sortby('idx','ASC');
            if (!$index = $this->modx->getObject('modxTalksPost',$c)) {
                return false;
            }
            $index = $index->get('idx');
            if ($this->mtCache && $cache) {
                $this->modx->cacheManager->set($date, $index, 0, array(xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/'.$conversationId.'/dates'));
            }
        }
        return $index;
    }

    /**
     * Create RSS Feed of latest comments
     *
     * @return sring
     **/
    public function createRssFeed($conversation,$limit) {
        /**
         * Check for conversation present
         */
        if (!$theme = $this->getConversation($conversation)) {
            return '';
        }

        $count = $theme->getProperty('total','comments');
        if ($count < 1) return '';

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
        $comments = $this->getCommentsArray($range,$theme->get('id'));
        /**
         * Get resource URL
         */
        $link = $this->modx->getOption('site_url').$this->modx->resource->uri;
        if (!$comments[0] && $count > 0) {
            $this->modx->sendRedirect($link);
        }

        $guest_name = $this->modx->lexicon('modxtalks.guest');

        $usersIds = & $comments[1];
        $users = array();

        /**
         * Get registered Users
         */
        if (count($usersIds)) {
            $authUsers = $this->getUsers($usersIds);
            foreach ($authUsers as $a) {
                $users[$a['id']] = array(
                    'name'  => $a['fullname'] ? $a['fullname'] : $a['username'],
                    'email' => $a['email'],
                );
            }
        }

        $output = '';
        $comments[0] = array_reverse($comments[0]);
        foreach ($comments[0] as $comment) {
            $date = date($this->config['mtDateFormat'].' O',$comment['time']);

            // If this is registered user
            if ($comment['userId'] > 0) {
                $name = $users[$comment['userId']]['name'];
                $email = $users[$comment['userId']]['email'];
            }
            // If this is guest
            else {
                $name = $comment['username'] ? $comment['username'] : $guest_name;
                $email = $comment['useremail'] ? $comment['useremail'] : 'anonym@anonym.com';
            }

            $tmp = array(
                'name'       => $name,
                'content'    => $this->modx->stripTags($comment['content']),
                'date'       => $date,
                'link'       => $this->getLink($comment['idx']),
            );

            $output .= $this->getChunk($item_tpl,$tmp);

        }
        $output = $this->getChunk($feed_tpl,array('content' => $output));

        return $output;
    }

    /**
     * Create conversations map
     *
     * @return array|false False if cache is off
     */
    public function conversationsMap() {
        /**
         * Если выключено кэширование возвращаем False
         */
        if (!$this->mtCache) return false;
        /**
         * Если карта присутсвует в кэше, то просто возвразщаем её
         */
        if ($map = $this->modx->cacheManager->get('conversations_map', array(
            xPDO::OPT_CACHE_KEY => 'modxtalks'))) {
            return $map;
        }
        /**
         * Если карта отсутсвует в кэше, получаем все темы из базы и строим карту
         */
        $map = array();
        $c = $this->modx->newQuery('modxTalksConversation');
        $c->select(array('id','rid','conversation'));
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
     * @param integer $id Resource ID
     * @return boolean True if resource have a conversation
     */
    public function hasConversations($id) {
        if (!intval($id)) return false;
        /**
         * Проверяем через карту тем в кэше
         */
        if ($map = $this->conversationsMap()) {
            /**
             * Если ресурс содержит хотя бы одну тему возвращаем true
             */
            if (array_key_exists($id, $map)) return true;
            return false;
        }
        /**
         * Если функция conversationsMap() вернула false (при выключенном кэше), проверяем по базе
         */
        elseif ($this->modx->getCount('modxTalksConversation',array('rid' => $id))) {
            return true;
        }

        return false;
    }

    /**
     * Refresh comment and conversation cache
     *
     * @param modxTalksPost $comment
     * @param modxTalksConversation $conversation
     */
    public function refreshCommentCache(modxTalksPost & $comment, modxTalksConversation & $conversation) {
        if ($this->mtCache === true) {
            if (!$this->cacheComment($comment)) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR,'[modxTalks web/comment/create] Error cache the comment with ID '.$comment->id);
            }
            if (!$this->cacheConversation($conversation)) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR,'[modxTalks web/comment/create] Error cache the conversation with ID '.$conversation->id);
            }
        }
        return true;
    }

    /**
     * Generate comment URL
     *
     * @param integer $cid Conversation Id
     * @param integer $idx Comment Idx
     * @param string $scheme URL scheme - full or abs
     * @return string Comment URL
     */
    public function generateLink($cid = 0,$idx = 0,$scheme = 'full') {
        $conversation = $this->modx->getObject('modxTalksConversation',$cid);
        $rid = $conversation->rid;
        if ($scheme !== 'full') $scheme = 'abs';
        $url = $this->modx->makeUrl($rid,$this->context,'',$scheme);
        if ($this->config['debug']) {
            $this->modx->log(xPDO::LOG_LEVEL_ERROR,'Link to Resource: '.$url);
        }
        $slug = 'comment-'.$this->config['slugReplace'].'-mt';
        $link = '';
        $slarray = explode('/',$url);
        if (count($slarray) > 1) {
           $doturi = end($slarray);
           $dotarray = explode('.',$doturi);
           if (count($dotarray) > 1) {
                $uriend = end($dotarray);
                $link = str_replace(('.'.$uriend),'/'.$slug,$url).'.'.$uriend;
           }
           else {
                $sleh = substr($url, -1) == '/' ? true : false;
                $link = $url.($sleh ? '' : '/').$slug.($sleh ? '/' : '');
           }
        }
        else {
            $link = $url.'/'.$slug;
        }

        if (intval($idx) > 0) $link = $this->getLink($idx);

        return $link;
    }

}