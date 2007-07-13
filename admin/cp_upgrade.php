<?php

/* $Id: cp_upgrade.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
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

function processUpgrade() {
    global $oToken, $tablepre, $allow_spec_q, $lang, $THEME, $db;
    global $SETTINGS, $navigation, $versionlong, $copyright, $footerads;
    global $footerstuff;
    
    $oToken->isValidToken();
    
    $upgrade = formVar('upgrade');
    
    if (isset($_FILES['sql_file'])) {
        $add = get_attached_file($_FILES['sql_file'], 'on');
        if ($add !== false) {
            $upgrade .= $add;
        }
    }

    $upgrade = str_replace('$table_', $tablepre, $upgrade);

    $explode = explode(";", $upgrade);
    $count = count($explode);

    if (strlen(trim($explode[$count -1])) == 0) {
        unset ($explode[$count -1]);
        $count--;
    }

    echo "</table></td></tr></table>";

    for ($num = 0; $num < $count; $num++) {
        $explode[$num] = stripslashes($explode[$num]);

        if ($allow_spec_q !== true) {
            if (strtoupper(substr(trim($explode[$num]), 0, 3)) == 'USE' || strtoupper(substr(trim($explode[$num]), 0, 14)) == 'SHOW DATABASES') {
                error($lang['textillegalquery'], false, '</td></tr></table></td></tr></table><br />');
            }
        }

        if ($explode[$num] != "") {
            $query = $db->query($explode[$num], true);
        }

        echo '<br />';
?>

            <table cellspacing="0" cellpadding="0" border="0" style="width: <?php echo $THEME['tablewidth']?>" align="center">
            <tr>
            <td style="background-color: <?php echo $THEME['bordercolor']?>">
            <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
            <tr class="altbg2 tablerow">
            <td colspan="<?php echo $db->num_fields($query)?>"><strong><?php echo $lang['upgraderesults']?></strong>&nbsp;<?php echo $explode[$num]?>

            <?php

        $xn = strtoupper($explode[$num]);
        if (strpos($xn, 'SELECT') !== false || strpos($xn, 'SHOW') !== false || strpos($xn, 'EXPLAIN') !== false || strpos($xn, 'DESCRIBE') !== false) {
            dump_query($query, true);
        } else {
            $selq = false;
        }
?>

            </td>
            </tr>
            </td>
            </tr>
            </table>
            </td>
            </tr>
            </table>

            <?php

    }
?>

        <br />
        <table cellspacing="0" cellpadding="0" border="0" style="width: <?php echo $THEME['tablewidth']?>" align="center">
        <tr>
        <td style="background-color: <?php echo $THEME['bordercolor']?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
        
        <?php
        end_time();
        message($lang['upgradesuccess'], false, '', '', false, true, false, true); // TODO
        ?>
        
        </table>
        </td>
        </tr>
        </table>
        <?php    
}

function displayUpgradePanel() {
    global $THEME, $lang, $oToken;
    
?>

        <tr class="altbg2">
        <td align="center">
        <form method="post" action="cp.php?action=upgrade" enctype="multipart/form-data">
        <?php echo $oToken->getToken(1); ?>
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td style="background-color: <?php echo $THEME['bordercolor']?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
        <tr>
        <td class="tablerow altbg1" colspan="2"><strong><?php echo $lang['textupgrade']?></strong></td>
        </tr>
        <tr>
        <td class="altbg2 tablerow" colspan="2"><?php echo $lang['upgrade']?></td>
        </tr>
        <tr>
        <td class="altbg1 tablerow" valign="top"><textarea cols="85" rows="10" name="upgrade"></textarea></td>
        </tr>
        <tr>
        <td class="altbg2 tablerow" colspan="2"><input type="file" name="sql_file" /></td>
        </tr>
        <tr>
        <td class="altbg1 tablerow" colspan="2"><?php echo $lang['upgradenote']?></td>
        </tr>
        <tr>
        <td class="ctrtablerow altbg2" colspan="2"><input type="submit" class="submit" name="upgradesubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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
?>
