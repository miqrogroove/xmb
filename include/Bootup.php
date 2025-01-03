<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
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

/**
 * Provides some of the procedural logic formerly in header.php.
 *
 * @since 1.10.00
 */
class Bootup
{
    public function __construct(private Template $template, private Variables $vars)
    {
        // Property promotion.
    }

    public function loadConfig()
    {
        require ROOT.'config.php';
        
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

        $this->template->copyright = $copyright;
        $this->template->versioncompany = $versioncompany;
        if (! $this->vars->show_full_info) {
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
        $this->vars->versiongeneral = $versiongeneral;

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
            $array = parse_url($this->vars->full_url);

            $cookiesecure = ($array['scheme'] == 'https');

            $cookiedomain = $array['host'];
            if (strpos($cookiedomain, '.') === false || preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $cookiedomain)) {
                $cookiedomain = '';
            } elseif (substr($cookiedomain, 0, 4) === 'www.') {
                $cookiedomain = substr($cookiedomain, 3);
            }

            if (!isset($array['path'])) {
                $array['path'] = '/';
            }
            $cookiepath = $array['path'];

            if ($this->vars->debug) {
                $this->debugURLsettings($cookiesecure, $cookiedomain, $cookiepath);
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
        if (basename($_SERVER['SCRIPT_NAME']) != 'search.php') {
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
                $fixed_url = $this->vars->full_url . substr($fixed_url, strlen($cookiepath));
                header('HTTP/1.0 301 Moved Permanently');
                header("Location: $fixed_url");
                exit('XMB detected an invalid URL');
            }
        }
    }

    private function debugURLsettings($securesetting, $hostsetting, $pathsetting)
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
                $db = new \XMB\MySQLiDatabase($this->vars->debug, $this->vars->log_mysql_errors);
        }
        $db->connect(
            $this->vars->dbhost,
            $this->vars->dbuser,
            $this->vars->dbpw,
            $this->vars->dbname,
            $this->vars->pconnect,
            force_db: true,
        );

        return $db;
    }
}