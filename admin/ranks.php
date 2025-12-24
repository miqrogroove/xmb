<?php

/**
 * eXtreme Message Board
 * XMB 1.10
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
$sql = Services\sql();
$template = Services\template();
$token = Services\token();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textuserranks']);
$core->setCanonicalLink('admin/ranks.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textuserranks'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (noSubmit('rankssubmit')) {
    $template->token = $token->create('Control Panel/User Ranks', 'mass-edit', $vars::NONCE_FORM_EXP);
    $body = $template->process('admin_ranks_start.php');

    $default_found = false;
    $ranks = $sql->getRanks();

    foreach ($ranks as $rank) {
        $template->deleteable = true;
        $template->staff_disable = '';
        if ($rank['title'] == 'Super Administrator' || $rank['title'] == 'Administrator' || $rank['title'] == 'Super Moderator' || $rank['title'] == 'Moderator') {
            $template->deleteable = false;
            $template->staff_disable = 'disabled="disabled"';
        } elseif ($rank['posts'] === '0' && ! $default_found) {
            $template->deleteable = false;
            $default_found = true;
            if ($rank['title'] === '') $rank['title'] = 'Newbie';
        }

        if ($rank['allowavatars'] == 'yes') {
            $template->avatarno = '';
            $template->avataryes = $vars::selHTML;
        } else {
            $template->avatarno = $vars::selHTML;
            $template->avataryes = '';
        }
        $template->rank = $rank;
        $body .= $template->process('admin_ranks_row.php');
    }
    $body .= $template->process('admin_ranks_end.php');
} else {
    $core->request_secure('Control Panel/User Ranks', 'mass-edit');
    $id = $validate->postedArray('id', 'int');
    $delete = $validate->postedArray('delete', 'int');
    $title = $validate->postedArray('title', dbescape: false);
    $posts = $validate->postedArray('posts', 'int');
    $stars = $validate->postedArray('stars', 'int');
    $allowavatars = $validate->postedArray('allowavatars', 'yesno');
    $avaurl = $validate->postedArray('avaurl', word: 'javascript');
    $newtitle = $validate->postedVar('newtitle', dbescape: false);
    $newposts = formInt('newposts');
    $newstars = formInt('newstars');
    $newallowavatars = formYesNo('newallowavatars');
    $newavaurl = $validate->postedVar('newavaurl', word: 'javascript');

    // Disabled fields are not submitted with form data, so staff rank IDs have to be retrieved again from the database.
    $ranks = $sql->getRanks();

    foreach ($ranks as $rank) {
        if ($rank['title'] == 'Super Administrator' || $rank['title'] == 'Administrator' || $rank['title'] == 'Super Moderator' || $rank['title'] == 'Moderator') {
            $title[$rank['id']] = $rank['title'];
            $posts[$rank['id']] = -1;
            if ((int) $stars[$rank['id']] == 0) {
                $stars[$rank['id']] = 1;
            }
            unset($delete[$rank['id']]);
        }
    }

    if (count($delete) > 0) {
        $sql->deleteRanksByList($delete);
    }

    foreach ($id as $key => $val) {
        if (isset($delete[$key])) continue;

        $sql->saveRank($title[$key], $posts[$key], $stars[$key], $allowavatars[$key] == 'yes', $avaurl[$key], $key);
    }

    if ($newtitle) {
        $sql->saveRank($newtitle, $newposts, $newstars, $newallowavatars == 'yes', $newavaurl);
    }
    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>' . $lang['rankingsupdate'] . '</td></tr>';
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
