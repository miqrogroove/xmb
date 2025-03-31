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

require './header.php';

$core = \XMB\Services\core();
$db = \XMB\Services\db();
$session = \XMB\Services\session();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$theme = \XMB\Services\theme();
$token = \XMB\Services\token();
$tran = \XMB\Services\translation();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;

$core->nav('<a href="./cp.php">'.$lang['textcp'].'</a>');
$core->nav($lang['texteditpro']);

$header = $template->process('header.php');

if (X_GUEST) {
    $core->redirect($vars->full_url . 'misc.php?action=login', timeout: 0);
    exit;
}

if (! X_SADMIN) {
    $core->error($lang['superadminonly']);
}

$rawuser = $core->postedVar('user', dbescape: false, sourcearray: 'g');
$member = $sql->getMemberByName($rawuser);

if (empty($member)) {
    $core->error($lang['nomember']);
}

$member['password'] = '';
$member['password2'] = '';

$https_only = 'on' == $vars->settings['images_https_only'];
$js_https_only = $https_only ? 'true' : 'false';

if (noSubmit('editsubmit')) {
    $form = new \XMB\UserEditForm($member, $vars->self, $core, $db, $sql, $theme, $tran, $vars);
    $form->setOptions();
    $form->setCallables();
    $form->setOptionalFields();
    $form->setBirthday();
    $form->setNumericFields();
    $form->setMiscFields();

    $subTemplate = $form->getTemplate();

    $subTemplate->custout = attrOut($member['customstatus']);

    $subTemplate->registerdate = gmdate($vars->dateformat, $core->timeKludge((int) $member['regdate']));

    if (0 == (int) $member['lastvisit']) {
        $subTemplate->lastlogdate = $lang['textpendinglogin'];
    } else {
        $lastvisitdate = gmdate($vars->dateformat, $core->timeKludge((int) $member['lastvisit']));
        $lastvisittime = gmdate($vars->timecode, $core->timeKludge((int) $member['lastvisit']));
        $subTemplate->lastlogdate = $lastvisitdate.' '.$lang['textat'].' '.$lastvisittime;
    }

    $subTemplate->loginfails = $member['bad_login_count'];
    if (0 == (int) $subTemplate->loginfails) {
        $subTemplate->loginfails = $lang['textnone'];
        $subTemplate->loginfaildate = $lang['textnone'];
    } else {
        $loginfaildate = gmdate($vars->dateformat, $core->timeKludge((int) $member['bad_login_date']));
        $loginfailtime = gmdate($vars->timecode, $core->timeKludge((int) $member['bad_login_date']));
        $subTemplate->loginfaildate = $loginfaildate.' '.$lang['textat'].' '.$loginfailtime;
    }

    $subTemplate->sessfails = $member['bad_session_count'];
    if (0 == (int) $subTemplate->sessfails) {
        $subTemplate->sessfails = $lang['textnone'];
        $subTemplate->sessfaildate = $lang['textnone'];
    } else {
        $sessfaildate = gmdate($vars->dateformat, $core->timeKludge((int) $member['bad_session_date']));
        $sessfailtime = gmdate($vars->timecode, $core->timeKludge((int) $member['bad_session_date']));
        $subTemplate->sessfaildate = $sessfaildate.' '.$lang['textat'].' '.$sessfailtime;
    }

    $guess_limit = 10;
    $lockout_timer = 3600 * 2;

    if ((int) $member['bad_login_count'] >= $guess_limit && time() < (int) $member['bad_login_date'] + $lockout_timer) {
        $subTemplate->loginfaildate .= "<br />\n{$lang['editprofile_lockout']} <input type='checkbox' name='unlock' value='yes' />";
    }

    $currdate = gmdate($vars->timecode, $vars->onlinetime + ($vars->settings['addtime'] * 3600));
    $subTemplate->textoffset = str_replace('$currdate', $currdate, $lang['evaloffset']);

    if ($vars->settings['sigbbcode'] == 'on') {
        $subTemplate->bbcodeis = $lang['texton'];
    } else {
        $subTemplate->bbcodeis = $lang['textoff'];
    }

    $subTemplate->htmlis = $lang['textoff'];

    $lang['searchusermsg'] = str_replace('*USER*', $member['username'], $lang['searchusermsg']);

    $subTemplate->email = $member['email'];
    $subTemplate->emailURL = recodeOut($member['email']);
    $subTemplate->regip = $member['regip'];
    $subTemplate->uid = $member['uid'];
    $subTemplate->username = $member['username'];
    $subTemplate->userrecode = recodeOut($member['username']);

    $subTemplate->token = $token->create('Edit User Account', $member['uid'], $vars::NONCE_FORM_EXP);
    $editpage = $subTemplate->process('admintool_editprofile.php');
} else {
    $core->request_secure('Edit User Account', $member['uid'], error_header: true);

    $form = new \XMB\UserEditForm($member, $vars->self, $core, $db, $sql, $theme, $tran, $vars);
    $form->readBirthday();
    $form->readOptionalFields();
    $form->readCallables();
    $form->readOptions();
    $form->readNumericFields();
    $form->readMiscFields();

    $edits = $form->getEdits();

    $email = $core->postedVar('newemail', 'javascript', dbescape: false, quoteencode: true);
    if ($member['email'] != $email) {
        $edits['email'] = $email;
    }

    $cusstatus = getPhpInput('cusstatus');
    if ($member['customstatus'] != $cusstatus) {
        $edits['customstatus'] = $cusstatus;
    }

    if (count($edits) > 0) {
        $sql->updateMember($member['username'], $edits);
    }

    if (getRawString('newpassword') != '') {
        $newPass = $core->assertPasswordPolicy('newpassword', 'newpassword');
        $passMan = new \XMB\Password($sql);
        $passMan->changePassword($rawuser, $newPass);
        unset($newPass, $passMan);

        // Force logout and delete cookies.
        $sql->deleteWhosonline($rawuser);
        $session->logoutAll($rawuser, isSelf: false);
    }

    $unlock = formYesNo('unlock');
    if ('yes' == $unlock) {
        $sql->unlockMember($rawuser);
    }

    $core->message($lang['adminprofilechange'], redirect: $full_url . 'admin/');
}

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $editpage, $footer;
