var node     = '';
var current  = 0;
var contents = new Array();
var running  = false;
var runid    = '';
var tnode    = '';

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

function setTickerEvent() {
    var old = (window.onload) ? window.onload : function () {};
    window.onload = function () {old(); tickerstart()};
}
