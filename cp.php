<?php
/**
 * eXtreme Message Board
 * XMB 1.9.8 Engage Final SP3
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2008, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * Copyright (c) 2007, iEntry, Inc.
 * http://www.ientry.com
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

define('X_SCRIPT', 'cp.php');

require 'header.php';
require ROOT.'include/admin.inc.php';

loadtemplates('error_nologinsession');

nav($lang['textcp']);

eval('$css = "'.template('css').'";');
eval('echo "'.template('header').'";');
echo '<script language="JavaScript" type="text/javascript" src="./js/admin.js"></script>';

if (!X_ADMIN) {
    eval('echo stripslashes("'.template('error_nologinsession').'");');
    end_time();
    eval('echo "'.template('footer').'";');
    exit();
}

$auditaction = $_SERVER['REQUEST_URI'];
$aapos = strpos($auditaction, "?");
if ($aapos !== false) {
    $auditaction = substr($auditaction, $aapos + 1);
}
$auditaction = addslashes("$onlineip|#|$auditaction");
audit($xmbuser, $auditaction, 0, 0);

displayAdminPanel();

$action = getVar('action');

if ($action == "settings") {
    if (noSubmit('settingsubmit')) {
        $langfileselect = createLangFileSelect($SETTINGS['langfile']);

        $themelist = array();
        $themelist[] = '<select name="themenew">';
        $query = $db->query("SELECT themeid, name FROM ".X_PREFIX."themes ORDER BY name ASC");
        while($themeinfo = $db->fetch_array($query)) {
            if ($themeinfo['themeid'] == $SETTINGS['theme']) {
                $themelist[] = '<option value="'.intval($themeinfo['themeid']).'" '.$selHTML.'>'.stripslashes($themeinfo['name']).'</option>';
            } else {
                $themelist[] = '<option value="'.intval($themeinfo['themeid']).'">'.stripslashes($themeinfo['name']).'</option>';
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

        $sightmlon = $sightmloff = '';
        settingHTML('sightml', $sightmlon, $sightmloff);

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

        $avataron = $avataroff = $avatarlist = '';
        if ($SETTINGS['avastatus'] == 'on') {
            $avataron = $selHTML;
        } else if ($avastatus == 'list') {
            $avatarlist = $selHTML;
        } else {
            $avataroff = $selHTML;
        }

        $check12 = $check24 = '';
        if ($SETTINGS['timeformat'] == 24) {
            $check24 = $cheHTML;
        } else {
            $check12 = $cheHTML;
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
        if (!empty($avatarlist)) {
            $avchecked[1] = true;
        } else if (!empty($avataroff)) {
            $avchecked[2] = true;
        } else {
            $avchecked[0] = true;
        }

        $timezone1 = $timezone2 = $timezone3 = $timezone4 = $timezone5 = $timezone6 = false;
        $timezone7 = $timezone8 = $timezone9 = $timezone10 = $timezone11 = $timezone12 = false;
        $timezone13 = $timezone14 = $timezone15 = $timezone16 = $timezone17 = $timezone18 = false;
        $timezone19 = $timezone20 = $timezone21 = $timezone22 = $timezone23 = $timezone24 = false;
        $timezone25 = $timezone26 = $timezone27 = $timezone28 = $timezone29 = $timezone30 = false;
        $timezone31 = $timezone32 = $timezone33 = false;
        switch($SETTINGS['def_tz']) {
            case '-12.00':
                $timezone1 = true;
                break;
            case '-11.00':
                $timezone2 = true;
                break;
            case '-10.00':
                $timezone3 = true;
                break;
            case '-9.00':
                $timezone4 = true;
                break;
            case '-8.00':
                $timezone5 = true;
                break;
            case '-7.00':
                $timezone6 = true;
                break;
            case '-6.00':
                $timezone7 = true;
                break;
            case '-5.00':
                $timezone8 = true;
                break;
            case '-4.00':
                $timezone9 = true;
                break;
            case '-3.50':
                $timezone10 = true;
                break;
            case '-3.00':
                $timezone11 = true;
                break;
            case '-2.00':
                $timezone12 = true;
                break;
            case '-1.00':
                $timezone13 = true;
                break;
            case '1.00':
                $timezone15 = true;
                break;
            case '2.00':
                $timezone16 = true;
                break;
            case '3.00':
                $timezone17 = true;
                break;
            case '3.50':
                $timezone18 = true;
                break;
            case '4.00':
                $timezone19 = true;
                break;
            case '4.50':
                $timezone20 = true;
                break;
            case '5.00':
                $timezone21 = true;
                break;
            case '5.50':
                $timezone22 = true;
                break;
            case '5.75':
                $timezone23 = true;
                break;
            case '6.00':
                $timezone24 = true;
                break;
            case '6.50':
                $timezone25 = true;
                break;
            case '7.00':
                $timezone26 = true;
                break;
            case '8.00':
                $timezone27 = true;
                break;
            case '9.00':
                $timezone28 = true;
                break;
            case '9.50':
                $timezone29 = true;
                break;
            case '10.00':
                $timezone30 = true;
                break;
            case '11.00':
                $timezone31 = true;
                break;
            case '12.00':
                $timezone32 = true;
                break;
            case '13.00':
                $timezone33 = true;
                break;
            case '0.00':
            default:
                $timezone14 = true;
                break;
        }

        $values = array('serverload', 'queries', 'phpsql', 'loadtimes');
        $names = array($lang['Enable_Server_Load'], $lang['Enable_Queries'], $lang['Enable_PHP_SQL'], $lang['Enable_Page_load']);
        $checked = array($sel_serverload, $sel_queries, $sel_phpsql, $sel_loadtimes);

        $max_avatar_sizes = explode('x', $SETTINGS['max_avatar_size']);
        $lang['spell_checker'] .= $spell_off_reason;
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp.php?action=settings">
        <table cellspacing="0" cellpadding="0" border="0" width="<?php echo $tablewidth?>" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings1']?></font></strong></td>
        </tr>
        <?php
        printsetting2($lang['textsitename'], 'sitenamenew', stripslashes($SETTINGS['sitename']), 50);
        printsetting2($lang['bbname'], 'bbnamenew', stripslashes($SETTINGS['bbname']), 50);
        printsetting2($lang['textsiteurl'], 'siteurlnew', stripslashes($SETTINGS['siteurl']), 50);
        printsetting2($lang['textboardurl'], 'boardurlnew', stripslashes($SETTINGS['boardurl']), 50);
        printsetting2($lang['adminemail'], 'adminemailnew', stripslashes($SETTINGS['adminemail']), 50);
        printsetting1($lang['textbbrules'], 'bbrulesnew', $ruleson, $rulesoff);
        ?>
        <?php
        printsetting4($lang['textbbrulestxt'], 'bbrulestxtnew', stripslashes($SETTINGS['bbrulestxt']), 5, 50);
        printsetting1($lang['textbstatus'], 'bbstatusnew', $onselect, $offselect);
        printsetting4($lang['textbboffreason'], 'bboffreasonnew', stripslashes($SETTINGS['bboffreason']), 5, 50);
        printsetting1($lang['gzipcompression'], 'gzipcompressnew', $gzipcompresson, $gzipcompressoff);
        ?>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg2?>" colspan="2">&nbsp;</td>
        </tr>
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings2']?></font></strong></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['textlanguage']?></td>
        <td bgcolor="<?php echo $altbg2?>"><?php echo $langfileselect?></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['texttheme']?></td>
        <td bgcolor="<?php echo $altbg2?>"><?php echo $themelist?></td>
        </tr>
        <?php
        printsetting2($lang['textppp'], 'postperpagenew', ((int)$SETTINGS['postperpage']), 3);
        printsetting2($lang['texttpp'], 'topicperpagenew', ((int)$SETTINGS['topicperpage']), 3);
        printsetting2($lang['textmpp'], 'memberperpagenew', ((int)$SETTINGS['memberperpage']), 3);
        ?>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['texttimeformat']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="radio" value="24" name="timeformatnew" <?php echo $check24?> />&nbsp;<?php echo $lang['text24hour']?>&nbsp;<input type="radio" value="12" name="timeformatnew" <?php echo $check12?> />&nbsp;<?php echo $lang['text12hour']?></td>
        </tr>
        <?php
        printsetting2($lang['dateformat'], 'dateformatnew', $SETTINGS['dateformat'], 20);
        ?>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg2?>" colspan="2">&nbsp;</td>
        </tr>
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings3']?></font></strong></td>
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
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg2?>" colspan="2">&nbsp;</td>
        </tr>
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings4']?></font></strong></td>
        </tr>
        <?php
        printsetting1($lang['showsubforums'], 'showsubforumsnew', $showsubson, $showsubsoff);
        printsetting1($lang['space_cats'], 'space_catsnew', $spacecatson, $spacecatsoff);
        printsetting3($lang['indexShowBarDesc'], 'indexShowBarNew', array($lang['indexShowBarCats'], $lang['indexShowBarTop'], $lang['indexShowBarNone']), array(1, 2, 3), array($indexShowBarCats, $indexShowBarTop, $indexShowBarNone), false);
        printsetting1($lang['index_stats'], 'index_statsnew', $index_statson, $index_statsoff);
        printsetting1($lang['quickreply_status'], 'quickreply_statusnew', $quickreply_statuson, $quickreply_statusoff);
        printsetting1($lang['quickjump_status'], 'quickjump_statusnew', $quickjump_statuson, $quickjump_statusoff);
        printsetting1($lang['allowrankedit'], 'allowrankeditnew', $allowrankediton, $allowrankeditoff);
        printsetting1($lang['subjectInTitle'], 'subjectInTitleNew', $subjectInTitleOn, $subjectInTitleOff);
        printsetting1($lang['textcatsonly'], 'catsonlynew', $catsonlyon, $catsonlyoff);
        printsetting1($lang['whosonline_on'], 'whos_on', $whosonlineon, $whosonlineoff);
        printsetting1($lang['onlinetoday_status'], 'onlinetoday_statusnew', $onlinetoday_statuson, $onlinetoday_statusoff);
        printsetting2($lang['max_onlinetodaycount'], 'onlinetodaycountnew', ((int)$SETTINGS['onlinetodaycount']), 5);
        printsetting2($lang['smtotal'], 'smtotalnew', ((int)$SETTINGS['smtotal']), 5);
        printsetting2($lang['smcols'], 'smcolsnew', ((int)$SETTINGS['smcols']), 5);
        printsetting1($lang['dotfolders'], 'dotfoldersnew', $dotfolderson, $dotfoldersoff);
        printsetting1($lang['editedby'], 'editedbynew', $editedbyon, $editedbyoff);
        printsetting1($lang['attachimginpost'], 'attachimgpostnew', $attachimgposton, $attachimgpostoff);
        ?>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg2?>" colspan="2">&nbsp;</td>
        </tr>
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings5']?></font></strong></td>
        </tr>
        <?php
        printsetting1($lang['reg_on'], 'reg_on', $regon, $regoff);
        printsetting3($lang['ipreg'], 'ipReg', array($lang['texton'], $lang['textoff']), array('on', 'off'), $allowipreg, false);
        printsetting2($lang['max_daily_regs'], 'maxDayReg', ((int)$SETTINGS['maxdayreg']), 3);
        printsetting3($lang['notifyonreg'], 'notifyonregnew', array($lang['textoff'], $lang['viau2u'], $lang['viaemail']), array('off', 'u2u', 'email'), $notifycheck, false);
        printsetting1($lang['textreggedonly'], 'regviewnew', $regonlyon, $regonlyoff);
        printsetting1($lang['texthidepriv'], 'hidepriv', $hideon, $hideoff);
        printsetting1($lang['emailverify'], 'emailchecknew',$echeckon, $echeckoff);
        printsetting1($lang['regoptional'], 'regoptionalnew',$regoptionalon, $regoptionaloff);
        printsetting2($lang['textflood'], 'floodctrlnew', ((int)$SETTINGS['floodctrl']), 3);
        printsetting2($lang['u2uquota'], 'u2uquotanew', ((int)$SETTINGS['u2uquota']), 3);
        printsetting3($lang['textavastatus'], 'avastatusnew', array($lang['texton'], $lang['textlist'], $lang['textoff']), array('on', 'list', 'off'), $avchecked, false);
        printsetting1($lang['resetSigDesc'], 'resetSigNew', $resetSigOn, $resetSigOff);
        printsetting1($lang['doublee'], 'doubleenew', $doubleeon, $doubleeoff);
        printsetting2($lang['pruneusers'], 'pruneusersnew', ((int)$SETTINGS['pruneusers']), 3);
        ?>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg2?>" colspan="2">&nbsp;</td>
        </tr>
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings6']?></font></strong></td>
        </tr>
        <?php
        printsetting2($lang['texthottopic'], 'hottopicnew', ((int)$SETTINGS['hottopic']), 3);
        printsetting1($lang['bbinsert'], 'bbinsertnew', $bbinserton, $bbinsertoff);
        printsetting1($lang['smileyinsert'], 'smileyinsertnew', $smileyinserton, $smileyinsertoff);
        printsetting3($lang['footer_options'], 'new_footer_options', $names, $values, $checked);
        printsetting2($lang['max_attachment_size'], 'maxAttachSize', ((int)$SETTINGS['maxattachsize']), 8);
        printsetting3($lang['defaultTimezoneDesc'], 'def_tz_new', array($lang['timezone1'], $lang['timezone2'], $lang['timezone3'], $lang['timezone4'], $lang['timezone5'], $lang['timezone6'], $lang['timezone7'], $lang['timezone8'], $lang['timezone9'], $lang['timezone10'], $lang['timezone11'], $lang['timezone12'], $lang['timezone13'], $lang['timezone14'], $lang['timezone15'], $lang['timezone16'], $lang['timezone17'], $lang['timezone18'], $lang['timezone19'], $lang['timezone20'], $lang['timezone21'], $lang['timezone22'], $lang['timezone23'], $lang['timezone24'], $lang['timezone25'], $lang['timezone26'], $lang['timezone27'], $lang['timezone28'], $lang['timezone29'], $lang['timezone30'], $lang['timezone31'], $lang['timezone32'], $lang['timezone33']), array('-12', '-11', '-10', '-9', '-8', '-7', '-6', '-5', '-4', '-3.5', '-3', '-2', '-1', '0', '1', '2', '3', '3.5', '4', '4.5', '5', '5.5', '5.75', '6', '6.5', '7', '8', '9', '9.5', '10', '11', '12', '13'), array($timezone1, $timezone2, $timezone3, $timezone4, $timezone5, $timezone6, $timezone7, $timezone8, $timezone9, $timezone10, $timezone11, $timezone12, $timezone13, $timezone14, $timezone15, $timezone16, $timezone17, $timezone18, $timezone19, $timezone20, $timezone21, $timezone22, $timezone23, $timezone24, $timezone25, $timezone26, $timezone27, $timezone28, $timezone29, $timezone30, $timezone31, $timezone32, $timezone33), false);
        printsetting2($lang['addtime'], 'addtimenew', $SETTINGS['addtime'], 3);
        printsetting1($lang['sigbbcode'], 'sigbbcodenew', $sigbbcodeon, $sigbbcodeoff);
        printsetting1($lang['sightml'], 'sightmlnew', $sightmlon, $sightmloff);
        printsetting2($lang['max_avatar_size_w'], 'max_avatar_size_w_new', $max_avatar_sizes[0], 4);
        printsetting2($lang['max_avatar_size_h'], 'max_avatar_size_h_new', $max_avatar_sizes[1], 4);
        printsetting1($lang['what_tickerstatus'], 'tickerstatusnew', $tickerstatuson, $tickerstatusoff);
        printsetting2($lang['what_tickerdelay'], 'tickerdelaynew', ((int)$SETTINGS['tickerdelay']), 5);
        printsetting4($lang['tickercontents'], 'tickercontentsnew', stripslashes($SETTINGS['tickercontents']), 5, 50);
        ?>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg2?>" colspan="2">&nbsp;</td>
        </tr>
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings7']?></font></strong></td>
        </tr>
        <?php
        printsetting1($lang['captchastatus'], 'captchanew', $captchaOn, $captchaOff);
        printsetting1($lang['captcharegstatus'], 'captcharegnew', $captcharegOn, $captcharegOff);
        printsetting1($lang['captchapoststatus'], 'captchapostnew', $captchapostOn, $captchapostOff);
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
        ?>
        <tr class="ctrtablerow">
        <td bgcolor="<?php echo $altbg2?>" colspan="2"><input class="submit" type="submit" name="settingsubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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
        $sitenamenew = postedVar('sitenamenew');
        $bbnamenew = postedVar('bbnamenew');
        $siteurlnew = postedVar('siteurlnew');
        $boardurlnew = postedVar('boardurlnew');
        $adminemailnew = postedVar('adminemailnew');
        $bbrulesnew = formOnOff('bbrulesnew');
        $bbrulestxtnew = postedVar('bbrulestxtnew');
        $bbstatusnew = formOnOff('bbstatusnew');
        $bboffreasonnew = postedVar('bboffreasonnew');
        $gzipcompressnew = formOnOff('gzipcompressnew');

        $langfilenew = getLangFileNameFromHash($langfilenew);
        if (!$langfilenew) {
            $langfilenew = $SETTINGS['langfile'];
        } else {
            $langfilenew = basename($langfilenew);
        }

        $themenew = formInt('themenew');
        $postperpagenew = formInt('postperpagenew');
        $topicperpagenew = formInt('topicperpagenew');
        $memberperpagenew = formInt('memberperpagenew');
        $timeformatnew = formInt('timeformatnew');
        $dateformatnew = postedVar('dateformatnew');
        $searchstatusnew = formOnOff('searchstatusnew');
        $faqstatusnew = formOnOff('faqstatusnew');
        $todaystatusnew = formOnOff('todaystatusnew');
        $statsstatusnew = formOnOff('statsstatusnew');
        $memliststatusnew = formOnOff('memliststatusnew');
        $spellchecknew = ($_POST['spellchecknew'] == 'on' && defined('PSPELL_FAST')) ? 'on' : 'off';
        $coppanew = formOnOff('coppanew');
        $reportpostnew = formOnOff('reportpostnew');
        $space_catsnew = formOnOff('space_catsnew');
        $indexShowBarNew = formInt('indexShowBarNew');
        $allowrankeditnew = formOnOff('allowrankeditnew');
        $subjectInTitleNew = formOnOff('subjectInTitleNew');
        $catsonlynew = formOnOff('catsonlynew');
        $whos_on = formOnOff('whos_on');
        $smtotalnew = formInt('smtotalnew');
        $smcolsnew = formInt('smcolsnew');
        $dotfoldersnew = formOnOff('dotfoldersnew');
        $editedbynew = formOnOff('editedbynew');
        $attachimgpostnew = formOnOff('attachimgpostnew');
        $reg_on = formOnOff('reg_on');
        $ipReg = formOnOff('ipReg');
        $maxDayReg = formInt('maxDayReg');
        $notifyonregnew = ($_POST['notifyonregnew'] == 'off') ? 'off' : ($_POST['notifyonregnew'] == 'u2u' ? 'u2u' : 'email');
        $regviewnew = formOnOff('regviewnew');
        $hidepriv = formOnOff('hidepriv');
        $emailchecknew = formOnOff('emailchecknew');
        $floodctrlnew = formInt('floodctrlnew');
        $u2uquotanew = formInt('u2uquotanew');
        $avastatusnew = formOnOff('avastatusnew');
        $resetSigNew = formOnOff('resetSigNew');
        $doubleenew = formOnOff('doubleenew');
        $pruneusersnew = formInt('pruneusersnew');
        $hottopicnew = formInt('hottopicnew');
        $bbinsertnew = formOnOff('bbinsertnew');
        $smileyinsertnew = formOnOff('smileyinsertnew');

        $new_footer_options = formArray('new_footer_options');
        if (!empty($new_footer_options)) {
            $footer_options = implode('-', $new_footer_options);
        } else {
            $footer_options = '';
        }

        $maxAttachSize = formInt('maxAttachSize');
        $def_tz_new = formInt('def_tz_new');
        $addtimenew = isset($_POST['addtimenew']) && is_numeric($_POST['addtimenew']) ? $_POST['addtimenew'] : 0;
        $sigbbcodenew = formOnOff('sigbbcodenew');
        $sightmlnew = formOnOff('sightmlnew');
        $max_avatar_size_w_new = formInt('max_avatar_size_w_new');
        $max_avatar_size_h_new = formInt('max_avatar_size_h_new');
        $tickerdelaynew = formInt('tickerdelaynew');
        $maxDayReg = formInt('maxDayReg');
        $captchanew = formOnOff('captchanew');
        $captcharegnew = formOnOff('captcharegnew');
        $captchapostnew = formOnOff('captchapostnew');
        $captchacharsetnew = postedVar('captchacharsetnew');
        $captchacodenew = formInt('captchacodenew');
        $captchacodecasenew = formOnOff('captchacodecasenew');
        $captchacodeshadownew = formOnOff('captchacodeshadownew');
        $captchaimagetypenew = postedVar('captchaimagetypenew');
        $captchaimagewidthnew = formInt('captchaimagewidthnew');
        $captchaimageheightnew = formInt('captchaimageheightnew');
        $captchaimagebgnew = postedVar('captchaimagebgnew');
        $captchaimagedotsnew = formInt('captchaimagedotsnew');
        $captchaimagelinesnew = formInt('captchaimagelinesnew');
        $captchaimagefontsnew = postedVar('captchaimagefontsnew');
        $captchaimageminfontnew = formInt('captchaimageminfontnew');
        $captchaimagemaxfontnew = formInt('captchaimagemaxfontnew');
        $captchaimagecolornew = formOnOff('captchaimagecolornew');
        $showsubforumsnew = formOnOff('showsubforumsnew');
        $max_avatar_size = $max_avatar_size_w_new.'x'.$max_avatar_size_h_new;
        $regoptionalnew = formOnOff('regoptionalnew');
        $quickreply_statusnew = formOnOff('quickreply_statusnew');
        $quickjump_statusnew = formOnOff('quickjump_statusnew');
        $index_statsnew = formOnOff('index_statsnew');
        $onlinetodaycountnew = formInt('onlinetodaycountnew');
        $onlinetoday_statusnew = formOnOff('onlinetoday_statusnew');

        $db->query("UPDATE ".X_PREFIX."settings SET
            langfile='$langfilenew',
            bbname='$bbnamenew',
            postperpage='$postperpagenew',
            topicperpage='$topicperpagenew',
            hottopic='$hottopicnew',
            theme='$themenew',
            bbstatus='$bbstatusnew',
            whosonlinestatus='$whos_on',
            regstatus='$reg_on',
            bboffreason='$bboffreasonnew',
            regviewonly='$regviewnew',
            floodctrl='$floodctrlnew',
            memberperpage='$memberperpagenew',
            catsonly='$catsonlynew',
            hideprivate='$hidepriv',
            emailcheck='$emailchecknew',
            bbrules='$bbrulesnew',
            bbrulestxt='$bbrulestxtnew',
            searchstatus='$searchstatusnew',
            faqstatus='$faqstatusnew',
            memliststatus='$memliststatusnew',
            sitename='$sitenamenew',
            siteurl='$siteurlnew',
            avastatus='$avastatusnew',
            u2uquota='$u2uquotanew',
            gzipcompress='$gzipcompressnew',
            boardurl='$boardurlnew',
            coppa='$coppanew',
            timeformat='$timeformatnew',
            adminemail='$adminemailnew',
            dateformat='$dateformatnew',
            sigbbcode='$sigbbcodenew',
            sightml='$sightmlnew',
            reportpost='$reportpostnew',
            bbinsert='$bbinsertnew',
            smileyinsert='$smileyinsertnew',
            doublee='$doubleenew',
            smtotal='$smtotalnew',
            smcols='$smcolsnew',
            editedby='$editedbynew',
            dotfolders='$dotfoldersnew',
            attachimgpost='$attachimgpostnew',
            tickerstatus='$tickerstatusnew',
            tickercontents='$tickercontentsnew',
            tickerdelay='$tickerdelaynew',
            addtime='$addtimenew',
            todaysposts='$todaystatusnew',
            stats='$statsstatusnew',
            max_avatar_size='$max_avatar_size',
            footer_options='$footer_options',
            space_cats='$space_catsnew',
            spellcheck='$spellchecknew',
            allowrankedit='$allowrankeditnew',
            notifyonreg='$notifyonregnew',
            indexshowbar='$indexShowBarNew',
            subject_in_title='$subjectInTitleNew',
            def_tz='$def_tz_new',
            resetsigs='$resetSigNew',
            pruneusers='$pruneusersnew',
            ipreg='$ipReg',
            maxdayreg='$maxDayReg',
            maxattachsize='$maxAttachSize',
            captcha_status='$captchanew',
            captcha_reg_status='$captcharegnew',
            captcha_post_status='$captchapostnew',
            captcha_code_charset='$captchacharsetnew',
            captcha_code_length='$captchacodenew',
            captcha_code_casesensitive='$captchacodecasenew',
            captcha_code_shadow='$captchacodeshadownew',
            captcha_image_type='$captchaimagetypenew',
            captcha_image_width='$captchaimagewidthnew',
            captcha_image_height='$captchaimageheightnew',
            captcha_image_bg='$captchaimagebgnew',
            captcha_image_dots='$captchaimagedotsnew',
            captcha_image_lines='$captchaimagelinesnew',
            captcha_image_fonts='$captchaimagefontsnew',
            captcha_image_minfont='$captchaimageminfontnew',
            captcha_image_maxfont='$captchaimagemaxfontnew',
            captcha_image_color='$captchaimagecolornew',
            showsubforums='$showsubforumsnew',
            regoptional='$regoptionalnew',
            quickreply_status='$quickreply_statusnew',
            quickjump_status='$quickjump_statusnew',
            index_stats='$index_statsnew',
            onlinetodaycount='$onlinetodaycountnew',
            onlinetoday_status='$onlinetoday_statusnew'
        ");

        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['textsettingsupdate'].'</td></tr>';
    }
}

if ($action == 'rename') {
    if (!X_SADMIN) {
        error($lang['superadminonly'], false, '</td></tr></table></td></tr></table><br />');
    }

    if (onSubmit('renamesubmit')) {
        $vUserFrom = formVar('frmUserFrom');
        $vUserTo = formVar('frmUserTo');
        $adm = new admin();
        $myErr = $adm->rename_user($vUserFrom, $vUserTo);
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$myErr.'</td></tr>';
    } else {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td>
        <form action="cp.php?action=rename" method="post">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category" colspan="2"><strong><font color="<?php echo $cattext?>"><?php echo $lang['admin_rename_txt']?></font></strong></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>" width="22%"><?php echo $lang['admin_rename_userfrom']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="frmUserFrom" size="25" /></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>" width="22%"><?php echo $lang['admin_rename_userto']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="frmUserTo" size="25" /></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow" colspan="2"><input type="submit" class="submit" name="renamesubmit" value="<?php echo $lang['admin_rename_txt']?>" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        </td>
        </tr>
        <?php
    }
}

if ($action == 'forum') {
    $fdetails = getInt('fdetails');
    if (noSubmit('forumsubmit') && !$fdetails) {
        $groups = array();
        $forums = array();
        $forums[0] = array();
        $forumlist = array();
        $subs = array();
        $i = 0;
        $query = $db->query("SELECT fid, type, name, displayorder, status, fup FROM ".X_PREFIX."forums ORDER BY fup ASC, displayorder ASC");
        while($selForums = $db->fetch_array($query)) {
            if ($selForums['type'] == 'group') {
                $groups[$i]['fid'] = $selForums['fid'];
                $groups[$i]['name'] = htmlspecialchars_decode($selForums['name']);
                $groups[$i]['displayorder'] = $selForums['displayorder'];
                $groups[$i]['status'] = $selForums['status'];
                $groups[$i]['fup'] = $selForums['fup'];
            } else if ($selForums['type'] == 'forum') {
                $id = (empty($selForums['fup'])) ? 0 : $selForums['fup'];
                $forums[$id][$i]['fid'] = $selForums['fid'];
                $forums[$id][$i]['name'] = htmlspecialchars_decode($selForums['name']);
                $forums[$id][$i]['displayorder'] = $selForums['displayorder'];
                $forums[$id][$i]['status'] = $selForums['status'];
                $forums[$id][$i]['fup'] = $selForums['fup'];
                $forumlist[$i]['fid'] = $selForums['fid'];
                $forumlist[$i]['name'] = $selForums['name'];
            } else if ($selForums['type'] == 'sub') {
                $subs[$selForums['fup']][$i]['fid'] = $selForums['fid'];
                $subs[$selForums['fup']][$i]['name'] = htmlspecialchars_decode($selForums['name']);
                $subs[$selForums['fup']][$i]['displayorder'] = $selForums['displayorder'];
                $subs[$selForums['fup']][$i]['status'] = $selForums['status'];
                $subs[$selForums['fup']][$i]['fup'] = $selForums['fup'];
            }
            $i++;
        }
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td>
        <form method="post" action="cp.php?action=forum">
        <table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category"><font color="<?php echo $cattext?>"><strong><?php echo $lang['textforumopts']?></strong></font></td>
        </tr>
        <?php
        foreach($forums[0] as $forum) {
            $on = $off = '';
            if ($forum['status'] == 'on') {
                $on = $selHTML;
            } else {
                $off = $selHTML;
            }
            ?>
            <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
            <td class="smalltxt"><input type="checkbox" name="delete<?php echo $forum['fid']?>" value="<?php echo $forum['fid']?>" />
            &nbsp;<input type="text" name="name<?php echo $forum['fid']?>" value="<?php echo stripslashes($forum['name'])?>" />
            &nbsp; <?php echo $lang['textorder']?> <input type="text" name="displayorder<?php echo $forum['fid']?>" size="2" value="<?php echo $forum['displayorder']?>" />
            &nbsp; <select name="status<?php echo $forum['fid']?>">
            <option value="on" <?php echo $on?>><?php echo $lang['texton']?></option><option value="off" <?php echo $off?>><?php echo $lang['textoff']?></option></select>
            &nbsp; <select name="moveto<?php echo $forum['fid']?>"><option value="" selected="selected">-<?php echo $lang['textnone']?>-</option>
            <?php
            foreach($groups as $moveforum) {
                echo "<option value=\"$moveforum[fid]\">".stripslashes($moveforum['name'])."</option>";
            }
            ?>
            </select>
            <a href="cp.php?action=forum&amp;fdetails=<?php echo $forum['fid']?>"><?php echo $lang['textmoreopts']?></a></td>
            </tr>
            <?php
            if (array_key_exists($forum['fid'], $subs)) {
                foreach($subs[$forum['fid']] as $subforum) {
                    $on = $off = '';
                    if ($subforum['status'] == 'on') {
                        $on = $selHTML;
                    } else {
                        $off = $selHTML;
                    }
                    ?>
                    <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
                    <td class="smalltxt"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="checkbox" name="delete<?php echo $subforum['fid']?>" value="<?php echo $subforum['fid']?>" />
                    &nbsp;<input type="text" name="name<?php echo $subforum['fid']?>" value="<?php echo stripslashes($subforum['name'])?>" />
                    &nbsp; <?php echo $lang['textorder']?> <input type="text" name="displayorder<?php echo $subforum['fid']?>" size="2" value="<?php echo $subforum['displayorder']?>" />
                    &nbsp; <select name="status<?php echo $subforum['fid']?>">
                    <option value="on" <?php echo $on?>><?php echo $lang['texton']?></option><option value="off" <?php echo $off?>><?php echo $lang['textoff']?></option></select>
                    &nbsp; <select name="moveto<?php echo $subforum['fid']?>">
                    <?php
                    foreach($forumlist as $moveforum) {
                        if ($subforum['fup'] == $moveforum['fid']) {
                            echo '<option value="'.$moveforum['fid'].'" selected="selected">'.stripslashes($moveforum['name']).'</option>';
                        } else {
                            echo '<option value="'.$moveforum['fid'].'">'.stripslashes($moveforum['name']).'</option>';
                        }
                    }
                    ?>
                    </select>
                    <a href="cp.php?action=forum&amp;fdetails=<?php echo $subforum['fid']?>"><?php echo $lang['textmoreopts']?></a></td>
                    </tr>
                    <?php
                }
            }
        }

        foreach($groups as $group) {
            $on = $off = '';
            if ($group['status'] == 'on') {
                $on = $selHTML;
            } else {
                $off = $selHTML;
            }
            ?>
            <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
            <td>&nbsp;</td>
            </tr>
            <tr bgcolor="<?php echo $altbg1?>" class="tablerow">
            <td class="smalltxt"><input type="checkbox" name="delete<?php echo $group['fid']?>" value="<?php echo $group['fid']?>" />
            <input type="text" name="name<?php echo $group['fid']?>" value="<?php echo stripslashes($group['name'])?>" />
            &nbsp; <?php echo $lang['textorder']?> <input type="text" name="displayorder<?php echo $group['fid']?>" size="2" value="<?php echo $group['displayorder']?>" />
            &nbsp; <select name="status<?php echo $group['fid']?>">
            <option value="on" <?php echo $on?>><?php echo $lang['texton']?></option><option value="off" <?php echo $off?>><?php echo $lang['textoff']?></option></select>
            </td>
            </tr>
            <?php
            if (array_key_exists($group['fid'], $forums)) {
                foreach($forums[$group['fid']] as $forum) {
                    $on = $off = '';
                    if ($forum['status'] == 'on') {
                        $on = $selHTML;
                    } else {
                        $off = $selHTML;
                    }
                    ?>
                    <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
                    <td class="smalltxt"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="checkbox" name="delete<?php echo $forum['fid']?>" value="<?php echo $forum['fid']?>" />
                    &nbsp;<input type="text" name="name<?php echo $forum['fid']?>" value="<?php echo stripslashes($forum['name'])?>" />
                    &nbsp; <?php echo $lang['textorder']?> <input type="text" name="displayorder<?php echo $forum['fid']?>" size="2" value="<?php echo $forum['displayorder']?>" />
                    &nbsp; <select name="status<?php echo $forum['fid']?>">
                    <option value="on" <?php echo $on?>><?php echo $lang['texton']?></option><option value="off" <?php echo $off?>><?php echo $lang['textoff']?></option></select>
                    &nbsp; <select name="moveto<?php echo $forum['fid']?>"><option value="">-<?php echo $lang['textnone']?>-</option>
                    <?php
                    foreach($groups as $moveforum) {
                        if ($moveforum['fid'] == $forum['fup']) {
                            $curgroup = $selHTML;
                        } else {
                            $curgroup = '';
                        }
                        echo '<option value="'.$moveforum['fid'].'" '.$curgroup.'>'.stripslashes($moveforum['name']).'</option>';
                    }
                    ?>
                    </select>
                    <a href="cp.php?action=forum&amp;fdetails=<?php echo $forum['fid']?>"><?php echo $lang['textmoreopts']?></a></td>
                    </tr>
                    <?php
                    if (array_key_exists($forum['fid'], $subs)) {
                        foreach($subs[$forum['fid']] as $forum) {
                            $on = $off = '';
                            if ($forum['status'] == 'on') {
                                $on = $selHTML;
                            } else {
                                $off = $selHTML;
                            }
                            ?>
                            <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
                            <td class="smalltxt"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<input type="checkbox" name="delete<?php echo $forum['fid']?>" value="<?php echo $forum['fid']?>" />
                            &nbsp;<input type="text" name="name<?php echo $forum['fid']?>" value="<?php echo stripslashes($forum['name'])?>" />
                            &nbsp; <?php echo $lang['textorder']?> <input type="text" name="displayorder<?php echo $forum['fid']?>" size="2" value="<?php echo $forum['displayorder']?>" />
                            &nbsp; <select name="status<?php echo $forum['fid']?>">
                            <option value="on" <?php echo $on?>><?php echo $lang['texton']?></option><option value="off" <?php echo $off?>><?php echo $lang['textoff']?></option></select>
                            &nbsp; <select name="moveto<?php echo $forum['fid']?>">
                            <?php
                            foreach($forumlist as $moveforum) {
                                if ($moveforum['fid'] == $forum['fup']) {
                                    echo '<option value="'.$moveforum['fid'].'" selected="selected">'.html_entity_decode(stripslashes($moveforum['name'])).'</option>';
                                } else {
                                    echo '<option value="'.$moveforum['fid'].'">'.html_entity_decode(stripslashes($moveforum['name'])).'</option>';
                                }
                            }
                            ?>
                            </select>
                            <a href="cp.php?action=forum&amp;fdetails=<?php echo $forum['fid']?>"><?php echo $lang['textmoreopts']?></a></td>
                            </tr>
                            <?php
                        }
                    }
                }
            }
        }
        ?>
        <tr bgcolor="<?php echo $altbg1?>" class="tablerow">
        <td>&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td class="smalltxt"><input type="text" name="newgname" value="<?php echo $lang['textnewgroup']?>" />
        &nbsp; <?php echo $lang['textorder']?> <input type="text" name="newgorder" size="2" />
        &nbsp; <select name="newgstatus">
        <option value="on"><?php echo $lang['texton']?></option><option value="off"><?php echo $lang['textoff']?></option></select></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg2?>" class="smalltxt"><input type="text" name="newfname" value="<?php echo $lang['textnewforum']?>" />
        &nbsp; <?php echo $lang['textorder']?> <input type="text" name="newforder" size="2" />
        &nbsp; <select name="newfstatus">
        <option value="on"><?php echo $lang['texton']?></option><option value="off"><?php echo $lang['textoff']?></option></select>
        &nbsp; <select name="newffup"><option value="" selected="selected">-<?php echo $lang['textnone']?>-</option>
        <?php
        foreach($groups as $group) {
            echo '<option value="'.$group['fid'].'">'.html_entity_decode(stripslashes($group['name'])).'</option>';
        }
        ?>
        </select>
        </td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td class="smalltxt"><input type="text" name="newsubname" value="<?php echo $lang['textnewsubf']?>" />
        &nbsp; <?php echo $lang['textorder']?> <input type="text" name="newsuborder" size="2" />
        &nbsp; <select name="newsubstatus"><option value="on"><?php echo $lang['texton']?></option><option value="off"><?php echo $lang['textoff']?></option></select>
        &nbsp; <select name="newsubfup">
        <?php
        foreach($forumlist as $group) {
            echo '<option value="'.$group['fid'].'">'.html_entity_decode(stripslashes($group['name'])).'</option>';
        }
        ?>
        </select>
        </td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow"><input type="submit" name="forumsubmit" value="<?php echo $lang['textsubmitchanges']?>" class="submit" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        </td>
        </tr>
        <?php
    } else if ($fdetails && noSubmit('forumsubmit')) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp.php?action=forum&amp;fdetails=<?php echo $fdetails?>">
        <table cellspacing="0" cellpadding="0" border="0" width="100%" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category" colspan="2"><font color="<?php echo $cattext?>"><strong><?php echo $lang['textforumopts']?></strong></font></td>
        </tr>
        <?php
        $queryg = $db->query("SELECT * FROM ".X_PREFIX."forums WHERE fid='$fdetails'");
        $forum = $db->fetch_array($queryg);

        $themelist = array();
        $themelist[] = '<select name="themeforumnew">';
        $themelist[] = '<option value="0">'.$lang['textusedefault'].'</option>';
        $query = $db->query("SELECT themeid, name FROM ".X_PREFIX."themes ORDER BY name ASC");
        while($themeinfo = $db->fetch_array($query)) {
            if ($themeinfo['themeid'] == $forum['theme']) {
                $themelist[] = '<option value="'.intval($themeinfo['themeid']).'" '.$selHTML.'>'.stripslashes($themeinfo['name']).'</option>';
            } else {
                $themelist[] = '<option value="'.intval($themeinfo['themeid']).'">'.stripslashes($themeinfo['name']).'</option>';
            }
        }
        $themelist[] = '</select>';
        $themelist = implode("\n", $themelist);
        $db->free_result($query);

        if ($forum['allowhtml'] == "yes") {
            $checked2 = $cheHTML;
        } else {
            $checked2 = '';
        }

        if ($forum['allowsmilies'] == "yes") {
            $checked3 = $cheHTML;
        } else {
            $checked3 = '';
        }

        if ($forum['allowbbcode'] == "yes") {
            $checked4 = $cheHTML;
        } else {
            $checked4 = '';
        }

        if ($forum['allowimgcode'] == "yes") {
            $checked5 = $cheHTML;
        } else {
            $checked5 = '';
        }

        if ($forum['attachstatus'] == "on") {
            $checked6 = $cheHTML;
        } else {
            $checked6 = '';
        }

        if ($forum['pollstatus'] == "on") {
            $checked7 = $cheHTML;
        } else {
            $checked7 = '';
        }

        if ($forum['guestposting'] == "on") {
            $checked8 = $cheHTML;
        } else {
            $checked8 = '';
        }

        $pperm = explode('|', $forum['postperm']);

        $type11 = $type12 = $type13 = $type14 = '';
        if ($pperm[0] == 2) {
            $type12 = $selHTML;
        } else if ($pperm['0'] == 3) {
            $type13 = $selHTML;
        } else if ($pperm[0] == 4) {
            $type14 = $selHTML;
        } else if ($pperm[0] == 1) {
            $type11 = $selHTML;
        }

        $type21 = $type22 = $type23 = $type24 = '';
        if ($pperm[1] == 2) {
            $type22 = $selHTML;
        } else if ($pperm[1] == 3) {
            $type23 = $selHTML;
        } else if ($pperm[1] == 4) {
            $type24 = $selHTML;
        } else if ($pperm[1] == 1) {
            $type21 = $selHTML;
        }

        $type31 = $type32 = $type33 = $type34 = '';
        if ($forum['private'] == 2) {
            $type32 = $selHTML;
        } else if ($forum['private'] == 3) {
            $type33 = $selHTML;
        } else if ($forum['private'] == 4) {
            $type34 = $selHTML;
        } else if ($forum['private'] == 1) {
            $type31 = $selHTML;
        }

        $forum['name'] = stripslashes($forum['name']);
        $forum['description'] = stripslashes($forum['description']);
        ?>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['textforumname']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="namenew" value="<?php echo htmlspecialchars_decode($forum['name'])?>" /></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['textdesc']?></td>
        <td bgcolor="<?php echo $altbg2?>"><textarea rows="4" cols="30" name="descnew"><?php echo htmlspecialchars_decode($forum['description'])?></textarea></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>" valign="top"><?php echo $lang['textallow']?></td>
        <td bgcolor="<?php echo $altbg2?>" class="smalltxt">
        <input type="checkbox" name="allowhtmlnew" value="yes" <?php echo $checked2?> /><?php echo $lang['texthtml']?><br />
        <input type="checkbox" name="allowsmiliesnew" value="yes" <?php echo $checked3?> /><?php echo $lang['textsmilies']?><br />
        <input type="checkbox" name="allowbbcodenew" value="yes" <?php echo $checked4?> /><?php echo $lang['textbbcode']?><br />
        <input type="checkbox" name="allowimgcodenew" value="yes" <?php echo $checked5?> /><?php echo $lang['textimgcode']?><br />
        <input type="checkbox" name="attachstatusnew" value="on" <?php echo $checked6?> /><?php echo $lang['attachments']?><br />
        <input type="checkbox" name="pollstatusnew" value="on" <?php echo $checked7?> /><?php echo $lang['polls']?><br />
        <input type="checkbox" name="guestpostingnew" value="on" <?php echo $checked8?> /><?php echo $lang['textanonymousposting']?><br />
        </td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['texttheme']?></td>
        <td bgcolor="<?php echo $altbg2?>"><?php echo $themelist?></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['whopostop1']?></td>
        <td bgcolor="<?php echo $altbg2?>"><select name="postperm1">
        <option value="1" <?php echo $type11?>><?php echo $lang['textpermission1']?>
        <option value="2" <?php echo $type12?>><?php echo $lang['textpermission2']?>
        <option value="3" <?php echo $type13?>><?php echo $lang['textpermission3']?>
        <option value="4" <?php echo $type14?>><?php echo $lang['textpermission41']?>
        </select>
        </td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['whopostop2']?></td>
        <td bgcolor="<?php echo $altbg2?>"><select name="postperm2">
        <option value="1" <?php echo $type21?>><?php echo $lang['textpermission1']?>
        <option value="2" <?php echo $type22?>><?php echo $lang['textpermission2']?>
        <option value="3" <?php echo $type23?>><?php echo $lang['textpermission3']?>
        <option value="4" <?php echo $type24?>><?php echo $lang['textpermission41']?>
        </select>
        </td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['whoview']?></td>
        <td bgcolor="<?php echo $altbg2?>"><select name="privatenew">
        <option value="1" <?php echo $type31?>><?php echo $lang['textpermission1']?>
        <option value="2" <?php echo $type32?>><?php echo $lang['textpermission2']?>
        <option value="3" <?php echo $type33?>><?php echo $lang['textpermission3']?>
        <option value="4" <?php echo $type34?>><?php echo $lang['textpermission42']?>
        </select>
        </td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['textuserlist']?></td>
        <td bgcolor="<?php echo $altbg2?>"><textarea rows="4" cols="30" name="userlistnew"><?php echo $forum['userlist']?></textarea></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['forumpw']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="passwordnew" value="<?php echo htmlspecialchars($forum['password'])?>" /></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['textdeleteques']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="checkbox" name="delete" value="<?php echo $forum['fid']?>" /></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow" colspan="2"><input type="submit" name="forumsubmit" value="<?php echo $lang['textsubmitchanges']?>" class="submit" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        </td>
        </tr>
        <?php
    } else if (onSubmit('forumsubmit') && !$fdetails) {
        $queryforum = $db->query("SELECT fid, type FROM ".X_PREFIX."forums WHERE type='forum' OR type='sub'");
        $db->query("DELETE FROM ".X_PREFIX."forums WHERE name=''");
        while($forum = $db->fetch_array($queryforum)) {
            $displayorder = formInt('displayorder'.$forum['fid']);
            $self['status'] = formOnOff('status'.$forum['fid']);
            $name = postedVar('name'.$forum['fid']);
            $delete = formInt('delete'.$forum['fid']);
            $moveto = formInt('moveto'.$forum['fid']);

            if ($delete) {
                $db->query("DELETE FROM ".X_PREFIX."forums WHERE (type='forum' OR type='sub') AND fid='$delete'");
                $querythread = $db->query("SELECT tid, author FROM ".X_PREFIX."threads WHERE fid='$delete'");
                while($thread = $db->fetch_array($querythread)) {
                    $db->query("DELETE FROM ".X_PREFIX."threads WHERE tid='$thread[tid]'");
                    $db->query("DELETE FROM ".X_PREFIX."favorites WHERE tid='$thread[tid]'");
                    $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-1 WHERE username='$thread[author]'");
                    $querypost = $db->query("SELECT pid, author FROM ".X_PREFIX."posts WHERE tid='$thread[tid]'");
                    while($post = $db->fetch_array($querypost)) {
                        $db->query("DELETE FROM ".X_PREFIX."posts WHERE pid='$post[pid]'");
                        $db->query("UPDATE ".X_PREFIX."members SET postnum=postnum-1 WHERE username='$post[author]'");
                    }
                    $db->free_result($querypost);
                }
                $db->free_result($querythread);
            }
            $db->query("UPDATE ".X_PREFIX."forums SET name='$name', displayorder=".$displayorder.", status='$self[status]', fup=".$moveto." WHERE fid='".$forum['fid']."'");
        }

        $querygroup = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE type='group'");
        while($group = $db->fetch_array($querygroup)) {
            $name = formVar('name'.$group['fid']);
            $displayorder = formInt('displayorder'.$group['fid']);
            $self['status'] = formOnOff('status'.$group['fid']);
            $delete = formVar('delete'.$group['fid']);

            if ($delete) {
                $query = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE type='forum' AND fup='$delete'");
                while($forum = $db->fetch_array($query)) {
                    $db->query("UPDATE ".X_PREFIX."forums SET fup=0 WHERE type='forum' AND fup='$delete'");
                }
                $db->query("DELETE FROM ".X_PREFIX."forums WHERE type='group' AND fid='$delete'");
            }
            $db->query("UPDATE ".X_PREFIX."forums SET name='$name', displayorder=".$displayorder.", status='".$self['status']."' WHERE fid='".$group['fid']."'");
        }

        $newgname = formVar('newgname');
        $newfname = formVar('newfname');
        $newsubname = formVar('newsubname');
        $newgorder = formVar('newgorder');
        $newforder = formVar('newforder');
        $newsuborder = formVar('newsuborder');
        $newgstatus = formOnOff('newgstatus');
        $newfstatus = formOnOff('newfstatus');
        $newsubstatus = formOnOff('newsubstatus');
        $newffup = formInt('newffup');
        $newsubfup = formInt('newsubfup');

        if ($newfname != $lang['textnewforum']) {
            $newfname = addslashes($newfname);
            $db->query("INSERT INTO ".X_PREFIX."forums (type, name, status, lastpost, moderator, displayorder, private, description, allowhtml, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, pollstatus, password, guestposting) VALUES ('forum', '$newfname', '$newfstatus', '', '', ".(int)$newforder.", '1', '', 'no', 'yes', 'yes', '', 0, 0, 0, ".(int)$newffup.", '1|1', 'yes', 'on', 'on', '', 'off')");
        }

        if ($newgname != $lang['textnewgroup']) {
            $newgname = addslashes($newgname);
            $db->query("INSERT INTO ".X_PREFIX."forums (type, name, status, lastpost, moderator, displayorder, private, description, allowhtml, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, pollstatus, password, guestposting) VALUES ('group', '$newgname', '$newgstatus', '', '', ".(int)$newgorder.", '', '', '', '', '', '', 0, 0, 0, 0, '', '', '', '', '', 'off')");
        }

        if ($newsubname != $lang['textnewsubf']) {
            $newsubname = addslashes($newsubname);
            $db->query("INSERT INTO ".X_PREFIX."forums (type, name, status, lastpost, moderator, displayorder, private, description, allowhtml, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, pollstatus, password, guestposting) VALUES ('sub', '$newsubname', '$newsubstatus', '', '', ".(int)$newsuborder.", '1', '', 'no', 'yes', 'yes', '', 0, 0, 0, ".(int)$newsubfup.", '1|1', 'yes', 'on', 'on', '', 'off')");
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['textforumupdate'].'</td></tr>';
    } else {
        $namenew = addslashes(formVar('namenew', false));
        $descnew = addslashes(formVar('descnew', false));
        $allowhtmlnew = formYesNo('allowhtmlnew');
        $allowsmiliesnew = formYesNo('allowsmiliesnew');
        $allowbbcodenew = formYesNo('allowbbcodenew');
        $allowimgcodenew = formYesNo('allowimgcodenew');
        $attachstatusnew = formOnOff('attachstatusnew');
        $pollstatusnew = formOnOff('pollstatusnew');
        $guestpostingnew = formOnOff('guestpostingnew');
        $themeforumnew = formInt('themeforumnew');
        $postperm1 = formInt('postperm1');
        $postperm2 = formInt('postperm2');
        $privatenew = formInt('privatenew');
        $userlistnew = addslashes(formVar('userlistnew'));
        $passwordnew = postedVar('passwordnew', '', FALSE);
        $delete = formInt('delete');

        $db->query("UPDATE ".X_PREFIX."forums SET
            name='$namenew',
            description='$descnew',
            allowhtml='$allowhtmlnew',
            allowsmilies='$allowsmiliesnew',
            allowbbcode='$allowbbcodenew',
            theme='$themeforumnew',
            userlist='$userlistnew',
            private='$privatenew',
            postperm='$postperm1|$postperm2',
            allowimgcode='$allowimgcodenew',
            attachstatus='$attachstatusnew',
            pollstatus='$pollstatusnew',
            password='$passwordnew',
            guestposting='$guestpostingnew'
            WHERE fid='$fdetails'"
        );

        if ($delete) {
            $db->query("DELETE FROM ".X_PREFIX."forums WHERE fid='$delete'");
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['textforumupdate'].'</td></tr>';
    }
}

if ($action == "mods") {
    if (noSubmit('modsubmit')) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td>
        <form method="post" action="cp.php?action=mods">
        <table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textforum']?></font></strong></td>
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textmoderator']?></font></strong></td>
        </tr>
        <?php
        $oldfid = 0;
        $query = $db->query("SELECT f.moderator, f.name, f.fid, c.name as cat_name, c.fid as cat_fid FROM ".X_PREFIX."forums f LEFT JOIN ".X_PREFIX."forums c ON (f.fup = c.fid) WHERE (c.type='group' AND f.type='forum') OR (f.type='forum' AND f.fup='') ORDER BY c.displayorder, f.displayorder");
        while($forum = $db->fetch_array($query)) {
            if ($oldfid != $forum['cat_fid']) {
                $oldfid = $forum['cat_fid']
                ?>
                <tr bgcolor="<?php echo $altbg1?>" class="tablerow">
                <td colspan="2"><strong><?php echo html_entity_decode(stripslashes($forum['cat_name']))?></strong></td>
                </tr>
                <?php
            }
            ?>
            <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
            <td><?php echo html_entity_decode(stripslashes($forum['name']))?></td>
            <td><input type="text" name="mod[<?php echo $forum['fid']?>]"" value="<?php echo $forum['moderator']?>" /></td>
            </tr>
            <?php
            $querys = $db->query("SELECT name, fid, moderator FROM ".X_PREFIX."forums WHERE fup='".$forum['fid']."' AND type='sub'");
            while($sub = $db->fetch_array($querys)) {
                ?>
                <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
                <td><?php echo $lang['4spaces']?><?php echo $lang['4spaces']?><em><?php echo html_entity_decode(stripslashes($sub['name']))?></em></td>
                <td><input type="text" name="mod[<?php echo $sub['fid']?>]"" value="<?php echo $sub['moderator']?>" /></td>
                </tr>
                <?php
            }
        }
        ?>
        <tr>
        <td colspan="2" class="tablerow" bgcolor="<?php echo $altbg1?>"><span class="smalltxt"><?php echo $lang['multmodnote']?></span></td>
        </tr>
        <tr>
        <td colspan="2" class="ctrtablerow" bgcolor="<?php echo $altbg2?>"><input type="submit" class="submit" name="modsubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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
        $mod = formArray('mod');
        if (is_array($mod)) {
            foreach($mod as $fid=>$mods) {
                $db->query("UPDATE ".X_PREFIX."forums SET moderator='$mods' WHERE fid='$fid'");
            }
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['textmodupdate'].'</td></tr>';
    }
}

if ($action == "members") {
    $members = getVar('members');
    if (noSubmit('membersubmit')) {
        if (!$members) {
            ?>
            <tr bgcolor="<?php echo $altbg2?>">
            <td>
            <form method="post" action="cp.php?action=members&amp;members=search">
            <table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
            <tr>
            <td bgcolor="<?php echo $bordercolor?>">
            <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
            <tr>
            <td class="category" colspan="2"><font color="<?php echo $cattext?>"><strong><?php echo $lang['textmembers']?></strong></font></td>
            </tr>
            <tr class="tablerow">
            <td bgcolor="<?php echo $altbg1?>" width="22%"><?php echo $lang['textsrchusr']?></td>
            <td bgcolor="<?php echo $altbg2?>"><input type="text" name="srchmem" /></td>
            </tr>
            <tr class="tablerow">
            <td bgcolor="<?php echo $altbg1?>" width="22%"><?php echo $lang['textwithstatus']?></td>
            <td bgcolor="<?php echo $altbg2?>">
            <select name="srchstatus">
            <option value="0"><?php echo $lang['anystatus']?></option>
            <option value="Super Administrator"><?php echo $lang['superadmin']?></option>
            <option value="Administrator"><?php echo $lang['textadmin']?></option>
            <option value="Super Moderator"><?php echo $lang['textsupermod']?></option>
            <option value="Moderator"><?php echo $lang['textmod']?></option>
            <option value="Member"><?php echo $lang['textmem']?></option>
            <option value="Banned"><?php echo $lang['textbanned']?></option>
            <option value="Pending"><?php echo $lang['textpendinglogin']?></option>
            </select>
            </td>
            </tr>
            <tr>
            <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow" colspan="2"><input type="submit" class="submit" value="<?php echo $lang['textgo']?>" /></td>
            </tr>
            </table>
            </td>
            </tr>
            </table>
            </form>
            </td>
            </tr>
            <?php
        } else if ($members == "search") {
            ?>
            <script language="javascript" type="text/javascript">var delmem = Array();</script>
            <tr bgcolor="<?php echo $altbg2?>">
            <td align="center">
            <form method="post" action="cp.php?action=members">
            <table cellspacing="0" cellpadding="0" border="0" width="91%" align="center">
            <tr>
            <td bgcolor="<?php echo $bordercolor?>">
            <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
            <tr class="category">
            <td align="center" width="3%"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textdeleteques']?></font></strong></td>
            <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textusername']?></font></strong></td>
            <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textnewpassword']?></font></strong></td>
            <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textposts']?></font></strong></td>
            <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textstatus']?></font></strong></td>
            <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textcusstatus']?></font></strong></td>
            <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textbanfrom']?></font></strong></td>
            </tr>
            <?php
            $srchmem = postedVar('srchmem');
            $srchstatus = postedVar('srchstatus');
            if ($srchstatus == '0') {
                $query = $db->query("SELECT * FROM ".X_PREFIX."members WHERE username LIKE '%$srchmem%' ORDER BY username");
            } else if ($srchstatus == "Pending") {
                $query = $db->query("SELECT * FROM ".X_PREFIX."members WHERE lastvisit=0 ORDER BY username");
            } else {
                $query = $db->query("SELECT * FROM ".X_PREFIX."members WHERE username LIKE '%$srchmem%' AND status='$srchstatus' ORDER BY username");
            }

            $sadminselect = $adminselect = $smodselect = '';
            $modselect = $memselect = $banselect = '';
            $noban = $u2uban = $postban = $bothban = '';
            while($member = $db->fetch_array($query)) {
                switch($member['status']) {
                    case 'Super Administrator':
                        $sadminselect = $selHTML;
                        break;
                    case 'Administrator':
                        $adminselect = $selHTML;
                        break;
                    case 'Super Moderator':
                        $smodselect = $selHTML;
                        break;
                    case 'Moderator':
                        $modselect = $selHTML;
                        break;
                    case 'Member':
                        $memselect = $selHTML;
                        break;
                    case 'Banned':
                        $banselect = $selHTML;
                        break;
                    default:
                        $memselect = $selHTML;
                        break;
                }

                switch($member['ban']) {
                    case 'u2u':
                        $u2uban = $selHTML;
                        break;
                    case 'posts':
                        $postban = $selHTML;
                        break;
                    case 'both':
                        $bothban = $selHTML;
                        break;
                    default:
                        $noban = $selHTML;
                        break;
                }

                if ($member['lastvisit'] == 0) {
                    $pending = '<br />'.$lang['textpendinglogin'];
                } else {
                    $pending = '';
                }
                ?>
                <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
                <td align="center"><input type="checkbox" name="delete<?php echo $member['uid']?>" onclick="addUserDel(<?php echo $member['uid']?>, '<?php echo $member['username']?>', this)" value="<?php echo $member['uid']?>" /></td>
                <td><a href="member.php?action=viewpro&amp;member=<?php echo $member['username']?>"><?php echo $member['username']?></a>
                <br /><a href="javascript:confirmAction('<?php echo addslashes($lang['confirmDeletePosts']);?>', 'cp.php?action=deleteposts&amp;member=<?php echo $member['username']?>', false);"><strong><?php echo $lang['cp_deleteposts']?></strong></a><?php echo $pending ?>
                </td>
                <td><input type="text" size="12" name="pw<?php echo $member['uid']?>"></td>
                <td><input type="text" size="3" name="postnum<?php echo $member['uid']?>" value="<?php echo $member['postnum']?>"></td>
                <td><select name="status<?php echo $member['uid']?>">
                <option value="Super Administrator" <?php echo $sadminselect?>><?php echo $lang['superadmin']?></option>
                <option value="Administrator" <?php echo $adminselect?>><?php echo $lang['textadmin']?></option>
                <option value="Super Moderator" <?php echo $smodselect?>><?php echo $lang['textsupermod']?></option>
                <option value="Moderator" <?php echo $modselect?>><?php echo $lang['textmod']?></option>
                <option value="Member" <?php echo $memselect?>><?php echo $lang['textmem']?></option>
                <option value="Banned" <?php echo $banselect?>><?php echo $lang['textbanned']?></option>
                </select></td>
                <td><input type="text" size="16" name="cusstatus<?php echo $member['uid']?>" value="<?php echo htmlspecialchars(stripslashes($member['customstatus']))?>" /></td>
                <td><select name="banstatus<?php echo $member['uid']?>">
                <option value="" <?php echo $noban?>><?php echo $lang['noban']?></option>
                <option value="u2u" <?php echo $u2uban?>><?php echo $lang['banu2u']?></option>
                <option value="posts" <?php echo $postban?>><?php echo $lang['banpost']?></option>
                <option value="both" <?php echo $bothban?>><?php echo $lang['banboth']?></option>
                </select></td>
                </tr>
                <?php
                $sadminselect = $adminselect = $smodselect = '';
                $modselect = $memselect = $banselect = '';
                $noban = $u2uban = $postban = $bothban = '';
            }
            ?>
            <tr>
            <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow" colspan="7"><input type="submit" class="submit" name="membersubmit" value="<?php echo $lang['textsubmitchanges']?>" onclick="return confirmUserDel('<?php echo $lang['confirmDeleteUser']?>');" /><input type="hidden" name="srchmem" value="<?php echo $srchmem?>" /><input type="hidden" name="srchstatus" value="<?php echo $srchstatus?>" /></td>
            </tr>
            </table>
            </td>
            </tr>
            </table>
            </form>
            </td>
            </tr>
            <?php
        }
    } else if (onSubmit('membersubmit')) {
        $query = $db->query("SELECT MIN(`uid`) FROM `" . X_PREFIX. "members` WHERE `status`='Super Administrator'");
        $sa_uid = $db->result($query, 0);
        $db->free_result($query);

        $srchmem = postedVar('srchmem');
        $srchstatus = postedVar('srchstatus');
        if ($srchstatus == '0') {
            $query = $db->query("SELECT uid, username, password, status FROM ".X_PREFIX."members WHERE username LIKE '%$srchmem%'");
        } else if ($srchstatus == "Pending") {
            $query = $db->query("SELECT uid, username, password, status FROM ".X_PREFIX."members WHERE username LIKE '%$srchmem%' AND lastvisit = 0");
        } else {
            $query = $db->query("SELECT uid, username, password, status FROM ".X_PREFIX."members WHERE username LIKE '%$srchmem%' AND status='$srchstatus'");
        }

        while($mem = $db->fetch_array($query)) {
            $to['status'] = "status".$mem['uid'];
            $to['status'] = isset($_POST[$to['status']]) ? $_POST[$to['status']] : '';

            if (trim($to['status']) == '') {
                $to['status'] = 'Member';
            }

            $origstatus = $mem['status'];
            $banstatus = "banstatus".$mem['uid'];
            $banstatus =  isset($_POST[$banstatus]) ? $_POST[$banstatus] : '';
            $cusstatus = "cusstatus".$mem['uid'];
            $cusstatus =  isset($_POST[$cusstatus]) ? $_POST[$cusstatus] : '';
            $pw = "pw" . $mem['uid'];
            $pw = isset($_POST[$pw]) ? $_POST[$pw] : '';
            $postnum = "postnum".$mem['uid'];
            $postnum = isset($_POST[$postnum]) ? $_POST[$postnum] : '';
            $delete = "delete".$mem['uid'];
            $delete = isset($_POST[$delete]) ? $_POST[$delete] : '';

            if ($pw != "") {
                $newpw = md5(trim($pw));
            } else {
                $newpw = $mem['password'];
            }
            $queryadd = " , password='$newpw'";

            if (!X_SADMIN && ($origstatus == "Super Administrator" || $to['status'] == "Super Administrator")) {
                continue;
            }

            if ($origstatus == 'Super Administrator' && $to['status'] != 'Super Administrator') {
                if ($db->result($db->query("SELECT count(uid) FROM ".X_PREFIX."members WHERE status='Super Administrator'"), 0) == 1) {
                    error($lang['lastsadmin'], false, '</td></tr></table></td></tr></table><br />');
                }
            }

            if ($delete != "" && $delete != $self['uid'] && $delete != $sa_uid) {
                $db->query("DELETE FROM ".X_PREFIX."members WHERE uid='$delete'");
                $db->query("UPDATE ".X_PREFIX."whosonline SET username='Anonymous' WHERE username='".$mem['username']."'");
            } else {
                if (strpos($pw, '"') !== false || strpos($pw, "'") !== false) {
                    $lang['textmembersupdate'] = $mem['username'].': '.$lang['textpwincorrect'];
                } else {
                    $newcustom = addslashes($cusstatus);
                    $db->query("UPDATE ".X_PREFIX."members SET ban='$banstatus', status='$to[status]', postnum='$postnum', customstatus='$newcustom'$queryadd WHERE uid='$mem[uid]'");
                    $newpw="";
                }
            }
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['textmembersupdate'].'</td></tr>';
    }
}

if ($action == "ipban") {
    if (noSubmit('ipbansubmit')) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp.php?action=ipban">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr><td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textdeleteques']?></font></strong></td>
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textip']?>:</font></strong></td>
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textipresolve']?>:</font></strong></td>
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textadded']?></font></strong></td>
        </tr>
        <?php
        $query = $db->query("SELECT * FROM ".X_PREFIX."banned ORDER BY dateline");
        while($ipaddress = $db->fetch_array($query)) {
            for($i=1; $i<=4; ++$i) {
                $j = "ip" . $i;
                if ($ipaddress[$j] == -1) {
                    $ipaddress[$j] = "*";
                }
            }
            $ipdate = gmdate($dateformat, $ipaddress['dateline'] + ($timeoffset * 3600) + ($addtime * 3600)) . " $lang[textat] " . gmdate("$timecode", $ipaddress['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));
            $theip = "$ipaddress[ip1].$ipaddress[ip2].$ipaddress[ip3].$ipaddress[ip4]";
            ?>
            <tr class="tablerow" bgcolor="<?php echo $altbg1?>">
            <td><input type="checkbox" name="delete[<?php echo $ipaddress['id']?>]" value="1" /></td>
            <td><?php echo $theip?></td>
            <td><?php echo @gethostbyaddr($theip)?></td>
            <td><?php echo $ipdate?></td>
            </tr>
            <?php
        }

        $query = $db->query("SELECT id FROM ".X_PREFIX."banned WHERE (ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')");
        $result = $db->fetch_array($query);
        if ($result) {
            $warning = $lang['ipwarning'];
        } else {
            $warning = '';
        }
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td colspan="4" class="tablerow" bgcolor="<?php echo $altbg2?>"><?php echo $lang['textnewip']?>
        <input type="text" name="newip1" size="3" maxlength="3" bgcolor="<?php echo $altbg2?>" />.<input type="text" name="newip2" size="3" maxlength="3" bgcolor="<?php echo $altbg2?>" />.<input type="text" name="newip3" size="3" maxlength="3" bgcolor="<?php echo $altbg2?>" />.<input type="text" name="newip4" size="3" maxlength="3" bgcolor="<?php echo $altbg2?>" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        <br />
        <span class="smalltxt"><?php echo $lang['currentip']?> <strong><?php echo $onlineip?></strong><?php echo $warning?><br /><?php echo $lang['multipnote']?></span><br />
        <br /><div align="center"><input type="submit" class="submit" name="ipbansubmit" value="<?php echo $lang['textsubmitchanges']?>" /></div>
        </form>
        </td>
        </tr>
        <?php
    } else {
        $newip = array();
        $newip[] = trim(formInt('newip1'));
        $newip[] = trim(formInt('newip2'));
        $newip[] = trim(formInt('newip3'));
        $newip[] = trim(formInt('newip4'));
        $delete = formArray('delete');

        if ($delete) {
            $dels = array();
            foreach($delete as $id => $del) {
                if ($del == 1) {
                    $dels[] = $id;
                }
            }

            if (count($dels) > 0) {
                $dels = implode(',', $dels);
                $db->query("DELETE FROM ".X_PREFIX."banned WHERE id IN ($dels)");
            }
        }
        $self['status'] = $lang['textipupdate'];

        if ($newip[1] != '0' && $newip[1] != '0' && $newip[2] != '0' && $newip[3] != '0') {
            $invalid = 0;
            for($i=0; $i<=3 && !$invalid; ++$i) {
                if ($newip[$i] == "*") {
                    $ip[$i+1] = -1;
                } else if (preg_match("#^[0-9]+$#", $newip[$i])) {
                    $ip[$i+1] = $newip[$i];
                } else {
                    $invalid = 1;
                }
            }

            if ($invalid) {
                $self['status'] = $lang['invalidip'];
            } else {
                if ($ip[1] == '-1' && $ip[2] == '-1' && $ip[3] == '-1' && $ip[4] == '-1') {
                    $self['status'] = $lang['impossiblebanall'];
                } else {
                    $query = $db->query("SELECT id FROM ".X_PREFIX."banned WHERE (ip1='$ip[1]' OR ip1='-1') AND (ip2='$ip[2]' OR ip2='-1') AND (ip3='$ip[3]' OR ip3='-1') AND (ip4='$ip[4]' OR ip4='-1')");
                    $result = $db->fetch_array($query);
                    if ($result) {
                        $self['status'] = $lang['existingip'];
                    } else {
                        $query = $db->query("INSERT INTO ".X_PREFIX."banned (ip1, ip2, ip3, ip4, dateline) VALUES ('$ip[1]', '$ip[2]', '$ip[3]', '$ip[4]', $onlinetime)");
                    }
                }
            }
        }
        echo '<tr bgcolor="'.$altbg2.'"><td class="ctrtablerow">'.$self['status'].'</td></tr>';
    }
}

if ($action == "deleteposts") {
    $member = getVar('member');
    $queryd = $db->query("DELETE FROM ".X_PREFIX."posts WHERE author='$member'");
    $queryt = $db->query("SELECT * FROM ".X_PREFIX."threads");
    while($threads = $db->fetch_array($queryt)) {
        $query = $db->query("SELECT COUNT(tid) FROM ".X_PREFIX."posts WHERE tid='$threads[tid]'");
        $replynum = $db->result($query, 0);
        $replynum--;
        $db->query("UPDATE ".X_PREFIX."threads SET replies=replies-1 WHERE tid='$threads[tid]'");
        $db->query("DELETE FROM ".X_PREFIX."threads WHERE author='$member'");
    }
}

if ($action == "upgrade") {
    if (!X_SADMIN) {
        error($lang['superadminonly'], false, '</td></tr></table></td></tr></table><br />');
    }

    if (onSubmit('upgradesubmit')) {
        $upgrade = formVar('upgrade');
        if (isset($_FILES['sql_file'])) {
            $add = get_attached_file($_FILES['sql_file'], 'on');
            if ($add !== false) {
                $upgrade .= $add;
            }
        }

        $upgrade = str_replace('$table_', $tablepre, $upgrade);
        $explode = explode(";", $upgrade);
        $count = count($explode);

        if (strlen(trim($explode[$count-1])) == 0) {
            unset($explode[$count-1]);
            $count--;
        }

        echo '</table></td></tr></table>';

        for($num=0;$num<$count;$num++) {
            $explode[$num] = stripslashes($explode[$num]);
            if ($allow_spec_q !== true) {
                if (strtoupper(substr(trim($explode[$num]), 0, 3)) == 'USE' || strtoupper(substr(trim($explode[$num]), 0, 14)) == 'SHOW DATABASES') {
                    error($lang['textillegalquery'], false, '</td></tr></table></td></tr></table><br />');
                }
            }

            if ($explode[$num] != '') {
                $query = $db->query($explode[$num], true);
            }
            echo '<br />';
            ?>
            <table cellspacing="0" cellpadding="0" border="0" width="<?php echo $tablewidth?>" align="center">
            <tr>
            <td bgcolor="<?php echo $bordercolor?>">
            <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
            <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
            <td colspan="<?php echo $db->num_fields($query)?>"><strong><?php echo $lang['upgraderesults']?></strong>&nbsp;<?php echo $explode[$num]?>
            <?php
            $xn = strtoupper($explode[$num]);
            if (strpos($xn, 'SELECT') !== false || strpos($xn, 'SHOW') !== false || strpos($xn, 'EXPLAIN') !== false || strpos($xn, 'DESCRIBE') !== false) {
                dump_query($query, true);
            } else {
                $selq=false;
            }
            ?>
            </td>
            </tr>
            </td>
            </tr>
            </table>
            </td>
            </tr>
            </table>
            <?php
        }
        ?>
        <br />
        <table cellspacing="0" cellpadding="0" border="0" width="<?php echo $tablewidth?>" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['upgradesuccess']?></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        <?php
        end_time();
        eval('echo "'.template('footer').'";');
        exit();
    } else {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp.php?action=upgrade" enctype="multipart/form-data">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>" colspan="2"><strong><?php echo $lang['textupgrade']?></strong></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow" colspan="2"><?php echo $lang['upgrade']?></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" class="tablerow" valign="top"><textarea cols="85" rows="10" name="upgrade"></textarea></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow" colspan="2"><input type="file" name="sql_file" /></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" class="tablerow" colspan="2"><?php echo $lang['upgradenote']?></td>
        </tr>
        <tr>
        <td class="ctrtablerow" bgcolor=<?php echo $altbg2?> colspan="2"><input type="submit" class="submit" name="upgradesubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        </td>
        </tr>
        <?php
    }
}

if ($action == "search") {
    if (onSubmit('searchsubmit')) {
        $userip = postedVar('userip');
        $postip = postedVar('postip');
        $profileword = postedVar('profileword');
        $postword = postedVar('postword');

        $found = 0;
        $list = array();
        if ($userip) {
            $query = $db->query("SELECT * FROM ".X_PREFIX."members WHERE regip = '$userip'");
            while($users = $db->fetch_array($query)) {
                $link = "./member.php?action=viewpro&amp;member=".recodeOut($users['username']);
                $list[] = "<a href = \"$link\">{$users['username']}<br />";
                $found++;
            }
        }

        if ($postip) {
            $query = $db->query("SELECT * FROM ".X_PREFIX."posts WHERE useip = '$postip'");
            while($users = $db->fetch_array($query)) {
                $link = "./viewthread.php?tid=$users[tid]#pid$users[pid]";
                if (!empty($users['subject'])) {
                    $list[] = '<a href="$link">'.$users['subject'].'<br />';
                } else {
                    $list[] = "<a href = \"$link\">- - No subject - -<br />";
                }
                $found++;
            }
        }

        if ($profileword) {
            $query = $db->query("SELECT * FROM ".X_PREFIX."members WHERE bio LIKE '%$profileword%'");
            while($users = $db->fetch_array($query)) {
                $link = "./member.php?action=viewpro&amp;member=".recodeOut($users['username']);
                $list[] = "<a href = \"$link\">{$users['username']}<br />";
                $found++;
            }
        }

        if ($postword) {
            $query = $db->query("SELECT * FROM ".X_PREFIX."posts WHERE subject LIKE '%".$postword."%' OR message LIKE '%".$postword."%'");
            while($users = $db->fetch_array($query)) {
                $link = "./viewthread.php?tid=$users[tid]#pid$users[pid]";
                if (!empty($users['subject'])) {
                    $list[] = '<a href="$link">'.$users['subject'].'<br />';
                } else {
                    $list[] = '<a href="$link">- - No subject - -<br />';
                }
                $found++;
            }
        }
        ?>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td align="left" colspan="2">
        <strong><?php echo $found?></strong> <?php echo $lang['beenfound']?>
        <br />
        </td>
        </tr>
        <?php
        foreach($list as $num=>$val) {
            ?>
            <tr class="tablerow" width="5%">
            <td align="left" bgcolor="<?php echo $altbg2?>">
            <strong><?php echo ($num+1)?>.</strong>
            </td>
            <td align="left" width="95%" bgcolor="<?php echo $altbg1?>">
            <?php echo html_entity_decode(stripslashes($val))?>
            </td>
            </tr>
            <?php
         }
    } else {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp.php?action=search">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td colspan=2><strong><font color="<?php echo $cattext?>"><?php echo $lang['insertdata']?>:</font></strong></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td valign="top"><div align="center"><br />
        <?php echo $lang['userip']?><br /><input type="text" name="userip" /></input><br /><br />
        <?php echo $lang['postip']?><br /><input type="text" name="postip" /></input><br /><br />
        <?php echo $lang['profileword']?><br /><input type="text" name="profileword" /></input><br /><br />
        <?php echo $lang['postword']?><br />
        <?php
        $query = $db->query("SELECT find FROM ".X_PREFIX."words");
        $select = "<select name=\"postword\"><option value=\"\"></option>";
        while($temp = $db->fetch_array($query)) {
            $select .= "<option value=\"$temp[find]\">$temp[find]</option>";
        }
        $select .= "</select>";
        echo $select;
        ?>
        <br />
        <br />
        <div align="center"><br /><input type="submit" class="submit" name="searchsubmit" value="Search now" /><br /><br /></div>
        </td>
        </tr>
        </table>
        </td></tr></table>
        </form>
        </td>
        </tr>
        <?php

    }
}

echo '</table></td></tr></table>';
end_time();
eval('echo "'.template('footer').'";');
?>