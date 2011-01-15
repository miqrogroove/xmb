<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

require "./header.php";
loadtemplates('header,footer');

$navigation = "&raquo; $lang_textcp";
eval("\$header = \"".template("header")."\";");
echo $header;

if(!$xmbuser || !$xmbpw) {
	$xmbuser = "";
	$xmbpw = "";
	$status = "";
}

if($status != "Administrator" && $status !="Super Administrator") {
	eval("\$notadmin = \"".template("error_nologinsession")."\";");
	echo $notadmin;
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
}

if(getenv(HTTP_CLIENT_IP)) { 
	$ip = getenv(HTTP_CLIENT_IP); 
} elseif(getenv(HTTP_X_FORWARDED_FOR)) { 
	$ip = getenv(HTTP_X_FORWARDED_FOR); 
} else { 
	$ip = getenv(REMOTE_ADDR); 
} 

$time = time();
$string = "$xmbuser|#||#|$ip|#||#|$time|#||#|$_SERVER[REQUEST_URI]\n";
@chmod('./cplogfile.php', 0777);
$filehandle = @fopen('./cplogfile.php','a');
if(!$filehandle){
	echo('<center><b>Wrong File permissions set. Please CHMOD the <i>cplogfile.php</i> to <i>777</i></b></center>');
}
@flock($filehandle, 2);
@fwrite($filehandle, $string);
@fclose($filehandle);
@chmod('./cplogfile.php', 0766);
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
<a href="cp2.php?action=restrictions"><?=$lang_cprestricted?></a> - <a href="cp.php?action=ipban"><?=$lang_textipban?></a> -
<a href="cp.php?action=upgrade"><?=$lang_textupgrade?></a> - <a href="cp.php?action=search"><?=$lang_cpsearch?></a><br />
<a href="cp2.php?action=themes"><?=$lang_themes?></a> - <a href="cp2.php?action=smilies"><?=$lang_smilies?></a> -
<a href="cp2.php?action=censor"><?=$lang_textcensors?></a> - <a href="cp2.php?action=ranks"><?=$lang_textuserranks?></a> -
<a href="cp2.php?action=newsletter"><?=$lang_textnewsletter?></a> - <a href="cp2.php?action=prune"><?=$lang_textprune?></a> -
<a href="cp2.php?action=templates"><?=$lang_templates?></a> - <a href="cp2.php?action=attachments"><?=$lang_textattachman?></a><br />
<a href="cp2.php?action=cplog"><?=$lang_cplog?></a>
<br /><tr bgcolor="<?=$altbg2?>" class="tablerow"><td align="center"><a href="tools.php?action=fixttotals"><?=$lang_textfixthread?></a> - <a href="tools.php?action=fixftotals"><?=$lang_textfixposts?></a> - <a href="tools.php?action=fixmposts"><?=$lang_textfixmemposts?></a> - <a href="tools.php?action=updatemoods"><?=$lang_textfixmoods?></a> - <a href="tools.php?action=u2udump"><?=$lang_u2udump?></a> - <a href="tools.php?action=whosonlinedump"><?=$lang_cpwodump?></a>
<br /><a href="tools.php?action=fixforumthemes"><?=$lang_fixforumthemes?></a>

<?
//Get All Plugins
for($plugnum=1; $plugname[$plugnum] != ""; $plugnum++) {
	if(!$plugurl[$plugnum] || !$plugname[$plugnum]) {
		echo $lang_textbadplug;
	} else {
		if($plugadmin[$plugnum] == "yes") {
			$pluglinks .= "<a href=\"$plugurl[$plugnum]\">$plugname[$plugnum]</a> - ";
		}
	}
}
if($pluglinks) {
echo "<br />$lang_textplugins $pluglinks";
}
?>
</td>
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
if ($thafile == $SETTINGS['langfile']) {
$langfileselect .= "<option value=\"$thafile\" selected=\"selected\">$thafile</option>\n";
}
else {
$langfileselect .= "<option value=\"$thafile\">$thafile</option>\n";
}
}
}

$langfileselect .= "</select>";

$themelist = "<select name=\"themenew\">\n";
$query = $db->query("SELECT name FROM $table_themes");
while($themeinfo = $db->fetch_array($query)) {
if($themeinfo[name] == $SETTINGS[theme]) {
$themelist .= "<option value=\"$themeinfo[name]\" selected=\"selected\">$themeinfo[name]</option>\n";
}
else {
$themelist .= "<option value=\"$themeinfo[name]\">$themeinfo[name]</option>\n";
}
}
$themelist  .= "</select>";

if($SETTINGS['bbstatus'] == "on") {
$onselect = "selected=\"selected\"";
} else {
$offselect = "selected=\"selected\"";
}

if($SETTINGS['whosonlinestatus'] == "on") {
$whosonlineon = "selected=\"selected\"";
} else {
$whosonlineoff = "selected=\"selected\"";
}

if($SETTINGS['regstatus'] == "on") {
$regon = "selected=\"selected\"";
} else {
$regoff = "selected=\"selected\"";
}

if($SETTINGS['regviewonly'] == "on") {
$regonlyon = "selected=\"selected\"";
} else {
$regonlyoff = "selected=\"selected\"";
}

if($SETTINGS['catsonly'] == "on") {
$catsonlyon = "selected=\"selected\"";
} else {
$catsonlyoff = "selected=\"selected\"";
}

if($SETTINGS['hideprivate'] == "on") {
$hideon = "selected=\"selected\"";
} else {
$hideoff = "selected=\"selected\"";
}

if($SETTINGS['emailcheck'] == "on") {
$echeckon = "selected=\"selected\"";
} else {
$echeckoff = "selected=\"selected\"";
}

if($SETTINGS['bbrules'] == "on") {
$ruleson = "selected=\"selected\"";
} else {
$rulesoff = "selected=\"selected\"";
}

if($SETTINGS['todaysposts'] == "on") {
$todayspostson = "selected=\"selected\"";
} else {
$todayspostsoff = "selected=\"selected\"";
}

if($SETTINGS['stats'] == "on") {
$statson = "selected=\"selected\"";
} else {
$statsoff = "selected=\"selected\"";
}

if($SETTINGS['searchstatus'] == "on") {
$searchon = "selected=\"selected\"";
} else {
$searchoff = "selected=\"selected\"";
}

if($SETTINGS['faqstatus'] == "on") {
$faqon = "selected=\"selected\"";
} else {
$faqoff = "selected=\"selected\"";
}

if($SETTINGS['memliststatus'] == "on") {
$memliston = "selected=\"selected\"";
} else {
$memlistoff = "selected=\"selected\"";
}

if($SETTINGS['avastatus'] == "on") {
$avataron = "selected=\"selected\"";
} elseif($avastatus == "list") {
$avatarlist = "selected=\"selected\"";
} else {
$avataroff = "selected=\"selected\"";
}

if($SETTINGS['gzipcompress'] == "on") {
$gzipcompresson = "selected=\"selected\"";
} else {
$gzipcompressoff = "selected=\"selected\"";
}

if($SETTINGS['coppa'] == "on") {
$coppaon = "selected=\"selected\"";
} else {
$coppaoff = "selected=\"selected\"";
}

if($SETTINGS['timeformat'] == "24") {
$check24 = "checked=\"checked\"";
} else {
$check12 = "checked=\"checked\"";
}

if($SETTINGS['sigbbcode'] == "on") {
	$sigbbcodeon = "selected=\"selected\"";
} else {
	$sigbbcodeoff = "selected=\"selected\"";
}

if($SETTINGS['sightml'] == "on") {
$sightmlon = "selected=\"selected\"";
} else {
$sightmloff = "selected=\"selected\"";
}

if($SETTINGS['reportpost'] == "on") {
$reportposton = "selected=\"selected\"";
} else {
$reportpostoff = "selected=\"selected\"";
}

if($SETTINGS['bbinsert'] != "on") {
$bbinsertoff = "selected=\"selected\"";
} else {
$bbinserton = "selected=\"selected\"";
}

if($SETTINGS['smileyinsert'] != "on") {
$smileyinsertoff = "selected=\"selected\"";
} else {
$smileyinserton = "selected=\"selected\"";
}

if($SETTINGS['doublee'] == "on") {
$doubleeon = "selected=\"selected\"";
} else {
$doubleeoff = "selected=\"selected\"";
}
if($SETTINGS['editedby'] == "on") {
$editedbyon = "selected=\"selected\"";
} else {
$editedbyoff = "selected=\"selected\"";
}
if($SETTINGS['dotfolders'] == "on") {
$dotfolderson = "selected=\"selected\"";
} else {
$dotfoldersoff = "selected=\"selected\"";
}
if($SETTINGS['attachimgpost'] == "on") {
$attachimgposton = "selected=\"selected\"";
} else {
$attachimgpostoff = "selected=\"selected\"";
}

if($SETTINGS['tickerstatus'] == "on") {
$tickerstatusonon = "selected=\"selected\"";
} else {
$tickerstatusoff = "selected=\"selected\"";
}

$SETTINGS['bboffreason'] = stripslashes($SETTINGS['bboffreason']);
$SETTINGS['bbrulestxt'] = stripslashes($SETTINGS['bbrulestxt']);
$SETTINGS['tickercontents'] = stripslashes($SETTINGS['tickercontents']);

?>
<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp.php?action=settings">
<table cellspacing="0" cellpadding="0" border="0" width="600" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td><?=$lang_textsetting?></td>
<td><?=$lang_textvalue?></td>
</tr>

<?
printsetting2($lang_bbname, "bbnamenew", $SETTINGS[bbname], "50");
printsetting2($lang_textsitename, "sitenamenew", $SETTINGS[sitename], "50");
printsetting2($lang_textsiteurl, "siteurlnew", $SETTINGS[siteurl], "50");
printsetting2($lang_textboardurl, "boardurlnew", $SETTINGS[boardurl], "50");
printsetting2($lang_adminemail, "adminemailnew", $SETTINGS[adminemail], "50");
?>

<tr>
<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$lang_textlanguage?></td>
<td class="tablerow" bgcolor="<?=$altbg2?>"><?=$langfileselect?></td>
</tr>

<tr>
<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$lang_texttheme?></td>
<td class="tablerow" bgcolor="<?=$altbg2?>"><?=$themelist?></td>
</tr>

<?
printsetting2($lang_textppp, "postperpagenew", $SETTINGS[postperpage], "2");
printsetting2($lang_texttpp, "topicperpagenew", $SETTINGS[topicperpage], "2");
printsetting2($lang_textmpp, "memberperpagenew", $SETTINGS[memberperpage], "2");
printsetting2($lang_texthottopic, "hottopicnew", $SETTINGS[hottopic], "2");
printsetting2($lang_textflood, "floodctrlnew", $SETTINGS[floodctrl], "2");
printsetting2($lang_u2uquota, "u2uquotanew", $SETTINGS[u2uquota], "2");
printsetting1($lang_textbstatus, "bbstatusnew", $onselect, $offselect, $langfile);
?>

<tr>
<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$lang_textbboffreason?></td>
<td class="tablerow" bgcolor="<?=$altbg2?>"><textarea rows="5" name="bboffreasonnew" cols="50"><?=$SETTINGS[bboffreason]?></textarea></td>
</tr>

<?
printsetting1($lang_whosonline_on, "whos_on", $whosonlineon, $whosonlineoff, $langfile);
printsetting1($lang_reg_on, "reg_on", $regon, $regoff, $langfile);
printsetting1($lang_textreggedonly, "regviewnew", $regonlyon, $regonlyoff, $langfile);
printsetting1($lang_textcatsonly, "catsonlynew", $catsonlyon, $catsonlyoff, $langfile);
printsetting1($lang_texthidepriv, "hidepriv", $hideon, $hideoff, $langfile);
printsetting1($lang_emailverify, "emailchecknew", $echeckon, $echeckoff, $langfile);
printsetting1($lang_textbbrules, "bbrulesnew", $ruleson, $rulesoff, $langfile);
?>

<tr>
<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$lang_textbbrulestxt?></td>
<td class="tablerow" bgcolor="<?=$altbg2?>"><textarea rows="5" name="bbrulestxtnew" cols="50"><?=$SETTINGS[bbrulestxt]?></textarea></td>
</tr>

<tr><td class="tablerow" bgcolor="<?=$altbg1?>"><?=$lang_textavastatus?></td>
<td class="tablerow" bgcolor="<?=$altbg2?>"><select name="avastatusnew">
<option value="on" <?=$avataron?>><?=$lang_texton?></option><option value="list" <?=$avatarlist?>><?=$lang_textlist?></option>
<option value="off" <?=$avataroff?>><?=$lang_textoff?></option>
</select></td></tr>

<?

printsetting1($lang_todayspostsstatus, "todayspostsnew", $todayspostson, $todayspostsoff, $langfile);
printsetting1($lang_statssatus, "statsnew", $statson, $statsoff, $langfile);



printsetting1($lang_textsearchstatus, "searchstatusnew", $searchon, $searchoff, $langfile);
printsetting1($lang_textfaqstatus, "faqstatusnew", $faqon, $faqoff, $langfile);
printsetting1($lang_textmemliststatus, "memliststatusnew", $memliston, $memlistoff, $langfile);
printsetting1($lang_reportpoststatus, "reportpostnew", $reportposton, $reportpostoff, $langfile);
printsetting1($lang_gzipcompression, "gzipcompressnew", $gzipcompresson, $gzipcompressoff, $langfile);
printsetting1($lang_coppastatus, "coppanew", $coppaon, $coppaoff, $langfile);
printsetting1($lang_sigbbcode, "sigbbcodenew", $sigbbcodeon, $sigbbcodeoff, $langfile);
printsetting1($lang_sightml, "sightmlnew", $sightmlon, $sightmloff, $langfile);
printsetting1($lang_bbinsert, "bbinsertnew", $bbinserton, $bbinsertoff, $langfile);
printsetting1($lang_smileyinsert, "smileyinsertnew", $smileyinserton, $smileyinsertoff, $langfile);
printsetting1($lang_doublee, "doubleenew", $doubleeon, $doubleeoff, $langfile);
?>

<tr>
<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$lang_texttimeformat?></td>
<td class="tablerow" bgcolor="<?=$altbg2?>"><input type="radio" value="24" name="timeformatnew" <?=$check24?>><?=$lang_text24hour?> <input type="radio" value="12" name="timeformatnew" <?=$check12?>><?=$lang_text12hour?></td>
</tr>

<?
printsetting2($lang_dateformat, "dateformatnew", $SETTINGS[dateformat], "20");
printsetting2($lang_smtotal, "smtotalnew", $SETTINGS[smtotal], "5");
printsetting2($lang_smcols, "smcolsnew", $SETTINGS[smcols], "5");
printsetting2($lang_addtime, "addtimenew", $SETTINGS[addtime], "3");
printsetting1($lang_editedby, "editedbynew", $editedbyon, $editedbyoff, $langfile);
printsetting1($lang_dotfolders, "dotfoldersnew", $dotfolderson, $dotfoldersoff, $langfile);
printsetting1($lang_attachimginpost, "attachimgpostnew", $attachimgposton, $attachimgpostoff, $langfile);
printsetting1($lang_what_tickerstatus, "tickerstatusnew", $tickerstatuson, $tickerstatusoff, $langfile);
printsetting2($lang_what_tickerdelay, "tickerdelaynew", $SETTINGS[tickerdelay], "5");

?>
<tr>
<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$lang_tickercontents?></td>
<td class="tablerow" bgcolor="<?=$altbg2?>"><textarea rows="5" name="tickercontentsnew" cols="50"><?=$SETTINGS[tickercontents]?></textarea></td>
</tr>

</table>
</td></tr></table><br />
<center><input type="submit" name="settingsubmit" value="<?=$lang_textsubmitchanges?>" /></center>
</form>

</td>
</tr>

<?
	}

	if($settingsubmit) {
		$bbrulestxtnew = addslashes($bbrulestxtnew);
		$bboffreasonnew = addslashes($bboffreasonnew);
		$tickercontentsnew = addslashes($tickercontentsnew);

		$db->query("UPDATE $table_settings SET langfile='$langfilenew', bbname='$bbnamenew', postperpage='$postperpagenew', topicperpage='$topicperpagenew', hottopic='$hottopicnew', theme='$themenew', bbstatus='$bbstatusnew', whosonlinestatus='$whos_on', regstatus='$reg_on', bboffreason='$bboffreasonnew', regviewonly='$regviewnew', floodctrl='$floodctrlnew', memberperpage='$memberperpagenew', catsonly='$catsonlynew', hideprivate='$hidepriv', emailcheck='$emailchecknew', bbrules='$bbrulesnew', bbrulestxt='$bbrulestxtnew', todaysposts='$todayspostsnew', stats='$statsnew', searchstatus='$searchstatusnew', faqstatus='$faqstatusnew', memliststatus='$memliststatusnew', sitename='$sitenamenew', siteurl='$siteurlnew', avastatus='$avastatusnew', u2uquota='$u2uquotanew', gzipcompress='$gzipcompressnew', boardurl='$boardurlnew', coppa='$coppanew', timeformat='$timeformatnew', adminemail='$adminemailnew', dateformat='$dateformatnew', sigbbcode='$sigbbcodenew', sightml='$sightmlnew', reportpost='$reportpostnew', bbinsert='$bbinsertnew', smileyinsert='$smileyinsertnew', doublee='$doubleenew', smtotal='$smtotalnew', smcols='$smcolsnew', editedby='$editedbynew', dotfolders='$dotfoldersnew', attachimgpost='$attachimgpostnew', tickerstatus='$tickerstatusnew', tickercontents='$tickercontentsnew', tickerdelay='$tickerdelaynew', addtime='$addtimenew'");

		echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_textsettingsupdate</td></tr>";
	}
}



if($action == "forum") {
if(!$forumsubmit && !$fdetails) {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp.php?action=forum">
<table cellspacing="0" cellpadding="0" border="0" width="700" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header"><?=$lang_textforumopts?></td>
</tr>

<?

$queryf = $db->query("SELECT * FROM $table_forums WHERE type='forum' AND fup='' ORDER BY displayorder");
while($forum = $db->fetch_array($queryf)) {

if($forum[status] == "on") {
	$on = "selected=\"selected\"";
} else {
	$off = "selected=\"selected\"";
}

?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td class="smalltxt"><input type="checkbox" name="delete<?=$forum[fid]?>" value="<?=$forum[fid]?>" />
&nbsp;<input type="text" name="name<?=$forum[fid]?>" value="<?=stripslashes($forum[name])?>" />
&nbsp; <?=$lang_textorder?> <input type="text" name="displayorder<?=$forum[fid]?>" size="2" value="<?=$forum[displayorder]?>" />
&nbsp; <select name="status<?=$forum[fid]?>">
<option value="on" <?=$on?>><?=$lang_texton?></option><option value="off" <?=$off?>><?=$lang_textoff?></option></select>
&nbsp; <select name="moveto<?=$forum[fid]?>"><option value="" selected="selected">-<?=$lang_textnone?>-</option>
<?
$movequery = $db->query("SELECT * FROM $table_forums WHERE type='group' ORDER BY displayorder");
while($moveforum = $db->fetch_array($movequery)) {
echo "<option value=\"$moveforum[fid]\">".stripslashes($moveforum[name])."</option>";
}
?>
</select>
<a href="cp.php?action=forum&fdetails=<?=$forum[fid]?>"><?=$lang_textmoreopts?></a></td>
</tr>

<?
$querys = $db->query("SELECT * FROM $table_forums WHERE type='sub' AND fup='$forum[fid]' ORDER BY displayorder");
while($subforum = $db->fetch_array($querys)) {

if($subforum[status] == "on") {
	$on = "selected=\"selected\"";
} else {
	$off = "selected=\"selected\"";
}
?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td class="smalltxt"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="checkbox" name="delete<?=$subforum[fid]?>" value="<?=$subforum[fid]?>" />
&nbsp;<input type="text" name="name<?=$subforum[fid]?>" value="<?=stripslashes($subforum[name])?>" />
&nbsp; <?=$lang_textorder?> <input type="text" name="displayorder<?=$subforum[fid]?>" size="2" value="<?=$subforum[displayorder]?>" />
&nbsp; <select name="status<?=$subforum[fid]?>">
<option value="on" <?=$on?>><?=$lang_texton?></option><option value="off" <?=$off?>><?=$lang_textoff?></option></select>
&nbsp; <select name="moveto<?=$subforum[fid]?>">
<?
	$movequery = $db->query("SELECT * FROM $table_forums WHERE type='forum' ORDER BY displayorder");
	while($moveforum = $db->fetch_array($movequery)) {
		if($subforum['fup'] == $moveforum['fid']) {
			echo '<option value="'.$moveforum['fid'].'" selected="selected">'.stripslashes($moveforum['name']).'</option>';
		} else {
			echo '<option value="'.$moveforum['fid'].'">'.stripslashes($moveforum['name']).'</option>';
		}
	}
?>
</select>
<a href="cp.php?action=forum&fdetails=<?=$subforum[fid]?>"><?=$lang_textmoreopts?></a></td>
</tr>

<?
$on = "";
$off = "";
}

$on = "";
$off = "";
}


$queryg = $db->query("SELECT * FROM $table_forums WHERE type='group' ORDER BY displayorder");
while($group = $db->fetch_array($queryg)) {

if($group[status] == "on") {
$on = "selected=\"selected\"";
} else {
$off = "selected=\"selected\"";
}
?>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td class="smalltxt"><input type="checkbox" name="delete<?=$group[fid]?>" value="<?=$group[fid]?>" />
 <input type="text" name="name<?=$group[fid]?>" value="<?=stripslashes($group[name])?>" />
&nbsp; <?=$lang_textorder?> <input type="text" name="displayorder<?=$group[fid]?>" size="2" value="<?=$group[displayorder]?>" />
&nbsp; <select name="status<?=$group[fid]?>">
<option value="on" <?=$on?>><?=$lang_texton?></option><option value="off" <?=$off?>><?=$lang_textoff?></option></select>
</td>
</tr>

<?
$queryf = $db->query("SELECT * FROM $table_forums WHERE type='forum' AND fup='$group[fid]' ORDER BY displayorder");
while($forum = $db->fetch_array($queryf)) {

if($forum[status] == "on") {
$on = "selected=\"selected\"";
} else {
$off = "selected=\"selected\"";
}
?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td class="smalltxt"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="checkbox" name="delete<?=$forum[fid]?>" value="<?=$forum[fid]?>" />
&nbsp;<input type="text" name="name<?=$forum[fid]?>" value="<?=stripslashes($forum[name])?>" />
&nbsp; <?=$lang_textorder?> <input type="text" name="displayorder<?=$forum[fid]?>" size="2" value="<?=$forum[displayorder]?>" />
&nbsp; <select name="status<?=$forum[fid]?>">
<option value="on" <?=$on?>><?=$lang_texton?></option><option value="off" <?=$off?>><?=$lang_textoff?></option></select>
&nbsp; <select name="moveto<?=$forum[fid]?>"><option value="">-<?=$lang_textnone?>-</option>
<?
$movequery = $db->query("SELECT * FROM $table_forums WHERE type='group' ORDER BY displayorder");
while($moveforum = $db->fetch_array($movequery)) {
if($moveforum[fid] == $forum[fup]) {
$curgroup = "selected=\"selected\"";
} else {
$curgroup = "";
}
echo "<option value=\"$moveforum[fid]\" $curgroup>".stripslashes($moveforum[name])."</option>";
}
?>
</select>
<a href="cp.php?action=forum&fdetails=<?=$forum[fid]?>"><?=$lang_textmoreopts?></a></td>
</tr>

<?
$querys = $db->query("SELECT * FROM $table_forums WHERE type='sub' AND fup='$forum[fid]' ORDER BY displayorder");
while($forum = $db->fetch_array($querys)) {

if($forum[status] == "on") {
$on = "selected=\"selected\"";
} else {
$off = "selected=\"selected\"";
}
?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td class="smalltxt"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<input type="checkbox" name="delete<?=$forum[fid]?>" value="<?=$forum[fid]?>" />
&nbsp;<input type="text" name="name<?=$forum[fid]?>" value="<?=stripslashes($forum[name])?>" />
&nbsp; <?=$lang_textorder?> <input type="text" name="displayorder<?=$forum[fid]?>" size="2" value="<?=$forum[displayorder]?>" />
&nbsp; <select name="status<?=$forum[fid]?>">
<option value="on" <?=$on?>><?=$lang_texton?></option><option value="off" <?=$off?>><?=$lang_textoff?></option></select>
&nbsp; <select name="moveto<?=$forum[fid]?>">
<?
$movequery = $db->query("SELECT * FROM $table_forums WHERE type='forum' ORDER BY displayorder");
while($moveforum = $db->fetch_array($movequery)) {
if($moveforum[fid] == $forum[fup]) {
echo "<option value=\"$moveforum[fid]\" selected=\"selected\">".stripslashes($moveforum[name])."</option>";
} else {
echo "<option value=\"$moveforum[fid]\">".stripslashes($moveforum[name])."</option>";
}
}
?>
</select>
<a href="cp.php?action=forum&fdetails=<?=$forum[fid]?>"><?=$lang_textmoreopts?></a></td>
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
<td class="smalltxt"><input type="text" name="newgname" value="<?=$lang_textnewgroup?>" />
&nbsp; <?=$lang_textorder?> <input type="text" name="newgorder" size="2" />
&nbsp; <select name="newgstatus">
<option value="on"><?=$lang_texton?></option><option value="off"><?=$lang_textoff?></option></select></td>
</tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td class="smalltxt"><input type="text" name="newfname" value="<?=$lang_textnewforum?>" />
&nbsp; <?=$lang_textorder?> <input type="text" name="newforder" size="2" />
&nbsp; <select name="newfstatus">
<option value="on"><?=$lang_texton?></option><option value="off"><?=$lang_textoff?></option></select>
&nbsp; <select name="newffup"><option value="" selected="selected">-<?=$lang_textnone?>-</option>
<?
$gquery = $db->query("SELECT * FROM $table_forums WHERE type='group' ORDER BY displayorder");
while($group = $db->fetch_array($gquery)) {
echo "<option value=\"$group[fid]\">".stripslashes($group[name])."</option>";
}
?>
</select>
</td></tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td class="smalltxt"><input type="text" name="newsubname" value="<?=$lang_textnewsubf?>" />
&nbsp; <?=$lang_textorder?> <input type="text" name="newsuborder" size="2" />
&nbsp; <select name="newsubstatus"><option value="on"><?=$lang_texton?></option><option value="off"><?=$lang_textoff?></option></select>
&nbsp; <select name="newsubfup">
<?
$fquery = $db->query("SELECT * FROM $table_forums WHERE type='forum' ORDER BY displayorder");
while($group = $db->fetch_array($fquery)) {
echo "<option value=\"$group[fid]\">".stripslashes($group[name])."</option>";
}
?>
</select>
</td></tr>

</table>
</td></tr></table><br />
<center><input type="submit" name="forumsubmit" value="<?=$lang_textsubmitchanges?>" /></center>
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
<table cellspacing="0" cellpadding="0" border="0" width="100%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header" colspan="2"><?=$lang_textforumopts?></td>
</tr>

<?
$queryg = $db->query("SELECT * FROM $table_forums WHERE fid='$fdetails'");
$forum = $db->fetch_array($queryg);

$themelist = "<select name=\"themeforumnew\"><option value=\"\">$lang_textusedefault</option>\n";
$querytheme = $db->query("SELECT name FROM $table_themes");
while($theme = $db->fetch_array($querytheme)) {
if($theme[name] == $forum[theme]) {
$themelist .= "<option value=\"$theme[name]\" selected>$theme[name]\n";
}
else {
$themelist .= "<option value=\"$theme[name]\">$theme[name]\n";
}
}
$themelist  .= "</select>";


if($forum[private] == "staff") {
$checked1 = "checked";
} else {
$checked1 = "";
}

if($forum[allowhtml] == "on") {
$checked2 = "checked";
} else {
$checked2 = "";
}

if($forum[allowsmilies] == "on") {
$checked3 = "checked";
} else {
$checked3 = "";
}

if($forum[allowbbcode] == "on") {
$checked4 = "checked";
} else {
$checked4 = "";
}

if($forum[allowimgcode] == "on") {
$checked5 = "checked";
} else {
$checked5 = "";
}

if($forum[attachstatus] == "on") {
$checked6 = "checked";
} else {
$checked6 = "";
}

if($forum[pollstatus] == "on") {
$checked7 = "checked";
} else {
$checked7 = "";
}
if($forum[guestposting] == "on") {
$checked8 = "checked";
} else {
$checked8 = "";
}
$pperm = explode("|", $forum[postperm]);

if($pperm[0] == "2") {
$type12 = "selected";
} elseif($pperm[0] == "3") {
$type13 = "selected";
} elseif($pperm[0] == "4") {
$type14 = "selected";
} elseif($pperm[0] == "1") {
$type11 = "selected";
}

if($pperm[1] == "2") {
	$type22 = "selected";
} elseif($pperm[1] == "3") {
	$type23 = "selected";
} elseif($pperm[1] == "4") {
	$type24 = "selected";
} elseif($pperm[1] == "1") {
	$type21 = "selected";
}

if($forum[private] == "2") {
$type32 = "selected";
} elseif($forum[private] == "3") {
$type33 = "selected";
} elseif($forum[private] == "4") {
$type34 = "selected";
} elseif($forum[private] == "1") {
$type31 = "selected";
}


$forum[private] = str_replace("pw|", "", $forum[private]);
?>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang_textforumname?></td>
<td><input type="text" name="namenew" value="<?=$forum[name]?>" /></td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang_textdesc?></td>
<td><textarea rows="4" cols="30" name="descnew"><?=$forum[description]?></textarea></td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow" valign="top"><?=$lang_textallow?></td>


<td class="smalltxt"><input type="checkbox" name="allowhtmlnew" value="on" <?=$checked2?> /><?=$lang_texthtml?><br />
<input type="checkbox" name="allowsmiliesnew" value="on" <?=$checked3?> /><?=$lang_textsmilies?><br />
<input type="checkbox" name="allowbbcodenew" value="on" <?=$checked4?> /><?=$lang_textbbcode?><br />
<input type="checkbox" name="allowimgcodenew" value="on" <?=$checked5?> /><?=$lang_textimgcode?><br />
<input type="checkbox" name="attachstatusnew" value="on" <?=$checked6?> /><?=$lang_attachments?><br />
<input type="checkbox" name="pollstatusnew" value="on" <?=$checked7?> /><?=$lang_polls?><br />
<input type="checkbox" name="guestpostingnew" value="on" <?=$checked8?> /><?=$lang_textanonymousposting?><br />



</td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang_texttheme?></td>
<td><?=$themelist?></td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang_whopostop1?></td>
<td class="tablerow"><select name="postperm1">
<option value="1" <?=$type11?>><?=$lang_textpermission1?>
<option value="2" <?=$type12?>><?=$lang_textpermission2?>
<option value="3" <?=$type13?>><?=$lang_textpermission3?>
<option value="4" <?=$type14?>><?=$lang_textpermission41?>
</select>
</td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang_whopostop2?></td>
<td class="tablerow"><select name="postperm2">
<option value="1" <?=$type21?>><?=$lang_textpermission1?>
<option value="2" <?=$type22?>><?=$lang_textpermission2?>
<option value="3" <?=$type23?>><?=$lang_textpermission3?>
<option value="4" <?=$type24?>><?=$lang_textpermission41?>
</select>
</td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang_whoview?></td>
<td class="tablerow"><select name="privatenew">
<option value="1" <?=$type31?>><?=$lang_textpermission1?>
<option value="2" <?=$type32?>><?=$lang_textpermission2?>
<option value="3" <?=$type33?>><?=$lang_textpermission3?>
<option value="4" <?=$type34?>><?=$lang_textpermission42?>
</select>
</td>
</tr>

<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang_textuserlist?></td>
<td class="tablerow"><textarea rows="4" cols="30" name="userlistnew"><?=$forum[userlist]?></textarea></td>
</tr>
<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang_forumpw?></td>
<td><input type="text" name="passwordnew" value="<?=$forum[password]?>"></td>
</tr>
<tr bgcolor="<?=$altbg2?>">
<td class="tablerow"><?=$lang_textdeleteques?></td>
<td><input type="checkbox" name="delete" value="<?=$forum[fid]?>" /></td>
</tr>

</table>
</td></tr></table><br />
<center><input type="submit" name="forumsubmit" value="<?=$lang_textsubmitchanges?>" /></center>
</form>

</td>
</tr>
<?
}

if($forumsubmit) {
if(!$fdetails) {
	$queryforum = $db->query("SELECT fid, type FROM $table_forums WHERE type='forum' OR type='sub'");
	while($forum = $db->fetch_array($queryforum)) {
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
			$db->query("DELETE FROM $table_forums WHERE (type='forum' OR type='sub') AND fid='$delete'");

			$querythread = $db->query("SELECT * FROM $table_threads WHERE fid='$delete'");
			while($thread = $db->fetch_array($querythread)) {
				$db->query("DELETE FROM $table_threads WHERE tid='$thread[tid]'");
				$db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$thread[author]'");

				$querypost = $db->query("SELECT * FROM $table_posts WHERE tid='$thread[tid]'");
				while($post = $db->fetch_array($querypost)) {
					$db->query("DELETE FROM $table_posts WHERE pid='$post[pid]'");
					$db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$post[author]'");
				}
			}
		}
		$name = addslashes($name);
		$db->query("UPDATE $table_forums SET name='$name', displayorder='$displayorder', status='$status', fup='$moveto' WHERE fid='$forum[fid]'");
	}


	$querygroup = $db->query("SELECT fid FROM $table_forums WHERE type='group'");
	while($group = $db->fetch_array($querygroup)) {
		$name = "name$group[fid]";
		$name = "${$name}";
		$displayorder = "displayorder$group[fid]";
		$displayorder = "${$displayorder}";
		$status = "status$group[fid]";
		$status = "${$status}";
		$delete = "delete$group[fid]";
		$delete = "${$delete}";

		if($delete != "") {
			$query = $db->query("SELECT fid FROM $table_forums WHERE type='forum' AND fup='$delete'");
			while($forum = $db->fetch_array($query)) {
				$db->query("UPDATE $table_forums SET fup='' WHERE type='forum' AND fup='$delete'");
			}

			$db->query("DELETE FROM $table_forums WHERE type='group' AND fid='$delete'");
		}
		$name = addslashes($name);
		$db->query("UPDATE $table_forums SET name='$name', displayorder='$displayorder', status='$status' WHERE fid='$group[fid]'");
	}

	if($newfname != $lang_textnewforum) {
		$newfname = addslashes($newfname);
		$db->query("INSERT INTO $table_forums VALUES ('forum', '', '$newfname', '$newfstatus', '', '', '$newforder', '1', '', 'no', 'yes', 'yes', '', '', '0', '0', '$newffup', '1', 'yes', 'on', 'on', '', 'off')");
	}

	if($newgname != $lang_textnewgroup) {
		$newgname = addslashes($newgname);
		$db->query("INSERT INTO $table_forums VALUES ('group', '', '$newgname', '$newgstatus', '', '', '$newgorder', '', '', '', '', '', '', '', '0', '0', '', '', '', '', '', '', 'off')");
	}

	if($newsubname != $lang_textnewsubf) {
		$newsubname = addslashes($newsubname);
		$db->query("INSERT INTO $table_forums VALUES ('sub', '', '$newsubname', '$newsubstatus', '', '', '$newsuborder', '1', '', 'no', 'yes', 'yes', '', '', '0', '0', '$newsubfup', '1', 'yes', 'on', 'on', '', 'off')");
	}

	echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_textforumupdate</td></tr>";
}else {
	if($attachstatusnew != "on") {
		$attachstatusnew = "off";
	}

	if($pollstatusnew != "on") {
		$pollstatusnew = "off";
	}

// Patch to safeguard smilies/bbcodes and others

	$check_vars = array('allowhtmlnew', 'allowsmiliesnew', 'allowbbcodenew', 'allowimgcodenew', 'attachstatusnew', 'pollstatusnew', 'guestpostingnew'); 
	foreach($check_vars as $key){ 
		if($$key != 'on' && $key != 'yes'){ 
			$$key = 'off'; 
		} 
	}

// End Patch

	$namenew = addslashes($namenew);
	$descnew = addslashes($descnew);

	$db->query("UPDATE $table_forums SET name='$namenew', description='$descnew', allowhtml='$allowhtmlnew', allowsmilies='$allowsmiliesnew', allowbbcode='$allowbbcodenew', theme='$themeforumnew', userlist='$userlistnew', private='$privatenew', postperm='$postperm1|$postperm2', allowimgcode='$allowimgcodenew', attachstatus='$attachstatusnew', pollstatus='$pollstatusnew', password='$passwordnew', guestposting='$guestpostingnew' WHERE fid='$fdetails'");
	if($delete != "") {
		$db->query("DELETE FROM $table_forums WHERE fid='$delete'");
	}

	echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_textforumupdate</td></tr>";
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
		<table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
		<tr><td bgcolor="<?=$bordercolor?>">
		
		<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
		
		<tr class="header">
		<td><?=$lang_textforum?></td>
		<td><?=$lang_textmoderator?></td>
		</tr>
		
		<?
		$queryf = $db->query("SELECT name, moderator, fid FROM $table_forums WHERE type !='group'");
		while($mod = $db->fetch_array($queryf)) {
			if($mod[type] == "forum"){
				?>
		
				<tr bgcolor="<?=$altbg2?>" class="tablerow">
				<td><?=$mod[name]?></td>
				<td><input type="text" name="mod<?=$mod[fid]?>" value="<?=$mod[moderator]?>" /></td>
				</tr>
				
				<?
			}else{
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
		</td></tr></table><br />
		<span class="smalltxt"><?=$lang_multmodnote?></span><br /><br />
		<center><input type="submit" name="modsubmit" value="<?=$lang_textsubmitchanges?>" /></center>
		</form>
		
		</td>
		</tr>
		
		<?
	}elseif($modsubmit) {
		$queryforum = $db->query("SELECT fid FROM $table_forums");
	
		while($forum = $db->fetch_array($queryforum)) {
			$mod = "mod$forum[fid]";
			$mod = "${$mod}";
			$db->query("UPDATE $table_forums SET moderator='$mod' WHERE fid='$forum[fid]'");
			
			
			$modz = explode(", ", $mod);
			for($num = 0; $num < count($modz); $num++) {
	
				if($modz[$num] != "") {
					$query = $db->query("SELECT status FROM $table_members WHERE username='$modz[$num]'");
					$userinfo = $db->fetch_array($query);
	
					if($userinfo[status] != "Administrator" && $userinfo[status] != "Super Moderator" && $userinfo[status] != "Super Administrator") {
						$db->query("UPDATE $table_members SET status='Moderator' WHERE username='$modz[$num]'");
					}else{
						echo "";
					}
				}
			}
		}
	
		echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_textmodupdate</td></tr>";
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
<span class="mediumtxt"><?=$lang_textsrchusr?></span> <input type="text" name="srchmem">&nbsp;&nbsp;<span class="mediumtxt"><?=$lang_textwithstatus?></span>

<select name="srchstatus">
<option value="0"><?=$lang_anystatus?></option>
<option value="Administrator"><?=$lang_textadmin?></option>
<option value="Super Administrator"><?=$lang_superadmin?></option>
<option value="Super Moderator"><?=$lang_textsupermod?></option>
<option value="Moderator"><?=$lang_textmod?></option>
<option value="Member"><?=$lang_textmem?></option>
<option value="Banned"><?=$lang_textbanned?></option>
</select><br /><br />
<input type="submit" value="<?=$lang_textgo?>" />
</form>
</td></tr>

<?
}

if($members == "search") {
?>
<form method="post" action="cp.php?action=members">
<table cellspacing="0" cellpadding="0" border="0" width="91%" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">


<tr class="header">
<td><?=$lang_textdeleteques?></td>
<td><?=$lang_textusername?></td>
<td><?=$lang_textnewpassword?></td>
<td><?=$lang_textposts?></td>
<td><?=$lang_textstatus?></td>
<td><?=$lang_textcusstatus?></td>
<td><?=$lang_textbanfrom?></td>
</tr>

<?
if($srchstatus == "0") {
$query = $db->query("SELECT * FROM $table_members WHERE username LIKE '%$srchmem%' ORDER BY username");
} else {
$query = $db->query("SELECT * FROM $table_members WHERE username LIKE '%$srchmem%' AND status='$srchstatus' ORDER BY username");
}
// Patch for improved protection from making accidental Admin

			while($member = $db->fetch_array($query)) { 
				switch($member['status']) { 
					case 'Super Administrator'; 
						$sadminselect = 'selected="selected"'; 
						break; 
					 
					case 'Administrator': 
						$adminselect = 'selected="selected"'; 
						break; 
					 
					case 'Super Moderator': 
						$smodselect = 'selected="selected"'; 
						break; 
						 
					case 'Moderator': 
						$modselect = 'selected="selected"'; 
						break; 
						 
					case 'Member': 
						$memselect = 'selected="selected"'; 
						break; 
					 
					case 'Banned': 
						$banselect = 'selected="selected"'; 
						break; 
					 
					default: 
						$memselect = 'selected="selected"'; 
						break; 
				} 
		 
				switch($member['ban']){ 
					case 'u2u': 
						$u2uban = 'selected="selected"'; 
						break; 
					 
					case 'posts': 
						$postban = 'selected="selected"'; 
						break; 
					 
					case 'both': 
						$bothban = 'selected="selected"'; 
						break; 
					 
					default: 
						$noban = 'selected="selected"'; 
						break; 
				}


// End Patch


?>

<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td><input type="checkbox" name="delete<?=$member[uid]?>" value="<?=$member[uid]?>" /></td>
<td><a href="member.php?action=viewpro&member=<?=$member[username]?>"><?=$member[username]?>
<br /><a href="cp.php?action=deleteposts&member=<?=$member[username]?>"><b><?=$lang_cp_deleteposts?></b></a>
<br /><a href="u2uadmin.php?uid=<?=$member[username]?>"><?=$lang_cp_viewinbox?></a></td>
<td><input type="text" size="12" name="pw<?=$member[uid]?>"></td>
<td><input type="text" size="3" name="postnum<?=$member[uid]?>" value="<?=$member[postnum]?>"></td>
<td><select name="status<?=$member[uid]?>">
<option value="Administrator" <?=$adminselect?>><?=$lang_textadmin?></option>
<option value="Super Administrator" <?=$sadminselect?>><?=$lang_superadmin?></option>
<option value="Super Moderator" <?=$smodselect?>><?=$lang_textsupermod?></option>
<option value="Moderator" <?=$modselect?>><?=$lang_textmod?></option>
<option value="Member" <?=$memselect?>><?=$lang_textmem?></option>
<option value="Banned" <?=$banselect?>><?=$lang_textbanned?></option>
</select></td>
<td><input type="text" size="16" name="cusstatus<?=$member[uid]?>" value="<?=stripslashes($member[customstatus])?>" /></td>
<td><select name="banstatus<?=$member[uid]?>">
<option value="" <?=$noban?>><?=$lang_noban?></option>
<option value="u2u" <?=$u2uban?>><?=$lang_banu2u?></option>
<option value="posts" <?=$postban?>><?=$lang_banpost?></option>
<option value="both" <?=$bothban?>><?=$lang_banboth?></option>
</select></td>
</tr>

<?
$adminselect = "";
$sadminselect = "";
$smodselect = "";
$modselect = "";
$memselect = "";
$banselect = "";
$noban = "";
$u2uban = "";
$postban = "";
$bothban = "";
}
?>

</table>
</td></tr></table><br />
<center><input type="submit" name="membersubmit" value="<?=$lang_textsubmitchanges?>" /></center>
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
	$query = $db->query("SELECT uid, username, password, status FROM $table_members WHERE username LIKE '%$srchmem%'");
} else {
	$query = $db->query("SELECT uid, username, password, status FROM $table_members WHERE username LIKE '%$srchmem%' AND status='$srchstatus'");
}

while($mem = $db->fetch_array($query)) {
	$status =  "status$mem[uid]";
	$status = "status$mem[uid]";
	$status = "${$status}";
	
	$origstatus = '';
	$origstatus = $mem['status'];
	
	$banstatus = "banstatus$mem[uid]";
	$banstatus = "${$banstatus}";
	
	$cusstatus = "cusstatus$mem[uid]";
	$cusstatus = "${$cusstatus}";
	
	$pw = "pw$mem[uid]";
	$pw = "${$pw}";
	
	$postnum = "postnum$mem[uid]";
	$postnum = "${$postnum}";
	
	$delete = "delete$mem[uid]";
	$delete = "${$delete}";
	
	if($pw != "") {
		$newpw = md5($pw);
		$queryadd = " , password='$newpw'";
	} else {
		$newpw = $mem[password];
		$queryadd = " , password='$newpw'";
	}

	if($delete != "") {
		$db->query("DELETE FROM $table_members WHERE uid='$delete'");
	}else {
		if(ereg('"', $pw) || ereg("'", $pw)) {
			$lang_textmembersupdate = "$mem[username]: $lang_textpwincorrect";
		} else {
			$newcustom = addslashes($cusstatus);
			
			if($self['status'] != "Super Administrator" && ($origstatus == "Super Administrator" || $status == "Super Administrator")){
				echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_textmembersupdatefailed</td></tr>";
				end_time();
				eval("\$footer = \"".template("footer")."\";");
				echo $footer;
				exit();
			}
// Patch to prevent accidental member to Admin vulnerability
			if ($status != ""){
				$db->query("UPDATE $table_members SET ban='$banstatus', status='$status', postnum='$postnum', customstatus='$newcustom'$queryadd WHERE uid='$mem[uid]'");
			}

			$newpw="";
		}
	}
}


echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_textmembersupdate</td></tr>";
}
}

if($action == "ipban") {
	if(!$ipbansubmit) {
		?>

		<tr bgcolor="<?=$altbg2?>">
		<td align="center">
		<br />
		<form method="post" action="cp.php?action=ipban">
		<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
		<tr><td bgcolor="<?=$bordercolor?>">
		
		<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
		<tr>
		<td class="header"><?=$lang_textdeleteques?></td>
		<td class="header"><?=$lang_textip?>:</td>
		<td class="header"><?=$lang_textipresolve?>:</td>
		<td class="header"><?=$lang_textadded?></td>
		</tr>
		
		<?
		$query = $db->query("SELECT * FROM $table_banned ORDER BY dateline");
		while($ipaddress = $db->fetch_array($query)) {
	
			for($i=1; $i<=4; ++$i) {
				$j = "ip" . $i;
				if($ipaddress[$j] == -1){
					$ipaddress[$j] = "*";
				}
			}
	
			$ipdate = gmdate("n/j/y", $ipaddress[dateline] + ($timeoffset * 3600) + ($addtime * 3600)) . " $lang_textat " . gmdate("$timecode", $ipaddress[dateline] + ($timeoffset * 3600) + ($addtime * 3600));
			$theip = "$ipaddress[ip1].$ipaddress[ip2].$ipaddress[ip3].$ipaddress[ip4]";
			?>
		
			<tr bgcolor="<?=$altbg2?>">
			<td class="tablerow"><input type="checkbox" name="delete<?=$ipaddress[id]?>" value="<?=$ipaddress[id]?>" /></td>
			<td class="tablerow"><?=$theip?></td>
			<td class="tablerow"><?=@gethostbyaddr($theip)?></td>
			<td class="tablerow"><?=$ipdate?></td>
			</tr>
	
			<?
		}
	
		$query = $db->query("SELECT id FROM $table_banned WHERE (ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')");
		$result = $db->fetch_array($query);
		if($result){
			$warning = $lang_ipwarning;
		}
	
		?>
		<tr bgcolor="<?=$altbg2?>"><td colspan="4"> </td></tr>
		<tr bgcolor="<?=$altbg1?>">
		<td colspan="4" class="tablerow"><?=$lang_textnewip?>
		<input type="text" name="newip1" size="3" maxlength="3" />.<input type="text" name="newip2" size="3" maxlength="3" />.<input type="text" name="newip3" size="3" maxlength="3" />.<input type="text" name="newip4" size="3" maxlength="3" /></td>
		</tr>
		
		</table>
		</td></tr></table><br />
		<span class="smalltxt"><?=$lang_currentip?> <b><?=$onlineip?></b><?=$warning?><br /><?=$lang_multipnote?></span><br />
		<br /><center><input type="submit" name="ipbansubmit" value="<?=$lang_textsubmitchanges?>" /></center>
		</form>

		</td>
		</tr>

		<?
	}else{
		$queryip = $db->query("SELECT id FROM $table_banned");
		$newid = 1;
		while($ip = $db->fetch_array($queryip)) {
			$delete = "delete$ip[id]";
			$delete = "${$delete}";

			if($delete != "") {
				$query = $db->query("DELETE FROM $table_banned WHERE id='$delete'");
			}elseif($ip[id] > $newid) {
				$query = $db->query("UPDATE $table_banned SET id='$newid' WHERE id='$ip[id]'");
			}
			++$newid;
		}

		$status = $lang_textipupdate;
		
		if($newip1 != "" || $newip2 != "" || $newip3 != "" || $newip4 != "") {
			$invalid = 0;

			for($i=1;$i<=4 && !$invalid;++$i) {
				$newip = "newip$i";
				$newip = "${$newip}";
				$newip = trim($newip);
				
				if($newip == "*"){
					$ip[$i] = -1;
				}elseif(ereg("^[0-9]+$", $newip)){
					$ip[$i] = $newip;
				}else{
					$invalid = 1;
				}
			}

			if($invalid){
				$status = $lang_invalidip;
			}else{
				if($ip[1] == '-1' && $ip[2] == '-1' && $ip[3] == '-1' && $ip[4] == '-1'){
					$status = $lang_impossiblebanall;
				}else{
					$query = $db->query("SELECT id FROM $table_banned WHERE (ip1='$ip[1]' OR ip1='-1') AND (ip2='$ip[2]' OR ip2='-1') AND (ip3='$ip[3]' OR ip3='-1') AND (ip4='$ip[4]' OR ip4='-1')");
					$result = $db->fetch_array($query);
					if ($result){
						$status = $lang_existingip;
					}else{
						$query = $db->query("INSERT INTO $table_banned VALUES ('$ip[1]', '$ip[2]', '$ip[3]', '$ip[4]', '$onlinetime', '$newid')");
					}
				}
			}
		}

		echo "<tr bgcolor=\"$altbg2\"><td align=\"center\" class=\"tablerow\">$status</td></tr>";
	}
}

if($action == "deleteposts"){
$queryd = $db->query("DELETE FROM $table_posts WHERE author='$member'");
$queryt = $db->query("SELECT * FROM $table_threads");
while($threads = $db->fetch_array($queryt)) {

$query = $db->query("SELECT COUNT(*) FROM $table_posts WHERE tid='$threads[tid]'");
$replynum = $db->result($query, 0);

$replynum--;
$db->query("UPDATE $table_threads SET replies=replies-1 WHERE tid='$threads[tid]'");
$db->query("DELETE FROM $table_threads WHERE author='$member'");
}
}

if($action == "upgrade") {
	if($status != "Super Administrator"){
		echo('This function can only be used by Super-Administrators');
		end_time();
		eval("\$footer = \"".template("footer")."\";");
		echo $footer;
		exit();
	}
if($upgradesubmit) {

$explode = explode(";", $upgrade);
$count = sizeof($explode);

for($num=0;$num<$count;$num++) {
$explode[$num]=stripslashes($explode[$num]);
if($explode[$num] != "") {
$db->query("$explode[$num]");
}
}
echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_upgradesuccess </td></tr>";
}
if(!$upgradesubmit) {

?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp.php?action=upgrade">
<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td colspan=2><?=$lang_textupgrade?></td>
</tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td valign="top"><?=$lang_upgrade?><br /><textarea cols="85" rows="10" name="upgrade"></textarea><br />
<?=$lang_upgradenote?><br />
<center><input type="submit" name="upgradesubmit" value="<?=$lang_textsubmitchanges?>" /></center>
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

if($action == "search"){
	if($searchsubmit){
		$found = 0;
		$list = NULL;
		if($userip && !empty($userip)){
			$query = $db->query("SELECT * FROM $table_members WHERE regip = '$userip'");
			while($users = $db->fetch_array($query)){
				$link = "./member.php?action=viewpro&member=$users[username]";
				$list .= "<a href = \"$link\">$users[username]<br />";
				$found++;
			}
		}

		if($postip && !empty($postip)){
			$query = $db->query("SELECT * FROM $table_posts WHERE useip = '$postip'");
			while($users = $db->fetch_array($query)){
				$link = "./viewthread.php?tid=$users[tid]#pid$users[pid]";
				if(!empty($users[subject])){
					$list .= "<a href = \"$link\">$users[subject]<br />";
				}else{
					$list .= "<a href = \"$link\">- - No subject - -<br />";
				}
				$found++;
			}
		}

		if($profileword && !empty($profileword)){
			$query = $db->query("SELECT * FROM $table_members WHERE bio = '%$profileword%'");
			while($users = $db->fetch_array($query)){
				$link = "./member.php?action=viewpro&member=$users[username]";
				$list .= "<a href = \"$link\">$users[username]<br />";
				$found++;
			}
		}

		if($postword && !empty($postword)){
			$query = $db->query("SELECT * FROM $table_posts WHERE subject LIKE '%".$postword."%' OR message LIKE '%".$postword."%'");
			while($users = $db->fetch_array($query)){
				$link = "./viewthread.php?tid=$users[tid]#pid$users[pid]";
				if(!empty($users[subject])){
					$list .= "<a href = \"$link\">$users[subject]<br />";
				}else{
					$list .= "<a href = \"$link\">- - No subject - -<br />";
				}
				$found++;
			}
		}


		echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$found $lang_beenfound<br />$list</td></tr>";
	}else{
		?>
		<tr bgcolor="<?=$altbg2?>">
		<td align="center">
		<br />
		<form method="post" action="cp.php?action=search">
		<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
		<tr><td bgcolor="<?=$bordercolor?>">
		
		<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
		
		<tr class="header">
		<td colspan=2><?=$lang_insertdata?>:</td>
		</tr>
		
		<tr bgcolor="<?=$altbg1?>" class="tablerow">
		<td valign="top"><center><br />
		<?=$lang_userip?><br /><input type="text" name="userip"></input><br /><br />
		<?=$lang_postip?><br /><input type="text" name="postip"></input><br /><br />
		      <?=$lang_profileword?><br /><input type="text" name="profileword"></input><br /><br />
		<?=$lang_postword?><br />
		<?php
		$query = $db->query("SELECT find FROM $table_words");
		$select = "<select name=\"postword\"><option value=\"\"></option>";
		while($temp = $db->fetch_array($query)){
			$select .= "<option value=\"$temp[find]\">$temp[find]</option>";
		}
		$select .= "</select>";
		echo $select;
		?>
		
		<br /><br />
		<center><br /><input type="submit" name="searchsubmit" value="Search now" /><br /><br /></center>
		</td>
		</tr>
		</table>
		</td></tr></table>
		</form>
		
		</td>
		</tr>
	<?php
	}
}

echo "</table></td></tr></table>";

end_time();

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>