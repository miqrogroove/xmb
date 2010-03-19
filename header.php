<?
$mtime1 = explode(" ", microtime());
$starttime = $mtime1[1] + $mtime1[0];

$currtime1 = time() + (86400*365);
$currtime2 = time() + 600;
setcookie("lastvisita", time(), $currtime1);

if($lastvisitb) {
$thetime = $lastvisitb;
} else {
$thetime = $lastvisita;
}

setcookie("lastvisitb", $thetime, $currtime2);

$lastvisit = $thetime;
$lastvisit2 = $lastvisit;

require "functions.php";
require "settings.php";
require "config.php";
$version = "1.05";

mysql_pconnect($dbhost, $dbuser, $dbpw) or die(mysql_error());
mysql_select_db($dbname) or die(mysql_error());


$tables = array('banned','forums', 'members', 'posts', 'ranks', 'smilies', 'themes', 'threads', 'u2u', 'whosonline', 'words');
foreach($tables as $name) {
${'table_'.$name} = $tablepre.$name;
}

if($nocacheheaders == "on") { 
header("Expires: ".gmdate("D, d M Y H:i:s")."GMT"); 
header("Cache-Control: no-cache, must-revalidate"); 
header("Pragma: no-cache"); 
} 

$bblang = $langfile;
if($thisuser && $thisuser != '') {
$query = mysql_query("SELECT * FROM $table_members WHERE username='$thisuser'") or die(mysql_error());
$this = mysql_fetch_array($query);
if($this[langfile] != "") {
$langfile = $this[langfile];
}
$timeoffset = $this[timeoffset];
$status = $this[status];
$themeuser = $this[theme];
$tpp = $this[tpp];
$ppp = $this[ppp];
$memtime = $this[timeformat];
$memdate = $this[dateformat];

if($this[password] == $thispw) {
$thisuser = $thisuser;
} else {
$thisuser = "";
}
}
require "lang/$langfile.lang.php";

if(!$thisuser || !$thispw) {
$thisuser = "";
$status = "";
}

if($thisuser && $thisuser != '') { 
$time = time(); 
mysql_query("UPDATE $table_members SET lastvisit='$time' WHERE username='$thisuser'") or die(mysql_error()); 
}

if($regstatus == "on" && $noreg != "on") { 
if($coppa == "on") { 
$reglink = "<a href=\"member.php?action=coppa\"><span class=\"navtd\">$lang[textregister]</span></a>"; 
} else { 
$reglink = "<a href=\"member.php?action=reg\"><span class=\"navtd\">$lang[textregister]</span></a>"; 
} 
$proreg = $reglink; 
} 

if($thisuser && $thisuser != '') { 
$notify = "$lang[loggedin] $thisuser"; 
$loginout = "<a href=\"misc.php?action=logout\"><span class=\"navtd\">$lang[textlogout]</span></a>"; 
$proreg = "<a href=\"member.php?action=editpro\"><span class=\"navtd\">$lang[textprofile]</span></a>"; 
$onlineuser = $thisuser; 

if($u2ustatus == "on") { 
$proreg .= " | <a href=\"#\" onclick=\"Popup('misc2.php?action1=u2u', 'Window', 550, 450);\"><span class=\"navtd\">$lang[textu2u]</span></a>"; 
} 

} else { 
$notify = "$lang[notloggedin]"; 
$loginout = "<a href=\"misc.php?action=login\"><span class=\"navtd\">$lang[textlogin]</span></a>"; 
$onlineuser = "xguest123"; 
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


if (getenv(HTTP_CLIENT_IP)) { 
$onlineip = getenv(HTTP_CLIENT_IP); 
} elseif (getenv(HTTP_X_FORWARDED_FOR)) { 
$onlineip = getenv(HTTP_X_FORWARDED_FOR); 
} else { 
$onlineip = getenv(REMOTE_ADDR); 
} 
$onlinetime = time();


if($tid != ""){
$sql = mysql_query("SELECT * FROM $table_threads WHERE tid='$tid'");  
$locate2 = mysql_fetch_array($sql);
$fid = $locate2[fid];
}

if($fid != ""){
$query = mysql_query("SELECT name, private, theme FROM $table_forums WHERE fid='$fid'") or die(mysql_error());
$locate = mysql_fetch_array($query);
}

if($fid != "" && $tid == "" && $locate[private] != "staff"){
$location = "<a href=\"forumdisplay.php?fid=$fid\">$locate[name]</a>";
} elseif($fid != "" && $tid != "" && $locate[private] != "staff"){
$location = "<a href=\"forumdisplay.php?fid=$fid\">$locate[name]</a>: <a href=\"viewthread.php?tid=$tid\">$locate2[subject]</a>";
} elseif($locate[private] == "staff"){
$location = "$lang[textpriv]";
} elseif($action == "list"){
$location = "<a href=\"member.php?action=list\">$lang[textmemberlist]</a>";
} elseif($action == "search"){
$location = "<a href=\"misc.php?action=search\">$lang[textsearch]</a>";
} elseif($action == "faq"){
$location = "$lang[textfaq]";
} elseif($action == "online"){
$lang[whosonline] = addslashes($lang[whosonline]);
$location = "$lang[whosonline]";
} elseif($action == "stats"){
$location = "<a href=\"misc.php?action=stats\">$lang[textstats]</a>";
} else{
$location = "<a href=\"index.php\">$lang[textindex]</a>";
}


mysql_query("DELETE FROM $table_whosonline WHERE ip='$onlineip' AND username !='$thisuser'");

if(!$thisuser) {
mysql_query("DELETE FROM $table_whosonline WHERE ip='$onlineip'");
} elseif($thisuser && !$anonlog) {
mysql_query("DELETE FROM $table_whosonline WHERE username='$thisuser'");
}

mysql_query("INSERT INTO $table_whosonline VALUES('$onlineuser', '$onlineip', '$onlinetime', '$location')");


$themedef = $theme;
if($locate[2] != "" && $themeuser == "$theme"){
$theme = $locate[2];
} elseif($themeuser != "") {
$theme = $themeuser;
} else {
$theme = $theme;
}

$query = mysql_query("SELECT * FROM $table_themes WHERE name='$theme'");
foreach(mysql_fetch_array($query) as $key => $val) {
if($key != "name") {
$$key = $val;
}
}

$font1 = $fontsize-1;
$font2 = $fontsize+1;
$font3 = $fontsize+3;


if($status == "Administrator") {
$cplink = "| <a href=\"cp.php\"><span class=\"navtd\">$lang[textcp]</span></a>";
}

if($lastvisit) {
$lastdate = gmdate("$dateformat",$lastvisita + ($timeoffset * 3600));
$lasttime = gmdate("$timecode",$lastvisita + ($timeoffset * 3600));
$lastvisittext = "$lang[lastactive] $lastdate $lang[textat] $lasttime";
}

$jump = "<select name=\"fid\">";

$queryfor = mysql_query("SELECT * FROM $table_forums WHERE fup='' AND type='forum' ORDER BY displayorder") or die(mysql_error());
while($forum = mysql_fetch_array($queryfor)) {
$authorization = privfcheck($hideprivate, $status, $forum[private], $thisuser, $forum[userlist]);
if($authorization == "true") { 
$jump .= "<option value=\"$forum[fid]\"> &nbsp; &gt; $forum[name]</option>";

$querysub = mysql_query("SELECT * FROM $table_forums WHERE fup='$forum[fid]' AND type='sub' ORDER BY displayorder") or die(mysql_error());
while($sub = mysql_fetch_array($querysub)) {
$authorization = privfcheck($hideprivate, $status, $sub[private], $thisuser, $sub[userlist]);
if($authorization == "true") { 
$jump .= "<option value=\"$sub[fid]\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &gt; $sub[name]</option>";
}
}
}
$jump .= "<option value=\"\"> </option>";
}

$querygrp = mysql_query("SELECT name, fid FROM $table_forums WHERE type='group' ORDER BY displayorder") or die(mysql_error());
while($group = mysql_fetch_array($querygrp)) {
$jump .= "<option value=\"\">$group[name]</option>";
$jump .= "<option value=\"\">--------------------</option>";

$queryfor = mysql_query("SELECT * FROM $table_forums WHERE fup='$group[fid]' AND type='forum' ORDER BY displayorder") or die(mysql_error());
while($forum = mysql_fetch_array($queryfor)) {
$authorization = privfcheck($hideprivate, $status, $forum[private], $thisuser, $forum[userlist]);
if($authorization == "true") { 
$jump .= "<option value=\"$forum[fid]\"> &nbsp; &gt; $forum[name]</option>";

$querysub = mysql_query("SELECT * FROM $table_forums WHERE fup='$forum[fid]' AND type='sub' ORDER BY displayorder") or die(mysql_error());
while($sub = mysql_fetch_array($querysub)) {
$authorization = privfcheck($hideprivate, $status, $sub[private], $thisuser, $sub[userlist]);
if($authorization == "true") { 
$jump .= "<option value=\"$sub[fid]\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &gt; $sub[name]</option>";
}
}
}
}
$jump .= "<option value=\"\"> </option>";
}
$jump .="</select>&nbsp;<input type=\"submit\" value=\"$lang[textgo]\">";


$query = mysql_query("SELECT * FROM $table_u2u WHERE msgto='$thisuser' ORDER BY dateline DESC LIMIT 1") or die(mysql_error()); 
$u2u = mysql_fetch_array($query); 
if($lastvisita < $u2u[dateline]) { 
$u2upopup = "onLoad=\"Popup(\'misc2.php?action1=u2u\', \'Window\', 550, 450);\""; 
} else { 
$u2upopup = ""; 
}

if($bbstatus == "off" && $status != "Administrator") {
$html = template("header.html");
eval("echo stripslashes(\"$html\");");
echo "$action";
?>
<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td class="12px"><?=$lang[textbboffnote]?> <?=$bboffreason?></td></tr></table>

<?
exit;
}


$ips = explode(".", $onlineip); 
$query = mysql_query("SELECT id FROM $table_banned WHERE (ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')") or die(mysql_error()); 
$result = mysql_fetch_array($query); 
if($status == "Banned" || ($result && (!$status || $status=="Member"))) { 
echo "$lang[bannedmessage]"; 
exit; 
}


if($regviewonly == "on") {
if($onlineuser == "xguest123" && $action != "reg" && $action != "login" && $action != "lostpw") {
$html = template("header.html");
eval("echo stripslashes(\"$html\");");
echo "$action";
?>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td class="12px">
<?=$lang[reggedonly]?> <a href="member.php?action=reg"><?=$lang[textregister]?></a> <?=$lang[textor]?> <a href="misc.php?action=login"><?=$lang[textlogin]?></a>
</td></tr></table>
<?
exit;
}
}

$query = mysql_query("SELECT COUNT(*) FROM $table_whosonline WHERE username!='onlinerecord'") or die(mysql_error()); 
$count = mysql_result($query, 0);

$query = mysql_query("SELECT * FROM $table_whosonline WHERE username='onlinerecord'") or die(mysql_error()); 
$onlinerecord = mysql_fetch_array($query); 

if ($count > $onlinerecord[ip]) { 
mysql_query("UPDATE $table_whosonline SET ip='$count' WHERE username='onlinerecord'") or die(mysql_error()); 
}

$bbrulestxt = stripslashes(stripslashes($bbrulestxt));
$bboffreason = stripslashes(stripslashes($bboffreason));

if($gzipcompress == "on") { 
ob_start("ob_gzhandler"); 
}

if($searchstatus == "on") { 
$searchlink = "| <a href=\"misc.php?action=search\"><span class=\"navtd\">$lang[textsearch]</span></a>"; 
} 

if($faqstatus == "on") { 
$faqlink = "| <a href=\"misc.php?action=faq\"><span class=\"navtd\">$lang[textfaq]</span></a>"; 
} 

if($memliststatus == "on") { 
$memlistlink = "| <a href=\"misc.php?action=list\"><span class=\"navtd\">$lang[textmemberlist]</span></a>"; 
} 

if($boardimg != "") { 
$logo = "<tr><td><a href=\"index.php\"><img src=\"$boardimg\" alt=\"Board logo\" border=\"0\" /></a></td><td> </td></tr>"; 
}

if($statspage == "on") {
$statslink = "| <a href=\"misc.php?action=stats\"><span class=\"navtd\">$lang[textstats]</span></a>";
}
?>