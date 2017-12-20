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

if (!defined('IN_CODE')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}


/* Assert Additional Security */

if (X_SADMIN) {
    $x_error = '';

    //@todo translation needed
    if (file_exists(ROOT.'install/') and !@rmdir(ROOT.'install/')) {
        $x_error = 'The installation files ("./install/") have been found on the server, but could not be removed automatically. Please remove them as soon as possible.';
    }
    if (file_exists(ROOT.'Upgrade/') and !@rmdir(ROOT.'Upgrade/') or file_exists(ROOT.'upgrade/') and !@rmdir(ROOT.'upgrade/')) {
        $x_error = 'The upgrade tool ("./upgrade/") has been found on the server, but could not be removed automatically. Please remove it as soon as possible.';
    }
    if (file_exists(ROOT.'upgrade.php')) {
        $x_error = 'The upgrade tool ("./upgrade.php") has been found on the server. Please remove it as soon as possible.';
    }

    if (strlen($x_error) > 0) {
        header('HTTP/1.0 500 Internal Server Error');
        loadtemplates('error');
        error($x_error);
    }
    unset($x_error);
}


/* Admin Panel Functions */

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

        $query = $db->query("SELECT ignoreu2u, uid FROM ".X_PREFIX."members WHERE (ignoreu2u REGEXP '(^|(,))()*$dbregexuserfrom()*((,)|$)')");
        while($usr = $db->fetch_array($query)) {
            $db->escape_fast($usr['ignoreu2u']);
            $parts = explode(',', $usr['ignoreu2u']);
            $index = array_search($dbuserfrom, $parts);
            $parts[$index] = $dbuserto;
            $parts = implode(',', $parts);
            $db->query("UPDATE ".X_PREFIX."members SET ignoreu2u='$parts' WHERE uid={$usr['uid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT moderator, fid FROM ".X_PREFIX."forums WHERE (moderator REGEXP '(^|(,))()*$dbregexuserfrom()*((,)|$)')");
        while($list = $db->fetch_array($query)) {
            $db->escape_fast($list['moderator']);
            $parts = explode(',', $list['moderator']);
            $index = array_search($dbuserfrom, $parts);
            $parts[$index] = $dbuserto;
            $parts = implode(', ', $parts);
            $db->query("UPDATE ".X_PREFIX."forums SET moderator='$parts' WHERE fid={$list['fid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT userlist, fid FROM ".X_PREFIX."forums WHERE (userlist REGEXP '(^|(,))()*$dbregexuserfrom()*((,)|$)')");
        while($list = $db->fetch_array($query)) {
            $db->escape_fast($list['userlist']);
            $parts = array_unique(array_map('trim', explode(',', $list['userlist'])));
            $index = array_search($dbuserfrom, $parts);
            $parts[$index] = $dbuserto;
            $parts = implode(', ', $parts);
            $db->query("UPDATE ".X_PREFIX."forums SET userlist='$parts' WHERE fid={$list['fid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT fid, lastpost FROM ".X_PREFIX."forums WHERE lastpost LIKE '%|$dblikeuserfrom|%'");
        while($result = $db->fetch_array($query)) {
            $db->escape_fast($result['lastpost']);
            $newlastpost = str_replace("|$dbuserfrom|", "|$dbuserto|", $result['lastpost']);
            $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$newlastpost' WHERE fid={$result['fid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT tid, lastpost FROM ".X_PREFIX."threads WHERE lastpost LIKE '%|$dblikeuserfrom|%'");
        while($result = $db->fetch_array($query)) {
            $db->escape_fast($result['lastpost']);
            $newlastpost = str_replace("|$dbuserfrom|", "|$dbuserto|", $result['lastpost']);
            $db->query("UPDATE ".X_PREFIX."threads SET lastpost='$newlastpost' WHERE tid={$result['tid']}");
        }
        $db->free_result($query);

        return (($self['username'] == $userfrom) ? $lang['admin_rename_warn_self'] : '') . $lang['admin_rename_success'];
    }

    function check_restricted($userto) {
        global $db;

        $nameokay = true;

        if ($userto != preg_replace('#[\]\'\x00-\x1F\x7F<>\\\\|"[,@]|  #', '', $userto)) {
            return false;
        }

        $query = $db->query("SELECT * FROM ".X_PREFIX."restricted");
        while($restriction = $db->fetch_array($query)) {
            if ($restriction['case_sensitivity'] == 0) {
                $t_username = strtolower($userto);
                $restriction['name'] = strtolower($restriction['name']);
            }

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
    &raquo;&nbsp;<a href="cp2.php?action=lang"><?php echo $lang['translations']?></a><br />
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
    &raquo;&nbsp;<a href="tools.php?action=fixorphanedpolls"><?php echo $lang['textfixopolls']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=fixorphanedposts"><?php echo $lang['textfixoposts']?></a><br />
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
    <td bgcolor="<?php echo $THEME['altbg2']?>"><textarea rows="<?php echo $rows; ?>" name="<?php echo $name; ?>" cols="<?php echo $cols; ?>">
<?php // Linefeed required here - Do not edit!
    echo $value;
    ?></textarea></td>
    </tr>
    <?php
}

function printsetting5($settingDesc, $errorMsg) {
    global $THEME;

    ?>
    <tr class="tablerow">
    <td bgcolor="<?php echo $THEME['altbg1']?>" valign="top"><?php echo $settingDesc; ?></td>
    <td bgcolor="<?php echo $THEME['altbg2']?>"><?php echo $errorMsg; ?></td>
    </tr>
    <?php
}

function readFileAsINI($filename) {
    $lines = file($filename);
    foreach($lines as $line_num => $line) {
        $temp = explode("=",$line);
        if ($temp[0] != 'dummy') {
            $key = trim($temp[0]);
            $val = trim($temp[1]);
            $thefile[$key] = $val;
        }
    }
    return $thefile;
}

function dump_query($resource, $header=true) {
    global $altbg2, $altbg1, $db, $cattext;
    if (!$db->error()) {
        $count = $db->num_fields($resource);
        if ($header) {
            ?>
            <tr class="category" bgcolor="<?php echo $altbg2?>" align="center">
            <?php
            for($i=0;$i<$count;$i++) {
                echo '<td align="left">';
                echo '<strong><font color='.$cattext.'>'.$db->field_name($resource, $i).'</font></strong>';
                echo '</td>';
            }
            echo '</tr>';
        }

        while($a = $db->fetch_array($resource, SQL_NUM)) {
            ?>
            <tr bgcolor="<?php echo $altbg1?>" class="ctrtablerow">
            <?php
            for($i=0;$i<$count;$i++) {
                echo '<td align="left">';

                if (trim($a[$i]) == '') {
                    echo '&nbsp;';
                } else {
                    echo nl2br(cdataOut($a[$i]));
                }
                echo '</td>';
            }
            echo '</tr>';
        }
    } else {
        error($db->error());
    }
}

return;
?>
