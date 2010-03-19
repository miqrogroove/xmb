<?
require "header.php";

$query = mysql_query("SELECT moderator, name, private, fid, userlist, threads, type, fup FROM $table_forums WHERE fid='$fid'") or die(mysql_error());
$forum = mysql_fetch_array($query);

if($forum[type] != "forum" && $forum[type] != "sub") {
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
$navigation .= $forum[name];
} else {
$query = mysql_query("SELECT name, fid FROM $table_forums WHERE fid='$forum[fup]'") or die(mysql_error());
$fup = mysql_fetch_array($query);
$navigation .= "<a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> &gt; $forum[name]";
}

$html = template("header.html");
eval("echo stripslashes(\"$html\");");

$query = mysql_query("SELECT name FROM $table_forums WHERE type='sub' AND fup='$fid'") or die(mysql_error());
$sub = mysql_fetch_array($query);

if($sub[name] != "") {
?>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr>
<?
if($postscol == "1col") {
?>
<td width="3%" class="header">&nbsp;</td>
<td width="60%" class="header"><?=$lang[textforum]?></td>
<td width="10%" class="header"><?=$lang[textcontents]?></td>
<td width="19%" class="header"><?=$lang[textlastpost]?></td>
</tr>
<?
} else {
?>
<td width="3%" class="header">&nbsp;</td>
<td width="58%" class="header"><?=$lang[textforum]?></td>
<td width="6%" class="header"><?=$lang[texttopics]?></td>
<td width="6%" class="header"><?=$lang[textposts]?></td>
<td width="19%" class="header"><?=$lang[textlastpost]?></td>
</tr>
<?
}
$fulist = $forum[userlist];
$querys = mysql_query("SELECT * FROM $table_forums WHERE type='sub' AND fup='$fid'") or die(mysql_error());
while($forum = mysql_fetch_array($querys)) {
forum($forum[lastpost], $timeoffset, $forum[moderator], $lastvisit2, $hideprivate, $status, $forum[private], $forum[posts], $forum[threads], $altbg1, $altbg2, $forum[name], $forum[fid], $forum[description], $timecode, $dateformat, $langfile, $thisuser, $forum[userlist]);
}
$forum[userlist] = $fulist;
?>
</table>
</td></tr></table><br />
<?
}
if($notexist != $lang[textnoforum]) {
if($newtopicimg != "") {
$newtopiclink = "<a href=\"post.php?action=newtopic&fid=$fid\"><img src=\"$newtopicimg\" border=\"0\"></a>";
} else {
$newtopiclink = "<a href=\"post.php?action=newtopic&fid=$fid\">$lang[textnewtopic]</a>";
}
}

if($piconstatus == "on") {
$picon1 = "<td width=\"4%\" class=\"header\">&nbsp;</td>";
}


if(!$tpp || $tpp == '') {
$tpp = $topicperpage;
}

if ($page) {
$start_limit = ($page-1) *$tpp;
} else {
$start_limit = 0;
$page = 1;
}

if($cusdate != 0) {
$cusdate = time() - $cusdate;
$cusdate = "AND (substring_index(lastpost, '|',1)+1) >= '$cusdate'";
}
elseif($cusdate == 0) {
$cusdate = "";
}

if(!$ascdesc) {
$ascdesc = "DESC";
}

$querytop = mysql_query("SELECT *, (substring_index(lastpost, '|',1)+1) lastpostd FROM $table_threads WHERE fid='$fid' $cusdate ORDER BY topped $ascdesc,lastpostd $ascdesc LIMIT $start_limit, $tpp") or die(mysql_error());
$query = mysql_query("SELECT count(tid) FROM $table_threads WHERE fid='$fid'") or die(mysql_error());
$topicsnum = mysql_result($query, 0);

if ($topicsnum  > $tpp) {
$pages = $topicsnum  / $tpp;
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
$fwd_back .= "<a href=\"forumdisplay.php?fid=$fid&page=1\"><<</a>";

for ($i = $from; $i <= $to; $i++) {
if($i != $page) {
$fwd_back .= "&nbsp;&nbsp;<a href=\"forumdisplay.php?fid=$fid&page=$i\">$i</a>&nbsp;&nbsp;";
} else {
$fwd_back .= "&nbsp;&nbsp;<u><b>$i</b></u>&nbsp;&nbsp;";
}
}

$fwd_back .= "<a href=\"forumdisplay.php?fid=$fid&page=$pages\">>></a>";
$multipage = $fwd_back;
}
?>

<table width="<?=$tablewidth?>" cellspacing="0" cellpadding="0" align="center">
<tr>
<td bgcolor="<?=$bgcolor?>" class="multi"><?=$multipage?></td>
<td bgcolor="<?=$bgcolor?>" class="post" align="right"><?=$newtopiclink?></td></tr></table>

<table cellspacing="0" cellpadding="0" border="0" width="<?=$tablewidth?>" align="center">
<tr><td bgcolor="<?=$bordercolor?>">

<table border="0" cellspacing="<?=$borderwidth?>" cellpadding="<?=$tablespace?>" width="100%">
<tr>
<?
if($postscol == "1col") {
?>
<td width="4%" class="header">&nbsp;</td>
<?=$picon1?>
<td width="47%" class="header"><?=$lang[textsubject]?></td>
<td width="14%" class="header"><?=$lang[textauthor]?></td>
<td width="10%" class="header"><?=$lang[textcontents]?></td>
<td width="19%" class="header"><?=$lang[textlastpost]?></td>
</tr>
<?
} else {
?>
<td width="4%" class="header">&nbsp;</td>
<?=$picon1?>
<td width="47%" class="header"><?=$lang[textsubject]?></td>
<td width="14%" class="header"><?=$lang[textauthor]?></td>
<td width="6%" class="header"><?=$lang[textreplies]?></td>
<td width="6%" class="header"><?=$lang[textviews]?></td>
<td width="19%" class="header"><?=$lang[textlastpost]?></td>
</tr>
<?
}

if($forum[private] == "staff" && $status != "Administrator" && $status != "Super Moderator" && $status != "Moderator") {
echo "<tr class=\"tablerow\"><td bgcolor=\"$altbg1\" colspan=\"8\">$lang[privforummsg]</td></tr>";
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

if($subf[private] == "staff" && $status != "Administrator" && $status != "Super Moderator" && $status != "Moderator") {
echo "<tr class=\"tablerow\"><td bgcolor=\"$altbg1\" colspan=\"8\">$lang[privforummsg]</td></tr>";
exit;
}


while($thread = mysql_fetch_array($querytop)) {
$lastpost = explode("|", $thread[lastpost]);
$dalast = $lastpost[0];

if($lastpost[1] == $lang[textguest]) {
$lastpost[1] = $lastpost[1];
} else {
$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";
}

$lastreplydate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600));
$lastreplytime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600));
$lastpost = "$lang[lastreply1] $lastreplydate $lang[textat] $lastreplytime<br />$lang[textby] $lastpost[1]";

if($thread[icon] != "") {
$thread[icon] = "<img src=\"images/$thread[icon]\" />";
} else {
$thread[icon] = "&nbsp;";
}

if($thread[replies] >= $hottopic) {
$folder = "<img src=\"images/hot_folder.gif\" alt=\"Hot Topic\" />";
} else {
$folder = "<img src=\"images/folder.gif\" alt=\"Topic\" />";
}

$lastvisit2 -= 540;
if($thread[replies] >= $hottopic && $lastvisit2 < $dalast) {
$folder = "<img src=\"images/hot_red_folder.gif\">";
}
elseif($lastvisit2 < $dalast) {
$folder = "<img src=\"images/red_folder.gif\">";
}
else {
$folder = $folder;
}
$lastvisit2 += 540;

if($thread[closed] == "yes") {
$folder = "<img src=\"images/lock_folder.gif\" alt=\"Closed Topic\" />";
}

if($thread[topped] == 1) {
$prefix = "<span class=\"11px\">($lang[toppedprefix])</span>";
} else {
$prefix = "";
}

$thread[subject] = stripslashes($thread[subject]);

$threadlink = "<a href=\"viewthread.php?tid=$thread[tid]\">$thread[subject]</a>";

if($thread[author] == $lang[textguest]) {
$authorlink = $thread[author];
}
else {
$authorlink = "<a href=\"member.php?action=viewpro&member=".rawurlencode($thread[author])."\">$thread[author]</a>";
}

if(!$ppp || $ppp == '') {
$ppp = $postperpage;
}

if($thread[replies]  > $ppp) {
$posts = $thread[replies];
$posts++;
$topicpages = $posts / $ppp;
$topicpages = ceil($topicpages);
for ($i = 1; $i <= $topicpages; $i++) {
$pagelinks .= " <a href=\"viewthread.php?tid=$thread[tid]&page=$i\">$i</a> ";
}
$multipage2 = "(<small>Pages: $pagelinks</small>)";
$pagelinks = "";
} else {
$multipage2 = "";
}

if($piconstatus == "on") {
$picon2 = "<td bgcolor=\"$altbg1\" align=\"center\" class=\"tablerow\">$thread[icon]</td>
<td bgcolor=\"$altbg2\" class=\"tablerow\"><font class=\"12px\">$threadlink $prefix $multipage2</font></td>
<td bgcolor=\"$altbg1\" class=\"tablerow\">$authorlink</td>";

if($postscol == "1col") {
$ratecol2 = "<td bgcolor=\"$altbg2\" class=\"tablerow\" align=\"center\"><font size=\"1\" face=\"verdana\">$lang[textreplies] $thread[replies]<br />$lang[textviews] $thread[views]</font></td>
<td bgcolor=\"$altbg1\" class=\"tablerow\"><font size=\"1\" face=\"verdana\">$lastpost</font></td>";
} else {
$ratecol2 = "<td bgcolor=\"$altbg2\" class=\"tablerow\" align=\"center\"><font class=\"12px\">$thread[replies]</font></td>
<td bgcolor=\"$altbg1\" class=\"tablerow\" align=\"center\"><font class=\"12px\">$thread[views]</font></td>
<td bgcolor=\"$altbg2\" class=\"tablerow\"><font size=\"1\" face=\"verdana\">$lastpost</font></td>";
}

} else {
$picon2 = "<td bgcolor=\"$altbg1\" class=\"tablerow\"><font class=\"12px\">$threadlink $prefix $multipage</font></td>
<td bgcolor=\"$altbg2\" class=\"tablerow\">$authorlink</td>";

if($postscol == "1col") {
$ratecol2 = "<td bgcolor=\"$altbg1\" class=\"tablerow\" align=\"center\"><font size=\"1\" face=\"verdana\">$lang[textreplies] $thread[replies]<br />$lang[textviews] $thread[views]</font></td>
<td bgcolor=\"$altbg2\" class=\"tablerow\"><font size=\"1\" face=\"verdana\">$lastpost</font></td>";
} else {
$ratecol2 = "<td bgcolor=\"$altbg1\" class=\"tablerow\" align=\"center\"><font class=\"12px\">$thread[replies]</font></td>
<td bgcolor=\"$altbg2\" class=\"tablerow\" align=\"center\"><font class=\"12px\">$thread[views]</font></td>
<td bgcolor=\"$altbg1\" class=\"tablerow\"><font size=\"1\" face=\"verdana\">$lastpost</font></td>";
}
}
?>

<tr>
<td bgcolor="<?=$altbg2?>" align="center" class="tablerow"><?=$folder?></td>
<?=$picon2?>
<?=$ratecol2?>
</tr>

<?
}
if($notexist) {
echo "<tr class=\"tablerow\"><td colspan=\"8\" bgcolor=\"$altbg1\">$notexist</td></tr>";
}

if($topicsnum == 0 && !$notexist) {
echo "<tr class=\"tablerow\"><td colspan=\"8\" bgcolor=\"$altbg1\">$lang[noposts]</td></tr>";
}
?>

</table>
</td></tr></table>

<table width="<?=$tablewidth?>" cellspacing="0" cellpadding="0" align="center">
<tr>
<td bgcolor="<?=$bgcolor?>" class="multi"><?=$multipage?></td>
<td bgcolor="<?=$bgcolor?>" class="post" align="right">
<?=$newtopiclink?></td></tr>

<?
if($showsort == "on") {

if($cusdate == "86400") {
$check1 = "selected=\"selected\"";
} elseif($cusdate == "432000") {
$check5 = "selected=\"selected\"";
} elseif($cusdate == "1296000") {
$check15 = "selected=\"selected\"";
} elseif($cusdate == "2592000") {
$check30 = "selected=\"selected\"";
} elseif($cusdate == "5184000") {
$check60 = "selected=\"selected\"";
} elseif($cusdate == "8640000") {
$check100 = "selected=\"selected\"";
} elseif($cusdate == "31536000") {
$checkyear = "selected=\"selected\"";
} elseif($cusdate == "0" || $cusdate == "") {
$checkall = "selected=\"selected\"";
}
?>

<table width="<?=$tablewidth?>" cellspacing="0" cellpadding="0" align="center"><tr><td align="center">
<form method="post" action="forumdisplay.php?fid=<?=$fid?>">
<span class="11px"><?=$lang[showtopics]?></span>
<select name="cusdate">
<option value="86400" <?=$check1?>><?=$lang[day1]?></option>
<option value="432000" <?=$check5?>><?=$lang[day5]?></option>
<option value="1296000" <?=$check15?>><?=$lang[day15]?></option>
<option value="2592000" <?=$check30?>><?=$lang[day30]?></option>
<option value="5184000" <?=$check60?>><?=$lang[day60]?></option>
<option value="8640000" <?=$check100?>><?=$lang[day100]?></option>
<option value="31536000" <?=$checkyear?>><?=$lang[lastyear]?></option>
<option value="0" <?=$checkall?>><?=$lang[beginning]?></option>
</select>

<span class="11px"><?=$lang[sortby]?></span>
<select name="ascdesc">
<option value="ASC"><?=$lang[asc]?></option>
<option value="DESC" selected="selected"><?=$lang[desc]?></option>
</select>

<input type="submit" value="<?=$lang[textgo]?>">
</form>
</td></tr></table>

<?
}

$multipage = "<div align=\"right\">$multipage</div>";
$foldernote = "<img src=\"images/red_folder.gif\" alt=\"Topic\" /> $lang[opennew] (<img src=\"images/hot_red_folder.gif\" alt=\"Hot Topic\" /> $lang[hottopic])<br /><img src=\"images/folder.gif\" alt=\"Topic\" /> $lang[opentopic] (<img src=\"images/hot_folder.gif\" alt=\"Hot Topic\" /> $lang[hottopic])<br /><img src=\"images/lock_folder.gif\" alt=\"Closed Topic\" /> $lang[locktopic]";

$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
if($showtotaltime != "off") {
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);
}

$html = template("footer.html");
eval("echo stripslashes(\"$html\");");
?>