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

define('X_SCRIPT', 'tools.php');

require 'header.php';
require ROOT.'include/admin.inc.php';

loadtemplates('error_nologinsession');
eval('$css = "'.template('css').'";');

nav('<a href="cp.php">'.$lang['textcp'].'</a>');
eval('echo ("'.template('header').'");');
echo '<script language="JavaScript" type="text/javascript" src="./js/admin.js"></script>';

if (!X_ADMIN) {
    eval('echo "'.template('error_nologinsession').'";');
    end_time();
    eval('echo "'.template('footer').'";');
    exit();
}

$auditaction = $_SERVER['REQUEST_URI'];
$aapos = strpos($auditaction, "?");
if ($aapos !== false) {
    $auditaction = substr($auditaction, $aapos + 1);
}
$auditaction = addslashes("$onlineip|#|$auditaction");
audit($xmbuser, $auditaction, 0, 0);

displayAdminPanel();

$action = postedVar('action', '', FALSE, FALSE, FALSE, 'g');

switch($action) {
    case 'fixftotals':
        // Update all forums using as few queries as possible.
        $sql = "UPDATE ".X_PREFIX."forums AS f "
             . " LEFT JOIN (SELECT fid, COUNT(tid) AS tcount FROM ".X_PREFIX."threads GROUP BY fid) AS query2 ON f.fid=query2.fid "
             . " LEFT JOIN (SELECT fid, COUNT(pid) AS pcount FROM ".X_PREFIX."posts GROUP BY fid) AS query3 ON f.fid=query3.fid "
             . "SET f.threads = IFNULL(query2.tcount, 0), f.posts = IFNULL(query3.pcount, 0) "
             . "WHERE f.type = 'sub'";
        $db->query($sql);

        $sql = "UPDATE ".X_PREFIX."forums AS f "
             . " LEFT JOIN (SELECT fup, SUM(threads) AS tcount, SUM(posts) AS pcount FROM ".X_PREFIX."forums GROUP BY fup) AS query2 ON f.fid=query2.fup "
             . " LEFT JOIN (SELECT fid, COUNT(tid) AS tcount FROM ".X_PREFIX."threads GROUP BY fid) AS query3 ON f.fid=query3.fid "
             . " LEFT JOIN (SELECT fid, COUNT(pid) AS pcount FROM ".X_PREFIX."posts GROUP BY fid) AS query4 ON f.fid=query4.fid "
             . "SET f.threads = IFNULL(query2.tcount, 0) + IFNULL(query3.tcount, 0), "
             . "    f.posts   = IFNULL(query2.pcount, 0) + IFNULL(query4.pcount, 0) "
             . "WHERE f.type = 'forum'";
        $db->query($sql);

        nav($lang['tools']);
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['tool_completed'].' - '.$lang['tool_forumtotal'].'</td></tr></table></table>';
        end_time();
        eval('echo "'.template('footer').'";');
        exit;
        break;

    case 'fixttotals':
        // Update all threads using as few queries as possible.
        $sql = "UPDATE ".X_PREFIX."threads AS t "
             . " INNER JOIN (SELECT tid, COUNT(pid) as pcount FROM ".X_PREFIX."posts GROUP BY tid) AS query2 USING (tid) "
             . "SET t.replies = query2.pcount - 1";
        $db->query($sql);

        nav($lang['tools']);
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['tool_completed'].' - '.$lang['tool_threadtotal'].'</td></tr></table></table>';
        end_time();
        eval('echo "'.template('footer').'";');
        exit;
        break;

    case 'fixmposts':
        // Update all members using as few queries as possible.
        $sql = "UPDATE ".X_PREFIX."members AS m "
             . " LEFT JOIN (SELECT author, COUNT(pid) as pcount FROM ".X_PREFIX."posts GROUP BY author) AS query2 ON m.username = query2.author "
             . "SET m.postnum = IFNULL(query2.pcount, 0)";
        $db->query($sql);

        nav($lang['tools']);
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['tool_completed'].' - '.$lang['tool_mempost'].'</td></tr></table></table>';
        end_time();
        eval('echo "'.template('footer').'";');
        exit;
        break;

    case 'fixlastposts':
        if (postedVar('scope', '', FALSE, FALSE, FALSE, 'g') == 'forumsonly') {
            // Update all forums using as few queries as possible
            $sql = 'SELECT f.fid, f.fup, f.type, f.lastpost, p.author, p.dateline, p.pid, log.username, log.date '
                 . 'FROM '.X_PREFIX.'forums AS f '
                 . 'LEFT JOIN ( '
                 . '    SELECT pid, p3.fid, author, dateline FROM '.X_PREFIX.'posts AS p3 '
                 . '    INNER JOIN ( '
                 . '        SELECT p2.fid, MAX(pid) AS lastpid '
                 . '        FROM '.X_PREFIX.'posts AS p2 '
                 . '        INNER JOIN ( '
                 . '            SELECT fid, MAX(dateline) AS lastdate '
                 . '            FROM '.X_PREFIX.'posts '
                 . '            GROUP BY fid '
                 . '        ) AS query3 ON p2.fid=query3.fid AND p2.dateline=query3.lastdate '
                 . '        GROUP BY p2.fid '
                 . '    ) AS query2 ON p3.pid=query2.lastpid '
                 . ') AS p ON f.fid=p.fid '
                 . 'LEFT JOIN ( /* Self-join order is critical with no unique key available */ '
                 . '    SELECT log2.fid, log2.date, log2.username '
                 . '    FROM '.X_PREFIX.'logs AS log2 '
                 . '    INNER JOIN ( '
                 . '        SELECT fid, MAX(`date`) AS lastdate '
                 . '        FROM '.X_PREFIX.'logs '
                 . '        WHERE `action` = "bump" '
                 . '        GROUP BY fid '
                 . '    ) AS query4 ON log2.fid=query4.fid AND log2.date=query4.lastdate '
                 . ') AS log ON f.fid=log.fid '
                 . 'WHERE f.type="forum" OR f.type="sub"';

            $q = $db->query($sql);

            // Structure results to accommodate a nested loop strategy.
            $forums_array = array();
            $subs_array = array();
            while ($row = $db->fetch_array($q)) {
                if ($row['type'] == 'forum') {
                    $forums_array[] = $row;
                } else {
                    $subs_array[] = $row;
                }
            }

            $db->free_result($q);

            // Loop through all forums
            foreach($forums_array as $loner) {
                $lastpost = array();

                // Loop through all sub-forums
                foreach($subs_array as $sub) {
                    if ($sub['fup'] == $loner['fid']) {
                        if ($sub['pid'] !== NULL) {
                            if ($sub['date'] !== NULL) {
                                if ($sub['date'] > $sub['dateline']) {
                                    $sub['dateline'] = $sub['date'];
                                    $sub['author'] = $sub['username'];
                                }
                            }
                            $lastpost[] = $sub;
                            $lp = $sub['dateline'].'|'.$sub['author'].'|'.$sub['pid'];
                        } else {
                            $lp = '';
                        }
                        if ($sub['lastpost'] != $lp) {
                            $db->escape_fast($lp);
                            $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$lp' WHERE fid={$sub['fid']}");
                        }
                    }
                }

                if ($loner['pid'] !== NULL) {
                    if ($loner['date'] !== NULL) {
                        if ($loner['date'] > $loner['dateline']) {
                            $loner['dateline'] = $loner['date'];
                            $loner['author'] = $loner['username'];
                        }
                    }
                    $lastpost[] = $loner;
                }

                if (count($lastpost) == 0) {
                    $lastpost = '';
                } else {
                    $top = 0;
                    $mkey = -1;
                    foreach($lastpost as $key => $v) {
                        if ($v['dateline'] > $top) {
                            $mkey = $key;
                            $top = $v['dateline'];
                        }
                    }
                    $lastpost = $lastpost[$mkey]['dateline'].'|'.$lastpost[$mkey]['author'].'|'.$lastpost[$mkey]['pid'];
                }
                $db->escape_fast($lastpost);
                $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$lastpost' WHERE fid='{$loner['fid']}'");
            }

        } else { // Update all threads using as few queries as possible
            $newsql = 'SELECT t.tid, t.lastpost, t.closed, p.author, p.dateline, p.pid, log.username, log.date '
                    . 'FROM '.X_PREFIX.'threads AS t '
                    . 'LEFT JOIN '.X_PREFIX.'posts AS p ON t.tid=p.tid '
                    . 'INNER JOIN ( '
                    . '    SELECT p2.tid, MAX(pid) AS lastpid '
                    . '    FROM '.X_PREFIX.'posts AS p2 '
                    . '    INNER JOIN ( '
                    . '        SELECT tid, MAX(dateline) AS lastdate '
                    . '        FROM '.X_PREFIX.'posts '
                    . '        GROUP BY tid '
                    . '    ) AS query3 ON p2.tid=query3.tid AND p2.dateline=query3.lastdate '
                    . '    GROUP BY p2.tid '
                    . ') AS query2 ON p.pid=query2.lastpid '
                    . 'LEFT JOIN ( /* Self-join order is critical with no unique key available */ '
                    . '    SELECT log2.tid, log2.date, log2.username '
                    . '    FROM '.X_PREFIX.'logs AS log2 '
                    . '    INNER JOIN ( '
                    . '        SELECT tid, MAX(`date`) AS lastdate '
                    . '        FROM '.X_PREFIX.'logs '
                    . '        WHERE `action` = "bump" '
                    . '        GROUP BY tid '
                    . '    ) AS query4 ON log2.tid=query4.tid AND log2.date=query4.lastdate '
                    . ') AS log ON t.tid=log.tid';

            $lpquery = $db->query($newsql);

            while($thread = $db->fetch_array($lpquery)) {
                if (!is_null($thread['pid'])) {
                    if ($thread['dateline'] == '0' And substr($thread['closed'], 0, 6) == 'moved|') {
                        // Handle situation where versions before 1.9.11 set posts.dateline=0 when redirecting threads.
                        $newtid = intval(substr($thread['closed'], 6));
                        $lastdate = $db->result($db->query("SELECT MAX(dateline) AS lastdate FROM ".X_PREFIX."posts WHERE tid=$newtid"), 0);
                        if (is_null($lastdate)) {
                            // Redirector is orphaned.  Set dateline to some non-zero value.
                            $db->query("UPDATE ".X_PREFIX."posts SET dateline=1 WHERE tid={$thread['tid']} AND dateline = 0");
                        } else {
                            $thread['dateline'] = $lastdate;
                            $db->query("UPDATE ".X_PREFIX."posts SET dateline=$lastdate WHERE tid={$thread['tid']} AND dateline = 0");
                        }
                    }
                    $lp = $thread['dateline'].'|'.$thread['author'].'|'.$thread['pid'];
                    if (!is_null($thread['date'])) {
                        if ($thread['date'] > $thread['dateline']) {
                            $lp = $thread['date'].'|'.$thread['username'].'|'.$thread['pid'];
                        }
                    }
                } else {
                    $lp = '';
                }

                if ($thread['lastpost'] != $lp) {
                    $db->escape_fast($lp);
                    $db->query("UPDATE ".X_PREFIX."threads SET lastpost='$lp' WHERE tid={$thread['tid']}");
                }
            }
            $db->free_result($lpquery);
        }

        nav($lang['tools']);
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['tool_completed'].' - '.$lang['tool_lastpost'].'</td></tr></table></table>';
        end_time();
        eval('echo "'.template('footer').'";');
        exit;
        break;

    case 'fixorphanedthreads':
        if (noSubmit('orphsubmit')) {
            echo '<form action="tools.php?action=fixorphanedthreads" method="post">';
            echo '<tr bgcolor="'.$altbg1.'" class="ctrtablerow"><td><input type="text" name="export_fid" size="4"/>&nbsp;'.$lang['export_fid_expl'].'</td></tr>';
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td><input class="submit" type="submit" name="orphsubmit" value="'.$lang['textsubmitchanges'].'" /></td></tr>';
            echo '</form>';
        } else {
            $export_fid = formInt('export_fid');
            $export_forum = getForum($export_fid);
            if ($export_forum['type'] != 'forum' And $export_forum['type'] != 'sub') {
                error($lang['export_fid_not_there'], false, '</table></table><br />');
            }

            $q = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE type='forum' OR type='sub'");
            while($f = $db->fetch_array($q)) {
                $fids[] = $f['fid'];
            }
            $db->free_result($q);

            $fq = "fid != '";
            $fids = implode("' AND fid != '", $fids);
            $fq .= $fids;
            $fq .= "'";

            $q = $db->query("SELECT tid FROM ".X_PREFIX."threads WHERE $fq");
            $i = 0;
            while($t = $db->fetch_array($q)) {
                $db->query("UPDATE ".X_PREFIX."threads SET fid='$export_fid' WHERE tid='$t[tid]'");
                $db->query("UPDATE ".X_PREFIX."posts SET fid='$export_fid' WHERE tid='$t[tid]'");
                $i++;
            }
            $db->free_result($q);

            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>';
            echo $i.$lang['o_threads_found'].'</td></tr>';
        }
        break;

    case 'fixorphanedposts':
        if (noSubmit('orphpostsubmit')) {
            echo '<form action="tools.php?action=fixorphanedposts" method="post">';
            echo '<tr bgcolor="'.$altbg1.'" class="ctrtablerow"><td><input type="text" name="export_tid" size="4"/>&nbsp;'.$lang['export_tid_expl'].'</td></tr>';
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td><input class="submit" type="submit" name="orphpostsubmit" value="'.$lang['textsubmitchanges'].'" /></td></tr>';
            echo '</form>';
        } else {
            // Validate Input
            $export_tid = formInt('export_tid');
            $query = $db->query("SELECT fid FROM ".X_PREFIX."threads WHERE tid=$export_tid");
            if ($db->num_rows($query) != 1) {
                error($lang['export_tid_not_there'], false, '</table></table><br />');
            }
            $row = $db->fetch_array($query);
            $export_fid = $row['fid'];
            $db->free_result($query);

            // Fix Invalid FIDs
            $db->query("UPDATE ".X_PREFIX."posts AS p INNER JOIN ".X_PREFIX."threads AS t USING (tid) "
                     . "SET p.fid = t.fid "
                     . "WHERE p.fid != t.fid");
            $i = $db->affected_rows();

            // Fix Invalid TIDs
            $db->query("UPDATE ".X_PREFIX."posts AS p LEFT JOIN ".X_PREFIX."threads AS t USING (tid) "
                     . "SET p.fid = $export_fid, p.tid = $export_tid "
                     . "WHERE t.tid IS NULL");
            $i += $db->affected_rows();

            updatethreadcount($export_tid);
            updateforumcount($export_fid);
            $forum = getForum($export_fid);
            if ($forum['type'] == 'sub') {
                updateforumcount($forum['fup']);
            }

            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>';
            echo $i.$lang['o_posts_found'].'</td></tr>';
        }
        break;

    case 'fixorphanedattachments':
        if (noSubmit('orphattachsubmit')) {
            echo '<form action="tools.php?action=fixorphanedattachments" method="post">';
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>';
            echo '<input type="submit" name="orphattachsubmit" value="'.$lang['o_attach_submit'].'" /></td></tr>';
            echo '</form>';
        } else {
            require('include/attach-admin.inc.php');
            $i = deleteOrphans();

            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>';
            echo $i.$lang['o_attachments_found'].'</td></tr>';
        }
        break;

    case 'fixorphanedpolls':
        if (noSubmit('orphpollsubmit')) {
            echo '<form action="tools.php?action=fixorphanedpolls" method="post">';
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>';
            echo '<input type="submit" name="orphpollsubmit" value="'.$lang['o_poll_submit'].'" /></td></tr>';
            echo '</form>';
        } else {
            $q = $db->query("SELECT topic_id "
                          . "FROM ".X_PREFIX."vote_desc AS v "
                          . "LEFT JOIN ".X_PREFIX."threads AS t ON t.tid=v.topic_id "
                          . "WHERE t.tid IS NULL");
            $i = $db->num_rows($q);
            if ($i > 0) {
                $tids = array();
                while($row = $db->fetch_array($q)) {
                    $tids[] = $row['topic_id'];
                }
                $tids = implode(', ', $tids);

                // Important: Do not alias tables in multi-table delete queries as long as MySQL 4.0 is supported.
                $db->query("DELETE FROM ".X_PREFIX."vote_desc, ".X_PREFIX."vote_results, ".X_PREFIX."vote_voters "
                         . "USING ".X_PREFIX."vote_desc "
                         . "LEFT JOIN ".X_PREFIX."vote_results ON ".X_PREFIX."vote_results.vote_id = ".X_PREFIX."vote_desc.vote_id "
                         . "LEFT JOIN ".X_PREFIX."vote_voters  ON ".X_PREFIX."vote_voters.vote_id  = ".X_PREFIX."vote_desc.vote_id "
                         . "WHERE ".X_PREFIX."vote_desc.topic_id IN ($tids)");

            }

            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>';
            echo $i.$lang['o_polls_found'].'</td></tr>';
        }
        break;

    case 'updatemoods':
        $db->query("UPDATE ".X_PREFIX."members SET mood='$lang[nomoodtext]' WHERE mood=''");
        nav($lang['tools']);
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['tool_completed'].' - '.$lang['tool_mood'].'</td></tr></table></table>';
        end_time();
        eval('echo "'.template('footer').'";');
        exit;
        break;

    case 'u2udump':
        if (noSubmit('yessubmit')) {
            ?>
            <tr bgcolor="<?php echo $altbg2; ?>" class="ctrtablerow"><td><?php echo $lang['u2udump_confirm']; ?><br />
            <form action="tools.php?action=u2udump" method="post">
              <input type="hidden" name="token" value="<?php echo nonce_create('truncateu2us'); ?>" />
              <input type="submit" name="yessubmit" value="<?php echo $lang['textyes']; ?>" /> -
              <input type="submit" name="yessubmit" value="<?php echo $lang['textno']; ?>" />
            </form></td></tr>
            <?php
        } else if ($lang['textyes'] == $yessubmit) {
            request_secure('truncateu2us', '', X_NONCE_AYS_EXP, FALSE);
            $db->query("TRUNCATE ".X_PREFIX."u2u");
            nav($lang['tools']);
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['tool_completed'].' - '.$lang['tool_u2u'].'</td></tr></table></table>';
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        } else {
            redirect($full_url.'cp.php', 0);
        }
        break;

    case 'whosonlinedump':
        if (noSubmit('yessubmit')) {
            ?>
            <tr bgcolor="<?php echo $altbg2; ?>" class="ctrtablerow"><td><?php echo $lang['whoodump_confirm']; ?><br />
            <form action="tools.php?action=whosonlinedump" method="post">
              <input type="hidden" name="token" value="<?php echo nonce_create('truncatewhos'); ?>" />
              <input type="submit" name="yessubmit" value="<?php echo $lang['textyes']; ?>" /> -
              <input type="submit" name="yessubmit" value="<?php echo $lang['textno']; ?>" />
            </form></td></tr>
            <?php
        } else if ($lang['textyes'] == $yessubmit) {
            request_secure('truncatewhos', '', X_NONCE_AYS_EXP, FALSE);
            $db->query("TRUNCATE ".X_PREFIX."whosonline");
            nav($lang['tools']);
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['tool_completed'].' - '.$lang['tool_whosonline'].'</td></tr></table></table>';
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        } else {
            redirect($full_url.'cp.php', 0);
        }
        break;

    case 'logsdump':
        if (!X_SADMIN) {
            error($lang['superadminonly'], false, '</td></tr></table></td></tr></table><br />');
        }

        if (noSubmit('yessubmit')) {
            ?>
            <tr bgcolor="<?php echo $altbg2; ?>" class="ctrtablerow"><td><?php echo $lang['logsdump_confirm']; ?><br />
            <form action="tools.php?action=logsdump" method="post">
              <input type="hidden" name="token" value="<?php echo nonce_create('deletecplogs'); ?>" />
              <input type="submit" name="yessubmit" value="<?php echo $lang['textyes']; ?>" /> -
              <input type="submit" name="yessubmit" value="<?php echo $lang['textno']; ?>" />
            </form></td></tr>
            <?php
        } else if ($lang['textyes'] == $yessubmit) {
            request_secure('deletecplogs', '', X_NONCE_AYS_EXP, FALSE);
            $db->query("DELETE FROM ".X_PREFIX."logs WHERE fid=0");
            nav($lang['tools']);
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['tool_completed'].' - '.$lang['tool_logs'].'</td></tr></table></table>';
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        } else {
            redirect($full_url.'cp.php', 0);
        }
        break;

    case 'repairtables':
        $start = TRUE;
        @set_time_limit(180);
        foreach($tables as $val) {
            dump_query($db->query('REPAIR TABLE `'.X_PREFIX.$val.'`'), $start);
            $start = FALSE;
        }
        break;

    case 'optimizetables':
        $start = TRUE;
        @set_time_limit(180);
        foreach($tables as $val) {
            dump_query($db->query('OPTIMIZE TABLE `'.X_PREFIX.$val.'`'), $start);
            $start = FALSE;
        }
        break;

    case 'analyzetables':
        $start = TRUE;
        @set_time_limit(180);
        foreach($tables as $val) {
            dump_query($db->query('ANALYZE TABLE `'.X_PREFIX.$val.'`'), $start);
            $start = FALSE;
        }
        break;

    case 'checktables':
        $start = TRUE;
        @set_time_limit(180);
        foreach($tables as $val) {
            dump_query($db->query('CHECK TABLE `'.X_PREFIX.$val.'`'), $start);
            $start = FALSE;
        }
        break;
}

echo '</td></tr></table></table>';
end_time();
eval('echo "'.template('footer').'";');
?>
