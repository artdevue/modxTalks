<?php

/**
 * This file is part of modxTalks, a simple commenting component for MODx Revolution.
 *
 * @copyright Copyright (C) 2013-2015, Artdevue Ltd, <info@artdevue.com>
 * @author    Valentin Rasulov <info@artdevue.com> && Ivan Brezhnev <brezhnev.ivan@yahoo.com>
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @package   modxtalks
 */

require_once MODX_CORE_PATH . 'model/modx/modrequest.class.php';

/**
 * Encapsulates the interaction of MODx manager with an HTTP request.
 *
 * {@inheritdoc}
 *
 * @package modxtalks
 * @extends modRequest
 */
class modxtalksControllerRequest extends modRequest {
    public $discuss = null;
    public $actionVar = 'action';
    public $defaultAction = 'index';

    function __construct(modxTalks &$modxtalks) {
        parent:: __construct($modxtalks->modx);
        $this->modxtalks =& $modxtalks;
    }

    /**
     * Extends modRequest::handleRequest and loads the proper error handler and
     * actionVar value.
     *
     * {@inheritdoc}
     */
    public function handleRequest() {
        $this->loadErrorHandler();

        /* save page to manager object. allow custom actionVar choice for extending classes. */
        $this->action = isset($_REQUEST[$this->actionVar]) ? $_REQUEST[$this->actionVar] : $this->defaultAction;

        $modx =& $this->modx;
        $modxtalks =& $this->modxtalks;
        $viewHeader = include $this->modxtalks->config['corePath'] . 'controllers/mgr/header.php';

        $f = $this->modxtalks->config['corePath'] . 'controllers/mgr/' . $this->action . '.php';
        if (file_exists($f)) {
            $viewOutput = include $f;
        } else {
            $viewOutput = 'Controller not found: ' . $f;
        }

        return $viewHeader . $viewOutput;
    }
}
