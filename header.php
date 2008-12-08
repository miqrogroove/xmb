<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Beta 2 - This software should not be used for any purpose after 1 February 2009.
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2008, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * Copyright (c) 2007, iEntry, Inc.
 * http://www.ientry.com
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 **/


/* Front Matter */

if (!defined('X_SCRIPT')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}
if (!defined('ROOT')) define('ROOT', './');
error_reporting(E_ALL&~E_NOTICE);
define('IN_CODE', TRUE);
require ROOT.'include/global.inc.php';


/* Global Constants and Initialized Values */

$versioncompany = 'The XMB Group';
$versionshort = '1.9.11';
$versiongeneral = 'XMB 1.9.11';
$copyright = '2001-2008';
$alpha = '';
$beta = 'Beta 2';
$gamma = '';
$service_pack = '';
$versionbuild = 20081207;
$versionlong = 'Powered by '.$versiongeneral.' '.$alpha.$beta.$gamma.$service_pack;
$mtime = explode(" ", microtime());
$starttime = $mtime[1] + $mtime[0];
$onlinetime = time();
$time = $onlinetime;
$selHTML = 'selected="selected"';
$cheHTML = 'checked="checked"';
$server = substr($_SERVER['SERVER_SOFTWARE'], 0, 3);
$url = $_SERVER['REQUEST_URI'];
$onlineip = $_SERVER['REMOTE_ADDR'];

$cookiepath = '';
$cookiedomain = '';
$bbcodescript = '';
$database = '';
$threadSubject = '';
$filesize = 0;
$filename = '';
$filetype = '';
$full_url = '';
$navigation = '';
$newu2umsg = '';
$othertid = '';
$quickjump = '';
$status = '';
$xmbuser = '';
$xmbpw = '';

$SETTINGS = array();
$THEME = array();
$footerstuff = array();
$links = array();
$lang = array();
$mailer = array();
$plugadmin = array();
$plugimg = array();
$plugname = array();
$plugurl = array();
$tables = array(
'attachments',
'banned',
'buddys',
'captchaimages',
'favorites',
'forums',
'lang_base',
'lang_keys',
'lang_text',
'logs',
'members',
'posts',
'ranks',
'restricted',
'settings',
'smilies',
'templates',
'themes',
'threads',
'u2u',
'whosonline',
'words',
'vote_desc',
'vote_results',
'vote_voters'
);

define('X_CACHE_GET', 1);
define('X_CACHE_PUT', 2);
define('X_REDIRECT_HEADER', 1);
define('X_REDIRECT_JS', 2);
define('X_SET_HEADER', 1);
define('X_SET_JS', 2);
define('X_SHORTEN_SOFT', 1);
define('X_SHORTEN_HARD', 2);
// permissions constants
define('X_PERMS_COUNT', 4); //Number of raw bit sets stored in postperm setting.
// indexes used in permissions arrays
define('X_PERMS_RAWPOLL', 0);
define('X_PERMS_RAWTHREAD', 1);
define('X_PERMS_RAWREPLY', 2);
define('X_PERMS_RAWVIEW', 3);
define('X_PERMS_POLL', 40);
define('X_PERMS_THREAD', 41);
define('X_PERMS_REPLY', 42);
define('X_PERMS_VIEW', 43); //View is now = Rawview || Userlist
define('X_PERMS_USERLIST', 44);
define('X_PERMS_PASSWORD', 45);
// status string to bit field assignments
$status_enum = array(
'Super Administrator' => 1,
'Administrator'       => 2,
'Super Moderator'     => 4,
'Moderator'           => 8,
'Member'              => 16,
'Guest'               => 32,
''                    => 32,
'Reserved-Future-Use' => 64,
'Banned'              => (1 << 30)
); //$status['Banned'] == 2^30
// status bit to $lang key assignments
$status_translate = array(
1         => 'superadmin',
2         => 'textadmin',
4         => 'textsupermod',
8         => 'textmod',
16        => 'textmem',
32        => 'textguest1',
(1 << 30) => 'textbanned'
);

// discover the most likely browser
// so we can use bbcode specifically made for it
$browser = 'opera'; // default to opera
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    if (false !== strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') && false === strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) {
        $browser = 'mozilla';
    }
    if (false !== strpos($_SERVER['HTTP_USER_AGENT'], 'Opera')) {
        $browser = 'opera';
    }
    if (false !== strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
        $browser = 'ie';
    }
}
define('IS_MOZILLA', ($browser == 'mozilla'));
define('IS_OPERA', ($browser == 'opera'));
define('IS_IE', ($browser == 'ie'));


/* Load the Configuration Created by Install */

require ROOT.'config.php';

if (!defined('DEBUG')) {
    define('DEBUG', FALSE);
} elseif (DEBUG) {
    require(ROOT.'include/debug.inc.php');
}
if (!defined('LOG_MYSQL_ERRORS')) {
    define('LOG_MYSQL_ERRORS', FALSE);
}

$config_array = array(
'dbname' => 'DB/NAME',
'dbuser' => 'DB/USER',
'dbpw' => 'DB/PW',
'dbhost' => 'DB_HOST',
'database' => 'DB_TYPE',
'tablepre' => 'TABLE/PRE',
'full_url' => 'FULLURL',
'ipcheck' => 'IPCHECK',
'allow_spec_q' => 'SPECQ',
'show_full_info' => 'SHOWFULLINFO',
'comment_output' => 'COMMENTOUTPUT'
);
foreach($config_array as $key => $value) {
    if (${$key} === $value) {
        header('HTTP/1.0 500 Internal Server Error');
        exit('Configuration Problem: XMB noticed that your config.php has not been fully configured.<br />The $'.$key.' has not been configured correctly.<br /><br />Please configure config.php before continuing.<br />Refresh the browser after uploading the new config.php (when asked if you want to resubmit POST data, click the \'OK\'-button).');
    }
}
unset($config_array);


/* Validate URL Configuration and Security */

if (empty($full_url)) {
    header('HTTP/1.0 500 Internal Server Error');
    exit('<b>ERROR: </b><i>Please fill the $full_url variable in your config.php!</i>');
} else {
    $array = parse_url($full_url);

    $cookiesecure = ($array['scheme'] == 'https');

    $cookiedomain = $array['host'];
    if (strpos($cookiedomain, '.') === FALSE || preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $cookiedomain)) {
        $cookiedomain = '';
    } elseif (substr($cookiedomain, 0, 4) === 'www.') {
        $cookiedomain = substr($cookiedomain, 3);
    }

    if (!isset($array['path'])) {
        $array['path'] = '/';
    }
    $cookiepath = $array['path'];

    if (DEBUG) {
        debugURLsettings($cookiesecure, $cookiedomain, $cookiepath);
    }
    unset($array);
}

// Common XSS Protection: XMB disallows '<' in all URLs.
if (X_SCRIPT != 'search.php') {
    $url_check = Array('%3c', '<');
    foreach($url_check as $name) {
        if (strpos(strtolower($url), $name) !== FALSE) {
            header('HTTP/1.0 403 Forbidden');
            exit('403 Forbidden - URL rejected by XMB');
        }
    }
    unset($url_check);
}

// Check for double-slash problems in REQUEST_URI
if (substr($url, 0, strlen($cookiepath)) != $cookiepath Or substr($url, strlen($cookiepath), 1) == '/') {
    $fixed_url = str_replace('//', '/', $url);
    if (substr($fixed_url, 0, strlen($cookiepath)) != $cookiepath Or substr($fixed_url, strlen($cookiepath), 1) == '/' Or $fixed_url != preg_replace('/[^\x20-\x7e]/', '', $fixed_url)) {
        header('HTTP/1.0 404 Not Found');
        exit('XMB detected an invalid URL.  Set DEBUG to TRUE in config.php to see diagnostic details.');
    } else {
        $fixed_url = $full_url.substr($fixed_url, strlen($cookiepath));
        header('HTTP/1.0 301 Moved Permanently');
        header("Location: $fixed_url");
        exit('XMB detected an invalid URL');
    }
}


/* Assert Additional Security */

if (file_exists('./install/')) {
    header('HTTP/1.0 500 Internal Server Error');
    exit('<h1>Error:</h1><br />The installation files ("./install/") have been found on the server. Please remove them as soon as possible. If you have not yet installed XMB, please do so at this time. Just <a href="./install/index.php">click here</a>.');
}
if (file_exists('./Upgrade/') && !@rmdir('./Upgrade/') Or file_exists('./upgrade/') && !@rmdir('./upgrade/')) {
    header('HTTP/1.0 503 Service Unavailable');
    header('Retry-After: 3600');
    exit('<h1>Error:</h1><br />The upgrade tool ("./upgrade/") has been found on the server, but could not be removed. Please remove it as soon as possible.');
}
if (file_exists('./upgrade.php') And X_SCRIPT != 'upgrade.php') {
    $flag = FALSE;
    if (X_SADMIN) {
        $flag |= @unlink('./upgrade.php');
    }
    if (!$flag) {
        header('HTTP/1.0 503 Service Unavailable');
        header('Retry-After: 3600');
        exit('<h1>Error:</h1><br />The upgrade tool ("./upgrade.php") has been found on the server, but could not be removed. Please remove it as soon as possible.');
    }
}

//Checks the IP-format, if it's not a IPv4, nor a IPv6 type, it will be blocked, safe to remove....
if ($ipcheck == 'on') {
    if (!eregi("^([0-9]{1,3}\.){3}[0-9]{1,3}$", $onlineip) && !eregi("^([a-z,0-9]{0,4}:){5}[a-z,0-9]{0,4}$", $onlineip)&& !stristr($onlineip, ':::::')) {
        header('HTTP/1.0 403 Forbidden');
        exit("Access to this website is currently not possible as your hostname/IP appears suspicous.");
    }
}


/* Load Common Files and Establish Database Connection */

define('X_PREFIX', $tablepre); // Secured table prefix constant

require ROOT.'db/'.$database.'.php';
require ROOT.'include/validate.inc.php';
require ROOT.'include/functions.inc.php';

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

// Make all settings global, and put them in the $SETTINGS[] array
$squery = $db->query("SELECT * FROM ".X_PREFIX."settings");
foreach($db->fetch_array($squery) as $key => $val) {
    $$key = $val;
    $SETTINGS[$key] = $val;
}
$db->free_result($squery);

if ($postperpage < 5) {
    $postperpage = 30;
}

if ($topicperpage < 5) {
    $topicperpage = 30;
}

if ($memberperpage < 5) {
    $memberperpage = 30;
}

if ($onlinetodaycount < 5) {
    $onlinetodaycount = 30;
}

// Validate maxattachsize with PHP configuration.
$inimax = phpShorthandValue('upload_max_filesize');
if ($inimax < $SETTINGS['maxattachsize']) {
    $SETTINGS['maxattachsize'] = $inimax;
}
unset($inimax);


/* Set Global HTTP Headers */

if (X_SCRIPT != 'files.php') {
    header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

// Fix annoying bug in windows... *sigh*
$action = postedVar('action', '', FALSE, FALSE, FALSE, 'g');
if ($action != 'attachment' && !($action == 'templates' && isset($download)) && !($action == 'themes' && isset($download))) {
    header("Content-type: text/html");
}

// Update last visit cookies
$xmblva = getInt('xmblva', 'c'); // Last visit
$xmblvb = getInt('xmblvb', 'c'); // Duration of this visit (considered to be up to 600 seconds)

if ($xmblvb > 0) {
    $thetime = $xmblvb;     // lvb will expire in 600 seconds, so if it's there, we're in a current session
} else if ($xmblva > 0) {
    $thetime = $xmblva;     // Not currently logged in, so let's get the time from the last visit
} else {
    $thetime = $onlinetime; // no cookie at all, so this is your first visit
}

put_cookie('xmblva', $onlinetime, ($onlinetime + (86400*365)), $cookiepath, $cookiedomain); // lva == now
put_cookie('xmblvb', $thetime, ($onlinetime + 600), $cookiepath, $cookiedomain); // lvb =

$lastvisit = $thetime;
$lastvisit2 = $lastvisit - 540;

if (isset($oldtopics)) {
    put_cookie('oldtopics', $oldtopics, ($onlinetime+600), $cookiepath, $cookiedomain);
}


/* Authorize User, Set Up Session, and Load Language Translation */

$uinput = postedVar('xmbuser', '', FALSE, TRUE, FALSE, 'c');
if (!elevateUser($uinput, postedVar('xmbpw', '', FALSE, FALSE, FALSE, 'c'))) {
    // Delete cookies when authentication fails.
    if ($uinput != '') {
        put_cookie("xmbuser", '', 0, $cookiepath, $cookiedomain);
        put_cookie("xmbpw", '', 0, $cookiepath, $cookiedomain);
    }
}
unset($uinput);


/* Set Up HTML Templates and Themes */

// Create a base element so that links aren't broken if scripts are accessed using unexpected paths.
// XMB expects all links to be relative to $full_url + script name + query string.
$querystring = strstr($url, '?');
if ($querystring === FALSE) {
    $querystring = '';
}
$querystring = preg_replace('#[\\x00-\\x1F\\x7F-\\xFF]#', '', $querystring);
$baseelement = '<base href="'.$full_url.X_SCRIPT.attrOut($querystring).'" />';

// login/logout links
if (X_MEMBER) {
    if (X_ADMIN) {
        $cplink = ' - <a href="cp.php">'.$lang['textcp'].'</a>';
    } else {
        $cplink = '';
    }
    $loginout = '<a href="misc.php?action=logout">'.$lang['textlogout'].'</a>';
    $memcp = '<a href="memcp.php">'.$lang['textusercp'].'</a>';
    $u2ulink = "<a href=\"u2u.php\" onclick=\"Popup(this.href, 'Window', 700, 450); return false;\">{$lang['banu2u']}</a> - ";
    $notify = $lang['loggedin'].' <a href="member.php?action=viewpro&amp;member='.recodeOut($xmbuser).'">'.$xmbuser.'</a><br />['.$loginout.' - '.$u2ulink.''.$memcp.''.$cplink.']';

    // Update lastvisit in the header shown
    $theTime = $xmblva + ($self['timeoffset'] * 3600) + ($SETTINGS['addtime'] * 3600);
    $lastdate = gmdate($dateformat, $theTime);
    $lasttime = gmdate($timecode, $theTime);
    $lastvisittext = $lang['lastactive'].' '.$lastdate.' '.$lang['textat'].' '.$lasttime;
} else {
    // Checks for the possibility to register
    if ($SETTINGS['regstatus'] == 'on') {
        $reglink = '- <a href="member.php?action=coppa">'.$lang['textregister'].'</a>';
    } else {
        $reglink = '';
    }
    $loginout = '<a href="misc.php?action=login">'.$lang['textlogin'].'</a>';
    $notify = $lang['notloggedin'].' ['.$loginout.' '.$reglink.']';
    $lastvisittext = '';
}

// Get themes, [fid, [tid]]
if (isset($tid) && is_numeric($tid) && $action != 'templates') {
    $query = $db->query("SELECT f.fid, f.theme FROM ".X_PREFIX."forums f RIGHT JOIN ".X_PREFIX."threads t USING (fid) WHERE t.tid=$tid");
    $locate = $db->fetch_array($query);
    $db->free_result($query);
    $fid = $locate['fid'];
    $forumtheme = $locate['theme'];
} else if (isset($fid) && is_numeric($fid)) {
    $forum = getForum($fid);
    if (($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on') {
        $forumtheme = 0;
    } else {
        $forumtheme = $forum['theme'];
    }
}

// Check what theme to use
if ((int) $themeuser > 0) {
    $theme = (int) $themeuser;
} else if (!empty($forumtheme) && (int) $forumtheme > 0) {
    $theme = (int) $forumtheme;
} else {
    $theme = (int) $SETTINGS['theme'];
}

// Make theme-vars semi-global
$query = $db->query("SELECT * FROM ".X_PREFIX."themes WHERE themeid='$theme'");
foreach($db->fetch_array($query) as $key=>$val) {
    if ($key != "name") {
        $$key = $val;
    } else {
        $val = stripslashes($val);
    }
    $THEME[$key] = $val;
}
$db->free_result($query);

// additional CSS to load?
if (file_exists(ROOT.$imgdir.'/theme.css')) {
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

// Set Extra Theme Keys
$THEME['bgcode'] = $bgcode;
$THEME['font1'] = $font1;
$THEME['font3'] = $font3;

// Search-link
$searchlink = makeSearchLink();

// Faq-link
if ($SETTINGS['faqstatus'] == 'on') {
    $links[] = '<img src="'.$imgdir.'/top_faq.gif" alt="'.$lang['altfaq'].'" border="0" /> <a href="faq.php"><font class="navtd">'.$lang['textfaq'].'</font></a>';
}

// Memberlist-link
if ($SETTINGS['memliststatus'] == 'on') {
    $links[] = '<img src="'.$imgdir.'/top_memberslist.gif" alt="'.$lang['altmemberlist'].'" border="0" /> <a href="misc.php?action=list"><font class="navtd">'.$lang['textmemberlist'].'</font></a>';
}

// Today's posts-link
if ($SETTINGS['todaysposts'] == 'on') {
    $links[] = '<img src="'.$imgdir.'/top_todaysposts.gif" alt="'.$lang['alttodayposts'].'" border="0" /> <a href="today.php"><font class="navtd">'.$lang['navtodaysposts'].'</font></a>';
}

// Stats-link
if ($SETTINGS['stats'] == 'on') {
    $links[] = '<img src="'.$imgdir.'/top_stats.gif" alt="'.$lang['altstats'].'" border="0" /> <a href="stats.php"><font class="navtd">'.$lang['navstats'].'</font></a>';
}

// 'Forum Rules'-link
if ($SETTINGS['bbrules'] == 'on') {
    $links[] = '<img src="'.$imgdir.'/top_bbrules.gif" alt="'.$lang['altrules'].'" border="0" /> <a href="faq.php?page=forumrules"><font class="navtd">'.$lang['textbbrules'].'</font></a>';
}

$links = implode(' &nbsp; ', $links);

// Show all plugins
$pluglinks = array();
foreach($plugname as $plugnum => $item) {
    if (!empty($plugurl[$plugnum]) && !empty($plugname[$plugnum])) {
        if (trim($plugimg[$plugnum]) != '') {
            $img = '&nbsp;<img src="'.$plugimg[$plugnum].'" border="0" />&nbsp;';
        } else {
            $img = '';
        }

        if ($plugadmin[$plugnum] != true || X_ADMIN) {
            $pluglinks[] = $img.'<a href="'.$plugurl[$plugnum].'"><font class="navtd">'.$plugname[$plugnum].'</font></a>&nbsp;';
        }
    }
}

if (count($pluglinks) == 0) {
    $pluglink = '';
} else {
    $pluglink = implode('&nbsp;', $pluglinks);
}


/* HTML Ready.  Issue Any Global Alerts To User. */

// Check if the client is ip-banned
if ($SETTINGS['ip_banning'] == 'on' And !X_ADMIN) {
    $ips = explode(".", $onlineip);
    $query = $db->query("SELECT id FROM ".X_PREFIX."banned WHERE ((ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')) AND NOT (ip1='-1' AND ip2='-1' AND ip3='-1' AND ip4='-1')");
    $result = $db->num_rows($query);
    $db->free_result($query);
    if ($result > 0) {
        header('HTTP/1.0 403 Forbidden');
        eval('$css = "'.template('css').'";');
        error($lang['bannedmessage']);
    }
}

// Check if the board is offline
if ($SETTINGS['bbstatus'] == 'off' && !(X_ADMIN)) {
    $SETTINGS['quickjump_status'] = 'off';
    if (($action != 'reg' && $action != 'login' && $action != 'lostpw' && $action != 'coppa' && $action != 'captchaimage') || (X_SCRIPT != 'misc.php' && X_SCRIPT != 'member.php')) {
        header('HTTP/1.0 503 Service Unavailable');
        header('Retry-After: 3600');
        eval('$css = "'.template('css').'";');
        if ($bboffreason != '') {
            message(nl2br($bboffreason));
        } else {
            message($lang['textbstatusdefault']);
        }
    }
}

// Check if the board is set to 'reg-only'
if ($SETTINGS['regviewonly'] == 'on' && X_GUEST) {
    $SETTINGS['quickjump_status'] = 'off';
    if (($action != 'reg' && $action != 'login' && $action != 'lostpw' && $action != 'coppa' && $action != 'captchaimage') || (X_SCRIPT != 'misc.php' && X_SCRIPT != 'member.php')) {
        $message = $lang['reggedonly'].' <a href="member.php?action=coppa">'.$lang['textregister'].'</a> '.$lang['textor'].' <a href="misc.php?action=login">'.$lang['textlogin'].'</a>';
        eval('$css = "'.template('css').'";');
        message($message);
    }
}

// create forum jump
$quickjump = '';
if ($SETTINGS['quickjump_status'] == 'on') {
    $quickjump = forumJump();
}

// check for new u2u's
$newu2umsg = '';
if (X_MEMBER) {
    $query = $db->query("SELECT COUNT(*) FROM ".X_PREFIX."u2u WHERE owner='$xmbuser' AND folder='Inbox' AND readstatus='no'");
    $newu2unum = $db->result($query, 0);
    $db->free_result($query);
    if ($newu2unum > 0) {
        $newu2umsg = "<a href=\"u2u.php\" onclick=\"Popup(this.href, 'Window', 700, 450); return false;\">{$lang['newu2u1']} $newu2unum {$lang['newu2u2']}</a>";
        // Popup Alert
        if ($self['u2ualert'] == 2 Or ($self['u2ualert'] == 1 And X_SCRIPT == 'index.php')) {
            $newu2umsg .= '<script language="JavaScript" type="text/javascript">function u2uAlert() { ';
            if ($newu2unum == 1) {
                $newu2umsg .= 'u2uAlertMsg = "'.$lang['newu2u1'].' '.$newu2unum.$lang['u2ualert5'].'"; ';
            } else {
                $newu2umsg .= 'u2uAlertMsg = "'.$lang['newu2u1'].' '.$newu2unum.$lang['u2ualert6'].'"; ';
            }
            $newu2umsg .= "if (confirm(u2uAlertMsg)) { Popup('u2u.php', 'testWindow', 700, 450); } } setTimeout('u2uAlert();', 10);</script>";
        }
    }
}


/* Perform HTTP Connection Maintenance */

// Gzip-compression
if ($SETTINGS['gzipcompress'] == 'on'
 && $action != 'captchaimage'
 && X_SCRIPT != 'files.php'
 && X_SCRIPT != 'upgrade.php') {
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

// catch all unexpected output
if (headers_sent()) {
    header('HTTP/1.0 500 Internal Server Error');
    if (DEBUG) {
        headers_sent($filepath, $linenum);
        exit(cdataOut("Error: XMB failed to start due to file corruption.  Please inspect $filepath at line number $linenum."));
    } else {
        exit("Error: XMB failed to start.  Set DEBUG to TRUE in config.php to see file system details.");
    }
} else {
    return;
}
?>
