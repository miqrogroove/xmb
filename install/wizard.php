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

use Exception;
use XMBVersion;

if (! defined('XMB_ROOT')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Not allowed to run this file directly.');
}

define('X_INST_ERR', 0);
define('X_INST_WARN', 1);
define('X_INST_OK', 2);
define('X_INST_SKIP', 3);

//Check location
if (! is_readable('./UpgradeOutput.php') || ! is_readable('./LoggedOutput.php') || ! is_readable('./upgrade.lib.php') || ! is_readable('./WizFunctions.php') || ! is_readable(XMB_ROOT . 'header.php')) {
    echo "Could not find the installer files!\n<br />\nPlease make sure the entire XMB folder contents are available.";
    throw new Exception('Attempted install by ' . $_SERVER['REMOTE_ADDR'] . ' without the required files.');
}

require './UpgradeOutput.php';
require './WizFunctions.php';

// Check the status of the config.php file, if any
$config_success = true;
$config_error = '';
$status = '';
if (is_readable(XMB_ROOT . 'config.php')) {
    try {
        include XMB_ROOT . 'config.php';
    } catch (Throwable $e) {
        $config_success = false;
        $config_error = $e->getMessage();
    }
    if (isset($database, $dbhost, $dbuser, $dbpw, $dbname, $pconnect, $tablepre)) {
        $status = already_installed($database, $dbhost, $dbuser, $dbpw, $dbname, $pconnect, $tablepre);
        switch ($status) {
            case 'no-db-config':
                $config_success = false;
                $config_error = 'The config.php file is not fully configured.';
                break;
            case 'no-connection':
                $config_success = false;
                $config_error = 'Unable to connect to the database specified in config.php.';
        }
    } else {
        $status = 'no-db-config';
        $config_success = false;
        $config_error = 'The config.php file is not fully configured.';
    }
} else {
    $status = 'no-config-file';
	$config_success = false;
	$config_error = 'The config.php file was not found, or bad permissions.';
}

if ($status == 'installed') {
    define('XMB_UPGRADE', true);
} else {
    define('XMB_INSTALL', true);
}

// Check location
if (! is_readable(XMB_ROOT . 'header.php')) {
    echo 'Could not find XMB!<br />Please make sure the install folder is in the same folder as header.php.<br />';
    throw new Exception('Attempted upgrade by ' . $_SERVER['REMOTE_ADDR'] . ' from wrong location.');
}

require XMB_ROOT . 'header.php';

$template = \XMB\Services\template();
$vars = \XMB\Services\vars();

$vStep = isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : 1;

if ($vStep != 4) {
    header("Content-type: text/html;charset=ISO-8859-1");
    $class = [];
    for ($i = 1; $i <= 6; $i++) {
        $class[$i] = 'none';
    }
    $class[$vStep] = 'current';
    $template->class = $class;
}

if ($status == 'installed') {
    $db = \XMB\Services\db();

    if ((int) $vars->settings['schema_version'] == Schema::VER) {
        header('HTTP/1.0 403 Forbidden');
        exit($vars->lang['already_installed']);
    }

    if (! defined('X_SADMIN') || ! X_SADMIN) {
        header('HTTP/1.0 403 Forbidden');
        echo "<br /><br />\n" . $vars->lang['upgrade_admin'] . "<br />\n" . str_replace('$url', $vars->full_url . 'install/login.php', $vars->lang['upgrade_admin_login']) . "<br />\n";
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

    $template->process('install_header.php', echo: true);

    if (XMB_ERR_DISPLAY_FORCED_OFF) {
        trigger_error($vars->lang['upgrade_display_errors'], E_USER_WARNING);
    }

    if ($vars->debug) {
        echo "<p>" . $vars->lang['upgrade_debug_on'] . "</p>\n";
    } else {
        echo "<p>" . $vars->lang['upgrade_debug_off'] . "</p>\n";
    }

    $template->version = $vars->versiongeneral;

    if (! isset($_GET['step']) || '1' === $_GET['step']) {
        $template->process('install_upgrade_intro.php', echo: true);
    } elseif ('2' === $_GET['step']) {

        // The status.php frame will show logged output.
        // The trigger.php frame will create the logged output and display any fatal errors.
        // These requests for separate frames avoid buffering of script output while the upgrade gets processed.
        $template->process('install_upgrade_window.php', echo: true);
        $template->process('install_footer.php', echo: true);
    }
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

switch($vStep) {
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
        switch($vSubStep) {
        case 'create':
            // Open config.php
            if (is_readable(XMB_ROOT . 'config-dist.php')) {
                $configuration = file_get_contents(XMB_ROOT . 'config-dist.php');
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
                "'default'",
                "'MAILER_USER'",
                "'MAILER_PASS'",
                "'MAILER_HOST'",
                "'MAILER_PORT'",
            ];
            $replace = [
                input_to_literal(getPhpInput('db_name')),
                input_to_literal(getPhpInput('db_user')),
                input_to_literal(getPhpInput('db_pw')),
                input_to_literal(getPhpInput('db_host')),
                input_to_literal(getPhpInput('table_pre')),
                input_to_literal(getPhpInput('fullurl')),
                input_to_literal(getPhpInput('MAILER_TYPE')),
                input_to_literal(getPhpInput('MAILER_USER')),
                input_to_literal(getPhpInput('MAILER_PASS')),
                input_to_literal(getPhpInput('MAILER_HOST')),
                input_to_literal(getPhpInput('MAILER_PORT')),
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
                        . "\$mailer['type']     = 'default';\n"
                        . "\$mailer['username'] = 'MAILER_USER';\n"
                        . "\$mailer['password'] = 'MAILER_PASS';\n"
                        . "\$mailer['host']     = 'MAILER_HOST';\n"
                        . "\$mailer['port']     = 'MAILER_PORT';\n"
                        . "\$i = 1;\n"
                        . "\$plugname[\$i]  = '';\n"
                        . "\$plugurl[\$i]   = '';\n"
                        . "\$plugadmin[\$i] = false;\n"
                        . "\$plugimg[\$i]   = '';\n"
                        . "\$i++;\n"
                        . "\$ipcheck          = false;\n"
                        . "\$allow_spec_q     = false;\n"
                        . "\$show_full_info   = true;\n\n"
                        . "\$debug            = false;\n"
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

                case 2:
                    header("Content-type: text/html;charset=ISO-8859-1");

                    if (file_put_contents(XMB_ROOT . 'config.php', $configuration) === false) {
                        $template->result = $vars->lang['config_write_error'];
                    } else {
                        $template->result = $vars->lang['config_write_success'];
                    }
                    $content = $template->process('install_config_write.php');
                    break;

                case 3:
                    // Get size
                    $size = strlen($configuration);
                    // Put out headers for mime-type, filesize, forced-download, description and no-cache.
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
            break; // for substep 'create'

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
            error('Incorrect Configuration', $config_error);
        }

        $config_array = array(
            'dbname' => 'DB/NAME',
            'dbuser' => 'DB/USER',
            'dbpw' => 'DB/PW',
            'dbhost' => 'DB_HOST',
            'database' => 'DB_TYPE',
            'tablepre' => 'TABLE/PRE',
            'full_url' => 'FULLURL',
            'ipcheck' => 'IPCHECK',
            'allow_spec_q' => 'SPECQ',
            'show_full_info' => 'SHOWFULLINFO',
            'comment_output' => 'COMMENTOUTPUT'
        );
        foreach($config_array as $key => $value) {
            if (${$key} === $value) {
                error('Incorrect Configuration', 'XMB noticed that your config.php file is not fully configured.<br />Please go back to the previous step and follow the instructions carefully.<br />Be sure to click the button labeled "Configure" before proceeding.', TRUE);
            }
        }
        $vars->debug = $debug;

        $boot = new \XMB\Bootup();
        $array = parse_url($full_url);
        if (!isset($array['path'])) {
            $array['path'] = '/';
        }
        if (strpos($array['host'], '.') === FALSE || preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $array['host'])) {
            $array['host'] = '';
        } elseif (substr($array['host'], 0, 4) === 'www.') {
            $array['host'] = substr($array['host'], 3);
        }
        $boot->debugURLsettings(($array['scheme'] == 'https'), $array['host'], $array['path']);
        unset($array);


        $content = $template->process('install_admin_form.php');
        break;

    case 6: // remaining parts
        // check db-connection.
        if (! $config_success) {
            error('Incorrect Configuration', $config_error);
        }

        show_act('Checking Database Files');

        // Force upgrade to mysqli
        if ('mysql' === $database) $database = 'mysqli';

        if (! file_exists(XMB_ROOT."db/{$database}.php")) {
            show_result(X_INST_ERR);
            error('Database connection', 'XMB could not locate the <i>/db/'.$database.'.php</i> file, you have configured xmb to use this database-type. For it to work you will need to upload the file, or change the config.php file to reflect a different choice.', true);
        }
        show_result(X_INST_OK);
        
        require_once XMB_ROOT . "db/{$database}.php";

        $db = new \XMB\MySQLiDatabase($debug, $log_mysql_errors);
        
        show_act('Checking Database API');
        // let's check if the actual functionality exists...

        if (! $db->isInstalled()) {
            error('Database Handler', 'XMB has determined that your php installation does not support the functions required to use <i>'.$database.'</i> to store all data.', true);
            unset($err);
        }
        show_result(X_INST_OK);

        // let's check the connection itself.
        show_act('Checking Database Username Security');
        if ($dbuser == 'root') {
            show_result(X_INST_WARN);
            error('Security hazard', 'You have configured XMB to use root access to the database, this is a security hazard. If your server gets hacked, or php itself crashes, the config.php file might be available freely to anyone looking at it, and thus reveal your root username/password. Please consider making a new user for XMB to use the database.', false);
        } else {
            show_result(X_INST_OK);
        }

        show_act('Checking Database Connection');
        $result = $db->testConnect($dbhost, $dbuser, $dbpw, $dbname);
        if (! $result) {
            show_result(X_INST_ERR);
            error('Database Connection', 'XMB could not connect to the specified database. The database returned "error ' . $db->getTestError() . '"', true);
        } else {
            show_result(X_INST_OK);
        }
        show_act('Checking Database Version');
        $sqlver = $db->server_version();

        $source = new XMBVersion();
        $data = $source->get();
        if (version_compare($sqlver, $data['mysqlMinVer'], '<')) {
            show_result(X_INST_ERR);
            error('Version mismatch', 'XMB requires MySQL version ' . $data['mysqlMinVer'] . ' or higher to work properly.  Version ' . $sqlver . ' is running.', true);
        } else {
            show_result(X_INST_OK);
        }

        // throw in all stuff then :)
        $template->process('install_header.php', echo: true);
        $template->process('install_progress_header.php', echo: true);

        require './cinst.php';
        require './HttpOutput.php';

        $show = new \XMB\HttpOutput($template, $vars);
        $sql = new \XMB\SQL($db, $vars->tablepre);
        $lib = new \XMB\Install($db, $sql, $show, $vars);

        $lib->go();

        $show->finished(str_replace('$url', $vars->full_url, $vars->lang['install_done_detail']));
        $template->process('install_footer.php', echo: true);
        exit;
    default:
        header('Location: ' . $template->full_url . 'index.php?step=1');
        exit;
}
    
$template->process('install_header.php', echo: true);
if (XMB_ERR_DISPLAY_FORCED_OFF) {
    trigger_error($vars->lang['upgrade_display_errors'], E_USER_WARNING);
}
echo $content;
$template->process('install_footer.php', echo: true);
