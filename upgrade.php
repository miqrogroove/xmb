<?php
/**
 * eXtreme Message Board
 * XMB 1.9.10 Karl
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


if (!isset($_GET['step']) Or $_GET['step'] == 1) {
?>
<h1>XMB 1.9.8 SP3 to 1.9.10 Upgrade Script</h1>

<p>This script is compatible with XMB 1.9.8 SP2 as well as SP3.

<p>This script is NOT compatible with XMB 1.9.9.

<h2>Instructions</h2>
<ol>
<li>BACKUP YOUR DATABASE - This script cannot be undone!
<li>Confirm your forum database account is granted ALTER and LOCK privileges.
<li>Disable your forums using the Board Status setting.
<li>Upload the XMB 1.9.10 files.
<li>Upload and run this script to complete your database upgrade.
<li>Go to the Administration Panel to edit your Forum permissions.
<li>Enable your forums using the Board Status setting.
<li>Deny ALTER and LOCK privileges to the forum database account. (Optional, recommended)
</ol>

<p>When you are ready, <a href="?step=2">Click Here if you already have a backup and want to begin the upgrade</a>.
<?php

} elseif ($_GET['step'] == 2) {

    ?>
    <h1>XMB 1.9.8 SP3 to 1.9.10 Upgrade Script</h1>
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
    
    echo 'Requesting to lock the forums table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."forums WRITE");

    echo 'Checking the forums table schema...<br />';
    $columns = array(
    'private',
    'pollstatus',
    'guestposting');
    foreach($columns as $colname) {
        $query = $db->query('DESCRIBE '.X_PREFIX.'forums '.$colname);
        if ($db->num_rows($query) != 1) {
            echo 'The forums table in your database is not at version 1.9.8.<br />'
                .'The upgrade process has been aborted to avoid damaging your database.<br />';
            trigger_error('Admin attempted upgrade while required fields missing from forums table.', E_USER_ERROR);
        }
        $db->free_result($query);
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

    echo 'Releasing the lock on the forums table...<br />';
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

    echo 'Requesting to lock the settings table...<br />';
    $db->query('LOCK TABLES '.X_PREFIX."settings WRITE");

    echo 'Deleting the old columns in the settings table...<br />';
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
        $query = $db->query('DESCRIBE '.X_PREFIX.'settings '.$colname);
        if ($db->num_rows($query) == 1) {
            $db->query('ALTER TABLE '.X_PREFIX.'settings DROP COLUMN '.$colname);
        }
        $db->free_result($query);
    }

    echo 'Releasing the lock on the settings table...<br />';
    $db->query('UNLOCK TABLES');
    flush();

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
                if(count($parts) == 1) {
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
                if($guestposting == 'yes' || $guestposting == 'on') {
                    $newFormat[0] |= 32;
                    $newFormat[1] |= 32;
                    $newFormat[2] |= 32;
                }

                if($polls == 'yes' || $polls == 'on') {
                    $newFormat[0] = $newFormat[1];
                } else {
                    $newFormat[0] = 0;
                }

                $cache[$fid] = $newFormat;
            }
            break;

        case 1:
            // restore
            if(isset($cache) && count($cache) > 0) {
                foreach($cache as $fid=>$format) {
                    $db->query("UPDATE ".X_PREFIX."forums SET postperm='".implode(',', $format)."' WHERE fid=$fid");
                }
            }
            break;
    }
}
?>
