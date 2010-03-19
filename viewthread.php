<?
require "header.php";

$query = mysql_query("SELECT * FROM $table_threads WHERE tid='$tid'") or die(mysql_error());
$thread = mysql_fetch_array($query);
$fid = $thread[fid];

if($thread[tid] != $tid) { 
$notexist = $lang[textnothread]; 
}

$closed = $thread[closed];
$topped = $thread[topped];

$query = mysql_query("SELECT name, private, userlist, type, fup, fid FROM $table_forums WHERE fid='$fid'") or die(mysql_error());
$forum = mysql_fetch_array($query);

if($forum[type] != "forum" && $forum[type] != "sub" && $forum[fid] != $fid) { 
$notexist = $lang[textnoforum]; 
}

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
$navigation = "<a href=\"index.php\">$lang[textindex]</a> &gt; <a href=\"index.php?gid=$cat[fid]\">$cat[name]</a> &gt; ";
} else {
$navigation = "<a href=\"index.php\">$lang[textindex]</a> &gt; ";
}

if($forum[type] == "forum") {
$navigation .= "<a href=\"forumdisplay.php?fid=$fid\"> $forum[name]</a> &gt; $thread[subject]";
} else {
$query = mysql_query("SELECT name, fid FROM $table_forums WHERE fid='$forum[fup]'") or die(mysql_error());
$fup = mysql_fetch_array($query);
$navigation .= "<a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> &gt; <a href=\"forumdisplay.php?fid=$fid\"> $forum[name]</a> &gt; $thread[subject]";
}


if(!$action) {
$html = template("header.html");
eval("echo stripslashes(\"$html\");");

if($closed == "yes") {
$replylink = "";
$closeopen = "<a href=\"topicadmin.php?action=close&fid=$fid&tid=$tid\">$lang[textopenthread]</a>";
$repquote = "";
}
else {
$closeopen = "<a href=\"topicadmin.php?action=close&fid=$fid&tid=$tid\">$lang[textclosethread]</a>";
$repquote = "<a href=\"post.php?action=reply&fid=$fid&tid=$tid&repquote=t|$tid\"><img src=\"images/quote.gif\" border=\"0\" alt=\"Reply With Quote\" /></a> ";

if($replyimg != "") {
$replylink = " &nbsp;<a href=\"post.php?action=reply&fid=$fid&tid=$tid\"><img src=\"$replyimg\"  border=\"0\"></a>";
} else {
$replylink = " &nbsp;<a href=\"post.php?action=reply&fid=$fid&tid=$tid\">$lang[textpostreply]</a>";
}
}

if($topped == "1") {
$topuntop = "<a href=\"topicadmin.php?action=top&fid=$fid&tid=$tid\">$lang[textuntopthread]</a>";
}
else {
$topuntop = "<a href=\"topicadmin.php?action=top&fid=$fid&tid=$tid\">$lang[texttopthread]</a>";
}

if($newtopicimg != "") {
$newtopiclink = "<a href=\"post.php?action=newtopic&fid=$fid\"><img src=\"$newtopicimg\" border=\"0\"></a>";
} else {
$newtopiclink = "<a href=\"post.php?action=newtopic&fid=$fid\">$lang[textnewtopic]</a>";
}



$querynext = mysql_query("SELECT * FROM $table_threads WHERE lastpost > '$thread[lastpost]' AND fid='$fid' ORDER BY lastpost") or die(mysql_error()); 
$gotothread = mysql_fetch_array($querynext); 
if ($gotothread[tid] != "") { 
$next = " <a href=\"viewthread.php?fid=$fid&tid=$gotothread[tid]\">$lang[nextthread] &gt;</a>"; 
} 

$querylast = mysql_query("SELECT * FROM $table_threads WHERE lastpost < '$thread[lastpost]' AND fid='$fid' ORDER BY lastpost DESC") or die(mysql_error()); 
$goto2 = mysql_fetch_array($querylast); 

if ($goto2[tid] != "") { 
$prev = "<a href=\"viewthread.php?fid=$fid&tid=$goto2[tid]\">&lt; $lang[lastthread]</a> &nbsp;"; 
} 


if(!$ppp || $ppp == '') { 
$ppp = $postperpage; 
}

if($page) {
$start_limit = ($page-1) * $ppp;
} else {
$start_limit = 0;
$page = 1;
}

if($page != 1) {
$start_limit -= 1;
}

mysql_query("UPDATE $table_threads SET views=views+1 WHERE tid='$tid'") or die(mysql_error()); 
$query = mysql_query("SELECT count(pid) FROM $table_posts WHERE fid='$fid' AND tid='$tid'") or die(mysql_error()); 
$num = mysql_result($query,0); 

if ($num >= $ppp) {
$num++;
$pages = $num / $ppp;
$pages = ceil($pages);
	
if ($page == $pages) {
$to = $pages;
} elseif ($page == $pages-1) {
$to = $page+1;
} elseif ($page == $pages-2) {
$to = $page+2;
} else {
$to = $page+3;
}

if ($page == 1 || $page == 2 || $page == 3) {
$from = 1;
} else {
$from = $page-3;
}
$fwd_back .= "<a href=\"viewthread.php?tid=$tid&page=1\"><<</a>";

for ($i = $from; $i <= $to; $i++) {
if($i != $page) {
$fwd_back .= "&nbsp;&nbsp;<a href=\"viewthread.php?fid=$fid&tid=$tid&page=$i\">$i</a>&nbsp;&nbsp;";
} else {
$fwd_back .= "&nbsp;&nbsp;<u><b>$i</b></u>&nbsp;&nbsp;";
}
}

$fwd_back .= "<a href=\"viewthread.php?tid=$tid&page=$pages\">>></a>";
$multipage = "$fwd_back";
}
?>
<table width="<?=$tablewidth?>" cellspacing="0" cellpadding="0" align="center">
<tr>
<td class="multi"><span style="font-family:arial; font-size: 11px;"><?=$prev?><?=$next?><br /><a href="viewthread.php?fid=<?=$fid?>&tid=<?=$tid?>&action=printable"><?=$lang[textprintver]?></a></span><br />
<?=$multipage?></td> 
<td class="post" align="right" valign="bottom">
<?=$newtopiclink?><?=$replylink?>
</td></tr></table>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">

<?
if($forums[private] == "staff" && $status != "Administrator" && $status != "Moderator") {
echo "<tr class=\"tablerow\"><td>$lang[privforummsg]</td></tr>";
exit;
}

if($forum[userlist] != "") {
if($thisuser == "") {
$thisuser = "blalaguestman123frzq";
}

if(!eregi($thisuser."(,|$)", $forum[userlist])) {
echo "<tr class=\"tablerow\"><td bgcolor=\"$altbg1\" colspan=\"8\">$lang[privforummsg]</td></tr>"; 
exit;
}

if($thisuser == "blalaguestman123fr") {
$thisuser = "";
}
}
?>
<tr><td bgcolor="<?=$bordercolor?>">

<?
if($page == 1) {
$date = gmdate("$dateformat",$thread[dateline] + ($timeoffset * 3600));
$time = gmdate("$timecode",$thread[dateline] + ($timeoffset * 3600));
$poston = "$lang[textposton] $date $lang[textat] $time";

if($thread[icon] != "") {
$thread[icon] = "<img src=\"images/$thread[icon]\" />";
}

$query = mysql_query("SELECT * FROM $table_members WHERE username='$thread[author]'") or die(mysql_error());
$member = mysql_fetch_array($query);

$info[$thread[author]] = $member;

if($member[email] != "" && $member[showemail] == "yes") {
$email = "<a href=\"mailto:$member[email]\"><img src=\"images/email.gif\" border=\"0\" alt=\"E-Mail User\" /></a> ";
} else {
$email = "";
}

$member[site] = str_replace("http://", "", $member[site]);
$member[site] = "http://$member[site]";

if($member[site] == "http://") {
$site = "";
} else {
$site = "<a href=\"$member[site]\" target=\"_blank\"><img src=\"images/site.gif\" border=\"0\" alt=\"Visit User's Homepage\" /></a> ";
}

$query = mysql_query("SELECT * FROM $table_whosonline WHERE username='$thread[author]'"); 
$onlineinfo = mysql_fetch_array($query);

$ol[$thread[author]] = $onlineinfo;

if ($onlineinfo[username] == $thread[author]) { 
$onlinestatus = "$lang[textstatus] <span class=\"11px\"><b>$lang[textonline]</b></span>"; 
} else { 
$onlinestatus = "$lang[textstatus] <span class=\"11px\"><b>$lang[textoffline]</b></span>"; 
}

$edit = "<a href=\"post.php?action=edit&fid=$fid&tid=$tid\"><img src=\"images/edit.gif\" border=\"0\" alt=\"Edit Post\" /></a> ";
$search = "<a href=\"misc.php?action=search&srchuname=".rawurlencode($thread[author])."&searchsubmit=a&srchfid=all\"><img src=\"images/find.gif\" border=\"0\"></a> ";
$profile = "<a href=\"member.php?action=viewpro&member=".rawurlencode($thread[author])."\"><img src=\"images/profile.gif\" border=\"0\"></a> ";

if($u2ustatus == "on") { 
$u2u = "<a href=\"#\" onclick=\"Popup('misc2.php?action1=u2u&action=send&username=".rawurlencode($thread[author])."', 'Window', 550, 450);\"><img src=\"images/u2u.gif\" border=\"0\" alt=\"U2U Member\" /></a> "; 
} else { $u2u = ""; }


if($status != "Administrator" && $status != "Moderator") {
$ip = "";
} else {
$ip = "<a href=\"topicadmin.php?action=getip&fid=$fid&tid=$tid\"><img src=\"images/ip.gif\" border=\"0\" alt=\"Get IP\" /></a>";
}

}

$showtitle = $member[status];
$queryrank = mysql_query("SELECT * FROM $table_ranks") or die(mysql_error());
while($rank = mysql_fetch_array($queryrank)) {
if($member[postnum] >= $rank[posts]) {
$allowavatars = $rank[allowavatars];
$showtitle = $rank[title];
$stars = "";
for($i = 0; $i < $rank[stars]; $i++) {
$stars .= "<img src=\"images/star.gif\">";
}

if($rank[avatarrank] != "") {
$avarank = $rank[avatarrank];
}
} else {
$showtitle = $showtitle;
$stars = $stars;
}
}

if($member[status] == "Administrator" || $member[status] == "Super Moderator" || $member[status] == "Moderator" || $member[status] == "Banned") {
$query = mysql_query("SELECT stars FROM $table_ranks ORDER BY stars DESC") or die(mysql_error());
$staffstar = mysql_result($query, 0);
$stars = "";
for($i = 0; $i < $staffstar; $i++) {
$stars .= "<img src=\"images/star.gif\">";
}

if($member[status] == "Administrator"){
$showtitle = "$lang[textadmin]";
$stars .= "<img src=\"images/star.gif\"><img src=\"images/star.gif\"><img src=\"images/star.gif\">";
$allowavatars = "yes";
} elseif($member[status] == "Super Moderator"){
$showtitle = "$lang[textsupermod]";
$stars .= "<img src=\"images/star.gif\"><img src=\"images/star.gif\">";
$allowavatars = "yes";
} elseif($member[status] == "Moderator"){
$showtitle = "$lang[textmod]";
$stars .= "<img src=\"images/star.gif\">";
$allowavatars = "yes";
}
}

if($member[status] == "Banned"){
$showtitle = "$lang[textbanned]";
$stars = "";
}

if($member[customstatus] != "") {
$showtitle = $member[customstatus];
} else {
$showtitle = $showtitle;
}

$tharegdate = gmdate("$dateformat", $member[regdate] + ($timeoffset * 3600));
$miscinfo = "<br />$lang[textposts] $member[postnum]<br />
$lang[textregistered] $tharegdate";

$showtitle .= "<br />";
$stars .= "<br />";

if($allowavatars == "yes") {
if($avarank != "" && $member[avatar] == "") {
$avatar = "<img src=\"$avarank\">";
} elseif($member[avatar] != "") {
$avatar = "<img src=\"$member[avatar]\">";
} else {
$avatar = "";
}
}


if($avastatus != "on") {
$avatar = "&nbsp;";
}

if($member[username] == "" || $thread[username] == $lang[textguest]) {
$showtitle = $lang[unreg];
$miscinfo = "";
$profile = "";
$email = "";
$onlinestatus = "";
$stars = "";
$u2u = "";
$search = "";
$warn = "";
$site = "";

if($noreg == "on") {
$showtitle = "";
}
}

if($thisuser != "" && $reportpost != "off") { 
$reportlink = " | <a href=\"topicadmin.php?action=report&fid=$fid&tid=$tid\">$lang[textreportpost]</a>"; 
} else { 
$reportlink = ""; 
} 

$userstuff = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\"><tr><td class=\"11px\">$profile$email$edit$repquote$site$search$u2u$reportlink</td></tr></table>";
$adminstuff = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"right\"><tr><td>$ip</td></tr></table>";

$thread[subject] = stripslashes($thread[subject]);
?>
<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr>
<td width="18%" class="header"><?=$lang[textauthor]?> </td>
<td class="header"><? echo "$lang[textsubject] $thread[subject]"; ?></td>
</tr>
<?
if($forum[private] == "staff" && $status != "Administrator" && $status != "Super Moderator" && $status != "Moderator") { 
echo "<tr class=\"tablerow\"><td bgcolor=\"$altbg1\" colspan=\"2\">$lang[privforummsg]</td></tr>"; 
exit; 
}

if($sub[private] == "staff" && $status != "Administrator" && $status != "Super Moderator" && $status != "Moderator") { 
echo "<tr class=\"tablerow\"><td bgcolor=\"$altbg1\" colspan=\"2\">$lang[privforummsg]</td></tr>"; 
exit; 
}

if($page == 1) {
if(!$notexist) {
$bbcodeoff = $thread[bbcodeoff];
$smileyoff = $thread[smileyoff];
$thread[message] = stripslashes($thread[message]);
$thread[message] = postify($thread[message], $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", $table_words, $table_forums, $table_smilies);

if($thread[usesig] == "yes") {
$member[sig] = postify($member[sig], "no", "", "", $bordercolor, $sigbbcode, $sightml, $table_words, $table_forums, $table_smilies);
$thread[message] .= "<p>&nbsp;</p>____________________<br />$member[sig]";
}
?>
<tr bgcolor="<?=$altbg1?>">
<td rowspan="3" valign="top" class="tablerow"><span class="postauthor"><?=$thread[author]?></span><br />
<div class="11px"><?=$showtitle?><?=$stars?><br /><?=$avatar?><br /><?=$miscinfo?><br /><?=$onlinestatus?></div><br /></td>
<td valign="top" class="11px" class="tablerow"><?=$thread[icon]?> &nbsp; <?=$poston?></td></tr>
<tr bgcolor="<?=$altbg1?>"><td height="120" valign="top"><font class="12px"><?=$thread[message]?></font><br />&nbsp;</td></tr>
<tr bgcolor="<?=$altbg1?>"><td valign="top"><?=$userstuff?> <?=$adminstuff?></td></tr>

<? 
$ppp -= 1; 
} else { 
echo "<tr class=\"tablerow\"><td colspan=\"8\" bgcolor=\"$altbg1\">$notexist</td></tr>"; 
} 
} 
$querypost = mysql_query("SELECT * FROM $table_posts WHERE fid='$fid' AND tid='$tid' ORDER BY dateline LIMIT $start_limit, $ppp") or die(mysql_error()); 
$thisbg = $altbg2; 

while($post = mysql_fetch_array($querypost)) {

$date = gmdate("$dateformat", $post[dateline] + ($timeoffset * 3600));
$time = gmdate("$timecode", $post[dateline] + ($timeoffset * 3600));

$poston = "$lang[textposton] $date $lang[textat] $time";

if($post[icon] != "") {
$post[icon] = "<img src=\"images/$post[icon]\" />";
}

if($info[$post[author]] && $info[$post[author]] != "") {
$member = $info[$post[author]];
}
else {
$query = mysql_query("SELECT * FROM $table_members WHERE username='$post[author]'") or die(mysql_error());
$member = mysql_fetch_array($query);

$info[$post[author]] = $member;
}

if($member[email] != "" && $member[showemail] == "yes") {
$email = "<a href=\"mailto:$member[email]\"><img src=\"images/email.gif\" border=\"0\" alt=\"E-Mail User\" /></a> ";
} else {
$email = "";
}
$member[site] = str_replace("http://", "", $member[site]);
$member[site] = "http://$member[site]";

if($member[site] == "http://") {
$site = "";
} else {
$site = "<a href=\"$member[site]\" target=\"_blank\"><img src=\"images/site.gif\" border=\"0\" alt=\"Visit User's Homepage\" /></a> ";
}

if($ol[$post[author]] && $ol[$post[author]] != '') {
$onlineinfo = $ol[$post[author]];
}
else {
$query = mysql_query("SELECT * FROM $table_whosonline WHERE username='$post[author]'"); 
$onlineinfo = mysql_fetch_array($query); 
$ol[$post[author]] = $onlineinfo;
}
if ($onlineinfo[username] == $post[author]) { 
$onlinestatus = "$lang[textstatus] <span class=\"11px\"><b>$lang[textonline]</b></span>"; 
} else { 
$onlinestatus = "$lang[textstatus] <span class=\"11px\"><b>$lang[textoffline]</b></span>"; 
} 

$edit = "<a href=\"post.php?action=edit&fid=$fid&tid=$tid&pid=$post[pid]\"><img src=\"images/edit.gif\" border=\"0\" alt=\"Edit Post\" /></a> ";

if($closed == "yes") {
$repquote = "";
} else {
$repquote = "<a href=\"post.php?action=reply&fid=$fid&tid=$tid&repquote=r|$post[pid]\"><img src=\"images/quote.gif\" border=\"0\" alt=\"Reply With Quote\" /></a> ";
}


$search = "<a href=\"misc.php?action=search&srchuname=".rawurlencode($post[author])."&searchsubmit=a&srchfid=all\"><img src=\"images/find.gif\" border=\"0\"></a> ";
$profile = "<a href=\"member.php?action=viewpro&member=".rawurlencode($post[author])."\"><img src=\"images/profile.gif\" border=\"0\"></a> ";

if($u2ustatus == "on") { 
$u2u = "<a href=\"#\" onclick=\"Popup('misc2.php?action1=u2u&action=send&username=".rawurlencode($post[author])."', 'Window', 550, 450);\"><img src=\"images/u2u.gif\" border=\"0\" alt=\"U2U Member\" /></a> "; 
} else { 
$u2u = ""; 
}

if($status != "Administrator" && $status != "Moderator" && $status != "Super Moderator") {
$ip = "";
} else{
$ip = "<a href=\"topicadmin.php?action=getip&fid=$fid&tid=$tid&pid=$post[pid]\"><img src=\"images/ip.gif\" border=\"0\" alt=\"Get IP\" /></a>";
}

$showtitle = $member[status];
$queryrank = mysql_query("SELECT * FROM $table_ranks") or die(mysql_error());
while($rank = mysql_fetch_array($queryrank)) {
if($member[postnum] >= $rank[posts]) {
$allowavatars = $rank[allowavatars];
$showtitle = $rank[title];
$stars = "";
for($i = 0; $i < $rank[stars]; $i++) {
$stars .= "<img src=\"images/star.gif\">";
}

if($rank[avatarrank] != "") {
$avarank = $rank[avatarrank];
}
} else {
$showtitle = $showtitle;
$stars = $stars;
}
}

if($member[status] == "Administrator" || $member[status] == "Super Moderator" || $member[status] == "Moderator") {
$query = mysql_query("SELECT stars FROM $table_ranks ORDER BY stars DESC") or die(mysql_error());
$staffstar = mysql_result($query, 0);
$stars = "";
for($i = 0; $i < $staffstar; $i++) {
$stars .= "<img src=\"images/star.gif\">";
}

if($member[status] == "Administrator"){
$showtitle = "$lang[textadmin]";
$stars .= "<img src=\"images/star.gif\"><img src=\"images/star.gif\"><img src=\"images/star.gif\">";
$allowavatars = "yes";
} elseif($member[status] == "Super Moderator"){
$showtitle = "$lang[textsupermod]";
$stars .= "<img src=\"images/star.gif\"><img src=\"images/star.gif\">";
$allowavatars = "yes";
} elseif($member[status] == "Moderator"){
$showtitle = "$lang[textmod]";
$stars .= "<img src=\"images/star.gif\">";
$allowavatars = "yes";
} 
}

if($member[status] == "Banned"){
$showtitle = "$lang[textbanned]";
$stars = "";
}

if($member[customstatus] != "") {
$showtitle = $member[customstatus];
} else {
$showtitle = $showtitle;
}

$tharegdate = gmdate("$dateformat", $member[regdate] + ($timeoffset * 3600));
$miscinfo = "<br />$lang[textposts] $member[postnum]<br />
$lang[textregistered] $tharegdate";

$showtitle .= "<br />";
$stars .= "<br />";

if($allowavatars == "yes") {
if($avarank != "" && $member[avatar] == "" && $member[status] != "Administrator" && $member[status] != "Super Moderator" && $member[status] != "Moderator") {
$avatar = "<img src=\"$avarank\">";
} elseif($member[avatar] != "") {
$avatar = "<img src=\"$member[avatar]\">";
} else {
$avatar = "";
}
}

if($avastatus != "on") {
$avatar = "&nbsp;";
}

if($member[username] == "" || $thread[username] == $lang[textguest]) {
$showtitle = $lang[unreg];
$miscinfo = "";
$profile = "";
$email = "";
$onlinestatus = "";
$stars = "";
$u2u = "";
$search = "";
$warn = "";
$site = "";

if($noreg == "on") {
$showtitle = "";
}
}

if($thisuser != "" && $reportpost != "off") { 
$reportlink = " | <a href=\"topicadmin.php?action=report&fid=$fid&tid=$tid&pid=$post[pid]\">$lang[textreportpost]</a>"; 
} else { 
$reportlink = ""; 
} 

$userstuff = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\"><tr><td class=\"11px\">$profile$email$edit$repquote$site$search$u2u$reportlink</td></tr></table>";
$adminstuff = "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"right\"><tr><td>$ip</td></tr></table>";
$bbcodeoff = $post[bbcodeoff];
$smileyoff = $post[smileyoff];
$post[message] = stripslashes($post[message]);
$post[message] = postify($post[message], $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", $table_words, $table_forums, $table_smilies);

if($post[usesig] == "yes") {
$member[sig] = postify($member[sig], "no", "", "", $bordercolor, $sigbbcode, $sightml, $table_words, $table_forums, $table_smilies);
$post[message] .= "<p>&nbsp;</p>____________________<br />$member[sig]";
}

if(!$notexist) { 
?>

<tr bgcolor="<?=$thisbg?>">
<td rowspan="3" valign="top" class="tablerow" width="18%"><a name="pid<?=$post[pid]?>"></a><span class="postauthor"><?=$post[author]?> </span><br />
<div class="11px"><?=$showtitle?><?=$stars?><?=$avatar?><br /><?=$miscinfo?><br /><?=$onlinestatus?></div><br /></td>
<td valign="top" class="11px" class="tablerow" width="82%"><?=$post[icon]?> &nbsp; <?=$poston?></td></tr>
<tr bgcolor="<?=$thisbg?>"><td height="120" valign="top"><font class="12px"><?=$post[message]?></font><br />&nbsp;</td></tr>
<tr bgcolor="<?=$thisbg?>"><td valign="top" class="tablerow"><?=$userstuff?> <?=$adminstuff?></td></tr>

<?
} else {
echo "<tr class=\"tablerow\"><td colspan=\"8\" bgcolor=\"$altbg1\">$notexist</td></tr>"; 
}

if($thisbg == $altbg2) {
$thisbg = $altbg1;
} 
else {
$thisbg = $altbg2;
}

}

?>
</table>
</td></tr></table>

<table width="<?=$tablewidth?>" cellspacing="0" cellpadding="0" align="center">
<tr bgcolor="<?=$bgcolor?>">
<td class="multi"><?=$multipage?></td>
<td class="post" align="right">
<?=$newtopiclink?> &nbsp;<?=$replylink?>
</td></tr>

<tr  bgcolor="<?=$bgcolor?>"><td class="11px" colspan="2">
<br />

<?
if($status == "Administrator" || $status == "Super Moderator" || $status == "Moderator") { 
?>
<span style="font-family:arial; font-size: 11px;">
<a href="topicadmin.php?action=delete&fid=<?=$fid?>&tid=<?=$tid?>"><?=$lang[textdeletethread]?></a> - <?=$closeopen?> - 
<a href="topicadmin.php?action=move&fid=<?=$fid?>&tid=<?=$tid?>"><?=$lang[textmovethread]?></a><br />
<?=$topuntop?> - <a href="topicadmin.php?action=bump&fid=<?=$fid?>&tid=<?=$tid?>"><?=$lang[textbumpthread]?></a></span>
<? 
} 
?>

</td></tr>

<?
$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
if($showtotaltime != "off") { 
$totaltime = ($endtime - $starttime); 
$totaltime = number_format($totaltime, 7); 
}

$html = template("footer.html");
eval("echo stripslashes(\"$html\");");
}

if($action == "printable") {
?>
<html>
<head>
<style type="text/css">
p {
font-size: 14px;
font-family: arial, verdana;
}

.16px {
font-size: 16px;
font-family: arial, verdana;
font-weight: bold;
}

.14px {
font-size: 14px;
font-family: arial, verdana;
font-weight: bold;
}

.13px {
font-size: 14px;
font-family: arial, verdana;
}
</style>
<title><?=$bbname?> - <?=$lang[textpowered]?></title>
</head>
<body>

<?
$query = mysql_query("SELECT * FROM $table_threads WHERE fid='$fid' AND tid='$tid'") or die(mysql_error());
$thread = mysql_fetch_array($query);

$date = gmdate("$dateformat",$thread[dateline] + ($timeoffset * 3600));
$time = gmdate("$timecode",$thread[dateline] + ($timeoffset * 3600));
$poston = "$date $lang[textat] $time";
$thread[message] = stripslashes($thread[message]);

$bbcodeoff = $thread[bbcodeoff]; 
$smileyoff = $thread[smileyoff]; 
$thread[message] = postify($thread[message], $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", $table_words, $table_forums, $table_smilies); 
$thread[subject] = stripslashes($thread[subject]);
?>

<img src="<?=$boardimg?>" alt="Board logo" border="0" /><br /><br />
<span class="16px"><?=$thread[subject]?></span>

<hr>
<span class="14px"><?=$thread[author]?></span> - <span class="13px"><?=$poston?></span>
<p><?=$thread[message]?></p>

<?
$querypost = mysql_query("SELECT * FROM $table_posts WHERE fid='$fid' AND tid='$tid' ORDER BY dateline") or die(mysql_error());
while($post = mysql_fetch_array($querypost)) {

$date = gmdate("$dateformat",$post[dateline] + ($timeoffset * 3600));
$time = gmdate("$timecode",$post[dateline] + ($timeoffset * 3600));
$poston = "$date $lang[textat] $time";
$post[message] = stripslashes($post[message]);

$bbcodeoff = $post[bbcodeoff]; 
$smileyoff = $post[smileyoff]; 
$post[message] = postify($post[message], $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", $table_words, $table_forums, $table_smilies);
?>

<hr>
<span class="14px"><?=$post[author]?></span> - <span class="13px"><?=$poston?></span>
<p><?=$post[message]?></p>
<?
}
?>
<hr>
<?
}
?>