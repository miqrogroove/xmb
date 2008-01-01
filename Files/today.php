<?php
/* $Id: today.php,v 1.3.2.20 2006/09/21 22:25:00 Tularis Exp $ */
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

require './header.php';

loadtemplates('footer_load', 'footer_querynum', 'footer_phpsql', 'footer_totaltime', 'today', 'today2', 'today_multipage');
smcwcache();

nav($lang['alttodayposts']);

eval('$css = "'.template('css').'";');
eval('echo "'.template('header').'";');

// Check if todays posts is on/off
if ( $todaysposts == 'off') {
    error($lang['fnasorry3'], false);
}

$srchfrom = $onlinetime - 86400;

$tids = array();
$fids = array();
if (X_SADMIN) {
    $q = $db->query("SELECT fid FROM $table_forums WHERE status = 'on'");
    while($f = $db->fetch_array($q)) {
        $fids[] = $f['fid'];
    }
} else {
    $fCache = array();
    $q = $db->query("SELECT fid, postperm, userlist, password, type, fup FROM $table_forums WHERE status = 'on' AND type != 'group' ORDER BY type ASC");
    while($forum = $db->fetch_array($q)) {
        $perms = checkForumPermissions($forum);
        $fCache[$forum['fid']] = $perms;

        if($perms[X_PERMS_VIEW] && $perms[X_PERMS_USERLIST] && $perms[X_PERMS_PASSWORD]) {
            if($forum['type'] == 'sub') {
                // also check above forum!
                $parentP = $fCache[$forum['fup']];
                if($parentP[X_PERMS_VIEW] && $parentP[X_PERMS_USERLIST] && $parentP[X_PERMS_PASSWORD]) {
                    $fids[] = $forum['fid'];
                }
            } else {
                $fids[] = $forum['fid'];
            }
        }
    }
}

if (count($fids) == 0) {
    error($lang['nopoststoday'], false);
}

$fids = implode(', ', $fids);

$query = $db->query("SELECT tid FROM $table_threads WHERE lastpost >= '$srchfrom' AND fid IN($fids)");
$results = $db->num_rows($query);
while($t = $db->fetch_array($query)) {
    $tids[] = $t['tid'];
}
$tids = implode(', ', $tids);

if ($results == 0) {
    error($lang['nopoststoday'], false);
}

validateTpp();
validatePpp();

$mpurl = "today.php";
$page = (isset($page) && is_numeric($page)) ? ((int) $page) : 1;
if ($page < 1) {
    $page = 1;
}
$start_limit = ($page > 0) ? (($page-1) * $tpp) : 0;

if (($multipage = multi($results, $tpp, $page, $mpurl)) !== false) {
    eval("\$multipage = \"".template('today_multipage')."\";");
} else {
    $multipage = '';
}
$query = $db->query("SELECT t.replies+1 as posts, t.tid, t.subject, t.author, t.lastpost, t.icon, t.replies, t.views, t.closed, f.fid, f.name FROM $table_threads t LEFT JOIN $table_forums f ON (f.fid=t.fid) WHERE t.tid IN ($tids) ORDER BY t.lastpost DESC LIMIT $start_limit, $tpp");
$today2 = array();

while($thread = $db->fetch_array($query)) {
    $thread['subject']  = shortenString(stripslashes($thread['subject']), 125, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...');
    $forum['name']      = $thread['name'];

    if ( $thread['author'] == 'Anonymous') {
        $authorlink = $thread['author'];
    } else {
        $authorlink = "<a href=\"member.php?action=viewpro&amp;member=".rawurlencode($thread['author'])."\">$thread[author]</a>";
    }

    $lastpost = explode("|", $thread['lastpost']);
    $dalast = $lastpost[0];
    $lastPid = $lastpost[2];

    if ( $lastpost[1] != $lang['textanonymous']) {
        $lastpost[1] = '<a href="member.php?action=viewpro&amp;member='.rawurlencode($lastpost[1]).'">'.$lastpost[1].'</a>';
    }

    $lastreplydate = printGmDate($lastpost[0]);
    $lastreplytime = printGmTime($lastpost[0]);
    $lastpost = $lang['lastreply1'].' '.$lastreplydate.' '.$lang['textat'].' '.$lastreplytime.'<br />'.$lang['textby'].' '.$lastpost[1];

    if ( $thread['icon'] != "") {
        $thread['icon'] = '<img src="'.$THEME['imgdir'].'/'.$thread['icon'].'" />';
    } else {
        $thread['icon'] = "&nbsp;";
    }

    if ( $thread['replies'] >= $SETTINGS['hottopic']) {
        $folder = '<img src="'.$THEME['imgdir'].'/hot_folder.gif" alt="'.$lang['althotfolder'].'" />';
    } else {
        $folder = '<img src="'.$THEME['imgdir'].'/folder.gif" alt="'.$lang['altfolder'].'" />';
    }

    $oldtopics = isset($oldtopics) ? $oldtopics : '';
    if (($oT = strpos($oldtopics, '|'.$lastPid.'|')) === false && $thread['replies'] >= $SETTINGS['hottopic'] && $lastvisit < $dalast) {
        $folder = '<img src="'.$THEME['imgdir'].'/hot_red_folder.gif" alt="'.$lang['althotredfolder'].'" />';
    }elseif ( $lastvisit < $dalast && $oT === false) {
        $folder = '<img src="'.$THEME['imgdir'].'/red_folder.gif" alt="'.$lang['altredfolder'].'" />';
    }

    if ($thread['closed'] == "yes") {
        $folder = '<img src="'.$THEME['imgdir'].'/lock_folder.gif" alt="'.$lang['altclosedtopic'].'" />';
        $prefix = '';
    } else {
        $moved = explode('|', $thread['closed']);
        if ( $moved[0] == 'moved') {
            continue;
        }
    }

    if ( $thread['posts']  > $self['ppp']) {
        $pagelinks = multi($thread['posts'], $self['ppp'], 0, 'viewthread.php?tid='.$thread['tid']);
        $multipage2 = '(<small>'.$pagelinks.'</small>)';
    } else {
        $pagelinks = '';
        $multipage2 = '';
    }

    $thread['subject'] = checkOutput($thread['subject'], 'no', '', true);
    $thread['subject'] = censor($thread['subject']);
    $thread['subject'] = addslashes($thread['subject']);

    eval('$today2[] = "'.template('today2').'";');
}

$rows = implode("\n", $today2);
eval('echo stripslashes("'.template('today').'");');

end_time();
eval('echo "'.template('footer').'";');
