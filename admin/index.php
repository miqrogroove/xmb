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
$template = \XMB\Services\template();
$vars = \XMB\Services\vars();

header('X-Robots-Tag: noindex');

$core->nav($vars->lang['textcp']);
$core->setCanonicalLink('admin/');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textcp'] . ' - ';
}

$header = $template->process('header.php');

if (!X_ADMIN) {
    $noLogin = $template->process('error_nologinsession.php');
    $template->footerstuff = $core->end_time();
    $footer = $template->process('footer.php');
    echo $header, $noLogin, $footer;
    exit();
}

/* Assert Additional Security */

if (X_SADMIN) {
    $x_error = '';

    if (file_exists(ROOT.'install/') && !@rmdir(ROOT.'install/')) {
        $x_error = $lang['admin_found_install'];
    }
    if (file_exists(ROOT.'Upgrade/') && !@rmdir(ROOT.'Upgrade/') || file_exists(ROOT.'upgrade/') && !@rmdir(ROOT.'upgrade/')) {
        $x_error = $lang['admin_found_updir'];
    }
    if (file_exists(ROOT.'upgrade.php')) {
        $x_error = $lang['admin_found_upfile'];
    }

    if (strlen($x_error) > 0) {
        header('HTTP/1.0 500 Internal Server Error');
        $core->error($x_error);
    }
    unset($x_error);
}

$table = $template->process('admin_table.php');
$panel = $template->process('admin_panel.php');
$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $panel, $endTable, $footer;
