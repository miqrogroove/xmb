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

use function XMB\Services\sql;

define('X_SCRIPT', 'lost.php');

require 'header.php';

loadtemplates(
'lost_pw_reset',
'misc_feature_not_while_loggedin'
);

$token1 = postedVar('a', '', false, false, false, 'g');
$token2 = postedVar('token', '', false, false, false, 'p');

$valid_get = preg_match('%^[a-f0-9]{32}$%', $token1) === 1;
$valid_post = preg_match('%^[a-f0-9]{32}$%', $token2) === 1;

if (X_MEMBER) {
    eval('$page = "'.template('misc_feature_not_while_loggedin').'";');

} elseif ($valid_get) {
    // Link from email received.
    $token = $token1;
    eval('$page = "'.template('lost_pw_reset').'";');

} elseif ($valid_post) {
    // New password from posted form received.
    $username = postedVar('username', '', true, false);
    if ('' == $username) {
        error($lang['textnousername']);
    }
    if (strlen($username) < $vars::USERNAME_MIN_LENGTH || strlen($username) > $vars::USERNAME_MAX_LENGTH) {
        error($lang['username_length_invalid']);
    }

    $newPass = $core->assertPasswordPolicy('password1', 'password2');

    // Inputs look reasonable.  Check the token.
    if (! \XMB\Token\consume($token2, 'Lost Password', $username)) {
        error($lang['lostpw_bad_token']);
    }

    $passMan = new \XMB\Password($sql);
    $passMan->changePassword($username, $newPass);
    unset($newPass, $passMan);

    $sql->deleteWhosonline($username);
    $session->logoutAll($username);

    message($lang['lostpw_success']);

} else {
    error($lang['lostpw_bad_token']);
}

eval('$header = "'.template('header').'";');
end_time();
eval('$footer = "'.template('footer').'";');
echo $header, $page, $footer;
