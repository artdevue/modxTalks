<?php
/**
 * MODXTalks
 *
 * Copyright 2012-2013 by
 * Valentin Rasulov <artdevue.com@yahoo.com> & Ivan Brezhnev <brezhnev.ivan@yahoo.com>
 *
 * MODXTalks is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * MODXTalks is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * MODXTalks; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package modxtalks
 */
/**
 * @var modX $modx
 * @package modxtalks
 * @subpackage build
 */
$action = $modx->newObject('modAction');
$action->fromArray(array(
    'id'          => 1,
    'namespace'   => 'modxtalks',
    'parent'      => 0,
    'controller'  => 'index',
    'haslayout'   => true,
    'lang_topics' => 'modxtalks:default',
    'assets'      => '',
), '', true, true);

// The main menu item
$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
    'text'        => 'modxtalks',
    'parent'      => 'components',
    'description' => 'modxtalks.menu_desc',
    'icon'        => 'images/icons/plugin.gif',
    'menuindex'   => 0,
    'params'      => '',
    'handler'     => '',
    'permissions' => 'view_modxtalks',
), '', true, true);
$menu->addOne($action);
$vehicle = $builder->createVehicle($menu, array(
    xPDOTransport::PRESERVE_KEYS   => true,
    xPDOTransport::UPDATE_OBJECT   => true,
    xPDOTransport::UNIQUE_KEY      => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
        'Action' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY    => array('namespace', 'controller'),
        )
    )
));
$builder->putVehicle($vehicle);
unset($menu, $vehicle);

// Create unconfirmed submenu
$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
    'text'        => 'modxtalks.comments_unconfirmed',
    'parent'      => 'modxtalks',
    'description' => 'modxtalks.comments_unconfirmed_desc',
    'icon'        => 'images/icons/plugin.gif',
    'menuindex'   => 0,
    'params'      => '#homeTab:not-confirmed',
    'handler'     => ''
), '', true, true);
$menu->addOne($action);

$vehicle = $builder->createVehicle($menu, array(
    xPDOTransport::PRESERVE_KEYS   => true,
    xPDOTransport::UPDATE_OBJECT   => true,
    xPDOTransport::UNIQUE_KEY      => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
        'Action' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY    => array('namespace', 'controller'),
        )
    )
));
$builder->putVehicle($vehicle);
unset($menu, $vehicle);

// Create help submenu
$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
    'text'        => 'modxtalks.help',
    'parent'      => 'modxtalks',
    'description' => 'modxtalks.help_desc',
    'icon'        => 'images/icons/plugin.gif',
    'menuindex'   => 1,
    'params'      => '',
    'handler'     => 'window.open("http://modxtalks.artdevue.com/en/help.html");'
), '', true, true);
$menu->addOne($action);

$vehicle = $builder->createVehicle($menu, array(
    xPDOTransport::PRESERVE_KEYS   => true,
    xPDOTransport::UPDATE_OBJECT   => true,
    xPDOTransport::UNIQUE_KEY      => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
        'Action' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY    => array('namespace', 'controller'),
        )
    )
));
$builder->putVehicle($vehicle);
unset($menu, $vehicle);

// Create submenu
$menu = $modx->newObject('modMenu');
$menu->fromArray(array(
    'text'        => 'modxtalks.demo',
    'parent'      => 'modxtalks',
    'description' => 'modxtalks.demo_desc',
    'icon'        => 'images/icons/plugin.gif',
    'menuindex'   => 2,
    'params'      => '',
    'handler'     => 'window.open("http://modxtalks.artdevue.com/en/");'
), '', true, true);
$menu->addOne($action);

$vehicle = $builder->createVehicle($menu, array(
    xPDOTransport::PRESERVE_KEYS   => true,
    xPDOTransport::UPDATE_OBJECT   => true,
    xPDOTransport::UNIQUE_KEY      => 'text',
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
        'Action' => array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY    => array('namespace','controller'),
        )
    )
));
$builder->putVehicle($vehicle);
unset($menu, $vehicle);

/* to keep memory low */
unset($vehicle, $action);