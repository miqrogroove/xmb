<?php
/* $Id: today.php,v 1.9.2.20 2004/09/24 19:10:32 Tularis Exp $ */
/*
    XMB 1.9
    © 2001 - 2004 Aventure Media & The XMB Development Team
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

loadtemplates('footer_load', 'footer_querynum', 'footer_phpsql', 'footer_totaltime', 'today', 'today2', 'header', 'footer', 'css', 'today_multipage');
smcwcache();

nav($lang['alttodayposts']);

eval('$css = "'.template('css').'";');
eval('echo "'.template('header').'";');

// Check if todays posts is on/off
if($todaysposts == 'off') {
    error($lang['fnasorry3'], false);
}

$srchfrom = $onlinetime - 86400;

$restrict = array('(1=1)'); // prevents empty restrictions
switch($self['status']) {
    case 'Member':
        $restrict[] = 'private = 1';
        $restrict[] = "(userlist = '' OR userlist REGEXP '(^|(,))( )*$xmbuser( )*((,)|$)')";
        break;

    case 'Moderator':
    case 'Super Moderator':
        $restrict[] = '(private = 1 OR private = 3)';
        $restrict[] = "(if((private=1 AND userlist != ''), if((userlist REGEXP '(^|(,))( )*$xmbuser( )*((,)|$)'), 1, 0), 1))";
        break;
    
    case 'Administrator':
        $restrict[] = '(private > 0 AND private < 4)';
        $restrict[] = "(if((private=1 AND userlist != ''), if((userlist REGEXP '(^|(,))( )*$xmbuser( )*((,)|$)'), 1, 0), 1))";
        break;
    
    case 'Super Administrator':
        break;
    
    default:
        $restrict[] = '(private=1)';
        $restrict[] = "(userlist='')";
        break;
}
$restrict = implode(' AND ', $restrict);

$fids = array();
if($self['status'] == 'Super Administrator') {
    $q = $db->query("SELECT fid FROM $table_forums WHERE status = 'on'");
    while($f = $db->fetch_array($q)) {
        $fids[] = $f['fid'];
    }
} else {
    $q = $db->query("SELECT fid FROM $table_forums WHERE status = 'on' AND $restrict");
    while($f = $db->fetch_array($q)) {
        $fids[] = $f['fid'];
    }
    
    if($xmbuser != '') {
        // let's add fids for passworded forums that the user can access
        $r2 = array();
        foreach($_COOKIE as $key=>$val) {
            if(preg_match('#^fidpw([0-9]+)$#', $key, $fetch)) {
                $r2[] = "(fid='$fetch[1]' AND password='$val')";
            }
        }
        if(count($r2) > 0) {
            $r = implode(' OR ', $r2);
            $q = $db->query("SELECT fid FROM $table_forums WHERE $r");
            while($f = $db->fetch_array($q)) {
                $fids[] = $f['fid'];
            }
        }
    }
}
if(count($fids) == 0) {
    error($lang['nopoststoday'], false);
}

$fids = implode(', ', $fids);

$results = $db->result($db->query("SELECT count(tid) FROM $table_threads WHERE lastpost >= '$srchfrom' AND fid IN($fids)"), 0);

if ($results == 0) {
    error($lang['nopoststoday'], false);
}

if (!isset($tpp) || $tpp < 1) {
    $tpp = $topicperpage;
}

if (!isset($ppp) || $ppp < 1) {
    $ppp = $postperpage;
}

$mpurl = "today.php";
$page = (isset($page) && is_numeric($page)) ? ((int) $page) : 1;
if ($page < 1) {
    $page = 1;
}
$start_limit = ($page > 0) ? (($page-1) * $tpp) : 0;

if(($multipage = multi($results, $tpp, $page, $mpurl)) !== false) {
    eval("\$multipage = \"".template('today_multipage')."\";");
} else {
    $multipage = '';
}
$query = $db->query("SELECT count(p.pid) as posts, t.tid, t.subject, t.author, t.lastpost, t.icon, t.replies, t.views, t.closed, f.fid, f.name FROM $table_threads t, $table_forums f, $table_posts p WHERE f.fid IN ($fids) AND t.fid=f.fid AND t.lastpost >= '$srchfrom' AND p.tid=t.tid GROUP BY t.tid ORDER BY t.lastpost DESC LIMIT $start_limit, $tpp");
$today2 = array();

$tmOffset = ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600);

while($thread = $db->fetch_array($query)) {
    $thread['subject']  = stripslashes($thread['subject']);
    $forum['name']      = $thread['name'];

    if($thread['author'] == $lang['textanonymous']) {
        $authorlink = $thread['author'];
    } else {
        $authorlink = "<a href=\"member.php?action=viewpro&amp;member=".rawurlencode($thread['author'])."\">$thread[author]</a>";
    }

    $lastpost = explode("|", $thread['lastpost']);
    $dalast = $lastpost[0];

    if($lastpost[1] != $lang['textanonymous']) {
        $lastpost[1] = '<a href="member.php?action=viewpro&amp;member='.rawurlencode($lastpost[1]).'">'.$lastpost[1].'</a>';
    }

    $lastreplydate = gmdate($dateformat, $lastpost[0] + $tmOffset);
    $lastreplytime = gmdate($timecode, $lastpost[0] + $tmOffset);
    $lastpost = $lang['lastreply1'].' '.$lastreplydate.' '.$lang['textat'].' '.$lastreplytime.'<br />'.$lang['textby'].' '.$lastpost[1];

    if($thread['icon'] != "") {
        $thread['icon'] = '<img src="'.$imgdir.'/'.$thread['icon'].'" />';
    } else {
        $thread['icon'] = "&nbsp;";
    }

    if($thread['replies'] >= $SETTINGS['hottopic']) {
        $folder = '<img src="'.$imgdir.'/hot_folder.gif" alt="'.$lang['althotfolder'].'" />';
    } else {
        $folder = '<img src="'.$imgdir.'/folder.gif" alt="'.$lang['altfolder'].'" />';
    }

    if($thread['replies'] >= $SETTINGS['hottopic'] && $lastvisit2 < $dalast) {
        $folder = '<img src="'.$imgdir.'/hot_red_folder.gif" alt="'.$lang['althotredfolder'].'" />';
    }elseif($lastvisit2 < $dalast) {
        $folder = '<img src="'.$imgdir.'/red_folder.gif" alt="'.$lang['altredfolder'].'" />';
    }
    
    if ($thread['closed'] == "yes") {
        $folder = '<img src="'.$imgdir.'/lock_folder.gif" alt="'.$lang['altclosedtopic'].'" />';
        $prefix = '';
    } else {
        $moved = explode('|', $thread['closed']);
        if($moved[0] == 'moved') {
            $prefix = $lang['moved'].' ';
            $thread['tid'] = $moved[1];
            $thread['replies'] = '-';
            $thread['views'] = '-';
            $folder = '<img src="'.$imgdir.'/lock_folder.gif" alt="'.$lang['altclosedtopic'].'" />';
        }
    }
    
    if($thread['posts']  > $ppp) {
        $pagelinks = multi($thread['posts'], $ppp, 0, 'viewthread.php?tid='.$thread['tid']);
        $multipage2 = '(<small>'.$pagelinks.'</small>)';
    } else {
        $pagelinks = '';
        $multipage2 = '';
    }
    
    $thread['subject'] = censor($thread['subject']);
    $thread['subject'] = stripslashes($thread['subject']);

    eval('$today2[] = "'.template('today2').'";');
}

$rows = implode("\n", $today2);
eval('echo stripslashes("'.template('today').'");');

end_time();
eval('echo "'.template('footer').'";');
?>