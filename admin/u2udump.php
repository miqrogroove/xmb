<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-3
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
$token = Services\token();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$relpath = 'admin/u2udump.php';
$title = $lang['u2udump'];

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($title);
$core->setCanonicalLink($relpath);

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = "$title - ";
}

if (! X_SADMIN) {
    $core->error($lang['superadminonly']);
}

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (onSubmit('nosubmit')) {
    $core->request_secure('Control Panel/Clear All U2Us', '');
    $core->redirect($vars->full_url . 'admin/', timeout: 0);
} elseif (onSubmit('yessubmit')) {
    $core->request_secure('Control Panel/Clear All U2Us', '');

    $db->query("TRUNCATE " . $vars->tablepre . "u2u");

    $auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
    $core->audit($vars->self['username'], $auditaction);
    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>'.$lang['tool_completed'].' - '.$lang['tool_u2u'].'</td></tr>';
} else {
    $template->token = $token->create('Control Panel/Clear All U2Us', '', $vars::NONCE_AYS_EXP);
    $template->prompt = $lang['u2udump_confirm'];
    $template->formURL = $vars->full_url . $relpath;
    $body = $template->process('admin_ays.php');
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
