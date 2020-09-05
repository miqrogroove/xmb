var URL_REGEXP_GOES_HERE = new RegExp('(.*)://(.*)');
var defmode = 'normal';

if (defmode == 'advanced') {
    helpmode    = false;
    normalmode  = false;
    advmode     = true;
} else if (defmode == 'help') {
    helpmode    = true;
    normalmode  = false;
    advmode     = false;
} else {
    helpmode    = false;
    normalmode  = true;
    advmode     = false;
}

function chmode(switchMode) {
    if (switchMode == 1) {
        advmode     = false;
        normalmode  = false;
        helpmode    = true;
        alert(bbcode_helpmode);
    } else if (switchMode == 0) {
        helpmode    = false;
        normalmode  = false;
        advmode     = true;
        alert(bbcode_advmode);
    } else if (switchMode == 2) {
        helpmode    = false;
        advmode     = false;
        normalmode  = true;
        alert(bbcode_normode);
    }
}

function AddText(bbFirst, bbLast, text, el) {
    var len   = el.textLength;
    var start = el.selectionStart;
    var end   = el.selectionEnd;
    var pre   = el.value.substring(0, start);
    var post  = el.value.substring(end, len);
    var caret = start + bbFirst.length + text.length + bbLast.length;

    el.value = pre + bbFirst + text + bbLast + post;
    el.focus();
    el.setSelectionRange(caret,caret);
}

function wrapText(prePend, apPend, el) {
    var len   = el.textLength;
    var start = el.selectionStart;
    var end   = el.selectionEnd;
    var pre   = el.value.substring(0, start);
    var mid   = el.value.substring(start, end);
    var post  = el.value.substring(end, len);
    el.value = pre + prePend + mid + apPend + post;
}

function hasSelection(el) {
    if (el.selectionEnd-el.selectionStart > 0) {
        return true;
    } else {
        return false;
    }
}

function fetchSelection(el) {
    return el.value.substring(el.selectionStart, el.selectionEnd);
}

function email() {
    if (helpmode) {
        alert(bbcode_help_email);
    } else if (advmode) {
        if (hasSelection(messageElement)) {
            if (fetchSelection(messageElement).match(/(.+)@(.+)/) != null) {
                wrapText('[email]', '[/email]', messageElement);
            } else {
                wrapText('[email=user@example.com]', '[/email]', messageElement);
            }
        } else {
            AddText('[email]', '[/email]', ' ', messageElement);
        }
    } else {
        if (hasSelection(messageElement)) {
            if (fetchSelection(messageElement).match(/(.+)@(.+)/) != null) {
                text = prompt(bbcode_prompt_email_email, fetchSelection(messageElement));
                desc = prompt(bbcode_prompt_email_desc, '');
                while(text.length == 0 || text.match(/(.+)@(.+)/) == null) {
                    text = prompt(bbcode_prompt_email_error, fetchSelection(messageElement));
                }

                if (desc.length == 0) {
                    if (text == fetchSelection(messageElement)) {
                        wrapText('[email]', '[/email]', messageElement);
                    } else {
                        AddText('[email]', '[/email]', text, messageElement);
                    }
                } else {
                    if (text == fetchSelection(messageElement)) {
                        wrapText('[email=', ']'+desc+'[/email]', messageElement);
                    } else {
                        AddText('[email='+text+']', '[/email]', desc, messageElement);
                    }
                }
            } else {
                text = prompt(bbcode_prompt_email_email, 'user@example.com');
                while(text.length == 0 || text.match(/(.+)@(.+)/) == null) {
                    text = prompt(bbcode_prompt_email_error, text);
                }

                desc = prompt(bbcode_prompt_email_email, fetchSelection(messageElement));
                if (desc == fetchSelection(messageElement)) {
                    wrapText('[email='+text+']', '[/email]', messageElement);
                } else if (desc != null) {
                    AddText('[email='+text+']', '[/email]', desc, messageElement);
                }
            }
        } else {
            text = prompt(bbcode_prompt_email_email, 'user@example.com');
            if (text != null) {
                while(text.length == 0 || text.match(/(.+)@(.+)/) == null) {
                    text = prompt(bbcode_prompt_email_error, text);
                }

                desc = prompt(bbcode_prompt_email_desc, '');
                if (desc.length == 0) {
                    AddText('[email]', '[/email]', text, messageElement);
                } else {
                    AddText('[email='+text+']', '[/email]', desc, messageElement);
                }
            }
        }
    }
}

function chsize(size) {
    if (helpmode) {
        alert(bbcode_help_size);
    } else if (advmode) {
        if (hasSelection(messageElement)) {
            wrapText('[size='+size+']', '[/size]', messageElement);
        } else {
            AddText('[size='+size+']', '[/size]', ' ', messageElement);
        }
    } else {
        if (hasSelection(messageElement)) {
            text = prompt(bbcode_prompt_size+size, fetchSelection(messageElement));
            if (text == fetchSelection(messageElement).replace(/\n/g, ' ')) {
                wrapText('[size='+size+']', '[/size]', messageElement);
            } else if (text != null) {
                AddText('[size='+size+']', '[/size]', text, messageElement);
            }
        } else {
            text = prompt(bbcode_prompt_size+size, "Text");
            if (text.length > 0) {
                AddText('[size='+size+']', '[/size]', text, messageElement);
            } else {
                AddText('[size='+size+']', '[/size]', ' ', messageElement);
            }
        }
    }
    document.getElementById("zerosize").selected = true;
}

function chfont(font) {
    if (helpmode) {
        alert(bbcode_help_font);
    } else if (advmode) {
        if (hasSelection(messageElement)) {
            wrapText('[font='+font+']', '[/font]', messageElement);
        } else {
            AddText('[font='+font+']', '[/font]', ' ', messageElement);
        }
    } else {
        if (hasSelection(messageElement)) {
            text = prompt(bbcode_prompt_font+font, fetchSelection(messageElement));
            if (text == fetchSelection(messageElement).replace(/\n/g, ' ')) {
                wrapText('[font='+font+']', '[/font]', messageElement);
            } else if (text != null) {
                AddText('[font='+font+']', '[/font]', text, messageElement);
            }
        } else {
            text = prompt(bbcode_prompt_font+font, "Text");
            if (text.length > 0) {
                AddText('[font='+font+']', '[/font]', text, messageElement);
            } else {
                AddText('[font='+font+']', '[/font]', ' ', messageElement);
            }
        }
    }
    document.getElementById("zerofont").selected = true;
}

function bold() {
    if (helpmode) {
        alert(bbcode_help_bold);
    } else if (advmode) {
        if (hasSelection(messageElement)) {
            wrapText('[b]', '[/b]', messageElement);
        } else {
            AddText('[b]', '[/b]', ' ', messageElement);
        }
    } else {
        if (hasSelection(messageElement)) {
            text = prompt(bbcode_prompt_bold, fetchSelection(messageElement));
            if (text == fetchSelection(messageElement).replace(/\n/g, ' ')) {
                wrapText('[b]', '[/b]', messageElement);
            } else if (text != null) {
                AddText('[b]', '[/b]', text, messageElement);
            }
        } else {
            text = prompt(bbcode_prompt_bold, 'Text');
            if (text != null) {
                AddText('[b]', '[/b]', text, messageElement);
            }
        }
    }
}

function italicize() {
    if (helpmode) {
        alert(bbcode_help_italic);
    } else if (advmode) {
        if (hasSelection(messageElement)) {
            wrapText('[i]', '[/i]', messageElement);
        } else {
            AddText('[i]', '[/i]', ' ', messageElement);
        }
    } else {
        if (hasSelection(messageElement)) {
            text = prompt(bbcode_prompt_italic, fetchSelection(messageElement));
            if (text == fetchSelection(messageElement).replace(/\n/g, ' ')) {
                wrapText('[i]', '[/i]', messageElement);
            } else if (text != null) {
                AddText('[i]', '[/i]', text, messageElement);
            }
        } else {
            text = prompt(bbcode_prompt_italic, 'Text');
            if (text != null) {
                AddText('[i]', '[/i]', text, messageElement);
            }
        }
    }
}

function underline() {
    if (helpmode) {
        alert(bbcode_help_underline);
    } else if (advmode) {
        if (hasSelection(messageElement)) {
            wrapText('[u]', '[/u]', messageElement);
        } else {
            AddText('[u]', '[/u]', ' ', messageElement);
        }
    } else {
        if (hasSelection(messageElement)) {
            text = prompt(bbcode_prompt_underline, fetchSelection(messageElement));
            if (text == fetchSelection(messageElement).replace(/\n/g, ' ')) {
                wrapText('[u]', '[/u]', messageElement);
            } else if (text != null) {
                AddText('[u]', '[/u]', text, messageElement);
            }
        } else {
            text = prompt(bbcode_prompt_underline, 'Text');
            if (text != null) {
                AddText('[u]', '[/u]', text, messageElement);
            }
        }
    }
}

function youtube() {
    if (helpmode) {
        alert(bbcode_help_youtube);
    } else if (advmode) {
        if (hasSelection(messageElement)) {
            wrapText("\r\n"+'[youtube]', '[/youtube]'+"\r\n", messageElement);
        } else {
            AddText("\r\n"+'[youtube]', '[/youtube]'+"\r\n", ' ', messageElement);
        }
    } else {
        if (hasSelection(messageElement)) {
            text = prompt(bbcode_prompt_youtube, fetchSelection(messageElement));
            if (text == fetchSelection(messageElement)) {
                wrapText("\r\n"+'[youtube]', '[/youtube]'+"\r\n", messageElement);
            } else {
                AddText("\r\n"+'[youtube]', '[/youtube]'+"\r\n", text, messageElement);
            }
        } else {
            text = prompt(bbcode_prompt_youtube,"movie-id");
            if (text != null) {
                AddText("\r\n"+'[youtube]', '[/youtube]'+"\r\n", text, messageElement);
            }
        }
    }
}

function center() {
    if (helpmode) {
        alert(bbcode_help_center);
    } else if (advmode) {
        if (hasSelection(messageElement)) {
            wrapText('[align=center]', '[/align]', messageElement);
        } else {
            AddText('[align=center]', '[/align]', ' ', messageElement);
        }
    } else {
        if (hasSelection(messageElement)) {
            text = prompt(bbcode_prompt_center, fetchSelection(messageElement));
            if (text == fetchSelection(messageElement).replace(/\n/g, ' ')) {
                wrapText('[align=center]', '[/align]', messageElement);
            } else if (text != null) {
                AddText('[align=center]', '[/align]', text, messageElement);
            }
        } else {
            text = prompt(bbcode_prompt_center, 'Text');
            if (text != null) {
                AddText('[align=center]', '[/align]', text, messageElement);
            }
        }
    }
}

function image() {
    if (helpmode) {
        alert(bbcode_help_image);
    } else if (advmode) {
        if (hasSelection(messageElement)) {
            wrapText('[img]', '[/img]', messageElement);
        } else {
            AddText('[img]', '[/img]', ' ', messageElement);
        }
    } else {
        if (hasSelection(messageElement)) {
            text = prompt(bbcode_prompt_image, fetchSelection(messageElement));
            if (text == fetchSelection(messageElement)) {
                wrapText('[img]', '[/img]', messageElement);
            } else if (text != null) {
                AddText('[img]', '[/img]', text, messageElement);
            }
        } else {
            text = prompt(bbcode_prompt_image, 'http://www.example.com/image.jpg');
            if (text != null) {
                AddText('[img]', '[/img]', text, messageElement);
            }
        }
    }
}

function quote() {
    if (helpmode) {
        alert(bbcode_help_quote);
    } else if (advmode) {
        if (hasSelection(messageElement)) {
            wrapText("\r\n"+'[quote]'+"\r\n", '[/quote]'+"\r\n", messageElement);
        } else {
            AddText("\r\n"+'[quote]'+"\r\n", '[/quote]'+"\r\n", ' ', messageElement);
        }
    } else {
        if (hasSelection(messageElement)) {
            text = prompt(bbcode_prompt_quote, fetchSelection(messageElement));
            if (text == fetchSelection(messageElement).replace(/\n/g, ' ')) {
                wrapText("\r\n"+'[quote]'+"\r\n", '[/quote]'+"\r\n", messageElement);
            } else if (text != null) {
                AddText("\r\n"+'[quote]'+"\r\n", '[/quote]'+"\r\n", text, messageElement);
            }
        } else {
            text = prompt(bbcode_prompt_quote, 'lorem ipsum');
            if (text != null) {
                AddText("\r\n"+'[quote]'+"\r\n", '[/quote]'+"\r\n", text, messageElement);
            }
        }
    }
}

function code() {
    if (helpmode) {
        alert(bbcode_help_code);
    } else if (advmode) {
        if (hasSelection(messageElement)) {
            wrapText("\r\n"+'[code]', '[/code]'+"\r\n", messageElement);
        } else {
            AddText("\r\n"+'[code]', '[/code]'+"\r\n", ' ', messageElement);
        }
    } else {
        if (hasSelection(messageElement)) {
            text = prompt(bbcode_prompt_code, fetchSelection(messageElement));
            if (text == fetchSelection(messageElement).replace(/\n/g, ' ')) {
                wrapText("\r\n"+'[code]', '[/code]'+"\r\n", messageElement);
            } else if (text != null) {
                AddText("\r\n"+'[code]', '[/code]'+"\r\n", text, messageElement);
            }
        } else {
            text = prompt(bbcode_prompt_code, 'lorem ipsum');
            if (text != null) {
                AddText("\r\n"+'[code]', '[/code]'+"\r\n", text, messageElement);
            }
        }
    }
}

function chcolor(color) {
    if (helpmode) {
        alert(bbcode_help_color);
    } else if (advmode) {
        if (hasSelection(messageElement)) {
            wrapText('[color='+color+']', '[/color]', messageElement);
        } else {
            AddText('[color='+color+']', '[/color]', ' ', messageElement);
        }
    } else {
        if (hasSelection(messageElement)) {
            text = prompt(bbcode_prompt_color+color, fetchSelection(messageElement));
            if (text == fetchSelection(messageElement).replace(/\n/g, ' ')) {
                wrapText('[color='+color+']', '[/color]', messageElement);
            } else if (text != null) {
                AddText('[color='+color+']', '[/color]', text, messageElement);
            }
        } else {
            text = prompt(bbcode_prompt_color+color, "Text");
            if (text.length > 0) {
                AddText('[color='+color+']', '[/color]', text, messageElement);
            } else {
                AddText('[color='+color+']', '[/color]', ' ', messageElement);
            }
        }
    }
    document.getElementById("zerocolor").selected = true;
}

function hyperlink() {
    if (helpmode) {
        alert(bbcode_help_link);
    } else if (advmode) {
        if (hasSelection(messageElement)) {
            if (fetchSelection(messageElement).match(URL_REGEXP_GOES_HERE) != null) {
                wrapText('[url]', '[/url]', messageElement);
            } else {
                wrapText('[url=', '] [/url]', messageElement);
            }
        } else {
            AddText('[url]', '[/url]', ' ', messageElement);
        }
    } else {
        if (hasSelection(messageElement)) {
            if (fetchSelection(messageElement).match(URL_REGEXP_GOES_HERE) != null) {
                var url = prompt(bbcode_prompt_link_url, fetchSelection(messageElement));
                var desc    = prompt(bbcode_prompt_link_desc, '');
                while(url.length == 0 || url.match(URL_REGEXP_GOES_HERE) == null) {
                    url = prompt(bbcode_prompt_link_error, fetchSelection(messageElement));
                }

                if (desc.length == 0) {
                    if (url == fetchSelection(messageElement)) {
                        wrapText('[url]', '[/url]', messageElement);
                    } else {
                        AddText('[url]', '[/url]', url, messageElement);
                    }
                } else {
                    if (url == fetchSelection(messageElement)) {
                        wrapText('[url=', ']'+desc+'[/url]', messageElement);
                    } else {
                        AddText('[url='+url+']', '[/url]', desc, messageElement);
                    }
                }
            } else {
                var url = prompt(bbcode_prompt_link_url, 'http://www.example.com');
                if (url != null) {
                    while(url.length == 0 || url.match(URL_REGEXP_GOES_HERE) == null) {
                        url = prompt(bbcode_prompt_link_url_error, url);
                    }

                    var desc = prompt(bbcode_prompt_link_desc, fetchSelection(messageElement));
                    if (desc == fetchSelection(messageElement)) {
                        wrapText('[url='+url+']', '[/url]', messageElement);
                    } else if (desc != null) {
                        AddText('[url='+url+']', '[/url]', desc, messageElement);
                    }
                }
            }
        } else {
            var url = prompt(bbcode_prompt_link_url, 'http://www.example.com');
            while(url.length == 0 || url.match(URL_REGEXP_GOES_HERE) == null) {
                url = prompt(bbcode_prompt_link_url_error, url);
            }

            desc = prompt(bbcode_prompt_link_desc, '');
            if (desc.length == 0) {
                AddText('[url]', '[/url]', url, messageElement);
            } else {
                AddText('[url='+url+']', '[/url]', desc, messageElement);
            }
        }
    }
}

function list() {
    if (helpmode) {
        alert(bbcode_help_list);
    } else if (advmode) {
        if (hasSelection(messageElement)) {
            var selection = fetchSelection(messageElement);
            var listReg   = new RegExp('(?:^|\r|\n)([^\r\n]+)(?=\r|\n|$)', 'g');
            var result;
            var returnStr = '';
            while(null != (result = listReg.exec(selection))) {
                returnStr += '[*]'+result[1]+"\r\n";
            }
            AddText('[list]', '[/list]', returnStr, messageElement);
        } else {
            AddText('[list]', '[/list]', '[*]'+"\r\n"+'[*]'+"\r\n"+'[*]'+"\r\n", messageElement);
        }
    } else {
        if (hasSelection(messageElement)) {
            var type = prompt(bbcode_prompt_list_start, '');
            var cType = type.toLowerCase();
            while(cType != '' && cType != 'a' && cType != '1' && cType != null) {
                type = prompt(bbcode_prompt_list_error, type);
            }

            var selection = fetchSelection(messageElement);
            var listReg   = new RegExp('(?:^|\r|\n)([^\r\n]+)(?=\r|\n|$)', 'g');
            var result;
            var returnStr = '';
            var endStr    = '[list'+((type == '' || type == null) ? ']' : '='+type+']');
            while(null != (result = listReg.exec(selection))) {
                returnStr = prompt(bbcode_prompt_list_item+bbcode_prompt_list_end, result[1]);
                if (returnStr != result[1] && returnStr != '') {
                    while(returnStr != result[1] && returnStr != '' && returnStr != null) {
                        endStr += '[*]'+returnStr+"\r\n";
                        returnStr = prompt(bbcode_prompt_list_item+bbcode_prompt_list_end, result[1]);
                        if (returnStr == '') {
                            break;
                        } else {
                            endStr += '[*]'+returnStr+"\r\n";
                        }
                    }
                } else if (returnStr == '') {
                    break;
                } else {
                    endStr += '[*]'+returnStr+"\r\n";
                }
            }

            if (result == null) {
                while('' != (returnStr = prompt(bbcode_prompt_list_end, ''))) {
                    endStr += '[*]'+returnStr+"\r\n";
                }
            }
            endStr += '[/list'+((type == '' || type == null) ? ']' : '='+type+']');
            AddText('', '', endStr, messageElement);
        } else {
            var returnStr = '';
            var type      = prompt(bbcode_prompt_list_start, '');
            if (type != null) {
                var cType     = type.toLowerCase();
                while(cType != '' && cType != 'a' && cType != '1' && cType != null) {
                    type = prompt(bbcode_prompt_list_error, type);
                    var cType = type.toLowerCase();
                }
                var endStr = '[list'+((type == '' || type == null) ? ']' : '='+type+']');

                while('' != (returnStr = prompt(bbcode_prompt_list_end, ''))) {
                    endStr += '[*]'+returnStr+"\r\n";
                }
                endStr += '[/list'+((type == '' || type == null) ? ']' : '='+type+']');
                AddText('', '', endStr, messageElement);
            }
        }
    }
}

function storeCaret() {
    return null;
}

function loadEls() {
    messageElement = document.getElementById("message");
}
