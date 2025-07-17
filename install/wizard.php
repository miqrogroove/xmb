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

if (! defined('XMB\ROOT')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Not allowed to run this file directly.');
}

//Check location
if (
    ! is_readable('./HttpOutput.php') ||
    ! is_readable('./LoggedOutput.php') ||
    ! is_readable('./UpgradeOutput.php') ||
    ! is_readable('./upgrade.lib.php') ||
    ! is_readable('./WizFunctions.php') ||
    ! is_readable(ROOT . 'header.php')
) {
    echo "Could not find the installer files!\n<br />\nPlease make sure the entire XMB folder contents are available.";
    throw new Exception('Attempted install by ' . $_SERVER['REMOTE_ADDR'] . ' without the required files.');
}

require './UpgradeOutput.php';

require './HttpOutput.php';
require './WizFunctions.php';

// Check the status of the config.php file, if any
$config_success = true;
$config_error = '';
$status = '';
if (is_readable(ROOT . 'config.php')) {
    try {
        include ROOT . 'config.php';
    } catch (Throwable $e) {
        $status = 'bad-config-file';
        $config_success = false;
        $config_error = $e->getMessage();
    }
} else {
    $status = 'no-config-file';
	$config_success = false;
}
if ($config_success) {    
    if (isset($database, $dbhost, $dbuser, $dbpw, $dbname, $pconnect, $tablepre)) {
        $status = already_installed($database, $dbhost, $dbuser, $dbpw, $dbname, $pconnect, $tablepre);
        switch ($status) {
            case 'no-db-config':
            case 'no-db-extension':
            case 'no-connection':
                $config_success = false;
        }
    } else {
        $status = 'no-db-config';
        $config_success = false;
    }
}

if ($status == 'installed') {
    define('XMB\UPGRADE', true);
} else {
    define('XMB\INSTALL', true);
}

// Check location
if (! is_readable(ROOT . 'header.php')) {
    echo 'Could not find XMB!<br />Please make sure the install folder is in the same folder as header.php.<br />';
    throw new Exception('Attempted upgrade by ' . $_SERVER['REMOTE_ADDR'] . ' from wrong location.');
}

require ROOT . 'header.php';

$template = Services\template();
$vars = Services\vars();

$template->versiongeneral = $vars->versiongeneral;
$template->versionshort = $vars->versionshort;

switch ($status) {
    case 'no-config-file':
        $config_error = $vars->lang['config_error_file'];
        break;
    case 'no-db-config':
        $config_error = $vars->lang['config_error_defaults'];
        break;
    case 'no-db-extension':
        $config_error = $vars->lang['install_db_ext_error'];
        break;
    case 'no-connection':
        $config_error = $vars->lang['config_error_connect'];
}

$vStep = intval($_REQUEST['step'] ?? 1);

if ($vStep != 4) {
    header("Content-type: text/html;charset=ISO-8859-1");
}

$class = [];
for ($i = 1; $i <= 6; $i++) {
    $class[$i] = 'none';
}
$class[$vStep] = 'current';
$template->class = $class;

if ($status == 'installed') {
    $db = Services\db();

    if ((int) $vars->settings['schema_version'] >= Schema::VER) {
        header('HTTP/1.0 403 Forbidden');
        exit($vars->lang['already_installed']);
    }

    if (! defined('XMB\X_SADMIN') || ! X_SADMIN) {
        header('HTTP/1.0 403 Forbidden');
        echo "<br /><br />\n<p>" . $vars->lang['upgrade_admin'] . "</p>\n<p>" . str_replace('$url', $vars->full_url . 'install/login.php', $vars->lang['upgrade_admin_login']) . "</p>\n";
        throw new Exception(str_replace('$ipAddress', $_SERVER['REMOTE_ADDR'], $vars->lang['upgrade_admin_error']));
    }

    ini_set('display_errors', '1');

    // Check Server Version
    $source = new XMBVersion();
    $data = $source->get();
    if (version_compare($db->server_version(), $data['mysqlMinVer'], '<')) {
        echo "<br /><br />\n" . str_replace(
            ['$minimum', '$current'],
            [$data['mysqlMinVer'], $db->server_version()],
            $vars->lang['mysql_min_ver'],
        );
        throw new Exception($vars->lang['mysql_min_error']);
    }

    // Initialize Verbose Logging
    require './LoggedOutput.php';

    $result = file_put_contents(LoggedOutput::LOG_FILE, $vars->lang['upgrade_init']);
    if (false === $result) {
        echo "<br /><br />\n" . str_replace('$filepath', LoggedOutput::LOG_FILE, $vars->lang['write_error']) . '  ' . $vars->lang['write_check'];
        throw new RuntimeException(str_replace('$filepath', LoggedOutput::LOG_FILE, $vars->lang['write_error']));
    }

    if ($vStep > 2) {
        header('Location: ' . $template->full_url . 'install/?step=1');
        exit;
    }

    $template->process('install_header.php', echo: true);

    if (ERR_DISPLAY_FORCED_OFF) {
        trigger_error($vars->lang['upgrade_display_errors'], E_USER_WARNING);
    }

    if ($vars->debug) {
        echo "<p>" . $vars->lang['upgrade_debug_on'] . "</p>\n";
    } else {
        echo "<p>" . $vars->lang['upgrade_debug_off'] . "</p>\n";
    }

    $template->version = $vars->versiongeneral;

    if ($vStep <= 1) {
        $template->process('install_upgrade_intro.php', echo: true);
    } elseif (2 === $vStep) {

        // The status.php frame will show logged output.
        // The trigger.php frame will create the logged output and display any fatal errors.
        // These requests for separate frames avoid buffering of script output while the upgrade gets processed.
        $template->process('install_upgrade_window.php', echo: true);
    }
    $template->process('install_footer.php', echo: true);
    exit;
}

ini_set('display_errors', '1');

if (! empty($full_url) && $full_url != 'FULLURL') {
    $template->full_url = $full_url;
} else {
    // Assumed Full URL
    if (! empty($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']) {
        $scheme = 'https';
    } else {
        $scheme = 'http';
    }
    // SCRIPT_NAME is expected to end with 'install/index.php'.  Anything before that is part of the forum's web path.
    $template->full_url = $scheme . '://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') - strlen('install'));
}

$show = new HttpOutput($template, $vars);

switch ($vStep) {
    case 1: // welcome
        $content = $template->process('install_welcome.php');
        break;

    case 2: // versioncheck
        $content = $template->process('install_version.php');
        break;

    case 3: // agreement
        $content = $template->process('install_license.php');
        break;

    case 4: // config.php set-up
        $vSubStep = isset($_REQUEST['substep']) ? trim($_REQUEST['substep']) : '';
        switch ($vSubStep) {
            case 'create':
                // Open config.php
                if (is_readable(ROOT . 'config-dist.php')) {
                    $configuration = file_get_contents(ROOT . 'config-dist.php');
                } else {
                    $configuration = '';
                }

                // Now, replace the main text values with those given by user
                $find = [
                    "'DB/NAME'",
                    "'DB/USER'",
                    "'DB/PW'",
                    "'localhost'",
                    "'TABLE/PRE'",
                    "'FULLURL'",
                ];
                $replace = [
                    input_to_literal(getPhpInput('db_name')),
                    input_to_literal(getPhpInput('db_user')),
                    input_to_literal(getPhpInput('db_pw')),
                    input_to_literal(getPhpInput('db_host')),
                    input_to_literal(getPhpInput('table_pre')),
                    input_to_literal(getPhpInput('fullurl')),
                ];
                foreach ($find as $phrase) {
                    if (strpos($configuration, $phrase) === false) {
                        $configuration = "<?php\n"
                            . "\$dbname   = 'DB/NAME';\n"
                            . "\$dbuser   = 'DB/USER';\n"
                            . "\$dbpw     = 'DB/PW';\n"
                            . "\$dbhost   = 'localhost';\n"
                            . "\$database = 'mysql';\n"
                            . "\$pconnect = false;\n"
                            . "\$tablepre = 'TABLE/PRE';\n"
                            . "\$full_url = 'FULLURL';\n"
                            . "\$comment_output = false;\n"
                            . "\$i = 1;\n"
                            . "\$plugname[\$i]  = '';\n"
                            . "\$plugurl[\$i]   = '';\n"
                            . "\$plugadmin[\$i] = false;\n"
                            . "\$plugimg[\$i]   = '';\n"
                            . "\$i++;\n"
                            . "\$allow_spec_q     = false;\n"
                            . "\$show_full_info   = true;\n\n"
                            . "\$debug            = true;\n"
                            . "\$log_mysql_errors = false;\n\n"
                            . "\n// Do not edit below this line.\nreturn;\n";
                        break;
                    }
                }

                $configuration = str_replace($find, $replace, $configuration);

                // Show Full Footer Info
                if (! isset($_REQUEST['showfullinfo'])) {
                    $configuration = str_ireplace('show_full_info = true;', 'show_full_info = false;', $configuration);
                }

                switch ($_REQUEST['method']) {
                    case 1: // Show configuration on screen
                        header("Content-type: text/html;charset=ISO-8859-1");
                        $template->configuration = $configuration;
                        $content = $template->process('install_config_inline.php');
                        break;

                    case 2: // Save configuration to disk
                        header("Content-type: text/html;charset=ISO-8859-1");

                        if (file_put_contents(ROOT . 'config.php', $configuration) === false) {
                            $template->result = $vars->lang['config_write_error'];
                        } else {
                            $template->result = $vars->lang['config_write_success'];
                        }
                        $content = $template->process('install_config_write.php');
                        break;

                    case 3: // Send configuration as a file
                        $size = strlen($configuration);
                        header("Content-type: application/octet-stream");
                        header("Content-length: $size");
                        header("Content-Disposition: attachment; filename=config.php");
                        header("Content-Description: XMB Configuration");
                        header("Pragma: no-cache");
                        header("Expires: 0");
                        // Start file download
                        echo $configuration;
                        exit;
                    default:
                        // This shouldn't happen because the template below has a default value in the select element.
                        $content = 'You did not specify a method of configuration.  Please go back and do so now.';
                        break;
                } // for method
                $footer = $template->process('install_footer_no_sidebar.php');
                echo $content, $footer;
                exit;

            default:
                header("Content-type: text/html;charset=ISO-8859-1");

                // Get the DB types...
                $template->types = '<select name="db_type"><option selected="selected" value="mysql">mysql</option></select>';

                $content = $template->process('install_config_form.php');
                break;
        }
        break; // end case 4

    case 5: // Make the administrator set a username and password for the super admin user

        if (! $config_success) {
            $show->wizardError($vars->lang['config_error'], $config_error);
        }

        $config_array = array(
            'dbname' => 'DB/NAME',
            'dbuser' => 'DB/USER',
            'dbpw' => 'DB/PW',
            'dbhost' => 'DB_HOST',
            'database' => 'DB_TYPE',
            'tablepre' => 'TABLE/PRE',
            'full_url' => 'FULLURL',
            'allow_spec_q' => 'SPECQ',
            'show_full_info' => 'SHOWFULLINFO',
            'comment_output' => 'COMMENTOUTPUT'
        );
        foreach ($config_array as $key => $value) {
            if (${$key} === $value) {
                $show->wizardError($vars->lang['config_error'], $vars->lang['config_error_defaults']);
            }
        }
        $vars->debug = $debug;

        $boot = new Bootup($template, $vars);
        $boot->parseURL($full_url);
        $boot->debugURLsettings($vars->cookiesecure, $vars->cookiedomain, $vars->cookiepath);

        $content = $template->process('install_admin_form.php');
        break;

    case 6: // remaining parts
        $vars->debug = $debug;
        $vars->full_url = $full_url;
        $vars->tablepre = $tablepre;
        $template->addRefs();

        // check db-connection.
        if (! $config_success) {
            $show->wizardError($vars->lang['config_error'], $config_error);
        }

        // Force upgrade to mysqli
        if ('mysql' === $database) $database = 'mysqli';

        require_once ROOT . "db/{$database}.php";

        $db = new MySQLiDatabase($debug, $log_mysql_errors);
        $db->stopQueryLogging();
        
        // let's check if the actual functionality exists...

        if (! $db->isInstalled()) {
            $show->wizardError($vars->lang['install_db_ext'], str_replace('$database', $database, $vars->lang['install_db_ext_error']));
        }

        // let's check the connection itself.
        $result = $db->testConnect($dbhost, $dbuser, $dbpw, $dbname);
        if (! $result) {
            $show->wizardError($vars->lang['install_db_connect'], str_replace('$msg', $db->getTestError(), $vars->lang['install_db_connect_error']));
        }

        $sqlver = $db->server_version();

        $source = new XMBVersion();
        $data = $source->get();
        if (version_compare($sqlver, $data['mysqlMinVer'], '<')) {
            $message = str_replace(
                ['$minimum', '$current'],
                [$data['mysqlMinVer'], $sqlver],
                $vars->lang['mysql_min_ver'],
            );
            $show->wizardError($vars->lang['version_check'], $message);
        }

        // throw in all stuff then :)
        $template->process('install_header.php', echo: true);
        $template->process('install_progress_header.php', echo: true);

        $show->progress('Checking Database Username Security');
        if ($dbuser == 'root') {
            $show->warning('You have configured XMB to use root access to the database. This is a security hazard. If your server gets hacked, or php itself crashes, the config.php file might be available freely to anyone looking at it, and thus reveal your root username/password. Please consider making a new user for XMB to use the database.');
        } else {
            $show->okay();
        }

        require './cinst.php';

        $schema = new Schema($db, $vars);
        $sql = new SQL($db, $vars->tablepre);
        $validate = new Validation($db);
        $lib = new Install($db, $schema, $sql, $show, $validate, $vars);

        $lib->go();

        $show->finished(str_replace('$url', $vars->full_url, $vars->lang['install_done_detail']));
        $template->process('install_footer.php', echo: true);
        exit;
    default:
        header('Location: ' . $template->full_url . 'install/step=1');
        exit;
}
    
$template->process('install_header.php', echo: true);
if (ERR_DISPLAY_FORCED_OFF) {
    trigger_error($vars->lang['upgrade_display_errors'], E_USER_WARNING);
}
echo $content;
$template->process('install_footer.php', echo: true);
