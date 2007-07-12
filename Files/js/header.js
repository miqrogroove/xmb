// <![CDATA[

function setCheckboxes(the_form, the_elements, do_check) {
    var elts      = document.forms[the_form].elements[the_elements]
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

function Popup(url, window_name, window_width, window_height) {
    settings=
    "toolbar=no,location=no,directories=no,"+
    "status=no,menubar=no,scrollbars=yes,"+
    "resizable=yes,width="+window_width+",height="+window_height;


    NewWindow=window.open(url,window_name,settings);
}


function icon(theicon) {
    AddText('', '', theicon, messageElement)
}

self.name = 'mainwindow';
// ]]>