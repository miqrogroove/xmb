<?
/*

XMB 1.8 Partagium
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/

require "./header.php";

loadtemplates('header,today,today2,footer');
$navigation .= "&raquo; Stats";

eval("\$header = \"".template("header")."\";");
echo $header;

if($status != "Super Administrator" && $status != "Administrator" && $status != "Super Moderator" && $status != "Moderator"){
	$restrict = "WHERE f.private != '2' AND f.private !='3' AND f.fid = t.fid AND userlist = ''";
}elseIf($status != "Administrator" && $status != "Super Administrator")	{
	$restrict = "WHERE f.private != '2' AND f.fid = t.fid AND userlist = ''";
}else{
	$restrict = "WHERE f.fid = t.fid";
}

if($action == "view") {

$query = $db->query("SELECT COUNT(*) FROM $table_threads");
$threads = $db->result($query, 0);

$query = $db->query("SELECT COUNT(*) FROM $table_posts");
$posts = $db->result($query, 0);

$query = $db->query("SELECT COUNT(*) FROM $table_forums WHERE type='forum'");
$forums = $db->result($query, 0);

$query = $db->query("SELECT COUNT(*) FROM $table_forums WHERE type='forum' AND status='on'");
$forumsa = $db->result($query, 0);

$query = $db->query("SELECT COUNT(*) FROM $table_members");
$members = $db->result($query, 0);

$query = $db->query("SELECT COUNT(*) FROM $table_members WHERE postnum!='0'");
$membersact = $db->result($query, 0);

if($posts == 0 || $members == 0 || $threads == 0 || $forums == 0){
	$warning = $lang_stats_incomplete;
	
	eval("\$stats = \"".template("feature_statistics")."\";");
	$stats = stripslashes($stats);
	echo $stats;


	end_time();

	eval("\$footer = \"".template("footer")."\";");
	echo $footer;

	exit();
}

$mapercent = $membersact*100/$members;
$mapercent  = number_format($mapercent, 2);
$mapercent .= "%";

$query = $db->query("SELECT t.views, t.tid, t.subject FROM $table_threads t, $table_forums f $addon $restrict ORDER BY views DESC LIMIT 0, 5");
while($views = $db->fetch_array($query)) {
$views_subject = stripslashes($views[subject]); $viewmost .= "<a href=\"viewthread.php?tid=$views[tid]\">$views_subject</a> ($views[views]$lang[viewsl])<br />";

}

$query = $db->query("SELECT t.replies, t.tid, t.subject FROM $table_threads t, $table_forums f $addon $restrict ORDER BY replies DESC LIMIT 0, 5");
while($reply = $db->fetch_array($query)) {
$reply_subject = stripslashes($reply[subject]); $replymost .= "<a href=\"viewthread.php?tid=$reply[tid]\">$reply_subject</a> ($reply[replies]$lang[repliesl])<br />";

}

$query = $db->query("SELECT t.lastpost, t.tid, t.subject FROM $table_threads t, $table_forums f $addon $restrict ORDER BY lastpost DESC LIMIT 0, 5 ");
while($last = $db->fetch_array($query)) {
$lpdate = date("$dateformat", $last[lastpost] + ($timeoffset * 3600));
$lptime = date("$timecode", $last[lastpost] + ($timeoffset * 3600));
$thislast = "$lang[lpoststats]$lang[lastreply1]$lpdate$lang[textat] $lptime";
$last_subject = stripslashes($last[subject]); $latest .= "<a href=\"viewthread.php?tid=$last[tid]\">$last_subject</a> ($thislast)<br/>";

}

$query = $db->query("SELECT f.posts, f.threads, f.fid, f.name FROM $table_forums f WHERE f.private != '2' AND f.private !='3' ORDER BY posts DESC LIMIT 0, 1");
$pop = $db->fetch_array($query);
$popforum = "<a href=\"forumdisplay.php?fid=$pop[fid]\"><b>$pop[name]</b></a>";
// $posts += $threads;

$mempost = 0;
$query = $db->query("SELECT postnum FROM $table_members");
while($mem= $db->fetch_array($query)) {
$mempost += $mem[postnum];
}
$mempost = $mempost / $members;
$mempost  = number_format($mempost, 2);

$forumpost = 0;
$query = $db->query("SELECT posts FROM $table_forums");
while($forum = $db->fetch_array($query)) {
$forumpost += $forum[posts];
}
$forumpost = $forumpost / $forums;
$forumpost  = number_format($forumpost, 2);

$threadreply = 0;
$query = $db->query("SELECT replies FROM $table_threads");
while($thread = $db->fetch_array($query)) {
$threadreply += $thread[replies];
}
$threadreply = $threadreply / $threads;
$threadreply  = number_format($threadreply, 2);

$query = $db->query("SELECT lastpost FROM $table_threads ORDER BY lastpost LIMIT 0, 1");
$postdays = $db->result($query, 0);
$postsday = $posts / ((time() - $postdays) / 86400);
$postsday  = number_format($postsday, 2);

$query = $db->query("SELECT regdate FROM $table_members ORDER BY regdate LIMIT 0, 1");
$memberdays = $db->result($query, 0);
$membersday = $members / ((time() - $memberdays) / 86400);
$membersday  = number_format($membersday, 2);

$timesearch = time() - 86400;
$query = $db->query("SELECT author, Count(*) AS Total FROM $table_posts WHERE dateline >= '$timesearch' GROUP BY author ORDER BY Total DESC");
$info = $db->fetch_array($query);
$bestmember = $info['author'];
if($bestmember == '') {
	$bestmember = 'Nobody';
	$bestmemberpost = 'No';
	$eval =& $lang_evalbestmember;
}else{
	if($info['Total'] != 0){
		$membesthtml = "<a href=\"member.php?action=viewpro&member=".rawurlencode($bestmember)."\"><b>$bestmember</b></a>";
		$bestmemberpost = $info['Total'];
		$eval =& $lang_evalbestmember;
	}else{
		$eval =& $lang_evalnobestmember;
	}
}
eval($eval);

eval($lang_evalstats1);
eval($lang_evalstats2);
eval($lang_evalstats3);
eval($lang_evalstats4);
eval($lang_evalstats5);
eval($lang_evalstats6);
eval($lang_evalstats7);
eval($lang_evalstats8);
eval($lang_evalstats9);
eval($lang_evalstats10);
eval($lang_evalstats11);
eval($lang_evalstats12);
eval($lang_evalstats13);
eval($lang_evalstats14);
eval($lang_evalstats15);
eval($lang_evalbestmember);

eval("\$stats = \"".template("feature_statistics")."\";");
$stats = stripslashes($stats);
echo $stats;
}

end_time();

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>
