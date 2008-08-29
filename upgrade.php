<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Alpha Two - This software should not be used for any purpose after 31 October 2008.
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2008, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * Copyright (c) 2007, iEntry, Inc.
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

//Check location
if (!(is_file('header.php') And is_dir('include'))) {
    echo 'Could not find XMB!<br />'
        .'Please make sure the upgrade.php file is in the same folder as index.php and header.php.<br />';
    trigger_error('Attempted upgrade by '.$_SERVER['REMOTE_ADDR'].' from wrong location.', E_USER_ERROR);
}

//Authenticate Browser
define('X_SCRIPT', 'upgrade.php');
require('header.php');
echo 'Database Connection Established<br />';
if (DEBUG) {
    echo 'Debug Mode Enabled';
} else {
    echo 'Debug is False - You will not see any errors.';
}

if (!defined('X_SADMIN') Or !X_SADMIN) {
    echo '<br /><br />This script may be run only by a Super Administrator.<br />'
        .'Please <a href="misc.php?action=login">Log In</a> first to begin the upgrade successfully.<br />';
    trigger_error('Unauthenticated upgrade attempt by '.$_SERVER['REMOTE_ADDR'], E_USER_ERROR);
}

//Check Server Version
define('MYSQL_MIN_VER', '4.0.16');
define('PHP_MIN_VER', '4.3.0');
$current = explode('.', phpversion());
$min = explode('.', PHP_MIN_VER);
if ($current[0] < $min[0] || ($current[0] == $min[0] && ($current[1] < $min[1] || ($current[1] == $min[1] && $current[2] < $min[2])))) {
    echo '<br /><br />XMB requires PHP version '.PHP_MIN_VER.' or higher to work properly.  Version '.phpversion().' is running.';
    trigger_error('Admin attempted upgrade with obsolete PHP engine.', E_USER_ERROR);
}
$sqlver = mysql_get_server_info($db->link);
$current = explode('.', $sqlver);
$min = explode('.', MYSQL_MIN_VER);
if ($current[0] < $min[0] || ($current[0] == $min[0] && ($current[1] < $min[1] || ($current[1] == $min[1] && $current[2] < $min[2])))) {
    echo '<br /><br />XMB requires MySQL version '.MYSQL_MIN_VER.' or higher to work properly.  Version '.$sqlver.' is running.';
    trigger_error('Admin attempted upgrade with obsolete MySQL engine.', E_USER_ERROR);
}


if (!isset($_GET['step']) Or $_GET['step'] == 1) {
?>
<h1>XMB 1.9.8 SP3 to 1.9.10 Upgrade Script</h1>

<p>This script is compatible with XMB 1.9.10 as well as 1.9.9.

<p>This script is NOT compatible with XMB 1.9.8.

<h2>Instructions</h2>
<ol>
<li>BACKUP YOUR DATABASE - This script cannot be undone!
<li>Copy your config.php settings into the new file.
<li>Confirm your forum database account is granted ALTER and LOCK privileges.
<li>Disable your forums using the Board Status setting.
<li>Upload the XMB 1.9.11 files.
<li>Upload and run this script to complete your database upgrade.
<li>Go to the Administration Panel to edit your Forum permissions.
<li>Enable your forums using the Board Status setting.
<li>Deny ALTER and LOCK privileges to the forum database account. (Optional, recommended)
</ol>

<p>When you are ready, <a href="?step=2">Click Here if you already have a backup and want to begin the upgrade</a>.
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

    echo 'Confirming forums are turned off...<br />';
    if ($SETTINGS['bbstatus'] != 'off') {
        echo 'Your board must be turned off before the upgrade can begin!<br />'
            .'Please <a href="cp.php?action=settings">Go To The Admin Panel</a> first to begin the upgrade successfully.<br />';
        trigger_error('Admin attempted upgrade without turning off the board.', E_USER_ERROR);
    }
    flush();

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
            $sql[] = 'DROP COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }
    $columns = array(
    'attach_remote_images' => "SET('on', 'off') NOT NULL DEFAULT 'off'";
    'files_min_disk_size' => "MEDIUMINT NOT NULL DEFAULT '9216'";
    'files_storage_path' => "VARCHAR( 100 ) NOT NULL";
    'files_subdir_format' => "TINYINT NOT NULL DEFAULT '1'";
    'file_url_format' => "TINYINT NOT NULL DEFAULT '1'",
    'files_virtual_url' => "VARCHAR(60) NOT NULL",
    'filesperpost' => "TINYINT NOT NULL DEFAULT '10'",
    'max_image_size' => "VARCHAR(9) NOT NULL DEFAULT '2000x2000'",
    'max_thumb_size' => "VARCHAR(9) NOT NULL DEFAULT '200x200'");
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
            $sql[] = 'DROP COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }
    $columns = array(
    'img_size' => "VARCHAR(9) NOT NULL",
    'parentid' => "INT NOT NULL DEFAULT '0'",
    'subdir' => "VARCHAR(1 ) NOT NULL",
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

    if (count($sql) > 0) {
        echo 'Adding/Deleting columns in the members table...<br />';
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    echo 'Releasing the lock on the members table...<br />';
    $db->query('UNLOCK TABLES');
    flush();


    echo 'Opening the templates file...<br />';
    $stream = fopen('templates.xmb','r');
    $file = fread($stream, filesize('templates.xmb'));
    fclose($stream);

    echo 'Resetting the templates table...<br />';
    $db->query('TRUNCATE TABLE '.X_PREFIX.'templates');

    echo 'Requesting to lock the templates table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."templates WRITE");

    echo 'Saving the new templates...<br />';
    $templates = explode("|#*XMB TEMPLATE FILE*#|", $file);
    foreach($templates as $key=>$val) {
        $template = explode("|#*XMB TEMPLATE*#|", $val);
        if (isset($template[1])) {
            $template[1] = addslashes($template[1]);
        } else {
            $template[1] = '';
        }
        $db->query("INSERT INTO `".X_PREFIX."templates` (`name`, `template`) VALUES ('".addslashes($template[0])."', '".addslashes($template[1])."')");
    }
    $db->query("DELETE FROM `".X_PREFIX."templates` WHERE name=''");
    unset($file);
    flush();

    echo 'Deleting the templates.xmb file...<br />';
    unlink('templates.xmb');


    echo 'Checking for new themes...';
    $query = $db->query("SELECT themeid FROM ".X_PREFIX."themes WHERE name='Oxygen XMB'");
    if ($db->num_rows($query) == 0 And is_dir('images/oxygen')) {
        echo 'Adding Oxygen as the new default theme...<br />';
        $db->query("INSERT INTO ".X_PREFIX."themes (`name`, `bgcolor`, `altbg1`, `altbg2`, `link`, `bordercolor`, `header`, `headertext`, `top`, `catcolor`, `tabletext`, `text`, `borderwidth`, `tablewidth`, `tablespace`, `font`, `fontsize`, `boardimg`, `imgdir`, `smdir`, `cattext`) VALUES ('Oxygen XMB', 'bg_loop.gif', '#fdfdfd', '#fdfdfd', '#000000', '#ddeef7', '#d1e5ef', '#000000', '#ffffff', 'catbg.png', '#343434', '#343434', '1px', '800px', '5px', 'Verdana, Arial, Helvetica', '10px', 'logo.png', 'images/oxygen', 'images/smilies', '#FFFFFF')");
        $newTheme = $db->insert_id();
        $db->query("UPDATE ".X_PREFIX."settings SET theme=$newTheme");
    }
    $db->free_result($query);

    echo 'Deleting the upgrade.php file...<br />';
    unlink('upgrade.php');

    echo 'Done! :D<br />Now <a href="cp.php?action=forum">edit the forum permissions</a>.<br />';
}

?>
