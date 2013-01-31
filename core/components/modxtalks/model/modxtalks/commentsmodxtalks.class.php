<?php

require_once MODX_CORE_PATH.'model/modx/modprocessor.class.php';
require_once MODX_CORE_PATH.'model/modx/processors/resource/update.class.php';

class CommentsModxTalks extends modResource {
    public $showInContextMenu = true;
    function __construct(xPDO & $xpdo) {
        parent::__construct($xpdo);
        /*
        require_once MODX_CORE_PATH.'components/modxtalks/model/modxtalks/modxtalks.class.php';
        $this->modxtalks = new modxTalks($xpdo);
        */
        $this->set('class_key','CommentsModxTalks');
    }

    /**
     * {@inheritDoc}
     * @return mixed
     */
    public static function getControllerPath(xPDO &$modx) {
        return $modx->getOption('modxtalks.core_path',null,$modx->getOption('core_path').'components/modxtalks/').'controllers/comments/';
    }

    /**
     * {@inheritDoc}
     * @return mixed
     */
    public function getContextMenuText() {
        $this->xpdo->lexicon->load('modxtalks:default');
        return array(
            'text_create' => $this->xpdo->lexicon('modxtalks.resource_comments'),
            'text_create_here' => $this->xpdo->lexicon('modxtalks.resource_comments_here')
        );
    }

    /**
     * {@inheritDoc}
     * @return mixed
     */
    public function getResourceTypeName() {
        $this->xpdo->lexicon->load('modxtalks:default');
        return $this->xpdo->lexicon('modxtalks.resource_comments');
    }

    public function getContent(array $options = array()) {
        $content = '<div class="postBody">'.parent::getContent($options).'</div>';
        $conversation = $this->class_key.'-'.$this->id;
        $properties = $this->getProperties('modxtalks');
        $properties = array_merge(array('conversation' => $conversation), $properties);

        $out = '';
        foreach ($properties as $key => $property) {
            $out .= "&{$key}=`{$property}`";
        }
        $content .= '[[!modxTalks?'.$out.']]';
        return $content;
    }
}

class CommentsModxTalksUpdateProcessor extends modResourceUpdateProcessor {
    protected $defaulText;

    public function beforeSave() {
        $this->modx->lexicon->load('modxtalks:default');
        $this->defaulText = $this->modx->lexicon('modxtalks.default');
        $properties = $this->getProperties();
        $settings = $this->cleanArray($properties['modxtalks']);
        $this->object->setProperties($settings,'modxtalks',false);

        return parent::beforeSave();
    }

    private function cleanArray(array $array) {
        foreach ($array as $key => $value) {
            $value = trim($value);
            if ($value === '' || $value === $this->defaulText) unset($array[$key]);
        }
        return $array;
    }
}