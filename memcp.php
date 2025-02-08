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
$forums = \XMB\Services\forums();
$session = \XMB\Services\session();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$theme = \XMB\Services\theme();
$tran = \XMB\Services\translation();
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
        break;
    case 'subscriptions':
        $core->nav('<a href="' . $vars->full_url . 'memcp.php">'.$lang['textusercp'].'</a>');
        $core->nav($lang['textsubscriptions']);
        break;
    case 'favorites':
        $core->nav('<a href="' . $vars->full_url . 'memcp.php">'.$lang['textusercp'].'</a>');
        $core->nav($lang['textfavorites']);
        break;
    case 'devices':
        $core->nav('<a href="' . $vars->full_url . 'memcp.php">'.$lang['textusercp'].'</a>');
        $core->nav($lang['devices']);
        break;
    default:
        $core->nav($lang['textusercp']);
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
        $member = $vars->self;

        $template->checked = $member['showemail'] == 'yes' ? $vars::cheHTML : '';
        $template->subschecked = $member['sub_each_post'] == 'yes' ? $vars::cheHTML : '';
        $template->newschecked = $member['newsletter'] == 'yes' ? $vars::cheHTML : '';
        $template->uou2uchecked = $member['useoldu2u'] == 'yes' ? $vars::cheHTML : '';
        $template->ogu2uchecked = $member['saveogu2u'] == 'yes' ? $vars::cheHTML : '';
        $template->eouchecked = $member['emailonu2u'] == 'yes' ? $vars::cheHTML : '';
        $template->invchecked = $member['invisible'] === '1' ? $vars::cheHTML : '';

        $currdate = gmdate($vars->timecode, $core->standardTime($vars->onlinetime));
        $template->textoffset = str_replace('$currdate', $currdate, $lang['evaloffset']);

        $template->timezones = $core->timezone_control($member['timeoffset']);

        $template->u2uasel0 = '';
        $template->u2uasel1 = '';
        $template->u2uasel2 = '';
        switch ($member['u2ualert']) {
            case '2':
                $template->u2uasel2 = $vars::selHTML;
                break;
            case '1':
                $template->u2uasel1 = $vars::selHTML;
                break;
            case '0':
            default:
                $template->u2uasel0 = $vars::selHTML;
                break;
        }

        $template->themelist = $theme->selector(
            nameAttr: 'thememem',
            selection: (int) $member['theme'],
        );

        $template->langfileselect = $tran->createLangFileSelect($member['langfile']);

        $day = intval(substr($member['bday'], 8, 2));
        $month = intval(substr($member['bday'], 5, 2));
        $template->year = substr($member['bday'], 0, 4);

        $sel = array_fill(start_index: 0, count: 13, value: '');
        $sel[$month] = $vars::selHTML;
        $template->sel = $sel;

        $template->dayselect = [
            "<select name='day'>",
            "<option value=''>&nbsp;</option>",
        ];
        for ($num = 1; $num <= 31; $num++) {
            $selected = $day == $num ? $vars::selHTML : '';
            $dayselect[] = "<option value='$num' $selected>$num</option>";
        }
        $dayselect[] = '</select>';
        $template->dayselect = implode("\n", $dayselect);

        $template->check12 = '';
        $template->check24 = '';
        if ('24' === $member['timeformat']) {
            $template->check24 = $vars::cheHTML;
        } else {
            $template->check12 = $vars::cheHTML;
        }

        if ($SETTINGS['sigbbcode'] == 'on') {
            $template->bbcodeis = $lang['texton'];
        } else {
            $template->bbcodeis = $lang['textoff'];
        }

        $template->htmlis = $lang['textoff'];

        null_string($member['avatar']);
        if ($SETTINGS['avastatus'] == 'on') {
            if ($https_only && strpos($member['avatar'], ':') !== false && substr($member['avatar'], 0, 6) !== 'https:') {
                $member['avatar'] = '';
            }
            $template->member = $member;
            $template->avatar = $template->process('memcp_profile_avatarurl.php');
        } elseif ($SETTINGS['avastatus'] == 'list')  {
            $avatars = '<option value="" />'.$lang['textnone'].'</option>';
            $dir1 = opendir(XMB_ROOT . 'images/avatars');
            while ($avFile = readdir($dir1)) {
                if (is_file(XMB_ROOT . 'images/avatars/' . $avFile) && $avFile != '.' && $avFile != '..' && $avFile != 'index.html') {
                    $avatars .= '<option value="' . $vars->full_url . 'images/avatars/' . $avFile . '" />' . $avFile . '</option>';
                }
            }
            closedir($dir1);
            $avatars = str_replace('value="'.$member['avatar'].'"', 'value="'.$member['avatar'].'" selected="selected"', $avatars);
            $template->avatarbox = '<select name="newavatar" onchange="document.images.avatarpic.src=this[this.selectedIndex].value;">'.$avatars.'</select>';
            $template->avatar = $template->process('memcp_profile_avatarlist.php');
            unset($avatars, $template->avatarbox);
        } else {
            $template->avatar = '';
        }

        $member['bio'] = $core->rawHTMLsubject($member['bio']);
        $member['location'] = $core->rawHTMLsubject($member['location']);
        $member['mood'] = $core->rawHTMLsubject($member['mood']);
        $member['sig'] = $core->rawHTMLsubject($member['sig']);

        $template->member = $member;
        if ('on' == $SETTINGS['regoptional'] || 'off' == $SETTINGS['quarantine_new_users'] || ((int) $vars->self['postnum'] > 0 && 'no' == $vars->self['waiting_for_mod']) || X_STAFF) {
            $template->optional = $template->process('memcp_profile_optional.php');
        } else {
            $template->optional = '';
        }

        $template->hUsername = $vars->self['username'];
        $template->token = $token->create('User Control Panel/Edit Profile', $vars->self['uid'], $vars::NONCE_FORM_EXP);

        $mempage = $template->process('memcp_profile.php');
    }

    if (onSubmit('editsubmit')) {
        $core->request_secure('User Control Panel/Edit Profile', $vars->self['uid'], error_header: true);
        if (! empty($_POST['newpassword'])) {
            if (empty($_POST['oldpassword'])) {
                error($lang['textpwincorrect']);
            }
            $member = $sql->getMemberByName($vars->self['username']);
            if ($member['password'] !== md5($_POST['oldpassword'])) {
                error($lang['textpwincorrect']);
            }
            unset($member);
            if (empty($_POST['newpasswordcf'])) {
                error($lang['pwnomatch']);
            }
            if ($_POST['newpassword'] !== $_POST['newpasswordcf']) {
                error($lang['pwnomatch']);
            }

            $newpassword = md5($_POST['newpassword']);

            $pwtxt = "password='$newpassword',";

            // Force logout and delete cookies.
            $query = $db->query("DELETE FROM " . $vars->tablepre . "whosonline WHERE username='$xmbuser'");
            $session->logoutAll();
        } else {
            $pwtxt = '';
        }

        $langfilenew = postedVar('langfilenew');
        $result = $db->query("SELECT devname FROM " . $vars->tablepre . "lang_base WHERE devname='$langfilenew'");
        if ($db->num_rows($result) == 0) {
            $langfilenew = $SETTINGS['langfile'];
        }

        $timeoffset1 = isset($_POST['timeoffset1']) && is_numeric($_POST['timeoffset1']) ? $_POST['timeoffset1'] : 0;
        $thememem = formInt('thememem');
        $tppnew = isset($_POST['tppnew']) ? (int) $_POST['tppnew'] : $SETTINGS['topicperpage'];
        $pppnew = isset($_POST['pppnew']) ? (int) $_POST['pppnew'] : $SETTINGS['postperpage'];

        $dateformatnew = postedVar('dateformatnew', '', FALSE, TRUE);
        $dateformattest = attrOut($dateformatnew, 'javascript');  // NEVER allow attribute-special data in the date format because it can be unescaped using the date() parser.
        if (strlen($dateformatnew) == 0 || $dateformatnew !== $dateformattest) {
            $dateformatnew = $SETTINGS['dateformat'];
        }
        unset($dateformattest);

        $timeformatnew = formInt('timeformatnew');
        if ($timeformatnew != 12 && $timeformatnew != 24) {
            $timeformatnew = $SETTINGS['timeformat'];
        }

        $newsubs = formYesNo('newsubs');
        $saveogu2u = formYesNo('saveogu2u');
        $emailonu2u = formYesNo('emailonu2u');
        $useoldu2u = formYesNo('useoldu2u');
        $invisible = formInt('newinv');
        $showemail = formYesNo('newshowemail');
        $newsletter = formYesNo('newnewsletter');
        $u2ualert = formInt('u2ualert');
        $year = formInt('year');
        $month = formInt('month');
        $day = formInt('day');
        // For year of birth, reject all integers from 100 through 1899.
        if ($year >= 100 && $year <= 1899) $year = 0;
        $bday = iso8601_date($year, $month, $day);
        $email = postedVar('newemail', 'javascript', TRUE, TRUE, TRUE);

        if ($email !== $db->escape($vars->self['email'])) {
            if ($SETTINGS['doublee'] == 'off' && false !== strpos($email, "@")) {
                $query = $db->query("SELECT COUNT(uid) FROM " . $vars->tablepre . "members WHERE email = '$email' AND username != '$xmbuser'");
                $count1 = (int) $db->result($query,0);
                $db->free_result($query);
                if ($count1 != 0) {
                    error($lang['alreadyreg']);
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
                error($lang['emailrestricted']);
            }

            require XMB_ROOT.'include/validate-email.inc.php';
            $test = new EmailAddressValidator();
            $rawemail = postedVar('newemail', '', FALSE, FALSE);
            if (false === $test->check_email_address($rawemail)) {
                error($lang['bademail']);
            }
        }

        if ($SETTINGS['avastatus'] == 'on') {
            $avatar = postedVar('newavatar', 'javascript', TRUE, TRUE, TRUE);
            $rawavatar = postedVar('newavatar', '', FALSE, FALSE);

            $newavatarcheck = postedVar('newavatarcheck');

            $max_size = explode('x', $SETTINGS['max_avatar_size']);

            if (preg_match('/^' . get_img_regexp($https_only) . '$/i', $rawavatar) == 0) {
                $avatar = '';
            } elseif (ini_get('allow_url_fopen')) {
                if ((int) $max_size[0] > 0 && (int) $max_size[1] > 0 && strlen($rawavatar) > 0) {
                    $size = @getimagesize($rawavatar);
                    if ($size === FALSE) {
                        $avatar = '';
                    } elseif (($size[0] > (int) $max_size[0] || $size[1] > (int) $max_size[1]) && !X_SADMIN) {
                        error($lang['avatar_too_big'] . $SETTINGS['max_avatar_size'] . 'px');
                    }
                }
            } elseif ($newavatarcheck == "no") {
                $avatar = '';
            }
            unset($rawavatar);
        } elseif ($SETTINGS['avastatus'] == 'list') {
            $rawavatar = postedVar('newavatar', '', FALSE, FALSE);
            $dirHandle = opendir(XMB_ROOT.'images/avatars');
            $filefound = FALSE;
            while($avFile = readdir($dirHandle)) {
                if ($rawavatar == './images/avatars/'.$avFile) {
                    if (is_file(XMB_ROOT.'images/avatars/'.$avFile) && $avFile != '.' && $avFile != '..' && $avFile != 'index.html') {
                        $filefound = TRUE;
                    }
                }
            }
            closedir($dirHandle);
            unset($rawavatar);
            if ($filefound) {
                $avatar = postedVar('newavatar', 'javascript', TRUE, TRUE, TRUE);
            } else {
                $avatar = '';
            }
        } else {
            $avatar = '';
        }

        if ('on' == $SETTINGS['regoptional'] || 'off' == $SETTINGS['quarantine_new_users'] || ((int) $vars->self['postnum'] > 0 && 'no' == $vars->self['waiting_for_mod']) || X_STAFF) {
            $location = postedVar('newlocation', 'javascript', TRUE, TRUE, TRUE);
            $site = postedVar('newsite', 'javascript', TRUE, TRUE, TRUE);
            $bio = postedVar('newbio', 'javascript', TRUE, TRUE, TRUE);
            $mood = postedVar('newmood', 'javascript', TRUE, TRUE, TRUE);
            $sig = postedVar('newsig', 'javascript', TRUE, TRUE, TRUE);

            if ($SETTINGS['resetsigs'] == 'on') {
                if (strlen(trim($vars->self['sig'])) == 0) {
                    if (strlen(trim($sig)) > 0) {
                        $sql->setPostSigsByAuthor(true, $vars->self['username']);
                    }
                } elseif (strlen(trim($sig)) == 0) {
                    $sql->setPostSigsByAuthor(false, $vars->self['username']);
                }
            }
        } else {
            $avatar = '';
            $location = '';
            $site = '';
            $bio = '';
            $mood = '';
            $sig = '';
        }

        $db->query("UPDATE " . $vars->tablepre . "members SET $pwtxt email='$email', site='$site', location='$location', bio='$bio', sig='$sig', showemail='$showemail',
            timeoffset='$timeoffset1', avatar='$avatar', theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew',
            newsletter='$newsletter', timeformat='$timeformatnew', dateformat='$dateformatnew', mood='$mood', invisible='$invisible', saveogu2u='$saveogu2u',
            emailonu2u='$emailonu2u', useoldu2u='$useoldu2u', u2ualert=$u2ualert, sub_each_post='$newsubs' WHERE username='$xmbuser'"
        );

        message($lang['usercpeditpromsg'], TRUE, '', '', $vars->full_url . 'memcp.php', true, false, true);
    }
} elseif ($action == 'favorites') {
    $header = $template->process('header.php');
    $header .= $template->process('memcp_nav.php');

    $favadd = getInt('favadd');
    if (noSubmit('favsubmit') && $favadd) {
        if ($favadd == 0) {
            error($lang['generic_missing']);
        }

        $query = $db->query("SELECT fid FROM " . $vars->tablepre . "threads WHERE tid=$favadd");
        if ($db->num_rows($query) == 0) {
            error($lang['privforummsg']);
        }
        $row = $db->fetch_array($query);
        $forum = $forums->getForum((int) $row['fid']);
        $perms = checkForumPermissions($forum);
        if (!($perms[$vars::PERMS_VIEW] && $perms[$vars::PERMS_PASSWORD])) {
            error($lang['privforummsg']);
        }
        if ($forum['type'] == 'sub') {
            $perms = $core->checkForumPermissions($forums->getForum((int) $forum['fup']));
            if (!($perms[$vars::PERMS_VIEW] && $perms[$vars::PERMS_PASSWORD])) {
                error($lang['privforummsg']);
            }
        }

        $query = $db->query("SELECT tid FROM " . $vars->tablepre . "favorites WHERE tid=$favadd AND username='$xmbuser' AND type='favorite'");
        $favthread = $db->fetch_array($query);
        $db->free_result($query);

        if ($favthread) {
            error($lang['favonlistmsg']);
        }

        $db->query("INSERT INTO " . $vars->tablepre . "favorites (tid, username, type) VALUES ($favadd, '$xmbuser', 'favorite')");
        message($lang['favaddedmsg'], TRUE, '', '', $vars->full_url . 'memcp.php?action=favorites', true, false, true);
    }

    if (!$favadd && noSubmit('favsubmit')) {
        $favnum = 0;
        $favs = '';
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

                $lastreplydate = gmdate($dateformat, $core->timeKludge((int) $lastpost[0]));
                $lastreplytime = gmdate($timecode, $core->timeKludge((int) $lastpost[0]));
                $lastpost = $lang['lastreply1'].' '.$lastreplydate.' '.$lang['textat'].' '.$lastreplytime.' '.$lang['textby'].' '.$lastpostname;
                $fav['subject'] = rawHTMLsubject(stripslashes($fav['subject']));

                if ($fav['icon'] != '') {
                    $fav['icon'] = '<img src="'.$smdir.'/'.$fav['icon'].'" alt="" border="0" />';
                } else {
                    $fav['icon'] = '';
                }

                $favnum++;
                eval('$favs .= "'.template('memcp_favs_row').'";');
            }
            unset($query);
        }

        $favsbtn = '';
        if ($favnum != 0) {
            eval('$favsbtn = "'.template('memcp_favs_button').'";');
        }

        if ($favnum == 0) {
            eval('$favs = "'.template('memcp_favs_none').'";');
        }
        eval('$mempage = "'.template('memcp_favs').'";');
    }

    if (!$favadd && onSubmit('favsubmit')) {
        $query = $db->query("SELECT tid FROM " . $vars->tablepre . "favorites WHERE username='$xmbuser' AND type='favorite'");
        $tids = array();
        while($fav = $db->fetch_array($query)) {
            $delete = formInt('delete'.$fav['tid']);
            if ($delete == intval($fav['tid'])) {
                $tids[] = $delete;
            }
        }
        $db->free_result($query);
        if (count($tids) > 0) {
            $tids = implode(', ', $tids);
            $db->query("DELETE FROM " . $vars->tablepre . "favorites WHERE username='$xmbuser' AND tid IN ($tids) AND type='favorite'");
        }
        message($lang['favsdeletedmsg'], TRUE, '', '', $vars->full_url . 'memcp.php?action=favorites', true, false, true);
    }
} elseif ($action == 'subscriptions') {
    $subadd = getInt('subadd');
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
        $query = $db->query("SELECT COUNT(tid) FROM " . $vars->tablepre . "favorites WHERE tid='$subadd' AND username='$xmbuser' AND type='subscription'");
        if ((int) $db->result($query, 0) == 1) {
            $db->free_result($query);
            error($lang['subonlistmsg'], TRUE);
        } else {
            $db->query("INSERT INTO " . $vars->tablepre . "favorites (tid, username, type) VALUES ('$subadd', '$xmbuser', 'subscription')");
            message($lang['subaddedmsg'], TRUE, '', '', $vars->full_url . 'memcp.php?action=subscriptions', true, false, true);
        }
    } elseif (! $subadd && onSubmit('subsubmit')) {
        $query = $db->query("SELECT tid FROM " . $vars->tablepre . "favorites WHERE username='$xmbuser' AND type='subscription'");
        $tids = array();
        while ($sub = $db->fetch_array($query)) {
            $delete = formInt('delete'.$sub['tid']);
            if ($delete == intval($sub['tid'])) {
                $tids[] = $delete;
            }
        }
        $db->free_result($query);
        if (count($tids) > 0) {
            $tids = implode(', ', $tids);
            $db->query("DELETE FROM " . $vars->tablepre . "favorites WHERE username='$xmbuser' AND tid IN ($tids) AND type='subscription'");
        }
        message($lang['subsdeletedmsg'], TRUE, '', '', $vars->full_url . 'memcp.php?action=subscriptions', true, false, true);
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
    require XMB_ROOT . 'include/buddy.inc.php';
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
