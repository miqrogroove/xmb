<?php
/**
 * eXtreme Message Board
 * XMB 1.9.8 Engage Final SP3
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

require 'header.php';
require ROOT.'include/topicadmin.inc.php';

$_tid = isset($_POST['tid']) ? $_POST['tid'] : (isset($_GET['tid']) ? $_GET['tid'] : 0);
$fid = isset($_POST['fid']) && is_numeric($_POST['fid']) ? (int) $_POST['fid'] : (isset($_GET['fid']) && is_numeric($_GET['fid']) ? (int) $_GET['fid'] : 0);
$pid = getInt('pid');
$othertid = formInt('othertid');
$action = isset($_POST['action']) && !empty($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) && !empty($_GET['action']) ? $_GET['action'] : '');

if (is_array($_tid)) {
    $tids = array_unique(array_map('intval', $_tid));
    $tid = array();
    foreach($tids as $value) {
        $tid[] = $value;
    }
} else if (strstr($_tid, ',')) {
    $tids = array_unique(array_map('intval', explode(',', $_tid)));
    $tid = array();
    foreach($tids as $value) {
        $tid[] = $value;
    }
    $tid = implode(',', $tid);
} else {
    $tid = (int) $_tid;
}
$kill = false;

loadtemplates(
'topicadmin_delete',
'topicadmin_openclose',
'topicadmin_move',
'topicadmin_topuntop',
'topicadmin_bump',
'topicadmin_split_row',
'topicadmin_split',
'topicadmin_merge',
'topicadmin_report',
'topicadmin_empty',
'topicadmin_threadprune_row',
'topicadmin_threadprune',
'topicadmin_copy',
'misc_feature_notavailable'
);

eval('$css = "'.template('css').'";');

if ($tid && !is_array($tid) && false === strstr($tid, ',')) {
    $query = $db->query("SELECT * FROM ".X_PREFIX."threads WHERE tid='$tid'");
    $thread = $db->fetch_array($query);
    $threadname = html_entity_decode(stripslashes($thread['subject']));
    $fid = $thread['fid'];
} else {
    $threadSubject = '';
}

$query = $db->query("SELECT * FROM ".X_PREFIX."forums WHERE fid='$fid'");
$forums = $db->fetch_array($query);
$forums['name'] = stripslashes($forums['name']);

if ($fid == 0) {
    $kill = true;
} else if (isset($forums['type']) && $forums['type'] == 'forum') {
    nav('<a href="forumdisplay.php?fid='.$fid.'">'.html_entity_decode($forums['name'].'</a>'));
    if (isset($thread['subject'])) {
        nav('<a href="viewthread.php?tid='.$tid.'">'.$threadname.'</a>');
    }
} else if (isset($forums['type']) && $forums['type'] == 'sub') {
    $query = $db->query("SELECT name, fid FROM ".X_PREFIX."forums WHERE fid='$forums[fup]'");
    $fup = $db->fetch_array($query);
    $fup['name'] = stripslashes($fup['name']);
    nav('<a href="forumdisplay.php?fid='.intval($fup['fid']).'">'.html_entity_decode($fup['name']).'</a>');
    nav('<a href="forumdisplay.php?fid='.$fid.'">'.html_entity_decode($forums['name']).'</a>');
    if (isset($threadname)) {
        nav('<a href="viewthread.php?tid='.$tid.'">'.$threadname.'</a>');
    }
} else {
    $kill = true;
}

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
        nav($lang['textgetip']);
        break;
    case 'bump':
        nav($lang['textbumpthread']);
        break;
    case 'report':
        nav($lang['textreportpost']);
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
    case 'votepoll':
        nav($lang['textvote']);
        break;
    default:
        $kill = true;
        break;
}

if ($kill) {
    error($lang['notpermitted']);
}

eval('echo "'.template('header').'";');

$mod = new mod();

switch($action) {
    case 'delete':
        $mod->statuscheck($fid);
        if (noSubmit('deletesubmit')) {
            $tid = $mod->create_tid_string($tid);
            eval('echo stripslashes("'.template('topicadmin_delete').'");');
        } else {
            $tids = $mod->create_tid_array($tid);
            foreach($tids AS $tid) {
                $query = $db->query("SELECT author FROM ".X_PREFIX."posts WHERE tid='$tid'");
                while($result = $db->fetch_array($query)) {
                    $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-1 WHERE username='$result[author]'");
                }
                $db->free_result($query);

                $db->query("DELETE FROM ".X_PREFIX."threads WHERE tid='$tid'");
                $db->query("DELETE FROM ".X_PREFIX."posts WHERE tid='$tid'");
                $db->query("DELETE FROM ".X_PREFIX."attachments WHERE tid='$tid'");
                $db->query("DELETE FROM ".X_PREFIX."favorites WHERE tid='$tid'");

                $db->query("DELETE FROM ".X_PREFIX."threads WHERE closed='moved|$tid'");

                if (isset($forums['type']) && $forums['type'] == 'sub') {
                    updateforumcount($fup['fup']);
                }
                updateforumcount($fid);

                $mod->log($xmbuser, $action, $fid, $tid);
            }
            message($lang['deletethreadmsg'], false, '', '', 'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'close':
        $mod->statuscheck($fid);
        $query = $db->query("SELECT closed FROM ".X_PREFIX."threads WHERE fid=$fid AND tid='$tid'");
        $closed = $db->result($query, 0);
        $db->free_result($query);

        if (noSubmit('closesubmit')) {
            if ($closed == 'yes') {
                $lang['textclosethread'] = $lang['textopenthread'];
            } else if ($closed == '') {
                $lang['textclosethread'] = $lang['textclosethread'];
            }
            eval('echo stripslashes("'.template('topicadmin_openclose').'");');
        } else {
            if ($closed == 'yes') {
                $db->query("UPDATE ".X_PREFIX."threads SET closed='' WHERE tid='$tid' AND fid='$fid'");
            } else {
                $db->query("UPDATE ".X_PREFIX."threads SET closed='yes' WHERE tid='$tid' AND fid='$fid'");
            }

            $act = ($closed != '') ? 'open' : 'close';
            $mod->log($xmbuser, $act, $fid, $tid);

            message($lang['closethreadmsg'], false, '', '', 'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'f_close':
        $mod->statuscheck($fid);
        if (noSubmit('closesubmit')) {
            $tid = $mod->create_tid_string($tid);
            eval('echo stripslashes("'.template('topicadmin_openclose').'");');
        } else {
            $tids = $mod->create_tid_array($tid);
            foreach($tids AS $tid) {
                $db->query("UPDATE ".X_PREFIX."threads SET closed='yes' WHERE tid='$tid' AND fid='$fid'");
                $mod->log($xmbuser, 'close', $fid, $tid);
            }

            message($lang['closethreadmsg'], false, '', '', 'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'f_open':
        $mod->statuscheck($fid);
        if (noSubmit('closesubmit')) {
            $tid = $mod->create_tid_string($tid);
            $lang['textclosethread'] = $lang['textopenthread'];
            eval('echo stripslashes("'.template('topicadmin_openclose').'");');
        } else {
            $tids = $mod->create_tid_array($tid);
            foreach($tids AS $tid) {
                $db->query("UPDATE ".X_PREFIX."threads SET closed='' WHERE tid='$tid' AND fid='$fid'");
                $mod->log($xmbuser, 'open', $fid, $tid);
            }

            message($lang['closethreadmsg'], false, '', '', 'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'move':
        $mod->statuscheck($fid);
        if (noSubmit('movesubmit')) {
            $tid = $mod->create_tid_string($tid);
            $forumselect = forumList('moveto', false, false, $fid);
            eval('echo stripslashes("'.template('topicadmin_move').'");');
        } else {
            $moveto = formInt('moveto');
            if ($moveto) {
                $query = $db->query("SELECT type FROM ".X_PREFIX."forums WHERE fid=$moveto");
                $forumtype = $db->result($query, 0);
                $db->free_result($query);

                if($forumtype == 'group') {
                    echo '<center><span class="mediumtxt">'.$lang['errormovingthreads'].'</span></center>';
                    end_time();
                    eval('echo "'.template('footer').'";');
                    exit();
                }

                $tids = $mod->create_tid_array($tid);
                foreach($tids AS $tid) {
                    if ($type == "normal") {
                        $db->query("UPDATE ".X_PREFIX."threads SET fid=$moveto WHERE tid='$tid'");
                        $db->query("UPDATE ".X_PREFIX."posts SET fid=$moveto WHERE tid='$tid'");
                    } else {
                        $query = $db->query("SELECT * FROM ".X_PREFIX."threads WHERE tid='$tid'");
                        $info = $db->fetch_array($query);

                        $db->query("INSERT INTO ".X_PREFIX."threads (fid, subject, icon, lastpost, views, replies, author, closed, topped) VALUES ('$info[fid]', '$info[subject]', '', '$info[lastpost]', 0, 0, '$info[author]', 'moved|$info[tid]', '$info[topped]')");
                        $ntid = $db->insert_id();

                        $db->query("INSERT INTO ".X_PREFIX."posts (fid, tid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff) VALUES ('$info[fid]', '$ntid', '$info[author]', '$info[tid]', '$info[subject]', 0, '', '', '', '', '')");
                        $db->query("UPDATE ".X_PREFIX."threads SET fid=$moveto WHERE tid='$tid' AND fid='$fid'");
                        $db->query("UPDATE ".X_PREFIX."posts SET fid=$moveto WHERE tid='$tid' AND fid='$fid'");
                    }
                    updatethreadcount($tid);
                    $f = "$fid -> $moveto";
                    $mod->log($xmbuser, $action, $moveto, $tid);
                }
            } else {
                echo '<center><span class="mediumtxt">'.$lang['errormovingthreads'].'</span></center>';
                end_time();
                eval('echo "'.template('footer').'";');
                exit();
            }

            if (isset($forums['type']) && $forums['type'] == "sub") {
                updateforumcount($fup['fup']);
            }
            updateforumcount($fid);
            updateforumcount($moveto);

            message($lang['movethreadmsg'], false, '', '', 'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'top':
        $mod->statuscheck($fid);
        if (noSubmit('topsubmit')) {
            if (!is_array($tid)) {
                $query = $db->query("SELECT topped FROM ".X_PREFIX."threads WHERE fid=$fid AND tid='$tid'");
                if ($db->num_rows($query) == 0) {
                    error($lang['textnothread'], FALSE);
                }
                $topped = $db->result($query, 0);
                if ($topped == 1) {
                    $lang['texttopthread'] = $lang['textuntopthread'];
                }
            } else {
                $lang['texttopthread'] = $lang['texttopthread'].' / '.$lang['textuntopthread'];
            }
            $tid = $mod->create_tid_string($tid);
            eval('echo stripslashes("'.template('topicadmin_topuntop').'");');
        } else {
            $tids = $mod->create_tid_array($tid);
            foreach($tids AS $tid) {
                $query = $db->query("SELECT topped FROM ".X_PREFIX."threads WHERE fid=$fid AND tid='$tid'");
                if ($db->num_rows($query) == 0) {
                    error($lang['textnothread'], FALSE);
                }
                $topped = $db->result($query, 0);

                if ($topped == 1) {
                    $db->query("UPDATE ".X_PREFIX."threads SET topped='0' WHERE tid='$tid' AND fid='$fid'");
                } else if ($topped == 0)    {
                    $db->query("UPDATE ".X_PREFIX."threads SET topped='1' WHERE tid='$tid' AND fid='$fid'");
                }

                $act = ($topped ? 'untop' : 'top');
                $mod->log($xmbuser, $act, $fid, $tid);
            }

            message($lang['topthreadmsg'], false, '', '', 'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'getip':
        $mod->statuscheck($fid);
        if ($pid) {
            $query = $db->query("SELECT * FROM ".X_PREFIX."posts WHERE pid='$pid'");
        } else {
            $query = $db->query("SELECT * FROM ".X_PREFIX."threads WHERE tid='$tid'");
        }
        $ipinfo = $db->fetch_array($query);
        ?>
        <form method="post" action="cp.php?action=ipban">
        <table cellspacing="0" cellpadding="0" border="0" width="60%" align="center">
        <tr><td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="header" colspan="3"><?php echo $lang['textgetip']?></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>">
        <td class="tablerow"><?php echo $lang['textyesip']?> <strong><?php echo $ipinfo['useip']?></strong> - <?php echo gethostbyaddr($ipinfo['useip'])?>
        <?php
        if (X_ADMIN) {
            $ip = explode('.', $ipinfo['useip']);
            $query = $db->query("SELECT * FROM ".X_PREFIX."banned WHERE (ip1='$ip[0]' OR ip1='-1') AND (ip2='$ip[1]' OR ip2='-1') AND (ip3='$ip[2]' OR ip3='-1') AND (ip4='$ip[3]' OR ip4='-1')");
            $result = $db->fetch_array($query);

            if ($result) {
                $buttontext = $lang['textunbanip'];
                for ($i=1; $i<=4; ++$i) {
                    $j = "ip$i";
                    if ($result[$j] == -1) {
                        $result[$j] = "*";
                        $foundmask = 1;
                    }
                }

                if ($foundmask) {
                    $ipmask = "<strong>$result[ip1].$result[ip2].$result[ip3].$result[ip4]</strong>";
                    eval($lang['evalipmask']);
                    $lang['bannedipmask'] = stripslashes($lang['bannedipmask']);
                    echo $lang['bannedipmask'];
                } else {
                    $lang['textbannedip'] = stripslashes($lang['textbannedip']);
                    echo $lang['textbannedip'];
                }
                echo "<input type=\"hidden\" name=\"delete$result[id]\" value=\"$result[id]\" />";
            } else {
                $buttontext = $lang['textbanip'];
                for ($i=1; $i<=4; ++$i) {
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
        $mod->statuscheck($fid);
        if (noSubmit('bumpsubmit')) {
            $tid = $mod->create_tid_string($tid);
            eval('echo stripslashes("'.template('topicadmin_bump').'");');
        } else {
            $tids = $mod->create_tid_array($tid);
            foreach($tids AS $tid) {
                $pid = $db->result($db->query("SELECT pid FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY pid DESC LIMIT 1"), 0);
                $db->query("UPDATE ".X_PREFIX."threads SET lastpost='".$onlinetime."|$xmbuser|$pid' WHERE tid='$tid' AND fid='$fid'");
                $db->query("UPDATE ".X_PREFIX."forums SET lastpost='".$onlinetime."|$xmbuser|$pid' WHERE fid='$fid'");

                $mod->log($xmbuser, $action, $fid, $tid);
            }

            message($lang['bumpthreadmsg'], false, '', '', 'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'empty':
        $mod->statuscheck($fid);
        if (noSubmit('emptysubmit')) {
            $tid = $mod->create_tid_string($tid);
            eval('echo stripslashes("'.template('topicadmin_empty').'");');
        } else {
            $tids = $mod->create_tid_array($tid);
            foreach($tids AS $tid) {
                $pid = $db->result($db->query("SELECT pid FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY pid ASC LIMIT 1"), 0);
                $query = $db->query("SELECT author FROM ".X_PREFIX."posts WHERE tid='$tid' AND pid!='$pid'");
                while($result = $db->fetch_array($query)) {
                    $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-1 WHERE username='$result[author]'");
                }
                $db->free_result($query);

                $db->query("DELETE FROM ".X_PREFIX."posts WHERE tid='$tid' AND pid!='$pid'");
                $db->query("DELETE FROM ".X_PREFIX."attachments WHERE pid='$pid'");

                updatethreadcount($tid);
                $mod->log($xmbuser, $action, $fid, $tid);
            }
            if (isset($forums['type']) && $forums['type'] == 'sub') {
                updateforumcount($fup['fup']);
            }
            updateforumcount($fid);

            message($lang['emptythreadmsg'], false, '', '', 'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'split':
        $mod->statuscheck($fid);
        if (noSubmit('splitsubmit')) {
            $query = $db->query("SELECT replies FROM ".X_PREFIX."threads WHERE tid='$tid'");
            $replies = $db->result($query, 0);

            if ($replies == 0) {
                error($lang['cantsplit'], false);
            }

            $query = $db->query("SELECT * FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY dateline");
            $posts = '';
            while($post = $db->fetch_array($query))    {
                $bbcodeoff = $post['bbcodeoff'];
                $smileyoff = $post['smileyoff'];
                $post['message'] = stripslashes($post['message']);
                $post['message'] = postify($post['message'], $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", X_PREFIX."words", X_PREFIX."forums", X_PREFIX."smilies");
                eval('$posts .= "'.template('topicadmin_split_row').'";');
            }
            eval('echo stripslashes("'.template('topicadmin_split').'");');
        } else {
            $subject = formVar('subject');
            if ($subject == '') {
                error($lang['textnosubject'], false);
            }
            $subject = addslashes($subject);

            $chkInputHTML = 'no';
            $chkInputTags = 'no';
            if (isset($forums['allowhtml']) && $forums['allowhtml'] == 'yes') {
                $chkInputHTML = 'yes';
                $chkInputTags = 'no';
            }
            $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);

            $threadcreated = false;
            $firstmove = false;
            $query = $db->query("SELECT subject, pid FROM ".X_PREFIX."posts WHERE tid='$tid'");
            while($post = $db->fetch_array($query)) {
                $move = "move".$post['pid'];
                $move = isset($_POST[$move]) ? $_POST[$move] : '';
                $thatime = $onlinetime;
                if (!$threadcreated) {
                    $db->query("INSERT INTO ".X_PREFIX."threads (fid, subject, icon, lastpost, views, replies, author, closed, topped) VALUES ($fid, '$subject', '', '$thatime|$xmbuser', 0, 0, '$xmbuser', '', 0)");
                    $newtid = $db->insert_id();
                    $threadcreated = true;
                }

                if (!empty($move)) {
                    $newsub = '';
                    if (!$firstmove) {
                        $newsub = ", subject='$subject'";
                        $firstmove = true;
                    }
                    $db->query("UPDATE ".X_PREFIX."posts SET tid=$newtid $newsub WHERE pid='$move'");
                    $db->query("UPDATE ".X_PREFIX."attachments SET tid=$newtid WHERE pid='$move'");
                    $db->query("UPDATE ".X_PREFIX."threads SET replies=replies+1 WHERE tid='$newtid'");
                    $db->query("UPDATE ".X_PREFIX."threads SET replies=replies-1 WHERE tid='$tid'");
                }
            }

            $query = $db->query("SELECT author FROM ".X_PREFIX."posts WHERE tid='$newtid' ORDER BY dateline ASC LIMIT 0,1");
            $firstauthor = $db->result($query, 0);
            $query = $db->query("SELECT author, dateline, pid FROM ".X_PREFIX."posts WHERE tid=$newtid ORDER BY dateline DESC LIMIT 0,1");
            $lastpost = $db->fetch_array($query);
            $db->query("UPDATE ".X_PREFIX."threads SET author='$firstauthor', lastpost='$lastpost[dateline]|$lastpost[author]|$lastpost[pid]', replies=replies-1 WHERE tid=$newtid");

            $query = $db->query("SELECT author FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY dateline ASC LIMIT 0,1");
            $firstauthor = $db->result($query, 0);
            $query = $db->query("SELECT author, dateline, pid FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY dateline DESC LIMIT 0,1");
            $lastpost = $db->fetch_array($query);
            $db->query("UPDATE ".X_PREFIX."threads SET author='$firstauthor', lastpost='$lastpost[dateline]|$lastpost[author]|$lastpost[pid]' WHERE tid='$tid'");

            $mod->log($xmbuser, $action, $fid, $tid);

            message($lang['splitthreadmsg'], false, '', '', 'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'merge':
        $mod->statuscheck($fid);
        if (noSubmit('mergesubmit')) {
            eval('echo stripslashes("'.template('topicadmin_merge').'");');
        } else {
            if ($othertid == 0) {
                error($lang['invalidtid'], false);
            } elseif ($tid == $othertid) {
                error($lang['cannotmergesamethread'], false);
            }

            $queryadd1 = $db->query("SELECT replies, fid FROM ".X_PREFIX."threads WHERE tid='$othertid'");

            if ($db->num_rows($queryadd1) == 0) {
                error($lang['tidnoexist'], false);
            }

            $queryadd2 = $db->query("SELECT replies FROM ".X_PREFIX."threads WHERE tid='$tid'");
            $replyadd = $db->result($queryadd1, 0, 'replies');
            $otherfid = $db->result($queryadd1, 0, 'fid');
            $replyadd2 = $db->result($queryadd2, 0);
            $replyadd++;
            $replyadd = $replyadd + $replyadd2;

            $db->query("UPDATE ".X_PREFIX."posts SET tid='$tid', fid='$fid' WHERE tid='$othertid'");
            $db->query("UPDATE ".X_PREFIX."attachments SET tid='$tid' WHERE tid='$othertid'");

            $db->query("DELETE FROM ".X_PREFIX."threads WHERE tid='$othertid'");
            $db->query("UPDATE ".X_PREFIX."forums SET threads = threads-1 WHERE fid='$otherfid'");

            $query = $db->query("SELECT * FROM ".X_PREFIX."favorites WHERE tid='$othertid' OR tid='$tid'");
            if ($db->num_rows($query) == 2) {
                $db->query("DELETE FROM ".X_PREFIX."favorites WHERE tid='$othertid'");
            } else {
                $db->query("UPDATE ".X_PREFIX."favorites SET tid='$tid' WHERE tid='$othertid'");
            }

            $query = $db->query("SELECT subject, author, icon FROM ".X_PREFIX."posts WHERE tid='$tid' OR tid='$othertid' ORDER BY pid ASC LIMIT 1");
            $thread = $db->fetch_array($query);
            $query = $db->query("SELECT author, dateline, pid FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY dateline DESC LIMIT 0, 1");
            $lastpost = $db->fetch_array($query);
            $db->query("UPDATE ".X_PREFIX."threads SET replies='$replyadd', subject='$thread[subject]', icon='$thread[icon]', author='$thread[author]', lastpost='$lastpost[dateline]|$lastpost[author]|$lastpost[pid]' WHERE tid='$tid'");

            $mod->log($xmbuser, $action, $fid, "$othertid, $tid");

            message($lang['mergethreadmsg'], false, '', '', 'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'threadprune':
        $mod->statuscheck($fid);
        if (noSubmit('threadprunesubmit')) {
            $query = $db->query("SELECT replies FROM ".X_PREFIX."threads WHERE tid='$tid'");
            $replies = $db->result($query, 0);
            $db->free_result($query);

            if ($replies == 0) {
                error($lang['cantthreadprune'], false);
            }

            if (X_SADMIN || $SETTINGS['allowrankedit'] == 'off') {
                $disablePost = '';
                $posts = '';
                $query = $db->query("SELECT * FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY dateline");
                while($post = $db->fetch_array($query)) {
                    $bbcodeoff = $post['bbcodeoff'];
                    $smileyoff = $post['smileyoff'];
                    $post['message'] = stripslashes($post['message']);
                    $post['message'] = postify($post['message'], $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", X_PREFIX."words", X_PREFIX."forums", X_PREFIX."smilies");
                    eval('$posts .= "'.template('topicadmin_threadprune_row').'";');
                }
                $db->free_result($query);
            } else {
                $ranks = array('Super Administrator'=>5, 'Administrator'=>4, 'Super Moderator'=>3, 'Moderator'=>2, 'Member'=>1, ''=>0);
                $posts = '';
                $query = $db->query("SELECT p.*, m.status FROM ".X_PREFIX."posts p LEFT JOIN ".X_PREFIX."members m ON (m.username=p.author) WHERE tid='$tid' ORDER BY dateline");
                while($post = $db->fetch_array($query)) {
                    if ($ranks[$post['status']] > $ranks[$self['status']]) {
                        $disablePost = 'disabled="disabled"';
                    } else {
                        $disablePost = '';
                    }
                    $bbcodeoff = $post['bbcodeoff'];
                    $smileyoff = $post['smileyoff'];
                    $post['message'] = stripslashes($post['message']);
                    $post['message'] = postify($post['message'], $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", X_PREFIX."words", X_PREFIX."forums", X_PREFIX."smilies");
                    eval('$posts .= "'.template('topicadmin_threadprune_row').'";');
                }
                $db->free_result($query);
            }
            eval('echo stripslashes("'.template('topicadmin_threadprune').'");');
        } else {
            if (X_SADMIN || $SETTINGS['allowrankedit'] == 'off') {
                $query = $db->query("SELECT author, pid, message FROM ".X_PREFIX."posts WHERE tid='$tid'");
                while($post = $db->fetch_array($query))    {
                    $move = "move".$post['pid'];
                    $move = isset($_POST[$move]) ? $_POST[$move] : '';
                    if (!empty($move)) {
                        $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-1 WHERE username='{$post['author']}'");
                        $db->query("DELETE FROM ".X_PREFIX."posts WHERE pid='$move'");
                        $db->query("DELETE FROM ".X_PREFIX."attachments WHERE pid='$move'");
                        $db->query("UPDATE ".X_PREFIX."threads SET replies=replies-1 WHERE tid='$tid'");
                    }
                }
                $db->free_result($query);
            } else {
                $ranks = array('Super Administrator'=>5, 'Administrator'=>4, 'Super Moderator'=>3, 'Moderator'=>2, 'Member'=>1, ''=>0);
                $query = $db->query("SELECT m.status, p.author, p.pid FROM ".X_PREFIX."posts p LEFT JOIN ".X_PREFIX."members m ON (m.username=p.author) WHERE p.tid='$tid'");
                while($post = $db->fetch_array($query))    {
                    if ($ranks[$post['status']] > $ranks[$self['status']]) {
                        continue;
                    }
                    $move = "move".$post['pid'];
                    $move = isset($_POST[$move]) ? $_POST[$move] : '';
                    if (!empty($move)) {
                        $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-1 WHERE username='{$post['author']}'");
                        $db->query("DELETE FROM ".X_PREFIX."posts WHERE pid='$move'");
                        $db->query("DELETE FROM ".X_PREFIX."attachments WHERE pid='$move'");
                        $db->query("UPDATE ".X_PREFIX."threads SET replies=replies-1 WHERE tid='$tid'");
                    }
                }
                $db->free_result($query);
            }

            $query = $db->query("SELECT author FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY dateline ASC LIMIT 0,1");
            $firstauthor = $db->result($query, 0);
            $db->free_result($query);

            $query = $db->query("SELECT pid, author, dateline FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY dateline DESC LIMIT 0,1");
            $lastpost = $db->fetch_array($query);
            $db->free_result($query);

            $db->query("UPDATE ".X_PREFIX."threads SET author='$firstauthor', lastpost='$lastpost[dateline]|$lastpost[author]|$lastpost[pid]' WHERE tid='$tid'");

            if (isset($forums['type']) && $forums['type'] == 'sub') {
                $query= $db->query("SELECT fup FROM ".X_PREFIX."forums WHERE fid=$fid LIMIT 1");
                $fup = $db->fetch_array($query);
                $db->free_result($query);
                updateforumcount($fid);
                updateforumcount($fup['fup']);
            } else {
                updateforumcount($fid);
            }

            $mod->log($xmbuser, $action, $fid, "$othertid, $tid");

            message($lang['complete_threadprune'], false, '', '', 'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'copy':
        $mod->statuscheck($fid);
        if (noSubmit('copysubmit')) {
            $tid = $mod->create_tid_string($tid);
            $forumselect = forumList('newfid', false, false);
            eval('echo stripslashes("'.template('topicadmin_copy').'");');
        } else {
            if (!formInt('newfid')) {
                error($lang['privforummsg'], false);
            }

            $mod->statuscheck($newfid);
            $tids = $mod->create_tid_array($tid);
            foreach($tids AS $tid) {
                $thread = $db->fetch_array($db->query("SELECT * FROM ".X_PREFIX."threads WHERE tid='$tid'"));
                foreach($thread as $key=>$val) {
                    switch($key) {
                        case 'tid':
                            unset($thread[$key]);
                            break;
                        case 'fid':
                            $thread['fid'] = $newfid;
                            break;
                        default:
                            break;
                    }
                }

                $cols = array();
                $vals = array();

                reset($thread);
                foreach($thread as $key=>$val) {
                    if (trim($key) == '') {
                        continue;
                    }

                    if ($key == 'subject') {
                        //$val = '[COPY] '.$val;
                        $val = $val;
                    }
                    $cols[] = $key;
                    $vals[] = addslashes($val);
                }
                reset($thread);
                $columns = implode(', ', $cols);
                $values  = "'".implode("', '", $vals)."'";

                $db->query("INSERT INTO ".X_PREFIX."threads ($columns) VALUES ($values)") or die($db->error());

                $newtid = $db->insert_id();
                $cols = array();
                $vals = array();

                $query = $db->query("SELECT * FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY pid ASC");
                while($post = $db->fetch_array($query)) {
                    $post['fid'] = $newfid;
                    $post['tid'] = $newtid;

                    $oldPid = $post['pid'];

                    unset($post['pid']);
                    reset($post);

                    foreach($post as $key=>$val) {
                        $cols[] = $key;
                        $vals[] = $val;
                    }
                    $columns = implode(', ', $cols);
                    $values  = "'".implode("', '", $vals)."'";

                    $cols = array();
                    $vals = array();

                    $db->query("INSERT INTO ".X_PREFIX."posts ($columns) VALUES ($values)") or die($db->error());
                    $newpid = $db->insert_id();

                    $db->query("INSERT INTO ".X_PREFIX."attachments (`tid`,`pid`,`filename`,`filetype`,`filesize`,`attachment`,`downloads`) SELECT '$newtid','$newpid',`filename`,`filetype`,`filesize`,`attachment`,`downloads` FROM ".X_PREFIX."attachments WHERE pid='$oldPid'");
                }

                $mod->log($xmbuser, $action, $fid, $tid);
            }

            message($lang['copythreadmsg'], false, '', '', 'forumdisplay.php?fid='.$fid, true, false, true);
        }
        break;

    case 'report':
        if ($SETTINGS['reportpost'] == 'off') {
            eval('echo "'.template('misc_feature_notavailable').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit;
        }

        if (noSubmit('reportsubmit')) {
            eval('echo stripslashes("'.template('topicadmin_report').'");');
        } else {
            $postcount = $db->result($db->query("SELECT count(pid) FROM ".X_PREFIX."posts WHERE tid='$tid'"), 0);
            $query = $db->query("SELECT moderator FROM ".X_PREFIX."forums WHERE fid='$fid'");
            $query2 = $db->query("SELECT username FROM ".X_PREFIX."members WHERE status='Super Administrator' OR status='Administrator' OR status='Super Moderator'");
            $mods = explode(", ", $db->result($query, 0));
            while($usr = $db->fetch_array($query2)) {
                $mods[] = $usr['username'];
            }
            $sent = 0;
            $time = $onlinetime;
            foreach($mods as $key=>$mod) {
                $mod = trim($mod);
                $q = $db->query("SELECT ppp FROM ".X_PREFIX."members WHERE username='$mod'");
                if ($db->num_rows($q) == 0) {
                    continue;
                }
                $page = quickpage($postcount, $db->result($q, 0));

                $posturl = $SETTINGS['boardurl']."viewthread.php?tid=$tid&page=$page#pid$pid";
                $reason = formVar('reason');
                $message = $lang['reportmessage'].' '.$posturl."\n\n".$lang['reason'].' '.$reason;

                $db->query("INSERT INTO ".X_PREFIX."u2u (msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus) VALUES ('$mod', '$self[username]', 'incoming', '$mod', 'Inbox', '$lang[reportsubject]', '$message', ".$db->time($time).", 'no', 'yes')");
                $sent++;
            }

            $page = quickpage($postcount, $self['tpp']);
            message($lang['reportmsg'], false, '', '', 'viewthread.php?tid='.$tid.'&page='.$page.'#pid'.$pid, true, false, true);
        }
        break;

    case 'votepoll':
        // Are we logged in? Only members can vote in polls (otherwise vote stuffing is trivial)
        if (!X_MEMBER) {
            error($lang['notloggedin'], false);
        }

        // User voted in poll related to thread $tid. The vote option is contained in $postopnum
        $postopnum = formInt('postopnum');
        if ($postopnum === 0) {
            error($lang['pollvotenotselected'], false);
        }

        // Does a poll exist for this thread?
        $query = $db->query("SELECT vote_id FROM ".X_PREFIX."vote_desc WHERE topic_id='$tid'");
        if ($query === false) {
            error($lang['pollvotenotselected'], false);
        }

        $vote_id = $db->fetch_array($query);
        $vote_id = (int) $vote_id['vote_id'];
        $db->free_result($query);

        // does the poll option exist?
        $vote_result = $db->result($db->query("SELECT COUNT(vote_option_id) FROM ".X_PREFIX."vote_results WHERE vote_id='$vote_id' AND vote_option_id='$postopnum'"), 0);
        if ($vote_result != 1) {
            error($lang['pollvotenotselected'], false);
        }

        // Has the user voted on this poll before?
        $voted = $db->result($db->query("SELECT COUNT(vote_id) FROM ".X_PREFIX."vote_voters WHERE vote_id='$vote_id' AND vote_user_id='$self[uid]'"), 0);
        if ($voted === 1) {
            error($lang['alreadyvoted'], false);
        }

        // Okay, the user is about to vote
        $db->query("INSERT INTO ".X_PREFIX."vote_voters (vote_id, vote_user_id, vote_user_ip) VALUES ('$vote_id', '$self[uid]', '".encode_ip($onlineip)."')");
        $db->query("UPDATE ".X_PREFIX."vote_results SET vote_result=vote_result+1 WHERE vote_id='$vote_id' AND vote_option_id='$postopnum'");

        if ($tid > 0) {
            message($lang['votemsg'], false, '', '', 'viewthread.php?tid='.$tid, true, false, true);
        } else {
            message($lang['votemsg'], false, '', '', 'index.php', true, false, true);
        }
        break;
}

end_time();
eval('echo "'.template('footer').'";');
?>