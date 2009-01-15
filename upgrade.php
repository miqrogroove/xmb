<?
require "./config.php";
require "./db/$database.php";

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname);

$tables = array('banned', 'favorites', 'forums', 'members', 'posts', 'ranks', 'settings', 'smilies', 'templates', 'themes', 'threads', 'u2u', 'whosonline', 'words');
foreach($tables as $name) {
${'table_'.$name} = "xmb_".$name;
}


if($action == "1_50") {
	if($convpasses != "yes") {
	echo "Modifying posts..";
	$db->query("ALTER TABLE $table_posts ADD subject varchar(100) NOT NULL AFTER message");

	$queryt = $db->query("SELECT * FROM $table_threads");
	while($thread = $db->fetch_array($queryt)) {
		$thread[message] = addslashes($thread[message]);
		$thread[subject] = addslashes($thread[subject]);
		$db->query("INSERT INTO $table_posts VALUES ('$thread[fid]', '$thread[tid]', '', '$thread[author]', '$thread[message]', '$thread[subject]', '$thread[dateline]', '$thread[icon]', '$thread[usesig]', '$thread[useip]', '$thread[bbcodeoff]', '$thread[smileyoff]', '')");
	}

	echo "Done<br>";
	echo "Adding settings..";
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
	smcols varchar(15) NOT NULL
	);");


	$bbrulestxt = stripslashes($bbrulestxt);
	$bboffreason = stripslashes($bboffreason);

	$db->query("INSERT INTO $table_settings VALUES('$langfile', '$bbname', '$postperpage', '$topicperpage', '$hottopic', '$theme', '$bbstatus', '$whosonlinestatus', '$regstatus', '$bboffreason', '$regviewonly', '$floodctrl', '$memberperpage', '$catsonly', '$hideprivate', '$emailcheck', '$bbrules', '$bbrulestxt', '$searchstatus', '$faqstatus', '$memliststatus', '$sitename', '$siteurl', '$avastatus', '$u2uquota', '$gzipcompress', '$boardurl', '$coppa', '$timeformat', '$adminemail', '$dateformat', '$sigbbcode', '$sightml', '$reportpost', 'on', 'on', 'on', '16', '4')");
	echo "Done<br>";

	echo "Inserting Templates..";
	$db->query("DROP TABLE IF EXISTS $table_templates");
	$db->query("CREATE TABLE $table_templates (
	id smallint(6) NOT NULL auto_increment,
	name varchar(40) NOT NULL,
	template text NOT NULL,
	PRIMARY KEY(id)
	);");
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
	echo "Done!";
	}
	if($convpasses == "yes") {
		echo "Converting Members..";
		$query = $db->query("SELECT * FROM $table_members");
		while($row = $db->fetch_array($query)) {
			$newpassword = md5($row[password]);
			$username = $row[username];
			$db->query("UPDATE $table_members SET password = '$newpassword' WHERE username = '$username'");
		}
		echo "Done<br>";
	}

}
?>