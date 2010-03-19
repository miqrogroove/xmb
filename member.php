<?
require "header.php";

if($action == "reg") {
$memberaction = "$lang[textregister]";
}
if($action == "list") {
$memberaction = "$lang[textmemberlist] ";
}
if($action == "viewpro") {
$memberaction = "$lang[textviewpro]";
}
if($action == "editpro") {
$memberaction = "$lang[texteditpro]";
}
if($action == "coppa") {
$memberaction = "$lang[textcoppa]";
}

$navigation = "<a href=\"index.php\">$lang[textindex]</a> &gt; $memberaction";
$html = template("header.html");
eval("echo stripslashes(\"$html\");");



if($action == "coppa") {
if($coppasubmit) {
?>
<script> location.href="member.php?action=reg";</script>
<?
} else {
?>
<form method="post" action="member.php?action=reg">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?=$lang[textcoppa]?></td>
</tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td><center><?=$lang[textcoppawording]?></center></td>
</tr>
<tr bgcolor="<?=$altbg2?>" class="tablerow">
<td>
<center><input type="submit" name="coppasubmit" value="<?=$lang[coppaagree]?>" /></center>
</td>
</tr>
</table>
</td></tr></table>
</form>

<?
}
}


if($action == "reg") {
if(($regstatus == "off" || $noreg == "on") && $status != "Administrator") {
echo "$lang[regoff]";
exit;
}

if(!$regsubmit) {

if($bbrules == "on" && !$rulesubmit) {
?>

<form method="post" action="member.php?action=reg">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr>
<td class="header"><?=$lang[textregister]?></td>
</tr>

<tr bgcolor="<?=$altbg1?>">
<td width="22%" class="tablerow"><? $bbrulestxt = nl2br($bbrulestxt); echo "$bbrulestxt"; ?></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="rulesubmit" value="<?=$lang[textagree]?>" /></center>
</form>
<?
}
else {

$currdate = gmdate("$timecode");
eval($lang[evaloffset]);

$themelist = "<select name=\"thememem\">\n";
$query = mysql_query("SELECT name FROM $table_themes") or die(mysql_error());
while($themeinfo = mysql_fetch_array($query)) {
if($theme == $themeinfo[name]) {
$themelist .= "<option value=\"$themeinfo[name]\" selected=\"selected\">$themeinfo[name]</option>\n";
} else {
$themelist .= "<option value=\"$themeinfo[name]\">$themeinfo[name]</option>\n";
}
}
$themelist  .= "</select>";


$langfileselect = "<select name=\"langfile\">\n";
$dir = opendir("lang");
while ($thafile = readdir($dir)) {
if (is_file("lang/$thafile")) {
$thafile = str_replace(".lang.php", "", $thafile);
if ($thafile == "$bblang") {
$langfileselect .= "<option value=\"$thafile\" selected=\"selected\">$thafile</option>\n";
}
else {
$langfileselect .= "<option value=\"$thafile\">$thafile</option>\n";
}
}
}
$langfileselect .= "</select>";


$dayselect = "<select name=\"day\">\n";
$dayselect .= "<option value=\"\">&nbsp;</option>\n";
for($num = 1; $num <= 31; $num++) {
$dayselect .= "<option value=\"$num\">$num</option>\n";
}
$dayselect .= "</select>";

if($sigbbcode == "on") {
$bbcodeis = $lang[texton];
} else {
$bbcodeis = $lang[textoff];
}

if($sightml == "on") {
$htmlis = $lang[texton];
} else {
$htmlis = $lang[textoff];
}
?>

<form method="post" action="member.php?action=reg">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr>
<td colspan="2" class="header"><?=$lang[textregister]?> - <?=$lang[required]?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" width="22%" class="tablerow" ><?=$lang[textusername]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="username" size="25" maxlength="25" /></td>
</tr>

<?
if($emailcheck == "on"){
$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
mt_srand((double)microtime() * 1000000);
for ($get = strlen($chars); $i < 8; ++$i)
$password .= $chars[mt_rand(0, $get)];
} else {
echo "<tr>
<td bgcolor=\"$altbg1\" class=\"tablerow\">$lang[textpassword]</td>
<td bgcolor=\"$altbg2\"class=\"tablerow\"><input type=\"password\" name=\"password\" size=\"25\" /></td>
</tr>";
}

if($emailcheck == "on"){
echo "<input type=\"hidden\" name=\"password\" value=\"$password\">";
echo "<input type=\"hidden\" name=\"password2\" value=\"$password\">";
} else {
echo"<tr>
<td bgcolor=\"$altbg1\" class=\"tablerow\">$lang[textretypepw]</td>
<td bgcolor=\"$altbg2\" class=\"tablerow\"><input type=\"password\" name=\"password2\" size=\"25\" /></td>
</tr>";
}
?>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textemail]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="email" size="25" /></td>
</tr>

<tr>
<td colspan="2" class="header"><?=$lang[textregister]?> - <?=$lang[optional]?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textsite]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="site" size="25" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textaim]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="aim" size="25" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[texticq]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="icq" size="25" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textyahoo]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="yahoo" size="25" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textmsn]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="msn" size="25" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textlocation]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="locationnew" size="25" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textbday]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><select name="month">
<option value="">&nbsp;</option>
<option value="<?=$lang[textjan]?>"><?=$lang[textjan]?></option>
<option value="<?=$lang[textfeb]?>"><?=$lang[textfeb]?></option>
<option value="<?=$lang[textmar]?>"><?=$lang[textmar]?></option>
<option value="<?=$lang[textapr]?>"><?=$lang[textapr]?></option>
<option value="<?=$lang[textmay]?>"><?=$lang[textmay]?></option>
<option value="<?=$lang[textjun]?>"><?=$lang[textjun]?></option>
<option value="<?=$lang[textjul]?>"><?=$lang[textjul]?></option>
<option value="<?=$lang[textaug]?>"><?=$lang[textaug]?></option>
<option value="<?=$lang[textsep]?>"><?=$lang[textsep]?></option>
<option value="<?=$lang[textoct]?>"><?=$lang[textoct]?></option>
<option value="<?=$lang[textnov]?>"><?=$lang[textnov]?></option>
<option value="<?=$lang[textdec]?>"><?=$lang[textdec]?></option>
</select>
<?=$dayselect?>
<input type="text" name="year" size="4" />
</td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textbio]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><textarea rows="5" cols="30" name="bio"></textarea></td>
</tr>

<tr>
<td colspan="2" class="header"><?=$lang[textregister]?> - <?=$lang[textoptions]?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[texttheme]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$themelist?> </td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textlanguage]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$langfileselect?> </td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[texttpp]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="tpp" value="<?=$topicperpage?>" size="4" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textppp]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="ppp" value="<?=$postperpage?>" size="4" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[texttimeformat]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="radio" value="24" name="timeformatnew"><?=$lang[text24hour]?> <input type="radio" value="12" name="timeformatnew" checked="checked"><?=$lang[text12hour]?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[dateformat]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="dateformatnew" size="25" value="<?=$dformatorig?>" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textoptions]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow">
<input type="checkbox" name="showemail" value="yes" checked="checked" /> <?=$lang[textshowemail]?><br />
<input type="checkbox" name="newsletter" value="yes" checked="checked" /> <?=$lang[textgetnews]?><br />
<select name="timeoffset1" size="1">
<option value="-12">-12:00</option>
<option value="-11">-11:00</option>
<option value="-10">-10:00</option>
<option value="-9">-9:00</option>
<option value="-8">-8:00</option>
<option value="-7">-7:00</option>
<option value="-6">-6:00</option>
<option value="-5">-5:00</option>
<option value="-4">-4:00</option>
<option value="-3.5">-3:30</option>
<option value="-3">-3:00</option>
<option value="-2">-2:00</option>
<option value="-1">-1:00</option>
<option value="0" selected>0</option>
<option value="1">+1:00</option>
<option value="2">+2:00</option>
<option value="3">+3:00</option>
<option value="3.5">+3:30</option>
<option value="4">+4:00</option>
<option value="4.5">+4:30</option>
<option value="5">+5:00</option>
<option value="5.5">+5:30</option>
<option value="6">+6:00</option>
<option value="7">+7:00</option>
<option value="8">+8:00</option>
<option value="9">+9:00</option>
<option value="9.5">+9:30</option>
<option value="10">+10:00</option>
<option value="11">+11:00</option>
<option value="12">+12:00</option>
</select> <?=$lang[textoffset]?><br /></td>
</tr>

<?
if($avastatus == "on") {
?>
<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textavatar]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="avatar" size="25" /></td>
</tr>
<?
}
?>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textsig]?><br /><span class="11px">
<?=$lang[texthtmlis]?> <?=$htmlis?><br />
<?=$lang[textbbcodeis]?> <?=$bbcodeis?></span></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><textarea rows="4" cols="30" name="sig"></textarea></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="regsubmit" value="<?=$lang[textregister]?>" /></center>
</form>

<?
}
}

if($regsubmit) {
$username = trim($username);
$query = mysql_query("SELECT username FROM $table_members WHERE username='$username'") or die(mysql_error());

if($member = mysql_fetch_array($query)) {
echo "<span class=\"12px \">$lang[alreadyreg]</span>";
exit;
}

if($password != $password2) {
echo "<span class=\"12px \">$lang[pwnomatch]</span>";
exit;
}

if($username == "" || $username == "xguest123" || $username == "onlinerecord" || $username == $lang[textguest] || ereg("'", $username) || ereg('"', $username) || ereg("<", $username) || ereg(",", $username)) {
echo "<span class=\"12px \">$lang[badname]</span>";
exit;
}

if($password == "" || ereg('"', $password)|| ereg("'", $password)) {
echo "<span class=\"12px \">$lang[textpwincorrect]</span>";
exit;
}

$query = mysql_query("SELECT COUNT(uid) FROM $table_members") or die(mysql_error());
$count1 = mysql_result($query,0);

if($count1 != "0") {
$query = mysql_query("SELECT uid FROM $table_members ORDER BY uid DESC") or die(mysql_error());
$count = mysql_result($query,0);
$status = "Member";
}
else {
$count = $count1;
$status = "Administrator";
}

if($showemail != "yes") {
$showemail = "no";
}

if($newsletter != "yes") {
$newsletter = "no";
}

$bday = "$month $day, $year";

if($month == "" || $day == "" || $year == "") {
$bday = "";
}

$avatar = str_replace("<","&lt;", $avatar);
$avatar = str_replace(">","&gt;", $avatar);
$locationnew = str_replace("<","&lt;", $locationnew);
$locationnew= str_replace(">","&gt;", $locationnew);
$icq = str_replace("<","&lt;", $icq);
$icq = str_replace(">","&gt;", $icq);
$yahoo = str_replace("<","&lt;", $yahoo);
$yahoo = str_replace(">","&gt;", $yahoo);
$aim = str_replace("<","&lt;", $aim);
$aim = str_replace(">","&gt;", $aim);
$email = str_replace("<","&lt;", $email);
$email = str_replace(">","&gt;", $email);
$site = str_replace("<","&lt;", $site);
$site = str_replace(">","&gt;", $site);
$bio = str_replace("<","&lt;", $bio);
$bio = str_replace(">","&gt;", $bio);
$bday = str_replace("<","&lt;", $bday);
$bday = str_replace(">","&gt;", $bday);

mysql_query("INSERT INTO $table_members VALUES ('', '$username', '$password', '" . time() . "', '0', '$email', '$site', '$aim', '$status', '$locationnew', '$bio', '$sig', '$showemail', '$timeoffset1', '$icq', '$avatar', '$yahoo', '', '$thememem', '$bday', '$langfile', '$tpp', '$ppp', '$newsletter', '$onlineip', '$timeformatnew', '$msn', '$dateformatnew', '', '')") or die(mysql_error());

if($emailcheck == "on"){
mail("$email", "$lang[textyourpw]", "$lang[textyourpwis] \n\n$username\n$password", "$lang[textfrom] $bbname");
echo "<span class=\"12px \">$lang[emailpw]</span>";
}
echo "<span class=\"12px \">$lang[regged]</span>";

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


if($action == "viewpro") {

if(!$member) {
echo $lang[nomember];
}
else {
$query = mysql_query("SELECT * FROM $table_members WHERE username='$member'") or die(mysql_error());
$memberinfo = mysql_fetch_array($query);

$daysreg = (time() - $memberinfo[regdate]) / (24*60*60);
$ppd = $memberinfo[postnum] / $daysreg;
$ppd = round($ppd, 2);

$memberinfo[regdate] = gmdate("n/j/y",$memberinfo[regdate]);

$memberinfo[site] = str_replace("http://", "", $memberinfo[site]);
$memberinfo[site] = "http://$memberinfo[site]";

if($memberinfo[site] != "http://") {
$site = "$memberinfo[site]";
}

if($memberinfo[email] != "" && $memberinfo[showemail] == "yes") {
$email = $memberinfo[email];
}

$query = mysql_query("SELECT * FROM $table_whosonline WHERE username='$member'");
$onlineinfo = mysql_fetch_array($query);
if ($onlineinfo[username] == $member) {
$onlinestatus = "$lang[textonline]";
} else {
$onlinestatus = "$lang[textoffline]";
}

$lastvisitdate = gmdate("$dateformat",$memberinfo[lastvisit] + ($timeoffset * 3600));
$lastvisittime = gmdate("$timecode",$memberinfo[lastvisit] + ($timeoffset * 3600));
$lastmembervisittext = "$lastvisitdate $lang[textat] $lastvisittime";


$query = mysql_query("SELECT COUNT(pid) FROM $table_posts") or die(mysql_error());
$posts = mysql_result($query, 0);

$query = mysql_query("SELECT COUNT(tid) FROM $table_threads") or die(mysql_error());
$threads = mysql_result($query, 0);

$posttot = $threads+$posts;
if($posttot == 0) {
$percent = "0";
} else {
$percent = $memberinfo[postnum]*100/$posttot;
$percent = round($percent, 2);
}

$memberinfo[bio] = nl2br($memberinfo[bio]);
$encodeuser = rawurlencode($memberinfo[username]);


$query = mysql_query("SELECT dateline, tid FROM $table_posts WHERE author='$memberinfo[username]' ORDER BY dateline DESC LIMIT 0, 1") or die(mysql_error());
$lastrep = mysql_fetch_array($query);

$query = mysql_query("SELECT dateline, subject, tid FROM $table_threads WHERE author='$memberinfo[username]' ORDER BY dateline DESC LIMIT 0, 1") or die(mysql_error());
$lasttop = mysql_fetch_array($query);

if($lastrep[dateline] > $lasttop[dateline]) {
$ltoptime = $lastrep[dateline];
$query = mysql_query("SELECT subject FROM $table_threads WHERE tid='$lastrep[tid]'") or die(mysql_error());
$ltop = mysql_fetch_array($query);
$lasttopsub = $ltop[subject];
$lttid = $lastrep[tid];
} else {
$ltoptime = $lasttop[dateline];
$lasttopsub = $lasttop[subject];
$lttid = $lasttop[tid];
}

$lasttopdate = gmdate("$dateformat", $ltoptime + ($timeoffset * 3600));
$lasttoptime = gmdate("$timecode", $ltoptime + ($timeoffset * 3600));
$lasttopic = "<a href=\"viewthread.php?tid=$lttid\">$lasttopsub</a> $lang[lastreply1] $lasttopdate $lang[textat] $lasttoptime";
?>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr>
<td colspan="2" class="header"><?=$lang[textprofor]?> <?=$member?></td>
</tr>

<tr><td bgcolor="<?=$altbg1?>" width="22%" class="tablerow"><?=$lang[textusername]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$memberinfo[username]?>&nbsp; <small>(<a href="#" onclick="Popup('misc2.php?action1=u2u&action=send&username=<?=$encodeuser?>', 'Window', 550, 450);"><?=$lang[textu2u]?></a>)</small></td></tr>

<tr><td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textregistered]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$memberinfo[regdate]?> (<?=$ppd?> <?=$lang[textmesperday]?>)</td></tr>

<tr><td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textposts]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$memberinfo[postnum]?> (<?=$percent?>% <?=$lang[textoftotposts]?>.)</td></tr>

<tr><td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textstatus]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$memberinfo[status]?></td></tr>

<tr><td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[onstatus]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$onlinestatus?></td></tr>

<? if($memberinfo[lastvisit] != "") {?>
<tr><td bgcolor="<?=$altbg1?>" valign="top" class="tablerow"><?=$lang[lastactive]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$lastmembervisittext?></td></tr>
<? } ?>

<? if($memberinfo[postnum] != "0") {?>
<tr><td bgcolor="<?=$altbg1?>" valign="top" class="tablerow"><?=$lang[lastpostin]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$lasttopic?></td></tr>
<? } ?>


<? if($memberinfo[email] != "" && $memberinfo[showemail] == "yes") { ?>
<tr><td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textemail]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><a href="mailto:<?=$email?>"><?=$email?></a></td></tr>
<? } ?>

<? if($memberinfo[site] != "http://") { ?>
<tr><td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textsite]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><a href="<?=$site?>"><?=$site?></a></td></tr>
<? } ?>

<? if($memberinfo[aim] != "") { ?>
<tr><td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textaim]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$memberinfo[aim]?></td></tr>
<? } ?>

<? if($memberinfo[icq] != "") { ?>
<tr><td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[texticq]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$memberinfo[icq]?></td></tr>
<? } ?>

<? if($memberinfo[yahoo] != "") { ?>
<tr><td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textyahoo]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$memberinfo[yahoo]?></td></tr>
<? } ?>

<? if($memberinfo[msn] != "") { ?>
<tr><td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textmsn]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$memberinfo[msn]?></td></tr>
<? } ?>

<? if($memberinfo[location] != "") { ?>
<tr><td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textlocation]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$memberinfo[location]?></td></tr>
<? } ?>

<? if($memberinfo[bday] != "") { ?>
<tr><td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textbday]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$memberinfo[bday]?></td></tr>
<? } ?>

<? if($memberinfo[bio] != "") { ?>
<tr><td bgcolor="<?=$altbg1?>" valign="top" class="tablerow"><?=$lang[textbio]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$memberinfo[bio]?></td></tr>
<? } ?>

<tr>
<td bgcolor="<?=$altbg1?>" colspan="2" class="tablerow"><?=$lang[searchusermsg]?></td>
</tr>

<?
}
}

if($action == "editpro") {

if(!$editlogsubmit) {
?>

<form method="post" action="member.php?action=editpro">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr>
<td colspan="2" class="header"><?=$lang[texteditpro]?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" width="22%" class="tablerow"><?=$lang[textusername]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="username" size="30" maxlength="40" value="<?=$thisuser?>" /> &nbsp;<span class="11px"><a href="member.php?action=reg"><?=$lang[regques]?></a></span></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textpassword]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="password" name="password" size="25" value="<?=$thispw?>" /> &nbsp;<span class="11px"><a href="misc.php?action=lostpw"><?=$lang[forgotpw]?></a></span></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="editlogsubmit" value="<?=$lang[texteditpro]?>" /></center>
</form>

<?
}

if($editlogsubmit && $editlogsubmit != 1) {
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

if($member[showemail] == "yes") {
$checked = "checked=\"checked\"";
}

if($member[newsletter] == "yes") {
$newschecked = "checked=\"checked\"";
}

$currdate = gmdate("$timecode");
eval($lang[evaloffset]);

if($member[timeoffset] == "-12") {
$sn12 = "selected=\"selected\"";
} elseif($member[timeoffset] == "-11") {
$sn11 = "selected=\"selected\"";
} elseif($member[timeoffset] == "-10") {
$sn10 = "selected=\"selected\"";
} elseif($member[timeoffset] == "-9") {
$sn9 = "selected=\"selected\"";
} elseif($member[timeoffset] == "-8") {
$sn8 = "selected=\"selected\"";
} elseif($member[timeoffset] == "-7") {
$sn7 = "selected=\"selected\"";
} elseif($member[timeoffset] == "-6") {
$sn6 = "selected=\"selected\"";
} elseif($member[timeoffset] == "-5") {
$sn5 = "selected=\"selected\"";
} elseif($member[timeoffset] == "-4") {
$sn8 = "selected=\"selected\"";
} elseif($member[timeoffset] == "-3.5") {
$sn35 = "selected=\"selected\"";
} elseif($member[timeoffset] == "-3") {
$sn3 = "selected=\"selected\"";
} elseif($member[timeoffset] == "-2") {
$sn2 = "selected=\"selected\"";
} elseif($member[timeoffset] == "-1") {
$sn1 = "selected=\"selected\"";
} elseif($member[timeoffset] == "0") {
$s0 = "selected=\"selected\"";
} elseif($member[timeoffset] == "1") {
$sp1 = "selected=\"selected\"";
} elseif($member[timeoffset] == "2") {
$sp2 = "selected=\"selected\"";
} elseif($member[timeoffset] == "3") {
$sp3 = "selected=\"selected\"";
} elseif($member[timeoffset] == "3.5") {
$sp35 = "selected=\"selected\"";
} elseif($member[timeoffset] == "4") {
$sp4 = "selected=\"selected\"";
} elseif($member[timeoffset] == "4.5") {
$sp45 = "selected=\"selected\"";
} elseif($member[timeoffset] == "5") {
$sp5 = "selected=\"selected\"";
} elseif($member[timeoffset] == "5.5") {
$sp55 = "selected=\"selected\"";
} elseif($member[timeoffset] == "6") {
$sp6 = "selected=\"selected\"";
} elseif($member[timeoffset] == "7") {
$sp7 = "selected=\"selected\"";
} elseif($member[timeoffset] == "8") {
$sp8 = "selected=\"selected\"";
} elseif($member[timeoffset] == "9") {
$sp9 = "selected=\"selected\"";
} elseif($member[timeoffset] == "9.5") {
$sp95 = "selected=\"selected\"";
} elseif($member[timeoffset] == "10") {
$sp10 = "selected=\"selected\"";
} elseif($member[timeoffset] == "11") {
$sp11 = "selected=\"selected\"";
} elseif($member[timeoffset] == "12") {
$sp12 = "selected=\"selected\"";
}

$themelist = "<select name=\"thememem\">\n";
$query = mysql_query("SELECT name FROM $table_themes") or die(mysql_error());
while($theme = mysql_fetch_array($query)) {
if($theme[name] == $member[theme]) {
$themelist .= "<option value=\"$theme[name]\" selected=\"selected\">$theme[name]</option>\n";
}
else {
$themelist .= "<option value=\"$theme[name]\">$theme[name]</option>\n";
}
}
$themelist  .= "</select>";


$langfileselect = "<select name=\"langfilenew\">\n";
$dir = opendir("lang");
while ($thafile = readdir($dir)) {
if(is_file("lang/$thafile")) {
$thafile = str_replace(".lang.php", "", $thafile);
if($thafile == "$member[langfile]") {
$langfileselect .= "<option value=\"$thafile\" selected=\"selected\">$thafile</option>\n";
}
else {
$langfileselect .= "<option value=\"$thafile\">$thafile</option>\n";
}
}
}

$langfileselect .= "</select>";


$member[bday] = str_replace(",", "", $member[bday]);
$bday = explode(" ", $member[bday]);

if($bday[0] == "") {
$sel0 = "selected=\"selected\"";
} elseif($bday[0] == $lang[textjan]) {
$sel1 = "selected=\"selected\"";
} elseif($bday[0] == $lang[textfeb]) {
$sel2 = "selected=\"selected\"";
} elseif($bday[0] == $lang[textmar]) {
$sel3 = "selected=\"selected\"";
} elseif($bday[0] == $lang[textapr]) {
$sel4 = "selected=\"selected\"";
} elseif($bday[0] == $lang[textmay]) {
$sel5 = "selected=\"selected\"";
} elseif($bday[0] == $lang[textjun]) {
$sel6 = "selected=\"selected\"";
} elseif($bday[0] == $lang[textjul]) {
$sel7 = "selected=\"selected\"";
} elseif($bday[0] == $lang[textaug]) {
$sel8 = "selected=\"selected\"";
} elseif($bday[0] == $lang[textsep]) {
$sel9 = "selected=\"selected\"";
} elseif($bday[0] == $lang[textoct]) {
$sel10 = "selected=\"selected\"";
} elseif($bday[0] == $lang[textnov]) {
$sel11 = "selected=\"selected\"";
} elseif($bday[0] == $lang[textdec]) {
$sel12 = "selected=\"selected\"";
}

$dayselect = "<select name=\"day\">\n";
$dayselect .= "<option value=\"\">&nbsp;</option>\n";
for($num = 1; $num <= 31; $num++) {
if($bday[1] == $num) {
$dayselect .= "<option value=\"$num\" selected=\"selected\">$num</option>\n";
}
else {
$dayselect .= "<option value=\"$num\">$num</option>\n";
}
}
$dayselect .= "</select>";

if($member[timeformat] == "24") {
$check24 = "checked=\"checked\"";
}
else {
$check12 = "checked=\"checked\"";
}

if($sigbbcode == "on") {
$bbcodeis = $lang[texton];
} else {
$bbcodeis = $lang[textoff];
}

if($sightml == "on") {
$htmlis = $lang[texton];
} else {
$htmlis = $lang[textoff];
}
?>

<form method="post" action="member.php?action=editpro&editlogsubmit=1" name="reg">
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr>
<td colspan="2" class="header"><?=$lang[textregister]?> - <?=$lang[required]?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" colspan="2" align="center" class="tablerow"><?=$lang[editpromsg]?><br /><a href="#" onclick="Popup('misc2.php?action1=u2u', 'Window', 400, 400);"><?=$lang[textu2u]?></a></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textpassword]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="password" name="newpassword" size="25" /> <?=$lang[pwnote]?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" width="22%" class="tablerow"><?=$lang[textemail]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="email" size="30" value="<?=$member[email]?>" /></td>
</tr>

<tr>
<td colspan="2" class="header"><?=$lang[textregister]?> - <?=$lang[optional]?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" width="22%" class="tablerow"><?=$lang[textsite]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="site" size="30" value="<?=$member[site]?>" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" width="22%" class="tablerow"><?=$lang[textaim]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="aim" size="30" value="<?=$member[aim]?>" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" width="22%" class="tablerow"><?=$lang[texticq]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="icq" size="30" value="<?=$member[icq]?>" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" width="22%" class="tablerow"><?=$lang[textyahoo]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="yahoo" size="30" value="<?=$member[yahoo]?>" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" width="22%" class="tablerow"><?=$lang[textmsn]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="msn" size="30" value="<?=$member[msn]?>"/></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" width="22%" class="tablerow"><?=$lang[textlocation]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="memlocation" size="30" value="<?=$member[location]?>" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textbday]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><select name="month">
<option value="" <?=$sel0?>>&nbsp;</option>
<option value="<?=$lang[textjan]?>" <?=$sel1?>><?=$lang[textjan]?></option>
<option value="<?=$lang[textfeb]?>" <?=$sel2?>><?=$lang[textfeb]?></option>
<option value="<?=$lang[textmar]?>" <?=$sel3?>><?=$lang[textmar]?></option>
<option value="<?=$lang[textapr]?>" <?=$sel4?>><?=$lang[textapr]?></option>
<option value="<?=$lang[textmay]?>" <?=$sel5?>><?=$lang[textmay]?></option>
<option value="<?=$lang[textjun]?>" <?=$sel6?>><?=$lang[textjun]?></option>
<option value="<?=$lang[textjul]?>" <?=$sel7?>><?=$lang[textjul]?></option>
<option value="<?=$lang[textaug]?>" <?=$sel8?>><?=$lang[textaug]?></option>
<option value="<?=$lang[textsep]?>" <?=$sel9?>><?=$lang[textsep]?></option>
<option value="<?=$lang[textoct]?>" <?=$sel10?>><?=$lang[textoct]?></option>
<option value="<?=$lang[textnov]?>" <?=$sel11?>><?=$lang[textnov]?></option>
<option value="<?=$lang[textdec]?>" <?=$sel12?>><?=$lang[textdec]?></option>
</select>
<?=$dayselect?>
<input type="text" name="year" size="4" value="<?=$bday[2]?>" />
</td>
</tr>


<tr>
<td bgcolor="<?=$altbg1?>" width="22%" class="tablerow"><?=$lang[textbio]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><textarea rows="5" cols="30" name="bio"><?=$member[bio]?></textarea></td>
</tr>

<tr>
<td colspan="2" class="header"><?=$lang[textregister]?> - <?=$lang[textoptions]?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[texttheme]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$themelist?> </td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textlanguage]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><?=$langfileselect?> </td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[texttpp]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="tppnew" size="4" value="<?=$member[tpp]?>" /> </td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textppp]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="pppnew" size="4" value="<?=$member[ppp]?>" /> </td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[texttimeformat]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="radio" value="24" name="timeformatnew" <?=$check24?>><?=$lang[text24hour]?>
<input type="radio" value="12" name="timeformatnew" <?=$check12?>><?=$lang[text12hour]?></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[dateformat]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="dateformatnew" size="30" value="<?=$member[dateformat]?>" /></td>
</tr>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textoptions]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow">
<input type="checkbox" name="showemail" value="yes" <?=$checked?> /> <?=$lang[textshowemail]?><br />
<input type="checkbox" name="newsletter" value="yes" <?=$newschecked?> /> <?=$lang[textgetnews]?><br />
<select name="timeoffset1" size="1">
<option value="-12" <?=$sn12?>>-12:00</option>
<option value="-11" <?=$sn11?>>-11:00</option>
<option value="-10" <?=$sn10?>>-10:00</option>
<option value="-9" <?=$sn9?>>-9:00</option>
<option value="-8" <?=$sn8?>>-8:00</option>
<option value="-7" <?=$sn7?>>-7:00</option>
<option value="-6" <?=$sn6?>>-6:00</option>
<option value="-5" <?=$sn5?>>-5:00</option>
<option value="-4" <?=$sn4?>>-4:00</option>
<option value="-3.5" <?=$sn35?>>-3:30</option>
<option value="-3" <?=$sn3?>>-3:00</option>
<option value="-2" <?=$sn2?>>-2:00</option>
<option value="-1" <?=$sn1?>>-1:00</option>
<option value="0" <?=$s0?>>0</option>
<option value="1" <?=$sp1?>>+1:00</option>
<option value="2" <?=$sp2?>>+2:00</option>
<option value="3" <?=$sp3?>>+3:00</option>
<option value="3.5" <?=$sp35?>>+3:30</option>
<option value="4" <?=$sp4?>>+4:00</option>
<option value="4.5" <?=$sp45?>>+4:30</option>
<option value="5" <?=$sp5?>>+5:00</option>
<option value="5.5" <?=$sp55?>>+5:30</option>
<option value="6" <?=$sp6?>>+6:00</option>
<option value="7" <?=$sp7?>>+7:00</option>
<option value="8" <?=$sp8?>>+8:00</option>
<option value="9" <?=$sp9?>>+9:00</option>
<option value="9.5" <?=$sp95?>>+9:30</option>
<option value="10" <?=$sp10?>>+10:00</option>
<option value="11" <?=$sp11?>>+11:00</option>
<option value="12" <?=$sp12?>>+12:00</option>
</select> <?=$lang[textoffset]?>
</td></tr>

<?
if($avastatus == "on") {
?>

<tr>
<td bgcolor="<?=$altbg1?>" class="tablerow"><?=$lang[textavatar]?></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><input type="text" name="avatar" size="30" value="<?=$member[avatar]?>" /></td>
</tr>

<?
}
?>

<tr>
<td bgcolor="<?=$altbg1?>" width="22%" class="tablerow"><?=$lang[textsig]?><br /><span class="11px">
<?=$lang[texthtmlis]?> <?=$htmlis?><br />
<?=$lang[textbbcodeis]?> <?=$bbcodeis?></span></td>
<td bgcolor="<?=$altbg2?>" class="tablerow"><textarea rows="4" cols="30" name="sig"><?=$member[sig]?></textarea></td>
</tr>

</table>
</td></tr></table>
<center><input type="submit" name="editsubmit" value="<?=$lang[texteditpro]?>" /></center>
<input type="hidden" name="username" value="<?=$username?>">
<input type="hidden" name="password" value="<?=$password?>">
</form>

<?
}

if($editsubmit) {
$query = mysql_query("SELECT * FROM $table_members WHERE username='$username'") or die(mysql_error());
$member = mysql_fetch_array($query);

if(!$member[username]) {
echo "<span class=\"12px \">$lang[badname]</span>";
exit;
}

if($password != $member[password]) {
echo "<span class=\"12px \">$lang[textpwincorrect]</span>";
exit;
}

if($showemail != "yes") {
$showemail = "no";
}

if($newsletter != "yes") {
$newsletter = "no";
}

$bday = "$month $day, $year";

if($month == "" || $day == "" || $year == "") {
$bday = "";
}

$avatar = str_replace("<","&lt;", $avatar);
$avatar = str_replace(">","&gt;", $avatar);
$memlocation = str_replace("<","&lt;", $memlocation);
$memlocation = str_replace(">","&gt;", $memlocation);
$icq = str_replace("<","&lt;", $icq);
$icq = str_replace(">","&gt;", $icq);
$yahoo = str_replace("<","&lt;", $yahoo);
$yahoo = str_replace(">","&gt;", $yahoo);
$aim = str_replace("<","&lt;", $aim);
$aim = str_replace(">","&gt;", $aim);
$email = str_replace("<","&lt;", $email);
$email = str_replace(">","&gt;", $email);
$site = str_replace("<","&lt;", $site);
$site = str_replace(">","&gt;", $site);
$bio = str_replace("<","&lt;", $bio);
$bio = str_replace(">","&gt;", $bio);
$bday = str_replace("<","&lt;", $bday);
$bday = str_replace(">","&gt;", $bday);

mysql_query("UPDATE $table_members SET email='$email', site='$site', aim='$aim', location='$memlocation', bio='$bio', sig='$sig', showemail='$showemail', timeoffset='$timeoffset1', icq='$icq', avatar='$avatar', yahoo='$yahoo', theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew', newsletter='$newsletter', timeformat='$timeformatnew', msn='$msn', dateformat='$dateformatnew' WHERE username='$username'") or die(mysql_error());

if($newpassword != "") {
if(ereg('"', $newpassword) || ereg("'", $newpassword)) {
echo "$lang[textpwincorrect]";
exit;
}
mysql_query("UPDATE $table_members SET password='$newpassword' WHERE username='$username'") or die(mysql_error());
}

echo "<span class=\"12px \">$lang[editedpro]</span>";
?>
<!-- <script>
function redirect()
{
window.location.replace("index.php");
}
setTimeout("redirect();", 1250);
</script> -->
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
