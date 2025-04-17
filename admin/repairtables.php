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

define('XMB_ROOT', '../');
require XMB_ROOT . 'header.php';

$core = \XMB\Services\core();
$db = \XMB\Services\db();
$session = \XMB\Services\session();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$validate = \XMB\Services\validate();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;

$admin = new \XMB\admin($core, $db, $session, $sql, $validate, $vars);
$schema = new \XMB\Schema($db, $vars);

header('X-Robots-Tag: noindex');

$relpath = 'admin/repairtables.php';
$title = $lang['textcheck'];

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($title);
$core->setCanonicalLink($relpath);

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = "$title - ";
}

if (! X_SADMIN) {
    error($lang['superadminonly']);
}

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (onSubmit('nosubmit')) {
    $core->request_secure('Control Panel/Repair Tables', '');
    $core->redirect($vars->full_url . 'admin/', timeout: 0);
} elseif (onSubmit('yessubmit')) {
    $core->request_secure('Control Panel/Repair Tables', '');
    $auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
    $core->audit($vars->self['username'], $auditaction);

    set_time_limit(180);
    $template->command = '';
    $template->numfields = 4;

    $body = $template->process('cp_dump_query_top.php');

    $start = true;
    $tables = $schema->listTables();
    foreach($tables as $val) {
        $body .= $admin->dump_query($db->query('REPAIR TABLE `' . $vars->tablepre . $val.'`'), header: $start);
        if ($start) $start = false;
    }

    $body .= $template->process('cp_dump_query_bottom.php');
} else {
    $template->token = $token->create('Control Panel/Repair Tables', '', $vars::NONCE_AYS_EXP);
    $template->prompt = $lang['repair_confirm'];
    $template->formURL = $vars->full_url . $relpath;
    $body = $template->process('admin_ays.php');
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
