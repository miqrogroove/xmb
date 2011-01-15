<html>
<head>
    <title>XMB 1.8 Installation</title>
    <style type="text/css">
    body {
        scrollbar-base-color: #0A1C31;
        scrollbar-arrow-color: #050C16;
        text-align:left;
    }

    a {
        color: #FFFFFF;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }

    textarea, select, input, object {
        font-family: Verdana, arial, helvetica, sans-serif;
        font-size: 12px;
        font-weight: normal;
        background-color: #0A1C31;
        color: #FFFFFF;
        table-layout: fixed;
    }

    .submit {
        text-align: center;
    }

    hr {
        border: 0px;
        color: #2E3E55;
        background-color: #2E3E55;
        height: 1px;
    }
    </style>
    <!--Created By Aventure Media & The XMB Group-->
    <!--www.aventure-media.co.uk  www.xmbforum.com-->
</head>

<body bgcolor="#050C16" text="#FFFFFF">
<?
require "./config.php";
require "./xmb.php";
require "./functions.php";
require "./db/$database.php";
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

if(!$cmd) {
    echo "XMB 1.8<br />";
    echo "<b>This script cannot be accessed directly.</b>";

}elseif($cmd == "do_install") {

echo "Installing XMB 1.8 Partagium<br /><br />";

// Check for required Files
echo "<b>Checking for required files...</b><br/>";
    if(!file_exists('./templates.xmb')){
        exit('templates.xmb could not be found, please upload this file to your forum root directory');
    }else{
        echo "templates.xmb found<br />";
    }


// Start creation of tables....
echo "<br /><b>Starting with Creation of Tables...</b><br/>";

echo "Creating ".$tablepre."attachments<br />";

// ------------ xmb_attachments --------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."attachments");
    $db->query("CREATE TABLE ".$tablepre."attachments (
          `aid` int(10) NOT NULL auto_increment,
          `tid` int(10) NOT NULL default '0',
          `pid` int(10) NOT NULL default '0',
          `filename` varchar(120) NOT NULL default '',
          `filetype` varchar(120) NOT NULL default '',
          `filesize` varchar(120) NOT NULL default '',
          `attachment` longblob NOT NULL,
          `downloads` int(10) NOT NULL default '0',
          PRIMARY KEY  (`aid`),
          KEY `tid` (`tid`),
          KEY `pid` (`pid`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."banned<br />";

// ------------ xmb_banned -------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."banned;");
    $db->query("CREATE TABLE ".$tablepre."banned (
          `ip1` smallint(3) NOT NULL default '0',
          `ip2` smallint(3) NOT NULL default '0',
          `ip3` smallint(3) NOT NULL default '0',
          `ip4` smallint(3) NOT NULL default '0',
          `dateline` bigint(30) NOT NULL default '0',
          `id` smallint(6) NOT NULL default '0',
          PRIMARY KEY  (`id`),
          KEY `ip1` (`ip1`),
          KEY `ip2` (`ip2`),
          KEY `ip3` (`ip3`),
          KEY `ip4` (`ip4`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."buddys<br />";

// ------------ xmb_buddys --------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."buddys;");
    $db->query("CREATE TABLE ".$tablepre."buddys (
        `username` varchar(32) NOT NULL default '',
        `buddyname` varchar(32) NOT NULL default '',
        KEY `username` (`username`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."favorites<br />";

// ------------ xmb_favorites -----------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."favorites;");
    $db->query("CREATE TABLE ".$tablepre."favorites (
          `tid` int(10) NOT NULL default '0',
          `username` varchar(32) NOT NULL default '',
          `type` varchar(32) NOT NULL default ''
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."forums<br />";

// ------------ xmb_forums --------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."forums;");
    $db->query("CREATE TABLE ".$tablepre."forums (
          `type` varchar(15) NOT NULL default '',
          `fid` smallint(6) NOT NULL auto_increment,
          `name` varchar(50) NOT NULL default '',
          `status` varchar(15) NOT NULL default '',
          `lastpost` varchar(30) NOT NULL default '',
          `moderator` varchar(100) NOT NULL default '',
          `displayorder` smallint(6) NOT NULL default '0',
          `private` varchar(30) default '1',
          `description` text,
          `allowhtml` char(3) NOT NULL default '',
          `allowsmilies` char(3) NOT NULL default '',
          `allowbbcode` char(3) NOT NULL default '',
          `userlist` text NOT NULL,
          `theme` varchar(30) NOT NULL default '',
          `posts` int(100) NOT NULL default '0',
          `threads` int(100) NOT NULL default '0',
          `fup` smallint(6) NOT NULL default '0',
          `postperm` char(3) NOT NULL default '',
          `allowimgcode` char(3) NOT NULL default '',
          `attachstatus` varchar(15) NOT NULL default '',
          `pollstatus` varchar(15) NOT NULL default '',
          `password` varchar(32) NOT NULL default '',
          `guestposting` char(3) NOT NULL default '',
          PRIMARY KEY  (`fid`),
          KEY `fup` (`fup`),
          KEY `type` (`type`),
          KEY `private` (`private`),
          KEY `status` (`status`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."members<br />";

// ------------ xmb_members -------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."members;");
    $db->query("CREATE TABLE ".$tablepre."members (
          `uid` int(6) NOT NULL auto_increment,
          `username` varchar(32) NOT NULL default '',
          `password` varchar(32) NOT NULL default '',
          `regdate` int(10) NOT NULL default '0',
          `postnum` smallint(5) NOT NULL default '0',
          `email` varchar(60) default NULL,
          `site` varchar(75) default NULL,
          `aim` varchar(40) default NULL,
          `status` varchar(35) NOT NULL default '',
          `location` varchar(50) default NULL,
          `bio` text,
          `sig` text NOT NULL,
          `showemail` varchar(15) NOT NULL default '',
          `timeoffset` DECIMAL(4,2) NOT NULL default '0',
          `icq` varchar(30) NOT NULL default '',
          `avatar` varchar(90) default NULL,
          `yahoo` varchar(40) NOT NULL default '',
          `customstatus` varchar(250) NOT NULL default '',
          `theme` varchar(30) NOT NULL default '',
          `bday` varchar(50) default NULL,
          `langfile` varchar(40) NOT NULL default '',
          `tpp` smallint(6) NOT NULL default '0',
          `ppp` smallint(6) NOT NULL default '0',
          `newsletter` char(3) NOT NULL default '',
          `regip` varchar(15) NOT NULL default '',
          `timeformat` int(5) NOT NULL default '0',
          `msn` varchar(40) NOT NULL default '',
          `ban` varchar(15) NOT NULL default '0',
          `dateformat` varchar(10) NOT NULL default '',
          `ignoreu2u` text,
          `lastvisit` bigint(30) default NULL,
          `mood` varchar(32) NOT NULL default 'Not Set',
          `pwdate` int(10) NOT NULL default '0',
          PRIMARY KEY  (`uid`),
          KEY `username` (`username`),
          KEY `status` (`status`),
          KEY `password` (`password`),
          KEY `email` (`email`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."posts<br />";

// ------------ xmb_posts ---------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."posts;");
    $db->query("CREATE TABLE ".$tablepre."posts (
          `fid` smallint(6) NOT NULL default '0',
          `tid` int(10) NOT NULL default '0',
          `pid` int(10) NOT NULL auto_increment,
          `author` varchar(32) NOT NULL default '',
          `message` text NOT NULL,
          `subject` tinytext NOT NULL,
          `dateline` bigint(30) NOT NULL default '0',
          `icon` varchar(50) default NULL,
          `usesig` varchar(15) NOT NULL default '',
          `useip` varchar(15) NOT NULL default '',
          `bbcodeoff` varchar(15) NOT NULL default '',
          `smileyoff` varchar(15) NOT NULL default '',
          PRIMARY KEY  (`pid`),
          KEY `fid` (`fid`),
          KEY `tid` (`tid`),
          KEY `dateline` (`dateline`),
          KEY `author` (`author`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."ranks<br />";

// ------------ xmb_ranks ---------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."ranks;");
    $db->query("CREATE TABLE ".$tablepre."ranks (
          `title` varchar(100) NOT NULL default '',
          `posts` smallint(5) default NULL,
          `id` smallint(5) NOT NULL auto_increment,
          `stars` smallint(6) NOT NULL default '0',
          `allowavatars` char(3) NOT NULL default '',
          `avatarrank` varchar(90) default NULL,
          PRIMARY KEY  (`id`),
          KEY `title` (`title`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."restricted<br />";

// ------------ xmb_restricted ----------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."restricted;");
    $db->query("CREATE TABLE ".$tablepre."restricted (
          `name` varchar(32) NOT NULL default '',
          `id` smallint(6) NOT NULL auto_increment,
          PRIMARY KEY  (`id`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."settings<br />";

// ------------ xmb_settings ------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."settings;");
    $db->query("CREATE TABLE ".$tablepre."settings (
          `langfile` varchar(34) NOT NULL default '',
          `bbname` varchar(32) NOT NULL default '',
          `postperpage` smallint(5) NOT NULL default '0',
          `topicperpage` smallint(5) NOT NULL default '0',
          `hottopic` smallint(5) NOT NULL default '0',
          `theme` varchar(30) NOT NULL default '',
          `bbstatus` char(3) NOT NULL default '',
          `whosonlinestatus` char(3) NOT NULL default '',
          `regstatus` char(3) NOT NULL default '',
          `bboffreason` text NOT NULL,
          `regviewonly` char(3) NOT NULL default '',
          `floodctrl` smallint(5) NOT NULL default '0',
          `memberperpage` smallint(5) NOT NULL default '0',
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
          `u2uquota` smallint(5) NOT NULL default '0',
          `gzipcompress` varchar(30) NOT NULL default '',
          `boardurl` varchar(60) NOT NULL default '',
          `coppa` char(3) NOT NULL default '',
          `timeformat` smallint(2) NOT NULL default '0',
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
          `tickerdelay` int(6) NOT NULL default '',
          `addtime` DECIMAL(4,2) NOT NULL DEFAULT '0'
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."smilies<br />";

// ------------ xmb_smilies -------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."smilies;");
    $db->query("CREATE TABLE ".$tablepre."smilies (
          `type` varchar(15) NOT NULL default '',
          `code` varchar(40) NOT NULL default '',
          `url` varchar(40) NOT NULL default '',
          `id` smallint(6) NOT NULL auto_increment,
          PRIMARY KEY  (`id`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."templates<br />";

// ------------ xmb_templates -----------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."templates;");
    $db->query("CREATE TABLE ".$tablepre."templates (
          `id` smallint(6) NOT NULL auto_increment,
          `name` varchar(32) NOT NULL default '',
          `template` text NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `name` (`name`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."themes<br />";

// ------------ xmb_themes --------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."themes;");
    $db->query("CREATE TABLE ".$tablepre."themes (
          `name` varchar(32) NOT NULL default '',
          `bgcolor` varchar(25) NOT NULL default '',
          `altbg1` varchar(15) NOT NULL default '',
          `altbg2` varchar(15) NOT NULL default '',
          `link` varchar(15) NOT NULL default '',
          `dummy` tinyint(4) default NULL,
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
          `boardimg` varchar(50) default NULL,
          `imgdir` varchar(120) NOT NULL default '',
          `smdir` varchar(120) NOT NULL default '',
          `cattext` varchar(15) NOT NULL default '',
          PRIMARY KEY  (`name`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."threads<br />";

// ------------ xmb_threads -------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."threads;");
    $db->query("CREATE TABLE ".$tablepre."threads (
          `tid` int(10) NOT NULL auto_increment,
          `fid` smallint(6) NOT NULL default '0',
          `subject` varchar(128) NOT NULL default '',
          `icon` varchar(75) NOT NULL default '',
          `lastpost` varchar(32) NOT NULL default '',
          `views` smallint(4) NOT NULL default '0',
          `replies` smallint(5) NOT NULL default '0',
          `author` varchar(32) NOT NULL default '',
          `closed` varchar(15) NOT NULL default '',
          `topped` tinyint(1) NOT NULL default '0',
          `pollopts` text NOT NULL,
          PRIMARY KEY  (`tid`),
          KEY `fid` (`fid`),
          KEY `tid` (`tid`),
          KEY `lastpost` (`lastpost`),
          KEY `author` (`author`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."u2u<br />";

// ------------ xmb_u2u -----------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."u2u;");
    $db->query("CREATE TABLE ".$tablepre."u2u (
          `u2uid` int(6) NOT NULL auto_increment,
          `msgto` varchar(32) NOT NULL default '',
          `msgfrom` varchar(32) NOT NULL default '',
          `dateline` int(10) NOT NULL default '0',
          `subject` varchar(64) NOT NULL default '',
          `message` text NOT NULL,
          `folder` varchar(32) NOT NULL default '',
          `new` char(3) NOT NULL default '',
          `readstatus` char(3) NOT NULL default '',
          PRIMARY KEY  (`u2uid`),
          KEY `msgto` (`msgto`),
          KEY `msgfrom` (`msgfrom`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."whosonline<br />";

// ------------ xmb_whosonline ----------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."whosonline;");
    $db->query("CREATE TABLE ".$tablepre."whosonline (
          `username` varchar(32) NOT NULL default '',
          `ip` varchar(15) NOT NULL default '',
          `time` bigint(10) NOT NULL default '0',
          `location` varchar(150) NOT NULL default '',
          KEY `username` (`username`),
          KEY `ip` (`ip`),
          KEY `time` (`time`)
        ) TYPE=MyISAM PACK_KEYS=0
    ");
// --------------------------------------------------------


echo "Creating ".$tablepre."words<br />";

// ------------ xmb_words ---------------------------------
    $db->query("DROP TABLE IF EXISTS ".$tablepre."words;");
    $db->query("CREATE TABLE ".$tablepre."words (
          `find` varchar(60) NOT NULL default '',
          `replace1` varchar(60) NOT NULL default '',
          `id` smallint(6) NOT NULL auto_increment,
          PRIMARY KEY  (`id`),
          KEY `find` (`find`)
        ) TYPE=MyISAM
    ");
// --------------------------------------------------------


// -- Insert Data -- //
echo "<br /><b>Inserting data into tables...</b><br />";

echo "Inserting data into ".$tablepre."forums<br />";

        $db->query("INSERT INTO ".$tablepre."forums VALUES ('forum', 1, 'Default Forum', 'on', '', '', 0, '1', 'This is your default forum which is created during installation<br />To add or modify forums goto your control panel - forums', 'no', 'yes', 'yes', '', '', 0, 0, 0, '1', 'yes', 'on', 'on', '', 'off');");


echo "Inserting data into ".$tablepre."ranks<br />";

    $db->query("INSERT INTO ".$tablepre."ranks VALUES ('Newbie', 1, 1, 1, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks VALUES ('Junior Member', 2, 2, 2, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks VALUES ('Member', 100, 3, 3, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks VALUES ('Senior Member', 500, 4, 4, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks VALUES ('Posting Freak', 1000, 5, 5, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks VALUES ('Moderator', -1, 6, 6, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks VALUES ('Super Moderator', -1, 7, 7, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks VALUES ('Administrator', -1, 8, 8, 'yes', '');");
    $db->query("INSERT INTO ".$tablepre."ranks VALUES ('Super Administrator', -1, 9, 9, 'yes', '');");


echo "Inserting data into ".$tablepre."settings<br />";

    $db->query("INSERT INTO ".$tablepre."settings VALUES ('English', 'Your Forums', 25, 30, 20, 'AventureMedia', 'on', 'on', 'on', '', 'off', 5, 45, 'off', 'on', 'off', 'off', '', 'on', 'on', 'on', 'YourDomain.com', 'http://www.yourdomain.com/home', 'on', 600, 'on', 'http://www.yourdomain.com/forum/', 'off', 12, 'webmaster@yourdomain.com', 'dd-mm-yyyy', 'on', 'off', 'on', 'on', 'on', 'off', '16', '4', 'off', 'on', 'on', 'on', 'on', 'on', 'on', '<b>Welcome to your new boards!!</b>\r\nFirst, register yourself, you will be made admin.\r\nAfter this, modify your board to your own taste, we recommend starting with changing the settings...!', '4000', '0');");


echo "Inserting data into ".$tablepre."smilies<br />";

    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':)', 'smile.gif', 1);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':(', 'sad.gif', 2);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':D', 'biggrin.gif', 3);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ';)', 'wink.gif', 4);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':cool:', 'cool.gif', 5);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':mad:', 'mad.gif', 6);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':o', 'shocked.gif', 7);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('smiley', ':P', 'tongue.gif', 8);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'smile.gif', 9);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'sad.gif', 10);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'biggrin.gif', 11);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'wink.gif', 12);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'cool.gif', 13);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'mad.gif', 14);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'shocked.gif', 15);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'thumbup.gif', 16);");
    $db->query("INSERT INTO ".$tablepre."smilies VALUES ('picon', '', 'thumbdown.gif', 17);");


echo "Inserting data into ".$tablepre."templates<br />";

    $filesize=filesize('templates.xmb');
    $fp=fopen('templates.xmb','r');
    $templatesfile=fread($fp,$filesize);
    fclose($fp);
    $templates = explode("|#*XMB TEMPLATE FILE*#|", $templatesfile);
        while (list($key,$val) = each($templates)) {
            $template = explode("|#*XMB TEMPLATE*#|", $val);
            $template[1] = addslashes($template[1]);
            $db->query("INSERT INTO ".$tablepre."templates VALUES ('', '".addslashes($template[0])."', '".addslashes($template[1])."')");
        }
    $db->query("DELETE FROM ".$tablepre."templates WHERE name=''");

echo "Inserting data into ".$tablepre."themes<br />";


$db->query("INSERT INTO ".$tablepre."themes VALUES ('AventureMedia', '#1F3145', '#011B35', '#304459', '#FFFFFF', NULL, '#000000', '#011B35', '#FFFFFF', '#011B35', '#011B35', '#FFFFFF', '#FFFFFF', '1', '97%', '6', 'Verdana', '10px', 'xmbheader.gif', 'images/aventure', 'images/smilies', '#FFFFFF');");
$db->query("INSERT INTO ".$tablepre."themes VALUES ('Windows XP Silver', '#FFFFFF', '#EDF0F7', '#FFFFFF', '#000000', NULL, '#C4C8D4', '#FFFFFF', '#000000', '#FFFFFF', 'silverbar.gif', '#000000', '#000000', '1', '90%', '4', 'Verdana', '10px', 'xplogo.gif', 'images/xpsilver', 'images/smilies', '#000000');");
$db->query("INSERT INTO ".$tablepre."themes VALUES ('Windows XP Blue', '#FFFFFF', '#ADD1FF', '#FFFFFF', '#0055E5', NULL, '#0055E5', '#0055E5', '#FFFFFF', '#FFFFFF', 'bluebar.gif', '#000000', '#000000', '1', '90%', '4', 'Verdana', '10px', 'xplogo.gif', 'images/xpblue', 'images/smilies', '#FFFFFF');");
$db->query("INSERT INTO ".$tablepre."themes VALUES ('XMBForum.com', '#8896A7', '#8A9AAD', '#6C7D92', '#000000', NULL, '#000000', '#456281', '#FFFFFF', '#6C7D92', '#456281', '#000000', '#000000', '1', '90%', '5', 'Verdana', '10px', 'boardheader.gif', 'images/xmbforum', 'images/smilies', '#FFFFFF');");



echo "Inserting data into ".$tablepre."words<br />";



    $db->query("INSERT INTO ".$tablepre."words VALUES ('cock', '<b>****</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('dick', '<b>****</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('fuck', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('shit', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('faggot', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('bitch', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('whore', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('mofo', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('shite', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('asshole', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('dumbass', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('blowjob', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('porn', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('masturbate', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('masturbation', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('jackoff', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('jack off', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('s h i t', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('f u c k', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('f a g g o t', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('b i t c h', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('cunt', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('c u n t', '<b>[Censored]</b>', '');");
    $db->query("INSERT INTO ".$tablepre."words VALUES ('damn', 'dang', '');");

echo "<br /><br /><b>Installation Complete</b><br />";
echo " &raquo; You are now being transferred to your forums.<br /><meta http-equiv=\"Refresh\" content=\"5; url=index.php\">";
echo " &raquo; If you don't want to wait, click <a href=\"index.php\">here</a><br />";
}

?>

</body>
</html>
<?php
@unlink('./cinst.php');
?>