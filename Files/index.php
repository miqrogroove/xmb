<?php
/* $Id: index.php,v 1.3.2.12 2006/09/19 22:57:28 Tularis Exp $ */
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

loadtemplates('footer_load', 'footer_phpsql', 'footer_querynum', 'footer_totaltime', 'functions_bbcode', 'index', 'index_category', 'index_category_hr','index_category_spacer', 'index_forum', 'index_forum_lastpost', 'index_forum_nolastpost', 'index_ticker', 'index_welcome_guest', 'index_welcome_member', 'index_whosonline');
eval('$css = "'.template('css').'";');

$ticker = '';

if ($SETTINGS['tickerstatus'] == 'on') {
    $contents   = '';
    $news       = explode("\n", str_replace(array("\r\n", "\r"), array("\n"), $SETTINGS['tickercontents']));
    for($i=0;$i<count($news);$i++) {
        if (strlen(trim($news[$i])) == 0) {
            continue;
        }
        $news[$i]  = postify($news[$i], 'no', 'no', 'yes', 'no', 'yes', 'yes', false, 'yes', 'no');
        $news[$i]  = str_replace('\"', '"', addslashes($news[$i]));
        $contents .= "\tcontents[$i]='$news[$i]';\n";
    }
    eval('$ticker  = "'.template('index_ticker').'";');
}


if (isset($gid) && is_numeric($gid)) {
    $gid = (int) $gid;
    $SETTINGS['whosonlinestatus'] = 'off';
    $query = $db->query("SELECT name FROM $table_forums WHERE fid='$gid' AND type='group' LIMIT 1");
    $cat = $db->fetch_array($query);
    $db->free_result($query);

    nav(stripslashes($cat['name']));
} else {
    $gid = 0;
}

eval('echo "'.template('header').'";');

// Start Stats

$query = $db->query("SELECT username FROM $table_members ORDER BY regdate DESC LIMIT 1");
$lastmember = $db->fetch_array($query);
$db->free_result($query);

$query = $db->query("SELECT count(uid) FROM $table_members");
$members = $db->result($query, 0);
$db->free_result($query);

$query = $db->query("SELECT COUNT(tid) FROM $table_threads");
$threads = $db->result($query, 0);
$db->free_result($query);

$query = $db->query("SELECT COUNT(pid) FROM $table_posts");
$posts = $db->result($query, 0);
$db->free_result($query);

$memhtml = "<a href=\"member.php?action=viewpro&amp;member=".rawurlencode($lastmember['username'])."\"><strong>$lastmember[username]</strong></a>.";
eval($lang['evalindexstats']);

if ( $gid == 0) {
    if ( X_MEMBER ) {
        eval("\$welcome = \"".template("index_welcome_member")."\";");
    } else {
        eval("\$welcome = \"".template("index_welcome_guest")."\";");
    }

    // Start Whos Online
    if($SETTINGS['whosonlinestatus'] == "on") {
        $guestcount     = 0;
        $membercount    = 0;
        $hiddencount    = 0;
        $member         = array();

        $query  = $db->query("SELECT m.status, m.username, m.invisible, w.* FROM $table_whosonline w LEFT JOIN $table_members m ON m.username=w.username ORDER BY w.username");
        while($online = $db->fetch_array($query)) {
            switch($online['username']) {
                case 'Anonymous':
                    $guestcount++;
                    break;

                default:
                    if ($online['invisible'] != 0 && X_ADMIN) {
                        $member[] = $online;
                        $hiddencount++;
                    } elseif ( $online['invisible'] != 0) {
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
            $membern = "<strong>$membercount</strong> $lang[textmembers]";
        } else {
            $membern = "<strong>1</strong> $lang[textmem]";
        }

        if ($guestcount != 1) {
            $guestn = "<strong>$guestcount</strong> $lang[textguests]";
        } else {
            $guestn = "<strong>1</strong> $lang[textguest1]";
        }

        if ($hiddencount != 1) {
            $hiddenn = "<strong>$hiddencount</strong> $lang[texthmems]";
        } else {
            $hiddenn = "<strong>1</strong> $lang[texthmem]";
        }

        eval($lang['whosoneval']);
        $memonmsg = "<span class=\"smalltxt\">$lang[whosonmsg]</span>";

        $memtally = array();
        $num = 1;
        $comma = "";
        $show_total = (X_ADMIN) ? ($membercount+$hiddencount) : ($membercount);

        $show_inv_key = false;

        for($mnum=0; $mnum<$show_total; $mnum++) {
            $pre = '';
            $suff = '';

            $online = $member[$mnum];

            $pre = '<span class="status_'.str_replace(' ', '_', $online['status']).'">';
            $suff = '</span>';

            if ($online['invisible'] != 0) {
                $pre .= '<strike>';
                $suff = '</strike>'.$suff;
                if (!X_ADMIN && $online['username'] != $self['username']) {
                    $num++;
                    continue;
                }
            }
            if ($online['username'] == $self['username'] && $online['invisible'] != 0) {
                $show_inv_key = true;
            } else {
                $show_inv_key = false;
            }

            $memtally[] = "<a href=\"member.php?action=viewpro&amp;member=".rawurlencode($online['username'])."\">$pre$online[username]$suff</a>";

            $num++;
        }
        if(X_ADMIN || $show_inv_key === true) {
            $hidden = " - <strike>$lang[texthmem]</strike>";
        } else {
            $hidden = '';
        }
        $memtally = implode(', ', $memtally);

        if ( $memtally == "") {
            $memtally = "&nbsp;";
        }

        $datecut = time() - (3600 * 24);
        if (X_ADMIN) {
            $query = $db->query("SELECT username FROM $table_members WHERE lastvisit >= '$datecut' ORDER BY lastvisit DESC LIMIT 0, 50");
        }else{
            $query = $db->query("SELECT username FROM $table_members WHERE lastvisit >= '$datecut' AND invisible != '1' ORDER BY lastvisit DESC LIMIT 0, 50");
        }

        $todaymembersnum = 0;
        $todaymembers = array();

        while($memberstoday = $db->fetch_array($query)) {
                $todaymembers[] = '<a href="member.php?action=viewpro&amp;member='.rawurlencode($memberstoday['username']).'">'.$memberstoday['username'].'</a>';
                ++$todaymembersnum;
        }
        
        $todaymembers = implode(', ', $todaymembers);

        $db->free_result($query);

        if ($todaymembersnum == 1) {
            $memontoday = $todaymembersnum.$lang['textmembertoday'];
        } else {
            $memontoday = $todaymembersnum.$lang['textmemberstoday'];
        }

        eval('$whosonline = "'.template('index_whosonline').'";');
    } else {
        $whosonline = '';
    }

    // End Whosonline and Stats

    // Start Getting Forums and Groups
    if($SETTINGS['catsonly'] == 'on') {
        $fquery = $db->query("SELECT name as cat_name, fid as cat_fid FROM $table_forums WHERE type='group' ORDER BY displayorder ASC");
    } else {
        $fquery = $db->query("SELECT f.*, c.name as cat_name, c.fid as cat_fid FROM $table_forums f LEFT JOIN $table_forums c ON (f.fup = c.fid) WHERE (c.type='group' AND f.type='forum' AND c.status='on' AND f.status='on') OR (f.type='forum' AND f.fup='' AND f.status='on') ORDER BY c.displayorder ASC, f.displayorder ASC");
    }
}else {
    $welcome    = '';
    $whosonline = '';
    $fquery     = $db->query("SELECT f.*, c.name as cat_name, c.fid as cat_fid FROM $table_forums f LEFT JOIN $table_forums c ON (f.fup = c.fid) WHERE (c.type='group' AND f.type='forum' AND c.status='on' AND f.status='on' AND f.fup='$gid') ORDER BY c.displayorder ASC, f.displayorder ASC");
}

$indexBarTop    = '';
$indexBar       = '';
$lastcat        = 0;
$forumlist      = '';
$catLessForums  = 0;
if($SETTINGS['space_cats'] == 'on') {
    eval("\$spacer = \"".template("index_category_spacer")."\";");
} else {
    $spacer = '';
}

// all cats
if($SETTINGS['catsonly'] != 'on') {
    if($SETTINGS['indexshowbar'] == 1) {
        eval('$indexBar = "'.template('index_category_hr').'";');
        $indexBarTop = $indexBar;
    }

    // top only
    if($SETTINGS['indexshowbar'] == 2) {
        eval('$indexBarTop = "'.template('index_category_hr').'";');
    }
} elseif($gid > 0) {
    eval('$indexBar = "'.template('index_category_hr').'";');
}

while($thing = $db->fetch_array($fquery)) {
    if($catsonly != 'on' || $gid > 0) {
        $cforum = forum($thing, "index_forum");
    } else {
        $cforum = '';
    }

    if((int) $thing['cat_fid'] === 0) {
        $catLessForums++;
    }

    if($lastcat != $thing['cat_fid'] && ($SETTINGS['catsonly'] == 'on' || (!empty($cforum) && $SETTINGS['catsonly'] != 'on'))) {
        $lastcat = $thing['cat_fid'];
        eval("\$forumlist .= \"".template('index_category')."\";");
        if($SETTINGS['catsonly'] != 'on' || $gid > 0) {
            $forumlist .= $indexBar;
        }
    }

    $forumlist .= $cforum;
}

$db->free_result($fquery);

if($catLessForums == 0 && $SETTINGS['indexshowbar'] == 1) {
    $indexBarTop = '';
}

eval('$index = "'.template('index').'";');
end_time();
eval("\$footer = \"".template("footer")."\";");
echo stripslashes($index . $footer);
