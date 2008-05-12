<?php
/**
 * eXtreme Message Board
 * XMB 1.9.10
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

define('X_SCRIPT', 'vtmisc.php');

require 'header.php';

eval('$css = "'.template('css').'";');

if (!X_MEMBER) {
    error($lang['notpermitted']);
}

loadtemplates(
'vtmisc_report',
'misc_feature_notavailable'
);

//Validate $action, $pid, $tid, and $fid
$fid = -1;
$tid = -1;
$pid = -1;
$action = postedVar('action', '', FALSE, FALSE, FALSE, 'g'); //Forms did not include the action
if ($action == 'report') {
    $pid = getRequestInt('pid');
    $query = $db->query("SELECT f.*, t.tid, t.subject FROM ".X_PREFIX."posts AS p LEFT JOIN (".X_PREFIX."threads AS t LEFT JOIN ".X_PREFIX."forums AS f USING (fid)) USING (tid) WHERE p.pid=$pid");
    if ($db->num_rows($query) != 1) {
        error($lang['textnothread']);
    }
    $forum = $db->fetch_array($query);
    $db->free_result($query);
    $fid = $forum['fid'];
    $tid = $forum['tid'];
    nav($lang['textreportpost']);
} elseif ($action == 'votepoll') {
    $tid = getRequestInt('tid');
    $query = $db->query("SELECT f.*, t.subject FROM ".X_PREFIX."threads AS t LEFT JOIN ".X_PREFIX."forums AS f USING (fid) WHERE t.tid=$tid");
    if ($db->num_rows($query) != 1) {
        error($lang['textnothread']);
    }
    $forum = $db->fetch_array($query);
    $db->free_result($query);
    $fid = $forum['fid'];
    nav($lang['textvote']);
} else {
    error($lang['textnoaction']);
}

if ($fid == 0 || ($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on') {
    error($lang['textnoforum']);
}

// check permissions on this forum
$perms = checkForumPermissions($forum);
if(!$perms[X_PERMS_VIEW] || !$perms[X_PERMS_USERLIST]) {
    error($lang['privforummsg']);
} elseif(!$perms[X_PERMS_PASSWORD]) {
    handlePasswordDialog($fid, basename(__FILE__), $_GET);
}

// check parent-forum permissions
if($forum['type'] == 'sub') {
    $fup = $db->fetch_array($db->query("SELECT postperm, userlist, password FROM ".X_PREFIX."forums WHERE fid=$forum[fup]"));
    $fupPerms = checkForumPermissions($fup);

    if(!$fupPerms[X_PERMS_VIEW] || !$fupPerms[X_PERMS_USERLIST] || !$fupPerms[X_PERMS_PASSWORD]) {
        error($lang['privforummsg']);
    }
    // do not show password-dialog here; it makes the situation too complicated
}

if ($SETTINGS['subject_in_title'] == 'on') {
    $threadSubject = '- '.censor(stripslashes($forum['subject']));
}

eval('echo "'.template('header').'";');

if ($action == 'report') {
    if ($SETTINGS['reportpost'] == 'off') {
        eval('echo "'.template('misc_feature_notavailable').'";');
        end_time();
        eval('echo "'.template('footer').'";');
        exit;
    }

    if (noSubmit('reportsubmit')) {
        eval('echo stripslashes("'.template('vtmisc_report').'");');
    } else {
        $query = $db->query("SELECT count(pid) FROM ".X_PREFIX."posts WHERE tid=$tid");
        $postcount = $db->result($query, 0); //Aggregate functions with no grouping always return 1 row.
        $db->free_result($query);

        $modquery = $db->query("SELECT username, ppp FROM ".X_PREFIX."members WHERE status='Super Administrator' OR status='Administrator' OR status='Super Moderator'");
        while($modusr = $db->fetch_array($modquery)) {
            $mod = $db->escape($modusr['username']);
            $page = quickpage($postcount, $modusr['ppp']);

            $posturl = $SETTINGS['boardurl']."viewthread.php?tid=$tid&page=$page#pid$pid";
            $reason = postedVar('reason');
            $message = $lang['reportmessage'].' '.$posturl."\n\n".$lang['reason'].' '.$reason;

            $db->query("INSERT INTO ".X_PREFIX."u2u (msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus) VALUES ('$mod', '$xmbuser', 'incoming', '$mod', 'Inbox', '{$lang['reportsubject']}', '$message', ".$db->time($onlinetime).", 'no', 'yes')");
        }
        $db->free_result($modquery);

        $page = quickpage($postcount, $tpp);
        message($lang['reportmsg'], false, '', '', 'viewthread.php?tid='.$tid.'&page='.$page.'#pid'.$pid, true, false, true);
    }

} elseif ($action == 'votepoll') {
    // User voted in poll related to thread $tid. The vote option is contained in $postopnum
    $postopnum = formInt('postopnum');
    if ($postopnum === 0) {
        error($lang['pollvotenotselected'], false);
    }

    // Does a poll exist for this thread?
    $tid = intval($tid);
    $query = $db->query("SELECT vote_id FROM ".X_PREFIX."vote_desc WHERE topic_id=$tid");
    if ($query === false) {
        error($lang['pollvotenotselected'], false);
    }

    $vote_id = $db->fetch_array($query);
    $vote_id = $vote_id['vote_id'];
    $db->free_result($query);

    // does the poll option exist?
    $query = $db->query("SELECT COUNT(vote_option_id) FROM ".X_PREFIX."vote_results WHERE vote_id=$vote_id AND vote_option_id=$postopnum");
    $vote_result = $db->result($query, 0); //Aggregate functions with no grouping always return 1 row.
    $db->free_result($query);
    if ($vote_result != 1) {
        error($lang['pollvotenotselected'], false);
    }

    // Has the user voted on this poll before?
    $query = $db->query("SELECT COUNT(vote_id) FROM ".X_PREFIX."vote_voters WHERE vote_id=$vote_id AND vote_user_id={$self['uid']}");
    $voted = $db->result($query, 0); //Aggregate functions with no grouping always return 1 row.
    $db->free_result($query);
    if ($voted === 1) {
        error($lang['alreadyvoted'], false);
    }

    // Okay, the user is about to vote
    $db->query("INSERT INTO ".X_PREFIX."vote_voters (vote_id, vote_user_id, vote_user_ip) VALUES ($vote_id, {$self['uid']}, '".encode_ip($onlineip)."')");
    $db->query("UPDATE ".X_PREFIX."vote_results SET vote_result=vote_result+1 WHERE vote_id=$vote_id AND vote_option_id=$postopnum");

    if ($tid > 0) {
        message($lang['votemsg'], false, '', '', 'viewthread.php?tid='.$tid, true, false, true);
    } else {
        message($lang['votemsg'], false, '', '', 'index.php', true, false, true);
    }
}

end_time();
eval('echo "'.template('footer').'";');
?>
