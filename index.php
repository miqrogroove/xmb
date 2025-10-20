<?php

/**
 * eXtreme Message Board
 * XMB 1.10.01
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

require './header.php';

$core = Services\core();
$db = Services\db();
$forumcache = Services\forums();
$settings = Services\settings();
$sql = Services\sql();
$template = Services\template();
$vars = Services\vars();
$lang = &$vars->lang;

$forums = $core->getStructuredForums(usePerms: true);

$local_index_stats = $settings->get('index_stats');
$local_tickerstatus = $settings->get('tickerstatus');
$local_whosonlinestatus = $settings->get('whosonlinestatus');

if (onSubmit('gid')) {
    $gid = getInt('gid');
    $local_index_stats = 'off';
    $local_tickerstatus = 'off';
    $local_whosonlinestatus = 'off';
    $cat = $forumcache->getForum($gid);

    if ($cat === null) {
        header('HTTP/1.0 404 Not Found');
        $core->error($lang['textnocat']);
    } elseif ($cat['type'] != 'group') {
        header('HTTP/1.0 404 Not Found');
        $core->error($lang['textnocat']);
    } elseif (! isset($forums['forum'][$gid])) {
        // Does this user not have permissions for any existing forums in this group?
        $allforums = $core->getStructuredForums(usePerms: false);
        if (isset($allforums['forum'][$gid])) {
            if (X_GUEST) {
                $core->redirect($vars->full_url . "misc.php?action=login", timeout: 0);
            } else {
                $core->error($lang['privforummsg']);
            }
        }
        unset($allforums);
    }

    $core->setCanonicalLink("index.php?gid=$gid");
    $core->nav(fnameOut($cat['name']));
    if ($settings->get('subject_in_title') == 'on') {
        $template->threadSubject = fnameOut($cat['name']) . ' - ';
    }
} else {
    $gid = 0;
    $cat = [];
    $core->setCanonicalLink('./');
}

$header = $template->process('header.php');

$body = new Template($vars);
$body->addRefs();

$body->ticker = '';
if ($local_tickerstatus == 'on' && $gid == 0) {
    $template->contents = '';
    $news = explode("\n", str_replace(["\r\n", "\r"], ["\n"], $settings->get('tickercontents')));
    $counter = 0;
    foreach ($news as $item) {
        if (strlen(trim($item)) == 0) {
            continue;
        }
        if ('bbcode' == $settings->get('tickercode')) {
            $item = $core->postify($item, 'no', 'no', 'yes', 'no', 'yes', 'yes', false, 'no', 'no');
        } elseif ('html' == $settings->get('tickercode')) {
            $item = rawHTML($item);
        }
        $item = str_replace('\"', '"', addslashes($item));
        $template->contents .= "\tcontents[$counter]='$item';\n";
        $counter++;
    }
    $body->ticker = $template->process('index_ticker.php');
}

if (X_SMOD && $gid == 0) {
    $result = $sql->countPosts(quarantine: true);
    if ($result > 0) {
        if (1 == $result) {
            $msg = str_replace(
                ['$url'],
                [$vars->full_url . 'quarantine.php'],
                $lang['moderation_notice_single'],
            );
        } else {
            $msg = str_replace(
                ['$result', '$url'],
                [(string) $result, $vars->full_url . 'quarantine.php'],
                $lang['moderation_notice_eval'],
            );
        }
        $body->ticker .= $core->message(
            msg: $msg,
            showheader: false,
            die: false,
            return_as_string: true,
            showfooter: false
        ) . "<br />\n";
    }
}

$body->statsbar = '';
if ($local_index_stats == 'on' && $gid == 0) {
    $where = '';
    if ('on' == $settings->get('hide_banned')) {
        $where = "AND status != 'Banned'";
    }
    $query1 = $db->query("SELECT username FROM " . $vars->tablepre . "members WHERE lastvisit != 0 $where ORDER BY regdate DESC LIMIT 1");
    if ($db->num_rows($query1) == 1) {
        $lastmember = $db->fetch_array($query1);

        $query = $db->query("SELECT COUNT(*) FROM " . $vars->tablepre . "members UNION ALL SELECT COUNT(*) FROM " . $vars->tablepre . "threads UNION ALL SELECT COUNT(*) FROM " . $vars->tablepre . "posts");
        $members = (int) $db->result($query, 0);
        $threads = (int) $db->result($query, 1);
        $posts = (int) $db->result($query, 2);
        $db->free_result($query);

        $template->memhtml = '<a href="member.php?action=viewpro&amp;member='.recodeOut($lastmember['username']).'"><strong>'.$lastmember['username'].'</strong></a>.';
        $search  = [ '$threads', '$posts', '$members' ];
        $replace = [  $threads,   $posts,   $members  ];
        $template->indexstats = str_replace($search, $replace, $lang['evalindexstats']);
        $body->statsbar = $template->process('index_stats.php');
    }
    $db->free_result($query1);
}

$body->welcome = '';
$body->whosonline = '';
if ($gid == 0) {
    if (X_MEMBER) {
        $template->hUsername = $vars->self['username'];
        $body->welcome = $template->process('index_welcome_member.php');
    } elseif ($core->coppa_check()) {
        $body->welcome = $template->process('index_welcome_guest.php');
    }

    if ($local_whosonlinestatus == 'on') {
        $hiddencount = 0;
        $membercount = 0;
        $guestcount = (int) $db->result($db->query("SELECT COUNT(DISTINCT ip) AS guestcount FROM " . $vars->tablepre . "whosonline WHERE username = 'xguest123'"), 0);
        $member = array();
        $where = '';
        if ('on' == $settings->get('hide_banned')) {
            $where = "WHERE m.status != 'Banned'";
        }
        $query = $db->query("SELECT m.username, MAX(m.status) AS status, MAX(m.invisible) AS invisible FROM " . $vars->tablepre . "members AS m INNER JOIN " . $vars->tablepre . "whosonline USING (username) $where GROUP BY m.username ORDER BY m.username");
        while ($online = $db->fetch_array($query)) {
            if ('1' === $online['invisible'] && X_ADMIN) {
                $member[] = $online;
                $hiddencount++;
            } else if ('1' === $online['invisible']) {
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
        $replace = [  $guestn,   $membern,   $hiddenn,   $settings->get('bbname')  ];
        $whosonmsg = str_replace($search, $replace, $lang['whosoneval']);
        $template->memonmsg = "<span class='smalltxt'>$whosonmsg</span>";

        $memtally = [];
        $num = 1;
        $show_total = (X_ADMIN) ? ($membercount+$hiddencount) : ($membercount);

        $show_inv_key = false;
        for ($mnum=0; $mnum<$show_total; $mnum++) {
            $pre = $suff = '';

            $online = $member[$mnum];

            $pre = '<span class="status_'.str_replace(' ', '_', $online['status']).'">';
            $suff = '</span>';

            if ('1' === $online['invisible']) {
                $pre .= '<strike>';
                $suff = '</strike>'.$suff;
                if (!X_ADMIN && $online['username'] !== $vars->xmbuser) {
                    $num++;
                    continue;
                }
            }

            if ($online['username'] === $vars->xmbuser && '1' === $online['invisible']) {
                $show_inv_key = true;
            }

            $memtally[] = '<a href="member.php?action=viewpro&amp;member='.recodeOut($online['username']).'">'.$pre.''.$online['username'].''.$suff.'</a>';
            $num++;
        }

        if (X_ADMIN || $show_inv_key === true) {
            $template->hidden = ' - <strike>'.$lang['texthmem'].'</strike>';
        } else {
            $template->hidden = '';
        }

        $template->memtally = implode(', ', $memtally);
        if ($template->memtally == '') {
            $template->memtally = '&nbsp;';
        }

        $template->whosonlinetoday = '';
        if ($settings->get('onlinetoday_status') == 'on') {
            $datecut = $vars->onlinetime - (3600 * 24);
            $where = '';
            if ('on' == $settings->get('hide_banned')) {
                $where = "AND status != 'Banned'";
            }
            if (X_ADMIN) {
                $query = $db->query("SELECT username, status FROM " . $vars->tablepre . "members WHERE lastvisit >= '$datecut' $where ORDER BY lastvisit DESC");
            } else {
                $query = $db->query("SELECT username, status FROM " . $vars->tablepre . "members WHERE lastvisit >= '$datecut' AND invisible != '1' $where ORDER BY lastvisit DESC");
            }

            $todaymembersnum = $db->num_rows($query);
            $todaymembers = [];
            $pre = $suff = '';
            $x = 0;
            while ($memberstoday = $db->fetch_array($query)) {
                if ($x <= $settings->get('onlinetodaycount')) {
                    $pre = '<span class="status_'.str_replace(' ', '_', $memberstoday['status']).'">';
                    $suff = '</span>';
                    $todaymembers[] = '<a href="member.php?action=viewpro&amp;member='.recodeOut($memberstoday['username']).'">'.$pre.''.$memberstoday['username'].''.$suff.'</a>';
                    $x++;
                } else {
                    continue;
                }
            }
            $template->todaymembers = implode(', ', $todaymembers);
            $db->free_result($query);

            if ($todaymembersnum == 1) {
                $template->memontoday = $todaymembersnum.$lang['textmembertoday'];
            } else {
                $template->memontoday = $todaymembersnum.$lang['textmemberstoday'];
            }
            $template->last50today = str_replace('$onlinetodaycount', $settings->get('onlinetodaycount'), $lang['last50todayeval']);
            $template->whosonlinetoday = $template->process('index_whosonline_today.php');
        }

        $body->whosonline = $template->process('index_whosonline.php');
    }
}

$fquery = $core->getIndexForums($forums, $cat, $settings->get('catsonly') == 'on');

if ($settings->get('catsonly') == 'on' && $gid == 0 && count($fquery) == 0) {
    // The admin has chosen to show categories only, but no existing categories are turned on.  Let's avoid this.
    $settings->put('catsonly', 'off');
    $fquery = $core->getIndexForums($forums, $cat, catsonly: false);
}

$body->indexBarTop = '';
$indexBar = $forumlist = $spacer = '';
$forumarray = [];
$catLessForums = 0;

if ($settings->get('space_cats') == 'on') {
    $spacer = $template->process('index_category_spacer.php');
}

if ($settings->get('catsonly') != 'on') {
    if ($settings->get('indexshowbar') == 1) {
        $indexBar = $template->process('index_category_hr.php');
        $body->indexBarTop = $indexBar;
    }

    if ($settings->get('indexshowbar') == 2) {
        $body->indexBarTop = $template->process('index_category_hr.php');
    }
} elseif ($gid > 0) {
    $indexBar = $template->process('index_category_hr.php');
}

// Collect Subforums ordered by fup, displayorder
$index_subforums = [];
if ($settings->get('showsubforums') == 'on') {
    if ($settings->get('catsonly') != 'on' || $gid > 0) {
        foreach ($forums['sub'] as $subForumsByFUP) {
            foreach ($subForumsByFUP as $forum) {
                $index_subforums[] = $forum;
            }
        }
    }
}

$lastcat = '0';
foreach ($fquery as $thing) {
    if ($settings->get('catsonly') != 'on' || $gid > 0) {
        $cforum = $core->forum($thing, "index_forum", $index_subforums);
    } else {
        $cforum = '';
    }

    if ('0' === $thing['cat_fid']) {
        $catLessForums++;
    }

    if ($lastcat !== $thing['cat_fid'] && ($settings->get('catsonly') == 'on' || ! empty($cforum))) {
        if ($forumlist != '') {
            $forumarray[] = $forumlist;
            $forumlist = '';
        }
        $lastcat = $thing['cat_fid'];
        $thing['cat_name'] = fnameOut($thing['cat_name']);
        $template->thing = $thing;
        $forumlist .= $template->process('index_category.php');
        if ($settings->get('catsonly') != 'on' || $gid > 0) {
            $forumlist .= $indexBar;
        }
    }

    if (! empty($cforum)) {
        $forumlist .= $cforum;
    }
}

$forumarray[] = $forumlist;
$body->forumlist = implode($spacer, $forumarray);

if ($body->forumlist == '') {
    if (X_GUEST && $gid == 0) {
        $template->message = $lang['reggedonly'];
    } else {
        $template->message = $lang['textnoforumsexist'];
    }
    $body->forumlist = $template->process('index_noforum.php');
}
unset($fquery);

if ($catLessForums == 0 && $settings->get('indexshowbar') == 1) {
    $body->indexBarTop = '';
}

$index = $body->process('index.php');
$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $index, $footer;
