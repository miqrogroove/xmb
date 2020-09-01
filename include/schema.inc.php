<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12-alpha  Do not use this experimental software after 1 October 2020.
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2020, The XMB Group
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

define('XMB_SCHEMA_VER', 5);

/**
 * Executes logic necessary to install or uninstall one of the XMB tables.
 *
 * @since 1.9.11.11
 * @param string $action Must be 'drop', 'create', or 'overwrite'.
 * @param string $name The name of the XMB table, with no prefix.
 */
function xmb_schema_table($action, $name) {
    global $db;

    if ('drop' == $action || 'overwrite' == $action) {
        $db->query(xmb_schema_drop($name));
    }
    if ('create' == $action || 'overwrite' == $action) {
        $db->query(xmb_schema_create($name));
    }
}

/**
 * Generates a DROP TABLE query for the XMB schema in MySQL.
 *
 * @since 1.9.11.11
 * @param string $name The name of the XMB table, with no prefix.
 * @return string
 */
function xmb_schema_drop($name){
    return "DROP TABLE IF EXISTS ".X_PREFIX.$name;
}

/**
 * Generates a CREATE TABLE query for the XMB schema in MySQL.
 *
 * @since 1.9.11.11
 * @param string $name The name of the XMB table, with no prefix.
 * @return string
 */
function xmb_schema_create($name){
    switch($name) {
    case 'attachments':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `aid` int(10) NOT NULL auto_increment,
          `pid` int(10) NOT NULL default 0,
          `filename` varchar(120) NOT NULL default '',
          `filetype` varchar(120) NOT NULL default '',
          `filesize` varchar(120) NOT NULL default '',
          `attachment` longblob NOT NULL,
          `downloads` int(10) NOT NULL default 0,
          `img_size` VARCHAR(9) NOT NULL,
          `parentid` INT NOT NULL DEFAULT '0',
          `subdir` VARCHAR( 15 ) NOT NULL,
          `uid` INT NOT NULL DEFAULT '0',
          `updatetime` TIMESTAMP NOT NULL default current_timestamp,
          PRIMARY KEY  (`aid`),
          KEY `pid` (`pid`),
          KEY `parentid` (`parentid`),
          KEY `uid` (`uid`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'banned':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `ip1` smallint(3) NOT NULL default 0,
          `ip2` smallint(3) NOT NULL default 0,
          `ip3` smallint(3) NOT NULL default 0,
          `ip4` smallint(3) NOT NULL default 0,
          `dateline` int(10) NOT NULL default 0,
          `id` smallint(6) NOT NULL AUTO_INCREMENT,
          PRIMARY KEY  (`id`),
          KEY `ip1` (`ip1`),
          KEY `ip2` (`ip2`),
          KEY `ip3` (`ip3`),
          KEY `ip4` (`ip4`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'buddys':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `username` varchar(32) NOT NULL default '',
          `buddyname` varchar(32) NOT NULL default '',
          KEY `username` (username (8))
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'captchaimages':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `imagehash` varchar(32) NOT NULL default '',
          `imagestring` varchar(12) NOT NULL default '',
          `dateline` int(10) NOT NULL default '0',
          KEY `dateline` (`dateline`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'favorites':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `tid` int(10) NOT NULL default 0,
          `username` varchar(32) NOT NULL default '',
          `type` varchar(32) NOT NULL default '',
          KEY `tid` (`tid`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'forums':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `type` varchar(15) NOT NULL default '',
          `fid` smallint(6) NOT NULL auto_increment,
          `name` varchar(128) NOT NULL default '',
          `status` varchar(15) NOT NULL default '',
          `lastpost` varchar(54) NOT NULL default '',
          `moderator` varchar(100) NOT NULL default '',
          `displayorder` smallint(6) NOT NULL default 0,
          `description` text,
          `allowhtml` char(3) NOT NULL default '',
          `allowsmilies` char(3) NOT NULL default '',
          `allowbbcode` char(3) NOT NULL default '',
          `userlist` text NOT NULL,
          `theme` smallint(3) NOT NULL default 0,
          `posts` int(10) NOT NULL default 0,
          `threads` int(10) NOT NULL default 0,
          `fup` smallint(6) NOT NULL default 0,
          `postperm` varchar(11) NOT NULL default '0,0,0,0',
          `allowimgcode` char(3) NOT NULL default '',
          `attachstatus` varchar(15) NOT NULL default '',
          `password` varchar(32) NOT NULL default '',
          PRIMARY KEY  (`fid`),
          KEY `fup` (`fup`),
          KEY `type` (`type`),
          KEY `displayorder` (`displayorder`),
          KEY `status` (`status`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'lang_base':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `langid` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
          `devname` VARCHAR( 20 ) NOT NULL ,
          UNIQUE ( `devname` )
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='List of Installed Languages'";
        break;
    case 'lang_keys':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `phraseid` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
          `langkey` VARCHAR( 30 ) NOT NULL ,
          UNIQUE ( `langkey` )
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='List of Translation Variables'";
        break;
    case 'lang_text':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `langid` TINYINT UNSIGNED NOT NULL ,
          `phraseid` SMALLINT UNSIGNED NOT NULL ,
          `cdata` BLOB NOT NULL ,
          PRIMARY KEY `langid` ( `langid` , `phraseid` ) ,
          INDEX ( `phraseid` )
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Translation Table'";
        break;
    case 'logs':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
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
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'members':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `uid` int(12) NOT NULL auto_increment,
          `username` varchar(32) NOT NULL default '',
          `password` varchar(32) NOT NULL default '',
          `regdate` int(10) NOT NULL default 0,
          `postnum` MEDIUMINT NOT NULL DEFAULT 0,
          `email` varchar(60) NOT NULL default '',
          `site` varchar(75) NOT NULL default '',
          `aim` varchar(40) NOT NULL default '',
          `status` varchar(35) NOT NULL default '',
          `location` varchar(50) NOT NULL default '',
          `bio` text NOT NULL,
          `sig` text NOT NULL,
          `showemail` varchar(15) NOT NULL default '',
          `timeoffset` DECIMAL(4,2) NOT NULL default 0,
          `icq` varchar(30) NOT NULL default '',
          `avatar` varchar(120) default NULL,
          `yahoo` varchar(40) NOT NULL default '',
          `customstatus` varchar(250) NOT NULL default '',
          `theme` smallint(3) NOT NULL default 0,
          `bday` varchar(10) NOT NULL default '0000-00-00',
          `langfile` varchar(40) NOT NULL default '',
          `tpp` smallint(6) NOT NULL default 0,
          `ppp` smallint(6) NOT NULL default 0,
          `newsletter` char(3) NOT NULL default '',
          `regip` varchar(15) NOT NULL default '',
          `timeformat` int(5) NOT NULL default 0,
          `msn` varchar(40) NOT NULL default '',
          `ban` varchar(15) NOT NULL default '0',
          `dateformat` varchar(10) NOT NULL default '',
          `ignoreu2u` text NOT NULL,
          `lastvisit` int(10) unsigned NOT NULL default 0,
          `mood` varchar(128) NOT NULL default 'Not Set',
          `pwdate` int(10) NOT NULL default 0,
          `invisible` SET('1','0') default 0,
          `u2ufolders` text NOT NULL,
          `saveogu2u` char(3) NOT NULL default '',
          `emailonu2u` char(3) NOT NULL default '',
          `useoldu2u` char(3) NOT NULL default '',
          `u2ualert` TINYINT NOT NULL DEFAULT '0',
          `bad_login_date` int(10) unsigned NOT NULL default 0,
          `bad_login_count` int(10) unsigned NOT NULL default 0,
          `bad_session_date` int(10) unsigned NOT NULL default 0,
          `bad_session_count` int(10) unsigned NOT NULL default 0,
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
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `fid` smallint(6) NOT NULL default '0',
          `tid` int(10) NOT NULL default '0',
          `pid` int(10) NOT NULL auto_increment,
          `author` varchar(32) NOT NULL default '',
          `message` text NOT NULL,
          `subject` tinytext NOT NULL,
          `dateline` int(10) NOT NULL default 0,
          `icon` varchar(50) default NULL,
          `usesig` varchar(15) NOT NULL default '',
          `useip` varchar(15) NOT NULL default '',
          `bbcodeoff` varchar(15) NOT NULL default '',
          `smileyoff` varchar(15) NOT NULL default '',
          PRIMARY KEY  (`pid`),
          KEY `fid` (`fid`),
          KEY `dateline` (`dateline`),
          KEY `author` (author (8)),
          KEY `thread_optimize` (`tid`, `dateline`, `pid`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'ranks':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `title` varchar(100) NOT NULL default '',
          `posts` MEDIUMINT DEFAULT 0,
          `id` smallint(5) NOT NULL auto_increment,
          `stars` smallint(6) NOT NULL default 0,
          `allowavatars` char(3) NOT NULL default '',
          `avatarrank` varchar(90) default NULL,
          PRIMARY KEY  (`id`),
          KEY `title` (`title`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'restricted':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `name` varchar(32) NOT NULL default '',
          `id` smallint(6) NOT NULL auto_increment,
          `case_sensitivity` ENUM('0', '1') DEFAULT '1' NOT NULL,
          `partial` ENUM('0', '1') DEFAULT '1' NOT NULL,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'sessions':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `token` varchar(32) NOT NULL,
          `username` varchar(32) NOT NULL,
          `login_date` int(10) unsigned NOT NULL,
          `expire` int(10) unsigned NOT NULL,
          `regenerate` int(10) unsigned NOT NULL,
          `replaces` varchar(32) NOT NULL,
          `agent` varchar(255) NOT NULL,
          PRIMARY KEY (`token`),
          KEY `username` (`username`),
          KEY `replaces` (`replaces`(6))
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'settings':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `langfile` varchar(34) NOT NULL default 'English',
          `bbname` varchar(32) NOT NULL default 'Your Forums',
          `postperpage` smallint(5) NOT NULL default 25,
          `topicperpage` smallint(5) NOT NULL default 30,
          `hottopic` smallint(5) NOT NULL default 20,
          `theme` smallint(3) NOT NULL default 1,
          `bbstatus` char(3) NOT NULL default 'on',
          `whosonlinestatus` char(3) NOT NULL default 'on',
          `regstatus` char(3) NOT NULL default 'on',
          `bboffreason` text NOT NULL,
          `regviewonly` char(3) NOT NULL default 'off',
          `floodctrl` smallint(5) NOT NULL default 5,
          `memberperpage` smallint(5) NOT NULL default 45,
          `catsonly` char(3) NOT NULL default 'off',
          `hideprivate` char(3) NOT NULL default 'on',
          `emailcheck` char(3) NOT NULL default 'off',
          `bbrules` char(3) NOT NULL default 'off',
          `bbrulestxt` text NOT NULL,
          `searchstatus` char(3) NOT NULL default 'on',
          `faqstatus` char(3) NOT NULL default 'on',
          `memliststatus` char(3) NOT NULL default 'on',
          `sitename` varchar(50) NOT NULL default 'YourDomain.com',
          `siteurl` varchar(60) NOT NULL default '',
          `avastatus` varchar(4) NOT NULL default 'on',
          `u2uquota` smallint(5) NOT NULL default 600,
          `gzipcompress` varchar(30) NOT NULL default 'on',
          `coppa` char(3) NOT NULL default 'off',
          `timeformat` smallint(2) NOT NULL default 12,
          `adminemail` varchar(60) NOT NULL default 'webmaster@domain.ext',
          `dateformat` varchar(10) NOT NULL default 'dd-mm-yyyy',
          `sigbbcode` char(3) NOT NULL default 'on',
          `sightml` char(3) NOT NULL default 'off',
          `reportpost` char(3) NOT NULL default 'on',
          `bbinsert` char(3) NOT NULL default 'on',
          `smileyinsert` char(3) NOT NULL default 'on',
          `doublee` char(3) NOT NULL default 'off',
          `smtotal` varchar(15) NOT NULL default '16',
          `smcols` varchar(15) NOT NULL default '4',
          `editedby` char(3) NOT NULL default 'off',
          `dotfolders` char(3) NOT NULL default 'on',
          `attachimgpost` char(3) NOT NULL default 'on',
          `todaysposts` char(3) NOT NULL default 'on',
          `stats` char(3) NOT NULL default 'on',
          `authorstatus` char(3) NOT NULL default 'on',
          `tickerstatus` char(3) NOT NULL default 'on',
          `tickercontents` text NOT NULL,
          `tickerdelay` int(6) NOT NULL default 4000,
          `addtime` DECIMAL(4,2) NOT NULL default 0,
          `max_avatar_size` varchar(9) NOT NULL default '100x100',
          `footer_options` varchar(45) NOT NULL default 'queries-phpsql-loadtimes-totaltime',
          `space_cats` char(3) NOT NULL default 'no',
          `spellcheck` char(3) NOT NULL default 'off',
          `allowrankedit` char(3) NOT NULL default 'on',
          `notifyonreg` SET('off','u2u','email') NOT NULL default 'off',
          `subject_in_title` char(3) NOT NULL default 'off',
          `def_tz` decimal(4,2) NOT NULL default '0.00',
          `indexshowbar` tinyint(2) NOT NULL default 2,
          `resetsigs` char(3) NOT NULL default 'off',
          `pruneusers` smallint(3) NOT NULL default 0,
          `ipreg` char(3) NOT NULL default 'on',
          `maxdayreg` smallint(5) UNSIGNED NOT NULL default 25,
          `maxattachsize` int(10) UNSIGNED NOT NULL default 256000,
          `captcha_status` set('on','off') NOT NULL default 'on',
          `captcha_reg_status` set('on','off') NOT NULL default 'on',
          `captcha_post_status` set('on','off') NOT NULL default 'on',
          `captcha_search_status` set('on','off') NOT NULL default 'off',
          `captcha_code_charset` varchar(128) NOT NULL default 'A-Z',
          `captcha_code_length` int(2) NOT NULL default '8',
          `captcha_code_casesensitive` set('on','off') NOT NULL default 'off',
          `captcha_code_shadow` set('on','off') NOT NULL default 'off',
          `captcha_image_type` varchar(4) NOT NULL default 'png',
          `captcha_image_width` int(3) NOT NULL default '250',
          `captcha_image_height` int(3) NOT NULL default '50',
          `captcha_image_bg` varchar(128) NOT NULL default '',
          `captcha_image_dots` int(3) NOT NULL default '0',
          `captcha_image_lines` int(2) NOT NULL default '70',
          `captcha_image_fonts` varchar(128) NOT NULL default '',
          `captcha_image_minfont` int(2) NOT NULL default '16',
          `captcha_image_maxfont` int(2) NOT NULL default '25',
          `captcha_image_color` set('on','off') NOT NULL default 'off',
          `showsubforums` set('on','off') NOT NULL default 'off',
          `regoptional` set('on','off') NOT NULL default 'off',
          `quickreply_status` set('on','off') NOT NULL default 'on',
          `quickjump_status` set('on','off') NOT NULL default 'on',
          `index_stats` set('on','off') NOT NULL default 'on',
          `onlinetodaycount` smallint(5) NOT NULL default '50',
          `onlinetoday_status` set('on','off') NOT NULL default 'on',
          `attach_remote_images` SET('on','off') NOT NULL DEFAULT 'off',
          `files_min_disk_size` MEDIUMINT NOT NULL DEFAULT '9216',
          `files_storage_path` VARCHAR( 100 ) NOT NULL,
          `files_subdir_format` TINYINT NOT NULL DEFAULT '1',
          `file_url_format` TINYINT NOT NULL DEFAULT '1',
          `files_virtual_url` VARCHAR(60) NOT NULL,
          `filesperpost` TINYINT NOT NULL DEFAULT '10',
          `ip_banning` SET('on','off') NOT NULL DEFAULT 'off',
          `max_image_size` VARCHAR(9) NOT NULL DEFAULT '1000x1000',
          `max_thumb_size` VARCHAR(9) NOT NULL DEFAULT '200x200',
          `schema_version` TINYINT UNSIGNED NOT NULL DEFAULT ".XMB_SCHEMA_VER."
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'smilies':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `type` varchar(15) NOT NULL default '',
          `code` varchar(40) NOT NULL default '',
          `url` varchar(40) NOT NULL default '',
          `id` smallint(6) NOT NULL auto_increment,
          PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'templates':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `id` smallint(6) NOT NULL auto_increment,
          `name` varchar(32) NOT NULL default '',
          `template` text NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `name` (`name`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'themes':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `themeid` smallint(3) NOT NULL auto_increment,
          `name` varchar(32) NOT NULL default '',
          `bgcolor` varchar(25) NOT NULL default '',
          `altbg1` varchar(15) NOT NULL default '',
          `altbg2` varchar(15) NOT NULL default '',
          `link` varchar(15) NOT NULL default '',
          `bordercolor` varchar(15) NOT NULL default '',
          `header` varchar(15) NOT NULL default '',
          `headertext` varchar(15) NOT NULL default '',
          `top` varchar(15) NOT NULL default '',
          `catcolor` varchar(15) NOT NULL default '',
          `tabletext` varchar(15) NOT NULL default '',
          `text` varchar(15) NOT NULL default '',
          `borderwidth` varchar(15) NOT NULL default '',
          `tablewidth` varchar(15) NOT NULL default '',
          `tablespace` varchar(15) NOT NULL default '',
          `font` varchar(40) NOT NULL default '',
          `fontsize` varchar(40) NOT NULL default '',
          `boardimg` varchar(128) default NULL,
          `imgdir` varchar(120) NOT NULL default '',
          `admdir` VARCHAR( 120 ) NOT NULL DEFAULT 'images/admin',
          `smdir` varchar(120) NOT NULL default 'images/smilies',
          `cattext` varchar(15) NOT NULL default '',
          PRIMARY KEY  (`themeid`),
          KEY `name` (`name`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'threads':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `tid` int(10) NOT NULL auto_increment,
          `fid` smallint(6) NOT NULL default 0,
          `subject` varchar(128) NOT NULL default '',
          `icon` varchar(75) NOT NULL default '',
          `lastpost` varchar(54) NOT NULL default '',
          `views` bigint(32) NOT NULL default 0,
          `replies` int(10) NOT NULL default 0,
          `author` varchar(32) NOT NULL default '',
          `closed` varchar(15) NOT NULL default '',
          `topped` tinyint(1) NOT NULL default 0,
          `pollopts` tinyint(1) NOT NULL default 0,
          PRIMARY KEY  (`tid`),
          KEY `lastpost` (`lastpost`),
          KEY `author` (author (8)),
          KEY `closed` (`closed`),
          KEY `forum_optimize` (`fid`, `topped`, `lastpost`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'u2u':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `u2uid` bigint(10) NOT NULL auto_increment,
          `msgto` varchar(32) NOT NULL default '',
          `msgfrom` varchar(32) NOT NULL default '',
          `type` set('incoming','outgoing','draft') NOT NULL default '',
          `owner` varchar(32) NOT NULL default '',
          `folder` varchar(32) NOT NULL default '',
          `subject` varchar(64) NOT NULL default '',
          `message` text NOT NULL,
          `dateline` int(10) NOT NULL default 0,
          `readstatus` set('yes','no') NOT NULL default '',
          `sentstatus` set('yes','no') NOT NULL default '',
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
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `vote_id` mediumint(8) unsigned NOT NULL auto_increment,
          `topic_id` INT UNSIGNED NOT NULL,
          `vote_text` text NOT NULL,
          `vote_start` int(11) NOT NULL default '0',
          `vote_length` int(11) NOT NULL default '0',
          PRIMARY KEY  (`vote_id`),
          KEY `topic_id` (`topic_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'vote_results':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `vote_id` mediumint(8) unsigned NOT NULL default '0',
          `vote_option_id` tinyint(4) unsigned NOT NULL default '0',
          `vote_option_text` varchar(255) NOT NULL default '',
          `vote_result` int(11) NOT NULL default '0',
          KEY `vote_option_id` (`vote_option_id`),
          KEY `vote_id` (`vote_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'vote_voters':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `vote_id` mediumint(8) unsigned NOT NULL default '0',
          `vote_user_id` mediumint(8) NOT NULL default '0',
          `vote_user_ip` char(8) NOT NULL default '',
          KEY `vote_id` (`vote_id`),
          KEY `vote_user_id` (`vote_user_id`),
          KEY `vote_user_ip` (`vote_user_ip`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'whosonline':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `username` varchar(32) NOT NULL default '',
          `ip` varchar(15) NOT NULL default '',
          `time` int(10) NOT NULL default 0,
          `location` varchar(150) NOT NULL default '',
          `invisible` SET('1','0') default '0',
          KEY `username` (username (8)),
          KEY `ip` (`ip`),
          KEY `time` (`time`),
          KEY `invisible` (`invisible`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1";
        break;
    case 'words':
        $sql =
        "CREATE TABLE IF NOT EXISTS ".X_PREFIX.$name." (
          `find` varchar(60) NOT NULL default '',
          `replace1` varchar(60) NOT NULL default '',
          `id` smallint(6) NOT NULL auto_increment,
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
 * @since 1.9.11.11
 * @return array
 */
function xmb_schema_list(): array {
    return [
    'attachments',
    'banned',
    'buddys',
    'captchaimages',
    'favorites',
    'forums',
    'lang_base',
    'lang_keys',
    'lang_text',
    'logs',
    'members',
    'posts',
    'ranks',
    'restricted',
    'sessions',
    'settings',
    'smilies',
    'templates',
    'themes',
    'threads',
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
 * @since 1.9.11.11
 * @param string $name The name of the XMB table, with no prefix.
 * @return bool
 */
function xmb_schema_table_exists($name) {
    global $db;

    $sqlname = $db->like_escape(X_PREFIX.$name);

    $result = $db->query("SHOW TABLES LIKE '$sqlname'");

    return ($db->num_rows($result) > 0);
}

/**
 * Determines if a specific index already exists in the database.
 *
 * @since 1.9.11.11
 * @param string $table The name of the XMB table, with no prefix.
 * @param string $column The name of the column on which you want to find any index. Set to '' if you want to search by index name only.
 * @param string $index Optional. The name of the index to check.
 * @param string $subpart Optional. The number of indexed characters, if you want to only find indexes that have this attribute.
 * @return bool
 */
function xmb_schema_index_exists($table, $column, $index = '', $subpart = '') {
    global $db;

    if (empty($column) && empty($index)) exit('Fatal Error: Invalid parameters for xmb_schema_index_exists().');

    $result = $db->query("SHOW INDEX FROM ".X_PREFIX.$table);

    while ($row = $db->fetch_array($result)) {
        if (!empty($column) && $row['Column_name'] != $column) {
            continue;
        } elseif (!empty($index) && $row['Key_name'] != $index) {
            continue;
        } elseif (!empty($subpart) && $row['Sub_part'] != $subpart) {
            continue;
        } else {
            $db->free_result($result);
            return TRUE;
        }
    }

    $db->free_result($result);
    return FALSE;
}

/**
 * Get the names of all existing columns in a table.
 *
 * @since 1.9.11.11
 * @param string $table The name of the XMB table, with no prefix.
 * @return array
 */
function xmb_schema_columns_list($table) {
    global $db;

    $columns = array();

    $result = $db->query("DESCRIBE ".X_PREFIX.$table);
    while($row = $db->fetch_array($result)) {
        $columns[] = $row['Field'];
    }
    $db->free_result($result);

    return $columns;
}

return;