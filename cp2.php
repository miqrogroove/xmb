<?
require "./header.php";

if(!$xmbuser || !$xmbpw) {
$xmbuser = "";
$xmbpw = "";
$status = "";
}

// Start Download Templates Code
if($status == "Administrator" && $action == "templates" && $download) {
$templates=$db->query("SELECT * FROM $table_templates");
while ($template=$db->fetch_array($templates)) {
$template[template] = stripslashes($template[template]);
$code.= "$template[name]|#*XMB TEMPLATE*#|$template[template]|#*XMB TEMPLATE FILE*#|";
}
header("Content-disposition: filename=templates.xmb");
header("Content-Length: ".strlen($code));
header("Content-type: unknown/unknown");
header("Pragma: no-cache");
header("Expires: 0");
echo $code;
exit;
}
// End Download Templates Code

$navigation = "&gt; $lang_textcp";
eval("\$header = \"".template("header")."\";");
echo $header;

if($status != "Administrator") {
echo "$lang_notadmin";
exit;
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
<a href="cp.php?action=settings"><?=$lang_textsettings?></a> - <a href="cp.php?action=forum"><?=$lang_textforums?></a> -
<a href="cp.php?action=mods"><?=$lang_textmods?></a> - <a href="cp.php?action=members"><?=$lang_textmembers?></a> -
<a href="cp.php?action=ipban"><?=$lang_textipban?></a> - <a href="cp.php?action=upgrade"><?=$lang_textupgrade?></a><br />

<a href="cp2.php?action=themes"><?=$lang_themes?></a> - <a href="cp2.php?action=smilies"><?=$lang_smilies?></a> -
<a href="cp2.php?action=censor"><?=$lang_textcensors?></a> - <a href="cp2.php?action=ranks"><?=$lang_textuserranks?></a> -
<a href="cp2.php?action=newsletter"><?=$lang_textnewsletter?></a> - <a href="cp2.php?action=prune"><?=$lang_textprune?></a> -
<a href="cp2.php?action=templates"><?=$lang_templates?></a></td>
</tr>

<?
if(!$action) {
}


if($action == "themes") {
if(!$themesubmit && !$single) {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=themes">
<table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td><?=$lang_textdeleteques?></td>
<td><?=$lang_textthemename?></td>
</tr>

<?
$query = $db->query("SELECT name FROM $table_themes");
while($themeinfo = $db->fetch_array($query)) {

if($themeinfo[name] == "$theme") {
$checked = "checked=\"checked\"";
}
?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><input type="checkbox" name="delete<?=$themeinfo[name]?>" value="<?=$themeinfo[name]?>" /></td>
<td><input type="text" name="name<?=$themeinfo[name]?>" value="<?=$themeinfo[name]?>" /> <a href="cp2.php?action=themes&single=<?=$themeinfo[name]?>"><?=$lang_textdetails?></a></td>
</tr>

<?
$checked = "";
}
?>

<tr bgcolor="<?=$altbg2?>"><td colspan="3"><img src="./images/pixel.gif"></td></tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td colspan="3"><a href="cp2.php?action=themes&single=anewtheme1"><?=$lang_textnewtheme?></a></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="themesubmit" value="<?=$lang_textsubmitchanges?>" /></center>
</form>

</td>
</tr>

<?
}

if($themesubmit) {
$querytheme = $db->query("SELECT name FROM $table_themes");
while($themes = $db->fetch_array($querytheme)) {
$name = "name$themes[name]";
$name = "${$name}";
$delete = "delete$themes[name]";
$delete = "${$delete}";

if($delete != "") {
$db->query("DELETE FROM $table_themes WHERE name='$delete'");
}


if($themes[name] == $themedef && $name != $themes[name]) {
$db->query("UPDATE $table_settings SET theme='$name'");
}

if($name != $themes[name]) {
$db->query("UPDATE $table_members SET theme='$name' WHERE theme='$themes[name]'");
$db->query("UPDATE $table_forums SET theme='$name' WHERE theme='$themes[name]'");
}

$db->query("UPDATE $table_themes SET name='$name' WHERE name='$themes[name]'");
}


echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_themeupdate</td></tr>";
}

if($single && $single != "submit" && $single != "anewtheme1") {
$query = $db->query("SELECT * FROM $table_themes WHERE name='$single'");
$themestuff = $db->fetch_array($query);
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=themes&single=submit">
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_texthemename?></td>
<td colspan="2"><input type="text" name="namenew" value="<?=$themestuff[name]?>" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textbgcolor?></td>
<td><input type="text" name="bgcolornew" value="<?=$themestuff[bgcolor]?>" /></td>
<td bgcolor="<?=$themestuff[bgcolor]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textaltbg1?></td>
<td><input type="text" name="altbg1new" value="<?=$themestuff[altbg1]?>" /></td>
<td bgcolor="<?=$themestuff[altbg1]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textaltbg2?></td>
<td><input type="text" name="altbg2new" value="<?=$themestuff[altbg2]?>" /></td>
<td bgcolor="<?=$themestuff[altbg2]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textlink?></td>
<td><input type="text" name="linknew" value="<?=$themestuff[link]?>" /></td>
<td bgcolor="<?=$themestuff[link]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textborder?></td>
<td><input type="text" name="bordercolornew" value="<?=$themestuff[bordercolor]?>" /></td>
<td bgcolor="<?=$themestuff[bordercolor]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textheader?></td>
<td><input type="text" name="headernew" value="<?=$themestuff[header]?>" /></td>
<td bgcolor="<?=$themestuff[header]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textheadertext?></td>
<td><input type="text" name="headertextnew" value="<?=$themestuff[headertext]?>" /></td>
<td bgcolor="<?=$themestuff[headertext]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_texttop?></td>
<td><input type="text" name="topnew" value="<?=$themestuff[top]?>" /></td>
<td bgcolor="<?=$themestuff[top]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textcatcolor?></td>
<td><input type="text" name="catcolornew" value="<?=$themestuff[catcolor]?>" /></td>
<td bgcolor="<?=$themestuff[catcolor]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_texttabletext?></td>
<td><input type="text" name="tabletextnew" value="<?=$themestuff[tabletext]?>" /></td>
<td bgcolor="<?=$themestuff[tabletext]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_texttext?></td>
<td><input type="text" name="textnew" value="<?=$themestuff[text]?>" /></td>
<td bgcolor="<?=$themestuff[text]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textborderwidth?></td>
<td colspan="2"><input type="text" name="borderwidthnew" value="<?=$themestuff[borderwidth]?>" size="2" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textwidth?></td>
<td colspan="2"><input type="text" name="tablewidthnew" value="<?=$themestuff[tablewidth]?>" size="3" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textspace?></td>
<td colspan="2"><input type="text" name="tablespacenew" value="<?=$themestuff[tablespace]?>" size="2" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textfont?></td>
<td colspan="2"><input type="text" name="fnew" value="<?=$themestuff[font]?>" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textbigsize?></td>
<td colspan="2"><input type="text" name="fsizenew" value="<?=$themestuff[fontsize]?>" size="4" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textboardlogo?></td>
<td colspan="2"><input type="text"  value="<?=$themestuff[boardimg]?>" name="boardlogonew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_imgdir?></td>
<td colspan="2"><input type="text"  value="<?=$themestuff[imgdir]?>" name="imgdirnew" /></td>
</tr>
<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_smdir?></td>
<td colspan="2"><input type="text"  value="<?=$themestuff[smdir]?>" name="smdirnew" /></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" value="<?=$lang_textsubmitchanges?>" /></center>
<input type="hidden" name="orig" value="<?=$single?>" />
</form>

</td>
</tr>

<?
}

if($single == "anewtheme1") {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=themes&single=submit">
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_texthemename?></td>
<td><input type="text" name="namenew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textbgcolor?></td>
<td><input type="text" name="bgcolornew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textaltbg1?></td>
<td><input type="text" name="altbg1new" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textaltbg2?></td>
<td><input type="text" name="altbg2new" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textlink?></td>
<td><input type="text" name="linknew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textborder?></td>
<td><input type="text" name="bordercolornew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textheader?></td>
<td><input type="text" name="headernew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textheadertext?></td>
<td><input type="text" name="headertextnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_texttop?></td>
<td><input type="text" name="topnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textcatcolor?></td>
<td><input type="text" name="catcolornew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_texttabletext?></td>
<td><input type="text" name="tabletextnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_texttext?></td>
<td><input type="text" name="textnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textborderwidth?></td>
<td><input type="text" name="borderwidthnew" size="2" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textwidth?></td>
<td><input type="text" name="tablewidthnew" size="3" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textspace?></td>
<td><input type="text" name="tablespacenew" size="2" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textfont?></td>
<td><input type="text" name="fnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textbigsize?></td>
<td><input type="text" name="fsizenew" size="4" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_textboardlogo?></td>
<td><input type="text" name="boardlogonew" value="<?=$boardimg?>" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_imgdir?></td>
<td><input type="text" name="imgdirnew" value="images" /></td>
</tr>
<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang_smdir?></td>
<td><input type="text" name="smdirnew" value="images" /></td>
</tr>
</table>
</td></tr></table>
<center><input type="submit" value="<?=$lang_textsubmitchanges?>" /></center>
<input type="hidden" name="newtheme" value="<?=$single?>" />
</form>

</td>
</tr>

<?
}


if($single == "submit" && !$newtheme) {
$db->query("UPDATE $table_themes SET name='$namenew', bgcolor='$bgcolornew', altbg1='$altbg1new', altbg2='$altbg2new', link='$linknew', bordercolor='$bordercolornew', header='$headernew', headertext='$headertextnew', top='$topnew', catcolor='$catcolornew', tabletext='$tabletextnew', text='$textnew', borderwidth='$borderwidthnew', tablewidth='$tablewidthnew', tablespace='$tablespacenew', fontsize='$fsizenew', font='$fnew', boardimg='$boardlogonew', imgdir='$imgdirnew', smdir='$smdirnew' WHERE name='$orig'");

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_themeupdate</td></tr>";
}

if($single == "submit" && $newtheme) {
$db->query("INSERT INTO $table_themes VALUES('$namenew', '$bgcolornew', '$altbg1new', '$altbg2new', '$linknew', '$bordercolornew', '$headernew', '$headertextnew', '$topnew', '$catcolornew', '$tabletextnew', '$textnew', '$borderwidthnew', '$tablewidthnew', '$tablespacenew','$fnew','$fsizenew', '$boardlogonew', '$imgdirnew', '$smdirnew')");

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_themeupdate</td></tr>";
}
}



if($action == "smilies") {
if(!$smiliesubmit) {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=smilies">
<table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header"><td colspan="4" align="left"><?=$lang_smilies?></td></tr>
<tr class="header">
<td><?=$lang_textdeleteques?></td>
<td><?=$lang_textsmiliecode?></td>
<td><?=$lang_textsmiliefile?></td>
<td><?=$lang_smilies?></td>
</tr>

<?
$query = $db->query("SELECT * FROM $table_smilies WHERE type='smiley' ORDER BY id");
while($smilie = $db->fetch_array($query)) {
?>

<tr>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="checkbox" name="delete<?=$smilie[id]?>" value="<?=$smilie[id]?>" /></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="code<?=$smilie[id]?>" value="<?=$smilie[code]?>" /></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="url<?=$smilie[id]?>" value="<?=$smilie[url]?>" /></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><img src="<?=$smdir?>/<?=$smilie[url]?>" /></td>
</tr>

<?
}
?>
<tr><td bgcolor="<?=$altbg2?>" colspan="4"><img src="./images/pixel.gif"></td></tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td><?=$lang_textnewsmilie?></td>
<td><input type="text" name="newcode" /></td>
<td colspan="2"><input type="text" name="newurl1" /></td>
</tr>
<tr><td bgcolor="<?=$altbg2?>" colspan="4"><img src="./images/pixel.gif"></td></tr>

<tr>
<td colspan="4" class="header"><?=$lang_picons?></td>
</tr>

<tr class="header">
<td><?=$lang_textdeleteques?></td>
<td colspan="2"><?=$lang_textsmiliefile?></td>
<td><?=$lang_picons?></td>
</tr>

<?
$query = $db->query("SELECT * FROM $table_smilies WHERE type='picon' ORDER BY id");
while($smilie = $db->fetch_array($query)) {
?>

<tr>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="checkbox" name="delete<?=$smilie[id]?>" value="<?=$smilie[id]?>" /></td>
<td colspan="2" bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="url<?=$smilie[id]?>" value="<?=$smilie[url]?>" /></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><img src="<?=$smdir?>/<?=$smilie[url]?>" /></td></tr>

<?
}
?>

<tr><td bgcolor="<?=$altbg2?>" colspan="4"><img src="./images/pixel.gif"></td></tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td colspan="4"><?=$lang_textnewpicon?>&nbsp;&nbsp;<input type="text" name="newurl2" /></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="smiliesubmit" value="<?=$lang_textsubmitchanges?>" /></center>
</form>

</td>
</tr>

<?
}

if($smiliesubmit) {
$querysmilie = $db->query("SELECT id FROM $table_smilies WHERE type='smiley'");
while($smilie = $db->fetch_array($querysmilie)) {
$code = "code$smilie[id]";
$code = "${$code}";
$url = "url$smilie[id]";
$url = "${$url}";
$delete = "delete$smilie[id]";
$delete = "${$delete}";

if($delete != "") {
$query = $db->query("DELETE FROM $table_smilies WHERE id='$delete'");
}

$query = $db->query("UPDATE $table_smilies SET code='$code', url='$url' WHERE id='$smilie[id]' AND type='smiley'");
}



$querysmilie = $db->query("SELECT id FROM $table_smilies WHERE type='picon'");
while($picon = $db->fetch_array($querysmilie)) {
$url = "url$picon[id]";
$url = "${$url}";
$delete = "delete$picon[id]";
$delete = "${$delete}";

if($delete != "") {
$query = $db->query("DELETE FROM $table_smilies WHERE id='$delete'");
}

$query = $db->query("UPDATE $table_smilies SET url='$url' WHERE id='$picon[id]' AND type='picon'");
}


if($newcode != "") {
$query = $db->query("INSERT INTO $table_smilies VALUES ('smiley', '$newcode', '$newurl1', '')");
}

if($newurl2 != "") {
$query = $db->query("INSERT INTO $table_smilies VALUES ('picon', '', '$newurl2', '')");
}

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_smilieupdate</td></tr>";
}
}



if($action == "censor") {
if(!$censorsubmit) {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=censor">
<table cellspacing="0" cellpadding="0" border="0" width="450" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td><?=$lang_textdeleteques?></td>
<td align="right"><?=$lang_textcensorfind?></td>
<td align="right"><?=$lang_textcensorreplace?></td>
</tr>

<?
$query = $db->query("SELECT * FROM $table_words ORDER BY id");
while($censor = $db->fetch_array($query)) {

?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><input type="checkbox" name="delete<?=$censor[id]?>" value="<?=$censor[id]?>" /></td>
<td align="right"><input type="text" size="20" name="find<?=$censor[id]?>" value="<?=$censor[find]?>" /></td>
<td align="right"><input type="text" size="20" name="replace<?=$censor[id]?>" value="<?=$censor[replace1]?>" /></td>
</tr>

<?
}
?>

<tr bgcolor="<?=$altbg2?>"><td colspan="3"><img src="./images/pixel.gif"></td></tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td align="right" colspan="2"><?=$lang_textnewcode?>&nbsp;&nbsp;<input type="text" size="20" name="newfind" /></td>
<td align="right"><input type="text" size="20" name="newreplace" /></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="censorsubmit" value="<?=$lang_textsubmitchanges?>" /></center>
</form>

</td>
</tr>

<?
}

if($censorsubmit) {
$querycensor = $db->query("SELECT id FROM $table_words");

while($censor = $db->fetch_array($querycensor)) {
$find = "find$censor[id]";
$find = "${$find}";
$replace = "replace$censor[id]";
$replace = "${$replace}";
$delete = "delete$censor[id]";
$delete = "${$delete}";

if($delete != "") {
$db->query("DELETE FROM $table_words WHERE id='$delete'");
}

$db->query("UPDATE $table_words SET find='$find', replace1='$replace' WHERE id='$censor[id]'");
}

if($newfind != "") {
$db->query("INSERT INTO $table_words VALUES ('$newfind', '$newreplace', '')");
}

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_censorupdate</td></tr>";
}
}



if($action == "ranks") {
if(!$rankssubmit) {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=ranks">
<table cellspacing="0" cellpadding="0" border="0" width="650" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td><?=$lang_textdeleteques?></td>
<td><?=$lang_textcusstatus?></td>
<td><?=$lang_textposts?></td>
<td><?=$lang_textstars?></td>
<td><?=$lang_textallowavatars?></td>
<td><?=$lang_textavatar?></td>
</tr>

<?
$query = $db->query("SELECT * FROM $table_ranks ORDER BY id");
while($rank = $db->fetch_array($query)) {

if($rank[allowavatars] == "yes") {
$avataryes = "selected=\"selected\"";
}
else {
$avatarno = "selected=\"selected\"";
}
?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><input type="checkbox" name="delete<?=$rank[id]?>" value="<?=$rank[id]?>" /></td>
<td><input type="text" name="title<?=$rank[id]?>" value="<?=$rank[title]?>" /></td>
<td><input type="text" name="posts<?=$rank[id]?>" value="<?=$rank[posts]?>" size="5" /></td>
<td><input type="text" name="stars<?=$rank[id]?>" value="<?=$rank[stars]?>" size="4" /></td>
<td><select name="allowavatars<?=$rank[id]?>"><option value="yes" <?=$avataryes?>><?=$lang_texton?></option>
<option value="no" <?=$avatarno?>><?=$lang_textoff?></option></select></td>
<td><input type="text" name="avaurl<?=$rank[id]?>" value="<?=$rank[avatarrank]?>" size="20" /></td>
</tr>

<?
$avataryes = "";
$avatarno = "";
}
?>

<tr bgcolor="<?=$altbg2?>"><td colspan="6"> </td></tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td colspan="2"><?=$lang_textnewrank?>&nbsp;&nbsp;<input type="text" name="newtitle" /></td>
<td><input type="text" name="newposts" size="5" /></td>
<td><input type="text" name="newstars" size="4" /></td>
<td><select name="newallowavatars"><option value="yes"><?=$lang_texton?></option><option value="no"><?=$lang_textoff?></option></select></td>
<td><input type="text" name="newavaurl" size="20" /></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="rankssubmit" value="<?=$lang_textsubmitchanges?>" /></center>
</form>

</td>
</tr>

<?
}

if($rankssubmit) {
$query = $db->query("SELECT id FROM $table_ranks");

while($ranks = $db->fetch_array($query)) {
$title = "title$ranks[id]";
$title = "${$title}";
$posts = "posts$ranks[id]";
$posts = "${$posts}";
$stars = "stars$ranks[id]";
$stars = "${$stars}";
$allowavatars = "allowavatars$ranks[id]";
$allowavatars = "${$allowavatars}";
$delete = "delete$ranks[id]";
$delete = "${$delete}";
$avaurl = "avaurl$ranks[id]";
$avaurl = "${$avaurl}";

if($delete != "") {
$db->query("DELETE FROM $table_ranks WHERE id='$delete'");
}

$db->query("UPDATE $table_ranks SET title='$title', posts='$posts', stars='$stars', allowavatars='$allowavatars', avatarrank='$avaurl' WHERE id='$ranks[id]'");
}

if($newtitle != "") {
$db->query("INSERT INTO $table_ranks VALUES ('$newtitle', '$newposts', '', '$newstars', '$newallowavatars', '$newavaurl')");
}

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_rankingsupdate</td></tr>";
}
}



if($action == "newsletter") {
if(!$newslettersubmit) {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=newsletter">
<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td colspan=2><?=$lang_textnewsletter?></td>
</tr>


<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td><?=$lang_textsubject?></td><td><input type="text" name="newssubject" size="80" /></td>
</tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td valign=top><?=$lang_textmessage?></td><td><textarea cols="80" rows="10" name="newsmessage"></textarea></td>
</tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td valign=top><?=$lang_textsendvia?></td><td><input type="radio" value="email" checked name="sendvia"> <?=$lang_textemail?><BR><input type="radio" value="u2u" checked name="sendvia"> <?=$lang_textu2u?></td>
</tr>
</table>
</td></tr></table>
<center><input type="submit" name="newslettersubmit" value="<?=$lang_textsubmitchanges?>" /></center>
</form>

</td>
</tr>

<?
}
if($newslettersubmit) {
$query = $db->query("SELECT * FROM $table_members WHERE newsletter='yes'");
while ($memnews = $db->fetch_array($query)) {
if($sendvia == "u2u") {
$newssuubject = addslashes($newssubject);
$newsmessage = addslashes($newsmessage);
$db->query("INSERT INTO $table_u2u VALUES('', '$memnews[username]', '$xmbuser', '" . time() . "', '$newssubject', '$newsmessage', 'inbox')");
} else {
mail("$memnews[email]", "$newssubject", "$newsmessage", "$lang_textfrom $adminemail");
}

}
echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_newslettersubmit </td></tr>";
}
}



if($action == "prune") {
if(!$prunesubmit) {

$forumselect = "<select name=\"forumprune\">\n";
$forumselect .= "<option value=\"$lang_textall\">$lang_textall</option>\n";
$querycat = $db->query("SELECT * FROM $table_forums WHERE type='forum' ORDER BY displayorder");
while($forum = $db->fetch_array($querycat)) {
$forumselect .= "<option value=\"$forum[fid]\">$forum[name]</option>\n";
}
$forumselect .= "</select>";
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=prune">
<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header" colspan="2"><?=$lang_textprune?></td>
</tr>

<tr>
<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$lang_prunewhere?></td>
<td align="right" bgcolor="<?=$altbg2?>"><input type="text" name="days" size="7" /></td>
</tr>

<tr>
<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$lang_prunein?></td>
<td align="right" bgcolor="<?=$altbg2?>"><?=$forumselect?></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="prunesubmit" value="<?=$lang_textsubmitchanges?>" /></center>
</form>

</td>
</tr>

<?
}

if($prunesubmit) {
$prunedate = time() - (86400*$days);

if($forumprune == $lang_textall) {
$querythread = $db->query("SELECT * FROM $table_threads WHERE lastpost <= '$prunedate'");
} else {
$querythread = $db->query("SELECT * FROM $table_threads WHERE lastpost <= '$prunedate' AND fid='$forumprune'");
}

while($thread = $db->fetch_array($querythread)) {
$db->query("DELETE FROM $table_threads WHERE tid='$thread[tid]'");
$db->query("UPDATE $table_forums SET posts=post-1, threads=threads-1 WHERE fid='$thread[fid]'");
$db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$thread[author]'");

$querypost = $db->query("SELECT * FROM $table_posts WHERE tid='$thread[tid]'");
while($post = $db->fetch_array($querypost)) {
$db->query("DELETE FROM $table_posts WHERE pid='$post[pid]'");
$db->query("UPDATE $table_forums SET posts=posts-1 WHERE fid='$post[fid]'");
$db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$post[author]'");
}
}

}
}


if($action == "templates") {
if(!$edit && !$editsubmit && !$delete && !$deletesubmit && !$new && !$restore) {
?>
<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=templates">
<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header"><?=$lang_templates?></td>
</tr>
<tr>
<td bgcolor="<?=$altbg2?>" class="tablerow" align="left">
<INPUT TYPE="text" NAME="newtemplatename" size="30" maxlength="50">&nbsp;&nbsp;
<input type="submit" name="new" value="<?=$lang_newtemplate?>">
</td></tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow" align="left">

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td bgcolor="<?=$altbg1?>">
<?
$query = $db->query("SELECT * FROM $table_templates ORDER BY name");
echo "<select name=\"tid\"><option value=\"default\">$lang_selecttemplate</option>";
while($template = $db->fetch_array($query)) {
echo "<option value=\"$template[id]\">$template[name]</option>\n";
}
echo "</select>&nbsp;&nbsp;";
?>
<input type="submit" name="edit" value="<?=$lang_textedit?>">&nbsp;
<input type="submit" name="delete" value="<?=$lang_deletebutton?>">
</td>
<td bgcolor="<?=$altbg1?>" align="right"><input type="submit" name="restore" value="<?=$lang_textrestoredeftemps?>">&nbsp;
<input type="submit" name="download" value="<?=$lang_textdownloadtemps?>"></td>
</tr>
</table>
</table>
</td></tr></table>
</form>
</td>
</tr>
<?
}
if($restore) {
?>
<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=templates">
<table cellspacing="0" cellpadding="0" border="0" width="300" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header"><?=$lang_templates?></td>
</tr>
<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow" align="center">
<?=$lang_templaterestoreconfirm?><p>
<center><input type="submit" name="restoresubmit" value="<?=$lang_textyes?>" /></center>
</td>
</tr>

</table>
</td></tr></table>
</form>

</td>
</tr>
<?
}
if($restoresubmit) {
$db->query("DELETE FROM $table_templates");
$filesize=filesize('templates.xmb');
$fp=fopen('templates.xmb','r');
$templatesfile=fread($fp,$filesize);
fclose($fp);
$templates = explode("|#*XMB TEMPLATE FILE*#|", $templatesfile);
while (list($key,$val) = each($templates)) {
$template = explode("|#*XMB TEMPLATE*#|", $val);
$template[1] = addslashes($template[1]);
$db->query("INSERT INTO $table_templates VALUES ('', '".addslashes($template[0])."', '".addslashes($template[1])."')");
}
$db->query("DELETE FROM $table_templates WHERE name=''");
echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_templatesrestoredone</td></tr>";
}

if($edit && !$editsubmit) {
if($tid == "default") {
	echo "<td align=\"center\"><font class=\"subject\">$lang_selecttemplate</td>";
	echo "<script> function redirect() { window.location.replace(\"cp2.php?action=templates\"); } setTimeout(\"redirect();\", 2250); </script>";
	exit;
}
?>
<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=templates&tid=<?=$tid?>">
<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header"><?=$lang_templates?></td>
</tr>
<?
$query = $db->query("SELECT * FROM $table_templates WHERE id='$tid' ORDER BY name");
$template = $db->fetch_array($query);
$template[template] = stripslashes($template[template]);
$template[template] = htmlspecialchars($template[template]);
?>
<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow" align="center">
<b><?=$template[name]?></b><br />
<textarea cols="70" rows="20" name="templatenew"><?=$template[template]?></textarea>
<center><input type="submit" name="editsubmit" value="<?=$lang_textsubmitchanges?>" /></center>
</td>
</tr>

</table>
</td></tr></table>
</form>

</td>
</tr>
<?
}

if($editsubmit) {
$templatenew = addslashes($templatenew);
if($tid == "new") {
$db->query("INSERT INTO $table_templates VALUES('', '$namenew', '$templatenew')");
} else {
$db->query("UPDATE $table_templates SET template='$templatenew' WHERE id='$tid'");
}
echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_templatesupdate</td></tr>";
}
if($delete) {

if($tid == "default") {
	echo "<td align=\"center\"><font class=\"subject\">$lang_selecttemplate</td>";
	echo "<script> function redirect() { window.location.replace(\"cp2.php?action=templates\"); } setTimeout(\"redirect();\", 2250); </script>";
	exit;
}

?>
<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=templates&tid=<?=$tid?>">
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header"><?=$lang_templates?></td>
</tr>
<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow" align="center">
<?=$lang_templatedelconfirm?>
</td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="deletesubmit" value="<?=$lang_textyes?>" /></center>
</form>

</td>
</tr>
<?
}
if($deletesubmit) {
$db->query("DELETE FROM $table_templates WHERE id='$tid'");
echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_templatesdelete</td></tr>";
}

if($new) {
?>
<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=templates&tid=new">
<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header"><?=$lang_templates?></td>
</tr>
<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow" align="center">
<?=$lang_templatename?> <input type="text" name="namenew" size="30" value="<?=$newtemplatename?>" /><br />
<textarea cols="70" rows="20" name="templatenew"></textarea>
<center><input type="submit" name="editsubmit" value="<?=$lang_textsubmitchanges?>" /></center>
</td>
</tr>

</table>
</td></tr></table>
</form>

</td>
</tr>
<?
}
}
echo "</table></td></tr></table>";

$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>
