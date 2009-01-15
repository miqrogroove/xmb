<?
/*

XMB 1.6 v2c Magic Lantern
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/
require "./config.php";
require "./db/$database.php";
require "./functions.php";
require "./xmb.php";
$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname);

$tables = array('banned', 'favorites', 'forums', 'members', 'posts', 'ranks', 'settings', 'smilies', 'templates', 'themes', 'threads', 'u2u', 'whosonline', 'words');
foreach($tables as $name) {
${'table_'.$name} = $tablepre.$name;
}


if($action == "fixftotals") {
$queryf = $db->query("SELECT * FROM $table_forums WHERE type!='group'");
while($forum = $db->fetch_array($queryf)) {

$query = $db->query("SELECT fid FROM $table_forums WHERE fup='$forum[fid]'");
$sub = $db->fetch_array($query);

$query = $db->query("SELECT COUNT(*) FROM $table_threads WHERE fid='$forum[fid]' OR fid='$sub[fid]'");
$threadnum = $db->result($query, 0);

$query = $db->query("SELECT COUNT(*) FROM $table_posts WHERE fid='$forum[fid]' OR fid='$sub[fid]'");
$postnum = $db->result($query, 0);

$db->query("UPDATE $table_forums SET threads='$threadnum', posts='$postnum' WHERE fid='$forum[fid]'");
}
echo "<b>Update successful!</b>";
}


if($action == "fixttotals") {
$queryt = $db->query("SELECT * FROM $table_threads");
while($threads = $db->fetch_array($queryt)) {

$query = $db->query("SELECT COUNT(*) FROM $table_posts WHERE tid='$threads[tid]'");
$replynum = $db->result($query, 0);

$replynum--;
$db->query("UPDATE $table_threads SET replies='$replynum' WHERE tid='$threads[tid]'");
}
echo "<b>Update successful!</b>";
}


if($action == "fixmposts") {
$queryt = $db->query("SELECT * FROM $table_members");
while($mem = $db->fetch_array($queryt)) {

$query = $db->query("SELECT COUNT(*) FROM $table_posts WHERE author='$mem[username]'");
$postsnum = $db->result($query, 0);

$postsnum += $postsnum2;

$db->query("UPDATE $table_members SET postnum='$postsnum' WHERE username='$mem[username]'");
}
echo "<b>Update successful!</b>";
}
?>