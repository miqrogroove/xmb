function Popup(url, window_name, window_width, window_height) {
    settings=
    "toolbar=no,location=no,directories=no,"+
    "status=no,menubar=no,scrollbars=yes,"+
    "resizable=yes,width="+window_width+",height="+window_height;


    NewWindow=window.open(url,window_name,settings); }


function icon(theicon) {
    AddText(theicon, messageElement);
}

function AddText(text, el) {
    // if (opener.document.input.message.createTextRange && opener.document.input.message.caretPos) {
    //    var caretPos = opener.document.input.message.caretPos;
    //    caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
    //    opener.document.input.focus();
    //}else {
    //	var els = opener.document.getElementsByName('message');
    //	
    //    opener.document.input.message.value  += text;
    //    opener.document.input.focus();
    //}
    
    
    if(el.createTextRange && el.caretPos) {
    	el.caretPos.text = (el.caretPos.text.charAt(el.caretPos.text.length-1) == ' ' ? text + ' ' : text);
    } else {
    	el.value += text;
    }
    el.focus();

}