<?php
/* $Id: member.php,v 1.3.2.13 2005/10/28 15:11:32 Tularis Exp $ */
/*
    XMB 1.9.2
    © 2001 - 2005 Aventure Media & The XMB Development Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require "./header.php";

loadtemplates('footer_load', 'footer_querynum', 'footer_phpsql', 'footer_totaltime','member_coppa','member_reg_rules','member_reg_password','member_reg_avatarurl','member_reg_avatarlist','member_reg','member_profile_email','member_profile','misc_feature_not_while_loggedin','misc_feature_notavailable');
smcwcache();
eval("\$css = \"".template("css")."\";");

nav(($action=='reg') ? ($lang['textregister']) : (($action == 'viewpro') ? ($lang['textviewpro']) : (($action == 'coppa') ? ($lang['textcoppa']) : ($lang['error']))));

$checked = '';

if ($action == "coppa") {
    if (X_MEMBER) {
        eval("\$featurelin = \"".template("misc_feature_not_while_loggedin")."\";");
        eval("echo (\"".template('header')."\");");
        echo $featurelin;
        end_time();
        eval("echo (\"".template('footer')."\");");
        exit();
    }

    if ($SETTINGS['coppa'] != 'on') {
        redirect("./member.php?action=reg", 0);
    }

    if (isset($coppasubmit)) {
        redirect("member.php?action=reg", 0);
    } else {
        eval("echo (\"".template('header')."\");");
        eval('echo stripslashes("'.template('member_coppa').'");');
    }


} elseif ($action == "reg") {
    $time = time()-86400; // take the date and distract 24 hours from it
    $query = $db->query("SELECT count(uid) FROM $table_members WHERE regdate > '$time'");
    // Select amount of registrations since $time, which is since 24 hours.
    if ($db->result($query, 0) > $max_reg_day) {
        error($lang['max_regs']);
    }

    if ($regstatus != "on") {
        eval("\$featureoff = \"".template("misc_feature_notavailable")."\";");
        eval("echo (\"".template('header')."\");");
        echo $featureoff;
        end_time();
        eval("echo (\"".template('footer')."\");");
        exit();
    }

    if ( X_MEMBER ) {
        eval("\$featurelin = \"".template("misc_feature_not_while_loggedin")."\";");
        eval("echo (\"".template('header')."\");");
        echo $featurelin;
        end_time();
        eval("echo (\"".template('footer')."\");");
        exit();
    }

    if (!isset($regsubmit)) {
        eval("echo (\"".template('header')."\");");
        if ($bbrules == "on" && !isset($rulesubmit)) {
            $bbrulestxt = nl2br(stripslashes(stripslashes($bbrulestxt)));
            eval('echo stripslashes("'.template('member_reg_rules').'");');
        } else {

            $newschecked = 'checked="checked"';

            $currdate = gmdate($timecode, time()+ ($addtime * 3600));
            eval($lang['evaloffset']);

            $themelist   = array();
            $themelist[] = '<select name="thememem">';
            $themelist[] = '<option value="0">'.$lang['textusedefault'].'</option>';
            $query = $db->query("SELECT themeid, name FROM $table_themes ORDER BY name ASC");
            while ($themeinfo = $db->fetch_array($query)) {
                $themelist[] = '<option value="'.$themeinfo['themeid'].'">'.stripslashes($themeinfo['name']).'</option>';
            }
            $themelist[] = '</select>';
            $themelist   = implode("\n", $themelist);

			$lfs = array();
			$dir = opendir(ROOT.'/lang/');
			while($file = readdir($dir)) {
				if (is_file(ROOT.'/lang/'.$file) && false !== strpos($file, '.lang.php')) {
					$file = str_replace('.lang.php', '', $file);
					if ($file == $SETTINGS['langfile']) {
						$lfs[] = '<option value="' .$file. '" selected="selected">'.$file.'</option>';
					} else {
						$lfs[] = '<option value="' .$file. '">'.$file.'</option>';
					}
				}
			}
			natcasesort($lfs);
			$langfileselect = '<select name="newlangfile">'.implode("\n", $lfs).'</select>';

            $dayselect   = array();
            $dayselect[] = '<select name="day">';
            $dayselect[] = '<option value="">&nbsp;</option>';
            for ($num = 1; $num <= 31; $num++) {
                $dayselect[] = '<option value="'.$num.'">'.$num.'</option>';
            }
            $dayselect[] = '</select>';
            $dayselect   = implode("\n", $dayselect);

            if ($sigbbcode == "on") {
                $bbcodeis = $lang['texton'];
            } else {
                $bbcodeis = $lang['textoff'];
            }

            if ($sightml == "on") {
                $htmlis = $lang['texton'];
            } else {
                $htmlis = $lang['textoff'];
            }

            if ($emailcheck != "on") {
                eval('$pwtd = "'.template('member_reg_password').'";');
            } else {
                $pwtd = '';
            }

			$timezone1 = $timezone2 = $timezone3 = $timezone4 = $timezone5 = $timezone6 = '';
			$timezone7 = $timezone8 = $timezone9 = $timezone10 = $timezone11 = $timezone12 = '';
			$timezone13 = $timezone14 = $timezone15 = $timezone16 = $timezone17 = $timezone18 = '';
			$timezone19 = $timezone20 = $timezone21 = $timezone22 = $timezone23 = $timezone24 = '';
			$timezone25 = $timezone26 = $timezone27 = $timezone28 = $timezone29 = $timezone30 = '';
			$timezone31 = $timezone32 = $timezone33 = '';

			switch($SETTINGS['def_tz']) {
				case '-12.00':
					$timezone1 = 'selected="selected"';
					break;
				case '-11.00':
					$timezone2 = 'selected="selected"';
					break;
				case '-10.00':
					$timezone3 = 'selected="selected"';
					break;
				case '-9.00':
					$timezone4 = 'selected="selected"';
					break;
				case '-8.00':
					$timezone5 = 'selected="selected"';
					break;
				case '-7.00':
					$timezone6 = 'selected="selected"';
					break;
				case '-6.00':
					$timezone7 = 'selected="selected"';
					break;
				case '-5.00':
					$timezone8 = 'selected="selected"';
					break;
				case '-4.00':
					$timezone9 = 'selected="selected"';
					break;
				case '-3.50':
					$timezone10 = 'selected="selected"';
					break;
				case '-3.00':
					$timezone11 = 'selected="selected"';
					break;
				case '-2.00':
					$timezone12 = 'selected="selected"';
					break;
				case '-1.00':
					$timezone13 = 'selected="selected"';
					break;

				case '1.00':
					$timezone15 = 'selected="selected"';
					break;
				case '2.00':
					$timezone16 = 'selected="selected"';
					break;
				case '3.00':
					$timezone17 = 'selected="selected"';
					break;
				case '3.50':
					$timezone18 = 'selected="selected"';
					break;
				case '4.00':
					$timezone19 = 'selected="selected"';
					break;
				case '4.50':
					$timezone20 = 'selected="selected"';
					break;
				case '5.00':
					$timezone21 = 'selected="selected"';
					break;
				case '5.50':
					$timezone22 = 'selected="selected"';
					break;
				case '5.75':
					$timezone23 = 'selected="selected"';
					break;
				case '6.00':
					$timezone24 = 'selected="selected"';
					break;
				case '6.50':
					$timezone25 = 'selected="selected"';
					break;
				case '7.00':
					$timezone26 = 'selected="selected"';
					break;
				case '8.00':
					$timezone27 = 'selected="selected"';
					break;
				case '9.00':
					$timezone28 = 'selected="selected"';
					break;
				case '9.50':
					$timezone29 = 'selected="selected"';
					break;
				case '10.00':
					$timezone30 = 'selected="selected"';
					break;
				case '11.00':
					$timezone31 = 'selected="selected"';
					break;
				case '12.00':
					$timezone32 = 'selected="selected"';
					break;
				case '13.00':
					$timezone33 = 'selected="selected"';
					break;

				case '0.00':
				default:
					$timezone14 = 'selected="selected"';
					break;		
			}            

            if ($avastatus == 'on') {
                eval('$avatd = "'.template('member_reg_avatarurl').'";');
            } elseif ($avastatus == 'list') {
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
            eval('echo stripslashes("'.template('member_reg').'");');
        }

    } else {
        $find = array('<', '>', '|', '"', '[', ']', '\\', ',', '@', '\'', ' ');
        foreach ($find as $needle) {
            if (false !== strpos($username, $needle)) {
                error($lang['restricted']);
            }
        }

        if ($ipreg != 'off') {
            $time = time()-86400;
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

        if ($password == "" || strpos($password, '"') != false || strpos($password, "'") != false) {
            error($lang['textpw1']);
        }

        if (trim($username) == '') {
            error($lang['textnousername']);
        }

        $query = $db->query("SELECT COUNT(uid) FROM $table_members");
        $count1 = $db->result($query,0);

        $self['status'] = ($count1 != 0) ? 'Member' : 'Super Administrator';

        $showemail = (isset($showemail) && $showemail == 'yes') ? 'yes' : 'no';
        $newsletter = (isset($newsletter) && $newsletter == 'yes') ? 'yes' : 'no';
        $saveogu2u = (isset($saveogu2u) && $saveogu2u == 'yes') ? 'yes' : 'no';
        $emailonu2u = (isset($emailonu2u) && $emailonu2u == 'yes') ? 'yes' : 'no';
        $useoldu2u = (isset($useoldu2u) && $useoldu2u == 'yes') ? 'yes' : 'no';

        $bday = "$month $day, $year";

        if ($month == "" || $day == "" || $year == "") {
            $bday = "";
        }

        $avatar     = checkInput($avatar, '', '', "javascript", false);
        $dateformatnew    = checkInput($dateformatnew, '', '', "javascript", false);
        $locationnew    = checkInput($locationnew, '', '', "javascript", false);
        $icq        = checkInput($icq, '', '', "javascript", false);
        $yahoo        = checkInput($yahoo, '', '', "javascript", false);
        $aim        = checkInput($aim, '', '', "javascript", false);
        $msn        = checkInput($msn, '', '', "javascript", false);
        $email        = checkInput($email, '', '', "javascript", false);
        $site        = checkInput($site, '', '', "javascript", false);
        $webcam        = checkInput($webcam, '', '', "javascript", false);
        $bio        = checkInput($bio, '', '', "javascript", false);
        $bday        = checkInput($bday, '', '', "javascript", false);
        $mood        = checkInput($newmood, '', '', "javascript", false);
        $sig         = checkInput($_POST['sig']);

        $sig        = addslashes($sig);
        $bio        = addslashes($bio);
        $locationnew    = addslashes($locationnew);

        $password    = md5(trim($password));

        $size = @getimagesize($avatar);
        $max_size = explode('x', $SETTINGS['max_avatar_size']);
        if ($size === false) {
            $avatar = '';
        } elseif (($size[0] > $max_size[0] || $size[1] > $max_size[1]) && !X_SADMIN) {
            error($lang['avatar_too_big'] . $SETTINGS['max_avatar_size'] . 'px');
        }
        $db->query("INSERT INTO $table_members (uid, username, password, regdate, postnum, email, site, aim, status, location, bio, sig, showemail, timeoffset, icq, avatar, yahoo, customstatus, theme, bday, langfile, tpp, ppp, newsletter, regip, timeformat, msn, ban, dateformat, ignoreu2u, lastvisit, mood, pwdate, invisible, u2ufolders, saveogu2u, emailonu2u, useoldu2u, webcam) VALUES ('', '$username', '$password', ".$db->time(time()).", '0', '$email', '$site', '$aim', '$self[status]',  '$locationnew', '$bio', '$sig', '$showemail', '$timeoffset1', '$icq', '$avatar', '$yahoo', '', '$thememem', '$bday', '$newlangfile', '$tpp', '$ppp',  '$newsletter', '$onlineip', '$timeformatnew', '$msn', '', '$dateformatnew', '', '', '$newmood', '', '0', '', '$saveogu2u', '$emailonu2u', '$useoldu2u', '$webcam')");


        if ($SETTINGS['notifyonreg'] != "off") {
            if ($SETTINGS['notifyonreg'] == 'u2u') {
                $mailquery = $db->query("SELECT username FROM $table_members WHERE status='Super Administrator'");
                while ($admin = $db->fetch_array($mailquery)) {
                    $db->query("INSERT INTO $table_u2u ( u2uid, msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus ) VALUES ('', '$admin[username]', '".addslashes($bbname)."', 'incoming', '$admin[username]', 'Inbox', '$lang[textnewmember]', '$lang[textnewmember2]', '" . time() . "', 'no', 'yes')");
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
            $currtime = time() + (86400*30);
            put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
            put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
        }
        eval("echo (\"".template('header')."\");");
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
            eval("echo (\"".template('header')."\");");

            $daysreg = (time() - $memberinfo['regdate']) / (24*3600);
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

            if ($memberinfo['email'] != "" && $memberinfo['showemail'] == "yes") {
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
				if (false !== strpos($memberinfo['avatar'], ",") && substr($memberinfo['avatar'], $pos-4, 4) == '.swf') {
					$flashavatar = explode(",",$memberinfo['avatar']);
					$memberinfo['avatar'] = '<object type="application/x-shockwave-flash" data="'.$flashavatar[0].'" width="'.$flashavatar[1].'" height="'.$flashavatar[2].'"><param name="movie" value="'.$flashavatar[0].'" /></object>';
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
                $lastvisitdate = gmdate("$dateformat",$memberinfo['lastvisit'] + ($timeoffset * 3600) + ($addtime * 3600));
                $lastvisittime = gmdate("$timecode",$memberinfo['lastvisit'] + ($timeoffset * 3600) + ($addtime * 3600));
                $lastmembervisittext = "$lastvisitdate $lang[textat] $lastvisittime";
            }

            $query = $db->query("SELECT COUNT(pid) FROM $table_posts");
            $posts = $db->result($query, 0);

            $posttot = $posts;
            if ($posttot == 0) {
                $percent = "0";
            } else {
                $percent = $memberinfo['postnum']*100/$posttot;
                $percent = round($percent, 2);
            }

            $memberinfo['bio'] = stripslashes(censor($memberinfo['bio']));
            $memberinfo['bio'] = nl2br($memberinfo['bio']);
            $encodeuser = rawurlencode($memberinfo['username']);

            if ($memberinfo['showemail'] == "yes") {
                eval("\$emailblock = \"".template("member_profile_email")."\";");
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
                $memberinfo['mood'] = '&nbsp;';
            }

            $memberinfo['location'] = censor($memberinfo['location']);
            $memberinfo['aim'] = censor($memberinfo['aim']);
            $memberinfo['icq'] = censor($memberinfo['icq']);
            $memberinfo['yahoo'] = censor($memberinfo['yahoo']);
            $memberinfo['msn'] = censor($memberinfo['msn']);

			$modXmbuser = str_replace(array('*', '.', '+'), array('\*', '\.', '\+'), $self['username']);
			$restrict = array('(1=1)'); // prevents empty restrictions
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

            // Forum most active in
            $query = $db->query("SELECT f.name, p.fid, COUNT(DISTINCT p.pid) as posts FROM $table_posts p LEFT JOIN $table_forums f ON p.fid=f.fid WHERE $restrict AND p.author='$member' GROUP BY p.fid ORDER BY posts DESC LIMIT 1");
            $forum = $db->fetch_array($query);

            if (!($forum['posts'] > 0)) {
                $topforum = $lang['textnopostsyet'];
            } elseif($memberinfo['postnum'] <= 0) {
            	$topforum = $lang['textnopostsyet'];
            } else {
                $topforum = "<a href=\"./forumdisplay.php?fid=$forum[fid]\">$forum[name]</a> ($forum[posts] $lang[textdeleteposts]) [".round(($forum['posts']/$memberinfo['postnum'])*100, 1)."% of total posts]";
            }

            // Last post
            $query = $db->query("SELECT t.tid, t.subject, p.dateline, p.pid FROM $table_posts p, $table_threads t LEFT JOIN $table_forums f ON p.fid=f.fid WHERE $restrict AND p.author='$memberinfo[username]' AND p.tid=t.tid ORDER BY p.dateline DESC LIMIT 1");
            if ($post = $db->fetch_array($query)) {
                $posts = $db->result($db->query("SELECT count(pid) FROM $table_posts WHERE tid='$post[tid]' AND pid < '$post[pid]'"), 0)+1; // +1 is faster than doing <= !
                validatePpp();

                $page = quickpage($posts, $ppp);

                $lastpostdate = gmdate($self['dateformat'], $post['dateline'] + ($self['timeoffset'] * 3600) + ($SETTINGS['addtime'] * 3600));
                $lastposttime = gmdate($self['timecode'], $post['dateline'] + ($self['timeoffset'] * 3600) + ($SETTINGS['addtime'] * 3600));
                $lastposttext = "$lastpostdate $lang[textat] $lastposttime";
                $post['subject'] = censor($post['subject']);
                $lastpost = "<a href=\"./viewthread.php?tid=$post[tid]&amp;page=$page#pid$post[pid]\">$post[subject]</a> ($lastposttext)";
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
eval("echo (\"".template('footer')."\");");
?>
