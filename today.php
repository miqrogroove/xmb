<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Beta 3 - This software should not be used for any purpose after 1 February 2009.
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

define('X_SCRIPT', 'today.php');

require 'header.php';

loadtemplates(
'today',
'today_noposts',
'today_row',
'today_multipage'
);

smcwcache();

nav($lang['navtodaysposts']);

eval('$css = "'.template('css').'";');
eval('$header = "'.template('header').'";');

if ($SETTINGS['todaysposts'] == 'off') {
    header('HTTP/1.0 403 Forbidden');
    error($lang['fnasorry3']);
}

$daysold = (isset($daysold) && is_numeric($daysold) ? (int) $daysold : 1);
$srchfrom = $onlinetime - (86400 * $daysold);

$tids = array();
$fids = permittedForums(forumCache(), 'thread', 'csv');

$query = $db->query("SELECT tid FROM ".X_PREFIX."threads WHERE lastpost >= '$srchfrom' AND fid IN ($fids)");
$results = $db->num_rows($query);
while($t = $db->fetch_array($query)) {
    $tids[] = $t['tid'];
}
$db->free_result($query);
$tids = implode(', ', $tids);

if ($results == 0) {
    $noPostsMessage = ($daysold == 1) ? $lang['nopoststoday'] : $lang['noPostsTimePeriod'];
    $multipage = '';
    eval('$rows = "'.template('today_noposts').'";');
} else {
    $t_extension = get_extension($lang['toppedprefix']);
    switch($t_extension) {
        case 'gif':
        case 'jpg':
        case 'jpeg':
        case 'png':
            $lang['toppedprefix'] = '<img src="'.$imgdir.'/'.$lang['toppedprefix'].'" alt="'.$lang['toppedpost'].'" border="0" />';
            break;
    }

    $p_extension = get_extension($lang['pollprefix']);
    switch($p_extension) {
        case 'gif':
        case 'jpg':
        case 'jpeg':
        case 'png':
            $lang['pollprefix'] = '<img src="'.$imgdir.'/'.$lang['pollprefix'].'" alt="'.$lang['postpoll'].'" border="0" />';
            break;
    }

    validateTpp();
    validatePpp();

    $max_page = (int) ($results / $tpp) + 1;
    $page = (isset($page) && is_numeric($page) && $page >= 1 && $page <= $max_page) ? ($page < 1 ? 1 : ((int) $page)) : 1;
    $start_limit = ($page > 1) ? (($page-1) * $tpp) : 0;

    $mpurl = 'today.php?daysold='.$daysold;
    $multipage = '';
    if (($multipage = multi($results, $tpp, $page, $mpurl)) !== false) {
        eval('$multipage = "'.template('today_multipage').'";');
    }

    $query = $db->query("SELECT t.replies+1 as posts, t.tid, t.subject, t.author, t.lastpost, t.icon, t.replies, t.views, t.closed, t.topped, t.pollopts, f.fid, f.name FROM ".X_PREFIX."threads t LEFT JOIN ".X_PREFIX."forums f ON (f.fid=t.fid) WHERE t.tid IN ($tids) ORDER BY t.lastpost DESC LIMIT $start_limit, $tpp");
    $today_row = array();
    $tmOffset = ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600);
    while($thread = $db->fetch_array($query)) {
        $thread['subject'] = shortenString(rawHTMLsubject(stripslashes($thread['subject'])), 125, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...');
        $thread['name'] = fnameOut($thread['name']);

        if ($thread['author'] == 'Anonymous') {
            $authorlink = $lang['textanonymous'];
        } else {
            $authorlink = '<a href="member.php?action=viewpro&amp;member='.recodeOut($thread['author']).'">'.$thread['author'].'</a>';

        }

        $lastpost = explode('|', $thread['lastpost']);
        $dalast = $lastpost[0];
        $lastPid = $lastpost[2];

        if ($lastpost[1] != 'Anonymous') {
            $lastpost[1] = '<a href="member.php?action=viewpro&amp;member='.recodeOut($lastpost[1]).'">'.$lastpost[1].'</a>';
        }

        $lastreplydate = gmdate($dateformat, $lastpost[0] + $tmOffset);
        $lastreplytime = gmdate($timecode, $lastpost[0] + $tmOffset);
        $lastpost = $lang['lastreply1'].' '.$lastreplydate.' '.$lang['textat'].' '.$lastreplytime.'<br />'.$lang['textby'].' '.$lastpost[1];

        if ($thread['icon'] != '' && file_exists($smdir.'/'.$thread['icon'])) {
            $thread['icon'] = '<img src="'.$smdir.'/'.$thread['icon'].'" alt="'.$thread['icon'].'" border="0" />';
        } else {
            $thread['icon'] = '';
        }

        if ($thread['replies'] >= $SETTINGS['hottopic']) {
            $folder = '<img src="'.$imgdir.'/hot_folder.gif" alt="'.$lang['althotfolder'].'" border="0" />';
        } else {
            $folder = '<img src="'.$imgdir.'/folder.gif" alt="'.$lang['altfolder'].'" border="0" />';
        }

        $oldtopics = isset($oldtopics) ? $oldtopics : '';
        if (($oT = strpos($oldtopics, '|'.$lastPid.'|')) === false && $thread['replies'] >= $SETTINGS['hottopic'] && $lastvisit < $dalast) {
            $folder = '<img src="'.$imgdir.'/hot_red_folder.gif" alt="'.$lang['althotredfolder'].'" border="0" />';
        } else if ($lastvisit < $dalast && $oT === false) {
            $folder = '<img src="'.$imgdir.'/red_folder.gif" alt="'.$lang['altredfolder'].'" border="0" />';
        }

        if ($thread['closed'] == 'yes') {
            $folder = '<img src="'.$imgdir.'/lock_folder.gif" alt="'.$lang['altclosedtopic'].'" border="0" />';
            $prefix = '';
        } else {
            $moved = explode('|', $thread['closed']);
            if ($moved[0] == 'moved') {
                continue;
            }
        }

        $prefix = '';
        if ($thread['pollopts'] == 1) {
            $prefix = $lang['pollprefix'].' ';
        }

        if ($thread['topped'] == 1) {
            $prefix = $lang['toppedprefix'].' '.$prefix;
        }

        if ($thread['posts'] > $ppp) {
            $pagelinks = multi($thread['posts'], $ppp, 0, 'viewthread.php?tid='.$thread['tid']);
            $multipage2 = '(<small>'.$pagelinks.'</small>)';
        } else {
            $pagelinks = $multipage2 = '';
        }

        eval('$today_row[] = "'.template('today_row').'";');
    }
    $rows = implode("\n", $today_row);
    $db->free_result($query);
}

eval('$todaypage = "'.template('today').'";');

end_time();
eval('$footer = "'.template('footer').'";');
echo $header.$todaypage.$footer;
?>
