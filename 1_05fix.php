<?
require "config.php";

mysql_connect($dbhost, $dbuser, $dbpw) or die(mysql_error());
mysql_select_db($dbname) or die(mysql_error());

$tables = array('members', 'posts', 'threads', 'u2u', 'whosonline', 'banned');
foreach($tables as $name) {
${'table_'.$name} = $tablepre.$name;
}
echo "Converting varchar to int that holds just the date...";
mysql_query("ALTER TABLE $table_banned CHANGE dateline dateline BIGINT(30) NOT NULL") or die(mysql_error());
mysql_query("ALTER TABLE $table_members CHANGE regdate regdate BIGINT(30) NOT NULL") or die(mysql_error());
mysql_query("ALTER TABLE $table_members CHANGE lastvisit lastvisit BIGINT(30) NOT NULL") or die(mysql_error());
mysql_query("ALTER TABLE $table_posts CHANGE dateline dateline BIGINT(30) NOT NULL") or die(mysql_error());
mysql_query("ALTER TABLE $table_threads CHANGE dateline dateline BIGINT(30) NOT NULL") or die(mysql_error());
mysql_query("ALTER TABLE $table_u2u CHANGE dateline dateline BIGINT(30) NOT NULL") or die(mysql_error());
mysql_query("ALTER TABLE $table_whosonline CHANGE time time BIGINT(30) NOT NULL") or die(mysql_error());
echo "<b>Done</b><br><br>";

echo "Upgrade Complete....... Now please follow the rest of the directions.";
?>
