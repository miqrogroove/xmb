<?php
/**
 * XMB 1.9.5 Nexus Final SP1
 * © 2007 John Briggs
 * http://www.xmbmods.com
 * john@xmbmods.com
 *
 * Developed By The XMB Group
 * Copyright (c) 2001-2007, The XMB Group
 * http://www.xmbforum.com
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 **/

error_reporting(E_ALL&~E_NOTICE);

define('IN_CODE', true);
define('X_CACHE_GET', 1);
define('X_CACHE_PUT', 2);
define('X_SET_HEADER', 1);
define('X_SET_JS', 2);
define('X_SHORTEN_SOFT', 1);
define('X_SHORTEN_HARD', 2);

if (!defined('ROOT')) {
    define('ROOT', './');
}

// Resolving serveral modes (currently, 2)
// Debug-mode
/*
/ To turn on DEBUG mode (you can then see ALL queries done at the bottom of each screen (except buddy-list & u2u)
/ just uncomment this variable. These queries are ONLY visible to the user currently loading that page
/ and ONLY visible to Super Administrators
/
/ SECURITY NOTICE: DO NOT COMMENT OUT UNLESS YOU KNOW WHAT YOU'RE DOING!
*/
define('DEBUG', false);
// define('DEBUG', true);
//
/*
/ Comment first line and uncomment second line to use debug mode (1.9+ only). Only one define can be
/ active as define is immutable once set.
*/

// Resolve Server specific issues
$server = substr($_SERVER['SERVER_SOFTWARE'], 0, 3);
switch ($server) {
    case 'Aby':     // Abyss web server
        $protocol = (getenv('HTTPS') == 'off') ? ('http://') : ('https://');
        $query = (getenv('QUERY_STRING')) ? ('?'.getenv('QUERY_STRING')) : ('');
        $url = $protocol.getenv('SERVER_NAME').getenv('SCRIPT_NAME').$query;
        break;
    default:        // includes Apache and IIS using module and CGI forms
        $url = $_SERVER['REQUEST_URI'];
}

// Required Files - XMB (Version/Patch File) Configuration File, Database Settings File
require ROOT.'xmb.php';

// Initialising certain key variables. These are default values, please don't change them!
$cookiepath     = '';
$cookiedomain   = '';
$mtime          = explode(" ", microtime());
$starttime      = $mtime[1] + $mtime[0];
$onlinetime     = time();
$bbcodescript   = '';
$threadSubject  = '';
$self           = array();
$user           = (isset($user)) ? $user : '';
$SETTINGS       = array();
$THEME          = array();
$links          = array();
$lang           = array();
$plugname       = array();
$plugadmin      = array();
$plugurl        = array();
$plugimg        = array();
$footerstuff    = array();
$mailer         = array();
$selHTML        = 'selected="selected"';
$cheHTML        = 'checked="checked"';
$filesize       = 0;
$filename       = '';
$filetype       = '';

define('COMMENTOUTPUT', false);
define('MAXATTACHSIZE', 256000);
define('IPREG', 'on');
define('IPCHECK', 'off');
define('SPECQ', false);
define('SHOWFULLINFO', false);

require ROOT.'config.php';

if (DEBUG) {
    error_reporting(E_ALL);
}

// Initialise pre-set Variables
// These strings can be pulled for use on any page as header is required by all XMB pages
$versioncompany = 'The XMB Group';
$versionshort   = "XMB 1.9.5";
$versiongeneral = 'XMB 1.9.5 Nexus';
$copyright      = '2002-2007';
if ($show_full_info) {
    $alpha        = '';
    $beta         = '';
    $gamma        = 'Final';
    $service_pack = ' SP1';
    $versionbuild = 200707062320;
    $versionlong  = 'Powered by '.$versiongeneral.' ('.$alpha.$beta.$gamma.$service_pack.')'.(DEBUG === true ? ' (Debug)' : '');
} else {
    $alpha        = '';
    $beta         = '';
    $gamma        = '';
    $service_pack = '';
    $versionbuild = '[HIDDEN]';
    $versionlong  = "Powered by XMB".(DEBUG === true ? ' (Debug)' : '');
}

// discover the most likely browser
//  so we can use bbcode specifically made for it
//  this allows the use of various nice new features in eg mozilla
//  while others are available via IE and/or opera
$browser = 'mozilla'; // default to mozilla for now
if (false !== strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') && false === strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) {
    define('IS_MOZILLA',true);
    $browser = 'mozilla';
}

if (false !== strpos($_SERVER['HTTP_USER_AGENT'], 'Opera')) {
    define('IS_OPERA',  true);
    $browser = 'opera';
}

if (false !== strpos($_SERVER['HTTP_USER_AGENT'], '.NET CLR')) {
    define('IS_IE',     true);
    $browser = 'ie';
}

if(!defined('IS_MOZILLA')) {
    define('IS_MOZILLA',false);
}

if (!defined('IS_OPERA')) {
    define('IS_OPERA',  false);
}

if (!defined('IS_IE')) {
    define('IS_IE',     false);
}

// sanity check maximum registrations
if (!isset($max_reg_day) || $max_reg_day < 1 || $max_reg_day > 100) {
    $max_reg_day = 25;
}

if (!file_exists(ROOT.'db/'.$database.'.php')) {
    die('Error: XMB is not installed, or is configured incorrectly. <a href="install/index.php">Click Here to install XMB</a>');
}
require_once(ROOT.'db/'.$database.'.php');
require_once(ROOT.'functions.php');

// initialize navigation
$navigation = '';
nav();

// Cache-control
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Fix annoying bug in windows... *sigh*
$action = isset($action) ? $action : '';
if ($action != 'attachment' && !($action == 'templates' && isset($download)) && !($action == 'themes' && isset($download))) {
    header("Content-type: text/html");
}

// Security checks
if (file_exists('./install/') && !@unlink('./install/')) {
    exit('<h1>Error:</h1><br />The installation files ("./install/") have been found on the server, but could not be removed. Please remove them as soon as possible. If you have not yet installed XMB, please do so at this time. Just <a href="./install/index.php">click here</a>.');
}

if (file_exists('./cplogfile.php') && !@unlink('./cplogfile.php')) {
    exit('<h1>Error:</h1><br />The old logfile ("./cplogfile.php") has been found on the server, but could not be removed. Please remove it as soon as possible.');
}

if (file_exists('./fixhack.php') && !@unlink('./fixhack.php')) {
    exit('<h1>Error:</h1><br />The hack repair tool ("./fixhack.php") has been found on the server, but could not be removed. Please remove it as soon as possible.');
}

if (file_exists('./upgrade/') && !@unlink('./upgrade/')) {
    exit('<h1>Error:</h1><br />The upgrade tool ("./upgrade/") has been found on the server, but could not be removed. Please remove it as soon as possible.');
}

// Checks the format of the URL, blocks if necessary....
if (eregi("\?[0-9]+$", $url)) {
    exit("Invalid String Format, Please Check Your URL");
}

// Get visitors IP address (which is usually their transparent proxy)
// DO NOT USE HTTP_CLIENT_IP or HTTP_X_FORWARDED_FOR as these can (and are) forged by attackers. ajv
$onlineip = '';
if (isset($_SERVER['REMOTE_ADDR'])) {
    $onlineip = $_SERVER['REMOTE_ADDR'];
}

//Checks the IP-format, if it's not a IPv4, nor a IPv6 type, it will be blocked, safe to remove....
if ($ipcheck == 'on') {
    if (!eregi("^([0-9]{1,3}\.){3}[0-9]{1,3}$", $onlineip) && !eregi("^([a-z,0-9]{0,4}:){5}[a-z,0-9]{0,4}$", $onlineip)&& !stristr($onlineip, ':::::')) {
        exit("Access to this website is currently not possible as your hostname/IP appears suspicous.");
    }
}

// Checks for various variables in the URL, if any of them is found, script is halted
$url_check = Array('status=', 'xmbuser=', 'xmbpw=', '<script');
$url = urldecode($url);
foreach ($url_check as $name) {
    if (strpos(strtolower($url), $name)) {
        exit();
    }
}

// Load Objects, and such
$tables = array('attachments','banned','buddys','favorites','forums','logs','members','posts','ranks','restricted','settings','smilies','templates','themes','threads','u2u','whosonline','words');
foreach ($tables as $name) {
    ${'table_'.$name} = $tablepre.$name;
}

// Secured table prefix constant
define('X_PREFIX', $tablepre);

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

// Load a few constants
define('XMB_VERSION', $versiongeneral);
define('XMB_BUILD', $versionbuild);

define('X_REDIRECT_HEADER', 1);
define('X_REDIRECT_JS', 2);

// Create cookie-settings
if (!isset($full_url) || empty($full_url) || $full_url == 'FULLURL') {
    exit('<b>ERROR: </b><i>Please fill the $full_url variable in your config.php!</i>');
} else {
    $array = parse_url($full_url);
    if (substr($array['host'], 0, 9) == 'localhost' || preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/i", $array['host'])) {
        $cookiedomain  = '';
    } else {
        $cookiedomain = str_replace('www', '', $array['host']);
    }

    if (!isset($array['path'])) {
        $array['path'] = '/';
    }
    $cookiepath = $array['path'];
}

// Set cookies
put_cookie('xmblva', $onlinetime, ($onlinetime + (86400*365)), $cookiepath, $cookiedomain);

if (isset($xmblvb)) {
    $thetime = $xmblvb;
} elseif (isset($xmblva)) {
    $thetime = $xmblva;
} else {
    $thetime = $onlinetime;
}

put_cookie('xmblvb', $thetime, ($onlinetime + 600), $cookiepath, $cookiedomain);

$lastvisit = $thetime;
$lastvisit2 = $lastvisit - 540;

if (isset($oldtopics)) {
    put_cookie('oldtopics', $oldtopics, ($onlinetime+600), $cookiepath, $cookiedomain);
}

// Make all settings global, and put them in the $SETTINGS[] array
$squery = $db->query("SELECT * FROM $table_settings");
foreach ($db->fetch_array($squery) as $key => $val) {
    $$key = $val;
    $SETTINGS[$key] = $val;
}

if ($postperpage < 5) {
    $postperpage = 30;
}

if ($topicperpage < 5) {
    $topicperpage = 30;
}

// Get the user-vars, and make them semi-global
if (!isset($xmbuser)) {
    $xmbuser = '';
    $xmbpw = '';
    $self['status'] = '';
}

$q = false;
if ($xmbuser != '') {
    $query = $db->query("SELECT * FROM $table_members WHERE username='$xmbuser'");
    $userrec = $db->fetch_array($query);
    if ($db->num_rows($query) == 1 && $userrec['password'] == $xmbpw) {
        $q = true;
    }
    $db->free_result($query);
}

if ($q) {
    foreach ($userrec as $key => $val) {
        $self[$key] = $val;
    }

    define('X_MEMBER', true);
    define('X_GUEST', false);

    $timeoffset = $self['timeoffset'];
    $themeuser  = $self['theme'];
    $status     = $self['status'];
    $tpp        = $self['tpp'];
    $ppp        = $self['ppp'];
    $memtime    = $self['timeformat'];
    $memdate    = $self['dateformat'];
    $sig        = $self['sig'];
    $invisible  = $self['invisible'];
    $time       = $onlinetime;
    $langfile   = ($self['langfile'] == "" || !file_exists("lang/$self[langfile].lang.php")) ? $SETTINGS['langfile'] : $self['langfile'];

    if (!empty($theme)) {
        $themeuser = $self['theme'];
    }
    $db->query("UPDATE $table_members SET lastvisit=".$db->time($onlinetime)." WHERE username='$xmbuser'");
} else {
    define('X_MEMBER', false);
    define('X_GUEST', true);
    $timeoffset = $SETTINGS['def_tz'];
    $themeuser  = '';
    $status     = 'member';
    $tpp        = $SETTINGS['topicperpage'];
    $ppp        = $SETTINGS['postperpage'];
    $memtime    = '';
    $memdate    = '';
    $sig        = '';
    $invisible  = 0;
    $time       = $onlinetime;
    $langfile   = $SETTINGS['langfile'];
    $self['ban'] = '';
    $self['sig'] = '';
}

if ($memtime == '') {
    if ($timeformat == 24) {
        $timecode = "H:i";
    } else {
        $timecode = "h:i A";
    }
} else {
    if ($memtime == 24) {
        $timecode = "H:i";
    } else {
        $timecode = "h:i A";
    }
}

// Fix by John Briggs Begin
$role = array();
$role['sadmin'] = false;
$role['admin']  = false;
$role['smod']   = false;
$role['mod']    = false;
$role['staff']  = false;
if (X_MEMBER) {
    switch ($self['status']) {
        case 'Super Administrator':
            $role['sadmin'] = true;
            $role['admin']  = true;
            $role['smod']   = true;
            $role['mod']    = true;
            $role['staff']  = true;
            break;
        case 'Administrator':
            $role['sadmin'] = false;
            $role['admin']  = true;
            $role['smod']   = true;
            $role['mod']    = true;
            $role['staff']  = true;
            break;
        case 'Super Moderator':
            $role['sadmin'] = false;
            $role['admin']  = false;
            $role['smod']   = true;
            $role['mod']    = true;
            $role['staff']  = true;
            break;
        case 'Moderator':
            $role['sadmin'] = false;
            $role['admin']  = false;
            $role['smod']   = false;
            $role['mod']    = true;
            $role['staff']  = true;
            break;
        default:
            $role['sadmin'] = false;
            $role['admin']  = false;
            $role['smod']   = false;
            $role['mod']    = false;
            $role['staff']  = false;
            break;
    }
}
define('X_SADMIN', $role['sadmin']);
define('X_ADMIN', $role['admin']);
define('X_SMOD', $role['smod']);
define('X_MOD', $role['smod']);
define('X_STAFF', $role['staff']);
// Fix by John Briggs End

// Get the required language file
if (!file_exists(ROOT.'lang/'.$langfile.'.lang.php')) {
    exit('You do not have a language file present. Please upload one to proceed with use of the bulletin board.');
}
require_once(ROOT.'lang/'.$langfile.'.lang.php');

// Checks for the possibility to register
if ($regstatus == "on" && X_GUEST) {
    $reglink = "- <a href=\"member.php?action=coppa\">$lang[textregister]</a>";
} else {
    $reglink = '';
}

// Creates login/logout links
if (X_MEMBER) {
    $loginout = '<a href="misc.php?action=logout">'.$lang['textlogout'].'</a>';
    $memcp = '<a href="memcp.php">'.$lang['textusercp'].'</a>';
    $onlineuser = $xmbuser;
    $cplink = '';
    $u2ulink = "<a href=\"#\" onclick=\"Popup('u2u.php', 'Window', 700, 450);\">$lang[banu2u]</a> - ";

    if (X_ADMIN) {
        $cplink = " - <a href=\"cp.php\">$lang[textcp]</a>";
    }

    $notify = "$lang[loggedin] <a href=\"member.php?action=viewpro&amp;member=".rawurlencode($onlineuser)."\">$xmbuser</a><br />[$loginout - $u2ulink$memcp$cplink]";
} else {
    $loginout = "<a href=\"misc.php?action=login\">$lang[textlogin]</a>";
    $onlineuser = 'xguest123';
    $self['status'] = "";
    $notify = "$lang[notloggedin] [$loginout $reglink]";
}

// Checks if the timeformat has been set, if not, use default
if ($memdate == '') {
    $dateformat = $dateformat;
} else {
    $dateformat = $memdate;
}

$dformatorig = $dateformat;
$dateformat = str_replace(array('mm','dd','yyyy','yy'), array('n','j','Y','y'), $dateformat);

// Get themes, [fid, [tid]]
if (isset($tid) && $action != 'templates') {
    $query = $db->query("SELECT f.fid, f.theme, t.subject FROM $table_forums f, $table_threads t WHERE f.fid=t.fid AND t.tid='$tid'");
    $locate = $db->fetch_array($query);
    $fid = $locate['fid'];
    $forumtheme = $locate['theme'];
    if ($SETTINGS['subject_in_title'] == 'on') {
        $threadSubject = '- '.stripslashes($locate['subject']);
    } else {
        $threadSubject = '';
    }
} else if (isset($fid)) {
    $q = $db->query("SELECT theme FROM $table_forums WHERE fid='$fid'");
    if ($db->num_rows($q) === 1) {
        $forumtheme = $db->result($q, 0);
    } else {
        $forumtheme = 0;
    }
}

$wollocation = addslashes($url);
$newtime = $onlinetime - 600;

// clear out old entries and guests
$db->query("DELETE FROM $table_whosonline WHERE ((ip = '$onlineip' && username = 'xguest123') OR (username = '$xmbuser') OR (time < '$newtime'))");
$db->query("INSERT INTO $table_whosonline (username, ip, time, location, invisible) VALUES ('$onlineuser', '$onlineip', ".$db->time($onlinetime).", '$wollocation', '$invisible')");

// Find duplicate entries for users only
$username = isset($username) ? $username : '';
if (X_MEMBER) {
    $result = $db->query("SELECT count(username) FROM $table_whosonline WHERE (username = '$xmbuser')");
    $usercount = $db->result($result, 0);
    if ($usercount > 1) {
        $db->query("DELETE FROM $table_whosonline WHERE (username = '$xmbuser')");
        $db->query("INSERT INTO $table_whosonline (username, ip, time, location, invisible) VALUES ('$onlineuser', '$onlineip', ".$db->time($onlinetime).", '$wollocation', '$invisible')");
    }
}

// Check what theme to use
if ((int) $themeuser > 0) {
    $theme = (int) $themeuser;
} elseif (!empty($forumtheme) && (int) $forumtheme > 0) {
    $theme = $forumtheme;
} else {
    $theme = $SETTINGS['theme'];
}

// Make theme-vars semi-global
$query = $db->query("SELECT * FROM $table_themes WHERE themeid='$theme'");
foreach ($db->fetch_array($query) as $key => $val) {
    if ($key != "name") {
        $$key = $val;
    } else {
        // make themes with apostrophes safe to display
        $val = stripslashes($val);
    }
    $THEME[$key] = $val;
}
$imgdir = './'.$imgdir;

// additional CSS to load?
if (file_exists($imgdir.'/theme.css')) {
    $cssInclude = '<style type="text/css">'."\n"."@import url('".$imgdir."/theme.css');"."\n".'</style>';
} else {
    $cssInclude = '';
}

// Alters certain visibility-variables
if (false === strpos($bgcolor, '.')) {
    $bgcode = "background-color: $bgcolor;";
} else {
    $bgcode = "background-image: url('$imgdir/$bgcolor');";
}

if (false === strpos($catcolor, '.')) {
    $catbgcode = "bgcolor=\"$catcolor\"";
    $catcss = 'background-color: '.$catcolor.';';
} else {
    $catbgcode = "style=\"background-image: url($imgdir/$catcolor)\"";
    $catcss = 'background-image: url('.$imgdir.'/'.$catcolor.');';
}

if (false === strpos($top, '.')) {
    $topbgcode = "bgcolor=\"$top\"";
} else {
    $topbgcode = "style=\"background-image: url($imgdir/$top)\"";
}

if (false !== strpos($boardimg, ',')) {
    $flashlogo = explode(",",$boardimg);
    //check if it's an URL or just a filename
    $l = array();
    $l = parse_url($flashlogo[0]);
    if (!isset($l['scheme']) || !isset($l['host'])) {
        $flashlogo[0] = $imgdir.'/'.$flashlogo[0];
    }
    $logo = '<object type="application/x-shockwave-flash" data="'.$flashlogo[0].'" width="'.$flashlogo[1].'" height="'.$flashlogo[2].'"><param name="movie" value="'.$flashlogo[0].'" /><param name="AllowScriptAccess" value="never" /></object>';
} else {
    $l = array();
    $l = parse_url($boardimg);
    if (!isset($l['scheme']) || !isset($l['host'])) {
        $boardimg = $imgdir.'/'.$boardimg;
    }
    $logo = '<a href="index.php"><img src="'.$boardimg.'" alt="'.$bbname.'" border="0" /></a>';
}

// Font stuff...
$fontedit = preg_replace('#(\D)#', '', $fontsize);
$fontsuf = preg_replace('#(\d)#', '', $fontsize);

$font1 = $fontedit-1 . $fontsuf;
$font3 = $fontedit+2 . $fontsuf;

// Update lastvisit in the header shown
if (isset($lastvisit) && X_MEMBER) {
    $theTime = $xmblva + ($timeoffset * 3600) + ($addtime * 3600);
    $lastdate = gmdate($dateformat, $theTime);
    $lasttime = gmdate($timecode, $theTime);
    $lastvisittext = "$lang[lastactive] $lastdate $lang[textat] $lasttime";
} else {
    $lastvisittext = "$lang[lastactive] $lang[textnever]";
}

// Checks for various settings
if (empty($action)) {
    $action = NULL;
}

// Gzip-compression
if ($SETTINGS['gzipcompress'] == "on" && $action != "attachment") {
    if (($res = @ini_get('zlib.output_compression')) === 1) {
        // leave it
    } else if ($res === false) {
        // ini_get not supported. So let's just leave it
    } else {
        if (function_exists('gzopen')) {
            $r = @ini_set('zlib.output_compression', 'On');
            $r2 = @ini_set('zlib.output_compression_level', '3');
            if (!$r || !$r2) {
                ob_start('ob_gzhandler');
            }
        } else {
            ob_start('ob_gzhandler');
        }
    }
}

// Search-link
if ($SETTINGS['searchstatus'] == "on") {
    $links[] = "<img src=\"$imgdir/search.gif\" alt=\"$lang[altsearch]\" border=\"0\" /> <a href=\"misc.php?action=search\"><font class=\"navtd\">$lang[textsearch]</font></a>";
}

// Faq-link
if ($SETTINGS['faqstatus'] == "on") {
    $links[] = "<img src=\"$imgdir/faq.gif\" alt=\"$lang[altfaq]\" border=\"0\" /> <a href=\"faq.php\"><font class=\"navtd\">$lang[textfaq]</font></a>";
}

// Memberlist-link
if ($SETTINGS['memliststatus'] == "on") {
    $links[] = "<img src=\"$imgdir/members_list.gif\" alt=\"$lang[altmemberlist]\" border=\"0\" /> <a href=\"misc.php?action=list\"><font class=\"navtd\">$lang[textmemberlist]</font></a>";
}

// Today's posts-link
if ($SETTINGS['todaysposts'] == "on") {
    $links[] = "<img src=\"$imgdir/todays_posts.gif\" alt=\"$lang[alttodayposts]\" border=\"0\" /> <a href=\"today.php\"><font class=\"navtd\">$lang[navtodaysposts]</font></a>";
}

// Stats-link
if ($SETTINGS['stats'] == "on") {
    $links[] = "<img src=\"$imgdir/stats.gif\" alt=\"$lang[altstats]\" border=\"0\" /> <a href=\"stats.php\"><font class=\"navtd\">$lang[navstats]</font></a>";
}

// 'Forum Rules'-link
if ($SETTINGS['bbrules'] == "on") {
    $links[] = "<img src=\"$imgdir/bbrules.gif\" alt=\"$lang[altrules]\" border=\"0\" /> <a href=\"faq.php?page=forumrules\"><font class=\"navtd\">$lang[textbbrules]</font></a>";
}

$links = implode(' &nbsp; ', $links);

// Show all plugins
$pluglinks = array();
foreach ($plugname as $plugnum => $item) {
    if (!empty($plugurl[$plugnum]) && !empty($plugname[$plugnum])) {
        if (trim($plugimg[$plugnum]) != '') {
            $img = '<img src="'.$plugimg[$plugnum].'" border="0" /> ';
        } else {
            $img = '';
        }

        if ($plugadmin[$plugnum] != true || X_ADMIN) {
            $pluglinks[] = " &nbsp; $img<a href=\"$plugurl[$plugnum]\"><font class=\"navtd\">$plugname[$plugnum]</font></a>";
        }
    }
}

if (count($pluglinks) == 0) {
    $pluglink = '';
} else {
    $pluglink = implode(' &nbsp; ', $pluglinks);
}

// If the board is offline, display an appropriate message
if ($bbstatus == 'off' && !(X_ADMIN) && false === strpos($url, 'misc.php') && false === strpos($url, 'member.php')) {
    eval('$css = "'.template('css').'";');
    error(stripslashes($bboffreason));
}

// If the board is set to 'reg-only' use, check if someone is logged in, and if not display a message
if ($regviewonly == "on") {
    if (X_GUEST && $action != 'reg' && $action != 'login' && $action != 'lostpw' && $action != 'coppa') {
        if ($coppa == 'on') {
            $message = "$lang[reggedonly] <a href=\"member.php?action=coppa\">$lang[textregister]</a> $lang[textor] <a href=\"misc.php?action=login\">$lang[textlogin]</a>";
        } else {
            $message = "$lang[reggedonly] <a href=\"member.php?action=reg\">$lang[textregister]</a> $lang[textor] <a href=\"misc.php?action=login\">$lang[textlogin]</a>";
        }
        eval('$css = "'.template('css').'";');
        error($message);
    }
}

// Check if the user is ip-banned
$ips = explode(".", $onlineip);
// also disable 'ban all'-possibility
$query = $db->query("SELECT id FROM $table_banned WHERE ((ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')) AND NOT (ip1='-1' AND ip2='-1' AND ip3='-1' AND ip4='-1')");
$result = $db->fetch_array($query);

// don't *ever* ban a (super-)admin!
if (!X_ADMIN && ($self['status'] == 'Banned' || $result)) {
    eval('$css = "'.template('css').'";');
    error($lang['bannedmessage']);
}

// if the user is registered, check for new u2u's
$newu2umsg = '';
if (X_MEMBER) {
    $query = $db->query("SELECT COUNT(readstatus) FROM $table_u2u WHERE owner='$self[username]' AND folder='Inbox' AND readstatus='no'");
    $newu2unum = $db->result($query, 0);
    if ($newu2unum > 0) {
        $newu2umsg = "<a href=\"#\" onclick=\"Popup('u2u.php', 'Window', 700, 450);\">$lang[newu2u1] $newu2unum $lang[newu2u2]</a>";
    }
}
?>