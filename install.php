<?
require "./config.php";
require "./functions.php";
require "./db/$database.php";
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

$tables = array('attachments', 'banned', 'buddys', 'favorites', 'forums', 'members', 'posts', 'ranks', 'settings', 'smilies', 'templates', 'themes', 'threads', 'u2u', 'whosonline', 'words');
foreach($tables as $name) {
${'table_'.$name} = $tablepre.$name;
}

echo "Creating $table_attachments...";
$db->query("CREATE TABLE $table_attachments (
aid smallint(6) NOT NULL auto_increment,
tid smallint(6) NOT NULL,
pid smallint(6) NOT NULL,
filename varchar(120) NOT NULL,
filetype varchar(120) NOT NULL,
attachment blob NOT NULL,
downloads smallint(6) NOT NULL,
PRIMARY KEY(aid)
);");
echo "<b>Done</b><br>";

echo "Creating $table_banned...";
$db->query("CREATE TABLE $table_banned (
ip1 smallint(3) NOT NULL,
ip2 smallint(3) NOT NULL,
ip3 smallint(3) NOT NULL,
ip4 smallint(3) NOT NULL,
dateline bigint(30) NOT NULL,
id SMALLINT(6) NOT NULL,
PRIMARY KEY(id)
);");
echo "<b>Done</b><br>";

echo "Creating $table_buddys...";
$db->query("CREATE TABLE $table_buddys (
username varchar(40) NOT NULL,
buddyname varchar(40) NOT NULL
);");
echo "<b>Done</b><br>";

echo "Creating $table_favorites...";
$db->query("CREATE TABLE $table_favorites (
tid smallint(6) NOT NULL,
username varchar(40) NOT NULL,
type varchar(20) NOT NULL
);");
echo "<b>Done</b><br>";

echo "Creating $table_forums...";
$db->query("CREATE TABLE $table_forums (
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
userlist text NOT NULL,
theme varchar(30) NOT NULL,
posts int(100) NOT NULL,
threads int(100) NOT NULL,
fup smallint(6) NOT NULL,
postperm varchar(3) NOT NULL,
allowimgcode varchar(3) NOT NULL,
attachstatus varchar(15) NOT NULL,
pollstatus varchar(15) NOT NULL,
password varchar(30) NOT NULL,
guestposting varchar(3) NOT NULL,
PRIMARY KEY(fid)
);");
echo "<b>Done</b><br>";

echo "Creating $table_members...";
$db->query("CREATE TABLE $table_members (
uid smallint(6) NOT NULL auto_increment,
username varchar(25) NOT NULL,
password varchar(40) NOT NULL,
regdate bigint(30) NOT NULL,
postnum int(10) NOT NULL,
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
lastvisit varchar(30),
PRIMARY KEY(uid)
);");
echo "<b>Done</b><br>";

echo "Creating $table_posts...";
$db->query("CREATE TABLE $table_posts (
fid smallint(6) NOT NULL,
tid smallint(6) NOT NULL,
pid int(10) NOT NULL auto_increment,
author varchar(40) NOT NULL,
message text NOT NULL,
subject varchar(100) NOT NULL,
dateline bigint(30) NOT NULL,
icon varchar(50),
usesig varchar(15) NOT NULL,
useip varchar(40) NOT NULL,
bbcodeoff varchar(15) NOT NULL,
smileyoff varchar(15) NOT NULL,
PRIMARY KEY(pid)
);");
echo "<b>Done</b><br>";

echo "Creating $table_ranks...";
$db->query("CREATE TABLE $table_ranks (
title varchar(40) NOT NULL,
posts smallint(6),
id smallint(6) NOT NULL auto_increment,
stars smallint(6) NOT NULL,
allowavatars varchar(3) NOT NULL,
avatarrank varchar(90),
PRIMARY KEY(id)
);");
echo "<b>Done</b><br>";

echo "Creating $table_settings...";
$db->query("CREATE TABLE $table_settings (
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
smcols varchar(15) NOT NULL,
editedby varchar(3) NOT NULL,
dotfolders varchar(3) NOT NULL,
attachimgpost varchar(3) NOT NULL
);");
echo "<b>Done</b><br>";

echo "Creating $table_smilies...";
$db->query("CREATE TABLE $table_smilies (
type varchar(15) NOT NULL,
code varchar(40) NOT NULL,
url varchar(40) NOT NULL,
id smallint(6) NOT NULL auto_increment,
PRIMARY KEY(id)
);");
echo "<b>Done</b><br>";

echo "Creating $table_templates...";
$db->query("CREATE TABLE $table_templates (
id smallint(6) NOT NULL auto_increment,
name varchar(40) NOT NULL,
template text NOT NULL,
PRIMARY KEY(id)
);");
echo "<b>Done</b><br>";

echo "Creating $table_themes...";
$db->query("CREATE TABLE $table_themes (
name varchar(30) NOT NULL,
bgcolor varchar(25) NOT NULL,
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
boardimg varchar(50),
imgdir varchar(120) NOT NULL,
smdir varchar(120) NOT NULL,
cattext varchar(15) NOT NULL
);");
echo "<b>Done</b><br>";

echo "Creating $table_threads...";
$db->query("CREATE TABLE $table_threads (
tid int(10) NOT NULL auto_increment,
fid smallint(6) NOT NULL,
subject varchar(100) NOT NULL,
icon varchar(75) NOT NULL,
lastpost varchar(30) NOT NULL,
views int(100) NOT NULL,
replies int(100) NOT NULL,
author varchar(40) NOT NULL,
closed varchar(15) NOT NULL,
topped smallint(6) NOT NULL,
pollopts text NOT NULL,
PRIMARY KEY(tid)
);");
echo "<b>Done</b><br>";

echo "Creating $table_u2u...";
$db->query("CREATE TABLE $table_u2u (
u2uid smallint(6) NOT NULL auto_increment,
msgto varchar(40) NOT NULL,
msgfrom varchar(40) NOT NULL,
dateline bigint(30) NOT NULL,
subject varchar(75) NOT NULL,
message text NOT NULL,
folder varchar(40) NOT NULL,
new varchar(3) NOT NULL,
PRIMARY KEY(u2uid)
);");
echo "<b>Done</b><br>";

echo "Creating $table_whosonline...";
$db->query("CREATE TABLE $table_whosonline (
username varchar(40) NOT NULL,
ip varchar(40) NOT NULL,
time bigint(30) NOT NULL,
location varchar(150) NOT NULL
);");
echo "<b>Done</b><br>";

echo "Creating $table_words...";
$db->query("CREATE TABLE $table_words (
find varchar(60) NOT NULL,
replace1 varchar(60) NOT NULL,
id smallint(6) NOT NULL auto_increment,
PRIMARY KEY(id)
);");
echo "<b>Done</b><br>";

echo "Inserting Default Data into $table_themes...";
$db->query("INSERT INTO $table_themes VALUES ( 'xmb1', '#ffffff', '#dededf', '#eeeeee', '#004455', '#778899', '#005555', '#ffffff', '#ffffff', '#dcdcde', '#003300', '#002200', '1', '94%', '4', 'Arial', '12px', 'logo1.gif', 'images/xmb1', 'images/smilies', '#004455')");
$db->query("INSERT INTO $table_themes VALUES ( 'xmb2', 'background.gif', '#e3e3ea', '#eeeef6', '#404060', '#404060', '#ffffff', '#505070', '#ffffff', '#e3e3ea', '#000033', '#000022', '1', '730', '6', 'Arial', '12px', 'logo1.gif', 'images/xmb2', 'images/smilies', '#404060')");
echo "<b>Done</b><br>";

echo "Inserting Default Data into $table_smilies...";
$db->query("INSERT INTO $table_smilies VALUES ( 'smiley', ':)', 'smile.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'smiley', ':(', 'sad.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'smiley', ':D', 'biggrin.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'smiley', ';)', 'wink.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'smiley', ':cool:', 'cool.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'smiley', ':mad:', 'mad.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'smiley', ':o', 'shocked.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'smiley', ':P', 'tongue.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'smile.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'sad.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'biggrin.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'wink.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'cool.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'mad.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'shocked.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'thumbup.gif', '')");
$db->query("INSERT INTO $table_smilies VALUES ( 'picon', '', 'thumbdown.gif', '')");
echo "<b>Done</b><br>";

echo "Inserting Default Data into $table_ranks...";
$db->query("INSERT INTO $table_ranks VALUES ('Newbie', '1', '', '1', 'yes', '')");
$db->query("INSERT INTO $table_ranks VALUES ('Junior Member', '2', '', '2', 'yes', '')");
$db->query("INSERT INTO $table_ranks VALUES ('Member', '100', '', '3', 'yes', '')");
$db->query("INSERT INTO $table_ranks VALUES ('Senior Member', '500', '', '4', 'yes', '')");
$db->query("INSERT INTO $table_ranks VALUES ('Posting Freak', '1000', '', '5', 'yes', '')");
$db->query("INSERT INTO $table_ranks VALUES ( 'Moderator', '0', '', '6', 'yes', '')");
$db->query("INSERT INTO $table_ranks VALUES ( 'Super Moderator', '0', '', '7', 'yes', '')");
$db->query("INSERT INTO $table_ranks VALUES ( 'Administrator', '0', '', '8', 'yes', '')");
echo "<b>Done</b><br>";

echo "Inserting Default Data into $table_settings...";

$db->query("INSERT INTO $table_settings VALUES('english', 'XMB Forums', '25', '30', '20', 'xmb1', 'on', 'on', 'on', '', 'off', '5', '45', 'off', 'on', 'off', 'off', '', 'on', 'on', 'on', 'XMBForum.com', 'http://www.xmbforum.com/', 'on', '75', 'off', 'http://www.xmbforum.com/xmb/', 'off', '12', 'kb9kss@xmbforum.com', 'mm-dd-yyyy', 'on', 'off', 'on', 'on', 'on', 'on', '16', '4', 'on', 'on', 'on')");
echo "<b>Done</b><br>";
echo "Inserting Default Data into $table_templates...";
$filesize=filesize('templates.xmb');
$fp=fopen('templates.xmb','r');
$templatesfile=fread($fp,$filesize);
fclose($fp);
$templates = explode("|#*XMB TEMPLATE FILE*#|", $templatesfile);
while (list($key,$val) = each($templates)) {
$template = explode("|#*XMB TEMPLATE*#|", $val);
$template[1] = addslashes($template[1]);
$db->query("INSERT INTO $table_templates VALUES ('', '".addslashes($template[0])."', '".addslashes($template[1])."')");
}
$db->query("DELETE FROM $table_templates WHERE name=''");
echo "<b>Done</b><br><br>";

echo "<b>Install successful!</b><br><br> The installation of XMB onto your server was successful. You can visit your board by clicking <a href=\"index.php\">here</a>.<br><br>The XMB Team thanks you for using XMB";
?>