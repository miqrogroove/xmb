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

// XMB Constants
define('ROOT', '../');
define('X_INST_ERR', 0);
define('X_INST_WARN', 1);
define('X_INST_OK', 2);
define('X_INST_SKIP', 3);
define('IN_CODE', true);

require ROOT . 'include/version.php';

function error($head, $msg, $die=true) {
    echo "\n";
    echo '<h1 class="progressErr">'.$head.'</h1>';
    echo '<span class="progressWarn">'.$msg.'</span><br />';
    echo "\n";
    if ($die) {
        echo '
            </div>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="footer">
        <div class="top"><span></span></div>
        <div class="center-content">
            <span><a href="https://www.xmbforum2.com/" onclick="window.open(this.href); return false;"><strong><abbr title="eXtreme Message Board">XMB</abbr>
            Forum Software</strong></a>&nbsp;&copy; '.COPY_YEAR.' The XMB Group</span>
        </div>
        <div class="bottom"><span></span></div>
    </div>
</div>';
        exit();
    }
}

error_reporting(-1);

$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : 1;
$substep = isset($_REQUEST['substep']) ? $_REQUEST['substep'] : 0;
$vStep = isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : 1;

if ($vStep >= 1 && $vStep <= 6 && $vStep != 4) {
    header("Content-type: text/html;charset=ISO-8859-1");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>XMB Installer</title>
    <meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" />
    <link rel="stylesheet" href="../images/install/install.css" type="text/css" media="screen"/>
</head>
<body>
<div id="main">
    <div id="header">
        <img src="../images/install/logo.png" alt="XMB" title="XMB" />
    </div>
<?php
}

// Check Server Version
if (version_compare(phpversion(), PHP_MIN_VER, '<')) {
    $message = 'XMB requires PHP version ' . PHP_MIN_VER . ' or higher to work properly.  Version ' . phpversion() . ' is running.';
    error('Version mismatch', $message);
}

// Proceed with modern scripting.
require ROOT . 'install/wizard.php';
