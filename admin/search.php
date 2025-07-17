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
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['cpsearch']);
$core->setCanonicalLink('admin/search.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['cpsearch'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (onSubmit('searchsubmit')) {
    $userip = $validate->postedVar('userip');
    $postip = $validate->postedVar('postip');
    $dblikeprofile = $db->like_escape($validate->postedVar('profileword', dbescape: false));
    $dblikepost = $db->like_escape($validate->postedVar('postword', dbescape: false));

    $msgFound = 0;
    $msgList = [];
    $userFound = 0;
    $userList = [];
    if ($userip) {
        $query = $db->query("SELECT * FROM " . $vars->tablepre . "members WHERE regip = '$userip'");
        while ($users = $db->fetch_array($query)) {
            $link = $vars->full_url . "member.php?action=viewpro&amp;member=" . recodeOut($users['username']);
            $userList[] = "<a href = '$link'>{$users['username']}<br />";
            $userFound++;
        }
    }

    if ($postip) {
        $query = $db->query("SELECT * FROM " . $vars->tablepre . "posts WHERE useip = '$postip'");
        while ($post = $db->fetch_array($query)) {
            $link = $vars->full_url . "viewthread.php?tid={$post['tid']}&amp;goto=search&amp;pid={$post['pid']}";
            if (! empty($post['subject'])) {
                $msgList[] = "<a href='$link'>" . $core->rawHTMLsubject($post['subject']) . '<br />';
            } else {
                $msgList[] = "<a href='$link'>- - {$lang['textnosub']} - -<br />";
            }
            $msgFound++;
        }
    }

    if ($dblikeprofile != '') {
        $query = $db->query("SELECT * FROM " . $vars->tablepre . "members WHERE bio LIKE '%$dblikeprofile%'");
        while ($users = $db->fetch_array($query)) {
            $link = $vars->full_url . "member.php?action=viewpro&amp;member=" . recodeOut($users['username']);
            $userList[] = "<a href='$link'>{$users['username']}<br />";
            $userFound++;
        }
    }

    if ($dblikepost != '') {
        $query = $db->query("SELECT tid, pid, subject FROM " . $vars->tablepre . "posts WHERE subject LIKE '%$dblikepost%' OR message LIKE '%$dblikepost%'");
        while ($post = $db->fetch_array($query)) {
            $link = $vars->full_url . "viewthread.php?tid={$post['tid']}&amp;goto=search&amp;pid={$post['pid']}";
            if (! empty($post['subject'])) {
                $msgList[] = "<a href='$link'>" . $core->rawHTMLsubject($post['subject']) . '<br />';
            } else {
                $msgList[] = "<a href='$link'>- - {$lang['textnosub']} - -<br />";
            }
            $msgFound++;
        }
    }
    
    $template->msgFound = $msgFound;
    $template->msgList = $msgList;
    $template->userFound = $userFound;
    $template->userList = $userList;
    $body = $template->process('admin_search_result.php');
} else {
    $query = $db->query("SELECT find FROM " . $vars->tablepre . "words");
    $select = '<select name="postword"><option value=""></option>';
    while ($temp = $db->fetch_array($query)) {
        $select .= "<option value='{$temp['find']}'>{$temp['find']}</option>";
    }
    $select .= '</select>';
    $template->select = $select;

    $body = $template->process('admin_search_form.php');
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
