<?php
/**
 * XMB 1.9.9 Saigo
 *
 * Developed by the XMB Group Copyright (c) 2001-2008
 * Sponsored by iEntry Inc. Copyright (c) 2007
 *
 * http://xmbgroup.com , http://ientry.com
 *
 * This software is released under the GPL License, you should
 * have received a copy of this license with the download of this
 * software. If not, you can obtain a copy by visiting the GNU
 * General Public License website <http://www.gnu.org/licenses/>.
 *
 **/

require 'header.php';

$page = getVar('page');

if ($SETTINGS['faqstatus'] == 'off' && $page != 'forumrules') {
    loadtemplates('misc_feature_notavailable');
    eval('$css = "'.template('css').'";');
    nav('<a href="faq.php">'.$lang['textfaq']. '</a>');
    eval('$header = "'.template('header').'";');
    eval('$featureoff = "'.template('misc_feature_notavailable').'";');
    end_time();
    eval('$footer = "'.template('footer').'";');
    echo stripslashes($header.$featureoff.$footer);
    exit();
}

nav('<a href="faq.php">'.$lang['textfaq'].'</a>');

switch($page) {
    case 'usermaint':
        loadtemplates('faq_usermaint');
        nav($lang['textuserman']);
        eval('$faq = "'.template('faq_usermaint').'";');
        break;
    case 'using':
        loadtemplates('faq_using_rankrow', 'faq_using');
        nav($lang['textuseboa']);
        $stars = $rankrows   = '';
        $query = $db->query("SELECT * FROM ".X_PREFIX."ranks WHERE title !='Moderator' AND title !='Super Moderator' AND title !='Super Administrator' AND title !='Administrator' ORDER BY posts ASC");
        while($ranks = $db->fetch_array($query)) {
            $stars = str_repeat('<img src="'.$imgdir.'/star.gif" alt="*" border="0" />', $ranks['stars']);
            eval('$rankrows .= "'.template('faq_using_rankrow').'";');
            $stars = '';
        }
        $db->free_result($query);
        eval('$faq = "'.template('faq_using').'";');
        break;
    case 'messages':
        loadtemplates('faq_messages_smilierow', 'faq_messages');
        $smilierows = NULL;
        nav($lang['textpostread']);
        $querysmilie = $db->query("SELECT * FROM `" .X_PREFIX. "smilies` WHERE type = 'smiley'") or die($db->error());
        while($smilie = $db->fetch_array($querysmilie)) {
            eval('$smilierows .= "'.template('faq_messages_smilierow').'";');
        }
        $db->free_result($querysmilie);
        eval('$faq = "'.template('faq_messages').'";');
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
