<?php
/* $Id: u2u.php,v 1.3.2.12 2007/05/22 21:07:45 ajv Exp $ */
/**
 * Changes to port UltimaBB 1.0's U2U classes to XMB 1.9.7
 * 
 * © 2007 The XMB Development Team
 *        http://www.xmbforum.com
 *
 * This code is from UltimaBB. The (C) notice is as follows:
 * 
 * UltimaBB
 * Copyright (c) 2004 - 2007 The UltimaBB Group
 * http://www.ultimabb.com
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
 **/

require 'header.php';
require 'include/validate.inc.php';
require 'include/u2u.inc.php';

loadtemplates('u2u_nav', 'u2u', 'u2u_folderlink', 'u2u_inbox', 'u2u_outbox', 'u2u_drafts', 'u2u_row', 'u2u_row_none', 'u2u_view', 'u2u_ignore', 'u2u_send', 'u2u_send_preview', 'u2u_folders', 'u2u_main', 'u2u_quotabar', 'u2u_printable', 'u2u_attachmentbox', 'u2u_attachment', 'u2u_sig', 'u2u_trash', 'u2u_send_preview_sig', 'u2u_attachmentimage', 'functions_smilieinsert', 'functions_smilieinsert_smilie', 'functions_bbcodeinsert', 'functions_bbcode');

smcwcache();

$oToken = new page_token();
$oToken->init();

eval ('$css = "' . template('css') . '";');

switch ($action) {
    case 'send' :
        nav('<a href="u2u.php">' . $lang['textu2umessenger'] . '</a>');
        nav($lang['textsendu2u']);
        btitle($lang['textu2umessenger']);
        btitle($lang['textsendu2u']);
        break;
    case 'ignore' :
        nav('<a href="u2u.php">' . $lang['textu2umessenger'] . '</a>');
        nav($lang['ignorelist']);
        btitle($lang['textu2umessenger']);
        btitle($lang['ignorelist']);
        break;
    case 'view' :
        nav('<a href="u2u.php">' . $lang['textu2umessenger'] . '</a>');
        nav($lang['textu2uinbox']);
        btitle($lang['textu2umessenger']);
        btitle($lang['textu2uinbox']);
        break;
    case 'folders' :
        nav('<a href="u2u.php">' . $lang['textu2umessenger'] . '</a>');
        nav($lang['folderlist']);
        btitle($lang['textu2umessenger']);
        btitle($lang['folderlist']);
        break;
    default :
        nav($lang['textu2umessenger']);
        btitle($lang['textu2umessenger']);
        break;
}

$sendmode = (isset ($action) && $action == 'send') ? true : false;

eval ('$bbcodescript = "' . template('functions_bbcode') . '";');

?>
<script src="include/u2uheader.js"></script>
<?php

if ($action != 'attachment' && $action != 'printable') {
    eval ('echo "' . template('header') . '";');
    eval ('echo "' . template('u2u_nav') . '";');
}

if (X_GUEST) {
    error($lang['u2unotloggedin'], false, '', '', 'index.php', true, false, true);
}

$u2uCommand = new u2uModel();

// If there's a new folder coming in from the URL, let's parse it.
$folder = getRequestVar('folder');
if (!empty ($folder)) {
    $folder = checkInput($folder);
    $_SESSION['folder'] = $folder;
} else {
    if (empty ($folder) || !isset ($folder)) {
        if ($action == 'view' || $action == 'modif') {
            $folder = '';
        }
        if ($action == '' || !isset ($action)) {
            $folder = 'Inbox';
            $_SESSION['folder'] = $folder;
        }
    } else {
        if (isset ($_SESSION['folder'])) {
            $folder = $_SESSION['folder'];
        } else {
            $folder = 'Inbox';
            $_SESSION['folder'] = $folder;
        }
    }
}

// Fill in the folder list, folders and farray.
$folderlist = $folders = '';
$farray = array ();
$u2ucount = $u2uCommand->viewFolderList();

// Get sanitized user data
$u2uid = getRequestInt('u2uid');

$u2u_select = formArray('u2u_select');
$aid = getInt('aid');

$mod = formVar('mod');
$modaction = formVar('modaction');

$tofolder = formVar('tofolder');
$type = formVar('type');

$msgto = formVar('msgto');
$subject = formVar('subject');
$message = formVar('message');
$usesig = formVar('usesig');

$u2upreview = formVar('u2upreview');
$u2ufolders = formVar('u2ufolders');
$u2upreview = $leftpane = '';

switch ($action) {
    case 'modif' :
        switch ($mod) {
            case 'send' :
                if ($u2uid > 0) {
                    redirect('u2u.php?action=send&u2uid=' . $u2uid, 0);
                } else {
                    redirect('u2u.php?action=send', 0);
                }
                break;
            case 'reply' :
                if ($u2uid > 0) {
                    redirect('u2u.php?action=send&u2uid=' . $u2uid . '&reply=yes', 0);
                } else {
                    redirect('u2u.php?action=send&reply=yes', 0);
                }
                break;
            case 'forward' :
                if ($u2uid > 0) {
                    redirect('u2u.php?action=send&u2uid=' . $u2uid . '&forward=yes', 0);
                } else {
                    redirect('u2u.php?action=send&forward=yes', 0);
                }
                break;
            case 'sendtoemail' :
                $u2uCommand->u2u_print($u2uid, true);
                break;
            case 'delete' :
                $u2uCommand->delete($u2uid);
                break;
            case 'move' :
                $u2uCommand->move($u2uid, $tofolder);
                break;
            case 'markunread' :
                $u2uCommand->markUnread($u2uid, $type);
                break;
            default :
                $leftpane = $u2uCommand->viewFolder($folders);
                break;
        }
        break;
    case 'mod' :
        switch ($modaction) {
            case 'delete' :

                if (empty ($u2u_select)) {
                    error($lang['textnonechosen'], false, '', '', 'u2u.php', true, false, true);
                }
                $u2uCommand->mod_delete($u2u_select);
                break;
            case 'move' :
                if (empty ($tofolder)) {
                    error($lang['textnofolder'], false, '', '', 'u2u.php', true, false, true);
                }

                if ($u2u_select == '') {
                    error($lang['textnonechosen'], false, '', '', 'u2u.php', true, false, true);
                }
                $u2uCommand->mod_move($tofolder, $u2u_select);
                break;
            case 'markunread' :
                $u2uCommand->mod_markUnread($u2u_select);
                break;
            default :
                error($lang['testnothingchos'], false, '', '', 'u2u.php', true, false, true);
                break;
        }
        break;
    case 'send' :
        if (!X_STAFF && isset ($self['postnum']) && $self['postnum'] < $SETTINGS['u2uposts']) {
            error($lang['u2uinsufficentposts'], false, '', '', 'u2u.php', true, false, true);
        }

        if (isset ($_GET['memberid'])) {
            $memberid = getInt('memberid');
            $gmem_query = $db->query("SELECT username FROM " . $table_members . " WHERE uid = '$memberid' LIMIT 1");
            $gmem_array = $db->fetch_array($gmem_query);
            $username = $gmem_array['username'];
        }

        $leftpane = $u2uCommand->send($u2uid, $msgto, $subject, $message, $u2upreview);
        break;
    case 'view' :
        $leftpane = $u2uCommand->view($u2uid, $folders);
        break;
    case 'printable' :
        $u2uCommand->u2u_print($u2uid, false);
        break;
    case 'folders' :
        if (onSubmit('folderssubmit')) {
            $u2uCommand->updateFolders($u2ufolders, $folders);
        } else {
            $self['u2ufolders'] = checkOutput($self['u2ufolders']);
            eval ('$leftpane = "' . template('u2u_folders') . '";');
        }
        break;
        
    case 'ignore' :
        $leftpane = $u2uCommand->viewIgnoreList();
        break;
    case 'emptytrash' :
        $in = '';
        $iquery = $db->query("SELECT u2uid FROM " . $table_u2u . " WHERE folder = 'Trash' AND owner = '$self[username]'");
        while ($ids = $db->fetch_array($iquery)) {
            $in .= (empty ($in)) ? $ids['u2uid'] : "," . $ids['u2uid'];
        }
        $db->free_result($iquery);
        $db->query("DELETE FROM " . $table_u2u . " WHERE u2uid IN($in)");
        
        error($lang['texttrashemptied'], false, '', '', 'u2u.php', true, false, true);
        break;
    default :
        $leftpane = $u2uCommand->viewFolders($folders);
        break;
}

if (!X_STAFF) {
    $percentage = (0 == $SETTINGS['u2uquota']) ? 0 : (float) (($u2ucount / $SETTINGS['u2uquota']) * 100);
    if (100 < $percentage) {
        $barwidth = 100;
        eval ($lang['evaluqinfo_over']);
    } else {
        $percent = number_format($percentage, 2);
        $barwidth = number_format($percentage, 0);
        eval ($lang['evaluqinfo']);
    }
} else {
    $barwidth = $percentage = 0;
    eval ($lang['evalu2ustaffquota']);
}

eval ('$u2uquotabar = "' . template('u2u_quotabar') . '";');
eval ('echo stripslashes("' . template('u2u') . '");');

end_time();
eval ('echo "' . template('footer') . '";');
?>