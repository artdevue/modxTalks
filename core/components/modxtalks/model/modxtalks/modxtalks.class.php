<?php

/**
 * This file is part of MODXTalks, a simple commenting component for MODx Revolution.
 *
 * @copyright Copyright (C) 2013, Artdevue Ltd, <info@artdevue.com>
 * @author Valentin Rasulov <info@artdevue.com> && Ivan Brezhnev <brezhnev.ivan@yahoo.com>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @package modxtalks
 *
 */
class modxTalks
{
	/**
	 * @var modX A reference to the modX object.
	 */
	public $modx = null;
	/**
	 * @var array An array of configuration options
	 */
	public $config = [];
	/**
	 * @var array An array of snippet config
	 */
	public $scriptProperties = [];
	/**
	 * @var array An array of chunks
	 */
	public $chunks = [];
	/**
	 * @var string Context key
	 */
	protected $context;
	/**
	 * @var bool Enable or Disable modxTalks Comments cache
	 */
	public $mtCache = true;
	/**
	 * @var array An array of other configuration options
	 */
	protected $mtConfig = [];
	/**
	 * @var bool True if FURLs Enabled
	 */
	protected $friendly_urls;
	/**
	 * @var string Conversation Id
	 */
	protected $conversationId;
	/**
	 * @var string Culture key
	 */
	protected $lang;
	/**
	 * @var bool Revers comments, newest on the top
	 */
	protected $revers;

	/**
	 * Constructs the modxTalks object
	 *
	 * @param modX &$modx A reference to the modX object
	 * @param array $config An array of configuration options
	 */
	function __construct(modX & $modx, array $config = [])
	{
		$this->modx =& $modx;

		$basePath = $this->modx->getOption('modxtalks.core_path', $config, $this->modx->getOption('core_path') . 'components/modxtalks/');
		$assetsUrl = $this->modx->getOption('modxtalks.assets_url', $config, $this->modx->getOption('assets_url') . 'components/modxtalks/');
		$siteUrl = $this->modx->getOption('server_protocol') . '://' . $this->modx->getOption('http_host');

		$this->config = [
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
			'videoSize' => [500, 350],
			'commentLength' => (int) $this->modx->getOption('modxtalks.commentLength', null, 2000),

			'slug' => '',
			'slugReplace' => '$$$',

			// Scrubber position
			'scrubberTop' => (bool) $this->modx->getOption('modxtalks.scrubberTop', null, false),
			'scrubberOffsetTop' => (int) $this->modx->getOption('modxtalks.scrubberOffsetTop', null, 0),

			'fullDeleteComment' => (bool) $this->modx->getOption('modxtalks.fullDeleteComment', null, false),

			'timeMarkers' => (bool) $this->modx->getOption('modxtalks.timeMarkers', null, false),
		];

		$this->scriptProperties =& $config;
		$this->config = array_merge($this->config, $config);

		// if videoSize not array
		if ( ! is_array($this->config['videoSize']) && strpos($this->config['videoSize'], ','))
		{
			$this->config['videoSize'] = explode(',', $this->config['videoSize']);
		}

		if (isset($config['commentsPerPage']) && intval($config['commentsPerPage']))
		{
			$config['commentsPerPage'] = (int) $config['commentsPerPage'];
		}

		$this->friendly_urls = (bool) $this->modx->getOption('friendly_urls');

		$this->revers = (bool) $this->modx->getOption('modxtalks.revers', null, true);

		$this->context = ! isset($this->config['context']) ? $this->context = $this->modx->context->key : $this->config['context'];

		$this->modx->addPackage('modxtalks', $this->config['modelPath']);
		$this->lang = $this->modx->getOption('cultureKey');
		$this->modx->lexicon->load('modxtalks:default');
	}

	/**
	 * Initializes modxTalks based on a specific context
	 *
	 * @access public
	 *
	 * @param string $ctx The context to initialize in
	 *
	 * @return bool
	 */
	public function initialize($ctx = 'web')
	{
		switch ($ctx)
		{
			case 'mgr':
				if ( ! $this->modx->loadClass('modxtalksControllerRequest', $this->config['modelPath'] . 'modxtalks/request/', true, true))
				{
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
	 * @access public
	 *
	 * @param integer $limit Commets per request
	 * @param string $id Optional
	 *
	 * @return string HTML
	 */
	public function init($limit = '', $id = '')
	{
		if (empty($this->config['conversation']))
		{
			$this->config['conversation'] = $this->modx->resource->class_key . '-' . $this->modx->resource->id;
		}

		$conversation = $this->validateConversation($this->config['conversation']);
		if ($conversation !== true)
		{
			return $conversation;
		}

		if (isset($_REQUEST['comment']))
		{
			$id = (string) $_REQUEST['comment'];
		}

		$limit = (int) $limit;
		if ( ! $limit)
		{
			$limit = $this->config['commentsPerPage'];
		}

		$comments = $this->getComments($this->config['conversation'], $limit, $id);

		$this->_regStyles();

		/**
		 * If Ajax enabled
		 */
		if ($this->modx->getOption('modxtalks.ajax', null, true))
		{
			$this->_regScripts();
			$this->_getScriptHead();
			$this->_ejsTemplates();
		}

		$output = '<div id="mt_conversationBody" class="mt_hasScrubber' . ($this->config['scrubberTop'] || $this->isRevers() ? ' mt_scrubber-top' : '') . '"><div class="mt_mthead"></div><div>';

		if ($this->isRevers())
		{
			$output .= $this->_parseTpl('scrubber_rev', $this->getScrubber(), true);
			$output .= $this->_getForm();
		}
		else
		{
			$output .= $this->_parseTpl('scrubber', $this->getScrubber(), true);
		}
		$output .= '<div id="mt_conversationPosts" class="mt_postList" start="' . $this->config['startFrom'] . '">';
		$output .= $comments;
		$output .= '</div>';
		if ( ! $this->isRevers())
		{
			$output .= $this->_getForm();
		}
		$output .= '</div></div>';

		$this->cacheProperties($this->config['conversation'], $this->scriptProperties);

		return $output;
	}

	public function generateButtons($buttnons = [], $id = 'mt_replay')
	{
		if ( ! is_array($buttnons))
		{
			$buttnons = explode(',', $buttnons);
		}

		$result = '';
		foreach ($buttnons as $btn)
		{
			$result .= '<a href="javascript:BBCode.' . $btn . '(' . $id . ');void(0)" title="' . $this->lang($btn) . '" class="bbcode-' . $btn . '"><span>' . $this->lang($btn) . '</span></a>' . "\n";
		}

		return $result;
	}

	/**
	 * Add an event handler to the "getEditControls" method of
	 * the conversation controller to add BBCode formatting buttons
	 * to the edit controls.
	 *
	 * @access protected
	 *
	 * @param string $id
	 *
	 * @return string $buttons
	 */
	protected function getEditControls($id = 'mt_reply')
	{
		$editControls = [
			"fixed" => "<a href='javascript:BBCode.fixed(\"$id\");void(0)' title='" . $this->lang('fixed') . "' class='bbcode-fixed'><span>" . $this->lang('fixed') . "</span></a>",
			"image" => "<a href='javascript:BBCode.image(\"$id\");void(0)' title='" . $this->lang('image') . "' class='bbcode-img'><span>" . $this->lang('image') . "</span></a>",
			"link" => "<a href='javascript:BBCode.link(\"$id\");void(0)' title='" . $this->lang('link') . "' class='bbcode-link'><span>" . $this->lang('link') . "</span></a>",
			"strike" => "<a href='javascript:BBCode.strikethrough(\"$id\");void(0)' title='" . $this->lang('strike') . "' class='bbcode-s'><span>" . $this->lang('strike') . "</span></a>",
			"header" => "<a href='javascript:BBCode.header(\"$id\");void(0)' title='" . $this->lang('header') . "' class='bbcode-h'><span>" . $this->lang('header') . "</span></a>",
			"italic" => "<a href='javascript:BBCode.italic(\"$id\");void(0)' title='" . $this->lang('italic') . "' class='bbcode-i'><span>" . $this->lang('italic') . "</span></a>",
			"bold" => "<a href='javascript:BBCode.bold(\"$id\");void(0)' title='" . $this->lang('bold') . "' class='bbcode-b'><span>" . $this->lang('bold') . "</span></a>",
			"video" => "<a href='javascript:BBCode.video(\"$id\");void(0)' title='" . $this->lang('video') . "' class='bbcode-v'><span>" . $this->lang('video') . "</span></a>",
			"quote" => "<a href='javascript:BBCode.quote(\"$id\");void(0)' title='" . $this->lang('quote') . "' class='bbcode-q'><span>" . $this->lang('quote') . "</span></a>",
		];

		if ( ! $this->config['bbcode'])
		{
			return $editControls['quote'];
		}

		$editOptionsControls = $this->config['editOptionsControls'];
		$editOptionsControlsArray = explode(',', $editOptionsControls);

		$buttons = '';
		foreach ($editOptionsControlsArray as $b)
		{
			$b = trim($b);
			if (array_key_exists($b, $editControls))
			{
				$buttons .= $editControls[$b];
			}
		}

		return $buttons;
	}

	/**
	 * Add JavaScript language definitions and variables
	 *
	 * @access protected
	 * @return string true
	 */
	protected function _regScripts()
	{
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
		if ( ! $this->isRevers())
		{
			$this->modx->regClientScript($this->config['jsUrl'] . 'web/modxtalks.js');
			$this->modx->regClientScript($this->config['jsUrl'] . 'web/scrubber.js');
		}
		else
		{
			$this->modx->regClientScript($this->config['jsUrl'] . 'web/modxtalks_rev.js');
		}

		$this->modx->regClientScript($this->config['jsUrl'] . 'web/lib/timeago.js');
		// Localization for timeago plugin
		$this->modx->regClientScript($this->config['jsUrl'] . 'web/lib/timeago/' . $this->lang . '.js');

		// Add a button at quoting the resource allocation in the footer
		$this->modx->regClientHTMLBlock('<div id="mt_MTpopUpBox"><span class="">' . $this->lang('quote_text') . '</span></div>');

		// Check the settings, turn BBCode
		if ($this->config['bbcode'])
		{
			$this->modx->regClientScript($this->config['jsUrl'] . 'web/bbcode/bbcode.js');
		}

		// Check the settings, turn Highlight
		if ($this->modx->getOption('modxtalks.highlight', null, false))
		{
			$this->modx->regClientScript($this->config['jsUrl'] . 'web/highlight.pack.js');
			$this->modx->regClientCSS($this->config['cssUrl'] . 'web/highlight/' . strtolower($this->modx->getOption('modxtalks.highlighttheme', null, 'GitHub')) . '.css');
		}

		return true;
	}

	/**
	 * Add Styles to head
	 *
	 * @access protected
	 * @return bool true
	 */
	protected function _regStyles()
	{
		$this->modx->regClientCSS($this->config['cssUrl'] . 'web/bbcode/bbcode.css');
		$this->modx->regClientCSS($this->config['cssUrl'] . 'web/styles.css');

		return true;
	}

	/**
	 * Get Script Head
	 *
	 * @access protected
	 * @return bool true
	 */
	protected function _getScriptHead()
	{
		$this->modx->regClientStartupHTMLBlock('<script>var MT = {
        "assetsPath":"' . $this->config['assetsUrl'] . '",
        "conversation":"' . $this->config['conversation'] . '",
        "ctx":"' . $this->context . '",
        "link": "' . $this->modx->getOption('site_url') . $this->modx->resource->uri . '",
        "webPath": "' . MODX_BASE_URL . '",
        "token": "",
        "lang": "' . $this->lang . '",
        "revers": ' . var_export($this->isRevers(), true) . ',
        "commentTpl": "ejs/' . $this->config['commentTpl'] . '.ejs' . '",
        "deletedCommentTpl": "ejs/' . $this->config['deletedCommentTpl'] . '.ejs' . '",
        "scrubberOffsetTop": ' . $this->config['scrubberOffsetTop'] . ',
        "language": {
            "message.ajaxRequestPending": "' . $this->lang('ajax_request_pending') . '",
            "message.ajaxDisconnected": "' . $this->lang('ajax_disconnected') . '",
            "Loading...": "' . $this->lang('loading') . '...",
            "Notifications": "' . $this->lang('notifications') . '",
            "newComment": "' . $this->lang('new_comment') . '",
            "moreText": "' . $this->lang('more_text') . '",
            "message.confirmDelete": "' . $this->lang('confirm_delete') . '",
            "message.confirmLeave":"' . $this->lang('confirmLeave') . '",
            "message.confirm_ip":"' . $this->lang('confirm_ip') . '",
            "message.confirm_email":"' . $this->lang('confirm_email') . '",
            "message.confirmDiscardReply":"' . $this->lang('confirm_discard_reply') . '",
            "Mute conversation": "' . $this->lang('mute_conversation') . '",
            "Unmute conversation": "' . $this->lang('unmute_conversation') . '"
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
	 * @access protected
	 * @return bool
	 */
	protected function _ejsTemplates()
	{
		$commentTpl = $this->config['ejsTemplatesPath'] . $this->config['commentTpl'] . '.ejs';
		$deletedCommentTpl = $this->config['ejsTemplatesPath'] . $this->config['deletedCommentTpl'] . '.ejs';
		if ( ! file_exists($commentTpl))
		{
			$tags = [
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
			];
			$tpl = $this->_getTpl($this->config['commentTpl']);
			$data = preg_replace('@\s{2,}|\n@i', '', $this->_parseTpl($tpl, $tags));
			file_put_contents($commentTpl, $data);
		}
		if ( ! file_exists($deletedCommentTpl))
		{
			$tags = [
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
			];
			$tpl = $this->_getTpl($this->config['deletedCommentTpl']);
			$data = preg_replace('@\s{2,}|\n@i', '', $this->_parseTpl($tpl, $tags));
			file_put_contents($deletedCommentTpl, $data);
		}

		return true;
	}

	/**
	 * Get Comments
	 *
	 * @access protected
	 *
	 * @param string $conversation Conversation Short name
	 * @param integer $limit (Optional) A limit of records to retrieve in the collection
	 * @param integer $id (Optional) Start comment ID
	 *
	 * @return string $output Full processed comments
	 */
	protected function getComments($conversation = '', $limit = 20, $id = '')
	{
		$output = '';
		if (empty($conversation))
		{
			return $output;
		}

		/**
		 * Check the cache section
		 */
		if ( ! $theme = $this->getConversation($conversation))
		{
			return $output;
		}

		$this->config['conversationId'] = $theme->get('id');

		$this->config['commentsCount'] = 0;

		$this->config['slug'] = $this->generateLink($this->config['conversationId'], null, 'abs');

		$count = $theme->total;
		if ($count < 1)
		{
			return $output;
		}

		/**
		 * Get resource URL
		 */
		$link = $this->modx->getOption('site_url') . $this->modx->resource->uri;

		$page = 1;
		$totalPages = ceil($count / ($limit > 0 ? $limit : $count));

		$offset = 0;
		$this->config['startFrom'] = $this->config['lastRead'] = 1;
		if ($id === 'last' && $limit < $count)
		{
			if ( ! $this->isRevers())
			{
				$range = range($count - $limit + 1, $count);
			}
			else
			{
				$range = range(1, $limit);
			}
		}
		elseif (ctype_digit($id))
		{
			$id = intval($id) !== 0 ? intval($id) : 1;
			if ( ! $this->isRevers())
			{
				if ($id === 1 || $id <= $limit)
				{
					$this->modx->sendRedirect($link);
				}
				$this->modx->sendRedirect($this->getLink('page_' . ceil($id / $limit)) . '#comment-' . $id);
			}
			else
			{
				$c = abs($count - $limit + 1);
				if ($c <= $id && $id <= $count)
				{
					$this->modx->sendRedirect($link . '#comment-' . $id);
				}
				$page = ceil(($count - $id + 1) / $limit);
				$this->modx->sendRedirect($this->getLink('page_' . $page) . '#comment-' . $id);
			}
		}
		elseif ($id == date('Y-m', strtotime($id)))
		{
			$idx = $this->getDateIndex($theme->get('id'), date('Y-m', strtotime($id)));
			if ( ! $this->isRevers())
			{
				$range = range($idx, $idx + $limit);
			}
			else
			{
				$last = ($idx - $limit) <= 0 ? 1 : $idx - $limit;
				$range = range($idx, $last);
				unset($last);
			}
		}
		elseif (preg_match('@page_(\d{1,4})@', $id, $match))
		{
			$page = (int) $match[1];
			if ($page === 1)
			{
				$this->modx->sendRedirect($link);
			}
			elseif ($page > $totalPages)
			{
				$this->modx->sendRedirect($link);
			}
			if ( ! $this->isRevers())
			{
				$first = ($page - 1) * $limit + 1;
				$range = range($first, $first + $limit - 1);
			}
			else
			{
				$end = $count - $limit * ($page - 1);
				$first = $count - $limit * $page + 1;
				$range = range($first, $end);
			}
		}
		else
		{
			if ( ! $this->isRevers())
			{
				$range = range($this->config['startFrom'], $limit);
			}
			else
			{
				$range = range($count - $limit + 1, $count);
			}
		}
		// Unset matches elements
		unset($match);

		$comments = $this->getCommentsArray($range, $theme->get('id'));

		if ( ! $comments[0] && $count > 0)
		{
			$this->modx->sendRedirect($link);
		}
		/**
		 * Set total comments count to config
		 */
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

		$isAuthenticated = $this->modx->user->isAuthenticated($this->context) || $this->isModerator();

		$guest_name = $this->lang('guest');
		$quote_text = $this->lang('quote');
		$del_by = $this->lang('deleted_by');

		$btn_like = '';
		$btn_unlike = '';

		if ($isAuthenticated)
		{
			$userId = (int) $this->modx->user->id;
			$restore = $this->lang('restore');
			$btn_like = $this->lang('i_like');
			$btn_unlike = $this->lang('not_like');
		}

		if ($isModerator = $this->isModerator())
		{
			$userInfoTpl = $this->_getTpl($this->config['user_info']);
		}

		if (count($comments[0]))
		{
			reset($comments[0]);
			$first = current($comments[0]);
			$last = end($comments[0]);
			$this->config['startFrom'] = $first['idx'];
			$this->config['lastRead'] = $last['idx'];
			reset($comments[0]);
		}

		$usersIds =& $comments[1];
		$users = [];

		/**
		 * Get registered Users
		 */
		if (count($usersIds))
		{
			$authUsers = $this->getUsers($usersIds);
			foreach ($authUsers as $a)
			{
				$users[$a['id']] = [
					'name' => $a['fullname'] ? $a['fullname'] : $a['username'],
					'email' => $a['email'],
				];
			}
		}

		/**
		 * Create a "more" block item which we can use below.
		 */
		if ($this->isRevers() && $page > 1)
		{
			$href = $page === 2 ? $link : $this->getLink('page_' . ($page - 1));
			$output .= '<div class="mt_scrubberMore mt_scrubberPrevious"><a href="' . $href . '">' . $this->lang('more_text') . '</a></div>';
		}
		else if ( ! $this->isRevers() && $this->config['startFrom'] > 1)
		{
			$linkPrev = $this->config['startFrom'] <= $this->config['commentsPerPage'] ? 1 : $this->config['startFrom'] - $this->config['commentsPerPage'];
			$output .= '<div class="mt_scrubberMore mt_scrubberPrevious"><a href="' . $this->getLink($linkPrev) . '#mt_conversationPosts">' . $this->lang('more_text') . '</a></div>';
		}

		foreach ($comments[0] as $comment)
		{
			$timeMarker = '';
			$date = date($this->config['dateFormat'] . ' O', $comment['time']);
			$funny_date = $this->date_format($comment['time']);
			$index = date('Ym', $comment['time']);

			/**
			 * If this is registered user
			 */
			if ($comment['userId'])
			{
				$name = $users[$comment['userId']]['name'];
				$email = $users[$comment['userId']]['email'];
			}
			else
			{
				/**
				 * If this is guest
				 */
				$name = $comment['username'] ? $comment['username'] : $guest_name;
				$email = $comment['useremail'] ? $comment['useremail'] : 'anonym@anonym.com';
			}

			if ($this->isTimeMarkers())
			{
				/**
				 * If the post before this one has a different relative
				 * time string to this one, output a 'time marker'.
				 */
				$relativeTimeComment = $this->date_format($comment['time'], true);
				if ($relativeTime !== $relativeTimeComment)
				{
					$timeMarker = "<div class=\"mt_timeMarker\" data-now=\"1\">{$relativeTimeComment}</div>";
					$relativeTime = $relativeTimeComment;
				}
			}

			/**
			 * Timeago date format
			 */
			$timeago = date('c', $comment['time']);
			/**
			 * Prepare data for deleted comment
			 */
			if ($comment['deleteTime'] > 0 && $comment['deleteUserId'] > 0)
			{
				$tmp = [
					'deleteUser' => $users[$comment['deleteUserId']]['name'],
					'delete_date' => date($this->config['dateFormat'] . ' O', $comment['deleteTime']),
					'funny_delete_date' => $this->date_format($comment['deleteTime']),
					'name' => $name,
					'index' => $index,
					'date' => $date,
					'funny_date' => $funny_date,
					'id' => $comment['id'],
					'idx' => $comment['idx'],
					'timeago' => $timeago,
					'timeMarker' => $this->isTimeMarkers() ? $timeMarker : '',
					'deleted_by' => $del_by,
					'restore' => '',
					'link' => $this->getLink($comment['idx']),
				];

				if ($isAuthenticated && ($isModerator || $comment['deleteUserId'] === $userId))
				{
					$tmp['restore'] = '<a href="' . $this->getLink('restore-' . $comment['idx']) . '" title="' . $restore . '" class="mt_control-restore">' . $restore . '</a>';
				}
			}
			else
			{
				/**
				 * Prepare data for published comment
				 */
				$tmp = [
					'avatar' => $this->getAvatar($email),
					'hideAvatar' => 'mt_noAvatar',
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
					'timeMarker' => $this->isTimeMarkers() ? $timeMarker : '',
					'link' => $this->getLink($comment['idx']),
					'timeago' => $timeago,
					'user_info' => '',
					'like_block' => '',
				];

				if ($isModerator)
				{
					$tmp['user_info'] = $this->_parseTpl($userInfoTpl, [
						'email' => $email,
						'ip' => $comment['ip']
					]);
				}
				/**
				 * Comment Votes
				 */
				if ($this->config['voting'])
				{
					$likes = '';
					$btn = $btn_like;
					if ($votes = json_decode($comment['votes'], true))
					{
						if ($isAuthenticated && in_array($this->modx->user->id, $votes['users']))
						{
							$btn = $btn_unlike;
							$total = count($votes['users']) - 1;
							if ($total > 0)
							{
								$likes = $this->decliner($total, $this->lang('people_like_and_you', ['total' => $total]));
							}
							else
							{
								$likes = $this->lang('you_like');
							}
						}
						else if ($votes['votes'] > 0)
						{
							$likes = $this->decliner($votes['votes'], $this->lang('people_like', ['total' => $votes['votes']]));
						}
					}

					if ( ! $isAuthenticated && ( ! isset($votes['votes']) || $votes['votes'] == 0))
					{
						$tmp['like_block'] = '';
					}
					else
					{
						$btn = $isAuthenticated ? '<a href="#" class="mt_like-btn">' . $btn . '</a>' : '';
						$tmp['like_block'] = '<div class="mt_like_block">' . $btn . '<span class="mt_likes">' . $likes . '</span></div>';
					}
				}

				/**
				 * If the post before this one is by the same member
				 * as this one, hide the avatar
				 */
				if ($email != $hideAvatarEmail)
				{
					$tmp['hideAvatar'] = '';
					$hideAvatarEmail = $email;
				}
				/**
				 * If comment edited, get edit time and user
				 */
				if ($comment['editTime'] && $comment['editUserId'] && ! $comment['deleteTime'])
				{
					$tmp['funny_edit_date'] = $this->date_format($comment['editTime']);
					$tmp['edit_name'] = $this->lang('edited_by', [
						'name' => $users[$comment['editUserId']]['name']
					]);
				}
			}

			if ($comment['deleteTime'] && $comment['deleteUserId'])
			{
				$output .= $this->_parseTpl($deletedTpl, $tmp);
			}
			else
			{
				$output .= $this->_parseTpl($tpl, $tmp);
			}
		}
		unset($email, $name, $tmp, $tpl, $deletedTpl, $comments);

		if ($this->isRevers() && $page < $totalPages)
		{
			$href = $this->getLink('page_' . ($page + 1));
			$output .= '<div class="mt_scrubberMore mt_scrubberNext"><a href="' . $href . '">' . $this->lang('more_text') . '</a></div>';
		}
		else if ( ! $this->isRevers() && ($this->config['startFrom'] + $this->config['commentsPerPage']) <= $this->config['commentsCount'])
		{
			$output .= '<div class="mt_scrubberMore mt_scrubberNext"><a href="' . $this->getLink(($this->config['lastRead'] + 1)) . '#mt_mt_cf_conversationReply">' . $this->lang('more_text') . '</a></div>';
		}

		return $output;
	}

	/**
	 * Generate comment link
	 *
	 * @access public
	 *
	 * @param string $link
	 *
	 * @return string
	 */
	public function getLink($link = '')
	{
		$link = (string) $link;

		return str_replace($this->config['slugReplace'], $link, $this->config['slug']);
	}

	/**
	 * Get resource alias path
	 *
	 * @access public
	 *
	 * @param string $link
	 * @param string $search
	 *
	 * @return string Resource alias
	 */
	public function aliasPath($link = '', $search = '$$$')
	{
		if ($this->friendly_urls)
		{
			if ( ! isset($this->resource_alias_path) && $this->config['slug'] !== '')
			{
				$this->resource_alias_path = $this->config['slug'];
			}
			if ( ! isset($this->resource_alias_path))
			{
				if ( ! isset($this->resource_alias))
				{
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
	 * @access public
	 *
	 * @param integer $conversationId Conversation Id
	 *
	 * @return string Full rendered scrubber
	 */
	public function getScrubber($conversationId = 0)
	{
		if ($conversationId == 0 && $this->config['conversationId'] == 0)
		{
			return [];
		}
		elseif ($conversationId == 0 && $this->config['conversationId'] != 0)
		{
			$conversationId = $this->config['conversationId'];
		}

		$scrubber = [
			'key' => 0,
			'start' => ! $this->isRevers() ? $this->lang('start') : $this->lang('now'),
			'start_link' => $this->aliasPath('1'),
			'now' => ! $this->isRevers() ? $this->lang('now') : $this->lang('start'),
			'now_link' => $this->aliasPath('last'),
			'reply' => $this->lang('reply'),
			'count_talks' => $this->config['commentsCount'],
			'months' => '',
			'conversation' => $this->modx->resource->id,
			'modxtalks_total' => $this->lang('total'),
		];
		// Conclusion placeholder count_talks. The total number of votes
		$this->modx->setPlaceholder('count_talks', $this->config['commentsCount']);

		// Choose the topics of the month and if necessary, the topics
		$dateScrubber = '';
		$ds = $this->modx->newQuery('modxTalksPost');
		$ds->where([
			'conversationId' => $conversationId
		]);
		$ds->select(['modxTalksPost.date', 'modxTalksPost.time']);
		$ds->groupby('modxTalksPost.date');

		if ($ds->prepare() && $ds->stmt->execute())
		{
			$dsd = $ds->stmt->fetchAll(PDO::FETCH_ASSOC);
			$dsArray = json_decode($this->lang('month_array'));
			$dsYear = [];
			foreach ($dsd as $dsp)
			{
				$scrYear = strftime('%Y', $dsp['time']);
				$dateScrubberm = strftime('%m', $dsp['time']);
				$dateScrubbermi = (int) $dateScrubberm;
				$dsmi = $dsArray->$dateScrubbermi;
				$dLink = $this->getLink((strftime('%Y', $dsp['time']) . '-' . $dateScrubberm));
				$dTitle = mb_convert_case($dsmi[0], MB_CASE_TITLE, "UTF-8");
				$dsYear[$scrYear] = (isset($dsYear[$scrYear]) ? $dsYear[$scrYear] : '') . '<li class="mt_scrubber-' . $dsp['date'] . '" data-index="' . $dsp['date'] . '"><a href="' . $dLink . '">' . $dTitle . '</a></li>';
			}
		}

		foreach ($dsYear as $key => $value)
		{
			if ($key != strftime('%Y', time()))
			{
				$dateScrubber .= '<li class="mt_scrubber-' . $key . '01 selected" data-index="' . $key . '01"><a href="' . $this->getLink($key . '-01') . '">' . $key . '</a><ul>' . $value . '</ul></li>';
			}
			else
			{
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
	 *
	 * @param string $content
	 *
	 * @return string Parsed content
	 */
	public function bbcode($content)
	{
		$tags = [
			'd_1' => ['[_[', ']_]'],
			'd_2' => ['&#091;&#091;', '&#093;&#093;'],
			's_1' => ['[', ']'],
			's_2' => ['&#091;', '&#093;']
		];
		if ( ! $this->config['bbcode'])
		{
			$content = $this->quotes($content);
			$content = str_replace($tags['d_1'], $tags['d_2'], $content);
			$content = str_replace($tags['s_1'], $tags['s_2'], $content);

			return $content;
		}
		if ( ! isset($this->modx->bbcode) || ! ($this->modx->bbcode instanceof BBCode))
		{
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
			if ($this->config['smileys'])
			{
				$this->modx->bbcode->SetEnableSmileys();
				$this->modx->bbcode->SetSmileyURL($this->config['imgUrl'] . 'smileys');
			}
			/**
			 * Detect URL's
			 */
			if ($this->config['detectUrls'])
			{
				$this->modx->bbcode->SetDetectURLs();
			}
			/**
			 * Comment Length
			 */
			if (isset($this->config['commentLength']) && (int) $this->config['commentLength'] > 2)
			{
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
	 * @access public
	 *
	 * @param string $email Email address
	 *
	 * @return string Gravatar image link
	 */
	public function getAvatar($email = '', $size = 0)
	{
		if ($this->config['gravatar'] && ! empty($email))
		{
			$urlsep = $this->modx->context->getOption('xhtml_urls', true) ? '&amp;' : '&';

			return $this->config['gravatarUrl'] . md5($email) . '?s=' . (intval($size) > 0 ? $size : $this->config['gravatarSize']) . $urlsep . 'd=' . urlencode($this->config['defaultAvatar']);
		}

		return $this->config['imgUrl'] . 'avatar.png';
	}

	/**
	 * Determines if this user is moderator in specified groups
	 *
	 * @access public
	 * @return bool True if user is moderator of any groups
	 */
	public function isModerator()
	{
		if ( ! isset($this->groups))
		{
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
	protected function _getForm()
	{
		if ($this->config['onlyAuthUsers'] && ! $this->modx->user->isAuthenticated($this->context) && ! $this->isModerator())
		{
			return $this->getChunk($this->config['commentAuthTpl'], [
				'avatar' => $this->config['defaultAvatar'],
				'noLogin' => $this->lang('no_login')
			]);
		}

		if ($this->config['commentsClosed'] && ! $this->isModerator())
		{
			return $this->_parseTpl($this->config['commentAuthTpl'], null, true);
		}

		if ($this->modx->user->isAuthenticated($this->context) || $this->isModerator())
		{
			$user = $this->modx->user->getOne('Profile');
			$email = $user->get('email');
			$name = $user->get('fullname');
			$tmp = [
				'user' => ! $name ? $this->modx->user->get('username') : $name,
				'avatar' => $this->getAvatar($email),
				'hidden' => ' hidden',
			];
		}
		else
		{
			$tmp = [
				'user' => $this->lang('guest'),
				'avatar' => $this->getAvatar(),
				'hidden' => '',
			];
		}

		$tmp['controlsbb'] = $this->getEditControls();
		$tmp['previewCheckbox'] = $this->lang('preview_checkbox');
		$tmp['reply'] = $this->lang('reply');
		$tmp['link'] = $this->modx->resource->uri;
		$tmp['write_comment'] = $this->lang('write_comment');
		$tmp['your_name_pl'] = $this->lang('your_name_pl');
		$tmp['your_email_pl'] = $this->lang('your_email_pl');

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
	public function _getEditForm($id = 0, $content = '', $ctx = 'web')
	{
		$user = $this->modx->user;
		$name = $user->get('username');
		$email = $user->Profile->get('email');
		$fullname = $user->Profile->get('fullname');
		$tmp = [
			'user' => ! empty($fullname) ? $fullname : $name,
			'avator' => $this->getAvatar($email),
			'controlsbb' => $this->getEditControls('comment-' . $id),
			'previewCheckbox' => $this->lang('preview_checkbox'),
			'content' => $content,
			'id' => $id,
			'write_comment' => $this->lang('write_comment'),
			'save_changes' => $this->lang('save_changes'),
			'cancel' => $this->lang('cancel'),
		];
		$tpl = $this->_getTpl($this->config['commentEditFormTpl']);
		$form = $this->_parseTpl($tpl, $tmp);

		return $form;
	}

	/**
	 * Gets a Chunk and caches it; also falls back to file-based templates
	 * for easier debugging.
	 *
	 * @access public
	 *
	 * @param string $name The name of the Chunk
	 * @param array $properties The properties for the Chunk
	 *
	 * @return string The processed content of the Chunk
	 */
	public function getChunk($name, $properties = [])
	{
		$chunk = null;
		if ( ! isset($this->chunks[$name]))
		{
			$chunk = $this->_getTplChunk($name);
			if (empty($chunk))
			{
				if ( ! $chunk = $this->modx->getObject('modChunk', ['name' => $name]))
				{
					return false;
				}
			}
			$this->chunks[$name] = $chunk->getContent();
		}
		else
		{
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
	 *
	 * @param string $name The name of the Chunk. Will parse to name.$postfix
	 * @param string $postfix The default postfix to search for chunks at.
	 *
	 * @return modChunk/bool Returns the modChunk object if found, otherwise
	 * false.
	 */
	private function _getTplChunk($name, $postfix = '.chunk.tpl')
	{
		$chunk = false;
		$f = $this->config['chunksPath'] . strtolower($name) . $postfix;
		if (file_exists($f))
		{
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
	 * @access public
	 *
	 * @param string $tpl Template file
	 * @param array $arr Array of placeholders
	 * @param bool $chunk If True get chunk, else use template from string
	 * @param string $postfix Chunk postfix if use file-based chunks
	 *
	 * @return string Parsed chunck file
	 */
	public function _parseTpl($tpl = '', $arr = [], $chunk = false, $postfix = '.chunk.tpl')
	{
		if (empty($tpl) && $chunk === false)
		{
			return '';
		}
		elseif ( ! empty($tpl) && $chunk === true)
		{
			$tpl = $this->_getTpl($tpl, $postfix);
		}

		if (count($arr))
		{
			$tmp = [];
			foreach ($arr as $k => $v)
			{
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
	 * @access private
	 *
	 * @param string $tpl Template file
	 * @param string $postfix Chunk postfix if use file-based chunks
	 *
	 * @return string Empty
	 */
	private function _getTpl($tpl = '', $postfix = '.chunk.tpl')
	{
		if (empty($tpl))
		{
			return '';
		}

		if (isset($this->chunks[$tpl]))
		{
			return $this->chunks[$tpl];
		}

		// If useChunk setting set to True, use the modx standard chunk
		if ($this->config['useChunks'] === true)
		{
			if ($chunk = $this->modx->getObject('modChunk', ['name' => $tpl]))
			{
				$this->chunks[$tpl] = $chunk->get('content');

				return $this->chunks[$tpl];
			}
		}
		// If chunk not found or useChunk set to False, use file-based chunk
		$f = $this->config['chunksPath'] . strtolower($tpl) . $postfix;
		if (file_exists($f))
		{
			$this->chunks[$tpl] = file_get_contents($f);

			return $this->chunks[$tpl];
		}

		return '';
	}

	/**
	 * Funny date
	 *
	 * @access public
	 *
	 * @param integer $time UNIX timestamp
	 * @param bool $group
	 *
	 * @return string
	 */
	public function date_format($time, $group = true)
	{
		$seconds = abs(time() - $time);
		$minutes = floor($seconds / 60);
		$hours = floor($minutes / 60);
		$days = floor($hours / 24);
		$months = floor($days / 30);
		$years = floor($days / 365);
		$seconds = floor($seconds);

		if ($group === true && $minutes < 60)
		{
			return $this->lang('date_hours_back_less');
		}
		if ($seconds < 60)
		{
			return $this->decliner($seconds, $this->lang('date_seconds_back', [
				'seconds' => $seconds
			]));
		}
		if ($minutes < 45)
		{
			return $this->decliner($minutes, $this->lang('date_minutes_back', [
				'minutes' => $minutes
			]));
		}
		if ($minutes < 60)
		{
			return $this->lang('date_hours_back_less');
		}
		if ($hours < 24)
		{
			return $this->decliner($hours, $this->lang('date_hours_back', [
				'hours' => $hours
			]));
		}
		if ($days < 30)
		{
			return $this->decliner($days, $this->lang('date_days_back', [
				'days' => $days
			]));
		}
		if ($days < 365)
		{
			return $this->decliner($months, $this->lang('date_month_back', [
				'months' => $months
			]));
		}
		if ($days > 365)
		{
			return $this->decliner($years, $this->lang('date_years_back', [
				'years' => $years
			]));
		}

		return date($this->config['dateFormat'], $time);
	}

	/**
	 * Declension of word
	 *
	 * @access public
	 *
	 * @param int $count
	 * @param string|array $forms
	 *
	 * @return string
	 */
	public function decliner($count, $forms)
	{
		if ( ! is_array($forms))
		{
			$forms = explode(';', $forms);
		}

		$count = abs($count);

		if ($this->lang === 'ru')
		{
			$mod100 = $count % 100;
			switch ($count % 10)
			{
				case 1:
					if ($mod100 == 11)
					{
						return $forms[2];
					}
					else
					{
						return $forms[0];
					}
				case 2:
				case 3:
				case 4:
					if (($mod100 > 10) && ($mod100 < 20))
					{
						return $forms[2];
					}
					else
					{
						return $forms[1];
					}
				case 5:
				case 6:
				case 7:
				case 8:
				case 9:
				case 0:
					return $forms[2];
			}
		}
		else
		{
			/**
			 * If lang not RU
			 */
			return ($count == 1) ? $forms[0] : $forms[1];
		}
	}

	/**
	 * Get user buttons for comment
	 *
	 * @access public
	 *
	 * @param int $userId User Id
	 * @param int $time UNIX timestamp
	 *
	 * @return string HTML buttons
	 */
	public function userButtons($userId = 0, $time = 0)
	{
		/**
		 * If a registered user is a member of moderators, then give moderate comments.
		 */
		$buttons = '<a href="#" title="' . $this->lang('edit') . '" class="mt_control-edit">' . $this->lang('edit') . '</a>';
		$buttons .= '<a href="#" title="' . $this->lang('remove') . '" class="mt_control-delete">' . $this->lang('remove') . '</a>';

		if ($this->isModerator())
		{
			return $buttons;
		}
		elseif ($userId != 0 && $this->modx->user->id == $userId && ($time + $this->config['edit_time']) > time())
		{
			return $buttons;
		}
		if ($this->config['onlyAuthUsers'] && ! $this->modx->user->isAuthenticated($this->context))
		{
			return '';
		}

		return '';
	}

	/**
	 * Make Quotes for modxTalks::quotes()
	 *
	 * @access public
	 *
	 * @param string $text
	 * @param string $postId
	 * @param string $user
	 * @param string $content
	 *
	 * @return string Processed Content
	 */
	public function makeQuote($text, $postId = '', $user = '', $content = '')
	{
		$content = htmlspecialchars($this->modx->stripTags($content));
		$text = htmlspecialchars($this->modx->stripTags($text));
		$user = htmlspecialchars($this->modx->stripTags($user));
		$postId = preg_replace('#[^0-9]#i', '', $postId);

		$quote = $content . '<blockquote>';

		if ( ! empty($postId))
		{
			$link = str_replace($this->config['slugReplace'], $postId, $this->config['slug']);
			$quote .= '<a href="' . $link . '" rel="comment" data-id="' . $postId . '" class="mt_control-search mt_postRef">' . $this->mtConfig['go_to_comment'] . '</a>';
		}

		if ( ! empty($user))
		{
			$quote .= '<cite>' . $user . '</cite>';
		}

		$quote .= $text . '</blockquote>';

		return $quote;
	}

	/**
	 * Make Quotes from BBCode
	 *
	 * @access public
	 *
	 * @param string $content Comment content
	 *
	 * @return string Processed Content
	 */
	public function quotes($content)
	{
		if ( ! isset($this->mtConfig['go_to_comment']))
		{
			$this->mtConfig['go_to_comment'] = $this->lang('go_to_comment');
		}

		$regexp = "/(.*?)\n?\[quote(\s?id\=(.*?))?(\s?user\=\"?(.*?)\"?)?(]?)?\]\n?(.*?)\n?\[\/quote\]\n{0,2}/ise";
		while (preg_match($regexp, $content))
		{
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
	public function get_client_ip()
	{
		if (isset($_SERVER['HTTP_CLIENT_IP']))
		{
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (isset($_SERVER['REMOTE_ADDR']))
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		else
		{
			$ip = '0.0.0.0';
		}

		return $ip;
	}

	/**
	 * Sends an email for conversation
	 *
	 * @access protected
	 *
	 * @param string $subject Email subject
	 * @param string $body Email content
	 * @param string $to Receiver Emails
	 * @param string $body_text Optional, Plain text version of email content
	 *
	 * @return bool
	 */
	protected function sendEmail($subject, $body = '', $to, $body_text = '', $options = [])
	{
		$this->modx->getService('mail', 'mail.modPHPMailer');
		if ( ! $this->modx->mail)
		{
			return false;
		}

		$emailFrom = $this->modx->getOption('modxtalks.emailsFrom', $this->modx->getOption('emailsender'));
		$emailReplyTo = $this->modx->getOption('modxtalks.emailsReplyTo', $this->modx->getOption('emailsender'));

		/* allow multiple to addresses */
		if ( ! is_array($to))
		{
			$to = explode(',', $to);
		}

		$success = false;
		foreach ($to as $emailAddress)
		{
			if (empty($emailAddress) || strpos($emailAddress, '@') === false)
			{
				continue;
			}

			$this->modx->mail->set(modMail::MAIL_BODY, $body);

			if ( ! empty($body_text))
			{
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
	 * @access public
	 *
	 * @param modxTalksPost|modxTalksTempPost $comment A reference to the actual comment
	 *
	 * @return bool True if successful
	 */
	public function notifyModerators(&$comment)
	{
		if ( ! $comment instanceof modxTalksPost && ! $comment instanceof modxTalksTempPost)
		{
			return false;
		}

		$this->modx->lexicon->load('modxtalks:emails');

		/**
		 * Get User info
		 */
		$user = $comment->getUserData();
		$images_url = $this->modx->getOption('site_url') . substr($this->config['imgUrl'], 1);

		if ($comment instanceof modxTalksPost)
		{
			$cid = $comment->conversationId;
			$idx = $comment->idx;
			$link = $this->generateLink($cid, $idx, 'full');
			$subject = $this->lang('email_new_comment');
			$text = $this->lang('email_added_new_comment', [
				'link' => $link,
				'name' => $user['name'],
			]);
		}
		elseif ($comment instanceof modxTalksTempPost)
		{
			$subject = $this->lang('email_new_premoderated_comment');
			$text = $this->lang('email_user_add_premoderated_comment', [
				'name' => $user['name'],
			]);
		}

		$params = [
			'title' => 'Заголовок',
			'content' => $this->modx->stripTags($this->bbcode($comment->content)),
			'images_url' => $images_url,
			'avatar' => $this->getAvatar($user['email'], 50),
			'text' => $text,
			'date' => date($this->config['dateFormat'] . ' O', $comment->time),
		];

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
		if ( ! empty($emails))
		{
			if ($this->sendEmail($subject, $body, $emails))
			{
				$success = true;
			}
		}

		return $success;
	}

	/**
	 * Sends notification to users
	 *
	 * @access public
	 *
	 * @param modxTalksPost $comment A reference to the actual comment
	 *
	 * @return bool True if successful
	 */
	public function notifyUser(&$comment)
	{
		if ( ! $comment instanceof modxTalksPost)
		{
			return false;
		}

		$this->modx->lexicon->load('modxtalks:emails');

		/**
		 * Get User info
		 */
		$user = $comment->getUserData();

		$cid = $comment->conversationId;
		$idx = $comment->idx;

		$link = $this->generateLink($cid, $idx, 'full');
		$images_url = $this->modx->getOption('site_url') . substr($this->config['imgUrl'], 1);

		$subject = $this->lang('email_comment_approved');
		$text = $this->lang('email_user_approve_comment', [
			'link' => $link,
		]);

		$params = [
			'title' => 'Заголовок',
			'content' => $this->modx->stripTags($this->bbcode($comment->content)),
			'images_url' => $images_url,
			'avatar' => $this->getAvatar($user['email'], 50),
			'text' => $text,
			'date' => date($this->config['dateFormat'] . ' O', $comment->time),
		];

		/**
		 * Get email body
		 */
		$body = $this->getChunk('mt_send_mail', $params);

		/**
		 * Send notifications to user
		 */
		$success = false;
		if ( ! empty($user['email']))
		{
			if ($this->sendEmail($subject, $body, $user['email']))
			{
				$success = true;
			}
		}

		return $success;
	}

	/**
	 * Get Users data by groups
	 *
	 * @access public
	 *
	 * @param string|array $groups Moderators groups
	 * @param modxTalksPost $comment A reference to the actual comment
	 *
	 * @return array
	 */
	public function getUsersEmailsByGroups($groups, &$comment)
	{
		if ( ! $comment instanceof modxTalksPost && ! $comment instanceof modxTalksTempPost)
		{
			return false;
		}

		if ( ! is_array($groups))
		{
			$groups = explode(',', $groups);
		}

		$usersIds = [];
		/**
		 * Moderators email addresses
		 */
		$emails = [];
		/**
		 * Moderator ID of this comment
		 */
		$userId = $comment->userId;

		$c = $this->modx->newQuery('modUserGroup');
		$c->where([
			'modUserGroup.name:IN' => $groups
		]);
		$c->select(['modUserGroup.id', 'UserGroupMembers.member']);
		$c->leftJoin('modUserGroupMember', 'UserGroupMembers', 'UserGroupMembers.user_group = modUserGroup.id');

		if ($c->prepare() && $c->stmt->execute())
		{
			$result = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($result as $uid)
			{
				if ($uid['member'] != $userId)
				{
					$usersIds[] = $uid['member'];
				}
			}
		}

		if (count($usersIds))
		{
			$c = $this->modx->newQuery('modUserProfile', [
				'internalKey:IN' => $usersIds
			]);
			$c->select(['id', 'email']);

			if ($c->prepare() && $c->stmt->execute())
			{
				$result = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach ($result as $u)
				{
					$emails[] = $u['email'];
				}
			}
		}

		return $emails;
	}

	/**
	 * Get conversation by conversation name
	 *
	 * @access public
	 *
	 * @param string $name Conversation name
	 *
	 * @return object $conversation
	 */
	public function getConversation($name = '')
	{
		/**
		 * If Conversation name is empty or not defined return False
		 */
		if (empty($name))
		{
			return false;
		}
		/**
		 * Conversation in cache TRUE or FALSE
		 */
		$cCache = false;
		$cache = $this->modx->getCacheManager();
		// Create a key by conversation name
		if ( ! $conversationKey = $this->conversationHash($name))
		{
			return false;
		}
		// If there is a cache, set the flag to TRUE ConversationCache, otherwise FALSE
		if ($this->mtCache && $cache)
		{
			$theme = $this->modx->cacheManager->get($conversationKey, [xPDO::OPT_CACHE_KEY => 'modxtalks/conversation']);

			if ($theme)
			{
				$cCache = true;
				$conversation = $this->modx->newObject('modxTalksConversation', $theme);
				$conversation->set('id', $theme['id']);
			}
			else
			{
				$cCache = false;
			}
		}

		// If the flag is in ConversationCache FALSE - get data from database
		if ( ! $cCache)
		{
			// If the key is not section, create a new
			if ( ! $conversation = $this->modx->getObject('modxTalksConversation', ['conversation' => $name]))
			{
				$conversation = $this->modx->newObject('modxTalksConversation', ['conversation' => $name]);
				$conversation->setSingleProperty($this->modx->resource->id);
				$conversation->set('rid', $this->modx->resource->id);
				$conversation->set('title', $this->modx->resource->pagetitle);
				$conversation->save();
			}

			// Put to the cache
			if ($this->mtCache && $cache)
			{
				$this->modx->cacheManager->set($conversationKey, $conversation, 0, [
					xPDO::OPT_CACHE_KEY => 'modxtalks/conversation'
				]);
			}
		}

		return $conversation;
	}

	/**
	 * Cache conversation
	 *
	 * @access public
	 *
	 * @param object $conversation Conversation object
	 *
	 * @return bool true|false
	 */
	public function cacheConversation(modxTalksConversation & $conversation)
	{
		/**
		 * If $conversation is empty or not defined return False
		 */
		if (empty($conversation))
		{
			return false;
		}

		$cache = $this->modx->getCacheManager();
		if ($this->mtCache && $cache)
		{
			if ( ! $conversationKey = $this->conversationHash($conversation->conversation))
			{
				return false;
			}

			if ( ! $this->modx->cacheManager->set($conversationKey, $conversation, 0, [xPDO::OPT_CACHE_KEY => 'modxtalks/conversation']))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Retrieve comment or comments from cache if it's cached or database
	 *
	 * @access public
	 *
	 * @param string|array $ids Comment idx or Comments idx'es
	 * @param integer $ids Comment ID or Comments IDs
	 *
	 * @return array array[0] - comments, array[1] - users
	 */
	public function getCommentsArray($ids, $conversationId)
	{
		if (empty($ids) || empty($conversationId))
		{
			return false;
		}
		if ( ! is_array($ids))
		{
			$ids = [$ids];
		}
		/**
		 * @var array Result Comments array
		 */
		$comments = [];
		/**
		 * @var array Non cached comments
		 */
		$nonCached = [];
		/**
		 * @var bool True if one or more comments not cached
		 */
		$cached = false;
		$cache = $this->modx->getCacheManager();
		/**
		 * Retrieve comments from cache
		 * те которых нет пишем в массив $nonCached для дальнейшего получения из базы
		 */
		if ($this->mtCache && $cache)
		{
			$cached = true;
			foreach ($ids as $id)
			{
				$comment = $this->modx->cacheManager->get($id, [
					xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/' . $conversationId
				]);

				if ($comment)
				{
					$comments[$id] = $comment;
				}
				else
				{
					$nonCached[] = $id;
				}
			}

			if (count($nonCached))
			{
				$cached = false;
			}
		}

		/**
		 * Get comments by idx and conversation Id
		 */
		if ( ! $cached)
		{
			$c = $this->modx->newQuery('modxTalksPost', ['conversationId' => $conversationId]);
			$c->select([
				'id', 'idx', 'conversationId', 'date', 'content', 'userId', 'time', 'deleteTime', 'deleteUserId',
				'editTime', 'editUserId', 'username', 'useremail', 'ip', 'votes', 'properties'
			]);
			if (count($nonCached) && $this->mtCache && $cache)
			{
				$c->andCondition([
					'idx:IN' => $nonCached
				]);
			}
			else
			{
				$c->andCondition([
					'idx:IN' => $ids
				]);
			}

			if ($c->prepare() && $c->stmt->execute())
			{
				$results = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
				foreach ($results as $result)
				{
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
					if ($this->mtCache && $cache)
					{
						$this->modx->cacheManager->set($result['idx'], $result, 0, [
							xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/' . $conversationId
						]);
					}
				}
			}
		}
		/**
		 * Get id's of registered users
		 */
		$users = [];
		foreach ($comments as $c)
		{
			if ($c['userId'])
			{
				$users[] = $c['userId'];
			}
			if ($c['deleteUserId'])
			{
				$users[] = $c['deleteUserId'];
			}
			if ($c['editUserId'])
			{
				$users[] = $c['editUserId'];
			}
		}
		$users = array_unique($users);

		/**
		 * Sort array ascending by idx
		 */
		if ( ! $this->isRevers())
		{
			ksort($comments);
		}
		else
		{
			krsort($comments);
		}

		return [$comments, $users];
	}

	/**
	 * Cache the comment
	 *
	 * @access public
	 *
	 * @param object $comment Comment object
	 *
	 * @return bool
	 */
	public function cacheComment(modxTalksPost & $comment)
	{
		$cache = $this->modx->getCacheManager();
		if ($this->mtCache && $cache)
		{
			$tmp = $comment->toArray('', true);
			$tmp['raw_content'] = $comment->content;
			$tmp['content'] = $this->bbcode($comment->content);
			if ( ! $this->modx->cacheManager->set($comment->idx, $tmp, 0, [
				xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/' . $comment->conversationId
			])
			)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Delete comment cache
	 *
	 * @access public
	 *
	 * @param object $comment Comment object
	 *
	 * @return bool
	 */
	public function deleteCommentCache(modxTalksPost & $comment)
	{
		$cache = $this->modx->getCacheManager();
		if ($this->mtCache && $cache)
		{
			if ( ! $this->modx->cacheManager->delete($comment->idx, [
				xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/' . $comment->conversationId
			])
			)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Delete all comments cache
	 *
	 * @access public
	 *
	 * @param int $id Conversation ID
	 *
	 * @return bool
	 */
	public function deleteAllCommentsCache($id = 0)
	{
		$cache = $this->modx->getCacheManager();
		if ($this->mtCache && $cache)
		{
			if ($this->modx->cacheManager->refresh([
				'modxtalks/conversation/' . $id => []
			])
			)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Get user or users
	 *
	 * @access public
	 *
	 * @param string|array $ids User id or Users id's
	 * @param integer $ids Comment ID or Comments IDs
	 *
	 * @return array $comments
	 */
	public function getUsers($ids)
	{
		/**
		 * If Users Ids is empty or not defined return False;
		 */
		if (empty($ids))
		{
			return false;
		}

		/**
		 * @var array Result Users array
		 */
		$users = [];
		/**
		 * @var array Non cached comments
		 */
		$nonCached = [];
		/**
		 * Получаем пользователей из кэша
		 * те которых нет в кэше, пишем в массив $nonCached для дальнейшего получения из базы
		 */
		foreach ($ids as $id)
		{
			if ($user = $this->modx->cacheManager->get($id, [xPDO::OPT_CACHE_KEY => 'modxtalks/users/']))
			{
				$users[$id] = $user;
			}
			else
			{
				$nonCached[] = $id;
			}
		}

		if (count($nonCached) === 0)
		{
			return $users;
		}

		/**
		 * Get user by id
		 */
		$c = $this->modx->newQuery('modUser');
		$c->select(['modUser.id', 'modUser.username', 'p.email', 'p.fullname']);
		$c->leftjoin('modUserProfile', 'p', 'modUser.id = p.internalKey');

		if (count($nonCached) > 1)
		{
			$c->andCondition([
				'modUser.id:IN' => $nonCached
			]);
		}
		else
		{
			$c->andCondition([
				'modUser.id' => array_shift($nonCached)
			]);
		}

		if ($c->prepare() && $c->stmt->execute())
		{
			$cache = $this->modx->getCacheManager();
			$results = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($results as $result)
			{
				$users[$result['id']] = $result;
				/**
				 * Cache user data
				 */
				if ($this->mtCache && $cache)
				{
					$this->modx->cacheManager->set($result['id'], $result, 0, [
						xPDO::OPT_CACHE_KEY => 'modxtalks/users']);
				}
			}
		}

		return $users;
	}

	/**
	 * Cache user data
	 *
	 * @access public
	 *
	 * @param object $user User object
	 *
	 * @return bool
	 */
	public function cacheUser(modUser & $user)
	{
		$cache = $this->modx->getCacheManager();

		if ($this->mtCache && $cache)
		{
			$profile = $user->getOne('Profile');
			$tmp = [
				'id' => $user->id,
				'username' => $user->username,
				'email' => $profile->email,
				'fullname' => $profile->fullname,
			];

			if ( ! $this->modx->cacheManager->set($user->id, $tmp, 0, [
				xPDO::OPT_CACHE_KEY => 'modxtalks/users'
			])
			)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Get index of comment by date
	 *
	 * @access public
	 *
	 * @param integer $conversationId Conversation ID
	 * @param string $date Conversation date index
	 *
	 * @return bool
	 */
	public function getDateIndex($conversationId, $date)
	{
		if (empty($conversationId) || empty($date) || $date !== date('Y-m', strtotime($date)))
		{
			return false;
		}

		$date = str_replace('-', '', $date);
		$index = null;

		/**
		 * @var bool True if comment not cached
		 */
		$cached = false;
		$cache = $this->modx->getCacheManager();
		if ($this->mtCache && $cache)
		{
			if ($index = $this->modx->cacheManager->get($date, [
				xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/' . $conversationId . '/dates'
			])
			)
			{
				$cached = true;
			}
		}

		if ($cached == false)
		{
			$c = $this->modx->newQuery('modxTalksPost', [
				'conversationId' => $conversationId,
				'date' => $date
			]);
			$c->sortby('idx', 'ASC');

			if ( ! $index = $this->modx->getObject('modxTalksPost', $c))
			{
				return false;
			}

			$index = $index->get('idx');

			if ($this->mtCache && $cache)
			{
				$this->modx->cacheManager->set($date, $index, 0, [
					xPDO::OPT_CACHE_KEY => 'modxtalks/conversation/' . $conversationId . '/dates'
				]);
			}
		}

		return $index;
	}

	/**
	 * Create conversations map
	 *
	 * @access public
	 * @return mixed False if cache is off
	 */
	public function conversationsMap()
	{
		/**
		 * If cache is disabled return False
		 */
		if ( ! $this->mtCache)
		{
			return false;
		}
		/**
		 * If conversation in cache return it
		 */
		$map = $this->modx->cacheManager->get('conversations_map', [xPDO::OPT_CACHE_KEY => 'modxtalks']);
		if ($map)
		{
			return $map;
		}
		/**
		 * Else if get it from database and put in cache
		 */
		$map = [];
		$c = $this->modx->newQuery('modxTalksConversation');
		$c->select(['id', 'conversation', 'rid']);

		if ($c->prepare() && $c->stmt->execute())
		{
			$conversations = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
			foreach ($conversations as $c)
			{
				$map[$c['rid']][$c['id']] = $c['conversation'];
			}

			$this->modx->cacheManager->set('conversations_map', $map, 0, [
				xPDO::OPT_CACHE_KEY => 'modxtalks'
			]);

		}

		return $map;
	}

	/**
	 * Check resource have conversations
	 *
	 * @access public
	 *
	 * @param integer $id Resource ID
	 *
	 * @return bool True if resource have a conversation
	 */
	public function hasConversations($id)
	{
		if ( ! intval($id))
		{
			return false;
		}
		/**
		 * Check in conversation map
		 */
		if ($map = $this->conversationsMap())
		{
			/**
			 * If resource has more then one conversation return True
			 */
			if (array_key_exists($id, $map))
			{
				return true;
			}

			return false;
		}
		else if ($this->modx->getCount('modxTalksConversation', ['rid' => $id]))
		{
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
	 * @access public
	 *
	 * @param modxTalksPost $comment
	 * @param modxTalksConversation $conversation
	 */
	public function refreshCommentCache(modxTalksPost & $comment, modxTalksConversation & $conversation)
	{
		if ($this->mtCache === true)
		{
			if ( ! $this->cacheComment($comment))
			{
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks::refreshCommentCache] Error cache the comment with ID ' . $comment->id);
			}

			if ( ! $this->cacheConversation($conversation))
			{
				$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks::refreshCommentCache] Error cache the conversation with ID ' . $conversation->id);
			}
		}

		return true;
	}

	/**
	 * Generate comment URL
	 *
	 * @access public
	 *
	 * @param integer $cid Conversation Id
	 * @param integer $idx Comment Idx
	 * @param string $scheme URL scheme - full or abs
	 *
	 * @return string Comment URL
	 */
	public function generateLink($cid = 0, $idx = 0, $scheme = 'full')
	{
		$conversation = $this->modx->getObject('modxTalksConversation', $cid);
		$rid = $conversation->rid;

		if ($scheme !== 'full')
		{
			$scheme = 'abs';
		}

		$ctx = $this->context;

		if ($this->context === 'mgr')
		{
			$config = $this->getProperties($conversation->conversation);
			$ctx = isset($config['context']) ? $config['context'] : $this->context;
		}

		$url = $this->modx->makeUrl($rid, $ctx, '', $scheme);

		$slug = 'comment-' . $this->config['slugReplace'] . '-mt';

		$slarray = explode('/', $url);
		if (count($slarray) > 1)
		{
			$doturi = end($slarray);
			$dotarray = explode('.', $doturi);
			if (count($dotarray) > 1)
			{
				$uriend = end($dotarray);
				$link = str_replace('.' . $uriend, '/' . $slug, $url) . '.' . $uriend;
			}
			else
			{
				$sleh = substr($url, -1) == '/' ? true : false;
				$link = $url . ($sleh ? '' : '/') . $slug . ($sleh ? '/' : '');
			}
		}
		else
		{
			$link = $url . '/' . $slug;
		}

		if (intval($idx) > 0)
		{
			$link = str_replace($this->config['slugReplace'], $idx, $link);
		}

		return $link;
	}

	/**
	 * Slice string
	 *
	 * @access public
	 *
	 * @param string $input String to slice
	 * @param integer $limit String limit. Default = 200
	 *
	 * @return string $output
	 */
	public function slice($input, $limit = 200)
	{
		$limit = isset($limit) ? $limit : 200;
		$enc = 'UTF-8';
		$len = mb_strlen($input, $enc);
		if ($limit > $len)
		{
			$limit = $len;
		}

		return trim(mb_substr($input, 0, $limit, $enc)) . ($limit == $len ? '' : '...');
	}

	/**
	 * Get Latest comments
	 *
	 * @access public
	 * @return string $output
	 */
	public function getLatestComments()
	{
		$this->modx->regClientCSS($this->config['cssUrl'] . 'web/mt_cl.css');
		$this->modx->regClientScript($this->config['jsUrl'] . 'web/mt_cl.js');
		$this->modx->regClientStartupHTMLBlock('<script>var MTL = {
        connectorUrl: "' . $this->config['ajaxConnectorUrl'] . '",
        limit: ' . $this->config['commentsLatestLimit'] . ',
        updateInterval: ' . $this->config['lates_comments_update'] . "\n"
			. '};</script>');

		$comments = $this->modx->runProcessor('web/comments/latest', [], [
			'processors_path' => $this->config['processorsPath']
		]);
		$comments =& $comments->response['results'];

		$output = '';
		foreach ($comments as $c)
		{
			$output .= $this->_parseTpl($this->config['commentLatestTpl'], $c, true);
		}

		$output = $this->_parseTpl($this->config['commentsLatestOutTpl'], [
			'output' => $output
		], true);

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
	public function sliceStringByWords($string, $count)
	{
		$words = preg_split('@[\s\r\n]+@um', $string);
		if ($count < count($words))
		{
			$words = array_slice($words, 0, $count);
		}

		return implode(' ', $words);
	}

	/**
	 * Prepare data for Ajax requests
	 */
	public function ajaxInit()
	{
		if (empty($this->config['conversation']))
		{
			$this->config['conversation'] = $this->modx->resource->class_key . '-' . $this->modx->resource->id;
		}

		$conversation = $this->validateConversation($this->config['conversation']);
		if ($conversation !== true)
		{
			return $conversation;
		}

		$this->_ejsTemplates();

		$this->context = $this->modx->context->key;
	}

	/**
	 * Validate conversation name
	 *
	 * @param string $value Conversation name
	 *
	 * @return mixed True if conversation name is valid or error message
	 */
	public function validateConversation($value = '')
	{
		if (preg_match('@[^a-zA-z-_.0-9]@i', $value))
		{
			return $this->lang('unallowed_symbols');
		}
		else if (strlen($value) < 2 || strlen($value) > 63)
		{
			return $this->lang('bad_id');
		}

		return true;
	}

	/**
	 * Cache snippet start properties
	 *
	 * @param string $conversation Conversation name
	 * @param array $config Config to cache
	 *
	 * @return bool False if conversation name is invalid
	 */
	public function cacheProperties($conversation = null, $config = [])
	{
		if ( ! $keyConversation = $this->conversationHash($conversation))
		{
			return false;
		}

		$path = 'modxtalks/properties';
		$config['context'] = $this->context;
		$this->modx->cacheManager->set($keyConversation, $config, 0, [
			xPDO::OPT_CACHE_KEY => $path,
			xPDO::OPT_CACHE_HANDLER => 'xPDOFileCache',
		]);
	}

	/**
	 * Get snippet properties from cache
	 *
	 * @param string $conversation Conversation name
	 *
	 * @return array|bool False if conversation name is invalid
	 */
	public function getProperties($conversation = null)
	{
		if ( ! $keyConversation = $this->conversationHash($conversation))
		{
			return false;
		}

		$path = 'modxtalks/properties';
		$config = $this->modx->cacheManager->get($keyConversation, [
			xPDO::OPT_CACHE_KEY => $path,
			xPDO::OPT_CACHE_HANDLER => 'xPDOFileCache',
		]);

		return is_array($config) ? $config : [];
	}

	/**
	 * Generate conversation hash by conversation name
	 *
	 * @param string $conversation Conversation name
	 *
	 * @return string Hash of conversation name
	 */
	public function conversationHash($conversation = null)
	{
		if (empty($conversation))
		{
			return false;
		}

		return md5('modxtalks::' . $conversation);
	}

	/**
	 * Check IP for block
	 *
	 * @param string $action
	 * @param array $allowedActions Array of allowed actions to check
	 *
	 * @return mixed True if IP address not blocked
	 */
	public function checkIp($action, $allowedActions = [])
	{
		if ( ! in_array($action, $allowedActions))
		{
			$ip = $this->get_client_ip();
			$ip = explode('.', $ip);
			$ipArr = [
				$ip[0] . '.',
				$ip[0] . '.' . $ip[1] . '.',
				$ip[0] . '.' . $ip[1] . '.' . $ip[2] . '.',
				$ip[0] . '.' . $ip[1] . '.' . $ip[2] . '.' . $ip[3]
			];

			if ($this->modx->getCount('modxTalksIpBlock', ['ip:IN' => $ipArr]))
			{
				return '{"message":"' . $this->lang('ip_blacklist_confirm') . '","success":false}';
			}
		}

		return true;
	}

	/**
	 * Is Revers
	 *
	 * @access public
	 * @return bool
	 */
	public function isRevers()
	{
		return $this->revers;
	}

	/**
	 * Is Time Markers
	 *
	 * @access public
	 * @return bool
	 */
	public function isTimeMarkers()
	{
		return (bool) $this->config['timeMarkers'];
	}

	/**
	 * Get current Context Key
	 *
	 * @access public
	 * @return string
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Send Mail
	 *
	 * @param $commentId
	 *
	 * @return bool
	 */
	public function sendMail($commentId = 0)
	{
		if ( ! intval($commentId))
		{
			return false;
		}

		$mail = $this->modx->newObject('modxTalksMails', [
			'post_id' => $commentId
		]);

		if ($mail->save() !== true)
		{
			$this->modx->log(xPDO::LOG_LEVEL_ERROR, '[modxTalks::sendMail] Error add mail with ID ' . $commentId);

			return false;
		}

		$mailer = $this->config['basePath'] . 'mailer.php';
		exec('php ' . $mailer . ' > ' . $this->config['basePath'] . 'error.log &');

		return true;
	}

	/**
	 * Debug function for printing vars, arrays or objects
	 *
	 * @access private
	 *
	 * @param mixed $var
	 *
	 * @return void
	 */
	protected function pr($var)
	{
		if (is_object($var))
		{
			if ( ! method_exists($var, 'toArray'))
			{
				return;
			}
			$var = $var->toArray();
		}

		echo '<pre style="font:15px Consolas;padding:10px;border:1px solid #c2c2c2;border-radius:10px;background-color:f7f7f7;box-shadow:0 1px 2px #ccc;">';
		print_r($var);
		echo '</pre>';
	}

	public function lang($key = '', $params = [])
	{
		return $this->modx->lexicon("modxtalks.{$key}", $params);
	}
}
