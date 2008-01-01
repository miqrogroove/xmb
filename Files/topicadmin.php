<?php
/* $Id: topicadmin.php,v 1.20.2.13 2005/01/15 23:41:48 Daf Exp $    */
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


// Get global stuff
    require    "./header.php";

$pid = (isset($pid) ? (int) $pid : 0);
$tid = (isset($tid) ? (int) $tid : 0);
$fid = (isset($fid) ? (int) $fid : 0);
$kill= false;

// Pre-load    templates
    loadtemplates('footer_load', 'footer_querynum', 'footer_phpsql', 'footer_totaltime','topicadmin_delete','topicadmin_openclose','topicadmin_move','topicadmin_topuntop','topicadmin_bump','topicadmin_split_row','topicadmin_split','topicadmin_merge','topicadmin_report','header','footer','css');
    eval("\$css = \"".template("css")."\";");

// Get all info about thread
    if ( $tid ) {
        $query = $db->query("SELECT * FROM $table_threads WHERE tid='$tid'");
        $thread = $db->fetch_array($query);
        $threadname = stripslashes($thread['subject']);
        $fid = $thread['fid'];
    }

// Get all info    about the forum    the    thread is in
    $query  = $db->query("SELECT * FROM $table_forums WHERE fid='$fid'");
    $forums = $db->fetch_array($query);
    $forums['name'] = stripslashes($forums['name']);

// Create navigation
    if ( $forums['type'] == 'forum') {
        $postaction = "<a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &raquo; <a href=\"viewthread.php?tid=$tid\">$threadname</a> &raquo; ";
    } elseif ( $forums['type'] == 'sub') {
        $query = $db->query("SELECT name, fid FROM $table_forums WHERE fid='$forums[fup]'");
        $fup = $db->fetch_array($query);
        $postaction = "<a href=\"forumdisplay.php?fid=$fup[fid]\">$fup[name]</a> &raquo; <a href=\"forumdisplay.php?fid=$fid\">$forums[name]</a> &raquo; <a href=\"viewthread.php?tid=$tid\">$threadname</a> &raquo; ";
    } else {
        $kill = true;
    }

    switch($action) {
    case 'delete':
        $postaction .= $lang['textdeletethread'];
        break;
    case 'top':
        $postaction .= $lang['texttopthread'];
        break;
    case 'close':
        $postaction .= $lang['textclosethread'];
        break;
    case 'copy':
        $postaction .= $lang['copythread'];
        break;
    case 'move':
        $postaction .= $lang['textmovemethod1'];
        break;
    case 'getip':
        $postaction .= $lang['textgetip'];
        break;
    case 'bump':
        $postaction .= $lang['textbumpthread'];
        break;
    case 'report':
        $postaction .= $lang['textreportpost'];
        break;
    case 'split':
        $postaction .= $lang['textsplitthread'];
        break;
    case 'merge':
        $postaction .= $lang['textmergethread'];
        break;
    case 'threadprune':
        $postaction .= $lang['textprunethread'];
        break;
    case 'empty':
        $postaction .= $lang['textemptythread'];
        break;
    case 'votepoll':
        $postaction .= $lang['textvote'];
        break;
    default:
        $kill = true;
        break;
    }

    if ( $kill) {
        error($lang['notpermitted']);
    }

    nav($postaction);

// Create and show header
eval("echo (\"".template('header')."\");");

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
        global $self, $xmbuser, $xmbpw, $lang, $action;

        if ( !X_STAFF && $action != 'votepoll' && $action != 'report' ) {
            extract($GLOBALS);
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
        global $self, $xmbuser, $lang, $table_forums, $db;

        $query   = $db->query("SELECT moderator FROM $table_forums WHERE fid='$fid'");
        $mods = $db->result($query, 0);
        $status1 = modcheck($self['status'], $xmbuser, $mods);

        // super mods, admins, and super admins can modify any topic
        if (X_SMOD || X_ADMIN) {
            $status1 = 'Moderator';
        }

        if ( $status1 != 'Moderator') {
            extract($GLOBALS);
            error($lang['textnoaction'], false);
        }
    }

    function log($user='', $action, $fid, $tid, $reason='') {
        global $xmbuser, $db, $table_logs;

        if ( $user == '') {
            $user = $xmbuser;
        }

        $db->query("REPLACE $table_logs (tid, username, action, fid, date) VALUES ('$tid', '$user', '$action', '$fid', ".$db->time().")");
        return true;
    } // end function
} // end class

$mod = new mod();

// Start actions...
    switch($action) {
    case 'delete':
        if ( !isset($deletesubmit) || !$deletesubmit) {
            eval('echo stripslashes("'.template('topicadmin_delete').'");');
            $deletesubmit = false;
        }

        if ( $deletesubmit) {
            $mod->statuscheck($fid);

            $query = $db->query("SELECT author FROM $table_posts WHERE tid='$tid'");
            while($result = $db->fetch_array($query)) {
                $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$result[author]'");
            }

            $db->query("DELETE FROM $table_threads WHERE tid='$tid'");
            $db->query("DELETE FROM $table_posts WHERE tid='$tid'");
            $db->query("DELETE FROM $table_attachments WHERE tid='$tid'");
            $db->query("DELETE FROM $table_favorites WHERE tid='$tid'");

            if ($forums['type'] == 'sub') {
                updateforumcount($fup['fup']);
            }
            updateforumcount($fid);

            $mod->log($xmbuser, $action, $fid, $tid);
            echo "<center><span class=\"mediumtxt \">$lang[deletethreadmsg]</span></center>";

            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'close':
        $query = $db->query("SELECT    closed FROM    $table_threads WHERE fid='$fid'    AND    tid='$tid'");
        $closed = $db->result($query, 0);

        if (!$closesubmit) {
            if ( $closed == "yes") {
                $lang[textclosethread] = $lang[textopenthread];
            } elseif ( $closed == "") {
                $lang[textclosethread] = $lang[textclosethread];
            }

            eval('echo stripslashes("'.template('topicadmin_openclose').'");');
        } else {
            $mod->statuscheck($fid);

            if ( $closed == 'yes') {
                $db->query("UPDATE $table_threads SET closed='' WHERE tid='$tid' AND fid='$fid'");
            } else {
                $db->query("UPDATE $table_threads SET closed='yes' WHERE tid='$tid' AND fid='$fid'");
            }

            $act = ( $closed != "" ) ? 'open' : 'close';
            $mod->log($xmbuser, $act, $fid, $tid);

            echo "<center><span class=\"mediumtxt \">$lang[closethreadmsg]</span></center>";

            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'move':
        if (!$movesubmit) {
            $forumselect = "<select    name=\"moveto\">\n";
            $queryfor = $db->query("SELECT fid, name FROM $table_forums WHERE fup='' AND type='forum' ORDER BY displayorder");

            while($forum = $db->fetch_array($queryfor))    {
            $forumselect .= "<option value=\"$forum[fid]\">    &nbsp; &raquo; $forum[name]</option>";
            $querysub = $db->query("SELECT fid, name FROM $table_forums WHERE fup='$forum[fid]' AND type='sub' ORDER BY displayorder");

            while($sub = $db->fetch_array($querysub)) {
                $forumselect .= "<option value=\"$sub[fid]\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; $sub[name]</option>";
            }

            $forumselect .= "<option value=\"\" disabled=\"disabled\">&nbsp;</option>";
        }

        $querygrp = $db->query("SELECT fid, name FROM $table_forums WHERE type='group' ORDER BY displayorder");
        while($group = $db->fetch_array($querygrp))    {
            $forumselect .= "<option value=\"\"    disabled=\"disabled\">$group[name]</option>";
            $forumselect .= "<option value=\"\"    disabled=\"disabled\">--------------------</option>";

            $queryfor = $db->query("SELECT fid, name FROM $table_forums WHERE fup='$group[fid]' AND type='forum' ORDER BY displayorder");
            while($forum = $db->fetch_array($queryfor))    {
                $forumselect .= "<option value=\"$forum[fid]\"> &nbsp; &raquo; $forum[name]</option>";
                $querysub = $db->query("SELECT    fid, name FROM $table_forums WHERE fup='$forum[fid]' AND type='sub' ORDER BY displayorder");
                while($sub = $db->fetch_array($querysub)) {
                    $forumselect .= "<option value=\"$sub[fid]\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; $sub[name]</option>";
                }
            }

            $forumselect .= "<option value=\"\"    disabled=\"disabled\">&nbsp;</option>";
        }

        $forumselect .= "</select>";
        eval('echo stripslashes("'.template('topicadmin_move').'");');
    } else {
        $mod->statuscheck($fid);

        if ( $moveto != "") {
            if ( $type == "normal") {
                $db->query("UPDATE $table_threads SET fid='$moveto' WHERE tid='$tid' AND fid='$fid'");
                $db->query("UPDATE $table_posts SET fid='$moveto' WHERE tid='$tid' AND fid='$fid'");
            } else {
                $query = $db->query("SELECT * FROM $table_threads WHERE tid='$tid'");
                $info = $db->fetch_array($query);
                $db->query("INSERT INTO $table_threads ( tid, fid, subject, icon, lastpost, views, replies, author, closed, topped, pollopts ) VALUES ('', '$info[fid]', '$info[subject]', '', '$info[lastpost]', '-', '-', '$info[author]', 'moved|$info[tid]', '$info[topped]', '$info[pollopts]')");
                $ntid = $db->insert_id();
                $db->query("INSERT INTO $table_posts ( fid, tid, pid, author, message, subject, dateline, icon, usesig, useip, bbcodeoff, smileyoff    ) VALUES ('$info[fid]', '$ntid', '', '$info[author]', '$info[tid]', '$info[subject]', '', '', '', '', '', '')");

                $db->query("UPDATE $table_threads SET fid='$moveto' WHERE tid='$tid' AND fid='$fid'");
                $db->query("UPDATE $table_posts SET fid='$moveto' WHERE tid='$tid' AND fid='$fid'");
            }
        } else {
            echo "<center><span class=\"mediumtxt \">$lang[errormovingthreads]</span></center>";
            end_time();
            eval("echo (\"".template('footer')."\");");
            exit();
        }

        if ($forums[type] == "sub") {
            updateforumcount($fup[fup]);
        }
        updateforumcount($fid);

        updateforumcount($moveto);
        updatethreadcount($tid);

        $f = "$fid -> $moveto";
        $mod->log($xmbuser, $action, $f, $tid);

        echo "<center><span class=\"mediumtxt \">$lang[movethreadmsg]</span></center>";
        redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
    }
    break;

    case 'top':
        $query = $db->query("SELECT topped FROM $table_threads WHERE fid='$fid' AND tid='$tid'");
        $topped = $db->result($query, 0);

        if (!$topsubmit) {
            if ( $topped == 1) {
                $lang[texttopthread] = $lang[textuntopthread];
            }

            eval('echo stripslashes("'.template('topicadmin_topuntop').'");');
        } else {
            $mod->statuscheck($fid);

            if ( $topped == "1") {
                $db->query("UPDATE $table_threads SET topped='0' WHERE tid='$tid' AND fid='$fid'");
            }elseif ( $topped == "0")    {
                $db->query("UPDATE $table_threads SET topped='1' WHERE tid='$tid' AND fid='$fid'");
            }

            $act = ($topped    ? 'untop' :    'top');
            $mod->log($xmbuser, $act, $fid, $tid);

            echo "<center><span    class=\"mediumtxt \">$lang[topthreadmsg]</span></center>";
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'getip':
        if ( $pid ) {
            $query = $db->query("SELECT * FROM $table_posts WHERE pid='$pid'");
        }else{
            $query = $db->query("SELECT * FROM $table_threads WHERE tid='$tid'");
        }

        $ipinfo = $db->fetch_array($query);

        $mod->statuscheck($fid);
    ?>
    <form method="post" action="cp.php?action=ipban">
    <table cellspacing="0" cellpadding="0" border="0" width="60%" align="center">
    <tr><td bgcolor="<?php echo $bordercolor?>">
    <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
    <tr>
    <td class="header" colspan="3"><?php echo $lang['textgetip']?></td>
    </tr>
    <tr bgcolor="<?php echo $altbg2?>">
    <td class="tablerow"><?php echo $lang['textyesip']?> <b><?php echo $ipinfo['useip']?></b> - <?php echo gethostbyaddr($ipinfo['useip'])?>
    <?

    if (X_ADMIN) {
        $ip = explode(".", $ipinfo[useip]);
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
        <tr    bgcolor="<?php echo    $altbg1?>"><td class="tablerow">
        <center><input type="submit" name="ipbansubmit"    value="<?php echo $buttontext?>" /></center>
        <?
    }
    echo '</td></tr></table></td></tr></table></form>';
    break;

    case 'bump':
        if (!$bumpsubmit) {
            eval('echo stripslashes("'.template('topicadmin_bump').'");');
        } else {
            $mod->statuscheck($fid);

            $db->query("UPDATE $table_threads SET lastpost='".$onlinetime."|$xmbuser' WHERE    tid=$tid AND fid=$fid");
            $db->query("UPDATE $table_forums SET lastpost='".$onlinetime."|$xmbuser' WHERE fid=$fid");

            $mod->log($xmbuser, $action, $fid, $tid);

            echo "<center><span    class=\"mediumtxt \">$lang[bumpthreadmsg]</span></center>";
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'empty':
        if (!$emptysubmit) {
            eval('echo stripslashes("'.template('topicadmin_empty').'");');
        }else{
            $mod->statuscheck($fid);

            $query = $db->query("SELECT    pid    FROM $table_posts WHERE    tid='$tid' ORDER BY    pid    ASC    LIMIT 1");
            $pid = $db->result($query, 0);
            $db->query("DELETE FROM    $table_posts WHERE tid='$tid' AND pid!='$pid'");
            updatethreadcount($tid);
            updateforumcount($fid);

            $mod->log($xmbuser, $action, $fid, $tid);

            echo "<center><span    class=\"mediumtxt \">$lang[emptythreadmsg]</span></center>";
            redirect("viewthread.php?tid=$tid", 2, X_REDIRECT_JS);
        }
    break;

    case 'split':
        if (!$splitsubmit) {
            $query = $db->query("SELECT    replies    FROM $table_threads    WHERE tid='$tid'");
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
            $mod->statuscheck($fid);
            $subject = trim($subject);
            if ($subject == '') {
                error($lang['textnosubject'], false);
            }

            $subject = addslashes($subject);
            $query = $db->query("SELECT author, subject FROM $table_posts WHERE tid='$tid' ORDER BY dateline LIMIT 0,1");
            $fpost = $db->fetch_array($query);
            $query = $db->query("SELECT subject, pid FROM $table_posts WHERE tid='$tid'");
            while ($post = $db->fetch_array($query)) {
                $move = "move$post[pid]";
                $move = "${$move}";
                $thatime = $onlinetime;
                if (!$firstsubject) {
                    $db->query("INSERT INTO $table_threads ( tid, fid, subject, icon, lastpost, views, replies, author, closed, topped, pollopts )    VALUES ('', '$fid', '$subject', '', '$thatime|$xmbuser', '0', '0', '$xmbuser', '', '', '')");
                    $newtid = $db->insert_id();
                    $firstsubject = 1;
                }

                if (!empty($move)) {
                    $db->query("UPDATE $table_posts SET tid='$newtid', subject='$subject' WHERE pid='$move'");
                    $db->query("UPDATE $table_attachments SET tid='$newtid' WHERE pid='$move'");

                    $db->query("UPDATE $table_threads SET replies=replies+1 WHERE tid='$newtid'");
                    $db->query("UPDATE $table_threads SET replies=replies-1 WHERE tid='$tid'");
                }
            } // while

            $query = $db->query("SELECT author FROM $table_posts WHERE tid='$newtid' ORDER BY dateline ASC LIMIT 0,1");
            $firstauthor = $db->result($query, 0);
            $query = $db->query("SELECT author, dateline FROM $table_posts WHERE tid='$newtid' ORDER BY    dateline DESC LIMIT    0,1");
            $lastpost = $db->fetch_array($query);
            $db->query("UPDATE $table_threads SET author='$firstauthor', lastpost='$lastpost[dateline]|$lastpost[author]', replies=replies-1 WHERE tid='$newtid'");

            $query = $db->query("SELECT author FROM $table_posts WHERE tid='$tid' ORDER BY dateline ASC LIMIT 0,1");
            $firstauthor = $db->result($query, 0);
            $query = $db->query("SELECT author, dateline FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC LIMIT 0,1");
            $lastpost = $db->fetch_array($query);
            $db->query("UPDATE $table_threads SET author='$firstauthor', lastpost='$lastpost[dateline]|$lastpost[author]' WHERE tid='$tid'");

            $mod->log($xmbuser, $action, $fid, "$tid, $newtid");

            echo "<center><span    class=\"mediumtxt \">$lang[splitthreadmsg]</span></center>";
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'merge':
        if (!$mergesubmit) {
            eval('echo stripslashes("'.template('topicadmin_merge').'");');
        }else{
            $mod->statuscheck($fid);

            if ( $tid == $othertid) {
                error($lang['cannotmergesamethread']);
            }

            $queryadd1 = $db->query("SELECT replies, fid FROM $table_threads WHERE tid='$othertid'");
            $queryadd2 = $db->query("SELECT replies FROM $table_threads WHERE tid='$tid'");

            $replyadd = $db->result($queryadd1, 0, 'replies');
            $otherfid = $db->result($queryadd1, 0, 'fid');
            $replyadd2 = $db->result($queryadd2, 0);
            $replyadd++;
            $replyadd = $replyadd + $replyadd2;

            // Change tid on attachments & posts
            $db->query("UPDATE $table_posts SET tid='$tid', fid='$fid' WHERE tid='$othertid'");
            $db->query("UPDATE $table_attachments SET tid='$tid' WHERE tid='$othertid'");

            // Get rid of the old thread
            $db->query("DELETE FROM $table_threads WHERE tid='$othertid'");

            // Update threadcount in old forum
            $db->query("UPDATE $table_forums SET threads = threads-1 WHERE fid='$otherfid'");

            // Change subscriptions    and    such
            $query = $db->query("SELECT * FROM $table_favorites WHERE tid='$othertid' OR tid='$tid'");
            if ( $db->num_rows($query) == 2) {
                $db->query("DELETE FROM $table_favorites WHERE tid='$othertid'");
            }else{
                $db->query("UPDATE $table_favorites SET tid='$tid' WHERE tid='$othertid'");
            }

            // Recreate    the    thread-entry
            $query = $db->query("SELECT subject, author, icon FROM $table_posts WHERE tid='$tid' OR tid='$othertid' ORDER BY pid ASC LIMIT 1");
            $thread = $db->fetch_array($query);
            $query = $db->query("SELECT author, dateline FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC LIMIT 0,1");
            $lastpost = $db->fetch_array($query);
            $db->query("UPDATE $table_threads SET replies = '$replyadd', subject='$thread[subject]', icon='$thread[icon]', author='$thread[author]', lastpost='$lastpost[dateline]|$lastpost[author]' WHERE tid='$tid'");

            // Log this action
            $mod->log($xmbuser, $action, $fid, "$othertid, $tid");

            // Tell the user it's done
            echo "<center><span class=\"mediumtxt \">$lang[mergethreadmsg]</span></center>";

            // Redirect the user back to the forum the merged thread is in.
            redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
        }
        break;

    case 'threadprune':
    if (!$threadprunesubmit) {
        $query = $db->query("SELECT replies FROM $table_threads WHERE tid='$tid'");
        $replies = $db->result($query, 0);

        if ( $replies == 0 ) {
            error($lang['cantthreadprune'], false);
        }

        $query = $db->query("SELECT * FROM $table_posts WHERE tid='$tid' ORDER BY dateline");
        while($post = $db->fetch_array($query)) {
            $bbcodeoff = $post['bbcodeoff'];
            $smileyoff = $post['smileyoff'];
            $post['message'] = stripslashes($post['message']);
            $post['message'] = postify($post['message'], $smileyoff, $bbcodeoff, $fid, $bordercolor, "", "", $table_words, $table_forums, $table_smilies);
            eval("\$posts .= \"".template("topicadmin_threadprune_row")."\";");
        }

        eval('echo stripslashes("'.template('topicadmin_threadprune').'");');
    }

    if ( $threadprunesubmit) {
        $mod->statuscheck($fid);

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

        $query = $db->query("SELECT author FROM $table_posts WHERE tid='$tid' ORDER BY dateline ASC LIMIT 0,1");
        $firstauthor = $db->result($query, 0);
        $query = $db->query("SELECT pid, author, dateline FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC LIMIT 0,1");
        $lastpost = $db->fetch_array($query);
        $db->query("UPDATE $table_threads SET author='$firstauthor', lastpost='$lastpost[dateline]|$lastpost[author]' WHERE tid='$tid'");
        if ($forums['type'] == "sub") {
            $query= $db->query("SELECT fup FROM $table_forums WHERE fid='$fid' LIMIT 1");
            $fup = $db->fetch_array($query);
            updateforumcount($fid);
            updateforumcount($fup['fup']);
        } else {
            updateforumcount($fid);
        }

        // Log this    action
        $mod->log($xmbuser, $action, $fid, "$othertid, $tid");

        // Tell the user it's done
        echo "<center><span class=\"mediumtxt \">$lang[complete_threadprune]</span></center>";

        // Redirect the user back to the forum the merged thread is in.
        redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
    }
    break;

    case 'copy':
        if (!isset($copysubmit)) {
            // start forumselect
            $forumselect = "<select name=\"newfid\">\n";
            $queryfor = $db->query("SELECT fid, name FROM $table_forums WHERE fup='' AND type='forum' ORDER BY displayorder");

            while ($forum = $db->fetch_array($queryfor)) {
                $forumselect .= "<option value=\"$forum[fid]\"> &nbsp; &raquo; $forum[name]</option>";
                $querysub = $db->query("SELECT fid, name FROM $table_forums WHERE fup='$forum[fid]' AND type='sub' ORDER BY displayorder");

                while ($sub = $db->fetch_array($querysub)) {
                    $forumselect .= "<option value=\"$sub[fid]\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; $sub[name]</option>";
                }

                $forumselect .= "<option value=\"\" disabled=\"disabled\">&nbsp;</option>";
            }

            $querygrp = $db->query("SELECT fid, name FROM $table_forums WHERE type='group' ORDER BY    displayorder");
            while($group = $db->fetch_array($querygrp)) {
                $forumselect .= "<option value=\"\" disabled=\"disabled\">$group[name]</option>";
                $forumselect .= "<option value=\"\" disabled=\"disabled\">--------------------</option>";

                $queryfor = $db->query("SELECT fid, name FROM $table_forums WHERE fup='$group[fid]' AND type='forum' ORDER BY displayorder");
                while($forum = $db->fetch_array($queryfor))    {
                    $forumselect .= "<option value=\"$forum[fid]\"> &nbsp; &raquo; $forum[name]</option>";

                    $querysub = $db->query("SELECT fid, name FROM $table_forums WHERE fup='$forum[fid]' AND type='sub'    ORDER BY displayorder");
                    while($sub = $db->fetch_array($querysub)) {
                        $forumselect .= "<option value=\"$sub[fid]\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; $sub[name]</option>";
                    }
                }

                $forumselect .= "<option value=\"\" disabled=\"disabled\">&nbsp;</option>";
            }

            $forumselect .= "</select>";
            // end forum select
            eval('echo stripslashes("'.template('topicadmin_copy').'");');
        } else {
            $mod->statuscheck($fid);
            if (!isset($newfid) || (int)    $newfid    < 1) {
                error($lang['privforummsg'], false);
            }
            $mod->statuscheck($newfid);

            $thread = $db->fetch_array($db->query("SELECT *    FROM $table_threads    WHERE tid='$tid'"));
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
        $cols = array();
        $vals = array();

        // also    copy all posts to the new thread.
        $query = $db->query("SELECT * FROM $table_posts WHERE tid='$tid' ORDER BY pid ASC");
        while($post = $db->fetch_array($query)) {
            $post['fid'] = $newfid;
            $post['tid'] = $newtid;

            unset($post['pid']);
            reset($post);

            foreach ($post as $key=>$val) {
                $cols[] = $key;
                $vals[] = $val;
            }
            $columns = implode(', ', $cols);
            $values  = "'".implode("', '", $vals)."'";

            $cols = array();
            $vals = array();
    
            // create new post in thread
            $db->query("INSERT INTO    $table_posts ($columns)    VALUES ($values)") or die($db->error());
            $newpid = $db->insert_id();

            // remember the attachment!
            $q = $db->query("SELECT * FROM $table_attachments WHERE pid='$pid'");
            if ( $db->num_rows($q) > 0) {
                $attachment = $db->fetch_array($q);
                $attachment['fid'] = $newfid;
                $attachment['tid'] = $newtid;
                $attachment['pid'] = $newpid;

                reset($attachment);

                foreach ($attachment as    $key=>$val) {
                    $cols[] = $key;
                    $vals[] = $val;
                }
                $columns = implode(', ', $cols);
                $values  = "'".implode("', '", $vals)."'";

                // create new post in thread
                $db->query("INSERT INTO $table_attachments ($columns) VALUES ($values)") or die($db->error());
                $cols = array();
                $vals = array();
            }
        }

        // Log this    action
        $mod->log($xmbuser, $action, $fid, "$othertid, $tid");

        // Tell    the    user it's done
        echo "<center><span    class=\"mediumtxt \">$lang[copythreadmsg]</span></center>";

        // Redirect    the    user back to the forum the original    thread is in.
        redirect("forumdisplay.php?fid=$fid", 2, X_REDIRECT_JS);
    }
    break;

    case 'report':
    if ( $reportpost == "off") {
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

            $db->query("INSERT INTO $table_u2u ( u2uid, msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus    ) VALUES ('', '$mod', '$self[username]', 'incoming', '$mod', 'Inbox', '$lang[reportsubject]', '$message', $db->time($time), 'no', 'yes')");
            $sent++;
        }
        echo "<center><span    class=\"mediumtxt \">$lang[reportmsg]</span></center>";

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

            $db->query("UPDATE $table_threads SET pollopts='$p'    WHERE fid='$fid' AND tid='$tid'");
            echo "<center><span    class=\"mediumtxt \">$lang[votemsg]</span></center>";
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
    eval("echo (\"".template('footer')."\");");
?>