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
define('XMB_SCHEMA_VER', 3);

//Check configuration
if (ini_get('display_errors')) {
	ini_set('display_errors', '0');
	$forced_display_off = TRUE;
} else {
	$forced_display_off = FALSE;
}

//Check location
if (!(is_file('header.php') and is_dir('include'))) {
    echo 'Could not find XMB!<br />'
        .'Please make sure the upgrade.php file is in the same folder as index.php and header.php.<br />';
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
<li>BACKUP YOUR DATABASE - This script cannot be undone!
<li>Copy your config.php settings into the new file.
<li>Confirm your forum database account is granted ALTER, CREATE, and LOCK privileges.
<li>Disable your forums using the Board Status setting.
<li>Upload the XMB 1.9.11 files.
<li>Upload and run this script to complete your database upgrade.
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
    if (is_dir('upgrade') Or is_dir('Upgrade') Or is_dir('install') Or is_dir('Install')) {
        echo 'Wrong files present!<br />'
            .'Please delete any folders named install or upgrade.<br />';
        trigger_error('Admin attempted upgrade while non-upgrade files were present.', E_USER_ERROR);
    }
    if (!is_file('templates.xmb')) {
        echo 'Files missing!<br />'
            .'Please make sure to upload the templates.xmb file.<br />';
        trigger_error('Admin attempted upgrade with templates.xmb missing.', E_USER_ERROR);
    }
    if (!is_file('lang/English.lang.php')) {
        echo 'Files missing!<br />'
            .'Please make sure to upload the lang/English.lang.php file.<br />';
        trigger_error('Admin attempted upgrade with English.lang.php missing.', E_USER_ERROR);
    }

    echo 'Confirming forums are turned off...<br />';
    if ($SETTINGS['bbstatus'] != 'off') {
        $db->query("UPDATE ".X_PREFIX."settings SET bbstatus = 'off'");
        echo '<b>Your forums were turned off by the upgrader to prevent damage.<br />'
            .'They will remain unavailable to your members until you reset the Board Status setting in the Admin Panel.</b><br />';
        trigger_error('Admin attempted upgrade without turning off the board.  Board now turned off.', E_USER_WARNING);
    }

    echo 'Determining the database schema version...<br />';
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
    require_once('include/translation.inc.php');
    $upload = file_get_contents('lang/English.lang.php');

    echo 'Installing English.lang.php...<br />';
    installNewTranslation($upload);
    unset($upload);

    echo 'Opening the templates file...<br />';
    $templates = explode("|#*XMB TEMPLATE FILE*#|", file_get_contents('templates.xmb'));

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
    unlink('templates.xmb');


    echo 'Checking for new themes...';
    $query = $db->query("SELECT themeid FROM ".X_PREFIX."themes WHERE name='XMB Davis'");
    if ($db->num_rows($query) == 0 And is_dir('images/davis')) {
        echo 'Adding Davis as the new default theme...<br />';
        $db->query("INSERT INTO ".X_PREFIX."themes (`name`,      `bgcolor`, `altbg1`,  `altbg2`,  `link`,    `bordercolor`, `header`,  `headertext`, `top`,       `catcolor`,   `tabletext`, `text`,    `borderwidth`, `tablewidth`, `tablespace`, `font`,                              `fontsize`, `boardimg`, `imgdir`,       `smdir`,          `cattext`) "
                                          ."VALUES ('XMB Davis', 'bg.gif',  '#FFFFFF', '#f4f7f8', '#24404b', '#86a9b6',     '#d3dfe4', '#24404b',    'topbg.gif', 'catbar.gif', '#000000',   '#000000', '1px',         '97%',        '5px',        'Tahoma, Arial, Helvetica, Verdana', '11px',     'logo.gif', 'images/davis', 'images/smilies', '#163c4b');");
        $newTheme = $db->insert_id();
        $db->query("UPDATE ".X_PREFIX."settings SET theme=$newTheme");
    }
    $db->free_result($query);

    echo 'Deleting the upgrade.php file...<br />';
    unlink('upgrade.php');

    echo '<b>Done! :D</b><br />Now <a href="cp.php?action=settings#1">reset the Board Status setting to turn your board back on</a>.<br />';
}

echo "\n</body></html>";

/**
 * Performs all tasks needed to upgrade the schema to version 1.9.9.
 *
 * This function is officially compatible with the following XMB versions
 * that did not have a schema_version number:
 * 1.8 SP2, 1.9.1, 1.9.2, 1.9.3, 1.9.4, 1.9.5, 1.9.5 SP1, 1.9.6 RC1, 1.9.6 RC2,
 * 1.9.7 RC3, 1.9.7 RC4, 1.9.8, 1.9.8 SP1, 1.9.8 SP2, 1.9.8 SP3, 1.9.9, 1.9.10.
 *
 * Some tables (such as xmb_logs) will be upgraded directly to schema_version 3 for simplicity.
 *
 * @author Robert Chapin (miqrogroove)
 * @since 1.9.11 (Patch #11)
 */
function upgrade_schema_to_v0() {
    global $db, $SETTINGS;

    echo 'Beginning schema upgrade from legacy version...<br />';

    echo 'Requesting to lock the banned table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."banned WRITE");

    echo 'Gathering schema information from the banned table...<br />';
    $sql = array();
    $table = 'banned';
    $colname = 'id';
    $coltype = "smallint(6) NOT NULL AUTO_INCREMENT";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Extra']) != 'auto_increment') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }
    $db->free_result($query);

    $columns = array(
    'dateline' => "int(10) NOT NULL default 0");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'bigint(30)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'ip1',
    'ip2',
    'ip3',
    'ip4');
    foreach($columns as $colname) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Column_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX ($colname)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Modifying columns in the banned table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the buddys table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."buddys WRITE");

    echo 'Gathering schema information from the buddys table...<br />';
    $sql = array();
    $table = 'buddys';
    $columns = array(
    'username' => "varchar(32) NOT NULL default ''",
    'buddyname' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(40)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'username' => 'username (8)');
    foreach($columns as $colname => $coltype) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Column_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        } else {
            $row = $db->fetch_array($query);
            if ($row['Sub_part'] != '8') {
                $sql[] = "DROP INDEX $colname";
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Modifying columns in the buddys table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the favorites table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."favorites WRITE");

    echo 'Gathering schema information from the favorites table...<br />';
    $sql = array();
    $table = 'favorites';
    $columns = array(
    'tid' => "int(10) NOT NULL default 0");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'smallint(6)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'username' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(40)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'type' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(20)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'tid');
    foreach($columns as $colname) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Column_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX ($colname)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Modifying columns in the favorites table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the themes table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."themes WRITE");

    echo 'Gathering schema information from the themes table...<br />';
    $sql = array();
    $table = 'themes';
    $colname = 'themeid';
    $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Key_name = 'PRIMARY'");
    if ($db->num_rows($query) == 1) {
        $row = $db->fetch_array($query);
        if ($row['Column_name'] != $colname) {
            $sql[] = "DROP PRIMARY KEY";
        }
    }
    $db->free_result($query);

    $columns = array(
    'themeid' => "smallint(3) NOT NULL auto_increment");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'name' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(30)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'boardimg' => "varchar(128) default NULL");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(50)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'dummy');
    foreach($columns as $colname) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'DROP COLUMN '.$colname;
        }
        $db->free_result($query);
    }

    $colname = 'themeid';
    $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Key_name = 'PRIMARY' AND Column_name = '$colname'");
    if ($db->num_rows($query) == 0) {
        $sql[] = "ADD PRIMARY KEY ($colname)";
    }
    $db->free_result($query);

    $columns = array(
    'name');
    foreach($columns as $colname) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Key_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX ($colname)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Modifying columns in the themes table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the forums table...<br />';
    $db->query('LOCK TABLES '.
        X_PREFIX.'forums WRITE, '.
        X_PREFIX.'themes READ');

    $upgrade_permissions = TRUE;

    echo 'Gathering schema information from the forums table...<br />';
    $sql = array();
    $table = 'forums';
    $columns = array(
    'private',
    'pollstatus',
    'guestposting');
    foreach($columns as $colname) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'DROP COLUMN '.$colname;
        } else {
            $upgrade_permissions = FALSE;
        }
        $db->free_result($query);
    }

    if ($upgrade_permissions) {

        // Verify new schema is not coexisting with the old one.  Results would be unpredictable.
        $colname = 'postperm';
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(11)') {
            echo 'Unexpected schema in forums table.  Upgrade aborted to prevent damage.';
            trigger_error('Attempted upgrade on inconsistent schema aborted automatically.', E_USER_ERROR);
        }

        echo 'Making room for the new values in the postperm column...<br />';
        $db->query('ALTER TABLE '.X_PREFIX."forums MODIFY COLUMN postperm VARCHAR(11) NOT NULL DEFAULT '0,0,0,0'");

        echo 'Restructuring the forum permissions data...<br />';
        fixPostPerm();   // 1.8 => 1.9.1
        fixForumPerms(); // 1.9.1 => 1.9.9

    } else {

        // Verify new schema is not missing.  Results would be unpredictable.
        $colname = 'postperm';
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) != 'varchar(11)') {
            echo 'Unexpected schema in forums table.  Upgrade aborted to prevent damage.';
            trigger_error('Attempted upgrade on inconsistent schema aborted automatically.', E_USER_ERROR);
        }
    }
    
    $columns = array(
    'mt_status',
    'mt_open',
    'mt_close');
    foreach($columns as $colname) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'DROP COLUMN '.$colname;
        }
        $db->free_result($query);
    }
    
    $columns = array(
    'lastpost' => "varchar(54) NOT NULL default ''",
    'password' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(30)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'theme' => "smallint(3) NOT NULL default 0");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(30)') {
            // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
            $sql2 = "UPDATE ".X_PREFIX."$table "
                  . "LEFT JOIN ".X_PREFIX."themes ON ".X_PREFIX."$table.$colname = ".X_PREFIX."themes.name "
                  . "SET ".X_PREFIX."$table.$colname = IFNULL(".X_PREFIX."themes.themeid, 0)";
            $db->query($sql2);

            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $colname = 'name';
    $coltype = "varchar(128) NOT NULL default ''";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(50)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $columns = array(
    'posts' => "int(10) NOT NULL default 0",
    'threads' => "int(10) NOT NULL default 0");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'int(100)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'fup',
    'type',
    'displayorder',
    'status');
    foreach($columns as $colname) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Column_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX ($colname)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Deleting/Modifying columns in the forums table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the settings table...<br />';
    $db->query('LOCK TABLES '.
        X_PREFIX.'settings WRITE, '.
        X_PREFIX.'themes READ');

    echo 'Gathering schema information from the settings table...<br />';
    $sql = array();
    $table = 'settings';
    $columns = array(
    'files_status',
    'files_foldername',
    'files_screenshot',
    'files_shotsize',
    'files_guests',
    'files_cpp',
    'files_mouseover',
    'files_fpp',
    'files_report',
    'files_jumpbox',
    'files_search',
    'files_spp',
    'files_searchcolor',
    'files_stats',
    'files_notify',
    'files_content_types',
    'files_comment_report',
    'files_navigation',
    'files_faq',
    'files_paypal_account');
    foreach($columns as $colname) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'DROP COLUMN '.$colname;
        }
        $db->free_result($query);
    }
    $columns = array(
    'addtime' => "DECIMAL(4,2) NOT NULL default 0",
    'max_avatar_size' => "varchar(9) NOT NULL default '100x100'",
    'footer_options' => "varchar(45) NOT NULL default 'queries-phpsql-loadtimes-totaltime'",
    'space_cats' => "char(3) NOT NULL default 'no'",
    'spellcheck' => "char(3) NOT NULL default 'off'",
    'allowrankedit' => "char(3) NOT NULL default 'on'",
    'notifyonreg' => "SET('off','u2u','email') NOT NULL default 'off'",
    'subject_in_title' => "char(3) NOT NULL default ''",
    'def_tz' => "decimal(4,2) NOT NULL default '0.00'",
    'indexshowbar' => "tinyint(2) NOT NULL default 2",
    'resetsigs' => "char(3) NOT NULL default 'off'",
    'pruneusers' => "smallint(3) NOT NULL default 0",
    'ipreg' => "char(3) NOT NULL default 'on'",
    'maxdayreg' => "smallint(5) UNSIGNED NOT NULL default 25",
    'maxattachsize' => "int(10) UNSIGNED NOT NULL default 256000",
    'captcha_status' => "set('on','off') NOT NULL default 'on'",
    'captcha_reg_status' => "set('on','off') NOT NULL default 'on'",
    'captcha_post_status' => "set('on','off') NOT NULL default 'on'",
    'captcha_code_charset' => "varchar(128) NOT NULL default 'A-Z'",
    'captcha_code_length' => "int(2) NOT NULL default '8'",
    'captcha_code_casesensitive' => "set('on','off') NOT NULL default 'off'",
    'captcha_code_shadow' => "set('on','off') NOT NULL default 'off'",
    'captcha_image_type' => "varchar(4) NOT NULL default 'png'",
    'captcha_image_width' => "int(3) NOT NULL default '250'",
    'captcha_image_height' => "int(3) NOT NULL default '50'",
    'captcha_image_bg' => "varchar(128) NOT NULL default ''",
    'captcha_image_dots' => "int(3) NOT NULL default '0'",
    'captcha_image_lines' => "int(2) NOT NULL default '70'",
    'captcha_image_fonts' => "varchar(128) NOT NULL default ''",
    'captcha_image_minfont' => "int(2) NOT NULL default '16'",
    'captcha_image_maxfont' => "int(2) NOT NULL default '25'",
    'captcha_image_color' => "set('on','off') NOT NULL default 'off'",
    'showsubforums' => "set('on','off') NOT NULL default 'off'",
    'regoptional' => "set('on','off') NOT NULL default 'off'",
    'quickreply_status' => "set('on','off') NOT NULL default 'on'",
    'quickjump_status' => "set('on','off') NOT NULL default 'on'",
    'index_stats' => "set('on','off') NOT NULL default 'on'",
    'onlinetodaycount' => "smallint(5) NOT NULL default '50'",
    'onlinetoday_status' => "set('on','off') NOT NULL default 'on'");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }
    
    $colname = 'adminemail';
    $coltype = "varchar(60) NOT NULL default 'webmaster@domain.ext'";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(32)' or strtolower($row['Type']) == 'varchar(50)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $columns = array(
    'langfile' => "varchar(34) NOT NULL default 'English'",
    'bbname' => "varchar(32) NOT NULL default 'Your Forums'");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(50)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'theme' => "smallint(3) NOT NULL default 1");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(30)') {
            // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
            $sql2 = "UPDATE ".X_PREFIX."$table "
                  . "LEFT JOIN ".X_PREFIX."themes ON ".X_PREFIX."$table.$colname = ".X_PREFIX."themes.name "
                  . "SET ".X_PREFIX."$table.$colname = IFNULL(".X_PREFIX."themes.themeid, 1)";
            $db->query($sql2);

            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'dateformat' => "varchar(10) NOT NULL default 'dd-mm-yyyy'");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(20)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'tickerdelay' => "int(6) NOT NULL default 4000");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'char(10)') {
            // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
            $db->query("UPDATE ".X_PREFIX."$table SET $colname = '4000' WHERE $colname = '' OR $colname IS NULL");
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'todaysposts' => "char(3) NOT NULL default 'on'",
    'stats' => "char(3) NOT NULL default 'on'",
    'authorstatus' => "char(3) NOT NULL default 'on'",
    'tickercontents' => "text NOT NULL");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Null']) == 'yes') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Adding/Deleting/Modifying columns in the settings table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the members table...<br />';
    $db->query('LOCK TABLES '.
        X_PREFIX.'members WRITE, '.
        X_PREFIX.'themes READ');

    echo 'Fixing birthday values...<br />';
    fixBirthdays();

    echo 'Gathering schema information from the members table...<br />';
    $sql = array();
    $table = 'members';
    $columns = array(
    'webcam');
    foreach($columns as $colname) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'DROP COLUMN '.$colname;
        }
        $db->free_result($query);
    }

    $colname = 'uid';
    $coltype = "int(12) NOT NULL auto_increment";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'smallint(6)' or strtolower($row['Type']) == 'int(6)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'username';
    $coltype = "varchar(32) NOT NULL default ''";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(25)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'password';
    $coltype = "varchar(32) NOT NULL default ''";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(40)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'regdate';
    $coltype = "int(10) NOT NULL default 0";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'bigint(30)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'postnum';
    $coltype = "MEDIUMINT NOT NULL DEFAULT 0";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'int(10)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'timeoffset';
    $coltype = "DECIMAL(4,2) NOT NULL default 0";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'int(5)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'avatar';
    $coltype = "varchar(120) default NULL";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(90)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'theme';
    $coltype = "smallint(3) NOT NULL default 0";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(30)') {
        // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
        $sql2 = "UPDATE ".X_PREFIX."$table "
              . "LEFT JOIN ".X_PREFIX."themes ON ".X_PREFIX."$table.$colname = ".X_PREFIX."themes.name "
              . "SET ".X_PREFIX."$table.$colname = IFNULL(".X_PREFIX."themes.themeid, 0)";
        $db->query($sql2);

        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'regip';
    $coltype = "varchar(15) NOT NULL default ''";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(40)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'lastvisit';
    $coltype = "bigint(15) NOT NULL default 0";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(30)' or strtolower($row['Type']) == 'bigint(30)' or strtolower($row['Null']) == 'yes') {
        // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
        $db->query("UPDATE ".X_PREFIX."$table SET $colname = '0' WHERE $colname = '' OR $colname IS NULL");
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'mood';
    $coltype = "varchar(128) NOT NULL default 'Not Set'";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(15)' or strtolower($row['Type']) == 'varchar(32)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'pwdate';
    $coltype = "int(10) NOT NULL default 0";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'bigint(30)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $columns = array(
    'email' => "varchar(60) NOT NULL default ''",
    'site' => "varchar(75) NOT NULL default ''",
    'aim' => "varchar(40) NOT NULL default ''",
    'location' => "varchar(50) NOT NULL default ''",
    'bio' => "text NOT NULL",
    'ignoreu2u' => "text NOT NULL");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Null']) == 'yes') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }
    $columns = array(
    'bday' => "varchar(10) NOT NULL default '0000-00-00'");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Null']) == 'yes' or strtolower($row['Type']) == 'varchar(50)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'invisible' => "SET('1','0') default 0",
    'u2ufolders' => "text NOT NULL",
    'saveogu2u' => "char(3) NOT NULL default ''",
    'emailonu2u' => "char(3) NOT NULL default ''",
    'useoldu2u' => "char(3) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'username' => 'username (8)');
    foreach($columns as $colname => $coltype) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Key_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        } else {
            $row = $db->fetch_array($query);
            if ($row['Sub_part'] != '8') {
                $sql[] = "DROP INDEX $colname";
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }
        $db->free_result($query);
    }

    $columns = array(
    'status',
    'postnum',
    'password',
    'email',
    'regdate',
    'invisible');
    foreach($columns as $colname) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Column_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX ($colname)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Deleting/Adding/Modifying columns in the members table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    // Mimic old function fixPPP()
    echo 'Fixing missing posts per page values...<br />';
    $db->query("UPDATE ".X_PREFIX."members SET ppp={$SETTINGS['postperpage']} WHERE ppp=0");
    $db->query("UPDATE ".X_PREFIX."members SET tpp={$SETTINGS['topicperpage']} WHERE tpp=0");

    echo 'Updating outgoing U2U status...<br />';
	$db->query("UPDATE ".X_PREFIX."members SET saveogu2u='yes'");

    echo 'Releasing the lock on the members table...<br />';
    $db->query('UNLOCK TABLES');

    echo 'Adding new tables for polls...<br />';
    $db->query("CREATE TABLE IF NOT EXISTS ".X_PREFIX."vote_desc (
        `vote_id` mediumint(8) unsigned NOT NULL auto_increment,
        `topic_id` INT UNSIGNED NOT NULL,
        `vote_text` text NOT NULL,
        `vote_start` int(11) NOT NULL default '0',
        `vote_length` int(11) NOT NULL default '0',
        PRIMARY KEY  (`vote_id`),
        KEY `topic_id` (`topic_id`)
      ) ENGINE=MyISAM");
    $db->query("CREATE TABLE IF NOT EXISTS ".X_PREFIX."vote_results (
        `vote_id` mediumint(8) unsigned NOT NULL default '0',
        `vote_option_id` tinyint(4) unsigned NOT NULL default '0',
        `vote_option_text` varchar(255) NOT NULL default '',
        `vote_result` int(11) NOT NULL default '0',
        KEY `vote_option_id` (`vote_option_id`),
        KEY `vote_id` (`vote_id`)
      ) ENGINE=MyISAM");
    $db->query("CREATE TABLE IF NOT EXISTS ".X_PREFIX."vote_voters (
        `vote_id` mediumint(8) unsigned NOT NULL default '0',
        `vote_user_id` mediumint(8) NOT NULL default '0',
        `vote_user_ip` char(8) NOT NULL default '',
        KEY `vote_id` (`vote_id`),
        KEY `vote_user_id` (`vote_user_id`),
        KEY `vote_user_ip` (`vote_user_ip`)
      ) ENGINE=MyISAM");

    echo 'Requesting to lock the polls tables...<br />';
    $db->query('LOCK TABLES '.
        X_PREFIX.'threads WRITE, '.
        X_PREFIX.'vote_desc WRITE, '.
        X_PREFIX.'vote_results WRITE, '.
        X_PREFIX.'vote_voters WRITE, '.
        X_PREFIX.'members READ');

    echo 'Upgrading polls to new system...<br />';
    fixPolls();

    echo 'Gathering schema information from the threads table...<br />';
    $sql = array();
    $table = 'threads';
    $colname = 'subject';
    $coltype = "varchar(128) NOT NULL default ''";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(100)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'views';
    $coltype = "bigint(32) NOT NULL default 0";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'smallint(4)' or strtolower($row['Type']) == 'int(100)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'replies';
    $coltype = "int(10) NOT NULL default 0";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'smallint(5)' or strtolower($row['Type']) == 'int(100)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }
    
    $colname = 'lastpost';
    $coltype = "varchar(54) NOT NULL default ''";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(32)' or strtolower($row['Type']) == 'varchar(30)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'pollopts';
    $coltype = "tinyint(1) NOT NULL default 0";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'text') {
        // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
        $db->query("UPDATE ".X_PREFIX."$table SET $colname = '0' WHERE $colname = '' OR $colname IS NULL");
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'author';
    $coltype = "varchar(32) NOT NULL default ''";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(40)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'topped';
    $coltype = "tinyint(1) NOT NULL default 0";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'smallint(6)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'tid';
    $query = $db->query("SHOW INDEX FROM ".X_PREFIX."$table WHERE Key_name = '$colname' AND Column_name = '$colname'");
    if ($db->num_rows($query) > 0) {
        $sql[] = 'DROP INDEX '.$colname;
    }

    $columns = array(
    'author' => "author (8)");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Key_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        } else {
            $row = $db->fetch_array($query);
            if ($row['Sub_part'] != '8') {
                $sql[] = "DROP INDEX $colname";
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }
        $db->free_result($query);
    }

    $columns = array(
    'lastpost' => "lastpost",
    'closed' => "closed",
    'forum_optimize' => "fid, topped, lastpost");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Key_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Modifying columns in the threads table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the attachments table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."attachments WRITE");

    echo 'Gathering schema information from the attachments table...<br />';
    $sql = array();
    $table = 'attachments';
    $columns = array(
    'aid' => "int(10) NOT NULL auto_increment",
    'pid' => "int(10) NOT NULL default 0",
    'downloads' => "int(10) NOT NULL default 0");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'smallint(6)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }
    $columns = array(
    'pid');
    foreach($columns as $colname) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Column_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX ($colname)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Modifying columns in the attachments table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the posts table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."posts WRITE");

    echo 'Gathering schema information from the posts table...<br />';
    $sql = array();
    $table = 'posts';
    $columns = array(
    'tid' => "int(10) NOT NULL default '0'");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'smallint(6)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'author' => "varchar(32) NOT NULL default ''",
    'useip' => "varchar(15) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(40)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $colname = 'subject';
    $coltype = "tinytext NOT NULL";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(100)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'dateline';
    $coltype = "int(10) NOT NULL default 0";
    $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'bigint(30)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $columns = array(
    'author' => "author (8)");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Key_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        } else {
            $row = $db->fetch_array($query);
            if ($row['Sub_part'] != '8') {
                $sql[] = "DROP INDEX $colname";
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }
        $db->free_result($query);
    }

    $columns = array(
    'fid' => "fid",
    'dateline' => "dateline",
    'thread_optimize' => "tid, dateline, pid");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Key_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Modifying columns in the posts table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the ranks table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."ranks WRITE");

    echo 'Gathering schema information from the ranks table...<br />';
    $sql = array();
    $table = 'ranks';
    $columns = array(
    'title' => "varchar(100) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(40)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'posts' => "MEDIUMINT DEFAULT 0",
    'id' => "smallint(5) NOT NULL auto_increment");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'smallint(6)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'title');
    foreach($columns as $colname) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Column_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX ($colname)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Modifying columns in the ranks table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Fixing special ranks...<br />';
    $db->query("DELETE FROM ".X_PREFIX."ranks WHERE title IN ('Moderator', 'Super Moderator', 'Administrator', 'Super Administrator')");
    $db->query("INSERT INTO ".X_PREFIX."ranks
     (title,                 posts, stars, allowavatars, avatarrank) VALUES
     ('Moderator',           -1,    6,     'yes',  ''),
     ('Super Moderator',     -1,    7,     'yes',  ''),
     ('Administrator',       -1,    8,     'yes',  ''),
     ('Super Administrator', -1,    9,     'yes',  '')"
    );

    echo 'Requesting to lock the templates table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."templates WRITE");

    echo 'Gathering schema information from the templates table...<br />';
    $sql = array();
    $table = 'templates';
    $columns = array(
    'name' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(40)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'name');
    foreach($columns as $colname) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Column_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX ($colname)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Modifying columns in the templates table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the u2u table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."u2u WRITE");

    $upgrade_u2u = FALSE;

    echo 'Gathering schema information from the u2u table...<br />';
    $sql = array();
    $table = 'u2u';
    $columns = array(
    'u2uid' => "bigint(10) NOT NULL auto_increment");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'smallint(6)' or strtolower($row['Type']) == 'int(6)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'msgto' => "varchar(32) NOT NULL default ''",
    'msgfrom' => "varchar(32) NOT NULL default ''",
    'folder' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(40)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'dateline' => "int(10) NOT NULL default 0");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'bigint(30)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'subject' => "varchar(64) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(75)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'type' => "set('incoming','outgoing','draft') NOT NULL default ''",
    'owner' => "varchar(32) NOT NULL default ''",
    'sentstatus' => "set('yes','no') NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
            $upgrade_u2u = TRUE;
        }
        $db->free_result($query);
    }

    if ($upgrade_u2u) {
        // Commit changes so far.
        if (count($sql) > 0) {
            echo 'Modifying columns in the u2u table...<br />';
            $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
            $db->query($sql);
        }

        $sql = array();

        // Mimic old function upgradeU2U() but with fewer queries
        echo 'Upgrading U2Us...<br />';
        $db->query("UPDATE ".X_PREFIX."$table SET type='incoming', owner=msgto WHERE folder='inbox'");
        $db->query("UPDATE ".X_PREFIX."$table SET type='outgoing', owner=msgfrom WHERE folder='outbox'");
        $db->query("UPDATE ".X_PREFIX."$table SET type='incoming', owner=msgfrom WHERE folder != 'outbox' AND folder != 'inbox'");
        $db->query("UPDATE ".X_PREFIX."$table SET readstatus='no' WHERE readstatus=''");

        $colname = 'new';
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $db->query("UPDATE ".X_PREFIX."$table SET sentstatus='yes' WHERE new=''");
        }
    }

    $columns = array(
    'readstatus' => "set('yes','no') NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(3)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'new');
    foreach($columns as $colname) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'DROP COLUMN '.$colname;
        }
        $db->free_result($query);
    }

    $columns = array(
    'msgto' => "msgto (8)",
    'msgfrom' => "msgfrom (8)");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Column_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        } else {
            $row = $db->fetch_array($query);
            if ($row['Sub_part'] != '8') {
                $sql[] = "DROP INDEX $colname";
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }
        $db->free_result($query);
    }

    $columns = array(
    'folder' => "folder (8)",
    'readstatus' => "readstatus",
    'owner' => "owner (8)");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Column_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Modifying columns in the u2u table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the words table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."words WRITE");

    echo 'Gathering schema information from the words table...<br />';
    $sql = array();
    $table = 'words';
    $columns = array(
    'find');
    foreach($columns as $colname) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Column_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX ($colname)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Adding indexes in the words table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the restricted table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."restricted WRITE");

    echo 'Gathering schema information from the restricted table...<br />';
    $sql = array();
    $table = 'restricted';
    $columns = array(
    'name' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(25)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'case_sensitivity' => "ENUM('0', '1') DEFAULT '1' NOT NULL",
    'partial' => "ENUM('0', '1') DEFAULT '1' NOT NULL");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Modifying columns in the restricted table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Releasing the lock on the restricted table...<br />';
    $db->query('UNLOCK TABLES');

    echo 'Deleting old tables...<br />';
    $db->query("DROP TABLE IF EXISTS ".X_PREFIX."logs");
    $db->query("DROP TABLE IF EXISTS ".X_PREFIX."whosonline");

    echo 'Adding new tables...<br />';
    $db->query("CREATE TABLE IF NOT EXISTS ".X_PREFIX."captchaimages (
        `imagehash` varchar(32) NOT NULL default '',
        `imagestring` varchar(12) NOT NULL default '',
        `dateline` int(10) NOT NULL default '0',
        KEY `dateline` (`dateline`)
      ) ENGINE=MyISAM");
    $db->query("CREATE TABLE ".X_PREFIX."logs (
        `username` varchar(32) NOT NULL,
        `action` varchar(64) NOT NULL default '',
        `fid` smallint(6) NOT NULL default 0,
        `tid` int(10) NOT NULL default 0,
        `date` int(10) NOT NULL default 0,
        KEY `username` (username (8)),
        KEY `action` (action (8)),
        INDEX ( `fid` ),
        INDEX ( `tid` ),
        INDEX ( `date` )
      ) ENGINE=MyISAM");
    $db->query("CREATE TABLE ".X_PREFIX."whosonline (
        `username` varchar(32) NOT NULL default '',
        `ip` varchar(15) NOT NULL default '',
        `time` int(10) NOT NULL default 0,
        `location` varchar(150) NOT NULL default '',
        `invisible` SET('1','0') default '0',
        KEY `username` (username (8)),
        KEY `ip` (`ip`),
        KEY `time` (`time`),
        KEY `invisible` (`invisible`)
      ) ENGINE=MyISAM PACK_KEYS=0");
}

/**
 * Performs all tasks needed to raise the database schema_version number to 2.
 *
 * This function is officially compatible with schema_version 1 as well as the following
 * XMB versions that did not have a schema_version number: 1.9.9, 1.9.10, and 1.9.11 Alpha (all).
 *
 * @author Robert Chapin (miqrogroove)
 * @since 1.9.11 Beta 3
 */
function upgrade_schema_to_v2() {
    global $db;
    
    echo 'Beginning schema upgrade to version number 2...<br />';

    echo 'Requesting to lock the settings table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."settings WRITE");

    echo 'Gathering schema information from the settings table...<br />';
    $sql = array();
    $table = 'settings';
    $columns = array(
    'boardurl');
    foreach($columns as $colname) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'DROP COLUMN '.$colname;
        }
        $db->free_result($query);
    }
    $columns = array(
    'attach_remote_images' => "SET('on', 'off') NOT NULL DEFAULT 'off'",
    'captcha_search_status' => "SET('on', 'off') NOT NULL DEFAULT 'off'",
    'files_min_disk_size' => "MEDIUMINT NOT NULL DEFAULT '9216'",
    'files_storage_path' => "VARCHAR( 100 ) NOT NULL",
    'files_subdir_format' => "TINYINT NOT NULL DEFAULT '1'",
    'file_url_format' => "TINYINT NOT NULL DEFAULT '1'",
    'files_virtual_url' => "VARCHAR(60) NOT NULL",
    'filesperpost' => "TINYINT NOT NULL DEFAULT '10'",
    'ip_banning' => "SET('on', 'off') NOT NULL DEFAULT 'on'",
    'max_image_size' => "VARCHAR(9) NOT NULL DEFAULT '1000x1000'",
    'max_thumb_size' => "VARCHAR(9) NOT NULL DEFAULT '200x200'",
    'schema_version' => "TINYINT UNSIGNED NOT NULL DEFAULT ".XMB_SCHEMA_VER);
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Adding/Deleting columns in the settings table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the attachments table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."attachments WRITE");

    echo 'Gathering schema information from the attachments table...<br />';
    $sql = array();
    $table = 'attachments';
    $columns = array(
    'tid');
    foreach($columns as $colname) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'DROP COLUMN '.$colname;
        }
        $db->free_result($query);
    }
    $columns = array(
    'img_size' => "VARCHAR(9) NOT NULL",
    'parentid' => "INT NOT NULL DEFAULT '0'",
    'subdir' => "VARCHAR(15) NOT NULL",
    'uid' => "INT NOT NULL DEFAULT '0'",
    'updatetime' => "TIMESTAMP NOT NULL default current_timestamp");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }
    $columns = array(
    'parentid',
    'uid');
    foreach($columns as $colname) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Column_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX ($colname)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Adding/Deleting columns in the attachments table...<br />';
        // Important to do this all in one step because MySQL copies the entire table after every ALTER command.
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the members table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."members WRITE");

    echo 'Gathering schema information from the members table...<br />';
    $sql = array();
    $table = 'members';
    $columns = array(
    'u2ualert' => "TINYINT NOT NULL DEFAULT '0'");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }
    $columns = array(
    'postnum' => "postnum MEDIUMINT NOT NULL DEFAULT 0");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'CHANGE '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Adding/Deleting columns in the members table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the ranks table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."ranks WRITE");

    echo 'Gathering schema information from the ranks table...<br />';
    $sql = array();
    $table = 'ranks';
    $columns = array(
    'posts' => "posts MEDIUMINT DEFAULT 0");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'CHANGE '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Adding/Deleting columns in the ranks table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the themes table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."themes WRITE");

    echo 'Gathering schema information from the themes table...<br />';
    $sql = array();
    $table = 'themes';
    $columns = array(
    'admdir' => "VARCHAR( 120 ) NOT NULL DEFAULT 'images/admin'");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Adding/Deleting columns in the themes table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the vote_desc table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."vote_desc WRITE");

    echo 'Gathering schema information from the vote_desc table...<br />';
    $sql = array();
    $table = 'vote_desc';
    $columns = array(
    'topic_id' => "topic_id INT UNSIGNED NOT NULL");
    foreach($columns as $colname => $coltype) {
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'CHANGE '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Adding/Deleting columns in the vote_desc table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Releasing the lock on the vote_desc table...<br />';
    $db->query('UNLOCK TABLES');

    echo 'Adding new tables...<br />';
    $db->query("CREATE TABLE IF NOT EXISTS ".X_PREFIX."lang_base (
        `langid` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `devname` VARCHAR( 20 ) NOT NULL ,
        UNIQUE ( `devname` )
      ) ENGINE=MyISAM COMMENT = 'List of Installed Languages'");
    $db->query("CREATE TABLE IF NOT EXISTS ".X_PREFIX."lang_keys (
        `phraseid` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `langkey` VARCHAR( 30 ) NOT NULL ,
        UNIQUE ( `langkey` )
      ) ENGINE=MyISAM COMMENT = 'List of Translation Variables'");
    $db->query("CREATE TABLE IF NOT EXISTS ".X_PREFIX."lang_text (
        `langid` TINYINT UNSIGNED NOT NULL ,
        `phraseid` SMALLINT UNSIGNED NOT NULL ,
        `cdata` BLOB NOT NULL ,
        PRIMARY KEY `langid` ( `langid` , `phraseid` ) ,
        INDEX ( `phraseid` )
      ) ENGINE=MyISAM COMMENT = 'Translation Table'");

    echo 'Resetting the schema version number...<br />';
    $db->query("UPDATE ".X_PREFIX."settings SET schema_version = 2");
}

/**
 * Performs all tasks needed to raise the database schema_version number to 3.
 *
 * This function is officially compatible with schema_version 2 only.
 *
 * @since 1.9.11 Beta 4
 */
function upgrade_schema_to_v3() {
    global $db;

    echo 'Beginning schema upgrade to version number 3...<br />';

    echo 'Requesting to lock the logs table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."logs WRITE");

    echo 'Gathering schema information from the logs table...<br />';
    $sql = array();
    $table = 'logs';
    $columns = array(
    'date',
    'tid');
    foreach($columns as $colname) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Column_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX ($colname)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Adding indexes to the logs table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Releasing the lock on the logs table...<br />';
    $db->query('UNLOCK TABLES');

    echo 'Resetting the schema version number...<br />';
    $db->query("UPDATE ".X_PREFIX."settings SET schema_version = 3");
}

/**
 * Performs all tasks needed to raise the database schema_version number to 4.
 *
 * @since 1.9.11 (Patch #11)
 */
function upgrade_schema_to_v4() {
    global $db;

    echo 'Beginning schema upgrade to version number 4...<br />';

    echo 'Requesting to lock the threads table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."threads WRITE");

    echo 'Gathering schema information from the threads table...<br />';
    $sql = array();
    $table = 'threads';
    $columns = array(
    'fid');
    foreach($columns as $colname) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Key_name = '$colname'");
        if ($db->num_rows($query) > 0) {
            $sql[] = "DROP INDEX $colname";
        }
        $db->free_result($query);
    }
    $columns = array(
    'forum_optimize' => 'fid, topped, lastpost');
    foreach($columns as $colname => $coltype) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Key_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Deleting/Adding indexes to the threads table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the posts table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."posts WRITE");

    echo 'Gathering schema information from the posts table...<br />';
    $sql = array();
    $table = 'posts';
    $columns = array(
    'tid');
    foreach($columns as $colname) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Key_name = '$colname'");
        if ($db->num_rows($query) > 0) {
            $sql[] = "DROP INDEX $colname";
        }
        $db->free_result($query);
    }
    $columns = array(
    'thread_optimize' => 'tid, dateline, pid');
    foreach($columns as $colname => $coltype) {
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX."$table WHERE Key_name = '$colname'");
        if ($db->num_rows($query) == 0) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        echo 'Deleting/Adding indexes to the posts table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Releasing the lock on the posts table...<br />';
    $db->query('UNLOCK TABLES');

    echo 'Resetting the schema version number...<br />';
    $db->query("UPDATE ".X_PREFIX."settings SET schema_version = 4");
}

/**
 * Recalculates the value of every field in the forums.postperm column.
 *
 * Function has been modified to run without parameters.
 *
 * @since 1.9.6 RC1
 */
function fixForumPerms() {
    global $db;
    /***
        OLD FORMAT:
        "NewTopics|NewReplies". Each field contains a number between 1 and 4:
        - 1 normal (all ranks),
        - 2 admin only,
        - 3 admin/mod only,
        - 4 no posting/viewing.
    ***/

    /***
        NEW FORMAT:
        NewPolls,NewThreads,NewReplies,View. Each field contains a number between 0-63 (a sum of the following:)
        - 1  Super Administrator
        - 2  Administrator
        - 4  Super Moderator
        - 8  Moderator
        - 16 Member
        - 32 Guest
    ***/

    // store
    $q = $db->query("SELECT fid, private, userlist, postperm, guestposting, pollstatus FROM ".X_PREFIX."forums WHERE (type='forum' or type='sub')");
    while($forum = $db->fetch_array($q)) {
        // check if we need to change it first
        $parts = explode('|', $forum['postperm']);
        if (count($parts) == 1) {
            // no need to upgrade these; new format in use [we hope]
            continue;
        }
        $newFormat = array(0,0,0,0);

        $fid            = $forum['fid'];
        $private        = $forum['private'];
        $permField      = $forum['postperm'];
        $guestposting   = $forum['guestposting'];
        $polls          = $forum['pollstatus'];

        $translationFields = array(0=>1, 1=>2);
        foreach($parts as $key=>$val) {
            switch($val) {
            case 1:
                $newFormat[$translationFields[$key]] = 31;
                break;
            case 2:
                $newFormat[$translationFields[$key]] = 3;
                break;
            case 3:
                $newFormat[$translationFields[$key]] = 15;
                break;
            case 4:
            default:
                $newFormat[$translationFields[$key]] = 1;
                break;
            }
        }
        switch($private) {
        case 1:
            $newFormat[3] = 63;
            break;
        case 2:
            $newFormat[3] = 3;
            break;
        case 3:
            $newFormat[3] = 15;
            break;
        case 4:
        default:
            $newFormat[3] = 1;
            break;
        }
        if ($guestposting == 'yes' || $guestposting == 'on') {
            $newFormat[0] |= 32;
            $newFormat[1] |= 32;
            $newFormat[2] |= 32;
        }

        if ($polls == 'yes' || $polls == 'on') {
            $newFormat[0] = $newFormat[1];
        } else {
            $newFormat[0] = 0;
        }

        $db->query("UPDATE ".X_PREFIX."forums SET postperm='".implode(',', $newFormat)."' WHERE fid=$fid");
    }
}

/**
 * Convert threads.pollopts text column into relational vote_ tables.
 *
 * @since 1.9.8
 */
function fixPolls() {
    global $db;

    $q = $db->query("SHOW COLUMNS FROM ".X_PREFIX."threads LIKE 'pollopts'");
    $result = $db->fetch_array($q);
    $db->free_result($q);

    if (FALSE === $result) return; // Unexpected condition, do not attempt to use fixPolls().
    if (FALSE !== strpos(strtolower($result['Type']), 'int')) return; // Schema already at 1.9.8+

    $q = $db->query("SELECT tid, subject, pollopts FROM ".X_PREFIX."threads WHERE pollopts != ''");
    while($thread = $db->fetch_array($q)) {
        // Poll titles are historically unslashed, but thread titles are double-slashed.
        $thread['subject'] = $db->escape(stripslashes($thread['subject']));

        $db->query("INSERT INTO ".X_PREFIX."vote_desc (`topic_id`, `vote_text`, `vote_start`) VALUES ({$thread['tid']}, '{$thread['subject']}', 0)");
        $poll_id = $db->insert_id();

        $options = explode("#|#", $thread['pollopts']);
        $num_options = count($options) - 1;

        if (0 == $num_options) continue; // Sanity check.  Remember, 1 != '' evaluates to TRUE in MySQL.

        $voters = explode('    ', trim($options[$num_options]));

        if (1 == count($voters) and strlen($voters[0]) < 3) {
            // The most likely values for $options[$num_options] are '' and '1'.  Treat them equivalent to null.
        } else {
            $name = array();
            foreach($voters as $v) {
                $name[] = $db->escape(trim($v));
            }
            $name = "'".implode("', '", $name)."'";
            $query = $db->query("SELECT uid FROM ".X_PREFIX."members WHERE username IN ($name)");
            $values = array();
            while($u = $db->fetch_array($query)) {
                $values[] = "($poll_id, {$u['uid']})";
            }
            $db->free_result($query);
            if (count($values) > 0) {
                $db->query("INSERT INTO ".X_PREFIX."vote_voters (`vote_id`, `vote_user_id`) VALUES ".implode(',', $values));
            }
        }

        $values = array();
        for($i = 0; $i < $num_options; $i++) {
            $bit = explode('||~|~||', $options[$i]);
            $option_name = $db->escape(trim($bit[0]));
            $num_votes = (int) trim($bit[1]);
            $values[] = "($poll_id, ".($i+1).", '$option_name', $num_votes)";
        }
        $db->query("INSERT INTO ".X_PREFIX."vote_results (`vote_id`, `vote_option_id`, `vote_option_text`, `vote_result`) VALUES ".implode(',', $values));
    }
    $db->free_result($q);
    $db->query("UPDATE ".X_PREFIX."threads SET pollopts='1' WHERE pollopts != ''");
}

/**
 * Checks the format of everyone's birthdate and fixes or resets them.
 *
 * Function has been modified to work without parameters.
 * Note the actual schema change was made in 1.9.4, but the first gamma version
 * to implement fixBirthdays was 1.9.8, and it still didn't work right.
 *
 * @since 1.9.6 RC1
 */
function fixBirthdays() {
    global $db;

    $cachedLanguages = array();
    $lang = array();

    require ROOT.'lang/English.lang.php';
    $baselang = $lang;
    $cachedLanguages['English'] = $lang;

    $q = $db->query("SELECT uid, bday, langfile FROM ".X_PREFIX."members");
    while($m = $db->fetch_array($q)) {
        $uid = $m['uid'];
        if (strlen($m['bday']) == 0) {
            $db->query("UPDATE ".X_PREFIX."members SET bday='0000-00-00' WHERE uid=$uid");
            continue;
        }

        // check if the birthday is already in proper format
        $parts = explode('-', $m['bday']);
        if (count($parts) == 3 && is_numeric($parts[0]) && is_numeric($parts[1]) && is_numeric($parts[2])) {
            continue;
        }

        $lang = array();

        if (!isset($cachedLanguages[$m['langfile']])) {
			$old_error_level = error_reporting();
		    error_reporting(E_ERROR | E_PARSE | E_USER_ERROR);
            require ROOT.'lang/'.$m['langfile'].'.lang.php';
			error_reporting($old_error_level);
            $cachedLanguages[$m['langfile']] = $lang;
        }

        if (isset($cachedLanguages[$m['langfile']])) {
            $lang = array_merge($baselang, $cachedLanguages[$m['langfile']]);
        } else {
            $lang = $baselang;
        }

        $day = 0;
        $month = 0;
        $year = 0;
        $monthList = array($lang['textjan'] => 1,$lang['textfeb'] => 2,$lang['textmar'] => 3,$lang['textapr'] =>4,$lang['textmay'] => 5,$lang['textjun'] => 6,$lang['textjul'] => 7,$lang['textaug'] => 8,$lang['textsep'] => 9,$lang['textoct'] => 10,$lang['textnov'] => 11,$lang['textdec'] => 12);
        $parts = explode(' ', $m['bday']);
        if (count($parts) == 3 && isset($monthList[$parts[0]])) {
            $month = $monthList[$parts[0]];
            $day = substr($parts[1], 0, -1); // cut off trailing comma
            $year = $parts[2];
            $db->query("UPDATE ".X_PREFIX."members SET bday='".iso8601_date($year, $month, $day)."' WHERE uid=$uid");
        } else {
            $db->query("UPDATE ".X_PREFIX."members SET bday='0000-00-00' WHERE uid=$uid");
        }
    }
}

/**
 * Recalculates the value of every field in the forums.postperm column.
 *
 * @since 1.9.1
 */
function fixPostPerm() {
    global $db;

	$query = $db->query("SELECT fid, private, postperm, guestposting FROM ".X_PREFIX."forums WHERE type != 'group'");
	while ( $forum = $db->fetch_array($query) ) {
		$update = false;
		$pp = trim($forum['postperm']);
		if ( strlen($pp) > 0 && strpos($pp, '|') === false ) {
			$update = true;
			$forum['postperm'] = $pp . '|' . $pp;	// make the postperm the same for thread and reply
		}
		if ( $forum['guestposting'] != 'on' and $forum['guestposting'] != 'off' ) {
			$forum['guestposting'] = 'off';
			$update = true;
		}
		if ( $forum['private'] == '' ) {
			$forum['private'] = '1';	// by default, forums are not private.
			$update = true;
		}
		if ( $update ) {
			$db->query("UPDATE ".X_PREFIX."forums SET postperm='{$forum['postperm']}', guestposting='{$forum['guestposting']}', private='{$forum['private']}' WHERE fid={$forum['fid']}");
		}
	}
	$db->free_result($query);
}
?>
