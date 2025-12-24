<?php

/**
 * eXtreme Message Board
 * XMB 1.10
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

use RuntimeException;

/**
 * Check if XMB is already installed.
 *
 * @since 1.9.11.09
 * @param string $database
 * @param string $dbhost
 * @param string $dbuser
 * @param string $dbpw
 * @param string $dbname
 * @param bool   $pconnect
 * @param string $tablepre
 */
function already_installed(
    string $database,
    string $dbhost,
    string $dbuser,
    #[\SensitiveParameter]
    string $dbpw,
    string $dbname,
    bool $pconnect,
    string $tablepre,
): string {
    // When config.php has default values, XMB is not installed.
    $config_array = [
        'dbname' => 'DB/NAME',
        'dbuser' => 'DB/USER',
        'dbpw' => 'DB/PW',
        'dbhost' => 'DB_HOST',
        'tablepre' => 'TABLE/PRE',
    ];
    foreach ($config_array as $key => $value) {
        if (${$key} === $value) {
            return 'no-db-config';
        }
    }

    // Force upgrade to mysqli
    if ('mysql' === $database) $database = 'mysqli';

    if (! is_readable(ROOT . "db/{$database}.php")) return false;
    require_once ROOT . 'db/DBStuff.php';
    require_once ROOT . "db/{$database}.php";

    $db = new MySQLiDatabase(debug: true, logErrors: true);
    $db->stopQueryLogging();

    if (! $db->isInstalled()) {
        return 'no-db-extension';
    }

    $result = $db->testConnect($dbhost, $dbuser, $dbpw, $dbname);
    if (! $result) return 'no-connection';

    $like_name = $db->like_escape($tablepre . 'settings');
    $result = $db->query("SHOW TABLES LIKE '$like_name'");
    $count = $db->num_rows($result);
    $db->free_result($result);
    $db->close();
    if (1 === $count) {
        return 'installed';
    } else {
        return 'no-db-table';
    }
}

/**
 * Get the config file with all template values filled by specified data.
 *
 * @since 1.10.00
 * @param SiteData $site
 * @return string
 */
function generate_config(SiteData $site): string
{
    // Open config.php
    if (is_readable(ROOT . 'config-dist.php')) {
        $configuration = file_get_contents(ROOT . 'config-dist.php');
    } else {
        $configuration = '';
    }

    // Now, replace the configuration text values with those given by user
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
    if (! $site->showVersion) {
        $configuration = str_ireplace('show_full_info = true;', 'show_full_info = false;', $configuration);
    }

    return $configuration;
}

/**
 * Attempt to remove any incompatible code from the config.php file.
 *
 * @since 1.10.02
 */
function upgrade_config()
{
    $dirty = false;
    $phrase1 = [
        'old' => "defined('IN_CODE')",
        'new' => "defined('XMB\ROOT')",
    ];
    $phrase2 = [
        'old' => "define('DEBUG'",
        'new' => '$debug = ',
    ];
    $phrase3 = [
        'old' => "define('DEBUG_ALL'",
        'new' => '',
    ];
    $phrase4 = [
        'old' => "define('LOG_MYSQL_ERRORS'",
        'new' => '$log_mysql_errors = ',
    ];
    $config = file_get_contents(ROOT . 'config.php');

    if (strpos($config, $phrase1['old']) !== false) {
        // The IN_CODE constant was introduced in v1.9.8 and replaced with the XMB\ROOT constant in v1.10.
        $config = str_replace($phrase1['old'], $phrase1['new'], $config);
        $dirty = true;
    }
    $start = strpos($config, $phrase2['old']);
    while ($start !== false) {
        // The DEBUG constant was introduced in v.1.9.8 and replaced with the $debug variable in v1.10.
        $end = strpos($config, ';', $start);
        $end++;
        $phrase = substr($config, $start, $end - $start);
        $value = stripos($phrase, 'true') !== false;
        $value = $value ? 'true' : 'false';
        $config = substr($config, 0, $start) . $phrase2['new'] . $value . ';' . substr($config, $end);
        $end = 0; // $end is no longer a valid offset because the semi-colon has moved.
        $dirty = true;
        $start = strpos($config, $phrase2['old']);
    }
    $start = strpos($config, $phrase3['old']);
    while ($start !== false) {
        // The DEBUG_ALL constant was introduced in v.1.9.9 and eliminated in v1.9.11.
        $end = strpos($config, ';', $start);
        $end++;
        $config = substr($config, 0, $start) . substr($config, $end);
        $end = 0; // $end is no longer a valid offset because the semi-colon has moved.
        $dirty = true;
        $start = strpos($config, $phrase3['old']);
    }
    $start = strpos($config, $phrase4['old']);
    while ($start !== false) {
        // The LOG_MYSQL_ERRORS constant was introduced in v.1.9.11 and replaced with the $log_mysql_errors variable in v1.10.
        $end = strpos($config, ';', $start);
        $end++;
        $phrase = substr($config, $start, $end - $start);
        $value = stripos($phrase, 'true') !== false;
        $value = $value ? 'true' : 'false';
        $config = substr($config, 0, $start) . $phrase4['new'] . $value . ';' . substr($config, $end);
        $end = 0; // $end is no longer a valid offset because the semi-colon has moved.
        $dirty = true;
        $start = strpos($config, $phrase4['old']);
    }
    if ($dirty) {
        $result = file_put_contents(ROOT . 'config.php', $config);
        if ($result === false) {
            throw new RuntimeException(
                'An old config.php file is present and appears to be read-only.
                Please change the file permissions.
                Otherwise, you will need to manually migrate the configuration into config-dist.php and use it to replace the old config.php file.'
            );
        }
    }
}

/**
 * Gather the required dependencies and create an Install service.
 *
 * @since 1.10.00
 */
function installer_factory(DBStuff $db, SiteData $site, UpgradeOutput $show, Variables $vars): Install
{
    $schema = new Schema($db, $vars);
    $sql = new SQL($db, $vars->tablepre);

    $password = new Password($sql);

    return new Install($db, $password, $schema, $site, $sql, $show, $vars);
}
