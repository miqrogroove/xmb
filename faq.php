<?php
/* $Id: faq.php,v 1.3.2.6 2007/01/28 01:57:25 FunForum Exp $ */
/*
    © 2001 - 2007 Aventure Media & The XMB Development Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require './header.php';

if ($SETTINGS['faqstatus'] != "on" && $page != 'forumrules') {
    loadtemplates('misc_feature_notavailable');
    eval('$css = "'.template('css').'";');

    nav('<a href="faq.php">'.$lang['textfaq']. '</a>');

    eval('$header = "'.template('header').'";');
    eval('$featureoff = "'.template('misc_feature_notavailable').'";');
    end_time();
    eval('$footer = "'.template('footer').'";');

    echo stripslashes($header . $featureoff . $footer);
    exit();
}

$page = (isset($page)) ? $page : null;
nav('<a href="faq.php">'.$lang['textfaq'].'</a>');

switch($page) {
    case 'usermaint':
        loadtemplates('css', 'faq_usermaint', 'header', 'footer', 'footer_querynum', 'footer_phpsql', 'footer_totaltime', 'footer_load');
        nav($lang['textuserman']);

        eval('$faq = "'.template('faq_usermaint').'";');
        break;

    case 'using':
        loadtemplates('css', 'faq_using', 'header', 'footer', 'footer_querynum', 'footer_phpsql', 'footer_totaltime', 'footer_load');
        nav($lang['textuseboa']);
        eval('$faq = "'.template('faq_using').'";');
        break;

    case 'messages':
        loadtemplates('css', 'faq_messages_smilierow', 'footer', 'faq_messages', 'header', 'footer_querynum', 'footer_phpsql', 'footer_totaltime', 'footer_load');
        $smilierows = NULL;
        nav($lang['textpostread']);
        $querysmilie = $db->query("SELECT * FROM " .$table_smilies. " WHERE type = 'smiley'") or die($db->error());
        while($smilie = $db->fetch_array($querysmilie)) {
            eval('$smilierows .= "'.template('faq_messages_smilierow').'";');
        }
        eval('$faq = "'.template('faq_messages').'";');
        break;

    case 'misc':
        loadtemplates('css', 'faq_misc_rankrow', 'faq_misc', 'footer', 'header', 'footer_querynum', 'footer_phpsql', 'footer_totaltime', 'footer_load');
        $stars      = '';
        $rankrows   = '';
        nav($lang['textmiscfaq']);
        $query = $db->query("SELECT * FROM $table_ranks WHERE title!='Moderator' AND title!='Super Moderator' AND title!='Super Administrator' AND title!='Administrator' ORDER BY posts ASC");
        while($ranks = $db->fetch_array($query)) {
            $stars = str_repeat("<img src=\"" .$THEME['imgdir']. "/star.gif\" alt=\"*\" />", $ranks['stars']);
            eval('$rankrows .= "'.template('faq_misc_rankrow').'";');
            $stars = '';
        }
        eval('$faq = "'.template('faq_misc').'";');
        break;

    case 'forumrules':
        loadtemplates('css', 'faq_forumrules' ,'header', 'footer', 'footer_querynum', 'footer_phpsql', 'footer_totaltime', 'footer_load');
        nav();
        nav($lang['textbbrules']);
        if (empty($SETTINGS['bbrulestxt'])) {
            $SETTINGS['bbrulestxt'] = $lang['textnone'];
        } else {
            $SETTINGS['bbrulestxt'] = nl2br(stripslashes($SETTINGS['bbrulestxt']));
        }
        eval('$faq = "'.template('faq_forumrules').'";');
        break;

    default:
        loadtemplates('css', 'faq', 'header', 'footer_querynum', 'footer', 'footer_phpsql', 'footer_totaltime', 'footer_load');
        eval('$faq = "'.template('faq').'";');
        break;
}

eval("\$css = \"".template("css")."\";");
eval("\$header = \"".template("header")."\";");
end_time();
eval("\$footer = \"".template("footer")."\";");
echo stripslashes($header . $faq . $footer);
