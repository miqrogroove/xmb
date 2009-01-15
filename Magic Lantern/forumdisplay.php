<?
/*

XMB 1.6 v2b Magic Lantern Final
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/
require "./header.php";
require "./xmb.php";
loadtemplates('header,footer,forumdisplay_newtopic,forumdisplay_newpoll,forumdisplay_password,forumdisplay_thread,forumdisplay_invalidforum,forumdisplay_nothreads,forumdisplay,forumdisplay_subforum_lastpost,forumdisplay_thread_lastpost');

$query = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
$forum = $db->fetch_array($query);

if($forum[type] != "forum" && $forum[type] != "sub") {
	$notexist = $lang_textnoforum;
}

if($forum[type] == "forum") {
	$navigation .= "&raquo; $forum[name]";
} else {
	$query = $db->query("SELECT name, fid FROM $table_forums WHERE fid='$forum[fup]'");
	$fup = $db->fetch_array($query);
	$navigation .= "&raquo; <a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> &raquo; $forum[name]";
}

// Start Forum Password Verify (by surfi)
if($forum[password] != "" && $action == "pwverify") {
	if($pw != $forum[password]) {
		eval("\$header = \"".template("header")."\";");
		echo $header;
		echo "$lang_invalidforumpw";
		exit;
	} else {
		setcookie("fidpw$fid", $pw, (time() + (86400*30)), $cookiepath, $cookiedomain);

		header("Location: forumdisplay.php?fid=$fid");
	}
}

eval("\$header = \"".template("header")."\";");
echo $header;

$query = $db->query("SELECT name FROM $table_forums WHERE type='sub' AND fup='$fid' AND status='on' ORDER BY displayorder");
$sub = $db->fetch_array($query);

if($sub[name] != "") {
	$fulist = $forum[userlist];
	$querys = $db->query("SELECT * FROM $table_forums WHERE type='sub' AND fup='$fid' AND status='on'");
	while($sub = $db->fetch_array($querys)) {
		$forumlist .= forum($sub, "forumdisplay_subforum");
	}
	$forum[userlist] = $fulist;
	eval("\$subforums = \"".template("forumdisplay_subforums")."\";");
}
if($notexist != $lang_textnoforum) {
	eval("\$newtopiclink = \"".template("viewthread_newtopic")."\";");
	if($forum[pollstatus] != "off") {
		eval("\$newpolllink = \"".template("viewthread_newpoll")."\";");
	}

}

// Start Topped Image Processing
if(eregi(".gif", $lang_toppedprefix) || eregi(".jpg", $lang_toppedprefix) || eregi(".png", $lang_toppedprefix)){
    $lang_toppedprefix = "<img src=\"$imgdir/$lang_toppedprefix\">";
}
// Start Poll Image Processing
if(eregi(".gif", $lang_pollprefix) || eregi(".jpg", $lang_pollprefix) || eregi(".png", $lang_pollprefix)){
    $lang_pollprefix = "<img src=\"$imgdir/$lang_pollprefix\">";
}

if(!$tpp || $tpp == '') {
	$tpp = $topicperpage;
}

if($page) {
	$start_limit = ($page-1) *$tpp;
} else {
	$start_limit = 0;
	$page = 1;
}
if($cusdate != 0) {
	$cusdate = time() - $cusdate;
	$cusdate = "AND (substring_index(lastpost, '|',1)+1) >= '$cusdate'";
}
elseif($cusdate == 0) {
	$cusdate = "";
}

if(!$ascdesc) {
	$ascdesc = "DESC";
}
$dotadd1 = "";
$dotadd2 = "";
$dotadd3 = "";
if($dotfolders == "on" && $xmbuser != "") {

	$dotadd1 = "DISTINCT p.author AS dotauthor, ";
	$dotadd2 = "LEFT JOIN $table_posts p ON (t.tid = p.tid AND p.author = '$xmbuser')";

}
$querytop = $db->query("SELECT $dotadd1 t.*, (substring_index(lastpost, '|',1)+1) lastpostd FROM $table_threads t $dotadd2 WHERE t.fid='$fid' $cusdate ORDER BY topped $ascdesc,lastpostd $ascdesc LIMIT $start_limit, $tpp");

// Start Authorization Checks
$authorization = privfcheck($forum[private], $forum[userlist]);
if(!$authorization) {
	echo "<div class=\"tablerow\">$lang_privforummsg</div>";
	exit;
}
if($forum[password] != $HTTP_COOKIE_VARS["fidpw$fid"] && $forum[password] != "") {
	$url = "forumdisplay.php?fid=$fid&action=pwverify";
	eval("\$pwform = \"".template("forumdisplay_password")."\";");
	echo $pwform;
	exit;
}
// Start Displaying the threads
$topicsnum = 0;
while($thread = $db->fetch_array($querytop)) {
	$lastpost = explode("|", $thread[lastpost]);
	$dalast = $lastpost[0];

	if($lastpost[1] != "Anonymous") {
	$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";
	} else {
	$lastpost[1] = "$lang_textanonymous";
	}

	$lastreplydate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600));
	$lastreplytime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600));
	$lastpost = "$lastreplydate $lang_textat $lastreplytime<br>$lang_textby $lastpost[1]";
	eval("\$lastpostrow = \"".template("forumdisplay_thread_lastpost")."\";");
	if($thread[icon] != "") {
		$thread[icon] = "<img src=\"$smdir/$thread[icon]\" />";
	} else {
		$thread[icon] = " ";
	}

	if($thread[replies] >= $hottopic) {
		$folder = "hot_folder.gif";
	} else {
		$folder = "folder.gif";
	}

	$lastvisit2 -= 540;
	if($thread[replies] >= $hottopic && $lastvisit2 < $dalast && !strstr($oldtopics, "|$thread[tid]|")) {
		$folder = "hot_red_folder.gif";
	}
	elseif($lastvisit2 < $dalast && !strstr($oldtopics, "|$thread[tid]|")) {
		$folder = "red_folder.gif";
	}
	else {
		$folder = $folder;
	}
	$lastvisit2 += 540;
	if($dotfolders == "on" && $thread[dotauthor] == $xmbuser && $xmbuser != "") {
		$folder = "dot_".$folder;
	}
	$folder = "<img src=\"$imgdir/$folder\">";

	if($thread[closed] == "yes") {
		$folder = "<img src=\"$imgdir/lock_folder.gif\">";
	}
	$thread[subject] = stripslashes($thread[subject]);

	$authorlink = "<a href=\"member.php?action=viewpro&member=".rawurlencode($thread[author])."\">$thread[author]</a>";

	if(!$ppp || $ppp == '') {
		$ppp = $postperpage;
	}

	$postsnum = $thread[replies] + 1;
	if($postsnum  > $ppp) {
		$posts = $postsnum;
		$topicpages = $posts / $ppp;
		$topicpages = ceil($topicpages);
		for ($i = 1; $i <= $topicpages; $i++) {
			$pagelinks .= " <a href=\"viewthread.php?tid=$thread[tid]&page=$i\">$i</a> ";
			if($i == 3) {
				$i = $topicpages + 1;
			}
		}
		if($topicpages > 3) {
			$pagelinks .= " .. <a href=\"viewthread.php?tid=$thread[tid]&page=$topicpages\">$topicpages </a>";
		}
		$multipage2 = "(<small>Pages: $pagelinks</small>)";
		$pagelinks = "";
	} else {
		$multipage2 = "";
	}
	$moved = explode("|", $thread[closed]);
	if($moved[0] == "moved") {
		$prefix = "$lang_moved ";
		$thread[tid] = $moved[1];
		$thread[replies] = "-";
		$thread[views] = "-";
		$folder = "<img src=\"$imgdir/lock_folder.gif\" alt=\"Closed Topic\" />";
	}
	if($thread[pollopts] != "") {
		$prefix = "$lang_poll ";
	}
	if($thread[topped] == 1) {
		$prefix = "$lang_toppedprefix ";
	}

	eval("\$threadlist .= \"".template("forumdisplay_thread")."\";");
	$prefix = "";
	$topicsnum++;
}
if($notexist) {
	eval("\$threadlist = \"".template("forumdisplay_invalidforum")."\";");
}

if($topicsnum == 0 && !$notexist) {
	eval("\$threadlist = \"".template("forumdisplay_nothreads")."\";");
}

if($cusdate == "86400") {
	$check1 = "selected=\"selected\"";
} elseif($cusdate == "432000") {
	$check5 = "selected=\"selected\"";
} elseif($cusdate == "1296000") {
	$check15 = "selected=\"selected\"";
} elseif($cusdate == "2592000") {
	$check30 = "selected=\"selected\"";
} elseif($cusdate == "5184000") {
	$check60 = "selected=\"selected\"";
} elseif($cusdate == "8640000") {
	$check100 = "selected=\"selected\"";
} elseif($cusdate == "31536000") {
	$checkyear = "selected=\"selected\"";
} elseif($cusdate == "0" || $cusdate == "") {
	$checkall = "selected=\"selected\"";
}
// Do Multipaging
	if(!$tpp || $tpp == '') {
		$tpp = $topicperpage;
	}
	if($page) {
		$start_limit = ($page-1) *$tpp;
	} else {
		$start_limit = 0;
		$page = 1;
	}
	if($cusdate != 0) {
		$cusdate = time() - $cusdate;
		$cusdate = "AND (substring_index(lastpost, '|',1)+1) >= '$cusdate'";
	} elseif($cusdate == 0) {
		$cusdate = "";
	}
	if(!$ascdesc) {
		$ascdesc = "DESC";
	} 
	$querytop = $db->query("SELECT t.*, (substring_index(lastpost, '|',1)+1) lastpostd FROM $table_threads t WHERE t.fid='$fid' $cusdate ORDER BY topped $ascdesc,lastpostd $ascdesc LIMIT $start_limit, $tpp");
	$query = $db->query("SELECT count(tid) FROM $table_threads WHERE fid='$fid'");
	$topicsnum = $db->result($query, 0);
	$mpurl = "forumdisplay.php?fid=$fid";
	$multipage = multi($topicsnum, $tpp, $page, $mpurl);


eval("\$forumdisplay = \"".template("forumdisplay")."\";");
echo $forumdisplay;

$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>
