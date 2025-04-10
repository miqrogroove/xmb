<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
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
 */

$attach = \XMB\Services\attach();
$core = \XMB\Services\core();
$forumCache = \XMB\Services\forums();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$validate = \XMB\Services\validate();
$vars = \XMB\Services\vars();

require 'header.php';

if (X_GUEST) {
    redirect("{$full_url}misc.php?action=login", 0);
    exit;
}

$onlinetime = $vars->onlinetime;

$tids = array_unique(postedArray('tid', 'int', source: 'r'));
$fid = getInt('fid', 'p');
if ($fid == 0) {
    $fid = getInt('fid');
}
$pid = getInt('pid');
$othertid = formInt('othertid');
$action = postedVar('action', '', TRUE, TRUE, FALSE, 'r');

loadtemplates(
'topicadmin_delete',
'topicadmin_openclose',
'topicadmin_move',
'topicadmin_topuntop',
'topicadmin_bump',
'topicadmin_split_row',
'topicadmin_split',
'topicadmin_merge',
'topicadmin_empty',
'topicadmin_threadprune_row',
'topicadmin_threadprune',
'topicadmin_copy'
);

if (count($tids) == 1) {
    $query = $db->query("SELECT * FROM ".X_PREFIX."threads WHERE tid={$tids[0]}");
    $thread = $db->fetch_array($query);
    $db->free_result($query);
    $threadname = rawHTMLsubject(stripslashes($thread['subject']));
    $fid = (int) $thread['fid'];
} else {
    $threadname = '';
}

$forums = $forumCache->getForum($fid);

if (false === $forums || ($forums['type'] != 'forum' && $forums['type'] != 'sub') || $forums['status'] != 'on') {
    header('HTTP/1.0 404 Not Found');
    error($lang['textnoforum']);
}

// Check for authorization to be here in the first place
$perms = checkForumPermissions($forums);
if (!$perms[$vars::PERMS_VIEW]) {
    error($lang['privforummsg']);
} else if (!$perms[$vars::PERMS_PASSWORD]) {
    handlePasswordDialog($fid);
}

$fup = array();
if ($forums['type'] == 'sub') {
    $fup = $forumCache->getForum((int) $forums['fup']);
    // prevent access to subforum when upper forum can't be viewed.
    $fupPerms = checkForumPermissions($fup);
    if (!$fupPerms[$vars::PERMS_VIEW]) {
        error($lang['privforummsg']);
    } else if (!$fupPerms[$vars::PERMS_PASSWORD]) {
        handlePasswordDialog((int) $fup['fid']);
    } else if ((int) $fup['fup'] > 0) {
        $fupup = $forumCache->getForum((int) $fup['fup']);
        nav('<a href="index.php?gid='.$fup['fup'].'">'.fnameOut($fupup['name']).'</a>');
        unset($fupup);
    }
    nav('<a href="forumdisplay.php?fid='.$fup['fid'].'">'.fnameOut($fup['name']).'</a>');
} else if ((int) $forums['fup'] > 0) { // 'forum' in a 'group'
    $fup = $forumCache->getForum((int) $forums['fup']);
    nav('<a href="index.php?gid='.$fup['fid'].'">'.fnameOut($fup['name']).'</a>');
}
nav('<a href="forumdisplay.php?fid='.$fid.'">'.fnameOut($forums['name']).'</a>');
if (count($tids) == 1) {
    nav('<a href="viewthread.php?tid='.$tids[0].'">'.$threadname.'</a>');
}

$kill = FALSE;

switch($action) {
    case 'delete':
        nav($lang['textdeletethread']);
        break;
    case 'top':
        nav($lang['texttopthread']);
        break;
    case 'close':
        nav($lang['textclosethread']);
        break;
    case 'copy':
        nav($lang['copythread']);
        break;
    case 'f_close':
        nav($lang['textclosethread']);
        break;
    case 'f_open':
        nav($lang['textopenthread']);
        break;
    case 'move':
        nav($lang['textmovemethod1']);
        break;
    case 'getip':
        $kill |= !X_ADMIN;
        nav($lang['textgetip']);
        break;
    case 'bump':
        nav($lang['textbumpthread']);
        break;
    case 'split':
        nav($lang['textsplitthread']);
        break;
    case 'merge':
        nav($lang['textmergethread']);
        break;
    case 'threadprune':
        nav($lang['textprunethread']);
        break;
    case 'empty':
        nav($lang['textemptythread']);
        break;
    default:
        $kill = TRUE;
        break;
}

$kill |= !X_STAFF || !statuscheck($fid);

if ($kill) {
    error($lang['notpermitted']);
}

if ($SETTINGS['subject_in_title'] == 'on') {
    $threadSubject = $threadname . ' - ';
}

// Search-link
$template->searchlink = makeSearchLink($forums['fid']);

if (0 === $pid && 'getip' === $action) {
    header('HTTP/1.0 404 Not Found');
    error($lang['noresults']);
}

eval('echo "'.template('header').'";');

//Assert permissions on all TIDs
if (count($tids) > 1) {
    $csv = implode(',', $tids);
    $tids = array();
    $query = $db->query("SELECT tid FROM ".X_PREFIX."threads WHERE tid IN ($csv) AND fid=$fid");
    while ($row = $db->fetch_array($query)) {
        $tids[] = $row['tid'];
    }
    $db->free_result($query);
    unset($csv);
}

$template->hUsername = $vars->self['username'];

switch($action) {
    case 'delete':
        if (noSubmit('deletesubmit')) {
            $tid = implode(',', $tids);
            $template = template_secure('topicadmin_delete', 'Thread Admin Options/Delete', (string) min($tids), $vars::NONCE_AYS_EXP);
            eval('echo "'.$template.'";');
        } else {
            request_secure('Thread Admin Options/Delete', (string) min($tids));

            foreach($tids AS $tid) {
                $query = $db->query("SELECT author, COUNT(*) AS pidcount FROM ".X_PREFIX."posts WHERE tid=$tid GROUP BY author");
                while($result = $db->fetch_array($query)) {
                    $db->escape_fast($result['author']);
                    $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-{$result['pidcount']} WHERE username='{$result['author']}'");
                }
                $db->free_result($query);

                $attach->deleteByThread($tid);  // Must delete attachments before posts!
                $db->query("DELETE FROM ".X_PREFIX."posts WHERE tid=$tid");
                $db->query("DELETE FROM ".X_PREFIX."favorites WHERE tid=$tid");
                $sql->deleteVotesByTID([$tid]);

                $db->query("DELETE FROM ".X_PREFIX."threads WHERE tid=$tid OR closed='moved|$tid'");

                if ($forums['type'] == 'sub') {
                    updateforumcount($fup['fid']);
                }
                updateforumcount($fid);

                audit($self['username'], $action, $fid, $tid);
            }
            message($lang['deletethreadmsg'], false, '', '', $full_url.'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'close':
        $tid = $tids[0];
        $query = $db->query("SELECT closed FROM ".X_PREFIX."threads WHERE tid=$tid");
        if ($db->num_rows($query) == 0) {
            error($lang['textnothread'], FALSE);
        }
        $closed = $db->result($query, 0);
        $db->free_result($query);

        if (noSubmit('closesubmit')) {
            if ($closed == 'yes') {
                $lang['textclosethread'] = $lang['textopenthread'];
            } else if ($closed == '') {
                $lang['textclosethread'] = $lang['textclosethread'];
            }
            $template = template_secure('topicadmin_openclose', 'Thread Admin Options/OpenOrClose', (string) $tid, $vars::NONCE_AYS_EXP);
            eval('echo "'.$template.'";');
        } else {
            request_secure('Thread Admin Options/OpenOrClose', (string) $tid);
            if ($closed == 'yes') {
                $db->query("UPDATE ".X_PREFIX."threads SET closed='' WHERE tid=$tid");
            } else {
                $db->query("UPDATE ".X_PREFIX."threads SET closed='yes' WHERE tid=$tid");
            }

            $act = ($closed != '') ? 'open' : 'close';
            audit($self['username'], $act, $fid, $tid);

            message($lang['closethreadmsg'], false, '', '', $full_url.'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'f_close':
        if (noSubmit('closesubmit')) {
            $tid = implode(',', $tids);
            $template = template_secure('topicadmin_openclose', 'Thread Admin Options/Close', (string) min($tids), $vars::NONCE_AYS_EXP);
            eval('echo "'.$template.'";');
        } else {
            request_secure('Thread Admin Options/Close', (string) min($tids));
            if (count($tids) > 0) {
                $csv = implode(',', $tids);
                $db->query("UPDATE ".X_PREFIX."threads SET closed='yes' WHERE tid IN ($csv)");
                foreach($tids AS $tid) {
                    audit($self['username'], 'close', $fid, $tid);
                }
            }
            message($lang['closethreadmsg'], false, '', '', $full_url.'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'f_open':
        if (noSubmit('closesubmit')) {
            $tid = implode(',', $tids);
            $lang['textclosethread'] = $lang['textopenthread'];
            $template = template_secure('topicadmin_openclose', 'Thread Admin Options/Open', (string) min($tids), $vars::NONCE_AYS_EXP);
            eval('echo "'.$template.'";');
        } else {
            request_secure('Thread Admin Options/Open', (string) min($tids));
            if (count($tids) > 0) {
                $csv = implode(',', $tids);
                $db->query("UPDATE ".X_PREFIX."threads SET closed='' WHERE tid IN ($csv)");
                foreach($tids AS $tid) {
                    audit($self['username'], 'open', $fid, $tid);
                }
            }
            message($lang['closethreadmsg'], false, '', '', $full_url.'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'move':
        if (noSubmit('movesubmit')) {
            $tid = implode(',', $tids);
            $forumselect = forumList('moveto', false, false, $fid);
            $template = template_secure('topicadmin_move', 'Thread Admin Options/Move', (string) min($tids), $vars::NONCE_AYS_EXP);
            eval('echo "'.$template.'";');
        } else {
            request_secure('Thread Admin Options/Move', (string) min($tids));
            $moveto = formInt('moveto');
            $type = $validate->postedVar('type');

            $movetorow = $forumCache->getForum($moveto);
            if ($movetorow === FALSE) {
                error($lang['textnoforum'], FALSE);
            }
            if ($movetorow['type'] == 'group' || $moveto == $fid) {
                error($lang['errormovingthreads'], FALSE);
            }

            //Perform sanity checks on all redirects
            if ($type != 'normal' && count($tids) > 0) {
                $csv = implode(',', $tids);
                $tids = array();
                $query = $db->query("SELECT * FROM ".X_PREFIX."threads WHERE tid IN ($csv)");
                while ($info = $db->fetch_array($query)) {
                    if (substr($info['closed'], 0, 5) != 'moved') {
                        //Insert all thread redirectors.
                        $db->escape_fast($info['author']);
                        $db->escape_fast($info['subject']);
                        $db->query("INSERT INTO ".X_PREFIX."threads (fid, subject, icon, lastpost, views, replies, author, closed, topped) VALUES ({$info['fid']}, '{$info['subject']}', '', '".$db->escape($info['lastpost'])."', 0, 0, '{$info['author']}', 'moved|{$info['tid']}', '{$info['topped']}')");
                        $ntid = $db->insert_id();

                        $lastpost = explode('|', $info['lastpost']);
                        $lastposttime = intval($lastpost[0]);

                        $db->query("INSERT INTO ".X_PREFIX."posts (fid, tid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff) VALUES ({$info['fid']}, '$ntid', '{$info['author']}', '{$info['tid']}', '{$info['subject']}', $lastposttime, '', '', '', '', '')");
                        $tids[] = $info['tid'];
                    }
                }
                $db->free_result($query);
            }

            if (count($tids) > 0) {
                //Perform all moves using as few queries as possible.
                $csv = implode(',', $tids);
                $db->query("UPDATE ".X_PREFIX."threads SET fid=$moveto WHERE tid IN ($csv)");
                $db->query("UPDATE ".X_PREFIX."posts SET fid=$moveto WHERE tid IN ($csv)");
                foreach($tids AS $tid) {
                    audit($self['username'], $action, $moveto, $tid);
                }

                //Update all summary columns.
                if ($forums['type'] == 'sub') {
                    updateforumcount($fup['fid']);
                }
                if ($movetorow['type'] == 'sub') {
                    $doupdate = TRUE;
                    if (isset($fup['fid'])) {
                        $doupdate = ($movetorow['fup'] != $fup['fid']);
                    }
                    if ($doupdate) {
                        updateforumcount($movetorow['fup']);
                    }
                }
                updateforumcount($fid);
                updateforumcount($moveto);
            }

            message($lang['movethreadmsg'], false, '', '', $full_url.'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'top':
        if (noSubmit('topsubmit')) {
            if (count($tids) == 1) {
                $query = $db->query("SELECT topped FROM ".X_PREFIX."threads WHERE tid={$tids[0]}");
                if ($db->num_rows($query) == 0) {
                    $db->free_result($query);
                    error($lang['textnothread'], FALSE);
                }
                $topped = $db->result($query, 0);
                $db->free_result($query);
                if ('1' === $topped) {
                    $lang['texttopthread'] = $lang['textuntopthread'];
                }
            } else {
                $lang['texttopthread'] = $lang['texttopthread'].' / '.$lang['textuntopthread'];
            }
            $tid = implode(',', $tids);
            $template = template_secure('topicadmin_topuntop', 'Thread Admin Options/Top', (string) min($tids), $vars::NONCE_AYS_EXP);
            eval('echo "'.$template.'";');
        } else {
            request_secure('Thread Admin Options/Top', (string) min($tids));
            foreach($tids AS $tid) {
                $query = $db->query("SELECT topped FROM ".X_PREFIX."threads WHERE tid=$tid");
                if ($db->num_rows($query) == 0) {
                    $db->free_result($query);
                    error($lang['textnothread'], FALSE);
                }
                $topped = $db->result($query, 0);
                $db->free_result($query);

                if ('1' === $topped) {
                    $db->query("UPDATE ".X_PREFIX."threads SET topped='0' WHERE tid=$tid");
                } else if ('0' === $topped)    {
                    $db->query("UPDATE ".X_PREFIX."threads SET topped='1' WHERE tid=$tid");
                }

                $act = ($topped ? 'untop' : 'top');
                audit($self['username'], $act, $fid, $tid);
            }

            message($lang['topthreadmsg'], false, '', '', $full_url.'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'getip':
        $useip = $sql->getIPFromPost($pid);
        if ($useip === '') {
            $address = $lang['textnone'];
            $name = $lang['textnone'];
        } else {
            $address = $useip;
            $name = gethostbyaddr($useip);
        }

        ?>
        <form method="post" action="cp.php?action=ipban">
        <input type="hidden" name="token" value="<?php echo \XMB\Token\create('Control Panel/IP Banning', 'mass-edit', $vars::NONCE_AYS_EXP); ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="60%" align="center">
        <tr><td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="header" colspan="3"><?php echo $lang['textgetip']?></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>">
        <td class="tablerow"><?= $lang['textyesip'] ?> <strong><?= $address ?></strong> - <?= $name ?>
        <?php

        $ip = explode('.', $useip);
        if (count($ip) === 4) {
            $query = $db->query("SELECT * FROM ".X_PREFIX."banned WHERE (ip1='$ip[0]' OR ip1='-1') AND (ip2='$ip[1]' OR ip2='-1') AND (ip3='$ip[2]' OR ip3='-1') AND (ip4='$ip[3]' OR ip4='-1')");
            $result = $db->fetch_array($query);
            $db->free_result($query);
            if ($result) {
                $buttontext = $lang['textunbanip'];
                $foundmask = false;
                for($i=1; $i<=4; ++$i) {
                    $j = "ip$i";
                    if ('-1' === $result[$j]) {
                        $result[$j] = "*";
                        $foundmask = true;
                    }
                }

                if ($foundmask) {
                    $ipmask = "<strong>$result[ip1].$result[ip2].$result[ip3].$result[ip4]</strong>";
                    $bannedipmask = str_replace('$ipmask', $ipmask, $lang['evalipmask']);
                    echo $bannedipmask;
                } else {
                    echo $lang['textbannedip'];
                }
                echo "<input type='hidden' name='delete[{$result['id']}]' value='1' />";
            } else {
                $buttontext = $lang['textbanip'];
                for($i=1; $i<=4; ++$i) {
                    $j = $i - 1;
                    echo "<input type=\"hidden\" name=\"newip$i\" value=\"$ip[$j]\" />";
                }
            }
            ?>
            </td>
            </tr>
            <tr bgcolor="<?php echo $altbg1?>"><td class="ctrtablerow"><input type="submit" name="ipbansubmit" value="<?php echo $buttontext?>" />
            <?php
        }

        echo '</td></tr></table></td></tr></table></form>';
        break;

    case 'bump':
        if (noSubmit('bumpsubmit')) {
            $tid = implode(',', $tids);
            $template = template_secure('topicadmin_bump', 'Thread Admin Options/Bump', (string) min($tids), $vars::NONCE_AYS_EXP);
            eval('echo "'.$template.'";');
        } else {
            request_secure('Thread Admin Options/Bump', (string) min($tids));
            foreach($tids AS $tid) {
                $query = $db->query("SELECT pid FROM ".X_PREFIX."posts WHERE tid=$tid ORDER BY dateline DESC, pid DESC LIMIT 1");
                if ($db->num_rows($query) == 1) {
                    $pid = $db->result($query, 0);

                    $where = "WHERE fid=$fid";
                    if ($forums['type'] == 'sub') {
                        $where .= " OR fid={$forums['fup']}";
                    }

                    $db->query("UPDATE ".X_PREFIX."threads SET lastpost='$onlinetime|$xmbuser|$pid' WHERE tid=$tid");
                    $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$onlinetime|$xmbuser|$pid' $where");

                    audit($self['username'], $action, $fid, $tid);
                }
                $db->free_result($query);
            }

            message($lang['bumpthreadmsg'], false, '', '', $full_url.'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'empty':
        if (noSubmit('emptysubmit')) {
            $tid = implode(',', $tids);
            $template = template_secure('topicadmin_empty', 'Thread Admin Options/Empty', (string) min($tids), $vars::NONCE_AYS_EXP);
            eval('echo "'.$template.'";');
        } else {
            request_secure('Thread Admin Options/Empty', (string) min($tids));
            foreach($tids AS $tid) {
                $query = $db->query("SELECT pid FROM ".X_PREFIX."posts WHERE tid=$tid ORDER BY dateline ASC LIMIT 1");
                if ($db->num_rows($query) == 1) {
                    $pid = $db->result($query, 0);
                    $query = $db->query("SELECT author, COUNT(*) AS pidcount FROM ".X_PREFIX."posts WHERE tid=$tid AND pid!=$pid GROUP BY author");
                    while($result = $db->fetch_array($query)) {
                        $db->escape_fast($result['author']);
                        $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-{$result['pidcount']} WHERE username='{$result['author']}'");
                    }

                    $attach->emptyThread($tid, $pid);  // Must delete attachments before posts!
                    $db->query("DELETE FROM ".X_PREFIX."posts WHERE tid=$tid AND pid!=$pid");

                    updatethreadcount($tid); //Also updates lastpost
                    audit($self['username'], $action, $fid, $tid);
                }
                $db->free_result($query);
            }
            if ($forums['type'] == 'sub') {
                updateforumcount($fup['fid']);
            }
            updateforumcount($fid);

            message($lang['emptythreadmsg'], false, '', '', $full_url.'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'split':
        $tid = $tids[0];
        if (noSubmit('splitsubmit')) {
            $query = $db->query("SELECT replies FROM ".X_PREFIX."threads WHERE tid=$tid");
            if ($db->num_rows($query) == 0) {
                error($lang['textnothread'], FALSE);
            }
            $replies = (int) $db->result($query, 0);
            $db->free_result($query);
            if ($replies == 0) {
                error($lang['cantsplit'], false);
            }

            $query = $db->query("SELECT * FROM ".X_PREFIX."posts WHERE tid=$tid ORDER BY dateline");
            $posts = '';
            while($post = $db->fetch_array($query))    {
                $bbcodeoff = $post['bbcodeoff'];
                $smileyoff = $post['smileyoff'];
                $post['message'] = stripslashes($post['message']);
                $post['message'] = postify($post['message'], $smileyoff, $bbcodeoff, $fid, 'no', 'no', 'no');
                eval('$posts .= "'.template('topicadmin_split_row').'";');
            }
            $db->free_result($query);
            $template = template_secure('topicadmin_split', 'Thread Admin Options/Split', (string) min($tids), $vars::NONCE_AYS_EXP);
            eval('echo "'.$template.'";');
        } else {
            request_secure('Thread Admin Options/Split', (string) min($tids));
            $subject = addslashes(postedVar('subject', 'javascript', TRUE, TRUE, TRUE));  // Subjects are historically double-quoted
            if ($subject == '') {
                error($lang['textnosubject'], false);
            }

            $threadcreated = false;
            $firstmove = false;
            $query = $db->query("SELECT pid, author, dateline, subject FROM ".X_PREFIX."posts WHERE tid=$tid ORDER BY dateline ASC");
            $movecount = 0;
            while($post = $db->fetch_array($query)) {
                $db->escape_fast($post['author']);
                $move = getInt('move'.$post['pid'], 'p');
                if ($move == (int) $post['pid']) {
                    if (!$threadcreated) {
                        $thatime = $onlinetime;
                        $db->query("INSERT INTO ".X_PREFIX."threads (fid, subject, icon, lastpost, views, replies, author, closed, topped) VALUES ($fid, '$subject', '', '$thatime|$xmbuser', 0, 0, '{$post['author']}', '', 0)");
                        $newtid = $db->insert_id();
                        $threadcreated = true;
                    }

                    $newsub = '';
                    if (!$firstmove) {
                        $newsub = ", subject='$subject'";
                        $firstmove = true;
                    }
                    $db->query("UPDATE ".X_PREFIX."posts SET tid=$newtid $newsub WHERE pid=$move");
                    $lastpost = $post['dateline'].'|'.$post['author'].'|'.$post['pid'];
                    $movecount++;
                } else {
                    $oldlastpost = $post['dateline'].'|'.$post['author'].'|'.$post['pid'];
                }
            }
            $db->query("UPDATE ".X_PREFIX."threads SET replies=$movecount-1, lastpost='$lastpost' WHERE tid='$newtid'");
            $db->query("UPDATE ".X_PREFIX."threads SET replies=replies-$movecount, lastpost='$oldlastpost' WHERE tid=$tid");
            $db->free_result($query);

            audit($self['username'], $action, $fid, $tid);

            if ($forums['type'] == 'sub') {
                updateforumcount($fup['fid']);
            }
            updateforumcount($fid);

            message($lang['splitthreadmsg'], false, '', '', $full_url.'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'merge':
        $tid = $tids[0];
        if (noSubmit('mergesubmit')) {
            $template = template_secure('topicadmin_merge', 'Thread Admin Options/Merge', (string) min($tids), $vars::NONCE_AYS_EXP);
            eval('echo "'.$template.'";');
        } else {
            request_secure('Thread Admin Options/Merge', (string) min($tids));
            if ($othertid == 0) {
                error($lang['invalidtid'], false);
            } else if ($tid == $othertid) {
                error($lang['cannotmergesamethread'], false);
            }

            $queryadd1 = $db->query("SELECT t.replies, t.fid, f.type, f.fup FROM ".X_PREFIX."threads AS t LEFT JOIN ".X_PREFIX."forums AS f USING(fid) WHERE t.tid='$othertid'");

            if ($db->num_rows($queryadd1) == 0) {
                $db->free_result($queryadd1);
                error($lang['invalidtid'], false);
            }
            $otherthread = $db->fetch_array($queryadd1);
            $db->free_result($queryadd1);
            $replyadd = intval($otherthread['replies']) + 1;
            $otherfid = $otherthread['fid'];

            $db->query("UPDATE ".X_PREFIX."posts SET tid=$tid, fid='$fid' WHERE tid='$othertid'");

            $db->query("UPDATE ".X_PREFIX."threads SET closed='moved|$tid' WHERE closed='moved|$othertid'");

            $db->query("DELETE FROM ".X_PREFIX."threads WHERE tid='$othertid'");

            $sql->deleteVotesByTID([$othertid]);

            $db->query("UPDATE ".X_PREFIX."favorites AS f "
                     . "INNER JOIN ".X_PREFIX."members AS m ON m.username = f.username "
                     . "INNER JOIN ( "
                     . " SELECT username, COUNT(*) AS fcount "
                     . " FROM ".X_PREFIX."favorites AS f2 "
                     . " WHERE tid=$tid "
                     . " GROUP BY username "
                     . ") AS query2 ON m.username = query2.username "
                     . "SET f.tid=$tid "
                     . "WHERE f.tid='$othertid' AND query2.fcount=0");
            $db->query("DELETE FROM ".X_PREFIX."favorites WHERE tid='$othertid'");

            $query = $db->query("SELECT subject, author, icon FROM ".X_PREFIX."posts WHERE tid=$tid OR tid='$othertid' ORDER BY dateline, pid ASC LIMIT 1");
            $thread = $db->fetch_array($query);
            $db->free_result($query);
            $query = $db->query("SELECT author, dateline, pid FROM ".X_PREFIX."posts WHERE tid=$tid ORDER BY dateline DESC LIMIT 0, 1");
            $lastpost = $db->fetch_array($query);
            $db->free_result($query);
            $db->escape_fast($thread['author']);
            $db->escape_fast($thread['subject']);
            $db->escape_fast($lastpost['author']);
            $db->query("UPDATE ".X_PREFIX."threads SET replies=replies+'$replyadd', subject='{$thread['subject']}', icon='{$thread['icon']}', author='{$thread['author']}', lastpost='{$lastpost['dateline']}|{$lastpost['author']}|{$lastpost['pid']}' WHERE tid=$tid");

            audit($self['username'], $action, $fid, $tid);

            if ($forums['type'] == 'sub') {
                updateforumcount($fup['fid']);
            }
            if ($otherthread['type'] == 'sub') {
                $doupdate = TRUE;
                if (isset($fup['fid'])) {
                    $doupdate = ($otherthread['fup'] != $fup['fid']);
                }
                if ($doupdate) {
                    updateforumcount($otherthread['fup']);
                }
            }
            updateforumcount($fid);
            updateforumcount($otherfid);

            message($lang['mergethreadmsg'], false, '', '', $full_url.'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'threadprune':
        $tid = $tids[0];
        if (noSubmit('threadprunesubmit')) {
            $query = $db->query("SELECT replies FROM ".X_PREFIX."threads WHERE tid=$tid");
            if ($db->num_rows($query) == 0) {
                error($lang['textnothread'], FALSE);
            }
            $replies = (int) $db->result($query, 0);
            $db->free_result($query);

            if ($replies == 0) {
                error($lang['cantthreadprune'], false);
            }

            if (X_SADMIN || $SETTINGS['allowrankedit'] == 'off') {
                $disablePost = '';
                $posts = '';
                $query = $db->query("SELECT * FROM ".X_PREFIX."posts WHERE tid=$tid ORDER BY dateline");
                while($post = $db->fetch_array($query)) {
                    $bbcodeoff = $post['bbcodeoff'];
                    $smileyoff = $post['smileyoff'];
                    $post['message'] = stripslashes($post['message']);
                    $post['message'] = postify($post['message'], $smileyoff, $bbcodeoff, $fid, 'no', 'no', 'no');
                    eval('$posts .= "'.template('topicadmin_threadprune_row').'";');
                }
                $db->free_result($query);
            } else {
                $ranks = array('Super Administrator'=>5, 'Administrator'=>4, 'Super Moderator'=>3, 'Moderator'=>2, 'Member'=>1, ''=>0);
                $posts = '';
                $query = $db->query("SELECT p.*, m.status FROM ".X_PREFIX."posts p LEFT JOIN ".X_PREFIX."members m ON (m.username=p.author) WHERE tid=$tid ORDER BY dateline");
                while($post = $db->fetch_array($query)) {
                    if ($ranks[$post['status']] > $ranks[$self['status']]) {
                        $disablePost = 'disabled="disabled"';
                    } else {
                        $disablePost = '';
                    }
                    $bbcodeoff = $post['bbcodeoff'];
                    $smileyoff = $post['smileyoff'];
                    $post['message'] = stripslashes($post['message']);
                    $post['message'] = postify($post['message'], $smileyoff, $bbcodeoff, $fid, 'no', 'no', 'no');
                    eval('$posts .= "'.template('topicadmin_threadprune_row').'";');
                }
                $db->free_result($query);
            }
            $template = template_secure('topicadmin_threadprune', 'Thread Admin Options/Prune', (string) min($tids), $vars::NONCE_AYS_EXP);
            eval('echo "'.$template.'";');
        } else {
            request_secure('Thread Admin Options/Prune', (string) min($tids));
            $postcount = (int) $db->result($db->query("SELECT COUNT(*) FROM ".X_PREFIX."posts WHERE tid=$tid"), 0);
            $delcount = 0;
            foreach($_POST as $key=>$val) {
                if (substr($key, 0, 4) == 'move') {
                    $delcount++;
                }
            }
            if ($delcount >= $postcount) {
                error($lang['cantthreadprune'], false);
            }
            if (X_SADMIN || $SETTINGS['allowrankedit'] == 'off') {
                $query = $db->query("SELECT author, pid, message FROM ".X_PREFIX."posts WHERE tid=$tid");
                while($post = $db->fetch_array($query))    {
                    $move = "move".$post['pid'];
                    $move = getInt($move, 'p');
                    if (!empty($move)) {
                        $db->escape_fast($post['author']);
                        $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-1 WHERE username='{$post['author']}'");
                        $db->query("DELETE FROM ".X_PREFIX."posts WHERE pid=$move");
                        $attach->deleteByPost($move);
                        $db->query("UPDATE ".X_PREFIX."threads SET replies=replies-1 WHERE tid=$tid");
                    }
                }
                $db->free_result($query);
            } else {
                $ranks = array('Super Administrator'=>5, 'Administrator'=>4, 'Super Moderator'=>3, 'Moderator'=>2, 'Member'=>1, ''=>0);
                $query = $db->query("SELECT m.status, p.author, p.pid FROM ".X_PREFIX."posts p LEFT JOIN ".X_PREFIX."members m ON (m.username=p.author) WHERE p.tid=$tid");
                while($post = $db->fetch_array($query))    {
                    if ($ranks[$post['status']] > $ranks[$self['status']]) {
                        continue;
                    }
                    $move = "move".$post['pid'];
                    $move = getInt($move, 'p');
                    if (!empty($move)) {
                        $db->escape_fast($post['author']);
                        $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-1 WHERE username='{$post['author']}'");
                        $db->query("DELETE FROM ".X_PREFIX."posts WHERE pid=$move");
                        $attach->deleteByPost($move);
                        $db->query("UPDATE ".X_PREFIX."threads SET replies=replies-1 WHERE tid=$tid");
                    }
                }
                $db->free_result($query);
            }

            $firstauthor = $db->result($db->query("SELECT author FROM ".X_PREFIX."posts WHERE tid=$tid ORDER BY dateline ASC LIMIT 0,1"), 0);
            $db->escape_fast($firstauthor);

            $query = $db->query("SELECT pid, author, dateline FROM ".X_PREFIX."posts WHERE tid=$tid ORDER BY dateline DESC LIMIT 0,1");
            $lastpost = $db->fetch_array($query);
            $db->free_result($query);
            $db->escape_fast($lastpost['author']);
            $db->query("UPDATE ".X_PREFIX."threads SET author='$firstauthor', lastpost='{$lastpost['dateline']}|{$lastpost['author']}|{$lastpost['pid']}' WHERE tid=$tid");

            if ($forums['type'] == 'sub') {
                updateforumcount($fup['fid']);
            }
            updateforumcount($fid);

            audit($self['username'], $action, $fid, $tid);

            message($lang['complete_threadprune'], false, '', '', $full_url.'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'copy':
        if (noSubmit('copysubmit')) {
            $tid = implode(',', $tids);
            $forumselect = forumList('newfid', false, false);
            $template = template_secure('topicadmin_copy', 'Thread Admin Options/Copy', (string) min($tids), $vars::NONCE_AYS_EXP);
            eval('echo "'.$template.'";');
        } else {
            request_secure('Thread Admin Options/Copy', (string) min($tids));
            if (!formInt('newfid')) {
                error($lang['privforummsg'], false);
            }

            $newfid = getRequestInt('newfid');

            $otherforum = $forumCache->getForum($newfid);
            if ($otherforum === FALSE) {
                error($lang['textnoforum'], FALSE);
            }

            if (!statuscheck($newfid)) {
                error($lang['notpermitted'], false);
            }

            foreach($tids AS $tid) {
                $thread = $db->fetch_array($db->query("SELECT * FROM ".X_PREFIX."threads WHERE tid=$tid"));

                $thread['fid'] = $newfid;
                unset($thread['tid']);

                $cols = array();
                $vals = array();

                foreach($thread as $key=>$val) {
                    $cols[] = $key;
                    $vals[] = $db->escape($val);
                }
                $columns = implode(', ', $cols);
                $values  = "'".implode("', '", $vals)."'";

                $db->query("INSERT INTO ".X_PREFIX."threads ($columns) VALUES ($values)");

                $newtid = $db->insert_id();

                $query = $db->query("SELECT * FROM ".X_PREFIX."posts WHERE tid=$tid ORDER BY dateline, pid ASC");
                while($post = $db->fetch_array($query)) {
                    $oldPid = $post['pid'];
                    $post['fid'] = $newfid;
                    $post['tid'] = $newtid;
                    unset($post['pid']);

                    $cols = array();
                    $vals = array();

                    foreach($post as $key=>$val) {
                        $cols[] = $key;
                        $vals[] = $db->escape($val);
                    }
                    $columns = implode(', ', $cols);
                    $values  = "'".implode("', '", $vals)."'";

                    $db->query("INSERT INTO ".X_PREFIX."posts ($columns) VALUES ($values)");
                    $newpid = $db->insert_id();

                    $attach->copyByPost((int) $oldPid, $newpid);
                }

                $query = $db->query("SELECT author, COUNT(*) AS pidcount FROM ".X_PREFIX."posts WHERE tid=$tid GROUP BY author");
                while($result = $db->fetch_array($query)) {
                    $db->escape_fast($result['author']);
                    $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum+{$result['pidcount']} WHERE username='{$result['author']}'");
                }
                $db->free_result($query);

                audit($self['username'], $action, $fid, $tid);

                if ($otherforum['type'] == 'sub') {
                    updateforumcount($otherforum['fup']);
                }
                updateforumcount($newfid);
            }

            message($lang['copythreadmsg'], false, '', '', $full_url.'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;
}

end_time();
eval('echo "'.template('footer').'";');

function statuscheck($fid) {
    global $self;

    $forum = forums()->getForum($fid);
    if ($forum === FALSE) {
        return FALSE;
    }

    return (modcheck($self['username'], $forum['moderator']) == 'Moderator');
}
