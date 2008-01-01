<?php
/* $Id: stats.php,v 1.3.2.8 2006/03/04 17:33:44 Jamie Exp $ */
/*
    XMB 1.9.2
    © 2001 - 2005 Aventure Media & The XMB Development Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Fetch global stuff
    require "./header.php";

// Pre-define a few variables
    nav($lang['altstats']);

// Pre-load templates (saves queries)
    loadtemplates('footer_load', 'footer_querynum', 'footer_phpsql', 'footer_totaltime', 'feature_statistics');
    smcwcache();
    eval("\$css = \"".template("css")."\";");

// Show header
    eval("echo (\"".template('header')."\");");

// Check if stats is on/off
    if ($stats == 'off') {
        error($lang['fnasorry3'], false);
    }

// Create the query for
	$modXmbuser = str_replace(array('*', '.', '+'), array('\*', '\.', '\+'), $self['username']);
	$restrict = array("(f.password='')"); // prevents empty restrictions, also make sure password protected forums aren't shown (unless opened further down)
	switch($self['status']) {
		case 'Member':
			$restrict[] = 'f.private = 1';
			$restrict[] = "(f.userlist = '' OR f.userlist REGEXP '(^|(,))([:space:])*$modXmbuser([:space:])*((,)|$)')";
			break;

		case 'Moderator':
		case 'Super Moderator':
			$restrict[] = '(f.private = 1 OR f.private = 3)';
			$restrict[] = "(if ((f.private=1 AND f.userlist != ''), if ((f.userlist REGEXP '(^|(,))([:space:])*$modXmbuser([:space:])*((,)|$)'), 1, 0), 1))";
			break;

		case 'Administrator':
			$restrict[] = '(f.private > 0 AND f.private < 4)';
			$restrict[] = "(if ((f.private=1 AND f.userlist != ''), if ((f.userlist REGEXP '(^|(,))([:space:])*$modXmbuser([:space:])*((,)|$)'), 1, 0), 1))";
			break;

		case 'Super Administrator':
			break;

		default:
			$restrict[] = '(f.private=1)';
			$restrict[] = "(f.userlist='')";
			break;
	}

	$fids = array();
	if (!X_SADMIN) {
		$q = $db->query("SELECT fid FROM $table_forums f WHERE f.status = 'on' AND ".implode(' AND ', $restrict));
		while($f = $db->fetch_array($q)) {
			$fids[] = $f['fid'];
		}

		if ( X_MEMBER ) {
			// let's add fids for passworded forums that the user can access
			$r2 = array();
			foreach ($_COOKIE as $key=>$val) {
				if (preg_match('#^fidpw([0-9]+)$#', $key, $fetch)) {
			            $r2[] = '(fid="' . $fetch[1] . '" AND password="' . addslashes($val) . '")';
				}
			}
			if (count($r2) > 0) {
				$r = implode(' OR ', $r2);
				$q = $db->query("SELECT fid FROM $table_forums WHERE $r");
				while($f = $db->fetch_array($q)) {
					$fids[] = $f['fid'];
				}
			}
		}
	}

	if(X_SADMIN) {
		$restrict = '(1=1)';
	} else {
		$fids = implode(',', $fids);
		$restrict = 'fid IN('.$fids.')';
	}

	// Get total amount of threads
    $query      = $db->query("SELECT COUNT(tid) FROM $table_threads");
    $threads    = $db->result($query, 0);
    $db->free_result($query);

	// Get total amount of posts
    $query      = $db->query("SELECT COUNT(pid) FROM $table_posts");
    $posts      = $db->result($query, 0);
    $db->free_result($query);

	// Get total amount of members
    $query      = $db->query("SELECT COUNT(postnum) FROM $table_members");
    $members    = $db->result($query, 0);
    $db->free_result($query);

// Get amount of registrations per day
    $query      = $db->query("SELECT regdate FROM $table_members ORDER BY regdate LIMIT 0, 1");
    $days       = (time() - @$db->result($query, 0)) / 86400;
    if ($days > 0) {
        $membersday = number_format(($members / $days), 2);
    } else {
        $membersday = number_format(0, 2);
    }
	$db->free_result($query);


// Get total amount of forums
    $query      = $db->query("SELECT COUNT(fid) FROM $table_forums WHERE type='forum'");
    $forums     = $db->result($query, 0);
    $db->free_result($query);

// Get total amount of forums that are ON
    $query      = $db->query("SELECT COUNT(fid) FROM $table_forums WHERE type='forum' AND status='on'");
    $forumsa    = $db->result($query, 0);
    $db->free_result($query);

// Get total amount of members that actually posted...
    $query      = $db->query("SELECT COUNT(postnum) FROM $table_members WHERE postnum > '0'");
    $membersact     = $db->result($query, 0);
    $db->free_result($query);


// In case any of these is 0, the stats will show wrong info, take care of that
    if ($posts == 0 || $members == 0 || $threads == 0 || $forums == 0 || $days < 1) {
        error($lang['stats_incomplete'], false);
    }


	// Get amount of posts per user
    $mempost    = 0;
    $query      = $db->query("SELECT SUM(postnum) FROM $table_members");
    $mempost    = number_format(($db->result($query, 0) / $members), 2);
    $db->free_result($query);

// Get amount of posts per forum
    $forumpost  = 0;
    $query      = $db->query("SELECT SUM(posts) FROM $table_forums");
    $forumpost  = number_format(($db->result($query, 0) / $forums), 2);
    $db->free_result($query);

// Get amount of posts per thread
    $threadreply    = 0;
    $query      = $db->query("SELECT SUM(replies) FROM $table_threads");
    $threadreply    = number_format(($db->result($query, 0) / $threads), 2);
    $db->free_result($query);

// Check the percentage of members that posted against the amount of members that didn't post
    $mapercent  = number_format(($membersact*100/$members), 2).'%';

// Get top 5 most viewed threads
    $viewmost = '';
    $query      = $db->query("SELECT views, tid, subject FROM $table_threads WHERE $restrict GROUP BY tid ORDER BY views DESC LIMIT 5");
    while ($views = $db->fetch_array($query)) {
        $views_subject   = stripslashes(censor($views['subject']));
        $viewmost   .= "<a href=\"viewthread.php?tid=$views[tid]\">$views_subject</a> ($views[views])<br />";
    }

// Get top 5 most replied to threads
    $replymost = '';
    $query = $db->query("SELECT replies, tid, subject FROM $table_threads WHERE $restrict GROUP BY tid ORDER BY replies DESC LIMIT 5");
    while ($reply = $db->fetch_array($query)) {
        $reply_subject   = stripslashes(censor($reply['subject']));
        $replymost  .= "<a href=\"viewthread.php?tid=$reply[tid]\">$reply_subject</a> ($reply[replies])<br />";
    }

// Get last 5 posts
    $latest = '';
    $query = $db->query("SELECT lastpost, tid, subject FROM $table_threads WHERE $restrict GROUP BY tid ORDER BY lastpost DESC LIMIT 5");
    $adjTime = ($timeoffset * 3600) + ($addtime * 3600);
    while ($last = $db->fetch_array($query)) {
        $lpdate      = gmdate("$dateformat", $last['lastpost'] + $adjTime);
        $lptime      = gmdate("$timecode", $last['lastpost'] + $adjTime);
        $thislast    = "$lang[lpoststats] $lang[lastreply1] $lpdate $lang[textat] $lptime";
        $last_subject= stripslashes(censor($last['subject']));
        $latest     .= "<a href=\"viewthread.php?tid=$last[tid]\">$last_subject</a> ($thislast)<br/>";
    }

// Get most popular forum
    $query      = $db->query("SELECT posts, threads, fid, name FROM $table_forums WHERE $restrict AND type='sub' OR type='forum' ORDER BY posts DESC LIMIT 0, 1");
    $pop        = $db->fetch_array($query);
    $popforum   = "<a href=\"forumdisplay.php?fid=$pop[fid]\"><b>$pop[name]</b></a>";

// Get amount of posts per day
    $postsday   = number_format($posts / $days, 2);

// Get best member
    $timesearch = time() - 86400;
    $eval = $lang['evalnobestmember'];

    $query = $db->query("SELECT author, Count(author) AS Total FROM $table_posts WHERE dateline >= '$timesearch' GROUP BY author ORDER BY Total DESC LIMIT 1");
    $info = $db->fetch_array($query);

    $bestmember = $info['author'];
    if ($bestmember == '') {
        $bestmember = 'Nobody';
        $bestmemberpost = 'No';
    } else {
        if ($info['Total'] != 0) {
            $membesthtml = "<a href=\"member.php?action=viewpro&amp;member=".rawurlencode($bestmember)."\"><b>$bestmember</b></a>";
            $bestmemberpost = $info['Total'];
            $eval = $lang['evalbestmember'];
        }
    }

// eval, and show it all
    eval($eval);

    eval($lang['evalstats1']);
    eval($lang['evalstats2']);
    eval($lang['evalstats3']);
    eval($lang['evalstats4']);
    eval($lang['evalstats5']);
    eval($lang['evalstats6']);
    eval($lang['evalstats7']);
    eval($lang['evalstats8']);
    eval($lang['evalstats9']);
    eval($lang['evalstats10']);
    eval($lang['evalstats11']);
    eval($lang['evalstats12']);
    eval($lang['evalstats13']);
    eval($lang['evalstats14']);
    eval($lang['evalstats15']);

    eval('echo stripslashes("'.template('feature_statistics').'");');
// Create footer, and end page
    end_time();
    eval("echo (\"".template('footer')."\");");
?>