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

if (! defined('XMB\ROOT')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

// Database connection settings
    $dbname         = 'DB/NAME';   // Name of your database.
    $dbuser         = 'DB/USER';   // Username for database account.
    $dbpw           = 'DB/PW';     // Password for database account.  For help with quotes and slashes, see https://www.php.net/manual/en/language.types.string.php
    $dbhost         = 'localhost'; // Database host. Usually, 'localhost'.
    $database       = 'mysql';     // Database type. Currently, only mysql is supported.
    $pconnect       = false;       // Persistent connection, true = on, false = off, use if 'too many connections'-errors appear

// Table Settings
    $tablepre       = 'TABLE/PRE'; // XMB will prefix each table name with the string you specify here.  'xmb_' is a common choice.

// Address settings
    // In full_url, put the full URL you see when you go to your boards, WITHOUT the filename though!!
    // And please, remember to add the / at the end...
    $full_url       = 'FULLURL';

// Other settings
    // Adds comments to all template output like <!-- template start: index -->...<!-- template end: index -->
    // These comments may invalidate HTML DTDs and should be used for development purposes only.
    // Default value: false;
    $comment_output = false;

// Plugin Settings
    $i = 1;
    // Plugins are the links in the navigation part of the Header. Plugins built-in by default include Search, FAQ, Member List, Today's Posts, Stats and Board Rules.
    // To add extra plugins (links of your own), just edit the code between Start Plugin Code and End Plugin Code. If you with to add more than one, simply copy that block, paste it and add the second one.

    // Start Plugin code
    $plugname[$i]    = '';    // This is the name of your plugin. eg. Avatar Gallery, TeddyBear, etc.
    $plugurl[$i]     = '';    // This is the location, link, or URL to the plugin
    $plugadmin[$i]   = false; // Is this plugin only for admins? Set to true if the plugin can only be seen/used by (super-)admins, false when it's can be used by anyone
    $plugimg[$i]     = '';    // This is the path (full URL) to the image to show in front of the text.
    $i++;
    // End plugin code.

    // Start Plugin code for plugin #2
    $plugname[$i]    = '';    // This is the name of your plugin. eg. Avatar Gallery, TeddyBear, etc.
    $plugurl[$i]     = '';    // This is the location, link, or URL to the plugin
    $plugadmin[$i]   = false; // Is this plugin only for admins? Set to true if the plugin can only be seen/used by (super-)admins, false when it's can be used by anyone
    $plugimg[$i]     = '';    // This is the path (full URL) to the image to show in front of the text.
    $i++;
    // End plugin code for plugin #2

    // To make multiple plugins, copy and paste this plugin-code, so you have multiple entries.

// Registration settings
    /***************
     * The allow_spec_q variable specifies if Special queries (eg. USE database and SHOW DATABASES) are allowed.
     * By default, they are not, meaning $allow_spec_q = false;
     * To allow them, change $allow_spec_q to true ($allow_spec_q = true;)
     ****************
     * The show_full_info variable lets you decide whether to show the Build and Alpha/Beta/SP markings in the HTML or not.
     * Change the value to true to show them, or false to turn them off.
     * Default = true;
     ****************/

    $allow_spec_q   = false;
    $show_full_info = true;

// Debug-mode
    /**
     * To turn on Debug mode, set to true.  To turn off Debug mode, set to false.
     * This mode helps with troubleshooting during install, and allows the new Super Administrator to see
     * database performance after install.
     */

    $debug = true;

    /**
     * To turn on SQL error logging, set to true.  To turn off SQL error logging, set to false.
     * Note the log file will be visible to the public unless it is protected
     * by your web server configuration.  The file name will be 'error_log' unless you change the PHP configuration.
     * If the chmod settings of this directory prevent file Write then the log will not be created.
     */

    $log_mysql_errors = false;

// Do not edit below this line.
// ---------------------------
return;
