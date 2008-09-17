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

loadtemplates(
'member_coppa',
'member_reg_rules',
'member_reg_password',
'member_reg_avatarurl',
'member_reg_avatarlist',
'member_reg',
'member_profile_email',
'member_profile',
'misc_feature_not_while_loggedin',
'misc_feature_notavailable'
);

smcwcache();

eval('$css = "'.template('css').'";');

switch ($action) {
    case 'reg':
        nav($lang['textregister']);
        break;
    case 'viewpro':
        nav($lang['textviewpro']);
        break;
    case 'coppa':
        nav($lang['textcoppa']);
        break;
    default:
        nav($lang['error']);
        break;
}

if ($action == 'coppa') {
    if (X_MEMBER) {
        eval('echo "'.template('header').'";');
        eval('echo "'.template('misc_feature_not_while_loggedin').'";');
        end_time();
        eval('echo "'.template('footer').'";');
        exit();
    }

    if ($SETTINGS['coppa'] != 'on') {
        redirect('member.php?action=reg', 0);
    }

    if (isset($coppasubmit)) {
        redirect('member.php?action=reg', 0);
    } else {
        eval('echo "'.template('header').'";');
        eval('echo stripslashes("'.template('member_coppa').'");');
    }
} elseif ($action == 'reg') {
    $time = $onlinetime-86400;
    $query = $db->query("SELECT count(uid) FROM $table_members WHERE regdate > '$time'");
    if ($db->result($query, 0) > $max_reg_day) {
        error($lang['max_regs']);
    }

    if ($regstatus != "on") {
        eval('echo "'.template('header').'";');
        eval('echo "'.template('misc_feature_notavailable').'";');
        end_time();
        eval('echo "'.template('footer').'";');
        exit();
    }

    if (X_MEMBER) {
        eval('echo "'.template('header').'";');
        eval('echo "'.template('misc_feature_not_while_loggedin').'";');
        end_time();
        eval('echo "'.template('footer').'";');
        exit();
    }

    if (!isset($regsubmit)) {
        eval('echo "'.template('header').'";');
        if ($bbrules == "on" && !isset($rulesubmit)) {
            $bbrulestxt = nl2br(stripslashes(stripslashes($bbrulestxt)));
            eval('echo stripslashes("'.template('member_reg_rules').'");');
        } else {
            $currdate = gmdate($timecode, $onlinetime+ ($addtime * 3600));
            eval($lang['evaloffset']);

            $themelist = array();
            $themelist[] = '<select name="thememem">';
            $themelist[] = '<option value="0">'.$lang['textusedefault'].'</option>';
            $query = $db->query("SELECT themeid, name FROM $table_themes ORDER BY name ASC");
            while ($themeinfo = $db->fetch_array($query)) {
                $themelist[] = '<option value="'.intval($themeinfo['themeid']).'">'.stripslashes($themeinfo['name']).'</option>';
            }
            $themelist[] = '</select>';
            $themelist = implode("\n", $themelist);

            $langfileselect = createLangFileSelect($SETTINGS['langfile']);

            $dayselect = array();
            $dayselect[] = '<select name="day">';
            $dayselect[] = '<option value="">&nbsp;</option>';
            for ($num = 1; $num <= 31; $num++) {
                $dayselect[] = '<option value="'.$num.'">'.$num.'</option>';
            }
            $dayselect[] = '</select>';
            $dayselect   = implode("\n", $dayselect);

            if ($SETTINGS['sigbbcode'] == "on") {
                $bbcodeis = $lang['texton'];
            } else {
                $bbcodeis = $lang['textoff'];
            }

            if ($SETTINGS['sightml'] == "on") {
                $htmlis = $lang['texton'];
            } else {
                $htmlis = $lang['textoff'];
            }

            if ($SETTINGS['emailcheck'] != "on") {
                eval('$pwtd = "'.template('member_reg_password').'";');
            } else {
                $pwtd = '';
            }

            if($SETTINGS['timeformat'] == 24) {
                $timeFormat12Checked = '';
                $timeFormat24Checked = $cheHTML;
            } else {
                $timeFormat12Checked = $cheHTML;
                $timeFormat24Checked = '';
            }

            $timezone1 = $timezone2 = $timezone3 = $timezone4 = $timezone5 = $timezone6 = '';
            $timezone7 = $timezone8 = $timezone9 = $timezone10 = $timezone11 = $timezone12 = '';
            $timezone13 = $timezone14 = $timezone15 = $timezone16 = $timezone17 = $timezone18 = '';
            $timezone19 = $timezone20 = $timezone21 = $timezone22 = $timezone23 = $timezone24 = '';
            $timezone25 = $timezone26 = $timezone27 = $timezone28 = $timezone29 = $timezone30 = '';
            $timezone31 = $timezone32 = $timezone33 = '';
            switch ($SETTINGS['def_tz']) {
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

            if ($SETTINGS['avastatus'] == 'on') {
                eval('$avatd = "'.template('member_reg_avatarurl').'";');
            } elseif ($SETTINGS['avastatus'] == 'list') {
                $avatars   = array();
                $avatars[] = '<option value=""/>'.$lang['textnone'].'</option>';
                $dirHandle = opendir('./images/avatars');
                while ($avFile = readdir($dirHandle)) {
                    if (is_file('./images/avatars/'.$avFile) && $avFile != '.' && $avFile != '..') {
                        $avatars[] = '<option value="./images/avatars/'.$avFile.'" />'.$avFile.'</option>';
                    }
                }
                closedir($dirHandle);
                $avatars = implode("\n", str_replace('value="'.$member['avatar'].'"', 'value="'.$member['avatar'].'" selected="selected"', $avatars));

                eval('$avatd = "'.template('member_reg_avatarlist').'";');
            } else {
                $avatd = '';
            }

            if (empty($dformatorig)) {
                $dformatorig = $SETTINGS['dateformat'];
            }
            eval('echo stripslashes("'.template('member_reg').'");');
        }
    } else {
        $find = array('<', '>', '|', '"', '[', ']', '\\', ',', '@', '\'', ' ');
        foreach ($find as $needle) {
            if (false !== strpos($username, $needle)) {
                error($lang['restricted']);
            }
        }

        if (strlen($username) < 3 || strlen($username) > 32) {
            error($lang['username_length_invalid']);
        }

        if ($ipreg != 'off') {
            $time = $onlinetime-86400;
            $query = $db->query("SELECT uid FROM $table_members WHERE regip = '$onlineip' AND regdate >= '$time'");
            if ($db->num_rows($query) >= 1) {
                error($lang['reg_today']);
            }
        }

        $email = addslashes(trim($email));

        if ($doublee == "off" && false !== strpos($email, "@")) {
            $email1 = ", email";
            $email2 = "OR email='$email'";
        } else {
            $email1 = '';
            $email2 = '';
        }

        $username = trim($username);
        $query = $db->query("SELECT username$email1 FROM $table_members WHERE username='$username' $email2");

        if ($member = $db->fetch_array($query)) {
            error($lang['alreadyreg']);
        }

        if ($emailcheck == "on") {
            $password = '';
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
            mt_srand((double)microtime() * 1000000);
            for ($get = strlen($chars); $i < 8; $i++) {
                $password .= $chars[mt_rand(0, $get)];
            }
            $password2 = $password;
        }

        $password = trim($password);
        $password2 = trim($password2);

        if ($password != $password2) {
            error($lang['pwnomatch']);
        }

        $fail = false;
        $efail = false;
        $query = $db->query("SELECT * FROM $table_restricted");
        while ($restriction = $db->fetch_array($query)) {
            if ($restriction['case_sensitivity'] == 1) {
                if ($restriction['partial'] == 1) {
                    if (strpos($username, $restriction['name']) !== false) {
                        $fail = true;
                    }
                    if (strpos($email, $restriction['name']) !== false) {
                        $efail = true;
                    }
                } else {
                    if ($username == $restriction['name']) {
                        $fail = true;
                    }
                    if ($email == $restriction['name']) {
                        $efail = true;
                    }
                }
            } else {
                $t_username = strtolower($username);
                $t_email = strtolower($email);
                $restriction['name'] = strtolower($restriction['name']);

                if ($restriction['partial'] == 1) {
                    if (strpos($t_username, $restriction['name']) !== false) {
                        $fail = true;
                    }
                    if (strpos($t_email, $restriction['name']) !== false) {
                        $efail = true;
                    }
                } else {
                    if ($t_username == $restriction['name']) {
                        $fail = true;
                    }
                    if ($t_email == $restriction['name']) {
                        $efail = true;
                    }
                }
            }
        }

        if ($fail) {
            error($lang['restricted']);
        }

        if ($efail) {
            error($lang['emailrestricted']);
        }

        if (false === strpos($email, "@")) {
            error($lang['bademail']);
        }

        if ($password == '' || strpos($password, '"') != false || strpos($password, "'") != false) {
            error($lang['textpw1']);
        }

        if (trim($username) == '') {
            error($lang['textnousername']);
        }

        $langfilenew = getLangFileNameFromHash($langfilenew);
        if (!$langfilenew) {
            $langfilenew = $SETTINGS['langfile'];
        } else {
            $langfilenew = basename($langfilenew);
        }

        $query = $db->query("SELECT COUNT(uid) FROM $table_members");
        $count1 = $db->result($query,0);

        $self['status'] = ($count1 != 0) ? 'Member' : 'Super Administrator';

        $timeoffset1   = isset($timeoffset1) ? (float) $timeoffset1 : 0;
        $thememem      = isset($thememem) ? (int) $thememem : 0;
        $tpp           = isset($tpp) ? (int) $tpp : $topicperpage;
        $ppp           = isset($ppp) ? (int) $ppp : $postperpage;

        $showemail     = (isset($showemail) && $showemail == 'yes') ? 'yes' : 'no';
        $newsletter    = (isset($newsletter) && $newsletter == 'yes') ? 'yes' : 'no';
        $saveogu2u     = (isset($saveogu2u) && $saveogu2u == 'yes') ? 'yes' : 'no';
        $emailonu2u    = (isset($emailonu2u) && $emailonu2u == 'yes') ? 'yes' : 'no';
        $useoldu2u     = (isset($useoldu2u) && $useoldu2u == 'yes') ? 'yes' : 'no';

        $bday          = iso8601_date($year, $month, $day);

        if (strlen(trim($dateformatnew)) == 0) {
            $dateformatnew = $SETTINGS['dateformat'];
        } else {
            $dateformatnew = (intval($dateformatnew) > 0 ? $SETTINGS['dateformat'] : checkInput($dateformatnew, '', '', 'javascript', true)); // Temporary validation of dateformat - if it contains numbers we assume the date is incorrect (blacklist approach) and therefore use the default dateformat otherwise we proceed to validation of input.
        }
        $timeformatnew = isset($timeformatnew) ? checkInput($timeformatnew, '', '', 'script', true) : $SETTINGS['timeformat'];

        $avatar        = isset($avatar) ? checkInput($avatar, '', '', 'javascript', false) : '';
        $location      = isset($location) ? checkInput($location, '', '', "javascript", false) : '';
        $icq           = (isset($icq) && is_numeric($icq) && $icq > 0) ? $icq : 0;
        $yahoo         = isset($yahoo) ? checkInput($yahoo, '', '', 'javascript', false) : '';
        $aim           = isset($aim) ? checkInput($aim, '', '', 'javascript', false) : '';
        $msn           = isset($msn) ? checkInput($msn, '', '', 'javascript', false) : '';
        $email         = isset($email) ? checkInput($email, '', '', 'javascript', false) : '';
        $site          = isset($site) ? checkInput($site, '', '', 'javascript', false) : '';
        $webcam        = isset($webcam) ? checkInput($webcam, '', '', 'javascript', false) : '';
        $bio           = isset($bio) ? checkInput($bio, '', '', 'javascript', false) : '';
        $mood          = isset($mood) ? checkInput($mood, '', '', 'javascript', false) : '';
        $sig           = isset($sig) ? checkInput($sig, '', $SETTINGS['sightml'], '', false) : '';

        $avatar        = addslashes($avatar);
        $location      = addslashes($location);
        $yahoo         = addslashes($yahoo);
        $aim           = addslashes($aim);
        $email         = addslashes($email);
        $site          = addslashes($site);
        $webcam        = addslashes($webcam);
        $bio           = addslashes($bio);
        $mood          = addslashes($mood);
        $sig           = addslashes($sig);

        $password      = md5(trim($password));

        $max_size = explode('x', $SETTINGS['max_avatar_size']);
        if ($max_size[0] > 0 && $max_size[1] > 0 && substr_count($avatar, ',') < 2) {
            // we ignore flash avatars here
            $size = @getimagesize($avatar);
            if ($size === false ) {
                $avatar = '';
            } elseif(($size[0] > $max_size[0] && $max_size[0] > 0) || ($size[1] > $max_size[1] && $max_size[1] > 0)) {
                error($lang['avatar_too_big'] . $SETTINGS['max_avatar_size'] . 'px');
            }
        }

        $db->query("INSERT INTO $table_members (uid, username, password, regdate, postnum, email, site, aim, status, location, bio, sig, showemail, timeoffset, icq, avatar, yahoo, customstatus, theme, bday, langfile, tpp, ppp, newsletter, regip, timeformat, msn, ban, dateformat, ignoreu2u, lastvisit, mood, pwdate, invisible, u2ufolders, saveogu2u, emailonu2u, useoldu2u, webcam) VALUES ('', '$username', '$password', ".$db->time($onlinetime).", '0', '$email', '$site', '$aim', '$self[status]',  '$location', '$bio', '$sig', '$showemail', '$timeoffset1', '$icq', '$avatar', '$yahoo', '', '$thememem', '$bday', '$newlangfile', '$tpp', '$ppp',  '$newsletter', '$onlineip', '$timeformatnew', '$msn', '', '$dateformatnew', '', '', '$mood', '', '0', '', '$saveogu2u', '$emailonu2u', '$useoldu2u', '$webcam')");

        if ($SETTINGS['notifyonreg'] != "off") {
            if ($SETTINGS['notifyonreg'] == 'u2u') {
                $mailquery = $db->query("SELECT username FROM $table_members WHERE status='Super Administrator'");
                while ($admin = $db->fetch_array($mailquery)) {
                    $db->query("INSERT INTO $table_u2u ( u2uid, msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus ) VALUES ('', '$admin[username]', '".addslashes($bbname)."', 'incoming', '$admin[username]', 'Inbox', '$lang[textnewmember]', '$lang[textnewmember2]', '" . $onlinetime . "', 'no', 'yes')");
                }
            } else {
                $headers[] = "From: $bbname <$adminemail>";
                $headers[] = "X-Sender: <$adminemail>";
                $headers[] = 'X-Mailer: PHP';
                $headers[] = 'X-AntiAbuse: Board servername - '.$bbname;
                $headers[] = 'X-AntiAbuse: Username - '.$xmbuser;
                $headers[] = 'X-Priority: 2';
                $headers[] = "Return-Path: <$adminemail>";
                $headers[] = 'Content-Type: text/plain; charset=ASCII';
                $headers = implode("\r\n", $headers);

                $mailquery = $db->query("SELECT email FROM $table_members WHERE status = 'Super Administrator'");
                while ($notify = $db->fetch_array($mailquery)) {
                    altMail($notify['email'], $lang['textnewmember'], $lang['textnewmember2'], $headers);
                }
            }
        }

        if ($emailcheck == "on") {
            altMail($email, $lang['textyourpw'], $lang['textyourpwis']." \n\n$username\n$password2", "From: $bbname <$adminemail>");
        } else {
            $currtime = $onlinetime + (86400*30);
            put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
            put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
        }
        eval('echo "'.template('header').'";');
        echo ($emailcheck == "on") ? "<center><span class=\"mediumtxt \">$lang[emailpw]</span></center>" : "<center><span class=\"mediumtxt \">$lang[regged]</span></center>";

        redirect('index.php', 2, X_REDIRECT_JS);
    }
} elseif ($action == "viewpro") {
    if (!$member) {
        error($lang['nomember']);
    } else {
        $memberinfo = $db->fetch_array($db->query("SELECT * FROM $table_members WHERE username='$member'"));
        if ($memberinfo['status'] == 'Administrator' || $memberinfo['status'] == 'Super Administrator' || $memberinfo['status'] == 'Super Moderator' || $memberinfo['status'] == 'Moderator') {
            $limit = "title = '$memberinfo[status]'";
        } else {
            $limit = "posts <= '$memberinfo[postnum]' AND title != 'Super Administrator' AND title != 'Administrator' AND title != 'Super Moderator' AND title != 'Super Moderator' AND title != 'Moderator'";
        }

        $rank = $db->fetch_array($db->query("SELECT * FROM $table_ranks WHERE $limit ORDER BY posts DESC LIMIT 1"));

        if ($memberinfo['uid'] == '') {
            error($lang['nomember']);
            end_time();
        } else {
            eval('echo "'.template('header').'";');

            $daysreg = ($onlinetime - $memberinfo['regdate']) / (24*3600);
            if ($daysreg > 1 ) {
                $ppd = $memberinfo['postnum'] / $daysreg;
                $ppd = round($ppd, 2);
            } else {
                $ppd = $memberinfo['postnum'];
            }

            $memberinfo['regdate'] = gmdate($dateformat , $memberinfo['regdate'] + ($addtime * 3600) + ($timeoffset * 3600));

            if (strpos($memberinfo['site'], 'http') === false) {
                $memberinfo['site'] = "http://$memberinfo[site]";
            }

            if ($memberinfo['site'] != 'http://') {
                $site = $memberinfo['site'];
            } else {
                $site = '';
            }

            if (strpos($memberinfo['webcam'], 'http') === false) {
                $memberinfo['webcam'] = "http://$memberinfo[webcam]";
            }

            if ($memberinfo['webcam'] != 'http://') {
                $webcam = $memberinfo['webcam'];
            } else {
                $webcam = '';
            }

            if (X_MEMBER && $memberinfo['email'] != '' && $memberinfo['showemail'] == 'yes') {
                $email = $memberinfo['email'];
            } else {
                $email = '';
            }

            $rank['avatarrank'] = trim($rank['avatarrank']);
            $memberinfo['avatar'] = trim($memberinfo['avatar']);

            if ($rank['avatarrank'] != '') {
                $rank['avatarrank'] = '<img src="'.$rank['avatarrank'].'" alt="'.$lang['altavatar'].'" border="0" />';
            }

            if ($memberinfo['avatar'] != '') {
                if (false !== ($pos = strpos($memberinfo['avatar'], ',')) && substr($memberinfo['avatar'], $pos-4, 4) == '.swf') {
                    $flashavatar = explode(",",$memberinfo['avatar']);
                    $memberinfo['avatar'] = '<object type="application/x-shockwave-flash" data="'.$flashavatar[0].'" width="'.$flashavatar[1].'" height="'.$flashavatar[2].'"><param name="movie" value="'.$flashavatar[0].'" /><param name="AllowScriptAccess" value="never" /></object>';
                } else {
                    $memberinfo['avatar'] = '<img src="'.$memberinfo['avatar'].'" alt="'.$lang['altavatar'].'" border="0" />';
                }
            }

            if ($rank['avatarrank'] || $memberinfo['avatar']) {
                if (isset($site) && strlen(trim($site)) > 0) {
                    $sitelink = $site;
                } else {
                    $sitelink = "about:blank";
                }
            } else {
                $sitelink = "about:blank";
            }

            $showtitle = $rank['title'];
            $stars = str_repeat("<img src=\"$imgdir/star.gif\" alt=\"*\" />", $rank['stars']);

            if ($memberinfo['customstatus'] != '') {
                $showtitle = $rank['title'];
                $customstatus = '<br />'.$memberinfo['customstatus'];
            } else {
                $showtitle = $rank['title'];
                $customstatus = '';
            }

            if (!($memberinfo['lastvisit'] > 0)) {
                $lastmembervisittext = $lang['textpendinglogin'];
            } else {
                $lastvisitdate = gmdate($dateformat, $memberinfo['lastvisit'] + ($timeoffset * 3600) + ($addtime * 3600));
                $lastvisittime = gmdate($timecode, $memberinfo['lastvisit'] + ($timeoffset * 3600) + ($addtime * 3600));
                $lastmembervisittext = "$lastvisitdate $lang[textat] $lastvisittime";
            }

            $query = $db->query("SELECT COUNT(pid) FROM $table_posts");
            $posts = $db->result($query, 0);

            $posttot = $posts;
            if ($posttot == 0) {
                $percent = '0';
            } else {
                $percent = $memberinfo['postnum']*100/$posttot;
                $percent = round($percent, 2);
            }

            $memberinfo['bio'] = stripslashes(censor($memberinfo['bio']));
            $memberinfo['bio'] = nl2br($memberinfo['bio']);
            $encodeuser = rawurlencode($memberinfo['username']);

            if ($memberinfo['showemail'] == 'yes') {
                eval('$emailblock = "'.template('member_profile_email').'";');
            } else {
                $emailblock = '';
            }

            if (X_SADMIN) {
                $admin_edit = "<br />$lang[adminoption] <a href=\"./editprofile.php?user=$encodeuser\">$lang[admin_edituseraccount]</a>";
            } else {
                $admin_edit = NULL;
            }

            if ($memberinfo['mood'] != '') {
                $memberinfo['mood'] = censor($memberinfo['mood']);
                $memberinfo['mood'] = postify($memberinfo['mood'], 'no', 'no', 'yes', 'no', 'yes', 'no', true, 'yes');
            } else {
                $memberinfo['mood'] = '';
            }

            $memberinfo['location'] = censor($memberinfo['location']);
            $memberinfo['aim'] = censor($memberinfo['aim']);
            $memberinfo['icq'] = ($member['icq'] > 0) ? $member['icq'] : '';
            $memberinfo['yahoo'] = censor($memberinfo['yahoo']);
            $memberinfo['msn'] = censor($memberinfo['msn']);

            if ($memberinfo['bday'] === iso8601_date(0,0,0)) {
                $memberinfo['bday'] = $lang['textnone'];
            } else {
                $memberinfo['bday'] = date($dateformat, mktime(12,0,0,substr($memberinfo['bday'],5,2),substr($memberinfo['bday'],8,2),substr($memberinfo['bday'],0,4)));
            }

            $modXmbuser = str_replace(array('*', '.', '+'), array('\*', '\.', '\+'), $xmbuser);
            $restrict = array("(password='')");
            switch($self['status']) {
                case 'Member':
                    $restrict[] = 'private = 1';
                    $restrict[] = "(userlist = '' OR userlist REGEXP '(^|(,))([:space:])*$modXmbuser([:space:])*((,)|$)')";
                    break;
                case 'Moderator':
                case 'Super Moderator':
                    $restrict[] = '(private = 1 OR private = 3)';
                    $restrict[] = "(if ((private=1 AND userlist != ''), if ((userlist REGEXP '(^|(,))([:space:])*$modXmbuser([:space:])*((,)|$)'), 1, 0), 1))";
                    break;
                case 'Administrator':
                    $restrict[] = '(private > 0 AND private < 4)';
                    $restrict[] = "(if ((private=1 AND userlist != ''), if ((userlist REGEXP '(^|(,))([:space:])*$modXmbuser([:space:])*((,)|$)'), 1, 0), 1))";
                    break;
                case 'Super Administrator':
                    break;
                default:
                    $restrict[] = '(private=1)';
                    $restrict[] = "(userlist='')";
                    break;
            }
            $restrict = implode(' AND ', $restrict);

            $query = $db->query("SELECT f.name, p.fid, COUNT(DISTINCT p.pid) as posts FROM $table_posts p LEFT JOIN $table_forums f ON p.fid=f.fid WHERE $restrict AND p.author='$member' GROUP BY p.fid ORDER BY posts DESC LIMIT 1");
            $forum = $db->fetch_array($query);

            if (!($forum['posts'] > 0)) {
                $topforum = $lang['textnopostsyet'];
            } elseif($memberinfo['postnum'] <= 0) {
                $topforum = $lang['textnopostsyet'];
            } else {
                $forum['fid'] = intval($forum['fid']);
                $topforum = "<a href=\"forumdisplay.php?fid=$forum[fid]\">$forum[name]</a> ($forum[posts] $lang[textdeleteposts]) [".round(($forum['posts']/$memberinfo['postnum'])*100, 1)."% of total posts]";
            }

            $query = $db->query("SELECT t.tid, t.subject, p.dateline, p.pid FROM ($table_posts p, $table_threads t) LEFT JOIN $table_forums f ON p.fid=f.fid WHERE $restrict AND p.author='$member' AND p.tid=t.tid ORDER BY p.dateline DESC LIMIT 1");
            if ($post = $db->fetch_array($query)) {
                $posts = $db->result($db->query("SELECT COUNT(pid) FROM $table_posts WHERE tid='$post[tid]' AND pid < '$post[pid]'"), 0)+1;
                validatePpp();
                $page = quickpage($posts, $ppp);
                $lastpostdate = gmdate($dateformat, $post['dateline'] + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600));
                $lastposttime = gmdate($timecode, $post['dateline'] + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600));
                $lastposttext = $lastpostdate.' '.$lang['textat'].' '.$lastposttime;
                $post['subject'] = censor($post['subject']);
                $lastpost = '<a href="viewthread.php?tid='.intval($post['tid']).'&amp;page='.$page.'#pid'.intval($post['pid']).'">'.$post['subject'].'</a> ('.$lastposttext.')';
            } else {
                $lastpost = $lang['textnopostsyet'];
            }

            $lang['searchusermsg'] = str_replace('*USER*', $memberinfo['username'], $lang['searchusermsg']);

            eval('echo stripslashes("'.template('member_profile').'");');
        }
    }
} else {
    error($lang['textnoaction']);
}

end_time();
eval('echo "'.template('footer').'";');
?>