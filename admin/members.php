<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2024, The XMB Group
 * https://www.xmbforum2.com/
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace XMB;

define('ROOT', '../');
require ROOT . 'header.php';

$core = \XMB\Services\core();
$db = \XMB\Services\db();
$session = \XMB\Services\session();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textmembers']);
$core->setCanonicalLink('admin/members.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textmembers'] . ' - ';
}

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

$members = getPhpInput('members', 'g');

$srchmem = $core->postedVar('srchmem', 'javascript', TRUE, FALSE, TRUE);
$srchemail = $core->postedVar('srchemail', 'javascript', TRUE, FALSE, TRUE);
$srchip = $core->postedVar('srchip', 'javascript', TRUE, FALSE, TRUE);
$srchstatus = $core->postedVar('srchstatus', 'javascript', TRUE, TRUE, TRUE);
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
    if (!$members) {
        $body = $template->process('admin_members_search.php');
    } else if ($members == "search") {
        $template->token = $token->create('Control Panel/Members', 'mass-edit', X_NONCE_FORM_EXP);

        $body = $template->process('admin_members_edit_start.php');

        $query = $db->query("SELECT * FROM " . $vars->tablepre . "members $where ORDER BY username");

        while($member = $db->fetch_array($query)) {
            $template->member = $member;
            $template->userLink = recodeOut($member['username']);
            $template->statusAttr = attrOut($member['customstatus']);
            $template->sadminselect = '';
            $template->adminselect = '';
            $template->smodselect = '';
            $template->modselect = '';
            $template->memselect = '';
            $template->banselect = '';
            $template->noban = '';
            $template->u2uban = '';
            $template->postban = '';
            $template->bothban = '';

            switch($member['status']) {
                case 'Super Administrator':
                    $template->sadminselect = $vars::selHTML;
                    break;
                case 'Administrator':
                    $template->adminselect = $vars::selHTML;
                    break;
                case 'Super Moderator':
                    $template->smodselect = $vars::selHTML;
                    break;
                case 'Moderator':
                    $template->modselect = $vars::selHTML;
                    break;
                case 'Member':
                    $template->memselect = $vars::selHTML;
                    break;
                case 'Banned':
                    $template->banselect = $vars::selHTML;
                    break;
                default:
                    $template->memselect = $vars::selHTML;
                    break;
            }

            switch($member['ban']) {
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
} else if (onSubmit('membersubmit')) {
    $core->request_secure('Control Panel/Members', 'mass-edit', error_header: true);
    $query = $db->query("SELECT uid, username, password, status FROM " . $vars->tablepre . "members $where");

    // Guarantee this request will not remove all Super Administrators.
    if (X_SADMIN && $db->num_rows($query) > 0) {
        $saquery = $db->query("SELECT COUNT(uid) FROM " . $vars->tablepre . "members WHERE status='Super Administrator'");
        $sa_count = (int) $db->result($saquery, 0);
        $db->free_result($saquery);

        while($mem = $db->fetch_array($query)) {
            if ($mem['status'] == 'Super Administrator' && $core->postedVar('status'.$mem['uid']) != 'Super Administrator') {
                $sa_count--;
            }
        }
        if ($sa_count < 1) {
            $core->error($lang['lastsadmin']);
        }
        $db->data_seek($query, 0);
    }

    // Now execute this request
    while($mem = $db->fetch_array($query)) {
        $origstatus = $mem['status'];
        $status = $core->postedVar('status'.$mem['uid']);
        if ($status == '') {
            $status = 'Member';
        }

        if (!X_SADMIN && ($origstatus == "Super Administrator" || $status == "Super Administrator")) {
            continue;
        }

        $banstatus = $core->postedVar('banstatus'.$mem['uid']);
        $cusstatus = $core->postedVar('cusstatus'.$mem['uid'], '', FALSE);
        $postnum = getInt('postnum'.$mem['uid'], 'p');
        $delete = getInt('delete'.$mem['uid'], 'p');

        $queryadd = '';
        if (isset($_POST['pw'.$mem['uid']])) {
            if ($_POST['pw'.$mem['uid']] != '') {
                $newpw = md5($_POST['pw'.$mem['uid']]);
                $queryadd = ", password='$newpw' ";
            }
        }

        if ($delete == (int) $mem['uid'] && $delete != (int) $self['uid'] && $origstatus != "Super Administrator") {
            $db->escape_fast($mem['username']);
            $db->query("DELETE FROM " . $vars->tablepre . "members WHERE uid=$delete");
            $db->query("DELETE FROM " . $vars->tablepre . "buddys WHERE username='{$mem['username']}'");
            $db->query("DELETE FROM " . $vars->tablepre . "favorites WHERE username='{$mem['username']}'");
            $db->query("DELETE FROM " . $vars->tablepre . "u2u WHERE owner='{$mem['username']}'");
            $db->query("UPDATE " . $vars->tablepre . "whosonline SET username='xguest123' WHERE username='{$mem['username']}'");
        } else {
            $db->query("UPDATE " . $vars->tablepre . "members SET ban='$banstatus', status='$status', postnum='$postnum', customstatus='$cusstatus'$queryadd WHERE uid={$mem['uid']}");
            if ('' != $queryadd) {
                $session->logoutAll($mem['username']);
            }
        }
    }
    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>' . $lang['textmembersupdate'] . '</td></tr>';
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
