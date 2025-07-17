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

require './header.php';

$core = Services\core();
$db = Services\db();
$sql = Services\sql();
$template = Services\template();
$validate = Services\validate();
$vars = Services\vars();

header('X-Robots-Tag: noindex');

if (X_GUEST) {
    $core->redirect($vars->full_url . 'misc.php?action=login', timeout: 0);
    exit;
}

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textbuddylist'] . ' - ';
}

$buddy = new BuddyManager($core, $db, $sql, $template, $vars);

$action = getPhpInput('action', 'g');
switch ($action) {
    case 'add':
        $buddys = $validate->postedArray('buddys', source: 'r');
        $buddy->add($buddys);
        break;
    case 'edit':
        $buddy->edit();
        break;
    case 'delete':
        $delete = $validate->postedArray('delete');
        if ($delete) {
            $buddy->delete($delete);
        } else {
            $buddy->blistmsg($vars->lang['nomember']);
        }
        break;
    case 'add2u2u':
        // This action is obscured by a client-side script.  It gets called from one of the u2u.php templates that invokes u2uheader.js::aBook().
        // However, the route looks like it could be determined just as easily on the server side and provided as a normal link.
        $buddy->addu2u();
        break;
    default:
        $buddy->display();
}
