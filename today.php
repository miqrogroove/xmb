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

$core = \XMB\Services\core();
$db = \XMB\Services\db();
$forums = \XMB\Services\forums();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;
$SETTINGS = &$vars->settings;

$core->nav($lang['navtodaysposts']);

if ($SETTINGS['todaysposts'] == 'off') {
    header('HTTP/1.0 403 Forbidden');
    $core->error($lang['fnasorry3']);
}

$header = $template->process('header.php');

$template->daysold = max(1, getInt('daysold', 'r'));
$srchfrom = $vars->onlinetime - (86400 * $template->daysold);

$tids = [];
$afids = $core->permittedFIDsForThreadView();
$fids = implode(',', $afids);

if (count($afids) == 0) {
    $threadcount = 0;
} else {
    $threadcount = $sql->countThreadsByFIDs($afids, $srchfrom);
}

$template->multipage = '';

if ($threadcount == 0) {
    $template->noPostsMessage = ($template->daysold == 1) ? $lang['nopoststoday'] : $lang['noPostsTimePeriod'];
    $template->rows = $template->process('today_noposts.php');
} else {
    if ($template->daysold == 1) {
        $mpage = $core->multipage($threadcount, $vars->tpp, $vars->full_url . 'today.php');
    } else {
        $mpage = $core->multipage($threadcount, $vars->tpp, $vars->full_url . 'today.php?daysold=' . $template->daysold);
    }
    if (strlen($mpage['html']) != 0) {
        $template->pagenav = $mpage['html'];
        $template->multipage = $template->process('today_multipage.php');
    }

    $t_extension = get_extension($lang['toppedprefix']);
    switch ($t_extension) {
        case 'gif':
        case 'jpg':
        case 'jpeg':
        case 'png':
            $lang['toppedprefix'] = '<img src="' . $vars->full_url . $vars->theme['imgdir'] . '/' . $lang['toppedprefix'] . '" alt="' . $lang['toppedpost'] . '" border="0" />';
            break;
    }

    $p_extension = get_extension($lang['pollprefix']);
    switch ($p_extension) {
        case 'gif':
        case 'jpg':
        case 'jpeg':
        case 'png':
            $lang['pollprefix'] = '<img src="' . $vars->full_url . $vars->theme['imgdir'] . '/' . $lang['pollprefix'] . '" alt="' . $lang['postpoll'] . '" border="0" />';
            break;
    }

    $query = $db->query(
        "SELECT t.*, t.replies+1 as posts, m.uid
         FROM " . $vars->tablepre . "threads t
         LEFT JOIN " . $vars->tablepre . "members AS m ON t.author = m.username
         WHERE t.lastpost > '$srchfrom' AND t.fid IN ($fids)
         ORDER BY t.lastpost DESC
         LIMIT {$mpage['start']}, " . $vars->tpp
    );
    
    $threadsInFid = [];

    if ($SETTINGS['dotfolders'] == 'on' && X_MEMBER && (int) $vars->self['postnum'] > 0) {
        while ($thread = $db->fetch_array($query)) {
            $threadsInFid[] = $thread['tid'];
        }
        $db->data_seek($query, 0);

        $threadsInFid = implode(',', $threadsInFid);
        $queryfids = $db->query("SELECT tid FROM " . $vars->tablepre . "posts WHERE tid IN ($threadsInFid) AND author = '" . $vars->xmbuser . "' GROUP BY tid");

        $threadsInFid = [];
        while ($row = $db->fetch_array($queryfids)) {
            $threadsInFid[] = $row['tid'];
        }
        $db->free_result($queryfids);
    }

    $today_row = [];
    while ($thread = $db->fetch_array($query)) {
        $thread['subject'] = shortenString($core->rawHTMLsubject(stripslashes($thread['subject'])));
        $forum = $forums->getForum((int) $thread['fid']);
        $thread['name'] = fnameOut($forum['name']);

        if ($thread['author'] == 'Anonymous') {
            $template->authorlink = $lang['textanonymous'];
        } elseif (is_null($thread['uid'])) {
            $template->authorlink = $thread['author'];
        } else {
            $template->authorlink = '<a href="' . $vars->full_url . 'member.php?action=viewpro&amp;member=' . recodeOut($thread['author']) . '">' . $thread['author'] . '</a>';
        }

        $lastpost = explode('|', $thread['lastpost']);
        $dalast = $lastpost[0];
        $lastPid = $lastpost[2];

        // Translate "Anonymous" author.
        $lastpostname = trim($lastpost[1]);
        if ('Anonymous' == $lastpostname) {
            $lastpostname = $lang['textanonymous'];
        }

        $lastreplydate = gmdate($vars->dateformat, $core->timeKludge((int) $lastpost[0]));
        $lastreplytime = gmdate($vars->timecode, $core->timeKludge((int) $lastpost[0]));
        $template->lastpost = "$lastreplydate {$lang['textat']} $lastreplytime<br />{$lang['textby']} $lastpostname";

        if ($thread['icon'] != '' && file_exists(XMB_ROOT . $vars->theme['smdir'] . '/' . $thread['icon'])) {
            $thread['icon'] = '<img src="' . $vars->full_url . $vars->theme['smdir'] . '/' . $thread['icon'] . '" alt="' . $thread['icon'] . '" border="0" />';
        } else {
            $thread['icon'] = '';
        }

        if ($thread['closed'] == 'yes') {
            $template->folder = '<img src="' . $vars->full_url . $vars->theme['imgdir'] . '/lock_folder.gif" alt="' . $lang['altclosedtopic'] . '" border="0" />';
        } else {
            if ((int) $thread['replies'] >= (int) $SETTINGS['hottopic']) {
                $folder = 'hot_folder.gif';
            } else {
                $folder = 'folder.gif';
            }

            $oT = strpos($vars->oldtopics, "|$lastPid|");
            if ($vars->lastvisit < (int) $dalast && $oT === false) {
                if ((int) $thread['replies'] >= (int) $SETTINGS['hottopic']) {
                    $folder = 'hot_red_folder.gif';
                } else {
                    $folder = 'red_folder.gif';
                }
            }

            if ($SETTINGS['dotfolders'] == 'on' && X_MEMBER && (count($threadsInFid) > 0) && in_array($thread['tid'], $threadsInFid)) {
                $folder = 'dot_'.$folder;
            }

            $template->folder = '<img src="' . $vars->full_url . $vars->theme['imgdir'] . '/' . $folder . '" alt="' . $lang['altfolder'] . '" border="0" />';

            $moved = explode('|', $thread['closed']);
            if ($moved[0] == 'moved') {
                continue;
            }
        }
        
        $template->tid = $thread['tid'];
        $template->lastpostrow = $template->process('forumdisplay_thread_lastpost.php');

        $template->prefix = '';

        if ('1' === $thread['pollopts']) {
            $template->prefix = $lang['pollprefix'] . ' ';
        }

        if ('1' === $thread['topped']) {
            $template->prefix = $lang['toppedprefix'] . ' ' . $template->prefix;
        }

        $multipage2 = '';
        $template->thread = $thread;

        $today_row[] = $template->process('today_row.php');
    }
    $template->rows = implode("\n", $today_row);
    $db->free_result($query);
}

$todaypage = $template->process('today.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $todaypage, $footer;
