<?php
/**
 * Get Latest Comments
 *
 * @package modxtalks
 * @subpackage processors
 */
class getLatestCommentsListProcessor extends modObjectGetListProcessor {
    public $classKey = 'modxTalksPost';
    public $languageTopics = array('modxtalks:default');
    public $limit = 10;
    public $start = 0;

    public function beforeQuery() {
        if ($this->modx->modxtalks->config['commentsPerPage'] != 0) {
            $this->limit = $this->modx->modxtalks->config['commentsPerPage'];
        }

        return parent::beforeQuery();
    }

    public function getData() {
        $data = array('total' => 0, 'results' => array());

        $count = $this->theme->getProperty('total','comments');
        if ($count < 1) return $data;

        if ($slug = $this->getProperty('slug')) {
            $this->modx->modxtalks->config['slug'] = $slug;
        }

        $array = ();

        $comments = $this->modx->modxtalks->getCommentsArray($array,$this->conversationId);

        $usersIds =& $comments[1];
        $users = array();
        if (count($usersIds)) {
            $authUsers = $this->modx->modxtalks->getUsers($usersIds);
            foreach ($authUsers as $a) {
                $users[$a['id']] = array(
                    'name'  => $a['fullname'] ? $a['fullname'] : $a['username'],
                    'email' => $a['email'],
                );
            }
        }

        $data['total'] = $count;
        $data['results'] =& $comments[0];
        $data['users'] =& $users;
        return $data;
    }


    /**
     * Iterate across the data
     *
     * @param array $data
     * @return array
     */
    public function iterate(array $data) {
        $list = array();
        $link = $this->modx->getOption('site_url');
        $users =& $data['users'];
        $relativeTime = '';
        $date_format = $this->modx->modxtalks->config['mtDateFormat'];
        /**
         * Languages...
         */
        $guest_name = $this->modx->lexicon('modxtalks.guest');

        foreach ($data['results'] as $k => $comment) {
            $funny_date = $this->modx->modxtalks->date_format(array('date' => $comment['time']));
            $index = date('Ym',$comment['time']);
            $date = date($date_format.' O',$comment['time']);
            if ($comment['userId'] > 0) {
                $name = $users[$comment['userId']]['name'];
                $email = $users[$comment['userId']]['email'];
            }
            else {
                $name = $comment['username'] ? $comment['username'] : $guest_name;
                $email = $comment['useremail'] ? $comment['useremail'] : 'anonym@anonym.com';
            }

            $userId = md5($comment['userId'].$email);

            $relativeTimeComment = $this->modx->modxtalks->relativeTime($comment['time']);
            if ($relativeTime != $relativeTimeComment) {
                $timeMarker = '<div class="timeMarker" data-now="1">'.$relativeTimeComment.'</div>';
                $relativeTime = $relativeTimeComment;
            }
            /**
             * Timeago date format
             */
            $timeago = date('c',$comment['time']);
            /**
             * Prepare data for published comment
             */
            else {
                $tmp = array(
                    'name'       => $name,
                    'index'      => $index,
                    'date'       => $date,
                    'funny_date' => $funny_date,
                    'id'         => $comment['id'],
                    'idx'        => $comment['idx'],
                    'link'       => $this->modx->modxtalks->getLink($comment['idx']),
                    'timeago'    => $timeago,
                );

            }

            $list[] = $tmp;

        }
        return $list;
    }

}

return 'getLatestCommentsListProcessor';