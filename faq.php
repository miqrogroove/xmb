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

require './header.php';

$core = \XMB\Services\core();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$vars = \XMB\Services\vars();

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
        $faq = $template->process('faq_usermaint.php');
        break;
    case 'using':
        $core->setCanonicalLink("faq.php?page=$page");
        $core->nav($vars->lang['textuseboa']);
        $template->stars = '';
        $template->rankrows = '';
        $ranks = $sql->getRanks(noStaff: true);
        foreach ($ranks as $rank) {
            $template->ranks = $rank;
            $template->stars = str_repeat('<img src="' . $vars->theme['imgdir'] . '/star.gif" alt="*" border="0" />', (int) $rank['stars']);
            $template->rankrows .= $template->process('faq_using_rankrow.php');
        }
        $faq = $template->process('faq_using.php');
        break;
    case 'messages':
        $core->setCanonicalLink("faq.php?page=$page");
        $core->nav($vars->lang['textpostread']);
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
        if (empty($vars->settings['bbrulestxt'])) {
            $vars->settings['bbrulestxt'] = $this->vars->lang['textnone'];
        } else {
            $vars->settings['bbrulestxt'] = nl2br($vars->settings['bbrulestxt']);
        }
        $faq = $template->process('faq_forumrules.php');
        break;
    default:
        $core->setCanonicalLink('faq.php');
        $faq = $template->process('faq.php');
        break;
}

$header = $template->process('header.php');
$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $faq, $footer;
