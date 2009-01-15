<?php

if (file_exists("./templates.xmb")) {

$dbname = "xmb_1_11d";
$dbuser = "";
$dbpw = "";
$dbhost = "localhost";
$tablepre = "xmb_";

class dbstuff {
	var $querynum = 0;

	function connect($dbhost="localhost", $dbuser, $dbpw, $dbname, $pconnect=0) {
		if($pconnect) {
			mysql_pconnect($dbhost, $dbuser, $dbpw);
		} else {
			mysql_connect($dbhost, $dbuser, $dbpw);
		}
		mysql_select_db($dbname);
	}

	function fetch_array($query) {
		$query = mysql_fetch_array($query);
		return $query;
	}

	function query($sql) {
		$query = mysql_query($sql) or die(mysql_error());
		$this->querynum++;
		return $query;
	}

	function result($query, $row) {
		$query = mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function insert_id() {
		$id = mysql_insert_id();
		return $id;
	}

	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}
}

$db = new dbstuff;

$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

echo "<font face=verdana size=1><B>Upgrading to XMB 1.6 Magic<a href=\"install.php\">Lantern</a></B><br /><br />";

// Attachments Start //

echo "<font face=verdana size=1><b>Starting Attachments</b></font><br>";

echo "<font face=verdana size=1>--> Dropping Table</font><br>";

$db->query("DROP TABLE IF EXISTS ".$tablepre."attachments;");

echo "<font face=verdana size=1>--> Adding Table</font><br>";

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

echo "<font face=verdana size=1><b>Finished Attachments</b></font><br><br>";

// Attachments Finished //

// Banned Start //

echo "<font face=verdana size=1><b>Starting Banned</b></font><br>";

echo "<font face=verdana size=1>--> Altering Table</font><br>";

$db->query("ALTER TABLE ".$tablepre."banned ADD KEY ip1 (ip1);");
$db->query("ALTER TABLE ".$tablepre."banned ADD KEY ip2 (ip2);");
$db->query("ALTER TABLE ".$tablepre."banned ADD KEY ip3 (ip3);");
$db->query("ALTER TABLE ".$tablepre."banned ADD KEY ip4 (ip4);");

echo "<font face=verdana size=1><B>Finished Banned</B></font><br><br>";

// Banned Finished //

// Buddys Start //

echo "<font face=verdana size=1><b>Starting Buddys</b></font><br>";

echo "<font face=verdana size=1>--> Adding Table</font><br>";

$db->query("CREATE TABLE ".$tablepre."buddys (
	username varchar(40) NOT NULL default '',
	buddyname varchar(40) NOT NULL default ''
)");

echo "<font face=verdana size=1><B>Finished Buddys</B></font><br><br>";

// Buddys Finished //

// Favorites Start //

echo "<font face=verdana size=1><b>Starting Favorites</b></font><br>";

echo "<font face=verdana size=1>--> Adding Table</font><br>";

$db->query("CREATE TABLE ".$tablepre."favorites (
	tid smallint(6) NOT NULL default '0',
	username varchar(40) NOT NULL default '',
	type varchar(20) NOT NULL default ''
)");

echo "<font face=verdana size=1><B>Finished Favorites</B></font><br><br>";

// Favorites Finished //

// Forums Start //

echo "<font face=verdana size=1><b>Starting Forums</b></font><br>";

echo "<font face=verdana size=1>--> Selecting Data</font><br>";

$query = $db->query("SELECT * FROM ".$tablepre."forums");

echo "<font face=verdana size=1>--> Dropping Table</font><br>";

$db->query("DROP TABLE ".$tablepre."forums");

echo "<font face=verdana size=1>--> Creating Table</font><br>";

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

echo "<font face=verdana size=1>--> Inserting Data</font><br>";

while($forums = mysql_fetch_array($query)){
	$db->query("INSERT INTO ".$tablepre."forums (type, fid, name, status, lastpost, moderator, displayorder, private, description, allowhtml, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, pollstatus, password, guestposting) VALUES ('$forums[type]','$forums[fid]','$forums[name]','$forums[status]','$forums[lastpost]','$forums[moderator]','$forums[displayorder]','1','".addslashes($forums[description])."','$forums[allowhtml]','$forums[allowsmilies]','$forums[allowbbcode]','$forums[userlist]','','$forums[posts]','$forums[threads]','$forums[fup]','$forums[postperm]','$forums[allowimgcode]','','','','off');");
}

echo "<font face=verdana size=1><b>Finished Forums</b></font><br><br>";

unset($query);

// Forums Finish //

// Members Start //

echo "<font face=verdana size=1><b>Starting Members</b></font><br>";

echo "<font face=verdana size=1>--> Selecting Data</font><br>";

$query = $db->query("SELECT * FROM ".$tablepre."members");

echo "<font face=verdana size=1>--> Dropping Table</font><br>";

$db->query("DROP TABLE ".$tablepre."members");

echo "<font face=verdana size=1>--> Creating Table</font><br>";

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
	pwdate BIGINT(30) NOT NULL,
	PRIMARY KEY	(uid)
)");

echo "<font face=verdana size=1>--> Inserting Data</font><br>";

while($members = mysql_fetch_array($query)){
	$members[password] = md5($members[password]);
	$db->query("INSERT INTO ".$tablepre."members (uid, username, password, regdate, postnum, email, site, aim, status, location, bio, sig, showemail, timeoffset, icq, avatar, yahoo, customstatus, theme, bday, langfile, tpp, ppp, newsletter, regip, timeformat, msn, dateformat, ban, ignoreu2u, lastvisit, mood) VALUES ('$members[uid]','$members[username]','$members[password]','$members[regdate]','$members[postnum]','$members[email]','$members[site]','$members[aim]','$members[status]','$members[location]','$members[bio]','$members[sig]','$members[showemail]','$members[timeoffset]','$members[icq]','$members[avatar]','$members[yahoo]','$members[customstatus]','XMBForum.com','$members[bday]','$members[langfile]','$members[tpp]','$members[ppp]','$members[newsletter]','$members[regip]','$members[timeformat]','$members[msn]','$members[dateformat]','','$members[ignoreu2u]','$members[lastvisit]','');");
}

echo "<font face=verdana size=1>--> Altering Data</font><br>";

	$db->query("UPDATE  ".$tablepre."members SET langfile = 'English' WHERE langfile ='english'");

echo "<font face=verdana size=1><b>Finished Members</b></font><br><br>";

unset($query);

// Members Finish //

// Posts Start //

echo "<font face=verdana size=1><b>Starting Posts</b></font><br>";

echo "<font face=verdana size=1>--> Selecting Data</font><br>";

$query = $db->query("SELECT * FROM ".$tablepre."posts");

echo "<font face=verdana size=1>--> Dropping Table</font><br>";

$db->query("DROP TABLE IF EXISTS ".$tablepre."posts;");

echo "<font face=verdana size=1>--> Creating Table</font><br>";

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

echo "<font face=verdana size=1>--> Inserting Data</font><br>";

while($posts = mysql_fetch_array($query)){
	$db->query("INSERT INTO ".$tablepre."posts (fid, tid, pid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff) VALUES ('$posts[fid]', '$posts[tid]', '$posts[pid]', '$posts[author]', '".addslashes($posts[message])."', '', '$posts[dateline]', '$posts[icon]', '$posts[usesig]', '$posts[useip]', '$posts[bbcodeoff]', '$posts[smileyoff]');");
}

echo "<font face=verdana size=1><b>Finished Posts</b></font><br><br>";

unset($query);

// Posts Finish //

// Ranks Start //

echo "<font face=verdana size=1><b>Starting Ranks</b></font><br>";

echo "<font face=verdana size=1>--> Selecting Data</font><br>";

$query = $db->query("SELECT * FROM ".$tablepre."ranks");

echo "<font face=verdana size=1>--> Dropping Table</font><br>";

$db->query("DROP TABLE IF EXISTS ".$tablepre."ranks;");

echo "<font face=verdana size=1>--> Creating Table</font><br>";

$db->query("CREATE TABLE ".$tablepre."ranks (
	title varchar(40) NOT NULL default '',
	posts smallint(6) default NULL,
	id smallint(6) NOT NULL auto_increment,
	stars smallint(6) NOT NULL default '0',
	allowavatars char(3) NOT NULL default '',
	avatarrank varchar(90) default NULL,
	PRIMARY KEY	(id)
)");

echo "<font face=verdana size=1>--> Inserting Data</font><br>";

while($rank = mysql_fetch_array($query)){
	$db->query("INSERT INTO ".$tablepre."ranks (title, posts, id, stars, allowavatars, avatarrank) VALUES ( '$rank[title]', '$rank[posts]', '$rank[id]', '$rank[stars]', '$rank[allowavatars]', '$rank[avatarrank]');");
}

echo "<font face=verdana size=1><b>Finished Ranks</b></font><br><br>";

unset($query);

// Ranks Finish //

// Restricted Start //

echo "<font face=verdana size=1><b>Starting Restricted</b></font><br>";

echo "<font face=verdana size=1>--> Dropping Table</font><br>";

$db->query("DROP TABLE IF EXISTS ".$tablepre."restricted;");

echo "<font face=verdana size=1>--> Creating Table</font><br>";

$db->query("CREATE TABLE ".$tablepre."restricted (
	name varchar(25)  NOT NULL,
	id smallint(6) NOT NULL auto_increment,
	PRIMARY KEY	(id)
)");

echo "<font face=verdana size=1><b>Finished Restricted</b></font><br><br>";

unset($query);

// Restricted Finish //

// Settings Start //

echo "<font face=verdana size=1><b>Starting Settings</b></font><br>";

echo "<font face=verdana size=1>--> Dropping Table</font><br>";

$db->query("DROP TABLE IF EXISTS ".$tablepre."settings;");

echo "<font face=verdana size=1>--> Creating Table</font><br>";

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
	authorstatus char(3),
	tickerstatus char(3) NOT NULL default '',
	tickercontents text,
	tickerdelay char(10)
)");

echo "<font face=verdana size=1>--> Inserting Data</font><br>";

include('settings.php');

$db->query("INSERT INTO ".$tablepre."settings (langfile, bbname, postperpage, topicperpage, hottopic, theme, bbstatus, whosonlinestatus, regstatus, bboffreason, regviewonly, floodctrl, memberperpage, catsonly, hideprivate, emailcheck, bbrules, bbrulestxt, searchstatus, faqstatus, memliststatus, sitename, siteurl, avastatus, u2uquota, gzipcompress, boardurl, coppa, timeformat, adminemail, dateformat, sigbbcode, sightml, reportpost, bbinsert, smileyinsert, doublee, smtotal, smcols, editedby, dotfolders, attachimgpost, todaysposts, stats, authorstatus) VALUES ('English', '$bbname', '$postperpage', '$topicperpage', '$hottopic', 'XMBForum.com', '$bbstatus', '$whosonlinestatus', '$regstatus', '".addslashes($regviewonly)."', '$regviewonly', '$floodctrl', '$memberperpage', '$catsonly', '$hideprivate', '$emailcheck', '$bbrules', '".addslashes($bbrulestxt)."', '$searchstatus', '$faqstatus', '$memliststatus', '$sitename', '$siteurl', '$avastatus', '$u2uquota', '$gzipcompress', '$boardurl', '$coppa', '12', '$adminemail', '$dateformat', '$sigbbcode', '$sightml', '$reportpost', 'on', 'on', 'off', '16', '4', 'off', 'on', 'on', 'off', '', '4000');");


echo "<font face=verdana size=1><b>Finished Settings</b></font><br><br>";

unset($query);

// Settings Finish //

// Smilies Start //

echo "<font face=verdana size=1><b>Starting Smilies</b></font><br>";

echo "<font face=verdana size=1>--> Selecting Data</font><br>";

$query = $db->query("SELECT * FROM ".$tablepre."smilies");

echo "<font face=verdana size=1>--> Dropping Table</font><br>";

$db->query("DROP TABLE IF EXISTS ".$tablepre."smilies;");

echo "<font face=verdana size=1>--> Creating Table</font><br>";

$db->query("CREATE TABLE ".$tablepre."smilies (
	type varchar(15) NOT NULL default '',
	code varchar(40) NOT NULL default '',
	url varchar(40) NOT NULL default '',
	id smallint(6) NOT NULL auto_increment,
	PRIMARY KEY	(id)
)");

echo "<font face=verdana size=1>--> Inserting Data</font><br>";

while($smilies = mysql_fetch_array($query)){
	$db->query("INSERT INTO ".$tablepre."smilies (type, code, url, id) VALUES ( '$smilies[type]', '$smilies[code]', '$smilies[url]', '$smilies[id]');");
}

echo "<font face=verdana size=1><b>Finished Smilies</b></font><br><br>";

unset($query);

// Smilies Finish //

// Templates Start //

echo "<font face=verdana size=1><b>Starting Templates</b></font><br>";

echo "<font face=verdana size=1>--> Dropping Table</font><br>";

$db->query("DROP TABLE IF EXISTS ".$tablepre."templates;");

echo "<font face=verdana size=1>--> Adding Table</font><br>";

$db->query("CREATE TABLE ".$tablepre."templates (
	id smallint(6) NOT NULL auto_increment,
	name varchar(40) NOT NULL default '',
	template text NOT NULL,
	PRIMARY KEY	(id)
)");

echo "<font face=verdana size=1>--> Inserting Data</font><br>";

$filesize=filesize('templates.xmb');
$fp=fopen('templates.xmb','r');
$templatesfile=fread($fp,$filesize);
fclose($fp);
$templates = explode("|#*XMB TEMPLATE FILE*#|", $templatesfile);
	while (list($key,$val) = each($templates)) {
		$template = explode("|#*XMB TEMPLATE*#|", $val);
		$template[1] = addslashes($template[1]);
		$db->query("INSERT INTO ".$tablepre."templates (id, name, template) VALUES ('', '".addslashes($template[0])."', '".addslashes($template[1])."')");
	}
$db->query("DELETE FROM ".$tablepre."templates WHERE name=''");

echo "<font face=verdana size=1><B>Finished Templates</B></font><br><br>";

// Templates Finished //

// Themes Start //

echo "<font face=verdana size=1><b>Starting Themes</b></font><br>";

echo "<font face=verdana size=1>--> Dropping Table</font><br>";

$db->query("DROP TABLE IF EXISTS ".$tablepre."themes;");

echo "<font face=verdana size=1>--> Creating Table</font><br>";

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

echo "<font face=verdana size=1>--> Inserting Data</font><br>";

$db->query("INSERT INTO ".$tablepre."themes (name, bgcolor, altbg1, altbg2, link, dummy, bordercolor, header, headertext, top, catcolor, tabletext, text, borderwidth, tablewidth, tablespace, font, fontsize, boardimg, imgdir, smdir, cattext) VALUES ('XMBForum.com', '#8896A7', '#8A9AAD', '#6C7D92', '#000000', NULL, '#000000', '#456281', '#FFFFFF', '#6C7D92', '#456281', '#000000', '#000000', '1', '90%', '5', 'Verdana', '10px', 'boardheader.gif', 'images/xmbforum', 'images/smilies', '#FFFFFF');");

$db->query("INSERT INTO ".$tablepre."themes (name, bgcolor, altbg1, altbg2, link, dummy, bordercolor, header, headertext, top, catcolor, tabletext, text, borderwidth, tablewidth, tablespace, font, fontsize, boardimg, imgdir, smdir, cattext) VALUES ('AventureMedia', '#1F3145', '#011B35', '#304459', '#FFFFFF', NULL, '#000000', '#011B35', '#FFFFFF', '#011B35', '#011B35', '#FFFFFF', '#FFFFFF', '1', '97%', '6', 'Verdana', '10px', 'xmbheader.gif', 'images/aventure', 'images/smilies', '#FFFFFF');");

echo "<font face=verdana size=1><b>Finished Themes</b></font><br><br>";

unset($query);

// Themes Finish //

// Threads Start //

echo "<font face=verdana size=1><b>Starting Threads</b></font><br>";

echo "<font face=verdana size=1>--> Selecting Data</font><br>";

$query = $db->query("SELECT * FROM ".$tablepre."threads");
$query_posts = $db->query("SELECT * FROM ".$tablepre."threads");

echo "<font face=verdana size=1>--> Dropping Table</font><br>";

$db->query("DROP TABLE ".$tablepre."threads");

echo "<font face=verdana size=1>--> Creating Table</font><br>";

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

echo "<font face=verdana size=1>--> Inserting Data</font><br>";

while($threads = mysql_fetch_array($query)){
	$db->query("INSERT INTO ".$tablepre."threads (tid, fid, subject, icon, lastpost, views, replies, author, closed, topped, pollopts) VALUES ('$threads[tid]', '$threads[fid]', '".addslashes($threads[subject])."', '$threads[icon]', '$threads[lastpost]', '$threads[views]', '$threads[replies]', '$threads[author]', '$threads[closed]', '$threads[topped]', '$threads[pollopts]');");
}

while($posts = mysql_fetch_array($query_posts)){
	$db->query("INSERT INTO ".$tablepre."posts (fid, tid, pid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff) VALUES ('$posts[fid]', '$posts[tid]', '', '$posts[author]', '".addslashes($posts[message])."', '".addslashes($posts[subject])."', '$posts[dateline]', '$posts[icon]', '$posts[usesig]', '127.0.0.1', '', '');");
}

echo "<font face=verdana size=1><b>Finished Threads</b></font><br><br>";

unset($query);
unset($query_posts);

// Threads Finish //

// U2U Start //

echo "<font face=verdana size=1><b>Starting U2U</b></font><br>";

echo "<font face=verdana size=1>--> Selecting Data</font><br>";

$query = $db->query("SELECT * FROM ".$tablepre."u2u");

echo "<font face=verdana size=1>--> Dropping Table</font><br>";

$db->query("DROP TABLE IF EXISTS ".$tablepre."u2u;");

echo "<font face=verdana size=1>--> Creating Table</font><br>";

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

echo "<font face=verdana size=1>--> Inserting Data</font><br>";

while($u2u = mysql_fetch_array($query)){
	$db->query("INSERT INTO ".$tablepre."u2u (u2uid, msgto, msgfrom, dateline, subject, message, folder, new, readstatus) VALUES ('$u2u[u2uid]','$u2u[msgto]','$u2u[msgfrom]','$u2u[dateline]','".addslashes($u2u[subject])."','".addslashes($u2u[message])."','$u2u[folder]','yes','no');");
}

echo "<font face=verdana size=1><b>Finished U2U</b></font><br><br>";

unset($query);

// U2U Finish //

// Whosonline Start //

echo "<font face=verdana size=1><b>Starting Whosonline</b></font><br>";

echo "<font face=verdana size=1>--> No Changes Needed</font><br>";

echo "<font face=verdana size=1><b>Finished Whosonline</b></font><br><br>";

unset($query);

// Whosonline Finish //

// Words Start //

echo "<font face=verdana size=1><b>Starting Words</b></font><br>";

echo "<font face=verdana size=1>--> No Changes Needed</font><br>";

echo "<font face=verdana size=1><b>Finished Words</b></font><br><br>";

unset($query);

// Words Finish //

$db->query("create index fid on ".$tablepre."posts (fid);");
$db->query("create index tid on ".$tablepre."posts (tid);");
$db->query("ALTER TABLE `".$tablepre."_posts` ADD INDEX ( `author` )");
$db->query("create index fid on ".$tablepre."threads (fid);");
$db->query("create index tid on ".$tablepre."threads (tid);");
$db->query("create index username on ".$tablepre."members (username(25));");
$db->query("create index status on ".$tablepre."members (status(35));");
$db->query("ALTER TABLE `".$tablepre."_members` ADD INDEX ( `email` )");
$db->query("ALTER TABLE `".$tablepre."_members` ADD INDEX ( `password` )");
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
$db->query("create index status on ".$tablepre."forums (status);");
$db->query("create index title on ".$tablepre."ranks (title);");
$db->query("ALTER TABLE ".$tablepre."themes DROP PRIMARY KEY, ADD PRIMARY KEY(name);");
$db->query("INSERT INTO ".$tablepre."themes VALUES ('Windows XP Silver', '#FFFFFF', '#EDF0F7', '#FFFFFF', '#000000', NULL, '#C4C8D4', '#FFFFFF', '#000000', '#FFFFFF', 'silverbar.gif', '#000000', '#000000', '1', '90%', '4', 'Verdana', '10px', 'xplogo.gif', 'images/xpsilver', 'images/smilies', '#000000');");
$db->query("INSERT INTO ".$tablepre."themes VALUES ('Windows XP Blue', '#FFFFFF', '#ADD1FF', '#FFFFFF', '#0055E5', NULL, '#0055E5', '#0055E5', '#FFFFFF', '#FFFFFF', 'bluebar.gif', '#000000', '#000000', '1', '90%', '4', 'Verdana', '10px', 'xplogo.gif', 'images/xpblue', 'images/smilies', '#FFFFFF');");
$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Super Administrator', '0', '9', '', 'yes', '');");
$db->query("UPDATE ".$tablepre."members SET status='Super Administrator' WHERE status = 'Administrator'");

echo "<font face=verdana size=1><b>Upgrade Complete</font></b>";

} else {

die("<font face=verdana size=1>Error: template.xmb was not found on the server and the script cannot run without that file please upload it and try again.</font>");

}

?>