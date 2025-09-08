<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-2
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

namespace SampleCode;

use XMB\Schema;
use XMB\Services;
use XMB\ShellOutput;
use XMB\Upgrade;

use const XMB\ROOT;

// Delete me.
header('HTTP/1.0 403 Forbidden');
exit('This file is provided to illustrate customized XMB upgrade techniques.');

// Allow script to continue if user goes away.
ignore_user_abort(true);

// Script constants.
define('XMB\ROOT', '../'); // Location of XMB files relative to this script.
define('XMB\UPGRADE', true);

// Run XMB's header.php file and add upgrade dependencies.
require ROOT . 'header.php';
require './UpgradeOutput.php'; // Interface must go before implementation.
require './ShellOutput.php';
require './upgrade.lib.php';

// Retrieve XMB's shared services for ease of use.
$db = Services\db();
$vars = Services\vars();

// Create an output implementation for the upgrade library.  You may also customize this one or supply your own.
$show = new ShellOutput();

// Create instances of Schema and Upgrade.
$schema = new Schema($db, $vars);
$lib = new Upgrade($db, $show, $schema, $vars);

// Make it happen!
$lib->xmb_upgrade();
$show->finished('Done');

// Cleanup Notes
// 1. The website is still in maintenance mode, which was forced during the upgrade.  Reset it now.
$db->query("UPDATE " . $vars->tablepre . "settings SET value = 'on' WHERE name = 'bbstatus'");

// 2. This script did not self-destruct and should not be available for public use on a live site.
