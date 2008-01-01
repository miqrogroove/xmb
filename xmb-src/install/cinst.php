<?php
/* $Id: cinst.php,v 1.1.2.29 2007/03/12 10:52:37 FunForum Exp $ */
/*
    © 2001 - 2007 Aventure Media & The XMB Development Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
if (!defined('ROOT')) {
    define('ROOT', './');
}

if (!defined('X_INST_ERR')) {
    define('X_INST_ERR', 0);
    define('X_INST_WARN', 1);
    define('X_INST_OK', 2);
    define('X_INST_SKIP', 3);
}

if (!function_exists('show_act')) {
    function show_act($act) {
        $act .= str_repeat('.', (75-strlen($act)));
        echo '<font class="progress">'.$act;
    }
}

if (!function_exists('show_result')) {
    function show_result($type) {
        switch($type) {
            case 0:
                echo '<font class="progressErr">ERROR</font><br>';
                break;

            case 1:
                echo '<font class="progressWarn">WARNING</font><br>';
                break;

             case 2:
                echo '<font class="progressOk">OK</font><br>';
                break;

             case 3:
                echo '<font class="progressSkip">SKIPPED</font><br>';
                break;
        }
        echo "</font>\n";
    }
}

function rmFromDir($path) {
    if (is_dir($path)) {
        $stream = opendir($path);
        while(($file = readdir($stream)) !== false) {
            if ( $file == '.' || $file == '..') {
                continue;
            }
            rmFromDir($path.'/'.$file);
        }
        closedir($stream);
        @rmdir($path);
    } elseif (is_file($path)) {
        @unlink($path);
    }
}

while(ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(1);

require ROOT."xmb.php";

require_once ROOT."config.php";
require_once ROOT."db/$database.php";

$db = new dbstuff;
$tmphost = $dbhost; // dbhost gets cleared by the following method.
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true);

show_act("Checking Super Administrator Account");
    $vUsername = trim($frmUsername);
    $frmPassword = trim($frmPassword);
    $vEmail = trim($frmEmail);

    if ( $vUsername == '' || $frmPassword == '' || $vEmail == '' ) {
        show_result(X_INST_ERR);
        $errStr = 'The username, password or e-mail address cannot be blank or malformed. Please press back and try again.';
        error('Bad super administrator credentials', $errStr);
        exit();
    }

    if ( $frmPassword != $frmPasswordCfm ) {
        show_result(X_INST_ERR);
        $errStr = 'The passwords do not match. Please press back and try again.';
        error('Bad super administrator credentials', $errStr);
        exit();
    }

    // these two are used waaaaay down below.
    $vPassword = md5($frmPassword);
    $myDate = $db->time(time());
show_result(X_INST_OK);

// is XMB already installed?
show_act('Checking for previous XMB Installations');
if (@in_array($tablepre.'settings', $db->fetch_tables($dbname)))
{
    show_result(X_INST_WARN);
        $errStr = 'An existing installation of XMB has been detected in the "'
                . $dbname . '" database located on "'
                . $tmphost . '". <br />If you wish to overwrite this installation, please drop your "'
                . $tablepre . 'settings" table by using <pre>DROP TABLE `'
                . $tablepre . 'settings`;</pre>To install another forum on the same database, go back and enter a different table prefix.';

    error('XMB Already Installed', $errStr);
    exit();
}
show_result(X_INST_OK);

// Start creation of tables....
show_act("Creating ".$tablepre."attachments");

// ------------ xmb_attachments --------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."attachments");
    $r = $db->query("CREATE TABLE ".$tablepre."attachments (
          `aid` int(10) NOT NULL auto_increment,
          `tid` int(10) NOT NULL default 0,
          `pid` int(10) NOT NULL default 0,
          `filename` varchar(120) NOT NULL default '',
          `filetype` varchar(120) NOT NULL default '',
          `filesize` varchar(120) NOT NULL default '',
          `attachment` longblob NOT NULL,
          `downloads` int(10) NOT NULL default 0,
          PRIMARY KEY  (`aid`),
          KEY `tid` (`tid`),
          KEY `pid` (`pid`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."banned");

// ------------ xmb_banned -------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."banned");
    $r = $db->query("CREATE TABLE ".$tablepre."banned (
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
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."buddys");

// ------------ xmb_buddys --------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."buddys");
    $db->query("CREATE TABLE ".$tablepre."buddys (
        `username` varchar(32) NOT NULL default '',
        `buddyname` varchar(32) NOT NULL default '',
        KEY `username` (username (8) )
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."favorites");

// ------------ xmb_favorites -----------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."favorites");
    $db->query("CREATE TABLE ".$tablepre."favorites (
          `tid` int(10) NOT NULL default 0,
          `username` varchar(32) NOT NULL default '',
          `type` varchar(32) NOT NULL default '',
          KEY `tid` (`tid`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."forums");

// ------------ xmb_forums --------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."forums");
    $db->query("CREATE TABLE ".$tablepre."forums (
          `fid` smallint(6) NOT NULL auto_increment,
          `type` varchar(15) NOT NULL default '',
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
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."logs");

// ------------ xmb_logs --------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."logs");
    $db->query("CREATE TABLE ".$tablepre."logs (
          `lid` bigint(32) NOT NULL auto_increment,
          `uid` int(12) NOT NULL,
          `type` SET('admin','mod','user') default 'user',
          `action` varchar(64) NOT NULL default '',
          `data` text NOT NULL,
          `dateline` int(10) NOT NULL default 0,
          PRIMARY KEY  (`lid`),
          KEY `uid` (`uid`),
          KEY `dateline` (`dateline`)
        ) TYPE=MyISAM
    ");
// ---------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."members");

// ------------ xmb_members -------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."members");
    $db->query("CREATE TABLE ".$tablepre."members (
          `uid` int(12) NOT NULL auto_increment,
          `username` varchar(32) NOT NULL default '',
          `password` varchar(32) NOT NULL default '',
          `regdate` int(10) NOT NULL default 0,
          `postnum` smallint(5) NOT NULL default 0,
          `email` varchar(60) NOT NULL default '',
          `site` varchar(75) NOT NULL default '',
          `aim` varchar(40) NOT NULL default '',
          `status` varchar(35) NOT NULL default '',
          `location` varchar(50) NOT NULL default '',
          `bio` text NOT NULL default '',
          `sig` text NOT NULL default '',
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
          `ignoreu2u` text NOT NULL default '',
          `lastvisit` bigint(15) NOT NULL default 0,
          `mood` varchar(128) NOT NULL default 'Not Set',
          `pwdate` int(10) NOT NULL default 0,
          `invisible` SET('1','0') default 0,
          `u2ufolders` text NOT NULL default '',
          `saveogu2u` char(3) NOT NULL default '',
          `emailonu2u` char(3) NOT NULL default '',
          `useoldu2u` char(3) NOT NULL default '',
          `webcam` varchar(75) NOT NULL default '',
          PRIMARY KEY  (`uid`),
          KEY `username` (username (8) ),
          KEY `status` (`status`),
          KEY `postnum` (`postnum`),
          KEY `password` (`password`),
          KEY `email` (`email`),
          KEY `regdate` (`regdate`),
          KEY `invisible` (`invisible`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."posts");

// ------------ xmb_posts ---------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."posts");
    $db->query("CREATE TABLE ".$tablepre."posts (
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
          KEY `tid` (`tid`),
          KEY `dateline` (`dateline`),
          KEY `author` (author (8) )
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."ranks");

// ------------ xmb_ranks ---------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."ranks");
    $db->query("CREATE TABLE ".$tablepre."ranks (
          `title` varchar(100) NOT NULL default '',
          `posts` smallint(5) default 0,
          `id` smallint(5) NOT NULL auto_increment,
          `stars` smallint(6) NOT NULL default 0,
          `allowavatars` char(3) NOT NULL default '',
          `avatarrank` varchar(90) default NULL,
          PRIMARY KEY  (`id`),
          KEY `title` (`title`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."restricted");

// ------------ xmb_restricted ----------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."restricted");
    $db->query("CREATE TABLE ".$tablepre."restricted (
          `name` varchar(32) NOT NULL default '',
          `id` smallint(6) NOT NULL auto_increment,
          `case_sensitivity` ENUM( '0', '1' ) DEFAULT '1' NOT NULL,
          `partial` ENUM( '0', '1' ) DEFAULT '1' NOT NULL,
          PRIMARY KEY  (`id`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."settings");

// ------------ xmb_settings ------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."settings");
    $db->query("CREATE TABLE ".$tablepre."settings (
          `langfile` varchar(34) NOT NULL default '',
          `bbname` varchar(32) NOT NULL default '',
          `postperpage` smallint(5) NOT NULL default 0,
          `topicperpage` smallint(5) NOT NULL default 0,
          `hottopic` smallint(5) NOT NULL default 0,
          `theme` smallint(3) NOT NULL default 1,
          `bbstatus` char(3) NOT NULL default '',
          `whosonlinestatus` char(3) NOT NULL default '',
          `regstatus` char(3) NOT NULL default '',
          `pruneusers` smallint(3) NOT NULL default 0,
          `bboffreason` text NOT NULL,
          `regviewonly` char(3) NOT NULL default '',
          `floodctrl` smallint(5) NOT NULL default 0,
          `memberperpage` smallint(5) NOT NULL default 0,
          `catsonly` char(3) NOT NULL default '',
          `hideprivate` char(3) NOT NULL default '',
          `emailcheck` char(3) NOT NULL default '',
          `bbrules` char(3) NOT NULL default '',
          `bbrulestxt` text NOT NULL,
          `searchstatus` char(3) NOT NULL default '',
          `faqstatus` char(3) NOT NULL default '',
          `memliststatus` char(3) NOT NULL default '',
          `sitename` varchar(50) NOT NULL default '',
          `siteurl` varchar(60) NOT NULL default '',
          `avastatus` varchar(4) NOT NULL default '',
          `u2uquota` smallint(5) NOT NULL default 0,
          `gzipcompress` varchar(30) NOT NULL default '',
          `boardurl` varchar(60) NOT NULL default '',
          `coppa` char(3) NOT NULL default '',
          `timeformat` smallint(2) NOT NULL default 0,
          `adminemail` varchar(32) NOT NULL default '',
          `dateformat` varchar(10) NOT NULL default '',
          `sigbbcode` char(3) NOT NULL default '',
          `sightml` char(3) NOT NULL default '',
          `reportpost` char(3) NOT NULL default '',
          `bbinsert` char(3) NOT NULL default '',
          `smileyinsert` char(3) NOT NULL default '',
          `doublee` char(3) NOT NULL default '',
          `smtotal` varchar(15) NOT NULL default '',
          `smcols` varchar(15) NOT NULL default '',
          `editedby` char(3) NOT NULL default '',
          `dotfolders` char(3) NOT NULL default '',
          `attachimgpost` char(3) NOT NULL default '',
          `todaysposts` char(3) NOT NULL default '',
          `stats` char(3) NOT NULL default '',
          `authorstatus` char(3) NOT NULL default '',
          `tickerstatus` char(3) NOT NULL default '',
          `tickercontents` text NOT NULL,
          `tickerdelay` int(6) NOT NULL default 0,
          `addtime` DECIMAL(4,2) NOT NULL default 0,
          `max_avatar_size` varchar(9) NOT NULL default '100x100',
          `footer_options` varchar(45) NOT NULL default 'queries-phpsql-loadtimes-totaltime',
          `space_cats` char(3) NOT NULL default '',
          `spellcheck` char(3) NOT NULL default 'off',
          `allowrankedit` char(3) NOT NULL default '',
          `notifyonreg` SET('off','u2u','email') NOT NULL default 'off',
          `subject_in_title` char(3) NOT NULL default '',
          `def_tz` decimal(4,2) NOT NULL default '0.00',
          `indexshowbar` tinyint(2) NOT NULL default 2,
          `resetsigs` char(3) NOT NULL default '',
          `ipreg` char(3) NOT NULL default 'on',
          `maxdayreg` smallint(5) UNSIGNED NOT NULL default 25,
          `maxattachsize` int(10) UNSIGNED NOT NULL default 256000
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."smilies");

// ------------ xmb_smilies -------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."smilies");
    $db->query("CREATE TABLE ".$tablepre."smilies (
          `type` varchar(15) NOT NULL default '',
          `code` varchar(40) NOT NULL default '',
          `url` varchar(40) NOT NULL default '',
          `id` smallint(6) NOT NULL auto_increment,
          PRIMARY KEY  (`id`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."templates");

// ------------ xmb_templates -----------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."templates");
    $db->query("CREATE TABLE ".$tablepre."templates (
          `id` smallint(6) NOT NULL auto_increment,
          `name` varchar(32) NOT NULL default '',
          `template` text NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `name` (`name`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."themes");

// ------------ xmb_themes --------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."themes");
    $db->query("CREATE TABLE ".$tablepre."themes (
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
          `smdir` varchar(120) NOT NULL default '',
          `cattext` varchar(15) NOT NULL default '',
          PRIMARY KEY  (`themeid`),
          KEY `name` (`name`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."threads");

// ------------ xmb_threads -------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."threads");
    $db->query("CREATE TABLE ".$tablepre."threads (
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
          `pollopts` text NOT NULL,
          PRIMARY KEY  (`tid`),
          KEY `fid` (`fid`),
          KEY `tid` (`tid`),
          KEY `lastpost` (`lastpost`),
          KEY `author` (author (8) ),
          KEY `closed` (`closed`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."u2u");

// ------------ xmb_u2u -----------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."u2u");
    $db->query("CREATE TABLE ".$tablepre."u2u (
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
          KEY `msgto` (msgto (8) ),
          KEY `msgfrom` (msgfrom (8) ),
          KEY `folder` (folder (8) ),
          KEY `readstatus` (`readstatus`),
          KEY `owner` (owner (8) )
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."whosonline");

// ------------ xmb_whosonline ----------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."whosonline");
    $db->query("CREATE TABLE ".$tablepre."whosonline (
          `username` varchar(32) NOT NULL default '',
          `ip` varchar(15) NOT NULL default '',
          `time` int(10) NOT NULL default 0,
          `location` varchar(150) NOT NULL default '',
          `invisible` SET('1','0') default '0',
          KEY `username` (username (8) ),
          KEY `ip` (`ip`),
          KEY `time` (`time`),
          KEY `invisible` (`invisible`)
        ) TYPE=MyISAM PACK_KEYS=0
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."words");

// ------------ xmb_words ---------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."words");
    $db->query("CREATE TABLE ".$tablepre."words (
          `find` varchar(60) NOT NULL default '',
          `replace1` varchar(60) NOT NULL default '',
          `id` smallint(6) NOT NULL auto_increment,
          PRIMARY KEY  (`id`),
          KEY `find` (`find`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------
show_result(X_INST_OK);

// -- Insert Data -- //
show_act("Inserting data into ".$tablepre."restricted");
    $db->query("INSERT INTO ".$tablepre."restricted (`name`, `case_sensitivity`, `partial`) VALUES ('Anonymous', '1', '0');");
    $db->query("INSERT INTO ".$tablepre."restricted (`name`, `case_sensitivity`, `partial`) VALUES ('||~|~||', '1', '1');");
    $db->query("INSERT INTO ".$tablepre."restricted (`name`, `case_sensitivity`, `partial`) VALUES ('#|#', '1', '1');");
    $db->query("INSERT INTO ".$tablepre."restricted (`name`, `case_sensitivity`, `partial`) VALUES ('//||//', '1', '1');");
    $db->query("INSERT INTO ".$tablepre."restricted (`name`, `case_sensitivity`, `partial`) VALUES ('<script', '1', '1');");
show_result(X_INST_OK);


show_act("Inserting data into ".$tablepre."forums");
    $db->query("INSERT INTO ".$tablepre."forums (`type`, `name`, `status`, `lastpost`, `moderator`, `displayorder`, `description`, `allowhtml`, `allowsmilies`, `allowbbcode`, `userlist`, `theme`, `posts`, `threads`, `fup`, `postperm`, `allowimgcode`, `attachstatus`, `password`) VALUES ('forum', 'Default Forum', 'on', '', '', 0, 'This is your default forum which is created during installation<br />To add or modify forums goto your control panel - forums', 'no', 'yes', 'yes', '', 0, 0, 0, 0, '31,31,31,63', 'yes', 'on', '');");
show_result(X_INST_OK);


show_act("Inserting data into ".$tablepre."ranks");
    $db->query("INSERT INTO ".$tablepre."ranks (`title`, `posts`, `stars`,`allowavatars`,`avatarrank`) VALUES ('Newbie', 0, 1, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks (`title`, `posts`, `stars`,`allowavatars`,`avatarrank`) VALUES ('Junior Member', 2, 2, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks (`title`, `posts`, `stars`,`allowavatars`,`avatarrank`) VALUES ('Member', 100, 3, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks (`title`, `posts`, `stars`,`allowavatars`,`avatarrank`) VALUES ('Senior Member', 500, 4, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks (`title`, `posts`, `stars`,`allowavatars`,`avatarrank`) VALUES ('Posting Freak', 1000, 5, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks (`title`, `posts`, `stars`,`allowavatars`,`avatarrank`) VALUES ('Moderator', -1, 6, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks (`title`, `posts`, `stars`,`allowavatars`,`avatarrank`) VALUES ('Super Moderator', -1, 7, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks (`title`, `posts`, `stars`,`allowavatars`,`avatarrank`) VALUES ('Administrator', -1, 8, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks (`title`, `posts`, `stars`,`allowavatars`,`avatarrank`) VALUES ('Super Administrator', -1, 9, 'yes', '');");
show_result(X_INST_OK);


show_act("Inserting data into ".$tablepre."settings");
    $db->query("INSERT INTO ".$tablepre."settings (`langfile`, `bbname`, `postperpage`, `topicperpage`, `hottopic`, `theme`, `bbstatus`, `whosonlinestatus`, `regstatus`, `pruneusers`, `bboffreason`, `regviewonly`, `floodctrl`, `memberperpage`, `catsonly`, `hideprivate`, `emailcheck`, `bbrules`, `bbrulestxt`, `searchstatus`, `faqstatus`, `memliststatus`, `sitename`, `siteurl`, `avastatus`, `u2uquota`, `gzipcompress`, `boardurl`, `coppa`, `timeformat`, `adminemail`, `dateformat`, `sigbbcode`, `sightml`, `reportpost`, `bbinsert`, `smileyinsert`, `doublee`, `smtotal`, `smcols`, `editedby`, `dotfolders`, `attachimgpost`, `todaysposts`, `stats`, `authorstatus`, `tickerstatus`, `tickercontents`, `tickerdelay`, `addtime`, `max_avatar_size`, `footer_options`, `space_cats`, `spellcheck`, `allowrankedit`, `notifyonreg`, `subject_in_title`, `def_tz`, `indexshowbar`, `resetsigs`, `ipreg`, `maxdayreg`, `maxattachsize`) VALUES ('English', 'Your Forums', 25, 30, 20, 1, 'on', 'on', 'on', 0, '', 'off', 5, 45, 'off', 'on', 'off', 'off', '', 'on', 'on', 'on', 'YourDomain.com', '$full_url', 'on', 600, 'on', '$full_url', 'off', 12, 'webmaster@domain.ext', 'dd-mm-yyyy', 'on', 'off', 'on', 'on', 'on', 'off', '16', '4', 'off', 'on', 'on', 'on', 'on', 'on', 'on', '<b>Welcome to your new boards!!</b>\nModify your board to your own taste, we recommend starting with changing the settings...!', '4000', '0', '100x100', 'queries-phpsql-loadtimes-totaltime', 'no', 'off', 'on', 'off', 'off', '0.00', '2', 'off', 'on', 25, 256000);");
show_result(X_INST_OK);

show_act("Inserting data into ".$tablepre."smilies");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('smiley', ':)', 'smile.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('smiley', ':(', 'sad.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('smiley', ':D', 'biggrin.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('smiley', ';)', 'wink.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('smiley', ':cool:', 'cool.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('smiley', ':mad:', 'mad.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('smiley', ':o', 'shocked.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('smiley', ':P', 'tongue.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('picon', '', 'smile.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('picon', '', 'sad.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('picon', '', 'biggrin.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('picon', '', 'wink.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('picon', '', 'cool.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('picon', '', 'mad.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('picon', '', 'shocked.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('picon', '', 'thumbup.gif');");
    $db->query("INSERT INTO ".$tablepre."smilies (`type`, `code`, `url`) VALUES ('picon', '', 'thumbdown.gif');");
show_result(X_INST_OK);


show_act("Inserting data into ".$tablepre."templates");
    $stream = fopen(ROOT.'templates.xmb','r');
    $file   = fread($stream, filesize(ROOT.'templates.xmb'));
              fclose($stream);

    $templates = explode("|#*XMB TEMPLATE FILE*#|", $file);
    foreach ($templates as $key=>$val) {
        $template = explode("|#*XMB TEMPLATE*#|", $val);
        if ( isset($template[1]) ) {
            $template[1] = addslashes($template[1]);
        } else {
            $template[1] = '';
        }
        $db->query("INSERT INTO ".$tablepre."templates (`name`, `template`) VALUES ('".addslashes($template[0])."', '".addslashes($template[1])."')");
    }
    $db->query("DELETE FROM ".$tablepre."templates WHERE name=''");
show_result(X_INST_OK);


show_act("Inserting data into ".$tablepre."themes");
        $db->query("INSERT INTO ".$tablepre."themes (`name`, `bgcolor`, `altbg1`, `altbg2`, `link`, `bordercolor`, `header`, `headertext`, `top`, `catcolor`, `tabletext`, `text`, `borderwidth`, `tablewidth`, `tablespace`, `font`, `fontsize`, `boardimg`, `imgdir`, `smdir`, `cattext`) VALUES ('XMB Corporate',    'rep_1.jpg', '#D1E5EF', '#EFEFEF', '#000000', '#FFFFFF', '#737373', '#FFFFFF', 'main.jpg', '#246197', '#2E3E55', '#000000', '2px', '696', '4', 'Verdana, Arial, Helvetica', '10px', 'space.gif', 'images/corporate', 'images/smilies', '#FFFFFF');");
        $db->query("INSERT INTO ".$tablepre."themes (`name`, `bgcolor`, `altbg1`, `altbg2`, `link`, `bordercolor`, `header`, `headertext`, `top`, `catcolor`, `tabletext`, `text`, `borderwidth`, `tablewidth`, `tablespace`, `font`, `fontsize`, `boardimg`, `imgdir`, `smdir`, `cattext`) VALUES ('Iconic',           '#050C16', '#0A1C31', '#081627', '#FFFFFF', '#2E3E55', '#050C16', '#FFFFFF', '#050C16', 'catbg.gif', '#FFFFFF', '#FFFFFF', '1', '90%', '5', 'Verdana, Arial, Helvetica', '10px', 'iconicheader.gif', 'images/iconic', 'images/smilies', '#FFFFFF');");
        $db->query("INSERT INTO ".$tablepre."themes (`name`, `bgcolor`, `altbg1`, `altbg2`, `link`, `bordercolor`, `header`, `headertext`, `top`, `catcolor`, `tabletext`, `text`, `borderwidth`, `tablewidth`, `tablespace`, `font`, `fontsize`, `boardimg`, `imgdir`, `smdir`, `cattext`) VALUES ('one.point9',       'bgg.gif', '#D5E0EC', '#D5E0EC', '#000000', '#315275', '#1C8BCB', '#FFFFFF', 'bg2.gif', 'bar.gif', '#2E3E55', '#000000', '1', '85%', '4', 'Verdana, Arial, Helvetica', '10px', 'banner.gif', 'images/onepoint9', 'images/smilies', '#FFFFFF');");
        $db->query("INSERT INTO ".$tablepre."themes (`name`, `bgcolor`, `altbg1`, `altbg2`, `link`, `bordercolor`, `header`, `headertext`, `top`, `catcolor`, `tabletext`, `text`, `borderwidth`, `tablewidth`, `tablespace`, `font`, `fontsize`, `boardimg`, `imgdir`, `smdir`, `cattext`) VALUES ('Windows XP Silver','#FFFFFF', '#EDF0F7', '#FFFFFF', '#000000', '#C4C8D4', '#FFFFFF', '#000000', '#FFFFFF', 'silverbar.gif', '#000000', '#000000', '1', '90%', '4', 'Verdana, Arial, Helvetica', '10px', 'xplogo.gif', 'images/xpsilver', 'images/smilies', '#000000');");
show_result(X_INST_OK);

show_act("Inserting data into ".$tablepre."words");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('cock', '<b>****</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('dick', '<b>****</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('fuck', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('shit', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('faggot', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('bitch', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('whore', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('mofo', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('shite', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('asshole', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('dumbass', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('blowjob', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('porn', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('masturbate', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('masturbation', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('jackoff', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('jack off', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('s h i t', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('f u c k', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('f a g g o t', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('b i t c h', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('cunt', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('c u n t', '<b>[Censored]</b>');");
    $db->query("INSERT INTO ".$tablepre."words (`find`, `replace1`) VALUES ('damn', 'dang');");
show_result(X_INST_OK);

show_act("Creating Super Administrator Account");
    $db->query("INSERT INTO ".$tablepre."members (`username`, `password`, `regdate`, `email`, `status`, `showemail`, `theme`, `langfile`, `timeformat`, `dateformat`, `mood`, `pwdate`, `tpp`, `ppp`, `saveogu2u`, `emailonu2u`, `useoldu2u`) VALUES ('$vUsername', '$vPassword', $myDate, '$vEmail', 'Super Administrator', 'no', '0', 'English', 24, 'dd-mm-yyyy', '', $myDate, 30, 30, 'yes', 'no', 'no');");
show_result(X_INST_OK);

// Try to remove all files now
show_act('Removing installer files');
chdir('..');
rmFromDir('install');
clearstatcache();
if (file_exists('./install')) {
    show_result(X_INST_SKIP);
    error('Permission Error', 'XMB could not remove the installer because of wrong permissions. Please remove it manually via eg. FTP', false);
} else {
    show_result(X_INST_OK);
}

// If the 'Upgrade' folder has been found, try to silently delete it. If that fails, then show the progress and the failed message.
if ( file_exists('./Upgrade') ) {
    rmFromDir('Upgrade');
    if ( file_exists('./Upgrade') ) {
        show_act('Removing Upgrade files');
        show_result(X_INST_SKIP);
        error('Permission Error', 'XMB could not remove the Upgrade folder because of wrong permissions. Please remove it manually via eg. FTP', false);
    }
}
