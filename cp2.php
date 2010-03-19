<?
require "header.php";

$navigation = "<a href=\"index.php\">$lang[textindex]</a> &gt; $lang[textcp]";
$html = template("header.html");
eval("echo stripslashes(\"$html\");");

if($status != "Administrator") {
echo "$lang[notadmin]";
exit;
}
//if(!$thisuser || !$thispw) {
//echo "$lang[notadmin]";
//exit;
//}
?>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?=$lang[textcp]?></td>
</tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td align="center">
<a href="cp.php?action=settings"><?=$lang[textsettings]?></a> - <a href="cp.php?action=forum"><?=$lang[textforums]?></a> - 
<a href="cp.php?action=mods"><?=$lang[textmods]?></a> - <a href="cp.php?action=members"><?=$lang[textmembers]?></a> - 
<a href="cp.php?action=ipban"><?=$lang[textipban]?></a> - <a href="cp.php?action=upgrade"><?=$lang[textupgrade]?></a><br />

<a href="cp2.php?action=themes"><?=$lang[textthemes]?></a> - <a href="cp2.php?action=smilies"><?=$lang[textsmilies]?></a> - 
<a href="cp2.php?action=censor"><?=$lang[textcensors]?></a> - <a href="cp2.php?action=ranks"><?=$lang[textuserranks]?></a> - 
<a href="cp2.php?action=newsletter"><?=$lang[textnewsletter]?></a> - <a href="cp2.php?action=prune"><?=$lang[textprune]?></a></td>
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
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td><?=$lang[textdeleteques]?></td>
<td><?=$lang[textthemename]?></td>
</tr>

<?
$query = mysql_query("SELECT name FROM $table_themes") or die(mysql_error());
while($themeinfo = mysql_fetch_array($query)) {

if($themeinfo[name] == "$theme") {
$checked = "checked=\"checked\"";
}
?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><input type="checkbox" name="delete<?=$themeinfo[name]?>" value="<?=$themeinfo[name]?>" /></td>
<td><input type="text" name="name<?=$themeinfo[name]?>" value="<?=$themeinfo[name]?>" /> <a href="cp2.php?action=themes&single=<?=$themeinfo[name]?>"><?=$lang[textdetails]?></a></td>
</tr>

<?
$checked = "";
}
?>

<tr bgcolor="<?=$altbg2?>"><td colspan="3"> </td></tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td colspan="3"><a href="cp2.php?action=themes&single=anewtheme1"><?=$lang[textnewtheme]?></a></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="themesubmit" value="<?=$lang[textsubmitchanges]?>" /></center>
</form>

</td>
</tr>

<?
}

if($themesubmit) {
$querytheme = mysql_query("SELECT name FROM $table_themes") or die(mysql_error());
while($themes = mysql_fetch_array($querytheme)) {
$name = "name$themes[name]";
$name = "${$name}";
$delete = "delete$themes[name]";
$delete = "${$delete}";

if($delete != "") {
mysql_query("DELETE FROM $table_themes WHERE name='$delete'") or die(mysql_error());
}


if($themes[name] == $themedef && $name != $themes[name]) {

$setcontents = "<?
\$langfile = \"$langfile\";
\$bbname = \"$bbname\";
\$postperpage = \"$postperpage\";
\$topicperpage = \"$topicperpage\";
\$hottopic = \"$hottopic\";
\$theme = \"$name\";
\$bbstatus = \"$bbstatus\";
\$whosonlinestatus = \"$whosonlinestatus\";
\$regstatus = \"$regstatus\";
\$bboffreason = \"$bboffreason\";
\$regviewonly = \"$regview\";
\$floodctrl = \"$floodctrl\";
\$memberperpage = \"$memberperpage\";
\$catsonly = \"$catsonly\";
\$hideprivate = \"$hideprivate\";
\$showsort = \"$showsort\";
\$emailcheck = \"$emailcheck\";
\$bbrules = \"$bbrules\";
\$bbrulestxt = \"$bbrulestxt\";
\$u2ustatus = \"$u2ustatus\";
\$searchstatus = \"$searchstatus\";
\$faqstatus = \"$faqstatus\";
\$memliststatus = \"$memliststatus\";
\$piconstatus = \"$piconstatus\";
\$sitename = \"$sitename\";
\$siteurl = \"$siteurl\";
\$avastatus = \"$avastatus\";
\$u2uquota = \"$u2uquota\";
\$noreg = \"$noreg\";
\$nocacheheaders = \"$nocacheheaders\";
\$gzipcompress = \"$gzipcompress\";
\$boardurl = \"$boardurl\";
\$coppa = \"$coppa\";
\$timeformat = \"$timeformat\";
\$adminemail = \"$adminemail\";
\$dateformat = \"$dateformat\";
\$statspage = \"$statspage\";
\$sigbbcode = \"$sigbbcode\";
\$sightml = \"$sightml\";
\$indexstats = \"$indexstats\";
\$reportpost = \"$reportpost\";
\$showtotaltime = \"$showtotaltime\";
?>";

$file = fopen("settings.php", "w");
fwrite($file, $setcontents);
fclose($file);

}

if($name != $themes[name]) {
mysql_query("UPDATE $table_members SET theme='$name' WHERE theme='$themes[name]'") or die(mysql_error());
mysql_query("UPDATE $table_forums SET theme='$name' WHERE theme='$themes[name]'") or die(mysql_error());
}

mysql_query("UPDATE $table_themes SET name='$name' WHERE name='$themes[name]'") or die(mysql_error());
}


echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textthemeupdate]</td></tr>";
} 

if($single && $single != "submit" && $single != "anewtheme1") {
$query = mysql_query("SELECT * FROM $table_themes WHERE name='$single'") or die(mysql_error());
$themestuff = mysql_fetch_array($query);

if($themestuff[postscol] == "2col") {
$pcol2 = "selected=\"selected\"";
} else {
$pcol1 = "selected=\"selected\"";
}
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=themes&single=submit">
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[texthemename]?></td>
<td colspan="2"><input type="text" name="namenew" value="<?=$themestuff[name]?>" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textbgcolor]?></td>
<td><input type="text" name="bgcolornew" value="<?=$themestuff[bgcolor]?>" /></td>
<td bgcolor="<?=$themestuff[bgcolor]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textaltbg1]?></td>
<td><input type="text" name="altbg1new" value="<?=$themestuff[altbg1]?>" /></td>
<td bgcolor="<?=$themestuff[altbg1]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textaltbg2]?></td>
<td><input type="text" name="altbg2new" value="<?=$themestuff[altbg2]?>" /></td>
<td bgcolor="<?=$themestuff[altbg2]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textlink]?></td>
<td><input type="text" name="linknew" value="<?=$themestuff[link]?>" /></td>
<td bgcolor="<?=$themestuff[link]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textborder]?></td>
<td><input type="text" name="bordercolornew" value="<?=$themestuff[bordercolor]?>" /></td>
<td bgcolor="<?=$themestuff[bordercolor]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textheader]?></td>
<td><input type="text" name="headernew" value="<?=$themestuff[header]?>" /></td>
<td bgcolor="<?=$themestuff[header]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textheadertext]?></td>
<td><input type="text" name="headertextnew" value="<?=$themestuff[headertext]?>" /></td>
<td bgcolor="<?=$themestuff[headertext]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[texttop]?></td>
<td><input type="text" name="topnew" value="<?=$themestuff[top]?>" /></td>
<td bgcolor="<?=$themestuff[top]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textcatcolor]?></td>
<td><input type="text" name="catcolornew" value="<?=$themestuff[catcolor]?>" /></td>
<td bgcolor="<?=$themestuff[catcolor]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[texttabletext]?></td>
<td><input type="text" name="tabletextnew" value="<?=$themestuff[tabletext]?>" /></td>
<td bgcolor="<?=$themestuff[tabletext]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[texttext]?></td>
<td><input type="text" name="textnew" value="<?=$themestuff[text]?>" /></td>
<td bgcolor="<?=$themestuff[text]?>">&nbsp;</td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textborderwidth]?></td>
<td colspan="2"><input type="text" name="borderwidthnew" value="<?=$themestuff[borderwidth]?>" size="2" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textwidth]?></td>
<td colspan="2"><input type="text" name="tablewidthnew" value="<?=$themestuff[tablewidth]?>" size="3" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textspace]?></td>
<td colspan="2"><input type="text" name="tablespacenew" value="<?=$themestuff[tablespace]?>" size="2" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textfont]?></td>
<td colspan="2"><input type="text" name="fnew" value="<?=$themestuff[font]?>" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textaltfont]?></td>
<td colspan="2"><input type="text" name="altfnew" value="<?=$themestuff[altfont]?>" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textbigsize]?></td>
<td colspan="2"><input type="text" name="fsizenew" value="<?=$themestuff[fontsize]?>" size="4" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textsmallsize]?></td>
<td colspan="2"><input type="text" name="altfsizenew" value="<?=$themestuff[altfontsize]?>" size="4" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textreplyimg]?></td>
<td colspan="2"><input type="text"  value="<?=$themestuff[replyimg]?>" name="replyimgnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textnewtopicimg]?></td>
<td colspan="2"><input type="text"  value="<?=$themestuff[newtopicimg]?>" name="newtopicimgnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textboardlogo]?></td>
<td colspan="2"><input type="text"  value="<?=$themestuff[boardimg]?>" name="boardlogonew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[postscolumn]?></td>
<td colspan="2"><select name="postscolnew"><option value="2col" <?=$pcol2?>><?=$lang[text2col]?></option><option value="1col" <?=$pcol1?>><?=$lang[text1col]?></option></select></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" value="<?=$lang[textsubmitchanges]?>" /></center>
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
<td><?=$lang[texthemename]?></td>
<td><input type="text" name="namenew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textbgcolor]?></td>
<td><input type="text" name="bgcolornew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textaltbg1]?></td>
<td><input type="text" name="altbg1new" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textaltbg2]?></td>
<td><input type="text" name="altbg2new" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textlink]?></td>
<td><input type="text" name="linknew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textborder]?></td>
<td><input type="text" name="bordercolornew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textheader]?></td>
<td><input type="text" name="headernew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textheadertext]?></td>
<td><input type="text" name="headertextnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[texttop]?></td>
<td><input type="text" name="topnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textcatcolor]?></td>
<td><input type="text" name="catcolornew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[texttabletext]?></td>
<td><input type="text" name="tabletextnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[texttext]?></td>
<td><input type="text" name="textnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textborderwidth]?></td>
<td><input type="text" name="borderwidthnew" size="2" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textwidth]?></td>
<td><input type="text" name="tablewidthnew" size="3" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textspace]?></td>
<td><input type="text" name="tablespacenew" size="2" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textfont]?></td>
<td><input type="text" name="fnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textaltfont]?></td>
<td><input type="text" name="altfnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textbigsize]?></td>
<td><input type="text" name="fsizenew" size="4" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textsmallsize]?></td>
<td><input type="text" name="altfsizenew" size="4" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textreplyimg]?></td>
<td><input type="text"  name="replyimgnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textnewtopicimg]?></td>
<td><input type="text" name="newtopicimgnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textboardlogo]?></td>
<td><input type="text" name="boardlogonew" value="<?=$boardimg?>" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[postscolumn]?></td>
<td colspan="2"><select name="postscolnew"><option value="2col"><?=$lang[text2col]?></option><option value="1col"><?=$lang[text1col]?></option></select></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" value="<?=$lang[textsubmitchanges]?>" /></center>
<input type="hidden" name="newtheme" value="<?=$single?>" />
</form>

</td>
</tr>

<?
}


if($single == "submit" && !$newtheme) {
mysql_query("UPDATE $table_themes SET name='$namenew', bgcolor='$bgcolornew', altbg1='$altbg1new', altbg2='$altbg2new', link='$linknew', bordercolor='$bordercolornew', header='$headernew', headertext='$headertextnew', top='$topnew', catcolor='$catcolornew', tabletext='$tabletextnew', text='$textnew', borderwidth='$borderwidthnew', tablewidth='$tablewidthnew', tablespace='$tablespacenew', fontsize='$fsizenew', font='$fnew', altfontsize='$altfsizenew', altfont='$altfnew', replyimg='$replyimgnew', newtopicimg='$newtopicimgnew', boardimg='$boardlogonew', postscol='$postscolnew' WHERE name='$orig'") or die(mysql_error());

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textthemeupdate]</td></tr>";
}

if($single == "submit" && $newtheme) {
mysql_query("INSERT INTO $table_themes VALUES('$namenew', '$bgcolornew', '$altbg1new', '$altbg2new', '$linknew', '$bordercolornew', '$headernew', '$headertextnew', '$topnew', '$catcolornew', '$tabletextnew', '$textnew', '$borderwidthnew', '$tablewidthnew', '$tablespacenew','$fnew','$fsizenew','$altfnew','$altfsizenew', '$replyimgnew', '$newtopicimgnew', '$boardlogonew', '$postscolnew')") or die(mysql_error());

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textthemeupdate]</td></tr>";
}
}



if($action == "smilies") {
if(!$smiliesubmit) {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=smilies">
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td><?=$lang[textdeleteques]?></td>
<td><?=$lang[textsmiliecode]?></td>
<td><?=$lang[textsmilieurl]?></td>
</tr>

<?
$query = mysql_query("SELECT * FROM $table_smilies WHERE type='smiley' ORDER BY id") or die(mysql_error());
while($smilie = mysql_fetch_array($query)) {
?>

<tr>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="checkbox" name="delete<?=$smilie[id]?>" value="<?=$smilie[id]?>" /></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="code<?=$smilie[id]?>" value="<?=$smilie[code]?>" /></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="url<?=$smilie[id]?>" value="<?=$smilie[url]?>" /></td>
</tr>

<?
}
?>
<tr bgcolor="<?=$altbg2?>"><td colspan="3"> </td></tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td colspan="2"><?=$lang[textnewsmilie]?>&nbsp;&nbsp;<input type="text" name="newcode" /></td>
<td><input type="text" name="newurl1" /></td>
</tr>

<tr>
<td colspan="3" class="header"><?=$lang[picons]?></td>
</tr>

<?
$query = mysql_query("SELECT * FROM $table_smilies WHERE type='picon' ORDER BY id") or die(mysql_error());
while($smilie = mysql_fetch_array($query)) {
?>

<tr>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="checkbox" name="delete<?=$smilie[id]?>" value="<?=$smilie[id]?>" /></td>
<td bgcolor="<?=$altbg2?>" class="tablerow" colspan="2"><input type="text" name="url<?=$smilie[id]?>" value="<?=$smilie[url]?>" /></td></tr>

<?
}
?>

<tr bgcolor="<?=$altbg2?>"><td colspan="3"> </td></tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td colspan="3"><?=$lang[textnewpicon]?>&nbsp;&nbsp;<input type="text" name="newurl2" /></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="smiliesubmit" value="<?=$lang[textsubmitchanges]?>" /></center>
</form>

</td>
</tr>

<?
}

if($smiliesubmit) {
$querysmilie = mysql_query("SELECT id FROM $table_smilies WHERE type='smiley'") or die(mysql_error());
while($smilie = mysql_fetch_array($querysmilie)) {
$code = "code$smilie[id]";
$code = "${$code}";
$url = "url$smilie[id]";
$url = "${$url}";
$delete = "delete$smilie[id]";
$delete = "${$delete}";

if($delete != "") {
$query = mysql_query("DELETE FROM $table_smilies WHERE id='$delete'") or die(mysql_error());
}

$query = mysql_query("UPDATE $table_smilies SET code='$code', url='$url' WHERE id='$smilie[id]' AND type='smiley'") or die(mysql_error());
}



$querysmilie = mysql_query("SELECT id FROM $table_smilies WHERE type='picon'") or die(mysql_error());
while($picon = mysql_fetch_array($querysmilie)) {
$url = "url$picon[id]";
$url = "${$url}";
$delete = "delete$picon[id]";
$delete = "${$delete}";

if($delete != "") {
$query = mysql_query("DELETE FROM $table_smilies WHERE id='$delete'") or die(mysql_error());
}

$query = mysql_query("UPDATE $table_smilies SET url='$url' WHERE id='$picon[id]' AND type='picon'") or die(mysql_error());
}


if($newcode != "") {
$query = mysql_query("INSERT INTO $table_smilies VALUES ('smiley', '$newcode', '$newurl1', '')") or die(mysql_error());
}

if($newurl2 != "") {
$query = mysql_query("INSERT INTO $table_smilies VALUES ('picon', '', '$newurl2', '')") or die(mysql_error());
}

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textsmilieupdate]</td></tr>";
}
}



if($action == "censor") {
if(!$censorsubmit) {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=censor">
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td><?=$lang[textdeleteques]?></td>
<td><?=$lang[textcensorfind]?></td>
<td><?=$lang[textcensorreplace]?></td>
</tr>

<?
$query = mysql_query("SELECT * FROM $table_words ORDER BY id") or die(mysql_error());
while($censor = mysql_fetch_array($query)) {

?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><input type="checkbox" name="delete<?=$censor[id]?>" value="<?=$censor[id]?>" /></td>
<td><input type="text" name="find<?=$censor[id]?>" value="<?=$censor[find]?>" /></td>
<td><input type="text" name="replace<?=$censor[id]?>" value="<?=$censor[replace1]?>" /></td>
</tr>

<?
}
?>

<tr bgcolor="<?=$altbg2?>"><td colspan="3"> </td></tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td colspan="2"><?=$lang[textnewcode]?>&nbsp;&nbsp;<input type="text" name="newfind" /></td>
<td><input type="text" name="newreplace" /></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="censorsubmit" value="<?=$lang[textsubmitchanges]?>" /></center>
</form>

</td>
</tr>

<?
}

if($censorsubmit) {
$querycensor = mysql_query("SELECT id FROM $table_words") or die(mysql_error());

while($censor = mysql_fetch_array($querycensor)) {
$find = "find$censor[id]";
$find = "${$find}";
$replace = "replace$censor[id]";
$replace = "${$replace}";
$delete = "delete$censor[id]";
$delete = "${$delete}";

if($delete != "") {
mysql_query("DELETE FROM $table_words WHERE id='$delete'") or die(mysql_error());
}

mysql_query("UPDATE $table_words SET find='$find', replace1='$replace' WHERE id='$censor[id]'") or die(mysql_error());
}

if($newfind != "") {
mysql_query("INSERT INTO $table_words VALUES ('$newfind', '$newreplace', '')") or die(mysql_error());
}

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textcensorupdate]</td></tr>";
}
}



if($action == "ranks") {
if(!$rankssubmit) {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=ranks">
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td><?=$lang[textdeleteques]?></td>
<td><?=$lang[textcusstatus]?></td>
<td><?=$lang[textposts]?></td>
<td><?=$lang[textstars]?></td>
<td><?=$lang[textallowavatars]?></td>
<td><?=$lang[textavatar]?></td>
</tr>

<?
$query = mysql_query("SELECT * FROM $table_ranks ORDER BY id") or die(mysql_error());
while($rank = mysql_fetch_array($query)) {

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
<td><select name="allowavatars<?=$rank[id]?>"><option value="yes" <?=$avataryes?>><?=$lang[texton]?></option>
<option value="no" <?=$avatarno?>><?=$lang[textoff]?></option></select></td>
<td><input type="text" name="avaurl<?=$rank[id]?>" value="<?=$rank[avatarrank]?>" size="20" /></td>
</tr>

<?
$avataryes = "";
$avatarno = "";
}
?>

<tr bgcolor="<?=$altbg2?>"><td colspan="6"> </td></tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td colspan="2"><?=$lang[textnewrank]?>&nbsp;&nbsp;<input type="text" name="newtitle" /></td>
<td><input type="text" name="newposts" size="5" /></td>
<td><input type="text" name="newstars" size="4" /></td>
<td><select name="newallowavatars"><option value="yes"><?=$lang[texton]?></option><option value="no"><?=$lang[textoff]?></option></select></td>
<td><input type="text" name="newavaurl" size="20" /></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="rankssubmit" value="<?=$lang[textsubmitchanges]?>" /></center>
</form>

</td>
</tr>

<?
}

if($rankssubmit) {
$query = mysql_query("SELECT id FROM $table_ranks") or die(mysql_error());

while($ranks = mysql_fetch_array($query)) {
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
mysql_query("DELETE FROM $table_ranks WHERE id='$delete'") or die(mysql_error());
}

mysql_query("UPDATE $table_ranks SET title='$title', posts='$posts', stars='$stars', allowavatars='$allowavatars', avatarrank='$avaurl' WHERE id='$ranks[id]'") or die(mysql_error());
}

if($newtitle != "") {
mysql_query("INSERT INTO $table_ranks VALUES ('$newtitle', '$newposts', '', '$newstars', '$newallowavatars', '$newavaurl')") or die(mysql_error());
}

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textrankingsupdate]</td></tr>";
}
}



if($action == "newsletter") {
if(!$newslettersubmit) {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=newsletter">
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td colspan=2><?=$lang[textnewsletter]?></td>
</tr>


<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td><?=$lang[textsubject]?></td><td><input type="text" name="newssubject" size="45" /></td>
</tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td valign=top><?=$lang[textmessage]?></td><td><textarea cols="35" rows="7" name="newsmessage"></textarea></td>
</tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow"> 
<td valign=top><?=$lang[textsendvia]?></td><td><input type="radio" value="email" checked name="sendvia"> <?=$lang[textemail]?><BR><input type="radio" value="u2u" checked name="sendvia"> <?=$lang[textu2u]?></td> 
</tr> 
</table>
</td></tr></table>
<center><input type="submit" name="newslettersubmit" value="<?=$lang[textsubmitchanges]?>" /></center>
</form>

</td>
</tr>

<?
}
if($newslettersubmit) {
$query = mysql_query("SELECT * FROM $table_members WHERE newsletter='yes'") or die(mysql_error());
while ($memnews = mysql_fetch_array($query)) {
$newsmessage = stripslashes($newsmessage);
if($sendvia == "u2u") { 
mysql_query("INSERT INTO $table_u2u VALUES('', '$memnews[username]', '$thisuser', '" . time() . "', '$newssubject', '$newsmessage', 'inbox')") or die(mysql_error()); 
} else { 
mail("$memnews[email]", "$newssubject", "$newsmessage", "$lang[textfrom] $adminemail"); 
} 

}
echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textnewslettersubmit] </td></tr>";
}
}



if($action == "prune") {
if(!$prunesubmit) {

$forumselect = "<select name=\"forumprune\">\n";
$forumselect .= "<option value=\"$lang[textall]\">$lang[textall]</option>\n";
$querycat = mysql_query("SELECT * FROM $table_forums WHERE type='forum' ORDER BY displayorder") or die(mysql_error());
while($forum = mysql_fetch_array($querycat)) {
$forumselect .= "<option value=\"$forum[fid]\">$forum[name]</option>\n";
}
$forumselect .= "</select>";
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=prune ">
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td colspan="2"><?=$lang[textprune]?></td>
</tr>

<tr bgcolor="<?=$altbg1?>">
<td class="tablerow"><?=$lang[prunewhere]?></td>
<td><input type="text" name="days" size="3" /></td>
</tr>

<tr bgcolor="<?=$altbg1?>">
<td class="tablerow"><?=$lang[prunein]?></td>
<td><?=$forumselect?></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="prunesubmit" value="<?=$lang[textsubmitchanges]?>" /></center>
</form>

</td>
</tr>

<?
}

if($prunesubmit) {
$prunedate = time() - (86400*$days);

if($forumprune == $lang[textall]) {
$querythread = mysql_query("SELECT * FROM $table_threads WHERE lastpost <= '$prunedate'") or die(mysql_error());
} else {
$querythread = mysql_query("SELECT * FROM $table_threads WHERE lastpost <= '$prunedate' AND fid='$forumprune'") or die(mysql_error());
}

while($thread = mysql_fetch_array($querythread)) {
mysql_query("DELETE FROM $table_threads WHERE tid='$thread[tid]'") or die(mysql_error());
mysql_query("UPDATE $table_forums SET posts=post-1, threads=threads-1 WHERE fid='$thread[fid]'") or die(mysql_error());
mysql_query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$thread[author]'") or die(mysql_error());

$querypost = mysql_query("SELECT * FROM $table_posts WHERE tid='$thread[tid]'") or die(mysql_error());
while($post = mysql_fetch_array($querypost)) {
mysql_query("DELETE FROM $table_posts WHERE pid='$post[pid]'") or die(mysql_error());
mysql_query("UPDATE $table_forums SET posts=post-1 WHERE fid='$post[fid]'") or die(mysql_error());
mysql_query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$post[author]'") or die(mysql_error());
}
}

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
