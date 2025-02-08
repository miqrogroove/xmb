<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12
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
 *
 **/

// This file has been tested against PHP v4.4.6 for backward-compatible error reporting.

// HTTP headers
header('X-Frame-Options: deny');
header('X-Robots-Tag: noindex');

// XMB constants
define('X_SCRIPT', 'upgrade.php');
define('ROOT', '../');
define('LOG_FILE', './upgrade.log');

// PHP configuration
if (ini_get('display_errors')) {
	ini_set('display_errors', '0');
	$forced_display_off = true;
} else {
	$forced_display_off = false;
}

// Check Server Version
require ROOT . 'include/version.php';
if (version_compare(phpversion(), PHP_MIN_VER, '<')) {
    $message = 'XMB requires PHP version ' . PHP_MIN_VER . ' or higher to work properly.  Version ' . phpversion() . ' is running.';
    trigger_error("Attempted upgrade with obsolete PHP engine.  $message", E_USER_WARNING);
    exit("<br /><br />\n\n$message");
}

// Proceed with modern scripting.
require ROOT . 'upgrade/instructions.php';
