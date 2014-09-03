<?php
/**
 * MODXTalks
 *
 * Copyright 2012-2014 by
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
$events = [];

$events['OnSiteRefresh'] = $modx->newObject('modPluginEvent');
$events['OnSiteRefresh']->fromArray([
	'event' => 'OnSiteRefresh',
	'priority' => 0,
	'propertyset' => 0,
], '', true, true);

$events['OnUserFormSave'] = $modx->newObject('modPluginEvent');
$events['OnUserFormSave']->fromArray([
	'event' => 'OnUserFormSave',
	'priority' => 0,
	'propertyset' => 0,
], '', true, true);

$events['OnPageNotFound'] = $modx->newObject('modPluginEvent');
$events['OnPageNotFound']->fromArray([
	'event' => 'OnPageNotFound',
	'priority' => 0,
	'propertyset' => 0,
], '', true, true);

$events['OnManagerPageInit'] = $modx->newObject('modPluginEvent');
$events['OnManagerPageInit']->fromArray([
	'event' => 'OnManagerPageInit',
	'priority' => 0,
	'propertyset' => 0,
], '', true, true);

$events['OnDocFormPrerender'] = $modx->newObject('modPluginEvent');
$events['OnDocFormPrerender']->fromArray([
	'event' => 'OnDocFormPrerender',
	'priority' => 0,
	'propertyset' => 0,
], '', true, true);

$events['OnWebPagePrerender'] = $modx->newObject('modPluginEvent');
$events['OnWebPagePrerender']->fromArray([
	'event' => 'OnWebPagePrerender',
	'priority' => 0,
	'propertyset' => 0,
], '', true, true);

$events['OnModxTalksCommentAfterAdd'] = $modx->newObject('modPluginEvent');
$events['OnModxTalksCommentAfterAdd']->fromArray([
	'event' => 'OnModxTalksCommentAfterAdd',
	'priority' => 0,
	'propertyset' => 0,
], '', true, true);

$events['OnModxTalksCommentAfterRemove'] = $modx->newObject('modPluginEvent');
$events['OnModxTalksCommentAfterRemove']->fromArray([
	'event' => 'OnModxTalksCommentAfterRemove',
	'priority' => 0,
	'propertyset' => 0,
], '', true, true);

$events['OnModxTalksCommentBeforeRemove'] = $modx->newObject('modPluginEvent');
$events['OnModxTalksCommentBeforeRemove']->fromArray([
	'event' => 'OnModxTalksCommentBeforeRemove',
	'priority' => 0,
	'propertyset' => 0,
], '', true, true);

return $events;
