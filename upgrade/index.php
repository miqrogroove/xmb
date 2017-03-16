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

ignore_user_abort(TRUE);

//Script constants
define('MYSQL_MIN_VER', '4.1.7');
define('PHP_MIN_VER', '4.3.0');
define('X_SCRIPT', 'upgrade.php');
define('ROOT', '../');

//Check configuration
if (ini_get('display_errors')) {
	ini_set('display_errors', '0');
	$forced_display_off = TRUE;
} else {
	$forced_display_off = FALSE;
}

//Check location
if (!(is_file(ROOT.'header.php') and is_dir(ROOT.'include'))) {
    echo 'Could not find XMB!<br />'
        .'Please make sure the upgrade folder is in the same folder as index.php and header.php.<br />';
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

if (!defined('X_SADMIN') or !X_SADMIN) {
    echo '<br /><br />This script may be run only by a Super Administrator.<br />'
        .'Please <a href="login.php">Log In</a> first to begin the upgrade successfully.<br />';
    trigger_error('Unauthenticated upgrade attempt by '.$_SERVER['REMOTE_ADDR'], E_USER_ERROR);
}

//Check Server Version
if (version_compare(phpversion(), PHP_MIN_VER, '<')) {
    echo '<br /><br />XMB requires PHP version '.PHP_MIN_VER.' or higher to work properly.  Version '.phpversion().' is running.';
    trigger_error('Admin attempted upgrade with obsolete PHP engine.', E_USER_ERROR);
}
if (version_compare($db->server_version(), MYSQL_MIN_VER, '<')) {
    echo '<br /><br />XMB requires MySQL version '.MYSQL_MIN_VER.' or higher to work properly.  Version '.$db->server_version().' is running.';
    trigger_error('Admin attempted upgrade with obsolete MySQL engine.', E_USER_ERROR);
}


if (!isset($_GET['step']) or $_GET['step'] == 1) {
?>
<h1>XMB 1.9.11 Upgrade Script</h1>

<p>This script is compatible with XMB versions 1.8 through 1.9.10, and XMB 1.9.11 Betas.

<p>This script is NOT compatible with older versions.

<h2>Instructions</h2>
<ol>
<li>Disable your forums using the Board Status setting.
<li>BACKUP YOUR DATABASE - This script cannot be undone!
<li>Confirm your forum database account is granted ALTER, CREATE, INDEX, and LOCK privileges.
<li>Copy your config.php settings into the new file.
<li>Upload the XMB 1.9.11 files.  Do not upload the install folder (delete it if necessary).
<li>Upload the upgrade directory to your board's root directory.
<li>Run this script by hitting the upgrade URL, for example:  http://www.example.com/forum/upgrade/
<li>When the upgrade finishes successfully, delete the upgrade directory.
<li>Enable your forums using the Board Status setting.
</ol>

<script type="text/javascript">
<!--//--><![CDATA[//><!--
function disableButton() {
    var newAttr = document.createAttribute("disabled");
    newAttr.nodeValue = "disabled";
    document.getElementById("submit1").setAttributeNode(newAttr);
    return true;
}
//--><!]]>
</script>

<form method="get" onsubmit="disableButton();">
<input type="hidden" name="step" value="2" />
<p>When you are ready, <input type="submit" value="Click Here if you already have a backup and want to begin the upgrade" id="submit1" />.
</form>
<?php

} else if ($_GET['step'] == 2) {

    ?>
    <h1>XMB 1.9.11 Upgrade Script</h1>
    <h2>Status Information</h2>
    <?php

    echo 'Confirming the upgrade files are present...<br />';
    if (is_dir(ROOT.'install') or is_dir(ROOT.'Install')) {
        echo 'Wrong files present!<br />'
            .'Please delete any folders named install or upgrade.<br />';
        trigger_error('Admin attempted upgrade while non-upgrade files were present.', E_USER_ERROR);
    }
    if (!is_file(ROOT.'templates.xmb')) {
        echo 'Files missing!<br />'
            .'Please make sure to upload the templates.xmb file.<br />';
        trigger_error('Admin attempted upgrade with templates.xmb missing.', E_USER_ERROR);
    }
    if (!is_file(ROOT.'lang/English.lang.php')) {
        echo 'Files missing!<br />'
            .'Please make sure to upload the lang/English.lang.php file.<br />';
        trigger_error('Admin attempted upgrade with English.lang.php missing.', E_USER_ERROR);
    }
    if (!is_file(ROOT.'include/schema.inc.php')) {
        echo 'Files missing!<br />'
            .'Please make sure to upload the include/schema.inc.php file.<br />';
        trigger_error('Admin attempted upgrade with schema.lang.php missing.', E_USER_ERROR);
    }
    if (!is_file('./upgrade.lib.php')) {
        echo 'Files missing!<br />'
            .'Please make sure to upload the upgrade/upgrade.lib.php file.<br />';
        trigger_error('Admin attempted upgrade with upgrade.lib.php missing.', E_USER_ERROR);
    }



    require('./upgrade.lib.php');
    xmb_upgrade();


    echo '<b>Done! :D</b><br />Now <a href="../cp.php?action=settings#1">reset the Board Status setting to turn your board back on</a>.<br />';
}

echo "\n</body></html>";

/**
 * Output the upgrade progress at each step.
 *
 * This function is intended to be overridden by other upgrade scripts
 * that don't use this exact file, to support various output streams.
 *
 * @param string $text Description of current progress.
 */
function show_progress($text) {
    echo $text, "...<br />\n";
}

/**
 * Output a warning message to the user.
 *
 * @param string $text
 */
function show_warning($text) {
    echo '<b>', $text, "</b><br />\n";
}

/**
 * Output an error message to the user.
 *
 * @param string $text Description of current progress.
 */
function show_error($text) {
    echo $text, "<br />\n";
}
?>
