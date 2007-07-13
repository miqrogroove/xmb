<?php
/* $Id: cp_censor.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
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

function displayCensorPanel() {
    global $db, $lang, $THEME, $SETTINGS;
    global $table_words, $oToken;
?>

    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp2.php?action=censor">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="450" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td width="4%" align="center"><font style="color: <?php echo $THEME['cattext']?>"><strong><?php echo $lang['textdeleteques']?></strong></font></td>
    <td align="left"><font style="color: <?php echo $THEME['cattext']?>"><strong><?php echo $lang['textcensorfind']?></strong></font></td>
    <td align="left"><font style="color: <?php echo $THEME['cattext']?>"><strong><?php echo $lang['textcensorreplace']?></strong></font></td>
    </tr>

    <?php

    $query = $db->query("SELECT * FROM $table_words ORDER BY id");
    while ($censor = $db->fetch_array($query)) {
        ?>
        <tr class="altbg2 tablerow">
        <td align="center"><input type="checkbox" name="delete<?php echo $censor['id']?>" value="<?php echo $censor['id']?>" /></td>
        <td align="left"><input type="text" size="20" name="find<?php echo $censor['id']?>" value="<?php echo $censor['find']?>" /></td>
        <td align="left"><input type="text" size="20" name="replace<?php echo $censor['id']?>" value="<?php echo $censor['replace1']?>" /></td>
        </tr>
        <?php
    }
    $db->free_result($query);
    ?>
    <tr class="altbg2">
    <td colspan="3"><img src="<?php echo ROOT?>images/pixel.gif" alt="" /></td>
    </tr>
    <tr class="altbg1 tablerow">
    <td align="center"><strong><?php echo $lang['textnewcode']?></strong></td>
    <td align="left"><input type="text" size="20" name="newfind" /></td>
    <td align="left"><input type="text" size="20" name="newreplace" /></td>
    </tr>
    <tr>
    <td align="center" colspan="3" class="tablerow altbg2"><input type="submit" class="submit" name="censorsubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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

function processCensorPanel() {
    global $db, $lang, $table_words, $oToken;
    
    $oToken->isValidToken();

    $querycensor = $db->query("SELECT id FROM $table_words");

    while ($censor = $db->fetch_array($querycensor)) {
        $find = formVar("find" . $censor['id']);
        $replace = formVar("replace" . $censor['id']);
        $delete = formInt("delete" . $censor['id']);

        if ($delete > 0) {
            $db->query("DELETE FROM $table_words WHERE id='$delete'");
        }

        $db->query("UPDATE $table_words SET find='$find', replace1='$replace' WHERE id='$censor[id]'");
    }

    $db->free_result($querycensor);

    $newfind = trim(formVar('newfind'));
    if (!empty($newfind)) {
        $db->query("INSERT INTO $table_words ( find, replace1 ) VALUES ('$newfind', '$newreplace')");
    }

    message($lang['censorupdate'], false, '', '', 'cp2.php', false, false, false);
}
?>
