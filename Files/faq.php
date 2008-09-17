<?php
/**
 * XMB 1.9.5 Nexus Final SP1
 * � 2007 John Briggs
 * http://www.xmbmods.com
 * john@xmbmods.com
 *
 * Developed By The XMB Group
 * Copyright (c) 2001-2007, The XMB Group
 * http://www.xmbforum.com
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

if ($faqstatus != "on" && $page != 'forumrules') {
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

switch ($page) {
    case 'usermaint':
        loadtemplates('faq_usermaint');
        nav($lang['textuserman']);
        eval('$faq = "'.template('faq_usermaint').'";');
        break;
    case 'using':
        loadtemplates('faq_using');
        nav($lang['textuseboa']);
        eval('$faq = "'.template('faq_using').'";');
        break;
    case 'messages':
        loadtemplates('faq_messages_smilierow', 'faq_messages');
        $smilierows = NULL;
        nav($lang['textpostread']);
        $querysmilie = $db->query("SELECT * FROM " .$table_smilies. " WHERE type = 'smiley'") or die($db->error());
        while ($smilie = $db->fetch_array($querysmilie)) {
            eval('$smilierows .= "'.template('faq_messages_smilierow').'";');
        }
        $db->free_result($querysmilie);
        eval('$faq = "'.template('faq_messages').'";');
        break;
    case 'misc':
        loadtemplates('faq_misc_rankrow', 'faq_misc');
        $stars      = '';
        $rankrows   = '';
        nav($lang['textmiscfaq']);
        $query = $db->query("SELECT * FROM $table_ranks WHERE title!='Moderator' AND title!='Super Moderator' AND title!='Super Administrator' AND title!='Administrator' ORDER BY posts ASC");
        while ($ranks = $db->fetch_array($query)) {
            $stars = str_repeat("<img src=\"" .$imgdir. "/star.gif\" alt=\"*\" />", $ranks['stars']);
            eval('$rankrows .= "'.template('faq_misc_rankrow').'";');
            $stars = '';
        }
        $db->free_result($query);
        eval('$faq = "'.template('faq_misc').'";');
        break;
    case 'forumrules':
        loadtemplates('faq_forumrules');
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
        loadtemplates('faq');
        eval('$faq = "'.template('faq').'";');
        break;
}

eval('$css = "'.template('css').'";');
eval('$header = "'.template('header').'";');
end_time();
eval('$footer = "'.template('footer').'";');
echo stripslashes($header.$faq.$footer);
?>