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

$attachSvc = Services\attach();
$core = Services\core();
$db = Services\db();
$forums = Services\forums();
$sqlSvc = Services\sql();
$smile = Services\smile();
$template = Services\template();
$token = Services\token();
$vars = Services\vars();
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
    $core->redirect("{$full_url}viewthread.php?tid=$tid$page#pid$pid", timeout: 0);

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

$thread['subject'] = shortenString($core->rawHTMLsubject($thread['subject']));

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

$perms = $core->assertForumPermissions($forum);

$core->forumBreadcrumbs($forum);
$core->nav($thread['subject']);

if ($SETTINGS['subject_in_title'] == 'on') {
    $template->threadSubject = $thread['subject'] . ' - ';
}

// Search-link
$template->searchlink = $core->makeSearchLink((int) $forum['fid']);

$template->replylink = '';
$template->quickreply = '';

$subTemplate = new Template($vars);
$subTemplate->addRefs();
$subTemplate->fid = $fid;
$subTemplate->tid = $tid;

$status1 = $core->modcheck($vars->self['username'], $forum['moderator']);

if ($action == '') {
    $ranks = new Ranks($sqlSvc, $vars);
    $render = new ThreadRender($core, $ranks, $sqlSvc, $vars);

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
            $quickTemplate = new Template($vars);
            $quickTemplate->addRefs();
            $quickTemplate->tid = $tid;
            $quickTemplate->allowimgcode = ($forum['allowimgcode'] == 'yes') ? $lang['texton']:$lang['textoff'];
            $quickTemplate->allowhtml = $lang['textoff'];
            $quickTemplate->allowsmilies = ($forum['allowsmilies'] == 'yes') ? $lang['texton']:$lang['textoff'];
            $quickTemplate->allowbbcode = ($forum['allowbbcode'] == 'yes') ? $lang['texton']:$lang['textoff'];

            if (X_MEMBER) {
                $quickTemplate->usesigcheck = ($vars->self['sig'] != '') ? $vars::cheHTML : '';
                $quickTemplate->subcheck = ('yes' == $vars->self['sub_each_post']) ? $vars::cheHTML : '';
            } else {
                $quickTemplate->usesigcheck = '';
                $quickTemplate->subcheck = '';
            }
            $quickTemplate->captchapostcheck = '';
            if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on') {
                $Captcha = new Captcha($core, $vars);
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
            $options = $sqlSvc->getVoteOptions($vote_id, $quarantine);
            foreach ($options as $option) {
                $num_votes += (int) $option['vote_result'];
                $pollentry = [];
                $pollentry['name'] = $core->postify(
                    message: $option['vote_option_text'],
                    allowsmilies: $forum['allowsmilies'],
                    allowbbcode: $forum['allowbbcode'],
                    allowimgcode: 'no',
                    ismood: 'yes',
                );
                $pollentry['votes'] = $option['vote_result'];
                $poll[] = $pollentry;
            }
            unset($options);

            foreach ($poll as $array) {
                $subTemplate->pollbar = '';
                if ((int) $array['votes'] > 0) {
                    $orig = round($array['votes']/$num_votes*100, 2);
                    $subTemplate->percentage = round($orig, 2) . '%';
                    $poll_length = (int) $orig;
                    if ($poll_length > 97) {
                        $poll_length = 97;
                    }
                    $subTemplate->pollbar = "<img src='{$full_url}" . $vars->theme['imgdir'] . "/pollbar.gif' height=10 width='{$poll_length}%' alt='{$lang['altpollpercentage']}' title='{$lang['altpollpercentage']}' border='0' />";
                } else {
                    $subTemplate->percentage = '0%';
                }
                $subTemplate->name = $array['name'];
                $subTemplate->votes = $array['votes'];
                $subTemplate->pollhtml .= $subTemplate->process('viewthread_poll_options_view.php');
            }
            $subTemplate->buttoncode = '';
        } else {
            $subTemplate->token = $token->create('View Thread/Poll Vote', (string) $vote_id, $vars::NONCE_FORM_EXP);

            $subTemplate->results = "- [<a href='{$full_url}viewthread.php?tid=$tid&amp;viewresults=yes'><font color='" . $vars->theme['cattext'] . "'>{$lang['viewresults']}</font></a>]";

            $subTemplate->pollhtml = $render->pollOptionsVotable($vote_id, $quarantine, $forum['allowsmilies'], $forum['allowbbcode']);

            $subTemplate->buttoncode = $subTemplate->process('viewthread_poll_submitbutton.php');
        }
        $subTemplate->subject = $thread['subject'];
        $template->poll = $subTemplate->process('viewthread_poll.php');
    }

    if (X_MEMBER && 'yes' == $vars->self['waiting_for_mod']) {
        $quarantine = true;
        $result = $sqlSvc->countPosts($quarantine, $tid, $vars->self['username']);
        if ($result > 0) {
            if (1 == $result) {
                $msg = $lang['moderation_replies_single'];
            } else {
                $msg = str_replace('$result', (string) $result, $lang['moderation_replies_eval']);
            }
            $template->poll .= $core->message(
                msg: $msg,
                showheader: false,
                die: false,
                return_as_string: true,
                showfooter: false,
            ) . "<br />\n";
        }
        $quarantine = false;
    }

    $startdate = 0;
    $startpid = 0;
    $enddate = 0;
    $sql = "SELECT dateline, pid "
         . "FROM " . $vars->tablepre . "posts "
         . "WHERE tid=$tid "
         . "ORDER BY dateline ASC, pid ASC "
         . "LIMIT {$mpage['start']}, ".($vars->ppp + 1);
    $query1 = $db->query($sql);
    $rowcount = $db->num_rows($query1);
    if ($rowcount > 0) {
        $row = $db->fetch_array($query1);
        $startdate = (int) $row['dateline'];
        $startpid = (int) $row['pid'];
        if ($rowcount <= $vars->ppp) {
            $enddate = $vars->onlinetime;
        } else {
            $db->data_seek($query1, $rowcount - 1);
            $row = $db->fetch_array($query1);
            $enddate = (int) $row['dateline'];
        }
    }
    $db->free_result($query1);

    $subTemplate->thisbg = $vars->theme['altbg2'];
    
    if ($SETTINGS['show_logs_in_threads'] == 'on') {
        $posts = $sqlSvc->getPostsAndLogsForThreadPage($tid, $startdate, $enddate, $startpid, $vars->ppp);
    } else {
        $posts = $sqlSvc->getPostsForThreadPage($tid, $startdate, $startpid, $vars->ppp);
    }

    if ($forum['attachstatus'] == 'on') {
        $pids = [];
        foreach ($posts as $post) {
            if ($post['pid'] != '') {
                $pids[] = (int) $post['pid'];
            }
        }
        $attachments = $sqlSvc->getAttachmentsByPIDs($pids);
    } else {
        $attachments = [];
    }

    $template->posts = '';

    foreach ($posts as $post) {
        // Perform automatic maintenance
        if ($post['type'] == 'post' && $post['fid'] !== $thread['fid']) {
            // Also verify the value that we expect to overwrite.
            $db->query('UPDATE ' . $vars->tablepre . "posts SET fid = {$thread['fid']} WHERE pid = {$post['pid']} AND fid = {$post['fid']}");
            $post['fid'] = $thread['fid'];
        }

        $render->preparePost($post, $subTemplate);

        if ($perms[$vars::PERMS_REPLY] && ($thread['closed'] != 'yes' || X_SADMIN)) {
            // Already set
        } else {
            $subTemplate->repquote = '';
        }

        if ($core->modcheckPost($vars->self['username'], $forum['moderator'], $post['status']) == 'Moderator' || ($thread['closed'] != 'yes' && $post['author'] == $vars->xmbuser)) {
            // Already set
        } else {
            $subTemplate->edit = '';
        }

        $render->preparePostBody($post, $forum, $attachments, $quarantine, $subTemplate);

        if ($post['type'] == 'post') {
            if ($post['subject'] == '') {
                $subTemplate->linktitle = $thread['subject'];
            }

            $template->posts .= $subTemplate->process('viewthread_post.php');
        } else {
            $subTemplate->message = $lang["modlog_{$post['subject']}"] . "<br />$date {$lang['textat']} $time";

            $template->posts .= $subTemplate->process('viewthread_modlog.php');
        }

        if ($subTemplate->thisbg == $vars->theme['altbg2']) {
            $subTemplate->thisbg = $vars->theme['altbg1'];
        } else {
            $subTemplate->thisbg = $vars->theme['altbg2'];
        }
    } // post loop
    unset($posts);

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
        $adjStamp = $core->timeKludge((int) $post['dateline']);
        $date = $core->printGmDate($adjStamp);
        $time = gmdate($vars->timecode, $adjStamp);
        $subTemplate->poston = "$date $lang[textat] $time";
        $bbcodeoff = $post['bbcodeoff'];
        $smileyoff = $post['smileyoff'];
        if ($counter == 0) {
            $subTemplate->subject = '';
        } else {
            $subTemplate->subject = $core->rawHTMLsubject($post['subject']);
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
                $post['message'] = $core->bbcodeFileTags($post['message'], $files, (int) $post['pid'], ($forum['allowbbcode'] == 'yes' && $bbcodeoff == 'no'));
            }
        }
        $post['message'] = $core->postify($post['message'], $smileyoff, $bbcodeoff, $forum['allowsmilies'], 'no', $forum['allowbbcode'], $forum['allowimgcode']);
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
