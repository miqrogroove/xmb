<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-3
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

const ROOT = '../';
const UPGRADE = true;

require ROOT . 'header.php';

$core = Services\core();
$session = Services\session();
$sql = Services\sql();
$tokenSvc = Services\token();
$validate = Services\validate();
$vars = Services\vars();

$username = $validate->postedVar('username', dbescape: false);

if (strlen($username) == 0) {
    $core->put_cookie('xmbuser');  // Make sure user is logged out.
    $token = '';
    if ($core->schemaHasTokens()) {
        $token = $tokenSvc->create('Login', '', $vars::NONCE_FORM_EXP, anonymous: true);
        $session->preLogin($token);
    }
    ?>
    <form method="post" action="">
        <label>Username: <input type="text" name="username" /></label><br />
        <label>Password: <input type="password" name="password" /></label><br />
        <input type="hidden" name="token" value="<?= $token ?>" />
        <input type="submit" />
    </form>
    <?php
} else {
    if ($core->schemaHasSessions()) {
        // Already logged in by Session\Manager
        if (! X_SADMIN) {
            echo "This script may be run only by a Super Administrator.<br />Please <a href='" . $vars->full_url . "install/login.php'>Try Again</a>.<br />";
            throw new Exception('Upgrade login failure by '.$_SERVER['REMOTE_ADDR']);
        }
    } else {
        $password = md5($_POST['password']);
        if ($sql->checkUpgradeOldLogin($username, $password)) {
            $core->put_cookie('xmbuser', $username);
            $core->put_cookie('xmbpw', $password);
        } else {
            echo "This script may be run only by a Super Administrator.<br />Please <a href='" . $vars->full_url . "install/login.php'>Try Again</a>.<br />";
            throw new Exception('Upgrade login failure by '.$_SERVER['REMOTE_ADDR']);
        }
    }

    echo "Cookies set.  <a href='" . $vars->full_url . "install/'>Return to upgrade.</a>";
}
