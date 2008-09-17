<?php
/**
 * XMB 1.9.5 Nexus Final SP1
 * © 2007 John Briggs
 * http://www.xmbmods.com
 * john@xmbmods.com
 *
 * Developed By The XMB Group
 * Copyright (c) 2001-2007, The XMB Group
 * http://www.xmbforum.com
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 **/

require_once('header.php');

function bbcodeinsert() {
    global $imgdir, $bbinsert, $altbg1, $altbg2, $lang, $SETTINGS, $spelling_lang;

    $bbcode = '';
    if ($bbinsert == 'on') {
        eval('$bbcode = "'.template('functions_bbcodeinsert').'";');
    }
    return $bbcode;
}

loadtemplates(
'post_notloggedin',
'post_loggedin',
'post_preview',
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

smcwcache();

$pid = (isset($pid) ? (int) $pid : 0);
$tid = (isset($tid) ? (int) $tid : 0);
$fid = (isset($fid) ? (int) $fid : 0);
$posterror = false;

validatePpp();

$thread = array();
$threadname = '';

if ($tid) {
    $query = $db->query("SELECT fid, subject FROM $table_threads WHERE tid='$tid' LIMIT 1");
    if ($db->num_rows($query) == 1) {
        $thread = $db->fetch_array($query);
        $threadname = $thread['subject'];
        $threadname = stripslashes($threadname);
        $fid = (int) $thread['fid'];
    } else {
        error($lang['textnothread']);
    }
}

$query = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
$forums = $db->fetch_array($query);
$forums['name'] = stripslashes($forums['name']);

if (($fid == 0 && $tid == 0) || ( $forums['type'] != 'forum' && $forums['type'] != 'sub' && $forums['fid'] != $fid)) {
    $posterror = $lang['textnoforum'];
}

if (isset($forums['type']) && $forums['type'] == "forum") {
    nav('<a href="forumdisplay.php?fid='.$fid.'">'.stripslashes($forums['name']) .'</a>');
} else {
    if (!isset($forums['fup']) || !is_numeric($forums['fup'])) {
        $posterror = $lang['textnoforum'];
    } else {
        $query = $db->query("SELECT name, fid FROM $table_forums WHERE fid='$forums[fup]'");
        $fup = $db->fetch_array($query);
        nav('<a href="forumdisplay.php?fid='.$fup['fid'].'">'.stripslashes($fup['name']).'</a>');
        nav('<a href="forumdisplay.php?fid='.$fid.'">'.stripslashes($forums['name']).'</a>');
    }
}

$attachfile = '';
if (isset($forums['attachstatus']) && $forums['attachstatus'] != 'off') {
    eval('$attachfile = "'.template("post_attachmentbox").'";');
}

if (X_GUEST) {
    eval('$loggedin = "'.template('post_notloggedin').'";');
} else {
    eval("\$loggedin = \"".template("post_loggedin")."\";");
}

if ($self['ban'] == "posts" || $self['ban'] == "both") {
    error($lang['textbanfrompost']);
}

if ($self['status'] == "Banned") {
    error($lang['bannedmessage']);
}

$listed_icons = 0;
$icons = '<input type="radio" name="posticon" value="" /> <img src="'.$imgdir.'/default_icon.gif" alt="[*]" />';

if ($action != 'edit') {
    if (!X_STAFF) {
        $querysmilie = $db->query("SELECT url, code FROM $table_smilies WHERE type='picon' AND (url NOT LIKE '%rsvd%')");
        while ($smilie = $db->fetch_array($querysmilie)) {
            $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$smdir/$smilie[url]\" alt=\"$smilie[code]\" />";
            $listed_icons += 1;
            if ( $listed_icons == 9) {
                $icons .= "<br />";
                $listed_icons = 0;
            }
        }
        $db->free_result($querysmilie);
    } else {
        $querysmilie = $db->query("SELECT url, code FROM $table_smilies WHERE type='picon'");
        while ($smilie = $db->fetch_array($querysmilie)) {
            $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$smdir/$smilie[url]\" alt=\"$smilie[code]\" />";
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

if (!isset($usesig) ) {
    $usesig = 'no';
}

if ($usesig != 'yes' ) {
    $usesig = 'no';
}

$chkInputHTML = 'no';
$chkInputTags = 'no';
if ( isset($forums['allowhtml']) && $forums['allowhtml'] == 'yes' ) {
    $chkInputHTML = 'yes';
    $chkInputTags = 'no';
}

$allowimgcode   = ( isset($forums['allowimgcode']) && $forums['allowimgcode'] == "yes" )    ? $lang['texton'] : $lang['textoff'];
$allowhtml      = ( $chkInputHTML == 'yes' )                                                ? $lang['texton'] : $lang['textoff'];
$allowsmilies   = ( isset($forums['allowsmilies']) && $forums['allowsmilies'] == "yes" )    ? $lang['texton'] : $lang['textoff'];
$allowbbcode    = ( isset($forums['allowbbcode']) && $forums['allowbbcode'] == "yes" )      ? $lang['texton'] : $lang['textoff'];
$pperm['type']  = ( isset($action) && $action == 'newthread' )                              ? 'thread' : 'reply';

if (!postperm($forums, $pperm['type'])) {
    error($lang['privforummsg']);
}

if (X_GUEST && $forums['guestposting'] == 'on') {
    $guestpostingmsg = $lang['guestpostingonmsg'];
}else{
    $guestpostingmsg = '';
}

if ( $posterror) {
    error($posterror);
}

// add all checks in case of preview
if (isset($smileyoff) && $smileyoff == 'yes') {
    $smileoffcheck = 'checked="checked"';
} else {
    $smileoffcheck = '';
    $smileyoff = 'no';
}

if (isset($bbcodeoff) && $bbcodeoff == 'yes') {
    $codeoffcheck = 'checked="checked"';
} else {
    $codeoffcheck = '';
    $bbcodeoff = 'no';
}

if (isset($emailnotify) && $emailnotify == 'yes') {
    $emailnotifycheck = 'checked="checked"';
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

if (X_STAFF) {
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

pwverify($forums['password'], 'post.php?action='.$action.'&fid='.$fid.'&tid='.$tid.'&repquote='.$repquote.'&poll='.$poll, $fid);

$query = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
$forum = $db->fetch_array($query);
$authorization = privfcheck($forum['private'], $forum['userlist']);
if (!$authorization) {
    error($lang['privforummsg']);
}

if ( !empty($posticon) ) {
    $thread['icon'] = (file_exists($smdir.'/'.$posticon)) ? "<img src=\"$smdir/$posticon\" />" : '';
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
    $currtime = time();
    $date = gmdate("n/j/y",$currtime + ($timeoffset * 3600) + ($addtime * 3600));
    $time = gmdate("H:i",$currtime + ($timeoffset * 3600) + ($addtime * 3600));
    $poston = "$lang[textposton] $date $lang[textat] $time";

    $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
    $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);
    $message1 = postify($message, $smileyoff, $bbcodeoff, $forums['allowsmilies'], $forums['allowhtml'], $forums['allowbbcode'], $forums['allowimgcode']);
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




if ( $action == "newthread") {
    $priv = privfcheck($forums['private'], $forums['userlist']);
    if (isset($poll) && $poll == 'yes') {
        nav($lang['textnewpoll']);
    } else {
        nav($lang['textpostnew']);
    }
    if (!isset($topicsubmit) || !$topicsubmit) {
        eval('echo "'.template('header').'";');
        $status1 = modcheck($self['status'], $xmbuser, $forums['moderator']);
        if ( $self['status'] == "Super Moderator") {
            $status1 = "Moderator";
        }

        if (X_STAFF) {
            $topoption = "<br /><input type=\"checkbox\" name=\"toptopic\" value=\"yes\" $topcheck/> $lang[topmsgques]";
            $closeoption = "<br /><input type=\"checkbox\" name=\"closetopic\" value=\"yes\" $closecheck/> $lang[closemsgques]<br />";
        }else{
            $topoption = '';
            $closeoption = '';
        }

        if ( ! isset($spelling_submit2) ) {
            $spelling_submit2 = '';
        }

        // new poll code begin
        if (isset($poll) && $poll == 'yes' && $forums['pollstatus'] != 'off') {
            if (!isset($pollanswers)){
                $pollanswers = '';
            }
            eval('echo stripslashes("'.template('post_newpoll').'");');
        } else {
            eval('echo stripslashes("'.template('post_newthread').'");');
        }
        // new poll code end
    }else{
        if (!empty($username) && !empty($password)) {
            if (X_GUEST) {
                $password = md5(trim($password));
            }
            $username = trim($username);
            $q = $db->query("SELECT * FROM $table_members WHERE username='$username'");
            if ($db->num_rows($q) != 1) {
                error($lang['badname']);
            }
            else {
                $self = $db->fetch_array($q);
                if ($password != $self['password']) {
                    error($lang['textpw1']);
                }
                $username = $self['username'];
            }
            if ($self['status'] == "Banned") {
                error($lang['bannedmessage']);
            }
            $currtime = time() + (86400*30);
            put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
            put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
            if ($self['ban'] == "posts" || $self['ban'] == "both") {
                error($lang['textbanfrompost']);
            }
        }
        else {
            if (X_GUEST) {
                $username = "Anonymous";
            }else{
                $username = $xmbuser;
            }
        }

        if ( $forums['guestposting'] != "on" && $username == "Anonymous") {
            error($lang['textnoguestposting']);
        }

        $pperm = explode("|", $forums['postperm']);

        if ( $pperm[0] == "2" && !X_ADMIN) {
            error($lang['postpermerr']);
        } elseif ( $pperm[0] == "3" && !X_STAFF) {
            error($lang['postpermerr']);
        } elseif ( $pperm[0] == "4") {
            error($lang['postpermerr']);
        }

        $query = $db->query("SELECT lastpost, type, fup FROM $table_forums WHERE fid='$fid'");
        $for = $db->fetch_array($query);

        if ( $for['lastpost'] != "") {
            $lastpost = explode("|", $for['lastpost']);
            $rightnow = time() - $floodctrl;

            if ( $rightnow <= $lastpost[0] && $username == $lastpost[1]) {
                error($lang['floodprotect'].' '.$floodlink.' '.$lang['tocont']);
            }
        }

        if ( $forums['pollstatus'] != "off") {
            if (isset($pollanswers)) {
                $pollanswers = checkInput($pollanswers);
                if (strpos($pollanswers, '#|#') !== false || strpos($pollanswers, '||~|~||') !== false) {
                    $pollanswers = '';
                }else{
                    $pollops = explode("\n", $pollanswers);
                    $pollanswers = "";
                    $pnumnum = count($pollops);
                    if ( $pnumnum < 2 && $pollanswers != '') {
                        error($lang['too_few_pollopts']);
                    }
                    for($pnum = 0; $pnum < $pnumnum; $pnum++) {
                        if ( $pollops[$pnum] != "") {
                            $pollanswers .= "$pollops[$pnum]||~|~|| 0#|#";
                        }
                    }

                    $pollanswers = str_replace("\n", '', $pollanswers);
                }
            } else {
                $pollanswers = '';
            }
        }

        if (isset($posticon) && $posticon != '') {
            $query = $db->query("SELECT id FROM $table_smilies WHERE type='picon' AND url='$posticon'");

            if (!$db->result($query, 0)) {
                exit();
            }
        } else {
            $posticon = '';
        }

        $thatime = time();

        $subject = addslashes($subject);
        $message = addslashes($message);

        $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
        $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);

        $db->query("INSERT INTO $table_threads ( fid, subject, icon, lastpost, views, replies, author, closed, topped, pollopts ) VALUES ('$fid', '$subject', '$posticon', '$thatime|$username', 0, 0, '$username', '', 0, '$pollanswers')");
        $tid = $db->insert_id();

        $db->query("INSERT INTO $table_posts ( fid, tid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff ) VALUES ('$fid', '$tid', '$username', '$message', '$subject', ".$db->time($thatime).", '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff')");
        $pid = $db->insert_id();

        $db->query("UPDATE $table_threads SET lastpost=concat(lastpost, '|".$pid."') WHERE tid='$tid'");

        // Check if forum is subforum, if so, make lastpost on fup-forum
        if ( $forum['type'] == 'sub') {
            $db->query("UPDATE $table_forums SET lastpost='$thatime|$username|$pid', threads=threads+1, posts=posts+1 WHERE fid='$for[fup]'");
        }

        $db->query("UPDATE $table_forums SET lastpost='$thatime|$username|$pid', threads=threads+1, posts=posts+1 WHERE fid='$fid'");

        // Auto subscribe options
        if ( $emailnotify == "yes") {
            $query = $db->query("SELECT tid FROM $table_favorites WHERE tid='$tid' AND username='$xmbuser' AND type='subscription'");
            $thread = $db->fetch_array($query);
            if (!$thread) {
                $db->query("INSERT INTO $table_favorites ( tid, username, type ) VALUES ('$tid', '$username', 'subscription')");
            }
        }


        $db->query("UPDATE $table_members SET postnum=postnum+1 WHERE username like '$username'");

        if ((X_STAFF) && $toptopic == "yes") {
            $db->query("UPDATE $table_threads SET topped='1' WHERE tid='$tid' AND fid='$fid'");
        }
        if ((X_STAFF) && $closetopic == "yes") {
            $db->query("UPDATE $table_threads SET closed='yes' WHERE tid='$tid' AND fid='$fid'");
        }

        // add the header here already so IF we get an error from get_attach_file() it prints out pretty.
        eval('echo "'.template('header').'";');

        // Insert Attachment if there is one
        // Do this last so if it errors out it doesn't break anything important
        if (isset($_FILES['attach']) && ($attachedfile = get_attached_file($_FILES['attach'], $forums['attachstatus'], $max_attach_size)) !== false) {
            $db->query("INSERT INTO $table_attachments ( tid, pid, filename, filetype, filesize, attachment, downloads ) VALUES ('$tid', '$pid', '$filename', '$filetype', '$filesize', '$attachedfile', '0')");
        }


        echo "<center><span class=\"mediumtxt \">$lang[postmsg]</span></center>";

        $query = $db->query("SELECT count(tid) FROM $table_posts WHERE tid='$tid'");
        $posts = $db->result($query, 0);

        $topicpages = quickpage($posts, $ppp);

        redirect("viewthread.php?tid=".$tid."&page=".$topicpages."#pid".$pid, 2, X_REDIRECT_JS);
    }

}elseif ( $action == "reply") {
    nav('<a href="viewthread.php?tid='.$tid.'">'.$threadname.'</a>');
    nav($lang['textreply']);

    $priv = privfcheck($forums['private'], $forums['userlist']);
    if (!isset($replysubmit) || !$replysubmit) {
        $posts = '';
        eval("echo (\"".template('header')."\");");

        if (X_STAFF) {
            $closeoption = "<br /><input type=\"checkbox\" name=\"closetopic\" value=\"yes\" $closecheck/> $lang[closemsgques]<br />";
        }else{
            $closeoption = '';
        }

        // Start Reply With Quote
        if (isset($repquote) && ($repquote = (int) $repquote)) {
            $query = $db->query("SELECT p.message, p.fid, p.author, f.private AS fprivate, f.userlist AS fuserlist, f.password AS fpassword FROM $table_posts p, $table_forums f WHERE pid='$repquote' AND f.fid=p.fid");
            $thaquote = $db->fetch_array($query);
            $quotefid = $thaquote['fid'];
            $pass     = trim($thaquote['fpassword']);

            if ( !X_ADMIN && trim($pass) != '' && $_COOKIE['fidpw'.$quotefid] != $pass ) {
               error($lang['privforummsg'], false);
            }

            $authorization = privfcheck($thaquote['fprivate'], $thaquote['fuserlist']);
            if (!$authorization) {
                error($lang['privforummsg'], false);
            }

            $message = "[quote][i]$lang[origpostedby] $thaquote[author][/i]\n$thaquote[message] [/quote]";
        }
        // Start Topic/Thread Review
        $querytop = $db->query("SELECT COUNT(tid) FROM $table_posts WHERE tid='$tid'");
        $replynum = $db->result($querytop, 0);

        if ( $replynum >= $ppp) {
            $threadlink = "viewthread.php?fid=$fid&tid=$tid";
            eval($lang['evaltrevlt']);
            eval("\$posts .= \"".template("post_reply_review_toolong")."\";");
        }else{
            $thisbg = $altbg1;
            $query = $db->query("SELECT * FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC");
            while($post = $db->fetch_array($query)) {
                $date = gmdate($dateformat, $post['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));
                $time = gmdate($timecode, $post['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));

                $poston = "$lang[textposton] $date $lang[textat] $time";
                if ( $post['icon'] != "") {
                    $post['icon'] = "<img src=\"$smdir/$post[icon]\" alt=\"$lang[altpostmood]\" />";
                } else {
                    $post['icon'] = "<img src=\"$imgdir/default_icon.gif\" alt=\"[*]\" />";
                }

                $post['message'] = postify($post['message'], $post['smileyoff'], $post['bbcodeoff'], $forums['allowsmilies'], $forums['allowhtml'], $forums['allowbbcode'], $forums['allowimgcode']);
                eval("\$posts .= \"".template("post_reply_review_post")."\";");
                if ( $thisbg == $altbg2) {
                    $thisbg = $altbg1;
                } else {
                    $thisbg = $altbg2;
                }
            }
        }
        // Start Displaying the Post form
        if ( $forums['attachstatus'] != "off") {
            eval("\$attachfile = \"".template("post_attachmentbox")."\";");
        } else {
            $attachfile = '';
        }

        eval('echo stripslashes("'.template('post_reply').'");');
    }else{
        if (!$subject && !$message) {
            error($lang['postnothing']);
        }
        if ( X_MEMBER && empty($username) && empty($password) ) {
            $username = $xmbuser;
            $password = $xmbpw;
        }
        if (!empty($username) && !empty($password)) {
            if ( X_GUEST ) {
                $username = trim($username);
                $password = md5(trim($password));
            }
            $q = $db->query("SELECT * FROM $table_members WHERE username='$username'");
            if ($db->num_rows($q) != 1) {
                error($lang['badname']);
            }
            else {
                $self = $db->fetch_array($q);
                if ($password != $self['password']) {
                    error($lang['textpw1']);
                }
                $username = $self['username'];
            }
            if ($self['status'] == "Banned") {
                error($lang['bannedmessage']);
            }
            $currtime = time() + (86400*30);
            put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
            put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
            if ($self['ban'] == "posts" || $self['ban'] == "both") {
                error($lang['textbanfrompost']);
            }
        }
        else {
            if ( X_GUEST ) {
                $username = "Anonymous";
            }else{
                $username = $xmbuser;
            }
        }

        if ( $forums['guestposting'] != "on" && $username == "Anonymous") {
            error($lang['textnoguestposting']);
        }

        $pperm = explode("|", $forums['postperm']);

        if ( $pperm[1] == "2" && !X_ADMIN) {
            error($lang['postpermerr']);
        } elseif ( $pperm[1] == "3" && !X_STAFF) {
            error($lang['postpermerr']);
        } elseif ( $pperm[1] == "4") {
            error($lang['postpermerr']);
        }

        if (isset($posticon) && $posticon != "") {
            $query = $db->query("SELECT id FROM $table_smilies WHERE type='picon' AND url='$posticon'");

            if (!$db->result($query, 0)) {
                exit();
            }
        } else {
            $posticon = '';
        }
        $query = $db->query("SELECT lastpost, type, fup FROM $table_forums WHERE fid='$fid'");
        $for = $db->fetch_array($query);
        $last = $for['lastpost'];

        if ( $last != "") {
            $lastpost = explode("|", $last);
            $rightnow = time() - $floodctrl;

            if ( $rightnow <= $lastpost[0] && $username == $lastpost[1]) {
                $floodlink = "<a href=\"viewthread.php?fid=$fid&tid=$tid\">Click here</a>";
                error($lang['floodprotect'].' '.$floodlink.' '.$lang['tocont']);
            }
        }

        if ( $usesig != "yes") {
            $usesig = "no";
        }

        $subject = addslashes($subject);
        $message = addslashes($message);

        $query = $db->query("SELECT closed,topped FROM $table_threads WHERE fid=$fid AND tid=$tid");
        $closed1 = $db->fetch_array($query);
        $closed = $closed1['closed'];
        if ( $closed == "yes" && !X_STAFF) {
            error($lang['closedmsg']);
        } else {
            $thatime = time();
            $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
            $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);
            $db->query("INSERT INTO $table_posts ( fid, tid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff ) VALUES ('$fid', '$tid', '$username', '$message', '$subject', ".$db->time(time()).", '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff')");
            $pid = $db->insert_id();

            if ((X_STAFF) && $closetopic == "yes") {
                $db->query("UPDATE $table_threads SET closed='yes' WHERE tid='$tid' AND fid='$fid'");
            }

            $db->query("UPDATE $table_threads SET lastpost='$thatime|$username|$pid', replies=replies+1 WHERE (tid='$tid' AND fid='$fid') OR closed='moved|$tid'");

            if ( $for['type'] == 'sub') {
                $db->query("UPDATE $table_forums SET lastpost='$thatime|$username|$pid', posts=posts+1 WHERE fid='$for[fup]'");
            }
            $db->query("UPDATE $table_forums SET lastpost='$thatime|$username|$pid', posts=posts+1 WHERE fid='$fid'");
            $db->query("UPDATE $table_members SET postnum=postnum+1 WHERE username='$username'");

            // Start Subscriptions

            $query = $db->query("SELECT COUNT(pid) FROM $table_posts WHERE pid<=$pid AND tid='$tid'");
            $posts = $db->result($query,0);

            if ($posts > $ppp) {
                $topicpages = quickpage($posts, $ppp);
            } else {
                $topicpages = 1;
            }

            redirect("viewthread.php?tid=${tid}&page=${topicpages}#pid${pid}", 2, X_REDIRECT_JS);

            // let's get the time for the previous post.
            $date = $db->result($db->query("SELECT dateline FROM $table_posts WHERE tid='$tid' AND pid < '$pid' ORDER BY pid ASC LIMIT 1"), 0);
            $subquery = $db->query("SELECT m.email, m.lastvisit, m.ppp, m.status FROM $table_favorites f LEFT JOIN $table_members m ON (m.username=f.username) WHERE f.type='subscription' AND f.tid='$tid' AND f.username != '$username'");
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
                $query = $db->query("SELECT tid FROM $table_favorites WHERE tid='$tid' AND username='$xmbuser' AND type='subscription'");
                if ( $db->num_rows($query) < 1) {
                    $db->query("INSERT INTO $table_favorites ( tid, username, type ) VALUES ('$tid', '$username', 'subscription')");
                }
            }
            eval('echo "'.template('header').'";'); // do it here so errors won't be shown above the header :P

            // Insert Attachment if there is one
            // Insert this last so if it errors out it doesn't break something
            if (isset($_FILES['attach']) && ($attachedfile = get_attached_file($_FILES['attach'], $forums['attachstatus'], $max_attach_size)) !== false) {
                $db->query("INSERT INTO $table_attachments ( tid, pid, filename, filetype, filesize, attachment, downloads ) VALUES ('$tid', '$pid', '$filename', '$filetype', '$filesize', '$attachedfile', '0')");
            }

        }

        if ( X_MEMBER ) {
            $currtime = time() + (86400*30);
            put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
            put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
        }


        echo "<center><span class=\"mediumtxt \">$lang[replymsg]</span></center>";
    }

}elseif ( $action == "edit") {
    nav('<a href="viewthread.php?tid='.$tid.'">'.$threadname.'</a>');
    nav($lang['texteditpost']);

    if (!isset($editsubmit)) {
        eval("echo (\"".template('header')."\");");
        $queryextra = $db->query("SELECT f.* FROM $table_posts p LEFT JOIN $table_forums f ON (f.fid = p.fid) WHERE p.tid='$tid' AND p.pid='$pid'");
        $forum = $db->fetch_array($queryextra);

        $authorization = privfcheck($forum['private'], $forum['userlist']);
        if (!$authorization) {
            $header = '';
            error($lang['privforummsg']);
        }
        /*
        if (!X_SADMIN && $forums['password'] != '') {
            if (!isset($_COOKIE['fidpw'.$fid]) || isset($_COOKIE['fidpw'.$fid]) && $forums['password'] != $_COOKIE['fidpw'.$fid]) {
                $url = "viewthread.php?tid=$tid&action=pwverify";
                eval('$pwverify = "'.template('forumdisplay_password').'";');

                error($lang['forumpwinfo'], false, '', $pwverify, false, true, false, true);
            }
        }
        */
        // pwverify($forums['password'], 'post.php?action=edit&amp;tid='.$tid, $fid);

        if (isset($previewpost) || (isset($subaction) && $subaction == 'spellcheck' && (isset($spellchecksubmit) || isset($updates_submit)))) {
            $postinfo = array("usesig"=>$usesig, "bbcodeoff"=>$bbcodeoff, "smileyoff"=>$smileyoff, "message"=>$message, "subject"=>$subject, 'icon'=>$posticon);
            $query = $db->query("SELECT filename, filesize, downloads FROM $table_attachments WHERE pid='$pid' AND tid='$tid'");
            if ( $db->num_rows($query) > 0) {
                $postinfo = array_merge($postinfo, $db->fetch_array($query));
            }
        }else{
            $query = $db->query("SELECT a.filename, a.filesize, a.downloads, p.* FROM $table_posts p LEFT JOIN $table_attachments a  ON (a.pid = p.pid) WHERE p.pid='$pid' AND p.tid='$tid' AND p.fid='$forum[fid]'");
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
                    $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" checked=\"checked\"/><img src=\"$smdir/$smilie[url]\" alt=\"$smilie[code]\" />";
                }else{
                    $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$smdir/$smilie[url]\" alt=\"$smilie[code]\" />";
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
                    $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" checked=\"checked\"/><img src=\"$smdir/$smilie[url]\" alt=\"$smilie[code]\" />";
                }else{
                    $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$smdir/$smilie[url]\" alt=\"$smilie[code]\" />";
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
        eval("\$edit = \"".template("post_edit")."\";");
        echo $edit;
    }else{
        if (X_GUEST) {
            $username = trim($username);
            $password = md5(trim($password));
        }
        $q = $db->query("SELECT * FROM $table_members WHERE username='$username'");
        if ($db->num_rows($q) != 1) {
            error($lang['badname']);
        }
        else {
            $self = $db->fetch_array($q);
            if ($password != $self['password']) {
                error($lang['textpw1']);
            }
            $username = $self['username'];
        }
        if ($self['status'] == "Banned") {
            error($lang['bannedmessage']);
        }
        $currtime = time() + (86400*30);
        put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
        put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
        if ($self['ban'] == "posts" || $self['ban'] == "both") {
            error($lang['textbanfrompost']);
        }

        $date = gmdate($dateformat);
        if ($SETTINGS['editedby'] == 'on') {
            $message .= "\n\n[$lang[textediton] $date $lang[textby] $username]";
        }

        $subject = addslashes($subject);


        $status1 = modcheck($self['status'], $username, $forums['moderator']);
        if ( $self['status'] == "Super Moderator") {
            $status1 = "Moderator";
        }

        if (isset($posticon) && $posticon != "") {
            $query = $db->query("SELECT id FROM $table_smilies WHERE type='picon' AND url='$posticon'");

            if (!$db->result($query, 0)) {
                exit();
            }
        } else {
            $posticon = '';
        }

        $query = $db->query("SELECT pid FROM $table_posts WHERE tid='$tid' ORDER BY dateline LIMIT 1");
        $isfirstpost = $db->fetch_array($query);

        if((trim($subject) == '' && $pid == $isfirstpost['pid']) && !(isset($delete) && $delete == "yes")) {
            error($lang['textnosubject']);
        }
        $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
        $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);
        $posticon = htmlspecialchars($posticon);

        $query = $db->query("SELECT p.author as author, m.status as status, p.subject as subject FROM $table_posts p LEFT JOIN $table_members m ON p.author=m.username WHERE pid='$pid' AND tid='$tid' AND fid='$fid'");
        $orig = $db->fetch_array($query);
        $db->free_result($query);

        $message = addslashes($message);

        if ((X_STAFF && $status1 == 'Moderator') || $username == $orig['author']) {
            if ( $SETTINGS['allowrankedit'] != 'off') {

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
                $db->query("UPDATE $table_threads SET icon='$posticon', subject='$subject' WHERE tid='$tid'");
            }

            $threaddelete = 'no';
            eval('echo "'.template('header').'";');

            $db->query("UPDATE $table_posts SET message='$message', usesig='$usesig', bbcodeoff='$bbcodeoff', smileyoff='$smileyoff', icon='$posticon', subject='$subject' WHERE pid='$pid'");

            if (isset($_FILES['attach']) && ($file = get_attached_file($_FILES['attach'], $forums['attachstatus'], $max_attach_size)) !== false) {
                $db->query("INSERT INTO $table_attachments ( tid, pid, filename, filetype, filesize, attachment, downloads ) VALUES ('$tid', '$pid', '$filename', '$attach[type]', '$filesize', '$file', '0')");
            }

            if (isset($attachment) && is_array($attachment)) {
                switch($attachment['action']) {
                    case 'replace':
                        if (isset($_FILES['attachment_replace']) && ($file = get_attached_file($_FILES['attachment_replace'], $forums['attachstatus'], $max_attach_size)) !== false) {
                            $db->query("DELETE FROM $table_attachments WHERE pid='$pid'");
                            $db->query("INSERT INTO $table_attachments ( aid, tid, pid, filename, filetype, filesize, attachment, downloads ) VALUES ('', '$tid', '$pid', '$filename', '$attachment_replace[type]', '$filesize', '$file', '0')");
                        }
                        break;

                    case 'rename':
                        $name = basename($attach_name);
                        if (strlen($name) < 2 || preg_match('#^[^a-z0-9]+$#', $name) == 1) {
                            break;
                        } else {
                            $db->query("UPDATE $table_attachments SET filename='$name' WHERE pid='$pid'");
                        }
                        break;

                    case 'delete':
                        $db->query("DELETE FROM $table_attachments WHERE pid='$pid'");
                        break;

                    default:
                        break;
                }
            }

            if (isset($delete) && $delete == "yes" && !($isfirstpost['pid'] == $pid)) {
                $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$orig[author]'");
                $db->query("DELETE FROM $table_attachments WHERE pid='$pid'");
                $db->query("DELETE FROM $table_posts WHERE pid='$pid'");
                updateforumcount($fid);
                updatethreadcount($tid);

            } elseif (isset($delete) && $delete == "yes" && $isfirstpost['pid'] == $pid) {
                // find out if thread contains more than one post.
                // -- If so, delete only the head pid.
                // -- If not (ie only one post in the thread) kill the thread

                $query = $db->query("SELECT pid FROM $table_posts WHERE tid='$tid'");
                $numrows = $db->num_rows($query);
                $db->free_result($query);

                // one row = kill thread properly
                if ( $numrows == 1 ) {
                    $query = $db->query("SELECT author FROM $table_posts WHERE tid='$tid'");
                    while($result = $db->fetch_array($query)) {
                        $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$result[author]'");
                    }
                    $db->free_result($query);
                    $db->query("DELETE FROM $table_threads WHERE tid='$tid'");
                    $db->query("DELETE FROM $table_attachments WHERE tid='$tid'");
                    $db->query("DELETE FROM $table_posts WHERE tid='$tid'");
                    $threaddelete = 'yes';
                }

                if ($numrows > 1) {
                    // delete the old head pid, but leave the rest of the thread intact
                    $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$orig[author]'");
                    $db->query("DELETE FROM $table_attachments WHERE pid='$pid'");
                    $db->query("DELETE FROM $table_posts WHERE pid='$pid'");
                    $db->query("UPDATE $table_posts SET subject='$orig[subject]' WHERE tid='$tid' ORDER BY dateline ASC LIMIT 1");
                    $threaddelete = 'no';
                }

                // update forum and thread count stats
                updateforumcount($fid);
                updatethreadcount($tid);
            }
        } else {
            error($lang['noedit']);
        }
        echo "<center><span class=\"mediumtxt \">$lang[editpostmsg]</span></center>";

        if ( $threaddelete != 'yes') {
            $query =$db->query("SELECT COUNT(pid) FROM $table_posts WHERE pid<=$pid AND tid='$tid' AND fid='$fid'");
            $posts = $db->result($query,0);
            $topicpages = quickpage($posts, $ppp);

            redirect("viewthread.php?tid=${tid}&page=${topicpages}#pid${pid}", 2, X_REDIRECT_JS);
        }else{
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
    }
}else{
    error($lang['textnoaction']);
}

end_time();
eval('echo "'.template('footer').'";');
?>