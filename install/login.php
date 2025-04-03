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

define('XMB_ROOT', '../');
define('XMB_UPGRADE', true);

require XMB_ROOT . 'header.php';

$core = \XMB\Services\core();
$sql = \XMB\Services\sql();
$vars = \XMB\Services\vars();

$username = $validate->postedVar('username', dbescape: false);

if (strlen($username) == 0) {
    $core->put_cookie('xmbuser');  // Make sure user is logged out.
    ?>
    <form method="post" action="">
        <label>Username: <input type="text" name="username" /></label><br />
        <label>Password: <input type="password" name="password" /></label><br />
        <input type="submit" />
    </form>
    <?php
} else {
    if ((int) $vars->settings['schema_version'] >= 5) {
        // Already logged in by \XMB\Session\Manager
        if (! X_SADMIN) {
            echo "This script may be run only by a Super Administrator.<br />Please <a href='" . $vars->full_url . "upgrade/login.php'>Try Again</a>.<br />";
            throw new Exception('Upgrade login failure by '.$_SERVER['REMOTE_ADDR']);
        }
    } else {
        $password = md5($_POST['password']);
        if ($sql->checkUpgradeOldLogin($username, $password)) {
            $core->put_cookie('xmbuser', $username);
            $core->put_cookie('xmbpw', $password);
        } else {
            echo "This script may be run only by a Super Administrator.<br />Please <a href='" . $vars->full_url . "upgrade/login.php'>Try Again</a>.<br />";
            throw new Exception('Upgrade login failure by '.$_SERVER['REMOTE_ADDR']);
        }
    }

    echo "Cookies set.  <a href='" . $vars->full_url . "upgrade/'>Return to upgrade.</a>";
}
