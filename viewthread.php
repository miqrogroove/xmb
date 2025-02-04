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
$sqlSvc = \XMB\Services\sql();
$smile = \XMB\Services\smile();
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$vars = \XMB\Services\vars();
$full_url = $vars->full_url;
$lang = &$vars->lang;
$SETTINGS = &$vars->settings;

$printable_ppp = 100;

$pid = getInt('pid');
$tid = getInt('tid');
$fid = getInt('fid');
$goto = getPhpInput('goto', 'g');
$action = getPhpInput('action', 'g');
$quarantine = false;

if ($goto == 'lastpost') {
    if ($pid > 0) {
        $query = $db->query("SELECT tid, dateline FROM " . $vars->tablepre . "posts WHERE pid=$pid");
        if ($db->num_rows($query) == 1) {
            $post = $db->fetch_array($query);
            $tid = (int) $post['tid'];

            $posts = $sqlSvc->countPosts($quarantine, $tid, before: (int) $post['dateline']);
        } else {
            header('HTTP/1.0 404 Not Found');
            $core->error($lang['textnothread']);
        }
    } elseif ($tid > 0) {
        $posts = $sqlSvc->countPosts($quarantine, $tid);

        if ($posts == 0) {
            header('HTTP/1.0 404 Not Found');
            $core->error($lang['textnothread']);
        }

        $query = $db->query("SELECT pid FROM " . $vars->tablepre . "posts WHERE tid = $tid ORDER BY dateline DESC, pid DESC LIMIT 0, 1");
        $pid = (int) $db->result($query);
        $db->free_result($query);
    } elseif ($fid > 0) {
        $pid = 0;
        $tid = 0;
        $query = $db->query("SELECT pid, tid, dateline FROM " . $vars->tablepre . "posts WHERE fid = $fid ORDER BY dateline DESC, pid DESC LIMIT 0, 1");
        if ($db->num_rows($query) == 1) {
            $posts = $db->fetch_array($query);
            $db->free_result($query);

            $pid = (int) $posts['pid'];
            $tid = (int) $posts['tid'];
        }

        $query = $db->query("SELECT p.pid, p.tid, p.dateline FROM " . $vars->tablepre . "posts p LEFT JOIN " . $vars->tablepre . "forums f USING (fid) WHERE f.fup = $fid ORDER BY p.dateline DESC, p.pid DESC LIMIT 0, 1");
        if ($db->num_rows($query) == 1) {
            $fupPosts = $db->fetch_array($query);
            $db->free_result($query);

            if ($pid == 0) {
                $pid = (int) $fupPosts['pid'];
                $tid = (int) $fupPosts['tid'];
            } elseif ((int) $fupPosts['dateline'] > (int) $posts['dateline']) {
                $pid = (int) $fupPosts['pid'];
                $tid = (int) $fupPosts['tid'];
            }
        }

        if ($pid == 0) {
            header('HTTP/1.0 404 Not Found');
            $core->error($lang['textnothread']);
        }

        $posts = $sqlSvc->countPosts($quarantine, $tid);
    } else {
        header('HTTP/1.0 404 Not Found');
        $core->error($lang['textnothread']);
    }
    $page = $core->quickpage($posts, $vars->ppp);
    if ($page == 1) {
        $page = '';
    } else {
        $page = "&page=$page";
    }
    redirect("{$full_url}viewthread.php?tid=$tid$page#pid$pid", timeout: 0);

} elseif ($goto == 'search') {
    $tidtest = $db->query("SELECT dateline FROM " . $vars->tablepre . "posts WHERE tid = $tid AND pid = $pid");
    if ($db->num_rows($tidtest) == 1) {
        $post = $db->fetch_array($tidtest);
        $posts = $sqlSvc->countPosts($quarantine, $tid, '', (int) $post['dateline']);
        $page = $core->quickpage($posts, $vars->ppp);
        if ($page == 1) {
            $page = '';
        } else {
            $page = "&page=$page";
        }
        $core->redirect("{$full_url}viewthread.php?tid=$tid$page#pid$pid", timeout: 0);
    } else {
        header('HTTP/1.0 404 Not Found');
        $core->error($lang['textnothread']);
    }
}

$query = $db->query("SELECT t.*, COUNT(*) AS postcount FROM " . $vars->tablepre . "threads AS t LEFT JOIN " . $vars->tablepre . "posts USING (tid) WHERE t.tid = $tid GROUP BY t.tid");
if ($db->num_rows($query) != 1) {
    $db->free_result($query);
    header('HTTP/1.0 404 Not Found');
    $core->error($lang['textnothread']);
}

$thread = $db->fetch_array($query);
$db->free_result($query);

$thislast = explode('|', $thread['lastpost']);

// Perform automatic maintenance
$replycount = (int) $thread['postcount'] - 1;
if ((int) $thread['replies'] != $replycount) {
    // Also verify the value that we expect to overwrite.
    $core->updatethreadcount($tid, $replycount);
}

if (strpos($thread['closed'], '|') !== false) {
    $moved = explode('|', $thread['closed']);
    if ($moved[0] == 'moved') {
        header('HTTP/1.0 301 Moved Permanently');
        header("Location: {$full_url}viewthread.php?tid={$moved[1]}");
        exit();
    }
}

$thread['subject'] = shortenString($core->rawHTMLsubject(stripslashes($thread['subject'])));

$lastPid = isset($thislast[2]) ? $thislast[2] : 0;
$expire = $vars->onlinetime + $vars::ONLINE_TIMER;
if (false === strpos($vars->oldtopics, "|$lastPid|")) {
    if (empty($vars->oldtopics)) {
        $vars->oldtopics = "|$lastPid|";
    } else {
        $vars->oldtopics .= "$lastPid|";
    }
    $core->put_cookie('oldtopics', $vars->oldtopics, $expire);
}

$fid = (int) $thread['fid'];
$forum = $forums->getForum($fid);

if (null === $forum || ($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on') {
    header('HTTP/1.0 404 Not Found');
    $core->error($lang['textnoforum']);
}

$perms = $core->checkForumPermissions($forum);
if (! $perms[$vars::PERMS_VIEW]) {
    if (X_GUEST) {
        $core->redirect("{$full_url}misc.php?action=login", timeout: 0);
        exit;
    } else {
        $core->error($lang['privforummsg']);
    }
} elseif (! $perms[$vars::PERMS_PASSWORD]) {
    $core->handlePasswordDialog($fid);
}

$fup = [];
if ($forum['type'] == 'sub') {
    $fup = $forums->getForum((int) $forum['fup']);
    // prevent access to subforum when upper forum can't be viewed.
    $fupPerms = $core->checkForumPermissions($fup);
    if (! $fupPerms[$vars::PERMS_VIEW]) {
        if (X_GUEST) {
            redirect("{$full_url}misc.php?action=login", timeout: 0);
            exit;
        } else {
            $core->error($lang['privforummsg']);
        }
    } elseif (! $fupPerms[$vars::PERMS_PASSWORD]) {
        $core->handlePasswordDialog((int) $fup['fid']);
    } elseif ((int) $fup['fup'] > 0) {
        $fupup = $forums->getForum((int) $fup['fup']);
        $core->nav("<a href='{$full_url}index.php?gid={$fup['fup']}'>" . fnameOut($fupup['name']) . '</a>');
        unset($fupup);
    }
    $core->nav("<a href='{$full_url}forumdisplay.php?fid={$fup['fid']}'>" . fnameOut($fup['name']) . '</a>');
    unset($fup);
} elseif ((int) $forum['fup'] > 0) { // 'forum' in a 'group'
    $fup = $forums->getForum((int) $forum['fup']);
    $core->nav("<a href='{$full_url}index.php?gid={$fup['fid']}'>" . fnameOut($fup['name']) . '</a>');
    unset($fup);
}
$core->nav("<a href='{$full_url}forumdisplay.php?fid=$fid'>" . fnameOut($forum['name']) . '</a>');
$core->nav($thread['subject']);

if ($SETTINGS['subject_in_title'] == 'on') {
    $template->threadSubject = $thread['subject'] . ' - ';
}

// Search-link
$template->searchlink = $core->makeSearchLink((int) $forum['fid']);

$template->replylink = '';
$template->quickreply = '';

$subTemplate = new \XMB\Template($vars);
$subTemplate->addRefs();
$subTemplate->fid = $fid;
$subTemplate->tid = $tid;

$status1 = $core->modcheck($vars->self['username'], $forum['moderator']);

if ($action == '') {
    $mpage = $core->multipage((int) $thread['postcount'], $vars->ppp, "{$full_url}viewthread.php?tid=$tid");
    $template->multipage = '';
    if (strlen($mpage['html']) != 0) {
        $subTemplate->multipage = $mpage['html'];
        $template->multipage = $subTemplate->process('viewthread_multipage.php');
    }
    $printable_page = intval(floor($mpage['start'] / $printable_ppp)) + 1;
    $printable_page = $printable_page == 1 ? '' : "&amp;page=$printable_page";
    $template->printable_link = "{$full_url}viewthread.php?action=printable&amp;tid={$tid}{$printable_page}";

    $header = $template->process('header.php');

    if ($perms[$vars::PERMS_REPLY] && ($thread['closed'] == '' || X_SADMIN)) {
        $template->replylink = $subTemplate->process('viewthread_reply.php');
        if ($SETTINGS['quickreply_status'] == 'on') {
            $quickTemplate = new \XMB\Template($vars);
            $quickTemplate->addRefs();
            $quickTemplate->tid = $tid;
            $quickTemplate->allowimgcode = ($forum['allowimgcode'] == 'yes') ? $lang['texton']:$lang['textoff'];
            $quickTemplate->allowhtml = $lang['textoff'];
            $quickTemplate->allowsmilies = ($forum['allowsmilies'] == 'yes') ? $lang['texton']:$lang['textoff'];
            $quickTemplate->allowbbcode = ($forum['allowbbcode'] == 'yes') ? $lang['texton']:$lang['textoff'];

            if (X_MEMBER) {
                $quickTemplate->usesigcheck = ($vars->self['sig'] != '') ? $cheHTML : '';
                $quickTemplate->subcheck = ('yes' == $vars->self['sub_each_post']) ? $cheHTML : '';
            } else {
                $quickTemplate->usesigcheck = '';
                $quickTemplate->subcheck = '';
            }
            $quickTemplate->captchapostcheck = '';
            if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on') {
                $Captcha = new Captcha();
                if ($Captcha->bCompatible !== false) {
                    $quickTemplate->imghash = $Captcha->GenerateCode();
                    if ($SETTINGS['captcha_code_casesensitive'] == 'off') {
                        $lang['captchacaseon'] = '';
                    }
                    $quickTemplate->captchapostcheck = $quickTemplate->process('viewthread_quickreply_captcha.php');
                }
            }

            if ($SETTINGS['smileyinsert'] == 'on' && $forum['allowsmilies'] == 'yes' && $smile->isAnySmilieInstalled()) {
                $quickTemplate->quickbbcode = $template->process('functions_bbcode_quickreply.php'); // Uses shared $template->browser.

                $quickTemplate->smilieinsert = $core->smilieinsert('quick');
                $quickTemplate->smilies = $quickTemplate->process('viewthread_quickreply_smilies.php');
            } else {
                $quickTemplate->quickbbcode = '';
                $quickTemplate->smilies = '';
            }

            $quickTemplate->disableguest = X_GUEST ? 'style="display:none;"' : '';
            if (X_MEMBER) {
                $quickTemplate->quick_name_display = "&nbsp;- [{$lang['loggedin']} <strong>" . $vars->self['username'] . "</strong>]";
            } else {
                $quickTemplate->quick_name_display = "&nbsp;- [<strong>{$lang['textanonymous']}</strong>]";
            }

            $template->quickreply = $quickTemplate->process('viewthread_quickreply.php');
            unset($quickTemplate);
        }
    }

    if ($thread['closed'] == '') {
        $subTemplate->closeopen = $lang['textclosethread'];
    } else {
        $subTemplate->closeopen = $lang['textopenthread'];
    }

    if (X_GUEST) {
        $template->memcplink = '';
    } else {
        $template->memcplink = " | <a href='{$full_url}memcp.php?action=subscriptions&amp;subadd=$tid'>{$lang['textsubscribe']}</a> | <a href='{$full_url}memcp.php?action=favorites&amp;favadd=$tid'>{$lang['textaddfav']}</a>";
    }

    if ($perms[$vars::PERMS_THREAD]) {
        $template->newtopiclink = $subTemplate->process('viewthread_newtopic.php');
    } else {
        $template->newtopiclink = '';
    }

    if ($perms[$vars::PERMS_POLL]) {
        $template->newpolllink = $subTemplate->process('viewthread_newpoll.php');
    } else {
        $template->newpolllink = '';
    }

    $subTemplate->topuntop = ($thread['topped'] == 1) ? $lang['textuntopthread'] : $lang['texttopthread'];

    $specialrank = [];
    $rankposts = [];
    $queryranks = $sqlSvc->getRanks();
    foreach($queryranks as $query) {
        $query['posts'] = (int) $query['posts'];
        if ($query['title'] === 'Super Administrator' || $query['title'] === 'Administrator' || $query['title'] === 'Super Moderator' || $query['title'] === 'Moderator') {
            $specialrank[$query['title']] = &$query;
        } else {
            $rankposts[$query['posts']] = &$query;
        }
        unset($query);
    }
    unset($queryranks);

    $db->query("UPDATE " . $vars->tablepre . "threads SET views = views + 1 WHERE tid = $tid");

    $subTemplate->pollhtml = '';
    $template->poll = '';
    $vote_id = $voted = 0;

    if ('1' === $thread['pollopts']) {
        $vote_id = $sqlSvc->getPollId($tid);
    }

    if ($vote_id > 0) {
        $subTemplate->token = '';

        if (X_MEMBER) {
            $query = $db->query("SELECT COUNT(vote_id) AS cVotes FROM " . $vars->tablepre . "vote_voters WHERE vote_id = $vote_id AND vote_user_id=" . intval($vars->self['uid']));
            if ($query) {
                $voted = $db->fetch_array($query);
                $voted = (int) $voted['cVotes'];
            }
            $db->free_result($query);
        }

        $viewresults = (isset($viewresults) && $viewresults == 'yes') ? 'yes' : '';
        if ($voted >= 1 || $thread['closed'] == 'yes' || X_GUEST || $viewresults) {
            if ($viewresults) {
                $subTemplate->results = "- [<a href='{$full_url}viewthread.php?tid=$tid'><font color='" . $vars->theme['cattext'] . "'>{$lang['backtovote']}</font></a>]";
            } else {
                $subTemplate->results = '';
            }

            $poll = [];
            $num_votes = 0;
            $query = $db->query("SELECT vote_result, vote_option_text FROM " . $vars->tablepre . "vote_results WHERE vote_id = $vote_id");
            while($result = $db->fetch_array($query)) {
                $num_votes += (int) $result['vote_result'];
                $pollentry = [];
                $pollentry['name'] = $core->postify(
                    $result['vote_option_text'],
                    allowsmilies: $forum['allowsmilies'],
                    allowbbcode: $forum['allowbbcode'],
                    allowimgcode: 'no',
                    ismood: 'yes',
                );
                $pollentry['votes'] = $result['vote_result'];
                $poll[] = $pollentry;
            }
            $db->free_result($query);

            foreach($poll as $array) {
                $subTemplate->pollbar = '';
                if ((int) $array['votes'] > 0) {
                    $orig = round($array['votes']/$num_votes*100, 2);
                    $subTemplate->percentage = round($orig, 2) . '%';
                    $poll_length = (int) $orig;
                    if ($poll_length > 97) {
                        $poll_length = 97;
                    }
                    $subTemplate->pollbar = "<img src='{$full_url}" . $vars->theme['imgdir'] . "/pollbar.gif' height=10 width='{$poll_length}%' alt='{$lang['altpollpercentage']}' title='{$lang['altpollpercentage']}' border=0 />";
                } else {
                    $subTemplate->percentage = '0%';
                }
                $subTemplate->array = $array;
                $subTemplate->pollhtml .= $subTemplate->process('viewthread_poll_options_view.php');
            }
            $subTemplate->buttoncode = '';
        } else {
            $subTemplate->token = $token->create('View Thread/Poll Vote', (string) $vote_id, $vars::NONCE_FORM_EXP);

            $subTemplate->results = "- [<a href='{$full_url}viewthread.php?tid=$tid&amp;viewresults=yes'><font color='" . $vars->theme['cattext'] . "'>{$lang['viewresults']}</font></a>]";
            $query = $db->query("SELECT vote_option_id, vote_option_text FROM " . $vars->tablepre . "vote_results WHERE vote_id = $vote_id");
            while($result = $db->fetch_array($query)) {
                $poll = [];
                $poll['id'] = (int) $result['vote_option_id'];
                $poll['name'] = $core->postify(
                    $result['vote_option_text'],
                    allowsmilies: $forum['allowsmilies'],
                    allowbbcode: $forum['allowbbcode'],
                    allowimgcode: 'no',
                    ismood: 'yes',
                );
                $subTemplate->poll = $poll;
                $subTemplate->pollhtml .= $subTemplate->process('viewthread_poll_options.php');
            }
            $db->free_result($query);
            $subTemplate->buttoncode = $subTemplate->process('viewthread_poll_submitbutton.php');
        }
        $subTemplate->thread = $thread;
        $template->poll = $subTemplate->process('viewthread_poll.php');
    }

    if (X_MEMBER && 'yes' == $vars->self['waiting_for_mod']) {
        $quarantine = true;
        $result = $sqlSvc->countPosts($quarantine, $tid, $vars->self['username']);
        if ($result > 0) {
            if (1 == $result) {
                $msg = $lang['moderation_replies_single'];
            } else {
                $msg = str_replace('$result', $result, $lang['moderation_replies_eval']);
            }
            $template->poll .= message(
                $msg,
                showheader: false,
                die: false,
                return_as_string: true,
                showfooter: false,
            ) . "<br />\n";
        }
        $quarantine = false;
    }

    $startdate = '0';
    $startpid = '0';
    $enddate = '0';
    $sql = "SELECT dateline, pid "
         . "FROM " . $vars->tablepre . "posts "
         . "WHERE tid=$tid "
         . "ORDER BY dateline ASC, pid ASC "
         . "LIMIT {$mpage['start']}, ".($vars->ppp + 1);
    $query1 = $db->query($sql);
    $rowcount = $db->num_rows($query1);
    if ($rowcount > 0) {
        $row = $db->fetch_array($query1);
        $startdate = $row['dateline'];
        $startpid = $row['pid'];
        if ($rowcount <= $vars->ppp) {
            $enddate = $vars->onlinetime;
        } else {
            $db->data_seek($query1, $rowcount - 1);
            $row = $db->fetch_array($query1);
            $enddate = $row['dateline'];
        }
    }
    $db->free_result($query1);

    $subTemplate->thisbg = $vars->theme['altbg2'];
    
    if ($SETTINGS['show_logs_in_threads'] == 'on') {
        $sql = "SELECT p.*, m.* "
             . "FROM "
             . "( "
             . "  ( "
             . "    SELECT 'post' AS type, fid, tid, author, subject, dateline, pid, message, icon, usesig, useip, bbcodeoff, smileyoff "
             . "    FROM " . $vars->tablepre . "posts "
             . "    WHERE tid=$tid AND (dateline > $startdate OR dateline = $startdate AND pid >= $startpid)"
             . "    ORDER BY dateline ASC, pid ASC "
             . "    LIMIT " . $vars->ppp
             . "  ) "
             . "  UNION ALL "
             . "  ( "
             . "    SELECT 'modlog' AS type, fid, tid, username AS author, action AS subject, date AS dateline, '', '', '', '', '', '', '' "
             . "    FROM " . $vars->tablepre . "logs "
             . "    WHERE tid=$tid AND date >= $startdate AND date < $enddate "
             . "  ) "
             . ") AS p "
             . "LEFT JOIN " . $vars->tablepre . "members m ON m.username=p.author "
             . "ORDER BY p.dateline ASC, p.type DESC, p.pid ASC ";
    } else {
        $sql = "SELECT 'post' AS type, p.fid, p.tid, p.author, p.subject, p.dateline, p.pid, p.message, p.icon, p.usesig, p.useip, p.bbcodeoff, p.smileyoff, m.* "
             . "FROM " . $vars->tablepre . "posts AS p "
             . "LEFT JOIN " . $vars->tablepre . "members AS m ON m.username=p.author "
             . "WHERE tid=$tid AND (dateline > $startdate OR dateline = $startdate AND pid >= $startpid)"
             . "ORDER BY dateline ASC, pid ASC "
             . "LIMIT " . $vars->ppp;
    }
    $querypost = $db->query($sql);

    if ($forum['attachstatus'] == 'on') {
        $queryattach = $db->query("SELECT a.aid, a.pid, a.filename, a.filetype, a.filesize, a.downloads, a.img_size, thumbs.aid AS thumbid, thumbs.filename AS thumbname, thumbs.img_size AS thumbsize FROM " . $vars->tablepre . "attachments AS a LEFT JOIN " . $vars->tablepre . "attachments AS thumbs ON a.aid=thumbs.parentid INNER JOIN " . $vars->tablepre . "posts AS p ON a.pid=p.pid WHERE p.tid=$tid AND a.parentid=0");
    }

    $template->posts = '';

    while ($post = $db->fetch_array($querypost)) {
        // Perform automatic maintenance
        if ($post['type'] == 'post' && $post['fid'] !== $thread['fid']) {
            // Also verify the value that we expect to overwrite.
            $db->query('UPDATE ' . $vars->tablepre . "posts SET fid = {$thread['fid']} WHERE pid = {$post['pid']} AND fid = {$post['fid']}");
        }

        null_string($post['avatar']);
        $post['avatar'] = str_replace("script:", "sc ript:", $post['avatar']);

        if ($vars->onlinetime - (int) $post['lastvisit'] <= $vars::ONLINE_TIMER) {
            if ('1' === $post['invisible']) {
                if (! X_ADMIN) {
                    $subTemplate->onlinenow = $lang['memberisoff'];
                } else {
                    $subTemplate->onlinenow = $lang['memberison'].' ('.$lang['hidden'].')';
                }
            } else {
                $subTemplate->onlinenow = $lang['memberison'];
            }
        } else {
            $subTemplate->onlinenow = $lang['memberisoff'];
        }

        $date = gmdate($vars->dateformat, $core->timeKludge((int) $post['dateline']));
        $time = gmdate($vars->timecode, $core->timeKludge((int) $post['dateline']));

        $subTemplate->poston = "{$lang['textposton']} $date {$lang['textat']} $time";

        if ($post['icon'] != '' && file_exists(XMB_ROOT . $vars->theme['smdir'] . '/' . $post['icon'])) {
            $post['icon'] = "<img src='{$full_url}" . $vars->theme['smdir'] . "/{$post['icon']}' alt='{$post['icon']}' border=0 />";
        } else {
            $post['icon'] = "<img src='{$full_url}" . $vars->theme['imgdir'] . "/default_icon.gif' alt='[*]' border=0 />";
        }

        if ($post['author'] != 'Anonymous' && $post['username'] && ('off' == $SETTINGS['hide_banned'] || $post['status'] != 'Banned')) {
            if (X_MEMBER && $post['showemail'] == 'yes') {
                $subTemplate->post = $post;
                $subTemplate->email = $subTemplate->process('viewthread_post_email.php');
            } else {
                $subTemplate->email = '';
            }

            $post['site'] = format_member_site($post['site']);
            if ($post['site'] == '') {
                $subTemplate->site = '';
            } else {
                $subTemplate->post = $post;
                $subTemplate->site = $subTemplate->process('viewthread_post_site.php');
            }

            $encodename = recodeOut($post['author']);
            $subTemplate->profileURL = "{$full_url}member.php?action=viewpro&amp;member=$encodename";
            $subTemplate->profilelink = "<a href='" . $subTemplate->profileURL . "'>{$post['author']}</a>";

            if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_search_status'] == 'on') {
                $subTemplate->search = '';
            } else {
                $subTemplate->encodename = $encodename;
                $subTemplate->search = $subTemplate->process('viewthread_post_search.php');
            }

            $subTemplate->profile = $subTemplate->process('viewthread_post_profile.php');
            if (X_GUEST) {
                $subTemplate->u2u = '';
            } else {
                $subTemplate->u2u = $subTemplate->process('viewthread_post_u2u.php');
            }

            $subTemplate->showtitle = $post['status'];

            if ($post['status'] == 'Administrator' || $post['status'] == 'Super Administrator' || $post['status'] == 'Super Moderator' || $post['status'] == 'Moderator') {
                // Specify the staff rank.
                $sr = $post['status'];
                $rank = [
                    'allowavatars' => $specialrank[$sr]['allowavatars'],
                    'title' => $lang[$vars->status_translate[$vars->status_enum[$sr]]],
                    'stars' => $specialrank[$sr]['stars'],
                    'avatarrank' => $specialrank[$sr]['avatarrank'],
                ];
            } elseif ($post['status'] == 'Banned') {
                // Specify no rank.
                $rank = [
                    'allowavatars' => 'no',
                    'title' => $lang['textbanned'],
                    'stars' => 0,
                    'avatarrank' => '',
                ];
            } elseif (count($rankposts) === 0) {
                // Specify no rank.
                $rank = [
                    'allowavatars' => 'no',
                    'title' => '',
                    'stars' => 0,
                    'avatarrank' => '',
                ];
            } else {
                // Find the appropriate member rank.
                $max = -1;
                $keys = array_keys($rankposts);
                foreach($keys as $key) {
                    if ((int) $post['postnum'] >= (int) $key && (int) $key > (int) $max) {
                        $max = $key;
                    }
                }
                $rank = &$rankposts[$max];
            }

            $subTemplate->stars = str_repeat("<img src='{$full_url}" . $vars->theme['imgdir'] . "/star.gif' alt='*' border=0 />", (int) $rank['stars']) . '<br />';
            $subTemplate->showtitle = ($post['customstatus'] != '') ? $post['customstatus'] . '<br />' : $rank['title'] . '<br />';

            // $rankAvatar is the avatar configured in rank settings.  $avatar is the user's avatar, pulled from the posts-join-members query.
            if ($rank['avatarrank'] != '') {
                $subTemplate->rankAvatar = "<img src='{$rank['avatarrank']}' alt='{$lang['altavatar']}' border=0 /><br />";
            } else {
                $subTemplate->rankAvatar = '';
            }

            if ($rank['allowavatars'] == 'no') {
                $post['avatar'] = '';
            }

            if ('on' == $SETTINGS['images_https_only'] && strpos($post['avatar'], ':') !== false && substr($post['avatar'], 0, 6) !== 'https:') {
                $post['avatar'] = '';
            }

            $subTemplate->avatar = '';
            if ($SETTINGS['avastatus'] == 'on' || $SETTINGS['avastatus'] == 'list') {
                if ($post['avatar'] !== '' && $rank['allowavatars'] != "no") {
                    $subTemplate->avatar = "<img src='{$post['avatar']}' alt='{$lang['altavatar']}' border=0 />";
                }
            }

            $subTemplate->tharegdate = gmdate($vars->dateformat, $core->timeKludge((int) $post['regdate']));

            if ($post['mood'] != '') {
                $subTemplate->mood = '<strong>' . $lang['mood'] . '</strong> ' . $core->postify($post['mood'], allowimgcode: 'no', ismood: 'yes');
            } else {
                $subTemplate->mood = '';
            }

            if ($post['location'] != '') {
                $post['location'] = $core->rawHTMLsubject($post['location']);
                $subTemplate->location = "<br />{$lang['textlocation']} {$post['location']}";
            } else {
                $subTemplate->location = '';
            }
        } else {
            $post['author'] = ($post['author'] == 'Anonymous') ? $lang['textanonymous'] : $post['author'];
            $post['postnum'] = $lang['not_applicable_abbr'];
            $post['usesig'] = 'no';
            $subTemplate->showtitle = $lang['textunregistered'] . '<br />';
            $subTemplate->stars = '';
            $subTemplate->avatar = '';
            $subTemplate->rankAvatar = '';
            $subTemplate->tharegdate = $lang['not_applicable_abbr'];
            $subTemplate->email = '';
            $subTemplate->site = '';
            $subTemplate->profile = '';
            $subTemplate->search = '';
            $subTemplate->u2u = '';
            $subTemplate->location = '';
            $subTemplate->mood = '';
            $subTemplate->profilelink = $post['author'];
        }
        $subTemplate->post = $post;

        $subTemplate->ip = '';
        if (X_ADMIN) {
            $subTemplate->ip = $subTemplate->process('viewthread_post_ip.php');
        }

        $subTemplate->repquote = '';
        if ($perms[$vars::PERMS_REPLY] && ($thread['closed'] != 'yes' || X_SADMIN)) {
            $subTemplate->repquote = $subTemplate->process('viewthread_post_repquote.php');
        }

        $subTemplate->reportlink = '';
        if (X_MEMBER && $post['author'] != $vars->xmbuser && $SETTINGS['reportpost'] == 'on') {
            // Post reporting is enabled, but is this user legit?
            if ('on' == $SETTINGS['quarantine_new_users'] && (0 == (int) $vars->self['postnum'] || 'yes' == $vars->self['waiting_for_mod']) && ! X_STAFF) {
                // Nope
            } else {
                $subTemplate->reportlink = $subTemplate->process('viewthread_post_report.php');
            }
        }

        $subTemplate->edit = '';
        if ($core->modcheckPost($vars->self['username'], $forum['moderator'], $post['status']) == 'Moderator' || ($thread['closed'] != 'yes' && $post['author'] == $vars->xmbuser)) {
            $subTemplate->edit = $subTemplate->process('viewthread_post_edit.php');
        }

        if ($forum['attachstatus'] == 'on' && $db->num_rows($queryattach) > 0) {
            $files = [];
            $db->data_seek($queryattach, 0);
            while ($attach = $db->fetch_array($queryattach)) {
                if ($attach['pid'] === $post['pid']) {
                    $files[] = $attach;
                }
            }
            if (count($files) > 0) {
                $core->bbcodeFileTags($post['message'], $files, (int) $post['pid'], ($forum['allowbbcode'] == 'yes' && $post['bbcodeoff'] == 'no'));
            }
        }

        $post['message'] = $core->postify(
            stripslashes($post['message']),
            $post['smileyoff'],
            $post['bbcodeoff'],
            $forum['allowsmilies'],
            allowbbcode: $forum['allowbbcode'],
            allowimgcode: $forum['allowimgcode'],
        );

        if ($post['usesig'] == 'yes') {
            $post['sig'] = $core->postify(
                $post['sig'],
                allowsmilies: $forum['allowsmilies'],
                allowbbcode: $SETTINGS['sigbbcode'],
                allowimgcode: $forum['allowimgcode'],
            );
            $subTemplate->post = $post;
            $post['message'] .= $subTemplate->process('viewthread_post_sig.php');
        }

        if ($post['type'] == 'post') {

            if ($post['subject'] != '') {
                $subTemplate->linktitle = $core->rawHTMLsubject(stripslashes($post['subject']));
                $post['subject'] = wordwrap($subTemplate->linktitle, 150, '<br />', true) . '<br />';
            } else {
                $subTemplate->linktitle = $thread['subject'];
            }
            $subTemplate->post = $post;

            $template->posts .= $subTemplate->process('viewthread_post.php');

        } else {

            $post['message'] = $lang["modlog_{$post['subject']}"] . "<br />$date {$lang['textat']} $time";
            $subTemplate->post = $post;

            $template->posts .= $subTemplate->process('viewthread_modlog.php');

        }

        if ($subTemplate->thisbg == $vars->theme['altbg2']) {
            $subTemplate->thisbg = $vars->theme['altbg1'];
        } else {
            $subTemplate->thisbg = $vars->theme['altbg2'];
        }
        
        // Remove array reference(s)
        unset($rank);
    } // post loop
    $db->free_result($querypost);

    $template->modoptions = '';
    if ('Moderator' == $status1) {
        $template->modoptions = $subTemplate->process('viewthread_modoptions.php');
    }
    $template->thread = $thread;
    $template->ppp = $vars->ppp;
    $viewthread = $template->process('viewthread.php');

    $template->footerstuff = $core->end_time();
    $footer = $template->process('footer.php');
    echo $header, $viewthread, $footer;
} elseif ($action == 'printable') {
    $mpage = $core->multipage((int) $thread['postcount'], $printable_ppp, $vars->full_url . 'viewthread.php?action=printable&amp;tid=' . $tid);
    $template->multipage = '';
    if (strlen($mpage['html']) != 0) {
        $subTemplate->multipage = $mpage['html'];
        $template->multipage = $subTemplate->process('viewthread_multipage.php');
    }

    $normal_page = intval(floor($mpage['start'] / $vars->ppp)) + 1;

    $threadlink = $vars->full_url . "viewthread.php?tid=$tid";
    if ($normal_page != 1) $threadlink .= "&amp;page=$normal_page";

    $core->setCanonicalLink($threadlink);
    $template->threadlink = $threadlink;

    // This first query does not access any table data if the new thread_optimize index is available.  :)
    $criteria = '';
    $offset = '';
    if ($mpage['start'] <= 300) {
        // However, we need to be beyond page 1 to get any boost.
        $offset = "{$mpage['start']},";
    } else {
        $sql = "SELECT dateline, pid "
             . "FROM " . $vars->tablepre . "posts "
             . "WHERE tid=$tid "
             . "ORDER BY dateline ASC, pid ASC "
             . "LIMIT {$mpage['start']}, 1";
        $query1 = $db->query($sql);
        if ($row = $db->fetch_array($query1)) {
            $criteria = "AND (dateline > {$row['dateline']} OR dateline = {$row['dateline']} AND pid >= {$row['pid']})";
        }
        $db->free_result($query1);
    }

    // This second query retrieves table data via multi-column criteria.
    $sql = "SELECT * "
         . "FROM " . $vars->tablepre . "posts "
         . "WHERE tid=$tid $criteria "
         . "ORDER BY dateline ASC, pid ASC "
         . "LIMIT $offset $printable_ppp";

    $querypost = $db->query($sql);
    if ($forum['attachstatus'] == 'on') {
        $queryattach = $db->query("SELECT a.aid, a.pid, a.filename, a.filetype, a.filesize, a.downloads, a.img_size, thumbs.aid AS thumbid, thumbs.filename AS thumbname, thumbs.img_size AS thumbsize FROM " . $vars->tablepre . "attachments AS a LEFT JOIN " . $vars->tablepre . "attachments AS thumbs ON a.aid=thumbs.parentid INNER JOIN " . $vars->tablepre . "posts AS p ON a.pid=p.pid WHERE p.tid=$tid AND a.parentid=0");
    }

    $counter = 0;
    $template->posts = '';
    while ($post = $db->fetch_array($querypost)) {
        $date = gmdate($vars->dateformat, $core->timeKludge((int) $post['dateline']));
        $time = gmdate($vars->timecode, $core->timeKludge((int) $post['dateline']));
        $subTemplate->poston = "$date $lang[textat] $time";
        $bbcodeoff = $post['bbcodeoff'];
        $smileyoff = $post['smileyoff'];
        if ($counter == 0) {
            $subTemplate->subject = '';
        } else {
            $subTemplate->subject = $core->rawHTMLsubject(stripslashes($post['subject']));
        }
        if ($forum['attachstatus'] == 'on' && $db->num_rows($queryattach) > 0) {
            $files = [];
            $db->data_seek($queryattach, 0);
            while ($attach = $db->fetch_array($queryattach)) {
                if ($attach['pid'] === $post['pid']) {
                    $files[] = $attach;
                }
            }
            if (count($files) > 0) {
                $core->bbcodeFileTags($post['message'], $files, (int) $post['pid'], ($forum['allowbbcode'] == 'yes' && $bbcodeoff == 'no'));
            }
        }
        $post['message'] = $core->postify(stripslashes($post['message']), $smileyoff, $bbcodeoff, $forum['allowsmilies'], 'no', $forum['allowbbcode'], $forum['allowimgcode']);
        $subTemplate->post = $post;
        $template->posts .= $subTemplate->process('viewthread_printable_row.php');
        $counter++;
    }
    $db->free_result($querypost);
    $template->thread = $thread;
    $template->process('viewthread_printable.php', echo: true);
} else {
    header('HTTP/1.0 404 Not Found');
    $core->error($lang['textnoaction']);
}
