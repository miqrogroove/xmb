<?
require "config.php";

mysql_connect($dbhost, $dbuser, $dbpw) or die(mysql_error());
mysql_select_db($dbname) or die(mysql_error());

$tables = array('banned','forums', 'members', 'posts', 'ranks', 'smilies', 'themes', 'threads', 'u2u', 'whosonline', 'words');
foreach($tables as $name) {
${'table_'.$name} = $tablepre.$name;
}

mysql_query("CREATE TABLE $table_banned (
ip1 smallint(3) NOT NULL,
ip2 smallint(3) NOT NULL,
ip3 smallint(3) NOT NULL,
ip4 smallint(3) NOT NULL,
dateline BIGINT(30) NOT NULL,
id SMALLINT(6) NOT NULL,
PRIMARY KEY(id)
);") or die(mysql_error());

mysql_query("CREATE TABLE $table_forums (
type varchar(15) NOT NULL,
fid smallint(6) NOT NULL auto_increment,
name varchar(50) NOT NULL,
status varchar(15) NOT NULL,
lastpost varchar(30) NOT NULL,
moderator varchar(100) NOT NULL,
displayorder smallint(6) NOT NULL,
private varchar(30),
description text,
allowhtml varchar(3) NOT NULL,
allowsmilies varchar(3) NOT NULL,
allowbbcode varchar(3) NOT NULL,
guestposting varchar(3) NOT NULL,
userlist text NOT NULL,
theme varchar(30) NOT NULL,
posts int(100) NOT NULL,
threads int(100) NOT NULL,
fup smallint(6) NOT NULL,
postperm varchar(3) NOT NULL,
allowimgcode varchar(3) NOT NULL,
PRIMARY KEY(fid)
);") or die(mysql_error());

mysql_query("CREATE TABLE $table_members (
uid smallint(6) NOT NULL auto_increment,
username varchar(25) NOT NULL,
password varchar(18) NOT NULL,
regdate bigint(30) NOT NULL,
postnum smallint(6) NOT NULL,
email varchar(60),
site varchar(75),
aim varchar(40),
status varchar(35) NOT NULL,
location varchar(50),
bio text,
sig text,
showemail varchar(15) NOT NULL,
timeoffset int(5) NOT NULL,
icq varchar(30) NOT NULL,
avatar varchar(90),
yahoo varchar(40) NOT NULL,
customstatus varchar(100) NOT NULL,
theme varchar(30) NOT NULL,
bday varchar(50),
langfile varchar(40) NOT NULL,
tpp smallint(6) NOT NULL,
ppp smallint(6) NOT NULL,
newsletter varchar(3) NOT NULL,
regip varchar(40) NOT NULL,
timeformat int(5) NOT NULL,
msn varchar(40) NOT NULL,
dateformat varchar(10) NOT NULL,
ignoreu2u text,
lastvisit BIGINT(30),
PRIMARY KEY(uid)
);") or die(mysql_error());

mysql_query("CREATE TABLE $table_posts (
fid smallint(6) NOT NULL,
tid smallint(6) NOT NULL,
pid smallint(8) NOT NULL auto_increment,
author varchar(40) NOT NULL,
message text NOT NULL,
dateline BIGINT(30) NOT NULL,
icon varchar(50),
usesig varchar(15) NOT NULL,
useip varchar(40) NOT NULL,
bbcodeoff varchar(15) NOT NULL,
smileyoff varchar(15) NOT NULL,
emailnotify varchar(15) NOT NULL,
PRIMARY KEY(pid)
);") or die(mysql_error());

mysql_query("CREATE TABLE $table_ranks (
title varchar(40) NOT NULL,
posts smallint(6) NOT NULL,
id smallint(6) NOT NULL auto_increment,
stars smallint(6) NOT NULL,
allowavatars varchar(3) NOT NULL,
avatarrank varchar(90),
PRIMARY KEY(id)
);") or die(mysql_error());

mysql_query("CREATE TABLE $table_smilies (
type varchar(15) NOT NULL,
code varchar(40) NOT NULL,
url varchar(40) NOT NULL,
id smallint(6) NOT NULL auto_increment,
PRIMARY KEY(id)
);") or die(mysql_error());

mysql_query("CREATE TABLE $table_themes (
name varchar(30) NOT NULL,
bgcolor varchar(15) NOT NULL,
altbg1 varchar(15) NOT NULL,
altbg2 varchar(15) NOT NULL,
link varchar(15) NOT NULL,
bordercolor varchar(15) NOT NULL,
header varchar(15) NOT NULL,
headertext varchar(15) NOT NULL,
top varchar(15) NOT NULL,
catcolor varchar(15) NOT NULL,
tabletext varchar(15) NOT NULL,
text varchar(15) NOT NULL,
borderwidth varchar(15) NOT NULL,
tablewidth varchar(15) NOT NULL,
tablespace varchar(15) NOT NULL,
font varchar(40) NOT NULL,
fontsize varchar(40) NOT NULL,
altfont varchar(40) NOT NULL,
altfontsize varchar(40) NOT NULL,
replyimg varchar(50),
newtopicimg varchar(50),
boardimg varchar(50),
postscol varchar(5) NOT NULL
);") or die(mysql_error());

mysql_query("CREATE TABLE $table_threads (
tid smallint(6) NOT NULL auto_increment,
fid smallint(6) NOT NULL,
subject varchar(100) NOT NULL,
lastpost varchar(30) NOT NULL,
views int(100) NOT NULL,
replies int(100) NOT NULL,
author varchar(40) NOT NULL,
message text NOT NULL,
dateline BIGINT(30) NOT NULL,
icon varchar(50),
usesig varchar(15) NOT NULL,
closed varchar(15) NOT NULL,
topped smallint(6) NOT NULL,
useip varchar(40) NOT NULL,
bbcodeoff varchar(15) NOT NULL,
smileyoff varchar(15) NOT NULL,
emailnotify varchar(15) NOT NULL,
PRIMARY KEY(tid)
);") or die(mysql_error());

mysql_query("CREATE TABLE $table_u2u (
u2uid smallint(6) NOT NULL auto_increment,
msgto varchar(40) NOT NULL,
msgfrom varchar(40) NOT NULL,
dateline BIGINT(30) NOT NULL,
subject varchar(75) NOT NULL,
message text NOT NULL,
folder varchar(40) NOT NULL,
PRIMARY KEY(u2uid)
);") or die(mysql_error());

mysql_query("CREATE TABLE $table_whosonline (
username varchar(40) NOT NULL,
ip varchar(40) NOT NULL,
time BIGINT(40) NOT NULL,
location varchar(150) NOT NULL
);") or die(mysql_error());

mysql_query("CREATE TABLE $table_words (
find varchar(60) NOT NULL,
replace1 varchar(60) NOT NULL,
id smallint(6) NOT NULL auto_increment,
PRIMARY KEY(id)
);") or die(mysql_error());


mysql_query("INSERT INTO $table_themes VALUES ( 'gray', '#ffffff', '#dededf', '#eeeeee', '#333399', '#778899', '#778899', '#ffffff', '#eeeeee', '#dcdcde', '#000000', '#000000', '1', '97%', '6', 'Arial', '12px', 'Verdana', '10px', '', '', 'images/logo1.gif', '2col')") or die(mysql_error());
mysql_query("INSERT INTO $table_themes VALUES ( 'blue', '#ffffff', '#b0c0d0', '#d0e0f0', '#cc6600', '#000000', '#e0f0f9', '#000000', '#d0e0f0', '#b0c0d4', '#000000', '#000000', '1', '97%', '6', 'Arial', '12px', 'Verdana', '10px', '', '', 'images/logo1.gif', '2col')") or die(mysql_error());



mysql_query("INSERT INTO $table_whosonline VALUES ('onlinerecord', '-1', '', '')") or die(mysql_error());

mysql_query("INSERT INTO $table_smilies VALUES ( 'smiley', ':)', 'smilies/smile.gif', '1')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'smiley', ':(', 'smilies/sad.gif', '2')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'smiley', ':D', 'smilies/bigsmile.gif', '3')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'smiley', ';)', 'smilies/wink.gif', '4')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'smiley', ':cool:', 'smilies/cool.gif', '5')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'smiley', ':mad:', 'smilies/mad.gif', '6')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'smiley', ':o', 'smilies/shocked.gif', '7')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'smiley', ':P', 'smilies/tongue.gif', '8')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'smilies/smile.gif', '9')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'smilies/sad.gif', '10')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'smilies/bigsmile.gif', '11')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'smilies/wink.gif', '12')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'smilies/cool.gif', '13')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'smilies/mad.gif', '14')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'smilies/shocked.gif', '15')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'smilies/tongue.gif', '16')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'smilies/exclamation.gif', '17')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'smilies/question.gif', '18')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'smilies/thumbup.gif', '19')") or die(mysql_error());
mysql_query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'smilies/thumbdown.gif', '20')") or die(mysql_error());

mysql_query("INSERT INTO $table_words VALUES ('damn', '****', '')") or die(mysql_error());
mysql_query("INSERT INTO $table_words VALUES ('shit', '****', '')") or die(mysql_error());
mysql_query("INSERT INTO $table_words VALUES ('fuck', '****', '')") or die(mysql_error());
mysql_query("INSERT INTO $table_words VALUES ('bitch', '*****', '')") or die(mysql_error());
mysql_query("INSERT INTO $table_words VALUES ('ass', '***', '')") or die(mysql_error());

mysql_query("INSERT INTO $table_ranks VALUES ('Newbie', '1', '', '1', 'yes', '')") or die(mysql_error());
mysql_query("INSERT INTO $table_ranks VALUES ('Junior Member', '2', '', '2', 'yes', '')") or die(mysql_error());
mysql_query("INSERT INTO $table_ranks VALUES ('Member', '100', '', '3', 'yes', '')") or die(mysql_error());
mysql_query("INSERT INTO $table_ranks VALUES ('Senior Member', '500', '', '4', 'yes', '')") or die(mysql_error());
mysql_query("INSERT INTO $table_ranks VALUES ('Posting Freak', '1000', '', '5', 'yes', '')") or die(mysql_error());

echo "Install successful!<br>Please delete install.php before continuing!!!";
?>