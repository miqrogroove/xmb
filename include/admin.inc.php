<?php
/**
 * eXtreme Message Board
 * XMB 1.9.10 Karl
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

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

class admin {
    function rename_user($userfrom, $userto) {
        global $db, $lang, $self;

        if (strlen($userto) < 3 || strlen($userto) > 32) {
            return $lang['username_length_invalid'];
        }

        $dbuserfrom = $db->escape($userfrom);
        $dbuserto = $db->escape($userto);
        $dblikeuserfrom = $db->like_escape($userfrom);
        $dbregexuserfrom = $db->regexp_escape($userfrom);
        $userfrom = '';
        $userto = '';

        $query = $db->query("SELECT username FROM ".X_PREFIX."members WHERE username='$dbuserfrom'");
        $cUsrFrm = $db->num_rows($query);
        $db->free_result($query);

        $query = $db->query("SELECT username FROM ".X_PREFIX."members WHERE username='$dbuserto'");
        $cUsrTo = $db->num_rows($query);
        $db->free_result($query);

        if (!($cUsrFrm == 1 && $cUsrTo == 0)) {
            return $lang['admin_rename_fail'];
        }

        if (!$this->check_restricted($dbuserto)) {
            return $lang['restricted'];
        }

        @set_time_limit(180);
        $db->query("UPDATE ".X_PREFIX."members SET username='$dbuserto' WHERE username='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."buddys SET username='$dbuserto' WHERE username='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."buddys SET buddyname='$dbuserto' WHERE buddyname='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."favorites SET username='$dbuserto' WHERE username='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."forums SET moderator='$dbuserto' WHERE moderator='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."logs SET username='$dbuserto' WHERE username='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."posts SET author='$dbuserto' WHERE author='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."threads SET author='$dbuserto' WHERE author='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."u2u SET msgto='$dbuserto' WHERE msgto='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."u2u SET msgfrom='$dbuserto' WHERE msgfrom='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."u2u SET owner='$dbuserto' WHERE owner='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."whosonline SET username='$dbuserto' WHERE username='$dbuserfrom'");

        $query = $db->query("SELECT tid, lastpost from ".X_PREFIX."threads WHERE lastpost like '%|$dblikeuserfrom|%'");
        while($result = $db->fetch_array($query)) {
            $newlastpost = str_replace("|$dbuserfrom|", "|$dbuserto|", $db->escape($result['lastpost']));
            $db->query("UPDATE ".X_PREFIX."threads SET lastpost='$newlastpost' WHERE tid={$result['tid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT ignoreu2u, uid FROM ".X_PREFIX."members WHERE (ignoreu2u REGEXP '(^|(,))()*$dbregexuserfrom()*((,)|$)')");
        while($usr = $db->fetch_array($query)) {
            $parts = explode(',', $db->escape($usr['ignoreu2u']));
            $index = array_search($dbuserfrom, $parts);
            $parts[$index] = $dbuserto;
            $parts = implode(',', $parts);
            $db->query("UPDATE ".X_PREFIX."members SET ignoreu2u='$parts' WHERE uid={$usr['uid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT moderator, fid FROM ".X_PREFIX."forums WHERE (moderator REGEXP '(^|(,))()*$dbregexuserfrom()*((,)|$)')");
        while($list = $db->fetch_array($query)) {
            $parts = explode(',', $db->escape($list['moderator']));
            $index = array_search($dbuserfrom, $parts);
            $parts[$index] = $dbuserto;
            $parts = implode(', ', $parts);
            $db->query("UPDATE ".X_PREFIX."forums SET moderator='$parts' WHERE fid={$list['fid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT userlist, fid FROM ".X_PREFIX."forums WHERE (userlist REGEXP '(^|(,))()*$dbregexuserfrom()*((,)|$)')");
        while($list = $db->fetch_array($query)) {
            $parts = array_unique(array_map('trim', explode(',', $db->escape($list['userlist']))));
            $index = array_search($dbuserfrom, $parts);
            $parts[$index] = $dbuserto;
            $parts = implode(', ', $parts);
            $db->query("UPDATE ".X_PREFIX."forums SET userlist='$parts' WHERE fid={$list['fid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT fid, lastpost FROM ".X_PREFIX."forums WHERE lastpost LIKE '%|$dblikeuserfrom|%'");
        while($result = $db->fetch_array($query)) {
            $newlastpost = str_replace("|$dbuserfrom|", "|$dbuserto|", $db->escape($result['lastpost']));
            $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$newlastpost' WHERE fid={$result['fid']}");
        }
        $db->free_result($query);

        $this->fix_last_posts();

        return (($self['username'] == $userfrom) ? $lang['admin_rename_warn_self'] : '') . $lang['admin_rename_success'];
    }

    function fix_last_posts() {
        global $db;

        $q = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE (fup='0' OR fup='') AND type='forum'");
        while($loner = $db->fetch_array($q)) {
            $lastpost = array();
            $subq = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE fup = '$loner[fid]'");
            while($sub = $db->fetch_array($subq)) {
                $pq = $db->query("SELECT author, dateline, pid FROM ".X_PREFIX."posts WHERE fid='$sub[fid]' ORDER BY pid DESC LIMIT 1");
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

            $pq = $db->query("SELECT author, dateline, pid FROM ".X_PREFIX."posts WHERE fid='$loner[fid]' ORDER BY pid DESC LIMIT 1");
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

        $q = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE type='group'");
        while($cat = $db->fetch_array($q)) {
            $fq = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE type='forum' AND fup='$cat[fid]'");
            while($forum = $db->fetch_array($fq)) {
                $lastpost = array();
                $subq = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE fup='$forum[fid]'");
                while($sub = $db->fetch_array($subq)) {
                    $pq = $db->query("SELECT author, dateline, pid FROM ".X_PREFIX."posts WHERE fid='$sub[fid]' ORDER BY pid DESC LIMIT 1");
                    if ($db->num_rows($pq) > 0) {
                        $curr = $db->fetch_array($pq);
                        $lastpost[] = $curr;
                        $lp = $curr['dateline'].'|'.$curr['author'].'|'.$curr['pid'];
                    } else {
                        $lp = '';
                    }
                    $db->free_result($pq);
                    $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$lp' WHERE fid='$sub[fid]'");
                }
                $db->free_result($subq);

                $pq = $db->query("SELECT author, dateline, pid FROM ".X_PREFIX."posts WHERE fid='$forum[fid]' ORDER BY pid DESC LIMIT 1");
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
                $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$lastpost' WHERE fid='$forum[fid]'");
            }
            $db->free_result($fq);
        }
        $db->free_result($q);

        $q = $db->query("SELECT tid FROM ".X_PREFIX."threads");
        while($thread = $db->fetch_array($q)) {
            $lastpost = array();
            $pq = $db->query("SELECT author, dateline, pid FROM ".X_PREFIX."posts WHERE tid='$thread[tid]' ORDER BY pid DESC LIMIT 1");
            if ($db->num_rows($pq) > 0) {
                $curr = $db->fetch_array($pq);
                $lastpost[] = $curr;
                $lp = $curr['dateline'].'|'.$curr['author'].'|'.$curr['pid'];
            } else {
                $lp = '';
            }
            $db->free_result($pq);
            $db->query("UPDATE ".X_PREFIX."threads SET lastpost = '$lp' WHERE tid = '$thread[tid]'");
        }
        $db->free_result($q);
        return true;
    }

    function check_restricted($userto) {
        global $db;

        $nameokay = true;

        $find = array('<', '>', '|', '"', '[', ']', '\\', ',', '@', '\'');
        foreach($find as $needle) {
            if (false !== strpos($userto, $needle)) {
                return false;
            }
        }

        $query = $db->query("SELECT * FROM ".X_PREFIX."restricted");
        while($restriction = $db->fetch_array($query)) {
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
    global $lang, $THEME;

    ?>
    <table cellspacing="0" cellpadding="0" border="0" width="<?php echo $THEME['tablewidth']?>" align="center">
    <tr>
    <td bgcolor="<?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td colspan="30" align="center"><strong><font color="<?php echo $THEME['cattext']?>"><?php echo $lang['textcp']?></font></strong></td>
    </tr>
    <tr bgcolor="<?php echo $THEME['altbg1']?>" class="ctrtablerow">
    <td colspan="30">
    <br />
    <table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
    <tr>
    <td bgcolor="<?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="ctrcategory">
    <td valign="top" width="20%"><strong><font color="<?php echo $THEME['cattext']?>"><?php echo $lang['general']?></font></strong></td>
    <td valign="top" width="20%"><strong><font color="<?php echo $THEME['cattext']?>"><?php echo $lang['textforums']?></font></strong></td>
    <td valign="top" width="20%"><strong><font color="<?php echo $THEME['cattext']?>"><?php echo $lang['textmembers']?></font></strong></td>
    <td valign="top" width="20%"><strong><font color="<?php echo $THEME['cattext']?>"><?php echo $lang['look_feel']?></font></strong></td>
    </tr>
    <tr>
    <td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="cp2.php?action=attachments"><?php echo $lang['textattachman']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=censor"><?php echo $lang['textcensors']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=newsletter"><?php echo $lang['textnewsletter']?></a><br />
    &raquo;&nbsp;<a href="cp.php?action=search"><?php echo $lang['cpsearch']?></a><br />
    &raquo;&nbsp;<a href="cp.php?action=settings"><?php echo $lang['textsettings']?></a><br />
    </td>
    <td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="cp.php?action=forum"><?php echo $lang['textforums']?></a><br />
    &raquo;&nbsp;<a href="cp.php?action=mods"><?php echo $lang['textmods']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=prune"><?php echo $lang['textprune']?></a><br />
    </td>
    <td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="cp.php?action=ipban"><?php echo $lang['textipban']?></a><br />
    &raquo;&nbsp;<a href="cp.php?action=members"><?php echo $lang['textmembers']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=ranks"><?php echo $lang['textuserranks']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=restrictions"><?php echo $lang['cprestricted']?></a><br />
    &raquo;&nbsp;<a href="cp.php?action=rename"><?php echo $lang['admin_rename_txt']?></a><br />
    </td>
    <td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="cp2.php?action=smilies"><?php echo $lang['smilies']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=templates"><?php echo $lang['templates']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=themes"><?php echo $lang['themes']?></a><br />
    </td>
    </tr>
    <tr class="ctrcategory">
    <td valign="top" width="20%"><strong><font color="<?php echo $THEME['cattext']?>"><?php echo $lang['logs']?></font></strong></td>
    <td valign="top" width="20%"><strong><font color="<?php echo $THEME['cattext']?>"><?php echo $lang['tools']?></font></strong></td>
    <td valign="top" width="20%"><strong><font color="<?php echo $THEME['cattext']?>"><?php echo $lang['mysql_tools']?></font></strong></td>
    <td valign="top" width="20%"><strong><font color="<?php echo $THEME['cattext']?>"><?php echo $lang['textfaqextra']?></font></strong></td>
    </tr>
    <tr>
    <td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="cp2.php?action=modlog"><?php echo $lang['textmodlogs']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=cplog"><?php echo $lang['textcplogs']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=logsdump"><?php echo $lang['textlogsdump']?></a><br />
    </td>
    <td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="tools.php?action=fixftotals"><?php echo $lang['textfixposts']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=fixlastposts&amp;scope=forumsonly"><?php echo $lang['textfixlastposts'].' - '.$lang['textforums']; ?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=fixlastposts"><?php echo $lang['textfixlastposts'].' - '.$lang['threads']; ?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=fixmposts"><?php echo $lang['textfixmemposts']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=fixttotals"><?php echo $lang['textfixthread']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=fixorphanedthreads"><?php echo $lang['textfixothreads']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=fixorphanedattachments"><?php echo $lang['textfixoattachments']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=updatemoods"><?php echo $lang['textfixmoods']?></a><br />
    </td>
    <td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="cp.php?action=upgrade"><?php echo $lang['raw_mysql']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=analyzetables"><?php echo $lang['analyze']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=checktables"><?php echo $lang['textcheck']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=optimizetables"><?php echo $lang['optimize']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=repairtables"><?php echo $lang['repair']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=u2udump"><?php echo $lang['u2udump']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=whosonlinedump"><?php echo $lang['cpwodump']?></a><br />
    </td>
    <td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $THEME['altbg2']?>">
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    <br />
    <?php
}

function settingHTML($setting, &$on, &$off) {
    global $SETTINGS, $selHTML;

    $on = $off = '';
    switch($SETTINGS[$setting]) {
        case 'on':
            $on = $selHTML;
            break;
        default:
            $off = $selHTML;
            break;
    }
}

function printsetting1($setname, $varname, $check1, $check2) {
    global $lang, $THEME;

    ?>
    <tr class="tablerow">
    <td bgcolor="<?php echo $THEME['altbg1']?>" valign="top"><?php echo $setname?></td>
    <td bgcolor="<?php echo $THEME['altbg2']?>">
    <select name="<?php echo $varname?>">
    <option value="on" <?php echo $check1?>><?php echo $lang['texton']?></option>
    <option value="off" <?php echo $check2?>><?php echo $lang['textoff']?></option>
    </select>
    </td>
    </tr>
    <?php
}

function printsetting2($setname, $varname, $value, $size) {
    global $THEME;

    ?>
    <tr class="tablerow">
    <td bgcolor="<?php echo $THEME['altbg1']?>" valign="top"><?php echo $setname?></td>
    <td bgcolor="<?php echo $THEME['altbg2']?>"><input type="text" size="<?php echo $size?>" value="<?php echo $value?>" name="<?php echo $varname?>" /></td>
    </tr>
    <?php
}

function printsetting3($setname, $boxname, $varnames, $values, $checked, $multi=true) {
    global $THEME, $selHTML;

    foreach($varnames as $key=>$val) {
        if (isset($checked[$key]) && $checked[$key] !== true) {
            $optionlist[] = '<option value="'.$values[$key].'">'.$varnames[$key].'</option>';
        } else {
            $optionlist[] = '<option value="'.$values[$key].'" '.$selHTML.'>'.$varnames[$key].'</option>';
        }
    }
    $optionlist = implode("\n", $optionlist);
    ?>
    <tr class="tablerow">
    <td bgcolor="<?php echo $THEME['altbg1']?>" valign="top"><?php echo $setname?></td>
    <td bgcolor="<?php echo $THEME['altbg2']?>"><select <?php echo ($multi ? 'multiple="multiple"' : '')?> name="<?php echo $boxname?><?php echo ($multi ? '[]' : '')?>"><?php echo $optionlist?></select></td>
    </tr>
    <?php
}

function printsetting4($settingDesc, $name, $value, $rows=5, $cols=50) {
    global $THEME;

    ?>
    <tr class="tablerow">
    <td bgcolor="<?php echo $THEME['altbg1']?>" valign="top"><?php echo $settingDesc?></td>
    <td bgcolor="<?php echo $THEME['altbg2']?>"><textarea rows="<?php echo $rows; ?>" name="<?php echo $name; ?>" cols="<?php echo $cols; ?>"><?php echo $value?></textarea></td>
    </tr>
    <?php
}
?>