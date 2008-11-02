<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Alpha Two - This software should not be used for any purpose after 30 November 2008.
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

define('X_SCRIPT', 'tools.php');

require 'header.php';
require ROOT.'include/admin.inc.php';

loadtemplates('error_nologinsession');
eval('$css = "'.template('css').'";');

nav('<a href="cp.php">'.$lang['textcp'].'</a>');
eval('echo ("'.template('header').'");');
echo '<script language="JavaScript" type="text/javascript" src="./js/admin.js"></script>';

if (!X_ADMIN) {
    eval('echo stripslashes("'.template('error_nologinsession').'");');
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
             . " INNER JOIN (SELECT fid, COUNT(tid) AS tcount FROM ".X_PREFIX."threads GROUP BY fid) AS query2 ON f.fid=query2.fid "
             . " INNER JOIN (SELECT fid, COUNT(pid) AS pcount FROM ".X_PREFIX."posts GROUP BY fid) AS query3 ON f.fid=query3.fid "
             . "SET f.threads = query2.tcount, f.posts = query3.pcount "
             . "WHERE f.type = 'sub'";
        $db->query($sql);

        $sql = "UPDATE ".X_PREFIX."forums AS f "
             . " INNER JOIN (SELECT fup, SUM(threads) AS tcount, SUM(posts) AS pcount FROM ".X_PREFIX."forums GROUP BY fup) AS query2 ON f.fid=query2.fup "
             . " INNER JOIN (SELECT fid, COUNT(tid) AS tcount FROM ".X_PREFIX."threads GROUP BY fid) AS query3 ON f.fid=query3.fid "
             . " INNER JOIN (SELECT fid, COUNT(pid) AS pcount FROM ".X_PREFIX."posts GROUP BY fid) AS query4 ON f.fid=query4.fid "
             . "SET f.threads = query2.tcount + query3.tcount, f.posts = query2.pcount + query4.pcount "
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
             . " INNER JOIN (SELECT author, COUNT(pid) as pcount FROM ".X_PREFIX."posts GROUP BY author) AS query2 ON m.username = query2.author "
             . "SET m.postnum = query2.pcount";
        $db->query($sql);

        nav($lang['tools']);
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['tool_completed'].' - '.$lang['tool_mempost'].'</td></tr></table></table>';
        end_time();
        eval('echo "'.template('footer').'";');
        exit;
        break;

    case 'fixlastposts':
        if (postedVar('scope', '', FALSE, FALSE, FALSE, 'g') == 'forumsonly') {
            $q = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE type='forum'");
            while($loner = $db->fetch_array($q)) {
                $lastpost = array();
                $subq = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE fup='$loner[fid]'");
                while($sub = $db->fetch_array($subq)) {
                    $pq = $db->query("SELECT author, dateline, pid FROM ".X_PREFIX."posts WHERE fid='$sub[fid]' ORDER BY dateline DESC LIMIT 1");
                    if ($db->num_rows($pq) > 0) {
                        $curr = $db->fetch_array($pq);
                        $lastpost[] = $curr;
                        $lp = $curr['dateline'].'|'.$curr['author'].'|'.$curr['pid'];
                    } else {
                        $lp = '';
                    }
                    $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$lp' WHERE fid='$sub[fid]'");
                    $db->free_result($pq);
                }
                $db->free_result($subq);

                $pq = $db->query("SELECT author, dateline, pid FROM ".X_PREFIX."posts WHERE fid='$loner[fid]' ORDER BY dateline DESC LIMIT 1");
                if ($db->num_rows($pq) > 0) {
                    $lastpost[] = $db->fetch_array($pq);
                }
                $db->free_result($pq);

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
                $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$lastpost' WHERE fid='$loner[fid]'");
            }
            $db->free_result($q);

        } else { // Update all threads using as few queries as possible
            $newsql = 'SELECT t.tid, t.lastpost, p.author, p.dateline, p.pid '
                    . 'FROM '.X_PREFIX.'threads AS t '
                    . 'LEFT JOIN '.X_PREFIX.'posts AS p ON t.tid=p.tid '
                    . 'INNER JOIN ('
                    . '    SELECT MAX(pid) AS lastpid '
                    . '    FROM '.X_PREFIX.'posts AS p2'
                    . '    INNER JOIN ('
                    . '        SELECT tid, MAX(dateline) AS lastdate '
                    . '        FROM '.X_PREFIX.'posts '
                    . '        GROUP BY tid'
                    . '    ) AS query3 ON p2.tid=query3.tid AND p2.dateline=query3.lastdate '
                    . '    GROUP BY p2.tid, p2.dateline'
                    . ') AS query2 ON p.pid=query2.lastpid';

            $lpquery = $db->query($newsql);

            while($thread = $db->fetch_array($lpquery)) {
                if ($thread['pid'] !== NULL) {
                    $lp = $thread['dateline'].'|'.$thread['author'].'|'.$thread['pid'];
                } else {
                    $lp = '';
                }

                if ($thread['lastpost'] != $lp) {
                    $lp = $db->escape($lp);
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
            if (!$export_fid) {
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
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['u2udump_confirm'].'<br /><form action="tools.php?action=u2udump" method="post"><input type="submit" name="yessubmit" value="'.$lang['textyes'].'" /> - <input type="submit" name="yessubmit" value="'.$lang['textno'].'" /></form></td></tr>';
        } else if ($lang['textyes'] == $yessubmit) {
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
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['whoodump_confirm'].'<br /><form action="tools.php?action=whosonlinedump" method="post"><input type="submit" name="yessubmit" value="'.$lang['textyes'].'" /> - <input type="submit" name="yessubmit" value="'.$lang['textno'].'" /></form></td></tr>';
        } else if ($lang['textyes'] == $yessubmit) {
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
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['logsdump_confirm'].'<br /><form action="tools.php?action=logsdump" method="post"><input type="submit" name="yessubmit" value="'.$lang['textyes'].'" /> - <input type="submit" name="yessubmit" value="'.$lang['textno'].'" /></form></td></tr>';
        } else if ($lang['textyes'] == $yessubmit) {
            $db->query("TRUNCATE ".X_PREFIX."logs");
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
        $start = true;
        @set_time_limit(180);
        $tables = $db->fetch_tables($dbname);
        $q = array();
        foreach($tables as $key=>$val) {
            if ($start) {
                dump_query($db->query('REPAIR TABLE `'.$val.'`'));
                $start = false;
            } else {
                dump_query($db->query('REPAIR TABLE `'.$val.'`'), false);
            }
        }
        break;

    case 'optimizetables':
        $start = true;
        @set_time_limit(180);
        $tables = $db->fetch_tables($dbname);
        $q = array();
        foreach($tables as $key=>$val) {
            if ($start) {
                dump_query($db->query('OPTIMIZE TABLE `'.$val.'`'));
                $start = false;
            } else {
                dump_query($db->query('OPTIMIZE TABLE `'.$val.'`'), false);
            }
        }
        break;

    case 'analyzetables':
        $start = true;
        @set_time_limit(180);
        $tables = $db->fetch_tables($dbname);
        $q = array();
        foreach($tables as $key=>$val) {
            if ($start) {
                dump_query($db->query('ANALYZE TABLE `'.$val.'`'));
                $start = false;
            } else {
                dump_query($db->query('ANALYZE TABLE `'.$val.'`'), false);
            }
        }
        break;

    case 'checktables':
        $start = true;
        @set_time_limit(180);
        $tables = $db->fetch_tables($dbname);
        $q = array();
        foreach($tables as $key=>$val) {
            if ($start) {
                dump_query($db->query('CHECK TABLE `'.$val.'`'));
                $start = false;
            } else {
                dump_query($db->query('CHECK TABLE `'.$val.'`'), false);
            }
        }
        break;
}

echo '</td></tr></table></table>';
end_time();
eval('echo "'.template('footer').'";');
?>
