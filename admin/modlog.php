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
$vars = \XMB\Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textmodlogs']);
$core->setCanonicalLink('admin/modlog.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textmodlogs'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $_SERVER['REQUEST_URI'];
$aapos = strpos($auditaction, "?");
if ($aapos !== false) {
    $auditaction = substr($auditaction, $aapos + 1);
}
$auditaction = $vars->onlineip . '|#|' . $auditaction;
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

$body = $template->process('admin_modlog_start.php');

$count = (int) $db->result($db->query("SELECT count(fid) FROM " . $vars->tablepre . "logs WHERE NOT (fid='0' AND tid='0')"), 0);
$template->count = $count;

$page = getInt('page');
if (! $page) {
    $page = 1;
}

$old = (($page-1)*100);
$current = ($page*100);

$template->firstpage = '';
$template->lastpage = '';
$template->prevpage = '';
$template->nextpage = '';
$template->random_var = '';

$query = $db->query("SELECT l.*, t.subject FROM " . $vars->tablepre . "logs l LEFT JOIN " . $vars->tablepre . "threads t ON l.tid=t.tid WHERE NOT (l.fid='0' AND l.tid='0') ORDER BY date ASC LIMIT $old, 100");
$template->url = '';
while($recordinfo = $db->fetch_array($query)) {
    $template->date = gmdate($vars->dateformat, (int) $recordinfo['date']);
    $template->time = gmdate($vars->timecode, (int) $recordinfo['date']);
    if ((int) $recordinfo['tid'] > 0 && $recordinfo['action'] != 'delete' && trim($recordinfo['subject']) != '') {
        $template->url = "<a href='" . $vars->full_url . "viewthread.php?tid={$recordinfo['tid']}' target='_blank'>{$recordinfo['subject']}</a>";
    } else if ($recordinfo['action'] == 'delete') {
        $recordinfo['action'] = '<strong>'.$recordinfo['action'].'</strong>';
        $template->url = '&nbsp;';
    } else {
        $template->url = 'tid='.$recordinfo['tid'].' - fid:'.$recordinfo['fid'];
    }
    $template->recordinfo = $recordinfo;
    $body .= $template->process('admin_modlog_row.php');
}

// TODO: Check if this can be replaced by the multipage functions.

if ($count > $current) {
    $page = $current/100;
    if ($page > 1) {
        $template->prevpage = '<a href="' . $vars->full_url . 'admin/modlog.php?page='.($page-1).'">&laquo; Previous Page</a>';
    }

    $template->nextpage = '<a href="' . $vars->full_url . 'admin/modlog.php?page='.($page+1).'">Next Page &raquo;</a>';

    if ($template->prevpage == '' || $template->nextpage == '') {
        $template->random_var = '';
    } else {
        $template->random_var = '-';
    }

    $last = ceil($count/100);
    if ($last > $page) {
        $template->lastpage = '<a href="' . $vars->full_url . 'admin/modlog.php?page='.$last.'">&nbsp;&raquo;&raquo;</a>';
    }

    $first = 1;
    if ($page > $first) {
        $template->firstpage = '<a href="' . $vars->full_url . 'admin/modlog.php?page='.$first.'">&nbsp;&laquo;&laquo;</a>';
    }
} else {
    if ($page > 1) {
        $template->prevpage = '<a href="' . $vars->full_url . 'admin/modlog.php?page='.($page-1).'">&laquo; Previous Page</a>';
    }

    $first = 1;
    if ($page > $first) {
        $template->firstpage = '<a href="' . $vars->full_url . 'admin/modlog.php?page='.$first.'">&nbsp;&laquo;&laquo;</a>';
    } else {
        $template->firstpage = '';
    }

    if ($template->prevpage == '' || $template->nextpage == '') {
        $template->random_var = '';
    } else {
        $template->random_var = '-';
    }
}

$body .= $template->process('admin_modlog_end.php');

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
