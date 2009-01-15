<?
/*

XMB 1.6 v2c Magic Lantern
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/
require "./header.php";
require "./xmb.php";
loadtemplates('header,footer');

eval("\$header = \"".template("header")."\";");
echo $header;

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

$mapercent = $membersact*100/$members;
$mapercent  = number_format($mapercent, 2);
$mapercent .= "%";

$query = $db->query("SELECT views, tid, subject FROM $table_threads $addon ORDER BY views DESC LIMIT 0, 5");
while($views = $db->fetch_array($query)) {
$views_subject = stripslashes($views[subject]); $viewmost .= "<a href=\"viewthread.php?tid=$views[tid]\">$views_subject</a>($views[views] $lang[viewsl])<br />";

}

$query = $db->query("SELECT replies, tid, subject FROM $table_threads $addon ORDER BY replies DESC LIMIT 0, 5");
while($reply = $db->fetch_array($query)) {
$reply_subject = stripslashes($reply[subject]); $replymost .= "<a href=\"viewthread.php?tid=$reply[tid]\">$reply_subject</a>($reply[replies] $lang[repliesl])<br />";

}

$query = $db->query("SELECT lastpost, tid, subject FROM $table_threads $addon ORDER BY lastpost DESC LIMIT 0, 5 ");
while($last = $db->fetch_array($query)) {
$lpdate = date("$dateformat", $last[lastpost] + ($timeoffset * 3600));
$lptime = date("$timecode", $last[lastpost] + ($timeoffset * 3600));
$thislast = "$lang[lpoststats] $lang[lastreply1] $lpdate $lang[textat] $lptime";
$last_subject = stripslashes($last[subject]); $latest .= "<a href=\"viewthread.php?tid=$last[tid]\">$last_subject</a>($thislast)<br/>";

}

$query = $db->query("SELECT posts, threads, fid, name FROM $table_forums ORDER BY posts DESC LIMIT 0, 1");
$pop = $db->fetch_array($query);
$popforum = "<a href=\"forumdisplay.php?fid=$pop[fid]\">$pop[name]</a>";
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
}else{
$membesthtml = "<a href=\"member.php?action=viewpro&member=".rawurlencode($bestmember)."\"><b>$bestmember</b></a>";
$bestmemberpost = $info['Total'];
}
eval($lang_evalbestmember);

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
echo $stats;
}

$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>
