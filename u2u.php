<?php
/**
 * eXtreme Message Board
 * XMB 1.9.8 Engage Final
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2007, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * Copyright (c) 2007, iEntry, Inc.
 * http://www.ientry.com
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 **/

require 'header.php';
require ROOT.'include/u2u.inc.php';

loadtemplates(
'u2u_header',
'u2u_footer',
'u2u_msg',
'u2u',
'u2u_folderlink',
'u2u_inbox',
'u2u_outbox',
'u2u_drafts',
'u2u_row',
'u2u_row_none',
'u2u_view',
'u2u_ignore',
'u2u_send',
'u2u_send_preview',
'u2u_folders',
'u2u_main',
'u2u_quotabar',
'u2u_old',
'u2u_printable',
'email_html_header',
'email_html_footer'
);

smcwcache();

eval('$css = "'.template('css').'";');

$action = getVar('action');
$sendmode = (isset($action) && $action == 'send') ? "true" : "false";

eval('$u2uheader = "'.template('u2u_header').'";');
eval('$u2ufooter = "'.template('u2u_footer').'";');

if (X_GUEST) {
    error($lang['u2unotloggedin'], false, $u2uheader, $u2ufooter, false, true, false, false);
    exit;
}

$folder = formVar('folder');
if (!$folder) {
    $folder = getVar('folder');
}

$folderlist = $folders = '';
$farray = array();
if ($folder && (!$action || $action == 'mod' || $action == 'view')) {
    $folder = checkInput($folder, true);
} else {
    $folder = 'Inbox';
}

$u2ucount = u2u_folderList();
$u2uid = getInt('u2uid');
if (!$u2uid) {
    $u2uid = formVar('u2uid');
}

$thewidth = ($self['useoldu2u'] == 'yes') ? $tablewidth : '100%';

$u2upreview = $leftpane = '';

switch ($action) {
    case 'modif':
        $mod = formVar('mod');
        switch($mod) {
            case 'send':
                if ($u2uid > 0) {
                    redirect("u2u.php?action=send&u2uid=$u2uid", 0);
                } else {
                    redirect('u2u.php?action=send', 0);
                }
                break;
            case 'reply':
                if ($u2uid > 0) {
                    redirect("u2u.php?action=send&u2uid=$u2uid&reply=yes", 0);
                } else {
                    redirect("u2u.php?action=send&reply=yes", 0);
                }
                break;
            case 'replydel':
                if ($u2uid > 0) {
                    redirect("u2u.php?action=send&u2uid=$u2uid&reply=yes&del=yes", 0);
                } else {
                    redirect("u2u.php?action=send&reply=yes&del=yes", 0);
                }
                break;
            case 'forward':
                if ($u2uid > 0) {
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
                $leftpane = u2u_display($folder, $folders);
                break;
        }
        break;
    case 'mod':
        $modaction = formVar('modaction');
        $u2u_select = getFormArrayInt('u2u_select');
        $delete = formVar('delete');
        $move = formVar('move');
        $tofolder = formVar('tofolder');
        $markunread = formVar('markunread');

        switch ($modaction) {
            case 'delete':
                if (!isset($u2u_select) || empty($u2u_select)) {
                    error($lang['textnonechosen'], false, $u2uheader, $u2ufooter, "u2u.php?folder=$folder", true, false, false);
                }
                u2u_mod_delete($folder, $u2u_select);
                break;
            case 'move':
                if (!isset($tofolder) || empty($tofolder)) {
                    error($lang['textnofolder'], false, $u2uheader, $u2ufooter, 'u2u.php', true, false, false);
                }

                if (!isset($u2u_select) || empty($u2u_select)) {
                    error($lang['textnonechosen'], false, $u2uheader, $u2ufooter, "u2u.php?folder=$folder", true, false, false);
                    return;
                }

                u2u_mod_move($tofolder, $u2u_select);
                break;
            case 'markunread':
                if (!isset($u2u_select) || empty($u2u_select)) {
                    error($lang['textnonechosen'], false, $u2uheader, $u2ufooter, "u2u.php?folder=$folder", true, false, false);
                }
                u2u_mod_markUnread($folder, $u2u_select);
                break;
            default:
                error($lang['testnothingchos'], false, $u2uheader, $u2ufooter, "u2u.php?folder=$folder", true, false, false);
                break;
        }
        break;
    case 'send':
        $msgto = formVar('msgto');
        $subject = formVar('subject');
        $message = formVar('message');

        $leftpane = u2u_send($u2uid, $msgto, $subject, $message, $u2upreview);
        break;
    case 'view':
        $leftpane = u2u_view($u2uid, $folders);
        break;
    case 'printable':
        u2u_print($u2uid, false);
        break;
    case 'folders':
        if (onSubmit('folderssubmit')) {
            $u2ufolders = formVar('u2ufolders');
            u2u_folderSubmit($u2ufolders, $folders);
        } else {
            $self['u2ufolders'] = checkOutput($self['u2ufolders']);
            eval('$leftpane = "'.template('u2u_folders').'";');
        }
        break;
    case 'ignore':
        $leftpane = u2u_ignore();
        break;
    case 'emptytrash':
        $db->query("DELETE FROM ".X_PREFIX."u2u WHERE folder='Trash' AND owner='$self[username]'");
        u2u_msg($lang['texttrashemptied'], 'u2u.php');
        break;
    default:
        $leftpane = u2u_display($folder, $folders);
        break;
}

if (!X_STAFF) {
    $percentage = (0 == $SETTINGS['u2uquota']) ? 0 : (float)(($u2ucount / $SETTINGS['u2uquota']) * 100);
    if ($percentage > 100) {
        $barwidth = 100;
        eval($lang['evaluqinfo_over']);
    } else {
        $percent = number_format($percentage, 2);
        $barwidth = number_format($percentage, 0);
        eval($lang['evaluqinfo']);
    }
} else {
    $barwidth = $percentage = 0;
    eval($lang['evalu2ustaffquota']);
}
eval('$u2uquotabar = "'.template('u2u_quotabar').'";');

$tu2u = ($self['useoldu2u'] == 'yes') ? 'u2u_old' : 'u2u';

eval('echo stripslashes("'.template($tu2u).'");');
?>