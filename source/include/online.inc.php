<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2012, The XMB Group
 * http://www.xmbforum2.com/
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
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

function url_to_text($url) {
    global $db, $lang, $self, $xmbuser, $SETTINGS;
    static $fname, $tsub;
    static $restrict = '';

    if ($restrict == '') {
        $fids = permittedForums(forumCache(), 'thread', 'csv');
        if (strlen($fids) == 0) {
            $restrict = ' FALSE';
        } else {
            $restrict = ' f.fid IN('.$fids.')';
        }
    }

    if (false !== strpos($url, '/viewthread.php')) {
        $temp = explode('?', $url);
        if (count($temp) > 1) {
            $tid = 0;
            if (!empty($temp[1])) {
                $urls = explode('&', $temp[1]);
                foreach($urls as $key=>$val) {
                    if (strpos($val, 'tid') !== false) {
                        $tid = (int) substr($val, 4);
                    }
                }
            }

            $location = $lang['onlinenothread'];
            if (isset($tsub[$tid])) {
                $location = $lang['onlineviewthread'].' '.$tsub[$tid];
            } else {
                $query = $db->query("SELECT t.fid, t.subject FROM ".X_PREFIX."forums f, ".X_PREFIX."threads t WHERE $restrict AND f.fid=t.fid AND t.tid='$tid'");
                while($locate = $db->fetch_array($query)) {
                    $location = $lang['onlineviewthread'].' '.rawHTMLsubject(stripslashes($locate['subject']));
                    $tsub[$tid] = $locate['subject'];
                }
                $db->free_result($query);
            }
        } else {
            $location = $lang['onlinenothread'];
        }
    } else if (false !== strpos($url, '/forumdisplay.php')) {
        $temp = explode('?', $url);
        if (count($temp) > 1) {
            $fid = 0;
            $urls = explode('&', $temp[1]);
            if (!empty($temp[1])) {
                foreach($urls as $key=>$val) {
                    if (strpos($val, 'fid') !== false) {
                        $fid = (int) substr($val, 4);
                    }
                }
            }

            $location = $lang['onlinenoforum'];
            if (isset($fname[$fid])) {
                $location = $lang['onlineforumdisplay'].' '.$fname[$fid];
            } else {
                $locate = getForum($fid);
                $perms = checkForumPermissions($locate);
                if ($SETTINGS['hideprivate'] == 'off' || $locate['type'] == 'group' || $perms[X_PERMS_VIEW]) {
                    $location = $lang['onlineforumdisplay'].' '.fnameOut($locate['name']);
                    $fname[$fid] = $locate['name'];
                }
            }
        } else {
            $location = $lang['onlinenoforum'];
        }
    } else if (false !== strpos($url, "/memcp.php")) {
        if (false !== strpos($url, 'action=profile')) {
            $location = $lang['onlinememcppro'];
        } else if (false !== strpos($url, 'action=subscriptions')) {
            $location = $lang['onlinememcpsub'];
        } else if (false !== strpos($url, 'action=favorites')) {
            $location = $lang['onlinememcpfav'];
        } else {
            $location = $lang['onlinememcp'];
        }
    } else if (false !== strpos($url, '/cp.php') || false !== strpos($url, '/cp2.php')) {
        $location = $lang['onlinecp'];
        if (!X_ADMIN) {
            $url = 'index.php';
        }
    } else if (false !== strpos($url, '/editprofile.php')) {
        $location = $lang['onlinecp'];
        if (!X_SADMIN) {
            $url = 'index.php';
        }
    } else if (false !== strpos($url, '/faq.php')) {
        $location = $lang['onlinefaq'];
    } else if (false !== strpos($url, '/index.php')) {
        if (false !== strpos($url, 'gid=')) {
            $temp = explode('?', $url);
            $gid = (int) str_replace('gid=', '', $temp[1]);
            $cat = getForum($gid);
            if ($cat === FALSE) {
                $location = $lang['onlinecatunknown'];
            } elseif ($cat['type'] != 'group') {
                $location = $lang['onlinecatunknown'];
            } else {
                $location = $lang['onlineviewcat'].fnameOut($cat['name']);
            }
        } else {
            $location = $lang['onlineindex'];
        }
    } else if (false !== strpos($url, '/member.php')) {
        if (false !== strpos($url, 'action=reg')) {
            $location = $lang['onlinereg'];
        } else if (false !== strpos($url, 'action=viewpro')) {
            $location = $lang['onlinenoprofile']; // initialize
            $temp = explode('?', $url);
            $urls = explode('&', $temp[1]);
            if (isset($urls[1]) && !empty($urls[1]) && $urls[1] != 'member=') {
                foreach($urls as $argument) {
                    if (strpos($argument, 'member=') !== false) {
                        $member = str_replace('member=', '', $argument);
                        $member = rawurldecode(str_replace('+', ' ', $member));
                        $member = preg_replace('#[\]\'\x00-\x1F\x7F<>\\\\|"[,@]#', '', $member);
                        $member = cdataOut(censor($member));
                        eval('$location = "'.$lang['onlineviewpro'].'";');
                        break;
                    }
                }
            }
        } else if (false !== strpos($url, 'action=coppa')) {
            $location = $lang['onlinecoppa'];
        } else {
            $location = $lang['onlineunknown'];
        }
    } else if (false !== strpos($url, 'misc.php')) {
        if (false !== strpos($url, 'login')) {
            $location = $lang['onlinelogin'];
        } else if (false !== strpos($url, 'logout')) {
            $location = $lang['onlinelogout'];
        } else if (false !== strpos($url, 'lostpw')) {
            $location = $lang['onlinelostpw'];
        } else if (false !== strpos($url, 'online')) {
            $location = $lang['onlinewhosonline'];
        } else if (false !== strpos($url, 'onlinetoday')) {
            $location = $lang['onlineonlinetoday'];
        } else if (false !== strpos($url, 'list')) {
            $location = $lang['onlinememlist'];
        } else if (false !== strpos($url, 'captchaimage')) {
            $location = $lang['onlinereg'];
        } else {
            $location = $lang['onlineunknown'];
        }
    } else if (false !== strpos($url, '/post.php')) {
        if (false !== strpos($url, 'action=edit')) {
            $location = $lang['onlinepostedit'];
        } else if (false !== strpos($url, 'action=newthread')) {
            $location = $lang['onlinepostnewthread'];
        } else if (false !== strpos($url, 'action=reply')) {
            $location = $lang['onlinepostreply'];
        } else {
            $location = $lang['onlineunknown'];
        }
    } else if (false !== strpos($url, '/search.php')) {
        $location = $lang['onlinesearch'];
    } else if (false !== strpos($url, '/stats.php')) {
        $location = $lang['onlinestats'];
    } else if (false !== strpos($url, '/today.php')) {
        $location = $lang['onlinetodaysposts'];
    } else if (false !== strpos($url, '/tools.php')) {
        $location = $lang['onlinetools'];
    } else if (false !== strpos($url, '/topicadmin.php')) {
        $location = $lang['onlinetopicadmin'];
    } else if (false !== strpos($url, '/u2u.php')) {
        if (false !== strpos($url, 'action=send')) {
            $location = $lang['onlineu2usend'];
        } else if (false !== strpos($url, 'action=delete')) {
            $location = $lang['onlineu2udelete'];
        } else if (false !== strpos($url, 'action=ignore') || false !== strpos($url, 'action=ignoresubmit')) {
            $location = $lang['onlineu2uignore'];
        } else if (false !== strpos($url, 'action=view')) {
            $location = $lang['onlineu2uview'];
        } else if (false !== strpos($url, 'action=folders') || false !== strpos($url, 'folder=')) {
            $location = $lang['onlinemanagefolders'];
        } else {
            $location = $lang['onlineu2uint'];
        }

        if (!X_SADMIN) {
            $url = './u2u.php';
        }
    } else if (false !== strpos($url, '/buddy.php')) {
        if (false !== strpos($url, 'action=add2u2u')) {
            $location = $lang['onlinebuddyadd2u2u'];
        } else if (false !== strpos($url, 'action=add')) {
            $location = $lang['onlinebuddyadd'];
        } else if (false !== strpos($url, 'action=edit')) {
            $location = $lang['onlinebuddyedit'];
        } else if (false !== strpos($url, 'action=delete')) {
            $location = $lang['onlinebuddydelete'];
        } else {
            $location = $lang['onlinebuddy'];
        }
    } else {
        $location = $lang['onlineindex'];
    }

    $return = array();
    $return['url'] = attrOut($url, 'javascript');
    $return['text'] = $location;
    return $return;
}

return;
?>
