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

$type = getPhpInput('type', 'g');
switch ($type) {
    case 'cp':
        $titleKey = 'textcplogs';
        $queryWhere = "(l.fid='0' AND l.tid='0')";
        $startTemplate = 'admin_log_start.php';
        $rowTemplate = 'admin_log_row.php';
        $endTemplate = 'admin_log_end.php';
        break;
    case 'mod':
        $titleKey = 'textmodlogs';
        $queryWhere = "NOT (l.fid='0' AND l.tid='0')";
        $startTemplate = 'admin_modlog_start.php';
        $rowTemplate = 'admin_modlog_row.php';
        $endTemplate = 'admin_modlog_end.php';
        break;
    default:
        http_response_code(404);
        $core->message($lang['generic_missing']);
}

$count = (int) $db->result($db->query("SELECT count(fid) FROM " . $vars->tablepre . "logs AS l WHERE $queryWhere"));

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
$pageLinkPart = ($page > 1) ? "&amp;page=$page" : '';
$old = ($page - 1) * $rowsPerPage;

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang[$titleKey]);
$core->setCanonicalLink("admin/log.php?type=$type$pageLinkPart");

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang[$titleKey] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

$body = $template->process($startTemplate);

$template->count = $count;
$template->firstpage = '';
$template->lastpage = '';
$template->prevpage = '';
$template->nextpage = '';
$template->random_var = '';
$template->url = '';

$query = $db->query("SELECT l.*, t.subject FROM " . $vars->tablepre . "logs AS l LEFT JOIN " . $vars->tablepre . "threads t USING (tid) WHERE $queryWhere ORDER BY date ASC LIMIT $old, 100");
$template->url = '';
while ($recordinfo = $db->fetch_array($query)) {
    $template->date = $core->printGmDate((int) $recordinfo['date']);
    $template->time = gmdate($vars->timecode, (int) $recordinfo['date']);
    switch ($type) {
        case 'cp':
            $action = explode('|#|', $recordinfo['action']);
            if (strpos($action[1], '/') === false) {
                $recordinfo['action'] = $action[1];
                $template->url = '&nbsp';
            } else {
                $recordinfo['action'] = '&nbsp;';
                $template->url = $action[1];
            }
            $template->action = $action;
            break;
        case 'mod':
            if ((int) $recordinfo['tid'] > 0 && $recordinfo['action'] != 'delete' && trim($recordinfo['subject'] ?? '') != '') {
                $template->url = "<a href='" . $vars->full_url . "viewthread.php?tid={$recordinfo['tid']}' target='_blank'>{$recordinfo['subject']}</a>";
            } elseif ($recordinfo['action'] == 'delete') {
                $recordinfo['action'] = "<strong>{$recordinfo['action']}</strong>";
                $template->url = "tid={$recordinfo['tid']} - fid:{$recordinfo['fid']}";
            } else {
                $template->url = "tid={$recordinfo['tid']} - fid:{$recordinfo['fid']}";
            }
    }
    $template->recordinfo = $recordinfo;
    $body .= $template->process($rowTemplate);
}

// TODO: Check if this can be replaced by the multipage functions.

if ($page != $first) {
    $template->firstpage = '<a href="' . $vars->full_url . 'admin/log.php?type=' . $type . '">&nbsp;&laquo;&laquo;</a>';
    $template->prevpage = '<a href="' . $vars->full_url . "admin/log.php?type=$type&amp;page=" . ($page - 1) . '">&laquo; Previous Page</a>';
}

if ($page != $last) {
    $template->nextpage = '<a href="' . $vars->full_url . "admin/log.php?type=$type&amp;page=" . ($page + 1) . '">Next Page &raquo;</a>';
    $template->lastpage = '<a href="' . $vars->full_url . "admin/log.php?type=$type&amp;page=" . $last . '">&nbsp;&raquo;&raquo;</a>';
}

if ($template->prevpage == '' || $template->nextpage == '') {
    $template->random_var = '';
} else {
    $template->random_var = '-';
}

$body .= $template->process($endTemplate);

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
