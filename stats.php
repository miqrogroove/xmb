<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Alpha Zero - This software should not be used for any purpose after 31 August 2008.
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2008, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * Copyright (c) 2007, iEntry, Inc.
 * http://www.ientry.com
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 **/

define('X_SCRIPT', 'stats.php');

require 'header.php';

nav($lang['altstats']);

loadtemplates('feature_statistics');

smcwcache();

eval('$css = "'.template('css').'";');

eval('echo "'.template('header').'";');

if ($SETTINGS['stats'] == 'off') {
    error($lang['fnasorry3'], false);
}

if (X_SADMIN) {
    $q = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE status = 'on'");
    while($f = $db->fetch_array($q)) {
        $fids[] = $f['fid'];
    }
} else {
    $fCache = array();
    $q = $db->query("SELECT fid, postperm, userlist, password, type, fup FROM ".X_PREFIX."forums WHERE status = 'on' AND type != 'group' ORDER BY type ASC");
    while($forum = $db->fetch_array($q)) {
        $perms = checkForumPermissions($forum);
        $fCache[$forum['fid']] = $perms;

        if ($perms[X_PERMS_VIEW] && $perms[X_PERMS_USERLIST] && $perms[X_PERMS_PASSWORD]) {
            if ($forum['type'] == 'sub') {
                // also check above forum!
                $parentP = $fCache[$forum['fup']];
                if ($parentP[X_PERMS_VIEW] && $parentP[X_PERMS_USERLIST] && $parentP[X_PERMS_PASSWORD]) {
                    $fids[] = $forum['fid'];
                }
            } else {
                $fids[] = $forum['fid'];
            }
        }
    }
}

$fids = implode(',', $fids);
$restrict = ' fid IN ('.$fids.')';

$query = $db->query("SELECT COUNT(uid) FROM ".X_PREFIX."members UNION ALL SELECT COUNT(tid) FROM ".X_PREFIX."threads UNION ALL SELECT COUNT(pid) FROM ".X_PREFIX."posts");
$members = $db->result($query, 0);
if ($members == false) {
    $members = 0;
}

$threads = $db->result($query, 1);
if ($threads == false) {
    $threads = 0;
}

$posts = $db->result($query, 2);
if ($posts == false) {
    $posts = 0;
}
$db->free_result($query);

$query = $db->query("SELECT regdate FROM ".X_PREFIX."members ORDER BY regdate LIMIT 0, 1");
$days = ($onlinetime - @$db->result($query, 0)) / 86400;
if ($days > 0) {
    $membersday = number_format(($members / $days), 2);
} else {
    $membersday = number_format(0, 2);
}
$db->free_result($query);

// Get total amount of forums
$query = $db->query("SELECT COUNT(fid) FROM ".X_PREFIX."forums WHERE type='forum'");
$forums = $db->result($query, 0);
$db->free_result($query);

// Get total amount of forums that are ON
$query = $db->query("SELECT COUNT(fid) FROM ".X_PREFIX."forums WHERE type='forum' AND status='on'");
$forumsa = $db->result($query, 0);
$db->free_result($query);

// Get total amount of members that actually posted...
$query = $db->query("SELECT COUNT(postnum) FROM ".X_PREFIX."members WHERE postnum > '0'");
$membersact = $db->result($query, 0);
$db->free_result($query);

// In case any of these is 0, the stats will show wrong info, take care of that
if ($posts == 0 || $members == 0 || $threads == 0 || $forums == 0 || $days < 1) {
    message($lang['stats_incomplete'], false);
}

// Get amount of posts per user
$mempost = 0;
$query = $db->query("SELECT SUM(postnum) FROM ".X_PREFIX."members");
$mempost = number_format(($db->result($query, 0) / $members), 2);
$db->free_result($query);

// Get amount of posts per forum
$forumpost = 0;
$query = $db->query("SELECT SUM(posts) FROM ".X_PREFIX."forums");
$forumpost = number_format(($db->result($query, 0) / $forums), 2);
$db->free_result($query);

// Get amount of posts per thread
$threadreply = 0;
$query = $db->query("SELECT SUM(replies) FROM ".X_PREFIX."threads");
$threadreply = number_format(($db->result($query, 0) / $threads), 2);
$db->free_result($query);

// Check the percentage of members that posted against the amount of members that didn't post
$mapercent  = number_format(($membersact*100/$members), 2).'%';

// Get top 5 most viewed threads
$viewmost = array();
$query = $db->query("SELECT views, tid, subject FROM ".X_PREFIX."threads WHERE $restrict ORDER BY views DESC LIMIT 5");
while($views = $db->fetch_array($query)) {
    $views['subject'] = shortenString(rawHTMLsubject(stripslashes($views['subject'])), 125, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...');
    $viewmost[] = '<a href="viewthread.php?tid='.intval($views['tid']).'">'.$views['subject'].'</a> ('.$views['views'].')';
}
$viewmost = implode('<br />', $viewmost);
$db->free_result($query);

// Get top 5 most replied to threads
$replymost = array();
$query = $db->query("SELECT replies, tid, subject FROM ".X_PREFIX."threads WHERE $restrict ORDER BY replies DESC LIMIT 5");
while($reply = $db->fetch_array($query)) {
    $reply['subject'] = shortenString(rawHTMLsubject(stripslashes($reply['subject'])), 125, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...');
    $replymost[] = '<a href="viewthread.php?tid='.intval($reply['tid']).'">'.$reply['subject'].'</a> ('.$reply['replies'].')';
}
$replymost = implode('<br />', $replymost);
$db->free_result($query);

// Get last 5 posts
$latest = array();
$query = $db->query("SELECT lastpost, tid, subject FROM ".X_PREFIX."threads WHERE $restrict ORDER BY lastpost DESC LIMIT 5");
$adjTime = ($timeoffset * 3600) + ($addtime * 3600);
while($last = $db->fetch_array($query)) {
    $lpdate = gmdate($dateformat, $last['lastpost'] + $adjTime);
    $lptime = gmdate($timecode, $last['lastpost'] + $adjTime);
    $thislast = $lang['lpoststats'].' '.$lang['lastreply1'].' '.$lpdate.' '.$lang['textat'].' '.$lptime;
    $last['subject'] = shortenString(rawHTMLsubject(stripslashes($last['subject'])), 125, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...');
    $latest[] = '<a href="viewthread.php?tid='.intval($last['tid']).'">'.$last['subject'].'</a> ('.$thislast.')';
}
$latest = implode('<br />', $latest);
$db->free_result($query);

// Get most popular forum
$query = $db->query("SELECT posts, threads, fid, name FROM ".X_PREFIX."forums WHERE $restrict AND (type='sub' OR type='forum') AND status='on' ORDER BY posts DESC LIMIT 0, 1");
$pop = $db->fetch_array($query);
$popforum = '<a href="forumdisplay.php?fid='.intval($pop['fid']).'"><strong>'.fnameOut($pop['name']).'</strong></a>';
$db->free_result($query);

// Get amount of posts per day
$postsday = number_format($posts / $days, 2);

// Get best member
$timesearch = $onlinetime - 86400;
$eval = $lang['evalnobestmember'];

$query = $db->query("SELECT author, COUNT(author) AS Total FROM ".X_PREFIX."posts WHERE dateline >= '$timesearch' GROUP BY author ORDER BY Total DESC LIMIT 1");
$info = $db->fetch_array($query);

$bestmember = $info['author'];
if ($bestmember == '') {
    $bestmember = 'Nobody';
    $bestmemberpost = 'No';
} else {
    if ($info['Total'] != 0) {
        $membesthtml = '<a href="member.php?action=viewpro&amp;member='.recodeOut($bestmember).'"><strong>'.$bestmember.'</strong></a>';
        $bestmemberpost = $info['Total'];
        $eval = $lang['evalbestmember'];
    }
}
$db->free_result($query);

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

eval('echo "'.template('feature_statistics').'";');

end_time();
eval('echo "'.template('footer').'";');
?>
