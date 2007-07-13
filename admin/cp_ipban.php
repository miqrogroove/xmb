<?php
/* $Id: cp_ipban.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
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

function displayIPBanPanel() {
    global $THEME, $lang, $onlineip, $oToken;

    echo '<tr class="altbg2"><td align="center">';
    echo '<form name="ipban" method="post" action="cp.php?action=ipban">';
    echo '<?php echo $oToken->getToken(1); ?>';
    echo '<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">';
    echo '<tr><td style="background-color: ' . $THEME['bordercolor'] . '">';
    echo '<table border="0" cellspacing="' . $THEME['borderwidth'] . '" cellpadding="' . $THEME['tablespace'] . '" width="100%"><tr class="category">';

    echo '<td><font style="color: ' . $THEME['cattext'] . '; font-weight: bold;">' . $lang['textdeleteques'] . '</font></td>';
    echo '<td><font style="color: ' . $THEME['cattext'] . '; font-weight: bold;">' . $lang['textip'] . ':</font></td>';
    echo '<td><font style="color: ' . $THEME['cattext'] . '; font-weight: bold;">' . $lang['textipresolve'] . ':</font></td>';
    echo '<td><font style="color: ' . $THEME['cattext'] . '; font-weight: bold;">' . $lang['textadded'] . '</font></td></tr>';

    $query = $db->query("SELECT * FROM $table_banned ORDER BY dateline");
    while ($ipadr = $db->fetch_array($query)) {

        for ($i = 1; $i <= 4; ++ $i) {
            $j = "ip" . $i;
            if ($ipadr[$j] == -1) {
                $ipadr[$j] = "*";
            }
        }
        $ipdate = printGmDate($ipadr['dateline']) . '&nbsp;' . $lang['textat'] . '&nbsp;' . printGmTime($ipadr['dateline']);
        $theip = "$ipadr[ip1].$ipadr[ip2].$ipadr[ip3].$ipadr[ip4]";

        echo '<tr class="altbg1"><td class="tablerow" align="center"><input type="checkbox" name="delete[' . $ipadr['id'] . ']" value="1" /></td>';
        echo '<td class="tablerow">' . $theip . '</td>';
        echo '<td class="tablerow">' . @gethostbyaddr($theip) . '</td>';
        echo '<td class="tablerow">' . $ipdate . '</td></tr>';
    }

    $query = $db->query("SELECT id FROM $table_banned WHERE (ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')");
    $result = $db->fetch_array($query);
    if ($result) {
        $warning = $lang['ipwarning'];
    } else {
        $warning = '';
    }
    $newipmsg = $lang['textnewip'];

    echo '</table></td></tr></table><br />'; // End Current IP List

    echo '<table cellspacing="0" cellpadding="0" border="0" width="550" align="center">';
    echo '<tr><td style="background-color: ' . $THEME['bordercolor'] . '">';
    echo '<table border="0" cellspacing="' . $THEME['borderwidth'] . '" cellpadding="' . $THEME['tablespace'] . '" width="100%">';
    echo '<tr style="background-color: ' . $THEME['bordercolor'] . '">';
    echo '<td colspan="4" class="category"><font style="color: ' . $THEME['cattext'] . '; font-weight: bold;">' . $newipmsg . '</font></td></tr>';
    echo '<tr><td class="altbg2"><input type="text" name="newip1" size="3" maxlength="3" onKeyUp="return autotab(this, document.ipban.newip2);" />.<input type="text" name="newip2" size="3" maxlength="3" onKeyUp="return autotab(this, document.ipban.newip3);" />.<input type="text" name="newip3" size="3" maxlength="3" onKeyUp="return autotab(this, document.ipban.newip4);" />.<input type="text" name="newip4" size="3" maxlength="3"  /></td>';
    echo '<td class="altbg2" colspan="3"><font class="smalltxt">' . $lang['multipnote'] . '</font></td></tr>';
    echo '<tr><td class="altbg2" colspan="4" align="center"><font class="smalltxt">' . $lang['currentip'] . '&nbsp;<strong>' . $onlineip . '</strong>' . $warning . '</font></td>';
    echo '</tr></table></td></tr></table><br />';
    echo '<div align="center"><input type="submit" class="submit" name="ipbansubmit" value="' . $lang['textsubmitchanges'] . '" /></div></form></td></tr>'; // Submit changes, and end new IPs

}

function processIPBan() {
    global $oToken, $lang;
    
    $oToken->isValidToken();
    
    $delete = formArray('delete');
    if (!empty($delete)) {
        $dels = array ();
        foreach ($delete as $id => $del) {
            if ($del == 1) {
                $dels[] = (int)$id;
            }
        }
        if (count($dels) > 0) {
            $dels = implode(',', $dels);
            $db->query("DELETE FROM $table_banned WHERE id IN($dels)");
        }
    }
    $self['status'] = $lang['textipupdate'];

    $newip1 = formVar('newip1');
    $newip2 = formVar('newip2');
    $newip3 = formVar('newip3');
    $newip4 = formVar('newip4');

    if ($newip1 != "" || $newip2 != "" || $newip3 != "" || $newip4 != "") {
        $invalid = 0;

        for ($i = 1; $i <= 4 && !$invalid; ++ $i) {
            $newip = "newip$i";
            $newip = trim(formVar($newip));

            if ($newip == "*") {
                $ip[$i] = -1;
            } elseif (preg_match(" #^[0-9]+$#", $newip)) {
                $ip[$i] = $newip;
            } else {
                $invalid = 1;
            }
        }

        if ($invalid) {
            $self['status'] = $lang['invalidip'];
        } else {
            if ($ip[1] == '-1' && $ip[2] == '-1' && $ip[3] == '-1' && $ip[4] == '-1') {
                $self['status'] = $lang['impossiblebanall'];
            } else {
                $query = $db->query("SELECT id FROM $table_banned WHERE (ip1='$ip[1]' OR ip1='-1') AND (ip2='$ip[2]' OR ip2='-1') AND (ip3='$ip[3]' OR ip3='-1') AND (ip4='$ip[4]' OR ip4='-1')");
                $result = $db->fetch_array($query);
                if ($result) {
                    $self['status'] = $lang['existingip'];
                } else {
                    $query = $db->query("INSERT INTO $table_banned (ip1, ip2, ip3, ip4, dateline) VALUES ('$ip[1]', '$ip[2]', '$ip[3]', '$ip[4]', $onlinetime)");
                }
            }
        }
    }

    message($self['status'], false, '</td></tr></table></td></tr></table><br />', '', 'cp2.php', false, false, false);
}
?>