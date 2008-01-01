<?php
/* $Id: header.php,v 1.3.2.55 2007/03/20 02:18:07 ajv Exp $ */
/*
    © 2001 - 2007 Aventure Media & The XMB Development Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

    error_reporting(0);                     // Production under normal circumstances
    ini_set('log_errors', true);

    define('IN_CODE', true);                // used to prevent inappropriate file inclusion.

    define('X_CACHE_GET',       1);
    define('X_CACHE_PUT',       2);

    define('X_SET_HEADER',      1);
    define('X_SET_JS',          2);

    define('X_SHORTEN_SOFT',    1);
    define('X_SHORTEN_HARD',    2);

    define('X_LOG_ADMIN',       1);
    define('X_LOG_MOD',         2);
    define('X_LOG_USER',        3);

    define('X_PERMS_POLL',      0);
    define('X_PERMS_THREAD',    1);
    define('X_PERMS_REPLY',     2);
    define('X_PERMS_VIEW',      3);
    define('X_PERMS_USERLIST',  4);
    define('X_PERMS_PASSWORD',  5);

    // Initialise pre-set Variables
    // These strings can be pulled for use on any page as header is required by all XMB pages

    $versioncompany     = 'Aventure Media & The XMB Group';
    $versionshort       = "XMB 1.9.7";
    $versiongeneral     = 'XMB 1.9.7 Nexus';
    $copyright          = '2002-2007';
    $versionbuild       = 200703192123;

    if (!defined('ROOT')) {
        define('ROOT', './');
    }

// Resolve Server specific issues
    $server = substr($_SERVER['SERVER_SOFTWARE'], 0, 3);
    switch ( $server )
    {
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
    $footerads      = '';
    $threadSubject  = '';
    $self           = array();
    $user           = (isset($user)) ? $user : '';
    $SETTINGS       = array();
    $THEME          = array();
    $links          = array();
    $lang           = array();
    $plugname       = array();
    $mailer         = array();

    define('COMMENTOUTPUT', false);
    define('MAXATTACHSIZE', 256000);
    define('IPREG', 'on');
    define('IPCHECK', 'off');
    define('SPECQ', false);
    define('SHOWFULLINFO', false);

    require ROOT.'config.php';

    if (defined('DEBUG') && DEBUG==true) {
        error_reporting(E_ALL | E_STRICT);      // Development or DEBUG
    }

// discover the most likely browser
//  so we can use bbcode specifically made for it
//  this allow the use of various nice new features in eg mozilla
//  while others are available via IE and/or opera
    $browser = 'opera'; // default to opera for now
    if(false !== strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') && false === strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) {
        define('IS_MOZILLA',true);
        $browser = 'mozilla';
    }
    if(false !== strpos($_SERVER['HTTP_USER_AGENT'], 'Opera')) {
        define('IS_OPERA',  true);
        $browser = 'opera';
    }
    if(false !== strpos($_SERVER['HTTP_USER_AGENT'], '.NET CLR')) {
        define('IS_IE',     true);
        $browser = 'ie';
    }
    if(!defined('IS_MOZILLA')) {
        define('IS_MOZILLA',false);
    }
    if(!defined('IS_OPERA')) {
        define('IS_OPERA',  false);
    }
    if(!defined('IS_IE')) {
        define('IS_IE',     false);
    }

    if (!file_exists(ROOT.'db/'.$database.'.php')) {
        die('Error: XMB is not installed, or is configured incorrectly. <a href="install/index.php">Click Here to install XMB</a>');
    }
    require ROOT.'db/'.$database.'.php';
    require ROOT.'functions.php';

// initialize navigation
    $navigation = '';
    nav();

// Cache-control
    header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

// Fix annoying bug in windows... *sigh*

    $action = isset($action) ? $action : '';

    if ( $action != "attachment" && !($action == "templates" && isset($download)) && !($action == "themes" && isset($download)) && !($action == 'dbdump') && !($action == 'dump_attachments')) {
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
    if ( isset($_SERVER['REMOTE_ADDR']) ) {
        $onlineip = $_SERVER['REMOTE_ADDR'];
    }

    //Checks the IP-format, if it's not a IPv4, nor a IPv6 type, it will be blocked, safe to remove....
    if ( $ipcheck == 'on') {
        if (!eregi("^([0-9]{1,3}\.){3}[0-9]{1,3}$", $onlineip) && !eregi("^([a-z,0-9]{0,4}:){5}[a-z,0-9]{0,4}$", $onlineip)&& !stristr($onlineip, ':::::')) {
            exit("Access to this website is currently not possible as your hostname/IP appears suspicous.");
        }
    }

    // Checks for various variables in the URL, if any of them is found, script is halted
    $url_check = Array('status=', 'xmbuser=', 'xmbpw=', '<script');
    $url = urldecode($url);
    foreach ($url_check as $name) {
        if ( strpos(strtolower($url), $name) ) {
            exit();
        }
    }

    // Load Objects, and such
    $tables = array('attachments','banned','buddys','favorites','forums','logs','members','posts','ranks','restricted','settings','smilies','templates','themes','threads','u2u','whosonline','words');
    foreach ($tables as $name) {
        ${'table_'.$name} = $tablepre.$name;
    }

    $db = new dbstuff;
    $db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

    // Load a few constants
    define('XMB_VERSION', $versiongeneral);
    define('XMB_BUILD', $versionbuild);

    define('X_REDIRECT_HEADER', 1);
    define('X_REDIRECT_JS', 2);

    // Create cookie-settings
    if ( !isset($full_url) || empty($full_url) || $full_url == 'FULLURL' ) {
        exit('<b>ERROR: </b><i>Please fill the $full_url variable in your config.php!</i>');
    } else {
        $array = parse_url($full_url);
        if (substr($array['host'], 0, 9) == 'localhost' || preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/i", $array['host'])) {
            $cookiedomain  = '';
        }else{
            $cookiedomain = str_replace('www', '', $array['host']);
        }
        if(!isset($array['path'])) {
            $array['path'] = '/';
        }
        //$cookiepath = ($array['path'] == '/') ? ('') : ($array['path']);
        $cookiepath = $array['path'];
    }

// Set cookies

    put_cookie('xmblva', $onlinetime, ($onlinetime + (86400*365)), $cookiepath, $cookiedomain);

    if (isset($xmblvb)) {
        $thetime = $xmblvb;
    }elseif (isset($xmblva)) {
        $thetime = $xmblva;
    }else{
        $thetime = $onlinetime;
    }

    put_cookie('xmblvb', $thetime, ($onlinetime + 600), $cookiepath, $cookiedomain);

    $lastvisit = $thetime;
    $lastvisit2 = $lastvisit - 540;

    if(isset($oldtopics)) {
        put_cookie('oldtopics', $oldtopics, (time()+600), $cookiepath, $cookiedomain);
    }

// Make all settings global, and put them in the $SETTINGS[] array
    $settingsquery = $db->query("SELECT * FROM $table_settings");
    foreach ($db->fetch_array($settingsquery) as $key => $val) {
        $$key = $val;
        $SETTINGS[$key] = $val;
    }

    if ( $SETTINGS['postperpage'] < 5 ) {
        $SETTINGS['postperpage'] = 30;
    }

    if ( $SETTINGS['topicperpage'] < 5 ) {
        $SETTINGS['topicperpage'] = 30;
    }

// Get the user-vars, and make them semi-global
    if (!isset($xmbuser)) {
        $xmbuser    = '';
        $xmbpw      = '';
        $self['status'] = '';
    }

    $q = false;
    if ( $xmbuser != '') {
        $query = $db->query("SELECT * FROM $table_members WHERE username='$xmbuser'");
        $userrec = $db->fetch_array($query);
        if ( $db->num_rows($query) == 1 && $userrec['password'] == $xmbpw ) {
            $q = true;
        }
        $db->free_result($query);
    }

    if ( $q ) {
        foreach ($userrec as $key => $val) {
            $self[$key] = $val;
        }

        define('X_MEMBER', true);
        define('X_GUEST', false);

        if ( empty($self['langfile']) || !file_exists("lang/$self[langfile].lang.php")) {
            $self['langfile'] = $SETTINGS['langfile'];
        }

        $db->query("UPDATE $table_members SET lastvisit=".$db->time($onlinetime)." WHERE username='$xmbuser'");
    }else{
        define('X_MEMBER', false);
        define('X_GUEST', true);
        $self = array('uid' => 0,
            'username'      => 'Anonymous',
            'password'      => '',
            'regdate'       => 0,
            'postnum'       => 0,
            'email'         => '',
            'site'          => '',
            'aim'           => '',
            'status'        => 'guest',
            'location'      => '',
            'bio'           => '',
            'sig'           => '',
            'showemail'     => 'no',
            'timeoffset'    => $SETTINGS['def_tz'],
            'icq'           => 0,
            'avatar'        => '',
            'yahoo'         => '',
            'customstatus'  => '',
            'theme'         => 0,
            'bday'          => '0000-00-00',
            'langfile'      => $SETTINGS['langfile'],
            'tpp'           => $SETTINGS['topicperpage'],
            'ppp'           => $SETTINGS['postperpage'],
            'newsletter'    => 'no',
            'regip'         => '000.000.000.000',
            'timeformat'    => $SETTINGS['timeformat'],
            'msn'           => '',
            'ban'           => '',
            'dateformat'    => $SETTINGS['dateformat'],
            'ignoreu2u'     => '',
            'lastvisit'     => 0,
            'mood'          => '',
            'pwdate'        => 0,
            'invisible'     => 0,
            'u2ufolders'    => '',
            'saveogu2u'     => 'no',
            'emailonu2u'    => 'no',
            'useoldu2u'     => 'no',
            'webcam'        => ''
        );
    }

    if($self['timeformat'] == 24) {
        $self['timecode'] = "H:i";
    } else {
        $self['timecode'] = "h:i A";
    }

    // Fix by John Briggs Begin
    $role_admin = false;
    $role_staff = false;
    $role_sadmin = false;
    $role_smod = false;

    switch($self['status']) {
        case 'Super Administrator':
            $role_admin = true;
            $role_staff = true;
            $role_sadmin = true;
            break;

        case 'Administrator':
            $role_admin = true;
            $role_staff = true;
            break;

        case 'Super Moderator':
            $role_staff = true;
            $role_smod = true;
            break;

        case 'Moderator':
            $role_staff = true;
            break;

        default:
            break;
    }

    define('X_STAFF', $role_staff);
    define('X_ADMIN', $role_admin);
    define('X_SADMIN',$role_sadmin);
    define('X_SMOD',  $role_smod);

    unset($role_staff, $role_admin, $role_sadmin, $role_smod);
    // Fix by John Briggs End

    if ( $show_full_info || X_SADMIN ) {
        $alpha              = '';
        $beta               = '';
        $gamma              = 'RC3';
        $service_pack       = '';
        $versionlong        = 'Powered by '.$versiongeneral.' ('.$alpha.$beta.$gamma.$service_pack.')'.(DEBUG === true ? ' (Debug)' : '');
    } else {
        $alpha              = '';
        $beta               = '';
        $gamma              = '';
        $service_pack       = '';
        $versionbuild       = '[HIDDEN]';
        $versionlong        = "Powered by XMB".(DEBUG === true ? ' (Debug)' : '');
    }

// load base language file (english), then overload with locale-specific
    require ROOT.'lang/Base.lang.php';
    require ROOT.'lang/'.$self['langfile'].'.lang.php';

// Checks for the possibility to register
    if($SETTINGS['regstatus'] == "on" && X_GUEST) {
        $reglink = "- <a href=\"member.php?action=coppa\">$lang[textregister]</a>";
    } else {
        $reglink = '';
    }

// Creates login/logout links

    if (X_MEMBER) {
        $loginout = "<a href=\"misc.php?action=logout\">$lang[textlogout]</a>";
        $memcp = "<a href=\"memcp.php\">$lang[textusercp]</a>";
        $cplink = '';
        $u2ulink = "<a href=\"u2u.php\" target=\"u2uWindow\" onclick=\"Popup(this.href, 'u2uWindow', 750, 450); return false;\">$lang[banu2u]</a> - ";

        if (X_ADMIN) {
            $cplink = " - <a href=\"cp.php\">$lang[textcp]</a>";
        }
        $notify = "$lang[loggedin] <a href=\"member.php?action=viewpro&amp;member=".rawurlencode($self['username'])."\">$self[username]</a><br />[$loginout - $u2ulink$memcp$cplink]";
    } else {
        $loginout = "<a href=\"misc.php?action=login\">$lang[textlogin]</a>";
        $notify = "$lang[notloggedin] [$loginout $reglink]";
    }

// Checks if the timeformat has been set, if not, use default
    $self['dateformat_orig'] = $self['dateformat'];
    $self['dateformat'] = str_replace(($date_from = array('mm', 'MM', 'dd', 'DD', 'yyyy', 'YYYY', 'yy', 'YY')), ($date_to = array('n', 'n', 'j', 'j', 'Y', 'Y', 'y', 'y')), $self['dateformat']);

// Get themes, [fid, [tid]]
    if (isset($tid) && $action != 'templates') {
        $query = $db->query("SELECT f.fid, f.theme, t.subject FROM $table_forums f, $table_threads t WHERE f.fid=t.fid AND t.tid='$tid'");
        $locate = $db->fetch_array($query);
        $fid = $locate['fid'];
        $forumtheme = $locate['theme'];
        if($SETTINGS['subject_in_title'] == 'on') {
            $threadSubject = '- '.stripslashes($locate['subject']);
        } else {
            $threadSubject = '';
        }
    }elseif (isset($fid)) {
        $q = $db->query("SELECT theme FROM $table_forums WHERE fid='$fid'");
        if($db->num_rows($q) === 1) {
            $forumtheme = $db->result($q, 0);
        } else {
            $forumtheme = 0;
        }
    }

    $wollocation = addslashes($url);
    $newtime = $onlinetime - 600;

    // clear out old entries and guests
    if(X_GUEST) {
        // don't remove all username=$self['username'] otherwise we'll auto-remove all guests (with username 'Anonymous')
        $db->query("DELETE FROM $table_whosonline WHERE (ip = '$onlineip' && username = 'Anonymous') OR (time < '$newtime' )");
    } else {
        $db->query("DELETE FROM $table_whosonline WHERE ((ip = '$onlineip' && username = 'Anonymous') OR (username = '$self[username]') OR (time < '$newtime') )");

        // Find duplicate entries for users only
        $result = $db->query("SELECT count(username) FROM $table_whosonline WHERE ( username = '$self[username]' )");
        $usercount = $db->result($result, 0);
        if ( $usercount > 1 ) {
            $db->query("DELETE FROM $table_whosonline WHERE (username = '$self[username]')");
            $db->query("INSERT INTO $table_whosonline (username, ip, time, location, invisible) VALUES ('$self[username]', '$onlineip', ".$db->time($onlinetime).", '$wollocation', '$self[invisible]')");
        }
    }
    $db->query("INSERT INTO $table_whosonline (username, ip, time, location, invisible) VALUES ('$self[username]', '$onlineip', ".$db->time($onlinetime).", '$wollocation', '$self[invisible]')");

// Check what theme to use
    if ((int) $self['theme'] == 0) {
        if(!empty($forumtheme) && (int) $forumtheme > 0) {
            $theme = $forumtheme;
        } else {
            $theme = $SETTINGS['theme'];
        }
    } else {
        $theme = $self['theme'];
    }

// Make theme-vars semi-global
    $query = $db->query("SELECT * FROM $table_themes WHERE themeid=$theme");
    foreach ($db->fetch_array($query) as $key => $val) {
        /*
        if ( $key != "name") {
            $$key = $val;
        } else {
            // make themes with apostrophes safe to display
            $val = stripslashes($val);
        }
        */
        if($key == 'name') {
            $val = stripslashes($val);
        }
        $THEME[$key] = $val;
    }
    $THEME['imgdir'] = ROOT.$THEME['imgdir'];

    // additional CSS to load?
    if(file_exists($THEME['imgdir'].'/theme.css')) {
        $cssInclude = '<style type="text/css">'."\n"."@import url('".$THEME['imgdir']."/theme.css');"."\n".'</style>';
    } else {
        $cssInclude = '';
    }

    // Alters certain visibility-variables
    if (false === strpos($THEME['bgcolor'], ".")) {
        $bgcode = 'background-color: '.$THEME['bgcolor'].';';
    } else {
        $bgcode = 'background-image: url(\''.$THEME['imgdir'].'/'.$THEME['bgcolor'].'\');';
    }

    if (false === strpos($THEME['catcolor'], ".")) {
        //$catbgcode  = "bgcolor=\"$catcolor\"";
        $catcss     = 'background-color: '.$THEME['catcolor'].';';
    } else {
        //$catbgcode  = "style=\"background-image: url($imgdir/$catcolor)\"";
        $catcss     = 'background-image: url('.$THEME['imgdir'].'/'.$THEME['catcolor'].');';
    }

    if (false === strpos($THEME['top'], ".")) {
        $topbgcode = 'style="background-color: '.$THEME['top'].'"';
    } else {
        $topbgcode = 'style="background-image: url('.$THEME['imgdir'].'/'.$THEME['top'].')"';
    }

    if (false !== strpos($THEME['boardimg'], ",")) {
        $flashlogo = explode(",",$THEME['boardimg']);

        //check if it's an URL or just a filename
        $l = array();
        $l = parse_url($flashlogo[0]);
        if (!isset($l['scheme']) || !isset($l['host'])) {
            $flashlogo[0] = $THEME['imgdir'].'/'.$flashlogo[0];
        }
        $logo = '<object type="application/x-shockwave-flash" data="'.$flashlogo[0].'" width="'.$flashlogo[1].'" height="'.$flashlogo[2].'"><param name="movie" value="'.$flashlogo[0].'" /><param name="AllowScriptAccess" value="never" /></object>';
    } else {
        $l = array();
        $l = parse_url($THEME['boardimg']);
        if (!isset($l['scheme']) || !isset($l['host'])) {
            $THEME['boardimg'] = $THEME['imgdir'].'/'.$THEME['boardimg'];
        }
        $logo = '<a href="index.php"><img src="'.$THEME['boardimg'].'" alt="'.$SETTINGS['bbname'].'" border="0" /></a>';
    }

    // Font stuff...
    $fontedit = preg_replace('#(\D)#', '', $THEME['fontsize']);
    $fontsuf  = preg_replace('#(\d)#', '', $THEME['fontsize']);

    $THEME['font1'] = $fontedit-1 . $fontsuf;
    $THEME['font3'] = $fontedit+2 . $fontsuf;

    // Update lastvisit in the header shown
    if (isset($lastvisit) && X_MEMBER) {
        $lastdate = printGmDate($xmblva);
        $lasttime = printGmTime($xmblva);

        $lastvisittext = $lang['lastactive'].' '.$lastdate.' '.$lang['textat'].' '.$lasttime;
    } else {
        $lastvisittext = $lang['lastactive'].' '.$lang['textnever'];
    }

    // Checks for various settings

    if (empty($action)) {
        $action = NULL;
    }

    // Gzip-compression
    if ( $SETTINGS['gzipcompress'] == "on" && $action != "attachment") {
        if (($res = @ini_get('zlib.output_compression')) === 1) {
            // leave it
        } elseif ( $res === false) {
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
    if($SETTINGS['searchstatus'] == "on") {
        $links[] = '<img src="'.$THEME['imgdir'].'/search.gif" alt="'.$lang['altsearch'].'" border="0" /> <a href="misc.php?action=search"><font class="navtd">'.$lang['textsearch'].'</font></a>';
    }

    // Faq-link
    if($SETTINGS['faqstatus'] == "on") {
        $links[] = '<img src="'.$THEME['imgdir'].'/faq.gif" alt="'.$lang['altfaq'].'" border="0" /> <a href="faq.php"><font class="navtd">'.$lang['textfaq'].'</font></a>';
    }

    // Memberlist-link
    if($SETTINGS['memliststatus'] == "on") {
        $links[] = '<img src="'.$THEME['imgdir'].'/members_list.gif" alt="'.$lang['altmemberlist'].'" border="0" /> <a href="misc.php?action=list"><font class="navtd">'.$lang['textmemberlist'].'</font></a>';
    }

    // Today's posts-link
    if($SETTINGS['todaysposts'] == "on") {
        $links[] = '<img src="'.$THEME['imgdir'].'/todays_posts.gif" alt="'.$lang['alttodayposts'].'" border="0" /> <a href="today.php"><font class="navtd">'.$lang['navtodaysposts'].'</font></a>';
    }

    // Stats-link
    if($SETTINGS['stats'] == "on") {
        $links[] = '<img src="'.$THEME['imgdir'].'/stats.gif" alt="'.$lang['altstats'].'" border="0" /> <a href="stats.php"><font class="navtd">'.$lang['navstats'].'</font></a>';
    }

    // 'Forum Rules'-link
    if($SETTINGS['bbrules'] == "on") {
        $links[] = '<img src="'.$THEME['imgdir'].'/bbrules.gif" alt="'.$lang['altrules'].'" border="0" /> <a href="faq.php?page=forumrules"><font class="navtd">'.$lang['textbbrules'].'</font></a>';
    }

    $links = implode(' &nbsp; ', $links);

//Show all plugins
    $pluglinks = array();
    foreach ($plugname as $plugnum => $item) {
        if ( !empty($plugurl[$plugnum]) && !empty($plugname[$plugnum]) ) {
            if ( trim($plugimg[$plugnum]) != '' ) {
                $img = '<img src="'.$plugimg[$plugnum].'" border="0" /> ';
            } else {
                $img = '';
            }
            if ( $plugadmin[$plugnum] != true || X_ADMIN ) {
                $pluglinks[] = " &nbsp; $img<a href=\"$plugurl[$plugnum]\"><font class=\"navtd\">$plugname[$plugnum]</font></a>";
            }
        }
    }
    if ( count($pluglinks) == 0 ) {
        $pluglink = '';
    } else {
        $pluglink = implode(' &nbsp; ', $pluglinks);
    }

// If the board is offline, display an appropriate message
    if ( $SETTINGS['bbstatus'] == "off" && !(X_ADMIN) && false === strpos($url, "misc.php") && false === strpos($url, "member.php")) {
        eval('$css = "'.template('css').'";');
        error(stripslashes($SETTINGS['bboffreason']));
    }

// If the board is set to 'reg-only' use, check if someone is logged in, and if not display a message
    if ( $SETTINGS['regviewonly'] == "on") {
        if (X_GUEST && $action != "reg" && $action != "login" && $action != "lostpw" && $action != "coppa") {
            if ( $SETTINGS['coppa'] == 'on') {
                $message = "$lang[reggedonly] <a href=\"member.php?action=coppa\">$lang[textregister]</a> $lang[textor] <a href=\"misc.php?action=login\">$lang[textlogin]</a>";
            }else{
                $message = "$lang[reggedonly] <a href=\"member.php?action=reg\">$lang[textregister]</a> $lang[textor] <a href=\"misc.php?action=login\">$lang[textlogin]</a>";
            }
            eval('$css = "'.template('css').'";');
            error($message);
        }
    }

    // Check if the user is ip-banned
    // also disable 'ban all'-possibility
    $ips = explode(".", $onlineip);
    $query = $db->query("SELECT id FROM $table_banned WHERE ((ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')) AND NOT (ip1='-1' AND ip2='-1' AND ip3='-1' AND ip4='-1')");
    $result = $db->fetch_array($query);

    // don't *ever* ban a (super-)admin!
    if (!X_ADMIN && ($self['status'] == "Banned" || $result )) {
        eval('$css = "'.template('css').'";');
        error($lang['bannedmessage']);
    }

// if the user is registered, check for new u2u's
    $newu2umsg = '';
    if (X_MEMBER) {
        $query = $db->query("SELECT count(readstatus) FROM $table_u2u WHERE owner='$self[username]' AND folder='Inbox' AND readstatus='no'");
        $newu2unum = $db->result($query, 0);
        if ( $newu2unum > 0) {
            $newu2umsg = "<a href=\"u2u.php\" target=\"u2uWindow\" onclick=\"Popup(this.href, 'u2uWindow', 750, 450);\">$lang[newu2u1] $newu2unum $lang[newu2u2]</a>";
        }
    }

