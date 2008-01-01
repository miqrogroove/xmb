<?php
/* $Id: memcp.php,v 1.3.2.8 2005/09/18 18:20:42 Tularis Exp $ */
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

require "header.php";

loadtemplates('buddylist_buddy_offline', 'buddylist_buddy_online', 'footer_load', 'footer_phpsql', 'footer_querynum', 'footer_totaltime', 'memcp_favs', 'memcp_favs_button', 'memcp_favs_none', 'memcp_favs_row', 'memcp_home', 'memcp_home_favs_none', 'memcp_home_favs_row', 'memcp_home_u2u_none', 'memcp_home_u2u_row', 'memcp_profile', 'memcp_profile_avatarlist', 'memcp_profile_avatarurl', 'memcp_subscriptions', 'memcp_subscriptions_button', 'memcp_subscriptions_none', 'memcp_subscriptions_row');
smcwcache();
eval("\$css = \"".template("css")."\";");

$favs = NULL;
$buddys = NULL;

// Determine the navigation

$action = isset($action) ? $action : '';
switch ($action) {
    case "profile":
        nav('<a href="memcp.php">'.$lang['textusercp'].'</a>');
        nav($lang['texteditpro']);
        break;

    case "subscriptions":
        nav('<a href="memcp.php">'.$lang['textusercp'].'</a>');
        nav($lang['textsubscriptions']);
        break;

    case "favorites":
        nav('<a href="memcp.php">'.$lang['textusercp'].'</a>');
        nav($lang['textfavorites']);
        break;

    default:
        nav($lang['textusercp']);
        break;
}


/**
* makenav() - Create the user control panel navigation links
*
* Create the user control panel navigation links in HTML
*
* @param    $current    current URI we are viewing from (can be one of blank (default), profile, subscriptions, or favorites)
* @return   no return value
*/
function makenav($current) {
    global $bordercolor, $tablewidth, $borderwidth, $tablespacing, $altbg1, $altbg2, $lang;

    ?>
    <table cellpadding="0" cellspacing="0" border="0" bgcolor="<?php echo $bordercolor?>" width="<?php echo $tablewidth?>" align="center"><tr><td>
    <table cellpadding="4" cellspacing="1" border="0" width="100%">
    <tr align="center" class="tablerow">

    <?php

    if ($current == "") {
        echo "<td bgcolor=\"$altbg1\" width=\"15%\" class=\"ctrtablerow\">" .$lang['textmyhome']. "</td>";
    } else {
        echo "<td bgcolor=\"$altbg2\" width=\"15%\" class=\"ctrtablerow\"><a href=\"memcp.php\">" .$lang['textmyhome']. "</a></td>";
    }

    if ($current == "profile") {
        echo "<td bgcolor=\"$altbg1\" width=\"15%\" class=\"ctrtablerow\">" .$lang['texteditpro']. "</td>";
    } else {
        echo "<td bgcolor=\"$altbg2\" width=\"15%\" class=\"ctrtablerow\"><a href=\"memcp.php?action=profile\">" .$lang['texteditpro']. "</a></td>";
    }

    if ($current == "subscriptions") {
        echo "<td bgcolor=\"$altbg1\" width=\"15%\" class=\"ctrtablerow\">" .$lang['textsubscriptions']. "</td>";
    } else {
        echo "<td bgcolor=\"$altbg2\" width=\"15%\" class=\"ctrtablerow\"><a href=\"memcp.php?action=subscriptions\">" .$lang['textsubscriptions']. "</a></td>";
    }

    if ($current == "favorites") {
        echo "<td bgcolor=\"$altbg1\" width=\"15%\" class=\"ctrtablerow\">" .$lang['textfavorites']. "</td>";
    } else {
        echo "<td bgcolor=\"$altbg2\" width=\"15%\" class=\"ctrtablerow\"><a href=\"memcp.php?action=favorites\">" .$lang['textfavorites']. "</a></td>";
    }

    echo "<td bgcolor=\"$altbg2\" width=\"20%\" class=\"ctrtablerow\"><a href=\"#\" onclick=\"Popup('u2u.php', 'Window', 700, 450);\">" .$lang['textu2umessenger']. "</a></td>";
    echo "<td bgcolor=\"$altbg2\" width=\"15%\" class=\"ctrtablerow\"><a href=\"#\" onclick=\"Popup('buddy.php?', 'Window', 450, 400);\">" .$lang['textbuddylist']. "</a></td>";
    echo "<td bgcolor=\"$altbg2\" width=\"10%\" class=\"ctrtablerow\"><a href=\"faq.php\">" .$lang['helpbar']. "</a></td>";

    ?>
    </tr>
    </table>
    </td></tr></table><br />

    <?php
}

// Determine if user is logged in, if not send to login page
if ( X_GUEST ) {
    redirect('misc.php?action=login', 0);
    exit();
}

// Start Profile Editor Code
if ($action == "profile") {
    eval("echo (\"".template('header')."\");");
    makenav($action);

    if (!isset($editsubmit)) {
        $member = $self;

        $checked = '';
        if ($member['showemail'] == "yes") {
            $checked = "checked=\"checked\"";
        }

        $newschecked = '';
        if ($member['newsletter'] == "yes") {
            $newschecked = "checked=\"checked\"";
        }

        $uou2uchecked = '';
        if ($member['useoldu2u'] == "yes") {
            $uou2uchecked = "checked=\"checked\"";
        }

        $ogu2uchecked = '';
        if ($member['saveogu2u'] == "yes") {
            $ogu2uchecked = "checked=\"checked\"";
        }

        $eouchecked = '';
        if ($member['emailonu2u'] == "yes") {
            $eouchecked = "checked=\"checked\"";
        }

        $invchecked = '';
        if ($member['invisible'] == 1) {
            $invchecked = "checked=\"checked\"";
        }

        $currdate = gmdate("$timecode", time()+ ($addtime * 3600));
        eval($lang['evaloffset']);

        $timezone1 = $timezone2 = $timezone3 = $timezone4 = $timezone5 = $timezone6 = "";
        $timezone7 = $timezone8 = $timezone9 = $timezone10 = $timezone11 = $timezone12 = "";
        $timezone13 = $timezone14 = $timezone15 = $timezone16 = $timezone17 = $timezone18 = "";
        $timezone19 = $timezone20 = $timezone21 = $timezone22 = $timezone23 = $timezone24 = "";
        $timezone25 = $timezone26 = $timezone27 = $timezone28 = $timezone29 = $timezone30 = "";
        $timezone31 = $timezone32 = $timezone33 = "";

        if ($member['timeoffset'] == "-12") { $timezone1 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "-11") { $timezone2 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "-10") { $timezone3 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "-9") { $timezone4 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "-8") { $timezone5 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "-7") { $timezone6 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "-6") { $timezone7 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "-5") { $timezone8 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "-4") { $timezone9 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "-3.5") { $timezone10 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "-3") { $timezone11 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "-2") { $timezone12 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "-1") { $timezone13 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "0") { $timezone14 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "1") { $timezone15 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "2") { $timezone16 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "3") { $timezone17 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "3.5") { $timezone18 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "4") { $timezone19 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "4.5") { $timezone20 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "5") { $timezone21 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "5.5") { $timezone22 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "5.75") { $timezone23 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "6") { $timezone24 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "6.5") { $timezone25 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "7") { $timezone26 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "8") { $timezone27 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "9") { $timezone28 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "9.5") { $timezone29 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "10") { $timezone30 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "11") { $timezone31 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "12") { $timezone32 = "selected=\"selected\""; }
        elseif ($member['timeoffset'] == "13") { $timezone33 = "selected=\"selected\""; }

        $themelist   = array();
        $themelist[] = '<select name="thememem">';
        $themelist[] = '<option value="0">'.$lang['textusedefault'].'</option>';
        $query = $db->query("SELECT themeid, name FROM $table_themes ORDER BY name ASC");
        while ($themeinfo = $db->fetch_array($query)) {
            if ($themeinfo['themeid'] == $member['theme']) {
                $themelist[] = '<option value="'.$themeinfo['themeid'].'" selected="selected">'.stripslashes($themeinfo['name']).'</option>';
            } else {
                $themelist[] = '<option value="'.$themeinfo['themeid'].'">'.stripslashes($themeinfo['name']).'</option>';
            }
        }
        $themelist[] = '</select>';
        $themelist   = implode("\n", $themelist);

        $lfs = array();
        $dir = opendir(ROOT.'/lang/');
        while($file = readdir($dir)) {
            if (is_file(ROOT.'/lang/'.$file) && false !== strpos($file, '.lang.php')) {
                $file = str_replace('.lang.php', '', $file);
                if ($file == $member['langfile']) {
                    $lfs[] = '<option value="' .$file. '" selected="selected">'.$file.'</option>';
                } else {
                    $lfs[] = '<option value="' .$file. '">'.$file.'</option>';
                }
            }
        }
        natcasesort($lfs);
        $langfileselect = '<select name="langfilenew">'.implode("\n", $lfs).'</select>';

        $member['bday'] = str_replace(",", "", $member['bday']);
        $bday = explode(" ", $member['bday']);

        $sel0 = $sel1 = $sel2 = $sel3 = $sel4 = $sel5 = $sel6 = "";
        $sel7 = $sel8 = $sel9 = $sel10 = $sel11 = $sel12 = "";

        if ($bday['0'] == "") {
            $sel0 = "selected=\"selected\"";
        } elseif ($bday['0'] == $lang['textjan']) {
            $sel1 = "selected=\"selected\"";
        } elseif ($bday['0'] == $lang['textfeb']) {
            $sel2 = "selected=\"selected\"";
        } elseif ($bday['0'] == $lang['textmar']) {
            $sel3 = "selected=\"selected\"";
        } elseif ($bday['0'] == $lang['textapr']) {
            $sel4 = "selected=\"selected\"";
        } elseif ($bday['0'] == $lang['textmay']) {
            $sel5 = "selected=\"selected\"";
        } elseif ($bday['0'] == $lang['textjun']) {
            $sel6 = "selected=\"selected\"";
        } elseif ($bday['0'] == $lang['textjul']) {
            $sel7 = "selected=\"selected\"";
        } elseif ($bday['0'] == $lang['textaug']) {
            $sel8 = "selected=\"selected\"";
        } elseif ($bday['0'] == $lang['textsep']) {
            $sel9 = "selected=\"selected\"";
        } elseif ($bday['0'] == $lang['textoct']) {
            $sel10 = "selected=\"selected\"";
        } elseif ($bday['0'] == $lang['textnov']) {
            $sel11 = "selected=\"selected\"";
        } elseif ($bday['0'] == $lang['textdec']) {
            $sel12 = "selected=\"selected\"";
        }

        $dayselect = "<select name=\"day\">\n";
        $dayselect .= "<option value=\"\">&nbsp;</option>\n";
        for ($num = 1; $num <= 31; $num++) {
            if (isset($bday[1]) && $bday[1] == $num) {
                $dayselect .= "<option value=\"" .$num. "\" selected=\"selected\">" .$num. "</option>\n";
            } else {
                $dayselect .= "<option value=\"" .$num. "\">" .$num. "</option>\n";
            }
        }
        $dayselect .= "</select>";

        $check12 = $check24 = "";

        if ($member['timeformat'] == "24") {
            $check24 = "checked=\"checked\"";
        } else {
            $check12 = "checked=\"checked\"";
        }

        if ($sigbbcode == "on") {
            $bbcodeis = $lang['texton'];
        } else {
            $bbcodeis = $lang['textoff'];
        }

        if ($sightml == "on") {
            $sigallowhtml = "yes";
            $htmlis = $lang['texton'];
        } else {
            $sigallowhtml = "no";
            $htmlis = $lang['textoff'];
        }

        $avatar = '';
        if ($avastatus == "on") {
            eval("\$avatar = \"".template("memcp_profile_avatarurl")."\";");
        }

        if ($avastatus == "list") {
            $avatars = " <option value=\"\" />" .$lang['textnone']. "</option>  ";
            $dir1 = opendir("./images/avatars");
            while ($avatar1 = readdir($dir1)) {
                if (is_file("./images/avatars/" .$avatar1. "")) {
                    $avatars .= " <option value=\"./images/avatars/" .$avatar1. "\" />" .$avatar1. "</option>  ";
                }
            }

            $avatars = str_replace("value=\"" .$member['avatar']. "\"", "value=\"" .$member['avatar']. "\" SELECTED", $avatars);
            $avatarbox = "<select name=\"newavatar\" onchange=\"document.images.avatarpic.src =this[this.selectedIndex].value;\">" .$avatars. "</select>";
            eval("\$avatar = \"".template("memcp_profile_avatarlist")."\";");
            closedir($dir1);
        }

        if ( !isset($bday[2]) ) {
            $bday[2] = '';
        }

        eval("\$profile = \"".template("memcp_profile")."\";");
        $profile = stripslashes($profile);
        echo $profile;
    }

    if (isset($editsubmit)) {
        reset($self);
        $member = $self;

        if (!$member['username']) {
            error($lang['badname'], false);
        }

        if ($xmbpw != $member['password']) {
            error($lang['textpwincorrect'], false);
        }

        if ( isset($newemail) && ( !isset($_POST['newemail']) || isset($_GET['newemail'])) ) {
            $auditaction = $_SERVER['REQUEST_URI'];
            $aapos = strpos($auditaction, "?");
            if ( $aapos !== false ) {
                $auditaction = substr($auditaction, $aapos + 1);
            }
            $auditaction = addslashes("$onlineip|#|$auditaction");
            audit($xmbuser, $auditaction, 0, 0, "Potential XSS exploit using newemail");
            die("Hack atttempt recorded in audit logs.");
        }

        if ( isset($newpassword) && (!isset($_POST['newpassword']) || isset($_GET['newpassword'])) ) {
            $auditaction = $_SERVER['REQUEST_URI'];
            $aapos = strpos($auditaction, "?");
            if ( $aapos !== false ) {
                $auditaction = substr($auditaction, $aapos + 1);
            }
            $auditaction = addslashes("$onlineip|#|$auditaction");
            audit($xmbuser, $auditaction, 0, 0, "Potential XSS exploit using newpassword");
            die("Hack atttempt recorded in audit logs.");
        }

        $showemail = ( isset($showemail) && $showemail == "yes" ) ? "yes" : "no";
        $newsletter = ( isset($newsletter) && $newsletter == "yes" ) ? "yes" : "no";
        $saveogu2u = ( isset($saveogu2u) && $saveogu2u == "yes" ) ? "yes" : "no";
        $emailonu2u = ( isset($emailonu2u) && $emailonu2u == "yes" ) ? "yes" : "no";
        $useoldu2u = ( isset($useoldu2u) && $useoldu2u == "yes" ) ? "yes" : "no";

        $bday = "$month $day, $year";

        if ($month == "" || $day == "" || $year == "") {
            $bday = "";
        }

        $newavatar      = isset($newavatar) ? ereg_replace(' ', '%20', $newavatar) : ''; // Problem with spaces in avatar url
        $avatar         = checkInput($newavatar, '', '', 'javascript', false);

        $memlocation    = isset($newmemlocation) ? checkInput($newmemlocation, '', '', 'javascript', false) : '';
        $icq            = isset($newicq) ? checkInput($newicq, '', '', 'javascript', false) : '';
        $yahoo          = isset($newyahoo) ? checkInput($newyahoo, '', '', 'javascript', false) : '';
        $aim            = isset($newaim) ? checkInput($newaim, '', '', 'javascript', false) : '';
        $msn            = isset($newmsn) ? checkInput($newmsn, '', '', 'javascript', false) : '';

        // Do not allow blank email addresses.
        if ( isset($newemail) ) {
            $newemail = trim($newemail);
            if ( $newemail != '' ) {
                $email = checkInput($newemail, '', '', 'javascript', false);
            } else {
                $email = $member['email'];
            }
        }

        $site           = isset($newsite) ? checkInput($newsite, '', '', 'javascript', false) : '';
        $webcam         = isset($newwebcam) ? checkInput($newwebcam, '', '', 'javascript', false) : '';
        $bio            = isset($newbio) ? checkInput($newbio, '', '', 'javascript', false) : '';
        $bday           = isset($bday) ? checkInput($bday, '', '', 'javascript', false) : '';
        $mood           = isset($newmood) ? checkInput($newmood, '', '', 'javascript', false) : '';
        $pstatus        = isset($newpstatus) ? checkInput($newpstatus, '', '', 'javascript', false) : '';
        $sig            = isset($newsig) ? checkInput($newsig, '', $SETTINGS['sightml'], '', false) : '';
        
        if($SETTINGS['resetsigs'] == 'on') {
			if(strlen(trim($self['sig'])) == 0) {
				if(strlen($sig) > 0) {
					// we have a sig now, reset posts to show it
					$db->query("UPDATE $table_posts SET usesig='yes' WHERE author='".$self['username']."'");
				}
			} else {
				if(strlen(trim($sig)) == 0) {
					// we had a sig, but lost it now. Reset posts to hide it
					$db->query("UPDATE $table_posts SET usesig='no' WHERE author='".$self['username']."'");
				}
			}
		}

        $sig            = addslashes($sig);
        $bio            = addslashes($bio);
        $memlocation    = addslashes($memlocation);

        $invisible      = (isset($newinv) && $newinv == 1) ? 1 : 0;
        $showemail      = (isset($newshowemail) && $newshowemail == 'yes') ? 'yes' : 'no';
        $newsletter     = (isset($newnewsletter) && $newnewsletter == 'yes') ? 'yes' : 'no';

        $max_size = explode('x', $SETTINGS['max_avatar_size']);
        if($max_size[0] > 0 && $max_size[1] > 0 && substr_count($avatar, ',') < 2) {
        	// we ignore flash avatars here
            $size = @getimagesize($avatar);
            if($size === false ) {
                $avatar = '';
            } elseif(($size[0] > $max_size[0] && $max_size[0] > 0) || ($size[1] > $max_size[1] && $max_size[1] > 0)) {
                error($lang['avatar_too_big'] . $SETTINGS['max_avatar_size'] . 'px', false);
            }
        }

        $newpassword = trim($newpassword);
        $newpasswordcf = trim($newpasswordcf);

        if ($newpassword != '' || $newpasswordcf != '' ) {
            if ($newpassword != $newpasswordcf ) {
                error($lang['pwnomatch'], false);
            }

            $newpassword = md5($newpassword);

            $pwtxt = "password='$newpassword',";

            $currtime = time() - (86400*30);
            put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
            put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
        } else {
            $pwtxt = '';
        }

        $db->query("UPDATE $table_members SET $pwtxt email='$email', site='$site', aim='$aim', location='$memlocation', bio='$bio', sig='$sig', showemail='$showemail', timeoffset='$timeoffset1', icq='$icq', avatar='$avatar', yahoo='$yahoo', theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew', newsletter='$newsletter', timeformat='$timeformatnew', msn='$msn', dateformat='$dateformatnew', mood='$mood', invisible='$invisible', saveogu2u='$saveogu2u', emailonu2u='$emailonu2u', useoldu2u='$useoldu2u', webcam='$webcam' WHERE username='$xmbuser'");

        echo "<center><span class=\"mediumtxt \">" .$lang['usercpeditpromsg']. "</span></center>";
        redirect('memcp.php', 2.5, X_REDIRECT_JS);
    }
}

// Start Favorites
elseif ($action == "favorites") {
    eval("echo (\"".template('header')."\");");

    makenav($action);

    if (!isset($favsubmit) && isset($favadd) && is_numeric($favadd)) {
        $favadd = (int) $favadd;
        if ( $favadd == 0 ) {
            error($lang['fnasorry'], false);
        }
        $query = $db->query("SELECT tid FROM $table_favorites WHERE tid='$favadd' AND username='$xmbuser' AND type='favorite'");
        $favthread = $db->fetch_array($query);

        if ($favthread) {
            error($lang['favonlistmsg'], false);
        }

        $db->query("INSERT INTO $table_favorites ( tid, username, type ) VALUES ('$favadd', '$xmbuser', 'favorite')");
        echo "<center><span class=\"mediumtxt \">" .$lang['favaddedmsg']. "</span></center>";
        redirect('memcp.php?action=favorites', 2, X_REDIRECT_JS);
    }

    if (!isset($favadd) && !isset($favsubmit)) {
        $query = $db->query("SELECT f.*, t.fid, t.icon, t.lastpost, t.subject, t.replies FROM $table_favorites f, $table_threads t WHERE f.tid=t.tid AND f.username='$xmbuser' AND f.type='favorite' ORDER BY t.lastpost DESC");
        $favnum = 0;
        $favs = '';

        $tmOffset = ($timeoffset * 3600) + ($addtime * 3600);

        while ($fav = $db->fetch_array($query)) {
            $query2 = $db->query("SELECT name, fup, fid FROM $table_forums WHERE fid='$fav[fid]'");
            $forum = $db->fetch_array($query2);
            $lastpost = explode("|", $fav['lastpost']);
            $dalast = $lastpost['0'];
            $lastpost['1'] = "<a href=\"member.php?action=viewpro&amp;member=".rawurlencode($lastpost['1'])."\">$lastpost[1]</a>";
            $lastreplydate = gmdate($dateformat, $lastpost['0'] + $tmOffset);
            $lastreplytime = gmdate($timecode, $lastpost['0'] + $tmOffset);
            $lastpost = "" .$lang['lastreply1']. " " .$lastreplydate. " " .$lang['textat']. " " .$lastreplytime. " " .$lang['textby']. " " .$lastpost['1']. "";
            $fav['subject'] = stripslashes(censor($fav['subject']));

            if ($fav['icon'] != "") {
                $fav['icon'] = "<img src=\"" .$smdir. "/" .$fav['icon']. "\" />";
            } else {
                $fav['icon'] = "&nbsp;";
            }

            $favnum++;
            eval("\$favs .= \"".template("memcp_favs_row")."\";");
        }

        // Fix By John Briggs
        $favsbtn = '';
        if ($favnum != 0) {
            eval("\$favsbtn = \"".template("memcp_favs_button")."\";");
        }
        // Fix By John Briggs

        if ($favnum == 0) {
            eval("\$favs = \"".template("memcp_favs_none")."\";");
        }

        eval("\$favorites = \"".template("memcp_favs")."\";");
        $favorites = stripslashes($favorites);
        echo $favorites;
    }

    if (!isset($favadd) && isset($favsubmit)) {
        $query = $db->query("SELECT tid FROM $table_favorites WHERE username='$xmbuser' AND type='favorite'");
        while($fav = $db->fetch_array($query)) {
            $delete = "delete" .$fav['tid']. "";
            $delete = "${$delete}";
            $db->query("DELETE FROM $table_favorites WHERE username='$xmbuser' AND tid='$delete' AND type='favorite'");
        }

        echo "<center><span class=\"mediumtxt \">" .$lang['favsdeletedmsg']. "</span></center>";
        redirect('memcp.php?action=favorites', 2, X_REDIRECT_JS);
    }
}

// Start Subscriptions
elseif ($action == "subscriptions") {
    eval("echo (\"".template('header')."\");");

    makenav($action);

    if (!isset($subadd) && !isset($subsubmit)) {
        $query = $db->query("SELECT f.*, t.fid, t.icon, t.lastpost, t.subject, t.replies FROM $table_favorites f, $table_threads t WHERE f.tid=t.tid AND f.username='$xmbuser' AND f.type='subscription' ORDER BY t.lastpost DESC");
        $subnum = 0;
        $subscriptions = '';

        $tmOffset = ($timeoffset * 3600) + ($addtime * 3600);

        while ($fav = $db->fetch_array($query)) {
            $query2 = $db->query("SELECT name, fup, fid FROM $table_forums WHERE fid='$fav[fid]'");
            $forum = $db->fetch_array($query2);
            $lastpost = explode("|", $fav['lastpost']);
            $dalast = $lastpost['0'];
            $lastpost['1'] = "<a href=\"member.php?action=viewpro&amp;member=".rawurlencode($lastpost['1'])."\">$lastpost[1]</a>";
            $lastreplydate = gmdate($dateformat, $lastpost['0'] + $tmOffset);
            $lastreplytime = gmdate($timecode, $lastpost['0'] + $tmOffset);
            $lastpost = "" .$lang['lastreply1']. " " .$lastreplydate. " " .$lang['textat']. " " .$lastreplytime. " " .$lang['textby']. " " .$lastpost['1']. "";
            $fav['subject'] = stripslashes(censor($fav['subject']));

            if ($fav['icon'] != "") {
                $fav['icon'] = "<img src=\"" .$smdir. "/" .$fav['icon']. "\" />";
            } else {
                $fav['icon'] = "&nbsp;";
            }
            $subnum++;
            eval("\$subscriptions .= \"".template("memcp_subscriptions_row")."\";");
        }

        // Fix By John Briggs
        $subsbtn = '';
        if ($subnum != 0) {
            eval("\$subsbtn = \"".template("memcp_subscriptions_button")."\";");
        }
        // Fix By John Briggs

        if ($subnum == 0) {
            eval("\$subscriptions = \"".template("memcp_subscriptions_none")."\";");
        }

        eval("\$page = \"".template("memcp_subscriptions")."\";");
        $page = stripslashes($page);
        echo $page;

    } elseif (isset($subadd) && !isset($subsubmit)) {
        $query = $db->query("SELECT count(tid) FROM $table_favorites WHERE tid='$subadd' AND username='$xmbuser' AND type='subscription'");
        if ($db->result($query,0) == 1) {
            error($lang['subonlistmsg'], false);
        } else {
            $db->query("INSERT INTO $table_favorites ( tid, username, type ) VALUES ('$subadd', '$xmbuser', 'subscription')");
            echo "<center><span class=\"mediumtxt \">$lang[subaddedmsg]</span></center>";
            redirect('memcp.php?action=subscriptions', 2, X_REDIRECT_JS);
        }
    } elseif (!isset($subadd) && isset($subsubmit)) {
        $query = $db->query("SELECT tid FROM $table_favorites WHERE username='$xmbuser' AND type='subscription'");
        while ($sub = $db->fetch_array($query)) {
            $delete = "delete" .$sub['tid']. "";
            $delete = "${$delete}";
            $db->query("DELETE FROM $table_favorites WHERE username='$xmbuser' AND tid='$delete' AND type='subscription'");
        }

        echo "<center><span class=\"mediumtxt \">" .$lang['subsdeletedmsg']. "</span></center>";
        redirect('memcp.php?action=subscriptions', 2, X_REDIRECT_JS);
    }
}
// Load the Default Page
else {
    eval("echo (\"".template('header')."\");");
    eval($lang['evalusercpwelcome']);

    makenav($action);

    // Load Buddy List
    $q = $db->query("SELECT b.buddyname, w.invisible, w.username FROM $table_buddys b LEFT JOIN $table_whosonline w ON (b.buddyname=w.username) WHERE b.username='$xmbuser'");
    $buddys = array();
    $buddys['offline'] = '';
    $buddys['online'] = '';
    if (X_ADMIN) {
        while ($buddy = $db->fetch_array($q)) {
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
    } else {
        while ($buddy = $db->fetch_array($q)) {
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
    }

    $query = $db->query("SELECT * FROM $table_members WHERE username='$xmbuser'");
    $member = $db->fetch_array($query);

    if ($member['avatar'] == "") {
        $member['avatar'] = "&nbsp;";
    } else {
		if (false !== strpos($member['avatar'], ",") && substr($member['avatar'], $pos-4, 4) == '.swf') {
			$flashavatar = explode(",",$member['avatar']);
			$member['avatar'] = '<object type="application/x-shockwave-flash" data="'.$flashavatar[0].'" width="'.$flashavatar[1].'" height="'.$flashavatar[2].'"><param name="movie" value="'.$flashavatar[0].'" /></object>';
		} else {
	        $member['avatar'] = "<img src=\"" .$member['avatar']. "\" border=\"0\" alt=\"$lang[altavatar]\" />";
	    }
    }

    if ($member['mood'] != '') {
        $member['mood'] = censor($member['mood']);
        $member['mood'] = postify($member['mood'], 'no', 'no', 'yes', 'no', 'yes', 'no', true, 'yes');
    } else {
        $member['mood'] = '&nbsp;';
    }

// Make the Page
    $query = $db->query("SELECT * FROM $table_u2u WHERE owner='$xmbuser' AND type='incoming' ORDER BY dateline DESC LIMIT 0, 15");
    $u2unum = $db->num_rows($query);
    $messages = '';

    $tmOffset = ($timeoffset * 3600) + ($addtime * 3600);

    while ($message = $db->fetch_array($query)) {
        $postdate = gmdate("$dateformat",$message['dateline'] + $tmOffset);
        $posttime = gmdate("$timecode",$message['dateline'] + $tmOffset);
        $senton = "" .$postdate. " " .$lang['textat']. " " .$posttime. "";

        if ($message['subject'] == '') {
            $message['subject'] = "&laquo;" .$lang['textnosub']. "&raquo;";
        }

        if ($message['readstatus'] == "yes") {
            $read = $lang['textread'];
        } else {
            $read = $lang['textunread'];
        }

        $message['subject'] = stripslashes(censor($message['subject']));
        eval("\$messages .= \"".template("memcp_home_u2u_row")."\";");
    }

    if ($u2unum == 0) {
        eval("\$messages = \"".template("memcp_home_u2u_none")."\";");
    }

    $query2 = $db->query("SELECT * FROM $table_favorites f, $table_threads t, $table_posts p WHERE f.tid=t.tid AND p.tid=t.tid AND p.subject=t.subject AND f.username='$xmbuser' AND f.type='favorite' ORDER BY t.lastpost DESC LIMIT 0,5");
    $favnum = $db->num_rows($query2);
    $favs = '';

    $tmOffset = ($timeoffset * 3600) + ($addtime * 3600);

    while ($fav = $db->fetch_array($query2)) {
        $query = $db->query("SELECT name, fup, fid FROM $table_forums WHERE fid='$fav[fid]'");
        $forum = $db->fetch_array($query);
        $lastpost = explode("|", $fav['lastpost']);
        $dalast = $lastpost['0'];
        $lastpost['1'] = "<a href=\"member.php?action=viewpro&amp;member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";
        $lastreplydate = gmdate($dateformat, $lastpost['0'] + $tmOffset);
        $lastreplytime = gmdate($timecode, $lastpost['0'] + $tmOffset);
        $lastpost = "" .$lang['lastreply1']. " " .$lastreplydate. " " .$lang['textat']. " " .$lastreplytime. " " .$lang['textby']. " " .$lastpost['1']. "";
        $fav['subject'] = stripslashes(censor($fav['subject']));

        if ($fav['icon'] != "") {
            $fav['icon'] = "<img src=\"" .$smdir. "/" .$fav['icon']. "\" />";
        } else {
            $fav['icon'] = "&nbsp;";
        }

        eval("\$favs .= \"".template("memcp_home_favs_row")."\";");
    }

    if ($favnum == 0) {
        eval("\$favs = \"".template("memcp_home_favs_none")."\";");
    }

    eval('echo stripslashes("'.template('memcp_home').'");');
}

end_time();
eval("echo (\"".template('footer')."\");");
?>