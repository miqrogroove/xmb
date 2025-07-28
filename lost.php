<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-2
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

require './header.php';

$core = Services\core();
$session = Services\session();
$sql = Services\sql();
$template = Services\template();
$token = Services\token();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

$token1 = getPhpInput('a', sourcearray: 'g');
$token2 = getPhpInput('token');

$valid_get = preg_match('%^[a-f0-9]{32}$%', $token1) === 1;
$valid_post = preg_match('%^[a-f0-9]{32}$%', $token2) === 1;

if (X_MEMBER) {
    $page = $template->process('misc_feature_not_while_loggedin.php');
} elseif ($valid_get) {
    // Link from email received.
    $template->token = $token1;
    $page = $template->process('lost_pw_reset.php');
} elseif ($valid_post) {
    // New password from posted form received.
    $username = $validate->postedVar('username', dbescape: false);
    if ('' == $username) {
        $core->error($lang['textnousername']);
    }
    if (strlen($username) < $vars::USERNAME_MIN_LENGTH || strlen($username) > $vars::USERNAME_MAX_LENGTH) {
        $core->error($lang['username_length_invalid']);
    }

    $newPass = $core->assertPasswordPolicy('password1', 'password2');

    // Inputs look reasonable.  Check the token.
    if (! $token->consume($token2, 'Lost Password', $username)) {
        $core->error($lang['lostpw_bad_token']);
    }

    $passMan = new Password($sql);
    $passMan->changePassword($username, $newPass);
    unset($newPass, $passMan);

    $sql->deleteWhosonline($username);
    $session->logoutAll($username);

    $core->message($lang['lostpw_success']);
} else {
    $core->error($lang['lostpw_bad_token']);
}

$header = $template->process('header.php');
$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $page, $footer;
