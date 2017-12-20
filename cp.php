<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2012, The XMB Group
 * http://www.xmbforum2.com/
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

header('X-Robots-Tag: noindex');

loadtemplates('error_nologinsession');

nav($lang['textcp']);

eval('$css = "'.template('css').'";');
eval('echo "'.template('header').'";');
echo '<script language="JavaScript" type="text/javascript" src="./js/admin.js"></script>';

if (!X_ADMIN) {
    eval('echo "'.template('error_nologinsession').'";');
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

$action = postedVar('action', '', FALSE, FALSE, FALSE, 'g');

if ($action == "settings") {
    if (noSubmit('settingsubmit1')
    And noSubmit('settingsubmit2')
    And noSubmit('settingsubmit3')
    And noSubmit('settingsubmit4')
    And noSubmit('settingsubmit5')
    And noSubmit('settingsubmit6')
    And noSubmit('settingsubmit7')) {
        $langfileselect = createLangFileSelect($SETTINGS['langfile']);

        $themelist = array();
        $themelist[] = '<select name="themenew">';
        $query = $db->query("SELECT themeid, name FROM ".X_PREFIX."themes ORDER BY name ASC");
        while($themeinfo = $db->fetch_array($query)) {
            if ($themeinfo['themeid'] == $SETTINGS['theme']) {
                $themelist[] = '<option value="'.intval($themeinfo['themeid']).'" '.$selHTML.'>'.$themeinfo['name'].'</option>';
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
        if ($SETTINGS['avastatus'] == 'list') {
            $avchecked[1] = true;
        } else if ($SETTINGS['avastatus'] == 'off') {
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
        <span class="smalltxt">
        <a href="#1"><?php echo $lang['admin_main_settings1']; ?></a><br />
        <a href="#2"><?php echo $lang['admin_main_settings2']; ?></a><br />
        <a href="#3"><?php echo $lang['admin_main_settings3']; ?></a><br />
        <a href="#4"><?php echo $lang['admin_main_settings4']; ?></a><br />
        <a href="#5"><?php echo $lang['admin_main_settings5']; ?></a><br />
        <a href="#8"><?php echo $lang['admin_main_settings8']; ?></a><br />
        <a href="#6"><?php echo $lang['admin_main_settings6']; ?></a><br />
        <a href="#7"><?php echo $lang['admin_main_settings7']; ?></a><br />
        </span>
        <form method="post" action="cp.php?action=settings">
        <input type="hidden" name="token" value="<?php echo nonce_create('mainsettings'); ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="<?php echo $tablewidth?>" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>"><a name="1" />&raquo;&nbsp;<?php echo $lang['admin_main_settings1']?></font></strong></td>
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
        <td bgcolor="<?php echo $altbg2?>" colspan="2"><input class="submit" type="submit" name="settingsubmit1" value="<?php echo $lang['textsubmitchanges']?>" /></td>
        </tr>
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>"><a name="2" />&raquo;&nbsp;<?php echo $lang['admin_main_settings2']?></font></strong></td>
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
        <tr class="ctrtablerow">
        <td bgcolor="<?php echo $altbg2?>" colspan="2"><input class="submit" type="submit" name="settingsubmit2" value="<?php echo $lang['textsubmitchanges']?>" /></td>
        </tr>
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>"><a name="3" />&raquo;&nbsp;<?php echo $lang['admin_main_settings3']?></font></strong></td>
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
        <td bgcolor="<?php echo $altbg2?>" colspan="2"><input class="submit" type="submit" name="settingsubmit3" value="<?php echo $lang['textsubmitchanges']?>" /></td>
        </tr>
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>"><a name="4" />&raquo;&nbsp;<?php echo $lang['admin_main_settings4']?></font></strong></td>
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
        ?>
        <tr class="ctrtablerow">
        <td bgcolor="<?php echo $altbg2?>" colspan="2"><input class="submit" type="submit" name="settingsubmit3" value="<?php echo $lang['textsubmitchanges']?>" /></td>
        </tr>
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>"><a name="5" />&raquo;&nbsp;<?php echo $lang['admin_main_settings5']?></font></strong></td>
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
        <tr class="ctrtablerow">
        <td bgcolor="<?php echo $altbg2?>" colspan="2"><input class="submit" type="submit" name="settingsubmit4" value="<?php echo $lang['textsubmitchanges']?>" /></td>
        </tr>
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>"><a name="8" />&raquo;&nbsp;<?php echo $lang['admin_main_settings8']?></font></strong></td>
        </tr>
        <?php
        $max_image_sizes = explode('x', $SETTINGS['max_image_size']);
        $max_thumb_sizes = explode('x', $SETTINGS['max_thumb_size']);
        for($i=0; $i<=4; $i++) {
            $urlformatchecked[$i] = ($SETTINGS['file_url_format'] == $i + 1);
        }
        for($i=0; $i<=1; $i++) {
            $subdirchecked[$i] = ($SETTINGS['files_subdir_format'] == $i + 1);
        }
        printsetting2($lang['textfilesperpost'], 'filesperpostnew', ((int)$SETTINGS['filesperpost']), 3);
        printsetting2($lang['max_attachment_size'], 'maxAttachSize', ((int)$SETTINGS['maxattachsize']), 12);
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
        <td bgcolor="<?php echo $altbg2?>" colspan="2"><input class="submit" type="submit" name="settingsubmit5" value="<?php echo $lang['textsubmitchanges']?>" /></td>
        </tr>
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>"><a name="6" />&raquo;&nbsp;<?php echo $lang['admin_main_settings6']?></font></strong></td>
        </tr>
        <?php
        printsetting2($lang['texthottopic'], 'hottopicnew', ((int)$SETTINGS['hottopic']), 3);
        printsetting1($lang['bbinsert'], 'bbinsertnew', $bbinserton, $bbinsertoff);
        printsetting1($lang['smileyinsert'], 'smileyinsertnew', $smileyinserton, $smileyinsertoff);
        printsetting3($lang['footer_options'], 'new_footer_options', $names, $values, $checked);
        printsetting3($lang['defaultTimezoneDesc'], 'def_tz_new', array($lang['timezone1'], $lang['timezone2'], $lang['timezone3'], $lang['timezone4'], $lang['timezone5'], $lang['timezone6'], $lang['timezone7'], $lang['timezone8'], $lang['timezone9'], $lang['timezone10'], $lang['timezone11'], $lang['timezone12'], $lang['timezone13'], $lang['timezone14'], $lang['timezone15'], $lang['timezone16'], $lang['timezone17'], $lang['timezone18'], $lang['timezone19'], $lang['timezone20'], $lang['timezone21'], $lang['timezone22'], $lang['timezone23'], $lang['timezone24'], $lang['timezone25'], $lang['timezone26'], $lang['timezone27'], $lang['timezone28'], $lang['timezone29'], $lang['timezone30'], $lang['timezone31'], $lang['timezone32'], $lang['timezone33']), array('-12', '-11', '-10', '-9', '-8', '-7', '-6', '-5', '-4', '-3.5', '-3', '-2', '-1', '0', '1', '2', '3', '3.5', '4', '4.5', '5', '5.5', '5.75', '6', '6.5', '7', '8', '9', '9.5', '10', '11', '12', '13'), array($timezone1, $timezone2, $timezone3, $timezone4, $timezone5, $timezone6, $timezone7, $timezone8, $timezone9, $timezone10, $timezone11, $timezone12, $timezone13, $timezone14, $timezone15, $timezone16, $timezone17, $timezone18, $timezone19, $timezone20, $timezone21, $timezone22, $timezone23, $timezone24, $timezone25, $timezone26, $timezone27, $timezone28, $timezone29, $timezone30, $timezone31, $timezone32, $timezone33), false);
        printsetting2($lang['addtime'], 'addtimenew', $SETTINGS['addtime'], 3);
        printsetting1($lang['sigbbcode'], 'sigbbcodenew', $sigbbcodeon, $sigbbcodeoff);
        printsetting1($lang['sightml'], 'sightmlnew', $sightmlon, $sightmloff);
        if (!ini_get('allow_url_fopen')) {
            printsetting5($lang['max_avatar_size_w'], $lang['no_url_fopen']);
        } else {
            printsetting2($lang['max_avatar_size_w'], 'max_avatar_size_w_new', $max_avatar_sizes[0], 4);
            printsetting2($lang['max_avatar_size_h'], 'max_avatar_size_h_new', $max_avatar_sizes[1], 4);
        }
        printsetting1($lang['what_tickerstatus'], 'tickerstatusnew', $tickerstatuson, $tickerstatusoff);
        printsetting2($lang['what_tickerdelay'], 'tickerdelaynew', ((int)$SETTINGS['tickerdelay']), 5);
        printsetting4($lang['tickercontents'], 'tickercontentsnew', $SETTINGS['tickercontents'], 5, 50);
        ?>
        <tr class="ctrtablerow">
        <td bgcolor="<?php echo $altbg2?>" colspan="2"><input class="submit" type="submit" name="settingsubmit6" value="<?php echo $lang['textsubmitchanges']?>" /></td>
        </tr>
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>"><a name="7" />&raquo;&nbsp;<?php echo $lang['admin_main_settings7']?></font></strong></td>
        </tr>
        <?php
        require ROOT.'include/captcha.inc.php';
        $Captcha = new Captcha();
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
        <td bgcolor="<?php echo $altbg2?>" colspan="2"><input class="submit" type="submit" name="settingsubmit7" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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
        request_secure('mainsettings', '', X_NONCE_FORM_EXP, FALSE);

        $sitenamenew = postedVar('sitenamenew');
        $bbnamenew = postedVar('bbnamenew');
        $siteurlnew = postedVar('siteurlnew');
        $adminemailnew = postedVar('adminemailnew');
        $bbrulesnew = formOnOff('bbrulesnew');
        $bbrulestxtnew = postedVar('bbrulestxtnew', '', FALSE);
        $bbstatusnew = formOnOff('bbstatusnew');
        $bboffreasonnew = postedVar('bboffreasonnew');
        $gzipcompressnew = formOnOff('gzipcompressnew');

        $langfilenew = postedVar('langfilenew');
        $result = $db->query("SELECT devname FROM ".X_PREFIX."lang_base WHERE devname='$langfilenew'");
        if ($db->num_rows($result) == 0) {
            $langfilenew = $SETTINGS['langfile'];
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
        $avastatusnew = postedVar('avastatusnew');
        if ($avastatusnew != 'on' And $avastatusnew != 'list') {
            $avastatusnew = 'off';
        }
        $resetSigNew = formOnOff('resetSigNew');
        $doubleenew = formOnOff('doubleenew');
        $pruneusersnew = formInt('pruneusersnew');
        $hottopicnew = formInt('hottopicnew');
        $bbinsertnew = formOnOff('bbinsertnew');
        $smileyinsertnew = formOnOff('smileyinsertnew');

        $new_footer_options = postedArray('new_footer_options');
        if (!empty($new_footer_options)) {
            $footer_options = implode('-', $new_footer_options);
        } else {
            $footer_options = '';
        }

        $maxAttachSize = formInt('maxAttachSize');
        $def_tz_new = isset($_POST['def_tz_new']) && is_numeric($_POST['def_tz_new']) ? $_POST['def_tz_new'] : 0;
        $addtimenew = isset($_POST['addtimenew']) && is_numeric($_POST['addtimenew']) ? $_POST['addtimenew'] : 0;
        $sigbbcodenew = formOnOff('sigbbcodenew');
        $sightmlnew = formOnOff('sightmlnew');
        $max_avatar_size_w_new = formInt('max_avatar_size_w_new');
        $max_avatar_size_h_new = formInt('max_avatar_size_h_new');
        $tickerstatusnew = formOnOff('tickerstatusnew');
        $tickerdelaynew = formInt('tickerdelaynew');
        $tickercontentsnew = postedVar('tickercontentsnew');
        $maxDayReg = formInt('maxDayReg');
        $showsubforumsnew = formOnOff('showsubforumsnew');
        $max_avatar_size = $max_avatar_size_w_new.'x'.$max_avatar_size_h_new;
        $regoptionalnew = formOnOff('regoptionalnew');
        $quickreply_statusnew = formOnOff('quickreply_statusnew');
        $quickjump_statusnew = formOnOff('quickjump_statusnew');
        $index_statsnew = formOnOff('index_statsnew');
        $onlinetodaycountnew = formInt('onlinetodaycountnew');
        $onlinetoday_statusnew = formOnOff('onlinetoday_statusnew');

        $filespathnew = postedVar('filespathnew');
        $filesbasenew = postedVar('filesbasenew');
        $filesminsizenew = formInt('filesminsizenew');
        $filesperpostnew = formInt('filesperpostnew');
        $filesurlpathnew = formInt('filesurlpathnew');
        $filessubdirnew = formInt('filessubdirnew');
        $max_image_size_w_new = formInt('max_image_size_w_new');
        $max_image_size_h_new = formInt('max_image_size_h_new');
        $max_thumb_size_w_new = formInt('max_thumb_size_w_new');
        $max_thumb_size_h_new = formInt('max_thumb_size_h_new');
        $max_image_size = $max_image_size_w_new.'x'.$max_image_size_h_new;
        $max_thumb_size = $max_thumb_size_w_new.'x'.$max_thumb_size_h_new;
        $remoteimages = formOnOff('remoteimages');

        $sql = "UPDATE ".X_PREFIX."settings SET
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
            showsubforums='$showsubforumsnew',
            regoptional='$regoptionalnew',
            quickreply_status='$quickreply_statusnew',
            quickjump_status='$quickjump_statusnew',
            index_stats='$index_statsnew',
            onlinetodaycount='$onlinetodaycountnew',
            onlinetoday_status='$onlinetoday_statusnew',
            attach_remote_images='$remoteimages',
            files_min_disk_size='$filesminsizenew',
            files_storage_path='$filespathnew',
            files_subdir_format='$filessubdirnew',
            file_url_format='$filesurlpathnew',
            files_virtual_url='$filesbasenew',
            filesperpost='$filesperpostnew',
            max_image_size='$max_image_size',
            max_thumb_size='$max_thumb_size'
        ";

        $captchanew = formOnOff('captchanew');
        $captcharegnew = formOnOff('captcharegnew');
        $captchapostnew = formOnOff('captchapostnew');
        $captchasearchnew = formOnOff('captchasearchnew');
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

        $sql .= ",
        captcha_status='$captchanew',
        captcha_reg_status='$captcharegnew',
        captcha_post_status='$captchapostnew',
        captcha_search_status='$captchasearchnew',
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
        captcha_image_color='$captchaimagecolornew'
        ";

        $db->query($sql);

        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['textsettingsupdate'].'</td></tr>';
    }
}

if ($action == 'rename') {
    if (!X_SADMIN) {
        error($lang['superadminonly'], false, '</td></tr></table></td></tr></table><br />');
    }

    if (onSubmit('renamesubmit')) {
        request_secure('renamemember', '', X_NONCE_AYS_EXP, FALSE);
        $vUserFrom = postedVar('frmUserFrom', '', TRUE, FALSE);
        $vUserTo = postedVar('frmUserTo', '', TRUE, FALSE);
        $adm = new admin();
        $myErr = $adm->rename_user($vUserFrom, $vUserTo);
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$myErr.'</td></tr>';
    } else {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td>
        <form action="cp.php?action=rename" method="post">
        <input type="hidden" name="token" value="<?php echo nonce_create('renamemember'); ?>" />
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
                $groups[$i]['name'] = $selForums['name'];
                $groups[$i]['displayorder'] = $selForums['displayorder'];
                $groups[$i]['status'] = $selForums['status'];
                $groups[$i]['fup'] = $selForums['fup'];
            } else if ($selForums['type'] == 'forum') {
                $id = (empty($selForums['fup'])) ? 0 : $selForums['fup'];
                $forums[$id][$i]['fid'] = $selForums['fid'];
                $forums[$id][$i]['name'] = $selForums['name'];
                $forums[$id][$i]['displayorder'] = $selForums['displayorder'];
                $forums[$id][$i]['status'] = $selForums['status'];
                $forums[$id][$i]['fup'] = $selForums['fup'];
                $forumlist[$i]['fid'] = $selForums['fid'];
                $forumlist[$i]['name'] = $selForums['name'];
            } else if ($selForums['type'] == 'sub') {
                $subs[$selForums['fup']][$i]['fid'] = $selForums['fid'];
                $subs[$selForums['fup']][$i]['name'] = $selForums['name'];
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
        <input type="hidden" name="token" value="<?php echo nonce_create('massedforums'); ?>" />
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
            if (!isset($subs[$forum['fid']])) { //Ungrouped forum options.
                foreach($forums[0] as $moveforum) {
                    if ($moveforum['fid'] != $forum['fid']) {
                        echo "<option value=\"$moveforum[fid]\"> &nbsp; &raquo; ".stripslashes($moveforum['name'])."</option>";
                    }
                }
            }
            foreach($groups as $moveforum) { //Groups and grouped forum options.
                echo "<option value=\"$moveforum[fid]\">".stripslashes($moveforum['name'])."</option>";
                if (isset($forums[$moveforum['fid']]) And !isset($subs[$forum['fid']])) {
                    foreach($forums[$moveforum['fid']] as $moveforum) {
                        echo "<option value=\"$moveforum[fid]\"> &nbsp; &raquo; ".stripslashes($moveforum['name'])."</option>";
                    }
                }
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
                    &nbsp; <select name="moveto<?php echo $subforum['fid']?>"><option value="" selected="selected">-<?php echo $lang['textnone']?>-</option>
                    <?php
                    foreach($forums[0] as $moveforum) { //Ungrouped forum options.
                        if ($moveforum['fid'] == $subforum['fup']) {
                            $curgroup = $selHTML;
                        } else {
                            $curgroup = '';
                        }
                        echo "<option value=\"$moveforum[fid]\" $curgroup> &nbsp; &raquo; ".stripslashes($moveforum['name'])."</option>";
                    }
                    foreach($groups as $moveforum) { //Groups and grouped forum options.
                        echo '<option value="'.$moveforum['fid'].'">'.stripslashes($moveforum['name']).'</option>';
                        if (isset($forums[$moveforum['fid']])) {
                            foreach($forums[$moveforum['fid']] as $moveforum) {
                                echo "<option value=\"$moveforum[fid]\"> &nbsp; &raquo; ".stripslashes($moveforum['name'])."</option>";
                            }
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
                    if (!isset($subs[$forum['fid']])) { //Ungrouped forum options.
                        foreach($forums[0] as $moveforum) {
                            echo "<option value=\"$moveforum[fid]\"> &nbsp; &raquo; ".stripslashes($moveforum['name'])."</option>";
                        }
                    }
                    foreach($groups as $moveforum) { //Groups and grouped forum options.
                        if ($moveforum['fid'] == $forum['fup']) {
                            $curgroup = $selHTML;
                        } else {
                            $curgroup = '';
                        }
                        echo '<option value="'.$moveforum['fid'].'" '.$curgroup.'>'.stripslashes($moveforum['name']).'</option>';
                        if (!isset($subs[$forum['fid']]) And isset($forums[$moveforum['fid']])) {
                            foreach($forums[$moveforum['fid']] as $moveforum) {
                                if ($moveforum['fid'] != $forum['fid']) {
                                    echo "<option value=\"$moveforum[fid]\"> &nbsp; &raquo; ".stripslashes($moveforum['name'])."</option>";
                                }
                            }
                        }
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
                            &nbsp; <select name="moveto<?php echo $forum['fid']?>"><option value="" selected="selected">-<?php echo $lang['textnone']?>-</option>
                            <?php
                            foreach($forums[0] as $moveforum) { //Ungrouped forum options.
                                echo "<option value=\"$moveforum[fid]\" $curgroup> &nbsp; &raquo; ".stripslashes($moveforum['name'])."</option>";
                            }
                            foreach($groups as $moveforum) { //Groups and grouped forum options.
                                echo '<option value="'.$moveforum['fid'].'">'.stripslashes($moveforum['name']).'</option>';
                                if (isset($forums[$moveforum['fid']])) {
                                    foreach($forums[$moveforum['fid']] as $moveforum) {
                                        if ($moveforum['fid'] == $forum['fup']) {
                                            $curgroup = $selHTML;
                                        } else {
                                            $curgroup = '';
                                        }
                                        echo "<option value=\"$moveforum[fid]\" $curgroup> &nbsp; &raquo; ".stripslashes($moveforum['name'])."</option>";
                                    }
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
            echo '<option value="'.$group['fid'].'">'.fnameOut($group['name']).'</option>';
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
            echo '<option value="'.$group['fid'].'">'.fnameOut($group['name']).'</option>';
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
        $key = template_key('editfid', $fdetails);
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp.php?action=forum&amp;fdetails=<?php echo $fdetails?>">
        <input type="hidden" name="token" value="<?php echo nonce_create($key); ?>" />
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
                $themelist[] = '<option value="'.intval($themeinfo['themeid']).'" '.$selHTML.'>'.$themeinfo['name'].'</option>';
            } else {
                $themelist[] = '<option value="'.intval($themeinfo['themeid']).'">'.$themeinfo['name'].'</option>';
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

        $forum['name'] = stripslashes($forum['name']);
        ?>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['textforumname']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="namenew" value="<?php echo $forum['name']; ?>" /></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['textdesc']?></td>
        <td bgcolor="<?php echo $altbg2?>"><textarea rows="4" cols="30" name="descnew">
<?php // Linefeed required here - Do not edit!
        echo $forum['description'];
        ?></textarea></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>" valign="top"><?php echo $lang['textallow']?></td>
        <td bgcolor="<?php echo $altbg2?>" class="smalltxt">
        <input type="checkbox" name="allowhtmlnew" value="yes" <?php echo $checked2?> /><?php echo $lang['texthtml']?><br />
        <input type="checkbox" name="allowsmiliesnew" value="yes" <?php echo $checked3?> /><?php echo $lang['textsmilies']?><br />
        <input type="checkbox" name="allowbbcodenew" value="yes" <?php echo $checked4?> /><?php echo $lang['textbbcode']?><br />
        <input type="checkbox" name="allowimgcodenew" value="yes" <?php echo $checked5?> /><?php echo $lang['textimgcode']?><br />
        <input type="checkbox" name="attachstatusnew" value="on" <?php echo $checked6?> /><?php echo $lang['attachments']?><br />
        </td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['texttheme']?></td>
        <td bgcolor="<?php echo $altbg2?>"><?php echo $themelist?></td>
        </tr>

        <tr class="tablerow">
        <td style="background-color: <?php echo $THEME['altbg1']?>"><?php echo $lang['forumpermissions']?></td>
        <td style="background-color: <?php echo $THEME['altbg2']?>"><table style="width: 100%; text-align: center;">
        <tr>
            <td class="tablerow" style="width: 25ex;">&nbsp;</td>
            <td class="category" style="color: <?php echo $THEME['cattext']?>; font-weight: bold; text-align: center;"><?php echo $lang['polls'];   ?></td>
            <td class="category" style="color: <?php echo $THEME['cattext']?>; font-weight: bold; text-align: center;"><?php echo $lang['threads']; ?></td>
            <td class="category" style="color: <?php echo $THEME['cattext']?>; font-weight: bold; text-align: center;"><?php echo $lang['replies']; ?></td>
            <td class="category" style="color: <?php echo $THEME['cattext']?>; font-weight: bold; text-align: center;"><?php echo $lang['view'];    ?></td>
        </tr>
        <?php
        $perms = explode(',', $forum['postperm']);
        foreach($status_enum as $key=>$val) {
            if ($key != '' And $val <= $status_enum['Guest']) {
                if (!X_SADMIN and $key == 'Super Administrator') {
                    $disabled = 'disabled="disabled"';
                } else {
                    $disabled = '';
                }
                ?>
                <tr class="tablerow">
                    <td class="category" style="color: <?php echo $THEME['cattext']; ?>; font-weight: bold; text-align: right;"><?php echo $lang[$status_translate[$val]]; ?></td>
                    <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[<?php echo X_PERMS_RAWPOLL; ?>][]" value="<?php echo $val;?>" <?php echo ((($perms[X_PERMS_RAWPOLL]&$val) == $val) ? 'checked="checked"' : ''); ?> <?php echo $disabled;?> /></td>
                    <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[<?php echo X_PERMS_RAWTHREAD; ?>][]" value="<?php echo $val;?>" <?php echo ((($perms[X_PERMS_RAWTHREAD]&$val) == $val) ? 'checked="checked"' : ''); ?> <?php echo $disabled;?> /></td>
                    <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[<?php echo X_PERMS_RAWREPLY; ?>][]" value="<?php echo $val;?>" <?php echo ((($perms[X_PERMS_RAWREPLY]&$val) == $val) ? 'checked="checked"' : ''); ?> <?php echo $disabled;?> /></td>
                    <td class="altbg1 ctrtablerow"><input type="checkbox" name="permsNew[<?php echo X_PERMS_RAWVIEW; ?>][]" value="<?php echo $val;?>" <?php echo ((($perms[X_PERMS_RAWVIEW]&$val) == $val) ? 'checked="checked"' : ''); ?> <?php echo $disabled;?> /></td>
                </tr>
                <?php
            }
        }
        ?>
        </table></td>
        </tr>

        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['textuserlist']?></td>
        <td bgcolor="<?php echo $altbg2?>"><textarea rows="4" cols="30" name="userlistnew">
<?php // Linefeed required here - Do not edit!
        echo $forum['userlist'];
        ?></textarea></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['forumpw']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="passwordnew" value="<?php echo attrOut($forum['password'], 'javascript')?>" /></td>
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
        request_secure('massedforums', '', X_NONCE_FORM_EXP, FALSE);
        $queryforum = $db->query("SELECT fid, type, fup FROM ".X_PREFIX."forums WHERE type='forum' OR type='sub'");
        while($forum = $db->fetch_array($queryforum)) {
            $displayorder = formInt('displayorder'.$forum['fid']);
            $forum['status'] = formOnOff('status'.$forum['fid']);
            $name = addslashes(htmlspecialchars(postedVar('name'.$forum['fid'], 'javascript', FALSE), ENT_COMPAT)); //Forum names are historically double-slashed.  We also have an unusual situation where ENT_COMPAT is the XMB standard.
            $delete = formInt('delete'.$forum['fid']);
            $moveto = formInt('moveto'.$forum['fid']);

            $dsuccess = FALSE;
            if ($delete == $forum['fid']) {
                if ($db->num_rows($db->query('SELECT tid FROM '.X_PREFIX.'threads WHERE fid='.$forum['fid'])) > 0) {
                    $dsuccess = FALSE;
                } elseif ($db->num_rows($db->query('SELECT fid FROM '.X_PREFIX.'forums WHERE fup='.$forum['fid'])) > 0) {
                    $dsuccess = FALSE;
                } elseif ($db->num_rows($db->query('SELECT pid FROM '.X_PREFIX.'posts WHERE fid='.$forum['fid'])) > 0) {
                    $dsuccess = FALSE;
                } else {
                    $db->query("DELETE FROM ".X_PREFIX."forums WHERE (type='forum' OR type='sub') AND fid=".$forum['fid']);
                    $dsuccess = TRUE;
                }
                if (!$dsuccess) {
                    message($lang['deleteaborted'].'<br />'.$lang['forumnotempty'], FALSE, '', '', FALSE, FALSE, FALSE, FALSE);
                }
            }

            if (!$dsuccess) {
                $settype = '';
                if ($forum['fup'] != $moveto And $moveto != $forum['fid'] And $forum['type'] != 'group') { //Forum is being moved
                    if ($moveto == 0) {
                        $settype = ", type='forum', fup=0";
                    } else {
                        $query = $db->query("SELECT type FROM ".X_PREFIX."forums WHERE fid=$moveto");
                        if ($frow = $db->fetch_array($query)) {
                            if ($frow['type'] == 'group') {
                                $settype = ", type='forum', fup=$moveto";
                            } else if ($frow['type'] == 'forum') {
                                if ($forum['type'] == 'sub') {
                                    $settype = ", fup=$moveto";
                                } else if ($forum['type'] == 'forum') { //Make sure the admin didn't try to demote a parent
                                    $query2 = $db->query("SELECT COUNT(*) AS subcount FROM ".X_PREFIX."forums WHERE fup={$forum['fid']}");
                                    $frow = $db->fetch_array($query2);
                                    $db->free_result($query2);
                                    if ($frow['subcount'] == 0) {
                                        $settype = ", type='sub', fup=$moveto";
                                    }
                                }
                            }
                        }
                        $db->free_result($query);
                    }
                }
                $db->query("UPDATE ".X_PREFIX."forums SET name='$name', displayorder=".$displayorder.", status='{$forum['status']}'$settype WHERE fid='".$forum['fid']."'");
            }
        }

        $querygroup = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE type='group'");
        while($group = $db->fetch_array($querygroup)) {
            $name = addslashes(htmlspecialchars(postedVar('name'.$group['fid'], 'javascript', FALSE), ENT_COMPAT));  //Forum names are historically double-slashed.  We also have an unusual situation where ENT_COMPAT is the XMB standard.
            $displayorder = formInt('displayorder'.$group['fid']);
            $group['status'] = formOnOff('status'.$group['fid']);
            $delete = formInt('delete'.$group['fid']);

            if ($delete == $group['fid']) {
                $query = $db->query("SELECT fid FROM ".X_PREFIX."forums WHERE type='forum' AND fup=$delete");
                if ($db->num_rows($query) > 0) {
                    message($lang['deleteaborted'].'<br />'.$lang['forumnotempty'], FALSE, '', '', FALSE, FALSE, FALSE, FALSE);
                } else {
                    $db->query("DELETE FROM ".X_PREFIX."forums WHERE type='group' AND fid=$delete");
                }
            } else {
                $db->query("UPDATE ".X_PREFIX."forums SET name='$name', displayorder=$displayorder, status='{$group['status']}' WHERE fid={$group['fid']}");
            }
        }

        $newgname = addslashes(htmlspecialchars(postedVar('newgname', 'javascript', FALSE), ENT_COMPAT));  //Forum names are historically double-slashed.  We also have an unusual situation where ENT_COMPAT is the XMB standard.
        $newfname = addslashes(htmlspecialchars(postedVar('newfname', 'javascript', FALSE), ENT_COMPAT));
        $newsubname = addslashes(htmlspecialchars(postedVar('newsubname', 'javascript', FALSE), ENT_COMPAT));
        $newgorder = formInt('newgorder');
        $newforder = formInt('newforder');
        $newsuborder = formInt('newsuborder');
        $newgstatus = formOnOff('newgstatus');
        $newfstatus = formOnOff('newfstatus');
        $newsubstatus = formOnOff('newsubstatus');
        $newffup = formInt('newffup');
        $newsubfup = formInt('newsubfup');

        if ($newfname != $lang['textnewforum'] And $newfname != '') {
            $db->query("INSERT INTO ".X_PREFIX."forums (type, name, status, lastpost, moderator, displayorder, description, allowhtml, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, password) VALUES ('forum', '$newfname', '$newfstatus', '', '', $newforder, '', 'no', 'yes', 'yes', '', 0, 0, 0, $newffup, '31,31,31,63', 'yes', 'on', '')");
        }

        if ($newgname != $lang['textnewgroup'] And $newgname != '') {
            $db->query("INSERT INTO ".X_PREFIX."forums (type, name, status, lastpost, moderator, displayorder, description, allowhtml, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, password) VALUES ('group', '$newgname', '$newgstatus', '', '', $newgorder, '', '', '', '', '', 0, 0, 0, 0, '', '', '', '')");
        }

        if ($newsubname != $lang['textnewsubf'] And $newsubname != '') {
            $db->query("INSERT INTO ".X_PREFIX."forums (type, name, status, lastpost, moderator, displayorder, description, allowhtml, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, password) VALUES ('sub', '$newsubname', '$newsubstatus', '', '', $newsuborder, '', 'no', 'yes', 'yes', '', 0, 0, 0, $newsubfup, '31,31,31,63', 'yes', 'on', '')");
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['textforumupdate'].'</td></tr>';
    } else {
        request_secure('editfid', $fdetails, X_NONCE_FORM_EXP, FALSE);
        $namenew = addslashes(htmlspecialchars(postedVar('namenew', 'javascript', FALSE), ENT_COMPAT));  //Forum names are historically double-slashed.  We also have an unusual situation where ENT_COMPAT is the XMB standard.
        $descnew = postedVar('descnew');
        $allowhtmlnew = formYesNo('allowhtmlnew');
        $allowsmiliesnew = formYesNo('allowsmiliesnew');
        $allowbbcodenew = formYesNo('allowbbcodenew');
        $allowimgcodenew = formYesNo('allowimgcodenew');
        $attachstatusnew = formOnOff('attachstatusnew');
        $themeforumnew = formInt('themeforumnew');
        $userlistnew = postedVar('userlistnew', 'javascript');
        $passwordnew = postedVar('passwordnew', '', FALSE, TRUE);
        $delete = formInt('delete');

        $overrule = array(0,0,0,0);
        if (!X_SADMIN) {
            $forum = $db->fetch_array($db->query("SELECT postperm FROM ".X_PREFIX."forums WHERE fid=$fdetails"));
            $parts = explode(',', $forum['postperm']);
            foreach($parts as $p=>$v) {
                if ($v & 1 == 1) {
                    // super admin status set
                    $overrule[$p] = 1;
                }
            }
        }

        $perms = array(0,0,0,0);
        foreach($_POST['permsNew'] as $key=>$val) {
            $perms[$key] = array_sum($_POST['permsNew'][$key]);
            $perms[$key] |= $overrule[$key];
        }
        $perms = implode(',', $perms);

        $db->query("UPDATE ".X_PREFIX."forums SET
            name='$namenew',
            description='$descnew',
            allowhtml='$allowhtmlnew',
            allowsmilies='$allowsmiliesnew',
            allowbbcode='$allowbbcodenew',
            theme='$themeforumnew',
            userlist='$userlistnew',
            postperm='$perms',
            allowimgcode='$allowimgcodenew',
            attachstatus='$attachstatusnew',
            password='$passwordnew'
            WHERE fid='$fdetails'"
        );

        $dsuccess = TRUE;
        if ($delete) {
            if ($delete == $fdetails) {
                if ($db->num_rows($db->query('SELECT tid FROM '.X_PREFIX.'threads WHERE fid='.$fdetails)) > 0) {
                    $dsuccess = FALSE;
                } elseif ($db->num_rows($db->query('SELECT fid FROM '.X_PREFIX.'forums WHERE fup='.$fdetails)) > 0) {
                    $dsuccess = FALSE;
                } elseif ($db->num_rows($db->query('SELECT pid FROM '.X_PREFIX.'posts WHERE fid='.$fdetails)) > 0) {
                    $dsuccess = FALSE;
                } else {
                    $db->query("DELETE FROM ".X_PREFIX."forums WHERE (type='forum' OR type='sub') AND fid=".$fdetails);
                    $dsuccess = TRUE;
                }
                if (!$dsuccess) {
                    message($lang['deleteaborted'].'<br />'.$lang['forumnotempty'], FALSE, '', '', FALSE, FALSE, FALSE, FALSE);
                }
            }
        }
        if ($dsuccess) {
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['textforumupdate'].'</td></tr>';
        }
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
                <td colspan="2"><strong><?php echo fnameOut($forum['cat_name'])?></strong></td>
                </tr>
                <?php
            }
            ?>
            <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
            <td><?php echo fnameOut($forum['name'])?></td>
            <td><input type="text" name="mod[<?php echo $forum['fid']?>]"" value="<?php echo $forum['moderator']?>" /></td>
            </tr>
            <?php
            $querys = $db->query("SELECT name, fid, moderator FROM ".X_PREFIX."forums WHERE fup='".$forum['fid']."' AND type='sub'");
            while($sub = $db->fetch_array($querys)) {
                ?>
                <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
                <td><?php echo $lang['4spaces']?><?php echo $lang['4spaces']?><em><?php echo fnameOut($sub['name'])?></em></td>
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
        $mod = postedArray('mod');
        if (is_array($mod)) {
            foreach($mod as $fid=>$mods) {
                $db->query("UPDATE ".X_PREFIX."forums SET moderator='$mods' WHERE fid='$fid'");
            }
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['textmodupdate'].'</td></tr>';
    }
}

if ($action == "members") {
    $members = postedVar('members', '', FALSE, FALSE, FALSE, 'g');

    $srchmem = postedVar('srchmem', 'javascript', TRUE, FALSE, TRUE);
    $srchemail = postedVar('srchemail', 'javascript', TRUE, FALSE, TRUE);
    $srchip = postedVar('srchip', 'javascript', TRUE, FALSE, TRUE);
    $srchstatus = postedVar('srchstatus', 'javascript', TRUE, TRUE, TRUE);
    $dblikemem = $db->like_escape($srchmem);
    $dblikeemail = $db->like_escape($srchemail);
    $dblikeip = $db->like_escape($srchip);

    $where = array();

    if ($srchmem != '') {
        $where[] = "username LIKE '%$dblikemem%' ";
    }
    if ($srchemail != '') {
        $where[] = "email LIKE '%$dblikeemail%' ";
    }
    if ($srchip != '') {
        $where[] = "regip LIKE '%$dblikeip%' ";
    }
    if ($srchstatus != '') {
        if ($srchstatus == 'Pending') {
            $where[] = "lastvisit = 0 ";
        } else {
            $where[] = "status = '$srchstatus' ";
        }
    }

    if (count($where) == 0) {
        $where = '';
    } else {
        $where = 'WHERE '.implode('AND ', $where);
    }


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
            <td bgcolor="<?php echo $altbg1?>" width="22%"><?php echo $lang['textsrchemail']?></td>
            <td bgcolor="<?php echo $altbg2?>"><input type="text" name="srchemail" /></td>
            </tr>
            <tr class="tablerow">
            <td bgcolor="<?php echo $altbg1?>" width="22%"><?php echo $lang['textsrchip']?></td>
            <td bgcolor="<?php echo $altbg2?>"><input type="text" name="srchip" /></td>
            </tr>
            <tr class="tablerow">
            <td bgcolor="<?php echo $altbg1?>" width="22%"><?php echo $lang['textwithstatus']?></td>
            <td bgcolor="<?php echo $altbg2?>">
            <select name="srchstatus">
            <option value=""><?php echo $lang['anystatus']?></option>
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
            <input type="hidden" name="token" value="<?php echo nonce_create('masseditmems'); ?>" />
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

            $query = $db->query("SELECT * FROM ".X_PREFIX."members $where ORDER BY username");

            while($member = $db->fetch_array($query)) {
                $sadminselect = $adminselect = $smodselect = '';
                $modselect = $memselect = $banselect = '';
                $noban = $u2uban = $postban = $bothban = '';

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

                if ($member['status'] == 'Super Administrator') {
                    $disabledelete = ' disabled="disabled"';
                } else {
                    $disabledelete = '';
                }
                ?>
                <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
                <td align="center"><input type="checkbox" name="delete<?php echo $member['uid']?>" onclick="addUserDel(<?php echo $member['uid']?>, '<?php echo $member['username']?>', this)" value="<?php echo $member['uid']?>"<?php echo $disabledelete; ?> /></td>
                <td><a href="member.php?action=viewpro&amp;member=<?php echo recodeOut($member['username']); ?>"><?php echo $member['username']?></a>
                <?php if (X_SADMIN) { ?>
                <br /><a href="editprofile.php?user=<?php echo recodeOut($member['username']); ?>"><strong><?php echo $lang['admin_edituseraccount']; ?></strong></a>
                <?php } ?>
                <br /><a href="cp.php?action=deleteposts&amp;member=<?php echo recodeOut($member['username'])?>"><strong><?php echo $lang['cp_deleteposts']?></strong></a><?php echo $pending ?>
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
                <td><input type="text" size="16" name="cusstatus<?php echo $member['uid']?>" value="<?php echo attrOut($member['customstatus']); ?>" /></td>
                <td><select name="banstatus<?php echo $member['uid']?>">
                <option value="" <?php echo $noban?>><?php echo $lang['noban']?></option>
                <option value="u2u" <?php echo $u2uban?>><?php echo $lang['banu2u']?></option>
                <option value="posts" <?php echo $postban?>><?php echo $lang['banpost']?></option>
                <option value="both" <?php echo $bothban?>><?php echo $lang['banboth']?></option>
                </select></td>
                </tr>
                <?php
            }
            ?>
            <tr>
            <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow" colspan="7">
             <input type="submit" class="submit" name="membersubmit" value="<?php echo $lang['textsubmitchanges']; ?>" onclick="return confirmUserDel('<?php echo $lang['confirmDeleteUser']; ?>');" />
             <input type="hidden" name="srchmem" value="<?php echo $srchmem; ?>" />
             <input type="hidden" name="srchemail" value="<?php echo $srchemail; ?>" />
             <input type="hidden" name="srchip" value="<?php echo $srchip; ?>" />
             <input type="hidden" name="srchstatus" value="<?php echo $srchstatus; ?>" />
            </td>
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
        request_secure('masseditmems', '', X_NONCE_FORM_EXP, FALSE);
        $query = $db->query("SELECT uid, username, password, status FROM ".X_PREFIX."members $where");

        // Guarantee this request will not remove all Super Administrators.
        if (X_SADMIN And $db->num_rows($query) > 0) {
            $saquery = $db->query("SELECT COUNT(uid) FROM ".X_PREFIX."members WHERE status='Super Administrator'");
            $sa_count = $db->result($saquery, 0);
            $db->free_result($saquery);

            while($mem = $db->fetch_array($query)) {
                if ($mem['status'] == 'Super Administrator' And postedVar('status'.$mem['uid']) != 'Super Administrator') {
                    $sa_count--;
                }
            }
            if ($sa_count < 1) {
                error($lang['lastsadmin'], false, '</td></tr></table></td></tr></table><br />');
            }
            $db->data_seek($query, 0);
        }

        // Now execute this request
        while($mem = $db->fetch_array($query)) {
            $origstatus = $mem['status'];
            $status = postedVar('status'.$mem['uid']);
            if ($status == '') {
                $status = 'Member';
            }

            if (!X_SADMIN && ($origstatus == "Super Administrator" || $status == "Super Administrator")) {
                continue;
            }

            $banstatus = postedVar('banstatus'.$mem['uid']);
            $cusstatus = postedVar('cusstatus'.$mem['uid'], '', FALSE);
            $postnum = getInt('postnum'.$mem['uid'], 'p');
            $delete = getInt('delete'.$mem['uid'], 'p');

            $queryadd = '';
            if (isset($_POST['pw'.$mem['uid']])) {
                if ($_POST['pw'.$mem['uid']] != '') {
                    $newpw = md5($_POST['pw'.$mem['uid']]);
                    $queryadd = ", password='$newpw' ";
                }
            }

            if ($delete == $mem['uid'] && $delete != $self['uid'] && $origstatus != "Super Administrator") {
                $db->escape_fast($mem['username']);
                $db->query("DELETE FROM ".X_PREFIX."members WHERE uid=$delete");
                $db->query("DELETE FROM ".X_PREFIX."buddys WHERE username='{$mem['username']}'");
                $db->query("DELETE FROM ".X_PREFIX."favorites WHERE username='{$mem['username']}'");
                $db->query("DELETE FROM ".X_PREFIX."u2u WHERE owner='{$mem['username']}'");
                $db->query("UPDATE ".X_PREFIX."whosonline SET username='xguest123' WHERE username='{$mem['username']}'");
            } else {
                $db->query("UPDATE ".X_PREFIX."members SET ban='$banstatus', status='$status', postnum='$postnum', customstatus='$cusstatus'$queryadd WHERE uid={$mem['uid']}");
            }
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['textmembersupdate'].'</td></tr>';
    }
}

if ($action == "ipban") {
    if ($SETTINGS['ip_banning'] == 'on') {
        if (noSubmit('ipbansubmit') And noSubmit('ipbandisable')) {
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
                <td><?php echo $ipdate?></td>
                </tr>
                <?php
            }

            $ips = explode(".", $onlineip);
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
            <br /><div align="center">
            <input type="submit" class="submit" name="ipbansubmit" value="<?php echo $lang['textsubmitchanges']; ?>" />
            <input type="submit" class="submit" name="ipbandisable" value="<?php echo $lang['ipbandisable']; ?>" />
            </div>
            </form>
            </td>
            </tr>
            <?php
        } elseif (onSubmit('ipbandisable')) {
            $db->query('UPDATE '.X_PREFIX.'settings SET ip_banning="off"');
            echo '<tr bgcolor="'.$altbg2.'"><td class="ctrtablerow">'.$lang['textipupdate'].'</td></tr>';
        } else {
            $newip = array();
            $newip[] = (is_numeric(postedVar('newip1')) Or postedVar('newip1') == '*') ? trim(postedVar('newip1')) : '0' ;
            $newip[] = (is_numeric(postedVar('newip2')) Or postedVar('newip2') == '*') ? trim(postedVar('newip2')) : '0' ;
            $newip[] = (is_numeric(postedVar('newip3')) Or postedVar('newip3') == '*') ? trim(postedVar('newip3')) : '0' ;
            $newip[] = (is_numeric(postedVar('newip4')) Or postedVar('newip4') == '*') ? trim(postedVar('newip4')) : '0' ;
            $delete = postedArray('delete', 'int');

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

            if ($newip[0] != '0' || $newip[1] != '0' || $newip[2] != '0' || $newip[3] != '0') {
                $invalid = 0;
                for($i=0; $i<=3 && !$invalid; ++$i) {
                    if ($newip[$i] == "*") {
                        $ip[$i+1] = -1;
                    } else if (intval($newip[$i]) >=0 And intval($newip[$i]) <= 255) {
                        $ip[$i+1] = intval($newip[$i]);
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
    } else {
        if (noSubmit('ipbanenable')) {
            ?>
            <tr bgcolor="<?php echo $altbg2?>">
            <td align="center">
            <form method="post" action="cp.php?action=ipban">
            <div align="center">
            <input type="submit" class="submit" name="ipbanenable" value="<?php echo $lang['ipbanenable']; ?>" />
            </div>
            </form>
            </td>
            </tr>
            <?php
        } else {
            $db->query('UPDATE '.X_PREFIX.'settings SET ip_banning="on"');
            echo '<tr bgcolor="'.$altbg2.'"><td class="ctrtablerow">'.$lang['textipupdate'].'</td></tr>';
        }
    }
}

if ($action == "deleteposts") {
    $member = postedVar('member', '', TRUE, TRUE, FALSE, 'g');
    if (noSubmit('yessubmit')) {
        $key = template_key('delps', $member);
        ?>
        <tr bgcolor="<?php echo $altbg2; ?>" class="ctrtablerow"><td><?php echo $lang['confirmDeletePosts']; ?><br />
        <form action="cp.php?action=deleteposts&amp;member=<?php echo recodeOut($member); ?>" method="post">
          <input type="hidden" name="token" value="<?php echo nonce_create($key); ?>" />
          <input type="submit" name="yessubmit" value="<?php echo $lang['textyes']; ?>" /> -
          <input type="submit" name="yessubmit" value="<?php echo $lang['textno']; ?>" />
        </form></td></tr>
        <?php
    } elseif ($lang['textyes'] == $yessubmit) {
        request_secure('delps', $member, X_NONCE_AYS_EXP, FALSE);
        require('include/attach-admin.inc.php');

        // Get TIDs
        $dirty = array();
        $countquery = $db->query("SELECT tid FROM ".X_PREFIX."posts WHERE author='$member' GROUP BY tid");
        while($post = $db->fetch_array($countquery)) {
            $dirty[] = $post['tid'];
        }
        $db->free_result($countquery);

        // Get FIDs
        $fids = array();
        if (count($dirty) > 0) {
            $csv = implode(',', $dirty);
            $countquery = $db->query("SELECT fid FROM ".X_PREFIX."threads WHERE tid IN ($csv) GROUP BY fid");
            while($thread = $db->fetch_array($countquery)) {
                $fids[] = $thread['fid'];
            }
            $db->free_result($countquery);
        }

        // Delete Member's Posts
        deleteAttachmentsByUser($member);
        $db->query("DELETE FROM ".X_PREFIX."posts WHERE author='$member'");
        $db->query("UPDATE ".X_PREFIX."members SET postnum = 0 WHERE username='$member'");

        // Delete Empty Threads
        // This will also delete thread redirectors where the redirect's author is $member
        $tids = array();
        $movedids = array();
        $countquery = $db->query("SELECT t.tid FROM ".X_PREFIX."threads AS t LEFT JOIN ".X_PREFIX."posts AS p USING (tid) WHERE t.closed NOT LIKE 'moved%' GROUP BY t.tid HAVING COUNT(p.pid) = 0");
        while($threads = $db->fetch_array($countquery)) {
            $tids[] = $threads['tid'];
            $movedids[] = 'moved|'.$threads['tid'];
        }
        $db->free_result($countquery);
        if (count($tids) > 0) {
            $csv = implode(',', $tids);
            $movedids = implode("', '", $movedids);
            $db->query("DELETE FROM ".X_PREFIX."threads WHERE tid IN ($csv) OR closed IN ('$movedids')");
            $db->query("DELETE FROM ".X_PREFIX."favorites WHERE tid IN ($csv)");
            $db->query("DELETE FROM d, r, v "
                     . "USING ".X_PREFIX."vote_desc AS d "
                     . "LEFT JOIN ".X_PREFIX."vote_results AS r ON r.vote_id = d.vote_id "
                     . "LEFT JOIN ".X_PREFIX."vote_voters AS v  ON v.vote_id = d.vote_id "
                     . "WHERE d.topic_id IN ($csv)");
        }

        // Update Thread Stats
        $dirty = array_diff($dirty, $tids);
        foreach($dirty as $tid) {
            updatethreadcount($tid);
        }

        // Update Forum Stats
        $fids = array_unique($fids);
        $fups = array();
        foreach ($fids as $fid) {
            $forum = getForum($fid);
            if ('sub' == $forum['type']) {
                $fups[] = $forum['fup'];
            }
        }
        $fids = array_unique(array_merge($fids, $fups));
        foreach ($fids as $fid) {
            updateforumcount($fid);
        }

        echo "<p align=\"center\">Deleted ...</br>";
    }
}

if ($action == "upgrade") {
    if (!X_SADMIN) {
        error($lang['superadminonly'], false, '</td></tr></table></td></tr></table><br />');
    }

    if (onSubmit('upgradesubmit')) {
        request_secure('insertrawsql', '', X_NONCE_FORM_EXP, FALSE);
        $upgrade = postedVar('upgrade', '', FALSE, FALSE);
        if (isset($_FILES['sql_file'])) {
            require('include/attach.inc.php');
            $filename = '';
            $filetype = '';
            $filesize = 0;
            $add = get_attached_file('sql_file', $filename, $filetype, $filesize, FALSE);
            if ($add !== FALSE) {
                $upgrade .= $add;
                unlink($_FILES['sql_file']['tmp_name']);
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
            if ($allow_spec_q !== true) {
                if (strtoupper(substr(trim($explode[$num]), 0, 3)) == 'USE' || strtoupper(substr(trim($explode[$num]), 0, 14)) == 'SHOW DATABASES') {
                    error($lang['textillegalquery'], false, '</td></tr></table></td></tr></table><br />');
                }
            }

            if ($explode[$num] != '') {
                $query = $db->query($explode[$num]." -- Injected by $xmbuser using cp.php");

                if (is_bool($query)) {
                    $numfields = 1;
                } else {
                    $numfields = $db->num_fields($query);
                }

                echo '<br />';
                ?>
                <table cellspacing="0" cellpadding="0" border="0" width="<?php echo $tablewidth?>" align="center">
                <tr>
                <td bgcolor="<?php echo $bordercolor?>">
                <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
                <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
                <td colspan="<?php echo $numfields; ?>"><strong><?php echo $lang['upgraderesults']?></strong>&nbsp;<?php echo cdataOut($explode[$num]); ?>
                <?php
                $xn = strtoupper($explode[$num]);
                if (!is_bool($query)) {
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
        <input type="hidden" name="token" value="<?php echo nonce_create('insertrawsql'); ?>" />
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
        smcwcache();
        $userip = postedVar('userip');
        $postip = postedVar('postip');
        $dblikeprofile = $db->like_escape(postedVar('profileword', '', TRUE, FALSE));
        $dblikepost = $db->like_escape(postedVar('postword', '', TRUE, FALSE));

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
                    $list[] = '<a href="'.$link.'">'.rawHTMLsubject(stripslashes($users['subject'])).'<br />';
                } else {
                    $list[] = "<a href = \"$link\">- - No subject - -<br />";
                }
                $found++;
            }
        }

        if ($dblikeprofile != '') {
            $query = $db->query("SELECT * FROM ".X_PREFIX."members WHERE bio LIKE '%$dblikeprofile%'");
            while($users = $db->fetch_array($query)) {
                $link = "./member.php?action=viewpro&amp;member=".recodeOut($users['username']);
                $list[] = "<a href = \"$link\">{$users['username']}<br />";
                $found++;
            }
        }

        if ($dblikepost != '') {
            $query = $db->query("SELECT * FROM ".X_PREFIX."posts WHERE subject LIKE '%$dblikepost%' OR message LIKE '%$dblikepost%'");
            while($users = $db->fetch_array($query)) {
                $link = "./viewthread.php?tid=$users[tid]#pid$users[pid]";
                if (!empty($users['subject'])) {
                    $list[] = '<a href="'.$link.'">'.rawHTMLsubject(stripslashes($users['subject'])).'<br />';
                } else {
                    $list[] = '<a href="'.$link.'">- - No subject - -<br />';
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
            <?php echo $val; ?>
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
        <div align="center"><br /><input type="submit" class="submit" name="searchsubmit" value="<?php echo $lang['cpsearch']; ?>" /><br /><br /></div>
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
