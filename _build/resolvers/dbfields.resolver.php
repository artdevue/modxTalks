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
 * Handles adding custom fields to modResource table
 *
 * @var xPDOObject $object
 * @var array $options
 * @package modxtalks
 * @subpackage build
 */
if ($object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            /** @var modX $modx */
            $modx =& $object->xpdo;
            $modelPath = $modx->getOption('modxtalks.core_path', null, $modx->getOption('core_path') . 'components/modxtalks/') . 'model/';
            $modx->addPackage('modxtalks', $modelPath);

            /** @var xPDOManager $manager */
            $manager = $modx->getManager();

            $manager->createObjectContainer('modxTalksConversation');
            $manager->createObjectContainer('modxTalksIpBlock');
            $manager->createObjectContainer('modxTalksLike');
            $manager->createObjectContainer('modxTalksPost');
            $manager->createObjectContainer('modxTalksSubscribers');
            $manager->createObjectContainer('modxTalksTempPost');
            $manager->createObjectContainer('modxTalksMails');
            $manager->createObjectContainer('modxTalksEmailBlock');
            $manager->createObjectContainer('modxTalksLatestPost');
            //CommentsModxTalks
            break;
        case xPDOTransport::ACTION_UPGRADE:
            $modx =& $object->xpdo;
            $modelPath = $modx->getOption('modxtalks.core_path', null, $modx->getOption('core_path') . 'components/modxtalks/') . 'model/';
            $modx->addPackage('modxtalks', $modelPath);
            $manager = $modx->getManager();
            $oldLogLevel = $modx->getLogLevel();
            $modx->setLogLevel(0);

            $manager->createObjectContainer('modxTalksEmailBlock');
            $manager->createObjectContainer('modxTalksLatestPost');
            $manager->createObjectContainer('modxTalksMails');
            $modx->setLogLevel($oldLogLevel);
            break;
    }
}
return true;