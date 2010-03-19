<?

if ((file_exists("./install.php")) || (file_exists("./newinstall.php")))
{
die("Error: The installation file install.php or newinstall.php (XMB installer scripts) still exists on the server! This is a security risk! Please delete the file to continue using XMB!");
}

require "header.php";

if(!$gid) {
$navigation = "$lang[textindex]";
}
else {
$query = mysql_query("SELECT name FROM $table_forums WHERE fid='$gid' AND type='group'") or die(mysql_error());
$cat = mysql_fetch_array($query);
$navigation ="<a href=\"index.php\">$lang[textindex]</a> &gt; $cat[name]";
}

$html = template("header.html");
eval("echo stripslashes(\"$html\");");
?>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center"> 
<tr><td bgcolor="<?=$bordercolor?>"> 

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%"> 
<tr>
<?
if($postscol == "1col") {
?>
<td width="4%" class="header">&nbsp;</td> 
<td width="60%" class="header"><?=$lang[textforum]?></td> 
<td width="10%" class="header"><?=$lang[textcontents]?></td> 
<td width="19%" class="header"><?=$lang[textlastpost]?></td> 
</tr>
<?
} else {
?>
<td width="4%" class="header">&nbsp;</td> 
<td width="58%" class="header"><?=$lang[textforum]?></td> 
<td width="6%" class="header"><?=$lang[texttopics]?></td>
<td width="6%" class="header"><?=$lang[textposts]?></td> 
<td width="19%" class="header"><?=$lang[textlastpost]?></td> 
</tr>
<?
}

if(!$gid) {

$query = mysql_query("SELECT * FROM $table_forums WHERE type='forum' AND status='on' AND fup='' ORDER BY displayorder") or die(mysql_error());
while($forum = mysql_fetch_array($query)) {

if($announcestatus == "on" && !$gid) {
?> <tr><td colspan="6" class="category"><a href="#"><b><?=$lang[texttoplevel]?></b></a></td></tr> <?
}

forum($forum[lastpost], $timeoffset, $forum[moderator], $lastvisit2, $hideprivate, $status, $forum[private], $forum[posts], $forum[threads], $altbg1, $altbg2, $forum[name], $forum[fid], $forum[description], $timecode, $dateformat, $langfile, $thisuser, $forum[userlist]);
}

$queryg = mysql_query("SELECT * FROM $table_forums WHERE type='group' AND status='on' ORDER BY displayorder") or die(mysql_error());
} else {
$queryg = mysql_query("SELECT * FROM $table_forums WHERE type='group' AND fid='$gid' AND status='on' ORDER BY displayorder") or die(mysql_error());
}

while($group = mysql_fetch_array($queryg)) {
?>

<tr> 
<td colspan="6" class="category"><a href="index.php?gid=<?=$group[fid]?>"><b><?=$group[name]?></b></a></td> 
</tr> 

<?

if($catsonly != "on" || $gid) {
$query = mysql_query("SELECT * FROM $table_forums WHERE type='forum' AND status='on' AND fup='$group[fid]' ORDER BY displayorder") or die(mysql_error());
while($forum = mysql_fetch_array($query)) {
forum($forum[lastpost], $timeoffset, $forum[moderator], $lastvisit2, $hideprivate, $status, $forum[private], $forum[posts], $forum[threads], $altbg1, $altbg2, $forum[name], $forum[fid], $forum[description], $timecode, $dateformat, $langfile, $thisuser, $forum[userlist]);
}
}
}

$query = mysql_query("SELECT username FROM $table_members ORDER BY regdate DESC") or die(mysql_error());
$lastmem = mysql_fetch_array($query);
$lastmember = $lastmem[username];
$members = mysql_num_rows($query);

$query = mysql_query("SELECT COUNT(*) FROM $table_threads") or die(mysql_error());
$threads = mysql_result($query, 0);

$query = mysql_query("SELECT COUNT(*) FROM $table_posts") or die(mysql_error());
$posts = mysql_result($query, 0);

$posts = $threads + $posts;
$memhtml = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastmember)."\"><b>$lastmember</b></a>.";
eval($lang[evalindexstats]);

if($members == "0") {
$memhtml = "<b>$lang[textnoone]</b>";
}

if($indexstats == "on") {
$stats = "$lang[indexstats]<br /> $lang[stats4] $memhtml";
}

$stats .= "<br /><br /><img src=\"images/red_folder.gif\"> = $lang[newposts]<br /><img src=\"images/folder.gif\"> = $lang[nonewposts]";

if(!$gid) {
if($whosonlinestatus == "on") { 
$time = time(); 
$newtime = $time - 450;

mysql_query("DELETE FROM $table_whosonline WHERE time<'$newtime' AND username != 'onlinerecord'"); 
$query = mysql_query("SELECT COUNT(*) FROM $table_whosonline WHERE username='xguest123'") or die(mysql_error()); 
$guestcount = mysql_result($query, 0);

$query = mysql_query("SELECT COUNT(*) FROM $table_whosonline WHERE username != 'xguest123' AND username != 'onlinerecord'") or die(mysql_error()); 
$membercount = mysql_result($query, 0);

$query = mysql_query("SELECT * FROM $table_whosonline WHERE username = 'onlinerecord'") or die(mysql_error()); 
$record = mysql_fetch_array($query);

eval($lang[evalwhosonline]);
$memonmsg = "<span class=\"11px\">$lang[whosonline1] $guestcount $lang[whosonline2] $membercount $lang[whosonline3] $lang[whosonline4]</span>"; 

$queryonline = mysql_query("SELECT * FROM $table_whosonline WHERE username!='xguest123' AND username!='onlinerecord'") or die(mysql_error()); 

$memtally = ""; 
$num = 1; 
while ($online = mysql_fetch_array($queryonline)) { 
if($num < $membercount) { 
$memtally .= "<a href=\"member.php?action=viewpro&member=".rawurlencode($online[username])."\">$online[username]</a>, "; 
} else { 
$memtally .= "<a href=\"member.php?action=viewpro&member=".rawurlencode($online[username])."\">$online[username]</a>"; 
} 
$num++; 
}

if($memtally == "") {
$memtally = "&nbsp;";
}
?>

<tr>
<td colspan="6" class="category"><b><font color="<?=$link?>"><a href="misc.php?action=online"><?=$lang[whosonline]?></a> - <?=$memonmsg?></font></b></td>
</tr>
<tr>
<td bgcolor="<?=$altbg1?>" align="center" class="tablerow"><img src="images/online.gif"></td>
<td bgcolor="<?=$altbg2?>" colspan="5" class="12px"><?=$memtally?></td>
</tr>

<?
} 
}

$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
if($showtotaltime != "off") { 
$totaltime = ($endtime - $starttime); 
$totaltime = number_format($totaltime, 7); 
}

$html = template("footer.html");
eval("echo stripslashes(\"$html\");");
?>
