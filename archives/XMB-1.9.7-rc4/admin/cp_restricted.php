<?php
/* $Id: cp_restricted.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
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

function displayRestrictedPanel() {
    global $THEME, $lang, $db, $oToken, $table_restricted;
    
    ?>

    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp2.php">
    <input type="hidden" name="action" value="restrictions">
    <?php echo $oToken->getToken(1); ?>
    <table align="center" border="0" cellspacing="0" cellpadding="0" width="80%">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td>
    <span class="smalltxt">
    <strong>
    <font style="color: <?php echo $THEME['cattext']?>">
    <?php echo $lang['textdeleteques']?>
    </font>
    </strong>
    </span>
    </td>
    <td>
    <span class="smalltxt">
    <strong>
    <font style="color: <?php echo $THEME['cattext']?>">
    <?php echo $lang['restrictedname']?>
    </font>
    </strong>
    </span>
    </td>
    <td>
    <span class="smalltxt">
    <strong>
    <font style="color: <?php echo $THEME['cattext']?>">
    <?php echo $lang['case_sensitive']?>
    </font>
    </strong>
    </span>
    </td>
    <td>
    <span class="smalltxt">
    <strong>
    <font style="color: <?php echo $THEME['cattext']?>">
    <?php echo $lang['partial_match']?>
    </font>
    </strong>
    </span>
    </td>
    </tr>

    <?php

    $query = $db->query("SELECT * FROM $table_restricted ORDER BY id");
    while ($restricted = $db->fetch_array($query)) {
        if ($restricted['case_sensitivity'] == 1) {
            $case_check = 'checked="checked"';
        } else {
            $case_check = '';
        }

        if ($restricted['partial'] == 1) {
            $partial_check = 'checked="checked"';
        } else {
            $partial_check = '';
        }
        ?>
        <tr class="tablerow">
        <td class="altbg2"><input type="checkbox" name="delete<?php echo $restricted['id']?>" value="<?php echo $restricted['id']?>" /></td>
        <td class="altbg2"><input type="text" size="30" name="name<?php echo $restricted['id']?>" value="<?php echo $restricted['name']?>" /></td>
        <td class="altbg2"><input type="checkbox" name="case<?php echo $restricted['id']?>" value="<?php echo $restricted['id']?>" <?php echo $case_check?> /></td>
        <td class="altbg2"><input type="checkbox" name="partial<?php echo $restricted['id']?>" value="<?php echo $restricted['id']?>" <?php echo $partial_check?> /></td>
        </tr>
        <?php
    }
    ?>
    <tr>
    <td class="altbg2" colspan="4"><img src="<?php echo ROOT?>images/pixel.gif" alt="" /></td>
    </tr>
    <tr class="tablerow">
    <td class="altbg2" colspan="4" align="left">
    <table border="0" width="100%">
    <tr class="category">
    <td colspan="2"><span class="smalltxt">
    <strong>
    <font style="color: <?php echo $THEME['cattext']?>">
    <?php echo $lang['textnewcode']?>
    </font>
    </strong>
    </span>
    </td>
    </tr>
    <tr class="tablerow">
    <td colspan="2"><span class="smalltxt">
    <?php echo $lang['newrestriction']?>
    </span>
    </td>
    </tr>
    <tr>
    <td colspan="2">
    <span class="smalltxt">
    <?php echo $lang['newrestrictionwhy']?></span></td>
    </tr>
    <tr>
    <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
    <td><span class="smalltxt">name:</span></td>
    <td><input type="text" size="30" name="newname" /></td>
    </tr>
    <tr>
    <td><span class="smalltxt"><?php echo $lang['case_sensitive']?>:</span></td>
    <td><input type="checkbox" name="newcase" value="1" checked="unchecked" /></td>
    </tr>
    <tr>
    <td><span class="smalltxt"><?php echo $lang['partial_match']?>:</span></td>
    <td><input type="checkbox" name="newpartial" value="1" checked="checked" /></td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table><br />
    <div align="center">
    <input class="submit" type="submit" name="restrictedsubmit" value="<?php echo $lang['textsubmitchanges']?>" />
    </div>
    </form>
    </td>
    </tr>
    <?php
}

function processRestrictedPanel() {
    global $db, $table_restricted, $lang, $oToken;
    
    $oToken->isValidToken();

    $queryrestricted = $db->query("SELECT id FROM $table_restricted");
    while ($restricted = $db->fetch_array($queryrestricted)) {
        $name = '';
        $delete = '';
        $case = '';
        $partial = '';

        $name = $db->escape(formVar('name' . $restricted['id']));
        $delete = formInt('delete' . $restricted['id']);
        $case = formVar('case' . $restricted['id']);
        $partial = formVar('partial' . $restricted['id']);

        if ($partial != '') {
            $partial = 1;
        } else {
            $partial = 0;
        }

        if ($case != '') {
            $case = 1;
        } else {
            $case = 0;
        }

        if ($delete != "") {
            $db->query("DELETE FROM $table_restricted WHERE id='$delete'");
            continue;
        }
        $db->query("UPDATE `$table_restricted` SET `name`='$name', `case_sensitivity`='$case', `partial`='$partial' WHERE `id`='$restricted[id]'");
    }

    $newname = $db->escape(formVar('newname'));
    $newpartial = formVar('newpartial');
    $newcase = formVar('newcase');

    if ($newname != "") {
        if (!$newpartial || $newpartial != 1) {
            $newpartial = 0;
        } else {
            $newpartial = 1;
        }
        if (!$newcase || $newcase != 1) {
            $newcase = 0;
        } else {
            $newcase = 1;
        }
        $db->query("INSERT INTO $table_restricted (`name`, `case_sensitivity`, `partial`) VALUES ('$newname', $newcase, $newpartial)");
    }

    echo '<tr class="tablerow altbg2"><td align="center">' . $lang['restrictedupdate'] . '</td></tr>';
    message($lang['censorupdate'], false, '', '', 'cp2.php', false, false, false); // TODO

    redirect('cp2.php?action=restrictions', 2);
}

?>
