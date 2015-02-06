<?php

/**
 * Get a list of unconfirmed posts
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalksTempPostGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'modxTalksTempPost';
    public $languageTopics = array('modxtalks:default');
    public $defaultSortField = 'id';

    /**
     * Iterate across the data
     *
     * @param array $data
     *
     * @return array
     */
    public function iterate(array $data) {
        $list = array();
        $list = $this->beforeIteration($list);
        $this->currentIndex = 0;

        /** @var array $uIds Users Ids */
        $uIds = array();
        /** @var array $cIds Conversations Ids */
        $cIds = array();
        foreach ($data['results'] as $comment) {
            if ($comment->userId > 0) {
                $uIds[] = $comment->userId;
            }
            if ($comment->conversationId > 0) {
                $cIds[] = $comment->conversationId;
            }
        }
        $uIds = array_unique($uIds);
        $cIds = array_unique($cIds);

        /** @var array $conversations Conversations - Name and Url */
        $conversations = array();
        if (count($cIds) > 0) {
            $q = $this->modx->newQuery('modxTalksConversation', array(
                'id:IN' => $cIds
            ));
            $q->select(array('id', 'conversation', 'properties'));
            if ($q->prepare() && $q->stmt->execute()) {
                $tmp = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($tmp as $c) {
                    $properties = $this->modx->fromJSON($c['properties'], true);
                    $url = $this->modx->makeUrl($properties['id']);
                    $conversations[$c['id']] = array(
                        'name' => $c['conversation'],
                        'url' => $url,
                    );
                }
            }
        }

        /** @var array $users Registered Users - Name and Email */
        $users = array();
        if (count($uIds) > 0) {
            $q = $this->modx->newQuery('modUser', array(
                'modUser.id:IN' => $uIds
            ));
            $q->select(array('modUser.id', 'modUser.username', 'p.email', 'p.fullname'));
            $q->leftjoin('modUserProfile', 'p', 'modUser.id = p.internalKey');
            if ($q->prepare() && $q->stmt->execute()) {
                $tmp = $q->stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($tmp as $a) {
                    $users[$a['id']] = array(
                        'name' => $a['fullname'] ? $a['fullname'] : $a['username'],
                        'email' => $a['email'],
                    );
                }
            }
        }

        $this->modx->modxtalks->config['videoSize'] = array(300, 250);
        foreach ($data['results'] as $object) {
            if ($this->checkListPermission && $object instanceof modAccessibleObject && !$object->checkPolicy('list')) {
                continue;
            }

            /** @var array $c Comment Object to Array */
            $c = $this->prepareRow($object);
            if (!empty($c) && is_array($c)) {
                $c['content'] = $this->modx->modxtalks->bbcode($c['content']);

                $userId = $c['userId'];
                if ($userId > 0) {
                    $c['username'] = $users[$userId]['name'];
                    $c['useremail'] = $users[$userId]['email'];
                }

                $c['avatar'] = $this->modx->modxtalks->getAvatar($c['useremail']);
                $c['funny_date'] = $this->modx->modxtalks->date_format($c['time']);
                $c['date'] = date('j-m-Y, G:i O', $c['time']);

                $c['conversationName'] = $c['conversationUrl'] = '';
                if (isset($conversations[$c['conversationId']])) {
                    $c['conversationName'] = $conversations[$c['conversationId']]['name'];
                    $c['conversationUrl'] = $conversations[$c['conversationId']]['url'];
                }

                unset($c['token'], $c['hash']);
                $list[] = $c;
                $this->currentIndex++;
            }
        }

        $list = $this->afterIteration($list);

        return $list;
    }
}

return 'modxTalksTempPostGetListProcessor';
