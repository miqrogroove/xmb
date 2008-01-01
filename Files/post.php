<?php
/* $Id: post.php,v 1.3.2.37 2006/09/24 18:52:49 Tularis Exp $ */
/*
    XMB 1.9.2
    © 2001 - 2005 Aventure Media & The XMB Development Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require './header.php';

/**
* bbcodeinsert() - return $bbcode populated with bbcode JS functions
*
* Returns $bbcode populated with bbcode JS functions if the feature is on
*
* @return   the required bbcode or a blank string if $bbinsert setting is not "on"
*/
function bbcodeinsert() {
    global $THEME, $SETTINGS, $lang, $bbinsert,$spelling_lang;
    
    $bbcode = '';
    if ($bbinsert == 'on') {
        eval("\$bbcode = \"".template("functions_bbcodeinsert")."\";");
    }
    return $bbcode;
}

loadtemplates('post_notloggedin','post_loggedin','post_preview','post_attachmentbox','post_newthread','post_reply_review_toolong','post_reply_review_post','post_reply','post_edit','functions_smilieinsert','functions_smilieinsert_smilie','functions_bbcodeinsert','forumdisplay_password', 'functions_bbcode', 'post_newpoll', 'post_edit_attachment');
eval("\$css = \"".template("css")."\";");
smcwcache();

$pid = (isset($pid) ? (int) $pid : 0);
$tid = (isset($tid) ? (int) $tid : 0);
$fid = (isset($fid) ? (int) $fid : 0);
$posterror = false;

validatePpp();

$thread = array();
$threadname = '';

if ($tid) {
    $query = $db->query("SELECT fid, subject FROM $table_threads WHERE tid=$tid LIMIT 1");
    if ( $db->num_rows($query) == 1) {
        $thread = $db->fetch_array($query);
        $threadname = $thread['subject'];
        $threadname = stripslashes($threadname);
        $fid = (int) $thread['fid'];
    } else {
        error($lang['textnothread']);
    }
}

$query = $db->query("SELECT * FROM $table_forums WHERE fid=$fid");
if($db->num_rows($query) == 0) {
    error($lang['textnoforum']);
}
$forum = $db->fetch_array($query);
$forum['name'] = stripslashes($forum['name']);

if (($fid == 0 && $tid == 0) || ($forum['type'] != 'forum' && $forum['type'] != 'sub')) {
    error($lang['textnoforum']);
}

// check permissions on this forum (and top forum if it's a sub?)
$PERMISSIONS = checkForumPermissions($forum);
if(!$PERMISSIONS[X_PERMS_VIEW] || !$PERMISSIONS[X_PERMS_USERLIST]) {
    error($lang['privforummsg']);
} elseif(!$PERMISSIONS[X_PERMS_PASSWORD]) {
    handlePasswordDialog($fid, basename(__FILE__), $_GET);
}

// check posting permissions specifically
if(isset($action)) {
    if($action == 'newthread') {
        if(!$PERMISSIONS[X_PERMS_THREAD]) {
            error($lang['textnoaction']);
            
        } elseif(isset($poll) && $poll == 'yes') {
            if(!$PERMISSIONS[X_PERMS_POLL]) {
                error($lang['textnoaction']);
            }
            
        } else {
            // allowed to do whatever it is they're doing
        }
        
    } elseif($action == 'reply') {
        if(!$PERMISSIONS[X_PERMS_REPLY]) {
            error($lang['textnoaction']);
        } else {
            // allowed to post a reply!
        }
        
    } elseif($action == 'edit') {
        // let's allow edits for now, we'll check for permissions later on in the script
        
    } else {
        error($lang['textnoaction']);
    }
} else {
    error($lang['textnoaction']);
}

// check parent-forum permissions
if($forum['type'] == 'sub') {
    $fup = $db->fetch_array($db->query("SELECT postperm, userlist, password FROM $table_forums WHERE fid=$forum[fup]"));
    $fupPerms = checkForumPermissions($fup);
    
    if(!$fupPerms[X_PERMS_VIEW] || !$fupPerms[X_PERMS_USERLIST] || !$fupPerms[X_PERMS_PASSWORD]) {
        error($lang['privforummsg']);
    }
    // do not show password-dialog here; it makes the situation too complicated
}

// check ban-from-post
if ($self['ban'] == 'posts' || $self['ban'] == 'both') {
    error($lang['textbanfrompost']);
}

// add navigation links
if ($forum['type'] == "forum") {
    nav('<a href="forumdisplay.php?fid='.$fid.'">'.stripslashes($forum['name']) .'</a>');
} else {
    $query = $db->query("SELECT name, fid FROM $table_forums WHERE fid='$forum[fup]'");
    $fup = $db->fetch_array($query);
    nav('<a href="forumdisplay.php?fid='.$fup['fid'].'">'.stripslashes($fup['name']).'</a>');
    nav('<a href="forumdisplay.php?fid='.$fid.'">'.stripslashes($forum['name']).'</a>');
}

if(X_MEMBER) {
    eval("\$loggedin = \"".template("post_loggedin")."\";");
} else {
    $loggedin = '';
}

if($self['status'] == 'Moderator') {
    // need to check if it's a moderator for this specific forum
    $isModerator = false;
    $mods = explode(',', $forum['moderators']);
    $user = strtolower(trim($self['username']));
    foreach($mods as $mod) {
        if(strtolower(trim($mod)) == $user) {
            $isModerator = true;
            break;
        }
    }
} elseif(X_STAFF) {
    // all other staff, except for moderators
    $isModerator = true;
} else {
    $isModerator = false;
}

if ($forum['attachstatus'] != 'off') {
    eval('$attachfile = "'.template('post_attachmentbox').'";');
} else {
    $attachfile = '';
}

$listed_icons = 0;
$icons = '<input type="radio" name="posticon" value="" /> <img src="'.$THEME['imgdir'].'/default_icon.gif" alt="[*]" />';

if ( $action != 'edit') {
    if (!X_STAFF) {
        $querysmilie = $db->query("SELECT url, code FROM $table_smilies WHERE type='picon' AND (url NOT LIKE '%rsvd%')");
        while($smilie = $db->fetch_array($querysmilie)) {
            $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$THEME[smdir]/$smilie[url]\" alt=\"$smilie[code]\" />";
            $listed_icons += 1;
            if ( $listed_icons == 9) {
                $icons .= "<br />";
                $listed_icons = 0;
            }
        }
        $db->free_result($querysmilie);
    } else {
        $querysmilie = $db->query("SELECT url, code FROM $table_smilies WHERE type='picon'");
        while($smilie = $db->fetch_array($querysmilie)) {
            $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$THEME[smdir]/$smilie[url]\" alt=\"$smilie[code]\" />";
            $listed_icons += 1;
            if ( $listed_icons == 9) {
                $icons .= "<br />";
                $listed_icons = 0;
            }
        }
        $db->free_result($querysmilie);
    }
}

eval('$bbcodescript = "'.template('functions_bbcode').'";');

if(isset($usesig) && $usesig == 'yes') {
    $usesig = 'yes';
} else {
    $usesig = 'no';
}

$chkInputHTML = 'no';
$chkInputTags = 'no';
if ( isset($forum['allowhtml']) && $forum['allowhtml'] == 'yes' ) {
    $chkInputHTML = 'yes';
    $chkInputTags = 'no';
}

$allowimgcode   = ( isset($forum['allowimgcode']) && $forum['allowimgcode'] == "yes" )    ? $lang['texton'] : $lang['textoff'];
$allowhtml      = ( $chkInputHTML == 'yes' )                                              ? $lang['texton'] : $lang['textoff'];
$allowsmilies   = ( isset($forum['allowsmilies']) && $forum['allowsmilies'] == "yes" )    ? $lang['texton'] : $lang['textoff'];
$allowbbcode    = ( isset($forum['allowbbcode']) && $forum['allowbbcode'] == "yes" )      ? $lang['texton'] : $lang['textoff'];

// add all checks in case of preview
if (isset($smileyoff) && $smileyoff == 'yes') {
    $smileoffcheck = 'checked="checked"';
    $smileyoff = 'yes'; // unnecessery, but easier to spot
} else {
    $smileoffcheck = '';
    $smileyoff = 'no';
}

if (isset($bbcodeoff) && $bbcodeoff == 'yes') {
    $codeoffcheck = 'checked="checked"';
    $bbcodeoff = 'yes';
} else {
    $codeoffcheck = '';
    $bbcodeoff = 'no';
}

if (isset($emailnotify) && $emailnotify == 'yes') {
    $emailnotifycheck = 'checked="checked"';
    $emailnotify = 'yes';
} else {
    $emailnotifycheck = '';
    $emailnotify = 'no';
}

if (isset($subaction) && $subaction == 'spellcheck' && (isset($spellchecksubmit) || isset($spellcheckersubmit))) {
    $sc = true;
} else {
    $sc = false;
}

if ((isset($previewpost) || $sc) && isset($usesig) && $usesig == 'yes') {
    $usesigcheck = 'checked="checked"';
} elseif (isset($previewpost) || $sc) {
    $usesigcheck = '';
} elseif ( $self['sig'] != '') {
    $usesigcheck = 'checked="checked"';
} else {
    $usesigcheck = '';
}

if ($isModerator) {
    if (isset($toptopic) && $toptopic == 'yes') {
        $topcheck = 'checked="checked"';
    } else {
        $topcheck = '';
        $toptopic = 'no';
    }

    if (isset($closetopic) && $closetopic == 'yes') {
        $closecheck = 'checked="checked"';
    } else {
        $closecheck = '';
        $closetopic = 'no';
    }
} else {    // just in case
    $topcheck = '';
    $closecheck = '';
}

$repquote = isset($repquote) ? (int) $repquote : 0;
if ( isset($poll) ) {
    if ( $poll != "yes" ) {
        $poll = '';
    } else {
        $poll = 'yes';
    }
} else {
    $poll = '';
}

if ( !empty($posticon) ) {
    $thread['icon'] = (file_exists($THEME['smdir'].'/'.$posticon)) ? "<img src=\"$THEME[smdir]/$posticon\" />" : '';
    $icons = str_replace('<input type="radio" name="posticon" value="'.$posticon.'" />', '<input type="radio" name="posticon" value="'.$posticon.'" checked="checked" />', $icons);
}else{
    $thread['icon'] = '';
    $icons = str_replace('<input type="radio" name="posticon" value="" />', '<input type="radio" name="posticon" value="" checked="checked" />', $icons);
}

// Start temp-spellcheck //
if ( $SETTINGS['spellcheck'] == 'on') {
    $spelling_submit1 = '<input type="hidden" name="subaction" value="spellcheck" /><input type="submit" class="submit" name="spellchecksubmit" value="'.$lang['checkspelling'].'" />';
    $spelling_lang = '<select name="language"><option value="en" selected="selected">English</option></select>';

    if (isset($subaction) && $subaction == 'spellcheck' && (isset($spellchecksubmit) || isset($updates_submit))) {
        if (!$updates_submit) {
            $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
            $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);
            require './include/spelling.inc.php';
            $spelling = new spelling($language);
            $problems = $spelling->check_text($message);
            if (count($problems) > 0) {
                foreach ($problems as $orig=>$new) {
                    $mistake = array();
                    foreach ($new as $suggestion) {
                        eval("\$mistake[] = \"".template('spelling_suggestion_new')."\";");
                    }
                    $mistake = implode("\n", $mistake);
                    eval("\$suggest[] = \"".template('spelling_suggestion_row')."\";");
                }
                $suggestions = implode("\n", $suggest);
                eval("\$suggestions = \"".template('spelling_suggestion')."\";");
                $spelling_submit2 = '<input type="submit" class="submit" name="updates_submit" value="'.$lang['replace'].'" />';
            }else{
                eval("\$suggestions = \"".template('spelling_suggestion_no')."\";");
                $spelling_submit2 = '';
            }

        }else{
            foreach ($old_words as $word) {
                $message = str_replace($word, ${'replace_'.$word}, $message);
            }
            $spelling_submit2 = '';
        }
    }else{
        $suggestions = '';
        $spelling_submit2 = '';
    }
}else{
    $spelling_submit1 = '';
    $spelling_submit2 = '';
    $spelling_lang = '';
    $suggestions = '';
}
// End temp-spellcheck //

// check for thread-title
if(isset($topicsubmit)) {
    // check if subject is set
    if(!isset($subject) || trim($subject) == '') {
        $preview = error($lang['textnosubject'], false, '', '<br /><br />', false, false, true, false);
        $error = true;
        unset($topicsubmit);
        if(isset($previewpost)) {
            unset($previewpost);
        }
    } else {
        $preview = '';
        $error = false;
    }
} else {
    $error = false;
}

$bbcodeinsert = bbcodeinsert();
$smilieinsert = smilieinsert();

if (isset($previewpost)) {
    $date = printGmDate();
    $time = printGmTime();
    
    $poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;

    $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
    $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);
    $message1 = postify($message, $smileyoff, $bbcodeoff, $forum['allowsmilies'], $forum['allowhtml'], $forum['allowbbcode'], $forum['allowimgcode']);
    $dissubject = censor($subject);

    if ($pid > 0) {
        eval('$preview = stripslashes("'.template('post_preview').'");');
    } else {
        eval('$preview = "'.template('post_preview').'";');
    }
} elseif($error) {
    $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
    $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);
} else {
    $preview = '';
    $subject = (isset($subject) ? $subject : '');
    $message = (isset($message) ? $message : '');
}

if($action == 'newthread') {
    if (isset($poll) && $poll == 'yes') {
        nav($lang['textnewpoll']);
    } else {
        nav($lang['textpostnew']);
    }

    if(!isset($topicsubmit) || !$topicsubmit) {
        eval('echo "'.template('header').'";');

        if($isModerator) {
            $topoption      = "<br /><input type=\"checkbox\" name=\"toptopic\" value=\"yes\" $topcheck/> $lang[topmsgques]";
            $closeoption    = "<br /><input type=\"checkbox\" name=\"closetopic\" value=\"yes\" $closecheck/> $lang[closemsgques]<br />";
        } else {
            $topoption      = '';
            $closeoption    = '';
        }

        if(!isset($spelling_submit2)) {
            $spelling_submit2 = '';
        }

        if(isset($poll) && $poll == "yes") {
            eval('echo stripslashes("'.template('post_newpoll').'");');
        } else {
            eval('echo stripslashes("'.template('post_newthread').'");');
        }
    } else {
        if(X_GUEST) {
            $username = "Anonymous";
        } else {
            $username = $self['username'];
        }
        
        // check for flooding
        $query = $db->query("SELECT dateline FROM $table_posts WHERE author='$username' ORDER BY pid DESC LIMIT 1");
        if($db->num_rows($query) == 1) {
            $date = $db->result($query, 0);
            if($date > (time()-$SETTINGS['floodctrl'])) {
                error($lang['floodprotect']);
            }
        }

        // check for polls
        if(isset($pollanswers) && $PERMISSIONS[X_PERMS_POLL]) {
            $pollanswers = checkInput($pollanswers);
            if (strpos($pollanswers, '#|#') !== false || strpos($pollanswers, '||~|~||') !== false) {
                $pollanswers = '';
            }else{
                $pollops = explode("\n", $pollanswers);
                $pollanswers = '';
                $pnumnum = count($pollops);
                if ( $pnumnum < 2 && $pollanswers != '') {
                    error($lang['too_few_pollopts']);
                }
                for($pnum = 0; $pnum < $pnumnum; $pnum++) {
                    if ( $pollops[$pnum] != '') {
                        $pollanswers .= $pollops[$pnum].'||~|~|| 0#|#';
                    }
                }
                $pollanswers = str_replace("\n", '', $pollanswers);
            }
        } else {
            $pollanswers = '';
        }

        // check for posticon injection attacks
        if (isset($posticon) && $posticon != '') {
            $query = $db->query("SELECT id FROM $table_smilies WHERE type='picon' AND url='$posticon'");
            if (!$db->result($query, 0)) {
                $posticon = '';
                
                // register potential hack-attempt in logs
                $auditaction = $_SERVER['REQUEST_URI'];
                $aapos = strpos($auditaction, "?");
                if ($aapos !== false) {
                    $auditaction = basename(__FILE__).'?'.substr($auditaction, $aapos + 1);
                }                
                logAction('hackAttempt', array('comment'=>'Potential XSS exploit using posticon averted', 'url'=>$auditaction, 'ip'=>$onlineip), X_LOG_USER);
            }
        } else {
            $posticon = '';
        }

        $thatime = time();

        $subject = addslashes($subject);
        $message = addslashes($message);

        $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
        $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);

        $db->query("INSERT INTO $table_threads ( fid, subject, icon, lastpost, views, replies, author, closed, topped, pollopts ) VALUES ($fid, '$subject', '$posticon', '$thatime|$username', 0, 0, '$username', '', 0, '$pollanswers')");
        $tid = $db->insert_id();

        $db->query("INSERT INTO $table_posts ( fid, tid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff ) VALUES ($fid, $tid, '$username', '$message', '$subject', ".$db->time($thatime).", '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff')");
        $pid = $db->insert_id();

        $db->query("UPDATE $table_threads SET lastpost='$thatime|$username|$pid' WHERE tid=$tid");

        // Check if forum is subforum, if so, make lastpost on fup-forum
        if($forum['type'] == 'sub') {
            $db->query("UPDATE $table_forums SET lastpost='$thatime|$username|$pid', threads=threads+1, posts=posts+1 WHERE fid=$forum[fup]");
        }

        $db->query("UPDATE $table_forums SET lastpost='$thatime|$username|$pid', threads=threads+1, posts=posts+1 WHERE fid=$fid");

        // Auto subscribe options
        if ( $emailnotify == "yes") {
            $query = $db->query("SELECT tid FROM $table_favorites WHERE tid=$tid AND username='$xmbuser' AND type='subscription'");
            $thread = $db->fetch_array($query);
            if (!$thread) {
                $db->query("INSERT INTO $table_favorites (tid, username, type) VALUES ($tid, '$username', 'subscription')");
            }
        }

        if(X_MEMBER) {
            $db->query("UPDATE $table_members SET postnum=postnum+1 WHERE username like '$username'");
        }

        if($isModerator && $toptopic == "yes") {
            $db->query("UPDATE $table_threads SET topped=1 WHERE tid=$tid AND fid=$fid");
        }
        if($isModerator && $closetopic == "yes") {
            $db->query("UPDATE $table_threads SET closed='yes' WHERE tid=$tid AND fid=$fid");
        }

        // add the header here already so IF we get an error from get_attach_file() it prints out pretty.
        eval('echo "'.template('header').'";');

        // Insert Attachment if there is one
        // Do this last so if it errors out it doesn't break anything important
        if (isset($_FILES['attach']) && ($attachedfile = get_attached_file($_FILES['attach'], $forums['attachstatus'], $SETTINGS['maxattachsize'])) !== false) {
            $db->query("INSERT INTO $table_attachments (tid, pid, filename, filetype, filesize, attachment, downloads) VALUES ($tid, $pid, '$filename', '$filetype', $filesize, '$attachedfile', 0)");
            $redirSpeed = 2;
        } elseif(false === $attachedfile) {
            $redirSpeed = 5;
        } else {
            $redirSpeed = 2;
        }


        echo "<center><span class=\"mediumtxt \">$lang[postmsg]</span></center>";

        $query = $db->query("SELECT count(tid) FROM $table_posts WHERE tid='$tid'");
        $posts = $db->result($query, 0);

        $topicpages = quickpage($posts, $self['ppp']);

        redirect("viewthread.php?tid=".$tid."&page=".$topicpages."#pid".$pid, $redirSpeed, X_REDIRECT_JS);
    }

} elseif($action == 'reply') {
    nav('<a href="viewthread.php?tid='.$tid.'">'.$threadname.'</a>');
    nav($lang['textreply']);

    // check for moderator permissions
    if($isModerator) {
        $closeoption = '<br /><input type="checkbox" name="closetopic" value="yes" '.$closecheck.'/> '.$lang['closemsgques'].'<br />';
    } else {
        $closeoption = '';
    }

    if(!isset($replysubmit) || !$replysubmit) {
        eval('echo "'.template('header').'";');
        
        $posts          = '';
        $closeoption    = '';
        
        // Start Reply With Quote (casting $repquote to an integer just in case)
        if (isset($repquote) && ($repquote = (int) $repquote)) {
            $query = $db->query("SELECT message, fid, author, fid FROM $table_posts WHERE pid=$repquote");
            if($db->num_rows($query) == 1) {
                $thaquote = $db->fetch_array($query);
                if($thaquote['fid'] == $fid) {
                    $message = '[quote][i]'.$lang['origpostedby'].' '.$thaquote['author'].'[/i]'."\n".$thaquote['message'].'[/quote]';
                } else {
                    // we disallow quotes from other fora, 
                    // this is to prevent having to check for complex permission combinations
                    $message = '';
                }
            } else {
                $message = '';
            }
        }
        
        // Start Topic/Thread Review
        $querytop = $db->query("SELECT COUNT(tid) FROM $table_posts WHERE tid=$tid");
        $replynum = $db->result($querytop, 0);

        if($replynum >= $self['ppp']) {
            $threadlink = 'viewthread.php?fid='.$fid.'&tid='.$tid;
            eval($lang['evaltrevlt']);
            eval('$posts .= "'.template('post_reply_review_toolong').'";');
        } else {
            $thisbg = $THEME['altbg1'];
            $query = $db->query("SELECT * FROM $table_posts WHERE tid=$tid ORDER BY dateline DESC");
            while($post = $db->fetch_array($query)) {
                $date = printGmDate($post['dateline']);
                $time = printGmTime($post['dateline']);

                $poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;
                if($post['icon'] != '') {
                    $post['icon'] = '<img src="'.$THEME['smdir'].'/'.$post['icon'].'" alt="[*]" />';
                } else {
                    $post['icon'] = '<img src="'.$THEME['imgdir'].'/default_icon.gif" alt="[*]" />';
                }

                $post['message'] = postify($post['message'], $post['smileyoff'], $post['bbcodeoff'], $forum['allowsmilies'], $forum['allowhtml'], $forum['allowbbcode'], $forum['allowimgcode']);
                eval('$posts .= "'.template('post_reply_review_post').'";');
                if($thisbg == $THEME['altbg2']) {
                    $thisbg = $THEME['altbg1'];
                } else {
                    $thisbg = $THEME['altbg2'];
                }
            }
        }
        
        // Start Displaying the Post form
        eval('echo stripslashes("'.template('post_reply').'");');
        
    }else{
        if(!$subject && !$message) {
            error($lang['postnothing']);
        }

        if(X_GUEST) {
            $username = "Anonymous";
        } else {
            $username = $self['username'];
        }

        // check for flooding
        $query = $db->query("SELECT dateline FROM $table_posts WHERE author='$username' ORDER BY pid DESC LIMIT 1");
        if($db->num_rows($query) == 1) {
            $date = $db->result($query, 0);
            if($date > (time()-$SETTINGS['floodctrl'])) {
                error($lang['floodprotect']);
            }
        }
        
        // check for posticon injection attacks
        if (isset($posticon) && $posticon != '') {
            $query = $db->query("SELECT id FROM $table_smilies WHERE type='picon' AND url='$posticon'");
            if (!$db->result($query, 0)) {
                $posticon = '';
                
                // register potential hack-attempt in logs
                $auditaction = $_SERVER['REQUEST_URI'];
                $aapos = strpos($auditaction, "?");
                if ($aapos !== false) {
                    $auditaction = basename(__FILE__).'?'.substr($auditaction, $aapos + 1);
                }                
                logAction('hackAttempt', array('comment'=>'Potential XSS exploit using posticon averted', 'url'=>$auditaction, 'ip'=>$onlineip), X_LOG_USER);
            }
        } else {
            $posticon = '';
        }

        if($usesig == 'yes') {
            $usesig = 'yes';
        } else {
            $usesig = 'no';
        }

        $subject = addslashes($subject);
        $message = addslashes($message);

        $closed = $db->result($db->query("SELECT closed FROM $table_threads WHERE fid=$fid AND tid=$tid"), 0);
        if($closed == "yes" &&  !$isModerator) {
            error($lang['closedmsg']);
        } else {
            $thatime = time();
            $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
            $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);
            $db->query("INSERT INTO $table_posts (fid, tid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff) VALUES ($fid, $tid, '$username', '$message', '$subject', ".$db->time(time()).", '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff')");
            $pid = $db->insert_id();

            if($isModerator && $closetopic == "yes") {
                $db->query("UPDATE $table_threads SET closed='yes' WHERE tid=$tid AND fid=$fid");
            }

            $db->query("UPDATE $table_threads SET lastpost='$thatime|$username|$pid', replies=replies+1 WHERE (tid=$tid AND fid=$fid) OR closed='moved|$tid'");

            if ($forum['type'] == 'sub') {
                $db->query("UPDATE $table_forums SET lastpost='$thatime|$username|$pid', posts=posts+1 WHERE fid=$forum[fup]");
            }
            $db->query("UPDATE $table_forums SET lastpost='$thatime|$username|$pid', posts=posts+1 WHERE fid=$fid");
            if(X_MEMBER) {
                $db->query("UPDATE $table_members SET postnum=postnum+1 WHERE username='$username'");
            }

            // Start Subscriptions

            $query = $db->query("SELECT COUNT(pid) FROM $table_posts WHERE pid<=$pid AND tid=$tid");
            $posts = $db->result($query,0);

            // let's get the time for the previous post.
            $date = $db->result($db->query("SELECT dateline FROM $table_posts WHERE tid=$tid AND pid < $pid ORDER BY pid ASC LIMIT 1"), 0);
            $subquery = $db->query("SELECT m.email, m.lastvisit, m.ppp, m.status FROM $table_favorites f LEFT JOIN $table_members m ON (m.username=f.username) WHERE f.type='subscription' AND f.tid=$tid AND f.username != '$username'");
            while($subs = $db->fetch_array($subquery)) {
                if ($subs['status'] == 'banned' || $subs['lastvisit'] < $date) { // don't send double mails...!
                    continue;
                }
                if ($subs['ppp'] < 1) {
                    $subs['ppp'] = $posts;
                }

                $topicpages = quickpage($posts, $subs['ppp']);

                $threadurl = $SETTINGS['boardurl'] . 'viewthread.php?tid='.$tid.'&page='.$topicpages.'#pid'.$pid;
                altMail($subs['email'], $lang['textsubsubject'].' '.$threadname, $username.' '.$lang['textsubbody']." \n".$threadurl, "From: $bbname <$adminemail>");
            }

            // End Subscriptions
            // Auto subscribe options
            if ( $emailnotify == "yes") {
                $query = $db->query("SELECT tid FROM $table_favorites WHERE tid=$tid AND username=$xmbuser AND type=subscription");
                if ( $db->num_rows($query) < 1) {
                    $db->query("INSERT INTO $table_favorites ( tid, username, type ) VALUES ($tid, '$username', 'subscription')");
                }
            }
            eval('echo "'.template('header').'";'); // do it here so errors won't be shown above the header :P

            // Insert Attachment if there is one
            // Insert this last so if it errors out it doesn't break something
            if (isset($_FILES['attach']) && ($attachedfile = get_attached_file($_FILES['attach'], $forums['attachstatus'], $SETTINGS['maxattachsize'])) !== false) {
                $db->query("INSERT INTO $table_attachments ( tid, pid, filename, filetype, filesize, attachment, downloads ) VALUES ($tid, $pid, '$filename', '$filetype', $filesize, '$attachedfile', 0)");
                $redirSpeed = 2;
            } elseif($attachedfile === false) {
                $redirSpeed = 5;
            } else {
                $redirSpeed = 2;
            }

        }

        echo "<center><span class=\"mediumtxt \">$lang[replymsg]</span></center>";

        if ($posts > $self['ppp']) {
            $topicpages = quickpage($posts, $self['ppp']);
        } else {
            $topicpages = 1;
        }

        redirect("viewthread.php?tid=${tid}&page=${topicpages}#pid${pid}", $redirSpeed, X_REDIRECT_JS);
    }

} elseif($action == "edit") {
    nav('<a href="viewthread.php?tid='.$tid.'">'.$threadname.'</a>');
    nav($lang['texteditpost']);

    if (!isset($editsubmit)) {
        eval('echo "'.template('header').'";');
        $pq = $db->query("SELECT * FROM $table_posts WHERE tid=$tid AND pid=$pid");
        $postData = $db->fetch_array($pq);
        
        if($postData['fid'] != $fid) {
            // we have a nice injection attack on our hands (*sigh*)
            
            // register potential hack-attempt in logs
            $auditaction = $_SERVER['REQUEST_URI'];
            $aapos = strpos($auditaction, "?");
            if ($aapos !== false) {
                $auditaction = basename(__FILE__).'?'.substr($auditaction, $aapos + 1);
            }                
            logAction('hackAttempt', array('comment'=>'Potential fid exploit (fid provided did not match fid of thread)', 'url'=>$auditaction, 'ip'=>$onlineip), X_LOG_USER);
            
            // bail out, just-in-case
            error($lang['textnothread']);
        }

        if (isset($previewpost) || (isset($subaction) && $subaction == 'spellcheck' && (isset($spellchecksubmit) || isset($updates_submit)))) {
            $postinfo = array("usesig"=>$usesig, "bbcodeoff"=>$bbcodeoff, "smileyoff"=>$smileyoff, "message"=>$message, "subject"=>$subject, 'icon'=>$posticon);
            $query = $db->query("SELECT filename, filesize, downloads FROM $table_attachments WHERE pid=$pid AND tid=$tid");
            if ( $db->num_rows($query) > 0) {
                $postinfo = $db->fetch_array($query);
            }
        }else{
            $query = $db->query("SELECT a.filename, a.filesize, a.downloads, p.* FROM $table_posts p LEFT JOIN $table_attachments a  ON (a.pid = p.pid) WHERE p.pid=$pid AND p.tid=$tid AND p.fid=$forum[fid]");
            $postinfo = $db->fetch_array($query);
        }

        if (isset($postinfo['filesize'])) {
            $postinfo['filesize'] = number_format($postinfo['filesize'], 0, '.', ',');
        }

        $postinfo['message'] = stripslashes($postinfo['message']);

        if ( $postinfo['bbcodeoff'] == "yes") {
            $offcheck1 = "checked=\"checked\"";
        } else {
            $offcheck1 = '';
        }

        if ( $postinfo['smileyoff'] == "yes") {
            $offcheck2 = "checked=\"checked\"";
        } else {
            $offcheck2 = '';
        }

        if ( $postinfo['usesig'] == "yes") {
            $offcheck3 = "checked=\"checked\"";
        } else {
            $offcheck3 = '';
        }

        if (!X_STAFF) {
            $querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='picon' AND (url NOT LIKE '%rsvd%')");
            while($smilie = $db->fetch_array($querysmilie)) {
                if ( $postinfo['icon'] == $smilie['url']) {
                    $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" checked=\"checked\"/><img src=\"$THEME[smdir]/$smilie[url]\" alt=\"$smilie[code]\" />";
                }else{
                    $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$THEME[smdir]/$smilie[url]\" alt=\"$smilie[code]\" />";
                }
                $listed_icons += 1;
                if ( $listed_icons == 9) {
                    $icons .= "<br />";
                    $listed_icons = 0;
                }
            }
        } else {
            $querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='picon'");
            while($smilie = $db->fetch_array($querysmilie)) {
                if ( $postinfo['icon'] == $smilie['url']) {
                    $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" checked=\"checked\"/><img src=\"$THEME[smdir]/$smilie[url]\" alt=\"$smilie[code]\" />";
                }else{
                    $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$THEME[smdir]/$smilie[url]\" alt=\"$smilie[code]\" />";
                }
                $listed_icons += 1;
                if ( $listed_icons == 9) {
                    $icons .= "<br />";
                    $listed_icons = 0;
                }
            }
        }

        $postinfo['subject'] = stripslashes($postinfo['subject']);
        $postinfo['subject'] = str_replace('"', "&quot;", $postinfo['subject']);

        if (!X_SADMIN) {
            $postinfo['subject'] = censor($postinfo['subject']);
        }

        $message = $postinfo['message'];
        $subject = $postinfo['subject'];

        if (isset($previewpost)) {
            $message = censor($message);
        }
        if ( $postinfo['filename'] != '') {
            eval("\$attachment = \"".template('post_edit_attachment')."\";");
        }else{
            $attachment = $attachfile;
        }
        eval('echo "'.template('post_edit').'";');

    }else{
        if (X_GUEST) {
            $username = 'Anonymous';
        } else {
            $username = $self['username'];
        }

        $date = printGmDate();
        
        if ($SETTINGS['editedby'] == 'on') {
            $message .= "\n\n[".$lang['textediton'].' '.$date.' '.$lang['textby'].' '.$username.']';
        }

        $subject = addslashes($subject);

        // check for posticon injection attacks
        if (isset($posticon) && $posticon != '') {
            $query = $db->query("SELECT id FROM $table_smilies WHERE type='picon' AND url='$posticon'");
            if (!$db->result($query, 0)) {
                $posticon = '';
                
                // register potential hack-attempt in logs
                $auditaction = $_SERVER['REQUEST_URI'];
                $aapos = strpos($auditaction, "?");
                if ($aapos !== false) {
                    $auditaction = basename(__FILE__).'?'.substr($auditaction, $aapos + 1);
                }                
                logAction('hackAttempt', array('comment'=>'Potential XSS exploit using posticon averted', 'url'=>$auditaction, 'ip'=>$onlineip), X_LOG_USER);
            }
        } else {
            $posticon = '';
        }

        $query = $db->query("SELECT pid FROM $table_posts WHERE tid=$tid ORDER BY dateline LIMIT 1");
        $isfirstpost = $db->fetch_array($query);

        if((trim($subject) == '' && $pid == $isfirstpost['pid']) && !(isset($delete) && $delete == "yes")) {
            error($lang['textnosubject']);
        }
        $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
        $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);
        $posticon = htmlspecialchars($posticon);

        $query = $db->query("SELECT p.author as author, m.status as status FROM $table_posts p LEFT JOIN $table_members m ON p.author=m.username WHERE pid=$pid AND tid=$tid AND fid=$fid");
        $orig = $db->fetch_array($query);

        $message = addslashes($message);

        if($isModerator || ($username == $orig['author'] && $orig['author'] != 'Anonymous')) {
            if($SETTINGS['allowrankedit'] != 'off') {

                switch($orig['status']) {
                    case 'Super Administrator':
                        if (!X_SADMIN && $xmbuser != $orig['author']) {
                            error($lang['noedit']);
                        }
                        break;

                    case 'Administrator':
                        if ( !X_ADMIN && $xmbuser != $orig['author'] ) {
                            error($lang['noedit']);
                        }
                    break;

                    case 'Super Moderator':
                        if (( !X_ADMIN && $self['status'] != 'Super Moderator') && $xmbuser != $orig['author']) {
                            error($lang['noedit']);
                        }
                        break;

                    case 'Moderator':
                        if (( !X_ADMIN && $self['status'] != 'Moderator') && $xmbuser != $orig['author']) {
                            error($lang['noedit']);
                        }
                        break;
                }
            }

            if ( $isfirstpost['pid'] == $pid && !(isset($delete) && $delete == "yes")) {
                $db->query("UPDATE $table_threads SET icon='$posticon', subject='$subject' WHERE tid=$tid");
            }

            $threaddelete = 'no';
            eval('echo "'.template('header').'";');

            $db->query("UPDATE $table_posts SET message='$message', usesig='$usesig', bbcodeoff='$bbcodeoff', smileyoff='$smileyoff', icon='$posticon', subject='$subject' WHERE pid=$pid");

            if (isset($_FILES['attach']) && ($file = get_attached_file($_FILES['attach'], $forums['attachstatus'], $SETTINGS['maxattachsize'])) !== false) {
                $db->query("INSERT INTO $table_attachments ( tid, pid, filename, filetype, filesize, attachment, downloads ) VALUES ($tid, $pid, '$filename', '$attach[type]', $filesize, '$file', 0)");
                $redirSpeed = 2;
            } elseif($file === false) {
                $redirSpeed = 5;
            } else {
                $redirSpeed = 2;
            }

            if (isset($attachment) && is_array($attachment)) {
                switch($attachment['action']) {
                    case 'replace':
                        if (isset($_FILES['attachment_replace']) && ($file = get_attached_file($_FILES['attachment_replace'], $forums['attachstatus'], $SETTINGS['maxattachsize'])) !== false) {
                            $db->query("DELETE FROM $table_attachments WHERE pid=$pid");
                            $db->query("INSERT INTO $table_attachments (tid, pid, filename, filetype, filesize, attachment, downloads) VALUES ($tid, $pid, '$filename', '$attachment_replace[type]', $filesize, '$file', 0)");
                        } elseif($file === false) {
                            $redirSpeed = 5;
                        } else {
                            $redirSpeed = 2;
                        }
                        break;

                    case 'rename':
                        $name = basename($attach_name);
                        if (strlen($name) < 2 || preg_match('#^[^a-z0-9]+$#', $name) == 1) {
                            break;
                        } else {
                            $db->query("UPDATE $table_attachments SET filename='$name' WHERE pid=$pid");
                        }
                        break;

                    case 'delete':
                        $db->query("DELETE FROM $table_attachments WHERE pid=$pid");
                        break;

                    default:
                        break;
                }
            }

            if (isset($delete) && $delete == "yes" && !($isfirstpost['pid'] == $pid)) {
                $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$orig[author]'");
                $db->query("DELETE FROM $table_attachments WHERE pid=$pid");
                $db->query("DELETE FROM $table_posts WHERE pid=$pid");
                updateforumcount($fid);
                updatethreadcount($tid);

            } elseif (isset($delete) && $delete == "yes" && $isfirstpost['pid'] == $pid) {
                // find out if thread contains more than one post.
                // -- If so, delete only the head pid.
                // -- If not (ie only one post in the thread) kill the thread

                $query = $db->query("SELECT pid FROM $table_posts WHERE tid=$tid");
                $numrows = $db->num_rows($query);
                $db->free_result($query);
                
                // one row = kill thread properly
                if ( $numrows == 1 ) {
                    $query = $db->query("SELECT author FROM $table_posts WHERE tid=$tid");
                    while($result = $db->fetch_array($query)) {
                        $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$result[author]'");
                    }
                    $db->free_result($query);
                    $db->query("DELETE FROM $table_threads WHERE tid=$tid");
                    $db->query("DELETE FROM $table_attachments WHERE tid=$tid");
                    $db->query("DELETE FROM $table_posts WHERE tid=$tid");
                    $threaddelete = 'yes';
                }

                if ( $numrows > 1 ) {
                    // delete the old head pid, but leave the rest of the thread intact
                    $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$orig[author]'");
                    $db->query("DELETE FROM $table_attachments WHERE pid=$pid");
                    $db->query("DELETE FROM $table_posts WHERE pid=$pid");
                    $threaddelete = 'no';
                }

                // update forum and thread count stats
                updateforumcount($fid);
                updatethreadcount($tid);
            }
        } else {
            error($lang['noedit']);
        }
        echo '<center><span class="mediumtxt">'.$lang['editpostmsg'].'</span></center>';

        if($threaddelete != 'yes') {
            $query = $db->query("SELECT COUNT(pid) FROM $table_posts WHERE pid<=$pid AND tid=$tid AND fid=$fid");
            $posts = $db->result($query,0);
            $topicpages = quickpage($posts, $self['ppp']);

            redirect('viewthread.php?tid='.$tid.'&page='.$topicpages.'#pid'.$pid, $redirspeed, X_REDIRECT_JS);
        } else {
            redirect('forumdisplay.php?fid='.$fid, $redirspeed, X_REDIRECT_JS);
        }
    }
} else {
    error($lang['textnoaction']);
}

end_time();
eval('echo "'.template('footer').'";');