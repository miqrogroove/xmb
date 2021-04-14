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

header( 'X-Frame-Options: deny' );

// Script constants
define('MYSQL_MIN_VER', '4.1.7');
define('PHP_MIN_VER', '7.0.0');
define('X_SCRIPT', 'upgrade.php');
define('ROOT', '../');
define('LOG_FILE', './upgrade.log');

// Check configuration
if (ini_get('display_errors')) {
	ini_set('display_errors', '0');
	$forced_display_off = TRUE;
} else {
	$forced_display_off = FALSE;
}

// Check location
if (!(is_file(ROOT.'header.php') && is_dir(ROOT.'include'))) {
    echo 'Could not find XMB!<br />'
        .'Please make sure the upgrade folder is in the same folder as index.php and header.php.<br />';
    trigger_error('Attempted upgrade by '.$_SERVER['REMOTE_ADDR'].' from wrong location.', E_USER_ERROR);
}

// Authenticate Browser
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
    echo '<br /><br />This script may be run only by a Super Administrator.<br />'
        .'Please <a href="login.php">Log In</a> first to begin the upgrade successfully.<br />';
    trigger_error('Unauthenticated upgrade attempt by '.$_SERVER['REMOTE_ADDR'], E_USER_ERROR);
}

// Check Server Version
if (version_compare(phpversion(), PHP_MIN_VER, '<')) {
    echo '<br /><br />XMB requires PHP version '.PHP_MIN_VER.' or higher to work properly.  Version '.phpversion().' is running.';
    trigger_error('Admin attempted upgrade with obsolete PHP engine.', E_USER_ERROR);
}
if (version_compare($db->server_version(), MYSQL_MIN_VER, '<')) {
    echo '<br /><br />XMB requires MySQL version '.MYSQL_MIN_VER.' or higher to work properly.  Version '.$db->server_version().' is running.';
    trigger_error('Admin attempted upgrade with obsolete MySQL engine.', E_USER_ERROR);
}

// Initialize Verbose Logging
$result = file_put_contents( LOG_FILE, 'Initializing Upgrade Engine...' );
if ( false === $result ) {
	echo '<br /><br />Unable to write to file ' . LOG_FILE . '. Please check permissions for the XMB directory.';
	trigger_error( 'Unable to write to file ' . LOG_FILE, E_USER_ERROR );
}

// Ready to Upgrade
if ( !isset($_GET['step']) || '1' === $_GET['step'] ) {
?>
<h1>XMB 1.9.12 Upgrade Script</h1>

<p>This script is compatible with XMB versions 1.8 through 1.9.11, and XMB 1.9.12 Betas.

<p>This script is NOT compatible with older versions.

<h2>Instructions</h2>
<ol>
<li>Disable your forums using the Board Status setting.
<li>BACKUP YOUR DATABASE - This script cannot be undone!
<li>Confirm your forum database account is granted ALTER, CREATE, INDEX, and LOCK privileges.
<li>Copy your config.php settings into the new file.
<li>Upload the XMB 1.9.12 files.  Do not upload the install folder (delete it if necessary).
<li>Upload the upgrade directory to your board's root directory.
<li>Run this script by hitting the upgrade URL, for example:  https://www.example.com/forum/upgrade/
<li>When the upgrade finishes successfully, delete the upgrade directory.
<li>Enable your forums using the Board Status setting.
</ol>

<form method="get" onsubmit="this.submit1.disabled=true; return true;">
<input type="hidden" name="step" value="2" />
<p>When you are ready, <input type="submit" value="Click Here if you already have a backup and want to begin the upgrade" id="submit1" />.
</form>
<?php

} else if ( '2' === $_GET['step'] ) {

    ?>
    <h1>XMB 1.9.12 Upgrade Script</h1>
    <h2>Status Information</h2>
    <?php

    ?>
	<iframe src='status.php' width='100%' height='50%'></iframe>
	<iframe src='trigger.php' width='100%'></iframe>
    <?php

}

echo "\n</body></html>";

?>