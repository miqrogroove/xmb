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

declare(strict_types=1);

namespace XMB;

require './header.php';

$attachSvc = \XMB\Services\attach();
$core = \XMB\Services\core();
$db = \XMB\Services\db();
$forums = \XMB\Services\forums();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$tokenSvc = \XMB\Services\token();
$tran = \XMB\Services\translation();
$validate = \XMB\Services\validate();
$vars = \XMB\Services\vars();

$lang = &$vars->lang;
$onlinetime = $vars->onlinetime;
$SETTINGS = &$vars->settings;

$core->nav("<a href='" . $vars->full_url . "quarantine.php'>{$lang['moderation_meta_name']}</a>");

if (! X_SMOD) {
    header('HTTP/1.0 403 Forbidden');
    $core->error($lang['notpermitted']);
}

$quarantine = true;
$https_only = 'on' == $SETTINGS['images_https_only'];

$template->process('header.php', echo: true);
$template->process('quarantine_wrap.php', echo: true);

$action = getPhpInput('action', sourcearray: 'g');

if ($action == 'viewforum' || $action == 'viewuser') {
    $ranks = new \XMB\Ranks($sql, $vars);
    $render = new \XMB\ThreadRender($core, $ranks, $sql, $vars);

    if ('viewuser' == $action) {
        $user = $validate->postedVar('u', dbescape: false, sourcearray: 'g');
        $dbuser = $db->escape($user);
        $member = $sql->getMemberByName($user);
        if (empty($member)) {
            $core->error($lang['nomember'], showheader: false, append: '</td></tr></table></td></tr></table>');
        }

        echo "<h2>{$lang['moderation_new_member']}: {$member['username']}</h2>\n";
    } else {
        $fid = getInt('fid');
        $forum = $forums->getForum($fid);
        if (false === $forum) {
            $core->error($lang['textnoforum'], showheader: false, append: '</td></tr></table></td></tr></table>');
        }

        echo "<h2>" . fnameOut($forum['name']) . "</h2>\n";
        
        $token = $tokenSvc->create('Quarantine Panel/Anonymous Queue', 'Approve or Delete', $vars::NONCE_AYS_EXP);
    }

    $template->thisbg = $vars->theme['altbg2'];

    if ('viewuser' == $action) {
        $result = $db->query("SELECT * FROM " . $vars->tablepre . "hold_threads WHERE author = '$dbuser'");
    } else {
        $result = $db->query("SELECT * FROM " . $vars->tablepre . "hold_threads WHERE fid = $fid AND author = 'Anonymous' ORDER BY lastpost ASC");        
    }

    $threadcount = $db->num_rows($result);

    if ($threadcount > 0) {
        echo "<h3>{$lang['moderation_new_threads']}</h3>\n";
        while ($thread = $db->fetch_array($result)){
            $tid = (int) $thread['tid'];
            $fid = (int) $thread['fid'];
            $forum = $forums->getForum($fid);
            $thread['subject'] = shortenString($core->rawHTMLsubject(stripslashes($thread['subject'])));
            $template->subject = $thread['subject'];

            if ('viewforum' == $action) {
                $approve = "<form action='?action=approvethread&amp;tid=$tid' method='post' style='float:left;'><input type='submit' value='{$lang['moderation_approve']}' /><input type='hidden' name='token' value='$token' /></form>";
                $delete  = "<form action='?action=deletethread&amp;tid=$tid' method='post' style='float:right;'><input type='submit' value='{$lang['moderation_delete']}' /><input type='hidden' name='token' value='$token' /></form>";
            }

            $template->buttoncode = '';
            $template->pollhtml = '';
            $poll = '';
            $vote_id = 0;
            $voted = 0;

            if ('1' === $thread['pollopts']) {
                $vote_id = $sql->getPollId($tid, true);
            }

            if ($vote_id > 0) {
                $template->results = '- [<a href=""><font color="' . $vars->theme['cattext'] . '">' . $lang['viewresults'] . '</font></a>]';
                $template->pollhtml = $render->pollOptionsVotable($vote_id, $quarantine, $forum['allowsmilies'], $forum['allowbbcode']);
                $template->token = '';
                $template->thread = $thread;
                $template->fid = $fid;
                $template->tid = $tid;
                $template->process('viewthread_poll.php', echo: true);
            }

            $template->process('quarantine_post_wrap.php', echo: true);

            if ('viewuser' == $action) {
                $result2 = $db->query("SELECT * FROM " . $vars->tablepre . "hold_posts WHERE newtid = $tid");
                $post = array_merge($db->fetch_array($result2), $member);
            } else {
                $result2 = $db->query("SELECT * FROM " . $vars->tablepre . "hold_posts AS p LEFT JOIN " . $vars->tablepre . "members AS m ON m.username = p.author WHERE p.newtid = $tid");
                $post = $db->fetch_array($result2);
            }
            $db->free_result($result2);

            $render->preparePost($post, $template);

            if ('viewuser' == $action) {
                $template->profile = '';
            } else {
                $template->profile = $approve . $delete;
                $template->author = $lang['textanonymous'];
                $template->profilelink = $lang['textanonymous'];
                $post['usesig'] = 'no';
            }
            $template->site = '';
            $template->search = '';
            $template->u2u = '';
            $template->ip = '';
            $template->repquote = '';
            $template->reportlink = '';
            $template->edit = '';

            if ($forum['attachstatus'] == 'on') {
                $attachments = $sql->getOrphanedAttachments($quarantine, (int) $post['pid']);
            }

            $render->preparePostBody($post, $forum, $attachments, $quarantine, $template);

            if ($post['subject'] == '') {
                $template->linktitle = $thread['subject'];
            }
            
            $template->process('viewthread_post.php', echo: true);
            echo "</table></td></tr></table><br />\n";
        }
    }
    $db->free_result($result);

    if ('viewuser' == $action) {
        $result = $db->query("SELECT * FROM " . $vars->tablepre . "hold_posts WHERE author = '$dbuser' AND tid != 0");
    } else {
        $result = $db->query("SELECT * FROM " . $vars->tablepre . "hold_posts AS p LEFT JOIN " . $vars->tablepre . "members AS m ON m.username = p.author WHERE p.fid = $fid AND p.author = 'Anonymous' AND p.tid != 0 ORDER BY p.tid, p.dateline");        
    }

    $replycount = $db->num_rows($result);

    if ($replycount > 0) {
        echo "<h3>{$lang['moderation_new_replies']}</h3>\n";
        $lasttid = '0';
        while ($post = $db->fetch_array($result)){
            $tid = (int) $post['tid'];
            $fid = (int) $post['fid'];
            $forum = $forums->getForum($fid);
            if ('viewforum' == $action) {
                $approve = "<form action='?action=approvereply&amp;pid={$post['pid']}' method='post' style='float:left;'><input type='submit' value='{$lang['moderation_approve']}' /><input type='hidden' name='token' value='$token' /></form>";
                $delete  = "<form action='?action=deletereply&amp;pid={$post['pid']}' method='post' style='float:right;'><input type='submit' value='{$lang['moderation_delete']}' /><input type='hidden' name='token' value='$token' /></form>";

                if ($tid !== $lasttid) {
                    if ('0' !== $lasttid) echo "</table></td></tr></table><br />\n";
                    $template->thisbg = $vars->theme['altbg2'];
                }
            }

            if ('viewuser' == $action || $tid !== $lasttid) {
                $lasttid = $tid;
                $result2 = $db->query("SELECT * FROM " . $vars->tablepre . "threads WHERE tid = $tid");
                $thread = $db->fetch_array($result2);
                $db->free_result($result2);
                $thread['subject'] = shortenString($core->rawHTMLsubject(stripslashes($thread['subject'])));
                $template->subject = $thread['subject'];
                $template->process('quarantine_post_wrap.php', echo: true);
            }

            if ('viewuser' == $action) {
                $post = array_merge($post, $member);
            }

            $render->preparePost($post, $template);

            //$template->showtitle = $post['status'];

            if ('viewuser' == $action) {
                $template->profile = '';
            } else {
                $template->profile = $approve . $delete;
                $post['author'] = $lang['textanonymous'];
                $post['usesig'] = 'no';
                $template->profilelink = $lang['textanonymous'];
            }
            $template->site = '';
            $template->search = '';
            $template->u2u = '';
            $template->ip = '';
            $template->repquote = '';
            $template->reportlink = '';
            $template->edit = '';

            if ($forum['attachstatus'] == 'on') {
                $attachments = $sql->getOrphanedAttachments($quarantine, (int) $post['pid']);
            }

            $render->preparePostBody($post, $forum, $attachments, $quarantine, $template);

            if ($post['subject'] == '') {
                $template->linktitle = $thread['subject'];
            }

            $template->process('viewthread_post.php', echo: true);

            if ('viewuser' == $action) {
                echo "</table></td></tr></table><br />\n";
            } else {
                if ($template->thisbg == $vars->theme['altbg2']) {
                    $template->thisbg = $vars->theme['altbg1'];
                } else {
                    $template->thisbg = $vars->theme['altbg2'];
                }
            }
        } //wend
        if ('viewforum' == $action) {
            echo "</table></td></tr></table><br />\n";
        }
    }
    $db->free_result($result);

    if (0 == $threadcount && 0 == $replycount) {
        echo "<p>{$lang['noresults']}</p>\n";
        if ('viewuser' == $action && 'yes' == $member['waiting_for_mod'] && (int) $member['postnum'] > 0) {
            // Unexpected desync of member from quarantine content.
            echo "<h3>{$lang['moderation_actions']}</h3>\n";
            echo "<form action='quarantine.php?action=modays' method='post'>\n";
            echo "<input type='hidden' name='u' value='{$member['username']}' />\n";
            echo "<input type='submit' name='approveall' value='{$lang['moderation_approve_all']}' />\n";
            echo "</form>\n";
        }
    } elseif ('viewuser' == $action) {
        echo "<h3>{$lang['moderation_actions']}</h3>\n";
        echo "<form action='quarantine.php?action=modays' method='post'>\n";
        echo "<input type='hidden' name='u' value='{$member['username']}' />\n";
        echo "<input type='submit' name='approveall' value='{$lang['moderation_approve_all']}' />\n";
        echo "<input type='submit' name='deleteall' value='{$lang['moderation_delete_all']}' />\n";
        if (X_ADMIN) {
            echo "<input type='submit' name='deleteban' value='{$lang['moderation_delete_ban']}' />\n";
        }
        echo "</form>\n";
    }

} elseif ($action == 'modays') {
    $member = $validate->postedVar('u', dbescape: false);
    $sub = $validate->postedVar('sub');
    if (onSubmit('approveall')) {
        $act = 'approveall';
        $phrase = 'moderation_ays_appr';
    } elseif (onSubmit('deleteall')) {
        $act = 'deleteall';
        $phrase = 'moderation_ays_dele';
    } elseif (onSubmit('deleteban') && X_ADMIN) {
        $act = 'deleteban';
        $phrase = 'moderation_ays_dele';
    } else {
        $core->error($lang['textnoaction'], showheader: false, append: '</td></tr></table></td></tr></table>');
    }
    $phrase = str_replace('$user', $member, $lang[$phrase]);
    ?>
    <tr bgcolor="<?php echo $vars->theme['altbg2']; ?>" class="ctrtablerow"><td><?php echo $phrase; ?><br />
    <form action="quarantine.php?action=<?php echo $act; ?>" method="post">
      <input type="hidden" name="token" value="<?php echo $tokenSvc->create("Quarantine Panel/$act", $member, $vars::NONCE_AYS_EXP); ?>" />
      <input type="hidden" name="u" value="<?php echo $member; ?>" />
      <input type="submit" name="yessubmit" value="<?php echo $lang['textyes']; ?>" /> -
      <input type="submit" name="nosubmit" value="<?php echo $lang['textno']; ?>" />
    </form></td></tr>
    <?php
} elseif ($action == 'approveall') {
    $member = $validate->postedVar('u');
    $rawmember = $validate->postedVar('u', dbescape: false);
    $core->request_secure("Quarantine Panel/approveall", $rawmember);

    if (onSubmit('yessubmit')) {
        $count = $sql->countPosts($quarantine, 0, $rawmember);
        $thatime = $onlinetime - $count;
        $result = $db->query("SELECT * FROM " . $vars->tablepre . "hold_threads WHERE author='$member' ORDER BY lastpost ASC");
        while ($thread = $db->fetch_array($result)) {
            $thatime++;
            $forum = $forums->getForum((int) $thread['fid']);
            $db->query(
                "INSERT INTO " . $vars->tablepre . "threads " .
                "      (fid, subject, icon,           lastpost, views, replies, author, closed, topped, pollopts) " .
                "SELECT fid, subject, icon, '$thatime|$member', views, replies, author, closed, topped, pollopts " .
                "FROM " . $vars->tablepre . "hold_threads WHERE tid = {$thread['tid']}"
            );
            $newtid = $db->insert_id();
            $oldpid = (int) $db->result($db->query("SELECT pid FROM " . $vars->tablepre . "hold_posts WHERE newtid = {$thread['tid']}"));
            $db->query(
                "INSERT INTO " . $vars->tablepre . "posts " .
                "      (fid,     tid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff) " .
                "SELECT fid, $newtid, author, message, subject, $thatime, icon, usesig, useip, bbcodeoff, smileyoff " .
                "FROM " . $vars->tablepre . "hold_posts WHERE pid = $oldpid"
            );
            $newpid = $db->insert_id();
            $db->query("UPDATE " . $vars->tablepre . "threads SET lastpost=concat(lastpost, '|$newpid') WHERE tid = $newtid");
            $where = "WHERE fid={$thread['fid']}";
            if ($forum['type'] == 'sub') {
                $where .= " OR fid={$forum['fup']}";
            }
            $db->query("UPDATE " . $vars->tablepre . "forums SET lastpost='$thatime|$member|$newpid', threads=threads+1, posts=posts+1 $where");
            unset($where);
            $db->query("UPDATE " . $vars->tablepre . "members SET postnum=postnum+1 WHERE username='$member'");
            $attachSvc->approve($oldpid, $newpid);
            if (intval($thread['pollopts']) != 0) {
                $oldpoll = $sql->getPollId($thread['tid'], true);
                if ($oldpoll !== 0) {
                    $newpoll = $sql->addVoteDesc($newtid);
                    $db->query(
                        "INSERT INTO " . $vars->tablepre . "vote_results " .
                        "      ( vote_id, vote_option_id, vote_option_text, vote_result) " .
                        "SELECT $newpoll, vote_option_id, vote_option_text, vote_result " .
                        "FROM " . $vars->tablepre . "hold_vote_results WHERE vote_id = $oldpoll"
                    );
                    $sql->deleteVotesByTID([$oldpoll], quarantine: true);
                }
            }
            $count2 = (int) $db->result($db->query("SELECT COUNT(*) FROM " . $vars->tablepre . "hold_favorites WHERE tid={$thread['tid']} AND username='$member' AND type='subscription'"), 0);
            if ($count2 != 0) {
                $db->query("INSERT INTO " . $vars->tablepre . "favorites (tid, username, type) VALUES ($newtid, '$member', 'subscription')");
                $db->query("DELETE FROM " . $vars->tablepre . "hold_favorites WHERE tid={$thread['tid']}");
            }
            $db->query("DELETE FROM " . $vars->tablepre . "hold_posts WHERE pid = $oldpid");
            $db->query("DELETE FROM " . $vars->tablepre . "hold_threads WHERE tid = {$thread['tid']}");
        }
        $db->free_result($result);
        $result = $db->query("SELECT * FROM " . $vars->tablepre . "hold_posts WHERE author='$member' ORDER BY dateline ASC");
        while ($post = $db->fetch_array($result)) {
            $thatime++;
            $forum = $forums->getForum((int) $post['fid']);
            $db->query(
                "INSERT INTO " . $vars->tablepre . "posts " .
                "      (fid, tid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff) " .
                "SELECT fid, tid, author, message, subject, $thatime, icon, usesig, useip, bbcodeoff, smileyoff " .
                "FROM " . $vars->tablepre . "hold_posts WHERE pid = {$post['pid']}"
            );
            $newpid = $db->insert_id();
            $db->query("UPDATE " . $vars->tablepre . "threads SET lastpost='$thatime|$member|$newpid', replies=replies+1 WHERE tid = {$post['tid']}");
            $where = "WHERE fid={$post['fid']}";
            if ($forum['type'] == 'sub') {
                $where .= " OR fid={$forum['fup']}";
            }
            $db->query("UPDATE " . $vars->tablepre . "forums SET lastpost='$thatime|$member|$newpid', threads=threads+1, posts=posts+1 $where");
            unset($where);
            $db->query("UPDATE " . $vars->tablepre . "members SET postnum=postnum+1 WHERE username='$member'");
            $attachSvc->approve((int) $post['pid'], $newpid);
            $db->query("DELETE FROM " . $vars->tablepre . "hold_posts WHERE pid = {$post['pid']}");

            $result2 = $db->query("SELECT subject FROM " . $vars->tablepre . "threads WHERE tid = {$post['tid']}");
            $thread = $db->fetch_array($result2);
            $db->free_result($result2);
            $threadname = $core->rawHTMLsubject(stripslashes($thread['subject']));

            $query = $db->query("SELECT COUNT(*) FROM " . $vars->tablepre . "posts WHERE pid <= $newpid AND tid={$post['tid']}");
            $posts = $db->result($query,0);
            $db->free_result($query);

            $lang2 = $tran->loadPhrases(['charset','textsubsubject','textsubbody']);
            $viewperm = $core->getOneForumPerm($forum, $vars::PERMS_RAWVIEW);
            $date = $db->result($db->query("SELECT dateline FROM " . $vars->tablepre . "posts WHERE tid={$post['tid']} AND pid < $newpid ORDER BY dateline DESC LIMIT 1"), 0);
            $subquery = $db->query("SELECT m.email, m.lastvisit, m.ppp, m.status, m.langfile "
                                 . "FROM " . $vars->tablepre . "favorites f "
                                 . "INNER JOIN " . $vars->tablepre . "members m USING (username) "
                                 . "WHERE f.type = 'subscription' AND f.tid = {$post['tid']} AND m.username != '$member' AND m.lastvisit >= $date");
            while ($subs = $db->fetch_array($subquery)) {
                if ($viewperm < $vars->status_enum[$subs['status']]) {
                    continue;
                }

                if ((int) $subs['ppp'] < 1) {
                    $subs['ppp'] = $posts;
                }

                $translate = $lang2[$subs['langfile']];
                $topicpages = quickpage($posts, $subs['ppp']);
                $topicpages = ($topicpages == 1) ? '' : '&page='.$topicpages;
                $threadurl = $vars->full_url . 'viewthread.php?tid='.$post['tid'].$topicpages.'#pid'.$newpid;
                $rawsubject = htmlspecialchars_decode($threadname, ENT_QUOTES);
                $rawusername = htmlspecialchars_decode($member, ENT_QUOTES);
                $rawemail = htmlspecialchars_decode($subs['email'], ENT_QUOTES);
                $title = "$rawsubject ({$translate['textsubsubject']})";
                $body = "$rawusername {$translate['textsubbody']} \n$threadurl";
                xmb_mail($rawemail, $title, $body, $translate['charset']);
            }
            $db->free_result($subquery);
        }
        $db->free_result($result);
        $sql->endMemberQuarantine($rawmember);
        $core->moderate_cleanup($rawmember);
        echo $lang['moderation_approved'];
    } else {
        echo $lang['moderation_canceled'];
    }

} elseif ($action == 'deleteall' || $action == 'deleteban') {
    $member = $validate->postedVar('u');
    $rawmember = $validate->postedVar('u', dbescape: false);
    $core->request_secure("Quarantine Panel/$action", $rawmember);

    if (onSubmit('yessubmit')) {
        $result = $db->query("SELECT * FROM " . $vars->tablepre . "hold_threads WHERE author='$member' ORDER BY lastpost ASC");
        while ($thread = $db->fetch_array($result)) {
            $oldpid = (int) $db->result($db->query("SELECT pid FROM " . $vars->tablepre . "hold_posts WHERE newtid = {$thread['tid']}"));
            $db->query("DELETE FROM " . $vars->tablepre . "hold_attachments WHERE pid = $oldpid");
            if (intval($thread['pollopts']) != 0) {
                $oldpoll = $sql->getPollId($thread['tid'], true);
                if ($oldpoll !== 0) {
                    $sql->deleteVotesByTID([$oldpoll], quarantine: true);
                }
            }
            $db->query("DELETE FROM " . $vars->tablepre . "hold_favorites WHERE tid={$thread['tid']}");
            $db->query("DELETE FROM " . $vars->tablepre . "hold_posts WHERE pid = $oldpid");
            $db->query("DELETE FROM " . $vars->tablepre . "hold_threads WHERE tid = {$thread['tid']}");
        }
        $db->free_result($result);
        $result = $db->query("SELECT * FROM " . $vars->tablepre . "hold_posts WHERE author='$member' ORDER BY dateline ASC");
        while ($post = $db->fetch_array($result)) {
            $db->query("DELETE FROM " . $vars->tablepre . "hold_attachments WHERE pid = {$post['pid']}");
            $db->query("DELETE FROM " . $vars->tablepre . "hold_posts WHERE pid = {$post['pid']}");
        }
        $db->free_result($result);
        $core->moderate_cleanup($rawmember);
        if ('deleteban' == $action && X_ADMIN) {
            $db->query("UPDATE " . $vars->tablepre . "members SET status = 'Banned', customstatus = 'Spammer' WHERE username = '$member'");
        }
        echo $lang['moderation_deleted'];
    } else {
        echo $lang['moderation_canceled'];
    }
} elseif ($action == 'approvethread') {
    $core->request_secure('Quarantine Panel/Anonymous Queue', 'Approve or Delete');
    $oldtid = getInt('tid');
    $result = $db->query("SELECT * FROM " . $vars->tablepre . "hold_threads WHERE tid=$oldtid");
    if ($db->num_rows($result) == 0) {
        $core->error($lang['textnoforum'], showheader: false, append: '</td></tr></table></td></tr></table>');
    }
    $thread = $db->fetch_array($result);
    $db->free_result($result);

    $forum = $forums->getForum((int) $thread['fid']);
    $member = $db->escape($thread['author']);
    $result = $db->query("SELECT * FROM " . $vars->tablepre . "hold_posts WHERE newtid = {$thread['tid']}");
    $post = $db->fetch_array($result);
    $db->free_result($result);
    $oldpid = (int) $post['pid'];
    $db->query(
        "INSERT INTO " . $vars->tablepre . "threads " .
        "      (fid, subject, icon,              lastpost, views, replies, author, closed, topped, pollopts) " .
        "SELECT fid, subject, icon, '$onlinetime|$member', views, replies, author, closed, topped, pollopts " .
        "FROM " . $vars->tablepre . "hold_threads WHERE tid = {$thread['tid']}"
    );
    $newtid = $db->insert_id();
    $db->query(
        "INSERT INTO " . $vars->tablepre . "posts " .
        "      (fid,     tid, author, message, subject,    dateline, icon, usesig, useip, bbcodeoff, smileyoff) " .
        "SELECT fid, $newtid, author, message, subject, $onlinetime, icon, usesig, useip, bbcodeoff, smileyoff " .
        "FROM " . $vars->tablepre . "hold_posts WHERE pid = $oldpid"
    );
    $newpid = $db->insert_id();
    $db->query("UPDATE " . $vars->tablepre . "threads SET lastpost=concat(lastpost, '|$newpid') WHERE tid = $newtid");
    $where = "WHERE fid={$thread['fid']}";
    if ($forum['type'] == 'sub') {
        $where .= " OR fid={$forum['fup']}";
    }
    $db->query("UPDATE " . $vars->tablepre . "forums SET lastpost='$onlinetime|$member|$newpid', threads=threads+1, posts=posts+1 $where");
    unset($where);
    $db->query("UPDATE " . $vars->tablepre . "members SET postnum=postnum+1 WHERE username='$member'");
    $attachSvc->approve($oldpid, $newpid);
    if (intval($thread['pollopts']) != 0) {
        $oldpoll = $sql->getPollId($thread['tid'], true);
        if ($oldpoll !== 0) {
            $newpoll = $sql->addVoteDesc($newtid);
            $db->query(
                "INSERT INTO " . $vars->tablepre . "vote_results " .
                "      ( vote_id, vote_option_id, vote_option_text, vote_result) " .
                "SELECT $newpoll, vote_option_id, vote_option_text, vote_result " .
                "FROM " . $vars->tablepre . "hold_vote_results WHERE vote_id = $oldpoll"
            );
            $sql->deleteVotesByTID([$oldpoll], quarantine: true);
        }
    }
    $count2 = (int) $db->result($db->query("SELECT COUNT(*) FROM " . $vars->tablepre . "hold_favorites WHERE tid={$thread['tid']} AND username='$member' AND type='subscription'"), 0);
    if ($count2 != 0) {
        $db->query("INSERT INTO " . $vars->tablepre . "favorites (tid, username, type) VALUES ($newtid, '$member', 'subscription')");
        $db->query("DELETE FROM " . $vars->tablepre . "hold_favorites WHERE tid={$thread['tid']}");
    }
    $db->query("DELETE FROM " . $vars->tablepre . "hold_posts WHERE pid = $oldpid");
    $db->query("DELETE FROM " . $vars->tablepre . "hold_threads WHERE tid = {$thread['tid']}");

    $core->moderate_cleanup($thread['author']);
    echo $lang['moderation_approved'];
} elseif ($action == 'approvereply') {
    $core->request_secure('Quarantine Panel/Anonymous Queue', 'Approve or Delete');
    $oldpid = getInt('pid');
    $result = $db->query("SELECT * FROM " . $vars->tablepre . "hold_posts WHERE pid = $oldpid");
    if ($db->num_rows($result) == 0) {
        $core->error($lang['textnoforum'], showheader: false, append: '</td></tr></table></td></tr></table>');
    }
    $post = $db->fetch_array($result);
    $db->free_result($result);

    $forum = $forums->getForum((int) $post['fid']);
    $member = $db->escape($post['author']);
    $db->query(
        "INSERT INTO " . $vars->tablepre . "posts " .
        "      (fid, tid, author, message, subject,    dateline, icon, usesig, useip, bbcodeoff, smileyoff) " .
        "SELECT fid, tid, author, message, subject, $onlinetime, icon, usesig, useip, bbcodeoff, smileyoff " .
        "FROM " . $vars->tablepre . "hold_posts WHERE pid = {$post['pid']}"
    );
    $newpid = $db->insert_id();
    $db->query("UPDATE " . $vars->tablepre . "threads SET lastpost='$onlinetime|$member|$newpid', replies=replies+1 WHERE tid = {$post['tid']}");
    $where = "WHERE fid={$post['fid']}";
    if ($forum['type'] == 'sub') {
        $where .= " OR fid={$forum['fup']}";
    }
    $db->query("UPDATE " . $vars->tablepre . "forums SET lastpost='$onlinetime|$member|$newpid', threads=threads+1, posts=posts+1 $where");
    unset($where);
    $db->query("UPDATE " . $vars->tablepre . "members SET postnum=postnum+1 WHERE username='$member'");
    $attachSvc->approve((int) $post['pid'], $newpid);
    $db->query("DELETE FROM " . $vars->tablepre . "hold_posts WHERE pid = {$post['pid']}");

    $result2 = $db->query("SELECT subject FROM " . $vars->tablepre . "threads WHERE tid = {$post['tid']}");
    $thread = $db->fetch_array($result2);
    $db->free_result($result2);
    $threadname = $core->rawHTMLsubject(stripslashes($thread['subject']));

    $query = $db->query("SELECT COUNT(pid) FROM " . $vars->tablepre . "posts WHERE pid <= $newpid AND tid={$post['tid']}");
    $posts = $db->result($query,0);
    $db->free_result($query);

    $lang2 = $tran->loadPhrases(['charset','textsubsubject','textsubbody']);
    $viewperm = $core->getOneForumPerm($forum, $vars::PERMS_RAWVIEW);
    $date = $db->result($db->query("SELECT dateline FROM " . $vars->tablepre . "posts WHERE tid={$post['tid']} AND pid < $newpid ORDER BY dateline DESC LIMIT 1"), 0);
    $subquery = $db->query("SELECT m.email, m.lastvisit, m.ppp, m.status, m.langfile "
                         . "FROM " . $vars->tablepre . "favorites f "
                         . "INNER JOIN " . $vars->tablepre . "members m USING (username) "
                         . "WHERE f.type = 'subscription' AND f.tid = {$post['tid']} AND m.username != '$member' AND m.lastvisit >= $date");
    while ($subs = $db->fetch_array($subquery)) {
        if ($viewperm < $vars->status_enum[$subs['status']]) {
            continue;
        }

        if ((int) $subs['ppp'] < 1) {
            $subs['ppp'] = $posts;
        }

        $translate = $lang2[$subs['langfile']];
        $topicpages = quickpage($posts, $subs['ppp']);
        $topicpages = ($topicpages == 1) ? '' : '&page='.$topicpages;
        $threadurl = $vars->full_url . 'viewthread.php?tid='.$post['tid'].$topicpages.'#pid'.$newpid;
        $rawsubject = htmlspecialchars_decode($threadname, ENT_QUOTES);
        $rawusername = htmlspecialchars_decode($member, ENT_QUOTES);
        $rawemail = htmlspecialchars_decode($subs['email'], ENT_QUOTES);
        $title = "$rawsubject ({$translate['textsubsubject']})";
        $body = "$rawusername {$translate['textsubbody']} \n$threadurl";
        xmb_mail($rawemail, $title, $body, $translate['charset']);
    }
    $db->free_result($subquery);

    $core->moderate_cleanup($post['author']);
    echo $lang['moderation_approved'];
} elseif ($action == 'deletethread') {
    $core->request_secure('Quarantine Panel/Anonymous Queue', 'Approve or Delete');
    $oldtid = getInt('tid');
    $result = $db->query("SELECT * FROM " . $vars->tablepre . "hold_threads WHERE tid = $oldtid");
    if ($db->num_rows($result) == 0) {
        $core->error($lang['textnoforum'], showheader: false, append: '</td></tr></table></td></tr></table>');
    }
    $thread = $db->fetch_array($result);
    $db->free_result($result);

    $member = $db->escape($thread['author']);

    $oldpid = (int) $db->result($db->query("SELECT pid FROM " . $vars->tablepre . "hold_posts WHERE newtid = {$thread['tid']}"));
    $db->query("DELETE FROM " . $vars->tablepre . "hold_attachments WHERE pid = $oldpid");
    if (intval($thread['pollopts']) != 0) {
        $oldpoll = $sql->getPollId($thread['tid'], true);
        if ($oldpoll !== 0) {
            $sql->deleteVotesByTID([$oldpoll], quarantine: true);
        }
    }
    $db->query("DELETE FROM " . $vars->tablepre . "hold_favorites WHERE tid = {$thread['tid']}");
    $db->query("DELETE FROM " . $vars->tablepre . "hold_posts WHERE pid = $oldpid");
    $db->query("DELETE FROM " . $vars->tablepre . "hold_threads WHERE tid = {$thread['tid']}");

    $core->moderate_cleanup($thread['author']);
    echo $lang['moderation_deleted'];
} elseif ($action == 'deletereply') {
    $core->request_secure('Quarantine Panel/Anonymous Queue', 'Approve or Delete');
    $oldpid = getInt('pid');
    $result = $db->query("SELECT * FROM " . $vars->tablepre . "hold_posts WHERE pid = $oldpid");
    if ($db->num_rows($result) == 0) {
        $core->error($lang['textnoforum'], showheader: false, append: '</td></tr></table></td></tr></table>');
    }
    $post = $db->fetch_array($result);
    $db->free_result($result);

    $member = $db->escape($post['author']);

    $db->query("DELETE FROM " . $vars->tablepre . "hold_attachments WHERE pid = {$post['pid']}");
    $db->query("DELETE FROM " . $vars->tablepre . "hold_posts WHERE pid = {$post['pid']}");

    $core->moderate_cleanup($post['author']);
    echo $lang['moderation_deleted'];
} else {
    echo "<h2>{$lang['moderation_new_memq']}</h2>\n";
    $result = $db->query(
        "SELECT m.username, COUNT(*) AS postnum " .
        "FROM " . $vars->tablepre . "members AS m " .
        "INNER JOIN " . $vars->tablepre . "hold_posts AS p ON m.username = p.author " .
        "GROUP BY m.username " .
        "ORDER BY m.regdate ASC " .
        "LIMIT 10"
    );
    if ($db->num_rows($result) == 0) {
        // Double check to make sure there aren't any desync user records.
        $result2 = $db->query("SELECT username FROM " . $vars->tablepre . "members WHERE waiting_for_mod = 'yes' AND postnum > 0");
        if ($db->num_rows($result2) == 0) {
            echo "<p>{$lang['moderation_empty']}</p>\n";
        } else {
            echo "<table>\n<tr><th>{$lang['textusername']}</th><th>{$lang['memposts']}</th></tr>\n";
            while ($row = $db->fetch_array($result2)) {
                $user = $row['username'];
                $userurl = recodeOut($user);
                echo "<tr><td><a href='" . $vars->full_url . "quarantine.php?action=viewuser&amp;u=$userurl'>$user</a></td><td>0</td></tr>\n";
            }
            echo "</table>\n";
        }
    } else {
        echo "<table>\n<tr><th>{$lang['textusername']}</th><th>{$lang['memposts']}</th></tr>\n";
        while ($row = $db->fetch_array($result)) {
            $user = $row['username'];
            $userurl = recodeOut($user);
            $count = $row['postnum'];
            echo "<tr><td><a href='" . $vars->full_url . "quarantine.php?action=viewuser&amp;u=$userurl'>$user</a></td><td>$count</td></tr>\n";
        }
        echo "</table>\n";
    }
    $db->free_result($result);


    echo "<h2>{$lang['moderation_anonq']}</h2>\n";
    $result = $db->query(
        "SELECT fid, COUNT(*) AS postnum " .
        "FROM " . $vars->tablepre . "hold_posts WHERE author='Anonymous' " .
        "GROUP BY fid "
    );
    if ($db->num_rows($result) == 0) {
        echo "<p>{$lang['moderation_empty']}</p>\n";
    } else {
        echo "<table>\n<tr><th>{$lang['textforum']}</th><th>{$lang['memposts']}</th></tr>\n";
        while ($row = $db->fetch_array($result)) {
            $fid = (int) $row['fid'];
            $forum = $forums->getForum($fid);
            $fname = fnameOut($forum['name']);
            $count = $row['postnum'];
            echo "<tr><td><a href='" . $vars->full_url . "quarantine.php?action=viewforum&amp;fid=$fid'>$fname</a></td><td>$count</td></tr>\n";
        }
        echo "</table>\n";
    }
    $db->free_result($result);
}

echo "</td></tr></table></td></tr></table>\n";

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $footer;
