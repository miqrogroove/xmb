<?php
/**
 * eXtreme Message Board
 * XMB 1.9.8 Engage Final SP3
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

define('X_SCRIPT', 'misc.php');

require 'header.php';
require ROOT.'include/online.inc.php';

loadtemplates(
'functions_smilieinsert',
'functions_smilieinsert_smilie',
'misc_feature_not_while_loggedin',
'misc_feature_notavailable',
'misc_login_incorrectdetails',
'misc_login',
'misc_lostpw',
'misc_mlist',
'misc_mlist_admin',
'misc_mlist_multipage',
'misc_mlist_results_none',
'misc_mlist_row',
'misc_mlist_row_email',
'misc_mlist_row_site',
'misc_mlist_separator',
'misc_online',
'misc_online_admin',
'misc_online_multipage',
'misc_online_multipage_admin',
'misc_online_row',
'misc_online_row_admin',
'misc_online_today',
'misc_search',
'misc_search_nextlink',
'misc_search_results',
'misc_search_results_none',
'misc_search_results_row',
'misc_smilies',
'popup_footer',
'popup_header'
);

smcwcache();
eval('$css = "'.template('css').'";');

$action = postedVar('action', '', FALSE, FALSE, FALSE, 'g');
switch($action) {
    case 'login':
        nav($lang['textlogin']);
        break;
    case 'logout':
        nav($lang['textlogout']);
        break;
    case 'search':
        nav($lang['textsearch']);
        break;
    case 'lostpw':
        nav($lang['textlostpw']);
        break;
    case 'online':
        nav($lang['whosonline']);
        break;
    case 'list':
        nav($lang['textmemberlist']);
        break;
    case 'onlinetoday':
        nav($lang['whosonlinetoday']);
        break;
    case 'captchaimage':
        nav($lang['textregister']);
        break;
    default:
        nav($lang['error']);
        break;
}

$misc = $multipage = $nextlink = '';

switch($action) {
    case 'login':
        if (X_MEMBER) {
            eval('echo "'.template('header').'";');
            eval('echo "'.template('misc_feature_not_while_loggedin').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        }

        if (noSubmit('loginsubmit')) {
            eval('$misc = "'.template('misc_login').'";');
            $misc = stripslashes($misc);
        } else {
            $password = md5($_POST['password']);
            $usernameinput = postedVar('username');
            $query = $db->query("SELECT username FROM ".X_PREFIX."members WHERE username='$usernameinput' AND password='$password'");
            if ($query && $db->num_rows($query) == 1) {
                $member = $db->fetch_array($query);
                $db->free_result($query);
                $db->query("DELETE FROM ".X_PREFIX."whosonline WHERE ip='$onlineip' && username='xguest123'");
                $currtime = $onlinetime + (86400*30);
                $username = $member['username'];

                if (formInt('hide')) {
                    $db->query("UPDATE ".X_PREFIX."members SET invisible='1' WHERE username='$usernameinput'");
                } else {
                    $db->query("UPDATE ".X_PREFIX."members SET invisible='0' WHERE username='$usernameinput'");
                }

                if ($server == 'Mic') {
                    $misc = '<script>
                        function put_cookie(name, value, expires, path, domain, secure) {
                            var curCookie = name + "=" + escape(value) +
                            ((expires) ? "; expires=" + expires.toGMTString() : "") +
                            ((path) ? "; path=" + path : "") +
                            ((domain) ? "; domain=" + domain : "") +
                            ((secure) ? "; secure" : "");
                            document.cookie = curCookie;
                        }

                        var now = new Date();
                        now.setTime(now.getTime() + 365 * 24 * 60 * 60 * 1000);

                        put_cookie("xmbuser", "'.$username.'", now, "'.$cookiepath.'", "'.$cookiedomain.'");
                        put_cookie("xmbpw", "'.$password.'", now, "'.$cookiepath.'", "'.$cookiedomain.'");

                        window.location="index.php";
                    </script>';
                } else {
                    $secure = formYesNo('secure');
                    if ($secure == 'yes') {
                        put_cookie("xmbuser", $username, NULL, $cookiepath, $cookiedomain);
                        put_cookie("xmbpw", $password, NULL, $cookiepath, $cookiedomain);
                    } else {
                        put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
                        put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
                    }

                    redirect('index.php', 0);
                    $misc = '';
                }
            } else {
                eval('echo "'.template('header').'";');
                eval('echo "'.template('misc_login_incorrectdetails').'";');
                end_time();
                eval('echo "'.template('footer').'";');
                exit();
            }
        }
        break;

    case 'logout':
        if (X_GUEST) {
            redirect("index.php", 0);
            break;
        }

        $currtime = $onlinetime - (86400*30);
        $query = $db->query("DELETE FROM ".X_PREFIX."whosonline WHERE username='$xmbuser'");

        put_cookie("xmbuser", '', 0, $cookiepath, $cookiedomain);
        put_cookie("xmbpw", '', 0, $cookiepath, $cookiedomain);

        foreach($_COOKIE as $key=>$val) {
            if (preg_match('#^fidpw([0-9]+)$#', $key)) {
                put_cookie($key, '', 0, $cookiepath, $cookiedomain);
            }
        }

        redirect('index.php', 0);
        break;

    case 'search':
        if ($SETTINGS['searchstatus'] == 'off') {
            eval('echo "'.template('header').'";');
            eval('echo "'.template('misc_feature_notavailable').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        }

        $srchfrom = intval(getRequestVar('srchfrom'));
        $srchtxt = addslashes(getRequestVar('srchtxt'));
        $srchuname = cdataOut(stripslashes(getRequestVar('srchuname')));
        $srchfid = getRequestInt('srchfid');
        if (empty($srchfid)) {
            $srchfid = 'all';
        }

        $filter_distinct = getRequestVar('filter_distinct');
        $filter_distinct = valYesNo($filter_distinct);

        $page = getInt('page');
        if ($page < 1) {
            $page = 1;
        }

        validatePpp();

        $searchresults = '';

        if (noSubmit('searchsubmit')) {
            $forumselect = forumList('srchfid', FALSE, TRUE);
            eval('$search = "'.template('misc_search').'";');
            $misc = stripslashes($search);
        } else {
            if (strlen($srchuname) < 3 && strlen($srchtxt) < 3) {
                error($lang['nosearchq']);
            }

            if (strlen($srchuname) < 3) {
                $srchuname = '';
            }

            if (strlen($srchtxt) < 3) {
                $srchtxt = '';
            }

            if (onSubmit('searchsubmit')) {
                $offset = ($page-1) * $ppp;
                $start = $offset;
                $end = $ppp;

                $sql = "SELECT p.*, t.tid AS ttid, t.subject AS tsubject, f.fup AS fup, f.type AS type, f.fid, f.private AS fprivate, f.userlist AS fuserlist, f.password AS password FROM ".X_PREFIX."posts p, ".X_PREFIX."threads t LEFT JOIN ".X_PREFIX."forums f ON f.fid=t.fid WHERE p.tid=t.tid";

                if ($srchfrom == 0) {
                    $srchfrom = $onlinetime;
                    $srchfromold = 0;
                } else {
                    $srchfromold = $srchfrom;
                }

                $ext = array();

                $srchfrom = $onlinetime - (int) $srchfrom;
                if ($srchtxt) {
                    $srchtxtsq = addslashes(str_replace(array('%', '_'), array('\%', '\_'), $srchtxt));
                    $sql .= " AND (p.message LIKE '%$srchtxtsq%' OR p.subject LIKE '%$srchtxtsq%' OR t.subject LIKE '%$srchtxtsq')";
                    $ext[] = 'srchtxt='.$srchtxt;
                }

                if ($srchuname != '') {
                    $sql .= " AND p.author='".$db->escape($srchuname)."'";
                    $ext[] = 'srchuname='.recodeOut($srchuname);
                }

                if ($srchfid != "all" && $srchfid != "") {
                    $sql .= " AND p.fid='".(int)$srchfid."'";
                    $ext[] = 'srchfid='.((int) $srchfid);
                }

                if ($srchfrom) {
                    $sql .= " AND p.dateline >= '$srchfrom'";
                    $ext[] = 'srchfrom'.((int)$srchfromold);
                }

                $sql .=" GROUP BY dateline ORDER BY dateline DESC LIMIT $start,$end";
                $pagenum = $page + 1;

                $querysrch = $db->query($sql);
                $results = $db->num_rows($querysrch);

                if ($srchuname) {
                    $srchtxt = '\0';
                }

                if ($filter_distinct == 'yes') {
                    $temparray = array();
                    $searchresults = '';
                    while($post = $db->fetch_array($querysrch)) {
                        $fidpw = isset($_COOKIE['fidpw'.$post['fid']]) ? $_COOKIE['fidpw'.$post['fid']] : '';
                        $authorization = privfcheck($post['fprivate'], $post['fuserlist']);
                        if (($post['password'] != '' && $post['password'] != $fidpw) && !X_SADMIN) {
                            continue;
                        }

                        if (isset($post['type']) && $post['type'] == 'sub') {
                            $query = $db->query("SELECT private, userlist, name, fid FROM ".X_PREFIX."forums WHERE fid='$post[fup]'");
                            $fup = $db->fetch_array($query);
                            if (!privfcheck($fup['private'], $fup['userlist'])) {
                                continue;
                            }
                            $db->free_result($query);
                        }

                        if ($authorization) {
                            if (!array_key_exists($post['ttid'], $temparray)) {
                                $tid = $post['ttid'];
                                $temparray[$tid] = true;

                                if ($post['status'] == 'Banned') {
                                    $post['message'] = $lang['bannedpostmsg'];
                                }

                                $message = $post['message'];

                                $srchtxt = str_replace(array('_ ', ' _','% ', ' %'), '', $srchtxt);
                                $position = strpos($message, $srchtxt, 0);

                                $show_num = 100;
                                $msg_leng = strlen($message);

                                if ($position <= $show_num) {
                                    $min = 0;
                                    $add_pre = '';
                                } else {
                                    $min = $position - $show_num;
                                    $add_pre = '...';
                                }

                                if (($msg_leng - $position) <= $show_num) {
                                    $max = $msg_leng;
                                    $add_post = '';
                                } else {
                                    $max = $position + $show_num;
                                    $add_post = '...';
                                }

                                $show = substr($message, $min, $max);
                                $show = preg_replace("/($srchtxt)/i", '<strong><em>\\0</em></strong>', $show);
                                $show = postify($show, 'no', 'yes', 'yes', 'no', 'no', 'no');

                                $date = gmdate($dateformat, $post['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));
                                $time = gmdate($timecode, $post['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));
                                $poston = $date.' '.$lang['textat'].' '.$time;
                                $postby = $post['author'];

                                $post['tsubject'] = html_entity_decode(stripslashes(censor($post['tsubject'])));
                                if (trim($post['subject']) == '') {
                                    $post['subject'] = $post['tsubject'];
                                } else {
                                    $post['subject'] = html_entity_decode($post['subject']);
                                }

                                $post['subject'] = censor($post['subject']);

                                if ($post['posts'] > $ppp) {
                                    $pbefore = $db->result($db->query("SELECT COUNT(pid) FROM ".X_PREFIX."posts WHERE tid = '".$post['ttid']."' AND pid < '".$post['pid']."'"), 0);
                                    $page = ceil(($pbefore+1)/$ppp);
                                } else {
                                    $page = 1;
                                }
                                eval('$searchresults .= "'.template('misc_search_results_row').'";');
                            }
                        }
                    }
                    $db->free_result($querysrch);
                } else {
                    while($post = $db->fetch_array($querysrch)) {
                        $fidpw = isset($_COOKIE['fidpw'.$post['fid']]) ? $_COOKIE['fidpw'.$post['fid']] : '';
                        $authorization = privfcheck($post['fprivate'], $post['fuserlist']);

                        if (($post['password'] != '' && $post['password'] != $fidpw) && !X_SADMIN) {
                            continue;
                        }

                        if (isset($post['type']) && $post['type'] == 'sub') {
                            $query = $db->query("SELECT private, userlist, name, fid FROM ".X_PREFIX."forums WHERE fid='$post[fup]'");
                            $fup = $db->fetch_array($query);
                            if (!privfcheck($fup['private'], $fup['userlist'])) {
                                continue;
                            }
                            $db->free_result($query);
                        }

                        if ($authorization) {
                            $tid = $post['ttid'];
                            if ($post['status'] == 'Banned') {
                                $post['message'] = $lang['bannedpostmsg'];
                            }

                            $message = $post['message'];

                            $srchtxt = str_replace(array('_ ', ' _','% ', ' %'), '', $srchtxt);

                            $position = 0;
                            if (!empty($srchtxt)) {
                                $position = strpos($message, $srchtxt, 0);
                            }

                            $show_num = 100;
                            $msg_leng = strlen($message);

                            if ($position <= $show_num) {
                                $min = 0;
                                $add_pre = '';
                            } else {
                                $min = $position - $show_num;
                                $add_pre = '...';
                            }

                            if (($msg_leng - $position) <= $show_num) {
                                $max = $msg_leng;
                                $add_post = '';
                            } else {
                                $max = $position + $show_num;
                                $add_post = '...';
                            }

                            $show = substr($message, $min, $max);
                            $show = preg_replace("/($srchtxt)/i", '<strong><em>\\0</em></strong>', $show);
                            $show = postify($show, 'no', 'yes', 'yes', 'no', 'no', 'no');

                            $date = gmdate($dateformat, $post['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));
                            $time = gmdate($timecode, $post['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));
                            $poston = $date.' '.$lang['textat'].' '.$time;
                            $postby = $post['author'];

                            $post['tsubject'] = stripslashes(censor($post['tsubject']));
                            if (trim($post['subject']) == '') {
                                $post['subject'] = html_entity_decode($post['tsubject']);
                            } else {
                                $post['tsubject'] = html_entity_decode($post['subject']);
                            }

                            if ($post['posts'] > $ppp) {
                                $pbefore = $db->result($db->query("SELECT COUNT(pid) FROM ".X_PREFIX."posts WHERE tid = '".$post['ttid']."' AND pid < '".$post['pid']."'"), 0);
                                $page = ceil(($pbefore+1)/$ppp);
                            } else {
                                $page = 1;
                            }
                            eval('$searchresults .= "'.template('misc_search_results_row').'";');
                        }
                    }
                    $db->free_result($querysrch);
                }
            }

            if ($results == 0) {
                eval('$searchresults = "'.template('misc_search_results_none').'";');
            } else if ($results == $ppp) {
                $ext = htmlspecialchars(implode('&', $ext));
                eval('$nextlink = "'.template('misc_search_nextlink').'";');
            }

            eval('$search = "'.template('misc_search_results').'";');
            $misc = stripslashes($search);
        }
        break;

    case 'lostpw':
        if (X_MEMBER) {
            eval('echo "'.template('header').'";');
            eval('echo "'.template('misc_feature_not_while_loggedin').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        }

        if (noSubmit('lostpwsubmit')) {
            eval('$misc = "'.template('misc_lostpw').'";');
            $misc = stripslashes($misc);
        } else {
            $username = postedVar('username');
            $email = postedVar('email');
            $query = $db->query("SELECT username, email, pwdate FROM ".X_PREFIX."members WHERE username='$username' AND email='$email'");
            $member = $db->fetch_array($query);
            $db->free_result($query);

            $time = $onlinetime-86400;
            if ($member['pwdate'] > $time) {
                error($lang['lostpw_in24hrs']);
            }

            if (!$member['username']) {
                error($lang['badinfo']);
            }

            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
            $newpass = '';
            mt_srand((double)microtime() * 1000000);
            $max = mt_rand(8, 12);
            for ($get=strlen($chars), $i=0; $i < $max; $i++) {
                $newpass .= $chars[mt_rand(0, $get)];
            }
            $newmd5pass = md5($newpass);

            $db->query("UPDATE ".X_PREFIX."members SET password='$newmd5pass', pwdate='".$onlinetime."' WHERE username='$member[username]' AND email='$member[email]'");

            altMail($member['email'], '['.$bbname.'] '.$lang['textyourpw'], $lang['textyourpwis']."\n\n".$member['username']."\n".$newpass, "From: ".$bbname." <".$adminemail.">");

            $misc .= '<span class="mediumtxt"><center>'.$lang['emailpw'].'</span></center><br />';
            $misc .= '<script>function redirect() {window.location.replace("index.php");}setTimeout("redirect();", 1250);</script>';
        }
        break;

    case 'online':
        if ($SETTINGS['whosonlinestatus'] == 'off') {
            eval('echo "'.template('header').'";');
            eval('echo "'.template('misc_feature_notavailable').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        }

        $page = getInt('page');
        $count = $db->num_rows($db->query("SELECT * FROM ".X_PREFIX."whosonline"));
        $max_page = (int) ($count / $tpp) + 1;
        if ($page && $page >= 1 && $page <= $max_page) {
            $start_limit = ($page-1) * $tpp;
        } else {
            $start_limit = 0;
            $page = 1;
        }

        if (($multipage = multi($count, $tpp, $page, 'misc.php?action=online&amp;page='.$page)) !== false) {
            if (X_ADMIN) {
                eval('$multipage = "'.template('misc_online_multipage_admin').'";');
            } else {
                eval('$multipage = "'.template('misc_online_multipage').'";');
            }
        }

        if (X_ADMIN) {
            $query = $db->query("SELECT * FROM ".X_PREFIX."whosonline ORDER BY username ASC LIMIT $start_limit, $tpp");
        } else {
            $query = $db->query("SELECT * FROM ".X_PREFIX."whosonline WHERE invisible='0' OR (invisible='1' AND username='$xmbuser') ORDER BY username ASC LIMIT $start_limit, $tpp");
        }

        $onlineusers = '';
        while($online = $db->fetch_array($query)) {
            $array = url_to_text($online['location']);
            $onlinetime = gmdate ($timecode, $online['time'] + ($timeoffset * 3600) + ($addtime * 3600));
            $username = str_replace('xguest123', $lang['textguest1'], $online['username']);

            $online['location'] = shortenString($array['text'], 80, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...');
            if (X_STAFF) {
                $online['location'] = '<a href="'.$array['url'].'">'.shortenString($array['text'], 80, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...').'</a>';
                $online['location'] = stripslashes($online['location']);
            }

            if ($online['invisible'] == 1 && (X_ADMIN || $online['username'] == $xmbuser)) {
                $hidden = ' ('.$lang['hidden'].')';
            } else {
                $hidden = '';
            }

            if (X_SADMIN && $online['username'] != 'xguest123' && $online['username'] != $lang['textguest1']) {
                $online['username'] = '<a href="member.php?action=viewpro&amp;member='.recodeOut($online['username']).'">'.$username.'</a>'.$hidden;
            } else {
                $online['username'] = $username;
            }

            if (X_ADMIN) {
                eval('$onlineusers .= "'.template('misc_online_row_admin').'";');
            } else {
                eval('$onlineusers .= "'.template('misc_online_row').'";');
            }
        }
        $db->free_result($query);

        if (X_ADMIN) {
            eval('$misc = "'.template('misc_online_admin').'";');
        } else {
            eval('$misc = "'.template('misc_online').'";');
        }

        $misc = stripslashes($misc);
        break;

    case 'onlinetoday':
        if ($SETTINGS['whosonlinestatus'] == 'off' || $SETTINGS['onlinetoday_status'] == 'off') {
            eval('echo "'.template('header').'";');
            eval('echo "'.template('misc_feature_notavailable').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        }

        $datecut = $onlinetime - (3600 * 24);
        if (X_ADMIN) {
            $query = $db->query("SELECT username, status FROM ".X_PREFIX."members WHERE lastvisit >= '$datecut' ORDER BY username ASC");
        } else {
            $query = $db->query("SELECT username, status FROM ".X_PREFIX."members WHERE lastvisit >= '$datecut' AND invisible != '1' ORDER BY username ASC");
        }

        $todaymembersnum = 0;
        $todaymembers = array();
        $pre = $suff = '';
        while($memberstoday = $db->fetch_array($query)) {
            $pre = '<span class="status_'.str_replace(' ', '_', $memberstoday['status']).'">';
            $suff = '</span>';
            $todaymembers[] = '<a href="member.php?action=viewpro&amp;member='.recodeOut($memberstoday['username']).'">'.$pre.''.$memberstoday['username'].''.$suff. '</a>';
            ++$todaymembersnum;
        }
        $todaymembers = implode(', ', $todaymembers);
        $db->free_result($query);

        if ($todaymembersnum == 1) {
            $memontoday = $todaymembersnum.$lang['textmembertoday'];
        } else {
            $memontoday = $todaymembersnum.$lang['textmemberstoday'];
        }
        eval('$misc = "'.template('misc_online_today').'";');
        $misc = stripslashes($misc);
        break;

    case 'list':
        $order = postedVar('order', '', FALSE, FALSE, FALSE, 'g');
        $desc = postedVar('desc', '', FALSE, FALSE, FALSE, 'g');
        $page = getInt('page');
        $srchmem = postedVar('srchmem', '', TRUE, FALSE);
        $srchemail = postedVar('srchemail');
        $srchip = postedVar('srchip');

        if ($SETTINGS['memliststatus'] == 'off') {
            eval('echo "'.template('header').'";');
            eval('echo "'.template('misc_feature_notavailable').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        }

        if (strtolower($desc) != 'desc') {
            $desc = 'asc';
        }

        $result = $db->result($db->query("SELECT COUNT(uid) FROM ".X_PREFIX."members WHERE lastvisit!=0"), 0);
        $max_page = (int) ($result / $memberperpage) + 1;
        if ($page && $page >= 1 && $page <= $max_page) {
            $start_limit = ($page-1) * $SETTINGS['memberperpage'];
        } else {
            $start_limit = 0;
            $page = 1;
        }

        if ($order != 'username' && $order != 'postnum' && $order != 'status') {
            $orderby = "uid";
            $order = 'uid';
        } else if ($order == 'status') {
            $orderby = "if (status='Super Administrator',1, if (status='Administrator', 2, if (status='Super Moderator', 3, if (status='Moderator', 4, if (status='member', 5, if (status='banned',6,7))))))";
        } else {
            $orderby = $order;
        }

        if (!X_ADMIN) {
            $srchip = '';
            $srchemail = '';
            $misc_mlist_template = 'misc_mlist';
            $where = array();
        } else {
            $where = array();
            $misc_mlist_template = 'misc_mlist_admin';
        }

        $ext = array('&order='.$order);

        if ($srchemail) {
            if (!X_SADMIN) {
                $where[] = " email LIKE '%".$srchemail."%'";
                $where[] = " showemail='yes'";
            } else {
                $where[] = " email LIKE '%".$srchemail."%'";
            }
            $ext[] = 'srchemail='.rawurlencode($_POST['srchemail']);
            $srchemail = postedVar('srchemail', 'javascript', TRUE, FALSE);
            /* Warning: $srchemail is used for template output */
        } else {
            $srchemail = '';
        }

        if ($srchip) {
            $where[] = " regip LIKE '%".$srchip."%'";
            $ext[] = 'srchip='.rawurlencode($_POST['srchip']);
            $srchip = postedVar('srchip', 'javascript', TRUE, FALSE);
            /* Warning: $srchip is used for template output */
        } else {
            $srchip = '';
        }

        if ($srchmem) {
            $where[] = " username LIKE '%".addslashes(str_replace(array('%', '_'), array('\%', '\_'), $srchmem))."%'";
            $ext[] = 'srchmem='.rawurlencode($_POST['srchmem']);
            $srchmem = postedVar('srchmem', 'javascript', TRUE, FALSE);
            /* Warning: $srchmem is used for template output */
        } else {
            $srchmem = '';
        }

        $where[] = " lastvisit!=0 ";

        $q = implode(' AND', $where);
        $querymem = $db->query("SELECT * FROM ".X_PREFIX."members WHERE $q ORDER BY $orderby $desc LIMIT $start_limit, $memberperpage");
        $num = $db->result($db->query("SELECT COUNT(uid) FROM ".X_PREFIX."members WHERE $q"), 0);

        $ext = implode('&amp;', $ext);

        $adjTime = ($timeoffset * 3600) + ($addtime * 3600);

        $replace = array('http://', 'https://', 'ftp://');
        $members = $oldst = '';
        if ($db->num_rows($querymem) == 0) {
            eval('$members = "'.template('misc_mlist_results_none').'";');
        } else {
            while($member = $db->fetch_array($querymem)) {
                $member['regdate'] = gmdate($dateformat, $member['regdate'] + $adjTime);

                if (X_MEMBER && $member['email'] != '' && $member['showemail'] == 'yes') {
                    eval('$email = "'.template('misc_mlist_row_email').'";');
                } else {
                    $email = '';
                }

                $member['site'] = str_replace($replace, '', $member['site']);
                $member['site'] = "http://$member[site]";

                if ($member['site'] == "http://") {
                    $site = '';
                } else {
                    eval('$site = "'.template('misc_mlist_row_site').'";');
                }

                if ($member['location'] != '') {
                    $member['location'] = censor($member['location']);
                } else {
                    $member['location'] = '';
                }

                $memurl = recodeOut($member['username']);
                if ($order == 'status') {
                    if ($oldst != $member['status']) {
                        $oldst = $member['status'];
                        $seperator_text = (trim($member['status']) == '' ? $lang['onlineother'] : $member['status']);
                        eval('$members .= "'.template('misc_mlist_separator').'";');
                    }
                }
                eval('$members .= "'.template('misc_mlist_row').'";');
            }
            $db->free_result($querymem);
        }

        if (!isset($memberperpage)) {
            $memberperpage = $postperpage;
        }

        $mpurl = 'misc.php?action=list&amp;desc='.$desc.''.$ext;
        if (($multipage = multi($num, $memberperpage, $page, $mpurl)) === false) {
            $multipage = '';
        } else {
            eval('$multipage = "'.template('misc_mlist_multipage').'";');
        }

        if ($desc == 'desc') {
            $init['ascdesc'] = 'asc';
            $ascdesc = $lang['asc'];
        } else {
            $init['ascdesc'] = 'desc';
            $ascdesc = $lang['desc'];
        }
        eval('$memlist = "'.template($misc_mlist_template).'";');
        $misc = stripslashes($memlist);
        break;

    case 'smilies':
        $header = '';
        eval('$css = "'.template('css').'";');
        eval('$header = "'.template('popup_header').'";');
        eval('$footer = "'.template('popup_footer').'";');
        $smtotal = 0;
        $smilies = smilieinsert();
        eval('$misc = "'.template('misc_smilies').'";');
        echo $header;
        echo $misc;
        echo $footer;
        exit();
        break;

    case 'captchaimage':
        require ROOT.'include/captcha.inc.php';
        $oPhpCaptcha = new Captcha(250, 50);
        $imagehash = postedVar('imagehash', '', FALSE, TRUE, FALSE, 'g');
        $oPhpCaptcha->Create($imagehash);
        exit();
        break;

    default:
        error($lang['textnoaction']);
        break;
}

eval('$header = "'.template('header').'";');
end_time();
eval('$footer = "'.template('footer').'";');
echo stripslashes($header.$misc.$footer);
?>
