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

// Delete me.
header('HTTP/1.0 403 Forbidden');
exit('This file is provided to illustrate customized XMB upgrade techniques.');

ignore_user_abort(true);

// Script constants.
define('XMB_ROOT', '../'); // Location of XMB files relative to this script.

// Emulate logic needed from XMB's header.php file.
error_reporting(-1);

// Interfaces and base dependencies go first.
require XMB_ROOT.'db/DBStuff.php';
require XMB_ROOT.'include/Variables.php';
require './UpgradeOutput.php';

// Implementations
require XMB_ROOT.'include/Bootup.php';
require XMB_ROOT.'include/format.php';
require XMB_ROOT.'include/schema.inc.php';
require XMB_ROOT.'include/SettingsLoader.php';
require XMB_ROOT.'include/Template.php';
require './upgrade.lib.php';

$vars = new \XMB\Variables();
$boot = new \XMB\Bootup(
    new \XMB\Template($vars),
    $vars,
);

$boot->loadConfig();
$boot->setVersion();

if (! $vars->debug) {
    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR);
}

$db = $boot->connectDB();

unset($boot);

define('$vars::NONCE_KEY_LEN', 12);

(new \XMB\SettingsLoader(db(), vars()))->readToVars();


// Create an output implementation for the upgrade library.
require './ShellOutput.php';

$show = new \XMB\ShellOutput();


//Make it happen!
$schema = new \XMB\Schema($db, $vars);
$lib = new \XMB\Upgrade($db, $show, $schema, $vars);

$lib->xmb_upgrade();
$show->progress('Done');

//Cleanup Notes
//1. The website is still in maintenance mode.  The script sets xmb.settings (name='bbstatus', value='off')
//2. This script did not self-destruct and should not be available for public use on a live site.

