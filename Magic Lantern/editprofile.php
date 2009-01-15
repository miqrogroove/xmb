<?
/*

XMB 1.6 v2c Magic Lantern
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/
require "./header.php";
require "./xmb.php";
$navigation = "&raquo; $lang_textcp";
eval("\$header = \"".template("header")."\";");
echo $header;
if(!$xmbuser || !$xmbpw) {
	$user = "";
	$xmbpw = "";
	$status = "";
}

if($status != "Administrator") {
	echo "$lang_notadmin";
	exit;
}

if(!$editsubmit) {
$query = $db->query("SELECT * FROM $table_members WHERE username='$user'");
$member = $db->fetch_array($query);

if($member[showemail] == "yes") {
$checked = "checked=\"checked\"";
}

if($member[newsletter] == "yes") {
$newschecked = "checked=\"checked\"";
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
echo "<center><span class=\"mediumtxt \">$lang_editprofile_postsdeleted<br><a href=cp.php><b>$lang_editprofile_backtocp</b></a></span></center>";
eval("\$footer = \"".template("footer")."\";");
echo $footer;
exit;
}

$registerdate = date("D M j G:i:s T Y",$member[regdate]);
$lastlogdate = date("D M j G:i:s T Y",$member[lastvisit]);

$currdate = gmdate("$timecode");
eval($lang_evaloffset);

$themelist = "<select name=\"thememem\">\n<option value=\"\">$lang_textusedefault</option>";
$query = $db->query("SELECT name FROM $table_themes");
while($theme = $db->fetch_array($query)) {
if($theme[name] == $member[theme]) {
$themelist .= "<option value=\"$theme[name]\" selected=\"selected\">$theme[name]</option>\n";
}
else {
$themelist .= "<option value=\"$theme[name]\">$theme[name]</option>\n";
}
}
$themelist .= "</select>";

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
} elseif($bday[0] == $lang_textjan) {
$sel1 = "selected=\"selected\"";
} elseif($bday[0] == $lang_textfeb) {
$sel2 = "selected=\"selected\"";
} elseif($bday[0] == $lang_textmar) {
$sel3 = "selected=\"selected\"";
} elseif($bday[0] == $lang_textapr) {
$sel4 = "selected=\"selected\"";
} elseif($bday[0] == $lang_textmay) {
$sel5 = "selected=\"selected\"";
} elseif($bday[0] == $lang_textjun) {
$sel6 = "selected=\"selected\"";
} elseif($bday[0] == $lang_textjul) {
$sel7 = "selected=\"selected\"";
} elseif($bday[0] == $lang_textaug) {
$sel8 = "selected=\"selected\"";
} elseif($bday[0] == $lang_textsep) {
$sel9 = "selected=\"selected\"";
} elseif($bday[0] == $lang_textoct) {
$sel10 = "selected=\"selected\"";
} elseif($bday[0] == $lang_textnov) {
$sel11 = "selected=\"selected\"";
} elseif($bday[0] == $lang_textdec) {
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
} else {
$check12 = "checked=\"checked\"";
}

if($sigbbcode == "on") {
$bbcodeis = $lang_texton;
} else {
$bbcodeis = $lang_textoff;
}

if($sightml == "on") {
$htmlis = $lang_texton;
} else {
$htmlis = $lang_textoff;
}

$member[bio] = stripslashes($member[bio]);
$member[sig] = stripslashes($member[sig]);


if($avastatus == "on") {
eval("\$avatar = \"".template("memcp_profile_avatarurl")."\";");
}

if($avastatus == "list") {
$avatars = " <option value=\"\" />$lang_textnone</option>  ";
$dir1 = opendir("images/avatars");
while ($avatar1 = readdir($dir1)) {
if (is_file("images/avatars/$avatar1")) {
$avatars .= " <option value=\"images/avatars/$avatar1\" />$avatar1</option>  ";
}
}
$avatars = str_replace("value=\"$member[avatar]\"", "value=\"$member[avatar]\" SELECTED", $avatars);
$avatarbox = "<select name=\"avatar\">$avatars</select>";
eval("\$avatar = \"".template("memcp_profile_avatarlist")."\";");
closedir($dir1);
}

eval("\$profile = \"".template("admintool_editprofile")."\";");
echo $profile;
}

if($editsubmit) {
$query = $db->query("SELECT * FROM $table_members WHERE username='$user'");
$member = $db->fetch_array($query);

if(!$member[username]) {
echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
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

$bio = addslashes($bio);

$db->query("UPDATE $table_members SET customstatus='$cstatus', email='$email', site='$site', aim='$aim', location='$memlocation', bio='$bio', sig='$sig', showemail='$showemail', timeoffset='$timeoffset1', icq='$icq', avatar='$avatar', yahoo='$yahoo', theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew', newsletter='$newsletter', timeformat='$timeformatnew', msn='$msn', dateformat='$dateformatnew', mood='$mood' WHERE username='$user'");

if($newpassword != "") {
if(ereg('"', $newpassword) || ereg("'", $newpassword)) {
echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span><center>";
exit;
}
$newpassword = md5($newpassword);
$db->query("UPDATE $table_members SET password='$newpassword' WHERE username='$user'");
}

echo "<center><span class=\"mediumtxt \">$lang_adminprofilechange</span></center>";
?>
<script>
function redirect()
{
window.location.replace("cp.php");
}
setTimeout("redirect();", 1250);
</script>
<?
}

$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>