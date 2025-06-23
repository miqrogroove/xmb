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

const ROOT = '../';
require ROOT . 'header.php';

$core = \XMB\Services\core();
$db = \XMB\Services\db();
$email = \XMB\Services\email();
$session = \XMB\Services\session();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$validate = \XMB\Services\validate();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['admin_email_settings']);
$core->setCanonicalLink('admin/email.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['admin_email_settings'] . ' - ';
}

if (! X_SADMIN) {
    $core->error($lang['superadminonly']);
}

$header = $template->process('header.php');

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$table = $template->process('admin_table.php');

$admin = new admin($core, $db, $session, $sql, $validate, $vars);

if (noSubmit('settingsubmit')) {
    $template->admin = $admin;

    $template->token = $token->create('Control Panel/email', 'settings', $vars::NONCE_FORM_EXP);

    $template->mailerInConfig = ! empty($vars->mailer);
    $template->passwordAttr = attrOut($vars->settings['mailer_password'] ?? '');

    $set = $email->getSettings();
    $type = $set['type'];
    $template->mailerDefaultSel = $type == 'default' ? $vars::cheHTML : '';
    $template->mailerSymfonySel = $type == 'symfony' ? $vars::cheHTML : '';

    $template->tlsSel = [
        $set['tls'] === 'off',
        $set['tls'] === 'auto',
        $set['tls'] === 'on',
    ];

    $body = $template->process('admin_email.php');
} else {
    $core->request_secure('Control Panel/email', 'settings');

    $rawemail = getPhpInput('adminemailnew');
    $test = new EmailAddressValidator();
    if (! $test->isValid($rawemail)) {
        $core->error($lang['bademail']);
    }

    $admin->input_string_setting('adminemail', 'adminemailnew');

    if (empty($vars->mailer)) {
        $admin->input_string_setting('mailer_type', 'mailerType');
        $admin->input_string_setting('mailer_host', 'hostnew');
        $admin->input_int_setting('mailer_port', 'portnew');
        $admin->input_string_setting('mailer_username', 'usernamenew');
        $admin->input_custom_setting('mailer_password', getRawString('passwordnew'));
        $admin->input_string_setting('mailer_tls', 'tlsnew');
    }

    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>' . $lang['textsettingsupdate'] . '</td></tr>';
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
