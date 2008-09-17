<?php
/**
 * XMB 1.9.5 Nexus Final SP1
 * © 2007 John Briggs
 * http://www.xmbmods.com
 * john@xmbmods.com
 *
 * Developed By The XMB Group
 * Copyright (c) 2001-2007, The XMB Group
 * http://www.xmbforum.com
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 **/

require_once('header.php');
require_once('./include/admin.user.inc.php');

loadtemplates('error_nologinsession');
eval("\$css = \"".template("css")."\";");

nav('<a href="cp.php">'.$lang['textcp'].'</a>');
eval("echo (\"".template('header')."\");");
echo '<script language="JavaScript" type="text/javascript" src="./include/admin.js"></script>';

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

switch ($action) {
    case 'fixftotals':
        $fquery = $db->query("SELECT fid FROM $table_forums WHERE type='forum'");
        while ($forum = $db->fetch_array($fquery)) {
            $threadnum = $postnum = $sub_threadnum = $sub_postnum = 0;
            $squery = $stquery = $spquery = $ftquery = $fpquery = '';

            $squery = $db->query("SELECT fid FROM $table_forums WHERE fup='$forum[fid]' AND type='sub'");
            while ($sub = $db->fetch_array($squery)) {
                $stquery = $db->query("SELECT COUNT(tid) FROM $table_threads WHERE fid='$sub[fid]'");
                $sub_threadnum = $db->result($stquery, 0);

                $spquery = $db->query("SELECT COUNT(pid) FROM $table_posts WHERE fid='$sub[fid]'");
                $sub_postnum = $db->result($spquery, 0);

                $db->query("UPDATE $table_forums SET threads='$sub_threadnum', posts='$sub_postnum' WHERE fid='$sub[fid]'");
                $threadnum += $sub_threadnum;
                $postnum += $sub_postnum;
            }
            $db->free_result($squery);

            $ftquery = $db->query("SELECT COUNT(tid) FROM $table_threads WHERE fid='$forum[fid]'");
            $threadnum += $db->result($ftquery, 0);

            $fpquery = $db->query("SELECT COUNT(pid) FROM $table_posts WHERE fid='$forum[fid]'");
            $postnum += $db->result($fpquery, 0);

            $db->query("UPDATE $table_forums SET threads='$threadnum', posts='$postnum' WHERE fid='$forum[fid]'");
        }
        nav($lang['tools']);
        echo "<tr bgcolor=\"$altbg2\" class=\"ctrtablerow\"><td>$lang[tool_completed] $lang[tool_forumtotal]</td></tr></table></table>";
        end_time();
        eval('echo "'.template('footer').'";');
        exit;
        break;

    case 'fixttotals':
        $queryt = $db->query("SELECT * FROM $table_threads");
        while ($threads = $db->fetch_array($queryt)) {
            $query = $db->query("SELECT COUNT(pid) FROM $table_posts WHERE tid='$threads[tid]'");
            $replynum = $db->result($query, 0) -1;
            $db->query("UPDATE $table_threads SET replies='$replynum' WHERE tid='$threads[tid]'");
        }
        nav($lang['tools']);
        echo "<tr bgcolor=\"$altbg2\" class=\"ctrtablerow\"><td>$lang[tool_completed] $lang[tool_threadtotal]</td></tr></table></table>";
        end_time();
        eval('echo "'.template('footer').'";');
        exit;
        break;

    case 'fixmposts':
        $queryt = $db->query("SELECT username FROM $table_members");
        while ($mem = $db->fetch_array($queryt)) {
            $mem['username'] = addslashes(stripslashes($mem['username']));
            $query = $db->query("SELECT COUNT(pid) FROM $table_posts WHERE author='$mem[username]'");
            $postsnum = $db->result($query, 0);
            $db->query("UPDATE $table_members SET postnum='$postsnum' WHERE username='$mem[username]'");
        }
        nav($lang['tools']);
        echo "<tr bgcolor=\"$altbg2\" class=\"ctrtablerow\"><td>$lang[tool_completed] - $lang[tool_mempost]</td></tr></table></table>";
        end_time();
        eval('echo "'.template('footer').'";');
        exit;
        break;

    case 'fixlastposts':
        $q = $db->query("SELECT fid FROM $table_forums WHERE (fup = '0' OR fup = '') AND type = 'forum'");
        while ($loner = $db->fetch_array($q)) {
            $lastpost = array();
            $subq = $db->query("SELECT fid FROM $table_forums WHERE fup = '$loner[fid]'");
            while ($sub = $db->fetch_array($subq)) {
                $pq = $db->query("SELECT author, dateline, pid FROM $table_posts WHERE fid = '$sub[fid]' ORDER BY pid DESC LIMIT 1");
                if ($db->num_rows($pq) > 0) {
                    $curr = $db->fetch_array($pq);
                    $lastpost[] = $curr;
                    $lp = $curr['dateline'].'|'.$curr['author'].'|'.$curr['pid'];
                } else {
                    $lp = '';
                }
                $db->query("UPDATE $table_forums SET lastpost = '$lp' WHERE fid = '$sub[fid]'");
                $db->free_result($pq);
            }
            $db->free_result($subq);
            $pq = $db->query("SELECT author, dateline, pid FROM $table_posts WHERE fid = '$loner[fid]' ORDER BY pid DESC LIMIT 1");
            if ($db->num_rows($pq) > 0) {
                $lastpost[] = $db->fetch_array($pq);
            }
            $db->free_result($pq);
            if (count($lastpost) == 0) {
                $lastpost = '';
            } else {
                $top = 0;
                $mkey = -1;
                foreach ($lastpost as $key => $v) {
                    if ($v['dateline'] > $top) {
                        $mkey = $key;
                        $top = $v['dateline'];
                    }
                }
                $lastpost = $lastpost[$mkey]['dateline'].'|'.$lastpost[$mkey]['author'].'|'.$lastpost[$mkey]['pid'];
            }
            $db->query("UPDATE $table_forums SET lastpost = '$lastpost' WHERE fid = '$loner[fid]'");
        }
        $db->free_result($q);
        $q = $db->query("SELECT fid FROM $table_forums WHERE type = 'group'");
        while ($cat = $db->fetch_array($q)) {
            $fq = $db->query("SELECT fid FROM $table_forums WHERE type = 'forum' AND fup = '$cat[fid]'");
            while ($forum = $db->fetch_array($fq)) {
                $lastpost = array();
                $subq = $db->query("SELECT fid FROM $table_forums WHERE fup = '$forum[fid]'");
                while ($sub = $db->fetch_array($subq)) {
                    $pq = $db->query("SELECT author, dateline, pid FROM $table_posts WHERE fid = '$sub[fid]' ORDER BY pid DESC LIMIT 1");
                    if ($db->num_rows($pq) > 0) {
                        $curr = $db->fetch_array($pq);
                        $lastpost[] = $curr;
                        $lp = $curr['dateline'].'|'.$curr['author'].'|'.$curr['pid'];
                    } else {
                        $lp = '';
                    }
                    $db->free_result($pq);
                    $db->query("UPDATE $table_forums SET lastpost = '$lp' WHERE fid = '$sub[fid]'");
                }
                $db->free_result($subq);
                $pq = $db->query("SELECT author, dateline, pid FROM $table_posts WHERE fid = '$forum[fid]' ORDER BY pid DESC LIMIT 1");
                if ($db->num_rows($pq) > 0) {
                    $lastpost[] = $db->fetch_array($pq);
                }
                $db->free_result($pq);
                if (count($lastpost) == 0) {
                    $lastpost = '';
                } else {
                    $top = 0;
                    $mkey = -1;
                    foreach ($lastpost as $key => $v) {
                        if ($v['dateline'] > $top) {
                            $mkey = $key;
                            $top = $v['dateline'];
                        }
                    }
                    $lastpost = $lastpost[$mkey]['dateline'].'|'.$lastpost[$mkey]['author'].'|'.$lastpost[$mkey]['pid'];
                }
                $db->query("UPDATE $table_forums SET lastpost = '$lastpost' WHERE fid = '$forum[fid]'");
            }
            $db->free_result($fq);
        }
        $db->free_result($q);
        $q = $db->query("SELECT tid FROM $table_threads");
        while ($thread = $db->fetch_array($q)) {
            $lastpost = array();
            $pq = $db->query("SELECT author, dateline, pid FROM $table_posts WHERE tid = '$thread[tid]' ORDER BY pid DESC LIMIT 1");
            if ($db->num_rows($pq) > 0) {
                $curr = $db->fetch_array($pq);
                $lastpost[] = $curr;
                $lp = $curr['dateline'].'|'.$curr['author'].'|'.$curr['pid'];
            } else {
                $lp = '';
            }
            $db->free_result($pq);
            $db->query("UPDATE $table_threads SET lastpost = '$lp' WHERE tid = '$thread[tid]'");
        }
        $db->free_result($q);
        nav($lang['tools']);
        echo "<tr bgcolor=\"$altbg2\" class=\"ctrtablerow\"><td>$lang[tool_completed] - $lang[tool_lastpost]</td></tr></table></table>";
        end_time();
        eval('echo "'.template('footer').'";');
        exit;
        break;

    case 'updatemoods':
        $db->query("UPDATE $table_members SET mood='$lang[nomoodtext]' WHERE mood=''");
        nav($lang['tools']);
        echo "<tr bgcolor=\"$altbg2\" class=\"ctrtablerow\"><td>$lang[tool_completed] - $lang[tool_mood]</td></tr></table></table>";
        end_time();
        eval('echo "'.template('footer').'";');
        exit;
        break;

    case 'u2udump':
        if (!isset($_POST['yessubmit'])) {
            echo "<tr bgcolor=\"$altbg2\" class=\"ctrtablerow\"><td>".$lang['u2udump_confirm']."<br /><form action=\"tools.php?action=u2udump\" method=\"post\"><input type=\"submit\" name=\"yessubmit\" value=\"".$lang['textyes']."\" /> - <input type=\"submit\" name=\"yessubmit\" value=\"".$lang['textno']."\" /></form></td></tr>";
        } elseif ($lang['textyes'] == $yessubmit) {
            $db->query("DELETE FROM $table_u2u");
            nav($lang['tools']);
            echo "<tr bgcolor=\"$altbg2\" class=\"ctrtablerow\"><td>$lang[tool_completed] - $lang[tool_u2u]</td></tr></table></table>";
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        } else {
            redirect('./cp.php', 0);
        }
        break;

    case 'whosonlinedump':
        if (!isset($_POST['yessubmit'])) {
            echo "<tr bgcolor=\"$altbg2\" class=\"ctrtablerow\"><td>".$lang['whoodump_confirm']."<br /><form action=\"tools.php?action=whosonlinedump\" method=\"post\"><input type=\"submit\" name=\"yessubmit\" value=\"".$lang['textyes']."\" /> - <input type=\"submit\" name=\"yessubmit\" value=\"".$lang['textno']."\" /></form></td></tr>";
        } elseif ($lang['textyes'] == $yessubmit) {
            $db->query("DELETE FROM $table_whosonline");
            nav($lang['tools']);
            echo "<tr bgcolor=\"$altbg2\" class=\"ctrtablerow\"><td>$lang[tool_completed] - $lang[tool_whosonline]</td></tr></table></table>";
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        } else {
            redirect('./cp.php', 0);
        }
        break;

    case 'fixorphanedthreads':
        if (!isset($_POST['orphsubmit'])) {
            echo '<tr bgcolor="'.$altbg2.'" class="tablerow"><td>';
            echo '<form action="tools.php?action=fixorphanedthreads" method="post">';
            echo '<input type="text" name="export_fid" size="4"/> '.$lang['export_fid_expl'];
            echo '<br /><input type="submit" name="orphsubmit" />';
            echo '</form>';
        } else {
            if (!isset($export_fid)) {
                error($lang['export_fid_not_there'], false, '</table></table><br />');
            }

            $q = $db->query("SELECT fid FROM $table_forums WHERE type = 'forum' OR type='sub'");
            while ($f = $db->fetch_array($q)) {
                $fids[] = $f['fid'];
            }
            $fq = "fid != '";
            $fids = implode("' AND fid != '", $fids);
            $fq .= $fids;
            $fq .= "'";

            $q = $db->query("SELECT tid FROM $table_threads WHERE $fq");
            $i = 0;
            while ($t = $db->fetch_array($q)) {
                $db->query("UPDATE $table_threads SET fid='$export_fid' WHERE tid='$t[tid]'");
                $db->query("UPDATE $table_posts SET fid='$export_fid' WHERE tid='$t[tid]'");
                $i++;
            }
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>';
            echo $i.$lang['o_threads_found'];
        }
        break;

    case 'repairtables':
        $start = true;
        @set_time_limit(180);
        $tables = $db->fetch_tables($dbname);
        $q = array();
        foreach ($tables as $key=>$val) {
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
        foreach ($tables as $key=>$val) {
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
        foreach ($tables as $key=>$val) {
            if ($start) {
                dump_query($db->query('ANALYZE TABLE `'.$val.'`'));
                $start = false;
            } else {
                dump_query($db->query('ANALYZE TABLE `'.$val.'`'), false);
            }
        }
        break;

    case 'fixorphanedattachments':
        if (!isset($_POST['orphattachsubmit'])) {
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>';
            echo '<form action="tools.php?action=fixorphanedattachments" method="post">';
            echo '<input type="submit" name="orphattachsubmit" value="'.$lang['o_attach_submit'].'" />';
            echo '</form>';
        } else {
            $i = 0;
            $q = $db->query("SELECT aid, pid FROM $table_attachments");
            while ($a = $db->fetch_array($q)) {
                $result = $db->query("SELECT pid FROM $table_posts WHERE pid='$a[pid]'");
                if ($db->num_rows($result) == 0) {
                    $db->query("DELETE FROM $table_attachments WHERE aid='$a[aid]'");
                    $i++;
                }
            }
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>';
            echo $i . $lang['o_attachments_found'];
        }
        break;
}

echo '</td></tr></table></table>';
end_time();
eval('echo "'.template('footer').'";');
?>