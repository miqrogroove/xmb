<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-3
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
$sql = Services\sql();
$template = Services\template();
$vars = Services\vars();

$page = getPhpInput('page', 'g');

$core->nav('<a href="faq.php">' . $vars->lang['textfaq'] . '</a>');

if ($vars->settings['faqstatus'] == 'off' && $page != 'forumrules') {
    header('HTTP/1.0 403 Forbidden');
    $header = $template->process('header.php');
    $featureoff = $template->process('misc_feature_notavailable.php');
    $template->footerstuff = $core->end_time();
    $footer = $template->process('footer.php');
    echo $header, $featureoff, $footer;
    exit();
}

switch ($page) {
    case 'usermaint':
        $core->setCanonicalLink("faq.php?page=$page");
        $core->nav($vars->lang['textuserman']);
        if ($vars->settings['subject_in_title'] == 'on') {
            $template->threadSubject = $vars->lang['textuserman'] . ' - ';
        }
        $faq = $template->process('faq_usermaint.php');
        break;
    case 'using':
        $core->setCanonicalLink("faq.php?page=$page");
        $core->nav($vars->lang['textuseboa']);
        if ($vars->settings['subject_in_title'] == 'on') {
            $template->threadSubject = $vars->lang['textuseboa'] . ' - ';
        }
        $template->stars = '';
        $template->rankrows = '';
        $ranks = $sql->getRanks(noStaff: true);
        foreach ($ranks as $rank) {
            $rank['title'] = rawHTML($rank['title']);
            $template->ranks = $rank;
            $template->stars = str_repeat('<img src="' . $vars->theme['imgdir'] . '/star.gif" alt="*" border="0" />', (int) $rank['stars']);
            $template->rankrows .= $template->process('faq_using_rankrow.php');
        }
        $faq = $template->process('faq_using.php');
        break;
    case 'messages':
        $core->setCanonicalLink("faq.php?page=$page");
        $core->nav($vars->lang['textpostread']);
        if ($vars->settings['subject_in_title'] == 'on') {
            $template->threadSubject = $vars->lang['textpostread'] . ' - ';
        }
        $template->smilierows = '';
        $smilies = $sql->getSmilies();
        foreach ($smilies as $smilie) {
            $template->smilie = $smilie;
            $template->smilierows .= $template->process('faq_messages_smilierow.php');
        }
        $faq = $template->process('faq_messages.php');
        break;
    case 'forumrules':
        $core->setCanonicalLink("faq.php?page=$page");
        $core->nav();
        $core->nav($vars->lang['textbbrules']);
        if ($vars->settings['subject_in_title'] == 'on') {
            $template->threadSubject = $vars->lang['textbbrules'] . ' - ';
        }
        if (empty($vars->settings['bbrulestxt'])) {
            $template->rules = $this->vars->lang['textnone'];
        } else {
            $template->rules = nl2br(rawHTML($vars->settings['bbrulestxt']));
        }
        $faq = $template->process('faq_forumrules.php');
        break;
    case '':
        $core->setCanonicalLink('faq.php');
        if ($vars->settings['subject_in_title'] == 'on') {
            $template->threadSubject = $vars->lang['textfaq'] . ' - ';
        }
        $faq = $template->process('faq.php');
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        $core->error($vars->lang['generic_missing']);
}

$header = $template->process('header.php');
$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $faq, $footer;
