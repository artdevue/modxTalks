<?php
/**
 * @package modxtalks
 * @subpackage controllers
 */
class modxTalksHomeManagerController extends modxTalksManagerController {

    public function process(array $scriptProperties = array()) {

    }

    public function getPageTitle() {
        return $this->modx->lexicon('modxtalks');
    }

    public function loadCustomCssJs() {
        $this->addJavascript($this->modxtalks->config['jsUrl'].'mgr/widgets/unconfirmed.panel.js');
        $this->addJavascript($this->modxtalks->config['jsUrl'].'mgr/widgets/ipblock.grid.js');
        $this->addJavascript($this->modxtalks->config['jsUrl'].'mgr/widgets/emailblock.grid.js');
        $this->addJavascript($this->modxtalks->config['jsUrl'].'mgr/widgets/modxtalks.grid.js');
        $this->addJavascript($this->modxtalks->config['jsUrl'].'mgr/widgets/home.panel.js');
        $this->addLastJavascript($this->modxtalks->config['jsUrl'].'mgr/sections/index.js');
        //$this->addCss($this->modxtalks->config['cssUrl'].'mgr/comments.css');
        $this->addHtml('<script type="text/javascript">
		Ext.onReady(function() {
			MODx.load({
			    xtype: "modxtalks-page-home"
			});
		});
		</script>');
    }

    public function getTemplateFile() {
        return $this->modxtalks->config['templatesPath'].'home.tpl';
    }
}
