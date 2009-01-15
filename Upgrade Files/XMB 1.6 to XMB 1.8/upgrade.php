<html>
<head>
	<title>XMB 1.6 -> XMB 1.8</title>
	<body text="#FFFFFF" bgcolor="#8896A7" link="#FFFFFF" vlink="#FFFFFF" alink="#FFFFFF">
	<!--Created By Aventure Media & The XMB Group-->
	<!--www.aventure-media.co.uk  www.xmbforum.com-->
</head>

<body>
<?
require './config.php';
require './functions.php';
require "./db/$database.php";
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);

if(!$cmd) {

	echo "<b>XMB 1.6 -> XMB 1.8 Upgrade</b><br />";
	echo "<b>To upgrade click <a href=\"upgrade.php?cmd=do_upgrade\">here</a></b>";

}

elseif($cmd == "do_upgrade") {

echo "Upgrading to <b>XMB 1.8 Partagium</b><br />";

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
// Insert Templates.xmb Into Newly Created Templates Table

//
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

// Change the settings table
echo "Altering the Settings table to reflect the right settings.<br />";
	$db->query("ALTER TABLE ".$tablepre."settings ADD tickerstatus char(3) NOT NULL default ''");
	$db->query("ALTER TABLE ".$tablepre."settings ADD tickercontents text");
	$db->query("ALTER TABLE ".$tablepre."settings ADD tickerdelay char(10)");
	$db->query("ALTER TABLE ".$tablepre."members ADD pwdate BIGINT(30)");
	$db->query("UPDATE ".$tablepre."members SET status='Super Administrator' WHERE status = 'Administrator'");
	$db->query("UPDATE ".$tablepre."settings SET tickerstatus = 'off', tickercontents = '', tickerdelay = '4000'");
	$db->query("INSERT INTO ".$tablepre."ranks VALUES ('Super Administrator', '0', '9', '', 'yes', '');");
	$db->query("INSERT INTO ".$tablepre."themes VALUES ('Windows XP Silver', '#FFFFFF', '#EDF0F7', '#FFFFFF', '#000000', NULL, '#C4C8D4', '#FFFFFF', '#000000', '#FFFFFF', 'silverbar.gif', '#000000', '#000000', '1', '90%', '4', 'Verdana', '10px', 'xplogo.gif', 'images/xpsilver', 'images/smilies', '#000000');");
        $db->query("INSERT INTO ".$tablepre."themes VALUES ('Windows XP Blue', '#FFFFFF', '#ADD1FF', '#FFFFFF', '#0055E5', NULL, '#0055E5', '#0055E5', '#FFFFFF', '#FFFFFF', 'bluebar.gif', '#000000', '#000000', '1', '90%', '4', 'Verdana', '10px', 'xplogo.gif', 'images/xpblue', 'images/smilies', '#FFFFFF');");
        $db->query("ALTER TABLE `".$tablepre."members` ADD INDEX ( `email` )");
	$db->query("ALTER TABLE `".$tablepre."members` ADD INDEX ( `password` )");
	$db->query("ALTER TABLE `".$tablepre."posts` ADD INDEX ( `author` )");
	$db->query("OPTIMIZE TABLE `".$tablepre."attachments` , `".$tablepre."banned` , `".$tablepre."buddys` , `".$tablepre."favorites` , `".$tablepre."forums` , `".$tablepre."members` , `".$tablepre."posts` , `".$tablepre."ranks` , `".$tablepre."restricted` , `".$tablepre."settings` , `".$tablepre."smilies` , `".$tablepre."templates` , `".$tablepre."themes` , `".$tablepre."threads` , `".$tablepre."u2u` , `".$tablepre."whosonline` , `".$tablepre."words`");


echo "<b>Upgrade Complete</b>To skip the exit screen click <a href=\"index.php\">here</a><br />.";
echo "<b>Please wait, Transferring..</b><meta http-equiv=\"Refresh\" content=\"1; url=index.php\">";


}

?>
</body>
</html>