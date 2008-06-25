<?php
/**
 * eXtreme Message Board
 * XMB 1.9.10 Karl
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
        $db->query("DELETE FROM ".X_PREFIX."whosonline WHERE ip='$onlineip' && username='xguest123'");

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
    global $xmbuser, $xmbpw, $self, $lang, $db, $charset, $SETTINGS, $onlineip;

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

    //Database routine complete.  Now set the role constants.

    if ($xmbuser == '') {
        $self = array();
        $role['sadmin'] = false;
        $role['admin']  = false;
        $role['smod']   = false;
        $role['mod']    = false;
        $role['staff']  = false;
        if (!defined('X_GUEST')) {
            define('X_MEMBER', false);
            define('X_GUEST', true);
        }
    } else {
        if (!defined('X_GUEST')) {
            define('X_MEMBER', true);
            define('X_GUEST', false);
        }

        switch($self['status']) {
            case 'Super Administrator':
                $role['sadmin'] = true;
                $role['admin']  = true;
                $role['smod']   = true;
                $role['mod']    = true;
                $role['staff']  = true;
                break;
            case 'Administrator':
                $role['sadmin'] = false;
                $role['admin']  = true;
                $role['smod']   = true;
                $role['mod']    = true;
                $role['staff']  = true;
                break;
            case 'Super Moderator':
                $role['sadmin'] = false;
                $role['admin']  = false;
                $role['smod']   = true;
                $role['mod']    = true;
                $role['staff']  = true;
                break;
            case 'Moderator':
                $role['sadmin'] = false;
                $role['admin']  = false;
                $role['smod']   = false;
                $role['mod']    = true;
                $role['staff']  = true;
                break;
            default:
                $role['sadmin'] = false;
                $role['admin']  = false;
                $role['smod']   = false;
                $role['mod']    = false;
                $role['staff']  = false;
                break;
        }
    }

    if (!defined('X_STAFF')) {
        define('X_SADMIN', $role['sadmin']);
        define('X_ADMIN', $role['admin']);
        define('X_SMOD', $role['smod']);
        define('X_MOD', $role['mod']);
        define('X_STAFF', $role['staff']);
    }

    return ($xmbuser != '');
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

    $shorturl = '';
    if (strlen($fullurl) > 80) {
        $shorturl = substr($fullurl, 0, 80);
        $shorturl = substr_replace($shorturl, '...', 77, 3);
        return ' [url='.$fullurl.']'.$shorturl.'[/url]';
    } else {
        return ' <a href="'.$fullurl.'" target="_blank">'.$fullurl.'</a>';
    }
}

function postify($message, $smileyoff='no', $bbcodeoff='no', $allowsmilies='yes', $allowhtml='yes', $allowbbcode='yes', $allowimgcode='yes', $ignorespaces=false, $ismood="no", $wrap="yes") {
    global $imgdir, $bordercolor, $db, $smdir, $smiliecache, $censorcache, $smiliesnum, $wordsnum, $versionbuild, $fontsize;

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
    } else {
        $message = rawHTMLmessage($message, $allowhtml);
        if ($smiliesallow) {
            smile($message);
        }
    }

    $message = nl2br($message);
    if ($wrap == "yes") {
        $message = wordwrap($message, 150, "\n", 1);
        $message = preg_replace('#(\[/?.*)\n(.*\])#mi', '\\1\\2', $message);
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
        12 => '</font><table align="center" class="quote" cellspacing="0" cellpadding="0"><tr><td class="quote">'.$lang['textquote'].'</td></tr><tr><td class="quotemessage">',
        13 => ' </td></tr></table><font class="mediumtxt">',
        14 => '</font><table align="center" class="code" cellspacing="0" cellpadding="0"><tr><td class="code">'.$lang['textcode'].'</td></tr>'."\n".'<tr><td class="codemessage">',
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

    $patterns[] = "#\[color=([^\"'<>]*?)\](.*?)\[/color\]#Ssi";
    $replacements[] = '<span style="color: $1;">$2</span>';
    $patterns[] = "#\[size=([+-]?[0-9]{1,2})\](.*?)\[/size\]#Ssie";
    $replacements[] = '"<span style=\"font-size: ".createAbsFSizeFromRel(\'$1\').";\">".stripslashes(\'$2\')."</span>"';
    $patterns[] = "#\[font=([a-z\r\n\t 0-9]+)\](.*?)\[/font\]#Ssi";
    $replacements[] = '<span style="font-family: $1;">$2</span>';
    $patterns[] = "#\[align=(left|center|right|justify)\](.+?)\[/align\]#Ssi";
    $replacements[] = '<div style="text-align: $1;">$2</div>';

    if ($allowimgcode != 'no' && $allowimgcode != 'off') {
        if (false == stripos($message, 'javascript:')) {
            $patterns[] = '#\[img\](http[s]?|ftp[s]?){1}://([:a-z\\./_\-0-9%~]+){1}\[/img\]#Smi';
            $replacements[] = '<img src="\1://\2\3" border="0" alt="\1://\2\3"/>';
            $patterns[] = "#\[img=([0-9]*?){1}x([0-9]*?)\](http[s]?|ftp[s]?){1}://([:~a-z\\./0-9_\-%]+){1}(\?[a-z=0-9&_\-;~]*)?\[/img\]#Smi";
            $replacements[] = '<img width="\1" height="\2" src="\3://\4\5" alt="\3://\4\5" border="0" />';
        }
    }

    $message = preg_replace_callback('#(^|\s|\()((((http(s?)|ftp(s?))://)|www)[-a-z0-9.]+\.[a-z]{2,4}[^\s()]*)i?#Smi', 'fixUrl', $message);

    $patterns[] = "#\[url\]([a-z]+?://){1}([^\"'<>]*?)\[/url\]#Smi";
    $replacements[] = '<a href="\1\2" target="_blank">\1\2</a>';
    $patterns[] = "#\[url\]([^\[\"'<>]*?)\[/url\]#Smi";
    $replacements[] = '<a href="http://\1" target="_blank">\1</a>';
    $patterns[] = "#\[url=([a-z]+?://){1}([^\"'<>\[\]]*?)\](.*?)\[/url\]#Smi";
    $replacements[] = '<a href="\1\2" target="_blank">\3</a>';
    $patterns[] = "#\[url=([^\[\"'<>]*?)\](.*?)\[/url\]#Smi";
    $replacements[] = '<a href="http://\1" target="_blank">\2</a>';
    $patterns[] = "#\[email\]([^\"'<>]*?)\[/email\]#Smi";
    $replacements[] = '<a href="mailto:\1">\1</a>';
    $patterns[] = "#\[email=([^\"'<>]*?){1}([^\"]*?)\](.*?)\[/email\]#Smi";
    $replacements[] = '<a href="mailto:\1\2">\3</a>';

    return preg_replace($patterns, $replacements, $message);
}

function modcheck($username, $mods) {

    $retval = '';
    if (X_SMOD) {
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

function forum($forum, $template) {
    global $timecode, $dateformat, $lang, $xmbuser, $self, $lastvisit2, $timeoffset, $hideprivate, $addtime, $oldtopics, $lastvisit;
    global $altbg1, $altbg2, $imgdir, $THEME, $SETTINGS, $index_subforums;

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
    if (X_SADMIN || $SETTINGS['hideprivate'] == 'off' || ($perms[X_PERMS_VIEW] && $perms[X_PERMS_USERLIST])) {
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
                    if (X_SADMIN || $SETTINGS['hideprivate'] == 'off' || $subperms[X_PERMS_VIEW] || $subperms[X_PERMS_USERLIST]) {
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
            $multipage .= '&nbsp;&nbsp;<u><a href="'.$mpurl.$string.'page=1">1</a></u>';
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
    $query = $db->query("SELECT dateline, author, pid FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY dateline DESC LIMIT 1");
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

/* checkInput() is deprecated */
function checkInput($input, $striptags='no', $allowhtml='no', $word='', $no_quotes=true) {
    $input = trim($input);
    if ($striptags == 'yes') {
        $input = strip_tags($input);
    }

    if ($allowhtml != 'yes' && $allowhtml != 'on') {
        if ($no_quotes) {
            $input = htmlspecialchars($input, ENT_NOQUOTES);
        } else {
            $input = htmlspecialchars($input, ENT_QUOTES);
        }
    }

    if ($word != '') {
        $input = str_ireplace($word, "_".$word, $input);
    }

    return $input;
}

if (!function_exists('htmlspecialchars_decode')) {
    function htmlspecialchars_decode($string, $type=ENT_QUOTES) {
        $array = array_flip(get_html_translation_table(HTML_SPECIALCHARS, $type));
        return strtr($string, $array);
    }
}

if (!function_exists('htmlentities_decode')) {
    function htmlentities_decode($string, $type=ENT_QUOTES) {
        $array = array_flip(get_html_translation_table(HTML_ENTITIES, $type));
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
            $val = mysql_syn_highlight($val);
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

function get_attached_file($file, $attachstatus, $max_size=1000000) {
    global $db, $lang, $filename, $filetype, $filesize;

    $filename = '';
    $filetype = '';
    $filesize = 0;

    if ($file['name'] != 'none' && !empty($file['name']) && $attachstatus != 'off' && is_uploaded_file($file['tmp_name'])) {
        $file['name'] = trim($file['name']);
        if (!isValidFilename($file['name'])) {
            error($lang['invalidFilename'], false, '', '', false, false, false, false);
            return false;
        }

        $filesize = intval(filesize($file['tmp_name'])); // fix bad filesizes
        if ($file['size'] > $max_size) {
            error($lang['attachtoobig'], false, '', '', false, false, false, false);
            return false;
        } else {
            $attachment = $db->escape(fread(fopen($file['tmp_name'], 'rb'), filesize($file['tmp_name'])));
            $filename = $db->escape($file['name']);
            $filetype = $db->escape(preg_replace('#[\r\n%]#', '', $file['type']));

            if ($filesize == 0) {
                return false;
            } else {
                return $attachment;
            }
        }
    } else {
        return false;
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
    global $footerstuff, $lang, $navigation;

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

    nav($lang['error']);

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
    global $footerstuff, $lang, $navigation;

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

    nav($lang['message']);

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

function array_keys2keys($array, $translator) {
    $new_array = array();

    foreach($array as $key=>$val) {
        if (isset($translator[$key])) {
            $new_key = $translator[$key];
        } else {
            $new_key = $key;
        }
        $new_array[$new_key] = $val;
    }

    return $new_array;
}

function mysql_syn_highlight($query) {
    global $tables, $tablepre;

    $find = array();
    $replace = array();

    foreach($tables as $name) {
        $find[] = $tablepre.$name;
    }

    $find[] = 'SELECT';
    $find[] = 'UPDATE';
    $find[] = 'DELETE';
    $find[] = 'INSERT INTO ';
    $find[] = ' WHERE ';
    $find[] = ' ON ';
    $find[] = ' FROM ';
    $find[] = ' GROUP BY ';
    $find[] = 'ORDER BY ';
    $find[] = ' LEFT JOIN ';
    $find[] = ' IN ';
    $find[] = ' SET ';
    $find[] = ' AS ';
    $find[] = '(';
    $find[] = ')';
    $find[] = ' ASC';
    $find[] = ' DESC';
    $find[] = ' AND ';
    $find[] = ' OR ';
    $find[] = ' NOT';

    foreach($find as $key=>$val) {
        $replace[$key] = '</em><strong>'.$val.'</strong><em>';
    }

    return '<em>'.str_replace($find, $replace, $query).'</em>';
}

function dump_query($resource, $header=true) {
    global $altbg2, $altbg1, $db, $cattext;
    if (!$db->error()) {
        $count = $db->num_fields($resource);
        if ($header) {
            ?>
            <tr class="category" bgcolor="<?php echo $altbg2?>" align="center">
            <?php
            for($i=0;$i<$count;$i++) {
                echo '<td align="left">';
                echo '<strong><font color='.$cattext.'>'.$db->field_name($resource, $i).'</font></strong>';
                echo '</td>';
            }
            echo '</tr>';
        }

        while($a = $db->fetch_array($resource, SQL_NUM)) {
            ?>
            <tr bgcolor="<?php echo $altbg1?>" class="ctrtablerow">
            <?php
            for($i=0;$i<$count;$i++) {
                echo '<td align="left">';

                if (trim($a[$i]) == '') {
                    echo '&nbsp;';
                } else {
                    echo nl2br($a[$i]);
                }
                echo '</td>';
            }
            echo '</tr>';
        }
    } else {
        error($db->error());
    }
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
    $action = checkInput($action);
    $user = checkInput($user);
    $reason = checkInput($reason);

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
    static $isInc, $handlers;

    $message = str_replace(array("\r\n", "\r", "\n"), array("\n", "\n", "\r\n"), $message);
    $subject = str_replace(array("\r", "\n"), array('', ''), $subject);

    switch($mailer['type']) {
        case 'socket_SMTP':
            if (!isset($isInc['socket_SMTP'])) {
                require ROOT.'include/smtp.inc.php';
                $isInc['socket_SMTP'] = true;
            }

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
            break;
        default:
            if (PHP_OS == 'WINNT' Or PHP_OS == 'WIN32') {  // Official XMB hack for PHP bug #45283 a.k.a. #28038
                ini_set('sendmail_from', ini_get('sendmail_from'));
            }
            if (ini_get('safe_mode') == "1") {
                return mail($to, $subject, $message, $additional_headers);
            } else {
                return mail($to, $subject, $message, $additional_headers, $additional_parameters);
            }
            break;
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

function forumList($selectname='srchfid', $multiple=false, $allowall=true, $currentfid=0) {
    global $db, $self, $lang, $SETTINGS;

    $query = $db->query("SELECT f.* FROM ".X_PREFIX."forums f WHERE f.status='on' ORDER BY f.displayorder ASC");

    $standAloneForums = array();
    $forums = array();
    $categories = array();
    $subforums = array();
    while($forum = $db->fetch_array($query)) {
        $perms = checkForumPermissions($forum);
        if (X_SADMIN || $SETTINGS['hideprivate'] == 'off' || $forum['type'] == 'group' || ($perms[X_PERMS_VIEW] && $perms[X_PERMS_USERLIST])) {
            $forum['name'] = fnameOut($forum['name']);
            if (!X_SADMIN && $forum['password'] != '') {
                $fidpw = postedVar('fidpw'.$forum['fid'], '', FALSE, FALSE, FALSE, 'c');
                if ($forum['password'] !== $fidpw) {
                    continue;
                }
            }

            switch($forum['type']) {
                case 'group':
                    $categories[] = $forum;
                    break;
                case 'sub':
                    if (!isset($subforums[$forum['fup']])) {
                        $subforums[$forum['fup']] = array();
                    }
                    $subforums[$forum['fup']][] = $forum;
                    break;
                case 'forum':
                default:
                    if ($forum['fup'] == 0) {
                        $standAloneForums[] = $forum;
                    } else {
                        if (!isset($forums[$forum['fup']])) {
                            $forums[$forum['fup']] = array();
                        }
                        $forums[$forum['fup']][] = $forum;
                    }
                    break;
            }
        }
    }
    $db->free_result($query);

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

    unset($forum);
    reset($forums);

    foreach($standAloneForums as $forum) {
        $forumselect[] = '<option value="'.intval($forum['fid']).'"'.($forum['fid'] == $currentfid ? ' selected="selected"' : '').'> &nbsp; &raquo; '.$forum['name'].'</option>';
        if (isset($subforums[$forum['fid']])) {
            foreach($subforums[$forum['fid']] as $sub) {
                $forumselect[] = '<option value="'.intval($sub['fid']).'"'.($sub['fid'] == $currentfid ? ' selected="selected"' : '').'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
            }
        }
    }

    $forumselect[] = '<option value="0" disabled="disabled">&nbsp;</option>';
    foreach($categories as $group) {
        if (isset($forums[$group['fid']]) && count($forums[$group['fid']]) > 0) {
            $forumselect[] = '<option value="'.intval($group['fid']).'" disabled="disabled">'.$group['name'].'</option>';
            foreach($forums[$group['fid']] as $forum) {
                $forumselect[] = '<option value="'.intval($forum['fid']).'"'.($forum['fid'] == $currentfid ? ' selected="selected"' : '').'> &nbsp; &raquo; '.$forum['name'].'</option>';
                if (isset($subforums[$forum['fid']])) {
                    foreach($subforums[$forum['fid']] as $sub) {
                        $forumselect[] = '<option value="'.intval($sub['fid']).'"'.($sub['fid'] == $currentfid ? ' selected="selected"' : '').'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
                    }
                }
            }
        }
        $forumselect[] = '<option value="" disabled="disabled">&nbsp;</option>';
    }
    $forumselect[] = '</select>';
    return implode("\n", $forumselect);
}

function readFileAsINI($filename) {
    $lines = file($filename);
    foreach($lines as $line_num => $line) {
        $temp = explode("=",$line);
        if ($temp[0] != 'dummy') {
            $key = trim($temp[0]);
            $val = trim($temp[1]);
            $thefile[$key] = $val;
        }
    }
    return $thefile;
}

function forumJump() {
    global $db, $self, $lang, $SETTINGS;

    $query = $db->query("SELECT f.* FROM ".X_PREFIX."forums f WHERE f.status='on' ORDER BY f.displayorder ASC");

    $standAloneForums = array();
    $forums = array();
    $categories = array();
    $subforums = array();
    while($forum = $db->fetch_array($query)) {
        $perms = checkForumPermissions($forum);
        if (X_SADMIN || $SETTINGS['hideprivate'] == 'off' || $forum['type'] == 'group' || ($perms[X_PERMS_VIEW] && $perms[X_PERMS_USERLIST])) {
            $forum['name'] = fnameOut($forum['name']);
            if (!X_SADMIN && $forum['password'] != '') {
                $fidpw = postedVar('fidpw'.$forum['fid'], '', FALSE, FALSE, FALSE, 'c');
                if ($forum['password'] !== $fidpw) {
                    continue;
                }
            }

            switch($forum['type']) {
                case 'group':
                    $categories[] = $forum;
                    break;
                case 'sub':
                    if (!isset($subforums[$forum['fup']])) {
                        $subforums[$forum['fup']] = array();
                    }
                    $subforums[$forum['fup']][] = $forum;
                    break;
                case 'forum':
                default:
                    if ($forum['fup'] == 0) {
                        $standAloneForums[] = $forum;
                    } else {
                        if (!isset($forums[$forum['fup']])) {
                            $forums[$forum['fup']] = array();
                        }
                        $forums[$forum['fup']][] = $forum;
                    }
                    break;
            }
        }
    }
    $db->free_result($query);

    $forumselect = array();

    $forumselect[] = "<select onchange=\"if (this.options[this.selectedIndex].value) {window.location=(''+this.options[this.selectedIndex].value)}\">";
    $forumselect[] = '<option value="0" selected="selected">'.$lang['forumjumpselect'].'</option>';

    unset($forum);
    reset($forums);

    foreach($standAloneForums as $forum) {
        $forumselect[] = '<option value="'.ROOT.'forumdisplay.php?fid='.intval($forum['fid']).'"> &nbsp; &raquo; '.$forum['name'].'</option>';
        if (isset($subforums[$forum['fid']])) {
            foreach($subforums[$forum['fid']] as $sub) {
                $forumselect[] = '<option value="'.ROOT.'forumdisplay.php?fid='.intval($sub['fid']).'">&nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
            }
        }
    }

    foreach($categories as $group) {
        if (isset($forums[$group['fid']])) {
            $forumselect[] = '<option value="0"></option>';
            $forumselect[] = '<option value="'.ROOT.'index.php?gid='.intval($group['fid']).'">'.$group['name'].'</option>';
            foreach($forums[$group['fid']] as $forum) {
                $forumselect[] = '<option value="'.ROOT.'forumdisplay.php?fid='.intval($forum['fid']).'"> &nbsp; &raquo; '.$forum['name'].'</option>';
                if (isset($subforums[$forum['fid']])) {
                    foreach($subforums[$forum['fid']] as $sub) {
                        $forumselect[] = '<option value="'.ROOT.'forumdisplay.php?fid='.intval($sub['fid']).'">&nbsp; &nbsp; &raquo; '.$sub['name'].'</option>';
                    }
                }
            }
        }
    }
    $forumselect[] = '</select>';
    return implode("\n", $forumselect);
}

function checkForumPermissions($forum) {
    // 1. Check Forum Permissions
    global $self;
    $status = array(
        'Super Administrator' => 1,
        'Administrator'       => 2,
        'Super Moderator'     => 4,
        'Moderator'           => 8,
        'Member'              => 16,
        ''                    => 32,
        'Banned'              => (1 << 30));  //$status['Banned'] == 2^30

    // NewPoll,NewThread,NewReply,View,Userlist,Password
    $ret = array(false, false, false, false, false, false);
    $pp = explode(',', $forum['postperm']);
    foreach($pp as $key=>$val) {
        if (($val & $status[$self['status']]) == $status[$self['status']]) {
            $ret[$key] = true;
        }
    }

    // 2. Check for userlist
    $userlist = trim($forum['userlist']);

    if (strlen($userlist) > 0) {
        if (modcheck($self['username'], $forum['moderator']) == "Moderator") {
            $ret[X_PERMS_USERLIST] = true;
        } else {
            $users = explode(',', $userlist);
            foreach($users as $user) {
                if (strtolower(trim($user)) == strtolower($self['username'])) {
                    $ret[X_PERMS_USERLIST] = true;
                    break;
                }
            }
        }
    } else {
        $ret[X_PERMS_USERLIST] = true;
    }

    // 3.Check for password
    $pwinput = postedVar('fidpw'.$forum['fid'], '', FALSE, FALSE, FALSE, 'c');
    if ($forum['password'] != '') {
        if ($pwinput == $forum['password']) {
            $ret[X_PERMS_PASSWORD] = true;
        } else {
            $ret[X_PERMS_PASSWORD] = false;
        }
    } else {
        $ret[X_PERMS_PASSWORD] = true;
    }

    return $ret;
}

function handlePasswordDialog($fid) {
    global $db, $url, $cookiepath, $cookiedomain;  // function vars
    global $THEME, $lang, $oToken, $altbg1, $altbg2, $tablewidth, $tablespace, $bordercolor;  // template vars

    $pwform = '';
    $pwinput = postedVar('pw', '', FALSE, FALSE);
    $query = $db->query("SELECT password FROM ".X_PREFIX."forums WHERE fid=$fid");
    if ($pwinput != '' And $db->num_rows($query) == 1) {
        $pass = $db->result($query, 0);

        if ($pwinput == $pass) {
            put_cookie('fidpw'.$fid, $pass, (time() + (86400*30)), $cookiepath, $cookiedomain);
            redirect($url, 0);
        } else {
            eval('$pwform = "'.template('forumdisplay_password').'";');
            error($lang['invalidforumpw'], true, '', $pwform, false, true, false, true);
        }
    } else {
        eval('$pwform = "'.template('forumdisplay_password').'";');
        error($lang['forumpwinfo'], true, '', $pwform, false, true, false, true);
    }
}
?>
