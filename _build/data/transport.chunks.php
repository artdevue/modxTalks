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
$chunks = [];

$chunks[1] = $modx->newObject('modChunk');
$chunks[1]->fromArray([
	'id' => 1,
	'name' => 'comment_auth_tpl',
	'description' => 'This tpl for displaying information not authorized users.',
	'snippet' => file_get_contents($sources['chunks'] . 'comment_auth_tpl.chunk.tpl'),
]);

return $chunks;
