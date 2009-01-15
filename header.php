<?php
$mtime1 = explode(" ", microtime());
$starttime = $mtime1[1] + $mtime1[0];
require "./functions.php";
require "./config.php";
require "./db/$database.php";
$version = "1.5 RC3";

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname);

$currtime1 = time() + (86400*365);
$currtime2 = time() + 600;
setcookie("xmblva", time(), $currtime1, $cookiepath, $cookiedomain);

if($xmblvb) {
$thetime = $xmblva;
} else {
$thetime = time();
}

setcookie("xmblvb", $thetime, $currtime2, $cookiepath, $cookiedomain);

$lastvisit = $thetime;
$lastvisit2 = $lastvisit;

$tables = array('banned', 'favorites', 'forums', 'members', 'posts', 'ranks', 'settings', 'smilies', 'templates', 'themes', 'threads', 'u2u', 'whosonline', 'words', 'buddys'); foreach($tables as $name) {
${'table_'.$name} = $tablepre.$name;
}

$query = $db->query("SELECT * FROM $table_settings");
foreach($db->fetch_array($query) as $key => $val) {
$$key = $val;
}
$bblang = $langfile;
$query = $db->query("SELECT * FROM $table_members WHERE username='$xmbuser' AND password='$xmbpw'");
$this = $db->fetch_array($query);
if($this) {
if($this[langfile] != "" && file_exists("lang/$langfile.lang.php")) {
$langfile = $this[langfile];
}
$timeoffset = $this[timeoffset];
$status = $this[status];
$themeuser = $this[theme];
$tpp = $this[tpp];
$ppp = $this[ppp];
$memtime = $this[timeformat];
$memdate = $this[dateformat];
$time = time();
$db->query("UPDATE $table_members SET lastvisit='$time' WHERE username='$xmbuser'");
} else {
$xmbuser = "";
$status = "";
$xmbpw = "";
}
require "lang/$langfile.lang.php";

if($regstatus == "on" || !$xmbuser) {
if($coppa == "on") {
$reglink = "<a href=\"member.php?action=coppa\">$lang_textregister</a>";
} else {
$reglink = "<a href=\"member.php?action=reg\">$lang_textregister</a>";
}
}

if($xmbuser && $xmbuser != '') {
$loginout = "<a href=\"misc.php?action=logout\">$lang_textlogout</a>";
$memcp = "<a href=\"memcp.php\">$lang_textusercp</a>";
$onlineuser = $xmbuser;

if($status == "Administrator") {
$cplink = "- <a href=\"cp.php\">$lang_textcp</a>";
}

$notify = "$lang_loggedin $xmbuser<br>[$loginout - $memcp $cplink]";

} else {
$loginout = "<a href=\"misc.php?action=login\">$lang_textlogin</a>";
$onlineuser = "xguest123";
$status = "";
$notify = "$lang_notloggedin [$loginout - $reglink]";
}

if($memtime == "") {
if($timeformat == "24") {
$timecode = "H:i";
} else {
$timecode = "h:i A";
}
} else {
if($memtime == "24") {
$timecode = "H:i";
} else {
$timecode = "h:i A";
}
}

if($memdate == "") {
$dateformat = $dateformat;
} else {
$dateformat = $memdate;
}

$dformatorig = $dateformat;
$dateformat = eregi_replace("mm", "n", $dateformat);
$dateformat = eregi_replace("dd", "j", $dateformat);
$dateformat = eregi_replace("yyyy", "Y", $dateformat);
$dateformat = eregi_replace("yy", "y", $dateformat);

// Get vistor's IP
if(getenv(HTTP_CLIENT_IP)) {
$onlineip = getenv(HTTP_CLIENT_IP);
} elseif(getenv(HTTP_X_FORWARDED_FOR)) {
$onlineip = getenv(HTTP_X_FORWARDED_FOR);
} else {
$onlineip = getenv(REMOTE_ADDR);
}
$onlinetime = time();


if(!$fid){
$query = $db->query("SELECT f.theme, t.fid FROM $table_forums f, $table_threads t WHERE f.fid=t.fid");
$locate = $db->fetch_array($query);
$fid = $locate[fid];
} elseif($fid) {
$query = $db->query("SELECT theme FROM $table_forums WHERE fid='$fid'");
$locate = $db->fetch_array($query);
}

$location = $HTTP_SERVER_VARS["REQUEST_URI"];
$location = "<a href=\"$location\">$location</a>";
$newtime = $time - 600;
$db->query("DELETE FROM $table_whosonline WHERE (ip='$onlineip' && username='$xmbuser') OR ip='$onlineip' OR username='$xmbuser' OR time<'$newtime'");
$db->query("INSERT INTO $table_whosonline VALUES('$onlineuser', '$onlineip', '$onlinetime', '$location')");


$themedef = $theme;
if($locate[theme] != "" && $themeuser == "$theme"){
$theme = $locate[theme];
} elseif($themeuser != "") {
$theme = $themeuser;
} else {
$theme = $theme;
}

$query = $db->query("SELECT * FROM $table_themes WHERE name='$theme'");
foreach($db->fetch_array($query) as $key => $val) {
if($key != "name") {
$$key = $val;
}
}

$fontedit = ereg_replace("[A-Z][a-z]", "", $fontsize);
$fontsuf = ereg_replace("[0-9]", "", $fontsize);
$font1 = $fontedit-1 . $fontsuf;
$font2 = $fontedit+1 . $fontsuf;
$font3 = $fontedit+2 . $fontsuf;


if($lastvisit) {
$lastdate = gmdate("$dateformat",$xmblva + ($timeoffset * 3600));
$lasttime = gmdate("$timecode",$xmblva + ($timeoffset * 3600));
$lastvisittext = "$lang_lastactive $lastdate $lang_textat $lasttime";
}

if($bbstatus == "off" && $status != "Administrator") {
$message = "$lang_textbboffnote $bboffreason";
noaccess($message);
exit;
}


$ips = explode(".", $onlineip);
$query = $db->query("SELECT id FROM $table_banned WHERE (ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')");
$result = $db->fetch_array($query);
if($status == "Banned" || ($result && (!$status || $status=="Member"))) {
$message = "$lang_bannedmessage";
noaccess($message);
exit;
}


if($regviewonly == "on") {
if($onlineuser == "xguest123" && $action != "reg" && $action != "login" && $action != "lostpw") {
$message = "$lang_reggedonly <a href=\"member.php?action=reg\">$lang_textregister</a> $lang_textor <a href=\"misc.php?action=login\">$lang_textlogin</a>";
noaccess($message);
exit;
}
}

$bbrulestxt = stripslashes(stripslashes($bbrulestxt));
$bboffreason = stripslashes(stripslashes($bboffreason));

if($gzipcompress == "on") {
ob_start("ob_gzhandler");
}

if($searchstatus == "on") {
$searchlink = "<a href=\"misc.php?action=search\"><font class=\"navtd\">$lang_textsearch</font></a> |";
}

if($faqstatus == "on") {
$faqlink = "<a href=\"faq.php\"><font class=\"navtd\">$lang_textfaq</font></a>";
}

if($memliststatus == "on") {
$memlistlink = "<a href=\"misc.php?action=list\"><font class=\"navtd\">$lang_textmemberlist</font></a> |";
}

//Get All Plugins
for($plugnum=1; $plugname[$plugnum] != ""; $plugnum++) {
if(!$plugurl[$plugnum] || !$plugname[$plugnum]) {
echo $lang_textbadplug;
} else {
$pluglink .= "| <a href=\"$plugurl[$plugnum]\"><font class=\"navtd\">$plugname[$plugnum]</font></a> ";
}
}


if(!strstr($bgcolor, ".")) {
$bgcode = "bgcolor=\"$bgcolor\"";
} else {
$bgcode = "background=\"$imgdir/$bgcolor\"";
}

if($xmbuser) {
$query = $db->query("SELECT * FROM $table_u2u WHERE msgto = '$xmbuser' AND folder = 'inbox'");
$newu2unum = $db->num_rows($query);
if($newu2unum > 0) {
$newu2umsg = "<a href=\"#\" onclick=\"Popup('u2u.php', 'Window', 550, 450);\">$lang_newu2u1 $newu2unum $lang_newu2u2</a>";
}
}
// Cache Smilies and Censored Words
smcwcache();

//Get Most Common Templates
eval("\$css = \"".template("css")."\";");
eval(template('phpinclude'));
?>
