<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2021, The XMB Group
 * https://www.xmbforum2.com/
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 **/


/* Front Matter */

if (!defined('X_SCRIPT')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}
if (!defined('ROOT')) define('ROOT', './');
error_reporting(-1); // Report all errors until config.php loads successfully.
define('IN_CODE', TRUE);
require ROOT.'include/global.inc.php';


/* Global Constants and Initialized Values */

$versioncompany = 'The XMB Group';
$versionshort = '1.9.12';
$versiongeneral = 'XMB 1.9.12';
$copyright = '2001-2021';
$alpha = '';
$beta = '';
$gamma = '';
$service_pack = '';
$versionbuild = 20210416;
$mtime = explode(" ", microtime());
$starttime = $mtime[1] + $mtime[0];
$onlinetime = time();
$time = $onlinetime;
$selHTML = 'selected="selected"';
$cheHTML = 'checked="checked"';
$server = substr($_SERVER['SERVER_SOFTWARE'], 0, 3);
$url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$onlineip = $_SERVER['REMOTE_ADDR'];

$canonical_link = '';
$cookiepath = '';
$cookiedomain = '';
$bbcodescript = '';
$cssInclude = '';
$database = '';
$threadSubject = '';
$filesize = 0;
$filename = '';
$filetype = '';
$full_url = '';
$navigation = '';
$newu2umsg = '';
$othertid = '';
$password = '';
$pluglink = '';
$quickjump = '';
$searchlink = '';
$smiliesnum = 0;
$status = '';
$wordsnum = 0;
$xmbuser = '';
$xmbpw = '';

$SETTINGS = array();
$THEME = array();
$censorcache = array();
$footerstuff = array();
$links = '';
$lang = array();
$mailer = array();
$plugadmin = array();
$plugimg = array();
$plugname = array();
$plugurl = array();
$smiliecache = array();

define('X_CACHE_GET', 1);
define('X_CACHE_PUT', 2);
define('X_NONCE_AYS_EXP', 300); // Yes/no prompt expiration, in seconds.
define('X_NONCE_FORM_EXP', 3600); // Form expiration, in seconds.
define('X_NONCE_MAX_AGE', 86400); // CAPTCHA expiration, in seconds.
define('X_NONCE_KEY_LEN', 12); // Size of captchaimages.imagestring.
define('X_ONLINE_TIMER', 600); // Visitors are offline after this many seconds.
define('X_REDIRECT_HEADER', 1);
define('X_REDIRECT_JS', 2);
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

assertEmptyOutputStream('header.php or global.inc.php');


/* Load the Configuration Created by Install */

require ROOT.'config.php';
assertEmptyOutputStream('config.php');

if (!$show_full_info) {
    $versionshort = '';
    $versiongeneral = 'XMB';
    $alpha = '';
    $beta = '';
    $gamma = '';
    $service_pack = '';
    $versionbuild = '[HIDDEN]';
} else {
    $versiongeneral .= ' ';
}
$versionlong = 'Powered by '.$versiongeneral.$alpha.$beta.$gamma.$service_pack;

if (!defined('DEBUG')) define('DEBUG', FALSE);
if (!defined('LOG_MYSQL_ERRORS')) define('LOG_MYSQL_ERRORS', FALSE);

if (DEBUG) {
    require(ROOT.'include/debug.inc.php');
    assertEmptyOutputStream('debug.inc.php');
} else {
    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
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
        if (file_exists(ROOT.'install/')) {
            exit('<h1>Error:</h1><br />The installation files ("./install/") have been found on the server. Please remove them as soon as possible. If you have not yet installed XMB, please do so at this time. Just <a href="./install/index.php">click here</a>.');
        }
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
    } elseif (0 == strlen($url)) {
        header('HTTP/1.0 500 Internal Server Error');
        exit('Error: URL Not Found.  Set DEBUG to TRUE in config.php to see diagnostic details.');
    } elseif ( $cookiesecure && $_SERVER['HTTPS'] !== 'on' ) {
        header('HTTP/1.0 404 Not Found');
        exit('XMB is configured for HTTPS access only.  Set DEBUG to TRUE in config.php to see diagnostic details.');
    }
    unset($array);
}

// Common XSS Protection: XMB disallows '<' and unencoded ':/' in all URLs.
if (X_SCRIPT != 'search.php') {
    $url_check = Array('%3c', '<', ':/');
    foreach($url_check as $name) {
        if (strpos(strtolower($url), $name) !== FALSE) {
            header('HTTP/1.0 403 Forbidden');
            exit('403 Forbidden - URL rejected by XMB');
        }
    }
    unset($url_check);
}

// Check for double-slash problems in REQUEST_URI
if (substr($url, 0, strlen($cookiepath)) != $cookiepath || substr($url, strlen($cookiepath), 1) == '/') {
    $fixed_url = str_replace('//', '/', $url);
    if (substr($fixed_url, 0, strlen($cookiepath)) != $cookiepath || substr($fixed_url, strlen($cookiepath), 1) == '/' || $fixed_url != preg_replace('/[^\x20-\x7e]/', '', $fixed_url)) {
        header('HTTP/1.0 404 Not Found');
        exit('XMB detected an invalid URL.  Set DEBUG to TRUE in config.php to see diagnostic details.');
    } else {
        $fixed_url = $full_url.substr($fixed_url, strlen($cookiepath));
        header('HTTP/1.0 301 Moved Permanently');
        header("Location: $fixed_url");
        exit('XMB detected an invalid URL');
    }
}

//Checks the IP-format, if it's not a IPv4 type, it will be blocked, safe to remove....
if ($ipcheck == 'on') {
    if (1 != preg_match('@^(\\d{1,3}\\.){3}\\d{1,3}$@', $onlineip)) {
        header('HTTP/1.0 403 Forbidden');
        exit("Access to this website is currently not possible as your hostname/IP appears suspicous.");
    }
}

// Force upgrade to mysqli when available.
if ( 'mysql' === $database ) $database = 'mysqli';
if ( ! extension_loaded( 'mysqli' ) ) {
    header('HTTP/1.0 500 Internal Server Error');
    exit("Error: The PHP mysqli extension is missing.");
}

/* Load Common Files and Establish Database Connection */

define('X_PREFIX', $tablepre); // Secured table prefix constant

require ROOT.'db/'.$database.'.php';
assertEmptyOutputStream('db/'.$database.'.php');

require ROOT.'include/sql.inc.php';
assertEmptyOutputStream('sql.inc.php');

require ROOT.'include/validate.inc.php';
assertEmptyOutputStream('validate.inc.php');

require ROOT.'include/functions.inc.php';
assertEmptyOutputStream('functions.inc.php');

require ROOT.'include/sessions.inc.php';
assertEmptyOutputStream('sessions.inc.php');

require ROOT.'include/tokens.inc.php';
assertEmptyOutputStream('tokens.inc.php');

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, TRUE);

// Make all settings global, and put them in the $SETTINGS[] array
// This is the first query, so do not panic unless query logging is enabled.
$squery = $db->query("SELECT * FROM ".X_PREFIX."settings", (DEBUG && LOG_MYSQL_ERRORS));
// Assume XMB is not installed if first query fails.
if (FALSE === $squery) {
    header('HTTP/1.0 500 Internal Server Error');
    if (file_exists(ROOT.'install/')) {
        exit('XMB is not yet installed. Please do so at this time. Just <a href="./install/index.php">click here</a>.');
    }
    exit('Fatal Error: XMB is not installed. Please upload the /install/ directory to begin.');
}
if ($db->num_rows($squery) == 0) {
    header('HTTP/1.0 500 Internal Server Error');
    exit('Fatal Error: The XMB settings table is empty.');
}
// Check schema for upgrade compatibility back to 1.8 SP2.
$row = $db->fetch_array( $squery );
if ( isset( $row['langfile'] ) ) {
    // Schema version <= 4 has only one row.
    foreach ( $row as $key => $val ) {
        $SETTINGS[$key] = $val;
    }
    if ( ! isset( $SETTINGS['schema_version'] ) ) {
        $SETTINGS['schema_version'] = '0';
    }
} else {
    // Current schema uses a separate row for each setting.
    do {
        $SETTINGS[$row['name']] = $row['value'];
    } while ( $row = $db->fetch_array( $squery ) );
}
$db->free_result( $squery );
unset( $row );

if ( (int) $SETTINGS['postperpage'] < 5 ) {
    $SETTINGS['postperpage'] = '30';
}

if ( (int) $SETTINGS['topicperpage'] < 5 ) {
    $SETTINGS['topicperpage'] = '30';
}

if ( (int) $SETTINGS['memberperpage'] < 5 ) {
    $SETTINGS['memberperpage'] = '30';
}

if ( (int) $SETTINGS['smcols'] < 1 ) {
    $SETTINGS['smcols'] = '4';
}

// The latest upgrade script advertises compatibility with v1.8 SP2.  These defaults might not exist yet.
if ( empty( $SETTINGS['onlinetodaycount'] ) || (int) $SETTINGS['onlinetodaycount'] < 5 ) {
    $SETTINGS['onlinetodaycount'] = '30';
}

if ( empty( $SETTINGS['captcha_code_length'] ) || (int) $SETTINGS['captcha_code_length'] < 3 || (int) $SETTINGS['captcha_code_length'] >= X_NONCE_KEY_LEN ) {
    $SETTINGS['captcha_code_length'] = '8';
}

if ( empty( $SETTINGS['ip_banning'] ) ) {
    $SETTINGS['ip_banning'] == 'off';
}

if ( empty( $SETTINGS['schema_version'] ) ) {
    $SETTINGS['schema_version'] == '0';
}

// Validate maxattachsize with PHP configuration.
$inimax = phpShorthandValue('upload_max_filesize');
if ( empty( $SETTINGS['maxattachsize'] ) || $inimax < (int) $SETTINGS['maxattachsize'] ) {
    $SETTINGS['maxattachsize'] = $inimax;
}
unset($inimax);

// XMB settings are historically available as individual variables.
extract( $SETTINGS );


/* Set Global HTTP Headers */

if ( X_SCRIPT != 'files.php' && X_SCRIPT != 'css.php' ) {
    header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

ini_set('user_agent', "XMB-eXtreme-Message-Board/1.9; $full_url");

$oldtopics = postedVar( 'oldtopics', null, false, false, false, 'c' );
if ( X_SCRIPT != 'viewthread.php' && ! empty( $oldtopics ) ) {
    put_cookie('oldtopics', $oldtopics, ($onlinetime + X_ONLINE_TIMER));
}

if ( X_SCRIPT == 'upgrade.php' && (int) $SETTINGS['schema_version'] < 5 ) {
    define( 'X_SADMIN', \XMB\SQL\checkUpgradeOldLogin( postedVar( 'xmbuser', '', true, false, false, 'c' ), postedVar( 'xmbpw', '', false, false, false, 'c' ) ) );
    return;
}


/* Authorize User, Set Up Session, and Load Language Translation */

$serror = '';
$action = postedVar('action', '', FALSE, FALSE, FALSE, 'g');

// Check if the client is ip-banned
if ($SETTINGS['ip_banning'] == 'on') {
    $ips = explode(".", $onlineip);
    $query = $db->query("SELECT id FROM ".X_PREFIX."banned WHERE ((ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')) AND NOT (ip1='-1' AND ip2='-1' AND ip3='-1' AND ip4='-1')");
    $result = $db->num_rows($query);
    $db->free_result($query);
    if ($result > 0) {
        // Block all non-admins
        $serror = 'ip';
    }
}

// Check other access restrictions
if ( '' == $serror ) {
    if ( (int) $SETTINGS['schema_version'] < 5 ) {
        // During upgrade of session system, no features are available.
        $serror = 'bstatus';
    } elseif ( ( $action == 'login' || $action == 'lostpw' ) && X_SCRIPT == 'misc.php' ) {
        // Allow login
    } elseif ( X_SCRIPT == 'css.php' || X_SCRIPT == 'lost.php' ) {
        // Allow stylesheets and password resets
    } elseif ( $SETTINGS['bbstatus'] == 'off' ) {
        // Block all non-admins
        $serror = 'bstatus';
    } elseif ( $SETTINGS['regstatus'] == 'on' && ( $action == 'reg' || $action == 'captchaimage' ) && ( X_SCRIPT == 'misc.php' || X_SCRIPT == 'member.php' ) ) {
        // Allow registration
    } elseif ( $SETTINGS['regviewonly'] == 'on' ) {
        // Block all guests
        $serror = 'guest';
    } else {
        // Allow everything else
    }
}

// Authenticate session or login credentials.
$force_inv = false;
if ( (int) $SETTINGS['schema_version'] < 5 ) {
    $mode = 'disabled';
} else if ( X_SCRIPT == 'upgrade.php' && isset( $_POST['xmbpw'] ) ) {
    $mode = 'login';
} else if ( $action == 'login' && onSubmit('loginsubmit') && X_SCRIPT == 'misc.php' ) {
    $mode = 'login';
    $force_inv = (formInt('hide') == 1);
} else if ( $action == 'logout' && X_SCRIPT == 'misc.php' ) {
    $mode = 'logout';
} else {
    $mode = 'resume';
}

$session = new \XMB\Session\Manager( $mode );

elevateUser( $force_inv, $serror );

if ( X_SCRIPT == 'upgrade.php' ) return;


/* Set Up HTML Templates and Themes */

// Specify all charset variables as early as possible.
if ($action != 'attachment' && !($action == 'templates' && isset($download)) && !($action == 'themes' && isset($download))) {
    header("Content-type: text/html;charset={$lang['charset']}");
}
if ( function_exists( 'mb_list_encodings' ) ) {
    // The list of charsets common to mb_string and htmlspecialchars is extremely restrictive.
    switch ( strtoupper( $lang['charset'] ) ) {
    case 'UTF-8':
        $newcharset = 'UTF-8';
        break;
    case 'WINDOWS-1251':
        $newcharset = 'Windows-1251';
        break;
    default:
        $newcharset = 'ISO-8859-1';
        break;
    }
    if ( ! in_array( $newcharset, mb_list_encodings() ) ) {
        $newcharset = 'ISO-8859-1';
    }
} else {
    $newcharset = 'ISO-8859-1';
}
ini_set( 'default_charset', $newcharset );

// Create a base element so that links aren't broken if scripts are accessed using unexpected paths.
// XMB expects all links to be relative to $full_url + script name + query string.
$querystring = strstr($url, '?');
if ($querystring === FALSE) {
    $querystring = '';
}
$querystring = preg_replace('/[^\x20-\x7e]/', '', $querystring);
if ($url == $cookiepath) {
    $baseelement = '<base href="'.$full_url.'" />';
} else {
    $baseelement = '<base href="'.$full_url.X_SCRIPT.attrOut($querystring).'" />';
}

// Read last visit cookies
$xmblva = getInt('xmblva', 'c'); // Previous request timestamp.
$xmblvb = getInt('xmblvb', 'c'); // Ending timestamp of previous session.

if ($xmblvb > 0) {
    $thetime = $xmblvb;     // lvb will expire in 600 seconds, so if it's there, we're still in a session and persisting the value from the last visit.
} else if ($xmblva > 0) {
    $thetime = $xmblva;     // Not currently logged in, so let's get the time from the last visit and save it
} else {
    $thetime = $onlinetime; // no cookie at all, so this is your first visit
}

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
    if ( (int) $self['lastvisit'] < $thetime || ( (int) $self['lastvisit'] > $thetime + X_ONLINE_TIMER && (int) $self['lastvisit'] < $onlinetime - X_ONLINE_TIMER)) {
        $thetime = $self['lastvisit'];
    }
    $lastlocal = $thetime + ($self['timeoffset'] * 3600) + ($SETTINGS['addtime'] * 3600);
    $lastdate = gmdate($dateformat, $lastlocal);
    $lasttime = gmdate($timecode, $lastlocal);
    $lastvisittext = $lang['lastactive'].' '.$lastdate.' '.$lang['textat'].' '.$lasttime;
} else {
    // Checks for the possibility to register
    if ($SETTINGS['regstatus'] == 'on') {
        $reglink = '- <a href="member.php?action=reg">'.$lang['textregister'].'</a>';
    } else {
        $reglink = '';
    }
    $loginout = '<a href="misc.php?action=login">'.$lang['textlogin'].'</a>';
    $notify = $lang['notloggedin'].' ['.$loginout.' '.$reglink.']';
    $lastvisittext = '';
}

// Update last visit cookies
put_cookie('xmblva', $onlinetime, ($onlinetime + (86400*365))); // lva == now
put_cookie('xmblvb', $thetime, ($onlinetime + X_ONLINE_TIMER)); // lvb == last visit
$lastvisit = $thetime; // Used by forumdisplay

// Get themes, [fid, [tid]]
$forumtheme = '0';
$fid = getInt('fid', 'r');
$tid = getInt('tid', 'r');
if ($tid > 0 && $action != 'templates') {
    $query = $db->query("SELECT f.fid, f.theme FROM ".X_PREFIX."forums f RIGHT JOIN ".X_PREFIX."threads t USING (fid) WHERE t.tid=$tid");
    if ($db->num_rows($query) == 0) {
        $tid = 0;
        $fid = 0;
    } else {
        $locate = $db->fetch_array($query);
        $fid = $locate['fid'];
        $forumtheme = $locate['theme'];
    }
    $db->free_result($query);
} else if ($fid > 0) {
    $forum = getForum($fid);
    if ( false === $forum || ( $forum['type'] != 'forum' && $forum['type'] != 'sub' ) || $forum['status'] != 'on' ) {
        $forumtheme = '0';
    } else {
        $forumtheme = $forum['theme'];
    }
}

// Check which theme to use
$validtheme = FALSE;
if (!$validtheme && (int) $themeuser > 0) {
    $theme = (int) $themeuser;
    $row = \XMB\SQL\getThemeByID( $theme );
    if ( ! $validtheme = ( ! empty( $row ) ) ) {
        $themeuser = '0';
        $db->query("UPDATE ".X_PREFIX."members SET theme=0 WHERE uid={$self['uid']}");
    }
}
if (!$validtheme && (int) $forumtheme > 0) {
    $theme = (int) $forumtheme;
    $row = \XMB\SQL\getThemeByID( $theme );
    if ( ! $validtheme = ( ! empty( $row ) ) ) {
        $themeuser = '0';
        $db->query("UPDATE ".X_PREFIX."forums SET theme=0 WHERE fid=$fid");
    }
}
if (!$validtheme) {
    $theme = (int) $SETTINGS['theme'];
    $row = \XMB\SQL\getThemeByID( $theme );
    $validtheme = ( ! empty( $row ) );
}
if (!$validtheme) {
    $query = $db->query("SELECT * FROM ".X_PREFIX."themes LIMIT 1");
    if ($validtheme = ($db->num_rows($query) > 0)) {
        $row = $db->fetch_array($query);
        $SETTINGS['theme'] = $row['themeid'];
        \XMB\SQL\updateSetting( 'theme', $SETTINGS['theme'] );
    }
    $db->free_result($query);
}
if (!$validtheme) {
    header('HTTP/1.0 500 Internal Server Error');
    exit('Fatal Error: The XMB themes table is empty.');
}

// Make theme-vars semi-global
$THEME = &$row;
unset( $row );
more_theme_vars();
extract( $THEME );

$css = '';
if ( (int) $SETTINGS['schema_version'] >= 6 ) {
    $css = "<link rel='stylesheet' type='text/css' href='{$full_url}css.php?id={$THEME['themeid']}&amp;v={$THEME['version']}' />";
}

// additional CSS to load?
if (file_exists(ROOT.$THEME['imgdir'].'/theme.css')) {
    $css .= "\n<link rel='stylesheet' type='text/css' href='{$full_url}{$THEME['imgdir']}/theme.css' />";
}


/* Theme Ready.  Make pretty errors. */

switch ($serror) {
case 'ip':
    if ( ! X_ADMIN ) {
        header('HTTP/1.0 403 Forbidden');
        error($lang['bannedmessage']);
    }
    break;
case 'bstatus':
    if ( ! X_ADMIN ) {
        header('HTTP/1.0 503 Service Unavailable');
        header('Retry-After: 3600');
        if ($bboffreason != '') {
            message(nl2br($bboffreason));
        } else {
            message($lang['textbstatusdefault']);
        }
    }
    break;
case 'guest':
    if ( X_GUEST ) {
        if ($SETTINGS['regstatus'] == 'on') {
            $message = $lang['reggedonly'].' '.$reglink.' '.$lang['textor'].' <a href="misc.php?action=login">'.$lang['textlogin'].'</a>';
        } else {
            $message = $lang['reggedonly'].' <a href="misc.php?action=login">'.$lang['textlogin'].'</a>';
        }
        message($message);
    }
    break;
}


/* Finish HTML Templates */

if ((X_ADMIN || $SETTINGS['bbstatus'] == 'on') && (X_MEMBER || $SETTINGS['regviewonly'] == 'off')) {

    $links = array();

    // Search-link
    $searchlink = makeSearchLink();

    // Faq-link
    if ($SETTINGS['faqstatus'] == 'on') {
        $links[] = '<img src="'.$THEME['imgdir'].'/top_faq.gif" alt="'.$lang['altfaq'].'" border="0" /> <a href="faq.php"><font class="navtd">'.$lang['textfaq'].'</font></a>';
    }

    // Memberlist-link
    if ($SETTINGS['memliststatus'] == 'on') {
        $links[] = '<img src="'.$THEME['imgdir'].'/top_memberslist.gif" alt="'.$lang['altmemberlist'].'" border="0" /> <a href="misc.php?action=list"><font class="navtd">'.$lang['textmemberlist'].'</font></a>';
    }

    // Today's posts-link
    if ($SETTINGS['todaysposts'] == 'on') {
        $links[] = '<img src="'.$THEME['imgdir'].'/top_todaysposts.gif" alt="'.$lang['alttodayposts'].'" border="0" /> <a href="today.php"><font class="navtd">'.$lang['navtodaysposts'].'</font></a>';
    }

    // Stats-link
    if ($SETTINGS['stats'] == 'on') {
        $links[] = '<img src="'.$THEME['imgdir'].'/top_stats.gif" alt="'.$lang['altstats'].'" border="0" /> <a href="stats.php"><font class="navtd">'.$lang['navstats'].'</font></a>';
    }

    // 'Forum Rules'-link
    if ($SETTINGS['bbrules'] == 'on') {
        $links[] = '<img src="'.$THEME['imgdir'].'/top_bbrules.gif" alt="'.$lang['altrules'].'" border="0" /> <a href="faq.php?page=forumrules"><font class="navtd">'.$lang['textbbrules'].'</font></a>';
    }

    $links = implode(' &nbsp; ', $links);

    // Show all plugins
    $pluglinks = array();
    foreach($plugname as $plugnum => $item) {
        if (!empty($plugurl[$plugnum]) && !empty($plugname[$plugnum])) {
            if (trim($plugimg[$plugnum]) != '') {
                $img = '&nbsp;<img src="'.$plugimg[$plugnum].'" border="0" alt="'.$plugname[$plugnum].'" />&nbsp;';
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

    // create forum jump
    if ($SETTINGS['quickjump_status'] == 'on') {
        $quickjump = forumJump();
    }

    // check for new u2u's
    if (X_MEMBER) {
        $query = $db->query("SELECT COUNT(*) FROM ".X_PREFIX."u2u WHERE owner='$xmbuser' AND folder='Inbox' AND readstatus='no'");
        $newu2unum = (int) $db->result($query, 0);
        $db->free_result($query);
        if ($newu2unum > 0) {
            $newu2umsg = "<a href=\"u2u.php\" onclick=\"Popup(this.href, 'Window', 700, 450); return false;\">{$lang['newu2u1']} $newu2unum {$lang['newu2u2']}</a>";
            // Popup Alert
            if ( '2' === $self['u2ualert'] || ( '1' === $self['u2ualert'] && X_SCRIPT == 'index.php' ) ) {
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
}


/* Perform HTTP Connection Maintenance */

assertEmptyOutputStream('header.php');

// Gzip-compression
if ($SETTINGS['gzipcompress'] == 'on'
 && $action != 'captchaimage'
 && X_SCRIPT != 'files.php'
 && !DEBUG) {
    if (($res = @ini_get('zlib.output_compression')) > 0) {
        // leave it
    } else if ($res === false) {
        // ini_get not supported. So let's just leave it
    } else {
        if (function_exists('gzopen')) {
            $r = @ini_set('zlib.output_compression', 4096);
            $r2 = @ini_set('zlib.output_compression_level', '3');
            if (FALSE === $r || FALSE === $r2) {
                ob_start('ob_gzhandler');
            }
        } else {
            ob_start('ob_gzhandler');
        }
    }
}

assertEmptyOutputStream('header.php');

return;