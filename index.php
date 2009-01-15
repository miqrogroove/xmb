<?
require "./header.php";
loadtemplates('header,footer,index_whosonline,index_category,index_forum,index');
if($gid) {
$whosonlinestatus = "off";
$query = $db->query("SELECT name FROM $table_forums WHERE fid='$gid' AND type='group'");
$cat = $db->fetch_array($query);
$navigation ="&gt; $cat[name]";
}

eval("\$header = \"".template("header")."\";");
echo $header;
if(!$gid) {

// Start Whos Online and Stats
$query = $db->query("SELECT username FROM $table_members ORDER BY regdate DESC");
$lastmem = $db->fetch_array($query);
$lastmember = $lastmem[username];
$members = $db->num_rows($query);

$query = $db->query("SELECT COUNT(*) FROM $table_threads");
$threads = $db->result($query, 0);

$query = $db->query("SELECT COUNT(*) FROM $table_posts");
$posts = $db->result($query, 0);

$memhtml = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastmember)."\"><b>$lastmember</b></a>.";
eval($lang_evalindexstats);

if($members == "0") {
$memhtml = "<b>$lang_textnoone</b>";
}

if($whosonlinestatus == "on") {
$time = time();
$newtime = $time - 600;
$membercount = 0;
$query = $db->query("SELECT * FROM $table_whosonline ORDER BY username");
while($online = $db->fetch_array($query)) {
switch($online[username]) {
case xguest123:
$guestcount++;
break;

default:
$member[$membercount] = $online;
$membercount++;
break;
}
}

if(!$guestcount) {
$guestcount = "0";
}
if(!$membercount) {
$membercount = "0";
}
$onlinenum = $guestcount + $membercount;
eval($lang_whosoneval);
$memonmsg = "<span class=\"smalltxt\">$lang_whosonmsg</span>";

$memtally = "";
$num = 1;
for($mnum=0; $mnum<$membercount; $mnum++) {
$online = $member[$mnum];
if($num < $membercount) {
$memtally .= "<a href=\"member.php?action=viewpro&member=".rawurlencode($online[username])."\">$online[username]</a>, ";
} else {
$memtally .= "<a href=\"member.php?action=viewpro&member=".rawurlencode($online[username])."\">$online[username]</a>";
}
$num++;
}

if($memtally == "") {
$memtally = "&nbsp;";
}

eval("\$whosonline = \"".template("index_whosonline")."\";");
}
// End Whosonline and Stats
// Start Getting Forums and Groups
$query = $db->query("SELECT * FROM $table_forums WHERE type='forum' AND status='on' AND fup='' ORDER BY displayorder");
while($forum = $db->fetch_array($query)) {
$forumlist .= forum($forum, "index_forum");
}

$queryg = $db->query("SELECT * FROM $table_forums WHERE type='group' AND status='on' ORDER BY displayorder");
} else {
$queryg = $db->query("SELECT * FROM $table_forums WHERE type='group' AND fid='$gid' AND status='on' ORDER BY displayorder");
}

while($group = $db->fetch_array($queryg)) {
eval("\$forumlist .= \"".template("index_category")."\";");

if($catsonly != "on" || $gid) {
$query = $db->query("SELECT * FROM $table_forums WHERE type='forum' AND status='on' AND fup='$group[fid]' ORDER BY displayorder");
while($forum = $db->fetch_array($query)) {
$forumlist .= forum($forum, "index_forum");
}
}
}

eval("\$index = \"".template("index")."\";");
echo $index;

$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>
