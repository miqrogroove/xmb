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
if (!(is_file('header.php') And is_dir('include'))) {
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

if (!defined('X_SADMIN') Or !X_SADMIN) {
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


if (!isset($_GET['step']) Or $_GET['step'] == 1) {
?>
<h1>XMB 1.9.10 to 1.9.11 Upgrade Script</h1>

<p>This script is also compatible with XMB 1.9.9 as well as XMB 1.9.11 Betas.

<p>This script is NOT compatible with XMB 1.9.8.

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
    <h1>XMB 1.9.10 to 1.9.11 Upgrade Script</h1>
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
 * 1.9.8 SP2, 1.9.8 SP3, 1.9.9, 1.9.10.
 *
 * @author Robert Chapin (miqrogroove)
 * @since 1.9.11 (Patch #11)
 */
function upgrade_schema_to_v0() {
    global $db;

    echo 'Beginning schema upgrade from legacy version...<br />';

    $upgrade_permissions = TRUE;

    echo 'Requesting to lock the forums table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."forums WRITE");

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

        echo 'Loading the new postperm values...<br />';
        fixForumPerms(0);

        echo 'Making room for the new values in the postperm column...<br />';
        $db->query('ALTER TABLE '.X_PREFIX."forums MODIFY COLUMN postperm VARCHAR(11) NOT NULL DEFAULT '0,0,0,0'");

        echo 'Saving the new postperm values...<br />';
        fixForumPerms(1);

        echo 'Deleting the index on the private column...<br />';
        $query = $db->query('SHOW INDEX FROM '.X_PREFIX.'forums');
        while($indexrow = $db->fetch_array($query)) {
            if ($indexrow['Key_name'] == 'private') { // Index exists
                $db->query('ALTER TABLE '.X_PREFIX."forums DROP INDEX private");
                break;
            }
        }
        $db->free_result($query);

        echo 'Deleting the old columns in the forums table...<br />';
        $columns = array(
        'private',
        'pollstatus',
        'guestposting',
        'mt_status',
        'mt_open',
        'mt_close');
        foreach($columns as $colname) {
            $query = $db->query('DESCRIBE '.X_PREFIX.'forums '.$colname);
            if ($db->num_rows($query) == 1) {
                $db->query('ALTER TABLE '.X_PREFIX.'forums DROP COLUMN '.$colname);
            }
            $db->free_result($query);
        }

    } else {
        // Verify new schema is not missing.  Results would be unpredictable.
        $colname = 'postperm';
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) != 'varchar(11)') {
            echo 'Unexpected schema in forums table.  Upgrade aborted to prevent damage.';
            trigger_error('Attempted upgrade on inconsistent schema aborted automatically.', E_USER_ERROR);
        }
    } // upgrade_permissions
    
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
    
    if (count($sql) > 0) {
        echo 'Deleting the old columns in the forums table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Requesting to lock the settings table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."settings WRITE");

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

    if (count($sql) > 0) {
        echo 'Deleting the old columns in the settings table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Releasing the lock on the settings table...<br />';
    $db->query('UNLOCK TABLES');
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
      ) TYPE=MyISAM COMMENT = 'List of Installed Languages'");
    $db->query("CREATE TABLE IF NOT EXISTS ".X_PREFIX."lang_keys (
        `phraseid` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `langkey` VARCHAR( 30 ) NOT NULL ,
        UNIQUE ( `langkey` )
      ) TYPE=MyISAM COMMENT = 'List of Translation Variables'");
    $db->query("CREATE TABLE IF NOT EXISTS ".X_PREFIX."lang_text (
        `langid` TINYINT UNSIGNED NOT NULL ,
        `phraseid` SMALLINT UNSIGNED NOT NULL ,
        `cdata` BLOB NOT NULL ,
        PRIMARY KEY `langid` ( `langid` , `phraseid` ) ,
        INDEX ( `phraseid` )
      ) TYPE=MyISAM COMMENT = 'Translation Table'");

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
 * Recalculates the value of every field in the forums.postperm column.
 *
 * Note the "old format" never used more than two digits.  The original
 * comments appear to be wrong.
 *
 * @param int $v Zero causes old values to be cached. One causes new values to be recorded.
 * @since 1.9.6 RC1
 */
function fixForumPerms($v) {
    static $cache;
    global $db;
    /***
        OLD FORMAT:
        "NewTopics|NewReplies|ViewForum". Each field contains a number between 1 and 4:
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
    switch($v) {
        case 0:
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
                            $newFormat[$translationFields[$key]] = 1;
                            break;
                        default:
                            // allow only superadmin
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
                        $newFormat[3] = 1;
                        break;
                    default:
                        // allow only superadmin
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

                $cache[$fid] = $newFormat;
            }
            break;

        case 1:
            // restore
            if (isset($cache) && count($cache) > 0) {
                foreach($cache as $fid=>$format) {
                    $db->query("UPDATE ".X_PREFIX."forums SET postperm='".implode(',', $format)."' WHERE fid=$fid");
                }
            }
            break;
    }
}
?>
