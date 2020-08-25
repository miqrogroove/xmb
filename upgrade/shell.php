<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2017, The XMB Group
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
require ROOT.'config.php';
if (DEBUG) {
    require(ROOT.'include/debug.inc.php');
} else {
    error_reporting(E_ERROR | E_PARSE | E_USER_ERROR);
}
define('X_PREFIX', $tablepre);
if ( 'mysql' === $database && extension_loaded( 'mysqli' ) ) $database = 'mysqli';
require ROOT.'db/'.$database.'.php';
require ROOT.'include/functions.inc.php';
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, TRUE);
$squery = $db->query("SELECT * FROM ".X_PREFIX."settings", (DEBUG && LOG_MYSQL_ERRORS));
if (FALSE === $squery) exit('Fatal Error: XMB is not installed.');
if ($db->num_rows($squery) == 0) exit('Fatal Error: The XMB settings table is empty.');
$SETTINGS = array();
foreach($db->fetch_array($squery) as $key => $val) $SETTINGS[$key] = $val;
$db->free_result($squery);
if ($SETTINGS['postperpage'] < 5) $SETTINGS['postperpage'] = 30;
if ($SETTINGS['topicperpage'] < 5) $SETTINGS['topicperpage'] = 30;

//Make it happen!
require('./upgrade.lib.php');
xmb_upgrade();
show_progress('Done');

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
?>
