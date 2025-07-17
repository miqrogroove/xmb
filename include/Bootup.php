<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

use XMBVersion;

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
        if (! is_readable(ROOT . 'config.php')) {
            header('HTTP/1.0 500 Internal Server Error');
            exit('XMB is not yet installed.<br />The <code>config.php</code> file was not found.<br /><br />To start the install, just <a href="install/">click here</a>.');
        }

        require ROOT . 'config.php';
        
        $this->vars->dbname = $dbname;
        $this->vars->dbuser = $dbuser;
        $this->vars->dbpw = $dbpw;
        $this->vars->dbhost = $dbhost;
        $this->vars->database = $database;
        $this->vars->pconnect = (bool) $pconnect;
        $this->vars->tablepre = $tablepre;
        $this->vars->full_url = $full_url;
        $this->vars->comment_output = (bool) $comment_output;
        if (isset($mailer)) $this->vars->mailer = $mailer;
        $this->vars->plugname = $plugname;
        $this->vars->plugurl = $plugurl;
        $this->vars->plugadmin = $plugadmin;
        $this->vars->plugimg = $plugimg;
        $this->vars->allow_spec_q = (bool) $allow_spec_q;
        $this->vars->show_full_info = (bool) $show_full_info;
        $this->vars->debug = (bool) $debug;
        $this->vars->log_mysql_errors = (bool) $log_mysql_errors;

        $config_array = [
            'dbname' => 'DB/NAME',
            'dbuser' => 'DB/USER',
            'dbpw' => 'DB/PW',
            'dbhost' => 'DB_HOST',
            'database' => 'DB_TYPE',
            'tablepre' => 'TABLE/PRE',
            'full_url' => 'FULLURL',
            'allow_spec_q' => 'SPECQ',
            'show_full_info' => 'SHOWFULLINFO',
            'comment_output' => 'COMMENTOUTPUT',
        ];
        foreach ($config_array as $key => $value) {
            if ($this->vars->$key === $value) {
                header('HTTP/1.0 500 Internal Server Error');
                exit('Configuration Problem: XMB is not yet installed.<br />The <code>$'.$key.'</code> has not been specified in <code>config.php</code>.<br /><br />To start the install, just <a href="install/">click here</a>.');
            }
        }
    }

    public function setVersion()
    {
        $source = new XMBVersion();
        $data = $source->get();

        $this->template->copyright = $data['copyright'];
        $this->template->versioncompany = $data['company'];
        if (! $this->vars->show_full_info) {
            $versionshort = '';
            $versiongeneral = 'XMB';
            $stage = '';
            $versionbuild = '[HIDDEN]';
        } else {
            $versionshort = $data['version'];
            $versiongeneral = 'XMB ' . $data['versionExt'];
            $stage = $data['versionStage'];
            $versionbuild = $data['versionDate'];
        }
        $this->template->versionbuild = $versionbuild;
        $this->vars->versionshort = $versionshort;
        $this->vars->versiongeneral = $versiongeneral;

        // "Debug Mode" and "Powered by" string usage has moved to Login::elevateUser() for translation.
    }

    public function setURL()
    {
        // Validate URL Configuration
        $this->vars->url = $_SERVER['REQUEST_URI'] ?? '';

        if (empty($this->vars->full_url)) {
            header('HTTP/1.0 500 Internal Server Error');
            exit('<b>ERROR: </b><i>Please fill the $full_url variable in your config.php!</i>');
        }

        $this->parseURL($this->vars->full_url);

        if ($this->vars->debug) {
            $this->debugURLsettings($this->vars->cookiesecure, $this->vars->cookiedomain, $this->vars->cookiepath);
        } elseif (0 == strlen($this->vars->url)) {
            header('HTTP/1.0 500 Internal Server Error');
            exit('Error: URL Not Found.  Set DEBUG to TRUE in config.php to see diagnostic details.');
        } elseif ($this->vars->cookiesecure && $_SERVER['HTTPS'] !== 'on') {
            header('HTTP/1.0 404 Not Found');
            exit('XMB is configured for HTTPS access only.  Set DEBUG to TRUE in config.php to see diagnostic details.');
        }

        // Common XSS Protection: XMB disallows '<' and unencoded ':/' in all URLs.
        $url_check = ['%3c', '<', ':/'];
        foreach ($url_check as $name) {
            if (strpos(strtolower($this->vars->url), $name) !== false) {
                header('HTTP/1.0 403 Forbidden');
                exit('403 Forbidden - URL rejected by XMB');
            }
        }
        unset($url_check);

        // Check for double-slash problems in REQUEST_URI
        if (substr($this->vars->url, 0, strlen($this->vars->cookiepath)) != $this->vars->cookiepath || substr($this->vars->url, strlen($this->vars->cookiepath), 1) == '/') {
            $fixed_url = str_replace('//', '/', $this->vars->url);
            if (substr($fixed_url, 0, strlen($this->vars->cookiepath)) != $this->vars->cookiepath || substr($fixed_url, strlen($this->vars->cookiepath), 1) == '/' || $fixed_url != preg_replace('/[^\x20-\x7e]/', '', $fixed_url)) {
                header('HTTP/1.0 404 Not Found');
                exit('XMB detected an invalid URL.  Set DEBUG to TRUE in config.php to see diagnostic details.');
            } else {
                $fixed_url = $this->vars->full_url . substr($fixed_url, strlen($this->vars->cookiepath));
                header('HTTP/1.0 301 Moved Permanently');
                header("Location: $fixed_url");
                exit('XMB detected an invalid URL');
            }
        }
    }

    public function parseURL(string $full_url)
    {
        $array = parse_url($full_url);

        $cookiesecure = ($array['scheme'] == 'https');

        $cookiedomain = $array['host'];
        if (strpos($cookiedomain, '.') === false || preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $cookiedomain)) {
            $cookiedomain = '';
        } elseif (substr($cookiedomain, 0, 4) === 'www.') {
            $cookiedomain = substr($cookiedomain, 3);
        }

        if (! isset($array['path'])) {
            $array['path'] = '/';
        }
        $cookiepath = $array['path'];

        $this->vars->cookiedomain = $cookiedomain;
        $this->vars->cookiepath = $cookiepath;
        $this->vars->cookiesecure = $cookiesecure;        
    }

    /**
     * Assert reasonable accuracy of the $full_url config value.
     *
     * @since 1.9.11
     */
    public function debugURLsettings($securesetting, $hostsetting, $pathsetting)
    {
        if (! isset($_SERVER['REQUEST_URI'])) {
            if (! headers_sent()) header('HTTP/1.0 500 Internal Server Error');
            if (false === strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft')) {
                exit('Error: REQUEST_URI is missing.  Your server may be misconfigured or incompatible with XMB.');
            } elseif(! extension_loaded('ISAPI') && ! isset($_ENV['PHP_FCGI_MAX_REQUESTS'])) {
                exit('Error: FastCGI is missing or not configured on your server.');
            } else {
                exit('Error: Unexpected environment.  Please make sure FastCGI is working.');
            }
        }

        $secure = false;
        if (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $secure = true;
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

        if (! $success) {
            if (! headers_sent()) header('HTTP/1.0 500 Internal Server Error');
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
    }

    public function connectDB(): DBStuff
    {
        // Force upgrade to mysqli.
        if ('mysql' === $this->vars->database) {
            $this->vars->database = 'mysqli';
        }

        require_once ROOT . 'db/' . $this->vars->database . '.php';

        switch ($this->vars->database) {
            default:
                $db = new MySQLiDatabase($this->vars->debug, $this->vars->log_mysql_errors);
        }

        if ($this->vars->debug && defined('XMB\UPGRADE')) {
            $db->stopQueryLogging();
        }

        $db->connect(
            $this->vars->dbhost,
            $this->vars->dbuser,
            $this->vars->dbpw,
            $this->vars->dbname,
            $this->vars->pconnect,
        );

        return $db;
    }
}
