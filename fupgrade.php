<html>
<head>
<title>Upgrade from 1.11 to 1.5 Silver</title>
</head>
<body>
<?php
#fugrade.php
require "./config.php";
require "./db/$database.php";

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

	echo "Adding settings...";
	$db->query("CREATE TABLE ".$tablepre."settings (
	langfile varchar(50) NOT NULL,
	bbname varchar(50) NOT NULL,
	postperpage smallint(5) NOT NULL,
	topicperpage smallint(5) NOT NULL,
	hottopic smallint(5) NOT NULL,
	theme varchar(30) NOT NULL,
	bbstatus char(3) NOT NULL,
	whosonlinestatus char(3) NOT NULL,
	regstatus char(3) NOT NULL,
	bboffreason text NOT NULL,
	regviewonly char(3) NOT NULL,
	floodctrl smallint(5) NOT NULL,
	memberperpage smallint(5) NOT NULL,
	catsonly char(3) NOT NULL,
	hideprivate char(3) NOT NULL,
	emailcheck char(3) NOT NULL,
	bbrules char(3) NOT NULL,
	bbrulestxt text NOT NULL,
	searchstatus char(3) NOT NULL,
	faqstatus char(3) NOT NULL,
	memliststatus char(3) NOT NULL,
	sitename varchar(50) NOT NULL,
	siteurl varchar(60) NOT NULL,
	avastatus varchar(4) NOT NULL,
	u2uquota smallint(5) NOT NULL,
	gzipcompress varchar(30) NOT NULL,
	boardurl varchar(60) NOT NULL,
	coppa char(3) NOT NULL,
	timeformat smallint(2) NOT NULL,
	adminemail varchar(50) NOT NULL,
	dateformat varchar(20) NOT NULL,
	sigbbcode char(3) NOT NULL,
	sightml char(3) NOT NULL,
	reportpost char(3) NOT NULL,
	bbinsert char(3) NOT NULL,
	smileyinsert char(3) NOT NULL,
	doublee char(3) NOT NULL,
	smtotal varchar(15) NOT NULL,
	smcols varchar(15) NOT NULL
	)");

	echo "<b>Done</b> <br>Modifying posts...";
	$db->query("ALTER TABLE ".$tablepre."posts ADD subject varchar(100) NOT NULL AFTER message");
		$queryt = $db->query("SELECT * FROM ".$tablepre."threads");

	while($thread = $db->fetch_array($queryt)) {
		$thread[message] = addslashes($thread[message]);
		$thread[subject] = addslashes($thread[subject]);
		$db->query("INSERT INTO ".$tablepre."posts VALUES ('$thread[fid]', '$thread[tid]', '', '$thread[author]', '$thread[message]', '$thread[subject]', '$thread[dateline]', '$thread[icon]', '$thread[usesig]', '$thread[useip]', '$thread[bbcodeoff]', '$thread[smileyoff]', '')");
	}

	$db->query("INSERT INTO ".$tablepre."settings VALUES('$langfile', '$bbname', '$postperpage', '$topicperpage', '$hottopic', '$theme', '$bbstatus', '$whosonlinestatus', '$regstatus', '$bboffreason', '$regviewonly', '$floodctrl', '$memberperpage', '$catsonly', '$hideprivate', '$emailcheck', '$bbrules', '$bbrulestxt', '$searchstatus', '$faqstatus', '$memliststatus', '$sitename', '$siteurl', '$avastatus', '$u2uquota', '$gzipcompress', '$boardurl', '$coppa', '$timeformat', '$adminemail', '$dateformat', '$sigbbcode', '$sightml', '$reportpost', 'on', 'on', 'on', '16', '4')");

	echo "<b>Done</b> <br>Inserting Templates...";
	$db->query("DROP TABLE IF EXISTS ".$tablepre."templates");
	$db->query("CREATE TABLE ".$tablepre."templates (
	id smallint(6) NOT NULL auto_increment,
	name varchar(40) NOT NULL,
	template text NOT NULL,
	PRIMARY KEY(id)
	);");
	echo "<b>Done</b> <br>Inserting the templates ...";
	$filesize=filesize('templates.xmb');
	$fp=fopen('templates.xmb','r');
	$templatesfile=fread($fp,$filesize);
	fclose($fp);
	$templates = explode("|#*XMB TEMPLATE FILE*#|", $templatesfile);
	while (list($key,$val) = each($templates)) {
		$template = explode("|#*XMB TEMPLATE*#|", $val);
			$db->query("INSERT INTO ".$tablepre."templates VALUES ('', '".addslashes($template[0])."', '".addslashes($template[1])."')");
	}
	echo "<b>Done</b> <br> Doing misc queries ...";
	$db->query("DELETE FROM ".$tablepre."templates WHERE name=''");
	$db->query("ALTER TABLE ".$tablepre."threads DROP message;"); 
	$db->query("ALTER TABLE ".$tablepre."threads DROP dateline;"); 
	$db->query("ALTER TABLE ".$tablepre."threads DROP icon;"); 
	$db->query("ALTER TABLE ".$tablepre."threads DROP usesig;"); 
	$db->query("ALTER TABLE ".$tablepre."threads DROP useip;"); 
	$db->query("ALTER TABLE ".$tablepre."threads DROP bbcodeoff;"); 
	$db->query("ALTER TABLE ".$tablepre."threads DROP smileyoff;"); 
	$db->query("ALTER TABLE ".$tablepre."threads DROP emailnotify;"); 
	$db->query("ALTER TABLE ".$tablepre."posts DROP emailnotify;"); 
	$db->query("UPDATE ".$tablepre."forums SET private='1' WHERE private='';"); 
	$db->query("UPDATE ".$tablepre."forums SET private='3' WHERE private='staff';"); 
	$db->query("ALTER TABLE ".$tablepre."themes ADD imgdir varchar(120) NOT NULL;"); 
	$db->query("ALTER TABLE ".$tablepre."themes ADD smdir varchar(120) NOT NULL;"); 
	$db->query("UPDATE ".$tablepre."themes SET imgdir='images/';"); 
	$db->query("ALTER TABLE ".$tablepre."themes DROP newtopicimg;"); 
	$db->query("ALTER TABLE ".$tablepre."themes DROP replyimg;"); 
	$db->query("ALTER TABLE ".$tablepre."threads ADD pollopts text NOT NULL;"); 
	$db->query("ALTER TABLE ".$tablepre."themes CHANGE bgcolor bgcolor varchar(25) NOT NULL;"); 
	$db->query("ALTER TABLE ".$tablepre."settings CHANGE avastatus avastatus varchar(4) NOT NULL;"); 
	$db->query("ALTER TABLE ".$tablepre."ranks CHANGE posts posts SMALLINT (6);"); 
	$db->query("ALTER TABLE ".$tablepre."forums DROP guestposting;"); 
	$db->query("DELETE FROM ".$tablepre."whosonline WHERE username='onlinerecord';"); 
	$db->query("ALTER TABLE ".$tablepre."themes DROP altfont;"); 
	$db->query("ALTER TABLE ".$tablepre."themes DROP altfontsize;"); 
	$db->query("ALTER TABLE ".$tablepre."forums ADD attachstatus varchar(15) NOT NULL;"); 
	$db->query("ALTER TABLE ".$tablepre."forums ADD pollstatus varchar(15) NOT NULL;"); 
	$db->query("UPDATE ".$tablepre."forums SET attachstatus='on', pollstatus='on';"); 
	$db->query("ALTER TABLE ".$tablepre."themes DROP postscol;"); 
	$db->query("ALTER TABLE ".$tablepre."members CHANGE password password varchar(40) NOT NULL;"); 
	$db->query("ALTER TABLE ".$tablepre."posts CHANGE pid pid int(10) NOT NULL auto_increment;"); 
	$db->query("ALTER TABLE ".$tablepre."threads CHANGE tid tid int(10) NOT NULL auto_increment;"); 
	$db->query("ALTER TABLE ".$tablepre."members CHANGE postnum postnum int(10) NOT NULL;"); 
	$db->query("ALTER TABLE ".$tablepre."forums ADD password varchar(30) NOT NULL;"); 
	$db->query("ALTER TABLE ".$tablepre."threads ADD icon varchar(75) NOT NULL AFTER subject;"); 
	$db->query("UPDATE ".$tablepre."threads SET pollopts='';"); 

	echo "<b>Done</b> <br>Adding favorites ...";
	$db->query("CREATE TABLE ".$tablepre."favorites ( 
	tid smallint(6) NOT NULL, 
	username varchar(40) NOT NULL, 
	type varchar(20) NOT NULL, 
	PRIMARY KEY(tid) 
	);"); 
	echo "<b>Done</b> <br>Adding buddies ...";

	$db->query("CREATE TABLE ".$tablepre."buddys ( 
	username varchar(40) NOT NULL, 
	buddyname varchar(40) NOT NULL 
	)");

	echo "Converting Members...";
	$query = $db->query("SELECT * FROM ".$tablepre."members");
	while($row = $db->fetch_array($query)) {
		$newpassword = md5($row[password]);
		$username = $row[username];
		$db->query("UPDATE ".$tablepre."members SET password = '$newpassword' WHERE username = '$username'");
	}

	$db->query("ALTER TABLE ".$tablepre."u2u ADD new VARCHAR(3) NOT NULL DEFAULT 'yes';");
	$db->query("ALTER TABLE ".$tablepre."settings ADD editedby VARCHAR (3) DEFAULT 'off' NOT NULL;");
	$db->query("ALTER TABLE ".$tablepre."settings ADD dotfolders VARCHAR (3) DEFAULT 'on' NOT NULL;"); 
	$db->query("ALTER TABLE ".$tablepre."settings ADD attachimgpost VARCHAR (3) DEFAULT 'on' NOT NULL;"); 
	$db->query("ALTER TABLE ".$tablepre."themes ADD cattext VARCHAR (15) NOT NULL;"); 
	$db->query("ALTER TABLE ".$tablepre."forums ADD guestposting varchar(3) NOT NULL;");

	echo "<b>Done</b> <br>Creating attachments...";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."attachments");

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

	echo "<b>Done</b> <br>Converting your settings...";
	include('settings.php');

	$db->query("INSERT INTO ".$tablepre."settings (langfile, bbname, postperpage, topicperpage, hottopic, theme, bbstatus, whosonlinestatus, regstatus, bboffreason, regviewonly, floodctrl, memberperpage, catsonly, hideprivate, emailcheck, bbrules, bbrulestxt, searchstatus, faqstatus, memliststatus, sitename, siteurl, avastatus, u2uquota, gzipcompress, boardurl, coppa, timeformat, adminemail, dateformat, sigbbcode, sightml, reportpost, bbinsert, smileyinsert, doublee, smtotal, smcols, editedby, dotfolders, attachimgpost) VALUES ('$langfile', '$bbname', '$postperpage', '$topicperpage', '$hottopic', '$theme', '$bbstatus', '$whosonlinestatus', '$regstatus', '$bboffreason', '$regviewonly', '$floodctrl', '$memberperpage', '$catsonly', '$hideprivate', '$emailcheck', '$bbrules', '$bbrulestxt', '$searchstatus', '$faqstatus', '$memliststatus', '$sitename', '$siteurl', '$avastatus', '$u2uquota', '$gzipcompress', '$boardurl', '$coppa', '$timeformat', '$adminemail', '$dateformat', '$sigbbcode', '$sightml', '$reportpost', 'on', 'on', 'on', '16', '4', 'off', 'on', 'on')");
	echo "<b>Done</b><br><br>Upgrading completed, enjoy 1.5RC5<br /> - dine and Thejavaman1<br />";

	?>
</body>
</html>