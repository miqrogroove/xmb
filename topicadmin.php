<?
require "./header.php";
loadtemplates('header,footer,topicadmin_delete,topicadmin_openclose,topicadmin_move,topicadmin_topuntop,topicadmin_bump,topicadmin_split_row,topicadmin_split,topicadmin_merge,topicadmin_report');

if($tid && $fid) {
	$query = $db->query("SELECT * FROM $table_threads WHERE tid='$tid'");
	$thread = $db->fetch_array($query);
	$threadname = $thread[subject];
	$threadname = stripslashes($threadname);
    $fid = $thread[fid];
}

$query = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
$forums = $db->fetch_array($query);

/*
if($tid && $fid) {
$query = $db->query("SELECT * FROM $table_threads WHERE fid='$fid' AND tid='$tid'");
$thread = $db->fetch_array($query);
$threadname = $thread[subject];
}
*/

if($forums[type] == "forum") {
$postaction = "<a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &raquo; <a href=\"viewthread.php?tid=$tid\">$threadname</a> &raquo; ";
} else {
$query = $db->query("SELECT name, fid FROM $table_forums WHERE fid='$forums[fup]'");
$fup = $db->fetch_array($query);
$postaction = "<a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> &gt; <a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &raquo; <a href=\"viewthread.php?tid=$tid\">$threadname</a> &raquo; ";
}

if($action == "delete") {
$postaction .= $lang_textdeletethread;
} elseif($action == "top") {
$postaction .= $lang_texttopthread;
} elseif($action == "close") {
$postaction .= $lang_textclosethread;
} elseif($action == "move") {
$postaction .= $lang_textmovethread;
} elseif($action == "getip") {
$postaction .= $lang_textgetip;
} elseif($action == "bump") {
$postaction .= $lang_textbumpthread;
} elseif($action == "report") {
$postaction .= $lang_textreportpost;
} elseif($action == "split") {
$postaction .= $lang_textsplitthread;
} elseif($action == "merge") {
$postaction .= $lang_textmergethread;
} elseif($action == "votepoll") {
$postaction .= $lang_textvote;
}


$navigation = "&raquo; $postaction";
eval("\$header = \"".template("header")."\";");
echo $header;
if($forums[private] == "3" && $status != "Administrator" && $status != "Super Moderator" && $status != "Moderator") {
echo "<center><span class=\"mediumtxt \">$lang_privforummsg</span></center>";
exit;
}
if($status != "Administrator" && $status != "Super Moderator" && $status != "Moderator" && $action != "votepoll" && $action != "report" || !$xmbuser || !$xmbpw) {
echo "<center><span class=\"mediumtxt \">$lang_notpermitted</span></center>";
exit;
}
if($action == "delete") {
if(!$deletesubmit) {
eval("\$delete = \"".template("topicadmin_delete")."\";");
echo $delete;
}

if($deletesubmit) {
$query = $db->query("SELECT username, password, status FROM $table_members WHERE username='$xmbuser'");
$member = $db->fetch_array($query);
$status = $member[status];

if(!$member[username]) {
echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
exit;
}

if($xmbpw != $member[password]) {
echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
exit;
}
$status1 = modcheck($status, $xmbuser, $fid);

if($status == "Super Moderator") {
$status1 = "Moderator";
}

if($status != "Administrator" && $status1 != "Moderator") {
echo "<center><span class=\"mediumtxt \">$lang_textnoaction</span></center>";
exit;
}

$query = $db->query("SELECT author FROM $table_posts WHERE tid='$tid'");
while($result = $db->fetch_array($query)) {
$db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$result[author]'");
}
$db->query("DELETE FROM $table_threads WHERE tid='$tid'");
$db->query("DELETE FROM $table_posts WHERE tid='$tid'");
$db->query("DELETE FROM $table_attachments WHERE tid='$tid'");
updateforumcount($fid);



echo "<center><span class=\"mediumtxt \">$lang_deletethreadmsg</span></center>";
?>
<script>
function redirect() {
window.location.replace("forumdisplay.php?fid=<?=$fid?>");
}
setTimeout("redirect();", 1250);
</script>
<?
}
}

if($action == "close") {

$query = $db->query("SELECT closed FROM $table_threads WHERE fid='$fid' AND tid='$tid'");
$closed = $db->result($query, 0);

if($closed == "yes") {
$lang_textclosethread = $lang_textopenthread;
}
elseif($closed == "") {
$lang_textclosethread = $lang_textclosethread;
}

if(!$closesubmit) {
eval("\$close = \"".template("topicadmin_openclose")."\";");
echo $close;
}

if($closesubmit) {
$query = $db->query("SELECT username, password, status FROM $table_members WHERE username='$xmbuser'");
$member = $db->fetch_array($query);
$status = $member[status];

if(!$member[username]) {
echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
exit;
}

if($xmbpw != $member[password]) {
echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
exit;
}

$status1 = modcheck($status, $xmbuser, $fid);

if($status == "Super Moderator") {
$status1 = "Moderator";
}

if($status != "Administrator" && $status1 != "Moderator") {
echo "<center><span class=\"mediumtxt \">$lang_textnoaction</center></span>";
exit;
}

if($closed == "yes") {
$db->query("UPDATE $table_threads SET closed='' WHERE tid='$tid' AND fid='$fid'");
}
elseif($closed == "") {
$db->query("UPDATE $table_threads SET closed='yes' WHERE tid='$tid' AND fid='$fid'");
}

echo "<center><span class=\"mediumtxt \">$lang_closethreadmsg</span></center>";
?>
<script>
function redirect() {
window.location.replace("forumdisplay.php?fid=<?=$fid?>");
}
setTimeout("redirect();", 1250);
</script>
<?
}
}


if($action == "move") {
if(!$movesubmit) {

$forumselect = "<select name=\"moveto\">\n";
$queryfor = $db->query("SELECT * FROM $table_forums WHERE fup='' AND type='forum' ORDER BY displayorder");
while($forum = $db->fetch_array($queryfor)) {
$forumselect .= "<option value=\"$forum[fid]\"> &nbsp; &gt; $forum[name]</option>";

$querysub = $db->query("SELECT * FROM $table_forums WHERE fup='$forum[fid]' AND type='sub' ORDER BY displayorder");
while($sub = $db->fetch_array($querysub)) {
$forumselect .= "<option value=\"$sub[fid]\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &gt; $sub[name]</option>";
}
$forumselect .= "<option value=\"\"> </option>";
}

$querygrp = $db->query("SELECT * FROM $table_forums WHERE type='group' ORDER BY displayorder");
while($group = $db->fetch_array($querygrp)) {
$forumselect .= "<option value=\"\">$group[name]</option>";
$forumselect .= "<option value=\"\">--------------------</option>";

$queryfor = $db->query("SELECT * FROM $table_forums WHERE fup='$group[fid]' AND type='forum' ORDER BY displayorder");
while($forum = $db->fetch_array($queryfor)) {
$forumselect .= "<option value=\"$forum[fid]\"> &nbsp; &gt; $forum[name]</option>";

$querysub = $db->query("SELECT * FROM $table_forums WHERE fup='$forum[fid]' AND type='sub' ORDER BY displayorder");
while($sub = $db->fetch_array($querysub)) {
$forumselect .= "<option value=\"$sub[fid]\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &gt; $sub[name]</option>";
}
}
$forumselect .= "<option value=\"\"> </option>";
}
$forumselect .= "</select>";
eval("\$move = \"".template("topicadmin_move")."\";");
echo $move;
}

if($movesubmit) {
$query = $db->query("SELECT username, password, status FROM $table_members WHERE username='$xmbuser'");
$member = $db->fetch_array($query);
$status = $member[status];

if(!$member[username]) {
echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
exit;
}

if($xmbpw != $member[password]) {
echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
exit;
}

$status1 = modcheck($status, $xmbuser, $fid);

if($status == "Super Moderator") {
$status1 = "Moderator";
}

if($status != "Administrator" && $status1 != "Moderator") {
echo "<center><span class=\"mediumtxt \">$lang_textnoaction</span></center>";
exit;
}
if($type == "normal") {

$db->query("UPDATE $table_threads SET fid='$moveto' WHERE tid='$tid' AND fid='$fid'");
$db->query("UPDATE $table_posts SET fid='$moveto' WHERE tid='$tid' AND fid='$fid'");

} else {
$query = $db->query("SELECT * FROM $table_threads WHERE tid='$tid'");
$info = $db->fetch_array($query);
$db->query("INSERT INTO $table_threads VALUES ('', '$info[fid]', '$info[subject]', '', '$info[lastpost]', '-', '-', '$info[author]', 'moved|$info[tid]', '$info[topped]', '$info[pollopts]')");
$ntid = $db->insert_id();
$db->query("INSERT INTO $table_posts VALUES ('$info[fid]', '$ntid', '', '$info[author]', '$info[tid]', '$info[subject]', '', '', '', '', '', '', '')");

$db->query("UPDATE $table_threads SET fid='$moveto' WHERE tid='$tid' AND fid='$fid'");
$db->query("UPDATE $table_posts SET fid='$moveto' WHERE tid='$tid' AND fid='$fid'");
}
updateforumcount($fid);
updateforumcount($moveto);
updatethreadcount($tid);
echo "<center><span class=\"mediumtxt \">$lang_movethreadmsg</span></center>";
?>
<script>
function redirect() {
window.location.replace("forumdisplay.php?fid=<?=$fid?>");
}
setTimeout("redirect();", 1250);
</script>
<?
}
}

if($action == "top") {

$query = $db->query("SELECT topped FROM $table_threads WHERE fid='$fid' AND tid='$tid'");
$topped = $db->result($query, 0);

if($topped == "1") {
$lang_texttopthread = $lang_textuntopthread;
} elseif($topped == "0") {
$lang_texttopthread = $lang_texttopthread;
}

if(!$topsubmit) {
eval("\$top = \"".template("topicadmin_topuntop")."\";");
echo $top;

}
if($topsubmit) {
$query = $db->query("SELECT username, password, status FROM $table_members WHERE username='$xmbuser'");
$member = $db->fetch_array($query);
$status = $member[status];

if(!$member[username]) {
echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
exit;
}

if($xmbpw != $member[password]) {
echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span><center>";
exit;
}

$status1 = modcheck($status, $xmbuser, $fid);

if($status == "Super Moderator") {
$status1 = "Moderator";
}

if($status != "Administrator" && $status1 != "Moderator") {
echo "<center><span class=\"mediumtxt \">$lang_textnoaction</span></center>";
exit;
}

if($topped == "1") {
$db->query("UPDATE $table_threads SET topped='0' WHERE tid='$tid' AND fid='$fid'");
}
elseif($topped == "0") {
$db->query("UPDATE $table_threads SET topped='1' WHERE tid='$tid' AND fid='$fid'");
}

echo "<center><span class=\"mediumtxt \">$lang_topthreadmsg</span></center>";
?>
<script>
function redirect() {
window.location.replace("forumdisplay.php?fid=<?=$fid?>");
}
setTimeout("redirect();", 1250);
</script>
<?
}
}

if($action == "getip") {

if(!$pid) {
$query = $db->query("SELECT * FROM $table_threads WHERE tid='$tid'");
}
else {
$query = $db->query("SELECT * FROM $table_posts WHERE pid='$pid' AND tid='$tid'");
}

$ipinfo = $db->fetch_array($query);

$query = $db->query("SELECT status FROM $table_members WHERE username='$xmbuser'");
$status = $db->fetch_array($query);
$status = $status[status];


$status1 = modcheck($status, $xmbuser, $fid);

if($status == "Super Moderator") {
$status1 = "Moderator";
}

if($status != "Administrator" && $status1 != "Moderator") {
echo "<center><span class=\"mediumtxt \">$lang_textnoip</span></center>";
}
else {

?>
<table cellspacing="0" cellpadding="0" border="0" width="60%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">
<form method="post" action="cp.php?action=ipban">
<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header" colspan="3"><?=$lang_textgetip?></td>
</tr>
<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang_textyesip?> <b><?=$ipinfo[useip]?></b>
<?

if($status == "Administrator") {

$ip = explode(".", $ipinfo[useip]);
$query = $db->query("SELECT * FROM $table_banned WHERE (ip1='$ip[0]' OR ip1='-1') AND (ip2='$ip[1]' OR ip2='-1') AND (ip3='$ip[2]' OR ip3='-1') AND (ip4='$ip[3]' OR ip4='-1')");
$result = $db->fetch_array($query);

if ($result) {
$buttontext = $lang_textunbanip;

for($i=1; $i<=4; ++$i) {
$j = "ip$i";
if ($result[$j] == -1) {
$result[$j] = "*";
$foundmask = 1;
}
}
if ($foundmask) {
$ipmask = "<b>$result[ip1].$result[ip2].$result[ip3].$result[ip4]</b>";
eval($lang_evalipmask);
echo $lang_bannedipmask;
}

else {
echo $lang_textbannedip;
}

echo "<input type=\"hidden\" name=\"delete$result[id]\" value=\"$result[id]\" />";
}

else {
$buttontext = $lang_textbanip;
for($i=1; $i<=4; ++$i) {
$j = $i - 1;
echo "<input type=\"hidden\" name=\"newip$i\" value=\"$ip[$j]\" />";
}

}
?>
</td>
<tr bgcolor="<?=$altbg1?>"><td class="tablerow">
<center><input type="submit" name="ipbansubmit" value="<?=$buttontext?>" /></center>

<?
}

echo "</td></tr></table></td></tr></table></form>";
}
}


if($action == "bump") {
if(!$bumpsubmit) {
eval("\$bump = \"".template("topicadmin_bump")."\";");
echo $bump;
}

if($bumpsubmit) {
$query = $db->query("SELECT username, password, status FROM $table_members WHERE username='$xmbuser'");
$member = $db->fetch_array($query);
$status = $member[status];

if(!$member[username]) {
echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
exit;
}

if($xmbpw != $member[password]) {
echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
exit;
}

$status1 = modcheck($status, $xmbuser, $fid);

if($status == "Super Moderator") {
$status1 = "Moderator";
}

if($status != "Administrator" && $status1 != "Moderator") {
echo "<center><span class=\"mediumtxt \">$lang_textnoaction</span></center>";
exit;
}

$db->query("UPDATE $table_threads SET lastpost='" . time() . "|$xmbuser' WHERE tid=$tid AND fid=$fid");
$db->query("UPDATE $table_forums SET lastpost='" . time() . "|$xmbuser' WHERE fid=$fid");

echo "<center><span class=\"mediumtxt \">$lang_bumpthreadmsg</span></center>";
?>
<script>
function redirect() {
window.location.replace("forumdisplay.php?fid=<?=$fid?>");
}
setTimeout("redirect();", 1250);
</script>
<?
}
}



if($action == "split") {
if(!$splitsubmit) {

$query = $db->query("SELECT replies FROM $table_threads WHERE tid='$tid'");
$replies = $db->result($query, 0);

if($replies == 0) {
echo "<center><span class=\"mediumtxt \">$lang_cantsplit</span></center>";
exit;
}

$query = $db->query("SELECT * FROM $table_posts WHERE tid='$tid' ORDER BY dateline");
while($post = $db->fetch_array($query)) {
$bbcodeoff = $post[bbcodeoff];
$smileyoff = $post[smileyoff];
$post[message] = stripslashes($post[message]);
$post[message] = postify($post[message], $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", $table_words, $table_forums, $table_smilies);
eval("\$posts .= \"".template("topicadmin_split_row")."\";");

}
eval("\$split = \"".template("topicadmin_split")."\";");
echo $split;

}

if($splitsubmit) {
$query = $db->query("SELECT username, password, status FROM $table_members WHERE username='$xmbuser'");
$member = $db->fetch_array($query);
$status = $member[status];

if(!$member[username]) {
echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
exit;
}

if($xmbpw != $member[password]) {
echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
exit;
}

$status1 = modcheck($status, $xmbuser, $fid);

if($status == "Super Moderator") {
$status1 = "Moderator";
}

if($status != "Administrator" && $status1 != "Moderator") {
echo "<center><span class=\"mediumtxt \">$lang_textnoaction</span></center>";
exit;
}

if($subject == "" || ereg("^ *$", $subject)) {
echo "<center><span class=\"mediumtxt \">$lang_textnosubject</span></center>";
exit;
}

$subject = addslashes($subject);
$query = $db->query("SELECT author, subject FROM $table_posts WHERE tid='$tid' ORDER BY dateline LIMIT 0,1");
$fpost = $db->fetch_array($query);
$thatime = time();
$db->query("INSERT INTO $table_threads VALUES ('', '$fid', '$subject', '', '$thatime|$fpost[author]', '0', '0', '$fpost[author]', '', '', '')");
$newtid = $db->insert_id();

$query = $db->query("SELECT * FROM $table_posts WHERE tid='$tid'");
while($post = $db->fetch_array($query)) {
$move = "move$post[pid]";
$move = "${$move}";

if($move != "") {
$thatime = time();
$db->query("UPDATE $table_posts SET tid='$newtid' WHERE pid='$move'");
$db->query("UPDATE $table_attachments SET tid='$newtid' WHERE pid='$move'");
$db->query("UPDATE $table_threads SET replies=replies+1, lastpost='$thatime|$post[author]' WHERE tid='$newtid'");
$db->query("UPDATE $table_threads SET replies=replies-1 WHERE tid='$tid'");

if(!$firstsubject) {
$db->query("UPDATE $table_posts SET subject='$subject' WHERE pid='$move'");
$firstsubject = "1";
}

if($fpost[subject] == $post[subject]) {
$db->query("UPDATE $table_threads SET replies=replies+1 WHERE tid='$tid'");
}
}
}
$db->query("UPDATE $table_threads SET replies=replies-1 WHERE tid='$newtid'");

echo "<center><span class=\"mediumtxt \">$lang_splitthreadmsg</span></center>";
?>
<script>
function redirect() {
window.location.replace("forumdisplay.php?fid=<?=$fid?>");
}
setTimeout("redirect();", 1250);
</script>
<?
}
}


if($action == "merge") {
if(!$mergesubmit) {
eval("\$merge = \"".template("topicadmin_merge")."\";");
echo $merge;
}

if($mergesubmit) {
$query = $db->query("SELECT username, password, status FROM $table_members WHERE username='$xmbuser'");
$member = $db->fetch_array($query);
$status = $member[status];

if(!$member[username]) {
echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
exit;
}

if($xmbpw != $member[password]) {
echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
exit;
}

$status1 = modcheck($status, $xmbuser, $fid);

if($status == "Super Moderator") {
$status1 = "Moderator";
}

if($status != "Administrator" && $status1 != "Moderator") {
echo "<center><span class=\"mediumtxt \">$lang_textnoaction</span></center>";
exit;
}

$query = $db->query("SELECT replies FROM $table_threads WHERE tid='$othertid'");
$replyadd = $db->result($query, 0);
$replyadd++;
$db->query("UPDATE $table_posts SET tid='$tid' WHERE tid='$othertid'");
$db->query("UPDATE $table_attachments SET tid='$tid' WHERE tid='$othertid'");
$db->query("DELETE FROM $table_threads WHERE tid='$othertid'");
$db->query("UPDATE $table_threads SET replies=replies+$replyadd WHERE tid='$tid'");

echo "<center><span class=\"mediumtxt \">$lang_mergethreadmsg</span></center>";
?>
<script>
function redirect() {
window.location.replace("forumdisplay.php?fid=<?=$fid?>");
}
setTimeout("redirect();", 1250);
</script>
<?
}
}


if($action == "report") {

if($reportpost == "off") {
echo "<span class=\"mediumtxt \">$lang_reportpostdisabled</span>";
exit;
}

if(!$reportsubmit) {
eval("\$report = \"".template("topicadmin_report")."\";");
echo $report;
}

if($reportsubmit) {
if($pid) {
$posturl = $boardurl . "viewthread.php?tid=$tid#pid$pid";
} else {
$posturl = $boardurl . "viewthread.php?tid=$tid";
}

$message = "$lang_reportmessage $posturl \n\n$lang_reason $reason";

$query = $db->query("SELECT moderator FROM $table_forums WHERE fid='$fid'");
$forum = $db->fetch_array($query);

$mods = explode(", ", $forum[moderator]);
for($num = 0; $num < 10; $num++) {
if($mods[$num] != "") {
$db->query("INSERT INTO $table_u2u VALUES('', '$mods[$num]', '$xmbuser', '" . time() . "', '$lang_reportsubject', '$message', 'inbox')");
}
}

$query = $db->query("SELECT username FROM $table_members WHERE status='$lang_textadmin'");
while($member = $db->fetch_array($query)) {
if($member[username] != "") {
$db->query("INSERT INTO $table_u2u VALUES('', '$member[username]', '$xmbuser', '" . time() . "', '$lang_reportsubject', '$message', 'inbox')");
}
}



echo "<center><span class=\"mediumtxt \">$lang_reportmsg</span></center>";
?>
<script>
function redirect() {
window.location.replace("viewthread.php?tid=<?=$tid?>");
}
setTimeout("redirect();", 1250);
</script>
<?
}
}


if($action == "votepoll") {
$currpoll = $thread[pollopts];
$pollops = explode("#|#", $currpoll);
for($pnum = 0; $pnum < 10; $pnum++) {
if(!strstr($pollops[$pnum], "||~|~||")) {
$oldips .= $pollops[$pnum];
}

$thispoll = explode("||~|~|| ", $pollops[$pnum]);
if($pnum == $postopnum) {
$thispoll[1]++;
}

if($pollops[$pnum] != "" && !ereg("[0-9]{1,3}\.", $pollops[$pnum])) {
$newvotecol .= "$thispoll[0]||~|~|| $thispoll[1]#|#";
$thispoll = "";
}
}
if($onlineip != "unknown") {
$newvotecol .= "$oldips $onlineip";
}

$db->query("UPDATE $table_threads SET pollopts='$newvotecol' WHERE fid='$fid' AND tid='$tid'");

echo "<center><span class=\"mediumtxt \">$lang_votemsg</span></center>";
?>
<script>
function redirect() {
window.location.replace("viewthread.php?tid=<?=$tid?>");
}
setTimeout("redirect();", 1250);
</script>
<?
}

$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>