<?php
/* $Id: cp_rank.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
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

function displayRankPanel() {
    global $THEME, $db, $lang, $SETTINGS;
?>

    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp2.php?action=ranks">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="650" align="center">
    <tr>
    <td bgcolor="<?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr>
    <td class="category" align="center"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textdeleteques']?></font></strong></td>
    <td class="category" align="left"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textcusstatus']?></font></strong></td>
    <td class="category"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textposts']?></font></strong></td>
    <td class="category"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textstars']?></font></strong></td>
    <td class="category"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textallowavatars']?></font></strong></td>
    <td class="category"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textavatar']?></font></strong></td>
    </tr>

    <?php

    $avatarno = $avataryes = '';
    $query = $db->query("SELECT * FROM $table_ranks ORDER BY id");
    while ($rank = $db->fetch_array($query)) {
        if ($rank['title'] == 'Super Administrator' || $rank['title'] == 'Administrator' || $rank['title'] == 'Super Moderator' || $rank['title'] == 'Moderator') {
            $staff_disable = 'disabled';
        } else {
            $staff_disable = '';
        }

        if ($rank['allowavatars'] == 'yes') {
            $avataryes = "selected=\"selected\"";
        } else {
            $avatarno = "selected=\"selected\"";
        }
?>

        <tr class="altbg2 tablerow">
        <td class="tablerow" align="center"><input type="checkbox" name="delete[<?php echo $rank['id']?>]" value="1" <?php echo $staff_disable?> /></td>
        <td class="tablerow" align="left"><input type="text" name="title[<?php echo $rank['id']?>]" value="<?php echo $rank['title']?>" <?php echo $staff_disable?>/></td>
        <td class="tablerow"><input type="text" name="posts[<?php echo $rank['id']?>]" value="<?php echo $rank['posts']?>" <?php echo $staff_disable?> size="5" /></td>
        <td class="tablerow"><input type="text" name="stars[<?php echo $rank['id']?>]" value="<?php echo $rank['stars']?>" size="4" /></td>
        <td class="tablerow"><select name="allowavatars[<?php echo $rank['id']?>]">
        <option value="yes" <?php echo $avataryes?>><?php echo $lang['texton']?></option>
        <option value="no" <?php echo $avatarno?>><?php echo $lang['textoff']?></option>
        </select><input type="hidden" name="id[<?php echo $rank['id']?>]" value="<?php echo $rank['id']?>" /></td>
        <td class="tablerow"><input type="text" name="avaurl[<?php echo $rank['id']?>]" value="<?php echo $rank['avatarrank']?>" size="20" /></td>
        </tr>

        <?php

        $avataryes = "";
        $avatarno = "";
    }
?>

    <tr class="altbg2"><td colspan="6"> </td></tr>
    <tr class="altbg1 tablerow">
    <td colspan="2"><?php echo $lang['textnewrank']?>&nbsp;&nbsp;<input type="text" name="newtitle" /></td>
    <td class="tablerow"><input type="text" name="newposts" size="5" /></td>
    <td class="tablerow"><input type="text" name="newstars" size="4" /></td>
    <td class="tablerow"><select name="newallowavatars"><option value="yes"><?php echo $lang['texton']?></option>
    <option value="no"><?php echo $lang['textoff']?></option></select></td>
    <td class="tablerow"><input type="text" name="newavaurl" size="20" /></td>
    </tr>
    <tr>
    <td align="center" colspan="6" class="tablerow altbg2"><input type="submit" name="rankssubmit" class="submit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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

function processRanks() {
    global $db, $lang, $table_ranks, $oToken;
    
    $oToken->isValidToken();

    $stars = formArray('stars');
    $id = formArray('id');
    $delete = formArray('delete');
    $title = formArray('title');
    $posts = formArray('posts');
    $allowavatars = formArray('allowavatars');
    $avatarrank = formArray('avaurl');

    $query = $db->query("SELECT * FROM $table_ranks");
    while ($ranks = $db->fetch_array($query)) {
        if ($ranks['title'] == 'Super Administrator' || $ranks['title'] == 'Administrator' || $ranks['title'] == 'Super Moderator' || $ranks['title'] == 'Moderator') {
            $title[$ranks['id']] = $ranks['title'];
            $posts[$ranks['id']] = 0;
            if (0 === (intval($stars[$ranks['id']]))) {
                $stars[$ranks['id']] = 1;
            }
        }
    }

    foreach ($id as $key => $val) {
        if ($delete[$key] == 1) {
            $db->query("DELETE FROM $table_ranks WHERE id='$key'");
            continue;
        }

        $db->query("UPDATE $table_ranks SET title='$title[$key]', posts='$posts[$key]', stars='$stars[$key]', allowavatars='$allowavatars[$key]', avatarrank='$avaurl[$key]' WHERE id='$key'");
    }

    $newtitle = $db->escape(formVar('newtitle'));
    $newposts = $db->escape(formVar('newposts'));
    $newstars = $db->escape(formVar('newstars'));
    $newallowavatars = $db->escape(formVar('newallowavatars'));
    $newavaurl = $db->escape(formVar('newavaurl'));
    if ($newtitle != "") {
        $db->query("INSERT INTO $table_ranks ( title, posts, stars, allowavatars, avatarrank ) VALUES ('$newtitle', '$newposts', '$newstars', '$newallowavatars', '$newavaurl')");
    }

    echo '<tr class="tablerow altbg2"><td align="center">' . $lang['rankingsupdate'] . '</td></tr>';
    message($lang['censorupdate'], false, '', '', 'cp2.php', false, false, false); // TODO
}

?>
