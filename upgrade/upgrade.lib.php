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

if (!defined('IN_CODE')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

require(ROOT.'include/schema.inc.php');

/**
 * Performs all tasks necessary for a normal upgrade.
 */
function xmb_upgrade() {
    global $db, $SETTINGS;

    show_progress('Confirming forums are turned off');
    if ($SETTINGS['bbstatus'] != 'off') {
        if ( (int) $SETTINGS['schema_version'] < 5 ) {
            upgrade_query("UPDATE ".X_PREFIX."settings SET bbstatus = 'off'");
        } else {
            upgrade_query("UPDATE ".X_PREFIX."settings SET value = 'off' WHERE name = 'bbstatus'");
        }
        show_warning('Your forums were turned off by the upgrader.  They will remain unavailable to your members until you reset the Board Status setting in the Admin Panel.');
        trigger_error('Admin attempted upgrade without turning off the board.  Board now turned off.', E_USER_NOTICE);
    }

    show_progress('Selecting the appropriate change set');
    switch ( (int) $SETTINGS['schema_version'] ) {
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
            upgrade_schema_to_v5();
        case 5:
            upgrade_schema_to_v6();
        case 6:
            upgrade_schema_to_v7();
        case 7:
            upgrade_schema_to_v8();
        case 8:
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
    upgrade_query('TRUNCATE TABLE '.X_PREFIX.'templates');

    show_progress('Requesting to lock the templates table');
    upgrade_query('LOCK TABLES '.X_PREFIX.'templates WRITE, '.X_PREFIX.'themes WRITE');

    show_progress('Saving the new templates');
    $values = array();
    foreach($templates as $val) {
        $template = explode("|#*XMB TEMPLATE*#|", $val);
        if (isset($template[1])) {
            $template[1] = addslashes(ltrim($template[1]));
        } else {
            $template[1] = '';
        }
        $db->escape_fast($template[0]);
        $db->escape_fast($template[1]);
        $values[] = "('{$template[0]}', '{$template[1]}')";
    }
    unset($templates);
    if (count($values) > 0) {
        $values = implode(', ', $values);
        upgrade_query("INSERT INTO `".X_PREFIX."templates` (`name`, `template`) VALUES $values");
        upgrade_query("UPDATE `".X_PREFIX."themes` SET version = version + 1");
    }
    unset($values);
    upgrade_query("DELETE FROM `".X_PREFIX."templates` WHERE name=''");

    show_progress('Releasing the lock on the templates table');
    upgrade_query('UNLOCK TABLES');

    show_progress('Deleting the templates.xmb file');
    unlink(ROOT.'templates.xmb');


    show_progress('Checking for new themes');
    $query = upgrade_query("SELECT themeid FROM ".X_PREFIX."themes WHERE name='XMB Davis'");
    if ($db->num_rows($query) == 0 && is_dir(ROOT.'images/davis')) {
        show_progress('Adding Davis as the new default theme');
        upgrade_query("INSERT INTO ".X_PREFIX."themes (`name`,      `bgcolor`, `altbg1`,  `altbg2`,  `link`,    `bordercolor`, `header`,  `headertext`, `top`,       `catcolor`,   `tabletext`, `text`,    `borderwidth`, `tablewidth`, `tablespace`, `font`,                              `fontsize`, `boardimg`, `imgdir`,       `smdir`,          `cattext`) "
                                          ."VALUES ('XMB Davis', 'bg.gif',  '#FFFFFF', '#f4f7f8', '#24404b', '#86a9b6',     '#d3dfe4', '#24404b',    'topbg.gif', 'catbar.gif', '#000000',   '#000000', '1px',         '97%',        '5px',        'Tahoma, Arial, Helvetica, Verdana', '11px',     'logo.gif', 'images/davis', 'images/smilies', '#163c4b');");
        $newTheme = $db->insert_id();
        upgrade_query("UPDATE ".X_PREFIX."settings SET value='$newTheme' WHERE name='theme'");
    }
    $db->free_result($query);
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
 * @since 1.9.11.11
 */
function upgrade_schema_to_v0() {
    global $db, $SETTINGS;

    show_progress('Checking for legacy version tables');

    $schema = array();
    $schema['attachments'] = array('aid', 'pid', 'filename', 'filetype', 'attachment', 'downloads');
    $schema['banned'] = array('ip1', 'ip2', 'ip3', 'ip4', 'dateline', 'id');
    $schema['buddys'] = array('username', 'buddyname');
    $schema['favorites'] = array('tid', 'username', 'type');
    $schema['forums'] = array('type', 'fid', 'name', 'status', 'lastpost', 'moderator', 'displayorder', 'description', 'allowhtml', 'allowsmilies', 'allowbbcode', 'userlist', 'theme', 'posts', 'threads', 'fup', 'postperm', 'allowimgcode', 'attachstatus', 'password');
    $schema['members'] = array('uid', 'username', 'password', 'regdate', 'postnum', 'email', 'site', 'aim', 'status', 'location', 'bio', 'sig', 'showemail', 'timeoffset', 'icq', 'avatar', 'yahoo', 'customstatus', 'theme', 'bday', 'langfile', 'tpp', 'ppp', 'newsletter', 'regip', 'timeformat', 'msn', 'dateformat', 'ban', 'ignoreu2u', 'lastvisit', 'mood', 'pwdate');
    $schema['posts'] = array('fid', 'tid', 'pid', 'author', 'message', 'subject', 'dateline', 'icon', 'usesig', 'useip', 'bbcodeoff', 'smileyoff');
    $schema['ranks'] = array('title', 'posts', 'id', 'stars', 'allowavatars', 'avatarrank');
    $schema['restricted'] = array('name', 'id');
    $schema['settings'] = array('langfile', 'bbname', 'postperpage', 'topicperpage', 'hottopic', 'theme', 'bbstatus', 'whosonlinestatus', 'regstatus', 'bboffreason', 'regviewonly', 'floodctrl', 'memberperpage', 'catsonly', 'hideprivate',
        'emailcheck', 'bbrules', 'bbrulestxt', 'searchstatus', 'faqstatus', 'memliststatus', 'sitename', 'siteurl', 'avastatus', 'u2uquota', 'gzipcompress', 'coppa', 'timeformat', 'adminemail', 'dateformat', 'sigbbcode', 'sightml',
        'reportpost', 'bbinsert', 'smileyinsert', 'doublee', 'smtotal', 'smcols', 'editedby', 'dotfolders', 'attachimgpost', 'todaysposts', 'stats', 'authorstatus', 'tickerstatus', 'tickercontents', 'tickerdelay');
    $schema['smilies'] = array('type', 'code', 'url', 'id');
    $schema['templates'] = array('id', 'name', 'template');
    $schema['themes'] = array('name', 'bgcolor', 'altbg1', 'altbg2', 'link', 'bordercolor', 'header', 'headertext', 'top', 'catcolor', 'tabletext', 'text', 'borderwidth', 'tablewidth', 'tablespace', 'font', 'fontsize', 'boardimg', 'imgdir', 'smdir', 'cattext');
    $schema['threads'] = array('tid', 'fid', 'subject', 'icon', 'lastpost', 'views', 'replies', 'author', 'closed', 'topped', 'pollopts');
    $schema['u2u'] = array('u2uid', 'msgto', 'msgfrom', 'dateline', 'subject', 'message', 'folder', 'readstatus');
    $schema['words'] = array('find', 'replace1', 'id');

    foreach($schema as $table => $columns) {
        $missing = array_diff($columns, xmb_schema_columns_list($table));
        if (!empty($missing)) {
            show_error('Unrecognized Database!  This upgrade utility is not compatible with your version of XMB.  Upgrade halted to prevent damage.');
            trigger_error("Admin attempted upgrade with obsolete database.  Columns missing from $table table: ".implode(', ', $missing), E_USER_ERROR);
        }
    }

    show_progress('Beginning schema upgrade from legacy version');

    show_progress('Requesting to lock the banned table');
    upgrade_query('LOCK TABLES '.X_PREFIX."banned WRITE");

    show_progress('Gathering schema information from the banned table');
    $sql = array();
    $table = 'banned';
    $colname = 'id';
    $coltype = "smallint(6) NOT NULL AUTO_INCREMENT";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Extra']) != 'auto_increment') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }
    $db->free_result($query);

    $columns = array(
    'dateline' => "int(10) NOT NULL default 0");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the buddys table');
    upgrade_query('LOCK TABLES '.X_PREFIX."buddys WRITE");

    show_progress('Gathering schema information from the buddys table');
    $sql = array();
    $table = 'buddys';
    $columns = array(
    'username' => "varchar(32) NOT NULL default ''",
    'buddyname' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the favorites table');
    upgrade_query('LOCK TABLES '.X_PREFIX."favorites WRITE");

    show_progress('Gathering schema information from the favorites table');
    $sql = array();
    $table = 'favorites';
    $columns = array(
    'tid' => "int(10) NOT NULL default 0");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'smallint(6)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'username' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(40)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'type' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the themes table');
    upgrade_query('LOCK TABLES '.X_PREFIX."themes WRITE");

    show_progress('Gathering schema information from the themes table');
    $sql = array();
    $table = 'themes';
    $colname = 'themeid';
    if (xmb_schema_index_exists($table, '', 'PRIMARY') && !xmb_schema_index_exists($table, $colname, 'PRIMARY')) {
        $sql[] = "DROP PRIMARY KEY";
    }

    $columns = array(
    'themeid' => "smallint(3) NOT NULL auto_increment");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'name' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(30)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'boardimg' => "varchar(128) default NULL");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(50)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'dummy');
    foreach($columns as $colname) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the forums table');
    upgrade_query('LOCK TABLES '.
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
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(11)') {
            show_error('Unexpected schema in forums table.  Upgrade aborted to prevent damage.');
            trigger_error('Attempted upgrade on inconsistent schema aborted automatically.', E_USER_ERROR);
        }

        show_progress('Making room for the new values in the postperm column');
        upgrade_query('ALTER TABLE '.X_PREFIX."forums MODIFY COLUMN postperm VARCHAR(11) NOT NULL DEFAULT '0,0,0,0'");

        show_progress('Restructuring the forum permissions data');
        fixPostPerm();   // 1.8 => 1.9.1
        fixForumPerms(); // 1.9.1 => 1.9.9

        // Drop columns now so that any errors later on wont leave both sets of permissions.
        show_progress('Deleting the old permissions data');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        upgrade_query($sql);
        $sql = array();

    } else {

        // Verify new schema is not missing.  Results would be unpredictable.
        $colname = 'postperm';
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'DROP COLUMN '.$colname;
        }
        $db->free_result($query);
    }

    $columns = array(
    'lastpost' => "varchar(54) NOT NULL default ''",
    'password' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(30)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'theme' => "smallint(3) NOT NULL default 0");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(30)') {
            // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
            $sql2 = "UPDATE ".X_PREFIX."$table "
                  . "LEFT JOIN ".X_PREFIX."themes ON ".X_PREFIX."$table.$colname = ".X_PREFIX."themes.name "
                  . "SET ".X_PREFIX."$table.$colname = IFNULL(".X_PREFIX."themes.themeid, 0)";
            upgrade_query($sql2);

            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $colname = 'name';
    $coltype = "varchar(128) NOT NULL default ''";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(50)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $columns = array(
    'posts' => "int(10) NOT NULL default 0",
    'threads' => "int(10) NOT NULL default 0");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the settings table');
    upgrade_query('LOCK TABLES '.
        X_PREFIX.'settings WRITE, '.
        X_PREFIX.'themes READ');

    show_progress('Gathering schema information from the settings table');
    $sql = array();
    $table = 'settings';
    $existing = xmb_schema_columns_list($table);
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
    $obsolete = array_intersect($columns, $existing);
    foreach($obsolete as $colname) {
        $sql[] = 'DROP COLUMN '.$colname;
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
    $missing = array_diff(array_keys($columns), $existing);
    foreach($missing as $colname) {
        $coltype = $columns[$colname];
        $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'adminemail';
    $coltype = "varchar(60) NOT NULL default 'webmaster@domain.ext'";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(32)' || strtolower($row['Type']) == 'varchar(50)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

    $columns = array(
    'langfile' => "varchar(34) NOT NULL default 'English'",
    'bbname' => "varchar(32) NOT NULL default 'Your Forums'");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(50)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }
        $db->free_result($query);
    }

    $columns = array(
    'theme' => "smallint(3) NOT NULL default 1");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(30)') {
            // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
            $sql2 = "UPDATE ".X_PREFIX."$table "
                  . "LEFT JOIN ".X_PREFIX."themes ON ".X_PREFIX."$table.$colname = ".X_PREFIX."themes.name "
                  . "SET ".X_PREFIX."$table.$colname = IFNULL(".X_PREFIX."themes.themeid, 1)";
            upgrade_query($sql2);

            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
            $db->free_result($query);
        }

    $columns = array(
    'dateformat' => "varchar(10) NOT NULL default 'dd-mm-yyyy'");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(20)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'tickerdelay' => "int(6) NOT NULL default 4000");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'char(10)') {
            // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
            upgrade_query("UPDATE ".X_PREFIX."$table SET $colname = '4000' WHERE $colname = '' OR $colname IS NULL");
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
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Null']) == 'yes') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        show_progress('Adding/Deleting/Modifying columns in the settings table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the members table');
    upgrade_query('LOCK TABLES '.
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
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'DROP COLUMN '.$colname;
        }
        $db->free_result($query);
    }

    $colname = 'uid';
    $coltype = "int(12) NOT NULL auto_increment";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'smallint(6)' || strtolower($row['Type']) == 'int(6)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'username';
    $coltype = "varchar(32) NOT NULL default ''";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(25)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'password';
    $coltype = "varchar(32) NOT NULL default ''";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(40)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'regdate';
    $coltype = "int(10) NOT NULL default 0";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'bigint(30)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'postnum';
    $coltype = "MEDIUMINT NOT NULL DEFAULT 0";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'int(10)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'timeoffset';
    $coltype = "DECIMAL(4,2) NOT NULL default 0";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'int(5)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'avatar';
    $coltype = "varchar(120) default NULL";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(90)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'theme';
    $coltype = "smallint(3) NOT NULL default 0";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(30)') {
        // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
        $sql2 = "UPDATE ".X_PREFIX."$table "
              . "LEFT JOIN ".X_PREFIX."themes ON ".X_PREFIX."$table.$colname = ".X_PREFIX."themes.name "
              . "SET ".X_PREFIX."$table.$colname = IFNULL(".X_PREFIX."themes.themeid, 0)";
        upgrade_query($sql2);

        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'regip';
    $coltype = "varchar(15) NOT NULL default ''";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(40)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'lastvisit';
    $coltype = "int(10) unsigned NOT NULL default 0";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(30)' || strtolower($row['Type']) == 'bigint(30)' || strtolower($row['Null']) == 'yes') {
        // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
        upgrade_query("UPDATE ".X_PREFIX."$table SET $colname = '0' WHERE $colname = '' OR $colname IS NULL");
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'mood';
    $coltype = "varchar(128) NOT NULL default 'Not Set'";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(15)' || strtolower($row['Type']) == 'varchar(32)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'pwdate';
    $coltype = "int(10) NOT NULL default 0";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Null']) == 'yes') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }
    $columns = array(
    'bday' => "varchar(10) NOT NULL default '0000-00-00'");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Null']) == 'yes' || strtolower($row['Type']) == 'varchar(50)') {
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
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        upgrade_query($sql);
    }

    // Mimic old function fixPPP()
    show_progress('Fixing missing posts per page values');
    upgrade_query("UPDATE ".X_PREFIX."members SET ppp={$SETTINGS['postperpage']} WHERE ppp=0");
    upgrade_query("UPDATE ".X_PREFIX."members SET tpp={$SETTINGS['topicperpage']} WHERE tpp=0");

    show_progress('Updating outgoing U2U status');
	upgrade_query("UPDATE ".X_PREFIX."members SET saveogu2u='yes'");

    show_progress('Releasing the lock on the members table');
    upgrade_query('UNLOCK TABLES');

    show_progress('Adding new tables for polls');
    xmb_schema_table('create', 'vote_desc');
    xmb_schema_table('create', 'vote_results');
    xmb_schema_table('create', 'vote_voters');

    show_progress('Requesting to lock the polls tables');
    upgrade_query('LOCK TABLES '.
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
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(100)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'views';
    $coltype = "bigint(32) NOT NULL default 0";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'smallint(4)' || strtolower($row['Type']) == 'int(100)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'replies';
    $coltype = "int(10) NOT NULL default 0";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'smallint(5)' || strtolower($row['Type']) == 'int(100)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'lastpost';
    $coltype = "varchar(54) NOT NULL default ''";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(32)' || strtolower($row['Type']) == 'varchar(30)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'pollopts';
    $coltype = "tinyint(1) NOT NULL default 0";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'text') {
        // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
        upgrade_query("UPDATE ".X_PREFIX."$table SET $colname = '0' WHERE $colname = '' OR $colname IS NULL");
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'author';
    $coltype = "varchar(32) NOT NULL default ''";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(40)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'topped';
    $coltype = "tinyint(1) NOT NULL default 0";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the attachments table');
    upgrade_query('LOCK TABLES '.X_PREFIX."attachments WRITE");

    show_progress('Gathering schema information from the attachments table');
    $sql = array();
    $table = 'attachments';
    $columns = array(
    'aid' => "int(10) NOT NULL auto_increment",
    'pid' => "int(10) NOT NULL default 0",
    'downloads' => "int(10) NOT NULL default 0");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
    $filesize_was_missing = FALSE;
    $columns = array(
    'filesize' => "varchar(120) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
            $filesize_was_missing = TRUE;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        show_progress('Modifying columns in the attachments table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        upgrade_query($sql);
    }

    if ($filesize_was_missing) {
        upgrade_query('UPDATE '.X_PREFIX.$table.' SET filesize = LENGTH(attachment)');
    }

    show_progress('Requesting to lock the posts table');
    upgrade_query('LOCK TABLES '.X_PREFIX."posts WRITE");

    show_progress('Gathering schema information from the posts table');
    $sql = array();
    $table = 'posts';
    $columns = array(
    'tid' => "int(10) NOT NULL default '0'");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(40)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $colname = 'subject';
    $coltype = "tinytext NOT NULL";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) == 'varchar(100)') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $colname = 'dateline';
    $coltype = "int(10) NOT NULL default 0";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the ranks table');
    upgrade_query('LOCK TABLES '.X_PREFIX."ranks WRITE");

    show_progress('Gathering schema information from the ranks table');
    $sql = array();
    $table = 'ranks';
    $columns = array(
    'title' => "varchar(100) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        upgrade_query($sql);
    }

    show_progress('Fixing special ranks');
    upgrade_query("DELETE FROM ".X_PREFIX."ranks WHERE title IN ('Moderator', 'Super Moderator', 'Administrator', 'Super Administrator')");
    upgrade_query("INSERT INTO ".X_PREFIX."ranks
     (title,                 posts, stars, allowavatars, avatarrank) VALUES
     ('Moderator',           -1,    6,     'yes',  ''),
     ('Super Moderator',     -1,    7,     'yes',  ''),
     ('Administrator',       -1,    8,     'yes',  ''),
     ('Super Administrator', -1,    9,     'yes',  '')"
    );
    $result = upgrade_query("SELECT title FROM ".X_PREFIX."ranks WHERE posts = 0");
    if ($db->num_rows($result) == 0) {
        $result2 = upgrade_query("SELECT title FROM ".X_PREFIX."ranks WHERE title = 'Newbie'");
        if ($db->num_rows($result2) == 0) {
            upgrade_query("INSERT INTO ".X_PREFIX."ranks
             (title,    posts, stars, allowavatars, avatarrank) VALUES
             ('Newbie', 0,     1,     'yes',  '')"
            );
        } else {
            upgrade_query("UPDATE ".X_PREFIX."ranks SET posts = 0 WHERE title = 'Newbie'");
        }
        $db->free_result($result2);
    }
    $db->free_result($result);

    show_progress('Requesting to lock the templates table');
    upgrade_query('LOCK TABLES '.X_PREFIX."templates WRITE");

    show_progress('Gathering schema information from the templates table');
    $sql = array();
    $table = 'templates';
    $columns = array(
    'name' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the u2u table');
    upgrade_query('LOCK TABLES '.X_PREFIX."u2u WRITE");

    $upgrade_u2u = FALSE;

    show_progress('Gathering schema information from the u2u table');
    $sql = array();
    $table = 'u2u';
    $columns = array(
    'u2uid' => "bigint(10) NOT NULL auto_increment");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'smallint(6)' || strtolower($row['Type']) == 'int(6)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'msgto' => "varchar(32) NOT NULL default ''",
    'msgfrom' => "varchar(32) NOT NULL default ''",
    'folder' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(40)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'dateline' => "int(10) NOT NULL default 0");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'bigint(30)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'subject' => "varchar(64) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
            upgrade_query($sql);
        }

        $sql = array();

        // Mimic old function upgradeU2U() but with fewer queries
        show_progress('Upgrading U2Us');
        upgrade_query("UPDATE ".X_PREFIX."$table SET type='incoming', owner=msgto WHERE folder='inbox'");
        upgrade_query("UPDATE ".X_PREFIX."$table SET type='outgoing', owner=msgfrom WHERE folder='outbox'");
        upgrade_query("UPDATE ".X_PREFIX."$table SET type='incoming', owner=msgfrom WHERE folder != 'outbox' AND folder != 'inbox'");
        upgrade_query("UPDATE ".X_PREFIX."$table SET readstatus='no' WHERE readstatus=''");

        $colname = 'new';
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            upgrade_query("UPDATE ".X_PREFIX."$table SET sentstatus='yes' WHERE new=''");
        }
    }

    $columns = array(
    'readstatus' => "set('yes','no') NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        $row = $db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(3)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $columns = array(
    'new');
    foreach($columns as $colname) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the words table');
    upgrade_query('LOCK TABLES '.X_PREFIX."words WRITE");

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
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the restricted table');
    upgrade_query('LOCK TABLES '.X_PREFIX."restricted WRITE");

    show_progress('Gathering schema information from the restricted table');
    $sql = array();
    $table = 'restricted';
    $columns = array(
    'name' => "varchar(32) NOT NULL default ''");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        show_progress('Modifying columns in the restricted table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        upgrade_query($sql);
    }

    show_progress('Releasing the lock on the restricted table');
    upgrade_query('UNLOCK TABLES');

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
 * @since 1.9.11 Beta 3
 */
function upgrade_schema_to_v2() {
    global $db;

    show_progress('Beginning schema upgrade to version number 2');

    show_progress('Requesting to lock the settings table');
    upgrade_query('LOCK TABLES '.X_PREFIX."settings WRITE");

    show_progress('Gathering schema information from the settings table');
    $sql = array();
    $table = 'settings';
    $columns = array(
    'boardurl');
    foreach($columns as $colname) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
    'schema_version' => "TINYINT UNSIGNED NOT NULL DEFAULT 1");
    $missing = array_diff(array_keys($columns), xmb_schema_columns_list($table));
    foreach($missing as $colname) {
        $coltype = $columns[$colname];
        $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
    }

    if (count($sql) > 0) {
        show_progress('Adding/Deleting columns in the settings table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the attachments table');
    upgrade_query('LOCK TABLES '.X_PREFIX."attachments WRITE");

    show_progress('Gathering schema information from the attachments table');
    $sql = array();
    $table = 'attachments';
    $columns = array(
    'tid');
    foreach($columns as $colname) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
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
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the members table');
    upgrade_query('LOCK TABLES '.X_PREFIX."members WRITE");

    show_progress('Gathering schema information from the members table');
    $sql = array();
    $table = 'members';
    $columns = array(
    'u2ualert' => "TINYINT NOT NULL DEFAULT '0'");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }
    $columns = array(
    'postnum' => "postnum MEDIUMINT NOT NULL DEFAULT 0");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'CHANGE '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        show_progress('Adding/Deleting columns in the members table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the ranks table');
    upgrade_query('LOCK TABLES '.X_PREFIX."ranks WRITE");

    show_progress('Gathering schema information from the ranks table');
    $sql = array();
    $table = 'ranks';
    $columns = array(
    'posts' => "posts MEDIUMINT DEFAULT 0");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'CHANGE '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        show_progress('Adding/Deleting columns in the ranks table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the themes table');
    upgrade_query('LOCK TABLES '.X_PREFIX."themes WRITE");

    show_progress('Gathering schema information from the themes table');
    $sql = array();
    $table = 'themes';
    $columns = array(
    'admdir' => "VARCHAR( 120 ) NOT NULL DEFAULT 'images/admin'");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        show_progress('Adding/Deleting columns in the themes table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the vote_desc table');
    upgrade_query('LOCK TABLES '.X_PREFIX."vote_desc WRITE");

    show_progress('Gathering schema information from the vote_desc table');
    $sql = array();
    $table = 'vote_desc';
    $columns = array(
    'topic_id' => "topic_id INT UNSIGNED NOT NULL");
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'CHANGE '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        show_progress('Adding/Deleting columns in the vote_desc table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        upgrade_query($sql);
    }

    show_progress('Releasing the lock on the vote_desc table');
    upgrade_query('UNLOCK TABLES');

    show_progress('Adding new tables');
    xmb_schema_table('create', 'lang_base');
    xmb_schema_table('create', 'lang_keys');
    xmb_schema_table('create', 'lang_text');

    show_progress('Resetting the schema version number');
    upgrade_query("UPDATE ".X_PREFIX."settings SET schema_version = 2");
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
    upgrade_query('LOCK TABLES '.X_PREFIX."logs WRITE");

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
        upgrade_query($sql);
    }

    show_progress('Releasing the lock on the logs table');
    upgrade_query('UNLOCK TABLES');

    show_progress('Resetting the schema version number');
    upgrade_query("UPDATE ".X_PREFIX."settings SET schema_version = 3");
}

/**
 * Performs all tasks needed to raise the database schema_version number to 4.
 *
 * @since 1.9.11.11
 */
function upgrade_schema_to_v4() {
    global $db;

    show_progress('Beginning schema upgrade to version number 4');

    show_progress('Requesting to lock the threads table');
    upgrade_query('LOCK TABLES '.X_PREFIX."threads WRITE");

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
        upgrade_query($sql);
    }

    show_progress('Requesting to lock the posts table');
    upgrade_query('LOCK TABLES '.X_PREFIX."posts WRITE");

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
        upgrade_query($sql);
    }

    show_progress('Releasing the lock on the posts table');
    upgrade_query('UNLOCK TABLES');

    show_progress('Resetting the schema version number');
    upgrade_query("UPDATE ".X_PREFIX."settings SET schema_version = 4");
}

/**
 * Performs all tasks needed to raise the database schema_version number to 5.
 *
 * @since 1.9.12
 */
function upgrade_schema_to_v5() {
    global $db;

    show_progress('Requesting to lock the members table');
    upgrade_query('LOCK TABLES '.X_PREFIX."members WRITE");

    show_progress('Gathering schema information from the members table');
    $sql = [];
    $table = 'members';
    $columns = [
    'bad_login_date' => "int(10) unsigned NOT NULL default 0",
    'bad_login_count' => "int(10) unsigned NOT NULL default 0",
    'bad_session_date' => "int(10) unsigned NOT NULL default 0",
    'bad_session_count' => "int(10) unsigned NOT NULL default 0",
    'sub_each_post' => "varchar(3) NOT NULL default 'no'",
    'waiting_for_mod' => "varchar(3) NOT NULL default 'no'",
    ];
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    $colname = 'lastvisit';
    $coltype = "int(10) unsigned NOT NULL default 0";
    $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
    $row = $db->fetch_array($query);
    if (strtolower($row['Type']) != 'int(10) unsigned') {
        $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
    }

    $columns = [
    'invisible' => 'invisible',
    'password' => 'password',
    'username' => 'username',
    ];
    foreach($columns as $colname => $coltype) {
        if (xmb_schema_index_exists($table, $coltype, $colname)) {
            $sql[] = "DROP INDEX $colname";
        }
    }

    if ( ! xmb_schema_index_exists( $table, 'username', 'userunique' ) ) {
        show_progress('Removing duplicate username records');
        $query = upgrade_query('SELECT username, MIN(uid) AS firstuser FROM '.X_PREFIX.$table.' GROUP BY username HAVING COUNT(*) > 1');
        while( $dupe = $db->fetch_array( $query ) ) {
            $name = $db->escape( $dupe['username'] );
            $id = $dupe['firstuser'];
            upgrade_query( 'DELETE FROM '.X_PREFIX.$table." WHERE username = '$name' AND uid != $id" );
        }
        $sql[] = "ADD UNIQUE INDEX `userunique` (`username`)";
    }

    $columns = [
    'lastvisit' => 'lastvisit',
    ];
    foreach($columns as $colname => $coltype) {
        if (!xmb_schema_index_exists($table, $coltype, $colname)) {
            $sql[] = "ADD INDEX $colname ($coltype)";
        }
    }

    if (count($sql) > 0) {
        show_progress('Adding/Deleting columns in the members table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        upgrade_query($sql);
    }

    $table = 'settings';
    show_progress('Requesting to lock the settings table');
    upgrade_query('LOCK TABLES '.X_PREFIX."$table WRITE");

    show_progress('Reading the settings table data');
    $query = upgrade_query('SELECT * FROM '.X_PREFIX.$table);
    $settings = $db->fetch_array( $query );
    $settings['google_captcha'] = 'off';
    $settings['google_captcha_sitekey'] = '';
    $settings['google_captcha_secret'] = '';
    $settings['hide_banned'] = 'off';
    $settings['quarantine_new_users'] = 'off';
    $settings['show_logs_in_threads'] = 'off';
    $settings['tickercode'] = 'html';
    unset( $settings['sightml'] );

    show_progress('Replacing the settings table');
    xmb_schema_table( 'overwrite', 'settings' );
    $sql = [];
    foreach( $settings as $name => $value ) {
        $db->escape_fast( $value );
        $sql[] = "('$name', '$value')";
    }
    upgrade_query('INSERT INTO '.X_PREFIX."settings (name, value) VALUES ". implode( ',', $sql ));

    show_progress('Requesting to lock the forums table');
    upgrade_query('LOCK TABLES '.X_PREFIX."forums WRITE");

    show_progress('Gathering schema information from the forums table');
    $sql = [];
    $table = 'forums';
    $columns = [
    'allowhtml',
    ];
    foreach($columns as $colname) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'DROP COLUMN '.$colname;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        show_progress('Deleting columns in the forums table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        upgrade_query($sql);
    }

    show_progress('Releasing the lock on the forums table');
    upgrade_query('UNLOCK TABLES');

    show_progress('Adding new tables');
    xmb_schema_table('create', 'sessions');
    xmb_schema_table('create', 'tokens');

    show_progress('Resetting the schema version number');
    upgrade_query("UPDATE ".X_PREFIX."settings SET value = '5' WHERE name = 'schema_version'");
}

/**
 * Performs all tasks needed to raise the database schema_version number to 6.
 *
 * @since 1.9.12
 */
function upgrade_schema_to_v6() {
    global $db;

    show_progress('Requesting to lock the themes table');
    upgrade_query('LOCK TABLES '.X_PREFIX."themes WRITE");

    show_progress('Gathering schema information from the themes table');
    $sql = [];
    $table = 'themes';
    $columns = [
    'version' => "int(10) unsigned NOT NULL default 0",
    ];
    foreach($columns as $colname => $coltype) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 0) {
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        show_progress('Adding columns to the themes table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        upgrade_query($sql);
    }

    show_progress('Releasing the lock on the themes table');
    upgrade_query('UNLOCK TABLES');

    show_progress('Emptying the captcha table');
    upgrade_query('TRUNCATE TABLE '.X_PREFIX."captchaimages");

    show_progress('Resetting the schema version number');
    upgrade_query("UPDATE ".X_PREFIX."settings SET value = '6' WHERE name = 'schema_version'");
}

/**
 * Performs all tasks needed to raise the database schema_version number to 7.
 *
 * @since 1.9.12
 */
function upgrade_schema_to_v7() {
    global $db;

    show_progress('Adding new tables');
    xmb_schema_table('create', 'hold_attachments');
    xmb_schema_table('create', 'hold_favorites');
    xmb_schema_table('create', 'hold_posts');
    xmb_schema_table('create', 'hold_threads');
    xmb_schema_table('create', 'hold_vote_desc');
    xmb_schema_table('create', 'hold_vote_results');

    show_progress('Requesting to lock the vote_desc table');
    upgrade_query('LOCK TABLES '.X_PREFIX."vote_desc WRITE");

    show_progress('Gathering schema information from the vote_desc table');
    $sql = [];
    $table = 'vote_desc';
    $columns = [
    'vote_length',
    'vote_start',
    'vote_text',
    ];
    foreach($columns as $colname) {
        $query = upgrade_query('DESCRIBE '.X_PREFIX.$table.' '.$colname);
        if ($db->num_rows($query) == 1) {
            $sql[] = 'DROP COLUMN '.$colname;
        }
        $db->free_result($query);
    }

    if (count($sql) > 0) {
        show_progress('Deleting columns in the vote_desc table');
        $sql = 'ALTER TABLE '.X_PREFIX.$table.' '.implode(', ', $sql);
        upgrade_query($sql);
    }

    show_progress('Releasing the lock on the vote_desc table');
    upgrade_query('UNLOCK TABLES');

    show_progress('Resetting the schema version number');
    upgrade_query("UPDATE ".X_PREFIX."settings SET value = '7' WHERE name = 'schema_version'");
}

/**
 * Performs all tasks needed to raise the database schema_version number to 8.
 *
 * @since 1.9.12
 */
function upgrade_schema_to_v8() {
    global $db;

    show_progress('Gathering schema information from the settings table');
    $table = 'settings';
    $query = upgrade_query('SELECT value FROM '.X_PREFIX.$table.' WHERE name = "images_https_only"');
    if ( $db->num_rows( $query ) != 1 ) {
        show_progress('Adding data to the settings table');
        upgrade_query('INSERT INTO '.X_PREFIX.$table.' SET value = "off", name = "images_https_only"');
    }

    show_progress('Resetting the schema version number');
    upgrade_query("UPDATE ".X_PREFIX."settings SET value = '8' WHERE name = 'schema_version'");
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
    $q = upgrade_query("SELECT fid, private, userlist, postperm, guestposting, pollstatus FROM ".X_PREFIX."forums WHERE (type='forum' OR type='sub')");
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

        upgrade_query("UPDATE ".X_PREFIX."forums SET postperm='".implode(',', $newFormat)."' WHERE fid=$fid");
    }
}

/**
 * Convert threads.pollopts text column into relational vote_ tables.
 *
 * @since 1.9.8
 */
function fixPolls() {
    global $db;

    $q = upgrade_query("SHOW COLUMNS FROM ".X_PREFIX."threads LIKE 'pollopts'");
    $result = $db->fetch_array($q);
    $db->free_result($q);

    if (FALSE === $result) return; // Unexpected condition, do not attempt to use fixPolls().
    if (FALSE !== strpos(strtolower($result['Type']), 'int')) return; // Schema already at 1.9.8+

    $q = upgrade_query("SELECT tid, subject, pollopts FROM ".X_PREFIX."threads WHERE pollopts != '' AND pollopts != '1'");
    while($thread = $db->fetch_array($q)) {
        // Poll titles are historically unslashed, but thread titles are double-slashed.
        $thread['subject'] = stripslashes($thread['subject']);
        $db->escape_fast($thread['subject']);

        upgrade_query("INSERT INTO ".X_PREFIX."vote_desc SET `topic_id` = {$thread['tid']}");
        $poll_id = $db->insert_id();

        $options = explode("#|#", $thread['pollopts']);
        $num_options = count($options) - 1;

        if (0 == $num_options) continue; // Sanity check.  Remember, 1 != '' evaluates to TRUE in MySQL.

        $voters = explode('    ', trim($options[$num_options]));

        if (1 == count($voters) && strlen($voters[0]) < 3) {
            // The most likely values for $options[$num_options] are '' and '1'.  Treat them equivalent to null.
        } else {
            $name = array();
            foreach($voters as $v) {
                $name[] = $db->escape(trim($v));
            }
            $name = "'".implode("', '", $name)."'";
            $query = upgrade_query("SELECT uid FROM ".X_PREFIX."members WHERE username IN ($name)");
            $values = array();
            while($u = $db->fetch_array($query)) {
                $values[] = "($poll_id, {$u['uid']})";
            }
            $db->free_result($query);
            if (count($values) > 0) {
                upgrade_query("INSERT INTO ".X_PREFIX."vote_voters (`vote_id`, `vote_user_id`) VALUES ".implode(',', $values));
            }
        }

        $values = array();
        for($i = 0; $i < $num_options; $i++) {
            $bit = explode('||~|~||', $options[$i]);
            $option_name = $db->escape(trim($bit[0]));
            $num_votes = (int) trim($bit[1]);
            $values[] = "($poll_id, ".($i+1).", '$option_name', $num_votes)";
        }
        upgrade_query("INSERT INTO ".X_PREFIX."vote_results (`vote_id`, `vote_option_id`, `vote_option_text`, `vote_result`) VALUES ".implode(',', $values));
    }
    $db->free_result($q);
    upgrade_query("UPDATE ".X_PREFIX."threads SET pollopts='1' WHERE pollopts != ''");
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

    $q = upgrade_query("SELECT uid, bday, langfile FROM ".X_PREFIX."members WHERE bday != ''");
    while($m = $db->fetch_array($q)) {
        $uid = $m['uid'];

        // check if the birthday is already in proper format
        $parts = explode('-', $m['bday']);
        if (count($parts) == 3 && is_numeric($parts[0]) && is_numeric($parts[1]) && is_numeric($parts[2])) {
            continue;
        }

        $lang = array();

        if (!isset($cachedLanguages[$m['langfile']])) {
            if (!file_exists(ROOT.'lang/'.$m['langfile'].'.lang.php')) {
                // Re-try in case the old file was named english.lang.php instead of English.lang.php for some reason.
                $test = $m['langfile'];
                $test[0] = strtoupper($test[0]);
                if (isset($cachedLanguages[$test])) {
                    $m['langfile'] = $test;
                } elseif (file_exists(ROOT.'lang/'.$test.'.lang.php')) {
                    upgrade_query("UPDATE ".X_PREFIX."members SET langfile='$test' WHERE langfile = '{$m['langfile']}'");
                    $m['langfile'] = $test;
                } else {
                    show_error('A needed file is missing for date translation: '.ROOT.'lang/'.$m['langfile'].'.lang.php.  Upgrade halted to prevent damage.');
                    trigger_error('fixBirthdays() stopped the upgrade because language "'.$m['langfile'].'" was missing.', E_USER_ERROR);
                }
            }
            if (!isset($cachedLanguages[$m['langfile']])) {
                $old_error_level = error_reporting();
                error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR | E_USER_ERROR);
                require ROOT.'lang/'.$m['langfile'].'.lang.php';
                error_reporting($old_error_level);
                $cachedLanguages[$m['langfile']] = $lang;
            }
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
            upgrade_query("UPDATE ".X_PREFIX."members SET bday='".iso8601_date($year, $month, $day)."' WHERE uid=$uid");
        } else {
            upgrade_query("UPDATE ".X_PREFIX."members SET bday='0000-00-00' WHERE uid=$uid");
        }
    }
	$db->free_result($q);
    upgrade_query("UPDATE ".X_PREFIX."members SET bday='0000-00-00' WHERE bday=''");
}

/**
 * Recalculates the value of every field in the forums.postperm column.
 *
 * @since 1.9.1
 */
function fixPostPerm() {
    global $db;

	$query = upgrade_query("SELECT fid, private, postperm, guestposting FROM ".X_PREFIX."forums WHERE type != 'group'");
	while ( $forum = $db->fetch_array($query) ) {
		$update = false;
		$pp = trim($forum['postperm']);
		if ( strlen($pp) > 0 && strpos($pp, '|') === false ) {
			$update = true;
			$forum['postperm'] = $pp . '|' . $pp;	// make the postperm the same for thread and reply
		}
		if ( $forum['guestposting'] != 'on' && $forum['guestposting'] != 'off' ) {
			$forum['guestposting'] = 'off';
			$update = true;
		}
		if ( $forum['private'] == '' ) {
			$forum['private'] = '1';	// by default, forums are not private.
			$update = true;
		}
		if ( $update ) {
			upgrade_query("UPDATE ".X_PREFIX."forums SET postperm='{$forum['postperm']}', guestposting='{$forum['guestposting']}', private='{$forum['private']}' WHERE fid={$forum['fid']}");
		}
	}
	$db->free_result($query);
}

/**
 * Abstracts database queries for better error handling.
 *
 * @since 1.9.12
 * @param string $sql
 * @return mixed Result of $db->query()
 */
function upgrade_query( $sql ) {
	global $db;
	
	$result = $db->query( $sql, false );
	
	if ( false === $result ) {
		$error = '<pre>MySQL encountered the following error: '.cdataOut( $db->error() )."\n\n";
		if ( '' != $sql ) {
			$error .= 'In the following query: <em>'.cdataOut( $sql ).'</em>';
		}
		$error .= '</pre>';
		
		show_error( $error );
		exit;
	}
	
	return $result;
}

return;