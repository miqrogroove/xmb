<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2017, The XMB Group
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

// For all supported versions of PHP, we can trust but verify the variables_order setting.
testSuperGlobals();

// make sure magic_quotes_runtime doesn't kill XMB
if (get_magic_quotes_runtime()) set_magic_quotes_runtime(false);

// force registerglobals
extract($_REQUEST, EXTR_SKIP);

/**
 * Assert presence and scope of PHP superglobal variables.
 *
 * @since 1.9.11.14
 */
function testSuperGlobals() {
    if (!is_array($_GET) or !is_array($_POST) or !is_array($_COOKIE) or !is_array($_SERVER) or !is_array($_FILES) or !is_array($_REQUEST)) {
        header('HTTP/1.0 500 Internal Server Error');
        exit('XMB could not find the PHP Superglobals.  Please check PHP configuration.  Detected variables_order setting: ' . ini_get('variables_order'));
    }
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
