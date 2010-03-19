<?
require "./header.php";

$navigation = "&raquo; $lang_textcp";
eval("\$header = \"".template("header")."\";");
echo $header;
if(!$xmbuser || !$xmbpw) {
$xmbuser = "";
$xmbpw = "";
$status = "";
}

if($status != "Administrator") {
echo "$lang_notadmin";
exit;
}

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname);

$tables = array('banned', 'favorites', 'forums', 'members', 'posts', 'ranks', 'settings', 'smilies', 'templates', 'themes', 'threads', 'u2u', 'whosonline', 'words');
foreach($tables as $name) {
${'table_'.$name} = $tablepre.$name;
}
?>
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?=$lang_textcp?></td>
</tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td align="center">
<a href="cp.php?action=settings"><?=$lang_textsettings?></a> - 
<a href="cp.php?action=forum"><?=$lang_textforums?></a> - 
<a href="cp.php?action=mods"><?=$lang_textmods?></a> - 
<a href="cp.php?action=members"><?=$lang_textmembers?></a> - 
<a href="cp.php?action=ipban"><?=$lang_textipban?></a> - 
<a href="cp.php?action=upgrade"><?=$lang_textupgrade?></a> - 
<a href="cp2.php?action=cplog"><?=$lang_textcplog?></a>
<br />

<a href="cp2.php?action=themes"><?=$lang_themes?></a> - 
<a href="cp2.php?action=smilies"><?=$lang_smilies?></a> - 
<a href="cp2.php?action=censor"><?=$lang_textcensors?></a> - 
<a href="cp2.php?action=ranks"><?=$lang_textuserranks?></a> - 
<a href="cp2.php?action=newsletter"><?=$lang_textnewsletter?></a> - 
<a href="cp2.php?action=prune"><?=$lang_textprune?></a> - 
<a href="cp2.php?action=templates"><?=$lang_templates?></a> - 
<a href="cp2.php?action=attachments"><?=$lang_textattachman?></a>
<br />

<a href="tools.php?action=fixttotals"><?=$lang_textfixthread?></a> - 
<a href="tools.php?action=fixftotals"><?=$lang_textfixmemposts?></a> - 
<a href="tools.php?action=fixmposts"><?=$lang_textfixposts?></a>

<?
//Get All Plugins
for($plugnum=1; $plugname[$plugnum] != ""; $plugnum++) {
	if(!$plugurl[$plugnum] || !$plugname[$plugnum]) {
		echo $lang_textbadplug;
	} else {
		if($plugadmin == "yes") {
			$pluglinks .= "<a href=\"$plugurl[$plugnum]\">$plugname[$plugnum]</a> - ";
		}
	}
}
if($pluglinks) {
echo "<br>$lang_textplugins $pluglinks";
}
?>
</td>
</tr>
</table>

<?
if($action == "fixftotals") {
$queryf = $db->query("SELECT * FROM $table_forums WHERE type!='group'");
while($forum = $db->fetch_array($queryf)) {

$query = $db->query("SELECT fid FROM $table_forums WHERE fup='$forum[fid]'");
$sub = $db->fetch_array($query);

$query = $db->query("SELECT COUNT(*) FROM $table_threads WHERE fid='$forum[fid]' OR fid='$sub[fid]'");
$threadnum = $db->result($query, 0);

$query = $db->query("SELECT COUNT(*) FROM $table_posts WHERE fid='$forum[fid]' OR fid='$sub[fid]'");
$postnum = $db->result($query, 0);

$db->query("UPDATE $table_forums SET threads='$threadnum', posts='$postnum' WHERE fid='$forum[fid]'");
}
echo "<tr bgcolor=\"$altbg2\"><td><div align=\"center\"><b>Update successful!</b></div></td></tr>";
}


if($action == "fixttotals") {
$queryt = $db->query("SELECT * FROM $table_threads");
while($threads = $db->fetch_array($queryt)) {

$query = $db->query("SELECT COUNT(*) FROM $table_posts WHERE tid='$threads[tid]'");
$replynum = $db->result($query, 0);

$replynum--;
$db->query("UPDATE $table_threads SET replies='$replynum' WHERE tid='$threads[tid]'");
}
echo "<tr bgcolor=\"$altbg2\"><td><div align=\"center\"><b>Update successful!</b></div></td></tr>";
}


if($action == "fixmposts") {
$queryt = $db->query("SELECT * FROM $table_members");
while($mem = $db->fetch_array($queryt)) {

$query = $db->query("SELECT COUNT(*) FROM $table_posts WHERE author='$mem[username]'");
$postsnum = $db->result($query, 0);

$postsnum += $postsnum2;

$db->query("UPDATE $table_members SET postnum='$postsnum' WHERE username='$mem[username]'");
}
echo "<tr bgcolor=\"$altbg2\"><td><div align=\"center\"><b>Update successful!</b></div></td></tr>";
}
?>
</table>
<?
$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>