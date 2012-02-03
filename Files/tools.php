<?php
/* $Id: tools.php,v 1.21.2.9 2004/09/24 19:10:32 Tularis Exp $ */
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

require "./header.php";
loadtemplates('footer_load','footer_querynum','footer_phpsql','footer_totaltime','header','footer','css','error_nologinsession');
eval("\$css = \"".template("css")."\";");

nav('<a href="cp.php">'.$lang['textcp'].'</a>');
eval("echo (\"".template('header')."\");");

if (!$xmbuser || !$xmbpw) {
    $xmbuser = "";
    $xmbpw = "";
    $self['status'] = "";
}

if (!X_ADMIN) {
    eval('echo stripslashes("'.template('error_nologinsession').'");');
    end_time();
    eval("echo (\"".template('footer')."\");");
    exit();
}

$auditaction = $_SERVER['REQUEST_URI'];
$aapos = strpos($auditaction, "?");
if ($aapos !== false) {
    $auditaction = substr($auditaction, $aapos + 1);
}

$auditaction = addslashes("$onlineip|#|$auditaction");
audit($xmbuser, $auditaction, 0, 0);

?>

<!-- Admin Panel design kindly donated by John Briggs begin -->
<table cellspacing="0" cellpadding="0" border="0" width="<?php echo $tablewidth?>" align="center">
<tr>
<td bgcolor="<?php echo $bordercolor?>">
<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
<tr class="category">
<td colspan="30" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textcp']?></font></strong></td>
</tr>

<tr bgcolor="<?php echo $altbg1?>" class="tablerow">
<td colspan="30" align="center">
<br />
<table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
<tr>
<td bgcolor="<?php echo $bordercolor?>">
<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">

<tr class="category">
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['general']?></font></strong></td>
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textforums']?></font></strong></td>
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textmembers']?></font></strong></td>
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['look_feel']?></font></strong></td>
</tr>

<tr>
<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="cp2.php?action=attachments"><?php echo $lang['textattachman']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=censor"><?php echo $lang['textcensors']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=newsletter"><?php echo $lang['textnewsletter']?></a><br />
&raquo;&nbsp;<a href="cp.php?action=search"><?php echo $lang['cpsearch']?></a><br />
&raquo;&nbsp;<a href="cp.php?action=settings"><?php echo $lang['textsettings']?></a><br />
</td>

<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="cp.php?action=forum"><?php echo $lang['textforums']?></a><br />
&raquo;&nbsp;<a href="cp.php?action=mods"><?php echo $lang['textmods']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=prune"><?php echo $lang['textprune']?></a><br />
</td>

<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="cp.php?action=ipban"><?php echo $lang['textipban']?></a><br />
&raquo;&nbsp;<a href="cp.php?action=members"><?php echo $lang['textmembers']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=ranks"><?php echo $lang['textuserranks']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=restrictions"><?php echo $lang['cprestricted']?></a><br />
&raquo;&nbsp;<a href="cp.php?action=rename"><?php echo $lang['admin_rename_txt']?></a><br />
</td>

<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="cp2.php?action=smilies"><?php echo $lang['smilies']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=templates"><?php echo $lang['templates']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=themes"><?php echo $lang['themes']?></a><br />
</td>
</tr>

<tr class="category">
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['logs']?></font></strong></td>
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['tools']?></font></strong></td>
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['mysql_tools']?></font></strong></td>
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['backup_tools']?></font></strong></td>
</tr>

<tr>
<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="cp2.php?action=modlog"><?php echo $lang['textmodlogs']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=cplog"><?php echo $lang['textcplogs']?></a>
</td>

<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="tools.php?action=fixftotals"><?php echo $lang['textfixposts']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=fixlastposts"><?php echo $lang['textfixlastposts']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=fixmposts"><?php echo $lang['textfixmemposts']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=fixttotals"><?php echo $lang['textfixthread']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=updatemoods"><?php echo $lang['textfixmoods']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=fixorphanedthreads"><?php echo $lang['textfixothreads']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=fixorphanedattachments"><?php echo $lang['textfixoattachments']?></a><br />
</td>

<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="tools.php?action=analyzetables"><?php echo $lang['analyze']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=whosonlinedump"><?php echo $lang['cpwodump']?></a><br />
&raquo;&nbsp;<a href="cp.php?action=upgrade"><?php echo $lang['raw_mysql']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=optimizetables"><?php echo $lang['optimize']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=repairtables"><?php echo $lang['repair']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=u2udump"><?php echo $lang['u2udump']?></a><br />
</td>

<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="cp2.php?action=dbdump"><?php echo $lang['db_backup']?></a><br />
&raquo;&nbsp;<a href="dump_attachments.php?action=dump_attachments"><?php echo $lang['dump_attachments']?></a><br />
&raquo;&nbsp;<a href="dump_attachments.php?action=restore_attachments"><?php echo $lang['restore_attachments']?></a><br />
</td>
</tr>
</table>
</td>
</tr>
</table>
<br />
<!-- Admin Panel design kindly donated by John Briggs end -->

<?php

switch ($action) {
    case 'fixftotals':
        $fquery = $db->query("SELECT fid FROM $table_forums WHERE type='forum'");
        while ($forum = $db->fetch_array($fquery)) {
            $threadnum    = 0;
            $postnum    = 0;
            $sub_threadnum    = 0;
            $sub_postnum    = 0;
            $squery        = '';
            $stquery    = '';
            $spquery    = '';
            $ftquery    = '';
            $fpquery    = '';

            // Get all posts and threads from the subforums
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

            // Get all threads and posts for the forum itself
            $ftquery = $db->query("SELECT COUNT(tid) FROM $table_threads WHERE fid='$forum[fid]'");
            $threadnum += $db->result($ftquery, 0);

            $fpquery = $db->query("SELECT COUNT(pid) FROM $table_posts WHERE fid='$forum[fid]'");
            $postnum += $db->result($fpquery, 0);

            // Update it all
            $db->query("UPDATE $table_forums SET threads='$threadnum', posts='$postnum' WHERE fid='$forum[fid]'");
        }

        nav($lang['tools']);
        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[tool_completed] $lang[tool_forumtotal]</td></tr></table></table>";
        end_time();
        eval("echo (\"".template('footer')."\");");
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

        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[tool_completed] $lang[tool_threadtotal]</td></tr></table></table>";
        end_time();
        eval("echo (\"".template('footer')."\");");
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
        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[tool_completed] - $lang[tool_mempost]</td></tr></table></table>";
        end_time();
        eval("echo (\"".template('footer')."\");");
        exit;
        break;

    case 'fixlastposts':
        $q = $db->query("SELECT fid FROM $table_forums WHERE (fup='0' OR fup='') AND type='forum'");
        while ($loner = $db->fetch_array($q)) {
            $lastpost = array();
            $subq = $db->query("SELECT fid FROM $table_forums WHERE fup='$loner[fid]'");
            while ($sub = $db->fetch_array($subq)) {
                $pq = $db->query("SELECT author, dateline FROM $table_posts WHERE fid='$sub[fid]' ORDER BY pid DESC LIMIT 1");
                if ($db->num_rows($pq) > 0) {
                    $curr = $db->fetch_array($pq);
                    $lastpost[] = $curr;
                    $lp = $curr['dateline'].'|'.$curr['author'];
                } else {
                    $lp = '';
                }
                $db->query("UPDATE $table_forums SET lastpost='$lp' WHERE fid='$sub[fid]'");
            }
            $pq = $db->query("SELECT author, dateline FROM $table_posts WHERE fid='$loner[fid]' ORDER BY pid DESC LIMIT 1");
            if ($db->num_rows($pq) > 0) {
                $lastpost[] = $db->fetch_array($pq);
            }

            if (count($lastpost) == 0) {
                $lastpost = '';
            } else {
                $top = 0;
                $mkey = -1;
                foreach ($lastpost as $key=>$v) {
                    if ($v['dateline'] > $top) {
                        $mkey = $key;
                        $top = $v['dateline'];
                    }
                }
                $lastpost = $lastpost[$mkey]['dateline'].'|'.$lastpost[$mkey]['author'];
            }
            $db->query("UPDATE $table_forums SET lastpost='$lastpost' WHERE fid = '$loner[fid]'");
        }

        // now to go trough categories :|

        $q = $db->query("SELECT fid FROM $table_forums WHERE type='group'");
        while ($cat = $db->fetch_array($q)) {
            // select forums
            $fq = $db->query("SELECT fid FROM $table_forums WHERE type='forum' AND fup='$cat[fid]'");
            while ($forum = $db->fetch_array($fq)) {
                $lastpost = array();
                $subq = $db->query("SELECT fid FROM $table_forums WHERE fup='$forum[fid]'");
                while ($sub = $db->fetch_array($subq)) {
                    $pq = $db->query("SELECT author, dateline FROM $table_posts WHERE fid='$sub[fid]' ORDER BY pid DESC LIMIT 1");
                    if ($db->num_rows($pq) > 0)  {
                        $curr = $db->fetch_array($pq);
                        $lastpost[] = $curr;
                        $lp = $curr['dateline'].'|'.$curr['author'];
                    } else {
                        $lp = '';
                    }
                    $db->query("UPDATE $table_forums SET lastpost='$lp' WHERE fid='$sub[fid]'");
                }
                $pq = $db->query("SELECT author, dateline FROM $table_posts WHERE fid='$forum[fid]' ORDER BY pid DESC LIMIT 1");
                if ($db->num_rows($pq) > 0) {
                    $lastpost[] = $db->fetch_array($pq);
                }


                if (count($lastpost) == 0) {
                    $lastpost = '';
                } else {
                    $top = 0;
                    $mkey = -1;
                    foreach ($lastpost as $key=>$v) {
                        if ($v['dateline'] > $top) {
                            $mkey = $key;
                            $top = $v['dateline'];
                        }
                    }
                    $lastpost = $lastpost[$mkey]['dateline'].'|'.$lastpost[$mkey]['author'];
                }
                $db->query("UPDATE $table_forums SET lastpost='$lastpost' WHERE fid = '$forum[fid]'");
            }
        }

        nav($lang['tools']);
        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[tool_completed] - $lang[tool_lastpost]</td></tr></table></table>";
        end_time();
        eval("echo (\"".template('footer')."\");");
        exit;
        break;

    case 'updatemoods':
        $db->query("UPDATE $table_members SET mood='$lang[nomoodtext]' WHERE mood=''");
        nav($lang['tools']);
        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[tool_completed] - $lang[tool_mood]</td></tr></table></table>";
        end_time();
        eval("echo (\"".template('footer')."\");");
        exit;
        break;

    case 'u2udump':
        if (!isset($yessubmit)) {
            echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">".$lang['u2udump_confirm']."<br /><form action=\"tools.php?action=u2udump\" method=\"post\"><input type=\"submit\" name=\"yessubmit\" value=\"".$lang['textyes']."\" /> - <input type=\"submit\" name=\"yessubmit\" value=\"".$lang['textno']."\" /></form></td></tr>";
        } elseif ($lang['textyes'] == $yessubmit) {
            $db->query("DELETE FROM $table_u2u");
            nav($lang['tools']);
            echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[tool_completed] - $lang[tool_u2u]</td></tr></table></table>";
            end_time();
            eval("echo (\"".template('footer')."\");");
            exit();
        } else {
            redirect('./cp.php', 0);
        }
        break;

    case 'whosonlinedump':
        if (!isset($yessubmit)) {
            echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">".$lang['whoodump_confirm']."<br /><form action=\"tools.php?action=whosonlinedump\" method=\"post\"><input type=\"submit\" name=\"yessubmit\" value=\"".$lang['textyes']."\" /> - <input type=\"submit\" name=\"yessubmit\" value=\"".$lang['textno']."\" /></form></td></tr>";
        } elseif ($lang['textyes'] == $yessubmit) {
            $db->query("DELETE FROM $table_whosonline");
            nav($lang['tools']);
            echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[tool_completed] - $lang[tool_whosonline]</td></tr></table></table>";
            end_time();
            eval("echo (\"".template('footer')."\");");
            exit();
        } else {
            redirect('./cp.php', 0);
        }
        break;

    case 'fixorphanedthreads':
        if (!isset($orphsubmit)) {
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
        /*
            function generously donated by vanderaj
        */
        if (!isset($orphattachsubmit)) {
            echo '<tr bgcolor="' . $altbg2 . '" class="ctrtablerow"><td>';
            echo '<form action="tools.php?action=fixorphanedattachments" method="post">';
            echo '<input type="submit" name="orphattachsubmit" value="'.$lang['o_attach_submit'].'" />';
            echo '</form>';
        } else {
            // discover the total number of attachments
            $i = 0;
            $q = $db->query("SELECT aid, pid FROM $table_attachments");
            while ($a = $db->fetch_array($q)) {
                $result = $db->query("SELECT pid FROM $table_posts WHERE pid='$a[pid]'");
                if ( $db->num_rows($result) == 0) {
                    // take action against this row as it has no parent post to attach to.
                    $db->query("DELETE FROM $table_attachments WHERE aid='$a[aid]'");
                    $i++;
                }
            }

            echo '<tr bgcolor="' . $altbg2 . '" class="ctrtablerow"><td>';
            echo $i . $lang['o_attachments_found'];
        }
        break;
}



echo "</td></tr></table></table>";
end_time();
eval("echo (\"".template('footer')."\");");
?>