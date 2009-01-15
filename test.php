<?
require "header.php";

$query = $db->query("SELECT * FROM $table_forums WHERE fid='7'");
foreach($db->fetch_array($query) as $key => $val) {
echo "<b>$key:</b>$val<br>";
}
?>