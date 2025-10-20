<?php

/**
 * eXtreme Message Board
 * XMB 1.10.01
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
$template = Services\template();
$vars = Services\vars();

header('X-Robots-Tag: noindex');

$core->nav($vars->lang['textcp']);
$core->setCanonicalLink('admin/');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textcp'] . ' - ';
}

$core->assertAdminOnly();

$header = $template->process('header.php');

$table = $template->process('admin_table.php');
$panel = $template->process('admin_panel.php');
$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $panel, $endTable, $footer;
