<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00
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
$tran = Services\translation();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

if (X_GUEST) {
    $core->redirect($vars->full_url . 'misc.php?action=login', timeout: 0);
}

//Validate $action, $pid, $tid, and $fid
$fid = -1;
$tid = -1;
$pid = -1;
$action = getPhpInput('action', 'g');
if ($action == 'report') {
    $pid = getRequestInt('pid');
    $query = $db->query("SELECT f.*, t.tid, t.subject FROM " . $vars->tablepre . "posts AS p LEFT JOIN " . $vars->tablepre . "threads AS t USING (tid) LEFT JOIN " . $vars->tablepre . "forums AS f ON f.fid=t.fid WHERE p.pid=$pid");
    if ($db->num_rows($query) != 1) {
        header('HTTP/1.0 404 Not Found');
        $core->error($lang['textnothread']);
    }
    $forum = $db->fetch_array($query);
    $db->free_result($query);
    $fid = (int) $forum['fid'];
    $tid = (int) $forum['tid'];
} elseif ($action == 'votepoll') {
    $tid = getRequestInt('tid');
    $query = $db->query("SELECT f.*, t.subject FROM " . $vars->tablepre . "threads AS t LEFT JOIN " . $vars->tablepre . "forums AS f USING (fid) WHERE t.tid = $tid");
    if ($db->num_rows($query) != 1) {
        header('HTTP/1.0 404 Not Found');
        $core->error($lang['textnothread']);
    }
    $forum = $db->fetch_array($query);
    $db->free_result($query);
    $fid = (int) $forum['fid'];
} else {
    header('HTTP/1.0 404 Not Found');
    $core->error($lang['textnoaction']);
}

if (($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on') {
    header('HTTP/1.0 404 Not Found');
    $core->error($lang['textnoforum']);
}

$core->assertForumPermissions($forum);

$core->forumBreadcrumbs($forum);

if ($tid > 0) {
    $subject = shortenString($core->rawHTMLsubject($forum['subject']));
    $core->nav('<a href="' . $vars->full_url . 'viewthread.php?tid='.$tid.'">'.$subject.'</a>');
    unset($subject);
}

// Search-link
$template->searchlink = $core->makeSearchLink((int) $forum['fid']);

if ($action == 'report') {
    $core->nav($lang['textreportpost']);
    if ($vars->settings['subject_in_title'] == 'on') {
        $template->threadSubject = $vars->lang['textreportpost'] . ' - ';
    }

    $header = $template->process('header.php');

    if ('off' == $vars->settings['reportpost'] || ('on' == $vars->settings['quarantine_new_users'] && (0 == (int) $vars->self['postnum'] || 'yes' == $vars->self['waiting_for_mod']) && ! X_STAFF)) {
        header('HTTP/1.0 403 Forbidden');
        $featureoff = $template->process('misc_feature_notavailable.php');
        $template->footerstuff = $core->end_time();
        $footer = $template->process('footer.php');
        echo $header, $featureoff, $footer;
        exit();
    }

    if (noSubmit('reportsubmit')) {
        $template->pid = $pid;
        $template->tid = $tid;
        $template->fid = $fid;
        $body = $template->process('vtmisc_report.php');
    } else {
        $u2u = new U2U($db, $sql, $tran, $validate, $vars);
        $modquery = $db->query("SELECT username FROM " . $vars->tablepre . "members WHERE status IN ('Super Administrator', 'Administrator', 'Super Moderator')");
        while ($modusr = $db->fetch_array($modquery)) {
            $posturl = $vars->full_url . "viewthread.php?tid=$tid&amp;goto=search&amp;pid=$pid";
            $reason = $validate->postedVar('reason', dbescape: false, quoteencode: false);
            $message = "{$lang['reportmessage']} $posturl\n\n{$lang['reason']} $reason";
            
            $u2u->send_single($modusr['username'], $lang['reportsubject'], $message);
        }
        $db->free_result($modquery);

        $core->message($lang['reportmsg'], redirect: $vars->full_url . "viewthread.php?tid=$tid&goto=search&pid=$pid");
    }

} elseif ($action == 'votepoll') {
    $core->nav($lang['textvote']);
    if ($vars->settings['subject_in_title'] == 'on') {
        $template->threadSubject = $vars->lang['textvote'] . ' - ';
    }

    // User voted in poll related to thread $tid. The vote option is contained in $postopnum
    $postopnum = formInt('postopnum');
    if ($postopnum === 0) {
        $core->error($lang['pollvotenotselected']);
    }

    // Does a poll exist for this thread?
    $tid = intval($tid);
    $vote_id = $sql->getPollId($tid);
    if ($vote_id === 0) {
        $core->error($lang['pollvotenotselected']);
    }

    // does the poll option exist?
    $query = $db->query("SELECT COUNT(vote_option_id) FROM " . $vars->tablepre . "vote_results WHERE vote_id = $vote_id AND vote_option_id = $postopnum");
    $vote_result = intval($db->result($query)); //Aggregate functions with no grouping always return 1 row.
    $db->free_result($query);
    if ($vote_result != 1) {
        $core->error($lang['pollvotenotselected']);
    }

    // Has the user voted on this poll before?
    $query = $db->query("SELECT COUNT(vote_id) FROM " . $vars->tablepre . "vote_voters WHERE vote_id = $vote_id AND vote_user_id = " . $vars->self['uid']);
    $voted = intval($db->result($query));
    $db->free_result($query);
    if ($voted >= 1) {
        $core->error($lang['alreadyvoted']);
    }

    // Okay, the user is about to vote

    $core->request_secure('View Thread/Poll Vote', (string) $vote_id);

    if ((int) $vars->settings['schema_version'] < 9 || strlen($vars->onlineip) > 39) {
        $userip = '';
    } else {
        $userip = $vars->onlineip;
    }

    // TODO: Trying to check affected rows before updating the vote count.  However, the vote_voters table lacks a unique index.  Also, the previous query would become unnecessary.
    $added = $sql->addVoter($vote_id, (int) $vars->self['uid'], $userip);

    if ($added) {
        $db->query("UPDATE " . $vars->tablepre . "vote_results SET vote_result = vote_result + 1 WHERE vote_id = $vote_id AND vote_option_id = $postopnum");
    } 

    if ($tid > 0) {
        $core->message($lang['votemsg'], redirect: $vars->full_url . "viewthread.php?tid=$tid");
    } else {
        $core->message($lang['votemsg'], redirect: $vars->full_url);
    }
}

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $body, $footer;
