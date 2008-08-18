<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Alpha One - This software should not be used for any purpose after 30 September 2008.
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

define('X_SCRIPT', 'forumdisplay.php');

require 'header.php';

loadtemplates(
'forumdisplay',
'forumdisplay_admin',
'forumdisplay_sortby',
'forumdisplay_invalidforum',
'forumdisplay_multipage',
'forumdisplay_multipage_admin',
'forumdisplay_newpoll',
'forumdisplay_newtopic',
'forumdisplay_nothreads',
'forumdisplay_password',
'forumdisplay_subforum',
'forumdisplay_subforum_lastpost',
'forumdisplay_subforum_nolastpost',
'forumdisplay_subforums',
'forumdisplay_thread',
'forumdisplay_thread_admin',
'forumdisplay_thread_lastpost'
);

smcwcache();

eval('$css = "'.template('css').'";');

$tid = getInt('tid');
$fid = getInt('fid');
$page = getInt('page');

$forum = getForum($fid);

if (($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on') {
    header('HTTP/1.0 404 Not Found');
    error($lang['textnoforum']);
}

$perms = checkForumPermissions($forum);
if (!$perms[X_PERMS_VIEW]) {
    error($lang['privforummsg']);
} else if (!$perms[X_PERMS_PASSWORD]) {
    handlePasswordDialog($fid);
}

$fup = array();
if ($forum['type'] == 'sub') {
    $fup = getForum($forum['fup']);
    // prevent access to subforum when upper forum can't be viewed.
    $fupPerms = checkForumPermissions($fup);
    if (!$fupPerms[X_PERMS_VIEW]) {
        error($lang['privforummsg']);
    } else if (!$fupPerms[X_PERMS_PASSWORD]) {
        handlePasswordDialog($fup['fid']);
    } else if ($fup['fup'] > 0) {
        $fupup = getForum($fup['fup']);
        nav('<a href="index.php?gid='.$fup['fup'].'">'.fnameOut($fupup['name']).'</a>');
        unset($fupup);
    }
    nav('<a href="forumdisplay.php?fid='.$fup['fid'].'">'.fnameOut($fup['name']).'</a>');
    unset($fup);
} else if ($forum['fup'] > 0) { // 'forum' in a 'group'
    $fup = getForum($forum['fup']);
    nav('<a href="index.php?gid='.$fup['fid'].'">'.fnameOut($fup['name']).'</a>');
    unset($fup);
}
nav(fnameOut($forum['name']));

eval('echo "'.template('header').'";');

if ($perms[X_PERMS_POLL]) {
    eval('$newpolllink = "'.template('forumdisplay_newpoll').'";');
} else {
    $newpolllink = '';
}

if ($perms[X_PERMS_THREAD]) {
    eval('$newtopiclink = "'.template('forumdisplay_newtopic').'";');
} else {
    $newtopiclink = '';
}

$index_subforums = array();
$subforums = '';
if ($forum['type'] == 'forum') {
    $forumlist = '';
    $permitted = permittedForums(forumCache(), 'forum');
    foreach($permitted as $sub) {
        if ($sub['type'] == 'sub' And $sub['fup'] == $fid) {
            $forumlist .= forum($sub, "forumdisplay_subforum", $index_subforums);
        }
    }
    if ($forumlist != '') {
        eval('$subforums .= "'.template('forumdisplay_subforums').'";');
    }
}

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

$max_page = (int) ($forum['threads'] / $tpp) + 1;
if ($page && $page >= 1 && $page <= $max_page) {
    $start_limit = ($page-1) * $tpp;
} else {
    $start_limit = 0;
    $page = 1;
}

$cusdate = formInt('cusdate');
if ($cusdate) {
    $cusdate = $onlinetime - $cusdate;
    $cusdate = "AND (substring_index(lastpost, '|',1)+1) >= '$cusdate'";
} else {
    $cusdate = '';
}

$ascdesc = postedVar('ascdesc', '', FALSE, FALSE);
if (strtolower($ascdesc) != 'asc') {
    $ascdesc = "desc";
}

$forumdisplay_thread = 'forumdisplay_thread';

$status1 = modcheck($self['username'], $forum['moderator']);

if ($status1 == 'Moderator') {
    $forumdisplay_thread = 'forumdisplay_thread_admin';
}

$topicsnum = 0;
$threadlist = '';
$threadsInFid = array();
if ($SETTINGS['dotfolders'] == 'on' && X_MEMBER) {
    $query = $db->query("SELECT tid FROM ".X_PREFIX."posts WHERE author='$xmbuser' AND fid='$fid'");
    while($row = $db->fetch_array($query)) {
        array_push($threadsInFid, $row['tid']);
    }
    $db->free_result($query);
}

$querytop = $db->query("SELECT t.* FROM ".X_PREFIX."threads t WHERE t.fid='$fid' $cusdate ORDER BY topped $ascdesc, lastpost $ascdesc LIMIT $start_limit, $tpp");
while($thread = $db->fetch_array($querytop)) {
    if ($thread['icon'] != '' && file_exists($smdir.'/'.$thread['icon'])) {
        $thread['icon'] = '<img src="'.$smdir.'/'.$thread['icon'].'" alt="'.$thread['icon'].'" border="0" />';
    } else {
        $thread['icon'] = '';
    }

    if ($thread['topped'] == 1) {
        $topimage = '<img src="./images/admin/untop.gif" alt="'.$lang['textuntopthread'].'" border="0" />';
    } else {
        $topimage = '<img src="./images/admin/top.gif" alt="'.$lang['alttopthread'].'" border="0" />';
    }

    $thread['subject'] = shortenString(rawHTMLsubject(stripslashes($thread['subject'])), 125, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...');

    if ($thread['author'] == $lang['textanonymous']) {
        $authorlink = $thread['author'];
    } else {
        $authorlink = '<a href="member.php?action=viewpro&amp;member='.recodeOut($thread['author']).'">'.$thread['author'].'</a>';
    }

    $prefix = '';

    $lastpost = explode('|', $thread['lastpost']);
    $dalast = trim($lastpost[0]);

    if ($lastpost[1] != $lang['textanonymous']) {
        $lastpost[1] = '<a href="member.php?action=viewpro&amp;member='.recodeOut(trim($lastpost[1])).'">'.trim($lastpost[1]).'</a>';
    } else {
        $lastpost[1] = $lang['textanonymous'];
    }

    $lastPid = isset($lastpost[2]) ? $lastpost[2] : 0;

    if ($thread['replies'] >= $SETTINGS['hottopic']) {
        $folder = 'hot_folder.gif';
    } else {
        $folder = 'folder.gif';
    }

    $oldtopics = isset($oldtopics) ? $oldtopics : '';

    if (($oT = strpos($oldtopics, '|'.$lastPid.'|')) === false && $thread['replies'] >= $SETTINGS['hottopic'] && $lastvisit < $dalast) {
        $folder = "hot_red_folder.gif";
    } else if ($lastvisit < $dalast && $oT === false) {
        $folder = "red_folder.gif";
    }

    if ($SETTINGS['dotfolders'] == 'on' && X_MEMBER && (count($threadsInFid) > 0) && in_array($thread['tid'], $threadsInFid)) {
        $folder = 'dot_'.$folder;
    }

    $folder = '<img src="'.$imgdir.'/'.$folder.'" alt="'.$lang['altfolder'].'" border="0" />';

    if ($thread['closed'] == 'yes') {
        $folder = '<img src="'.$imgdir.'/lock_folder.gif" alt="'.$lang['altclosedtopic'].'" border="0" />';
    }

    $lastreplydate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));
    $lastreplytime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));

    $lastpost = $lastreplydate.' '.$lang['textat'].' '.$lastreplytime.'<br />'.$lang['textby'].' '.$lastpost[1];

    $moved = explode('|', $thread['closed']);
    if ($moved[0] == 'moved') {
        $prefix = $lang['moved'].' ';
        $thread['realtid'] = $thread['tid'];
        $thread['tid'] = $moved[1];
        $thread['replies'] = "-";
        $thread['views'] = "-";
        $folder = '<img src="'.$imgdir.'/lock_folder.gif" alt="'.$lang['altclosedtopic'].'" border="0" />';
        $query = $db->query("SELECT COUNT(pid) FROM ".X_PREFIX."posts WHERE tid='$thread[tid]'");
        $postnum = 0;
        if ($query !== false) {
            $postnum = $db->result($query, 0);
        }
    } else {
        $thread['realtid'] = $thread['tid'];
    }

    eval('$lastpostrow = "'.template('forumdisplay_thread_lastpost').'";');

    if ($thread['pollopts'] == 1) {
        $prefix = $lang['pollprefix'].' ';
    }

    if ($thread['topped'] == 1) {
        $prefix = $lang['toppedprefix'].' '.$prefix;
    }

    $postnum = $thread['replies']+1;
    if ($postnum > $ppp) {
        $pagelinks = multi($postnum, $ppp, 0, 'viewthread.php?tid='.intval($thread['tid']));
        $multipage2 = '(<small>'.$pagelinks.'</small>)';
    } else {
        $pagelinks = $multipage2 = '';
    }

    eval('$threadlist .= "'.template($forumdisplay_thread).'";');

    $prefix = '';
    $topicsnum++;
}
$db->free_result($querytop);

if ($topicsnum == 0) {
    eval('$threadlist = "'.template('forumdisplay_nothreads').'";');
}

$check1 = $check5 = '';
$check15 = $check30 = '';
$check60 = $check100 = '';
$checkyear = $checkall = '';
switch($cusdate) {
    case 86400:
        $check1 = $selHTML;
        break;
    case 432000:
        $check5 = $selHTML;
        break;
    case 1296000:
        $check15 = $selHTML;
        break;
    case 2592000:
        $check30 = $selHTML;
        break;
    case 5184000:
        $check60 = $selHTML;
        break;
    case 8640000:
        $check100 = $selHTML;
        break;
    case 31536000:
        $checkyear = $selHTML;
        break;
    default:
        $checkall = $selHTML;
        break;
}

$query = $db->query("SELECT COUNT(tid) FROM ".X_PREFIX."threads WHERE fid='$fid'");
$topicsnum = $db->result($query, 0);
$db->free_result($query);

eval('$sortby = "'.template('forumdisplay_sortby').'";');

$mpurl = 'forumdisplay.php?fid='.$fid;
if (($multipage = multi($topicsnum, $tpp, $page, $mpurl)) === false) {
    $multipage = '';
} else {
    if ($status1 == 'Moderator') {
        eval('$multipage = "'.template('forumdisplay_multipage_admin').'";');
    } else {
        eval('$multipage = "'.template('forumdisplay_multipage').'";');
    }
}

if ($status1 == 'Moderator') {
    eval('echo "'.template('forumdisplay_admin').'";');
} else {
    eval('echo "'.template('forumdisplay').'";');
}

end_time();
eval('echo "'.template('footer').'";');
?>
