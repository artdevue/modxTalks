<?php
/**
 * MODXTalks
 *
 * Copyright 2012-2014 by
 * Valentin Rasulov <artdevue.com@yahoo.com> & Ivan Brezhnev <brezhnev.ivan@yahoo.com>
 *
 * This file is part of MODXTalks, a simple commenting component for MODx Revolution.
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
 * MODXTalks; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package modxtalks
 */
/**
 * Build Schema script
 *
 * @package modxtalks
 * @subpackage build
 */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

/* define package name and sources */
define('PKG_NAME', 'MODXTalks');
define('PKG_NAME_LOWER', 'modxtalks');

$root = dirname(dirname(__FILE__)) . '/';
$sources = [
	'root' => $root,
	'core' => $root . 'core/components/' . PKG_NAME_LOWER . '/',
	'model' => $root . 'core/components/' . PKG_NAME_LOWER . '/model/',
	'assets' => $root . 'assets/components/' . PKG_NAME_LOWER . '/',
];

/* load modx and configs */
require_once 'build.config.php';
include_once MODX_CORE_PATH . 'model/modx/modx.class.php';
require_once 'build.properties.php';
$modx = new modX();
$modx->initialize('mgr');
$modx->loadClass('transport.modPackageBuilder', '', false, true);

echo '<pre>';
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

foreach (['mysql', 'sqlsrv'] as $driver)
{
	$xpdo = new xPDO(
		$properties["{$driver}_string_dsn_nodb"],
		$properties["{$driver}_string_username"],
		$properties["{$driver}_string_password"],
		$properties["{$driver}_array_options"],
		$properties["{$driver}_array_driverOptions"]
	);
	$xpdo->setPackage('modx', dirname(XPDO_CORE_PATH) . '/model/');
	$xpdo->setDebug(true);

	$manager = $xpdo->getManager();
	$generator = $manager->getGenerator();

	$manager = $xpdo->getManager();
	$generator = $manager->getGenerator();

	$generator->classTemplate = <<<EOD
<?php
/**
 * MODXTalks
 *
 * Copyright 2012-2014 by
 * Valentin Rasulov <artdevue.com@yahoo.com> & Ivan Brezhnev <brezhnev.ivan@yahoo.com>
 *
 * This file is part of MODXTalks, a simple commenting component for MODx Revolution.
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
 * MODXTalks; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package modxtalks
 */
/**
 * [+phpdoc-package+]
 * [+phpdoc-subpackage+]
 */
class [+class+] extends [+extends+] {}
?>
EOD;
	$generator->platformTemplate = <<<EOD
<?php
/**
 * MODXTalks
 *
 * Copyright 2012-2014 by
 * Valentin Rasulov <artdevue.com@yahoo.com> & Ivan Brezhnev <brezhnev.ivan@yahoo.com>
 *
 * This file is part of MODXTalks, a simple commenting component for MODx Revolution.
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
 * MODXTalks; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package modxtalks
 */
/**
 * [+phpdoc-package+]
 * [+phpdoc-subpackage+]
 */
require_once (strtr(realpath(dirname(dirname(__FILE__))), '\\\\', '/') . '/[+class-lowercase+].class.php');
class [+class+]_[+platform+] extends [+class+] {}
?>
EOD;
	$generator->mapHeader = <<<EOD
<?php
/**
 * MODXTalks
 *
 * Copyright 2012-2014 by
 * Valentin Rasulov <artdevue.com@yahoo.com> & Ivan Brezhnev <brezhnev.ivan@yahoo.com>
 *
 * This file is part of MODXTalks, a simple commenting component for MODx Revolution.
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
 * MODXTalks; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package modxtalks
 */
/**
 * [+phpdoc-package+]
 * [+phpdoc-subpackage+]
 */
EOD;
	$generator->parseSchema($sources['model'] . 'schema/' . PKG_NAME_LOWER . '.' . $driver . '.schema.xml', $sources['model']);
}

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

echo "\nExecution time: {$totalTime}\n";

die;
