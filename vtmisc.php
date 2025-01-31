<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
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
 */

$forums = \XMB\Services\forums();
$sql = \XMB\Services\sql();

define('X_SCRIPT', 'vtmisc.php');

require 'header.php';

loadtemplates(
'vtmisc_report',
'misc_feature_notavailable'
);

if (X_GUEST) {
    redirect("{$full_url}misc.php?action=login", 0);
    exit;
}

//Validate $action, $pid, $tid, and $fid
$fid = -1;
$tid = -1;
$pid = -1;
$action = postedVar('action', '', FALSE, FALSE, FALSE, 'g'); //Forms did not include the action
if ($action == 'report') {
    $pid = getRequestInt('pid');
    $query = $db->query("SELECT f.*, t.tid, t.subject FROM ".X_PREFIX."posts AS p LEFT JOIN ".X_PREFIX."threads AS t USING (tid) LEFT JOIN ".X_PREFIX."forums AS f ON f.fid=t.fid WHERE p.pid=$pid");
    if ($db->num_rows($query) != 1) {
        header('HTTP/1.0 404 Not Found');
        error($lang['textnothread']);
    }
    $forum = $db->fetch_array($query);
    $db->free_result($query);
    $fid = (int) $forum['fid'];
    $tid = (int) $forum['tid'];
} else if ($action == 'votepoll') {
    $tid = getRequestInt('tid');
    $query = $db->query("SELECT f.*, t.subject FROM ".X_PREFIX."threads AS t LEFT JOIN ".X_PREFIX."forums AS f USING (fid) WHERE t.tid=$tid");
    if ($db->num_rows($query) != 1) {
        header('HTTP/1.0 404 Not Found');
        error($lang['textnothread']);
    }
    $forum = $db->fetch_array($query);
    $db->free_result($query);
    $fid = (int) $forum['fid'];
} else {
    header('HTTP/1.0 404 Not Found');
    error($lang['textnoaction']);
}

if (($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on') {
    header('HTTP/1.0 404 Not Found');
    error($lang['textnoforum']);
}

// check permissions on this forum
$perms = checkForumPermissions($forum);
if (!($perms[$vars::PERMS_VIEW] || $perms[$vars::PERMS_USERLIST])) {
    error($lang['privforummsg']);
} else if (!$perms[$vars::PERMS_PASSWORD]) {
    handlePasswordDialog($fid);
}

$fup = array();
if ($forum['type'] == 'sub') {
    $fup = $forums->getForum((int) $forum['fup']);
    // prevent access to subforum when upper forum can't be viewed.
    $fupPerms = checkForumPermissions($fup);
    if (!$fupPerms[$vars::PERMS_VIEW]) {
        error($lang['privforummsg']);
    } else if (!$fupPerms[$vars::PERMS_PASSWORD]) {
        handlePasswordDialog($fup['fid']);
    } else if ((int) $fup['fup'] > 0) {
        $fupup = $forums->getForum((int) $fup['fup']);
        nav('<a href="index.php?gid='.$fup['fup'].'">'.fnameOut($fupup['name']).'</a>');
        unset($fupup);
    }
    nav('<a href="forumdisplay.php?fid='.$fup['fid'].'">'.fnameOut($fup['name']).'</a>');
    unset($fup);
} else if ((int) $forum['fup'] > 0) { // 'forum' in a 'group'
    $fup = $forums->getForum((int) $forum['fup']);
    nav('<a href="index.php?gid='.$fup['fid'].'">'.fnameOut($fup['name']).'</a>');
    unset($fup);
}
nav('<a href="forumdisplay.php?fid='.$fid.'">'.fnameOut($forum['name']).'</a>');
if ($tid > 0) {
    $subject = shortenString(rawHTMLsubject(stripslashes($forum['subject'])));
    nav('<a href="viewthread.php?tid='.$tid.'">'.$subject.'</a>');
    unset($subject);
}

if ($SETTINGS['subject_in_title'] == 'on') {
    $threadSubject = rawHTMLsubject(stripslashes($forum['subject'])) . ' - ';
}

// Search-link
$searchlink = makeSearchLink($forum['fid']);

if ($action == 'report') {
    nav($lang['textreportpost']);
    eval('echo "'.template('header').'";');

    if ('off' == $SETTINGS['reportpost'] || ('on' == $SETTINGS['quarantine_new_users'] && (0 == (int) $self['postnum'] || 'yes' == $self['waiting_for_mod']) && ! X_STAFF)) {
        header('HTTP/1.0 403 Forbidden');
        eval('echo "'.template('misc_feature_notavailable').'";');
        end_time();
        eval('echo "'.template('footer').'";');
        exit;
    }

    if (noSubmit('reportsubmit')) {
        eval('echo "'.template('vtmisc_report').'";');
    } else {
        require('include/u2u.inc.php');
        $modquery = $db->query("SELECT username, ppp FROM ".X_PREFIX."members WHERE status='Super Administrator' OR status='Administrator' OR status='Super Moderator'");
        while($modusr = $db->fetch_array($modquery)) {
            $posturl = $full_url."viewthread.php?tid=$tid&amp;goto=search&amp;pid=$pid";
            $reason = postedVar('reason', '', TRUE, FALSE);
            $message = $lang['reportmessage'].' '.$posturl."\n\n".$lang['reason'].' '.$reason;
            $message = addslashes($message); //Messages are historically double-slashed.
            $subject = addslashes($lang['reportsubject']);
            $db->escape_fast($message);
            $db->escape_fast($subject);
            $db->escape_fast($modusr['username']);

            u2u_send_recp($modusr['username'], $subject, $message);
        }
        $db->free_result($modquery);

        message($lang['reportmsg'], false, '', '', $full_url.'viewthread.php?tid='.$tid.'&goto=search&pid='.$pid, true, false, true);
    }

} else if ($action == 'votepoll') {
    nav($lang['textvote']);
    eval('echo "'.template('header').'";');

    // User voted in poll related to thread $tid. The vote option is contained in $postopnum
    $postopnum = formInt('postopnum');
    if ($postopnum === 0) {
        error($lang['pollvotenotselected'], false);
    }

    // Does a poll exist for this thread?
    $tid = intval($tid);
    $vote_id = $sql->getPollId($tid);
    if ($vote_id === 0) {
        error($lang['pollvotenotselected'], false);
    }

    // does the poll option exist?
    $query = $db->query("SELECT COUNT(vote_option_id) FROM ".X_PREFIX."vote_results WHERE vote_id=$vote_id AND vote_option_id=$postopnum");
    $vote_result = intval($db->result($query, 0)); //Aggregate functions with no grouping always return 1 row.
    $db->free_result($query);
    if ($vote_result != 1) {
        error($lang['pollvotenotselected'], false);
    }

    // Has the user voted on this poll before?
    $query = $db->query("SELECT COUNT(vote_id) FROM ".X_PREFIX."vote_voters WHERE vote_id=$vote_id AND vote_user_id={$self['uid']}");
    $voted = intval($db->result($query, 0));
    $db->free_result($query);
    if ($voted >= 1) {
        error($lang['alreadyvoted'], false);
    }

    // Okay, the user is about to vote
    
    if ((int) $SETTINGS['schema_version'] < 9 || strlen($onlineip) > 39) {
        $userip = '';
    } else {
        $userip = $onlineip;
    }
    
    $sql->addVoter($vote_id, $self['uid'], $userip);
    $db->query("UPDATE ".X_PREFIX."vote_results SET vote_result=vote_result+1 WHERE vote_id=$vote_id AND vote_option_id=$postopnum");

    if ($tid > 0) {
        message($lang['votemsg'], false, '', '', $full_url.'viewthread.php?tid='.$tid, true, false, true);
    } else {
        message($lang['votemsg'], false, '', '', $full_url, true, false, true);
    }
}

end_time();
eval('echo "'.template('footer').'";');
