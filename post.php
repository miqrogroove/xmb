<?
require "header.php";

$query = mysql_query("SELECT * FROM $table_forums WHERE fid='$fid'") or die(mysql_error());
$forums = mysql_fetch_array($query);

if($forums[type] != "forum" && $forums[type] != "sub" && $forums[fid] != $fid) {
$posterror = $lang[textnoforum];
}

if($tid && $fid) {
$query = mysql_query("SELECT subject FROM $table_threads WHERE fid='$fid' AND tid='$tid'") or die(mysql_error());
$threadname = mysql_result($query,0);
$threadname = stripslashes($threadname);
}

if($forums[type] == "forum") {
$postaction = "<a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &gt; <a href=\"viewthread.php?tid=$tid\">$threadname</a> &gt; ";
} else {
$query = mysql_query("SELECT name, fid FROM $table_forums WHERE fid='$forums[fup]'") or die(mysql_error());
$fup = mysql_fetch_array($query);
$postaction = "<a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> &gt; <a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &gt; <a href=\"viewthread.php?tid=$tid\">$threadname</a> &gt; ";
}

if($action == "reply" && !$previewpost) {
$postaction .= "$lang[textpostreply]";
}
elseif($action == "reply" && $previewpost) {
$postaction .= "$lang[textpreview]";
}
elseif($action == "edit") {
$postaction .= "$lang[texteditpost]";
}
elseif($action == "rate") {
$postaction .= "$lang[ratethread]";
}

if($action == "newtopic") {
if($forums[type] == "forum") {
$postaction = "<a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &gt; ";
} else {
$query = mysql_query("SELECT name, fid FROM $table_forums WHERE fid='$forums[fup]'") or die(mysql_error());
$fup = mysql_fetch_array($query);
$postaction = "<a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> &gt; <a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &gt; ";
}
}

if($action == "newtopic" && !$previewpost) {
$postaction .= "$lang[textpostnew]";
}
elseif($action == "newtopic" && $previewpost) {
$postaction .= "$lang[textpreview]";
}

if($catsonly == "on" && $forums[type] == "sub") {
$query = mysql_query("SELECT fup FROM $table_forums WHERE fid='$forums[fup]'") or die(mysql_error());
$forum1 = mysql_fetch_array($query);
$query = mysql_query("SELECT fid, name FROM $table_forums WHERE fid='$forum1[fup]'") or die(mysql_error());
$cat = mysql_fetch_array($query);
} elseif($catsonly == "on" && $forums[type] == "forum") {
$query = mysql_query("SELECT fid, name FROM $table_forums WHERE fid='$forums[fup]'") or die(mysql_error());
$cat = mysql_fetch_array($query);
}

if($catsonly == "on") {
$navigation = "<a href=\"index.php\">$lang[textindex]</a> &gt; <a href=\"index.php?gid=$cat[fid]\">$cat[name]</a> &gt; $postaction";
} else {
$navigation = "<a href=\"index.php\">$lang[textindex]</a> &gt; $postaction";
}

$html = template("header.html");
eval("echo stripslashes(\"$html\");");

if($status == "Banned") {
echo $lang[bannedmessage];
}

if($piconstatus == "on") {
$listed_icons = 0;
$querysmilie = mysql_query("SELECT * FROM $table_smilies WHERE type='picon'") or die(mysql_error());
while($smilie = mysql_fetch_array($querysmilie)) {
$icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"images/$smilie[url]\" />  ";
$listed_icons += 1;
if($listed_icons == 8) {
$icons .= "<br />";
$listed_icons = 0;
}
}
}


if($piconstatus == "on") {
$listed_icons = 0;
$querysmilie = mysql_query("SELECT * FROM $table_smilies WHERE type='picon'") or die(mysql_error());
while($smilie = mysql_fetch_array($querysmilie)) {
if($posticon == $smilie[url]) {
$icons1 .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\"checked=\"checked\" /><img src=\"images/$smilie[url]\" />  ";
} else {
$icons1 .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"images/$smilie[url]\" />  ";
}
$listed_icons += 1;
if($listed_icons == 8) {
$icons1 .= "<br />";
$listed_icons = 0;
}
}
}

if($forums[allowimgcode] == "yes") {
$allowimgcode = "$lang[texton]";
} else {
$allowimgcode = "$lang[textoff]";
}

if($forums[allowhtml] == "yes") {
$allowhtml = "$lang[texton]";
} else {
$allowhtml = "$lang[textoff]";
}

if($forums[allowsmilies] == "yes") {
$allowsmilies = "$lang[texton]";
} else {
$allowsmilies = "$lang[textoff]";
}

if($forums[allowbbcode] == "yes") {
$allowbbcode = "$lang[texton]";
} else {
$allowbbcode = "$lang[textoff]";
}

if($forums[guestposting] == "yes") {
if(!$thisuser || $thisuser == "") {
$bbpostuser = $lang[textguest];
} else {
$bbpostuser = $thisuser;
}
} else {
$bbpostuser = $thisuser;
}

$pperm = explode("|", $forums[postperm]);

if($pperm[0] == "1") {
$whopost1 = $lang[whocanpost11];
} elseif($pperm[0] == "2") {
$whopost1 = $lang[whocanpost12];
} elseif($pperm[0] == "3") {
$whopost1 = $lang[whocanpost13];
} elseif($pperm[0] == "4") {
$whopost1 = $lang[whocanpost14];
}

if($pperm[1] == "1") {
$whopost2 = $lang[whocanpost21];
} elseif($pperm[1] == "2") {
$whopost2 = $lang[whocanpost22];
} elseif($pperm[1] == "3") {
$whopost2 = $lang[whocanpost23];
} elseif($pperm[1] == "4") {
$whopost2 = $lang[whocanpost24];
}

if($forums[guestposting] == "yes") {
$whopost3 = $lang[whocanpost31];
} else {
$whopost3 = $lang[whocanpost32];
}

if($pperm[0] == "4" && $pperm[1] == "4") {
$whopost3 = $lang[whocanpost32];
}

if($thisuser && $thisuser != '') {
$query = mysql_query("SELECT sig FROM $table_members WHERE username='$thisuser'") or die(mysql_error());
$this = mysql_fetch_array($query);
$sig = $this[sig];
if($sig != "") {
$sigcheck = "checked=\"checked\"";
}
}

if($forums[private] == "staff" && $status != "Administrator" && $status != "Super Moderator" && $status != "Moderator") {
echo "<div class=\"tablerow\">$lang[privforummsg]</div>";
exit;
}

if($subf[private] == "staff" && $status != "Administrator" && $status != "Super Moderator" && $status != "Moderator") {
echo "<div class=\"tablerow\">$lang[privforummsg]</div>";
exit;
}

if($posterror) {
echo "<div class=\"tablerow\">$posterror</div>";
exit;
}


if($action == "newtopic") {
if($previewpost) {

$currtime = time();
$date = gmdate("n/j/y",$currtime + ($timeoffset * 3600));
$time = gmdate("H:i",$currtime + ($timeoffset * 3600));
$poston = "$lang[textposton] $date $lang[textat] $time";

$subject = stripslashes($subject);
$message = stripslashes($message);
$message1 = postify($message, $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", $table_words, $table_forums, $table_smilies);

if($smileyoff == "yes") {
$smileoffcheck = "checked=\"checked\"";
}

if($usesig == "yes") {
$usesigcheck = "checked=\"checked\"";
}

if($bbcodeoff == "yes") {
$codeoffcheck = "checked=\"checked\"";
}

if($emailnotify == "yes") {
$notifycheck = "checked=\"checked\"";
}
?>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?=$lang[textpreview]?></td>
</tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td rowspan="2" valign="top" width="19%"><span class="postauthor"><?=$username?></span><br /><br /></td>
<td><?=$thread[icon]?>  <?=$poston?></td></tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow"><td><p><?=$message1?></p><br /></td></tr>

</table>
</td></tr></table>
<br />

<form method="post" name="input" action="post.php?action=newtopic&fid=<?=$fid?>">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>"><?=$lang[textsubject]?></td>
<td bgcolor="<?=$altbg2?>"><input type="text" name="subject" size="45" value="<?=$subject?>" /></td>
</tr>

<?
if($piconstatus == "on") {
?>
<tr class="tablerow"><td bgcolor="<?=$altbg1?>"><?=$lang[texticon]?></td><td bgcolor="<?=$altbg2?>"><?=$icons1?></td></tr>
<?
}
?>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>" valign="top" width="19%"><?=$lang[textmessage]?></td>
<td bgcolor="<?=$altbg2?>"><textarea rows="9" cols="45" name="message"><?=$message?></textarea>
<br />
<input type="checkbox" name="smileyoff" value="yes" <?=$smileoffcheck?> /> <?=$lang[textdissmileys]?><br />
<input type="checkbox" name="usesig" value="yes" <?=$usesigcheck?> /> <?=$lang[textusesig]?><br />
<input type="checkbox" name="bbcodeoff" value="yes" <?=$codeoffcheck?> /><?=$lang[bbcodeoff]?><br />
<input type="checkbox" name="emailnotify" value="yes" <?=$notifycheck?> /> <?=$lang[emailnotifytoreplies]?><br />
</td>
</tr>
</table>
</td></tr></table>

<input type="hidden" name="username" value="<?=$username?>">
<input type="hidden" name="password" value="<?=$password?>">
<center><input type="submit" name="topicsubmit" value="<?=$lang[textpostnew]?>" />
<input type="submit" name="previewpost" value="<?=$lang[textpreview]?>" /></center>
</form>

<?
}

if(!$topicsubmit && !$previewpost) {
?>

<form method="post" name="input" action="post.php?action=newtopic&fid=<?=$fid?>">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?=$lang[textpostnew]?></td>
</tr>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>" width="22%"><?=$lang[whocanpost]?></td>
<td bgcolor="<?=$altbg2?>"><span class="11px"><?=$whopost1?> <?=$whopost2?> <?=$whopost3?></span></td>
</tr>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>" width="22%"><?=$lang[textusername]?></td>
<td bgcolor="<?=$altbg2?>"><input type="text" name="username" size="25" maxlength="25" value="<?=$bbpostuser?>" /> &nbsp;<span class="11px"><a href="member.php?action=reg"><?=$lang[regques]?></a></span></td>
</tr>

<?
if($noreg != "on") {
?>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>"><?=$lang[textpassword]?></td>
<td bgcolor="<?=$altbg2?>"><input type="password" name="password" size="25" value="<?=$thispw?>" /> &nbsp;<span class="11px"><a href="misc.php?action=lostpw"><?=$lang[forgotpw]?></a></span></td>
</tr>

<?
}
?>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>"><?=$lang[textsubject]?></td>
<td bgcolor="<?=$altbg2?>"><input type="text" name="subject" size="45" /></td>
</tr>

<?
if($piconstatus == "on") {
?>
<tr class="tablerow"><td bgcolor="<?=$altbg1?>"><?=$lang[texticon]?></td><td bgcolor="<?=$altbg2?>"><?=$icons?></td></tr>
<?
}
?>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>" valign="top"><?=$lang[textmessage]?><br /><span class="11px">
<?=$lang[texthtmlis]?> <?=$allowhtml?><br />
<?=$lang[textsmiliesare]?>  <?=$allowsmilies?><br />
<?=$lang[textbbcodeis]?> <?=$allowbbcode?><br />
<?=$lang[textimgcodeis]?> <?=$allowimgcode?>
</span></td>

<td bgcolor="<?=$altbg2?>">
<table width="100%"><tr><td align="left" width="70%" colspan="2">
<a href="javascript:icon('[b] [/b]')"><img src="images/bb_bold.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[i] [/i]')"><img src="images/bb_italicize.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[u] [/u]')"><img src="images/bb_underline.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[email] [/email]')"><img src="images/bb_email.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[quote] [/quote]')"><img src="images/bb_quote.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[url] [/url]')"><img src="images/bb_url.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[img] [/img]')"><img src="images/bb_image.gif" border="0"></a>&nbsp; &nbsp;
</td></tr>

<tr><td align="left" width="70%"><textarea rows="9" cols="45" name="message"></textarea></td><td>

<?
$querysmilie = mysql_query("SELECT * FROM $table_smilies WHERE type='smiley'") or die(mysql_error());
echo "<table border=\"1\" align=\"center\">";
$l = "on";
while($smilie = mysql_fetch_array($querysmilie)) {

if($l == "on") {
echo "<tr><td><a href=\"javascript:icon('$smilie[code]')\"><img src=\"images/$smilie[url]\" border=\"0\"></a></td>";
} else {
echo "<td><a href=\"javascript:icon('$smilie[code]')\"><img src=\"images/$smilie[url]\" border=\"0\"></a></td></tr>";
}

if($l == "on") {
$r = "on";
$l = "off";
} else {
$l = "on";
$r = "off";
}
}

if($l == "off") {
echo "<td>&nbsp;</td></tr>";
}

echo "</table>\n";


if($status == "Administrator" || $status == "Super Moderator" || $status == "Moderator") {
$topoption = "<input type=\"checkbox\" name=\"toptopic\" value=\"yes\" />$lang[topmsgques]<br />";
}
?>
</td></tr></table>

<br />
<input type="checkbox" name="smileyoff" value="yes" /> <?=$lang[textdissmileys]?><br />
<input type="checkbox" name="usesig" value="yes" <?=$sigcheck?> /> <?=$lang[textusesig]?><br />
<input type="checkbox" name="bbcodeoff" value="yes" /><?=$lang[bbcodeoff]?><br />
<input type="checkbox" name="emailnotify" value="yes" /><?=$lang[emailnotifytoreplies]?><br /> <?=$topoption?></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="topicsubmit" value="<?=$lang[textpostnew]?>" />
<input type="submit" name="previewpost" value="<?=$lang[textpreview]?>" />
</center>
</form>

<?
}

if($topicsubmit) {
if($username != $lang[textguest]) {
if($noreg != "on") {
$query = mysql_query("SELECT username, password, status FROM $table_members WHERE username='$username'") or die(mysql_error());
$member = mysql_fetch_array($query);

if(!$member[username]) {
echo "<span class=\"12px \">$lang[badname]</span>";
exit;
}

$username = $member[username];

if($password != $member[password]) {
echo "<span class=\"12px \">$lang[textpwincorrect]</span>";
exit;
}

if($status == "Banned") {
echo "<span class=\"12px \">$lang[bannedmessage]</span>";
exit;
}
}
}

if($subject == "") {
echo "$lang[textnosubject]";
exit;
}

if($noreg == "on" && $username == "") {
echo "<span class=\"12px \">$lang[badname]</span>";
exit;
}

if($forums[guestposting] != "yes" && $username == $lang[textguest]) {
echo "<span class=\"12px \">$lang[textnoquestposting]</span>";
exit;
}

$pperm = explode("|", $forums[postperm]);

if($pperm[0] == "2" && $status != "Administrator") {
echo "<span class=\"12px \">$lang[postpermerr]</span>";
exit;
} elseif($pperm[0] == "3" && $status != "Administrator" && $status != "Moderator" && $status != "Super Moderator") {
echo "<span class=\"12px \">$lang[postpermerr]</span>";
exit;
} elseif($pperm[0] == "4") {
echo "<span class=\"12px \">$lang[postpermerr]</span>";
exit;
}

$query = mysql_query("SELECT lastpost, type, fup FROM $table_forums WHERE fid='$fid'") or die(mysql_error());
$for = mysql_fetch_array($query);

if($for[lastpost] != "") {
$lastpost = explode("|", $for[lastpost]);
$rightnow = time() - $floodctrl;

if($rightnow <= $lastpost[0] && $username == $lastpost[1]) {
$floodlink = "<a href=\"forumdisplay.php?fid=$fid\">Click here</a>";
echo "<span class=\"12px \">$lang[floodprotect] $floodlink $lang[tocont]</span>";
exit;
}
}

$subject = str_replace("<", "&lt;", $subject);
$subject = str_replace(">", "&gt;", $subject);

$message = addslashes($message);
$subject = addslashes($subject);

if($usesig != "yes") {
$usesig = "no";
}

if($emailnotify != "yes" || $username == $lang[textguest]) {
$emailnotify = "no";
}

$thatime = time();
mysql_query("INSERT INTO $table_threads VALUES ('', '$fid', '$subject', '$thatime|$username', '0', '0', '$username', '$message', '$thatime', '$posticon', '$usesig', '', '', '$onlineip', '$bbcodeoff', '$smileyoff', '$emailnotify')") or die(mysql_error());
$tid = mysql_insert_id();
mysql_query("UPDATE $table_forums SET lastpost='$thatime|$username', threads=threads+1, posts=posts+1 WHERE fid='$fid'") or die(mysql_error());

if($for[type] == "sub") {
mysql_query("UPDATE $table_forums SET lastpost='$thatime|$username', threads=threads+1, posts=posts+1 WHERE fid='$for[fup]'") or die(mysql_error());
}

if($username != $lang[textguest]) {
mysql_query("UPDATE $table_members SET postnum=postnum+1 WHERE username like '$username'") or die(mysql_error());
}

if(($status == "Administrator" || $status == "Super Moderator" || $status == "Moderator") && $toptopic == "yes") {
mysql_query("UPDATE $table_threads SET topped='1' WHERE tid='$tid' AND fid='$fid'");
}

echo "<span class=\"12px \">$lang[postmsg]</span>";
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

if($action == "reply") {
if($previewpost) {

$currtime = time();
$date = gmdate("n/j/y",$currtime + ($timeoffset * 3600));
$time = gmdate("H:i",$currtime + ($timeoffset * 3600));
$poston = "$lang[textposton] $date $lang[textat] $time";

$message = stripslashes($message);
$message1 = postify($message, $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", $table_words, $table_forums, $table_smilies);

if($smileyoff == "yes") {
$smileoffcheck = "checked=\"checked\"";
}

if($usesig == "yes") {
$usesigcheck = "checked=\"checked\"";
}

if($bbcodeoff == "yes") {
$codeoffcheck = "checked=\"checked\"";
}
?>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?=$lang[textpreview]?></td>
</tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td rowspan="2" valign="top" width="19%"><span class="postauthor"><?=$username?></span><br /><br /></td>
<td><?=$thread[icon]?>  <?=$poston?></td></tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow"><td><p><?=$message1?></p><br /></td></tr>

</table>
</td></tr></table>
<br />

<form method="post" name="input" action="post.php?action=reply&fid=<?=$fid?>&tid=<?=$tid?>">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<?
if($piconstatus == "on") {
?>
<tr class="tablerow"><td bgcolor="<?=$altbg1?>"><?=$lang[texticon]?></td><td bgcolor="<?=$altbg2?>"><?=$icons1?></td></tr>
<?
}
?>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>" valign="top" width="19%"><?=$lang[textmessage]?></td>
<td bgcolor="<?=$altbg2?>"><textarea rows="9" cols="45" name="message"><?=$message?></textarea>
<br />
<input type="checkbox" name="smileyoff" value="yes" <?=$smileoffcheck?> /> <?=$lang[textdissmileys]?><br />
<input type="checkbox" name="usesig" value="yes" <?=$usesigcheck?> /> <?=$lang[textusesig]?><br />
<input type="checkbox" name="bbcodeoff" value="yes" <?=$codeoffcheck?> /><?=$lang[bbcodeoff]?> </td>
</tr>
</table>
</td></tr></table>

<input type="hidden" name="username" value="<?=$username?>">
<input type="hidden" name="password" value="<?=$password?>">
<center><input type="submit" name="replysubmit" value="<?=$lang[textpostreply]?>" />
<input type="submit" name="previewpost" value="<?=$lang[textpreview]?>" /></center>
</form>

<?
}

if($replysubmit) {
if($username != $lang[textguest]) {
if($noreg != "on") {
$query = mysql_query("SELECT username, password, status FROM $table_members WHERE username='$username'") or die(mysql_error());
$member = mysql_fetch_array($query);

if(!$member[username]) {
echo "<span class=\"12px \">$lang[badname]</span>";
exit;
}

$username = $member[username];

if($password != $member[password]) {
echo "<span class=\"12px \">$lang[textpwincorrect]</span>";
exit;
}

if($status == "Banned") {
echo "<span class=\"12px \">$lang[bannedmessage]</span>";
exit;
}
}
}

if($forums[guestposting] != "yes" && $username == $lang[textguest]) {
echo "<span class=\"12px \">$lang[textnoquestposting]";
exit;
}

$pperm = explode("|", $forums[postperm]);

if($pperm[1] == "2" && $status != "Administrator") {
echo "<span class=\"12px \">$lang[postpermerr]</span>";
exit;
} elseif($pperm[1] == "3" && $status != "Administrator" && $status != "Moderator" && $status != "Super Moderator") {
echo "<span class=\"12px \">$lang[postpermerr]</span>";
exit;
} elseif($pperm[1] == "4") {
echo "<span class=\"12px \">$lang[postpermerr]</span>";
exit;
}


$query = mysql_query("SELECT lastpost FROM $table_forums WHERE fid='$fid'") or die(mysql_error());
$last = mysql_result($query, 0);

if($last != "") {
$lastpost = explode("|", $last);
$rightnow = time() - $floodctrl;

if($rightnow <= $lastpost[0] && $username == $lastpost[1]) {
$floodlink = "<a href=\"viewthread.php?fid=$fid&tid=$tid\">Click here</a>";
echo "<span class=\"12px \">$lang[floodprotect] $floodlink $lang[tocont]</span>";
exit;
}
}
$message = addslashes($message);

if($usesig != "yes") {
$usesig = "no";
}

if($emailnotify != "yes" || $username == $lang[textguest]) {
$emailnotify = "no";
}

$query = mysql_query("SELECT closed FROM $table_threads WHERE fid=$fid AND tid=$tid") or die(mysql_error());
$closed1 = mysql_fetch_array($query);
$closed = $closed1[closed];

if($closed != "yes") {
$thatime = time();

$query = mysql_query("SELECT author FROM $table_threads WHERE tid='$tid' AND emailnotify='yes'") or die(mysql_error());
$thread = mysql_fetch_array($query);
if($thread) {

$getuser = mysql_query("SELECT email FROM $table_members WHERE username='$thread[author]'") or die(mysql_error());
$user = mysql_fetch_array($getuser);
$theurl = $boardurl . "viewthread.php?tid=$tid";
mail("$user[email]", "$lang[emailnotifysubject] $threadname", "$lang[emailnotifyintro]$threadname$lang[emailnotifyintro2]\n\n$theurl\n\n$lang[emailnotifyend]", "$lang[textfrom] $adminemail");
}
$query = mysql_query("SELECT author FROM $table_posts WHERE tid='$tid' AND emailnotify='yes'") or die(mysql_error());
while($post = mysql_fetch_array($query)) {
if($post) {
$getuser = mysql_query("SELECT email FROM $table_members WHERE username='$post[author]'") or die(mysql_error());
$user = mysql_fetch_array($getuser);
$theurl = $boardurl . "viewthread.php?tid=$tid";
mail("$user[email]", "$lang[emailnotifysubject] $threadname", "$lang[emailnotifyintro]$threadname$lang[emailnotifyintro2]\n\n$theurl\n\n$lang[emailnotifyend]", "$lang[textfrom] $adminemail");
}
}

mysql_query("INSERT INTO $table_posts VALUES ('$fid', '$tid', '', '$username', '$message', '$thatime', '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff', '$emailnotify')") or die(mysql_error());

mysql_query("UPDATE $table_threads SET lastpost='$thatime|$username', replies=replies+1 WHERE tid='$tid' AND fid='$fid'") or die(mysql_error());
mysql_query("UPDATE $table_forums SET lastpost='$thatime|$username', posts=posts+1 WHERE fid='$fid'") or die(mysql_error());

if($username != $lang[textguest]) {
mysql_query("UPDATE $table_members SET postnum=postnum+1 WHERE username='$username'") or die(mysql_error());
}

}
else {
echo "<span class=\"12px \">$lang[closedmsg]</span>";
exit;
}


echo "<span class=\"12px \">$lang[replymsg]</span>";
?>
<script>
function redirect()
{
window.location.replace("viewthread.php?tid=<?=$tid?>");
}
setTimeout("redirect();", 1250);
</script>
<?
} elseif(!$replysubmit && !$previewpost) {

if($repquote) {
$quote = explode("|", $repquote);

if($quote[0] == 't') {
$query = mysql_query("SELECT message, fid FROM $table_threads WHERE tid='$quote[1]'") or die(mysql_error());
$thaquote = mysql_fetch_array($query);
$quotefid = $thaquote[fid];
$thaquote = $thaquote[message];
}
elseif($quote[0] == 'r') {
$query = mysql_query("SELECT message, fid FROM $table_posts WHERE pid='$quote[1]'") or die(mysql_error());
$thaquote = mysql_fetch_array($query);
$quotefid = $thaquote[fid];
$thaquote = $thaquote[message];
}

$query = mysql_query("SELECT private FROM $table_forums WHERE fid='$quotefid'") or die(mysql_error());
$quoteforum = mysql_fetch_array($query);

if($quoteforum[private] == "staff" && $status != "Administrator" && $status != "Super Moderator" && $status != "Moderator") {
echo "<tr class=\"tablerow\"><td bgcolor=\"$altbg1\" colspan=\"2\">$lang[privforummsg]</td></tr>";
exit;
}

$thaquote = stripslashes($thaquote);
$thaquote = "[quote]$thaquote [/quote]";
}

?>
<form method="post" name="input" action="post.php?action=reply&fid=<?=$fid?>&tid=<?=$tid?>">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?=$lang[textpostreply]?></td>
</tr>

<?
if($piconstatus == "on") {
$icontable = "<tr class=\"tablerow\"><td bgcolor=\"$altbg1\">$lang[texticon]</td><td bgcolor=\"$altbg2\">$icons</td></tr>";
}
?>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>" width="22%"><?=$lang[whocanpost]?></td>
<td bgcolor="<?=$altbg2?>"><span class="11px"><?=$whopost1?> <?=$whopost2?> <?=$whopost3?></span></td>
</tr>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>" width="22%"><?=$lang[textusername]?></td>
<td bgcolor="<?=$altbg2?>"><input type="text" name="username" size="25" maxlength="25" value="<?=$bbpostuser?>" /> &nbsp;<span class="11px"><a href="member.php?action=reg"><?=$lang[regques]?></a></span></td>
</tr>

<?
if($noreg != "on") {
?>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>"><?=$lang[textpassword]?></td>
<td bgcolor="<?=$altbg2?>"><input type="password" name="password" size="25" value="<?=$thispw?>" /> &nbsp;<span class="11px"><a href="misc.php?action=lostpw"><?=$lang[forgotpw]?></a></span></td>
</tr>

<?
}
?>

<?=$icontable?>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>" valign="top"><?=$lang[textmessage]?><br /><span class="11px">
<?=$lang[texthtmlis]?> <?=$allowhtml?><br />
<?=$lang[textsmiliesare]?> <?=$allowsmilies?><br />
<?=$lang[textbbcodeis]?> <?=$allowbbcode?><br />
<?=$lang[textimgcodeis]?> <?=$allowimgcode?>
</span></td>

<td bgcolor="<?=$altbg2?>">
<table width="100%"><tr><td align="left" width="70%" colspan="2">
<a href="javascript:icon('[b] [/b]')"><img src="images/bb_bold.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[i] [/i]')"><img src="images/bb_italicize.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[u] [/u]')"><img src="images/bb_underline.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[email] [/email]')"><img src="images/bb_email.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[quote] [/quote]')"><img src="images/bb_quote.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[url] [/url]')"><img src="images/bb_url.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[img] [/img]')"><img src="images/bb_image.gif" border="0"></a>&nbsp; &nbsp;
</td></tr>

<tr><td align="left" width="70%"><textarea rows="9" cols="45" name="message"><?=$thaquote?></textarea></td><td>

<?
$querysmilie = mysql_query("SELECT * FROM $table_smilies WHERE type='smiley'") or die(mysql_error());
echo "<table border=\"1\" align=\"center\">";
$l = "on";
while($smilie = mysql_fetch_array($querysmilie)) {

if($l == "on") {
echo "<tr><td><a href=\"javascript:icon('$smilie[code]')\"><img src=\"images/$smilie[url]\" border=\"0\"></a></td>";
} else {
echo "<td><a href=\"javascript:icon('$smilie[code]')\"><img src=\"images/$smilie[url]\" border=\"0\"></a></td></tr>";
}

if($l == "on") {
$r = "on";
$l = "off";
} else {
$l = "on";
$r = "off";
}
}

if($l == "off") {
echo "<td>&nbsp;</td></tr>";
}

echo "</table>\n";
?>
</td></tr></table>

<br />
<input type="checkbox" name="smileyoff" value="yes" /> <?=$lang[textdissmileys]?><br />
<input type="checkbox" name="usesig" value="yes" <?=$sigcheck?> /> <?=$lang[textusesig]?><br />
<input type="checkbox" name="bbcodeoff" value="yes" /><?=$lang[bbcodeoff]?><br />
        <input type="checkbox" name="emailnotify" value="yes" /><?=$lang[emailnotifytoreplies]?> </td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="replysubmit" value="<?=$lang[textpostreply]?>" />
<input type="submit" name="previewpost" value="<?=$lang[textpreview]?>" />
</center>
</form>

</table>
</td></tr></table>
<br>
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="1" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?=$lang[texttopicreview]?></td>
</tr>
<?
if($thisuser && $thisuser != '') {
$query = mysql_query("SELECT ppp FROM $table_members WHERE username='$thisuser'") or die(mysql_error());
$this = mysql_fetch_array($query);
$ppp = $this[ppp];
}

if(!$ppp || $ppp == '') {
$ppp = $postperpage;
}
$querytop = mysql_query("SELECT COUNT(*) FROM $table_posts WHERE tid='$tid'") or die(mysql_error());
$replynum = mysql_result($querytop, 0);
$replynum += 1;
if($replynum >= $ppp) {
$threadlink = "viewthread.php?fid=$fid&tid=$tid";
eval($lang[evaltrevlt]);
?>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td colspan="2" valign="top" width="19%"><?=$lang[trevltmsg]?></td></tr>
<?
}
else {
$thisbg = $altbg1;
$query = mysql_query("SELECT * FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC") or die(mysql_error());
while($reply = mysql_fetch_array($query)) {
$date = gmdate($dateformat, $reply[dateline] + ($timeoffset * 3600));
$time = gmdate($timecode, $reply[dateline] + ($timeoffset * 3600));

$poston = "$lang[textposton] $date $lang[textat] $time";
if($reply[icon] != "") {
$reply[icon] = "<img src=\"images/$reply[icon]\" alt=\"Icon depicting mood of post\" />";
}

$bbcodeoff = $reply[bbcodeoff];
$smileyoff = $reply[smileyoff];
$reply[message] = stripslashes($reply[message]);
$reply[message] = postify($reply[message], $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", $table_words, $table_forums, $table_smilies);
?>
<tr bgcolor="<?=$thisbg?>" class="tablerow">
<td rowspan="2" valign="top" width="19%"><span class="postauthor"><?=$reply[author]?></span><br /><br /></td>
<td><?=$reply[icon]?>  <?=$poston?></td></tr>
<tr bgcolor="<?=$thisbg?>" class="tablerow"><td><p><?=$reply[message]?></p><br /></td></tr>
<?

if($thisbg == $altbg2) {
$thisbg = $altbg1;
}
else {
$thisbg = $altbg2;
}
}
$query = mysql_query("SELECT * FROM $table_threads WHERE tid='$tid'") or die(mysql_error());
$topic = mysql_fetch_array($query);
$date = gmdate($dateformat, $topic[dateline] + ($timeoffset * 3600));
$time = gmdate("$timecode", $topic[dateline] + ($timeoffset * 3600));

$poston = "$lang[textposton] $date $lang[textat] $time";
if($topic[icon] != "") {
$topic[icon] = "<img src=\"images/$topic[icon]\" alt=\"Icon depicting mood of post\" />";
}

$bbcodeoff = $topic[bbcodeoff];
$smileyoff = $topic[smileyoff];
$topic[message] = stripslashes($topic[message]);
$topic[message] = postify($topic[message], $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", $table_words, $table_forums, $table_smilies);
?>
<tr bgcolor="<?=$thisbg?>" class="tablerow">
<td rowspan="2" valign="top" width="19%"><span class="postauthor"><?=$topic[author]?></span><br /><br /></td>
<td><?=$topic[icon]?>  <?=$poston?></td></tr>
<tr bgcolor="<?=$thisbg?>" class="tablerow"><td><p><?=$topic[message]?></p><br /></td></tr>
<?
}

}
}

if($action == "edit") {
if(!$editsubmit) {
if($pid) {
$query = mysql_query("SELECT * FROM $table_posts WHERE pid='$pid' AND tid='$tid' AND fid='$fid'") or die(mysql_error());
$postinfo = mysql_fetch_array($query);
}
else {
$query = mysql_query("SELECT * FROM $table_threads WHERE tid='$tid'") or die(mysql_error());
$postinfo = mysql_fetch_array($query);
}

$postinfo[message] = str_replace("<br>", "", $postinfo[message]);

if($forums[allowsmilies] == "yes") {
$querysmilie = mysql_query("SELECT * FROM $table_smilies WHERE type='smiley'") or die(mysql_error());
while($smilie = mysql_fetch_array($querysmilie)) {
$postinfo[message] = str_replace("<img src=\"images/$smilie[url]\" border=0>",$smilie[code],$postinfo[message]);
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

if($postinfo[emailnotify] == "yes") {
$notifycheck = "checked=\"checked\"";
}

$postinfo[subject] = stripslashes($postinfo[subject]);
$postinfo[subject] = str_replace('"', "&quot;", $postinfo[subject]);

$icons = "";
if($piconstatus == "on") {
$listed_icons = 0;
$querysmilie = mysql_query("SELECT * FROM $table_smilies WHERE type='picon'") or die(mysql_error());
while($smilie = mysql_fetch_array($querysmilie)) {
if($postinfo[icon] == $smilie[url]) {
$icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\"checked=\"checked\" /><img src=\"images/$smilie[url]\" />  ";
} else {
$icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"images/$smilie[url]\" />  ";
}
$listed_icons += 1;
if($listed_icons == 8) {
$icons .= "<br />";
$listed_icons = 0;
}
}
}
?>

<form method="post" name="input" action="post.php?action=edit">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?=$lang[texteditpost]?></td>
</tr>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>" width="22%"><?=$lang[textusername]?></td>
<td bgcolor="<?=$altbg2?>"><input type="text" name="username" size="25" maxlength="25" value="<?=$thisuser?>" /> &nbsp;<span class="11px"><a href="member.php?action=reg"><?=$lang[regques]?></a></span></td>
</tr>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>"><?=$lang[textpassword]?></td>
<td bgcolor="<?=$altbg2?>"><input type="password" name="password" size="25" value="<?=$thispw?>" /> &nbsp;<span class="11px"><a href="misc.php?action=lostpw"><?=$lang[forgotpw]?></a></span></td>
</tr>

<?
if(!$pid) {
?>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>" width="22%"><?=$lang[textsubject]?></td>
<td bgcolor="<?=$altbg2?>"><input type="text" name="subject" size="45" value="<?=$postinfo[subject]?>" /></td>
</tr>

<?
}
?>

<?
if($piconstatus == "on") {
?>
<tr class="tablerow">
<td bgcolor="<?=$altbg1?>"><?=$lang[texticon]?></td>
<td bgcolor="<?=$altbg2?>"><?=$icons?></td>
</tr>
<?
}
?>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>" valign="top"><?=$lang[textmessage]?><br /><span class="11px">
<?=$lang[texthtmlis]?> <?=$allowhtml?><br />
<?=$lang[textsmiliesare]?>  <?=$allowsmilies?><br />
<?=$lang[textbbcodeis]?> <?=$allowbbcode?><br />
<?=$lang[textimgcodeis]?> <?=$allowimgcode?>
</span></td>

<td bgcolor="<?=$altbg2?>">
<table width="100%"><tr><td align="left" width="70%" colspan="2">
<a href="javascript:icon('[b] [/b]')"><img src="images/bb_bold.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[i] [/i]')"><img src="images/bb_italicize.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[u] [/u]')"><img src="images/bb_underline.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[email] [/email]')"><img src="images/bb_email.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[quote] [/quote]')"><img src="images/bb_quote.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[url] [/url]')"><img src="images/bb_url.gif" border="0"></a>&nbsp; &nbsp;
<a href="javascript:icon('[img] [/img]')"><img src="images/bb_image.gif" border="0"></a>&nbsp; &nbsp;
</td></tr>

<tr><td align="left" width="70%"><textarea rows="9" cols="45" name="message"><?=$postinfo[message]?></textarea></td><td>

<?
$querysmilie = mysql_query("SELECT * FROM $table_smilies WHERE type='smiley'") or die(mysql_error());
echo "<table border=\"1\" align=\"center\">";
$l = "on";
while($smilie = mysql_fetch_array($querysmilie)) {

if($l == "on") {
echo "<tr><td><a href=\"javascript:icon('$smilie[code]')\"><img src=\"images/$smilie[url]\" border=\"0\"></a></td>";
} else {
echo "<td><a href=\"javascript:icon('$smilie[code]')\"><img src=\"images/$smilie[url]\" border=\"0\"></a></td></tr>";
}

if($l == "on") {
$r = "on";
$l = "off";
} else {
$l = "on";
$r = "off";
}
}

if($l == "off") {
echo "<td>&nbsp;</td></tr>";
}

echo "</table>\n";
?>
</td></tr></table>

<br />
<input type="checkbox" name="smileyoff" value="yes" <?=$offcheck2?> /> <?=$lang[textdissmileys]?><br />
<input type="checkbox" name="usesig" value="yes" <?=$offcheck3?> /> <?=$lang[textusesig]?><br />
<input type="checkbox" name="bbcodeoff" value="yes" <?=$offcheck1?> /><?=$lang[bbcodeoff]?><br />
        <input type="checkbox" name="emailnotify" value="yes" <?=$notifycheck?> /> <?=$lang[emailnotifytoreplies]?><br />
<input type="checkbox" name="delete" value="yes" /> <b><?=$lang[textdelete]?></b></td>
</tr>

</table>
</td></tr></table>
<input type="hidden" name="fid" value="<?=$fid?>" />
<input type="hidden" name="tid" value="<?=$tid?>" />
<input type="hidden" name="pid" value="<?=$pid?>" />
<input type="hidden" name="origauthor" value="<?=$postinfo[author]?>" />
<center><input type="submit" name="editsubmit" value="<?=$lang[texteditpost]?>" /></center>
</form>

<?
}

if($editsubmit) {
$query = mysql_query("SELECT username, password, status FROM $table_members WHERE username='$username'") or die(mysql_error());
$member = mysql_fetch_array($query);
$status = $member[status];

if(!$member[username]) {
echo "<span class=\"12px \">$lang[badname]</span>";
exit;
}

$username = $member[username];

if($password != $member[password]) {
echo "<span class=\"12px \">$lang[textpwincorrect]</span>";
exit;
}

if($status == "Banned") {
echo "<span class=\"12px \">$lang[bannedmessage]</span>";
exit;
}

$date = date("n/j/y");
$message .= "

[$lang[textediton] $date $lang[textby] $username]";

if($emailnotify != "yes" || $username == $lang[textguest]) {
$emailnotify = "no";
}

$status1 = modcheck($status, $username, $fid, $table_forums);
if($status == "Super Moderator") {
$status1 = "Moderator";
}


if($status == "Administrator" || $status1 == "Moderator" || $username == $origauthor) {
$message = addslashes($message);
if($pid && $delete != "yes") {
mysql_query("UPDATE $table_posts SET message='$message', usesig='$usesig', bbcodeoff='$bbcodeoff', smileyoff='$smileyoff', emailnotify='$emailnotify', icon='$posticon' WHERE pid='$pid'") or die(mysql_error());
} elseif($delete != "yes") {
mysql_query("UPDATE $table_threads SET message='$message', usesig='$usesig', subject='$subject', bbcodeoff='$bbcodeoff', smileyoff='$smileyoff', emailnotify='$emailnotify', icon='$posticon' WHERE tid='$tid'") or die(mysql_error());
} elseif($pid && $delete == "yes") {
mysql_query("UPDATE $table_forums SET posts=posts-1 WHERE fid='$fid'") or die(mysql_error());
mysql_query("UPDATE $table_threads SET replies=replies-1 WHERE tid='$tid' AND fid='$fid'") or die(mysql_error());
mysql_query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$origauthor'") or die(mysql_error());
mysql_query("DELETE FROM $table_posts WHERE pid='$pid'") or die(mysql_error());
} elseif($delete == "yes") {
$query = mysql_query("SELECT COUNT(pid) FROM $table_posts WHERE tid='$tid'") or die(mysql_error());
$subtract = mysql_result($query, 0);
$subtract++;

$count = mysql_query("SELECT type, fup FROM $table_forums WHERE fid='$fid'") or die(mysql_error());
$for = mysql_fetch_array($query);

if($status == "Administrator" || $status == "Moderator" || ($username == $origauthor && $subtract == 0)) {
mysql_query("UPDATE $table_forums SET threads=threads-1, posts=posts-'$subtract' WHERE fid='$fid'") or die(mysql_error());

if($for[type] == "sub") {
mysql_query("UPDATE $table_forums SET threads=threads-1, posts=posts-'$subtract' WHERE fid='$for[fup]'") or die(mysql_error());
}

$query = mysql_query("SELECT author FROM $table_posts WHERE tid='$tid'") or die(mysql_error());
while($result = mysql_fetch_array($query)) {
mysql_query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$result[author]'") or die(mysql_error());
}

mysql_query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$origauthor'") or die(mysql_error());
mysql_query("DELETE FROM $table_threads WHERE tid='$tid'") or die(mysql_error());
mysql_query("DELETE FROM $table_posts WHERE tid='$tid'") or die(mysql_error());
}
}
} else {
echo "<span class=\"12px \">$lang[noedit]</span>";
exit;
}

echo "<span class=\"12px \">$lang[editpostmsg]</span>";
?>
<script>
function redirect()
{
window.location.replace("forumdisplay.php?fid=<?=$fid?>");
}
setTimeout("redirect();", 1250);
</script>
<?
}
}

$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
if($showtotaltime != "off") {
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);
}

$html = template("footer.html");
eval("echo stripslashes(\"$html\");");
?>

