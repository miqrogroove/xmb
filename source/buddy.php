<?php
/**
 * XMB 1.9.8 Engage Final
 *
 * Developed By The XMB Group
 * Copyright (c) 2001-2007, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * Copyright (c) 2007, iEntry, Inc.
 * http://www.ientry.com
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 **/

require_once('header.php');
require_once(ROOT.'include/buddy.inc.php');

loadtemplates(
'buddy_u2u',
'buddy_u2u_inv',
'buddy_u2u_off',
'buddy_u2u_on',
'buddylist',
'buddylist_buddy_offline',
'buddylist_buddy_online',
'buddylist_edit',
'buddylist_edit_buddy',
'buddylist_message'
);

eval('$css = "'.template('css').'";');

if (X_GUEST) {
    error($lang['u2unotloggedin']);
}

$action = getVar('action');
switch ($action) {
    case 'add':
        $buddys = getVar('buddys');
        if (empty($buddys)) {
            $buddys = formArray('buddys');
        }
        buddy_add($buddys);
        break;
    case 'edit':
        buddy_edit();
        break;
    case 'delete':
        $delete = formArray('delete');
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