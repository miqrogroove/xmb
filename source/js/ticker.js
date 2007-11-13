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
var node = '';
var current = 0;
var contents = new Array();
var running = false;
var runid = '';
var tnode = '';

function nodechange(node, text) {
    if (node.nodeType == 3) {
        node = text;
    } else {
        node.innerHTML = text;
    }
}

function tickerrun() {
    nodechange(node, contents[current]);
    if (current == contents.length-1) {
        current = 0;
    } else {
        current++;
    }
}

function tickertoggle() {
    if (running === true) {
        running = false;
        window.clearInterval(runid);
        nodechange(tnode, startticker);
    } else {
        tickerstart();
    }
}

function tickerstart() {
    node = document.getElementById("tickerdiv");
    tnode = document.getElementById("tickertoggle");
    nodechange(tnode, stopticker);
    tickerrun();
    runid = window.setInterval(tickerrun, delay, '');
    running = true;
}