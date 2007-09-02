<?php
/**
 * XMB 1.9.8 Engage Final
 *
 * Developed By The XMB Group
 * Copyright (c) 2001-2007, The XMB Group
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 **/

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

    switch ($type) {
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
        $namesarray = array_unique(array_merge(func_get_args(), array('header', 'css', 'error', 'message', 'footer', 'footer_querynum', 'footer_phpsql', 'footer_totaltime', 'footer_load')));
        $sql = "'".implode("', '", $namesarray)."'";
        $query = $db->query("SELECT name, template FROM ".X_PREFIX."templates WHERE name IN ($sql)");
        while ($template = $db->fetch_array($query)) {
            templatecache(X_CACHE_PUT, $template['name'], $template['template']);
        }
        $db->free_result($query);
    }
}

function censor($txt, $ignorespaces=false) {
    global $censorcache;

    if (is_array($censorcache)) {
        if (count($censorcache) > 0) {
            foreach($censorcache as $find=>$replace) {
                if ($ignorespaces === true) {
                    $txt = str_replace($find, $replace, $txt);
                } else {
                    $txt = preg_replace("#(^|[^a-z])(".preg_quote($find).")($|[^a-z])#si", '\1'.$replace.'\3', $txt);
                }
            }
        }
    }
    return $txt;
}

function smile($txt) {
    global $smiliesnum, $smiliecache, $smdir;

    if ($smiliesnum > 0) {
        reset($smiliecache);
        foreach ($smiliecache as $code=>$url) {
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
    global $imgdir, $bordercolor, $db, $smdir, $smiliecache, $censorcache, $smiliesnum, $wordsnum, $versionbuild, $lang, $fontsize;

    $message = checkOutput($message, $allowhtml, '', true);

    $message = censor($message, $ignorespaces);

    $bballow = ($allowbbcode == 'yes' || $allowbbcode == 'on') ? (($bbcodeoff != 'off' && $bbcodeoff != 'yes') ? true : false) : false;
    $smiliesallow = ($allowsmilies == 'yes' || $allowsmilies == 'on') ? (($smileyoff != 'off' && $smileyoff != 'yes') ? true : false) : false;

    if ($bballow) {
        $message = stripslashes($message);

        if ($ismood == 'yes') {
            $message = str_replace(array('[quote]', '[/quote]', '[code]', '[/code]', '[list]', '[/list]', '[list=1]', '[list=a]', '[list=A]', '[/list=1]', '[/list=a]', '[/list=A]'), '', $message);
        }

        $begin = array(
                0    => '[b]',
                1    => '[i]',
                2    => '[u]',
                3    => '[marquee]',
                4    => '[blink]',
                5    => '[strike]',
                6    => '[quote]',
                7    => '[code]',
                8    => '[list]',
                9    => '[list=1]',
                10   => '[list=a]',
                11   => '[list=A]',
        );

        $end = array(
                0    => '[/b]',
                1    => '[/i]',
                2    => '[/u]',
                3    => '[/marquee]',
                4    => '[/blink]',
                5    => '[/strike]',
                6    => '[/quote]',
                7    => '[/code]',
                8    => '[/list]',
                9    => '[/list=1]',
                10   => '[/list=a]',
                11   => '[/list=A]',
        );

        foreach ($begin as $key=>$value) {
            $check = substr_count($message, $value) - substr_count($message, $end[$key]);
            if ($check > 0) {
                $message = $message.str_repeat($end[$key], $check);
            } else if ($check < 0) {
                $message = str_repeat($value, abs($check)).$message;
            }
        }

        $find = array(
                0   => '[b]',
                1   => '[/b]',
                2   => '[i]',
                3   => '[/i]',
                4   => '[u]',
                5   => '[/u]',
                6   => '[marquee]',
                7   => '[/marquee]',
                8   => '[blink]',
                9   => '[/blink]',
                10  => '[strike]',
                11  => '[/strike]',
                12  => '[quote]',
                13  => '[/quote]',
                14  => '[code]',
                15  => '[/code]',
                16  => '[list]',
                17  => '[/list]',
                18  => '[list=1]',
                19  => '[list=a]',
                20  => '[list=A]',
                21  => '[/list=1]',
                22  => '[/list=a]',
                23  => '[/list=A]',
                24  => '[*]',
                25  => '<br />'
        );

        $replace = array(
                0   => '<strong>',
                1   => '</strong>',
                2   => '<em>',
                3   => '</em>',
                4   => '<u>',
                5   => '</u>',
                6   => '<marquee>',
                7   => '</marquee>',
                8   => '<blink>',
                9   => '</blink>',
                10  => '<strike>',
                11  => '</strike>',
                12  => "</font><table align=\"center\" class=\"quote\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"quote\">$lang[textquote]</td></tr><tr><td class=\"quotemessage\">",
                13  => " </td></tr></table><font class=\"mediumtxt\">",
                14  => "</font><table align=\"center\" class=\"code\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"code\">$lang[textcode]</td></tr><tr><td class=\"codemessage\">",
                15  => "</td></tr></table><font class=\"mediumtxt\">",
                16  => '<ul type="square">',
                17  => '</ul>',
                18  => '<ol type="1">',
                19  => '<ol type="A">',
                20  => '<ol type="A">',
                21  => '</ol>',
                22  => '</ol>',
                23  => '</ol>',
                24  => '<li />',
                25  => ' <br />'
        );

        //$message = str_replace($find, $replace, $message);

        //if ($smiliesallow) {
        //    $message = smile($message);
        //}
        // contributed by JDaniels
        if ($smiliesallow) {
            $messagearray = preg_split("/\[code\]|\[\/code\]/", $message);
            for ($i = 0; $i < sizeof($messagearray); $i++) {
                if (sizeof($messagearray) != 1) {
                    if ($i == 0) {
                        $messagearray[$i] = $messagearray[$i]."[code]";
                        $messagearray[$i] = smile($messagearray[$i]);
                    } else if ($i == sizeof($messagearray) - 1) {
                        $messagearray[$i] = "[/code]".$messagearray[$i];
                        $messagearray[$i] = smile($messagearray[$i]);
                    } else if ($i % 2 == 0) {
                        $messagearray[$i] = "[/code]".$messagearray[$i]."[code]";
                        $messagearray[$i] = smile($messagearray[$i]);
                    }
                } else {
                    $messagearray[0] = smile($messagearray[0]);
                }
            }
            $message = implode("", $messagearray);
        }

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
            if (false == strpos($message, 'javascript:')) {
                $patterns[] = '#\[img\](http[s]?|ftp[s]?){1}://([:a-z\\./_\-0-9%~]+){1}\[/img\]#Smi';
                $replacements[] = '<img src="\1://\2\3" border="0" alt="\1://\2\3"/>';

                $patterns[] = "#\[img=([0-9]*?){1}x([0-9]*?)\](http[s]?|ftp[s]?){1}://([:~a-z\\./0-9_\-%]+){1}(\?[a-z=0-9&_\-;~]*)?\[/img\]#Smi";
                $replacements[] = '<img width="\1" height="\2" src="\3://\4\5" alt="\3://\4\5" border="0" />';

                $patterns[] = "#\[flash=([0-9]*?){1}x([0-9]*?)\]([^\"'<>]*?)\[/flash\]#Ssi";
                $replacements[] = '<object type="application/x-shockwave-flash" data="$3" width="$1" height="$2"><param name="movie" value="$3" /><param name="AllowScriptAccess" value="never" /></object>';
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

        $message = preg_replace($patterns, $replacements, $message);

        $message = addslashes($message);
    } else {
        //if ($smiliesallow) {
        //    $message = smile($message);
        //}
        if ($smiliesallow) {
            $messagearray = preg_split("/\[code\]|\[\/code\]/", $message);
            for ($i = 0; $i < sizeof($messagearray); $i++) {
                if (sizeof($messagearray) != 1) {
                    if ($i == 0) {
                        $messagearray[$i] = $messagearray[$i]."[code]";
                        $messagearray[$i] = smile($messagearray[$i]);
                    } else if ($i == sizeof($messagearray) - 1) {
                        $messagearray[$i] = "[/code]".$messagearray[$i];
                        $messagearray[$i] = smile($messagearray[$i]);
                    } else if ($i % 2 == 0) {
                        $messagearray[$i] = "[/code]".$messagearray[$i]."[code]";
                        $messagearray[$i] = smile($messagearray[$i]);
                    }
                } else {
                    $messagearray[0] = smile($messagearray[0]);
                }
            }
            $message = implode("", $messagearray);
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

function modcheck($status, $username, $mods) {

    if (X_ADMIN || in_array($status, array('Super Moderator'))) {
        return 'Moderator';
    }

    $retval = '';
    if ($status == 'Moderator') {
        $username = strtoupper($username);
        $mods = explode(',', $mods);
        foreach ($mods as $key=>$moderator) {
            if (strtoupper(trim($moderator)) == $username) {
                $retval = 'Moderator';
                break;
            }
        }
    }
    return $retval;
}

function privfcheck($private, $userlist) {
    global $self, $xmbuser;

    if (X_SADMIN) {
        return true;
    }

    switch ($private) {
        case 4:
            return false;
            break;
        case 3:
            return X_STAFF;
            break;
        case 2:
            return X_ADMIN;
            break;
        case 1:
            if (trim($userlist) == '') {
                return true;
            }

            $user = explode(',', $userlist);
            $xuser = strtolower($xmbuser);
            foreach ($user as $usr) {
                $usr = strtolower(trim($usr));
                if ($usr != '' && $xuser == $usr) {
                   return true;
                }
            }
            return false;
            break;
        default:
            return false;
            break;
    }
    return false;
}

function forum($forum, $template) {
    global $timecode, $dateformat, $lang, $xmbuser, $self, $lastvisit2, $timeoffset, $hideprivate, $addtime, $oldtopics, $lastvisit;
    global $altbg1, $altbg2, $imgdir, $THEME, $SETTINGS, $index_subforums;

    $forum['name'] = html_entity_decode($forum['name']);
	$forum['description'] = html_entity_decode($forum['description']);		// Fix for HTML characters in forum descriptions
	
    if (isset($forum['moderator']) && $forum['lastpost'] != '') {
        $lastpost = explode('|', $forum['lastpost']);
        $dalast = $lastpost[0];
        if ($lastpost[1] != 'Anonymous' && $lastpost[1] != '') {
            $lastpost[1] = '<a href="member.php?action=viewpro&amp;member='.rawurlencode($lastpost[1]).'">'.$lastpost[1].'</a>';
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
    if (X_SADMIN || $SETTINGS['hideprivate'] == 'off' || privfcheck($forum['private'], $forum['userlist'])) {
        if (isset($forum['moderator']) && $forum['moderator'] != '') {
            $moderators = explode(', ', $forum['moderator']);
            $forum['moderator'] = array();
            for ($num = 0; $num < count($moderators); $num++) {
                $forum['moderator'][] = '<a href="member.php?action=viewpro&amp;member='.rawurlencode($moderators[$num]).'">'.$moderators[$num].'</a>';
            }
            $forum['moderator'] = implode(', ', $forum['moderator']);
            $forum['moderator'] = '('.$lang['textmodby'].' '.$forum['moderator'].')';
        }

        // create sub-forums on index
        $subforums = array();
        if (count($index_subforums) > 0) {
            for ($i=0; $i < count($index_subforums); $i++) {
                $sub = $index_subforums[$i];
                if ($sub['fup'] == $forum['fid']) {
                    if (X_SADMIN || $SETTINGS['hideprivate'] == 'off' || privfcheck($sub['private'], $sub['userlist'])) {
                        $subforums[] = '<a href="forumdisplay.php?fid='.intval($sub['fid']).'">'.html_entity_decode(stripslashes($sub['name'])).'</a>';
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
        eval('$foruminfo = stripslashes("'.template($template).'");');
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

        for ($i = $from; $i <= $to; $i++) {
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
    $smilies = $smilieinsert = '';

    if ($smileyinsert == 'on' && $smcols != '') {
        if ($smtotal == 0) {
            $querysmilie = $db->query("SELECT * FROM ".X_PREFIX."smilies WHERE type='smiley' ORDER BY code DESC");
        } else {
            $querysmilie = $db->query("SELECT * FROM ".X_PREFIX."smilies WHERE type='smiley' ORDER BY code DESC LIMIT 0, ".$smtotal);
        }

        if (($smilienum = $db->num_rows($querysmilie)) > 0){
            while($smilie = $db->fetch_array($querysmilie)) {
                eval('$sms[] = "'.template('functions_smilieinsert_smilie').'";');
            }

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
                for ($i=0;$i<$left;$i++) {
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

    $postcount = 0;
    $threadcount = 0;

    $query = $db->query("SELECT count(pid) FROM ".X_PREFIX."posts WHERE fid='$fid'");
    $postcount = $db->result($query, 0);
    $db->free_result($query);

    $query = $db->query("SELECT count(tid) FROM ".X_PREFIX."threads WHERE (fid='$fid' AND closed != 'moved')");
    $threadcount = $db->result($query, 0);
    $db->free_result($query);

    $query = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE fup='$fid'");
    while ($children = $db->fetch_array($query)) {
        $chquery1 = $db->query("SELECT count(pid) FROM ".X_PREFIX."posts WHERE fid='$children[fid]'");
        $postcount += $db->result($chquery1, 0);
        $db->free_result($chquery1);

        $chquery2 = $db->query("SELECT count(tid) FROM ".X_PREFIX."threads WHERE fid='$children[fid]' AND closed != 'moved'");
        $threadcount += $db->result($chquery2, 0);
        $db->free_result($chquery2);
    }
    $db->free_result($query);

    $query = $db->query("SELECT t.lastpost FROM ".X_PREFIX."threads t, ".X_PREFIX."forums f WHERE (t.fid=f.fid AND f.fid='$fid') OR (t.fid=f.fid AND f.fup='$fid') ORDER BY t.lastpost DESC LIMIT 0,1");
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
        $input = str_replace($word, "_".$word, $input);
    }
    return $input;
}

function checkOutput($output, $allowhtml='no', $word='', $allowEntities=false) {
    $output = trim($output);
    if ($allowhtml == 'yes' || $allowhtml == 'on') {
        $output = htmlspecialchars_decode($output);
    }
    if ($word != '') {
        $output = str_replace($word, "_".$word, $output);
    }

    if ($allowEntities) {
        $output = preg_replace('/(&amp;(#[0-9]+);)/Ui', '&$2;', $output);
    }
    return $output;
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
        if (! empty($load)) {
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
        foreach ($db->querylist as $key=>$val) {
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

function pwverify($pass='', $url, $fid, $showHeader=false) {
    global $self, $cookiepath, $cookiedomain, $lang;

    if (X_SADMIN) {
        return true;
    }

    $pwform = '';
    if (trim($pass) != '' && $_COOKIE['fidpw'.$fid] != $pass) {
        if ($_POST['pw'] != $pass) {
            extract($GLOBALS);
            eval('$pwform = "'.template('forumdisplay_password').'";');
            error(((isset($_POST['pw'])) ? $lang['invalidforumpw'] : $lang['forumpwinfo']), $showHeader, '', $pwform, false, true, false, true);
        } else {
            put_cookie("fidpw$fid", $pass, (time() + (86400*30)), $cookiepath, $cookiedomain);
            redirect($url, 0);
        }
        exit();
    }

    return true;
}

function redirect($path, $timeout=2, $type=X_REDIRECT_HEADER) {
    if (strpos(urldecode($path), "\n") !== false || strpos(urldecode($path), "\r") !== false) {
        error('Tried to redirect to potentially insecure url.');
    }

    session_write_close();

    $type = (headers_sent() || $type == X_REDIRECT_JS) ? X_REDIRECT_JS : X_REDIRECT_HEADER;
    if ($type == X_REDIRECT_JS) {
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
        } else {
            header("Refresh: $timeout; URL=./$path");
        }
    }
    return true;
}

function postperm(& $forums, $type) {
    global $lang, $self, $whopost1, $whopost2;
    static $cache;

    if (!isset($forums['postperm'])) {
        return false;
    }

    $pperm = explode('|', $forums['postperm']);
    if (!isset($cache[$forums['fid']])) {
        switch ($pperm[0]) {
            case 1:
                $whopost1 = $lang['whocanpost11'];
                break;
            case 2:
                $whopost1 = $lang['whocanpost12'];
                break;
            case 3:
                $whopost1 = $lang['whocanpost13'];
                break;
            case 4:
                $whopost1 = $lang['whocanpost14'];
                break;
        }

        switch ($pperm[1]) {
            case 1:
                $whopost2 = $lang['whocanpost21'];
                break;
            case 2:
                $whopost2 = $lang['whocanpost22'];
                break;
            case 3:
                $whopost2 = $lang['whocanpost23'];
                break;
            case 4:
                $whopost2 = $lang['whocanpost24'];
                break;
        }
        $cache[$forums['fid']] = true;
    }

    if (X_SADMIN) {
        return true;
    }

    $perm = ($type == 'thread') ? $pperm[0] : $pperm[1];
    switch ($forums['private']) {
        case 1:
            $fplen = isset($forums['password']) ? strlen($forums['password']) : 0;
            $fulen = isset($forums['userlist']) ? strlen($forums['userlist']) : 0;
            if (($fplen > 1 || $fulen > 1) && privfcheck($forums['private'], $forums['userlist'])) {
                return true;
            }

            if (!X_STAFF) {
                if ($perm == 1) {
                    return true;
                }
                break;
            }
        case 3:
            if (!X_ADMIN) {
                if ($perm == 1 || $perm == 3) {
                    return true;
                }
                break;
            }
        case 2:
            if ($perm < 4) {
                return true;
            }
            break;
        case 4:
            return false;
            break;
        default:
            return false;
            break;
    }
    return false;
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
    global $lang, $filename, $filetype, $filesize;

    $filename = $filetype = '';
    $filesize = 0;

    if ($file['name'] != 'none' && !empty($file['name']) && $attachstatus != 'off' && is_uploaded_file($file['tmp_name'])) {
        if (!isValidFilename($file['name'])) {
            error($lang['invalidFilename'], false, '', '', false, false, false, false);
            return false;
        }

        $filesize = intval(filesize($file['tmp_name'])); // fix bad filesizes
        if ($file['size'] > $max_size) {
            error($lang['attachtoobig'], false, '', '', false, false, false, false);
            return false;
        } else {
            $attachment = addslashes(fread(fopen($file['tmp_name'], 'rb'), filesize($file['tmp_name'])));
            $filename = $file['name'];
            $filetype = $file['type'];
            $filesize = $file['size'];

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

    $navigation = '';
    nav();
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
        $return = $prepend . $error . $footer . $append;
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

    $messagedisplay = (isset($args[0]) ? $args[0] : '');
    $showheader = (isset($args[1]) ? $args[1] : true);
    $prepend = (isset($args[2]) ? $args[2] : '');
    $append = (isset($args[3]) ? $args[3] : '');
    $redirect = (isset($args[4]) ? $args[4] : false);
    $die = (isset($args[5]) ? $args[5] : true);
    $return_str = (isset($args[6]) ? $args[6] : false);
    $showfooter = (isset($args[7]) ? $args[7] : true);

    $navigation = '';
    nav();
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

    $message = '';
    eval('$message = "'.template('message').'";');
    if ($showfooter === true) {
        eval('$footer = "'.template('footer').'";');
    } else {
        $footer = '';
    }

    if ($return_str !== false) {
        $return = $prepend . $message . $footer . $append;
    } else {
        echo $prepend . $message . $append . $footer;
        $return = '';
    }

    if ($die) {
        exit();
    }
    return $return;
}

function array_keys2keys($array, $translator) {
    $new_array = array();

    foreach ($array as $key=>$val) {
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

    foreach ($tables as $name) {
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

    foreach ($find as $key=>$val) {
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

        while ($a = $db->fetch_array($resource, SQL_NUM)) {
            ?>
            <tr bgcolor="<?php echo $altbg1?>" class="ctrtablerow">
            <?php
            for ($i=0;$i<$count;$i++) {
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

function put_cookie($name, $value=null, $expire=null, $path=null, $domain=null, $secure=null, $setVia=X_SET_HEADER) {

    if (!headers_sent() && $setVia != X_SET_JS) {
        return setcookie($name, $value, $expire, $path, $domain, $secure);
    } else {
        if ($expire >= 0) {
            $expire = date('r', $expire);
        } else {
            $expire = null;
        }
        ?>
        <script type="text/javascript">
            function setcookie(name, value="deleted", expire=0, path="", domain="", secure=0) {
                if (expire == 0) {
                    var now = new Date();
                    expire = now.toGMTString();
                }

                if (path == "") {
                    path = window.location.pathname;
                }

                if (domain == "") {
                    domain = window.location.host;
                }

                // create cookie string (expire in GMT TIME!)
                var cookie = '';
                cookie = name+"="+value+"; expires="+expire+"; path="+path+"; domain="+domain+"; secure="+secure"; HttpOnly";
                document.cookie += cookie;
            }
            setcookie(<?php echo $name?>, <?php echo $value?>, <?php echo $expire?>, <?php echo $path?>, <?php echo $domain?>, <?php echo $secure?>);
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
    $subject = str_replace(array("\r\n", "\r", "\n"), array("\n", "\n", "\r\n"), $subject);

    switch ($mailer['type']) {
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
            foreach ($additional_headers as $k=>$h) {
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

    $months = array($lang['textjan'],$lang['textfeb'],$lang['textmar'],$lang['textapr'],$lang['textmay'],$lang['textjun'],$lang['textjul'],$lang['textaug'],$lang['textsep'],$lang['textoct'],$lang['textnov'],$lang['textdec']);

    return $months[$num-1];
}

function forumList($selectname='srchfid', $multiple=false, $allowall=true, $currentfid=0) {
    global $db, $self, $lang;

    $restrict = array();
    switch ($self['status']) {
        case 'Member':
            $restrict[] = "private != '3'";
        case 'Moderator':
        case 'Super Moderator':
            $restrict[] = "private != '2'";
        case 'Administrator':
            $restrict[] = "userlist = ''";
        case 'Super Administrator':
            break;
        default:
            $restrict[] = "private != '3'";
            $restrict[] = "private != '2'";
            $restrict[] = "userlist = ''";
            $restrict[] = "password = ''";
            break;
    }
    $restrict = implode(' AND ', $restrict);

    if ($restrict != '') {
        $sql = $db->query("SELECT fid, type, name, fup, status, private, userlist, password FROM ".X_PREFIX."forums WHERE $restrict AND status = 'on' ORDER BY displayorder");
    } else {
        $sql = $db->query("SELECT fid, type, name, fup, private, userlist, password FROM ".X_PREFIX."forums ORDER BY displayorder");
    }

    $standAloneForums = array();
    $forums = array();
    $categories = array();
    $subforums = array();
    while ($forum = $db->fetch_array($sql)) {
        $forum['name'] = html_entity_decode($forum['name']);
        if (!X_SADMIN && $forum['password'] != '') {
            $fidpw = isset($_COOKIE['fidpw'.$forum['fid']]) ? trim($_COOKIE['fidpw'.$forum['fid']]) : '';
            if ($forum['password'] !== $fidpw) {
                continue;
            }
        }

        switch ($forum['type']) {
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
    $db->free_result($sql);

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

    foreach ($standAloneForums as $forum) {
        $forumselect[] = '<option value="'.intval($forum['fid']).'"'.($forum['fid'] == $currentfid ? ' selected="selected"' : '').'> &nbsp; &raquo; '.stripslashes($forum['name']).'</option>';
        if (isset($subforums[$forum['fid']])) {
            foreach ($subforums[$forum['fid']] as $sub) {
                $forumselect[] = '<option value="'.intval($sub['fid']).'"'.($sub['fid'] == $currentfid ? ' selected="selected"' : '').'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.stripslashes($sub['name']).'</option>';
            }
        }
    }

    $forumselect[] = '<option value="" disabled="disabled">&nbsp;</option>';
    foreach ($categories as $group) {
        if (isset($forums[$group['fid']]) && count($forums[$group['fid']]) > 0) {
            $forumselect[] = '<option value="'.intval($group['fid']).'" disabled="disabled">'.stripslashes($group['name']).'</option>';
            foreach ($forums[$group['fid']] as $forum) {
                $forumselect[] = '<option value="'.intval($forum['fid']).'"'.($forum['fid'] == $currentfid ? ' selected="selected"' : '').'> &nbsp; &raquo; '.stripslashes($forum['name']).'</option>';
                if (isset($subforums[$forum['fid']])) {
                    foreach ($subforums[$forum['fid']] as $sub) {
                        $forumselect[] = '<option value="'.intval($sub['fid']).'"'.($sub['fid'] == $currentfid ? ' selected="selected"' : '').'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.stripslashes($sub['name']).'</option>';
                    }
                }
            }
        }
        $forumselect[] = '<option value="" disabled="disabled">&nbsp;</option>';
    }
    $forumselect[] = '</select>';
    return implode("\n", $forumselect);
}
?>