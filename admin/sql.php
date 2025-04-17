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

$attach = \XMB\Services\attach();
$core = \XMB\Services\core();
$db = \XMB\Services\db();
$session = \XMB\Services\session();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$validate = \XMB\Services\validate();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');
header('X-XSS-Protection: 0'); // Disables HTML input errors in Chrome.

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['raw_mysql']);
$core->setCanonicalLink('admin/sql.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['raw_mysql'] . ' - ';
}

if (! X_SADMIN) {
    error($lang['superadminonly']);
}

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (onSubmit('upgradesubmit')) {
    $core->request_secure('Control Panel/Insert Raw SQL', id: '');

    $admin = new \XMB\admin($core, $db, $session, $sql, $validate, $vars);

    $upgrade = getPhpInput('upgrade');
    if (isset($_FILES['sql_file'])) {
        $result = $attach->getUpload('sql_file');
        if ($result->status === UploadStatus::Success) {
            $upgrade .= $result->binaryFile;
            unlink($_FILES['sql_file']['tmp_name']);
        }
    }

    $upgrade = str_replace('$table_', $vars->tablepre, $upgrade);
    $explode = explode(";", $upgrade);
    $body = '';

    foreach ($explode as $command) {
        if ($vars->allow_spec_q !== true) {
            if (strtoupper(substr(trim($command), 0, 3)) == 'USE' || strtoupper(substr(trim($command), 0, 14)) == 'SHOW DATABASES') {
                error($lang['textillegalquery']);
            }
        }

        if (trim($command) == '') continue;

        $query = $db->query($command . ' -- Injected by ' . $vars->xmbuser . ' using admin/sql.php', panic: false);
        $template->command = cdataOut($command);
        if (is_bool($query)) {
            $template->numfields = 1;
        } else {
            $template->numfields = $db->num_fields($query);
        }

        $body .= $template->process('cp_dump_query_top.php');

        $body .= $admin->dump_query($query);

        $body .= $template->process('cp_dump_query_bottom.php');
    }
    $body .= $template->process('admin_sql_close.php');
} else {
    $template->token = $token->create('Control Panel/Insert Raw SQL', '', $vars::NONCE_FORM_EXP);

    $body = $template->process('admin_sql_form.php');
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
