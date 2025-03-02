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

$core = \XMB\Services\core();
$db = \XMB\Services\db();
$session = \XMB\Services\session();
$sql = \XMB\Services\sql();
$theme = \XMB\Services\theme();
$tran = \XMB\Services\translation();
$vars = \XMB\Services\vars();

define('X_SCRIPT', 'editprofile.php');

require 'header.php';

loadtemplates(
'memcp_profile_avatarurl',
'memcp_profile_avatarlist',
'admintool_editprofile'
);

nav('<a href="./cp.php">'.$lang['textcp'].'</a>');
nav($lang['texteditpro']);

eval('$header = "'.template('header').'";');

if (X_GUEST) {
    redirect("{$full_url}misc.php?action=login", 0);
    exit;
}

if (!X_SADMIN) {
    error($lang['superadminonly']);
}

$rawuser = postedVar('user', '', TRUE, FALSE, FALSE, 'g');
$member = $sql->getMemberByName($rawuser);

if (empty($member)) {
    error($lang['nomember']);
}

$member['password'] = '';

$user = $db->escape($rawuser);

$https_only = 'on' == $SETTINGS['images_https_only'];
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

    $custout = attrOut($member['customstatus']);

    $registerdate = gmdate($vars->dateformat, $core->timeKludge((int) $member['regdate']));

    if (0 == (int) $member['lastvisit']) {
        $lastlogdate = $lang['textpendinglogin'];
    } else {
        $lastvisitdate = gmdate($vars->dateformat, $core->timeKludge((int) $member['lastvisit']));
        $lastvisittime = gmdate($vars->timecode, $core->timeKludge((int) $member['lastvisit']));
        $lastlogdate = $lastvisitdate.' '.$lang['textat'].' '.$lastvisittime;
    }

    $loginfails = $member['bad_login_count'];
    if (0 == (int) $loginfails) {
        $loginfails = $lang['textnone'];
        $loginfaildate = $lang['textnone'];
    } else {
        $loginfaildate = gmdate($vars->dateformat, $core->timeKludge((int) $member['bad_login_date']));
        $loginfailtime = gmdate($vars->timecode, $core->timeKludge((int) $member['bad_login_date']));
        $loginfaildate = $loginfaildate.' '.$lang['textat'].' '.$loginfailtime;
    }

    $sessfails = $member['bad_session_count'];
    if (0 == (int) $sessfails) {
        $sessfails = $lang['textnone'];
        $sessfaildate = $lang['textnone'];
    } else {
        $sessfaildate = gmdate($vars->dateformat, $core->timeKludge((int) $member['bad_session_date']));
        $sessfailtime = gmdate($vars->timecode, $core->timeKludge((int) $member['bad_session_date']));
        $sessfaildate = $sessfaildate.' '.$lang['textat'].' '.$sessfailtime;
    }

    $guess_limit = 10;
    $lockout_timer = 3600 * 2;
    
    if ((int) $member['bad_login_count'] >= $guess_limit && time() < (int) $member['bad_login_date'] + $lockout_timer) {
        $loginfaildate .= "<br />\n{$lang['editprofile_lockout']} <input type='checkbox' name='unlock' value='yes' />";
    }

    $currdate = gmdate($vars->timecode, $vars->onlinetime + ($SETTINGS['addtime'] * 3600));
    $textoffset = str_replace('$currdate', $currdate, $lang['evaloffset']);

    if ($SETTINGS['sigbbcode'] == 'on') {
        $bbcodeis = $lang['texton'];
    } else {
        $bbcodeis = $lang['textoff'];
    }

    $htmlis = $lang['textoff'];

    $lang['searchusermsg'] = str_replace('*USER*', $member['username'], $lang['searchusermsg']);

    $subTemplate->email = $member['email'];
    $subTemplate->emailURL = recodeOut($member['email']);
    $subTemplate->regip = $member['regip'];
    $subTemplate->uid = $member['uid'];
    $subTemplate->username = $member['username'];
    $subTemplate->userrecode = recodeOut($member['username']);

    $template = template_secure('admintool_editprofile', 'Edit User Account', $member['uid'], $vars::NONCE_FORM_EXP);
    eval('$editpage = "'.$template.'";');
} else {
    request_secure('Edit User Account', $member['uid']);

    $form = new \XMB\UserEditForm($member, $vars->self, $core, $db, $sql, $theme, $tran, $vars);
    $form->readBirthday();
    $form->readOptionalFields();
    $form->readCallables();
    $form->readOptions();
    $form->readNumericFields();
    $form->readMiscFields();

    $cusstatus = postedVar('cusstatus', '', FALSE);

    $email = postedVar('newemail', 'javascript', TRUE, TRUE, TRUE);

    $db->query("UPDATE ".X_PREFIX."members SET status='$status', customstatus='$cusstatus', email='$email', site='$site',
    location='$location', bio='$bio', sig='$sig', showemail='$showemail', timeoffset='$timeoffset1', avatar='$avatar',
    theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew', newsletter='$newsletter', timeformat='$timeformatnew',
    dateformat='$dateformatnew', mood='$mood', invisible='$invisible', saveogu2u='$saveogu2u', emailonu2u='$emailonu2u',
    useoldu2u='$useoldu2u', u2ualert=$u2ualert, sub_each_post='$newsubs' WHERE username='$user'");

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

    message($lang['adminprofilechange'], TRUE, '', '', $full_url.'cp.php', true, false, true);
}

end_time();
eval('$footer = "'.template('footer').'";');
echo $header, $editpage, $footer;
