<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Beta 2 - This software should not be used for any purpose after 1 February 2009.
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

define('X_SCRIPT', 'post.php');

require 'header.php';

header('X-Robots-Tag: noindex');

loadtemplates(
'post_captcha',
'post_notloggedin',
'post_loggedin',
'post_preview',
'post_attachment_orphan',
'post_attachmentbox',
'post_newthread',
'post_reply_review_toolong',
'post_reply_review_post',
'post_reply',
'post_edit',
'functions_smilieinsert',
'functions_smilieinsert_smilie',
'functions_bbcodeinsert',
'forumdisplay_password',
'functions_bbcode',
'post_newpoll',
'post_edit_attachment'
);

eval('$css = "'.template('css').'";');

if (X_GUEST) {
    eval('$loggedin = "'.template('post_notloggedin').'";');
} else {
    eval('$loggedin = "'.template('post_loggedin').'";');
}

if ($self['ban'] == "posts" || $self['ban'] == "both") {
    error($lang['textbanfrompost']);
}

//Validate $pid, $tid, $fid, and $repquote
$fid = -1;
$tid = -1;
$pid = -1;
$repquote = -1;
if ($action == 'edit') {
    $pid = getRequestInt('pid');
    $query = $db->query("SELECT f.*, t.tid FROM ".X_PREFIX."posts AS p LEFT JOIN ".X_PREFIX."threads AS t USING (tid) LEFT JOIN ".X_PREFIX."forums AS f ON f.fid=t.fid WHERE p.pid=$pid");
    if ($db->num_rows($query) != 1) {
        error($lang['textnothread']);
    }
    $forum = $db->fetch_array($query);
    $db->free_result($query);
    $fid = $forum['fid'];
    $tid = $forum['tid'];
} else if ($action == 'reply') {
    $tid = getRequestInt('tid');
    $repquote = getInt('repquote');
    $query = $db->query("SELECT f.* FROM ".X_PREFIX."threads AS t LEFT JOIN ".X_PREFIX."forums AS f USING (fid) WHERE t.tid=$tid");
    if ($db->num_rows($query) != 1) {
        error($lang['textnothread']);
    }
    $forum = $db->fetch_array($query);
    $db->free_result($query);
    $fid = $forum['fid'];
} else if ($action == 'newthread') {
    $fid = getRequestInt('fid');
    $forum = getForum($fid);
    if ($forum === FALSE) {
        error($lang['textnoforum']);
    }
} else {
    error($lang['textnoaction']);
}

if (($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on') {
    error($lang['textnoforum']);
}

smcwcache();

if ($tid > 0) {
    $query = $db->query("SELECT * FROM ".X_PREFIX."threads WHERE tid=$tid");
    if ($db->num_rows($query) != 1) {
        error($lang['textnothread']);
    }
    $thread = $db->fetch_array($query);
    $db->free_result($query);
    $threadname = rawHTMLsubject(stripslashes($thread['subject']));
} else {
    $thread = array();
    $threadname = '';
}

//Warning! These variables are used for template output.
$attachfile = '';
$attachment = '';
$captchapostcheck = '';
$dissubject = '';
$errors = '';
$imghash = '';
$message = '';
$message1 = '';
$postinfo = array();
$preview = '';
$spelling_lang = '';
$spelling_submit1 = '';
$spelling_submit2 = '';
$subject = '';
$suggestions = '';
if (X_GUEST) {
    $username = 'Anonymous';
} else {
    $username = $xmbuser;
}

validatePpp();

$poll = postedVar('poll', '', FALSE, FALSE, FALSE, 'g');
if ($poll != 'yes') {
    $poll = '';
}

// check permissions on this forum (and top forum if it's a sub?)
$perms = checkForumPermissions($forum);
if (!$perms[X_PERMS_VIEW]) {
    error($lang['privforummsg']);
} else if (!$perms[X_PERMS_PASSWORD]) {
    handlePasswordDialog($fid);
}

// check posting permissions specifically
if ($action == 'newthread') {
    if (($poll == '' && !$perms[X_PERMS_THREAD]) || ($poll == 'yes' && !$perms[X_PERMS_POLL])) {
        error($lang['textnoaction']);
    }
} else if ($action == 'reply') {
    if (!$perms[X_PERMS_REPLY]) {
        error($lang['textnoaction']);
    }
} else if ($action == 'edit') {
    // let's allow edits for now, we'll check for permissions later on in the script (due to need for $orig['author'])
} else {
    error($lang['textnoaction']);
}

$fup = array();
if ($forum['type'] == 'sub') {
    $fup = getForum($forum['fup']);
    // prevent access to subforum when upper forum can't be viewed.
    $fupPerms = checkForumPermissions($fup);
    if (!($fupPerms[X_PERMS_VIEW] && $fupPerms[X_PERMS_PASSWORD])) {
        error($lang['privforummsg']);     // do not show password-dialog here; it makes the situation too complicated
    } else if ($fup['fup'] > 0) {
        $fupup = getForum($fup['fup']);
        nav('<a href="index.php?gid='.$fup['fup'].'">'.fnameOut($fupup['name']).'</a>');
        unset($fupup);
    }
    nav('<a href="forumdisplay.php?fid='.$fup['fid'].'">'.fnameOut($fup['name']).'</a>');
} else if ($forum['fup'] > 0) { // 'forum' in a 'group'
    $fup = getForum($forum['fup']);
    nav('<a href="index.php?gid='.$fup['fid'].'">'.fnameOut($fup['name']).'</a>');
}
nav('<a href="forumdisplay.php?fid='.$fid.'">'.fnameOut($forum['name']).'</a>');

// Search-link
$searchlink = makeSearchLink($forum['fid']);

if ($forum['attachstatus'] == 'on') {
    require 'include/attach.inc.php';
    $attachlimits = $lang['attachmaxsize'].' '.getSizeFormatted($SETTINGS['maxattachsize']).'.  '.$lang['attachmaxdims'].' '.$SETTINGS['max_image_size'].'.';
}

$posticon = postedVar('posticon', 'javascript', TRUE, TRUE, TRUE);
if (!isValidFilename($posticon)) {
    $posticon = '';
} elseif (!file_exists($smdir.'/'.$posticon)) {
    $posticon = '';
}

$listed_icons = 0;
$icons = '<input type="radio" name="posticon" value="" /> <img src="'.$imgdir.'/default_icon.gif" alt="[*]" border="0" />';
$querysmilie = $db->query("SELECT url, code FROM ".X_PREFIX."smilies WHERE type='picon'");
while($smilie = $db->fetch_array($querysmilie)) {
    $icons .= ' <input type="radio" name="posticon" value="'.$smilie['url'].'" /><img src="'.$smdir.'/'.$smilie['url'].'" alt="'.$smilie['code'].'" border="0" />';
    $listed_icons++;
    if ($listed_icons == 9) {
        $icons .= '<br />';
        $listed_icons = 0;
    }
}
$db->free_result($querysmilie);

if ($action != 'edit') {
    $icons = str_replace('<input type="radio" name="posticon" value="'.$posticon.'" />', '<input type="radio" name="posticon" value="'.$posticon.'" checked="checked" />', $icons);

    if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on' && !DEBUG) {
        require ROOT.'include/captcha.inc.php';
    }
}

eval('$bbcodescript = "'.template('functions_bbcode').'";');

if (!isset($usesig)) {
    $usesig = 'no';
}

if ($usesig != 'yes') {
    $usesig = 'no';
}

$allowimgcode = (isset($forum['allowimgcode']) && $forum['allowimgcode'] == 'yes') ? $lang['texton'] : $lang['textoff'];
$allowhtml = (isset($forum['allowhtml']) && $forum['allowhtml'] == 'yes') ? $lang['texton'] : $lang['textoff'];
$allowsmilies = (isset($forum['allowsmilies']) && $forum['allowsmilies'] == 'yes') ? $lang['texton'] : $lang['textoff'];
$allowbbcode = (isset($forum['allowbbcode']) && $forum['allowbbcode'] == 'yes') ? $lang['texton'] : $lang['textoff'];

if (isset($smileyoff) && $smileyoff == 'yes') {
    $smileoffcheck = $cheHTML;
} else {
    $smileoffcheck = '';
    $smileyoff = 'no';
}

if (isset($bbcodeoff) && $bbcodeoff == 'yes') {
    $codeoffcheck = $cheHTML;
} else {
    $codeoffcheck = '';
    $bbcodeoff = 'no';
}

if (isset($emailnotify) && $emailnotify == 'yes') {
    $emailnotifycheck = $cheHTML;
} else {
    $emailnotifycheck = '';
    $emailnotify = 'no';
}

if (isset($subaction) && $subaction == 'spellcheck' && (isset($spellchecksubmit) || isset($updates_submit))) {
    $sc = true;
} else {
    $sc = false;
}

if ((isset($previewpost) || $sc) && $usesig == 'yes') {
    $usesigcheck = $cheHTML;
} else if (isset($previewpost) || $sc) {
    $usesigcheck = '';
} else if ($self['sig'] != '') {
    $usesigcheck = $cheHTML;
} else {
    $usesigcheck = '';
}

if (X_STAFF) {
    if (isset($toptopic) && $toptopic == 'yes') {
        $topcheck = $cheHTML;
    } else {
        $topcheck = '';
        $toptopic = 'no';
    }

    if (isset($closetopic) && $closetopic == 'yes') {
        $closecheck = $cheHTML;
    } else {
        $closecheck = '';
        $closetopic = 'no';
    }
} else {
    $topcheck = '';
    $closecheck = '';
}

$messageinput = postedVar('message', '', TRUE, FALSE);  //postify() is responsible for DECODING if html is allowed.

if ($SETTINGS['spellcheck'] == 'on') {
    $spelling_submit1 = '<input type="hidden" name="subaction" value="spellcheck" /><input type="submit" class="submit" name="spellchecksubmit" value="'.$lang['checkspelling'].'" />';
    $spelling_lang = '<select name="language"><option value="en" selected="selected">English</option></select>';
    if ($sc) {
        if (isset($language) && !isset($updates_submit)) {
            require ROOT.'include/spelling.inc.php';
            $spelling = new spelling($language);
            $problems = $spelling->check_text(postedVar('message', '', FALSE, FALSE));  //Use raw value so we're not checking entity names.
            if (count($problems) > 0) {
                $suggest = array();
                foreach($problems as $raworig=>$new) {
                    $orig = cdataOut($raworig);
                    $mistake = array();
                    foreach($new as $rawsuggestion) {
                        $suggestion = attrOut($rawsuggestion);
                        eval('$mistake[] = "'.template('spelling_suggestion_new').'";');
                    }
                    $mistake = implode("\n", $mistake);
                    eval('$suggest[] = "'.template('spelling_suggestion_row').'";');
                }
                $suggestions = implode("\n", $suggest);
                eval('$suggestions = "'.template('spelling_suggestion').'";');
                $spelling_submit2 = '<input type="submit" class="submit" name="updates_submit" value="'.$lang['replace'].'" />';
            } else {
                eval('$suggestions = "'.template('spelling_suggestion_no').'";');
            }
        } else {
            $old_words = postedArray('old_words', 'string', '', TRUE, FALSE);
            foreach($old_words as $word) {
                $replacement = postedVar('replace_'.$word, '', TRUE, FALSE);
                $messageinput = str_replace($word, $replacement, $messageinput);
            }
        }
    }
}

$bbcodeinsert = bbcodeinsert();
$smilieinsert = smilieinsert();

switch($action) {
    case 'reply':
        nav('<a href="viewthread.php?tid='.$tid.'">'.$threadname.'</a>');
        nav($lang['textreply']);

        if ($SETTINGS['subject_in_title'] == 'on') {
            $threadSubject = '- '.$threadname;
        }

        eval('$header = "'.template('header').'";');

        $replyvalid = onSubmit('replysubmit'); // This new flag will indicate a message was submitted and successful.

        if ($forum['attachstatus'] == 'on' And $username != 'Anonymous') {
            for ($i=1; $i<=$SETTINGS['filesperpost']; $i++) {
                if (isset($_FILES['attach'.$i])) {
                    $result = attachUploadedFile('attach'.$i);
                    if ($result < 0 And $result != X_EMPTY_UPLOAD) {
                        $errors .= softerror($attachmentErrors[$result]);
                        $replyvalid = FALSE;
                    }
                }
            }
            $result = doAttachmentEdits($deletes);
            if ($result < 0) {
                $errors .= softerror($attachmentErrors[$result]);
                $replyvalid = FALSE;
            }
            foreach($deletes as $aid) {
                $messageinput = str_replace("[file]{$aid}[/file]", '', $messageinput);
            }
            if ($SETTINGS['attach_remote_images'] == 'on') {
                $result = extractRemoteImages(0, $messageinput);
                if ($result < 0) {
                    $errors .= softerror($attachmentErrors[$result]);
                    $replyvalid = FALSE;
                }
            }
            $attachSkipped = FALSE;
        } else {
            $attachSkipped = TRUE;
        }

        //Check all replying permissions for this $tid.
        if (!X_SADMIN And $thread['closed'] != '') {
            if ($replyvalid) {
                $errors .= softerror($lang['closedmsg']);
            } else {
                error($lang['closedmsg']);
            }
            $replyvalid = FALSE;
        }

        if ($replyvalid) {
            if (X_GUEST) { // Anonymous posting is allowed, and was checked in forum perms at top of file.
                $password = '';
                if (strlen(postedVar('username')) > 0 And isset($_POST['password'])) {
                    if (loginUser(postedVar('username'), md5($_POST['password']))) {
                        if ($self['status'] == "Banned") {
                            $errors .= softerror($lang['bannedmessage']);
                            $replyvalid = FALSE;
                        } else if ($self['ban'] == "posts" || $self['ban'] == "both") {
                            $errors .= softerror($lang['textbanfrompost']);
                            $replyvalid = FALSE;
                        } else {
                            $username = $xmbuser;

                            // check permissions on this forum (and top forum if it's a sub?)
                            $perms = checkForumPermissions($forum);
                            if (!$perms[X_PERMS_VIEW]) {
                                $errors .= softerror($lang['privforummsg']);
                                $topicvalid = FALSE;
                            } else if (!$perms[X_PERMS_REPLY]) {
                                $errors .= softerror($lang['textnoaction']);
                                $topicvalid = FALSE;
                            }

                            if ($forum['type'] == 'sub') {
                                // prevent access to subforum when upper forum can't be viewed.
                                $fupPerms = checkForumPermissions($fup);
                                if (!$fupPerms[X_PERMS_VIEW]) {
                                    $errors .= softerror($lang['privforummsg']);
                                    $topicvalid = FALSE;
                                }
                            }
                        }
                    } else {
                        $errors .= softerror($lang['textpw1']);
                        $replyvalid = FALSE;
                    }
                } else if ($SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on' && !DEBUG) {
                    $Captcha = new Captcha(250, 50);
                    if ($Captcha->bCompatible !== false) {
                        $imgcode = postedVar('imgcode', '', FALSE, FALSE);
                        $imghash = postedVar('imghash');
                        if ($Captcha->ValidateCode($imgcode, $imghash) !== TRUE) {
                            $errors .= softerror($lang['captchaimageinvalid']);
                            $replyvalid = FALSE;
                        }
                    }
                    unset($Captcha);
                }
            }
        }

        if ($replyvalid) {
            if (strlen(postedVar('subject')) == 0 && strlen($messageinput) == 0) {
                $errors .= softerror($lang['postnothing']);
                $replyvalid = FALSE;
            }
        }

        if ($replyvalid) {
            if ($posticon != '') {
                $query = $db->query("SELECT id FROM ".X_PREFIX."smilies WHERE type='picon' AND url='$posticon'");
                if ($db->num_rows($query) == 0) {
                    $posticon = '';
                    $errors .= softerror($lang['error']);
                    $replyvalid = FALSE;
                }
                $db->free_result($query);
            }
        }

        if ($replyvalid) {
            if ($forum['lastpost'] != '') {
                $lastpost = explode('|', $forum['lastpost']);
                $rightnow = $onlinetime - $floodctrl;
                if ($rightnow <= $lastpost[0] && $username == $lastpost[1]) {
                    $floodlink = "<a href=\"viewthread.php?fid=$fid&tid=$tid\">Click here</a>";
                    $errmsg = $lang['floodprotect'].' '.$floodlink.' '.$lang['tocont'];
                    $errors .= softerror($errmsg);
                    $replyvalid = FALSE;
                }
            }
        }

        if ($replyvalid) {
            $thatime = $onlinetime;
            postLinkBBcode($messageinput);
            $dbmessage = $db->escape(addslashes($messageinput)); //The message column is historically double-quoted.
            $dbsubject = addslashes(postedVar('subject', 'javascript', TRUE, TRUE, TRUE));
            $db->query("INSERT INTO ".X_PREFIX."posts (fid, tid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff) VALUES ($fid, $tid, '$username', '$dbmessage', '$dbsubject', ".$db->time(time()).", '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff')");
            $pid = $db->insert_id();

            if ((X_STAFF) && $closetopic == 'yes') {
                $db->query("UPDATE ".X_PREFIX."threads SET closed='yes' WHERE tid='$tid' AND fid='$fid'");
            }

            $db->query("UPDATE ".X_PREFIX."threads SET lastpost='$thatime|$username|$pid', replies=replies+1 WHERE tid=$tid");

            $where = "WHERE fid=$fid";
            if ($forum['type'] == 'sub') {
                $where .= " OR fid={$forum['fup']}";
            }
            $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$thatime|$username|$pid', posts=posts+1 $where");
            unset($where);

            $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum+1 WHERE username='$username'");

            $query = $db->query("SELECT COUNT(pid) FROM ".X_PREFIX."posts WHERE pid <= $pid AND tid='$tid'");
            $posts = $db->result($query,0);
            $db->free_result($query);

            $lang2 = loadPhrases(array('charset','textsubsubject','textsubbody'));
            $viewperm = getOneForumPerm($forum, X_PERMS_RAWVIEW);
            $date = $db->result($db->query("SELECT dateline FROM ".X_PREFIX."posts WHERE tid='$tid' AND pid < $pid ORDER BY dateline DESC LIMIT 1"), 0);
            $subquery = $db->query("SELECT m.email, m.lastvisit, m.ppp, m.status, m.langfile "
                                 . "FROM ".X_PREFIX."favorites f "
                                 . "INNER JOIN ".X_PREFIX."members m USING (username) "
                                 . "WHERE f.type = 'subscription' AND f.tid = $tid AND m.username != '$username' AND m.lastvisit >= $date");
            while($subs = $db->fetch_array($subquery)) {
                if ($viewperm < $status_enum[$subs['status']]) {
                    continue;
                }

                if ($subs['ppp'] < 1) {
                    $subs['ppp'] = $posts;
                }

                $translate = $lang2[$subs['langfile']];
                $topicpages = quickpage($posts, $subs['ppp']);
                $topicpages = ($topicpages == 1) ? '' : '&page='.$topicpages;
                $threadurl = $full_url.'viewthread.php?tid='.$tid.$topicpages.'#pid'.$pid;
                $rawsubject = htmlspecialchars_decode($threadname, ENT_QUOTES);
                $rawusername = htmlspecialchars_decode($username, ENT_QUOTES);
                $rawemail = htmlspecialchars_decode($subs['email'], ENT_QUOTES);
                $headers = array();
                $headers[] = "From: $bbname <$adminemail>";
                $headers[] = 'X-Mailer: PHP';
                $headers[] = 'X-AntiAbuse: Board servername - '.$cookiedomain;
                $headers[] = 'X-AntiAbuse: Username - '.$rawusername;
                $headers[] = 'Content-Type: text/plain; charset='.$translate['charset'];
                $headers = implode("\r\n", $headers);
                altMail($rawemail, $rawsubject.' ('.$translate['textsubsubject'].')', $rawusername.' '.$translate['textsubbody']." \n".$threadurl, $headers);
            }
            $db->free_result($subquery);

            if (isset($emailnotify) && $emailnotify == 'yes') {
                $query = $db->query("SELECT tid FROM ".X_PREFIX."favorites WHERE tid='$tid' AND username='$username' AND type='subscription'");
                if ($db->num_rows($query) < 1) {
                    $db->query("INSERT INTO ".X_PREFIX."favorites (tid, username, type) VALUES ($tid, '$username', 'subscription')");
                }
                $db->free_result($query);
            }

            if ($forum['attachstatus'] == 'on') {
                if ($attachSkipped) {
                    for ($i=1; $i<=$SETTINGS['filesperpost']; $i++) {
                        if (isset($_FILES['attach'.$i])) {
                            attachUploadedFile('attach'.$i, $pid);
                        }
                    }
                    if ($SETTINGS['attach_remote_images'] == 'on') {
                        extractRemoteImages($pid, $messageinput);
                        $newdbmessage = $db->escape(addslashes($messageinput));
                        if ($newdbmessage != $dbmessage) { // Anonymous message was modified after save, in order to use the pid.
                            $db->query("UPDATE ".X_PREFIX."posts SET message='$newdbmessage' WHERE pid=$pid");
                        }
                    }
                } elseif ($username != 'Anonymous') {
                    claimOrphanedAttachments($pid);
                }
            }

            $topicpages = quickpage($posts, $ppp);
            $topicpages = ($topicpages == 1) ? '' : '&page='.$topicpages;
            message($lang['replymsg'], TRUE, '', '', $full_url."viewthread.php?tid={$tid}{$topicpages}#pid{$pid}", true, false, true);
        }

        if (!$replyvalid) {
            if (isset($repquote) && ($repquote = (int) $repquote)) {
                $query = $db->query("SELECT p.message, p.tid, p.fid, p.author FROM ".X_PREFIX."posts p WHERE p.pid=$repquote");
                $thaquote = $db->fetch_array($query);
                $db->free_result($query);
                $quoteperms = checkForumPermissions(getForum($thaquote['fid']));
                if ($quoteperms[X_PERMS_VIEW] And $quoteperms[X_PERMS_PASSWORD]) {
                    $thaquote['message'] = preg_replace('@\\[file\\]\\d*\\[/file\\]@', '', $thaquote['message']); //These codes will not work inside quotes.
                    //Note this bbcode is a pseudo-link. Treat it as cdata.  Do not recode the author string.
                    $messageinput = "[rquote=$repquote&amp;tid={$thaquote['tid']}&amp;author={$thaquote['author']}]".rawHTMLmessage(stripslashes($thaquote['message']))."[/rquote]"; //Messages are historically double-quoted.
                }
            }

            // Fill $attachfile
            if ($forum['attachstatus'] == 'on' And $username != 'Anonymous') {
                $attachfile = '';
                $query = $db->query("SELECT aid, filename, filesize FROM ".X_PREFIX."attachments WHERE uid={$self['uid']} AND pid=0 AND parentid=0");
                $counter = 0;
                while ($postinfo = $db->fetch_array($query)) {
                    $postinfo['filename'] = attrOut($postinfo['filename']);
                    $postinfo['filesize'] = number_format($postinfo['filesize'], 0, '.', ',');
                    eval('$attachfile .= "'.template('post_attachment_orphan').'";');
                    if ($bbcodeoff = 'no') {
                        $bbcode = "[file]{$postinfo['aid']}[/file]";
                        if (strpos($messageinput, $bbcode) === FALSE) {
                            if ($counter == 0) {
                                $messageinput .= "\r\n\r\n";
                            }
                            $messageinput .= ' '.$bbcode; // Use a leading space to prevent awkward line wraps.
                            $counter++;
                        }
                    }
                }
                $maxtotal = phpShorthandValue('post_max_size');
                if ($maxtotal > 0) {
                    $lang['attachmaxtotal'] .= ' '.getSizeFormatted($maxtotal);
                } else {
                    $lang['attachmaxtotal'] = '';
                }
                $maxuploads = $SETTINGS['filesperpost'] - $db->num_rows($query);
                if ($maxuploads > 0) {
                    eval('$attachfile .= "'.template("post_attachmentbox").'";');
                }
                $db->free_result($query);
            }

            //Allow sanitized message to pass-through to template in case of: #1 preview, #2 post error
            $subject = rawHTMLsubject(postedVar('subject', 'javascript', TRUE, FALSE, TRUE));
            $message = rawHTMLmessage($messageinput);

            if (isset($previewpost)) {
                if ($posticon != '') {
                    $thread['icon'] = "<img src=\"$smdir/$posticon\" />";
                } else {
                    $thread['icon'] = '';
                }
                $currtime = $onlinetime;
                $date = gmdate($dateformat, $currtime + ($timeoffset * 3600) + ($addtime * 3600));
                $time = gmdate($timecode, $currtime + ($timeoffset * 3600) + ($addtime * 3600));
                $poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;
                $dissubject = $subject;
                postLinkBBcode($messageinput);
                $message1 = postify($messageinput, $smileyoff, $bbcodeoff, $forum['allowsmilies'], $forum['allowhtml'], $forum['allowbbcode'], $forum['allowimgcode']);
                eval('$preview = "'.template('post_preview').'";');
            }

            if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on' && !DEBUG) {
                $Captcha = new Captcha(250, 50);
                if ($Captcha->bCompatible !== false) {
                    $imghash = $Captcha->GenerateCode();
                    if ($SETTINGS['captcha_code_casesensitive'] == 'off') {
                        $lang['captchacaseon'] = '';
                    }
                    eval('$captchapostcheck = "'.template('post_captcha').'";');
                }
                unset($Captcha);
            }

            $posts = '';

            if (X_STAFF) {
                $closeoption = '<br /><input type="checkbox" name="closetopic" value="yes" '.$closecheck.' /> '.$lang['closemsgques'].'<br />';
            } else {
                $closeoption = '';
            }

            $querytop = $db->query("SELECT COUNT(tid) FROM ".X_PREFIX."posts WHERE tid='$tid'");
            $replynum = $db->result($querytop, 0);
            if ($replynum >= $ppp) {
                $threadlink = 'viewthread.php?fid='.$fid.'&tid='.$tid;
                eval($lang['evaltrevlt']);
                eval('$posts .= "'.template('post_reply_review_toolong').'";');
            } else {
                $thisbg = $altbg1;
                $query = $db->query("SELECT * FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY dateline DESC");
                while($post = $db->fetch_array($query)) {
                    $date = gmdate($dateformat, $post['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));
                    $time = gmdate($timecode, $post['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));
                    $poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;

                    if ($post['icon'] != '') {
                        $post['icon'] = '<img src="'.$smdir.'/'.$post['icon'].'" alt="'.$lang['altpostmood'].'" border="0" />';
                    } else {
                        $post['icon'] = '<img src="'.$imgdir.'/default_icon.gif" alt="[*]" border="0" />';
                    }

                    $post['message'] = preg_replace('@\\[file\\]\\d*\\[/file\\]@', '', $post['message']); //These codes do not work in postify()
                    $post['message'] = postify(stripslashes($post['message']), $post['smileyoff'], $post['bbcodeoff'], $forum['allowsmilies'], $forum['allowhtml'], $forum['allowbbcode'], $forum['allowimgcode']);
                    eval('$posts .= "'.template('post_reply_review_post').'";');
                    if ($thisbg == $altbg2) {
                        $thisbg = $altbg1;
                    } else {
                        $thisbg = $altbg2;
                    }
                }
                $db->free_result($query);
            }
            $db->free_result($querytop);

            if (getOneForumPerm($forum, X_PERMS_RAWREPLY) == $status_enum['Guest']) { // Member posting is not allowed, do not request credentials!
                $loggedin = '';
            }

            eval('$postpage = "'.template('post_reply').'";');
        }
        break;

    case 'newthread':
        if ($poll == 'yes') {
            nav($lang['textnewpoll']);
        } else {
            nav($lang['textpostnew']);
        }

        if ($SETTINGS['subject_in_title'] == 'on') {
            $threadSubject = '- '.$dissubject;
        }

        eval('$header = "'.template('header').'";');

        $pollanswers = postedVar('pollanswers', '', TRUE, FALSE);
        $topicvalid = onSubmit('topicsubmit'); // This new flag will indicate a message was submitted and successful.

        if ($forum['attachstatus'] == 'on' And $username != 'Anonymous') {
            for ($i=1; $i<=$SETTINGS['filesperpost']; $i++) {
                if (isset($_FILES['attach'.$i])) {
                    $result = attachUploadedFile('attach'.$i);
                    if ($result < 0 And $result != X_EMPTY_UPLOAD) {
                        $errors .= softerror($attachmentErrors[$result]);
                        $topicvalid = FALSE;
                    }
                }
            }
            $result = doAttachmentEdits($deletes);
            if ($result < 0) {
                $errors .= softerror($attachmentErrors[$result]);
                $topicvalid = FALSE;
            }
            foreach($deletes as $aid) {
                $messageinput = str_replace("[file]{$aid}[/file]", '', $messageinput);
            }
            if ($SETTINGS['attach_remote_images'] == 'on') {
                $result = extractRemoteImages(0, $messageinput);
                if ($result < 0) {
                    $errors .= softerror($attachmentErrors[$result]);
                    $topicvalid = FALSE;
                }
            }
            $attachSkipped = FALSE;
        } else {
            $attachSkipped = TRUE;
        }

        if ($topicvalid) {
            if (X_GUEST) { // Anonymous posting is allowed, and was checked in forum perms at top of file.
                $password = '';
                if (strlen(postedVar('username')) > 0 And isset($_POST['password'])) {
                    if (loginUser(postedVar('username'), md5($_POST['password']))) {
                        if ($self['status'] == "Banned") {
                            $errors .= softerror($lang['bannedmessage']);
                            $topicvalid = FALSE;
                        } else if ($self['ban'] == "posts" || $self['ban'] == "both") {
                            $errors .= softerror($lang['textbanfrompost']);
                            $topicvalid = FALSE;
                        } else {
                            $username = $xmbuser;

                            // check permissions on this forum (and top forum if it's a sub?)
                            $perms = checkForumPermissions($forum);
                            if (!$perms[X_PERMS_VIEW]) {
                                $errors .= softerror($lang['privforummsg']);
                                $topicvalid = FALSE;
                            } else if (($poll == '' && !$perms[X_PERMS_THREAD]) || ($poll == 'yes' && !$perms[X_PERMS_POLL])) {
                                $errors .= softerror($lang['textnoaction']);
                                $topicvalid = FALSE;
                            }

                            if ($forum['type'] == 'sub') {
                                // prevent access to subforum when upper forum can't be viewed.
                                $fupPerms = checkForumPermissions($fup);
                                if (!$fupPerms[X_PERMS_VIEW]) {
                                    $errors .= softerror($lang['privforummsg']);
                                    $topicvalid = FALSE;
                                }
                            }
                        }
                    } else {
                        $errors .= softerror($lang['textpw1']);
                        $topicvalid = FALSE;
                    }
                } else if ($SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on' && !DEBUG) {
                    $Captcha = new Captcha(250, 50);
                    if ($Captcha->bCompatible !== false) {
                        $imgcode = postedVar('imgcode', '', FALSE, FALSE);
                        $imghash = postedVar('imghash');
                        if ($Captcha->ValidateCode($imgcode, $imghash) !== TRUE) {
                            $errors .= softerror($lang['captchaimageinvalid']);
                            $topicvalid = FALSE;
                        }
                    }
                    unset($Captcha);
                }
            }
        }

        if ($topicvalid) {
            if (strlen(postedVar('subject')) == 0) {
                $errors .= softerror($lang['textnosubject']);
                $topicvalid = FALSE;
            }
        }

        if ($topicvalid) {
            if ($posticon != '') {
                $query = $db->query("SELECT id FROM ".X_PREFIX."smilies WHERE type='picon' AND url='$posticon'");
                if ($db->num_rows($query) == 0) {
                    $posticon = '';
                    $errors .= softerror($lang['error']);
                    $topicvalid = FALSE;
                }
                $db->free_result($query);
            }
        }

        if ($topicvalid) {
            if ($forum['lastpost'] != '') {
                $lastpost = explode('|', $forum['lastpost']);
                $rightnow = $onlinetime - $floodctrl;
                if ($rightnow <= $lastpost[0] && $username == $lastpost[1]) {
                    $errors .= softerror($lang['floodprotect']);
                    $topicvalid = FALSE;
                }
            }
        }

        if ($topicvalid) {
            if ($poll == 'yes') {
                $pollopts = array();
                $pollopts2 = explode("\n", $pollanswers);
                foreach($pollopts2 as $value) {
                    $value = trim($value);
                    if ($value != '') {
                        $pollopts[] = $value;
                    }
                }
                $pnumnum = count($pollopts);

                if ($pnumnum < 2) {
                    $errors .= softerror($lang['too_few_pollopts']);
                    $topicvalid = FALSE;
                }
            }
        }

        if ($topicvalid) {
            $thatime = $onlinetime;

            postLinkBBcode($messageinput);
            $dbmessage = $db->escape(addslashes($messageinput)); //The message column is historically double-quoted.
            $dbsubject = addslashes(postedVar('subject', 'javascript', TRUE, TRUE, TRUE));
            $db->query("INSERT INTO ".X_PREFIX."threads (fid, subject, icon, lastpost, views, replies, author, closed, topped) VALUES ($fid, '$dbsubject', '$posticon', '$thatime|$username', 0, 0, '$username', '', 0)");
            $tid = $db->insert_id();

            $db->query("INSERT INTO ".X_PREFIX."posts (fid, tid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff) VALUES ($fid, $tid, '$username', '$dbmessage', '$dbsubject', ".$db->time($thatime).", '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff')");
            $pid = $db->insert_id();

            $db->query("UPDATE ".X_PREFIX."threads SET lastpost=concat(lastpost, '|".$pid."') WHERE tid='$tid'");

            $where = "WHERE fid=$fid";
            if ($forum['type'] == 'sub') {
                $where .= " OR fid={$forum['fup']}";
            }
            $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$thatime|$username|$pid', threads=threads+1, posts=posts+1 $where");
            unset($where);

            if ($poll == 'yes') {
                $query = $db->query("SELECT vote_id, topic_id FROM ".X_PREFIX."vote_desc WHERE topic_id='$tid'");
                if ($query) {
                    $vote_id = $db->fetch_array($query);
                    $vote_id = $vote_id['vote_id'];
                    if ($vote_id > 0) {
                        $db->query("DELETE FROM ".X_PREFIX."vote_results WHERE vote_id='$vote_id'");
                        $db->query("DELETE FROM ".X_PREFIX."vote_voters WHERE vote_id='$vote_id'");
                        $db->query("DELETE FROM ".X_PREFIX."vote_desc WHERE vote_id='$vote_id'");
                    }
                }
                $db->free_result($query);

                $dbsubject = addslashes(postedVar('subject', 'javascript', TRUE, TRUE, TRUE));
                $db->query("INSERT INTO ".X_PREFIX."vote_desc (topic_id, vote_text) VALUES ($tid, '$dbsubject')");
                $vote_id =  $db->insert_id();
                $i = 1;
                foreach($pollopts as $p) {
                    $p = $db->escape($p);
                    $db->query("INSERT INTO ".X_PREFIX."vote_results (vote_id, vote_option_id, vote_option_text, vote_result) VALUES ($vote_id, $i, '$p', 0)");
                    $i++;
                }
                $db->query("UPDATE ".X_PREFIX."threads SET pollopts=1 WHERE tid='$tid'");
            }

            if (isset($emailnotify) && $emailnotify == 'yes') {
                $query = $db->query("SELECT tid FROM ".X_PREFIX."favorites WHERE tid='$tid' AND username='$username' AND type='subscription'");
                $thread = $db->fetch_array($query);
                $db->free_result($query);
                if (!$thread) {
                    $db->query("INSERT INTO ".X_PREFIX."favorites (tid, username, type) VALUES ($tid, '$username', 'subscription')");
                }
            }

            $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum+1 WHERE username='$username'");

            if ((X_STAFF) && $toptopic == 'yes') {
                $db->query("UPDATE ".X_PREFIX."threads SET topped='1' WHERE tid='$tid' AND fid='$fid'");
            }

            if ((X_STAFF) && $closetopic == 'yes') {
                $db->query("UPDATE ".X_PREFIX."threads SET closed='yes' WHERE tid='$tid' AND fid='$fid'");
            }

            if ($forum['attachstatus'] == 'on') {
                if ($attachSkipped) {
                    for ($i=1; $i<=$SETTINGS['filesperpost']; $i++) {
                        if (isset($_FILES['attach'.$i])) {
                            attachUploadedFile('attach'.$i, $pid);
                        }
                    }
                    if ($SETTINGS['attach_remote_images'] == 'on') {
                        extractRemoteImages($pid, $messageinput);
                        $newdbmessage = $db->escape(addslashes($messageinput));
                        if ($newdbmessage != $dbmessage) { // Anonymous message was modified after save, in order to use the pid.
                            $db->query("UPDATE ".X_PREFIX."posts SET message='$newdbmessage' WHERE pid=$pid");
                        }
                    }
                } elseif ($username != 'Anonymous') {
                    claimOrphanedAttachments($pid);
                }
            }

            $query = $db->query("SELECT COUNT(tid) FROM ".X_PREFIX."posts WHERE tid='$tid'");
            $posts = $db->result($query, 0);
            $db->free_result($query);

            $topicpages = quickpage($posts, $ppp);
            $topicpages = ($topicpages == 1) ? '' : '&page='.$topicpages;
            message($lang['postmsg'], TRUE, '', '', $full_url."viewthread.php?tid={$tid}{$topicpages}#pid{$pid}", true, false, true);
        }

        if (!$topicvalid) {
            // Fill $attachfile
            if ($forum['attachstatus'] == 'on' And $username != 'Anonymous') {
                $attachfile = '';
                $query = $db->query("SELECT aid, filename, filesize FROM ".X_PREFIX."attachments WHERE uid={$self['uid']} AND pid=0 AND parentid=0");
                $counter = 0;
                while ($postinfo = $db->fetch_array($query)) {
                    $postinfo['filename'] = attrOut($postinfo['filename']);
                    $postinfo['filesize'] = number_format($postinfo['filesize'], 0, '.', ',');
                    eval('$attachfile .= "'.template('post_attachment_orphan').'";');
                    if ($bbcodeoff = 'no') {
                        $bbcode = "[file]{$postinfo['aid']}[/file]";
                        if (strpos($messageinput, $bbcode) === FALSE) {
                            if ($counter == 0) {
                                $messageinput .= "\r\n\r\n";
                            }
                            $messageinput .= ' '.$bbcode; // Use a leading space to prevent awkward line wraps.
                            $counter++;
                        }
                    }
                }
                $maxtotal = phpShorthandValue('post_max_size');
                if ($maxtotal > 0) {
                    $lang['attachmaxtotal'] .= ' '.getSizeFormatted($maxtotal);
                } else {
                    $lang['attachmaxtotal'] = '';
                }
                $maxuploads = $SETTINGS['filesperpost'] - $db->num_rows($query);
                if ($maxuploads > 0) {
                    eval('$attachfile .= "'.template("post_attachmentbox").'";');
                }
                $db->free_result($query);
            }

            //Allow sanitized message to pass-through to template in case of: #1 preview, #2 post error
            $subject = rawHTMLsubject(postedVar('subject', 'javascript', TRUE, FALSE, TRUE));
            $message = rawHTMLmessage($messageinput);

            if (isset($previewpost)) {
                if ($posticon != '') {
                    $thread['icon'] = "<img src=\"$smdir/$posticon\" />";
                } else {
                    $thread['icon'] = '';
                }
                $currtime = $onlinetime;
                $date = gmdate($dateformat, $currtime + ($timeoffset * 3600) + ($addtime * 3600));
                $time = gmdate($timecode, $currtime + ($timeoffset * 3600) + ($addtime * 3600));
                $poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;
                $dissubject = $subject;
                postLinkBBcode($messageinput);
                $message1 = postify($messageinput, $smileyoff, $bbcodeoff, $forum['allowsmilies'], $forum['allowhtml'], $forum['allowbbcode'], $forum['allowimgcode']);
                eval('$preview = "'.template('post_preview').'";');
            }

            if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on' && !DEBUG) {
                $Captcha = new Captcha(250, 50);
                if ($Captcha->bCompatible !== false) {
                    $imghash = $Captcha->GenerateCode();
                    if ($SETTINGS['captcha_code_casesensitive'] == 'off') {
                        $lang['captchacaseon'] = '';
                    }
                    eval('$captchapostcheck = "'.template('post_captcha').'";');
                }
                unset($Captcha);
            }

            if (X_STAFF) {
                $topoption = '<br /><input type="checkbox" name="toptopic" value="yes" '.$topcheck.' /> '.$lang['topmsgques'];
                $closeoption = '<br /><input type="checkbox" name="closetopic" value="yes" '.$closecheck.' /> '.$lang['closemsgques'].'<br />';
            } else {
                $topoption = '';
                $closeoption = '';
            }

            if (!isset($spelling_submit2)) {
                $spelling_submit2 = '';
            }

            if (getOneForumPerm($forum, X_PERMS_RAWTHREAD) == $status_enum['Guest']) { // Member posting is not allowed, do not request credentials!
                $loggedin = '';
            }

            if (isset($poll) && $poll == 'yes') {
                eval('$postpage = "'.template('post_newpoll').'";');
            } else {
                eval('$postpage = "'.template('post_newthread').'";');
            }
        }
        break;

    case 'edit':
        nav('<a href="viewthread.php?tid='.$tid.'">'.$threadname.'</a>');
        nav($lang['texteditpost']);

        if ($SETTINGS['subject_in_title'] == 'on') {
            $threadSubject = '- '.$threadname;
        }

        eval('$header = "'.template('header').'";');

        $editvalid = TRUE; // This new flag will indicate a message was submitted and successful.

        //Check all editing permissions for this $pid.  Based on viewthread design, forum Moderators can always edit, $orig['author'] can edit open threads only.
        $query = $db->query("SELECT p.*, m.status FROM ".X_PREFIX."posts p LEFT JOIN ".X_PREFIX."members m ON p.author=m.username WHERE p.pid=$pid");
        $orig = $db->fetch_array($query);
        $db->free_result($query);

        $status1 = modcheckPost($self['username'], $forum['moderator'], $orig['status']);

        if ($status1 != 'Moderator' And ($self['username'] != $orig['author'] Or $thread['closed'] != '')) {
            $errors .= softerror($lang['noedit']);
            $editvalid = FALSE;
        }

        if ($editvalid) {
            if ($forum['attachstatus'] == 'on') {
                for ($i=1; $i<=$SETTINGS['filesperpost']; $i++) {
                    if (isset($_FILES['attach'.$i])) {
                        $result = attachUploadedFile('attach'.$i, $pid);
                        if ($result < 0 And $result != X_EMPTY_UPLOAD) {
                            $errors .= softerror($attachmentErrors[$result]);
                            $editvalid = FALSE;
                        }
                    }
                }
                $result = doAttachmentEdits($deletes, $pid);
                if ($result < 0) {
                    $errors .= softerror($attachmentErrors[$result]);
                    $editvalid = FALSE;
                }
                foreach($deletes as $aid) {
                    $messageinput = str_replace("[file]{$aid}[/file]", '', $messageinput);
                }
                $temp = '';
                if ($SETTINGS['attach_remote_images'] == 'on') {
                    $result = extractRemoteImages($pid, $messageinput);
                    if ($result < 0) {
                        $errors .= softerror($attachmentErrors[$result]);
                        $editvalid = FALSE;
                    }
                }
            }
        }

        $editvalid &= onSubmit('editsubmit');

        if ($editvalid) {
            if ($posticon != '') {
                $query = $db->query("SELECT id FROM ".X_PREFIX."smilies WHERE type='picon' AND url='$posticon'");
                if ($db->num_rows($query) == 0) {
                    $posticon = '';
                    $errors .= softerror($lang['error']);
                    $editvalid = FALSE;
                }
                $db->free_result($query);
            }
        }

        if ($editvalid) {
            $query = $db->query("SELECT pid FROM ".X_PREFIX."posts WHERE tid=$tid ORDER BY dateline LIMIT 1");
            $isfirstpost = $db->fetch_array($query);
            $db->free_result($query);

            if ((strlen(postedVar('subject')) == 0 && $pid == $isfirstpost['pid']) && !(isset($delete) && $delete == 'yes')) {
                $errors .= softerror($lang['textnosubject']);
                $editvalid = FALSE;
            }
        }

        if ($editvalid) {
            $threaddelete = 'no';

            if (!(isset($delete) && $delete == 'yes')) {
                if ($SETTINGS['editedby'] == 'on') {
                    $messageinput .= "\n\n[".$lang['textediton'].' '.gmdate($dateformat).' '.$lang['textby']." $username]";
                }

                postLinkBBcode($messageinput);
                $dbmessage = $db->escape(addslashes($messageinput)); //The subject and message columns are historically double-quoted.
                $dbsubject = addslashes(postedVar('subject', 'javascript', TRUE, TRUE, TRUE));

                if ($isfirstpost['pid'] == $pid) {
                    $db->query("UPDATE ".X_PREFIX."threads SET icon='$posticon', subject='$dbsubject' WHERE tid=$tid");
                }

                $db->query("UPDATE ".X_PREFIX."posts SET message='$dbmessage', usesig='$usesig', bbcodeoff='$bbcodeoff', smileyoff='$smileyoff', icon='$posticon', subject='$dbsubject' WHERE pid=$pid");
            } else {
                $db->query("DELETE FROM ".X_PREFIX."posts WHERE pid=$pid");
                $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-1 WHERE username='".$db->escape($orig['author'])."'");
                deleteAllAttachments($pid);

                if ($isfirstpost['pid'] == $pid) {
                    $query = $db->query("SELECT COUNT(pid) AS pcount FROM ".X_PREFIX."posts WHERE tid=$tid");
                    $numrows = $db->fetch_array($query);
                    $numrows = $numrows['pcount'];
                    $db->free_result($query);

                    if ($numrows == 0) {
                        $threaddelete = 'yes';
                        $db->query("DELETE FROM ".X_PREFIX."favorites WHERE tid='$tid'");

                        // Important: Do not alias tables in multi-table delete queries as long as MySQL 4.0 is supported.
                        $db->query("DELETE FROM ".X_PREFIX."vote_desc, ".X_PREFIX."vote_results, ".X_PREFIX."vote_voters "
                                 . "USING ".X_PREFIX."vote_desc "
                                 . "LEFT JOIN ".X_PREFIX."vote_results ON ".X_PREFIX."vote_results.vote_id = ".X_PREFIX."vote_desc.vote_id "
                                 . "LEFT JOIN ".X_PREFIX."vote_voters  ON ".X_PREFIX."vote_voters.vote_id  = ".X_PREFIX."vote_desc.vote_id "
                                 . "WHERE ".X_PREFIX."vote_desc.topic_id = $tid");

                        $db->query("DELETE FROM ".X_PREFIX."threads WHERE tid=$tid OR closed='moved|$tid'");
                    } else {
                        $db->query("UPDATE ".X_PREFIX."posts SET subject='".$db->escape($orig['subject'])."' WHERE tid=$tid ORDER BY dateline LIMIT 1");
                    }
                }
                updatethreadcount($tid);
                updateforumcount($fid);
            }

            if ($threaddelete == 'no') {
                $query = $db->query("SELECT COUNT(pid) FROM ".X_PREFIX."posts WHERE dateline <= {$orig['dateline']} AND tid=$tid");
                $posts = $db->result($query,0);
                $db->free_result($query);
                $topicpages = quickpage($posts, $ppp);
                $topicpages = ($topicpages == 1) ? '' : '&page='.$topicpages;
                message($lang['editpostmsg'], TRUE, '', '', $full_url."viewthread.php?tid={$tid}{$topicpages}#pid{$pid}", true, false, true);
            } else {
                message($lang['editpostmsg'], TRUE, '', '', $full_url.'forumdisplay.php?fid='.$fid, true, false, true);
            }
        }

        if (!$editvalid) {
            // Fill $postinfo
            $subjectinput = postedVar('subject', 'javascript', TRUE, FALSE, TRUE);
            if (onSubmit('editsubmit') || isset($previewpost) || $sc) {
                $postinfo = array("usesig"=>$usesig, "bbcodeoff"=>$bbcodeoff, "smileyoff"=>$smileyoff, "message"=>$messageinput, "subject"=>$subjectinput, 'icon'=>$posticon);
            } else {
                $postinfo = $orig;
                $postinfo['message'] = stripslashes($postinfo['message']); //Messages are historically double-quoted.
                $postinfo['subject'] = stripslashes($postinfo['subject']);
            }

            // Fill $attachment
            $attachment = '';
            $query = $db->query("SELECT aid, filename, filesize, downloads FROM ".X_PREFIX."attachments WHERE pid=$pid AND parentid=0");
            $counter = 0;
            while ($attach = $db->fetch_array($query)) {
                $postinfo['aid'] = $attach['aid'];
                $postinfo['downloads'] = $attach['downloads'];
                $postinfo['filename'] = attrOut($attach['filename']);
                $postinfo['filesize'] = number_format($attach['filesize'], 0, '.', ',');
                $postinfo['url'] = getAttachmentURL($attach['aid'], $pid, $attach['filename']);
                eval('$attachment .= "'.template('post_edit_attachment').'";');
                if ($bbcodeoff = 'no') {
                    $bbcode = "[file]{$attach['aid']}[/file]";
                    if (strpos($postinfo['message'], $bbcode) === FALSE) {
                        if ($counter == 0) {
                            $postinfo['message'] .= "\r\n\r\n";
                        }
                        $postinfo['message'] .= ' '.$bbcode; // Use a leading space to prevent awkward line wraps.
                        $counter++;
                    }
                }
            }
            $maxtotal = phpShorthandValue('post_max_size');
            if ($maxtotal > 0) {
                $lang['attachmaxtotal'] .= ' '.getSizeFormatted($maxtotal);
            } else {
                $lang['attachmaxtotal'] = '';
            }
            $maxuploads = $SETTINGS['filesperpost'] - $db->num_rows($query);
            if ($maxuploads > 0) {
                eval('$attachment .= "'.template("post_attachmentbox").'";');
            }
            $db->free_result($query);

            //Allow sanitized message to pass-through to template in case of: #1 preview, #2 post error
            $subject = rawHTMLsubject($postinfo['subject']);
            $message = rawHTMLmessage($postinfo['message']);

            if (isset($previewpost)) {
                if ($postinfo['icon'] != '') {
                    $thread['icon'] = "<img src=\"$smdir/{$postinfo['icon']}\" />";
                } else {
                    $thread['icon'] = '';
                }
                $currtime = $onlinetime;
                $date = gmdate($dateformat, $currtime + ($timeoffset * 3600) + ($addtime * 3600));
                $time = gmdate($timecode, $currtime + ($timeoffset * 3600) + ($addtime * 3600));
                $poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;
                $dissubject = $subject;
                $message1 = $postinfo['message'];
                if ($SETTINGS['editedby'] == 'on') {
                    $message1 .= "\n\n[".$lang['textediton'].' '.gmdate($dateformat).' '.$lang['textby']." $username]";
                }
                postLinkBBcode($message1);
                $message1 = postify($message1, $smileyoff, $bbcodeoff, $forum['allowsmilies'], $forum['allowhtml'], $forum['allowbbcode'], $forum['allowimgcode']);
                eval('$preview = "'.template('post_preview').'";');
            }

            if ($postinfo['bbcodeoff'] == 'yes') {
                $offcheck1 = $cheHTML;
            } else {
                $offcheck1 = '';
            }

            if ($postinfo['smileyoff'] == 'yes') {
                $offcheck2 = $cheHTML;
            } else {
                $offcheck2 = '';
            }

            if ($postinfo['usesig'] == 'yes') {
                $offcheck3 = $cheHTML;
            } else {
                $offcheck3 = '';
            }

            $icons = str_replace('<input type="radio" name="posticon" value="'.$postinfo['icon'].'" />', '<input type="radio" name="posticon" value="'.$postinfo['icon'].'" checked="checked" />', $icons);

            $postinfo['message'] = rawHTMLmessage($postinfo['message']);
            $postinfo['subject'] = rawHTMLsubject($postinfo['subject']);

            eval('$postpage = "'.template('post_edit').'";');
        }
        break;

    default:
        error($lang['textnoaction']);
        break;
}

end_time();
eval('$footer = "'.template('footer').'";');
echo $header.$errors.$postpage.$footer;

function bbcodeinsert() {
    global $imgdir, $bbinsert, $altbg1, $altbg2, $lang, $SETTINGS, $spelling_lang;

    $bbcode = '';
    if ($SETTINGS['bbinsert'] == 'on') {
        eval('$bbcode = "'.template('functions_bbcodeinsert').'";');
    }
    return $bbcode;
}

function postLinkBBcode(&$message) {
    global $db;
    
    $items = array();
    $pattern = "@\\[pid](\\d+)\\[/pid]@si";
    preg_match_all($pattern, $message, $results, PREG_SET_ORDER);
    if (count($results) == 0) {
        return TRUE;
    }
    foreach($results as $result) {
        $items[] = $result[1];
    }

    $pids = implode(', ', $items);
    $query = $db->query("SELECT p.pid, p.tid, p.subject, t.subject AS tsubject, t.fid FROM ".X_PREFIX."posts AS p LEFT JOIN ".X_PREFIX."threads AS t USING (tid) WHERE pid IN ($pids)");
    while($row = $db->fetch_array($query)) {
        $perms = checkForumPermissions(getForum($row['fid']));
        if ($perms[X_PERMS_VIEW] And $perms[X_PERMS_PASSWORD]) {
            if ($row['subject'] != '') {
                $subject = stripslashes($row['subject']);
            } else {
                $subject = stripslashes($row['tsubject']);
            }
            $pattern = "[pid]{$row['pid']}[/pid]";
            $replacement = "[pid={$row['pid']}&amp;tid={$row['tid']}]{$subject}[/pid]";
            $message = str_replace($pattern, $replacement, $message);
        }
    }
    return TRUE;
}

function softerror(&$msg) {
    return error($msg, FALSE, '', '', FALSE, FALSE, TRUE, FALSE);
}
?>
