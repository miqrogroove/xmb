<?
// Load the database and options
require "header.php";
loadtemplates('header,footer,memcp_profile_avatarurl,memcp_profile_avatarlist,memcp_profile,memcp_favs_row,memcp_favs_none,memcp_favs,memcp_subscriptions_row,memcp_subscriptions_none,memcp_subscriptions,buddylist_buddy_online,buddylist_buddy_offline,memcp_home_u2u_row,memcp_home_u2u_none,memcp_home');
// Determine the navigation
if($action == "profile") {
        $memberaction = "<a href=\"memcp.php\">$lang_textusercp</a> &gt;
$lang_texteditpro";
}
elseif($action == "subscriptions") {
        $memberaction = "<a href=\"memcp.php\">$lang_textusercp</a> &gt;
$lang_textsubscriptions";
}
elseif($action == "favorites") {
        $memberaction = "<a href=\"memcp.php\">$lang_textusercp</a> &gt;
$lang_textfavorites";
}
else {
        $memberaction = "$lang_textusercp";
}
$navigation = " &gt; $memberaction";

// Make the user CP Nav Bar
function makenav($current) {
global $bordercolor, $tablewidth, $borderwidth, $tablespacing, $altbg1,
$altbg2, $lang_textmyhome, $lang_texteditpro, $lang_textsubscriptions,
$lang_textfavorites, $lang_textu2umessenger, $lang_textbuddylist;
?>
<table cellpadding="0" cellspacing="0" border="0"
bgcolor="<?=$bordercolor?>" width="<?=$tablewidth?>"
align="center"><tr><td>
<table cellpadding="4" cellspacing="1" border="0" width="100%">
<tr align="center" class="tablerow">
<?php
if($current == "") {
echo "<td bgcolor=\"$altbg1\">$lang_textmyhome</td>";
} else {
echo "<td bgcolor=\"$altbg2\"><a href=\"memcp.php\">$lang_textmyhome</a></td>";
}
if($current == "profile") {
echo "<td bgcolor=\"$altbg1\">$lang_texteditpro</td>";
} else {
echo "<td bgcolor=\"$altbg2\"><a href=\"memcp.php?action=profile\">$lang_texteditpro</a></td>";
}
if($current == "subscriptions") {
echo "<td bgcolor=\"$altbg1\">$lang_textsubscriptions</td>";
} else {
echo "<td bgcolor=\"$altbg2\"><a href=\"memcp.php?action=subscriptions\">$lang_textsubscriptions</a></td>";
}
if($current == "favorites") {
echo "<td bgcolor=\"$altbg1\">$lang_textfavorites</td>";
} else {
echo "<td bgcolor=\"$altbg2\"><a href=\"memcp.php?action=favorites\">$lang_textfavorites</a></td>";
}
echo "<td bgcolor=\"$altbg2\"><a href=\"#\" onclick=\"Popup('u2u.php', 'Window', 550, 450);\">$lang_textu2umessenger</a></td>";
echo "<td bgcolor=\"$altbg2\"><a href=\"#\" onclick=\"Popup('buddy.php?', 'Window', 250, 300);\">$lang_textbuddylist</a></td>";
?>
</tr>
</table>
</td></tr></table><br>
<?php
}
// Determine if user is logged in, if not send to login page
if(!$xmbuser || !$xmbpw) {
?>
<script>
function redirect()
{
window.location.replace("misc.php?action=login");
}
setTimeout("redirect();", 1250);
</script>
<?
exit;
}
// Start Profile Editor Code
if($action == "profile") {
eval("\$header = \"".template("header")."\";");
echo $header;
makenav($action);
if(!$editsubmit) {
$query = $db->query("SELECT * FROM $table_members WHERE
username='$xmbuser'");
$member = $db->fetch_array($query);

if($member[showemail] == "yes") {
$checked = "checked=\"checked\"";
}

if($member[newsletter] == "yes") {
$newschecked = "checked=\"checked\"";
}

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
$dir1 = opendir("avatars");
while ($avatar1 = readdir($dir1)) {
if (is_file("avatars/$avatar1")) {
$avatars .= " <option value=\"avatars/$avatar1\" />$avatar1</option>  ";
}
}
$avatars = str_replace("value=\"$member[avatar]\"", "value=\"$member[avatar]\" SELECTED", $avatars);
$avatarbox = "<select name=\"avatar\">$avatars</select>";
eval("\$avatar = \"".template("memcp_profile_avatarlist")."\";");
closedir($dir1);
}

eval("\$profile = \"".template("memcp_profile")."\";");
echo $profile;
}

if($editsubmit) {

$query = $db->query("SELECT * FROM $table_members WHERE
username='$xmbuser'");
$member = $db->fetch_array($query);

if(!$member[username]) {
echo "<center><span class=\"mediumtxt \">$lang_badname</span></center>";
exit;
}

if($xmbpw != $member[password]) {
echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span></center>";
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

$db->query("UPDATE $table_members SET email='$email', site='$site', aim='$aim', location='$memlocation', bio='$bio', sig='$sig', showemail='$showemail', timeoffset='$timeoffset1', icq='$icq', avatar='$avatar', yahoo='$yahoo', theme='$thememem', bday='$bday', langfile='$langfilenew', tpp='$tppnew', ppp='$pppnew', newsletter='$newsletter', timeformat='$timeformatnew', msn='$msn', dateformat='$dateformatnew' WHERE username='$xmbuser'");

if($newpassword != "") {
if(ereg('"', $newpassword) || ereg("'", $newpassword)) {
echo "<center><span class=\"mediumtxt \">$lang_textpwincorrect</span><center>";
exit;
}
$newpassword = md5($newpassword);
$db->query("UPDATE $table_members SET password='$newpassword' WHERE username='$xmbuser'");
}

echo "<center><span class=\"mediumtxt \">$lang_usercpeditpromsg</span></center>";
?>
<script>
function redirect()
{
window.location.replace("memcp.php");
}
setTimeout("redirect();", 1250);
</script>
<?
}
}
// Start Favorites
elseif($action == "favorites") {
eval("\$header = \"".template("header")."\";");
echo $header;
makenav($action);
if($favadd && !$favsubmit) {
$query = $db->query("SELECT tid FROM $table_favorites WHERE tid='$favadd' AND username='$xmbuser' AND type='favorite'");
$favthread = $db->fetch_array($query);
if($favthread) {
echo "<center><span class=\"mediumtxt \">$lang_favonlistmsg</span></center>";
exit;
}
$db->query("INSERT INTO $table_favorites VALUES ('$favadd', '$xmbuser', 'favorite')");
echo "<center><span class=\"mediumtxt \">$lang_favaddedmsg</span></center>";
?>
<script>
function redirect()
{
window.location.replace("memcp.php?action=favorites");
}
setTimeout("redirect();", 1250);
</script>
<?
}
if(!$favadd && !$favsubmit) {
$query = $db->query("SELECT * FROM $table_favorites f, $table_threads t, $table_posts p WHERE f.tid=t.tid AND p.tid=t.tid AND p.subject=t.subject AND f.username='$xmbuser' AND f.type='favorite' ORDER BY t.lastpost DESC");
$favnum = 0;
while($fav = $db->fetch_array($query)) {
$query2 = $db->query("SELECT name, fup, fid FROM $table_forums WHERE fid='$fav[fid]'");
$forum = $db->fetch_array($query2);
$lastpost = explode("|", $fav[lastpost]);
$dalast = $lastpost[0];


$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";

$lastreplydate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600));
$lastreplytime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600));
$lastpost = "$lang_lastreply1 $lastreplydate $lang_textat $lastreplytime
$lang_textby $lastpost[1]";
$fav[subject] = stripslashes($fav[subject]);
if($fav[icon] != "") {
$fav[icon] = "<img src=\"$smdir/$fav[icon]\" />";
} else {
$fav[icon] = "&nbsp;";
}
$favnum++;
eval("\$favs .= \"".template("memcp_favs_row")."\";");
}
if($favnum == 0) {
eval("\$favs = \"".template("memcp_favs_none")."\";");
}

eval("\$favorites = \"".template("memcp_favs")."\";");
echo $favorites;
}
if(!$favadd && $favsubmit) {
$query = $db->query("SELECT tid FROM $table_favorites WHERE username='$xmbuser' AND type='favorite'");
while($fav = $db->fetch_array($query)) {
$delete = "delete$fav[tid]";
$delete = "${$delete}";
$db->query("DELETE FROM $table_favorites WHERE username='$xmbuser' AND tid='$delete' AND type='favorite'");
}
echo "<center><span class=\"mediumtxt \">$lang_favsdeletedmsg</span></center>";
?>
<script>
function redirect()
{
window.location.replace("memcp.php?action=favorites");
}
setTimeout("redirect();", 1250);
</script>
<?
}
}
// Start Subscriptions
elseif($action == "subscriptions") {
eval("\$header = \"".template("header")."\";");
echo $header;
makenav($action);
if($subadd && !$subsubmit) {
$query = $db->query("SELECT tid FROM $table_favorites WHERE tid='$subadd'
AND username='$xmbuser' AND type='subscription'");
$thread = $db->fetch_array($query);
if($thread) {
echo "<center><span class=\"mediumtxt \">$lang_subonlistmsg</span><center>";
exit;
}
$db->query("INSERT INTO $table_favorites VALUES ('$subadd', '$xmbuser', 'subscription')");
echo "<center><span class=\"mediumtxt \">$lang_subaddedmsg</span></center>";
?>
<script>
function redirect()
{
window.location.replace("memcp.php?action=subscriptions");
}
setTimeout("redirect();", 1250);
</script>
<?
}
if(!$subadd && !$subsubmit) {

$query = $db->query("SELECT * FROM $table_favorites f, $table_threads t, $table_posts p WHERE f.tid=t.tid AND p.tid=t.tid AND p.subject=t.subject AND f.username='$xmbuser' AND f.type='subscription' ORDER BY t.lastpost DESC");
$subnum = 0;
while($fav = $db->fetch_array($query)) {
$query2 = $db->query("SELECT name, fup, fid FROM $table_forums WHERE
fid='$fav[fid]'");
$forum = $db->fetch_array($query2);
$lastpost = explode("|", $fav[lastpost]);
$dalast = $lastpost[0];


$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";

$lastreplydate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600));
$lastreplytime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600));
$lastpost = "$lang_lastreply1 $lastreplydate $lang_textat $lastreplytime
$lang_textby $lastpost[1]";
$fav[subject] = stripslashes($fav[subject]);
if($fav[icon] != "") {
$fav[icon] = "<img src=\"$smdir/$fav[icon]\" />";
} else {
$fav[icon] = "&nbsp;";
}
$subnum++;
eval("\$subscriptions .= \"".template("memcp_subscriptions_row")."\";");
}
if($subnum == 0) {
eval("\$subscriptions = \"".template("memcp_subscriptions_none")."\";");
}
eval("\$page = \"".template("memcp_subscriptions")."\";");
echo $page;
}
if(!$subadd && $subsubmit) {
$query = $db->query("SELECT tid FROM $table_favorites WHERE
username='$xmbuser' AND type='subscription'");
while($sub = $db->fetch_array($query)) {
$delete = "delete$sub[tid]";
$delete = "${$delete}";
$db->query("DELETE FROM $table_favorites WHERE username='$xmbuser' AND tid='$delete' AND type='subscription'");
}
echo "<center><span class=\"mediumtxt \">$lang_subsdeletedmsg</span></center>";
?>
<script>
function redirect()
{
window.location.replace("memcp.php?action=subscriptions");
}
setTimeout("redirect();", 1250);
</script>
<?
}
}
// Load the Default Page
else {
eval("\$header = \"".template("header")."\";");
echo $header;
eval($lang_evalusercpwelcome);
makenav($action);
// Load Buddy List
$query = $db->query("SELECT * FROM $table_buddys WHERE username='$xmbuser'");
while($buddy = $db->fetch_array($query)) {
$query2 = $db->query("SELECT * FROM $table_whosonline WHERE username='$buddy[buddyname]'");
$onlineinfo = $db->fetch_array($query2);
if($onlineinfo) {
eval("\$buddys[online] .= \"".template("buddylist_buddy_online")."\";");
} else {
eval("\$buddys[offline] .= \"".template("buddylist_buddy_offline")."\";");
}
}
$query = $db->query("SELECT * FROM $table_members WHERE username='$xmbuser'");
$member = $db->fetch_array($query);
if($member[avatar] == "") {
$member[avatar] = "&nbsp;";
} else {
$member[avatar] = "<img src=\"$member[avatar]\" border=0\">";
}
// Make the Page
$query = $db->query("SELECT * FROM $table_u2u WHERE msgto='$xmbuser' AND folder='inbox' ORDER BY dateline DESC LIMIT 0, 5");
$u2unum = $db->num_rows($query);
while($message = $db->fetch_array($query)) {
$postdate = date("$dateformat",$message[dateline] + ($timeoffset * 3600));
$posttime = date("$timecode",$message[dateline] + ($timeoffset * 3600));
$senton = "$postdate $lang_textat $posttime";
if($message[subject] == "") {
$message[subject] = "&lt;$lang_textnosub]&gt;";
}
eval("\$messages .= \"".template("memcp_home_u2u_row")."\";");
}
if($u2unum == 0) {
eval("\$messages = \"".template("memcp_home_u2u_none")."\";");
}
$query2 = $db->query("SELECT * FROM $table_favorites f, $table_threads t, $table_posts p WHERE f.tid=t.tid AND p.tid=t.tid AND p.subject=t.subject AND f.username='$xmbuser' AND f.type='favorite' ORDER BY t.lastpost DESC");
$favnum = $db->num_rows($query);
while($fav = $db->fetch_array($query2)) {
$query = $db->query("SELECT name, fup, fid FROM $table_forums WHERE	fid='$fav[fid]'");
$forum = $db->fetch_array($query);
$lastpost = explode("|", $fav[lastpost]);
$dalast = $lastpost[0];
$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";
$lastreplydate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600));
$lastreplytime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600));
$lastpost = "$lang_lastreply1 $lastreplydate $lang_textat
$lastreplytime $lang_textby $lastpost[1]";
$fav[subject] = stripslashes($fav[subject]);
if($fav[icon] != "") {
$fav[icon] = "<img src=\"$smdir/$fav[icon]\" />";
} else {
$fav[icon] = "&nbsp;";
}
eval("\$favs .= \"".template("memcp_favs_row")."\";");
}
if($favnum == 0) {
eval("\$favs = \"".template("memcp_favs_none")."\";");
}
eval("\$home = \"".template("memcp_home")."\";");
echo $home;
}
$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
if($showtotaltime != "off") {
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);
}
eval("\$footer = \"".template("footer")."\";");
echo $footer;
// End Script
// by Chris Boulton (chris@xmbforum.com)
?>
