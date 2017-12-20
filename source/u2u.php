<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2012, The XMB Group
 * http://www.xmbforum2.com/
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

define('X_SCRIPT', 'u2u.php');

require 'header.php';
require ROOT.'include/u2u.inc.php';

header('X-Robots-Tag: noindex');

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

$action = postedVar('action', '', FALSE, FALSE, FALSE, 'g');
$sendmode = ($action == 'send') ? "true" : "false";

eval('$u2uheader = "'.template('u2u_header').'";');
eval('$u2ufooter = "'.template('u2u_footer').'";');

if (X_GUEST) {
    redirect("{$full_url}misc.php?action=login", 0);
    exit;
}

$folder = postedVar('folder', '', TRUE, FALSE, TRUE);
if ($folder == '') {
    $folder = postedVar('folder', '', TRUE, FALSE, TRUE, 'g');
}

$tofolder = postedVar('tofolder', '', TRUE, FALSE, TRUE);

$folderlist = '';
$folders = '';
$farray = array();
if ($folder != '' && ($action == '' || $action == 'mod' || $action == 'view')) {
    //$folder = checkInput($folder, true);
} else {
    $folder = 'Inbox';
}

$u2ucount = u2u_folderList(); //Sets several global vars
$u2uid = getInt('u2uid');
if (!$u2uid) {
    $u2uid = postedVar('u2uid');
}

$thewidth = ($self['useoldu2u'] == 'yes') ? $tablewidth : '100%';

$u2upreview = '';
$leftpane = '';

switch($action) {
    case 'modif':
        $mod = postedVar('mod', '', FALSE, FALSE);
        switch($mod) {
            case 'send':
                if ($u2uid > 0) {
                    redirect($full_url."u2u.php?action=send&u2uid=$u2uid", 0);
                } else {
                    redirect($full_url.'u2u.php?action=send', 0);
                }
                break;
            case 'reply':
                if ($u2uid > 0) {
                    redirect($full_url."u2u.php?action=send&u2uid=$u2uid&reply=yes", 0);
                } else {
                    redirect($full_url."u2u.php?action=send&reply=yes", 0);
                }
                break;
            case 'replydel':
                if ($u2uid > 0) {
                    redirect($full_url."u2u.php?action=send&u2uid=$u2uid&reply=yes&del=yes", 0);
                } else {
                    redirect($full_url."u2u.php?action=send&reply=yes&del=yes", 0);
                }
                break;
            case 'forward':
                if ($u2uid > 0) {
                    redirect($full_url."u2u.php?action=send&u2uid=$u2uid&forward=yes", 0);
                } else {
                    redirect($full_url."u2u.php?action=send&forward=yes", 0);
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
        $modaction = postedVar('modaction', '', FALSE, FALSE);
        $u2u_select = getFormArrayInt('u2u_select');
        $tofolder = postedVar('tofolder', '', TRUE, FALSE);
        $folder_url = recodeOut($folder);
        switch($modaction) {
            case 'delete':
                if (!isset($u2u_select) || empty($u2u_select)) {
                    error($lang['textnonechosen'], false, $u2uheader, $u2ufooter, $full_url."u2u.php?folder=$folder_url", true, false, false);
                }
                u2u_mod_delete($folder, $u2u_select);
                break;
            case 'move':
                if (!isset($tofolder) || empty($tofolder)) {
                    error($lang['textnofolder'], false, $u2uheader, $u2ufooter, $full_url.'u2u.php', true, false, false);
                }

                if (!isset($u2u_select) || empty($u2u_select)) {
                    error($lang['textnonechosen'], false, $u2uheader, $u2ufooter, $full_url."u2u.php?folder=$folder_url", true, false, false);
                    return;
                }
                u2u_mod_move($tofolder, $u2u_select);
                break;
            case 'markunread':
                if (!isset($u2u_select) || empty($u2u_select)) {
                    error($lang['textnonechosen'], false, $u2uheader, $u2ufooter, $full_url."u2u.php?folder=$folder_url", true, false, false);
                }
                u2u_mod_markUnread($folder, $u2u_select);
                break;
            default:
                error($lang['testnothingchos'], false, $u2uheader, $u2ufooter, $full_url."u2u.php?folder=$folder_url", true, false, false);
                break;
        }
        break;
    case 'send':
        $msgto = postedVar('msgto', 'javascript', TRUE, FALSE, TRUE);
        $subject = postedVar('subject', 'javascript', TRUE, FALSE, TRUE);
        $message = postedVar('message', '', TRUE, FALSE);
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
            $u2ufolders = postedVar('u2ufolders', 'javascript', TRUE, FALSE, TRUE);
            u2u_folderSubmit($u2ufolders, $folders);
        } else {
            eval('$leftpane = "'.template('u2u_folders').'";');
        }
        break;
    case 'ignore':
        $leftpane = u2u_ignore();
        break;
    case 'emptytrash':
        $db->query("DELETE FROM ".X_PREFIX."u2u WHERE folder='Trash' AND owner='$xmbuser'");
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
eval('echo "'.template($tu2u).'";');
?>
