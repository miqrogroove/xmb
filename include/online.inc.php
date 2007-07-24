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

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

function url_to_text($url) {
    global $db, $lang, $self, $xmbuser, $location;
    static $restrict, $rset, $fname, $tsub;

    if (!$rset) {
        $modXmbuser = str_replace(array('*', '.', '+'), array('\*', '\.', '\+'), $xmbuser);
        $restrict = array("(password='')");
        switch ($self['status']) {
            case 'Member':
                $restrict[] = 'private = 1';
                $restrict[] = "(userlist = '' OR userlist REGEXP '(^|(,))([:space:])*$modXmbuser([:space:])*((,)|$)')";
                break;
            case 'Moderator':
            case 'Super Moderator':
                $restrict[] = '(private = 1 OR private = 3)';
                $restrict[] = "(if ((private=1 AND userlist != ''), if ((userlist REGEXP '(^|(,))([:space:])*$modXmbuser([:space:])*((,)|$)'), 1, 0), 1))";
                break;
            case 'Administrator':
                $restrict[] = '(private > 0 AND private < 4)';
                $restrict[] = "(if ((private=1 AND userlist != ''), if ((userlist REGEXP '(^|(,))([:space:])*$modXmbuser([:space:])*((,)|$)'), 1, 0), 1))";
                break;
            case 'Super Administrator':
                break;
            default:
                $restrict[] = '(private=1)';
                $restrict[] = "(userlist='')";
                break;
        }
        $restrict = implode(' AND ', $restrict);
        $rset = true;
    }

    if (false !== strpos($url, 'tid') && false === strpos($url, "/post.php")) {
        $temp = explode('?', $url);
        $urls = explode('&', $temp[1]);
        foreach ($urls as $key=>$val) {
            if (strpos($val, 'tid') !== false) {
                $tid = (int) substr($val, 4);
            }
        }

        if (isset($tsub[$tid])) {
            $location = $lang['onlineviewthread'].' '.$tsub[$tid];
        } else {
            $query = $db->query("SELECT t.fid, t.subject FROM ".X_PREFIX."forums f, ".X_PREFIX."threads t WHERE $restrict AND f.fid=t.fid AND t.tid='$tid'");
            while ($locate = $db->fetch_array($query)) {
                $location = $lang['onlineviewthread'].' '.censor($locate['subject']);
                $tsub[$tid] = $locate['subject'];
            }
            $db->free_result($query);
        }

        if (false !== strpos($url, 'action=attachment')) {
            $url = substr($url, 0, strpos($url, '?'));
            $url .= '?tid='.$tid;
        }
    } else if (false !== strpos($url, 'fid')  && false === strpos($url, "/post.php")) {
        $temp = explode('?', $url);
        $urls = explode('&', $temp[1]);
        foreach ($urls as $key=>$val) {
            if (strpos($val, 'fid') !== false) {
                $fid = (int) substr($val, 4);
            }
        }

        if (isset($fname[$fid])) {
            $location = $lang['onlineforumdisplay'].' '.$fname[$fid];
        } else {
            $query = $db->query("SELECT name FROM ".X_PREFIX."forums f WHERE $restrict AND f.fid='$fid'");
            while ($locate = $db->fetch_array($query)) {
                $location = $lang['onlineforumdisplay'].' '.$locate['name'];
                $fname[$fid] = $locate['name'];
            }
            $db->free_result($query);
        }
    } else if (false !== strpos($url, "/memcp.php")) {
        $location = $lang['onlinememcp'];
    } else if (false !== strpos($url, "/cp.php") || false !== strpos($url, "/cp2.php")) {
        $location = $lang['onlinecp'];
        if (!X_ADMIN) {
            $url = 'index.php';
        }
    } else if (false !== strpos($url, "/editprofile.php")) {
        if (!X_SADMIN) {
            $url = 'index.php';
        }
        $location = $lang['onlineeditprofile'];
    } else if (false !== strpos($url, "/faq.php")) {
        $location = $lang['onlinefaq'];
    } else if (false !== strpos($url, "/index.php")) {
        $location = $lang['onlineindex'];
    } else if (false !== strpos($url, "/member.php")) {
        if (false !== strpos($url, 'action=reg')) {
            $location = $lang['onlinereg'];
        } else if (false !== strpos($url, 'action=viewpro')) {
            $temp = explode('?', $url);
            $urls = explode('&', $temp[1]);
            foreach ($urls as $argument) {
                if (strpos($argument, 'member') !== false) {
                    $member = str_replace('member=', '', $argument);
                }
            }
            eval("\$location = \"$lang[onlineviewpro]\";");
        } else if (false !== strpos($url, 'action=coppa')) {
            $location = $lang['onlinecoppa'];
        }
    } else if (false !== strpos($url, "misc.php")) {
        if (false !== strpos($url, 'login')) {
            $location = $lang['onlinelogin'];
        } elseif (false !== strpos($url, 'logout')) {
            $location = $lang['onlinelogout'];
        } else if (false !== strpos($url, 'search')) {
            $location = $lang['onlinesearch'];
        } else if (false !== strpos($url, 'lostpw')) {
            $location = $lang['onlinelostpw'];
        } else if (false !== strpos($url, 'online')) {
            $location = $lang['onlinewhosonline'];
        } else if (false !== strpos($url, 'onlinetoday')) {
            $location = $lang['onlineonlinetoday'];
        } else if (false !== strpos($url, 'list')) {
            $location = $lang['onlinememlist'];
        }
    } else if (false !== strpos($url, "/post.php")) {
        if (false !== strpos($url, 'action=edit')) {
            $location = $lang['onlinepostedit'];
        } else if (false !== strpos($url, 'action=newthread')) {
            $location = $lang['onlinepostnewthread'];
        } else if (false !== strpos($url, 'action=reply')) {
            $location = $lang['onlinepostreply'];
        }
    } else if (false !== strpos($url, "/stats.php")) {
        $location = $lang['onlinestats'];
    } elseif (false !== strpos($url, "/today.php")) {
        $location = $lang['onlinetodaysposts'];
    } else if (false !== strpos($url, "/tools.php")) {
        $location = $lang['onlinetools'];
    } else if (false !== strpos($url, "/topicadmin.php")) {
        $location = $lang['onlinetopicadmin'];
    } else if (false !== strpos($url, "/u2u.php")) {
        if (false !== strpos($url, 'action=send')) {
            $location = $lang['onlineu2usend'];
        } else if (false !== strpos($url, 'action=delete')) {
            $location = $lang['onlineu2udelete'];
        } else if (false !== strpos($url, 'action=ignore') || false !== strpos($url, 'action=ignoresubmit')) {
            $location = $lang['onlineu2uignore'];
        } else if (false !== strpos($url, 'action=view')) {
            $location = $lang['onlineu2uview'];
        }

        if (!X_SADMIN) {
            $url = './u2u.php';
        }
    } else {
        $location = $url;
    }

    $location = trim($location);
    if ($location == '') {
        $url = 'about:none';
    } else {
        $location = str_replace('%20', '&nbsp;', $location);
    }

    $url = addslashes($url);
    $return = array();
    $return['url'] = checkInput($url, 'yes');
    $return['text'] = $location;

    return $return;
}
?>