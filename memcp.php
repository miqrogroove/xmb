<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2021, The XMB Group
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
 *
 **/

define('X_SCRIPT', 'memcp.php');

require 'header.php';

header('X-Robots-Tag: noindex');

loadtemplates(
'buddylist_buddy_offline',
'buddylist_buddy_online',
'memcp_devices',
'memcp_devices_button',
'memcp_devices_firstrow',
'memcp_devices_row',
'memcp_favs',
'memcp_favs_button',
'memcp_favs_none',
'memcp_favs_row',
'memcp_home',
'memcp_home_favs_none',
'memcp_home_favs_row',
'memcp_home_u2u_none',
'memcp_home_u2u_row',
'memcp_profile',
'memcp_profile_avatarlist',
'memcp_profile_avatarurl',
'memcp_profile_optional',
'memcp_subscriptions',
'memcp_subscriptions_button',
'memcp_subscriptions_multipage',
'memcp_subscriptions_none',
'memcp_subscriptions_row',
'timezone_control'
);

smcwcache();

$buddys = array();
$favs = '';
$footer = '';
$header = '';
$mempage = '';
$https_only = 'on' == $SETTINGS['images_https_only'];
$js_https_only = $https_only ? 'true' : 'false';

$action = postedVar('action', '', FALSE, FALSE, FALSE, 'g');
switch($action) {
    case 'profile':
        nav('<a href="memcp.php">'.$lang['textusercp'].'</a>');
        nav($lang['texteditpro']);
        break;
    case 'subscriptions':
        nav('<a href="memcp.php">'.$lang['textusercp'].'</a>');
        nav($lang['textsubscriptions']);
        break;
    case 'favorites':
        nav('<a href="memcp.php">'.$lang['textusercp'].'</a>');
        nav($lang['textfavorites']);
        break;
    case 'devices':
        nav('<a href="memcp.php">'.$lang['textusercp'].'</a>');
        nav($lang['devices']);
        break;
    default:
        nav($lang['textusercp']);
        break;
}

function makenav($current) {
    global $THEME, $bordercolor, $tablewidth, $altbg1, $altbg2, $lang;

    $output =
      '<table cellpadding="0" cellspacing="0" border="0" bgcolor="'.$bordercolor.'" width="'.$tablewidth.'" align="center"><tr><td>
      <table cellpadding="4" cellspacing="'.$THEME['borderwidth'].'" border="0" width="100%" class="tablelinks">
      <tr align="center" class="tablerow">';

    
    $color = ($current == '') ? $altbg1 : $altbg2;
    $output .= "<td bgcolor='$color' width='15%' class='ctrtablerow'><a href='memcp.php'>{$lang['textmyhome']}</a></td>";

    $color = ($current == 'profile') ? $altbg1 : $altbg2;
    $output .= "<td bgcolor='$color' width='15%' class='ctrtablerow'><a href='memcp.php?action=profile'>{$lang['texteditpro']}</a></td>";

    $color = ($current == 'subscriptions') ? $altbg1 : $altbg2;
    $output .= "<td bgcolor='$color' width='15%' class='ctrtablerow'><a href='memcp.php?action=subscriptions'>{$lang['textsubscriptions']}</a></td>";

    $color = ($current == 'favorites') ? $altbg1 : $altbg2;
    $output .= "<td bgcolor='$color' width='15%' class='ctrtablerow'><a href='memcp.php?action=favorites'>{$lang['textfavorites']}</a></td>";

    $color = ($current == 'devices') ? $altbg1 : $altbg2;
    $output .= "<td bgcolor='$color' width='15%' class='ctrtablerow'><a href='memcp.php?action=devices'>{$lang['devices']}</a></td>";

    $output .= "<td bgcolor='$altbg2' width='13%' class='ctrtablerow'><a href='u2u.php' onclick=\"Popup(this.href, 'Window', 700, 450); return false;\">{$lang['textu2umessenger']}</a></td>";
    $output .= "<td bgcolor='$altbg2' width='12%' class='ctrtablerow'><a href='buddy.php' onclick=\"Popup(this.href, 'Window', 450, 400); return false;\">{$lang['textbuddylist']}</a></td>";
    $output .=
      '</tr>
      </table>
      </td>
      </tr>
      </table>
      <br />';

    return $output;
}

if (X_GUEST) {
    redirect($full_url.'misc.php?action=login', 0);
    exit();
}

if ($action == 'profile') {
    eval('$header = "'.template('header').'";');
    $header .= makenav($action);

    if (noSubmit('editsubmit')) {
        $member = $self;

        $checked = '';
        if ($member['showemail'] == 'yes') {
            $checked = $cheHTML;
        }

        $subschecked = '';
        if ($member['sub_each_post'] == 'yes') {
            $subschecked = $cheHTML;
        }

        $newschecked = '';
        if ($member['newsletter'] == 'yes') {
            $newschecked = $cheHTML;
        }

        $uou2uchecked = '';
        if ($member['useoldu2u'] == 'yes') {
            $uou2uchecked = $cheHTML;
        }

        $ogu2uchecked = '';
        if ($member['saveogu2u'] == 'yes') {
            $ogu2uchecked = $cheHTML;
        }

        $eouchecked = '';
        if ($member['emailonu2u'] == 'yes') {
            $eouchecked = $cheHTML;
        }

        $invchecked = '';
        if ( '1' === $member['invisible'] ) {
            $invchecked = $cheHTML;
        }

        $currdate = gmdate($timecode, $onlinetime+ ($SETTINGS['addtime'] * 3600));
        $textoffset = str_replace( '$currdate', $currdate, $lang['evaloffset'] );

        $timezones = timezone_control( $member['timeoffset'] );

        $u2uasel0 = $u2uasel1 = $u2uasel2 = '';
        switch($member['u2ualert']) {
            case 2:
                $u2uasel2 = $selHTML;
                break;
            case 1:
                $u2uasel1 = $selHTML;
                break;
            case 0:
            default:
                $u2uasel0 = $selHTML;
                break;
        }

        $themelist = array();
        $themelist[] = '<select name="thememem">';
        $themelist[] = '<option value="0">'.$lang['textusedefault'].'</option>';
        $query = $db->query("SELECT themeid, name FROM ".X_PREFIX."themes ORDER BY name ASC");
        while($themeinfo = $db->fetch_array($query)) {
            if ( $themeinfo['themeid'] === $member['theme'] ) {
                $themelist[] = '<option value="'.intval($themeinfo['themeid']).'" '.$selHTML.'>'.$themeinfo['name'].'</option>';
            } else {
                $themelist[] = '<option value="'.intval($themeinfo['themeid']).'">'.$themeinfo['name'].'</option>';
            }
        }
        $themelist[] = '</select>';
        $themelist = implode("\n", $themelist);
        $db->free_result($query);

        $langfileselect = createLangFileSelect($member['langfile']);

        $day = intval(substr($member['bday'], 8, 2));
        $month = intval(substr($member['bday'], 5, 2));
        $year = substr($member['bday'], 0, 4);

        for($i = 0; $i <= 12; $i++) {
            $sel[$i] = '';
        }
        $sel[$month] = $selHTML;

        $dayselect = array();
        $dayselect[] = '<select name="day">';
        $dayselect[] = '<option value="">&nbsp;</option>';
        for($num = 1; $num <= 31; $num++) {
            if ($day == $num) {
                $dayselect[] = '<option value="'.$num.'" '.$selHTML.'>'.$num.'</option>';
            } else {
                $dayselect[] = '<option value="'.$num.'">'.$num.'</option>';
            }
        }
        $dayselect[] = '</select>';
        $dayselect = implode("\n", $dayselect);

        $check12 = $check24 = '';
        if ( '24' === $member['timeformat'] ) {
            $check24 = $cheHTML;
        } else {
            $check12 = $cheHTML;
        }

        if ($SETTINGS['sigbbcode'] == 'on') {
            $bbcodeis = $lang['texton'];
        } else {
            $bbcodeis = $lang['textoff'];
        }

        $htmlis = $lang['textoff'];

        $avatar = '';
        if ($SETTINGS['avastatus'] == 'on') {
            if ( $https_only && strpos( $member['avatar'], ':' ) !== false && substr( $member['avatar'], 0, 6 ) != 'https:' ) {
                $member['avatar'] = '';
            }
            eval('$avatar = "'.template('memcp_profile_avatarurl').'";');
        }

        if ($SETTINGS['avastatus'] == 'list')  {
            $avatars = '<option value="" />'.$lang['textnone'].'</option>';
            $dir1 = opendir(ROOT.'images/avatars');
            while($avFile = readdir($dir1)) {
                if (is_file(ROOT.'images/avatars/'.$avFile) && $avFile != '.' && $avFile != '..' && $avFile != 'index.html') {
                    $avatars .= '<option value="./images/avatars/'.$avFile.'" />'.$avFile.'</option>';
                }
            }
            $avatars = str_replace('value="'.$member['avatar'].'"', 'value="'.$member['avatar'].'" selected="selected"', $avatars);
            $avatarbox = '<select name="newavatar" onchange="document.images.avatarpic.src=this[this.selectedIndex].value;">'.$avatars.'</select>';
            eval('$avatar = "'.template('memcp_profile_avatarlist').'";');
            closedir($dir1);
        }

        $member['icq'] = ($member['icq'] > 0) ? $member['icq'] : '';
        $member['bio'] = rawHTMLsubject($member['bio']);
        $member['location'] = rawHTMLsubject($member['location']);
        $member['mood'] = rawHTMLsubject($member['mood']);
        $member['sig'] = rawHTMLsubject($member['sig']);
        $optional = '';
        if ( 'on' == $SETTINGS['regoptional'] || 'off' == $SETTINGS['quarantine_new_users'] || ( (int) $self['postnum'] > 0 && 'no' == $self['waiting_for_mod'] ) || X_STAFF ) {
            eval('$optional = "'.template('memcp_profile_optional').'";');
        }
        if (X_STAFF) {
            $template = template_secure( 'memcp_profile', 'User Control Panel/Edit Profile', $self['uid'], X_NONCE_FORM_EXP );
        } else {
            $template = template('memcp_profile');
        }
        eval('$mempage = "'.$template.'";');
    }

    if (onSubmit('editsubmit')) {
        if (X_STAFF) request_secure( 'User Control Panel/Edit Profile', $self['uid'], 0, true );
        if (!empty($_POST['newpassword'])) {
            if (empty($_POST['oldpassword'])) {
                error($lang['textpwincorrect']);
            }
            $member = \XMB\SQL\getMemberByName( $self['username'] );
            if ( $member['password'] !== md5($_POST['oldpassword']) ) {
                error($lang['textpwincorrect']);
            }
            unset( $member );
            if (empty($_POST['newpasswordcf'])) {
                error($lang['pwnomatch']);
            }
            if ( $_POST['newpassword'] !== $_POST['newpasswordcf'] ) {
                error($lang['pwnomatch']);
            }

            $newpassword = md5($_POST['newpassword']);

            $pwtxt = "password='$newpassword',";

            // Force logout and delete cookies.
            $query = $db->query("DELETE FROM ".X_PREFIX."whosonline WHERE username='$xmbuser'");
            $session->logoutAll();
        } else {
            $pwtxt = '';
        }

        $langfilenew = postedVar('langfilenew');
        $result = $db->query("SELECT devname FROM ".X_PREFIX."lang_base WHERE devname='$langfilenew'");
        if ($db->num_rows($result) == 0) {
            $langfilenew = $SETTINGS['langfile'];
        }

        $timeoffset1 = isset($_POST['timeoffset1']) && is_numeric($_POST['timeoffset1']) ? $_POST['timeoffset1'] : 0;
        $thememem = formInt('thememem');
        $tppnew = isset($_POST['tppnew']) ? (int) $_POST['tppnew'] : $SETTINGS['topicperpage'];
        $pppnew = isset($_POST['pppnew']) ? (int) $_POST['pppnew'] : $SETTINGS['postperpage'];

        $dateformatnew = postedVar('dateformatnew', '', FALSE, TRUE);
        $dateformattest = attrOut($dateformatnew, 'javascript');  // NEVER allow attribute-special data in the date format because it can be unescaped using the date() parser.
        if ( strlen($dateformatnew) == 0 || $dateformatnew !== $dateformattest ) {
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

        if ( $email !== $db->escape( $self['email'] ) ) {
            if ($SETTINGS['doublee'] == 'off' && false !== strpos($email, "@")) {
                $query = $db->query("SELECT COUNT(uid) FROM ".X_PREFIX."members WHERE email = '$email' AND username != '$xmbuser'");
                $count1 = (int) $db->result($query,0);
                $db->free_result($query);
                if ($count1 != 0) {
                    error($lang['alreadyreg']);
                }
            }

            $efail = false;
            $query = $db->query("SELECT * FROM ".X_PREFIX."restricted");
            while($restriction = $db->fetch_array($query)) {
                $t_email = $email;
                if ( '0' === $restriction['case_sensitivity'] ) {
                    $t_email = strtolower($t_email);
                    $restriction['name'] = strtolower($restriction['name']);
                }

                if ( '1' === $restriction['partial'] ) {
                    if (strpos($t_email, $restriction['name']) !== false) {
                        $efail = true;
                    }
                } else {
                    if ( $t_email === $restriction['name'] ) {
                        $efail = true;
                    }
                }
            }
            $db->free_result($query);

            if ($efail) {
                error($lang['emailrestricted']);
            }

            require ROOT.'include/validate-email.inc.php';
            $test = new EmailAddressValidator();
            $rawemail = postedVar('newemail', '', FALSE, FALSE);
            if (false === $test->check_email_address($rawemail)) {
                error($lang['bademail']);
            }
        }

        if ($SETTINGS['resetsigs'] == 'on') {
            if (strlen(trim($self['sig'])) == 0) {
                if (strlen($sig) > 0) {
                    $db->query("UPDATE ".X_PREFIX."posts SET usesig='yes' WHERE author='$xmbuser'");
                }
            } else {
                if (strlen(trim($sig)) == 0) {
                    $db->query("UPDATE ".X_PREFIX."posts SET usesig='no' WHERE author='$xmbuser'");
                }
            }
        }

        if ($SETTINGS['avastatus'] == 'on') {
            $avatar = postedVar('newavatar', 'javascript', TRUE, TRUE, TRUE);
            $rawavatar = postedVar('newavatar', '', FALSE, FALSE);

            $newavatarcheck = postedVar('newavatarcheck');

            $max_size = explode('x', $SETTINGS['max_avatar_size']);

            if (preg_match('/^' . get_img_regexp( $https_only ) . '$/i', $rawavatar) == 0) {
                $avatar = '';
            } elseif (ini_get('allow_url_fopen')) {
                if ( (int) $max_size[0] > 0 && (int) $max_size[1] > 0 && strlen($rawavatar) > 0) {
                    $size = @getimagesize($rawavatar);
                    if ($size === FALSE) {
                        $avatar = '';
                    } elseif ( ( $size[0] > (int) $max_size[0] || $size[1] > (int) $max_size[1] ) && !X_SADMIN ) {
                        error($lang['avatar_too_big'] . $SETTINGS['max_avatar_size'] . 'px');
                    }
                }
            } elseif ($newavatarcheck == "no") {
                $avatar = '';
            }
            unset($rawavatar);
        } elseif ($SETTINGS['avastatus'] == 'list') {
            $rawavatar = postedVar('newavatar', '', FALSE, FALSE);
            $dirHandle = opendir(ROOT.'images/avatars');
            $filefound = FALSE;
            while($avFile = readdir($dirHandle)) {
                if ($rawavatar == './images/avatars/'.$avFile) {
                    if (is_file(ROOT.'images/avatars/'.$avFile) && $avFile != '.' && $avFile != '..' && $avFile != 'index.html') {
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

        if ( 'on' == $SETTINGS['regoptional'] || 'off' == $SETTINGS['quarantine_new_users'] || ( (int) $self['postnum'] > 0 && 'no' == $self['waiting_for_mod'] ) || X_STAFF ) {
            $location = postedVar('newlocation', 'javascript', TRUE, TRUE, TRUE);
            $icq = abs( formInt( 'newicq' ) );
            $yahoo = postedVar('newyahoo', 'javascript', TRUE, TRUE, TRUE);
            $aim = postedVar('newaim', 'javascript', TRUE, TRUE, TRUE);
            $msn = postedVar('newmsn', 'javascript', TRUE, TRUE, TRUE);
            $site = postedVar('newsite', 'javascript', TRUE, TRUE, TRUE);
            $bio = postedVar('newbio', 'javascript', TRUE, TRUE, TRUE);
            $mood = postedVar('newmood', 'javascript', TRUE, TRUE, TRUE);
            $sig = postedVar('newsig', 'javascript', TRUE, TRUE, TRUE);
        } else {
            $avatar = '';
            $location = '';
            $icq = '';
            $yahoo = '';
            $aim = '';
            $msn = '';
            $site = '';
            $bio = '';
            $mood = '';
            $sig = '';
        }

        $db->query("UPDATE ".X_PREFIX."members SET $pwtxt email='$email', site='$site', aim='$aim', location='$location', bio='$bio', sig='$sig', showemail='$showemail',
        timeoffset='$timeoffset1', icq='$icq', avatar='$avatar', yahoo='$yahoo', theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew',
        newsletter='$newsletter', timeformat='$timeformatnew', msn='$msn', dateformat='$dateformatnew', mood='$mood', invisible='$invisible', saveogu2u='$saveogu2u',
        emailonu2u='$emailonu2u', useoldu2u='$useoldu2u', u2ualert=$u2ualert, sub_each_post='$newsubs' WHERE username='$xmbuser'");

        message($lang['usercpeditpromsg'], TRUE, '', '', $full_url.'memcp.php', true, false, true);
    }
} else if ($action == 'favorites') {
    eval('$header = "'.template('header').'";');
    $header .= makenav($action);

    $favadd = getInt('favadd');
    if (noSubmit('favsubmit') && $favadd) {
        if ($favadd == 0) {
            error($lang['generic_missing']);
        }

        $query = $db->query("SELECT fid FROM ".X_PREFIX."threads WHERE tid=$favadd");
        if ($db->num_rows($query) == 0) {
            error($lang['privforummsg']);
        }
        $row = $db->fetch_array($query);
        $forum = getForum($row['fid']);
        $perms = checkForumPermissions($forum);
        if (!($perms[X_PERMS_VIEW] && $perms[X_PERMS_PASSWORD])) {
            error($lang['privforummsg']);
        }
        if ($forum['type'] == 'sub') {
            $perms = checkForumPermissions(getForum($forum['fup']));
            if (!($perms[X_PERMS_VIEW] && $perms[X_PERMS_PASSWORD])) {
                error($lang['privforummsg']);
            }
        }

        $query = $db->query("SELECT tid FROM ".X_PREFIX."favorites WHERE tid=$favadd AND username='$xmbuser' AND type='favorite'");
        $favthread = $db->fetch_array($query);
        $db->free_result($query);

        if ($favthread) {
            error($lang['favonlistmsg']);
        }

        $db->query("INSERT INTO ".X_PREFIX."favorites (tid, username, type) VALUES ($favadd, '$xmbuser', 'favorite')");
        message($lang['favaddedmsg'], TRUE, '', '', $full_url.'memcp.php?action=favorites', true, false, true);
    }

    if (!$favadd && noSubmit('favsubmit')) {
        $favnum = 0;
        $favs = '';
        $fids = permittedForums(forumCache(), 'thread', 'csv');
        if (strlen($fids) != 0) {
            $query = $db->query(
                "SELECT t.tid, t.fid, t.icon, t.lastpost, t.subject, t.replies, r.uid AS lastauthor
                 FROM ".X_PREFIX."favorites f
                 INNER JOIN ".X_PREFIX."threads t USING (tid)
                 LEFT JOIN ".X_PREFIX."members AS r ON SUBSTRING_INDEX(SUBSTRING_INDEX(t.lastpost, '|', 2), '|', -1) = r.username
                 WHERE f.username='$xmbuser' AND f.type='favorite' AND t.fid IN ($fids)
                 ORDER BY t.lastpost DESC"
            );
            $tmOffset = ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600);
            while($fav = $db->fetch_array($query)) {
                $forum = getForum($fav['fid']);
                $forum['name'] = fnameOut($forum['name']);

                $lastpost = explode('|', $fav['lastpost']);

                // Translate "Anonymous" author.
                $lastpostname = trim( $lastpost[1] );
                if ( 'Anonymous' == $lastpostname ) {
                    $lastpostname = $lang['textanonymous'];
                }

                $lastreplydate = gmdate($dateformat, $lastpost[0] + $tmOffset);
                $lastreplytime = gmdate($timecode, $lastpost[0] + $tmOffset);
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
            $db->free_result($query);
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
        $query = $db->query("SELECT tid FROM ".X_PREFIX."favorites WHERE username='$xmbuser' AND type='favorite'");
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
            $db->query("DELETE FROM ".X_PREFIX."favorites WHERE username='$xmbuser' AND tid IN ($tids) AND type='favorite'");
        }
        message($lang['favsdeletedmsg'], TRUE, '', '', $full_url.'memcp.php?action=favorites', true, false, true);
    }
} else if ($action == 'subscriptions') {
    $subadd = getInt('subadd');
    if (!$subadd && noSubmit('subsubmit')) {
        $num = $db->result($db->query("SELECT COUNT(*) FROM ".X_PREFIX."favorites WHERE username='$xmbuser' AND type='subscription'"), 0);
        $mpage = multipage($num, $tpp, 'memcp.php?action=subscriptions');
        $multipage =& $mpage['html'];
        if (strlen($mpage['html']) != 0) {
            eval('$multipage = "'.template('memcp_subscriptions_multipage').'";');
        }

        eval('$header = "'.template('header').'";');
        $header .= makenav($action);

        $query = $db->query(
            "SELECT t.tid, t.fid, t.icon, t.lastpost, t.subject, t.replies, r.uid AS lastauthor
             FROM ".X_PREFIX."favorites f
             INNER JOIN ".X_PREFIX."threads t USING (tid)
             LEFT JOIN ".X_PREFIX."members AS r ON SUBSTRING_INDEX(SUBSTRING_INDEX(t.lastpost, '|', 2), '|', -1) = r.username
             WHERE f.username='$xmbuser' AND f.type='subscription'
             ORDER BY t.lastpost DESC
             LIMIT {$mpage['start']}, $tpp"
        );
        $subnum = 0;
        $subscriptions = '';
        $tmOffset = ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600);
        while($fav = $db->fetch_array($query)) {
            $forum = getForum($fav['fid']);
            $forum['name'] = fnameOut($forum['name']);

            $lastpost = explode('|', $fav['lastpost']);

            // Translate "Anonymous" author.
            $lastpostname = trim( $lastpost[1] );
            if ( 'Anonymous' == $lastpostname ) {
                $lastpostname = $lang['textanonymous'];
            }

            $lastreplydate = gmdate($dateformat, $lastpost[0] + $tmOffset);
            $lastreplytime = gmdate($timecode, $lastpost[0] + $tmOffset);
            $lastpost = $lang['lastreply1'].' '.$lastreplydate.' '.$lang['textat'].' '.$lastreplytime.' '.$lang['textby'].' '.$lastpostname;
            $fav['subject'] = rawHTMLsubject(stripslashes($fav['subject']));

            if ($fav['icon'] != '') {
                $fav['icon'] = '<img src="'.$smdir.'/'.$fav['icon'].'" alt="" border="0" />';
            } else {
                $fav['icon'] = '';
            }
            $subnum++;
            eval('$subscriptions .= "'.template('memcp_subscriptions_row').'";');
        }

        $subsbtn = '';
        if ($subnum != 0) {
            eval('$subsbtn = "'.template('memcp_subscriptions_button').'";');
        }

        if ($subnum == 0) {
            eval('$subscriptions = "'.template('memcp_subscriptions_none').'";');
        }
        $db->free_result($query);
        eval('$mempage = "'.template('memcp_subscriptions').'";');
    } else if ($subadd && noSubmit('subsubmit')) {
        $query = $db->query("SELECT COUNT(tid) FROM ".X_PREFIX."favorites WHERE tid='$subadd' AND username='$xmbuser' AND type='subscription'");
        if ( (int) $db->result( $query, 0 ) == 1 ) {
            $db->free_result($query);
            error($lang['subonlistmsg'], TRUE);
        } else {
            $db->query("INSERT INTO ".X_PREFIX."favorites (tid, username, type) VALUES ('$subadd', '$xmbuser', 'subscription')");
            message($lang['subaddedmsg'], TRUE, '', '', $full_url.'memcp.php?action=subscriptions', true, false, true);
        }
    } else if (!$subadd && onSubmit('subsubmit')) {
        $query = $db->query("SELECT tid FROM ".X_PREFIX."favorites WHERE username='$xmbuser' AND type='subscription'");
        $tids = array();
        while($sub = $db->fetch_array($query)) {
            $delete = formInt('delete'.$sub['tid']);
            if ($delete == intval($sub['tid'])) {
                $tids[] = $delete;
            }
        }
        $db->free_result($query);
        if (count($tids) > 0) {
            $tids = implode(', ', $tids);
            $db->query("DELETE FROM ".X_PREFIX."favorites WHERE username='$xmbuser' AND tid IN ($tids) AND type='subscription'");
        }
        message($lang['subsdeletedmsg'], TRUE, '', '', $full_url.'memcp.php?action=subscriptions', true, false, true);
    }
} else if ( $action == 'devices' ) {
    if ( onSubmit( 'devicesubmit' ) ) {
        $ids = [];
        foreach( $_POST as $name => $value ) {
            if ( substr( $name, 0, 6 ) == 'delete' && strlen( $value ) == 4 && $name == "delete$value" ) {
                $ids[] = $value;
            }
        }
        if ( ! empty( $ids ) ) {
            // This page only handles the default session mechanism for now.
            $lists = [\XMB\Session\FormsAndCookies::class => $ids];
            $session->logoutByLists( $lists );
        }
    }

    eval('$header = "'.template('header').'";');
    $header .= makenav($action);
    $current = '';
    $other = '';

    $lists = $session->getSessionLists();
    foreach ( $lists as $name => $list ) {
        if ( $name != \XMB\Session\FormsAndCookies::class ) {
            // This page only handles the default session mechanism for now.
            continue;
        }
        foreach ( $list as $device ) {
            $did = $device['token'];
            $time = ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600) + (int) $device['login_date'];
            $dlogin = gmdate( $dateformat, $time ).' '.$lang['textat'].' '.gmdate( $timecode, $time );
            $dagent = parse_user_agent( $device['agent'] );
            if ( $device['current'] ) {
                eval('$current .= "'.template('memcp_devices_firstrow').'";');
            } else {
                eval('$other .= "'.template('memcp_devices_row').'";');
            }
        }
    }
    
    if ( '' == $other ) {
        $devicesbtn = '';
    } else {
        eval('$devicesbtn = "'.template('memcp_devices_button').'";');
    }
    
    eval('$mempage = "'.template('memcp_devices').'";');
} else {
    eval('$header = "'.template('header').'";');
    $usercpwelcome = str_replace( '$xmbuser', $self['username'], $lang['evalusercpwelcome'] );
    $header .= makenav($action);

    $q = $db->query("SELECT b.buddyname, m.invisible, m.username, m.lastvisit FROM ".X_PREFIX."buddys b LEFT JOIN ".X_PREFIX."members m ON (b.buddyname=m.username) WHERE b.username='$xmbuser'");
    $buddys = array();
    $buddys['offline'] = '';
    $buddys['online'] = '';
    while($buddy = $db->fetch_array($q)) {
        $recodename = recodeOut($buddy['buddyname']);
        if ($onlinetime - (int)$buddy['lastvisit'] <= X_ONLINE_TIMER) {
            if ( '1' === $buddy['invisible'] ) {
                if (!X_ADMIN) {
                    eval('$buddys["offline"] .= "'.template('buddylist_buddy_offline').'";');
                    continue;
                } else {
                    $buddystatus = $lang['hidden'];
                }
            } else {
                $buddystatus = $lang['textonline'];
            }
            eval('$buddys["online"] .= "'.template('buddylist_buddy_online').'";');
        } else {
            eval('$buddys["offline"] .= "'.template('buddylist_buddy_offline').'";');
        }
    }
    $db->free_result($q);

    $member = $self;

    if ( $https_only && strpos( $member['avatar'], ':' ) !== false && substr( $member['avatar'], 0, 6 ) != 'https:' ) {
        $member['avatar'] = '';
    }

    if ($member['avatar'] != '') {
        $member['avatar'] = '<img src="'.$member['avatar'].'" border="0" alt="'.$lang['altavatar'].'" />';
    }

    if ($member['mood'] != '') {
        $member['mood'] = postify($member['mood'], 'no', 'no', 'yes', 'no', 'yes', 'no', true, 'yes');
    } else {
        $member['mood'] = '';
    }

    $u2uquery = $db->query("SELECT * FROM ".X_PREFIX."u2u WHERE owner='$xmbuser' AND folder='Inbox' ORDER BY dateline DESC LIMIT 0, 5");
    $u2unum = $db->num_rows($u2uquery);
    $messages = '';
    $tmOffset = ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600);
    while($message = $db->fetch_array($u2uquery)) {
        $postdate = gmdate($dateformat, $message['dateline'] + $tmOffset);
        $posttime = gmdate($timecode, $message['dateline'] + $tmOffset);
        $senton = $postdate.' '.$lang['textat'].' '.$posttime;

        $message['subject'] = rawHTMLsubject(stripslashes($message['subject']));
        if ($message['subject'] == '') {
            $message['subject'] = '&laquo;'.$lang['textnosub'].'&raquo;';
        }

        if ($message['readstatus'] == 'yes') {
            $read = $lang['textread'];
        } else {
            $read = $lang['textunread'];
        }
        eval('$messages .= "'.template('memcp_home_u2u_row').'";');
    }

    if ($u2unum == 0) {
        eval('$messages = "'.template('memcp_home_u2u_none').'";');
    }
    $db->free_result($u2uquery);

    $favnum = 0;
    $favs = '';
    $fids = permittedForums(forumCache(), 'thread', 'csv');
    if (strlen($fids) != 0) {
        $query2 = $db->query(
            "SELECT t.tid, t.fid, t.lastpost, t.subject, t.icon, t.replies, r.uid AS lastauthor
             FROM ".X_PREFIX."favorites f
             INNER JOIN ".X_PREFIX."threads t USING (tid)
             LEFT JOIN ".X_PREFIX."members AS r ON SUBSTRING_INDEX(SUBSTRING_INDEX(t.lastpost, '|', 2), '|', -1) = r.username
             WHERE f.username='$xmbuser' AND f.type='favorite' AND t.fid IN ($fids)
             ORDER BY t.lastpost DESC
             LIMIT 5"
        );
        $favnum = $db->num_rows($query2);
        $tmOffset = ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600);
        while($fav = $db->fetch_array($query2)) {
            $forum = getForum($fav['fid']);
            $forum['name'] = fnameOut($forum['name']);

            $lastpost = explode('|', $fav['lastpost']);

            // Translate "Anonymous" author.
            $lastpostname = trim( $lastpost[1] );
            if ( 'Anonymous' == $lastpostname ) {
                $lastpostname = $lang['textanonymous'];
            }

            $lastreplydate = gmdate($dateformat, $lastpost[0] + $tmOffset);
            $lastreplytime = gmdate($timecode, $lastpost[0] + $tmOffset);
            $lastpost = $lang['lastreply1'].' '.$lastreplydate.' '.$lang['textat'].' '.$lastreplytime.' '.$lang['textby'].' '.$lastpostname;
            $fav['subject'] = rawHTMLsubject(stripslashes($fav['subject']));

            if ($fav['icon'] != '') {
                $fav['icon'] = '<img src="'.$smdir.'/'.$fav['icon'].'" alt="" border="0" />';
            } else {
                $fav['icon'] = '';
            }
            eval('$favs .= "'.template('memcp_home_favs_row').'";');
        }
        $db->free_result($query2);
    }

    if ($favnum == 0) {
        eval('$favs = "'.template('memcp_home_favs_none').'";');
    }
    eval('$mempage = "'.template('memcp_home').'";');
}

end_time();
eval('$footer = "'.template('footer').'";');
echo $header, $mempage, $footer;
?>
