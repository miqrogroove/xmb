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
$forums = \XMB\Services\forums();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$relpath = 'admin/fixtorphans.php';
$title = $lang['textfixothreads'];

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($title);
$core->setCanonicalLink($relpath);

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = "$title - ";
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (noSubmit('orphsubmit')) {
    $template->token = $token->create('Control Panel/Fix Orphans', 'Threads', X_NONCE_FORM_EXP);
    $template->formURL = $vars->full_url . $relpath;
    $template->select = $core->forumList('export_fid', allowall: false);
    $body = $template->process('admin_fixtorphans.php');
} else {
    $core->request_secure('Control Panel/Fix Orphans', 'Threads', error_header: true);

    $export_fid = formInt('export_fid');
    $export_forum = $forums->getForum($export_fid);
    if ($export_forum['type'] != 'forum' && $export_forum['type'] != 'sub') {
        $core->error($lang['export_fid_not_there']);
    }

    $sql->fixOrphanedThreads($export_fid);

    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>' . $lang['tool_completed'] . '</td></tr>';
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
