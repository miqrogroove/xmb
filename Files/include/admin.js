function confirmAction() {
    if(arguments.length > 0) {
        var message = arguments[0];
    } else {
        return;
    }

    if(arguments.length > 1) {
        var url = arguments[1];
    } else {
        return;
    }

    if(arguments.length > 2) {
        var popup = arguments[2];
    } else {
        var popup = false;
    }

    if(window.confirm(message)) {
        if(popup) {
            Popup(url, 'new', 450, 200);
        } else {
            location.replace(url);
        }
    }
}

function confirmActionCheckbox(message, el, onCheck, onUncheck) {
    if(arguments.length > 0) {
        var message = arguments[0];
    } else {
        return;
    }

    if((el.checked == true && onCheck == true) || (el.checked == false && onUncheck == true)) {
        if(window.confirm(message)) {
            el.checked=true
        } else {
            el.checked=false;
        }
    }
}