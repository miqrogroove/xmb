<?
require "./header.php";
loadtemplates('header,footer,misc_login,misc_search,misc_search_results_row,misc_search_results_none,misc_search_results,misc_lostpw,misc_online_row_admin,misc_online_row,misc_online_admin,misc_online,misc_mlist_row_email,misc_mlist_row_site,misc_mlist_row,misc_mlist');

if($loginsubmit) {
	$password = md5($password);
	$query = $db->query("SELECT * FROM $table_members WHERE username='$username'");
	$member = $db->fetch_array($query);

	if(!$member[username]) {
		echo "$lang_badname";
		exit;
	}

	if($password != $member[password]) {
		echo "$lang_textpwincorrect";
		exit;
	}

	$currtime = time() + (86400*30);
	$username = $member[username];
	setcookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
	setcookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
?> 
	<script> 
	function redirect() { 
	window.location.replace("index.php"); 
	} 
	setTimeout("redirect();", 1250); 
	</script> 

<? 
}
if($action == 'logout') {
	$currtime = time() - (86400*30);
	setcookie("xmbuser", "guest", $currtime, $cookiepath, $cookiedomain);
	setcookie("xmbpw", "nopass", $currtime, $cookiepath, $cookiedomain);
	?> 
	<script> 
	function redirect() { 
	window.location.replace("index.php"); 
	} 
	setTimeout("redirect();", 1250); 
	</script> 

<? 
}

if($action == 'login') {
		$navigation = $lang_textlogin;
} elseif($action == 'logout') {
		$navigation = $lang_textlogout;
} elseif($action == 'faq') {
	$navigation = $lang_textfaq;
} elseif($action == 'search') {
	$navigation = $lang_textsearch;
} elseif($action == 'lostpw') {
	$navigation = $lang_textlostpw;
} elseif($action == 'online') {
	$navigation = $lang_whosonline;
} elseif($action == "list") {
	$navigation = $lang_textmemberlist;
} elseif($action == "active") {
	$navigation = $lang_textactivethreads;
}

$navigation = "&raquo; $navigation";
eval("\$header = \"".template("header")."\";");
echo $header;

if($action == 'login') {
	if(!$loginsubmit) {
		eval("\$misc = \"".template("misc_login")."\";");
		echo $misc;
	}
}

if($action == "search") {

	if($searchstatus != "on") {
		echo $lang_searchoff;
		exit;
	}

	if(!$searchsubmit) {

		$forumselect = "<select name=\"srchfid\">\n";
		$forumselect .= "<option value=\"all\">$lang_textall</option>\n";
		$queryforum = $db->query("SELECT * FROM $table_forums WHERE type='forum'");
		while($forum = $db->fetch_array($queryforum)) {

			$authorization = privfcheck($forum[private], $forum[userlist]);

			if($authorization == "true") {
				$forumselect .= "<option value=\"$forum[fid]\">$forum[name]</option>\n";
			}
		}
		$forumselect .= "</select>";

		eval("\$search = \"".template("misc_search")."\";");
		echo $search;

	}

	if($searchsubmit || $page) {
		if (!isset($page)) {
			$page = 1;
				$offset = 0;
			$start = 0;
			$end = 20;
		} else {
			$offset = ($page-1)*20;
			$start = $offset;
			$end = $offset+20;
		}
		$sql = "SELECT count(*), p.*, t.tid AS ttid, t.subject AS tsubject, f.fid, f.private AS fprivate, f.userlist AS fuserlist FROM $table_posts p, $table_threads t LEFT JOIN $table_forums f ON  f.fid=t.fid WHERE p.tid=t.tid";

		if($srchfrom == "0") {
			$srchfrom = time();
		}

		$srchfrom = time() - $srchfrom;
		if($srchtxt) {
			$sql .= " AND (p.message LIKE '%$srchtxt%' OR p.subject LIKE '%$srchtxt%' OR t.subject LIKE '%$srchtxt')";
		}
		if($srchuname != "") {
			$sql .= " AND p.author='$srchuname'";
		}
		if($srchfid != "all" && $srchfid != "") {
			$sql .= " AND p.fid='$srchfid'";
		}
		if($srchfrom) {
			$sql .= " AND p.dateline >= '$srchfrom'";
		}
			$sql .=" GROUP BY dateline ORDER BY dateline DESC LIMIT $start,20";
			$pagenum = $page+1;
			eval("\$nextlink = \"".template("misc_search_nextlink")."\";");
		$querysrch = $db->query($sql);
		$postcount = 0;
		while($post = $db->fetch_array($querysrch)) {
			$authorization = privfcheck($post[fprivate], $post[fuserlist]);
			if($authorization == "true") {
				$date = date("$dateformat",$post[dateline]);
				$time = date("$timecode",$post[dateline]);
				$poston = "$date $lang_textat $time";

				$post[tsubject] = stripslashes($post[tsubject]);
				$postcount++;
				eval("\$searchresults .= \"".template("misc_search_results_row")."\";");
			}
		}

		if($postcount == "0") {
			eval("\$searchresults = \"".template("misc_search_results_none")."\";");
		}
		
		eval("\$search = \"".template("misc_search_results")."\";");
		echo $search;

	}
}

if($action == 'lostpw') {
	if(!$lostpwsubmit) {
		eval("\$misc = \"".template("misc_lostpw")."\";");
		echo $misc;
	}

	if($lostpwsubmit) {
		$query = $db->query("SELECT username, email FROM $table_members WHERE username='$username' AND email='$email'");
		$member = $db->fetch_array($query);

		if(!$member[username] || !$member[email]) {
			echo "$lang_badinfo";
			exit;
		}

		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		mt_srand((double)microtime() * 1000000);
		for($get = strlen($chars); $i < 8; $i++)
			$newpass .= $chars[mt_rand(0, $get)];
		$newmd5pass = md5($newpass);

		$db->query("UPDATE $table_members SET password='$newmd5pass' WHERE username='$member[username]' OR email='$member[email]'");

		mail("$member[email]", "$lang_textyourpw", "$lang_textyourpwis\n\n$member[username]\n$newpass", "From: $adminemail");
		echo "<span class=\"mediumtxt \">$lang_emailpw</span>";
		?><script>
		function redirect()
		{
		window.location.replace("index.php");
		}
		setTimeout("redirect();", 1250);
		</script>
		<?
	}
}

if($action == 'online') {

	$query = $db->query("SELECT * FROM $table_whosonline WHERE username != 'onlinerecord' ORDER BY time DESC");
	while($online = $db->fetch_array($query)){
		$onlinetime = gmdate("$timecode",$online[time] + ($timeoffset * 3600));

		$username = str_replace("xguest123", "$lang_textguest1", $online[username]);

		if($online[username] != "xguest123" && $online[username] != "$lang_textguest1") {
			$online[username] = "<a href=\"member.php?action=viewpro&member=$online[username]\">$username</a>";
		}
		else {
			$online[username] = $username;
		}
		if($status == "Administrator") {
			eval("\$onlineusers .= \"".template("misc_online_row_admin")."\";");
		} else {
			eval("\$onlineusers .= \"".template("misc_online_row")."\";");
		}
	}
	if($status == "Administrator") {
		eval("\$misc = \"".template("misc_online_admin")."\";");
	} else {
		eval("\$misc = \"".template("misc_online")."\";");
	}
	echo $misc;
}

if($action == "list") {

	if($memliststatus != "on") {
		echo $lang_memlistoff;
		exit;
	}


	if(!$order) {
		$order = "regdate";
	}

	if($page) {
		$start_limit = ($page-1) * $memberperpage;
	}
	else {
		$start_limit = 0;
		$page = 1;
	}

	if($srchmem == "") {
		$query = $db->query("SELECT count(uid) FROM $table_members");
	} else {
		$query = $db->query("SELECT count(uid) FROM $table_members WHERE username LIKE '%$srchmem%'");
	}
	$num = $db->result($query,0);

	if($num > $memberperpage) {
		$pages = $num / $memberperpage;
		$pages = ceil($pages);

		if ($page == $pages) {
			$to = $pages;
		} elseif ($page == $pages-1) {
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
				$fwd_back .= "&nbsp;&nbsp;<a href=\"misc.php?action=list&page=$i\">$i</a>&nbsp;&nbsp;";
			} elseif ($order && !$desc) {
				$fwd_back .= "&nbsp;&nbsp;<a href=\"misc.php?action=list&order=$order&page=$i\">$i</a>&nbsp;&nbsp;";
			} elseif ($order && $desc) {
				$fwd_back .= "&nbsp;&nbsp;<a href=\"misc.php?action=list&order=$order&desc=$desc&page=$i\">$i</a>&nbsp;&nbsp;";
			}
		}

		$fwd_back .= "<a href=\"misc.php?action=list&page=$pages\">>></a>";
		$multipage = "$backall $backone $fwd_back $forwardone $forwardall";
	}

	if($order != "regdate" && $order != "username"&& $order != "postnum") {
		$order = "regdate";
	}

	if($srchmem == "") {
		$querymem = $db->query("SELECT * FROM $table_members ORDER BY $order $desc LIMIT $start_limit, $memberperpage");
	} else {
		$querymem = $db->query("SELECT * FROM $table_members WHERE username LIKE '%$srchmem%' ORDER BY $order $desc LIMIT $start_limit, $memberperpage");
	}
if($forumleaders == "010101") { 
$querymem = $db->query("SELECT * FROM $table_members WHERE status = 'Administrator' OR status = 'Super Moderator' OR status = 'Moderator' ORDER BY regdate"); 
}
	while ($member = $db->fetch_array($querymem)) {

		$member[regdate] = date("n/j/y",$member[regdate]);

		if($member[email] != "" && $member[showemail] == "yes") {
			eval("\$email = \"".template("misc_mlist_row_email")."\";");
		} else {
			$email = "&nbsp;";
		}

		$member[site] = str_replace("http://", "", $member[site]);
		$member[site] = "http://$member[site]";

		if($member[site] == "http://") {
			$site = "&nbsp;";
		} else {
			eval("\$site = \"".template("misc_mlist_row_site")."\";");
		}

		if($member[location] == "") {
			$member[location] = "&nbsp;";
		}

		$memurl = rawurlencode($member[username]);

		eval("\$members .= \"".template("misc_mlist_row")."\";");
	}
	eval("\$memlist = \"".template("misc_mlist")."\";");
	echo $memlist;
}

$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>