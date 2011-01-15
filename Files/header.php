<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

// Anti-Hacking Patch

if(eregi("\?[0-9]+$", $_SERVER['REQUEST_URI'])){
 	exit("hacking attempt");
}



// Define Constants
	// Removal of the version information is against the XMB license agreement
	// You are NOT permitted to remove any copyright material from the XMB software
	// These strings can be pulled for use on any page as header is required by all XMB pages

	$versioncompany = 'Aventure Media & The XMB Group';
	$versionshort = 'XMB 1.8 Partagium Service Pack 2';
	$versiongeneral = 'XMB 1.8 SP2';
	$versionbuild = 2003100518;
	$versionlong = 'Powered by XMB 1.8 Partagium Final SP2';
	
	$server 	= substr(getenv('SERVER_SOFTWARE'), 0, 3);

	$cookiepath	= '';
	$cookiedomain	= '';
	
	$onlinetime 	= time();
	$mtime1 	= explode(' ', microtime());
	$starttime 	= $mtime1[1] + $mtime1[0];
	
	$navigation	= '';
	$bbcodescript	= '';
	$status		= '';
	
	error_reporting(E_ALL&~E_NOTICE);


// Resolve Server specific issues
	if($server == 'Apa') {
		$wookie = $server;
		$url = getenv('REQUEST_URI');
	}elseif($server == 'Mic'){
		$protocol = (getenv('HTTPS') == 'off') ? ('http://') : ('https://');
		$query = (getenv('QUERY_STRING')) ? ('?'.getenv('QUERY_STRING')) : ('');
			
		$url = $protocol.getenv('SERVER_NAME').getenv('SCRIPT_NAME').$query;
	}else{
		$url = getenv('REQUEST_URI');
	}


// Required Files - XMB (Version/Patch File) Configuration File, Database Settings File & Language Files
	require './xmb.php';
	require './config.php';
	require "./db/$database.php";
	require './functions.php';

// Patch to deal with some Action bugs
 
$pos = strpos($action,"&"); 
if ($pos != 0){ 
$action = substr_replace($action,"",$pos); 
} 

// End Patch


// Cache-control
	header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");


// Get visitors IP address
	if(getenv('HTTP_CLIENT_IP')) {
		$onlineip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR')) {
		$onlineip = getenv('HTTP_X_FORWARDED_FOR');
	} else {
		$onlineip = getenv('REMOTE_ADDR');
	}


// Security checks
	if (file_exists('./cinst.php')) { exit('Error: The file cinst.php was found on your server, please delete this file to continue.'); }
	if (file_exists('./index_log.log')) { exit('Error: The file index_log.log was found on your server, please delete this file to continue.'); }
	
	// Checks the format of the URL, blocks if necessary....
	if(eregi("\?[0-9]+$", $url)){
		exit("Invalid String Format, Please Check Your URL");
	}

// Patch to resolve XSS Cross-scripting and similar problems

	//Checks the IP-format, if it's not a IPv4, nor a IPv6 type, it will be blocked, safe to remove....
	if($ipcheck == 'on'){
		if(!eregi("^([0-9]{1,3}\.){3}[0-9]{1,3}$", $onlineip) && !eregi("^([a-z,0-9]{0,4}:){5}[a-z,0-9]{0,4}$", $onlineip)&& !stristr($onlineip, ':::::')){
			exit("Access to this website is currently not possible as your hostname/IP appears suspicous.");
		}
	}

	// Checks for various variables in the URL, if any of them is found, script is halted
	$url2 = str_replace("subscriptions","",$url);
	$url_check = Array('status', 'xmbuser', 'xmbpw','script','javascript');
	foreach ($url_check as $name) {
	 	if (eregi($name, $url2)){
	 		exit();
	 	}
	}

	// Checks to make sure fid, pid, and tid are interger
	if ($fid){$fid = (int)$fid;}
	if ($pid){$pid = (int)$pid;}
	if ($tid){$tid = (int)$tid;}


// Load Objects, and such
	$tables = array('attachments', 'banned', 'favorites', 'forums', 'members', 'posts', 'ranks', 'settings', 'smilies', 'templates', 'themes', 'threads', 'u2u', 'whosonline', 'words', 'restricted', 'buddys');
	foreach($tables as $name) {
		${'table_'.$name} = $tablepre.$name;
	}

	$db = new dbstuff;
	$tempcache = "";
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	
	
// Load a few constants
	define('XMB_VERSION', $versiongeneral);
	define('XMB_BUILD', $versionbuild);


// Create cookie-settings
	$old['url'] = $url;
	
	if(!$full_url || empty($full_url)){
		exit('<b>ERROR: </b><i>Please fill the $full_url variable in your config.php!</i>');
	}else{
		$replace 	= array('https://', 'http://', 'www');
		$url 		= str_replace($replace, '', $full_url);
		
		if(!strpos($url, '/')){
			$cookiepath 	= '';
			$cookiedomain 	= $url;
		}else{
			if(substr($url, 0, 9) == 'localhost' || preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/i", $url)){
				$cookiedomain  = '';
			}else{
				$cookiedomain  = substr($url, 0, strpos($url, '/'));
			}
			$cookiepath 	= strstr($url, '/');
		}
	}
	$url = $old['url'];
	
// Set cookies
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

// Make all settings global, and put them in the $SETTINGS[] array
	$settingsquery = $db->query("SELECT * FROM $table_settings");
	foreach($db->fetch_array($settingsquery) as $key => $val) {
		$$key = $val;
		$SETTINGS[$key] = $val;
	}

// Get the user-vars, and make them semi-global
	if(!isset($xmbuser)){
		$xmbuser	= '';
		$xmbpw		= '';
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
		$db->query("UPDATE $table_members SET lastvisit='$time' WHERE username='$xmbuser'");
	}else{
		$memtime	= '';
		$memdate	= '';
		$xmbuser 	= '';
		$self 		= array();
		$status		= '';
		$xmbpw		= '';
		$sig		= '';
		$themeuser	= false;
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


// Get the required language file
	$bblang = $langfile;
	require "lang/$langfile.lang.php";

// Reset Time() reading
	$time = time();

// Checks for the possibility to register
	if($regstatus == "on" && empty($self['username'])) {
		if($SETTINGS['coppa'] == 'on'){
			$reglink = "- <a href=\"member.php?action=coppa\">$lang_textregister</a>";
		}else{
			$reglink = "- <a href=\"member.php?action=reg\">$lang_textregister</a>";
		}
	}else{
		$reglink = '';
	}

// Creates login/logout links
	if(($xmbuser && $xmbuser != '') && !empty($self['username'])) {
		$loginout = "<a href=\"misc.php?action=logout\">$lang_textlogout</a>";
		$memcp = "<a href=\"memcp.php\">$lang_textusercp</a>";
		$onlineuser = $xmbuser;
		if($status == "Administrator" || $status == "Super Administrator") {
				$cplink = "- <a href=\"cp.php\">$lang_textcp</a>";
			}
		$notify = "$lang_loggedin $xmbuser<br />[$loginout - $memcp $cplink]";
	} else {
		$loginout = "<a href=\"misc.php?action=login\">$lang_textlogin</a>";
		$onlineuser = "xguest123";
		$status = "";
		$notify = "$lang_notloggedin [$loginout $reglink]";
	}

// Checks if the timeformat has been set, if not, use default
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

// Start Url->text transcription - rather simple, and not very good, but will be updated in a later version
	if(isset($tid) && !strstr($url, "/post.php")){
		$query = $db->query("SELECT f.theme, t.fid, t.subject FROM $table_forums f, $table_threads t WHERE f.fid=t.fid AND t.tid='$tid'");
		while($locate = $db->fetch_array($query)){
			$fid = $locate['fid'];
			$forumtheme = $locate['theme'];
			$location = "$lang_onlineviewthread $locate[subject]";
		}
	}elseif(isset($fid)  && !strstr($url, "/post.php")){
		$query = $db->query("SELECT theme, name FROM $table_forums WHERE fid='$fid'");
		while($locate = $db->fetch_array($query)){
			$forumtheme = $locate['theme'];
			$location = "$lang_onlineforumdisplay $locate[name]";
		}
	}elseif(strstr($url, "/memcp.php")){
		$location = "$lang_onlinememcp";
	}elseif(strstr($url, "/cp.php") || strstr($url, "/cp2.php")){
		$location = "$lang_onlinecp";
	}elseif(strstr($url, "/editprofile.php")){
		$location = "$lang_onlineeditprofile";
	}elseif(strstr($url, "/emailfriend.php")){
		$location = "$lang_onlineemailfriend";
	}elseif(strstr($url, "/faq.php")){
		$location = "$lang_onlinefaq";
	}elseif(strstr($url, "/index.php")){
		$location = "$lang_onlineindex";
	}elseif(strstr($url, "/member.php")){
		if($action == "reg"){
			$location = "$lang_onlinereg";
		}elseif($action == "viewpro"){
			$location = "$lang_onlineviewpro";
		}elseif($action == "coppa"){
			$location = "$lang_onlinecoppa";
		}       
	}elseif(strstr($url, "/misc.php")){
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
	}elseif(strstr($url, "/post.php")){
		if($action == "edit"){
			$location = "$lang_onlinepostedit";
		}elseif($action == "newthread"){
			$location = "$lang_onlinepostnewthread";
		}elseif($action == "reply"){
			$location = "$lang_onlinepostreply";
		}
	}elseif(strstr($url, "/stats.php")){
		$location = "$lang_onlinestats";
	}elseif(strstr($url, "/today.php")){
		$location = "$lang_onlinetodaysposts";
	}elseif(strstr($url, "/tools.php")){
		$location = "$lang_onlinetools";
	}elseif(strstr($url, "/topicadmin.php")){
		$location = "$lang_onlinetopicadmin";
	}elseif(strstr($url, "/u2u.php")){
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
		$location = $url;
	}

// Patch to resolve XSS cross-scripting other problems

// Set the whosonline
	$location = checkInput($location, '', '', 'javascript');
	$wollocation = addslashes($url);

	$wollocation = "<a href=\"$wollocation\">$location</a>";
	$newtime = time() - 600;
	$db->query("DELETE FROM $table_whosonline WHERE (ip='$onlineip' && username='xguest123') OR (username='$xmbuser') OR time<'$newtime'");
	$db->query("INSERT INTO $table_whosonline VALUES('$onlineuser', '$onlineip', '$onlinetime', '$wollocation')");

// Check what theme to use
	if($themeuser) {
		$theme = $themeuser;
	} elseif(!empty($forumtheme)) {
		$theme = $forumtheme;
	} else {
		$theme = $SETTINGS['theme'];
	}

// Make theme-vars semi-global
	$query = $db->query("SELECT * FROM $table_themes WHERE name='$theme'");
	foreach($db->fetch_array($query) as $key => $val) {
		if($key != "name") {
			$$key = $val;
		}
	}
	$imgdir = './'.$imgdir;
	
	// Alters certain visibility-variables
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
		$logo = '<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://active.macromedia.com/flash2/cabs/swflash.cab#version=6,0,0,0" ID=main WIDTH="'.$flashlogo[1].'" HEIGHT="'.$flashlogo[2].'"><PARAM NAME=movie VALUE="'.$imgdir.'/'.$flashlogo[0].'"><PARAM NAME="loop" VALUE="false"><PARAM NAME="menu" VALUE="false"><PARAM NAME="quality" VALUE="best"><EMBED src="'.$imgdir.'/'.$flashlogo[0].'" loop="false" menu="false" quality="best" WIDTH="'.$flashlogo[1].'" HEIGHT="'.$flashlogo[2].'" TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"></EMBED></OBJECT>';
	} else {
		$logo = '<a href="index.php"><img src="'.$imgdir.'/'.$boardimg.'" alt="'.$bbname.'" border="0" /></a>';
	}

// Font stuff...
	$fontedit = preg_replace('#(\D)#', '', $fontsize);
	$fontsuf  = preg_replace('#(\d)#', '', $fontsize);
	$font1 = $fontedit-1 . $fontsuf;
	$font3 = $fontedit+2 . $fontsuf;

// Update lastvisit in the header shown
	if($lastvisit && $xmbuser && $self['username'] != "") {
		$lastdate = gmdate("$dateformat",$xmblva + ($timeoffset * 3600) + ($addtime * 3600));
		$lasttime = gmdate("$timecode",$xmblva + ($timeoffset * 3600) + ($addtime * 3600));
		$lastvisittext = "$lang_lastactive $lastdate $lang_textat $lasttime";
	} else {
		$lastvisittext = "$lang_lastactive $lang_textnever";
	}

// Checks for various settings
	// Gzip-compression
	if($SETTINGS['gzipcompress'] == "on") {
		@ob_start('ob_gzhandler');
	}

// Patch to correct for extra pipe when option not selected
	
	
	$pipe = "";
	// Memberlist-link
	if($SETTINGS['memliststatus'] == "on") {
		$memlistlink = "<a href=\"misc.php?action=list\"><font class=\"navtd\">$lang_textmemberlist</font></a>";
		$pipe = " | ";
	} else {
		$memlistlink = "";
	}

	// Search-link
	if($SETTINGS['searchstatus'] == "on") {
		$searchlink = $pipe."<a href=\"misc.php?action=search\"><font class=\"navtd\">$lang_textsearch</font></a>";
		$pipe = " | ";
	} else {
		$searchlink = "";
	}
	
	// Faq-link
	if($SETTINGS['faqstatus'] == "on") {
		$faqlink = $pipe."<a href=\"faq.php\"><font class=\"navtd\">$lang_textfaq</font></a>";
		$pipe = " | ";
	} else {
		$faqlink = "";
	}
	
	// Today's posts-link
	if($SETTINGS['todaysposts'] == "on") {
		$todaysposts = $pipe."<a href=\"today.php\"><font class=\"navtd\">$lang_navtodaysposts</font></a>";
		$pipe = " | ";
	} else {
		$todaysposts = "";
	}

	// Stats-link
	if($SETTINGS['stats'] == "on") {
		$stats = $pipe."<a href=\"stats.php?action=view\"><font class=\"navtd\">$lang_navstats</font></a>";
	} else {
		$stats = "";
	}

//Show all plugins
	for($plugnum=1; $plugnum <= count($plugname); $plugnum++) {
		if(!empty($plugurl[$plugnum]) && !empty($plugname[$plugnum])) {
			if($plugadmin[$plugnum] != "yes"){
				$pluglink .= "| <a href=\"$plugurl[$plugnum]\"><font class=\"navtd\">$plugname[$plugnum]</font></a> ";
			}elseif($status == 'Administrator' || $status == 'Super Administrator'){
				$pluglink .= "| <a href=\"$plugurl[$plugnum]\"><font class=\"navtd\">$plugname[$plugnum]</font></a> ";
			}
		}
	}
	if(!isset($pluglink)){
		$pluglink = '';
	}


// Get Most Common Templates
	loadtemplates('header,footer,css,functions_bbcode');
	eval('$css = "'.template('css').'";');



// If the board is offline, display an appropriate message
	if($bbstatus == "off" && !($status == "Administrator" || $status == "Super Administrator") && !strstr($url, "misc.php") && !strstr($url, "member.php")){
		$bboffreason = stripslashes($bboffreason);
		eval("\$header = \"".template("header")."\";");
		echo $header;
		
		$message = $bboffreason;
		echo "<center>$message</center>";
		
		end_time();
		eval("\$footer = \"".template("footer")."\";");
		echo $footer;
		
		exit();
	}
// If the board is set to 'reg-only' use, check if someone is logged in, and if not display a message
	if($regviewonly == "on") {
		if($onlineuser == "xguest123" && $action != "reg" && $action != "login" && $action != "lostpw" && $action != "coppa") {
			if($coppa == 'on'){
				$message = "$lang_reggedonly <a href=\"member.php?action=coppa\">$lang_textregister</a> $lang_textor <a href=\"misc.php?action=login\">$lang_textlogin</a>";
			}else{
				$message = "$lang_reggedonly <a href=\"member.php?action=reg\">$lang_textregister</a> $lang_textor <a href=\"misc.php?action=login\">$lang_textlogin</a>";
			}
			eval("\$header = \"".template("header")."\";");
			echo $header;

			echo "<center>$message</center>";
			
			end_time();
			eval("\$footer = \"".template("footer")."\";");
			echo $footer;
			
			exit();
		}
	}

// Check if the user is ip-banned
	$ips = explode(".", $onlineip);
	$query = $db->query("SELECT id FROM $table_banned WHERE (ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')");
	$result = $db->fetch_array($query);

	if($status == "Banned" || ($result && (!$status || $status=="Member"))) {
		eval("\$header = \"".template("header")."\";");
		echo $header;

		echo "<center>$lang_bannedmessage;</center>";

		end_time();
		eval("\$footer = \"".template("footer")."\";");
		echo $footer;

		exit();
	}

// if the user is registered, check for new u2u's
	if($xmbuser) {
		$query = $db->query("SELECT * FROM $table_u2u WHERE msgto = '$xmbuser' AND folder = 'inbox' AND new = 'yes'");
		$newu2unum = $db->num_rows($query);
		if($newu2unum > 0) {
			$newu2umsg = "<a href=\"#\" onclick=\"Popup('u2u.php', 'Window', 550, 450);\">$lang_newu2u1 $newu2unum $lang_newu2u2</a>";
		}else{
			$newu2umsg = '';
		}
	}else{
		$newu2umsg = '';
	}
?>