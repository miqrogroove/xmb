<?php
/* $Id: cp.php,v 1.21.2.26 2004/09/24 19:10:30 Tularis Exp $ */
/*
    XMB 1.9
    © 2001 - 2004 Aventure Media & The XMB Development Team
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

require "./header.php";
require "./include/admin.user.inc.php";

loadtemplates('footer_load','footer_querynum','footer_phpsql','footer_totaltime','header','footer','css','error_nologinsession');

nav($lang['textcp']);

eval("\$css = \"".template("css")."\";");
eval("echo (\"".template('header')."\");");

if (!X_ADMIN) {
    eval('echo stripslashes("'.template('error_nologinsession').'");');
    end_time();
    eval("echo (\"".template('footer')."\");");
    exit();
}

$auditaction = $_SERVER['REQUEST_URI'];
$aapos = strpos($auditaction, "?");
if ($aapos !== false) {
    $auditaction = substr($auditaction, $aapos + 1);
}

$auditaction = addslashes("$onlineip|#|$auditaction");
audit($xmbuser, $auditaction, 0, 0);

function printsetting1($setname, $varname, $check1, $check2) {
    global $lang, $altbg1, $altbg2;

    ?>
    <tr><td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $setname?></td>
    <td class="tablerow" bgcolor="<?php echo $altbg2?>"><select name="<?php echo $varname?>">
    <option value="on" <?php echo $check1?>><?php echo $lang['texton']?></option><option value="off" <?php echo $check2?>><?php echo $lang['textoff']?></option>
    </select></td></tr>
    <?php
}

function printsetting2($setname, $varname, $value, $size) {
    global $altbg1, $altbg2;

    ?>
    <tr>
    <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $setname?></td>
    <td class="tablerow" bgcolor="<?php echo $altbg2?>"><input type="text"  size="<?php echo $size?>" value="<?php echo $value?>" name="<?php echo $varname?>" /></td>
    </tr>
    <?php
}

function printsetting3($setname, $boxname, $varnames, $values, $checked, $multi=true) {
    global $altbg1, $altbg2;

    foreach($varnames as $key=>$val){
        if(isset($checked[$key]) && $checked[$key] !== true){
            $optionlist[] = '<option value="'.$values[$key].'">'.$varnames[$key].'</option>';
        }else{
            $optionlist[] = '<option value="'.$values[$key].'" selected="selected">'.$varnames[$key].'</option>';
        }
    }

    $optionlist = implode("\n", $optionlist);
    ?>
    <tr>
    <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $setname?></td>
    <td class="tablerow" bgcolor="<?php echo $altbg2?>"><select <?php echo ($multi ? 'multiple="multiple"' : '')?> name="<?php echo $boxname?><?php echo ($multi ? '[]' : '')?>"><?php echo $optionlist?></select></td>
    </tr>
    <?php
}

?>

<!-- Admin Panel design kindly donated by John Briggs begin -->
<table cellspacing="0" cellpadding="0" border="0" width="<?php echo $tablewidth?>" align="center">
<tr>
<td bgcolor="<?php echo $bordercolor?>">
<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
<tr class="category">
<td colspan="30" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textcp']?></font></strong></td>
</tr>

<tr bgcolor="<?php echo $altbg1?>" class="tablerow">
<td colspan="30" align="center">
<br />
<table cellspacing="0" cellpadding="0" border="0" width="98%" align="center">
<tr>
<td bgcolor="<?php echo $bordercolor?>">
<table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">

<tr class="category">
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['general']?></font></strong></td>
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textforums']?></font></strong></td>
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textmembers']?></font></strong></td>
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['look_feel']?></font></strong></td>
</tr>

<tr>
<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="cp2.php?action=attachments"><?php echo $lang['textattachman']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=censor"><?php echo $lang['textcensors']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=newsletter"><?php echo $lang['textnewsletter']?></a><br />
&raquo;&nbsp;<a href="cp.php?action=search"><?php echo $lang['cpsearch']?></a><br />
&raquo;&nbsp;<a href="cp.php?action=settings"><?php echo $lang['textsettings']?></a><br />
</td>

<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="cp.php?action=forum"><?php echo $lang['textforums']?></a><br />
&raquo;&nbsp;<a href="cp.php?action=mods"><?php echo $lang['textmods']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=prune"><?php echo $lang['textprune']?></a><br />
</td>

<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="cp.php?action=ipban"><?php echo $lang['textipban']?></a><br />
&raquo;&nbsp;<a href="cp.php?action=members"><?php echo $lang['textmembers']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=ranks"><?php echo $lang['textuserranks']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=restrictions"><?php echo $lang['cprestricted']?></a><br />
&raquo;&nbsp;<a href="cp.php?action=rename"><?php echo $lang['admin_rename_txt']?></a><br />
</td>

<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="cp2.php?action=smilies"><?php echo $lang['smilies']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=templates"><?php echo $lang['templates']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=themes"><?php echo $lang['themes']?></a><br />
</td>
</tr>

<tr class="category">
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['logs']?></font></strong></td>
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['tools']?></font></strong></td>
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['mysql_tools']?></font></strong></td>
<td valign="top" width="20%" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['backup_tools']?></font></strong></td>
</tr>

<tr>
<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="cp2.php?action=modlog"><?php echo $lang['textmodlogs']?></a><br />
&raquo;&nbsp;<a href="cp2.php?action=cplog"><?php echo $lang['textcplogs']?></a>
</td>

<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="tools.php?action=fixftotals"><?php echo $lang['textfixposts']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=fixlastposts"><?php echo $lang['textfixlastposts']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=fixmposts"><?php echo $lang['textfixmemposts']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=fixttotals"><?php echo $lang['textfixthread']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=updatemoods"><?php echo $lang['textfixmoods']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=fixorphanedthreads"><?php echo $lang['textfixothreads']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=fixorphanedattachments"><?php echo $lang['textfixoattachments']?></a><br />
</td>

<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="tools.php?action=analyzetables"><?php echo $lang['analyze']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=whosonlinedump"><?php echo $lang['cpwodump']?></a><br />
&raquo;&nbsp;<a href="cp.php?action=upgrade"><?php echo $lang['raw_mysql']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=optimizetables"><?php echo $lang['optimize']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=repairtables"><?php echo $lang['repair']?></a><br />
&raquo;&nbsp;<a href="tools.php?action=u2udump"><?php echo $lang['u2udump']?></a><br />
</td>

<td class="tablerow" align="left" valign="top" width="20%" bgcolor="<?php echo $altbg2?>">
&raquo;&nbsp;<a href="cp2.php?action=dbdump"><?php echo $lang['db_backup']?></a><br />
&raquo;&nbsp;<a href="dump_attachments.php?action=dump_attachments"><?php echo $lang['dump_attachments']?></a><br />
&raquo;&nbsp;<a href="dump_attachments.php?action=restore_attachments"><?php echo $lang['restore_attachments']?></a><br />
</td>
</tr>
</table>
</td>
</tr>
</table>
<br />
<!-- Admin Panel design kindly donated by John Briggs end -->

<?php

if (!isset($action)) {
    $action = '';
}

$selHTML = 'selected="selected"';

if ($action == "settings") {
    if (!isset($settingsubmit)) {

        $langfileselect = "<select name=\"langfilenew\">\n";

        $dir = opendir("lang");
        while ($thafile = readdir($dir)) {
            if (is_file("lang/$thafile") && false !== strpos($thafile, '.lang.php')) {
                $thafile = str_replace('.lang.php', '', $thafile);
                if ($thafile == $SETTINGS['langfile']) {
                    $langfileselect .= "<option value=\"$thafile\" selected=\"selected\">$thafile</option>\n";
                } else {
                    $langfileselect .= "<option value=\"$thafile\">$thafile</option>\n";
                }
            }
        }

        $langfileselect .= "</select>";

        $themelist = "<select name=\"themenew\">\n";
        $query = $db->query("SELECT themeid, name FROM $table_themes ORDER BY name ASC");
        while ($themeinfo = $db->fetch_array($query)) {
            if ($themeinfo['themeid'] == $SETTINGS['theme']) {
                $themelist .= "<option value=\"$themeinfo[themeid]\" selected=\"selected\">$themeinfo[name]</option>\n";
            } else {
                $themelist .= "<option value=\"$themeinfo[themeid]\">$themeinfo[name]</option>\n";
            }
        }
        $themelist  .= "</select>";

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
        } elseif ($avastatus == "list") {
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

        $allowrankediton = $allowrankeditoff = '';
        if ($SETTINGS['allowrankedit'] == "on") {
            $allowrankediton = $selHTML;
        } else {
            $allowrankeditoff = $selHTML;
        }

        $spell_off_reason = '';
        if (!defined('PSPELL_FAST')){
            $spell_off_reason = $lang['pspell_needed'];
            $SETTINGS['spellcheck'] = 'off';
        }

        $spellcheckon = $spellcheckoff = '';
        if ($SETTINGS['spellcheck'] == "on") {
            $spellcheckon = $selHTML;
        } else {
            $spellcheckoff = $selHTML;
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

        $values = array('serverload', 'queries', 'phpsql', 'loadtimes');
        $names = array('Enable Server Load', 'Enable Queries', 'Enable PHP/SQL Calculation', 'Enable Page-loadtimes');
        $checked = array($sel_serverload, $sel_queries, $sel_phpsql, $sel_loadtimes);

        $SETTINGS['bboffreason'] = stripslashes($SETTINGS['bboffreason']);
        $SETTINGS['bbrulestxt'] = stripslashes($SETTINGS['bbrulestxt']);
        $SETTINGS['tickercontents'] = stripslashes($SETTINGS['tickercontents']);
        $max_avatar_sizes = explode('x', $SETTINGS['max_avatar_size']);
        $lang['spell_checker'] .= $spell_off_reason;
        ?>

        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp.php?action=settings">
        <table cellspacing="0" cellpadding="0" border="0" width="600" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings1']?></font></strong></td>
        </tr>

        <?php
        printsetting2($lang['textsitename'], "sitenamenew", $SETTINGS['sitename'], "50");
        printsetting2($lang['bbname'], "bbnamenew", $SETTINGS['bbname'], "50");
        printsetting2($lang['textsiteurl'], "siteurlnew", $SETTINGS['siteurl'], "50");
        printsetting2($lang['textboardurl'], "boardurlnew", $SETTINGS['boardurl'], "50");
        printsetting2($lang['adminemail'], "adminemailnew", $SETTINGS['adminemail'], "50");
        printsetting1($lang['textbbrules'], 'bbrulesnew', $ruleson, $rulesoff);
        ?>

        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang['textbbrulestxt']?></td>
        <td class="tablerow" bgcolor="<?php echo $altbg2?>"><textarea rows="5" name="bbrulestxtnew" cols="50"><?php echo $SETTINGS['bbrulestxt']?></textarea></td>
        </tr>

        <?php
        printsetting1($lang['textbstatus'], "bbstatusnew", $onselect, $offselect);
        ?>

        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang['textbboffreason']?></td>
        <td class="tablerow" bgcolor="<?php echo $altbg2?>"><textarea rows="5" name="bboffreasonnew" cols="50"><?php echo $SETTINGS['bboffreason']?></textarea></td>
        </tr>

        <?php
        printsetting1($lang['gzipcompression'], 'gzipcompressnew', $gzipcompresson, $gzipcompressoff);
        ?>

        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg2?>" colspan="2">&nbsp;</td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" colspan="2" class="category"><strong><font color="<?php echo $cattext?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings2']?></font></strong></td>
        </tr>
        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang['textlanguage']?></td>
        <td class="tablerow" bgcolor="<?php echo $altbg2?>"><?php echo $langfileselect?></td>
        </tr>
        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang['texttheme']?></td>
        <td class="tablerow" bgcolor="<?php echo $altbg2?>"><?php echo $themelist?></td>
        </tr>

        <?php
        printsetting2($lang['textppp'], "postperpagenew", $SETTINGS['postperpage'], 3);
        printsetting2($lang['texttpp'], "topicperpagenew", $SETTINGS['topicperpage'], 3);
        printsetting2($lang['textmpp'], "memberperpagenew", $SETTINGS['memberperpage'], 3);
        ?>

        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang['texttimeformat']?></td>
        <td class="tablerow" bgcolor="<?php echo $altbg2?>"><input type="radio" value="24" name="timeformatnew" <?php echo $check24?> />&nbsp;<?php echo $lang['text24hour']?>&nbsp;<input type="radio" value="12" name="timeformatnew" <?php echo $check12?> />&nbsp;<?php echo $lang['text12hour']?></td>
        </tr>

        <?php
        printsetting2($lang['dateformat'], "dateformatnew", $SETTINGS['dateformat'], "20");
        ?>

        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg2?>" colspan="2">&nbsp;</td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" colspan="2" class="category"><strong><font color="<?php echo $cattext?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings3']?></font></strong></td>
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
        <td class="tablerow" bgcolor="<?php echo $altbg2?>" colspan="2">&nbsp;</td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" colspan="2" class="category"><strong><font color="<?php echo $cattext?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings4']?></font></strong></td>
        </tr>

        <?php
        printsetting1($lang['space_cats'], 'space_catsnew',$spacecatson, $spacecatsoff);
        printsetting1($lang['allowrankedit'], 'allowrankeditnew', $allowrankediton, $allowrankeditoff);
        printsetting1($lang['textcatsonly'], 'catsonlynew', $catsonlyon, $catsonlyoff);
        printsetting1($lang['whosonline_on'], 'whos_on', $whosonlineon, $whosonlineoff);
        printsetting2($lang['smtotal'], "smtotalnew", $SETTINGS['smtotal'], 5);
        printsetting2($lang['smcols'], "smcolsnew", $SETTINGS['smcols'], 5);
        printsetting1($lang['dotfolders'], "dotfoldersnew", $dotfolderson, $dotfoldersoff);
        printsetting1($lang['editedby'], "editedbynew", $editedbyon, $editedbyoff);
        printsetting1($lang['attachimginpost'], "attachimgpostnew", $attachimgposton, $attachimgpostoff);
        ?>

        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg2?>" colspan="2">&nbsp;</td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" colspan="2" class="category"><strong><font color="<?php echo $cattext?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings5']?></font></strong></td>
        </tr>

        <?php
        printsetting1($lang['reg_on'], 'reg_on', $regon, $regoff);
        printsetting3($lang['notifyonreg'], 'notifyonregnew', array($lang['textoff'], $lang['viau2u'], $lang['viaemail']), array('off', 'u2u', 'email'), $notifycheck, false);
        printsetting1($lang['textreggedonly'], 'regviewnew', $regonlyon, $regonlyoff);
        printsetting1($lang['texthidepriv'], 'hidepriv', $hideon, $hideoff);
        printsetting1($lang['emailverify'], 'emailchecknew',$echeckon, $echeckoff);
        printsetting2($lang['textflood'], "floodctrlnew", $SETTINGS['floodctrl'], 3);
        printsetting2($lang['u2uquota'], "u2uquotanew", $SETTINGS['u2uquota'], 3);
        printsetting3($lang['textavastatus'], 'avastatusnew', array($lang['texton'], $lang['textlist'], $lang['textoff']), array('on', 'list', 'off'), $avchecked, false);
        printsetting1($lang['doublee'], 'doubleenew', $doubleeon, $doubleeoff);
        ?>

        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg2?>" colspan="2">&nbsp;</td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" colspan="2" class="category"><strong><font color="<?php echo $cattext?>">&raquo;&nbsp;<?php echo $lang['admin_main_settings6']?></font></strong></td>
        </tr>

        <?php
        printsetting2($lang['texthottopic'], "hottopicnew", $SETTINGS['hottopic'], 3);
        printsetting1($lang['bbinsert'], 'bbinsertnew', $bbinserton, $bbinsertoff);
        printsetting1($lang['smileyinsert'], 'smileyinsertnew', $smileyinserton, $smileyinsertoff);
        printsetting3($lang['footer_options'], 'new_footer_options', $names, $values, $checked);
        printsetting2($lang['addtime'], "addtimenew", $SETTINGS['addtime'], 3);
        printsetting1($lang['sigbbcode'], 'sigbbcodenew', $sigbbcodeon, $sigbbcodeoff);
        printsetting1($lang['sightml'], 'sightmlnew', $sightmlon, $sightmloff);
        printsetting2($lang['max_avatar_size_w'], "max_avatar_size_w_new", $max_avatar_sizes[0], 4);
        printsetting2($lang['max_avatar_size_h'], "max_avatar_size_h_new", $max_avatar_sizes[1], 4);
        printsetting1($lang['what_tickerstatus'], "tickerstatusnew", $tickerstatuson, $tickerstatusoff);
        printsetting2($lang['what_tickerdelay'], "tickerdelaynew", $SETTINGS['tickerdelay'], "5");
        ?>

        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang['tickercontents']?></td>
        <td class="tablerow" bgcolor="<?php echo $altbg2?>"><textarea rows="5" name="tickercontentsnew" cols="50"><?php echo $SETTINGS['tickercontents']?></textarea></td>
        </tr>
        <tr>
        <td align="center" class="tablerow" bgcolor="<?php echo $altbg2?>" colspan="2"><input class="submit" type="submit" name="settingsubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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
        $bbrulestxtnew = addslashes($bbrulestxtnew);
        $bboffreasonnew = addslashes($bboffreasonnew);
        $tickercontentsnew = addslashes($tickercontentsnew);

        $max_avatar_size_w_new = (int) $max_avatar_size_w_new;
        $max_avatar_size_h_new = (int) $max_avatar_size_h_new;

        if (!empty($new_footer_options)) {
                $footer_options = implode('-', $new_footer_options);
        } else {
                $footer_options = '';
        }

        $space_catsnew = ($space_catsnew == 'on') ? 'on' : 'off';
        $allowrankeditnew = ($allowrankeditnew == 'on') ? 'on' : 'off';
        $notifyonregnew = ($notifyonregnew == 'off') ? 'off' : ($notifyonregnew == 'u2u' ? 'u2u' : 'email');
        $spellchecknew = ($spellchecknew == 'on' && defined('PSPELL_FAST')) ? 'on' : 'off';

        $db->query("UPDATE $table_settings SET langfile='$langfilenew', bbname='$bbnamenew', postperpage='$postperpagenew', topicperpage='$topicperpagenew', hottopic='$hottopicnew', theme='$themenew', bbstatus='$bbstatusnew', whosonlinestatus='$whos_on', regstatus='$reg_on', bboffreason='$bboffreasonnew', regviewonly='$regviewnew', floodctrl='$floodctrlnew', memberperpage='$memberperpagenew', catsonly='$catsonlynew', hideprivate='$hidepriv', emailcheck='$emailchecknew', bbrules='$bbrulesnew', bbrulestxt='$bbrulestxtnew', searchstatus='$searchstatusnew', faqstatus='$faqstatusnew', memliststatus='$memliststatusnew', sitename='$sitenamenew', siteurl='$siteurlnew', avastatus='$avastatusnew', u2uquota='$u2uquotanew', gzipcompress='$gzipcompressnew', boardurl='$boardurlnew', coppa='$coppanew', timeformat='$timeformatnew', adminemail='$adminemailnew', dateformat='$dateformatnew', sigbbcode='$sigbbcodenew', sightml='$sightmlnew', reportpost='$reportpostnew', bbinsert='$bbinsertnew', smileyinsert='$smileyinsertnew', doublee='$doubleenew', smtotal='$smtotalnew', smcols='$smcolsnew', editedby='$editedbynew', dotfolders='$dotfoldersnew', attachimgpost='$attachimgpostnew', tickerstatus='$tickerstatusnew', tickercontents='$tickercontentsnew', tickerdelay='$tickerdelaynew', addtime='$addtimenew', todaysposts='$todaystatusnew', stats='$statsstatusnew', max_avatar_size='${max_avatar_size_w_new}x${max_avatar_size_h_new}', footer_options='$footer_options', space_cats='$space_catsnew', spellcheck='$spellchecknew', allowrankedit='$allowrankeditnew', notifyonreg='$notifyonregnew'");

        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textsettingsupdate]</td></tr>";
    }
}

if ($action == "rename") {
    // check to make sure admins don't rename super admins.
    if ($self['status'] != 'Super Administrator') {
        error($lang['superadminonly'], false, '</td></tr></table></td></tr></table><br />');
    }
    if (isset($renamesubmit) && isset($frmUserFrom) && isset($frmUserTo)) {
        // process input
        $vUserFrom = trim($frmUserFrom);
        $vUserTo = trim($frmUserTo);

        $adm = new admin();
        $myErr = $adm->rename_user($vUserFrom, $vUserTo);
        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$myErr</td></tr>";
    } else {
        // Display the rename user form
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td>
        <form action="cp.php?action=rename" method="post">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category" colspan="2"><strong><font color="<?php echo $cattext?>"><?php echo $lang['admin_rename_txt']?></font></strong></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" class="tablerow" width="22%"><?php echo $lang['admin_rename_userfrom']?></td>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow"><input type="text" name="frmUserFrom" size="25" /></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" class="tablerow" width="22%"><?php echo $lang['admin_rename_userto']?></td>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow"><input type="text" name="frmUserTo" size="25" /></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow" colspan="2" align="center"><input type="submit" class="submit" name="renamesubmit" value="<?php echo $lang['admin_rename_txt']?>" /></td>
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

if ($action == "forum") {
    if (!isset($forumsubmit) && !isset($fdetails)) {
        $groups = array();
        $forums = array();
        $forums['0'] = array();
        $forumlist = array();
        $subs = array();
        $i = 0;
        $query = $db->query("SELECT fid, type, name, displayorder, status, fup FROM $table_forums ORDER BY fup ASC, displayorder ASC");
        while ($selForums = $db->fetch_array($query)) {
            if ($selForums['type'] == 'group') {
                $groups[$i]['fid'] = $selForums['fid'];
                $groups[$i]['name'] = $selForums['name'];
                $groups[$i]['displayorder'] = $selForums['displayorder'];
                $groups[$i]['status'] = $selForums['status'];
                $groups[$i]['fup'] = $selForums['fup'];

            } elseif ($selForums['type'] == 'forum') {
                $id = (empty($selForums['fup'])) ? 0 : $selForums['fup'];
                $forums[$id][$i]['fid'] = $selForums['fid'];
                $forums[$id][$i]['name'] = $selForums['name'];
                $forums[$id][$i]['displayorder'] = $selForums['displayorder'];
                $forums[$id][$i]['status'] = $selForums['status'];
                $forums[$id][$i]['fup'] = $selForums['fup'];
                $forumlist[$i]['fid'] = $selForums['fid'];
                $forumlist[$i]['name'] = $selForums['name'];

            } elseif ($selForums['type'] == 'sub') {
                $subs["$selForums[fup]"][$i]['fid'] = $selForums['fid'];
                $subs["$selForums[fup]"][$i]['name'] = $selForums['name'];
                $subs["$selForums[fup]"][$i]['displayorder'] = $selForums['displayorder'];
                $subs["$selForums[fup]"][$i]['status'] = $selForums['status'];
                $subs["$selForums[fup]"][$i]['fup'] = $selForums['fup'];
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
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category"><font color="<?php echo $cattext?>"><strong><?php echo $lang['textforumopts']?></strong></font></td>
        </tr>

        <?php
        foreach ($forums['0'] as $forum) {

            $on = $off = '';
            if ($forum['status'] == "on") {
                $on = "selected=\"selected\"";
            } else {
                $off = "selected=\"selected\"";
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
            foreach ($groups as $moveforum) {
                echo "<option value=\"$moveforum[fid]\">".stripslashes($moveforum['name'])."</option>";
            }
            ?>

            </select>
            <a href="cp.php?action=forum&amp;fdetails=<?php echo $forum['fid']?>"><?php echo $lang['textmoreopts']?></a></td>
            </tr>

            <?php
        if (array_key_exists("$forum[fid]", $subs)) {
            foreach ($subs["$forum[fid]"] as $subforum) {
                $on = $off = '';
                if ($subforum['status'] == "on") {
                    $on = "selected=\"selected\"";
                } else {
                    $off = "selected=\"selected\"";
                }
                ?>

                <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
                <td class="smalltxt"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="checkbox" name="delete<?php echo $subforum[fid]?>" value="<?php echo $subforum[fid]?>" />
                &nbsp;<input type="text" name="name<?php echo $subforum[fid]?>" value="<?php echo stripslashes($subforum[name])?>" />
                &nbsp; <?php echo $lang['textorder']?> <input type="text" name="displayorder<?php echo $subforum[fid]?>" size="2" value="<?php echo $subforum[displayorder]?>" />
                &nbsp; <select name="status<?php echo $subforum[fid]?>">
                <option value="on" <?php echo $on?>><?php echo $lang['texton']?></option><option value="off" <?php echo $off?>><?php echo $lang['textoff']?></option></select>
                &nbsp; <select name="moveto<?php echo $subforum[fid]?>">

                <?php
                foreach ($forumlist as $moveforum) {
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

        foreach ($groups as $group) {
            $on = $off = '';
            if ($group['status'] == "on") {
                $on = "selected=\"selected\"";
            } else {
                $off = "selected=\"selected\"";
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
            foreach ($forums[$group['fid']] as $forum) {
                $on = $off = '';
                if ($forum['status'] == "on") {
                    $on = "selected=\"selected\"";
                } else {
                    $off = "selected=\"selected\"";
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
                foreach ($groups as $moveforum) {
                    if ($moveforum['fid'] == $forum['fup']) {
                        $curgroup = "selected=\"selected\"";
                    } else {
                        $curgroup = "";
                    }
                    echo "<option value=\"$moveforum[fid]\" $curgroup>".stripslashes($moveforum['name'])."</option>";
                }
                ?>
                </select>
                <a href="cp.php?action=forum&amp;fdetails=<?php echo $forum['fid']?>"><?php echo $lang['textmoreopts']?></a></td>
                </tr>

                <?php
            if (array_key_exists($forum['fid'], $subs)) {
                foreach ($subs["$forum[fid]"] as $forum) {
                    $on = $off = '';
                    if ($forum['status'] == "on") {
                        $on = "selected=\"selected\"";
                    } else {
                        $off = "selected=\"selected\"";
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
                    foreach ($forumlist as $moveforum) {
                        if ($moveforum['fid'] == $forum['fup']) {
                            echo '<option value="'.$moveforum['fid'].'" selected="selected">'.stripslashes($moveforum['name']).'</option>';
                        } else {
                            echo '<option value="'.$moveforum['fid'].'">'.stripslashes($moveforum['name']).'</option>';
                        }
                    }

                    ?>
                    </select>
                    <a href="cp.php?action=forum&amp;fdetails=<?php echo $forum[fid]?>"><?php echo $lang['textmoreopts']?></a></td>
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
        foreach ($groups as $group) {
            echo '<option value="'.$group['fid'].'">'.stripslashes($group['name']).'</option>';
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
        foreach ($forumlist as $group) {
            echo '<option value="'.$group['fid'].'">'.stripslashes($group['name']).'</option>';
        }
        ?>

        </select>
        </td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow" align="center"><input type="submit" name="forumsubmit" value="<?php echo $lang['textsubmitchanges']?>" class="submit" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        </td>
        </tr>

        <?php
    } elseif ($fdetails && !$forumsubmit) {
        ?>

        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp.php?action=forum&amp;fdetails=<?php echo $fdetails?>">
        <table cellspacing="0" cellpadding="0" border="0" width="100%" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category" colspan="2"><font color="<?php echo $cattext?>"><strong><?php echo $lang['textforumopts']?></strong></font></td>
        </tr>

        <?php
        $queryg = $db->query("SELECT * FROM $table_forums WHERE fid='$fdetails'");
        $forum = $db->fetch_array($queryg);

        $themelist = "<select name=\"themeforumnew\"><option value=\"\">$lang[textusedefault]</option>\n";
        $querytheme = $db->query("SELECT themeid, name FROM $table_themes ORDER BY name ASC");
        while ($theme = $db->fetch_array($querytheme)) {
            if ($theme['themeid'] == $forum['theme']) {
                $themelist .= "<option value=\"$theme[themeid]\" selected>$theme[name]\n";
            } else {
                $themelist .= "<option value=\"$theme[themeid]\">$theme[name]\n";
            }
        }
        $themelist  .= "</select>";

        if ($forum['private'] == "staff") {
            $checked1 = "checked";
        } else {
            $checked1 = "";
        }

        if ($forum['allowhtml'] == "yes" || $forum['allowhtml'] == 'on') {
            $checked2 = "checked";
        } else {
            $checked2 = "";
        }

        if ($forum['allowsmilies'] == "yes" || $forum['allowsmilies'] == "on") {
            $checked3 = "checked";
        } else {
            $checked3 = "";
        }

        if ($forum['allowbbcode'] == "yes" || $forum['allowbbcode'] == "on") {
            $checked4 = "checked";
        } else {
            $checked4 = "";
        }

        if ($forum['allowimgcode'] == "yes" || $forum['allowimgcode'] == "on") {
            $checked5 = "checked";
        } else {
            $checked5 = "";
        }

        if ($forum['attachstatus'] == "on" || $forum['attachstatus'] == "yes") {
            $checked6 = "checked";
        } else {
            $checked6 = "";
        }

        if ($forum['pollstatus'] == "on" || $forum['pollstatus'] == "yes") {
            $checked7 = "checked";
        } else {
            $checked7 = "";
        }

        if ($forum['guestposting'] == "on" || $forum['guestposting'] == "yes") {
            $checked8 = "checked";
        } else {
            $checked8 = "";
        }

        $pperm = explode("|", $forum['postperm']);

        $type11 = $type12 = $type13 = $type14 = '';

        if ($pperm['0'] == "2") {
            $type12 = "selected";
        } elseif ($pperm['0'] == "3") {
            $type13 = "selected";
        } elseif ($pperm['0'] == "4") {
            $type14 = "selected";
        } elseif ($pperm['0'] == "1") {
            $type11 = "selected";
        }

        $type21 = $type22 = $type23 = $type24 = '';
        if ($pperm['1'] == "2") {
            $type22 = "selected";
        } elseif ($pperm['1'] == "3") {
            $type23 = "selected";
        } elseif ($pperm['1'] == "4") {
            $type24 = "selected";
        } elseif ($pperm['1'] == "1") {
            $type21 = "selected";
        }

        $type31 = $type32 = $type33 = $type34 = '';
        if ($forum['private'] == "2") {
            $type32 = "selected";
        } elseif ($forum['private'] == "3") {
            $type33 = "selected";
        } elseif ($forum['private'] == "4") {
            $type34 = "selected";
        } elseif ($forum['private'] == "1") {
            $type31 = "selected";
        }

        $forum['name'] = stripslashes($forum['name']);
        $forum['description'] = stripslashes($forum['description']);
        ?>

        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['textforumname']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="namenew" value="<?php echo $forum['name']?>" /></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['textdesc']?></td>
        <td bgcolor="<?php echo $altbg2?>"><textarea rows="4" cols="30" name="descnew"><?php echo $forum['description']?></textarea></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>" valign="top"><?php echo $lang['textallow']?></td>
        <td bgcolor="<?php echo $altbg2?>" class="smalltxt"><input type="checkbox" name="allowhtmlnew" value="yes" <?php echo $checked2?> /><?php echo $lang['texthtml']?><br />
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
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="passwordnew" value="<?php echo $forum['password']?>" /></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['textdeleteques']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="checkbox" name="delete" value="<?php echo $forum['fid']?>" /></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow" align="center" colspan="2"><input type="submit" name="forumsubmit" value="<?php echo $lang['textsubmitchanges']?>" class="submit" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        </td>
        </tr>

        <?php
    } elseif ($forumsubmit) {
        if (!$fdetails) {
            $queryforum = $db->query("SELECT fid, type FROM $table_forums WHERE type='forum' OR type='sub'");
            $db->query("DELETE FROM $table_forums WHERE name=''");
            while ($forum = $db->fetch_array($queryforum)) {
                $displayorder = "displayorder$forum[fid]";
                $displayorder = "${$displayorder}";
                $name = "name$forum[fid]";
                $name = "${$name}";
                $self['status'] = "status$forum[fid]";
                $self['status'] = "${$self[status]}";
                $delete = "delete$forum[fid]";
                $delete = "${$delete}";
                $moveto = "moveto$forum[fid]";
                $moveto = "${$moveto}";

                if ($delete != "") {
                    $db->query("DELETE FROM $table_forums WHERE (type='forum' OR type='sub') AND fid='$delete'");

                    $querythread = $db->query("SELECT tid, author FROM $table_threads WHERE fid='$delete'");
                    while ($thread = $db->fetch_array($querythread)) {
                        $db->query("DELETE FROM $table_threads WHERE tid='$thread[tid]'");
                        $db->query("DELETE FROM $table_favorites WHERE tid='$thread[tid]'");
                        $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$thread[author]'");

                        $querypost = $db->query("SELECT pid, author FROM $table_posts WHERE tid='$thread[tid]'");
                        while ($post = $db->fetch_array($querypost)) {
                            $db->query("DELETE FROM $table_posts WHERE pid='$post[pid]'");
                            $db->query("UPDATE $table_members SET postnum=postnum-1 WHERE username='$post[author]'");
                        }
                        $db->free_result($querypost);
                    }
                    $db->free_result($querythread);
                }
                $name = addslashes($name);
                $db->query("UPDATE $table_forums SET name='$name', displayorder='$displayorder', status='$self[status]', fup='$moveto' WHERE fid='$forum[fid]'");
            }

            $querygroup = $db->query("SELECT fid FROM $table_forums WHERE type='group'");
            while ($group = $db->fetch_array($querygroup)) {
                $name = "name$group[fid]";
                $name = "${$name}";
                $displayorder = "displayorder$group[fid]";
                $displayorder = "${$displayorder}";
                $self['status'] = "status$group[fid]";
                $self['status'] = "${$self[status]}";
                $delete = "delete$group[fid]";
                $delete = "${$delete}";

                if ($delete != "") {
                    $query = $db->query("SELECT fid FROM $table_forums WHERE type='forum' AND fup='$delete'");
                    while ($forum = $db->fetch_array($query)) {
                        $db->query("UPDATE $table_forums SET fup='' WHERE type='forum' AND fup='$delete'");
                    }

                    $db->query("DELETE FROM $table_forums WHERE type='group' AND fid='$delete'");
                }
                $name = addslashes($name);
                $db->query("UPDATE $table_forums SET name='$name', displayorder='$displayorder', status='$self[status]' WHERE fid='$group[fid]'");
            }

            if ($newfname != $lang['textnewforum']) {
                $newfname = addslashes($newfname);
                $db->query("INSERT INTO $table_forums ( type, fid, name, status, lastpost, moderator, displayorder, private, description, allowhtml, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, pollstatus, password, guestposting ) VALUES ('forum', '', '$newfname', '$newfstatus', '', '', '$newforder', '1', '', 'no', 'yes', 'yes', '', '', '0', '0', '$newffup', '1|1', 'yes', 'on', 'on', '', 'off')");
            }

            if ($newgname != $lang['textnewgroup']) {
                $newgname = addslashes($newgname);
                $db->query("INSERT INTO $table_forums ( type, fid, name, status, lastpost, moderator, displayorder, private, description, allowhtml, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, pollstatus, password, guestposting ) VALUES ('group', '', '$newgname', '$newgstatus', '', '', '$newgorder', '', '', '', '', '', '', '', '0', '0', '', '', '', '', '', '', 'off')");
            }

            if ($newsubname != $lang['textnewsubf']) {
                $newsubname = addslashes($newsubname);
                $db->query("INSERT INTO $table_forums ( type, fid, name, status, lastpost, moderator, displayorder, private, description, allowhtml, allowsmilies, allowbbcode, userlist, theme, posts, threads, fup, postperm, allowimgcode, attachstatus, pollstatus, password, guestposting ) VALUES ('sub', '', '$newsubname', '$newsubstatus', '', '', '$newsuborder', '1', '', 'no', 'yes', 'yes', '', '', '0', '0', '$newsubfup', '1|1', 'yes', 'on', 'on', '', 'off')");
            }

            echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textforumupdate]</td></tr>";
        } else {
            $check_vars = array('allowhtmlnew', 'allowsmiliesnew', 'allowbbcodenew', 'allowimgcodenew', 'attachstatusnew', 'pollstatusnew', 'guestpostingnew');
            foreach ($check_vars as $key) {
                if ($$key != 'on' && $$key != 'yes') {
                    $$key = 'off';
                }
            }

            $namenew = addslashes($namenew);
            $descnew = addslashes($descnew);

            $db->query("UPDATE $table_forums SET name='$namenew', description='$descnew', allowhtml='$allowhtmlnew', allowsmilies='$allowsmiliesnew', allowbbcode='$allowbbcodenew', theme='$themeforumnew', userlist='$userlistnew', private='$privatenew', postperm='$postperm1|$postperm2', allowimgcode='$allowimgcodenew', attachstatus='$attachstatusnew', pollstatus='$pollstatusnew', password='$passwordnew', guestposting='$guestpostingnew' WHERE fid='$fdetails'");
            if ($delete != "") {
                $db->query("DELETE FROM $table_forums WHERE fid='$delete'");
            }

            echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textforumupdate]</td></tr>";
        }
    }
}

if ($action == "mods") {
    if (!isset($modsubmit)) {
        ?>

        <tr bgcolor="<?php echo $altbg2?>">
        <td>
        <form method="post" action="cp.php?action=mods">
        <table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textforum']?></font></strong></td>
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textmoderator']?></font></strong></td>
        </tr>

        <?php
        $oldfid = 0;
        $query = $db->query("SELECT f.moderator, f.name, f.fid, c.name as cat_name, c.fid as cat_fid FROM $table_forums f LEFT JOIN $table_forums c ON (f.fup = c.fid) WHERE (c.type='group' AND f.type='forum') OR (f.type='forum' AND f.fup='') ORDER BY c.displayorder, f.displayorder");
        while ($forum = $db->fetch_array($query)) {
            if ($oldfid != $forum['cat_fid']) {
                $oldfid = $forum['cat_fid']
                ?>
                <tr bgcolor="<?php echo $altbg1?>" class="tablerow">
                <td colspan="2"><strong><?php echo stripslashes($forum['cat_name'])?></strong></td>
                </tr>
                <?php
            }
            ?>

            <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
            <td><?php echo stripslashes($forum['name'])?></td>
            <td><input type="text" name="mod[<?php echo $forum['fid']?>]"" value="<?php echo $forum['moderator']?>" /></td>
            </tr>

            <?php
            $querys = $db->query("SELECT name, fid, moderator FROM $table_forums WHERE fup='$forum[fid]' AND type='sub'");
            while ($sub = $db->fetch_array($querys)) {
                ?>
                <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
                <td><?php echo $lang['4spaces']?><?php echo $lang['4spaces']?><em><?php echo stripslashes($sub['name'])?></em></td>
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
        <td align="center" colspan="2" class="tablerow" bgcolor="<?php echo $altbg2?>"><input type="submit" class="submit" name="modsubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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
        if (is_array($mod)) {
            foreach ($mod as $fid=>$mods) {
                $db->query("UPDATE $table_forums SET moderator='$mods' WHERE fid='$fid'");
            }
        }

        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textmodupdate]</td></tr>";
    }
}

if($action == "members") {
    if (!isset($membersubmit)) {
        ?>

        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">

        <?php
        if (!isset($members)) {
            ?>

            <tr bgcolor="<?php echo $altbg2?>">
            <td>
            <form method="post" action="cp.php?action=members&amp;members=search">
            <table cellspacing="0" cellpadding="0" border="0" width="90%" align="center">
            <tr>
            <td bgcolor="<?php echo $bordercolor?>">
            <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
            <tr>
            <td class="category" colspan="2"><font color="<?php echo $cattext?>"><strong><?php echo $lang['textmembers']?></strong></font></td>
            </tr>
            <tr>
            <td class="tablerow" bgcolor="<?php echo $altbg1?>" width="22%"><?php echo $lang['textsrchusr']?></td>
            <td class="tablerow" bgcolor="<?php echo $altbg2?>"><input type="text" name="srchmem" /></td>
            </tr>
            <tr>
            <td class="tablerow" bgcolor="<?php echo $altbg1?>" width="22%"><?php echo $lang['textwithstatus']?></td>
            <td class="tablerow" bgcolor="<?php echo $altbg2?>">
            <select name="srchstatus">
            <option value="0"><?php echo $lang['anystatus']?></option>
            <option value="Super Administrator"><?php echo $lang['superadmin']?></option>
            <option value="Administrator"><?php echo $lang['textadmin']?></option>
            <option value="Super Moderator"><?php echo $lang['textsupermod']?></option>
            <option value="Moderator"><?php echo $lang['textmod']?></option>
            <option value="Member"><?php echo $lang['textmem']?></option>
            <option value="Banned"><?php echo $lang['textbanned']?></option>
            </select>
            </td>
            </tr>
            <tr>
            <td bgcolor="<?php echo $altbg2?>" class="tablerow" align="center" colspan="2"><input type="submit" class="submit" value="<?php echo $lang['textgo']?>" /></td>
            </tr>
            </table>
            </td>
            </tr>
            </table>
            </form>
            </td>
            </tr>

            <?php
        } elseif ($members == "search") {
            ?>

            <form method="post" action="cp.php?action=members">
            <table cellspacing="0" cellpadding="0" border="0" width="91%" align="center">
            <tr>
            <td bgcolor="<?php echo $bordercolor?>">
            <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
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
            if ($srchstatus == "0") {
                $query = $db->query("SELECT * FROM $table_members WHERE username LIKE '%$srchmem%' ORDER BY username");
            } else {
                $query = $db->query("SELECT * FROM $table_members WHERE username LIKE '%$srchmem%' AND status='$srchstatus' ORDER BY username");
            }
            
            $sadminselect = "";
            $adminselect = "";
            $smodselect = "";
            $modselect = "";
            $memselect = "";
            $banselect = "";
            $noban = "";
            $u2uban = "";
            $postban = "";
            $bothban = "";

            while ($member = $db->fetch_array($query)) {
                switch ($member['status']) {
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

                switch ($member['ban']) {
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
                ?>

                <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
                <td align="center"><input type="checkbox" name="delete<?php echo $member['uid']?>" value="<?php echo $member['uid']?>" /></td>
                <td><a href="member.php?action=viewpro&amp;member=<?php echo $member['username']?>"><?php echo $member['username']?>
                <br /><a href="cp.php?action=deleteposts&amp;member=<?php echo $member['username']?>"><strong><?php echo $lang['cp_deleteposts']?></strong></a>
                <br /><a href="u2uadmin.php?uid=<?php echo $member['username']?>"><?php echo $lang['cp_viewinbox']?></a></td>
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
                <td><input type="text" size="16" name="cusstatus<?php echo $member['uid']?>" value="<?php echo stripslashes($member['customstatus'])?>" /></td>
                <td><select name="banstatus<?php echo $member['uid']?>">
                <option value="" <?php echo $noban?>><?php echo $lang['noban']?></option>
                <option value="u2u" <?php echo $u2uban?>><?php echo $lang['banu2u']?></option>
                <option value="posts" <?php echo $postban?>><?php echo $lang['banpost']?></option>
                <option value="both" <?php echo $bothban?>><?php echo $lang['banboth']?></option>
                </select></td>
                </tr>

                <?php
                $sadminselect = "";
                $adminselect = "";
                $smodselect = "";
                $modselect = "";
                $memselect = "";
                $banselect = "";
                $noban = "";
                $u2uban = "";
                $postban = "";
                $bothban = "";
            }
            ?>

            <tr>
            <td bgcolor="<?php echo $altbg2?>" class="tablerow" align="center" colspan="7"><input type="submit" class="submit" name="membersubmit" value="<?php echo $lang['textsubmitchanges']?>" /><input type="hidden" name="srchmem" value="<?php echo $srchmem?>" /><input type="hidden" name="srchstatus" value="<?php echo $srchstatus?>" /></td>
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
    } elseif ($membersubmit) {
        if ($srchstatus == "0") {
            $query = $db->query("SELECT uid, username, password, status FROM $table_members WHERE username LIKE '%$srchmem%'");
        } else {
            $query = $db->query("SELECT uid, username, password, status FROM $table_members WHERE username LIKE '%$srchmem%' AND status='$srchstatus'");
        }

        while ($mem = $db->fetch_array($query)) {
            $to['status'] = "status$mem[uid]";
            $to['status'] = "${$to['status']}";

            $origstatus = '';
            $origstatus = $mem['status'];

            $banstatus = "banstatus$mem[uid]";
            $banstatus = "${$banstatus}";

            $cusstatus = "cusstatus$mem[uid]";
            $cusstatus = "${$cusstatus}";

            $pw = "pw$mem[uid]";
            $pw = "${$pw}";

            $postnum = "postnum$mem[uid]";
            $postnum = "${$postnum}";

            $delete = "delete$mem[uid]";
            $delete = "${$delete}";

            if ($pw != "") {
                $newpw = md5($pw);
                $queryadd = " , password='$newpw'";
            } else {
                $newpw = $mem['password'];
                $queryadd = " , password='$newpw'";
            }

            if ($self['status'] != "Super Administrator" && ($origstatus == "Super Administrator" || $to['status'] == "Super Administrator")) {
                continue;
            }

            if ($delete != "") {
                $db->query("DELETE FROM $table_members WHERE uid='$delete'");
            } else {
                if (strpos($pw, '"') !== false || strpos($pw, "'") !== false) {
                    $lang['textmembersupdate'] = $mem['username'].': '.$lang['textpwincorrect'];
                } else {
                    $newcustom = addslashes($cusstatus);
                    $db->query("UPDATE $table_members SET ban='$banstatus', status='$to[status]', postnum='$postnum', customstatus='$newcustom'$queryadd WHERE uid='$mem[uid]'");
                    $newpw="";
                }
            }
        }

        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textmembersupdate]</td></tr>";
    }
}

if ($action == "ipban") {
    if (!isset($ipbansubmit)) {
        ?>

        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp.php?action=ipban">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr><td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td class="header"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textdeleteques']?></font></strong></td>
        <td class="header"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textip']?>:</font></strong></td>
        <td class="header"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textipresolve']?>:</font></strong></td>
        <td class="header"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textadded']?></font></strong></td>
        </tr>

        <?php
        $query = $db->query("SELECT * FROM $table_banned ORDER BY dateline");
        while ($ipaddress = $db->fetch_array($query)) {

            for ($i=1; $i<=4; ++$i) {
                $j = "ip" . $i;
                if($ipaddress[$j] == -1){
                    $ipaddress[$j] = "*";
                }
            }

            $ipdate = gmdate($dateformat, $ipaddress['dateline'] + ($timeoffset * 3600) + ($addtime * 3600)) . " $lang[textat] " . gmdate("$timecode", $ipaddress['dateline'] + ($timeoffset * 3600) + ($addtime * 3600));
            $theip = "$ipaddress[ip1].$ipaddress[ip2].$ipaddress[ip3].$ipaddress[ip4]";
            ?>

            <tr bgcolor="<?php echo $altbg1?>">
            <td class="tablerow"><input type="checkbox" name="delete<?php echo $ipaddress[id]?>" value="<?php echo $ipaddress[id]?>" /></td>
            <td class="tablerow"><?php echo $theip?></td>
            <td class="tablerow"><?php echo @gethostbyaddr($theip)?></td>
            <td class="tablerow"><?php echo $ipdate?></td>
            </tr>

            <?php
        }

        $query = $db->query("SELECT id FROM $table_banned WHERE (ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')");
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
        $queryip = $db->query("SELECT id FROM $table_banned");
        $newid = 1;
        while ($ip = $db->fetch_array($queryip)) {
            $delete = "delete$ip[id]";
            if (isset(${$delete}))
                $delete = "${$delete}";

            if ($delete != "") {
                $query = $db->query("DELETE FROM $table_banned WHERE id='$delete'");
            } elseif ($ip['id'] > $newid) {
                $query = $db->query("UPDATE $table_banned SET id='$newid' WHERE id='$ip[id]'");
            }
            ++$newid;
        }

        $self['status'] = $lang['textipupdate'];

        if ($newip1 != "" || $newip2 != "" || $newip3 != "" || $newip4 != "") {
            $invalid = 0;

            for ($i=1;$i<=4 && !$invalid;++$i) {
                $newip = "newip$i";
                $newip = "${$newip}";
                $newip = trim($newip);

                if ($newip == "*") {
                    $ip[$i] = -1;
                } elseif (ereg("^[0-9]+$", $newip)) {
                    $ip[$i] = $newip;
                } else {
                    $invalid = 1;
                }
            }

            if ($invalid) {
                $self['status'] = $lang[invalidip];
            } else {
                if ($ip[1] == '-1' && $ip[2] == '-1' && $ip[3] == '-1' && $ip[4] == '-1') {
                    $self['status'] = $lang['impossiblebanall'];
                } else {
                    $query = $db->query("SELECT id FROM $table_banned WHERE (ip1='$ip[1]' OR ip1='-1') AND (ip2='$ip[2]' OR ip2='-1') AND (ip3='$ip[3]' OR ip3='-1') AND (ip4='$ip[4]' OR ip4='-1')");
                    $result = $db->fetch_array($query);
                    if ($result) {
                        $self['status'] = $lang['existingip'];
                    } else {
                        $query = $db->query("INSERT INTO $table_banned (ip1, ip2, ip3, ip4, dateline, id) VALUES ('$ip[1]', '$ip[2]', '$ip[3]', '$ip[4]', '$onlinetime', '$newid')");
                    }
                }
            }
        }

        echo "<tr bgcolor=\"$altbg2\"><td align=\"center\" class=\"tablerow\">$self[status]</td></tr>";
    }
}

if ($action == "deleteposts") {
    $queryd = $db->query("DELETE FROM $table_posts WHERE author='$member'");
    $queryt = $db->query("SELECT * FROM $table_threads");
    while($threads = $db->fetch_array($queryt)) {
        $query = $db->query("SELECT COUNT(*) FROM $table_posts WHERE tid='$threads[tid]'");
        $replynum = $db->result($query, 0);
        $replynum--;
        $db->query("UPDATE $table_threads SET replies=replies-1 WHERE tid='$threads[tid]'");
        $db->query("DELETE FROM $table_threads WHERE author='$member'");
    }
}

if ($action == "upgrade") {
    if ($self['status'] != "Super Administrator") {
        error($lang['superadminonly'], false, '</td></tr></table></td></tr></table><br />');
    }

    if (isset($upgradesubmit)) {
        if (isset($_FILES['sql_file'])) {
            $add = get_attached_file($_FILES['sql_file'], 'on');
            if($add !== false) {
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

        echo "</table></td></tr></table>";

        for ($num=0;$num<$count;$num++) {
            $explode[$num] = stripslashes($explode[$num]);

            if ($allow_spec_q !== true) {
                if (strtoupper(substr(trim($explode[$num]), 0, 3)) == 'USE' || strtoupper(substr(trim($explode[$num]), 0, 14)) == 'SHOW DATABASES') {
                    error($lang['textillegalquery'], false, '</td></tr></table></td></tr></table><br />');
                }
            }

            if ($explode[$num] != "") {
                $query = $db->query($explode[$num], true);
            }

            echo '<br />';
            ?>

            <table cellspacing="0" cellpadding="0" border="0" width="<?php echo $tablewidth?>" align="center">
            <tr>
            <td bgcolor="<?php echo $bordercolor?>">
            <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
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
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['upgradesuccess']?></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>

        <?php
        end_time();
        eval("echo \"".template("footer")."\";");
        exit();
    } else {
        ?>

        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp.php?action=upgrade" enctype="multipart/form-data">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
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
    if (isset($searchsubmit)) {
        $found = 0;
        $list = array();
        if ($userip && !empty($userip)) {
            $query = $db->query("SELECT * FROM $table_members WHERE regip = '$userip'");
            while ($users = $db->fetch_array($query)) {
                $link = "./member.php?action=viewpro&amp;member=$users[username]";
                $list[] = "<a href = \"$link\">$users[username]<br />";
                $found++;
            }
        }

        if ($postip && !empty($postip)) {
            $query = $db->query("SELECT * FROM $table_posts WHERE useip = '$postip'");
            while ($users = $db->fetch_array($query)) {
                $link = "./viewthread.php?tid=$users[tid]#pid$users[pid]";
                if (!empty($users[subject])) {
                    $list[] = "<a href = \"$link\">$users[subject]<br />";
                } else {
                    $list[] = "<a href = \"$link\">- - No subject - -<br />";
                }
                $found++;
            }
        }

        if ($profileword && !empty($profileword)) {
            $query = $db->query("SELECT * FROM $table_members WHERE bio = '%$profileword%'");
            while ($users = $db->fetch_array($query)) {
                $link = "./member.php?action=viewpro&amp;member=$users[username]";
                $list[] = "<a href = \"$link\">$users[username]<br />";
                $found++;
            }
        }

        if ($postword && !empty($postword)) {
            $query = $db->query("SELECT * FROM $table_posts WHERE subject LIKE '%".$postword."%' OR message LIKE '%".$postword."%'");
            while ($users = $db->fetch_array($query)) {
                $link = "./viewthread.php?tid=$users[tid]#pid$users[pid]";
                if (!empty($users[subject])) {
                    $list[] = "<a href = \"$link\">$users[subject]<br />";
                } else {
                    $list[] = "<a href = \"$link\">- - No subject - -<br />";
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
        foreach ($list as $num=>$val) {
            ?>

            <tr class="tablerow" width="5%">
            <td align="left" bgcolor="<?php echo $altbg2?>">
            <strong><?php echo ($num+1)?>.</strong>
            </td>
            <td align="left" width="95%" bgcolor="<?php echo $altbg1?>">
            <?php echo $val?>
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
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
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
        $query = $db->query("SELECT find FROM $table_words");
        $select = "<select name=\"postword\"><option value=\"\"></option>";
        while ($temp = $db->fetch_array($query)) {
            $select .= "<option value=\"$temp[find]\">$temp[find]</option>";
        }
        $select .= "</select>";
        echo $select;
        ?>

        <br /><br />
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

echo "</table></td></tr></table>";

end_time();

eval("echo (\"".template('footer')."\");");
?>