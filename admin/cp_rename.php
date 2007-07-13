<?php
/* $Id: cp_rename.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
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

function displayRenamePanel()
{
    global $lang, $THEME, $oToken;
    
    // Display the rename user form
    ?>
    <tr class="altbg2">
    <td>
    <form action="cp.php?action=rename" method="post">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr>
    <td class="category" colspan="2"><strong><font color="<?php echo $THEME['cattext']?>"><?php echo $lang['admin_rename_txt']?></font></strong></td>
    </tr>
    <tr>
    <td class="altbg1 tablerow" width="22%"><?php echo $lang['admin_rename_userfrom']?></td>
    <td class="altbg2 tablerow"><input type="text" name="frmUserFrom" size="25" /></td>
    </tr>
    <tr>
    <td class="altbg1 tablerow" width="22%"><?php echo $lang['admin_rename_userto']?></td>
    <td class="altbg2 tablerow"><input type="text" name="frmUserTo" size="25" /></td>
    </tr>
    <tr>
    <td class="altbg2 tablerow" colspan="2" align="center"><input type="submit" class="submit" name="renamesubmit" value="<?php echo $lang['admin_rename_txt']?>" /></td>
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

function processRenameMember()
{
    global $oToken, $lang;
    
    $oToken->isValidToken();
        
    $vUserFrom = trim(formVar('frmUserFrom'));
    $vUserTo = trim(formVar('frmUserTo'));

    $adm = new admin();
    $myErr = $adm->rename_user($vUserFrom, $vUserTo);
    echo '<tr class="tablerow altbg2"><td align="center">'.$myErr.'</td></tr>';
    message($lang['censorupdate'], false, '', '', 'cp2.php', false, false, false); // TODO
}

?>
