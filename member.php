<?
require "./header.php";
loadtemplates('header,footer,member_coppa,member_reg_rules,member_reg_password,member_reg_avatarurl,member_reg_avatarlist,member_reg,member_profile_email,member_profile');
if($action == "reg") {
$memberaction = "$lang_textregister";
}
if($action == "viewpro") {
$memberaction = "$lang_textviewpro";
}
if($action == "coppa") {
$memberaction = "$lang_textcoppa";
}

$navigation = "&gt; $memberaction";
if($action == "coppa") {
eval("\$header = \"".template("header")."\";");
echo $header;
if($coppasubmit) {
?>
<script> location.href="member.php?action=reg";</script>
<?
} else {
$page = template("member_coppa");
}
}


if($action == "reg") {
if($regstatus == "off" && $status != "Administrator") {
eval("\$header = \"".template("header")."\";");
echo $header;
echo "$lang_regoff";
exit;
}

if(!$regsubmit) {
eval("\$header = \"".template("header")."\";");
echo $header;
if($bbrules == "on" && !$rulesubmit) {
$bbrulestxt = nl2br($bbrulestxt);
eval("\$page = \"".template("member_reg_rules")."\";");
echo $page;
}
else {

$currdate = gmdate("$timecode");
eval($lang_evaloffset);

$themelist = "<select name=\"thememem\">\n<option value=\"\">$lang_textusedefault</option>";
$query = $db->query("SELECT name FROM $table_themes");
while($themeinfo = $db->fetch_array($query)) {
$themelist .= "<option value=\"$themeinfo[name]\">$themeinfo[name]</option>\n";
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
$bbcodeis = $lang_texton;
} else {
$bbcodeis = $lang_textoff;
}

if($sightml == "on") {
$htmlis = $lang_texton;
} else {
$htmlis = $lang_textoff;
}

if($emailcheck != "on"){
eval("\$pwtd = \"".template("member_reg_password")."\";");
}

if($avastatus == "on") {
eval("\$avatd = \"".template("member_reg_avatarurl")."\";");
} elseif($avastatus == "list") {
$avatars = " <option value=\"\" />$lang_textnone</option>  ";
$dir1 = opendir("avatars");
while($avatar1 = readdir($dir1)) {
if(is_file("avatars/$avatar1")) {
$avatars .= " <option value=\"avatars/$avatar1\" />$avatar1</option>  ";
}
}
closedir($dir1);
$avatars = str_replace("value=\"$member[avatar]\"", "value=\"$member[avatar]\" selected=\"selected\"", $avatars);

eval("\$avatd = \"".template("member_reg_avatarlist")."\";");
}
eval("\$page = \"".template("member_reg")."\";");
echo $page;
}
}

if($regsubmit) {
if($doublee == "off" && strstr($email, "@")){
$email = trim($email);
$email1 = ", email";
$email2 = "OR email='$email'";
}

$username = trim($username);
$query = $db->query("SELECT username$email1 FROM $table_members WHERE username='$username' $email2");

if($member = $db->fetch_array($query)) {
eval("\$header = \"".template("header")."\";");
echo $header;
echo "<center><span class=\"mediumtxt \">$lang_alreadyreg</span></center>";
exit;
}
if($emailcheck == "on"){
$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
mt_srand((double)microtime() * 1000000);
for($get = strlen($chars); $i < 8; $i++) {
$password .= $chars[mt_rand(0, $get)];
}
$password2 = $password;
}
if($password != $password2) {
eval("\$header = \"".template("header")."\";");
echo $header;
echo "<center><span class=\"mediumtxt \">$lang_pwnomatch</span></center>";
exit;
}

if(!$username || $username == "xguest123" || $username == "onlinerecord"
|| eregi("'", $username) || eregi('"', $username) || eregi("<", $username)
|| eregi(",", $username) || eregi("^ *$", $username)) {
eval("\$header = \"".template("header")."\";");
echo $header;
echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
exit;
}

if(!strstr($email, "@")) {
eval("\$header = \"".template("header")."\";");
echo $header;
echo "<center><span class=\"mediumtxt \">$lang_bademail</span></center>";
exit;
}

if($password == "" || ereg('"', $password)|| ereg("'", $password)) {
eval("\$header = \"".template("header")."\";");
echo $header;
echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
exit;
}

$query = $db->query("SELECT COUNT(uid) FROM $table_members");
$count1 = $db->result($query,0);

if($count1 != "0") {
$query = $db->query("SELECT uid FROM $table_members ORDER BY uid DESC");
$count = $db->result($query,0);
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

$bio = addslashes($bio);
$locationnew = addslashes($locationnew);

$password = md5($password);

$db->query("INSERT INTO $table_members VALUES ('', '$username', '$password', '" . time() . "', '0', '$email', '$site', '$aim', '$status', '$locationnew', '$bio', '$sig', '$showemail', '$timeoffset1', '$icq', '$avatar', '$yahoo', '', '$thememem', '$bday', '$langfile', '$tpp', '$ppp', '$newsletter', '$onlineip', '$timeformatnew', '$msn', '$dateformatnew', '', '')");
$currtime = time() + (86400*30);
$username = $member[username];
setcookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
setcookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
if($emailcheck == "on"){
mail("$email", "$lang_textyourpw", "$lang_textyourpwis \n\n$username\n$password", "$lang_textfrom $bbname");
eval("\$header = \"".template("header")."\";");
echo $header;
echo "<center><span class=\"mediumtxt \">$lang_emailpw</span></center>";
}
eval("\$header = \"".template("header")."\";");
echo $header;
echo "<center><span class=\"mediumtxt \">$lang_regged</span></center>";
}
}


if($action == "viewpro") {
eval("\$header = \"".template("header")."\";");
echo $header;
if(!$member) {
echo $lang_nomember;
}
else {
$query = $db->query("SELECT * FROM $table_members WHERE username='$member'");
$memberinfo = $db->fetch_array($query);

$daysreg = (time() - $memberinfo[regdate]) / (24*3600);
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

$lastvisitdate = gmdate("$dateformat",$memberinfo[lastvisit] + ($timeoffset * 3600));
$lastvisittime = gmdate("$timecode",$memberinfo[lastvisit] + ($timeoffset * 3600));
$lastmembervisittext = "$lastvisitdate $lang_textat $lastvisittime";


$query = $db->query("SELECT COUNT(pid) FROM $table_posts");
$posts = $db->result($query, 0);

$query = $db->query("SELECT COUNT(tid) FROM $table_threads");
$threads = $db->result($query, 0);

$posttot = $threads+$posts;
if($posttot == 0) {
$percent = "0";
} else {
$percent = $memberinfo[postnum]*100/$posttot;
$percent = round($percent, 2);
}

$memberinfo[bio] = stripslashes($memberinfo[bio]);
$memberinfo[bio] = nl2br($memberinfo[bio]);
$encodeuser = rawurlencode($memberinfo[username]);

if($memberinfo[showemail] == "yes") {
eval("\$emailblock = \"".template("member_profile_email")."\";");
}
eval("\$page = \"".template("member_profile")."\";");
echo $page;

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
