<?php
/**
 * XMB 1.9.5 Nexus Final SP1
 * © 2007 John Briggs
 * http://www.xmbmods.com
 * john@xmbmods.com
 *
 * Developed By The XMB Group
 * Copyright (c) 2001-2007, The XMB Group
 * http://www.xmbforum.com
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 **/

require_once('header.php');

loadtemplates('memcp_profile_avatarurl','memcp_profile_avatarlist','admintool_editprofile');

nav('<a href="./cp.php">'.$lang['textcp'].'</a>');
nav($lang['texteditpro']);

eval("\$css = \"".template("css")."\";");

eval("echo (\"".template('header')."\");");

if (!X_SADMIN) {
    error($lang['superadminonly'], false);
}

$userid = $db->fetch_array($db->query("SELECT uid FROM $table_members WHERE username='$user'"));
if (empty($userid['uid'])) {
    error($lang['nomember'], false);
}

if (!isset($_POST['editsubmit'])) {
    $query = $db->query("SELECT * FROM $table_members WHERE username='".rawurldecode($user)."'");
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
    $query = $db->query("SELECT themeid, name FROM $table_themes ORDER BY name ASC");
    while ($themeinfo = $db->fetch_array($query)) {
        if ($themeinfo['themeid'] == $member['theme']) {
            $themelist[] = '<option value="'.intval($themeinfo['themeid']).'" '.$selHTML.'>'.stripslashes($themeinfo['name']).'</option>';
        } else {
            $themelist[] = '<option value="'.intval($themeinfo['themeid']).'">'.stripslashes($themeinfo['name']).'</option>';
        }
    }
    $themelist[] = '</select>';
    $themelist = implode("\n", $themelist);

    $langfileselect = createLangFileSelect($member['langfile']);

    $day   = substr($member['bday'], 8, 2);
    $month = substr($member['bday'], 5, 2);
    $year  = substr($member['bday'], 0, 4);

    $sel0 = $sel1 = $sel2 = $sel3 = $sel4 = $sel5 = $sel6 = '';
    $sel7 = $sel8 = $sel9 = $sel10 = $sel11 = $sel12 = '';

    ${'sel'.(int)$month} = $selHTML;

    $dayselect = array();
    $dayselect[] = '<select name="day">';
    $dayselect[] = '<option value="">&nbsp;</option>';
    for ($num = 1; $num <= 31; $num++) {
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

    if ($SETTINGS['avastatus'] == 'on') {
        eval('$avatar = "'.template('memcp_profile_avatarurl').'";');
    } else {
        $avatar = '';
    }

    if ($SETTINGS['avastatus'] == 'list')  {
        $avatars = '<option value="" />'.$lang['textnone'].'</option>';
        $dir1 = opendir(ROOT.'images/avatars');
        while ($avatar1 = readdir($dir1)) {
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
    $query  = $db->query("SELECT * FROM $table_members WHERE username='$user'");
    $member = $db->fetch_array($query);

    if (!$member['username']) {
        error($lang['badname'], false);
    }

    $fileNameHash = getLangFileNameFromHash($langfilenew);
    if ($fileNameHash === false) {
        $langfilenew = $SETTINGS['langfile'];
    } else {
        $langfilenew = basename($fileNameHash);
    }

    $timeoffset1    = isset($timeoffset1) ? (float) $timeoffset1 : 0;
    $thememem       = isset($thememem) ? (int) $thememem : 0;
    $tppnew         = isset($tppnew) ? (int) $tppnew : $topicperpage;
    $pppnew         = isset($pppnew) ? (int) $pppnew : $postperpage;

    if (strlen(trim($dateformatnew)) == 0) {
        $dateformatnew = $SETTINGS['dateformat'];
    } else {
        $dateformatnew  = isset($dateformatnew) ? checkInput($dateformatnew, '', '', 'script', true) : $SETTINGS['dateformat'];
    }

    $timeformatnew  = isset($timeformatnew) ? checkInput($timeformatnew, '', '', 'script', true) : $SETTINGS['timeformat'];

    $saveogu2u      = (isset($saveogu2u) && $saveogu2u == 'yes') ? 'yes' : 'no';
    $emailonu2u     = (isset($emailonu2u) && $emailonu2u == 'yes') ? 'yes' : 'no';
    $useoldu2u      = (isset($useoldu2u) && $useoldu2u == 'yes') ? 'yes' : 'no';
    $invisible      = (isset($newinv) && $newinv == 1) ? 1 : 0;
    $showemail      = (isset($newshowemail) && $newshowemail == 'yes') ? 'yes' : 'no';
    $newsletter     = (isset($newnewsletter) && $newnewsletter == 'yes') ? 'yes' : 'no';

    $bday           = iso8601_date($year, $month, $day);

    $newavatar      = isset($newavatar) ? ereg_replace(' ', '%20', $newavatar) : '';
    $avatar         = checkInput($newavatar, 'no', 'no', 'javascript', false);
    $location       = isset($newlocation) ? checkInput($newlocation, 'no', 'no', 'javascript', false) : '';
    $icq            = (isset($newicq) && is_numeric($newicq) && $newicq > 0) ? $newicq : 0;
    $yahoo          = isset($newyahoo) ? checkInput($newyahoo, 'no', 'no', 'javascript', false) : '';
    $aim            = isset($newaim) ? checkInput($newaim, 'no', 'no', 'javascript', false) : '';
    $msn            = isset($newmsn) ? checkInput($newmsn, 'no', 'no', 'javascript', false) : '';
    $email          = isset($newemail) ? checkInput($newemail, 'no', 'no', 'javascript', false) : '';
    $site           = isset($newsite) ? checkInput($newsite, 'no', 'no', 'javascript', false) : '';
    $webcam         = isset($newwebcam) ? checkInput($newwebcam, 'no', 'no', 'javascript', false) : '';
    $bio            = isset($newbio) ? checkInput($newbio, 'no', 'no', 'javascript', false) : '';
    $mood           = isset($newmood) ? checkInput($newmood, 'no', 'no', 'javascript', false) : '';
    $sig            = isset($newsig) ? checkInput($newsig, '', $SETTINGS['sightml'], '', false) : '';

    $avatar         = addslashes($avatar);
    $location       = addslashes($location);
    $yahoo          = addslashes($yahoo);
    $aim            = addslashes($aim);
    $email          = addslashes($email);
    $site           = addslashes($site);
    $webcam         = addslashes($webcam);
    $bio            = addslashes($bio);
    $mood           = addslashes($mood);
    $sig            = addslashes($sig);

    $max_size = explode('x', $SETTINGS['max_avatar_size']);
    if ($max_size[0] > 0 && $max_size[1] > 0 && substr_count($avatar, ',') < 2) {
        $size = @getimagesize($avatar);
        if ($size === false) {
            $avatar = '';
        } elseif (($size[0] > $max_size[0] && $max_size[0] > 0) || ($size[1] > $max_size[1] && $max_size[1] > 0) && !X_SADMIN) {
            error($lang['avatar_too_big'] . $SETTINGS['max_avatar_size'] . 'px', false);
        }
    }

    $db->query("UPDATE $table_members SET email='$email', site='$site', aim='$aim', location='$location', bio='$bio', sig='$sig', showemail='$showemail', timeoffset='$timeoffset1', icq='$icq', avatar='$avatar', yahoo='$yahoo', theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew', newsletter='$newsletter', timeformat='$timeformatnew', msn='$msn', dateformat='$dateformatnew', mood='$mood', invisible='$invisible', saveogu2u='$saveogu2u', emailonu2u='$emailonu2u', useoldu2u='$useoldu2u', webcam='$webcam' WHERE username='$user'");

    $newpassword = trim($newpassword);
    if ($newpassword != '') {
        $newpassword = md5($newpassword);
        $db->query("UPDATE $table_members SET password='$newpassword' WHERE username='$user'");
    }

    echo '<div align="center"><span class="mediumtxt">'.$lang['adminprofilechange'].'</span></div>';
    redirect('cp.php', 2, X_REDIRECT_JS);
}

end_time();
eval("echo (\"".template('footer')."\");");
?>