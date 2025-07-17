<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-1
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
$template = Services\template();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textcplogs']);
$core->setCanonicalLink('admin/log.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textcplogs'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

$body = $template->process('admin_log_start.php');

$count = (int) $db->result($db->query("SELECT count(fid) FROM " . $vars->tablepre . "logs WHERE (fid='0' AND tid='0')"), 0);
$template->count = $count;

$page = getInt('page');
if (!$page) {
    $page = 1;
}

$old = (($page-1)*100);
$current = ($page*100);

$template->firstpage = '';
$template->lastpage = '';
$template->prevpage = '';
$template->nextpage = '';
$template->random_var = '';

$query = $db->query("SELECT l.*, t.subject FROM " . $vars->tablepre . "logs l LEFT JOIN " . $vars->tablepre . "threads t ON l.tid=t.tid WHERE (l.fid='0' AND l.tid='0') ORDER BY date ASC LIMIT $old, 100");
$template->url = '';
while ($recordinfo = $db->fetch_array($query)) {
    $template->date = $core->printGmDate((int) $recordinfo['date']);
    $template->time = gmdate($vars->timecode, (int) $recordinfo['date']);
    $action = explode('|#|', $recordinfo['action']);
    if (strpos($action[1], '/') === false) {
        $recordinfo['action'] = $action[1];
        $template->url = '&nbsp';
    } else {
        $recordinfo['action'] = '&nbsp;';
        $template->url = $action[1];
    }
    $template->action = $action;
    $template->recordinfo = $recordinfo;
    $body .= $template->process('admin_log_row.php');
}

// TODO: Check if this can be replaced by the multipage functions.
// Also, figure out how much of this just duplicates the modlog.php script.

if ($count > $current) {
    $page = $current/100;
    if ($page > 1) {
        $template->prevpage = '<a href="' . $vars->full_url . 'admin/log.php?page='.($page-1).'">&laquo; Previous Page</a>';
    }

    $template->nextpage = '<a href="' . $vars->full_url . 'admin/log.php?page='.($page+1).'">Next Page &raquo;</a>';

    if ($template->prevpage == '' || $template->nextpage == '') {
        $template->random_var = '';
    } else {
        $template->random_var = '-';
    }

    $last = ceil($count/100);
    if ($last > $page) {
        $template->lastpage = '<a href="' . $vars->full_url . 'admin/log.php?page='.$last.'">&nbsp;&raquo;&raquo;</a>';
    }

    $first = 1;
    if ($page > $first) {
        $template->firstpage = '<a href="' . $vars->full_url . 'admin/log.php?page='.$first.'">&nbsp;&laquo;&laquo;</a>';
    }
} else {
    if ($page == 1) {
        $template->prevpage = '';
    } else {
        $template->prevpage = '<a href="' . $vars->full_url . 'admin/log.php?page='.($page-1).'">&laquo; Previous Page</a>';
    }

    $first = 1;
    if ($page > $first) {
        $template->firstpage = '<a href="' . $vars->full_url . 'admin/log.php?page='.$first.'">&nbsp;&laquo;&laquo;</a>';
    }
}

$body .= $template->process('admin_log_end.php');

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
