<?php

/*

XMB 1.8 Partagium
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/

// Required Files - XMB (Version/Patch File) Configuration File, Database Settings File & Language Files

require "./xmb.php";
require "./config.php";
require "./db/$database.php";
require "./functions.php";

// Check for the presence of installation files, if someone ran these it would reset the board.
// Slows the board down by micro seconds but if you know what you are doing, you can remove these lines for speed.

if (file_exists("./cinst.php")) { die("Error: The file cinst.php was found on your server, please delete this file to continue."); }
if (file_exists("./index_log.log")) { die("Error: The file index_log.log was found on your server, please delete this file to continue."); }

// Removal of the version information is against the XMB license agreement
// You are NOT permitted to remove any copyright material from the XMB software
// These strings can be pulled for use on any page as header is required by all XMB pages

$versionlong = "Powered by XMB 1.8 Partagium Final Beta (Build: 2120211PM)";
$versioncompany = "Aventure Media & The XMB Group";
$versionshort = "XMB 1.8 Partagium";
$versiongeneral = "XMB 1.8";
$versionbuild = "#2120211PM";

// Check the format of the URL

if(eregi("\?[0-9]+$", $_SERVER['REQUEST_URI'])){
	exit("Invalid String Format, Please Check Your URL");
}

// Check IP Address to see if a false IP address or format is being us - to delete remove the lines between start and finish
if(!eregi("^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$", $_SERVER['REMOTE_ADDR'])){
        exit("Access to this website is currently not possible as your hostname/IP appears suspicous.");
}
// Finish IP CHeck

$url_check = Array('status', 'xmbuser', 'xmbpw');
foreach ($url_check as $name) {
 	if (eregi($name, $_SERVER['REQUEST_URI'])){
 		exit();
 	}
}

$server = substr_replace($_SERVER['SERVER_SOFTWARE'], '', 3, 50);

if($server == "Apa") {
        $wookie = $server;
} else {
        error_reporting(32);
}

$mtime1 = explode(" ", microtime());
$starttime = $mtime1[1] + $mtime1[0];

$db = new dbstuff;
$tempcache = "";
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

$tables = array('attachments', 'banned', 'favorites', 'forums', 'members', 'posts', 'ranks', 'settings', 'smilies', 'templates', 'themes', 'threads', 'u2u', 'whosonline', 'words', 'restricted', 'buddys');
foreach($tables as $name) {
	${'table_'.$name} = $tablepre.$name;
}

loadtemplates('css,functions_bbcode');

$currtime1 = time() + (86400*365);
$currtime2 = time() + 600;
setcookie("xmblva", time(), $currtime1, $cookiepath, $cookiedomain);

if($xmblvb){
	$thetime = $xmblvb;
}elseif($xmblva){
	$thetime = $xmblva;
}else{
	$thetime = 0;
}

setcookie("xmblvb", $thetime, $currtime2, $cookiepath, $cookiedomain);

$lastvisit = $thetime;
$lastvisit2 = $lastvisit;

$settingsquery = $db->query("SELECT * FROM $table_settings");
foreach($db->fetch_array($settingsquery) as $key => $val) {
        $$key = $val;
	$SETTINGS[$key] = $val;
}
$query = $db->query("SELECT * FROM $table_members WHERE username='$xmbuser' AND password='$xmbpw'");
if($db->num_rows($query) == 1){
	foreach($db->fetch_array($query) as $key => $val) {
		$self[$key] = $val;
	}
	$timeoffset = $self['timeoffset'];
	$status = $self['status'];
	$themeuser = $self['theme'];
	$tpp = $self['tpp'];
	$ppp = $self['ppp'];
	$memtime = $self['timeformat'];
	$memdate = $self['dateformat'];
	$signature = $self['sig'];
	$sig = $signature;
	$time = time();
	if($self['langfile'] == "" || !file_exists("lang/$self[langfile].lang.php")) {
		$langfile = $SETTINGS['langfile'];
	}else{
		$langfile = $self['langfile'];
	}
	if(!empty($theme)){
		$themeuser = $self['theme'];
	}
	$time = time();
	$db->query("UPDATE $table_members SET lastvisit='$time' WHERE username='$xmbuser'");
}else{
	$xmbuser = NULL;
	$self = NULL;
	$status = NULL;
	$xmbpw = NULL;
	$sig = NULL;
}

$bblang = $langfile;

require "lang/$langfile.lang.php";

if($regstatus == "on" || empty($self['username'])) {
	if($coppa == "on") {
		$reglink = "<a href=\"member.php?action=coppa\">$lang_textregister</a>";
	} else {
		$reglink = "<a href=\"member.php?action=reg\">$lang_textregister</a>";
	}
}

if(($xmbuser && $xmbuser != '') && !empty($self['username'])) {
	$loginout = "<a href=\"misc.php?action=logout\">$lang_textlogout</a>";
	$memcp = "<a href=\"memcp.php\">$lang_textusercp</a>";
	$onlineuser = $xmbuser;
        if($status == "Administrator" || $status == "Super Administrator") {
			$cplink = "- <a href=\"cp.php\">$lang_textcp</a>";
		}
	$notify = "$lang_loggedin $xmbuser<br>[$loginout - $memcp $cplink]";
} else {
	$loginout = "<a href=\"misc.php?action=login\">$lang_textlogin</a>";
	$onlineuser = "xguest123";
	$status = "";
	$notify = "$lang_notloggedin [$loginout - $reglink]";
}

if(empty($memtime)) {
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

if(empty($memdate)) {
	$dateformat = $dateformat;
} else {
	$dateformat = $memdate;
}

$dformatorig = $dateformat;
$dateformat = eregi_replace("mm", "n", $dateformat);
$dateformat = eregi_replace("dd", "j", $dateformat);
$dateformat = eregi_replace("yyyy", "Y", $dateformat);
$dateformat = eregi_replace("yy", "y", $dateformat);


// Get visitors IP address

if(getenv('HTTP_CLIENT_IP')) {
	$onlineip = getenv('HTTP_CLIENT_IP');
} elseif(getenv('HTTP_X_FORWARDED_FOR')) {
	$onlineip = getenv('HTTP_X_FORWARDED_FOR');
} else {
	$onlineip = getenv('REMOTE_ADDR');
}

$onlinetime = time();

// Start url to text transcription - this is used for whosonline

if(!empty($tid) && !preg_match("/post.php/i",$_SERVER['REQUEST_URI'])){
	$query = $db->query("SELECT f.theme, t.fid, t.subject FROM $table_forums f, $table_threads t WHERE f.fid=t.fid AND t.tid='$tid'");
		while($locate = $db->fetch_array($query)){
			$fid = $locate['fid'];
			$forumtheme = $locate['theme'];
			$location = "$lang_onlineviewthread $locate[subject]";
        }
}elseif(!empty($fid)  && !preg_match("/post.php/i",$_SERVER['REQUEST_URI'])){
	$query = $db->query("SELECT theme, name FROM $table_forums WHERE fid='$fid'");
        while($locate = $db->fetch_array($query)){
			$forumtheme = $locate['theme'];
			$location = "$lang_onlineforumdisplay $locate[name]";
        }
}elseif(preg_match("/memcp.php/i",$_SERVER['REQUEST_URI'])){
        $location = "$lang_onlinememcp";
}elseif(preg_match("/cp.php/i", $_SERVER['REQUEST_URI']) || preg_match("/cp2.php/i", $_SERVER['REQUEST_URI'])){
        $location = "$lang_onlinecp";
}elseif(preg_match("/editprofile.php/i",$_SERVER['REQUEST_URI'])){
        $location = "$lang_onlineeditprofile";
}elseif(preg_match("/emailfriend.php/i",$_SERVER['REQUEST_URI'])){
        $location = "$lang_onlineemailfriend";
}elseif(preg_match("/faq.php/i",$_SERVER['REQUEST_URI'])){
        $location = "$lang_onlinefaq";
}elseif(preg_match("/index.php/i",$_SERVER['REQUEST_URI'])){
        $location = "$lang_onlineindex";
}elseif(preg_match("/member.php/i",$_SERVER['REQUEST_URI'])){
        if($action == "reg"){
                $location = "$lang_onlinereg";
        }elseif($action == "viewpro"){
                $location = "$lang_onlineviewpro";
        }elseif($action == "coppa"){
                $location = "$lang_onlinecoppa";
        }
}elseif(preg_match("/misc.php/i",$_SERVER['REQUEST_URI'])){
        if($action == "login"){
                $location = "$lang_onlinelogin";
        }elseif($action == "logout"){
                $location = "$lang_onlinelogout";
        }elseif($action == "search"){
                $location = "$lang_onlinesearch";
        }elseif($action == "lostpw"){
                $location = "$lang_onlinelostpw";
        }elseif($action == "online"){
                $location = "$lang_onlinewhosonline";
        }elseif($action == "onlinetoday"){
                $location = "$lang_onlineonlinetoday";
        }elseif($action == "list"){
                $location = "$lang_onlinememlist";
        }
}elseif(preg_match("/post.php/i",$_SERVER['REQUEST_URI'])){
        if($action == "edit"){
                $location = "$lang_onlinepostedit";
        }elseif($action == "newthread"){
                $location = "$lang_onlinepostnewthread";
        }elseif($action == "reply"){
                $location = "$lang_onlinepostreply";
        }
}elseif(preg_match("/stats.php/i",$_SERVER['REQUEST_URI'])){
        $location = "$lang_onlinestats";
}elseif(preg_match("/today.php/i",$_SERVER['REQUEST_URI'])){
        $location = "$lang_onlinetodaysposts";
}elseif(preg_match("/tools.php/i",$_SERVER['REQUEST_URI'])){
        $location = "$lang_onlinetools";
}elseif(preg_match("/topicadmin.php/i",$_SERVER['REQUEST_URI'])){
        $location = "$lang_onlinetopicadmin";
}elseif(preg_match("/u2u.php/i",$_SERVER['REQUEST_URI'])){
        if($action == "send"){
                $location = $lang_onlineu2usend;
        }elseif($action == "delete"){
                $location = $lang_onlineu2udelete;
        }elseif($action == "ignore" || $action == "ignoresubmit"){
                $location = $lang_onlineu2uignore;
        }elseif($action == "view"){
                $location = $lang_onlineu2uview;
        }
}else{
        $location = getenv('REQUEST_URI');
}


$wollocation = getenv('REQUEST_URI');
$wollocation = "<a href=\"$wollocation\">$location</a>";
$newtime = $time - 600;
$db->query("DELETE FROM $table_whosonline WHERE (ip='$onlineip' && username='xguest123') OR (username='$xmbuser') OR time<'$newtime'");
$db->query("INSERT INTO $table_whosonline VALUES('$onlineuser', '$onlineip', '$onlinetime', '$wollocation')");


if(!empty($themeuser)) {
        $theme = $themeuser;
} elseif(!empty($forumtheme)) {
        $theme = $forumtheme;
} else {
	$theme = $SETTINGS['theme'];
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


if($lastvisit && $xmbuser && $self['username'] != "") {
	$lastdate = date("$dateformat",$xmblva + ($timeoffset * 3600));
	$lasttime = date("$timecode",$xmblva + ($timeoffset * 3600));
	$lastvisittext = "$lang_lastactive $lastdate $lang_textat $lasttime";
} else {
	$lastvisittext = "$lang_lastactive $lang_textnever";
}

if($SETTINGS['gzipcompress'] == "on") {
	ob_start('ob_gzhandler');
}

if($SETTINGS['searchstatus'] == "on") {
	$searchlink = "<a href=\"misc.php?action=search\"><font class=\"navtd\">$lang_textsearch</a> |</font>";
} else {
	$searchlink = "";
}

if($SETTINGS['faqstatus'] == "on") {
	$faqlink = "<a href=\"faq.php\"><font class=\"navtd\">$lang_textfaq</a> |</font>";
} else {
	$faqlink = "";
}

if($SETTINGS['memliststatus'] == "on") {
	$memlistlink = "<a href=\"misc.php?action=list\"><font class=\"navtd\">$lang_textmemberlist</a> |</font>";
} else {
	$memlistlink = "";
}

if($SETTINGS['todaysposts'] == "on") {
	$todaysposts = "<a href=\"today.php\"><font class=\"navtd\">$lang_navtodaysposts</a> |</font>";
} else {
	$todaysposts = "";

}

if($SETTINGS['stats'] == "on") {
	$stats = "<a href=\"stats.php?action=view\"><font class=\"navtd\">$lang_navstats</a></font>";
} else {
	$stats = "";
}

//Get All Plugins
for($plugnum=1; $plugname[$plugnum] != ""; $plugnum++) {
	if(!empty($plugurl[$plugnum]) && !empty($plugname[$plugnum])) {
		if($plugadmin != "yes") {
			$pluglink .= "| <a href=\"$plugurl[$plugnum]\"><font class=\"navtd\">$plugname[$plugnum]</font></a> ";
		}elseif($status == "Administrator" || $status == "Super Administrator"){
			$pluglink .= "| <a href=\"$plugurl[$plugnum]\"><font class=\"navtd\">$plugname[$plugnum]</font></a> ";
		}
	}
}


if(!strstr($bgcolor, ".")) {
	$bgcode = "bgcolor=\"$bgcolor\"";
} else {
	$bgcode = "background=\"$imgdir/$bgcolor\"";
}

if(!strstr($catcolor, ".")) {
	$catbgcode = "bgcolor=\"$catcolor\"";
} else {
	$catbgcode = "background=\"$imgdir/$catcolor\"";
}

if(!strstr($top, ".")) {
	$topbgcode = "bgcolor=\"$top\"";
} else {
	$topbgcode = "background=\"$imgdir/$top\"";
}

if (strstr($boardimg, ",")){
	$flashlogo = explode(",",$boardimg);
	$logo = "<OBJECT classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://active.macromedia.com/flash2/cabs/swflash.cab#version=5,0,0,0\" ID=main WIDTH=$flashlogo[1] HEIGHT=$flashlogo[2]><PARAM NAME=movie VALUE=\"$imgdir/$flashlogo[0]\"><PARAM NAME=loop VALUE=false><PARAM NAME=menu VALUE=false><PARAM NAME=quality VALUE=best><EMBED src=\"$imgdir/$flashlogo[0]\" loop=false menu=false quality=best WIDTH=$flashlogo[1] HEIGHT=$flashlogo[2] TYPE=\"application/x-shockwave-flash\" PLUGINSPAGE=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\"></EMBED></OBJECT>";
} else {
	$logo = "<a href=\"index.php\"><img src=\"$imgdir/$boardimg\" alt=\"$bbname\" border=\"0\" /></a>";
}

// Get Most Common Templates

eval("\$css = \"".template("css")."\";");
eval("\$bbcodescript = \"".template("functions_bbcode")."\";");

$bbrulestxt = stripslashes(stripslashes($bbrulestxt));
$bboffreason = stripslashes(stripslashes($bboffreason));

if($bbstatus == "off" && !($status == "Administrator" || $status == "Super Administrator") && !preg_match("misc.php", $REQUEST_URI)){
        loadtemplates('header,footer');
        eval("\$header = \"".template("header")."\";");
        echo $header;
        $message = "$bboffreason";
        echo "<center>$message</center>";
        end_time();
        eval("\$footer = \"".template("footer")."\";");
        echo $footer;
        exit;
}

if($regviewonly == "on") {
        if($onlineuser == "xguest123" && $action != "reg" && $action != "login" && $action != "lostpw") {
        $message = "$lang_reggedonly <a href=\"member.php?action=reg\">$lang_textregister</a> $lang_textor <a href=\"misc.php?action=login\">$lang_textlogin</a>";
        loadtemplates('header,footer');
        eval("\$header = \"".template("header")."\";");
        echo $header;
        echo "<center>$message</center>";
		end_time();
        eval("\$footer = \"".template("footer")."\";");
        echo $footer;
        exit;
        }
}

$ips = explode(".", $onlineip);
$query = $db->query("SELECT id FROM $table_banned WHERE (ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')");
$result = $db->fetch_array($query);

if($status == "Banned" || ($result && (!$status || $status=="Member"))) {
        $message = "$lang_bannedmessage";
        loadtemplates('header,footer');
        eval("\$header = \"".template("header")."\";");
        echo $header;
        echo "<center>$message</center>";
		end_time();
        eval("\$footer = \"".template("footer")."\";");
        echo $footer;
        exit;
}

if($xmbuser) {
        $query = $db->query("SELECT * FROM $table_u2u WHERE msgto = '$xmbuser' AND folder = 'inbox' AND new = 'yes'");
        $newu2unum = $db->num_rows($query);
        if($newu2unum > 0) {
                $newu2umsg = "<a href=\"#\" onclick=\"Popup('u2u.php', 'Window', 550, 450);\">$lang_newu2u1 $newu2unum $lang_newu2u2</a>";
        }
}

?>
