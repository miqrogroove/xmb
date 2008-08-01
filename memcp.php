<?php
/**
 * eXtreme MessageBoard
 * XMB 1.9.11 Alpha Zero - This software should not be used for any purpose after 31 August 2008.
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2008, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * Copyright (c) 2007, iEntry, Inc.
 * http://www.ientry.com
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 **/

define('X_SCRIPT', 'memcp.php');

require 'header.php';

loadtemplates(
'buddylist_buddy_offline',
'buddylist_buddy_online',
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
'memcp_subscriptions',
'memcp_subscriptions_button',
'memcp_subscriptions_none',
'memcp_subscriptions_row'
);

smcwcache();

eval('$css = "'.template('css').'";');

$favs = $buddys = NULL;

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
    default:
        nav($lang['textusercp']);
        break;
}

function makenav($current) {
    global $THEME, $bordercolor, $tablewidth, $tablespacing, $altbg1, $altbg2, $lang;
    ?>
    <table cellpadding="0" cellspacing="0" border="0" bgcolor="<?php echo $bordercolor?>" width="<?php echo $tablewidth?>" align="center"><tr><td>
    <table cellpadding="4" cellspacing="<?php echo $THEME['borderwidth']?>" border="0" width="100%">
    <tr align="center" class="tablerow">
    <?php
    if ($current == '') {
        echo "<td bgcolor=\"$altbg1\" width=\"15%\" class=\"ctrtablerow\">" .$lang['textmyhome']. "</td>";
    } else {
        echo "<td bgcolor=\"$altbg2\" width=\"15%\" class=\"ctrtablerow\"><a href=\"memcp.php\">" .$lang['textmyhome']. "</a></td>";
    }

    if ($current == 'profile') {
        echo "<td bgcolor=\"$altbg1\" width=\"15%\" class=\"ctrtablerow\">" .$lang['texteditpro']. "</td>";
    } else {
        echo "<td bgcolor=\"$altbg2\" width=\"15%\" class=\"ctrtablerow\"><a href=\"memcp.php?action=profile\">" .$lang['texteditpro']. "</a></td>";
    }

    if ($current == 'subscriptions') {
        echo "<td bgcolor=\"$altbg1\" width=\"15%\" class=\"ctrtablerow\">" .$lang['textsubscriptions']. "</td>";
    } else {
        echo "<td bgcolor=\"$altbg2\" width=\"15%\" class=\"ctrtablerow\"><a href=\"memcp.php?action=subscriptions\">" .$lang['textsubscriptions']. "</a></td>";
    }

    if ($current == 'favorites') {
        echo "<td bgcolor=\"$altbg1\" width=\"15%\" class=\"ctrtablerow\">" .$lang['textfavorites']. "</td>";
    } else {
        echo "<td bgcolor=\"$altbg2\" width=\"15%\" class=\"ctrtablerow\"><a href=\"memcp.php?action=favorites\">" .$lang['textfavorites']. "</a></td>";
    }

    echo "<td bgcolor=\"$altbg2\" width=\"20%\" class=\"ctrtablerow\"><a href=\"u2u.php\" onclick=\"Popup(this.href, 'Window', 700, 450); return false;\">" .$lang['textu2umessenger']. "</a></td>";
    echo "<td bgcolor=\"$altbg2\" width=\"15%\" class=\"ctrtablerow\"><a href=\"buddy.php\" onclick=\"Popup(this.href, 'Window', 450, 400); return false;\">" .$lang['textbuddylist']. "</a></td>";
    echo "<td bgcolor=\"$altbg2\" width=\"10%\" class=\"ctrtablerow\"><a href=\"faq.php\">" .$lang['helpbar']. "</a></td>";
    ?>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    <br />
    <?php
}

if (X_GUEST) {
    redirect($full_url.'misc.php?action=login', 0);
    exit();
}

if ($action == 'profile') {
    eval('echo "'.template('header').'";');
    makenav($action);

    if (noSubmit('editsubmit')) {
        $member = $self;

        $checked = '';
        if ($member['showemail'] == 'yes') {
            $checked = $cheHTML;
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
        if ($member['invisible'] == 1) {
            $invchecked = $cheHTML;
        }

        $currdate = gmdate($timecode, $onlinetime+ ($addtime * 3600));
        eval($lang['evaloffset']);

        $timezone1 = $timezone2 = $timezone3 = $timezone4 = $timezone5 = $timezone6 = '';
        $timezone7 = $timezone8 = $timezone9 = $timezone10 = $timezone11 = $timezone12 = '';
        $timezone13 = $timezone14 = $timezone15 = $timezone16 = $timezone17 = $timezone18 = '';
        $timezone19 = $timezone20 = $timezone21 = $timezone22 = $timezone23 = $timezone24 = '';
        $timezone25 = $timezone26 = $timezone27 = $timezone28 = $timezone29 = $timezone30 = '';
        $timezone31 = $timezone32 = $timezone33 = '';
        switch($member['timeoffset']) {
            case '-12.00':
                $timezone1 = $selHTML;
                break;
            case '-11.00':
                $timezone2 = $selHTML;
                break;
            case '-10.00':
                $timezone3 = $selHTML;
                break;
            case '-9.00':
                $timezone4 = $selHTML;
                break;
            case '-8.00':
                $timezone5 = $selHTML;
                break;
            case '-7.00':
                $timezone6 = $selHTML;
                break;
            case '-6.00':
                $timezone7 = $selHTML;
                break;
            case '-5.00':
                $timezone8 = $selHTML;
                break;
            case '-4.00':
                $timezone9 = $selHTML;
                break;
            case '-3.50':
                $timezone10 = $selHTML;
                break;
            case '-3.00':
                $timezone11 = $selHTML;
                break;
            case '-2.00':
                $timezone12 = $selHTML;
                break;
            case '-1.00':
                $timezone13 = $selHTML;
                break;
            case '1.00':
                $timezone15 = $selHTML;
                break;
            case '2.00':
                $timezone16 = $selHTML;
                break;
            case '3.00':
                $timezone17 = $selHTML;
                break;
            case '3.50':
                $timezone18 = $selHTML;
                break;
            case '4.00':
                $timezone19 = $selHTML;
                break;
            case '4.50':
                $timezone20 = $selHTML;
                break;
            case '5.00':
                $timezone21 = $selHTML;
                break;
            case '5.50':
                $timezone22 = $selHTML;
                break;
            case '5.75':
                $timezone23 = $selHTML;
                break;
            case '6.00':
                $timezone24 = $selHTML;
                break;
            case '6.50':
                $timezone25 = $selHTML;
                break;
            case '7.00':
                $timezone26 = $selHTML;
                break;
            case '8.00':
                $timezone27 = $selHTML;
                break;
            case '9.00':
                $timezone28 = $selHTML;
                break;
            case '9.50':
                $timezone29 = $selHTML;
                break;
            case '10.00':
                $timezone30 = $selHTML;
                break;
            case '11.00':
                $timezone31 = $selHTML;
                break;
            case '12.00':
                $timezone32 = $selHTML;
                break;
            case '13.00':
                $timezone33 = $selHTML;
                break;
            case '0.00':
            default:
                $timezone14 = $selHTML;
                break;
        }

        $themelist = array();
        $themelist[] = '<select name="thememem">';
        $themelist[] = '<option value="0">'.$lang['textusedefault'].'</option>';
        $query = $db->query("SELECT themeid, name FROM ".X_PREFIX."themes ORDER BY name ASC");
        while($themeinfo = $db->fetch_array($query)) {
            if ($themeinfo['themeid'] == $member['theme']) {
                $themelist[] = '<option value="'.intval($themeinfo['themeid']).'" '.$selHTML.'>'.stripslashes($themeinfo['name']).'</option>';
            } else {
                $themelist[] = '<option value="'.intval($themeinfo['themeid']).'">'.stripslashes($themeinfo['name']).'</option>';
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
        if ($member['timeformat'] == 24) {
            $check24 = $cheHTML;
        } else {
            $check12 = $cheHTML;
        }

        if ($SETTINGS['sigbbcode'] == 'on') {
            $bbcodeis = $lang['texton'];
        } else {
            $bbcodeis = $lang['textoff'];
        }

        if ($SETTINGS['sightml'] == 'on') {
            $htmlis = $lang['texton'];
        } else {
            $htmlis = $lang['textoff'];
        }

        $avatar = '';
        if ($SETTINGS['avastatus'] == 'on') {
            eval('$avatar = "'.template('memcp_profile_avatarurl').'";');
        }

        if ($SETTINGS['avastatus'] == 'list')  {
            $avatars = '<option value="" />'.$lang['textnone'].'</option>';
            $dir1 = opendir(ROOT.'images/avatars');
            while($avatar1 = readdir($dir1)) {
                if (is_file(ROOT.'images/avatars/'.$avatar1)) {
                    $avatars .= '<option value="'.ROOT.'images/avatars/'.$avatar1.'" />'.$avatar1.'</option>';
                }
            }
            $avatars = str_replace('value="'.$member['avatar'].'"', 'value="'.$member['avatar'].'" selected="selected"', $avatars);
            $avatarbox = '<select name="newavatar" onchange="document.images.avatarpic.src=this[this.selectedIndex].value;">'.$avatars.'</select>';
            eval('$avatar = "'.template('memcp_profile_avatarlist').'";');
            closedir($dir1);
        }

        $member['icq'] = ($member['icq'] > 0) ? $member['icq'] : '';
        eval('echo "'.template('memcp_profile').'";');
    }

    if (onSubmit('editsubmit')) {
        $newemail = postedVar('newemail', '', FALSE, FALSE);
        if ($newemail && (!$newemail || isset($_GET['newemail']))) {
            $auditaction = $_SERVER['REQUEST_URI'];
            $aapos = strpos($auditaction, "?");
            if ($aapos !== false) {
                $auditaction = substr($auditaction, $aapos + 1);
            }
            $auditaction = addslashes("$onlineip|#|$auditaction");
            audit($xmbuser, $auditaction, 0, 0, "Potential XSS exploit using newemail");
            die("Hack atttempt recorded in audit logs.");
        }

        $newpassword = postedVar('newpassword', '', FALSE, FALSE);
        $newpasswordcf = postedVar('newpasswordcf', '', FALSE, FALSE);
        if ($newpassword && (!$newpassword || isset($_GET['newpassword']))) {
            $auditaction = $_SERVER['REQUEST_URI'];
            $aapos = strpos($auditaction, "?");
            if ($aapos !== false) {
                $auditaction = substr($auditaction, $aapos + 1);
            }
            $auditaction = addslashes("$onlineip|#|$auditaction");
            audit($xmbuser, $auditaction, 0, 0, "Potential XSS exploit using newpassword");
            die("Hack atttempt recorded in audit logs.");
        }

        $langfilenew = postedVar('langfilenew', '', FALSE, FALSE);
        $fileNameHash = getLangFileNameFromHash($langfilenew);
        if ($fileNameHash === false) {
            $langfilenew = $SETTINGS['langfile'];
        } else {
            $langfilenew = basename($fileNameHash);
        }
        $langfilenew = $db->escape($langfilenew);

        $timeoffset1 = isset($_POST['timeoffset1']) && is_numeric($_POST['timeoffset1']) ? $_POST['timeoffset1'] : 0;
        $thememem = formInt('thememem');
        $tppnew = isset($_POST['tppnew']) ? (int) $_POST['tppnew'] : $SETTINGS['topicperpage'];
        $pppnew = isset($_POST['pppnew']) ? (int) $_POST['pppnew'] : $SETTINGS['postperpage'];

        $dateformatnew = postedVar('dateformatnew', '', FALSE, TRUE);
        $dateformattest = attrOut($dateformatnew, 'javascript');  // NEVER allow attribute-special data in the date format because it can be unescaped using the date() parser.
        if (strlen($dateformatnew) == 0 Or $dateformatnew != $dateformattest) {
            $dateformatnew = $SETTINGS['dateformat'];
        }
        unset($dateformattest);

        $timeformatnew = formInt('timeformatnew');
        if ($timeformatnew != 12 And $timeformatnew != 24) {
            $timeformatnew = $SETTINGS['timeformat'];
        }

        $saveogu2u = formYesNo('saveogu2u');
        $emailonu2u = formYesNo('emailonu2u');
        $useoldu2u = formYesNo('useoldu2u');
        $invisible = formInt('newinv');
        $showemail = formYesNo('newshowemail');
        $newsletter = formYesNo('newnewsletter');
        $year = formInt('year');
        $month = formInt('month');
        $day = formInt('day');
        $bday = iso8601_date($year, $month, $day);
        $avatar = postedVar('newavatar', 'javascript', TRUE, TRUE, TRUE);
        $newavatarcheck = postedVar('newavatarcheck');
        $location = postedVar('newlocation', 'javascript', TRUE, TRUE, TRUE);
        $icq = postedVar('newicq', '', FALSE, FALSE);
        $icq = ($icq && is_numeric($icq) && $icq > 0) ? $icq : 0;
        $yahoo = postedVar('newyahoo', 'javascript', TRUE, TRUE, TRUE);
        $aim = postedVar('newaim', 'javascript', TRUE, TRUE, TRUE);
        $msn = postedVar('newmsn', 'javascript', TRUE, TRUE, TRUE);
        $email = postedVar('newemail', 'javascript', TRUE, TRUE, TRUE);
        $site = postedVar('newsite', 'javascript', TRUE, TRUE, TRUE);
        $bio = postedVar('newbio', 'javascript', TRUE, TRUE, TRUE);
        $mood = postedVar('newmood', 'javascript', TRUE, TRUE, TRUE);
        $sig = postedVar('newsig', 'javascript', ($SETTINGS['sightml']=='off'), TRUE, TRUE);

        if ($SETTINGS['doublee'] == 'off' && false !== strpos($email, "@")) {
            $query = $db->query("SELECT COUNT(uid) FROM ".X_PREFIX."members WHERE email = '$email' AND username != '$xmbuser'");
            $count1 = $db->result($query,0);
            $db->free_result($query);
            if ($count1 != 0) {
                error($lang['alreadyreg']);
            }
        }

        $efail = false;
        $query = $db->query("SELECT * FROM ".X_PREFIX."restricted");
        while($restriction = $db->fetch_array($query)) {
            $t_email = $email;
            if ($restriction['case_sensitivity'] == 1) {
                $t_email = strtolower($t_email);
                $restriction['name'] = strtolower($restriction['name']);
            }

            if ($restriction['partial'] == 1) {
                if (strpos($t_email, $restriction['name']) !== false) {
                    $efail = true;
                }
            } else {
                if ($t_email == $restriction['name']) {
                    $efail = true;
                }
            }
        }
        $db->free_result($query);

        if ($efail) {
            error($lang['emailrestricted']);
        }

        if ($SETTINGS['resetsigs'] == 'on') {
            if (strlen(trim($self['sig'])) == 0) {
                if (strlen($sig) > 0) {
                    $db->query("UPDATE ".X_PREFIX."posts SET usesig='yes' WHERE author='".$self['username']."'");
                }
            } else {
                if (strlen(trim($sig)) == 0) {
                    $db->query("UPDATE ".X_PREFIX."posts SET usesig='no' WHERE author='".$self['username']."'");
                }
            }
        }

        $max_size = explode('x', $SETTINGS['max_avatar_size']);
        if (ini_get('allow_url_fopen')) {
            if ($max_size[0] > 0 && $max_size[1] > 0) {
                $size = @getimagesize($_POST['newavatar']);
                if ($size === false) {
                    $avatar = '';
                } else if ((($size[0] > $max_size[0] && $max_size[0] > 0) || ($size[1] > $max_size[1] && $max_size[1] > 0)) && !X_SADMIN) {
                    error($lang['avatar_too_big'] . $SETTINGS['max_avatar_size'] . 'px', false);
                }
            }
        } else if ($newavatarcheck == "no") {
            $avatar = '';
        }

        if ($_POST['newpassword'] != '' || $_POST['newpasswordcf'] != '') {
            if ($_POST['newpassword'] != $_POST['newpasswordcf']) {
                error($lang['pwnomatch'], false);
            }

            $newpassword = md5($_POST['newpassword']);

            $pwtxt = "password='$newpassword',";

            $currtime = $onlinetime - (86400*30);
            put_cookie("xmbuser", $self['username'], $currtime, $cookiepath, $cookiedomain);
            put_cookie("xmbpw", $newpassword, $currtime, $cookiepath, $cookiedomain);
        } else {
            $pwtxt = '';
        }

        $db->query("UPDATE ".X_PREFIX."members SET $pwtxt email='$email', site='$site', aim='$aim', location='$location', bio='$bio', sig='$sig', showemail='$showemail', timeoffset='$timeoffset1', icq='$icq', avatar='$avatar', yahoo='$yahoo', theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew', newsletter='$newsletter', timeformat='$timeformatnew', msn='$msn', dateformat='$dateformatnew', mood='$mood', invisible='$invisible', saveogu2u='$saveogu2u', emailonu2u='$emailonu2u', useoldu2u='$useoldu2u' WHERE username='$xmbuser'");

        message($lang['usercpeditpromsg'], false, '', '', 'memcp.php', true, false, true);
    }
} else if ($action == 'favorites') {
    eval('echo "'.template('header').'";');
    makenav($action);

    $favadd = getInt('favadd');
    if (noSubmit('favsubmit') && $favadd) {
        if ($favadd == 0) {
            error($lang['fnasorry'], false);
        }

        $query = $db->query("SELECT tid FROM ".X_PREFIX."favorites WHERE tid='$favadd' AND username='$xmbuser' AND type='favorite'");
        $favthread = $db->fetch_array($query);
        $db->free_result($query);

        if ($favthread) {
            error($lang['favonlistmsg'], false);
        }

        $db->query("INSERT INTO ".X_PREFIX."favorites (tid, username, type) VALUES ('$favadd', '$xmbuser', 'favorite')");
        message($lang['favaddedmsg'], false, '', '', 'memcp.php?action=favorites', true, false, true);
    }

    if (!$favadd && noSubmit('favsubmit')) {
        $query = $db->query("SELECT f.*, t.fid, t.icon, t.lastpost, t.subject, t.replies FROM ".X_PREFIX."favorites f, ".X_PREFIX."threads t WHERE f.tid=t.tid AND f.username='$xmbuser' AND f.type='favorite' ORDER BY t.lastpost DESC");
        $favnum = 0;
        $favs = '';
        $tmOffset = ($timeoffset * 3600) + ($addtime * 3600);
        while($fav = $db->fetch_array($query)) {
            $query2 = $db->query("SELECT name, fup, fid FROM ".X_PREFIX."forums WHERE fid='$fav[fid]'");
            $forum = $db->fetch_array($query2);
            $db->free_result($query2);
            $forum['name'] = fnameOut($forum['name']);

            $lastpost = explode('|', $fav['lastpost']);
            $dalast = $lastpost[0];
            $lastpost[1] = '<a href="member.php?action=viewpro&amp;member='.recodeOut($lastpost[1]).'">'.$lastpost[1].'</a>';
            $lastreplydate = gmdate($dateformat, $lastpost[0] + $tmOffset);
            $lastreplytime = gmdate($timecode, $lastpost[0] + $tmOffset);
            $lastpost = $lang['lastreply1'].' '.$lastreplydate.' '.$lang['textat'].' '.$lastreplytime.' '.$lang['textby'].' '.$lastpost[1];
            $fav['subject'] = rawHTMLsubject(stripslashes($fav['subject']));

            if ($fav['icon'] != '') {
                $fav['icon'] = '<img src="'.$smdir.'/'.$fav['icon'].'" alt="" border="0" />';
            } else {
                $fav['icon'] = '';
            }

            $favnum++;
            eval('$favs .= "'.template('memcp_favs_row').'";');
        }

        $favsbtn = '';
        if ($favnum != 0) {
            eval('$favsbtn = "'.template('memcp_favs_button').'";');
        }

        if ($favnum == 0) {
            eval('$favs = "'.template('memcp_favs_none').'";');
        }
        $db->free_result($query);
        eval('echo "'.template('memcp_favs').'";');
    }

    if (!$favadd && onSubmit('favsubmit')) {
        $query = $db->query("SELECT tid FROM ".X_PREFIX."favorites WHERE username='$xmbuser' AND type='favorite'");
        while($fav = $db->fetch_array($query)) {
            $delete = formInt('delete'.$fav['tid']);
            $db->query("DELETE FROM ".X_PREFIX."favorites WHERE username='$xmbuser' AND tid='$delete' AND type='favorite'");
        }
        $db->free_result($query);

        message($lang['favsdeletedmsg'], false, '', '', 'memcp.php?action=favorites', true, false, true);
    }
} else if ($action == 'subscriptions') {
    eval('echo "'.template('header').'";');
    makenav($action);

    $subadd = getInt('subadd');
    if (!$subadd && noSubmit('subsubmit')) {
        $query = $db->query("SELECT f.*, t.fid, t.icon, t.lastpost, t.subject, t.replies FROM ".X_PREFIX."favorites f, ".X_PREFIX."threads t WHERE f.tid=t.tid AND f.username='$xmbuser' AND f.type='subscription' ORDER BY t.lastpost DESC");
        $subnum = 0;
        $subscriptions = '';
        $tmOffset = ($timeoffset * 3600) + ($addtime * 3600);
        while($fav = $db->fetch_array($query)) {
            $query2 = $db->query("SELECT name, fup, fid FROM ".X_PREFIX."forums WHERE fid='$fav[fid]'");
            $forum = $db->fetch_array($query2);
            $db->free_result($query2);
            $forum['name'] = fnameOut($forum['name']);

            $lastpost = explode('|', $fav['lastpost']);
            $dalast = $lastpost[0];
            $lastpost['1'] = '<a href="member.php?action=viewpro&amp;member='.recodeOut($lastpost[1]).'">'.$lastpost[1].'</a>';
            $lastreplydate = gmdate($dateformat, $lastpost[0] + $tmOffset);
            $lastreplytime = gmdate($timecode, $lastpost[0] + $tmOffset);
            $lastpost = $lang['lastreply1'].' '.$lastreplydate.' '.$lang['textat'].' '.$lastreplytime.' '.$lang['textby'].' '.$lastpost[1];
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
        eval('echo "'.template('memcp_subscriptions').'";');
    } else if ($subadd && noSubmit('subsubmit')) {
        $query = $db->query("SELECT COUNT(tid) FROM ".X_PREFIX."favorites WHERE tid='$subadd' AND username='$xmbuser' AND type='subscription'");
        if ($db->result($query,0) == 1) {
            $db->free_result($query);
            error($lang['subonlistmsg'], false);
        } else {
            $db->query("INSERT INTO ".X_PREFIX."favorites (tid, username, type) VALUES ('$subadd', '$xmbuser', 'subscription')");
            message($lang['subaddedmsg'], false, '', '', 'memcp.php?action=subscriptions', true, false, true);
        }
    } else if (!$subadd && onSubmit('subsubmit')) {
        $query = $db->query("SELECT tid FROM ".X_PREFIX."favorites WHERE username='$xmbuser' AND type='subscription'");
        while($sub = $db->fetch_array($query)) {
            $delete = formInt('delete'.$sub['tid']);
            $db->query("DELETE FROM ".X_PREFIX."favorites WHERE username='$xmbuser' AND tid='$delete' AND type='subscription'");
        }
        $db->free_result($query);
        message($lang['subsdeletedmsg'], false, '', '', 'memcp.php?action=subscriptions', true, false, true);
    }
} else {
    eval('echo "'.template('header').'";');
    eval($lang['evalusercpwelcome']);
    makenav($action);

    $q = $db->query("SELECT b.buddyname, w.invisible, w.username FROM ".X_PREFIX."buddys b LEFT JOIN ".X_PREFIX."whosonline w ON (b.buddyname=w.username) WHERE b.username='$xmbuser'");
    $buddys = array();
    $buddys['offline'] = '';
    $buddys['online'] = '';
    if (X_ADMIN) {
        while($buddy = $db->fetch_array($q)) {
            $recodename = recodeOut($buddy['buddyname']);
            if (strlen($buddy['username']) > 0) {
                if ($buddy['invisible'] == 1) {
                   $buddystatus = $lang['hidden'];
                } else {
                    $buddystatus = $lang['textonline'];
                }
                eval("\$buddys['online'] .= \"".template("buddylist_buddy_online")."\";");
            } else {
                eval("\$buddys['offline'] .= \"".template("buddylist_buddy_offline")."\";");
            }
        }
        $db->free_result($q);
    } else {
        while($buddy = $db->fetch_array($q)) {
            if (strlen($buddy['username']) > 0) {
                if ($buddy['invisible'] == 1) {
                   eval("\$buddys[offline] .= \"".template("buddylist_buddy_offline")."\";");
                   continue;
                } else {
                    $buddystatus = $lang['textonline'];
                }
                eval("\$buddys['online'] .= \"".template("buddylist_buddy_online")."\";");
            } else {
                eval("\$buddys['offline'] .= \"".template("buddylist_buddy_offline")."\";");
            }
        }
        $db->free_result($q);
    }

    $query = $db->query("SELECT * FROM ".X_PREFIX."members WHERE username='$xmbuser'");
    $member = $db->fetch_array($query);
    $db->free_result($query);

    if ($member['avatar'] == '') {
        $member['avatar'] = '';
    } else {
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
    $tmOffset = ($timeoffset * 3600) + ($addtime * 3600);
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

    $query2 = $db->query("SELECT * FROM ".X_PREFIX."favorites f, ".X_PREFIX."threads t, ".X_PREFIX."posts p WHERE f.tid=t.tid AND p.tid=t.tid AND p.subject=t.subject AND f.username='$xmbuser' AND f.type='favorite' ORDER BY t.lastpost DESC LIMIT 0,5");
    $favnum = $db->num_rows($query2);
    $favs = '';
    $tmOffset = ($timeoffset * 3600) + ($addtime * 3600);
    while($fav = $db->fetch_array($query2)) {
        $query = $db->query("SELECT name, fup, fid FROM ".X_PREFIX."forums WHERE fid='$fav[fid]'");
        $forum = $db->fetch_array($query);
        $db->free_result($query);
        $forum['name'] = fnameOut($forum['name']);

        $lastpost = explode('|', $fav['lastpost']);
        $dalast = $lastpost[0];
        $lastpost[1] = '<a href="member.php?action=viewpro&amp;member='.recodeOut($lastpost[1]).'">'.$lastpost[1].'</a>';
        $lastreplydate = gmdate($dateformat, $lastpost[0] + $tmOffset);
        $lastreplytime = gmdate($timecode, $lastpost[0] + $tmOffset);
        $lastpost = $lang['lastreply1'].' '.$lastreplydate.' '.$lang['textat'].' '.$lastreplytime.' '.$lang['textby'].' '.$lastpost[1];
        $fav['subject'] = rawHTMLsubject(stripslashes($fav['subject']));

        if ($fav['icon'] != '') {
            $fav['icon'] = '<img src="'.$smdir.'/'.$fav['icon'].'" alt="" border="0" />';
        } else {
            $fav['icon'] = '';
        }
        eval('$favs .= "'.template('memcp_home_favs_row').'";');
    }

    if ($favnum == 0) {
        eval('$favs = "'.template('memcp_home_favs_none').'";');
    }
    $db->free_result($query2);
    eval('echo "'.template('memcp_home').'";');
}

end_time();
eval('echo "'.template('footer').'";');
?>
