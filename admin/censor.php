<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
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

define('XMB_ROOT', '../');
require XMB_ROOT . 'header.php';

$core = \XMB\Services\core();
$db = \XMB\Services\db();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$validate = \XMB\Services\validate();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textcensors']);
$core->setCanonicalLink('admin/censor.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textcensors'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (noSubmit('censorsubmit')) {
    $template->token = $token->create('Control Panel/Censors', 'mass-edit', $vars::NONCE_FORM_EXP);
    $body = $template->process('admin_censor_start.php');

    $rows = $sql->getCensors();
    foreach ($rows as $template->censor) {
        $body .= $template->process('admin_censor_row.php');
    }
    $body .= $template->process('admin_censor_end.php');
} else {
    $newfind = $validate->postedVar('newfind', 'javascript');
    $newreplace = $validate->postedVar('newreplace', 'javascript');
    $querycensor = $db->query("SELECT id FROM " . $vars->tablepre . "words");
    while($censor = $db->fetch_array($querycensor)) {
        $find = $validate->postedVar('find'.$censor['id']);
        $replace = $validate->postedVar('replace'.$censor['id']);
        $delete = formInt('delete'.$censor['id']);

        if ($delete) {
            $db->query("DELETE FROM " . $vars->tablepre . "words WHERE id=$delete");
        }

        if ($find) {
            $db->query("UPDATE " . $vars->tablepre . "words SET find='$find', replace1='$replace' WHERE id='$censor[id]'");
        }
    }
    $db->free_result($querycensor);

    if ($newfind) {
        $db->query("INSERT INTO " . $vars->tablepre . "words (find, replace1) VALUES ('$newfind', '$newreplace')");
    }
    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>' . $lang['censorupdate'] . '</td></tr>';
}


$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
