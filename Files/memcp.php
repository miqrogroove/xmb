<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

require "header.php";

loadtemplates('header,footer,memcp_profile_avatarurl,memcp_profile_avatarlist,memcp_profile,memcp_favs_row,memcp_favs_none,memcp_favs,memcp_subscriptions_row,memcp_subscriptions_none,memcp_subscriptions,buddylist_buddy_online,buddylist_buddy_offline,memcp_home_u2u_row,memcp_home_u2u_none,memcp_home,memcp_home_favs_none');

// Determine the navigation
if($action == "profile"){
	$memberaction = "<a href=\"memcp.php\">$lang_textusercp</a> &raquo; $lang_texteditpro";
}elseif($action == "subscriptions"){
	$memberaction = "<a href=\"memcp.php\">$lang_textusercp</a> &raquo; $lang_textsubscriptions";
}elseif($action == "favorites"){
	$memberaction = "<a href=\"memcp.php\">$lang_textusercp</a> &raquo; $lang_textfavorites";
}else{
	$memberaction = "$lang_textusercp";
}

$navigation = " &raquo; $memberaction";


// Make the user CP Nav Bar
function makenav($current) {
	global $bordercolor, $tablewidth, $borderwidth, $tablespacing, $altbg1,	$altbg2, $lang_textmyhome, $lang_texteditpro, $lang_textsubscriptions,$lang_textfavorites, $lang_textu2umessenger, $lang_textbuddylist, $lang_helpbar;

	?>
	<table cellpadding="0" cellspacing="0" border="0" bgcolor="<?=$bordercolor?>" width="<?=$tablewidth?>" align="center"><tr><td>
	<table cellpadding="4" cellspacing="1" border="0" width="100%">
	<tr align="center" class="tablerow">
	
	<?php
	if($current == "") {
		echo "<td bgcolor=\"$altbg1\">$lang_textmyhome</td>";
	} else {
		echo "<td bgcolor=\"$altbg2\"><a href=\"memcp.php\">$lang_textmyhome</a></td>";
	}

	if($current == "profile") {
		echo "<td bgcolor=\"$altbg1\">$lang_texteditpro</td>";
	} else {
		echo "<td bgcolor=\"$altbg2\"><a href=\"memcp.php?action=profile\">$lang_texteditpro</a></td>";
	}

	if($current == "subscriptions") {
		echo "<td bgcolor=\"$altbg1\">$lang_textsubscriptions</td>";
	} else {
		echo "<td bgcolor=\"$altbg2\"><a href=\"memcp.php?action=subscriptions\">$lang_textsubscriptions</a></td>";
	}

	if($current == "favorites") {
		echo "<td bgcolor=\"$altbg1\">$lang_textfavorites</td>";
	} else {
		echo "<td bgcolor=\"$altbg2\"><a href=\"memcp.php?action=favorites\">$lang_textfavorites</a></td>";
	}

	echo "<td bgcolor=\"$altbg2\"><a href=\"#\" onclick=\"Popup('u2u.php', 'Window', 550, 450);\">$lang_textu2umessenger</a></td>";
	echo "<td bgcolor=\"$altbg2\"><a href=\"#\" onclick=\"Popup('buddy.php?', 'Window', 250, 300);\">$lang_textbuddylist</a></td>";
	echo "<td bgcolor=\"$altbg2\"><a href=\"faq.php\">$lang_helpbar</a></td>";

	?>
	</tr>
	</table>
	</td></tr></table><br />
	
	<?php
}


// Determine if user is logged in, if not send to login page

if(!$xmbuser || !$xmbpw) {
	
	?>
	<script>
	function redirect(){
		window.location.replace("misc.php?action=login");
	}
	
	setTimeout("redirect();", 1250);
	</script>

	<?
	exit();
}

// Start Profile Editor Code
if($action == "profile") {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	makenav($action);
	
	if(!$editsubmit) {
		$member = $self;

		if($member[showemail] == "yes") {
			$checked = "checked=\"checked\"";
		}

		if($member[newsletter] == "yes") {
			$newschecked = "checked=\"checked\"";
		}

		$currdate = gmdate("$timecode", time()+ ($addtime * 3600));
		eval($lang_evaloffset);

		if($member[timeoffset] == "-12") { $timezone1 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "-11") { $timezone2 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "-10") { $timezone3 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "-9") { $timezone4 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "-8") { $timezone5 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "-7") { $timezone6 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "-6") { $timezone7 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "-5") { $timezone8 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "-4") { $timezone9 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "-3.5") { $timezone10 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "-3") { $timezone11 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "-2") { $timezone12 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "-1") { $timezone13 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "0") { $timezone14 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "1") { $timezone15 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "2") { $timezone16 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "3") { $timezone17 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "3.5") { $timezone18 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "4") { $timezone19 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "4.5") { $timezone20 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "5") { $timezone21 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "5.5") { $timezone22 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "5.75") { $timezone23 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "6") { $timezone24 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "6.5") { $timezone25 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "7") { $timezone26 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "8") { $timezone27 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "9") { $timezone28 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "9.5") { $timezone29 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "10") { $timezone30 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "11") { $timezone31 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "12") { $timezone32 = "selected=\"selected\""; }
		elseif($member[timeoffset] == "13") { $timezone33 = "selected=\"selected\""; }

		$themelist = "<select name=\"thememem\">\n<option value=\"\">$lang_textusedefault</option>";
		$query = $db->query("SELECT name FROM $table_themes");
		while($theme = $db->fetch_array($query)) {
			if($theme[name] == $member[theme]) {
				$themelist .= "<option value=\"$theme[name]\" selected=\"selected\">$theme[name]</option>\n";
			}else{
				$themelist .= "<option value=\"$theme[name]\">$theme[name]</option>\n";
			}
		}
		
		$themelist  .= "</select>";


		$langfileselect = "<select name=\"langfilenew\">\n";
		$dir = opendir("lang");
		while ($thafile = readdir($dir)) {
			if(is_file("lang/$thafile") && strstr($thafile, '.lang.php')) {
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
			$sigallowhtml = "yes";
			$htmlis = $lang_texton;
		} else {
			$sigallowhtml = "yes";
			$htmlis = $lang_textoff;
		}

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
			$avatarbox = "<select name=\"newavatar\" onchange=\"document.images.avatarpic.src =this[this.selectedIndex].value;\">$avatars</select>";
			eval("\$avatar = \"".template("memcp_profile_avatarlist")."\";");
			closedir($dir1);
		}

		eval("\$profile = \"".template("memcp_profile")."\";");
		$profile = stripslashes($profile);
		echo $profile;
	}

	if($editsubmit) {
		$query = $db->query("SELECT * FROM $table_members WHERE username='$xmbuser'");
		$member = $db->fetch_array($query);

		if(!$member[username]) {
			eval("\$header = \"".template('header')."\";");
			echo $header;

			echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";

			end_time();
			eval("\$footer = \"".template('footer')."\";");
			echo $footer;
			exit();
		}

		if($xmbpw != $member[password]) {
			eval("\$header = \"".template('header')."\";");
			echo $header;

			echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";

			end_time();
			eval("\$footer = \"".template('footer')."\";");
			echo $footer;
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

// Patch to prevent HTML injection vulnerability

		$email 		= checkInput($newemail, '', '', 'javascript');
		$site 		= checkInput($newsite, '', '', 'javascript');
		$aim 		= checkInput($newaim, '', '', 'javascript');
		$memlocation 	= checkInput($newmemlocation, '', '', 'javascript');
		$bio 		= checkInput($newbio, '', '', 'javascript');
		$sig 		= checkInput($newsig);
		$showemail 	= $newshowemail;
		$icq 		= checkInput($newicq, '', '', 'javascript');
		$avatar 	= checkInput($newavatar, '', '', 'javascript');
		$yahoo 		= checkInput($newyahoo, '', '', 'javascript');
		$bday 		= checkInput($bday, '', '', 'javascript');
		$pstatus 	= checkInput($newpstatus, '', '', 'javascript');
		$newsletter	= $newnewsletter;
		$msn		= checkInput($newmsn, '', '', 'javascript');
		$dateformatnew	= checkInput($dateformatnew, '', '', 'javascript');
		$mood 		= checkInput($newmood, '', '', 'javascript');

		$sig 		= addslashes($sig);
		$bio 		= addslashes($bio);
		$memlocation 	= addslashes($memlocation);

		$db->query("UPDATE $table_members SET email='$email', site='$site', aim='$aim', location='$memlocation', bio='$bio', sig='$sig', showemail='$showemail', timeoffset='$timeoffset1', icq='$icq', avatar='$avatar', yahoo='$yahoo', theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew', newsletter='$newsletter', timeformat='$timeformatnew', msn='$msn', dateformat='$dateformatnew', mood='$mood' WHERE username='$xmbuser'");

		if(trim($newpassword) != "") {
			if(ereg('"', $newpassword) || ereg("'", $newpassword)) {
				eval("\$header = \"".template('header')."\";");
				echo $header;

				echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span><center>";

				end_time();
				eval("\$footer = \"".template('footer')."\";");
				echo $footer;
				exit();
			}
			
			$newpassword = md5($newpassword);
			$db->query("UPDATE $table_members SET password='$newpassword' WHERE username='$xmbuser'");

			$currtime = time() - (86400*30);
			setcookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
			setcookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
		}

		echo "<center><span class=\"mediumtxt \">$lang_usercpeditpromsg</span></center>";
		?>

		<script>
		function redirect(){
			window.location.replace("memcp.php");
		}
		
		setTimeout("redirect();", 1250);
		</script>
		
		<?
	}
}

// Start Favorites
elseif($action == "favorites") {
	eval("\$header = \"".template("header")."\";");
	echo $header;

	makenav($action);

	if($favadd && !$favsubmit) {
		$query = $db->query("SELECT tid FROM $table_favorites WHERE tid='$favadd' AND username='$xmbuser' AND type='favorite'");
		$favthread = $db->fetch_array($query);

		if($favthread) {
			eval("\$header = \"".template('header')."\";");
			echo $header;

			echo "<center><span class=\"mediumtxt \">$lang_favonlistmsg</span></center>";

			end_time();
			eval("\$footer = \"".template('footer')."\";");
			echo $footer;
			exit();
		}

		$db->query("INSERT INTO $table_favorites VALUES ('$favadd', '$xmbuser', 'favorite')");
		echo "<center><span class=\"mediumtxt \">$lang_favaddedmsg</span></center>";
		?>
		<script>
		function redirect(){
			window.location.replace("memcp.php?action=favorites");
		}
		setTimeout("redirect();", 1250);
		</script>
		<?
	}
	
// Patch to fix Double Subscription issue

	if(!$favadd && !$favsubmit) {
		$query = $db->query("SELECT DISTINCT t.fid, t.tid, t.icon, t.subject, t.replies, t.lastpost FROM $table_favorites f, $table_threads t, $table_posts p WHERE f.tid=t.tid AND p.tid=t.tid AND f.username='$xmbuser' AND f.type='favorite' ORDER BY t.lastpost DESC");
// End patch

		$favnum = 0;
		while($fav = $db->fetch_array($query)) {
			$query2 = $db->query("SELECT name, fup, fid FROM $table_forums WHERE fid='$fav[fid]'");
			$forum = $db->fetch_array($query2);
			$lastpost = explode("|", $fav[lastpost]);
			$dalast = $lastpost[0];


			$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";

			$lastreplydate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));
			$lastreplytime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));
			$lastpost = "$lang_lastreply1 $lastreplydate $lang_textat $lastreplytime
			$lang_textby $lastpost[1]";
			$fav[subject] = stripslashes($fav[subject]);
			
			if($fav[icon] != "") {
				$fav[icon] = "<img src=\"$smdir/$fav[icon]\" />";
			} else {
				$fav[icon] = "&nbsp;";
			}

			$favnum++;
			eval("\$favs .= \"".template("memcp_favs_row")."\";");
		}

		if($favnum == 0) {
			eval("\$favs = \"".template("memcp_favs_none")."\";");
		}

		eval("\$favorites = \"".template("memcp_favs")."\";");
		$favorites = stripslashes($favorites);
		echo $favorites;
	}

	if(!$favadd && $favsubmit) {
		$query = $db->query("SELECT tid FROM $table_favorites WHERE username='$xmbuser' AND type='favorite'");
		while($fav = $db->fetch_array($query)) {
			$delete = "delete$fav[tid]";
			$delete = "${$delete}";
			$db->query("DELETE FROM $table_favorites WHERE username='$xmbuser' AND tid='$delete' AND type='favorite'");
		}
		
		echo "<center><span class=\"mediumtxt \">$lang_favsdeletedmsg</span></center>";
		?>
		<script>
		function redirect(){
			window.location.replace("memcp.php?action=favorites");
		}
		setTimeout("redirect();", 1250);
		</script>
		<?
	}
}

// Start Subscriptions
elseif($action == "subscriptions") {
	eval("\$header = \"".template("header")."\";");
	echo $header;

	makenav($action);
	
// Patch to fix Double Subscription issue

	if(!$subadd && !$subsubmit) {
		$query = $db->query("SELECT DISTINCT t.fid, t.tid, t.icon, t.subject, t.replies, t.lastpost FROM $table_favorites f, $table_threads t, $table_posts p WHERE f.tid=t.tid AND p.tid=t.tid AND f.username='$xmbuser' AND f.type='subscription' ORDER BY t.lastpost DESC");

// End patch

		$subnum = 0;
		while($fav = $db->fetch_array($query)) {
			$query2 = $db->query("SELECT name, fup, fid FROM $table_forums WHERE fid='$fav[fid]'");
			$forum = $db->fetch_array($query2);
			$lastpost = explode("|", $fav[lastpost]);
			$dalast = $lastpost[0];
	
	
			$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";
	
			$lastreplydate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));
			$lastreplytime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));
			$lastpost = "$lang_lastreply1 $lastreplydate $lang_textat $lastreplytime
			$lang_textby $lastpost[1]";
			$fav[subject] = stripslashes($fav[subject]);
	
			if($fav[icon] != "") {
				$fav[icon] = "<img src=\"$smdir/$fav[icon]\" />";
			} else {
				$fav[icon] = "&nbsp;";
			}
	
			$subnum++;
			eval("\$subscriptions .= \"".template("memcp_subscriptions_row")."\";");
		}
	
		if($subnum == 0) {
			eval("\$subscriptions = \"".template("memcp_subscriptions_none")."\";");
		}
	
		eval("\$page = \"".template("memcp_subscriptions")."\";");
		$page = stripslashes($page);
		echo $page;
		
	}elseif($subadd && !$subsubmit) {
		$query = $db->query("SELECT count(tid) FROM $table_favorites WHERE tid='$subadd' AND username='$xmbuser' AND type='subscription'");
		if($db->result($query,0) == 1){
			echo "<center><span class=\"mediumtxt \">$lang_subonlistmsg</span><center>";

			end_time();
			eval("\$footer = \"".template('footer')."\";");
			echo $footer;
			exit();
		}else{
			$db->query("INSERT INTO $table_favorites VALUES ('$subadd', '$xmbuser', 'subscription')");
			echo "<center><span class=\"mediumtxt \">$lang_subaddedmsg</span></center>";
			?>
			<script>
			function redirect(){
				window.location.replace("memcp.php?action=subscriptions");
			}
			setTimeout("redirect();", 1250);
			</script>
			<?
		}
	}elseif(!$subadd && $subsubmit) {
		$query = $db->query("SELECT tid FROM $table_favorites WHERE username='$xmbuser' AND type='subscription'");
		while($sub = $db->fetch_array($query)) {
			$delete = "delete$sub[tid]";
			$delete = "${$delete}";
			$db->query("DELETE FROM $table_favorites WHERE username='$xmbuser' AND tid='$delete' AND type='subscription'");
		}

		echo "<center><span class=\"mediumtxt \">$lang_subsdeletedmsg</span></center>";
		?>
		<script>
		function redirect(){
			window.location.replace("memcp.php?action=subscriptions");
		}
		setTimeout("redirect();", 1250);
		</script>
		<?
	}

}
// Load the Default Page
else {
	eval("\$header = \"".template("header")."\";");
	echo $header;
	eval($lang_evalusercpwelcome);

	makenav($action);

	// Load Buddy List
	$query = $db->query("SELECT * FROM $table_buddys WHERE username='$xmbuser'");
	while($buddy = $db->fetch_array($query)) {
		$query2 = $db->query("SELECT * FROM $table_whosonline WHERE username='$buddy[buddyname]'");
		$onlineinfo = $db->fetch_array($query2);
		
		if($onlineinfo) {
			eval("\$buddys[online] .= \"".template("buddylist_buddy_online")."\";");
		} else {
			eval("\$buddys[offline] .= \"".template("buddylist_buddy_offline")."\";");
		}
	}
	
	$query = $db->query("SELECT * FROM $table_members WHERE username='$xmbuser'");
	$member = $db->fetch_array($query);
	if($member[avatar] == "") {
		$member[avatar] = "&nbsp;";
	} else {
		$member[avatar] = "<img src=\"$member[avatar]\" border=0\" />";
	}


// Make the Page
	$query = $db->query("SELECT * FROM $table_u2u WHERE msgto='$xmbuser' AND folder='inbox' ORDER BY dateline DESC LIMIT 0, 15");
	$u2unum = $db->num_rows($query);
	while($message = $db->fetch_array($query)) {
		$postdate = gmdate("$dateformat",$message[dateline] + ($timeoffset * 3600) + ($addtime * 3600));
		$posttime = gmdate("$timecode",$message[dateline] + ($timeoffset * 3600) + ($addtime * 3600));
		$senton = "$postdate $lang_textat $posttime";
		
		if($message[subject] == "") {
			$message[subject] = "&lt;$lang_textnosub&raquo;";
		}

		if($message[readstatus] == "yes"){
			$read = "$lang_textread";
		} else {
			$read = "$lang_textunread";
		}

		$message[subject] = stripslashes($message[subject]);
		eval("\$messages .= \"".template("memcp_home_u2u_row")."\";");
	}

	if($u2unum == 0) {
		eval("\$messages = \"".template("memcp_home_u2u_none")."\";");
	}
	$query2 = $db->query("SELECT * FROM $table_favorites f, $table_threads t, $table_posts p WHERE f.tid=t.tid AND p.tid=t.tid AND p.subject=t.subject AND f.username='$xmbuser' AND f.type='favorite' ORDER BY t.lastpost DESC");
	$favnum = $db->num_rows($query2);
	while($fav = $db->fetch_array($query2)) {
		$query = $db->query("SELECT name, fup, fid FROM $table_forums WHERE	fid='$fav[fid]'");
		$forum = $db->fetch_array($query);
		$lastpost = explode("|", $fav[lastpost]);
		$dalast = $lastpost[0];
		$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";
		$lastreplydate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));
		$lastreplytime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));
		$lastpost = "$lang_lastreply1 $lastreplydate $lang_textat
		$lastreplytime $lang_textby $lastpost[1]";
		$fav[subject] = stripslashes($fav[subject]);
		
		if($fav[icon] != "") {
			$fav[icon] = "<img src=\"$smdir/$fav[icon]\" />";
		} else {
			$fav[icon] = "&nbsp;";
		}

		eval("\$favs .= \"".template("memcp_home_favs_row")."\";");
	}

	if($favnum == '0') {
		eval("\$favs .= \"".template("memcp_home_favs_none")."\";");
	}

	eval("\$home = \"".template("memcp_home")."\";");
	$home = stripslashes($home);
	echo $home;
}

end_time();
eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>