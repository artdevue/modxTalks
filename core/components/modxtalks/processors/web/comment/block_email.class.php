<?php

/**
 * @package modxTalks
 * @subpackage processors
 */
class blockUserEmailProcessor extends modObjectCreateProcessor {
    public $classKey = 'modxTalksEmailBlock';
    public $languageTopics = array('modxtalks:default');
    public $objectType = 'modxtalks.email';

    public function beforeSet() {
        $value = (string) $this->getProperty('value');
        if (!$value) {
            $this->failure($this->modx->lexicon('modxtalks.error_try_again'));

            return false;
        }

        /**
         * @var string Context key
         */
        $this->context = trim($this->getProperty('ctx'));
        /**
         * Check context
         */
        if (empty($this->context)) {
            $this->failure($this->modx->lexicon('modxtalks.empty_context'));

            return false;
        } elseif (!$this->modx->getCount('modContext', $this->context)) {
            $this->failure($this->modx->lexicon('modxtalks.bad_context'));

            return false;
        }

        if (!$this->modx->modxtalks->isModerator()) {
            $this->failure($this->modx->lexicon('modxtalks.edit_permission'));

            return false;
        }
        /**
         * If Email Address already banned
         */
        if ($this->doesAlreadyExist(array('email' => $value))) {
            $this->failure($this->modx->lexicon('modxtalks.email_already_banned'));

            return false;
        }

        $this->properties = array(
            'email' => $value,
            'date' => time(),
        );

        return parent::beforeSet();
    }

    /**
     * Return the success message
     * @return array
     */
    public function cleanup() {
        return $this->success($this->modx->lexicon('modxtalks.email_ban_success'));
    }
}

return 'blockUserEmailProcessor';
