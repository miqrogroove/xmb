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

use XMB\UploadStatus;

require './header.php';

$attachSvc = Services\attach();
$core = Services\core();
$db = Services\db();
$email = Services\email();
$forums = Services\forums();
$sql = Services\sql();
$template = Services\template();
$tran = Services\translation();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;
$SETTINGS = &$vars->settings;

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
    $threadname = $core->rawHTMLsubject(stripslashes($thread['subject']));
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
if ($action == 'newthread') {
    if (($poll == '' && ! $perms[$vars::PERMS_THREAD]) || ($poll == 'yes' && ! $perms[$vars::PERMS_POLL])) {
        if (X_GUEST) {
            $core->redirect($vars->full_url . "misc.php?action=login", timeout: 0);
        } else {
            $core->error($lang['textnoaction']);
        }
    }
} elseif ($action == 'reply') {
    if (! $perms[$vars::PERMS_REPLY]) {
        if (X_GUEST) {
            $core->redirect($vars->full_url . "misc.php?action=login", timeout: 0);
        } else {
            $core->error($lang['textnoaction']);
        }
    }
} elseif ($action == 'edit') {
    // let's allow edits for now, we'll check for permissions later on in the script (due to need for $orig['author'])
} else {
    $core->error($lang['textnoaction']);
}
unset($perms);

$core->forumBreadcrumbs($forum);

// Search-link
$template->searchlink = $core->makeSearchLink((int) $forum['fid']);

// Moderation of new users
if (X_STAFF || 'off' == $SETTINGS['quarantine_new_users']) {
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

// TODO: Does this need to be set to $thread['posticon'] when $action == 'edit' and no edit submitted yet?
$posticon = $validate->postedVar('posticon', 'javascript', dbescape: false);
if ($posticon != '') {
    if (! isValidFilename($posticon)) {
        $posticon = '';
    } elseif (! file_exists(ROOT . $vars->theme['smdir'] . '/' . $posticon)) {
        $posticon = '';
    }
}
$sql_posticon = $db->escape($posticon);

$listed_icons = 0;
$icons = '<input type="radio" name="posticon" value="" /> <img src="' . $vars->theme['imgdir'] . '/default_icon.gif" alt="[*]" border="0" />';
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

if ($action != 'edit') {
    $icons = str_replace('<input type="radio" name="posticon" value="'.$posticon.'" />', '<input type="radio" name="posticon" value="'.$posticon.'" checked="checked" />', $icons);
}
$template->icons = $icons;

$template->allowimgcode = ($forum['allowimgcode'] == 'yes' && $forum['allowbbcode'] == 'yes') ? $lang['texton'] : $lang['textoff'];
$template->allowhtml = $lang['textoff'];
$template->allowsmilies = ($forum['allowsmilies'] == 'yes') ? $lang['texton'] : $lang['textoff'];
$template->allowbbcode = ($forum['allowbbcode'] == 'yes') ? $lang['texton'] : $lang['textoff'];

$bbcodeoff = formYesNo('bbcodeoff');
$smileyoff = formYesNo('smileyoff');
if (X_MEMBER) {
    $emailnotify = formYesNo('emailnotify');
    if ($emailnotify != 'yes') {
        $emailnotify = $vars->self['sub_each_post'];
    }
    $usesig = formYesNo('usesig');
} else {
    $emailnotify = 'no';
    $usesig = 'no';
}

$template->codeoffcheck = ($bbcodeoff == 'yes') ? $vars::cheHTML : '';
$template->emailnotifycheck = ($emailnotify == 'yes') ? $vars::cheHTML : '';
$template->smileoffcheck = ($smileyoff == 'yes') ? $vars::cheHTML : '';
if (onSubmit('previewpost')) {
    $template->usesigcheck = $usesig == 'yes' ? $vars::cheHTML : '';
} else {
    $template->usesigcheck = $vars->self['sig'] != '' ? $vars::cheHTML : '';
}
$template->disableguest = X_GUEST ? 'style="display:none;"' : '';

// New bool vars to clear up the confusion about effective settings.
$bBBcodeInserterEnabled = ($SETTINGS['bbinsert'] == 'on' && $forum['allowbbcode'] == 'yes');
$bBBcodeOnForThisPost = ($forum['allowbbcode'] == 'yes' && $bbcodeoff == 'no');
$bIMGcodeOnForThisPost = ($bBBcodeOnForThisPost && $forum['allowimgcode'] == 'yes');
$bSmilieInserterEnabled = ($SETTINGS['smileyinsert'] == 'on' && $forum['allowsmilies'] == 'yes');
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

$messageinput = $validate->postedVar('message', dbescape: false, quoteencode: false);  //postify() was responsible for decoding this if html was allowed in the past.
$subjectinput = $validate->postedVar('subject', dbescape: false);
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
        switch($mode) {
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
        $template->moresmilies = "<a href=\"misc.php?action=smilies\" onclick=\"Popup(this.href, 'Window', 175, 250); return false;\">[{$lang['moresmilies']}]</a>";
    }
}

switch ($action) {
    case 'reply':
        $core->nav('<a href="' . $vars->full_url . 'viewthread.php?tid='.$tid.'">'.$threadname.'</a>');
        $core->nav($lang['textreply']);

        if ($SETTINGS['subject_in_title'] === 'on') {
            $template->threadSubject = $threadname . ' - ';
        }

        $replyvalid = onSubmit('replysubmit'); // This new flag will indicate a message was submitted and successful.

        if ($forum['attachstatus'] == 'on' && X_MEMBER) {
            for ($i = 1; $i <= $SETTINGS['filesperpost']; $i++) {
                if (isset($_FILES['attach' . $i])) {
                    $result = $attachSvc->uploadedFile('attach' . $i, 0, $quarantine);
                    if ($result->status !== UploadStatus::Success && $result->status !== UploadStatus::EmptyUpload) {
                        $errors .= $core->softerror($attachSvc->uploadErrorMsg($result->status));
                        $replyvalid = false;
                    }
                }
            }
            $aid_list = $sql->getOrphanedAttachmentIDs((int) $vars->self['uid'], $quarantine);
            $deletes = [];
            $status = $attachSvc->doEdits($deletes, $aid_list, 0, $quarantine);
            if ($status !== UploadStatus::Success) {
                $errors .= $core->softerror($attachSvc->uploadErrorMsg($status));
                $replyvalid = false;
            }
            foreach ($deletes as $aid) {
                $messageinput = str_replace("[file]{$aid}[/file]", '', $messageinput);
            }
            if ($SETTINGS['attach_remote_images'] == 'on' && $bIMGcodeOnForThisPost) {
                $status = $attachSvc->remoteImages(0, $messageinput, $quarantine);
                if ($status !== UploadStatus::Success) {
                    $errors .= $core->softerror($attachSvc->uploadErrorMsg($status));
                    $replyvalid = false;
                }
            }
            $attachSkipped = false;
        } else {
            $attachSkipped = true;
        }

        //Check all replying permissions for this $tid.
        if (! X_SADMIN && $thread['closed'] != '') {
            if ($replyvalid) {
                $errors .= $core->softerror($lang['closedmsg']);
            } else {
                $core->error($lang['closedmsg']);
            }
            $replyvalid = false;
        }

        if ($replyvalid) {
            if (X_GUEST) { // Anonymous posting is allowed, and was checked in forum perms at top of file.
                $password = '';
                if ($SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on') {
                    $Captcha = new Captcha($core, $vars);
                    if ($Captcha->bCompatible !== false) {
                        $imgcode = getPhpInput('imgcode');
                        $imghash = getPhpInput('imghash');
                        if ($Captcha->ValidateCode($imgcode, $imghash) !== true) {
                            $errors .= $core->softerror($lang['captchaimageinvalid']);
                            $replyvalid = false;
                        }
                    }
                    unset($Captcha);
                }
            }
        }

        if ($replyvalid) {
            if (strlen($subjectinput) == 0 && strlen($messageinput) == 0) {
                $errors .= $core->softerror($lang['postnothing']);
                $replyvalid = false;
            }
        }

        if ($replyvalid) {
            if ($posticon != '') {
                $query = $db->query("SELECT id FROM " . $vars->tablepre . "smilies WHERE type='picon' AND url='$sql_posticon'");
                if ($db->num_rows($query) == 0) {
                    $sql_posticon = '';
                    $posticon = '';
                    $errors .= $core->softerror($lang['error']);
                    $replyvalid = false;
                }
                $db->free_result($query);
            }
        }

        if ($replyvalid) {
            if ($forum['lastpost'] != '') {
                $lastpost = explode('|', $forum['lastpost']);
                $rightnow = $vars->onlinetime - (int) $SETTINGS['floodctrl'];
                if ($rightnow <= (int) $lastpost[0] && $username === $lastpost[1]) {
                    $floodlink = "<a href=\"viewthread.php?fid=$fid&tid=$tid\">Click here</a>";
                    $errmsg = $lang['floodprotect'].' '.$floodlink.' '.$lang['tocont'];
                    $errors .= $core->softerror($errmsg);
                    $replyvalid = false;
                }
            }
        }

        if ($replyvalid) {
            $thatime = $vars->onlinetime;
            if ($bBBcodeOnForThisPost) {
                $core->postLinkBBcode($messageinput);
            }

            $dbmessage = addslashes($messageinput); //The message column is historically double-quoted.
            $dbsubject = addslashes($subjectinput);

            if (strlen($dbmessage) > 65535 || strlen($dbsubject) > 255) {
                // Inputs are suspiciously long.  Has the schema been customized?
                $query = $db->query("SELECT message, subject FROM " . $vars->tablepre . "posts WHERE 1=0");
                $msgmax = $db->field_len($query, 0);
                $submax = $db->field_len($query, 1);
                $db->free_result($query);
                if (strlen($dbmessage) > $msgmax) {
                    $dbmessage = substr($dbmessage, 0, $msgmax);
                }
                if (strlen($dbsubject) > $submax) {
                    $dbsubject = substr($dbsubject, 0, $submax);
                }
            }

            if (strlen($vars->onlineip) > 15 && ((int) $SETTINGS['schema_version'] < 9 || strlen($vars->onlineip) > 39)) {
                $useip = '';
            } else {
                $useip = $vars->onlineip;
            }

            $values = [
                'fid' => (int) $fid,
                'tid' => (int) $tid,
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

            $pid = $sql->addPost($values, $quarantine);

            $moderator = ($core->modcheck($username, $forum['moderator']) == 'Moderator');
            if ($moderator && $closetopic == 'yes') {
                $db->query("UPDATE " . $vars->tablepre . "threads SET closed = 'yes' WHERE tid = $tid AND fid = $fid");
            }

            if (! $quarantine) {
                // Update stats
                $fupArg = $forum['type'] == 'sub' ? (int) $forum['fup'] : null;
                $sql->setThreadLastpost($tid, "$thatime|$sql_username|$pid", newReply: true);
                $sql->setForumCounts($fid, "$thatime|$sql_username|$pid", fup: $fupArg, newReply: true);

                if (X_MEMBER) {
                    $sql->raisePostCount($username, $vars->onlinetime);
                    $expire = $vars->onlinetime + $vars::ONLINE_TIMER;
                    if (empty($oldtopics)) {
                        $oldtopics = "|$pid|";
                    } else {
                        $oldtopics .= "$pid|";
                    }
                    $core->put_cookie('oldtopics', $oldtopics, $expire);
                }

                // Send subscription notifications
                $query = $db->query("SELECT COUNT(*) FROM " . $vars->tablepre . "posts WHERE pid <= $pid AND tid='$tid'");
                $posts = (int) $db->result($query);
                $db->free_result($query);

                $lang2 = $tran->loadPhrases(['charset','textsubsubject','textsubbody']);
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

            if ('yes' == $emailnotify) {
                $sql->addFavoriteIfMissing((int) $tid, $username, 'subscription');
            }

            if ($forum['attachstatus'] == 'on') {
                if ($attachSkipped) {
                    for ($i = 1; $i <= $SETTINGS['filesperpost']; $i++) {
                        if (isset($_FILES["attach$i"])) {
                            $attachSvc->uploadedFile("attach$i", $pid, $quarantine);
                        }
                    }
                    if ($SETTINGS['attach_remote_images'] == 'on' && $bIMGcodeOnForThisPost) {
                        $attachSvc->remoteImages($pid, $messageinput, $quarantine);
                        $newdbmessage = addslashes($messageinput);
                        if ($newdbmessage !== $dbmessage) { // Anonymous message was modified after save, in order to use the pid.
                            $sql->savePostBody($pid, $newdbmessage, $quarantine);
                        }
                    }
                } elseif (X_MEMBER) {
                    $sql->claimOrphanedAttachments($pid, (int) $vars->self['uid'], $quarantine);
                }
            }

            if ($quarantine) {
                $core->message($lang['moderation_hold']);
            } else {
                $topicpages = $core->quickpage($posts, $vars->ppp);
                $topicpages = ($topicpages == 1) ? '' : '&page=' . $topicpages;
                $core->message($lang['replymsg'], redirect: $vars->full_url . "viewthread.php?tid={$tid}{$topicpages}#pid{$pid}");
            }
        }

        if (! $replyvalid) {
            if ($repquote > 0) {
                $query = $db->query("SELECT p.message, p.tid, p.fid, p.author FROM " . $vars->tablepre . "posts p WHERE p.pid = $repquote");
                $thaquote = $db->fetch_array($query);
                $db->free_result($query);
                $quoteperms = $core->checkForumPermissions($forums->getForum((int) $thaquote['fid']));
                if ($quoteperms[$vars::PERMS_VIEW] && $quoteperms[$vars::PERMS_PASSWORD]) {
                    $thaquote['message'] = preg_replace('@\\[file\\]\\d*\\[/file\\]@', '', $thaquote['message']); //These codes will not work inside quotes.
                    $quoteblock = $core->rawHTMLmessage(stripslashes($thaquote['message'])); //Messages are historically double-quoted.
                    if ($bBBcodeOnForThisPost) {
                        $messageinput = "[rquote=$repquote&amp;tid={$thaquote['tid']}&amp;author={$thaquote['author']}]{$quoteblock}[/rquote]";
                    } else {
                        $quotesep = '|| ';
                        $quoteblock = $quotesep.str_replace("\n", "\n$quotesep", $quoteblock);
                        $messageinput = "{$lang['textquote']} {$lang['origpostedby']} {$thaquote['author']}\r\n$quotesep\r\n$quoteblock\r\n\r\n";
                    }
                }
            }

            // Fill $attachfile
            $files = [];
            if ($forum['attachstatus'] == 'on' && X_MEMBER) {
                $template->attachfile = '';
                $files = $sql->getOrphanedAttachments($quarantine, (int) $vars->self['uid']);
                $counter = 0;
                $prevsize = '';
                foreach ($files as $postinfo) {
                    $postinfo['filename'] = attrOut($postinfo['filename']);
                    $postinfo['filesize'] = number_format((int) $postinfo['filesize'], 0, '.', ',');
                    $subTemplate->postinfo = $postinfo;
                    $template->attachfile .= $subTemplate->process('post_attachment_orphan.php');
                    if ($bBBcodeOnForThisPost) {
                        $bbcode = "[file]{$postinfo['aid']}[/file]";
                        if (strpos($messageinput, $bbcode) === false) {
                            if ($counter == 0 || $postinfo['img_size'] == '' || $prevsize == '' || $SETTINGS['attachimgpost'] == 'off') {
                                $messageinput .= "\n\n";
                            }
                            $messageinput .= ' '.$bbcode; // Use a leading space to prevent awkward line wraps.
                            $counter++;
                            $prevsize = $postinfo['img_size'];
                        }
                    }
                }
                $template->attachfile .= $core->makeAttachmentBox(count($files));
            }

            //Allow sanitized message to pass-through to template in case of: #1 preview, #2 post error
            $template->subject = $core->rawHTMLsubject($subjectinput);
            $template->message = $core->rawHTMLmessage($messageinput);

            if (onSubmit('previewpost')) {
                if ($SETTINGS['subject_in_title'] === 'on' && $template->subject !== '') {
                    $threadSubject = $template->subject . ' - ';
                }
                if ($posticon != '') {
                    $thread['icon'] = "<img src='" . $vars->full_url . $vars->theme['smdir'] . "/$posticon' />";
                } else {
                    $thread['icon'] = '';
                }
                $subTemplate->thread = $thread;
                $currtime = $core->timeKludge($vars->onlinetime);
                $date = gmdate($vars->dateformat, $currtime);
                $time = gmdate($vars->timecode, $currtime);
                $subTemplate->poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;
                if (strlen($template->subject) > 0) {
                    $subTemplate->dissubject = $template->subject.'<br />';
                } else {
                    $subTemplate->dissubject = '';
                }
                if ($bBBcodeOnForThisPost) {
                    $core->postLinkBBcode($messageinput);
                }
                if (count($files) > 0) {
                    $messageinput = $core->bbcodeFileTags($messageinput, $files, 0, $bBBcodeOnForThisPost, $quarantine);
                }
                $subTemplate->message1 = $core->postify($messageinput, $smileyoff, $bbcodeoff, $forum['allowsmilies'], 'no', $forum['allowbbcode'], $forum['allowimgcode']);

                if ($usesig == 'yes') {
                    $subTemplate->sig = $core->postify(
                        message: $vars->self['sig'],
                        allowsmilies: $forum['allowsmilies'],
                        allowbbcode: $SETTINGS['sigbbcode'],
                        allowimgcode: $forum['allowimgcode'],
                    );
                    $subTemplate->message1 .= $subTemplate->process('viewthread_post_sig.php');
                }
                $subTemplate->username = $username;
                $template->preview = $subTemplate->process('post_preview.php');
            }

            if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on') {
                $Captcha = new Captcha($core, $vars);
                if ($Captcha->bCompatible !== false) {
                    $subTemplate->imghash = $Captcha->GenerateCode();
                    $template->captchapostcheck = $subTemplate->process('post_captcha.php');
                }
                unset($Captcha);
            }

            $template->posts = '';

            if ($core->modcheck($username, $forum['moderator']) == 'Moderator') {
                $template->closeoption = '<label><input type="checkbox" name="closetopic" value="yes" '.$closecheck.' /> '.$lang['closemsgques'].'</label>';
            } else {
                $template->closeoption = '';
            }

            $replynum = $sql->countPosts(false, $tid);
            if ($replynum >= $vars->ppp) {
                $threadlink = $vars->full_url . "viewthread.php?fid=$fid&tid=$tid";
                $subTemplate->trevltmsg = str_replace('$threadlink', $threadlink, $lang['evaltrevlt']);
                $template->posts .= $subTemplate->process('post_reply_review_toolong.php');
            } else {
                $subTemplate->thisbg = $vars->theme['altbg1'];
                $posts = $sql->getPostsByTID($tid, $vars->ppp, ascending: false);
                foreach ($posts as $post) {
                    $currtime = $core->timeKludge((int) $post['dateline']);
                    $date = gmdate($vars->dateformat, $currtime);
                    $time = gmdate($vars->timecode, $currtime);
                    $subTemplate->poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;

                    if ($post['icon'] != '') {
                        $post['icon'] = '<img src="' . $vars->full_url . $vars->theme['smdir'] . '/' . $post['icon'] . '" alt="' . $lang['altpostmood'] . '" border="0" />';
                    } else {
                        $post['icon'] = '<img src="' . $vars->full_url . $vars->theme['imgdir'] . '/default_icon.gif" alt="[*]" border="0" />';
                    }

                    $post['message'] = preg_replace('@\\[file\\]\\d*\\[/file\\]@', '', $post['message']); //These codes do not work in postify()
                    $post['message'] = $core->postify(stripslashes($post['message']), $post['smileyoff'], $post['bbcodeoff'], $forum['allowsmilies'], 'no', $forum['allowbbcode'], $forum['allowimgcode']);
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

            // TODO: Why is this here?
            if ($core->getOneForumPerm($forum, $vars::PERMS_RAWREPLY) == $vars->status_enum['Guest']) { // Member posting is not allowed, do not request credentials!
                $template->loggedin = '';
            }

            $postpage = $template->process('post_reply.php');
        }
        break;

    case 'newthread':
        if ($poll == 'yes') {
            $core->nav($lang['textnewpoll']);
        } else {
            $core->nav($lang['textpostnew']);
        }

        $template->threadSubject = $lang['textpostnew'] . ' - ';

        $template->pollanswers = $validate->postedVar('pollanswers', dbescape: false);
        $topicvalid = onSubmit('topicsubmit'); // This new flag will indicate a message was submitted and successful.

        if ($forum['attachstatus'] == 'on' && X_MEMBER) {
            for ($i = 1; $i <= $SETTINGS['filesperpost']; $i++) {
                if (isset($_FILES["attach$i"])) {
                    $result = $attachSvc->uploadedFile("attach$i", 0, $quarantine);
                    if ($result->status !== UploadStatus::Success && $result->status !== UploadStatus::EmptyUpload) {
                        $errors .= $core->softerror($attachSvc->uploadErrorMsg($result->status));
                        $topicvalid = false;
                    }
                }
            }
            $aid_list = $sql->getOrphanedAttachmentIDs((int) $vars->self['uid'], $quarantine);
            $deletes = [];
            $status = $attachSvc->doEdits($deletes, $aid_list, 0, $quarantine);
            if ($status !== UploadStatus::Success) {
                $errors .= $core->softerror($attachSvc->uploadErrorMsg($status));
                $topicvalid = false;
            }
            foreach ($deletes as $aid) {
                $messageinput = str_replace("[file]{$aid}[/file]", '', $messageinput);
            }
            if ($SETTINGS['attach_remote_images'] == 'on' && $bIMGcodeOnForThisPost) {
                $status = $attachSvc->remoteImages(0, $messageinput, $quarantine);
                if ($status !== UploadStatus::Success) {
                    $errors .= $core->softerror($attachSvc->uploadErrorMsg($status));
                    $topicvalid = false;
                }
            }
            $attachSkipped = false;
        } else {
            $attachSkipped = true;
        }

        if ($topicvalid) {
            if (X_GUEST) { // Anonymous posting is allowed, and was checked in forum perms at top of file.
                $password = '';
                if ($SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on') {
                    $Captcha = new Captcha($core, $vars);
                    if ($Captcha->bCompatible !== false) {
                        $imgcode = getPhpInput('imgcode');
                        $imghash = getPhpInput('imghash');
                        if ($Captcha->ValidateCode($imgcode, $imghash) !== true) {
                            $errors .= $core->softerror($lang['captchaimageinvalid']);
                            $topicvalid = false;
                        }
                    }
                    unset($Captcha);
                }
            }
        }

        if ($topicvalid) {
            if (strlen($subjectinput) == 0) {
                $errors .= $core->softerror($lang['textnosubject']);
                $topicvalid = false;
            }
        }

        if ($topicvalid) {
            if ($posticon != '') {
                $query = $db->query("SELECT id FROM " . $vars->tablepre . "smilies WHERE type='picon' AND url='$sql_posticon'");
                if ($db->num_rows($query) == 0) {
                    $sql_posticon = '';
                    $posticon = '';
                    $errors .= $core->softerror($lang['error']);
                    $topicvalid = false;
                }
                $db->free_result($query);
            }
        }

        if ($topicvalid) {
            if ($forum['lastpost'] != '') {
                $lastpost = explode('|', $forum['lastpost']);
                $rightnow = $vars->onlinetime - (int) $SETTINGS['floodctrl'];
                if ($rightnow <= (int) $lastpost[0] && $username === $lastpost[1]) {
                    $errors .= $core->softerror($lang['floodprotect']);
                    $topicvalid = false;
                }
            }
        }

        if ($topicvalid) {
            if ($poll == 'yes') {
                $pollopts = [];
                $pollopts2 = explode("\n", $template->pollanswers);
                foreach ($pollopts2 as $value) {
                    $value = trim($value);
                    if ($value != '') {
                        $pollopts[] = $value;
                    }
                }
                $pnumnum = count($pollopts);

                if ($pnumnum < 2) {
                    $errors .= $core->softerror($lang['too_few_pollopts']);
                    $topicvalid = false;
                }
            }
        }

        if ($topicvalid) {
            $thatime = $vars->onlinetime;

            if ($bBBcodeOnForThisPost) {
                $core->postLinkBBcode($messageinput);
            }
            $dbmessage = addslashes($messageinput); //The message column is historically double-quoted.
            $dbsubject = addslashes($subjectinput);
            $dbtsubject = $dbsubject;

            if (strlen($dbmessage) > 65535 || strlen($dbsubject) > 128) {
                // Inputs are suspiciously long.  Has the schema been customized?
                $query = $db->query("SELECT message, subject FROM " . $vars->tablepre . "posts WHERE 1=0");
                $msgmax = $db->field_len($query, 0);
                $submax = $db->field_len($query, 1);
                $db->free_result($query);
                if (strlen($dbmessage) > $msgmax) {
                    $dbmessage = substr($dbmessage, 0, $msgmax);
                }
                if (strlen($dbsubject) > $submax) {
                    $dbsubject = substr($dbsubject, 0, $submax);
                }

                $query = $db->query("SELECT subject FROM " . $vars->tablepre . "threads WHERE 1=0");
                $tsubmax = $db->field_len($query, 0);
                $db->free_result($query);
                if (strlen($dbtsubject) > $tsubmax) {
                    $dbtsubject = substr($dbtsubject, 0, $tsubmax);
                }
            }
            
            $lastpost = "$thatime|$username";
            $closed = '';
            $topped = 0;
            $dbpollopts = ('yes' == $poll) ? 1 : 0;

            if (X_MEMBER) {
                $moderator = ($core->modcheck($username, $forum['moderator']) == 'Moderator');
                if ($moderator) {
                    if ('yes' == $closetopic) {
                        // Be careful here; threads.closed is historically yes/moved/empty rather than yes/no.
                        $closed = 'yes';
                    }
                    if ($toptopic == 'yes') {
                        $topped = 1;
                    }
                }
            }

            $values = [
                'fid' => (int) $fid,
                'subject' => $dbtsubject,
                'icon' => $posticon,
                'lastpost' => $lastpost,
                'author' => $username,
                'closed' => $closed,
                'topped' => $topped,
                'pollopts' => $dbpollopts,
            ];

            $tid = $sql->addThread($values, $quarantine);

            if (strlen($vars->onlineip) > 15 && ((int) $SETTINGS['schema_version'] < 9 || strlen($vars->onlineip) > 39)) {
                $useip = '';
            } else {
                $useip = $vars->onlineip;
            }

            $values = [
                'fid' => (int) $fid,
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

            $pid = $sql->addPost($values, $quarantine, $quarantine); // 3rd arg signals that this is not a reply.

            $lastpost .= "|$pid";
            $sql->setThreadLastpost($tid, $lastpost, $quarantine);

            if (! $quarantine) {
                $where = "WHERE fid=$fid";
                if ($forum['type'] == 'sub') {
                    $where .= " OR fid={$forum['fup']}";
                }
                $db->query("UPDATE " . $vars->tablepre . "forums SET lastpost='$thatime|$sql_username|$pid', threads=threads+1, posts=posts+1 $where");
                unset($where);
            }

            if ($poll == 'yes') {
                // Create a poll ID.  Works like a junction table even though we only support one poll per thread.
                $dbsubject = addslashes($subjectinput);
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

            if (X_MEMBER) {
                if ($emailnotify == 'yes') {
                    $sql->addFavoriteIfMissing((int) $tid, $username, 'subscription', $quarantine);
                }

                if (! $quarantine) {
                    $sql->raisePostCount($username, $vars->onlinetime);
                    $expire = $vars->onlinetime + $vars::ONLINE_TIMER;
                    if (empty($oldtopics)) {
                        $oldtopics = "|$pid|";
                    } else {
                        $oldtopics .= "$pid|";
                    }
                    $core->put_cookie('oldtopics', $oldtopics, $expire);
                }
            }

            if ($forum['attachstatus'] == 'on') {
                if ($attachSkipped) {
                    for ($i = 1; $i <= $SETTINGS['filesperpost']; $i++) {
                        if (isset($_FILES["attach$i"])) {
                            $attachSvc->uploadedFile("attach$i", $pid, $quarantine);
                        }
                    }
                    if ($SETTINGS['attach_remote_images'] == 'on' && $bIMGcodeOnForThisPost) {
                        $attachSvc->remoteImages($pid, $messageinput, $quarantine);
                        $newdbmessage = addslashes($messageinput);
                        if ($newdbmessage !== $dbmessage) { // Anonymous message was modified after save, in order to use the pid.
                            $sql->savePostBody($pid, $newdbmessage, $quarantine);
                        }
                    }
                } elseif (X_MEMBER) {
                    $sql->claimOrphanedAttachments($pid, (int) $vars->self['uid'], $quarantine);
                }
            }

            if ($quarantine) {
                $core->message($lang['moderation_hold']);
            } else {
                $posts = $sql->countPosts(tid: $tid);

                $topicpages = $core->quickpage($posts, $vars->ppp);
                $topicpages = ($topicpages == 1) ? '' : '&page='.$topicpages;
                $core->message($lang['postmsg'], redirect: $vars->full_url . "viewthread.php?tid={$tid}{$topicpages}#pid{$pid}");
            }
        }

        if (! $topicvalid) {
            // Fill $attachfile
            $files = [];
            if ($forum['attachstatus'] == 'on' && X_MEMBER) {
                $template->attachfile = '';
                $files = $sql->getOrphanedAttachments($quarantine, (int) $vars->self['uid']);
                $counter = 0;
                $prevsize = '';
                foreach ($files as $postinfo) {
                    $postinfo['filename'] = attrOut($postinfo['filename']);
                    $postinfo['filesize'] = number_format((int) $postinfo['filesize'], 0, '.', ',');
                    $subTemplate->postinfo = $postinfo;
                    $template->attachfile .= $subTemplate->process('post_attachment_orphan.php');
                    if ($bBBcodeOnForThisPost) {
                        $bbcode = "[file]{$postinfo['aid']}[/file]";
                        if (strpos($messageinput, $bbcode) === false) {
                            if ($counter == 0 || $postinfo['img_size'] == '' || $prevsize == '' || $SETTINGS['attachimgpost'] == 'off') {
                                $messageinput .= "\n\n";
                            }
                            $messageinput .= ' '.$bbcode; // Use a leading space to prevent awkward line wraps.
                            $counter++;
                            $prevsize = $postinfo['img_size'];
                        }
                    }
                }
                $template->attachfile .= $core->makeAttachmentBox(count($files));
            }

            //Allow sanitized message to pass-through to template in case of: #1 preview, #2 post error
            $template->subject = $core->rawHTMLsubject($subjectinput);
            $template->message = $core->rawHTMLmessage($messageinput);

            if (onSubmit('previewpost')) {
                if ($SETTINGS['subject_in_title'] === 'on' && $template->subject !== '') {
                    $threadSubject = $template->subject . ' - ';
                }
                if ($posticon != '') {
                    $thread['icon'] = "<img src='" . $vars->full_url . $vars->theme['smdir'] . "/$posticon' />";
                } else {
                    $thread['icon'] = '';
                }
                $subTemplate->thread = $thread;
                $currtime = $core->timeKludge($vars->onlinetime);
                $date = gmdate($vars->dateformat, $currtime);
                $time = gmdate($vars->timecode, $currtime);
                $subTemplate->poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;
                if (strlen($template->subject) > 0) {
                    $subTemplate->dissubject = $template->subject.'<br />';
                } else {
                    $subTemplate->dissubject = '';
                }
                if ($bBBcodeOnForThisPost) {
                    $core->postLinkBBcode($messageinput);
                }
                if (count($files) > 0) {
                    $messageinput = $core->bbcodeFileTags($messageinput, $files, 0, $bBBcodeOnForThisPost, $quarantine);
                }
                $subTemplate->message1 = $core->postify($messageinput, $smileyoff, $bbcodeoff, $forum['allowsmilies'], 'no', $forum['allowbbcode'], $forum['allowimgcode']);

                if ($usesig == 'yes') {
                    $subTemplate->sig = $core->postify(
                        message: $vars->self['sig'],
                        allowsmilies: $forum['allowsmilies'],
                        allowbbcode: $SETTINGS['sigbbcode'],
                        allowimgcode: $forum['allowimgcode'],
                    );
                    $subTemplate->message1 .= $subTemplate->process('viewthread_post_sig.php');
                }

                $template->preview = $subTemplate->process('post_preview.php');
            }

            if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on') {
                $Captcha = new Captcha($core, $vars);
                if ($Captcha->bCompatible !== false) {
                    $subTemplate->imghash = $Captcha->GenerateCode();
                    $template->captchapostcheck = $subTemplate->process('post_captcha.php');
                }
                unset($Captcha);
            }

            if ($core->modcheck($username, $forum['moderator']) == 'Moderator') {
                $template->topoption = '<label><input type="checkbox" name="toptopic" value="yes" '.$topcheck.' /> '.$lang['topmsgques'] . '</label>';
                $template->closeoption = '<label><input type="checkbox" name="closetopic" value="yes" '.$closecheck.' /> '.$lang['closemsgques'].'</label>';
            } else {
                $template->topoption = '';
                $template->closeoption = '';
            }

            // TODO: Why is this here?
            if ($core->getOneForumPerm($forum, $vars::PERMS_RAWTHREAD) == $vars->status_enum['Guest']) { // Member posting is not allowed, do not request credentials!
                $template->loggedin = '';
            }

            if (isset($poll) && $poll == 'yes') {
                $postpage = $template->process('post_newpoll.php');
            } else {
                $postpage = $template->process('post_newthread.php');
            }
        }
        break;

    case 'edit':
        $core->nav('<a href="' . $vars->full_url . 'viewthread.php?tid='.$tid.'">'.$threadname.'</a>');
        $core->nav($lang['texteditpost']);

        if ($SETTINGS['subject_in_title'] === 'on') {
            $threadSubject = $threadname . ' - ';
        }

        $editvalid = true; // This new flag will indicate a message was submitted and successful.

        // Check all editing permissions for this $pid.  Based on viewthread design, forum Moderators can always edit, $orig['author'] can edit open threads only.
        $query = $db->query("SELECT p.*, m.status FROM " . $vars->tablepre . "posts p LEFT JOIN " . $vars->tablepre . "members m ON p.author=m.username WHERE p.pid=$pid");
        $orig = $db->fetch_array($query);
        $db->free_result($query);

        $status1 = $core->modcheckPost($vars->self['username'], $forum['moderator'], $orig['status']);

        if ($status1 != 'Moderator' && ($vars->self['username'] !== $orig['author'] || $thread['closed'] != '')) {
            $core->error($lang['noedit']);
        }

        if ($editvalid) {
            if ($forum['attachstatus'] == 'on') {
                for ($i = 1; $i <= $SETTINGS['filesperpost']; $i++) {
                    if (isset($_FILES["attach$i"])) {
                        $result = $attachSvc->uploadedFile("attach$i", $pid);
                        if ($result->status !== UploadStatus::Success && $result->status !== UploadStatus::EmptyUpload) {
                            $errors .= $core->softerror($attachSvc->uploadErrorMsg($result->status));
                            $editvalid = false;
                        }
                    }
                }
                $children = false;
                $aid_list = $sql->getAttachmentIDsByPost($pid, $children);
                $deletes = [];
                $status = $attachSvc->doEdits($deletes, $aid_list, $pid);
                if ($status !== UploadStatus::Success) {
                    $errors .= $core->softerror($attachSvc->uploadErrorMsg($status));
                    $editvalid = false;
                }
                foreach ($deletes as $aid) {
                    $messageinput = str_replace("[file]{$aid}[/file]", '', $messageinput);
                }
                $temp = '';
                if ($SETTINGS['attach_remote_images'] == 'on' && $bIMGcodeOnForThisPost) {
                    $status = $attachSvc->remoteImages($pid, $messageinput);
                    if ($status !== UploadStatus::Success) {
                        $errors .= $core->softerror($attachSvc->uploadErrorMsg($status));
                        $editvalid = false;
                    }
                }
            }
        }

        $editvalid &= onSubmit('editsubmit');

        if ($editvalid) {
            if ($posticon != '') {
                $query = $db->query("SELECT id FROM " . $vars->tablepre . "smilies WHERE type='picon' AND url='$sql_posticon'");
                if ($db->num_rows($query) == 0) {
                    $sql_posticon = '';
                    $posticon = '';
                    $errors .= $core->softerror($lang['error']);
                    $editvalid = false;
                }
                $db->free_result($query);
            }
        }

        if ($editvalid) {
            $query = $db->query("SELECT pid FROM " . $vars->tablepre . "posts WHERE tid = $tid ORDER BY dateline LIMIT 1");
            $isfirstpost = $db->fetch_array($query);
            $db->free_result($query);

            if ((strlen($subjectinput) == 0 && $pid == (int) $isfirstpost['pid']) && ! (isset($delete) && $delete == 'yes')) {
                $errors .= $core->softerror($lang['textnosubject']);
                $editvalid = false;
            }
        }

        if ($editvalid) {
            $threaddelete = 'no';

            if (!(isset($delete) && $delete == 'yes')) {
                if ($SETTINGS['editedby'] == 'on') {
                    $messageinput .= "\n\n[".$lang['textediton'].' '.gmdate($vars->dateformat).' '.$lang['textby']." $username]";
                }

                if ($bBBcodeOnForThisPost) {
                    $core->postLinkBBcode($messageinput);
                }
                $dbmessage = addslashes($messageinput); //The message column is historically double-quoted.
                $dbsubject = addslashes($subjectinput);

                if (strlen($dbmessage) > 65535 || strlen($dbsubject) > 255) {
                    // Inputs are suspiciously long.  Has the schema been customized?
                    $query = $db->query("SELECT message, subject FROM " . $vars->tablepre . "posts WHERE 1=0");
                    $msgmax = $db->field_len($query, 0);
                    $submax = $db->field_len($query, 1);
                    $db->free_result($query);
                    if (strlen($dbmessage) > $msgmax) {
                        $dbmessage = substr($dbmessage, 0, $msgmax);
                    }
                    if (strlen($dbsubject) > $submax) {
                        $dbsubject = substr($dbsubject, 0, $submax);
                    }
                }

                $db->escape_fast($dbmessage);
                $db->escape_fast($dbsubject);

                if ((int) $isfirstpost['pid'] == $pid) {
                    $db->query("UPDATE " . $vars->tablepre . "threads SET icon='$sql_posticon', subject='$dbsubject' WHERE tid=$tid");
                }

                $db->query("UPDATE " . $vars->tablepre . "posts SET message='$dbmessage', usesig='$usesig', bbcodeoff='$bbcodeoff', smileyoff='$smileyoff', icon='$sql_posticon', subject='$dbsubject' WHERE pid=$pid");
            } else {
                $db->query("DELETE FROM " . $vars->tablepre . "posts WHERE pid=$pid");
                if ($orig['author'] != 'Anonymous') {
                    $db->query("UPDATE " . $vars->tablepre . "members SET postnum=postnum-1 WHERE username='".$db->escape($orig['author'])."'");
                }
                $attachSvc->deleteByPost($pid);

                if ((int) $isfirstpost['pid'] == $pid) {
                    $numrows = $sql->countPosts(tid: $tid);

                    if ($numrows == 0) {
                        $threaddelete = 'yes';
                        $db->query("DELETE FROM " . $vars->tablepre . "favorites WHERE tid='$tid'");

                        $sql->deleteVotesByTID([$tid]);

                        $db->query("DELETE FROM " . $vars->tablepre . "threads WHERE tid=$tid OR closed='moved|$tid'");
                    } else {
                        $db->query("UPDATE " . $vars->tablepre . "posts SET subject='".$db->escape($orig['subject'])."' WHERE tid=$tid ORDER BY dateline LIMIT 1");
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

            if ($threaddelete == 'no') {
                $posts = $sql->countPosts(tid: $tid, before: (int) $orig['dateline']);
                $topicpages = $core->quickpage($posts, $vars->ppp);
                $topicpages = ($topicpages == 1) ? '' : '&page='.$topicpages;
                $core->message($lang['editpostmsg'], redirect: $vars->full_url . "viewthread.php?tid={$tid}{$topicpages}#pid{$pid}");
            } else {
                $core->message($lang['editpostmsg'], redirect: $vars->full_url . 'forumdisplay.php?fid='.$fid);
            }
        }

        if (! $editvalid) {
            // Fill $postinfo
            if (onSubmit('editsubmit') || onSubmit('previewpost')) {
                // For post_edit template.
                $postinfo = [
                    'usesig' => $usesig,
                    'bbcodeoff' => $bbcodeoff,
                    'smileyoff' => $smileyoff,
                    'message' => $messageinput,
                    'subject' => $subjectinput,
                    'icon' => $sql_posticon,
                    'dateline' => $orig['dateline'],
                ];
            } else {
                $postinfo = $orig;
                $postinfo['message'] = stripslashes($postinfo['message']); //Messages are historically double-quoted.
                $postinfo['subject'] = stripslashes($postinfo['subject']);
                $bBBcodeOnForThisPost = ($forum['allowbbcode'] == 'yes' && $postinfo['bbcodeoff'] == 'no');
                $bIMGcodeOnForThisPost = ($bBBcodeOnForThisPost && $forum['allowimgcode'] == 'yes');
                $bSmiliesOnForThisPost = ($forum['allowsmilies'] == 'yes' && $postinfo['smileyoff'] == 'no');
            }

            if ($SETTINGS['subject_in_title'] === 'on' && $postinfo['subject'] !== '') {
                $template->threadSubject = $postinfo['subject'] . ' - ';
            }

            // Fill $attachment
            $template->attachment = '';
            $files = [];
            if ($forum['attachstatus'] == 'on') {
                $files = $sql->getAttachmentsByPIDs([$pid]);
                $counter = 0;
                $prevsize = '';
                foreach ($files as $attach) {
                    $subTemplate->aInfo = [
                        'aid' => $attach['aid'],
                        'downloads' => $attach['downloads'],
                        'filename' => attrOut($attach['filename']),
                        'filesize' => number_format((int) $attach['filesize'], 0, '.', ','),
                        'url' => $attachSvc->getURL((int) $attach['aid'], $pid, $attach['filename']),
                    ];
                    $template->attachment .= $subTemplate->process('post_edit_attachment.php');
                    if ($bBBcodeOnForThisPost) {
                        $bbcode = "[file]{$attach['aid']}[/file]";
                        if (strpos($postinfo['message'], $bbcode) === false) {
                            if ($counter == 0 || $attach['img_size'] == '' || $prevsize == '' || $SETTINGS['attachimgpost'] == 'off') {
                                $postinfo['message'] .= "\n\n";
                            }
                            $postinfo['message'] .= ' ' . $bbcode; // Use a leading space to prevent awkward line wraps.
                            $counter++;
                            $prevsize = $attach['img_size'];
                        }
                    }
                }
                $template->attachment .= $core->makeAttachmentBox(count($files));
            }

            //Allow sanitized message to pass-through to template in case of: #1 preview, #2 post error
            $subject = $core->rawHTMLsubject($postinfo['subject']);  // This variable used only to set $dissubject.
            // $message = $core->rawHTMLmessage($postinfo['message']);  // This variable unused here in favor of $postinfo['message'].

            if (onSubmit('previewpost')) {
                null_string($postinfo['icon']);
                if ($postinfo['icon'] !== '') {
                    $thread['icon'] = "<img src='" . $vars->full_url . $vars->theme['smdir'] . "/{$postinfo['icon']}' />";
                }
                $subTemplate->thread = $thread;
                $currtime = $core->timeKludge((int) $postinfo['dateline']);
                $date = gmdate($vars->dateformat, $currtime);
                $time = gmdate($vars->timecode, $currtime);
                $subTemplate->poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;
                if (strlen($subject) > 0) {
                    $subTemplate->dissubject = $subject.'<br />';
                } else {
                    $subTemplate->dissubject = '';
                }
                $message1 = $postinfo['message'];
                if ($SETTINGS['editedby'] == 'on') {
                    $message1 .= "\n\n[".$lang['textediton'].' '.gmdate($vars->dateformat).' '.$lang['textby']." $username]";
                }
                if ($bBBcodeOnForThisPost) {
                    $core->postLinkBBcode($message1);
                }
                if (count($files) > 0) {
                    $message1 = $core->bbcodeFileTags($message1, $files, $pid, $bBBcodeOnForThisPost);
                }
                $message1 = $core->postify($message1, $smileyoff, $bbcodeoff, $forum['allowsmilies'], 'no', $forum['allowbbcode'], $forum['allowimgcode']);

                if ($usesig == 'yes') {
                    $subTemplate->sig = $core->postify(
                        message: $vars->self['sig'],
                        allowsmilies: $forum['allowsmilies'],
                        allowbbcode: $SETTINGS['sigbbcode'],
                        allowimgcode: $forum['allowimgcode'],
                    );
                    $message1 .= $subTemplate->process('viewthread_post_sig.php');
                }
                $subTemplate->message1 = $message1;
                $template->preview = $subTemplate->process('post_preview.php');
            }

            if ($postinfo['bbcodeoff'] == 'yes') {
                $template->offcheck1 = $vars::cheHTML;
            } else {
                $template->offcheck1 = '';
            }

            if ($postinfo['smileyoff'] == 'yes') {
                $template->offcheck2 = $vars::cheHTML;
            } else {
                $template->offcheck2 = '';
            }

            if ($postinfo['usesig'] == 'yes') {
                $template->offcheck3 = $vars::cheHTML;
            } else {
                $template->offcheck3 = '';
            }

            $icons = str_replace('<input type="radio" name="posticon" value="'.$postinfo['icon'].'" />', '<input type="radio" name="posticon" value="'.$postinfo['icon'].'" checked="checked" />', $icons);

            $postinfo['message'] = $core->rawHTMLmessage($postinfo['message']);
            $postinfo['subject'] = $core->rawHTMLsubject($postinfo['subject']);
            $template->postinfo = $postinfo;
            $postpage = $template->process('post_edit.php');
        }
        break;

    default:
        $core->error($lang['textnoaction']);
        break;
}

$header = $template->process('header.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $errors, $postpage, $footer;
