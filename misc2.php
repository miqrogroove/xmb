<?
require "functions.php";
require "settings.php";
require "config.php";

mysql_connect($dbhost, $dbuser, $dbpw) or die(mysql_error());
mysql_select_db($dbname) or die(mysql_error());

$tables = array('announce','banned','forums', 'members', 'posts', 'ranks', 'smilies', 'themes', 'threads', 'u2u', 'whosonline', 'words');
foreach($tables as $name) {
${'table_'.$name} = $tablepre.$name;
}

if($nocacheheaders == "on") { 
header("Expires: ".gmdate("D, d M Y H:i:s")."GMT"); 
header("Cache-Control: no-cache, must-revalidate"); 
header("Pragma: no-cache"); 
} 

$bblang = $langfile;
if($thisuser && $thisuser != '') {
$query = mysql_query("SELECT * FROM $table_members WHERE username='$thisuser'") or die(mysql_error());
$this = mysql_fetch_array($query);
if($this[langfile] != "") {
$langfile = $this[langfile];
}
$timeoffset = $this[timeoffset];
$status = $this[status];
$themeuser = $this[theme];
$memtime = $this[timeformat];
$memdate = $this[dateformat];
$theme = $themeuser;
if($this[password] == $thispw) {
$thisuser = $thisuser;
}
else {
$thisuser = "";
$theme = $theme;
}
}
require "lang/$langfile.lang.php";

$query = mysql_query("SELECT * FROM $table_themes WHERE name='$theme'");
foreach(mysql_fetch_array($query) as $key => $val) {
if($key != "name") {
$$key = $val;
}
}

if($memtime == "") {
if($timeformat == "24") {
$timecode = "H:i";
} else {
$timecode = "h:i A";
}
} else {
if($memtime == "24") {
$timecode = "H:i";
} else {
$timecode = "h:i A";
}
}

if($memdate == "") {
$dateformat = $dateformat;
}
else {
$dateformat = $memdate;
}

$dateformat = eregi_replace("mm", "n", $dateformat);
$dateformat = eregi_replace("dd", "j", $dateformat);
$dateformat = eregi_replace("yyyy", "Y", $dateformat);
$dateformat = eregi_replace("yy", "y", $dateformat);

if(!$thisuser || !$thispw) {
$thisuser = "";
$status = "";
}
?>
<html>
<head>
<style type="text/css">
body {
background-color: <?=$bgcolor?>;
}

a {
color: <?=$link?>;
text-decoration: none;
}

a:hover {
text-decoration: underline;
}

.header {
color: <?=$headertext?>;
background-color: <?=$header?>;
font-family: <?=$altfont?>;
font-weight: bold;
font-size: <?=$altfontsize?>;
}

.15px {
font-size: <?=$font3?>;
font-family: <?=$font?>;
}

.tablerow {
font-family: <?=$font?>;
color: <?=$tabletext?>;
font-size: <?=$fontsize?>;
}

.11px {
font-size: <?=$font1?>;
font-family: <?=$font?>;
}

.12px {
font-size: <?=$fontsize?>;
font-family: <?=$font?>;
}
</style>
<title><?=$bbname?> - <?=$lang[textpowered]?></title>
</head>
<body text="<?=$text?>">

<?
if($action1 == "u2u") {

if($thisuser == "") {
echo "$lang[u2unotloggedin]";
exit;
}
?>

<div align="center">

<font class="12px">
<a href="misc2.php?action1=u2u"><?=$lang[textu2uinbox]?></a> - 
<a href="misc2.php?action1=u2u&action=outbox"><?=$lang[textu2uoutbox]?></a> - 
<a href="misc2.php?action1=u2u&action=send"><?=$lang[textsendu2u]?></a> - 
<a href="misc2.php?action1=u2u&action=ignore"><?=$lang[ignorelist]?></a>
</font><br /><br />
<?

if(!$action || $action == "") {
?>
<form method="post" action="misc2.php?action1=u2u&action=delete">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr> <td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr><td class="header" colspan="4" align="center"><?=$lang[textu2uinbox]?></td></tr>
<tr>
<td class="header"><?=$lang[textdeleteques]?></td>
<td class="header"><?=$lang[textsubject]?></td>
<td class="header"><?=$lang[textfrom]?></td>
<td class="header"><?=$lang[textsent]?></td>
</tr>
<?

$query = mysql_query("SELECT * FROM $table_u2u WHERE msgto='$thisuser' AND folder='inbox' ORDER BY dateline DESC") or die(mysql_error());

while($message = mysql_fetch_array($query)) {

$postdate = date("$dateformat",$message[dateline] + ($timeoffset * 3600));
$posttime = date("$timecode",$message[dateline] + ($timeoffset * 3600));

$senton = "$postdate $lang[textat] $posttime";

if($message[subject] == "") {
$message[subject] = "&lt;$lang[textnosub]&gt;";
}
?>
<tr>
<td class="tablerow" bgcolor="<?=$altbg1?>"><input type="checkbox" name="delete<?=$message[u2uid]?>" value="<?=$message[u2uid]?>" /></td>
<td class="tablerow" bgcolor="<?=$altbg2?>"><a href="misc2.php?action1=u2u&action=view&u2uid=<?=$message[u2uid]?>"><?=$message[subject]?></a></td>
<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$message[msgfrom]?></td>
<td class="tablerow" bgcolor="<?=$altbg2?>"><span class="11px"><?=$senton?></span></td>
</tr>
<?
}
?>
</table>
</td></tr></table>
<input type="submit" name="alldel" value="<?=$lang[textsubmitchanges]?>">
</form>
<?
}

if($action == "send") {
$query = mysql_query("SELECT count(u2uid) FROM $table_u2u WHERE msgto='$thisuser' AND folder='inbox'") or die(mysql_error());
$u2uinboxnum = mysql_result($query, 0);
$query = mysql_query("SELECT count(u2uid) FROM $table_u2u WHERE msgfrom='$thisuser' AND folder='outbox'") or die(mysql_error());
$u2uoutboxnum = mysql_result($query, 0);
$u2unum = $u2uinboxnum + $u2uoutboxnum;
if($u2unum >= $u2uquota) {
echo "<span class=\"12px \">$lang[u2ureachedquota]</span>";
} else {
if(!$u2usubmit) {
$touser = $username;
if($u2uid) {
$query = mysql_query("SELECT * FROM $table_u2u WHERE u2uid='$u2uid'") or die(mysql_error());
$u2u = mysql_fetch_array($query);

if($do == "reply") {
$subject = "$lang[textre] $u2u[subject]";
$message = "[quote]$u2u[message][/quote]";
$touser = "$u2u[msgfrom]";
}
if($do == "forward") {
$subject = "$lang[textfwd] $u2u[subject]";
$message = "[quote]$u2u[message][/quote]";
$touser = "$u2u[msgfrom]";
}
}
?>

<form method="post" action="misc2.php?action1=u2u&action=send">

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr> <td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr>
<td width="100%" colspan="2" class="header"><?=$lang[imtextsendto]?> <?=$touser?></td>
</tr>

<tr bgcolor="<?=$altbg1?>">
<td class="tablerow"><?=$lang[textsendto]?></td>
<td class="tablerow"><input type="text" name="msgto" size="20" value="<?=$touser?>" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang[textusername]?></td>
<td class="tablerow"><input type="text" name="username" size="20" value="<?=$thisuser?>" /></td>
</tr>

<tr bgcolor="<?=$altbg1?>">
<td class="tablerow"><?=$lang[textpassword]?></td>
<td class="tablerow"><input type="password" name="password" size="20" value="<?=$thispw?>" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang[textsubject]?></td>
<td class="tablerow"><input type="text" name="subject" size="35" value="<?=$subject?>" /></td>
</tr>

<tr bgcolor="<?=$altbg1?>">
<td valign="top" class="tablerow"><?=$lang[textmessage]?></td>
<td class="tablerow"><textarea rows="6" name="message" cols="35"><?=$message?></textarea><BR>
<input type="checkbox" name="saveoutbox" value="yes" /><?=$lang[saveinoutbox]?></td>
</tr>

</table>
</td></tr></table>
<input type="submit" name="u2usubmit" value="<?=$lang[textsendu2u]?>">
</form>

<?
}

if($u2usubmit) {

$query = mysql_query("SELECT username FROM $table_members WHERE username='$msgto'") or die(mysql_error());
$member = mysql_fetch_array($query);
if(!$member[username]) {
echo "<span class=\"12px \">$lang[badrcpt]</span>";
exit;
}

$msgto = $member[username];

$query = mysql_query("SELECT username, password FROM $table_members WHERE username='$username'") or die(mysql_error());
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

$query = mysql_query("SELECT ignoreu2u FROM $table_members WHERE username='$msgto'") or die(mysql_error());
$list = mysql_fetch_array($query);

if(eregi($username."(,|$)", $list[ignoreu2u])) {
echo "<span class=\"12px \">$lang[u2ublocked]</span>";
exit;
}


$subject = str_replace("<","&lt;", $subject);
$subject = str_replace(">","&gt;", $subject);
$message = str_replace("<","&lt;", $message);
$message = str_replace(">","&gt;", $message);

mysql_query("INSERT INTO $table_u2u VALUES('', '$msgto', '$username', '" . time() . "', '$subject', '$message', 'inbox')") or die(mysql_error());
if($saveoutbox == "yes") {
mysql_query("INSERT INTO $table_u2u VALUES('', '$msgto', '$username', '" . time() . "', '$subject', '$message', 'outbox')") or die(mysql_error());
}
echo "<span class=\"12px \">$lang[imsentmsg]</span>";
?>
<script> 
function redirect()
{ 
window.location.replace("misc2.php?action1=u2u"); 
} 
setTimeout("redirect();", 1250); 
</script>
<?
}
}
}

if($action == "delete") {
if(!$alldel) {
$query = mysql_query("DELETE FROM $table_u2u WHERE u2uid='$u2uid'") or die(mysql_error());
}
else {
$query = mysql_query("SELECT * FROM $table_u2u WHERE msgto='$thisuser' ORDER BY dateline DESC") or die(mysql_error());

while($u2u = mysql_fetch_array($query)) {
$delete = "delete$u2u[u2uid]";
$delete = "${$delete}";
mysql_query("DELETE FROM $table_u2u WHERE msgto='$thisuser' AND u2uid='$delete'") or die(mysql_error());
}

}

echo "<span class=\"12px \">$lang[imdeletedmsg]</span>";

?>
<script>
function redirect() {
window.location.replace("misc2.php?action1=u2u&action=<?=$backto?>");
}
setTimeout("redirect();", 1250);
</script>
<?
}


if($action == "outbox") {
?>
<form method="post" action="misc2.php?action1=u2u&action=delete">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr> <td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr><td class="header" colspan="4" align="center"><?=$lang[textu2uoutbox]?></td></tr>
<tr>
<td class="header"><?=$lang[textdeleteques]?></td>
<td class="header"><?=$lang[textsubject]?></td>
<td class="header"><?=$lang[textto]?></td>
<td class="header"><?=$lang[textsent]?></td>
</tr>
<?

$query = mysql_query("SELECT * FROM $table_u2u WHERE msgfrom='$thisuser' AND folder='outbox' ORDER BY dateline DESC") or die(mysql_error());

while($message = mysql_fetch_array($query)) {

$postdate = date("$dateformat",$message[dateline] + ($timeoffset * 3600));
$posttime = date("$timecode",$message[dateline] + ($timeoffset * 3600));

$senton = "$postdate $lang[textat] $posttime";

if($message[subject] == "") {
$message[subject] = "&lt;$lang[textnosub]&gt;";
}
?>
<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><input type="checkbox" name="delete<?=$message[u2uid]?>" value="<?=$message[u2uid]?>" /></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><a href="misc2.php?action1=u2u&action=view&u2uid=<?=$message[u2uid]?>"><?=$message[subject]?></a></td>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$message[msgto]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow" class="11px"><?=$senton?></td>
</tr>
<?
}
?>
</table>
</td></tr></table>
<input type="submit" name="alldel" value="<?=$lang[textsubmitchanges]?>">
<input type="hidden" name="backto" value="outbox">
</form>
<?
}


if($action == "ignore") {
$query = mysql_query("SELECT ignoreu2u, username FROM $table_members WHERE username='$thisuser'") or die(mysql_error());
$mem = mysql_fetch_array($query);
?>
<form method="post" action="misc2.php?action1=u2u&action=ignoresubmit">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr><td class="header" colspan="2"><?=$lang[ignorelist]?></td></tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[ignoremsg]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><textarea rows="5" cols="30" name="ignorelist"><?=$mem[ignoreu2u]?></textarea></td>
</tr>

</table>
</td></tr></table>
<input type="submit" value="<?=$lang[textsubmitchanges]?>">
<input type="hidden" name="listof" value="<?=$mem[username]?>">
</form>
<?
}

if($action == "ignoresubmit") {
mysql_query("UPDATE $table_members SET ignoreu2u='$ignorelist' WHERE username='$listof'") or die(mysql_error());
echo "<span class=\"12px \">$lang[ignoreupdate]</span>";
}


if($action == "view") {
$query = mysql_query("SELECT * FROM $table_u2u WHERE u2uid='$u2uid'") or die(mysql_error());
$u2u = mysql_fetch_array($query);

if(($u2u[msgfrom] == $thisuser) || ($u2u[msgto] == $thisuser)) {

$u2udate = date("$dateformat",$u2u[dateline] + ($timeoffset * 3600));
$u2utime = date("$timecode",$u2u[dateline] + ($timeoffset * 3600));
$dateline = "$u2udate $lang[textat] $u2utime";
$u2u[subject] = "$lang[textsubject] $u2u[subject]";
$u2u[message] = postify($u2u[message], "no", "", "", $bordercolor, "", "", $table_words, $table_forums, $table_smilies);
?>
<form method="post" action="misc2.php?action1=u2u&action=delete">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr>
<td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr>
<td  class="header" colspan="2"><?=$u2u[subject]?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textfrom]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$u2u[msgfrom]?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textto]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$u2u[msgto]?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textsent]?></td>
<td bgcolor="<?=$altbg2?>" class="11px"><?=$dateline?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textmessage]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$u2u[message]?></td>
</tr>

<? 
if($u2u[msgfrom] != $thisuser) { 
?>
<tr>
<td bgcolor="<?=$altbg1?>" colspan="2" class="tablerow"><a href="misc2.php?action1=u2u&action=send&u2uid=<?=$u2uid?>&do=reply"><?=$lang[textreply]?></a> - <a href="misc2.php?action1=u2u&action=send&u2uid=<?=$u2uid?>&do=forward"><?=$lang[textforward]?></a></td>
</tr>
<? 
} 
?>

</table>
</center></div></td>
</tr>
</table>
<input type="hidden" name="u2uid" value="<?=$u2uid?>">
<input type="submit" name="imsubmit" value="<?=$lang[deletebutton]?>"></form>
<?
}
}
?>

<br /><br /><font class="12px">
<a href="misc2.php?action1=u2u"><?=$lang[textu2uinbox]?></a> - 
<a href="misc2.php?action1=u2u&action=outbox"><?=$lang[textu2uoutbox]?></a> - 
<a href="misc2.php?action1=u2u&action=send"><?=$lang[textsendu2u]?></a> - 
<a href="misc2.php?action1=u2u&action=ignore"><?=$lang[ignorelist]?></a>
</font></div>

<?
}
?>

</body>
</html>
