<?php

require_once dirname(dirname(dirname(__FILE__))) . '/modxtalksprocessor.trait.php';

/**
 * Get Comments list
 *
 * @package modxtalks
 * @subpackage processors
 */
class getCommentsListProcessor extends modObjectGetListProcessor
{
	use modxTalksProcessorTrait;

	public $classKey = 'modxTalksPost';
	public $languageTopics = ['modxtalks:default'];
	public $limit = 20;
	public $start = 0;
	/**
	 * @var modxTalksConversation
	 */
	protected $conversation;
	protected $conversationId;
	protected $context;

	/**
	 * {@inheritDoc}
	 * @return mixed
	 */
	public function process()
	{
		$beforeQuery = $this->beforeQuery();
		if ($beforeQuery !== true)
		{
			return $this->failure($beforeQuery);
		}
		$data = $this->getData();
		$list = [];
		if ($data['results'])
		{
			$list = $this->iterate($data);
		}

		return $this->outputArray($list, $data['total']);
	}

	public function beforeQuery()
	{
		if ($this->app()->config['commentsPerPage'] != 0)
		{
			$this->limit = $this->app()->config['commentsPerPage'];
		}

		$this->context = $this->app()->getContext();

		/**
		 * Check Conversation
		 */
		$this->conversation = $this->app()->getConversation((string) $this->getProperty('conversation'));
		if ( ! $this->conversation)
		{
			$this->failure($this->app()->lang('empty_conversationId'));

			return false;
		}

		return parent::beforeQuery();
	}

	public function getData()
	{
		$data = [
			'total' => 0,
			'results' => []
		];

		$count = $this->conversation->total;
		$data['total'] = $count;
		if ($count < 1)
		{
			return $data;
		}

		if ($slug = $this->getProperty('slug'))
		{
			$this->app()->config['slug'] = $slug;
		}

		$this->start = $this->getProperty('start');
		if ($this->start == date('Y-m', strtotime($this->start)))
		{
			$idx = $this->app()->getDateIndex(
				$this->conversation->id,
				date('Y-m', strtotime($this->start))
			);

			if ( ! $this->app()->isRevers())
			{
				$range = range($idx, $idx + $this->limit);
			}
			else
			{
				$last = ($idx - $this->limit) <= 0 ? 1 : $idx - $this->limit;
				$range = range($idx, $last);
				unset($last);
			}
		}
		else
		{
			$this->start = (int) $this->start;
			if ( ! $this->app()->isRevers())
			{
				$range = range($this->start, $this->start + $this->limit - 1);
			}
			else
			{
				$start = $this->start - $this->limit + 1;
				if ($this->start <= $count)
				{
					$range = range($start, $this->start);
				}
				elseif (($this->start - $this->limit) < $count)
				{
					$range = range($start, $count);
				}
				else
				{
					return $data;
				}
			}
		}

		$comments = $this->app()->getCommentsArray($range, $this->conversation->id);

		$usersIds =& $comments[1];
		$users = [];
		if (count($usersIds))
		{
			$authUsers = $this->app()->getUsers($usersIds);
			foreach ($authUsers as $a)
			{
				$users[$a['id']] = [
					'name' => $a['fullname'] ? $a['fullname'] : $a['username'],
					'email' => $a['email'],
				];
			}
		}

		$data['results'] =& $comments[0];
		$data['users'] =& $users;

		return $data;
	}


	/**
	 * Iterate across the data
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function iterate(array $data)
	{
		$list = [];
		$link = $this->modx->getOption('site_url');
		$users =& $data['users'];
		$hideAvatar = '';
		$hideAvatarEmail = '';
		$relativeTime = '';
		$date_format = $this->app()->config['dateFormat'];
		$isModerator = $this->app()->isModerator();
		$isAuthenticated = $this->modx->user->isAuthenticated($this->context) || $isModerator;
		$voting = $this->app()->config['voting'];
		/**
		 * Languages...
		 */
		$quote_text = $this->app()->lang('quote');
		$guest_name = $this->app()->lang('guest');
		$del_by = $this->app()->lang('deleted_by');
		$restore = $this->app()->lang('restore');
		$btn_like = '';

		if ($isAuthenticated)
		{
			$userID = $this->modx->user->id;
			$btn_like = $this->app()->lang('i_like');
			$btn_unlike = $this->app()->lang('not_like');
		}

		if ($isModerator)
		{
			$userInfoTpl = $this->app()->config['user_info'];
		}

		foreach ($data['results'] as $k => $comment)
		{
			$funny_date = $this->app()->date_format($comment['time']);
			$index = date('Ym', $comment['time']);
			$date = date($date_format . ' O', $comment['time']);
			$timeMarker = '';
			if ($comment['userId'] > 0)
			{
				$name = $users[$comment['userId']]['name'];
				$email = $users[$comment['userId']]['email'];
			}
			else
			{
				$name = $comment['username'] ? $comment['username'] : $guest_name;
				$email = $comment['useremail'] ? $comment['useremail'] : 'anonym@anonym.com';
			}

			$userId = md5($comment['userId'] . $email);

			$relativeTimeComment = $this->app()->date_format($comment['time'], true);
			if ($relativeTime != $relativeTimeComment)
			{
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
			if ($comment['deleteTime'] > 0 && $comment['deleteUserId'] > 0)
			{
				$tmp = [
					'deleteUser' => $users[$comment['deleteUserId']]['name'],
					'delete_date' => date($date_format . ' O', $comment['deleteTime']),
					'funny_delete_date' => $this->app()->date_format($comment['deleteTime']),
					'name' => $name,
					'index' => $index,
					'date' => $date,
					'funny_date' => $funny_date,
					'id' => $comment['id'],
					'idx' => $comment['idx'],
					'timeMarker' => $timeMarker,
					'userId' => $userId,
					'timeago' => $timeago,
					'deleted_by' => $del_by,
					'restore' => '',
					'link' => $this->app()->getLink($comment['idx']),
				];
				if ($isAuthenticated && ($isModerator || $comment['deleteUserId'] === $userID))
				{
					$tmp['restore'] = '<a href="' . $this->app()->getLink('restore-' . $comment['idx']) . '" title="' . $restore . '" class="mt_control-restore">' . $restore . '</a>';
				}
			}
			/**
			 * Prepare data for published comment
			 */
			else
			{
				$tmp = [
					'avatar' => $this->app()->getAvatar($email),
					'hideAvatar' => ' style="display:none"',
					'name' => $name,
					'content' => $comment['content'],
					'index' => $index,
					'date' => $date,
					'funny_date' => $funny_date,
					'link_reply' => $this->app()->getLink('mt_reply-' . $comment['idx']),
					'id' => $comment['id'],
					'idx' => $comment['idx'],
					'userId' => $userId,
					'quote' => $quote_text,
					'user' => $this->app()->userButtons($comment['userId'], $comment['time']),
					'timeMarker' => $timeMarker,
					'link' => $this->app()->getLink($comment['idx']),
					'funny_edit_date' => '',
					'edit_name' => '',
					'timeago' => $timeago,
					'user_info' => '',
					'like_block' => '',
				];
				if ($isModerator)
				{
					$tmp['user_info'] = $this->app()->_parseTpl($userInfoTpl, [
						'email' => $email,
						'ip' => $comment['ip']
					], true);
				}
				/**
				 * Check for voting
				 */
				if ($voting)
				{
					/**
					 * Comment Votes
					 */
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
								$likes = $this->app()->decliner($total, $this->app()->lang('people_like_and_you', ['total' => $total]));
							}
							else
							{
								$likes = $this->app()->lang('you_like');
							}
						}
						elseif ($votes['votes'] > 0)
						{
							$likes = $this->app()->decliner($votes['votes'], $this->app()->lang('people_like', ['total' => $votes['votes']]));
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

				if ($email !== $hideAvatarEmail)
				{
					$tmp['hideAvatar'] = '';
					$hideAvatarEmail = $email;
				}
				if ($comment['editTime'] && $comment['editUserId'] && ! $comment['deleteTime'])
				{
					$tmp['funny_edit_date'] = $this->app()->date_format($comment['editTime']);
					$tmp['edit_name'] = $this->app()->lang(
						'edited_by', ['name' => $users[$comment['editUserId']]['name']]
					);
				}
			}

			$list[] = $tmp;
		}

		return $list;
	}
}

return 'getCommentsListProcessor';
