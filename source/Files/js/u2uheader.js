var aBookOpen = false;
var aBookLink = '';
self.name = 'u2u';

function setCheckboxes(the_form, do_check) {
    var elts      = document.forms[the_form].elements['u2u_select[]']
    var elts_cnt  = elts.length;

    if (elts_cnt) {
        for (var i = 0; i < elts_cnt; i++) {
            elts[i].checked = do_check;
        }
    }

    return true;
}

function invertSelection(the_form, element_name) {
    var elements    = document.forms[the_form].elements[element_name];
    var count    = elements.length;

    if (count) {
        for (var i = 0; i < count; i++) {
            if(elements[i].checked == true) {
                elements[i].checked = false;
            } else {
                elements[i].checked = true;
            }
        }
    }

    return true;
}

function aBook() {
    if(aBookOpen == true) {
        aBookLink.close();
        aBookOpen = false;
    } else {
        if(typeof sendMode === "undefined" || sendMode != true) {
        	aBookLink = window.open('buddy.php', 'aBook', "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=450,height=400");
        } else {
        	aBookLink = window.open('buddy.php?action=add2u2u', 'aBook', "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=450,height=400");    
        }
        aBookOpen = true;
    }

    return false;
}