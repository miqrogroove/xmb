<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00
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

// This file has been tested against PHP v4.4.6 for backward-compatible error reporting.

if (count(get_included_files()) <= 1) {
    header('HTTP/1.0 403 Forbidden');
    exit('Not allowed to run this file directly.');
}

// HTTP headers
header('X-Frame-Options: deny');
header('X-Robots-Tag: noindex');

// XMB Constants
if (! defined('XMB\ROOT')) define('XMB\ROOT', '../');
define('XMB\ERR_DISPLAY_FORCED_OFF', (bool) ini_get('display_errors'));

// PHP configuration
if (constant('XMB\ERR_DISPLAY_FORCED_OFF')) ini_set('display_errors', '0');
error_reporting(-1);

// Check location
if (! is_readable(constant('XMB\ROOT') . 'include/version.php') || ! is_readable(constant('XMB\ROOT') . 'install/wizard.php')) {
    exit("Could not find the installer files!\n<br />\nPlease make sure the entire <code>include</code> and <code>install</code> folder contents are available.");
}

// PHP Version Test
require constant('XMB\ROOT') . 'include/version.php';
$version = new XMBVersion();
$version->assertPHP();
unset($version);

// Proceed with modern scripting.
require constant('XMB\ROOT') . 'install/wizard.php';
