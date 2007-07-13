<?php
/* $Id: cp_moderators.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
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

function displayModerationPanel()
{
    global $oToken, $lang, $THEME; 
    ?>

    <tr class="altbg2">
    <td>
    <form method="post" action="cp.php?action=mods">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textforum']?></font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textmoderator']?></font></strong></td>
    </tr>

    <?php
    $oldfid = 0;
    $query = $db->query("SELECT f.moderator, f.name, f.fid, c.name as cat_name, c.fid as cat_fid FROM $table_forums f LEFT JOIN $table_forums c ON (f.fup = c.fid) WHERE (c.type='group' AND f.type='forum') OR (f.type='forum' AND f.fup='') ORDER BY c.displayorder, f.displayorder");
    while ($forum = $db->fetch_array($query)) {
        if ($oldfid != $forum['cat_fid']) {
            $oldfid = $forum['cat_fid']
            ?>
            <tr class="altbg1 tablerow">
            <td colspan="2"><strong><?php echo stripslashes($forum['cat_name'])?></strong></td>
            </tr>
            <?php
        }
        ?>

        <tr class="altbg2 tablerow">
        <td><?php echo stripslashes($forum['name'])?></td>
        <td><input type="text" name="mod[<?php echo $forum['fid']?>]"" value="<?php echo $forum['moderator']?>" /></td>
        </tr>

        <?php
        $querys = $db->query("SELECT name, fid, moderator FROM $table_forums WHERE fup='$forum[fid]' AND type='sub'");
        while ($sub = $db->fetch_array($querys)) {
            ?>
            <tr class="altbg2 tablerow">
            <td><?php echo $lang['4spaces']?><?php echo $lang['4spaces']?><em><?php echo stripslashes($sub['name'])?></em></td>
            <td><input type="text" name="mod[<?php echo $sub['fid']?>]"" value="<?php echo $sub['moderator']?>" /></td>
            </tr>
            <?php
        }
    }
    ?>
    <tr>
    <td colspan="2" class="tablerow altbg1"><span class="smalltxt"><?php echo $lang['multmodnote']?></span></td>
    </tr>
    <tr>
    <td align="center" colspan="2" class="tablerow altbg2"><input type="submit" class="submit" name="modsubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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

function processModerators()
{
    global $lang, $db, $table_forums, $oToken;
    
    $oToken->isValidToken();
    
    $mod = formArray('mod');
    
    if (!empty($mod)) {
        foreach ($mod as $fid=>$mods) {
            $db->query("UPDATE $table_forums SET moderator='$mods' WHERE fid='$fid'");
        }
    }

    echo '<tr class="tablerow altbg2"><td align="center">'.$lang['textmodupdate'].'</td></tr>';
    message($lang['censorupdate'], false, '', '', 'cp2.php', false, false, false); // TODO
}

?>
