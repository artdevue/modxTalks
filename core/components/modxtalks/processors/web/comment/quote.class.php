<?php

class makeQuoteProcessor extends modObjectGetProcessor {
    public $classKey = 'modxTalksPost';
    public $objectType = 'modxtalks.post';
    public $languageTopics = array('modxtalks:default');

    public function initialize() {
        if ($this->modx->modxtalks->isModerator()) {
            return parent::initialize();
        }

        $this->context = trim($this->getProperty('ctx'));
        // Check Context
        if (empty($this->context)) {
            $this->failure($this->modx->lexicon('modxtalks.empty_context'));

            return false;
        } elseif (!$this->modx->getCount('modContext', $this->context)) {
            $this->failure($this->modx->lexicon('modxtalks.bad_context'));

            return false;
        }

        if ($this->modx->modxtalks->config['onlyAuthUsers'] && !$this->modx->user->isAuthenticated($this->context)) {
            $this->failure($this->modx->lexicon('modxtalks.no_login'));

            return false;
        }

        return parent::initialize();
    }

    /**
     * {@inheritDoc}
     * @return mixed
     */
    public function process() {
        if ($this->object->deleteTime || $this->object->deleteUser) {
            $this->failure($this->modx->lexicon('modxtalks.unknown_error'));

            return false;
        }

        return $this->cleanup();
    }

    public function cleanup() {
        $content = $this->removeQuotes($this->object->content);
        $name = $this->modx->lexicon('modxtalks.guest');
        if ($userId = $this->object->userId) {
            $profile = $this->modx->getObject('modUserProfile', $userId);
            if (!$name = $profile->get('fullname')) {
                $user = $this->modx->getObject('modUser', $userId);
                $name = $user->get('username');
            }
        } else {
            $name = $this->object->username;
        }

        $output = array(
            'content' => $content,
            'id' => $this->object->id,
            'user' => '"' . $name . '"'
        );

        return $this->success('', $output);
    }

    /**
     * Remove all quotes and videos from the content string.
     * This can be used to prevent nested quotes and videos when quoting a post.
     *
     * @param string $content Raw content
     *
     * @return string $content
     */
    public function removeQuotes($content) {
        // Remove [quote]
        while (preg_match("`(.*)\[quote(.*)?\].*?\[/quote\]`si", $content)) {
            $content = preg_replace("`(.*)\[quote(.*)?\].*?\[/quote\]`si", "$1", $content);
        }
        // Remove [video]
        while (preg_match("`(.*)\[video(.*)?\].*?\[/video\]`si", $content)) {
            $content = preg_replace("`(.*)\[video(.*)?\].*?\[/video\]`si", "$1", $content);
        }

        return trim($content);
    }
}

return 'makeQuoteProcessor';
