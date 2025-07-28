<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-2
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
$settings = Services\settings();
$sql = Services\sql();
$template = Services\template();
$token = Services\token();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

$admin = new admin($core, $db, $session, $settings, $sql, $validate, $vars);
$schema = new Schema($db, $vars);

header('X-Robots-Tag: noindex');

$relpath = 'admin/analyzetables.php';
$title = $lang['analyze'];

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($title);
$core->setCanonicalLink($relpath);

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = "$title - ";
}

$core->assertAdminOnly();

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (onSubmit('nosubmit')) {
    $core->request_secure('Control Panel/Analyze Tables', '');
    $core->redirect($vars->full_url . 'admin/', timeout: 0);
} elseif (onSubmit('yessubmit')) {
    $core->request_secure('Control Panel/Analyze Tables', '');
    $auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
    $core->audit($vars->self['username'], $auditaction);

    set_time_limit(180);
    $template->command = '';
    $template->numfields = 4;

    $body = $template->process('cp_dump_query_top.php');

    $start = true;
    $tables = $schema->listTables();
    foreach ($tables as $val) {
        $body .= $admin->dump_query($db->query('ANALYZE TABLE `' . $vars->tablepre . $val.'`'), header: $start);
        if ($start) $start = false;
    }

    $body .= $template->process('cp_dump_query_bottom.php');
} else {
    $template->token = $token->create('Control Panel/Analyze Tables', '', $vars::NONCE_AYS_EXP);
    $template->prompt = $lang['analyze_confirm'];
    $template->formURL = $vars->full_url . $relpath;
    $body = $template->process('admin_ays.php');
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
