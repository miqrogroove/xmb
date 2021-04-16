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

//Delete me.
header('HTTP/1.0 403 Forbidden');
exit('This file is provided to illustrate customized XMB upgrade techniques.');

ignore_user_abort(TRUE);

//Script constants.
define('ROOT', '../'); // Location of XMB files relative to this script.

//Emulate logic needed from XMB's header.php file.
error_reporting(-1);
define('IN_CODE', TRUE);
$SETTINGS = array();
define('X_NONCE_KEY_LEN', 12);
require ROOT.'config.php';
if (DEBUG) {
    require(ROOT.'include/debug.inc.php');
} else {
    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR);
}
define('X_PREFIX', $tablepre);
if ( 'mysql' === $database ) $database = 'mysqli';
require ROOT.'db/'.$database.'.php';
require ROOT.'include/functions.inc.php';
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, TRUE);
$squery = $db->query("SELECT * FROM ".X_PREFIX."settings", (DEBUG && LOG_MYSQL_ERRORS));
if (FALSE === $squery) exit('Fatal Error: XMB is not installed.');
if ($db->num_rows($squery) == 0) exit('Fatal Error: The XMB settings table is empty.');

// Check schema for upgrade compatibility back to 1.8 SP2.
$row = $db->fetch_array( $squery );
if ( isset( $row['langfile'] ) ) {
    // Schema version <= 4 has only one row.
    foreach ( $row as $key => $val ) {
        $SETTINGS[$key] = $val;
    }
    if ( ! isset( $SETTINGS['schema_version'] ) ) {
        $SETTINGS['schema_version'] = '0';
    }
} else {
    // Current schema uses a separate row for each setting.
    do {
        $SETTINGS[$row['name']] = $row['value'];
    } while ( $row = $db->fetch_array( $squery ) );
}
$db->free_result( $squery );
unset( $row );

if ( (int) $SETTINGS['postperpage'] < 5 ) $SETTINGS['postperpage'] = '30';
if ( (int) $SETTINGS['topicperpage'] < 5 ) $SETTINGS['topicperpage'] = '30';
if ( (int) $SETTINGS['memberperpage'] < 5 ) $SETTINGS['memberperpage'] = '30';
if ( (int) $SETTINGS['smcols'] < 1 ) $SETTINGS['smcols'] = '4';
if ( empty( $SETTINGS['onlinetodaycount'] ) || (int) $SETTINGS['onlinetodaycount'] < 5 ) {
    $SETTINGS['onlinetodaycount'] = '30';
}
if ( empty( $SETTINGS['captcha_code_length'] ) || (int) $SETTINGS['captcha_code_length'] < 3 || (int) $SETTINGS['captcha_code_length'] >= X_NONCE_KEY_LEN ) {
    $SETTINGS['captcha_code_length'] = '8';
}
if ( empty( $SETTINGS['ip_banning'] ) ) {
    $SETTINGS['ip_banning'] == 'off';
}
if ( empty( $SETTINGS['schema_version'] ) ) {
    $SETTINGS['schema_version'] == '0';
}
$inimax = phpShorthandValue('upload_max_filesize');
if ( empty( $SETTINGS['maxattachsize'] ) || $inimax < (int) $SETTINGS['maxattachsize'] ) {
    $SETTINGS['maxattachsize'] = $inimax;
}
unset($inimax);
define( 'X_SADMIN', true );


//Make it happen!
require('./upgrade.lib.php');
xmb_upgrade();
show_progress('Done');

//Cleanup Notes
//1. The website is still in maintenance mode.  The script sets xmb.settings (name='bbstatus', value='off')
//2. This script did not self-destruct and should not be available for public use on a live site.

/**
 * Output the upgrade progress at each step.
 *
 * This function is called by upgrade.lib.php with verbose status information.
 * You can change the output stream or suppress it completely.
 *
 * @param string $text Description of current progress.
 */
function show_progress($text) {
    echo $text, "...\n";
}

/**
 * Output a warning message to the user.
 *
 * @param string $text
 */
function show_warning($text) {
    echo $text, "\n";
}

/**
 * Output an error message to the user.
 *
 * @param string $text Description of current progress.
 */
function show_error($text) {
    echo $text, "\n";
}
