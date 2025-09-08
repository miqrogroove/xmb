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

use XMB\Install;
use XMB\MySQLiDatabase;
use XMB\Password;
use XMB\Schema;
use XMB\Services;
use XMB\ShellOutput;
use XMB\SiteData;
use XMB\SQL;

use function XMB\intput_to_literal;

// Delete me.
header('HTTP/1.0 403 Forbidden');
exit('This file is provided to illustrate customized XMB install techniques.');

// PHP configuration
error_reporting(-1);
ini_set('display_errors', '1');

// Script constants.
define('XMB\ROOT', '../'); // Location of XMB files relative to this script.
define('XMB\INSTALL', true);

// Run XMB's header.php file and add installer dependencies.
require \XMB\ROOT . 'header.php';
require './UpgradeOutput.php'; // Interface must go before implementation.
require './cinst.php';
require './ShellOutput.php';
require './SiteData.php';
require_once \XMB\ROOT . 'db/mysqli.php';

// Create an output implementation for the install library.  You may also customize this one or supply your own.
$show = new ShellOutput();

// Create an object for your customized settings.  Use any means to fill the properties.  See SiteData.php for details.
$site = new SiteData();
$site->showVersion = true;
$site->adminEmail = 'nobody@example.com';
$site->adminPass = '-&afEh##A5lNAthuY#tH';
$site->adminUser = 'admin';
$site->dbHost = 'localhost';
$site->dbName = 'xmbbase';
$site->dbPass = '+rara$ruyiB=tho6eB-j';
$site->dbTablePrefix = 'xmb_';
$site->dbUser = 'xmbweb';
$site->fullURL = 'https://example.com/forums/';

// Load the config template, fill it with some of the SiteData, and create config.php.
$configuration = file_get_contents(\XMB\ROOT . 'config-dist.php');
$find = [
    "'DB/NAME'",
    "'DB/USER'",
    "'DB/PW'",
    "'localhost'",
    "'TABLE/PRE'",
    "'FULLURL'",
];
$replace = [
    input_to_literal($site->dbName),
    input_to_literal($site->dbUser),
    input_to_literal($site->dbPass),
    input_to_literal($site->dbHost),
    input_to_literal($site->dbTablePrefix),
    input_to_literal($site->fullURL),
];
$configuration = str_replace($find, $replace, $configuration);
if (! $site->showVersion) {
    $configuration = str_ireplace('show_full_info = true;', 'show_full_info = false;', $configuration);
}
file_put_contents(\XMB\ROOT . 'config.php', $configuration);

// Manage XMB services
$vars = Services\vars();
$vars->debug = true;
$vars->full_url = $site->fullURL;
$vars->log_mysql_errors = false; // You may change this if appropriate for debugging.
$vars->tablepre = $site->dbTablePrefix;

$db = new MySQLiDatabase($vars->debug, $vars->log_mysql_errors);
$db->stopQueryLogging();
$result = $db->testConnect($site->dbHost, $site->dbUser, $site->dbPass, $site->dbName);
if (! $result) {
    $show->error($vars->lang['install_db_connect'], str_replace('$msg', $db->getTestError(), $vars->lang['install_db_connect_error']));
}

$schema = new Schema($db, $vars);
$sql = new SQL($db, $vars->tablepre);

$password = new Password($sql);

$lib = new Install($db, $password, $schema, $site, $sql, $show, $vars);

$lib->go();

// Cleanup Notes
// 1. The Super Admin password has not been provided to the user yet.

// 2. This script did not self-destruct and should not be available for public use on a live site.
