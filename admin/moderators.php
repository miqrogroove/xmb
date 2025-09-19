<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00
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
$forums = Services\forums();
$sql = Services\sql();
$template = Services\template();
$token = Services\token();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textmods']);
$core->setCanonicalLink('admin/moderators.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textmods'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (noSubmit('modsubmit')) {
    $template->token = $token->create('Control Panel/Moderators', 'mass-edit', $vars::NONCE_FORM_EXP);

    $body = $template->process('admin_moderators_start.php');

    $oldfid = '0';
    $query = $db->query("SELECT f.moderator, f.name, f.fid, c.name as cat_name, c.fid as cat_fid FROM " . $vars->tablepre . "forums f LEFT JOIN " . $vars->tablepre . "forums c ON (f.fup = c.fid) WHERE (c.type='group' AND f.type='forum') OR (f.type='forum' AND f.fup='') ORDER BY c.displayorder, f.displayorder");
    while ($forum = $db->fetch_array($query)) {
        if ($oldfid !== $forum['cat_fid']) {
            $oldfid = $forum['cat_fid'];
            $template->catName = fnameOut($forum['cat_name']);
            $body .= $template->process('admin_moderators_cat.php');
        }
        $template->name = fnameOut($forum['name']);
        $template->fid = $forum['fid'];
        $template->moderator = $forum['moderator'];
        $body .= $template->process('admin_moderators_forum.php');

        $children = $forums->getChildForums((int) $forum['fid']);
        foreach ($children as $sub) {
            $template->name = fnameOut($sub['name']);
            $template->fid = $sub['fid'];
            $template->moderator = $sub['moderator'];
            $body .= $template->process('admin_moderators_sub.php');
        }
    }
    $body .= $template->process('admin_moderators_end.php');
} else {
    $core->request_secure('Control Panel/Moderators', 'mass-edit');
    $mod = $validate->postedArray('mod', dbescape: false);
    if (is_array($mod)) {
        // Retrieve valid staff names.
        $staff = $sql->getStaffNames();
        $staff = array_map('strtoupper', $staff);

        // Loop through each posted FID.
        foreach ($mod as $fid => $mods) {
            $list = explode(',', $mods);
            $list = array_map('trim', $list);
            $newlist = [];

            // Loop through each submitted name.
            foreach ($list as $moderator) {

                // Build up a new list of valid names.
                if (in_array(strtoupper($moderator), $staff)) {
                    $newlist[] = $moderator;
                }
            }

            // Save any valid names.
            if (empty($newlist)) {
                $sql->setForumMods($fid, '');
            } else {
                $mods = implode(', ', $newlist);
                $sql->setForumMods($fid, $mods);
            }
        }
    }
    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>' . $lang['textmodupdate'] . '</td></tr>';
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
