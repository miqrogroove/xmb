<?php
/* $Id: viewthread.php,v 1.3.2.34 2007/03/20 01:13:18 Tularis Exp $ */
/*
    © 2001 - 2007 Aventure Media & The XMB Development Team
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

require "./header.php";

validatePpp();

// validation of tid and fid. If they don't exist, they will after this, allowing
// us to drop most tid, fid validation post these lines.
$pid = (isset($pid) && is_numeric($pid)) ? (int) $pid : 0;
$tid = (isset($tid) && is_numeric($tid)) ? (int) $tid : 0;
$fid = (isset($fid) && is_numeric($fid)) ? (int) $fid : 0;
$page = (isset($page) && is_numeric($page)) ? (int) $page : 1;

if (isset($goto) && $goto == "lastpost") {
    if ($pid > 0) {
        if($tid == 0) {
            $tid = $db->result($db->query("SELECT tid FROM $table_posts WHERE pid=$pid"), 0);
        }
        $query = $db->query("SELECT count(pid) as num FROM $table_posts WHERE tid=$tid AND pid <= $pid");
        $posts = $db->result($query, 0);
        $db->free_result($query);

        if ($posts == 0) {
            eval("\$css = \"".template("css")."\";");
            error($lang['textnothread']);
        }

    } elseif ($tid > 0) {
        $query = $db->query("SELECT count(pid) FROM $table_posts WHERE tid='$tid'");
        $posts = $db->result($query, 0);
        $db->free_result($query);

        if ($posts == 0) {
            eval("\$css = \"".template("css")."\";");
            error($lang['textnothread']);
        }

        $query = $db->query("SELECT pid FROM $table_posts WHERE tid='$tid' ORDER BY pid DESC LIMIT 0,1");
        $pid = $db->result($query, 0);
        $db->free_result($query);

    } elseif ($fid > 0) {
        $query = $db->query("SELECT pid, tid FROM $table_posts WHERE fid='$fid' ORDER BY pid DESC LIMIT 0,1");
        $posts = $db->fetch_array($query);
        $db->free_result($query);

        $pid = $posts['pid'];
        $tid = $posts['tid'];

        $query = $db->query("SELECT p.pid, p.tid FROM $table_posts p, $table_forums f WHERE p.fid = f.fid and (f.fup='$fid') ORDER BY p.pid DESC LIMIT 0,1");
        $fupPosts = $db->fetch_array($query);
        $db->free_result($query);

        if ($fupPosts['pid'] > $pid) {
            $pid = $fupPosts['pid'];
            $tid = $fupPosts['tid'];
        }

        $query = $db->query("SELECT count(pid) FROM $table_posts WHERE tid='$tid'");
        $posts = $db->result($query, 0);
        $db->free_result($query);
    }

    $page = quickpage($posts, $self['ppp']);
    redirect("viewthread.php?tid=$tid&page=$page#pid$pid", 0);
}

// Actual viewthread.php page
loadtemplates('functions_bbcode','functions_smilieinsert_smilie', 'viewthread_reply','viewthread_quickreply','viewthread','viewthread_invalid','viewthread_modoptions','viewthread_newpoll','viewthread_newtopic','viewthread_poll_options_view','viewthread_poll_options','viewthread_poll_submitbutton','viewthread_poll','viewthread_post','viewthread_post_email','viewthread_post_site','viewthread_post_icq','viewthread_post_aim','viewthread_post_msn','viewthread_post_yahoo','viewthread_post_search','viewthread_post_profile','viewthread_post_u2u','viewthread_post_ip','viewthread_post_repquote','viewthread_post_report','viewthread_post_edit','viewthread_post_attachmentimage','viewthread_post_attachment','viewthread_post_sig','viewthread_post_nosig','viewthread_printable','viewthread_printable_row', 'viewthread_multipage');
smcwcache();
eval("\$css = \"".template("css")."\";");

$notexist = false;
$notexist_txt = '';
$posts = '';

$query = $db->query("SELECT fid, subject, closed, topped, pollopts, lastpost FROM $table_threads WHERE tid='$tid'");
if ($tid == 0 || $db->num_rows($query) != 1 ) {
    $db->free_result($query);
    error($lang['textnoforum']);
}

$thread = $db->fetch_array($query);
$db->free_result($query);

$fid = $thread['fid'];
$fq = $db->query("SELECT * FROM $table_forums WHERE fid=$fid");

if($db->num_rows($fq) < 1) {
    error($lang['textnoforum']);
}

$forum = $db->fetch_array($fq);

if($forum['type'] != "forum" && $forum['type'] != "sub") {
    $db->free_result($query);
    error($lang['textnoforum']);
}

// Check for authorization to be here in the first place
$PERMISSIONS = checkForumPermissions($forum);
if(!$PERMISSIONS[X_PERMS_VIEW] || !$PERMISSIONS[X_PERMS_USERLIST]) {
    error($lang['privforummsg']);
} elseif(!$PERMISSIONS[X_PERMS_PASSWORD]) {
    handlePasswordDialog($fid, basename(__FILE__), array());
}

if(strpos($thread['closed'], '|') !== false) {
    $moved = explode('|', $thread['closed']);
    if($moved[0] == 'moved') {
        redirect('forumdisplay.php?tid='.$moved[1], 0);
    }
}

// if subject has MAX $table_thread[subject] size (128 chars) assume it is actually longer
$thread['subject'] = shortenString($thread['subject'], 125, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...');
$thread['subject'] = checkOutput($thread['subject'], 'no', '', true);

$thislast = explode('|', $thread['lastpost']);
$lastPid = isset($thislast[2]) ? $thislast[2] : 0;
if(!isset($oldtopics)) {
    put_cookie('oldtopics', '|'.$lastPid.'|', time()+600, $cookiepath, $cookiedomain, null, X_SET_HEADER);
} elseif(false === strpos($oldtopics, '|'.$lastPid.'|')) {
    $expire = time() + 600;
    $oldtopics .= $lastPid.'|';
    put_cookie('oldtopics', $oldtopics, $expire, $cookiepath, $cookiedomain, null, X_SET_HEADER);
}

$thread['subject'] = censor($thread['subject']);

if ($forum['type'] == 'sub') {
    $query = $db->query("SELECT name, fid, userlist, password, postperm FROM $table_forums WHERE fid='$forum[fup]'");
    $fup = $db->fetch_array($query);
    $db->free_result($query);

    $fupPerms = checkForumPermissions($fup);
    if(!$fupPerms[X_PERMS_VIEW] || !$fupPerms[X_PERMS_USERLIST]) {
        error($lang['privforummsg']);
    } elseif(!$fupPerms[X_PERMS_PASSWORD]) {
        handlePasswordDialog($fup['fid'], basename(__FILE__), $_GET);
    }
}

$ssForumName = stripslashes($forum['name']);

if ($forum['type'] == "forum") {
    nav('<a href="forumdisplay.php?fid='.$fid.'"> '.$ssForumName.'</a>');
    nav(checkOutput(stripslashes($thread['subject']), 'no', '', true));
} else {
    nav('<a href="forumdisplay.php?fid='.$fup['fid'].'">'.stripslashes($fup['name']).'</a>');
    nav('<a href="forumdisplay.php?fid='.$fid.'">'.$ssForumName.'</a>');
    nav(checkOutput(stripslashes($thread['subject']), 'no', '', true));
}

$allowimgcode   = ($forum['allowimgcode'] == 'yes') ? $lang['texton']:$lang['textoff'];
$allowhtml      = ($forum['allowhtml'] == 'yes')    ? $lang['texton']:$lang['textoff'];
$allowsmilies   = ($forum['allowsmilies'] == 'yes') ? $lang['texton']:$lang['textoff'];
$allowbbcode    = ($forum['allowbbcode'] == 'yes')  ? $lang['texton']:$lang['textoff'];

eval("\$bbcodescript = \"".template('functions_bbcode')."\";");
if ($SETTINGS['smileyinsert'] == 'on' && $smiliesnum > 0) {
    $max = ($smiliesnum > 16) ? 16 : $smiliesnum;

    srand((double)microtime() * 1000000);
    $keys = array_rand($smiliecache, $max);

    $smilies = array();
    $smilies[] = '<table border="0"><tr>';
    $i = 0;
    $total = 0;
    $pre = 'opener.';
    foreach ($keys as $key) {
        if ($total == 16) {
            break;
        }
        $smilie['code'] = $key;
        $smilie['url'] = $smiliecache[$key];

        if ($i >= 4) {
            $smilies[] = '</tr><tr>';
            $i = 0;
        }
        eval("\$smilies[] = \"".template('functions_smilieinsert_smilie')."\";");
        $i++;
        $total++;
    }
    $smilies[] = '</tr></table>';
    $smilies = implode("\n", $smilies);
}

$usesig = false;
$replylink = '';
$quickreply = '';

$isModerator = false;
if($self['status'] == 'Moderator') {
    // need to check if it's a moderator for this specific forum
    $isModerator = false;
    $mods = explode(',', $forum['moderator']);
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

if (!$action) {
    if (X_MEMBER && $self['sig'] != '') {
        $usesig = true;
    }

    eval('echo "'.template('header').'";');

    $usesigcheck    = $usesig ? 'checked="checked"' : '';
    $codeoffcheck   = (isset($bbcodeoff) && $bbcodeoff == 'yes') ? 'checked="checked"' : '';
    $smileoffcheck  = (isset($smileyoff) && $smileyoff == 'yes') ? 'checked="checked"' : '';

    if ($thread['closed'] == 'yes') {
        if (X_SADMIN) {
            eval("\$replylink = \"".template('viewthread_reply')."\";");
            eval("\$quickreply = \"".template('viewthread_quickreply')."\";");
        } else {
            $replylink = '';
            $quickreply = '';
        }
        $closeopen = $lang['textopenthread'];
    } else {
        $closeopen = $lang['textclosethread'];
        if($PERMISSIONS[X_PERMS_REPLY]) {
            eval('$replylink = "'.template('viewthread_reply').'";');
            eval('$quickreply = "'.template('viewthread_quickreply').'";');
        } else {
            $replylink = '';
            $quickreply = '';
        }
    }

    if($PERMISSIONS[X_PERMS_THREAD]) {
        eval('$newtopiclink = "'.template('viewthread_newtopic').'";');
        if($PERMISSIONS[X_PERMS_POLL]) {
            eval('$newpolllink = "'.template('viewthread_newpoll').'";');
        } else {
            $newpolllink = '';
        }
    } else {
        $newtopiclink = '';
        $newpolllink = '';
    }

    $topuntop = ($thread['topped'] == 1) ? $lang['textuntopthread'] : $lang['texttopthread'];

    if (isset($page)) {
        if ( $page < 1 ) {
            $page = 1;
        }
        $start_limit = ($page-1) * $self['ppp'];
    } else {
        $start_limit = 0;
        $page = 1;
    }

    // Query for user ranks. We do this only once now.  -Aharon
    $specialrank = array();
    $rankposts = array();

    $queryranks = $db->query("SELECT id,title,posts,stars,allowavatars,avatarrank FROM $table_ranks");
    while ($query = $db->fetch_row($queryranks)) {
        $title = $query[1];
        $rposts= $query[2];

        if ($title == 'Super Administrator' || $title == 'Administrator' || $title == 'Super Moderator' || $title == 'Moderator') {
            $specialrank[$title] = "$query[0],$query[1],$query[2],$query[3],$query[4],$query[5]";
        } else {
            $rankposts[$rposts]  = "$query[0],$query[1],$query[2],$query[3],$query[4],$query[5]";
        }
    }
    $db->free_result($queryranks);
    // End user rank query.

    $db->query("UPDATE $table_threads SET views=views+1 WHERE tid='$tid'");
    $query = $db->query("SELECT count(pid) FROM $table_posts WHERE tid='$tid'");
    $num = $db->result($query, 0);
    $db->free_result($query);

    $mpurl = "viewthread.php?tid=$tid";

    $multipage = '';
    if (($multipage = multi($num, $self['ppp'], $page, $mpurl)) !== false) {
        eval('$multipage = "'.template('viewthread_multipage').'";');
    }

    $that = array();
    $poll = '';

    // Start polls
    if ($thread['pollopts'] != '' && $thread['closed'] != 'yes') {
        $pollbar = '';
        $num = array();
        $pollhtml = '';

        $options = explode("#|#", $thread['pollopts']);
        $num_options = count($options);

        if (false !== strpos(' '.$options[$num_options-1].' ', ' '.$xmbuser.' ') || $viewresults == 'yes') {
            //show the 'voted' look
            if ($viewresults == 'yes') {
                $results = "[<a href=\"./viewthread.php?tid=$tid\">$lang[backtovote]</a>]";
            } else {
                $results = '';
            }

            $num_votes = 0;

            for ($i=0; $i < ($num_options-1); $i++) {
                $that = explode('||~|~||', $options[$i]);
                $num_votes += $that[1];
                $poll[$i]['name'] = postify($that[0], 'no', 'no', 'yes', 'no', 'yes', 'yes');
                $poll[$i]['votes'] = $that[1];
            }

            foreach ($poll as $num=>$array) {
                $pollimgnum = 0;
                $pollbar = '';

                if ($array['votes'] > 0) {
                    $orig = round($array['votes']/$num_votes*100, 2);
                    $percentage = round($orig, 2);
                    $poll_length = round($orig/3, 2);
                    $pollbar = str_repeat('<img src="'.$THEME['imgdir'].'/pollbar.gif" alt="'.$lang['altpollpercentage'].'" />', $poll_length);
                    $percentage .= '%';
                } else {
                    $percentage = '0%';
                }
                eval("\$pollhtml .= \"".template('viewthread_poll_options_view')."\";");

            }

            $buttoncode = '';
        } else {
            $results = '[<a href="./viewthread.php?tid='.$tid.'&viewresults=yes">'.$lang['viewresults'].'</a>]';
            for ($i=0;$i<($num_options-1);$i++) {
                $that = explode('||~|~||', $options[$i]);
                $poll['name'] = postify($that[0], 'no', 'no', 'yes', 'no', 'yes', 'yes');
                eval("\$pollhtml .= \"".template('viewthread_poll_options')."\";");
            }
            eval("\$buttoncode = \"".template('viewthread_poll_submitbutton')."\";");
        }
        eval("\$poll = \"".template('viewthread_poll')."\";");

    } elseif ($thread['closed'] == 'yes' && $thread['pollopts'] != '') {
        $pollbar = '';
        $pollhtml = '';

        $options = explode("#|#", $thread['pollopts']);
        $num_options = count($options);
        $num_votes = 0;

        for ($i=0; $i < ($num_options-1); $i++) {
            $that = explode('||~|~||', $options[$i]);
            $num_votes += $that[1];
            $poll[$i]['name'] = postify($that[0], 'no', 'no', 'yes', 'no', 'yes', 'yes');
            $poll[$i]['votes'] = $that[1];
        }

        foreach ($poll as $array) {
            $pollimgnum = 0;
            $pollbar = '';

            if ($array['votes'] > 0) {
                $percentage = round($array['votes']/$num_votes*100,2);
                for($num = 0; $num < floor($percentage/3); $num++) {
                    $pollbar .= '<img src="'.$THEME['imgdir'].'/pollbar.gif" alt="'.$lang['altpollpercentage'].'" />';
                }
                $percentage .= '%';
            } else {
                $percentage = '0%';
            }
            eval("\$pollhtml .= \"".template('viewthread_poll_options_view')."\";");
        }

        $buttoncode = '';
        eval("\$poll = \"".template('viewthread_poll')."\";");
    }
    // End Polls

    $thisbg = $THEME['altbg2'];
    $querypost = $db->query("SELECT a.aid, a.filename, a.filetype, a.filesize, a.downloads, p.*, m.*,w.time FROM $table_posts p LEFT JOIN $table_members m ON m.username=p.author LEFT JOIN $table_attachments a ON a.pid=p.pid LEFT JOIN $table_whosonline w ON w.username=p.author WHERE p.fid='$fid' AND p.tid='$tid' GROUP BY p.pid ORDER BY p.pid ASC LIMIT $start_limit, $self[ppp]");
    while ($post = $db->fetch_array($querypost)) {
        $post['avatar'] = str_replace("script:", "sc ript:", $post['avatar']);

        $onlinenow = $lang['memberisoff'];
        if ($post['time'] != '' && $post['author'] != "Anonymous") {
            if ($post['invisible'] == 1) {
                $onlinenow = X_ADMIN ? $lang['memberison'] . ' (' . $lang['hidden'] . ')' : $lang['memberisoff'];
            } else {
                $onlinenow = $lang['memberison'];
            }
        }

        $date = printGmDate($post['dateline']);
        $time = printGmTime($post['dateline']);

        $poston = "$lang[textposton] $date $lang[textat] $time";

        if ($post['icon'] != '') {
            $post['icon'] = "<img src=\"$THEME[smdir]/$post[icon]\" alt=\"$post[icon]\" />";
        } else {
            $post['icon'] = "<img src=\"$THEME[imgdir]/default_icon.gif\" alt=\"[*]\" />";
        }

        if ($post['author'] != "Anonymous") {
            if ($post['showemail'] == 'yes') {
                eval("\$email = \"".template('viewthread_post_email')."\";");
            } else {
                $email = "";
            }

            if ($post['site'] == '') {
                $site = "";
            } else {
                $post['site'] = str_replace("http://", "", $post['site']);
                $post['site'] = "http://$post[site]";
                eval("\$site = \"".template('viewthread_post_site')."\";");
            }

            $encodename = urlencode($post['author']);
            $icq = '';
            if ($post['icq'] != '' && $post['icq'] > 0) {
                eval("\$icq = \"".template('viewthread_post_icq')."\";");
            }
            $aim = '';
            if ($post['aim'] != '') {
                eval("\$aim = \"".template('viewthread_post_aim')."\";");
            }
            $msn = '';
            if ($post['msn'] != '') {
                eval("\$msn = \"".template('viewthread_post_msn')."\";");
            }
            $yahoo = '';
            if ($post['yahoo'] != '') {
                eval("\$yahoo = \"".template('viewthread_post_yahoo')."\";");
            }

            eval("\$search = \"".template('viewthread_post_search')."\";");
            eval("\$profile = \"".template('viewthread_post_profile')."\";");
            eval("\$u2u = \"".template('viewthread_post_u2u')."\";");

            $showtitle = $post['status'];
            $rank = array();

            if ($post['status'] == 'Administrator' || $post['status'] == 'Super Administrator' || $post['status'] == 'Super Moderator' || $post['status'] == 'Moderator') {
                $sr = $post['status'];
                $rankinfo = explode(",", $specialrank[$sr]);
                $rank['allowavatars']   = $rankinfo[4];
                $rank['title']      = $rankinfo[1];
                $rank['stars']      = $rankinfo[3];
                $rank['avatarrank'] = $rankinfo[5];

            } elseif ($post['status'] == 'Banned') {
                $rank['allowavatars']   = 'no';
                $rank['title']      = $lang['textbanned'];
                $rank['stars']      = 0;
                $rank['avatarrank'] = '';

            } else {
                $last_max = -1;
                foreach ($rankposts as $key => $rankstuff) {
                    if ($post['postnum'] >= $key && $key > $last_max) {
                        $last_max = $key;
                        $rankinfo = explode(",", $rankstuff);
                        $rank['allowavatars'] = $rankinfo[4];
                        $rank['title'] = $rankinfo[1];
                        $rank['stars'] = $rankinfo[3];
                        $rank['avatarrank'] = $rankinfo[5];
                    }
                }
            }

            $allowavatars   = $rank['allowavatars'];
            $stars          = str_repeat("<img src=\"" .$THEME['imgdir']. "/star.gif\" alt=\"*\" />", $rank['stars']) . '<br />';
            $showtitle      = ($post['customstatus'] != '') ? $post['customstatus'].'<br />' : $rank['title'].'<br />';

            if ($allowavatars == 'no') {
                $post['avatar'] = '';
            }

            if ($rank['avatarrank'] != '') {
                $rank['avatar'] = '<img src="'.$rank['avatarrank'].'" class="ctrtablerow" alt="'.$lang['altavatar'].'"/><br />';
            }

            $tharegdate = printGmDate($post['regdate']);
            $avatar = '';

            if ($SETTINGS['avastatus'] == 'on' || $SETTINGS['avastatus'] == 'list') {
                if ($post['avatar'] != '' && $allowavatars != "no") {
                    if (false !== ($pos = strpos($post['avatar'], ',')) && substr($post['avatar'], $pos-4, 4) == '.swf') {
                        $flashavatar = explode(",",$post['avatar']);
                        $avatar = '<object type="application/x-shockwave-flash" data="'.$flashavatar[0].'" width="'.$flashavatar[1].'" height="'.$flashavatar[2].'"><param name="movie" value="'.$flashavatar[0].'" /><param name="AllowScriptAccess" value="never" /></object>';
                    } else {
                        $avatar = '<img src="'.$post['avatar'].'" alt="'.$lang['altavatar'].'"/>';
                    }
                }
            }

            if ($post['mood'] != '') {
                $post['mood'] = censor($post['mood']);
                $mood = '<strong>'.$lang['mood'].'</strong> '.postify($post['mood'], 'no', 'no', 'yes', 'no', 'yes', 'no', true, 'yes');
            } else {
                $mood = '';
            }

            if ($post['location'] != '') {
                $post['location'] = censor($post['location']);
                $location = '<br />'.$lang['textlocation'].' '.$post['location'];
            } else {
                $location = '';
            }
        } else {
            $post['author'] = $lang['textanonymous'];
            $showtitle = $lang['textunregistered'].'<br />';
            $stars = '';
            $avatar = '';
            $rank['avatar'] = '';
            $post['postnum'] = 'N/A';
            $tharegdate = 'N/A';
            $email = '';
            $site = '';
            $icq = '';
            $msn='';
            $aim = '';
            $yahoo = "";
            $profile = '';
            $search = '';
            $u2u = '';
            $location = '';
            $mood = '';
        }

        if(X_ADMIN) {
            eval("\$ip = \"".template('viewthread_post_ip')."\";");
        } else {
            $ip = '';
        }

        if($PERMISSIONS[X_PERMS_REPLY] && $thread['closed'] != 'yes') {
            eval("\$repquote = \"".template('viewthread_post_repquote')."\";");
        } else {
            $repquote = '';
        }

        if(X_MEMBER && $SETTINGS['reportpost'] != 'off') {
            eval("\$reportlink = \"".template('viewthread_post_report')."\";");
        } else {
            $reportlink = '';
        }

        if ($post['subject'] != '') {
            $post['subject'] = censor($post['subject']).'<br /><br />';
            //$post['subject'] = str_replace('&amp;', '&', $post['subject']);
            $post['subject'] = checkOutput($post['subject'], 'no', '', true);
        }

        if (!X_GUEST && ($isModerator || ($thread['closed'] != 'yes' && $post['author'] == $xmbuser))) {
            eval('$edit = "'.template('viewthread_post_edit').'";');
        } else {
            $edit = '';
        }

        $bbcodeoff = $post['bbcodeoff'];
        $smileyoff = $post['smileyoff'];
        $post['message'] = postify($post['message'], $smileyoff, $bbcodeoff, $forum['allowsmilies'], $forum['allowhtml'], $forum['allowbbcode'], $forum['allowimgcode']);

        // Deal with the attachment if there is one
        if ($post['filename'] != '' && $forum['attachstatus'] != 'off') {
            $exp = floor(log($post['filesize'])/log(1024));
            switch($exp) {
                case 3:
                    $attachsize = sprintf('%.2f GiB', ($post['filesize']/pow(1024, floor($exp))));
                    break;
                case 2:
                    $attachsize = sprintf('%.2f MiB', ($post['filesize']/pow(1024, floor($exp))));
                    break;
                case 1:
                    $attachsize = sprintf('%.2f KiB', ($post['filesize']/pow(1024, floor($exp))));
                    break;
                case 0:
                    $attachsize = sprintf('%.2f B', $post['filesize']);
                    break;
            }

            $extention = strtolower(substr(strrchr($post['filename'],"."),1));
            if ($SETTINGS['attachimgpost'] == 'on' && ($extention == 'jpg' || $extention == 'jpeg' || $extention == 'jpe' || $extention == 'gif' || $extention == 'png' || $extention == 'bmp')) {
                eval("\$post['message'] .= \"".template('viewthread_post_attachmentimage')."\";");
            } else {
                $downloadcount = $post['downloads'];
                if ($downloadcount == '') {
                    $downloadcount = 0;
                }
                eval("\$post['message'] .= \"".template('viewthread_post_attachment')."\";");
            }
        }

        if($post['usesig'] == 'yes') {
            $post['sig'] = postify($post['sig'], 'no', 'no', $forum['allowsmilies'], $SETTINGS['sightml'], $SETTINGS['sigbbcode'], $forum['allowimgcode'], false);
            eval("\$post['message'] .= \"".template('viewthread_post_sig')."\";");
        } else {
            eval("\$post['message'] .= \"".template('viewthread_post_nosig')."\";");
        }

        if(!isset($rank['avatar'])) {
            $rank['avatar'] = '';
        }

        if(!$notexist) {
            eval("\$posts .= \"".template('viewthread_post')."\";");
        } else {
            eval("\$posts .= \"".template('viewthread_invalid')."\";");
        }

        if($thisbg == $THEME['altbg2']) {
            $thisbg = $THEME['altbg1'];
        } else {
            $thisbg = $THEME['altbg2'];
        }
    }
    $db->free_result($querypost);

    if($isModerator) {
        eval("\$modoptions = \"".template('viewthread_modoptions')."\";");
    } else {
        $modoptions = '';
    }
    eval('echo stripslashes("'.template('viewthread').'");');

    end_time();
    eval("echo (\"".template('footer')."\");");
    exit();

} elseif ($action == "attachment" && $forum['attachstatus'] != 'off' && $pid > 0 && $tid > 0) {
    // select attachment
    $query = $db->query("SELECT * FROM $table_attachments WHERE pid='$pid' and tid='$tid'");
    $file = $db->fetch_array($query);
    $db->free_result($query);
    $db->query("UPDATE $table_attachments SET downloads=downloads+1 WHERE pid='$pid'");

    // Check if file is corrupt
    if ($file['filesize'] != strlen($file['attachment'])) {
        error($lang['filecorrupt']);
    }

    // Generate $type, $name and $size vars
    $type = strtolower($file['filetype']);
    $name = $file['filename'];
    $size = (int) $file['filesize'];

    // Make sure text/html types can't be run...
    $type = ($type == 'text/html') ? 'text/plain' : $type;

    // Put out headers for mime-type, filesize, forced-download, description and no-cache.
    header("Content-type: $type");
    header("Content-length: $size");
    header("Content-Disposition: attachment; filename=$name");
    header("Content-Description: XMB Attachment");
    header("Cache-Control: public; max-age=604800"); // http 1.1
    header("Expires: 604800"); // http 1.0

    // Start file download
    echo $file['attachment'];

    // End download

    exit();

} elseif ($action == "printable") {
    $querypost = $db->query("SELECT p.*, a.filename, a.filesize FROM $table_posts p LEFT JOIN $table_attachments a ON a.pid=p.pid WHERE p.fid='$fid' AND p.tid='$tid' ORDER BY p.pid");
    $posts = '';

    while ($post = $db->fetch_array($querypost)) {
        $date = printGmDate($post['dateline']);
        $time = printGmTime($post['dateline']);

        $poston = $date.' '.$lang['textat'].' '.$time;
        $post['message'] = stripslashes($post['message']);

        $bbcodeoff = $post['bbcodeoff'];
        $smileyoff = $post['smileyoff'];
        $post['message'] = postify($post['message'], $smileyoff, $bbcodeoff, $forum['allowsmilies'], $forum['allowhtml'], $forum['allowbbcode'], 'off');

        if($post['filesize'] > 0) {
            $exp = floor(log($post['filesize'])/log(1024));
            switch($exp) {
                case 3:
                    $filesize = sprintf('%.2f GiB', ($post['filesize']/pow(1024, floor($exp))));
                    break;
                case 2:
                    $filesize = sprintf('%.2f MiB', ($post['filesize']/pow(1024, floor($exp))));
                    break;
                case 1:
                    $filesize = sprintf('%.2f KiB', ($post['filesize']/pow(1024, floor($exp))));
                    break;
                case 0:
                    $filesize = sprintf('%.2f B', $post['filesize']);
                    break;
            }
            $filename = $post['filename'];

            eval($lang['eval_attachment_string']);
            $attachment = $lang['attachment_string'];
        } else {
            $attachment = '';
        }

        eval('$posts .= "'.template('viewthread_printable_row').'";');
    }
    $db->free_result($querypost);
    eval('echo stripslashes("'.template('viewthread_printable').'");');
}