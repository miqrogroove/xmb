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

define('X_SCRIPT', 'today.php');

require 'header.php';

loadtemplates(
'forumdisplay_thread_lastpost',
'today',
'today_noposts',
'today_row',
'today_multipage'
);

smcwcache();

nav($lang['navtodaysposts']);

if ($SETTINGS['todaysposts'] == 'off') {
    header('HTTP/1.0 403 Forbidden');
    error($lang['fnasorry3'], TRUE);
}

$daysold = getInt('daysold', 'r');
if ($daysold < 1) {
    $daysold = 1;
}
$srchfrom = $onlinetime - (86400 * $daysold);

$tids = array();
$fids = permittedForums(forumCache(), 'thread', 'csv');

if (strlen($fids) == 0) {
    $threadcount = 0;
} else {
    $threadcount = (int) $db->result($db->query("SELECT COUNT(*) FROM ".X_PREFIX."threads WHERE lastpost > '$srchfrom' AND fid IN ($fids)"), 0);
}

if ($threadcount == 0) {
    eval('$header = "'.template('header').'";');
    $noPostsMessage = ($daysold == 1) ? $lang['nopoststoday'] : $lang['noPostsTimePeriod'];
    $multipage = '';
    eval('$rows = "'.template('today_noposts').'";');
} else {
    validateTpp();
    validatePpp();

    if ($daysold == 1) {
        $mpage = multipage($threadcount, $tpp, 'today.php');
    } else {
        $mpage = multipage($threadcount, $tpp, 'today.php?daysold='.$daysold);
    }
    $multipage =& $mpage['html'];
    if (strlen($mpage['html']) != 0) {
        eval('$multipage = "'.template('today_multipage').'";');
    }

    eval('$header = "'.template('header').'";');

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

    $query = $db->query(
        "SELECT t.*, t.replies+1 as posts, m.uid, r.uid AS lastauthor
         FROM ".X_PREFIX."threads t
         LEFT JOIN ".X_PREFIX."members AS m ON t.author = m.username
         LEFT JOIN ".X_PREFIX."members AS r ON SUBSTRING_INDEX(SUBSTRING_INDEX(t.lastpost, '|', 2), '|', -1) = r.username
         WHERE t.lastpost > '$srchfrom' AND t.fid IN ($fids)
         ORDER BY t.lastpost DESC
         LIMIT {$mpage['start']}, $tpp"
    );
    
    $threadsInFid = array();

    if ( $SETTINGS['dotfolders'] == 'on' && X_MEMBER && (int) $self['postnum'] > 0 ) {
        while($thread = $db->fetch_array($query)) {
            $threadsInFid[] = $thread['tid'];
        }
        $db->data_seek($query, 0);

        $threadsInFid = implode(',', $threadsInFid);
        $queryfids = $db->query("SELECT tid FROM ".X_PREFIX."posts WHERE tid IN ($threadsInFid) AND author='$xmbuser' GROUP BY tid");

        $threadsInFid = array();
        while($row = $db->fetch_array($queryfids)) {
            $threadsInFid[] = $row['tid'];
        }
        $db->free_result($queryfids);
    }

    $today_row = array();
    $tmOffset = ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600);
    while($thread = $db->fetch_array($query)) {
        $thread['subject'] = shortenString(rawHTMLsubject(stripslashes($thread['subject'])), 125, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...');
        $forum = getForum($thread['fid']);
        $thread['name'] = fnameOut($forum['name']);

        if ($thread['author'] == 'Anonymous') {
            $authorlink = $lang['textanonymous'];
        } elseif (is_null($thread['uid'])) {
            $authorlink = $thread['author'];
        } else {
            $authorlink = '<a href="member.php?action=viewpro&amp;member='.recodeOut($thread['author']).'">'.$thread['author'].'</a>';
        }

        $lastpost = explode('|', $thread['lastpost']);
        $dalast = $lastpost[0];
        $lastPid = $lastpost[2];

        // Translate "Anonymous" author.
        $lastpostname = trim( $lastpost[1] );
        if ( 'Anonymous' == $lastpostname ) {
            $lastpostname = $lang['textanonymous'];
        }

        $lastreplydate = gmdate($dateformat, $lastpost[0] + $tmOffset);
        $lastreplytime = gmdate($timecode, $lastpost[0] + $tmOffset);
        $lastpost = "$lastreplydate {$lang['textat']} $lastreplytime<br />{$lang['textby']} $lastpostname";

        if ($thread['icon'] != '' && file_exists($smdir.'/'.$thread['icon'])) {
            $thread['icon'] = '<img src="'.$smdir.'/'.$thread['icon'].'" alt="'.$thread['icon'].'" border="0" />';
        } else {
            $thread['icon'] = '';
        }

        if ($thread['closed'] == 'yes') {
            $folder = '<img src="'.$imgdir.'/lock_folder.gif" alt="'.$lang['altclosedtopic'].'" border="0" />';
        } else {
            if ( (int) $thread['replies'] >= (int) $SETTINGS['hottopic'] ) {
                $folder = 'hot_folder.gif';
            } else {
                $folder = 'folder.gif';
            }

            $oT = strpos( $oldtopics, "|$lastPid|" );
            if ( $lastvisit < (int) $dalast && $oT === false ) {
                if ( (int) $thread['replies'] >= (int) $SETTINGS['hottopic'] ) {
                    $folder = 'hot_red_folder.gif';
                } else {
                    $folder = 'red_folder.gif';
                }
            }

            if ($SETTINGS['dotfolders'] == 'on' && X_MEMBER && (count($threadsInFid) > 0) && in_array($thread['tid'], $threadsInFid)) {
                $folder = 'dot_'.$folder;
            }

            $folder = '<img src="'.$imgdir.'/'.$folder.'" alt="'.$lang['altfolder'].'" border="0" />';

            $moved = explode('|', $thread['closed']);
            if ($moved[0] == 'moved') {
                continue;
            }
        }

        $prefix = '';
        eval('$lastpostrow = "'.template('forumdisplay_thread_lastpost').'";');

        if ( '1' === $thread['pollopts'] ) {
            $prefix = $lang['pollprefix'].' ';
        }

        if ( '1' === $thread['topped'] ) {
            $prefix = $lang['toppedprefix'].' '.$prefix;
        }

        $multipage2 = '';

        eval('$today_row[] = "'.template('today_row').'";');
    }
    $rows = implode("\n", $today_row);
    $db->free_result($query);
}

eval('$todaypage = "'.template('today').'";');

end_time();
eval('$footer = "'.template('footer').'";');
echo $header, $todaypage, $footer;
?>
