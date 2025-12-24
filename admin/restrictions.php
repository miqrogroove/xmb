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
$core->nav($lang['cprestricted']);
$core->setCanonicalLink('admin/restrictions.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['cprestricted'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (noSubmit('restrictedsubmit')) {
    $template->token = $token->create('Control Panel/Restrictions', 'mass-edit', $vars::NONCE_FORM_EXP);
    $body = $template->process('admin_restrictions_start.php');

    $restrictions = $sql->getRestrictions();
    foreach ($restrictions as $restricted) {
        if ('1' === $restricted['case_sensitivity']) {
            $template->case_check = 'checked="checked"';
        } else {
            $template->case_check = '';
        }

        if ('1' === $restricted['partial']) {
            $template->partial_check = 'checked="checked"';
        } else {
            $template->partial_check = '';
        }
        $template->restricted = $restricted;
        $body .= $template->process('admin_restrictions_row.php');
    }
    unset($restrictions);
    $body .= $template->process('admin_restrictions_end.php');
} else {
    $core->request_secure('Control Panel/Restrictions', 'mass-edit');

    $restrictions = $sql->getRestrictions();
    foreach ($restrictions as $restricted) {
        $name = $validate->postedVar('name' . $restricted['id'], dbescape:false);
        $delete = formInt('delete' . $restricted['id']);
        $case = (bool) formInt('case' . $restricted['id']);
        $partial = (bool) formInt('partial' . $restricted['id']);
        if ($delete) {
            $sql->deleteRestriction($delete);
        } elseif ($name !== $restricted['name'] || $case !== (bool) $restricted['case_sensitivity'] || $partial !== (bool) $restricted['partial']) {
            $sql->updateRestriction((int) $restricted['id'], $name, $case, $partial);
        }
    }
    unset($restrictions);

    $newname = $validate->postedVar('newname', dbescape:false);
    $newcase = (bool) formInt('newcase');
    $newpartial = (bool) formInt('newpartial');
    if (! empty($newname)) {
        $sql->addRestriction($newname, $newcase, $newpartial);
    }

    $link = '</p><p><a href="' . $vars->full_url . 'admin/restrictions.php">' . $lang['cprestrictedlink'] . '</a>';
    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td><p>' . $lang['restrictedupdate'] . $link . '</p></td></tr>';
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
