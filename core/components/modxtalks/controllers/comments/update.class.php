<?php

class CommentsModxTalksUpdateManagerController extends ResourceUpdateManagerController {
    public function getLanguageTopics() {
        return array('resource','modxtalks:default');
    }
    /**
     * Register custom CSS/JS for the page
     * @return void
     */
    public function loadCustomCssJs() {
        $modxtalksAssetsUrl = $this->modx->getOption('modxtalks.assets_url',null,$this->modx->getOption('assets_url',null,MODX_ASSETS_URL).'components/modxtalks/');
        $connectorUrl = $modxtalksAssetsUrl.'connector.php';
        $modxtalksJsUrl = $modxtalksAssetsUrl.'js/mgr/';
        $modxtalksCssUrl = $modxtalksAssetsUrl.'css/mgr/';

        $this->addLastJavascript($modxtalksJsUrl.'modxtalks.js');
        $this->addLastJavascript($modxtalksJsUrl.'comments/comments.js');
        $this->addCss($modxtalksCssUrl.'comments.css');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            modxTalks.config = '.$this->modx->toJSON(array()).';
            modxTalks.config.connector_url = "'.$connectorUrl.'";
            modxTalks.request = '.$this->modx->toJSON($_GET).';
        });
        </script>');

        parent::loadCustomCssJs();
    }

}