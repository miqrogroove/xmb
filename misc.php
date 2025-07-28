<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-2
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

require './header.php';

$core = Services\core();
$db = Services\db();
$emailSvc = Services\email();
$forums = Services\forums();
$login = Services\login();
$observer = Services\observer();
$session = Services\session();
$smile = Services\smile();
$sql = Services\sql();
$template = Services\template();
$token = Services\token();
$tran = Services\translation();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;
$SETTINGS = &$vars->settings;

$action = getPhpInput('action', 'g');
switch ($action) {
    case 'login':
        $pagename = 'textlogin';
        break;
    case 'logout':
        $pagename = 'textlogout';
        break;
    case 'lostpw':
        $pagename = 'textlostpw';
        break;
    case 'online':
        $pagename = 'whosonline';
        break;
    case 'list':
        $pagename = 'textmemberlist';
        break;
    case 'onlinetoday':
        $pagename = 'whosonlinetoday';
        break;
    case 'captchaimage':
        $pagename = 'textregister';
        break;
    case 'smilies':
        $pagename = 'smilies';
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        $core->error($lang['textnoaction']);
}

$core->nav($lang[$pagename]);
if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang[$pagename] . ' - ';
}

$misc = '';
$multipage = '';
$nextlink = '';

switch ($action) {
    case 'login':
        $template->nameMin = $vars::USERNAME_MIN_LENGTH;
        $template->nameSize = $vars::USERNAME_MAX_LENGTH;
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
            switch ($session->getStatus()) {
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

    case 'lostpw':
        if (X_MEMBER) {
            $misc = $template->process('misc_feature_not_while_loggedin.php');
        } elseif (noSubmit('lostpwsubmit')) {
            $template->token = $token->create('Lost Password', '', $vars::NONCE_FORM_EXP, anonymous: true);
            $misc = $template->process('misc_lostpw.php');
        } else {
            $core->request_secure('Lost Password', '');
            $username = $validate->postedVar('username');
            if (strlen($username) < $vars::USERNAME_MIN_LENGTH || strlen($username) > $vars::USERNAME_MAX_LENGTH) {
                $core->error($lang['badinfo']);
            }
            $email = $validate->postedVar('email');

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
            $name = rawHTML($member['username']);
            $emailaddy = rawHTML($member['email']);
            $rawbbname = rawHTML($SETTINGS['bbname']);
            $subject = "[$rawbbname] {$translate['textyourpw']}";
            $search  = ['$name', '$link'];
            $replace = [$name, $link];
            $body = str_replace($search, $replace, $lang['lostpw_body_eval']);
            $emailSvc->send($emailaddy, $subject, $body, $translate['charset']);

            $core->message($lang['emailpw']);
        }
        break;

    case 'online':
        if ($SETTINGS['whosonlinestatus'] == 'off') {
            header('HTTP/1.0 403 Forbidden');
            $header = $template->process('header.php');
            $misc = $template->process('misc_feature_notavailable.php');
            $template->footerstuff = $core->end_time();
            $footer = $template->process('footer.php');
            echo $header, $misc, $footer;
            exit;
        }

        $urlSvc = new URL2Text($core, $db, $forums, $smile, $vars);

        $count = $db->result($db->query("SELECT COUNT(*) FROM " . $vars->tablepre . "whosonline"));
        $mpage = $core->multipage((int) $count, $vars->tpp, $vars->full_url . 'misc.php?action=online');
        $template->multipage = $mpage['html'];
        if (strlen($mpage['html']) != 0) {
            if (X_ADMIN) {
                $template->multipage = $template->process('misc_online_multipage_admin.php');
            } else {
                $template->multipage = $template->process('misc_online_multipage.php');
            }
        }

        $where = "WHERE username != 'xguest123'";
        if (! X_ADMIN) {
            $xmbuser = $vars->xmbuser;
            $where .= " AND (invisible != '1' OR username = '$xmbuser')";
        }

        // UNION Syntax Reminder: "Use of ORDER BY for individual SELECT statements implies nothing about the order in which the rows appear."
        $sql = "SELECT username, 1 AS sort_col, MAX(ip) AS ip, MAX(`time`) as `time`, MAX(location) AS location, MAX(invisible) AS invisible "
             . "FROM " . $vars->tablepre . "whosonline $where GROUP BY username, sort_col "
             . "UNION ALL "
             . "SELECT username, 2 AS sort_col, ip, `time`, location, invisible "
             . "FROM " . $vars->tablepre . "whosonline WHERE username = 'xguest123' "
             . "ORDER BY sort_col, username, `time` DESC "
             . "LIMIT {$mpage['start']}, " . $vars->tpp;
        $query = $db->query($sql);

        $template->onlineusers = '';
        while ($online = $db->fetch_array($query)) {
            $array = $urlSvc->convert($online['location']);
            $template->onlinetime = gmdate($vars->timecode, $core->timeKludge((int) $online['time']));
            $username = str_replace('xguest123', $lang['textguest1'], $online['username']);

            $online['location'] = shortenString($array['text'], 80);
            if (X_STAFF) {
                $online['location'] = "<a href='{$array['url']}'>" . shortenString($array['text'], 80) . '</a>';
            }

            if ('1' === $online['invisible'] && (X_ADMIN || $online['username'] === $xmbuser)) {
                $hidden = " ({$lang['hidden']})";
            } else {
                $hidden = '';
            }

            if (X_SADMIN && $online['username'] != 'xguest123' && $online['username'] !== $lang['textguest1']) {
                $online['username'] = "<a href='" . $vars->full_url . 'member.php?action=viewpro&amp;member=' . recodeOut($online['username']) . "'>$username</a>$hidden";
            } else {
                $online['username'] = $username;
            }

            $template->online = $online;
            if (X_ADMIN) {
                $template->online = $online;
                $template->onlineusers .= $template->process('misc_online_row_admin.php');
            } else {
                $online['invisible'] = '';
                $online['ip'] = '';
                $template->online = $online;
                $template->onlineusers .= $template->process('misc_online_row.php');
            }
        }
        $db->free_result($query);

        if (X_ADMIN) {
            $misc = $template->process('misc_online_admin.php');
        } else {
            $misc = $template->process('misc_online.php');
        }

        break;

    case 'onlinetoday':
        if ($SETTINGS['whosonlinestatus'] == 'off' || $SETTINGS['onlinetoday_status'] == 'off') {
            header('HTTP/1.0 403 Forbidden');
            $header = $template->process('header.php');
            $misc = $template->process('misc_feature_notavailable.php');
            $template->footerstuff = $core->end_time();
            $footer = $template->process('footer.php');
            echo $header, $misc, $footer;
            exit;
        }

        $datecut = $vars->onlinetime - (3600 * 24);
        if (X_ADMIN) {
            $extra = '';
        } else {
            $extra = "AND invisible != '1'";
        }
        $query = $db->query("SELECT username, status FROM " . $vars->tablepre . "members WHERE lastvisit >= '$datecut' $extra ORDER BY username ASC");

        $template->todaymembersnum = $db->num_rows($query);
        $todaymembers = [];
        while ($memberstoday = $db->fetch_array($query)) {
            $pre = '<span class="status_' . str_replace(' ', '_', $memberstoday['status']) . '">';
            $suff = '</span>';
            $todaymembers[] = "<a href='" . $vars->full_url . "member.php?action=viewpro&amp;member=" . recodeOut($memberstoday['username']) . "'>$pre{$memberstoday['username']}$suff</a>";
        }
        $template->todaymembers = implode(', ', $todaymembers);
        $db->free_result($query);

        $misc = $template->process('misc_online_today.php');
        break;

    case 'list':
        if ($SETTINGS['memliststatus'] == 'off') {
            header('HTTP/1.0 403 Forbidden');
            $header = $template->process('header.php');
            $misc = $template->process('misc_feature_notavailable.php');
            $template->footerstuff = $core->end_time();
            $footer = $template->process('footer.php');
            echo $header, $misc, $footer;
            exit;
        }

        // Validate All Inputs
        $order = getPhpInput('order', 'g');
        $desc = getPhpInput('desc', 'g');
        $page = getInt('page');
        $dblikemem = $db->like_escape($validate->postedVar('srchmem', dbescape: false, sourcearray: 'g'));
        if (X_ADMIN) {
            $dblikeemail = $db->like_escape($validate->postedVar('srchemail', dbescape: false, sourcearray: 'g'));
            $dblikeip = $db->like_escape($validate->postedVar('srchip', dbescape: false, sourcearray: 'g'));
        } else {
            $dblikeemail = '';
            $dblikeip = '';
        }

        if (strtolower($desc) != 'desc') {
            $desc = 'asc';
        }

        if ($order != 'username' && $order != 'postnum' && $order != 'status' && $order != 'location') {
            $order = '';
            $orderby = 'regdate';
        } elseif ($order == 'status') {
            $orderby = "if (status='Super Administrator', 1, if (status='Administrator', 2, if (status='Super Moderator', 3, if (status='Moderator', 4, if (status='Member', 5, if (status='Banned',6,7))))))";
        } else {
            $orderby = $order;
        }

        $misc_mlist_template = X_ADMIN ? 'misc_mlist_admin.php' : 'misc_mlist.php';

        $where = [];
        $ext = [];

        if ($desc != 'asc') {
            $ext[] = "desc=$desc";
        }

        if ($order != '') {
            $ext[] = "order=$order";
        }

        if ($dblikeemail != '') {
            $where[] = "email LIKE '%$dblikeemail%'";
            $ext[] = 'srchemail=' . rawurlencode(getPhpInput('srchemail', 'g'));
            $template->srchemail = $validate->postedVar(
                varname: 'srchemail',
                dbescape: false,
                sourcearray: 'g'
            );
        } else {
            $template->srchemail = '';
        }

        if ($dblikeip != '') {
            $where[] = "regip LIKE '%$dblikeip%'";
            $ext[] = 'srchip=' . rawurlencode(getPhpInput('srchip', 'g'));
            $template->srchip = $validate->postedVar(
                varname: 'srchip',
                dbescape: false,
                sourcearray: 'g'
            );
        } else {
            $template->srchip = '';
        }

        if ($dblikemem != '') {
            $where[] = "username LIKE '%$dblikemem%'";
            $ext[] = 'srchmem=' . rawurlencode(getPhpInput('srchmem', 'g'));
            $template->srchmem = $validate->postedVar(
                varname: 'srchmem',
                dbescape: false,
                sourcearray: 'g'
            );
        } else {
            $template->srchmem = '';
        }

        if (count($ext) > 0) {
            $params = '&amp;' . implode('&amp;', $ext);

            if ($ext[0] == 'desc=desc') {
                array_shift($ext);
                $template->sflip = '';
            } else {
                $template->sflip = '&amp;desc=desc';
            }
            if (count($ext) > 0) {
                if (substr($ext[0], 0, 6) == 'order=') {
                    $template->sflip .= '&amp;' . array_shift($ext);
                }
            }
            if (count($ext) > 0) {
                $template->ext = '&amp;' . implode('&amp;', $ext);
            } else {
                $template->ext = '';
            }
        } else {
            $params = '';
            $template->sflip = '&amp;desc=desc';
            $template->ext = '';
        }

        $where[] = "lastvisit != 0";
        if ('on' == $SETTINGS['hide_banned']) {
            $where[] = "status != 'Banned' ";
        }
        $q = implode(' AND ', $where);
        $num = (int) $db->result($db->query("SELECT COUNT(*) FROM " . $vars->tablepre . "members WHERE $q"));
        $canonical = $vars->full_url . 'misc.php?action=list';
        $baseurl = $canonical . $params;
        $memberperpage = (int) $vars->settings['memberperpage'];
        $mpage = $core->multipage($num, $memberperpage, $baseurl, $canonical);
        $template->multipage = $mpage['html'];
        if (strlen($mpage['html']) != 0) {
            $template->multipage = $template->process('misc_mlist_multipage.php');
        }
        unset($num, $where);


        /* Generate Output */

        $querymem = $db->query("SELECT * FROM " . $vars->tablepre . "members WHERE $q ORDER BY $orderby $desc LIMIT {$mpage['start']}, $memberperpage");

        $template->members = '';
        $oldst = '';
        if ($db->num_rows($querymem) == 0) {
            $template->members = $template->process('misc_mlist_results_none.php');
        } else {
            while ($member = $db->fetch_array($querymem)) {
                $template->regdate = $core->printGmDate($core->timeKludge((int) $member['regdate']));

                $member['site'] = format_member_site($member['site']);
                if ($member['site'] == '') {
                    $template->site = '';
                } else {
                    $template->site = $member['site'];
                    $template->site = $template->process('misc_mlist_row_site.php');
                }

                if ($member['location'] != '') {
                    $template->location = $smile->censor($member['location']);
                } else {
                    $template->location = '';
                }

                $template->memurl = recodeOut($member['username']);
                $template->username = $member['username'];
                if ($order == 'status') {
                    if ($oldst != $member['status']) {
                        $oldst = $member['status'];
                        $template->seperator_text = (trim($member['status']) == '' ? $lang['onlineother'] : $member['status']);
                        $template->members .= $template->process('misc_mlist_separator.php');
                    }
                }
                $template->postnum = $member['postnum'];
                $template->status = $member['status'];
                $template->members .= $template->process('misc_mlist_row.php');
            }
            $db->free_result($querymem);
        }

        if (strtolower($desc) == 'desc') {
            $template->ascdesc = $lang['asc'];
        } else {
            $template->ascdesc = $lang['desc'];
        }
        $misc = $template->process($misc_mlist_template);
        break;

    case 'smilies':
        $header = $template->process('popup_header.php');
        $template->smilies = $core->smilieinsert('full');
        $misc = $template->process('misc_smilies.php');
        $footer = $template->process('popup_footer.php');
        echo $header, $misc, $footer;
        exit;

    case 'captchaimage':
        if ($SETTINGS['captcha_status'] == 'off') {
            header('HTTP/1.0 403 Forbidden');
            $misc = $template->process('misc_feature_notavailable.php');
        } else {
            header('X-Robots-Tag: noindex');
            $oPhpCaptcha = new Captcha($core, $vars);
            $imagehash = getPhpInput('imagehash', sourcearray: 'g');
            $oPhpCaptcha->Create($imagehash, $observer);
            exit;
        }
        break;

    default:
        $core->error($lang['textnoaction']);
}

$header = $template->process('header.php');
$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $misc, $footer;
