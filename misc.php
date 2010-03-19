<?
require "header.php";

if($action == 'login') {
if(!$loginsubmit) {

$miscaction = $lang[textlogin];
misctemplate($miscaction, $lastvisita, $thisuser, $cplink, $lastvisittext, $langfile);
?>

<form method="post" action="misc.php?action=login">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?=$lang[textlogin]?></td>
</tr>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>" width="22%"><?=$lang[textusername]?></td>
<td bgcolor="<?=$altbg2?>"><input type="text" name="username" size="30" maxlength="40" /> &nbsp;<span class="11px"><a href="member.php?action=reg"><?=$lang[regques]?></a></span></td>
</tr>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>"><?=$lang[textpassword]?></td>
<td bgcolor="<?=$altbg2?>"><input type="password" name="password" size="25" /> &nbsp;<span class="11px"><a href="misc.php?action=lostpw"><?=$lang[forgotpw]?></a></span><br /></td>
</tr>

<tr class="tablerow">
<td bgcolor="<?=$altbg1?>"><?=$lang[textdayslog]?></td>
<td bgcolor="<?=$altbg2?>"><input type="text" name="dayslog" size="3" value="10" /></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="loginsubmit" value="<?=$lang[textlogin]?>" /></center>
</form>

<?
}

if($loginsubmit) {
$query = mysql_query("SELECT * FROM $table_members WHERE username='$username'") or die(mysql_error());
$member = mysql_fetch_array($query);

if(!$member[username]) {
echo "$lang[badname]";
exit;
}

if($password != $member[password]) {
echo "$lang[textpwincorrect]";
exit;
}

$currtime = time() + (86400*$dayslog);
$username = $member[username];

setcookie("thisuser", $username, $currtime, "/");
setcookie("thispw", $password, $currtime, "/");

$miscaction = $lang[textlogin];
misctemplate($miscaction, $lastvisita, $thisuser, $cplink, $lastvisittext, $langfile);
echo "<span class=\"12px \">$lang[successlogin]</span>";
?>
<script>
function redirect()
{
window.location.replace("index.php");
}
setTimeout("redirect();", 1250);
</script>
<?
}
}

if($action == 'logout') {

$currtime = time() - (86400*3);

setcookie("thisuser", $username, $currtime, "/");
setcookie("thispw", $password, $currtime, "/");

$miscaction = $lang[textlogout];
misctemplate($miscaction, $lastvisita, $thisuser, $cplink, $lastvisittext, $langfile);

echo "<span class=\"12px \">$lang[successlogout]</span>";
?>
<script>
function redirect()
{
window.location.replace("index.php");
}
setTimeout("redirect();", 1250);
</script>
<?
}

if($action == 'faq') {
$miscaction = $lang[textfaq];
misctemplate($miscaction, $lastvisita, $thisuser, $cplink, $lastvisittext, $langfile);

if($faqstatus != "on") {
echo $lang[faqoff];
exit;
}

$query = mysql_query("SELECT * FROM $table_ranks ORDER BY posts") or die(mysql_error());
while($ranks = mysql_fetch_array($query)) {
for($i = 0; $i < $ranks[stars]; $i++) {
$stars .= "<img src=\"images/star.gif\">";
}
$allranks .= "<tr><td bgcolor=\"$altbg2\">$ranks[title]</td><td bgcolor=\"$altbg2\">$stars</td><td bgcolor=\"$altbg2\">$ranks[posts] $lang[memposts]</td></tr>\n";
$stars = "";
}
?>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?=$lang[textfaq]?></td>
</tr>

<tr><td bgcolor="<?=$altbg2?>" class="tablerow">
<a name="bbcode"></a>
<b><?=$lang[textbbcode]?></b><br />
<?=$lang[bbcodeinfo]?><p>&nbsp;</p>

<b><?=$lang[textuserranks]?></b><br />
<?=$lang[rankinfo]?><br /><br />

<table cellspacing="0" cellpadding="0" border="0" width="25%">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="2" width="100%">
<?=$allranks?></table>
</td></tr><table></table>
</td></tr>

<?
}

if($action == 'search') {
$miscaction = $lang[textsearch];
misctemplate($miscaction, $lastvisita, $thisuser, $cplink, $lastvisittext, $langfile);

if($searchstatus != "on") {
echo $lang[searchoff];
exit;
}

if(!$searchsubmit) {

$forumselect = "<select name=\"srchfid\">\n";
$forumselect .= "<option value=\"all\">$lang[textall]</option>\n";
$queryforum = mysql_query("SELECT * FROM $table_forums WHERE type='forum'") or die(mysql_error());
while($forum = mysql_fetch_array($queryforum)) {

$authorization = privfcheck($hideprivate, $status, $forum[private], $thisuser, $forum[userlist]);

if($authorization == "true") {
$forumselect .= "<option value=\"$forum[fid]\">$forum[name]</option>\n";
}
}
$forumselect .= "</select>";

?>

<form method="post" action="misc.php?action=search">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?=$lang[textsearch]?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow" width="22%"><?=$lang[textsearchfor]?></td>
<td bgcolor="<?=$altbg2?>"><input type="text" name="srchtxt" size="30" maxlength="40" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow" width="22%"><?=$lang[textsrchuname]?></td>
<td bgcolor="<?=$altbg2?>"><input type="text" name="srchuname" size="30" maxlength="40" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow" width="22%"><?=$lang[srchbyforum]?></td>
<td bgcolor="<?=$altbg2?>"><?=$forumselect?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow" width="22%"><?=$lang[textlfrom]?></td>
<td bgcolor="<?=$altbg2?>"><select name="srchfrom">
<option value="86400"><?=$lang[day1]?></option>
<option value="604800"><?=$lang[aweek]?></option>
<option value="2592000"><?=$lang[month1]?></option>
<option value="7948800"><?=$lang[month3]?></option>
<option value="15897600"><?=$lang[month6]?></option>
<option value="31536000"><?=$lang[lastyear]?></option>
<option value="0" selected="selected"><?=$lang[beginning]?></option>
</select></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow" width="22%"><?=$lang[textsearchin]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="radio" name="srchin" value="reply"><?=$lang[repliesl]?> <input type="radio" name="srchin" value="topic"><?=$lang[topicsl]?> <input type="radio" name="srchin" value="both" checked="checked"><?=$lang[textboth]?></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="searchsubmit" value="<?=$lang[textsearch]?>" /></center>
</form>

<?
}

if($searchsubmit) {
?>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="3"><?=$lang[textsearch]?></td>
</tr>

<?
$sql1 = "SELECT * FROM $table_threads";
$sql2 = "SELECT * FROM $table_posts";

if($srchfrom == "0") {
$srchfrom = time();
}

$srchfrom = time() - $srchfrom;

if($srchtxt) {
$sql1 .= " WHERE (message LIKE '%$srchtxt%' OR subject LIKE '%$srchtxt%') AND lastpost >= '$srchfrom'";
$sql2 .= " WHERE message LIKE '%$srchtxt%' AND dateline >= '$srchfrom'";
}
elseif($srchtxt == "" && $srchuname == "" && $srchfid != "all" || $srchfid == "") {
$sql1 .= " WHERE fid='$srchfid' AND lastpost >= '$srchfrom'";
$sql2 .= " WHERE fid='$srchfid' AND dateline >= '$srchfrom'";
}
elseif($srchtxt == "" && $srchuname != "") {
if($srchfid == "all" || $srchfid != "") {
$sql1 .= " WHERE author='$srchuname' AND lastpost >= '$srchfrom'";
$sql2 .= " WHERE author='$srchuname' AND dateline >= '$srchfrom'";
}
}

if($srchfid != "all" && $srchtxt != "" && $srchuname == "") {
$sql1 .= " AND fid='$srchfid' AND lastpost >= '$srchfrom'";
$sql2 .= " AND fid='$srchfid' AND dateline >= '$srchfrom'";
}
elseif($srchuname != "" && $srchfid != "all" && $srchtxt != "") {
$sql1 .= " AND fid='$srchfid' AND lastpost >= '$srchfrom'";
$sql2 .= " AND fid='$srchfid' AND dateline >= '$srchfrom'";
}

if($srchtxt != "" && $srchuname != "") {
$sql1 .= " AND author='$srchuname' AND lastpost >= '$srchfrom'";
$sql2 .= " AND author='$srchuname' AND dateline >= '$srchfrom'";
}

$query1 = mysql_query($sql1) or die(mysql_error());
$query2 = mysql_query($sql2) or die(mysql_error());

if($srchin == "both" || $srchin == "topic") {
$threadcount = mysql_num_rows($query1);
while($thread = mysql_fetch_array($query1)) {
$date = date("$dateformat",$thread[dateline]);
$time = date("$timecode",$thread[dateline]);
$poston = "$date $lang[textat] $time";
$thread[subject] = stripslashes($thread[subject]);

$queryforum = mysql_query("SELECT * FROM $table_forums WHERE type='forum' AND fid='$thread[fid]'") or die(mysql_error());
$forum = mysql_fetch_array($queryforum);

if($forum[private] == "staff" && $modmin == "true") {
$authorization = "true";
}
elseif($forum[private] != "staff") {
$authorization = "true";
}
elseif($forum[userlist] != "") {

if(eregi("(^|[:space:])".$thisuser."(,|$)", $forum[userlist])) {
$authorization = "true";
}
else {
$authorization = "no";
}

}
else {
$authorization = "no";
}

if($authorization == "true") {
?>
<tr class="tablerow">
<td bgcolor="<?=$altbg1?>"><a href="viewthread.php?tid=<?=$thread[tid]?>"><?=$thread[subject]?></a></td>
<td bgcolor="<?=$altbg2?>"><?=$lang[texttopic]?></td>
<td bgcolor="<?=$altbg1?>"><?=$poston?></td>
</tr>
<?
}
}
}

if($srchin != "both" || $srchin == "reply") {
$postcount = mysql_num_rows($query2);
while($post = mysql_fetch_array($query2)) {
$date = date("$dateformat",$post[dateline]);
$time = date("$timecode",$post[dateline]);
$poston = "$date $lang[textat] $time";

$infoquery = mysql_query("SELECT * FROM $table_threads WHERE tid='$post[tid]'") or die(mysql_error());
$threadinfo = mysql_fetch_array($infoquery);
$threadinfo[subject] = stripslashes($threadinfo[subject]);


$queryforum = mysql_query("SELECT * FROM $table_forums WHERE type='forum' AND fid='$post[fid]'") or die(mysql_error());
$forum = mysql_fetch_array($queryforum);

if($forum[private] == "staff" && $modmin == "true") {
$authorization = "true";
}
elseif($forum[private] != "staff") {
$authorization = "true";
}
elseif($forum[userlist] != "") {

if(eregi("(^|[:space:])".$thisuser."(,|$)", $forum[userlist])) {
$authorization = "true";
}
else {
$authorization = "no";
}

}
else {
$authorization = "no";
}

if($authorization == "true") {
?>
<tr class="tablerow">
<td bgcolor="<?=$altbg1?>"><a href="viewthread.php?tid=<?=$threadinfo[tid]?>#pid<?=$post[pid]?>"><?=$threadinfo[subject]?></a></td>
<td bgcolor="<?=$altbg2?>"><?=$lang[textreply]?></td>
<td bgcolor="<?=$altbg1?>"><?=$poston?></td>
</tr>
<?
}
}
}

if($threadcount == "0" && $postcount == "0") {
echo "<tr><td bgcolor=\"$altbg1\" colspan=\"3\"><span class=\"12px \">$lang[noresults]</span></td></tr>";
}

}
}

if($action == 'lostpw') {
if(!$lostpwsubmit) {

$miscaction = $lang[textlostpw];
misctemplate($miscaction, $lastvisita, $thisuser, $cplink, $lastvisittext, $langfile);
?>

<form method="post" action="misc.php?action=lostpw">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?=$lang[textlostpw]?></td>
</tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td width="22%"><?=$lang[textusername]?></td>
<td><input type="text" name="username" size="30" maxlength="40" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textemail]?></td>
<td><input type="text" name="email" size="25" /><br />
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="lostpwsubmit" value="<?=$lang[textsubmit]?>" /></center>
</form>

<?
}

if($lostpwsubmit) {
$query = mysql_query("SELECT * FROM $table_members WHERE username='$username' OR email='$email'") or die(mysql_error());
$member = mysql_fetch_array($query);

if(!$member[username] || !$member[email]) {
echo "$lang[badinfo]";
exit;
}

$miscaction = $lang[textlostpw];
misctemplate($miscaction, $lastvisita, $thisuser, $cplink, $lastvisittext, $langfile);

mail("$member[email]", "$lang[textyourpw]", "$lang[textyourpwis]\n\n$member[username]\n$member[password]", "$lang[textfrom] $adminemail");
echo "<span class=\"12px \">$lang[emailpw]</span>";
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

$miscaction = $lang[whosonline];
misctemplate($miscaction, $lastvisita, $thisuser, $cplink, $lastvisittext, $langfile);
?>
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td><?=$lang[textusername]?></td>
<td><?=$lang[texttime]?></td>
<td><?=$lang[textlocation]?></td>

<?
if($status == "Administrator") {
echo "<td>$lang[textipaddress]</td>";
}
?>
</tr>
<?
$query = mysql_query("SELECT * FROM $table_whosonline WHERE username != 'onlinerecord' ORDER BY time DESC") or die(mysql_error());
while ($online = mysql_fetch_array($query)){
$onlinetime = gmdate("$timecode",$online[time] + ($timeoffset * 3600));

$username = str_replace("xguest123", "$lang[textguest1]", $online[username]);

if($online[username] != "xguest123") {
$online[username] = "<a href=\"member.php?action=viewpro&member=$online[username]\">$username</a>";
}
else {
$online[username] = $username;
}
?>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td width="22%"><?=$online[username]?></td>
<td width="28%"><?=$onlinetime?></td>
<td><?=$online[location]?></td>

<?
if($status == "Administrator") {
echo "<td>$online[ip]</td>";
}
?>
</tr>
<?
}
echo "</table>
</td></tr></table>";
}


if($action == "list") {
$miscaction = $lang[textmemberlist];
misctemplate($miscaction, $lastvisita, $thisuser, $cplink, $lastvisittext, $langfile);

if($memliststatus != "on") {
echo $lang[memlistoff];
exit;
}
?>
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr>
<td width="20%" class="header"><?=$lang[textusername]?></td>
<td width="10%" class="header"><?=$lang[textemail]?></td>
<td width="10%" class="header"><?=$lang[textsite]?></td>
<td class="header"><?=$lang[textlocation] ?></td>
<td class="header"><?=$lang[textregistered]?></td>
<td class="header"><?=$lang[textposts]?></td>
</tr>

<?
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
$query = mysql_query("SELECT count(uid) FROM $table_members") or die(mysql_error());
} else {
$query = mysql_query("SELECT count(uid) FROM $table_members WHERE username LIKE '%$srchmem%'") or die(mysql_error());
}
$num = mysql_result($query,0);

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
$querymem = mysql_query("SELECT * FROM $table_members ORDER BY $order $desc LIMIT $start_limit, $memberperpage") or die(mysql_error());
} else {
$querymem = mysql_query("SELECT * FROM $table_members WHERE username LIKE '%$srchmem%' ORDER BY $order $desc LIMIT $start_limit, $memberperpage") or die(mysql_error());
}

while ($member = mysql_fetch_array($querymem)) {

$member[regdate] = date("n/j/y",$member[regdate]);

if($member[email] != "" && $member[showemail] == "yes") {
$email = "<a href=\"mailto:$member[email]\"><img src=\"images/email.gif\" border=\"0\" alt=\"E-mail User\" /></a>";
} else {
$email = "&nbsp;";
}

$member[site] = str_replace("http://", "", $member[site]);
$member[site] = "http://$member[site]";

if($member[site] == "http://") {
$site = "&nbsp;";
}
else {
$site = "<a href=\"$member[site]\"><img src=\"images/site.gif\" border=\"0\" alt=\"Visit User's Homepage\" /></a>";
}

if($member[location] == "") {
$member[location] = "&nbsp;";
}
?>
<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><a href="member.php?action=viewpro&member=<?=rawurlencode($member[username])?>"><?=$member[username]?></a></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$email?></td>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$site?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$member[location]?></td>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$member[regdate]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$member[postnum]?></td>
</tr>
<?
}
?>

<form method="post" action="misc.php?action=list">
<tr class="tablerow">
<td bgcolor="<?=$altbg2?>" colspan="6">
<span class="12px"><?=$lang[textsrchusr]?></span> <input type="text" size="15" name="srchmem"> <input type="submit" value="<?=$lang[textgo]?>" />

&nbsp;&nbsp;&nbsp;&nbsp;<b><?=$lang[textor]?></b>&nbsp;&nbsp;&nbsp;&nbsp;
<?=$lang[textsortby]?>
<a href="misc.php?action=list&order=postnum&desc=desc"><?=$lang[textpostnum]?></a> -
<a href="misc.php?action=list&order=username"><?=$lang[textalpha]?></a> -
<a href="misc.php?action=list"><?=$lang[textregdate]?></a></td></tr></form>

</table>
</td></tr></table>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td align="right" class="multi"><?=$multipage?></td></tr></table>

<?
}


if($action == "stats") {
$miscaction = $lang[textstats];
misctemplate($miscaction, $lastvisita, $thisuser, $cplink, $lastvisittext, $langfile);

$query = mysql_query("SELECT COUNT(*) FROM $table_threads") or die(mysql_error());
$threads = mysql_result($query, 0);

$query = mysql_query("SELECT COUNT(*) FROM $table_posts") or die(mysql_error());
$posts = mysql_result($query, 0);

$query = mysql_query("SELECT COUNT(*) FROM $table_forums WHERE type='forum'") or die(mysql_error());
$forums = mysql_result($query, 0);

$query = mysql_query("SELECT COUNT(*) FROM $table_forums WHERE type='forum' AND status='on'") or die(mysql_error());
$forumsa = mysql_result($query, 0);

$query = mysql_query("SELECT COUNT(*) FROM $table_members") or die(mysql_error());
$members = mysql_result($query, 0);

$query = mysql_query("SELECT COUNT(*) FROM $table_members WHERE postnum!='0'") or die(mysql_error());
$membersact = mysql_result($query, 0);

$mapercent = $membersact*100/$members;
$mapercent  = number_format($mapercent, 2);
$mapercent .= "%";


if($status == "Administrator" || $status == "Super Moderator" || $status == "Moderator") {
$privauth = "yes";
} else {
$privauth = "no";
}

$query = mysql_query("SELECT fid, userlist FROM $table_forums WHERE private != '' || userlist != ''") or die(mysql_error());
while($priv = mysql_fetch_array($query)) {

if(eregi($thisuser."(,|$)", $priv[userlist]) && $thisuser != "") {
$auth = "yes";
} elseif($privauth == "yes" && $priv[userlist] == "") {
$auth = "yes";
} else {
$auth = "no";
}

if(!$auth || $auth != "yes") {
if(!$addon || $addon == "") {
$addon = "WHERE fid != '$priv[fid]'";
} else {
$addon .= " && fid != '$priv[fid]'";
}
}
}

$query = mysql_query("SELECT views, tid, subject FROM $table_threads $addon ORDER BY views DESC LIMIT 0, 5") or die(mysql_error());
while($views = mysql_fetch_array($query)) {
$viewmost .= "<a href=\"viewthread.php?tid=$views[tid]\">$views[subject]</a> ($views[views] $lang[viewsl])<br />";
}

$query = mysql_query("SELECT replies, tid, subject FROM $table_threads $addon ORDER BY replies DESC LIMIT 0, 5") or die(mysql_error());
while($reply = mysql_fetch_array($query)) {
$replymost .= "<a href=\"viewthread.php?tid=$reply[tid]\">$reply[subject]</a> ($reply[replies] $lang[repliesl])<br />";
}

$query = mysql_query("SELECT lastpost, tid, subject FROM $table_threads $addon ORDER BY lastpost DESC LIMIT 0, 5 ") or die(mysql_error());
while($last = mysql_fetch_array($query)) {
$lpdate = date("$dateformat", $last[lastpost] + ($timeoffset * 3600));
$lptime = date("$timecode", $last[lastpost] + ($timeoffset * 3600));
$thislast = "$lang[lpoststats] $lang[lastreply1] $lpdate $lang[textat] $lptime";
$latest .= "<a href=\"viewthread.php?tid=$last[tid]\">$last[subject]</a> ($thislast)<br />";
}

$query = mysql_query("SELECT posts, threads, fid, name FROM $table_forums ORDER BY posts DESC LIMIT 0, 1") or die(mysql_error());
$pop = mysql_fetch_array($query);
$popforum = "<a href=\"forumdisplay.php?fid=$pop[fid]\">$pop[name]</a>";
$pop[posts] += $pop[threads];
$posts += $threads;


$mempost = 0;
$query = mysql_query("SELECT postnum FROM $table_members") or die(mysql_error());
while ($mem = mysql_fetch_array($query)) {
$mempost += $mem[postnum];
}

$mempost = $mempost / $members;
$mempost  = number_format($mempost, 2);


$forumpost = 0;
$query = mysql_query("SELECT posts FROM $table_forums") or die(mysql_error());
while ($forum = mysql_fetch_array($query)) {
$forumpost += $forum[posts];
}

$forumpost = $forumpost / $forums;
$forumpost  = number_format($forumpost, 2);


$threadreply = 0;
$query = mysql_query("SELECT replies FROM $table_threads") or die(mysql_error());
while($thread = mysql_fetch_array($query)) {
$threadreply += $thread[replies];
}

$threadreply = $threadreply / $threads;
$threadreply  = number_format($threadreply, 2);


$query = mysql_query("SELECT dateline FROM $table_threads ORDER BY dateline LIMIT 0, 1") or die(mysql_error());
$postdays = mysql_result($query, 0);

$postsday = $posts / ((time() - $postdays) / 86400);
$postsday  = number_format($postsday, 2);


$query = mysql_query("SELECT regdate FROM $table_members ORDER BY regdate LIMIT 0, 1") or die(mysql_error());
$memberdays = mysql_result($query, 0);

$membersday = $members / ((time() - $memberdays) / 86400);
$membersday  = number_format($membersday, 2);

eval($lang[evalstats1]);
eval($lang[evalstats2]);
eval($lang[evalstats3]);
eval($lang[evalstats4]);
eval($lang[evalstats5]);
eval($lang[evalstats6]);
eval($lang[evalstats7]);
eval($lang[evalstats8]);
eval($lang[evalstats9]);
eval($lang[evalstats10]);
eval($lang[evalstats11]);
eval($lang[evalstats12]);
eval($lang[evalstats13]);
eval($lang[evalstats14]);
eval($lang[evalstats15]);
?>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr><td class="header"><?=$lang[textstats]?></td></tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"><td>
<b><?=$lang[stats1]?></b><br />
<?=$lang[stats2]?><br />
<?=$lang[stats3]?><br />
<?=$lang[stats4]?><br />
<?=$lang[stats5]?><br /><br />
<?=$lang[stats6]?><br /><br />
<?=$lang[stats7]?><br /><br />
<?=$lang[stats14]?><br /><br />
<?=$lang[stats8]?><br /><br />

<b><?=$lang[textaverages]?></b></br>
<?=$lang[stats9]?><br />
<?=$lang[stats10]?><br />
<?=$lang[stats11]?><br />
<?=$lang[stats12]?><br />
<?=$lang[stats13]?><br />
<?=$lang[stats15]?><br />
</td></tr>

</table>
</td></tr></table>

<?
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
