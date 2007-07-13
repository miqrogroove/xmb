<?php
/* $Id: cp_member.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
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

function displayMemberPanel()
{
    global $THEME, $oToken, $lang;
    ?>

    <tr class="altbg2">
    <td>
    <form method="post" action="cp.php?action=members">
    <input type="hidden" name="members" value="search">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr>
    <td class="category" colspan="2"><font style="color: <?php echo $THEME['cattext']?>"><strong><?php echo $lang['textmembers']?></strong></font></td>
    </tr>
    <tr>
    <td class="tablerow altbg1" width="22%"><?php echo $lang['textsrchusr']; ?></td>
    <td class="tablerow altbg2"><input type="text" name="srchmem" /></td>
    </tr>
    
    <tr>
    <td class="tablerow altbg1" width="22%"><?php echo $lang['textsrchemail']; ?></td>
    <td class="tablerow altbg2"><input type="text" name="srchemail" /></td>
    </tr>
    
    <tr>
    <td class="tablerow altbg1" width="22%"><?php echo $lang['textwithstatus']?></td>
    <td class="tablerow altbg2">
    <select name="srchstatus">
    <option value="0"><?php echo $lang['anystatus']?></option>
    <option value="Super Administrator"><?php echo $lang['superadmin']?></option>
    <option value="Administrator"><?php echo $lang['textadmin']?></option>
    <option value="Super Moderator"><?php echo $lang['textsupermod']?></option>
    <option value="Moderator"><?php echo $lang['textmod']?></option>
    <option value="Member"><?php echo $lang['textmem']?></option>
    <option value="Banned"><?php echo $lang['textbanned']?></option>
    </select>
    </td>
    </tr>
    <tr>
    <td class="altbg2 tablerow" align="center" colspan="2"><input type="submit" class="submit" value="<?php echo $lang['textgo']?>" /></td>
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

function displayMemberSearchPanel()
{
    global $THEME, $oToken, $lang, $selHTML;
    global $db, $table_members;
    ?>

    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp.php?action=members">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="91%" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td align="center" width="3%"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textdeleteques']?></font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textusername']?></font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textnewpassword']?></font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textposts']?></font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textstatus']?></font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textcusstatus']?></font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textbanfrom']?></font></strong></td>
    </tr>

    <?php
    $srchstatus = formVar('srchstatus');
    $srchmem = formVar('srchmem');
    if ($srchstatus == "0") {
        $query = $db->query("SELECT * FROM $table_members WHERE username LIKE '%$srchmem%' ORDER BY username");
    } else {
        $query = $db->query("SELECT * FROM $table_members WHERE username LIKE '%$srchmem%' AND status='$srchstatus' ORDER BY username");
    }

    $sadminselect   = "";
    $adminselect    = "";
    $smodselect     = "";
    $modselect      = "";
    $memselect      = "";
    $banselect      = "";
    $noban          = "";
    $u2uban         = "";
    $postban        = "";
    $bothban        = "";

    while ($member = $db->fetch_array($query)) {
        switch ($member['status']) {
            case 'Super Administrator':
                $sadminselect = $selHTML;
                break;

            case 'Administrator':
                $adminselect = $selHTML;
                break;

            case 'Super Moderator':
                $smodselect = $selHTML;
                break;

            case 'Moderator':
                $modselect = $selHTML;
                break;

            case 'Member':
                $memselect = $selHTML;
                break;

            case 'Banned':
                $banselect = $selHTML;
                break;

            default:
                $memselect = $selHTML;
                break;
        }

        switch ($member['ban']) {
            case 'u2u':
                $u2uban = $selHTML;
                break;

            case 'posts':
                $postban = $selHTML;
                break;

            case 'both':
                $bothban = $selHTML;
                break;

            default:
                $noban = $selHTML;
                break;
        }
        ?>

        <tr class="altbg2 tablerow">
        <td align="center"><input type="checkbox" name="delete<?php echo $member['uid']?>" onclick="confirmActionCheckbox('<?php echo $lang['confirmDeleteUser']?>', this, true, false);" value="<?php echo $member['uid']?>" /></td>
        <td><a href="member.php?action=viewpro&amp;member=<?php echo rawurlencode($member['username'])?>"><?php echo $member['username']?></a>
        <br /><a href="javascript:confirmAction('<?php echo addslashes($lang['confirmDeletePosts']);?>', 'cp.php?action=deleteposts&amp;member=<?php echo rawurlencode($member['username'])?>', false);"><strong><?php echo $lang['cp_deleteposts']?></strong></a>
        </td>
        <td><input type="text" size="12" name="pw<?php echo $member['uid']?>"></td>
        <td><input type="text" size="3" name="postnum<?php echo $member['uid']?>" value="<?php echo $member['postnum']?>"></td>
        <td><select name="status<?php echo $member['uid']?>">
        <option value="Super Administrator" <?php echo $sadminselect?>><?php echo $lang['superadmin']?></option>
        <option value="Administrator" <?php echo $adminselect?>><?php echo $lang['textadmin']?></option>
        <option value="Super Moderator" <?php echo $smodselect?>><?php echo $lang['textsupermod']?></option>
        <option value="Moderator" <?php echo $modselect?>><?php echo $lang['textmod']?></option>
        <option value="Member" <?php echo $memselect?>><?php echo $lang['textmem']?></option>
        <option value="Banned" <?php echo $banselect?>><?php echo $lang['textbanned']?></option>
        </select></td>
        <td><input type="text" size="16" name="cusstatus<?php echo $member['uid']?>" value="<?php echo htmlspecialchars(stripslashes($member['customstatus']))?>" /></td>
        <td><select name="banstatus<?php echo $member['uid']?>">
        <option value="" <?php echo $noban?>><?php echo $lang['noban']?></option>
        <option value="u2u" <?php echo $u2uban?>><?php echo $lang['banu2u']?></option>
        <option value="posts" <?php echo $postban?>><?php echo $lang['banpost']?></option>
        <option value="both" <?php echo $bothban?>><?php echo $lang['banboth']?></option>
        </select></td>
        </tr>

        <?php
        $sadminselect   = "";
        $adminselect    = "";
        $smodselect     = "";
        $modselect      = "";
        $memselect      = "";
        $banselect      = "";
        $noban          = "";
        $u2uban         = "";
        $postban        = "";
        $bothban        = "";
    }
    ?>

    <tr>
    <td class="altbg2 tablerow" align="center" colspan="7"><input type="submit" class="submit" name="membersubmit" value="<?php echo $lang['textsubmitchanges']?>" /><input type="hidden" name="srchmem" value="<?php echo $srchmem?>" /><input type="hidden" name="srchstatus" value="<?php echo $srchstatus?>" /></td>
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

function processMembers()
{
    global $db, $table_members;
    global $lang, $self, $oToken;
    
    $oToken->isValidToken();
    
    /*
    Get the uid first Super Administrator (the first to register and thus most likely to be the 'top level' admin) to compare against the delete uid. This member should *never* be deleted this way.
    */
    $query = $db->query("SELECT MIN(`uid`) FROM `" . $table_members . "` WHERE `status`='Super Administrator'");
    $sa_uid = $db->result($query, 0);
    $db->free_result($query);

    $srchstatus = formVar('srchstatus');
    $srchmem = formVar('srchmem');
    if ($srchstatus == "0") {
        $query = $db->query("SELECT uid, username, password, status FROM $table_members WHERE username LIKE '%$srchmem%'");
    } else {
        $query = $db->query("SELECT uid, username, password, status FROM $table_members WHERE username LIKE '%$srchmem%' AND status='$srchstatus'");
    }

    while ($mem = $db->fetch_array($query)) {
        $to['status'] = 'status'.$mem['uid'];
        $to['status'] = formVar($to['status']);

        // Fix a race condition noted by wrdyjoey
        if ( trim($to['status']) == '' ) {
            $to['status'] = 'Member';
        }

        $origstatus = '';
        $origstatus = $mem['status'];

        $banstatus = "banstatus$mem[uid]";
        $banstatus = formVar($banstatus);

        $cusstatus = "cusstatus$mem[uid]";
        $cusstatus = formVar($cusstatus);

        $pw = "pw$mem[uid]";
        $pw = formVar($pw);

        $postnum = "postnum$mem[uid]";
        $postnum = formVar($postnum);

        $delete = "delete$mem[uid]";
        $delete = formVar($delete);

        if ($pw != "") {
            $newpw = md5(trim($pw));
            $queryadd = " , password='$newpw'";
        } else {
            $newpw = $mem['password'];
            $queryadd = " , password='$newpw'";
        }

        if (!X_SADMIN && ($origstatus == "Super Administrator" || $to['status'] == "Super Administrator")) {
            continue;
        }

        if($origstatus == 'Super Administrator' && $to['status'] != 'Super Administrator') {
            if($db->result($db->query("SELECT count(uid) FROM $table_members WHERE status='Super Administrator'"), 0) == 1) {
                error($lang['lastsadmin'], false, '</td></tr></table></td></tr></table><br />');
            }
        }

        if ($delete != "" && $delete != $self['uid'] && $delete != $sa_uid) {
            $db->query("DELETE FROM $table_members WHERE uid='$delete'");
        } else {
            if (strpos($pw, '"') !== false || strpos($pw, "'") !== false) {
                $lang['textmembersupdate'] = $mem['username'].': '.$lang['textpwincorrect'];
            } else {
                $newcustom = addslashes($cusstatus);
                $db->query("UPDATE $table_members SET ban='$banstatus', status='$to[status]', postnum='$postnum', customstatus='$newcustom'$queryadd WHERE uid='$mem[uid]'");
                $newpw="";
            }
        }
    }

    message($lang['textmembersupdate'], false, '</td></tr></table></td></tr></table><br />', '', 'cp2.php', false, false, false);
}

function processDeleteMemberPosts()
{
    global $oToken;
    
    $oToken->isValidToken();
    
    $queryd = $db->query("DELETE FROM $table_posts WHERE author='$member'");
    $queryt = $db->query("SELECT tid FROM $table_threads");
    while($threads = $db->fetch_array($queryt)) {
        $query = $db->query("SELECT COUNT(*) FROM $table_posts WHERE tid=$threads[tid]");
        $replynum = $db->result($query, 0);
        $replynum--;
        $db->query("UPDATE $table_threads SET replies=replies-1 WHERE tid=$threads[tid]"); // TODO
        $db->query("DELETE FROM $table_threads WHERE author='$member'");
    }
}

?>