<?php
/**
* $Id: admin.user.inc.php,v 1.1.2.13 2007/03/19 21:53:30 ajv Exp $
*/

/**
* © 2001 - 2007 Aventure Media & The XMB Development Team
* http://www.aventure-media.co.uk
* http://www.xmbforum.com
*
*
*    This program is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program; if not, write to the Free Software
*    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
*/

if (!defined('IN_CODE') && (defined('DEBUG') && DEBUG == false)) {
    exit ("Not allowed to run this file directly.");
}

/**
* Class Admin - provides generic business logic for admin activities
* 
* Admin routines
* Static initialization allowed, does not require instantiation before first use.
*/
class admin {
    /**
    * rename_user()
    *
    * @param  $userfrom - message to display to user
    * @param  $userto    - new username
    * @return string to display to the admin once the operation has completed
    */
    function rename_user( $userfrom, $userto ) {
        global $db, $lang, $self;
        global $table_whosonline, $table_members, $table_posts, $table_threads;
        global $table_forums, $table_favorites, $table_buddys, $table_u2u;

        // can't do it if either username is blank
        if ( $userfrom == '' || $userto == '' ) {
            return $lang['admin_rename_fail'];
        }

        // user must currently exist and must not become anyone else
        $query     = $db->query("SELECT username FROM $table_members WHERE username='$userfrom'");
        $cUsrFrm = $db->num_rows($query);
        $db->free_result($query);

        $query    = $db->query("SELECT username FROM $table_members WHERE username='$userto'");
        $cUsrTo = $db->num_rows($query);
        $db->free_result($query);

        // userfrom must only be 1 (row), and userto must not exist (ie 0 rows)
        if ( !($cUsrFrm == 1 && $cUsrTo == 0) ) {
            return $lang['admin_rename_fail'];
        }

        // userto must not obviate restricted username rules
        if ( !$this->check_restricted($userto) ) {
            return $lang['restricted'];
        }
        
        // username must be >= 3 chars
        if ( strlen($userto) < 3 ) {
            return $lang['username_too_short'];
        }

        // we're good to go, rename user
        @set_time_limit(180);
        $db->query("UPDATE $table_members SET username='$userto' WHERE username='$userfrom'");
        $db->query("UPDATE $table_buddys SET username='$userto' WHERE username='$userfrom'");
        $db->query("UPDATE $table_buddys SET buddyname='$userto' WHERE buddyname='$userfrom'");
        $db->query("UPDATE $table_favorites SET username='$userto' WHERE username='$userfrom'");
        $db->query("UPDATE $table_forums SET moderator='$userto' WHERE moderator='$userfrom'");
        $db->query("UPDATE $table_posts SET author='$userto' WHERE author='$userfrom'");
        $db->query("UPDATE $table_threads SET author='$userto' WHERE author='$userfrom'");
        $db->query("UPDATE $table_u2u SET msgto='$userto' WHERE msgto='$userfrom'");
        $db->query("UPDATE $table_u2u SET msgfrom='$userto' WHERE msgfrom='$userfrom'");
        $db->query("UPDATE $table_u2u SET owner='$userto' WHERE owner='$userfrom'");
        $db->query("UPDATE $table_whosonline SET username='$userto' WHERE username='$userfrom'");

        // update thread last posts
        $query = $db->query("SELECT tid, lastpost from $table_threads WHERE lastpost like '%$userfrom'");
        while ( $result = $db->fetch_array($query) ) {
            list($posttime, $lastauthor) = explode("|", $result['lastpost']);
            if ( $lastauthor == $userfrom ) {
                $newlastpost = $posttime . '|' . $userto;
                $db->query("UPDATE $table_threads SET lastpost='$newlastpost' WHERE tid='".$result['tid']."'");
            }
        }
        $db->free_result($query);
        
        // update the polls
        $query = $db->query("SELECT pollopts, tid FROM $table_threads WHERE pollopts LIKE '% ".$userfrom." %'");
        $poll = array();
        while($result = $db->fetch_array($query)) {
            $parts = explode('#|#', $result['pollopts']);
            $parts[count($parts)-1] = str_replace(' '.$userfrom.' ', ' '.$userto.' ', $parts[count($parts)-1]);
            $pollopts = implode('#|#', $parts);
            
            $db->query("UPDATE $table_threads SET pollopts = '$pollopts' WHERE tid='".$result['tid']."'");
        }
        $db->free_result($query);
        
        // update ignoreu2u
            // make sure we get em from all 4 possible places (only one, middle, start, end)
            // maybe this'd be faster using a regexp?
        $query = $db->query("SELECT ignoreu2u, uid FROM $table_members WHERE (ignoreu2u REGEXP '(^|(,))( )*$userfrom( )*((,)|$)')");
        while($usr = $db->fetch_array($query)) {
            $parts = explode(',', $usr['ignoreu2u']);
            $index = array_search($userfrom, $parts);
            $parts[$index] = $userto;
            $parts = implode(',', $parts);
            $db->query("UPDATE $table_members SET ignoreu2u='".$parts."' WHERE uid='".$usr['uid']."'");
        }
        $db->free_result($query);
        
        // Moderator column in forums
        $query = $db->query("SELECT moderator, fid FROM $table_forums WHERE (moderator REGEXP '(^|(,))( )*$userfrom( )*((,)|$)')");
        while($list = $db->fetch_array($query)) {
            $parts = explode(',', $list['moderator']);
            $index = array_search($userfrom, $parts);
            $parts[$index] = $userto;
            $parts = implode(', ', $parts);
            $db->query("UPDATE $table_forums SET moderator='".$parts."' WHERE fid='".$list['fid']."'");
        }
        
        // update forum-accesslists
        $query = $db->query("SELECT userlist, fid FROM $table_forums WHERE (userlist REGEXP '(^|(,))( )*$userfrom( )*((,)|$)')");
        while($list = $db->fetch_array($query)) {
            $parts = array_unique(array_map('trim', explode(',', $list['userlist'])));
            $index = array_search($userfrom, $parts);
            $parts[$index] = $userto;
            $parts = implode(', ', $parts);
            $db->query("UPDATE $table_forums SET userlist='".$parts."' WHERE fid='".$list['fid']."'");
        }
        $db->free_result($query);
        
        // update forum last posts
        $query = $db->query("SELECT fid, lastpost FROM $table_forums WHERE lastpost LIKE '%$userfrom'");
        while ( $result = $db->fetch_array($query) ) {
            list($posttime, $lastauthor, $lastpid) = explode("|", $result['lastpost']);
            if ( $lastauthor == $userfrom ) {
                $newlastpost = $posttime . '|' . $userto.'|'.$lastpid;
                $db->query("UPDATE $table_forums SET lastpost='$newlastpost' WHERE fid='".$result['fid']."'");
            }
        }
        $db->free_result($query);

        return (($self['username'] == $userfrom) ? $lang['admin_rename_warn_self'] : '') . $lang['admin_rename_success'];
    }

    /**
    * check_restricted()
    *
    * @param  $userto    username to check
    * @return true = username okay    false username bad
    */
    function check_restricted( $userto ) {
        global $db, $table_restricted;

        $nameokay = true;

        $find = array('<', '>', '|', '"', '[', ']', '\\', ',', '@', '\'', ' '); 
        foreach ($find as $needle) { 
            if (false !== strpos($userto, $needle)) { 
                return false;
            }
        }

        $query = $db->query("SELECT * FROM $table_restricted");
        while ($restriction = $db->fetch_array($query)) {
            if ($restriction['case_sensitivity'] == 1) {
                if ($restriction['partial'] == 1) {
                    if (strpos($userto, $restriction['name']) !== false) {
                        $nameokay = false;
                    }
                } else {
                    if ($userto == $restriction['name']) {
                        $nameokay = false;
                    }
                }
            } else {
                $t_username = strtolower($userto);
                $restriction['name'] = strtolower($restriction['name']);

                if ($restriction['partial'] == 1) {
                    if (strpos($t_username, $restriction['name']) !== false) {
                        $nameokay = false;
                    }
                } else {
                    if ($t_username == $restriction['name']) {
                        $nameokay = false;
                    }
                }
            }
        }
        $db->free_result($query);
        return $nameokay;
    }
}

function displayAdminPanel() {
    global $SETTINGS, $THEME, $self, $lang, $db;
    
    ?>
    <table cellspacing="0" cellpadding="0" border="0" width="<?php echo $THEME['tablewidth']?>" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td colspan="30" align="center"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textcp']?></font></strong></td>
    </tr>

    <tr style="background-color: <?php echo $THEME['altbg1']?>" class="tablerow">
    <td colspan="30" align="center">




    <br />
    <table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">

    <tr class="category">
    <td valign="top" width="20%" align="center"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['general']?></font></strong></td>
    <td valign="top" width="20%" align="center"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textforums']?></font></strong></td>
    <td valign="top" width="20%" align="center"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textmembers']?></font></strong></td>
    <td valign="top" width="20%" align="center"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['look_feel']?></font></strong></td>
    </tr>

    <tr>
    <td class="tablerow" align="left" valign="top" width="20%" style="background-color: <?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="cp2.php?action=attachments"><?php echo $lang['textattachman']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=censor"><?php echo $lang['textcensors']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=newsletter"><?php echo $lang['textnewsletter']?></a><br />
    &raquo;&nbsp;<a href="cp.php?action=search"><?php echo $lang['cpsearch']?></a><br />
    &raquo;&nbsp;<a href="cp.php?action=settings"><?php echo $lang['textsettings']?></a><br />
    </td>

    <td class="tablerow" align="left" valign="top" width="20%" style="background-color: <?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="cp.php?action=forum"><?php echo $lang['textforums']?></a><br />
    &raquo;&nbsp;<a href="cp.php?action=mods"><?php echo $lang['textmods']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=prune"><?php echo $lang['textprune']?></a><br />
    </td>

    <td class="tablerow" align="left" valign="top" width="20%" style="background-color: <?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="cp.php?action=ipban"><?php echo $lang['textipban']?></a><br />
    &raquo;&nbsp;<a href="cp.php?action=members"><?php echo $lang['textmembers']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=ranks"><?php echo $lang['textuserranks']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=restrictions"><?php echo $lang['cprestricted']?></a><br />
    &raquo;&nbsp;<a href="cp.php?action=rename"><?php echo $lang['admin_rename_txt']?></a><br />
    </td>

    <td class="tablerow" align="left" valign="top" width="20%" style="background-color: <?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="cp2.php?action=smilies"><?php echo $lang['smilies']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=templates"><?php echo $lang['templates']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=themes"><?php echo $lang['themes']?></a><br />
    </td>
    </tr>

    <tr class="category">
    <td valign="top" width="20%" align="center"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['logs']?></font></strong></td>
    <td valign="top" width="20%" align="center"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['tools']?></font></strong></td>
    <td valign="top" width="20%" align="center"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['mysql_tools']?></font></strong></td>
    <td valign="top" width="20%" align="center"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['backup_tools']?></font></strong></td>
    </tr>

    <tr>
    <td class="tablerow" align="left" valign="top" width="20%" style="background-color: <?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="cp2.php?action=modlog"><?php echo $lang['textmodlogs']?></a><br />
    &raquo;&nbsp;<a href="cp2.php?action=cplog"><?php echo $lang['textcplogs']?></a>
    </td>

    <td class="tablerow" align="left" valign="top" width="20%" style="background-color: <?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="tools.php?action=fixftotals"><?php echo $lang['textfixposts']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=fixlastposts"><?php echo $lang['textfixlastposts']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=fixmposts"><?php echo $lang['textfixmemposts']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=fixttotals"><?php echo $lang['textfixthread']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=updatemoods"><?php echo $lang['textfixmoods']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=fixorphanedthreads"><?php echo $lang['textfixothreads']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=fixorphanedattachments"><?php echo $lang['textfixoattachments']?></a><br />
    </td>

    <td class="tablerow" align="left" valign="top" width="20%" style="background-color: <?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="tools.php?action=analyzetables"><?php echo $lang['analyze']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=whosonlinedump"><?php echo $lang['cpwodump']?></a><br />
    &raquo;&nbsp;<a href="cp.php?action=upgrade"><?php echo $lang['raw_mysql']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=optimizetables"><?php echo $lang['optimize']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=repairtables"><?php echo $lang['repair']?></a><br />
    &raquo;&nbsp;<a href="tools.php?action=u2udump"><?php echo $lang['u2udump']?></a><br />
    </td>

    <td class="tablerow" align="left" valign="top" width="20%" style="background-color: <?php echo $THEME['altbg2']?>">
    &raquo;&nbsp;<a href="javascript:confirmAction('<?php echo $lang['disclaimer'];?>', 'cp2.php?action=dbdump');"><?php echo $lang['db_backup']?></a><br />
    &raquo;&nbsp;<a href="javascript:confirmAction('<?php echo $lang['disclaimer'];?>', 'dump_attachments.php?action=dump_attachments');"><?php echo $lang['dump_attachments']?></a><br />
    &raquo;&nbsp;<a href="javascript:confirmAction('<?php echo $lang['disclaimer'];?>', 'dump_attachments.php?action=restore_attachments');"><?php echo $lang['restore_attachments']?></a><br />
    </td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    <br />

    </td>
    </tr>
<?php
}
