<?php
/**
 * eXtreme Message Board
 * XMB 1.9.8 Engage Final SP3
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

$userid = $db->fetch_array($db->query("SELECT uid FROM ".X_PREFIX."members WHERE username='$user'"));
if (empty($userid['uid'])) {
    error($lang['nomember'], false);
}

if (noSubmit('editsubmit')) {
    $query = $db->query("SELECT * FROM ".X_PREFIX."members WHERE username='$user'");
    $member = $db->fetch_array($query);

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
    if ($member['invisible'] == 1 ) {
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

    $day = substr($member['bday'], 8, 2);
    $month = substr($member['bday'], 5, 2);
    $year = substr($member['bday'], 0, 4);

    $sel0 = $sel1 = $sel2 = $sel3 = $sel4 = $sel5 = $sel6 = '';
    $sel7 = $sel8 = $sel9 = $sel10 = $sel11 = $sel12 = '';

    ${'sel'.(int)$month} = $selHTML;

    $dayselect = array();
    $dayselect[] = '<select name="day">';
    $dayselect[] = '<option value="">&nbsp;</option>';
    for($num = 1; $num <= 31; $num++) {
        if ($day == $num) {
            $dayselect[] = '<option value='.$num.'" '.$selHTML.'>'.$num.'</option>';
        } else {
            $dayselect[] = '<option value='.$num.'">'.$num.'</option>';
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

    $lang['searchusermsg'] = str_replace('*USER*', $user, $lang['searchusermsg']);

    $member['icq'] = ($member['icq'] > 0) ? $member['icq'] : '';

    eval('echo stripslashes("'.template('admintool_editprofile').'");');
} else {
    $query = $db->query("SELECT * FROM ".X_PREFIX."members WHERE username='$user'");
    $member = $db->fetch_array($query);

    if (!$member['username']) {
        error($lang['badname'], false);
    }

    $langfilenew = getLangFileNameFromHash(formVar('langfilenew'));
    $fileNameHash = getLangFileNameFromHash($langfilenew);
    if ($fileNameHash === false) {
        $langfilenew = $SETTINGS['langfile'];
    } else {
        $langfilenew = basename($fileNameHash);
    }

    $timeoffset1 = isset($_POST['timeoffset1']) && is_numeric($_POST['timeoffset1']) ? $_POST['timeoffset1'] : 0;
    $thememem = formInt('thememem');
    $tppnew = isset($_POST['tppnew']) ? (int) $_POST['tppnew'] : $SETTINGS['topicperpage'];
    $pppnew = isset($_POST['pppnew']) ? (int) $_POST['pppnew'] : $SETTINGS['postperpage'];

    $dateformatnew = formVar('dateformatnew');
    if (strlen($dateformatnew) == 0) {
        $dateformatnew = $SETTINGS['dateformat'];
    } else {
        $dateformatnew = isset($dateformatnew) ? checkInput($dateformatnew, '', '', 'script', true) : $SETTINGS['dateformat'];
    }

    $timeformatnew = formInt('timeformatnew');
    $timeformatnew = $timeformatnew ? checkInput($timeformatnew, '', '', 'script', true) : $SETTINGS['timeformat'];
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
    $newavatar = formVar('newavatar');
    $avatar = $newavatar ? checkInput($newavatar, 'no', 'yes', 'javascript', false) : '';
    $newlocation = formVar('newlocation');
    $location = $newlocation ? checkInput($newlocation, 'no', 'yes', 'javascript', false) : '';
    $newicq = formVar('newicq');
    $icq = ($newicq && is_numeric($newicq) && $newicq > 0) ? $newicq : 0;
    $newyahoo = formVar('newyahoo');
    $yahoo = $newyahoo ? checkInput($newyahoo, 'no', 'yes', 'javascript', false) : '';
    $newaim = formVar('newaim');
    $aim = $newaim ? checkInput($newaim, 'no', 'yes', 'javascript', false) : '';
    $newmsn = formVar('newmsn');
    $msn = $newmsn ? checkInput($newmsn, 'no', 'yes', 'javascript', false) : '';
    $newemail = formVar('newemail');
    $email = $newemail ? checkInput($newemail, 'no', 'yes', 'javascript', false) : '';
    $newsite = formVar('newsite');
    $site = $newsite ? checkInput($newsite, 'no', 'yes', 'javascript', false) : '';
    $bio = isset($_POST['newbio']) ? checkInput($_POST['newbio'], 'no', 'no', 'javascript', false) : '';
    $mood = isset($_POST['newmood']) ? checkInput($_POST['newmood'], 'no', 'no', 'javascript', false) : '';
    $sig = isset($_POST['newsig']) ? checkInput($_POST['newsig'], '', $SETTINGS['sightml'], '', false) : '';

    $avatar = addslashes($avatar);
    $location = addslashes($location);
    $yahoo = addslashes($yahoo);
    $aim = addslashes($aim);
    $msn = addslashes($msn);
    $email = addslashes($email);
    $site = addslashes($site);
    $bio = addslashes($bio);
    $mood = addslashes($mood);
    $sig = addslashes($sig);

    $max_size = explode('x', $SETTINGS['max_avatar_size']);
    if ($max_size[0] > 0 && $max_size[1] > 0 && ini_get('allow_url_fopen')) {
        $size = @getimagesize($_POST['newavatar']);
        if ($size === false) {
            $avatar = '';
        } else if (($size[0] > $max_size[0] && $max_size[0] > 0) || ($size[1] > $max_size[1] && $max_size[1] > 0) && !X_SADMIN) {
            error($lang['avatar_too_big'] . $SETTINGS['max_avatar_size'] . 'px', false);
        }
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
