<?php
/**
 * eXtreme Message Board
 * XMB 1.9.8 Engage Final SP1
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

require 'header.php';

function bbcodeinsert() {
    global $imgdir, $bbinsert, $altbg1, $altbg2, $lang, $SETTINGS, $spelling_lang;

    $bbcode = '';
    if ($SETTINGS['bbinsert'] == 'on') {
        eval('$bbcode = "'.template('functions_bbcodeinsert').'";');
    }
    return $bbcode;
}

loadtemplates(
'post_captcha',
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

$pid = getInt('pid');
$tid = getInt('tid');
$fid = getInt('fid');
$posterror = false;

validatePpp();

$thread = array();
$threadname = '';

if ($tid) {
    $query = $db->query("SELECT fid, subject FROM ".X_PREFIX."threads WHERE tid='$tid' LIMIT 1");
    if ($db->num_rows($query) == 1) {
        $thread = $db->fetch_array($query);
        $threadname = html_entity_decode(stripslashes(htmlspecialchars($thread['subject'])));
        $fid = (int) $thread['fid'];
    } else {
        error($lang['textnothread']);
    }
}

$query = $db->query("SELECT * FROM ".X_PREFIX."forums WHERE fid='$fid'");
$forums = $db->fetch_array($query);
$forums['name'] = stripslashes($forums['name']);

if (($fid == 0 && $tid == 0) || (!isset($forums['type']) && $forums['type'] != 'forum' && $forums['type'] != 'sub' && $forums['fid'] != $fid)) {
    $posterror = $lang['textnoforum'];
}

if (isset($forums['type']) && $forums['type'] == 'forum') {
    nav('<a href="forumdisplay.php?fid='.$fid.'">'.html_entity_decode(stripslashes($forums['name'])).'</a>');
} else {
    if (!isset($forums['fup']) || !is_numeric($forums['fup'])) {
        $posterror = $lang['textnoforum'];
    } else {
        $query = $db->query("SELECT name, fid FROM ".X_PREFIX."forums WHERE fid='$forums[fup]'");
        $fup = $db->fetch_array($query);
        nav('<a href="forumdisplay.php?fid='.$fup['fid'].'">'.html_entity_decode(stripslashes($fup['name'])).'</a>');
        nav('<a href="forumdisplay.php?fid='.$fid.'">'.html_entity_decode(stripslashes($forums['name'])).'</a>');
    }
}

$attachfile = '';
if (isset($forums['attachstatus']) && $forums['attachstatus'] != 'off') {
    eval('$attachfile = "'.template("post_attachmentbox").'";');
}

if (X_GUEST) {
    eval('$loggedin = "'.template('post_notloggedin').'";');
} else {
    eval('$loggedin = "'.template('post_loggedin').'";');
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
    $captchapostcheck = '';
    if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on' && !DEBUG) {
        require ROOT.'include/captcha.inc.php';
        $Captcha = new Captcha(250, 50);
    }

    if (!X_STAFF) {
        $querysmilie = $db->query("SELECT url, code FROM ".X_PREFIX."smilies WHERE type='picon' AND (url NOT LIKE '%rsvd%')");
    } else {
        $querysmilie = $db->query("SELECT url, code FROM ".X_PREFIX."smilies WHERE type='picon'");
    }
    while($smilie = $db->fetch_array($querysmilie)) {
        $icons .= ' <input type="radio" name="posticon" value="'.$smilie['url'].'" /><img src="'.$smdir.'/'.$smilie['url'].'" alt="'.$smilie['code'].'" border="0" />';
        $listed_icons += 1;
        if ($listed_icons == 9) {
            $icons .= '<br />';
            $listed_icons = 0;
        }
    }
    $db->free_result($querysmilie);
}

eval('$bbcodescript = "'.template('functions_bbcode').'";');

if (!isset($usesig)) {
    $usesig = 'no';
}

if ($usesig != 'yes') {
    $usesig = 'no';
}

$chkInputHTML = 'no';
$chkInputTags = 'no';
if (isset($forums['allowhtml']) && $forums['allowhtml'] == 'yes') {
    $chkInputHTML = 'yes';
    $chkInputTags = 'no';
}

$allowimgcode = (isset($forums['allowimgcode']) && $forums['allowimgcode'] == 'yes') ? $lang['texton'] : $lang['textoff'];
$allowhtml = ($chkInputHTML == 'yes') ? $lang['texton'] : $lang['textoff'];
$allowsmilies = (isset($forums['allowsmilies']) && $forums['allowsmilies'] == 'yes') ? $lang['texton'] : $lang['textoff'];
$allowbbcode = (isset($forums['allowbbcode']) && $forums['allowbbcode'] == 'yes') ? $lang['texton'] : $lang['textoff'];
$pperm['type'] = (isset($action) && $action == 'newthread') ? 'thread' : 'reply';

if (!postperm($forums, $pperm['type'])) {
    error($lang['privforummsg']);
}

if (X_GUEST && isset($forums['guestposting']) && $forums['guestposting'] == 'on') {
    $guestpostingmsg = $lang['guestpostingonmsg'];
} else {
    $guestpostingmsg = '';
}

if ($posterror) {
    error($posterror);
}

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

if (isset($subaction) && $subaction == 'spellcheck' && (isset($spellchecksubmit) || isset($spellcheckersubmit))) {
    $sc = true;
} else {
    $sc = false;
}

if ((isset($previewpost) || $sc) && isset($usesig) && $usesig == 'yes') {
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

$repquote = isset($repquote) ? (int) $repquote : 0;
if (isset($poll)) {
    if ($poll != 'yes') {
        $poll = '';
    } else {
        $poll = 'yes';
    }
} else {
    $poll = '';
}

pwverify($forums['password'], 'post.php?action='.$action.'&fid='.$fid.'&tid='.$tid.'&repquote='.$repquote.'&poll='.$poll, $fid);

$query = $db->query("SELECT * FROM ".X_PREFIX."forums WHERE fid='$fid'");
$forum = $db->fetch_array($query);
$authorization = privfcheck($forum['private'], $forum['userlist']);
if (!$authorization) {
    error($lang['privforummsg']);
}

if (!empty($posticon)) {
    $thread['icon'] = (file_exists($smdir.'/'.$posticon)) ? "<img src=\"$smdir/$posticon\" />" : '';
    $icons = str_replace('<input type="radio" name="posticon" value="'.$posticon.'" />', '<input type="radio" name="posticon" value="'.$posticon.'" checked="checked" />', $icons);
} else {
    $thread['icon'] = '';
    $icons = str_replace('<input type="radio" name="posticon" value="" />', '<input type="radio" name="posticon" value="" checked="checked" />', $icons);
}

if ($SETTINGS['spellcheck'] == 'on') {
    $spelling_submit1 = '<input type="hidden" name="subaction" value="spellcheck" /><input type="submit" class="submit" name="spellchecksubmit" value="'.$lang['checkspelling'].'" />';
    $spelling_lang = '<select name="language"><option value="en" selected="selected">English</option></select>';
    if (isset($subaction) && $subaction == 'spellcheck' && (isset($spellchecksubmit) || isset($updates_submit))) {
        if (!$updates_submit) {
            $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
            $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);
            require ROOT.'include/spelling.inc.php';
            $spelling = new spelling($language);
            $problems = $spelling->check_text($message);
            if (count($problems) > 0) {
                foreach($problems as $orig=>$new) {
                    $mistake = array();
                    foreach($new as $suggestion) {
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
                $spelling_submit2 = '';
            }
        } else {
            foreach($old_words as $word) {
                $message = str_replace($word, ${'replace_'.$word}, $message);
            }
            $spelling_submit2 = '';
        }
    } else {
        $suggestions = '';
        $spelling_submit2 = '';
    }
} else {
    $spelling_submit1 = '';
    $spelling_submit2 = '';
    $spelling_lang = '';
    $suggestions = '';
}

if (isset($topicsubmit)) {
    if (!isset($subject) || trim($subject) == '') {
        $preview = error($lang['textnosubject'], false, '', '<br /><br />', false, false, true, false);
        $error = true;
        unset($topicsubmit);
        if (isset($previewpost)) {
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
    $currtime = $onlinetime;
    $date = gmdate($dateformat, $currtime + ($timeoffset * 3600) + ($addtime * 3600));
    $time = gmdate($timecode, $currtime + ($timeoffset * 3600) + ($addtime * 3600));
    $poston = $lang['textposton'].' '.$date.' '.$lang['textat'].' '.$time;

    $subject = html_entity_decode(checkInput($subject, $chkInputTags, $chkInputHTML, '', false));
    $message = html_entity_decode(checkInput($message, $chkInputTags, $chkInputHTML, '', true));
    $message1 = postify($message, $smileyoff, $bbcodeoff, $forums['allowsmilies'], $forums['allowhtml'], $forums['allowbbcode'], $forums['allowimgcode']);
    $dissubject = censor($subject);

    if ($pid > 0) {
        eval('$preview = stripslashes("'.template('post_preview').'");');
    } else {
        eval('$preview = "'.template('post_preview').'";');
    }
} else if ($error) {
    $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
    $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);
} else {
    $preview = '';
    $subject = (isset($subject) ? $subject : '');
    $message = (isset($message) ? $message : '');
}

if ($action == 'newthread') {
    $priv = privfcheck($forums['private'], $forums['userlist']);
    if (isset($poll) && $poll == 'yes') {
        nav($lang['textnewpoll']);
    } else {
        nav($lang['textpostnew']);
    }

    if (!isset($topicsubmit) || !$topicsubmit) {
        if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on' && !DEBUG) {
            if ($Captcha->bCompatible !== false) {
                $imghash = $Captcha->GenerateCode();
                eval('$captchapostcheck = "'.template('post_captcha').'";');
            }
        }

        eval('echo "'.template('header').'";');

        $status1 = modcheck($self['status'], $xmbuser, $forums['moderator']);
        if ($self['status'] == "Super Moderator") {
            $status1 = "Moderator";
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

        if (isset($poll) && $poll == 'yes' && $forums['pollstatus'] != 'off') {
            if (!isset($pollanswers)){
                $pollanswers = '';
            }
            eval('echo stripslashes("'.template('post_newpoll').'");');
        } else {
            eval('echo stripslashes("'.template('post_newthread').'");');
        }
    } else {
        if (!empty($username) && !empty($password)) {
            if (X_GUEST) {
                $password = md5(trim($password));
            }

            $username = trim($username);
            $q = $db->query("SELECT * FROM ".X_PREFIX."members WHERE username='$username'");
            if ($db->num_rows($q) != 1) {
                error($lang['badname']);
            } else {
                $self = $db->fetch_array($q);
                if ($password != $self['password']) {
                    error($lang['textpw1']);
                }
                $username = $self['username'];
            }

            if ($self['status'] == "Banned") {
                error($lang['bannedmessage']);
            }

            $currtime = $onlinetime + (86400*30);
            put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
            put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);

            if ($self['ban'] == "posts" || $self['ban'] == "both") {
                error($lang['textbanfrompost']);
            }
        } else {
            if (X_GUEST) {
                $username = "Anonymous";
            } else {
                $username = $xmbuser;
            }
        }

        if ($username == 'Anonymous' && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on' && !DEBUG) {
            if ($Captcha->bCompatible !== false) {
                $imghash = addslashes($imghash);
                $imgcode = addslashes($imgcode);
                if ($Captcha->ValidateCode($imgcode, $imghash) !== true) {
                    error($lang['captchaimageinvalid']);
                }
            }
        }

        if ($forums['guestposting'] != 'on' && $username == 'Anonymous') {
            error($lang['textnoguestposting']);
        }

        $pperm = explode('|', $forums['postperm']);

        if ($pperm[0] == 2 && !X_ADMIN) {
            error($lang['postpermerr']);
        } else if ($pperm[0] == 3 && !X_STAFF) {
            error($lang['postpermerr']);
        } else if ($pperm[0] == 4) {
            error($lang['postpermerr']);
        }

        $query = $db->query("SELECT lastpost, type, fup FROM ".X_PREFIX."forums WHERE fid='$fid'");
        $for = $db->fetch_array($query);

        if ($for['lastpost'] != '') {
            $lastpost = explode('|', $for['lastpost']);
            $rightnow = $onlinetime - $floodctrl;
            if ($rightnow <= $lastpost[0] && $username == $lastpost[1]) {
                error($lang['floodprotect'].' '.$floodlink.' '.$lang['tocont']);
            }
        }

        if (isset($posticon) && $posticon != '') {
            $query = $db->query("SELECT id FROM ".X_PREFIX."smilies WHERE type='picon' AND url='$posticon'");
            if (!$db->result($query, 0)) {
                exit();
            }
        } else {
            $posticon = '';
        }

        $thatime = $onlinetime;

        $subject = addslashes($subject);
        $message = addslashes($message);

        $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
        $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);

        $db->query("INSERT INTO ".X_PREFIX."threads (fid, subject, icon, lastpost, views, replies, author, closed, topped) VALUES ($fid, '$subject', '$posticon', '$thatime|$username', 0, 0, '$username', '', 0)");
        $tid = $db->insert_id();

        $db->query("INSERT INTO ".X_PREFIX."posts (fid, tid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff) VALUES ($fid, $tid, '$username', '$message', '$subject', ".$db->time($thatime).", '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff')");
        $pid = $db->insert_id();

        $db->query("UPDATE ".X_PREFIX."threads SET lastpost=concat(lastpost, '|".$pid."') WHERE tid='$tid'");

        if (isset($forum['type']) && $forum['type'] == 'sub') {
            $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$thatime|$username|$pid', threads=threads+1, posts=posts+1 WHERE fid='$for[fup]'");
        }

        $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$thatime|$username|$pid', threads=threads+1, posts=posts+1 WHERE fid='$fid'");

        if (X_MEMBER && isset($pollanswers) && isset($forums['pollstatus']) && $forums['pollstatus'] != 'off') {
            $pollanswers = checkInput($pollanswers);
            $pollopts = explode("\n", $pollanswers);
            $pnumnum = count($pollopts);

            if ($pnumnum < 2 && $pollanswers != '') {
                error($lang['too_few_pollopts']);
            }

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

            $db->query("INSERT INTO ".X_PREFIX."vote_desc (topic_id, vote_text) VALUES ($tid, '$subject')");
            $vote_id =  $db->insert_id();
            $i = 1;
            foreach($pollopts as $p) {
                $p = addslashes($p);
                $db->query("INSERT INTO ".X_PREFIX."vote_results (vote_id, vote_option_id, vote_option_text, vote_result) VALUES ($vote_id, $i, '$p', 0)");
                $i++;
            }
            $db->query("UPDATE ".X_PREFIX."threads SET pollopts=1 WHERE tid='$tid'");
        }

        if (isset($emailnotify) && $emailnotify == 'yes') {
            $query = $db->query("SELECT tid FROM ".X_PREFIX."favorites WHERE tid='$tid' AND username='$xmbuser' AND type='subscription'");
            $thread = $db->fetch_array($query);
            if (!$thread) {
                $db->query("INSERT INTO ".X_PREFIX."favorites (tid, username, type) VALUES ($tid, '$username', 'subscription')");
            }
        }

        $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum+1 WHERE username like '$username'");

        if ((X_STAFF) && $toptopic == 'yes') {
            $db->query("UPDATE ".X_PREFIX."threads SET topped='1' WHERE tid='$tid' AND fid='$fid'");
        }

        if ((X_STAFF) && $closetopic == 'yes') {
            $db->query("UPDATE ".X_PREFIX."threads SET closed='yes' WHERE tid='$tid' AND fid='$fid'");
        }

        eval('echo "'.template('header').'";');

        if (isset($_FILES['attach']) && ($attachedfile = get_attached_file($_FILES['attach'], $forums['attachstatus'], $SETTINGS['maxattachsize'])) !== false) {
            $db->query("INSERT INTO ".X_PREFIX."attachments (tid, pid, filename, filetype, filesize, attachment, downloads) VALUES ($tid, $pid, '$filename', '$filetype', '$filesize', '$attachedfile', 0)");
        }

        $query = $db->query("SELECT COUNT(tid) FROM ".X_PREFIX."posts WHERE tid='$tid'");
        $posts = $db->result($query, 0);

        $topicpages = quickpage($posts, $ppp);
        message($lang['postmsg'], false, '', '', "viewthread.php?tid=".$tid."&page=".$topicpages."#pid".$pid, true, false, true);
    }
} else if ($action == 'reply') {
    nav('<a href="viewthread.php?tid='.$tid.'">'.$threadname.'</a>');
    nav($lang['textreply']);

    $priv = privfcheck($forums['private'], $forums['userlist']);
    if (!isset($replysubmit) || !$replysubmit) {
        if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on' && !DEBUG) {
            if ($Captcha->bCompatible !== false) {
                $imghash = $Captcha->GenerateCode();
                eval('$captchapostcheck = "'.template('post_captcha').'";');
            }
        }

        $posts = '';
        eval('echo "'.template('header').'";');

        if (X_STAFF) {
            $closeoption = '<br /><input type="checkbox" name="closetopic" value="yes" '.$closecheck.' /> '.$lang['closemsgques'].'<br />';
        } else {
            $closeoption = '';
        }

        if (isset($repquote) && ($repquote = (int) $repquote)) {
            $query = $db->query("SELECT p.message, p.fid, p.author, f.private AS fprivate, f.userlist AS fuserlist, f.password AS fpassword FROM ".X_PREFIX."posts p, ".X_PREFIX."forums f WHERE p.pid=$repquote AND f.fid=p.fid");
            $thaquote = $db->fetch_array($query);
            $quotefid = $thaquote['fid'];
            $pass = trim($thaquote['fpassword']);

            if (!X_ADMIN && trim($pass) != '' && $_COOKIE['fidpw'.$quotefid] != $pass) {
               error($lang['privforummsg'], false);
            }

            $authorization = privfcheck($thaquote['fprivate'], $thaquote['fuserlist']);
            if (!$authorization) {
                error($lang['privforummsg'], false);
            }
            $message = html_entity_decode("[quote][i]$lang[origpostedby] $thaquote[author][/i]\n$thaquote[message] [/quote]");
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

                $post['message'] = postify($post['message'], $post['smileyoff'], $post['bbcodeoff'], $forums['allowsmilies'], $forums['allowhtml'], $forums['allowbbcode'], $forums['allowimgcode']);
                eval('$posts .= "'.template('post_reply_review_post').'";');
                if ($thisbg == $altbg2) {
                    $thisbg = $altbg1;
                } else {
                    $thisbg = $altbg2;
                }
            }
        }

        $attachfile = '';
        if (isset($forums['attachstatus']) && $forums['attachstatus'] == 'on') {
            eval('$attachfile = "'.template('post_attachmentbox').'";');
        }
        eval('echo stripslashes("'.template('post_reply').'");');
    } else {
        if (!$subject && !$message) {
            error($lang['postnothing']);
        }

        if (X_MEMBER && empty($username) && empty($password)) {
            $username = $xmbuser;
            $password = $xmbpw;
        }

        if (!empty($username) && !empty($password)) {
            if (X_GUEST) {
                $username = trim($username);
                $password = md5(trim($password));
            }

            $q = $db->query("SELECT * FROM ".X_PREFIX."members WHERE username='$username'");
            if ($db->num_rows($q) != 1) {
                error($lang['badname']);
            } else {
                $self = $db->fetch_array($q);
                if ($password != $self['password']) {
                    error($lang['textpw1']);
                }
                $username = $self['username'];
            }

            if ($self['status'] == "Banned") {
                error($lang['bannedmessage']);
            }

            $currtime = $onlinetime + (86400*30);
            put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
            put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);

            if ($self['ban'] == 'posts' || $self['ban'] == 'both') {
                error($lang['textbanfrompost']);
            }
        } else {
            if (X_GUEST) {
                $username = 'Anonymous';
            } else {
                $username = $xmbuser;
            }
        }

        if ($username == 'Anonymous' && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_post_status'] == 'on' && !DEBUG) {
            if ($Captcha->bCompatible !== false) {
                $imghash = addslashes($imghash);
                $imgcode = addslashes($imgcode);
                if ($Captcha->ValidateCode($imgcode, $imghash) !== true) {
                    error($lang['captchaimageinvalid']);
                }
            }
        }

        if ($forums['guestposting'] != 'on' && $username == 'Anonymous') {
            error($lang['textnoguestposting']);
        }

        $pperm = explode('|', $forums['postperm']);
        if ($pperm[1] == 2 && !X_ADMIN) {
            error($lang['postpermerr']);
        } else if ($pperm[1] == 3 && !X_STAFF) {
            error($lang['postpermerr']);
        } else if ($pperm[1] == 4) {
            error($lang['postpermerr']);
        }

        if (isset($posticon) && $posticon != "") {
            $query = $db->query("SELECT id FROM ".X_PREFIX."smilies WHERE type='picon' AND url='$posticon'");
            if (!$db->result($query, 0)) {
                exit();
            }
        } else {
            $posticon = '';
        }

        $query = $db->query("SELECT lastpost, type, fup FROM ".X_PREFIX."forums WHERE fid='$fid'");
        $for = $db->fetch_array($query);
        $last = $for['lastpost'];

        if ($last != '') {
            $lastpost = explode('|', $last);
            $rightnow = $onlinetime - $floodctrl;
            if ($rightnow <= $lastpost[0] && $username == $lastpost[1]) {
                $floodlink = "<a href=\"viewthread.php?fid=$fid&tid=$tid\">Click here</a>";
                error($lang['floodprotect'].' '.$floodlink.' '.$lang['tocont']);
            }
        }

        if ($usesig != "yes") {
            $usesig = "no";
        }

        $subject = addslashes($subject);
        $message = addslashes($message);

        $query = $db->query("SELECT closed, topped FROM ".X_PREFIX."threads WHERE fid='$fid' AND tid='$tid'");
        $closed1 = $db->fetch_array($query);
        $closed = $closed1['closed'];
        if ($closed == 'yes' && !X_STAFF) {
            error($lang['closedmsg']);
        } else {
            $thatime = $onlinetime;
            $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
            $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);
            $db->query("INSERT INTO ".X_PREFIX."posts (fid, tid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff) VALUES ($fid, $tid, '$username', '$message', '$subject', ".$db->time(time()).", '$posticon', '$usesig', '$onlineip', '$bbcodeoff', '$smileyoff')");
            $pid = $db->insert_id();

            if ((X_STAFF) && $closetopic == 'yes') {
                $db->query("UPDATE ".X_PREFIX."threads SET closed='yes' WHERE tid='$tid' AND fid='$fid'");
            }

            $db->query("UPDATE ".X_PREFIX."threads SET lastpost='$thatime|$username|$pid', replies=replies+1 WHERE (tid='$tid' AND fid='$fid') OR closed='moved|$tid'");

            if ($for['type'] == 'sub') {
                $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$thatime|$username|$pid', posts=posts+1 WHERE fid='$for[fup]'");
            }

            $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$thatime|$username|$pid', posts=posts+1 WHERE fid='$fid'");
            $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum+1 WHERE username='$username'");

            $query = $db->query("SELECT COUNT(pid) FROM ".X_PREFIX."posts WHERE pid <= $pid AND tid='$tid'");
            $posts = $db->result($query,0);

            if ($posts > $ppp) {
                $topicpages = quickpage($posts, $ppp);
            } else {
                $topicpages = 1;
            }

            $date = $db->result($db->query("SELECT dateline FROM ".X_PREFIX."posts WHERE tid='$tid' AND pid < $pid ORDER BY pid ASC LIMIT 1"), 0);
            $subquery = $db->query("SELECT m.email, m.lastvisit, m.ppp, m.status FROM ".X_PREFIX."favorites f LEFT JOIN ".X_PREFIX."members m ON (m.username=f.username) WHERE f.type='subscription' AND f.tid='$tid' AND f.username!= '$username'");
            while($subs = $db->fetch_array($subquery)) {
                if ($subs['status'] == 'banned' || $subs['lastvisit'] < $date) {
                    continue;
                }

                if ($subs['ppp'] < 1) {
                    $subs['ppp'] = $posts;
                }

                $topicpages = quickpage($posts, $subs['ppp']);
                $threadurl = $SETTINGS['boardurl'] . 'viewthread.php?tid='.$tid.'&page='.$topicpages.'#pid'.$pid;
                altMail($subs['email'], $lang['textsubsubject'].' '.$threadname, $username.' '.$lang['textsubbody']." \n".$threadurl, "From: $bbname <$adminemail>");
            }

            if (isset($emailnotify) && $emailnotify == 'yes') {
                $query = $db->query("SELECT tid FROM ".X_PREFIX."favorites WHERE tid='$tid' AND username='$xmbuser' AND type='subscription'");
                if ($db->num_rows($query) < 1) {
                    $db->query("INSERT INTO ".X_PREFIX."favorites (tid, username, type) VALUES ($tid, '$username', 'subscription')");
                }
            }

            eval('echo "'.template('header').'";');

            if (isset($_FILES['attach']) && ($attachedfile = get_attached_file($_FILES['attach'], $forums['attachstatus'], $SETTINGS['maxattachsize'])) !== false) {
                $db->query("INSERT INTO ".X_PREFIX."attachments (tid, pid, filename, filetype, filesize, attachment, downloads) VALUES ($tid, $pid, '$filename', '$filetype', '$filesize', '$attachedfile', 0)");
            }
        }

        if (X_MEMBER) {
            $currtime = $onlinetime + (86400*30);
            put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
            put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
        }

        message($lang['replymsg'], false, '', '', 'viewthread.php?tid='.$tid.'&page='.$topicpages.'#pid'.$pid, true, false, true);
    }
} else if ($action == 'edit') {
    nav('<a href="viewthread.php?tid='.$tid.'">'.html_entity_decode($threadname).'</a>');
    nav($lang['texteditpost']);

    if (!isset($editsubmit)) {
        eval('echo "'.template('header').'";');
        $queryextra = $db->query("SELECT f.* FROM ".X_PREFIX."posts p LEFT JOIN ".X_PREFIX."forums f ON (f.fid=p.fid) WHERE p.tid='$tid' AND p.pid='$pid'");
        $forum = $db->fetch_array($queryextra);

        $authorization = privfcheck($forum['private'], $forum['userlist']);
        if (!$authorization) {
            $header = '';
            error($lang['privforummsg']);
        }

        if (isset($previewpost) || (isset($subaction) && $subaction == 'spellcheck' && (isset($spellchecksubmit) || isset($updates_submit)))) {
            $postinfo = array("usesig"=>$usesig, "bbcodeoff"=>$bbcodeoff, "smileyoff"=>$smileyoff, "message"=>$message, "subject"=>$subject, 'icon'=>$posticon);
            $query = $db->query("SELECT filename, filesize, downloads FROM ".X_PREFIX."attachments WHERE pid='$pid' AND tid='$tid'");
            if ($db->num_rows($query) > 0) {
                $postinfo = array_merge($postinfo, $db->fetch_array($query));
            }
        } else {
            $query = $db->query("SELECT a.filename, a.filesize, a.downloads, p.* FROM ".X_PREFIX."posts p LEFT JOIN ".X_PREFIX."attachments a  ON (a.pid=p.pid) WHERE p.pid='$pid' AND p.tid='$tid' AND p.fid=".$forum['fid']);
            $postinfo = $db->fetch_array($query);
        }

        if (isset($postinfo['filesize'])) {
            $postinfo['filesize'] = number_format($postinfo['filesize'], 0, '.', ',');
        }

        $postinfo['subject'] = html_entity_decode($postinfo['subject']);
        $postinfo['message'] = html_entity_decode(stripslashes($postinfo['message']));

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

        if (!X_STAFF) {
            $querysmilie = $db->query("SELECT * FROM ".X_PREFIX."smilies WHERE type='picon' AND (url NOT LIKE '%rsvd%')");
            while($smilie = $db->fetch_array($querysmilie)) {
                if ($postinfo['icon'] == $smilie['url']) {
                    $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" checked=\"checked\"/><img src=\"$smdir/$smilie[url]\" alt=\"$smilie[code]\" />";
                } else {
                    $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$smdir/$smilie[url]\" alt=\"$smilie[code]\" />";
                }
                $listed_icons += 1;
                if ($listed_icons == 9) {
                    $icons .= "<br />";
                    $listed_icons = 0;
                }
            }
        } else {
            $querysmilie = $db->query("SELECT * FROM ".X_PREFIX."smilies WHERE type='picon'");
            while($smilie = $db->fetch_array($querysmilie)) {
                if ($postinfo['icon'] == $smilie['url']) {
                    $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" checked=\"checked\"/><img src=\"$smdir/$smilie[url]\" alt=\"$smilie[code]\" />";
                } else {
                    $icons .= " <input type=\"radio\" name=\"posticon\" value=\"$smilie[url]\" /><img src=\"$smdir/$smilie[url]\" alt=\"$smilie[code]\" />";
                }
                $listed_icons += 1;
                if ($listed_icons == 9) {
                    $icons .= '<br />';
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

        if (isset($postinfo['filename']) && $postinfo['filename'] != '') {
            eval('$attachment = "'.template('post_edit_attachment').'";');
        } else {
            $attachment = $attachfile;
        }
        eval('echo "'.template('post_edit').'";');
    } else {
        if (X_GUEST) {
            $username = trim($username);
            $password = md5(trim($password));
        }

        $q = $db->query("SELECT * FROM ".X_PREFIX."members WHERE username='$username'");
        if ($db->num_rows($q) != 1) {
            error($lang['badname']);
        } else {
            $self = $db->fetch_array($q);
            if ($password != $self['password']) {
                error($lang['textpw1']);
            }
            $username = $self['username'];
        }

        if ($self['status'] == "Banned") {
            error($lang['bannedmessage']);
        }

        $currtime = $onlinetime + (86400*30);
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
        if ($self['status'] == "Super Moderator") {
            $status1 = "Moderator";
        }

        if (isset($posticon) && $posticon != "") {
            $query = $db->query("SELECT id FROM ".X_PREFIX."smilies WHERE type='picon' AND url='$posticon'");
            if (!$db->result($query, 0)) {
                exit();
            }
        } else {
            $posticon = '';
        }

        $query = $db->query("SELECT pid FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY dateline LIMIT 1");
        $isfirstpost = $db->fetch_array($query);

        if ((trim($subject) == '' && $pid == $isfirstpost['pid']) && !(isset($delete) && $delete == 'yes')) {
            error($lang['textnosubject']);
        }

        $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);
        $message = checkInput($message, $chkInputTags, $chkInputHTML, '', true);
        $posticon = htmlspecialchars($posticon);

        $query = $db->query("SELECT p.author as author, m.status as status, p.subject as subject FROM ".X_PREFIX."posts p LEFT JOIN ".X_PREFIX."members m ON p.author=m.username WHERE pid='$pid' AND tid='$tid' AND fid='$fid'");
        $orig = $db->fetch_array($query);
        $db->free_result($query);

        $message = addslashes($message);

        if ((X_STAFF && $status1 == 'Moderator') || $username == $orig['author']) {
            if ($SETTINGS['allowrankedit'] != 'off') {
                switch($orig['status']) {
                    case 'Super Administrator':
                        if (!X_SADMIN && $xmbuser != $orig['author']) {
                            error($lang['noedit']);
                        }
                        break;
                    case 'Administrator':
                        if (!X_ADMIN && $xmbuser != $orig['author']) {
                            error($lang['noedit']);
                        }
                        break;
                    case 'Super Moderator':
                        if ((!X_ADMIN && $self['status'] != 'Super Moderator') && $xmbuser != $orig['author']) {
                            error($lang['noedit']);
                        }
                        break;
                    case 'Moderator':
                        if ((!X_ADMIN && $self['status'] != 'Moderator') && $xmbuser != $orig['author']) {
                            error($lang['noedit']);
                        }
                        break;
                }
            }

            if ($isfirstpost['pid'] == $pid && !(isset($delete) && $delete == 'yes')) {
                $db->query("UPDATE ".X_PREFIX."threads SET icon='$posticon', subject='$subject' WHERE tid='$tid'");
            }

            $threaddelete = 'no';
            eval('echo "'.template('header').'";');

            $db->query("UPDATE ".X_PREFIX."posts SET message='$message', usesig='$usesig', bbcodeoff='$bbcodeoff', smileyoff='$smileyoff', icon='$posticon', subject='$subject' WHERE pid='$pid'");

            if (isset($_FILES['attach']) && ($file = get_attached_file($_FILES['attach'], $forums['attachstatus'], $SETTINGS['maxattachsize'])) !== false) {
                $db->query("INSERT INTO ".X_PREFIX."attachments (tid, pid, filename, filetype, filesize, attachment, downloads) VALUES ($tid, $pid, '$filename', '$attach[type]', '$filesize', '$file', 0)");
            }

            if (isset($attachment) && is_array($attachment)) {
                switch($attachment['action']) {
                    case 'replace':
                        if (isset($_FILES['attachment_replace']) && ($file = get_attached_file($_FILES['attachment_replace'], $forums['attachstatus'], $SETTINGS['maxattachsize'])) !== false) {
                            $db->query("DELETE FROM ".X_PREFIX."attachments WHERE pid='$pid'");
                            $db->query("INSERT INTO ".X_PREFIX."attachments (tid, pid, filename, filetype, filesize, attachment, downloads) VALUES ($tid, $pid, '$filename', '$attachment_replace[type]', '$filesize', '$file', 0)");
                        }
                        break;
                    case 'rename':
                        $name = basename($attach_name);
                        if (strlen(trim($name)) > 2 || preg_match('#^[^a-z0-9]+$#', $name) == 1) {
                            break;
                        } else {
                            $db->query("UPDATE ".X_PREFIX."attachments SET filename='$name' WHERE pid='$pid'");
                        }
                        break;
                    case 'delete':
                        $db->query("DELETE FROM ".X_PREFIX."attachments WHERE pid='$pid'");
                        break;
                    default:
                        break;
                }
            }

            if (isset($delete) && $delete == 'yes' && !($isfirstpost['pid'] == $pid)) {
                $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-1 WHERE username='$orig[author]'");
                $db->query("DELETE FROM ".X_PREFIX."attachments WHERE pid='$pid'");
                $db->query("DELETE FROM ".X_PREFIX."posts WHERE pid='$pid'");
                updatethreadcount($tid);
                updateforumcount($fid);
            } else if (isset($delete) && $delete == 'yes' && $isfirstpost['pid'] == $pid) {
                $query = $db->query("SELECT pid FROM ".X_PREFIX."posts WHERE tid='$tid'");
                $numrows = $db->num_rows($query);
                $db->free_result($query);

                if ($numrows == 1) {
                    $query = $db->query("SELECT author FROM ".X_PREFIX."posts WHERE tid='$tid'");
                    while($result = $db->fetch_array($query)) {
                        $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-1 WHERE username='$result[author]'");
                    }
                    $db->free_result($query);
                    $db->query("DELETE FROM ".X_PREFIX."threads WHERE tid='$tid'");
                    $db->query("DELETE FROM ".X_PREFIX."attachments WHERE tid='$tid'");
                    $db->query("DELETE FROM ".X_PREFIX."posts WHERE tid='$tid'");
                    $threaddelete = 'yes';
                }

                if ($numrows > 1) {
                    $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-1 WHERE username='$orig[author]'");
                    $db->query("DELETE FROM ".X_PREFIX."attachments WHERE pid='$pid'");
                    $db->query("DELETE FROM ".X_PREFIX."posts WHERE pid='$pid'");
                    $db->query("UPDATE ".X_PREFIX."posts SET subject='$orig[subject]' WHERE tid='$tid' ORDER BY dateline ASC LIMIT 1");
                    $threaddelete = 'no';
                }
                updatethreadcount($tid);
                updateforumcount($fid);
            }
        } else {
            error($lang['noedit']);
        }

        if ($threaddelete != 'yes') {
            $query = $db->query("SELECT COUNT(pid) FROM ".X_PREFIX."posts WHERE pid <= '$pid' AND tid='$tid' AND fid='$fid'");
            $posts = $db->result($query,0);
            $topicpages = quickpage($posts, $ppp);
            message($lang['editpostmsg'], false, '', '', "viewthread.php?tid=${tid}&page=${topicpages}#pid${pid}", true, false, true);
        } else {
            message($lang['editpostmsg'], false, '', '', 'forumdisplay.php?fid='.$fid, true, false, true);
        }
    }
} else {
    error($lang['textnoaction']);
}

end_time();
eval('echo "'.template('footer').'";');
?>