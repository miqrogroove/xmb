<?php
require "./header.php";
if(!$_GET['do'] || $_GET['do'] != 'update'){
	?>
	XMB will now update your current 1.8 database. If you are not running version 1.8 yet, PLEASE USE A DIFFERENT upgrade!!! This one has a high chance of failing it's purpose in that case....<br><br><a href="./upgrade.php?do=update">Press here to continue upgrading</a>
	<?php
}else{

// These queries are used to speed up your database
	$db->query("ALTER TABLE `$table_members` ADD INDEX ( `email` )");
	$db->query("ALTER TABLE `$table_members` ADD INDEX ( `password` )");
	$db->query("ALTER TABLE `$table_posts` ADD INDEX ( `author` )");
	$db->query("ALTER TABLE `$table_templates` ADD INDEX ( `name` )");
	$db->query("OPTIMIZE TABLE `$table_attachments` , `$table_banned` , `$table_buddys` , `$table_favorites` , `$table_forums` , `$table_members` , `$table_posts` , `$table_ranks` , `$table_restricted` , `$table_settings` , `$table_smilies` , `$table_templates` , `$table_themes` , `$table_threads` , `$table_u2u` , `$table_whosonline` , `$table_words`");
	
	echo "Xmb has successfully updated your database.<br> Please delete this file from your forum root directory before continuing to the index.php page";
}
?>