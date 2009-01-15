<html>
<head>
	<title>XMB 1.6 Magic Lantern Installation</title>
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
	echo "<b>To upgrade click <a href=\"upgrade.php?cmd=do_upgrade\">here</a></b>";

}

elseif($cmd == "do_upgrade") {

echo "Upgrading to XMB 1.6 Magic Lantern<br>";

//
// Drop Templates And Recreate Table
//

	$db->query("DROP TABLE IF EXISTS ".$tablepre."templates;");
	$db->query("CREATE TABLE ".$tablepre."templates (
		id smallint(6) NOT NULL auto_increment,
		name varchar(40) NOT NULL default '',
		template text NOT NULL,
		PRIMARY KEY	(id)
	)");



//
// Create Restriction Manager Table And Insert Default Data
//

echo "Creating ".$tablepre."restricted<br>";

	$db->query("DROP TABLE IF EXISTS ".$tablepre."restricted;");
	$db->query("CREATE TABLE ".$tablepre."restricted (
		name varchar(25)  NOT NULL,
		id smallint(6) NOT NULL auto_increment,
		PRIMARY KEY	(id)
	)");

//
// Insert Templates.xmb Into Newly Created Templates Table

//
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



echo "Inserting data into necessary tables<br>";

	$db->query("INSERT INTO ".$tablepre."themes VALUES ('XMBForum.com', '#8896A7', '#8A9AAD', '#6C7D92', '#000000', NULL, '#000000', '#456281', '#FFFFFF', '#6C7D92', '#456281', '#000000', '#000000', '1', '90%', '5', 'Verdana', '10px', 'boardheader.gif', 'images/xmbforum', 'images/smilies', '#FFFFFF');");

	$db->query("INSERT INTO ".$tablepre."themes VALUES ('AventureMedia', '#1F3145', '#011B35', '#304459', '#FFFFFF', NULL, '#000000', '#011B35', '#FFFFFF', '#011B35', '#011B35', '#FFFFFF', '#FFFFFF', '1', '97%', '6', 'Verdana', '10px', 'xmbheader.gif', 'images/aventure', 'images/smilies', '#FFFFFF');");

echo "Modifying Tables";

	$db->query("ALTER TABLE ".$tablepre."members ADD mood varchar(15) NOT NULL");
	$db->query("ALTER TABLE ".$tablepre."members ADD ban varchar(15) NOT NULL AFTER dateformat");
	$db->query("ALTER TABLE ".$tablepre."settings ADD todaysposts varchar(3) NOT NULL");
	$db->query("ALTER TABLE ".$tablepre."settings ADD stats varchar(3) NOT NULL");
	$db->query("ALTER TABLE ".$tablepre."settings ADD authorstatus varchar(3) NOT NULL");
	$db->query("ALTER TABLE ".$tablepre."u2u ADD readstatus varchar(5) NOT NULL");

echo "<b>Upgrade Complete</b>To skip the exit screen click <a href=\"index.php\">here</a><br>.";
echo "<b>Please wait, Transferring..</b><meta http-equiv=\"Refresh\" content=\"1; url=completed.html\">";


}

?>

</body>
</html>
