<?
/*

XMB 1.6 v2c Magic Lantern
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/
require "./header.php";

loadtemplates('header,footer');

$navigation .= "&raquo; Todays Posts";

eval("\$header = \"".template("header")."\";");
echo $header;


eval("\$today = \"".template("today")."\";");
echo $today;

$sql1 = "SELECT * FROM $table_threads";
$sql2 = "SELECT * FROM $table_posts";

$srchfrom = time() - 86400;

$sql1 .= " WHERE lastpost >= '$srchfrom' ORDER BY lastpost DESC";
$sql2 .= " WHERE dateline >= '$srchfrom' ORDER BY dateline DESC";

$query1 = $db->query($sql1);
$query2 = $db->query($sql2);

$threadcount = mysql_num_rows($query1);
while($thread = mysql_fetch_array($query1)) {
$date = date("$dateformat",$thread[dateline]);
$time = date("$timecode",$thread[dateline]);
$poston = "$date $lang[textat] $time";
$thread[subject] = stripslashes($thread[subject]);

$query = $db->query("SELECT * FROM $table_forums WHERE fid='$thread[fid]'");
$forum = $db->fetch_array($query);

$authorization = privfcheck($forum[private], $forum[userlist]);

if($authorization == "true") {

if($thread[author] == $lang[textguest]) {
$authorlink = $thread[author];
}else {
$authorlink = "<a href=\"member.php?action=viewpro&member=".rawurlencode($thread[author])."\">$thread[author]</a>";
}

$lastpost = explode("|", $thread[lastpost]);
$dalast = $lastpost[0];

if($lastpost[1] == $lang[textguest]) {
$lastpost[1] = $lastpost[1];
} else {
$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";
}

$lastreplydate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600));
$lastreplytime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600));
$lastpost = "$lang_lastreply1 $lastreplydate $lang_textat $lastreplytime<br />$lang_textby $lastpost[1]";

if($thread[icon] != "") {
$thread[icon] = "<img src=\"$imgdir/$thread[icon]\" />";
} else {
$thread[icon] = "&nbsp;";
}

if($thread[replies] >= $hottopic) {
$folder = "<img src=\"$imgdir/hot_folder.gif\" alt=\"Hot Topic\" />";
} else {
$folder = "<img src=\"$imgdir/folder.gif\" alt=\"Topic\" />";
}

$lastvisit2 -= 540;
if($thread[replies] >= $hottopic && $lastvisit2 < $dalast) {
$folder = "<img src=\"$imgdir/hot_red_folder.gif\">";
}
elseif($lastvisit2 < $dalast) {
$folder = "<img src=\"$imgdir/red_folder.gif\">";
}else {
$folder = $folder;
}
$lastvisit2 += 540;

if($thread[closed] == "yes") {
$folder = "<img src=\"$imgdir/lock_folder.gif\" alt=\"Closed Topic\" />";
}

$moved = explode("|", $thread[closed]);
if($moved[0] == "moved") {
$prefix = "$lang_moved ";
$thread[tid] = $moved[1];
$thread[replies] = "-";
$thread[views] = "-";
$folder = "<img src=\"$imgdir/lock_folder.gif\" alt=\"Closed Topic\" />";
}

if(!$ppp || $ppp == '') {
        $ppp = $postperpage;
}

$postsnum = $thread[replies] + 1;
if($postsnum  > $ppp) {
        $posts = $postsnum;
        $topicpages = $posts / $ppp;
        $topicpages = ceil($topicpages);
        for ($i = 1; $i <= $topicpages; $i++) {
                $pagelinks .= " <a href=\"viewthread.php?tid=$thread[tid]&page=$i\">$i</a> ";
                if($i == 3) {
                        $i = $topicpages + 1;
                }
        }
        if($topicpages > 3) {
                $pagelinks .= " .. <a href=\"viewthread.php?tid=$thread[tid]&page=$topicpages\">$topicpages </a>";
        }
        $multipage2 = "(<small>Pages: $pagelinks</small>)";
        $pagelinks = "";
} else {
        $multipage2 = "";
}


$thread[subject] = stripslashes($thread[subject]);

eval("\$today2 = \"".template("today2")."\";");
echo $today2;

}
}

if($threadcount == "0" && $postcount == "0") {

eval("\$today3 = \"".template("today3")."\";");
echo $today3;
}
echo "</table></td></tr></table>";

$mtime2 = explode(" ", microtime());
$endtime = $mtime2[1] + $mtime2[0];
$totaltime = ($endtime - $starttime);
$totaltime = number_format($totaltime, 7);

eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>