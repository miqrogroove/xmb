// <![CDATA[
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
        for (var i=0; i < count; i++) {
            if(elements[i].checked == true) {
                elements[i].checked = false;
            } else {
                elements[i].checked = true;
            }
        }
    }

    return true;
}
// ]]>