<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
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
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['cprestricted']);
$core->setCanonicalLink('admin/restrictions.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['cprestricted'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (noSubmit('restrictedsubmit')) {
    $template->token = $token->create('Control Panel/Restrictions', 'mass-edit', X_NONCE_FORM_EXP);
    $body = $template->process('admin_restrictions_start.php');

    $query = $db->query("SELECT * FROM " . $vars->tablepre . "restricted ORDER BY id");
    while($restricted = $db->fetch_array($query)) {
        if ('1' === $restricted['case_sensitivity']) {
            $template->case_check = 'checked="checked"';
        } else {
            $template->case_check = '';
        }

        if ('1' === $restricted['partial']) {
            $template->partial_check = 'checked="checked"';
        } else {
            $template->partial_check = '';
        }
        $template->restricted = $restricted;
        $body .= $template->process('admin_restrictions_row.php');
    }
    $body .= $template->process('admin_restrictions_end.php');
} else {
    $core->request_secure('Control Panel/Restrictions', 'mass-edit', error_header: true);

    $queryrestricted = $db->query("SELECT id FROM " . $vars->tablepre . "restricted");
    while($restricted = $db->fetch_array($queryrestricted)) {
        $name = $core->postedVar('name'.$restricted['id'], '', FALSE, TRUE);
        $delete = getInt('delete'.$restricted['id'], 'p');
        $case = getInt('case'.$restricted['id'], 'p');
        $partial = getInt('partial'.$restricted['id'], 'p');
        if ($partial) {
            $partial = 1;
        }
        if ($case) {
            $case = 1;
        }
        if ($delete) {
            $db->query("DELETE FROM " . $vars->tablepre . "restricted WHERE id=$delete");
        } else {
            $db->query("UPDATE " . $vars->tablepre . "restricted SET name='$name', case_sensitivity='$case', partial='$partial' WHERE id=" . $restricted['id']);
        }
    }

    $newname = $core->postedVar('newname', '', FALSE, TRUE);
    $newcase = getInt('newcase', 'p');
    $newpartial = getInt('newpartial', 'p');
    if (!empty($newname)) {
        if ($newpartial) {
            $newpartial = 1;
        }
        if ($newcase) {
            $newcase = 1;
        }
        $db->query("INSERT INTO " . $vars->tablepre . "restricted (`name`, `case_sensitivity`, `partial`) VALUES ('$newname', '$newcase', '$newpartial')");
    }

    $link = '</p><p><a href="' . $vars->full_url . 'admin/restrictions.php">' . $lang['cprestrictedlink'] . '</a>';
    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td><p>' . $lang['restrictedupdate'] . $link . '</p></td></tr>';
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
