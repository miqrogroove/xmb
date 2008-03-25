<?php
/**
 * XMB 1.9.9 Saigo
 *
 * Developed by the XMB Group Copyright (c) 2001-2008
 * Sponsored by iEntry Inc. Copyright (c) 2007
 *
 * http://xmbgroup.com , http://ientry.com
 *
 * This software is released under the GPL License, you should
 * have received a copy of this license with the download of this
 * software. If not, you can obtain a copy by visiting the GNU
 * General Public License website <http://www.gnu.org/licenses/>.
 *
 **/

require 'header.php';
require ROOT.'include/buddy.inc.php';

loadtemplates(
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
    error($lang['u2unotloggedin']);
}

$action = getVar('action');
switch($action) {
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
    default:
        buddy_display();
        break;
}
?>
