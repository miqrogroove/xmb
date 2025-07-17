<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-1
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
