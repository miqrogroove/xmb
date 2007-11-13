/**
 * eXtreme Message Board
 * XMB 1.9.8 Engage Final
 *
 * Developed And Maintained By The XMB Group
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 **/
function confirmAction() {
    if (arguments.length > 0) {
        var message = arguments[0];
    } else {
        return;
    }

    if (arguments.length > 1) {
        var url = arguments[1];
    } else {
        return;
    }

    if (arguments.length > 2) {
        var popup = arguments[2];
    } else {
        var popup = false;
    }

    if (window.confirm(message)) {
        if (popup) {
            Popup(url, 'new', 450, 200);
        } else {
            location.replace(url);
        }
    }
}

function confirmActionCheckbox(message, el, onCheck, onUncheck) {
    if (arguments.length > 0) {
        var message = arguments[0];
    } else {
        return;
    }

    if ((el.checked == true && onCheck == true) || (el.checked == false && onUncheck == true)) {
        if (window.confirm(message)) {
            el.checked=true
        } else {
            el.checked=false;
        }
    }
}