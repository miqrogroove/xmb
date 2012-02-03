<?php
/* $Id: viewthread.php,v 1.33.2.14 2004/09/24 19:10:32 Tularis Exp $ */
/*
    XMB 1.9
    � 2001 - 2004 Aventure Media & The XMB Development Team
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

if (!isset($ppp) || $ppp == '') {
    $ppp = $postperpage;
}

// validation of tid and fid. If they don't exist, they will after this, allowing
// us to drop most tid, fid validation post these lines.
$tid = (isset($tid) && is_numeric($tid)) ? (int) $tid : 0;
$fid = (isset($fid) && is_numeric($fid)) ? (int) $fid : 0;

if (isset($goto) && $goto == "lastpost") {
    if ($tid > 0) {
        $query = $db->query("SELECT count(*) FROM $table_posts WHERE tid='$tid'");
        $posts = $db->result($query, 0);

        if ($posts == 0) {
            eval("\$css = \"".template("css")."\";");
            error($lang['textnothread']);
        }

        $query = $db->query("SELECT pid FROM $table_posts WHERE tid='$tid' ORDER BY pid DESC LIMIT 0,1");
        $pid = $db->result($query, 0);
    } elseif ($fid > 0) {
        $query = $db->query("SELECT pid, tid FROM $table_posts WHERE fid='$fid' ORDER BY pid DESC LIMIT 0,1");
        $stuff = $db->fetch_array($query);
        $pid = $stuff['pid'];
        $tid = $stuff['tid'];

        $query = $db->query("SELECT fid FROM $table_forums WHERE fup='$fid'");
        while ($sub = $db->fetch_array($query)) {
            $query2 = $db->query("SELECT pid, tid FROM $table_posts WHERE fid='$sub[fid]' ORDER BY pid DESC LIMIT 0,1");
            $stuff = $db->fetch_array($query2);

            if ($stuff['pid'] > $pid) {
                $pid = $stuff['pid'];
                $tid = $stuff['tid'];
            }
        }

        $query = $db->query("SELECT count(*) FROM $table_posts WHERE tid='$tid'");
        $posts = $db->result($query, 0);
    }

    $topicpages = quickpage($posts, $ppp);

    redirect("viewthread.php?tid=$tid&page=$topicpages#pid$pid", 0);
    exit();
}

loadtemplates('footer_load', 'footer_querynum', 'footer_phpsql', 'footer_totaltime', 'css','functions_bbcode','functions_smilieinsert_smilie','header','viewthread_reply','viewthread_quickreply','viewthread','viewthread_invalid','viewthread_modoptions','viewthread_newpoll','viewthread_newtopic','viewthread_poll_options_view','viewthread_poll_options','viewthread_poll_submitbutton','viewthread_poll','viewthread_post','viewthread_post_email','viewthread_post_site','viewthread_post_icq','viewthread_post_aim','viewthread_post_msn','viewthread_post_yahoo','viewthread_post_search','viewthread_post_profile','viewthread_post_u2u','viewthread_post_ip','viewthread_post_repquote','viewthread_post_report','viewthread_post_edit','viewthread_post_attachmentimage','viewthread_post_attachment','viewthread_post_sig','viewthread_post_nosig','viewthread_printable','viewthread_printable_row','footer', 'viewthread_multipage');
smcwcache();
eval("\$css = \"".template("css")."\";");

if (!isset($_COOKIE['oldtopics'])) {
    $oldtopics = '';
}

if (false === strpos($oldtopics, "|$tid|")) {
    $oldtopics .= "|$tid| ";
    $expire = time() + 600;
    put_cookie("oldtopics", $oldtopics, $expire, $cookiepath, $cookiedomain);
}

$notexist = false;
$notexist_txt = '';
$posts = '';

$query = $db->query("SELECT * FROM $table_threads WHERE tid='$tid'");
$thread = $db->fetch_array($query);

// if subject has MAX $table_thread[subject] size (128 chars) assume it is actually longer
if (strlen($thread['subject']) == 127 || strlen($thread['subject']) == 128) {
    $thread['subject'] .= '...';
}

$thread['subject'] = censor($thread['subject']);
$fid = (int) $thread['fid'];

if ($thread['tid'] != $tid || $tid == 0) {
    $notexist_txt = $lang['textnothread'];
    $notexist = true;
}

$query = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
$forum = $db->fetch_array($query);

if ($forum['type'] != "forum" && $forum['type'] != "sub" && $forum['fid'] != $fid) {
    $notexist_txt = $lang['textnoforum'];
    $notexist = true;
}

if ( $notexist || trim($notexist_txt) != '' ) {
    error($notexist_txt);
}

$authorization = true;
if ($forum['type'] == 'sub') {
    $query = $db->query("SELECT name, fid, private, userlist FROM $table_forums WHERE fid='$forum[fup]'");
    $fup = $db->fetch_array($query);
    $authorization = privfcheck($fup['private'], $fup['userlist']);
}

if (!$authorization || !privfcheck($forum['private'], $forum['userlist'])) {
    error($lang['privforummsg']);
}

pwverify($forum['password'], 'viewthread.php?tid='.$tid, $fid);

if ($forum['type'] == "forum") {
    nav('<a href="forumdisplay.php?fid='.$fid.'"> '.stripslashes($forum['name']).'</a>');
    nav(stripslashes($thread['subject']));
} else {
    nav('<a href="forumdisplay.php?fid='.$fup['fid'].'">'.stripslashes($fup['name']).'</a>');
    nav('<a href="forumdisplay.php?fid='.$fid.'">'.stripslashes($forum['name']).'</a>');
    nav(stripslashes($thread['subject']));
}

$allowimgcode   = ($forum['allowimgcode'] == 'yes') ? $lang['texton']:$lang['textoff'];
$allowhtml      = ($forum['allowhtml'] == 'yes')    ? $lang['texton']:$lang['textoff'];
$allowsmilies   = ($forum['allowsmilies'] == 'yes') ? $lang['texton']:$lang['textoff'];
$allowbbcode    = ($forum['allowbbcode'] == 'yes')  ? $lang['texton']:$lang['textoff'];

eval("\$bbcodescript = \"".template('functions_bbcode')."\";");
if ($smileyinsert == 'on' && $smiliesnum > 0) {
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

if (!$action) {
    if ($xmbuser && $xmbuser != '') {
        if ($self['sig'] != '') {
            $usesig = true;
        }
    }

    eval("echo (\"".template('header')."\");");

    $ppthread = postperm($forum, 'thread');
    $ppreply = postperm($forum, 'reply');

    $usesigcheck = $usesig ? "checked=\"checked\"" : '';
    $codeoffcheck = (isset($bbcodeoff) && $bbcodeoff == "yes") ? "checked=\"checked\"" : '';
    $smileoffcheck = (isset($smileyoff) && $smileyoff == "yes") ? "checked=\"checked\"" : '';

    if ($thread['closed'] == 'yes') {
        if ($self['status'] == 'Super Administrator') {
            eval("\$replylink = \"".template('viewthread_reply')."\";");
            eval("\$quickreply = \"".template('viewthread_quickreply')."\";");
        }
        $closeopen = $lang['textopenthread'];
    } else {
        $closeopen = $lang['textclosethread'];
        eval("\$replylink = \"".template('viewthread_reply')."\";");
        eval("\$quickreply = \"".template('viewthread_quickreply')."\";");
    }

    if (!$ppthread) {
        $newtopiclink = '';
        $newpolllink = '';

        if (!$ppreply || ($self['status'] == '' && $forum['guestposting'] != 'on')) {
            $replylink = '';
            $quickreply = '';
        }
    } else {
        eval("\$newtopiclink = \"".template('viewthread_newtopic')."\";");
        if ($forum['pollstatus'] != "off") {
            eval("\$newpolllink = \"".template('viewthread_newpoll')."\";");
        } else {
            $newpolllink = '';
        }

        if (!$ppreply || ($self['status'] == '' && $forum['guestposting'] != 'on')) {
            $replylink = '';
            $quickreply = '';
        }
    }

    if ($thread['topped'] == 1) {
        $topuntop = $lang['textuntopthread'];
    } else {
        $topuntop = $lang['texttopthread'];
    }

    if (isset($page)) {
        if ( $page < 1 ) {
            $page = 1;
        }
        $start_limit = ($page-1) * $ppp;
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
            $specialrank[$title]    = "$query[0],$query[1],$query[2],$query[3],$query[4],$query[5]";
        } else {
            $rankposts[$rposts]     = "$query[0],$query[1],$query[2],$query[3],$query[4],$query[5]";
        }
    }
    // End user rank query.

    $db->query("UPDATE $table_threads SET views=views+1 WHERE tid='$tid'");
    $query = $db->query("SELECT count(pid) FROM $table_posts WHERE fid='$fid' AND tid='$tid'");
    $num = $db->result($query, 0);

    $mpurl = "viewthread.php?tid=$tid";
    
    if(($multipage = multi($num, $ppp, $page, $mpurl)) === false) {
        $multipage = '';
    } else {
        eval('$multipage = "'.template('viewthread_multipage').'";');
    }

    $that = array();

    $poll = '';

    // Start polls
    if ($thread['pollopts'] != '' && $forum['pollstatus'] != 'off' && $thread['closed'] != 'yes') {
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
                    $pollbar = str_repeat('<img src="'.$imgdir.'/pollbar.gif" alt="'.$lang['altpollpercentage'].'" />', $poll_length);
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
                $percentage = round(round(($array['votes'])/$num_votes*100,2)/3, 2);
                for($num = 0; $num < $percentage; $num++) {
                    $pollbar .= '<img src="'.$imgdir.'/pollbar.gif" alt="'.$lang['altpollpercentage'].'" />';
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

    $thisbg = $altbg2;
    $querypost = $db->query("SELECT a.aid, a.filename, a.filetype, a.filesize, a.downloads, p.*, m.*,w.time FROM $table_posts p LEFT JOIN $table_members m ON m.username=p.author LEFT JOIN $table_attachments a ON a.pid=p.pid LEFT JOIN $table_whosonline w ON p.author=w.username WHERE p.fid='$fid' AND p.tid='$tid' ORDER BY p.pid ASC LIMIT $start_limit, $ppp");
    $tmoffset = ($timeoffset * 3600) + ($addtime * 3600);
    while ($post = $db->fetch_array($querypost)) {
        $post['avatar'] = str_replace("javascript:", "java script:", $post['avatar']);

        $onlinenow = $lang['memberisoff'];
        if ($post['time'] != "" && $post['author'] != "xguest123") {
            if ($post['invisible'] == 1) {
                if (X_ADMIN) {
                    $onlinenow = $lang['memberison'] . ' (' . $lang['hidden'] . ')';
                } else {
                    $onlinenow = $lang['memberisoff'];
                }
            } else {
                $onlinenow = $lang['memberison'];
            }
        }

        $date = gmdate("$dateformat", $post['dateline'] + $tmoffset);
        $time = gmdate("$timecode", $post['dateline'] + $tmoffset);

        $poston = "$lang[textposton] $date $lang[textat] $time";

        if ($post['icon'] != "") {
            $post['icon'] = "<img src=\"$smdir/$post[icon]\" alt=\"$post[icon]\" />";
        }

        if ($post['author'] != "Anonymous") {
            if ($post['showemail'] == "yes") {
                eval("\$email = \"".template('viewthread_post_email')."\";");
            } else {
                $email = "";
            }

            if (isset($post['personstatus']) && $post['personstatus'] != "" && $personstaton == "on") {
                $personstatus = substr_replace($personstatus, ' ', 20, 0);
                $personstatus = substr_replace($personstatus, ' ', 41, 0);
                $personstatus = substr_replace($personstatus, ' ', 62, 0);
                $personstatus = substr_replace($personstatus, ' ', 83, 0);
                $personstatus .= "<br />";
            } else {
                $personstatus = "";
            }

            if ($post['site'] == "") {
                $site = "";
            } else {
                $post['site'] = str_replace("http://", "", $post['site']);
                $post['site'] = "http://$post[site]";
                eval("\$site = \"".template('viewthread_post_site')."\";");
            }

            $encodename = urlencode($post['author']);

            if ($post['icq'] == "") {
                $icq = "";
            } else {
                eval("\$icq = \"".template('viewthread_post_icq')."\";");
            }

            if ($post['aim'] == "") {
                $aim = "";
            } else {
                eval("\$aim = \"".template('viewthread_post_aim')."\";");
            }

            if ($post['msn'] == "") {
                $msn = "";
            } else {
                eval("\$msn = \"".template('viewthread_post_msn')."\";");
            }

            if ($post['yahoo'] == "") {
                $yahoo = "";
            } else {
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
            $showtitle      = $rank['title'];
            $stars          = str_repeat("<img src=\"" .$imgdir. "/star.gif\" alt=\"*\" />", $rank['stars']);

            if ($allowavatars == 'no') {
                $post['avatar'] = '';
            }

            if ($post['customstatus'] != "") {
                $showtitle = $post['customstatus'];
                $showtitle .= "<br />";
            } else {
                $showtitle .= "<br />";
            }

            if ($rank['avatarrank'] != '') {
                $rank['avatar'] = '<img src="'.$rank['avatarrank'].'" class="ctrtablerow" alt="'.$lang['altavatar'].'"/><br />';
            }

            $tharegdate = gmdate($dateformat, $post['regdate'] + $tmoffset);
            $stars .= '<br />';

            $avatar = '';

            if ($SETTINGS['avastatus'] == 'on' || $SETTINGS['avastatus'] == 'list') {
                if ($post['avatar'] != "" && $allowavatars != "no") {
                    if (false !== strpos($post['avatar'], ",")) {
                        $flashavatar = explode(",",$post['avatar']);
                        $avatar = "<OBJECT classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://active.macromedia.com/flash2/cabs/swflash.cab#version=4,0,0,0\" ID=main WIDTH=$flashavatar[1] HEIGHT=$flashavatar[2]>
                            <PARAM NAME=movie VALUE=\"$flashavatar[0]\">
                            <PARAM NAME=loop VALUE=false>
                            <PARAM NAME=menu VALUE=false>
                            <PARAM NAME=quality VALUE=best>
                            <EMBED src=\"$flashavatar[0]\" loop=false menu=false quality=best WIDTH=$flashavatar[1] HEIGHT=$flashavatar[2] TYPE=\"application/x-shockwave-flash\" PLUGINSPAGE=\"http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash\">
                            </EMBED>
                            </OBJECT>";
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

            if ($post['location'] != "") {
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

        $status1 = modcheck($self['status'], $xmbuser, $forum['moderator']);

        if ('Moderator' == $status1) {
            eval("\$ip = \"".template('viewthread_post_ip')."\";");
        } else {
            $ip = "";
        }

        if ($thread['closed'] == "yes") {
            $repquote = "";
        } else {
            eval("\$repquote = \"".template('viewthread_post_repquote')."\";");
        }

        if ($xmbuser != "" && $reportpost != "off") {
            eval("\$reportlink = \"".template('viewthread_post_report')."\";");
        } else {
            $reportlink = "";
        }
        if ($post['subject'] != "") {
            $post['subject'] = censor($post['subject']).'<br /><br />';
            $post['subject'] = str_replace('&amp;', '&', $post['subject']);
        }

        eval("\$edit = \"".template('viewthread_post_edit')."\";");
        $bbcodeoff = $post['bbcodeoff'];
        $smileyoff = $post['smileyoff'];
        $post['message'] = postify($post['message'], $smileyoff, $bbcodeoff, $forum['allowsmilies'], $forum['allowhtml'], $forum['allowbbcode'], $forum['allowimgcode']);

        // Deal with the attachment if there is one
        if ($post['filename'] != "" && $forum['attachstatus'] != "off") {
            $attachsize = $post['filesize'];
            if ($attachsize >= 1073741824) {
                $attachsize = round($attachsize / 1073741824 * 100) / 100 . "gb";
            } elseif ($attachsize >= 1048576) {
                $attachsize = round($attachsize / 1048576 * 100) / 100 . "mb";
                } elseif ($attachsize >= 1024) {
                $attachsize = round($attachsize / 1024 * 100) / 100 . "kb";
            } else {
                $attachsize = $attachsize . "b";
            }
            
            $extention = strtolower(substr(strrchr($post['filename'],"."),1));
            if ($attachimgpost == 'on' && ($extention == 'jpg' || $extention == 'jpeg' || $extention == 'jpe' || $extention == 'gif' || $extention == 'png' || $extention == 'bmp')) {
                eval("\$post['message'] .= \"".template('viewthread_post_attachmentimage')."\";");
            } else {
                $downloadcount = $post['downloads'];
                if ($downloadcount == "") {
                    $downloadcount = 0;
                }
                eval("\$post['message'] .= \"".template('viewthread_post_attachment')."\";");
            }
        }

        if ($post['usesig'] == "yes") {
            $post['sig'] = postify($post['sig'], 'no', 'no', $forum['allowsmilies'], $SETTINGS['sightml'], $SETTINGS['sigbbcode'], $forum['allowimgcode'], false);
            eval("\$post['message'] .= \"".template('viewthread_post_sig')."\";");
        } else {
            eval("\$post['message'] .= \"".template('viewthread_post_nosig')."\";");
        }

        if (!isset($rank['avatar'])) {
            $rank['avatar'] = '';
        }

        if (!$notexist) {
            eval("\$posts .= \"".template('viewthread_post')."\";");
        } else {
            eval("\$posts .= \"".template('viewthread_invalid')."\";");
        }

        if ($thisbg == $altbg2) {
            $thisbg = $altbg1;
        } else {
            $thisbg = $altbg2;
        }
    }

    $status1 = modcheck($self['status'], $xmbuser, $forum['moderator']);

    if ('Moderator' == $status1) {
        eval("\$modoptions = \"".template('viewthread_modoptions')."\";");
    } else {
        $modoptions = "";
    }

    eval('echo stripslashes("'.template('viewthread').'");');
    $db->free_result($querypost);
    end_time();
    eval("echo (\"".template('footer')."\");");
    exit();

} elseif ($action == "attachment" && $forum['attachstatus'] != "off") {
    // select attachment
    $query = $db->query("SELECT * FROM $table_attachments WHERE pid='$pid' and tid='$tid'");
    $file = $db->fetch_array($query);
    $db->query("UPDATE $table_attachments SET downloads=downloads+1 WHERE pid='$pid'");

    // Check if file is corrupt
    if ($file['filesize'] != strlen($file['attachment'])) {
        error('The file you are trying to download appears corrupt.<br /><br />&raquo; File download aborted');
    }

    // Generate $type, $name and $size vars
    $type = $file['filetype'];
    $name = $file['filename'];
    $size = (int) $file['filesize'];

    // Make sure text/html types can't be run...
    $type = ($type == 'text/html') ? 'text/plain' : $type;

    // Put out headers for mime-type, filesize, forced-download, description and no-cache.
    header("Content-type: $type");
    header("Content-length: $size");
    header("Content-Disposition: inline; filename=$name");
    header("Content-Description: XMB Attachment");
    header("Cache-Control: public; max-age=604800"); // http 1.1
    header("Expires: 604800"); // http 1.0

    // Start file download
    echo $file['attachment'];

    // End download

    exit();

} elseif ($action == "printable") {

    $querypost = $db->query("SELECT * FROM $table_posts WHERE fid='$fid' AND tid='$tid' ORDER BY pid");
    $posts = '';

    $tmoffset = ($timeoffset * 3600) + ($addtime * 3600);

    while ($post = $db->fetch_array($querypost)) {
        $date = gmdate($dateformat, $post['dateline'] + $tmoffset);
        $time = gmdate($timecode, $post['dateline'] + $tmoffset);
        $poston = "$date $lang[textat] $time";
        $post['message'] = stripslashes($post['message']);

        $bbcodeoff = $post['bbcodeoff'];
        $smileyoff = $post['smileyoff'];
        $post['message'] = postify($post['message'], $smileyoff, $bbcodeoff, $forum['allowsmilies'], $forum['allowhtml'], $forum['allowbbcode'], $forum['allowimgcode']);

        eval("\$posts .= \"".template('viewthread_printable_row')."\";");
    }
    eval('echo stripslashes("'.template('viewthread_printable').'");');
}

?>