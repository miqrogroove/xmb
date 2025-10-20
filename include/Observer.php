<?php

/**
 * eXtreme Message Board
 * XMB 1.10.01
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

use RuntimeException;

class Observer
{
    public function __construct(private Variables $vars)
    {
        // Property promotion.
    }

    /**
     * Assert presence and scope of PHP superglobal variables.
     *
     * @since 1.9.11.15
     */
    public function testSuperGlobals()
    {
        if (! is_array($_GET) || ! is_array($_POST) || ! is_array($_COOKIE) || ! is_array($_SERVER) || ! is_array($_FILES) || ! is_array($_REQUEST)) {
            header('HTTP/1.0 500 Internal Server Error');
            echo 'XMB could not find the PHP Superglobals.  Please check PHP configuration.  Detected variables_order setting: ' . ini_get('variables_order');
            throw new RuntimeException('PHP Superglobals are missing');
        }
    }

    /**
     * Kill the script and debug dirty output streams.
     *
     * @param string $error_source File name to mention if a non-empty buffer is found.
     * @param bool   $use_debug    Optional.  When FALSE the value of DEBUG is ignored.
     * @since 1.9.11
     */
    public function assertEmptyOutputStream($error_source, $use_debug = true)
    {
        $buffered_fault = (ob_get_length() > 0); // Checks top of buffer stack only.
        $unbuffered_fault = headers_sent();

        if ($buffered_fault || $unbuffered_fault) {
            if ($buffered_fault) header('HTTP/1.0 500 Internal Server Error');

            if ($use_debug && ! $this->vars->debug) {
                echo "Error: XMB failed to start.  Set DEBUG to TRUE in config.php to see file system details.";
            } elseif ($unbuffered_fault) {
                headers_sent($filepath, $linenum);
                echo "Error: XMB failed to start due to file corruption.  Please inspect $filepath at line number $linenum.";
            } else {
                $buffer = ob_get_clean();
                echo 'OB:';
                var_dump(ini_get('output_buffering'));
                if (isset($this->vars->settings['gzipcompress'])) {
                    echo '<br />GZ:';
                    var_dump($this->vars->settings['gzipcompress']);
                }
                echo "<br /><br />Error: XMB failed to start due to file corruption. "
                   . "Please inspect $error_source.  It has generated the following unexpected output:$buffer";
            }
            exit;
        }
    }
}
