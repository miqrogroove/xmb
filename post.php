<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2021, The XMB Group
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
'post_edit_attachment',
'spelling_suggestion',
'spelling_suggestion_new',
'spelling_suggestion_no',
'spelling_suggestion_row',
'viewthread_post_attachmentthumb',
'viewthread_post_attachmentimage',
'viewthread_post_attachment',
'viewthread_post_nosig',
'viewthread_post_sig'
);

if ( X_GUEST ) {
    if ( ! coppa_check() ) {
        // User previously attempted registration with age < 13.
        message( $lang['coppa_fail'] );
    }
    $loggedin = '';
} else {
    eval('$loggedin = "'.template('post_loggedin').'";');
}

if ($self['ban'] == "posts" || $self['ban'] == "both") {
    message( $lang['textbanfrompost'] );
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
        header('HTTP/1.0 404 Not Found');
        error($lang['textnothread']);
    }
    $forum = $db->fetch_array($query);
    $db->free_result($query);
    $fid = (int) $forum['fid'];
    $tid = (int) $forum['tid'];
} else if ($action == 'reply') {
    $tid = getRequestInt('tid');
    $repquote = getInt('repquote');
    $query = $db->query("SELECT f.* FROM ".X_PREFIX."threads AS t LEFT JOIN ".X_PREFIX."forums AS f USING (fid) WHERE t.tid=$tid");
    if ($db->num_rows($query) != 1) {
        header('HTTP/1.0 404 Not Found');
        error($lang['textnothread']);
    }
    $forum = $db->fetch_array($query);
    $db->free_result($query);
    $fid = (int) $forum['fid'];
} else if ($action == 'newthread') {
    $fid = getRequestInt('fid');
    $forum = getForum($fid);
    if ($forum === FALSE) {
        header('HTTP/1.0 404 Not Found');
        error($lang['textnoforum']);
    }
} else {
    header('HTTP/1.0 404 Not Found');
    error($lang['textnoaction']);
}

if (($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on') {
    header('HTTP/1.0 404 Not Found');
    error($lang['textnoforum']);
}

smcwcache();

if ($tid > 0) {
    $query = $db->query("SELECT * FROM ".X_PREFIX."threads WHERE tid=$tid");
    if ($db->num_rows($query) != 1) {
        header('HTTP/1.0 404 Not Found');
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
    $sql_username = 'Anonymous';
    $username = 'Anonymous';
} else {
    $sql_username = $xmbuser;
    $username = $self['username'];
}

validatePpp();

$poll = postedVar('poll', '', FALSE, FALSE, FALSE, 'g');
if ($poll != 'yes') {
    $poll = '';
}

// check permissions on this forum (and top forum if it's a sub?)
$perms = checkForumPermissions($forum);
if (!$perms[X_PERMS_VIEW]) {
    if (X_GUEST) {
        redirect("{$full_url}misc.php?action=login", 0);
        exit;
    } else {
        error($lang['privforummsg']);
    }
} else if (!$perms[X_PERMS_PASSWORD]) {
    handlePasswordDialog($fid);
}

// check posting permissions specifically
if ($action == 'newthread') {
    if (($poll == '' && !$perms[X_PERMS_THREAD]) || ($poll == 'yes' && !$perms[X_PERMS_POLL])) {
        if (X_GUEST) {
            redirect("{$full_url}misc.php?action=login", 0);
            exit;
        } else {
            error($lang['textnoaction']);
        }
    }
} else if ($action == 'reply') {
    if (!$perms[X_PERMS_REPLY]) {
        if (X_GUEST) {
            redirect("{$full_url}misc.php?action=login", 0);
            exit;
        } else {
            error($lang['textnoaction']);
        }
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
    if (!$fupPerms[X_PERMS_VIEW]) {
        if (X_GUEST) {
            redirect("{$full_url}misc.php?action=login", 0);
            exit;
        } else {
            error($lang['privforummsg']);
        }
    } else if (!$fupPerms[X_PERMS_PASSWORD]) {
        error($lang['privforummsg']);     // do not show password-dialog here; it makes the situation too complicated
    } else if ( (int) $fup['fup'] > 0 ) {
        $fupup = getForum($fup['fup']);
        nav('<a href="index.php?gid='.$fup['fup'].'">'.fnameOut($fupup['name']).'</a>');
        unset($fupup);
    }
    nav('<a href="forumdisplay.php?fid='.$fup['fid'].'">'.fnameOut($fup['name']).'</a>');
} else if ( (int) $forum['fup'] > 0 ) { // 'forum' in a 'group'
    $fup = getForum($forum['fup']);
    nav('<a href="index.php?gid='.$fup['fid'].'">'.fnameOut($fup['name']).'</a>');
}
nav('<a href="forumdisplay.php?fid='.$fid.'">'.fnameOut($forum['name']).'</a>');

// Search-link
$searchlink = makeSearchLink($forum['fid']);

// Moderation of new users
if ( X_STAFF || 'off' == $SETTINGS['quarantine_new_users'] ) {
    // Default immunity
    $quarantine = false;
} else {
    $quarantine = true;
    if ( X_MEMBER ) {
        if ( 'yes' == $self['waiting_for_mod'] ) {
            // Member is already flagged for quarantine.
        } elseif ( (int) $self['postnum'] > 0 ) {
            // Member has posted before and is immune.
            $quarantine = false;
        } else {
            // Member has not posted before and will be flagged for quarantine starting now.
            \XMB\SQL\startMemberQuarantine( (int) $self['uid'] );
        }
    } else {
        // Guests have no immunity.
    }
}

if (!ini_get('file_uploads')) {
    $forum['attachstatus'] = 'off';
} elseif ($forum['attachstatus'] == 'on') {
    require 'include/attach.inc.php';
    $maxsize = \XMB\Attach\getSizeFormatted( min( phpShorthandValue( 'upload_max_filesize' ), (int) $SETTINGS['maxattachsize'] ) );
    $attachlimits = " {$lang['attachmaxsize']} $maxsize.  {$lang['attachmaxdims']} {$SETTINGS['max_image_size']}.";
}

$sql_posticon = postedVar('posticon', 'javascript', TRUE, TRUE, TRUE);
$posticon = postedVar( 'posticon', 'javascript', true, false, true );
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

    if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on') {
        require ROOT.'include/captcha.inc.php';
    }
}

$allowimgcode = ($forum['allowimgcode'] == 'yes' && $forum['allowbbcode'] == 'yes') ? $lang['texton'] : $lang['textoff'];
$allowhtml = $lang['textoff'];
$allowsmilies = ($forum['allowsmilies'] == 'yes') ? $lang['texton'] : $lang['textoff'];
$allowbbcode = ($forum['allowbbcode'] == 'yes') ? $lang['texton'] : $lang['textoff'];

$bbcodeoff = formYesNo('bbcodeoff');
if ( X_MEMBER ) {
    $emailnotify = formYesNo('emailnotify');
    if ( $emailnotify != 'yes' ) {
        $emailnotify = $self['sub_each_post'];
    }
} else {
    $emailnotify = 'no';
}
$smileyoff = formYesNo('smileyoff');
$usesig = formYesNo('usesig');

$codeoffcheck = ($bbcodeoff == 'yes') ? $cheHTML : '';
$emailnotifycheck = ($emailnotify == 'yes') ? $cheHTML : '';
$smileoffcheck = ($smileyoff == 'yes') ? $cheHTML : '';

// New bool vars to clear up the confusion about effective settings.
$bBBcodeInserterEnabled = ($SETTINGS['bbinsert'] == 'on' && $forum['allowbbcode'] == 'yes');
$bBBcodeOnForThisPost = ($forum['allowbbcode'] == 'yes' && $bbcodeoff == 'no');
$bIMGcodeOnForThisPost = ($bBBcodeOnForThisPost && $forum['allowimgcode'] == 'yes');
$bSmilieInserterEnabled = ($SETTINGS['smileyinsert'] == 'on' && $forum['allowsmilies'] == 'yes');
$bSmiliesOnForThisPost = ($forum['allowsmilies'] == 'yes' && $smileyoff == 'no');

if (isset($subaction) && $subaction == 'spellcheck' && (isset($spellchecksubmit) || isset($updates_submit))) {
    $sc = TRUE;
} else {
    $sc = FALSE;
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

$topcheck = '';
$closecheck = '';
$toptopic = 'no';
$closetopic = 'no';
if ( X_STAFF ) {
    $toptopic = formYesNo( 'toptopic' );
    $closetopic = formYesNo( 'closetopic' );
    
    if ( 'yes' == $toptopic ) {
        $topcheck = $cheHTML;
    }
    if ( 'yes' == $closetopic ) {
        $closecheck = $cheHTML;
    }
}

$messageinput = postedVar('message', '', TRUE, FALSE);  //postify() is responsible for DECODING if html is allowed.
$subjectinput = postedVar('subject', 'javascript', TRUE, FALSE, TRUE);
$subjectinput = str_replace(array("\r", "\n"), array('', ''), $subjectinput);

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

$bbcodeinsert = '';
$bbcodescript = '';
$moresmilies = '';
$smilieinsert = '';
if ($bBBcodeInserterEnabled || $bSmilieInserterEnabled) {
    eval('$bbcodescript = "'.template('functions_bbcode').'";');
    if ($bBBcodeInserterEnabled) {
        $mode0check = '';
        $mode1check = '';
        $mode2check = '';
        $mode = isset($mode) ? formInt('mode') : 2;
        switch($mode) {
        case 0:
            $mode0check = $cheHTML;
            $setbbcodemode = 'advmode=true;normalmode=false;';
            break;
        case 1:
            $mode1check = $cheHTML;
            $setbbcodemode = 'helpmode=true;normalmode=false;';
            break;
        default:
            $mode2check = $cheHTML;
            $setbbcodemode = '';
            break;
        }
        eval('$bbcodeinsert = "'.template('functions_bbcodeinsert').'";'); // Uses $spelling_lang
    }
    if ($bSmilieInserterEnabled) {
        $smilieinsert = smilieinsert();
        $moresmilies = "<a href=\"misc.php?action=smilies\" onclick=\"Popup(this.href, 'Window', 175, 250); return false;\">[{$lang['moresmilies']}]</a>";
    }
}

switch($action) {
    case 'reply':
        nav('<a href="viewthread.php?tid='.$tid.'">'.$threadname.'</a>');
        nav($lang['textreply']);

        if ($SETTINGS['subject_in_title'] == 'on') {
            $threadSubject = '- '.$threadname;
        }

        eval('$header = "'.template('header').'";');

        $replyvalid = onSubmit('replysubmit'); // This new flag will indicate a message was submitted and successful.

        if ($forum['attachstatus'] == 'on' && X_MEMBER) {
            for ($i=1; $i<=$SETTINGS['filesperpost']; $i++) {
                if (isset($_FILES['attach'.$i])) {
                    $result = \XMB\Attach\uploadedFile( 'attach'.$i, 0, $quarantine );
                    if ($result < 0 && $result != X_EMPTY_UPLOAD) {
                        $errors .= softerror($attachmentErrors[$result]);
                        $replyvalid = FALSE;
                    }
                }
            }
            $aid_list = \XMB\SQL\getOrphanedAttachmentIDs( (int) $self['uid'], $quarantine );
            $result = \XMB\Attach\doEdits( $deletes, $aid_list, 0, $quarantine );
            if ($result < 0) {
                $errors .= softerror($attachmentErrors[$result]);
                $replyvalid = FALSE;
            }
            foreach($deletes as $aid) {
                $messageinput = str_replace("[file]{$aid}[/file]", '', $messageinput);
            }
            if ($SETTINGS['attach_remote_images'] == 'on' && $bIMGcodeOnForThisPost) {
                $result = \XMB\Attach\remoteImages( 0, $messageinput, $quarantine );
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
        if (!X_SADMIN && $thread['closed'] != '') {
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
                if ($SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on') {
                    $Captcha = new Captcha();
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
            if (strlen($subjectinput) == 0 && strlen($messageinput) == 0) {
                $errors .= softerror($lang['postnothing']);
                $replyvalid = FALSE;
            }
        }

        if ($replyvalid) {
            if ($posticon != '') {
                $query = $db->query("SELECT id FROM ".X_PREFIX."smilies WHERE type='picon' AND url='$sql_posticon'");
                if ($db->num_rows($query) == 0) {
                    $sql_posticon = '';
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
                if ( $rightnow <= (int) $lastpost[0] && $username === $lastpost[1] ) {
                    $floodlink = "<a href=\"viewthread.php?fid=$fid&tid=$tid\">Click here</a>";
                    $errmsg = $lang['floodprotect'].' '.$floodlink.' '.$lang['tocont'];
                    $errors .= softerror($errmsg);
                    $replyvalid = FALSE;
                }
            }
        }

        if ($replyvalid) {
            $thatime = $onlinetime;
            if ($bBBcodeOnForThisPost) {
                postLinkBBcode($messageinput);
            }

            $dbmessage = addslashes($messageinput); //The message column is historically double-quoted.
            $dbsubject = addslashes($subjectinput);

            if (strlen($dbmessage) > 65535 || strlen($dbsubject) > 255) {
                // Inputs are suspiciously long.  Has the schema been customized?
                $query = $db->query("SELECT message, subject FROM ".X_PREFIX."posts WHERE 1=0");
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

            $values = [
                'fid' => (int) $fid,
                'tid' => (int) $tid,
                'dateline' => $onlinetime,
                'author' => $username,
                'message' => $dbmessage,
                'subject' => $dbsubject,
                'icon' => $posticon,
                'usesig' => $usesig,
                'useip' => $onlineip,
                'bbcodeoff' => $bbcodeoff,
                'smileyoff' => $smileyoff,
            ];

            $pid = \XMB\SQL\addPost( $values, $quarantine );

            $moderator = (modcheck($username, $forum['moderator']) == 'Moderator');
            if ($moderator && $closetopic == 'yes') {
                $db->query("UPDATE ".X_PREFIX."threads SET closed='yes' WHERE tid='$tid' AND fid='$fid'");
            }

            if ( ! $quarantine ) {
                // Update stats
                $db->query("UPDATE ".X_PREFIX."threads SET lastpost='$thatime|$sql_username|$pid', replies=replies+1 WHERE tid=$tid");

                $where = "WHERE fid=$fid";
                if ($forum['type'] == 'sub') {
                    $where .= " OR fid={$forum['fup']}";
                }
                $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$thatime|$sql_username|$pid', posts=posts+1 $where");
                unset($where);

                if ( X_MEMBER ) {
                    \XMB\SQL\raisePostCount( $username, $onlinetime );
                    $expire = $onlinetime + X_ONLINE_TIMER;
                    if ( empty( $oldtopics ) ) {
                        $oldtopics = "|$pid|";
                    } else {
                        $oldtopics .= "$pid|";
                    }
                    put_cookie( 'oldtopics', $oldtopics, $expire );
                }

                // Send subscription notifications
                $query = $db->query("SELECT COUNT(*) FROM ".X_PREFIX."posts WHERE pid <= $pid AND tid='$tid'");
                $posts = $db->result($query,0);
                $db->free_result($query);

                $lang2 = loadPhrases(array('charset','textsubsubject','textsubbody'));
                $viewperm = getOneForumPerm($forum, X_PERMS_RAWVIEW);

                $query = $db->query("SELECT dateline FROM ".X_PREFIX."posts WHERE tid = $tid AND pid < $pid ORDER BY dateline DESC LIMIT 1");
                if ($db->num_rows($query) > 0) {
                    $date = $db->result($query, 0);
                } else {
                    // Replying to a thread that has zero posts.
                    $date = '0';
                }
                $db->free_result($query);

                $subquery = $db->query("SELECT m.email, m.lastvisit, m.ppp, m.status, m.langfile "
                                     . "FROM ".X_PREFIX."favorites f "
                                     . "INNER JOIN ".X_PREFIX."members m USING (username) "
                                     . "WHERE f.type = 'subscription' AND f.tid = $tid AND m.username != '$sql_username' AND m.lastvisit >= $date");
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
                    $title = "$rawsubject ({$translate['textsubsubject']})";
                    $body = "$rawusername {$translate['textsubbody']} \n$threadurl";
                    xmb_mail( $rawemail, $title, $body, $translate['charset'] );
                }
                $db->free_result($subquery);
            }

            if ( 'yes' == $emailnotify ) {
                \XMB\SQL\addFavoriteIfMissing( (int) $tid, $username, 'subscription' );
            }

            if ($forum['attachstatus'] == 'on') {
                if ($attachSkipped) {
                    for ($i=1; $i<=$SETTINGS['filesperpost']; $i++) {
                        if (isset($_FILES['attach'.$i])) {
                            \XMB\Attach\uploadedFile( 'attach'.$i, $pid, $quarantine );
                        }
                    }
                    if ($SETTINGS['attach_remote_images'] == 'on' && $bIMGcodeOnForThisPost) {
                        \XMB\Attach\remoteImages( $pid, $messageinput, $quarantine );
                        $newdbmessage = addslashes($messageinput);
                        if ( $newdbmessage !== $dbmessage ) { // Anonymous message was modified after save, in order to use the pid.
                            \XMB\SQL\savePostBody( $pid, $newdbmessage, $quarantine );
                        }
                    }
                } elseif ( X_MEMBER ) {
                    \XMB\SQL\claimOrphanedAttachments( $pid, (int) $self['uid'], $quarantine );
                }
            }

            if ( $quarantine ) {
                message( $lang['moderation_hold'] );
            } else {
                $topicpages = quickpage($posts, $ppp);
                $topicpages = ($topicpages == 1) ? '' : '&page='.$topicpages;
                message($lang['replymsg'], TRUE, '', '', $full_url."viewthread.php?tid={$tid}{$topicpages}#pid{$pid}", true, false, true);
            }
        }

        if (!$replyvalid) {
            if ( $repquote > 0 ) {
                $query = $db->query("SELECT p.message, p.tid, p.fid, p.author FROM ".X_PREFIX."posts p WHERE p.pid=$repquote");
                $thaquote = $db->fetch_array($query);
                $db->free_result($query);
                $quoteperms = checkForumPermissions(getForum($thaquote['fid']));
                if ($quoteperms[X_PERMS_VIEW] && $quoteperms[X_PERMS_PASSWORD]) {
                    $thaquote['message'] = preg_replace('@\\[file\\]\\d*\\[/file\\]@', '', $thaquote['message']); //These codes will not work inside quotes.
                    $quoteblock = rawHTMLmessage(stripslashes($thaquote['message'])); //Messages are historically double-quoted.
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
            $files = array();
            if ($forum['attachstatus'] == 'on' && X_MEMBER) {
                $attachfile = '';
                $orphans = \XMB\SQL\getOrphanedAttachments( (int) $self['uid'], $quarantine );
                $counter = 0;
                foreach ( $orphans as $postinfo ) {
                    $files[] = $postinfo;
                    $postinfo['filename'] = attrOut($postinfo['filename']);
                    $postinfo['filesize'] = number_format($postinfo['filesize'], 0, '.', ',');
                    eval('$attachfile .= "'.template('post_attachment_orphan').'";');
                    if ($bBBcodeOnForThisPost) {
                        $bbcode = "[file]{$postinfo['aid']}[/file]";
                        if (strpos($messageinput, $bbcode) === FALSE) {
                            if ($counter == 0 || $postinfo['img_size'] == '' || $prevsize = '' || $SETTINGS['attachimgpost'] == 'off') {
                                $messageinput .= "\r\n\r\n";
                            }
                            $messageinput .= ' '.$bbcode; // Use a leading space to prevent awkward line wraps.
                            $counter++;
                            $prevsize = $postinfo['img_size'];
                        }
                    }
                }
                $maxuploads = (int) $SETTINGS['filesperpost'] - count( $orphans );
                if ($maxuploads > 0) {
                    $max_dos_limit = (int) ini_get('max_file_uploads');
                    if ($max_dos_limit > 0) $maxuploads = min($maxuploads, $max_dos_limit);
                    $max_dos_size = phpShorthandValue('post_max_size');
                    $max_xmb_size = (int) $SETTINGS['filesperpost'] * (int) $SETTINGS['maxattachsize'];
                    $maxtotal = ( 0 == $max_dos_size ) ? $max_xmb_size : min( $max_dos_size, $max_xmb_size );
                    $lang['attachmaxtotal'] .= ' '.\XMB\Attach\getSizeFormatted($maxtotal);
                    eval('$attachfile .= "'.template("post_attachmentbox").'";');
                }
                unset( $orphans );
            }

            //Allow sanitized message to pass-through to template in case of: #1 preview, #2 post error
            $subject = rawHTMLsubject($subjectinput);
            $message = rawHTMLmessage($messageinput);

            if (isset($previewpost)) {
                if ($posticon != '') {
                    $thread['icon'] = "<img src=\"$smdir/$posticon\" />";
                } else {
                    $thread['icon'] = '';
                }
                $currtime = $onlinetime + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600);
                $date = gmdate($dateformat, $currtime);
                $time = gmdate($timecode, $currtime);
                $poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;
                if (strlen($subject) > 0) {
                    $dissubject = $subject.'<br />';
                }
                if ($bBBcodeOnForThisPost) {
                    postLinkBBcode($messageinput);
                }
                if (count($files) > 0) {
                    bbcodeFileTags( $messageinput, $files, 0, $bBBcodeOnForThisPost, $quarantine );
                }
                $message1 = postify($messageinput, $smileyoff, $bbcodeoff, $forum['allowsmilies'], 'no', $forum['allowbbcode'], $forum['allowimgcode']);

                if ($usesig == 'yes') {
                    $post['sig'] = postify($self['sig'], 'no', 'no', $forum['allowsmilies'], 'no', $SETTINGS['sigbbcode'], $forum['allowimgcode'], false);
                    eval('$message1 .= "'.template('viewthread_post_sig').'";');
                } else {
                    eval('$message1 .= "'.template('viewthread_post_nosig').'";');
                }

                eval('$preview = "'.template('post_preview').'";');
            }

            if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on') {
                $Captcha = new Captcha();
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

            if (modcheck($username, $forum['moderator']) == 'Moderator') {
                $closeoption = '<br /><input type="checkbox" name="closetopic" value="yes" '.$closecheck.' /> '.$lang['closemsgques'].'<br />';
            } else {
                $closeoption = '';
            }

            $querytop = $db->query("SELECT COUNT(*) FROM ".X_PREFIX."posts WHERE tid='$tid'");
            $replynum = (int) $db->result($querytop, 0);
            if ($replynum >= $ppp) {
                $threadlink = 'viewthread.php?fid='.$fid.'&tid='.$tid;
                $trevltmsg = str_replace( '$threadlink', $threadlink, $lang['evaltrevlt'] );
                eval('$posts .= "'.template('post_reply_review_toolong').'";');
            } else {
                $thisbg = $altbg1;
                $query = $db->query("SELECT * FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY dateline DESC");
                while($post = $db->fetch_array($query)) {
                    $currtime = $post['dateline'] + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600);
                    $date = gmdate($dateformat, $currtime);
                    $time = gmdate($timecode, $currtime);
                    $poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;

                    if ($post['icon'] != '') {
                        $post['icon'] = '<img src="'.$smdir.'/'.$post['icon'].'" alt="'.$lang['altpostmood'].'" border="0" />';
                    } else {
                        $post['icon'] = '<img src="'.$imgdir.'/default_icon.gif" alt="[*]" border="0" />';
                    }

                    $post['message'] = preg_replace('@\\[file\\]\\d*\\[/file\\]@', '', $post['message']); //These codes do not work in postify()
                    $post['message'] = postify(stripslashes($post['message']), $post['smileyoff'], $post['bbcodeoff'], $forum['allowsmilies'], 'no', $forum['allowbbcode'], $forum['allowimgcode']);
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

        if ($forum['attachstatus'] == 'on' && X_MEMBER) {
            for ($i=1; $i<=$SETTINGS['filesperpost']; $i++) {
                if (isset($_FILES['attach'.$i])) {
                    $result = \XMB\Attach\uploadedFile( 'attach'.$i, 0, $quarantine );
                    if ($result < 0 && $result != X_EMPTY_UPLOAD) {
                        $errors .= softerror($attachmentErrors[$result]);
                        $topicvalid = FALSE;
                    }
                }
            }
            $aid_list = \XMB\SQL\getOrphanedAttachmentIDs( (int) $self['uid'], $quarantine );
            $result = \XMB\Attach\doEdits( $deletes, $aid_list, 0, $quarantine );
            if ($result < 0) {
                $errors .= softerror($attachmentErrors[$result]);
                $topicvalid = FALSE;
            }
            foreach($deletes as $aid) {
                $messageinput = str_replace("[file]{$aid}[/file]", '', $messageinput);
            }
            if ($SETTINGS['attach_remote_images'] == 'on' && $bIMGcodeOnForThisPost) {
                $result = \XMB\Attach\remoteImages( 0, $messageinput, $quarantine );
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
                if ($SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on') {
                    $Captcha = new Captcha();
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
            if (strlen($subjectinput) == 0) {
                $errors .= softerror($lang['textnosubject']);
                $topicvalid = FALSE;
            }
        }

        if ($topicvalid) {
            if ($posticon != '') {
                $query = $db->query("SELECT id FROM ".X_PREFIX."smilies WHERE type='picon' AND url='$sql_posticon'");
                if ($db->num_rows($query) == 0) {
                    $sql_posticon = '';
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
                if ( $rightnow <= (int) $lastpost[0] && $username === $lastpost[1] ) {
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

            if ($bBBcodeOnForThisPost) {
                postLinkBBcode($messageinput);
            }
            $dbmessage = addslashes($messageinput); //The message column is historically double-quoted.
            $dbsubject = addslashes($subjectinput);
            $dbtsubject = $dbsubject;

            if (strlen($dbmessage) > 65535 || strlen($dbsubject) > 128) {
                // Inputs are suspiciously long.  Has the schema been customized?
                $query = $db->query("SELECT message, subject FROM ".X_PREFIX."posts WHERE 1=0");
                $msgmax = $db->field_len($query, 0);
                $submax = $db->field_len($query, 1);
                $db->free_result($query);
                if (strlen($dbmessage) > $msgmax) {
                    $dbmessage = substr($dbmessage, 0, $msgmax);
                }
                if (strlen($dbsubject) > $submax) {
                    $dbsubject = substr($dbsubject, 0, $submax);
                }

                $query = $db->query("SELECT subject FROM ".X_PREFIX."threads WHERE 1=0");
                $tsubmax = $db->field_len($query, 0);
                $db->free_result($query);
                if (strlen($dbtsubject) > $tsubmax) {
                    $dbtsubject = substr($dbtsubject, 0, $tsubmax);
                }
            }
            
            $lastpost = "$thatime|$username";
            $closed = '';
            $topped = 0;
            $dbpollopts = ( 'yes' == $poll ) ? 1 : 0;

            if ( X_MEMBER ) {
                $moderator = (modcheck($username, $forum['moderator']) == 'Moderator');
                if ( $moderator ) {
                    if ( 'yes' == $closetopic ) {
                        // Be careful here; threads.closed is historically yes/moved/empty rather than yes/no.
                        $closed = 'yes';
                    }
                    if ( $toptopic == 'yes' ) {
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

            $tid = \XMB\SQL\addThread( $values, $quarantine );

            $values = [
                'fid' => (int) $fid,
                'tid' => $tid,
                'dateline' => $onlinetime,
                'author' => $username,
                'message' => $dbmessage,
                'subject' => $dbsubject,
                'icon' => $posticon,
                'usesig' => $usesig,
                'useip' => $onlineip,
                'bbcodeoff' => $bbcodeoff,
                'smileyoff' => $smileyoff,
            ];

            $pid = \XMB\SQL\addPost( $values, $quarantine, $quarantine ); // 3rd arg signals that this is not a reply.

            $lastpost .= "|$pid";
            \XMB\SQL\setThreadLastpost( $tid, $lastpost, $quarantine );

            if ( ! $quarantine ) {
                $where = "WHERE fid=$fid";
                if ($forum['type'] == 'sub') {
                    $where .= " OR fid={$forum['fup']}";
                }
                $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$thatime|$sql_username|$pid', threads=threads+1, posts=posts+1 $where");
                unset($where);
            }

            if ($poll == 'yes') {
                // Create a poll ID.  Works like a junction table even though we only support one poll per thread.
                $dbsubject = addslashes($subjectinput);
                $vote_id = \XMB\SQL\addVoteDesc( $tid, $quarantine );
                
                // Create poll options.  This is the part we care about.
                $options = [];
                $i = 1;
                foreach($pollopts as $p) {
                    $options[] = [
                        'vote_id' => $vote_id,
                        'vote_option_id' => $i++,
                        'vote_option_text' => $p,
                    ];
                }
                \XMB\SQL\addVoteOptions( $options, $quarantine );
            }

            if ( X_MEMBER ) {
                if ($emailnotify == 'yes') {
                    \XMB\SQL\addFavoriteIfMissing( (int) $tid, $username, 'subscription', $quarantine );
                }

                if ( ! $quarantine ) {
                    \XMB\SQL\raisePostCount( $username, $onlinetime );
                    $expire = $onlinetime + X_ONLINE_TIMER;
                    if ( empty( $oldtopics ) ) {
                        $oldtopics = "|$pid|";
                    } else {
                        $oldtopics .= "$pid|";
                    }
                    put_cookie( 'oldtopics', $oldtopics, $expire );
                }
            }

            if ($forum['attachstatus'] == 'on') {
                if ($attachSkipped) {
                    for ($i=1; $i<=$SETTINGS['filesperpost']; $i++) {
                        if (isset($_FILES['attach'.$i])) {
                            \XMB\Attach\uploadedFile( 'attach'.$i, $pid, $quarantine );
                        }
                    }
                    if ($SETTINGS['attach_remote_images'] == 'on' && $bIMGcodeOnForThisPost) {
                        \XMB\Attach\remoteImages( $pid, $messageinput, $quarantine );
                        $newdbmessage = addslashes($messageinput);
                        if ( $newdbmessage !== $dbmessage ) { // Anonymous message was modified after save, in order to use the pid.
                            \XMB\SQL\savePostBody( $pid, $newdbmessage, $quarantine );
                        }
                    }
                } elseif ( X_MEMBER ) {
                    \XMB\SQL\claimOrphanedAttachments( $pid, (int) $self['uid'], $quarantine );
                }
            }

            if ( $quarantine ) {
                message( $lang['moderation_hold'] );
            } else {
                $query = $db->query("SELECT COUNT(*) FROM ".X_PREFIX."posts WHERE tid='$tid'");
                $posts = $db->result($query, 0);
                $db->free_result($query);

                $topicpages = quickpage($posts, $ppp);
                $topicpages = ($topicpages == 1) ? '' : '&page='.$topicpages;
                message($lang['postmsg'], TRUE, '', '', $full_url."viewthread.php?tid={$tid}{$topicpages}#pid{$pid}", true, false, true);
            }
        }

        if (!$topicvalid) {
            // Fill $attachfile
            $files = array();
            if ($forum['attachstatus'] == 'on' && X_MEMBER) {
                $attachfile = '';
                $orphans = \XMB\SQL\getOrphanedAttachments( (int) $self['uid'], $quarantine );
                $counter = 0;
                foreach ( $orphans as $postinfo ) {
                    $files[] = $postinfo;
                    $postinfo['filename'] = attrOut($postinfo['filename']);
                    $postinfo['filesize'] = number_format($postinfo['filesize'], 0, '.', ',');
                    eval('$attachfile .= "'.template('post_attachment_orphan').'";');
                    if ($bBBcodeOnForThisPost) {
                        $bbcode = "[file]{$postinfo['aid']}[/file]";
                        if (strpos($messageinput, $bbcode) === FALSE) {
                            if ($counter == 0 || $postinfo['img_size'] == '' || $prevsize == '' || $SETTINGS['attachimgpost'] == 'off') {
                                $messageinput .= "\r\n\r\n";
                            }
                            $messageinput .= ' '.$bbcode; // Use a leading space to prevent awkward line wraps.
                            $counter++;
                            $prevsize = $postinfo['img_size'];
                        }
                    }
                }
                $maxuploads = (int) $SETTINGS['filesperpost'] - count( $orphans );
                if ($maxuploads > 0) {
                    $max_dos_limit = (int) ini_get('max_file_uploads');
                    if ($max_dos_limit > 0) $maxuploads = min($maxuploads, $max_dos_limit);
                    $max_dos_size = phpShorthandValue('post_max_size');
                    $max_xmb_size = (int) $SETTINGS['filesperpost'] * (int) $SETTINGS['maxattachsize'];
                    $maxtotal = ( 0 == $max_dos_size ) ? $max_xmb_size : min( $max_dos_size, $max_xmb_size );
                    $lang['attachmaxtotal'] .= ' '.\XMB\Attach\getSizeFormatted($maxtotal);
                    eval('$attachfile .= "'.template("post_attachmentbox").'";');
                }
                unset( $orphans );
            }

            //Allow sanitized message to pass-through to template in case of: #1 preview, #2 post error
            $subject = rawHTMLsubject($subjectinput);
            $message = rawHTMLmessage($messageinput);

            if (isset($previewpost)) {
                if ($posticon != '') {
                    $thread['icon'] = "<img src=\"$smdir/$posticon\" />";
                } else {
                    $thread['icon'] = '';
                }
                $currtime = $onlinetime + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600);
                $date = gmdate($dateformat, $currtime);
                $time = gmdate($timecode, $currtime);
                $poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;
                if (strlen($subject) > 0) {
                    $dissubject = $subject.'<br />';
                }
                if ($bBBcodeOnForThisPost) {
                    postLinkBBcode($messageinput);
                }
                if (count($files) > 0) {
                    bbcodeFileTags( $messageinput, $files, 0, $bBBcodeOnForThisPost, $quarantine );
                }
                $message1 = postify($messageinput, $smileyoff, $bbcodeoff, $forum['allowsmilies'], 'no', $forum['allowbbcode'], $forum['allowimgcode']);

                if ($usesig == 'yes') {
                    $post['sig'] = postify($self['sig'], 'no', 'no', $forum['allowsmilies'], 'no', $SETTINGS['sigbbcode'], $forum['allowimgcode'], false);
                    eval('$message1 .= "'.template('viewthread_post_sig').'";');
                } else {
                    eval('$message1 .= "'.template('viewthread_post_nosig').'";');
                }

                eval('$preview = "'.template('post_preview').'";');
            }

            if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on') {
                $Captcha = new Captcha();
                if ($Captcha->bCompatible !== false) {
                    $imghash = $Captcha->GenerateCode();
                    if ($SETTINGS['captcha_code_casesensitive'] == 'off') {
                        $lang['captchacaseon'] = '';
                    }
                    eval('$captchapostcheck = "'.template('post_captcha').'";');
                }
                unset($Captcha);
            }

            if (modcheck($username, $forum['moderator']) == 'Moderator') {
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

        if ( $status1 != 'Moderator' && ( $self['username'] !== $orig['author'] || $thread['closed'] != '' ) ) {
            $errors .= softerror($lang['noedit']);
            $editvalid = FALSE;
        }

        if ($editvalid) {
            if ($forum['attachstatus'] == 'on') {
                for ($i=1; $i<=$SETTINGS['filesperpost']; $i++) {
                    if (isset($_FILES['attach'.$i])) {
                        $result = \XMB\Attach\uploadedFile('attach'.$i, $pid);
                        if ($result < 0 && $result != X_EMPTY_UPLOAD) {
                            $errors .= softerror($attachmentErrors[$result]);
                            $editvalid = FALSE;
                        }
                    }
                }
                $children = false;
                $aid_list = \XMB\SQL\getAttachmentIDsByPost( $pid, $children );
                $result = \XMB\Attach\doEdits( $deletes, $aid_list, $pid );
                if ($result < 0) {
                    $errors .= softerror($attachmentErrors[$result]);
                    $editvalid = FALSE;
                }
                foreach($deletes as $aid) {
                    $messageinput = str_replace("[file]{$aid}[/file]", '', $messageinput);
                }
                $temp = '';
                if ($SETTINGS['attach_remote_images'] == 'on' && $bIMGcodeOnForThisPost) {
                    $result = \XMB\Attach\remoteImages($pid, $messageinput);
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
                $query = $db->query("SELECT id FROM ".X_PREFIX."smilies WHERE type='picon' AND url='$sql_posticon'");
                if ($db->num_rows($query) == 0) {
                    $sql_posticon = '';
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

            if ( ( strlen($subjectinput) == 0 && $pid == (int) $isfirstpost['pid'] ) && ! ( isset($delete) && $delete == 'yes' ) ) {
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

                if ($bBBcodeOnForThisPost) {
                    postLinkBBcode($messageinput);
                }
                $dbmessage = addslashes($messageinput); //The message column is historically double-quoted.
                $dbsubject = addslashes($subjectinput);

                if (strlen($dbmessage) > 65535 || strlen($dbsubject) > 255) {
                    // Inputs are suspiciously long.  Has the schema been customized?
                    $query = $db->query("SELECT message, subject FROM ".X_PREFIX."posts WHERE 1=0");
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

                if ( (int) $isfirstpost['pid'] == $pid ) {
                    $db->query("UPDATE ".X_PREFIX."threads SET icon='$sql_posticon', subject='$dbsubject' WHERE tid=$tid");
                }

                $db->query("UPDATE ".X_PREFIX."posts SET message='$dbmessage', usesig='$usesig', bbcodeoff='$bbcodeoff', smileyoff='$smileyoff', icon='$sql_posticon', subject='$dbsubject' WHERE pid=$pid");
            } else {
                require_once('include/attach.inc.php');
                $db->query("DELETE FROM ".X_PREFIX."posts WHERE pid=$pid");
                if ($orig['author'] != 'Anonymous') {
                    $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-1 WHERE username='".$db->escape($orig['author'])."'");
                }
                \XMB\Attach\deleteByPost($pid);

                if ( (int) $isfirstpost['pid'] == $pid ) {
                    $query = $db->query("SELECT COUNT(*) AS pcount FROM ".X_PREFIX."posts WHERE tid=$tid");
                    $numrows = $db->fetch_array($query);
                    $numrows = (int) $numrows['pcount'];
                    $db->free_result($query);

                    if ($numrows == 0) {
                        $threaddelete = 'yes';
                        $db->query("DELETE FROM ".X_PREFIX."favorites WHERE tid='$tid'");

                        $db->query("DELETE FROM d, r, v "
                                 . "USING ".X_PREFIX."vote_desc AS d "
                                 . "LEFT JOIN ".X_PREFIX."vote_results AS r ON r.vote_id = d.vote_id "
                                 . "LEFT JOIN ".X_PREFIX."vote_voters AS v  ON v.vote_id = d.vote_id "
                                 . "WHERE d.topic_id = $tid");

                        $db->query("DELETE FROM ".X_PREFIX."threads WHERE tid=$tid OR closed='moved|$tid'");
                    } else {
                        $db->query("UPDATE ".X_PREFIX."posts SET subject='".$db->escape($orig['subject'])."' WHERE tid=$tid ORDER BY dateline LIMIT 1");
                    }
                }
                if ($forum['type'] == 'sub') {
                    updateforumcount($fup['fid']);
                }
                updatethreadcount($tid);
                updateforumcount($fid);
            }

            if ($threaddelete == 'no') {
                $query = $db->query("SELECT COUNT(*) FROM ".X_PREFIX."posts WHERE dateline <= {$orig['dateline']} AND tid=$tid");
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
            if (onSubmit('editsubmit') || isset($previewpost) || $sc) {
                $postinfo = array("usesig"=>$usesig, "bbcodeoff"=>$bbcodeoff, "smileyoff"=>$smileyoff, "message"=>$messageinput, "subject"=>$subjectinput, 'icon'=>$sql_posticon, 'dateline'=>$orig['dateline']);
            } else {
                $postinfo = $orig;
                $postinfo['message'] = stripslashes($postinfo['message']); //Messages are historically double-quoted.
                $postinfo['subject'] = stripslashes($postinfo['subject']);
                $bBBcodeOnForThisPost = ($forum['allowbbcode'] == 'yes' && $postinfo['bbcodeoff'] == 'no');
                $bIMGcodeOnForThisPost = ($bBBcodeOnForThisPost && $forum['allowimgcode'] == 'yes');
                $bSmiliesOnForThisPost = ($forum['allowsmilies'] == 'yes' && $postinfo['smileyoff'] == 'no');
            }

            // Fill $attachment
            $attachment = '';
            $files = array();
            if ($forum['attachstatus'] == 'on') {
                $query = $db->query("SELECT a.aid, a.pid, a.filename, a.filetype, a.filesize, a.downloads, a.img_size, thumbs.aid AS thumbid, thumbs.filename AS thumbname, thumbs.img_size AS thumbsize FROM ".X_PREFIX."attachments AS a LEFT JOIN ".X_PREFIX."attachments AS thumbs ON a.aid=thumbs.parentid WHERE a.pid=$pid AND a.parentid=0");
                $counter = 0;
                while ($attach = $db->fetch_array($query)) {
                    $files[] = $attach;
                    $postinfo['aid'] = $attach['aid'];
                    $postinfo['downloads'] = $attach['downloads'];
                    $postinfo['filename'] = attrOut($attach['filename']);
                    $postinfo['filesize'] = number_format($attach['filesize'], 0, '.', ',');
                    $postinfo['url'] = \XMB\Attach\getURL( (int) $attach['aid'], $pid, $attach['filename'] );
                    eval('$attachment .= "'.template('post_edit_attachment').'";');
                    if ($bBBcodeOnForThisPost) {
                        $bbcode = "[file]{$attach['aid']}[/file]";
                        if (strpos($postinfo['message'], $bbcode) === FALSE) {
                            if ($counter == 0 || $attach['img_size'] == '' || $prevsize = '' || $SETTINGS['attachimgpost'] == 'off') {
                                $postinfo['message'] .= "\r\n\r\n";
                            }
                            $postinfo['message'] .= ' '.$bbcode; // Use a leading space to prevent awkward line wraps.
                            $counter++;
                            $prevsize = $attach['img_size'];
                        }
                    }
                }
                $maxuploads = (int) $SETTINGS['filesperpost'] - $db->num_rows($query);
                if ($maxuploads > 0) {
                    $max_dos_limit = (int) ini_get('max_file_uploads');
                    if ($max_dos_limit > 0) $maxuploads = min($maxuploads, $max_dos_limit);
                    $max_dos_size = phpShorthandValue('post_max_size');
                    $max_xmb_size = (int) $SETTINGS['filesperpost'] * (int) $SETTINGS['maxattachsize'];
                    $maxtotal = ( 0 == $max_dos_size ) ? $max_xmb_size : min( $max_dos_size, $max_xmb_size );
                    $lang['attachmaxtotal'] .= ' '.\XMB\Attach\getSizeFormatted($maxtotal);
                    eval('$attachment .= "'.template("post_attachmentbox").'";');
                }
                $db->free_result($query);
            }

            //Allow sanitized message to pass-through to template in case of: #1 preview, #2 post error
            $subject = rawHTMLsubject($postinfo['subject']);
            $message = rawHTMLmessage($postinfo['message']);

            if (isset($previewpost)) {
                if ($postinfo['icon'] != '') {
                    $thread['icon'] = "<img src=\"$smdir/{$postinfo['icon']}\" />";
                } else {
                    $thread['icon'] = '';
                }
                $currtime = $postinfo['dateline'] + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600);
                $date = gmdate($dateformat, $currtime);
                $time = gmdate($timecode, $currtime);
                $poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;
                if (strlen($subject) > 0) {
                    $dissubject = $subject.'<br />';
                }
                $message1 = $postinfo['message'];
                if ($SETTINGS['editedby'] == 'on') {
                    $message1 .= "\n\n[".$lang['textediton'].' '.gmdate($dateformat).' '.$lang['textby']." $username]";
                }
                if ($bBBcodeOnForThisPost) {
                    postLinkBBcode($message1);
                }
                if (count($files) > 0) {
                    bbcodeFileTags($message1, $files, $pid, $bBBcodeOnForThisPost);
                }
                $message1 = postify($message1, $smileyoff, $bbcodeoff, $forum['allowsmilies'], 'no', $forum['allowbbcode'], $forum['allowimgcode']);

                if ($usesig == 'yes') {
                    $post['sig'] = postify($self['sig'], 'no', 'no', $forum['allowsmilies'], 'no', $SETTINGS['sigbbcode'], $forum['allowimgcode'], false);
                    eval('$message1 .= "'.template('viewthread_post_sig').'";');
                } else {
                    eval('$message1 .= "'.template('viewthread_post_nosig').'";');
                }

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
echo $header, $errors, $postpage, $footer;

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
        if ($perms[X_PERMS_VIEW] && $perms[X_PERMS_PASSWORD]) {
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
    return error($msg, FALSE, '', '<br />', FALSE, FALSE, TRUE, FALSE);
}
?>
