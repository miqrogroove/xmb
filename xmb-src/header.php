<?php
/* $Id: header.php,v 1.26 2006/02/25 18:19:13 Tularis Exp $ */
/*
    XMB 1.10
    © 2001 - 2006 Aventure Media & The XMB Development Team
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

    error_reporting(E_ALL&~E_NOTICE);

    define('IN_XMB', true);

    define('X_SET_HEADER', 1);
    define('X_SET_JS', 2);

	define('X_SHORTEN_SOFT', 1);
	define('X_SHORTEN_HARD', 2);

    if (!defined('ROOT')) {
        define('ROOT', './');
    }

    if (!defined('CLEAN_GLOBALS')) {
        define('CLEAN_GLOBALS', true);
    }

    // set up our prefered environment
    @ini_set('magic_quotes_runtime', '0');

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

    require ROOT.'include/sanitize.inc.php';

// Resolve Server specific issues

    $server = substr(safeString(fetchFromRequest('SERVER_SOFTWARE', X_SERVER), false, false), 0, 3);
    switch ( $server )
    {
        case 'Aby':     // Abyss web server
            $protocol = (getenv('HTTPS') == 'off') ? ('http://') : ('https://');
            $query = (getenv('QUERY_STRING')) ? ('?'.getenv('QUERY_STRING')) : ('');
            $url = $protocol.getenv('SERVER_NAME').getenv('SCRIPT_NAME').$query;
            break;

        default:        // includes Apache and IIS using module and CGI forms
            $url = safeString(fetchFromRequest('REQUEST_URI', X_SERVER), false, false);
    }

    // Initialising certain key variables. These are default values, please don't change them!

    $cookiepath     = '';
    $cookiedomain   = '';

    $mtime          = explode(" ", microtime());
    $starttime      = $mtime[1] + $mtime[0];

    $onlinetime     = time();

    $bbcodescript   = '';
    $footerads		= '';
    $threadSubject	= '';
    $self           = array();
    $SETTINGS       = array();
    $THEME          = array();
    $links          = array();
    $lang           = array();
    $plugname       = array();
	$mailer			= array();

    define('COMMENTOUTPUT', false);
    define('MAXATTACHSIZE', 1000000);
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

    $versioncompany     = 'Aventure Media & The XMB Group';
    $versionshort       = 'XMB 1.10.0';
    $versiongeneral     = 'XMB 1.10.0 Unnamed';
    $copyright			= '2002-2006';

    if ( $show_full_info) {
        $alpha              = 'pre-Alpha';
        $beta               = '';
        $gamma              = '';
        $service_pack       = '';
        $versionbuild       = 2006020116;
        $versionlong        = "Powered by XMB 1.10.0 Unnamed ($alpha$beta$gamma$service_pack)".(DEBUG === true ? ' (Debug)' : '');
    } else {
        $alpha              = '';
        $beta               = '';
        $gamma              = '';
        $service_pack       = '';
        $versionbuild       = '[HIDDEN]';
        $versionlong        = "Powered by XMB".(DEBUG === true ? ' (Debug)' : '');
    }

// discover the most likely browser
//	so we can use bbcode specifically made for it
//	this allow the use of various nice new features in eg mozilla
//	while others are available via IE and/or opera
	$browser = 'opera';	// default to opera for now
	if(false !== strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') && false === strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) {
		define('IS_MOZILLA',true);
		$browser = 'mozilla';
	}
	if(false !== strpos($_SERVER['HTTP_USER_AGENT'], 'Opera')) {
		define('IS_OPERA',	true);
		$browser = 'opera';
	}
	if(false !== strpos($_SERVER['HTTP_USER_AGENT'], '.NET CLR')) {
		define('IS_IE',		true);
		$browser = 'ie';
	}
	if(!defined('IS_MOZILLA')) {
		define('IS_MOZILLA',false);
	}
	if(!defined('IS_OPERA')) {
		define('IS_OPERA',	false);
	}
	if(!defined('IS_IE')) {
		define('IS_IE',		false);
	}

    // sanity check maximum registrations
    if ( !isset($max_reg_day) || $max_reg_day < 1 || $max_reg_day > 100 ) {
        $max_reg_day = 25;
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

    if (file_exists('./Upgrade/') && !@unlink('./Upgrade/')) {
        exit('<h1>Error:</h1><br />The upgrade tool ("./Upgrade/") has been found on the server, but could not be removed. Please remove it as soon as possible.');
    }

    // Checks the format of the URL, blocks if necessary....
    if (eregi("\?[0-9]+$", $url)) {
        exit("Invalid String Format, Please Check Your URL");
    }

    // Get visitors IP address (which is usually their transparent proxy)
    // DO NOT USE HTTP_CLIENT_IP or HTTP_X_FORWARDED_FOR as these can (and are) forged by attackers. ajv
    $onlineip = safeString(fetchFromRequest('REMOTE_ADDR', X_SERVER), false, false);

    //Checks the IP-format, if it's not a IPv4, nor a IPv6 type, it will be blocked, safe to remove....
    if ( $ipcheck == 'on') {
        if (!eregi("^([0-9]{1,3}\.){3}[0-9]{1,3}$", $onlineip) && !eregi("^([a-z,0-9]{0,4}:){5}[a-z,0-9]{0,4}$", $onlineip)&& !stristr($onlineip, ':::::')) {
            exit("Access to this website is currently not possible as your hostname/IP appears suspicous.");
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
        $cookiepath = ($array['path'] == '/') ? ('') : ($array['path']);
    }

// Set cookies

    put_cookie('xmblva', $onlinetime, ($onlinetime + (86400*365)), $cookiepath, $cookiedomain);

    if (null !== ($xmblvb = fetchFromRequest('xmblvb', X_COOKIE))) {
        $thetime = $xmblvb;
    }elseif (null !== ($xmblva = fetchFromRequest('xmblva', X_COOKIE))) {
        $thetime = $xmblva;
    }else{
        $thetime = $onlinetime;
    }

    put_cookie('xmblvb', $thetime, ($onlinetime + 600), $cookiepath, $cookiedomain);

    $lastvisit = $thetime;
    $lastvisit2 = $lastvisit - 540;

// put all settings in the $SETTINGS[] array (we don't make them global anymore; use the array instead)
    $SETTINGS = $db->fetch_array($db->query("SELECT * FROM $table_settings"));
	if(isset($oldtopics)) {
		put_cookie('oldtopics', $oldtopics, (time()+600), $cookiepath, $cookiedomain);
	}

    if ( $SETTINGS['postperpage'] < 5 ) {
        $postperpage = 30;
    }

    if ( $SETTINGS['topicperpage'] < 5 ) {
        $topicperpage = 30;
    }

// Get the user-vars, and make them semi-global
    $xmbuser = fetchFromRequest('xmbuser', X_COOKIE);
    if(null === $xmbuser) {
        $xmbuser = '';
        $xmbpw = '';
        $self['status'] = '';
    } else {
        $xmbuser	= safeString($xmbuser, false, false);
        $xmbpw		= safeString(fetchFromRequest('xmbpw', X_COOKIE), false, false);
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
		$self = $userrec;

        define('X_MEMBER', true);
        define('X_GUEST', false);

        $time       = time();

        $self['langfile'] = ($self['langfile'] == "" || !file_exists("lang/$self[langfile].lang.php")) ? $SETTINGS['langfile'] : $self['langfile'];

        if (!empty($self['theme'])) {
            $userTheme = $self['theme'];
        } else {
            $userTheme = 0;
        }
        $db->query("UPDATE $table_members SET lastvisit=".$db->time($onlinetime)." WHERE username='$xmbuser'");
    }else{
        define('X_MEMBER', false);
        define('X_GUEST', true);
        $self['timeoffset'] = $SETTINGS['def_tz'];
        $self['userTheme']  = 0;
        $self['status']     = 'member';
        $self['tpp']        = $SETTINGS['topicperpage'];
        $self['ppp']        = $SETTINGS['postperpage'];
        $self['memtime']    = '';
        $self['memdate']    = '';
        $self['sig']        = '';
        $self['invisible']  = 0;
        $self['time']       = time();
        $self['langfile']   = $SETTINGS['langfile'];
    }

    if ( $self['memtime'] == '') {
        if ( $self['timeformat'] == 24) {
            $timecode = "H:i";
        } else {
            $timecode = "h:i A";
        }
    } else {
        if ( $self['memtime'] == 24) {
            $timecode = "H:i";
        } else {
            $timecode = "h:i A";
        }
    }
    $self['timecode'] = $timecode;

    $role       = array();
    switch($self['status']) {
        case 'Super Administrator':
            $role['sadmin'] = true;
            $role['admin']  = true;
            $role['smod']   = true;
            $role['staff']  = true;
            break;

        case 'Administrator':
            $role['sadmin'] = false;
            $role['admin']  = true;
            $role['smod']   = true;
            $role['staff']  = true;
            break;

        case 'Super Moderator':
            $role['sadmin'] = false;
            $role['admin']  = false;
            $role['smod']   = true;
            $role['staff']  = true;
            break;

        case 'Moderator':
            $role['sadmin'] = false;
            $role['admin']  = false;
            $role['smod']   = false;
            $role['staff']  = true;
            break;

        default:
            $role['sadmin'] = false;
            $role['admin']  = false;
            $role['smod']   = false;
            $role['staff']  = false;
            break;
    }

    define('X_SADMIN',  $role['sadmin']);
    define('X_ADMIN',   $role['admin']);
    define('X_SMOD',    $role['smod']);
    define('X_STAFF',   $role['staff']);

// Get the required language file
    require ROOT.'lang/'.$self['langfile'].'.lang.php';

// Checks for the possibility to register
    if ( $SETTINGS['regstatus'] == "on" && X_GUEST ) {
        $reglink = "- <a href=\"member.php?action=coppa\">$lang[textregister]</a>";
    }else{
        $reglink = '';
    }

// Creates login/logout links

    if (X_MEMBER) {
        $loginout = '<a href="misc.php?action=logout">'.$lang['textlogout'].'</a>';
        $memcp = '<a href="memcp.php">'.$lang['textusercp'].'</a>';
        $onlineuser = $xmbuser;
        $cplink = '';
        $u2ulink = '<a href="#" onclick="Popup(\'u2u.php\', \'Window\', 700, 450);">'.$lang['banu2u'].'</a> - ';

        if (X_ADMIN) {
            $cplink = ' - <a href="cp.php">'.$lang['textcp'].'</a>';
        }
        $notify = $lang['loggedin'].' <a href="member.php?action=viewpro&amp;member='.$onlineuser.'">'.$xmbuser.'</a><br />['.$loginout.' - '.$u2ulink.$memcp.$cplink.']';
    } else {
        $loginout = '<a href="misc.php?action=login">'.$lang['textlogin'].'</a>';
        $onlineuser = 'xguest123';
        $self['status'] = '';
        $notify = $lang['notloggedin'].' ['.$loginout.' '.$reglink.']';
    }

// Checks if the timeformat has been set, if not, use default
    if ( $self['memdate'] != "") {
        $dateformat = $self['memdate'];
    } else {
        $dateformat = $SETTINGS['dateformat'];
	}

    $dateformat = str_replace("mm", "n", $dateformat);
    $dateformat = str_replace("dd", "j", $dateformat);
    $dateformat = str_replace("yyyy", "Y", $dateformat);
    $dateformat = str_replace("yy", "y", $dateformat);

	$self['dateformat'] = $dateformat;

// Get themes, [fid, [tid]]
    $forumTheme = 0;
    if (isset($tid) && $userTheme === 0) {
        $query = $db->query("SELECT f.fid, f.theme, t.subject FROM $table_forums f, $table_threads t WHERE f.fid=t.fid AND t.tid='$tid'");
        while($locate = $db->fetch_array($query)) {
            $fid = $locate['fid'];
            $forumtheme = $locate['theme'];

			if($SETTINGS['subject_in_title'] == 'on' && $action != 'templates') {
				$threadSubject = '- '.stripslashes($locate['subject']);
			}
        }
    }elseif (isset($fid) && $userTheme === 0) {
        $query = $db->query("SELECT theme, name FROM $table_forums WHERE fid='$fid'");
        while($locate = $db->fetch_array($query)) {
            $forumtheme = $locate['theme'];
        }
    }

    $wollocation = safeAddslashes($url);
    $newtime = $onlinetime - 600;

    // clear out old entries and guests
    $db->query("DELETE FROM $table_whosonline WHERE ((ip = '$onlineip' && username = 'xguest123') OR (username = '$xmbuser') OR (time < '$newtime') )");
    $db->query("INSERT INTO $table_whosonline (username, ip, time, location, invisible) VALUES ('$onlineuser', '$onlineip', ".$db->time($onlinetime).", '$wollocation', '".$self['invisible']."')");

    // Find duplicate entries for users only

    $username = isset($username) ? $username : '';

    if ( X_MEMBER ) {
        $result = $db->query("SELECT count(username) FROM $table_whosonline WHERE ( username = '$xmbuser' )");
        $usercount = $db->result($result, 0);
        if ( $usercount > 1 ) {
            $db->query("DELETE FROM $table_whosonline WHERE (username = '$xmbuser')");
            $db->query("INSERT INTO $table_whosonline (username, ip, time, location, invisible) VALUES ('$onlineuser', '$onlineip', ".$db->time($onlinetime).", '$wollocation', '$invisible')");
        }
    }

// Check what theme to use
    if ($userTheme > 0) {
        $theme = $userTheme;
    } elseif($forumTheme > 0) {
        $theme = $forumtheme;
    } else {
        $theme = $SETTINGS['theme'];
    }

// Make theme-vars semi-global in its own array
    $THEME = array_map('stripslashes', $db->fetch_array($db->query("SELECT * FROM $table_themes WHERE themeid='$theme'")));
    $THEME['imgdir'] = './'.$THEME['imgdir'];

    // additional CSS to load?
    if(file_exists($THEME['imgdir'].'/theme.css')) {
    	$cssInclude = '<style type="text/css">'."\n"."@import url('".$THEME['imgdir']."/theme.css');"."\n".'</style>';
    } else {
    	$cssInclude = '';
    }

    // additional CSS to load?
    if(file_exists($imgdir.'/theme.css')) {
    	$cssInclude = '<style type="text/css">'."\n"."@import url('".$imgdir."/theme.css');"."\n".'</style>';
    } else {
    	$cssInclude = '';
    }

    // Alters certain visibility-variables
    if (false === strpos($THEME['bgcolor'], ".")) {
        $THEME['bgcode'] = 'background-color: '.$THEME['bgcolor'].';';
    } else {
        $THEME['bgcode'] = 'background-image: url(\''.$THEME['imgdir'].'/'.$THEME['bgcolor'].'\');';
    }

    if (false === strpos($THEME['catcolor'], '.')) {
        $THEME['catbgcode']  = 'bgcolor="'.$THEME['catcolor'].'"';
        $THEME['catcss']     = 'background-color: '.$THEME['catcolor'].';';
    } else {
        $THEME['catbgcode']  = 'style="background-image: url('.$THEME['imgdir'].'/'.$THEME['catcolor'].')"';
        $THEME['catcss']     = 'background-image: url('.$THEME['imgdir'].'/'.$THEME['catcolor'].');';
    }

    if (false === strpos($THEME['top'], '.')) {
        $THEME['topbgcode'] = 'bgcolor="'.$THEME['top'].'"';
    } else {
        $THEME['topbgcode'] = 'style="background-image: url('.$THEME['imgdir'].'/'.$THEME['top'].')"';
    }

    if (false !== strpos($THEME['boardimg'], ',')) {
        $flashlogo = explode(",",$THEME['boardimg']);

        //check if it's an URL or just a filename
        $l = array();
        $l = parse_url($flashlogo[0]);
        if (!isset($l['scheme']) || !isset($l['host'])) {
            $flashlogo[0] = $imgdir.'/'.$flashlogo[0];
        }
        $THEME['logo'] = '<object type="application/x-shockwave-flash" data="'.$flashlogo[0].'" width="'.$flashlogo[1].'" height="'.$flashlogo[2].'"><param name="movie" value="'.$flashlogo[0].'" /></object>';
    } else {
        $l = array();
        $l = parse_url($THEME['boardimg']);
        if (!isset($l['scheme']) || !isset($l['host'])) {
            $THEME['boardimg'] = $THEME['imgdir'].'/'.$THEME['boardimg'];
        }
        $THEME['logo'] = '<a href="index.php"><img src="'.$THEME['boardimg'].'" alt="'.$SETTINGS['bbname'].'" border="0" /></a>';
    }

    // Font stuff...
    $fontedit = preg_replace('#(\D)#', '', $THEME['fontsize']);
    $fontsuf  = preg_replace('#(\d)#', '', $THEME['fontsize']);

    $THEME['font1'] = $fontedit-1 . $fontsuf;
    $THEME['font2'] = $THEME['fontsize'];
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

    $action = fetchFromRequest('action', X_GET|X_POST);

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
    if ( $SETTINGS['searchstatus'] == "on") {
        $links[] = '<img src="'.$THEME['imgdir'].'/search.gif" alt="'.$lang['altsearch'].'" border="0" /> <a href="misc.php?action=search"><font class="navtd">'.$lang['textsearch'].'</font></a>';
    }

    // Faq-link
    if ( $SETTINGS['faqstatus'] == "on") {
        $links[] = '<img src="'.$THEME['imgdir'].'/faq.gif" alt="'.$lang['altfaq'].'" border="0" /> <a href="faq.php"><font class="navtd">'.$lang['textfaq'].'</font></a>';
    }

    // Memberlist-link
    if ( $SETTINGS['memliststatus'] == "on") {
        $links[] = '<img src="'.$THEME['imgdir'].'/members_list.gif" alt="'.$lang['altmemberlist'].'" border="0" /> <a href="misc.php?action=list"><font class="navtd">'.$lang['textmemberlist'].'</font></a>';
    }

    // Today's posts-link
    if ( $SETTINGS['todaysposts'] == "on") {
        $links[] = '<img src="'.$THEME['imgdir'].'/todays_posts.gif" alt="'.$lang['alttodayposts'].'" border="0" /> <a href="today.php"><font class="navtd">'.$lang['navtodaysposts'].'</font></a>';
    }

    // Stats-link
    if ( $SETTINGS['stats'] == "on") {
        $links[] = '<img src="'.$THEME['imgdir'].'/stats.gif" alt="'.$lang['altstats'].'" border="0" /> <a href="stats.php"><font class="navtd">'.$lang['navstats'].'</font></a>';
    }

    // 'Forum Rules'-link
    if ( $SETTINGS['bbrules'] == "on") {
        $links[] = '<img src="'.$THEME['imgdir'].'/bbrules.gif" alt="'.$lang['altrules'].'" border="0" /> <a href="faq.php?page=forumrules"><font class="navtd">'.$lang['textbbrules'].'</font></a>';
    }

    $links = implode(' &nbsp; ', $links);

//Show all plugins
    if (!isset($plugname)) {
        $plugname = array();
    }
    $pluglinks = array();

    for($plugnum=1; $plugnum <= count($plugname); $plugnum++) {
        if (!empty($plugurl[$plugnum]) && !empty($plugname[$plugnum])) {
            if (trim($plugimg[$plugnum]) != '') {
                $img = '<img src="'.$plugimg[$plugnum].'" border="0" /> ';
            }else{
                $img = '';
            }

            if (X_ADMIN || $plugadmin[$plugnum] != "yes") {
                $pluglinks[] = ' &nbsp; '.$img.'<a href="'.$plugurl[$plugnum].'"><font class="navtd">'.$plugname[$plugnum].'</font></a>';
            }
        }
    }
    if (count($pluglinks) == 0) {
        $pluglink = '';
    }else{
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
            if ( $coppa == 'on') {
                $message = $lang['reggedonly'].' <a href="member.php?action=coppa">'.$lang['textregister'].'</a> '.$lang['textor'].' <a href="misc.php?action=login">'.$lang['textlogin'].'</a>';
            }else{
                $message = $lang['reggedonly'].' <a href="member.php?action=reg">'.$lang['textregister'].'</a> '.$lang['textor'].' <a href="misc.php?action=login">'.$lang['textlogin'].'</a>';
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
    if (!X_ADMIN && ($self['status'] == "Banned" || $result )) {
        eval('$css = "'.template('css').'";');
        error($lang['bannedmessage']);
    }

// if the user is registered, check for new u2u's
    $newu2umsg = '';
    if (X_MEMBER) {
        $query = $db->query("SELECT count(readstatus) FROM $table_u2u WHERE owner='".$self['username']."' AND folder='Inbox' AND readstatus='no'");
        $newu2unum = $db->result($query, 0);
        if ( $newu2unum > 0) {
            $newu2umsg = '<a href="#" onclick="Popup(\'u2u.php\', \'Window\', 700, 450);">'.$lang['newu2u1'].' '.$newu2unum.' '.$lang['newu2u2'].'</a>';
        }
    }
