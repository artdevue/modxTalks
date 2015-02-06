<?php

/**
 * @package modxTalks
 * @subpackage processors
 */
class blockUserIpProcessor extends modObjectCreateProcessor {
    public $classKey = 'modxTalksIpBlock';
    public $languageTopics = array('modxtalks:default');
    public $objectType = 'modxtalks.ip';

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
         * If IP Address already banned
         */
        if ($this->doesAlreadyExist(array('ip' => $value))) {
            $this->failure($this->modx->lexicon('modxtalks.ip_already_banned'));

            return false;
        }

        $this->properties = array(
            'ip' => $value,
            'date' => time(),
        );

        return parent::beforeSet();
    }

    /**
     * Return the success message
     * @return array
     */
    public function cleanup() {
        return $this->success($this->modx->lexicon('modxtalks.ip_ban_success'));
    }
}

return 'blockUserIpProcessor';
