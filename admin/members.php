<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-1
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

const ROOT = '../';
require ROOT . 'header.php';

$core = Services\core();
$db = Services\db();
$session = Services\session();
$sql = Services\sql();
$template = Services\template();
$token = Services\token();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textmembers']);
$core->setCanonicalLink('admin/members.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textmembers'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$header .= '<script src="' . $vars->full_url . 'js/admin.js"></script>';

$table = $template->process('admin_table.php');

$members = getPhpInput('members', 'g');

$srchmem = $validate->postedVar('srchmem', dbescape: false);
$srchemail = $validate->postedVar('srchemail', dbescape: false);
$srchip = $validate->postedVar('srchip', dbescape: false);
$srchstatus = $validate->postedVar('srchstatus');
$dblikemem = $db->like_escape($srchmem);
$dblikeemail = $db->like_escape($srchemail);
$dblikeip = $db->like_escape($srchip);

$where = [];

if ($srchmem != '') {
    $where[] = "username LIKE '%$dblikemem%' ";
}
if ($srchemail != '') {
    $where[] = "email LIKE '%$dblikeemail%' ";
}
if ($srchip != '') {
    $where[] = "regip LIKE '%$dblikeip%' ";
}
if ($srchstatus != '') {
    if ($srchstatus == 'Pending') {
        $where[] = "lastvisit = 0 ";
    } else {
        $where[] = "status = '$srchstatus' ";
    }
}

if (count($where) == 0) {
    $where = '';
} else {
    $where = 'WHERE '.implode('AND ', $where);
}

$members = getPhpInput('members', 'g');

if (noSubmit('membersubmit')) {
    if (! $members) {
        $body = $template->process('admin_members_search.php');
    } elseif ($members == "search") {
        $template->token = $token->create('Control Panel/Members', 'mass-edit', $vars::NONCE_FORM_EXP);

        $body = $template->process('admin_members_edit_start.php');

        $query = $db->query("SELECT * FROM " . $vars->tablepre . "members $where ORDER BY username");

        while ($member = $db->fetch_array($query)) {
            $template->uid = $member['uid'];
            $template->username = $member['username'];
            $template->postnum = $member['postnum'];
            $template->userLink = recodeOut($member['username']);
            $template->statusAttr = $member['customstatus'];
            $template->userStatus = $core->userStatusControl("status{$member['uid']}", $member['status']);
            $template->noban = '';
            $template->u2uban = '';
            $template->postban = '';
            $template->bothban = '';

            switch ($member['ban']) {
                case 'u2u':
                    $template->u2uban = $vars::selHTML;
                    break;
                case 'posts':
                    $template->postban = $vars::selHTML;
                    break;
                case 'both':
                    $template->bothban = $vars::selHTML;
                    break;
                default:
                    $template->noban = $vars::selHTML;
                    break;
            }

            if ('0' === $member['lastvisit']) {
                $template->pending = '<br />'.$lang['textpendinglogin'];
            } else {
                $template->pending = '';
            }

            if ($member['status'] == 'Super Administrator') {
                $template->disabledelete = ' disabled="disabled"';
            } else {
                $template->disabledelete = '';
            }

            $body .= $template->process('admin_members_edit_row.php');
        }
        $template->srchmem = $srchmem;
        $template->srchemail = $srchemail;
        $template->srchip = $srchip;
        $template->srchstatus = $srchstatus;
        $body .= $template->process('admin_members_edit_end.php');
    }
} elseif (onSubmit('membersubmit')) {
    $core->request_secure('Control Panel/Members', 'mass-edit');
    $query = $db->query("SELECT uid, username, status, ban, customstatus FROM " . $vars->tablepre . "members $where");

    // Guarantee this request will not remove all Super Administrators.
    if (X_SADMIN && $db->num_rows($query) > 0) {
        $sa_count = $sql->countSuperAdmins();

        while ($mem = $db->fetch_array($query)) {
            if ($mem['status'] == 'Super Administrator' && getPhpInput('status' . $mem['uid']) != 'Super Administrator') {
                $sa_count--;
            }
        }
        if ($sa_count < 1) {
            $core->error($lang['lastsadmin']);
        }
        $db->data_seek($query, 0);
    }

    // Now execute this request
    while ($mem = $db->fetch_array($query)) {
        $origstatus = $mem['status'];
        $status = $validate->postedVar('status' . $mem['uid'], dbescape: false);
        if ($status == '') {
            $status = 'Member';
        }

        if (! X_SADMIN && ($origstatus == "Super Administrator" || $status == "Super Administrator")) {
            continue;
        }

        $delete = getInt('delete'.$mem['uid'], 'p');

        if ($delete == (int) $mem['uid'] && $delete != (int) $vars->self['uid'] && $origstatus != "Super Administrator") {
            $db->escape_fast($mem['username']);
            $db->query("DELETE FROM " . $vars->tablepre . "members WHERE uid=$delete");
            $db->query("DELETE FROM " . $vars->tablepre . "buddys WHERE username='{$mem['username']}'");
            $db->query("DELETE FROM " . $vars->tablepre . "favorites WHERE username='{$mem['username']}'");
            $db->query("DELETE FROM " . $vars->tablepre . "u2u WHERE owner='{$mem['username']}'");
            $db->query("UPDATE " . $vars->tablepre . "whosonline SET username='xguest123' WHERE username='{$mem['username']}'");
        } else {
            $edits = [];
            if ($mem['status'] !== $status) {
                $edits['status'] = $status;
            }

            $banstatus = $validate->postedVar('banstatus' . $mem['uid'], dbescape: false);
            if ($mem['ban'] !== $banstatus) {
                $edits['ban'] = $banstatus;
            }

            $cusstatus = $validate->postedVar('cusstatus' . $mem['uid'], dbescape: false);
            if ($mem['customstatus'] !== $cusstatus) {
                $edits['customstatus'] = $cusstatus;
            }

            if (count($edits) > 0) {
                $sql->updateMember((int) $mem['uid'], $edits);
            }

            if (getRawString('pw' . $mem['uid']) != '') {
                $newPass = $core->assertPasswordPolicy('pw' . $mem['uid'], 'pw' . $mem['uid']);
                $passMan = new Password($sql);
                $passMan->changePassword($mem['username'], $newPass);
                unset($newPass, $passMan);

                // Force logout and delete cookies.
                $sql->deleteWhosonline($mem['username']);
                $session->logoutAll($mem['username'], isSelf: false);
            }
        }
    }
    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>' . $lang['textmembersupdate'] . '</td></tr>';
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
