<?php
/* $Id: cp_log.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
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

function splitModLogData($username, $action, $data, $dateline) {
    global $lang;

    $res = array ();
    $ip = '';
    $details = array (
        '<table class="dataTable">'
    );
    $data = unserialize(base64_decode($data));

    foreach ($data as $key => $val) {
        if ($key == 'ip') {
            $ip = $val;
        }
        $details[] = '<tr><td class="dataKey">' . $key . ': </td><td class="dataVal">' . nl2br(htmlentities($val)) . '</td></tr>';
    }
    $details[] = '</table>';

    $res['details'] = implode("\n", $details);
    $res['ip'] = $ip;
    $res['username'] = $username;
    $res['dateline'] = printGmDate($dateline) . ' ' . $lang['textat'] . ' ' . printGmTime($dateline);

    switch ($action) {
        case 'useModDelete' :
            $res['action'] = $lang['deletethread'];
            break;

        case 'useModOpen' :
        case 'useModClose' :
            $res['action'] = ($action == 'useModOpen' ? $lang['textopenthread'] : $lang['textclosethread']);
            break;

        case 'useModMove' :
            $res['action'] = $lang['textmovemethod1'];
            break;

        case 'useModUntop' :
        case 'useModTop' :
            $res['action'] = ($action == 'useModUntop' ? $lang['textuntopthread'] : $lang['texttopthread']);
            break;

        case 'useModBump' :
            $res['action'] = $lang['textbumpthread'];
            break;

        case 'useModEmpty' :
            $res['action'] = $lang['textemptythread'];
            break;

        case 'useModSplit' :
            $res['action'] = $lang['textsplitthread'];
            break;

        case 'useModMerge' :
            $res['action'] = $lang['textmergethread'];
            break;

        case 'useModPrune' :
            $res['action'] = $lang['textprunethread'];
            break;

        case 'useModCopy' :
            $res['action'] = $lang['copythread'];
            break;

        default :
            $res['action'] = $action;
            break;
    }

    return $res;
}

function splitAdminLogData($username, $action, $data, $dateline) {
    global $lang;

    $res = array ();
    $ip = '';
    $details = array (
        '<table class="dataTable">'
    );
    $data = unserialize(base64_decode($data));

    foreach ($data as $key => $val) {
        if ($key == 'ip') {
            $ip = $val;
        }
        $details[] = '<tr><td class="dataKey">' . $key . ': </td><td class="dataVal">' . nl2br(htmlentities($val)) . '</td></tr>';
    }
    $details[] = '</table>';

    $res['details'] = implode("\n", $details);
    $res['ip'] = $ip;
    $res['username'] = $username;
    $res['dateline'] = printGmDate($dateline) . ' ' . $lang['textat'] . ' ' . printGmTime($dateline);

    switch ($action) {
        case 'accessCP' :
            $res['action'] = $lang['textaccessCP'];
            break;
        case 'accessU2UAdmin' :
            $res['action'] = $lang['textaccessU2UAdmin'];
            break;
        case 'accessTools' :
            $res['action'] = $lang['textaccessTools'];
            break;
        case 'useEditProfile' :
            $res['action'] = $lang['textuseEditProfile'];
            break;
        default :
            $res['action'] = $action;
            break;
    }

    return $res;
}


function displayModLog() {
    global $THEME, $lang, $oToken, $db, $table_logs, $table_members;
    
    $oToken->isValidToken();

    nav($lang['textmodlogs']);
?>
    <tr class="altbg2">
    <td align="center">
    <table cellspacing="0" cellpadding="0" border="0" width="97%" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td><strong><font style="color: <?php echo $THEME['cattext']?>">Username:</font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>">Date/Time:</font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>">IP:</font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>">Action:</font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>">Details:</font></strong></td>
    </tr>

    <?php

    $count = $db->result($db->query("SELECT count(lid) FROM $table_logs WHERE (type='mod')"), 0);

    $page = formInt('page');
    if ($page < 1) {
        $page = 1;
    }

    $old = (($page -1) * 100);
    $current = ($page * 100);

    $prevpage = '';
    $nextpage = '';
    $random_var = '';

    $query = $db->query("SELECT l.action, l.data, l.dateline, m.username FROM $table_logs l LEFT JOIN $table_members m ON (l.uid=m.uid) WHERE (l.type='mod') ORDER BY l.lid DESC LIMIT $old, 100");

    $url = '';

    while ($recordinfo = $db->fetch_array($query)) {
        $data = splitModLogData($recordinfo['username'], $recordinfo['action'], $recordinfo['data'], $recordinfo['dateline']);
?>
        <tr>
        <td class="tablerow altbg1"><a href="./member.php?action=viewpro&amp;member=<?php echo urlencode($data['username'])?>"><?php echo htmlspecialchars($data['username'])?></a></td>
        <td class="tablerow altbg2"><?php echo $data['dateline']?></td>
        <td class="tablerow altbg1"><?php echo $data['ip']?></td>
        <td class="tablerow altbg1"><?php echo $data['action']?></td>
        <td class="tablerow altbg1"><?php echo $data['details']?></td>
        </tr>
        <?php

    }

    if ($count > $current) {
        $page = $current / 100;
        if ($page > 1) {
            $prevpage = '<a href="./cp2.php?action=modlog&amp;page=' . ($page -1) . '">&laquo; Previous Page</a>';
        }

        $nextpage = '<a href="./cp2.php?action=modlog&amp;page=' . ($page +1) . '">Next Page &raquo;</a>';

        if ($prevpage == '' || $nextpage == '') {
            $random_var = '';
        } else {
            $random_var = '-';
        }

        $last = ceil($count / 100);
        if ($last > $page) {
            $lastpage = '<a href="./cp2.php?action=modlog&amp;page=' . $last . '">&nbsp;&raquo;&raquo;</a>';
        }

        $first = 1;
        if ($page > $first) {
            $firstpage = '<a href="./cp2.php?action=modlog&amp;page=' . $first . '">&nbsp;&laquo;&laquo;</a>';
        }
?>
        <tr class="header">
        <td colspan="5"><?php echo $firstpage?> <?php echo $prevpage?> <?php echo $random_var?> <?php echo $nextpage?> <?php echo $lastpage?></td>
        </tr>

        <?php

    } else {
        if ($page > 1) {
            $prevpage = '<a href="./cp2.php?action=modlog&amp;page=' . ($page -1) . '">&laquo; Previous Page</a>';
        }

        $first = 1;
        if ($page > $first) {
            $firstpage = '<a href="./cp2.php?action=mod&amp;page=' . $first . '">&nbsp;&laquo;&laquo;</a>';
        } else {
            $firstpage = '';
        }
        if ($prevpage == '' || $nextpage == '') {
            $random_var = '';
        } else {
            $random_var = '-';
        }
?>
        <tr class="header">
        <td colspan="5"><?php echo $firstpage?> <?php echo $prevpage?> <?php echo $random_var?> <?php echo $nextpage?></td>
        </tr>
        <?php

    }

    if ($count == 0) {
?>
        <tr class="header">
        <td colspan="5">No logs present</td>
        </tr>
        <?php

    }
?>
    </table>
    </td></tr></table>
    </td>
    </tr>
    <?php

}


function displayAdminLog() {
    global $THEME, $lang, $oToken, $db, $table_logs, $table_members;

    $oToken->isValidToken();

    nav($lang['textcplogs']);
    ?>
    <tr class="altbg2">
    <td align="center">
    <table cellspacing="0" cellpadding="0" border="0" width="97%" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td><strong><font style="color: <?php echo $THEME['cattext']?>">Username:</font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>">Date/Time:</font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>">IP:</font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>">Action:</font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>">Details:</font></strong></td>
    </tr>

    <?php

    $count = $db->result($db->query("SELECT count(lid) FROM $table_logs WHERE type='admin'"), 0);

    $page = formInt('page');
    if ($page < 1) {
        $page = 1;
    }

    $old = (($page -1) * 100);
    $current = ($page * 100);
    $firstpage = '';
    $prevpage = '';
    $nextpage = '';
    $random_var = '';

    $query = $db->query("SELECT l.action, l.data, l.dateline, m.username FROM $table_logs l LEFT JOIN $table_members m ON (l.uid=m.uid) WHERE (l.type='admin') ORDER BY l.lid DESC LIMIT $old, 100");

    $url = '';

    while ($recordinfo = $db->fetch_array($query)) {
        $data = splitAdminLogData($recordinfo['username'], $recordinfo['action'], $recordinfo['data'], $recordinfo['dateline']);
        ?>
        <tr>
        <td class="tablerow altbg1"><a href="./member.php?action=viewpro&amp;member=<?php echo urlencode($data['username'])?>"><?php echo htmlspecialchars($data['username'])?></a></td>
        <td class="tablerow altbg2"><?php echo $data['dateline']?></td>
        <td class="tablerow altbg1"><?php echo $data['ip']?></td>
        <td class="tablerow altbg1"><?php echo $data['action']?></td>
        <td class="tablerow altbg1"><?php echo $data['details']?></td>
        </tr>
        <?php
    }

    if ($count > $current) {
        $page = $current / 100;
        if ($page > 1) {
            $prevpage = '<a href="./cp2.php?action=cplog&amp;page=' . ($page -1) . '">&laquo; Previous Page</a>';
        }

        $nextpage = '<a href="./cp2.php?action=cplog&amp;page=' . ($page +1) . '">Next Page &raquo;</a>';

        if ($prevpage == '' || $nextpage == '') {
            $random_var = '';
        } else {
            $random_var = '-';
        }

        $last = ceil($count / 100);
        if ($last > $page) {
            $lastpage = '<a href="./cp2.php?action=cplog&amp;page=' . $last . '">&nbsp;&raquo;&raquo;</a>';
        }

        $first = 1;
        if ($page > $first) {
            $firstpage = '<a href="./cp2.php?action=cplog&amp;page=' . $first . '">&nbsp;&laquo;&laquo;</a>';
        }
        ?>
        <tr class="header">
        <td colspan="5"><?php echo $firstpage?> <?php echo $prevpage?> <?php echo $random_var?> <?php echo $nextpage?> <?php echo $lastpage?></td>
        </tr>

        <?php

    } else {
        if ($page == 1) {
            $prevpage = '';
        } else {
            $prevpage = '<a href="./cp2.php?action=cplog&amp;page=' . ($page -1) . '">&laquo; Previous Page</a>';
        }

        $first = 1;
        if ($page > $first) {
            $firstpage = '<a href="./cp2.php?action=cplog&amp;page=' . $first . '">&nbsp;&laquo;&laquo;</a>';
        }
        ?>
        <tr class="header">
        <td colspan="5"><?php echo $firstpage?> <?php echo $prevpage?> <?php echo $random_var?> <?php echo $nextpage?></td>
        </tr>
        <?php
    }

    if ($count == 0) {
        ?>
        <tr class="header">
        <td colspan="5">No logs present</td>
        </tr>
        <?php
    }
    ?>
    </table>
    </td></tr></table>
    </td>
    </tr>
    <?php
}

?>
