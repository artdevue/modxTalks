<?php
/**
 * Adds modActions and modMenus into package
 *
 * @package modxtalks
 * @subpackage build
 */
$action= $modx->newObject('modAction');
$action->fromArray(array(
    'id' => 1,
    'namespace' => 'modxtalks',
    'parent' => 0,
    'controller' => 'index',
    'haslayout' => true,
    'lang_topics' => 'modxtalks:default',
    'assets' => '',
),'',true,true);

// The main menu item
$menu= $modx->newObject('modMenu');
$menu->fromArray(array(
    'text' => 'modxtalks',
    'parent' => 'components',
    'description' => 'modxtalks.menu_desc',
    'icon' => 'images/icons/plugin.gif',
    'menuindex' => 0,
    'params' => '',
    'handler' => '',
    'permissions' => 'view_modxtalks',
),'',true,true);
$menu->addOne($action);
$vehicle = $builder->createVehicle($menu, array (
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::UNIQUE_KEY => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Action' => array (
            xPDOTransport::PRESERVE_KEYS => false, 
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => array ('namespace','controller'),
        )
    )
));
$builder->putVehicle($vehicle);
unset($menu, $vehicle);

// Create unconfirmed submenu
$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
    'text' => 'modxtalks.comments_unconfirmed',
    'parent' => 'modxtalks', 
    'description' => 'modxtalks.comments_unconfirmed_desc',
    'icon' => 'images/icons/plugin.gif',
    'menuindex' => 0,
    'params' => '#homeTab:not-confirmed',
    'handler' => ''
), '', true, true);
$menu->addOne($action);

$vehicle = $builder->createVehicle($menu, array (
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::UNIQUE_KEY => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Action' => array (
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => array ('namespace','controller'),
        )
    )
));
$builder->putVehicle($vehicle);
unset($menu, $vehicle);

// Create help submenu
$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
    'text' => 'modxtalks.help',
    'parent' => 'modxtalks', 
    'description' => 'modxtalks.help_desc',
    'icon' => 'images/icons/plugin.gif',
    'menuindex' => 1,
    'params' => '',
    'handler' => 'window.open("http://modxtalks.artdevue.com/en/help.html");'
), '', true, true);
$menu->addOne($action);

$vehicle = $builder->createVehicle($menu, array (
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::UNIQUE_KEY => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Action' => array (
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => array ('namespace','controller'),
        )
    )
));
$builder->putVehicle($vehicle);
unset($menu, $vehicle);

// Create demo submenu
$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
    'text' => 'modxtalks.demo',
    'parent' => 'modxtalks', 
    'description' => 'modxtalks.demo_desc',
    'icon' => 'images/icons/plugin.gif',
    'menuindex' => 2,
    'params' => '',
    'handler' => 'window.open("http://modxtalks.artdevue.com/en/");'
), '', true, true);
$menu->addOne($action);

$vehicle = $builder->createVehicle($menu, array (
    xPDOTransport::PRESERVE_KEYS => true,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::UNIQUE_KEY => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
        'Action' => array (
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => array ('namespace','controller'),
        )
    )
));
$builder->putVehicle($vehicle);
unset($menu, $vehicle);

unset($vehicle,$action); /* to keep memory low */