<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2012, The XMB Group
 * http://www.xmbforum2.com/
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 **/

define('X_SCRIPT', 'buddy.php');

require 'header.php';
require ROOT.'include/buddy.inc.php';

header('X-Robots-Tag: noindex');

loadtemplates(
'buddy_u2u_inv',
'buddy_u2u_off',
'buddy_u2u_on',
'buddy_u2u',
'buddylist',
'buddylist_buddy_offline',
'buddylist_buddy_online',
'buddylist_edit',
'buddylist_edit_buddy',
'buddylist_message'
);

eval('$css = "'.template('css').'";');

if (X_GUEST) {
    redirect("{$full_url}misc.php?action=login", 0);
    exit;
}

$action = postedVar('action', '', FALSE, FALSE, FALSE, 'g');
switch($action) {
    case 'add':
        $buddys = postedVar('buddys', '', TRUE, TRUE, FALSE, 'g');
        if (empty($buddys)) {
            $buddys = postedArray('buddys');
        }
        buddy_add($buddys);
        break;
    case 'edit':
        buddy_edit();
        break;
    case 'delete':
        $delete = postedArray('delete');
        if ($delete) {
            buddy_delete($delete);
        } else {
            blistmsg($lang['nomember']);
        }
        break;
    case 'add2u2u':
        buddy_addu2u();
        break;
    default:
        buddy_display();
        break;
}
?>
