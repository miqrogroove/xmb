<?php
/**
 * eXtreme Message Board
 * XMB 1.9.10 Karl
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

define('X_SCRIPT', 'editprofile.php');

require 'header.php';

loadtemplates(
'memcp_profile_avatarurl',
'memcp_profile_avatarlist',
'admintool_editprofile'
);

nav('<a href="./cp.php">'.$lang['textcp'].'</a>');
nav($lang['texteditpro']);

eval('$css = "'.template('css').'";');

eval('echo "'.template('header').'";');

if (!X_SADMIN) {
    error($lang['superadminonly'], false);
}

$user = postedVar('user', '', TRUE, TRUE, FALSE, 'g');

$query = $db->query("SELECT * FROM ".X_PREFIX."members WHERE username='$user'");
if ($db->num_rows($query) != 1) {
    error($lang['nomember'], false);
}
$member = $db->fetch_array($query);

if (noSubmit('editsubmit')) {
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

    $registerdate = gmdate($dateformat, $member['regdate'] + ($addtime * 3600) + ($timeoffset * 3600));
    $lastlogdate = gmdate($timecode, $member['lastvisit'] + ($addtime * 3600) + ($timeoffset * 3600));

    $currdate = gmdate($timecode, $onlinetime + ($addtime * 3600));
    eval($lang['evaloffset']);

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

    $lang['searchusermsg'] = str_replace('*USER*', $member['username'], $lang['searchusermsg']);

    $member['icq'] = ($member['icq'] > 0) ? $member['icq'] : '';

    $userrecode = recodeOut($member['username']);

    $template = template_secure('admintool_editprofile', 'edpro', $member['uid']);
    eval('echo "'.$template.'";');
} else {
    request_secure('edpro', $member['uid'], X_NONCE_FORM_EXP);
    $langfilenew = postedVar('langfilenew', '', FALSE, FALSE);
    $langfilenew = getLangFileNameFromHash($langfilenew);
    if ($langfilenew === false) {
        $langfilenew = $SETTINGS['langfile'];
    } else {
        $langfilenew = basename($langfilenew);
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

    $max_size = explode('x', $SETTINGS['max_avatar_size']);
    if (ini_get('allow_url_fopen')) {
        if ($max_size[0] > 0 && $max_size[1] > 0) {
            $size = @getimagesize($_POST['newavatar']);
            if ($size === false) {
                $avatar = '';
            } else if (($size[0] > $max_size[0] && $max_size[0] > 0) || ($size[1] > $max_size[1] && $max_size[1] > 0) && !X_SADMIN) {
                error($lang['avatar_too_big'] . $SETTINGS['max_avatar_size'] . 'px', false);
            }
        }
    } else if ($newavatarcheck == "no") {
        $avatar = '';
    }

    $db->query("UPDATE ".X_PREFIX."members SET email='$email', site='$site', aim='$aim', location='$location', bio='$bio', sig='$sig', showemail='$showemail', timeoffset='$timeoffset1', icq='$icq', avatar='$avatar', yahoo='$yahoo', theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew', newsletter='$newsletter', timeformat='$timeformatnew', msn='$msn', dateformat='$dateformatnew', mood='$mood', invisible='$invisible', saveogu2u='$saveogu2u', emailonu2u='$emailonu2u', useoldu2u='$useoldu2u' WHERE username='$user'");
    $newpassword = $_POST['newpassword'];
    if ($newpassword) {
        $newpassword = md5($newpassword);
        $db->query("UPDATE ".X_PREFIX."members SET password='$newpassword' WHERE username='$user'");
    }

    echo '<div align="center"><span class="mediumtxt">'.$lang['adminprofilechange'].'</span></div>';
    redirect('cp.php', 2, X_REDIRECT_JS);
}

end_time();
eval('echo "'.template('footer').'";');
?>
