<?php
/* $Id: functions.php,v 1.55.2.27 2004/09/24 19:10:32 Tularis Exp $ */
/*
    XMB 1.9
    � 2001 - 2004 Aventure Media & The XMB Development Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

function nav($add=false) {
    global $navigation;
    if(!$add) {
        $navigation = '';
    } else {
        $navigation .= ' &raquo; '. $add;
    }

    return $navigation;
}

function template($name){
    global $tempcache, $table_templates, $db, $comment_output;

    if (($template = templatecache(X_CACHE_GET, $name)) === false) {
        $query = $db->query("SELECT template FROM $table_templates WHERE name='$name'");
        if($db->num_rows($query) == 1) {
            if(DEBUG) {
                // show an alert that the template $name is not cached!
                trigger_error('Efficiency Notice: The template `'.$name.'` was not preloaded.', E_USER_NOTICE);
            }
            $gettemplate = $db->fetch_array($query);
            templatecache(X_CACHE_PUT, $name, $gettemplate['template']);
            $template = $gettemplate['template'];
        } else {
            if(DEBUG) {
                // show an alert that the template $name is not cached!
                trigger_error('Efficiency Warning: The template `'.$name.'` could not be found.', E_USER_WARNING);
            }
        }
    }

    $template = str_replace("\\'","'", $template);

    if($name != 'phpinclude' && $comment_output === true) {
        return "<!--Begin Template: $name -->\n$template\n<!-- End Template: $name -->";
    } else {
        return $template;
    }
}

function templatecache($type=X_CACHE_GET, $name, $data='') {
    static $cache;

    switch($type) {
        case X_CACHE_GET:
            if(!isset($cache[$name])) {
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
    global $db, $tempcache, $table_templates;

    $num = func_num_args();
    if($num < 1){
        echo 'Not enough arguments given to loadtemplates() on line: '.__LINE__;
        return false;
    }else{
        $namesarray = func_get_args();
        $sql = "'".implode("', '", $namesarray)."'";

        $query = $db->query("SELECT * FROM $table_templates WHERE name IN ($sql)");
        while($template = $db->fetch_array($query)) {
            templatecache(X_CACHE_PUT, $template['name'], $template['template']);
        }
    }
}

function censor($txt, $ignorespaces=false) {
    global $censorcache;

    if (is_array($censorcache)) {
        if (count($censorcache) > 0) {
            reset($censorcache);
            // assign a tuple of ($find, $replace) from censorcache one at a time until censorcache is exhausted
            while (list($find, $replace) = each($censorcache)) {
                if ($ignorespaces === true) {
                    $txt = str_replace($find, $replace, $txt);
                }
                else {
                    $txt = preg_replace("#(^|[[:space:]\.\,\!\?\[\]\{\}\(\)])(".preg_quote($find).")($|[[:space:]\.\,\!\?\(\)\[\]\{\}])#si",
                                        "$1" . $replace . "$3",
                                        $txt);
                }
            }
        }
    }

    return $txt;
}

function smile($txt) {
    global $smiliesnum, $smiliecache, $smdir;

    if($smiliesnum > 0) {
        reset($smiliecache);
        foreach($smiliecache as $code=>$url){
            $txt = str_replace($code, '<img src="./'.$smdir.'/'.$url.'" style="border:none" alt="'.$code.'" />', $txt);
        }
    }
    return $txt;
}

function createAbsFSizeFromRel($rel) {
    global $fontsize;
    static $cachedFs;
    
    if(!is_array($cachedFs) || count($cachedFs) != 2) {
        preg_match('#([0-9]+)([a-z]+)?#i', $fontsize, $res);
        $cachedFs[0] = $res[1];
        $cachedFs[1] = $res[2];
        
        if(empty($cachedFs[1])) {
            $cachedFs[1] = 'px';
        }
    }
    
    $o = ($rel+$cachedFs[0]).$cachedFs[1];
    //echo $o;
    return $o;
}
    

function postify($message, $smileyoff='no', $bbcodeoff='no', $allowsmilies='yes', $allowhtml='yes', $allowbbcode='yes', $allowimgcode='yes', $ignorespaces=false, $ismood="no", $wrap="yes") {
    global $imgdir, $bordercolor, $table_words, $table_forums, $table_smilies, $db, $smdir, $smiliecache, $censorcache, $smiliesnum, $wordsnum, $versionbuild, $lang, $fontsize;

    $message = checkOutput($message, $allowhtml);

    $message = censor($message, $ignorespaces);

    $bballow = ($allowbbcode == 'yes' || $allowbbcode == 'on') ? (($bbcodeoff != 'off' && $bbcodeoff != 'yes') ? true : false) : false;
    $smiliesallow = ($allowsmilies == 'yes' || $allowsmilies == 'on') ? (($smileyoff != 'off' && $smileyoff != 'yes') ? true : false) : false;

    if($bballow) {
        $message = stripslashes($message);

        if($ismood == "yes"){
            $message = str_replace(array('[poem]', '[/poem]', '[quote]', '[/quote]', '[code]', '[/code]', '[list]', '[/list]', '[list=1]', '[list=a]', '[list=A]', '[/list=1]', '[/list=a]', '[/list=A]'), '', $message);
        }

        $begin = array(
                0    => '[b]',
                1    => '[i]',
                2    => '[u]',
                3    => '[poem]',
                4    => '[marquee]',
                5    => '[blink]',
                6    => '[strike]',
                7    => '[quote]',
                8    => '[code]',
                9    => '[list]',
                10    => '[list=1]',
                11    => '[list=a]',
                12    => '[list=A]',
                );

        $end = array(
                0    => '[/b]',
                1    => '[/i]',
                2    => '[/u]',
                3    => '[/poem]',
                4    => '[/marquee]',
                5    => '[/blink]',
                6    => '[/strike]',
                7    => '[/quote]',
                8    => '[/code]',
                9    => '[/list]',
                10    => '[/list=1]',
                11    => '[/list=a]',
                12    => '[/list=A]',
                );

        foreach($begin as $key=>$value){
            $check = substr_count($message, $value) - substr_count($message, $end[$key]);
            if($check > 0){
                $message = $message.str_repeat($end[$key], $check);
            }elseif($check < 0){
                $message = str_repeat($value, abs($check)).$message;
            }
        }

        $find = array(
                0   => '[b]',
                1   => '[/b]',
                2   => '[i]',
                3   => '[/i]',
                4   => '[poem]',
                5   => '[/poem]',
                6   => '[u]',
                7   => '[/u]',
                8   => '[marquee]',
                9   => '[/marquee]',
                10  => '[blink]',
                11  => '[/blink]',
                12  => '[strike]',
                13  => '[/strike]',
                14  => '[vinfo]',
                15  => '[quote]',
                16  => '[/quote]',
                17  => '[code]',
                18  => '[/code]',
                19  => '[list]',
                20  => '[/list]',
                21  => '[list=1]',
                22  => '[list=a]',
                23  => '[list=A]',
                24  => '[/list=1]',
                25  => '[/list=a]',
                26  => '[/list=A]',
                27  => '[credits]',
                28  => '[*]',
                29  => '[buildedition]',
                30  => '<br />'
                );

        $replace = array(
                0   => '<strong>',
                1   => '</strong>',
                2   => '<em>',
                3   => '</em>',
                4   => '<div align=\"center\"><em>',
                5   => '</div></em>',
                6   => '<u>',
                7   => '</u>',
                8   => '<marquee>',
                9   => '</marquee>',
                10  => '<blink>',
                11  => '</blink>',
                12  => '<strike>',
                13  => '</strike>',
                14  => '<strong>'.strrev('suxeN - 1.9.1 BMX').'</strong>',
                15  => "<table align=\"center\" class=\"quote\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"quote\">$lang[textquote]</td></tr><tr><td class=\"quotemessage\">",
                16  => "</td></tr></table>",
                17  => "<table align=\"center\" class=\"code\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"code\">$lang[textcode]</td></tr><tr><td class=\"codemessage\">",
                18  => "</td></tr></table>",
                19  => '<ul type=square>',
                20  => '</ul>',
                21  => '<ol type=1>',
                22  => '<ol type=A>',
                23  => '<ol type=A>',
                24  => '</ol>',
                25  => '</ol>',
                26  => '</ol>',
                27  => 'XMB 1.9.1 Main Developers - Tularis, vanderaj, Richard, RevMac, Daf, Ixan. For More Information On Other Staff - Visit xmbforum.com',
                28  => '<li>',
                29  => '<strong>Build ID: '.$versionbuild.'</strong>',
                30  => ' <br />'
                );

        $message = str_replace($find, $replace, $message);

        if($smiliesallow) {
            $message = smile($message);
        }
        
        $patterns = array();
        $replacements = array();

        //$message = eregi_replace("(^|[>[:space:]\n])([[:alnum:]]+)://([^[:space:]<]*)([[:alnum:]#?/&=])([<[:space:]\n]|$)","\\1<a href=\"\\2://\\3\\4\" target=\"_blank\">\\2://\\3\\4</a>\\5", $message);
        //$message = preg_replace('#([^\'"]|^)(http[s]?|ftp[s]?|gopher|irc){1}://([a-z\\./0-9%]+){1}(\?[a-z=0-9&;]*)?(\#[a-z0-9]+)?#mi', '\1<a href="\2://\3\4\5" target="_blank">\1\2://\3\4\5</a>', $message);

        $patterns[] = "#\[color=([^\"'<>]*?)\](.*?)\[/color\]#si";
        $replacements[] = '<font color="\1">\2</font>';

        $patterns[] = "#\[size=([^\"'<>]*?)\](.*?)\[/size\]#sie";
        $replacements[] = '"<font style=\"font-size: ".createAbsFSizeFromRel($1).";\">".stripslashes("$2")."</font>"';

        $patterns[] = "#\[font=([a-z\r\n\t 0-9]+)\](.*?)\[/font\]#si";
        $replacements[] = '<font face="\1">\2</font>';

        $patterns[] = "#\[align=([a-z]+)\](.*?)\[/align\]#si";
        $replacements[] = '<p align="\1">\2</p>';

        if(($allowimgcode != 'no' && $allowimgcode != 'off')) {

        if(!stristr($message, 'javascript:') && (stristr($message, 'jpg[/img]') || stristr($message, 'jpeg[/img]') || stristr($message, 'gif[/img]') || stristr($message, 'png[/img]') || stristr($message, 'bmp[/img]') || stristr($message, 'php[/img]'))) {

            $patterns[] = '#\[img\](http[s]?|ftp[s]?){1}://([:a-z\\./_\-0-9%~]+){1}(\?[a-z=_\-0-9&;]*)?\[/img\]#mi';
            $replacements[] = '<img src="\1://\2\3" border="0" alt="\1://\2\3"/>';

            $patterns[] = "#\[img=([0-9]*?){1}x([0-9]*?)\](http[s]?|ftp[s]?){1}://([:~a-z\\./0-9_\-%]+){1}(\?[a-z=0-9&_\-;]*)?\[/img\]#mi";
            $replacements[] = '<img width="\1" height="\2" src="\3://\4\5" alt="\3://\4\5" border="0" />';
        }

            $patterns[] = "#\[flash=([0-9]*?){1}x([0-9]*?)\](.*?)\[/flash\]#si";
            $replacements[] = '<OBJECT classid=clsid:D27CDB6E-AE6D-11cf-96B8-444553540000 codebase=http://active.macromedia.com/flash2/cabs/swflash.cab#version=6,0,0,0 ID=main WIDTH=\1 HEIGHT=\2><PARAM NAME=movie VALUE=\3><PARAM NAME=loop VALUE=false><PARAM NAME=menu VALUE=false><PARAM NAME=quality VALUE=best><EMBED src=\3 loop=false menu=false quality=best WIDTH=\1 HEIGHT=\2 TYPE=application/x-shockwave-flash PLUGINSPAGE=http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash></EMBED></OBJECT>';
        }

        $patterns[] = '#([^\'"=\]]|^)(http[s]?|ftp[s]?|gopher|irc){1}://([:a-z_\-\\./0-9%~]+){1}(\?[a-z=0-9\-_&;]*)?(\#[a-z0-9]+)?#mi';
        $replacements[] = '\1<a href="\2://\3\4\5" target="_blank">\2://\3\4\5</a>';


        $patterns[] = "#\[url\]([a-z]+?://){1}([^\"]*?)\[/url\]#mi";
        $replacements[] = '<a href="\1\2" target="_blank">\1\2</a>';

        $patterns[] = "#\[url\]([^\"]*?)\[/url\]#mi";
        $replacements[] = '<a href="http://\1" target="_blank">\1</a>';

        // Do we need this? yes!
        $patterns[] = "#\[url=([a-z]+?://){1}([^\"']*?)\](.*?)\[/url\]#mi";
        $replacements[] = '<a href="\1\2" target="_blank">\3</a>';

        $patterns[] = "#\[url=([^\"]*?)\](.*?)\[/url\]#mi";
        $replacements[] = '<a href="http://\1" target="_blank">\2</a>';

        $patterns[] = "#\[email\]([^\"]*?)\[/email\]#mi";
        $replacements[] = '<a href="mailto:\1">\1</a>';

        $patterns[] = "#\[email=(.*?){1}([^\"]*?)\](.*?)\[/email\]#mi";
        $replacements[] = '<a href="mailto:\1\2">\3</a>';

        $message = preg_replace($patterns, $replacements, $message);

        $message = addslashes($message);
    }else{
        if($smiliesallow) {
            $message = smile($message);
        }
    }

    $message = nl2br($message);
    if($wrap == "yes") {
        $message = wordwrap($message, 150, "\n", 1);
        $message = preg_replace('#(\[/?.*)\n(.*\])#mi', '\\1\\2', $message);
    }

    return $message;
}

function modcheck($status, $username, $mods) {

    if (in_array($status, array('Super Administrator', 'Administrator', 'Super Moderator'))) {
        return 'Moderator';
    }

    $retval = '';

    if ($status == 'Moderator') {
        $username = strtoupper($username);
        $mods = explode(',', $mods);
        foreach($mods as $key=>$moderator) {
            if(strtoupper(trim($moderator)) == $username) {
                $retval = 'Moderator';
                break;
            }
        }
    }
    return $retval;
}

/*
function privfcheck($private, $userlist) {
    global $self, $xmbuser, $hideprivate;

    if($self['status'] == 'Super Administrator') {
        return true;
    } elseif($private > 1 && X_ADMIN) { // implies 2 or 3
        return true;
    } elseif($private == 3 && X_STAFF) {
        return true;
    } elseif($private == 1 && trim($userlist) == '') {
        return true;
    } elseif($userlist != '') {
        $user = explode(',', $userlist);
        $xuser = strtolower($xmbuser);

        $userc = count($user);
        foreach($user as $usr) {
            $usr = strtolower(trim($usr));
            if($xuser == $usr && $usr != '') {
                return true;
            }
        }
        
        return false;
    }else{
        return false;
    }
}
*/

function privfcheck($private, $userlist) {
    global $self, $xmbuser;

    
        // 1 - All
        // 2 - Admins
        // 3 - Mods/Admins
        // 4 - No Viewing

    if($self['status'] == 'Super Administrator') {
        return true;
    }    
    
    switch($private) {
        case 4:
            return false;
            break;
        
        case 3:
            if(X_STAFF) {
                return true;
            } else {
                return false;
            }
            break;
        
        case 2:
            if(X_ADMIN) {
                return true;
            } else {
                return false;
            }
            break;
        
        case 1:
            if(trim($userlist) == '') {
                return true;
            } else {
                $user = explode(',', $userlist);
                $xuser = strtolower($xmbuser);

                $userc = count($user);
                foreach($user as $usr) {
                    $usr = strtolower(trim($usr));
                    if($xuser == $usr && $usr != '') {
                        return true;
                    }
                }
                return false;
            }
            break;
        
        default:
            return false;
            break;
    }
}

function forum($forum, $template) {
    global $timecode, $dateformat, $lang, $xmbuser, $self, $lastvisit2, $timeoffset, $hideprivate, $addtime;

    $altbg1 = $GLOBALS['altbg1'];
    $altbg2 = $GLOBALS['altbg2'];
    $imgdir = $GLOBALS['imgdir'];

    if($forum['lastpost'] != '') {
        $lastpost = explode('|', $forum['lastpost']);
        $dalast = $lastpost[0];
        if($lastpost[1] != 'Anonymous' && $lastpost[1] != '') {
            $lastpost[1] = '<a href="member.php?action=viewpro&amp;member='.rawurlencode($lastpost[1]).'">'.$lastpost[1].'</a>';
        } else {
            $lastpost[1] = $lang['textanonymous'];
        }

        $lastpostdate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));
        $lastposttime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600) + ($addtime * 3600));
        $lastpost = $lastpostdate.' '.$lang['textat'].' '.$lastposttime.'<br />'.$lang['textby'].' '.$lastpost[1];
        eval("\$lastpostrow = \"".template("".$template."_lastpost")."\";");
    } else {
        $dalast     = 0;
        $lastpost   = $lang['textnever'];
        eval("\$lastpostrow = \"".template($template.'_nolastpost')."\";");
    }

    if(($lastvisit2-540) < $dalast) {
        $folder = "<img src=\"$imgdir/red_folder.gif\" alt=\"$lang[altredfolder]\" />";
    } else {
        $folder = "<img src=\"$imgdir/folder.gif\" alt=\"$lang[altfolder]\" />";
    }

    if($dalast == "") {
        $folder = "<img src=\"$imgdir/folder.gif\" alt=\"$lang[altfolder]\" />";
    }

    $foruminfo = '';

    if($self['status'] == 'Super Administrator' || $hideprivate == 'off' || privfcheck($forum['private'], $forum['userlist'])) {
        if($forum['moderator'] != '') {
            $moderators = explode(', ', $forum['moderator']);
            $forum['moderator'] = array();
            for($num = 0; $num < count($moderators); $num++) {
                $forum['moderator'][] = '<a href="member.php?action=viewpro&amp;member='.$moderators[$num].'">'.$moderators[$num].'</a>';
            }
            $forum['moderator'] = implode(', ', $forum['moderator']);

            $forum['moderator'] = '('.$lang['textmodby'].' '.$forum['moderator'].')';
        }
        eval("\$foruminfo = \"".template("$template")."\";");
    }

    $foruminfo = stripslashes($foruminfo);
    $dalast = '';
    $fmods = '';

    return $foruminfo;
}


function multi($num, $perpage, $page, $mpurl, $strict=false) {

    $multipage = $GLOBALS['lang']['textpages'];

    $pages = quickpage($num, $perpage);

    if ( $pages > 1 ) {
        if($page == 0) {
            if($pages < 4) {
                $to = $pages;
            } else {
                $to = 3;
            }
        } elseif ($page == $pages) {
            $to = $pages;
        } elseif($page == $pages-1) {
            $to = $page+1;
        } elseif($page == $pages-2) {
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

        $string = (strpos($mpurl, '?') !== false) ? '&' : '?';
        if (1 != $page) {
            $multipage .= '&nbsp;&nbsp;<a href="'.$mpurl.$string.'page=1">1</a>';
            if (2 < $from) {
                $multipage .= '&nbsp;&nbsp;..';
            }
        } else {
            $multipage .= '&nbsp;&nbsp;<u><strong>1</strong></u>';
        }

        for($i = $from; $i <= $to; $i++) {
            if ($i != $page) {
                $multipage .= '&nbsp;&nbsp;<a href="'.$mpurl.$string.'page='.$i.'">'.$i.'</a>';
            } else {
                $multipage .= '&nbsp;&nbsp;<u><strong>'.$i.'</strong></u>';
            }
        }

        if ($pages != $page) {
            if (($pages - 1) > $to) {
                $multipage .= '&nbsp;&nbsp;..';
            }
            $multipage .= '&nbsp;&nbsp;<a href="'.$mpurl.$string.'page='.$pages.'">'.$pages.'</a>';
        } else {
            $multipage .= '&nbsp;&nbsp;<u><strong>'.$pages.'</strong></u>';
        }
    }
    elseif($strict !== true) {
        //$multipage .= '&nbsp;&nbsp;<u><strong>1</strong></u>';
        return false;
    }
    return $multipage;
}

function quickpage($things, $thingsperpage) {
    return ((($things > 0) && ($thingsperpage > 0) && ($things > $thingsperpage)) ? ceil($things / $thingsperpage) : 1);
}


function smilieinsert() {
    global $imgdir, $smdir, $table_smilies, $db, $smileyinsert, $smcols, $smtotal;

    $smilies = '';
    if($smileyinsert == 'on' && $smcols != '') {
        $col_smilies = 0;
        $smilies .= '<tr>';
        if($smtotal == 0) {
            $querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='smiley' ORDER BY code DESC") or die($db->error());
        } else {
            $querysmilie = $db->query("SELECT * FROM $table_smilies WHERE type='smiley' ORDER BY code DESC LIMIT 0, $smtotal") or die($db->error());
        }

        while($smilie = $db->fetch_array($querysmilie)) {
            eval("\$smilies .= \"".template("functions_smilieinsert_smilie")."\";");
            $col_smilies += 1;
            if($col_smilies == $smcols) {
                $smilies .= '</tr><tr>';
                $col_smilies = 0;
            }
        }
        $smilies .= '</tr>';
        eval("\$smilieinsert = \"".template("functions_smilieinsert")."\";");
    }
    return $smilieinsert;
}


function noaccess($message) {
    global $css;

    loadtemplates("css");
    eval("\$css = \"".template("css")."\";");

    error($message);
}


function updateforumcount($fid) {
    global $db, $table_posts, $table_forums, $table_threads;

    $postcount = 0;
    $threadcount = 0;

    $pquery = $db->query("SELECT count(pid) FROM $table_posts WHERE fid='$fid'");
    $postcount = $db->result($pquery, 0);

    $tquery = $db->query("SELECT count(tid) FROM $table_threads WHERE (fid='$fid' AND closed != 'moved')");
    $threadcount = $db->result($tquery, 0);

    // Count posts in subforums.
    $queryc = $db->query("SELECT fid FROM $table_forums WHERE fup='$fid'");
    while($children = $db->fetch_array($queryc)) {
        $chquery1 = '';
        $chquery2 = '';
        $chquery1 = $db->query("SELECT count(pid) FROM $table_posts WHERE fid='$children[fid]'");
        $postcount += $db->result($chquery1, 0);

        $chquery2 = $db->query("SELECT count(tid) FROM $table_threads WHERE fid='$children[fid]' AND closed != 'moved'");
        $threadcount += $db->result($chquery2, 0);
    }

    $query = $db->query("SELECT t.lastpost FROM $table_threads t, $table_forums f WHERE (t.fid=f.fid AND f.fid='$fid') OR (t.fid=f.fid AND f.fup='$fid') ORDER BY t.lastpost DESC LIMIT 0,1");
    $lp = $db->fetch_array($query);
    $db->query("UPDATE $table_forums SET posts='$postcount', threads='$threadcount', lastpost='$lp[lastpost]' WHERE fid='$fid'");
}

function updatethreadcount($tid) {
    global $db, $table_threads, $table_posts;

    $query1 = $db->query("SELECT * FROM $table_posts WHERE tid='$tid'");
    $replycount = $db->num_rows($query1);
    $replycount--;
    $query2 = $db->query("SELECT dateline, author FROM $table_posts WHERE tid='$tid' ORDER BY dateline DESC LIMIT 1");
    $lp = $db->fetch_array($query2);
    $lastpost = "$lp[dateline]|$lp[author]";
    $db->query("UPDATE $table_threads SET replies='$replycount', lastpost='$lastpost' WHERE tid='$tid'");
}

function smcwcache() {
    global $db, $table_smilies, $table_words, $smiliecache, $censorcache, $smiliesnum, $wordsnum;
    static $cached;

    if(!$cached) {
        $smiliecache = array();
        $censorcache = array();

        $smquery = $db->query("SELECT * FROM $table_smilies WHERE type='smiley'");
        $smiliesnum = $db->num_rows($smquery);
        $wquery = $db->query("SELECT * FROM $table_words");
        $wordsnum = $db->num_rows($wquery);

        if($smiliesnum > 0) {
            while($smilie = $db->fetch_array($smquery)) {
                $code = $smilie['code'];
                $smiliecache[$code] = $smilie['url'];
            }
        }
        if($wordsnum > 0) {
            while($word = $db->fetch_array($wquery)) {
                $find = $word['find'];
                $censorcache[$find] = $word['replace1'];
            }
        }
        $cached = true;
        return true;
    } else {
        return false;
    }
}

function checkInput($input, $striptags='no', $allowhtml='no', $word='', $no_quotes=true){
    // Function generously donated by FiXato

    $input = trim($input);
    if($striptags == 'yes'){
        $input = strip_tags($input);
    }

    if($allowhtml != 'yes' && $allowhtml != 'on'){
        if($no_quotes){
            $input = htmlspecialchars($input, ENT_NOQUOTES);
        }else{
            $input = htmlspecialchars($input, ENT_QUOTES);
        }
    }
    if($word != '') {
        $input = str_replace($word, "_".$word, $input);
    }

    return $input;
}

function checkOutput($output, $allowhtml='no', $word=''){

    $output = trim($output);
    if($allowhtml == 'yes' || $allowhtml == 'on'){
        $output = htmlspecialchars_decode($output);
    }
    if($word != '') {
        $output = str_replace($word, "_".$word, $output);
    }

    return $output;
}

function htmlspecialchars_decode($string, $type=ENT_QUOTES){

    $array = array_flip(get_html_translation_table(HTML_SPECIALCHARS, $type));
    return strtr($string, $array);
}

function htmlentities_decode($string, $type=ENT_QUOTES){

    $array = array_flip(get_html_translation_table(HTML_ENTITIES, $type));
    return strtr($string, $array);
}

function end_time() {
    global $footerstuff;

    extract($GLOBALS);

    $mtime2 = explode(" ", microtime());
    $endtime = $mtime2[1] + $mtime2[0];

    $totaltime = ($endtime - $starttime);

    $footer_options = explode('-', $SETTINGS['footer_options']);

    if ( X_ADMIN && in_array('serverload', $footer_options) ) {
        $load = ServerLoad();
        if ( ! empty($load) ) {
            eval("\$footerstuff['load'] = \"".template('footer_load')."\";");
        } else {
            $footerstuff['load'] = '';
        }
    }else{
        $footerstuff['load'] = '';
    }
    if(in_array('queries', $footer_options)){
        $querynum = $db->querynum;
        eval("\$footerstuff['querynum'] = \"".template('footer_querynum')."\";");
    }else{
        $footerstuff['querynum'] = '';
    }
    if(in_array('phpsql', $footer_options)){
        $db_duration = number_format(($db->duration/$totaltime)*100, 1);
        $php_duration = number_format((1-($db->duration/$totaltime))*100, 1);
        eval("\$footerstuff['phpsql'] = \"".template('footer_phpsql')."\";");
    }else{
        $footerstuff['phpsql'] = '';
    }

    if(in_array('loadtimes', $footer_options) && X_ADMIN){
        $totaltime = number_format($totaltime, 7);
        eval("\$footerstuff['totaltime'] = \"".template('footer_totaltime')."\";");
    }else{
        $footerstuff['totaltime'] = '';
    }

    if($self['status'] == 'Super Administrator' && DEBUG ) {
        $stuff = array();

        $stuff[] = '<table cols="2" style="width: 97%;"><tr><td style="width: 2em;">#</td><td style="width: 8em;">Duration:</td><td>Query:</td></tr>';
        foreach($db->querylist as $key=>$val){
            $val = mysql_syn_highlight($val);
            $stuff[] = '<tr><td><strong>'.++$key.'.</strong></td><td>'.number_format($db->querytimes[$key-1], 8).'</td><td>'.$val.'</td></tr>';
        }
        $stuff[] = '</table>';
        $footerstuff['querydump'] = implode("\n", $stuff);
    }else{
        $footerstuff['querydump'] = '';
    }

    return $footerstuff;
}

function pwverify($pass='', $url, $fid){
    global $self, $cookiepath, $cookiedomain;

    if(trim($pass) != '' && $_COOKIE['fidpw'.$fid] != $pass && $self['status'] != 'Super Administrator'){
        if($_POST['pw'] != $pass){
            extract($GLOBALS);
            eval('$pwform = "'.template('forumdisplay_password').'";');
            error(((isset($_POST['pw'])) ? $lang['invalidforumpw'] : $lang['forumpwinfo']), false, '', $pwform, false, true, false, true);
        }else{
            put_cookie("fidpw$fid", $pass, (time() + (86400*30)), $cookiepath, $cookiedomain);
            redirect($url, 0);
        }
        exit();
    } else {
        return true;
    }
}

function redirect($path, $timeout=2, $type=X_REDIRECT_HEADER){

    // split session attack code fix from phpBB. Thanks guys :)
    if (strpos(urldecode($path), "\n") !== false || strpos(urldecode($path), "\r") !== false)
    {
        error('Tried to redirect to potentially insecure url.');
    }

    // force session to be written before redirecting
    session_write_close();

    $type = (headers_sent() || $type == X_REDIRECT_JS ) ? X_REDIRECT_JS : X_REDIRECT_HEADER;
    if ($type == X_REDIRECT_JS) {
        ?>
        <script>
        function redirect(){
            window.location.replace("<?php echo $path?>");
        }

        setTimeout("redirect();", <?php echo ($timeout*1000)?>);
        </script>

        <?
    } else {
        if($timeout == 0) {
            header("Location: $path");
        } else {
            header("Refresh: $timeout; URL=./$path");
        }
    }
    return true;
}

function postperm($forums, $type) {
    global $lang, $self, $whopost1, $whopost2;
    static $cache;

    $pperm = explode('|', $forums['postperm']);

    if(!isset($cache[$forums['fid']])) {
        switch($pperm[0]) {
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

        switch($pperm[1]) {
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

    if($self['status'] == 'Super Administrator') {
        return true;
    }

    // $private = VIEW permission
    // $pperm[0] = THREAD permission
    // $pperm[1] = REPLY permission

    $perm = ($type == 'thread') ? $pperm[0] : $pperm[1];

    switch ($forums['private']) {
        case 1: // all can view
            // first, we always check for the password and userlist here.
            $fplen = isset($forums['password']) ? strlen($forums['password']) : 0;
            $fulen = isset($forums['userlist']) ? strlen($forums['userlist']) : 0;

            if ( ($fplen > 1 || $fulen > 1 )
                    && privfcheck($forums['private'], $forums['userlist']) ) {
                return true;
            }

            if (!X_STAFF) {
                // only permissions for 1 allowed
                if($perm == 1) {
                    return true;
                }
                break;
            }

        case 3:
            if (!X_ADMIN) {
                // mods/supermods
                if ($perm == 1 || $perm == 3) {
                    return true;
                }
                break;
            }

        case 2:
            // admin only
            if($perm < 4) {
                return true;
            }
            break;

        case 4:
            // none can see
            return false;
            break;

        default:
            // none can see
            return false;
            break;
    }
    return false;
}


function get_extension($filename){
    $a = explode('.', $filename);
    $count = count($a);
    if($count == 1){
        return '';
    }else{
        return $a[$count-1];
    }
}

function get_attached_file($file, $attachstatus, $max_size=1000000) {
    global $lang, $filename, $filetype, $filesize;

    $filename = '';
    $filetype = '';
    $filesize = 0;

    if($file['name'] != 'none' && !empty($file['name']) && $attachstatus != 'off' && is_uploaded_file($file['tmp_name'])) {
        $attachment = addslashes(fread( fopen($file['tmp_name'], 'rb'), filesize($file['tmp_name'])));
        $file['size'] = filesize($file['tmp_name']);    // fix bad filesizes

        if($file['size'] > $max_size) {
            error($lang['attachtoobig'], false, '', '', false, false, false, false);
            return false;
        }else{
            $filename = $file['name'];
            $filetype = $file['type'];
            $filesize = $file['size'];

            if($filesize == 0) {
                return false;
            } else {
                return $attachment;
            }
        }
    }else{
        return false;
    }
}

function ServerLoad() {
    if($stats = @exec('uptime')) {
        $parts = explode(',', $stats);
        $count = count($parts);

        $first = explode(' ', $parts[$count-3]);
        $c = count($first);
        $first = $first[$c-1];

        return array($first, $parts[$count-2], $parts[$count-1]);
    }else{
        return array();
    }
}

function error($msg, $showheader=true, $prepend='', $append='', $redirect=false, $die=true, $return_as_string=false, $showfooter=true) {
    global $footerstuff;
    extract($GLOBALS);
    $args = func_get_args();

    $message    = (isset($args[0]) ? $args[0] : '');
    $showheader = (isset($args[1]) ? $args[1] : true);
    $prepend    = (isset($args[2]) ? $args[2] : '');
    $append     = (isset($args[3]) ? $args[3] : '');
    $redirect   = (isset($args[4]) ? $args[4] : false);
    $die        = (isset($args[5]) ? $args[5] : true);
    $return_str = (isset($args[6]) ? $args[6] : false);
    $showfooter = (isset($args[7]) ? $args[7] : true);

    nav();
    $navigation = nav($lang['error']);

    end_time();

    if($redirect !== false){
        redirect($redirect, 1);
    }

    if($showheader === false){
        $header = '';
    }else{
        eval("\$header = stripslashes(\"".template('header')."\");");
    }

    eval("\$error = \"".template('error')."\";");
    if($showfooter === true) {
        eval("\$footer = stripslashes(\"".template('footer')."\");");
    }else{
        $footer = '';
    }

    if($return_str !== false){
        $return = $prepend . $error . $footer . $append;
    }else{
        echo $prepend . $error . $footer . $append;
        $return = '';
    }

    if($die) {
        exit();
    }
    return $return;
}

function array_keys2keys($array, $translator){  // changes array(0=>'two', 1=>'six') & array(0=>2,1=>6) to array(2=>'two', 6=>'six');
    $new_array = array();

    foreach($array as $key=>$val){
        if(isset($translator[$key])){
            $new_key = $translator[$key];
        }else{
            $new_key = $key;
        }
        $new_array[$new_key] = $val;
    }

    return $new_array;
}

function mysql_syn_highlight($query){
    global $tables, $tablepre;

    $find       = array();
    $replace    = array();

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

    // temporary

    foreach($find as $key=>$val){
        $replace[$key] = '</em><strong>'.$val.'</strong><em>';
    }

    //$query = preg_replace('#([A-Za-Z0-9]?)[\d]?=\'(.*?)\'#i', '<strong>\1</strong> = </em>\2<em>', $query);
    return '<em>'.str_replace($find, $replace, $query).'</em>';
    //return str_replace('</em><strong></em><strong>IN</strong><em>SERT </em><strong>IN</strong><em>TO</strong><em>', '</em><strong>INSERT INTO</strong><em>', $return);
}

function dump_query($resource, $header=true){
    global $altbg2, $altbg1, $db;
    if(!$db->error()){
        $count = $db->num_fields($resource);
        if($header){
            ?><tr bgcolor="<?php echo $altbg2?>" align="center" class="header"><?php
            for($i=0;$i<$count;$i++){
                echo '<td align="left">';
                echo '<u><strong>'.$db->field_name($resource, $i).'</strong></u>';
                echo '</td>';
            }
            echo '</tr>';
        }

        while($a = $db->fetch_array($resource, SQL_NUM)){
            ?><tr bgcolor="<?php echo $altbg1?>" class="ctrtablerow"><?php
            for($i=0;$i<$count;$i++){
                echo '<td align="left">';

                if(trim($a[$i]) == ''){
                    echo '&nbsp;';
                }else{
                    echo nl2br($a[$i]);
                }
                echo '</td>';
            }
            echo '</tr>';
        }
    }else{
        error($db->error());
    }
}

function put_cookie($name, $value=null, $expire=null, $path=null, $domain=null, $secure=null) {

    if(!headers_sent()){
        return setcookie($name, $value, $expire, $path, $domain, $secure);
    } else {
        if($expire >= 0) {
            // need to make an RFC date!
            $expire = date('r', $expire);
        } else {
            $expire = null;
        }
        ?>
        <script type="text/javascript">
            function setcookie(name, value="deleted", expire=0, path="", domain="", secure=0) {
                if(expire == 0) {
                    var now = new Date();
                    expire = now.toGMTString();
                }

                if(path == "") {
                    path = window.location.pathname;
                }

                if(domain == "") {
                    domain = window.location.host;
                }

                // create cookie string (expire in GMT TIME!)
                var cookie = '';
                cookie = name+"="+value+"; expires="+expire+"; path="+path+"; domain="+domain+"; secure="+secure";";
                document.cookie += cookie;
            }
            setcookie(<?php echo $name?>, <?php echo $value?>, <?php echo $expire?>, <?php echo $path?>, <?php echo $domain?>, <?php echo $secure?>);
        </script>
        <?php
        return true;
    }
}

function audit($user='', $action, $fid, $tid, $reason='') {
    global $xmbuser, $db, $table_logs;

    if ($user == '') {
        $user = $xmbuser;
    }

    $db->query("INSERT $table_logs
                (tid, username, action, fid, date)
                VALUES ('$tid', '$user', '$action', '$fid', " . $db->time() . ")");

    return true;
}

?>