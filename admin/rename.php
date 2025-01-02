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
$session = \XMB\Services\session();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['admin_rename_txt']);
$core->setCanonicalLink('admin/rename.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['admin_rename_txt'] . ' - ';
}

if (! X_SADMIN) {
    error($lang['superadminonly']);
}

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (onSubmit('renamesubmit')) {
    $core->request_secure('Control Panel/Rename User', id: '', error_header: true);

    $admin = new \XMB\admin($core, $db, $session, $sql, $template, $vars);

    $vUserFrom = $core->postedVar('frmUserFrom', dbescape: false);
    $vUserTo = $core->postedVar('frmUserTo', dbescape: false);
    $myErr = $admin->rename_user($vUserFrom, $vUserTo);
    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>' . $myErr . '</td></tr>';
} else {
    $template->token = $token->create('Control Panel/Rename User', '', X_NONCE_FORM_EXP);

    $body = $template->process('admin_rename.php');
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
