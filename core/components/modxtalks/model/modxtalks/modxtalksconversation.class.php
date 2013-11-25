<?php
/**
 * This file is part of modxTalks, a simple commenting component for MODx Revolution.
 *
 * @copyright Copyright (C) 2013, Artdevue Ltd, <info@artdevue.com>
 * @author Valentin Rasulov <info@artdevue.com> && Ivan Brezhnev <brezhnev.ivan@yahoo.com>
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @package modxtalks
 *
 */

class modxTalksConversation extends xPDOSimpleObject {
    /**
     * Get a namespaced property for the Conversation
     * @param string $key
     * @param string $namespace
     * @param null $default
     * @return null
     */
    public function getProperty($key, $namespace = 'comments', $default = null) {
        $properties = $this->get('properties');
        $properties = !empty($properties) ? $properties : array();
        return array_key_exists($namespace,$properties) && array_key_exists($key,$properties[$namespace]) ? $properties[$namespace][$key] : $default;
    }
    /**
     * Get the properties for the specific namespace for the Conversation
     * @param string $namespace
     * @return array
     */
    public function getProperties($namespace = 'comments') {
        $properties = $this->get('properties');
        $properties = !empty($properties) ? $properties : array();
        return array_key_exists($namespace,$properties) ? $properties[$namespace] : array();
    }

    /**
     * Set a namespaced single property for the Conversation
     * @param mixed $value
     * @param string $key
     * @return bool
     */
    public function setSingleProperty($value, $key = 'id') {
        $properties = $this->get('properties');
        $properties = !empty($properties) ? $properties : array();
        if (!array_key_exists($key,$properties)) $properties[$key] = '';
        $properties[$key] = $value;
        return $this->set('properties',$properties);
    }

    /**
     * Get a namespaced single property of Conversation
     * @param string $key
     * @return bool
     */
    public function getSingleProperty($key = 'id', $default = null) {
        $properties = $this->get('properties');
        $properties = !empty($properties) ? $properties : array();
        return array_key_exists($key,$properties) ? $properties[$key] : $default;
    }

    /**
     * Set a namespaced property for the Conversation
     * @param string $key
     * @param mixed $value
     * @param string $namespace
     * @return bool
     */
    public function setProperty($key, $value, $namespace = 'comments') {
        $properties = $this->get('properties');
        $properties = !empty($properties) ? $properties : array();
        if (!array_key_exists($namespace,$properties)) $properties[$namespace] = array();
        $properties[$namespace][$key] = $value;
        return $this->set('properties',$properties);
    }

    /**
     * Set properties for a namespace on the Conversation, optionally merging them with existing ones.
     * @param array $newProperties
     * @param string $namespace
     * @param bool $merge
     * @return boolean
     */
    public function setProperties(array $newProperties, $namespace = 'comments', $merge = true) {
        $properties = $this->get('properties');
        $properties = !empty($properties) ? $properties : array();
        if (!array_key_exists($namespace,$properties)) $properties[$namespace] = array();
        $properties[$namespace] = $merge ? array_merge($properties[$namespace],$newProperties) : $newProperties;
        return $this->set('properties',$properties);
    }

    /**
     * Get All subscribers email addresses
     *
     * @return array $emails
     **/
    public function getSubscribersEmails() {
        $emails = array();
        if (!$subscribers = $this->getMany('Subscribers')) {
            return $emails;
        }
        foreach ($subscribers as $subscriber) {
            $emails[] = $subscriber->get('email');
        }
        return $emails;
    }

    /**
     * Get All comments of this conversation
     *
     * @return array $commentsArr
     **/
    public function getComments() {
        $commentsArr = array();
        if (!$comments = $this->getMany('Comments')) {
            return $commentsArr;
        }
        foreach ($comments as $comment) {
            $commentsArr[] = $comment->toArray();
        }
        return $commentsArr;
    }

    /**
     * Get All unconfirmed comments of this conversation
     *
     * @return array $commentsArr
     **/
    public function getUnconfirmedComments() {
        $commentsArr = array();
        if (!$comments = $this->getMany('UnconfirmedComments')) {
            return $commentsArr;
        }
        foreach ($comments as $comment) {
            $commentsArr[] = $comment->toArray();
        }
        return $commentsArr;
    }

    /**
     * Sync comments count: total, deleted, unconfirmed
     *
     * @return array $commentsArr
     **/
    public function syncCommentsCount() {
        $changed = false;

        $total = $this->xpdo->getCount('modxTalksPost', array('conversationId' => $this->id));
        $deleted = $this->xpdo->getCount('modxTalksPost', array('conversationId' => $this->id, 'deleteUserId:>' => 0));
        $unconfirmed = $this->xpdo->getCount('modxTalksTempPost', array('conversationId' => $this->id));

        $properties = $this->setProperties(array(
            'total' => $total,
            'deleted' => $deleted,
            'unconfirmed' => $unconfirmed,
        ),'comments',false);

        if ($this->save()) {
            $changed = true;
        }
        return $changed;
    }

    /**
     * Recalculate comments indexes
     *
     * @return boolean If success return True
     **/
    public function recalculateIndexes($idx = 0) {
        $success = false;
        $q = $this->xpdo->newQuery('modxTalksPost');
        $q->select('id');
        if ($idx = intval($idx)) {
            $q->andCondition(array(
                'idx:>' => $idx,
                'conversationId' => $this->id
            ));
        }

        $q->sortby('idx', 'ASC');

        $q->prepare(); $q->stmt->execute();
        $result = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) return $success;

        $indexes = array();
        if (!$idx) {
            $idx = 1;
        }
        foreach ($result as $r) {
            $indexes[$r['id']] = $idx;
            $idx += 1;
        }

        $ids = implode(',', array_keys($indexes));
        $table = $this->xpdo->getTableName('modxTalksPost');
        $sql = "UPDATE {$table} SET idx = CASE id ";
        foreach ($indexes as $id => $new_idx) {
            $sql .= sprintf("WHEN %d THEN %d ", $id, $new_idx);
        }
        $sql .= "END WHERE id IN ($ids)";

        $sql = $this->xpdo->query($sql);
        if ($sql->execute()) {
            $success = true;
        }

        return $success;
    }
}
