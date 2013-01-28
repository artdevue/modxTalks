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

class modxTalksRouter {
    /** @var modX $modx */
    public $modx;
    /** @var array $config */
    public $config = array();

    function __construct(modX &$modx,array $config = array()) {
        $this->modx =& $modx;
        $this->config = array_merge(array(

        ),$config);
    }

    /**
     * Create conversations map
     *
     * @return array|false False if cache is off
     */
    public function conversationsMap() {
        /**
         * If disabled caching return False
         */
        if (!$this->modx->getCacheManager()) return false;
        /**
         * If a map is present in the cache, then just return it
         */
        if ($map = $this->modx->cacheManager->get('conversations_map', array(
            xPDO::OPT_CACHE_KEY => 'modxtalks'))) {
            return $map;
        }
        /**
         * If the map is not in the cache, we all topics from the database and build the map
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
     * @access private
     * @return boolean True if resource have a conversation
     */
    private function _hasConversations($id) {
        if (!intval($id)) return false;
        /**
         * Checking through the map so the cache
         */
        if ($map = $this->conversationsMap()) {
            /**
             * If the resource contains at least one topic return true
             */
            if (array_key_exists($id, $map)) return true;
            return false;
        }
        /**
         * If the conversationsMap () returned false (with the cache enabled), on-base
         */
        elseif ($this->modx->getCount('modxTalksConversation',array('rid' => $id))) {
            return true;
        }

        return false;
    }

    /**
     * Route the URL request based on the container IDs
     * @return boolean
     */
    public function route() {
        $serveruri = $this->modx->request->parameters['REQUEST'];
        $qmt = $serveruri[$this->modx->getOption('request_param_alias')];
        $aliasMap = $this->modx->aliasMap;

        // check to see there is a link rss comments
        if (preg_match("@comments-(.*?).rss@si",$qmt,$data)) {
            if(in_array($data[1],$aliasMap) && $data[0] == $qmt && $this->_hasConversations($data[1])) {
                $_REQUEST['comment'] = $data[1];
                $_REQUEST['rss'] = 'true';
                $this->modx->sendForward($data[1]);
            }
        }

        // check to see there is a link to a comment
        if (!preg_match("@/comment-(.*?)-mt@si",$qmt,$data))
            return true;

        if ($id = $aliasMap[str_replace($data[0],'',$qmt)]) {
            if (isset($data[1]) && $this->_hasConversations($id)) {
                if ($data[1] == 'last' || intval($data[1]) || date('Y-m', strtotime($data[1]))) {
                    $_REQUEST['comment'] = $data[1];
                    $this->modx->sendForward($id);
                }
            }
        }

        return true;
    }
}
