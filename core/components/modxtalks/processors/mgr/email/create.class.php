<?php
/**
 * @package modxTalks
 * @subpackage processors
 */
class modxTalksEmailBlockCreateProcessor extends modObjectCreateProcessor {
    public $classKey = 'modxTalksEmailBlock';
    public $languageTopics = array('modxtalks:default');
    public $objectType = 'modxtalks.email';

    public function beforeSet() {
        $email = $this->getProperty('email');
        $intro = $this->getProperty('intro');

        if ($this->doesAlreadyExist(array('email' => $email))) {
            $this->addFieldError('email',$this->modx->lexicon('modxtalks.email_already_banned'));
        }

        $comment = $this->modx->newObject('modxTalksPost');

        if (!$comment->validateEmail($email)) {
            $this->addFieldError('email',$this->modx->lexicon('modxtalks.bad_email'));
        }

        $this->properties = array(
            'email' => $email,
            'date' => time(),
        );

        if (!empty($intro)) {
            $this->properties['intro'] = $intro;
        }

        return parent::beforeSet();
    }

}
return 'modxTalksEmailBlockCreateProcessor';