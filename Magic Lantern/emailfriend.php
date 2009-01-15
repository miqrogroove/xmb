<?
/*

XMB 1.6 v2c Magic Lantern
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/
require "./header.php";
require "./xmb.php";
$query = mysql_query("SELECT * FROM $table_threads WHERE tid='$tid'") or die(mysql_error());
$thread = mysql_fetch_array($query);
$thread[subject] = stripslashes($thread[subject]);
$fid = $thread[fid];

$query = mysql_query("SELECT name, type, fup, fid FROM $table_forums WHERE fid='$fid'") or die(mysql_error());
$forum = mysql_fetch_array($query);

if($catsonly == "on" && $forum[type] == "sub") {
	$query = mysql_query("SELECT fup FROM $table_forums WHERE fid='$forum[fup]'") or die(mysql_error());
	$forum1 = mysql_fetch_array($query);
	$query = mysql_query("SELECT fid, name FROM $table_forums WHERE fid='$forum1[fup]'") or die(mysql_error());
	$cat = mysql_fetch_array($query);
} elseif($catsonly == "on" && $forum[type] == "forum") {
	$query = mysql_query("SELECT fid, name FROM $table_forums WHERE fid='$forum[fup]'") or die(mysql_error());
	$cat = mysql_fetch_array($query);
}

if($catsonly == "on") {
	$navigation = "<a href=\"index.php\">$lang[textindex]</a> &raquo; <a href=\"index.php?gid=$cat[fid]\">$cat[name]</a> &raquo; ";
} else {
	$navigation = "<a href=\"index.php\">$lang[textindex]</a> &raquo; ";
}

if($forum[type] == "forum") {
	$navigation .= "<a href=\"forumdisplay.php?fid=$fid\"> $forum[name]</a> &raquo; <a href=\"viewthread.php?tid=$tid\">$thread[subject]</a> &raquo; $lang_textsendtofriend";
} else {
	$query = mysql_query("SELECT name, fid FROM $table_forums WHERE fid='$forum[fup]'") or die(mysql_error());
	$fup = mysql_fetch_array($query);
	$navigation .= "<a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> &raquo; <a href=\"forumdisplay.php?fid=$fid\"> $forum[name]</a> &raquo; <a href=\"viewthread.php?tid=$tid\">$thread[subject]</a> &raquo; $lang_textsendtofriend";
}
eval("\$header = \"".template("header")."\";");
echo $header;


if(!$sendsubmit) {
	$threadurl = $boardurl;
	$threadurl .= "viewthread.php?tid=$tid";

	if(!$xmbuser) {
		$query = $db->query("SELECT * FROM $table_members WHERE username='$xmbuser'");
		$username = $db->fetch_array($query);
		$email = $username[email];
	}
	eval("\$form = \"".template("emailfriend")."\";");
	echo $form;
}
if ($sendsubmit){
	if($message == "") {
		$message = "$lang_frienddefmsg:\n\n$threadurl";
	}
	if($subject == "") {
		$subject = $thread[subject];
	}
	if(!$fromname) {
		echo "<center><span class=\"12px \">$lang[friendnonamemsg]</span></center>";
		exit;
	}
	if(!$fromemail) {
		echo "<center><span class=\"12px \">$lang[friendnoemailmsg]</span></center>";
		exit;
	}
	if(!$sendtoname) {
		echo "<center><span class=\"12px \">$lang[friendnotonamemsg]</span></center>";
		exit;
	}
	if(!$sendtoemail) {
		echo "<center><span class=\"12px \">$lang[friendnotoemailmsg]</span></center>";
		exit;
	}
	mail("$sendtoemail", "$subject", "$message", "From:$fromname <$fromemail>");
	echo "<center><span class=\"12px \">$lang_friendsentmsg</span></center>";
	?>
	<script>
	function redirect()
	{
	window.location.replace("viewthread.php?tid=<?=$tid?>");
	}
	setTimeout("redirect();", 1250);
	</script>
	<?
}

$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>