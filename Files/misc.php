<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

// Get global settings
	require "./header.php";

// Patch to deal with spaces in member names for searches, etc.

	$member = str_replace("%20", "+", $member); 

// pre-Load templates (saves queries)
	loadtemplates('header,footer,misc_login,misc_search,misc_search_results_row,misc_search_results_none,misc_search_results,misc_lostpw,misc_online_row_admin,misc_online_row,misc_online_admin,misc_online,misc_mlist_row_email,misc_mlist_row_site,misc_mlist_row,misc_mlist');

// Create navigation
	switch($action){
		case 'login';
			$navigation = '&raquo; '.$lang_textlogin;
			break;
		case 'logout';
			$navigation = '&raquo; '.$lang_textlogout;
			break;
		
		case 'faq';
			$navigation = '&raquo; '.$lang_textfaq;
			break;
		
		case 'search';			
			$navigation = '&raquo; '.$lang_textsearch;
			break;
		
		case 'lostpw';
			$navigation = '&raquo; '.$lang_textlostpw;
			break;
		
		case 'online';
			$navigation = '&raquo; '.$lang_whosonline;
			break;
		
		case 'list';
			$navigation = '&raquo; '.$lang_textmemberlist;
			break;
		
		case 'active';
			$navigation = '&raquo; '.$lang_textactivethreads;
			break;
		
		case 'onlinetoday';
			$navigation = '&raquo; '.$lang_whosonlinetoday;
			break;
	}

// Based on the action, choose what to do
	switch($action){
		case 'login':
			if(!$loginsubmit) {
				eval("\$misc = \"".template("misc_login")."\";");
				$misc = stripslashes($misc);
			}else{
				$password = md5($password);
				$query = $db->query("SELECT * FROM $table_members WHERE username='$username' AND password='$password'");
				if($query && $db->num_rows($query) == 1){
					$member = $db->fetch_array($query);
					$db->query("DELETE FROM $table_whosonline WHERE ip='$onlineip' && username='xguest123'");
					$currtime = time() + (86400*30);
					$username = $member[username];
					if($server == 'Mic'){
						$misc = '<script>
							function setCookie(name, value, expires, path, domain, secure) {
								var curCookie = name + "=" + escape(value) +
								((expires) ? "; expires=" + expires.toGMTString() : "") +
								((path) ? "; path=" + path : "") +
								((domain) ? "; domain=" + domain : "") +
								((secure) ? "; secure" : "");
								document.cookie = curCookie;
							}
							
							var now = new Date();
							now.setTime(now.getTime() + 365 * 24 * 60 * 60 * 1000);
							
							setCookie("xmbuser", "'.$username.'", now, "'.$cookiepath.'", "'.$cookiedomain.'");
							setCookie("xmbpw", "'.$password.'", now, "'.$cookiepath.'", "'.$cookiedomain.'");
							
							window.location="index.php";
						</script>';
					}else{
						setcookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
						setcookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
						header("Location: index.php");
					}
					
				}else{
					eval("\$header = \"".template("header")."\";");
					echo $header;
					eval("\$incorrectpassword = \"".template("misc_login_incorrectdetails")."\";");
					end_time();
					eval("\$footer = \"".template("footer")."\";");
					echo $incorrectpassword;
					echo $footer;
					exit();
				}
			}
			break;
		
		case 'logout':
			$currtime = time() - (86400*30);
			$query = $db->query("DELETE FROM $table_whosonline WHERE username='$xmbuser'");
			
			if($server == 'Mic'){
				$misc = '<script>
				function setCookie(name, value, expires, path, domain, secure) {
					var curCookie = name + "=" + escape(value) +
					((expires) ? "; expires=" + expires.toGMTString() : "") +
					((path) ? "; path=" + path : "") +
					((domain) ? "; domain=" + domain : "") +
					((secure) ? "; secure" : "");
					document.cookie = curCookie;
				}
				
				var now = new Date();
				now.setTime(now.getTime() + 365 * 24 * 60 * 60 * 1000);
				
				setCookie("xmbuser", "'.$username.'", now, "'.$cookiepath.'", "'.$cookiedomain.'");
				setCookie("xmbpw", "'.$password.'", now, "'.$cookiepath.'", "'.$cookiedomain.'");
				
				window.location="index.php";
				</script>';
			}else{
				setcookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
				setcookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
				header("Location: index.php");
			}
			break;
		
		case 'search':
			if($searchstatus != "on") {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				
				eval("\$featureoff = \"".template("misc_feature_notavailable")."\";");
				end_time();
				eval("\$footer = \"".template("footer")."\";");
				$featureoff = stripslashes($featureoff);
				echo $featureoff;
				echo $footer;
				exit();
			}
			
			if(!$searchsubmit && !$page){
				$forumselect = "<select name=\"srchfid\">\n";
				$forumselect .= "<option value=\"all\">$lang_textall</option>\n";
				$queryforum = $db->query("SELECT * FROM $table_forums WHERE type='forum'");
				while($forum = $db->fetch_array($queryforum)) {
					$authorization = privfcheck($forum[private], $forum[userlist]);
					
					if($authorization) {
						$forumselect .= "<option value=\"$forum[fid]\">$forum[name]</option>\n";
					}
				}
				$forumselect .= "</select>";
				
				eval("\$search = \"".template("misc_search")."\";");
				$misc = stripslashes($search);
			}else{
				foreach($_POST as $key => $val){
					$$key 	= $val;
				}
				
				if (!isset($page)) {
					$page 		 = 1;
					$offset 	 = 0;
					$start		 = 0;
					$end		 = 20;
				} else {
					$offset		 = ($page-1)*20;
					$start		 = $offset;
					$end		 = $offset+20;
				}
				
				if(!(empty($srchtxt) && empty($srchuname))){
					$sql 		 = "SELECT count(*), p.*, t.tid AS ttid, t.subject AS tsubject, f.fid, f.private AS fprivate, f.userlist AS fuserlist FROM $table_posts p, $table_threads t LEFT JOIN $table_forums f ON  f.fid=t.fid WHERE p.tid=t.tid";
					
					if($srchfrom == "0") {
						$srchfrom= time();
					}
					
					$srchfrom = time() - $srchfrom;
					if($srchtxt) {
						$srchtxt = checkInput($srchtxt);
						$sql 	.= " AND (p.message LIKE '%$srchtxt%' OR p.subject LIKE '%$srchtxt%' OR t.subject LIKE '%$srchtxt')";
					}
					if($srchuname != "") {
						$srchuname = checkInput($srchuname);
						$sql 	.= " AND p.author='$srchuname'";
					}
					if($srchfid != "all" && $srchfid != "") {
						$sql 	.= " AND p.fid='$srchfid'";
					}
					if($srchfrom) {
						$sql 	.= " AND p.dateline >= '$srchfrom'";
					}
					
					$sql 		.=" GROUP BY dateline ORDER BY dateline DESC LIMIT $start,20";
					$pagenum 	 = $page+1;
					
					$querysrch = $db->query($sql);
					
					$postcount = 0;
					
					while($post = $db->fetch_array($querysrch)) {
						$authorization = privfcheck($post[fprivate], $post[fuserlist]);
						if($authorization) {
							$date = gmdate($dateformat, $post[dateline] + ($timeoffset * 3600) + ($addtime * 3600));
							$time = gmdate($timecode, $post[dateline] + ($timeoffset * 3600) + ($addtime * 3600));
							$poston = "$date $lang_textat $time";

							$post[tsubject] = stripslashes($post[tsubject]); 
							eval("\$searchresults .= \"".template("misc_search_results_row")."\";"); 
						} 
						$postcount++;

					}
				}
				if($postcount == 0 || !$postcount) {
					eval("\$searchresults = \"".template("misc_search_results_none")."\";");
				}elseif($postcount == 20){
					eval("\$nextlink = \"".template("misc_search_nextlink")."\";");
				}
				
				eval("\$search = \"".template("misc_search_results")."\";");
				$misc = stripslashes($search);
			}
			break;
		
		case 'lostpw':
			if(!$lostpwsubmit) {
				eval("\$misc = \"".template("misc_lostpw")."\";");
				$misc = stripslashes($misc);
			}else{
				$query = $db->query("SELECT username, email, pwdate FROM $table_members WHERE username='$username' AND email='$email'");
				$member = $db->fetch_array($query);
				
				$time = time()-86400;
				if($member[pwdate] > $time){
					eval("\$header = \"".template("header")."\";");
					echo $header;					
					
					end_time();
					eval("\$footer = \"".template("footer")."\";");
					
					echo $lang_lostpw_in24hrs;
					echo $footer;
					exit();
				}
				
				if(!$member[username]) {
					eval("\$header = \"".template("header")."\";");
					echo $header;
					
					end_time();
					eval("\$footer = \"".template("footer")."\";");
					
					echo $lang_badinfo;
					echo $footer;
					exit();
				}
				
				$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';

				$newpass = ''; 
				mt_srand((double)microtime() * 1000000); 
				$max = mt_rand(8, 12); 
				for($get=strlen($chars), $i=0; $i < $max; $i++){ 
					$newpass .= $chars[mt_rand(0, $get)]; 
				}


				
				$newmd5pass = md5($newpass);
// Patch to prevent inadvertant password change for multiple users with same email address
				
				$db->query("UPDATE $table_members SET password='$newmd5pass', pwdate='".time()."' WHERE username='$member[username]' AND email='$member[email]'");
// End Patch

// Patch to further expand lost password email message

				mail($member[email], $bbname." - ".$lang_textyourpw, $lang_textyourpwis."\n\n".$member[username]."\n".$newpass, "From: ".$bbname." <".$adminemail.">");

// End Patch
				
				$misc .= '<span class="mediumtxt"><center>'.$lang_emailpw.'</span></center><br />';
				$misc .='<script>function redirect(){window.location.replace("index.php");}setTimeout("redirect();", 1250);</script>';
			}
			break;
		
		case 'online':
			$query = $db->query("SELECT * FROM $table_whosonline WHERE username != 'onlinerecord' ORDER BY time DESC");
			while($online = $db->fetch_array($query)){
				$onlinetime = gmdate("$timecode",$online[time] + ($timeoffset * 3600) + ($addtime * 3600));
				$username = str_replace("xguest123", "$lang_textguest1", $online[username]);
				
				$online['location'] = str_replace('&amp;quot;', '"', $online['location']);
				$online['location'] = str_replace("&amp;#039;", "'", $online['location']);
				$online[location] = stripslashes($online[location]);
				
				if($online[username] != "xguest123" && $online[username] != "$lang_textguest1") {
					$online[username] = "<a href=\"member.php?action=viewpro&member=$online[username]\">$username</a>";
				}else{
					$online[username] = $username;
				}
				
				if($status == "Administrator" || $status =="Super Administrator") {
					eval("\$onlineusers .= \"".template("misc_online_row_admin")."\";");
				}else{
					eval("\$onlineusers .= \"".template("misc_online_row")."\";");
				}
			}
			
			if($status == "Administrator" || $status =="Super Administrator") {
				eval("\$misc = \"".template("misc_online_admin")."\";");
			}else{
				eval("\$misc = \"".template("misc_online")."\";");
			}
			
			$misc = stripslashes($misc);
			break;
		
		case 'onlinetoday':
			$datecut = time() - (3600 * 24);
			$query = $db->query("SELECT username FROM $table_members WHERE lastvisit>='$datecut' ORDER BY lastvisit ASC");
			
			$todaymembersnum = 0;
			$todaymembers = ''; 
			$comma = '';
			
			while ($memberstoday = $db->fetch_array($query)) {
				$todaymembers .= $comma.'<a href="member.php?action=viewpro&member='.rawurlencode($memberstoday['username']).'">'.$memberstoday['username'].'</a>';
				++$todaymembersnum;
				$comma = ", ";
			}
			
			if ($todaymembersnum == 1) {
				$memontoday = $todaymembersnum.$lang_textmembertoday;
			}else{
				$memontoday = $todaymembersnum.$lang_textmemberstoday; 
			}
			
			eval("\$misc = \"".template("misc_online_today")."\";");
			$misc = stripslashes($misc);
			break;
		
		case 'list':
			// Check for status of member-list
			if($memliststatus != "on") {
				eval("\$header = \"".template("header")."\";");
				echo $header;
				
				eval("\$featureoff = \"".template("misc_feature_notavailable")."\";");
				end_time();
				eval("\$footer = \"".template("footer")."\";");
				
				$featureoff = stripslashes($featureoff);
				echo $featureoff;
				echo $footer;
				exit();
			}
			
			if($page) {
				$start_limit = ($page-1) * $memberperpage;
			}else{
				$start_limit = 0;
				$page = 1;
			}
			
			if($order != "regdate" && $order != "username" && $order != "postnum") {
				$order = "regdate";
			}
			
			
			if($staff == 'view'){
				$querymem = $db->query("SELECT * FROM $table_members WHERE status = 'Administrator' OR status = 'Super Moderator' OR status = 'Moderator' OR status ='Super Administrator' ORDER BY $order $desc LIMIT $start_limit, $memberperpage");
				$num = $db->result($db->query("SELECT count(uid) FROM $table_members WHERE status = 'Administrator' OR status = 'Super Moderator' OR status = 'Moderator' OR status ='Super Administrator'"), 0);
				$staff = '&staff=view';
			}elseif($srchmem == "") {
				$querymem = $db->query("SELECT * FROM $table_members ORDER BY $order $desc LIMIT $start_limit, $memberperpage");
				$num = $db->result($db->query("SELECT count(uid) FROM $table_members"), 0);
				$staff = '';
			}else {
				$querymem = $db->query("SELECT * FROM $table_members WHERE username LIKE '%$srchmem%' ORDER BY $order $desc LIMIT $start_limit, $memberperpage");
				$num = $db->result($db->query("SELECT count(uid) FROM $table_members WHERE username LIKE '%$srchmem%'"), 0);
				$staff = '';
			}
			
			$replace = array('http://', 'https://', 'ftp://');
			
			while ($member = $db->fetch_array($querymem)) {
				$member[regdate] = gmdate("n/j/y",$member[regdate]);
				
				if($member[email] != "" && $member[showemail] == "yes") {
					eval("\$email = \"".template("misc_mlist_row_email")."\";");
				}else{
					$email = "&nbsp;";
				}
				
				$member[site] = str_replace($replace, '', $member['site']);
				$member[site] = "http://$member[site]";
				
				if($member[site] == "http://") {
					$site = "&nbsp;";
				}else{
					eval("\$site = \"".template("misc_mlist_row_site")."\";");
				}
				
				if($member[location] == "") {
					$member[location] = "&nbsp;";
				}
				
				$memurl = rawurlencode($member[username]);
				
				eval("\$members .= \"".template("misc_mlist_row")."\";");
			}
			
			if($num > $memberperpage) {
				if(!$memberperpage){
					$memberperpage=30;
				}
				
				$pages = $num / $memberperpage;
				$pages = ceil($pages);
							
				if ($page == $pages) {
					$to = $pages;
				}elseif($page == $pages-1) {
					$to = $page+1;
				} elseif ($page == $pages-2) {
					$to = $page+2;
				} else {
					$to = $page+3;
				}
				
				if ($page == 1 || $page == 2 || $page == 3) {
					$from = 1;
				} else {
					$from = $page-3;
				}
				
				$fwd_back .= "<a href=\"misc.php?action=list&page=1\"><<</a>";
							
				for ($i = $from; $i <= $to; $i++) {
					if ($i == $page) {
						$fwd_back .= "&nbsp;&nbsp;<u><b>$i</b></u>&nbsp;&nbsp;";
					} elseif (!$order) {
						$fwd_back .= "&nbsp;&nbsp;<a href=\"misc.php?action=list&page=$i$staff\">$i</a>&nbsp;&nbsp;";
					} elseif ($order && !$desc) {
						$fwd_back .= "&nbsp;&nbsp;<a href=\"misc.php?action=list&order=$order&page=$i$staff\">$i</a>&nbsp;&nbsp;";
					} elseif ($order && $desc) {
						$fwd_back .= "&nbsp;&nbsp;<a href=\"misc.php?action=list&order=$order&desc=$desc&page=$i$staff\">$i</a>&nbsp;&nbsp;";
					}
				}
							
				$fwd_back .= "<a href=\"misc.php?action=list&page=$pages$staff\">>></a>";
				$multipage = "$backall $backone $fwd_back $forwardone $forwardall";
			}
			
			eval("\$memlist = \"".template("misc_mlist")."\";");
			$misc = stripslashes($memlist);
			break;
		
	}

// Show the created page
	eval("\$header = \"".template("header")."\";");
	echo $header;
	echo $misc;

// Show footer
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
?>