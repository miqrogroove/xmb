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
$db = \XMB\Services\db();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textmods']);
$core->setCanonicalLink('admin/moderators.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textmods'] . ' - ';
}

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (noSubmit('modsubmit')) {
    $body = $template->process('admin_moderators_start.php');

    $oldfid = '0';
    $query = $db->query("SELECT f.moderator, f.name, f.fid, c.name as cat_name, c.fid as cat_fid FROM " . $vars->tablepre . "forums f LEFT JOIN " . $vars->tablepre . "forums c ON (f.fup = c.fid) WHERE (c.type='group' AND f.type='forum') OR (f.type='forum' AND f.fup='') ORDER BY c.displayorder, f.displayorder");
    while($forum = $db->fetch_array($query)) {
        if ($oldfid !== $forum['cat_fid']) {
            $oldfid = $forum['cat_fid'];
            $template->catName = fnameOut($forum['cat_name'])
            $body .= $template->process('admin_moderators_cat.php');
        }
        $template->name = fnameOut($forum['name']);
        $template->fid = $forum['fid'];
        $template->moderator = $forum['moderator'];
        $body .= $template->process('admin_moderators_forum.php');

        $querys = $db->query("SELECT name, fid, moderator FROM " . $vars->tablepre . "forums WHERE fup='".$forum['fid']."' AND type='sub'");
        while($sub = $db->fetch_array($querys)) {
            $template->name = fnameOut($sub['name']);
            $template->fid = $sub['fid'];
            $template->moderator = $sub['moderator'];
            $body .= $template->process('admin_moderators_sub.php');
        }
    }
    $body .= $template->process('admin_moderators_end.php');
} else {
    $mod = $core->postedArray('mod', dbescape: false);
    if (is_array($mod)) {
        foreach($mod as $fid => $mods) {
            $sql->setForumMods($fid, $mods);
        }
    }
    echo '<tr bgcolor="' . $THEME['altbg2'] . '" class="ctrtablerow"><td>' . $lang['textmodupdate'] . '</td></tr>';
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
