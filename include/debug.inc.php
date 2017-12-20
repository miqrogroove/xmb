<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2012, The XMB Group
 * http://www.xmbforum2.com/
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

if (!defined('IN_CODE')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

if ($show_full_info) {
    $versionlong .= ' (Debug Mode)';
} else {
    $alpha = '';
    $beta = '';
    $gamma = '';
    $service_pack = '';
    $versionbuild = '[HIDDEN]';
    $versionlong = 'Powered by XMB (Debug Mode)';
}

function debugURLsettings($securesetting, $hostsetting, $pathsetting) {
    if (!isset($_SERVER['REQUEST_URI'])) {
        if (!headers_sent()) header('HTTP/1.0 500 Internal Server Error');
        if (FALSE === strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft')) {
            exit('Error: REQUEST_URI is missing.  Your server may be misconfigured or incompatible with XMB.');
        } elseif(!extension_loaded('ISAPI') and !isset($_ENV['PHP_FCGI_MAX_REQUESTS'])) {
            if (version_compare(phpversion(), '5.2.8', '>')) {
                exit('Error: FastCGI is missing or not configured on your server.');
            } else {
                exit('Error: XMB is not compatible with PHP IIS CGI mode.  ISAPI mode is recommended.');
            }
        } else {
            exit('Error: Unexpected environment.  Please make sure FastCGI is working.');
        }
    }

    $secure = FALSE;
    if (isset($_SERVER['HTTPS'])) {
        if ($_SERVER['HTTPS'] != 'off') {
            $secure = TRUE;
        }
    }
    if (substr($hostsetting, 0, 1) == '.') {
        $hostsetting = substr($hostsetting, 1);
    }
    $host = substr($_SERVER['HTTP_HOST'], 0, strcspn($_SERVER['HTTP_HOST'], ':'));
    if (strpos($host, '.') === FALSE || preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $host)) {
        $host = '';
    }
    $path = substr($_SERVER['REQUEST_URI'], 0, strlen($pathsetting));

    $success = FALSE;
    if ($hostsetting != $host And $host != 'www.'.$hostsetting) {
        if (0 == strlen($hostsetting)) $hostsetting = 'The domain name';
        if (0 == strlen($host)) $host = $_SERVER['HTTP_HOST'];
        $reason = 'Host names do not match.  '.$hostsetting.' should be '.$host;
    } elseif ($securesetting != $secure) {
        $reason = '$full_url should start with http'.($secure ? 's' : '').'://';
    } elseif ($pathsetting != $path And $pathsetting != '') {
        $reason = 'URI paths do not match.<br />'.$pathsetting.' was expected, but server saw '.$path;
    } elseif (substr($pathsetting, -1) != '/') {
        $reason = 'A forward-slash is required at the end of the URL.';
    } else {
        $success = TRUE;
    }

    if (!$success) {
        if (!headers_sent()) header('HTTP/1.0 500 Internal Server Error');
        exit('Error: The $full_url setting in config.php appears to be incorrect.<br />'.$reason);
    }
}

function printAllQueries() {
    global $db;
    
    $stuff = array();
    if (X_SADMIN) {
        $stuff[] = '<table style="width: 97%;"><colgroup span="2" /><tr><td style="width: 2em;">#</td><td style="width: 8em;">Duration:</td><td>Query:</td></tr>';
        foreach($db->querylist as $key=>$val) {
            $val = mysql_syn_highlight(cdataOut($val));
            $stuff[] = '<tr><td><strong>'.++$key.'.</strong></td><td>'.number_format($db->querytimes[$key-1], 8).'</td><td>'.$val.'</td></tr>';
        }
        $stuff[] = '</table>';
    }
    return implode("\n", $stuff);
}

function mysql_syn_highlight($query) {
    global $tables, $tablepre;

    $find = array();
    $replace = array();

    foreach($tables as $name) {
        $find[] = $tablepre.$name;
    }

    $find[] = 'SELECT';
    $find[] = 'UPDATE';
    $find[] = 'DELETE';
    $find[] = 'INSERT INTO ';
    $find[] = ' WHERE ';
    $find[] = ' ON ';
    $find[] = ' FROM ';
    $find[] = ' GROUP BY ';
    $find[] = 'ORDER BY ';
    $find[] = ' LEFT JOIN ';
    $find[] = ' IN ';
    $find[] = ' SET ';
    $find[] = ' AS ';
    $find[] = '(';
    $find[] = ')';
    $find[] = ' ASC';
    $find[] = ' DESC';
    $find[] = ' AND ';
    $find[] = ' OR ';
    $find[] = ' NOT';

    foreach($find as $key=>$val) {
        $replace[$key] = '</em><strong>'.$val.'</strong><em>';
    }

    return '<em>'.str_replace($find, $replace, $query).'</em>';
}

?>
