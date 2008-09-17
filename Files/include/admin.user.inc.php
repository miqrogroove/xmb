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

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

class admin {
    function rename_user($userfrom, $userto) {
        global $db, $lang, $self;
        global $table_whosonline, $table_members, $table_posts, $table_threads;
        global $table_forums, $table_favorites, $table_buddys, $table_u2u, $table_logs;

        if ($userfrom == '' || $userto == '') {
            return $lang['admin_rename_fail'];
        }

        $query = $db->query("SELECT username FROM $table_members WHERE username='$userfrom'");
        $cUsrFrm = $db->num_rows($query);
        $db->free_result($query);

        $query = $db->query("SELECT username FROM $table_members WHERE username='$userto'");
        $cUsrTo = $db->num_rows($query);
        $db->free_result($query);

        if (!($cUsrFrm == 1 && $cUsrTo == 0)) {
            return $lang['admin_rename_fail'];
        }

        if (!$this->check_restricted($userto)) {
            return $lang['restricted'];
        }

        if (strlen($userto) < 3 || strlen($userto) > 32) {
            return $lang['username_length_invalid'];
        }

        @set_time_limit(180);
        $db->query("UPDATE $table_members SET username='$userto' WHERE username='$userfrom'");
        $db->query("UPDATE $table_buddys SET username='$userto' WHERE username='$userfrom'");
        $db->query("UPDATE $table_buddys SET buddyname='$userto' WHERE buddyname='$userfrom'");
        $db->query("UPDATE $table_favorites SET username='$userto' WHERE username='$userfrom'");
        $db->query("UPDATE $table_forums SET moderator='$userto' WHERE moderator='$userfrom'");
        $db->query("UPDATE $table_logs SET username='$userto' WHERE username='$userfrom'");
        $db->query("UPDATE $table_posts SET author='$userto' WHERE author='$userfrom'");
        $db->query("UPDATE $table_threads SET author='$userto' WHERE author='$userfrom'");
        $db->query("UPDATE $table_u2u SET msgto='$userto' WHERE msgto='$userfrom'");
        $db->query("UPDATE $table_u2u SET msgfrom='$userto' WHERE msgfrom='$userfrom'");
        $db->query("UPDATE $table_u2u SET owner='$userto' WHERE owner='$userfrom'");
        $db->query("UPDATE $table_whosonline SET username='$userto' WHERE username='$userfrom'");

        $query = $db->query("SELECT tid, lastpost from $table_threads WHERE lastpost like '%$userfrom'");
        while ($result = $db->fetch_array($query)) {
            list($posttime, $lastauthor) = explode("|", $result['lastpost']);
            if ($lastauthor == $userfrom) {
                $newlastpost = $posttime . '|' . $userto;
                $db->query("UPDATE $table_threads SET lastpost='$newlastpost' WHERE tid='".$result['tid']."'");
            }
        }
        $db->free_result($query);

        $query = $db->query("SELECT pollopts, tid FROM $table_threads WHERE pollopts LIKE '% ".$userfrom." %'");
        $poll = array();
        while ($result = $db->fetch_array($query)) {
            $parts = explode('#|#', $result['pollopts']);
            $parts[count($parts)-1] = str_replace(' '.$userfrom.' ', ' '.$userto.' ', $parts[count($parts)-1]);
            $pollopts = implode('#|#', $parts);
            $db->query("UPDATE $table_threads SET pollopts = '$pollopts' WHERE tid='".$result['tid']."'");
        }
        $db->free_result($query);

        $query = $db->query("SELECT ignoreu2u, uid FROM $table_members WHERE (ignoreu2u REGEXP '(^|(,))()*$userfrom()*((,)|$)')");
        while ($usr = $db->fetch_array($query)) {
            $parts = explode(',', $usr['ignoreu2u']);
            $index = array_search($userfrom, $parts);
            $parts[$index] = $userto;
            $parts = implode(',', $parts);
            $db->query("UPDATE $table_members SET ignoreu2u='".$parts."' WHERE uid='".$usr['uid']."'");
        }
        $db->free_result($query);

        $query = $db->query("SELECT moderator, fid FROM $table_forums WHERE (moderator REGEXP '(^|(,))()*$userfrom()*((,)|$)')");
        while ($list = $db->fetch_array($query)) {
            $parts = explode(',', $list['moderator']);
            $index = array_search($userfrom, $parts);
            $parts[$index] = $userto;
            $parts = implode(', ', $parts);
            $db->query("UPDATE $table_forums SET moderator='".$parts."' WHERE fid='".$list['fid']."'");
        }
        $db->free_result($query);

        $query = $db->query("SELECT userlist, fid FROM $table_forums WHERE (userlist REGEXP '(^|(,))()*$userfrom()*((,)|$)')");
        while ($list = $db->fetch_array($query)) {
            $parts = array_unique(array_map('trim', explode(',', $list['userlist'])));
            $index = array_search($userfrom, $parts);
            $parts[$index] = $userto;
            $parts = implode(', ', $parts);
            $db->query("UPDATE $table_forums SET userlist='".$parts."' WHERE fid='".$list['fid']."'");
        }
        $db->free_result($query);

        $query = $db->query("SELECT fid, lastpost FROM $table_forums WHERE lastpost LIKE '%$userfrom'");
        while ($result = $db->fetch_array($query)) {
            list($posttime, $lastauthor, $lastpid) = explode("|", $result['lastpost']);
            if ($lastauthor == $userfrom) {
                $newlastpost = $posttime . '|' . $userto.'|'.$lastpid;
                $db->query("UPDATE $table_forums SET lastpost='$newlastpost' WHERE fid='".$result['fid']."'");
            }
        }
        $db->free_result($query);

        $this->fix_last_posts();

        return (($self['username'] == $userfrom) ? $lang['admin_rename_warn_self'] : '') . $lang['admin_rename_success'];
    }

    function fix_last_posts() {
        global $db, $table_forums, $table_threads, $table_posts;

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
        return true;
    }

    function check_restricted($userto) {
        global $db, $table_restricted;

        $nameokay = true;

        $find = array('<', '>', '|', '"', '[', ']', '\\', ',', '@', '\'', ' ');
        foreach ($find as $needle) {
            if (false !== strpos($userto, $needle)) {
                return false;
            }
        }

        $query = $db->query("SELECT * FROM $table_restricted");
        while ($restriction = $db->fetch_array($query)) {
            if ($restriction['case_sensitivity'] == 1) {
                if ($restriction['partial'] == 1) {
                    if (strpos($userto, $restriction['name']) !== false) {
                        $nameokay = false;
                    }
                } else {
                    if ($userto == $restriction['name']) {
                        $nameokay = false;
                    }
                }
            } else {
                $t_username = strtolower($userto);
                $restriction['name'] = strtolower($restriction['name']);

                if ($restriction['partial'] == 1) {
                    if (strpos($t_username, $restriction['name']) !== false) {
                        $nameokay = false;
                    }
                } else {
                    if ($t_username == $restriction['name']) {
                        $nameokay = false;
                    }
                }
            }
        }
        $db->free_result($query);
        return $nameokay;
    }
}

function displayAdminPanel() {
    global $tablewidth, $bordercolor, $borderwidth, $tablespace, $altbg1, $altbg2;
    global $lang, $cattext;

    ?>
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
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    <br />
    <?php
}
?>