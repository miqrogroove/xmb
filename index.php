<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Alpha Zero - This software should not be used for any purpose after 31 August 2008.
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

define('X_SCRIPT', 'index.php');

require 'header.php';

loadtemplates(
'index',
'index_category',
'index_category_hr',
'index_category_spacer',
'index_forum',
'index_forum_lastpost',
'index_forum_nolastpost',
'index_noforum',
'index_ticker',
'index_stats',
'index_welcome_guest',
'index_welcome_member',
'index_whosonline',
'index_whosonline_today'
);

eval('$css = "'.template('css').'";');

$ticker = '';
if ($SETTINGS['tickerstatus'] == 'on') {
    $contents = '';
    $news = explode("\n", str_replace(array("\r\n", "\r"), array("\n"), $tickercontents));
    for($i=0;$i<count($news);$i++) {
        if (strlen(trim($news[$i])) == 0) {
            continue;
        }

        $news[$i] = str_replace('\"', '"', addslashes(postify($news[$i], 'no', 'no', 'yes', 'yes', 'yes', 'yes', false, 'yes', 'no')));
        $contents .= "\tcontents[$i]='$news[$i]';\n";
    }
    eval('$ticker = "'.template('index_ticker').'";');
}

$gid = 0;
if (onSubmit('gid')) {
    $gid = getInt('gid');
    $SETTINGS['tickerstatus'] = 'off';
    $SETTINGS['whosonlinestatus'] = 'off';
    $SETTINGS['index_stats'] = 'off';
    $query = $db->query("SELECT name FROM ".X_PREFIX."forums WHERE fid=$gid AND type='group' AND status='on' LIMIT 1");
    if ($db->num_rows($query) != 1) {
        header('HTTP/1.0 404 Not Found');
        error($lang['textnocat']);
    }
    $cat = $db->fetch_array($query);
    $db->free_result($query);
    nav(fnameOut($cat['name']));
}

eval('echo "'.template('header').'";');

$statsbar = '';
if ($SETTINGS['index_stats'] == 'on') {
    $query = $db->query("SELECT username FROM ".X_PREFIX."members WHERE lastvisit!=0 ORDER BY regdate DESC LIMIT 1");
    $lastmember = $db->fetch_array($query);
    $db->free_result($query);

    $query = $db->query("SELECT COUNT(uid) FROM ".X_PREFIX."members UNION ALL SELECT COUNT(tid) FROM ".X_PREFIX."threads UNION ALL SELECT COUNT(pid) FROM ".X_PREFIX."posts");
    $members = $db->result($query, 0);
    if ($members == false) {
        $members = 0;
    }

    $threads = $db->result($query, 1);
    if ($threads == false) {
        $threads = 0;
    }

    $posts = $db->result($query, 2);
    if ($posts == false) {
        $posts = 0;
    }
    $db->free_result($query);

    $memhtml = '<a href="member.php?action=viewpro&amp;member='.recodeOut($lastmember['username']).'"><strong>'.$lastmember['username'].'</strong></a>.';
    eval($lang['evalindexstats']);
    eval('$statsbar = "'.template('index_stats').'";');
}

if ($gid == 0) {
    if (X_MEMBER) {
        eval('$welcome = "'.template('index_welcome_member').'";');
    } else {
        eval('$welcome = "'.template('index_welcome_guest').'";');
    }

    $whosonline = $whosonlinetoday = '';
    if ($SETTINGS['whosonlinestatus'] == 'on') {
        $guestcount = $membercount = $hiddencount = 0;
        $member = array();
        $query  = $db->query("SELECT m.status, m.username, m.invisible, w.* FROM ".X_PREFIX."whosonline w LEFT JOIN ".X_PREFIX."members m ON m.username=w.username ORDER BY w.username");
        while($online = $db->fetch_array($query)) {
            switch($online['username']) {
                case 'xguest123':
                    $guestcount++;
                    break;
                default:
                    if ($online['invisible'] != 0 && X_ADMIN) {
                        $member[] = $online;
                        $hiddencount++;
                    } else if ($online['invisible'] != 0) {
                        $hiddencount++;
                    } else {
                        $member[] = $online;
                        $membercount++;
                    }
                    break;
            }
        }
        $db->free_result($query);

        $onlinetotal = $guestcount + $membercount;

        if ($membercount != 1) {
            $membern = '<strong>'.$membercount.'</strong> '.$lang['textmembers'];
        } else {
            $membern = '<strong>1</strong> '.$lang['textmem'];
        }

        if ($guestcount != 1) {
            $guestn = '<strong>'.$guestcount.'</strong> '.$lang['textguests'];
        } else {
            $guestn = '<strong>1</strong> '.$lang['textguest1'];
        }

        if ($hiddencount != 1) {
            $hiddenn = '<strong>'.$hiddencount.'</strong> '.$lang['texthmems'];
        } else {
            $hiddenn = '<strong>1</strong> '.$lang['texthmem'];
        }

        eval($lang['whosoneval']);
        $memonmsg = '<span class="smalltxt">'.$lang['whosonmsg'].'</span>';

        $memtally = array();
        $num = 1;
        $show_total = (X_ADMIN) ? ($membercount+$hiddencount) : ($membercount);

        $show_inv_key = false;
        for($mnum=0; $mnum<$show_total; $mnum++) {
            $pre = $suff = '';

            $online = $member[$mnum];

            $pre = '<span class="status_'.str_replace(' ', '_', $online['status']).'">';
            $suff = '</span>';

            if ($online['invisible'] != 0) {
                $pre .= '<strike>';
                $suff = '</strike>'.$suff;
                if (!X_ADMIN && $online['username'] != $xmbuser) {
                    $num++;
                    continue;
                }
            }

            if ($online['username'] == $xmbuser && $online['invisible'] != 0) {
                $show_inv_key = true;
            }

            $memtally[] = '<a href="member.php?action=viewpro&amp;member='.recodeOut($online['username']).'">'.$pre.''.$online['username'].''.$suff.'</a>';
            $num++;
        }

        if (X_ADMIN || $show_inv_key === true) {
            $hidden = ' - <strike>'.$lang['texthmem'].'</strike>';
        } else {
            $hidden = '';
        }

        $memtally = implode(', ', $memtally);
        if ($memtally == '') {
            $memtally = '&nbsp;';
        }

        $whosonlinetoday = '';
        if ($SETTINGS['onlinetoday_status'] == 'on') {
            $datecut = $onlinetime - (3600 * 24);
            if (X_ADMIN) {
                $query = $db->query("SELECT username, status FROM ".X_PREFIX."members WHERE lastvisit >= '$datecut' ORDER BY lastvisit DESC");
            } else {
                $query = $db->query("SELECT username, status FROM ".X_PREFIX."members WHERE lastvisit >= '$datecut' AND invisible!=1 ORDER BY lastvisit DESC");
            }

            $todaymembersnum = $db->num_rows($query);
            $todaymembers = array();
            $pre = $suff = '';
            $x = 0;
            while($memberstoday = $db->fetch_array($query)) {
                if ($x <= $onlinetodaycount) {
                    $pre = '<span class="status_'.str_replace(' ', '_', $memberstoday['status']).'">';
                    $suff = '</span>';
                    $todaymembers[] = '<a href="member.php?action=viewpro&amp;member='.recodeOut($memberstoday['username']).'">'.$pre.''.$memberstoday['username'].''.$suff.'</a>';
                    $x++;
                } else {
                    continue;
                }
            }
            $todaymembers = implode(', ', $todaymembers);
            $db->free_result($query);

            if ($todaymembersnum == 1) {
                $memontoday = $todaymembersnum.$lang['textmembertoday'];
            } else {
                $memontoday = $todaymembersnum.$lang['textmemberstoday'];
            }
            eval('$whosonlinetoday = "'.template('index_whosonline_today').'";');
        }

        eval('$whosonline = "'.template('index_whosonline').'";');
    }

    if ($SETTINGS['catsonly'] == 'on') {
        $fquery = $db->query("SELECT name as cat_name, fid as cat_fid FROM ".X_PREFIX."forums WHERE type='group' AND status='on' ORDER BY displayorder ASC");
    } else {
        $fquery = $db->query("SELECT f.*, c.name as cat_name, c.fid as cat_fid FROM ".X_PREFIX."forums f LEFT JOIN ".X_PREFIX."forums c ON (f.fup=c.fid) WHERE (c.type='group' AND f.type='forum' AND c.status='on' AND f.status='on') OR (f.type='forum' AND f.fup='' AND f.status='on') ORDER BY c.displayorder ASC, f.displayorder ASC");
    }
} else {
    $ticker = $welcome = $whosonline = $statsbar = $whosonlinetoday = '';
    $fquery = $db->query("SELECT f.*, c.name as cat_name, c.fid as cat_fid FROM ".X_PREFIX."forums f LEFT JOIN ".X_PREFIX."forums c ON (f.fup=c.fid) WHERE (c.type='group' AND f.type='forum' AND c.status='on' AND f.status='on' AND f.fup='$gid') ORDER BY c.displayorder ASC, f.displayorder ASC");
}

$indexBarTop = $indexBar = $forumlist =  $spacer = '';
$forumarray = array();
$catLessForums = $lastcat = 0;

if ($SETTINGS['space_cats'] == 'on') {
    eval('$spacer = "'.template('index_category_spacer').'";');
}

if ($SETTINGS['catsonly'] != 'on') {
    if ($SETTINGS['indexshowbar'] == 1) {
        eval('$indexBar = "'.template('index_category_hr').'";');
        $indexBarTop = $indexBar;
    }

    if ($SETTINGS['indexshowbar'] == 2) {
        eval('$indexBarTop = "'.template('index_category_hr').'";');
    }
} else if ($gid > 0) {
    eval('$indexBar = "'.template('index_category_hr').'";');
}

if ($SETTINGS['showsubforums'] == 'on') {
    $index_subforums = array();
    if ($SETTINGS['catsonly'] != 'on' || $gid > 0) {
        $query = $db->query("SELECT * FROM ".X_PREFIX."forums WHERE status='on' AND type='sub' ORDER BY fup, displayorder");
        while($queryrow = $db->fetch_array($query)) {
            $subperms = checkForumPermissions($queryrow);
            if (X_SADMIN || $SETTINGS['hideprivate'] == 'off' || ($subperms[X_PERMS_VIEW] && $subperms[X_PERMS_USERLIST])) {
                $index_subforums[] = $queryrow;
            }
        }
        $db->free_result($query);
    }
}

while($thing = $db->fetch_array($fquery)) {

    if ($SETTINGS['catsonly'] != 'on' || $gid > 0) {
        $cforum = forum($thing, "index_forum");
    } else {
        $cforum = '';
    }

    if ((int)$thing['cat_fid'] === 0) {
        $catLessForums++;
    }

    if ($lastcat != $thing['cat_fid'] && ($SETTINGS['catsonly'] == 'on' || (!empty($cforum) && $SETTINGS['catsonly'] != 'on'))) {
        if ($forumlist != '') {
            $forumarray[] = $forumlist;
            $forumlist = '';
        }
        $lastcat = $thing['cat_fid'];
        $thing['cat_name'] = fnameOut($thing['cat_name']);
        eval('$forumlist .= "'.template('index_category').'";');
        if ($SETTINGS['catsonly'] != 'on' || $gid > 0) {
            $forumlist .= $indexBar;
        }
    }

    if (!empty($cforum)) {
        $forumlist .= $cforum;
    }

}

$forumarray[] = $forumlist;
$forumlist = implode($spacer, $forumarray);

if ($forumlist == '') {
    eval('$forumlist = "'.template('index_noforum').'";');
}
$db->free_result($fquery);

if ($catLessForums == 0 && $SETTINGS['indexshowbar'] == 1) {
    $indexBarTop = '';
}

eval('$index = "'.template('index').'";');
end_time();
eval('$footer = "'.template('footer').'";');
echo $index.$footer;
?>
