<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2010, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * http://www.ientry.com
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
require('header.php');
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
        .'Please <a href="misc.php?action=login">Log In</a> first to begin the upgrade successfully.<br />';
    trigger_error('Unauthenticated upgrade attempt by '.$_SERVER['REMOTE_ADDR'], E_USER_ERROR);
}

//Check Server Version
$current = array_map('intval', explode('.', phpversion()));
$min = array_map('intval', explode('.', PHP_MIN_VER));
if ($current[0] < $min[0] || ($current[0] == $min[0] && ($current[1] < $min[1] || ($current[1] == $min[1] && $current[2] < $min[2])))) {
    echo '<br /><br />XMB requires PHP version '.PHP_MIN_VER.' or higher to work properly.  Version '.phpversion().' is running.';
    trigger_error('Admin attempted upgrade with obsolete PHP engine.', E_USER_ERROR);
}
$sqlver = mysql_get_server_info($db->link);
$current = array_map('intval', explode('.', $sqlver));
$min = array_map('intval', explode('.', MYSQL_MIN_VER));
if ($current[0] < $min[0] || ($current[0] == $min[0] && ($current[1] < $min[1] || ($current[1] == $min[1] && $current[2] < $min[2])))) {
    echo '<br /><br />XMB requires MySQL version '.MYSQL_MIN_VER.' or higher to work properly.  Version '.$sqlver.' is running.';
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

    echo 'Confirming forums are turned off...<br />';
    if ($SETTINGS['bbstatus'] != 'off') {
        $db->query("UPDATE ".X_PREFIX."settings SET bbstatus = 'off'");
        echo '<b>Your forums were turned off by the upgrader to prevent damage.<br />'
            .'They will remain unavailable to your members until you reset the Board Status setting in the Admin Panel.</b><br />';
        trigger_error('Admin attempted upgrade without turning off the board.  Board now turned off.', E_USER_WARNING);
    }

    echo 'Determining the database schema version...<br />';
    require(ROOT.'include/schema.inc.php');
    require('./upgrade.lib.php');
    if (!isset($SETTINGS['schema_version'])) {
        $SETTINGS['schema_version'] = 0;
    }
    switch ($SETTINGS['schema_version']) {
    case XMB_SCHEMA_VER:
        echo 'Database schema is current, skipping ALTER commands...<br />';
        break;
    case 0:
        //Ambiguous case.  Attempt a backward-compatible schema change.
        upgrade_schema_to_v0();
        //No breaks.
    case 1:
        upgrade_schema_to_v2();
    case 2:
        upgrade_schema_to_v3();
    case 3:
        upgrade_schema_to_v4();
    case 4:
        //Future use. Break only before case default.
        break;
    default:
        echo 'Unrecognized Database!<br />'
            .'This upgrade utility is not compatible with your version of XMB.  Upgrade halted to prevent damage.<br />';
        trigger_error('Admin attempted upgrade with obsolete upgrade utility.', E_USER_ERROR);
        break;
    }
    echo 'Database schema is now current...<br />';

    echo 'Initializing the new translation system...<br />';
    require_once(ROOT.'include/translation.inc.php');
    $upload = file_get_contents('lang/English.lang.php');

    echo 'Installing English.lang.php...<br />';
    installNewTranslation($upload);
    unset($upload);

    echo 'Opening the templates file...<br />';
    $templates = explode("|#*XMB TEMPLATE FILE*#|", file_get_contents(ROOT.'templates.xmb'));

    echo 'Resetting the templates table...<br />';
    $db->query('TRUNCATE TABLE '.X_PREFIX.'templates');

    echo 'Requesting to lock the templates table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."templates WRITE");

    echo 'Saving the new templates...<br />';
    $values = array();
    foreach($templates as $val) {
        $template = explode("|#*XMB TEMPLATE*#|", $val);
        if (isset($template[1])) {
            $template[1] = addslashes(ltrim($template[1]));
        } else {
            $template[1] = '';
        }
        $values[] = "('".$db->escape_var($template[0])."', '".$db->escape_var($template[1])."')";
    }
    unset($templates);
    if (count($values) > 0) {
        $values = implode(', ', $values);
        $db->query("INSERT INTO `".X_PREFIX."templates` (`name`, `template`) VALUES $values");
    }
    unset($values);
    $db->query("DELETE FROM `".X_PREFIX."templates` WHERE name=''");

    echo 'Releasing the lock on the templates table...<br />';
    $db->query('UNLOCK TABLES');

    echo 'Deleting the templates.xmb file...<br />';
    unlink(ROOT.'templates.xmb');


    echo 'Checking for new themes...';
    $query = $db->query("SELECT themeid FROM ".X_PREFIX."themes WHERE name='XMB Davis'");
    if ($db->num_rows($query) == 0 and is_dir(ROOT.'images/davis')) {
        echo 'Adding Davis as the new default theme...<br />';
        $db->query("INSERT INTO ".X_PREFIX."themes (`name`,      `bgcolor`, `altbg1`,  `altbg2`,  `link`,    `bordercolor`, `header`,  `headertext`, `top`,       `catcolor`,   `tabletext`, `text`,    `borderwidth`, `tablewidth`, `tablespace`, `font`,                              `fontsize`, `boardimg`, `imgdir`,       `smdir`,          `cattext`) "
                                          ."VALUES ('XMB Davis', 'bg.gif',  '#FFFFFF', '#f4f7f8', '#24404b', '#86a9b6',     '#d3dfe4', '#24404b',    'topbg.gif', 'catbar.gif', '#000000',   '#000000', '1px',         '97%',        '5px',        'Tahoma, Arial, Helvetica, Verdana', '11px',     'logo.gif', 'images/davis', 'images/smilies', '#163c4b');");
        $newTheme = $db->insert_id();
        $db->query("UPDATE ".X_PREFIX."settings SET theme=$newTheme");
    }
    $db->free_result($query);

    echo 'Deleting the upgrade files...<br />';
    rmFromDir('upgrade');

    echo '<b>Done! :D</b><br />Now <a href="cp.php?action=settings#1">reset the Board Status setting to turn your board back on</a>.<br />';
}

echo "\n</body></html>";

?>