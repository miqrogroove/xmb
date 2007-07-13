<?php
/* $Id: cp_search.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
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

function displaySearchPanel()
{
    global $oToken, $lang, $THEME, $db, $table_words;
    
    ?>

    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp.php?action=search">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td colspan=2><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['insertdata']?>:</font></strong></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td valign="top"><div align="center"><br />
    <?php echo $lang['userip']?><br /><input type="text" name="userip" /></input><br /><br />
    <?php echo $lang['postip']?><br /><input type="text" name="postip" /></input><br /><br />
    <?php echo $lang['profileword']?><br /><input type="text" name="profileword" /></input><br /><br />
    <?php echo $lang['postword']?><br />

    <?php
    $query = $db->query("SELECT find FROM $table_words");
    $select = "<select name=\"postword\"><option value=\"\"></option>";
    while ($temp = $db->fetch_array($query)) {
        $select .= "<option value=\"$temp[find]\">$temp[find]</option>";
    }
    $select .= "</select>";
    echo $select;
    ?>

    <br /><br />
    <div align="center"><br /><input type="submit" class="submit" name="searchsubmit" value="Search now" /><br /><br /></div>
    </td>
    </tr>
    </table>
    </td></tr></table>
    </form>
    </td>
    </tr>
    <?php
}

function processSearch()
{
    global $lang, $oToken, $db, $table_members, $table_posts;
    
    $oToken->isValidToken();

    $found = 0;
    $list = array();
    
    $userip = formVar('userip');
    if (!empty($userip)) {
        $query = $db->query("SELECT * FROM $table_members WHERE regip = '$userip'");
        while ($users = $db->fetch_array($query)) {
            $link = "./member.php?action=viewpro&amp;member=$users[username]";
            $list[] = "<a href = \"$link\">$users[username]<br />";
            $found++;
        }
    }

    $postip = formVar('postip');
    if (!empty($postip)) {
        $query = $db->query("SELECT * FROM $table_posts WHERE useip = '$postip'");
        while ($users = $db->fetch_array($query)) {
            $link = "./viewthread.php?tid=$users[tid]#pid$users[pid]";
            if (!empty($users['subject'])) {
                $list[] = "<a href = \"$link\">$users[subject]<br />";
            } else {
                $list[] = "<a href = \"$link\">- - No subject - -<br />";
            }
            $found++;
        }
    }

    $profileword = formVar('profileword');
    if (!empty($profileword)) {
        $query = $db->query("SELECT * FROM $table_members WHERE bio = '%$profileword%'");
        while ($users = $db->fetch_array($query)) {
            $link = "./member.php?action=viewpro&amp;member=$users[username]";
            $list[] = "<a href = \"$link\">$users[username]<br />";
            $found++;
        }
    }

    $postword = formVar('postword');
    if (!empty($postword)) {
        $query = $db->query("SELECT * FROM $table_posts WHERE subject LIKE '%".$postword."%' OR message LIKE '%".$postword."%'");
        while ($users = $db->fetch_array($query)) {
            $link = "./viewthread.php?tid=$users[tid]#pid$users[pid]";
            if (!empty($users['subject'])) {
                $list[] = "<a href = \"$link\">$users[subject]<br />";
            } else {
                $list[] = "<a href = \"$link\">- - No subject - -<br />";
            }
            $found++;
        }
    }
    ?>

    <tr class="altbg2 tablerow">
    <td align="left" colspan="2">
    <strong><?php echo $found?></strong> <?php echo $lang['beenfound']?>
    <br />
    </td>
    </tr>

    <?php
    foreach ($list as $num=>$val) {
        ?>
        <tr class="tablerow" width="5%">
        <td align="left" class="altbg2">
        <strong><?php echo ($num+1)?>.</strong>
        </td>
        <td align="left" width="95%" class="altbg1">
        <?php echo $val?>
        </td>
        </tr>

        <?php
     }    
}
?>