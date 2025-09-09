<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-3
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

use InvalidArgumentException;

/**
 * Database table layouts and logic.
 *
 * @since 1.10.00
 */
class Schema
{
    public const VER = 14;

    public function __construct(private DBStuff $db, private Variables $vars)
    {
        // Property promotion.
    }

    /**
     * Executes logic necessary to install or uninstall one of the XMB tables.
     *
     * @since 1.9.11.11 Formerly xmb_schema_table()
     * @since 1.10.00
     * @param string $action Must be 'drop', 'create', or 'overwrite'.
     * @param string $name The name of the XMB table, with no prefix.
     */
    public function table(string $action, string $name)
    {
        // Check existence to help avoid dropping non-existent tables.
        $exists = $this->tableExists($name);

        if ($exists && ('drop' == $action || 'overwrite' == $action)) {
            $this->db->query($this->dropTable($name));
        }
        if ('create' == $action && ! $exists || 'overwrite' == $action) {
            $this->db->query($this->createTable($name));
        }
    }

    /**
     * Generates a DROP TABLE query for the XMB schema in MySQL.
     *
     * @since 1.9.11.11 Formerly xmb_schema_drop()
     * @since 1.10.00
     * @param string $name The name of the XMB table, with no prefix.
     * @return string
     */
    private function dropTable(string $name): string
    {
        return "DROP TABLE IF EXISTS " . $this->vars->tablepre . $name;
    }

    /**
     * Generates a CREATE TABLE query for the XMB schema in MySQL.
     *
     * @since 1.9.11.11 Formerly xmb_schema_create()
     * @since 1.10.00
     * @param string $name The name of the XMB table, with no prefix.
     * @return string
     */
    private function createTable(string $name): string
    {
        switch($name) {
            case 'attachments':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `aid` int NOT NULL auto_increment,
                  `pid` int NOT NULL DEFAULT 0,
                  `filename` varchar(120) NOT NULL DEFAULT '',
                  `filetype` varchar(120) NOT NULL DEFAULT '',
                  `filesize` varchar(120) NOT NULL DEFAULT '',
                  `attachment` longblob NOT NULL,
                  `downloads` int NOT NULL DEFAULT 0,
                  `img_size` VARCHAR(9) NOT NULL,
                  `parentid` INT NOT NULL DEFAULT '0',
                  `subdir` VARCHAR( 15 ) NOT NULL,
                  `uid` INT NOT NULL DEFAULT '0',
                  `updatetime` TIMESTAMP NOT NULL DEFAULT current_timestamp,
                  PRIMARY KEY  (`aid`),
                  KEY `pid` (`pid`),
                  KEY `parentid` (`parentid`),
                  KEY `uid` (`uid`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'banned':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `ip1` smallint NOT NULL DEFAULT 0,
                  `ip2` smallint NOT NULL DEFAULT 0,
                  `ip3` smallint NOT NULL DEFAULT 0,
                  `ip4` smallint NOT NULL DEFAULT 0,
                  `dateline` int NOT NULL DEFAULT 0,
                  `id` smallint NOT NULL AUTO_INCREMENT,
                  PRIMARY KEY  (`id`),
                  KEY `ip1` (`ip1`),
                  KEY `ip2` (`ip2`),
                  KEY `ip3` (`ip3`),
                  KEY `ip4` (`ip4`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'buddys':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `username` varchar(32) NOT NULL DEFAULT '',
                  `buddyname` varchar(32) NOT NULL DEFAULT '',
                  KEY `username` (username (8))
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'captchaimages':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `imagehash` varchar(32) NOT NULL DEFAULT '',
                  `imagestring` varchar(12) NOT NULL DEFAULT '',
                  `dateline` int NOT NULL DEFAULT '0',
                  KEY `dateline` (`dateline`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'favorites':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `tid` int NOT NULL DEFAULT 0,
                  `username` varchar(32) NOT NULL DEFAULT '',
                  `type` varchar(32) NOT NULL DEFAULT '',
                  KEY `tid` (`tid`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'forums':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `type` varchar(15) NOT NULL DEFAULT '',
                  `fid` smallint NOT NULL auto_increment,
                  `name` varchar(128) NOT NULL DEFAULT '',
                  `status` varchar(15) NOT NULL DEFAULT '',
                  `lastpost` varchar(54) NOT NULL DEFAULT '',
                  `moderator` varchar(100) NOT NULL DEFAULT '',
                  `displayorder` smallint NOT NULL DEFAULT 0,
                  `description` text,
                  `allowsmilies` char(3) NOT NULL DEFAULT '',
                  `allowbbcode` char(3) NOT NULL DEFAULT '',
                  `userlist` text NOT NULL,
                  `theme` smallint NOT NULL DEFAULT 0,
                  `posts` int NOT NULL DEFAULT 0,
                  `threads` int NOT NULL DEFAULT 0,
                  `fup` smallint NOT NULL DEFAULT 0,
                  `postperm` varchar(11) NOT NULL DEFAULT '0,0,0,0',
                  `allowimgcode` char(3) NOT NULL DEFAULT '',
                  `attachstatus` varchar(15) NOT NULL DEFAULT '',
                  `password` varchar(32) NOT NULL DEFAULT '',
                  PRIMARY KEY  (`fid`),
                  KEY `fup` (`fup`),
                  KEY `type` (`type`),
                  KEY `displayorder` (`displayorder`),
                  KEY `status` (`status`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'hold_attachments':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `aid` int NOT NULL auto_increment,
                  `pid` int NOT NULL DEFAULT 0,
                  `filename` varchar(120) NOT NULL DEFAULT '',
                  `filetype` varchar(120) NOT NULL DEFAULT '',
                  `filesize` varchar(120) NOT NULL DEFAULT '',
                  `attachment` longblob NOT NULL,
                  `downloads` int NOT NULL DEFAULT 0,
                  `img_size` VARCHAR(9) NOT NULL,
                  `parentid` INT NOT NULL DEFAULT '0',
                  `subdir` VARCHAR( 15 ) NOT NULL,
                  `uid` INT NOT NULL DEFAULT '0',
                  `updatetime` TIMESTAMP NOT NULL DEFAULT current_timestamp,
                  PRIMARY KEY  (`aid`),
                  KEY `pid` (`pid`),
                  KEY `parentid` (`parentid`),
                  KEY `uid` (`uid`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'hold_favorites':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `tid` int NOT NULL DEFAULT 0,
                  `username` varchar(32) NOT NULL DEFAULT '',
                  `type` varchar(32) NOT NULL DEFAULT '',
                  KEY `tid` (`tid`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'hold_posts':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `fid` smallint NOT NULL DEFAULT '0',
                  `tid` int NOT NULL DEFAULT '0',
                  `pid` int NOT NULL auto_increment,
                  `author` varchar(32) NOT NULL DEFAULT '',
                  `message` text NOT NULL,
                  `subject` tinytext NOT NULL,
                  `dateline` int NOT NULL DEFAULT 0,
                  `icon` varchar(50) DEFAULT NULL,
                  `usesig` varchar(15) NOT NULL DEFAULT '',
                  `useip` varchar(39) NOT NULL DEFAULT '',
                  `bbcodeoff` varchar(15) NOT NULL DEFAULT '',
                  `smileyoff` varchar(15) NOT NULL DEFAULT '',
                  `newtid` int NOT NULL DEFAULT '0',
                  PRIMARY KEY  (`pid`),
                  KEY `fid` (`fid`),
                  KEY `dateline` (`dateline`),
                  KEY `author` (author (8)),
                  KEY `thread_optimize` (`tid`, `dateline`, `pid`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'hold_threads':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `tid` int NOT NULL auto_increment,
                  `fid` smallint NOT NULL DEFAULT 0,
                  `subject` varchar(128) NOT NULL DEFAULT '',
                  `icon` varchar(75) NOT NULL DEFAULT '',
                  `lastpost` varchar(54) NOT NULL DEFAULT '',
                  `views` bigint NOT NULL DEFAULT 0,
                  `replies` int NOT NULL DEFAULT 0,
                  `author` varchar(32) NOT NULL DEFAULT '',
                  `closed` varchar(15) NOT NULL DEFAULT '',
                  `topped` tinyint NOT NULL DEFAULT 0,
                  `pollopts` tinyint NOT NULL DEFAULT 0,
                  PRIMARY KEY  (`tid`),
                  KEY `lastpost` (`lastpost`),
                  KEY `author` (author (8)),
                  KEY `closed` (`closed`),
                  KEY `forum_optimize` (`fid`, `topped`, `lastpost`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'hold_vote_desc':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `vote_id` mediumint unsigned NOT NULL auto_increment,
                  `topic_id` INT UNSIGNED NOT NULL,
                  PRIMARY KEY  (`vote_id`),
                  KEY `topic_id` (`topic_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'hold_vote_results':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `vote_id` mediumint unsigned NOT NULL DEFAULT '0',
                  `vote_option_id` tinyint unsigned NOT NULL DEFAULT '0',
                  `vote_option_text` varchar(255) NOT NULL DEFAULT '',
                  `vote_result` int NOT NULL DEFAULT '0',
                  KEY `vote_option_id` (`vote_option_id`),
                  KEY `vote_id` (`vote_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'logs':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `username` varchar(32) NOT NULL,
                  `action` varchar(64) NOT NULL DEFAULT '',
                  `fid` smallint NOT NULL DEFAULT 0,
                  `tid` int NOT NULL DEFAULT 0,
                  `date` int NOT NULL DEFAULT 0,
                  KEY `username` (username (8)),
                  KEY `action` (action (8)),
                  INDEX ( `fid` ),
                  INDEX ( `tid` ),
                  INDEX ( `date` )
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'members':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `uid` int NOT NULL auto_increment,
                  `username` varchar(32) NOT NULL DEFAULT '',
                  `password` varchar(32) NOT NULL DEFAULT '',
                  `regdate` int NOT NULL DEFAULT 0,
                  `postnum` MEDIUMINT NOT NULL DEFAULT 0,
                  `email` varchar(60) NOT NULL DEFAULT '',
                  `site` varchar(75) NOT NULL DEFAULT '',
                  `status` varchar(35) NOT NULL DEFAULT '',
                  `location` varchar(50) NOT NULL DEFAULT '',
                  `bio` text NOT NULL,
                  `sig` text NOT NULL,
                  `timeoffset` DECIMAL(4,2) NOT NULL DEFAULT 0,
                  `avatar` varchar(120) DEFAULT NULL,
                  `customstatus` varchar(250) NOT NULL DEFAULT '',
                  `theme` smallint NOT NULL DEFAULT 0,
                  `bday` varchar(10) NOT NULL DEFAULT '0000-00-00',
                  `langfile` varchar(40) NOT NULL DEFAULT '',
                  `tpp` smallint NOT NULL DEFAULT 30,
                  `ppp` smallint NOT NULL DEFAULT 30,
                  `newsletter` char(3) NOT NULL DEFAULT '',
                  `regip` varchar(39) NOT NULL DEFAULT '',
                  `timeformat` int NOT NULL DEFAULT 0,
                  `ban` varchar(15) NOT NULL DEFAULT '0',
                  `dateformat` varchar(10) NOT NULL DEFAULT '',
                  `ignoreu2u` text NOT NULL,
                  `lastvisit` int UNSIGNED NOT NULL DEFAULT 0,
                  `mood` varchar(128) NOT NULL DEFAULT 'Not Set',
                  `pwdate` int NOT NULL DEFAULT 0,
                  `invisible` SET('1','0') DEFAULT 0,
                  `u2ufolders` text NOT NULL,
                  `saveogu2u` char(3) NOT NULL DEFAULT '',
                  `emailonu2u` char(3) NOT NULL DEFAULT '',
                  `u2ualert` TINYINT NOT NULL DEFAULT '0',
                  `bad_login_date` int unsigned NOT NULL DEFAULT 0,
                  `bad_login_count` int unsigned NOT NULL DEFAULT 0,
                  `bad_session_date` int unsigned NOT NULL DEFAULT 0,
                  `bad_session_count` int unsigned NOT NULL DEFAULT 0,
                  `sub_each_post` varchar(3) NOT NULL DEFAULT 'no',
                  `waiting_for_mod` varchar(3) NOT NULL DEFAULT 'no',
                  `password2` varchar(255) NOT NULL DEFAULT '',
                  `post_date` int unsigned NOT NULL DEFAULT 0,
                  PRIMARY KEY  (`uid`),
                  UNIQUE KEY `userunique` (`username`),
                  KEY `status` (`status`),
                  KEY `postnum` (`postnum`),
                  KEY `email` (`email`),
                  KEY `regdate` (`regdate`),
                  KEY `lastvisit` (`lastvisit`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'posts':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `fid` smallint NOT NULL DEFAULT '0',
                  `tid` int NOT NULL DEFAULT '0',
                  `pid` int NOT NULL auto_increment,
                  `author` varchar(32) NOT NULL DEFAULT '',
                  `message` text NOT NULL,
                  `subject` tinytext NOT NULL,
                  `dateline` int NOT NULL DEFAULT 0,
                  `icon` varchar(50) DEFAULT NULL,
                  `usesig` varchar(15) NOT NULL DEFAULT '',
                  `useip` varchar(39) NOT NULL DEFAULT '',
                  `bbcodeoff` varchar(15) NOT NULL DEFAULT '',
                  `smileyoff` varchar(15) NOT NULL DEFAULT '',
                  PRIMARY KEY  (`pid`),
                  KEY `fid` (`fid`),
                  KEY `dateline` (`dateline`),
                  KEY `author` (author (8)),
                  KEY `thread_optimize` (`tid`, `dateline`, `pid`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'ranks':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `title` varchar(100) NOT NULL DEFAULT '',
                  `posts` MEDIUMINT DEFAULT 0,
                  `id` smallint NOT NULL auto_increment,
                  `stars` smallint NOT NULL DEFAULT 0,
                  `allowavatars` char(3) NOT NULL DEFAULT '',
                  `avatarrank` varchar(90) DEFAULT NULL,
                  PRIMARY KEY  (`id`),
                  KEY `title` (`title`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'restricted':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `name` varchar(32) NOT NULL DEFAULT '',
                  `id` smallint NOT NULL auto_increment,
                  `case_sensitivity` ENUM('0', '1') DEFAULT '1' NOT NULL,
                  `partial` ENUM('0', '1') DEFAULT '1' NOT NULL,
                  PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'sessions':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `token` varchar(32) NOT NULL,
                  `username` varchar(32) NOT NULL,
                  `login_date` int unsigned NOT NULL,
                  `expire` int unsigned NOT NULL,
                  `regenerate` int unsigned NOT NULL,
                  `replaces` varchar(32) NOT NULL,
                  `agent` varchar(255) NOT NULL,
                  `name` varchar(80) NOT NULL DEFAULT '',
                  PRIMARY KEY (`token`),
                  KEY `username` (`username`),
                  KEY `replaces` (`replaces`(6)),
                  KEY `expire` (`expire`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'settings':
                // Note support for VARCHAR types longer than 255 is not available prior to MySQL v5.0.3.
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `name` varchar(32) NOT NULL,
                  `value` text NOT NULL,
                  PRIMARY KEY (`name`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'smilies':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `type` varchar(15) NOT NULL DEFAULT '',
                  `code` varchar(40) NOT NULL DEFAULT '',
                  `url` varchar(40) NOT NULL DEFAULT '',
                  `id` smallint NOT NULL auto_increment,
                  PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'themes':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `themeid` smallint NOT NULL auto_increment,
                  `name` varchar(32) NOT NULL DEFAULT '',
                  `bgcolor` varchar(25) NOT NULL DEFAULT '',
                  `altbg1` varchar(15) NOT NULL DEFAULT '',
                  `altbg2` varchar(15) NOT NULL DEFAULT '',
                  `link` varchar(15) NOT NULL DEFAULT '',
                  `bordercolor` varchar(15) NOT NULL DEFAULT '',
                  `header` varchar(15) NOT NULL DEFAULT '',
                  `headertext` varchar(15) NOT NULL DEFAULT '',
                  `top` varchar(15) NOT NULL DEFAULT '',
                  `catcolor` varchar(15) NOT NULL DEFAULT '',
                  `tabletext` varchar(15) NOT NULL DEFAULT '',
                  `text` varchar(15) NOT NULL DEFAULT '',
                  `borderwidth` varchar(15) NOT NULL DEFAULT '',
                  `tablewidth` varchar(15) NOT NULL DEFAULT '',
                  `tablespace` varchar(15) NOT NULL DEFAULT '',
                  `font` varchar(40) NOT NULL DEFAULT '',
                  `fontsize` varchar(40) NOT NULL DEFAULT '',
                  `boardimg` varchar(128) DEFAULT NULL,
                  `imgdir` varchar(120) NOT NULL DEFAULT '',
                  `admdir` VARCHAR( 120 ) NOT NULL DEFAULT 'images/admin',
                  `smdir` varchar(120) NOT NULL DEFAULT 'images/smilies',
                  `cattext` varchar(15) NOT NULL DEFAULT '',
                  `version` int unsigned NOT NULL DEFAULT 0,
                  PRIMARY KEY  (`themeid`),
                  KEY `name` (`name`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'threads':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `tid` int NOT NULL auto_increment,
                  `fid` smallint NOT NULL DEFAULT 0,
                  `subject` varchar(128) NOT NULL DEFAULT '',
                  `icon` varchar(75) NOT NULL DEFAULT '',
                  `lastpost` varchar(54) NOT NULL DEFAULT '',
                  `views` bigint NOT NULL DEFAULT 0,
                  `replies` int NOT NULL DEFAULT 0,
                  `author` varchar(32) NOT NULL DEFAULT '',
                  `closed` varchar(15) NOT NULL DEFAULT '',
                  `topped` tinyint NOT NULL DEFAULT 0,
                  `pollopts` tinyint NOT NULL DEFAULT 0,
                  PRIMARY KEY  (`tid`),
                  KEY `lastpost` (`lastpost`),
                  KEY `author` (author (8)),
                  KEY `closed` (`closed`),
                  KEY `forum_optimize` (`fid`, `topped`, `lastpost`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'tokens':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `token` varchar(32) NOT NULL,
                  `username` varchar(32) NOT NULL,
                  `action` varchar(32) NOT NULL,
                  `object` varchar(32) NOT NULL,
                  `expire` int unsigned NOT NULL,
                  PRIMARY KEY (`token`),
                  KEY `expire` (`expire`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'u2u':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `u2uid` bigint NOT NULL auto_increment,
                  `msgto` varchar(32) NOT NULL DEFAULT '',
                  `msgfrom` varchar(32) NOT NULL DEFAULT '',
                  `type` set('incoming','outgoing','draft') NOT NULL DEFAULT '',
                  `owner` varchar(32) NOT NULL DEFAULT '',
                  `folder` varchar(32) NOT NULL DEFAULT '',
                  `subject` varchar(64) NOT NULL DEFAULT '',
                  `message` text NOT NULL,
                  `dateline` int NOT NULL DEFAULT 0,
                  `readstatus` set('yes','no') NOT NULL DEFAULT '',
                  `sentstatus` set('yes','no') NOT NULL DEFAULT '',
                  PRIMARY KEY  (`u2uid`),
                  KEY `msgto` (msgto (8)),
                  KEY `msgfrom` (msgfrom (8)),
                  KEY `folder` (folder (8)),
                  KEY `readstatus` (`readstatus`),
                  KEY `owner` (owner (8))
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'vote_desc':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `vote_id` mediumint unsigned NOT NULL auto_increment,
                  `topic_id` INT UNSIGNED NOT NULL,
                  PRIMARY KEY  (`vote_id`),
                  KEY `topic_id` (`topic_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'vote_results':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `vote_id` mediumint unsigned NOT NULL DEFAULT '0',
                  `vote_option_id` tinyint unsigned NOT NULL DEFAULT '0',
                  `vote_option_text` varchar(255) NOT NULL DEFAULT '',
                  `vote_result` int NOT NULL DEFAULT '0',
                  KEY `vote_option_id` (`vote_option_id`),
                  KEY `vote_id` (`vote_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'vote_voters':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `vote_id` mediumint unsigned NOT NULL DEFAULT '0',
                  `vote_user_id` mediumint NOT NULL DEFAULT '0',
                  `vote_user_ip` varchar(39) NOT NULL DEFAULT '',
                  KEY `vote_id` (`vote_id`),
                  KEY `vote_user_id` (`vote_user_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'whosonline':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `username` varchar(32) NOT NULL DEFAULT '',
                  `ip` varchar(39) NOT NULL DEFAULT '',
                  `time` int NOT NULL DEFAULT 0,
                  `location` varchar(150) NOT NULL DEFAULT '',
                  `invisible` SET('1','0') DEFAULT '0',
                  KEY `username` (username (8)),
                  KEY `ip` (`ip`),
                  KEY `time` (`time`),
                  KEY `invisible` (`invisible`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            case 'words':
                $sql =
                "CREATE TABLE IF NOT EXISTS " . $this->vars->tablepre . $name." (
                  `find` varchar(60) NOT NULL DEFAULT '',
                  `replace1` varchar(60) NOT NULL DEFAULT '',
                  `id` smallint NOT NULL auto_increment,
                  PRIMARY KEY  (`id`),
                  KEY `find` (`find`)
                ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
                break;
            default:
                exit('Fatal Error: Invalid parameter for xmb_schema_create().');
        } // switch
        return $sql;
    }

    /**
     * Generates an array of table names in the XMB schema.
     *
     * @since 1.9.11.11 Formerly xmb_schema_list()
     * @since 1.10.00
     * @return array
     */
    public function listTables(): array
    {
        return [
            'attachments',
            'banned',
            'buddys',
            'captchaimages',
            'favorites',
            'forums',
            'hold_attachments',
            'hold_favorites',
            'hold_posts',
            'hold_threads',
            'hold_vote_desc',
            'hold_vote_results',
            'logs',
            'members',
            'posts',
            'ranks',
            'restricted',
            'sessions',
            'settings',
            'smilies',
            'themes',
            'threads',
            'tokens',
            'u2u',
            'whosonline',
            'words',
            'vote_desc',
            'vote_results',
            'vote_voters',
        ];
    }

    /**
     * Determines if a specific table already exists in the database.
     *
     * @since 1.9.11.11 Formerly xmb_schema_table_exists()
     * @since 1.10.00
     * @param string $name The name of the XMB table, with no prefix.
     * @return bool
     */
    public function tableExists(string $name): bool
    {
        $sqlname = $this->db->like_escape($this->vars->tablepre . $name);

        $result = $this->db->query("SHOW TABLES LIKE '$sqlname'");
        $status = $this->db->num_rows($result) === 1;
        $this->db->free_result($result);

        return $status;
    }

    /**
     * Determines if a specific index already exists in the database.
     *
     * @since 1.9.11.11 Formerly xmb_schema_index_exists()
     * @since 1.10.00
     * @param string $table The name of the XMB table, with no prefix.
     * @param string $column The name of the column on which you want to find any index. Set to '' if you want to search by index name only.
     * @param string $index Optional. The name of the index to check.
     * @param string $subpart Optional. The number of indexed characters, if you want to only find indexes that have this attribute.
     * @return bool
     */
    public function indexExists(string $table, string $column, string $index = '', string $subpart = ''): bool
    {
        if (empty($column) && empty($index)) throw new InvalidArgumentException('The column and the index must not be empty');

        $result = $this->db->query("SHOW INDEX FROM " . $this->vars->tablepre . $table);

        while ($row = $this->db->fetch_array($result)) {
            if (!empty($column) && $row['Column_name'] !== $column) {
                continue;
            } elseif (!empty($index) && $row['Key_name'] !== $index) {
                continue;
            } elseif (!empty($subpart) && $row['Sub_part'] !== $subpart) {
                continue;
            } else {
                $this->db->free_result($result);
                return true;
            }
        }

        $this->db->free_result($result);
        return false;
    }

    /**
     * Get the names of all existing columns in a table.
     *
     * @since 1.9.11.11 Formerly xmb_schema_columns_list()
     * @since 1.10.00
     * @param string $table The name of the XMB table, with no prefix.
     * @return array
     */
    public function listColumns(string $table): array
    {
        $columns = [];

        $result = $this->db->query("DESCRIBE " . $this->vars->tablepre . $table);
        while ($row = $this->db->fetch_array($result)) {
            $columns[] = $row['Field'];
        }
        $this->db->free_result($result);

        return $columns;
    }
}
