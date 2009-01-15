<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	<title>XMB Install and Upgrade Script.</title>
</head>

<body>
<?
set_time_limit (1000);
require "./config.php";
require "./functions.php";
require "./db/$database.php";
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

if(!$cmd) {

	echo "Thanks for picking XMB!<br /><br />Please pick an option below:";
	echo "<br /><a href=\"?cmd=install\">Install</a><br /><a href=\"?cmd=upgrade\">Upgrade</a>";
}

elseif($cmd == "install") {

	echo "<ol><li>Make sure that you have uploaded the following(though we suggest uploading everything):</li><ul><li>config.php</li><li>functions.php</li><li>tempaltes.xmb</li><li>db directory</li></ul><li>Notice: If you are installing to a database that already has one install: <b>MAKE SURE THAT YOU HAVE CHANGE THE TABLEPRE SETTING</b> If you have not, you WILL delete your current install.</li><li>Click <a href=\"?cmd=do_install\">here</a> to continue.</li></ol>";

}

elseif($cmd == "do_install") {

echo "Thank you for picking XMB! We're installing now :)<br /><br />Creating ".$tablepre."attachments... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."attachments");
	$db->query("CREATE TABLE ".$tablepre."attachments (
		aid smallint(6) NOT NULL auto_increment,
		tid smallint(6) NOT NULL default '0',
		pid smallint(6) NOT NULL default '0',
		filename varchar(120) NOT NULL default '',
		filetype varchar(120) NOT NULL default '',
		filesize varchar(120) NOT NULL default '',
		attachment longblob NOT NULL,
		downloads smallint(6) NOT NULL default '0',
		PRIMARY KEY	(aid)
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre banned
//

echo "<b>Done</b> with ".$tablepre."attachments :)<br /><br />Creating ".$tablepre."banned... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."banned;");
	$db->query("CREATE TABLE ".$tablepre."banned (
		ip1 smallint(3) NOT NULL default '0',
		ip2 smallint(3) NOT NULL default '0',
		ip3 smallint(3) NOT NULL default '0',
		ip4 smallint(3) NOT NULL default '0',
		dateline bigint(30) NOT NULL default '0',
		id smallint(6) NOT NULL default '0',
		PRIMARY KEY	(id)
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre buddys
//

echo "<b>Done</b> with ".$tablepre."banned :)<br /><br />Creating ".$tablepre."buddys... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."buddys;");
	$db->query("CREATE TABLE ".$tablepre."buddys (
		username varchar(40) NOT NULL default '',
		buddyname varchar(40) NOT NULL default ''
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre favorites
//
echo "<b>Done</b> with ".$tablepre."buddys :)<br /><br />Creating ".$tablepre."favorites... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."favorites;");
	$db->query("CREATE TABLE ".$tablepre."favorites (
		tid smallint(6) NOT NULL default '0',
		username varchar(40) NOT NULL default '',
		type varchar(20) NOT NULL default ''
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre forums
//

echo "<b>Done</b> with ".$tablepre."favorites :)<br /><br />Creating ".$tablepre."forums... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."forums;");
	$db->query("CREATE TABLE ".$tablepre."forums (
		type varchar(15) NOT NULL default '',
		fid smallint(6) NOT NULL auto_increment,
		name varchar(50) NOT NULL default '',
		status varchar(15) NOT NULL default '',
		lastpost varchar(30) NOT NULL default '',
		moderator varchar(100) NOT NULL default '',
		displayorder smallint(6) NOT NULL default '0',
		private varchar(30) default '1',
		description text,
		allowhtml char(3) NOT NULL default '',
		allowsmilies char(3) NOT NULL default '',
		allowbbcode char(3) NOT NULL default '',
		userlist text NOT NULL,
		theme varchar(30) NOT NULL default '',
		posts int(100) NOT NULL default '0',
		threads int(100) NOT NULL default '0',
		fup smallint(6) NOT NULL default '0',
		postperm char(3) NOT NULL default '',
		allowimgcode char(3) NOT NULL default '',
		attachstatus varchar(15) NOT NULL default '',
		pollstatus varchar(15) NOT NULL default '',
		password varchar(30) NOT NULL default '',
		guestposting char(3) NOT NULL default '',
		PRIMARY KEY	(fid)
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre members
//

echo "<b>Done</b> with ".$tablepre."forums :)<br /><br />Creating ".$tablepre."members... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."members;");
	$db->query("CREATE TABLE ".$tablepre."members (
		uid smallint(6) NOT NULL auto_increment,
		username varchar(25) NOT NULL default '',
		password varchar(40) NOT NULL default '',
		regdate bigint(30) NOT NULL default '0',
		postnum int(10) NOT NULL default '0',
		email varchar(60) default NULL,
		site varchar(75) default NULL,
		aim varchar(40) default NULL,
		status varchar(35) NOT NULL default '',
		location varchar(50) default NULL,
		bio text,
		sig text NOT NULL default '',
		showemail varchar(15) NOT NULL default '',
		timeoffset int(5) NOT NULL default '0',
		icq varchar(30) NOT NULL default '',
		avatar varchar(90) default NULL,
		yahoo varchar(40) NOT NULL default '',
		customstatus varchar(100) NOT NULL default '',
		theme varchar(30) NOT NULL default '',
		bday varchar(50) default NULL,
		langfile varchar(40) NOT NULL default '',
		tpp smallint(6) NOT NULL default '0',
		ppp smallint(6) NOT NULL default '0',
		newsletter char(3) NOT NULL default '',
		regip varchar(40) NOT NULL default '',
		timeformat int(5) NOT NULL default '0',
		msn varchar(40) NOT NULL default '',
		dateformat varchar(10) NOT NULL default '',
		ignoreu2u text,
		lastvisit varchar(30) default NULL,
		PRIMARY KEY	(uid)
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre posts
//

echo "<b>Done</b> with ".$tablepre."members :)<br /><br />Creating ".$tablepre."posts... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."posts;");
	$db->query("CREATE TABLE ".$tablepre."posts (
		fid smallint(6) NOT NULL default '0',
		tid smallint(6) NOT NULL default '0',
		pid int(10) NOT NULL auto_increment,
		author varchar(40) NOT NULL default '',
		message text NOT NULL,
		subject varchar(100) NOT NULL default '',
		dateline bigint(30) NOT NULL default '0',
		icon varchar(50) default NULL,
		usesig varchar(15) NOT NULL default '',
		useip varchar(40) NOT NULL default '',
		bbcodeoff varchar(15) NOT NULL default '',
		smileyoff varchar(15) NOT NULL default '',
		PRIMARY KEY	(pid)
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre ranks
//

echo "<b>Done</b> with ".$tablepre."posts :)<br /><br />Creating ".$tablepre."ranks... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."ranks;");
	$db->query("CREATE TABLE ".$tablepre."ranks (
		title varchar(40) NOT NULL default '',
		posts smallint(6) default NULL,
		id smallint(6) NOT NULL auto_increment,
		stars smallint(6) NOT NULL default '0',
		allowavatars char(3) NOT NULL default '',
		avatarrank varchar(90) default NULL,
		PRIMARY KEY	(id)
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre settings
//

echo "<b>Done</b> with ".$tablepre."ranks :)<br /><br />Creating ".$tablepre."settings... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."settings;");
	$db->query("CREATE TABLE ".$tablepre."settings (
		langfile varchar(50) NOT NULL default '',
		bbname varchar(50) NOT NULL default '',
		postperpage smallint(5) NOT NULL default '0',
		topicperpage smallint(5) NOT NULL default '0',
		hottopic smallint(5) NOT NULL default '0',
		theme varchar(30) NOT NULL default '',
		bbstatus char(3) NOT NULL default '',
		whosonlinestatus char(3) NOT NULL default '',
		regstatus char(3) NOT NULL default '',
		bboffreason text NOT NULL,
		regviewonly char(3) NOT NULL default '',
		floodctrl smallint(5) NOT NULL default '0',
		memberperpage smallint(5) NOT NULL default '0',
		catsonly char(3) NOT NULL default '',
		hideprivate char(3) NOT NULL default '',
		emailcheck char(3) NOT NULL default '',
		bbrules char(3) NOT NULL default '',
		bbrulestxt text NOT NULL,
		searchstatus char(3) NOT NULL default '',
		faqstatus char(3) NOT NULL default '',
		memliststatus char(3) NOT NULL default '',
		sitename varchar(50) NOT NULL default '',
		siteurl varchar(60) NOT NULL default '',
		avastatus varchar(4) NOT NULL default '',
		u2uquota smallint(5) NOT NULL default '0',
		gzipcompress varchar(30) NOT NULL default '',
		boardurl varchar(60) NOT NULL default '',
		coppa char(3) NOT NULL default '',
		timeformat smallint(2) NOT NULL default '0',
		adminemail varchar(50) NOT NULL default '',
		dateformat varchar(20) NOT NULL default '',
		sigbbcode char(3) NOT NULL default '',
		sightml char(3) NOT NULL default '',
		reportpost char(3) NOT NULL default '',
		bbinsert char(3) NOT NULL default '',
		smileyinsert char(3) NOT NULL default '',
		doublee char(3) NOT NULL default '',
		smtotal varchar(15) NOT NULL default '',
		smcols varchar(15) NOT NULL default '',
		editedby char(3) NOT NULL default '',
		dotfolders char(3) NOT NULL default '',
		attachimgpost char(3) NOT NULL default ''
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre smilies
//

echo "<b>Done</b> with ".$tablepre."settings :)<br /><br />Creating ".$tablepre."smilies... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."smilies;");
	$db->query("CREATE TABLE ".$tablepre."smilies (
		type varchar(15) NOT NULL default '',
		code varchar(40) NOT NULL default '',
		url varchar(40) NOT NULL default '',
		id smallint(6) NOT NULL auto_increment,
		PRIMARY KEY	(id)
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre templates
//

	$db->query("DROP TABLE IF EXISTS ".$tablepre."templates;");
	$db->query("CREATE TABLE ".$tablepre."templates (
		id smallint(6) NOT NULL auto_increment,
		name varchar(40) NOT NULL default '',
		template text NOT NULL,
		PRIMARY KEY	(id)
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre themes
//

echo "<b>Done</b> with ".$tablepre."templates :)<br /><br />Creating ".$tablepre."themes... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."themes;");
	$db->query("CREATE TABLE ".$tablepre."themes (
		name varchar(30) NOT NULL default '',
		bgcolor varchar(25) NOT NULL default '',
		altbg1 varchar(15) NOT NULL default '',
		altbg2 varchar(15) NOT NULL default '',
		link varchar(15) NOT NULL default '',
		dummy tinyint(4) default NULL,
		bordercolor varchar(15) NOT NULL default '',
		header varchar(15) NOT NULL default '',
		headertext varchar(15) NOT NULL default '',
		top varchar(15) NOT NULL default '',
		catcolor varchar(15) NOT NULL default '',
		tabletext varchar(15) NOT NULL default '',
		text varchar(15) NOT NULL default '',
		borderwidth varchar(15) NOT NULL default '',
		tablewidth varchar(15) NOT NULL default '',
		tablespace varchar(15) NOT NULL default '',
		font varchar(40) NOT NULL default '',
		fontsize varchar(40) NOT NULL default '',
		boardimg varchar(50) default NULL,
		imgdir varchar(120) NOT NULL default '',
		smdir varchar(120) NOT NULL default '',
		cattext varchar(15) NOT NULL default '',
		PRIMARY KEY (name)
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre threads
//

echo "<b>Done</b> with ".$tablepre."themes :)<br /><br />Creating ".$tablepre."threads... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."threads;");
	$db->query("CREATE TABLE ".$tablepre."threads (
		tid int(10) NOT NULL auto_increment,
		fid smallint(6) NOT NULL default '0',
		subject varchar(100) NOT NULL default '',
		icon varchar(75) NOT NULL default '',
		lastpost varchar(30) NOT NULL default '',
		views int(100) NOT NULL default '0',
		replies int(100) NOT NULL default '0',
		author varchar(40) NOT NULL default '',
		closed varchar(15) NOT NULL default '',
		topped smallint(6) NOT NULL default '0',
		pollopts text NOT NULL,
		PRIMARY KEY	(tid)
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre u2u
//

echo "<b>Done</b> with ".$tablepre."threads :)<br /><br />Creating ".$tablepre."themes... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."u2u;");
	$db->query("CREATE TABLE ".$tablepre."u2u (
		u2uid smallint(6) NOT NULL auto_increment,
		msgto varchar(40) NOT NULL default '',
		msgfrom varchar(40) NOT NULL default '',
		dateline bigint(30) NOT NULL default '0',
		subject varchar(75) NOT NULL default '',
		message text NOT NULL,
		folder varchar(40) NOT NULL default '',
		new char(3) NOT NULL default '',
		PRIMARY KEY	(u2uid)
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre whosonline
//

echo "<b>Done</b> with ".$tablepre."themes :)<br /><br />Creating ".$tablepre."whosonline... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."whosonline;");
	$db->query("CREATE TABLE ".$tablepre."whosonline (
		username varchar(40) NOT NULL default '',
		ip varchar(40) NOT NULL default '',
		time bigint(30) NOT NULL default '0',
		location varchar(150) NOT NULL default ''
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre words
//

echo "<b>Done</b> with ".$tablepre."whosonline :)<br /><br />Creating ".$tablepre."words... ";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."words;");
	$db->query("CREATE TABLE ".$tablepre."words (
		find varchar(60) NOT NULL default '',
		replace1 varchar(60) NOT NULL default '',
		id smallint(6) NOT NULL auto_increment,
		PRIMARY KEY	(id)
	)");

echo "<b>Done</b> with ".$tablepre."words :)<br /><br />Now we'll begin inserting the default ranks into ".$tablepre."ranks...";

	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Newbie', 1, 1, 1, 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Junior Member', 2, 2, 2, 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Member', 100, 3, 3, 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Senior Member', 500, 4, 4, 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Posting Freak', 1000, 5, 5, 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Moderator', 0, 6, 6, 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Super Moderator', 0, 7, 7, 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Administrator', 0, 8, 8, 'yes', '');");

echo "<b>Done</b> with ".$tablepre."ranks :)<br /><br />Now  we'll begin inserting the default templates into ".$tablepre."templates...";

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

echo "<b>Done</b> with ".$tablepre."templates :)<br /><br />Now  we'll begin inserting the default settings into ".$tablepre."settings...";
	$db->query("INSERT INTO ".$tablepre."settings VALUES ('English', 'XMB Forums', 25, 30, 20, 'Winter Wind', 'on', 'on', 'on', '', 'off', 5, 45, 'off', 'on', 'off', 'off', '', 'on', 'on', 'on', 'XMBForum.com', 'http://www.xmbforum.com/', 'on', 75, 'off', 'http://forums.xmbforum.com/xmb/', 'off', 12, 'admin@domain.com', 'mm-dd-yyyy', 'on', 'off', 'on', 'on', 'on', 'on', '16', '4', 'off', 'on', 'on');");

echo "<b>Done</b> with ".$tablepre."settings :)<br /><br />Now  we'll begin inserting the default ".$tablepre."smilies...";

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

echo "<b>Done</b> with ".$tablepre."smilies :)<br /><br />Now  we'll begin inserting the default censored words into ".$tablepre."words...";

	$db->query("INSERT INTO ".$tablepre."words VALUES ('fuck', '<b>f*ck</b>', 1);");
	$db->query("INSERT INTO ".$tablepre."words VALUES ('shit', '<b>shoot</b>', 2);");
	$db->query("INSERT INTO ".$tablepre."words VALUES ('cock', '<b>penis</b>', 3);");
	$db->query("INSERT INTO ".$tablepre."words VALUES ('ass', '<b>bottom</b>', 4);");

echo "<b>Done</b> with ".$tablepre."words :)<br /><br />Now  we'll begin inserting the default forum into ".$tablepre."forums...";

	$db->query("INSERT INTO ".$tablepre."forums VALUES ('forum', 1, 'Default Forum', 'on', '', '', 0, '1', '', 'no', 'yes', 'yes', '', '', 0, 0, 0, '1', 'yes', 'on', 'on', '', 'off');");

echo "<b>Done</b> with ".$tablepre."forums :)<br /><br />Now  we'll begin inserting the default themes into ".$tablepre."themes...";

	$db->query("INSERT INTO ".$tablepre."themes VALUES ('xmb1', '#ffffff', '#dedfde', '#eeefee', '#004455', 0, '#ffffff', '#005555', '#ffffff', '', '#004c55', '#003300', '#002200', '1', '90%', '4', 'Arial', '12px', 'logo1.gif', 'images/xmb1', 'images/smilies', 'white');");
	$db->query("INSERT INTO ".$tablepre."themes VALUES ('xmb2', '#ffffff', '#e3e3ea', '#eeeef6', '#404060', 0, '#ffffff', '#505070', '#ffffff', '', 'category3.jpg', '#000033', '#000022', '1', '95%', '6', 'Arial', '12px', 'logo1.gif', 'images/xmb2', 'images/smilies', '');");
	$db->query("INSERT INTO ".$tablepre."themes VALUES ('xmb3', '#ffffff', '#ffdd00', '#ffe000', '#cc0000', 0, '#0000cc', '#0000aa', '#ffdd00', '#ffffff', '#ffcf00', '#000099', '#0000cc', '1', '95%', '6', 'Arial', '12px', 'logo.jpg', 'images/xmb3', 'images/smilies', '');");
	$db->query("INSERT INTO ".$tablepre."themes VALUES ('xmb1b', '#000000', '#220022', '#330033', '#bb44ff', 0, '#000000', '#880099', '#ffccff', '#000000', '#110011', '#eebbee', '#cc00ff', '1', '95%', '6', 'Arial', '12px', 'logo.jpg', 'images/xmb1b', 'images/smilies', '');");
	$db->query("INSERT INTO ".$tablepre."themes VALUES ('xmb2b', '#000000', '#222240', '#333350', '#ddddff', 0, '#000000', '#777799', '#222233', '#000000', '#000000', '#eeeeff', '#ccccee', '1', '85%', '6', 'Arial', '12px', 'logo.gif', 'images/xmb2b', 'images/smilies', '#ccccee');");
	$db->query("INSERT INTO ".$tablepre."themes VALUES ('xmb3b', '#000000', '#608876', '#709986', '#ddf0dd', 0, '#000000', '#557766', '#eeffee', '#000000', '#305550', '#eeffee', '#eeffee', '1', '95%', '6', 'arial', '12px', 'logo.swf,170,80', 'images/xmb1', 'images/smilies', '');");
	$db->query("INSERT INTO ".$tablepre."themes VALUES ('xmb4', '#ffffff', '#E6E6E6', '#D6D6D6', '#000000', 0, '#000000', '#2663E2', '#FFFFFF', '#FFFFFF', '#E6E6E6', '#000000', '#000000', '1', '99%', '4', 'Trebuchet MS', '12px', 'logo_xp.gif', 'images/xmb1', 'images/smilies', '');");
	$db->query("INSERT INTO ".$tablepre."themes VALUES ('XP-Blue', '#FFFFFF', '#F4F3E8', '#EDECD8', '#000000', 0, '#2663E2', '#2663E2', '#FFFFFF', '#FFFFFF', '#2663E2', '#000000', '#000000', '1', '98%', '4', 'Trebuchet MS', '12px', 'logo1.jpg', 'images/xp', 'images/smilies', '#ffffff');");
	$db->query("INSERT INTO ".$tablepre."themes VALUES ('Winter Wind', '#000000', '#B0893C', '#CDAD6E', '#5F4200', NULL, '#794F00', '#866426', '#CDAD6E', '#ffffff', '#5FA9E6', '#794F00', '#794F00', '1', '97%', '5', 'Arial', '11px', 'wind.jpg', 'images/xp', 'images/smilies', '#794F00');");
	$db->query("INSERT INTO ".$tablepre."themes VALUES ('XHalloween', '#000000', '#330000', '#440000', '#ff9900', 0, '#000000', '#ff9900', '#000000', '#ffffff', '#cc5500', '#ff9900', '#ff9900', '1', '94%', '4', 'arial', '12px', '../../../wj/pumpkin.gif', 'images/xmb1', 'images/smilies', '');");
	$db->query("INSERT INTO ".$tablepre."themes VALUES ('xmb gray', '#eeeeee', '#dededf', '#eeeeee', '#333399', NULL, '#778899', '#69798c', '#ffffff', '#eeeeee', '#dcdcde', '#000000', '#000000', '1', '95%', '5', 'Arial', '12px', 'logo.gif', 'images/gray', 'images/smilies', '#333399');");

	$db->query("create index fid on ".$tablepre."posts (fid);");
	$db->query("create index tid on ".$tablepre."posts (tid);");
	$db->query("create index fid on ".$tablepre."threads (fid);");
	$db->query("create index tid on ".$tablepre."threads (tid);");
	$db->query("create index username on ".$tablepre."members (username(25));");
	$db->query("create index status on ".$tablepre."members (status(35));");
	$db->query("create index tid on ".$tablepre."attachments (tid);");
	$db->query("create index pid on ".$tablepre."attachments (pid);");
	$db->query("create index dateline on ".$tablepre."posts (dateline);");

	$db->query("create index fup on ".$tablepre."forums (fup);");
	$db->query("create index msgto on ".$tablepre."u2u (msgto);");
	$db->query("create index type on ".$tablepre."forums (type);");
	$db->query("create index private on ".$tablepre."forums (private);");
	$db->query("create index username on ".$tablepre."whosonline (username);");
	$db->query("create index name on ".$tablepre."templates (name);");
	$db->query("create index find on ".$tablepre."words (find);");
	$db->query("ALTER TABLE ".$tablepre."themes DROP PRIMARY KEY, ADD PRIMARY KEY(name);");
	$db->query("create index status on ".$tablepre."forums (status);");
	$db->query("create index title on ".$tablepre."ranks (title);");
	$db->query("create index ip1 on ".$tablepre."banned (ip1);");
	$db->query("create index ip2 on ".$tablepre."banned (ip2);");
	$db->query("create index ip3 on ".$tablepre."banned (ip3);");
	$db->query("create index ip4 on ".$tablepre."banned (ip1);");


echo "<b>Done</b> with ".$tablepre."themes :)<br /><br />You have completed the install :) Now visit your board at <a href=\"index.php\">index.php</a>.";
}

if($cmd == "upgrade") {

	echo "What are you upgrading from?<ul><li><a href=\"?cmd=upgrade_rc4\">XMB RC4 or RC5</a></li><li>XMB RC3 - Not implemented, please downlaod RC4, and upgrade to that then RC5.1</li><li><a href=\"?cmd=upgrade_111\">XMB 1.11</a></li></ul>";

}

if($cmd == "upgrade_111") {

	echo"<ol><li>Make sure you upload the following:</li><ul><li>the 'db' directory</li><li>templates.xmb</li><li>config.php(updated)</li><li>newinstall.php(this file)</li></ul><li>BACK UP YOUR DB. If you don't know how to do this, please refer to the phpmyadmin manual, you can get there by opening phpmyadmin and appending Documentation.html to the end of the url. If it is not there, please direct yourself to <a href=\"http://phpwizard.net/projects/phpMyAdmin/\">The phpmyadmin homepage</a>.</li><li>Now click <a href=\"?cmd=do_upgrade_111\">here</a> to run the upgrader.</li></ol>";

}

if($cmd == "upgrade_rc4") {

	echo"<ol><li>Make sure you upload the following:</li><ul><li>the 'db' directory</li><li>templates.xmb</li><li>config.php(updated)</li><li>newinstall.php(this file)</li></ul><li>BACK UP YOUR DB. If you don't know how to do this, please refer to the phpmyadmin manual, you can get there by opening phpmyadmin and appending Documentation.html to the end of the url. If it is not there, please direct yourself to <a href=\"http://phpwizard.net/projects/phpMyAdmin/\">The phpmyadmin homepage</a>.</li><li>Now click <a href=\"?cmd=do_upgrade_rc4\">here</a> to run the upgrader.</li></ol>";

}

if($cmd == "do_upgrade_rc4") {

	echo "doing the two tiny changes to the members database :P ...";
	$db->query("ALTER TABLE ".$tablepre."members CHANGE sig sig TEXT NOT NULL;");

echo "<b>Done</b> doing the additions of the keys :) ...";

	$db->query("create index fid on ".$tablepre."posts (fid);");
	$db->query("create index tid on ".$tablepre."posts (tid);");
	$db->query("create index fid on ".$tablepre."threads (fid);");
	$db->query("create index tid on ".$tablepre."threads (tid);");
	$db->query("create index username on ".$tablepre."members (username(25));");
	$db->query("create index status on ".$tablepre."members (status(35));");
	$db->query("create index tid on ".$tablepre."attachments (tid);");
	$db->query("create index pid on ".$tablepre."attachments (pid);");
	$db->query("create index dateline on ".$tablepre."posts (dateline);");
	$db->query("create index fup on ".$tablepre."forums (fup);");
	$db->query("create index msgto on ".$tablepre."u2u (msgto);");
	$db->query("create index type on ".$tablepre."forums (type);");
	$db->query("create index private on ".$tablepre."forums (private);");
	$db->query("create index username on ".$tablepre."whosonline (username);");
	$db->query("create index name on ".$tablepre."templates (name);");
	$db->query("create index find on ".$tablepre."words (find);");
	$db->query("ALTER TABLE ".$tablepre."themes DROP PRIMARY KEY, ADD PRIMARY KEY(name);");
	$db->query("create index status on ".$tablepre."forums (status);");
	$db->query("create index title on ".$tablepre."ranks (title);");
	$db->query("create index ip1 on ".$tablepre."banned (ip1);");
	$db->query("create index ip2 on ".$tablepre."banned (ip2);");
	$db->query("create index ip3 on ".$tablepre."banned (ip3);");
	$db->query("create index ip4 on ".$tablepre."banned (ip1);");

echo "<b>Done</b> You have officially upgraded, restore your templates, then go rest. You've earned it ;)";

}

if($cmd == "do_upgrade_111") {

	$db->query("DROP TABLE IF EXISTS ".$tablepre."settings;");
	$db->query("CREATE TABLE ".$tablepre."settings (
		langfile varchar(50) NOT NULL default '',
		bbname varchar(50) NOT NULL default '',
		postperpage smallint(5) NOT NULL default '0',
		topicperpage smallint(5) NOT NULL default '0',
		hottopic smallint(5) NOT NULL default '0',
		theme varchar(30) NOT NULL default '',
		bbstatus char(3) NOT NULL default '',
		whosonlinestatus char(3) NOT NULL default '',
		regstatus char(3) NOT NULL default '',
		bboffreason text NOT NULL,
		regviewonly char(3) NOT NULL default '',
		floodctrl smallint(5) NOT NULL default '0',
		memberperpage smallint(5) NOT NULL default '0',
		catsonly char(3) NOT NULL default '',
		hideprivate char(3) NOT NULL default '',
		emailcheck char(3) NOT NULL default '',
		bbrules char(3) NOT NULL default '',
		bbrulestxt text NOT NULL,
		searchstatus char(3) NOT NULL default '',
		faqstatus char(3) NOT NULL default '',
		memliststatus char(3) NOT NULL default '',
		sitename varchar(50) NOT NULL default '',
		siteurl varchar(60) NOT NULL default '',
		avastatus varchar(4) NOT NULL default '',
		u2uquota smallint(5) NOT NULL default '0',
		gzipcompress varchar(30) NOT NULL default '',
		boardurl varchar(60) NOT NULL default '',
		coppa char(3) NOT NULL default '',
		timeformat smallint(2) NOT NULL default '0',
		adminemail varchar(50) NOT NULL default '',
		dateformat varchar(20) NOT NULL default '',
		sigbbcode char(3) NOT NULL default '',
		sightml char(3) NOT NULL default '',
		reportpost char(3) NOT NULL default '',
		bbinsert char(3) NOT NULL default '',
		smileyinsert char(3) NOT NULL default '',
		doublee char(3) NOT NULL default '',
		smtotal varchar(15) NOT NULL default '',
		smcols varchar(15) NOT NULL default '',
		editedby char(3) NOT NULL default '',
		dotfolders char(3) NOT NULL default '',
		attachimgpost char(3) NOT NULL default ''
	)");
	
echo "<b>Done</b> with ".$tablepre."settings :)<br /><br />Creating ".$tablepre."favorites... ";

	$db->query("CREATE TABLE ".$tablepre."favorites ( 
		tid smallint(6) NOT NULL, 
		username varchar(40) NOT NULL, 
		type varchar(20) NOT NULL, 
		PRIMARY KEY(tid) 
	);"); 

echo "<b>Done</b> with ".$tablepre."favorites :)<br /><br />Creating ".$tablepre."buddies... ";

	$db->query("CREATE TABLE ".$tablepre."buddys ( 
		username varchar(40) NOT NULL, 
		buddyname varchar(40) NOT NULL 
	)");

echo "<b>Done</b> with ".$tablepre."buddys :)<br /><br />Creating ".$tablepre."attachments... ";

	$db->query("CREATE TABLE ".$tablepre."attachments (
	aid smallint(6) NOT NULL auto_increment,
	tid smallint(6) NOT NULL,
	pid smallint(6) NOT NULL,
	filename varchar(120) NOT NULL,
	filetype varchar(120) NOT NULL,
	filesize varchar(30) NOT NULL,
	attachment blob NOT NULL,
	downloads smallint(6) NOT NULL,
	PRIMARY KEY(aid)
	)");
	$db->query("create index tid on ".$tablepre."attachments (tid);");
	$db->query("create index pid on ".$tablepre."attachments (pid);");

echo "<b>Done</b> with ".$tablepre."attachments :)<br /><br />Creating ".$tablepre."templates... ";

	$db->query("CREATE TABLE ".$tablepre."templates (
		id smallint(6) NOT NULL auto_increment,
		name varchar(40) NOT NULL default '',
		template text NOT NULL,
		PRIMARY KEY	(id)
	)");

echo "<b>Done</b> with ".$tablepre."templates :)<br /><br />Inserting data into ".$tablepre."templates... ";

	// templates

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
	$db->query("create index name on ".$tablepre."templates (name);");

	// settings

echo "<b>Done</b> with ".$tablepre."templates :)<br /><br />Inserting data into ".$tablepre."settings... ";

	include('settings.php');
	$db->query("INSERT INTO ".$tablepre."settings (langfile, bbname, postperpage, topicperpage, hottopic, theme, bbstatus, whosonlinestatus, regstatus, bboffreason, regviewonly, floodctrl, memberperpage, catsonly, hideprivate, emailcheck, bbrules, bbrulestxt, searchstatus, faqstatus, memliststatus, sitename, siteurl, avastatus, u2uquota, gzipcompress, boardurl, coppa, timeformat, adminemail, dateformat, sigbbcode, sightml, reportpost, bbinsert, smileyinsert, doublee, smtotal, smcols, editedby, dotfolders, attachimgpost) VALUES ('English', '$bbname', '$postperpage', '$topicperpage', '$hottopic', '$theme', '$bbstatus', '$whosonlinestatus', '$regstatus', '$bboffreason', '$regviewonly', '$floodctrl', '$memberperpage', '$catsonly', '$hideprivate', '$emailcheck', '$bbrules', '$bbrulestxt', '$searchstatus', '$faqstatus', '$memliststatus', '$sitename', '$siteurl', '$avastatus', '$u2uquota', '$gzipcompress', '$boardurl', '$coppa', '$timeformat', '$adminemail', '$dateformat', '$sigbbcode', '$sightml', '$reportpost', 'on', 'on', 'on', '16', '4', 'off', 'on', 'on')");


	// members

echo "<b>Done</b> with ".$tablepre."settings :)<br /><br />Doing the modifications needed to ".$tablepre."members... ";

	$db->query("ALTER TABLE ".$tablepre."members CHANGE password password varchar(40) NOT NULL;");
	$query = $db->query("SELECT username,password FROM ".$tablepre."members");
	while($row = $db->fetch_array($query)) {
		$newpassword = md5($row[password]);
		$username = $row[username];
		$db->query("UPDATE ".$tablepre."members SET password = '$newpassword' WHERE username = '$username'");
	}

	$db->query("create index username on ".$tablepre."members (username(25));");
	$db->query("create index status on ".$tablepre."members (status(35));");
	$db->query("UPDATE ".$tablepre."members SET langfile='English';");
	$db->query("ALTER TABLE ".$tablepre."members CHANGE sig sig TEXT NOT NULL;");

	// posts

echo "<b>Done</b> with ".$tablepre."members :)<br /><br />Doing the modifications needed to ".$tablepre."posts... ";

	$db->query("create index fid on ".$tablepre."posts (fid);");
	$db->query("create index tid on ".$tablepre."posts (tid);");
	$db->query("ALTER TABLE ".$tablepre."posts CHANGE pid pid INT(10) NOT NULL AUTO_INCREMENT;");
	$db->query("ALTER TABLE ".$tablepre."posts ADD subject VARCHAR(100) NOT NULL AFTER message;");
	$db->query("ALTER TABLE ".$tablepre."posts DROP emailnotify;");
	$db->query("create index dateline on ".$tablepre."posts (dateline);");

	// threads

echo "<b>Done</b> with ".$tablepre."posts :)<br /><br />Doing the modifications needed to ".$tablepre."threads... ";

	$queryt = $db->query("SELECT * FROM ".$tablepre."threads");
	while($thread = $db->fetch_array($queryt)) {
		$thread[message] = addslashes($thread[message]);
		$thread[subject] = addslashes($thread[subject]);
		$db->query("INSERT INTO ".$tablepre."posts VALUES ('$thread[fid]', '$thread[tid]', '', '$thread[author]', '$thread[message]', '$thread[subject]', '$thread[dateline]', '$thread[icon]', '$thread[usesig]', '$thread[useip]', '$thread[bbcodeoff]', '$thread[smileyoff]')");
	}
	$db->query("ALTER TABLE ".$tablepre."threads DROP message, DROP dateline, DROP usesig, DROP useip, DROP bbcodeoff, DROP smileyoff, DROP emailnotify, DROP icon;");
	$db->query("ALTER TABLE ".$tablepre."threads ADD pollopts text NOT NULL;");
	$db->query("ALTER TABLE ".$tablepre."threads CHANGE tid tid int(10) NOT NULL auto_increment;");
	$db->query("ALTER TABLE ".$tablepre."threads ADD icon varchar(75) NOT NULL AFTER subject;");
	$db->query("UPDATE ".$tablepre."threads SET pollopts='';");
	$db->query("create index fid on ".$tablepre."threads (fid);");
	$db->query("create index tid on ".$tablepre."threads (tid);");

	// themes

echo "<b>Done</b> with ".$tablepre."threads :)<br /><br />Doing the modifications needed to ".$tablepre."themes... ";

	$db->query("ALTER TABLE ".$tablepre."themes DROP altfont, DROP altfontsize, DROP replyimg, DROP newtopicimg, DROP postscol;");
	$db->query("ALTER TABLE ".$tablepre."themes ADD imgdir VARCHAR(120) NOT NULL AFTER boardimg, ADD smdir VARCHAR(120) NOT NULL AFTER imgdir, ADD cattext VARCHAR(15) NOT NULL AFTER smdir;");
	$db->query("ALTER TABLE ".$tablepre."themes DROP PRIMARY KEY, ADD PRIMARY KEY(name);");
	$db->query("UPDATE ".$tablepre."themes SET smdir='images/smilies', imgdir='images/gray';");

	// u2u

echo "<b>Done</b> with ".$tablepre."themes :)<br /><br />Doing the modifications needed to ".$tablepre."u2u... ";

	$db->query("create index msgto on ".$tablepre."u2u (msgto);");
	$db->query("ALTER TABLE ".$tablepre."u2u ADD new CHAR(3) NOT NULL AFTER folder;");

	// forums

echo "<b>Done</b> with ".$tablepre."u2u :)<br /><br />Doing the modifications needed to ".$tablepre."forums... ";

	$db->query("ALTER TABLE ".$tablepre."forums DROP guestposting;");
	$db->query("ALTER TABLE ".$tablepre."forums ADD attachstatus VARCHAR(15) NOT NULL AFTER allowimgcode, ADD pollstatus VARCHAR(15) NOT NULL AFTER attachstatus, ADD password VARCHAR(30) NOT NULL AFTER pollstatus, ADD guestposting CHAR(3) NOT NULL AFTER password;");
	$db->query("create index type on ".$tablepre."forums (type);");
	$db->query("create index private on ".$tablepre."forums (private);");
	$db->query("create index fup on ".$tablepre."forums (fup);");
	$db->query("create index status on ".$tablepre."forums (status);");
	$db->query("UPDATE ".$tablepre."forums SET attachstatus='on', pollstatus='on', private='1', password='', guestposting='off' WHERE type='forum' OR type='subforum';");

	// misc

echo "<b>Done</b> with ".$tablepre."forums :)<br /><br />Doing the few other small mods... ";

	$db->query("create index username on ".$tablepre."whosonline (username);");
	$db->query("create index ip1 on ".$tablepre."banned (ip1);");
	$db->query("create index ip2 on ".$tablepre."banned (ip2);");
	$db->query("create index ip3 on ".$tablepre."banned (ip3);");
	$db->query("create index ip4 on ".$tablepre."banned (ip1);");
	$db->query("create index title on ".$tablepre."ranks (title);");
	$db->query("create index find on ".$tablepre."words (find);");

echo "<b>Done</b> with ".$tablepre."settings :)<br /><br />That\'s it :) your <a href=\"index.php\">board</a> should not be fully upgraded. :)";

}

?>

</body>
</html>
