<?
require "header.php";


$query = $db->query("SELECT fid FROM $table_forums");
while($forum = $db->fetch_array($query)) {
updateforumcount($forum[fid]);
}
echo "done";
?>