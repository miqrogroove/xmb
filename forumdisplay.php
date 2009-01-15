<?
require "./header.php";
loadtemplates('header,footer,forumdisplay_password,forumdisplay_thread,forumdisplay_invalidforum,forumdisplay_nothreads,forumdisplay');

$query = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
$forum = $db->fetch_array($query);

if($forum[type] != "forum" && $forum[type] != "sub") {
$notexist = $lang_textnoforum;
}

if($forum[type] == "forum") {
$navigation .= "> $forum[name]";
} else {
$query = $db->query("SELECT name, fid FROM $table_forums WHERE fid='$forum[fup]'");
$fup = $db->fetch_array($query);
$navigation .= "> <a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> > $forum[name]";
}

// Start Forum Password Verify (by surfi)
if($forum[password] != "" && $action == "pwverify") {
if($pw != $forum[password]) {
eval("\$header = \"".template("header")."\";");
echo $header;
echo "$lang_invalidforumpw";
exit;
} else {
setcookie("fidpw$fid", $pw, ".time() + (86400*30).", $cookiepath, $cookiedomain);
header("Location: forumdisplay.php?fid=$fid");
}
}
eval("\$header = \"".template("header")."\";");
echo $header;

$query = $db->query("SELECT name FROM $table_forums WHERE type='sub' AND fup='$fid' AND status='on'");
$sub = $db->fetch_array($query);

if($sub[name] != "") {

$fulist = $forum[userlist];
$querys = $db->query("SELECT * FROM $table_forums WHERE type='sub' AND fup='$fid'");
while($sub = $db->fetch_array($querys)) {
$forumlist .= forum($sub, "forumdisplay_subforum");
}
$forum[userlist] = $fulist;
eval("\$subforums = \"".template("forumdisplay_subforums")."\";");
}
if($notexist != $lang_textnoforum) {
$newtopicimg = "$imgdir/newtopic.gif";
$newpollimg = "$imgdir/poll.gif";
if(file_exists($newtopicimg)) {
$newtopiclink = "<a href=\"post.php?action=newthread&fid=$fid\"><img src=\"$newtopicimg\" border=\"0\"></a>";
} else {
$newtopiclink = "<a href=\"post.php?action=newthread&fid=$fid\">$lang_textnewtopic</a>";
}

if($forum[pollstatus] != "off") {
if(file_exists($newpollimg)) {
$newpolllink = "   <a href=\"post.php?action=newthread&fid=$fid&poll=yes\"><img src=\"$newpollimg\" border=\"0\"></a>";
} else {
$newpolllink = "   <a href=\"post.php?action=newthread&fid=$fid&poll=yes\">$lang_textnewpoll</a>";
}
}

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

$querytop = $db->query("SELECT t.*, (substring_index(lastpost, '|',1)+1) lastpostd FROM $table_threads t WHERE t.fid='$fid' $cusdate ORDER BY topped $ascdesc,lastpostd $ascdesc LIMIT $start_limit, $tpp");

$query = $db->query("SELECT count(tid) FROM $table_threads WHERE fid='$fid'");
$topicsnum = $db->result($query, 0);

$mpurl = "forumdisplay.php?fid=$fid";
$multipage = multi($topicsnum, $tpp, $page, $mpurl);

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

while($thread = $db->fetch_array($querytop)) {
$lastpost = explode("|", $thread[lastpost]);
$dalast = $lastpost[0];

$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";

$lastreplydate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600));
$lastreplytime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600));
$lastpost = "$lang_lastreply1 $lastreplydate $lang_textat $lastreplytime<br />$lang_textby $lastpost[1]";

if($thread[icon] != "") {
$thread[icon] = "<img src=\"$smdir/$thread[icon]\" />";
} else {
$thread[icon] = " ";
}

if($thread[replies] >= $hottopic) {
$folder = "<img src=\"$imgdir/hot_folder.gif\" alt=\"Hot Topic\" />";
} else {
$folder = "<img src=\"$imgdir/folder.gif\" alt=\"Topic\" />";
}

$lastvisit2 -= 540;
if($thread[replies] >= $hottopic && $lastvisit2 < $dalast && !strstr($oldtopics, "|$thread[tid]|")) {
$folder = "<img src=\"$imgdir/hot_red_folder.gif\">";
}
elseif($lastvisit2 < $dalast && !strstr($oldtopics, "|$thread[tid]|")) {
$folder = "<img src=\"$imgdir/red_folder.gif\">";
}
else {
$folder = $folder;
}
$lastvisit2 += 540;

if($thread[closed] == "yes") {
$folder = "<img src=\"$imgdir/lock_folder.gif\" alt=\"Closed Topic\" />";
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

eval("\$forumdisplay = \"".template("forumdisplay")."\";");
echo $forumdisplay;

$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>
