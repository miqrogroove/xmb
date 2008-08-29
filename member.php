<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Alpha Two - This software should not be used for any purpose after 31 October 2008.
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

define('X_SCRIPT', 'member.php');

require 'header.php';

loadtemplates(
'member_coppa',
'member_reg_rules',
'member_reg_password',
'member_reg_avatarurl',
'member_reg_avatarlist',
'member_reg',
'member_reg_optional',
'member_reg_captcha',
'member_profile_email',
'member_profile',
'misc_feature_not_while_loggedin',
'misc_feature_notavailable'
);

smcwcache();

eval('$css = "'.template('css').'";');

$action = postedVar('action', '', FALSE, FALSE, FALSE, 'g');
switch($action) {
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
        header('HTTP/1.0 404 Not Found');
        error($lang['textnoaction']);
        break;
}

switch($action) {
    case 'coppa':
        if (X_MEMBER) {
            eval('echo "'.template('header').'";');
            eval('echo "'.template('misc_feature_not_while_loggedin').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        }

        if ($SETTINGS['coppa'] != 'on') {
            redirect($full_url.'member.php?action=reg', 0);
        }

        if (onSubmit('coppasubmit')) {
            redirect($full_url.'member.php?action=reg', 0);
        } else {
            eval('echo "'.template('header').'";');
            eval('echo "'.template('member_coppa').'";');
        }
        break;

    case 'reg':
        if ($SETTINGS['pruneusers'] > 0) {
            $prunebefore = $onlinetime - (60 * 60 * 24 * $SETTINGS['pruneusers']);
            $db->query("DELETE FROM ".X_PREFIX."members WHERE lastvisit=0 AND regdate < $prunebefore AND status='Member'");
        }

        if ($SETTINGS['maxdayreg'] > 0) {
            $time = $onlinetime - 86400; // take the date and distract 24 hours from it
            $query = $db->query("SELECT COUNT(uid) FROM ".X_PREFIX."members WHERE regdate > $time");
            if ($db->result($query, 0) > $SETTINGS['maxdayreg']) {
                error($lang['max_regs']);
            }
            $db->free_result($query);
        }

        if ($SETTINGS['regstatus'] == 'off') {
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

        if (noSubmit('regsubmit')) {
            eval('echo "'.template('header').'";');
            if ($SETTINGS['bbrules'] == 'on' && noSubmit('rulesubmit')) {
                $SETTINGS['bbrulestxt'] = nl2br($SETTINGS['bbrulestxt']);
                eval('echo "'.template('member_reg_rules').'";');
            } else {
                $currdate = gmdate($timecode, $onlinetime+ ($addtime * 3600));
                eval($lang['evaloffset']);

                $themelist = array();
                $themelist[] = '<select name="thememem">';
                $themelist[] = '<option value="0">'.$lang['textusedefault'].'</option>';
                $query = $db->query("SELECT themeid, name FROM ".X_PREFIX."themes ORDER BY name ASC");
                while($themeinfo = $db->fetch_array($query)) {
                    $themelist[] = '<option value="'.intval($themeinfo['themeid']).'">'.stripslashes($themeinfo['name']).'</option>';
                }
                $themelist[] = '</select>';
                $themelist = implode("\n", $themelist);
                $db->free_result($query);

                $langfileselect = createLangFileSelect($SETTINGS['langfile']);

                $dayselect = array();
                $dayselect[] = '<select name="day">';
                $dayselect[] = '<option value="">&nbsp;</option>';
                for($num = 1; $num <= 31; $num++) {
                    $dayselect[] = '<option value="'.$num.'">'.$num.'</option>';
                }
                $dayselect[] = '</select>';
                $dayselect = implode("\n", $dayselect);

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

                $pwtd = '';
                if ($SETTINGS['emailcheck'] == 'off') {
                    eval('$pwtd = "'.template('member_reg_password').'";');
                }

                if ($SETTINGS['timeformat'] == 24) {
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
                switch($SETTINGS['def_tz']) {
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

                $avatd = '';
                if ($SETTINGS['avastatus'] == 'on') {
                    eval('$avatd = "'.template('member_reg_avatarurl').'";');
                } else if ($SETTINGS['avastatus'] == 'list') {
                    $avatars = array();
                    $avatars[] = '<option value=""/>'.$lang['textnone'].'</option>';
                    $dirHandle = opendir('./images/avatars');
                    while($avFile = readdir($dirHandle)) {
                        if (is_file('./images/avatars/'.$avFile) && $avFile != '.' && $avFile != '..') {
                            $avatars[] = '<option value="./images/avatars/'.$avFile.'" />'.$avFile.'</option>';
                        }
                    }
                    closedir($dirHandle);
                    $avatars = implode("\n", str_replace('value="'.$member['avatar'].'"', 'value="'.$member['avatar'].'" selected="selected"', $avatars));
                    eval('$avatd = "'.template('member_reg_avatarlist').'";');
                }

                if (empty($dformatorig)) {
                    $dformatorig = $SETTINGS['dateformat'];
                }

                $regoptional = '';
                if ($SETTINGS['regoptional'] == 'on') {
                    eval('$regoptional = "'.template('member_reg_optional').'";');
                }

                $captcharegcheck = '';
                if ($SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_reg_status'] == 'on' && !DEBUG) {
                    require ROOT.'include/captcha.inc.php';
                    $Captcha = new Captcha(250, 50);
                    if ($Captcha->bCompatible !== false) {
                        $imghash = $Captcha->GenerateCode();
                        eval('$captcharegcheck = "'.template('member_reg_captcha').'";');
                    }
                }
                eval('echo "'.template('member_reg').'";');
            }
        } else {
            if ($_POST['username'] != preg_replace('#[\]\'\x00-\x1F\x7F<>\\\\|"[,@]#', '', $_POST['username'])) {
                error($lang['restricted']);
            }

            $username = postedVar('username', '', TRUE, FALSE);

            if (strlen($username) < 3 || strlen($username) > 32) {
                error($lang['username_length_invalid']);
            }

            $username = postedVar('username');

            if ($SETTINGS['ipreg'] != 'off') {
                $time = $onlinetime-86400;
                $query = $db->query("SELECT uid FROM ".X_PREFIX."members WHERE regip='$onlineip' AND regdate >= $time");
                if ($db->num_rows($query) >= 1) {
                    error($lang['reg_today']);
                }
                $db->free_result($query);
            }

            $email = postedVar('email', 'javascript', TRUE, TRUE, TRUE);
            if ($SETTINGS['doublee'] == 'off' && false !== strpos($email, "@")) {
                $email1 = ", email";
                $email2 = "OR email='$email'";
            } else {
                $email1 = '';
                $email2 = '';
            }

            $query = $db->query("SELECT username$email1 FROM ".X_PREFIX."members WHERE username='$username' $email2");
            if ($member = $db->fetch_array($query)) {
                $db->free_result($query);
                error($lang['alreadyreg']);
            }

            if ($SETTINGS['emailcheck'] == 'on') {
                $password = '';
                $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
                mt_srand((double)microtime() * 1000000);
                $get = strlen($chars) - 1;
                for($i = 0; $i < 8; $i++) {
                    $password .= $chars[mt_rand(0, $get)];
                }
                $password2 = $password;
                } else {
                $password = $_POST['password'];
                $password2 = $_POST['password2'];
            }

            if ($password != $password2) {
                error($lang['pwnomatch']);
            }

            $fail = false;
            $efail = false;
            $query = $db->query("SELECT * FROM ".X_PREFIX."restricted");
            while($restriction = $db->fetch_array($query)) {
                $t_username = $username;
                $t_email = $email;
                if ($restriction['case_sensitivity'] == 1) {
                    $t_username = strtolower($t_username);
                    $t_email = strtolower($t_email);
                    $restriction['name'] = strtolower($restriction['name']);
                }

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
            $db->free_result($query);

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

            if ($username == '') {
                error($lang['textnousername']);
            }

            if ($SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_reg_status'] == 'on' && !DEBUG) {
                require ROOT.'include/captcha.inc.php';
                $Captcha = new Captcha(250, 50);
                if ($Captcha->bCompatible !== false) {
                    $imghash = postedVar('imghash', '', FALSE, TRUE);
                    $imgcode = postedVar('imgcode', '', FALSE, FALSE);
                    if ($Captcha->ValidateCode($imgcode, $imghash) !== true) {
                        error($lang['captchaimageinvalid']);
                    }
                }
            }

            $langfilenew = postedVar('langfilenew', '', FALSE, FALSE);
            $langfilenew = getLangFileNameFromHash($langfilenew);
            if ($langfilenew === FALSE) {
                $langfilenew = $SETTINGS['langfile'];
            } else {
                $langfilenew = basename($langfilenew);
            }
            $langfilenew = $db->escape($langfilenew);

            $query = $db->query("SELECT COUNT(uid) FROM ".X_PREFIX."members");
            $count1 = $db->result($query,0);
            $db->free_result($query);

            $self['status'] = ($count1 != 0) ? 'Member' : 'Super Administrator';

            $timeoffset1 = isset($_POST['timeoffset1']) && is_numeric($_POST['timeoffset1']) ? $_POST['timeoffset1'] : 0;
            $thememem = formInt('thememem');
            $tpp = formInt('tpp');
            $ppp = formInt('ppp');
            $showemail = formYesNo('showemail');
            $newsletter = formYesNo('newsletter');
            $saveogu2u = formYesNo('saveogu2u');
            $emailonu2u = formYesNo('emailonu2u');
            $useoldu2u = formYesNo('useoldu2u');
            $u2ualert = formInt('u2ualert');
            $year = formInt('year');
            $month = formInt('month');
            $day = formInt('day');
            $bday = iso8601_date($year, $month, $day);

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

            $password = md5($password);

            if ($SETTINGS['regoptional'] == 'off') {
                $db->query("INSERT INTO ".X_PREFIX."members (username, password, regdate, postnum, email, site, aim, status, location, bio, sig, showemail, timeoffset, icq, avatar, yahoo, customstatus, theme, bday, langfile, tpp, ppp, newsletter, regip, timeformat, msn, ban, dateformat, ignoreu2u, lastvisit, mood, pwdate, invisible, u2ufolders, saveogu2u, emailonu2u, useoldu2u, u2ualert) VALUES ('$username', '$password', ".$db->time($onlinetime).", 0, '$email', '', '', '$self[status]', '', '', '', '$showemail', '$timeoffset1', '', '', '', '', $thememem, '$bday', '$langfilenew', $tpp, $ppp, '$newsletter', '$onlineip', $timeformatnew, '', '', '$dateformatnew', '', 0, '', 0, '0', '', '$saveogu2u', '$emailonu2u', '$useoldu2u', $u2ualert)");
            } else {
                $avatar = postedVar('avatar', 'javascript', TRUE, TRUE, TRUE);
                $avatarcheck = postedVar('newavatarcheck');
                $location = postedVar('location', 'javascript', TRUE, TRUE, TRUE);
                $icq = postedVar('icq', '', FALSE, FALSE);
                $icq = ($icq && is_numeric($icq) && $icq > 0) ? $icq : 0;
                $yahoo = postedVar('yahoo', 'javascript', TRUE, TRUE, TRUE);
                $aim = postedVar('aim', 'javascript', TRUE, TRUE, TRUE);
                $msn = postedVar('msn', 'javascript', TRUE, TRUE, TRUE);
                $site = postedVar('site', 'javascript', TRUE, TRUE, TRUE);
                $bio = postedVar('bio', 'javascript', TRUE, TRUE, TRUE);
                $mood = postedVar('mood', 'javascript', TRUE, TRUE, TRUE);
                $sig = postedVar('sig', 'javascript', ($SETTINGS['sightml']=='off'), TRUE, TRUE);

                $max_size = explode('x', $SETTINGS['max_avatar_size']);
                if (ini_get('allow_url_fopen')) {
                    if ($max_size[0] > 0 && $max_size[1] > 0) {
                        $size = @getimagesize($_POST['avatar']);
                        if ($size === false) {
                            $avatar = '';
                        } else if (($size[0] > $max_size[0] && $max_size[0] > 0) || ($size[1] > $max_size[1] && $max_size[1] > 0)) {
                            error($lang['avatar_too_big'] . $SETTINGS['max_avatar_size'] . 'px');
                        }
                    }
                } else if ($newavatarcheck == "no") {
                    $avatar = '';
                }

                $db->query("INSERT INTO ".X_PREFIX."members (username, password, regdate, postnum, email, site, aim, status, location, bio, sig, showemail, timeoffset, icq, avatar, yahoo, customstatus, theme, bday, langfile, tpp, ppp, newsletter, regip, timeformat, msn, ban, dateformat, ignoreu2u, lastvisit, mood, pwdate, invisible, u2ufolders, saveogu2u, emailonu2u, useoldu2u, u2ualert) VALUES ('$username', '$password', ".$db->time($onlinetime).", 0, '$email', '$site', '$aim', '$self[status]', '$location', '$bio', '$sig', '$showemail', '$timeoffset1', '$icq', '$avatar', '$yahoo', '', $thememem, '$bday', '$langfilenew', $tpp, $ppp, '$newsletter', '$onlineip', $timeformatnew, '$msn', '', '$dateformatnew', '', 0, '$mood', 0, '0', '', '$saveogu2u', '$emailonu2u', '$useoldu2u', $u2ualert)");
            }

            if ($SETTINGS['notifyonreg'] != 'off') {
                if ($SETTINGS['notifyonreg'] == 'u2u') {
                    $mailquery = $db->query("SELECT username FROM ".X_PREFIX."members WHERE status='Super Administrator'");
                    while($admin = $db->fetch_array($mailquery)) {
                        $db->query("INSERT INTO ".X_PREFIX."u2u (u2uid, msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus) VALUES ('', '$admin[username]', '".addslashes($bbname)."', 'incoming', '$admin[username]', 'Inbox', '$lang[textnewmember]', '$lang[textnewmember2]', '".$onlinetime."', 'no', 'yes')");
                    }
                    $db->free_result($mailquery);
                } else {
                    $headers[] = "From: $bbname <$SETTINGS[adminemail]>";
                    $headers[] = "X-Sender: <$SETTINGS[adminemail]>";
                    $headers[] = 'X-Mailer: PHP';
                    $headers[] = 'X-AntiAbuse: Board servername - '.$SETTINGS['bbname'];
                    $headers[] = 'X-AntiAbuse: Username - '.$xmbuser;
                    $headers[] = 'X-Priority: 2';
                    $headers[] = "Return-Path: <$SETTINGS[adminemail]>";
                    $headers[] = 'Content-Type: text/plain; charset=ASCII';
                    $headers = implode("\r\n", $headers);

                    $mailquery = $db->query("SELECT email FROM ".X_PREFIX."members WHERE status = 'Super Administrator'");
                    while($notify = $db->fetch_array($mailquery)) {
                        altMail($notify['email'], $lang['textnewmember'], $lang['textnewmember2'], $headers);
                    }
                    $db->free_result($mailquery);
                }
            }

            if ($SETTINGS['emailcheck'] == 'on') {
                $username = $_POST['username'];
                altMail($email, '['.$bbname.'] '.$lang['textyourpw'], "{$lang['textyourpwis']} \n\n{$lang['textusername']} $username\n{$lang['textpassword']} $password2", "From: $bbname <$adminemail>");
            } else {
                $username = postedVar('username', '', TRUE, FALSE);
                $currtime = $onlinetime + (86400*30);
                put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
                put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
            }
            eval('echo "'.template('header').'";');
            echo ($SETTINGS['emailcheck'] == 'on') ? "<center><span class=\"mediumtxt \">$lang[emailpw]</span></center>" : "<center><span class=\"mediumtxt \">$lang[regged]</span></center>";

            redirect($full_url, 2, X_REDIRECT_JS);
        }
        break;

    case 'viewpro':
        $member = postedVar('member', '', TRUE, TRUE, FALSE, 'g');
        if ($member == '') {
            header('HTTP/1.0 404 Not Found');
            error($lang['nomember']);
        } else {
            $memberinfo = $db->fetch_array($db->query("SELECT * FROM ".X_PREFIX."members WHERE username='$member'"));
            if ($memberinfo['status'] == 'Banned') {
                $memberinfo['avatar'] = '';
                $rank = array(
                'title' => 'Banned',
                'posts' => 0,
                'id' => 0,
                'stars' => 0,
                'allowavatars' => 'no',
                'avatarrank' => ''
                );
            } else {
                if ($memberinfo['status'] == 'Administrator' || $memberinfo['status'] == 'Super Administrator' || $memberinfo['status'] == 'Super Moderator' || $memberinfo['status'] == 'Moderator') {
                    $limit = "title = '$memberinfo[status]'";
                } else {
                    $limit = "posts <= '$memberinfo[postnum]' AND title != 'Super Administrator' AND title != 'Administrator' AND title != 'Super Moderator' AND title != 'Super Moderator' AND title != 'Moderator'";
                }

                $rank = $db->fetch_array($db->query("SELECT * FROM ".X_PREFIX."ranks WHERE $limit ORDER BY posts DESC LIMIT 1"));
            }

            if ($memberinfo['uid'] == '') {
                header('HTTP/1.0 404 Not Found');
                error($lang['nomember']);
            } else {
                eval('echo "'.template('header').'";');
                
                $encodeuser = recodeOut($memberinfo['username']);
                if (X_GUEST) {
                    $memberlinks = '';
                } else {
                    $memberlinks = " <small>(<a href=\"u2u.php?action=send&amp;username=$encodeuser\" onclick=\"Popup(this.href, 'Window', 700, 450); return false;\">{$lang['textu2u']}</a>)&nbsp;&nbsp;(<a href=\"buddy.php?action=add&amp;buddys=$encodeuser\" onclick=\"Popup(this.href, 'Window', 450, 400); return false;\">{$lang['addtobuddies']}</a>)</small>";
                }

                $daysreg = ($onlinetime - $memberinfo['regdate']) / (24*3600);
                if ($daysreg > 1) {
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
                    $memberinfo['avatar'] = '<img src="'.$memberinfo['avatar'].'" alt="'.$lang['altavatar'].'" border="0" />';
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
                $stars = str_repeat('<img src="'.$imgdir.'/star.gif" alt="*" border="0" />', $rank['stars']);

                if ($memberinfo['customstatus'] != '') {
                    $showtitle = $rank['title'];
                    $customstatus = '<br />'.censor($memberinfo['customstatus']);
                } else {
                    $showtitle = $rank['title'];
                    $customstatus = '';
                }

                if (!($memberinfo['lastvisit'] > 0)) {
                    $lastmembervisittext = $lang['textpendinglogin'];
                } else {
                    $lastvisitdate = gmdate($dateformat, $memberinfo['lastvisit'] + ($timeoffset * 3600) + ($addtime * 3600));
                    $lastvisittime = gmdate($timecode, $memberinfo['lastvisit'] + ($timeoffset * 3600) + ($addtime * 3600));
                    $lastmembervisittext = $lastvisitdate.' '.$lang['textat'].' '.$lastvisittime;
                }

                $query = $db->query("SELECT COUNT(pid) FROM ".X_PREFIX."posts");
                $posts = $db->result($query, 0);
                $db->free_result($query);

                $posttot = $posts;
                if ($posttot == 0) {
                    $percent = '0';
                } else {
                    $percent = $memberinfo['postnum']*100/$posttot;
                    $percent = round($percent, 2);
                }

                $memberinfo['bio'] = censor($memberinfo['bio']);
                $memberinfo['bio'] = nl2br($memberinfo['bio']);

                $emailblock = '';
                if ($memberinfo['showemail'] == 'yes') {
                    eval('$emailblock = "'.template('member_profile_email').'";');
                }

                if (X_SADMIN) {
                    $admin_edit = "<br />$lang[adminoption] <a href=\"./editprofile.php?user=$encodeuser\">$lang[admin_edituseraccount]</a>";
                } else {
                    $admin_edit = NULL;
                }

                if ($memberinfo['mood'] != '') {
                    $memberinfo['mood'] = postify($memberinfo['mood'], 'no', 'no', 'yes', 'no', 'yes', 'no', true, 'yes');
                } else {
                    $memberinfo['mood'] = '';
                }

                $memberinfo['location'] = censor($memberinfo['location']);
                $memberinfo['aim'] = censor($memberinfo['aim']);
                $memberinfo['aimrecode'] = recodeOut($memberinfo['aim']);
                $memberinfo['icq'] = ($memberinfo['icq'] > 0) ? $memberinfo['icq'] : '';
                $memberinfo['yahoo'] = censor($memberinfo['yahoo']);
                $memberinfo['yahoorecode'] = recodeOut($memberinfo['yahoo']);
                $memberinfo['msn'] = censor($memberinfo['msn']);
                $memberinfo['msnrecode'] = recodeOut($memberinfo['msn']);

                if ($memberinfo['bday'] === iso8601_date(0,0,0)) {
                    $memberinfo['bday'] = $lang['textnone'];
                } else {
                    $memberinfo['bday'] = gmdate($dateformat, gmmktime(12,0,0,substr($memberinfo['bday'],5,2),substr($memberinfo['bday'],8,2),substr($memberinfo['bday'],0,4)));
                }

                // Forum most active in
                $found = false;
                $query = $db->query("SELECT f.userlist, f.password, f.postperm, f.moderator, f.name, p.fid, COUNT(DISTINCT p.pid) as posts FROM ".X_PREFIX."posts p LEFT JOIN ".X_PREFIX."forums f ON p.fid=f.fid WHERE p.author='$member' AND f.status='on' GROUP BY p.fid ORDER BY posts DESC");
                while($f = $db->fetch_array($query)) {
                    $pp = checkForumPermissions($f);
                    if (($pp[X_PERMS_VIEW] || $pp[X_PERMS_USERLIST]) && $pp[X_PERMS_PASSWORD]) {
                        $forum = $f;
                        $found = true;
                        break;
                    }
                }

                if (!$found || $forum['posts'] < 1) {
                    $topforum = $lang['textnopostsyet'];
                } else if ($memberinfo['postnum'] <= 0) {
                    $topforum = $lang['textnopostsyet'];
                } else {
                    $topforum = "<a href=\"./forumdisplay.php?fid=$forum[fid]\">".fnameOut($forum['name'])."</a> ($forum[posts] $lang[memposts]) [".round(($forum['posts']/$memberinfo['postnum'])*100, 1)."% $lang[textoftotposts]]";
                }

                // Last post
                $lpfound = false;
                $pq = $db->query("SELECT t.tid, t.subject, p.dateline, p.pid, f.fid, f.postperm, f.password, f.userlist, f.moderator FROM ".X_PREFIX."posts p, ".X_PREFIX."threads t, ".X_PREFIX."forums f WHERE p.fid=f.fid AND p.author='$memberinfo[username]' AND p.tid=t.tid AND f.status='on' ORDER BY p.dateline DESC");
                while($post = $db->fetch_array($pq)) {
                    $pp = checkForumPermissions($post);
                    if (!(($pp[X_PERMS_VIEW] || $pp[X_PERMS_USERLIST]) && $pp[X_PERMS_PASSWORD])) {
                        continue;
                    }
                    $lpfound = true;
                    $posts = $db->result($db->query("SELECT count(pid) FROM ".X_PREFIX."posts WHERE tid='$post[tid]' AND pid < '$post[pid]'"), 0)+1; // +1 is faster than doing <= !
                    validatePpp();

                    $page = quickpage($posts, $ppp);

                    $lastpostdate = gmdate($dateformat, $post['dateline'] + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600));
                    $lastposttime = gmdate($timecode, $post['dateline'] + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600));

                    $lastposttext = $lastpostdate.' '.$lang['textat'].' '.$lastposttime;
                    $post['subject'] = rawHTMLsubject(stripslashes($post['subject']));
                    $lastpost = "<a href=\"./viewthread.php?tid=$post[tid]&amp;page=$page#pid$post[pid]\">$post[subject]</a> ($lastposttext)";
                    break;
                }
                if (!$lpfound) {
                    $lastpost = $lang['textnopostsyet'];
                }

                $lang['searchusermsg'] = str_replace('*USER*', recodeOut($memberinfo['username']), $lang['searchusermsg']);
                eval('echo "'.template('member_profile').'";');
            }
        }
        break;

    default:
        error($lang['textnoaction']);
        break;
}

end_time();
eval('echo "'.template('footer').'";');
?>
