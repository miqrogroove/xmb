<?php
/*

XMB 1.8 Partagium
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/
require "./header.php";
loadtemplates('header,footer');

if(!$xmbuser || !$xmbpw) {
	$xmbuser = "";
	$xmbpw = "";
	$status = "";
}

// Start Download Templates and Theme Code
if($status == "Administrator" || $status=="Super Administrator") {
	if($action == "templates" && $download) {
		$templates=$db->query("SELECT * FROM $table_templates");
		while ($template=$db->fetch_array($templates)) {
			$template[template] = stripslashes($template[template]);
			$code.= "$template[name]|#*XMB TEMPLATE*#| \r\n $template[template] \r\n|#*XMB TEMPLATE FILE*#|";
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

if($status != "Administrator" && $status !="Super Administrator") {
eval("\$notadmin = \"".template("error_nologinsession")."\";");
echo $notadmin;
eval("\$footer = \"".template("footer")."\";");
echo $footer;
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
$filehandle=fopen("./cplogfile.php","a");
flock($filehandle, 2);
fwrite($filehandle, $string);
fclose($filehandle);

?>
<table cellspacing="0" cellpadding="0" border="0" width="<?php echo $tablewidth?>" align="center">
<tr><td bgcolor="<?php echo $bordercolor?>">

<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?php echo $lang_textcp?></td>
</tr>

<tr bgcolor="<?php echo $altbg1?>" class="tablerow">
<td align="center">
<a href="cp.php?action=settings"><?php echo $lang_textsettings?></a> - <a href="cp.php?action=forum"><?php echo $lang_textforums?></a> -
<a href="cp.php?action=mods"><?php echo $lang_textmods?></a> - <a href="cp.php?action=members"><?php echo $lang_textmembers?></a> -
<a href="cp2.php?action=restrictions"><?php echo $lang_cprestricted?></a> - <a href="cp.php?action=ipban"><?php echo $lang_textipban?></a> -
<a href="cp.php?action=upgrade"><?php echo $lang_textupgrade?></a> - <a href="cp.php?action=search"><?php echo $lang_cpsearch?></a><br>
<a href="cp2.php?action=themes"><?php echo $lang_themes?></a> - <a href="cp2.php?action=smilies"><?php echo $lang_smilies?></a> -
<a href="cp2.php?action=censor"><?php echo $lang_textcensors?></a> - <a href="cp2.php?action=ranks"><?php echo $lang_textuserranks?></a> -
<a href="cp2.php?action=newsletter"><?php echo $lang_textnewsletter?></a> - <a href="cp2.php?action=prune"><?php echo $lang_textprune?></a> -
<a href="cp2.php?action=templates"><?php echo $lang_templates?></a> - <a href="cp2.php?action=attachments"><?php echo $lang_textattachman?></a><br>
<a href="cp2.php?action=cplog"><?php echo $lang_cplog?></a>
<br /><tr bgcolor="<?php echo $altbg2?>" class="tablerow"><td align="center"><a href="tools.php?action=fixttotals"><?php echo $lang_textfixthread?></a> - <a href="tools.php?action=fixftotals"><?php echo $lang_textfixmemposts?></a> - <a href="tools.php?action=fixmposts"><?php echo $lang_textfixposts?></a> - <a href="tools.php?action=updatemoods"><?php echo $lang_textfixmoods?></a> - <a href="tools.php?action=u2udump"><?php echo $lang_u2udump?></a> - <a href="tools.php?action=whosonlinedump"><?php echo $lang_cpwodump?></a>
<br /><a href="tools.php?action=fixforumthemes"><?php echo $lang_fixforumthemes?></a>
<?php
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

<?php
if(!$action) {
}

if($action == "restrictions") {
if(!$restrictedsubmit) {
?>

<tr bgcolor="<?php echo $altbg2?>">
<td align="center">
<br>
<form method="post" action="cp2.php?action=restrictions">
<table align="center" border="0" cellspacing="0" cellpadding="0" width="450">
<tr>
<td bgcolor="<?php echo $bordercolor?>">
<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
<tr class="header">
<td><?php echo $lang_textdeleteques?></td>
<td><?php echo $lang_restrictedname?></td>
</tr>

<?php
$query = $db->query("SELECT * FROM $table_restricted ORDER BY id");
while($restricted = $db->fetch_array($query)) {
?>
<tr class="tablerow">
<td bgcolor="<?php echo $altbg1?>"><input type="checkbox" name="delete<?php echo $restricted[id]?>" value="<?php echo $restricted[id]?>"></td>
<td bgcolor="<?php echo $altbg1?>"><input type="text" size="30" name="name<?php echo $restricted[id]?>" value="<?php echo $restricted[name]?>"></td>
</tr>
<?php
}
?>
<tr>
<td bgcolor="<?php echo $altbg2?>" colspan="2"><img src="./images/pixel.gif"></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?php echo $altbg1?>" colspan="2" align="center"><?php echo $lang_textnewcode?><br><?php echo $lang_newrestriction?><br><?php echo $lang_newrestrictionwhy?><br><br><input type="text" size="30" name="newname"></td>
</tr>
</table>
</td>
</tr>
</table><br>
<center><input type="submit" name="restrictedsubmit" value="<?php echo $lang_textsubmitchanges?>"></center>
</form>
</td>
</tr>

<?php
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
<?php

}
}

if($action == "themes") {
if(!$themesubmit && !$single && !$importsubmit) {
?>

				<tr bgcolor="<?php echo $altbg2?>">
					<td align="center">
						<br />
						<form method="POST" action="cp2.php?action=themes">
						<table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
							<tr>
								<td bgcolor="<?php echo $bordercolor?>">
									<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
										<tr class="header">
											<td><?php echo $lang_textdeleteques?></td>
											<td><?php echo $lang_textthemename?></td>
<td><?php echo $lang_numberusing?></td>
										</tr>

<?php
$query = $db->query("SELECT name FROM $table_themes ORDER BY name ASC");
$i=1;
while($themeinfo = $db->fetch_array($query)) {
$memthemequery = $db->query("SELECT COUNT(theme) FROM $table_members where theme='$themeinfo[name]'");
$members = $db->result($memthemequery, 0);

if($themeinfo[name] == "$theme") {
$checked = "checked=\"checked\"";
}
?>

										<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
											<td><input type="checkbox" name="delete" value="<?php echo $i?>" /></td>
											<td><input type="text" name="name_<?php echo $i?>" value="<?php echo $themeinfo[name]?>" /> <a href="cp2.php?action=themes&single=<?php echo $themeinfo[name]?>"><?php echo $lang_textdetails?></a> - <a href="cp2.php?action=themes&download=<?php echo $themeinfo[name]?>"><?php echo $lang_textdltheme?></a></td>
											<td><?php echo $members?></td>
										</tr>

<?php
$checked = "";
$i++;
}
?>

										<tr bgcolor="<?php echo $altbg2?>">
											<td colspan="3"><img src="./images/pixel.gif"></td>
										</tr>
										<tr bgcolor="<?php echo $altbg1?>" class="tablerow">
											<td colspan="3"><a href="cp2.php?action=themes&single=anewtheme1"><?php echo $lang_textnewtheme?></a></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
							<center><br><input type="submit" name="themesubmit" value="<?php echo $lang_textsubmitchanges?>" /></center></form><br>
							<form method="post" action="cp2.php?action=themes" enctype="multipart/form-data">
						<table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
							<tr>
								<td bgcolor="<?php echo $bordercolor?>">
									<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
										<tr class="header">
											<td colspan="2"><?php echo $lang_textimporttheme?></td>
										</tr>
										<tr class="tablerow">
											<td bgcolor="<?php echo $altbg1?>"><?php echo $lang_textthemefile?></td>
											<td bgcolor="<?php echo $altbg2?>"><input name="themefile" type="file"></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
							<center><br><input type="submit" name="importsubmit" value="<?php echo $lang_textimportsubmit?>" /></center></form>
					</td>
				</tr>

<?php
}
if($importsubmit) {

	$themebits = readFileAsINI($themefile);
	$start = "INSERT INTO $table_themes";

	while(list($key,$val) = each($themebits)){
		$keysql .= "$key, ";
		$valsql .= "'$val', ";
	}

	$keysql = substr($keysql,0,-2);
	$valsql = substr($valsql,0,-2);

	$sql = "$start ($keysql) VALUES ($valsql);";
	$query = $db->query($sql);

	echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">";
		if (!$query) {
			echo "$lang_textthemeimportfail";
		} else {
			echo "$lang_textthemeimportsuccess";
		}
	echo "</td></tr>";
}
if($themesubmit) {
	$query = $db->query("SELECT name FROM $table_themes ORDER BY name ASC");
	$i=1;
	while($themes = $db->fetch_array($query)) {
		if($delete == $i){
			$db->query("DELETE FROM $table_themes WHERE name='$themes[name]'");
			if($SETTINGS[theme] == $themes[name]){
				$query = $db->query("SELECT name FROM $table_themes WHERE name != '$SETTINGS[theme]' LIMIT 0,1");
				$temp = $db->fetch_row($query);
				$db->query("UPDATE $table_settings SET theme='$temp[name]' WHERE theme='$SETTINGS[theme]'");
			}
		}else{
			$name = "name_{$i}";
			$name = $$name;
			if($name != $themes[name]){
				$db->query("UPDATE $table_themes SET name='$name' WHERE name='$themes[name]'");
				$db->query("UPDATE $table_members SET theme='$name' WHERE theme='$themes[name]'");
				$db->query("UPDATE $table_forums SET theme='$name' WHERE theme='$themes[name]'");
				if($SETTINGS[theme] == $themes[name]){
					$db->query("UPDATE $table_settings SET theme='$name'");
				}
			}
		}
		$i++;
	}
	echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_themeupdate</td></tr>";
}

if($single && $single != "submit" && $single != "anewtheme1") {
	$query = $db->query("SELECT * FROM $table_themes WHERE name='$single'");
	$themestuff = $db->fetch_array($query);
?>

	<tr bgcolor="<?php echo $altbg2?>">
		<td align="center">
		<br /><form method="post" action="cp2.php?action=themes&single=submit">
			<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
				<tr>
					<td bgcolor="<?php echo $bordercolor?>">
						<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_texthemename?></td>
								<td colspan="2"><input type="text" name="namenew" value="<?php echo $themestuff[name]?>" /></td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textbgcolor?></td>
								<td><input type="text" name="bgcolornew" value="<?php echo $themestuff[bgcolor]?>" /></td>
								<td bgcolor="<?php echo $themestuff[bgcolor]?>">&nbsp;</td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textaltbg1?></td>
								<td><input type="text" name="altbg1new" value="<?php echo $themestuff[altbg1]?>" /></td>
								<td bgcolor="<?php echo $themestuff[altbg1]?>">&nbsp;</td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textaltbg2?></td>
								<td><input type="text" name="altbg2new" value="<?php echo $themestuff[altbg2]?>" /></td>
								<td bgcolor="<?php echo $themestuff[altbg2]?>">&nbsp;</td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textlink?></td>
								<td><input type="text" name="linknew" value="<?php echo $themestuff[link]?>" /></td>
								<td bgcolor="<?php echo $themestuff[link]?>">&nbsp;</td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textborder?></td>
								<td><input type="text" name="bordercolornew" value="<?php echo $themestuff[bordercolor]?>" /></td>
								<td bgcolor="<?php echo $themestuff[bordercolor]?>">&nbsp;</td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textheader?></td>
								<td><input type="text" name="headernew" value="<?php echo $themestuff[header]?>" /></td>
								<td bgcolor="<?php echo $themestuff[header]?>">&nbsp;</td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textheadertext?></td>
								<td><input type="text" name="headertextnew" value="<?php echo $themestuff[headertext]?>" /></td>
								<td bgcolor="<?php echo $themestuff[headertext]?>">&nbsp;</td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_texttop?></td>
								<td><input type="text" name="topnew" value="<?php echo $themestuff[top]?>" /></td>
								<td bgcolor="<?php echo $themestuff[top]?>">&nbsp;</td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textcatcolor?></td>
								<td><input type="text" name="catcolornew" value="<?php echo $themestuff[catcolor]?>" /></td>
								<td bgcolor="<?php echo $themestuff[catcolor]?>">&nbsp;</td>
							</tr>
								<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textcattextcolor?></td>
								<td><input type="text" name="cattextnew" value="<?php echo $themestuff[cattext]?>" /></td>
								<td bgcolor="<?php echo $themestuff[cattext]?>">&nbsp;</td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_texttabletext?></td>
								<td><input type="text" name="tabletextnew" value="<?php echo $themestuff[tabletext]?>" /></td>
								<td bgcolor="<?php echo $themestuff[tabletext]?>">&nbsp;</td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_texttext?></td>
								<td><input type="text" name="textnew" value="<?php echo $themestuff[text]?>" /></td>
								<td bgcolor="<?php echo $themestuff[text]?>">&nbsp;</td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textborderwidth?></td>
								<td colspan="2"><input type="text" name="borderwidthnew" value="<?php echo $themestuff[borderwidth]?>" size="2" /></td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textwidth?></td>
								<td colspan="2"><input type="text" name="tablewidthnew" value="<?php echo $themestuff[tablewidth]?>" size="3" /></td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textspace?></td>
								<td colspan="2"><input type="text" name="tablespacenew" value="<?php echo $themestuff[tablespace]?>" size="2" /></td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textfont?></td>
								<td colspan="2"><input type="text" name="fnew" value="<?php echo $themestuff[font]?>" /></td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textbigsize?></td>
								<td colspan="2"><input type="text" name="fsizenew" value="<?php echo $themestuff[fontsize]?>" size="4" /></td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_textboardlogo?></td>
								<td colspan="2"><input type="text"  value="<?php echo $themestuff[boardimg]?>" name="boardlogonew" /></td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_imgdir?></td>
								<td colspan="2"><input type="text"  value="<?php echo $themestuff[imgdir]?>" name="imgdirnew" /></td>
							</tr>
							<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
								<td><?php echo $lang_smdir?></td>
								<td colspan="2"><input type="text"  value="<?php echo $themestuff[smdir]?>" name="smdirnew" /></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
				<center><br><input type="submit" value="<?php echo $lang_textsubmitchanges?>" /></center><input type="hidden" name="orig" value="<?php echo $single?>" /></form>
		</td>
	</tr>

<?php
}
if($single == "anewtheme1") {
?>

	<tr bgcolor="<?php echo $altbg2?>">
		<td align="center">
		<br />
		<form method="post" action="cp2.php?action=themes&single=submit">
		<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
			<tr>
				<td bgcolor="<?php echo $bordercolor?>">
					<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_texthemename?></td>
							<td><input type="text" name="namenew" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textbgcolor?></td>
							<td><input type="text" name="bgcolornew" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textaltbg1?></td>
							<td><input type="text" name="altbg1new" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textaltbg2?></td>
							<td><input type="text" name="altbg2new" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textlink?></td>
							<td><input type="text" name="linknew" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textborder?></td>
							<td><input type="text" name="bordercolornew" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textheader?></td>
							<td><input type="text" name="headernew" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textheadertext?></td>
							<td><input type="text" name="headertextnew" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_texttop?></td>
							<td><input type="text" name="topnew" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textcatcolor?></td>
							<td><input type="text" name="catcolornew" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textcattextcolor?></td>
							<td><input type="text" name="cattextnew" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_texttabletext?></td>
							<td><input type="text" name="tabletextnew" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_texttext?></td>
							<td><input type="text" name="textnew" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textborderwidth?></td>
							<td><input type="text" name="borderwidthnew" size="2" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textwidth?></td>
							<td><input type="text" name="tablewidthnew" size="3" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textspace?></td>
							<td><input type="text" name="tablespacenew" size="2" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textfont?></td>
							<td><input type="text" name="fnew" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textbigsize?></td>
							<td><input type="text" name="fsizenew" size="4" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_textboardlogo?></td>
							<td><input type="text" name="boardlogonew" value="<?php echo $boardimg?>" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_imgdir?></td>
							<td><input type="text" name="imgdirnew" value="images" /></td>
						</tr>
						<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
							<td><?php echo $lang_smdir?></td>
							<td><input type="text" name="smdirnew" value="images" /></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
			<center><br><input type="submit" value="<?php echo $lang_textsubmitchanges?>" /></center><input type="hidden" name="newtheme" value="<?php echo $single?>" /></form>
		</td>
	</tr>

<?php
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

	<tr bgcolor="<?php echo $altbg2?>">
		<td align="center">
		<br /><form method="post" action="cp2.php?action=smilies">
			<table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
				<tr>
					<td bgcolor="<?php echo $bordercolor?>">
						<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
							<tr class="header">
								<td colspan="4" align="left"><?php echo $lang_smilies?></td>
							</tr>
							<tr class="header">
								<td><?php echo $lang_textdeleteques?></td>
								<td><?php echo $lang_textsmiliecode?></td>
								<td><?php echo $lang_textsmiliefile?></td>
								<td><?php echo $lang_smilies?></td>
							</tr>

<?php
	$query = $db->query("SELECT * FROM $table_smilies WHERE type='smiley' ORDER BY id");
	while($smilie = $db->fetch_array($query)) {
?>

							<tr>
								<td bgcolor="<?php echo $altbg2?>" class="tablerow"><input type="checkbox" name="delete<?php echo $smilie[id]?>" value="<?php echo $smilie[id]?>" /></td>
								<td bgcolor="<?php echo $altbg2?>" class="tablerow"><input type="text" name="code<?php echo $smilie[id]?>" value="<?php echo $smilie[code]?>" /></td>
								<td bgcolor="<?php echo $altbg2?>" class="tablerow"><input type="text" name="url<?php echo $smilie[id]?>" value="<?php echo $smilie[url]?>" /></td>
								<td bgcolor="<?php echo $altbg2?>" class="tablerow"><img src="<?php echo $smdir?>/<?php echo $smilie[url]?>" /></td>
							</tr>

<?php
}
?>
							<tr>
								<td bgcolor="<?php echo $altbg2?>" colspan="4"><img src="./images/pixel.gif"></td>
							</tr>
							<tr bgcolor="<?php echo $altbg1?>" class="tablerow">
								<td><?php echo $lang_textnewsmilie?></td>
								<td><input type="text" name="newcode" /></td>
								<td colspan="2"><input type="text" name="newurl1" /></td>
							</tr>
							<tr>
								<td bgcolor="<?php echo $altbg2?>" colspan="4"><img src="./images/pixel.gif"></td>
							</tr>
							<tr>
								<td colspan="4" class="header"><?php echo $lang_picons?></td>
							</tr>
							<tr class="header">
								<td><?php echo $lang_textdeleteques?></td>
								<td colspan="2"><?php echo $lang_textsmiliefile?></td>
								<td><?php echo $lang_picons?></td>
							</tr>
<?php
	$query = $db->query("SELECT * FROM $table_smilies WHERE type='picon' ORDER BY id");
		while($smilie = $db->fetch_array($query)) {
?>

							<tr>
								<td bgcolor="<?php echo $altbg2?>" class="tablerow"><input type="checkbox" name="delete<?php echo $smilie[id]?>" value="<?php echo $smilie[id]?>" /></td>
								<td colspan="2" bgcolor="<?php echo $altbg2?>" class="tablerow"><input type="text" name="url<?php echo $smilie[id]?>" value="<?php echo $smilie[url]?>" /></td>
								<td bgcolor="<?php echo $altbg2?>" class="tablerow"><img src="<?php echo $smdir?>/<?php echo $smilie[url]?>" /></td>
							</tr>

<?php
}
?>

							<tr>
								<td bgcolor="<?php echo $altbg2?>" colspan="4"><img src="./images/pixel.gif"></td>
							</tr>
							<tr bgcolor="<?php echo $altbg1?>" class="tablerow">
								<td colspan="4"><?php echo $lang_textnewpicon?>&nbsp;&nbsp;<input type="text" name="newurl2" /></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
				<center><br><input type="submit" name="smiliesubmit" value="<?php echo $lang_textsubmitchanges?>" /></center></form>
		</td>
	</tr>

<?php
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

<tr bgcolor="<?php echo $altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=censor">
<table cellspacing="0" cellpadding="0" border="0" width="450"
align="center">
<tr><td bgcolor="<?php echo $bordercolor?>">

<table border="0" cellspacing="<?php echo $borderwidth?>"
cellpadding="<?php echo $tablespace?>" width="100%">

<tr class="header">
<td><?php echo $lang_textdeleteques?></td>
<td align="right"><?php echo $lang_textcensorfind?></td>
<td align="right"><?php echo $lang_textcensorreplace?></td>
</tr>

<?php
$query = $db->query("SELECT * FROM $table_words ORDER BY id");
while($censor = $db->fetch_array($query)) {

?>

<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
<td><input type="checkbox" name="delete<?php echo $censor[id]?>"
value="<?php echo $censor[id]?>" /></td>
<td align="right"><input type="text" size="20" name="find<?php echo $censor[id]?>"
value="<?php echo $censor[find]?>" /></td>
<td align="right"><input type="text" size="20"
name="replace<?php echo $censor[id]?>" value="<?php echo $censor[replace1]?>" /></td>
</tr>

<?php
}
?>

<tr bgcolor="<?php echo $altbg2?>"><td colspan="3"><img
src="./images/pixel.gif"></td></tr>
<tr bgcolor="<?php echo $altbg1?>" class="tablerow">
<td align="right" colspan="2"><?php echo $lang_textnewcode?>&nbsp;&nbsp;<input
type="text" size="20" name="newfind" /></td>
<td align="right"><input type="text" size="20" name="newreplace" /></td>
</tr>

</table>
</td></tr></table>
<center><br><input type="submit" name="censorsubmit"
value="<?php echo $lang_textsubmitchanges?>" /></center>
</form>

</td>
</tr>

<?php
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

<tr bgcolor="<?php echo $altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=ranks">
<table cellspacing="0" cellpadding="0" border="0" width="650"
align="center">
<tr><td bgcolor="<?php echo $bordercolor?>">

<table border="0" cellspacing="<?php echo $borderwidth?>"
cellpadding="<?php echo $tablespace?>" width="100%">

<tr class="header">
<td><?php echo $lang_textdeleteques?></td>
<td><?php echo $lang_textcusstatus?></td>
<td><?php echo $lang_textposts?></td>
<td><?php echo $lang_textstars?></td>
<td><?php echo $lang_textallowavatars?></td>
<td><?php echo $lang_textavatar?></td>
</tr>

<?php
$query = $db->query("SELECT * FROM $table_ranks ORDER BY id");
while($rank = $db->fetch_array($query)) {

if($rank[allowavatars] == "yes") {
$avataryes = "selected=\"selected\"";
}
else {
$avatarno = "selected=\"selected\"";
}
?>

<tr bgcolor="<?php echo $altbg2?>" class="tablerow">
<td><input type="checkbox" name="delete<?php echo $rank[id]?>"
value="<?php echo $rank[id]?>" /></td>
<td><input type="text" name="title<?php echo $rank[id]?>" value="<?php echo $rank[title]?>"
/></td>
<td><input type="text" name="posts<?php echo $rank[id]?>" value="<?php echo $rank[posts]?>"
size="5" /></td>
<td><input type="text" name="stars<?php echo $rank[id]?>" value="<?php echo $rank[stars]?>"
size="4" /></td>
<td><select name="allowavatars<?php echo $rank[id]?>"><option value="yes"
<?php echo $avataryes?>><?php echo $lang_texton?></option>
<option value="no" <?php echo $avatarno?>><?php echo $lang_textoff?></option></select></td>
<td><input type="text" name="avaurl<?php echo $rank[id]?>"
value="<?php echo $rank[avatarrank]?>" size="20" /></td>
</tr>

<?php
$avataryes = "";
$avatarno = "";
}
?>

<tr bgcolor="<?php echo $altbg2?>"><td colspan="6"> </td></tr>
<tr bgcolor="<?php echo $altbg1?>" class="tablerow">
<td colspan="2"><?php echo $lang_textnewrank?>&nbsp;&nbsp;<input type="text"
name="newtitle" /></td>
<td><input type="text" name="newposts" size="5" /></td>
<td><input type="text" name="newstars" size="4" /></td>
<td><select name="newallowavatars"><option
value="yes"><?php echo $lang_texton?></option><option
value="no"><?php echo $lang_textoff?></option></select></td>
<td><input type="text" name="newavaurl" size="20" /></td>
</tr>

</table>
</td></tr></table>
<center><br><input type="submit" name="rankssubmit"
value="<?php echo $lang_textsubmitchanges?>" /></center>
</form>

</td>
</tr>

<?php
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

<tr bgcolor="<?php echo $altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=newsletter">
<table cellspacing="0" cellpadding="0" border="0" width="550"
align="center">
<tr><td bgcolor="<?php echo $bordercolor?>">

<table border="0" cellspacing="<?php echo $borderwidth?>"
cellpadding="<?php echo $tablespace?>" width="100%">

<tr class="header">
<td colspan=2><?php echo $lang_textnewsletter?></td>
</tr>


<tr bgcolor="<?php echo $altbg1?>" class="tablerow">
<td><?php echo $lang_textsubject?></td><td><input type="text" name="newssubject"
size="80" /></td>
</tr>
<tr bgcolor="<?php echo $altbg1?>" class="tablerow">
<td valign=top><?php echo $lang_textmessage?></td><td><textarea cols="80" rows="10"
name="newsmessage"></textarea></td>
</tr>

<tr bgcolor="<?php echo $altbg1?>" class="tablerow">
<td valign=top><?php echo $lang_textsendvia?></td><td><input type="radio"
value="email" checked name="sendvia"> <?php echo $lang_textemail?><BR><input
type="radio" value="u2u" checked name="sendvia"> <?php echo $lang_textu2u?></td>

<tr bgcolor="<?php echo $altbg1?>" class="tablerow">
<td valign=top><?php echo $lang_textsendto?></td>
</td><td><input type="radio"
value="all" checked name="to"> <?php echo $lang_textsendall?><BR><input
type="radio" value="staff" name="to"> <?php echo $lang_textsendstaff?><BR><input
type="radio" value="admin" name="to"> <?php echo $lang_textsendadmin?><BR><input
type="radio" value="supermod" name="to"> <?php echo $lang_textsendsupermod?><BR><input
type="radio" value="mod" name="to"> <?php echo $lang_textsendmod?></td>

</tr>
</table>
</td></tr></table>
<center><br><input type="submit" name="newslettersubmit"
value="<?php echo $lang_textsubmitchanges?>" /></center>
</form>

</td>
</tr>

<?php
}
if($newslettersubmit) {

if($to == "all"){
$query = $db->query("SELECT * FROM $table_members WHERE newsletter='yes'");
}
elseif($to == "staff"){
$query = $db->query("SELECT * FROM $table_members WHERE status = 'Super Administrator' OR status='Administrator' OR status='Super Moderator' OR status='Moderator'");
}
elseif($to == "admin"){
$query = $db->query("SELECT * FROM $table_members WHERE status='Administrator' OR status = 'Super Administrator'");
}
elseif($to == "supermod"){
$query = $db->query("SELECT * FROM $table_members WHERE status='Super moderator'");
}
elseif($to == "mod"){
$query = $db->query("SELECT * FROM $table_members WHERE status='Moderator'");
}

if($sendvia == "u2u"){
	$newssuubject = addslashes($newssubject);
	$newsmessage = addslashes($newsmessage);
}

while ($memnews = $db->fetch_array($query)) {
if($sendvia == "u2u") {
	$db->query("INSERT INTO $table_u2u VALUES('', '$memnews[username]', '$xmbuser', '" . time() . "', '$newssubject', '$newsmessage', 'inbox', 'yes', 'no')");
} else {
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

<tr bgcolor="<?php echo $altbg2?>">
<td align="center">
<br />
<form method="post" action="cp2.php?action=prune">
<table cellspacing="0" cellpadding="0" border="0" width="550"
align="center">
<tr><td bgcolor="<?php echo $bordercolor?>">

<table border="0" cellspacing="<?php echo $borderwidth?>"
cellpadding="<?php echo $tablespace?>" width="100%">

<tr>
<td class="header" colspan="2"><?php echo $lang_textprune?></td>
</tr>

<tr>
<td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang_prunewhere?></td>
<td align="right" bgcolor="<?php echo $altbg2?>"><input type="text" name="days"
size="7" /></td>
</tr>

<tr>
<td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang_prunein?></td>
<td align="right" bgcolor="<?php echo $altbg2?>"><?php echo $forumselect?></td>
</tr>

</table>
</td></tr></table>
<center><br><input type="submit" name="prunesubmit"
value="<?php echo $lang_textsubmitchanges?>" /></center>
</form>

</td>
</tr>

<?php
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
	if(!$edit && !$editsubmit && !$delete && !$deletesubmit && !$new && !$restore && !$restoresubmit) {
		?>
		<tr bgcolor="<?php echo $altbg2?>">
		<td align="center">
		<br />
		<form method="post" action="cp2.php?action=templates">
		<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
		<tr><td bgcolor="<?php echo $bordercolor?>">

		<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">

		<tr>
		<td class="header"><?php echo $lang_templates?></td>
		</tr>
		<tr>
		<td bgcolor="<?php echo $altbg2?>" class="tablerow" align="left">
		<INPUT TYPE="text" NAME="newtemplatename" size="30" maxlength="50">&nbsp;&nbsp;
		<input type="submit" name="new" value="<?php echo $lang_newtemplate?>">
		</td></tr>

		<tr>
		<td bgcolor="<?php echo $altbg1?>" class="tablerow" align="left">

		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
		<td bgcolor="<?php echo $altbg1?>">

		<?php
		$query = $db->query("SELECT * FROM $table_templates ORDER BY name");
		echo "<select name=\"tid\"><option value=\"default\">$lang_selecttemplate</option>";
		while($template = $db->fetch_array($query)) {
			echo "<option value=\"$template[id]\">$template[name]</option>\n";
		}
		echo "</select>&nbsp;&nbsp;";
		?>

		<input type="submit" name="edit" value="<?php echo $lang_textedit?>">&nbsp;
		<input type="submit" name="delete" value="<?php echo $lang_deletebutton?>">
		</td>
		<td bgcolor="<?php echo $altbg1?>" align="right"><input type="submit" name="restore" value="<?php echo $lang_textrestoredeftemps?>">&nbsp;
		<input type="submit" name="download" value="<?php echo $lang_textdownloadtemps?>"></td>
		</tr>
		</table>
		</table>
		</td></tr></table>
		</form>
		</td>
		</tr>

		<?php
	}

	if($restore) {
		?>
		<tr bgcolor="<?php echo $altbg2?>">
		<td align="center">
		<br />
		<form method="post" action="cp2.php?action=templates">
		<table cellspacing="0" cellpadding="0" border="0" width="300" align="center">
		<tr><td bgcolor="<?php echo $bordercolor?>">

		<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">

		<tr>
		<td class="header"><?php echo $lang_templates?></td>
		</tr>
		<tr>
		<td bgcolor="<?php echo $altbg1?>" class="tablerow" align="center">
		<?php echo $lang_templaterestoreconfirm?><p>
		<center><input type="submit" name="restoresubmit" value="<?php echo $lang_textyes?>" /></center>
		</td>
		</tr>

		</table>
		</td></tr></table>
		</form>

		</td>
		</tr>
		<?php
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
		<tr bgcolor="<?php echo $altbg2?>">
		<td align="center">
		<br />
		<form method="post" action="cp2.php?action=templates&tid=<?php echo $tid?>">
		<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
		<tr><td bgcolor="<?php echo $bordercolor?>">

		<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">

		<tr>
		<td class="header"><?php echo $lang_templates?></td>
		</tr>

		<?php
		$query = $db->query("SELECT * FROM $table_templates WHERE id='$tid' ORDER BY name");
		$template = $db->fetch_array($query);
		$template[template] = stripslashes($template[template]);
		$template[template] = htmlspecialchars($template[template]);
		?>

		<tr>
		<td bgcolor="<?php echo $altbg1?>" class="tablerow" align="center">
		<b><?php echo $template[name]?></b><br />
		<textarea cols="70" rows="20" name="templatenew"><?php echo $template[template]?></textarea>
		<center><input type="submit" name="editsubmit" value="<?php echo $lang_textsubmitchanges?>" /></center>
		</td>
		</tr>

		</table>
		</td></tr></table>
		</form>

		</td>
		</tr>

		<?php
	}

	if($editsubmit) {
		$templatenew = addslashes($templatenew);
		if($tid == "new") {
			if(empty($namenew)){
				echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">Template Name can not be empty!!</td></tr></table></td></tr></table>";

				end_time();
				eval("\$footer = \"".template("footer")."\";");
				echo $footer;

				exit();
			}else{
				$db->query("INSERT INTO $table_templates VALUES('', '$namenew', '$templatenew')");
			}
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
		<tr bgcolor="<?php echo $altbg2?>">
		<td align="center">
		<br />
		<form method="post" action="cp2.php?action=templates&tid=<?php echo $tid?>">
		<table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
		<tr><td bgcolor="<?php echo $bordercolor?>">

		<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">

		<tr>
		<td class="header"><?php echo $lang_templates?></td>
		</tr>
		<tr>
		<td bgcolor="<?php echo $altbg1?>" class="tablerow" align="center">
		<?php echo $lang_templatedelconfirm?>
		</td>
		</tr>

		</table>
		</td></tr></table>
		<center><input type="submit" name="deletesubmit" value="<?php echo $lang_textyes?>" /></center>
		</form>

		</td>
		</tr>

		<?php
	}

	if($deletesubmit) {
		$db->query("DELETE FROM $table_templates WHERE id='$tid'");
		echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang_templatesdelete</td></tr>";
	}

	if($new) {

		?>
		<tr bgcolor="<?php echo $altbg2?>">
		<td align="center">
		<br />
		<form method="post" action="cp2.php?action=templates&tid=new">
		<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
		<tr><td bgcolor="<?php echo $bordercolor?>">

		<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">

		<tr>
		<td class="header"><?php echo $lang_templates?></td>
		</tr>
		<tr>
		<td bgcolor="<?php echo $altbg1?>" class="tablerow" align="center">
		<?php echo $lang_templatename?> <input type="text" name="namenew" size="30" value="<?php echo $newtemplatename?>" /><br />
		<textarea cols="70" rows="20" name="templatenew"></textarea>
		<center><input type="submit" name="editsubmit" value="<?php echo $lang_textsubmitchanges?>" /></center>
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
		<tr bgcolor="<?php echo $altbg2?>">
		<td align="center">
		<br>
		<form method="post" action="cp2.php?action=attachments">
		<table cellspacing="0" cellpadding="0" border="0" width="550"
align="center">
		<tr><td bgcolor="<?php echo $bordercolor?>">

		<table border="0" cellspacing="<?php echo $borderwidth?>"
cellpadding="<?php echo $tablespace?>" width="100%">

		<tr>
		<td class="header" colspan="2"><?php echo $lang_textsearch?></td>
		</tr>
		<tr>
		<td class="tablerow"
bgcolor="<?php echo $altbg1?>"><?php echo $lang_attachmanwherename?></td>
		<td bgcolor="<?php echo $altbg2?>"><input type="text" name="filename" size="30"
/></td>
		</tr>
		<tr>
		<td class="tablerow"
bgcolor="<?php echo $altbg1?>"><?php echo $lang_attachmanwhereauthor?></td>
		<td bgcolor="<?php echo $altbg2?>"><input type="text" name="author" size="40"
/></td>
		</tr>
		<tr>
		<td class="tablerow"
bgcolor="<?php echo $altbg1?>"><?php echo $lang_attachmanwhereforum?></td>
		<td bgcolor="<?php echo $altbg2?>"><?php echo $forumselect?></td>
		</tr>
		<tr>
		<td class="tablerow"
bgcolor="<?php echo $altbg1?>"><?php echo $lang_attachmanwheresizesmaller?></td>
		<td bgcolor="<?php echo $altbg2?>"><input type="text" name="sizeless" size="20"
/></td>
		</tr>
		<tr>
		<td class="tablerow"
bgcolor="<?php echo $altbg1?>"><?php echo $lang_attachmanwheresizegreater?></td>
		<td bgcolor="<?php echo $altbg2?>"><input type="text" name="sizemore" size="20"
/></td>
		</tr>
		<tr>
		<td class="tablerow"
bgcolor="<?php echo $altbg1?>"><?php echo $lang_attachmanwheredlcountsmaller?></td>
		<td bgcolor="<?php echo $altbg2?>"><input type="text" name="dlcountless" size="20"
/></td>
		</tr>
		<tr>
		<td class="tablerow"
bgcolor="<?php echo $altbg1?>"><?php echo $lang_attachmanwheredlcountgreater?></td>
		<td bgcolor="<?php echo $altbg2?>"><input type="text" name="dlcountmore" size="20"
/></td>
		</tr>
		<tr>
		<td class="tablerow"
bgcolor="<?php echo $altbg1?>"><?php echo $lang_attachmanwheredaysold?></td>
		<td bgcolor="<?php echo $altbg2?>"><input type="text" name="daysold" size="20"
/></td>
		</tr>
		</table>
		</td></tr></table><br>
		<center><input type="submit" name="searchsubmit"
value="<?php echo $lang_textsubmitchanges?>" /></center>
		</form>

		</td>
		</tr>
		<?php
	}
	if($searchsubmit) {
		?>
		<tr bgcolor="<?php echo $altbg2?>">
		<td align="center">
		<br />
		<form method="post" action="cp2.php?action=attachments">
		<table cellspacing="0" cellpadding="0" border="0" width="93%"
align="center">
		<tr><td bgcolor="<?php echo $bordercolor?>">

		<table border="0" cellspacing="<?php echo $borderwidth?>"
cellpadding="<?php echo $tablespace?>" width="100%">

		<tr>
		<td class="header" colspan="6"><?php echo $lang_textattachsearchresults?></td>
		</tr>
		<tr>
		<td class="header" width="4%" align="center">?</td>
		<td class="header" width="25%"><?php echo $lang_textfilename?></td>
		<td class="header" width="29%"><?php echo $lang_textauthor?></td>
		<td class="header" width="27%"><?php echo $lang_textinthread?></td>
		<td class="header" width="10%"><?php echo $lang_textfilesize?></td>
		<td class="header" width="5%"><?php echo $lang_textdownloads?></td>


		</tr>
		<?php
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
			<td bgcolor="<?php echo $altbg1?>" class="tablerow" align="center" valign="middle"><a href="cp2.php?action=delete_attachment&amp;aid=<?php echo $attachment[aid]?>">Delete</a>
			<td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top"><input type="text" name="filename<?php echo $attachment[aid]?>" value="<?php echo $attachment[filename]?>"><br><small><a href="viewthread.php?action=attachment&tid=<?php echo $attachment[tid]?>&pid=<?php echo $attachment[pid]?>"><?php echo $lang_textdownload?></a></td>
			<td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top"><?php echo $attachment[author]?></td>
			<td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top"><a href="viewthread.php?tid=<?php echo $attachment[tid]?>"><?php echo $attachment[tsubject]?></a><br><small><?php echo $lang_textinforum?> <a href="forumdisplay.php?fid=<?php echo $attachment[fid]?>"><?php echo $attachment[fname]?></a></small></td>
			<td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top" align="center"><?php echo $attachsize?></td>
			<td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top" align="center"><?php echo $attachment[downloads]?></td>
			</tr>
			<?php

		}
		?>
		</table>
		</td></tr></table><br>
		<center><input type="submit" name="deletesubmit"
value="<?php echo $lang_textsubmitchanges?>" /></center>
		<input type="hidden" name="filename" value="<?php echo $filename?>">
		<input type="hidden" name="author" value="<?php echo $author?>">
		<input type="hidden" name="forumprune" value="<?php echo $forumprune?>">
		<input type="hidden" name="sizeless" value="<?php echo $sizeless?>">
		<input type="hidden" name="sizemore" value="<?php echo $sizemore?>">
		<input type="hidden" name="dlcountless" value="<?php echo $dlcountless?>">
		<input type="hidden" name="dlcountmore" value="<?php echo $dlcountmore?>">
		<input type="hidden" name="daysold" value="<?php echo $daysold?>">
		</form>

		</td>
		</tr>
		<?php
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
<tr bgcolor="<?php echo $altbg2?>">
<td align="center">
<br />
<table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
<tr><td bgcolor="<?php echo $bordercolor?>">

<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">

<tr class="header">
<td>Username:</td>
<td>Time:</td>
<td>URL:</td>
</tr>
<?php
$logcontents = file("./cplogfile.php");
$total = count($logcontents);

for($i = 1; $i < $total; $i++) {
$recordinfo = explode("|#||#|", $logcontents[$i]);
$date = date($dateformat, $recordinfo[2]);
$time = date($timeformat, $recordinfo[2]);
$url = "<a href=\"$recordinfo[3]\" target=\"_blank\">$recordinfo[3]</a>";
?>
<tr>
<td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $recordinfo[0]?><br><small>IP: <?php echo $recordinfo[1]?></small></td>
<td class="tablerow" bgcolor="<?php echo $altbg2?>"><?php echo $date?> at <?php echo $time?></td>
<td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $url?></td>
</tr>
<?php
}
?>
</table>
</td></tr></table>
</td>
</tr>
<?php
}


end_time();

if($action == "delete_attachment") {
	$db->query("DELETE FROM $table_attachments WHERE aid='$aid'");
	echo "<p align=\"center\">Deleted ...</br>";
}

eval("\$footer = \"".template("footer")."\";");
echo $footer;

function readFileAsINI($filename) {
// Function taken from a phpBB hack with permission
	$lines = file($filename);
	foreach($lines as $line_num => $line){
		$temp = explode("=",$line);
		if($temp[0] == 'dummy'){
			$key = 'dummy';
			$val = 'NULL';
		}else{
			$key = $temp[0];
			$val = $temp[1];
		}

		$key = trim($key);
		$val = trim($val);

		$thefile[$key] = $val;
	}
	return $thefile;
}

?>
