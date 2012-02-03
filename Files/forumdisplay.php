<?php
/* $Id: forumdisplay.php,v 1.15.2.18 2004/09/24 19:10:32 Tularis Exp $ */
/*
    XMB 1.9
    © 2001 - 2004 Aventure Media & The XMB Development Team
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
loadtemplates('footer_load', 'footer_querynum', 'footer_phpsql', 'footer_totaltime','forumdisplay_newtopic','forumdisplay_newpoll','forumdisplay_password','forumdisplay_thread','forumdisplay_invalidforum','forumdisplay_nothreads','forumdisplay','forumdisplay_subforum_lastpost','forumdisplay_thread_lastpost','forumdisplay_admin','forumdisplay_thread_admin','viewthread_newpoll','viewthread_newtopic','header','footer','css','functions_bbcode','forumdisplay_subforum','forumdisplay_subforums', 'forumdisplay_multipage_admin', 'forumdisplay_multipage');
smcwcache();
eval("\$css = \"".template("css")."\";");

$tid = (isset($tid) && is_numeric($tid)) ? (int) $tid : 0;
$fid = (isset($fid) && is_numeric($fid)) ? (int) $fid : 0;

$query = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
$forum = $db->fetch_array($query);

$notexist = false;
if($forum['type'] != "forum" && $forum['type'] != "sub" || !$fid) {
    $notexist = $lang['textnoforum'];
}

$fup = array();
if($forum['type'] == 'sub'){
    $query = $db->query("SELECT private, userlist, name, fid FROM $table_forums WHERE fid='$forum[fup]'");
    $fup = $db->fetch_array($query);

    // prevent access to subforum when upper forum can't be viewed.
    if(!privfcheck($fup['private'], $fup['userlist'])) {
        error($lang['privforummsg']);
    }
}elseif($forum['type'] != 'forum'){
    error($notexist);
}

$authorization = privfcheck($forum['private'], $forum['userlist']);
if(!$authorization) {
    error($lang['privforummsg']);
}

pwverify($forum['password'], 'forumdisplay.php?fid='.$fid, $fid);

if($forum['type'] == "forum") {
    nav(stripslashes($forum['name']));
} elseif($forum['type'] == 'sub'){
    nav('<a href="forumdisplay.php?fid='.$fup['fid'].'">'.stripslashes($fup['name']).'</a>');
    nav(stripslashes($forum['name']));
}

eval("echo (\"".template('header')."\");");

// Start subforums
if(count($fup) == 0) {
    // implies this is a normal forum (non-sub)
    $query = $db->query("SELECT * FROM $table_forums WHERE type='sub' AND fup='$fid' AND status='on' ORDER BY displayorder");

    if($db->num_rows($query) != 0) {
        $forumlist = '';
        $fulist = $forum['userlist'];
        while($sub = $db->fetch_array($query)) {
            $forumlist .= forum($sub, "forumdisplay_subforum");
        }
        $forum['userlist'] = $fulist;
        eval("\$subforums = \"".template("forumdisplay_subforums")."\";");
    } else {
        $subforums = '';
    }
} else {
    $subforums = '';
}
// End subforums

if(!$notexist){
    if(!postperm($forum, 'thread')){
        $newtopiclink = '';
        $newpolllink = '';
    }else{
        eval("\$newtopiclink = \"".template("forumdisplay_newtopic")."\";");
        if($forum['pollstatus'] != "off") {
            eval("\$newpolllink = \"".template("forumdisplay_newpoll")."\";");
        }
    }
}

// Start Topped Image Processing
$t_extension = get_extension($lang['toppedprefix']);
switch($t_extension) {
    case 'gif':
    case 'jpg':
    case 'jpeg':
    case 'png':
        $lang['toppedprefix'] = "<img src=\"$imgdir/$lang[toppedprefix]\" />";
        break;
}

// Start Poll Image Processing
$p_extension = get_extension($lang['pollprefix']);
switch($p_extension) {
    case 'gif':
    case 'jpg':
    case 'jpeg':
    case 'png':
        $lang['pollprefix'] = "<img src=\"$imgdir/$lang[pollprefix]\" />";
        break;
}

if(!$tpp || $tpp == '') {
    $tpp = (int) $topicperpage;
} else {
    $tpp = (int) $tpp;
}

if(isset($page)) {
    $start_limit = ($page-1) *$tpp;
} else {
    $start_limit = 0;
    $page = 1;
}
if(isset($cusdate) && $cusdate != 0) {
    $cusdate = time() - $cusdate;
    $cusdate = "AND (substring_index(lastpost, '|',1)+1) >= '$cusdate'";
}
else {
    $cusdate = "";
}

$ascdesc = isset($ascdesc) ? $ascdesc : '';
if(strtolower($ascdesc) != 'asc') {
    $ascdesc = "desc";
}

if(X_STAFF && $self['status'] != 'Moderator') {
    $status1 = "Moderator";
} elseif($self['status'] == 'Moderator') {
    $status1 = modcheck($self['status'], $xmbuser, $forum['moderator']);
} else {
    $status1 = '';
}
// Start Displaying the threads

if($status1 == "Moderator"){
    $forumdisplay_thread = "forumdisplay_thread_admin";
}else{
    $forumdisplay_thread = "forumdisplay_thread";
}

$topicsnum = 0;
$threadlist = '';

$threadsInFid = array();

if ( $dotfolders == "on" && $xmbuser != "" ) {
    $query = $db->query("SELECT tid FROM $table_posts WHERE author='$xmbuser' AND fid='$fid'");
    while ($row = $db->fetch_array($query)) {
        array_push($threadsInFid, $row['tid']);
    }
    $db->free_result($query);
}

$querytop = $db->query("SELECT t.* FROM $table_threads t WHERE t.fid='$fid' $cusdate ORDER BY topped $ascdesc,lastpost $ascdesc LIMIT $start_limit, $tpp");
while($thread = $db->fetch_array($querytop)) {
    $lastpost = explode("|", $thread['lastpost']);
    $dalast = trim($lastpost[0]);

    if($lastpost[1] != "Anonymous") {
        $lastpost[1] = "<a href=\"member.php?action=viewpro&amp;member=".rawurlencode(trim($lastpost[1]))."\">".trim($lastpost[1])."</a>";
    } else {
        $lastpost[1] = "$lang[textanonymous]";
    }

    $lastreplydate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));
    $lastreplytime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));

    $lastpost = "$lastreplydate $lang[textat] $lastreplytime<br />$lang[textby] $lastpost[1]";
    eval("\$lastpostrow = \"".template("forumdisplay_thread_lastpost")."\";");
    if($thread['icon'] != "") {
        $thread['icon'] = "<img src=\"$smdir/$thread[icon]\" alt=\"$thread[icon]\" />";
    } else {
        $thread['icon'] = " ";
    }

    if($thread['replies'] >= $hottopic) {
        $folder = "hot_folder.gif";
    } else {
        $folder = "folder.gif";
    }

    if($thread['topped'] == 1) {
        $topimage = "<img src=\"./images/admin/untop.gif\" alt=\"$lang[textuntopthread]\" border=\"0\" />";
         } else {
        $topimage = "<img src=\"./images/admin/top.gif\" alt=\"$lang[alttopthread]\" border=\"0\" />";
    }

    if(!isset($_COOKIE['oldtopics'])) {
        $oldtopics = '';
    }

    if($thread['replies'] >= $hottopic && $lastvisit2 < $dalast && false === strpos($oldtopics, "|$thread[tid]|")) {
        $folder = "hot_red_folder.gif";
    }elseif($lastvisit2 < $dalast && false === strpos($oldtopics, "|$thread[tid]|")) {
        $folder = "red_folder.gif";
    }else {
        $folder = $folder;
    }

    $lastvisit2 += 540;

    if ( $dotfolders == "on" && $xmbuser != "" && (count($threadsInFid) > 0) && in_array($thread['tid'], $threadsInFid) ) {
        $folder = "dot_".$folder;
    }
    $folder = "<img src=\"$imgdir/$folder\" alt=\"$lang[altfolder]\" />";

    if($thread['closed'] == "yes") {
        $folder = "<img src=\"$imgdir/lock_folder.gif\" alt=\"$lang[altclosedtopic]\" />";
    }
    if(strlen($thread['subject']) == 127 || strlen($thread['subject']) == 128) {
        $thread['subject'] .= '...';
    }
    $thread['subject'] = stripslashes($thread['subject']);

    $authorlink = "<a href=\"member.php?action=viewpro&amp;member=".rawurlencode($thread['author'])."\">$thread[author]</a>";

    if(!$ppp || $ppp == '') {
        $ppp = $postperpage;
    }

    $postnum = $thread['replies']+1;
    if($postnum > $ppp) {
        $pagelinks = multi($postnum, $ppp, 0, 'viewthread.php?tid='.$thread['tid']);
        $multipage2 = '(<small>'.$pagelinks.'</small>)';
    } else {
        $pagelinks = '';
        $multipage2 = '';
    }
    $moved = explode("|", $thread['closed']);

    $prefix = '';

    if($moved[0] == "moved") {
        $prefix = "$lang[moved] ";
        $thread['realtid'] = $thread['tid'];
        $thread['tid'] = $moved[1];
        $thread['replies'] = "-";
        $thread['views'] = "-";
        $folder = "<img src=\"$imgdir/lock_folder.gif\" alt=\"$lang[altclosedtopic]\" />";
    }else{
        $thread['realtid'] = $thread['tid'];
    }
    if($thread['pollopts'] != "") {
        $prefix = "$lang[poll] ";
    }
    if($thread['topped'] == 1) {
        $prefix = "$lang[toppedprefix] ";
    }

    $thread['subject'] = censor($thread['subject']);

    eval("\$threadlist .= \"".template($forumdisplay_thread)."\";");

    $prefix = "";
    $topicsnum++;
}

if($notexist) {
    eval("\$threadlist = \"".template("forumdisplay_invalidforum")."\";");
}

if($topicsnum == 0 && !$notexist) {
    eval("\$threadlist = \"".template("forumdisplay_nothreads")."\";");
}

$check1 = '';
$check5 = '';
$check15 = '';
$check30 = '';
$check60 = '';
$check100 = '';
$checkyear = '';
$checkall = '';


switch($cusdate){
    case 86400:
        $check1 = "selected=\"selected\"";
        break;
    case 432000:
        $check5 = "selected=\"selected\"";
        break;
    case 1296000:
        $check15 = "selected=\"selected\"";
        break;
    case 2592000:
        $check30 = "selected=\"selected\"";
        break;
    case 5184000:
        $check60 = "selected=\"selected\"";
        break;
    case 8640000:
        $check100 = "selected=\"selected\"";
        break;
    case 31536000:
        $checkyear = "selected=\"selected\"";
        break;
    default:
        $checkall = "selected=\"selected\"";
        break;
}

// Do Multipaging
if(!$tpp || $tpp == '') {
    $tpp = $topicperpage;
}

if($page) {
    $start_limit = ($page-1) *$tpp;
} else {
    $start_limit = 0;
    $page = 1;
}

if($cusdate != 0) {
    $cusdate = time() - $cusdate;
} elseif($cusdate == 0) {
    $cusdate = "";
}

$query = $db->query("SELECT count(tid) FROM $table_threads WHERE fid='$fid'");
$topicsnum = $db->result($query, 0);
$mpurl = "forumdisplay.php?fid=$fid";
if(($multipage = multi($topicsnum, $tpp, $page, $mpurl)) === false) {
    $multipage = '';
} else {
    if($self['status'] == "Administrator" || $status1 == "Moderator"){
        eval('$multipage = "'.template('forumdisplay_multipage_admin').'";');
    } else {
        eval('$multipage = "'.template('forumdisplay_multipage').'";');
    }
}

if($self['status'] == "Administrator" || $status1 == "Moderator"){
    eval('echo stripslashes("'.template('forumdisplay_admin').'");');
} else {
    eval('echo stripslashes("'.template('forumdisplay').'");');
}

end_time();
eval("echo (\"".template('footer')."\");");
?>