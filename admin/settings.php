<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2024, The XMB Group
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

define('ROOT', '../');
require ROOT . 'header.php';
require ROOT . 'include/admin.inc.php';

$core = \XMB\Services\core();
$db = \XMB\Services\db();
$observer = \XMB\Services\observer();
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;
$SETTINGS = &$vars->settings;
$THEME = &$vars->theme;

header('X-Robots-Tag: noindex');
header('X-XSS-Protection: 0'); // Disables HTML input errors in Chrome.

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textsettings']);
$core->setCanonicalLink('admin/settings.php');

$header = $template->process('header.php');

if (!X_ADMIN) {
    $noLogin = $template->process('error_nologinsession.php');
    $template->footerstuff = $core->end_time();
    $footer = $template->process('footer.php');
    echo $header, $noLogin, $footer;
    exit();
}

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$panel = $template->process('admin_table.php');

echo $header, $panel;

if (
    noSubmit('settingsubmit1')
    && noSubmit('settingsubmit2')
    && noSubmit('settingsubmit3')
    && noSubmit('settingsubmit4')
    && noSubmit('settingsubmit5')
    && noSubmit('settingsubmit6')
    && noSubmit('settingsubmit7')
    && noSubmit('settingsubmit8')
    && noSubmit('settingsubmit9')
    && noSubmit('settingsubmit10')
) {
    $langfileselect = $core->createLangFileSelect($SETTINGS['langfile']);

    $themelist = array();
    $themelist[] = '<select name="themenew">';
    $query = $db->query("SELECT themeid, name FROM " . $vars->tablepre . "themes ORDER BY name ASC");
    while($themeinfo = $db->fetch_array($query)) {
        if ($themeinfo['themeid'] == $SETTINGS['theme']) {
            $themelist[] = '<option value="'.intval($themeinfo['themeid']).'" ' . $vars::selHTML . '>'.$themeinfo['name'].'</option>';
        } else {
            $themelist[] = '<option value="'.intval($themeinfo['themeid']).'">'.$themeinfo['name'].'</option>';
        }
    }
    $themelist[] = '</select>';
    $themelist = implode("\n", $themelist);
    $db->free_result($query);

    $onselect = $offselect = '';
    settingHTML('bbstatus', $onselect, $offselect);

    $whosonlineon = $whosonlineoff = '';
    settingHTML('whosonlinestatus', $whosonlineon, $whosonlineoff);

    $regon = $regoff = '';
    settingHTML('regstatus', $regon, $regoff);

    $regonlyon = $regonlyoff = '';
    settingHTML('regviewonly', $regonlyon, $regonlyoff);

    $catsonlyon = $catsonlyoff = '';
    settingHTML('catsonly', $catsonlyon, $catsonlyoff);

    $hideon = $hideoff = '';
    settingHTML('hideprivate', $hideon, $hideoff);

    $echeckon = $echeckoff = '';
    settingHTML('emailcheck', $echeckon, $echeckoff);

    $ruleson = $rulesoff = '';
    settingHTML('bbrules', $ruleson, $rulesoff);

    $searchon = $searchoff = '';
    settingHTML('searchstatus', $searchon, $searchoff);

    $faqon = $faqoff = '';
    settingHTML('faqstatus', $faqon, $faqoff);

    $memliston = $memlistoff = '';
    settingHTML('memliststatus', $memliston, $memlistoff);

    $todayon = $todayoff = '';
    settingHTML('todaysposts', $todayon, $todayoff);

    $statson = $statsoff = '';
    settingHTML('stats', $statson, $statsoff);

    $gzipcompresson = $gzipcompressoff = '';
    settingHTML('gzipcompress', $gzipcompresson, $gzipcompressoff);

    $coppaon = $coppaoff = '';
    settingHTML('coppa', $coppaon, $coppaoff);

    $sigbbcodeon = $sigbbcodeoff = '';
    settingHTML('sigbbcode', $sigbbcodeon, $sigbbcodeoff);

    $reportposton = $reportpostoff = '';
    settingHTML('reportpost', $reportposton, $reportpostoff);

    $bbinserton = $bbinsertoff = '';
    settingHTML('bbinsert', $bbinserton, $bbinsertoff);

    $smileyinserton = $smileyinsertoff = '';
    settingHTML('smileyinsert', $smileyinserton, $smileyinsertoff);

    $doubleeon = $doubleeoff = '';
    settingHTML('doublee', $doubleeon, $doubleeoff);

    $editedbyon = $editedbyoff = '';
    settingHTML('editedby', $editedbyon, $editedbyoff);

    $dotfolderson = $dotfoldersoff = '';
    settingHTML('dotfolders', $dotfolderson, $dotfoldersoff);

    $attachimgposton = $attachimgpostoff = '';
    settingHTML('attachimgpost', $attachimgposton, $attachimgpostoff);

    $tickerstatuson = $tickerstatusoff = '';
    settingHTML('tickerstatus', $tickerstatuson, $tickerstatusoff);

    $spacecatson = $spacecatsoff = '';
    settingHTML('space_cats', $spacecatson, $spacecatsoff);

    $subjectInTitleOn = $subjectInTitleOff = '';
    settingHTML('subject_in_title', $subjectInTitleOn, $subjectInTitleOff);

    $allowrankediton = $allowrankeditoff = '';
    settingHTML('allowrankedit', $allowrankediton, $allowrankeditoff);

    $spellcheckon = $spellcheckoff = '';
    settingHTML('spellcheck', $spellcheckon, $spellcheckoff);

    $resetSigOn = $resetSigOff = '';
    settingHTML('resetsigs', $resetSigOn, $resetSigOff);

    $captchaOn = $captchaOff = '';
    settingHTML('captcha_status', $captchaOn, $captchaOff);

    $captcharegOn = $captcharegOff = '';
    settingHTML('captcha_reg_status', $captcharegOn, $captcharegOff);

    $captchapostOn = $captchapostOff = '';
    settingHTML('captcha_post_status', $captchapostOn, $captchapostOff);

    $captchasearchOn = $captchasearchOff = '';
    settingHTML('captcha_search_status', $captchasearchOn, $captchasearchOff);

    $captchacodecaseOn = $captchacodecaseOff = '';
    settingHTML('captcha_code_casesensitive', $captchacodecaseOn, $captchacodecaseOff);

    $captchacodeshadowOn = $captchacodeshadowOff = '';
    settingHTML('captcha_code_shadow', $captchacodeshadowOn, $captchacodeshadowOff);

    $captchaimagecolorOn = $captchaimagecolorOff = '';
    settingHTML('captcha_image_color', $captchaimagecolorOn, $captchaimagecolorOff);

    $showsubson = $showsubsoff = '';
    settingHTML('showsubforums', $showsubson, $showsubsoff);

    $regoptionalon = $regoptionaloff = '';
    settingHTML('regoptional', $regoptionalon, $regoptionaloff);

    $quickreply_statuson = $quickreply_statusoff = '';
    settingHTML('quickreply_status', $quickreply_statuson, $quickreply_statusoff);

    $quickjump_statuson = $quickjump_statusoff = '';
    settingHTML('quickjump_status', $quickjump_statuson, $quickjump_statusoff);

    $index_statson = $index_statsoff = '';
    settingHTML('index_stats', $index_statson, $index_statsoff);

    $onlinetoday_statuson = $onlinetoday_statusoff = '';
    settingHTML('onlinetoday_status', $onlinetoday_statuson, $onlinetoday_statusoff);

    $remoteimageson = $remoteimagesoff = '';
    settingHTML('attach_remote_images', $remoteimageson, $remoteimagesoff);

    $showlogson = $showlogsoff = '';
    settingHTML('show_logs_in_threads', $showlogson, $showlogsoff);
    
    $quarantineon = $quarantineoff = '';
    settingHTML('quarantine_new_users', $quarantineon, $quarantineoff);

    $recaptchaon = $recaptchaoff = '';
    settingHTML('google_captcha', $recaptchaon, $recaptchaoff);

    $hidebannedon = $hidebannedoff = '';
    settingHTML('hide_banned', $hidebannedon, $hidebannedoff);

    $imageshttpson = $imageshttpsoff = '';
    settingHTML('images_https_only', $imageshttpson, $imageshttpsoff);

    $check12 = $check24 = '';
    if ('24' === $SETTINGS['timeformat']) {
        $check24 = $vars::cheHTML;
    } else {
        $check12 = $vars::cheHTML;
    }

    $indexShowBarCats = $indexShowBarTop = $indexShowBarNone = false;
    switch($SETTINGS['indexshowbar']) {
        case 1:
            $indexShowBarCats = true;
            break;
        case 3:
            $indexShowBarNone = true;
            break;
        default:
            $indexShowBarTop = true;
            break;
    }

    $spell_off_reason = '';
    if (!defined('PSPELL_FAST')) {
        $spell_off_reason = $lang['pspell_needed'];
        $SETTINGS['spellcheck'] = 'off';
    }

    $notifycheck[0] = $notifycheck[1] = $notifycheck[2] = false;
    if ($SETTINGS['notifyonreg'] == 'off') {
        $notifycheck[0] = true;
    } else if ($SETTINGS['notifyonreg'] == 'u2u') {
        $notifycheck[1] = true;
    } else {
        $notifycheck[2] = true;
    }

    $allowipreg[0] = $allowipreg[1] = false;
    if ($SETTINGS['ipreg'] == 'on') {
        $allowipreg[0] = true;
    } else {
        $allowipreg[1] = true;
    }

    $footer_options = explode('-', $SETTINGS['footer_options']);
    if (in_array('serverload', $footer_options)) {
        $sel_serverload = true;
    } else {
        $sel_serverload = false;
    }

    if (in_array('queries', $footer_options)) {
        $sel_queries = true;
    } else {
        $sel_queries = false;
    }

    if (in_array('phpsql', $footer_options)) {
        $sel_phpsql = true;
    } else {
        $sel_phpsql = false;
    }

    if (in_array('loadtimes', $footer_options)) {
        $sel_loadtimes = true;
    } else {
        $sel_loadtimes = false;
    }

    $avchecked[0] = $avchecked[1] = $avchecked[2] = false;
    if ($SETTINGS['avastatus'] == 'list') {
        $avchecked[1] = true;
    } else if ($SETTINGS['avastatus'] == 'off') {
        $avchecked[2] = true;
    } else {
        $avchecked[0] = true;
    }
    
    $tickercodechecked = [ $SETTINGS['tickercode'] == 'plain', $SETTINGS['tickercode'] == 'bbcode', $SETTINGS['tickercode'] == 'html' ];

    $values = array('serverload', 'queries', 'phpsql', 'loadtimes');
    $names = array($lang['Enable_Server_Load'], $lang['Enable_Queries'], $lang['Enable_PHP_SQL'], $lang['Enable_Page_load']);
    $checked = array($sel_serverload, $sel_queries, $sel_phpsql, $sel_loadtimes);

    $max_avatar_sizes = explode('x', $SETTINGS['max_avatar_size']);
    $lang['spell_checker'] .= $spell_off_reason;
    ?>
    <tr bgcolor="<?= $THEME['altbg2'] ?>">
    <td align="center">
    <span class="smalltxt">
    <a href="#1"><?= $lang['admin_main_settings1']; ?></a><br />
    <a href="#2"><?= $lang['admin_main_settings2']; ?></a><br />
    <a href="#3"><?= $lang['admin_main_settings3']; ?></a><br />
    <a href="#4"><?= $lang['admin_main_settings4']; ?></a><br />
    <a href="#9"><?= $lang['admin_main_settings9']; ?></a><br />
    <a href="#5"><?= $lang['admin_main_settings5']; ?></a><br />
    <a href="#8"><?= $lang['admin_main_settings8']; ?></a><br />
    <a href="#6"><?= $lang['admin_main_settings6']; ?></a><br />
    <a href="#7"><?= $lang['admin_main_settings7']; ?></a><br />
    <a href="#10"><?= $lang['admin_main_settings10']; ?></a><br />
    </span>
    <form method="post" action="<?= $vars->full_url ?>admin/settings.php">
    <input type="hidden" name="token" value="<?= $token->create('Control Panel/settings', 'global', X_NONCE_FORM_EXP); ?>" />
    <table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center">
    <tr>
    <td bgcolor="<?= $THEME['bordercolor'] ?>">
    <table border="0" cellspacing="<?= $THEME['borderwidth']?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%">
    <tr class="category">
    <td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="1" />&raquo;&nbsp;<?= $lang['admin_main_settings1']?></font></strong></td>
    </tr>
    <?php
    printsetting2($lang['textsitename'], 'sitenamenew', $SETTINGS['sitename'], 50);
    printsetting2($lang['bbname'], 'bbnamenew', $SETTINGS['bbname'], 50);
    printsetting2($lang['textsiteurl'], 'siteurlnew', $SETTINGS['siteurl'], 50);
    printsetting2($lang['adminemail'], 'adminemailnew', $SETTINGS['adminemail'], 50);
    printsetting1($lang['textbbrules'], 'bbrulesnew', $ruleson, $rulesoff);
    ?>
    <?php
    printsetting4($lang['textbbrulestxt'], 'bbrulestxtnew', cdataOut($SETTINGS['bbrulestxt']), 5, 50);
    printsetting1($lang['textbstatus'], 'bbstatusnew', $onselect, $offselect);
    printsetting4($lang['textbboffreason'], 'bboffreasonnew', $SETTINGS['bboffreason'], 5, 50);
    printsetting1($lang['gzipcompression'], 'gzipcompressnew', $gzipcompresson, $gzipcompressoff);
    ?>
    <tr class="ctrtablerow">
    <td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit1" value="<?= $lang['textsubmitchanges']?>" /></td>
    </tr>
    <tr class="category">
    <td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="2" />&raquo;&nbsp;<?= $lang['admin_main_settings2']?></font></strong></td>
    </tr>
    <tr class="tablerow">
    <td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textlanguage' ]?></td>
    <td bgcolor="<?= $THEME['altbg2'] ?>"><?= $langfileselect ?></td>
    </tr>
    <tr class="tablerow">
    <td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['texttheme'] ?></td>
    <td bgcolor="<?= $THEME['altbg2'] ?>"><?= $themelist ?></td>
    </tr>
    <?php
    printsetting2($lang['textppp'], 'postperpagenew', ((int)$SETTINGS['postperpage']), 3);
    printsetting2($lang['texttpp'], 'topicperpagenew', ((int)$SETTINGS['topicperpage']), 3);
    printsetting2($lang['textmpp'], 'memberperpagenew', ((int)$SETTINGS['memberperpage']), 3);
    ?>
    <tr class="tablerow">
    <td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['texttimeformat']?></td>
    <td bgcolor="<?= $THEME['altbg2'] ?>"><input type="radio" value="24" name="timeformatnew" <?= $check24 ?> />&nbsp;<?= $lang['text24hour']?>&nbsp;<input type="radio" value="12" name="timeformatnew" <?= $check12?> />&nbsp;<?= $lang['text12hour']?></td>
    </tr>
    <?php
    printsetting2($lang['dateformat'], 'dateformatnew', $SETTINGS['dateformat'], 20);
    ?>
    <tr class="ctrtablerow">
    <td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit2" value="<?= $lang['textsubmitchanges']?>" /></td>
    </tr>
    <tr class="category">
    <td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="3" />&raquo;&nbsp;<?= $lang['admin_main_settings3']?></font></strong></td>
    </tr>
    <?php
    printsetting1($lang['textsearchstatus'], 'searchstatusnew', $searchon, $searchoff);
    printsetting1($lang['textfaqstatus'], 'faqstatusnew', $faqon, $faqoff);
    printsetting1($lang['texttodaystatus'], 'todaystatusnew', $todayon, $todayoff);
    printsetting1($lang['textstatsstatus'], 'statsstatusnew', $statson,  $statsoff);
    printsetting1($lang['textmemliststatus'], 'memliststatusnew', $memliston, $memlistoff);
    printsetting1($lang['spell_checker'], 'spellchecknew', $spellcheckon, $spellcheckoff);
    printsetting1($lang['coppastatus'], 'coppanew', $coppaon, $coppaoff);
    printsetting1($lang['reportpoststatus'], 'reportpostnew', $reportposton, $reportpostoff);
    ?>
    <tr class="ctrtablerow">
    <td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit3" value="<?= $lang['textsubmitchanges'] ?>" /></td>
    </tr>
    <tr class="category">
    <td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="4" />&raquo;&nbsp;<?= $lang['admin_main_settings4'] ?></font></strong></td>
    </tr>
    <?php
    printsetting1($lang['showsubforums'], 'showsubforumsnew', $showsubson, $showsubsoff);
    printsetting1($lang['space_cats'], 'space_catsnew', $spacecatson, $spacecatsoff);
    printsetting3($lang['indexShowBarDesc'], 'indexShowBarNew', array($lang['indexShowBarCats'], $lang['indexShowBarTop'], $lang['indexShowBarNone']), array(1, 2, 3), array($indexShowBarCats, $indexShowBarTop, $indexShowBarNone), false);
    printsetting1($lang['quickreply_status'], 'quickreply_statusnew', $quickreply_statuson, $quickreply_statusoff);
    printsetting1($lang['quickjump_status'], 'quickjump_statusnew', $quickjump_statuson, $quickjump_statusoff);
    printsetting1($lang['allowrankedit'], 'allowrankeditnew', $allowrankediton, $allowrankeditoff);
    printsetting1($lang['subjectInTitle'], 'subjectInTitleNew', $subjectInTitleOn, $subjectInTitleOff);
    printsetting2($lang['smtotal'], 'smtotalnew', ((int)$SETTINGS['smtotal']), 5);
    printsetting2($lang['smcols'], 'smcolsnew', ((int)$SETTINGS['smcols']), 5);
    printsetting1($lang['dotfolders'], 'dotfoldersnew', $dotfolderson, $dotfoldersoff);
    printsetting1($lang['editedby'], 'editedbynew', $editedbyon, $editedbyoff);
    printsetting1($lang['show_logs_in_threads'], 'showlogsnew', $showlogson, $showlogsoff);
    ?>
    <tr class="ctrtablerow">
    <td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit4" value="<?= $lang['textsubmitchanges']?>" /></td>
    </tr>
    <tr class="category">
    <td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="9" />&raquo;&nbsp;<?= $lang['admin_main_settings9']?></font></strong></td>
    </tr>
    <?php
    printsetting1($lang['index_stats'], 'index_statsnew', $index_statson, $index_statsoff);
    printsetting1($lang['textcatsonly'], 'catsonlynew', $catsonlyon, $catsonlyoff);
    printsetting1($lang['whosonline_on'], 'whos_on', $whosonlineon, $whosonlineoff);
    printsetting1($lang['onlinetoday_status'], 'onlinetoday_statusnew', $onlinetoday_statuson, $onlinetoday_statusoff);
    printsetting2($lang['max_onlinetodaycount'], 'onlinetodaycountnew', ((int)$SETTINGS['onlinetodaycount']), 5);
    printsetting1($lang['what_tickerstatus'], 'tickerstatusnew', $tickerstatuson, $tickerstatusoff);
    printsetting2($lang['what_tickerdelay'], 'tickerdelaynew', ((int)$SETTINGS['tickerdelay']), 5);
    printsetting4($lang['tickercontents'], 'tickercontentsnew', $SETTINGS['tickercontents'], 5, 50);
    printsetting3($lang['tickercode'], 'tickercodenew', array($lang['plaintext'], $lang['textbbcode'], $lang['texthtml']), array('plain', 'bbcode', 'html'), $tickercodechecked, false);
    ?>
    <tr class="ctrtablerow">
    <td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit5" value="<?= $lang['textsubmitchanges']?>" /></td>
    </tr>
    <tr class="category">
    <td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="5" />&raquo;&nbsp;<?= $lang['admin_main_settings5']?></font></strong></td>
    </tr>
    <?php
    printsetting1($lang['reg_on'], 'reg_on', $regon, $regoff);
    printsetting3($lang['ipreg'], 'ipReg', array($lang['texton'], $lang['textoff']), array('on', 'off'), $allowipreg, false);
    printsetting2($lang['max_daily_regs'], 'maxDayReg', ((int)$SETTINGS['maxdayreg']), 3);
    printsetting3($lang['notifyonreg'], 'notifyonregnew', array($lang['textoff'], $lang['viau2u'], $lang['viaemail']), array('off', 'u2u', 'email'), $notifycheck, false);
    printsetting1($lang['textreggedonly'], 'regviewnew', $regonlyon, $regonlyoff);
    printsetting1($lang['texthidepriv'], 'hidepriv', $hideon, $hideoff);
    printsetting1($lang['emailverify'], 'emailchecknew', $echeckon, $echeckoff);
    printsetting1($lang['regoptional'], 'regoptionalnew', $regoptionalon, $regoptionaloff);
    printsetting2($lang['textflood'], 'floodctrlnew', ((int)$SETTINGS['floodctrl']), 3);
    printsetting2($lang['u2uquota'], 'u2uquotanew', ((int)$SETTINGS['u2uquota']), 3);
    printsetting3($lang['textavastatus'], 'avastatusnew', array($lang['texton'], $lang['textlist'], $lang['textoff']), array('on', 'list', 'off'), $avchecked, false);
    printsetting1($lang['images_https_only'], 'imageshttpsnew', $imageshttpson, $imageshttpsoff);
    printsetting1($lang['resetSigDesc'], 'resetSigNew', $resetSigOn, $resetSigOff);
    printsetting1($lang['doublee'], 'doubleenew', $doubleeon, $doubleeoff);
    printsetting2($lang['pruneusers'], 'pruneusersnew', ((int)$SETTINGS['pruneusers']), 3);
    printsetting1($lang['moderation_setting'], 'quarantinenew', $quarantineon, $quarantineoff);
    printsetting1($lang['hide_banned_users'], 'hidebannednew', $hidebannedon, $hidebannedoff);
    ?>
    <tr class="ctrtablerow">
    <td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit6" value="<?= $lang['textsubmitchanges']?>" /></td>
    </tr>
    <tr class="category">
    <td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="8" />&raquo;&nbsp;<?= $lang['admin_main_settings8']?></font></strong></td>
    </tr>
    <?php
    if (! ini_get('file_uploads')) {
        printsetting5($lang['status'], 'The file upload feature is disabled.  Please check the configuration of your PHP server.');
    }
    $max_image_sizes = explode('x', $SETTINGS['max_image_size']);
    $max_thumb_sizes = explode('x', $SETTINGS['max_thumb_size']);
    for($i=0; $i<=4; $i++) {
        $urlformatchecked[$i] = ($SETTINGS['file_url_format'] == $i + 1);
    }
    for($i=0; $i<=1; $i++) {
        $subdirchecked[$i] = ($SETTINGS['files_subdir_format'] == $i + 1);
    }
    printsetting2($lang['textfilesperpost'], 'filesperpostnew', ((int)$SETTINGS['filesperpost']), 3);
    printsetting2($lang['max_attachment_size'], 'maxAttachSize', min(phpShorthandValue('upload_max_filesize'), (int) $SETTINGS['maxattachsize']), 12);
    printsetting2($lang['textfilessizew'], 'max_image_size_w_new', $max_image_sizes[0], 5);
    printsetting2($lang['textfilessizeh'], 'max_image_size_h_new', $max_image_sizes[1], 5);
    printsetting2($lang['textfilesthumbw'], 'max_thumb_size_w_new', $max_thumb_sizes[0], 5);
    printsetting2($lang['textfilesthumbh'], 'max_thumb_size_h_new', $max_thumb_sizes[1], 5);
    if (!ini_get('allow_url_fopen')) {
        printsetting5($lang['attachimginpost'], $lang['no_url_fopen']);
    } else {
        printsetting1($lang['attachimginpost'], 'attachimgpostnew', $attachimgposton, $attachimgpostoff);
    }
    printsetting1($lang['textremoteimages'], 'remoteimages', $remoteimageson, $remoteimagesoff);
    printsetting2($lang['textfilespath'], 'filespathnew', $SETTINGS['files_storage_path'], 50);
    printsetting2($lang['textfilesminsize'], 'filesminsizenew', ((int)$SETTINGS['files_min_disk_size']), 7);
    printsetting3($lang['textfilessubdir'], 'filessubdirnew', array($lang['textfilessubdir1'], $lang['textfilessubdir2']), array('1', '2'), $subdirchecked, false);
    printsetting3($lang['textfilesurlpath'], 'filesurlpathnew', array($lang['textfilesurlpath1'], $lang['textfilesurlpath2'], $lang['textfilesurlpath3'], $lang['textfilesurlpath4'], $lang['textfilesurlpath5']), array('1', '2', '3', '4', '5'), $urlformatchecked, false);
    printsetting2($lang['textfilesbase'], 'filesbasenew', $SETTINGS['files_virtual_url'], 50);
    ?>
    <tr class="ctrtablerow">
    <td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit7" value="<?= $lang['textsubmitchanges']?>" /></td>
    </tr>
    <tr class="category">
    <td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="6" />&raquo;&nbsp;<?= $lang['admin_main_settings6']?></font></strong></td>
    </tr>
    <?php
    printsetting2($lang['texthottopic'], 'hottopicnew', ((int)$SETTINGS['hottopic']), 3);
    printsetting1($lang['bbinsert'], 'bbinsertnew', $bbinserton, $bbinsertoff);
    printsetting1($lang['smileyinsert'], 'smileyinsertnew', $smileyinserton, $smileyinsertoff);
    printsetting3($lang['footer_options'], 'new_footer_options', $names, $values, $checked);
    printsetting5($lang['defaultTimezoneDesc'], $core->timezone_control($SETTINGS['def_tz']));
    printsetting2($lang['addtime'], 'addtimenew', $SETTINGS['addtime'], 3);
    printsetting1($lang['sigbbcode'], 'sigbbcodenew', $sigbbcodeon, $sigbbcodeoff);
    if (!ini_get('allow_url_fopen')) {
        printsetting5($lang['max_avatar_size_w'], $lang['no_url_fopen']);
    } else {
        printsetting2($lang['max_avatar_size_w'], 'max_avatar_size_w_new', $max_avatar_sizes[0], 4);
        printsetting2($lang['max_avatar_size_h'], 'max_avatar_size_h_new', $max_avatar_sizes[1], 4);
    }
    ?>
    <tr class="ctrtablerow">
    <td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit8" value="<?= $lang['textsubmitchanges']?>" /></td>
    </tr>
    <tr class="category">
    <td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="7" />&raquo;&nbsp;<?= $lang['admin_main_settings7']?></font></strong></td>
    </tr>
    <?php
    require ROOT.'include/captcha.inc.php';
    $Captcha = new Captcha($core, $observer, $vars);
    if ($Captcha->bCompatible === FALSE) {
        printsetting5($lang['captchastatus'], 'CAPTCHA is not working. Usually, this means the GD or FreeType software is missing from your PHP server.');
    } else {
        printsetting1($lang['captchastatus'], 'captchanew', $captchaOn, $captchaOff);
        printsetting1($lang['captcharegstatus'], 'captcharegnew', $captcharegOn, $captcharegOff);
        printsetting1($lang['captchapoststatus'], 'captchapostnew', $captchapostOn, $captchapostOff);
        printsetting1($lang['captchasearchstatus'], 'captchasearchnew', $captchasearchOn, $captchasearchOff);
        printsetting2($lang['captchacharset'], 'captchacharsetnew', $SETTINGS['captcha_code_charset'], 50);
        printsetting2($lang['captchacodelength'], 'captchacodenew', ((int)$SETTINGS['captcha_code_length']), 3);
        printsetting1($lang['captchacodecase'], 'captchacodecasenew', $captchacodecaseOn, $captchacodecaseOff);
        printsetting1($lang['captchacodeshadow'], 'captchacodeshadownew', $captchacodeshadowOn, $captchacodeshadowOff);
        printsetting2($lang['captchaimagetype'], 'captchaimagetypenew', $SETTINGS['captcha_image_type'], 5);
        printsetting2($lang['captchaimagewidth'], 'captchaimagewidthnew', ((int)$SETTINGS['captcha_image_width']), 5);
        printsetting2($lang['captchaimageheight'], 'captchaimageheightnew', ((int)$SETTINGS['captcha_image_height']), 5);
        printsetting2($lang['captchaimagebg'], 'captchaimagebgnew', $SETTINGS['captcha_image_bg'], 50);
        printsetting2($lang['captchaimagedots'], 'captchaimagedotsnew', ((int)$SETTINGS['captcha_image_dots']), 3);
        printsetting2($lang['captchaimagelines'], 'captchaimagelinesnew', ((int)$SETTINGS['captcha_image_lines']), 3);
        printsetting2($lang['captchaimagefonts'], 'captchaimagefontsnew', $SETTINGS['captcha_image_fonts'], 50);
        printsetting2($lang['captchaimageminfont'], 'captchaimageminfontnew', ((int)$SETTINGS['captcha_image_minfont']), 3);
        printsetting2($lang['captchaimagemaxfont'], 'captchaimagemaxfontnew', ((int)$SETTINGS['captcha_image_maxfont']), 3);
        printsetting1($lang['captchaimagecolor'], 'captchaimagecolornew', $captchaimagecolorOn, $captchaimagecolorOff);
    }
    unset($Captcha);
    ?>
    <tr class="ctrtablerow">
    <td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit9" value="<?= $lang['textsubmitchanges']?>" /></td>
    </tr>
    <tr class="category">
    <td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="10" />&raquo;&nbsp;<?= $lang['admin_main_settings10']?></font></strong></td>
    </tr>
    <?php
    $recaptcha_link = '<br /><span class="smalltext">[ <a href="https://www.google.com/recaptcha/admin/" onclick="window.open(this.href); return false;">Setup</a> ]';
    printsetting1($lang['google_captcha_onoff'], 'recaptchanew', $recaptchaon, $recaptchaoff);
    printsetting2($lang['google_captcha_sitekey'].$recaptcha_link, 'recaptchakeynew', $SETTINGS['google_captcha_sitekey'], 50);
    printsetting2($lang['google_captcha_secretkey'], 'recaptchasecretnew', $SETTINGS['google_captcha_secret'], 50);
    ?>
    <tr class="ctrtablerow">
    <td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit10" value="<?= $lang['textsubmitchanges']?>" /></td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </form>
    </td>
    </tr>
    <?php
} else {
    $core->request_secure('Control Panel/settings', 'global');

    $spellchecknew = ($_POST['spellchecknew'] == 'on' && defined('PSPELL_FAST')) ? 'on' : 'off';
    $notifyonregnew = ($_POST['notifyonregnew'] == 'off') ? 'off' : ($_POST['notifyonregnew'] == 'u2u' ? 'u2u' : 'email');
    $avastatusnew = $core->postedVar('avastatusnew');
    if ($avastatusnew != 'on' && $avastatusnew != 'list') {
        $avastatusnew = 'off';
    }
    $recaptchanew = $core->postedVar('recaptchanew');
    if ($recaptchanew != 'on' || trim($core->postedVar('recaptchasecretnew')) == '' || trim($core->postedVar('recaptchakeynew')) == '') {
        $recaptchanew = 'off';
    }

    $new_footer_options = $core->postedArray('new_footer_options');
    if (!empty($new_footer_options)) {
        $footer_options = implode('-', $new_footer_options);
    } else {
        $footer_options = '';
    }

    $maxAttachSize = (string) min(phpShorthandValue('upload_max_filesize'), formInt('maxAttachSize'));
    $def_tz_new = isset($_POST['timeoffset1']) && is_numeric($_POST['timeoffset1']) ? $_POST['timeoffset1'] : '0';
    $addtimenew = isset($_POST['addtimenew']) && is_numeric($_POST['addtimenew']) ? $_POST['addtimenew'] : '0';
    $max_avatar_size_w_new = formInt('max_avatar_size_w_new');
    $max_avatar_size_h_new = formInt('max_avatar_size_h_new');
    $max_avatar_size = $max_avatar_size_w_new.'x'.$max_avatar_size_h_new;

    $max_image_size_w_new = formInt('max_image_size_w_new');
    $max_image_size_h_new = formInt('max_image_size_h_new');
    $max_thumb_size_w_new = formInt('max_thumb_size_w_new');
    $max_thumb_size_h_new = formInt('max_thumb_size_h_new');
    $max_image_size = $max_image_size_w_new.'x'.$max_image_size_h_new;
    $max_thumb_size = $max_thumb_size_w_new.'x'.$max_thumb_size_h_new;

    input_custom_setting('addtime', $addtimenew);
    input_string_setting('adminemail', 'adminemailnew');
    input_onoff_setting('allowrankedit', 'allowrankeditnew');
    input_onoff_setting('attachimgpost', 'attachimgpostnew');
    input_onoff_setting('attach_remote_images', 'remoteimages');
    input_custom_setting('avastatus', $avastatusnew);
    input_onoff_setting('bbinsert', 'bbinsertnew');
    input_string_setting('bbname', 'bbnamenew');
    input_string_setting('bboffreason', 'bboffreasonnew');
    input_onoff_setting('bbrules', 'bbrulesnew');
    input_string_setting('bbrulestxt', 'bbrulestxtnew', false);
    input_onoff_setting('bbstatus', 'bbstatusnew');
    input_onoff_setting('captcha_code_casesensitive', 'captchacodecasenew');
    input_string_setting('captcha_code_charset', 'captchacharsetnew');
    input_int_setting('captcha_code_length', 'captchacodenew');
    input_onoff_setting('captcha_code_shadow', 'captchacodeshadownew');
    input_string_setting('captcha_image_bg', 'captchaimagebgnew');
    input_onoff_setting('captcha_image_color', 'captchaimagecolornew');
    input_int_setting('captcha_image_dots', 'captchaimagedotsnew');
    input_string_setting('captcha_image_fonts', 'captchaimagefontsnew');
    input_int_setting('captcha_image_height', 'captchaimageheightnew');
    input_int_setting('captcha_image_lines', 'captchaimagelinesnew');
    input_int_setting('captcha_image_maxfont', 'captchaimagemaxfontnew');
    input_int_setting('captcha_image_minfont', 'captchaimageminfontnew');
    input_string_setting('captcha_image_type', 'captchaimagetypenew');
    input_int_setting('captcha_image_width', 'captchaimagewidthnew');
    input_onoff_setting('captcha_post_status', 'captchapostnew');
    input_onoff_setting('captcha_reg_status', 'captcharegnew');
    input_onoff_setting('captcha_search_status', 'captchasearchnew');
    input_onoff_setting('captcha_status', 'captchanew');
    input_onoff_setting('catsonly', 'catsonlynew');
    input_onoff_setting('coppa', 'coppanew');
    input_string_setting('dateformat', 'dateformatnew');
    input_custom_setting('def_tz', $def_tz_new);
    input_onoff_setting('dotfolders', 'dotfoldersnew');
    input_onoff_setting('doublee', 'doubleenew');
    input_onoff_setting('editedby', 'editedbynew');
    input_onoff_setting('emailcheck', 'emailchecknew');
    input_onoff_setting('faqstatus', 'faqstatusnew');
    input_int_setting('filesperpost', 'filesperpostnew');
    input_int_setting('files_min_disk_size', 'filesminsizenew');
    input_string_setting('files_storage_path', 'filespathnew');
    input_int_setting('files_subdir_format', 'filessubdirnew');
    input_int_setting('file_url_format', 'filesurlpathnew');
    input_string_setting('files_virtual_url', 'filesbasenew');
    input_int_setting('floodctrl', 'floodctrlnew');
    input_custom_setting('footer_options', $footer_options);
    input_custom_setting('google_captcha', $recaptchanew);
    input_string_setting('google_captcha_secret', 'recaptchasecretnew');
    input_string_setting('google_captcha_sitekey', 'recaptchakeynew');
    input_onoff_setting('gzipcompress', 'gzipcompressnew');
    input_onoff_setting('hideprivate', 'hidepriv');
    input_onoff_setting('hide_banned', 'hidebannednew');
    input_int_setting('hottopic', 'hottopicnew');
    input_onoff_setting('images_https_only', 'imageshttpsnew');
    input_int_setting('indexshowbar', 'indexShowBarNew');
    input_onoff_setting('index_stats', 'index_statsnew');
    input_onoff_setting('ipreg', 'ipReg');
    input_string_setting('langfile', 'langfilenew');
    input_custom_setting('maxattachsize', $maxAttachSize);
    input_int_setting('maxdayreg', 'maxDayReg');
    input_custom_setting('max_avatar_size', $max_avatar_size);
    input_custom_setting('max_image_size', $max_image_size);
    input_custom_setting('max_thumb_size', $max_thumb_size);
    input_int_setting('memberperpage', 'memberperpagenew');
    input_onoff_setting('memliststatus', 'memliststatusnew');
    input_custom_setting('notifyonreg', $notifyonregnew);
    input_int_setting('onlinetodaycount', 'onlinetodaycountnew');
    input_onoff_setting('onlinetoday_status', 'onlinetoday_statusnew');
    input_int_setting('postperpage', 'postperpagenew');
    input_int_setting('pruneusers', 'pruneusersnew');
    input_onoff_setting('quarantine_new_users', 'quarantinenew');
    input_onoff_setting('quickjump_status', 'quickjump_statusnew');
    input_onoff_setting('quickreply_status', 'quickreply_statusnew');
    input_onoff_setting('regoptional', 'regoptionalnew');
    input_onoff_setting('regstatus', 'reg_on');
    input_onoff_setting('regviewonly', 'regviewnew');
    input_onoff_setting('reportpost', 'reportpostnew');
    input_onoff_setting('resetsigs', 'resetSigNew');
    input_onoff_setting('searchstatus', 'searchstatusnew');
    input_onoff_setting('showsubforums', 'showsubforumsnew');
    input_onoff_setting('show_logs_in_threads', 'showlogsnew');
    input_onoff_setting('sigbbcode', 'sigbbcodenew');
    input_string_setting('sitename', 'sitenamenew');
    input_string_setting('siteurl', 'siteurlnew');
    input_int_setting('smcols', 'smcolsnew');
    input_onoff_setting('smileyinsert', 'smileyinsertnew');
    input_int_setting('smtotal', 'smtotalnew');
    input_onoff_setting('space_cats', 'space_catsnew');
    input_custom_setting('spellcheck', $spellchecknew);
    input_onoff_setting('stats', 'statsstatusnew');
    input_onoff_setting('subject_in_title', 'subjectInTitleNew');
    input_int_setting('theme', 'themenew');
    input_string_setting('tickercode', 'tickercodenew');
    input_string_setting('tickercontents', 'tickercontentsnew');
    input_int_setting('tickerdelay', 'tickerdelaynew');
    input_onoff_setting('tickerstatus', 'tickerstatusnew');
    input_int_setting('timeformat', 'timeformatnew');
    input_onoff_setting('todaysposts', 'todaystatusnew');
    input_int_setting('topicperpage', 'topicperpagenew');
    input_int_setting('u2uquota', 'u2uquotanew');
    input_onoff_setting('whosonlinestatus', 'whos_on');

    echo '<tr bgcolor="' . $THEME['altbg2'] . '" class="ctrtablerow"><td>'.$lang['textsettingsupdate'].'</td></tr>';
}


$close = '</table></td></tr></table>';
$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $close, $footer;
