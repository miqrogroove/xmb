<?
/*

XMB 1.6 v2c Magic Lantern
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/
require "./header.php";

if(!$xmbuser || !$xmbpw) {
	$xmbuser = "";
	$xmbpw = "";
	$status = "";
}

// Start Download Templates and Theme Code
if($status == "Administrator") {
	if($action == "templates" && $download) {
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
	if($action == "themes" && $download) {
		$query = $db->query("SELECT * FROM $table_themes WHERE name='$download'");
		$themebits = $db->fetch_array($query);
		while(list($key,$val) = each($themebits)) {
			if(!is_integer($key)) {
				$contents .= "$key=$val\r\n";
			}
		}
		header("Content-Type: application/x-ms-download");
		header("Content-Disposition: filename=$download-theme.xmb");
		echo $contents;
		exit;
	}
}
// End Download Templates and Theme Code

$navigation = "&raquo; $lang_textcp";
eval("\$header = \"".template("header")."\";");
echo $header;

if($status != "Administrator") {
	echo "$lang_notadmin";
	exit;
}

$cploc = $HTTP_SERVER_VARS["REQUEST_URI"];
if(getenv(HTTP_CLIENT_IP)) {
$ip = getenv(HTTP_CLIENT_IP);
} elseif(getenv(HTTP_X_FORWARDED_FOR)) {
$ip = getenv(HTTP_X_FORWARDED_FOR);
} else {
$ip = getenv(REMOTE_ADDR);
}
$time = time();
$string = "$xmbuser|#||#|$ip|#||#|$time|#||#|$cploc\n";
$filehandle=fopen("./cplogfile.log","a");
flock($filehandle, 2);
fwrite($filehandle, $string);
fclose($filehandle);

?>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
	<tr>
		<td bgcolor="<?=$bordercolor?>">
			<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
				<tr class="header">
					<td colspan="2"><?=$lang_textcp?></td>
				</tr>
				<tr bgcolor="<?=$altbg1?>" class="tablerow">
					<td align="center">
					<a href="cp.php?action=settings"><?=$lang_textsettings?></a> - <a href="cp.php?action=forum"><?=$lang_textforums?></a> -
					<a href="cp.php?action=mods"><?=$lang_textmods?></a> - <a href="cp.php?action=members"><?=$lang_textmembers?></a> -
					<a href="cp2.php?action=restrictions"><?=$lang_cprestricted?></a> - <a href="cp.php?action=ipban"><?=$lang_textipban?></a> - <a href="cp.php?action=upgrade"><?=$lang_textupgrade?></a><br>
					<a href="cp2.php?action=themes"><?=$lang_themes?></a> - <a href="cp2.php?action=smilies"><?=$lang_smilies?></a> -
					<a href="cp2.php?action=censor"><?=$lang_textcensors?></a> - <a href="cp2.php?action=ranks"><?=$lang_textuserranks?></a> -
					<a href="cp2.php?action=newsletter"><?=$lang_textnewsletter?></a> - <a href="cp2.php?action=prune"><?=$lang_textprune?></a> -
					<a href="cp2.php?action=templates"><?=$lang_templates?></a> - <a href="cp2.php?action=attachments"><?=$lang_textattachman?></a><br>
					<a href="tools.php?action=fixttotals"><?=$lang_textfixthread?></a> - <a href="tools.php?action=fixftotals"><?=$lang_textfixmemposts?></a> - <a href="tools.php?action=fixmposts"><?=$lang_textfixposts?></a><br> 
					<a href="cp2.php?action=cplog"><?=$lang_cplog?></a> - <a href="rawlogs.php?view=index"><?=$lang_cplogs?></a><br/>

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

<?
if(!$action) {
}

if($action == "restrictions") { 
if(!$restrictedsubmit) { 
?> 

<tr bgcolor="<?=$altbg2?>"> 
<td align="center"> 
<br> 
<form method="post" action="cp2.php?action=restrictions"> 
<table align="center" border="0" cellspacing="0" cellpadding="0" width="450"> 
<tr> 
<td bgcolor="<?=$bordercolor?>"> 
<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%"> 
<tr class="header"> 
<td><?=$lang_textdeleteques?></td> 
<td><?=$lang_restrictedname?></td> 
</tr> 

<? 
$query = $db->query("SELECT * FROM $table_restricted ORDER BY id"); 
while($restricted = $db->fetch_array($query)) { 
?> 
<tr class="tablerow"> 
<td bgcolor="<?=$altbg1?>"><input type="checkbox" name="delete<?=$restricted[id]?>" value="<?=$restricted[id]?>"></td> 
<td bgcolor="<?=$altbg1?>"><input type="text" size="30" name="name<?=$restricted[id]?>" value="<?=$restricted[name]?>"></td> 
</tr> 
<? 
} 
?> 
<tr> 
<td bgcolor="<?=$altbg2?>" colspan="2"><img src="./images/pixel.gif"></td> 
</tr> 
<tr class="tablerow">
<td bgcolor="<?=$altbg1?>" colspan="2" align="center"><?=$lang_textnewcode?><br><?=$lang_newrestriction?><br><?=$lang_newrestrictionwhy?><br><br><input type="text" size="30" name="newname"></td> 
</tr> 
</table> 
</td> 
</tr> 
</table><br>
<center><input type="submit" name="restrictedsubmit" value="<?=$lang_textsubmitchanges?>"></center> 
</form> 
</td> 
</tr> 

<? 
} 

if($restrictedsubmit) { 
$queryrestricted = $db->query("SELECT id FROM $table_restricted"); 
while($restricted = $db->fetch_array($queryrestricted)) { 
$name = "name$restricted[id]"; 
$name = "${$name}"; 
$delete = "delete$restricted[id]"; 
$delete = "${$delete}"; 
if($delete != "") { 
$db->query("DELETE FROM $table_restricted WHERE id='$delete'"); 
} 
$db->query("UPDATE $table_restricted SET name='$name' WHERE id='$restricted[id]'"); 
} 
if($newname != "") { 
$db->query("INSERT INTO $table_restricted VALUES ('$newname', '')"); 
} 
echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_restrictedupdate</td></tr>"; 

?> 
<script> 
function redirect() { 
window.location.replace("cp2.php?action=restrictions"); 
} 
setTimeout("redirect();", 2500); 
</script> 
<? 

} 
}

if($action == "themes") {
if(!$themesubmit && !$single && !$importsubmit) {
?>

				<tr bgcolor="<?=$altbg2?>">
					<td align="center">
						<br />
						<form method="post" action="cp2.php?action=themes">
						<table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
							<tr>
								<td bgcolor="<?=$bordercolor?>">
									<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
										<tr class="header">
											<td><?=$lang_textdeleteques?></td>
											<td><?=$lang_textthemename?></td>
<td><?=$lang_numberusing?></td>
										</tr>

<?
$query = $db->query("SELECT name FROM $table_themes");
while($themeinfo = $db->fetch_array($query)) {
$memthemequery = $db->query("SELECT COUNT(theme) FROM $table_members where theme='$themeinfo[name]'");
$members = $db->result($memthemequery, 0);

if($themeinfo[name] == "$theme") {
$checked = "checked=\"checked\"";
}
?>

										<tr bgcolor="<?=$altbg2?>" class="tablerow">
											<td><input type="checkbox" name="delete<?=$themeinfo[name]?>" value="<?=$themeinfo[name]?>" /></td>
											<td><input type="text" name="name<?=$themeinfo[name]?>" value="<?=$themeinfo[name]?>" /> <a href="cp2.php?action=themes&single=<?=$themeinfo[name]?>"><?=$lang_textdetails?></a> - <a href="cp2.php?action=themes&download=<?=$themeinfo[name]?>"><?=$lang_textdltheme?></a></td>
<td><?=$members?></td>
										</tr>

<?
$checked = "";
}
?>

										<tr bgcolor="<?=$altbg2?>">
											<td colspan="3"><img src="./images/pixel.gif"></td>
										</tr>
										<tr bgcolor="<?=$altbg1?>" class="tablerow">
											<td colspan="3"><a href="cp2.php?action=themes&single=anewtheme1"><?=$lang_textnewtheme?></a></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
							<center><br><input type="submit" name="themesubmit" value="<?=$lang_textsubmitchanges?>" /></center></form><br>
							<form method="post" action="cp2.php?action=themes" enctype="multipart/form-data">
						<table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
							<tr>
								<td bgcolor="<?=$bordercolor?>">
									<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
										<tr class="header">
											<td colspan="2"><?=$lang_textimporttheme?></td>
										</tr>
										<tr class="tablerow">
											<td bgcolor="<?=$altbg1?>"><?=$lang_textthemefile?></td>
											<td bgcolor="<?=$altbg2?>"><input name="themefile" type="file"></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
							<center><br><input type="submit" name="importsubmit" value="<?=$lang_textimportsubmit?>" /></center></form>
					</td>
				</tr>

<?
}
if($importsubmit) {
	$themebits = readFileAsINI($themefile);
	$sql = "INSERT INTO $table_themes (";
		while(list($key,$val) = each($themebits)) {
			$sql .= "$key, ";
		}
	$sql = substr($sql,0,strlen($sql)-2);
	$sql .= ") VALUES (";
	reset($themebits);
		while (list($key,$val) = each($themebits)) {
			$sql .= "'$val', ";
		}
	$sql = substr($sql,0,strlen($sql)-2);
	$sql .= ")";
	echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">";
		if (!$db->query($sql)) {
			echo "$lang_textthemeimportfail";
		} else {
			echo "$lang_textthemeimportsuccess";
		}
	echo "</td></tr>";
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

		if($themes[name] == $theme && $name != $themes[name]) {
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
		<br /><form method="post" action="cp2.php?action=themes&single=submit">
			<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
				<tr>
					<td bgcolor="<?=$bordercolor?>">
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
								<td><?=$lang_textcattextcolor?></td>
								<td><input type="text" name="cattextnew" value="<?=$themestuff[cattext]?>" /></td>
								<td bgcolor="<?=$themestuff[cattext]?>">&nbsp;</td>
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
					</td>
				</tr>
			</table>
				<center><br><input type="submit" value="<?=$lang_textsubmitchanges?>" /></center><input type="hidden" name="orig" value="<?=$single?>" /></form>
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
			<tr>
				<td bgcolor="<?=$bordercolor?>">
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
							<td><?=$lang_textcattextcolor?></td>
							<td><input type="text" name="cattextnew" /></td>
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
				</td>
			</tr>
		</table>
			<center><br><input type="submit" value="<?=$lang_textsubmitchanges?>" /></center><input type="hidden" name="newtheme" value="<?=$single?>" /></form>
		</td>
	</tr>

<?
}


if($single == "submit" && !$newtheme) {
	$db->query("UPDATE $table_themes SET name='$namenew', bgcolor='$bgcolornew', altbg1='$altbg1new', altbg2='$altbg2new', link='$linknew', bordercolor='$bordercolornew', header='$headernew', headertext='$headertextnew', top='$topnew', catcolor='$catcolornew', tabletext='$tabletextnew', text='$textnew', borderwidth='$borderwidthnew', tablewidth='$tablewidthnew', tablespace='$tablespacenew', fontsize='$fsizenew', font='$fnew', boardimg='$boardlogonew', imgdir='$imgdirnew', smdir='$smdirnew', cattext='$cattextnew' WHERE name='$orig'");
	echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_themeupdate</td></tr>";
}

if($single == "submit" && $newtheme) {
$db->query("INSERT INTO $table_themes (name, bgcolor, altbg1, altbg2, link, bordercolor, header, headertext, top, catcolor, tabletext, text, borderwidth, tablewidth, tablespace, font, fontsize, boardimg, imgdir, smdir, cattext) VALUES('$namenew', '$bgcolornew', '$altbg1new', '$altbg2new', '$linknew', '$bordercolornew', '$headernew', '$headertextnew', '$topnew', '$catcolornew', '$tabletextnew', '$textnew', '$borderwidthnew', '$tablewidthnew', '$tablespacenew', '$fnew', '$fsizenew', '$boardlogonew', '$imgdirnew', '$smdirnew', '$cattextnew')");

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_themeupdate</td></tr>";
}
}



if($action == "smilies") {
	if(!$smiliesubmit) {
?>

	<tr bgcolor="<?=$altbg2?>">
		<td align="center">
		<br /><form method="post" action="cp2.php?action=smilies">
			<table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
				<tr>
					<td bgcolor="<?=$bordercolor?>">
						<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
							<tr class="header">
								<td colspan="4" align="left"><?=$lang_smilies?></td>
							</tr>
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
							<tr>
								<td bgcolor="<?=$altbg2?>" colspan="4"><img src="./images/pixel.gif"></td>
							</tr>
							<tr bgcolor="<?=$altbg1?>" class="tablerow">
								<td><?=$lang_textnewsmilie?></td>
								<td><input type="text" name="newcode" /></td>
								<td colspan="2"><input type="text" name="newurl1" /></td>
							</tr>
							<tr>
								<td bgcolor="<?=$altbg2?>" colspan="4"><img src="./images/pixel.gif"></td>
							</tr>
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
								<td bgcolor="<?=$altbg2?>" class="tablerow"><img src="<?=$smdir?>/<?=$smilie[url]?>" /></td>
							</tr>

<?
}
?>

							<tr>
								<td bgcolor="<?=$altbg2?>" colspan="4"><img src="./images/pixel.gif"></td>
							</tr>
							<tr bgcolor="<?=$altbg1?>" class="tablerow">
								<td colspan="4"><?=$lang_textnewpicon?>&nbsp;&nbsp;<input type="text" name="newurl2" /></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
				<center><br><input type="submit" name="smiliesubmit" value="<?=$lang_textsubmitchanges?>" /></center></form>
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
<table cellspacing="0" cellpadding="0" border="0" width="450" 
align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" 
cellpadding="<?=$tablespace?>" width="100%">

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
<td><input type="checkbox" name="delete<?=$censor[id]?>" 
value="<?=$censor[id]?>" /></td>
<td align="right"><input type="text" size="20" name="find<?=$censor[id]?>" 
value="<?=$censor[find]?>" /></td>
<td align="right"><input type="text" size="20" 
name="replace<?=$censor[id]?>" value="<?=$censor[replace1]?>" /></td>
</tr>

<?
}
?>

<tr bgcolor="<?=$altbg2?>"><td colspan="3"><img 
src="./images/pixel.gif"></td></tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td align="right" colspan="2"><?=$lang_textnewcode?>&nbsp;&nbsp;<input 
type="text" size="20" name="newfind" /></td>
<td align="right"><input type="text" size="20" name="newreplace" /></td>
</tr>

</table>
</td></tr></table>
<center><br><input type="submit" name="censorsubmit" 
value="<?=$lang_textsubmitchanges?>" /></center>
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
<table cellspacing="0" cellpadding="0" border="0" width="650" 
align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" 
cellpadding="<?=$tablespace?>" width="100%">

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
<td><input type="checkbox" name="delete<?=$rank[id]?>" 
value="<?=$rank[id]?>" /></td>
<td><input type="text" name="title<?=$rank[id]?>" value="<?=$rank[title]?>" 
/></td>
<td><input type="text" name="posts<?=$rank[id]?>" value="<?=$rank[posts]?>" 
size="5" /></td>
<td><input type="text" name="stars<?=$rank[id]?>" value="<?=$rank[stars]?>" 
size="4" /></td>
<td><select name="allowavatars<?=$rank[id]?>"><option value="yes" 
<?=$avataryes?>><?=$lang_texton?></option>
<option value="no" <?=$avatarno?>><?=$lang_textoff?></option></select></td>
<td><input type="text" name="avaurl<?=$rank[id]?>" 
value="<?=$rank[avatarrank]?>" size="20" /></td>
</tr>

<?
$avataryes = "";
$avatarno = "";
}
?>

<tr bgcolor="<?=$altbg2?>"><td colspan="6"> </td></tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td colspan="2"><?=$lang_textnewrank?>&nbsp;&nbsp;<input type="text" 
name="newtitle" /></td>
<td><input type="text" name="newposts" size="5" /></td>
<td><input type="text" name="newstars" size="4" /></td>
<td><select name="newallowavatars"><option 
value="yes"><?=$lang_texton?></option><option 
value="no"><?=$lang_textoff?></option></select></td>
<td><input type="text" name="newavaurl" size="20" /></td>
</tr>

</table>
</td></tr></table>
<center><br><input type="submit" name="rankssubmit" 
value="<?=$lang_textsubmitchanges?>" /></center>
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

$db->query("UPDATE $table_ranks SET title='$title', posts='$posts', 
stars='$stars', allowavatars='$allowavatars', avatarrank='$avaurl' WHERE 
id='$ranks[id]'");
}

if($newtitle != "") {
$db->query("INSERT INTO $table_ranks VALUES ('$newtitle', '$newposts', '', 
'$newstars', '$newallowavatars', '$newavaurl')");
}

echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td 
align=\"center\">$lang_rankingsupdate</td></tr>";
}
}



if($action == "newsletter") {
if(!$newslettersubmit) {
?>

<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=newsletter">
<table cellspacing="0" cellpadding="0" border="0" width="550" 
align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" 
cellpadding="<?=$tablespace?>" width="100%">

<tr class="header">
<td colspan=2><?=$lang_textnewsletter?></td>
</tr>


<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td><?=$lang_textsubject?></td><td><input type="text" name="newssubject" 
size="80" /></td>
</tr>
<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td valign=top><?=$lang_textmessage?></td><td><textarea cols="80" rows="10" 
name="newsmessage"></textarea></td>
</tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td valign=top><?=$lang_textsendvia?></td><td><input type="radio" 
value="email" checked name="sendvia"> <?=$lang_textemail?><BR><input 
type="radio" value="u2u" checked name="sendvia"> <?=$lang_textu2u?></td>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td valign=top><?=$lang_textsendto?></td>
</td><td><input type="radio" 
value="all" checked name="to"> <?=$lang_textsendall?><BR><input 
type="radio" value="staff" name="to"> <?=$lang_textsendstaff?><BR><input 
type="radio" value="admin" name="to"> <?=$lang_textsendadmin?><BR><input 
type="radio" value="supermod" name="to"> <?=$lang_textsendsupermod?><BR><input 
type="radio" value="mod" name="to"> <?=$lang_textsendmod?></td>

</tr>
</table>
</td></tr></table>
<center><br><input type="submit" name="newslettersubmit" 
value="<?=$lang_textsubmitchanges?>" /></center>
</form>

</td>
</tr>

<?
}
if($newslettersubmit) {

if($to == "all"){
$query = $db->query("SELECT * FROM $table_members WHERE newsletter='yes'");
}
elseif($to == "staff"){
$query = $db->query("SELECT * FROM $table_members WHERE status='Administrator' OR status='Super Moderator' OR status='Moderator'");
}
elseif($to == "admin"){
$query = $db->query("SELECT * FROM $table_members WHERE status='Administrator'");
}
elseif($to == "supermod"){
$query = $db->query("SELECT * FROM $table_members WHERE status='Super moderator'");
}
elseif($to == "mod"){
$query = $db->query("SELECT * FROM $table_members WHERE status='Moderator'");
}

while ($memnews = $db->fetch_array($query)) {
if($sendvia == "u2u") {
$newssuubject = addslashes($newssubject);
$newsmessage = addslashes($newsmessage);
$db->query("INSERT INTO $table_u2u VALUES('', '$memnews[username]', 
'$xmbuser', '" . time() . "', '$newssubject', '$newsmessage', 'inbox', 'yes', 'no')");
// Remove slashes from newsletter
} else {
$newssuubject = stripslashes($newssubject);
$newsmessage = stripslashes($newsmessage);
mail("$memnews[email]", "$newssubject", "$newsmessage", "From: $bbname <$adminemail>");

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
<table cellspacing="0" cellpadding="0" border="0" width="550" 
align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" 
cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header" colspan="2"><?=$lang_textprune?></td>
</tr>

<tr>
<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$lang_prunewhere?></td>
<td align="right" bgcolor="<?=$altbg2?>"><input type="text" name="days" 
size="7" /></td>
</tr>

<tr>
<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$lang_prunein?></td>
<td align="right" bgcolor="<?=$altbg2?>"><?=$forumselect?></td>
</tr>

</table>
</td></tr></table>
<center><br><input type="submit" name="prunesubmit" 
value="<?=$lang_textsubmitchanges?>" /></center>
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
	$db->query("UPDATE $table_forums SET posts=posts-1, threads=threads-1 WHERE fid='$thread[fid]'");
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
if(!$edit && !$editsubmit && !$delete && !$deletesubmit && !$new && 
!$restore && !$restoresubmit) {
?>
<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=templates">
<table cellspacing="0" cellpadding="0" border="0" width="550" 
align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" 
cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header"><?=$lang_templates?></td>
</tr>
<tr>
<td bgcolor="<?=$altbg2?>" class="tablerow" align="left">
<INPUT TYPE="text" NAME="newtemplatename" size="30" 
maxlength="50">&nbsp;&nbsp;
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
<td bgcolor="<?=$altbg1?>" align="right"><input type="submit" name="restore" 
value="<?=$lang_textrestoredeftemps?>">&nbsp;
<input type="submit" name="download" 
value="<?=$lang_textdownloadtemps?>"></td>
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
<table cellspacing="0" cellpadding="0" border="0" width="300" 
align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" 
cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header"><?=$lang_templates?></td>
</tr>
<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow" align="center">
<?=$lang_templaterestoreconfirm?><p>
<center><input type="submit" name="restoresubmit" value="<?=$lang_textyes?>" 
/></center>
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
$db->query("INSERT INTO $table_templates VALUES ('', 
'".addslashes($template[0])."', '".addslashes($template[1])."')");
}
$db->query("DELETE FROM $table_templates WHERE name=''");
echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td 
align=\"center\">$lang_templatesrestoredone</td></tr>";
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
<table cellspacing="0" cellpadding="0" border="0" width="550" 
align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" 
cellpadding="<?=$tablespace?>" width="100%">

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
<textarea cols="70" rows="20" 
name="templatenew"><?=$template[template]?></textarea>
<center><input type="submit" name="editsubmit" 
value="<?=$lang_textsubmitchanges?>" /></center>
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
<table cellspacing="0" cellpadding="0" border="0" width="93%" 
align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" 
cellpadding="<?=$tablespace?>" width="100%">

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
<center><input type="submit" name="deletesubmit" value="<?=$lang_textyes?>" 
/></center>
</form>

</td>
</tr>
<?
}
if($deletesubmit) {
$db->query("DELETE FROM $table_templates WHERE id='$tid'");
echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td 
align=\"center\">$lang_templatesdelete</td></tr>";
}

if($new) {
?>
<tr bgcolor="<?=$altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=templates&tid=new">
<table cellspacing="0" cellpadding="0" border="0" width="550" 
align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" 
cellpadding="<?=$tablespace?>" width="100%">

<tr>
<td class="header"><?=$lang_templates?></td>
</tr>
<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow" align="center">
<?=$lang_templatename?> <input type="text" name="namenew" size="30" 
value="<?=$newtemplatename?>" /><br />
<textarea cols="70" rows="20" name="templatenew"></textarea>
<center><input type="submit" name="editsubmit" 
value="<?=$lang_textsubmitchanges?>" /></center>
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
if($action == "attachments") {
	if(!$attachsubmit && !$searchsubmit) {
		$forumselect = "<select name=\"forumprune\">\n";
		$forumselect .= "<option 
value=\"$lang_textall\">$lang_textall</option>\n";
		$querycat = $db->query("SELECT * FROM $table_forums WHERE type='forum' OR 
type='sub' ORDER BY displayorder");
		while($forum = $db->fetch_array($querycat)) {
			$forumselect .= "<option value=\"$forum[fid]\">$forum[name]</option>\n";
		}
		$forumselect .= "</select>";
		?>
		<tr bgcolor="<?=$altbg2?>">
		<td align="center">
		<br>
		<form method="post" action="cp2.php?action=attachments">
		<table cellspacing="0" cellpadding="0" border="0" width="550" 
align="center">
		<tr><td bgcolor="<?=$bordercolor?>">

		<table border="0" cellspacing="<?=$borderwidth?>" 
cellpadding="<?=$tablespace?>" width="100%">

		<tr>
		<td class="header" colspan="2"><?=$lang_textsearch?></td>
		</tr>
		<tr>
		<td class="tablerow" 
bgcolor="<?=$altbg1?>"><?=$lang_attachmanwherename?></td>
		<td bgcolor="<?=$altbg2?>"><input type="text" name="filename" size="30" 
/></td>
		</tr>
		<tr>
		<td class="tablerow" 
bgcolor="<?=$altbg1?>"><?=$lang_attachmanwhereauthor?></td>
		<td bgcolor="<?=$altbg2?>"><input type="text" name="author" size="40" 
/></td>
		</tr>
		<tr>
		<td class="tablerow" 
bgcolor="<?=$altbg1?>"><?=$lang_attachmanwhereforum?></td>
		<td bgcolor="<?=$altbg2?>"><?=$forumselect?></td>
		</tr>
		<tr>
		<td class="tablerow" 
bgcolor="<?=$altbg1?>"><?=$lang_attachmanwheresizesmaller?></td>
		<td bgcolor="<?=$altbg2?>"><input type="text" name="sizeless" size="20" 
/></td>
		</tr>
		<tr>
		<td class="tablerow" 
bgcolor="<?=$altbg1?>"><?=$lang_attachmanwheresizegreater?></td>
		<td bgcolor="<?=$altbg2?>"><input type="text" name="sizemore" size="20" 
/></td>
		</tr>
		<tr>
		<td class="tablerow" 
bgcolor="<?=$altbg1?>"><?=$lang_attachmanwheredlcountsmaller?></td>
		<td bgcolor="<?=$altbg2?>"><input type="text" name="dlcountless" size="20" 
/></td>
		</tr>
		<tr>
		<td class="tablerow" 
bgcolor="<?=$altbg1?>"><?=$lang_attachmanwheredlcountgreater?></td>
		<td bgcolor="<?=$altbg2?>"><input type="text" name="dlcountmore" size="20" 
/></td>
		</tr>
		<tr>
		<td class="tablerow" 
bgcolor="<?=$altbg1?>"><?=$lang_attachmanwheredaysold?></td>
		<td bgcolor="<?=$altbg2?>"><input type="text" name="daysold" size="20" 
/></td>
		</tr>
		</table>
		</td></tr></table><br>
		<center><input type="submit" name="searchsubmit" 
value="<?=$lang_textsubmitchanges?>" /></center>
		</form>

		</td>
		</tr>
		<?
	}
	if($searchsubmit) {
		?>
		<tr bgcolor="<?=$altbg2?>">
		<td align="center">
		<br />
		<form method="post" action="cp2.php?action=attachments">
		<table cellspacing="0" cellpadding="0" border="0" width="93%" 
align="center">
		<tr><td bgcolor="<?=$bordercolor?>">

		<table border="0" cellspacing="<?=$borderwidth?>" 
cellpadding="<?=$tablespace?>" width="100%">

		<tr>
		<td class="header" colspan="6"><?=$lang_textattachsearchresults?></td>
		</tr>
		<tr>
		<td class="header" width="4%" align="center">?</td>
		<td class="header" width="25%"><?=$lang_textfilename?></td>
		<td class="header" width="29%"><?=$lang_textauthor?></td>
		<td class="header" width="27%"><?=$lang_textinthread?></td>
		<td class="header" width="10%"><?=$lang_textfilesize?></td>
		<td class="header" width="5%"><?=$lang_textdownloads?></td>


		</tr>
		<?
		if($forumprune != "" && $forumprune != "$lang_textall") {
			$queryforum = "AND p.fid='$forumprune' ";
		}
		if($daysold != "") {
			$datethen = time() - (86400*$daysold);
			$querydate = "AND p.dateline <= '$datethen' ";
		}
		if($author != "") {
			$queryauthor = "AND p.author = '$author' ";
		}
		if($filename != "") {
			$queryname = "AND a.filename LIKE '%$filename%' ";
		}
		if($sizeless != "") {
			$querysizeless = "AND a.filesize < '$sizeless' ";
		}
		if($sizemore != "") {
			$querysizemore = "AND a.filesize > '$sizemore' ";
		}
		if($dlcountless != "") {
			$querydlcountless = "AND a.downloads < '$dlcountless' ";
		}
		if($dlcountmore != "") {
			$querydlcountmore = "AND a.downloads > '$dlcountmore' ";
		}
		$query = $db->query("SELECT a.*, p.*, t.tid, t.subject AS tsubject, f.name AS fname FROM $table_attachments a, $table_posts p, $table_threads t, $table_forums f WHERE a.pid=p.pid AND t.tid=a.tid AND f.fid=p.fid $queryforum $querydate $queryauthor $queryname $querysizeless $querysizemore");
			while($attachment = $db->fetch_array($query)) {
			$attachsize = strlen($attachment[attachment]);
			if($attachsize >= 1073741824) { $attachsize = round($attachsize / 1073741824 * 100) / 100 . "gb"; }
			elseif($attachsize >= 1048576) { $attachsize = round($attachsize / 1048576 * 100) / 100 . "mb"; }
			elseif($attachsize >= 1024)	{ $attachsize = round($attachsize / 1024 * 100) / 100 . "kb"; }
			else { $attachsize = $attachsize . "b"; }
			$attachment[tsubject] = stripslashes($attachment[tsubject]);
			$attachment[fname] = stripslashes($attachment[fname]);
			$attachment[filename] = stripslashes($attachment[filename]);
			?>
			<tr>
			<td bgcolor="<?=$altbg1?>" class="tablerow" align="center" valign="middle"><a href="cp2.php?action=delete_attachment&amp;aid=<?=$attachment[aid]?>">Delete</a>
			<td bgcolor="<?=$altbg2?>" class="tablerow" valign="top"><input type="text" name="filename<?=$attachment[aid]?>" value="<?=$attachment[filename]?>"><br><small><a href="viewthread.php?action=attachment&tid=<?=$attachment[tid]?>&pid=<?=$attachment[pid]?>"><?=$lang_textdownload?></a></td>
			<td bgcolor="<?=$altbg2?>" class="tablerow" valign="top"><?=$attachment[author]?></td>
			<td bgcolor="<?=$altbg2?>" class="tablerow" valign="top"><a href="viewthread.php?tid=<?=$attachment[tid]?>"><?=$attachment[tsubject]?></a><br><small><?=$lang_textinforum?> <a href="forumdisplay.php?fid=<?=$attachment[fid]?>"><?=$attachment[fname]?></a></small></td>
			<td bgcolor="<?=$altbg2?>" class="tablerow" valign="top" align="center"><?=$attachsize?></td>
			<td bgcolor="<?=$altbg2?>" class="tablerow" valign="top" align="center"><?=$attachment[downloads]?></td>
			</tr>
			<?

		}
		?>
		</table>
		</td></tr></table><br>
		<center><input type="submit" name="deletesubmit" 
value="<?=$lang_textsubmitchanges?>" /></center>
		<input type="hidden" name="filename" value="<?=$filename?>">
		<input type="hidden" name="author" value="<?=$author?>">
		<input type="hidden" name="forumprune" value="<?=$forumprune?>">
		<input type="hidden" name="sizeless" value="<?=$sizeless?>">
		<input type="hidden" name="sizemore" value="<?=$sizemore?>">
		<input type="hidden" name="dlcountless" value="<?=$dlcountless?>">
		<input type="hidden" name="dlcountmore" value="<?=$dlcountmore?>">
		<input type="hidden" name="daysold" value="<?=$daysold?>">
		</form>

		</td>
		</tr>
		<?
	}
	if($deletesubmit) {
		if($forumprune != "" && $forumprune != "$lang_textall") {
			$queryforum = "AND p.fid='$forumprune' ";
		}
		if($daysold != "") {
			$datethen = time() - (86400*$daysold);
			$querydate = "AND p.dateline <= '$datethen' ";
		}
		if($author != "") {
			$queryauthor = "AND p.author = '$author' ";
		}
		if($filename != "") {
			$queryname = "AND a.filename LIKE '%$filename%' ";
		}
		if($sizeless != "") {
			$querysizeless = "AND a.filesize < '$sizeless' ";
		}
		if($sizemore != "") {
			$querysizemore = "AND a.filesize > '$sizemore' ";
		}
		if($dlcountless != "") {
			$querydlcountless = "AND a.downloads < '$dlcountless' ";
		}
		if($dlcountmore != "") {
			$querydlcountmore = "AND a.downloads > '$dlcountmore' ";
		}

		$query = $db->query("SELECT a.*, p.*, t.tid, t.subject AS tsubject, f.name AS fname FROM $table_attachments a, $table_posts p, $table_threads t, $table_forums f WHERE a.pid=p.pid AND t.tid=a.tid AND f.fid=p.fid $queryforum $querydate $queryauthor $queryname $querysizeless $querysizemore");
		while($attachment = $db->fetch_array($query)) {
			$delete = "delete$attachment[aid]";
			$delete = "${$delete}";
			$afilename = "filename$attachment[aid]";
			$afilename = "${$afilename}";
			$status = "status$forum[fid]";
			$status = "${$status}";
			$delete = "delete$forum[fid]";
			$delete = "${$delete}";
			$moveto = "moveto$forum[fid]";
			$moveto = "${$moveto}";
//			if($delete != "") {
//				$db->query("DELETE FROM $table_attachments WHERE aid='$attachment[aid]'");
//			}
			if($attachment[filename] != $afilename) {
				$db->query("UPDATE $table_attachments SET filename='$afilename' WHERE aid='$attachment[aid]'");
			}
		}
		echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_textattachmentsupdate</td></tr>";
	}
}
echo "</table></td></tr></table>";

if($action == "cplog") { 
?> 
<tr bgcolor="<?=$altbg2?>"> 
<td align="center"> 
<br /> 
<table cellspacing="0" cellpadding="0" border="0" width="500" align="center"> 
<tr><td bgcolor="<?=$bordercolor?>"> 

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%"> 

<tr class="header"> 
<td>Username:</td> 
<td>Time:</td> 
<td>URL:</td> 
</tr> 
<? 
$logcontents = file("./cplogfile.log"); 
$total = count($logcontents); 

for($i = 0; $i < $total; $i++) { 
$recordinfo = explode("|#||#|", $logcontents[$i]); 
$date = date($dateformat, $recordinfo[2]); 
$time = date($timeformat, $recordinfo[2]); 
$url = "<a href=\"$recordinfo[3]\" target=\"_blank\">$recordinfo[3]</a>"; 
?> 
<tr> 
<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$recordinfo[0]?><br><small>IP: <?=$recordinfo[1]?></small></td> 
<td class="tablerow" bgcolor="<?=$altbg2?>"><?=$date?> at <?=$time?></td> 
<td class="tablerow" bgcolor="<?=$altbg1?>"><?=$url?></td> 
</tr> 
<? 
} 
?> 
</table> 
</td></tr></table> 
</td> 
</tr> 
<? 
} 


$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);

if($action == "delete_attachment") {
	$db->query("DELETE FROM $table_attachments WHERE aid='$aid'");
	echo "<p align=\"center\">Deleted ...</br>";
}

eval("\$footer = \"".template("footer")."\";");
echo $footer;

function readFileAsINI($filename) {
// Function taken from a phpBB hack with permission
	$fc = file($filename);
	while (list($linenum,$line) = each($fc)) {
		$temp = explode("=",$line);
		$key = $temp[0];
		$val = $temp[1];
		$thefile[$key] = $val;
	}
	return $thefile;
}

?>