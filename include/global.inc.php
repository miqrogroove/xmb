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

if (!defined('IN_CODE')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

// For all supported versions of PHP, we can trust but verify the variables_order setting.
testSuperGlobals();

// force registerglobals
extract($_REQUEST, EXTR_SKIP);

/**
 * Assert presence and scope of PHP superglobal variables.
 *
 * @since 1.9.11.15
 */
function testSuperGlobals() {
    if ( ! is_array( $_GET ) || ! is_array( $_POST ) || ! is_array( $_COOKIE ) || ! is_array( $_SERVER ) || ! is_array( $_FILES ) || ! is_array( $_REQUEST ) ) {
        header('HTTP/1.0 500 Internal Server Error');
        exit('XMB could not find the PHP Superglobals.  Please check PHP configuration.  Detected variables_order setting: ' . ini_get('variables_order'));
    }
}

/**
 * Kill the script and debug dirty output streams.
 *
 * @param string $error_source File name to mention if a dirty buffer is found.
 * @param bool   $use_debug    Optional.  When FALSE the value of DEBUG is ignored.
 * @since 1.9.11
 */
function assertEmptyOutputStream($error_source, $use_debug = TRUE) {
    global $SETTINGS;
    
    $buffered_fault = (ob_get_length() > 0); // Checks top of buffer stack only.
    $unbuffered_fault = headers_sent();
    
    if ($buffered_fault || $unbuffered_fault) {
        if ($buffered_fault) header('HTTP/1.0 500 Internal Server Error');

        if ($use_debug && defined('DEBUG') && DEBUG == FALSE) {
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

return;