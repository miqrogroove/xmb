<?
require "./header.php";

loadtemplates('header,footer,post_notloggedin,post_loggedin,post_preview,post_attachmentbox,post_newthread,post_reply_review_toolong,post_reply_review_post,post_reply,post_edit,functions_smilieinsert,functions_smilieinsert_smilie,functions_bbcodeinsert');

$query = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
$forums = $db->fetch_array($query);

if($forums[type] != "forum" && $forums[type] != "sub" && $forums[fid] != $fid) {
$posterror = $lang_textnoforum;
}

if($tid && $fid) {
$query = $db->query("SELECT subject FROM $table_threads WHERE fid='$fid' AND tid='$tid'");
$threadname = $db->result($query,0);
$threadname = stripslashes($threadname);
}

if($forums[type] == "forum") {
$postaction = "<a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &gt; <a href=\"viewthread.php?tid=$tid\">$threadname</a> &gt; ";
} else {
$query = $db->query("SELECT name, fid FROM $table_forums WHERE fid='$forums[fup]'");
$fup = $db->fetch_array($query);
$postaction = "<a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> &gt; <a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &gt; <a href=\"viewthread.php?tid=$tid\">$threadname</a> &gt; ";
}

if($action != "edit" && $tid) {
$postaction .= "$lang_textpostreply";
}
elseif($action == "edit") {
$postaction .= "$lang_texteditpost";
}

if($action != "edit" && !$tid) {
if($forums[type] == "forum") {
$postaction = "<a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &gt; ";
} else {
$query = $db->query("SELECT name, fid FROM $table_forums WHERE fid='$forums[fup]'");
$fup = $db->fetch_array($query);
$postaction = "<a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> &gt; <a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &gt; ";
}
}

if($action != "edit" && !$tid) {
$postaction .= "$lang_textpostnew";
}

// Get bb code and smilie inserters ready
$bbcodeinsert = bbcodeinsert();
$smilieinsert = smilieinsert();

if($forums[attachstatus] != "off") {
eval("\$attachfile = \"".template("post_attachmentbox")."\";");
}

if(!$xmbuser || !$xmbpw) {
eval("\$loggedin = \"".template("post_notloggedin")."\";");
} else {
eval("\$loggedin = \"".template("post_loggedin")."\";");
}
$navigation = "&gt; $postaction";
eval("\$header = \"".template("header")."\";");
echo $header;

if($status == "Banned") {
echo $lang_bannedmessage;
}



$listed_icons = 0;
$querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='picon'");
while($smilie = $db->fetch_array($querysmilie)) {
if($posticon == $smilie[url]) {
$icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\"checked=\"checked\" /><img src=\"$smdir/$smilie[url]\" />";
} else {
$icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$smdir/$smilie[url]\" />";
}
$listed_icons += 1;
if($listed_icons == 8) {
$icons .= "<br />";
$listed_icons = 0;
}
}


if($forums[allowimgcode] == "yes") {
$allowimgcode = "$lang_texton";
} else {
$allowimgcode = "$lang_textoff";
}

if($forums[allowhtml] == "yes") {
$allowhtml = "$lang_texton";
} else {
$allowhtml = "$lang_textoff";
}

if($forums[allowsmilies] == "yes") {
$allowsmilies = "$lang_texton";
} else {
$allowsmilies = "$lang_textoff";
}

if($forums[allowbbcode] == "yes") {
$allowbbcode = "$lang_texton";
} else {
$allowbbcode = "$lang_textoff";
}

$pperm = explode("|", $forums[postperm]);

if($pperm[0] == "1") {
$whopost1 = $lang_whocanpost11;
} elseif($pperm[0] == "2") {
$whopost1 = $lang_whocanpost12;
} elseif($pperm[0] == "3") {
$whopost1 = $lang_whocanpost13;
} elseif($pperm[0] == "4") {
$whopost1 = $lang_whocanpost14;
}

if($pperm[1] == "1") {
$whopost2 = $lang_whocanpost21;
} elseif($pperm[1] == "2") {
$whopost2 = $lang_whocanpost22;
} elseif($pperm[1] == "3") {
$whopost2 = $lang_whocanpost23;
} elseif($pperm[1] == "4") {
$whopost2 = $lang_whocanpost24;
}

if($pperm[0] == "4" && $pperm[1] == "4") {
$whopost3 = $lang_whocanpost32;
}

if($xmbuser && $xmbuser != '') {
$query = $db->query("SELECT sig FROM $table_members WHERE username='$xmbuser'");
$this = $db->fetch_array($query);
$sig = $this[sig];
if($sig != "") {
$usesigcheck = "checked=\"checked\"";
}
}

if(($forums[private] == "2" || $subf[private] == "2") && $status != "Administrator") {
echo "<font class=\"tablerow\">$lang_privforummsg</font>";
exit;
} elseif(($forums[private] == "3" || $subf[private] == "3") && $status != "Administrator" && $status != "Moderator" && $status != "Super Moderator") {
echo "<font class=\"tablerow\">$lang_privforummsg</font>";
exit;
} elseif($forums[private] == "4" || $subf[private] == "4") {
echo "<font class=\"tablerow\">$lang_privforummsg</font>";
exit;
}

if($posterror) {
echo "<div class=\"tablerow\">$posterror</div>";
exit;
}
// Start forum password check
if($forums[password] != $HTTP_COOKIE_VARS["fidpw$fid"] && $forums[password] != "") {
eval("\$pwform = \"".template("forumdisplay_password")."\";");
echo $pwform;
exit;
}

if($previewpost) {
$currtime = time();
$date = gmdate("n/j/y",$currtime + ($timeoffset * 3600));
$time = gmdate("H:i",$currtime + ($timeoffset * 3600));
$poston = "$lang_textposton $date $lang_textat $time";

$subject = stripslashes($subject);
$message = stripslashes($message);
$message1 = postify($message, $smileyoff, $bbcodeoff, $forums[allowsmilies], $forums[allowhtml], $forums[allowbbcode], $forums[allowimgcode]);

if($smileyoff == "yes") {
$smileoffcheck = "checked=\"checked\"";
}

if($usesig == "yes") {
$usesigcheck = "checked=\"checked\"";
}

if($bbcodeoff == "yes") {
$codeoffcheck = "checked=\"checked\"";
}

if($subject != "") {
$dissubject = stripslashes($subject);
} else {
$dissubject = "";
}
eval("\$preview = \"".template("post_preview")."\";");
echo $preview;
}

if($action == "newthread") {
$priv = privfcheck($private, $userlist);
if(!$topicsubmit) {
if($status == "Administrator" || $status == "Super Moderator" || $status == "Moderator") {
$topoption = "<input type=\"checkbox\" name=\"toptopic\" value=\"yes\" />$lang_topmsgques<br />";
}

if($poll == "yes" && $forums[pollstatus] != "off") {
eval("\$postform = \"".template("post_newpoll")."\";");
echo $postform;
} else {
eval("\$postform = \"".template("post_newthread")."\";");
echo $postform;
}
}
if($topicsubmit) {
if(!$xmbuser && !$xmbpw) {
$password = md5($password);
}
$query = $db->query("SELECT username, password, status FROM $table_members WHERE username='$username'");
$member = $db->fetch_array($query);

if(!$member[username]) {
echo "<span class=\"mediumtxt \">$lang_badname</span>";
exit;
}

$username = $member[username];

if($password != $member[password]) {
echo "<span class=\"mediumtxt \">$lang_textpwincorrect</span>";
exit;
}

if($status == "Banned") {
echo "<span class=\"mediumtxt \">$lang_bannedmessage</span>";
exit;
}

if($subject == "" || ereg("^ *$", $subject)) {
echo "$lang_textnosubject";
exit;
}

$pperm = explode("|", $forums[postperm]);

if($pperm[0] == "2" && $status != "Administrator") {
echo "<span class=\"mediumtxt \">$lang_postpermerr</span>";
exit;
} elseif($pperm[0] == "3" && $status != "Administrator" && $status != "Moderator" && $status != "Super Moderator") {
echo "<span class=\"mediumtxt \">$lang_postpermerr</span>";
exit;
} elseif($pperm[0] == "4") {
echo "<span class=\"mediumtxt \">$lang_postpermerr</span>";
exit;
}

$query = $db->query("SELECT lastpost, type, fup FROM $table_forums WHERE fid='$fid'");
$for = $db->fetch_array($query);

if($for[lastpost] != "") {
$lastpost = explode("|", $for[lastpost]);
$rightnow = time() - $floodctrl;

if($rightnow <= $lastpost[0] && $username == $lastpost[1]) {
$floodlink = "<a href=\"forumdisplay.php?fid=$fid\">Click here</a>";
echo "<span class=\"mediumtxt \">$lang_floodprotect $floodlink $lang_tocont</span>";
exit;
}
}

$subject = str_replace("<", "&lt;", $subject);
$subject = str_replace(">", "&gt;", $subject);
$message = addslashes($message);
$subject = addslashes($subject);

if($attach != "none" && $attach != "" && $forums[attachstatus] != "off") {
$attachedfile = addslashes(fread(fopen($attach, "r"), filesize($attach)));
if($attach_size > 1000000) {
echo "<span class=\"mediumtxt \">$lang_attachtoobig</span>";
exit;
}
$attachstuff = "$attach_name|^!*!^|$attach_type|^!*!^|$attachedfile";
}

if($usesig != "yes") {
$usesig = "no";
}

if($forums[pollstatus] != "off") {
$pollops = explode("\n", $pollanswers);
$pollanswers = "";
for($pnum = 0; $pnum < 10; $pnum++) {
if($pollops[$pnum] != "") {
$pollanswers .= "$pollops[$pnum]||~|~|| 0#|#";
}
}

$pollanswers = str_replace("\n", "", $pollanswers);
}

$thatime = time();
$db->query("INSERT INTO $table_threads VALUES ('', '$fid', '$subject', '$posticon', '$thatime|$username', '0', '0', '$username', '', '', '$pollanswers')");
$tid = $db->insert_id();

$db->query("INSERT INTO $table_posts VALUES ('$fid', '$tid', '', '$username', '$message', '$subject', '$thatime', '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff', '$attachstuff')");
$db->query("UPDATE $table_forums SET lastpost='$thatime|$username', threads=threads+1, posts=posts+1 WHERE fid='$fid'");

if($for[type] == "sub") {
$db->query("UPDATE $table_forums SET lastpost='$thatime|$username', threads=threads+1, posts=posts+1 WHERE fid='$for[fup]'");
}

$db->query("UPDATE $table_members SET postnum=postnum+1 WHERE username like '$username'");

if(($status == "Administrator" || $status == "Super Moderator" || $status == "Moderator") && $toptopic == "yes") {
$db->query("UPDATE $table_threads SET topped='1' WHERE tid='$tid' AND fid='$fid'");
}

echo "<span class=\"mediumtxt \">$lang_postmsg</span>";
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

if($action == "reply") {
$priv = privfcheck($private, $userlist);
if(!$replysubmit) {
// Start Reply With Quote
if($repquote) {
$query = $db->query("SELECT message, fid FROM $table_posts WHERE pid='$repquote'");
$thaquote = $db->fetch_array($query);
$quotefid = $thaquote[fid];
$message = $thaquote[message];

$query = $db->query("SELECT private FROM $table_forums WHERE fid='$quotefid'");
$quoteforum = $db->fetch_array($query);

if($quoteforum[private] == "2" && $status != "Administrator") {
echo "$lang_privforummsg";
exit;
} elseif($quoteforum[private] == "3" && $status != "Administrator" && $status != "Moderator" && $status != "Super Moderator") {
echo "$lang_privforummsg";
exit;
} elseif($quoteforum[private] == "4") {
echo "$lang_privforummsg";
exit;
}


$message = stripslashes($message);
$message = "[quote]$message [/quote]";
}
// Start Topic/Thread Review
if($xmbuser && $xmbuser != '') {
$query = $db->query("SELECT ppp FROM $table_members WHERE username='$xmbuser'");
$this = $db->fetch_array($query);
$ppp = $this[ppp];
}

if(!$ppp || $ppp == '') {
$ppp = $postperpage;
}
$querytop = $db->query("SELECT COUNT(*) FROM $table_posts WHERE tid='$tid'");
$replynum = $db->result($querytop, 0);
if($replynum >= $ppp) {
$threadlink = "viewthread.php?fid=$fid&tid=$tid";
eval($lang_evaltrevlt);
eval("\$posts .= \"".template("post_reply_review_toolong")."\";");
}
else {
$thisbg = $altbg1;
$query = $db->query("SELECT * FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC");
while($post = $db->fetch_array($query)) {
$date = gmdate($dateformat, $post[dateline] + ($timeoffset * 3600));
$time = gmdate($timecode, $post[dateline] + ($timeoffset * 3600));

$poston = "$lang_textposton $date $lang_textat $time";
if($post[icon] != "") {
$post[icon] = "<img src=\"$smdir/$post[icon]\" alt=\"Icon depicting mood of post\" />";
}

$bbcodeoff = $post[bbcodeoff];
$smileyoff = $post[smileyoff];
$post[message] = stripslashes($post[message]);
$post[message] = postify($post[message], $smileyoff, $bbcodeoff, $forums[allowsmilies], $forums[allowhtml], $forums[allowbbcode], $forums[allowimgcode]);
eval("\$posts .= \"".template("post_reply_review_post")."\";");
if($thisbg == $altbg2) {
$thisbg = $altbg1;
} else {
$thisbg = $altbg2;
}
}
}
// Start Displaying the Post form
if($forums[attachstatus] != "off") {
eval("\$attachfile = \"".template("post_attachmentbox")."\";");
}
eval("\$postform = \"".template("post_reply")."\";");
echo $postform;
}
if($replysubmit) {
if(!$xmbuser && !$xmbpw) {
$password = md5($password);
}
$query = $db->query("SELECT username, password, status FROM $table_members WHERE username='$username'");
$member = $db->fetch_array($query);

if(!$member[username]) {
echo "<span class=\"mediumtxt \">$lang_badname</span>";
exit;
}

$username = $member[username];

if($password != $member[password]) {
echo "<span class=\"mediumtxt \">$lang_textpwincorrect</span>";
exit;
}

if($status == "Banned") {
echo "<span class=\"mediumtxt \">$lang_bannedmessage</span>";
exit;
}

$pperm = explode("|", $forums[postperm]);

if($pperm[1] == "2" && $status != "Administrator") {
echo "<span class=\"mediumtxt \">$lang_postpermerr</span>";
exit;
} elseif($pperm[1] == "3" && $status != "Administrator" && $status != "Moderator" && $status != "Super Moderator") {
echo "<span class=\"mediumtxt \">$lang_postpermerr</span>";
exit;
} elseif($pperm[1] == "4") {
echo "<span class=\"mediumtxt \">$lang_postpermerr</span>";
exit;
}


$query = $db->query("SELECT lastpost FROM $table_forums WHERE fid='$fid'");
$last = $db->result($query, 0);

if($last != "") {
$lastpost = explode("|", $last);
$rightnow = time() - $floodctrl;

if($rightnow <= $lastpost[0] && $username == $lastpost[1]) {
$floodlink = "<a href=\"viewthread.php?fid=$fid&tid=$tid\">Click here</a>";
echo "<span class=\"mediumtxt \">$lang_floodprotect $floodlink $lang_tocont</span>";
exit;
}
}
$message = addslashes($message);

if($usesig != "yes") {
$usesig = "no";
}

$subject = str_replace("<", "&lt;", $subject);
$subject = str_replace(">", "&gt;", $subject);
$subject = addslashes($subject);

if($attach != "none" && $attach != "" && $forums[attachstatus] != "off") {
$attachedfile = addslashes(fread(fopen($attach, "r"), filesize($attach)));
if($attach_size > 1000000) {
echo "<span class=\"mediumtxt \">$lang_attachtoobig</span>";
exit;
}
$attachstuff = "$attach_name|^!*!^|$attach_type|^!*!^|$attachedfile";
}

$query = $db->query("SELECT closed FROM $table_threads WHERE fid=$fid AND tid=$tid");
$closed1 = $db->fetch_array($query);
$closed = $closed1[closed];

if($closed != "yes") {
$thatime = time();

// Start Subsciptions
$query = $db->query("SELECT dateline FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC LIMIT 1");
$lp = $db->fetch_array($query);
$threadurl = $boardurl;
$threadurl .= "viewthread.php?tid=$tid";

$subquery = $db->query("SELECT * FROM $table_favorites f, $table_members m WHERE f.type='subscription' AND f.tid='$tid' AND m.username=f.username AND f.username != '$username' AND m.lastvisit>'$lp[dateline]'");
while($subs = $db->fetch_array($subquery)) {
mail("$subs[email]", "$lang_textsubsubject $threadname", "$username $lang_textsubbody \n\n$threadurl $lang_textsubaol <a href=\"$threadurl\">$threadurl</a>", "From: $bbname");
}
// End Subscriptions
$db->query("INSERT INTO $table_posts VALUES ('$fid', '$tid', '', '$username', '$message', '$subject', '$thatime', '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff', '$attachstuff')");
$pid = $db->insert_id();

$db->query("UPDATE $table_threads SET lastpost='$thatime|$username', replies=replies+1 WHERE (tid='$tid' AND fid='$fid') OR closed='moved|$tid'");
$db->query("UPDATE $table_forums SET lastpost='$thatime|$username', posts=posts+1 WHERE fid='$fid'");

$db->query("UPDATE $table_members SET postnum=postnum+1 WHERE username='$username'");

}
else {
echo "<span class=\"mediumtxt \">$lang_closedmsg</span>";
exit;
}


echo "<span class=\"mediumtxt \">$lang_replymsg</span>";
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

if($action == "edit") {
if(!$editsubmit) {
$query = $db->query("SELECT * FROM $table_posts WHERE pid='$pid' AND tid='$tid' AND fid='$fid'");
$postinfo = $db->fetch_array($query);

$postinfo[message] = str_replace("<br>", "", $postinfo[message]);

if($forums[allowsmilies] == "yes") {
$querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='smiley'");
while($smilie = $db->fetch_array($querysmilie)) {
$postinfo[message] = str_replace("<img src=\"$smdir/$smilie[url]\" border=0>",$smilie[code],$postinfo[message]);
}
}

if($postinfo[usesig] == "yes") {
$checked = "checked=\"checked\"";
}

$postinfo[message] = stripslashes($postinfo[message]);

if($postinfo[bbcodeoff] == "yes") {
$offcheck1 = "checked=\"checked\"";
}

if($postinfo[smileyoff] == "yes") {
$offcheck2 = "checked=\"checked\"";
}

if($postinfo[usesig] == "yes") {
$offcheck3 = "checked=\"checked\"";
}

$postinfo[subject] = stripslashes($postinfo[subject]);
$postinfo[subject] = str_replace('"', "&quot;", $postinfo[subject]);

$icons = "";
$listed_icons = 0;
$querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='picon'");
while($smilie = $db->fetch_array($querysmilie)) {
if($postinfo[icon] == $smilie[url]) {
$icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\"checked=\"checked\" /><img src=\"$smdir/$smilie[url]\" />";
} else {
$icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$smdir/$smilie[url]\" />";
}
$listed_icons += 1;
if($listed_icons == 8) {
$icons .= "<br />";
$listed_icons = 0;
}
}
eval("\$edit = \"".template("post_edit")."\";");
echo $edit;
}

if($editsubmit) {
if(!$xmbuser && !$xmbpw) {
$password = md5($password);
}
$query = $db->query("SELECT username, password, status FROM $table_members WHERE username='$username'");
$member = $db->fetch_array($query);
$status = $member[status];

if(!$member[username]) {
echo "<span class=\"mediumtxt \">$lang_badname</span>";
exit;
}

$username = $member[username];

if($password != $member[password]) {
echo "<span class=\"mediumtxt \">$lang_textpwincorrect</span>";
exit;
}

if($status == "Banned") {
echo "<span class=\"mediumtxt \">$lang_bannedmessage</span>";
exit;
}

$date = gmdate($dateformat);
$message .= "

[$lang_textediton $date $lang_textby $username]";

$subject = str_replace("<", "&lt;", $subject);
$subject = str_replace(">", "&gt;", $subject);
$subject = addslashes($subject);

$status1 = modcheck($status, $username, $fid, $table_forums);
if($status == "Super Moderator") {
$status1 = "Moderator";
}

$query= $db->query("SELECT pid FROM $table_posts WHERE tid='$tid' ORDER BY dateline LIMIT 1");
$isfirstpost = $db->fetch_array($query);

$query = $db->query("SELECT author FROM $table_posts WHERE pid='$pid' AND tid='$tid' AND fid='$fid'");
$orig = $db->fetch_array($query);

$message = addslashes($message);
if($status == "Administrator" || $status1 == "Moderator" || $username == $orig[author]) {
if($delete != "yes") {
$db->query("UPDATE $table_posts SET message='$message', usesig='$usesig', bbcodeoff='$bbcodeoff', smileyoff='$smileyoff', icon='$posticon', subject='$subject' WHERE pid='$pid'");
} elseif($delete == "yes" && !($isfirstpost[pid] == $pid)) {
$db->query("UPDATE $table_forums SET posts=posts-1 WHERE fid='$fid'");
$db->query("UPDATE $table_threads SET replies=replies-1 WHERE tid='$tid' AND fid='$fid'");
$db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$origauthor'");
$db->query("DELETE FROM $table_posts WHERE pid='$pid'");
} elseif($delete == "yes" && $isfirstpost[pid] == $pid) {
$count = $db->query("SELECT COUNT(pid) FROM $table_posts WHERE tid='$tid'");
$subtract = $db->result($count, 0);

$count = $db->query("SELECT type, fup FROM $table_forums WHERE fid='$fid'");
$for = $db->fetch_array($count);

if($status == "Administrator" || $status == "Moderator" || ($username == $origauthor && $subtract == 1)) {
$db->query("UPDATE $table_forums SET threads=threads-1, posts=posts-'$subtract' WHERE fid='$fid'");

if($for[type] == "sub") {
$db->query("UPDATE $table_forums SET threads=threads-1, posts=posts-'$subtract' WHERE fid='$for[fup]'");
}

$query = $db->query("SELECT author FROM $table_threads WHERE tid='$tid'");
while($result = $db->fetch_array($query)) {
$db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$result[author]'");
}

$db->query("DELETE FROM $table_threads WHERE tid='$tid'");
$db->query("DELETE FROM $table_posts WHERE tid='$tid'");
}
}
} else {
echo "<span class=\"mediumtxt \">$lang_noedit</span>";
exit;
}

echo "<span class=\"mediumtxt \">$lang_editpostmsg</span>";
?>
<script>
function redirect()
{
window.location.replace("viewthread.php?tid=<?=$tid?>");
}
setTimeout("redirect();", 1250);
</script>
<?
}
}



$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>
