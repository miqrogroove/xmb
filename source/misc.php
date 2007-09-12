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

require_once('header.php');
require_once(ROOT.'include/online.inc.php');

loadtemplates(
'functions_smilieinsert',
'functions_smilieinsert_smilie',
'misc_feature_not_while_loggedin',
'misc_feature_notavailable',
'misc_feature_notavailable',
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
'popup_header',
'misc_login_incorrectdetails'
);

smcwcache();
eval('$css = "'.template('css').'";');

$action = getVar('action');
switch ($action) {
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

switch ($action) {
    case 'login':
        if (noSubmit('loginsubmit')) {
            eval('$misc = "'.template('misc_login').'";');
            $misc = stripslashes($misc);
        } else {
            $password = md5(formVar('password'));
            $username = addslashes(formVar('username'));
            $query = $db->query("SELECT username FROM ".X_PREFIX."members WHERE username='$username' AND password='$password'");
            if ($query && $db->num_rows($query) == 1) {
                $member = $db->fetch_array($query);
                $db->query("DELETE FROM ".X_PREFIX."whosonline WHERE ip='$onlineip' && username='xguest123'");
                $currtime = $onlinetime + (86400*30);
                $username = $member['username'];

                if (formInt('hide')) {
                    $db->query("UPDATE ".X_PREFIX."members SET invisible='1' WHERE username='$username'");
                } else {
                    $db->query("UPDATE ".X_PREFIX."members SET invisible='0' WHERE username='$username'");
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
                        put_cookie("xmbuser", $username);
                        put_cookie("xmbpw", $password);
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

        put_cookie("xmbuser", $username, $currtime, $cookiepath, $cookiedomain);
        put_cookie("xmbpw", $password, $currtime, $cookiepath, $cookiedomain);
        put_cookie("xmbuser", '', 0, $cookiepath, $cookiedomain);
        put_cookie("xmbpw", '', 0, $cookiepath, $cookiedomain);

        foreach ($_COOKIE as $key=>$val) {
            $val = addslashes($val);
            if (preg_match('#^fidpw([0-9]+)$#', $key)) {
                put_cookie($key, '');
            }
        }

        redirect("index.php", 0);
        break;

    case 'search':
        if ($SETTINGS['searchstatus'] == 'off') {
            eval('echo "'.template('header').'";');
            eval('echo "'.template('misc_feature_notavailable').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        }

        $searchresults = '';
        $page = getInt('page');

        if (noSubmit('searchsubmit') && !$page) {
            $forumselect = forumList('srchfid', true, true);
            eval('$search = "'.template('misc_search').'";');
            $misc = stripslashes($search);
        } else {
            $srchuname = getVar('srchuname');
            $srchtxt = getVar('srchtxt');
            $srchfid = getInt('srchfid');
            $srchfrom = getInt('srchfrom');
            $filter_distinct = getVar('filter_distinct');
            if ($filter_distinct != 'yes') {
                $filter_distinct = formYesNo('filter_distinct');
            }

            if (!$srchuname) {
                $srchuname = formVar('srchuname');
            }

            if (!$srchtxt) {
                $srchtxt = formVar('srchtxt');
            }

            if (!$srchfid) {
                $srchfid = formInt('srchfid');
            }

            if (!$srchfrom) {
                $srchfrom = formInt('srchfrom');
            }

            if (!$srchfid) {
                $srchfid = 'all';
            }

            if (!$srchuname && !$srchtxt || (strlen($srchuname) < 3 && strlen($srchtxt) < 3)) {
                error($lang['nosearchq']);
            }

            if (!$srchuname || strlen($srchuname) < 3) {
                $srchuname = '';
            }

            if (!$srchtxt || strlen($srchtxt) < 3) {
                $srchtxt = '';
            }

            if (onSubmit('searchsubmit') || $page) {
                if (!$page) {
                    $page = 1;
                    $offset = 0;
                    $start = 0;
                    $end = ((isset($self['ppp']) && $self['ppp'] > 0) ? $self['ppp'] : (isset($SETTINGS['postperpage']) && $SETTINGS['postperpage'] > 0 ? $SETTINGS['postperpage'] : 20));
                } else {
                    if ($page < 1 ) {
                        $page = 1;
                    }

                    $offset = ($page-1) * ((isset($self['ppp']) && $self['ppp'] > 0) ? $self['ppp'] : (isset($SETTINGS['postperpage']) && $SETTINGS['postperpage'] > 0 ? $SETTINGS['postperpage'] : 20));
                    $start = $offset;
                    $end = ((isset($member['ppp']) && $member['ppp'] > 0) ? $member['ppp'] : (isset($SETTINGS['postperpage']) && $SETTINGS['postperpage'] > 0 ? $SETTINGS['postperpage'] : 20));
                }
                $sql = "SELECT count(p.tid), p.*, t.tid AS ttid, t.subject AS tsubject, f.fid, f.private AS fprivate, f.userlist AS fuserlist, f.password AS password FROM ".X_PREFIX."posts p, ".X_PREFIX."threads t LEFT JOIN ".X_PREFIX."forums f ON  f.fid=t.fid WHERE p.tid=t.tid";

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

                if ($srchuname != "") {
                    $sql .= " AND p.author='".addslashes($srchuname)."'";
                    $ext[] = 'srchuname='.$srchuname;
                }

                if ($srchfid != "all" && $srchfid != "") {
                    $sql .= " AND p.fid='$srchfid'";
                    $ext[] = 'srchfid='.$srchfid;
                }

                if ($srchfrom) {
                    $sql .= " AND p.dateline >= '$srchfrom'";
                    $ext[] = 'srchfrom'.$srchfromold;
                }

                $sql .=" GROUP BY dateline ORDER BY dateline DESC LIMIT $start,$end";
                if (!$page || $page < 1) {
                    $pagenum = 2;
                } else {
                    $pagenum = $page+1;
                }

                $querysrch = $db->query($sql);
                $results = 0;
                $results = $db->num_rows($querysrch);

                if ($srchuname) {
                    $srchtxt = '\0';
                }

                if ($filter_distinct == 'yes') {
                    $temparray = array();
                    $searchresults = '';
                    while ($post = $db->fetch_array($querysrch)) {
                        $fidpw = isset($_COOKIE['fidpw'.$post['fid']]) ? $_COOKIE['fidpw'.$post['fid']] : '';
                        $authorization = privfcheck($post['fprivate'], $post['fuserlist']); // private forum check

                        if (($post['password'] != '' && $post['password'] != $fidpw) && !X_SADMIN) {
                            continue;
                        }

                        if ($authorization) {
                            if (!array_key_exists($post['ttid'], $temparray)) {
                                $tid = $post['ttid'];
                                $temparray[$tid] = true;
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
                                $show = str_replace($srchtxt, '<b><i>'.$srchtxt.'</i></b>', $show);
                                $show = postify($show, 'no', 'yes', 'yes', 'no', 'no', 'no');

                                $date = gmdate($dateformat, $post['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));
                                $time = gmdate($timecode, $post['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));
                                $poston = "$date $lang[textat] $time";
                                $postby = $post['author'];

                                $post['tsubject'] = html_entity_decode(stripslashes(censor($post['tsubject'])));
                                if (trim($post['subject']) == '') {
                                    $post['subject'] = $post['tsubject'];
                                } else {
                                    $post['subject'] = html_entity_decode($post['subject']);
                                }

                                $post['subject'] = censor($post['subject']);
                                eval('$searchresults .= "'.template('misc_search_results_row').'";');
                            }
                        }
                    }
                } else {
                    while ($post = $db->fetch_array($querysrch)) {
                        $fidpw = isset($_COOKIE['fidpw'.$post['fid']]) ? $_COOKIE['fidpw'.$post['fid']] : '';
                        $authorization = privfcheck($post['fprivate'], $post['fuserlist']); // private forum check

                        if (($post['password'] != '' && $post['password'] != $fidpw) && !X_SADMIN) {
                            continue;
                        }

                        if ($authorization) {
                            $tid = $post['ttid'];
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
                            $show = str_replace($srchtxt, '<strong><em>'.$srchtxt.'</em></strong>', $show);
                            $show = postify($show, 'no', 'yes', 'yes', 'no', 'no', 'no');

                            $date = gmdate($dateformat, $post['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));
                            $time = gmdate($timecode, $post['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));
                            $poston = "$date $lang[textat] $time";
                            $postby = $post['author'];

                            $post['tsubject'] = stripslashes(censor($post['tsubject']));
                            if (trim($post['subject']) == '') {
                                $post['subject'] = html_entity_decode($post['tsubject']);
                            } else {
                                $post['tsubject'] = html_entity_decode($post['subject']);
                            }
                            eval('$searchresults .= "'.template('misc_search_results_row').'";');
                        }
                    }
                }
            }

            if ($results == 0) {
                eval('$searchresults = "'.template('misc_search_results_none').'";');
            } else if ($results == ((isset($self['ppp']) && $self['ppp'] > 0) ? $self['ppp'] : (isset($SETTINGS['postperpage']) && $SETTINGS['postperpage'] > 0 ? $SETTINGS['postperpage'] : 20))) {
                $ext = htmlspecialchars(implode('&', $ext));
                eval('$nextlink = "'.template('misc_search_nextlink').'";');
            }

            eval('$search = "'.template('misc_search_results').'";');
            $misc = stripslashes($search);
        }
        break;

    case 'lostpw':
        if (noSubmit('lostpwsubmit')) {
            eval('$misc = "'.template('misc_lostpw').'";');
            $misc = stripslashes($misc);
        } else {
            $username = addslashes(formVar('username'));
            $email = addslashes(formVar('email'));
            $query = $db->query("SELECT username, email, pwdate FROM ".X_PREFIX."members WHERE username='$username' AND email='$email'");
            $member = $db->fetch_array($query);

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

        if (X_ADMIN) {
            $query = $db->query("SELECT * FROM ".X_PREFIX."whosonline ORDER BY username ASC");
        } else {
            $query = $db->query("SELECT * FROM ".X_PREFIX."whosonline WHERE invisible = '0' OR (invisible='1' AND username='$xmbuser') ORDER BY username ASC");
        }

        $onlineusers = '';
        while ($online = $db->fetch_array($query)) {
            $array = url_to_text($online['location']);
            $onlinetime = gmdate ($timecode, $online['time'] + ($timeoffset * 3600) + ($addtime * 3600));
            $username = str_replace('xguest123', $lang['textguest1'], $online['username']);

            $online['location'] = $array['text'];
            if (X_STAFF) {
                $online['location'] = '<a href="'.$array['url'].'">'.$array['text'].'</a>';
                $online['location'] = stripslashes($online['location']);
            }

            if ($online['invisible'] == 1 && (X_ADMIN || $online['username'] == $xmbuser)) {
                $hidden = ' ('.$lang['hidden'].')';
            } else {
                $hidden = '';
            }

            if (X_SADMIN && $online['username'] != 'xguest123' && $online['username'] != $lang['textguest1']) {
                $online['username'] = '<a href="member.php?action=viewpro&amp;member='.rawurlencode($online['username']).'">'.$username.'</a>'.$hidden;
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
        if ($SETTINGS['whosonlinestatus'] == 'off') {
            eval('echo "'.template('header').'";');
            eval('echo "'.template('misc_feature_notavailable').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        }

        $datecut = $onlinetime - (3600 * 24);
        if (X_ADMIN) {
            $query = $db->query("SELECT username FROM ".X_PREFIX."members WHERE lastvisit >= '$datecut' ORDER BY username ASC");
        } else {
            $query = $db->query("SELECT username FROM ".X_PREFIX."members WHERE lastvisit >= '$datecut' AND invisible != '1' ORDER BY username ASC");
        }

        $todaymembersnum = 0;
        $todaymembers = array();
        while ($memberstoday = $db->fetch_array($query)) {
            $todaymembers[] = '<a href="member.php?action=viewpro&amp;member='.rawurlencode($memberstoday['username']).'">'.$memberstoday['username'].'</a>';
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
        $order = getVar('order');
        $desc = getVar('desc');
        $page = getInt('page');

        $srchmem = formVar('srchmem');
        $srchemail = formVar('srchemail');
        $srchip = formVar('srchip');

        if ($SETTINGS['memliststatus'] == 'off') {
            eval('echo "'.template('header').'";');
            eval('echo "'.template('misc_feature_notavailable').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        }

        if (!$desc || strtolower($desc) != 'desc') {
            $desc = 'asc';
        }

        if ($page && $page > 0) {
            $start_limit = ($page-1) * $SETTINGS['memberperpage'];
        } else {
            $start_limit = 0;
            $page = 1;
        }

        if (!$order || ($order != "username" && $order != "postnum" && $order != 'status')) {
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
                $where[] = " showemail = 'yes'";
            } else {
                $where[] = " email LIKE '%".$srchemail."%'";
            }
            $ext[] = 'srchemail='.$srchemail;
            $srchemail = htmlspecialchars($srchemail);
        } else {
            $srchemail = '';
        }

        if ($srchip) {
            $where[] = " regip LIKE '%".$srchip."%'";
            $ext[] = 'srchip='.$srchip;
            $srchip = htmlspecialchars($srchip);
        } else {
            $srchip = '';
        }

        if ($srchmem) {
            $where[] = " username LIKE '%".addslashes(str_replace(array('%', '_'), array('\%', '\_'), $srchmem))."%'";
            $ext[] = 'srchmem='.$srchmem;
            $srchmem = htmlspecialchars($srchmem);
        } else {
            $srchmem = '';
        }

        if (isset($where) && isset($where[0]) && $where[0] != '') {
            $q = implode(' AND', $where);
            $querymem = $db->query("SELECT * FROM ".X_PREFIX."members WHERE $q ORDER BY $orderby $desc LIMIT $start_limit, $memberperpage");
            $num = $db->result($db->query("SELECT count(uid) FROM ".X_PREFIX."members WHERE $q"), 0);
        } else {
            $querymem = $db->query("SELECT * FROM ".X_PREFIX."members ORDER BY $orderby $desc LIMIT $start_limit, $memberperpage");
            $num = $db->result($db->query("SELECT count(uid) FROM ".X_PREFIX."members"), 0);
        }

        $ext = htmlspecialchars(implode('&amp;', $ext));

        $adjTime = ($timeoffset * 3600) + ($addtime * 3600);

        $replace = array('http://', 'https://', 'ftp://');
        $members = '';
        $oldst = '';
        if ($db->num_rows($querymem) == 0) {
            eval('$members = "'.template('misc_mlist_results_none').'";');
        } else {
            while ($member = $db->fetch_array($querymem)) {
                $member['regdate'] = gmdate($dateformat, $member['regdate'] + $adjTime );

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

                $memurl = rawurlencode($member['username']);
                if ($order == 'status') {
                    if ($oldst != $member['status']) {
                        $oldst = $member['status'];
                        $seperator_text = (trim($member['status']) == '' ? $lang['onlineother'] : $member['status']);
                        eval('$members .= "'.template('misc_mlist_separator').'";');
                    }
                }
                eval('$members .= "'.template('misc_mlist_row').'";');
            }
        }

        if (!isset($SETTINGS['memberperpage'])) {
            $memberperpage = $postperpage;
        }

        if (($multipage = multi($num, $SETTINGS['memberperpage'], $page, 'misc.php?action=list&amp;desc='.$desc.$ext)) === false) {
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
        require(ROOT.'include/captcha.inc.php');
        $oPhpCaptcha = new Captcha(250, 50);
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