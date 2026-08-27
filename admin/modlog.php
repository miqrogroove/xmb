<?php

/**
 * eXtreme Message Board
 * XMB 1.10
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2026, The XMB Group
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
$template = Services\template();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textmodlogs']);
$core->setCanonicalLink('admin/modlog.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textmodlogs'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

$body = $template->process('admin_modlog_start.php');

$count = (int) $db->result($db->query("SELECT count(fid) FROM " . $vars->tablepre . "logs WHERE NOT (fid='0' AND tid='0')"));

$rowsPerPage = 100;
$first = 1;
$last = ceil($count / $rowsPerPage);

$page = getInt('page');
if (0 == $page) {
    $page = $first;
} elseif ($page < $first || $page > $last) {
    http_response_code(404);
    $core->message($lang['generic_missing']);
}

$old = ($page - 1) * $rowsPerPage;

$template->count = $count;
$template->firstpage = '';
$template->lastpage = '';
$template->prevpage = '';
$template->nextpage = '';
$template->random_var = '';
$template->url = '';

$query = $db->query("SELECT l.*, t.subject FROM " . $vars->tablepre . "logs l LEFT JOIN " . $vars->tablepre . "threads t ON l.tid=t.tid WHERE NOT (l.fid='0' AND l.tid='0') ORDER BY date ASC LIMIT $old, $rowsPerPage");

while ($recordinfo = $db->fetch_array($query)) {
    $template->date = $core->printGmDate((int) $recordinfo['date']);
    $template->time = gmdate($vars->timecode, (int) $recordinfo['date']);
    if ((int) $recordinfo['tid'] > 0 && $recordinfo['action'] != 'delete' && trim($recordinfo['subject'] ?? '') != '') {
        $template->url = "<a href='" . $vars->full_url . "viewthread.php?tid={$recordinfo['tid']}' target='_blank'>{$recordinfo['subject']}</a>";
    } elseif ($recordinfo['action'] == 'delete') {
        $recordinfo['action'] = "<strong>{$recordinfo['action']}</strong>";
        $template->url = "tid={$recordinfo['tid']} - fid:{$recordinfo['fid']}";
    } else {
        $template->url = "tid={$recordinfo['tid']} - fid:{$recordinfo['fid']}";
    }
    $template->recordinfo = $recordinfo;
    $body .= $template->process('admin_modlog_row.php');
}

// TODO: Check if this can be replaced by the multipage functions.

if ($page != $first) {
    $template->firstpage = '<a href="' . $vars->full_url . 'admin/modlog.php?page=' . $first . '">&nbsp;&laquo;&laquo;</a>';
    $template->prevpage = '<a href="' . $vars->full_url . 'admin/modlog.php?page=' . ($page - 1) . '">&laquo; Previous Page</a>';
}

if ($page != $last) {
    $template->nextpage = '<a href="' . $vars->full_url . 'admin/modlog.php?page=' . ($page + 1) . '">Next Page &raquo;</a>';
    $template->lastpage = '<a href="' . $vars->full_url . 'admin/modlog.php?page=' . $last . '">&nbsp;&raquo;&raquo;</a>';
}

if ($template->prevpage == '' || $template->nextpage == '') {
    $template->random_var = '';
} else {
    $template->random_var = '-';
}

$body .= $template->process('admin_modlog_end.php');

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
