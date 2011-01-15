<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

// Load global stuff
	require "./header.php";

// Pre-load templates
	loadtemplates('error_nologinsession,memcp_profile_avatarurl,memcp_profile_avatarlist,admintool_editprofile,');

// Create navigation and header
	$navigation = "&raquo; <a href=\"./cp.php\">".$lang_textcp."</a> &raquo; ".$lang_texteditpro;
	eval("\$header = \"".template("header")."\";");
	echo $header;

// Check if the user is logged in
	if(!$xmbuser || !$xmbpw) {
		$user = NULL;
		$xmbpw = false;
		$status = NULL;
	}

// Check if user is an admin
	if($status !="Super Administrator") {
		eval("\$notadmin = \"".template("error_nologinsession")."\";");
		echo $notadmin;
		eval("\$footer = \"".template("footer")."\";");
		echo $footer;
		exit();
	}

// if no action specified
	if(!$editsubmit) {
		$query = $db->query("SELECT * FROM $table_members WHERE username='$user'");
		$member = $db->fetch_array($query);

		if($member[showemail] == "yes") {
			$checked = "checked=\"checked\"";
		}

		if($member[newsletter] == "yes") {
			$newschecked = "checked=\"checked\"";
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
	
			echo "<center><span class=\"mediumtxt \">$lang_editprofile_postsdeleted<br /><a href=cp.php><b>$lang_editprofile_backtocp</b></a></span></center>";
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			exit();
		}

		$registerdate = gmdate("D M j G:i:s T Y",$member[regdate] + ($addtime * 3600) + ($timeoffset * 3600));
		$lastlogdate = gmdate("D M j G:i:s T Y",$member[lastvisit] + ($addtime * 3600) + ($timeoffset * 3600));

		$currdate = gmdate("$timecode", time() + ($addtime * 3600));
		eval($lang_evaloffset);

		$themelist = "<select name=\"thememem\">\n<option value=\"\">$lang_textusedefault</option>";
		$query = $db->query("SELECT name FROM $table_themes");
		while($theme = $db->fetch_array($query)) {
			if($theme[name] == $member[theme]) {
				$themelist .= "<option value=\"$theme[name]\" selected=\"selected\">$theme[name]</option>\n";
			}else{
				$themelist .= "<option value=\"$theme[name]\">$theme[name]</option>\n";
			}
		}
		$themelist .= "</select>";

		$langfileselect = "<select name=\"langfilenew\">\n";
		$dir = opendir("lang");
		while ($thafile = readdir($dir)) {
			if(is_file("lang/$thafile")) {
				$thafile = str_replace(".lang.php", "", $thafile);
				if($thafile == "$member[langfile]") {
					$langfileselect .= "<option value=\"$thafile\" selected=\"selected\">$thafile</option>\n";
				}else{
					$langfileselect .= "<option value=\"$thafile\">$thafile</option>\n";
				}
			}
		}
		$langfileselect .= "</select>";


		$member[bday] = str_replace(",", "", $member[bday]);
		$bday = explode(" ", $member[bday]);

		if($bday[0] == "") {
			$sel0 = "selected=\"selected\"";
		} elseif($bday[0] == $lang_textjan) {
			$sel1 = "selected=\"selected\"";
		} elseif($bday[0] == $lang_textfeb) {
			$sel2 = "selected=\"selected\"";
		} elseif($bday[0] == $lang_textmar) {
			$sel3 = "selected=\"selected\"";
		} elseif($bday[0] == $lang_textapr) {
			$sel4 = "selected=\"selected\"";
		} elseif($bday[0] == $lang_textmay) {
			$sel5 = "selected=\"selected\"";
		} elseif($bday[0] == $lang_textjun) {
			$sel6 = "selected=\"selected\"";
		} elseif($bday[0] == $lang_textjul) {
			$sel7 = "selected=\"selected\"";
		} elseif($bday[0] == $lang_textaug) {
			$sel8 = "selected=\"selected\"";
		} elseif($bday[0] == $lang_textsep) {
			$sel9 = "selected=\"selected\"";
		} elseif($bday[0] == $lang_textoct) {
			$sel10 = "selected=\"selected\"";
		} elseif($bday[0] == $lang_textnov) {
			$sel11 = "selected=\"selected\"";
		} elseif($bday[0] == $lang_textdec) {
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
	
		if($member[timeformat] == "24") {
			$check24 = "checked=\"checked\"";
		} else {
			$check12 = "checked=\"checked\"";
		}
	
		if($sigbbcode == "on") {
			$bbcodeis = $lang_texton;
		} else {
			$bbcodeis = $lang_textoff;
		}

		if($sightml == "on") {
			$htmlis = $lang_texton;
		} else {
			$htmlis = $lang_textoff;
		}

		$member[bio] = stripslashes($member[bio]);
		$member[sig] = stripslashes($member[sig]);


		if($avastatus == "on") {
			eval("\$avatar = \"".template("memcp_profile_avatarurl")."\";");
		}

		if($avastatus == "list") {
			$avatars = " <option value=\"\" />$lang_textnone</option>  ";
			$dir1 = opendir("images/avatars");
			while ($avatar1 = readdir($dir1)) {
				if (is_file("images/avatars/$avatar1")) {
					$avatars .= " <option value=\"images/avatars/$avatar1\" />$avatar1</option>  ";
				}
			}

			$avatars = str_replace("value=\"$member[avatar]\"", "value=\"$member[avatar]\" SELECTED", $avatars);
			$avatarbox = "<select name=\"avatar\">$avatars</select>";
			eval("\$avatar = \"".template("memcp_profile_avatarlist")."\";");
			closedir($dir1);
		}
		$lang_searchusermsg = str_replace('*USER*', $user, $lang_searchusermsg);

		eval("\$profile = \"".template("admintool_editprofile")."\";");
		$profile = stripslashes($profile);
		echo $profile;

	}else{
		$query = $db->query("SELECT * FROM $table_members WHERE username='$user'");
		$member = $db->fetch_array($query);
	
		if(!$member[username]) {
			echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
			exit();
		}

		if($showemail != "yes") {
			$showemail = "no";
		}

		if($newsletter != "yes") {
			$newsletter = "no";
		}

		$bday = "$month $day, $year";

		if($month == "" || $day == "" || $year == "") {
			$bday = "";
		}

		$email 		= checkInput($newemail, 'no', 'no', 'javascript');
		$site 		= checkInput($newsite, 'no', 'no', 'javascript');
		$aim 		= checkInput($newaim, 'no', 'no', 'javascript');
		$memlocation 	= checkInput($newmemlocation, 'no', 'no', 'javascript');
		$bio 		= checkInput($newbio, 'no', 'no', 'javascript');
		$newsig 	= checkInput($newsig);
		$showemail 	= $newshowemail;
		$icq 		= checkInput($newicq, 'no', 'no', 'javascript');
		$avatar 	= checkInput($newavatar, 'no', 'no', 'javascript');
		$yahoo 		= checkInput($newyahoo, 'no', 'no', 'javascript');
		$bday 		= checkInput($bday, 'no', 'no', 'javascript');
		$pstatus 	= checkInput($newpstatus, 'no', 'no', 'javascript');
		$newsletter	= $newnewsletter;
		$msn		= checkInput($newmsn,'no', 'no', 'javascript');
		$dateformatnew	= checkInput($dateformatnew,'no', 'no', 'javascript');
		$mood 		= checkInput($newmood, 'no', 'no', 'javascript');

		$sig 		= addslashes($newsig);
		$bio 		= addslashes($bio);
		$memlocation 	= addslashes($newmemlocation);
		$db->query("UPDATE $table_members SET email='$email', site='$site', aim='$aim', location='$memlocation', bio='$bio', sig='$sig', showemail='$showemail', timeoffset='$timeoffset1', icq='$icq', avatar='$avatar', yahoo='$yahoo', theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew', newsletter='$newsletter', timeformat='$timeformatnew', msn='$msn', dateformat='$dateformatnew', mood='$mood' WHERE username='$user'");

		if($newpassword != "") {

// Patched to prevent edit profile error

			if(strstr($newpassword, '"') || strstr($newpassword, "'")) {
				echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span><center>";
				exit();
			}
			
			$newpassword = md5($newpassword);
			$db->query("UPDATE $table_members SET password='$newpassword' WHERE username='$user'");
		}

		echo "<center><span class=\"mediumtxt \">$lang_adminprofilechange</span></center>";
		?>
		
		<script>
		function redirect(){
			window.location.replace("cp.php");
		}
	
		setTimeout("redirect();", 1250);
		</script>

		<?
	}

end_time();

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>