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

ignore_user_abort( true );
header( 'Expires: 0' );
header( 'X-Frame-Options: sameorigin' );

//Script constants
define('MYSQL_MIN_VER', '4.1.7');
define('PHP_MIN_VER', '7.0.0');
define('X_SCRIPT', 'upgrade.php');
define('ROOT', '../');
define('LOG_FILE', './upgrade.log');

//Check configuration
if (ini_get('display_errors')) {
	ini_set('display_errors', '0');
	$forced_display_off = TRUE;
} else {
	$forced_display_off = FALSE;
}

//Check location
if (!(is_file(ROOT.'header.php') && is_dir(ROOT.'include'))) {
    show_error( 'Could not find XMB!<br />Please make sure the upgrade folder is in the same folder as index.php and header.php.' );
    trigger_error('Attempted upgrade by '.$_SERVER['REMOTE_ADDR'].' from wrong location.', E_USER_ERROR);
}

//Authenticate Browser
require(ROOT.'header.php');
echo "<html><head><title>XMB Upgrade Script</title><body>Database Connection Established<br />\n";
if (DEBUG) {
    echo 'Debug Mode Enabled.';
	if ($forced_display_off) {
		ini_set('display_errors', '1');
		trigger_error('Your PHP server has display_errors=On, which should never be used on production systems.', E_USER_WARNING);
	}
} else {
    echo 'Debug is False - You will not see any errors.';
}

if (!defined('X_SADMIN') || !X_SADMIN) {
    show_error( 'This script may be run only by a Super Administrator.<br />Please <a href="login.php" target="_parent">Log In</a> first to begin the upgrade successfully.' );
    trigger_error('Unauthenticated upgrade attempt by '.$_SERVER['REMOTE_ADDR'], E_USER_ERROR);
}

//Check Server Version
if (version_compare(phpversion(), PHP_MIN_VER, '<')) {
    show_error( 'XMB requires PHP version '.PHP_MIN_VER.' or higher to work properly.  Version '.phpversion().' is running.' );
    trigger_error('Admin attempted upgrade with obsolete PHP engine.', E_USER_ERROR);
}
if (version_compare($db->server_version(), MYSQL_MIN_VER, '<')) {
    show_error( '<br /><br />XMB requires MySQL version '.MYSQL_MIN_VER.' or higher to work properly.  Version '.$db->server_version().' is running.' );
    trigger_error('Admin attempted upgrade with obsolete MySQL engine.', E_USER_ERROR);
}


show_progress( 'Confirming the upgrade files are present' );

if (is_dir(ROOT.'install') || is_dir(ROOT.'Install')) {
	show_error( 'Wrong files present!<br />Please delete any folders named "install".' );
	trigger_error('Admin attempted upgrade while non-upgrade files were present.', E_USER_ERROR);
}
if (!is_file(ROOT.'templates.xmb')) {
	show_error( 'Files missing!<br />Please make sure to upload the templates.xmb file.' );
	trigger_error('Admin attempted upgrade with templates.xmb missing.', E_USER_ERROR);
}
if (!is_file(ROOT.'lang/English.lang.php')) {
	show_error( 'Files missing!<br />Please make sure to upload the lang/English.lang.php file.' );
	trigger_error('Admin attempted upgrade with English.lang.php missing.', E_USER_ERROR);
}
if (!is_file(ROOT.'include/schema.inc.php')) {
	show_error( 'Files missing!<br />Please make sure to upload the include/schema.inc.php file.' );
	trigger_error('Admin attempted upgrade with schema.lang.php missing.', E_USER_ERROR);
}
if (!is_file('./upgrade.lib.php')) {
	show_error( 'Files missing!<br />Please make sure to upload the upgrade/upgrade.lib.php file.' );
	trigger_error('Admin attempted upgrade with upgrade.lib.php missing.', E_USER_ERROR);
}

$trigger_old_schema = (int) $SETTINGS['schema_version'];

require('./upgrade.lib.php');
xmb_upgrade();

if ( $trigger_old_schema < 5 ) {
    show_finished( '<b>Done! :D</b><br />Now <a href="../misc.php?action=login" target="_parent">login and remember to turn your board back on</a>.<br />' );
} else {
    show_finished( '<b>Done! :D</b><br />Now <a href="../cp.php?action=settings#1" target="_parent">reset the Board Status setting to turn your board back on</a>.<br />' );
}
echo "\nDone.</body></html>";

/**
 * Output the upgrade progress at each step.
 *
 * This function is intended to be overridden by other upgrade scripts
 * that don't use this exact file, to support various output streams.
 *
 * @param string $text Description of current progress.
 */
function show_progress($text) {
	$result = file_put_contents( LOG_FILE, "\r\n$text...", FILE_APPEND );
	if ( false === $result ) {
		echo 'Unable to write to file ' . LOG_FILE;
		trigger_error( 'Unable to write to file ' . LOG_FILE, E_USER_ERROR );
	}
}

/**
 * Output a warning message to the user.
 *
 * @param string $text
 */
function show_warning($text) {
	$result = file_put_contents( LOG_FILE, "\r\n<b>$text</b>", FILE_APPEND );
	if ( false === $result ) {
		echo 'Unable to write to file ' . LOG_FILE;
		trigger_error( 'Unable to write to file ' . LOG_FILE, E_USER_ERROR );
	}
}

/**
 * Output an error message to the user.
 *
 * @param string $text Description of current progress.
 */
function show_error($text) {
    file_put_contents( LOG_FILE, "\r\n$text<!-- error -->", FILE_APPEND );
}

/**
 * Output final instructions to the user.
 *
 * @param string $text Description of current progress.
 */
function show_finished($text) {
    file_put_contents( LOG_FILE, "\r\n$text<!-- done. -->", FILE_APPEND );
}
?>