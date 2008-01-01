<?php
/* $Id: topicadmin.php,v 1.3.2.21 2007/03/18 19:51:18 ajv Exp $    */
/*
    © 2001 - 2007 Aventure Media & The XMB Development Team
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


// Get global stuff
    require "./header.php";

$pid = (isset($pid) ? (int) $pid : 0);
$fid = (isset($fid) ? (int) $fid : 0);
$thread_select = (isset($_POST['thread_select']) && !empty($_POST['thread_select']) ? $_POST['thread_select'] : '');
$kill= false;

// Pre-load    templates
    loadtemplates('topicadmin_delete','topicadmin_openclose','topicadmin_move','topicadmin_topuntop','topicadmin_bump','topicadmin_split_row','topicadmin_split','topicadmin_merge','topicadmin_report','topicadmin_empty','topicadmin_threadprune_row','topicadmin_threadprune','topicadmin_copy','misc_feature_notavailable');
    eval("\$css = \"".template("css")."\";");

// Get all info about thread
    if (  false === strstr($tid, ',') && (int)$tid > 0 ) {
        $tid = (int) $tid;
        $query = $db->query("SELECT * FROM $table_threads WHERE tid='$tid'");
        $thread = $db->fetch_array($query);
        $threadname = stripslashes($thread['subject']);
        $fid = $thread['fid'];
    }

// Get all info about the forum the thread is in
    $query  = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
    $forums = $db->fetch_array($query);
    $forums['name'] = stripslashes($forums['name']);

    $perms = checkForumPermissions($forums);
    if(!($perms[X_PERMS_VIEW] && $perms[X_PERMS_USERLIST] && $perms[X_PERMS_PASSWORD])) {
        error($lang['notpermitted']);
    }

    $chkInputHTML = 'no';
    $chkInputTags = 'no';
    if ( isset($forums['allowhtml']) && $forums['allowhtml'] == 'yes' ) {
        $chkInputHTML = 'yes';
        $chkInputTags = 'no';
    }

// Create navigation
    if ( $forums['type'] == 'forum') {
        nav('<a href="forumdisplay.php?fid='.$fid.'">'.stripslashes($forums['name']).'</a>');
        if ( isset($thread['subject']) ) {
            nav('<a href="viewthread.php?tid='.$tid.'">'.checkOutput($threadname, 'no', '', true).'</a>');
        }
    } elseif ( $forums['type'] == 'sub') {
        $query = $db->query("SELECT name, fid FROM $table_forums WHERE fid='$forums[fup]'");
        $fup = $db->fetch_array($query);
        nav('<a href="forumdisplay.php?fid='.$fup['fid'].'">'.stripslashes($fup['name']).'</a>');
        nav('<a href="forumdisplay.php?fid='.$fid.'">'.stripslashes($forums['name']).'</a>');
        if ( isset($thread['subject']) ) {
            nav('<a href="viewthread.php?tid='.$tid.'">'.checkOutput($threadname, 'no', '', true).'</a>');
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

    if ( $kill) {
        error($lang['notpermitted']);
    }

// Create and show header
eval('echo "'.template('header').'";');

/**
* Topic moderator functions
*
* All are statics, so you can call them without instantiation
*/
class mod {
    /**
    * mod() - Check if user is a valid topic moderator
    *
    * Looks at X_STAFF and $action to determine if you're able to do use one
    * of the unauthenticated topic admin functions
    *
    * @return   Returns if okay, otherwise quits with an error message
    */
    function mod() {
        global $lang, $action;

        if(!X_STAFF && $action != 'votepoll' && $action != 'report' ) {
            error($lang['notpermitted'], false);
        }
    }

    /**
    * statuscheck() - Check if user is a valid topic moderator
    *
    * Looks at X_STAFF and $action to determine if you're able to do use one
    * of the unauthenticated topic admin functions
    *
    * @param    $fid    the forum id to check for mod privileges of the current user
    * @return   Returns if okay, otherwise quits with an error message
    */
    function statuscheck($fid) {
        global $self, $lang, $table_forums, $db;

        $query   = $db->query("SELECT postperm, userlist, password, moderator FROM $table_forums WHERE fid=$fid");
        $forum = $db->fetch_array($query);
        $perms = checkForumPermissions($forum);
        if($perms[X_PERMS_VIEW] && $perms[X_PERMS_USERLIST] && $perms[X_PERMS_PASSWORD]) {
            if($self['status'] == 'Moderator') {
                $parts = explode(',', $forum['moderator']);
                $user = strtolower(trim($self['username']));
                foreach($parts as $mod) {
                    if(strtolower(trim($mod)) == $user) {
                        return true;
                    }
                }
                return false;

            } elseif(X_STAFF) {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
    * create_tid_string() - Checks if an array of TIDs or a single TID is submitted, returns a string seperated by comma's
    *
    * @param    $tid           The ThreadID
    * @param    $thread_select The array of ThreadID's
    * @return   Returns the string with ThreadID's
    */
    function create_tid_string($tid, $thread_select) {
        $tids = '';
        $tid = (int) $tid;
        if ( $tid > 0 ) {
            $tids = $tid;
        } else {
            foreach ( $thread_select as $value ) {
                $value = (int) $value;
                if ( $value > 0 ) {
                    $tids .= ( empty( $tids ) ) ? "$value" : ",$value";
                }
            }
        }
        return $tids;
    }

    /**
    * create_tid_array() - Checks the submitted $tid, and returns the values in an array
    *
    * @param    $tid           The ThreadID('s)
    * @return   Returns the array with ThreadID('s)
    */
    function create_tid_array($tids) {
        $tidArr = array();
        $tidP = explode(',', $tids);
        foreach($tidP AS $flip) {
            $flip = (int) $flip;
            if ( $flip > 0 ) {
                $tidArr[] = $flip;
            }
        }
        return $tidArr;
    }
} // end class

$mod = new mod();


// Start actions...
    switch($action) {

    case 'delete':
        if(!$mod->statuscheck($fid)) {
            error($lang['textnoaction']);
        }

        if ( !isset($_POST['deletesubmit']) ) {
            $tid = $mod->create_tid_string("$tid", $thread_select);
            eval('echo stripslashes("'.template('topicadmin_delete').'");');
        }

        if ( isset($_POST['deletesubmit']) ) {
            $thread_select = $mod->create_tid_array("$tid");
            foreach($thread_select AS $tid) {

                $subject = $db->result($db->query("SELECT subject FROM $table_threads WHERE tid=$tid"), 0);

                $query = $db->query("SELECT author FROM $table_posts WHERE tid='$tid'");
                while($result = $db->fetch_array($query)) {
                    $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$result[author]'");
                }

                $db->query("DELETE FROM $table_threads WHERE tid='$tid'");
                $db->query("DELETE FROM $table_posts WHERE tid='$tid'");
                $db->query("DELETE FROM $table_attachments WHERE tid='$tid'");
                $db->query("DELETE FROM $table_favorites WHERE tid='$tid'");

                // make sure to also delete any redirects leftover
                $db->query("DELETE FROM $table_threads WHERE closed='moved|$tid'");

                if ($forums['type'] == 'sub') {
                    updateforumcount($fup['fup']);
                }
                updateforumcount($fid);

                logAction('useModDelete', array('tid'=>$tid, 'fid'=>$fid, 'subject'=>$subject, 'ip'=>$onlineip), X_LOG_MOD);

            }
            echo '<p align="center" class="mediumtxt">'.$lang['deletethreadmsg'].'</p>';
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'close':
        if(!$mod->statuscheck($fid)) {
            error($lang['textnoaction']);
        }

        $query = $db->query("SELECT closed FROM $table_threads WHERE fid='$fid' AND tid=$tid");
        $closed = $db->result($query, 0);

        if ( !isset($_POST['closesubmit']) ) {
            if ( $closed == "yes") {
                $lang['textclosethread'] = $lang['textopenthread'];
            }
            eval('echo stripslashes("'.template('topicadmin_openclose').'");');
        }

        if ( isset($_POST['closesubmit']) ) {
            if ( $closed == 'yes') {
                $db->query("UPDATE $table_threads SET closed='' WHERE tid='$tid' AND fid='$fid'");
            } else {
                $db->query("UPDATE $table_threads SET closed='yes' WHERE tid='$tid' AND fid='$fid'");
            }

            $act = ( $closed != "" ) ? 'open' : 'close';
            if($act == 'open') {
                logAction('useModOpen', array('tid'=>$tid, 'fid'=>$fid, 'ip'=>$onlineip), X_LOG_MOD);
            } else {
                logAction('useModClose', array('tid'=>$tid, 'fid'=>$fid, 'ip'=>$onlineip), X_LOG_MOD);
            }

            echo '<p align="center" class="mediumtxt">'.$lang['closethreadmsg'].'</p>';
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'f_close':
        if(!$mod->statuscheck($fid)) {
            error($lang['textnoaction']);
        }

        if ( !isset($_POST['closesubmit']) ) {
            $tid = $mod->create_tid_string("$tid", $thread_select);

            eval('echo stripslashes("'.template('topicadmin_openclose').'");');
        }

        if ( isset($_POST['closesubmit']) ) {
            $thread_select = $mod->create_tid_array("$tid");
            foreach($thread_select AS $tid) {

                $db->query("UPDATE $table_threads SET closed='yes' WHERE tid='$tid' AND fid='$fid'");
                logAction('useModClose', array('tid'=>$tid, 'fid'=>$fid, 'ip'=>$onlineip), X_LOG_MOD);

            }
            echo '<p align="center" class="mediumtxt">'.$lang['closethreadmsg'].'</p>';
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;


    case 'f_open':
        if(!$mod->statuscheck($fid)) {
            error($lang['textnoaction']);
        }

        if ( !isset($_POST['closesubmit']) ) {
            $tid = $mod->create_tid_string("$tid", $thread_select);

            $lang['textclosethread'] = $lang['textopenthread'];
            eval('echo stripslashes("'.template('topicadmin_openclose').'");');
        }

        if ( isset($_POST['closesubmit']) ) {
            $thread_select = $mod->create_tid_array("$tid");
            foreach($thread_select AS $tid) {

                $db->query("UPDATE $table_threads SET closed='' WHERE tid='$tid' AND fid='$fid'");
                logAction('useModOpen', array('tid'=>$tid, 'fid'=>$fid, 'ip'=>$onlineip), X_LOG_MOD);

            }
            echo '<p align="center" class="mediumtxt">'.$lang['closethreadmsg'].'</p>';
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'move':
        if(!$mod->statuscheck($fid)) {
            error($lang['textnoaction']);
        }

        if ( !isset($_POST['movesubmit']) ) {
            $tid = $mod->create_tid_string("$tid", $thread_select);

            $forumselect = "<select    name=\"moveto\">\n";
            $queryfor = $db->query("SELECT fid, name, status, postperm, userlist, password FROM $table_forums WHERE fup='' AND type='forum' ORDER BY displayorder");

            while($forum = $db->fetch_array($queryfor)) {
                $fid = $forum['fid'];
                $fperm = checkForumPermissions($forum);
                if($fperm[X_PERMS_VIEW] && $fperm[X_PERMS_USERLIST] && $fperm[X_PERMS_PASSWORD]) {
                    $forumselect .= '<option value="'.$fid.'" '.($forum['status'] == 'off' ? 'disabled="disabled"' : '').'>    &nbsp; &raquo; '.$forum['name'].'</option>';
                    $querysub = $db->query("SELECT fid, name, status, postperm, userlist, password FROM $table_forums WHERE fup=$fid AND type='sub' ORDER BY displayorder");
                    while($sub = $db->fetch_array($querysub)) {
                        $subperm = checkForumPermissions($sub);
                        if($subperm[X_PERMS_VIEW] && $subperm[X_PERMS_USERLIST] && $subperm[X_PERMS_PASSWORD]) {
                            $forumselect .= '<option value="'.$sub['fid'].'" '.($sub['status'] == 'off' ? 'disabled="disabled"' : '').'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
                        } else {
                            $forumselect .= '<option value="'.$sub['fid'].'" disabled="disabled">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
                        }
                    }

                } else {
                    $forumselect .= '<option value="'.$fid.'" disabled="disabled">    &nbsp; &raquo; '.$forum['name'].'</option>';
                    $querysub = $db->query("SELECT fid, name, status FROM $table_forums WHERE fup=$fid AND type='sub' ORDER BY displayorder");
                    while($sub = $db->fetch_array($querysub)) {
                        $forumselect .= '<option value="'.$sub['fid'].'" disabled="disabled">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
                    }
                }
                $forumselect .= "<option value=\"\" disabled=\"disabled\">&nbsp;</option>";
            }

            $querygrp = $db->query("SELECT fid, name FROM $table_forums WHERE type='group' ORDER BY displayorder");
            while($group = $db->fetch_array($querygrp))    {
                $forumselect .= "<option value=\"\"    disabled=\"disabled\">$group[name]</option>";
                $forumselect .= "<option value=\"\"    disabled=\"disabled\">--------------------</option>";

                $queryfor = $db->query("SELECT fid, name, status, userlist, postperm, password FROM $table_forums WHERE fup='$group[fid]' AND type='forum' ORDER BY displayorder");
                while($forum = $db->fetch_array($queryfor))    {
                    $fid = $forum['fid'];
                    $fperm = checkForumPermissions($forum);
                    if($fperm[X_PERMS_VIEW] && $fperm[X_PERMS_USERLIST] && $fperm[X_PERMS_PASSWORD]) {
                        $forumselect .= "<option value=\"$forum[fid]\" ".($forum['status'] == 'off' ? 'disabled="disabled"' : '')."> &nbsp; &raquo; $forum[name]</option>";
                        $querysub = $db->query("SELECT status, fid, name, postperm, userlist, password FROM $table_forums WHERE fup='$forum[fid]' AND type='sub' ORDER BY displayorder");
                        while($sub = $db->fetch_array($querysub)) {
                            $subperm = checkForumPermissions($sub);
                            if($subperm[X_PERMS_VIEW] && $subperm[X_PERMS_USERLIST] && $subperm[X_PERMS_PASSWORD]) {
                                $forumselect .= '<option value="'.$sub['fid'].'" '.($sub['status'] == 'off' ? 'disabled="disabled"' : '').'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
                            } else {
                                $forumselect .= '<option value="'.$sub['fid'].'" disabled="disabled">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
                            }
                        }
                    } else {
                        $forumselect .= '<option value="'.$fid.'" disabled="disabled">    &nbsp; &raquo; '.$forum['name'].'</option>';
                        $querysub = $db->query("SELECT fid, name, status FROM $table_forums WHERE fup=$fid AND type='sub' ORDER BY displayorder");
                        while($sub = $db->fetch_array($querysub)) {
                            $forumselect .= '<option value="'.$sub['fid'].'" disabled="disabled">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
                        }
                    }
                }

                $forumselect .= "<option value=\"\"    disabled=\"disabled\">&nbsp;</option>";
            }

            $forumselect .= "</select>";
            eval('echo stripslashes("'.template('topicadmin_move').'");');
        }

        if ( isset($_POST['movesubmit']) ) {
            if(!$mod->statuscheck($moveto)) {
                error($lang['textnoaction']);
            }

            if ( empty($moveto) ) {
                echo "<center><span class=\"mediumtxt \">$lang[errormovingthreads]</span></center>";
                end_time();
                eval("echo (\"".template('footer')."\");");
                exit();
            }


            $thread_select = $mod->create_tid_array("$tid");
            foreach($thread_select AS $tid) {

                if ( $type == "normal") {
                    $db->query("UPDATE $table_threads SET fid=$moveto WHERE tid=$tid");
                    $db->query("UPDATE $table_posts SET fid=$moveto WHERE tid=$tid");
                } else {
                    $query = $db->query("SELECT * FROM $table_threads WHERE tid='$tid'");
                    $info = $db->fetch_array($query);
                    $db->query("INSERT INTO $table_threads (fid, subject, icon, lastpost, views, replies, author, closed, topped, pollopts) VALUES ($info[fid], '$info[subject]', '', '$info[lastpost]', '-', '-', '$info[author]', 'moved|$info[tid]', $info[topped], '$info[pollopts]')");
                    $ntid = $db->insert_id();
                    $db->query("INSERT INTO $table_posts ( fid, tid, pid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff    ) VALUES ($info[fid], $ntid, '', '$info[author]', '$info[tid]', '$info[subject]', '', '', '', '', '', '')");

                    $db->query("UPDATE $table_threads SET fid=$moveto WHERE tid=$tid AND fid=$fid");
                    $db->query("UPDATE $table_posts SET fid=$moveto WHERE tid=$tid AND fid=$fid");
                }
                updatethreadcount($tid);

                logAction('useModMove', array('tid'=>$tid, 'fidOld'=>$fid, 'fidNew'=>$moveto, 'ip'=>$onlineip, 'redirect'=>($normal ? false : true)), X_LOG_MOD);

            }

            if ( $forums['type'] == "sub" ) {
                updateforumcount($fup['fup']);
            }
            updateforumcount($fid);
            updateforumcount($moveto);

            echo '<p align="center" class="mediumtxt">'.$lang['movethreadmsg'].'</p>';
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'top':
        if(!$mod->statuscheck($fid)) {
            error($lang['textnoaction']);
        }

        if ( !isset($_POST['topsubmit']) ) {
            $tid = $mod->create_tid_string("$tid", $thread_select);

            $lang['texttopthread'] = $lang['topuntop'];

            eval('echo stripslashes("'.template('topicadmin_topuntop').'");');
        }

        if ( isset($_POST['topsubmit']) ) {
            $thread_select = $mod->create_tid_array("$tid");
            foreach($thread_select AS $tid) {

                $query = $db->query("SELECT topped FROM $table_threads WHERE fid='$fid' AND tid='$tid'");
                $topped = $db->result($query, 0);

                if ( $topped == 1 ) {
                    $db->query("UPDATE $table_threads SET topped='0' WHERE tid='$tid' AND fid='$fid'");
                } elseif ( $topped == 0 ) {
                    $db->query("UPDATE $table_threads SET topped='1' WHERE tid='$tid' AND fid='$fid'");
                }

                $act = ($topped    ? 'untop' :    'top');
                if ( $topped == 1 ) {
                    logAction('useModUntop', array('tid'=>$tid, 'fid'=>$fid, 'ip'=>$onlineip), X_LOG_MOD);
                } else {
                    logAction('useModTop', array('tid'=>$tid, 'fid'=>$fid, 'ip'=>$onlineip), X_LOG_MOD);
                }

            }
            echo '<p align="center" class="mediumtxt">'.$lang['topthreadmsg'].'</p>';
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'getip':
        if (!X_ADMIN) {
            error($lang['textnoaction']);
        }

        if ( $pid ) {
            $query = $db->query("SELECT * FROM $table_posts WHERE pid='$pid'");
        }else{
            $query = $db->query("SELECT * FROM $table_threads WHERE tid='$tid'");
        }

        $ipinfo = $db->fetch_array($query);

    ?>
    <form method="post" action="cp.php?action=ipban">
    <table cellspacing="0" cellpadding="0" border="0" width="60%" align="center">
    <tr><td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr>
    <td class="header" colspan="3"><?php echo $lang['textgetip']?></td>
    </tr>
    <tr style="background-color: <?php echo $THEME['altbg2']?>">
    <td class="tablerow"><?php echo $lang['textyesip']?> <b><?php echo $ipinfo['useip']?></b> - <?php echo gethostbyaddr($ipinfo['useip'])?>
    <?php

    if (X_ADMIN) {
        $ip = explode(".", $ipinfo['useip']);
        $query = $db->query("SELECT * FROM $table_banned WHERE (ip1='$ip[0]' OR ip1='-1') AND (ip2='$ip[1]' OR ip2='-1') AND (ip3='$ip[2]' OR ip3='-1') AND (ip4='$ip[3]' OR ip4='-1')");
        $result = $db->fetch_array($query);

        if ( $result ) {
            $buttontext = $lang['textunbanip'];
            for ($i=1; $i<=4; ++$i) {
                $j = "ip$i";
                if ($result[$j] == -1) {
                    $result[$j] = "*";
                    $foundmask = 1;
                }
            }

            if ( $foundmask ) {
                $ipmask = "<b>$result[ip1].$result[ip2].$result[ip3].$result[ip4]</b>";
                eval($lang['evalipmask']);
                $lang['bannedipmask'] = stripslashes($lang['bannedipmask']);
                echo $lang['bannedipmask'];
            } else {
                $lang['textbannedip'] = stripslashes($lang['textbannedip']);
                echo $lang['textbannedip'];
            }
            echo "<input type=\"hidden\" name=\"delete$result[id]\"    value=\"$result[id]\" />";
        } else {
            $buttontext = $lang['textbanip'];
            for ($i=1; $i<=4; ++$i) {
                $j = $i - 1;
                echo "<input type=\"hidden\" name=\"newip$i\" value=\"$ip[$j]\" />";
            }
        }
        ?>
        </td></tr>
        <tr style="background-color: <?php echo $THEME['altbg1']?>"><td class="tablerow">
        <center><input type="submit" name="ipbansubmit" value="<?php echo $buttontext?>" /></center>
        <?php
    }
    echo '</td></tr></table></td></tr></table></form>';
    break;

    case 'bump':
        if(!$mod->statuscheck($fid)) {
            error($lang['textnoaction']);
        }

        if ( !isset($_POST['bumpsubmit']) ) {
            $tid = $mod->create_tid_string("$tid", $thread_select);

            eval('echo stripslashes("'.template('topicadmin_bump').'");');
        }

        if ( isset($_POST['bumpsubmit']) ) {
            $thread_select = $mod->create_tid_array("$tid");
            foreach($thread_select AS $tid) {

                $pid = $db->result($db->query("SELECT pid FROM $table_posts WHERE tid='$tid' ORDER BY pid DESC LIMIT 1"), 0);

                $db->query("UPDATE $table_threads SET lastpost='".$onlinetime."|$xmbuser|$pid' WHERE    tid=$tid AND fid=$fid");
                $db->query("UPDATE $table_forums SET lastpost='".$onlinetime."|$xmbuser|$pid' WHERE fid=$fid");

                logAction('useModBump', array('tid'=>$tid, 'fid'=>$fid, 'ip'=>$onlineip), X_LOG_MOD);

            }
            echo '<p align="center" class="mediumtxt">'.$lang['bumpthreadmsg'].'</p>';
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'empty':
        if(!$mod->statuscheck($fid)) {
            error($lang['textnoaction']);
        }

        if ( !isset($_POST['emptysubmit']) ) {
            $tid = $mod->create_tid_string("$tid", $thread_select);

            eval('echo stripslashes("'.template('topicadmin_empty').'");');
        }

        if ( isset($_POST['emptysubmit']) ) {
            $thread_select = $mod->create_tid_array("$tid");
            foreach($thread_select AS $tid) {

                $query = $db->query("SELECT pid FROM $table_posts WHERE tid='$tid' ORDER BY pid ASC LIMIT 1");
                $pid = $db->result($query, 0);

                $query = $db->query("SELECT author FROM $table_posts WHERE tid='$tid' AND pid!='$pid'");
                while($result = $db->fetch_array($query)) {
                    $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$result[author]'");
                }

                $db->query("DELETE FROM $table_posts WHERE tid='$tid' AND pid!='$pid'");
                $db->query("DELETE FROM $table_attachments WHERE pid='$pid'");

                updatethreadcount($tid);
                logAction('useModEmpty', array('tid'=>$tid, 'fid'=>$fid, 'ip'=>$onlineip), X_LOG_MOD);

            }

            if ($forums['type'] == 'sub') {
                updateforumcount($fup['fup']);
            }
            updateforumcount($fid);

            echo '<p align="center" class="mediumtxt">'.$lang['emptythreadmsg'].'</p>';
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
    break;

    case 'split':
        if(!$mod->statuscheck($fid)) {
            error($lang['textnoaction']);
        }

        if (!$splitsubmit) {
            $query = $db->query("SELECT replies FROM $table_threads WHERE tid='$tid'");
            $replies = $db->result($query, 0);

            if ( $replies == 0) {
                error($lang['cantsplit'], false);
            }

            $query = $db->query("SELECT    * FROM $table_posts    WHERE tid='$tid' ORDER BY dateline");
            while($post = $db->fetch_array($query))    {
                $bbcodeoff = $post['bbcodeoff'];
                $smileyoff = $post['smileyoff'];
                $post['message'] = stripslashes($post['message']);
                $post['message'] = postify($post['message'], $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", $table_words, $table_forums, $table_smilies);
                eval("\$posts .= \"".template("topicadmin_split_row")."\";");
            }

            eval('echo stripslashes("'.template('topicadmin_split').'");');
        } else {
            $subject = trim($subject);
            if ($subject == '') {
                error($lang['textnosubject'], false);
            }
            $subject = addslashes($subject);
            $subject = checkInput($subject, $chkInputTags, $chkInputHTML, '', false);

            $firstsubject = false;
            $query = $db->query("SELECT subject, pid FROM $table_posts WHERE tid='$tid'");
            while ($post = $db->fetch_array($query)) {
                $move = "move$post[pid]";
                $move = "${$move}";
                $thatime = $onlinetime;
                if (!$firstsubject) {
                    $db->query("INSERT INTO $table_threads (fid, subject, icon, lastpost, views, replies, author, closed, topped, pollopts) VALUES ($fid, '$subject', '', '$thatime|$xmbuser', 0, 0, '$xmbuser', '', '', '')");
                    $newtid = $db->insert_id();
                    $firstsubject = 1;
                }

                if (!empty($move)) {
                    $db->query("UPDATE $table_posts SET tid=$newtid WHERE pid=$move");
                    $db->query("UPDATE $table_attachments SET tid=$newtid WHERE pid=$move");

                    $db->query("UPDATE $table_threads SET replies=replies+1 WHERE tid=$newtid");
                    $db->query("UPDATE $table_threads SET replies=replies-1 WHERE tid=$tid");
                }
            } // while

            $query = $db->query("SELECT author FROM $table_posts WHERE tid=$newtid ORDER BY dateline ASC LIMIT 0,1");
            $firstauthor = $db->result($query, 0);
            $query = $db->query("SELECT author, dateline, pid FROM $table_posts WHERE tid=$newtid ORDER BY dateline DESC LIMIT 0,1");
            $lastpost = $db->fetch_array($query);
            $db->query("UPDATE $table_threads SET author='$firstauthor', lastpost='$lastpost[dateline]|$lastpost[author]|$lastpost[pid]', replies=replies-1 WHERE tid=$newtid");

            $query = $db->query("SELECT author FROM $table_posts WHERE tid=$tid ORDER BY dateline ASC LIMIT 0,1");
            $firstauthor = $db->result($query, 0);
            $query = $db->query("SELECT author, dateline, pid FROM $table_posts WHERE tid=$tid ORDER BY dateline DESC LIMIT 0,1");
            $lastpost = $db->fetch_array($query);
            $db->query("UPDATE $table_threads SET author='$firstauthor', lastpost='$lastpost[dateline]|$lastpost[author]|$lastpost[pid]' WHERE tid=$tid");

            logAction('useModSplit', array('tid1'=>$tid, 'tid2'=> $newtid, 'fid'=>$fid, 'ip'=>$onlineip), X_LOG_MOD);

            echo '<p align="center" class="mediumtxt">'.$lang['splitthreadmsg'].'</p>';
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'merge':
        if(!$mod->statuscheck($fid)) {
            error($lang['textnoaction']);
        }

        if (!$mergesubmit) {
            eval('echo stripslashes("'.template('topicadmin_merge').'");');
        }else{
            if ( $tid == $othertid) {
                error($lang['cannotmergesamethread']);
            }

            $queryadd1 = $db->query("SELECT replies, fid FROM $table_threads WHERE tid=$othertid");
            $queryadd2 = $db->query("SELECT replies FROM $table_threads WHERE tid=$tid");

            $replyadd = $db->result($queryadd1, 0, 'replies');
            $otherfid = $db->result($queryadd1, 0, 'fid');
            $replyadd2 = $db->result($queryadd2, 0);
            $replyadd++;
            $replyadd = $replyadd + $replyadd2;

            if(!$mod->statuscheck($otherfid)) {
                error($lang['textnoaction']);
            }

            // Change tid on attachments & posts
            $db->query("UPDATE $table_posts SET tid=$tid, fid=$fid WHERE tid=$othertid");
            $db->query("UPDATE $table_attachments SET tid=$tid WHERE tid=$othertid");

            // Get rid of the old thread
            $db->query("DELETE FROM $table_threads WHERE tid=$othertid");

            // Update threadcount in old forum
            $db->query("UPDATE $table_forums SET threads = threads-1 WHERE fid=$otherfid");

            // Change subscriptions and such
            $query = $db->query("SELECT * FROM $table_favorites WHERE tid=$othertid OR tid=$tid");
            if ( $db->num_rows($query) == 2) {
                $db->query("DELETE FROM $table_favorites WHERE tid=$othertid");
            }else{
                $db->query("UPDATE $table_favorites SET tid=$tid WHERE tid=$othertid");
            }

            // Recreate the thread-entry
            $query = $db->query("SELECT subject, author, icon FROM $table_posts WHERE tid=$tid OR tid=$othertid ORDER BY pid ASC LIMIT 1");
            $thread = $db->fetch_array($query);
            $query = $db->query("SELECT author, dateline, pid FROM $table_posts WHERE tid=$tid ORDER BY dateline DESC LIMIT 0,1");
            $lastpost = $db->fetch_array($query);
            $db->query("UPDATE $table_threads SET replies = $replyadd, subject='$thread[subject]', icon='$thread[icon]', author='$thread[author]', lastpost='$lastpost[dateline]|$lastpost[author]|$lastpost[pid]' WHERE tid=$tid");

            // Log this action
            logAction('useModMerge', array('tid1'=>$tid, 'tid2'=> $othertid, 'fid'=>$fid, 'ip'=>$onlineip), X_LOG_MOD);

            echo '<p align="center" class="mediumtxt">'.$lang['mergethreadmsg'].'</p>';
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'threadprune':
        if(!$mod->statuscheck($fid)) {
            error($lang['textnoaction']);
        }

        if (!$threadprunesubmit) {
            $query = $db->query("SELECT replies FROM $table_threads WHERE tid='$tid'");
            $replies = $db->result($query, 0);

            if ( $replies == 0 ) {
                error($lang['cantthreadprune'], false);
            }

            if(X_SADMIN || $SETTINGS['allowrankedit'] == 'off') {
                $disablePost = '';
                $query = $db->query("SELECT * FROM $table_posts WHERE tid='$tid' ORDER BY dateline");
                while($post = $db->fetch_array($query)) {
                    $bbcodeoff = $post['bbcodeoff'];
                    $smileyoff = $post['smileyoff'];
                    $post['message'] = stripslashes($post['message']);
                    $post['message'] = postify($post['message'], $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", $table_words, $table_forums, $table_smilies);
                    eval("\$posts .= \"".template("topicadmin_threadprune_row")."\";");
                }
            } else {
                $ranks = array('Super Administrator'=>5, 'Administrator'=>4, 'Super Moderator'=>3, 'Moderator'=>2, 'Member'=>1, ''=>0);
                $query = $db->query("SELECT p.*, m.status FROM $table_posts p LEFT JOIN $table_members m ON (m.username=p.author) WHERE tid='$tid' ORDER BY dateline");
                while($post = $db->fetch_array($query)) {
                    if($ranks[$post['status']] > $ranks[$self['status']]) {
                        $disablePost = 'disabled="disabled"';
                    } else {
                        $disablePost = '';
                    }
                    $bbcodeoff = $post['bbcodeoff'];
                    $smileyoff = $post['smileyoff'];
                    $post['message'] = stripslashes($post['message']);
                    $post['message'] = postify($post['message'], $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", $table_words, $table_forums, $table_smilies);
                    eval("\$posts .= \"".template("topicadmin_threadprune_row")."\";");
                }
            }

            eval('echo stripslashes("'.template('topicadmin_threadprune').'");');
        } else {
            if(X_SADMIN || $SETTINGS['allowrankedit'] == 'off') {
                $query = $db->query("SELECT author, pid, message FROM $table_posts WHERE tid='$tid'");
                while($post = $db->fetch_array($query))    {
                    $move = "move$post[pid]";
                    $move = "${$move}";
                    if (!empty($move)) {
                        $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='{$post['author']}'");
                        $db->query("DELETE FROM $table_posts WHERE pid='$move'");
                        $db->query("DELETE FROM $table_attachments WHERE pid='$move'");
                        $db->query("UPDATE $table_threads SET replies=replies-1 WHERE tid='$tid'");
                    }
                }
            } else {
                $ranks = array('Super Administrator'=>5, 'Administrator'=>4, 'Super Moderator'=>3, 'Moderator'=>2, 'Member'=>1, ''=>0);
                $query = $db->query("SELECT m.status, p.author, p.pid FROM $table_posts p LEFT JOIN $table_members m ON (m.username=p.author) WHERE p.tid='$tid'");
                while($post = $db->fetch_array($query))    {
                    if($ranks[$post['status']] > $ranks[$self['status']]) {
                        continue;
                    }
                    $move = "move$post[pid]";
                    $move = "${$move}";
                    if (!empty($move)) {
                        $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='{$post['author']}'");
                        $db->query("DELETE FROM $table_posts WHERE pid='$move'");
                        $db->query("DELETE FROM $table_attachments WHERE pid='$move'");
                        $db->query("UPDATE $table_threads SET replies=replies-1 WHERE tid='$tid'");
                    }
                }

            }

            $query = $db->query("SELECT author FROM $table_posts WHERE tid='$tid' ORDER BY dateline ASC LIMIT 0,1");
            $firstauthor = $db->result($query, 0);
            $query = $db->query("SELECT pid, author, dateline FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC LIMIT 0,1");
            $lastpost = $db->fetch_array($query);
            $db->query("UPDATE $table_threads SET author='$firstauthor', lastpost='$lastpost[dateline]|$lastpost[author]|$lastpost[pid]' WHERE tid='$tid'");
            if ($forums['type'] == "sub") {
                $query= $db->query("SELECT fup FROM $table_forums WHERE fid='$fid' LIMIT 1");
                $fup = $db->fetch_array($query);
                updateforumcount($fid);
                updateforumcount($fup['fup']);
            } else {
                updateforumcount($fid);
            }

            // Log this action
            logAction('useModPrune', array('tid'=>$tid, 'fid'=>$fid, 'ip'=>$onlineip), X_LOG_MOD);

            // Tell the user it's done, and redirect the user back to the forum the original thread is in.
            echo '<p align="center" class="mediumtxt">'.$lang['complete_threadprune'].'</p>';
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
    break;

    case 'copy':
        if(!$mod->statuscheck($fid)) {
            error($lang['textnoaction']);
        }

        if ( !isset($_POST['copysubmit']) ) {
            $tid = $mod->create_tid_string("$tid", $thread_select);

            // start forumselect
            $forumselect = "<select    name=\"newfid\">\n";
            $queryfor = $db->query("SELECT fid, name, status, postperm, userlist, password FROM $table_forums WHERE fup='' AND type='forum' ORDER BY displayorder");

            while($forum = $db->fetch_array($queryfor)) {
                $fid = $forum['fid'];
                $fperm = checkForumPermissions($forum);
                if($fperm[X_PERMS_VIEW] && $fperm[X_PERMS_USERLIST] && $fperm[X_PERMS_PASSWORD]) {
                    $forumselect .= '<option value="'.$fid.'" '.($forum['status'] == 'off' ? 'disabled="disabled"' : '').'>    &nbsp; &raquo; '.$forum['name'].'</option>';
                    $querysub = $db->query("SELECT fid, name, status, postperm, userlist, password FROM $table_forums WHERE fup=$fid AND type='sub' ORDER BY displayorder");
                    while($sub = $db->fetch_array($querysub)) {
                        $subperm = checkForumPermissions($sub);
                        if($subperm[X_PERMS_VIEW] && $subperm[X_PERMS_USERLIST] && $subperm[X_PERMS_PASSWORD]) {
                            $forumselect .= '<option value="'.$sub['fid'].'" '.($sub['status'] == 'off' ? 'disabled="disabled"' : '').'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
                        } else {
                            $forumselect .= '<option value="'.$sub['fid'].'" disabled="disabled">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
                        }
                    }

                } else {
                    $forumselect .= '<option value="'.$fid.'" disabled="disabled">    &nbsp; &raquo; '.$forum['name'].'</option>';
                    $querysub = $db->query("SELECT fid, name, status FROM $table_forums WHERE fup=$fid AND type='sub' ORDER BY displayorder");
                    while($sub = $db->fetch_array($querysub)) {
                        $forumselect .= '<option value="'.$sub['fid'].'" disabled="disabled">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
                    }
                }
                $forumselect .= "<option value=\"\" disabled=\"disabled\">&nbsp;</option>";
            }

            $querygrp = $db->query("SELECT fid, name FROM $table_forums WHERE type='group' ORDER BY displayorder");
            while($group = $db->fetch_array($querygrp))    {
                $forumselect .= "<option value=\"\"    disabled=\"disabled\">$group[name]</option>";
                $forumselect .= "<option value=\"\"    disabled=\"disabled\">--------------------</option>";

                $queryfor = $db->query("SELECT fid, name, status, userlist, postperm, password FROM $table_forums WHERE fup='$group[fid]' AND type='forum' ORDER BY displayorder");
                while($forum = $db->fetch_array($queryfor))    {
                    $fid = $forum['fid'];
                    $fperm = checkForumPermissions($forum);
                    if($fperm[X_PERMS_VIEW] && $fperm[X_PERMS_USERLIST] && $fperm[X_PERMS_PASSWORD]) {
                        $forumselect .= "<option value=\"$forum[fid]\" ".($forum['status'] == 'off' ? 'disabled="disabled"' : '')."> &nbsp; &raquo; $forum[name]</option>";
                        $querysub = $db->query("SELECT status, fid, name, postperm, userlist, password FROM $table_forums WHERE fup='$forum[fid]' AND type='sub' ORDER BY displayorder");
                        while($sub = $db->fetch_array($querysub)) {
                            $subperm = checkForumPermissions($sub);
                            if($subperm[X_PERMS_VIEW] && $subperm[X_PERMS_USERLIST] && $subperm[X_PERMS_PASSWORD]) {
                                $forumselect .= '<option value="'.$sub['fid'].'" '.($sub['status'] == 'off' ? 'disabled="disabled"' : '').'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
                            } else {
                                $forumselect .= '<option value="'.$sub['fid'].'" disabled="disabled">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
                            }
                        }
                    } else {
                        $forumselect .= '<option value="'.$fid.'" disabled="disabled">    &nbsp; &raquo; '.$forum['name'].'</option>';
                        $querysub = $db->query("SELECT fid, name, status FROM $table_forums WHERE fup=$fid AND type='sub' ORDER BY displayorder");
                        while($sub = $db->fetch_array($querysub)) {
                            $forumselect .= '<option value="'.$sub['fid'].'" disabled="disabled">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
                        }
                    }
                }

                $forumselect .= "<option value=\"\"    disabled=\"disabled\">&nbsp;</option>";
            }

            $forumselect .= "</select>";
            // end forum select
            eval('echo stripslashes("'.template('topicadmin_copy').'");');

        }

        if ( isset($_POST['copysubmit']) ) {
            if ( !isset($newfid) || (int) $newfid < 1) {
                error($lang['privforummsg'], false);
            }
            if(!$mod->statuscheck($newfid)) {
                error($lang['textnoaction']);
            }

            $thread_select = $mod->create_tid_array("$tid");
            foreach($thread_select AS $tid) {

                $thread = $db->fetch_array($db->query("SELECT * FROM $table_threads WHERE tid='$tid'"));
                foreach ($thread    as $key=>$val) {
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
                // need to remember EMPTY VALUES! so we can't use implode()
                $cols = array();
                $vals = array();

                reset($thread);
                foreach ($thread as $key=>$val) {
                    if(trim($key) == '') {
                        continue;
                    }
                    if ( $key == 'subject') {
                        $val = '[COPY]    '.$val;
                    }
                    $cols[] = $key;
                    $vals[] = addslashes($val);
                }
                reset($thread);
                $columns = implode(', ', $cols);
                $values  = "'".implode("', '", $vals)."'";

                // create new thread
                $db->query("INSERT INTO $table_threads ($columns) VALUES ($values)") or die($db->error());

                // get tid
                $newtid = $db->insert_id();

                // also copy all posts to the new thread.
                $query = $db->query("SELECT * FROM $table_posts WHERE tid='$tid' ORDER BY pid ASC");
                while($post = $db->fetch_array($query)) {
                    $cols = array();
                    $vals = array();

                    $post['fid'] = $newfid;
                    $post['tid'] = $newtid;

                    $oldPid = $post['pid'];

                    unset($post['pid']);
                    reset($post);

                    foreach ($post as $key=>$val) {
                        $cols[] = $key;
                        $vals[] = addslashes($val);
                    }
                    $columns = implode(', ', $cols);
                    $values  = "'".implode("', '", $vals)."'";

                    $cols = array();
                    $vals = array();

                    // create new post in thread
                    $db->query("INSERT INTO $table_posts ($columns) VALUES ($values)") or die($db->error());
                    $newpid = $db->insert_id();

                    // remember the attachment!
                    if(version_compare($db->version, '4.1.0', '>=')) {
                        $db->query("INSERT INTO $table_attachments (`tid`,`pid`,`filename`,`filetype`,`filesize`,`attachment`,`downloads`) SELECT '$newtid','$newpid',`filename`,`filetype`,`filesize`,`attachment`,`downloads` FROM $table_attachments WHERE pid='$oldPid'");
                    } else {
                        $q = $db->query("SELECT aid FROM $table_attachments WHERE pid='$oldPid'");
                        if ( $db->num_rows($q) > 0) {
                            $attachment = $db->fetch_array($q);
                            unset($attachment['aid']);
                            $attachment['pid'] = $newpid;
                            $attachment['tid'] = $newtid;

                            $cols = implode('`,`', array_keys($attachment));
                            $vals = implode("','", array_keys($attachment));

                            $db->query("INSERT INTO $table_attachments (`$cols`) VALUES ('$vals')");
                        }

                    }

                }

                // Log this action
                logAction('useModCopy', array('tid1'=>$tid, 'tid2'=> $newtid, 'fid'=>$fid, 'ip'=>$onlineip), X_LOG_MOD);

            }

            // Tell the user it's done, and redirect the user back to the forum the original thread is in.
            echo '<p align="center" class="mediumtxt">'.$lang['copythreadmsg'].'</p>';
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'report':
        if ( $SETTINGS['reportpost'] == "off") {
            eval('echo stripslashes("'.template('misc_feature_notavailable').'");');
            end_time();
            eval("echo (\"".template('footer')."\");");
            exit();
        }

        if (!$reportsubmit) {
            eval('echo stripslashes("'.template('topicadmin_report').'");');
        }else{
            $postcount = $db->result($db->query("SELECT count(pid) FROM $table_posts WHERE tid='$tid'"), 0);
            $query     = $db->query("SELECT moderator FROM $table_forums WHERE fid='$fid'");
            $query2    = $db->query("SELECT username FROM $table_members WHERE status='Super Administrator' OR status='Administrator'");
            $mods      = explode(", ", $db->result($query, 0));
            while($usr = $db->fetch_array($query2)) {
                $mods[] = $usr['username'];
            }
            $sent  = 0;
            $time  = $onlinetime;
            foreach ($mods as $key=>$mod) {
                $mod = trim($mod);
                $q = $db->query("SELECT ppp FROM $table_members WHERE username='$mod'");
                if ( $db->num_rows($q) == 0) {
                    continue;
                }
                $page = quickpage($postcount, $db->result($q, 0));

                $posturl = $SETTINGS['boardurl'] . "viewthread.php?tid=$tid&page=$page#pid$pid";
                $reason  = checkInput($reason);
                $message = $lang['reportmessage'].' '.$posturl."\n\n".$lang['reason'].' '.$reason;

                $db->query("INSERT INTO $table_u2u (msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus) VALUES ('$mod', '$self[username]', 'incoming', '$mod', 'Inbox', '$lang[reportsubject]', '$message', $db->time($time), 'no', 'yes')");
                $sent++;
            }
            echo '<p align="center" class="mediumtxt">'.$lang['reportmsg'].'</p>';

            $page = quickpage($postcount, $self['tpp']);
            redirect("viewthread.php?tid=$tid&page=". $page    ."#pid$pid", 2, X_REDIRECT_JS);
        }
        break;

    case 'votepoll':
        $currpoll = '';
        $currpoll = stripslashes($db->result($db->query("SELECT pollopts FROM $table_threads WHERE fid='$fid' AND tid='$tid'"), 0));
        $options = explode("#|#", $currpoll);
        $num_options = count($options);

        if (false === strpos(' '.$options[$num_options-1].' ', ' '.$xmbuser.' ') && trim($self['username']) != '' && $num_options > 1) {
            for ($i=0;$i<($num_options-1);$i++) {
                $that = array();
                $that = explode('||~|~||', $options[$i]);

                $num_votes += $that[1];
                $poll[$i]['name'] = $that[0];
                $poll[$i]['votes'] = $that[1];
            }
            if (!isset($poll[$postopnum])) {
                error($lang['pollvotenotselected'], false, '', '', 'viewthread.php?tid='.$tid);
            }
            $poll[$postopnum]['votes']++;
            $users = $options[$num_options-1].'    '.$xmbuser.' ';

            foreach ($poll as $key=>$val) {
                $p[] = implode($val, '||~|~||');
            }
            $p[] = $users;
            $p = addslashes(implode($p, '#|#'));

            $db->query("UPDATE $table_threads SET pollopts='$p' WHERE fid='$fid' AND tid='$tid'");
            echo '<p align="center" class="mediumtxt">'.$lang['votemsg'].'</p>';
        } else {
            $header = '';
            $message = (trim($self['username']) == '') ? $lang['notloggedin'] : (($postopnum == 0) ? $lang['pollvotenotselected'] : (($num_options > 1) ? $lang['alreadyvoted'] : $lang['no_poll']));

            error($message, false, '', '', 'viewthread.php?tid='.$tid);
        }

        redirect("viewthread.php?tid=$tid", 2, X_REDIRECT_JS);
        break;
    }

// Create footer
    end_time();
    eval('echo "'.template('footer').'";');
