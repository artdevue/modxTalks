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
$plugins = [];

/* create the plugin object */
$plugins[0] = $modx->newObject('modPlugin');
$plugins[0]->set('id', 1);
$plugins[0]->set('name', 'MODXTalksPlugin');
$plugins[0]->set('description', 'Handles FURLs for MODXTalks.');
$plugins[0]->set('plugincode', getSnippetContent($sources['plugins'] . 'plugin.modxtalksplugin.php'));
$plugins[0]->set('category', 0);

$events = include $sources['events'] . 'events.modxtalks.php';
if (is_array($events) && ! empty($events))
{
	$plugins[0]->addMany($events);
	$modx->log(xPDO::LOG_LEVEL_INFO, 'Packaged in ' . count($events) . ' Plugin Events for MODXTalksPlugin.');
	flush();
}
else
{
	$modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find plugin events for MODXTalksPlugin!');
}

unset($events);

return $plugins;
