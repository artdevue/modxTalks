<?php
/**
 * Define the MODX path constants necessary for installation
 *
 * @package modxtalks
 * @subpackage build
 */
/* define package names */
define('PKG_NAME', 'MODXTalks');
define('PKG_NAME_LOWER', strtolower(PKG_NAME));
define('PKG_VERSION', '1.2.0');
define('PKG_RELEASE', 'pl');
define('PKG_AUTO_INSTALL', false);

define('MODX_BASE_PATH', dirname(dirname(__FILE__)) . '/');
define('MODX_CORE_PATH', MODX_BASE_PATH . 'core/');
define('MODX_MANAGER_PATH', MODX_BASE_PATH . 'manager/');
define('MODX_CONNECTORS_PATH', MODX_BASE_PATH . 'connectors/');
define('MODX_ASSETS_PATH', MODX_BASE_PATH . 'assets/');

define('MODX_BASE_URL', '/');
define('MODX_CORE_URL', MODX_BASE_URL . 'core/');
define('MODX_MANAGER_URL', MODX_BASE_URL . 'manager/');
define('MODX_CONNECTORS_URL', MODX_BASE_URL . 'connectors/');
define('MODX_ASSETS_URL', MODX_BASE_URL . 'assets/');
