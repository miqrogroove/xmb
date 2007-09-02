<?php
/**
 * XMB 1.9.8 Engage Final
 *
 * Developed By The XMB Group
 * Copyright (c) 2001-2007, The XMB Group
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 **/

require_once('header.php');

loadtemplates(
'today',
'today_noposts',
'today_row',
'today_multipage'
);

smcwcache();

nav($lang['alttodayposts']);

eval('$css = "'.template('css').'";');
eval('echo "'.template('header').'";');

if ($SETTINGS['todaysposts'] == 'off') {
    error($lang['fnasorry3'], false);
}

$daysold = (isset($daysold) && is_numeric($daysold) ? (int) $daysold : 1);
$srchfrom = $onlinetime - (86400 * $daysold);

$modXmbuser = str_replace(array('*', '.', '+'), array('\*', '\.', '\+'), $xmbuser);
$restrict = array("(password='')");
switch ($self['status']) {
    case 'Member':
        $restrict[] = 'private = 1';
        $restrict[] = "(userlist = '' OR userlist REGEXP '(^|(,))( )*$modXmbuser( )*((,)|$)')";
        break;
    case 'Moderator':
    case 'Super Moderator':
        $restrict[] = '(private = 1 OR private = 3)';
        $restrict[] = "(if ((private=1 AND userlist != ''), if ((userlist REGEXP '(^|(,))( )*$modXmbuser( )*((,)|$)'), 1, 0), 1))";
        break;
    case 'Administrator':
        $restrict[] = '(private > 0 AND private < 4)';
        $restrict[] = "(if ((private=1 AND userlist != ''), if ((userlist REGEXP '(^|(,))( )*$modXmbuser( )*((,)|$)'), 1, 0), 1))";
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
$tids = array();
if (X_SADMIN) {
    $q = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE status='on'");
    while ($f = $db->fetch_array($q)) {
        $fids[] = $f['fid'];
    }
    $db->free_result($q);
} else {
    $q = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE status='on' AND $restrict");
    while ($f = $db->fetch_array($q)) {
        $fids[] = $f['fid'];
    }
    $db->free_result($q);

    if (X_MEMBER) {
        // let's add fids for passworded forums that the user can access
        $r2 = array();
        foreach ($_COOKIE as $key=>$val) {
            if (preg_match('#^fidpw([0-9]+)$#', $key, $fetch)) {
                $r2[] = '(fid="' . $fetch[1] . '" AND password="'.addslashes($val).'")';
            }
        }

        if (count($r2) > 0) {
            $r = implode(' OR ', $r2);
            $q = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE $r");
            while ($f = $db->fetch_array($q)) {
                $fids[] = $f['fid'];
            }
            $db->free_result($q);
        }
    }
}

if (count($fids) == 0) {
    error($lang['nopoststoday'], false);
}

$fids = implode(', ', $fids);

$query = $db->query("SELECT tid FROM ".X_PREFIX."threads WHERE lastpost >= '$srchfrom' AND fid IN($fids)");
$results = $db->num_rows($query);
while ($t = $db->fetch_array($query)) {
    $tids[] = $t['tid'];
}
$db->free_result($query);
$tids = implode(', ', $tids);

if ($results == 0) {
	$noPostsMessage = ($daysold == 1) ? $lang["nopoststoday"] : $lang["noPostsTimePeriod"];
    $multipage = '';

	eval('$rows = "'.template('today_noposts').'";');
} else {
	validateTpp();
	validatePpp();

	$max_page = (int) ($results / $tpp) + 1;
	$page = (isset($page) && is_numeric($page) && $page <= $max_page) ? ($page < 1 ? 1 : ((int) $page)) : 1;
	$start_limit = ($page > 1) ? (($page-1) * $tpp) : 0;

	$mpurl = 'today.php?daysold='.$daysold;
	$multipage = '';
	if (($multipage = multi($results, $tpp, $page, $mpurl)) !== false) {
	    eval('$multipage = "'.template('today_multipage').'";');
	}

	$query = $db->query("SELECT t.replies+1 as posts, t.tid, t.subject, t.author, t.lastpost, t.icon, t.replies, t.views, t.closed, f.fid, f.name FROM ".X_PREFIX."threads t LEFT JOIN ".X_PREFIX."forums f ON (f.fid=t.fid) WHERE t.tid IN ($tids) ORDER BY t.lastpost DESC LIMIT $start_limit, $tpp");
	$today_row = array();
	$tmOffset = ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600);
	while ($thread = $db->fetch_array($query)) {
	    $thread['subject'] = shortenString(stripslashes($thread['subject']), 125, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...');
	    $thread['name'] = stripslashes($thread['name']);

	    if ($thread['author'] == $lang['textanonymous']) {
	        $authorlink = $thread['author'];
	    } else {
	        $authorlink = '<a href="member.php?action=viewpro&amp;member='.rawurlencode($thread['author']).'">'.$thread['author'].'</a>';
	    }

	    $lastpost = explode('|', $thread['lastpost']);
	    $dalast = $lastpost[0];
	    $lastPid = $lastpost[2];

	    if ($lastpost[1] != $lang['textanonymous']) {
	        $lastpost[1] = '<a href="member.php?action=viewpro&amp;member='.rawurlencode($lastpost[1]).'">'.$lastpost[1].'</a>';
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
	    } else if ( $lastvisit < $dalast && $oT === false) {
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

	    if ($thread['posts']  > $ppp) {
	        $pagelinks = multi($thread['posts'], $ppp, 0, 'viewthread.php?tid='.$thread['tid']);
	        $multipage2 = '(<small>'.$pagelinks.'</small>)';
	    } else {
	        $pagelinks = $multipage2 = '';
	    }

	    $thread['subject'] = checkOutput($thread['subject'], 'no', '', true);
	    $thread['subject'] = censor($thread['subject']);
	    $thread['subject'] = addslashes($thread['subject']);
        $thread['name'] = html_entity_decode($thread['name']);

	    eval('$today_row[] = "'.template('today_row').'";');
	}

	$rows = implode("\n", $today_row);
	$db->free_result($query);
}

eval('echo stripslashes("'.template('today').'");');

end_time();
eval('echo "'.template('footer').'";');
?>