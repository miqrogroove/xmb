<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2012, The XMB Group
 * http://www.xmbforum2.com/
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
eval($lang['hottopiceval']);

$fid = getInt('fid');

$forum = getForum($fid);

if (($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on') {
    header('HTTP/1.0 404 Not Found');
    error($lang['textnoforum']);
}

$perms = checkForumPermissions($forum);
if (!$perms[X_PERMS_VIEW]) {
    if (X_GUEST) {
        redirect("{$full_url}misc.php?action=login", 0);
        exit;
    } else {
        error($lang['privforummsg']);
    }
} else if (!$perms[X_PERMS_PASSWORD]) {
    handlePasswordDialog($fid);
}

$fup = array();
if ($forum['type'] == 'sub') {
    $fup = getForum($forum['fup']);
    // prevent access to subforum when upper forum can't be viewed.
    $fupPerms = checkForumPermissions($fup);
    if (!$fupPerms[X_PERMS_VIEW]) {
        if (X_GUEST) {
            redirect("{$full_url}misc.php?action=login", 0);
            exit;
        } else {
            error($lang['privforummsg']);
        }
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

if ($SETTINGS['subject_in_title'] == 'on') {
    $threadSubject = '- '.fnameOut($forum['name']);
}

// Search-link
$searchlink = makeSearchLink($forum['fid']);

validateTpp();
validatePpp();

$threadcount = $db->result($db->query("SELECT COUNT(tid) FROM ".X_PREFIX."threads WHERE fid=$fid"), 0);

// Perform automatic maintenance
if ($forum['type'] == 'sub' And $forum['threads'] != $threadcount) {
    updateforumcount($fid);
}

$mpage = multipage($threadcount, $tpp, 'forumdisplay.php?fid='.$fid);

eval('$header = "'.template('header').'";');

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

// This first query does not access any table data if the new forum_optimize index is available.  :)
$criteria = '';
$offset = '';
if ($mpage['start'] <= 30) {
    // However, we need to be beyond page 1 to get any boost.
    $offset = "{$mpage['start']},";
} else {
    $query1 = $db->query(
        "SELECT topped, lastpost
         FROM ".X_PREFIX."threads
         WHERE fid=$fid
         ORDER BY topped DESC, lastpost DESC
         LIMIT {$mpage['start']}, $tpp"
    );
    if ($row = $db->fetch_array($query1)) {
        $db->escape_fast($row['lastpost']);

        $rowcount = $db->num_rows($query1);
        $db->data_seek($query1, $rowcount - 1);
        $lastrow = $db->fetch_array($query1);

        if (intval($row['topped']) == 0) {
            $criteria = " AND topped = 0 AND lastpost <= '{$row['lastpost']}' ";
        } elseif (intval($lastrow['topped']) == 1) {
            $criteria = " AND topped = 1 AND lastpost <= '{$row['lastpost']}' ";
        } else {
            $criteria = " AND (lastpost <= '{$row['lastpost']}' OR topped = 0) ";
        }
    } else {
        $criteria = " AND 1=0 ";
    }
    $db->free_result($query1);
}

$threadlist = '';
$threadsInFid = array();

$querytop = $db->query(
    "SELECT t.*, m.uid, r.uid AS lastauthor
     FROM ".X_PREFIX."threads AS t
     LEFT JOIN ".X_PREFIX."members AS m ON t.author = m.username
     LEFT JOIN ".X_PREFIX."members AS r ON SUBSTRING_INDEX(SUBSTRING_INDEX(t.lastpost, '|', 2), '|', -1) = r.username
     WHERE t.fid=$fid $criteria $cusdate
     ORDER BY topped $ascdesc, lastpost $ascdesc
     LIMIT $offset $tpp"
);

if ($db->num_rows($querytop) == 0) {
    eval('$threadlist = "'.template('forumdisplay_nothreads').'";');
} elseif ($SETTINGS['dotfolders'] == 'on' && X_MEMBER && $self['postnum'] > 0) {
    while($thread = $db->fetch_array($querytop)) {
        $threadsInFid[] = $thread['tid'];
    }
    $db->data_seek($querytop, 0);

    $threadsInFid = implode(',', $threadsInFid);
    $query = $db->query("SELECT tid FROM ".X_PREFIX."posts WHERE tid IN ($threadsInFid) AND author='$xmbuser' GROUP BY tid");

    $threadsInFid = array();
    while($row = $db->fetch_array($query)) {
        $threadsInFid[] = $row['tid'];
    }
    $db->free_result($query);
}

while($thread = $db->fetch_array($querytop)) {
    if ($thread['icon'] != '' && file_exists($smdir.'/'.$thread['icon'])) {
        $thread['icon'] = '<img src="'.$smdir.'/'.$thread['icon'].'" alt="'.$thread['icon'].'" border="0" />';
    } else {
        $thread['icon'] = '';
    }

    if ($thread['topped'] == 1) {
        $topimage = '<img src="'.$admdir.'/untop.gif" alt="'.$lang['textuntopthread'].'" border="0" />';
    } else {
        $topimage = '<img src="'.$admdir.'/top.gif" alt="'.$lang['alttopthread'].'" border="0" />';
    }

    $thread['subject'] = shortenString(rawHTMLsubject(stripslashes($thread['subject'])), 125, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...');

    if ($thread['author'] == 'Anonymous') {
        $authorlink = $lang['textanonymous'];
    } elseif (is_null($thread['uid'])) {
        $authorlink = $thread['author'];
    } else {
        $authorlink = '<a href="member.php?action=viewpro&amp;member='.recodeOut($thread['author']).'">'.$thread['author'].'</a>';
    }

    $prefix = '';

    $lastpost = explode('|', $thread['lastpost']);
    $dalast = trim($lastpost[0]);

    if ($lastpost[1] == 'Anonymous') {
        $lastpost[1] = $lang['textanonymous'];
    } elseif (!is_null($thread['lastauthor'])) {
        $lastpost[1] = '<a href="member.php?action=viewpro&amp;member='.recodeOut(trim($lastpost[1])).'">'.trim($lastpost[1]).'</a>';
    } // else leave value unchanged

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

    $mpurl = 'viewthread.php?tid='.$thread['tid'];
    $multipage2 = multi(1, quickpage($thread['replies']+1, $ppp), $mpurl, FALSE);
    if (strlen($multipage2) != 0) {
        $multipage2 = "(<small>$multipage2</small>)";
    }
    unset($mpurl);

    eval('$threadlist .= "'.template($forumdisplay_thread).'";');

    $prefix = '';
}
$db->free_result($querytop);

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

eval('$sortby = "'.template('forumdisplay_sortby').'";');

$multipage =& $mpage['html'];
if (strlen($mpage['html']) != 0) {
    if ($status1 == 'Moderator') {
        eval('$multipage = "'.template('forumdisplay_multipage_admin').'";');
    } else {
        eval('$multipage = "'.template('forumdisplay_multipage').'";');
    }
}

if ($status1 == 'Moderator') {
    if (X_ADMIN) {
        $fadminlink = '<a href="cp.php?action=forum&amp;fdetails='.$forum['fid'].'" title="'.$lang['alteditsettings'].'"><img src="'.$admdir.'/editforumsets.gif" border="0" alt="" /></a>';
    } else {
        $fadminlink = '';
    }
    eval('$forumdisplay = "'.template('forumdisplay_admin').'";');
} else {
    eval('$forumdisplay = "'.template('forumdisplay').'";');
}

end_time();
eval('$footer = "'.template('footer').'";');
echo $header, $forumdisplay, $footer;
?>
