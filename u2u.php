<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

require './header.php';

header('X-Robots-Tag: noindex');

$core = Services\core();
$db = Services\db();
$email = Services\email();
$sql = Services\sql();
$template = Services\template();
$tran = Services\translation();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

$u2u = new U2U($core, $db, $email, $sql, $template, $tran, $validate, $vars);

$action = getPhpInput('action', 'g');
$sendmode = ($action == 'send') ? "true" : "false";

if (X_GUEST) {
    $core->redirect($vars->full_url . "misc.php?action=login", timeout: 0);
    exit;
}

$folder = $validate->postedVar('folder', dbescape: false);
if ($folder == '') {
    $folder = $validate->postedVar('folder', dbescape: false, sourcearray: 'g');
    if ($folder == '') {
        $folder = 'Inbox';
    }
}
$u2u->setFolder($folder);

$tofolder = $validate->postedVar('tofolder', dbescape: false);

$u2ucount = $u2u->folderList();
$u2uid = formInt('u2uid');
if ($u2uid == 0) {
    $u2uid = getInt('u2uid');
}

$template->thewidth = '100%';

$template->leftpane = '';

switch ($action) {
    case 'modif':
        $mod = getPhpInput('mod');
        switch ($mod) {
            // TODO: What is the purpose of any of these redirects?  At best, they are converting a POST request to a GET.
            case 'send':
                if ($u2uid > 0) {
                    $core->redirect($vars->full_url . "u2u.php?action=send&u2uid=$u2uid", 0);
                } else {
                    $core->redirect($vars->full_url . 'u2u.php?action=send', 0);
                }
                break;
            case 'reply':
                if ($u2uid > 0) {
                    $core->redirect($vars->full_url . "u2u.php?action=send&u2uid=$u2uid&reply=yes", 0);
                } else {
                    $core->redirect($vars->full_url . "u2u.php?action=send&reply=yes", 0);
                }
                break;
            case 'forward':
                if ($u2uid > 0) {
                    $core->redirect($vars->full_url . "u2u.php?action=send&u2uid=$u2uid&forward=yes", 0);
                } else {
                    $core->redirect($vars->full_url . "u2u.php?action=send&forward=yes", 0);
                }
                break;
            case 'sendtoemail':
                $u2u->printOrEmail($u2uid, eMail: true);
                break;
            case 'delete':
                $u2u->delete($u2uid);
                break;
            case 'move':
                $u2u->move($u2uid, $tofolder);
                break;
            case 'markunread':
                // This value is related to template u2u_view.php
                $type = getPhpInput('type');
                $u2u->markUnread($u2uid, $type);
                break;
            default:
                $template->leftpane = $u2u->display();
                break;
        }
        break;
    case 'mod':
        $modaction = getPhpInput('modaction');
        $u2u_select = getFormArrayInt('u2u_select');
        $folder_url = recodeOut($folder);
        switch ($modaction) {
            case 'delete':
                if (! isset($u2u_select) || empty($u2u_select)) {
                    $u2u->error($lang['textnonechosen'], $vars->full_url . "u2u.php?folder=$folder_url");
                }
                $u2u->modDelete($u2u_select);
                break;
            case 'move':
                if (! isset($tofolder) || empty($tofolder)) {
                    $u2u->error($lang['textnofolder'], $vars->full_url . 'u2u.php');
                }

                if (! isset($u2u_select) || empty($u2u_select)) {
                    $u2u->error($lang['textnonechosen'], $vars->full_url . "u2u.php?folder=$folder_url");
                }
                $u2u->modMove($tofolder, $u2u_select);
                break;
            case 'markunread':
                if (! isset($u2u_select) || empty($u2u_select)) {
                    $u2u->error($lang['textnonechosen'], $vars->full_url . "u2u.php?folder=$folder_url");
                }
                $u2u->modMarkUnread($u2u_select);
                break;
            default:
                $u2u->error($lang['testnothingchos'], $vars->full_url . "u2u.php?folder=$folder_url");
                break;
        }
        break;
    case 'send':
        $msgto = $validate->postedVar('msgto', dbescape: false);
        $subject = $validate->postedVar('subject', dbescape: false);
        $message = $validate->postedVar('message', dbescape: false, quoteencode: false);
        $template->leftpane = $u2u->send($u2uid, $msgto, $subject, $message);
        break;
    case 'view':
        $template->leftpane = $u2u->view($u2uid);
        break;
    case 'printable':
        $u2u->printOrEmail($u2uid);
        break;
    case 'folders':
        if (onSubmit('folderssubmit')) {
            $u2ufolders = $validate->postedVar('u2ufolders', dbescape: false);
            $u2u->folderSubmit($u2ufolders);
        } else {
            $template->hU2ufolders = $vars->self['u2ufolders'];
            $template->leftpane = $template->process('u2u_folders.php');
        }
        break;
    case 'ignore':
        $template->leftpane = $u2u->ignore();
        break;
    case 'emptytrash':
        $db->query("DELETE FROM " . $vars->tablepre . "u2u WHERE folder = 'Trash' AND owner = '" . $vars->xmbuser . "'");
        $u2u->msg($lang['texttrashemptied'], $vars->full_url . 'u2u.php');
        break;
    default:
        $template->leftpane = $u2u->display();
        break;
}

if (! X_STAFF) {
    $percentage = (0 == (int) $vars->settings['u2uquota']) ? 0 : (float)(($u2ucount / (int) $vars->settings['u2uquota']) * 100);
    if ($percentage > 100) {
        $template->barwidth = 100;
        $search  = ['$u2ucount', '$u2uquota'];
        $replace = [$u2ucount, $vars->settings['u2uquota']];
        $template->uqinfo = str_replace($search, $replace, $lang['evaluqinfo_over']);
    } else {
        $percent = number_format($percentage, 2);
        $template->barwidth = number_format($percentage, 0);
        $search  = ['$u2ucount', '$percent', '$u2uquota'];
        $replace = [$u2ucount, $percent, $vars->settings['u2uquota']];
        $template->uqinfo = str_replace($search, $replace, $lang['evaluqinfo']);
    }
} else {
    $template->barwidth = $percentage = 0;
    $template->uqinfo = str_replace('$u2ucount', (string) $u2ucount, $lang['evalu2ustaffquota']);
}

$template->u2uheader = $u2u->getHeader();
$template->u2uquotabar = $template->process('u2u_quotabar.php');
$template->u2ufooter = $u2u->getFooter();

$template->process("u2u.php", echo: true);
