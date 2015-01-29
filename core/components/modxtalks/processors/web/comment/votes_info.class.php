<?php

/**
 * @package modxtalks
 * @subpackage processors
 */
class getVotesInfo extends modObjectGetProcessor {
    public $classKey = 'modxTalksPost';
    public $objectType = 'modxtalks.post';
    public $languageTopics = array('modxtalks:default');
    public $context = '';

    public function initialize() {
        /**
         * Check for voting
         */
        if (!$this->modx->modxtalks->config['voting']) {
            $this->failure($this->modx->lexicon('modxtalks.voting_disabled'));

            return false;
        }
        /**
         * Check context
         */
        $this->context = trim($this->getProperty('ctx'));
        if (empty($this->context)) {
            $this->failure($this->modx->lexicon('modxtalks.empty_context'));

            return false;
        } elseif (!$this->modx->getCount('modContext', $this->context)) {
            $this->failure($this->modx->lexicon('modxtalks.bad_context'));

            return false;
        }

        return parent::initialize();
    }

    public function process() {
        $userId = $this->modx->user->id;
        $votes = $this->object->getVotes();
        if (in_array($userId, $votes['users'])) {
            $key = array_search($userId, $votes['users']);
            unset($votes['users'][$key]);
        }

        $users = $this->modx->modxtalks->getUsers($votes['users']);
        foreach ($users as $k => & $user) {
            $users[$k] = array(
                'name' => $user['fullname'] ? $user['fullname'] : $user['username'],
                'avatar' => $this->modx->modxtalks->getAvatar($user['email'], 50),
            );
        }

        return $this->success('', array('users' => $users));
    }
}

return 'getVotesInfo';
