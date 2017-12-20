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

// This is an old compatibility trick, kept in case superglobals are disabled.
if (!isset($_SERVER)) {
    $_GET = &$HTTP_GET_VARS;
    $_POST = &$HTTP_POST_VARS;
    $_ENV = &$HTTP_ENV_VARS;
    $_SERVER = &$HTTP_SERVER_VARS;
    $_COOKIE = &$HTTP_COOKIE_VARS;
    $_FILES = &$HTTP_POST_FILES;
    $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);
}

// make sure magic_quotes_runtime doesn't kill XMB
ini_set('magic_quotes_runtime', 0);

// force registerglobals
if (is_array($_REQUEST)) {
    extract($_REQUEST, EXTR_SKIP);
}

/**
 * Kill the script and debug dirty output streams.
 *
 * @author Robert Chapin (miqrogroove)
 * @param string $error_source File name to mention if a dirty buffer is found.
 * @param bool   $use_debug    Optional.  When FALSE the value of DEBUG is ignored.
 * @since 1.9.11
 */
function assertEmptyOutputStream($error_source, $use_debug = TRUE) {
    global $SETTINGS;
    
    $buffered_fault = (ob_get_length() > 0); // Checks top of buffer stack only.
    $unbuffered_fault = headers_sent();
    
    if ($buffered_fault Or $unbuffered_fault) {
        if ($buffered_fault) header('HTTP/1.0 500 Internal Server Error');

        if ($use_debug And defined('DEBUG') And DEBUG == FALSE) {
            echo "Error: XMB failed to start.  Set DEBUG to TRUE in config.php to see file system details.";
        } elseif ($unbuffered_fault) {
            headers_sent($filepath, $linenum);
            echo "Error: XMB failed to start due to file corruption.  Please inspect $filepath at line number $linenum.";
        } else {
            $buffer = ob_get_clean();
            echo 'OB:';
            var_dump(ini_get('output_buffering'));
            if (isset($SETTINGS['gzipcompress'])) {
                echo 'GZ:';
                var_dump($SETTINGS['gzipcompress']);
            }
            echo "<br /><br />Error: XMB failed to start due to file corruption. "
               . "Please inspect $error_source.  It has generated the following unexpected output:$buffer";
        }
        exit;
    }
}
?>
