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

define('X_SCRIPT', 'misc.php');

require 'header.php';

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
    case 'smilies':
        nav($lang['smilies']);
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        error($lang['textnoaction']);
        break;
}

$misc = $multipage = $nextlink = '';

switch($action) {
    case 'login':
        $password = '';
        $invisible = formInt('hide');
        if ($invisible == 2) { // '2' may be set explicitly when we want to ignore this input.
            $invisible = NULL;
        } else {
            $invisible = ($invisible == 1);
        }
        if (X_MEMBER) {
            eval('$misc = "'.template('misc_feature_not_while_loggedin').'";');
        } elseif (noSubmit('loginsubmit')) {
            eval('$misc = "'.template('misc_login').'";');
        } elseif (empty($_POST['password'])) {
            eval('$misc = "'.template('misc_login_incorrectdetails').'";');
            eval('$misc .= "'.template('misc_login').'";');
        } elseif (loginUser(postedVar('username'), md5($_POST['password']), $invisible, (formYesNo('secure') == 'yes'))) {
            if ($server == 'Mic') {
                $misc = message($lang['onlinelogin'], FALSE, '', '', $full_url, FALSE, TRUE, FALSE);
            } else {
                redirect($full_url, 0);
            }
        } else {
            if ($self['status'] == "Banned") {
                error($lang['bannedmessage']);
            } else {
                eval('$misc = "'.template('misc_login_incorrectdetails').'";');
                eval('$misc .= "'.template('misc_login').'";');
            }
        }
        break;

    case 'logout':
        if (X_GUEST) {
            redirect($full_url, 0);
            break;
        }

        $query = $db->query("DELETE FROM ".X_PREFIX."whosonline WHERE username='$xmbuser'");

        put_cookie("xmbuser", '', 0, $cookiepath, $cookiedomain);
        put_cookie("xmbpw", '', 0, $cookiepath, $cookiedomain);

        foreach($_COOKIE as $key=>$val) {
            if (preg_match('#^fidpw([0-9]+)$#', $key)) {
                put_cookie($key, '', 0, $cookiepath, $cookiedomain);
            }
        }

        redirect($full_url, 0);
        break;

    case 'search':
        $newurl = preg_replace('/[^\x20-\x7e]/', '', $url);
        if (substr($newurl, -22) == 'misc.php?action=search') {
            $newurl = substr($newurl, 0, -22).'search.php';
        } else {
            $newurl = str_replace('misc.php?action=search&', 'search.php?', $newurl);
        }
        if ($newurl == $url) { // Unexpected query string.
            $newurl = str_replace('&action=search', '', $newurl);
            $newurl = str_replace('/misc', '/search', $newurl);
        }
        $newurl = substr($full_url, 0, -strlen($cookiepath)).$newurl;
        header('HTTP/1.0 301 Moved Permanently');
        header('Location: '.$newurl);
        exit;

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
        } else {
            $username = postedVar('username');
            if (strlen($username) < 3) {
                error($lang['badinfo']);
            }
            $email = postedVar('email');
            $query = $db->query("SELECT username, email, pwdate, langfile, status FROM ".X_PREFIX."members WHERE username='$username' AND email='$email'");
            if ($db->num_rows($query) != 1) {
                error($lang['badinfo']);
            }
            $member = $db->fetch_array($query);
            $db->free_result($query);
            if ($member['status'] == 'Banned') {
                error($lang['bannedmessage']);
            }

            $time = $onlinetime - 86400;
            if ($member['pwdate'] > $time) {
                error($lang['lostpw_in24hrs']);
            }

            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
            $newpass = '';
            mt_srand((double)microtime() * 1000000);
            $get = strlen($chars) - 1;
            for($i = 0; $i < 13; $i++) {
                $newpass .= $chars[mt_rand(0, $get)];
            }
            $newmd5pass = md5($newpass);

            $db->query("UPDATE ".X_PREFIX."members SET password='$newmd5pass', pwdate='".$onlinetime."' WHERE username='$member[username]' AND email='$member[email]'");

            $lang2 = loadPhrases(array('charset','textyourpw','textyourpwis','textusername','textpassword'));
            $translate = $lang2[$member['langfile']];
            $emailuname = htmlspecialchars_decode($member['username'], ENT_QUOTES);
            $emailaddy = htmlspecialchars_decode($member['email'], ENT_QUOTES);
            $rawbbname = htmlspecialchars_decode($bbname, ENT_NOQUOTES);
            $headers = array();
            $headers[] = smtpHeaderFrom($rawbbname, $adminemail);
            $headers[] = 'X-Mailer: PHP';
            $headers[] = 'X-AntiAbuse: Board servername - '.$cookiedomain;
            $headers[] = 'X-AntiAbuse: Username - '.$emailuname;
            $headers[] = 'Content-Type: text/plain; charset='.$translate['charset'];
            $headers = implode("\r\n", $headers);
            altMail($emailaddy, '['.$rawbbname.'] '.$translate['textyourpw'], "{$translate['textyourpwis']} \n\n{$translate['textusername']} $emailuname\n{$translate['textpassword']} $newpass\n\n$full_url", $headers);

            $misc .= '<span class="mediumtxt"><center>'.$lang['emailpw'].'</span></center><br />';
            $misc .= '<script>function redirect() {window.location.replace("index.php");}setTimeout("redirect();", 1250);</script>';
        }
        break;

    case 'online':
        require ROOT.'include/online.inc.php';

        if ($SETTINGS['whosonlinestatus'] == 'off') {
            header('HTTP/1.0 403 Forbidden');
            eval('echo "'.template('header').'";');
            eval('echo "'.template('misc_feature_notavailable').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        }

        $count = $db->result($db->query("SELECT COUNT(*) FROM ".X_PREFIX."whosonline"), 0);
        $mpage = multipage($count, $tpp, 'misc.php?action=online');
        $multipage =& $mpage['html'];
        if (strlen($mpage['html']) != 0) {
            if (X_ADMIN) {
                eval('$multipage = "'.template('misc_online_multipage_admin').'";');
            } else {
                eval('$multipage = "'.template('misc_online_multipage').'";');
            }
        }

        $where = "WHERE username != 'xguest123'";
        if (!X_ADMIN) {
            $where .= " AND (invisible='0' OR username='$xmbuser')";
        }

        // UNION Syntax Reminder: "Use of ORDER BY for individual SELECT statements implies nothing about the order in which the rows appear."
        $sql = "SELECT username, 1 AS sort_col, MAX(ip) AS ip, MAX(`time`) as `time`, MAX(location) AS location, MAX(invisible) AS invisible "
             . "FROM ".X_PREFIX."whosonline $where GROUP BY username, sort_col "
             . "UNION ALL "
             . "SELECT username, 2 AS sort_col, ip, `time`, location, invisible "
             . "FROM ".X_PREFIX."whosonline WHERE username = 'xguest123' "
             . "ORDER BY sort_col, username, `time` DESC "
             . "LIMIT {$mpage['start']}, $tpp";
        $query = $db->query($sql);

        $onlineusers = '';
        while($online = $db->fetch_array($query)) {
            $array = url_to_text($online['location']);
            $onlinetime = gmdate ($timecode, $online['time'] + ($timeoffset * 3600) + ($addtime * 3600));
            $username = str_replace('xguest123', $lang['textguest1'], $online['username']);

            $online['location'] = shortenString($array['text'], 80, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...');
            if (X_STAFF) {
                $online['location'] = '<a href="'.$array['url'].'">'.shortenString($array['text'], 80, X_SHORTEN_SOFT|X_SHORTEN_HARD, '...').'</a>';
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
                $online['invisible'] = '';
                $online['ip'] = '';
                eval('$onlineusers .= "'.template('misc_online_row').'";');
            }
        }
        $db->free_result($query);

        if (X_ADMIN) {
            eval('$misc = "'.template('misc_online_admin').'";');
        } else {
            eval('$misc = "'.template('misc_online').'";');
        }

        break;

    case 'onlinetoday':
        if ($SETTINGS['whosonlinestatus'] == 'off' || $SETTINGS['onlinetoday_status'] == 'off') {
            header('HTTP/1.0 403 Forbidden');
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
        break;

    case 'list':
        if ($SETTINGS['memliststatus'] == 'off') {
            header('HTTP/1.0 403 Forbidden');
            eval('echo "'.template('header').'";');
            eval('echo "'.template('misc_feature_notavailable').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        }


        /* Validate All Inputs */

        $order = postedVar('order', '', FALSE, FALSE, FALSE, 'g');
        $desc = postedVar('desc', '', FALSE, FALSE, FALSE, 'g');
        $page = getInt('page');
        $dblikemem = $db->like_escape(postedVar('srchmem', '', TRUE, FALSE, FALSE, 'g'));
        $dblikeemail = $db->like_escape(postedVar('srchemail', '', TRUE, FALSE, TRUE, 'g'));
        $dblikeip = $db->like_escape(postedVar('srchip', '', TRUE, FALSE, TRUE, 'g'));

        if (strtolower($desc) != 'desc') {
            $desc = 'asc';
        }

        if ($order != 'username' && $order != 'postnum' && $order != 'status' && $order != 'location') {
            $order = '';
            $orderby = 'regdate';
        } else if ($order == 'status') {
            $orderby = "if (status='Super Administrator',1, if (status='Administrator', 2, if (status='Super Moderator', 3, if (status='Moderator', 4, if (status='Member', 5, if (status='Banned',6,7))))))";
        } else {
            $orderby = $order;
        }

        if (!X_ADMIN) {
            $dblikeip = '';
            $dblikeemail = '';
            $misc_mlist_template = 'misc_mlist';
        } else {
            $misc_mlist_template = 'misc_mlist_admin';
        }

        $where = array();
        $ext = array();

        if ($desc != 'asc') {
            $ext[] = "desc=$desc";
        }

        if ($order != '') {
            $ext[] = 'order='.$order;
        }

        if ($dblikeemail != '') {
            if (!X_SADMIN) {
                $where[] = " email LIKE '%$dblikeemail%'";
                $where[] = " showemail='yes'";
            } else {
                $where[] = " email LIKE '%$dblikeemail%'";
            }
            $ext[] = 'srchemail='.rawurlencode(postedVar('srchemail', '', FALSE, FALSE, FALSE, 'g'));
            $srchemail = postedVar('srchemail', 'javascript', TRUE, FALSE, TRUE, 'g');
            /* Warning: $srchemail is used for template output */
        } else {
            $srchemail = '';
        }

        if ($dblikeip != '') {
            $where[] = " regip LIKE '%$dblikeip%'";
            $ext[] = 'srchip='.rawurlencode(postedVar('srchip', '', FALSE, FALSE, FALSE, 'g'));
            $srchip = postedVar('srchip', 'javascript', TRUE, FALSE, TRUE, 'g');
            /* Warning: $srchip is used for template output */
        } else {
            $srchip = '';
        }

        if ($dblikemem != '') {
            $where[] = " username LIKE '%$dblikemem%'";
            $ext[] = 'srchmem='.rawurlencode(postedVar('srchmem', '', FALSE, FALSE, FALSE, 'g'));
            $srchmem = postedVar('srchmem', 'javascript', TRUE, FALSE, TRUE, 'g');
            /* Warning: $srchmem is used for template output */
        } else {
            $srchmem = '';
        }

        if (count($ext) > 0) {
            $params = '&amp;'.implode('&amp;', $ext);

            if ($ext[0] == 'desc=desc') {
                array_shift($ext);
                $sflip = '';
            } else {
                $sflip = '&amp;desc=desc';
            }
            if (count($ext) > 0) {
                if (substr($ext[0], 0, 6) == 'order=') {
                    $sflip .= '&amp;'.array_shift($ext);
                }
            }
            if (count($ext) > 0) {
                $ext = '&amp;'.implode('&amp;', $ext);
            } else {
                $ext = '';
            }
        } else {
            $params = '';
            $sflip = '&amp;desc=desc';
            $ext = '';
        }

        $where[] = " lastvisit!=0 ";
        $q = implode(' AND', $where);
        $num = $db->result($db->query("SELECT COUNT(uid) FROM ".X_PREFIX."members WHERE $q"), 0);
        $canonical = 'misc.php?action=list';
        $baseurl = $canonical.$params;
        $mpage = multipage($num, $memberperpage, $baseurl, $canonical);
        $multipage =& $mpage['html'];
        if (strlen($mpage['html']) != 0) {
            eval('$multipage = "'.template('misc_mlist_multipage').'";');
        }
        unset($num, $where);


        /* Generate Output */

        $querymem = $db->query("SELECT * FROM ".X_PREFIX."members WHERE $q ORDER BY $orderby $desc LIMIT {$mpage['start']}, $memberperpage");

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

        if (strtolower($desc) == 'desc') {
            $ascdesc = $lang['asc'];
        } else {
            $ascdesc = $lang['desc'];
        }
        eval('$memlist = "'.template($misc_mlist_template).'";');
        $misc = $memlist;
        break;

    case 'smilies':
        $header = '';
        eval('$css = "'.template('css').'";');
        eval('$header = "'.template('popup_header').'";');
        eval('$footer = "'.template('popup_footer').'";');
        $smilies = smilieinsert('full');
        eval('$misc = "'.template('misc_smilies').'";');
        echo $header;
        echo $misc;
        echo $footer;
        exit();
        break;

    case 'captchaimage':
        if ($SETTINGS['captcha_status'] == 'off') {
            header('HTTP/1.0 403 Forbidden');
            eval('echo "'.template('header').'";');
            eval('echo "'.template('misc_feature_notavailable').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        }
        require ROOT.'include/captcha.inc.php';
        header('X-Robots-Tag: noindex');
        $oPhpCaptcha = new Captcha();
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
echo $header, $misc, $footer;
?>
