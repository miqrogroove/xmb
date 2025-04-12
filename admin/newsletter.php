<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
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

define('XMB_ROOT', '../');
require XMB_ROOT . 'header.php';

$core = \XMB\Services\core();
$db = \XMB\Services\db();
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$validate = \XMB\Services\validate();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textnewsletter']);
$core->setCanonicalLink('admin/newsletter.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textnewsletter'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (noSubmit('newslettersubmit')) {
    $template->token = $token->create('Control Panel/Newsletter', 'send', $vars::NONCE_FORM_EXP);
    $body = $template->process('admin_newsletter.php');
} else {
    $core->request_secure('Control Panel/Newsletter', 'send', error_header: true);
    @set_time_limit(0);
    $newssubject = $validate->postedVar('newssubject');
    $newsmessage = $validate->postedVar('newsmessage');
    $sendvia = $validate->postedVar('sendvia', '', false, false);
    $to = $validate->postedVar('to', '', false, false);
    $newscopy = formYesNo('newscopy');
    $wait = formInt('wait');

    if ($newscopy != 'yes') {
        $tome = "AND NOT username = '" . $vars->xmbuser . "'";
    } else {
        $tome = "OR username = '" . $vars->xmbuser . "'";
    }

    if ($to == "all") {
        $query = $db->query("SELECT username, email FROM " . $vars->tablepre . "members WHERE newsletter='yes' $tome ORDER BY uid");
    } else if ($to == "staff") {
        $query = $db->query("SELECT username, email FROM " . $vars->tablepre . "members WHERE (status='Super Administrator' OR status='Administrator' OR status='Super Moderator' OR status='Moderator') $tome ORDER BY uid");
    } else if ($to == "admin") {
        $query = $db->query("SELECT username, email FROM " . $vars->tablepre . "members WHERE (status='Administrator' OR status = 'Super Administrator') $tome ORDER BY uid");
    } else if ($to == "supermod") {
        $query = $db->query("SELECT username, email FROM " . $vars->tablepre . "members WHERE status='Super moderator' $tome ORDER by uid");
    } else if ($to == "mod") {
        $query = $db->query("SELECT username, email FROM " . $vars->tablepre . "members WHERE status='Moderator' $tome ORDER BY uid");
    }

    if ($sendvia == "u2u") {
        while($memnews = $db->fetch_array($query)) {
            $db->escape_fast($memnews['username']);
            $db->query("INSERT INTO " . $vars->tablepre . "u2u (msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus) VALUES ('{$memnews['username']}', '" . $vars->xmbuser . "', 'incoming', '{$memnews['username']}', 'Inbox', '$newssubject', '$newsmessage', '" . time() . "', 'no', 'yes')");
        }
        $body = "<tr bgcolor='" . $vars->theme['altbg2'] . "' class='tablerow'><td align='center'>{$lang['newslettersubmit']}</td></tr>";
    } else {
        $rawnewssubject = $validate->postedVar('newssubject', '', FALSE, FALSE);
        $rawnewsmessage = $validate->postedVar('newsmessage', '', FALSE, FALSE);
        $rawuser = htmlspecialchars_decode($self['username'], ENT_QUOTES);
        $rawbbname = htmlspecialchars_decode($vars->settings['bbname'], ENT_NOQUOTES);
        $subject = "[$rawbbname] $rawnewssubject";

        $i = 0;
        $total = 0;
        @ignore_user_abort(1);
        @set_time_limit(0);
        @ob_implicit_flush(1);

        while($memnews = $db->fetch_array($query)) {
            if ($i > 0 && $i == $wait) {
                sleep(3);
                $i = 0;
            } else {
                if ($total % 250 == 0) {
                    error_log("XMB Notice: $total newsletter e-mails transmitted by $rawuser");
                }
                $i++;
            }

            $rawemail = htmlspecialchars_decode($memnews['email'], ENT_QUOTES);
            $core->xmb_mail($rawemail, $subject, $rawnewsmessage, $charset);
            $total++;
        }
        error_log("XMB Notice: $total newsletter e-mails transmitted by $rawuser");
        $body = "<tr bgcolor='" . $vars->theme['altbg2'] . "' class='tablerow'><td align='center'>{$lang['newslettersubmit']} {$lang['textsent']} $total</td></tr>";
    }
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
