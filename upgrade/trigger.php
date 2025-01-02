<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace XMB;

ignore_user_abort(true);
header('Expires: 0');
header('X-Frame-Options: sameorigin');

//Script constants
define('XMB_UPGRADE', true);
define('ROOT', '../');

$logfile = './upgrade.log';

//Check configuration
if (ini_get('display_errors')) {
	ini_set('display_errors', '0');
	$forced_display_off = true;
} else {
	$forced_display_off = false;
}

//Check location
if (! is_readable('./UpgradeOutput.php') || ! is_readable('./LoggedOutput.php')) {
    echo "Could not find the upgrader files!\n<br />\nPlease make sure the entire upgrade folder contents are available.";
    throw new Exception('Attempted upgrade by ' . $_SERVER['REMOTE_ADDR'] . ' without the logger class files.');
}

require './UpgradeOutput.php';
require './LoggedOutput.php';

$show = new \XMB\LoggedOutput($logfile);

if (! is_readable(ROOT . 'header.php')) {
    $show->error('Could not find XMB!<br />Please make sure the upgrade folder is in the same folder as index.php and header.php.');
    throw new Exception('Attempted upgrade by ' . $_SERVER['REMOTE_ADDR'] . ' from wrong location.');
}

//Authenticate Browser
require ROOT . 'header.php';

$db = \XMB\Services\db();
$vars = \XMB\Services\vars();

echo "<html><head><title>XMB Upgrade Script</title><body>Database Connection Established<br />\n";
if ($vars->debug) {
    echo 'Debug Mode Enabled.';
	if ($forced_display_off) {
		ini_set('display_errors', '1');
		trigger_error('Your PHP server has display_errors=On, which should never be used on production systems.', E_USER_WARNING);
	}
} else {
    echo 'Debug is False - You will not see any errors.';
}

if (! defined('X_SADMIN') || ! X_SADMIN) {
    $show->error('This script may be run only by a Super Administrator.<br />Please <a href="login.php" target="_parent">Log In</a> first to begin the upgrade successfully.');
    throw new Exception('Unauthenticated upgrade attempt by ' . $_SERVER['REMOTE_ADDR']);
}

//Check Server Version
if (version_compare(phpversion(), PHP_MIN_VER, '<')) {
    $show->error('XMB requires PHP version ' . PHP_MIN_VER . ' or higher to work properly.  Version ' . phpversion() . ' is running.');
    throw new Exception('Admin attempted upgrade with obsolete PHP engine.');
}
if (version_compare($db->server_version(), MYSQL_MIN_VER, '<')) {
    $show->error('<br /><br />XMB requires MySQL version ' . MYSQL_MIN_VER . ' or higher to work properly.  Version ' . $db->server_version() . ' is running.');
    throw new Exception('Admin attempted upgrade with obsolete MySQL engine.');
}


$show->progress('Confirming the upgrade files are present');

if (is_dir(ROOT . 'install') || is_dir(ROOT . 'Install')) {
	$show->error('Wrong files present!<br />Please delete any folders named "install".');
	throw new Exception('Admin attempted upgrade while non-upgrade files were present.');
}
if (!is_file(ROOT.'lang/English.lang.php')) {
	$show->error('Files missing!<br />Please make sure to upload the lang/English.lang.php file.');
	throw new Exception('Admin attempted upgrade with English.lang.php missing.');
}
if (!is_file(ROOT.'include/schema.inc.php')) {
	$show->error('Files missing!<br />Please make sure to upload the include/schema.inc.php file.');
	throw new Exception('Admin attempted upgrade with schema.lang.php missing.');
}
if (!is_file('./upgrade.lib.php')) {
	$show->error('Files missing!<br />Please make sure to upload the upgrade/upgrade.lib.php file.');
	throw new Exception('Admin attempted upgrade with upgrade.lib.php missing.');
}

$trigger_old_schema = (int) $vars->settings['schema_version'];

require ROOT . 'include/schema.inc.php';
require './upgrade.lib.php';

$schema = new \XMB\Schema($db, $vars);

$lib = new \XMB\Upgrade($db, $show, $schema, $vars);

$lib->xmb_upgrade();

if ($trigger_old_schema < 5) {
    $show->finished('<b>Done! :D</b><br />Now <a href="../misc.php?action=login" target="_parent">login and remember to turn your board back on</a>.<br />');
} else {
    $show->finished('<b>Done! :D</b><br />Now <a href="../admin/settings.php#1" target="_parent">reset the Board Status setting to turn your board back on</a>.<br />');
}
echo "\nDone.</body></html>";
