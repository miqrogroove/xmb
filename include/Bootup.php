<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2024, The XMB Group
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
 */

declare(strict_types=1);

namespace XMB;

use XMB\Session\Manager as SessionMgr;

use function assertEmptyOutputStream;
use function XMB\Validate\attrOut;
use function XMB\Validate\getInt;
use function XMB\Validate\onSubmit;
use function XMB\Validate\postedVar;
use function XMB\Validate\recodeOut;

class Bootup
{
    private DBStuff $db;
    
    public function __construct(private Template $template, private Variables $vars)
    {
        // Property promotion.
    }

    public function loadConfig()
    {
        require ROOT.'config.php';
        assertEmptyOutputStream('config.php');
        
        if ($ipcheck === 'on') $ipcheck = true;
        
        $this->vars->dbname = $dbname;
        $this->vars->dbuser = $dbuser;
        $this->vars->dbpw = $dbpw;
        $this->vars->dbhost = $dbhost;
        $this->vars->database = $database;
        $this->vars->pconnect = (bool) $pconnect;
        $this->vars->tablepre = $tablepre;
        $this->vars->full_url = $full_url;
        $this->vars->comment_output = $comment_output;
        $this->vars->mailer = $mailer;
        $this->vars->plugname = $plugname;
        $this->vars->plugurl = $plugurl;
        $this->vars->plugadmin = $plugadmin;
        $this->vars->plugimg = $plugimg;
        $this->vars->ipcheck = $ipcheck;
        $this->vars->allow_spec_q = $allow_spec_q;
        $this->vars->show_full_info = $show_full_info;
        $this->vars->debug = $debug;
        $this->vars->log_mysql_errors = $log_mysql_errors;

        $config_array = [
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
            'comment_output' => 'COMMENTOUTPUT',
        ];
        foreach($config_array as $key => $value) {
            if ($this->vars->$key === $value) {
                header('HTTP/1.0 500 Internal Server Error');
                if (file_exists(ROOT.'install/')) {
                    exit('<h1>Error:</h1><br />The installation files ("./install/") have been found on the server. Please remove them as soon as possible. If you have not yet installed XMB, please do so at this time. Just <a href="./install/index.php">click here</a>.');
                }
                exit('Configuration Problem: XMB noticed that your config.php has not been fully configured.<br />The $'.$key.' has not been configured correctly.<br /><br />Please configure config.php before continuing.<br />Refresh the browser after uploading the new config.php (when asked if you want to resubmit POST data, click the \'OK\'-button).');
            }
        }
    }

    public function setVersion()
    {
        require ROOT.'include/version.php';
        assertEmptyOutputStream('version.php');

        $this->template->copyright = $copyright;
        $this->template->versioncompany = $versioncompany;
        if (! $show_full_info) {
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
        $this->template->versionlong = 'Powered by '.$versiongeneral.$alpha.$beta.$gamma.$service_pack;
        $this->template->versionbuild = $versionbuild;
        $this->vars->versionshort = $versionshort;

        if ($this->vars->debug) {
            $this->template->versionlong .= ' (Debug Mode)';
        }
    }

    public function setURL()
    {
        // Validate URL Configuration
        $this->vars->url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        if (empty($this->vars->full_url)) {
            header('HTTP/1.0 500 Internal Server Error');
            exit('<b>ERROR: </b><i>Please fill the $full_url variable in your config.php!</i>');
        } else {
            $array = parse_url($$this->vars->full_url);

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
                $boot->debugURLsettings($cookiesecure, $cookiedomain, $cookiepath);
            } elseif (0 == strlen($this->vars->url)) {
                header('HTTP/1.0 500 Internal Server Error');
                exit('Error: URL Not Found.  Set DEBUG to TRUE in config.php to see diagnostic details.');
            } elseif ($cookiesecure && $_SERVER['HTTPS'] !== 'on') {
                header('HTTP/1.0 404 Not Found');
                exit('XMB is configured for HTTPS access only.  Set DEBUG to TRUE in config.php to see diagnostic details.');
            }
            
            $this->vars->cookiedomain = $cookiedomain;
            $this->vars->cookiepath = $cookiepath;
            $this->vars->cookiesecure = $cookiesecure;
        }

        // Common XSS Protection: XMB disallows '<' and unencoded ':/' in all URLs.
        if (X_SCRIPT != 'search.php') {
            $url_check = Array('%3c', '<', ':/');
            foreach($url_check as $name) {
                if (strpos(strtolower($this->vars->url), $name) !== false) {
                    header('HTTP/1.0 403 Forbidden');
                    exit('403 Forbidden - URL rejected by XMB');
                }
            }
            unset($url_check);
        }

        // Check for double-slash problems in REQUEST_URI
        if (substr($this->vars->url, 0, strlen($cookiepath)) != $cookiepath || substr($this->vars->url, strlen($cookiepath), 1) == '/') {
            $fixed_url = str_replace('//', '/', $this->vars->url);
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
    }

    public function debugURLsettings($securesetting, $hostsetting, $pathsetting)
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            if (!headers_sent()) header('HTTP/1.0 500 Internal Server Error');
            if (false === strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft')) {
                exit('Error: REQUEST_URI is missing.  Your server may be misconfigured or incompatible with XMB.');
            } elseif(!extension_loaded('ISAPI') && !isset($_ENV['PHP_FCGI_MAX_REQUESTS'])) {
                exit('Error: FastCGI is missing or not configured on your server.');
            } else {
                exit('Error: Unexpected environment.  Please make sure FastCGI is working.');
            }
        }

        $secure = false;
        if (isset($_SERVER['HTTPS'])) {
            if ($_SERVER['HTTPS'] != 'off') {
                $secure = true;
            }
        }
        if (substr($hostsetting, 0, 1) == '.') {
            $hostsetting = substr($hostsetting, 1);
        }
        $host = substr($_SERVER['HTTP_HOST'], 0, strcspn($_SERVER['HTTP_HOST'], ':'));
        if (strpos($host, '.') === false || preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $host)) {
            $host = '';
        }
        $path = substr($_SERVER['REQUEST_URI'], 0, strlen($pathsetting));

        $success = false;
        if ($hostsetting !== $host && $host !== 'www.'.$hostsetting) {
            if (0 == strlen($hostsetting)) $hostsetting = 'The domain name';
            if (0 == strlen($host)) $host = $_SERVER['HTTP_HOST'];
            $reason = 'Host names do not match.  '.$hostsetting.' should be '.$host;
        } elseif ($securesetting != $secure) {
            $reason = '$full_url should start with http'.($secure ? 's' : '').'://';
        } elseif ($pathsetting !== $path && $pathsetting != '') {
            $reason = 'URI paths do not match.<br />'.$pathsetting.' was expected, but server saw '.$path;
        } elseif (substr($pathsetting, -1) != '/') {
            $reason = 'A forward-slash is required at the end of the URL.';
        } else {
            $success = true;
        }

        if (!$success) {
            if (!headers_sent()) header('HTTP/1.0 500 Internal Server Error');
            exit('Error: The $full_url setting in config.php appears to be incorrect.<br />'.$reason);
        }
    }

    public function setBrowser()
    {
        // discover the most likely browser
        // so we can use javascript specifically made for it
        $browser = 'mozilla'; // default to mozilla
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
        $this->template->browser = $browser;
    }

    public function setIP()
    {
        $this->vars->onlineip = $_SERVER['REMOTE_ADDR'];

        //Checks the IP-format, if it's not a IPv4 type, it will be blocked, safe to remove....
        if ($this->vars->ipcheck) {
            if (1 != preg_match('@^(\\d{1,3}\\.){3}\\d{1,3}$@', $this->vars->onlineip)) {
                header('HTTP/1.0 403 Forbidden');
                exit('Access to this website is currently not possible as your IP address has been blocked.');
            }
        }
    }
    
    public function connectDB(): DBStuff
    {
        define('X_PREFIX', $this->vars->tablepre); // Historical table name prefix constant

        // Force upgrade to mysqli.
        if ('mysql' === $this->vars->database) {
            $this->vars->database = 'mysqli';
        }

        require ROOT . 'db/' . $this->vars->database . '.php';

        switch ($this->vars->database) {
            default:
                $this->db = new \XMB\MySQLiDatabase();
        }
        $this->db->connect(
            $this->vars->dbhost,
            $this->vars->dbuser,
            $this->vars->dbpw,
            $this->vars->dbname,
            $this->vars->pconnect,
            force_db: true,
        );

        return $this->db;
    }
    
    public function loadSettings()
    {
        // This is normally the first query on the connection, so do not panic unless query logging is enabled.
        $panic = $this->vars->debug && $this->vars->log_mysql_errors;
        $squery = $this->db->query("SELECT * FROM ".X_PREFIX."settings", $panic);

        // Assume XMB is not installed if first query fails.
        if (false === $squery) {
            header('HTTP/1.0 500 Internal Server Error');
            if (file_exists(ROOT.'install/')) {
                exit('XMB is not yet installed. Please do so at this time. Just <a href="./install/index.php">click here</a>.');
            }
            exit('Fatal Error: XMB is not installed. Please upload the /install/ directory to begin.');
        }
        if ($this->db->num_rows($squery) == 0) {
            header('HTTP/1.0 500 Internal Server Error');
            exit('Fatal Error: The XMB settings table is empty.');
        }
        // Check schema for upgrade compatibility back to 1.8 SP2.
        $row = $this->db->fetch_array($squery);
        if (isset($row['langfile'])) {
            // Schema version <= 4 has only one row.
            foreach ($row as $key => $val) {
                $this->vars->settings[$key] = $val;
            }
            if (! isset($this->vars->settings['schema_version'])) {
                $this->vars->settings['schema_version'] = '0';
            }
        } else {
            // Current schema uses a separate row for each setting.
            do {
                $this->vars->settings[$row['name']] = $row['value'];
            } while ($row = $this->db->fetch_array($squery));
        }
        $this->db->free_result($squery);
        unset($row);

        if ((int) $this->vars->settings['postperpage'] < 5) {
            $this->vars->settings['postperpage'] = '30';
        }

        if ((int) $this->vars->settings['topicperpage'] < 5) {
            $this->vars->settings['topicperpage'] = '30';
        }

        if ((int) $this->vars->settings['memberperpage'] < 5) {
            $this->vars->settings['memberperpage'] = '30';
        }

        if ((int) $this->vars->settings['smcols'] < 1) {
            $this->vars->settings['smcols'] = '4';
        }

        // The latest upgrade script advertises compatibility with v1.8 SP2.  These defaults might not exist yet.
        if (empty($this->vars->settings['onlinetodaycount']) || (int) $this->vars->settings['onlinetodaycount'] < 5) {
            $this->vars->settings['onlinetodaycount'] = '30';
        }

        if (
            empty($this->vars->settings['captcha_code_length'])
            || (int) $this->vars->settings['captcha_code_length'] < 3 
            || (int) $this->vars->settings['captcha_code_length'] >= X_NONCE_KEY_LEN
        ) {
            $this->vars->settings['captcha_code_length'] = '8';
        }

        if (empty($this->vars->settings['ip_banning'])) {
            $this->vars->settings['ip_banning'] == 'off';
        }

        if (empty($this->vars->settings['schema_version'])) {
            $this->vars->settings['schema_version'] == '0';
        }

        // Validate maxattachsize with PHP configuration.
        $inimax = phpShorthandValue('upload_max_filesize');
        if (empty($this->vars->settings['maxattachsize']) || $inimax < (int) $this->vars->settings['maxattachsize']) {
            $this->vars->settings['maxattachsize'] = $inimax;
        }
    }
    
    public function setHeaders()
    {
        // Set Global HTTP Headers
        $script = basename($_SERVER['SCRIPT_NAME']);
        if ($script != 'files.php' && $script != 'css.php') {
            header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }

        ini_set('user_agent', 'XMB-eXtreme-Message-Board/1.9; ' . $this->vars->full_url);

        $oldtopics = postedVar(
            varname: 'oldtopics',
            htmlencode: false,
            dbescape: false,
            sourcearray: 'c',
        );

        if ($script != 'viewthread.php' && ! empty($oldtopics)) {
            put_cookie('oldtopics', $oldtopics, (time() + $this->vars::ONLINE_TIMER));
        }
    }
    
    public function prepareSession(): array
    {
        $serror = '';
        $action = postedVar('action', '', false, false, false, 'g');
        $script = basename($_SERVER['SCRIPT_NAME']);

        // Check if the client is ip-banned
        if ($this->vars->settings['ip_banning'] === 'on') {
            $ips = explode(".", $onlineip);
            if (count($ips) === 4) {
                $query = $db->query("SELECT id FROM ".X_PREFIX."banned WHERE ((ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')) AND NOT (ip1='-1' AND ip2='-1' AND ip3='-1' AND ip4='-1')");
                $result = $db->num_rows($query);
                $db->free_result($query);
                if ($result > 0) {
                    // Block all non-admins
                    $serror = 'ip';
                }
                unset($result);
            }
            unset($ips);
        }

        // Check other access restrictions
        if ('' === $serror) {
            if ((int) $this->vars->settings['schema_version'] < 5) {
                // During upgrade of session system, no features are available.
                $serror = 'bstatus';
            } elseif (($action == 'login' || $action == 'lostpw') && $script == 'misc.php') {
                // Allow login
            } elseif ($script == 'css.php' || $script == 'lost.php') {
                // Allow stylesheets and password resets
            } elseif ($this->vars->settings['bbstatus'] == 'off') {
                // Block all non-admins
                $serror = 'bstatus';
            } elseif ($this->vars->settings['regstatus'] == 'on' && ($action == 'reg' || $action == 'captchaimage') && ($script == 'misc.php' || $script == 'member.php')) {
                // Allow registration
            } elseif ($this->vars->settings['regviewonly'] == 'on') {
                // Block all guests
                $serror = 'guest';
            } else {
                // Allow everything else
            }
        }

        // Authenticate session or login credentials.
        $force_inv = false;
        if ((int) $this->vars->settings['schema_version'] < 5) {
            $mode = 'disabled';
        } else if (defined('XMB_UPGRADE') && isset($_POST['xmbpw'])) {
            $mode = 'login';
        } else if ($action == 'login' && onSubmit('loginsubmit') && $script == 'misc.php') {
            $mode = 'login';
            $force_inv = (formInt('hide') == 1);
        } else if ($action == 'logout' && $script == 'misc.php') {
            $mode = 'logout';
        } else {
            $mode = 'resume';
        }

        return [
            'force_inv' => $force_inv,
            'mode' => $mode,
            'serror' => $serror,
        ];
    }
    
    public function setCharset()
    {
        // Specify all charset variables as early as possible.
        $action = postedVar('action', '', false, false, false, 'g');
        $download = getInt('download');

        if ($action != 'attachment' && !($action == 'templates' && $download != 0) && !($action == 'themes' && $download != 0)) {
            header('Content-type: text/html;charset=' . $this->vars->lang['charset']);
        }
        if (function_exists('mb_list_encodings')) {
            // The list of charsets common to mb_string and htmlspecialchars is extremely restrictive.
            switch (strtoupper($this->vars->lang['charset'])) {
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
            if (! in_array($newcharset, mb_list_encodings())) {
                $newcharset = 'ISO-8859-1';
            }
        } else {
            $newcharset = 'ISO-8859-1';
        }
        ini_set('default_charset', $newcharset);
    }
    
    public function setBaseElement()
    {
        // Create a base element so that links aren't broken if scripts are accessed using unexpected paths.
        // XMB expects all links to be relative to $full_url + script name + query string.
        $querystring = strstr($this->vars->url, '?');
        if ($querystring === false) {
            $querystring = '';
        }
        $querystring = preg_replace('/[^\x20-\x7e]/', '', $querystring);
        if ($this->vars->url == $this->vars->cookiepath) {
            $this->template->baseelement = '<base href="' . $this->vars->full_url . '" />';
        } else {
            $this->template->baseelement = '<base href="' . $this->vars->full_url . basename($_SERVER['SCRIPT_NAME']) . attrOut($querystring) . '" />';
        }
    }
    
    public function setVisit()
    {
        // Read last visit cookies
        $xmblva = getInt('xmblva', 'c'); // Previous request timestamp.
        $xmblvb = getInt('xmblvb', 'c'); // Ending timestamp of previous session.
        $onlinetime = time();

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
                $cplink = ' - <a href="cp.php">' . $this->vars->lang['textcp'] . '</a>';
            } else {
                $cplink = '';
            }
            $loginout = '<a href="misc.php?action=logout">' . $this->vars->lang['textlogout'] . '</a>';
            $memcp = '<a href="memcp.php">' . $this->vars->lang['textusercp'] . '</a>';
            $u2ulink = '<a href="u2u.php" onclick="Popup(this.href, \'Window\', 700, 450); return false;">' . $this->vars->lang['banu2u'] . '</a> - ';
            $this->template->notify = $this->vars->lang['loggedin']
                    . ' <a href="member.php?action=viewpro&amp;member=' . recodeOut($this->vars->xmbuser) . '">' . $this->vars->xmbuser . '</a><br />['
                    . $loginout . ' - ' . $u2ulink . '' . $memcp . '' . $cplink . ']';

            // Update lastvisit in the header shown
            if ((int) $this->vars->self['lastvisit'] < $thetime || (
                (int) $this->vars->self['lastvisit'] > $thetime + $this->vars::ONLINE_TIMER && (int) $this->vars->self['lastvisit'] < $onlinetime - $this->vars::ONLINE_TIMER
            )) {
                $thetime = $this->vars->self['lastvisit'];
            }
            $lastlocal = $thetime + ($this->vars->self['timeoffset'] * 3600) + ($this->vars->settings['addtime'] * 3600);
            $lastdate = gmdate($dateformat, $lastlocal);
            $lasttime = gmdate($timecode, $lastlocal);
            $this->template->lastvisittext = $this->vars->lang['lastactive'] . ' ' . $lastdate . ' ' . $this->vars->lang['textat'] . ' ' . $lasttime;
        } else {
            // Checks for the possibility to register
            if ($this->vars->settings['regstatus'] == 'on') {
                $this->template->reglink = '- <a href="member.php?action=reg">' . $this->vars->lang['textregister'] . '</a>';
            } else {
                $this->template->reglink = '';
            }
            $loginout = '<a href="misc.php?action=login">' . $this->vars->lang['textlogin'] . '</a>';
            $this->template->notify = $this->vars->lang['notloggedin'] . ' [' . $loginout . ' ' . $this->template->reglink . ']';
            $this->template->lastvisittext = '';
        }

        // Update last visit cookies
        put_cookie('xmblva', $onlinetime, ($onlinetime + (86400*365))); // lva == now
        put_cookie('xmblvb', $thetime, ($onlinetime + $this->vars::ONLINE_TIMER)); // lvb == last visit
        $this->vars->lastvisit = $thetime; // Used by forumdisplay
    }

    public function sendErrors(SessionMgr $session)
    {
        switch ($session->getSError()) {
        case 'ip':
            if (! X_ADMIN) {
                header('HTTP/1.0 403 Forbidden');
                error($this->vars->lang['bannedmessage']);
            }
            break;
        case 'bstatus':
            if (! X_ADMIN) {
                header('HTTP/1.0 503 Service Unavailable');
                header('Retry-After: 3600');
                if ($this->vars->settings['bboffreason'] != '') {
                    message(nl2br($this->vars->settings['bboffreason']));
                } else {
                    message($this->vars->lang['textbstatusdefault']);
                }
            }
            break;
        case 'guest':
            if (X_GUEST) {
                if ($this->vars->settings['bboffreason']['regstatus'] == 'on') {
                    $message = $this->vars->lang['reggedonly'].' '.$this->template->reglink.' '.$this->vars->lang['textor'].' <a href="misc.php?action=login">'.$this->vars->lang['textlogin'].'</a>';
                } else {
                    $message = $this->vars->lang['reggedonly'].' <a href="misc.php?action=login">'.$this->vars->lang['textlogin'].'</a>';
                }
                message($message);
            }
            break;
        }
    }
    
    public function checkU2U(SQL $sql)
    {
        // check for new u2u's
        $newu2unum->countU2UInbox($this->vars->self['username']);
        $newu2umsg = '';
        if ($newu2unum > 0) {
            $newu2umsg = "<a href='u2u.php' onclick=\"Popup(this.href, 'Window', 700, 450); return false;\">" . $this->vars->lang['newu2u1'] . ' ' . $newu2unum . ' ' . $this->vars->lang['newu2u2'] . '</a>';
            // Popup Alert
            if ('2' === $this->vars->self['u2ualert'] || ('1' === $this->vars->self['u2ualert'] && 'index.php' === basename($_SERVER['SCRIPT_NAME']))) {
                $newu2umsg .= '<script language="JavaScript" type="text/javascript">function u2uAlert() { ';
                if ($newu2unum == 1) {
                    $newu2umsg .= 'u2uAlertMsg = "' . $this->vars->lang['newu2u1'] . ' ' . $newu2unum . $this->vars->lang['u2ualert5'] . '"; ';
                } else {
                    $newu2umsg .= 'u2uAlertMsg = "' . $this->vars->lang['newu2u1'] . ' ' . $newu2unum . $this->vars->lang['u2ualert6'] . '"; ';
                }
                $newu2umsg .= "if (confirm(u2uAlertMsg)) { Popup('u2u.php', 'testWindow', 700, 450); } } setTimeout('u2uAlert();', 10);</script>";
            }
        }
        $this->template->newu2umsg = $newu2umsg;
    }
    
    public function createNavbarLinks()
    {
        $links = [];
        $imgdir = $this->vars->theme['imgdir'];

        // Search-link
        $searchlink = makeSearchLink();

        // Faq-link
        if ($this->vars->settings['faqstatus'] == 'on') {
            $links[] = '<img src="' . $imgdir . '/top_faq.gif" alt="" border="0" /> <a href="faq.php"><font class="navtd">' . $this->vars->lang['textfaq'] . '</font></a>';
        }

        // Memberlist-link
        if ($this->vars->settings['memliststatus'] == 'on') {
            $links[] = '<img src="' . $imgdir . '/top_memberslist.gif" alt="" border="0" /> <a href="misc.php?action=list"><font class="navtd">' . $this->vars->lang['textmemberlist'] . '</font></a>';
        }

        // Today's posts-link
        if ($this->vars->settings['todaysposts'] == 'on') {
            $links[] = '<img src="' . $imgdir . '/top_todaysposts.gif" alt="" border="0" /> <a href="today.php"><font class="navtd">' . $this->vars->lang['navtodaysposts'] . '</font></a>';
        }

        // Stats-link
        if ($this->vars->settings['stats'] == 'on') {
            $links[] = '<img src="' . $imgdir . '/top_stats.gif" alt="" border="0" /> <a href="stats.php"><font class="navtd">' . $this->vars->lang['navstats'] . '</font></a>';
        }

        // 'Forum Rules'-link
        if ($this->vars->settings['bbrules'] == 'on') {
            $links[] = '<img src="' . $imgdir . '/top_bbrules.gif" alt="" border="0" /> <a href="faq.php?page=forumrules"><font class="navtd">' . $this->vars->lang['textbbrules'] . '</font></a>';
        }

        $this->template->links = implode(' &nbsp; ', $links);
    }

    public function makePlugLinks()
    {
        // Show all plugins
        $pluglinks = [];
        foreach($this->vars->plugname as $plugnum => $item) {
            if (!empty($this->vars->plugurl[$plugnum]) && !empty($item)) {
                if (trim($this->vars->plugimg[$plugnum]) != '') {
                    $img = '&nbsp;<img src="'.$this->vars->plugimg[$plugnum].'" border="0" alt="'.$this->vars->plugname[$plugnum].'" />&nbsp;';
                } else {
                    $img = '';
                }

                if ($this->vars->plugadmin[$plugnum] != true || X_ADMIN) {
                    $pluglinks[] = $img.'<a href="'.$this->vars->plugurl[$plugnum].'"><font class="navtd">'.$this->vars->plugname[$plugnum].'</font></a>&nbsp;';
                }
            }
        }
        
        $this->template->pluglink = implode('&nbsp;', $pluglinks);
    }

    public function makeQuickJump()
    {
        // create forum jump
        if ($this->vars->settings['quickjump_status'] == 'on') {
            $this->template->quickjump = forumJump();
        }
    }
    
    public function startCompression()
    {
        $action = postedVar('action', '', false, false, false, 'g');
        if (
            $this->vars->settings['gzipcompress'] == 'on'
            && $action != 'captchaimage'
            && basename($_SERVER['SCRIPT_NAME']) != 'files.php'
            && ! $this->vars->debug
        ) {
            if (($res = @ini_get('zlib.output_compression')) > 0) {
                // leave it
            } else if ($res === false) {
                // ini_get not supported. So let's just leave it
            } else {
                if (function_exists('gzopen')) {
                    $r = @ini_set('zlib.output_compression', 4096);
                    $r2 = @ini_set('zlib.output_compression_level', '3');
                    if (false === $r || false === $r2) {
                        ob_start('ob_gzhandler');
                    }
                } else {
                    ob_start('ob_gzhandler');
                }
            }
        }
    }
}
