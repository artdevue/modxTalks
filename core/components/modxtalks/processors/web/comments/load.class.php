<?php

/**
 * Get Comments list
 *
 * @package modxtalks
 * @subpackage processors
 */
class getCommentsListProcessor extends modObjectGetListProcessor {
    public $classKey = 'modxTalksPost';
    public $languageTopics = array('modxtalks:default');
    public $limit = 20;
    public $start = 0;
    private $conversationId;
    private $context;
    private $revers;

    /**
     * {@inheritDoc}
     * @return mixed
     */
    public function process() {
        $beforeQuery = $this->beforeQuery();
        if ($beforeQuery !== true) {
            return $this->failure($beforeQuery);
        }

        $data = $this->getData();
        $list = array();
        if ($data['results']) {
            $list = $this->iterate($data);
        }

        return $this->outputArray($list, $data['total']);
    }

    public function beforeQuery() {
        if ($this->modx->modxtalks->config['commentsPerPage'] != 0) {
            $this->limit = $this->modx->modxtalks->config['commentsPerPage'];
        }

        $this->conversation = (string) $this->getProperty('conversation');

        $this->revers = $this->modx->modxtalks->getRevers();

        $this->context = $this->modx->modxtalks->getContext();

        /**
         * Check Conversation
         */
        if (!$this->theme = $this->modx->modxtalks->getConversation($this->conversation)) {
            $this->failure($this->modx->lexicon('modxtalks.empty_conversationId'));

            return false;
        }

        $this->conversationId = $this->theme->id;

        return parent::beforeQuery();
    }

    public function getData() {
        $data = array(
            'total' => 0,
            'results' => array()
        );

        $count = $this->theme->getProperty('total', 'comments');
        $data['total'] = $count;
        if ($count < 1) {
            return $data;
        }

        if ($slug = $this->getProperty('slug')) {
            $this->modx->modxtalks->config['slug'] = $slug;
        }

        $this->start = $this->getProperty('start');
        if ($this->start == date('Y-m', strtotime($this->start))) {
            $idx = $this->modx->modxtalks->getDateIndex($this->conversationId, date('Y-m', strtotime($this->start)));
            if (!$this->revers) {
                $range = range($idx, $idx + $this->limit);
            } else {
                $last = ($idx - $this->limit) <= 0 ? 1 : $idx - $this->limit;
                $range = range($idx, $last);
                unset($last);
            }
        } else {
            $this->start = (int) $this->start;
            if (!$this->revers) {
                $range = range($this->start, $this->start + $this->limit - 1);
            } else {
                $start = $this->start - $this->limit + 1;
                if ($this->start <= $count) {
                    $range = range($start, $this->start);
                } elseif (($this->start - $this->limit) < $count) {
                    $range = range($start, $count);
                } else {
                    return $data;
                }
            }
        }

        $comments = $this->modx->modxtalks->getCommentsArray($range, $this->conversationId);

        $usersIds =& $comments[1];
        $users = array();
        if (count($usersIds)) {
            $authUsers = $this->modx->modxtalks->getUsers($usersIds);
            foreach ($authUsers as $a) {
                $users[$a['id']] = array(
                    'name' => $a['fullname'] ? $a['fullname'] : $a['username'],
                    'email' => $a['email'],
                );
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
    public function iterate(array $data) {
        $list = array();
        $link = $this->modx->getOption('site_url');
        $users =& $data['users'];
        $hideAvatar = '';
        $hideAvatarEmail = '';
        $relativeTime = '';
        $date_format = $this->modx->modxtalks->config['dateFormat'];
        $isAuthenticated = $this->modx->user->isAuthenticated($this->context) || $this->modx->modxtalks->isModerator();
        $voting = $this->modx->modxtalks->config['voting'];
        /**
         * Languages...
         */
        $quote_text = $this->modx->lexicon('modxtalks.quote');
        $guest_name = $this->modx->lexicon('modxtalks.guest');
        $del_by = $this->modx->lexicon('modxtalks.deleted_by');
        $restore = $this->modx->lexicon('modxtalks.restore');
        $btn_like = '';
        if ($isAuthenticated === true) {
            $userID = $this->modx->user->id;
            $btn_like = $this->modx->lexicon('modxtalks.i_like');
            $btn_unlike = $this->modx->lexicon('modxtalks.not_like');
        }

        $isModerator = $this->modx->modxtalks->isModerator();
        if ($isModerator === true) {
            $userInfoTpl = $this->modx->modxtalks->config['user_info'];
        }

        foreach ($data['results'] as $k => $comment) {
            $funny_date = $this->modx->modxtalks->date_format($comment['time']);
            $index = date('Ym', $comment['time']);
            $date = date($date_format . ' O', $comment['time']);
            $timeMarker = '';
            if ($comment['userId'] > 0) {
                $name = $users[$comment['userId']]['name'];
                $email = $users[$comment['userId']]['email'];
            } else {
                $name = $comment['username'] ? $comment['username'] : $guest_name;
                $email = $comment['useremail'] ? $comment['useremail'] : 'anonym@anonym.com';
            }

            $userId = md5($comment['userId'] . $email);

            $relativeTimeComment = $this->modx->modxtalks->date_format($comment['time'], true);
            if ($relativeTime != $relativeTimeComment) {
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
                    'delete_date' => date($date_format . ' O', $comment['deleteTime']),
                    'funny_delete_date' => $this->modx->modxtalks->date_format($comment['deleteTime']),
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
                    'link' => $this->modx->modxtalks->getLink($comment['idx']),
                );

                if ($isAuthenticated && ($isModerator === true || $comment['deleteUserId'] === $userID)) {
                    $tmp['restore'] = '<a href="' . $this->modx->modxtalks->getLink('restore-' . $comment['idx']) . '" title="' . $restore . '" class="mt_control-restore">' . $restore . '</a>';
                }
            } /**
             * Prepare data for published comment
             */
            else {
                $tmp = array(
                    'avatar' => $this->modx->modxtalks->getAvatar($email),
                    'hideAvatar' => ' style="display:none"',
                    'name' => $name,
                    'content' => $comment['content'],
                    'index' => $index,
                    'date' => $date,
                    'funny_date' => $funny_date,
                    'link_reply' => $this->modx->modxtalks->getLink('mt_replay-' . $comment['idx']),
                    'id' => $comment['id'],
                    'idx' => $comment['idx'],
                    'userId' => $userId,
                    'quote' => $quote_text,
                    'user' => $this->modx->modxtalks->userButtons($comment['userId'], $comment['time']),
                    'timeMarker' => $timeMarker,
                    'link' => $this->modx->modxtalks->getLink($comment['idx']),
                    'funny_edit_date' => '',
                    'edit_name' => '',
                    'timeago' => $timeago,
                    'user_info' => '',
                    'like_block' => '',
                );

                if ($isModerator === true) {
                    $tmp['user_info'] = $this->modx->modxtalks->parseTpl($userInfoTpl, array(
                        'email' => $email,
                        'ip' => $comment['ip']
                    ), true);
                }
                /**
                 * Check for voting
                 */
                if ($voting) {
                    /**
                     * Comment Votes
                     */
                    $likes = '';
                    $btn = $btn_like;
                    if ($votes = json_decode($comment['votes'], true)) {
                        if ($isAuthenticated === true && in_array($this->modx->user->id, $votes['users'])) {
                            $btn = $btn_unlike;
                            $total = count($votes['users']) - 1;
                            if ($total > 0) {
                                $likes = $this->modx->modxtalks->decliner($total, $this->modx->lexicon('modxtalks.people_like_and_you', array('total' => $total)));
                            } else {
                                $likes = $this->modx->lexicon('modxtalks.you_like');
                            }
                        } elseif ($votes['votes'] > 0) {
                            $likes = $this->modx->modxtalks->decliner($votes['votes'], $this->modx->lexicon('modxtalks.people_like', array('total' => $votes['votes'])));
                        }
                    }

                    if ($isAuthenticated === false && (!isset($votes['votes']) || $votes['votes'] == 0)) {
                        $tmp['like_block'] = '';
                    } else {
                        $btn = $isAuthenticated === true ? '<a href="#" class="mt_like-btn">' . $btn . '</a>' : '';
                        $tmp['like_block'] = '<div class="mt_like_block">' . $btn . '<span class="mt_likes">' . $likes . '</span></div>';
                    }
                }

                if ($email !== $hideAvatarEmail) {
                    $tmp['hideAvatar'] = '';
                    $hideAvatarEmail = $email;
                }

                if ($comment['editTime'] && $comment['editUserId'] && !$comment['deleteTime']) {
                    $tmp['funny_edit_date'] = $this->modx->modxtalks->date_format($comment['editTime']);
                    $tmp['edit_name'] = $this->modx->lexicon('modxtalks.edited_by', array('name' => $users[$comment['editUserId']]['name']));;
                }
            }

            $list[] = $tmp;

        }

        return $list;
    }
}

return 'getCommentsListProcessor';
