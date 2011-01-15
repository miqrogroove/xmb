<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

// Fetch global stuff
	require "./header.php";

// Pre-define a few variables
	$navigation = "&raquo; Stats";
	$restrict = 'WHERE';

// Pre-load templates (saves queries)
	loadtemplates('header,feature_statistics,footer');

// Show header
	eval("\$header = \"".template("header")."\";");
	echo $header;

// Patch for Censoring properly
	smcwcache();


// Create the query for 
switch($status){
	case 'member';
		$restrict .= " f.private !='3' AND";

	case 'Moderator';

	case 'Super Moderator';
		$restrict .= " f.private != '2' AND";
		
	case 'Administrator';
		$restrict .= " f.userlist = '' AND";

	case 'Super Administrator';
		break;
}

// Get total amount of threads
	$query 		= $db->query("SELECT COUNT(*) FROM $table_threads");
	$threads 	= $db->result($query, 0);
// Get total amount of posts
	$query 		= $db->query("SELECT COUNT(*) FROM $table_posts");
	$posts 		= $db->result($query, 0);

// Get total amount of forums
	$query 		= $db->query("SELECT COUNT(*) FROM $table_forums WHERE type='forum'");
	$forums 	= $db->result($query, 0);

// Get total amount of forums that are ON
	$query 		= $db->query("SELECT COUNT(*) FROM $table_forums WHERE type='forum' AND status='on'");
	$forumsa 	= $db->result($query, 0);

// Get total amount of members
	$query 		= $db->query("SELECT COUNT(*) FROM $table_members");
	$members 	= $db->result($query, 0);

// Get total amount of members that actually posted...
	$query 		= $db->query("SELECT COUNT(*) FROM $table_members WHERE postnum!='0'");
	$membersact 	= $db->result($query, 0);

// In case any of these is 0, the stats will show wrong info, take care of that
	if($posts == 0 || $members == 0 || $threads == 0 || $forums == 0){
		$warning = $lang_stats_incomplete;
	
		eval("\$stats = \"".template("feature_statistics")."\";");
		$stats = stripslashes($stats);
		echo $stats;

		end_time();

		eval("\$footer = \"".template("footer")."\";");
		echo $footer;

		exit();
	}

// Check the percentage of members that posted against the amount of members that didn't post
	$mapercent  	= number_format(($membersact*100/$members), 2).'%';

// Get top 5 most viewed threads
	$query 		= $db->query("SELECT t.views, t.tid, t.subject FROM $table_threads t, $table_forums f $addon $restrict f.fid = t.fid ORDER BY views DESC LIMIT 0,5");
	while($views = $db->fetch_array($query)) {
		$views_subject 	 = stripslashes(censor($views[subject]));
		$viewmost 	.= "<a href=\"viewthread.php?tid=$views[tid]\">$views_subject</a> ($views[views] $lang_viewsl)<br />";
	}

// Get top 5 most replied to threads
	$query = $db->query("SELECT t.replies, t.tid, t.subject FROM $table_threads t, $table_forums f $addon $restrict f.fid = t.fid ORDER BY replies DESC LIMIT 0,5");
	while($reply = $db->fetch_array($query)) {
		$reply_subject 	 = stripslashes(censor($reply[subject]));
        	$replymost 	.= "<a href=\"viewthread.php?tid=$reply[tid]\">$reply_subject</a> ($reply[replies]$lang_repliesl)<br />";
	}

// Get last 5 posts
	$query = $db->query("SELECT t.lastpost, t.tid, t.subject FROM $table_threads t, $table_forums f $addon $restrict f.fid = t.fid ORDER BY lastpost DESC LIMIT 0,5");
	while($last = $db->fetch_array($query)) {
		$lpdate 	 = gmdate("$dateformat", $last[lastpost] + ($timeoffset * 3600) + ($addtime * 3600));
		$lptime 	 = gmdate("$timecode", $last[lastpost] + ($timeoffset * 3600) + ($addtime * 3600));
		$thislast 	 = "$lang_lpoststats $lang_lastreply1 $lpdate $lang_textat $lptime";
		$last_subject 	 = stripslashes(censor($last[subject]));
		$latest 	.= "<a href=\"viewthread.php?tid=$last[tid]\">$last_subject</a> ($thislast)<br/>";
	}

// Get most popular forum
	$query 		= $db->query("SELECT f.posts, f.threads, f.fid, f.name FROM $table_forums f $restrict f.fid = f.fid ORDER BY posts DESC LIMIT 0, 1");
	$pop 		= $db->fetch_array($query);
	$popforum 	= "<a href=\"forumdisplay.php?fid=$pop[fid]\"><b>$pop[name]</b></a>";

// Get amount of posts per user
	$mempost 	= 0;
	$query 		= $db->query("SELECT SUM(postnum) FROM $table_members");
	$mempost 	= number_format(($db->result($query, 0) / $members), 2);

// Get amount of posts per forum
	$forumpost 	= 0;
	$query 		= $db->query("SELECT SUM(posts) FROM $table_forums");
	$forumpost 	= number_format(($db->result($query, 0) / $forums), 2);

// Get amount of posts per thread
	$threadreply 	= 0;
	$query 		= $db->query("SELECT SUM(replies) FROM $table_threads");
	$threadreply 	= number_format(($db->result($query, 0) / $threads), 2);

// Get amount of posts per day
	$query 		= $db->query("SELECT lastpost FROM $table_threads ORDER BY lastpost LIMIT 0, 1");
	$postsday 	= number_format(($posts / ((time() - $db->result($query, 0)) / 86400)), 2);

// Get amount of registrations per day
	$query 		= $db->query("SELECT regdate FROM $table_members ORDER BY regdate LIMIT 0, 1");
	$membersday 	= number_format(($members / ((time() - $db->result($query, 0)) / 86400)), 2);

// Get best member
	$timesearch = time() - 86400;
	$eval = $lang_evalnobestmember;
	
	$query = $db->query("SELECT author, Count(*) AS Total FROM $table_posts WHERE dateline >= '$timesearch' GROUP BY author ORDER BY Total DESC");
	$info = $db->fetch_array($query);
	
	$bestmember = $info['author'];
	if($bestmember == '') {
		$bestmember = 'Nobody';
		$bestmemberpost = 'No';
	}else{
		if($info['Total'] != 0){
			if ($bestmember == "Anonymous"){
				$membesthtml = "$lang_textanonymous";
			} else {
				$membesthtml = "<a href=\"member.php?action=viewpro&member=".rawurlencode($bestmember)."\"><b>$bestmember</b></a>";
			}
			$bestmemberpost = $info['Total'];
			$eval = $lang_evalbestmember;
		}

	}
	
// eval, and show it all
	eval($eval);

	eval($lang_evalstats1);
	eval($lang_evalstats2);
	eval($lang_evalstats3);
	eval($lang_evalstats4);
	eval($lang_evalstats5);
	eval($lang_evalstats6);
	eval($lang_evalstats7);
	eval($lang_evalstats8);
	eval($lang_evalstats9);
	eval($lang_evalstats10);
	eval($lang_evalstats11);
	eval($lang_evalstats12);
	eval($lang_evalstats13);
	eval($lang_evalstats14);
	eval($lang_evalstats15);

	eval("\$stats = \"".template("feature_statistics")."\";");
	echo stripslashes($stats);

// Create footer, and end page
	end_time();

	eval("\$footer = \"".template("footer")."\";");
	echo $footer;
	exit();
?> 