<?php
/* $Id: cp_forum.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
/*
    © 2001 - 2007 The XMB Development Team
    http://www.xmbforum.com

    Financial and other support 2007- iEntry Inc
    http://www.ientry.com

    Financial and other support 2002-2007 Aventure Media 
    http://www.aventure-media.co.uk

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

if (!defined('IN_CODE') && (defined('DEBUG') && DEBUG == false)) {
    exit ("Not allowed to run this file directly.");
}

function displayForumPanel()
{
    global $db, $table_forums, $THEME, $lang, $oToken; 
    
    $groups = array();
    $forums = array();
    $forums['0'] = array();
    $forumlist = array();
    $subs = array();
    $i = 0;
    $query = $db->query("SELECT fid, type, name, displayorder, status, fup FROM $table_forums ORDER BY fup ASC, displayorder ASC");
    while ($selForums = $db->fetch_array($query)) {
        if ($selForums['type'] == 'group') {
            $groups[$i]['fid'] = $selForums['fid'];
            $groups[$i]['name'] = htmlspecialchars($selForums['name']);
            $groups[$i]['displayorder'] = $selForums['displayorder'];
            $groups[$i]['status'] = $selForums['status'];
            $groups[$i]['fup'] = $selForums['fup'];

        } elseif ($selForums['type'] == 'forum') {
            $id = (empty($selForums['fup'])) ? 0 : $selForums['fup'];
            $forums[$id][$i]['fid'] = $selForums['fid'];
            $forums[$id][$i]['name'] = htmlspecialchars($selForums['name']);
            $forums[$id][$i]['displayorder'] = $selForums['displayorder'];
            $forums[$id][$i]['status'] = $selForums['status'];
            $forums[$id][$i]['fup'] = $selForums['fup'];
            $forumlist[$i]['fid'] = $selForums['fid'];
            $forumlist[$i]['name'] = $selForums['name'];

        } elseif ($selForums['type'] == 'sub') {
            $subs["$selForums[fup]"][$i]['fid'] = $selForums['fid'];
            $subs["$selForums[fup]"][$i]['name'] = htmlspecialchars($selForums['name']);
            $subs["$selForums[fup]"][$i]['displayorder'] = $selForums['displayorder'];
            $subs["$selForums[fup]"][$i]['status'] = $selForums['status'];
            $subs["$selForums[fup]"][$i]['fup'] = $selForums['fup'];
        }
        $i++;
    }
    ?>

    <tr class="altbg2">
    <td>
    <form method="post" action="cp.php?action=forum">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr>
    <td class="category"><font style="color: <?php echo $THEME['cattext']?>"><strong><?php echo $lang['textforumopts']?></strong></font></td>
    </tr>

    <?php
    foreach ($forums[0] as $forum) {

        $on = $off = '';
        if ($forum['status'] == "on") {
            $on = "selected=\"selected\"";
        } else {
            $off = "selected=\"selected\"";
        }

        ?>

        <tr class="altbg2 tablerow">
        <td class="smalltxt"><input type="checkbox" name="delete<?php echo $forum['fid']?>" value="<?php echo $forum['fid']?>" />
        &nbsp;<input type="text" name="name<?php echo $forum['fid']?>" value="<?php echo stripslashes($forum['name'])?>" />
        &nbsp; <?php echo $lang['textorder']?> <input type="text" name="displayorder<?php echo $forum['fid']?>" size="2" value="<?php echo $forum['displayorder']?>" />
        &nbsp; <select name="status<?php echo $forum['fid']?>">
        <option value="on" <?php echo $on?>><?php echo $lang['texton']?></option><option value="off" <?php echo $off?>><?php echo $lang['textoff']?></option></select>
        &nbsp; <select name="moveto<?php echo $forum['fid']?>"><option value="" selected="selected">-<?php echo $lang['textnone']?>-</option>

        <?php
        foreach ($groups as $moveforum) {
            echo "<option value=\"$moveforum[fid]\">".stripslashes($moveforum['name'])."</option>";
        }
        ?>

        </select>
        <a href="cp.php?action=forum&amp;fdetails=<?php echo $forum['fid']?>"><?php echo $lang['textmoreopts']?></a></td>
        </tr>

        <?php
    if (array_key_exists($forum['fid'], $subs)) {
        foreach ($subs[$forum['fid']] as $subforum) {
            $on = $off = '';
            if ($subforum['status'] == "on") {
                $on = "selected=\"selected\"";
            } else {
                $off = "selected=\"selected\"";
            }
            ?>

            <tr class="altbg2 tablerow">
            <td class="smalltxt"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="checkbox" name="delete<?php echo $subforum['fid']?>" value="<?php echo $subforum['fid']?>" />
            &nbsp;<input type="text" name="name<?php echo $subforum['fid']?>" value="<?php echo stripslashes($subforum['name'])?>" />
            &nbsp; <?php echo $lang['textorder']?> <input type="text" name="displayorder<?php echo $subforum['fid']?>" size="2" value="<?php echo $subforum['displayorder']?>" />
            &nbsp; <select name="status<?php echo $subforum['fid']?>">
            <option value="on" <?php echo $on?>><?php echo $lang['texton']?></option><option value="off" <?php echo $off?>><?php echo $lang['textoff']?></option></select>
            &nbsp; <select name="moveto<?php echo $subforum['fid']?>">

            <?php
            foreach ($forumlist as $moveforum) {
                if ($subforum['fup'] == $moveforum['fid']) {
                    echo '<option value="'.$moveforum['fid'].'" selected="selected">'.stripslashes($moveforum['name']).'</option>';
                } else {
                    echo '<option value="'.$moveforum['fid'].'">'.stripslashes($moveforum['name']).'</option>';
                }
            }

            ?>

            </select>
            <a href="cp.php?action=forum&amp;fdetails=<?php echo $subforum['fid']?>"><?php echo $lang['textmoreopts']?></a></td>
            </tr>

            <?php
            }
        }
    }

    foreach ($groups as $group) {
        $on = $off = '';
        if ($group['status'] == "on") {
            $on = "selected=\"selected\"";
        } else {
            $off = "selected=\"selected\"";
        }

        ?>

        <tr class="altbg2 tablerow">
        <td>&nbsp;</td>
        </tr>
        <tr class="altbg1 tablerow">
        <td class="smalltxt"><input type="checkbox" name="delete<?php echo $group['fid']?>" value="<?php echo $group['fid']?>" />
        <input type="text" name="name<?php echo $group['fid']?>" value="<?php echo stripslashes($group['name'])?>" />
        &nbsp; <?php echo $lang['textorder']?> <input type="text" name="displayorder<?php echo $group['fid']?>" size="2" value="<?php echo $group['displayorder']?>" />
        &nbsp; <select name="status<?php echo $group['fid']?>">
        <option value="on" <?php echo $on?>><?php echo $lang['texton']?></option><option value="off" <?php echo $off?>><?php echo $lang['textoff']?></option></select>
        </td>
        </tr>

        <?php
    if (array_key_exists($group['fid'], $forums)) {
        foreach ($forums[$group['fid']] as $forum) {
            $on = $off = '';
            if ($forum['status'] == "on") {
                $on = "selected=\"selected\"";
            } else {
                $off = "selected=\"selected\"";
            }

            ?>

            <tr class="altbg2 tablerow">
            <td class="smalltxt"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="checkbox" name="delete<?php echo $forum['fid']?>" value="<?php echo $forum['fid']?>" />
            &nbsp;<input type="text" name="name<?php echo $forum['fid']?>" value="<?php echo stripslashes($forum['name'])?>" />
            &nbsp; <?php echo $lang['textorder']?> <input type="text" name="displayorder<?php echo $forum['fid']?>" size="2" value="<?php echo $forum['displayorder']?>" />
            &nbsp; <select name="status<?php echo $forum['fid']?>">
            <option value="on" <?php echo $on?>><?php echo $lang['texton']?></option><option value="off" <?php echo $off?>><?php echo $lang['textoff']?></option></select>
            &nbsp; <select name="moveto<?php echo $forum['fid']?>"><option value="">-<?php echo $lang['textnone']?>-</option>

            <?php
            foreach ($groups as $moveforum) {
                if ($moveforum['fid'] == $forum['fup']) {
                    $curgroup = "selected=\"selected\"";
                } else {
                    $curgroup = "";
                }
                echo "<option value=\"$moveforum[fid]\" $curgroup>".stripslashes($moveforum['name'])."</option>";
            }
            ?>
            </select>
            <a href="cp.php?action=forum&amp;fdetails=<?php echo $forum['fid']?>"><?php echo $lang['textmoreopts']?></a></td>
            </tr>

            <?php
        if (array_key_exists($forum['fid'], $subs)) {
            foreach ($subs[$forum['fid']] as $forum) {
                $on = $off = '';
                if ($forum['status'] == "on") {
                    $on = "selected=\"selected\"";
                } else {
                    $off = "selected=\"selected\"";
                }
                ?>

                <tr class="altbg2 tablerow">
                <td class="smalltxt"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<input type="checkbox" name="delete<?php echo $forum['fid']?>" value="<?php echo $forum['fid']?>" />
                &nbsp;<input type="text" name="name<?php echo $forum['fid']?>" value="<?php echo stripslashes($forum['name'])?>" />
                &nbsp; <?php echo $lang['textorder']?> <input type="text" name="displayorder<?php echo $forum['fid']?>" size="2" value="<?php echo $forum['displayorder']?>" />
                &nbsp; <select name="status<?php echo $forum['fid']?>">
                <option value="on" <?php echo $on?>><?php echo $lang['texton']?></option><option value="off" <?php echo $off?>><?php echo $lang['textoff']?></option></select>
                &nbsp; <select name="moveto<?php echo $forum['fid']?>">

                <?php
                foreach ($forumlist as $moveforum) {
                    if ($moveforum['fid'] == $forum['fup']) {
                        echo '<option value="'.$moveforum['fid'].'" selected="selected">'.stripslashes($moveforum['name']).'</option>';
                    } else {
                        echo '<option value="'.$moveforum['fid'].'">'.stripslashes($moveforum['name']).'</option>';
                    }
                }

                ?>
                </select>
                <a href="cp.php?action=forum&amp;fdetails=<?php echo $forum['fid']?>"><?php echo $lang['textmoreopts']?></a></td>
                </tr>

                <?php
                }
            }
        }
    }

    }
    ?>

    <tr class="altbg1 tablerow">
    <td>&nbsp;</td>
    </tr>
    <tr class="altbg2 tablerow">
    <td class="smalltxt"><input type="text" name="newgname" value="<?php echo $lang['textnewgroup']?>" />
    &nbsp; <?php echo $lang['textorder']?> <input type="text" name="newgorder" size="2" />
    &nbsp; <select name="newgstatus">
    <option value="on"><?php echo $lang['texton']?></option><option value="off"><?php echo $lang['textoff']?></option></select></td>
    </tr>
    <tr class="tablerow">
    <td class="altbg2 smalltxt"><input type="text" name="newfname" value="<?php echo $lang['textnewforum']?>" />
    &nbsp; <?php echo $lang['textorder']?> <input type="text" name="newforder" size="2" />
    &nbsp; <select name="newfstatus">
    <option value="on"><?php echo $lang['texton']?></option><option value="off"><?php echo $lang['textoff']?></option></select>
    &nbsp; <select name="newffup"><option value="" selected="selected">-<?php echo $lang['textnone']?>-</option>

    <?php
    foreach ($groups as $group) {
        echo '<option value="'.$group['fid'].'">'.stripslashes($group['name']).'</option>';
    }
    ?>

    </select>
    </td>
    </tr>
    <tr class="altbg2 tablerow">
    <td class="smalltxt"><input type="text" name="newsubname" value="<?php echo $lang['textnewsubf']?>" />
    &nbsp; <?php echo $lang['textorder']?> <input type="text" name="newsuborder" size="2" />
    &nbsp; <select name="newsubstatus"><option value="on"><?php echo $lang['texton']?></option><option value="off"><?php echo $lang['textoff']?></option></select>
    &nbsp; <select name="newsubfup">

    <?php
    foreach ($forumlist as $group) {
        echo '<option value="'.$group['fid'].'">'.stripslashes($group['name']).'</option>';
    }
    ?>

    </select>
    </td>
    </tr>
    <tr>
    <td class="altbg2 tablerow" align="center"><input type="submit" name="forumsubmit" value="<?php echo $lang['textsubmitchanges']?>" class="submit" /></td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </form>
    </td>
    </tr>

    <?php
}

function displayForumDetailPanel($fdetails)
{
    global $db, $table_forums, $table_themes, $THEME, $lang, $oToken; 
    
    ?>

    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp.php?action=forum&amp;fdetails=<?php echo $fdetails?>">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="100%" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr>
    <td class="category" colspan="2"><font style="color: <?php echo $THEME['cattext']?>"><strong><?php echo $lang['textforumopts']?></strong></font></td>
    </tr>

    <?php
    $queryg = $db->query("SELECT * FROM $table_forums WHERE fid=$fdetails");
    $forum = $db->fetch_array($queryg);

    $themelist   = array();
    $themelist[] = '<select name="themeforumnew">';
    $themelist[] = '<option value="0">'.$lang['textusedefault'].'</option>';
    $query = $db->query("SELECT themeid, name FROM $table_themes ORDER BY name ASC");
    while ($themeinfo = $db->fetch_array($query)) {
        if ($themeinfo['themeid'] == $forum['theme']) {
            $themelist[] = '<option value="'.$themeinfo['themeid'].'" selected="selected">'.stripslashes($themeinfo['name']).'</option>';
        } else {
            $themelist[] = '<option value="'.$themeinfo['themeid'].'">'.stripslashes($themeinfo['name']).'</option>';
        }
    }
    $themelist[] = '</select>';
    $themelist   = implode("\n", $themelist);

    if ($forum['allowsmilies'] == "yes" || $forum['allowsmilies'] == "on") {
        $checked3 = "checked";
    } else {
        $checked3 = "";
    }

    if ($forum['allowbbcode'] == "yes" || $forum['allowbbcode'] == "on") {
        $checked4 = "checked";
    } else {
        $checked4 = "";
    }

    if ($forum['allowimgcode'] == "yes" || $forum['allowimgcode'] == "on") {
        $checked5 = "checked";
    } else {
        $checked5 = "";
    }

    if ($forum['attachstatus'] == "on" || $forum['attachstatus'] == "yes") {
        $checked6 = "checked";
    } else {
        $checked6 = "";
    }
    
    $forum['name'] = stripslashes($forum['name']);
    $forum['description'] = stripslashes($forum['description']);
    ?>

    <tr class="tablerow">
    <td class="altbg1"><?php echo $lang['textforumname']?></td>
    <td class="altbg2"><input type="text" name="namenew" value="<?php echo htmlspecialchars($forum['name'])?>" /></td>
    </tr>
    <tr class="tablerow">
    <td class="altbg1"><?php echo $lang['textdesc']?></td>
    <td class="altbg2"><textarea rows="4" cols="30" name="descnew"><?php echo htmlspecialchars($forum['description'])?></textarea></td>
    </tr>
    <tr class="tablerow">
    <td class="altbg1" valign="top"><?php echo $lang['textallow']?></td>
    <td class="altbg2 smalltxt">
    <input type="checkbox" name="allowsmiliesnew" value="yes" <?php echo $checked3?> /><?php echo $lang['textsmilies']?><br />
    <input type="checkbox" name="allowbbcodenew" value="yes" <?php echo $checked4?> /><?php echo $lang['textbbcode']?><br />
    <input type="checkbox" name="allowimgcodenew" value="yes" <?php echo $checked5?> /><?php echo $lang['textimgcode']?><br />
    <input type="checkbox" name="attachstatusnew" value="on" <?php echo $checked6?> /><?php echo $lang['attachments']?><br />
    </td>
    </tr>
    
    <tr class="tablerow">
    <td class="altbg1"><?php echo $lang['texttheme']?></td>
    <td class="altbg2"><?php echo $themelist?></td>
    </tr>
    
    <tr class="tablerow">
    <td class="altbg1"><?php echo $lang['forumpermissions']?></td>
    <td class="altbg2"><table style="width: 100%;">
    <?php
    $perms = explode(',', $forum['postperm']);
    $statusList = array(
        'Super Administrator'   => 1,
        'Administrator'         => 2,
        'Super Moderator'       => 4,
        'Moderator'             => 8,
        'Member'                => 16,
        'guest'                 => 32);
     ?>
    <tr>
        <td class="tablerow" style="width: 25ex;">&nbsp;</td>
        <td class="category" style="color: <?php echo $THEME['cattext']?>; font-weight: bold;"><?php echo $lang['polls'];   ?></td>
        <td class="category" style="color: <?php echo $THEME['cattext']?>; font-weight: bold;"><?php echo $lang['threads']; ?></td>
        <td class="category" style="color: <?php echo $THEME['cattext']?>; font-weight: bold;"><?php echo $lang['replies']; ?></td>
        <td class="category" style="color: <?php echo $THEME['cattext']?>; font-weight: bold;"><?php echo $lang['view'];    ?></td>
    </tr>
    <?php
    foreach($statusList as $key=>$val) {
        if(!X_SADMIN and $key == 'Super Administrator') {
            $disabled = 'disabled="disabled"';
        } else {
            $disabled = '';
        }
        ?>
        <tr class="tablerow">
            <td class="category" style="color: <?php echo $THEME['cattext']?>; font-weight: bold;"><?php echo ucwords($key);?></td>
            <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[0][]" value="<?php echo $val;?>" <?php echo ((($perms[X_PERMS_POLL]&$val) == $val) ? 'checked="checked"' : ''); ?> <?php echo $disabled;?> /></td>
            <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[1][]" value="<?php echo $val;?>" <?php echo ((($perms[X_PERMS_THREAD]&$val) == $val) ? 'checked="checked"' : ''); ?> <?php echo $disabled;?> /></td>
            <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[2][]" value="<?php echo $val;?>" <?php echo ((($perms[X_PERMS_REPLY]&$val) == $val) ? 'checked="checked"' : ''); ?> <?php echo $disabled;?> /></td>
            <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[3][]" value="<?php echo $val;?>" <?php echo ((($perms[X_PERMS_VIEW]&$val) == $val) ? 'checked="checked"' : ''); ?> <?php echo $disabled;?> /></td>
        </tr>
        <?php
    }
    ?>
    </table>
    </tr>
    <tr class="tablerow">
    <td class="altbg1"><?php echo $lang['textuserlist']?></td>
    <td class="altbg2"><textarea rows="4" cols="30" name="userlistnew"><?php echo $forum['userlist']?></textarea></td>
    </tr>
    <tr class="tablerow">
    <td class="altbg1"><?php echo $lang['forumpw']?></td>
    <td class="altbg2"><input type="text" name="passwordnew" value="<?php echo htmlspecialchars($forum['password'])?>" /></td>
    </tr>
    <tr class="tablerow">
    <td class="altbg1"><?php echo $lang['textdeleteques']?></td>
    <td class="altbg2"><input type="checkbox" name="delete" value="<?php echo $forum['fid']?>" /></td>
    </tr>
    <tr>
    <td class="altbg2 tablerow" align="center" colspan="2"><input type="submit" name="forumsubmit" value="<?php echo $lang['textsubmitchanges']?>" class="submit" /></td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </form>
    </td>
    </tr>

    <?php
}

function processForumAllChanges()
{
    global $db, $lang, $table_forums;
   
    $queryforum = $db->query("SELECT fid, type FROM $table_forums WHERE type='forum' OR type='sub'");
    $db->query("DELETE FROM $table_forums WHERE name=''");
    while ($forum = $db->fetch_array($queryforum)) {
        $displayorder = "displayorder$forum[fid]";
        $displayorder = formVar($displayorder);
        $name = "name$forum[fid]";
        $name = formVar($name);
        $self['status'] = "status$forum[fid]";
        $self['status'] = formVar($self['status']);
        $delete = "delete$forum[fid]";
        $delete = formInt($delete);
        $moveto = "moveto$forum[fid]";
        $moveto = formVar($moveto);

        if ($delete != "") {
            $db->query("DELETE FROM $table_forums WHERE (type='forum' OR type='sub') AND fid='$delete'");

            $querythread = $db->query("SELECT tid, author FROM $table_threads WHERE fid='$delete'");
            while ($thread = $db->fetch_array($querythread)) {
                $db->query("DELETE FROM $table_threads WHERE tid='$thread[tid]'");
                $db->query("DELETE FROM $table_favorites WHERE tid='$thread[tid]'");
                $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$thread[author]'");

                $querypost = $db->query("SELECT pid, author FROM $table_posts WHERE tid='$thread[tid]'");
                while ($post = $db->fetch_array($querypost)) {
                    $db->query("DELETE FROM $table_posts WHERE pid='$post[pid]'");
                    $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$post[author]'");
                }
                $db->free_result($querypost);
            }
            $db->free_result($querythread);
        }
        $name = addslashes($name);
        $db->query("UPDATE $table_forums SET name='$name', displayorder=".(int)$displayorder.", status='$self[status]', fup=".(int)$moveto." WHERE fid='$forum[fid]'");
    }

    $querygroup = $db->query("SELECT fid FROM $table_forums WHERE type='group'");
    while ($group = $db->fetch_array($querygroup)) {
        $name = "name$group[fid]";
        $name = formVar($name);
        $displayorder = "displayorder$group[fid]";
        $displayorder = formVar($displayorder);
        $self['status'] = "status$group[fid]";
        $self['status'] = formVar($self['status']);
        $delete = "delete$group[fid]";
        $delete = formVar($delete);

        if ($delete != "") {
            $query = $db->query("SELECT fid FROM $table_forums WHERE type='forum' AND fup='$delete'");
            while ($forum = $db->fetch_array($query)) {
                $db->query("UPDATE $table_forums SET fup=0 WHERE type='forum' AND fup='$delete'");
            }

            $db->query("DELETE FROM $table_forums WHERE type='group' AND fid='$delete'");
        }
        $name = addslashes($name);
        $db->query("UPDATE $table_forums SET name='$name', displayorder=".(int)$displayorder.", status='$self[status]' WHERE fid='$group[fid]'");
    }

    $newfname = formVar('newfname');
    $newgstatus = formVar('newfstatus');
    $newffup = formVar('newffup');
    $newforder = formInt('newforder');
    if ($newfname != $lang['textnewforum']) {
        $newfname = addslashes($newfname);
        $db->query("INSERT INTO $table_forums ( type, name, status, lastpost, moderator, displayorder, description, allowhtml, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, password ) VALUES ('forum', '$newfname', '$newfstatus', '', '', ".(int)$newforder.", '', 'no', 'yes', 'yes', '', 0, 0, 0, ".(int)$newffup.", '31,31,31,63', 'yes', 'on', '')");
    }

    $newgname = formVar('newgname');
    $newgstatus = formVar('newgstatus');
    $newgorder = formInt('newgorder');
    if ($newgname != $lang['textnewgroup']) {
        $newgname = addslashes($newgname);
        $db->query("INSERT INTO $table_forums ( type, name, status, lastpost, moderator, displayorder, description, allowhtml, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, password ) VALUES ('group', '$newgname', '$newgstatus', '', '', ".(int)$newgorder.", '', 'no', '', '', '', 0, 0, 0, 0, '', '', '', '')");
    }

    $newsubname = formVar('newsubname');
    $newsubfup = formInt('newsubfup');
    $newsubstatus = formVar('newsubstatus');
    $newsuborder = formInt('newsuborder');
    if ($newsubname != $lang['textnewsubf']) {
        $newsubname = addslashes($newsubname);
        $db->query("INSERT INTO $table_forums ( type, name, status, lastpost, moderator, displayorder, description, allowhtml, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, password ) VALUES ('sub', '$newsubname', '$newsubstatus', '', '', ".(int)$newsuborder.", '', 'no', 'yes', 'yes', '', 0, 0, 0, ".(int)$newsubfup.", '31,31,31,63', 'yes', 'on', '')");
    }

    message($lang['textforumupdate'], false, '', '', 'cp.php?action=forum', false, false, false); 
}

function processForumDetailChanges($fdetails)
{
    global $db, $lang, $table_forums;
    
    $fdetails = intval($fdetails);
    if(!X_SADMIN) {
        $overrule = array(0,0,0,0);
        $forum = $db->fetch_array($db->query("SELECT postperm FROM $table_forums WHERE fid=$fdetails"));
        $parts = explode(',', $forum['postperm']);
        foreach($parts as $p=>$v) {
            if($v & 1 == 1) {
                // super admin status set
                $overrule[$p] = 1;
            }
        }
    } else {
        $overrule = array(0,0,0,0);
    }
    $check_vars = array('allowsmiliesnew', 'allowbbcodenew', 'allowimgcodenew', 'attachstatusnew');
    foreach ($check_vars as $key) {
        if ($$key != 'on' && $$key != 'yes') {
            $$key = 'off';
        }
    }

    $namenew = addslashes($namenew);
    $descnew = addslashes($descnew);
    
    $perms = array(0,0,0,0);
    $permsNew = formArray('permsNew');
    
    foreach($permsNew as $key=>$val) {
        $perms[$key] = array_sum($val);
        $perms[$key] |= $overrule[$key];
    }
    $perms = implode(',', $perms);

    $db->query("UPDATE $table_forums SET name='$namenew', description='$descnew', allowhtml='no', allowsmilies='$allowsmiliesnew', allowbbcode='$allowbbcodenew', theme='$themeforumnew', userlist='$userlistnew', postperm='$perms', allowimgcode='$allowimgcodenew', attachstatus='$attachstatusnew', password='$passwordnew' WHERE fid=$fdetails");
    
    $delete = formInt('delete');
    if ($delete > 0) {
        $db->query("DELETE FROM $table_forums WHERE fid='$delete'");
    }

    message($lang['textforumupdate'], false, '</td></tr></table></td></tr></table><br />', '', 'cp.php?action=forum', false, false, false);    
}

function processForumChanges()
{
    global $oToken;
    
    $oToken->isValidToken();
    
    $fdetails = formInt('fdetails');
    
    if ($fdetails > 0) {
        processForumDetailChanges($fdetails);        
    } else {
        processForumAllChanges();
    }
}

?>