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

$core = \XMB\Services\core();
$db = \XMB\Services\db();
$forums = \XMB\Services\forums();
$session = \XMB\Services\session();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$theme = \XMB\Services\theme();
$token = \XMB\Services\token();
$tran = \XMB\Services\translation();
$validate = \XMB\Services\validate();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;
$SETTINGS = &$vars->settings;

header('X-Robots-Tag: noindex');

$buddys = [];
$favs = '';
$footer = '';
$header = '';
$mempage = '';
$https_only = 'on' == $SETTINGS['images_https_only'];
$template->js_https_only = $https_only ? 'true' : 'false';

$action = getPhpInput('action', 'g');
switch ($action) {
    case 'profile':
        $core->nav('<a href="' . $vars->full_url . 'memcp.php">'.$lang['textusercp'].'</a>');
        $core->nav($lang['texteditpro']);
        if ($SETTINGS['subject_in_title'] == 'on') {
            $template->threadSubject = $lang['texteditpro'] . ' - ';
        }
        break;
    case 'subscriptions':
        $core->nav('<a href="' . $vars->full_url . 'memcp.php">'.$lang['textusercp'].'</a>');
        $core->nav($lang['textsubscriptions']);
        if ($SETTINGS['subject_in_title'] == 'on') {
            $template->threadSubject = $lang['textsubscriptions'] . ' - ';
        }
        break;
    case 'favorites':
        $core->nav('<a href="' . $vars->full_url . 'memcp.php">'.$lang['textusercp'].'</a>');
        $core->nav($lang['textfavorites']);
        if ($SETTINGS['subject_in_title'] == 'on') {
            $template->threadSubject = $lang['textfavorites'] . ' - ';
        }
        break;
    case 'devices':
        $core->nav('<a href="' . $vars->full_url . 'memcp.php">'.$lang['textusercp'].'</a>');
        $core->nav($lang['devices']);
        if ($SETTINGS['subject_in_title'] == 'on') {
            $template->threadSubject = $lang['devices'] . ' - ';
        }
        break;
    default:
        $core->nav($lang['textusercp']);
        if ($SETTINGS['subject_in_title'] == 'on') {
            $template->threadSubject = $lang['textusercp'] . ' - ';
        }
        break;
}

$template->action = $action;

if (X_GUEST) {
    $core->redirect($vars->full_url . 'misc.php?action=login', timeout: 0);
    exit();
}

if ($action == 'profile') {
    $header = $template->process('header.php');
    $header .= $template->process('memcp_nav.php');

    if (noSubmit('editsubmit')) {
        $form = new \XMB\UserEditForm($vars->self, $vars->self, $core, $db, $sql, $theme, $tran, $validate, $vars);
        $form->setOptions();
        $form->setCallables();
        $form->setBirthday();
        $form->setNumericFields();
        $form->setMiscFields();
        
        if ('on' == $SETTINGS['regoptional'] || 'off' == $SETTINGS['quarantine_new_users'] || ((int) $vars->self['postnum'] > 0 && 'no' == $vars->self['waiting_for_mod']) || X_STAFF) {
            $form->setOptionalFields();
            $subTemplate = $form->getTemplate();
            $subTemplate->bbcodeis = $SETTINGS['sigbbcode'] == 'on' ? $lang['texton'] : $lang['textoff'];
            $subTemplate->htmlis = $lang['textoff'];
            $subTemplate->optional = $subTemplate->process('memcp_profile_optional.php');
        } else {
            $subTemplate = $form->getTemplate();
            $subTemplate->optional = '';
        }

        $currdate = gmdate($vars->timecode, $core->standardTime($vars->onlinetime));
        $subTemplate->textoffset = str_replace('$currdate', $currdate, $lang['evaloffset']);

        $subTemplate->hUsername = $vars->self['username'];
        $subTemplate->email = $vars->self['email'];
        $subTemplate->token = $token->create('User Control Panel/Edit Profile', $vars->self['uid'], $vars::NONCE_FORM_EXP);

        $mempage = $subTemplate->process('memcp_profile.php');
    }

    if (onSubmit('editsubmit')) {
        $core->request_secure('User Control Panel/Edit Profile', $vars->self['uid'], error_header: true);

        if (getRawString('newpassword') != '') {
            $storedPass = $vars->self['password'] !== '' ? $vars->self['password'] : $vars->self['password2'];
            $passMan = new \XMB\Password($sql);
            $oldPass = getRawString('oldpassword');
            if ($oldPass == '') {
                $core->error($lang['textnopassword']);
            }
            if (! $passMan->checkInput($oldPass, $storedPass)) {
                $core->auditBadLogin($vars->self);
                $core->error($lang['textpwincorrect']);
            }
            $newPass = $core->assertPasswordPolicy('newpassword', 'newpasswordcf');
            $passMan->changePassword($vars->self['username'], $newPass);
            unset($newPass, $passMan, $oldPass, $storedPass);

            // Force logout and delete cookies.
            $sql->deleteWhosonline($vars->self['username']);
            $session->logoutAll();
        }

        $form = new \XMB\UserEditForm($vars->self, $vars->self, $core, $db, $sql, $theme, $tran, $validate, $vars);
        $form->readBirthday();
        $form->readCallables();
        $form->readOptionalFields();
        $form->readOptions();
        $form->readNumericFields();
        $form->readMiscFields();

        $edits = $form->getEdits();

        $email = $validate->postedVar('newemail', 'javascript', dbescape: false, quoteencode: true);

        if ($email !== $vars->self['email']) {
            if ($SETTINGS['doublee'] == 'off' && false !== strpos($email, "@")) {
                $sqlEmail = $db->escape($email);
                $query = $db->query("SELECT COUNT(uid) FROM " . $vars->tablepre . "members WHERE email = '$sqlEmail' AND username != '" . $vars->xmbuser . "'");
                $count1 = (int) $db->result($query);
                $db->free_result($query);
                if ($count1 != 0) {
                    $core->error($lang['alreadyreg']);
                }
            }

            $efail = false;
            $query = $db->query("SELECT * FROM " . $vars->tablepre . "restricted");
            while ($restriction = $db->fetch_array($query)) {
                $t_email = $email;
                if ('0' === $restriction['case_sensitivity']) {
                    $t_email = strtolower($t_email);
                    $restriction['name'] = strtolower($restriction['name']);
                }

                if ('1' === $restriction['partial']) {
                    if (strpos($t_email, $restriction['name']) !== false) {
                        $efail = true;
                    }
                } else {
                    if ($t_email === $restriction['name']) {
                        $efail = true;
                    }
                }
            }
            $db->free_result($query);

            if ($efail) {
                $core->error($lang['emailrestricted']);
            }

            require XMB_ROOT . 'include/validate-email.inc.php';
            $test = new EmailAddressValidator();
            $rawemail = getPhpInput('newemail');
            if (false === $test->check_email_address($rawemail)) {
                $core->error($lang['bademail']);
            }
        }
        
        if ($vars->self['email'] != $email) {
            $edits['email'] = $email;
        }
        
        if (count($edits) > 0) {
            $sql->updateMember($vars->self['username'], $edits);
        }

        $core->message($lang['usercpeditpromsg'], redirect: $vars->full_url . 'memcp.php');
    }
} elseif ($action == 'favorites') {
    $header = $template->process('header.php');
    $header .= $template->process('memcp_nav.php');

    $favadd = onSubmit('favadd');
    if (noSubmit('favsubmit') && $favadd) {
        $favadd = getInt('favadd');

        $row = $sql->getFIDfromTID($favadd);
        if (count($row) == 0) {
            $core->error($lang['privforummsg']);
        }
        $forum = $forums->getForum((int) $row['fid']);
        $perms = $core->checkForumPermissions($forum);
        if (! ($perms[$vars::PERMS_VIEW] && $perms[$vars::PERMS_PASSWORD])) {
            $core->error($lang['privforummsg']);
        }
        if ($forum['type'] == 'sub') {
            $perms = $core->checkForumPermissions($forums->getForum((int) $forum['fup']));
            if (! ($perms[$vars::PERMS_VIEW] && $perms[$vars::PERMS_PASSWORD])) {
                $core->error($lang['privforummsg']);
            }
        }

        $sql->addFavoriteIfMissing($favadd, $vars->self['username'], 'favorite');

        $core->message($lang['favaddedmsg'], redirect: $vars->full_url . 'memcp.php?action=favorites');
    } elseif (! $favadd && noSubmit('favsubmit')) {
        $favnum = 0;
        $template->favs = '';
        $fids = $core->permittedFIDsForThreadView();
        if (count($fids) != 0) {
            $query = $sql->getFavorites($vars->self['username'], $fids, limit: null);
            foreach ($query as $fav) {
                $forum = $forums->getForum((int) $fav['fid']);
                $forum['name'] = fnameOut($forum['name']);

                $lastpost = explode('|', $fav['lastpost']);

                // Translate "Anonymous" author.
                $lastpostname = trim($lastpost[1]);
                if ('Anonymous' == $lastpostname) {
                    $lastpostname = $lang['textanonymous'];
                }

                $lastreplydate = gmdate($vars->dateformat, $core->timeKludge((int) $lastpost[0]));
                $lastreplytime = gmdate($vars->timecode, $core->timeKludge((int) $lastpost[0]));
                $template->lastpost = $lang['lastreply1'].' '.$lastreplydate.' '.$lang['textat'].' '.$lastreplytime.' '.$lang['textby'].' '.$lastpostname;
                $fav['subject'] = $core->rawHTMLsubject(stripslashes($fav['subject']));

                if ($fav['icon'] != '') {
                    $fav['icon'] = '<img src="' . $vars->full_url . $smdir . '/' . $fav['icon'] . '" alt="" border="0" />';
                } else {
                    $fav['icon'] = '';
                }
                $template->fav = $fav;
                $template->forum = $forum;

                $favnum++;
                $template->favs .= $template->process('memcp_favs_row.php');
            }
            unset($query);
        }

        if ($favnum != 0) {
            $template->favsbtn = $template->process('memcp_favs_button.php');
        } else {
            $template->favsbtn = '';
            $template->favs = $template->process('memcp_favs_none.php');
        }

        $mempage = $template->process('memcp_favs.php');
    } elseif (! $favadd && onSubmit('favsubmit')) {
        $query = $db->query("SELECT tid FROM " . $vars->tablepre . "favorites WHERE username = '" . $vars->xmbuser . "' AND type = 'favorite'");
        $tids = [];
        while ($fav = $db->fetch_array($query)) {
            $delete = formInt('delete'.$fav['tid']);
            if ($delete == intval($fav['tid'])) {
                $tids[] = $delete;
            }
        }
        $db->free_result($query);
        if (count($tids) > 0) {
            $sql->deleteFavorites($tids, $vars->self['username'], 'favorite');
        }
        $core->message($lang['favsdeletedmsg'], redirect: $vars->full_url . 'memcp.php?action=favorites');
    }
} elseif ($action == 'subscriptions') {
    $subadd = onSubmit('subadd');
    if (! $subadd && noSubmit('subsubmit')) {
        $fids = $core->permittedFIDsForThreadView();
        $num = $sql->countSubscriptionsByUser($vars->self['username'], $fids);
        $mpage = $core->multipage($num, $vars->tpp, $vars->full_url . 'memcp.php?action=subscriptions');
        $template->multipage = $mpage['html'];
        if (strlen($mpage['html']) != 0) {
            $template->multipage = $template->process('memcp_subscriptions_multipage.php');
        }

        $header = $template->process('header.php');
        $header .= $template->process('memcp_nav.php');

        $query = $sql->getSubscriptions($vars->self['username'], $fids, $mpage['start'], $vars->tpp);

        $template->subscriptions = '';
        foreach ($query as $fav) {
            $forum = $forums->getForum((int) $fav['fid']);
            $forum['name'] = fnameOut($forum['name']);

            $lastpost = explode('|', $fav['lastpost']);

            // Translate "Anonymous" author.
            $lastpostname = trim($lastpost[1]);
            if ('Anonymous' == $lastpostname) {
                $lastpostname = $lang['textanonymous'];
            }

            $lastreplydate = gmdate($vars->dateformat, $core->timeKludge((int) $lastpost[0]));
            $lastreplytime = gmdate($vars->timecode, $core->timeKludge((int) $lastpost[0]));
            $template->lastpost = $lang['lastreply1'].' '.$lastreplydate.' '.$lang['textat'].' '.$lastreplytime.' '.$lang['textby'].' '.$lastpostname;
            $fav['subject'] = $core->rawHTMLsubject(stripslashes($fav['subject']));

            if ($fav['icon'] != '') {
                $fav['icon'] = '<img src="' . $vars->full_url . $smdir . '/' . $fav['icon'] . '" alt="" border="0" />';
            } else {
                $fav['icon'] = '';
            }
            $template->fav = $fav;
            $template->forum = $forum;
            $template->subscriptions .= $template->process('memcp_subscriptions_row.php');
        }

        if (count($query) == 0) {
            $template->subscriptions = $template->process('memcp_subscriptions_none.php');
            $template->subsbtn = '';
        } else {
            $template->subsbtn = $template->process('memcp_subscriptions_button.php');
        }

        $mempage = $template->process('memcp_subscriptions.php');
    } elseif ($subadd && noSubmit('subsubmit')) {
        $tid = getInt('subadd');

        $row = $sql->getFIDfromTID($tid);
        if (count($row) == 0) {
            $core->error($lang['privforummsg']);
        }
        $forum = $forums->getForum((int) $row['fid']);
        $perms = $core->checkForumPermissions($forum);
        if (! ($perms[$vars::PERMS_VIEW] && $perms[$vars::PERMS_PASSWORD])) {
            $core->error($lang['privforummsg']);
        }
        if ($forum['type'] == 'sub') {
            $perms = $core->checkForumPermissions($forums->getForum((int) $forum['fup']));
            if (! ($perms[$vars::PERMS_VIEW] && $perms[$vars::PERMS_PASSWORD])) {
                $core->error($lang['privforummsg']);
            }
        }

        $sql->addFavoriteIfMissing($tid, $vars->self['username'], 'subscription');
        $core->message($lang['subaddedmsg'], redirect: $vars->full_url . 'memcp.php?action=subscriptions');
    } elseif (! $subadd && onSubmit('subsubmit')) {
        $query = $db->query("SELECT tid FROM " . $vars->tablepre . "favorites WHERE username = '" . $vars->xmbuser . "' AND type = 'subscription'");
        $tids = [];
        while ($sub = $db->fetch_array($query)) {
            $delete = formInt('delete' . $sub['tid']);
            if ($delete == intval($sub['tid'])) {
                $tids[] = $delete;
            }
        }
        $db->free_result($query);
        if (count($tids) > 0) {
            $sql->deleteFavorites($tids, $vars->self['username'], 'subscription');
        }
        $core->message($lang['subsdeletedmsg'], redirect: $vars->full_url . 'memcp.php?action=subscriptions');
    }
} elseif ($action == 'devices') {
    if (onSubmit('devicesubmit')) {
        $ids = [];
        foreach ($_POST as $name => $value) {
            if (substr($name, 0, 6) == 'delete' && strlen($value) == 4 && $name == "delete$value") {
                $ids[] = $value;
            }
        }
        if (! empty($ids)) {
            // This page only handles the default session mechanism for now.
            $lists = [\XMB\Session\FormsAndCookies::class => $ids];
            $session->logoutByLists($lists);
        }
    }

    $header = $template->process('header.php');
    $header .= $template->process('memcp_nav.php');
    $template->current = '';
    $template->other = '';

    $lists = $session->getSessionLists();
    foreach ($lists as $name => $list) {
        if ($name != \XMB\Session\FormsAndCookies::class) {
            // This page only handles the default session mechanism for now.
            continue;
        }
        foreach ($list as $device) {
            $template->did = $device['token'];
            $time = $core->timeKludge((int) $device['login_date']);
            $template->dlogin = gmdate($vars->dateformat, $time).' '.$lang['textat'].' '.gmdate($vars->timecode, $time);
            $template->dagent = parse_user_agent($device['agent']);
            if ($device['current']) {
                $template->current .= $template->process('memcp_devices_firstrow.php');
            } else {
                $template->other .= $template->process('memcp_devices_row.php');
            }
        }
    }
    
    if ('' == $template->other) {
        $template->devicesbtn = '';
    } else {
        $template->devicesbtn = $template->process('memcp_devices_button.php');
    }
    
    $mempage = $template->process('memcp_devices.php');
} else {
    $buddy = new \XMB\BuddyManager($core, $db, $sql, $template, $vars);

    $header = $template->process('header.php');
    $template->usercpwelcome = str_replace('$xmbuser', $vars->self['username'], $lang['evalusercpwelcome']);
    $header .= $template->process('memcp_nav.php');

    $template->buddys = $buddy->list();

    $member = $vars->self;
    null_string($member['avatar']);

    if ($https_only && strpos($member['avatar'], ':') !== false && substr($member['avatar'], 0, 6) !== 'https:') {
        $member['avatar'] = '';
    }

    if ($member['avatar'] !== '') {
        $member['avatar'] = '<img src="'.$member['avatar'].'" border="0" alt="'.$lang['altavatar'].'" />';
    }

    if ($member['mood'] !== '') {
        $member['mood'] = $core->postify(
            message: $member['mood'],
            allowimgcode: 'no',
            ignorespaces: true,
            ismood: 'yes',
        );
    }

    $u2uquery = $sql->getU2UInbox($vars->self['username']);
    $template->messages = '';
    foreach ($u2uquery as $message) {
        $postdate = gmdate($vars->dateformat, $core->timeKludge((int) $message['dateline']));
        $posttime = gmdate($vars->timecode, $core->timeKludge((int) $message['dateline']));
        $template->senton = $postdate.' '.$lang['textat'].' '.$posttime;

        $message['subject'] = $core->rawHTMLsubject(stripslashes($message['subject']));
        if ($message['subject'] == '') {
            $message['subject'] = '&laquo;'.$lang['textnosub'].'&raquo;';
        }

        if ($message['readstatus'] == 'yes') {
            $template->read = $lang['textread'];
        } else {
            $template->read = $lang['textunread'];
        }
        $template->message = $message;
        $template->messages .= $template->process('memcp_home_u2u_row.php');
    }

    if (count($u2uquery) == 0) {
        $template->messages = $template->process('memcp_home_u2u_none.php');
    }
    unset($u2uquery);

    $favnum = 0;
    $favs = '';
    $fids = $core->permittedFIDsForThreadView();
    if (count($fids) != 0) {
        $query2 = $sql->getFavorites($vars->self['username'], $fids, limit: 5);
        $favnum = count($query2);
        foreach ($query2 as $fav) {
            $forum = $forums->getForum((int) $fav['fid']);
            $forum['name'] = fnameOut($forum['name']);

            $lastpost = explode('|', $fav['lastpost']);

            // Translate "Anonymous" author.
            $lastpostname = trim($lastpost[1]);
            if ('Anonymous' == $lastpostname) {
                $lastpostname = $lang['textanonymous'];
            }

            $lastreplydate = gmdate($vars->dateformat, $core->timeKludge((int) $lastpost[0]));
            $lastreplytime = gmdate($vars->timecode, $core->timeKludge((int) $lastpost[0]));
            $template->lastpost = $lang['lastreply1'].' '.$lastreplydate.' '.$lang['textat'].' '.$lastreplytime.' '.$lang['textby'].' '.$lastpostname;
            $fav['subject'] = $core->rawHTMLsubject(stripslashes($fav['subject']));

            if ($fav['icon'] != '') {
                $fav['icon'] = '<img src="' . $vars->full_url . $smdir . '/' . $fav['icon'] . '" alt="" border="0" />';
            } else {
                $fav['icon'] = '';
            }
            $template->fav = $fav;
            $template->forum = $forum;
            $favs .= $template->process('memcp_home_favs_row.php');
        }
        unset($query2);
    }

    if ($favnum == 0) {
        $favs = $template->process('memcp_home_favs_none.php');
    }
    $template->favs = $favs;
    $template->member = $member;
    $template->hUsername = $vars->self['username'];
    $template->hStatus = $vars->self['status'];
    $mempage = $template->process('memcp_home.php');
}

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $mempage, $footer;
