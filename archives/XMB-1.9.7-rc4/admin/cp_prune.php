<?php
/* $Id: cp_prune.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
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

function displayPrunePanel() {
    global $db, $lang, $table_forums, $THEME;
    $forumselect = array ();
    $forumselect[] = '<select name="pruneFromList[]" multiple="mutliple">';
    // first let's get the forums that aren't categorized.
    $querynongrpfs = $db->query("SELECT fid, name FROM $table_forums WHERE type='forum' AND fup='0' ORDER BY displayorder");
    if ($db->num_rows($querynongrpfs) > 0) {
        $forumselect[] = '<option value="" disabled="disabled">' . $lang['textnocat'] . '</option>';
    }
    while ($fs = $db->fetch_array($querynongrpfs)) {
        $forumselect[] = '<option value="' . $fs['fid'] . '">' . $fs['name'] . '</option>';

        $querysub = $db->query("SELECT fid, name FROM $table_forums WHERE fup='$fs[fid]' AND type='sub' ORDER BY displayorder");
        while ($sub = $db->fetch_array($querysub)) {
            $forumselect[] = '<option value="' . $sub['fid'] . '">&nbsp; &raquo; ' . $sub['name'] . '</option>';
        }
    }
    $forumselect[] = '<option value="" disabled="disabled">&nbsp;</option>';

    $querygrp = $db->query("SELECT fid, name FROM $table_forums WHERE type='group' ORDER BY displayorder");
    while ($group = $db->fetch_array($querygrp)) {
        $forumselect[] = '<option value="" disabled="disabled">' . $group['name'] . '</option>';
        $forumselect[] = '<option value="" disabled="disabled">--------------------</option>';

        $queryfor = $db->query("SELECT fid, name FROM $table_forums WHERE fup='$group[fid]' AND (type='forum' OR type='sub') ORDER BY displayorder");
        while ($forum = $db->fetch_array($queryfor)) {
            $forumselect[] = '<option value="' . $forum['fid'] . '"> &nbsp; &raquo; ' . $forum['name'] . '</option>';

            $querysub = $db->query("SELECT fid, name FROM $table_forums WHERE fup='$forum[fid]' AND type='sub' ORDER BY displayorder");
            while ($sub = $db->fetch_array($querysub)) {
                $forumselect[] = '<option value="' . $sub['fid'] . '">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; ' . $sub['name'] . '</option>';
            }
        }
        $forumselect[] = '<option value="" disabled="disabled">&nbsp;</option>';
    }
    $forumselect[] = '</select>';
    $forumselect = implode($forumselect, "\n");
?>

    <tr>
    <td style="width: <?php echo $THEME['tablewidth']?>" class="tablerow altbg1"><strong><?php echo $lang['prunemsg'];?></strong></td>
    </tr>
    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp2.php?action=prune">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="550">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%" style="vertical-align: top;">
    <tr>
    <td class="category" colspan="2">
    <strong>
    <span style="color: <?php echo $THEME['cattext']?>">
    <?php echo $lang['textprune']?>
    </span>
    </strong>
    </td>
    </tr>
    <tr>
    <td class="tablerow altbg1">
    <?php echo $lang['pruneby']?>
    </td>
    <td class="tablerow altbg2">
    <table>
    <tr>
    <td>
    <input type="checkbox" name="pruneBy[date][check]" value="1" />
    </td>
    <td class="tablerow">
    <select name="pruneBy[date][type]">
    <option value="more"><?php echo $lang['prunemorethan']?></option>
    <option value="is"><?php echo $lang['pruneexactly']?></option>
    <option value="less"><?php echo $lang['prunelessthan']?></option>
    </select>
    <input type="text" name="pruneBy[date][date]" value="10" /> <?php echo $lang['daysold']?>
    </td>
    </tr>
    <tr>
    <td>
    <input type="checkbox" name="pruneBy[posts][check]" value="1" />
    </td>
    <td class="tablerow">
    <select name="pruneBy[posts][type]">
    <option value="more"><?php echo $lang['prunemorethan']?></option>
    <option value="is"><?php echo $lang['pruneexactly']?></option>
    <option value="less"><?php echo $lang['prunelessthan']?></option>
    </select>
    <input type="text" name="pruneBy[posts][posts]" value="10" /> <?php echo $lang['memposts']?>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td class="tablerow altbg1">
    <?php echo $lang['prunefrom']?>
    </td>
    <td class="tablerow altbg2">
    <table>
    <tr>
    <td>
    <input type="radio" name="pruneFrom" value="all" checked="checked" />
    </td>
    <td class="tablerow">
    <?php echo $lang['textallforumsandsubs']?>
    </td>
    </tr>
    <tr>
    <td>
    <input type="radio" name="pruneFrom" value="list" />
    </td>
    <td>
    <?php echo $forumselect?>
    </td>
    </tr>
    <tr>
    <td>
    <input type="radio" name="pruneFrom" value="fid" />
    </td>
    <td class="tablerow">
    <?php echo $lang['prunefids']?> <input type="text" name="pruneFromFid" /> <span class="smalltxt">(<?php echo $lang['seperatebycomma']?>)</span>
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <tr>
    <td class="tablerow altbg1">
    <?php echo $lang['pruneposttypes']?>
    </td>
    <td class="tablerow altbg2">
    <input type="checkbox" name="pruneType[normal]" value="1" checked="checked" /> <?php echo $lang['prunenormal']?><br />
    <input type="checkbox" name="pruneType[closed]" value="1" checked="checked" /> <?php echo $lang['pruneclosed']?><br />
    <input type="checkbox" name="pruneType[topped]" value="1" /> <?php echo $lang['prunetopped']?><br />
    </td>
    </tr>
    <tr>
    <td class="ctrtablerow altbg2" colspan="2"><input type="submit" name="pruneSubmit" value="<?php echo $lang['textprune']?>" /></td>
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

function processPruneSubmit() {
    global $db, $lang, $table_attachments, $table_forums, $table_threads, $table_posts, $oToken;

    $oToken->isValidToken();

    $pruneFrom = formVar('pruneFrom');
    $pruneFromList = formArray('pruneFromList');
    $pruneFromFid = formArray('pruneFromFid');
    $pruneBy = formArray('pruneBy');
    $pruneType = formArray('pruneType');

    $queryWhere = array ();
    // let's check what to prune first
    switch ($pruneFrom) {
        case 'all' :
            break;

        case 'list' :
            $fs = array ();
            foreach ($pruneFromList as $fid) {
                $fs[] = (int) trim($fid);
            }
            $fs = array_unique($fs);

            if (count($fs) < 1) {
                error($lang['nopruneforums'], false, '</td></tr></table></td></tr></table><br />');
            }

            $queryWhere[] = 'fid IN (' . implode(',', $fs) . ')';
            break;

        case 'fid' :
            $fs = array ();

            $fids = explode(',', $pruneFromFid);

            foreach ($fids as $fid) {
                $fs[] = (int) trim($fid);
            }
            $fs = array_unique($fs);

            if (count($fs) < 1) {
                error($lang['nopruneforums'], false, '</td></tr></table></td></tr></table><br />');
            }

            $queryWhere[] = 'fid IN (' . implode(',', $fs) . ')';
            break;

        default :
            error($lang['nopruneforums'], false, '</td></tr></table></td></tr></table><br />');
    }

    // by!
    if (isset ($pruneBy['posts']['check']) && $pruneBy['posts']['check'] == 1) {
        $sign = '';
        switch ($pruneBy['posts']['type']) {
            case 'less' :
                $sign = '<';
                break;

            case 'is' :
                $sign = '=';
                break;

            case 'more' :
            default :
                $sign = '>';
                break;
        }

        $queryWhere[] = 'replies ' . $sign . ' ' . (int) ($pruneBy['posts']['posts'] - 1);
    }
    if (isset ($pruneBy['date']['check']) && $pruneBy['date']['check'] == 1) {
        $sign = '';
        switch ($pruneBy['date']['type']) {
            case 'less' :
                $queryWhere[] = 'lastpost >= ' . (time() - (24 * 3600 * $pruneBy['date']['date']));
                break;

            case 'is' :
                $queryWhere[] = 'lastpost >= ' . (time() - (24 * 3600 * ($pruneBy['date']['date'] - 1))) . ' AND lastpost <= ' . (time() - (24 * 3600 * ($pruneBy['date']['date'])));
                break;

            case 'more' :

            default :
                $queryWhere[] = 'lastpost <= ' . (time() - (24 * 3600 * $pruneBy['date']['date']));
                break;
        }
    }

    if (!isset ($pruneType['closed']) || $pruneType['closed'] != 1) {
        $queryWhere[] = "closed != 'yes'";
    }
    if (!isset ($pruneType['topped']) || $pruneType['topped'] != 1) {
        $queryWhere[] = 'topped != 1';
    }
    if (!isset ($pruneType['normal']) || $pruneType['normal'] != 1) {
        $queryWhere[] = "(topped == 1 OR closed == 'yes')";
    }

    if (count($queryWhere) > 0) {
        $tids = array ();

        $queryWhere = implode(' AND ', $queryWhere);
        $q = $db->query("SELECT tid FROM $table_threads WHERE " . $queryWhere);
        if ($db->num_rows($q) > 0) {
            while ($t = $db->fetch_array($q)) {
                $tids[] = $t['tid'];
            }
            $tids = implode(',', $tids);
            $db->query("DELETE FROM $table_threads WHERE tid IN($tids)");
            $db->query("DELETE FROM $table_posts WHERE tid IN($tids)");
            $db->query("DELETE FROM $table_attachments WHERE tid IN($tids)");
        }
    } else {
        $db->query("TRUNCATE TABLE $table_threads");
        $db->query("TRUNCATE TABLE $table_attachments");
        $db->query("TRUNCATE TABLE $table_posts");
        $db->query("UPDATE $table_members SET postnum=0");
    }

    echo '<tr class="tablerow altbg2"><td align="center">' . $lang['forumpruned'] . '</td></tr>';
    message($lang['censorupdate'], false, '', '', 'cp2.php', false, false, false); // TODO
}
?>
