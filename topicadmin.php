<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-2
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

$attach = Services\attach();
$core = Services\core();
$db = Services\db();
$forums = Services\forums();
$sql = Services\sql();
$template = Services\template();
$token = Services\token();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;
$full_url = $vars->full_url;

if (X_GUEST) {
    $core->redirect("{$full_url}misc.php?action=login", timeout: 0);
}

$onlinetime = $vars->onlinetime;

$tids = array_unique($validate->postedArray('tid', 'int', source: 'r'));
$fid = getInt('fid', 'p');
if ($fid == 0) {
    $fid = getInt('fid');
}
$pid = getInt('pid');
$othertid = formInt('othertid');
$action = $validate->postedVar('action', sourcearray: 'r');

if (count($tids) == 1) {
    $query = $db->query("SELECT * FROM " . $vars->tablepre . "threads WHERE tid={$tids[0]}");
    $thread = $db->fetch_array($query);
    $db->free_result($query);
    $threadname = $core->rawHTMLsubject($thread['subject']);
    $fid = (int) $thread['fid'];
} else {
    $threadname = '';
}

$forum = $forums->getForum($fid);

if (null === $forum || ($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on') {
    header('HTTP/1.0 404 Not Found');
    $core->error($lang['textnoforum']);
}

// Check for authorization to be here in the first place
$core->assertForumPermissions($forum);

$core->forumBreadcrumbs($forum);
if (count($tids) == 1) {
    $core->nav('<a href="' . $vars->full_url . 'viewthread.php?tid=' . $tids[0] . '">' . $threadname . '</a>');
}

$kill = false;

switch ($action) {
    case 'delete':
        $pagename = 'textdeletethread';
        break;
    case 'top':
        $pagename = 'texttopthread';
        break;
    case 'close':
        $pagename = 'textclosethread';
        break;
    case 'copy':
        $pagename = 'copythread';
        break;
    case 'f_close':
        $pagename = 'textclosethread';
        break;
    case 'f_open':
        $pagename = 'textopenthread';
        break;
    case 'move':
        $pagename = 'textmovemethod1';
        break;
    case 'getip':
        $kill = ! X_ADMIN;
        $pagename = 'textgetip';
        break;
    case 'bump':
        $pagename = 'textbumpthread';
        break;
    case 'split':
        $pagename = 'textsplitthread';
        break;
    case 'merge':
        $pagename = 'textmergethread';
        break;
    case 'threadprune':
        $pagename = 'textprunethread';
        break;
    case 'empty':
        $pagename = 'textemptythread';
        break;
    default:
        $kill = true;
}

$core->nav($lang[$pagename]);
if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $lang[$pagename] . ' - ';
}

$kill = $kill || ! X_STAFF || ! $core->modcheckForum($fid);

if ($kill) {
    $core->error($lang['notpermitted']);
}

// Search-link
$template->searchlink = $core->makeSearchLink((int) $forum['fid']);

if (0 === $pid && 'getip' === $action) {
    header('HTTP/1.0 404 Not Found');
    $core->error($lang['noresults']);
}

$header = $template->process('header.php');

//Assert permissions on all TIDs
if (count($tids) > 1) {
    $csv = implode(',', $tids);
    $tids = [];
    $query = $db->query("SELECT tid FROM " . $vars->tablepre . "threads WHERE tid IN ($csv) AND fid = $fid");
    while ($row = $db->fetch_array($query)) {
        $tids[] = $row['tid'];
    }
    $db->free_result($query);
    unset($csv);
}

$template->hUsername = $vars->self['username'];
$template->fid = $fid;

switch ($action) {
    case 'delete':
        if (noSubmit('deletesubmit')) {
            $template->tid = implode(',', $tids);
            $template->token = $token->create('Thread Admin Options/Delete', (string) min($tids), $vars::NONCE_AYS_EXP);
            $page = $template->process('topicadmin_delete.php');
        } else {
            $core->request_secure('Thread Admin Options/Delete', (string) min($tids));

            foreach ($tids as $tid) {
                $tid = (int) $tid;
                $query = $db->query("SELECT author, COUNT(*) AS pidcount FROM " . $vars->tablepre . "posts WHERE tid = $tid GROUP BY author");
                while ($result = $db->fetch_array($query)) {
                    $sql->adjustPostCount($result['author'], 0 - (int) $result['pidcount']);
                }
                $db->free_result($query);

                $attach->deleteByThread($tid);  // Must delete attachments before posts!
                $db->query("DELETE FROM " . $vars->tablepre . "posts WHERE tid = $tid");
                $db->query("DELETE FROM " . $vars->tablepre . "favorites WHERE tid = $tid");
                $sql->deleteVotesByTID([$tid]);

                $db->query("DELETE FROM " . $vars->tablepre . "threads WHERE tid = $tid OR closed = 'moved|$tid'");

                if ($forum['type'] == 'sub') {
                    $core->updateforumcount((int) $forum['fup']);
                }
                $core->updateforumcount($fid);

                $core->audit($vars->self['username'], $action, $fid, $tid);
            }
            $core->message($lang['deletethreadmsg'], redirect: $full_url . 'forumdisplay.php?fid=' . $fid);
        }
        break;

    case 'close':
        $tid = $tids[0];
        $query = $db->query("SELECT closed FROM " . $vars->tablepre . "threads WHERE tid = $tid");
        if ($db->num_rows($query) == 0) {
            $core->error($lang['textnothread']);
        }
        $closed = $db->result($query);
        $db->free_result($query);

        if (noSubmit('closesubmit')) {
            $template->action = $action;
            $template->closed = $closed;
            $template->tid = $tid;
            $template->token = $token->create('Thread Admin Options/OpenOrClose', (string) $tid, $vars::NONCE_AYS_EXP);
            $page = $template->process('topicadmin_openclose.php');
        } else {
            $core->request_secure('Thread Admin Options/OpenOrClose', (string) $tid);
            if ($closed == 'yes') {
                $db->query("UPDATE " . $vars->tablepre . "threads SET closed = '' WHERE tid = $tid");
            } else {
                $db->query("UPDATE " . $vars->tablepre . "threads SET closed = 'yes' WHERE tid = $tid");
            }

            $act = ($closed != '') ? 'open' : 'close';
            $core->audit($vars->self['username'], $act, $fid, $tid);

            $core->message($lang['closethreadmsg'], redirect: $full_url . 'forumdisplay.php?fid=' . $fid);
        }
        break;

    case 'f_close':
        if (noSubmit('closesubmit')) {
            $template->tid = implode(',', $tids);
            $template->action = $action;
            $template->closed = '';
            $template->token = $token->create('Thread Admin Options/Close', (string) min($tids), $vars::NONCE_AYS_EXP);
            $page = $template->process('topicadmin_openclose.php');
        } else {
            $core->request_secure('Thread Admin Options/Close', (string) min($tids));
            if (count($tids) > 0) {
                $csv = implode(',', $tids);
                $db->query("UPDATE " . $vars->tablepre . "threads SET closed = 'yes' WHERE tid IN ($csv)");
                foreach ($tids as $tid) {
                    $tid = (int) $tid;
                    $core->audit($vars->self['username'], 'close', $fid, $tid);
                }
            }
            $core->message($lang['closethreadmsg'], redirect: $full_url . 'forumdisplay.php?fid=' . $fid);
        }
        break;

    case 'f_open':
        if (noSubmit('closesubmit')) {
            $template->tid = implode(',', $tids);
            $template->action = $action;
            $template->closed = 'yes';
            $template->token = $token->create('Thread Admin Options/Open', (string) min($tids), $vars::NONCE_AYS_EXP);
            $page = $template->process('topicadmin_openclose.php');
        } else {
            $core->request_secure('Thread Admin Options/Open', (string) min($tids));
            if (count($tids) > 0) {
                $csv = implode(',', $tids);
                $db->query("UPDATE " . $vars->tablepre . "threads SET closed = '' WHERE tid IN ($csv)");
                foreach ($tids as $tid) {
                    $tid = (int) $tid;
                    $core->audit($vars->self['username'], 'open', $fid, $tid);
                }
            }
            $core->message($lang['closethreadmsg'], redirect: $full_url . 'forumdisplay.php?fid=' . $fid);
        }
        break;

    case 'move':
        if (noSubmit('movesubmit')) {
            $template->tid = implode(',', $tids);
            $template->forumselect = $core->forumList('moveto', allowall: false, currentfid: $fid);
            $template->token = $token->create('Thread Admin Options/Move', (string) min($tids), $vars::NONCE_AYS_EXP);
            $page = $template->process('topicadmin_move.php');
        } else {
            $core->request_secure('Thread Admin Options/Move', (string) min($tids));
            $moveto = formInt('moveto');
            $type = $validate->postedVar('type');

            $movetorow = $forums->getForum($moveto);
            if ($movetorow === null) {
                $core->error($lang['textnoforum']);
            }
            if ($movetorow['type'] == 'group' || $moveto == $fid) {
                $core->error($lang['errormovingthreads']);
            }

            // Perform sanity checks on all redirects
            if ($type != 'normal' && count($tids) > 0) {
                $csv = implode(',', $tids);
                $tids = [];
                $query = $db->query("SELECT * FROM " . $vars->tablepre . "threads WHERE tid IN ($csv)");
                while ($info = $db->fetch_array($query)) {
                    if (substr($info['closed'], 0, 5) != 'moved') {
                        // Insert all thread redirectors.
                        $db->escape_fast($info['author']);
                        $db->escape_fast($info['subject']);
                        $db->query("INSERT INTO " . $vars->tablepre . "threads (fid, subject, icon, lastpost, views, replies, author, closed, topped) VALUES ({$info['fid']}, '{$info['subject']}', '', '".$db->escape($info['lastpost'])."', 0, 0, '{$info['author']}', 'moved|{$info['tid']}', '{$info['topped']}')");
                        $ntid = $db->insert_id();

                        $lastpost = explode('|', $info['lastpost']);
                        $lastposttime = intval($lastpost[0]);

                        $db->query("INSERT INTO " . $vars->tablepre . "posts (fid, tid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff) VALUES ({$info['fid']}, '$ntid', '{$info['author']}', '{$info['tid']}', '{$info['subject']}', $lastposttime, '', '', '', '', '')");
                        $tids[] = $info['tid'];
                    }
                }
                $db->free_result($query);
            }

            if (count($tids) > 0) {
                // Perform all moves using as few queries as possible.
                $csv = implode(',', $tids);
                $db->query("UPDATE " . $vars->tablepre . "threads SET fid = $moveto WHERE tid IN ($csv)");
                $db->query("UPDATE " . $vars->tablepre . "posts SET fid = $moveto WHERE tid IN ($csv)");
                foreach ($tids as $tid) {
                    $tid = (int) $tid;
                    $core->audit($vars->self['username'], $action, $moveto, $tid);
                }

                // Update all summary columns.
                if ($forum['type'] == 'sub') {
                    $core->updateforumcount((int) $forum['fup']);
                }
                if ($movetorow['type'] == 'sub') {
                    if ($forum['type'] == 'sub') {
                        $doupdate = $movetorow['fup'] !== $forum['fup'];
                    } else {
                        $doupdate = $movetorow['fup'] !== $forum['fid'];
                    }
                    if ($doupdate) {
                        $core->updateforumcount((int) $movetorow['fup']);
                    }
                }
                $core->updateforumcount($fid);
                $core->updateforumcount($moveto);
            }

            $core->message($lang['movethreadmsg'], redirect: $full_url . 'forumdisplay.php?fid=' . $fid);
        }
        break;

    case 'top':
        if (noSubmit('topsubmit')) {
            if (count($tids) == 1) {
                $query = $db->query("SELECT topped FROM " . $vars->tablepre . "threads WHERE tid = {$tids[0]}");
                if ($db->num_rows($query) == 0) {
                    $db->free_result($query);
                    $core->error($lang['textnothread']);
                }
                $topped = $db->result($query);
                $db->free_result($query);
                $template->heading = ('1' === $topped) ? $lang['textuntopthread'] : $lang['texttopthread'];
            } else {
                $template->heading = $lang['texttopthread'] . ' / ' . $lang['textuntopthread'];
            }
            $template->tid = implode(',', $tids);
            $template->token = $token->create('Thread Admin Options/Top', (string) min($tids), $vars::NONCE_AYS_EXP);
            $page = $template->process('topicadmin_topuntop.php');
        } else {
            $core->request_secure('Thread Admin Options/Top', (string) min($tids));
            foreach ($tids as $tid) {
                $tid = (int) $tid;
                $query = $db->query("SELECT topped FROM " . $vars->tablepre . "threads WHERE tid = $tid");
                if ($db->num_rows($query) == 0) {
                    $db->free_result($query);
                    $core->error($lang['textnothread']);
                }
                $topped = $db->result($query);
                $db->free_result($query);

                if ('1' === $topped) {
                    $db->query("UPDATE " . $vars->tablepre . "threads SET topped = '0' WHERE tid = $tid");
                } elseif ('0' === $topped)    {
                    $db->query("UPDATE " . $vars->tablepre . "threads SET topped = '1' WHERE tid = $tid");
                }

                $act = ($topped ? 'untop' : 'top');
                $core->audit($vars->self['username'], $act, $fid, $tid);
            }

            $core->message($lang['topthreadmsg'], redirect: $full_url . 'forumdisplay.php?fid=' . $fid);
        }
        break;

    case 'getip':
        $useip = $sql->getIPFromPost($pid);
        if ($useip === '') {
            $template->address = $lang['textnone'];
            $template->name = $lang['textnone'];
        } else {
            $template->address = $useip;
            $template->name = gethostbyaddr($useip);
        }

        $template->token = $token->create('Control Panel/IP Banning', 'mass-edit', $vars::NONCE_AYS_EXP);

        $ip = explode('.', $useip);
        $template->banningEnabled = $vars->settings['ip_banning'] == 'on' && count($ip) === 4;
        if ($template->banningEnabled) {
            $query = $db->query("SELECT * FROM " . $vars->tablepre . "banned WHERE (ip1='$ip[0]' OR ip1='-1') AND (ip2='$ip[1]' OR ip2='-1') AND (ip3='$ip[2]' OR ip3='-1') AND (ip4='$ip[3]' OR ip4='-1')");
            $result = $db->fetch_array($query);
            $db->free_result($query);
            if ($result) {
                $template->buttontext = $lang['textunbanip'];
                $foundmask = false;
                for ($i = 1; $i <= 4; ++$i) {
                    $j = "ip$i";
                    if ('-1' === $result[$j]) {
                        $result[$j] = "*";
                        $foundmask = true;
                    }
                }

                if ($foundmask) {
                    $ipmask = "<strong>$result[ip1].$result[ip2].$result[ip3].$result[ip4]</strong>";
                    $bannedipmask = str_replace('$ipmask', $ipmask, $lang['evalipmask']);
                    $template->existingBan = $bannedipmask;
                } else {
                    $template->existingBan = $lang['textbannedip'];
                }
                $template->ipBanInputs = "<input type='hidden' name='delete[{$result['id']}]' value='1' />";
            } else {
                $template->buttontext = $lang['textbanip'];
                $template->existingBan = '';
                $template->ipBanInputs = '';
                for ($i = 1; $i <= 4; ++$i) {
                    $j = $i - 1;
                    $template->ipBanInputs .= "<input type='hidden' name='newip$i' value='$ip[$j]' />\n";
                }
            }
        }

        $page = $template->process('topicadmin_getip.php');
        break;

    case 'bump':
        if (noSubmit('bumpsubmit')) {
            $template->tid = implode(',', $tids);
            $template->token = $token->create('Thread Admin Options/Bump', (string) min($tids), $vars::NONCE_AYS_EXP);
            $page = $template->process('topicadmin_bump.php');
        } else {
            $core->request_secure('Thread Admin Options/Bump', (string) min($tids));
            foreach ($tids as $tid) {
                $tid = (int) $tid;
                $query = $db->query("SELECT pid FROM " . $vars->tablepre . "posts WHERE tid = $tid ORDER BY dateline DESC, pid DESC LIMIT 1");
                if ($db->num_rows($query) == 1) {
                    $pid = $db->result($query);

                    $fupID = ($forum['type'] == 'sub') ? (int) $forum['fup'] : null;
                    $lastpost = $db->escape($vars->onlinetime . '|' . $vars->self['username'] . '|' . $pid);

                    $sql->setThreadLastpost($tid, $lastpost);
                    $sql->setForumCounts($fid, $lastpost, fup: $fupID);

                    $core->audit($vars->self['username'], $action, $fid, $tid);
                }
                $db->free_result($query);
            }

            $core->message($lang['bumpthreadmsg'], redirect: $full_url . 'forumdisplay.php?fid=' . $fid);
        }
        break;

    case 'empty':
        if (noSubmit('emptysubmit')) {
            $template->tid = implode(',', $tids);
            $template->token = $token->create('Thread Admin Options/Empty', (string) min($tids), $vars::NONCE_AYS_EXP);
            $page = $template->process('topicadmin_empty.php');
        } else {
            $core->request_secure('Thread Admin Options/Empty', (string) min($tids));
            foreach ($tids as $tid) {
                $tid = (int) $tid;
                $query = $db->query("SELECT pid FROM " . $vars->tablepre . "posts WHERE tid = $tid ORDER BY dateline ASC LIMIT 1");
                if ($db->num_rows($query) == 1) {
                    $pid = (int) $db->result($query);
                    $query = $db->query("SELECT author, COUNT(*) AS pidcount FROM " . $vars->tablepre . "posts WHERE tid = $tid AND pid != $pid GROUP BY author");
                    while ($result = $db->fetch_array($query)) {
                        $db->escape_fast($result['author']);
                        $sql->adjustPostCount($result['author'], 0 - (int) $result['pidcount']);
                    }

                    $attach->emptyThread($tid, $pid);  // Must delete attachments before posts!
                    $db->query("DELETE FROM " . $vars->tablepre . "posts WHERE tid = $tid AND pid != $pid");

                    $core->updatethreadcount($tid); // Also updates lastpost
                    $core->audit($vars->self['username'], $action, $fid, $tid);
                }
                $db->free_result($query);
            }
            if ($forum['type'] == 'sub') {
                $core->updateforumcount((int) $forum['fup']);
            }
            $core->updateforumcount($fid);

            $core->message($lang['emptythreadmsg'], redirect: $full_url . 'forumdisplay.php?fid=' . $fid);
        }
        break;

    case 'split':
        $tid = $tids[0];
        $template->tid = $tid;
        if (noSubmit('splitsubmit')) {
            $query = $db->query("SELECT replies FROM " . $vars->tablepre . "threads WHERE tid = $tid");
            if ($db->num_rows($query) == 0) {
                $core->error($lang['textnothread']);
            }
            $replies = (int) $db->result($query, 0);
            $db->free_result($query);
            if ($replies == 0) {
                $core->error($lang['cantsplit']);
            }

            $query = $db->query("SELECT * FROM " . $vars->tablepre . "posts WHERE tid = $tid ORDER BY dateline");
            $posts = '';
            while ($post = $db->fetch_array($query)) {
                $template->pid = $post['pid'];
                $template->author = $post['author'];
                $bbcodeoff = $post['bbcodeoff'];
                $smileyoff = $post['smileyoff'];
                $template->message = $core->postify($post['message'], $smileyoff, $bbcodeoff, allowbbcode: 'no', allowimgcode: 'no');
                $posts .= $template->process('topicadmin_split_row.php');
            }
            $db->free_result($query);
            $template->posts = $posts;
            $template->token = $token->create('Thread Admin Options/Split', (string) min($tids), $vars::NONCE_AYS_EXP);
            $page = $template->process('topicadmin_split.php');
        } else {
            $core->request_secure('Thread Admin Options/Split', (string) min($tids));
            $subject = $validate->postedVar('subject');
            if ($subject == '') {
                $core->error($lang['textnosubject']);
            }

            $threadcreated = false;
            $firstmove = false;
            $query = $db->query("SELECT pid, author, dateline, subject FROM " . $vars->tablepre . "posts WHERE tid = $tid ORDER BY dateline ASC");
            $movecount = 0;
            while ($post = $db->fetch_array($query)) {
                $move = getInt('move' . $post['pid'], 'p');
                if ($move == (int) $post['pid']) {
                    if (! $threadcreated) {
                        $lastpost = $db->escape($vars->onlinetime . '|' . $vars->self['username']); // Temporary value
                        $author = $db->escape($post['author']);
                        $db->query("INSERT INTO " . $vars->tablepre . "threads (fid, subject, icon, lastpost, views, replies, author, closed, topped) VALUES ($fid, '$subject', '', '$lastpost', 0, 0, '$author', '', 0)");
                        $newtid = $db->insert_id();
                        $threadcreated = true;
                    }

                    $newsub = '';
                    if (! $firstmove) {
                        $newsub = ", subject = '$subject'";
                        $firstmove = true;
                    }
                    $db->query("UPDATE " . $vars->tablepre . "posts SET tid = $newtid $newsub WHERE pid = $move");
                    $lastpost = $db->escape($post['dateline'] . '|' . $post['author'] . '|' . $post['pid']);
                    $movecount++;
                } else {
                    $oldlastpost = $db->escape($post['dateline'] . '|' . $post['author'] . '|' . $post['pid']);
                }
            }
            $db->query("UPDATE " . $vars->tablepre . "threads SET replies = $movecount - 1, lastpost = '$lastpost' WHERE tid = '$newtid'");
            $db->query("UPDATE " . $vars->tablepre . "threads SET replies = replies - $movecount, lastpost = '$oldlastpost' WHERE tid = $tid");
            $db->free_result($query);

            $core->audit($vars->self['username'], $action, $fid, $tid);

            if ($forum['type'] == 'sub') {
                $core->updateforumcount((int) $forum['fup']);
            }
            $core->updateforumcount($fid);

            $core->message($lang['splitthreadmsg'], redirect: $full_url . 'forumdisplay.php?fid=' . $fid);
        }
        break;

    case 'merge':
        $tid = $tids[0];
        $template->tid = $tid;
        if (noSubmit('mergesubmit')) {
            $template->token = $token->create('Thread Admin Options/Merge', (string) min($tids), $vars::NONCE_AYS_EXP);
            $page = $template->process('topicadmin_merge.php');
        } else {
            $core->request_secure('Thread Admin Options/Merge', (string) min($tids));
            if ($othertid == 0) {
                $core->error($lang['invalidtid']);
            } elseif ($tid == $othertid) {
                $core->error($lang['cannotmergesamethread']);
            }

            $queryadd1 = $db->query("SELECT t.replies, t.fid, f.type, f.fup FROM " . $vars->tablepre . "threads AS t LEFT JOIN " . $vars->tablepre . "forums AS f USING (fid) WHERE t.tid = '$othertid'");

            if ($db->num_rows($queryadd1) == 0) {
                $db->free_result($queryadd1);
                error($lang['invalidtid'], false);
            }
            $otherthread = $db->fetch_array($queryadd1);
            $db->free_result($queryadd1);
            $replyadd = intval($otherthread['replies']) + 1;
            $otherfid = (int) $otherthread['fid'];

            $db->query("UPDATE " . $vars->tablepre . "posts SET tid = $tid, fid = '$fid' WHERE tid = '$othertid'");

            $db->query("UPDATE " . $vars->tablepre . "threads SET closed = 'moved|$tid' WHERE closed = 'moved|$othertid'");

            $db->query("DELETE FROM " . $vars->tablepre . "threads WHERE tid = '$othertid'");

            $sql->deleteVotesByTID([$othertid]);

            $db->query("UPDATE " . $vars->tablepre . "favorites AS f "
                     . "INNER JOIN " . $vars->tablepre . "members AS m ON m.username = f.username "
                     . "INNER JOIN ( "
                     . " SELECT username, COUNT(*) AS fcount "
                     . " FROM " . $vars->tablepre . "favorites AS f2 "
                     . " WHERE tid = $tid "
                     . " GROUP BY username "
                     . ") AS query2 ON m.username = query2.username "
                     . "SET f.tid = $tid "
                     . "WHERE f.tid = '$othertid' AND query2.fcount = 0");
            $db->query("DELETE FROM " . $vars->tablepre . "favorites WHERE tid = '$othertid'");

            $query = $db->query("SELECT subject, author, icon FROM " . $vars->tablepre . "posts WHERE tid = $tid OR tid = '$othertid' ORDER BY dateline, pid ASC LIMIT 1");
            $thread = $db->fetch_array($query);
            $db->free_result($query);
            $query = $db->query("SELECT author, dateline, pid FROM " . $vars->tablepre . "posts WHERE tid = $tid ORDER BY dateline DESC LIMIT 1");
            $lastpost = $db->fetch_array($query);
            $db->free_result($query);
            $db->escape_fast($thread['author']);
            $db->escape_fast($thread['subject']);
            $db->escape_fast($lastpost['author']);
            $db->query("UPDATE " . $vars->tablepre . "threads SET replies=replies+'$replyadd', subject='{$thread['subject']}', icon='{$thread['icon']}', author='{$thread['author']}', lastpost='{$lastpost['dateline']}|{$lastpost['author']}|{$lastpost['pid']}' WHERE tid=$tid");

            $core->audit($vars->self['username'], $action, $fid, $tid);

            if ($forum['type'] == 'sub') {
                $core->updateforumcount((int) $forum['fup']);
            }
            if ($otherthread['type'] == 'sub') {
                if ($forum['type'] == 'sub') {
                    $doupdate = ($otherthread['fup'] != $forum['fup']);
                } else {
                    $doupdate = ($otherthread['fup'] != $forum['fid']);
                }
                if ($doupdate) {
                    $core->updateforumcount((int) $otherthread['fup']);
                }
            }
            $core->updateforumcount($fid);
            $core->updateforumcount($otherfid);

            $core->message($lang['mergethreadmsg'], redirect: $full_url . 'forumdisplay.php?fid=' . $fid);
        }
        break;

    case 'threadprune':
        $tid = $tids[0];
        $template->tid = $tid;
        if (noSubmit('threadprunesubmit')) {
            $query = $db->query("SELECT replies FROM " . $vars->tablepre . "threads WHERE tid = $tid");
            if ($db->num_rows($query) == 0) {
                $core->error($lang['textnothread']);
            }
            $replies = (int) $db->result($query, 0);
            $db->free_result($query);

            if ($replies == 0) {
                $core->error($lang['cantthreadprune']);
            }

            $posts = '';
            $query = $db->query("SELECT p.*, m.status FROM " . $vars->tablepre . "posts p LEFT JOIN " . $vars->tablepre . "members m ON (m.username = p.author) WHERE tid = $tid ORDER BY dateline");
            while ($post = $db->fetch_array($query)) {
                if (X_SADMIN || $vars->settings['allowrankedit'] == 'off') {
                    $template->disablePost = '';
                } elseif ($vars->status_enum[$post['status']] < $vars->status_enum[$vars->self['status']]) {
                    $template->disablePost = 'disabled="disabled"';
                } else {
                    $template->disablePost = '';
                }
                $template->pid = $post['pid'];
                $template->author = $post['author'];
                $bbcodeoff = $post['bbcodeoff'];
                $smileyoff = $post['smileyoff'];
                $template->message = $core->postify($post['message'], $smileyoff, $bbcodeoff, allowbbcode: 'no', allowimgcode: 'no');
                $posts .= $template->process('topicadmin_threadprune_row.php');
            }
            $db->free_result($query);
            $template->posts = $posts;
            $template->token = $token->create('Thread Admin Options/Prune', (string) min($tids), $vars::NONCE_AYS_EXP);
            $page = $template->process('topicadmin_threadprune.php');
        } else {
            $core->request_secure('Thread Admin Options/Prune', (string) min($tids));
            $postcount = (int) $db->result($db->query("SELECT COUNT(*) FROM " . $vars->tablepre . "posts WHERE tid = $tid"));
            $delcount = 0;
            foreach ($_POST as $key => $val) {
                if (substr($key, 0, 4) == 'move') {
                    $delcount++;
                }
            }
            if ($delcount >= $postcount) {
                $core->error($lang['cantthreadprune']);
            }

            $query = $db->query("SELECT m.status, p.author, p.pid FROM " . $vars->tablepre . "posts p LEFT JOIN " . $vars->tablepre . "members m ON (m.username=p.author) WHERE p.tid=$tid");
            while ($post = $db->fetch_array($query))    {
                if (X_SADMIN || $vars->settings['allowrankedit'] == 'off') {
                    // proceed
                } elseif ($vars->status_enum[$post['status']] < $vars->status_enum[$vars->self['status']]) {
                    continue;
                }
                $move = "move" . $post['pid'];
                $move = getInt($move, 'p');
                if (! empty($move)) {
                    $sql->adjustPostCount($post['author'], -1);
                    $db->query("DELETE FROM " . $vars->tablepre . "posts WHERE pid = $move");
                    $attach->deleteByPost($move);
                    $db->query("UPDATE " . $vars->tablepre . "threads SET replies = replies - 1 WHERE tid = $tid");
                }
            }
            $db->free_result($query);

            $firstauthor = $db->result($db->query("SELECT author FROM " . $vars->tablepre . "posts WHERE tid = $tid ORDER BY dateline ASC LIMIT 1"));
            $db->escape_fast($firstauthor);

            $query = $db->query("SELECT pid, author, dateline FROM " . $vars->tablepre . "posts WHERE tid = $tid ORDER BY dateline DESC LIMIT 1");
            $lastpost = $db->fetch_array($query);
            $db->free_result($query);
            $db->escape_fast($lastpost['author']);
            $db->query("UPDATE " . $vars->tablepre . "threads SET author='$firstauthor', lastpost='{$lastpost['dateline']}|{$lastpost['author']}|{$lastpost['pid']}' WHERE tid=$tid");

            if ($forum['type'] == 'sub') {
                $core->updateforumcount((int) $forum['fup']);
            }
            $core->updateforumcount($fid);

            $core->audit($vars->self['username'], $action, $fid, $tid);

            $core->message($lang['complete_threadprune'], redirect: $full_url . 'forumdisplay.php?fid=' . $fid);
        }
        break;

    case 'copy':
        if (noSubmit('copysubmit')) {
            $template->tid = implode(',', $tids);
            $template->forumselect = $core->forumList('newfid', allowall: false);
            $template->token = $token->create('Thread Admin Options/Copy', (string) min($tids), $vars::NONCE_AYS_EXP);
            $page = $template->process('topicadmin_copy.php');
        } else {
            $core->request_secure('Thread Admin Options/Copy', (string) min($tids));
            if (! formInt('newfid')) {
                $core->error($lang['privforummsg']);
            }

            $newfid = getRequestInt('newfid');

            $otherforum = $forums->getForum($newfid);
            if ($otherforum === null) {
                $core->error($lang['textnoforum']);
            }

            if (! $core->modcheckForum($newfid)) {
                $core->error($lang['notpermitted']);
            }

            foreach ($tids as $tid) {
                $tid = (int) $tid;
                $thread = $db->fetch_array($db->query("SELECT * FROM " . $vars->tablepre . "threads WHERE tid = $tid"));

                $thread['fid'] = $newfid;
                $thread['views'] = (int) $thread['views'];
                $thread['replies'] = (int) $thread['replies'];
                $thread['topped'] = (int) $thread['topped'];
                unset($thread['tid']);
                $thread['pollopts'] = 0; // This routine doesn't copy the poll.
                
                $newtid = $sql->addThread($thread);

                $query = $db->query("SELECT * FROM " . $vars->tablepre . "posts WHERE tid = $tid ORDER BY dateline, pid ASC");
                while ($post = $db->fetch_array($query)) {
                    $oldPid = (int) $post['pid'];
                    $post['fid'] = $newfid;
                    $post['tid'] = $newtid;
                    $post['dateline'] = (int) $post['dateline'];
                    unset($post['pid']);

                    $newpid = $sql->addPost($post);

                    $attach->copyByPost($oldPid, $newpid);
                }

                $query = $db->query("SELECT author, COUNT(*) AS pidcount FROM " . $vars->tablepre . "posts WHERE tid = $tid GROUP BY author");
                while ($result = $db->fetch_array($query)) {
                    $sql->adjustPostCount($result['author'], (int) $result['pidcount']);
                }
                $db->free_result($query);

                $core->audit($vars->self['username'], $action, $fid, $tid);

                if ($otherforum['type'] == 'sub') {
                    $core->updateforumcount((int) $otherforum['fup']);
                }
                $core->updateforumcount($newfid);
            }

            $core->message($lang['copythreadmsg'], redirect: $full_url . 'forumdisplay.php?fid=' . $fid);
        }
        break;
}

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $page, $footer;
