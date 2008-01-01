<?php
/* $Id: forumdisplay.php,v 1.3.2.20 2006/09/23 08:57:25 Tularis Exp $ */
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

require "./header.php";
loadtemplates('footer_load', 'footer_phpsql', 'footer_querynum', 'footer_totaltime', 'forumdisplay', 'forumdisplay_admin', 'forumdisplay_invalidforum', 'forumdisplay_multipage', 'forumdisplay_multipage_admin', 'forumdisplay_newpoll', 'forumdisplay_newtopic', 'forumdisplay_nothreads', 'forumdisplay_password', 'forumdisplay_subforum', 'forumdisplay_subforum_lastpost', 'forumdisplay_subforum_nolastpost', 'forumdisplay_subforums', 'forumdisplay_thread', 'forumdisplay_thread_admin', 'forumdisplay_thread_lastpost', 'functions_bbcode', 'viewthread_newpoll', 'viewthread_newtopic');
smcwcache();
eval('$css = "'.template('css').'";');

$tid = (isset($tid) && is_numeric($tid)) ? (int) $tid : 0;
$fid = (isset($fid) && is_numeric($fid)) ? (int) $fid : 0;

$query = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
$forum = $db->fetch_array($query);

if($forum['type'] != "forum" && $forum['type'] != "sub" || $fid == 0 ) {
    error($lang['textnoforum']);
}

$fup = array();
if ($forum['type'] == 'sub') {
    $query = $db->query("SELECT userlist, name, fid, password, postperm FROM $table_forums WHERE fid='$forum[fup]'");
    $fup = $db->fetch_array($query);

    // prevent access to subforum when upper forum can't be viewed.
    $fupPerms = checkForumPermissions($fup);
    if(!$fupPerms[X_PERMS_VIEW] || !$fupPerms[X_PERMS_USERLIST]) {
        error($lang['privforummsg']);
    } elseif(!$fupPerms[X_PERMS_PASSWORD]) {
        handlePasswordDialog($fup['fid'], basename(__FILE__), $_GET);
    }
}

$PERMISSIONS = checkForumPermissions($forum);
if(!$PERMISSIONS[X_PERMS_VIEW] || !$PERMISSIONS[X_PERMS_USERLIST]) {
    error($lang['privforummsg']);
} elseif(!$PERMISSIONS[X_PERMS_PASSWORD]) {
    handlePasswordDialog($fid, basename(__FILE__), $_GET);
}

if ( $forum['type'] == "forum") {
    nav(stripslashes($forum['name']));
} elseif ( $forum['type'] == 'sub') {
    nav('<a href="forumdisplay.php?fid='.$fup['fid'].'">'.stripslashes($fup['name']).'</a>');
    nav(stripslashes($forum['name']));
}

eval('echo "'.template('header').'";');

// Start subforums
$subforums  = '';
if (count($fup) == 0) {
    // implies this is a normal forum (non-sub)
    $query = $db->query("SELECT * FROM $table_forums WHERE type='sub' AND fup='$fid' AND status='on' ORDER BY displayorder");

    if ( $db->num_rows($query) != 0) {
        $forumlist = array();
        while($sub = $db->fetch_array($query)) {
            // forum() checks permissions and eval's template
            $forumlist[] = forum($sub, 'forumdisplay_subforum');
        }

        if(count($forumlist) > 0) {
            $forumlist = implode("\n", $forumlist);
            eval('$subforums = "'.template('forumdisplay_subforums').'";');
        }
    }
}
// End subforums

if($PERMISSIONS[X_PERMS_POLL]) {
    eval('$newpolllink = "'.template('forumdisplay_newpoll').'";');
} else {
    $newpolllink = '';
}
    
if($PERMISSIONS[X_PERMS_THREAD]) {
    eval('$newtopiclink = "'.template('forumdisplay_newtopic').'";');
} else {
    $newtopiclink = '';
}

// Start Topped Image Processing
$t_extension = get_extension($lang['toppedprefix']);
switch($t_extension) {
    case 'gif':
    case 'jpg':
    case 'jpeg':
    case 'png':
        $lang['toppedprefix'] = '<img src="'.$THEME['imgdir'].'/'.$lang['toppedprefix'].'" title="'.$lang['toppedpost'].'" alt="[T]" />';
        break;
}

// Start Poll Image Processing
$p_extension = get_extension($lang['pollprefix']);
switch($p_extension) {
    case 'gif':
    case 'jpg':
    case 'jpeg':
    case 'png':
        $lang['pollprefix'] = '<img src="'.$THEME['imgdir'].'/'.$lang['pollprefix'].'" alt="[P]" title="'.$lang['postpoll'].'" />';
        break;
}

validateTpp();
validatePpp();

if (isset($page)) {
    $start_limit = ($page-1) *$self['tpp'];
} else {
    $start_limit = 0;
    $page = 1;
}
if (isset($cusdate) && $cusdate != 0) {
    $cusdate = time() - $cusdate;
    $cusdate = "AND (substring_index(lastpost, '|',1)+1) >= '$cusdate'";
}
else {
    $cusdate = "";
}

$ascdesc = isset($ascdesc) ? $ascdesc : '';
if (strtolower($ascdesc) != 'asc') {
    $ascdesc = "desc";
}



$isModerator = false;
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

// Start Displaying the threads

if ($isModerator) {
    $forumdisplay_thread = "forumdisplay_thread_admin";
} else {
    $forumdisplay_thread = "forumdisplay_thread";
}

$topicsnum = 0;
$threadlist = '';

$threadsInFid = array();

if ( $dotfolders == "on" && X_MEMBER ) {
    $query = $db->query("SELECT tid FROM $table_posts WHERE author='$xmbuser' AND fid='$fid'");
    while ($row = $db->fetch_array($query)) {
        array_push($threadsInFid, $row['tid']);
    }
    $db->free_result($query);
}

$querytop = $db->query("SELECT t.* FROM $table_threads t WHERE t.fid='$fid' $cusdate ORDER BY topped $ascdesc,lastpost $ascdesc LIMIT $start_limit, $self[tpp]");
while($thread = $db->fetch_array($querytop)) {
    if ( $thread['icon'] != "") {
        $thread['icon'] = '<img src="'.$THEME['smdir'].'/'.$thread['icon'].'" alt="'.$thread['icon'].'" />';
    } else {
        $thread['icon'] = ' ';
    }

    if ( $thread['topped'] == 1) {
        $topimage = '<img src="./images/admin/untop.gif" alt="'.$lang['textuntopthread'].'" border="0" />';
    } else {
        $topimage = '<img src="./images/admin/top.gif" alt="'.$lang['alttopthread'].'" border="0" />';
    }

    $thread['subject'] = shortenString($thread['subject'], 125, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...');

    $authorlink = '<a href="member.php?action=viewpro&amp;member='.rawurlencode($thread['author']).'">'.$thread['author'].'</a>';

    $prefix = '';

    $lastpost = explode("|", $thread['lastpost']);
    $dalast = trim($lastpost[0]);

    if($lastpost[1] != 'Anonymous') {
        $lastpost[1] = '<a href="member.php?action=viewpro&amp;member='.rawurlencode(trim($lastpost[1])).'">'.trim($lastpost[1]).'</a>';
    } else {
        $lastpost[1] = $lang['textanonymous'];
    }

    $lastPid = isset($lastpost[2]) ? $lastpost[2] : 0;

    if ( $thread['replies'] >= $hottopic ) {
        $folder = "hot_folder.gif";
    } else {
        $folder = "folder.gif";
    }

    $oldtopics = isset($oldtopics) ? $oldtopics : '';

    if (($oT = strpos($oldtopics, '|'.$lastPid.'|')) === false && $thread['replies'] >= $hottopic && $lastvisit < $dalast) {
        $folder = "hot_red_folder.gif";
    } elseif ($lastvisit < $dalast && $oT === false) {
        $folder = "red_folder.gif";
    }

    if ( $dotfolders == "on" && X_MEMBER && (count($threadsInFid) > 0) && in_array($thread['tid'], $threadsInFid) ) {
        $folder = "dot_".$folder;
    }
    $folder = '<img src="'.$THEME['imgdir'].'/'.$folder.'" alt="'.$lang['altfolder'].'" />';

    if ( $thread['closed'] == "yes") {
        $folder = '<img src="'.$THEME['imgdir'].'/lock_folder.gif" alt="'.$lang['altclosedtopic'].'" />';
    }

    $lastreplydate = printGmDate($lastpost[0]);
    $lastreplytime = printGmTime($lastpost[0]);

    $lastpost = "$lastreplydate $lang[textat] $lastreplytime<br />$lang[textby] $lastpost[1]";

    $moved = explode('|', $thread['closed']);

    if($moved[0] == "moved") {
        $prefix = $lang['moved'].' ';
        $thread['realtid'] = $thread['tid'];
        $thread['tid'] = $moved[1];
        $thread['replies'] = "-";
        $thread['views'] = "-";
        $folder = '<img src="'.$THEME['imgdir'].'/lock_folder.gif" alt="'.$lang['altclosedtopic'].'" />';
        $postnum = $db->result($db->query("SELECT count(pid) FROM $table_posts WHERE tid=$thread[tid]"), 0);
    } else {
        $thread['realtid'] = $thread['tid'];
    }

    eval("\$lastpostrow = \"".template("forumdisplay_thread_lastpost")."\";");

    if($thread['pollopts'] != '') {
        $prefix = $lang['pollprefix'].' ';
    } else {
        $prefix = '';
    }
    if($thread['topped'] == 1) {
        $prefix = $lang['toppedprefix'].' ';
    }

    $thread['subject'] = checkOutput(censor($thread['subject']), 'no', '', true);

    $postnum = $thread['replies']+1;
    if($postnum > $self['ppp']) {
        $pagelinks = multi($postnum, $self['ppp'], 0, 'viewthread.php?tid='.$thread['tid']);
        $multipage2 = '(<small>'.$pagelinks.'</small>)';
    } else {
        $pagelinks = '';
        $multipage2 = '';
    }

    eval("\$threadlist .= \"".template($forumdisplay_thread)."\";");

    $prefix = "";
    $topicsnum++;
}

if ( $topicsnum == 0) {
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


switch($cusdate) {
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

$query = $db->query("SELECT count(tid) FROM $table_threads WHERE fid='$fid'");
$topicsnum = $db->result($query, 0);
$mpurl = "forumdisplay.php?fid=$fid";
if (($multipage = multi($topicsnum, $self['tpp'], $page, $mpurl)) === false) {
    $multipage = '';
} else {
    if($isModerator) {
        eval('$multipage = "'.template('forumdisplay_multipage_admin').'";');
    } else {
        eval('$multipage = "'.template('forumdisplay_multipage').'";');
    }
}

if ($isModerator) {
    eval('echo stripslashes("'.template('forumdisplay_admin').'");');
} else {
    eval('echo stripslashes("'.template('forumdisplay').'");');
}

end_time();
eval('echo "'.template('footer').'";');
