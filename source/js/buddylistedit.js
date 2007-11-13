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
var isInit = false;
var attachNode = '';
var layer = '';
var clicked = 0;

function init() {
    attachNode = document.getElementById('address_add');
    layer = document.getElementById('addresses');
    isInit = true;
}

function add() {
    if (!isInit) {
        init();
    }

    if (++clicked >= 10) {
        window.alert(max_addresses_per_entry);
        return false;
    } else {
        var newChild = layer.appendChild(attachNode.cloneNode(true));
        newChild.childNodes[1].value = '';
    }
}