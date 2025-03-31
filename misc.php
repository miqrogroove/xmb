<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace XMB;

require './header.php';

$core = \XMB\Services\core();
$db = \XMB\Services\db();
$login = \XMB\Services\login();
$observer = \XMB\Services\observer();
$session = \XMB\Services\session();
$smile = \XMB\Services\smile();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$tran = \XMB\Services\translation();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;
$SETTINGS = &$vars->settings;

$action = getPhpInput('action', 'g');
switch($action) {
    case 'login':
        $core->nav($lang['textlogin']);
        break;
    case 'logout':
        $core->nav($lang['textlogout']);
        break;
    case 'search':
        break;
    case 'lostpw':
        $core->nav($lang['textlostpw']);
        break;
    case 'online':
        $core->nav($lang['whosonline']);
        break;
    case 'list':
        $core->nav($lang['textmemberlist']);
        break;
    case 'onlinetoday':
        $core->nav($lang['whosonlinetoday']);
        break;
    case 'captchaimage':
        $core->nav($lang['textregister']);
        break;
    case 'smilies':
        $core->nav($lang['smilies']);
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        $core->error($lang['textnoaction']);
        break;
}

$misc = $multipage = $nextlink = '';

switch($action) {
    case 'login':
        if (! $core->coppa_check()) {
            $core->message($lang['coppa_fail']);
        } elseif (noSubmit('loginsubmit')) {
            if (X_MEMBER) {
                $misc = $template->process('misc_feature_not_while_loggedin.php');
            } else {
                $template->token = $token->create('Login', '', $vars::NONCE_FORM_EXP, anonymous: true);
                $session->preLogin($template->token);
                $misc = $template->process('misc_login.php');
            }
        } else {
            switch($session->getStatus()) {
                case 'good':
                    // Set $invisible to true, false, or null.
                    $invisible = formInt('hide');
                    if ($invisible == 2) { // '2' may be set explicitly when we want to ignore this input.
                        $invisible = null;
                    } else {
                        $invisible = ($invisible == 1);
                    }

                    $login->loginUser($invisible);
                    $core->redirect($vars->full_url, timeout: 0);
                    break;
                case 'login-client-disabled':
                    $core->error($lang['cookies_disabled']);
                    break;
                case 'already-logged-in':
                    $misc = $template->process('misc_feature_not_while_loggedin.php');
                    break;
                case 'ip-banned':
                case 'member-banned':
                    $core->error($lang['bannedmessage']);
                    break;
                case 'password-locked':
                    $core->error($lang['login_lockout']);
                    break;
                case 'origin-check-fail':
                    $core->error($lang['bad_token']);
                    break;
                case 'login-no-input':
                case 'bad-password':
                case 'bad-username':
                default:
                    $template->token = $token->create('Login', '', $vars::NONCE_FORM_EXP, anonymous: true);
                    $session->preLogin($template->token);
                    $misc = $template->process('misc_login_incorrectdetails.php') . $template->process('misc_login.php');
                    break;
            }
        }
        break;

    case 'logout':
        if ('logged-out' == $session->getStatus()) {
            $gone = $session->getMember();
            $sql->deleteWhosonline($gone['username']);
            $core->redirect($vars->full_url, timeout: 0);
        } else {
            $core->message($lang['notloggedin']);
        }
        break;

    case 'search':
        $newurl = preg_replace('/[^\x20-\x7e]/', '', $url);
        if (substr($newurl, -22) == 'misc.php?action=search') {
            $newurl = substr($newurl, 0, -22).'search.php';
        } else {
            $newurl = str_replace('misc.php?action=search&', 'search.php?', $newurl);
        }
        if ($newurl === $url) { // Unexpected query string.
            $newurl = str_replace('&action=search', '', $newurl);
            $newurl = str_replace('/misc', '/search', $newurl);
        }
        $newurl = substr($vars->full_url, 0, -strlen($cookiepath)).$newurl;
        header('HTTP/1.0 301 Moved Permanently');
        header('Location: '.$newurl);
        exit;

        break;

    case 'lostpw':
        if (X_MEMBER) {
            $misc = $template->process('misc_feature_not_while_loggedin.php');
        } elseif (noSubmit('lostpwsubmit')) {
            $template->token = $token->create('Lost Password', '', $vars::NONCE_FORM_EXP, anonymous: true);
            $misc = $template->process('misc_lostpw.php');
        } else {
            $core->request_secure('Lost Password', '', error_header: true);
            $username = $core->postedVar('username');
            if (strlen($username) < $vars::USERNAME_MIN_LENGTH || strlen($username) > $vars::USERNAME_MAX_LENGTH) {
                $core->error($lang['badinfo']);
            }
            $email = $core->postedVar('email');

            $member = $sql->getMemberByName($username, $email);

            if (empty($member)) {
                $core->error($lang['badinfo']);
            }

            if ($member['status'] == 'Banned') {
                $core->error($lang['bannedmessage']);
            }

            $time = $vars->onlinetime - 86400;
            if ((int) $member['pwdate'] > $time) {
                $core->error($lang['lostpw_in24hrs']);
            }
            
            $sql->setLostPasswordDate((int) $member['uid'], time());
            $newtoken = $token->create('Lost Password', $member['username'], $vars::NONCE_MAX_AGE, anonymous: true);
            $link = $vars->full_url . "lost.php?a=$newtoken";

            $lang2 = $tran->loadPhrases(['charset', 'textyourpw', 'lostpw_body_eval']);
            $translate = $lang2[$member['langfile']];
            $name = htmlspecialchars_decode($member['username'], ENT_QUOTES);
            $emailaddy = htmlspecialchars_decode($member['email'], ENT_QUOTES);
            $rawbbname = htmlspecialchars_decode($SETTINGS['bbname'], ENT_NOQUOTES);
            $subject = "[$rawbbname] {$translate['textyourpw']}";
            $search  = ['$name', '$link'];
            $replace = [$name, $link];
            $body = str_replace($search, $replace, $lang['lostpw_body_eval']);
            $core->xmb_mail($emailaddy, $subject, $body, $translate['charset']);

            $core->message($lang['emailpw']);
        }
        break;

    case 'online':
        require XMB_ROOT . 'include/online.inc.php';

        if ($SETTINGS['whosonlinestatus'] == 'off') {
            header('HTTP/1.0 403 Forbidden');
            eval('echo "'.template('header').'";');
            eval('echo "'.template('misc_feature_notavailable').'";');
            end_time();
            eval('echo "'.template('footer').'";');
            exit();
        }

        $count = $db->result($db->query("SELECT COUNT(*) FROM " . $vars->tablepre . "whosonline"), 0);
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
            $where .= " AND (invisible != '1' OR username='$xmbuser')";
        }

        // UNION Syntax Reminder: "Use of ORDER BY for individual SELECT statements implies nothing about the order in which the rows appear."
        $sql = "SELECT username, 1 AS sort_col, MAX(ip) AS ip, MAX(`time`) as `time`, MAX(location) AS location, MAX(invisible) AS invisible "
             . "FROM " . $vars->tablepre . "whosonline $where GROUP BY username, sort_col "
             . "UNION ALL "
             . "SELECT username, 2 AS sort_col, ip, `time`, location, invisible "
             . "FROM " . $vars->tablepre . "whosonline WHERE username = 'xguest123' "
             . "ORDER BY sort_col, username, `time` DESC "
             . "LIMIT {$mpage['start']}, $tpp";
        $query = $db->query($sql);

        $onlineusers = '';
        while($online = $db->fetch_array($query)) {
            $array = url_to_text($online['location']);
            $vars->onlinetime = gmdate ($timecode, $core->timeKludge((int) $online['time']));
            $username = str_replace('xguest123', $lang['textguest1'], $online['username']);

            $online['location'] = shortenString($array['text'], 80);
            if (X_STAFF) {
                $online['location'] = '<a href="'.$array['url'].'">'.shortenString($array['text'], 80).'</a>';
            }

            if ('1' === $online['invisible'] && (X_ADMIN || $online['username'] === $xmbuser)) {
                $hidden = ' ('.$lang['hidden'].')';
            } else {
                $hidden = '';
            }

            if (X_SADMIN && $online['username'] != 'xguest123' && $online['username'] !== $lang['textguest1']) {
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

        $datecut = $vars->onlinetime - (3600 * 24);
        if (X_ADMIN) {
            $query = $db->query("SELECT username, status FROM " . $vars->tablepre . "members WHERE lastvisit >= '$datecut' ORDER BY username ASC");
        } else {
            $query = $db->query("SELECT username, status FROM " . $vars->tablepre . "members WHERE lastvisit >= '$datecut' AND invisible != '1' ORDER BY username ASC");
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
                $where[] = "email LIKE '%$dblikeemail%'";
                $where[] = "showemail='yes'";
            } else {
                $where[] = "email LIKE '%$dblikeemail%'";
            }
            $ext[] = 'srchemail='.rawurlencode(postedVar('srchemail', '', FALSE, FALSE, FALSE, 'g'));
            $srchemail = postedVar('srchemail', 'javascript', TRUE, FALSE, TRUE, 'g');
            /* Warning: $srchemail is used for template output */
        } else {
            $srchemail = '';
        }

        if ($dblikeip != '') {
            $where[] = "regip LIKE '%$dblikeip%'";
            $ext[] = 'srchip='.rawurlencode(postedVar('srchip', '', FALSE, FALSE, FALSE, 'g'));
            $srchip = postedVar('srchip', 'javascript', TRUE, FALSE, TRUE, 'g');
            /* Warning: $srchip is used for template output */
        } else {
            $srchip = '';
        }

        if ($dblikemem != '') {
            $where[] = "username LIKE '%$dblikemem%'";
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

        $where[] = "lastvisit != 0";
        if ('on' == $SETTINGS['hide_banned']) {
            $where[] = "status != 'Banned' ";
        }
        $q = implode(' AND ', $where);
        $num = $db->result($db->query("SELECT COUNT(*) FROM " . $vars->tablepre . "members WHERE $q"), 0);
        $canonical = 'misc.php?action=list';
        $baseurl = $canonical.$params;
        $mpage = multipage($num, $memberperpage, $baseurl, $canonical);
        $multipage =& $mpage['html'];
        if (strlen($mpage['html']) != 0) {
            eval('$multipage = "'.template('misc_mlist_multipage').'";');
        }
        unset($num, $where);


        /* Generate Output */

        $querymem = $db->query("SELECT * FROM " . $vars->tablepre . "members WHERE $q ORDER BY $orderby $desc LIMIT {$mpage['start']}, $memberperpage");

        $members = $oldst = '';
        if ($db->num_rows($querymem) == 0) {
            eval('$members = "'.template('misc_mlist_results_none').'";');
        } else {
            while($member = $db->fetch_array($querymem)) {
                $member['regdate'] = gmdate($dateformat, $core->timeKludge((int) $member['regdate']));

                $member['site'] = format_member_site($member['site']);
                if ($member['site'] == '') {
                    $site = '';
                } else {
                    eval('$site = "'.template('misc_mlist_row_site').'";');
                }

                if ($member['location'] != '') {
                    $member['location'] = $smile->censor($member['location']);
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
        eval('$header = "'.template('popup_header').'";');
        eval('$footer = "'.template('popup_footer').'";');
        $smilies = smilieinsert('full');
        eval('$misc = "'.template('misc_smilies').'";');
        echo $header, $misc, $footer;
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
        require XMB_ROOT.'include/captcha.inc.php';
        header('X-Robots-Tag: noindex');
        $oPhpCaptcha = new Captcha($core, $vars);
        $imagehash = getPhpInput('imagehash', sourcearray: 'g');
        $oPhpCaptcha->Create($imagehash, $observer);
        exit();
        break;

    default:
        $core->error($lang['textnoaction']);
        break;
}

$header = $template->process('header.php');
$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $misc, $footer;
