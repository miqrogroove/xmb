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

require './header.php';

$core = Services\core();
$db = Services\db();
$session = Services\session();
$sql = Services\sql();
$template = Services\template();
$theme = Services\theme();
$token = Services\token();
$tran = Services\translation();
$validate = Services\validate();
$vars = Services\vars();
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

$rawuser = $validate->postedVar('user', dbescape: false, sourcearray: 'g');
$member = $sql->getMemberByName($rawuser);

if (empty($member)) {
    $core->error($lang['nomember']);
}

$https_only = 'on' == $vars->settings['images_https_only'];
$js_https_only = $https_only ? 'true' : 'false';

if (noSubmit('editsubmit')) {
    $form = new UserEditForm($member, $vars->self, $core, $db, $sql, $theme, $tran, $validate, $vars);
    $form->setOptions();
    $form->setCallables();
    $form->setOptionalFields();
    $form->setBirthday();
    $form->setNumericFields();
    $form->setMiscFields();

    $subTemplate = $form->getTemplate();

    $subTemplate->custout = $member['customstatus'];

    $subTemplate->registerdate = $core->printGmDate($core->timeKludge((int) $member['regdate']));

    if (0 == (int) $member['lastvisit']) {
        $subTemplate->lastlogdate = $lang['textpendinglogin'];
    } else {
        $adjStamp = $core->timeKludge((int) $member['lastvisit']);
        $lastvisitdate = $core->printGmDate($adjStamp);
        $lastvisittime = gmdate($vars->timecode, $adjStamp);
        $subTemplate->lastlogdate = $lastvisitdate.' '.$lang['textat'].' '.$lastvisittime;
    }

    $subTemplate->loginfails = $member['bad_login_count'];
    if (0 == (int) $subTemplate->loginfails) {
        $subTemplate->loginfails = $lang['textnone'];
        $subTemplate->loginfaildate = $lang['textnone'];
    } else {
        $adjStamp = $core->timeKludge((int) $member['bad_login_date']);
        $loginfaildate = $core->printGmDate($adjStamp);
        $loginfailtime = gmdate($vars->timecode, $adjStamp);
        $subTemplate->loginfaildate = $loginfaildate.' '.$lang['textat'].' '.$loginfailtime;
    }

    $subTemplate->sessfails = $member['bad_session_count'];
    if (0 == (int) $subTemplate->sessfails) {
        $subTemplate->sessfails = $lang['textnone'];
        $subTemplate->sessfaildate = $lang['textnone'];
    } else {
        $adjStamp = $core->timeKludge((int) $member['bad_session_date']);
        $sessfaildate = $core->printGmDate($adjStamp);
        $sessfailtime = gmdate($vars->timecode, $adjStamp);
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

    $url = $vars->full_url . 'search.php?srchuname=' . recodeOut($member['username']) . '&amp;searchsubmit=a';
    $subTemplate->postSearchLink = str_replace('$url', $url, $lang['searchusermsg']);

    $subTemplate->email = $member['email'];
    $subTemplate->emailURL = recodeOut($member['email']);
    $subTemplate->regip = $member['regip'];
    $subTemplate->uid = $member['uid'];
    $subTemplate->username = $member['username'];
    $subTemplate->userrecode = recodeOut($member['username']);
    $subTemplate->userStatus = $core->userStatusControl('status', $member['status']);

    $subTemplate->token = $token->create('Edit User Account', $member['uid'], $vars::NONCE_FORM_EXP);
    $editpage = $subTemplate->process('admintool_editprofile.php');
} else {
    $core->request_secure('Edit User Account', $member['uid']);

    $form = new UserEditForm($member, $vars->self, $core, $db, $sql, $theme, $tran, $validate, $vars);
    $form->readBirthday();
    $form->readOptionalFields();
    $form->readCallables();
    $form->readOptions();
    $form->readNumericFields();
    $form->readMiscFields();

    $edits = $form->getEdits();

    $email = $validate->postedVar('newemail', dbescape: false);
    if ($member['email'] != $email) {
        $edits['email'] = $email;
    }

    $cusstatus = $validate->postedVar('cusstatus', dbescape: false);
    if ($member['customstatus'] != $cusstatus) {
        $edits['customstatus'] = $cusstatus;
    }

    $status = getPhpInput('status');
    if ($member['status'] != $status) {
        if ($member['status'] == 'Super Administrator') {
            $count = $sql->countSuperAdmins();
            if ($count == 1) {
                $core->error($lang['lastsadmin']);
            }
        }
        if ($status == '') {
            $status = 'Member';
        }
        $list = array_keys($vars->status_enum);
        if (in_array($status, $list)) {
            $edits['status'] = $status;
        }
    }

    if (count($edits) > 0) {
        $sql->updateMember((int) $member['uid'], $edits);
    }

    if (getRawString('newpassword') != '') {
        $newPass = $core->assertPasswordPolicy('newpassword', 'newpassword');
        $passMan = new Password($sql);
        $passMan->changePassword($rawuser, $newPass);
        unset($newPass, $passMan);

        // Force logout and delete cookies.
        $sql->deleteWhosonline($rawuser);
        $session->logoutAll($rawuser, isSelf: false);
    }

    $unlock = formYesNo('unlock');
    if ('yes' == $unlock) {
        $sql->unlockMember((int) $member['uid']);
    }

    $core->message($lang['adminprofilechange'], redirect: $vars->full_url . 'admin/');
}

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $editpage, $footer;
