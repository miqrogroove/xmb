<?php
/**
 * XMB 1.9.9 Saigo
 *
 * Developed by the XMB Group Copyright (c) 2001-2008
 * Sponsored by iEntry Inc. Copyright (c) 2007
 *
 * http://xmbgroup.com , http://ientry.com
 *
 * This software is released under the GPL License, you should
 * have received a copy of this license with the download of this
 * software. If not, you can obtain a copy by visiting the GNU
 * General Public License website <http://www.gnu.org/licenses/>.
 *
 **/

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

function url_to_text($url) {
    global $db, $lang, $self, $xmbuser;
    static $restrict, $rset, $fname, $tsub;

    if (!$rset) {
        if (X_SADMIN) {
            $q = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE status = 'on'");
            while($f = $db->fetch_array($q)) {
                $fids[] = $f['fid'];
            }
        } else {
            $fCache = array();
            $q = $db->query("SELECT fid, postperm, userlist, password, type, fup FROM ".X_PREFIX."forums WHERE status = 'on' AND type != 'group' ORDER BY type ASC");
            while($forum = $db->fetch_array($q)) {
                $perms = checkForumPermissions($forum);
                $fCache[$forum['fid']] = $perms;

                if($perms[X_PERMS_VIEW] && $perms[X_PERMS_USERLIST] && $perms[X_PERMS_PASSWORD]) {
                    if($forum['type'] == 'sub') {
                        // also check above forum!
                        $parentP = $fCache[$forum['fup']];
                        if($parentP[X_PERMS_VIEW] && $parentP[X_PERMS_USERLIST] && $parentP[X_PERMS_PASSWORD]) {
                            $fids[] = $forum['fid'];
                        }
                    } else {
                        $fids[] = $forum['fid'];
                    }
                }
            }
        }

        $fids = implode(',', $fids);
        $restrict = ' f.fid IN('.$fids.')';

        $rset = true;
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
                    $location = $lang['onlineviewthread'].' '.censor($locate['subject']);
                    $tsub[$tid] = $locate['subject'];
                }
                $db->free_result($query);
            }

            if (false !== strpos($url, 'action=attachment')) {
                $url = substr($url, 0, strpos($url, '?'));
                $url .= '?tid='.$tid;
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
                $query = $db->query("SELECT name FROM ".X_PREFIX."forums f WHERE $restrict AND f.fid='$fid'");
                while($locate = $db->fetch_array($query)) {
                    $location = $lang['onlineforumdisplay'].' '.$locate['name'];
                    $fname[$fid] = $locate['name'];
                }
                $db->free_result($query);
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
        $temp = explode('?', $url);
        if (!X_SADMIN) {
            $url = 'index.php';
        }

        if (false!== strpos($temp[1], 'user=')) {
            if (isset($temp[1]) && !empty($temp[1]) && $temp[1] != 'user=') {
                $user = str_replace('user=', '', $temp[1]);
                eval("\$location = \"$lang[onlineeditprofile]\";");
            } else {
                $location = $lang['onlineeditnoprofile'];
            }
        } else {
            $location = $lang['onlineeditnoprofile'];
        }
    } else if (false !== strpos($url, '/faq.php')) {
        $location = $lang['onlinefaq'];
    } else if (false !== strpos($url, '/index.php')) {
        if (false !== strpos($url, 'gid=')) {
            $temp = explode('?', $url);
            $gid = (int) str_replace('gid=', '', $temp[1]);
            $q = $db->query("SELECT name FROM ".X_PREFIX."forums f WHERE $restrict AND f.fid='$gid'");
            $cat = $db->fetch_array($q);
            if (!$cat) {
                $location = $lang['onlinecatunknown'];
            } else {
                $location = $lang['onlineviewcat'].$cat['name'];
            }
        } else {
            $location = $lang['onlineindex'];
        }
    } else if (false !== strpos($url, '/member.php')) {
        if (false !== strpos($url, 'action=reg')) {
            $location = $lang['onlinereg'];
        } else if (false !== strpos($url, 'action=viewpro')) {
            $temp = explode('?', $url);
            $urls = explode('&', $temp[1]);
            if (isset($urls[1]) && !empty($urls[1]) && $urls[1] != 'member=') {
                foreach($urls as $argument) {
                    if (strpos($argument, 'member') !== false) {
                        $member = str_replace('member=', '', $argument);
                    }
                }
                eval("\$location = \"$lang[onlineviewpro]\";");
            } else {
                $location = $lang['onlinenoprofile'];
            }
        } else if (false !== strpos($url, 'action=coppa')) {
            $location = $lang['onlinecoppa'];
        } else {
            $location = $lang['onlinenoprofile'];
        }
    } else if (false !== strpos($url, 'misc.php')) {
        if (false !== strpos($url, 'login')) {
            $location = $lang['onlinelogin'];
        } else if (false !== strpos($url, 'logout')) {
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

    $location = html_entity_decode(str_replace('%20', '&nbsp;', htmlspecialchars($location)));
    $url = addslashes(trim($url));

    $return = array();
    $return['url'] = checkInput($url, 'yes');
    $return['text'] = $location;
    return $return;
}
?>
