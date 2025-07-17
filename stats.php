<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

require './header.php';

$core = Services\core();
$db = Services\db();
$forums = Services\forums();
$template = Services\template();
$vars = Services\vars();
$lang = &$vars->lang;
$SETTINGS = &$vars->settings;

$core->nav($lang['altstats']);

if ($SETTINGS['stats'] == 'off') {
    header('HTTP/1.0 403 Forbidden');
    $core->error($lang['fnasorry3']);
}

$core->setCanonicalLink($vars->full_url . 'stats.php');
$header = $template->process('header.php');

$fids = implode(',', $core->permittedFIDsForThreadView());
if (strlen($fids) == 0) {
    $restrict = ' FALSE';
} else {
    $restrict = " fid IN ($fids)";
}

$query = $db->query("SELECT COUNT(*) FROM " . $vars->tablepre . "members UNION ALL SELECT COUNT(*) FROM " . $vars->tablepre . "threads UNION ALL SELECT COUNT(*) FROM " . $vars->tablepre . "posts");
$members = (int) $db->result($query, 0);
$threads = (int) $db->result($query, 1);
$posts = (int) $db->result($query, 2);
$db->free_result($query);

$query = $db->query("SELECT MIN(regdate) FROM " . $vars->tablepre . "members");
$first_date = (int) $db->result($query);  // If no aggregate rows, result of MIN() will be null and cast to zero.  Resolves ugly old error checking methods.
$db->free_result($query);

if ($first_date <= 0) {
    $days = 0;
} else {
    $days = ($vars->onlinetime - $first_date) / 86400;
}

if ($days > 0) {
    $membersday = number_format(($members / $days), 2);
} else {
    $membersday = number_format(0, 2);
}

// Get total amount of forums that are ON
$forumList = $forums->forumCache();
$types = array_column($forumList, 'type');
$counts = array_count_values($types);
$forumsa = $counts['forum'] ?? 0;

// Get total amount of members that actually posted...
$query = $db->query("SELECT COUNT(postnum) FROM " . $vars->tablepre . "members WHERE postnum > 0");
$membersact = $db->result($query);
$db->free_result($query);

// In case any of these is 0, the stats will show wrong info, take care of that
if ($posts == 0 || $members == 0 || $threads == 0 || $forumsa == 0 || $days < 1) {
    $core->message($lang['stats_incomplete']);
}

// Get amount of posts per user
$mempost = number_format($posts / $members, 2);

// Get amount of posts per forum
$forumpost = number_format($posts / $forumsa, 2);

// Get amount of replies per thread
$threadreply = number_format(($posts - $threads) / $threads, 2);

// Check the percentage of members that posted against the amount of members that didn't post
$mapercent  = number_format(($membersact*100/$members), 2).'%';

// Get top 5 most viewed threads
$viewmost = [];
$query = $db->query("SELECT views, tid, subject FROM " . $vars->tablepre . "threads WHERE $restrict ORDER BY views DESC LIMIT 5");
while ($views = $db->fetch_array($query)) {
    $views['subject'] = shortenString($core->rawHTMLsubject($views['subject']));
    $viewmost[] = '<a href="' . $vars->full_url . 'viewthread.php?tid=' . intval($views['tid']) . '">' . $views['subject'] . '</a> (' . $views['views'] . ')';
}
$viewmost = implode('<br />', $viewmost);
$db->free_result($query);

// Get top 5 most replied to threads
$replymost = [];
$query = $db->query("SELECT replies, tid, subject FROM " . $vars->tablepre . "threads WHERE $restrict ORDER BY replies DESC LIMIT 5");
while ($reply = $db->fetch_array($query)) {
    $reply['subject'] = shortenString($core->rawHTMLsubject($reply['subject']));
    $replymost[] = '<a href="' . $vars->full_url . 'viewthread.php?tid='.intval($reply['tid']).'">'.$reply['subject'].'</a> ('.$reply['replies'].')';
}
$replymost = implode('<br />', $replymost);
$db->free_result($query);

// Get last 5 posts
$latest = array();
$query = $db->query("SELECT lastpost, tid, subject FROM " . $vars->tablepre . "threads WHERE $restrict ORDER BY lastpost DESC LIMIT 5");
while ($last = $db->fetch_array($query)) {
    $adjStamp = $core->timeKludge((int) $last['lastpost']);
    $lpdate = $core->printGmDate($adjStamp);
    $lptime = gmdate($vars->timecode, $adjStamp);
    $thislast = $lang['lpoststats'].' '.$lang['lastreply1'].' '.$lpdate.' '.$lang['textat'].' '.$lptime;
    $last['subject'] = shortenString($core->rawHTMLsubject($last['subject']));
    $latest[] = '<a href="' . $vars->full_url . 'viewthread.php?tid=' . intval($last['tid']) . '">' . $last['subject'] . '</a> (' . $thislast . ')';
}
$latest = implode('<br />', $latest);
$db->free_result($query);

// Get most popular forum
if (strlen($fids) == 0) {
    $popforum = $lang['textnoforumsexist'];
} else {
    $query = $db->query("SELECT posts, threads, fid, name FROM " . $vars->tablepre . "forums WHERE $restrict AND (type='sub' OR type='forum') AND status='on' ORDER BY posts DESC LIMIT 0, 1");
    $pop = $db->fetch_array($query);
    $popforum = '<a href="' . $vars->full_url . 'forumdisplay.php?fid=' . intval($pop['fid']) . '"><strong>' . fnameOut($pop['name']) . '</strong></a>';
    $db->free_result($query);
}

// Get amount of posts per day
$postsday = number_format($posts / $days, 2);

// Get best member
$timesearch = $vars->onlinetime - 86400;

$query = $db->query("SELECT author, COUNT(author) AS Total FROM " . $vars->tablepre . "posts WHERE dateline >= '$timesearch' GROUP BY author ORDER BY Total DESC LIMIT 1");

if ($db->num_rows($query) == 0) {
    $template->bestmember = $lang['evalnobestmember'];
} else {
    $info = $db->fetch_array($query);
    $bestmember = $info['author'];
    $membesthtml = '<a href="' . $vars->full_url . 'member.php?action=viewpro&amp;member=' . recodeOut($bestmember) . '"><strong>' . $bestmember . '</strong></a>';
    $bestmemberpost = $info['Total'];
    $search  = [ '$membesthtml', '$bestmemberpost' ];
    $replace = [  $membesthtml,   $bestmemberpost  ];
    $template->bestmember = str_replace($search, $replace, $lang['evalbestmember']);
}
$db->free_result($query);

$template->stats1 = str_replace('$bbname', $SETTINGS['bbname'], $lang['evalstats1']);
$template->stats2 = str_replace('$posts', (string) $posts, $lang['evalstats2']);
$template->stats3 = str_replace('$threads', (string) $threads, $lang['evalstats3']);

$search  = [ '$forums' ];
$replace = [  $forumsa  ];
$template->stats4 = str_replace($search, $replace, $lang['evalstats4']);

$template->stats5 = str_replace('$members', (string) $members, $lang['evalstats5']);
$template->stats6 = str_replace('$viewmost', $viewmost, $lang['evalstats6']);
$template->stats7 = str_replace('$replymost', $replymost, $lang['evalstats7']);

$search  = [ '$popforum', '$pop[posts]', '$pop[threads]'  ];
$replace = [  $popforum,   $pop['posts'], $pop['threads'] ];
$template->stats8 = str_replace($search, $replace, $lang['evalstats8']);

$template->stats9 = str_replace('$mempost', $mempost, $lang['evalstats9']);
$template->stats10 = str_replace('$forumpost', $forumpost, $lang['evalstats10']);
$template->stats11 = str_replace('$threadreply', $threadreply, $lang['evalstats11']);
$template->stats12 = str_replace('$postsday', $postsday, $lang['evalstats12']);
$template->stats13 = str_replace('$membersday', $membersday, $lang['evalstats13']);
$template->stats14 = str_replace('$latest', $latest, $lang['evalstats14']);
$template->stats15 = str_replace('$mapercent', $mapercent, $lang['evalstats15']);

$statspage = $template->process('feature_statistics.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $statspage, $footer;
