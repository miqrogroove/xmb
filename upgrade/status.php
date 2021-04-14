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

header( 'Expires: 0' );
header( 'X-Frame-Options: sameorigin' );

define('LOG_FILE', './upgrade.log');

$log = file_get_contents( LOG_FILE );
$check = substr($log, -14);
$done = '<!-- done. -->' == $check;
$error = '<!-- error -->' == $check;

?>
<html>
<head>
<?php if ( ! $done && ! $error ) { ?>
<meta http-equiv="refresh" content="2" />
<?php } ?>
</head>
<body>
<?php
// Display the log file in reverse order, so latest message is first.
$lines = explode( "\r\n", $log );
$counter = count( $lines );
while( count( $lines ) > 0 ) {
	echo $counter--, ". ", array_pop( $lines ), "<br>\n";
}
?>
</body>
</html>
<?php

if ( $done ) {
	rmFromDir( dirname(__FILE__) );
}

/**
 * Recursively deletes all files in the given path.
 *
 * @param string $path
 */
function rmFromDir($path) {
    if (is_dir($path)) {
        $stream = opendir($path);
        while(($file = readdir($stream)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            rmFromDir($path.'/'.$file);
        }
        closedir($stream);
        @rmdir($path);
    } else if (is_file($path)) {
        @unlink($path);
    }
}

?>