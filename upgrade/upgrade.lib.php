<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2011, The XMB Group
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

if (!defined('IN_CODE')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

/**
 * Performs all tasks necessary for a normal upgrade.
 */
function xmb_upgrade() {
    global $db, $SETTINGS;
    
    show_progress('Confirming forums are turned off');
    if ($SETTINGS['bbstatus'] != 'off') {
        $db->query("UPDATE ".X_PREFIX."settings SET bbstatus = 'off'");
        show_warning('Your forums were turned off by the upgrader to prevent damage.  They will remain unavailable to your members until you reset the Board Status setting in the Admin Panel.');
        trigger_error('Admin attempted upgrade without turning off the board.  Board now turned off.', E_USER_WARNING);
    }

    show_progress('Determining the database schema version');
    require(ROOT.'include/schema.inc.php');
    if (!isset($SETTINGS['schema_version'])) {
        $SETTINGS['schema_version'] = 0;
    }
    switch ($SETTINGS['schema_version']) {
        case XMB_SCHEMA_VER:
            show_progress('Database schema is current, skipping ALTER commands');
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
            show_error('Unrecognized Database!  This upgrade utility is not compatible with your version of XMB.  Upgrade halted to prevent damage.');
            trigger_error('Admin attempted upgrade with obsolete upgrade utility.', E_USER_ERROR);
            break;
    }
    show_progress('Database schema is now current');

    show_progress('Initializing the new translation system');
    require_once(ROOT.'include/translation.inc.php');
    $upload = file_get_contents(ROOT.'lang/English.lang.php');

    show_progress('Installing English.lang.php');
    installNewTranslation($upload);
    unset($upload);

    show_progress('Opening the templates file');
    $templates = explode("|#*XMB TEMPLATE FILE*#|", file_get_contents(ROOT.'templates.xmb'));

    show_progress('Resetting the templates table');
    $db->query('TRUNCATE TABLE '.X_PREFIX.'templates');

    show_progress('Requesting to lock the templates table');
    $db->query('LOCK TABLES '.X_PREFIX."templates WRITE");

    show_progress('Saving the new templates');
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

    show_progress('Releasing the lock on the templates table');
    $db->query('UNLOCK TABLES');

    show_progress('Deleting the templates.xmb file');
    unlink(ROOT.'templates.xmb');


    show_progress('Checking for new themes');
    $query = $db->query("SELECT themeid FROM ".X_PREFIX."themes WHERE name='XMB Davis'");
    if ($db->num_rows($query) == 0 and is_dir(ROOT.'images/davis')) {
        show_progress('Adding Davis as the new default theme');
        $db->query("INSERT INTO ".X_PREFIX."themes (`name`,      `bgcolor`, `altbg1`,  `altbg2`,  `link`,    `bordercolor`, `header`,  `headertext`, `top`,       `catcolor`,   `tabletext`, `text`,    `borderwidth`, `tablewidth`, `tablespace`, `font`,                              `fontsize`, `boardimg`, `imgdir`,       `smdir`,          `cattext`) "
                                          ."VALUES ('XMB Davis', 'bg.gif',  '#FFFFFF', '#f4f7f8', '#24404b', '#86a9b6',     '#d3dfe4', '#24404b',    'topbg.gif', 'catbar.gif', '#000000',   '#000000', '1px',         '97%',        '5px',        'Tahoma, Arial, Helvetica, Verdana', '11px',     'logo.gif', 'images/davis', 'images/smilies', '#163c4b');");
        $newTheme = $db->insert_id();
        $db->query("UPDATE ".X_PREFIX."settings SET theme=$newTheme");
    }
    $db->free_result($query);

    show_progress('Deleting the upgrade files');
    rmFromDir(ROOT.'upgrade');
}

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

    show_progress('Beginning schema upgrade from legacy version');

    show_progress('Requesting to lock the banned table');
    $db->query('LOCK TABLES '.X_PREFIX."banned WRITE");

    show_progress('Gathering schema information from the banned table');
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
        if (!xmb_schema_index_exists($table, $colname)) {
            $sql[] = "ADD INDEX ($colname)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Modifying columns in the banned table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the buddys table');
    $db->query('LOCK TABLES '.X_PREFIX."buddys WRITE");

    show_progress('Gathering schema information from the buddys table');
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
        if (!xmb_schema_index_exists($table, $colname)) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        } elseif (!xmb_schema_index_exists($table, $colname, '', '8')) {
            $sql[] = "DROP INDEX $colname";
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Modifying columns in the buddys table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the favorites table');
    $db->query('LOCK TABLES '.X_PREFIX."favorites WRITE");

    show_progress('Gathering schema information from the favorites table');
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
        if (!xmb_schema_index_exists($table, $colname)) {
            $sql[] = "ADD INDEX ($colname)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Modifying columns in the favorites table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the themes table');
    $db->query('LOCK TABLES '.X_PREFIX."themes WRITE");

    show_progress('Gathering schema information from the themes table');
    $sql = array();
    $table = 'themes';
    $colname = 'themeid';
    if (xmb_schema_index_exists($table, '', 'PRIMARY') and !xmb_schema_index_exists($table, $colname, 'PRIMARY')) {
        $sql[] = "DROP PRIMARY KEY";
    }

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
    if (!xmb_schema_index_exists($table, $colname, 'PRIMARY')) {
        $sql[] = "ADD PRIMARY KEY ($colname)";
    }

    $columns = array(
    'name');
    foreach($columns as $colname) {
        if (!xmb_schema_index_exists($table, '', $colname)) {
            $sql[] = "ADD INDEX ($colname)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Modifying columns in the themes table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the forums table');
    $db->query('LOCK TABLES '.
        X_PREFIX.'forums WRITE, '.
        X_PREFIX.'themes READ');

    $upgrade_permissions = TRUE;

    show_progress('Gathering schema information from the forums table');
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
            show_error('Unexpected schema in forums table.  Upgrade aborted to prevent damage.');
            trigger_error('Attempted upgrade on inconsistent schema aborted automatically.', E_USER_ERROR);
        }

        show_progress('Making room for the new values in the postperm column');
        $db->query('ALTER TABLE '.X_PREFIX."forums MODIFY COLUMN postperm VARCHAR(11) NOT NULL DEFAULT '0,0,0,0'");

        show_progress('Restructuring the forum permissions data');
        fixPostPerm();   // 1.8 => 1.9.1
        fixForumPerms(); // 1.9.1 => 1.9.9

        // Drop columns now so that any errors later on wont leave both sets of permissions.
        show_progress('Deleting the old permissions data');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
        $sql = array();

    } else {

        // Verify new schema is not missing.  Results would be unpredictable.
        $colname = 'postperm';
        $query = $db->query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) != 'varchar(11)') {
            show_error('Unexpected schema in forums table.  Upgrade aborted to prevent damage.');
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
        if (!xmb_schema_index_exists($table, $colname)) {
            $sql[] = "ADD INDEX ($colname)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Deleting/Modifying columns in the forums table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the settings table');
    $db->query('LOCK TABLES '.
        X_PREFIX.'settings WRITE, '.
        X_PREFIX.'themes READ');

    show_progress('Gathering schema information from the settings table');
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
        show_progress('Adding/Deleting/Modifying columns in the settings table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the members table');
    $db->query('LOCK TABLES '.
        X_PREFIX.'members WRITE, '.
        X_PREFIX.'themes READ');

    show_progress('Fixing birthday values');
    fixBirthdays();

    show_progress('Gathering schema information from the members table');
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
        if (!xmb_schema_index_exists($table, '', $colname)) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        } elseif (!xmb_schema_index_exists($table, '', $colname, '8')) {
            $sql[] = "DROP INDEX $colname";
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
    }

    $columns = array(
    'status',
    'postnum',
    'password',
    'email',
    'regdate',
    'invisible');
    foreach($columns as $colname) {
        if (!xmb_schema_index_exists($table, $colname)) {
            $sql[] = "ADD INDEX ($colname)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Deleting/Adding/Modifying columns in the members table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    // Mimic old function fixPPP()
    show_progress('Fixing missing posts per page values');
    $db->query("UPDATE ".X_PREFIX."members SET ppp={$SETTINGS['postperpage']} WHERE ppp=0");
    $db->query("UPDATE ".X_PREFIX."members SET tpp={$SETTINGS['topicperpage']} WHERE tpp=0");

    show_progress('Updating outgoing U2U status');
	$db->query("UPDATE ".X_PREFIX."members SET saveogu2u='yes'");

    show_progress('Releasing the lock on the members table');
    $db->query('UNLOCK TABLES');

    show_progress('Adding new tables for polls');
    xmb_schema_table('create', 'vote_desc');
    xmb_schema_table('create', 'vote_results');
    xmb_schema_table('create', 'vote_voters');

    show_progress('Requesting to lock the polls tables');
    $db->query('LOCK TABLES '.
        X_PREFIX.'threads WRITE, '.
        X_PREFIX.'vote_desc WRITE, '.
        X_PREFIX.'vote_results WRITE, '.
        X_PREFIX.'vote_voters WRITE, '.
        X_PREFIX.'members READ');

    show_progress('Upgrading polls to new system');
    fixPolls();

    show_progress('Gathering schema information from the threads table');
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
    if (xmb_schema_index_exists($table, $colname, $colname)) {
        $sql[] = 'DROP INDEX '.$colname;
    }

    $columns = array(
    'author' => "author (8)");
    foreach($columns as $colname => $coltype) {
        if (!xmb_schema_index_exists($table, '', $colname)) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        } elseif (!xmb_schema_index_exists($table, '', $colname, '8')) {
            $sql[] = "DROP INDEX $colname";
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
    }

    $columns = array(
    'lastpost' => "lastpost",
    'closed' => "closed",
    'forum_optimize' => "fid, topped, lastpost");
    foreach($columns as $colname => $coltype) {
        if (!xmb_schema_index_exists($table, '', $colname)) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Modifying columns in the threads table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the attachments table');
    $db->query('LOCK TABLES '.X_PREFIX."attachments WRITE");

    show_progress('Gathering schema information from the attachments table');
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
        if (!xmb_schema_index_exists($table, $colname)) {
            $sql[] = "ADD INDEX ($colname)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Modifying columns in the attachments table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the posts table');
    $db->query('LOCK TABLES '.X_PREFIX."posts WRITE");

    show_progress('Gathering schema information from the posts table');
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
        if (!xmb_schema_index_exists($table, '', $colname)) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        } elseif (!xmb_schema_index_exists($table, '', $colname, '8')) {
            $sql[] = "DROP INDEX $colname";
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
    }

    $columns = array(
    'fid' => "fid",
    'dateline' => "dateline",
    'thread_optimize' => "tid, dateline, pid");
    foreach($columns as $colname => $coltype) {
        if (!xmb_schema_index_exists($table, '', $colname)) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Modifying columns in the posts table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the ranks table');
    $db->query('LOCK TABLES '.X_PREFIX."ranks WRITE");

    show_progress('Gathering schema information from the ranks table');
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
        if (!xmb_schema_index_exists($table, $colname)) {
            $sql[] = "ADD INDEX ($colname)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Modifying columns in the ranks table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Fixing special ranks');
    $db->query("DELETE FROM ".X_PREFIX."ranks WHERE title IN ('Moderator', 'Super Moderator', 'Administrator', 'Super Administrator')");
    $db->query("INSERT INTO ".X_PREFIX."ranks
     (title,                 posts, stars, allowavatars, avatarrank) VALUES
     ('Moderator',           -1,    6,     'yes',  ''),
     ('Super Moderator',     -1,    7,     'yes',  ''),
     ('Administrator',       -1,    8,     'yes',  ''),
     ('Super Administrator', -1,    9,     'yes',  '')"
    );

    show_progress('Requesting to lock the templates table');
    $db->query('LOCK TABLES '.X_PREFIX."templates WRITE");

    show_progress('Gathering schema information from the templates table');
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
        if (!xmb_schema_index_exists($table, $colname)) {
            $sql[] = "ADD INDEX ($colname)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Modifying columns in the templates table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the u2u table');
    $db->query('LOCK TABLES '.X_PREFIX."u2u WRITE");

    $upgrade_u2u = FALSE;

    show_progress('Gathering schema information from the u2u table');
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
            show_progress('Modifying columns in the u2u table');
            $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
            $db->query($sql);
        }

        $sql = array();

        // Mimic old function upgradeU2U() but with fewer queries
        show_progress('Upgrading U2Us');
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
        if (!xmb_schema_index_exists($table, $colname)) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        } elseif (!xmb_schema_index_exists($table, $colname, '', '8')) {
            $sql[] = "DROP INDEX $colname";
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
    }

    $columns = array(
    'folder' => "folder (8)",
    'readstatus' => "readstatus",
    'owner' => "owner (8)");
    foreach($columns as $colname => $coltype) {
        if (!xmb_schema_index_exists($table, $colname)) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Modifying columns in the u2u table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the words table');
    $db->query('LOCK TABLES '.X_PREFIX."words WRITE");

    show_progress('Gathering schema information from the words table');
    $sql = array();
    $table = 'words';
    $columns = array(
    'find');
    foreach($columns as $colname) {
        if (!xmb_schema_index_exists($table, $colname)) {
            $sql[] = "ADD INDEX ($colname)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Adding indexes in the words table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the restricted table');
    $db->query('LOCK TABLES '.X_PREFIX."restricted WRITE");

    show_progress('Gathering schema information from the restricted table');
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
        show_progress('Modifying columns in the restricted table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Releasing the lock on the restricted table');
    $db->query('UNLOCK TABLES');

    show_progress('Adding new tables');
    xmb_schema_table('create', 'captchaimages');
    xmb_schema_table('overwrite', 'logs');
    xmb_schema_table('overwrite', 'whosonline');
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

    show_progress('Beginning schema upgrade to version number 2');

    show_progress('Requesting to lock the settings table');
    $db->query('LOCK TABLES '.X_PREFIX."settings WRITE");

    show_progress('Gathering schema information from the settings table');
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
        show_progress('Adding/Deleting columns in the settings table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the attachments table');
    $db->query('LOCK TABLES '.X_PREFIX."attachments WRITE");

    show_progress('Gathering schema information from the attachments table');
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
        if (!xmb_schema_index_exists($table, $colname)) {
            $sql[] = "ADD INDEX ($colname)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Adding/Deleting columns in the attachments table');
        // Important to do this all in one step because MySQL copies the entire table after every ALTER command.
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the members table');
    $db->query('LOCK TABLES '.X_PREFIX."members WRITE");

    show_progress('Gathering schema information from the members table');
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
        show_progress('Adding/Deleting columns in the members table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the ranks table');
    $db->query('LOCK TABLES '.X_PREFIX."ranks WRITE");

    show_progress('Gathering schema information from the ranks table');
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
        show_progress('Adding/Deleting columns in the ranks table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the themes table');
    $db->query('LOCK TABLES '.X_PREFIX."themes WRITE");

    show_progress('Gathering schema information from the themes table');
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
        show_progress('Adding/Deleting columns in the themes table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the vote_desc table');
    $db->query('LOCK TABLES '.X_PREFIX."vote_desc WRITE");

    show_progress('Gathering schema information from the vote_desc table');
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
        show_progress('Adding/Deleting columns in the vote_desc table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Releasing the lock on the vote_desc table');
    $db->query('UNLOCK TABLES');

    show_progress('Adding new tables');
    xmb_schema_table('create', 'lang_base');
    xmb_schema_table('create', 'lang_keys');
    xmb_schema_table('create', 'lang_text');

    show_progress('Resetting the schema version number');
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

    show_progress('Beginning schema upgrade to version number 3');

    show_progress('Requesting to lock the logs table');
    $db->query('LOCK TABLES '.X_PREFIX."logs WRITE");

    show_progress('Gathering schema information from the logs table');
    $sql = array();
    $table = 'logs';
    $columns = array(
    'date',
    'tid');
    foreach($columns as $colname) {
        if (!xmb_schema_index_exists($table, $colname)) {
            $sql[] = "ADD INDEX ($colname)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Adding indexes to the logs table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Releasing the lock on the logs table');
    $db->query('UNLOCK TABLES');

    show_progress('Resetting the schema version number');
    $db->query("UPDATE ".X_PREFIX."settings SET schema_version = 3");
}

/**
 * Performs all tasks needed to raise the database schema_version number to 4.
 *
 * @since 1.9.11 (Patch #11)
 */
function upgrade_schema_to_v4() {
    global $db;

    show_progress('Beginning schema upgrade to version number 4');

    show_progress('Requesting to lock the threads table');
    $db->query('LOCK TABLES '.X_PREFIX."threads WRITE");

    show_progress('Gathering schema information from the threads table');
    $sql = array();
    $table = 'threads';
    $columns = array(
    'fid');
    foreach($columns as $colname) {
        if (xmb_schema_index_exists($table, '', $colname)) {
            $sql[] = "DROP INDEX $colname";
        }
    }
    $columns = array(
    'forum_optimize' => 'fid, topped, lastpost');
    foreach($columns as $colname => $coltype) {
        if (!xmb_schema_index_exists($table, '', $colname)) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Deleting/Adding indexes to the threads table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Requesting to lock the posts table');
    $db->query('LOCK TABLES '.X_PREFIX."posts WRITE");

    show_progress('Gathering schema information from the posts table');
    $sql = array();
    $table = 'posts';
    $columns = array(
    'tid');
    foreach($columns as $colname) {
        if (xmb_schema_index_exists($table, '', $colname)) {
            $sql[] = "DROP INDEX $colname";
        }
    }
    $columns = array(
    'thread_optimize' => 'tid, dateline, pid');
    foreach($columns as $colname => $coltype) {
        if (!xmb_schema_index_exists($table, '', $colname)) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Deleting/Adding indexes to the posts table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        $db->query($sql);
    }

    show_progress('Releasing the lock on the posts table');
    $db->query('UNLOCK TABLES');

    show_progress('Resetting the schema version number');
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

    $q = $db->query("SELECT tid, subject, pollopts FROM ".X_PREFIX."threads WHERE pollopts != '' AND pollopts != '1'");
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

    $q = $db->query("SELECT uid, bday, langfile FROM ".X_PREFIX."members WHERE bday != ''");
    while($m = $db->fetch_array($q)) {
        $uid = $m['uid'];

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
	$db->free_result($q);
    $db->query("UPDATE ".X_PREFIX."members SET bday='0000-00-00' WHERE bday=''");
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

/**
 * Recursively deletes all files in the given path.
 *
 * @param string $path
 */
function rmFromDir($path) {
    if (is_dir($path)) {
        $stream = opendir($path);
        while(($file = readdir($stream)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            rmFromDir($path.'/'.$file);
        }
        closedir($stream);
        @rmdir($path);
    } else if (is_file($path)) {
        @unlink($path);
    }
}
?>
