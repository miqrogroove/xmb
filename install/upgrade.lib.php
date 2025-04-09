<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
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
 */

declare(strict_types=1);

namespace XMB;

/**
 * Upgrade migration logic.
 *
 * @since 1.10.00
 */
class Upgrade
{
    public function __construct(private DBStuff $db, private UpgradeOutput $show, private Schema $schema, private Variables $vars)
    {
        // Property promotion.
    }

    /**
     * Performs all tasks necessary for a normal upgrade.
     *
     * @since 1.9.11.11
     */
    function xmb_upgrade()
    {
        $SETTINGS = &$this->vars->settings;

        $this->show->progress('Confirming forums are turned off');
        if ($SETTINGS['bbstatus'] != 'off') {
            if ((int) $SETTINGS['schema_version'] < 5) {
                $this->upgrade_query("UPDATE " . $this->vars->tablepre . "settings SET bbstatus = 'off'");
            } else {
                $this->upgrade_query("UPDATE " . $this->vars->tablepre . "settings SET value = 'off' WHERE name = 'bbstatus'");
            }
            $this->show->warning('Your forums were turned off by the upgrader.  They will remain unavailable to your members until you reset the Board Status setting in the Admin Panel.');
            trigger_error('Admin attempted upgrade without turning off the board.  Board now turned off.', E_USER_NOTICE);
        }

        $this->show->progress('Selecting the appropriate change set');
        switch ((int) $SETTINGS['schema_version']) {
            case $this->schema::VER:
                $this->show->progress('Database schema is current, skipping ALTER commands');
                break;
            case 0:
                //Ambiguous case.  Attempt a backward-compatible schema change.
                $this->upgrade_schema_to_v0();
                //No breaks.
            case 1:
                $this->upgrade_schema_to_v2();
            case 2:
                $this->upgrade_schema_to_v3();
            case 3:
                $this->upgrade_schema_to_v4();
            case 4:
                $this->upgrade_schema_to_v5();
            case 5:
                $this->upgrade_schema_to_v6();
            case 6:
                $this->upgrade_schema_to_v7();
            case 7:
                $this->upgrade_schema_to_v8();
            case 8:
                $this->upgrade_schema_to_v9();
            case 9:
                $this->upgrade_schema_to_v10();
                break;
            case 10:
                //Future use. Break only before case default.
                break;
            default:
                $this->show->error('Unrecognized Database!  This upgrade utility is not compatible with your version of XMB.  Upgrade halted to prevent damage.');
                throw new Exception('Admin attempted upgrade with obsolete upgrade utility.');
                break;
        }
        $this->show->progress('Database schema is now current');

        $this->show->progress('Checking for new themes');
        if ((int) $SETTINGS['schema_version'] < 3) {
            $query = $this->upgrade_query("SELECT themeid FROM " . $this->vars->tablepre . "themes WHERE name='XMB Davis'");
            if ($this->db->num_rows($query) == 0 && is_dir(XMB_ROOT.'images/davis')) {
                $this->show->progress('Adding Davis as the new default theme');
                $this->upgrade_query("INSERT INTO " . $this->vars->tablepre . "themes (`name`,      `bgcolor`, `altbg1`,  `altbg2`,  `link`,    `bordercolor`, `header`,  `headertext`, `top`,       `catcolor`,   `tabletext`, `text`,    `borderwidth`, `tablewidth`, `tablespace`, `font`,                              `fontsize`, `boardimg`, `imgdir`,       `smdir`,          `cattext`) "
                                                     ."VALUES ('XMB Davis', 'bg.gif',  '#FFFFFF', '#f4f7f8', '#24404b', '#86a9b6',     '#d3dfe4', '#24404b',    'topbg.gif', 'catbar.gif', '#000000',   '#000000', '1px',         '97%',        '5px',        'Tahoma, Arial, Helvetica, Verdana', '11px',     'logo.gif', 'images/davis', 'images/smilies', '#163c4b');");
                $newTheme = $this->db->insert_id();
                $this->upgrade_query("UPDATE " . $this->vars->tablepre . "settings SET value='$newTheme' WHERE name='theme'");
            }
            $this->db->free_result($query);
        }
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
    function upgrade_schema_to_v0()
    {
        $this->show->progress('Checking for legacy version tables');

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

        foreach ($schema as $table => $columns) {
            $missing = array_diff($columns, $this->schema->listColumns($table));
            if (!empty($missing)) {
                $this->show->error('Unrecognized Database!  This upgrade utility is not compatible with your version of XMB.  Upgrade halted to prevent damage.');
                throw new Exception("Admin attempted upgrade with obsolete database.  Columns missing from $table table: ".implode(', ', $missing));
            }
        }

        $this->show->progress('Beginning schema upgrade from legacy version');

        $this->show->progress('Requesting to lock the banned table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "banned WRITE");

        $this->show->progress('Gathering schema information from the banned table');
        $sql = array();
        $table = 'banned';
        $colname = 'id';
        $coltype = "smallint NOT NULL AUTO_INCREMENT";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Extra']) != 'auto_increment') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
        $this->db->free_result($query);

        $columns = array(
        'dateline' => "int NOT NULL DEFAULT 0");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            // Prior to MySQL 8.0, the column type was always "bigint(30)".
            // From v8.0 the integer width is deprecated and DESCRIBE always returns "bigint" only.
            if (stripos($row['Type'], 'bigint') === 0) {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'ip1',
        'ip2',
        'ip3',
        'ip4');
        foreach ($columns as $colname) {
            if (! $this->schema->indexExists($table, $colname)) {
                $sql[] = "ADD INDEX ($colname)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Modifying columns in the banned table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the buddys table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "buddys WRITE");

        $this->show->progress('Gathering schema information from the buddys table');
        $sql = array();
        $table = 'buddys';
        $columns = array(
        'username' => "varchar(32) NOT NULL DEFAULT ''",
        'buddyname' => "varchar(32) NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(40)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'username' => 'username (8)');
        foreach ($columns as $colname => $coltype) {
            if (! $this->schema->indexExists($table, $colname)) {
                $sql[] = "ADD INDEX $colname ($coltype)";
            } elseif (! $this->schema->indexExists($table, $colname, '', '8')) {
                $sql[] = "DROP INDEX $colname";
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Modifying columns in the buddys table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the favorites table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "favorites WRITE");

        $this->show->progress('Gathering schema information from the favorites table');
        $sql = array();
        $table = 'favorites';
        $columns = array(
        'tid' => "int NOT NULL DEFAULT 0");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (stripos($row['Type'], 'smallint') === 0) {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'username' => "varchar(32) NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(40)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'type' => "varchar(32) NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(20)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'tid');
        foreach ($columns as $colname) {
            if (! $this->schema->indexExists($table, $colname)) {
                $sql[] = "ADD INDEX ($colname)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Modifying columns in the favorites table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the themes table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "themes WRITE");

        $this->show->progress('Gathering schema information from the themes table');
        $sql = array();
        $table = 'themes';
        $colname = 'themeid';
        if ($this->schema->indexExists($table, '', 'PRIMARY') && ! $this->schema->indexExists($table, $colname, 'PRIMARY')) {
            $sql[] = "DROP PRIMARY KEY";
        }

        $columns = array(
        'themeid' => "smallint NOT NULL auto_increment");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 0) {
                $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'name' => "varchar(32) NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(30)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'boardimg' => "varchar(128) DEFAULT NULL");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(50)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'dummy');
        foreach ($columns as $colname) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 1) {
                $sql[] = 'DROP COLUMN '.$colname;
            }
            $this->db->free_result($query);
        }

        $colname = 'themeid';
        if (! $this->schema->indexExists($table, $colname, 'PRIMARY')) {
            $sql[] = "ADD PRIMARY KEY ($colname)";
        }

        $columns = array(
        'name');
        foreach ($columns as $colname) {
            if (! $this->schema->indexExists($table, '', $colname)) {
                $sql[] = "ADD INDEX ($colname)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Modifying columns in the themes table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the forums table');
        $this->upgrade_query('LOCK TABLES '.
            X_PREFIX.'forums WRITE, '.
            X_PREFIX.'themes READ');

        $upgrade_permissions = TRUE;

        $this->show->progress('Gathering schema information from the forums table');
        $sql = array();
        $table = 'forums';
        $columns = array(
        'private',
        'pollstatus',
        'guestposting');
        foreach ($columns as $colname) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 1) {
                $sql[] = 'DROP COLUMN '.$colname;
            } else {
                $upgrade_permissions = FALSE;
            }
            $this->db->free_result($query);
        }

        if ($upgrade_permissions) {

            // Verify new schema is not coexisting with the old one.  Results would be unpredictable.
            $colname = 'postperm';
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(11)') {
                $this->show->error('Unexpected schema in forums table.  Upgrade aborted to prevent damage.');
                throw new Exception('Attempted upgrade on inconsistent schema aborted automatically.');
            }

            $this->show->progress('Making room for the new values in the postperm column');
            $this->upgrade_query('ALTER TABLE ' . $this->vars->tablepre . "forums MODIFY COLUMN postperm VARCHAR(11) NOT NULL DEFAULT '0,0,0,0'");

            $this->show->progress('Restructuring the forum permissions data');
            $this->fixPostPerm();   // 1.8 => 1.9.1
            $this->fixForumPerms(); // 1.9.1 => 1.9.9

            // Drop columns now so that any errors later on won't leave both sets of permissions.
            $this->show->progress('Deleting the old permissions data');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
            $sql = array();

        } else {

            // Verify new schema is not missing.  Results would be unpredictable.
            $colname = 'postperm';
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) != 'varchar(11)') {
                $this->show->error('Unexpected schema in forums table.  Upgrade aborted to prevent damage.');
                throw new Exception('Attempted upgrade on inconsistent schema aborted automatically.');
            }
        }

        $columns = array(
        'mt_status',
        'mt_open',
        'mt_close');
        foreach ($columns as $colname) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 1) {
                $sql[] = 'DROP COLUMN '.$colname;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'lastpost' => "varchar(54) NOT NULL DEFAULT ''",
        'password' => "varchar(32) NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(30)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'theme' => "smallint NOT NULL DEFAULT 0");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(30)') {
                // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
                $sql2 = "UPDATE " . $this->vars->tablepre . "$table "
                      . "LEFT JOIN " . $this->vars->tablepre . "themes ON " . $this->vars->tablepre . "$table.$colname = " . $this->vars->tablepre . "themes.name "
                      . "SET " . $this->vars->tablepre . "$table.$colname = IFNULL(" . $this->vars->tablepre . "themes.themeid, 0)";
                $this->upgrade_query($sql2);

                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $colname = 'name';
        $coltype = "varchar(128) NOT NULL DEFAULT ''";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(50)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $columns = array(
        'fup',
        'type',
        'displayorder',
        'status');
        foreach ($columns as $colname) {
            if (! $this->schema->indexExists($table, $colname)) {
                $sql[] = "ADD INDEX ($colname)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Deleting/Modifying columns in the forums table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the settings table');
        $this->upgrade_query('LOCK TABLES '.
            X_PREFIX.'settings WRITE, '.
            X_PREFIX.'themes READ');

        $this->show->progress('Gathering schema information from the settings table');
        $sql = array();
        $table = 'settings';
        $existing = $this->schema->listColumns($table);
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
        foreach ($obsolete as $colname) {
            $sql[] = 'DROP COLUMN '.$colname;
        }

        $columns = array(
        'addtime' => "DECIMAL(4,2) NOT NULL DEFAULT 0",
        'max_avatar_size' => "varchar(9) NOT NULL DEFAULT '100x100'",
        'footer_options' => "varchar(45) NOT NULL DEFAULT 'queries-phpsql-loadtimes-totaltime'",
        'space_cats' => "char(3) NOT NULL DEFAULT 'no'",
        'spellcheck' => "char(3) NOT NULL DEFAULT 'off'",
        'allowrankedit' => "char(3) NOT NULL DEFAULT 'on'",
        'notifyonreg' => "SET('off','u2u','email') NOT NULL DEFAULT 'off'",
        'subject_in_title' => "char(3) NOT NULL DEFAULT ''",
        'def_tz' => "decimal(4,2) NOT NULL DEFAULT '0.00'",
        'indexshowbar' => "tinyint NOT NULL DEFAULT 2",
        'resetsigs' => "char(3) NOT NULL DEFAULT 'off'",
        'pruneusers' => "smallint NOT NULL DEFAULT 0",
        'ipreg' => "char(3) NOT NULL DEFAULT 'on'",
        'maxdayreg' => "smallint UNSIGNED NOT NULL DEFAULT 25",
        'maxattachsize' => "int UNSIGNED NOT NULL DEFAULT 256000",
        'captcha_status' => "set('on','off') NOT NULL DEFAULT 'on'",
        'captcha_reg_status' => "set('on','off') NOT NULL DEFAULT 'on'",
        'captcha_post_status' => "set('on','off') NOT NULL DEFAULT 'on'",
        'captcha_code_charset' => "varchar(128) NOT NULL DEFAULT 'A-Z'",
        'captcha_code_length' => "int NOT NULL DEFAULT '8'",
        'captcha_code_casesensitive' => "set('on','off') NOT NULL DEFAULT 'off'",
        'captcha_code_shadow' => "set('on','off') NOT NULL DEFAULT 'off'",
        'captcha_image_type' => "varchar(4) NOT NULL DEFAULT 'png'",
        'captcha_image_width' => "int NOT NULL DEFAULT '250'",
        'captcha_image_height' => "int NOT NULL DEFAULT '50'",
        'captcha_image_bg' => "varchar(128) NOT NULL DEFAULT ''",
        'captcha_image_dots' => "int NOT NULL DEFAULT '0'",
        'captcha_image_lines' => "int NOT NULL DEFAULT '70'",
        'captcha_image_fonts' => "varchar(128) NOT NULL DEFAULT ''",
        'captcha_image_minfont' => "int NOT NULL DEFAULT '16'",
        'captcha_image_maxfont' => "int NOT NULL DEFAULT '25'",
        'captcha_image_color' => "set('on','off') NOT NULL DEFAULT 'off'",
        'showsubforums' => "set('on','off') NOT NULL DEFAULT 'off'",
        'regoptional' => "set('on','off') NOT NULL DEFAULT 'off'",
        'quickreply_status' => "set('on','off') NOT NULL DEFAULT 'on'",
        'quickjump_status' => "set('on','off') NOT NULL DEFAULT 'on'",
        'index_stats' => "set('on','off') NOT NULL DEFAULT 'on'",
        'onlinetodaycount' => "smallint NOT NULL DEFAULT '50'",
        'onlinetoday_status' => "set('on','off') NOT NULL DEFAULT 'on'");
        $missing = array_diff(array_keys($columns), $existing);
        foreach ($missing as $colname) {
            $coltype = $columns[$colname];
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'adminemail';
        $coltype = "varchar(60) NOT NULL DEFAULT 'webmaster@domain.ext'";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(32)' || strtolower($row['Type']) == 'varchar(50)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }

        $columns = array(
        'langfile' => "varchar(34) NOT NULL DEFAULT 'English'",
        'bbname' => "varchar(32) NOT NULL DEFAULT 'Your Forums'");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(50)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }
            $this->db->free_result($query);
        }

        $columns = array(
        'theme' => "smallint NOT NULL DEFAULT 1");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(30)') {
                // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
                $sql2 = "UPDATE " . $this->vars->tablepre . "$table "
                      . "LEFT JOIN " . $this->vars->tablepre . "themes ON " . $this->vars->tablepre . "$table.$colname = " . $this->vars->tablepre . "themes.name "
                      . "SET " . $this->vars->tablepre . "$table.$colname = IFNULL(" . $this->vars->tablepre . "themes.themeid, 1)";
                $this->upgrade_query($sql2);

                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
                $this->db->free_result($query);
            }

        $columns = array(
        'dateformat' => "varchar(10) NOT NULL DEFAULT 'dd-mm-yyyy'");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(20)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'tickerdelay' => "int NOT NULL DEFAULT 4000");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'char(10)') {
                // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
                $this->upgrade_query("UPDATE " . $this->vars->tablepre . "$table SET $colname = '4000' WHERE $colname = '' OR $colname IS NULL");
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
                }
            $this->db->free_result($query);
        }

        $columns = array(
        'todaysposts' => "char(3) NOT NULL DEFAULT 'on'",
        'stats' => "char(3) NOT NULL DEFAULT 'on'",
        'authorstatus' => "char(3) NOT NULL DEFAULT 'on'",
        'tickercontents' => "text NOT NULL");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Null']) == 'yes') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
                }
            $this->db->free_result($query);
        }

        if (count($sql) > 0) {
            $this->show->progress('Adding/Deleting/Modifying columns in the settings table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the members table');
        $this->upgrade_query('LOCK TABLES '.
            X_PREFIX.'members WRITE, '.
            X_PREFIX.'themes READ');

        $this->show->progress('Fixing birthday values');
        $this->fixBirthdays();

        $this->show->progress('Gathering schema information from the members table');
        $sql = array();
        $table = 'members';
        $columns = array(
        'webcam');
        foreach ($columns as $colname) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 1) {
                $sql[] = 'DROP COLUMN '.$colname;
            }
            $this->db->free_result($query);
        }

        $colname = 'uid';
        $coltype = "int NOT NULL auto_increment";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (stripos($row['Type'], 'smallint') === 0) {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'username';
        $coltype = "varchar(32) NOT NULL DEFAULT ''";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(25)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'password';
        $coltype = "varchar(32) NOT NULL DEFAULT ''";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(40)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'regdate';
        $coltype = "int NOT NULL DEFAULT 0";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (stripos($row['Type'], 'bigint') === 0) {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'postnum';
        $coltype = "MEDIUMINT NOT NULL DEFAULT 0";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (stripos($row['Type'], 'int') === 0) {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'timeoffset';
        $coltype = "DECIMAL(4,2) NOT NULL DEFAULT 0";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (stripos($row['Type'], 'int') === 0) {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'avatar';
        $coltype = "varchar(120) DEFAULT NULL";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(90)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'theme';
        $coltype = "smallint NOT NULL DEFAULT 0";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(30)') {
            // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
            $sql2 = "UPDATE " . $this->vars->tablepre . "$table "
                  . "LEFT JOIN " . $this->vars->tablepre . "themes ON " . $this->vars->tablepre . "$table.$colname = " . $this->vars->tablepre . "themes.name "
                  . "SET " . $this->vars->tablepre . "$table.$colname = IFNULL(" . $this->vars->tablepre . "themes.themeid, 0)";
            $this->upgrade_query($sql2);

            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'regip';
        $coltype = "varchar(15) NOT NULL DEFAULT ''";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(40)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'lastvisit';
        $coltype = "int UNSIGNED NOT NULL DEFAULT 0";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(30)' || stripos($row['Type'], 'bigint') === 0 || strtolower($row['Null']) == 'yes') {
            // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
            $this->upgrade_query("UPDATE " . $this->vars->tablepre . "$table SET $colname = '0' WHERE $colname = '' OR $colname IS NULL");
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'mood';
        $coltype = "varchar(128) NOT NULL DEFAULT 'Not Set'";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(15)' || strtolower($row['Type']) == 'varchar(32)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'pwdate';
        $coltype = "int NOT NULL DEFAULT 0";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (stripos($row['Type'], 'bigint') === 0) {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $columns = array(
        'email' => "varchar(60) NOT NULL DEFAULT ''",
        'site' => "varchar(75) NOT NULL DEFAULT ''",
        'aim' => "varchar(40) NOT NULL DEFAULT ''",
        'location' => "varchar(50) NOT NULL DEFAULT ''",
        'bio' => "text NOT NULL",
        'ignoreu2u' => "text NOT NULL");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Null']) == 'yes') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }
        $columns = array(
        'bday' => "varchar(10) NOT NULL DEFAULT '0000-00-00'");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Null']) == 'yes' || strtolower($row['Type']) == 'varchar(50)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'invisible' => "SET('1','0') DEFAULT 0",
        'u2ufolders' => "text NOT NULL",
        'saveogu2u' => "char(3) NOT NULL DEFAULT ''",
        'emailonu2u' => "char(3) NOT NULL DEFAULT ''",
        'useoldu2u' => "char(3) NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 0) {
                $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'username' => 'username (8)');
        foreach ($columns as $colname => $coltype) {
            if (! $this->schema->indexExists($table, '', $colname)) {
                $sql[] = "ADD INDEX $colname ($coltype)";
            } elseif (! $this->schema->indexExists($table, '', $colname, '8')) {
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
        foreach ($columns as $colname) {
            if (! $this->schema->indexExists($table, $colname)) {
                $sql[] = "ADD INDEX ($colname)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Deleting/Adding/Modifying columns in the members table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        // Mimic old function fixPPP()
        $this->show->progress('Fixing missing posts per page values');
        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "members SET ppp = " . $this->vars->settings['postperpage'] . " WHERE ppp = 0");
        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "members SET tpp = " . $this->vars->settings['topicperpage'] . " WHERE tpp = 0");

        $this->show->progress('Updating outgoing U2U status');
        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "members SET saveogu2u='yes'");

        $this->show->progress('Releasing the lock on the members table');
        $this->upgrade_query('UNLOCK TABLES');

        $this->show->progress('Adding new tables for polls');
        $this->schema->table('create', 'vote_desc');
        $this->schema->table('create', 'vote_results');
        $this->schema->table('create', 'vote_voters');

        $this->show->progress('Requesting to lock the polls tables');
        $this->upgrade_query('LOCK TABLES '.
            X_PREFIX.'threads WRITE, '.
            X_PREFIX.'vote_desc WRITE, '.
            X_PREFIX.'vote_results WRITE, '.
            X_PREFIX.'vote_voters WRITE, '.
            X_PREFIX.'members READ');

        $this->show->progress('Upgrading polls to new system');
        $this->fixPolls();

        $this->show->progress('Gathering schema information from the threads table');
        $sql = array();
        $table = 'threads';
        $colname = 'subject';
        $coltype = "varchar(128) NOT NULL DEFAULT ''";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(100)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'views';
        $coltype = "bigint NOT NULL DEFAULT 0";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (stripos($row['Type'], 'smallint') === 0 || stripos($row['Type'], 'int') === 0) {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'replies';
        $coltype = "int NOT NULL DEFAULT 0";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (stripos($row['Type'], 'smallint') === 0) {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'lastpost';
        $coltype = "varchar(54) NOT NULL DEFAULT ''";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(32)' || strtolower($row['Type']) == 'varchar(30)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'pollopts';
        $coltype = "tinyint NOT NULL DEFAULT 0";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'text') {
            // SQL mode STRICT_TRANS_TABLES requires explicit conversion of non-numeric values before modifying column types in any table.
            $this->upgrade_query("UPDATE " . $this->vars->tablepre . "$table SET $colname = '0' WHERE $colname = '' OR $colname IS NULL");
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'author';
        $coltype = "varchar(32) NOT NULL DEFAULT ''";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(40)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'topped';
        $coltype = "tinyint NOT NULL DEFAULT 0";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (stripos($row['Type'], 'smallint') === 0) {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'tid';
        if ($this->schema->indexExists($table, $colname, $colname)) {
            $sql[] = 'DROP INDEX '.$colname;
        }

        $columns = array(
        'author' => "author (8)");
        foreach ($columns as $colname => $coltype) {
            if (! $this->schema->indexExists($table, '', $colname)) {
                $sql[] = "ADD INDEX $colname ($coltype)";
            } elseif (! $this->schema->indexExists($table, '', $colname, '8')) {
                $sql[] = "DROP INDEX $colname";
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }

        $columns = array(
        'lastpost' => "lastpost",
        'closed' => "closed",
        'forum_optimize' => "fid, topped, lastpost");
        foreach ($columns as $colname => $coltype) {
            if (! $this->schema->indexExists($table, '', $colname)) {
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Modifying columns in the threads table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the attachments table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "attachments WRITE");

        $this->show->progress('Gathering schema information from the attachments table');
        $sql = array();
        $table = 'attachments';
        $columns = array(
        'aid' => "int NOT NULL auto_increment",
        'pid' => "int NOT NULL DEFAULT 0",
        'downloads' => "int NOT NULL DEFAULT 0");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (stripos($row['Type'], 'smallint') === 0) {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }
        $columns = array(
        'pid');
        foreach ($columns as $colname) {
            if (! $this->schema->indexExists($table, $colname)) {
                $sql[] = "ADD INDEX ($colname)";
            }
        }
        $filesize_was_missing = FALSE;
        $columns = array(
        'filesize' => "varchar(120) NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 0) {
                $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
                $filesize_was_missing = TRUE;
            }
            $this->db->free_result($query);
        }

        if (count($sql) > 0) {
            $this->show->progress('Modifying columns in the attachments table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        if ($filesize_was_missing) {
            $this->upgrade_query('UPDATE ' . $this->vars->tablepre . $table.' SET filesize = LENGTH(attachment)');
        }

        $this->show->progress('Requesting to lock the posts table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "posts WRITE");

        $this->show->progress('Gathering schema information from the posts table');
        $sql = array();
        $table = 'posts';
        $columns = array(
        'tid' => "int NOT NULL DEFAULT '0'");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (stripos($row['Type'], 'smallint') === 0) {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'author' => "varchar(32) NOT NULL DEFAULT ''",
        'useip' => "varchar(15) NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(40)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $colname = 'subject';
        $coltype = "tinytext NOT NULL";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(100)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $colname = 'dateline';
        $coltype = "int NOT NULL DEFAULT 0";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (stripos($row['Type'], 'bigint') === 0) {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $columns = array(
        'author' => "author (8)");
        foreach ($columns as $colname => $coltype) {
            if (! $this->schema->indexExists($table, '', $colname)) {
                $sql[] = "ADD INDEX $colname ($coltype)";
            } elseif (! $this->schema->indexExists($table, '', $colname, '8')) {
                $sql[] = "DROP INDEX $colname";
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }

        $columns = array(
        'fid' => "fid",
        'dateline' => "dateline",
        'thread_optimize' => "tid, dateline, pid");
        foreach ($columns as $colname => $coltype) {
            if (! $this->schema->indexExists($table, '', $colname)) {
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Modifying columns in the posts table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the ranks table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "ranks WRITE");

        $this->show->progress('Gathering schema information from the ranks table');
        $sql = array();
        $table = 'ranks';
        $columns = array(
        'title' => "varchar(100) NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(40)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'posts' => "MEDIUMINT DEFAULT 0",
        );
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (stripos($row['Type'], 'smallint') === 0) {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'title');
        foreach ($columns as $colname) {
            if (! $this->schema->indexExists($table, $colname)) {
                $sql[] = "ADD INDEX ($colname)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Modifying columns in the ranks table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Fixing special ranks');
        $this->upgrade_query("DELETE FROM " . $this->vars->tablepre . "ranks WHERE title IN ('Moderator', 'Super Moderator', 'Administrator', 'Super Administrator')");
        $this->upgrade_query("INSERT INTO " . $this->vars->tablepre . "ranks
         (title,                 posts, stars, allowavatars, avatarrank) VALUES
         ('Moderator',           -1,    6,     'yes',  ''),
         ('Super Moderator',     -1,    7,     'yes',  ''),
         ('Administrator',       -1,    8,     'yes',  ''),
         ('Super Administrator', -1,    9,     'yes',  '')"
        );
        $result = $this->upgrade_query("SELECT title FROM " . $this->vars->tablepre . "ranks WHERE posts = 0");
        if ($this->db->num_rows($result) == 0) {
            $result2 = $this->upgrade_query("SELECT title FROM " . $this->vars->tablepre . "ranks WHERE title = 'Newbie'");
            if ($this->db->num_rows($result2) == 0) {
                $this->upgrade_query("INSERT INTO " . $this->vars->tablepre . "ranks
                 (title,    posts, stars, allowavatars, avatarrank) VALUES
                 ('Newbie', 0,     1,     'yes',  '')"
                );
            } else {
                $this->upgrade_query("UPDATE " . $this->vars->tablepre . "ranks SET posts = 0 WHERE title = 'Newbie'");
            }
            $this->db->free_result($result2);
        }
        $this->db->free_result($result);

        $this->show->progress('Requesting to lock the templates table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "templates WRITE");

        $this->show->progress('Gathering schema information from the templates table');
        $sql = array();
        $table = 'templates';
        $columns = array(
        'name' => "varchar(32) NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(40)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'name');
        foreach ($columns as $colname) {
            if (! $this->schema->indexExists($table, $colname)) {
                $sql[] = "ADD INDEX ($colname)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Modifying columns in the templates table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the u2u table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "u2u WRITE");

        $upgrade_u2u = FALSE;

        $this->show->progress('Gathering schema information from the u2u table');
        $sql = array();
        $table = 'u2u';
        $columns = array(
        'u2uid' => "bigint NOT NULL auto_increment");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (stripos($row['Type'], 'smallint') === 0 || stripos($row['Type'], 'int') === 0) {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'msgto' => "varchar(32) NOT NULL DEFAULT ''",
        'msgfrom' => "varchar(32) NOT NULL DEFAULT ''",
        'folder' => "varchar(32) NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(40)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'dateline' => "int NOT NULL DEFAULT 0");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (stripos($row['Type'], 'bigint') === 0) {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'subject' => "varchar(64) NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(75)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'type' => "set('incoming','outgoing','draft') NOT NULL DEFAULT ''",
        'owner' => "varchar(32) NOT NULL DEFAULT ''",
        'sentstatus' => "set('yes','no') NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 0) {
                $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
                $upgrade_u2u = TRUE;
            }
            $this->db->free_result($query);
        }

        if ($upgrade_u2u) {
            // Commit changes so far.
            if (count($sql) > 0) {
                $this->show->progress('Modifying columns in the u2u table');
                $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
                $this->upgrade_query($sql);
            }

            $sql = array();

            // Mimic old function upgradeU2U() but with fewer queries
            $this->show->progress('Upgrading U2Us');
            $this->upgrade_query("UPDATE " . $this->vars->tablepre . "$table SET type='incoming', owner=msgto WHERE folder='inbox'");
            $this->upgrade_query("UPDATE " . $this->vars->tablepre . "$table SET type='outgoing', owner=msgfrom WHERE folder='outbox'");
            $this->upgrade_query("UPDATE " . $this->vars->tablepre . "$table SET type='incoming', owner=msgfrom WHERE folder != 'outbox' AND folder != 'inbox'");
            $this->upgrade_query("UPDATE " . $this->vars->tablepre . "$table SET readstatus='no' WHERE readstatus=''");

            $colname = 'new';
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 1) {
                $this->upgrade_query("UPDATE " . $this->vars->tablepre . "$table SET sentstatus='yes' WHERE new=''");
            }
        }

        $columns = array(
        'readstatus' => "set('yes','no') NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(3)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'new');
        foreach ($columns as $colname) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 1) {
                $sql[] = 'DROP COLUMN '.$colname;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'msgto' => "msgto (8)",
        'msgfrom' => "msgfrom (8)");
        foreach ($columns as $colname => $coltype) {
            if (! $this->schema->indexExists($table, $colname)) {
                $sql[] = "ADD INDEX $colname ($coltype)";
            } elseif (! $this->schema->indexExists($table, $colname, '', '8')) {
                $sql[] = "DROP INDEX $colname";
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }

        $columns = array(
        'folder' => "folder (8)",
        'readstatus' => "readstatus",
        'owner' => "owner (8)");
        foreach ($columns as $colname => $coltype) {
            if (! $this->schema->indexExists($table, $colname)) {
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Modifying columns in the u2u table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the words table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "words WRITE");

        $this->show->progress('Gathering schema information from the words table');
        $sql = array();
        $table = 'words';
        $columns = array(
        'find');
        foreach ($columns as $colname) {
            if (! $this->schema->indexExists($table, $colname)) {
                $sql[] = "ADD INDEX ($colname)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Adding indexes in the words table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the restricted table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "restricted WRITE");

        $this->show->progress('Gathering schema information from the restricted table');
        $sql = array();
        $table = 'restricted';
        $columns = array(
        'name' => "varchar(32) NOT NULL DEFAULT ''");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            $row = $this->db->fetch_array($query);
            if (strtolower($row['Type']) == 'varchar(25)') {
                $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $columns = array(
        'case_sensitivity' => "ENUM('0', '1') DEFAULT '1' NOT NULL",
        'partial' => "ENUM('0', '1') DEFAULT '1' NOT NULL");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 0) {
                $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        if (count($sql) > 0) {
            $this->show->progress('Modifying columns in the restricted table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Releasing the lock on the restricted table');
        $this->upgrade_query('UNLOCK TABLES');

        $this->show->progress('Adding new tables');
        $this->schema->table('create', 'captchaimages');
        $this->schema->table('overwrite', 'logs');
        $this->schema->table('overwrite', 'whosonline');
    }

    /**
     * Performs all tasks needed to raise the database schema_version number to 2.
     *
     * This function is officially compatible with schema_version 1 as well as the following
     * XMB versions that did not have a schema_version number: 1.9.9, 1.9.10, and 1.9.11 Alpha (all).
     *
     * @since 1.9.11 Beta 3
     */
    function upgrade_schema_to_v2()
    {
        $this->show->progress('Beginning schema upgrade to version number 2');

        $this->show->progress('Requesting to lock the settings table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "settings WRITE");

        $this->show->progress('Gathering schema information from the settings table');
        $sql = array();
        $table = 'settings';
        $columns = array(
        'boardurl');
        foreach ($columns as $colname) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 1) {
                $sql[] = 'DROP COLUMN '.$colname;
            }
            $this->db->free_result($query);
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
        $missing = array_diff(array_keys($columns), $this->schema->listColumns($table));
        foreach ($missing as $colname) {
            $coltype = $columns[$colname];
            $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
        }

        if (count($sql) > 0) {
            $this->show->progress('Adding/Deleting columns in the settings table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the attachments table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "attachments WRITE");

        $this->show->progress('Gathering schema information from the attachments table');
        $sql = array();
        $table = 'attachments';
        $columns = array(
        'tid');
        foreach ($columns as $colname) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 1) {
                $sql[] = 'DROP COLUMN '.$colname;
            }
            $this->db->free_result($query);
        }
        $columns = array(
        'img_size' => "VARCHAR(9) NOT NULL",
        'parentid' => "INT NOT NULL DEFAULT '0'",
        'subdir' => "VARCHAR(15) NOT NULL",
        'uid' => "INT NOT NULL DEFAULT '0'",
        'updatetime' => "TIMESTAMP NOT NULL DEFAULT current_timestamp");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 0) {
                $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }
        $columns = array(
        'parentid',
        'uid');
        foreach ($columns as $colname) {
            if (! $this->schema->indexExists($table, $colname)) {
                $sql[] = "ADD INDEX ($colname)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Adding/Deleting columns in the attachments table');
            // Important to do this all in one step because MySQL copies the entire table after every ALTER command.
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the members table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "members WRITE");

        $this->show->progress('Gathering schema information from the members table');
        $sql = array();
        $table = 'members';
        $columns = array(
        'u2ualert' => "TINYINT NOT NULL DEFAULT '0'");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 0) {
                $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }
        $columns = array(
        'postnum' => "postnum MEDIUMINT NOT NULL DEFAULT 0");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 1) {
                $sql[] = 'CHANGE '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        if (count($sql) > 0) {
            $this->show->progress('Adding/Deleting columns in the members table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the ranks table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "ranks WRITE");

        $this->show->progress('Gathering schema information from the ranks table');
        $sql = array();
        $table = 'ranks';
        $columns = array(
        'posts' => "posts MEDIUMINT DEFAULT 0");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 1) {
                $sql[] = 'CHANGE '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        if (count($sql) > 0) {
            $this->show->progress('Adding/Deleting columns in the ranks table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the themes table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "themes WRITE");

        $this->show->progress('Gathering schema information from the themes table');
        $sql = array();
        $table = 'themes';
        $columns = array(
        'admdir' => "VARCHAR( 120 ) NOT NULL DEFAULT 'images/admin'");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 0) {
                $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        if (count($sql) > 0) {
            $this->show->progress('Adding/Deleting columns in the themes table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the vote_desc table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "vote_desc WRITE");

        $this->show->progress('Gathering schema information from the vote_desc table');
        $sql = array();
        $table = 'vote_desc';
        $columns = array(
        'topic_id' => "topic_id INT UNSIGNED NOT NULL");
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 1) {
                $sql[] = 'CHANGE '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        if (count($sql) > 0) {
            $this->show->progress('Adding/Deleting columns in the vote_desc table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Releasing the lock on the vote_desc table');
        $this->upgrade_query('UNLOCK TABLES');

        $this->show->progress('Adding new tables');
        $this->schema->table('create', 'lang_base');
        $this->schema->table('create', 'lang_keys');
        $this->schema->table('create', 'lang_text');

        $this->show->progress('Resetting the schema version number');
        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "settings SET schema_version = 2");
    }

    /**
     * Performs all tasks needed to raise the database schema_version number to 3.
     *
     * This function is officially compatible with schema_version 2 only.
     *
     * @since 1.9.11 Beta 4
     */
    function upgrade_schema_to_v3()
    {
        $this->show->progress('Beginning schema upgrade to version number 3');

        $this->show->progress('Requesting to lock the logs table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "logs WRITE");

        $this->show->progress('Gathering schema information from the logs table');
        $sql = array();
        $table = 'logs';
        $columns = array(
        'date',
        'tid');
        foreach ($columns as $colname) {
            if (! $this->schema->indexExists($table, $colname)) {
                $sql[] = "ADD INDEX ($colname)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Adding indexes to the logs table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Releasing the lock on the logs table');
        $this->upgrade_query('UNLOCK TABLES');

        $this->show->progress('Resetting the schema version number');
        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "settings SET schema_version = 3");
    }

    /**
     * Performs all tasks needed to raise the database schema_version number to 4.
     *
     * @since 1.9.11.11
     */
    function upgrade_schema_to_v4()
    {
        $this->show->progress('Beginning schema upgrade to version number 4');

        $this->show->progress('Requesting to lock the threads table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "threads WRITE");

        $this->show->progress('Gathering schema information from the threads table');
        $sql = array();
        $table = 'threads';
        $columns = array(
        'fid');
        foreach ($columns as $colname) {
            if ($this->schema->indexExists($table, '', $colname)) {
                $sql[] = "DROP INDEX $colname";
            }
        }
        $columns = array(
        'forum_optimize' => 'fid, topped, lastpost');
        foreach ($columns as $colname => $coltype) {
            if (! $this->schema->indexExists($table, '', $colname)) {
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Deleting/Adding indexes to the threads table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Requesting to lock the posts table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "posts WRITE");

        $this->show->progress('Gathering schema information from the posts table');
        $sql = array();
        $table = 'posts';
        $columns = array(
        'tid');
        foreach ($columns as $colname) {
            if ($this->schema->indexExists($table, '', $colname)) {
                $sql[] = "DROP INDEX $colname";
            }
        }
        $columns = array(
        'thread_optimize' => 'tid, dateline, pid');
        foreach ($columns as $colname => $coltype) {
            if (! $this->schema->indexExists($table, '', $colname)) {
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Deleting/Adding indexes to the posts table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Releasing the lock on the posts table');
        $this->upgrade_query('UNLOCK TABLES');

        $this->show->progress('Resetting the schema version number');
        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "settings SET schema_version = 4");
    }

    /**
     * Performs all tasks needed to raise the database schema_version number to 5.
     *
     * @since 1.9.12
     */
    function upgrade_schema_to_v5()
    {
        $this->show->progress('Requesting to lock the members table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "members WRITE");

        $this->show->progress('Gathering schema information from the members table');
        $sql = [];
        $table = 'members';
        $columns = [
        'bad_login_date' => "int unsigned NOT NULL DEFAULT 0",
        'bad_login_count' => "int unsigned NOT NULL DEFAULT 0",
        'bad_session_date' => "int unsigned NOT NULL DEFAULT 0",
        'bad_session_count' => "int unsigned NOT NULL DEFAULT 0",
        'sub_each_post' => "varchar(3) NOT NULL DEFAULT 'no'",
        'waiting_for_mod' => "varchar(3) NOT NULL DEFAULT 'no'",
        ];
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 0) {
                $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        $colname = 'lastvisit';
        $coltype = "int unsigned NOT NULL DEFAULT 0";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (stripos($row['Type'], 'int') !== 0 || stripos($row['Type'], 'unsigned') === null) {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        $columns = [
        'invisible' => 'invisible',
        'password' => 'password',
        'username' => 'username',
        ];
        foreach ($columns as $colname => $coltype) {
            if ($this->schema->indexExists($table, $coltype, $colname)) {
                $sql[] = "DROP INDEX $colname";
            }
        }

        if (! $this->schema->indexExists($table, 'username', 'userunique')) {
            $this->show->progress('Removing duplicate username records');
            $query = $this->upgrade_query('SELECT username, MIN(uid) AS firstuser FROM ' . $this->vars->tablepre . $table.' GROUP BY username HAVING COUNT(*) > 1');
            while($dupe = $this->db->fetch_array($query)) {
                $name = $this->db->escape($dupe['username']);
                $id = $dupe['firstuser'];
                $this->upgrade_query('DELETE FROM ' . $this->vars->tablepre . $table." WHERE username = '$name' AND uid != $id");
            }
            $sql[] = "ADD UNIQUE INDEX `userunique` (`username`)";
        }

        $columns = [
        'lastvisit' => 'lastvisit',
        ];
        foreach ($columns as $colname => $coltype) {
            if (! $this->schema->indexExists($table, $coltype, $colname)) {
                $sql[] = "ADD INDEX $colname ($coltype)";
            }
        }

        if (count($sql) > 0) {
            $this->show->progress('Adding/Deleting columns in the members table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $table = 'settings';
        $this->show->progress('Requesting to lock the settings table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "$table WRITE");

        $this->show->progress('Reading the settings table data');
        $query = $this->upgrade_query('SELECT * FROM ' . $this->vars->tablepre . $table);
        $settings = $this->db->fetch_array($query);
        $settings['google_captcha'] = 'off';
        $settings['google_captcha_sitekey'] = '';
        $settings['google_captcha_secret'] = '';
        $settings['hide_banned'] = 'off';
        $settings['quarantine_new_users'] = 'off';
        $settings['$this->show->logs_in_threads'] = 'off';
        $settings['tickercode'] = 'html';
        unset($settings['sightml']);

        $this->show->progress('Replacing the settings table');
        $this->schema->table('overwrite', 'settings');
        $sql = [];
        foreach ($settings as $name => $value) {
            $this->db->escape_fast($value);
            $sql[] = "('$name', '$value')";
        }
        $this->upgrade_query('INSERT INTO ' . $this->vars->tablepre . "settings (name, value) VALUES " . implode(',', $sql));

        $this->show->progress('Requesting to lock the forums table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "forums WRITE");

        $this->show->progress('Gathering schema information from the forums table');
        $sql = [];
        $table = 'forums';
        $columns = [
        'allowhtml',
        ];
        foreach ($columns as $colname) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 1) {
                $sql[] = 'DROP COLUMN '.$colname;
            }
            $this->db->free_result($query);
        }

        if (count($sql) > 0) {
            $this->show->progress('Deleting columns in the forums table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' ' . implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Releasing the lock on the forums table');
        $this->upgrade_query('UNLOCK TABLES');

        $this->show->progress('Adding new tables');
        $this->schema->table('create', 'sessions');
        $this->schema->table('create', 'tokens');

        $this->show->progress('Resetting the schema version number');
        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "settings SET value = '5' WHERE name = 'schema_version'");
    }

    /**
     * Performs all tasks needed to raise the database schema_version number to 6.
     *
     * @since 1.9.12
     */
    function upgrade_schema_to_v6()
    {
        $this->show->progress('Requesting to lock the themes table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "themes WRITE");

        $this->show->progress('Gathering schema information from the themes table');
        $sql = [];
        $table = 'themes';
        $columns = [
        'version' => "int unsigned NOT NULL DEFAULT 0",
        ];
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 0) {
                $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        if (count($sql) > 0) {
            $this->show->progress('Adding columns to the themes table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' ' . implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Releasing the lock on the themes table');
        $this->upgrade_query('UNLOCK TABLES');

        $this->show->progress('Emptying the captcha table');
        $this->upgrade_query('TRUNCATE TABLE ' . $this->vars->tablepre . "captchaimages");

        $this->show->progress('Resetting the schema version number');
        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "settings SET value = '6' WHERE name = 'schema_version'");
    }

    /**
     * Performs all tasks needed to raise the database schema_version number to 7.
     *
     * @since 1.9.12
     */
    function upgrade_schema_to_v7()
    {
        $this->show->progress('Adding new tables');
        $this->schema->table('create', 'hold_attachments');
        $this->schema->table('create', 'hold_favorites');
        $this->schema->table('create', 'hold_posts');
        $this->schema->table('create', 'hold_threads');
        $this->schema->table('create', 'hold_vote_desc');
        $this->schema->table('create', 'hold_vote_results');

        $this->show->progress('Requesting to lock the vote_desc table');
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . "vote_desc WRITE");

        $this->show->progress('Gathering schema information from the vote_desc table');
        $sql = [];
        $table = 'vote_desc';
        $columns = [
        'vote_length',
        'vote_start',
        'vote_text',
        ];
        foreach ($columns as $colname) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 1) {
                $sql[] = 'DROP COLUMN '.$colname;
            }
            $this->db->free_result($query);
        }

        if (count($sql) > 0) {
            $this->show->progress('Deleting columns in the vote_desc table');
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' ' . implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress('Releasing the lock on the vote_desc table');
        $this->upgrade_query('UNLOCK TABLES');

        $this->show->progress('Resetting the schema version number');
        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "settings SET value = '7' WHERE name = 'schema_version'");
    }

    /**
     * Performs all tasks needed to raise the database schema_version number to 8.
     *
     * @since 1.9.12
     */
    function upgrade_schema_to_v8()
    {
        $this->show->progress('Gathering schema information from the settings table');
        $table = 'settings';
        $query = $this->upgrade_query('SELECT value FROM ' . $this->vars->tablepre . $table.' WHERE name = "images_https_only"');
        if ($this->db->num_rows($query) != 1) {
            $this->show->progress('Adding data to the settings table');
            $this->upgrade_query('INSERT INTO ' . $this->vars->tablepre . $table.' SET value = "off", name = "images_https_only"');
        }

        $this->show->progress('Resetting the schema version number');
        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "settings SET value = '8' WHERE name = 'schema_version'");
    }

    /**
     * Performs all tasks needed to raise the database schema_version number to 9.
     *
     * @since 1.9.12.07
     */
    function upgrade_schema_to_v9()
    {
        $table = 'hold_posts';
        $this->show->progress("Requesting to lock the $table table");
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . $table." WRITE");
        $this->show->progress("Gathering schema information from the $table table");

        $sql = [];
        $colname = 'useip';
        $coltype = "varchar(39) NOT NULL DEFAULT ''";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(15)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        if (count($sql) > 0) {
            $this->show->progress("Modifying columns in the $table table");
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $table = 'members';
        $this->show->progress("Requesting to lock the $table table");
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . $table." WRITE");
        $this->show->progress("Gathering schema information from the $table table");

        $sql = [];
        $colname = 'regip';
        $coltype = "varchar(39) NOT NULL DEFAULT ''";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(15)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        if (count($sql) > 0) {
            $this->show->progress("Modifying columns in the $table table");
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $table = 'posts';
        $this->show->progress("Requesting to lock the $table table");
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . $table." WRITE");
        $this->show->progress("Gathering schema information from the $table table");

        $sql = [];
        $colname = 'useip';
        $coltype = "varchar(39) NOT NULL DEFAULT ''";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(15)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        if (count($sql) > 0) {
            $this->show->progress("Modifying columns in the $table table");
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $table = 'vote_voters';
        $this->show->progress("Requesting to lock the $table table");
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . $table." WRITE");
        $this->show->progress("Gathering schema information from the $table table");

        $sql = [];
        $columns = [
        'vote_user_ip' => 'vote_user_ip',
        ];
        foreach ($columns as $colname => $coltype) {
            if ($this->schema->indexExists($table, $coltype, $colname)) {
                $sql[] = "DROP INDEX $colname";
            }
        }

        $colname = 'vote_user_ip';
        $coltype = "varchar(39) NOT NULL DEFAULT ''";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'char(8)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        if (count($sql) > 0) {
            $this->show->progress("Modifying columns in the $table table");
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $table = 'whosonline';
        $this->show->progress("Requesting to lock the $table table");
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . $table." WRITE");
        $this->show->progress("Gathering schema information from the $table table");

        $sql = [];
        $colname = 'ip';
        $coltype = "varchar(39) NOT NULL DEFAULT ''";
        $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
        $row = $this->db->fetch_array($query);
        if (strtolower($row['Type']) == 'varchar(15)') {
            $sql[] = 'MODIFY COLUMN '.$colname.' '.$coltype;
        }

        if (count($sql) > 0) {
            $this->show->progress("Modifying columns in the $table table");
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' '.implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress("Releasing the lock on the $table table");
        $this->upgrade_query('UNLOCK TABLES');

        $this->show->progress('Resetting the schema version number');
        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "settings SET value = '9' WHERE name = 'schema_version'");
    }

    /**
     * Performs all tasks needed to raise the database schema_version number to 10.
     *
     * @since 1.10.00
     */
    function upgrade_schema_to_v10()
    {
        $this->show->progress('Dropping obsolete tables');
        $this->schema->table('drop', 'lang_base');
        $this->schema->table('drop', 'lang_keys');
        $this->schema->table('drop', 'lang_text');
        $this->schema->table('drop', 'templates');

        $table = 'members';

        $this->show->progress("Requesting to lock the $table table");
        $this->upgrade_query('LOCK TABLES ' . $this->vars->tablepre . $table." WRITE");
        $this->show->progress("Gathering schema information from the $table table");

        $sql = [];
        $columns = [
            'aim',
            'icq',
            'msn',
            'yahoo',
        ];
        foreach ($columns as $colname) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 1) {
                $sql[] = 'DROP COLUMN '.$colname;
            }
            $this->db->free_result($query);
        }

        $columns = [
            'password2' => "varchar(255) NOT NULL DEFAULT ''",
        ];
        foreach ($columns as $colname => $coltype) {
            $query = $this->upgrade_query('DESCRIBE ' . $this->vars->tablepre . $table.' '.$colname);
            if ($this->db->num_rows($query) == 0) {
                $sql[] = 'ADD COLUMN '.$colname.' '.$coltype;
            }
            $this->db->free_result($query);
        }

        if (count($sql) > 0) {
            $this->show->progress("Adding/Deleting columns in the $table table");
            $sql = 'ALTER TABLE ' . $this->vars->tablepre . $table.' ' . implode(', ', $sql);
            $this->upgrade_query($sql);
        }

        $this->show->progress("Releasing the lock on the $table table");
        $this->upgrade_query('UNLOCK TABLES');

        $this->show->progress('Resetting the schema version number');
        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "settings SET value = '10' WHERE name = 'schema_version'");
    }

// TODO: Schema 11
//  - Delete 'spellcheck' record from settings table.
//  - Delete members.showemail
//  - Delete members.useoldu2u
//  - Increment all themes.version values.

    /**
     * Recalculates the value of every field in the forums.postperm column.
     *
     * Function has been modified to run without parameters.
     *
     * @since 1.9.6 RC1
     */
    function fixForumPerms()
    {
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
        $q = $this->upgrade_query("SELECT fid, private, userlist, postperm, guestposting, pollstatus FROM " . $this->vars->tablepre . "forums WHERE (type='forum' OR type='sub')");
        while($forum = $this->db->fetch_array($q)) {
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
            foreach ($parts as $key=>$val) {
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

            $this->upgrade_query("UPDATE " . $this->vars->tablepre . "forums SET postperm='".implode(',', $newFormat)."' WHERE fid=$fid");
        }
    }

    /**
     * Convert threads.pollopts text column into relational vote_ tables.
     *
     * @since 1.9.8
     */
    function fixPolls()
    {
        $q = $this->upgrade_query("SHOW COLUMNS FROM " . $this->vars->tablepre . "threads LIKE 'pollopts'");
        $result = $this->db->fetch_array($q);
        $this->db->free_result($q);

        if (FALSE === $result) return; // Unexpected condition, do not attempt to use fixPolls().
        if (FALSE !== strpos(strtolower($result['Type']), 'int')) return; // Schema already at 1.9.8+

        $q = $this->upgrade_query("SELECT tid, subject, pollopts FROM " . $this->vars->tablepre . "threads WHERE pollopts != '' AND pollopts != '1'");
        while($thread = $this->db->fetch_array($q)) {
            // Poll titles are historically unslashed, but thread titles are double-slashed.
            $thread['subject'] = stripslashes($thread['subject']);
            $this->db->escape_fast($thread['subject']);

            $this->upgrade_query("INSERT INTO " . $this->vars->tablepre . "vote_desc SET `topic_id` = {$thread['tid']}");
            $poll_id = $this->db->insert_id();

            $options = explode("#|#", $thread['pollopts']);
            $num_options = count($options) - 1;

            if (0 == $num_options) continue; // Sanity check.  Remember, 1 != '' evaluates to TRUE in MySQL.

            $voters = explode('    ', trim($options[$num_options]));

            if (1 == count($voters) && strlen($voters[0]) < 3) {
                // The most likely values for $options[$num_options] are '' and '1'.  Treat them equivalent to null.
            } else {
                $name = array();
                foreach ($voters as $v) {
                    $name[] = $this->db->escape(trim($v));
                }
                $name = "'".implode("', '", $name)."'";
                $query = $this->upgrade_query("SELECT uid FROM " . $this->vars->tablepre . "members WHERE username IN ($name)");
                $values = array();
                while($u = $this->db->fetch_array($query)) {
                    $values[] = "($poll_id, {$u['uid']})";
                }
                $this->db->free_result($query);
                if (count($values) > 0) {
                    $this->upgrade_query("INSERT INTO " . $this->vars->tablepre . "vote_voters (`vote_id`, `vote_user_id`) VALUES ".implode(',', $values));
                }
            }

            $values = array();
            for($i = 0; $i < $num_options; $i++) {
                $bit = explode('||~|~||', $options[$i]);
                $option_name = $this->db->escape(trim($bit[0]));
                $num_votes = (int) trim($bit[1]);
                $values[] = "($poll_id, ".($i+1).", '$option_name', $num_votes)";
            }
            $this->upgrade_query("INSERT INTO " . $this->vars->tablepre . "vote_results (`vote_id`, `vote_option_id`, `vote_option_text`, `vote_result`) VALUES ".implode(',', $values));
        }
        $this->db->free_result($q);
        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "threads SET pollopts='1' WHERE pollopts != ''");
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
    function fixBirthdays()
    {
        $cachedLanguages = array();
        $lang = array();

        require XMB_ROOT.'lang/English.lang.php';
        $baselang = $lang;
        $cachedLanguages['English'] = $lang;

        $q = $this->upgrade_query("SELECT uid, bday, langfile FROM " . $this->vars->tablepre . "members WHERE bday != ''");
        while($m = $this->db->fetch_array($q)) {
            $uid = $m['uid'];

            // check if the birthday is already in proper format
            $parts = explode('-', $m['bday']);
            if (count($parts) == 3 && is_numeric($parts[0]) && is_numeric($parts[1]) && is_numeric($parts[2])) {
                continue;
            }

            $lang = array();

            if (!isset($cachedLanguages[$m['langfile']])) {
                if (!file_exists(XMB_ROOT.'lang/'.$m['langfile'].'.lang.php')) {
                    // Re-try in case the old file was named english.lang.php instead of English.lang.php for some reason.
                    $test = $m['langfile'];
                    $test[0] = strtoupper($test[0]);
                    if (isset($cachedLanguages[$test])) {
                        $m['langfile'] = $test;
                    } elseif (file_exists(XMB_ROOT.'lang/'.$test.'.lang.php')) {
                        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "members SET langfile='$test' WHERE langfile = '{$m['langfile']}'");
                        $m['langfile'] = $test;
                    } else {
                        $this->show->error('A needed file is missing for date translation: '.XMB_ROOT.'lang/'.$m['langfile'].'.lang.php.  Upgrade halted to prevent damage.');
                        throw new Exception('fixBirthdays() stopped the upgrade because language "'.$m['langfile'].'" was missing.');
                    }
                }
                if (!isset($cachedLanguages[$m['langfile']])) {
                    $old_error_level = error_reporting();
                    error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);
                    require XMB_ROOT.'lang/'.$m['langfile'].'.lang.php';
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
                $this->upgrade_query("UPDATE " . $this->vars->tablepre . "members SET bday='".iso8601_date($year, $month, $day)."' WHERE uid=$uid");
            } else {
                $this->upgrade_query("UPDATE " . $this->vars->tablepre . "members SET bday='0000-00-00' WHERE uid=$uid");
            }
        }
        $this->db->free_result($q);
        $this->upgrade_query("UPDATE " . $this->vars->tablepre . "members SET bday='0000-00-00' WHERE bday=''");
    }

    /**
     * Recalculates the value of every field in the forums.postperm column.
     *
     * @since 1.9.1
     */
    function fixPostPerm()
    {
        $query = $this->upgrade_query("SELECT fid, private, postperm, guestposting FROM " . $this->vars->tablepre . "forums WHERE type != 'group'");
        while ($forum = $this->db->fetch_array($query)) {
            $update = false;
            $pp = trim($forum['postperm']);
            if (strlen($pp) > 0 && strpos($pp, '|') === false) {
                $update = true;
                $forum['postperm'] = $pp . '|' . $pp;	// make the postperm the same for thread and reply
            }
            if ($forum['guestposting'] != 'on' && $forum['guestposting'] != 'off') {
                $forum['guestposting'] = 'off';
                $update = true;
            }
            if ($forum['private'] == '') {
                $forum['private'] = '1';	// by default, forums are not private.
                $update = true;
            }
            if ($update) {
                $this->upgrade_query("UPDATE " . $this->vars->tablepre . "forums SET postperm='{$forum['postperm']}', guestposting='{$forum['guestposting']}', private='{$forum['private']}' WHERE fid={$forum['fid']}");
            }
        }
        $this->db->free_result($query);
    }

    /**
     * Abstracts database queries for better error handling.
     *
     * @since 1.9.12
     * @param string $sql
     * @return mixed Result of $this->db->query()
     */
    private function upgrade_query(string $sql)
    {
        $result = $this->db->query($sql, false);
        
        if (false === $result) {
            $error = '<pre>MySQL encountered the following error: ' . cdataOut($this->db->error()) . "\n\n";
            if ('' != $sql) {
                $error .= 'In the following query: <em>' . cdataOut($sql) . '</em>';
            }
            $error .= '</pre>';
            
            $this->show->error($error);
            exit;
        }
        
        return $result;
    }
}
