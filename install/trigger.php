<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

use Exception;
use XMBVersion;

ignore_user_abort(true);
header('Expires: 0');
header('X-Frame-Options: sameorigin');

//Script constants
const ROOT = '../';
const UPGRADE = true;

//Check configuration
error_reporting(-1);
if (ini_get('display_errors')) {
	ini_set('display_errors', '0');
}

//Authenticate Browser
require ROOT . 'header.php';
require './UpgradeOutput.php';
require './LoggedOutput.php';
require './upgrade.lib.php';

$db = Services\db();
$vars = Services\vars();
$schema = new Schema($db, $vars);
$show = new LoggedOutput();
$lib = new Upgrade($db, $show, $schema, $vars);

if (! defined('XMB\X_SADMIN') || ! X_SADMIN) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Not allowed to run this file directly.';
    throw new Exception('Unauthenticated upgrade attempt by ' . $_SERVER['REMOTE_ADDR']);
}

$trigger_old_schema = (int) $vars->settings['schema_version'];

if ($trigger_old_schema >= $schema::VER) {
    header('HTTP/1.0 403 Forbidden');
    exit($vars->lang['already_installed']);
}

// Ensure any new fatal errors will be displayed, otherwise they might end up in a PHP log file (or not) that the admin doesn't know how to find.
ini_set('display_errors', '1');
error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_CORE_WARNING | E_COMPILE_WARNING);

// Ensure non-fatal errors will be sent to the upgrade output file.
set_error_handler([$show, 'error_handler']);

$show->progress($vars->lang['upgrade_connect']);

//Check Server Version
$version = new XMBVersion();
$version->assertPHP();
$data = $version->get();
if (version_compare($db->server_version(), $data['mysqlMinVer'], '<')) {
    throw new Exception('Admin attempted upgrade with obsolete MySQL engine.');
}

try {
    $lib->xmb_upgrade();
} catch (Throwable $e) {
    $show->error($e->getMessage() . "\n" . $e->getTraceAsString());
    exit;
}

if ($trigger_old_schema < 5) {
    $show->finished('<b>Done! :D</b><br />Now <a href="../misc.php?action=login" target="_parent">login and remember to turn your board back on</a>.<br />');
} else {
    $show->finished('<b>Done! :D</b><br />Now <a href="../admin/settings.php" target="_parent">reset the Board Status setting to turn your board back on</a>.<br />');
}
