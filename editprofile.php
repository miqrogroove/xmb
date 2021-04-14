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
$member = \XMB\SQL\getMemberByName( $rawuser );

if ( empty( $member ) ) {
    error($lang['nomember']);
}

$member['password'] = '';

$user = $db->escape( $rawuser );

$https_only = 'on' == $SETTINGS['images_https_only'];
$js_https_only = $https_only ? 'true' : 'false';

if (noSubmit('editsubmit')) {
    $sadminselect = $adminselect = $smodselect = '';
    $modselect = $memselect = $banselect = '';
    switch($member['status']) {
    case 'Super Administrator':
        $sadminselect = $selHTML;
        break;
    case 'Administrator':
        $adminselect = $selHTML;
        break;
    case 'Super Moderator':
        $smodselect = $selHTML;
        break;
    case 'Moderator':
        $modselect = $selHTML;
        break;
    case 'Member':
        $memselect = $selHTML;
        break;
    case 'Banned':
        $banselect = $selHTML;
        break;
    default:
        $memselect = $selHTML;
        break;
    }

    $custout = attrOut($member['customstatus']);

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

    $registerdate = gmdate($dateformat, $member['regdate'] + ($SETTINGS['addtime'] * 3600) + ($timeoffset * 3600));

    if ( 0 == (int) $member['lastvisit'] ) {
        $lastlogdate = $lang['textpendinglogin'];
    } else {
        $lastvisitdate = gmdate($dateformat, $member['lastvisit'] + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600));
        $lastvisittime = gmdate($timecode, $member['lastvisit'] + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600));
        $lastlogdate = $lastvisitdate.' '.$lang['textat'].' '.$lastvisittime;
    }

    $loginfails = $member['bad_login_count'];
    if ( 0 == (int) $loginfails ) {
        $loginfails = $lang['textnone'];
        $loginfaildate = $lang['textnone'];
    } else {
        $loginfaildate = gmdate( $dateformat, $member['bad_login_date'] + ($SETTINGS['addtime'] * 3600) + ($timeoffset * 3600) );
        $loginfailtime = gmdate( $timecode, $member['bad_login_date'] + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600) );
        $loginfaildate = $loginfaildate.' '.$lang['textat'].' '.$loginfailtime;
    }

    $sessfails = $member['bad_session_count'];
    if ( 0 == (int) $sessfails ) {
        $sessfails = $lang['textnone'];
        $sessfaildate = $lang['textnone'];
    } else {
        $sessfaildate = gmdate( $dateformat, $member['bad_session_date'] + ($SETTINGS['addtime'] * 3600) + ($timeoffset * 3600) );
        $sessfailtime = gmdate( $timecode, $member['bad_session_date'] + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600) );
        $sessfaildate = $sessfaildate.' '.$lang['textat'].' '.$sessfailtime;
    }

    $guess_limit = 10;
    $lockout_timer = 3600 * 2;
    
    if ( (int) $member['bad_login_count'] >= $guess_limit && time() < (int) $member['bad_login_date'] + $lockout_timer ) {
        $loginfaildate .= "<br />\n{$lang['editprofile_lockout']} <input type='checkbox' name='unlock' value='yes' />";
    }

    $currdate = gmdate($timecode, $onlinetime + ($SETTINGS['addtime'] * 3600));
    $textoffset = str_replace( '$currdate', $currdate, $lang['evaloffset'] );

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

    $lang['searchusermsg'] = str_replace('*USER*', $member['username'], $lang['searchusermsg']);

    $member['icq'] = ($member['icq'] > 0) ? $member['icq'] : '';
    $member['bio'] = decimalEntityDecode($member['bio']);
    $member['location'] = decimalEntityDecode($member['location']);
    $member['mood'] = decimalEntityDecode($member['mood']);
    $member['sig'] = decimalEntityDecode($member['sig']);

    $userrecode = recodeOut($member['username']);

    $template = template_secure( 'admintool_editprofile', 'Edit User Account', $member['uid'], X_NONCE_FORM_EXP );
    eval('$editpage = "'.$template.'";');
} else {
    request_secure( 'Edit User Account', $member['uid'] );
    $status = postedVar('status');
    $origstatus = $member['status'];
    $query = $db->query("SELECT COUNT(uid) FROM ".X_PREFIX."members WHERE status='Super Administrator'");
    $sa_count = (int) $db->result($query, 0);
    $db->free_result($query);
    if ($origstatus == 'Super Administrator' && $status != 'Super Administrator' && $sa_count == 1) {
        error($lang['lastsadmin']);
    }
    $cusstatus = postedVar('cusstatus', '', FALSE);
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
    $sig = postedVar('newsig', 'javascript', true, true, true);

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

    $db->query("UPDATE ".X_PREFIX."members SET status='$status', customstatus='$cusstatus', email='$email', site='$site', aim='$aim',
    location='$location', bio='$bio', sig='$sig', showemail='$showemail', timeoffset='$timeoffset1', icq='$icq', avatar='$avatar', yahoo='$yahoo',
    theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew', newsletter='$newsletter', timeformat='$timeformatnew',
    msn='$msn', dateformat='$dateformatnew', mood='$mood', invisible='$invisible', saveogu2u='$saveogu2u', emailonu2u='$emailonu2u',
    useoldu2u='$useoldu2u', u2ualert=$u2ualert, sub_each_post='$newsubs' WHERE username='$user'");

    $newpassword = $_POST['newpassword'];
    if ($newpassword) {
        $newpassword = md5($newpassword);
        $db->query("UPDATE ".X_PREFIX."members SET password='$newpassword' WHERE username='$user'");

        // Force logout and delete cookies.
        $query = $db->query("DELETE FROM ".X_PREFIX."whosonline WHERE username='$user'");
        $session->logoutAll( $rawuser );
    }

    $unlock = formYesNo('unlock');
    if ( 'yes' == $unlock ) {
        \XMB\SQL\unlockMember( $rawuser );
    }

    message($lang['adminprofilechange'], TRUE, '', '', $full_url.'cp.php', true, false, true);
}

end_time();
eval('$footer = "'.template('footer').'";');
echo $header, $editpage, $footer;
?>
