<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

require './header.php';
loadtemplates('header,today,today2,today3,footer');
$navigation .= '&raquo; Todays Posts';

eval("\$header = \"".template("header")."\";");
echo $header;

// Patch for proper Censor functioning

eval("\$today = \"".template("today")."\";");
$today = stripslashes($today);

echo $today;
$srchfrom = time() - 86400;
$query = $db->query("SELECT t.*,f.password,f.private,f.userlist,f.name FROM $table_threads t, $table_forums f WHERE t.lastpost >= '$srchfrom' AND t.fid=f.fid ORDER BY t.lastpost DESC");

$threadcount = $db->num_rows($query);
while($thread = $db->fetch_array($query)){
	$date = gmdate($dateformat, $thread['dateline'] + ($addtime * 3600));
	$time = gmdate($timecode, $thread['dateline'] + ($addtime * 3600));
	$poston = "$date $lang[textat] $time";
	$thread['subject'] = stripslashes($thread['subject']);

	$forum[private] = $thread[private];
	$forum[userlist] = $thread[userlist];
	$forum[name] = $thread[name];

	$authorization = privfcheck($forum[private], $forum[userlist]);
	if($authorization == "true" || $status == 'Super Administrator') {
		if((($thread[password] == $_COOKIE["fidpw".$thread[fid]]) || $thread[password] == "") || $status == 'Super Administrator') {

// Patched to correct issues with anonymous paosters

			if($thread[author] == "Anonymous") {
				$authorlink = "$lang_textanonymous";
			}else {
				$authorlink = "<a href=\"member.php?action=viewpro&member=".rawurlencode($thread[author])."\">$thread[author]</a>";
			}


			$lastpost = explode("|", $thread[lastpost]);
			$dalast = $lastpost[0];


			if($lastpost[1] == "Anonymous") {
				$lastpost[1] = "$lang_textanonymous";
			} else {
				$lastpost[1] = "<a href=\"member.php?action=viewpro&member=".rawurlencode($lastpost[1])."\">$lastpost[1]</a>";
			}


			$lastreplydate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));
			$lastreplytime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));
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
				$folder = "<img src=\"$imgdir/hot_red_folder.gif\" />";
			}elseif($lastvisit2 < $dalast) {
				$folder = "<img src=\"$imgdir/red_folder.gif\" />";
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
					$pagelinks .= " .. <a href=\"viewthread.php?tid=$thread[tid]&page=$topicpages\">$topicpages</a>";
				}
			
				$multipage2 = "(<small>Pages: $pagelinks</small>)";
				$pagelinks = '';

			} else {
				$multipage2 = '';

			}

// Patch to correct Censor problem
			$thread[subject] = stripslashes(censor($thread[subject]));



			eval("\$today2 = \"".template("today2")."\";");
			$today2 = stripslashes($today2);
			echo $today2;

		}
	}
}

if($threadcount == "0"){
	eval("\$today3 = \"".template("today3")."\";");
	$today3 = stripslashes($today3);
	echo $today3;
}

echo "</table></td></tr></table>";

end_time();


eval("\$footer = \"".template("footer")."\";");
echo $footer;
?>