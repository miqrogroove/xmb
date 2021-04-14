<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2021, The XMB Group
 * https://www.xmbforum2.com/
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 **/

define('X_SCRIPT', 'stats.php');

require 'header.php';

nav($lang['altstats']);

loadtemplates('feature_statistics');

smcwcache();

if ($SETTINGS['stats'] == 'off') {
    header('HTTP/1.0 403 Forbidden');
    error($lang['fnasorry3'], TRUE);
}

setCanonicalLink('stats.php');
eval('$header = "'.template('header').'";');

$fids = permittedForums(forumCache(), 'thread', 'csv');
if (strlen($fids) == 0) {
    $restrict = ' FALSE';
} else {
    $restrict = ' fid IN ('.$fids.')';
}

$query = $db->query("SELECT COUNT(*) FROM ".X_PREFIX."members UNION ALL SELECT COUNT(*) FROM ".X_PREFIX."threads UNION ALL SELECT COUNT(*) FROM ".X_PREFIX."posts");
$members = (int) $db->result($query, 0);
$threads = (int) $db->result($query, 1);
$posts = (int) $db->result($query, 2);
$db->free_result($query);

$query = $db->query("SELECT MIN(regdate) FROM ".X_PREFIX."members");
$first_date = (int) $db->result( $query, 0 );  // If no aggregate rows, result of MIN() will be null and cast to zero.  Resolves ugly old error checking methods.
$db->free_result($query);

if ( $first_date <= 0 ) {
    $days = 0;
} else {
    $days = ( $onlinetime - $first_date ) / 86400;
}

if ($days > 0) {
    $membersday = number_format(($members / $days), 2);
} else {
    $membersday = number_format(0, 2);
}

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
    message($lang['stats_incomplete']);
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
$adjTime = ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600);
while($last = $db->fetch_array($query)) {
    $last['lastpost'] = (int) $last['lastpost'];
    $lpdate = gmdate($dateformat, $last['lastpost'] + $adjTime);
    $lptime = gmdate($timecode, $last['lastpost'] + $adjTime);
    $thislast = $lang['lpoststats'].' '.$lang['lastreply1'].' '.$lpdate.' '.$lang['textat'].' '.$lptime;
    $last['subject'] = shortenString(rawHTMLsubject(stripslashes($last['subject'])), 125, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...');
    $latest[] = '<a href="viewthread.php?tid='.intval($last['tid']).'">'.$last['subject'].'</a> ('.$thislast.')';
}
$latest = implode('<br />', $latest);
$db->free_result($query);

// Get most popular forum
if (strlen($fids) == 0) {
    $popforum = $lang['textnoforumsexist'];
} else {
    $query = $db->query("SELECT posts, threads, fid, name FROM ".X_PREFIX."forums WHERE $restrict AND (type='sub' OR type='forum') AND status='on' ORDER BY posts DESC LIMIT 0, 1");
    $pop = $db->fetch_array($query);
    $popforum = '<a href="forumdisplay.php?fid='.intval($pop['fid']).'"><strong>'.fnameOut($pop['name']).'</strong></a>';
    $db->free_result($query);
}

// Get amount of posts per day
$postsday = number_format($posts / $days, 2);

// Get best member
$timesearch = $onlinetime - 86400;

$query = $db->query("SELECT author, COUNT(author) AS Total FROM ".X_PREFIX."posts WHERE dateline >= '$timesearch' GROUP BY author ORDER BY Total DESC LIMIT 1");

if ( $db->num_rows( $query ) == 0 ) {
    $bestmember = $lang['evalnobestmember'];
} else {
    $info = $db->fetch_array($query);
    $bestmember = $info['author'];
    $membesthtml = '<a href="member.php?action=viewpro&amp;member='.recodeOut($bestmember).'"><strong>'.$bestmember.'</strong></a>';
    $bestmemberpost = $info['Total'];
    $search  = [ '$membesthtml', '$bestmemberpost' ];
    $replace = [  $membesthtml,   $bestmemberpost  ];
    $bestmember = str_replace( $search, $replace, $lang['evalbestmember'] );
}
$db->free_result($query);

$stats1 = str_replace( '$bbname', $bbname, $lang['evalstats1'] );
$stats2 = str_replace( '$posts', $posts, $lang['evalstats2'] );
$stats3 = str_replace( '$threads', $threads, $lang['evalstats3'] );

$search  = [ '$forumsa', '$forums' ];
$replace = [  $forumsa,   $forums  ];
$stats4 = str_replace( $search, $replace, $lang['evalstats4'] );

$stats5 = str_replace( '$members', $members, $lang['evalstats5'] );
$stats6 = str_replace( '$viewmost', $viewmost, $lang['evalstats6'] );
$stats7 = str_replace( '$replymost', $replymost, $lang['evalstats7'] );

$search  = [ '$popforum', '$pop[posts]', '$pop[threads]'  ];
$replace = [  $popforum,   $pop['posts'], $pop['threads'] ];
$stats8 = str_replace( $search, $replace, $lang['evalstats8'] );

$stats9 = str_replace( '$mempost', $mempost, $lang['evalstats9'] );
$stats10 = str_replace( '$forumpost', $forumpost, $lang['evalstats10'] );
$stats11 = str_replace( '$threadreply', $threadreply, $lang['evalstats11'] );
$stats12 = str_replace( '$postsday', $postsday, $lang['evalstats12'] );
$stats13 = str_replace( '$membersday', $membersday, $lang['evalstats13'] );
$stats14 = str_replace( '$latest', $latest, $lang['evalstats14'] );
$stats15 = str_replace( '$mapercent', $mapercent, $lang['evalstats15'] );

eval('$statspage = "'.template('feature_statistics').'";');

end_time();
eval('$footer = "'.template('footer').'";');
echo $header, $statspage, $footer;
?>
