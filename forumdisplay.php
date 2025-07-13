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
$sql = Services\sql();
$template = Services\template();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;
$SETTINGS = &$vars->settings;

$template->hottopic = str_replace('$hottopic', $SETTINGS['hottopic'], $lang['hottopiceval']);

$fid = getInt('fid');
$forum = $forums->getForum($fid);

if (null === $forum || ($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on') {
    header('HTTP/1.0 404 Not Found');
    $core->error($lang['textnoforum']);
}

$perms = $core->assertForumPermissions($forum);

$core->forumBreadcrumbs($forum, linkSelf: false);

if ($SETTINGS['subject_in_title'] == 'on') {
    $template->threadSubject = fnameOut($forum['name']) . ' - ';
}

// Search-link
$template->searchlink = $core->makeSearchLink((int) $forum['fid']);

$threadcount = $sql->countThreadsByForum($fid);

// Perform automatic maintenance
if ($forum['type'] == 'sub' && (int) $forum['threads'] != $threadcount) {
    // Also verify the value that we expect to overwrite.
    $core->updateforumcount($fid, oldThreadCount: (int) $forum['threads']);
}

$mpage = $core->multipage($threadcount, $vars->tpp, $vars->full_url . "forumdisplay.php?fid=$fid");

$header = $template->process('header.php');

$template->fid = $fid;

if ($perms[$vars::PERMS_POLL]) {
    $template->newpolllink = $template->process('forumdisplay_newpoll.php');
} else {
    $template->newpolllink = '';
}

if ($perms[$vars::PERMS_THREAD]) {
    $template->newtopiclink = $template->process('forumdisplay_newtopic.php');
} else {
    $template->newtopiclink = '';
}

$template->subforums = '';
if ($forum['type'] == 'forum') {
    $template->forumlist = '';
    $permitted = $core->permittedForums('forum');
    foreach ($permitted as $sub) {
        if ($sub['type'] == 'sub' && (int) $sub['fup'] == $fid) {
            $template->forumlist .= $core->forum($sub, 'forumdisplay_subforum', index_subforums: []);
        }
    }
    if ($template->forumlist != '') {
        $template->subforums .= $template->process('forumdisplay_subforums.php');
    }
}

if (X_MEMBER && 'yes' == $vars->self['waiting_for_mod']) {
    $quarantine = true;
    $result = $sql->countThreadsByUser($vars->self['username'], $fid, $quarantine);
    if ($result > 0) {
        if (1 == $result) {
            $msg = $lang['moderation_threads_single'];
        } else {
            $msg = str_replace('$result', (string) $result, $lang['moderation_threads_eval']);
        }
        $template->subforums .= $core->message(
            msg: $msg,
            showheader: false,
            die: false,
            return_as_string: true,
            showfooter: false,
        ) . "<br />\n";
    }
}

$t_extension = get_extension($lang['toppedprefix']);
switch ($t_extension) {
    case 'gif':
    case 'jpg':
    case 'jpeg':
    case 'png':
        $lang['toppedprefix'] = '<img src="' . $vars->theme['imgdir'] . '/'.$lang['toppedprefix'].'" alt="'.$lang['toppedpost'].'" border="0" />';
        break;
}

$p_extension = get_extension($lang['pollprefix']);
switch ($p_extension) {
    case 'gif':
    case 'jpg':
    case 'jpeg':
    case 'png':
        $lang['pollprefix'] = '<img src="' . $vars->theme['imgdir'] . '/'.$lang['pollprefix'].'" alt="'.$lang['postpoll'].'" border="0" />';
        break;
}

$cusdate = formInt('cusdate');
if ($cusdate) {
    $cusdateval = $vars->onlinetime - $cusdate;
    $cusdatesql = "AND lastpost > '$cusdateval'";
} else {
    $cusdatesql = '';
}

$ascdesc = getPhpInput('ascdesc');
if (strtolower($ascdesc) != 'asc') {
    $ascdesc = "desc";
}

$forumdisplay_thread = 'forumdisplay_thread';

$status1 = $core->modcheck($vars->self['username'], $forum['moderator']);

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
         FROM " . $vars->tablepre . "threads
         WHERE fid=$fid
         ORDER BY topped DESC, lastpost DESC
         LIMIT {$mpage['start']}, " . $vars->tpp
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

$template->threadlist = '';
$threadsInFid = [];

$querytop = $db->query(
    "SELECT t.*, m.uid
     FROM " . $vars->tablepre . "threads AS t
     LEFT JOIN " . $vars->tablepre . "members AS m ON t.author = m.username
     WHERE t.fid = $fid $criteria $cusdatesql
     ORDER BY topped $ascdesc, lastpost $ascdesc
     LIMIT $offset " . $vars->tpp
);

if ($db->num_rows($querytop) == 0) {
    if ($status1 == 'Moderator') {
        $threadlist = $template->process('forumdisplay_nothreads_admin.php');
    } else {
        $threadlist = $template->process('forumdisplay_nothreads.php');
    }
} elseif ($SETTINGS['dotfolders'] == 'on' && X_MEMBER && (int) $vars->self['postnum'] > 0) {
    while ($thread = $db->fetch_array($querytop)) {
        $threadsInFid[] = $thread['tid'];
    }
    $db->data_seek($querytop, 0);

    $threadsInFid = implode(',', $threadsInFid);
    $query = $db->query("SELECT tid FROM " . $vars->tablepre . "posts WHERE tid IN ($threadsInFid) AND author='" . $vars->xmbuser . "' GROUP BY tid");

    $threadsInFid = [];
    while ($row = $db->fetch_array($query)) {
        $threadsInFid[] = $row['tid'];
    }
    $db->free_result($query);
}

while ($thread = $db->fetch_array($querytop)) {
    null_string($thread['icon']);
    if ($thread['icon'] !== '' && file_exists($vars->full_url . $vars->theme['smdir'] . '/' . $thread['icon'])) {
        $thread['icon'] = '<img src="' . $vars->full_url . $vars->theme['smdir'] . '/' . $thread['icon'] . '" alt="' . $thread['icon'] . '" border="0" />';
    } else {
        $thread['icon'] = '';
    }

    if ('1' === $thread['topped']) {
        $template->topimage = '<img src="' . $vars->full_url . $vars->theme['admdir'] . '/untop.gif" alt="' . $lang['textuntopthread'] . '" border="0" />';
    } else {
        $template->topimage = '<img src="' . $vars->full_url . $vars->theme['admdir'] . '/top.gif" alt="' . $lang['alttopthread'] . '" border="0" />';
    }

    $thread['subject'] = shortenString($core->rawHTMLsubject(stripslashes($thread['subject'])));

    if ($thread['author'] == 'Anonymous') {
        $template->authorlink = $lang['textanonymous'];
    } elseif (is_null($thread['uid'])) {
        $template->authorlink = $thread['author'];
    } else {
        $template->authorlink = '<a href="' . $vars->full_url . 'member.php?action=viewpro&amp;member=' . recodeOut($thread['author']) . '">' . $thread['author'] . '</a>';
    }

    $prefix = '';

    $lastpost = explode('|', $thread['lastpost']);
    $dalast = (int) trim($lastpost[0]);

    // Translate "Anonymous" author.
    $lastpostname = trim($lastpost[1]);
    if ('Anonymous' == $lastpostname) {
        $lastpostname = $lang['textanonymous'];
    }

    $lastPid = isset($lastpost[2]) ? $lastpost[2] : 0;

    if ($thread['closed'] == 'yes') {
        $folder = '<img src="' . $vars->full_url . $vars->theme['imgdir'] . '/lock_folder.gif" alt="'.$lang['altclosedtopic'].'" border="0" />';
    } else {
        if ((int) $thread['replies'] >= (int) $SETTINGS['hottopic']) {
            $folder = 'hot_folder.gif';
        } else {
            $folder = 'folder.gif';
        }

        $oT = strpos($vars->oldtopics, "|$lastPid|");
        if ($vars->lastvisit < $dalast && $oT === false) {
            if ((int) $thread['replies'] >= (int) $SETTINGS['hottopic']) {
                $folder = "hot_red_folder.gif";
            } else {
                $folder = "red_folder.gif";
            }
        }

        if ($SETTINGS['dotfolders'] == 'on' && X_MEMBER && (count($threadsInFid) > 0) && in_array($thread['tid'], $threadsInFid)) {
            $folder = 'dot_'.$folder;
        }

        $folder = '<img src="' . $vars->full_url . $vars->theme['imgdir'] . '/'.$folder.'" alt="'.$lang['altfolder'].'" border="0" />';
    }

    $adjStamp = $core->timeKludge((int) $lastpost[0]);
    $lastreplydate = $core->printGmDate($adjStamp);
    $lastreplytime = gmdate($vars->timecode, $adjStamp);

    $template->lastpost = "$lastreplydate {$lang['textat']} $lastreplytime<br />{$lang['textby']} $lastpostname";

    $moved = explode('|', $thread['closed']);
    if ($moved[0] == 'moved') {
        $prefix = $lang['moved'].' ';
        $thread['realtid'] = $thread['tid'];
        $thread['tid'] = $moved[1];
        $thread['replies'] = "-";
        $thread['views'] = "-";
        $folder = '<img src="' . $vars->full_url . $vars->theme['imgdir'] . '/lock_folder.gif" alt="' . $lang['altclosedtopic'] . '" border="0" />';
        $query = $db->query("SELECT COUNT(*) FROM " . $vars->tablepre . "posts WHERE tid='$thread[tid]'");
        $postnum = 0;
        if ($query !== false) {
            $postnum = $db->result($query, 0);
        }
    } else {
        $thread['realtid'] = $thread['tid'];
    }

    $template->tid = $thread['tid'];
    $template->lastpostrow = $template->process('forumdisplay_thread_lastpost.php');

    if ('1' === $thread['pollopts']) {
        $prefix = $lang['pollprefix'].' ';
    }

    if ('1' === $thread['topped']) {
        $prefix = $lang['toppedprefix'].' '.$prefix;
    }

    $template->folder = $folder;
    $template->prefix = $prefix;
    $template->thread = $thread;

    $template->threadlist .= $template->process($forumdisplay_thread . '.php');
}
$db->free_result($querytop);

$template->check1 = '';
$template->check5 = '';
$template->check15 = '';
$template->check30 = '';
$template->check60 = '';
$template->check100 = '';
$template->checkyear = '';
$template->checkall = '';
switch ($cusdate) {
    case 86400:
        $template->check1 = $vars::selHTML;
        break;
    case 432000:
        $template->check5 = $vars::selHTML;
        break;
    case 1296000:
        $template->check15 = $vars::selHTML;
        break;
    case 2592000:
        $template->check30 = $vars::selHTML;
        break;
    case 5184000:
        $template->check60 = $vars::selHTML;
        break;
    case 8640000:
        $template->check100 = $vars::selHTML;
        break;
    case 31536000:
        $template->checkyear = $vars::selHTML;
        break;
    default:
        $template->checkall = $vars::selHTML;
        break;
}

$template->sortby = $template->process('forumdisplay_sortby.php');

$template->mpage = $mpage['html'];
$template->multipage = '';
$template->multipage3 = '';
if (strlen($template->mpage) != 0) {
    if ($status1 == 'Moderator') {
        $template->multipage = $template->process('forumdisplay_multipage_admin.php');
        $template->multipage3 = $template->process('forumdisplay_multipage_admin3.php');
    } else {
        $template->multipage = $template->process('forumdisplay_multipage.php');
    }
}

if ($status1 == 'Moderator') {
    if (X_ADMIN) {
        $template->fadminlink = '<a href="' . $vars->full_url . 'cp.php?action=forum&amp;fdetails=' . $forum['fid'] . '" title="' . $lang['alteditsettings'] . '"><img src="' . $vars->full_url . $vars->theme['admdir'] . '/editforumsets.gif" border="0" alt="" /></a>';
    } else {
        $template->fadminlink = '';
    }
    $forumdisplay = $template->process('forumdisplay_admin.php');
} else {
    $forumdisplay = $template->process('forumdisplay.php');
}

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $forumdisplay, $footer;
