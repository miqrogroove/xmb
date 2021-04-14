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

$ticker = '';
if ($SETTINGS['tickerstatus'] == 'on') {
    $contents = '';
    $news = explode("\n", str_replace(array("\r\n", "\r"), array("\n"), $SETTINGS['tickercontents']));
    $counter = 0;
    foreach ( $news as $item ) {
        if (strlen(trim( $item )) == 0) {
            continue;
        }
        if ( 'bbcode' == $SETTINGS['tickercode'] ) {
            $item = postify( $item, 'no', 'no', 'yes', 'no', 'yes', 'yes', false, 'no', 'no' );
        } elseif ( 'html' == $SETTINGS['tickercode'] ) {
            $item = rawHTMLmessage( $item, 'yes' );
        }
        $item = str_replace( '\"', '"', addslashes( $item ) );
        $contents .= "\tcontents[$counter]='$item';\n";
        $counter++;
    }
    eval('$ticker = "'.template('index_ticker').'";');
}

if ( X_SMOD ) {
    $quarantine = true;
    $result = \XMB\SQL\countPosts( $quarantine );
    if ( $result > 0 ) {
        if ( 1 == $result ) {
            $msg = $lang['moderation_notice_single'];
        } else {
            $msg = str_replace( '$result', $result, $lang['moderation_notice_eval'] );
        }
        $ticker .= message( $msg, false, '', '', false, false, true, false ) . "<br />\n";
    }
}

$forums = getStructuredForums(TRUE);

if (onSubmit('gid')) {
    $gid = getInt('gid');
    $SETTINGS['tickerstatus'] = 'off';
    $SETTINGS['whosonlinestatus'] = 'off';
    $SETTINGS['index_stats'] = 'off';
    $cat = getForum($gid);

    if ($cat === FALSE) {
        header('HTTP/1.0 404 Not Found');
        error($lang['textnocat']);
    } elseif ($cat['type'] != 'group') {
        header('HTTP/1.0 404 Not Found');
        error($lang['textnocat']);
    } elseif (!isset($forums['forum'][$gid])) {
        // Does this user not have permissions for any existing forums in this group?
        $allforums = getStructuredForums(FALSE);
        if (isset($allforums['forum'][$gid])) {
            if (X_GUEST) {
                redirect("{$full_url}misc.php?action=login", 0);
                exit;
            } else {
                error($lang['privforummsg']);
            }
        }
        unset($allforums);
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
    $where = '';
    if ( 'on' == $SETTINGS['hide_banned'] ) {
        $where = "AND status != 'Banned'";
    }
    $query1 = $db->query("SELECT username FROM ".X_PREFIX."members WHERE lastvisit != 0 $where ORDER BY regdate DESC LIMIT 1");
    if ($db->num_rows($query1) == 1) {
        $lastmember = $db->fetch_array($query1);

        $query = $db->query("SELECT COUNT(*) FROM ".X_PREFIX."members UNION ALL SELECT COUNT(*) FROM ".X_PREFIX."threads UNION ALL SELECT COUNT(*) FROM ".X_PREFIX."posts");
        $members = (int) $db->result($query, 0);
        $threads = (int) $db->result($query, 1);
        $posts = (int) $db->result($query, 2);
        $db->free_result($query);

        $memhtml = '<a href="member.php?action=viewpro&amp;member='.recodeOut($lastmember['username']).'"><strong>'.$lastmember['username'].'</strong></a>.';
        $search  = [ '$threads', '$posts', '$members' ];
        $replace = [  $threads,   $posts,   $members  ];
        $indexstats = str_replace( $search, $replace, $lang['evalindexstats'] );
        eval('$statsbar = "'.template('index_stats').'";');
    }
    $db->free_result($query1);
}

if ($gid == 0) {
    if ( X_MEMBER ) {
        eval('$welcome = "'.template('index_welcome_member').'";');
    } elseif ( coppa_check() ) {
        eval('$welcome = "'.template('index_welcome_guest').'";');
    } else {
        $welcome = '';
    }

    $whosonline = $whosonlinetoday = '';
    if ($SETTINGS['whosonlinestatus'] == 'on') {
        $hiddencount = 0;
        $membercount = 0;
        $guestcount = (int) $db->result($db->query("SELECT COUNT(DISTINCT ip) AS guestcount FROM ".X_PREFIX."whosonline WHERE username = 'xguest123'"), 0);
        $member = array();
        $where = '';
        if ( 'on' == $SETTINGS['hide_banned'] ) {
            $where = "WHERE m.status != 'Banned'";
        }
        $query = $db->query("SELECT m.username, MAX(m.status) AS status, MAX(m.invisible) AS invisible FROM ".X_PREFIX."members AS m INNER JOIN ".X_PREFIX."whosonline USING (username) $where GROUP BY m.username ORDER BY m.username");
        while($online = $db->fetch_array($query)) {
            if ( '0' !== $online['invisible'] && X_ADMIN ) {
                $member[] = $online;
                $hiddencount++;
            } else if ( '0' !== $online['invisible'] ) {
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

        $search  = [ '$guestn', '$membern', '$hiddenn', '$bbname' ];
        $replace = [  $guestn,   $membern,   $hiddenn,   $bbname  ];
        $whosonmsg = str_replace( $search, $replace, $lang['whosoneval'] );
        $memonmsg = "<span class='smalltxt'>$whosonmsg</span>";

        $memtally = array();
        $num = 1;
        $show_total = (X_ADMIN) ? ($membercount+$hiddencount) : ($membercount);

        $show_inv_key = false;
        for($mnum=0; $mnum<$show_total; $mnum++) {
            $pre = $suff = '';

            $online = $member[$mnum];

            $pre = '<span class="status_'.str_replace(' ', '_', $online['status']).'">';
            $suff = '</span>';

            if ( '0' !== $online['invisible'] ) {
                $pre .= '<strike>';
                $suff = '</strike>'.$suff;
                if (!X_ADMIN && $online['username'] !== $xmbuser) {
                    $num++;
                    continue;
                }
            }

            if ( $online['username'] === $xmbuser && '0' !== $online['invisible'] ) {
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
            $where = '';
            if ( 'on' == $SETTINGS['hide_banned'] ) {
                $where = "AND status != 'Banned'";
            }
            if (X_ADMIN) {
                $query = $db->query("SELECT username, status FROM ".X_PREFIX."members WHERE lastvisit >= '$datecut' $where ORDER BY lastvisit DESC");
            } else {
                $query = $db->query("SELECT username, status FROM ".X_PREFIX."members WHERE lastvisit >= '$datecut' AND invisible != 1 $where ORDER BY lastvisit DESC");
            }

            $todaymembersnum = $db->num_rows($query);
            $todaymembers = array();
            $pre = $suff = '';
            $x = 0;
            while($memberstoday = $db->fetch_array($query)) {
                if ( $x <= $SETTINGS['onlinetodaycount'] ) {
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
            $last50today = str_replace( '$onlinetodaycount', $SETTINGS['onlinetodaycount'], $lang['last50todayeval'] );
            eval('$whosonlinetoday = "'.template('index_whosonline_today').'";');
        }

        eval('$whosonline = "'.template('index_whosonline').'";');
    }
} else {
    $ticker = $welcome = $whosonline = $statsbar = $whosonlinetoday = '';
}

$fquery = getIndexForums($forums, $cat, $SETTINGS['catsonly'] == 'on');

$indexBarTop = $indexBar = $forumlist = $spacer = '';
$forumarray = array();
$catLessForums = 0;

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

$lastcat = '0';
foreach($fquery as $thing) {

    if ($SETTINGS['catsonly'] != 'on' || $gid > 0) {
        $cforum = forum($thing, "index_forum", $index_subforums);
    } else {
        $cforum = '';
    }

    if ( '0' === $thing['cat_fid'] ) {
        $catLessForums++;
    }

    if ( $lastcat !== $thing['cat_fid'] && ( $SETTINGS['catsonly'] == 'on' || !empty( $cforum ) ) ) {
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
 * @return array Two-dimensional array of forums (arrays of strings) sorted by the group's displayorder, then the forum's displayorder.
 */
function getIndexForums( array $forums, array $cat, bool $catsonly ): array {
    $sorted = array();

    if (isset($cat['fid'])) {
        // Group forums.
        if (isset($forums['forum'][$cat['fid']])) {
            foreach($forums['forum'][$cat['fid']] as $forum) {
                $forum['cat_fid'] = $cat['fid'];
                $forum['cat_name'] = $cat['name'];
                $sorted[] = $forum;
            }
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
            $forum['cat_fid'] = '0';
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
