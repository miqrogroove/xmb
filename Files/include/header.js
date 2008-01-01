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
/*
function Popup(url, window_name, window_width, window_height) {
    settings=
    "toolbar=no,location=no,directories=no,"+
    "status=no,menubar=no,scrollbars=yes,"+
    "resizable=yes,width="+window_width+",height="+window_height;


    NewWindow=window.open(url,window_name,settings);
}
*/
var WindowObjectReference = null; // global variable
var PreviousUrl; /* global variable which will store the
                    url currently in the secondary window */

function Popup(strUrl, strWindowName, strWindowWidth, strWindowHeight)
{
  if(WindowObjectReference == null || WindowObjectReference.closed)
  {
    WindowObjectReference = window.open(strUrl, strWindowName, "width="+strWindowWidth+",height="+strWindowHeight+",resizable=yes,scrollbars=yes");
  }
  else if(PreviousUrl != strUrl)
  {
    WindowObjectReference = window.open(strUrl, strWindowName, "width="+strWindowWidth+",height="+strWindowHeight+",resizable=yes,scrollbars=yes");
    /* if the resource to load is different,
       then we load it in the already opened secondary window and then
       we bring such window back on top/in front of its parent window. */
    WindowObjectReference.focus();
  }
  else
  {
    WindowObjectReference.focus();
  };
  PreviousUrl = strUrl;
  /* explanation: we store the current url in order to compare url
     in the event of another call of this function. */
}

function icon(theicon) {
    AddText('', '', theicon, messageElement)
}

function login_focus()
{
	if (document.login.username.value == '')
		document.login.username.focus();
	else
		document.login.password.focus();
}

self.name = 'mainwindow';
// ]]>