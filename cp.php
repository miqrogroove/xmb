<?
require "header.php";

$navigation = "<a href=\"index.php\">$lang[textindex]</a> &gt; $lang[textcp]";
$html = template("header.html");
eval("echo stripslashes(\"$html\");");

if($status != "Administrator") {
echo "$lang[notadmin]</body></html>";
exit;
}

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

if($action == "settings") {
if(!$settingsubmit) {

$langfileselect = "<select name=\"langfilenew\">\n";

$dir = opendir("lang");
while ($thafile = readdir($dir)) {
if (is_file("lang/$thafile")) {
$thafile = str_replace(".lang.php", "", $thafile);
if ($thafile == "$langfile") {
$langfileselect .= "<option value=\"$thafile\" selected=\"selected\">$thafile</option>\n";
} 
else {
$langfileselect .= "<option value=\"$thafile\">$thafile</option>\n";
}
}
}

$langfileselect .= "</select>";

$themelist = "<select name=\"themenew\">\n";
$query = mysql_query("SELECT name FROM $table_themes") or die(mysql_error());
while($themeinfo = mysql_fetch_array($query)) {
if($themeinfo[name] == $theme) {
$themelist .= "<option value=\"$themeinfo[name]\" selected=\"selected\">$themeinfo[name]</option>\n";
}
else {
$themelist .= "<option value=\"$themeinfo[name]\">$themeinfo[name]</option>\n";
}
}
$themelist  .= "</select>";

if($bbstatus == "on") { 
$onselect = "selected=\"selected\""; 
} else { 
$offselect = "selected=\"selected\""; 
} 

if($whosonlinestatus == "on") {
$whosonlineon = "selected=\"selected\"";
} else {
$whosonlineoff = "selected=\"selected\"";
}

if($regstatus == "on") { 
$regon = "selected=\"selected\""; 
} else { 
$regoff = "selected=\"selected\""; 
} 

if($regviewonly == "on") { 
$regonlyon = "selected=\"selected\""; 
} else { 
$regonlyoff = "selected=\"selected\""; 
}

if($catsonly == "on") { 
$catsonlyon = "selected=\"selected\""; 
} else { 
$catsonlyoff = "selected=\"selected\""; 
}

if($hideprivate == "on") { 
$hideon = "selected=\"selected\""; 
} else { 
$hideoff = "selected=\"selected\""; 
}

if($showsort == "on") { 
$sorton = "selected=\"selected\""; 
} else { 
$sortoff = "selected=\"selected\""; 
}

if($emailcheck == "on") {
$echeckon = "selected=\"selected\"";
} else {
$echeckoff = "selected=\"selected\"";
}

if($bbrules == "on") {
$ruleson = "selected=\"selected\"";
} else {
$rulesoff = "selected=\"selected\"";
}

if($u2ustatus == "on") {
$u2uon = "selected=\"selected\"";
} else {
$u2uoff = "selected=\"selected\"";
}

if($searchstatus == "on") {
$searchon = "selected=\"selected\"";
} else {
$searchoff = "selected=\"selected\"";
}

if($faqstatus == "on") {
$faqon = "selected=\"selected\"";
} else {
$faqoff = "selected=\"selected\"";
}

if($memliststatus == "on") {
$memliston = "selected=\"selected\"";
} else {
$memlistoff = "selected=\"selected\"";
}

if($piconstatus == "on") {
$piconon = "selected=\"selected\"";
} else {
$piconoff = "selected=\"selected\"";
}

if($avastatus == "on") { 
$avataron = "selected=\"selected\""; 
} else { 
$avataroff = "selected=\"selected\""; 
}

if($noreg == "on") { 
$noregon = "selected=\"selected\""; 
} else { 
$noregoff = "selected=\"selected\""; 
}

if($nocacheheaders == "on") { 
$nocacheheaderson = "selected=\"selected\""; 
} else { 
$nocacheheadersoff = "selected=\"selected\""; 
}

if($gzipcompress == "on") { 
$gzipcompresson = "selected=\"selected\""; 
} else { 
$gzipcompressoff = "selected=\"selected\""; 
}

if($coppa == "on") { 
$coppaon = "selected=\"selected\""; 
} else {
$coppaoff = "selected=\"selected\""; 
}

if($timeformat == "24") {
$check24 = "checked=\"checked\"";
} else {
$check12 = "checked=\"checked\"";
}

if($statspage == "on") { 
$statson = "selected=\"selected\""; 
} else {
$statsoff = "selected=\"selected\""; 
}

if($sigbbcode == "on") {
$sigbbcodeon = "selected=\"selected\""; 
} else {
$sigbbcodeoff = "selected=\"selected\""; 
}

if($sightml == "on") {
$sightmlon = "selected=\"selected\""; 
} else {
$sightmloff = "selected=\"selected\""; 
}

if($indexstats == "on") {
$instatson = "selected=\"selected\""; 
} else {
$instatsoff = "selected=\"selected\""; 
}

if($reportpost == "on") { 
$reportposton = "selected=\"selected\""; 
} else { 
$reportpostoff = "selected=\"selected\""; 
}

if($showtotaltime != "on") { 
$showtotaltimeoff = "selected=\"selected\""; 
} else { 
$showtotaltimeon = "selected=\"selected\""; 
}

if($showtotaltime != "on") { 
$showtotaltimeoff = "selected=\"selected\""; 
} else { 
$showtotaltimeon = "selected=\"selected\""; 
}

?>
<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp.php?action=settings">
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td><?=$lang[textsetting]?></td>
<td><?=$lang[textvalue]?></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textbbname]?></td>
<td><input type="text"  value="<?=$bbname?>" name="bbnamenew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textsitename]?></td>
<td><input type="text"  value="<?=$sitename?>" name="sitenamenew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textsiteurl]?></td>
<td><input type="text"  value="<?=$siteurl?>" name="siteurlnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textboardurl]?></td>
<td><input type="text"  value="<?=$boardurl?>" name="boardurlnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[adminemail]?></td>
<td><input type="text"  value="<?=$adminemail?>" name="adminemailnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textlanguage]?></td>
<td><?=$langfileselect?></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[texttheme]?></td>
<td><?=$themelist?></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textppp]?></td>
<td><input type="text" size="2" value="<?=$postperpage?>" name="postperpagenew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[texttpp]?></td>
<td><input type="text" size="2" value="<?=$topicperpage?>" name="topicperpagenew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textmpp]?></td>
<td><input type="text" size="2" value="<?=$memberperpage?>" name="memberperpagenew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[texthottopic]?></td>
<td><input type="text" size="2" value="<?=$hottopic?>" name="hottopicnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[textflood]?></td>
<td><input type="text" size="2" value="<?=$floodctrl?>" name="floodctrlnew" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[u2uquota]?></td> 
<td><input type="text" size="2" value="<?=$u2uquota?>" name="u2uquotanew" /></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[textbstatus]?></td> 
<td><select name="bbstatusnew"><option value="on" <?=$onselect?>><?=$lang[texton]?></option><option value="off" <?=$offselect?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[textbboffreason]?></td> 
<td><textarea rows="3" name="bboffreasonnew" cols="30"><?=$bboffreason?></textarea></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[whosonline_on]?></td> 
<td><select name="whos_on"><option value="on" <?=$whosonlineon?>><?=$lang[texton]?></option><option value="off" <?=$whosonlineoff?>><?=$lang[textoff]?></option></select></td> 
</tr> 

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[reg_on]?></td> 
<td><select name="reg_on"><option value="on" <?=$regon?>><?=$lang[texton]?></option><option value="off" <?=$regoff?>><?=$lang[textoff]?></option></select></td> 
</tr> 

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[textreggedonly]?></td> 
<td><select name="regviewnew"><option value="on" <?=$regonlyon?>><?=$lang[texton]?></option><option value="off" <?=$regonlyoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[textcatsonly]?></td> 
<td><select name="catsonlynew"><option value="on" <?=$catsonlyon?>><?=$lang[texton]?></option><option value="off" <?=$catsonlyoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[texthidepriv]?></td> 
<td><select name="hidepriv"><option value="on" <?=$hideon?>><?=$lang[texton]?></option><option value="off" <?=$hideoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[textshowsort]?></td> 
<td><select name="showsortnew"><option value="on" <?=$sorton?>><?=$lang[texton]?></option><option value="off" <?=$sortoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[emailverify]?></td> 
<td><select name="emailchecknew"><option value="on" <?=$echeckon?>><?=$lang[texton]?></option><option value="off" <?=$echeckoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[textbbrules]?></td> 
<td><select name="bbrulesnew"><option value="on" <?=$ruleson?>><?=$lang[texton]?></option><option value="off" <?=$rulesoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[textbbrulestxt]?></td> 
<td><textarea rows="3" name="bbrulestxtnew" cols="30"><?=$bbrulestxt?></textarea></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[u2ustatus]?></td> 
<td><select name="u2ustatusnew"><option value="on" <?=$u2uon?>><?=$lang[texton]?></option><option value="off" <?=$u2uoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[textsearchstatus]?></td> 
<td><select name="searchstatusnew"><option value="on" <?=$searchon?>><?=$lang[texton]?></option><option value="off" <?=$searchoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[textfaqstatus]?></td> 
<td><select name="faqstatusnew"><option value="on" <?=$faqon?>><?=$lang[texton]?></option><option value="off" <?=$faqoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[textmemliststatus]?></td> 
<td><select name="memliststatusnew"><option value="on" <?=$memliston?>><?=$lang[texton]?></option><option value="off" <?=$memlistoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[statspage]?></td> 
<td><select name="statspagenew"><option value="on" <?=$statson?>><?=$lang[texton]?></option><option value="off" <?=$statsoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[textpiconstatus]?></td> 
<td><select name="piconstatusnew"><option value="on" <?=$piconon?>><?=$lang[texton]?></option><option value="off" <?=$piconoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[textavastatus]?></td> 
<td><select name="avastatusnew"><option value="on" <?=$avataron?>><?=$lang[texton]?></option><option value="off" <?=$avataroff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[reportpoststatus]?></td> 
<td><select name="reportpostnew"><option value="on" <?=$reportposton?>><?=$lang[texton]?></option><option value="off" <?=$reportpostoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[showtotaltime]?></td> 
<td><select name="showtotaltimenew"><option value="on" <?=$showtotaltimeon?>><?=$lang[texton]?></option><option value="off" <?=$showtotaltimeoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[indexstatscp]?></td> 
<td><select name="indexstatsnew"><option value="on" <?=$instatson?>><?=$lang[texton]?></option><option value="off" <?=$instatsoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[noreg]?></td> 
<td><select name="noregnew"><option value="on" <?=$noregon?>><?=$lang[texton]?></option><option value="off" <?=$noregoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[gzipcompression]?></td> 
<td><select name="gzipcompressnew"><option value="on" <?=$gzipcompresson?>><?=$lang[texton]?></option><option value="off" <?=$gzipcompressoff?>><?=$lang[textoff]?></option></select></td> 
</tr> 

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[nocacheheaders]?></td> 
<td><select name="nocacheheadersnew"><option value="on" <?=$nocacheheaderson?>><?=$lang[texton]?></option><option value="off" <?=$nocacheheadersoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[coppastatus]?></td> 
<td><select name="coppanew"><option value="on" <?=$coppaon?>><?=$lang[texton]?></option><option value="off" <?=$coppaoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[sigbbcode]?></td> 
<td><select name="sigbbcodenew"><option value="on" <?=$sigbbcodeon?>><?=$lang[texton]?></option><option value="off" <?=$sigbbcodeoff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[sightml]?></td> 
<td><select name="sightmlnew"><option value="on" <?=$sightmlon?>><?=$lang[texton]?></option><option value="off" <?=$sightmloff?>><?=$lang[textoff]?></option></select></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow"> 
<td><?=$lang[texttimeformat]?></td> 
<td><input type="radio" value="24" name="timeformatnew" <?=$check24?>><?=$lang[text24hour]?> <input type="radio" value="12" name="timeformatnew" <?=$check12?>><?=$lang[text12hour]?></td> 
</tr>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$lang[dateformat]?></td>
<td><input type="text"  value="<?=$dformatorig?>" name="dateformatnew" /></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="settingsubmit" value="<?=$lang[textsubmitchanges]?>" /></center>
</form>

</td>
</tr>

<?
}

if($settingsubmit) {
$bbrulestxtnew = addslashes($bbrulestxtnew);
$bboffreasonnew = addslashes($bboffreasonnew);

$setcontents = "<?
\$langfile = \"$langfilenew\";
\$bbname = \"$bbnamenew\";
\$postperpage = \"$postperpagenew\";
\$topicperpage = \"$topicperpagenew\";
\$hottopic = \"$hottopicnew\";
\$theme = \"$themenew\";
\$bbstatus = \"$bbstatusnew\";
\$whosonlinestatus = \"$whos_on\";
\$regstatus = \"$reg_on\";
\$bboffreason = \"$bboffreasonnew\";
\$regviewonly = \"$regviewnew\";
\$floodctrl = \"$floodctrlnew\";
\$memberperpage = \"$memberperpagenew\";
\$catsonly = \"$catsonlynew\";
\$hideprivate = \"$hidepriv\";
\$showsort = \"$showsortnew\";
\$emailcheck = \"$emailchecknew\";
\$bbrules = \"$bbrulesnew\";
\$bbrulestxt = \"$bbrulestxtnew\";
\$u2ustatus = \"$u2ustatusnew\";
\$searchstatus = \"$searchstatusnew\";
\$faqstatus = \"$faqstatusnew\";
\$memliststatus = \"$memliststatusnew\";
\$piconstatus = \"$piconstatusnew\";
\$sitename = \"$sitenamenew\";
\$siteurl = \"$siteurlnew\";
\$avastatus = \"$avastatusnew\";
\$u2uquota = \"$u2uquotanew\";
\$noreg = \"$noregnew\";
\$nocacheheaders = \"$nocacheheadersnew\";
\$gzipcompress = \"$gzipcompressnew\";
\$boardurl = \"$boardurlnew\";
\$coppa = \"$coppanew\";
\$timeformat = \"$timeformatnew\";
\$adminemail = \"$adminemailnew\";
\$dateformat = \"$dateformatnew\";
\$statspage = \"$statspagenew\";
\$sigbbcode = \"$sigbbcodenew\";
\$sightml = \"$sightmlnew\";
\$indexstats = \"$indexstatsnew\";
\$reportpost = \"$reportpostnew\";
\$showtotaltime = \"$showtotaltimenew\";
?>";

$file = fopen("settings.php", "w");
fwrite($file, $setcontents);
fclose($file);

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textsettingsupdate]</td></tr>";
}
}



if($action == "forum") {
if(!$forumsubmit && !$fdetails) {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp.php?action=forum">
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header"><?=$lang[textforumopts]?></td>
</tr>

<?

$queryf = mysql_query("SELECT * FROM $table_forums WHERE type='forum' AND fup='' ORDER BY displayorder") or die(mysql_error());
while($forum = mysql_fetch_array($queryf)) {

if($forum[status] == "on") {
$on = "selected=\"selected\"";
} else {
$off = "selected=\"selected\"";
}

?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td class="11px"><input type="checkbox" name="delete<?=$forum[fid]?>" value="<?=$forum[fid]?>" /> 
&nbsp;<input type="text" name="name<?=$forum[fid]?>" value="<?=$forum[name]?>" />
&nbsp; <?=$lang[textorder]?> <input type="text" name="displayorder<?=$forum[fid]?>" size="2" value="<?=$forum[displayorder]?>" />
&nbsp; <select name="status<?=$forum[fid]?>">
<option value="on" <?=$on?>><?=$lang[texton]?></option><option value="off" <?=$off?>><?=$lang[textoff]?></option></select>
&nbsp; <select name="moveto<?=$forum[fid]?>"><option value="" selected="selected">-<?=$lang[textnone]?>-</option>
<?
$movequery = mysql_query("SELECT * FROM $table_forums WHERE type='group' ORDER BY displayorder") or die(mysql_error());
while($moveforum = mysql_fetch_array($movequery)) {
echo "<option value=\"$moveforum[fid]\">$moveforum[name]</option>";
}
?>
</select>
<a href="cp.php?action=forum&fdetails=<?=$forum[fid]?>"><?=$lang[textmoreopts]?></a></td>
</tr>

<?
$querys = mysql_query("SELECT * FROM $table_forums WHERE type='sub' AND fup='$forum[fid]' ORDER BY displayorder") or die(mysql_error());
while($forum = mysql_fetch_array($querys)) {

if($forum[status] == "on") {
$on = "selected=\"selected\"";
} else {
$off = "selected=\"selected\"";
}
?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td class="11px"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="checkbox" name="delete<?=$forum[fid]?>" value="<?=$forum[fid]?>" /> 
&nbsp;<input type="text" name="name<?=$forum[fid]?>" value="<?=$forum[name]?>" />
&nbsp; <?=$lang[textorder]?> <input type="text" name="displayorder<?=$forum[fid]?>" size="2" value="<?=$forum[displayorder]?>" />
&nbsp; <select name="status<?=$forum[fid]?>">
<option value="on" <?=$on?>><?=$lang[texton]?></option><option value="off" <?=$off?>><?=$lang[textoff]?></option></select>
&nbsp; <select name="moveto<?=$forum[fid]?>">
<?
$movequery = mysql_query("SELECT * FROM $table_forums WHERE type='forum' ORDER BY displayorder") or die(mysql_error());
while($moveforum = mysql_fetch_array($movequery)) {
if($moveforum[fid] == $forum[fid]) {
echo "<option value=\"$moveforum[fid]\" selected=\"selected\">$moveforum[name]</option>";
} else {
echo "<option value=\"$moveforum[fid]\">$moveforum[name]</option>";
}
}
?>
</select> 
<a href="cp.php?action=forum&fdetails=<?=$forum[fid]?>"><?=$lang[textmoreopts]?></a></td>
</tr>

<?
$on = "";
$off = "";
}

$on = "";
$off = "";
}


$queryg = mysql_query("SELECT * FROM $table_forums WHERE type='group' ORDER BY displayorder") or die(mysql_error());
while($group = mysql_fetch_array($queryg)) {

if($group[status] == "on") {
$on = "selected=\"selected\"";
} else {
$off = "selected=\"selected\"";
}
?>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td class="11px"><input type="checkbox" name="delete<?=$group[fid]?>" value="<?=$group[fid]?>" />
 <input type="text" name="name<?=$group[fid]?>" value="<?=$group[name]?>" />
&nbsp; <?=$lang[textorder]?> <input type="text" name="displayorder<?=$group[fid]?>" size="2" value="<?=$group[displayorder]?>" />
&nbsp; <select name="status<?=$group[fid]?>">
<option value="on" <?=$on?>><?=$lang[texton]?></option><option value="off" <?=$off?>><?=$lang[textoff]?></option></select>
</td>
</tr>

<?
$queryf = mysql_query("SELECT * FROM $table_forums WHERE type='forum' AND fup='$group[fid]' ORDER BY displayorder") or die(mysql_error());
while($forum = mysql_fetch_array($queryf)) {

if($forum[status] == "on") {
$on = "selected=\"selected\"";
} else {
$off = "selected=\"selected\"";
}
?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td class="11px"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="checkbox" name="delete<?=$forum[fid]?>" value="<?=$forum[fid]?>" /> 
&nbsp;<input type="text" name="name<?=$forum[fid]?>" value="<?=$forum[name]?>" />
&nbsp; <?=$lang[textorder]?> <input type="text" name="displayorder<?=$forum[fid]?>" size="2" value="<?=$forum[displayorder]?>" />
&nbsp; <select name="status<?=$forum[fid]?>">
<option value="on" <?=$on?>><?=$lang[texton]?></option><option value="off" <?=$off?>><?=$lang[textoff]?></option></select>
&nbsp; <select name="moveto<?=$forum[fid]?>"><option value="">-<?=$lang[textnone]?>-</option>
<?
$movequery = mysql_query("SELECT * FROM $table_forums WHERE type='group' ORDER BY displayorder") or die(mysql_error());
while($moveforum = mysql_fetch_array($movequery)) {
if($moveforum[fid] == $forum[fup]) {
$curgroup = "selected=\"selected\"";
} else { 
$curgroup = "";
}
echo "<option value=\"$moveforum[fid]\" $curgroup>$moveforum[name]</option>";
}
?>
</select>
<a href="cp.php?action=forum&fdetails=<?=$forum[fid]?>"><?=$lang[textmoreopts]?></a></td>
</tr>

<?
$querys = mysql_query("SELECT * FROM $table_forums WHERE type='sub' AND fup='$forum[fid]' ORDER BY displayorder") or die(mysql_error());
while($forum = mysql_fetch_array($querys)) {

if($forum[status] == "on") {
$on = "selected=\"selected\"";
} else {
$off = "selected=\"selected\"";
}
?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td class="11px"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<input type="checkbox" name="delete<?=$forum[fid]?>" value="<?=$forum[fid]?>" /> 
&nbsp;<input type="text" name="name<?=$forum[fid]?>" value="<?=$forum[name]?>" />
&nbsp; <?=$lang[textorder]?> <input type="text" name="displayorder<?=$forum[fid]?>" size="2" value="<?=$forum[displayorder]?>" />
&nbsp; <select name="status<?=$forum[fid]?>">
<option value="on" <?=$on?>><?=$lang[texton]?></option><option value="off" <?=$off?>><?=$lang[textoff]?></option></select>
&nbsp; <select name="moveto<?=$forum[fid]?>">
<?
$movequery = mysql_query("SELECT * FROM $table_forums WHERE type='forum' ORDER BY displayorder") or die(mysql_error());
while($moveforum = mysql_fetch_array($movequery)) {
if($moveforum[fid] == $forum[fup]) {
echo "<option value=\"$moveforum[fid]\" selected=\"selected\">$moveforum[name]</option>";
} else {
echo "<option value=\"$moveforum[fid]\">$moveforum[name]</option>";
}
}
?>
</select>
<a href="cp.php?action=forum&fdetails=<?=$forum[fid]?>"><?=$lang[textmoreopts]?></a></td>
</tr>

<?
$on = "";
$off = "";
}

$on = "";
$off = "";
}

$on = "";
$off = "";
}
?>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td class="11px"><input type="text" name="newgname" value="<?=$lang[textnewgroup]?>" />
&nbsp; <?=$lang[textorder]?> <input type="text" name="newgorder" size="2" />
&nbsp; <select name="newgstatus">
<option value="on"><?=$lang[texton]?></option><option value="off"><?=$lang[textoff]?></option></select></td>
</tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td class="11px"><input type="text" name="newfname" value="<?=$lang[textnewforum1]?>" />
&nbsp; <?=$lang[textorder]?> <input type="text" name="newforder" size="2" />
&nbsp; <select name="newfstatus">
<option value="on"><?=$lang[texton]?></option><option value="off"><?=$lang[textoff]?></option></select>
&nbsp; <select name="newffup"><option value="" selected="selected">-<?=$lang[textnone]?>-</option>
<?
$gquery = mysql_query("SELECT * FROM $table_forums WHERE type='group' ORDER BY displayorder") or die(mysql_error());
while($group = mysql_fetch_array($gquery)) {
echo "<option value=\"$group[fid]\">$group[name]</option>";
}
?>
</select>
</td></tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td class="11px"><input type="text" name="newsubname" value="<?=$lang[textnewsubf]?>" />
&nbsp; <?=$lang[textorder]?> <input type="text" name="newsuborder" size="2" />
&nbsp; <select name="newsubstatus"><option value="on"><?=$lang[texton]?></option><option value="off"><?=$lang[textoff]?></option></select>
&nbsp; <select name="newsubfup">
<?
$fquery = mysql_query("SELECT * FROM $table_forums WHERE type='forum' ORDER BY displayorder") or die(mysql_error());
while($group = mysql_fetch_array($fquery)) {
echo "<option value=\"$group[fid]\">$group[name]</option>";
}
?>
</select>
</td></tr>

</table>
</td></tr></table>
<center><input type="submit" name="forumsubmit" value="<?=$lang[textsubmitchanges]?>" /></center>
</form>

</td>
</tr>

<?
}

if($fdetails && !$forumsubmit) {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp.php?action=forum&fdetails=<?=$fdetails?>">
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header" colspan="2"><?=$lang[textforumopts]?></td>
</tr>

<?
$queryg = mysql_query("SELECT * FROM $table_forums WHERE fid='$fdetails'") or die(mysql_error());
$forum = mysql_fetch_array($queryg);

$themelist = "<select name=\"themeforumnew\">";
$querytheme = mysql_query("SELECT name FROM $table_themes") or die(mysql_error());
while($theme = mysql_fetch_array($querytheme)) {
if($theme[name] == $forum[theme]) {
$themelist .= "<option value=\"$theme[name]\" selected=\"selected\">$theme[name]</option>\n";
}
else {
$themelist .= "<option value=\"$theme[name]\">$theme[name]</option>\n";
}
}
$themelist  .= "</select>";


if($forum[private] == "staff") {
$checked1 = "checked=\"checked\"";
} else {
$checked1 = "";
}

if($forum[allowhtml] == "yes") {
$checked2 = "checked=\"checked\"";
} else {
$checked2 = "";
}

if($forum[allowsmilies] == "yes") {
$checked3 = "checked=\"checked\"";
} else {
$checked3 = "";
}

if($forum[allowbbcode] == "yes") {
$checked4 = "checked=\"checked\"";
} else {
$checked4 = "";
}

if($forum[guestposting] == "yes") {
$checked5 = "checked=\"checked\"";
} else {
$checked5 = "";
}

if($forum[allowimgcode] == "yes") { 
$checked6 = "checked=\"checked\""; 
} else { 
$checked6 = ""; 
}

$pperm = explode("|", $forum[postperm]);

if($pperm[0] == "2") {
$type12 = "selected=\"selected\"";
} elseif($pperm[0] == "3") {
$type13 = "selected=\"selected\"";
} elseif($pperm[0] == "4") {
$type14 = "selected=\"selected\"";
} elseif($pperm[0] == "1") {
$type11 = "selected=\"selected\"";
}

if($pperm[1] == "2") {
$type22 = "selected=\"selected\"";
} elseif($pperm[1] == "3") {
$type23 = "selected=\"selected\"";
} elseif($pperm[0] == "4") {
$type24 = "selected=\"selected\"";
} elseif($pperm[1] == "1") {
$type21 = "selected=\"selected\"";
}


$forum[private] = str_replace("pw|", "", $forum[private]);
?>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang[textforumname]?></td>
<td><input type="text" name="namenew" value="<?=$forum[name]?>" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang[textdesc]?></td>
<td><textarea rows="4" cols="30" name="descnew"><?=$forum[description]?></textarea></td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang[textallow]?></td>
<td class="11px"><input type="checkbox" name="allowhtmlnew" value="yes" <?=$checked2?> /><?=$lang[texthtml]?><br />
<input type="checkbox" name="allowsmiliesnew" value="yes" <?=$checked3?> /><?=$lang[textsmilies]?><br />
<input type="checkbox" name="allowbbcodenew" value="yes" <?=$checked4?> /><?=$lang[textbbcode]?><br />
<input type="checkbox" name="guestpostingnew" value="yes" <?=$checked5?> /><?=$lang[textguestposting]?><br />
<input type="checkbox" name="allowimgcodenew" value="yes" <?=$checked6?> /><?=$lang[textimgcode]?></td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang[texttheme]?></td>
<td><?=$themelist?></td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang[whopostop1]?></td>
<td><select name="postperm1">
<option value="1" <?=$type11?>><?=$lang[textpostpermission1]?></option>
<option value="2" <?=$type12?>><?=$lang[textpostpermission2]?></option>
<option value="3" <?=$type13?>><?=$lang[textpostpermission3]?></option>
<option value="4" <?=$type14?>><?=$lang[textpostpermission4]?></option>
</td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang[whopostop2]?></td>
<td><select name="postperm2">
<option value="1" <?=$type21?>><?=$lang[textpostpermission1]?></option>
<option value="2" <?=$type22?>><?=$lang[textpostpermission2]?></option>
<option value="3" <?=$type23?>><?=$lang[textpostpermission3]?></option>
<option value="4" <?=$type24?>><?=$lang[textpostpermission4]?></option>
</td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang[textuserlist]?></td>
<td><textarea rows="4" cols="30" name="userlistnew"><?=$forum[userlist]?></textarea></td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang[textstaffonly]?></td>
<td><input type="checkbox" name="privatenew" value="staff" <?=$checked1?> /></td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang[textdeleteques]?></td>
<td><input type="checkbox" name="delete" value="<?=$forum[fid]?>" /></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="forumsubmit" value="<?=$lang[textsubmitchanges]?>" /></center>
</form>

</td>
</tr>
<?
}

if($forumsubmit) {
if(!$fdetails) {
$queryforum = mysql_query("SELECT fid, type FROM $table_forums WHERE type='forum' OR type='sub'") or die(mysql_error());
while($forum = mysql_fetch_array($queryforum)) {
$displayorder = "displayorder$forum[fid]";
$displayorder = "${$displayorder}";
$name = "name$forum[fid]";
$name = "${$name}";
$status = "status$forum[fid]";
$status = "${$status}";
$delete = "delete$forum[fid]";
$delete = "${$delete}";
$moveto = "moveto$forum[fid]";
$moveto = "${$moveto}";

if($delete != "") {
mysql_query("DELETE FROM $table_forums WHERE (type='forum' OR type='sub') AND fid='$delete'") or die(mysql_error());

$querythread = mysql_query("SELECT * FROM $table_threads WHERE fid='$delete'") or die(mysql_error());
while($thread = mysql_fetch_array($querythread)) {
mysql_query("DELETE FROM $table_threads WHERE tid='$thread[tid]'") or die(mysql_error());
mysql_query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$thread[author]'") or die(mysql_error());

$querypost = mysql_query("SELECT * FROM $table_posts WHERE tid='$thread[tid]'") or die(mysql_error());
while($post = mysql_fetch_array($querypost)) {
mysql_query("DELETE FROM $table_posts WHERE pid='$post[pid]'") or die(mysql_error());
mysql_query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$post[author]'") or die(mysql_error());
}
}
}

mysql_query("UPDATE $table_forums SET name='$name', displayorder='$displayorder', status='$status', fup='$moveto' WHERE fid='$forum[fid]'") or die(mysql_error());
}


$querygroup = mysql_query("SELECT fid FROM $table_forums WHERE type='group'") or die(mysql_error());
while($group = mysql_fetch_array($querygroup)) {
$name = "name$group[fid]";
$name = "${$name}";
$displayorder = "displayorder$group[fid]";
$displayorder = "${$displayorder}";
$status = "status$group[fid]";
$status = "${$status}";
$delete = "delete$group[fid]";
$delete = "${$delete}";

if($delete != "") {
$query = mysql_query("SELECT fid FROM $table_forums WHERE type='forum' AND fup='$delete'") or die(mysql_error());
while($forum = mysql_fetch_array($query)) {
mysql_query("UPDATE $table_forums SET fup='' WHERE type='forum' AND fup='$delete'") or die(mysql_error());
}

mysql_query("DELETE FROM $table_forums WHERE type='group' AND fid='$delete'") or die(mysql_error());
}

mysql_query("UPDATE $table_forums SET name='$name', displayorder='$displayorder', status='$status' WHERE fid='$group[fid]'") or die(mysql_error());
}

if($newfname != $lang[textnewforum1]) { 
mysql_query("INSERT INTO $table_forums VALUES ('forum', '', '$newfname', '$newfstatus', '', '', '$newforder', '', '', 'no', 'yes', 'yes', 'no', '', '', '0', '0', '$newffup', '1', 'yes')") or die(mysql_error()); 
}

if($newgname != $lang[textnewgroup]) { 
mysql_query("INSERT INTO $table_forums VALUES ('group', '', '$newgname', '$newgstatus', '', '', '$newgorder', '', '', '', '', '', '', '', '', '0', '0', '', '', '')") or die(mysql_error()); 
}

if($newsubname != $lang[textnewsubf]) { 
mysql_query("INSERT INTO $table_forums VALUES ('sub', '', '$newsubname', '$newsubstatus', '', '', '$newsuborder', '', '', 'no', 'yes', 'yes', 'no', '', '', '0', '0', '$newsubfup', '1', 'yes')") or die(mysql_error()); 
}

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textforumupdate]</td></tr>";
}
else {
mysql_query("UPDATE $table_forums SET name='$namenew', description='$descnew', allowhtml='$allowhtmlnew', allowsmilies='$allowsmiliesnew', allowbbcode='$allowbbcodenew', guestposting='$guestpostingnew', theme='$themeforumnew', userlist='$userlistnew', private='$privatenew', postperm='$postperm1|$postperm2', allowimgcode='$allowimgcodenew' WHERE fid='$fdetails'") or die(mysql_error());
if($delete != "") {
mysql_query("DELETE FROM $table_forums WHERE fid='$delete'") or die(mysql_error());
}

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textforumupdate]</td></tr>";
}
}
}



if($action == "mods") {
if(!$modsubmit) {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp.php?action=mods">
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td><?=$lang[textforum]?></td>
<td><?=$lang[textmoderator]?></td>
</tr>

<?
$queryf = mysql_query("SELECT name, moderator, fid FROM $table_forums WHERE type='forum'") or die(mysql_error());
while($mod = mysql_fetch_array($queryf)) {
?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><?=$mod[name]?></td>
<td><input type="text" name="mod<?=$mod[fid]?>" value="<?=$mod[moderator]?>" /></td>
</tr>

<?

$querys = mysql_query("SELECT name, moderator, fid FROM $table_forums WHERE type='sub' AND fup='$mod[fid]' ORDER BY displayorder") or die(mysql_error());
while($mod = mysql_fetch_array($querys)) {
?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <?=$mod[name]?></td>
<td> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="text" name="mod<?=$mod[fid]?>" value="<?=$mod[moderator]?>" /></td>
</tr>

<?
}

}
?>

</table>
</td></tr></table>
<span class="11px"><?=$lang[multmodnote]?></span>
<center><input type="submit" name="modsubmit" value="<?=$lang[textsubmitchanges]?>" /></center>
</form>

</td>
</tr>

<?
}

if($modsubmit) {
$queryforum = mysql_query("SELECT fid FROM $table_forums") or die(mysql_error());

while($forum = mysql_fetch_array($queryforum)) {
$mod = "mod$forum[fid]";
$mod = "${$mod}";
mysql_query("UPDATE $table_forums SET moderator='$mod' WHERE fid='$forum[fid]'") or die(mysql_error());


$modz = explode(", ", $mod);
for($num = 0; $num < count($modz); $num++) {

if($modz[$num] != "") {
$query = mysql_query("SELECT status FROM $table_members WHERE username='$modz[$num]'") or die(mysql_error());
$userinfo = mysql_fetch_array($query);

if($userinfo[status] != "Administrator" && $userinfo[status] != "Super Moderator") {
mysql_query("UPDATE $table_members SET status='Moderator' WHERE username='$modz[$num]'") or die(mysql_error());
}
else {
echo "";
}
}

}
}

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textmodupdate]</td></tr>";
}
}



if($action == "members") {
if(!$membersubmit) {
?>
<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />

<?
if(!$members) {
?>

<form method="post" action="cp.php?action=members&members=search">
<span class="12px"><?=$lang[textsrchusr]?></span> <input type="text" name="srchmem"><br />
<span class="12px"><?=$lang[textwithstatus]?></span> 

<select name="srchstatus">
<option value="0"><?=$lang[anystatus]?></option>
<option value="Administrator"><?=$lang[textadmin]?></option>
<option value="Super Moderator"><?=$lang[textsupermod]?></option>
<option value="Moderator"><?=$lang[textmod]?></option>
<option value="Member"><?=$lang[textmem]?></option>
<option value="Banned"><?=$lang[textbanned]?></option>
</select><br />
<input type="submit" value="<?=$lang[textgo]?>" />
</form>
</td></tr>

<?
}

if($members == "search") {
?>
<form method="post" action="cp.php?action=members">
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td><?=$lang[textdeleteques]?></td>
<td><?=$lang[textusername]?></td>
<td><?=$lang[textpassword]?></td>
<td><?=$lang[textposts]?></td>
<td><?=$lang[textstatus]?></td>
<td><?=$lang[textcusstatus]?></td>	
</tr>

<?
if($srchstatus == "0") {
$query = mysql_query("SELECT * FROM $table_members WHERE username LIKE '%$srchmem%' ORDER BY username") or die(mysql_error());
} else {
$query = mysql_query("SELECT * FROM $table_members WHERE username LIKE '%$srchmem%' AND status='$srchstatus' ORDER BY username") or die(mysql_error());
}
while($member = mysql_fetch_array($query)) {

if($member[status] == "Administrator") { 
$adminselect = "selected=\"selected\"";
}

if($member[status] == "Super Moderator") { 
$smodselect = "selected=\"selected\"";
} 

if($member[status] == "Moderator") { 
$modselect = "selected=\"selected\"";
} 

if($member[status] == "Member") { 
$memselect = "selected=\"selected\"";
} 

if($member[status] == "Banned") { 
$banselect = "selected=\"selected\"";
}
?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><input type="checkbox" name="delete<?=$member[uid]?>" value="<?=$member[uid]?>" /></td>
<td><a href="member.php?action=editpro&username=<?=$member[username]?>&password=<?=$member[password]?>&editlogsubmit=Edit%20Profile"><?=$member[username]?></a></td>
<td><input type="text" size="12" name="pw<?=$member[uid]?>" value="<?=$member[password]?>" /></td>
<td><?=$member[postnum]?></td>
<td><select name="status<?=$member[uid]?>">
<option value="Administrator" <?=$adminselect?>><?=$lang[textadmin]?></option>
<option value="Super Moderator" <?=$smodselect?>><?=$lang[textsupermod]?></option>
<option value="Moderator" <?=$modselect?>><?=$lang[textmod]?></option>
<option value="Member" <?=$memselect?>><?=$lang[textmem]?></option>
<option value="Banned" <?=$banselect?>><?=$lang[textbanned]?></option>
</select></td>
<td><input type="text" size="20" name="cusstatus<?=$member[uid]?>" value="<?=$member[customstatus]?>" /></td>
</tr>

<?
$adminselect = "";
$smodselect = "";
$modselect = "";
$memselect = "";
$banselect = "";
}
?>

</table>
</td></tr></table>
<center><input type="submit" name="membersubmit" value="<?=$lang[textsubmitchanges]?>" /></center>
<input type="hidden" name="srchmem" value="<?=$srchmem?>">
<input type="hidden" name="srchstatus" value="<?=$srchstatus?>">
</form>

</td>
</tr>

<?
}
}

if($membersubmit) {
if($srchstatus == "0") {
$query = mysql_query("SELECT uid, username FROM $table_members WHERE username LIKE '%$srchmem%'") or die(mysql_error());
} else {
$query = mysql_query("SELECT uid, username FROM $table_members WHERE username LIKE '%$srchmem%' AND status='$srchstatus'") or die(mysql_error());
}

while($mem = mysql_fetch_array($query)) {
$status = "status$mem[uid]";
$status = "${$status}";
$cusstatus = "cusstatus$mem[uid]";
$cusstatus = "${$cusstatus}";
$pw = "pw$mem[uid]";
$pw = "${$pw}";
$delete = "delete$mem[uid]";
$delete = "${$delete}";

if($delete != "") {
mysql_query("DELETE FROM $table_members WHERE uid='$delete'") or die(mysql_error());
}
else {
if(ereg('"', $pw) || ereg("'", $pw)) {
$lang[textmembersupdate] = "$mem[username]: $lang[textpwincorrect]";
} else {
	$newcustom = addslashes($cusstatus);
mysql_query("UPDATE $table_members SET status='$status', customstatus='$newcustom', password='$pw' WHERE uid='$mem[uid]'") or die(mysql_error());
}
}
}


echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textmembersupdate]</td></tr>";
}
}



if($action == "ipban") { 
if(!$ipbansubmit) { 
?> 

<tr bgcolor="<?=$altbg2?>"> 
<td align="center"> 
<br /> 
<form method="post" action="cp.php?action=ipban"> 
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center"> 
<tr><td bgcolor="<?=$bordercolor?>"> 

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%"> 
<tr> 
<td class="header"><?=$lang[textdeleteques]?></td> 
<td class="header"><?=$lang[textip]?>:</td> 
<td class="header"><?=$lang[textadded]?></td> 
</tr> 

<? 
$query = mysql_query("SELECT * FROM $table_banned ORDER BY dateline") or die(mysql_error()); 
while($ipaddress = mysql_fetch_array($query)) { 

for($i=1; $i<=4; ++$i) { 
$j = "ip" . $i; 
if ($ipaddress[$j] == -1) $ipaddress[$j] = "*"; 
} 

$ipdate = date("n/j/y", $ipaddress[dateline] + ($timeoffset * 3600)) . " $lang[textat] " . date("$timecode", $ipaddress[dateline] + ($timeoffset * 3600)); 
$theip = "$ipaddress[ip1].$ipaddress[ip2].$ipaddress[ip3].$ipaddress[ip4]";
?> 

<tr bgcolor="<?=$altbg2?>"> 
<td class="tablerow"><input type="checkbox" name="delete<?=$ipaddress[id]?>" value="<?=$ipaddress[id]?>" /></td> 
<td class="tablerow"><?=$theip?></td> 
<td class="tablerow"><?=$ipdate?></td> 
</tr> 

<? 
} 
$query = mysql_query("SELECT id FROM $table_banned WHERE (ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')") or die(mysql_error()); 
$result = mysql_fetch_array($query); 
if ($result) $warning = $lang[ipwarning]; 
?> 
<tr bgcolor="<?=$altbg2?>"><td colspan="3"> </td></tr> 
<tr bgcolor="<?=$altbg1?>"> 
<td colspan="3" class="tablerow"><?=$lang[textnewip]?>   
<input type="text" name="newip1" size="3" maxlength="3" />.<input type="text" name="newip2" size="3" maxlength="3" />.<input type="text" name="newip3" size="3" maxlength="3" />.<input type="text" name="newip4" size="3" maxlength="3" /></td> 
</tr> 

</table> 
</td></tr></table> 
<span class="11px"><?=$lang[currentip]?> <b><?=$onlineip?></b><?=$warning?><br /><?=$lang[multipnote]?></span> 
<center><input type="submit" name="ipbansubmit" value="<?=$lang[textsubmitchanges]?>" /></center> 
</form> 

</td> 
</tr> 

<? 
} 

if($ipbansubmit) { 
$queryip = mysql_query("SELECT id FROM $table_banned") or die(mysql_error()); 
$newid = 1; 
while($ip = mysql_fetch_array($queryip)) { 
$delete = "delete$ip[id]"; 
$delete = "${$delete}";

if($delete != "") { 
$query = mysql_query("DELETE FROM $table_banned WHERE id='$delete'") or die(mysql_error()); 
} 
elseif($ip[id] > $newid) { 
$query = mysql_query("UPDATE $table_banned SET id='$newid' WHERE id='$ip[id]'") or die(mysql_error()); 
} 
$newid++;
}

$status = $lang[textipupdate]; 

if($newip1 != "" || $newip2 != "" || $newip3 != "" || $newip4 != "") { 

$invalid = 0;

for($i=1;$i<=4 && !$invalid;++$i) { 
$newip = "newip$i"; 
$newip = "${$newip}"; 
$newip = trim($newip); 
if ($newip == "*") $ip[$i] = -1; 
elseif (ereg("^[0-9]+$", $newip)) $ip[$i] = $newip; 
else $invalid = 1; 
} 

if ($invalid) $status = $lang[invalidip]; 
else { 
$query = mysql_query("SELECT id FROM $table_banned WHERE (ip1='$ip[1]' OR ip1='-1') AND (ip2='$ip[2]' OR ip2='-1') AND (ip3='$ip[3]' OR ip3='-1') AND (ip4='$ip[4]' OR ip4='-1')") or die(mysql_error()); 
$result = mysql_fetch_array($query); 
if ($result) $status = $lang[existingip]; 
else $query = mysql_query("INSERT INTO $table_banned VALUES ('$ip[1]', '$ip[2]', '$ip[3]', '$ip[4]', '$onlinetime', '$newid')") or die(mysql_error()); 
} 
} 

echo "<tr bgcolor=\"$altbg2\"><td align=\"center\" class=\"tablerow\">$status</td></tr>";
} 
}



if($action == "upgrade") { 
if($upgradesubmit) { 

$explode = explode(";", $upgrade); 
$count = sizeof($explode); 

for($num=0;$num<$count;$num++) { 
$explode[$num]=stripslashes($explode[$num]); 
if($explode[$num] != "") { 
mysql_query("$explode[$num]") or die(mysql_error()); 
} 
}
echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[upgradesuccess] </td></tr>"; 
} 
if(!$upgradesubmit) { 

?> 

<tr bgcolor="<?=$altbg2?>"> 
<td align="center"> 
<br /> 
<form method="post" action="cp.php?action=upgrade"> 
<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center"> 
<tr><td bgcolor="<?=$bordercolor?>"> 

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%"> 

<tr class="header"> 
<td colspan=2><?=$lang[textupgrade]?></td> 
</tr> 

<tr bgcolor="<?=$altbg1?>" class="tablerow"> 
<td valign="top"><?=$lang[upgrade]?><br /><textarea cols="35" rows="7" name="upgrade"></textarea><br /><?=$lang[upgradenote]?></td> 
</tr> 
</table> 
</td></tr></table> 
<center><input type="submit" name="upgradesubmit" value="<?=$lang[textsubmitchanges]?>" /></center> 
</form> 

</td> 
</tr> 

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
