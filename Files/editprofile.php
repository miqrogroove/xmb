<?php
/* $Id: editprofile.php,v 1.12.2.6 2004/09/24 19:10:32 Tularis Exp $ */
/*
    XMB 1.9
    © 2001 - 2004 Aventure Media & The XMB Development Team
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

// Load global stuff
require "./header.php";

// Pre-load templates
loadtemplates('footer_load', 'footer_querynum', 'footer_phpsql', 'footer_totaltime','error_nologinsession','memcp_profile_avatarurl','memcp_profile_avatarlist','admintool_editprofile','header','footer','css','functions_bbcode');

// Create navigation and header
nav('<a href="./cp.php">'.$lang['textcp'].'</a>');
nav($lang['texteditpro']);

eval("\$css = \"".template("css")."\";");

// Check if user is an admin
if($self['status'] != "Super Administrator"){
    error($lang['superadminonly']);
}

eval("echo (\"".template('header')."\");");

// Check if the user is logged in
if(!$xmbuser || !$xmbpw) {
    $user = NULL;
    $xmbpw = false;
    $self['status'] = NULL;
}

// Check to see if member exists
$userid = $db->fetch_array($db->query("SELECT uid FROM $table_members WHERE username='$user'"));
if(empty($userid['uid'])){
    error($lang['nomember'], false, $u2uheader);
}

// if no action specified
if(!$editsubmit) {
    $query = $db->query("SELECT * FROM $table_members WHERE username='".rawurldecode($user)."'");
    $member = $db->fetch_array($query);

    if($member['showemail'] == "yes") {
        $checked = "checked=\"checked\"";
    }

    if($member['newsletter'] == "yes") {
        $newschecked = "checked=\"checked\"";
    }

    if($member['useoldu2u'] == "yes") {
        $uou2uchecked = "checked=\"checked\"";
    }

    if($member['saveogu2u'] == "yes") {
        $ogu2uchecked = "checked=\"checked\"";
    }

    if($member['emailonu2u'] == "yes") {
        $eouchecked = "checked=\"checked\"";
    }

    if($action == "deleteposts"){
        $queryd = $db->query("DELETE FROM $table_posts WHERE author='$member'");
        $queryt = $db->query("SELECT * FROM $table_threads");

        while($threads = $db->fetch_array($queryt)) {
            $query = $db->query("SELECT COUNT(*) FROM $table_posts WHERE tid='$threads[tid]'");
            $replynum = $db->result($query, 0);
            $replynum--;
            $db->query("UPDATE $table_threads SET replies=replies-1 WHERE tid='$threads[tid]'");
            $db->query("DELETE FROM $table_threads WHERE author='$member'");
        }

        echo "<center><span class=\"mediumtxt \">$lang[editprofile_postsdeleted]<br /><a href=cp.php><b>$lang[editprofile_backtocp]</b></a></span></center>";
        eval("echo (\"".template('footer')."\");");
        exit();
    }

    $registerdate = gmdate("D M j G:i:s T Y",$member['regdate'] + ($addtime * 3600) + ($timeoffset * 3600));
    $lastlogdate = gmdate("D M j G:i:s T Y",$member['lastvisit'] + ($addtime * 3600) + ($timeoffset * 3600));

    $currdate = gmdate($timecode, time() + ($addtime * 3600));
    eval($lang['evaloffset']);

    $themelist = "<select name=\"thememem\">\n<option value=\"\">$lang[textusedefault]</option>";
    $query = $db->query("SELECT themeid, name FROM $table_themes ORDER BY name ASC");
    while($theme = $db->fetch_array($query)) {
        if($theme['themeid'] == $member['theme']) {
            $themelist .= "<option value=\"$theme[themeid]\" selected=\"selected\">$theme[name]</option>\n";
        }else{
            $themelist .= "<option value=\"$theme[themeid]\">$theme[name]</option>\n";
        }
    }
    $themelist .= "</select>";

    $langfileselect = "<select name=\"langfilenew\">\n";
    $dir = opendir("lang");
    while ($thafile = readdir($dir)) {
        if(is_file("lang/$thafile") && false !== strpos($thafile, '.lang.php')) {
            $thafile = str_replace(".lang.php", "", $thafile);
            if($thafile == $member['langfile']) {
                $langfileselect .= "<option value=\"" .$thafile. "\" selected=\"selected\">" .$thafile. "</option>\n";
            }else{
                $langfileselect .= "<option value=\"" .$thafile. "\">" .$thafile. "</option>\n";
            }
        }
    }
    $langfileselect .= "</select>";


    $member['bday'] = str_replace(",", "", $member['bday']);
    $bday = explode(" ", $member['bday']);

    if($bday[0] == "") {
        $sel0 = "selected=\"selected\"";
    } elseif($bday[0] == $lang[textjan]) {
        $sel1 = "selected=\"selected\"";
    } elseif($bday[0] == $lang[textfeb]) {
        $sel2 = "selected=\"selected\"";
    } elseif($bday[0] == $lang[textmar]) {
        $sel3 = "selected=\"selected\"";
    } elseif($bday[0] == $lang[textapr]) {
        $sel4 = "selected=\"selected\"";
    } elseif($bday[0] == $lang[textmay]) {
        $sel5 = "selected=\"selected\"";
    } elseif($bday[0] == $lang[textjun]) {
        $sel6 = "selected=\"selected\"";
    } elseif($bday[0] == $lang[textjul]) {
        $sel7 = "selected=\"selected\"";
    } elseif($bday[0] == $lang[textaug]) {
        $sel8 = "selected=\"selected\"";
    } elseif($bday[0] == $lang[textsep]) {
        $sel9 = "selected=\"selected\"";
    } elseif($bday[0] == $lang[textoct]) {
        $sel10 = "selected=\"selected\"";
    } elseif($bday[0] == $lang[textnov]) {
        $sel11 = "selected=\"selected\"";
    } elseif($bday[0] == $lang[textdec]) {
        $sel12 = "selected=\"selected\"";
    }

    $dayselect = "<select name=\"day\">\n";
    $dayselect .= "<option value=\"\">&nbsp;</option>\n";
    for($num = 1; $num <= 31; $num++) {
        if($bday[1] == $num) {
            $dayselect .= "<option value=\"$num\" selected=\"selected\">$num</option>\n";
        }else{
            $dayselect .= "<option value=\"$num\">$num</option>\n";
        }
    }
    $dayselect .= "</select>";

    if($member['timeformat'] == "24") {
        $check24 = "checked=\"checked\"";
    } else {
        $check12 = "checked=\"checked\"";
    }

    if($sigbbcode == "on") {
        $bbcodeis = $lang['texton'];
    } else {
        $bbcodeis = $lang['textoff'];
    }

    if($sightml == "on") {
        $htmlis = $lang['texton'];
    } else {
        $htmlis = $lang['textoff'];
    }

    if($member['invisible'] == 1) {
        $invchecked = "checked=\"checked\"";
    }

    $member['bio'] = stripslashes($member['bio']);
    $member['sig'] = stripslashes($member['sig']);

    if($avastatus == "on") {
        eval("\$avatar = \"".template("memcp_profile_avatarurl")."\";");
    }

    if($avastatus == "list") {
        $avatars = " <option value=\"\" />$lang[textnone]</option>  ";
        $dir1 = opendir("./images/avatars");
        while ($avatar1 = readdir($dir1)) {
            if (is_file("./images/avatars/$avatar1")) {
                $avatars .= " <option value=\"./images/avatars/$avatar1\" />$avatar1</option>  ";
            }
        }

        $avatars = str_replace("value=\"$member[avatar]\"", "value=\"$member[avatar]\" SELECTED", $avatars);
        $avatarbox = "<select name=\"avatar\">$avatars</select>";
        eval("\$avatar = \"".template("memcp_profile_avatarlist")."\";");
        closedir($dir1);
    }
    $lang['searchusermsg'] = str_replace('*USER*', $user, $lang['searchusermsg']);

    eval("\$profile = \"".template("admintool_editprofile")."\";");
    $profile = stripslashes($profile);
    echo $profile;

}else{
    $query = $db->query("SELECT * FROM $table_members WHERE username='$user'");
    $member = $db->fetch_array($query);

    if(!$member['username']) {
        echo "<center><span class=\"mediumtxt \">$lang[badname]</span></center>";
        end_time();
        eval("echo (\"".template('footer')."\");");
        exit();
    }

    if($showemail != "yes") {
        $showemail = "no";
    }

    if($newsletter != "yes") {
        $newsletter = "no";
    }

    if($saveogu2u != "yes") {
        $saveogu2u = "no";
    }

    if($emailonu2u != "yes") {
        $emailonu2u = "no";
    }

    if($useoldu2u != "yes") {
        $useoldu2u = "no";
    }

    $bday = "$month $day, $year";

    if($month == "" || $day == "" || $year == "") {
        $bday = "";
    }

    $avatar         = checkInput($newavatar, 'no', 'no', 'javascript', false);
    $memlocation    = checkInput($newmemlocation, 'no', 'no', 'javascript', false);
    $icq            = checkInput($newicq, 'no', 'no', 'javascript', false);
    $yahoo          = checkInput($newyahoo, 'no', 'no', 'javascript', false);
    $aim            = checkInput($newaim, 'no', 'no', 'javascript', false);
    $email          = checkInput($newemail, 'no', 'no', 'javascript', false);
    $site           = checkInput($newsite, 'no', 'no', 'javascript', false);
    $webcam         = checkInput($newwebcam, 'no', 'no', 'javascript', false);
    $bio            = checkInput($newbio, 'no', 'no', 'javascript', false);
    $bday           = checkInput($bday, 'no', 'no', 'javascript', false);
    $mood           = checkInput($newmood, 'no', 'no', 'javascript', false);
    $pstatus        = checkInput($newpstatus, 'no', 'no', 'javascript', false);

    $sig            = addslashes($newsig);
    $bio            = addslashes($bio);
    $memlocation    = addslashes($newmemlocation);

    $msn            = checkInput($newmsn, 'no', 'no', 'javascript', false);
    $showemail      = $newshowemail;
    $newsletter     = $newnewsletter;

    $invisible      = ($newinv == 1) ? 1 : 0;

    $db->query("UPDATE $table_members SET email='$email', site='$site', aim='$aim', location='$memlocation', bio='$bio', sig='$sig', showemail='$showemail', timeoffset='$timeoffset1', icq='$icq', avatar='$avatar', yahoo='$yahoo', theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew', newsletter='$newsletter', timeformat='$timeformatnew', msn='$msn', dateformat='$dateformatnew', mood='$mood', invisible='$invisible', saveogu2u='$saveogu2u', emailonu2u='$emailonu2u', useoldu2u='$useoldu2u', webcam='$webcam' WHERE username='$user'");

    if($newpassword != "") {
        if(false !== strpos($newpassword, '"') || false !== strpos($newpassword, "'")) {
            echo "<center><span class=\"mediumtxt \">$lang[textpwincorrect]</span><center>";
            exit();
        }

        $newpassword = md5($newpassword);
        $db->query("UPDATE $table_members SET password='$newpassword' WHERE username='$user'");
    }

    echo "<center><span class=\"mediumtxt \">$lang[adminprofilechange]</span></center>";
    redirect('cp.php', 3, X_REDIRECT_JS);
}

end_time();
eval("echo (\"".template('footer')."\");");
?>