<?php
/**
 * eXtreme Message Board
 * XMB 1.9.8 Engage Final SP2
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

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

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
        echo '<span class="progress">'.$act;
    }
}

if (!function_exists('show_result')) {
    function show_result($type) {
        switch($type) {
            case 0:
                echo '<span class="progressErr">ERROR</span><br />';
                break;
            case 1:
                echo '<span class="progressWarn">WARNING</span><br />';
                break;
             case 2:
                echo '<span class="progressOk">OK</span><br />';
                break;
             case 3:
                echo '<span class="progressSkip">SKIPPED</span><br />';
                break;
        }
        echo "</span>\n";
    }
}

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

while(ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(1);

require ROOT.'include/global.inc.php';
require ROOT.'config.php';
require ROOT.'db/'.$database.'.php';

$db = new dbstuff;
$tmphost = $dbhost; // dbhost gets cleared by the following method.
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true);

show_act("Checking Super Administrator Account");
$vUsername = trim($frmUsername);
$frmPassword = trim($frmPassword);
$vEmail = trim($frmEmail);

if ($vUsername == '' || $frmPassword == '' || $vEmail == '') {
    show_result(X_INST_ERR);
    $errStr = 'The username, password or e-mail address cannot be blank or malformed. Please press back and try again.';
    error('Bad super administrator credentials', $errStr);
    exit();
}

if ($frmPassword != $frmPasswordCfm) {
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
if (@in_array($tablepre.'settings', $db->fetch_tables($dbname))) {
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
    KEY `username` (username (8))
   ) TYPE=MyISAM
");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."captchaimages");
// ------------ xmb_captchaimages --------------------------------
$db->query("DROP TABLE IF EXISTS ".$tablepre."captchaimages");
$db->query("CREATE TABLE ".$tablepre."captchaimages (
      `imagehash` varchar(32) NOT NULL default '',
      `imagestring` varchar(12) NOT NULL default '',
      `dateline` int(10) NOT NULL default '0',
      KEY `dateline` (`dateline`)
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
      `type` varchar(15) NOT NULL default '',
      `fid` smallint(6) NOT NULL auto_increment,
      `name` varchar(128) NOT NULL default '',
      `status` varchar(15) NOT NULL default '',
      `lastpost` varchar(54) NOT NULL default '',
      `moderator` varchar(100) NOT NULL default '',
      `displayorder` smallint(6) NOT NULL default 0,
      `private` varchar(30) default '1',
      `description` text,
      `allowhtml` char(3) NOT NULL default '',
      `allowsmilies` char(3) NOT NULL default '',
      `allowbbcode` char(3) NOT NULL default '',
      `userlist` text NOT NULL,
      `theme` smallint(3) NOT NULL default 0,
      `posts` int(10) NOT NULL default 0,
      `threads` int(10) NOT NULL default 0,
      `fup` smallint(6) NOT NULL default 0,
      `postperm` char(3) NOT NULL default '',
      `allowimgcode` char(3) NOT NULL default '',
      `attachstatus` varchar(15) NOT NULL default '',
      `pollstatus` varchar(15) NOT NULL default '',
      `password` varchar(32) NOT NULL default '',
      `guestposting` char(3) NOT NULL default '',
      PRIMARY KEY  (`fid`),
      KEY `fup` (`fup`),
      KEY `type` (`type`),
      KEY `displayorder` (`displayorder`),
      KEY `private` (`private`),
      KEY `status` (`status`)
   ) TYPE=MyISAM
");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."logs");
// ------------ xmb_logs --------------------------
$db->query("DROP TABLE IF EXISTS ".$tablepre."logs");
$db->query("CREATE TABLE ".$tablepre."logs (
      `username` varchar(32) NOT NULL,
      `action` varchar(64) NOT NULL default '',
      `fid` smallint(6) NOT NULL default 0,
      `tid` int(10) NOT NULL default 0,
      `date` int(10) NOT NULL default 0,
      KEY `username` (username (8)),
      KEY `action` (action (8)),
      KEY `fid` (`fid`)
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
      `lastvisit` bigint(15) NOT NULL default 0,
      `mood` varchar(128) NOT NULL default 'Not Set',
      `pwdate` int(10) NOT NULL default 0,
      `invisible` SET('1','0') default 0,
      `u2ufolders` text NOT NULL,
      `saveogu2u` char(3) NOT NULL default '',
      `emailonu2u` char(3) NOT NULL default '',
      `useoldu2u` char(3) NOT NULL default '',
      PRIMARY KEY  (`uid`),
      KEY `username` (username (8)),
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
      KEY `author` (author (8))
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
      `case_sensitivity` ENUM('0', '1') DEFAULT '1' NOT NULL,
      `partial` ENUM('0', '1') DEFAULT '1' NOT NULL,
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
      `adminemail` varchar(60) NOT NULL default '',
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
      `pruneusers` smallint(3) NOT NULL default 0,
      `ipreg` char(3) NOT NULL default 'on',
      `maxdayreg` smallint(5) UNSIGNED NOT NULL default 25,
      `maxattachsize` int(10) UNSIGNED NOT NULL default 256000,
      `captcha_status` set('on','off') NOT NULL default 'on',
      `captcha_reg_status` set('on','off') NOT NULL default 'on',
      `captcha_post_status` set('on','off') NOT NULL default 'on',
      `captcha_code_charset` varchar(128) NOT NULL default 'A-Z,1-9',
      `captcha_code_length` int(2) NOT NULL default '10',
      `captcha_code_casesensitive` set('on','off') NOT NULL default 'on',
      `captcha_code_shadow` set('on','off') NOT NULL default 'off',
      `captcha_image_type` varchar(4) NOT NULL default 'png',
      `captcha_image_width` int(3) NOT NULL default '250',
      `captcha_image_height` int(3) NOT NULL default '50',
      `captcha_image_bg` varchar(128) NOT NULL default '',
      `captcha_image_dots` int(3) NOT NULL default '0',
      `captcha_image_lines` int(2) NOT NULL default '0',
      `captcha_image_fonts` varchar(128) NOT NULL default '',
      `captcha_image_minfont` int(2) NOT NULL default '16',
      `captcha_image_maxfont` int(2) NOT NULL default '25',
      `captcha_image_color` set('on','off') NOT NULL default 'on',
      `showsubforums` set('on','off') NOT NULL default 'off',
      `regoptional` set('on','off') NOT NULL default 'off',
      `quickreply_status` set('on','off') NOT NULL default 'on',
      `quickjump_status` set('on','off') NOT NULL default 'on',
      `index_stats` set('on','off') NOT NULL default 'on',
      `onlinetodaycount` smallint(5) NOT NULL default 0,
      `onlinetoday_status` set('on','off') NOT NULL default 'on'
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
      `pollopts` tinyint(1) NOT NULL default 0,
      PRIMARY KEY  (`tid`),
      KEY `fid` (`fid`),
      KEY `lastpost` (`lastpost`),
      KEY `author` (author (8)),
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
      KEY `msgto` (msgto (8)),
      KEY `msgfrom` (msgfrom (8)),
      KEY `folder` (folder (8)),
      KEY `readstatus` (`readstatus`),
      KEY `owner` (owner (8))
   ) TYPE=MyISAM
");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."vote_desc");
// ------------ xmb_vote_desc ---------------------------------
$db->query("DROP TABLE IF EXISTS ".$tablepre."vote_desc");
$db->query("CREATE TABLE ".$tablepre."vote_desc (
      `vote_id` mediumint(8) unsigned NOT NULL auto_increment,
      `topic_id` mediumint(8) unsigned NOT NULL default '0',
      `vote_text` text NOT NULL,
      `vote_start` int(11) NOT NULL default '0',
      `vote_length` int(11) NOT NULL default '0',
      PRIMARY KEY  (`vote_id`),
      KEY `topic_id` (`topic_id`)
   ) TYPE=MyISAM
");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."vote_results");
// ------------ xmb_vote_desc ---------------------------------
$db->query("DROP TABLE IF EXISTS ".$tablepre."vote_results");
$db->query("CREATE TABLE ".$tablepre."vote_results (
      `vote_id` mediumint(8) unsigned NOT NULL default '0',
      `vote_option_id` tinyint(4) unsigned NOT NULL default '0',
      `vote_option_text` varchar(255) NOT NULL default '',
      `vote_result` int(11) NOT NULL default '0',
      KEY `vote_option_id` (`vote_option_id`),
      KEY `vote_id` (`vote_id`)
   ) TYPE=MyISAM
");
// --------------------------------------------------------
show_result(X_INST_OK);

show_act("Creating ".$tablepre."vote_voters");
// ------------ xmb_vote_desc ---------------------------------
$db->query("DROP TABLE IF EXISTS ".$tablepre."vote_voters");
$db->query("CREATE TABLE ".$tablepre."vote_voters (
      `vote_id` mediumint(8) unsigned NOT NULL default '0',
      `vote_user_id` mediumint(8) NOT NULL default '0',
      `vote_user_ip` char(8) NOT NULL default '',
      KEY `vote_id` (`vote_id`),
      KEY `vote_user_id` (`vote_user_id`),
      KEY `vote_user_ip` (`vote_user_ip`)
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
      KEY `username` (username (8)),
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
$db->query("INSERT INTO ".$tablepre."restricted (`name`, `case_sensitivity`, `partial`) VALUES ('xguest123', '1', '0');");
$db->query("INSERT INTO ".$tablepre."restricted (`name`, `case_sensitivity`, `partial`) VALUES ('||~|~||', '1', '1');");
$db->query("INSERT INTO ".$tablepre."restricted (`name`, `case_sensitivity`, `partial`) VALUES ('#|#', '1', '1');");
$db->query("INSERT INTO ".$tablepre."restricted (`name`, `case_sensitivity`, `partial`) VALUES ('//||//', '1', '1');");
$db->query("INSERT INTO ".$tablepre."restricted (`name`, `case_sensitivity`, `partial`) VALUES ('<script', '1', '1');");
show_result(X_INST_OK);

show_act("Inserting data into ".$tablepre."forums");
$db->query("INSERT INTO ".$tablepre."forums VALUES ('forum', 1, 'Default Forum', 'on', '', '', 0, '1', 'This is the default forum created during installation<br />To create or modify forums go to the forum section of the administration panel', 'no', 'yes', 'yes', '', 0, 0, 0, 0, '1|1', 'yes', 'on', 'on', '', 'off');");
show_result(X_INST_OK);

show_act("Inserting data into ".$tablepre."ranks");
$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Newbie', 0, 1, 1, 'yes', '');");
$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Junior Member', 2, 2, 2, 'yes', '');");
$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Member', 100, 3, 3, 'yes', '');");
$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Senior Member', 500, 4, 4, 'yes', '');");
$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Posting Freak', 1000, 5, 5, 'yes', '');");
$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Moderator', -1, 6, 6, 'yes', '');");
$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Super Moderator', -1, 7, 7, 'yes', '');");
$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Administrator', -1, 8, 8, 'yes', '');");
$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Super Administrator', -1, 9, 9, 'yes', '');");
show_result(X_INST_OK);

show_act("Inserting data into ".$tablepre."settings");
$db->query("INSERT INTO ".$tablepre."settings VALUES ('English', 'Your Forums', 25, 30, 20, 1, 'on', 'on', 'on', '', 'off', 5, 45, 'off', 'on', 'off', 'off', '', 'on', 'on', 'on', 'YourDomain.com', '$full_url', 'on', 600, 'on', '$full_url', 'off', 12, 'webmaster@domain.ext', 'dd-mm-yyyy', 'on', 'off', 'on', 'on', 'on', 'off', '16', '4', 'off', 'on', 'on', 'on', 'on', 'on', 'on', '<strong>Welcome to your new XMB Forum!</strong>\nWe recommend changing your forums <a href=\"cp.php?action=settings\">settings</a> first.', '4000', '0', '100x100', 'queries-phpsql-loadtimes-totaltime', 'no', 'off', 'on', 'off', 'off', '0.00', '2', 'off', '0', 'on', 25, 256000, 'on', 'on', 'on', 'A-Z', '8', 'off', 'off', 'png', '250', '50', '', '0', '70', '', '16', '25', 'off', 'off', 'off', 'on', 'on', 'on', '50', 'on');");
show_result(X_INST_OK);

show_act("Inserting data into ".$tablepre."smilies");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':)', 'smile.gif', 1);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':(', 'sad.gif', 2);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':thumbdown:', 'thumbdown.gif', 3);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ';)', 'wink.gif', 4);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':cool:', 'cool.gif', 5);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':mad:', 'mad.gif', 6);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':punk:', 'punk.gif', 7);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':blush:', 'blush.gif', 8);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':love:', 'love.gif', 9);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':ninja:', 'ninja.gif', 10);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':fake sniffle:', 'fake_sniffle.gif', 11);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':smilegrin:', 'smilegrin.gif', 12);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':kiss:', 'kiss.gif', 13);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':no:', 'no.gif', 14);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':post:', 'post.gif', 15);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':lol:', 'lol.gif', 16);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':sniffle:', 'sniffle.gif', 17);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':starhit:', 'starhit.gif', 18);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':yes:', 'yes.gif', 19);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':grind:', 'grind.gif', 20);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':crazy:', 'crazy.gif', 21);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':spin:', 'spin.gif', 22);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':exclamation:', 'exclamation.gif', 23);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':bigsmile:', 'bigsmile.gif', 24);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':smirk:', 'smirk.gif', 25);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':borg:', 'borg.gif', 26);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':rolleyes:', 'rolleyes.gif', 27);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':info:', 'info.gif', 28);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':question:', 'question.gif', 29);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':thumbup:', 'thumbup.gif', 30);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':dork:', 'dork.gif', 31);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'cool.gif', 32);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'mad.gif', 33);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'thumbup.gif', 34);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'thumbdown.gif', 35);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'post.gif', 36);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'exclamation.gif', 37);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'info.gif', 38);");
$db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'question.gif', 39);");
show_result(X_INST_OK);

show_act("Inserting data into ".$tablepre."templates");
$stream = fopen(ROOT.'templates.xmb','r');
$file = fread($stream, filesize(ROOT.'templates.xmb'));
fclose($stream);

$templates = explode("|#*XMB TEMPLATE FILE*#|", $file);
foreach($templates as $key=>$val) {
    $template = explode("|#*XMB TEMPLATE*#|", $val);
    if (isset($template[1])) {
        $template[1] = addslashes($template[1]);
    } else {
        $template[1] = '';
    }
    $db->query("INSERT INTO ".$tablepre."templates (`name`, `template`) VALUES ('".addslashes($template[0])."', '".addslashes($template[1])."')");
}
$db->query("DELETE FROM ".$tablepre."templates WHERE name=''");
show_result(X_INST_OK);

show_act("Inserting data into ".$tablepre."themes");
$db->query("INSERT INTO ".$tablepre."themes (`name`, `bgcolor`, `altbg1`, `altbg2`, `link`, `bordercolor`, `header`, `headertext`, `top`, `catcolor`, `tabletext`, `text`, `borderwidth`, `tablewidth`, `tablespace`, `font`, `fontsize`, `boardimg`, `imgdir`, `smdir`, `cattext`) VALUES ('XMB Engage', '#cdcecf', '#aebbcf', '#91a9cf', '#000000', '#2E3E55', '#5472a4', '#FFFFFF', 'topbg.gif', 'catbg.gif', '#000000', '#000000', '1px', '90%', '5px', 'Verdana, Arial, Helvetica', '10px', 'logo.gif', 'images/engage', 'images/smilies', '#FFFFFF');");
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
$db->query("INSERT INTO ".$tablepre."members (`username`, `password`, `regdate`, `email`, `status`, `bio`, `sig`, `showemail`, `theme`, `langfile`, `timeformat`, `dateformat`, `mood`, `pwdate`, `tpp`, `ppp`, `ignoreu2u`, `u2ufolders`, `saveogu2u`, `emailonu2u`, `useoldu2u`) VALUES ('$vUsername', '$vPassword', $myDate, '$vEmail', 'Super Administrator', '', '', 'no', 0, 'English', 12, 'dd-mm-yyyy', '', $myDate, 30, 30, '', '', 'yes', 'no', 'no');");
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
?>