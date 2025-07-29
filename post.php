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

$attachSvc = Services\attach();
$core = Services\core();
$db = Services\db();
$email = Services\email();
$forums = Services\forums();
$settings = Services\settings();
$sql = Services\sql();
$template = Services\template();
$tran = Services\translation();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$subTemplate = new Template($vars);
$subTemplate->addRefs();

if (X_GUEST) {
    if (! $core->coppa_check()) {
        // User previously attempted registration with age < 13.
        $core->message($lang['coppa_fail']);
    }
    $template->loggedin = '';
} else {
    $subTemplate->hUsername = $vars->self['username'];
    $template->loggedin = $subTemplate->process('post_loggedin.php');
}

if ($vars->self['ban'] == "posts" || $vars->self['ban'] == "both") {
    $core->message($lang['textbanfrompost']);
}

//Validate $pid, $tid, $fid, and $repquote
$fid = -1;
$tid = -1;
$pid = -1;
$repquote = -1;
$action = getPhpInput('action', 'g');
if ($action == 'edit') {
    $pid = getRequestInt('pid');
    $query = $db->query("SELECT f.*, t.tid FROM " . $vars->tablepre . "posts AS p LEFT JOIN " . $vars->tablepre . "threads AS t USING (tid) LEFT JOIN " . $vars->tablepre . "forums AS f ON f.fid = t.fid WHERE p.pid = $pid");
    if ($db->num_rows($query) != 1) {
        header('HTTP/1.0 404 Not Found');
        $core->error($lang['textnothread']);
    }
    $forum = $db->fetch_array($query);
    $db->free_result($query);
    $fid = (int) $forum['fid'];
    $tid = (int) $forum['tid'];
} elseif ($action == 'reply') {
    $tid = getRequestInt('tid');
    $repquote = getInt('repquote');
    $query = $db->query("SELECT f.* FROM " . $vars->tablepre . "threads AS t LEFT JOIN " . $vars->tablepre . "forums AS f USING (fid) WHERE t.tid = $tid");
    if ($db->num_rows($query) != 1) {
        header('HTTP/1.0 404 Not Found');
        $core->error($lang['textnothread']);
    }
    $forum = $db->fetch_array($query);
    $db->free_result($query);
    $fid = (int) $forum['fid'];
} elseif ($action == 'newthread') {
    $fid = getRequestInt('fid');
    $forum = $forums->getForum($fid);
    if ($forum === null) {
        header('HTTP/1.0 404 Not Found');
        $core->error($lang['textnoforum']);
    }
} else {
    header('HTTP/1.0 404 Not Found');
    $core->error($lang['textnoaction']);
}
$template->fid = $fid;
$template->tid = $tid;
$template->pid = $pid;

if (($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on') {
    header('HTTP/1.0 404 Not Found');
    $core->error($lang['textnoforum']);
}

if ($tid > 0) {
    $query = $db->query("SELECT * FROM " . $vars->tablepre . "threads WHERE tid = $tid");
    if ($db->num_rows($query) != 1) {
        header('HTTP/1.0 404 Not Found');
        $core->error($lang['textnothread']);
    }
    $thread = $db->fetch_array($query);
    $db->free_result($query);
    $threadname = $core->rawHTMLsubject($thread['subject']);
} else {
    $thread = [];
    $threadname = '';
}

// Initialize some of the primary template values.
$template->attachfile = '';
$template->captchapostcheck = '';
$template->preview = '';
$template->suggestions = '';

$errors = '';
if (X_GUEST) {
    $sql_username = 'Anonymous';
    $username = 'Anonymous';
} else {
    $sql_username = $vars->xmbuser;
    $username = $vars->self['username'];
}
$subTemplate->username = $username;

$poll = getPhpInput('poll', 'g');
if ($poll != 'yes') {
    $poll = '';
}

$perms = $core->assertForumPermissions($forum);

// check posting permissions specifically
switch ($action) {
    case 'newthread':
        if (($poll == '' && ! $perms[$vars::PERMS_THREAD]) || ($poll == 'yes' && ! $perms[$vars::PERMS_POLL])) {
            if (X_GUEST) {
                $core->redirect($vars->full_url . "misc.php?action=login", timeout: 0);
            } else {
                $core->error($lang['textnoaction']);
            }
        }
        break;
    case 'reply':
        if (! $perms[$vars::PERMS_REPLY]) {
            if (X_GUEST) {
                $core->redirect($vars->full_url . "misc.php?action=login", timeout: 0);
            } else {
                $core->error($lang['textnoaction']);
            }
        }
        break;
    case 'edit':
        if (X_GUEST) {
            $core->error($lang['noedit']);
        } else {
            // let's allow edits for now, we'll check for permissions later on in the script (due to need for $orig['author'])
        }
        break;
    default:
        $core->error($lang['textnoaction']);
}
unset($perms);

$core->forumBreadcrumbs($forum);

// Search-link
$template->searchlink = $core->makeSearchLink((int) $forum['fid']);

// Moderation of new users
if (X_STAFF || 'off' == $settings->get('quarantine_new_users')) {
    // Default immunity
    $quarantine = false;
} else {
    $quarantine = true;
    if (X_MEMBER) {
        if ('yes' == $vars->self['waiting_for_mod']) {
            // Member is already flagged for quarantine.
        } elseif ((int) $vars->self['postnum'] > 0) {
            // Member has posted before and is immune.
            $quarantine = false;
        } else {
            // Member has not posted before and will be flagged for quarantine starting now.
            $sql->startMemberQuarantine((int) $vars->self['uid']);
        }
    } else {
        // Guests have no immunity.
    }
}

if (! ini_get('file_uploads')) {
    $forum['attachstatus'] = 'off';
}

$posticon = $validate->postedVar('posticon', 'javascript', dbescape: false);
if ($posticon != '') {
    if (! isValidFilename($posticon)) {
        $posticon = '';
    } elseif (! file_exists(ROOT . $vars->theme['smdir'] . '/' . $posticon)) {
        $posticon = '';
    } elseif (! $sql->iconExists($posticon)) {
        $posticon = '';
    }
}
$sql_posticon = $db->escape($posticon);

$template->allowimgcode = ($forum['allowimgcode'] == 'yes' && $forum['allowbbcode'] == 'yes') ? $lang['texton'] : $lang['textoff'];
$template->allowhtml = $lang['textoff'];
$template->allowsmilies = ($forum['allowsmilies'] == 'yes') ? $lang['texton'] : $lang['textoff'];
$template->allowbbcode = ($forum['allowbbcode'] == 'yes') ? $lang['texton'] : $lang['textoff'];

$bbcodeoff = formYesNo('bbcodeoff');
$smileyoff = formYesNo('smileyoff');
if (X_MEMBER) {
    $delete = formYesNo('delete');
    $emailnotify = formYesNo('emailnotify');
    if ($emailnotify != 'yes') {
        $emailnotify = $vars->self['sub_each_post'];
    }
    $usesig = formYesNo('usesig');
} else {
    $delete = 'no';
    $emailnotify = 'no';
    $usesig = 'no';
}

$template->emailnotifycheck = ($emailnotify == 'yes') ? $vars::cheHTML : '';

// New bool vars to clear up the confusion about effective settings.
$bBBcodeInserterEnabled = ($settings->get('bbinsert') == 'on' && $forum['allowbbcode'] == 'yes');
$bBBcodeOnForThisPost = ($forum['allowbbcode'] == 'yes' && $bbcodeoff == 'no');
$bIMGcodeOnForThisPost = ($bBBcodeOnForThisPost && $forum['allowimgcode'] == 'yes');
$bSmilieInserterEnabled = ($settings->get('smileyinsert') == 'on' && $forum['allowsmilies'] == 'yes');
$bSmiliesOnForThisPost = ($forum['allowsmilies'] == 'yes' && $smileyoff == 'no');

$topcheck = '';
$closecheck = '';
$toptopic = 'no';
$closetopic = 'no';
if (X_STAFF) {
    $toptopic = formYesNo('toptopic');
    $closetopic = formYesNo('closetopic');
    
    if ('yes' == $toptopic) {
        $topcheck = $vars::cheHTML;
    }
    if ('yes' == $closetopic) {
        $closecheck = $vars::cheHTML;
    }
}

$messageinput = $validate->postedVar('message', dbescape: false);
$subjectinput = $validate->postedVar('subject', dbescape: false);
$messageinput = trim($messageinput);
$subjectinput = trim($subjectinput);
$subjectinput = str_replace(["\r", "\n"], ['', ''], $subjectinput);

$template->bbcodeinsert = '';
$template->bbcodescript = '';
$template->moresmilies = '';
$template->smilieinsert = '';
if ($bBBcodeInserterEnabled || $bSmilieInserterEnabled) {
    $template->bbcodescript = $template->process('functions_bbcode.php');
    if ($bBBcodeInserterEnabled) {
        $subTemplate->mode0check = '';
        $subTemplate->mode1check = '';
        $subTemplate->mode2check = '';
        $mode = isset($mode) ? formInt('mode') : 2;
        switch ($mode) {
            case 0:
                $subTemplate->mode0check = $vars::cheHTML;
                $subTemplate->setbbcodemode = 'advmode=true;normalmode=false;';
                break;
            case 1:
                $subTemplate->mode1check = $vars::cheHTML;
                $subTemplate->setbbcodemode = 'helpmode=true;normalmode=false;';
                break;
            default:
                $subTemplate->mode2check = $vars::cheHTML;
                $subTemplate->setbbcodemode = '';
                break;
        }
        $template->bbcodeinsert = $subTemplate->process('functions_bbcodeinsert.php');
    }
    if ($bSmilieInserterEnabled) {
        $template->smilieinsert = $core->smilieinsert();
        $template->moresmilies = "<a href='" . $vars->full_url . "misc.php?action=smilies' onclick=\"Popup(this.href, 'Window', 175, 250); return false;\">[{$lang['moresmilies']}]</a>";
    }
}

// Breadcrumbs
switch ($action) {
    case 'reply':
        $core->nav("<a href='" . $vars->full_url . "viewthread.php?tid=$tid'>$threadname</a>");
        $core->nav($lang['textreply']);
        break;
    case 'newthread':
        if ($poll == 'yes') {
            $core->nav($lang['textnewpoll']);
        } else {
            $core->nav($lang['textpostnew']);
        }
        break;
    case 'edit':
        $core->nav("<a href='" . $vars->full_url . "viewthread.php?tid=$tid'>$threadname</a>");
        $core->nav($lang['texteditpost']);
}

// Title
if ($settings->get('subject_in_title') === 'on') {
    switch ($action) {
        case 'reply':
            $template->threadSubject = $lang['textreply'] . ' - ';
            break;
        case 'newthread':
            $template->threadSubject = $lang['textpostnew'] . ' - ';
            break;
        case 'edit':
            $template->threadSubject = $lang['texteditpost'] . ' - ';
    }
}

// Set initial validity of inputs (when other than preview POST or any GET)
switch ($action) {
    case 'reply':
        $validForSave = onSubmit('replysubmit');
        break;
    case 'newthread':
        $validForSave = onSubmit('topicsubmit');
        break;
    case 'edit':
        $validForSave = onSubmit('editsubmit');
}

// Check permissions on action
switch ($action) {
    case 'reply':
        if (! X_SADMIN && $thread['closed'] != '') {
            if ($validForSave) {
                $errors .= $core->softerror($lang['closedmsg']);
            } else {
                $core->error($lang['closedmsg']);
            }
        }
        $isMod = $core->modcheck($username, $forum['moderator']);
        break;
    case 'newthread':
        $isMod = $core->modcheck($username, $forum['moderator']);
        break;
    case 'edit':
        // Based on viewthread design, forum Moderators can always edit, $orig['author'] can edit open threads only.
        $result = $db->query("SELECT p.*, m.status, m.sig FROM " . $vars->tablepre . "posts p LEFT JOIN " . $vars->tablepre . "members m ON p.author = m.username WHERE p.pid = $pid");
        $orig = $db->fetch_array($result);
        $db->free_result($result);
        $orig['sig'] ??= '';
        $orig['status'] ??= '';

        $isMod = $core->modcheckPost($vars->self['username'], $forum['moderator'], $orig['status']);

        if (! $isMod) {
            $delete = 'no';
            if ($vars->self['username'] !== $orig['author'] || $thread['closed'] != '') {
                $core->error($lang['noedit']);
            }
        }
}

// Attachment pre-processing
if ($forum['attachstatus'] == 'on' && X_MEMBER) {
    for ($i = 1; $i <= $settings->get('filesperpost'); $i++) {
        if (isset($_FILES['attach' . $i])) {
            $result = $attachSvc->uploadedFile('attach' . $i, 0, $quarantine);
            if ($result->status !== UploadStatus::Success && $result->status !== UploadStatus::EmptyUpload) {
                $errors .= $core->softerror($attachSvc->uploadErrorMsg($result->status));
            }
        }
    }
    if ($action == 'edit') {
        $children = false;
        $aid_list = $sql->getAttachmentIDsByPost($pid, $children);
    } else {
        $aid_list = $sql->getOrphanedAttachmentIDs((int) $vars->self['uid'], $quarantine);
    }
    $deletes = [];
    $status = $attachSvc->doEdits($deletes, $aid_list, 0, $quarantine);
    if ($status !== UploadStatus::Success) {
        $errors .= $core->softerror($attachSvc->uploadErrorMsg($status));
    }
    foreach ($deletes as $aid) {
        $messageinput = str_replace("[file]{$aid}[/file]", '', $messageinput);
    }
    if ($settings->get('attach_remote_images') == 'on' && $bIMGcodeOnForThisPost) {
        $status = $attachSvc->remoteImages(0, $messageinput, $quarantine);
        if ($status !== UploadStatus::Success) {
            $errors .= $core->softerror($attachSvc->uploadErrorMsg($status));
        }
    }
    $attachSkipped = false;
} else {
    $attachSkipped = true;
}

// CAPTCHA input
if ($validForSave && $action !== 'edit' && X_GUEST && $settings->get('captcha_status') == 'on' && $settings->get('captcha_post_status') == 'on') {
    $captcha = new Captcha($core, $vars);
    if ($captcha->bCompatible !== false) {
        $imgcode = getPhpInput('imgcode');
        $imghash = getPhpInput('imghash');
        if ($captcha->ValidateCode($imgcode, $imghash) !== true) {
            $errors .= $core->softerror($lang['captchaimageinvalid']);
        }
    }
    unset($captcha);
}

// Check required fields
if ($validForSave) {
    switch ($action) {
        case 'reply':
        case 'edit':
            $isFirstPost = $pid == $sql->getFirstPostInThread($tid);
            if (strlen($subjectinput) == 0) {
                if (strlen($messageinput) == 0) {
                    $errors .= $core->softerror($lang['postnothing']);
                } elseif ($delete != 'yes') {
                    // Check if this is the first post in the thread.
                    if ($isFirstPost) {
                        $errors .= $core->softerror($lang['textnosubject']);
                    }
                }
            }
            break;
        case 'newthread':
            if (strlen($subjectinput) == 0) {
                $errors .= $core->softerror($lang['textnosubject']);
            }
    }
}

// Flood protection
if ($validForSave && $action !== 'edit') {
    $floodLimit = $vars->onlinetime - (int) $settings->get('floodctrl');
    if (X_GUEST) {
        if ((int) $settings->get('anon_post_date') >= $floodLimit) {
            $errors .= $core->softerror($lang['floodprotect']);
        }
    } elseif ((int) $vars->self['post_date'] >= $floodLimit) {
        $errors .= $core->softerror($lang['floodprotect']);
    }
}

// Generate pollopts array and check the count
if ($action === 'newthread' && $poll === 'yes') {
    $template->pollanswers = $validate->postedVar('pollanswers', dbescape: false);

    if ($validForSave) {
        $pollopts = [];
        $pollopts2 = explode("\n", $template->pollanswers);
        foreach ($pollopts2 as $value) {
            $value = trim($value);
            if ($value != '') {
                $pollopts[] = $value;
            }
        }
        unset($pollopts2);

        if (count($pollopts) < 2) {
            $errors .= $core->softerror($lang['too_few_pollopts']);
        }
    }
}

// Expand any [pid]1234[/pid] BBCodes
if ($bBBcodeOnForThisPost) {
    $core->postLinkBBcode($messageinput);
}

// All soft errors have been processed. If there were no errors, then let's save the inputs now.
if ($validForSave && $errors == '') {
    if ($action == 'edit' && $settings->get('editedby') == 'on') {
        $messageinput .= "\n\n[{$lang['textediton']} " . $core->printGmDate(time()) . " {$lang['textby']} $username]";
    }

    // Assign tentative message & subject values, to be adjusted.  Leave the originals available for other needs.
    $dbmessage = $messageinput;
    $dbsubject = $subjectinput;

    // Check for field overflow
    if (strlen($dbmessage) > $vars::POST_MSG_MAX_LEN) {
        $dbmessage = substr($dbmessage, 0, $vars::POST_MSG_MAX_LEN);
    }
    if (strlen($dbsubject) > $vars::THREAD_SUB_MAX_LEN) {
        $dbsubject = substr($dbsubject, 0, $vars::THREAD_SUB_MAX_LEN);
    }
    if (strlen($vars->onlineip) > $vars::IP_ADDRESS_MAX_LENGTH) {
        $useip = '';
    } else {
        $useip = $vars->onlineip;
    }

    // Create a thread record
    $lastpost = $vars->onlinetime . '|' . $username;
    if ($action == 'newthread') {
        $closed = '';
        $topped = 0;
        $dbpollopts = ('yes' == $poll) ? 1 : 0;

        if ($isMod) {
            if ('yes' == $closetopic) {
                // Be careful here; threads.closed is historically yes/moved/empty rather than yes/no.
                $closed = 'yes';
            }
            if ($toptopic == 'yes') {
                $topped = 1;
            }
        }

        $values = [
            'fid' => $fid,
            'subject' => $dbsubject,
            'icon' => $posticon,
            'lastpost' => $lastpost,
            'author' => $username,
            'closed' => $closed,
            'topped' => $topped,
            'pollopts' => $dbpollopts,
        ];

        $tid = $sql->addThread($values, $quarantine);
    }

    // Create/modify/delete post record
    $threaddelete = 'no';
    if ($action != 'edit') {
        if (X_MEMBER && ! $quarantine) {
            $sql->raisePostCount((int) $vars->self['uid'], $vars->onlinetime);
        } elseif (X_GUEST) {
            $settings->put('anon_post_date', $vars->onlinetime);
        }

        $values = [
            'fid' => $fid,
            'tid' => $tid,
            'dateline' => $vars->onlinetime,
            'author' => $username,
            'message' => $dbmessage,
            'subject' => $dbsubject,
            'icon' => $posticon,
            'usesig' => $usesig,
            'useip' => $useip,
            'bbcodeoff' => $bbcodeoff,
            'smileyoff' => $smileyoff,
        ];

        $qthread = ($quarantine && $action == 'newthread'); // Signal that we don't want to associate this post with a non-quarantined TID.

        $pid = $sql->addPost($values, $quarantine, $qthread);
    } elseif ($delete != 'yes') {
        // Post was edited
        $sql_message = $db->escape($dbmessage);
        $sql_subject = $db->escape($dbsubject);

        $db->query("
            UPDATE " . $vars->tablepre . "posts
            SET message = '$sql_message', usesig = '$usesig', bbcodeoff = '$bbcodeoff', smileyoff = '$smileyoff', icon = '$sql_posticon', subject = '$sql_subject'
            WHERE pid = $pid
        ");
    } else {
        // Post was deleted.  Let's also figure out if the thread needs to be removed.
        $db->query("DELETE FROM " . $vars->tablepre . "posts WHERE pid = $pid");
        if ($orig['author'] != 'Anonymous') {
            $sql->adjustPostCount($orig['author'], -1);
        }
        $attachSvc->deleteByPost($pid);

        if ($isFirstPost) {
            if ($sql->countPosts(tid: $tid) == 0) {
                $threaddelete = 'yes';
            } else {
                $db->query("UPDATE " . $vars->tablepre . "posts SET subject = '" . $db->escape($orig['subject']) . "' WHERE tid = $tid ORDER BY dateline LIMIT 1");
            }
        }
    }

    // Modify the thread
    $lastpost .= "|$pid";
    $fupArg = $forum['type'] == 'sub' ? (int) $forum['fup'] : null;
    switch ($action) {
        case 'reply':
            if (! $quarantine) {
                $close = ($closetopic == 'yes' && $isMod);
                $sql->setThreadLastpost($tid, $lastpost, newReply: true, close: $close);
                $sql->setForumCounts($fid, $lastpost, fup: $fupArg, newReply: true);
            }
            break;
        case 'newthread':
            $sql->setThreadLastpost($tid, $lastpost, $quarantine);
            if (! $quarantine) {
                $sql->setForumCounts($fid, $lastpost, fup: $fupArg, newReply: true);
            }
            break;
        case 'edit':
            if ($isFirstPost && $delete != 'yes') {
                $db->query("UPDATE " . $vars->tablepre . "threads SET icon = '$sql_posticon', subject = '$sql_subject' WHERE tid = $tid");
            } elseif ($delete == 'yes') {
                if ($isFirstPost) {
                    if ($threaddelete == 'yes') {
                        $db->query("DELETE FROM " . $vars->tablepre . "favorites WHERE tid = '$tid'");
                        $sql->deleteVotesByTID([$tid]);
                        $db->query("DELETE FROM " . $vars->tablepre . "threads WHERE tid = $tid OR closed = 'moved|$tid'");
                    } else {
                        $core->updatethreadcount($tid);
                    }
                } else {
                    $core->updatethreadcount($tid);
                }
                if ($forum['type'] == 'sub') {
                    $core->updateforumcount((int) $forum['fup']);
                }
                $core->updateforumcount($fid);
            }
    }

    // Attachment post-processing
    if ($action != 'edit' && $forum['attachstatus'] == 'on') {
        if ($attachSkipped) {
            for ($i = 1; $i <= $settings->get('filesperpost'); $i++) {
                if (isset($_FILES["attach$i"])) {
                    $attachSvc->uploadedFile("attach$i", $pid, $quarantine);
                }
            }
            if ($settings->get('attach_remote_images') == 'on' && $bIMGcodeOnForThisPost) {
                $attachSvc->remoteImages($pid, $messageinput, $quarantine);
                $newdbmessage = $messageinput;
                if ($newdbmessage !== $dbmessage) { // Anonymous message was modified after save, in order to use the pid.
                    $sql->savePostBody($pid, $newdbmessage, $quarantine);
                }
            }
        } elseif (X_MEMBER) {
            $sql->claimOrphanedAttachments($pid, (int) $vars->self['uid'], $quarantine);
        }
    }

    // Add a poll and related options
    if ($action == 'newthread' && $poll == 'yes') {
        // Create a poll ID.  Works like a junction table even though we only support one poll per thread.
        $vote_id = $sql->addVoteDesc($tid, $quarantine);
        
        // Create poll options.  This is the part we care about.
        $options = [];
        $i = 1;
        foreach ($pollopts as $p) {
            $options[] = [
                'vote_id' => $vote_id,
                'vote_option_id' => $i++,
                'vote_option_text' => $p,
            ];
        }
        $sql->addVoteOptions($options, $quarantine);
    }

    // Add Subscription
    if ($action != 'edit' && 'yes' == $emailnotify) {
        $sql->addFavoriteIfMissing((int) $tid, $username, 'subscription');
    }

    // Update cookies
    if ($action != 'edit' && X_MEMBER && ! $quarantine) {
        $expire = $vars->onlinetime + $vars::ONLINE_TIMER;
        if ($vars->oldtopics == '') {
            $vars->oldtopics = "|$pid|";
        } else {
            $vars->oldtopics .= "$pid|";
        }
        $core->put_cookie('oldtopics', $vars->oldtopics, $expire);
    }

    // Send subscription notifications
    if ($action == 'reply' && ! $quarantine) {
        $query = $db->query("SELECT COUNT(*) FROM " . $vars->tablepre . "posts WHERE pid <= $pid AND tid = '$tid'");
        $posts = (int) $db->result($query);
        $db->free_result($query);

        $lang2 = $tran->loadPhrases(['charset', 'textsubsubject', 'textsubbody']);
        $viewperm = $core->getOneForumPerm($forum, $vars::PERMS_RAWVIEW);

        $query = $db->query("SELECT dateline FROM " . $vars->tablepre . "posts WHERE tid = $tid AND pid < $pid ORDER BY dateline DESC LIMIT 1");
        if ($db->num_rows($query) > 0) {
            $date = $db->result($query);
        } else {
            // Replying to a thread that has zero posts.
            $date = '0';
        }
        $db->free_result($query);

        $subquery = $db->query("SELECT m.email, m.lastvisit, m.ppp, m.status, m.langfile "
                             . "FROM " . $vars->tablepre . "favorites f "
                             . "INNER JOIN " . $vars->tablepre . "members m USING (username) "
                             . "WHERE f.type = 'subscription' AND f.tid = $tid AND m.username != '$sql_username' AND m.lastvisit >= $date");
        while ($subs = $db->fetch_array($subquery)) {
            if ($viewperm < $vars->status_enum[$subs['status']]) {
                continue;
            }

            if ($subs['ppp'] < 1) {
                $subs['ppp'] = $posts;
            }

            $translate = $lang2[$subs['langfile']];
            $topicpages = $core->quickpage($posts, (int) $subs['ppp']);
            $topicpages = ($topicpages == 1) ? '' : '&page='.$topicpages;
            $threadurl = $vars->full_url . 'viewthread.php?tid='.$tid.$topicpages.'#pid'.$pid;
            $rawsubject = rawHTML($threadname);
            $rawusername = rawHTML($username);
            $rawemail = rawHTML($subs['email']);
            $title = "$rawsubject ({$translate['textsubsubject']})";
            $body = "$rawusername {$translate['textsubbody']} \n$threadurl";
            $email->send($rawemail, $title, $body, $translate['charset']);
        }
        $db->free_result($subquery);
    }


    // Send response
    if ($quarantine) $core->message($lang['moderation_hold']);
    switch ($action) {
        case 'reply':
            $topicpages = $core->quickpage($posts, $vars->ppp);
            $topicpages = ($topicpages == 1) ? '' : '&page=' . $topicpages;
            $core->message($lang['replymsg'], redirect: $vars->full_url . "viewthread.php?tid={$tid}{$topicpages}#pid{$pid}");
            break;
        case 'newthread':
            $core->message($lang['postmsg'], redirect: $vars->full_url . "viewthread.php?tid=$tid");
            break;
        case 'edit':
            if ($threaddelete == 'no') {
                $posts = $sql->countPosts(tid: $tid, before: (int) $orig['dateline']);
                $topicpages = $core->quickpage($posts, $vars->ppp);
                $topicpages = ($topicpages == 1) ? '' : '&page=' . $topicpages;
                $core->message($lang['editpostmsg'], redirect: $vars->full_url . "viewthread.php?tid={$tid}{$topicpages}#pid{$pid}");
            } else {
                $core->message($lang['editpostmsg'], redirect: $vars->full_url . "forumdisplay.php?fid=$fid");
            }
    }
}

// Nothing saved. Show preview if requested. Show the post editor.

// Quote an existing post.
if ($action == 'reply' && $repquote > 0) {
    $query = $db->query("SELECT p.message, p.tid, p.fid, p.author FROM " . $vars->tablepre . "posts p WHERE p.pid = $repquote");
    $thaquote = $db->fetch_array($query);
    $db->free_result($query);
    $quoteperms = $core->checkForumPermissions($forums->getForum((int) $thaquote['fid']));
    if ($quoteperms[$vars::PERMS_VIEW] && $quoteperms[$vars::PERMS_PASSWORD]) {
        $thaquote['message'] = preg_replace('@\\[file\\]\\d*\\[/file\\]@', '', $thaquote['message']); // These codes will not work inside quotes.
        $quoteblock = $core->rawHTMLmessage($thaquote['message']);
        if ($bBBcodeOnForThisPost) {
            $messageinput = "[rquote=$repquote&amp;tid={$thaquote['tid']}&amp;author={$thaquote['author']}]{$quoteblock}[/rquote]";
        } else {
            $quotesep = '|| ';
            $quoteblock = $quotesep.str_replace("\n", "\n$quotesep", $quoteblock);
            $messageinput = "{$lang['textquote']} {$lang['origpostedby']} {$thaquote['author']}\r\n$quotesep\r\n$quoteblock\r\n\r\n";
        }
    }
}

// Abstract the latest values into $postinfo.  For post editing, these values might come from the database rather than the request.  For preview, always use request values.
if ($action == 'edit' && noSubmit('editsubmit') && noSubmit('previewpost')) {
    $postinfo = $orig;
    $bBBcodeOnForThisPost = ($forum['allowbbcode'] == 'yes' && $postinfo['bbcodeoff'] == 'no');
    $bIMGcodeOnForThisPost = ($bBBcodeOnForThisPost && $forum['allowimgcode'] == 'yes');
    $bSmiliesOnForThisPost = ($forum['allowsmilies'] == 'yes' && $postinfo['smileyoff'] == 'no');
} else {
    $postinfo = [
        'usesig' => $usesig,
        'bbcodeoff' => $bbcodeoff,
        'smileyoff' => $smileyoff,
        'message' => $messageinput,
        'subject' => $subjectinput,
        'icon' => $posticon,
        'dateline' => ($action == 'edit' ? (int) $orig['dateline'] : $vars->onlinetime),
    ];
}
unset($usesig, $bbcodeoff, $smileyoff, $messageinput, $subjectinput, $posticon);

// Generate the attachment input elements
$files = []; // This will be used again later.
if ($forum['attachstatus'] == 'on' && X_MEMBER) {
    // Quarantined members are allowed to attach files. Guests are not.
    $template->attachfile = '';
    if ($action == 'edit') {
        $files = $sql->getAttachmentsByPIDs([$pid]);
    } else {
        $files = $sql->getOrphanedAttachments($quarantine, (int) $vars->self['uid']);
    }
    $counter = 0;
    $prevsize = '';
    foreach ($files as $file) {
        if ($action == 'edit') {
            $subTemplate->aInfo = [
                'aid' => $file['aid'],
                'downloads' => $file['downloads'],
                'filename' => $file['filename'],
                'filesize' => number_format((int) $file['filesize'], 0, '.', ','),
                'url' => $attachSvc->getURL((int) $file['aid'], $pid, $file['filename']),
            ];
            $template->attachfile .= $subTemplate->process('post_edit_attachment.php');
        } else {
            $file['filesize'] = number_format((int) $file['filesize'], 0, '.', ',');
            $subTemplate->file = $file;
            $template->attachfile .= $subTemplate->process('post_attachment_orphan.php');
        }
        if ($bBBcodeOnForThisPost) {
            $bbcode = "[file]{$file['aid']}[/file]";
            if (strpos($postinfo['message'], $bbcode) === false) {
                if ($counter == 0 || $file['img_size'] == '' || $prevsize == '' || $settings->get('attachimgpost') == 'off') {
                    $postinfo['message'] .= "\n\n";
                }
                $postinfo['message'] .= ' ' . $bbcode; // Use a leading space to prevent awkward line wraps.
                $counter++;
                $prevsize = $file['img_size'];
            }
        }
    }
    $template->attachfile .= $core->makeAttachmentBox(count($files));
}

// Generate the preview, if requested.
if (onSubmit('previewpost')) {
    if ($postinfo['icon'] != '') {
        $subTemplate->icon = "<img src='" . $vars->full_url . $vars->theme['smdir'] . '/' . $postinfo['icon'] . "' />";
    } else {
        $subTemplate->icon = '';
    }
    $currtime = $core->timeKludge($postinfo['dateline']);
    $date = $core->printGmDate($currtime);
    $time = gmdate($vars->timecode, $currtime);
    $subTemplate->poston = "{$lang['textposton']} $date {$lang['textat']} $time";
    if (strlen($postinfo['subject']) > 0) {
        $subTemplate->dissubject = $core->rawHTMLsubject($postinfo['subject']) . '<br />';
    } else {
        $subTemplate->dissubject = '';
    }
    $message1 = $core->rawHTMLmessage($postinfo['message']);
    if ($action == 'edit' && $settings->get('editedby') == 'on') {
        $message1 .= "\n\n[{$lang['textediton']} " . $core->printGmDate(time()) . " {$lang['textby']} $username]";
    }
    if (count($files) > 0) {
        $message1 = $core->bbcodeFileTags($message1, $files, 0, $bBBcodeOnForThisPost, $quarantine);
    }
    $subTemplate->message1 = $core->postify($message1, $postinfo['smileyoff'], $postinfo['bbcodeoff'], $forum['allowsmilies'], allowbbcode: $forum['allowbbcode'], allowimgcode: $forum['allowimgcode']);
    if ($postinfo['usesig'] == 'yes') {
        $sigText = ($action == 'edit') ? $orig['sig'] : $vars->self['sig'];
        $subTemplate->sig = $core->postify(
            message: $sigText,
            allowsmilies: $forum['allowsmilies'],
            allowbbcode: $settings->get('sigbbcode'),
            allowimgcode: $forum['allowimgcode'],
        );
        $subTemplate->message1 .= $subTemplate->process('viewthread_post_sig.php');
    }
    if ($action == 'edit') {
        $subTemplate->username = $orig['author'];
    } else {
        $subTemplate->username = $username;
    }
    $template->preview = $subTemplate->process('post_preview.php');
}

// CAPTCHA output
if (X_GUEST && $settings->get('captcha_status') == 'on' && $settings->get('captcha_post_status') == 'on') {
    $Captcha = new Captcha($core, $vars);
    if ($Captcha->bCompatible !== false) {
        $subTemplate->imghash = $Captcha->GenerateCode();
        $template->captchapostcheck = $subTemplate->process('post_captcha.php');
    }
    unset($Captcha);
}

// Allow thread close
if ($action != 'edit' && $isMod) {
    $phrase = ($action == 'reply') ? $lang['closemsgques'] : $lang['closenewthread'];
    $template->closeoption = '<label><input type="checkbox" name="closetopic" value="yes" '.$closecheck.' /> '.$phrase.'</label>';
} else {
    $template->closeoption = '';
}

// Allow thread topping
if ($action == 'newthread' && $isMod) {
    $template->topoption = '<label><input type="checkbox" name="toptopic" value="yes" '.$topcheck.' /> '.$lang['topmsgques'] . '</label>';
} else {
    $template->topoption = '';
}

// Add the thread review
if ($action == 'reply') {
    $template->posts = '';

    $subTemplate->thisbg = $vars->theme['altbg1'];
    $posts = $sql->getPostsByTID($tid, $vars->ppp, ascending: false);
    foreach ($posts as $post) {
        $currtime = $core->timeKludge((int) $post['dateline']);
        $date = $core->printGmDate($currtime);
        $time = gmdate($vars->timecode, $currtime);
        $subTemplate->poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;

        if ($post['icon'] != '') {
            $post['icon'] = '<img src="' . $vars->full_url . $vars->theme['smdir'] . '/' . $post['icon'] . '" alt="' . $lang['altpostmood'] . '" border="0" />';
        } else {
            $post['icon'] = '<img src="' . $vars->full_url . $vars->theme['imgdir'] . '/default_icon.gif" alt="[*]" border="0" />';
        }

        $post['message'] = preg_replace('@\\[file\\]\\d*\\[/file\\]@', '', $post['message']); //These codes do not work in postify()
        $post['message'] = $core->postify($post['message'], $post['smileyoff'], $post['bbcodeoff'], $forum['allowsmilies'], 'no', $forum['allowbbcode'], $forum['allowimgcode']);
        $subTemplate->post = $post;
        $template->posts .= $subTemplate->process('post_reply_review_post.php');
        if ($subTemplate->thisbg == $vars->theme['altbg2']) {
            $subTemplate->thisbg = $vars->theme['altbg1'];
        } else {
            $subTemplate->thisbg = $vars->theme['altbg2'];
        }
    }
    unset($posts);
}

// Set checkbox values.
$template->codeoffcheck = ($postinfo['bbcodeoff'] == 'yes') ? $vars::cheHTML : '';
$template->smileoffcheck = ($postinfo['smileyoff'] == 'yes') ? $vars::cheHTML : '';
// Usesigcheck is different because it is based on the stored post record for initializing edits, the request value for saves and previews, and self['sig'] for new posts.
if ($validForSave || onSubmit('previewpost') || $action == 'edit') {
    $template->usesigcheck = $postinfo['usesig'] == 'yes' ? $vars::cheHTML : '';
} else {
    $template->usesigcheck = $vars->self['sig'] != '' ? $vars::cheHTML : '';
}
if ($action == 'edit' && $orig['author'] == 'Anonymous') {
    $template->disableguest = 'style="display:none;"';
} else {
    $template->disableguest = X_GUEST ? 'style="display:none;"' : '';
}

// Generate icon input elements
$posticon = $postinfo['icon'];
if ($posticon != '') {
    if (! isValidFilename($posticon)) {
        $posticon = '';
    } elseif (! file_exists(ROOT . $vars->theme['smdir'] . '/' . $posticon)) {
        $posticon = '';
    } elseif (! $sql->iconExists($posticon)) {
        $posticon = '';
    }
}

$listed_icons = 0;
$icons = '<input type="radio" name="posticon" value="" /> <img src="' . $vars->full_url . $vars->theme['imgdir'] . '/default_icon.gif" alt="[*]" border="0" />';
$querysmilie = $db->query("SELECT url, code FROM " . $vars->tablepre . "smilies WHERE type = 'picon'");
while ($smilie = $db->fetch_array($querysmilie)) {
    $icons .= ' <input type="radio" name="posticon" value="' . $smilie['url'] . '" /><img src="' . $vars->full_url . $vars->theme['smdir'] . '/' . $smilie['url'] . '" alt="' . $smilie['code'] . '" border="0" />';
    $listed_icons++;
    if ($listed_icons == 9) {
        $icons .= '<br />';
        $listed_icons = 0;
    }
}
$db->free_result($querysmilie);

$icons = str_replace('<input type="radio" name="posticon" value="'.$posticon.'" />', '<input type="radio" name="posticon" value="'.$posticon.'" checked="checked" />', $icons);
$template->icons = $icons;

// Subject and message values for the post editor.
$template->subject = $core->rawHTMLsubject($postinfo['subject']);
$template->message = $core->rawHTMLmessage($postinfo['message']);

// Process the templates.
switch ($action) {
    case 'reply':
        $postpage = $template->process('post_reply.php');
        break;
    case 'newthread':
        if ($poll == 'yes') {
            $postpage = $template->process('post_newpoll.php');
        } else {
            $postpage = $template->process('post_newthread.php');
        }
        break;
    case 'edit':
        $postpage = $template->process('post_edit.php');
        break;
    default:
        $core->error($lang['textnoaction']);
}

$header = $template->process('header.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $errors, $postpage, $footer;
