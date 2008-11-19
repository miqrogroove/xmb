<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Alpha Four - This software should not be used for any purpose after 31 January 2009.
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

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

error_reporting(E_ALL | E_STRICT);

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
    $secure = FALSE;
    if (isset($_SERVER['HTTPS'])) {
        if ($_SERVER['HTTPS'] != 'off') {
            $secure = TRUE;
        }
    }
    $host = $_SERVER['HTTP_HOST'];
    $path = substr($_SERVER['REQUEST_URI'], 0, strlen($pathsetting));

    $success = FALSE;
    if ($hostsetting != $host And $host != 'www'.$hostsetting) {
        $reason = 'Host names do not match.  '.$hostsetting.' should be '.$host;
    } elseif ($securesetting != $secure) {
        $reason = '$full_url should start with http'.($secure ? 's' : '').'://';
    } elseif ($pathsetting != $path) {
        $reason = 'URI paths do not match.<br />'.$pathsetting.' was expected, but server saw '.path;
    } elseif (substr($pathsetting, -1) != '/') {
        $reason = 'A forward-slash is required at the end of the URL.';
    } else {
        $success = TRUE;
    }

    if (!$success) {
        exit('Error: The $full_url setting in config.php appears to be incorrect.<br />'.$reason);
    }
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
