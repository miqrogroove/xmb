<?php
/* $Id: editprofile.php,v 1.3.2.6 2006/03/03 22:29:17 FunForum Exp $ */
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

// Load global stuff
require('./header.php');

// Pre-load templates
loadtemplates('memcp_profile_avatarurl','memcp_profile_avatarlist','admintool_editprofile');

// Create navigation and header
nav('<a href="./cp.php">'.$lang['textcp'].'</a>');
nav($lang['texteditpro']);

eval("\$css = \"".template("css")."\";");

eval("echo (\"".template('header')."\");");

// Check if user is an admin
if(!X_SADMIN) {
    error($lang['superadminonly'], false);
}

$selHTML = 'selected="selected"';
$cheHTML = 'checked="checked"';
$action = isset($action) ? $action : '';

// Check to see if member exists
$userid = $db->fetch_array($db->query("SELECT uid FROM $table_members WHERE username='$user'"));
if(empty($userid['uid'])) {
    error($lang['nomember'], false);
}

// if no action specified, display the form
if(!isset($_POST['editsubmit'])) {
    $query  = $db->query("SELECT * FROM $table_members WHERE username='".rawurldecode($user)."'");
    $member = $db->fetch_array($query);

    $checked = '';
    if ( $member['showemail'] == 'yes' )
    {
        $checked = $cheHTML;
    }

    $newschecked = '';
    if ( $member['newsletter'] == 'yes' )
    {
        $newschecked = $cheHTML;
    }

    $uou2uchecked = '';
    if ( $member['useoldu2u'] == 'yes' )
    {
        $uou2uchecked = $cheHTML;
    }

    $ogu2uchecked = '';
    if ( $member['saveogu2u'] == 'yes' )
    {
        $ogu2uchecked = $cheHTML;
    }

    $eouchecked = '';
    if ( $member['emailonu2u'] == 'yes' )
    {
        $eouchecked = $cheHTML;
    }

    if ( $action == "deleteposts" )
    {
        $query = $db->query("SELECT COUNT(pid) FROM $table_posts WHERE author='$member'");
        $replynum = $db->result($query, 0);
        $db->free_result($query);

        // Delete all the posts written by the member
        $db->query("DELETE FROM $table_posts WHERE author='$member'");

        // Carefully delete threads started by the member
        // Don't leave any orphans
        $query = $db->query("SELECT tid FROM $table_threads WHERE author='$member'");
        while ( $threads = $db->fetch_array($query) )
        {
            $query = $db->query("SELECT COUNT(pid) FROM $table_posts WHERE tid='$threads[tid]'");
            $replynum += $db->result($query, 0);
            $db->query("DELETE FROM $table_posts WHERE tid='$threads[tid]')");
            $db->query("DELETE FROM $table_attachments WHERE tid='$threads[tid]')");
            $db->query("DELETE FROM $table_favorites WHERE tid='$threads[tid]')");
            $db->query("DELETE FROM $table_threads WHERE tid='$threads[tid]')");
        }

        echo "<center><span class=\"mediumtxt \">$lang[editprofile_postsdeleted]<br /><a href=cp.php><b>$lang[editprofile_backtocp]</b></a></span></center>";
        eval("echo (\"".template('footer')."\");");
        exit();
    }

    $registerdate = gmdate("D M j G:i:s T Y", $member['regdate'] + ($addtime * 3600) + ($timeoffset * 3600));
    $lastlogdate  = gmdate("D M j G:i:s T Y", $member['lastvisit'] + ($addtime * 3600) + ($timeoffset * 3600));

    $currdate = gmdate($timecode, time() + ($addtime * 3600));
    eval($lang['evaloffset']);

    $themelist   = array();
    $themelist[] = '<select name="thememem">';
    $themelist[] = '<option value="0">'.$lang['textusedefault'].'</option>';
    $query     = $db->query("SELECT themeid, name FROM $table_themes ORDER BY name ASC");
    while ($themeinfo = $db->fetch_array($query)) {
        if ($themeinfo['themeid'] == $member['theme']) {
            $themelist[] = '<option value="'.$themeinfo['themeid'].'" selected="selected">'.stripslashes($themeinfo['name']).'</option>';
        } else {
            $themelist[] = '<option value="'.$themeinfo['themeid'].'">'.stripslashes($themeinfo['name']).'</option>';
        }
    }
    $themelist[] = '</select>';
    $themelist   = implode("\n", $themelist);

    $langfileselect = "<select name=\"langfilenew\">\n";
    $dir = opendir("lang");
    while ( $thafile = readdir($dir) )
    {
        if ( is_file("lang/$thafile") && false !== strpos($thafile, '.lang.php') )
        {
            $thafile = str_replace(".lang.php", "", $thafile);
            if ( $thafile == $member['langfile'] )
            {
                $langfileselect .= "<option value=\"" .$thafile. "\" selected=\"selected\">" .$thafile. "</option>\n";
            }
            else
            {
                $langfileselect .= "<option value=\"" .$thafile. "\">" .$thafile. "</option>\n";
            }
        }
    }
    $langfileselect .= "</select>";

    $day   = substr($member['bday'], 8, 2);
    $month = substr($member['bday'], 5, 2);
    $year  = substr($member['bday'], 0, 4);

    $sel0 = $sel1 = $sel2 = $sel3 = $sel4 = $sel5 = $sel6 = "";
    $sel7 = $sel8 = $sel9 = $sel10 = $sel11 = $sel12 = "";

    ${'sel'.(int)$month} = 'selected="selected"';

    $dayselect = "<select name=\"day\">\n";
    $dayselect .= "<option value=\"\">&nbsp;</option>\n";
    for ($num = 0; $num <= 31; $num++) {
        if ($day == $num ) {
            $dayselect .= "<option value=\"" .$num. "\" selected=\"selected\">" .$num. "</option>\n";
        } else {
            $dayselect .= "<option value=\"" .$num. "\">" .$num. "</option>\n";
        }
    }
    $dayselect .= "</select>";

    $check12 = $check24 = '';
    if ( $member['timeformat'] == 24 )
    {
        $check24 = $cheHTML;
    }
    else
    {
        $check12 = $cheHTML;
    }

    if ( $sigbbcode == 'on' )
    {
        $bbcodeis = $lang['texton'];
    }
    else
    {
        $bbcodeis = $lang['textoff'];
    }

    if ( $sightml == 'on' )
    {
        $htmlis = $lang['texton'];
    }
    else
    {
        $htmlis = $lang['textoff'];
    }

    $invchecked = '';
    if ( $member['invisible'] == 1 )
    {
        $invchecked = $cheHTML;
    }

    $avatar = '';
    if ( $avastatus == 'on' )
    {
        eval("\$avatar = \"".template("memcp_profile_avatarurl")."\";");
    }

    if ( $avastatus == 'list' )
    {
        $avatars = " <option value=\"\" />" .$lang['textnone']. "</option>  ";
        $dir1 = opendir("./images/avatars");
        while ($avatar1 = readdir($dir1))
        {
            if (is_file("./images/avatars/" .$avatar1. ""))
            {
                $avatars .= " <option value=\"./images/avatars/" .$avatar1. "\" />" .$avatar1. "</option>  ";
            }
        }

        $avatars = str_replace("value=\"" .$member['avatar']. "\"", "value=\"" .$member['avatar']. "\" selected=\"selected\"", $avatars);
        $avatarbox = "<select name=\"newavatar\" onchange=\"document.images.avatarpic.src =this[this.selectedIndex].value;\">" .$avatars. "</select>";
        eval("\$avatar = \"".template("memcp_profile_avatarlist")."\";");
        closedir($dir1);
    }

    $lang['searchusermsg'] = str_replace('*USER*', $user, $lang['searchusermsg']);
    $member['dateformat'] = str_replace($date_to, $date_from, $dateformat);

    eval('echo stripslashes("'.template('admintool_editprofile').'");');
}
else
{
    $query  = $db->query("SELECT * FROM $table_members WHERE username='$user'");
    $member = $db->fetch_array($query);

    if ( !$member['username'] )
    {
        error($lang['badname'], false);
    }

    $showemail  = ( isset($showemail) && $showemail == 'yes' ) ? 'yes' : 'no';
    $newsletter = ( isset($newsletter) && $newsletter == 'yes' ) ? 'yes' : 'no';
    $saveogu2u  = ( isset($saveogu2u) && $saveogu2u == 'yes' ) ? 'yes' : 'no';
    $emailonu2u = ( isset($emailonu2u) && $emailonu2u == 'yes' ) ? 'yes' : 'no';
    $useoldu2u  = ( isset($useoldu2u) && $useoldu2u == 'yes' ) ? 'yes' : 'no';

    $bday       = iso8601_date($year, $month, $day);

    $newavatar      = isset($newavatar) ? ereg_replace(' ', '%20', $newavatar) : '';
    $avatar         = checkInput($newavatar, 'no', 'no', 'javascript', false);
    $memlocation    = isset($newmemlocation) ? checkInput($newmemlocation, 'no', 'no', 'javascript', false) : '';
    $icq            = isset($newicq) ? checkInput($newicq, 'no', 'no', 'javascript', false) : '';
    $yahoo          = isset($newyahoo) ? checkInput($newyahoo, 'no', 'no', 'javascript', false) : '';
    $aim            = isset($newaim) ? checkInput($newaim, 'no', 'no', 'javascript', false) : '';
    $msn            = isset($newmsn) ? checkInput($newmsn, 'no', 'no', 'javascript', false) : '';
    $email          = isset($newemail) ? checkInput($newemail, 'no', 'no', 'javascript', false) : '';
    $site           = isset($newsite) ? checkInput($newsite, 'no', 'no', 'javascript', false) : '';
    $webcam         = isset($newwebcam) ? checkInput($newwebcam, 'no', 'no', 'javascript', false) : '';
    $bio            = isset($newbio) ? checkInput($newbio, 'no', 'no', 'javascript', false) : '';
    $mood           = isset($newmood) ? checkInput($newmood, 'no', 'no', 'javascript', false) : '';
    $pstatus        = isset($newpstatus) ? checkInput($newpstatus, 'no', 'no', 'javascript', false) : '';
    $sig            = isset($newsig) ? checkInput($newsig, '', $SETTINGS['sightml'], '', false) : '';

    $sig            = addslashes($newsig);
    $bio            = addslashes($bio);
    $memlocation    = addslashes($newmemlocation);

    $invisible      = (isset($newinv) && $newinv == 1) ? 1 : 0;
    $showemail      = (isset($newshowemail) && $newshowemail == 'yes') ? 'yes' : 'no';
    $newsletter     = (isset($newnewsletter) && $newnewsletter == 'yes') ? 'yes' : 'no';

    $max_size = explode('x', $SETTINGS['max_avatar_size']);
    if($max_size[0] > 0 && $max_size[1] > 0) {
        $size = @getimagesize($avatar);
        if($size === false ) {
            $avatar = '';
        } elseif(($size[0] > $max_size[0] && $max_size[0] > 0) || ($size[1] > $max_size[1] && $max_size[1] > 0)) {
            error($lang['avatar_too_big'] . $SETTINGS['max_avatar_size'] . 'px', false);
        }
    }

    $db->query("UPDATE $table_members SET email='$email', site='$site', aim='$aim', location='$memlocation', bio='$bio', sig='$sig', showemail='$showemail', timeoffset='$timeoffset1', icq='$icq', avatar='$avatar', yahoo='$yahoo', theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew', newsletter='$newsletter', timeformat='$timeformatnew', msn='$msn', dateformat='$dateformatnew', mood='$mood', invisible='$invisible', saveogu2u='$saveogu2u', emailonu2u='$emailonu2u', useoldu2u='$useoldu2u', webcam='$webcam' WHERE username='$user'");

    $newpassword = trim($newpassword);
    if ( $newpassword != '' )
    {
        $newpassword = md5($newpassword);
        $db->query("UPDATE $table_members SET password='$newpassword' WHERE username='$user'");
    }

    echo "<div align=\"center\"><span class=\"mediumtxt \">" .$lang['adminprofilechange']. "</span></div>";
    redirect('cp.php', 2, X_REDIRECT_JS);
}

end_time();
eval("echo (\"".template('footer')."\");");
?>