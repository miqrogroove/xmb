<?php
/* $Id: cp_settings.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
/*
    © 2001 - 2007 The XMB Development Team
    http://www.xmbforum.com

    Financial and other support 2007- iEntry Inc
    http://www.ientry.com

    Financial and other support 2002-2007 Aventure Media 
    http://www.aventure-media.co.uk

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

if (!defined('IN_CODE') && (defined('DEBUG') && DEBUG == false)) {
    exit ("Not allowed to run this file directly.");
}

function displaySettingsPanel()
{
    global $oToken, $lang, $selHTML, $SETTINGS, $THEME;
    global $db, $table_themes;

    $langfileselect = createLangFileSelect($SETTINGS['langfile']); 

    $themelist   = array();
    $themelist[] = '<select name="themenew">';
    $query = $db->query("SELECT themeid, name FROM $table_themes ORDER BY name ASC");
    while ($themeinfo = $db->fetch_array($query)) {
        if ($themeinfo['themeid'] == $SETTINGS['theme']) {
            $themelist[] = '<option value="'.$themeinfo['themeid'].'" selected="selected">'.stripslashes($themeinfo['name']).'</option>';
        } else {
            $themelist[] = '<option value="'.$themeinfo['themeid'].'">'.stripslashes($themeinfo['name']).'</option>';
        }
    }
    $themelist[] = '</select>';
    $themelist   = implode("\n", $themelist);

    $onselect = $offselect = '';
    if ($SETTINGS['bbstatus'] == "on") {
        $onselect = $selHTML;
    } else {
        $offselect = $selHTML;
    }

    $whosonlineon = $whosonlineoff = '';
    if ($SETTINGS['whosonlinestatus'] == "on") {
        $whosonlineon = $selHTML;
    } else {
        $whosonlineoff = $selHTML;
    }

    $regon = $regoff = '';
    if ($SETTINGS['regstatus'] == "on") {
        $regon = $selHTML;
    } else {
        $regoff = $selHTML;
    }

    $regonlyon = $regonlyoff = '';
    if ($SETTINGS['regviewonly'] == "on") {
        $regonlyon = $selHTML;
    } else {
        $regonlyoff = $selHTML;
    }

    $catsonlyon = $catsonlyoff = '';
    if ($SETTINGS['catsonly'] == "on") {
        $catsonlyon = $selHTML;
    } else {
        $catsonlyoff = $selHTML;
    }

    $hideon = $hideoff = '';
    if ($SETTINGS['hideprivate'] == "on") {
        $hideon = $selHTML;
    } else {
        $hideoff = $selHTML;
    }

    $echeckon = $echeckoff = '';
    if ($SETTINGS['emailcheck'] == "on") {
        $echeckon = $selHTML;
    } else {
        $echeckoff = $selHTML;
    }

    $ruleson = $rulesoff = '';
    if ($SETTINGS['bbrules'] == "on") {
        $ruleson = $selHTML;
    } else {
        $rulesoff = $selHTML;
    }

    $searchon = $searchoff = '';
    if ($SETTINGS['searchstatus'] == "on") {
        $searchon = $selHTML;
    } else {
        $searchoff = $selHTML;
    }

    $faqon = $faqoff = '';
    if ($SETTINGS['faqstatus'] == "on") {
        $faqon = $selHTML;
    } else {
        $faqoff = $selHTML;
    }

    $memliston = $memlistoff = '';
    if ($SETTINGS['memliststatus'] == "on") {
        $memliston = $selHTML;
    } else {
        $memlistoff = $selHTML;
    }

    $todayon = $todayoff = '';
    if ($SETTINGS['todaysposts'] == "on") {
        $todayon = $selHTML;
    } else {
        $todayoff = $selHTML;
    }

    $statson = $statsoff = '';
    if ($SETTINGS['stats'] == "on") {
        $statson = $selHTML;
    } else {
        $statsoff = $selHTML;
    }

    $avataron = $avataroff = $avatarlist = '';
    if ($SETTINGS['avastatus'] == "on") {
        $avataron = $selHTML;
    } elseif ($SETTINGS['avastatus'] == "list") {
        $avatarlist = $selHTML;
    } else {
        $avataroff = $selHTML;
    }

    $gzipcompresson = $gzipcompressoff = '';
    if ($SETTINGS['gzipcompress'] == "on") {
        $gzipcompresson = $selHTML;
    } else {
        $gzipcompressoff = $selHTML;
    }

    $coppaon = $coppaoff = '';
    if ($SETTINGS['coppa'] == "on") {
        $coppaon = $selHTML;
    } else {
        $coppaoff = $selHTML;
    }

    $check12 = $check24 = '';
    if ($SETTINGS['timeformat'] == "24") {
        $check24 = "checked=\"checked\"";
    } else {
        $check12 = "checked=\"checked\"";
    }

    $sigbbcodeon = $sigbbcodeoff = '';
    if ($SETTINGS['sigbbcode'] == "on") {
        $sigbbcodeon = $selHTML;
    } else {
        $sigbbcodeoff = $selHTML;
    }

    $sightmlon = $sightmloff = '';
    if ($SETTINGS['sightml'] == "on") {
        $sightmlon = $selHTML;
    } else {
        $sightmloff = $selHTML;
    }

    $reportposton = $reportpostoff = '';
    if ($SETTINGS['reportpost'] == "on") {
        $reportposton = $selHTML;
    } else {
        $reportpostoff = $selHTML;
    }

    $bbinserton = $bbinsertoff = '';
    if ($SETTINGS['bbinsert'] != "on") {
        $bbinsertoff = $selHTML;
    } else {
        $bbinserton = $selHTML;
    }

    $smileyinserton = $smileyinsertoff = '';
    if ($SETTINGS['smileyinsert'] != "on") {
        $smileyinsertoff = $selHTML;
    } else {
        $smileyinserton = $selHTML;
    }

    $doubleeon = $doubleeoff = '';
    if ($SETTINGS['doublee'] == "on") {
        $doubleeon = $selHTML;
    } else {
        $doubleeoff = $selHTML;
    }

    $editedbyon = $editedbyoff = '';
    if ($SETTINGS['editedby'] == "on") {
        $editedbyon = $selHTML;
    } else {
        $editedbyoff = $selHTML;
    }

    $dotfolderson = $dotfoldersoff = '';
    if ($SETTINGS['dotfolders'] == "on") {
        $dotfolderson = $selHTML;
    } else {
        $dotfoldersoff = $selHTML;
    }

    $attachimgposton = $attachimgpostoff = '';
    if ($SETTINGS['attachimgpost'] == "on") {
        $attachimgposton = $selHTML;
    } else {
        $attachimgpostoff = $selHTML;
    }

    $tickerstatuson = $tickerstatusoff = '';
    if ($SETTINGS['tickerstatus'] == "on") {
        $tickerstatuson = $selHTML;
    } else {
        $tickerstatusoff = $selHTML;
    }

    $spacecatson = $spacecatsoff = '';
    if ($SETTINGS['space_cats'] == "on") {
        $spacecatson = $selHTML;
    } else {
        $spacecatsoff = $selHTML;
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

    $subjectInTitleOn = $subjectInTitleOff = '';
    if ($SETTINGS['subject_in_title'] == "on") {
        $subjectInTitleOn = $selHTML;
    } else {
        $subjectInTitleOff = $selHTML;
    }

    $allowrankediton = $allowrankeditoff = '';
    if ($SETTINGS['allowrankedit'] == "on") {
        $allowrankediton = $selHTML;
    } else {
        $allowrankeditoff = $selHTML;
    }

    $spell_off_reason = '';
    if (!defined('PSPELL_FAST')) {
        $spell_off_reason = $lang['pspell_needed'];
        $SETTINGS['spellcheck'] = 'off';
    }

    $spellcheckon = $spellcheckoff = '';
    if ($SETTINGS['spellcheck'] == "on") {
        $spellcheckon = $selHTML;
    } else {
        $spellcheckoff = $selHTML;
    }

    $resetSigOn = $resetSigOff = '';
    if($SETTINGS['resetsigs'] == 'on') {
        $resetSigOn = $selHTML;
    } else {
        $resetSigOff = $selHTML;
    }

    $notifycheck[0] = false;
    $notifycheck[1] = false;
    $notifycheck[2] = false;

    if ($SETTINGS['notifyonreg'] == "off") {
        $notifycheck[0] = true;
    } elseif ($SETTINGS['notifyonreg'] == "u2u") {
            $notifycheck[1] = true;
        } else {
            $notifycheck[2] = true;
        }

        $allowipreg[0] = false;
        $allowipreg[1] = false;
 
        if ($SETTINGS['ipreg'] == "on") {
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
    } elseif (!empty($avataroff)) {
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
    $names = array('Enable Server Load', 'Enable Queries', 'Enable PHP/SQL Calculation', 'Enable Page-loadtimes');
    $checked = array($sel_serverload, $sel_queries, $sel_phpsql, $sel_loadtimes);

    $SETTINGS['bboffreason'] = stripslashes($SETTINGS['bboffreason']);
    $SETTINGS['bbrulestxt'] = stripslashes($SETTINGS['bbrulestxt']);
    $SETTINGS['tickercontents'] = stripslashes($SETTINGS['tickercontents']);
    $max_avatar_sizes = explode('x', $SETTINGS['max_avatar_size']);
    $lang['spell_checker'] .= $spell_off_reason;
    ?>

    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp.php?action=settings">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="600" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td colspan="2"><strong><font color="<?php echo $THEME['cattext']?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings1']?></font></strong></td>
    </tr>

    <?php
    printsetting2($lang['textsitename'], "sitenamenew", $SETTINGS['sitename'], "50");
    printsetting2($lang['bbname'], "bbnamenew", $SETTINGS['bbname'], "50");
    printsetting2($lang['textsiteurl'], "siteurlnew", $SETTINGS['siteurl'], "50");
    printsetting2($lang['textboardurl'], "boardurlnew", $SETTINGS['boardurl'], "50");
    printsetting2($lang['adminemail'], "adminemailnew", $SETTINGS['adminemail'], "50");
    printsetting1($lang['textbbrules'], 'bbrulesnew', $ruleson, $rulesoff);
    printsetting4($lang['textbbrulestxt'], 'bbrulestxtnew', $SETTINGS['bbrulestxt'], 5, 50);
    printsetting1($lang['textbstatus'], "bbstatusnew", $onselect, $offselect);
    printsetting4($lang['textbboffreason'], 'bboffreasonnew', $SETTINGS['bboffreason'], 5, 50);
    printsetting1($lang['gzipcompression'], 'gzipcompressnew', $gzipcompresson, $gzipcompressoff);
    ?>

    <tr>
    <td class="tablerow altbg2" colspan="2">&nbsp;</td>
    </tr>
    <tr>
    <td colspan="2" class="category"><strong><font style="color: <?php echo $THEME['cattext']?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings2']?></font></strong></td>
    </tr>
    <tr>
    <td class="tablerow altbg1"><?php echo $lang['textlanguage']?></td>
    <td class="tablerow altbg2"><?php echo $langfileselect?></td>
    </tr>
    <tr>
    <td class="tablerow altbg1"><?php echo $lang['texttheme']?></td>
    <td class="tablerow altbg2"><?php echo $themelist?></td>
    </tr>

    <?php
    printsetting2($lang['textppp'], "postperpagenew", $SETTINGS['postperpage'], 3);
    printsetting2($lang['texttpp'], "topicperpagenew", $SETTINGS['topicperpage'], 3);
    printsetting2($lang['textmpp'], "memberperpagenew", $SETTINGS['memberperpage'], 3);
    ?>

    <tr>
    <td class="tablerow altbg1"><?php echo $lang['texttimeformat']?></td>
    <td class="tablerow altbg2"><input type="radio" value="24" name="timeformatnew" <?php echo $check24?> />&nbsp;<?php echo $lang['text24hour']?>&nbsp;<input type="radio" value="12" name="timeformatnew" <?php echo $check12?> />&nbsp;<?php echo $lang['text12hour']?></td>
    </tr>

    <?php
    printsetting2($lang['dateformat'], "dateformatnew", $SETTINGS['dateformat'], "20");
    ?>

    <tr>
    <td class="tablerow altbg2" colspan="2">&nbsp;</td>
    </tr>
    <tr>
    <td colspan="2" class="category"><strong><font style="color: <?php echo $THEME['cattext']?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings3']?></font></strong></td>
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

    <tr>
    <td class="tablerow altbg2" colspan="2">&nbsp;</td>
    </tr>
    <tr>
    <td colspan="2" class="category"><strong><font style="color: <?php echo $THEME['cattext']?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings4']?></font></strong></td>
    </tr>

    <?php
    printsetting1($lang['space_cats'], 'space_catsnew',$spacecatson, $spacecatsoff);
    printsetting3($lang['indexShowBarDesc'], 'indexShowBarNew', array($lang['indexShowBarCats'], $lang['indexShowBarTop'], $lang['indexShowBarNone']), array(1, 2, 3), array($indexShowBarCats, $indexShowBarTop, $indexShowBarNone), false);
    printsetting1($lang['allowrankedit'], 'allowrankeditnew', $allowrankediton, $allowrankeditoff);
    printsetting1($lang['subjectInTitle'], 'subjectInTitleNew', $subjectInTitleOn, $subjectInTitleOff);
    printsetting1($lang['textcatsonly'], 'catsonlynew', $catsonlyon, $catsonlyoff);
    printsetting1($lang['whosonline_on'], 'whos_on', $whosonlineon, $whosonlineoff);
    printsetting2($lang['smtotal'], "smtotalnew", $SETTINGS['smtotal'], 5);
    printsetting2($lang['smcols'], "smcolsnew", $SETTINGS['smcols'], 5);
    printsetting1($lang['dotfolders'], "dotfoldersnew", $dotfolderson, $dotfoldersoff);
    printsetting1($lang['editedby'], "editedbynew", $editedbyon, $editedbyoff);
    printsetting1($lang['attachimginpost'], "attachimgpostnew", $attachimgposton, $attachimgpostoff);
    ?>

    <tr>
    <td class="tablerow altbg2" colspan="2">&nbsp;</td>
    </tr>
    <tr>
    <td colspan="2" class="category"><strong><font style="color: <?php echo $THEME['cattext']?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings5']?></font></strong></td>
    </tr>

    <?php
    printsetting1($lang['reg_on'], 'reg_on', $regon, $regoff);
    printsetting3($lang['ipreg'], 'ipReg', array($lang['textyes'], $lang['textno']), array('on', 'off'), $allowipreg, false);
    printsetting2($lang['max_daily_regs'], 'maxDayReg', $SETTINGS['maxdayreg'], 3);
    printsetting3($lang['notifyonreg'], 'notifyonregnew', array($lang['textoff'], $lang['viau2u'], $lang['viaemail']), array('off', 'u2u', 'email'), $notifycheck, false);
    printsetting1($lang['textreggedonly'], 'regviewnew', $regonlyon, $regonlyoff);
    printsetting1($lang['texthidepriv'], 'hidepriv', $hideon, $hideoff);
    printsetting1($lang['emailverify'], 'emailchecknew',$echeckon, $echeckoff);
    printsetting2($lang['textflood'], "floodctrlnew", $SETTINGS['floodctrl'], 3);
    printsetting2($lang['u2uquota'], "u2uquotanew", $SETTINGS['u2uquota'], 3);
    printsetting3($lang['textavastatus'], 'avastatusnew', array($lang['texton'], $lang['textlist'], $lang['textoff']), array('on', 'list', 'off'), $avchecked, false);
    printsetting1($lang['resetSigDesc'], 'resetSigNew', $resetSigOn, $resetSigOff);
    printsetting1($lang['doublee'], 'doubleenew', $doubleeon, $doubleeoff);
    printsetting2($lang['pruneusers'], "pruneusersnew", $SETTINGS['pruneusers'], 3);
    ?>

    <tr>
    <td class="tablerow altbg2" colspan="2">&nbsp;</td>
    </tr>
    <tr>
    <td colspan="2" class="category"><strong><font style="color: <?php echo $THEME['cattext']?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings6']?></font></strong></td>
    </tr>

    <?php
    printsetting2($lang['texthottopic'], "hottopicnew", $SETTINGS['hottopic'], 3);
    printsetting1($lang['bbinsert'], 'bbinsertnew', $bbinserton, $bbinsertoff);
    printsetting1($lang['smileyinsert'], 'smileyinsertnew', $smileyinserton, $smileyinsertoff);
    printsetting3($lang['footer_options'], 'new_footer_options', $names, $values, $checked);
    printsetting2($lang['max_attachment_size'], 'maxAttachSize', $SETTINGS['maxattachsize'], 8);
    printsetting3($lang['defaultTimezoneDesc'], 'def_tz_new', array($lang['timezone1'], $lang['timezone2'], $lang['timezone3'], $lang['timezone4'], $lang['timezone5'], $lang['timezone6'], $lang['timezone7'], $lang['timezone8'], $lang['timezone9'], $lang['timezone10'], $lang['timezone11'], $lang['timezone12'], $lang['timezone13'], $lang['timezone14'], $lang['timezone15'], $lang['timezone16'], $lang['timezone17'], $lang['timezone18'], $lang['timezone19'], $lang['timezone20'], $lang['timezone21'], $lang['timezone22'], $lang['timezone23'], $lang['timezone24'], $lang['timezone25'], $lang['timezone26'], $lang['timezone27'], $lang['timezone28'], $lang['timezone29'], $lang['timezone30'], $lang['timezone31'], $lang['timezone32'], $lang['timezone33']), array('-12', '-11', '-10', '-9', '-8', '-7', '-6', '-5', '-4', '-3.5', '-3', '-2', '-1', '0', '1', '2', '3', '3.5', '4', '4.5', '5', '5.5', '5.75', '6', '6.5', '7', '8', '9', '9.5', '10', '11', '12', '13'), array($timezone1, $timezone2, $timezone3, $timezone4, $timezone5, $timezone6, $timezone7, $timezone8, $timezone9, $timezone10, $timezone11, $timezone12, $timezone13, $timezone14, $timezone15, $timezone16, $timezone17, $timezone18, $timezone19, $timezone20, $timezone21, $timezone22, $timezone23, $timezone24, $timezone25, $timezone26, $timezone27, $timezone28, $timezone29, $timezone30, $timezone31, $timezone32, $timezone33), false);
    printsetting2($lang['addtime'], "addtimenew", $SETTINGS['addtime'], 3);
    printsetting1($lang['sigbbcode'], 'sigbbcodenew', $sigbbcodeon, $sigbbcodeoff);
    printsetting1($lang['sightml'], 'sightmlnew', $sightmlon, $sightmloff);
    printsetting2($lang['max_avatar_size_w'], "max_avatar_size_w_new", $max_avatar_sizes[0], 4);
    printsetting2($lang['max_avatar_size_h'], "max_avatar_size_h_new", $max_avatar_sizes[1], 4);
    printsetting1($lang['what_tickerstatus'], "tickerstatusnew", $tickerstatuson, $tickerstatusoff);
    printsetting2($lang['what_tickerdelay'], "tickerdelaynew", $SETTINGS['tickerdelay'], "5");
    printsetting4($lang['tickercontents'], 'tickercontentsnew', $SETTINGS['tickercontents'], 5, 50);
    ?>

    <tr>
    <td align="center" class="tablerow altbg2" colspan="2"><input class="submit" type="submit" name="settingsubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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

function processSettings()
{
    global $oToken, $db, $lang, $SETTINGS, $table_settings;
    
    $oToken->isValidToken();
    
    $bbrulestxtnew = addslashes(formVar('bbrulestxtnew'));
    $bboffreasonnew = addslashes(formVar('bboffreasonnew'));
    $tickercontentsnew = addslashes(formVar('$tickercontentsnew'));

    $max_avatar_size_w_new = formInt('max_avatar_size_w_new');
    $max_avatar_size_h_new = formInt('max_avatar_size_h_new');
    $pruneusersnew = formInt('pruneusersnew');

    $new_footer_options = formArray('new_footer_options');
    if (!empty($new_footer_options)) {
            $footer_options = implode('-', $new_footer_options);
    } else {
            $footer_options = '';
    }

    $maxDayReg = formInt('maxDayReg');

    $space_catsnew = formOnOff('space_catsnew');
    $allowrankeditnew = formOnOff('allowrankeditnew');
    $notifyonregnew = formVar('notifyonregnew');
    $notifyonregnew ==  ($notifyonregnew == 'off') ? 'off' : ($notifyonregnew == 'u2u' ? 'u2u' : 'email');
    
    $spellchecknew = formOnOff('spellchecknew');
    $indexShowBarNew = formInt('indexShowBarNew'); 
    $indexShowBarNew = (($indexShowBarNew > 3 || $indexShowBarNew < 1) ? 2 : (int) $indexShowBarNew);

    $subjectInTitleNew = formOnOff('subjectInTitleNew');
    $resetSigNew = formOnOff('esetSigNew');

    $langfilenew = formVar('langfilenew');
    $langfilenew = getLangFileNameFromHash($langfilenew);
    if(!$langfilenew) {
        $langfilenew = $SETTINGS['langfile'];
    } else {
        $langfilenew = basename($langfilenew);
    }

    $bbnamenew = formVar('bbnamenew');
    $postperpagenew = formVar('postperpagenew');
    $topicperpagenew = formVar('topicperpagenew');
    $hottopicnew = formVar('hottopicnew');
    $themenew = formVar('themenew');
    $bbstatusnew = formVar('bbstatusnew');
    $whos_on = formVar('whos_on');
    $reg_on = formVar('reg_on');
    $regviewnew = formVar('regviewnew');
    $floodctrlnew = formVar('floodctrlnew');
    $memberperpagenew = formVar('memberperpagenew');
    $catsonlynew = formVar('catsonlynew');
    $hidepriv = formVar('hidepriv');
    $emailchecknew = formVar('emailchecknew');
    $bbrulesnew = formVar('bbrulesnew');
    $searchstatusnew = formVar('searchstatusnew');
    
    $bbstatusnew = formVar('bbstatusnew');
    $faqstatusnew = formVar('faqstatusnew');
    $memliststatusnew = formVar('memliststatusnew');
    $sitenamenew = formVar('sitenamenew');
    $siteurlnew = formVar('siteurlnew');
    $avastatusnew = formVar('avastatusnew');
    $u2uquotanew = formVar('u2uquotanew');
    $gzipcompressnew = formVar('gzipcompressnew');
    $boardurlnew = formVar('boardurlnew');
    $coppanew = formVar('coppanew');
    
    $timeformatnew = formVar('timeformatnew');
    $adminemailnew = formVar('adminemailnew');
    $dateformatnew = formVar('dateformatnew');
    $sigbbcodenew = formVar('sigbbcodenew');
    $sightmlnew = formVar('sightmlnew');
    $reportpostnew = formVar('reportpostnew');
    $bbinsertnew = formVar('bbinsertnew');
    $smileyinsertnew = formVar('smileyinsertnew');
    $doubleenew = formVar('doubleenew');
    $smtotalnew = formVar('smtotalnew');
    
    $smcolsnew = formVar('smcolsnew');
    $editedbynew = formVar('editedbynew');
    $dotfoldersnew = formVar('dotfoldersnew');
    $attachimgpostnew = formVar('attachimgpostnew');
    $tickerstatusnew = formVar('tickerstatusnew');
    $tickerdelaynew = formVar('tickerdelaynew');
    $addtimenew = formVar('addtimenew');
    $todaystatusnew = formVar('todaystatusnew');
    $statsstatusnew = formVar('statsstatusnew');
    
    $def_tz_new = formVar('def_tz_new');
    $ipReg = formVar('ipReg');
    $maxAttachSize = formInt('maxAttachSize');

    $db->query("UPDATE $table_settings SET langfile='$langfilenew', bbname='$bbnamenew', postperpage='$postperpagenew', topicperpage='$topicperpagenew', hottopic='$hottopicnew', theme='$themenew', bbstatus='$bbstatusnew', whosonlinestatus='$whos_on', regstatus='$reg_on', pruneusers='$pruneusersnew', bboffreason='$bboffreasonnew', regviewonly='$regviewnew', floodctrl='$floodctrlnew', memberperpage='$memberperpagenew', catsonly='$catsonlynew', hideprivate='$hidepriv', emailcheck='$emailchecknew', bbrules='$bbrulesnew', bbrulestxt='$bbrulestxtnew', searchstatus='$searchstatusnew', faqstatus='$faqstatusnew', memliststatus='$memliststatusnew', sitename='$sitenamenew', siteurl='$siteurlnew', avastatus='$avastatusnew', u2uquota='$u2uquotanew', gzipcompress='$gzipcompressnew', boardurl='$boardurlnew', coppa='$coppanew', timeformat='$timeformatnew', adminemail='$adminemailnew', dateformat='$dateformatnew', sigbbcode='$sigbbcodenew', sightml='$sightmlnew', reportpost='$reportpostnew', bbinsert='$bbinsertnew', smileyinsert='$smileyinsertnew', doublee='$doubleenew', smtotal='$smtotalnew', smcols='$smcolsnew', editedby='$editedbynew', dotfolders='$dotfoldersnew', attachimgpost='$attachimgpostnew', tickerstatus='$tickerstatusnew', tickercontents='$tickercontentsnew', tickerdelay='$tickerdelaynew', addtime='$addtimenew', todaysposts='$todaystatusnew', stats='$statsstatusnew', max_avatar_size='${max_avatar_size_w_new}x${max_avatar_size_h_new}', footer_options='$footer_options', space_cats='$space_catsnew', spellcheck='$spellchecknew', allowrankedit='$allowrankeditnew', notifyonreg='$notifyonregnew', indexshowbar='$indexShowBarNew', subject_in_title='$subjectInTitleNew', def_tz='$def_tz_new', resetsigs='$resetSigNew', ipreg='$ipReg', maxdayreg=$maxDayReg, maxattachsize=$maxAttachSize");

    echo '<tr class="tablerow altbg2"><td align="center">'.$lang['textsettingsupdate'].'</td></tr>';
    message($lang['censorupdate'], false, '', '', 'cp2.php', false, false, false); // TODO
}

?>