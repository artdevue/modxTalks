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
$snippets = array();

$snippets[0] = $modx->newObject('modSnippet');
$snippets[0]->fromArray(array(
    'id' => 0,
    'name' => 'modxTalks',
    'description' => '[modxTalks] Snippet.',
    'snippet' => file_get_contents($sources['snippets'].'snippet.modxtalks.php'),
));
$properties = include $sources['properties'].'properties.modxtalks.php';
$snippets[0]->setProperties($properties);
unset($properties);

$snippets[1]= $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
    'id' => 1,
    'name' => 'modxTalksAjax',
    'description' => '[modxTalksAjax] Snippet.',
    'snippet' => file_get_contents($sources['snippets'].'snippet.modxtalksajax.php'),
));

$snippets[2]= $modx->newObject('modSnippet');
$snippets[2]->fromArray(array(
    'id' => 2,
    'name' => 'modxTalksLatestComments',
    'description' => 'The conclusion of the latest comments on your site.',
    'snippet' => file_get_contents($sources['snippets'].'snippet.modxtalkslatestcomments.php'),
));
$properties = include $sources['properties'].'properties.modxtalkslatestcomments.php';
$snippets[2]->setProperties($properties);
unset($properties);

$snippets[3]= $modx->newObject('modSnippet');
$snippets[3]->fromArray(array(
    'id' => 3,
    'name' => 'MtCount',
    'description' => 'Snippet to run the page parser and display the number of comments on the resource.',
    'snippet' => file_get_contents($sources['snippets'].'snippet.mtcount.php'),
));

return $snippets;