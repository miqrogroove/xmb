<html>
<head>
	<title>XMB 1.6 v2c Magic Lantern Final Installation</title>
	<body text="#FFFFFF" bgcolor="#8896A7" link="#FFFFFF" vlink="#FFFFFF" alink="#FFFFFF">
	<!--Created By Aventure Media & The XMB Group-->
	<!--www.aventure-media.co.uk  www.xmbforum.com-->
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

	echo "XMB 1.6 Magic Lantern<br />";
	echo "<b>This script cannot be accessed directly.</b>";

}

elseif($cmd == "do_install") {

echo "Installing XMB 1.6 Magic Lantern<br /><br />Creating ".$tablepre."attachments<br>";

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

echo "Creating ".$tablepre."banned<br>";

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

echo "Creating ".$tablepre."buddys<br>";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."buddys;");
	$db->query("CREATE TABLE ".$tablepre."buddys (
		username varchar(40) NOT NULL default '',
		buddyname varchar(40) NOT NULL default ''
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre favorites
//
echo "Creating ".$tablepre."favorites<br>";

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

echo "Creating ".$tablepre."forums<br>";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."forums;");
	$db->query("CREATE TABLE ".$tablepre."forums (
		type varchar(15) NOT NULL default '',
		fid smallint(6) NOT NULL auto_increment,
		name varchar(50) NOT NULL default '',
		status varchar(15) NOT NULL default '',
		lastpost varchar(30) NOT NULL default '',
		moderator varchar(100) NOT NULL default '',
		displayorder smallint(6) NOT NULL default '0',
		private varchar(30) NOT NULL default '1',
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

echo "Creating ".$tablepre."members<br>";

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
		customstatus varchar(250) NOT NULL default '',
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
		ban varchar(15) NOT NULL,
		ignoreu2u text,
		lastvisit varchar(30) default NULL,
		mood varchar(15) NOT NULL default 'Not Set',
		PRIMARY KEY	(uid)
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre posts
//

echo "Creating ".$tablepre."posts<br>";

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

echo "Creating ".$tablepre."ranks<br>";

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

echo "Creating ".$tablepre."settings<br>";

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
		attachimgpost char(3) NOT NULL default '',
		todaysposts char(3),
		stats char(3),
		authorstatus char(3)
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre smilies
//

echo "Creating ".$tablepre."smilies<br>";

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

echo "Creating ".$tablepre."themes<br>";

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

echo "Creating ".$tablepre."threads<br>";

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

echo "Creating ".$tablepre."themes<br>";

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
		readstatus varchar(3) NOT NULL,
		PRIMARY KEY	(u2uid)
	)");
// --------------------------------------------------------

//
// Table structure for table $tablepre whosonline
//

echo "Creating ".$tablepre."whosonline<br>";

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

echo "Creating ".$tablepre."words<br>";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."words;");
	$db->query("CREATE TABLE ".$tablepre."words (
		find varchar(60) NOT NULL default '',
		replace1 varchar(60) NOT NULL default '',
		id smallint(6) NOT NULL auto_increment,
		PRIMARY KEY	(id)
	)");

//
// Table structure for table $tablepre restricted
//

echo "Creating ".$tablepre."restricted<br>";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."restricted;");
	$db->query("CREATE TABLE ".$tablepre."restricted (
		name varchar(25)  NOT NULL,
		id smallint(6) NOT NULL auto_increment,
		PRIMARY KEY	(id)
	)");



echo "Inserting data into ".$tablepre."ranks<br>";

	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Newbie', 1, 1, 1, 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Junior Member', 2, 2, 2, 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Member', 100, 3, 3, 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Senior Member', 500, 4, 4, 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Posting Freak', 1000, 5, 5, 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Moderator', 0, 6, 6, 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Super Moderator', 0, 7, 7, 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Administrator', 0, 8, 8, 'yes', '');");

echo "Inserting data into ".$tablepre."templates<br>";

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


echo "Inserting data into ".$tablepre."smilies<br>";

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

echo "Inserting data into ".$tablepre."words<br>";

	$db->query("INSERT INTO ".$tablepre."words VALUES ('fuck', '<b>f*ck</b>', 1);");
	$db->query("INSERT INTO ".$tablepre."words VALUES ('shit', '<b>shoot</b>', 2);");
	$db->query("INSERT INTO ".$tablepre."words VALUES ('cock', '<b>penis</b>', 3);");
	$db->query("INSERT INTO ".$tablepre."words VALUES ('ass', '<b>bottom</b>', 4);");

echo "Inserting data into necessary tables<br>";

	$db->query("INSERT INTO ".$tablepre."forums VALUES ('forum', 1, 'Default Forum', 'on', '', '', 0, '1', 'This is your default forum which is created during installation<br>To add or modify forums goto your control panel - forums', 'no', 'yes', 'yes', '', '', 0, 0, 0, '1', 'yes', 'on', 'on', '', 'off');");
echo "Inserting data into ".$tablepre."themes<br>";

	$db->query("INSERT INTO ".$tablepre."themes VALUES ('XMBForum.com', '#8896A7', '#8A9AAD', '#6C7D92', '#000000', NULL, '#000000', '#456281', '#FFFFFF', '#6C7D92', '#456281', '#000000', '#000000', '1', '90%', '5', 'Verdana', '10px', 'boardheader.gif', 'images/xmbforum', 'images/smilies', '#FFFFFF');");

	$db->query("INSERT INTO ".$tablepre."themes VALUES ('AventureMedia', '#1F3145', '#011B35', '#304459', '#FFFFFF', NULL, '#000000', '#011B35', '#FFFFFF', '#011B35', '#011B35', '#FFFFFF', '#FFFFFF', '1', '97%', '6', 'Verdana', '10px', 'xmbheader.gif', 'images/aventure', 'images/smilies', '#FFFFFF');");

	$db->query("INSERT INTO ".$tablepre."settings VALUES ('English', 'Your Forums', 25, 30, 20, 'XMBForum.com', 'on', 'on', 'on', '', 'off', 5, 45, 'off', 'on', 'off', 'off', '', 'on', 'on', 'on', 'YourDomain.com', 'http://www.yourdomain.com/home', 'on', 600, 'on', 'http://www.yourdomain.com/forum/', 'off', 12, 'webmaster@yourdomain.com', 'dd-mm-yyyy', 'on', 'off', 'on', 'on', 'on', 'off', '16', '4', 'off', 'on', 'on', 'on', 'on', 'on');");

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

 
echo "<b>Installation Complete</b>To skip the exit screen click <a href=\"index.php\">here</a><br>.";
echo "<b>Please wait, Transferring..</b><meta http-equiv=\"Refresh\" content=\"1; url=completed.html\">";


}

?>

</body>
</html>
