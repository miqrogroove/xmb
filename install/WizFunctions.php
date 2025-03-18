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

function show_act($act)
{
    $act .= str_repeat('.', (75-strlen($act)));
    echo '<span class="progress">'.$act;
}

function show_result($type)
{
    switch ($type) {
        case X_INST_ERR:
            echo '<span class="progressErr">ERROR</span><br />';
            break;
        case X_INST_WARN:
            echo '<span class="progressWarn">WARNING</span><br />';
            break;
        case X_INST_OK:
            echo '<span class="progressOk">OK</span><br />';
            break;
        case X_INST_SKIP:
            echo '<span class="progressSkip">SKIPPED</span><br />';
            break;
    }
    echo "</span>\n";
}

function error($head, $msg, $die = true)
{
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

/**
 * Haults the script if XMB is already installed.
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
function already_installed($database, $dbhost, $dbuser, $dbpw, $dbname, $pconnect, $tablepre): string
{
    // When config.php has default values, XMB is not installed.
    $config_array = array(
        'dbname' => 'DB/NAME',
        'dbuser' => 'DB/USER',
        'dbpw' => 'DB/PW',
        'dbhost' => 'DB_HOST',
        'tablepre' => 'TABLE/PRE',
    );
    foreach($config_array as $key => $value) {
        if (${$key} === $value) {
            return 'no-db-config';
        }
    }

    // Force upgrade to mysqli
    if ('mysql' === $database) $database = 'mysqli';

    if (! is_readable(XMB_ROOT . "db/{$database}.php")) return false;
    require_once XMB_ROOT . "db/{$database}.php";

    $db = new dbstuff;
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

