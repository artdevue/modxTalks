<?php

/**
 * Get a list of Conversations
 *
 * @package modxtalks
 * @subpackage processors
 */
class modxTalkCommentsGetListProcessor extends modObjectGetListProcessor {
    public $classKey = 'modxTalksConversation';
    public $languageTopics = array('modxtalks:default');

    /**
     * {@inheritDoc}
     * @return mixed
     */
    public function process() {
        $cid = $this->getProperty('conversationName');
        $data = array(
            'total' => 0,
            'deleted' => 0,
            'unconfirmed' => 0,
        );

        if ($conversation = $this->modx->getObject($this->classKey, array(
            'conversation' => $cid
        ))
        ) {
            $data = $conversation->getProperties('comments');
        }

        return $this->outputArray($data);
    }

    /**
     * Return arrays of comments counts converted to JSON.
     *
     * @access public
     *
     * @param array $array An array of data objects.
     * @param mixed $count For backwards compatibility, do not use this key
     *
     * @return string The JSON output.
     */
    public function outputArray(array $array, $count = false) {
        return '{"total":' . $array['total'] . ',"deleted":' . $array['deleted'] . ',"notpublished":' . $array['unconfirmed'] . '}';
    }

}

return 'modxTalkCommentsGetListProcessor';
