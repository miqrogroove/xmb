<?php
/* $Id: cp_smilie.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
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

function displaySmiliePanel() {
    global $db, $lang, $SETTINGS, $THEME, $oToken, $table_smilies;
?>

    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp2.php?action=smilies">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr>
    <td class="category" colspan="4" align="left"><font style="color: <?php echo $THEME['cattext']?>"><strong><?php echo $lang['smilies']?></strong></font></td>
    </tr>
    <tr class="header">
    <td align="center"><?php echo $lang['textdeleteques']?></td>
    <td><?php echo $lang['textsmiliecode']?></td>
    <td><?php echo $lang['textsmiliefile']?></td>
    <td align="center"><?php echo $lang['smilies']?></td>
    </tr>

    <?php

    $query = $db->query("SELECT code, id, url FROM $table_smilies WHERE type='smiley'");
    while ($smilie = $db->fetch_array($query)) {
?>
        <tr>
        <td align="center" class="tablerow altbg2"><input type="checkbox" name="smdelete[<?php echo $smilie['id']?>]" value="1" /></td>
        <td class="altbg2 tablerow"><input type="text" name="smcode[<?php echo $smilie['id']?>]" value="<?php echo $smilie['code']?>" /></td>
        <td class="altbg2 tablerow"><input type="text" name="smurl[<?php echo $smilie['id']?>]" value="<?php echo $smilie['url']?>" /></td>
        <td align="center" class="tablerow altbg2"><img src="<?php echo $THEME['smdir']?>/<?php echo $smilie['url']?>" alt="<?php echo $smilie['code']?>" /></td>
        </tr>
        <?php

    }

    $db->free_result($query);
?>
    <tr>
    <td class="altbg2" colspan="4"><img src="./images/pixel.gif" alt="" /></td>
    </tr>
    <tr class="altbg1 tablerow">
    <td><?php echo $lang['textnewsmilie']?></td>
    <td><input type="text" name="newcode" /></td>
    <td colspan="2"><input type="text" name="newurl1" /></td>
    </tr>
    <tr>
    <td class="altbg2" colspan="4" align="left"><img src="<?php echo ROOT?>images/pixel.gif" alt="" /></td>
    </tr>
    <tr>
    <td colspan="4" class="header"><?php echo $lang['picons']?></td>
    </tr>
    <tr class="header">
    <td align="center"><?php echo $lang['textdeleteques']?></td>
    <td colspan="2" align="left"><?php echo $lang['textsmiliefile']?></td>
    <td align="center"><?php echo $lang['picons']?></td>
    </tr>

    <?php

    $query = $db->query("SELECT * FROM $table_smilies WHERE type='picon' ORDER BY id");
    while ($smilie = $db->fetch_array($query)) {
?>

        <tr>
        <td align="center" class="tablerow altbg2"><input type="checkbox" name="pidelete[<?php echo $smilie['id']?>]" value="1" /></td>
        <td colspan="2" align="left" class="altbg2 tablerow"><input type="text" name="piurl[<?php echo $smilie['id']?>]" value="<?php echo $smilie['url']?>" /></td>
        <td align="center" class="tablerow altbg2"><img src="<?php echo $THEME['smdir']?>/<?php echo $smilie['url']?>" alt="<?php echo $smilie['url']?>" /></td>
        </tr>

        <?php

    }
    $db->free_result($query);
?>

    <tr>
    <td class="altbg2" colspan="4"><img src="<?php echo ROOT?>images/pixel.gif" alt="" /></td>
    </tr>
    <tr class="altbg1 tablerow">
    <td colspan="4" align="left"><?php echo $lang['textnewpicon']?>&nbsp;&nbsp;<input type="text" name="newurl2" /></td>
    </tr>
    <tr>
    <td align="center" class="tablerow altbg2" colspan="4"><input type="submit" class="submit" name="smiliesubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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

function processSmiliePanel() {
    global $db, $lang, $THEME, $SETTINGS, $oToken, $table_smilies;

    $oToken->isValidToken();

    $smcode = formArray('smcode');
    $smdelete = formArray('smdelete');
    $smurl = formArray('smurl');
    
    if (is_array($smcode)) {
        foreach ($smcode as $key => $val) {
            if (count(array_keys($smcode, $val)) > 1) {
                error($lang['smilieexists'], false, '</td></tr></table></td></tr></table><br />');
            }
        }
    }
    
    $querysmilie = $db->query("SELECT id FROM $table_smilies WHERE type='smiley'");
    while ($smilie = $db->fetch_array($querysmilie)) {
        $id = $smilie['id'];
        if (isset ($smdelete[$id]) && $smdelete[$id] == 1) {
            $query = $db->query("DELETE FROM $table_smilies WHERE id='$id'");
            continue;
        }
        $query = $db->query("UPDATE $table_smilies SET code='$smcode[$id]', url='$smurl[$id]' WHERE id='$smilie[id]' AND type='smiley'");
    }

    $pidelete = formArray('pidelete');
    $picon = formArray('picon');
    $piurl = formArray('piurl');
    
    if (is_array($piurl)) {
        foreach ($piurl as $key => $val) {
            if (count(array_keys($piurl, $val)) > 1) {
                error($lang['piconexists'], false, '</td></tr></table></td></tr></table><br />');
            }
        }
    }
    
    $querysmilie = $db->query("SELECT id FROM $table_smilies WHERE type='picon'");
    
    while ($picon = $db->fetch_array($querysmilie)) {
        $id = $picon['id'];
        if (isset ($pidelete[$id]) && $pidelete[$id] == 1) {
            $query = $db->query("DELETE FROM $table_smilies WHERE id='$picon[id]'");
            continue;
        }
        $query = $db->query("UPDATE $table_smilies SET url='$piurl[$id]' WHERE id='$picon[id]' AND type='picon'");
    }

    $newcode = trim(formVar('newcode'));
    $newurl1 = trim(formVar('newurl1'));
    if (!empty($newcode)) {
        // make sure we don't already have one like that
        if ($db->result($db->query("SELECT count(id) FROM $table_smilies WHERE code='$newcode'"), 0) > 0) {
            error($lang['smilieexists'], false, '</td></tr></table></td></tr></table><br />');
        }
        $query = $db->query("INSERT INTO $table_smilies (type, code, url) VALUES ('smiley', '$newcode', '$newurl1')");
    }

    $newurl2 = trim(formVar('newurl2'));
    if (!empty($newurl2)) {
        if ($db->result($db->query("SELECT count(id) FROM $table_smilies WHERE url='$newurl2' AND type='picon'"), 0) > 0) {
            error($lang['piconexists'], false, '</td></tr></table></td></tr></table><br />');
        }
        $query = $db->query("INSERT INTO $table_smilies (type, code, url) VALUES ('picon', '', '$newurl2')");
    }

    message($lang['smilieupdate'], false, '</td></tr></table></td></tr></table><br />', '', 'cp2.php', false, false, false); 
}

?>