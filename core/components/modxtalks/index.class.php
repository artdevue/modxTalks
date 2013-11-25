<?php
/**
 * @package modxtalks
 * @subpackage controllers
 */
require_once dirname(__FILE__) . '/model/modxtalks/modxtalks.class.php';

abstract class modxTalksManagerController extends modExtraManagerController
{
    /** @var modxTalks $modxtalks */
    public $modxtalks;

    public function initialize() {
        $this->modxtalks = new modxTalks($this->modx);

        $this->addCss($this->modxtalks->config['cssUrl'] . 'mgr.css');
        $this->addJavascript($this->modxtalks->config['jsUrl'] . 'mgr/modxtalks.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            modxTalks.config = ' . $this->modx->toJSON($this->modxtalks->config) . ';
        });
        </script>');
        return parent::initialize();
    }

    public function getLanguageTopics() {
        return array('modxtalks:default');
    }

    public function checkPermissions() {
        return true;
    }
}
/**
 * @package modxtalks
 * @subpackage controllers
 */
class IndexManagerController extends modxTalksManagerController
{
    public static function getDefaultController() {
        return 'home';
    }
}