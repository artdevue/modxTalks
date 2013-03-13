<?php
/**
 * Get Latest Comments
 *
 * @package modxtalks
 * @subpackage processors
 */
class getLatestCommentsListProcessor extends modObjectGetListProcessor {
    public $classKey = 'modxTalksLatestPost';
    public $languageTopics = array('modxtalks:default');
    public $objectType = 'modxtalks.latestpost';
    public $limit;
    public $time;
    public $action;

    /**
     * {@inheritDoc}
     * @return mixed
     */
    public function process() {
        $this->action = $this->getProperty('action');
        $data = $this->getData();
        if ($this->action === 'latest') {
            $output = array();
            foreach ($data['results'] as $r) {
                $output[$r['cid']] = $this->modx->modxtalks->_parseTpl($this->modx->modxtalks->config['commentLatestTpl'], $r, true);
            }
            return $this->outputArray($output,$data['total']);
        }
        return $data;
    }

    public function getData() {
        $this->time = (int) $this->getProperty('time');
        $this->limit = (int) $this->modx->modxtalks->config['commentsLatestLimit'];

        $data = array('total' => 0, 'results' => array());

        $q = $this->modx->newQuery('modxTalksLatestPost');
        if ($this->action === 'latest') {
            $q->where(array('time:>' => $this->time));
        }

        $count = $this->modx->getCount('modxTalksLatestPost',$q);

        if ($count == 0) {
            return $data;
        }

        $q->select($this->modx->getSelectColumns('modxTalksLatestPost'));
        $q->sortby('time','DESC');
        $q->limit($this->limit);

        $comments = array();
        if ($q->prepare() && $q->stmt->execute()) {
            $comments = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $list = array();
        $date_format = $this->modx->modxtalks->config['dateFormat'];

        foreach ($comments as $k => $comment) {
            /**
             * Prepare data for published comment
             */
            $comment['content'] = $this->modx->stripTags($comment['content']);
            $list[] = array(
                'name'       => $comment['name'],
                'avatar'     => $this->modx->modxtalks->getAvatar($comment['email']),
                'date'       => date($date_format.' O',$comment['time']),
                'funny_date' => $this->modx->modxtalks->date_format($comment['time']),
                'id'         => $comment['pid'],
                'cid'        => $comment['cid'],
                'idx'        => $comment['idx'],
                'link'       => $comment['link'],
                'timeago'    => date('c',$comment['time']),
                'time'       => $comment['time'],
                'content'    => $this->modx->modxtalks->slice($comment['content']),
                'total'      => $comment['total'],
                'title'      => $comment['title'],
            );
        }

        $data['total'] = $count;
        $data['results'] =& $list;
        return $data;
    }

}

return 'getLatestCommentsListProcessor';