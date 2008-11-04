<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Alpha Three - This software should not be used for any purpose after 31 December 2008.
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2008, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * Copyright (c) 2007, iEntry, Inc.
 * http://www.ientry.com
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 **/

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

//loginUser() is responsible for accepting credentials for new sessions.
//$xmbuserinput must be html escaped & db escaped username input.
//$xmbpwinput must be raw password hash input.
function loginUser($xmbuserinput, $xmbpwinput, $invisible=FALSE, $tempcookie=FALSE) {
    global $server, $self, $onlineip, $onlinetime, $db, $cookiepath, $cookiedomain, $cookiesecure;

    if (elevateUser($xmbuserinput, $xmbpwinput)) {
        $dbname = $db->escape($self['username']);

        if ($invisible) {
            $db->query("UPDATE ".X_PREFIX."members SET invisible='1' WHERE username='$dbname'");
        } else {
            $db->query("UPDATE ".X_PREFIX."members SET invisible='0' WHERE username='$dbname'");
        }

        if ($tempcookie) {
            $currtime = 0;
        } else {
            $currtime = $onlinetime + (86400*30);
        }

        if ($server == 'Mic') {
            $setusing = X_SET_JS;
        } else {
            $setusing = X_SET_HEADER;
        }
        put_cookie("xmbuser", $self['username'], $currtime, $cookiepath, $cookiedomain, $cookiesecure, $setusing);
        put_cookie("xmbpw", $xmbpwinput, $currtime, $cookiepath, $cookiedomain, $cookiesecure, $setusing);
        return TRUE;
    } else {
        return FALSE;
    }
}

//elevateUser() is responsible for authenticating established sessions and setting up session variables.
//$xmbuserinput must be html escaped & db escaped username input.
//$xmbpwinput must be raw password hash input.
function elevateUser($xmbuserinput, $xmbpwinput) {
    global $xmbuser, $xmbpw, $self, $db, $SETTINGS, $status_enum;

    $xmbuser = '';
    $xmbpw = '';
    $self = array();

    //Usernames are historically html encoded in the XMB database, as well as in cookies.
    //$xmbuser is often used as a raw value in queries and should be sql escaped.
    //$self['username'] is a good alternative for future template use.
    //$xmbpw was historically abused and will no longer contain a value.

    $query = $db->query("SELECT * FROM ".X_PREFIX."members WHERE username='$xmbuserinput'");
    if ($db->num_rows($query) == 1) {
        $self = $db->fetch_array($query); //The self array will remain available, global.
        if ($self['password'] == $xmbpwinput) {
            $xmbuser = $db->escape($self['username']);
        }
        $self['password'] = '';
    }
    $db->free_result($query);

    $xmbuserinput = '';
    $xmbpwinput = '';

    //Database routine complete.  Now set the user status constants.

    if ($xmbuser != '') {
        // Initialize the new translation system
        if (X_SCRIPT != 'upgrade.php') {
            if (!loadLang($self['langfile'])) {
                if (!loadLang($SETTINGS['langfile'])) {
                    require_once(ROOT.'include/translation.inc.php');
                    langPanic();
                }
            }
        }

        if ($self['status'] == 'Banned') {
            $xmbuser = '';
            $self = array();
            $self['status'] = 'Banned';
            if (!defined('X_GUEST')) {
                define('X_MEMBER', FALSE);
                define('X_GUEST', TRUE);
            }
        } else {
            if (!defined('X_GUEST')) {
                define('X_MEMBER', TRUE);
                define('X_GUEST', FALSE);
            }
            $db->query("UPDATE ".X_PREFIX."members SET lastvisit=".$db->time(time())." WHERE username='$xmbuser'");
        }
    } else {
        if (X_SCRIPT != 'upgrade.php') {
            if (!loadLang($SETTINGS['langfile'])) {
                require_once(ROOT.'include/translation.inc.php');
                langPanic();
            }
        }

        $self = array();
        $self['status'] = '';
        if (!defined('X_GUEST')) {
            define('X_MEMBER', FALSE);
            define('X_GUEST', TRUE);
        }
    }

    // Enumerate status
    if (isset($status_enum[$self['status']])) {
        $int_status = $status_enum[$self['status']];
    } else {
        $int_status = $status_enum['Member']; // If $self['status'] contains an unknown value, default to Member.
    }

    if (!defined('X_STAFF')) {
        define('X_SADMIN', ($self['status'] == 'Super Administrator'));
        define('X_ADMIN', ($int_status <= $status_enum['Administrator']));
        define('X_SMOD', ($int_status <= $status_enum['Super Moderator']));
        define('X_MOD', ($int_status <= $status_enum['Moderator']));
        define('X_STAFF', X_MOD);
    }

    // Set more globals
    global $timeoffset, $themeuser, $status, $tpp, $ppp, $memtime, $dateformat,
           $sig, $invisible, $timecode, $dformatorig, $onlineuser;

    if ($xmbuser != '') {
        $timeoffset = $self['timeoffset'];
        $themeuser = $self['theme'];
        $status = $self['status'];
        $tpp = $self['tpp'];
        $ppp = $self['ppp'];
        $memtime = $self['timeformat'];
        $dateformat = $self['dateformat'];
        $sig = $self['sig'];
        $invisible = $self['invisible'];
        $onlineuser = $xmbuser;
    } else {
        $timeoffset = $SETTINGS['def_tz'];
        $themeuser = '';
        $status = 'member';
        $tpp = $SETTINGS['topicperpage'];
        $ppp = $SETTINGS['postperpage'];
        $memtime = $SETTINGS['timeformat'];
        $sig = '';
        $invisible = 0;
        $onlineuser = 'xguest123';
        $self['ban'] = '';
        $self['sig'] = '';
        $self['username'] = '';
    }

    if ($memtime == 24) {
        $timecode = "H:i";
    } else {
        $timecode = "h:i A";
    }

    $dformatorig = $dateformat;
    $dateformat = str_replace(array('mm', 'MM', 'dd', 'DD', 'yyyy', 'YYYY', 'yy', 'YY'), array('n', 'n', 'j', 'j', 'Y', 'Y', 'y', 'y'), $dateformat);

    // Save This Session
    global $onlineip, $onlinetime, $url;

    $wollocation = $db->escape($url);
    $newtime = $onlinetime - 600;
    $db->query("DELETE FROM ".X_PREFIX."whosonline WHERE ((ip='$onlineip' && username='xguest123') OR (username='$xmbuser') OR (time < '$newtime'))");
    $db->query("INSERT INTO ".X_PREFIX."whosonline (username, ip, time, location, invisible) VALUES ('$onlineuser', '$onlineip', ".$db->time($onlinetime).", '$wollocation', '$invisible')");

    return ($xmbuser != '');
}

// loadLang() uses the new translation database to populate the old $lang and $langfile variables.
// Parameter $devname is the name specified by XMB for internal use (usually written in English).
function loadLang($devname = "English") {
    global $charset, $db, $lang, $langfile;

    // Query The Translation Database
    $sql = 'SELECT k.langkey, t.cdata '
         . 'FROM '.X_PREFIX.'lang_keys AS k'
         . ' LEFT JOIN '.X_PREFIX.'lang_text AS t USING (phraseid)'
         . ' INNER JOIN '.X_PREFIX.'lang_base AS b USING (langid)'
         . "WHERE b.devname = '$devname'";
    $result = $db->query($sql);

    // Load the $lang array.
    if ($db->num_rows($result) > 0) {
        $langfile = $devname;
        $lang = array();
        while($row = $db->fetch_array($result)) {
            $lang[$row['langkey']] = $row['cdata'];
        }
        $db->free_result($result);
        $charset = $lang['charset'];
        return TRUE;
    } else {
        return FALSE;
    }
}

// loadPhrase uses the new translation database to retrieve a single phrase in all available languages.
// Parameter $langkey is the same string used as the $lang array key.
// Returns an associative array in which lang_base.devname is the key.
function loadPhrase($langkey) {
    global $db;

    // Query The Translation Database
    $sql = 'SELECT b.devname, t.cdata '
         . 'FROM '.X_PREFIX.'lang_base AS b'
         . ' LEFT JOIN '.X_PREFIX.'lang_text AS t USING (langid)'
         . ' INNER JOIN '.X_PREFIX.'lang_keys AS k USING (phraseid)'
         . "WHERE k.langkey = '$langkey'";
    $result = $db->query($sql);

    // Load the $lang array.
    if ($db->num_rows($result) > 0) {
        $phrase = array();
        while($row = $db->fetch_array($result)) {
            $phrase[$row['devname']] = $row['cdata'];
        }
        $db->free_result($query);
        return $phrase;
    } else {
        return FALSE;
    }
}

function nav($add=false, $raquo=true) {
    global $navigation;

    if (!$add) {
        $navigation = '';
    } else {
        $navigation .= ($raquo ? ' &raquo; ' : ''). $add;
    }
}

function template($name) {
    global $db, $comment_output;

    if (($template = templatecache(X_CACHE_GET, $name)) === false) {
        $query = $db->query("SELECT template FROM ".X_PREFIX."templates WHERE name='$name'");
        if ($db->num_rows($query) == 1) {
            if (X_SADMIN && DEBUG) {
                trigger_error('Efficiency Notice: The template `'.$name.'` was not preloaded.', E_USER_NOTICE);
            }
            $gettemplate = $db->fetch_array($query);
            templatecache(X_CACHE_PUT, $name, $gettemplate['template']);
            $template = $gettemplate['template'];
        } else {
            if (X_SADMIN && DEBUG) {
                trigger_error('Efficiency Warning: The template `'.$name.'` could not be found.', E_USER_WARNING);
            }
        }
        $db->free_result($query);
    }

    $template = str_replace("\\'","'", $template);

    if ($name != 'phpinclude' && $comment_output === true) {
        return "<!--Begin Template: $name -->\n$template\n<!-- End Template: $name -->";
    } else {
        return $template;
    }
}

function templatecache($type=X_CACHE_GET, $name, $data='') {
    static $cache;

    switch($type) {
        case X_CACHE_GET:
            if (!isset($cache[$name])) {
                return false;
            } else {
                return $cache[$name];
            }
            break;
        case X_CACHE_PUT:
            $cache[$name] = $data;
            return true;
            break;
    }
}

function loadtemplates() {
    global $db;

    $num = func_num_args();
    if ($num < 1) {
        echo 'Not enough arguments given to loadtemplates() on line: '.__LINE__;
        return false;
    } else {
        $namesarray = array_unique(array_merge(func_get_args(), array('header','css','error','message','footer','footer_querynum','footer_phpsql','footer_totaltime','footer_load')));
        $sql = "'".implode("', '", $namesarray)."'";
        $query = $db->query("SELECT name, template FROM ".X_PREFIX."templates WHERE name IN ($sql)");
        while($template = $db->fetch_array($query)) {
            templatecache(X_CACHE_PUT, $template['name'], $template['template']);
        }
        $db->free_result($query);
    }
}

function censor($txt) {
    global $censorcache;

    $ignorespaces = TRUE;
    if (is_array($censorcache)) {
        if (count($censorcache) > 0) {
            $prevfind = '';
            foreach($censorcache as $find=>$replace) {
                if ($ignorespaces === true) {
                    $txt = str_ireplace($find, $replace, $txt);
                } else {
                    if ($prevfind == '') {
                        $prevfind = $find;
                    }
                    $txt = preg_replace("#(^|[^a-z])(".preg_quote($find)."|".preg_quote($prevfind).")($|[^a-z])#si", '\1'.$replace.'\3', $txt);
                    $prevfind = $find;
                }
            }
            if ($ignorespaces !== true) {
                $txt = preg_replace("#(^|[^a-z])(".preg_quote($find).")($|[^a-z])#si", '\1'.$replace.'\3', $txt);
            }
        }
    }

    return $txt;
}

function smile($txt) {
    global $smiliesnum, $smiliecache, $smdir;

    if ($smiliesnum > 0) {
        reset($smiliecache);
        foreach($smiliecache as $code=>$url) {
            $txt = str_replace($code, '<img src="./'.$smdir.'/'.$url.'" style="border:none" alt="'.$code.'" />', $txt);
        }
    }

    return $txt;
}

function createAbsFSizeFromRel($rel) {
    global $fontsize;
    static $cachedFs;

    if (!is_array($cachedFs) || count($cachedFs) != 2) {
        preg_match('#([0-9]+)([a-z]+)?#i', $fontsize, $res);
        $cachedFs[0] = $res[1];
        $cachedFs[1] = $res[2];

        if (empty($cachedFs[1])) {
            $cachedFs[1] = 'px';
        }
    }

    $o = ($rel+$cachedFs[0]).$cachedFs[1];

    return $o;
}

function fixUrl($matches) {
    $fullurl = '';
    if (!empty($matches[2])) {
        if ($matches[3] != 'www') {
            $fullurl = $matches[2];
        } else {
            $fullurl = 'http://'.$matches[2];
        }
    }

    $fullurl = strip_tags($fullurl);

    return ' [url]'.$fullurl.'[/url]';
}

function postify($message, $smileyoff='no', $bbcodeoff='no', $allowsmilies='yes', $allowhtml='yes', $allowbbcode='yes', $allowimgcode='yes', $ignorespaces=false, $ismood="no", $wrap="yes") {

    $bballow = ($allowbbcode == 'yes' || $allowbbcode == 'on') ? (($bbcodeoff != 'off' && $bbcodeoff != 'yes') ? true : false) : false;
    $smiliesallow = ($allowsmilies == 'yes' || $allowsmilies == 'on') ? (($smileyoff != 'off' && $smileyoff != 'yes') ? true : false) : false;

    if ($bballow) {
        if ($ismood == 'yes') {
            $message = str_replace(array('[quote]', '[/quote]', '[code]', '[/code]', '[list]', '[/list]', '[list=1]', '[list=a]', '[list=A]', '[/list=1]', '[/list=a]', '[/list=A]', '[*]'), '_', $message);
        }

        $begin = array(
            0 => '[b]',
            1 => '[i]',
            2 => '[u]',
            3 => '[marquee]',
            4 => '[blink]',
            5 => '[strike]',
            6 => '[quote]',
            7 => '[code]',
            8 => '[list]',
            9 => '[list=1]',
            10 => '[list=a]',
            11 => '[list=A]',
        );

        $end = array(
            0 => '[/b]',
            1 => '[/i]',
            2 => '[/u]',
            3 => '[/marquee]',
            4 => '[/blink]',
            5 => '[/strike]',
            6 => '[/quote]',
            7 => '[/code]',
            8 => '[/list]',
            9 => '[/list=1]',
            10 => '[/list=a]',
            11 => '[/list=A]',
        );

        foreach($begin as $key=>$value) {
            $check = substr_count($message, $value) - substr_count($message, $end[$key]);
            if ($check > 0) {
                $message = $message.str_repeat($end[$key], $check);
            } else if ($check < 0) {
                $message = str_repeat($value, abs($check)).$message;
            }
        }

        $messagearray = preg_split("/\[code\]|\[\/code\]/", $message);
        for($i = 0; $i < sizeof($messagearray); $i++) {
            if (sizeof($messagearray) != 1) {
                if ($i == 0) {
                    $messagearray[$i] = rawHTMLmessage($messagearray[$i], $allowhtml)."[code]";
                    if ($smiliesallow) {
                        $messagearray[$i] = bbcode(smile($messagearray[$i]), $allowimgcode);
                    } else {
                        $messagearray[$i] = bbcode($messagearray[$i], $allowimgcode);
                    }
                } else if ($i == sizeof($messagearray) - 1) {
                    $messagearray[$i] = "[/code]".rawHTMLmessage($messagearray[$i], $allowhtml);
                    if ($smiliesallow) {
                        $messagearray[$i] = bbcode(smile($messagearray[$i]), $allowimgcode);
                    } else {
                        $messagearray[$i] = bbcode($messagearray[$i], $allowimgcode);
                    }
                } else if ($i % 2 == 0) {
                    $messagearray[$i] = "[/code]".rawHTMLmessage($messagearray[$i], $allowhtml)."[code]";
                    if ($smiliesallow) {
                        $messagearray[$i] = bbcode(smile($messagearray[$i]), $allowimgcode);
                    } else {
                        $messagearray[$i] = bbcode($messagearray[$i], $allowimgcode);
                    }
                } else { // Inside code block
                    $messagearray[$i] = censor($messagearray[$i]);
                }
            } else {
                $messagearray[0] = rawHTMLmessage($messagearray[0], $allowhtml);
                if ($smiliesallow) {
                    $messagearray[0] = bbcode(smile($messagearray[0]), $allowimgcode);
                } else {
                    $messagearray[0] = bbcode($messagearray[0], $allowimgcode);
                }
            }
        }
        $message = implode("", $messagearray);

        $message = nl2br($message);

        $messagearray = preg_split("#<!-- nobr -->|<!-- /nobr -->#", $message);
        for($i = 0; $i < sizeof($messagearray); $i++) {
            if ($i % 2 == 0) {
                $messagearray[$i] = wordwrap($messagearray[$i], 150, "\n", TRUE);
            } // else inside nobr block
        }
        $message = implode("", $messagearray);

    } else {
        $message = rawHTMLmessage($message, $allowhtml);
        if ($smiliesallow) {
            smile($message);
        }
        $message = nl2br($message);
        $message = wordwrap($message, 150, "\n", TRUE);
    }

    $message = preg_replace('#(script|about|applet|activex|chrome):#Sis',"\\1 &#058;",$message);

    return $message;
}

function bbcode($message, $allowimgcode) {
    global $lang;

    $find = array(
        0 => '[b]',
        1 => '[/b]',
        2 => '[i]',
        3 => '[/i]',
        4 => '[u]',
        5 => '[/u]',
        6 => '[marquee]',
        7 => '[/marquee]',
        8 => '[blink]',
        9 => '[/blink]',
        10 => '[strike]',
        11 => '[/strike]',
        12 => '[quote]',
        13 => '[/quote]',
        14 => '[code]',
        15 => '[/code]',
        16 => '[list]',
        17 => '[/list]',
        18 => '[list=1]',
        19 => '[list=a]',
        20 => '[list=A]',
        21 => '[/list=1]',
        22 => '[/list=a]',
        23 => '[/list=A]',
        24 => '[*]',
        25 => '<br />'
    );

    $replace = array(
        0 => '<strong>',
        1 => '</strong>',
        2 => '<em>',
        3 => '</em>',
        4 => '<u>',
        5 => '</u>',
        6 => '<marquee>',
        7 => '</marquee>',
        8 => '<blink>',
        9 => '</blink>',
        10 => '<strike>',
        11 => '</strike>',
        12 => '</font> <!-- nobr --><table align="center" class="quote" cellspacing="0" cellpadding="0"><tr><td class="quote">'.$lang['textquote'].'</td></tr><tr><td class="quotemessage"><!-- /nobr -->',
        13 => ' </td></tr></table><font class="mediumtxt">',
        14 => '</font> <!-- nobr --><table align="center" class="code" cellspacing="0" cellpadding="0"><tr><td class="code">'.$lang['textcode'].'</td></tr><tr><td class="codemessage"><!-- /nobr -->',
        15 => '</td></tr></table><font class="mediumtxt">',
        16 => '<ul type="square">',
        17 => '</ul>',
        18 => '<ol type="1">',
        19 => '<ol type="A">',
        20 => '<ol type="A">',
        21 => '</ol>',
        22 => '</ol>',
        23 => '</ol>',
        24 => '<li />',
        25 => '<br />'
    );

    $message = str_replace($find, $replace, $message);

    $patterns = array();
    $replacements = array();

    $patterns[] = "@\[color=(White|Black|Red|Yellow|Pink|Green|Orange|Purple|Blue|Beige|Brown|Teal|Navy|Maroon|LimeGreen|aqua|fuchsia|gray|silver|lime|olive)\](.*?)\[/color\]@Ssi";
    $replacements[] = '<span style="color: $1;">$2</span>';
    $patterns[] = "@\[color=#([\\da-f]{3,6})\](.*?)\[/color\]@Ssi";
    $replacements[] = '<span style="color: #$1;">$2</span>';
    $patterns[] = "@\[color=rgb\\(([\\s]*[\\d]{1,3}%?[\\s]*,[\\s]*[\\d]{1,3}%?[\\s]*,[\\s]*[\\d]{1,3}%?[\\s]*)\\)\](.*?)\[/color\]@Ssi";
    $replacements[] = '<span style="color: rgb($1);">$2</span>';
    $patterns[] = "#\[size=([+-]?[0-9]{1,2})\](.*?)\[/size\]#Ssie";
    $replacements[] = '"<span style=\"font-size: ".createAbsFSizeFromRel(\'$1\').";\">".stripslashes(\'$2\')."</span>"';
    $patterns[] = "#\[font=([a-z\r\n\t 0-9]+)\](.*?)\[/font\]#Ssi";
    $replacements[] = '<span style="font-family: $1;">$2</span>';
    $patterns[] = "#\[align=(left|center|right|justify)\](.+?)\[/align\]#Ssi";
    $replacements[] = '<div style="text-align: $1;">$2</div>';

    if ($allowimgcode != 'no' && $allowimgcode != 'off') {
        if (false == stripos($message, 'javascript:')) {
            $patterns[] = '#\[img\](http[s]?|ftp[s]?){1}://([:a-z\\./_\-0-9%~]+){1}\[/img\]#Smi';
            $replacements[] = '<img <!-- nobr -->src="\1://\2\3"<!-- /nobr --> border="0" alt="" />';
            $patterns[] = "#\[img=([0-9]*?){1}x([0-9]*?)\](http[s]?|ftp[s]?){1}://([:~a-z\\./0-9_\-%]+){1}(\?[a-z=0-9&_\-;~]*)?\[/img\]#Smi";
            $replacements[] = '<img width="\1" height="\2" <!-- nobr -->src="\3://\4\5"<!-- /nobr --> alt="" border="0" />';
        }
    }

    $message = preg_replace_callback('#(^|\s|\()((((http(s?)|ftp(s?))://)|www)[-a-z0-9.]+\.[a-z]{2,4}[^\s()]*)i?#Smi', 'fixUrl', $message);

    //[url]http://www.example.com/[/url]
    $patterns[] = "#\[url\]([a-z]+?://){1}([^\"'<>]{0,60}?)\[/url\]#Smi";  //Match only if length is <= 60 chars
    $replacements[] = '<a href="\1\2" onclick="window.open(this.href); return false;">\1\2</a>';
    $patterns[] = "#\[url\]([a-z]+?://){1}([^\"'<>\[\]]{61})([^\"'<>]*?)\[/url\]#Smi";  //Match only if length is >= 61 chars
    $replacements[] = ' <!-- nobr --><a href="\1\2\3" onclick="window.open(this.href); return false;">\1\2...</a><!-- /nobr --> ';

    //[url]www.example.com[/url]
    $patterns[] = "#\[url\]([^\[\"'<>]{0,60}?)\[/url\]#Smi";  //Match only if length is <= 60 chars
    $replacements[] = '<a href="http://\1" onclick="window.open(this.href); return false;">\1</a>';
    $patterns[] = "#\[url\]([^\"'<>\[\]]{61})([^\"'<>]*?)\[/url\]#Smi";  //Match only if length is >= 61 chars
    $replacements[] = ' <!-- nobr --><a href="http://\1\2" onclick="window.open(this.href); return false;">\1...</a><!-- /nobr --> ';

    //[url=http://www.example.com]Lorem Ipsum[/url]
    $patterns[] = "#\[url=([a-z]+?://){1}([^\"'<>\[\]]*?)\](.*?)\[/url\]#Smi";
    $replacements[] = '<a <!-- nobr -->href="\1\2"<!-- /nobr --> onclick="window.open(this.href); return false;">\3</a>';

    //[url=www.example.com]Lorem Ipsum[/url]
    $patterns[] = "#\[url=([^\[\"'<>]*?)\](.*?)\[/url\]#Smi";
    $replacements[] = '<a <!-- nobr -->href="http://\1"<!-- /nobr --> onclick="window.open(this.href); return false;">\2</a>';

    $patterns[] = "#\[email\]([^\"'<>]*?)\[/email\]#Smi";
    $replacements[] = '<a href="mailto:\1">\1</a>';
    $patterns[] = "#\[email=([^\"'<>]*?){1}([^\"]*?)\](.*?)\[/email\]#Smi";
    $replacements[] = '<a href="mailto:\1\2">\3</a>';

    return preg_replace($patterns, $replacements, $message);
}

function modcheck($username, $mods, $override=X_SMOD) {

    $retval = '';
    if ($override) {
        $retval = 'Moderator';
    } else if (X_MOD) {
        $username = strtoupper($username);
        $mods = explode(',', $mods);
        foreach($mods as $key=>$moderator) {
            if (strtoupper(trim($moderator)) == $username) {
                $retval = 'Moderator';
                break;
            }
        }
    }

    return $retval;
}

function modcheckPost($username, $mods, $origstatus) {
    global $SETTINGS;
    $retval = modcheck($username, $mods);

    if ($retval != '' And $SETTINGS['allowrankedit'] != 'off') {
        switch($origstatus) {
            case 'Super Administrator':
                if (!X_SADMIN) {
                    $retval = '';
                }
                break;
            case 'Administrator':
                if (!X_ADMIN) {
                    $retval = '';
                }
                break;
            case 'Super Moderator':
                if (!X_SMOD) {
                    $retval = '';
                }
                break;
            //If member does not have X_MOD then modcheck() returned a null string.  No reason to continue testing.
        }
    }

    return $retval;
}

function forum($forum, $template, $index_subforums) {
    global $timecode, $dateformat, $lang, $xmbuser, $self, $lastvisit2, $timeoffset, $hideprivate, $addtime, $oldtopics, $lastvisit;
    global $altbg1, $altbg2, $imgdir, $THEME, $SETTINGS;

    $forum['name'] = fnameOut($forum['name']);
    $forum['description'] = html_entity_decode($forum['description']);

    if (isset($forum['moderator']) && $forum['lastpost'] != '') {
        $lastpost = explode('|', $forum['lastpost']);
        $dalast = $lastpost[0];
        if ($lastpost[1] != 'Anonymous' && $lastpost[1] != '') {
            $lastpost[1] = '<a href="member.php?action=viewpro&amp;member='.recodeOut($lastpost[1]).'">'.$lastpost[1].'</a>';
        } else {
            $lastpost[1] = $lang['textanonymous'];
        }

        $lastPid = isset($lastpost[2]) ? $lastpost[2] : 0;

        $lastpostdate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600));
        $lastposttime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600));
        $lastpost = $lastpostdate.' '.$lang['textat'].' '.$lastposttime.'<br />'.$lang['textby'].' '.$lastpost[1];
        eval('$lastpostrow = "'.template($template.'_lastpost').'";');
    } else {
        $dalast = 0;
        $lastpost = $lang['textnever'];
        eval('$lastpostrow = "'.template($template.'_nolastpost').'";');
    }

    if ($lastvisit < $dalast && (strpos($oldtopics, '|'.$lastPid.'|') === false)) {
        $folder = '<img src="'.$THEME['imgdir'].'/red_folder.gif" alt="'.$lang['altredfolder'].'" border="0" />';
    } else {
        $folder = '<img src="'.$THEME['imgdir'].'/folder.gif" alt="'.$lang['altfolder'].'" border="0" />';
    }

    if ($dalast == '') {
        $folder = '<img src="'.$THEME['imgdir'].'/folder.gif" alt="'.$lang['altfolder'].'" border="0" />';
    }

    $foruminfo = '';
    $perms = checkForumPermissions($forum);
    if ($SETTINGS['hideprivate'] == 'off' || ($perms[X_PERMS_VIEW] || $perms[X_PERMS_USERLIST])) {
        if (isset($forum['moderator']) && $forum['moderator'] != '') {
            $moderators = explode(', ', $forum['moderator']);
            $forum['moderator'] = array();
            for($num = 0; $num < count($moderators); $num++) {
                $forum['moderator'][] = '<a href="member.php?action=viewpro&amp;member='.recodeOut($moderators[$num]).'">'.$moderators[$num].'</a>';
            }
            $forum['moderator'] = implode(', ', $forum['moderator']);
            $forum['moderator'] = '('.$lang['textmodby'].' '.$forum['moderator'].')';
        }

        $subforums = array();
        if (count($index_subforums) > 0) {
            for($i=0; $i < count($index_subforums); $i++) {
                $sub = $index_subforums[$i];
                $subperms = checkForumPermissions($sub);
                if ($sub['fup'] == $forum['fid']) {
                    if ($SETTINGS['hideprivate'] == 'off' || $subperms[X_PERMS_VIEW] || $subperms[X_PERMS_USERLIST]) {
                        $subforums[] = '<a href="forumdisplay.php?fid='.intval($sub['fid']).'">'.fnameOut($sub['name']).'</a>';
                    }
                }
            }
        }

        if (!empty($subforums)) {
            $subforums = implode(', ', $subforums);
            $subforums = '<br /><strong>'.$lang['textsubforums'].'</strong> '.$subforums;
        } else {
            $subforums = '';
        }
        eval('$foruminfo = "'.template($template).'";');
    }

    $dalast = '';

    return $foruminfo;
}

function multi($num, $perpage, $page, $mpurl, $strict = false) {
    $multipage = $GLOBALS['lang']['textpages'];

    $pages = quickpage($num, $perpage);

    if ($pages > 1) {
        if ($page == 0) {
            if ($pages < 4) {
                $to = $pages;
            } else {
                $to = 3;
            }
        } else if ($page == $pages) {
            $to = $pages;
        } else if ($page == $pages-1) {
            $to = $page+1;
        } else if ($page == $pages-2) {
            $to = $page+2;
        } else {
            $to = $page+3;
        }

        if ($page >= 0 && $page <= 3) {
            $from = 1;
        } else {
            $from = $page - 3;
        }

        $to--;
        $from++;

        $string = (strpos($mpurl, '?') !== false) ? '&amp;' : '?';
        if (1 != $page) {
            $multipage .= '&nbsp;&nbsp;<u><a href="'.$mpurl.'">1</a></u>';
            if (2 < $from) {
                $multipage .= '&nbsp;&nbsp;..';
            }
        } else {
            $multipage .= '&nbsp;&nbsp;<strong>1</strong>';
        }

        for($i = $from; $i <= $to; $i++) {
            if ($i != $page) {
                $multipage .= '&nbsp;&nbsp;<u><a href="'.$mpurl.$string.'page='.$i.'">'.$i.'</a></u>';
            } else {
                $multipage .= '&nbsp;&nbsp;<strong>'.$i.'</strong>';
            }
        }

        if ($pages != $page) {
            if (($pages - 1) > $to) {
                $multipage .= '&nbsp;&nbsp;..';
            }
            $multipage .= '&nbsp;&nbsp;<u><a href="'.$mpurl.$string.'page='.$pages.'">'.$pages.'</a></u>';
        } else {
            $multipage .= '&nbsp;&nbsp;<strong>'.$pages.'</strong>';
        }
    } else if ($strict !== true) {
        return false;
    }

    return $multipage;
}

function quickpage($things, $thingsperpage) {
    return ((($things > 0) && ($thingsperpage > 0) && ($things > $thingsperpage)) ? ceil($things / $thingsperpage) : 1);
}

function smilieinsert() {
    global $imgdir, $smdir, $db, $smileyinsert, $smcols, $smtotal;

    $sms = array();
    $smilienum = 0;
    $smilies = '';
    $smilieinsert = '';

    if ($smileyinsert == 'on' && $smcols != '') {
        if ($smtotal == 0) {
            $querysmilie = $db->query("SELECT * FROM ".X_PREFIX."smilies WHERE type='smiley' ORDER BY code DESC");
        } else {
            $querysmilie = $db->query("SELECT * FROM ".X_PREFIX."smilies WHERE type='smiley' ORDER BY code DESC LIMIT 0, ".$smtotal);
        }

        if (($smilienum = $db->num_rows($querysmilie)) > 0) {
            while($smilie = $db->fetch_array($querysmilie)) {
                eval('$sms[] = "'.template('functions_smilieinsert_smilie').'";');
            }
            $db->free_result($querysmilie);

            $smilies = '<tr>';
            for($i=0;$i<count($sms);$i++) {
                $smilies .= $sms[$i];
                if (($i+1)%$smcols == 0) {
                    $smilies .= '</tr>';
                    if (($i+1) < $smtotal) {
                        $smilies .= '<tr>';
                    }
                }
            }

            if ($smilienum%$smcols > 0) {
                $left = $smcols-($smilienum%$smcols);
                for($i=0;$i<$left;$i++) {
                    $smilies .= '<td>&nbsp;</td>';
                }
                $smilies .= '</tr>';
            }
            eval('$smilieinsert = "'.template('functions_smilieinsert').'";');
        } else {
            $smilieinsert = '';
        }
    }

    return $smilieinsert;
}

function updateforumcount($fid) {
    global $db;
    $fid = intval($fid);

    $query = $db->query("SELECT COUNT(pid) FROM ".X_PREFIX."forums AS f LEFT JOIN ".X_PREFIX."posts USING(fid) WHERE f.fid=$fid OR f.fup=$fid");
    $postcount = $db->result($query, 0);
    $db->free_result($query);

    $query = $db->query("SELECT COUNT(tid) FROM ".X_PREFIX."forums AS f LEFT JOIN ".X_PREFIX."threads USING(fid) WHERE f.fid=$fid OR f.fup=$fid");
    $threadcount = $db->result($query, 0);
    $db->free_result($query);

    $query = $db->query("SELECT t.lastpost FROM ".X_PREFIX."forums AS f LEFT JOIN ".X_PREFIX."threads AS t USING(fid) WHERE f.fid=$fid OR f.fup=$fid ORDER BY t.lastpost DESC LIMIT 0, 1");
    $lp = $db->fetch_array($query);
    $db->query("UPDATE ".X_PREFIX."forums SET posts='$postcount', threads='$threadcount', lastpost='$lp[lastpost]' WHERE fid='$fid'");
    $db->free_result($query);
}

function updatethreadcount($tid) {
    global $db;

    $query = $db->query("SELECT tid FROM ".X_PREFIX."posts WHERE tid='$tid'");
    $replycount = $db->num_rows($query);
    $db->free_result($query);
    $replycount--;
    $query = $db->query("SELECT dateline, author, pid FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY dateline DESC, pid DESC LIMIT 1");
    $lp = $db->fetch_array($query);
    $db->free_result($query);
    $lastpost = $lp['dateline'].'|'.$lp['author'].'|'.$lp['pid'];
    $db->query("UPDATE ".X_PREFIX."threads SET replies='$replycount', lastpost='$lastpost' WHERE tid='$tid'");
}

function smcwcache() {
    global $db, $smiliecache, $censorcache, $smiliesnum, $wordsnum;
    static $cached;

    if (!$cached) {
        $smiliecache = array();
        $censorcache = array();

        $query = $db->query("SELECT code, url FROM ".X_PREFIX."smilies WHERE type='smiley'");
        $smiliesnum = $db->num_rows($query);

        if ($smiliesnum > 0) {
            while($smilie = $db->fetch_array($query)) {
                $code = $smilie['code'];
                $smiliecache[$code] = $smilie['url'];
            }
        }
        $db->free_result($query);

        $query = $db->query("SELECT find, replace1 FROM ".X_PREFIX."words");
        $wordsnum = $db->num_rows($query);
        if ($wordsnum > 0) {
            while($word = $db->fetch_array($query)) {
                $find = $word['find'];
                $censorcache[$find] = $word['replace1'];
            }
        }
        $db->free_result($query);

        $cached = true;
        return true;
    }

    return false;
}

if (!function_exists('htmlspecialchars_decode')) {
    function htmlspecialchars_decode($string, $type=ENT_QUOTES) {
        $array = array_flip(get_html_translation_table(HTML_SPECIALCHARS, $type));
        return strtr($string, $array);
    }
}

function end_time() {
    global $footerstuff, $starttime, $SETTINGS;
    extract($GLOBALS);

    $mtime2 = explode(' ', microtime());
    $endtime = $mtime2[1] + $mtime2[0];

    $totaltime = ($endtime - $starttime);

    $footer_options = explode('-', $SETTINGS['footer_options']);

    if (X_ADMIN && in_array('serverload', $footer_options)) {
        $load = ServerLoad();
        if (!empty($load)) {
            eval("\$footerstuff['load'] = \"".template('footer_load')."\";");
        } else {
            $footerstuff['load'] = '';
        }
    } else {
        $footerstuff['load'] = '';
    }

    if (in_array('queries', $footer_options)) {
        $querynum = $db->querynum;
        eval("\$footerstuff['querynum'] = \"".template('footer_querynum')."\";");
    } else {
        $footerstuff['querynum'] = '';
    }

    if (in_array('phpsql', $footer_options)) {
        $db_duration = number_format(($db->duration/$totaltime)*100, 1);
        $php_duration = number_format((1-($db->duration/$totaltime))*100, 1);
        eval("\$footerstuff['phpsql'] = \"".template('footer_phpsql')."\";");
    } else {
        $footerstuff['phpsql'] = '';
    }

    if (in_array('loadtimes', $footer_options) && X_ADMIN) {
        $totaltime = number_format($totaltime, 7);
        eval("\$footerstuff['totaltime'] = \"".template('footer_totaltime')."\";");
    } else {
        $footerstuff['totaltime'] = '';
    }

    if (X_SADMIN && DEBUG) {
        $stuff = array();
        $stuff[] = '<table cols="2" style="width: 97%;"><tr><td style="width: 2em;">#</td><td style="width: 8em;">Duration:</td><td>Query:</td></tr>';
        foreach($db->querylist as $key=>$val) {
            $val = mysql_syn_highlight(cdataOut($val));
            $stuff[] = '<tr><td><strong>'.++$key.'.</strong></td><td>'.number_format($db->querytimes[$key-1], 8).'</td><td>'.$val.'</td></tr>';
        }
        $stuff[] = '</table>';
        $footerstuff['querydump'] = implode("\n", $stuff);
    } else {
        $footerstuff['querydump'] = '';
    }

    return $footerstuff;
}

function redirect($path, $timeout=2, $type=X_REDIRECT_HEADER) {
    if (strpos(urldecode($path), "\n") !== false || strpos(urldecode($path), "\r") !== false) {
        error('Tried to redirect to potentially insecure url.');
    }

    if (headers_sent() Or $type == X_REDIRECT_JS) {
        ?>
        <script language="javascript" type="text/javascript">
        function redirect() {
            window.location.replace("<?php echo $path?>");
        }
        setTimeout("redirect();", <?php echo ($timeout*1000)?>);
        </script>
        <?php
    } else {
        if ($timeout == 0) {
            header("Location: $path");
            exit;
        } else {
            header("Refresh: $timeout; URL=$path");
        }
    }

    return true;
}

function get_extension($filename) {
    $a = explode('.', $filename);
    $count = count($a);
    if ($count == 1) {
        return '';
    } else {
        return $a[$count-1];
    }
}

function ServerLoad() {
    if ($stats = @exec('uptime')) {
        $parts = explode(',', $stats);
        $count = count($parts);
        $first = explode(' ', $parts[$count-3]);
        $c = count($first);
        $first = $first[$c-1];
        return array($first, $parts[$count-2], $parts[$count-1]);
    } else {
        return array();
    }
}

function error($msg, $showheader=true, $prepend='', $append='', $redirect=false, $die=true, $return_as_string=false, $showfooter=true) {
    global $footerstuff, $lang, $navigation, $THEME;

    if (isset($GLOBALS)) {
        extract($GLOBALS);
    }

    $args = func_get_args();

    $message = (isset($args[0]) ? $args[0] : '');
    $showheader = (isset($args[1]) ? $args[1] : true);
    $prepend = (isset($args[2]) ? $args[2] : '');
    $append = (isset($args[3]) ? $args[3] : '');
    $redirect = (isset($args[4]) ? $args[4] : false);
    $die = (isset($args[5]) ? $args[5] : true);
    $return_str = (isset($args[6]) ? $args[6] : false);
    $showfooter = (isset($args[7]) ? $args[7] : true);

    $header = $footer = $return = '';

    if ($showheader) {
        nav($lang['error']);
    }

    end_time();

    if ($redirect !== false) {
        redirect($redirect, 3.0, X_REDIRECT_JS);
    }

    if ($showheader === false) {
        $header = '';
    } else {
        if (!isset($css) || strlen($css) ==0) {
            eval('$css = "'.template('css').'";');
        }
        eval('$header = "'.template('header').'";');
    }

    $error = '';
    eval('$error = "'.template('error').'";');

    if ($showfooter === true) {
        eval('$footer = "'.template('footer').'";');
    } else {
        $footer = '';
    }

    if ($return_str !== false) {
        $return = $prepend . $error . $append . $footer;
    } else {
        echo $prepend . $error . $append . $footer;
        $return = '';
    }

    if ($die) {
        exit();
    }

    return $return;
}

function message($msg, $showheader=true, $prepend='', $append='', $redirect=false, $die=true, $return_as_string=false, $showfooter=true) {
    global $footerstuff, $lang, $navigation, $THEME;

    if (isset($GLOBALS)) {
        extract($GLOBALS);
    }

    $args = func_get_args();

    $message = (isset($args[0]) ? $args[0] : '');
    $showheader = (isset($args[1]) ? $args[1] : true);
    $prepend = (isset($args[2]) ? $args[2] : '');
    $append = (isset($args[3]) ? $args[3] : '');
    $redirect = (isset($args[4]) ? $args[4] : false);
    $die = (isset($args[5]) ? $args[5] : true);
    $return_str = (isset($args[6]) ? $args[6] : false);
    $showfooter = (isset($args[7]) ? $args[7] : true);

    $header = $footer = $return = '';

    if ($showheader) {
        nav($lang['message']);
    }

    end_time();

    if ($redirect !== false) {
        redirect($redirect, 3.0, X_REDIRECT_JS);
    }

    if ($showheader === false) {
        $header = '';
    } else {
        if (!isset($css) || strlen($css) ==0) {
            eval('$css = "'.template('css').'";');
        }
        eval('$header = "'.template('header').'";');
    }

    $success = '';
    eval('$success = "'.template('message').'";');

    if ($showfooter === true) {
        eval('$footer = "'.template('footer').'";');
    } else {
        $footer = '';
    }

    if ($return_str !== false) {
        $return = $prepend . $success . $append . $footer;
    } else {
        echo $prepend . $success . $append . $footer;
        $return = '';
    }

    if ($die) {
        exit();
    }

    return $return;
}

function put_cookie($name, $value=null, $expire=0, $path=null, $domain=null, $secure=FALSE, $setVia=X_SET_HEADER) {
    if (!headers_sent() && $setVia != X_SET_JS) {
        return setcookie($name, $value, $expire, $path, $domain, $secure);
    } else {
        if ($expire > 0) {
            $expire = gmdate('r', $expire);
        } else {
            $expire = '';
        }
        ?>
        <script type="text/javascript">
            function put_cookie(name, value, expires, path, domain, secure) {
                var curCookie = name + "=" + escape(value) +
                ((expires) ? "; expires=" + expires : "") +
                ((path) ? "; path=" + path : "") +
                ((domain) ? "; domain=" + domain : "") +
                ((secure) ? "; secure" : "");
                document.cookie = curCookie;
            }
            put_cookie('<?php echo $name?>', '<?php echo $value?>', '<?php echo $expire?>', '<?php echo $path?>', '<?php echo $domain?>', '<?php echo $secure?>');
        </script>
        <?php
        return true;
    }
}

function audit($user='', $action, $fid, $tid, $reason='') {
    global $xmbuser, $db;

    if ($user == '') {
        $user = $xmbuser;
    }

    $fid = (int) $fid;
    $tid = (int) $tid;
    $action = cdataOut($action);
    $user = cdataOut($user);
    $reason = cdataOut($reason);

    $db->query("INSERT ".X_PREFIX."logs (tid, username, action, fid, date) VALUES ('$tid', '$user', '$action', '$fid', " . $db->time() . ")");
    return true;
}

function validatePpp() {
    global $ppp, $postperpage;

    if (!isset($ppp) || $ppp == '') {
        $ppp = $postperpage;
    } else {
        $ppp = is_numeric($ppp) ? (int) $ppp : $postperpage;
    }

    if ($ppp < 5) {
        $ppp = 30;
    }
}

function validateTpp() {
    global $tpp, $topicperpage;

    if (!isset($tpp) || $tpp == '') {
        $tpp = $topicperpage;
    } else {
        $tpp = is_numeric($tpp) ? (int) $tpp : $topicperpage;
    }

    if ($tpp < 5) {
        $tpp = 30;
    }
}

function altMail($to, $subject, $message, $additional_headers='', $additional_parameters=null) {
    global $mailer, $SETTINGS;
    static $handlers;

    $message = str_replace(array("\r\n", "\r", "\n"), array("\n", "\n", "\r\n"), $message);
    $subject = str_replace(array("\r", "\n"), array('', ''), $subject);

    if ($mailer['type'] == 'socket_SMTP') {
        require_once(ROOT.'include/smtp.inc.php');

        if (!isset($handlers['socket_SMTP'])) {
            if (DEBUG) {
                $mail = new socket_SMTP(true, './smtp-log.txt');
            } else {
                $mail = new socket_SMTP;
            }
            $handlers['socket_SMTP'] = &$mail;
            $mail->connect($mailer['host'], $mailer['port'], $mailer['username'], $mailer['password']);
            register_shutdown_function(array(&$mail, 'disconnect'));
        } else {
            $mail = &$handlers['socket_SMTP'];
        }

        $subjectInHeader = false;
        $toInHeader = false;
        $additional_headers = explode("\r\n", $additional_headers);
        foreach($additional_headers as $k=>$h) {
            if (strpos(trim($h), 'ubject:') === 1) {
                $additional_headers[$k] = 'Subject: '.$subject."\r\n";
                $subjectInHeader = true;
                continue;
            }

            if (strpos(trim(strtolower($h)), 'to:') === 0) {
                $toInHeader = true;
            }
        }

        if (!$subjectInHeader) {
            $additional_headers[] = 'Subject: '.$subject;
        }

        if (!$toInHeader) {
            $additional_headers[] = 'To: '.$to;
        }

        $additional_headers = implode("\r\n", $additional_headers);

        return $mail->sendMessage($SETTINGS['adminemail'], $to, $message, $additional_headers);
    } else {
        if (PHP_OS == 'WINNT' Or PHP_OS == 'WIN32') {  // Official XMB hack for PHP bug #45283 a.k.a. #28038
            ini_set('sendmail_from', ini_get('sendmail_from'));
        }
        if (ini_get('safe_mode') == "1") {
            return mail($to, $subject, $message, $additional_headers);
        } else {
            return mail($to, $subject, $message, $additional_headers, $additional_parameters);
        }
    }
}

function shortenString($string, $len=100, $shortType=X_SHORTEN_SOFT, $ps='...') {
    if (strlen($string) > $len) {
        if (($shortType & X_SHORTEN_SOFT) === X_SHORTEN_SOFT) {
            $string = preg_replace('#^(.{0,'.$len.'})([\W].*)#', '\1'.$ps, $string);
        }

        if ((strlen($string) > $len+strlen($ps)) && (($shortType & X_SHORTEN_HARD) === X_SHORTEN_HARD)) {
            $string = substr($string, 0, $len).$ps;
        }
        return $string;
    } else {
        return $string;
    }
}

function printGmDate($timestamp=null, $altFormat=null, $altOffset=0) {
    global $self, $SETTINGS, $timeoffset, $addtime;

    if ($timestamp === null) {
        $timestamp = time();
    }

    if ($altFormat === null) {
        $altFormat = $self['dateformat'];
    }

    $f = false;
    if ((($pos = strpos($altFormat, 'F')) !== false && $f = true) || ($pos2 = strpos($altFormat, 'M')) !== false) {
        $startStr = substr($altFormat, 0, $pos);
        $endStr = substr($altFormat, $pos+1);
        $month = gmdate('m', $timestamp + ($timeoffset*3600)+(($altOffset+$addtime)*3600));
        $textM = month2text($month);
        return printGmDate($timestamp, $startStr, $altOffset).substr($textM,0, ($f ? strlen($textM) : 3)).printGmDate($timestamp, $endStr, $altOffset);
    } else {
        return gmdate($altFormat, $timestamp + ($timeoffset * 3600) + (($altOffset+$addtime) * 3600));
    }
}

function printGmTime($timestamp=null, $altFormat=null, $altOffset=0) {
    global $self, $SETTINGS, $timeoffset, $addtime, $timecode;

    if ($timestamp === null) {
        $timestamp = time();
    }

    if ($altFormat !== null) {
        return gmdate($altFormat, $timestamp + ($timeoffset * 3600) + (($altOffset+$addtime) * 3600));
    } else {
        return gmdate($timecode, $timestamp + ($timeoffset * 3600) + (($altOffset+$addtime) * 3600));
    }
}

function MakeTime() {
   $objArgs = func_get_args();
   $nCount = count($objArgs);
   if ($nCount < 7) {
       $objDate = getdate();
       if ($nCount < 1) {
           $objArgs[] = $objDate['hours'];
       } else if ($nCount < 2) {
           $objArgs[] = $objDate['minutes'];
       } else if ($nCount < 3) {
           $objArgs[] = $objDate['seconds'];
       } else if ($nCount < 4) {
           $objArgs[] = $objDate['mon'];
       } else if ($nCount < 5) {
           $objArgs[] = $objDate['mday'];
       } else if ($nCount < 6) {
           $objArgs[] = $objDate['year'];
       } else if ($nCount < 7) {
           $objArgs[] = -1;
       }
   }

   $nYear = $objArgs[5];
   $nOffset = 0;
   if ($nYear < 1970) {
       if ($nYear < 1902) {
           return 0;
       } else if ($nYear < 1952) {
           $nOffset = -2650838400;
           $objArgs[5] += 84;
           if ($nYear < 1942) {
               $objArgs[6] = 0;
           }
       } else {
           $nOffset = -883612800;
           $objArgs[5] += 28;
       }
   }

   return call_user_func_array("mktime", $objArgs) + $nOffset;
}

function iso8601_date($year=0, $month=0, $day=0) {
    $year = (int) $year;
    $month = (int) $month;
    $day = (int) $day;

    if ($year < 1 || $month < 1 || $day < 1) {
        return '0000-00-00';
    }

    if ($year < 100) {
        $year += 1900;
    }

    if ($month > 12 || $month < 1) {
        $month = 1;
    }

    if ($day > 31 || $day < 1) {
        $day = 1;
    }

    return $year.'-'.str_pad($month, 2, 0, STR_PAD_LEFT).'-'.str_pad($day, 2, 0, STR_PAD_LEFT);
}

function month2text($num) {
    global $lang;

    $num = (int) $num;
    if ($num < 1 || $num > 12) {
        $num = 1;
    }

    $months = array(
        $lang['textjan'],
        $lang['textfeb'],
        $lang['textmar'],
        $lang['textapr'],
        $lang['textmay'],
        $lang['textjun'],
        $lang['textjul'],
        $lang['textaug'],
        $lang['textsep'],
        $lang['textoct'],
        $lang['textnov'],
        $lang['textdec']
    );

    return $months[$num-1];
}

// forumCache() returns a db query result containing all active forums and forum categories.
// Important: The return value is passed by reference.  There is only one query object.  This cannot be used in nested functions.
function forumCache() {
    global $db;
    static $cache = FALSE;

    if ($cache === FALSE) {
        $cache = $db->query("SELECT f.* FROM ".X_PREFIX."forums f WHERE f.status='on' ORDER BY f.displayorder ASC");
    }
    
    if ($cache !== FALSE) {
        if ($db->num_rows($cache) > 0) {
            $db->data_seek($cache, 0);  // Restores the pointer for fetch_array().
        }
    }

    return $cache;
}

// getForum() returns an associative array for the specified forum.
function getForum($fid) {
    global $db;
    
    $forums = forumCache();
    while($forum = $db->fetch_array($forums)) {
        if (intval($forum['fid']) == intval($fid)) {
            return $forum;
        }
    }
    return FALSE;
}

// getStructuredForums() returns a multi-dimensional array containing the following associative subscripts:
//  0:forums.type
//  1:forums.fup (always '0' for groups)
//  2:forums.fid
//  3:forums.*
// Usage example:
//  $forums = getStructuredForums();
//  echo fnameOut($forums['forum']['9']['14']['name']);
function getStructuredForums($usePerms=FALSE) {
    global $db;
    
    if ($usePerms) {
        $forums = permittedForums(forumCache(), 'forum');
    } else {
        $forums = array();
        $query = forumCache();
        while($forum = $db->fetch_array($query)) {
            $forums[] = $forum;
        }
    }
    
    // This function guarantees the following subscripts exist, regardless of forum count.
    $structured['group'] = array();
    $structured['forum'] = array();
    $structured['sub'] = array();
    $structured['group']['0'] = array();
    $structured['forum']['0'] = array();

    foreach($forums as $forum) {
        $structured[$forum['type']][$forum['fup']][$forum['fid']] = $forum;
    }
    
    return $structured;
}

// permittedForums() returns an array of permitted forum arrays
// $forums is a db query result, preferably from forumCache()
// $mode is a string designating whether to check for forum listing permissions or thread listing permissions
// $output if set to 'csv' causes the return value to be a CSV string of permitted forum IDs instead of an array of arrays.
// $check_parents is a bool indicating whether each forum's permissions depend on the parent forum also being permitted.
// $user_status is an optional masquerade value passed to checkForumPermissions()
function permittedForums($forums, $mode='thread', $output='array', $check_parents=TRUE, $user_status=FALSE) {
    global $db, $SETTINGS;
    
    $permitted = array();
    $fids['group'] = array();
    $fids['forum'] = array();
    $fids['sub'] = array();
    
    while($forum = $db->fetch_array($forums)) {
        $perms = checkForumPermissions($forum, $user_status);
        if ($mode == 'thread') {
            if ($forum['type'] == 'group' || ($perms[X_PERMS_VIEW] && $perms[X_PERMS_PASSWORD])) {
                $permitted[] = $forum;
                $fids[$forum['type']][] = $forum['fid'];
            }
        } elseif ($mode == 'forum') {
            if ($SETTINGS['hideprivate'] == 'off' || $forum['type'] == 'group' || $perms[X_PERMS_VIEW]) {
                $permitted[] = $forum;
                $fids[$forum['type']][] = $forum['fid'];
            }
        }
    }

    if ($check_parents) { // Use the $fids array to see if each forum's parent is permitted.
        $filtered = array();
        $fids['forum'] = array();
        $fids['sub'] = array();
        foreach($permitted as $forum) {
            if ($forum['type'] == 'group') {
                $filtered[] = $forum;
            } elseif ($forum['type'] == 'forum') {
                if (intval($forum['fup']) == 0) {
                    $filtered[] = $forum;
                    $fids['forum'][] = $forum['fid'];
                } elseif (array_search($forum['fup'], $fids['group']) !== FALSE) {
                    $filtered[] = $forum;
                    $fids['forum'][] = $forum['fid'];
                }
            }
        }

        foreach($permitted as $forum) {
            if ($forum['type'] == 'sub') {
                if (intval($forum['fup']) == 0) {
                    $filtered[] = $forum;
                    $fids['sub'][] = $forum['fid'];
                } elseif (array_search($forum['fup'], $fids['forum']) !== FALSE) {
                    $filtered[] = $forum;
                    $fids['sub'][] = $forum['fid'];
                }
            }
        }
        
        $permitted = $filtered;
    }
    
    if ($output == 'csv') {
        $permitted = implode(', ', array_merge($fids['group'], $fids['forum'], $fids['sub']));
    }
    
    return $permitted;
}

function forumList($selectname='srchfid', $multiple=false, $allowall=true, $currentfid=0) {
    global $lang;

    // Initialize $forumselect
    $forumselect = array();
    if (!$multiple) {
        $forumselect[] = '<select name="'.$selectname.'">';
    } else {
        $forumselect[] = '<select name="'.$selectname.'" multiple="multiple">';
    }

    if ($allowall) {
        $forumselect[] = '<option value="all" selected="selected">'.$lang['textallforumsandsubs'].'</option>';
    } else if (!$allowall && !$multiple) {
        $forumselect[] = '<option value="" disabled="disabled" selected="selected">'.$lang['textforum'].'</option>';
    }

    // Populate $forumselect
    $permitted = getStructuredForums(TRUE);

    foreach($permitted['forum']['0'] as $forum) {
        $forumselect[] = '<option value="'.intval($forum['fid']).'"'.($forum['fid'] == $currentfid ? ' selected="selected"' : '').'> &nbsp; &raquo; '.fnameOut($forum['name']).'</option>';
        if (isset($permitted['sub'][$forum['fid']])) {
            foreach($permitted['sub'][$forum['fid']] as $sub) {
                $forumselect[] = '<option value="'.intval($sub['fid']).'"'.($sub['fid'] == $currentfid ? ' selected="selected"' : '').'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.fnameOut($sub['name']).'</option>';
            }
        }
    }

    $forumselect[] = '<option value="0" disabled="disabled">&nbsp;</option>';
    foreach($permitted['group']['0'] as $group) {
        if (isset($permitted['forum'][$group['fid']]) && count($permitted['forum'][$group['fid']]) > 0) {
            $forumselect[] = '<option value="'.intval($group['fid']).'" disabled="disabled">'.fnameOut($group['name']).'</option>';
            foreach($permitted['forum'][$group['fid']] as $forum) {
                $forumselect[] = '<option value="'.intval($forum['fid']).'"'.($forum['fid'] == $currentfid ? ' selected="selected"' : '').'> &nbsp; &raquo; '.fnameOut($forum['name']).'</option>';
                if (isset($permitted['sub'][$forum['fid']])) {
                    foreach($permitted['sub'][$forum['fid']] as $sub) {
                        $forumselect[] = '<option value="'.intval($sub['fid']).'"'.($sub['fid'] == $currentfid ? ' selected="selected"' : '').'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.fnameOut($sub['name']).'</option>';
                    }
                }
            }
        }
        $forumselect[] = '<option value="" disabled="disabled">&nbsp;</option>';
    }
    $forumselect[] = '</select>';
    return implode("\n", $forumselect);
}

function forumJump() {
    global $lang;

    // Initialize $forumselect
    $forumselect = array();

    $forumselect[] = "<select onchange=\"if (this.options[this.selectedIndex].value) {window.location=(''+this.options[this.selectedIndex].value)}\">";
    $forumselect[] = '<option value="0" selected="selected">'.$lang['forumjumpselect'].'</option>';

    // Populate $forumselect
    $permitted = getStructuredForums(TRUE);

    foreach($permitted['forum']['0'] as $forum) {
        $forumselect[] = '<option value="'.ROOT.'forumdisplay.php?fid='.intval($forum['fid']).'"> &nbsp; &raquo; '.fnameOut($forum['name']).'</option>';
        if (isset($permitted['sub'][$forum['fid']])) {
            foreach($permitted['sub'][$forum['fid']] as $sub) {
                $forumselect[] = '<option value="'.ROOT.'forumdisplay.php?fid='.intval($sub['fid']).'">&nbsp; &nbsp; &raquo; '.fnameOut($sub['name']).'</option>';
            }
        }
    }

    foreach($permitted['group']['0'] as $group) {
        if (isset($permitted['forum'][$group['fid']])) {
            $forumselect[] = '<option value="0"></option>';
            $forumselect[] = '<option value="'.ROOT.'index.php?gid='.intval($group['fid']).'">'.fnameOut($group['name']).'</option>';
            foreach($permitted['forum'][$group['fid']] as $forum) {
                $forumselect[] = '<option value="'.ROOT.'forumdisplay.php?fid='.intval($forum['fid']).'"> &nbsp; &raquo; '.fnameOut($forum['name']).'</option>';
                if (isset($permitted['sub'][$forum['fid']])) {
                    foreach($permitted['sub'][$forum['fid']] as $sub) {
                        $forumselect[] = '<option value="'.ROOT.'forumdisplay.php?fid='.intval($sub['fid']).'">&nbsp; &nbsp; &raquo; '.fnameOut($sub['name']).'</option>';
                    }
                }
            }
        }
    }
    $forumselect[] = '</select>';
    return implode("\n", $forumselect);
}

// checkForumPermissions - Returns a set of boolean permissions for a specific forum.
// Normal Usage Example
//  $fid = 1;
//  $forum = getForum($fid);
//  $perms = checkForumPermissions($forum);
//  if ($perms[X_PERMS_VIEW]) { //$self is allowed to view $forum }
// Masquerade Example
//  $result = $db->query('SELECT * FROM '.X_PREFIX.'members WHERE uid=1');
//  $user = $db->fetch_array($result);
//  $perms = checkForumPermissions($forum, $user['status']);
//  if ($perms[X_PERMS_VIEW]) { //$user is allowed to view $forum }
// Masquerade Example 2
//  $perms = checkForumPermissions($forum, 'Moderator');
//  if ($perms[X_PERMS_VIEW]) { //Moderators are allowed to view $forum }
function checkForumPermissions($forum, $user_status_in=FALSE) {
    global $self, $status_enum;

    if (is_string($user_status_in)) {
        $user_status = $status_enum[$user_status_in];
    } else {
        $user_status = $status_enum[$self['status']];
    }

    // 1. Initialize $ret with zero permissions
    $ret = array_fill(0, X_PERMS_COUNT, FALSE);
    $ret[X_PERMS_POLL] = FALSE;
    $ret[X_PERMS_THREAD] = FALSE;
    $ret[X_PERMS_REPLY] = FALSE;
    $ret[X_PERMS_VIEW] = FALSE;
    $ret[X_PERMS_USERLIST] = FALSE;
    $ret[X_PERMS_PASSWORD] = FALSE;
    
    // 2. Check Forum Postperm
    $pp = explode(',', $forum['postperm']);
    foreach($pp as $key=>$val) {
        if ((intval($val) & $user_status) != 0) {
            $ret[$key] = TRUE;
        }
    }

    // 3. Check Forum Userlist
    if ($user_status_in === FALSE) {
        $userlist = $forum['userlist'];

        if (modcheck($self['username'], $forum['moderator'], FALSE) == "Moderator") {
            $ret[X_PERMS_USERLIST] = TRUE;
            $ret[X_PERMS_VIEW] = TRUE;
        } elseif (!X_GUEST) {
            $users = explode(',', $userlist);
            foreach($users as $user) {
                if (strtolower(trim($user)) == strtolower($self['username'])) {
                    $ret[X_PERMS_USERLIST] = TRUE;
                    $ret[X_PERMS_VIEW] = TRUE;
                    break;
                }
            }
        }
    }
    
    // 4. Set Effective Permissions
    $ret[X_PERMS_POLL]   = $ret[X_PERMS_RAWPOLL];
    $ret[X_PERMS_THREAD] = $ret[X_PERMS_RAWTHREAD];
    $ret[X_PERMS_REPLY]  = $ret[X_PERMS_RAWREPLY];
    $ret[X_PERMS_VIEW]   = $ret[X_PERMS_RAWVIEW] || $ret[X_PERMS_USERLIST];

    // 5. Check Forum Password
    $pwinput = postedVar('fidpw'.$forum['fid'], '', FALSE, FALSE, FALSE, 'c');
    if ($forum['password'] == '' Or $pwinput == $forum['password']) {
        $ret[X_PERMS_PASSWORD] = TRUE;
    }

    return $ret;
}

// getOneForumPerm - Enables you to do complex comparisons without string parsing.  Valid with X_PERMS_RAW* indexes only!
// Normal Usage Example
//  $fid = 1;
//  $forum = getForum($fid);
//  $viewperms = getOneForumPerm($forum, X_PERMS_RAWVIEW);
//  if ($viewperms >= $status_enum['Member']) { //Some non-staff status has perms to view $forum }
//  if ($viewperms == $status_enum['Guest']) { //$forum is guest-only }
//  if ($viewperms == $status_enum['Member'] - 1) { //$forum is staff-only }
function getOneForumPerm($forum, $bitfield) {
    $pp = explode(',', $forum['postperm']);
    return $pp[$bitfield];
}

function handlePasswordDialog($fid) {
    global $db, $full_url, $url, $cookiepath, $cookiedomain;  // function vars
    global $THEME, $lang, $oToken, $altbg1, $altbg2, $tablewidth, $tablespace, $bordercolor;  // template vars

    $pwform = '';
    $pwinput = postedVar('pw', '', FALSE, FALSE);
    $query = $db->query("SELECT password FROM ".X_PREFIX."forums WHERE fid=$fid");
    if ($pwinput != '' And $db->num_rows($query) == 1) {
        $pass = $db->result($query, 0);

        if ($pwinput == $pass) {
            put_cookie('fidpw'.$fid, $pass, (time() + (86400*30)), $cookiepath, $cookiedomain);
            redirect($full_url.substr($url, strlen($cookiepath)), 0);
        } else {
            eval('$pwform = "'.template('forumdisplay_password').'";');
            error($lang['invalidforumpw'], true, '', $pwform, false, true, false, true);
        }
    } else {
        eval('$pwform = "'.template('forumdisplay_password').'";');
        error($lang['forumpwinfo'], true, '', $pwform, false, true, false, true);
    }
}

function createLangFileSelect($currentLangFile) {
    global $db;

    $lfs = array();

    $query = $db->query("SELECT b.devname, t.cdata "
                      . "FROM ".X_PREFIX."lang_base AS b "
                      . "LEFT JOIN ".X_PREFIX."lang_text AS t USING (langid) "
                      . "INNER JOIN ".X_PREFIX."lang_keys AS k USING (phraseid) "
                      . "WHERE k.langkey='language' "
                      . "ORDER BY t.cdata ASC");
    while ($row = $db->fetch_array($query)) {
        if ($row['devname'] == $currentLangFile) {
            $lfs[] = '<option value="'.$row['devname'].'" selected="selected">'.$row['cdata'].'</option>';
        } else {
            $lfs[] = '<option value="'.$row['devname'].'">'.$row['cdata'].'</option>';
        }
    }
    return '<select name="langfilenew">'.implode("\n", $lfs).'</select>';
}
?>
