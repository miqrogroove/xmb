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