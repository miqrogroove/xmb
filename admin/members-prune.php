<?php

/**
 * eXtreme Message Board
 * XMB 1.10.01
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

const ROOT = '../';
require ROOT . 'header.php';

$attach = Services\attach();
$core = Services\core();
$db = Services\db();
$forumcache = Services\forums();
$sql = Services\sql();
$template = Services\template();
$token = Services\token();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav('<a href="' . $vars->full_url . 'admin/members.php">' . $lang['textmembers'] . '</a>');
$core->nav($lang['cp_deleteposts']);

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['cp_deleteposts'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

$member = $validate->postedVar('member', dbescape: false, sourcearray: 'g');
if (onSubmit('nosubmit')) {
    $core->redirect($vars->full_url . 'admin/members.php', timeout: 0);
} elseif (noSubmit('yessubmit')) {
    $template->token = $token->create('Control Panel/Members/Del Posts', $member, $vars::NONCE_AYS_EXP);
    $template->memberLink = recodeOut($member);
    $template->message = str_replace('$member', $member, $lang['confirmDeletePosts']);
    $body = $template->process('admin_members_prune_ays.php');
} else {
    $core->request_secure('Control Panel/Members/Del Posts', $member);

    // Get TIDs
    $dirty = [];
    $rawuser = $member;
    $member = $db->escape($rawuser);
    $countquery = $db->query("SELECT tid FROM " . $vars->tablepre . "posts WHERE author='$member' GROUP BY tid");
    while ($post = $db->fetch_array($countquery)) {
        $dirty[] = (int) $post['tid'];
    }
    $db->free_result($countquery);

    // Get FIDs
    $fids = [];
    if (count($dirty) > 0) {
        $csv = implode(',', $dirty);
        $countquery = $db->query("SELECT fid FROM " . $vars->tablepre . "threads WHERE tid IN ($csv) GROUP BY fid");
        while ($thread = $db->fetch_array($countquery)) {
            $fids[] = (int) $thread['fid'];
        }
        $db->free_result($countquery);
    }

    // Delete Member's Posts
    $attach->deleteByUser($rawuser);
    $db->query("DELETE FROM " . $vars->tablepre . "posts WHERE author='$member'");
    $db->query("UPDATE " . $vars->tablepre . "members SET postnum = 0 WHERE username='$member'");

    // Delete Empty Threads
    // This will also delete thread redirectors where the redirect's author is $member
    $tids = [];
    $movedids = [];
    $countquery = $db->query("SELECT t.tid FROM " . $vars->tablepre . "threads AS t LEFT JOIN " . $vars->tablepre . "posts AS p USING (tid) WHERE t.closed NOT LIKE 'moved%' GROUP BY t.tid HAVING COUNT(p.pid) = 0");
    while ($threads = $db->fetch_array($countquery)) {
        $tids[] = (int) $threads['tid'];
        $movedids[] = 'moved|'.$threads['tid'];
    }
    $db->free_result($countquery);
    if (count($tids) > 0) {
        $csv = implode(',', $tids);
        $movedids = implode("', '", $movedids);
        $db->query("DELETE FROM " . $vars->tablepre . "threads WHERE tid IN ($csv) OR closed IN ('$movedids')");
        $db->query("DELETE FROM " . $vars->tablepre . "favorites WHERE tid IN ($csv)");
        $sql->deleteVotesByTID($tids);
    }

    // Update Thread Stats
    $dirty = array_diff($dirty, $tids);
    foreach ($dirty as $tid) {
        $core->updatethreadcount($tid);
    }

    // Update Forum Stats
    $fids = array_unique($fids);
    $fups = [];
    foreach ($fids as $fid) {
        $forum = $forumcache->getForum($fid);
        if ('sub' == $forum['type']) {
            $fups[] = (int) $forum['fup'];
        }
    }
    $fids = array_unique(array_merge($fids, $fups));
    foreach ($fids as $fid) {
        $core->updateforumcount($fid);
    }

    $body = "<tr bgcolor='" . $vars->theme['altbg2'] . "' class='ctrtablerow'><td>{$lang['editprofile_postsdeleted']}</td></tr>";
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
