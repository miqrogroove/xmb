<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2012, The XMB Group
 * http://www.xmbforum2.com/
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
    $news = explode("\n", str_replace(array("\r\n", "\r"), array("\n"), $SETTINGS['tickercontents']));
    $counter = 0;
    for($i=0;$i<count($news);$i++) {
        if (strlen(trim($news[$i])) == 0) {
            continue;
        }

        $news[$i] = str_replace('\"', '"', addslashes(postify($news[$i], 'no', 'no', 'yes', 'yes', 'yes', 'yes', false, 'no', 'no')));
        $contents .= "\tcontents[$counter]='{$news[$i]}';\n";
        $counter++;
    }
    eval('$ticker = "'.template('index_ticker').'";');
}

if (onSubmit('gid')) {
    $gid = getInt('gid');
    $SETTINGS['tickerstatus'] = 'off';
    $SETTINGS['whosonlinestatus'] = 'off';
    $SETTINGS['index_stats'] = 'off';
    $cat = getForum($gid);
    if ($cat === FALSE) {
        header('HTTP/1.0 404 Not Found');
        error($lang['textnocat']);
    } elseif ($cat['type'] != 'group' Or $cat['status'] != 'on') {
        header('HTTP/1.0 404 Not Found');
        error($lang['textnocat']);
    }
    setCanonicalLink("index.php?gid=$gid");
    nav(fnameOut($cat['name']));
    if ($SETTINGS['subject_in_title'] == 'on') {
        $threadSubject = '- '.fnameOut($cat['name']);
    }
} else {
    $gid = 0;
    $cat = array();
    setCanonicalLink('./');
}

eval('$header = "'.template('header').'";');

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
        $hiddencount = 0;
        $membercount = 0;
        $guestcount = $db->result($db->query("SELECT COUNT(DISTINCT ip) AS guestcount FROM ".X_PREFIX."whosonline WHERE username = 'xguest123'"), 0);
        $member = array();
        $query  = $db->query("SELECT m.username, MAX(m.status) AS status, MAX(m.invisible) AS invisible FROM ".X_PREFIX."members AS m INNER JOIN ".X_PREFIX."whosonline USING (username) GROUP BY m.username ORDER BY m.username");
        while($online = $db->fetch_array($query)) {
            if ($online['invisible'] != 0 && X_ADMIN) {
                $member[] = $online;
                $hiddencount++;
            } else if ($online['invisible'] != 0) {
                $hiddencount++;
            } else {
                $member[] = $online;
                $membercount++;
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
            eval($lang['last50todayeval']);
            eval('$whosonlinetoday = "'.template('index_whosonline_today').'";');
        }

        eval('$whosonline = "'.template('index_whosonline').'";');
    }
} else {
    $ticker = $welcome = $whosonline = $statsbar = $whosonlinetoday = '';
}

$forums = getStructuredForums(TRUE);
$fquery = getIndexForums($forums, $cat, $SETTINGS['catsonly'] == 'on');

$indexBarTop = $indexBar = $forumlist = $spacer = '';
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

// Collect Subforums ordered by fup, displayorder
$index_subforums = array();
if ($SETTINGS['showsubforums'] == 'on') {
    if ($SETTINGS['catsonly'] != 'on' || $gid > 0) {
        foreach($forums['sub'] as $subForumsByFUP) {
            foreach($subForumsByFUP as $forum) {
                $index_subforums[] = $forum;
            }
        }
    }
}

foreach($fquery as $thing) {

    if ($SETTINGS['catsonly'] != 'on' || $gid > 0) {
        $cforum = forum($thing, "index_forum", $index_subforums);
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
unset($fquery);

if ($catLessForums == 0 && $SETTINGS['indexshowbar'] == 1) {
    $indexBarTop = '';
}

eval('$index = "'.template('index').'";');
end_time();
eval('$footer = "'.template('footer').'";');
echo $header, $index, $footer;

/**
 * Simulates needed SQL results using the forum cache.
 *
 * @since 1.9.11
 * @param array $forums Read-Only Variable. Must be a return value from the function getStructuredForums()
 * @param array $cat
 * @param bool  $catsonly
 * @return array Two-dimensional array of forums sorted by the group's displayorder, then the forum's displayorder.
 */
function getIndexForums(&$forums, $cat, $catsonly) {
    $sorted = array();

    if (isset($cat['fid']) and isset($forums['forum'][$cat['fid']])) {
        // Group forums.
        foreach($forums['forum'][$cat['fid']] as $forum) {
            $forum['cat_fid'] = $cat['fid'];
            $forum['cat_name'] = $cat['name'];
            $sorted[] = $forum;
        }
    } elseif ($catsonly) {
        // Groups instead of forums.
        foreach($forums['group']['0'] as $group) {
            $group['cat_fid'] = $group['fid'];
            $group['cat_name'] = $group['name'];
            $sorted[] = $group;
        }
    } else {
        // Ungrouped forums.
        foreach($forums['forum']['0'] as $forum) {
            $forum['cat_fid'] = '';
            $forum['cat_name'] = '';
            $sorted[] = $forum;
        }
        // Grouped forums.
        foreach($forums['group']['0'] as $group) {
            if (isset($forums['forum'][$group['fid']])) {
                foreach($forums['forum'][$group['fid']] as $forum) {
                    $forum['cat_fid'] = $group['fid'];
                    $forum['cat_name'] = $group['name'];
                    $sorted[] = $forum;
                }
            }
        }
    }

    return $sorted;
}

?>
