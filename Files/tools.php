<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

require "./header.php";
loadtemplates('header,footer');

$navigation .= "&raquo; <a href=\"cp.php\">Administration Panel</a>";
eval("\$header = \"".template("header")."\";");
echo $header;

if(!$xmbuser || !$xmbpw) {
	$xmbuser = "";
	$xmbpw = "";
	$status = "";
}

if($status != "Administrator" && $status !="Super Administrator") {
	eval("\$notadmin = \"".template("error_nologinsession")."\";");
	echo $notadmin;
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
}

$cploc = $HTTP_SERVER_VARS["REQUEST_URI"]; 

if(getenv(HTTP_CLIENT_IP)) { 
	$ip = getenv(HTTP_CLIENT_IP); 
} elseif(getenv(HTTP_X_FORWARDED_FOR)) { 
	$ip = getenv(HTTP_X_FORWARDED_FOR); 
} else { 
	$ip = getenv(REMOTE_ADDR); 
} 

$time = time();
$string = "$xmbuser|#||#|$ip|#||#|$time|#||#|$_SERVER[REQUEST_URI]\n";

@chmod('./cplogfile.php', 0777);
$filehandle = @fopen('./cplogfile.php','a');
if(!$filehandle){
	echo('<center><b>Wrong File permissions set. Please CHMOD the <i>cplogfile.php</i> to <i>777</i></b></center>');
}
@flock($filehandle, 2);
@fwrite($filehandle, $string);
@fclose($filehandle);
@chmod('./cplogfile.php', 0766);

?>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr class="header">
<td colspan="2"><?=$lang_textcp?></td>
</tr>

<tr bgcolor="<?=$altbg1?>" class="tablerow">
<td align="center">
<a href="cp.php?action=settings"><?=$lang_textsettings?></a> - <a href="cp.php?action=forum"><?=$lang_textforums?></a> -
<a href="cp.php?action=mods"><?=$lang_textmods?></a> - <a href="cp.php?action=members"><?=$lang_textmembers?></a> -
<a href="cp2.php?action=restrictions"><?=$lang_cprestricted?></a> - <a href="cp.php?action=ipban"><?=$lang_textipban?></a> -
<a href="cp.php?action=upgrade"><?=$lang_textupgrade?></a> - <a href="cp.php?action=search"><?=$lang_cpsearch?></a><br />
<a href="cp2.php?action=themes"><?=$lang_themes?></a> - <a href="cp2.php?action=smilies"><?=$lang_smilies?></a> -
<a href="cp2.php?action=censor"><?=$lang_textcensors?></a> - <a href="cp2.php?action=ranks"><?=$lang_textuserranks?></a> -
<a href="cp2.php?action=newsletter"><?=$lang_textnewsletter?></a> - <a href="cp2.php?action=prune"><?=$lang_textprune?></a> -
<a href="cp2.php?action=templates"><?=$lang_templates?></a> - <a href="cp2.php?action=attachments"><?=$lang_textattachman?></a><br />
<a href="cp2.php?action=cplog"><?=$lang_cplog?></a>
<br /><tr bgcolor="<?=$altbg2?>" class="tablerow"><td align="center"><a href="tools.php?action=fixttotals"><?=$lang_textfixthread?></a> - <a href="tools.php?action=fixftotals"><?=$lang_textfixposts?></a> - <a href="tools.php?action=fixmposts"><?=$lang_textfixmemposts?></a> - <a href="tools.php?action=updatemoods"><?=$lang_textfixmoods?></a> - <a href="tools.php?action=u2udump"><?=$lang_u2udump?></a> - <a href="tools.php?action=whosonlinedump"><?=$lang_cpwodump?></a>
<br /><a href="tools.php?action=fixforumthemes"><?=$lang_fixforumthemes?></a>
</td>
</tr>

<?
if(!$action) {
}

if($action == "fixftotals") {
	$fquery = $db->query("SELECT fid FROM $table_forums WHERE type='forum'");
	while($forum = $db->fetch_array($fquery)) {
		$threadnum	= 0;
		$postnum	= 0;
		$sub_threadnum	= 0;
		$sub_postnum	= 0;
		$squery		= '';
		$stquery	= '';
		$spquery	= '';
		$ftquery	= '';
		$fpquery	= '';
		
		// Get all posts and threads from the subforums
		$squery = $db->query("SELECT fid FROM $table_forums WHERE fup='$forum[fid]' AND type='sub'");
		
		while($sub = $db->fetch_array($squery)){
			$stquery = $db->query("SELECT COUNT(*) FROM $table_threads WHERE fid='$sub[fid]'");
			$sub_threadnum = $db->result($stquery, 0);
			
			$spquery = $db->query("SELECT COUNT(*) FROM $table_posts WHERE fid='$sub[fid]'");
			$sub_postnum = $db->result($spquery, 0);
			
			$db->query("UPDATE $table_forums SET threads='$sub_threadnum', posts='$sub_postnum' WHERE fid='$sub[fid]'");
			$threadnum += $sub_threadnum;
			$postnum += $sub_postnum;
		}
		
		// Get all threads and posts for the forum itself
		$ftquery = $db->query("SELECT COUNT(*) FROM $table_threads WHERE fid='$forum[fid]'");
		$threadnum += $db->result($ftquery, 0);
		
		$fpquery = $db->query("SELECT COUNT(*) FROM $table_posts WHERE fid='$forum[fid]'");
		$postnum += $db->result($fpquery, 0);
		
		// Update it all
		$db->query("UPDATE $table_forums SET threads='$threadnum', posts='$postnum' WHERE fid='$forum[fid]'");
	}
	
	$navigation .= " &raquo; Tools";
	echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">Tool Request Completed! Fixed Forum Totals</td></tr></table></table>";
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
}


if($action == "fixttotals") {
	$queryt = $db->query("SELECT * FROM $table_threads");
	while($threads = $db->fetch_array($queryt)) {
		$query = $db->query("SELECT COUNT(*) FROM $table_posts WHERE tid='$threads[tid]'");
		$replynum = $db->result($query, 0) -1;
		$db->query("UPDATE $table_threads SET replies='$replynum' WHERE tid='$threads[tid]'");
	}

	$navigation .= " &raquo; Tools";
	echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">Tool Request Completed! Fixed Thread Totals</td></tr></table></table>";
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
}


if($action == "fixmposts") {
	$queryt = $db->query("SELECT username FROM $table_members");
	while($mem = $db->fetch_array($queryt)) {
		$mem[username] = stripslashes($mem[username]);
		$mem[username] = addslashes($mem[username]);

		$query = $db->query("SELECT COUNT(pid) FROM $table_posts WHERE author='$mem[username]'");
		$postsnum = $db->result($query, 0);
		$db->query("UPDATE $table_members SET postnum='$postsnum' WHERE username='$mem[username]'");
	}
	
	$navigation .= " &raquo; Tools";
	echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">Tool Request Completed! - Fixed Member Post-numbers</td></tr></table></table>";
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
}

if($action == "updatemoods") {
	$db->query("UPDATE $table_members SET mood='No Mood.' WHERE mood=''");
	$navigation .= " &raquo; Tools";
	echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">Tool Request Completed! Moods Updated</td></tr></table></table>";
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
}


if($action == "u2udump") {
	$db->query("DELETE FROM $table_u2u");
	$navigation .= " &raquo; Tools";
	echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">Tool Request Completed! U2Us Cleared</td></tr></table></table>";
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
}

if($action == "whosonlinedump") {
	$db->query("DELETE FROM $table_whosonline");
	$navigation .= " &raquo; Tools";
	echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">Tool Request Completed! Whos Online Cleared</td></tr></table></table>";
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
}

if($action == "fixforumthemes") {
	$db->query("UPDATE $table_forums SET theme='' WHERE theme='name'");
	$navigation .= " &raquo; Tools";
	echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">Tool Request Completed! Themes Fixed</td></tr></table></table>";
	end_time();
	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
}

echo "</td></tr></table></table>";
end_time();
eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>