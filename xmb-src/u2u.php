<?php
/* $Id: u2u.php,v 1.8 2006/02/24 14:17:22 Jamie Exp $ */
/*
    XMB 1.10
    © 2001 - 2006 Aventure Media & The XMB Development Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

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

// Get required files
require './header.php';
require './include/u2u.inc.php';

// Load templates
loadtemplates('u2u_header','u2u_footer','u2u_msg','u2u','u2u_folderlink','u2u_inbox','u2u_outbox','u2u_drafts','u2u_row','u2u_row_none','u2u_view','u2u_ignore','u2u_send','u2u_send_preview','u2u_folders','u2u_main','u2u_quotabar','u2u_old','u2u_printable');
smcwcache();

// Eval necessary templates
eval("\$css = \"".template("css")."\";");

$sendmode = (isset($action) && $action == 'send') ? "true" : "false";

eval("\$u2uheader = \"" .template('u2u_header'). "\";");
eval("\$u2ufooter = \"" .template('u2u_footer'). "\";");

// Check if logged in
if ( X_GUEST ) {
    error($lang['u2unotloggedin'], false, $u2uheader, $u2ufooter, false, true, false, false);
    exit;
}

$folderlist = '';
$folders = '';
$farray = array();

if ( isset($folder) && ( !isset($action) || $action == 'mod' || $action == 'view' ) ) {
    $folder = checkInput($folder, true);
} else {
    $folder = 'Inbox';
}

$u2ucount   = u2u_folderList();
$u2uid      = ( isset($u2uid) && is_numeric($u2uid) )   ? (int) $u2uid  : 0;

$thewidth = ($self['useoldu2u'] == 'yes') ? $tablewidth : '100%';

$u2upreview = '';
$leftpane = '';

// Start actions
switch ($action) {
    case 'modif':
        $mod = (isset($mod)) ? $mod : '';
        switch($mod) {
            case 'send':
                if ( $u2uid > 0 ) {
                    redirect("u2u.php?action=send&u2uid=$u2uid", 0);
                } else {
                    redirect("u2u.php?action=send", 0);
                }
                break;

            case 'reply':
                if ( $u2uid > 0 ) {
                    redirect("u2u.php?action=send&u2uid=$u2uid&reply=yes", 0);
                } else {
                    redirect("u2u.php?action=send&reply=yes", 0);
                }
                break;

            case 'replydel':
                if ( $u2uid > 0 ) {
                    redirect("u2u.php?action=send&u2uid=$u2uid&reply=yes&del=yes", 0);
                } else {
                    redirect("u2u.php?action=send&reply=yes&del=yes", 0);
                }
                break;

            case 'forward':
                if ( $u2uid > 0 ) {
                    redirect("u2u.php?action=send&u2uid=$u2uid&forward=yes", 0);
                } else {
                    redirect("u2u.php?action=send&forward=yes", 0);
                }
                break;

            case 'sendtoemail':
                u2u_print($u2uid, true);
                break;

            case 'delete':
                u2u_delete($u2uid, $folder);
                break;

            case 'move':
                u2u_move($u2uid, $tofolder);
                break;

            case 'markunread':
                u2u_markUnread($u2uid, $folder, $type);
                break;

            default:
                // should not get here, but stop processing this page.
                $leftpane = u2u_display($folder, $folders);
                break;
        }
        break;

    case "mod":
        switch($modaction) {
            case 'delete':
                if ( !isset($u2u_select) || empty( $u2u_select ) ) {
                    error( $lang['textnonechosen'], false, $u2uheader, $u2ufooter, "u2u.php?folder=$folder", true, false, false );
                }
                u2u_mod_delete($folder, $u2u_select);
                break;

            case 'move':
                if ( !isset($tofolder) || empty( $tofolder ) ) {
                    error( $lang['textnofolder'], false, $u2uheader, $u2ufooter, "u2u.php", true, false, false );
                }

                if ( !isset($u2u_select) || empty( $u2u_select ) ) {
                    error( $lang['textnonechosen'], false, $u2uheader, $u2ufooter, "u2u.php?folder=$folder", true, false, false );
                    return;
                }

                u2u_mod_move($tofolder, $u2u_select);
                break;

            case 'markunread':
                u2u_mod_markUnread($folder, $u2u_select);
                break;

            default:
                error($lang['testnothingchos'], false, $u2uheader, $u2ufooter, "u2u.php?folder=$folder", true, false, false);
                break;
        }
        break;

    case 'send':
        $msgto = isset($msgto) ? $msgto : '';
        $subject = isset($subject) ? $subject : '';
        $message = isset($message) ? $message : '';
        $leftpane = u2u_send($u2uid, $msgto, $subject, $message, $u2upreview);
        break;

    case 'view':
        $leftpane = u2u_view($u2uid, $folders);
        break;

    case 'printable':
        u2u_print($u2uid, false);
        break;

    case 'folders':
        if ( isset( $folderssubmit ) ) {
            u2u_folderSubmit($u2ufolders, $folders);
        } else {
            $self['u2ufolders'] = checkOutput( $self['u2ufolders'] );
            eval( '$leftpane = "'.template('u2u_folders').'";');
        }
        break;

    case 'ignore':
        $leftpane = u2u_ignore();
        break;

    case 'emptytrash':
        $db->query("DELETE FROM $table_u2u WHERE folder='Trash' AND owner='$self[username]'");
        u2u_msg($lang['texttrashemptied'], "u2u.php");
        break;

    default:
        $leftpane = u2u_display($folder, $folders);
        break;
}

// Graphical u2ucount
$percentage = (0 == $u2uquota) ? 0 : (float)(($u2ucount / $u2uquota) * 100);
if (100 < $percentage) {
    $barwidth = 100;
    eval($lang['evaluqinfo_over']);
} else {
    $percent = number_format($percentage, 2);
    $barwidth = number_format($percentage, 0);
    eval($lang['evaluqinfo']);
}
eval('$u2uquotabar = "'.template('u2u_quotabar').'";');

// Check for old u2u interface
$tu2u = ($self['useoldu2u'] == 'yes') ? 'u2u_old' : 'u2u';

// Display page
eval('echo stripslashes("'.template($tu2u).'");');
?>