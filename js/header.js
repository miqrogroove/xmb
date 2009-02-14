function setCheckboxes(the_form, the_elements, do_check) {
    if (document.forms[the_form].elements[the_elements]) {
        var elts     = document.forms[the_form].elements[the_elements];
        var elts_cnt = elts.length;

        if (elts_cnt) {
            for(var i = 0; i < elts_cnt; i++) {
                elts[i].checked = do_check;
            }
        } else {
            elts.checked = do_check;
        }
    }
    return true;
}

function invertSelection(the_form, element_name) {
    if (document.forms[the_form].elements[element_name]) {
        var elements = document.forms[the_form].elements[element_name];
        var count    = elements.length;
        if (count) {
            for(var i = 0; i < count; i++) {
                if (elements[i].checked == true) {
                    elements[i].checked = false;
                } else {
                    elements[i].checked = true;
                }
            }
        } else {
            if (elements.checked == true) {
                elements.checked = false;
            } else {
                elements.checked = true;
            }
        }
    }
    return true;
}

function Popup(url, window_name, window_width, window_height) {
    settings=
    "toolbar=no,location=no,directories=no,"+
    "status=no,menubar=no,scrollbars=yes,"+
    "resizable=yes,width="+window_width+",height="+window_height;
    window.open(url,window_name,settings);
}


function icon(theicon) {
    AddText('', '', theicon, messageElement)
}

function avatarCheck(input, max_size) {
    var image = new Image();
    var avatarCheck = document.getElementById('avatarCheck');
    var isValid = document.getElementById('newavatarcheck');
    image.onload = function() {
        max_size = max_size.split("x");

        if (input.value == "") {
            avatarCheck.innerHTML = "";
            isValid.value = "no";
        } else if (image.width == 0 || image.height == 0) {
            isValid.value = "no";
            avatarCheck.style.color = "#ff0000";
            avatarCheck.innerHTML = "Invalid: Invalid Image";
        } else if ((image.width > max_size[0] && max_size[0] != 0) || (image.height > max_size[1] && max_size[1] != 0)) {
            isValid.value = "no";
            avatarCheck.style.color = "#ff0000";
            avatarCheck.innerHTML = "Invalid: Image Too Large (Max Size = " + max_size[0] + "x" + max_size[1] + ")";
        } else {
            avatarCheck.style.color = "#00ff00";
            isValid.value = "yes";
            avatarCheck.innerHTML = "Valid Image";
        }
    }
    if (input.value.substring(0, 7) == 'http://' || input.value.substring(0, 6) == 'ftp://') {
        avatarCheck.innerHTML = "Checking URL...";
        image.src = input.value;
    } else {
        if (input.value == '') {
            avatarCheck.innerHTML = "";
        } else {
            avatarCheck.style.color = "#ff0000";
            avatarCheck.innerHTML = "Invalid: Invalid Image";
        }
        isValid.value = "no";
    }
}

self.name = 'mainwindow';
