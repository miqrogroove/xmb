<?
require "config.php";

mysql_connect($dbhost, $dbuser, $dbpw) or die(mysql_error());
mysql_select_db($dbname) or die(mysql_error());

$tables = array('announce','banned','forums', 'members', 'posts', 'ranks', 'smilies', 'themes', 'threads', 'u2u', 'whosonline', 'words');
foreach($tables as $name) {
${'table_'.$name} = $tablepre.$name;
}



if($action == "fixftotals") {
$queryf = mysql_query("SELECT * FROM $table_forums WHERE type!='group'") or die(mysql_error());
while($forum = mysql_fetch_array($queryf)) {

$query = mysql_query("SELECT fid FROM $table_forums WHERE fup='$forum[fid]'") or die(mysql_error());
$sub = mysql_fetch_array($query);

$query = mysql_query("SELECT COUNT(*) FROM $table_threads WHERE fid='$forum[fid]' OR fid='$sub[fid]'") or die(mysql_error());
$threadnum = mysql_result($query, 0);

$query = mysql_query("SELECT COUNT(*) FROM $table_posts WHERE fid='$forum[fid]' OR fid='$sub[fid]'") or die(mysql_error());
$replynum = mysql_result($query, 0);

$postnum = $threadnum + $replynum;
mysql_query("UPDATE $table_forums SET threads='$threadnum', posts='$postnum' WHERE fid='$forum[fid]'") or die(mysql_error());
}
}


if($action == "fixttotals") {
$queryt = mysql_query("SELECT * FROM $table_threads") or die(mysql_error());
while($threads = mysql_fetch_array($queryt)) {

$query = mysql_query("SELECT COUNT(*) FROM $table_posts WHERE tid='$threads[tid]'") or die(mysql_error());
$replynum = mysql_result($query, 0);

mysql_query("UPDATE $table_threads SET replies='$replynum' WHERE tid='$threads[tid]'") or die(mysql_error());
}
}


if($action == "fixmposts") {
$queryt = mysql_query("SELECT * FROM $table_members") or die(mysql_error());
while($mem = mysql_fetch_array($queryt)) {

$query = mysql_query("SELECT COUNT(*) FROM $table_posts WHERE author='$mem[username]'") or die(mysql_error());
$postsnum = mysql_result($query, 0);

$query = mysql_query("SELECT COUNT(*) FROM $table_threads WHERE author='$mem[username]'") or die(mysql_error());
$postsnum2 = mysql_result($query, 0);

$postsnum += $postsnum2;

mysql_query("UPDATE $table_members SET postnum='$postsnum' WHERE username='$mem[username]'") or die(mysql_error());
}
}

echo "Update successful!";
?>