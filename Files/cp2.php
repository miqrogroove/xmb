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
require_once('./include/admin.user.inc.php');

function readFileAsINI($filename) {
    $lines = file($filename);
    foreach ($lines as $line_num => $line) {
        $temp = explode("=",$line);
        if ( $temp[0] != 'dummy') {
            $key = trim($temp[0]);
            $val = trim($temp[1]);

            $thefile[$key] = $val;
        }
    }
    return $thefile;
}

loadtemplates('error_nologinsession');
eval("\$css = \"".template("css")."\";");

// Start Download Templates and Theme Code
if (X_ADMIN) {
    if ($action == "templates" && isset($download)) {
        $code = '';
        $templates  = $db->query("SELECT * FROM $table_templates");
        while ($template = $db->fetch_array($templates)) {
            $template['template']   = trim($template['template']);
            $template['name']       = trim($template['name']);

            if ($template['name'] != '') {
                $template['template'] = stripslashes($template['template']);

                $code.= $template['name'].'|#*XMB TEMPLATE*#|'."\r\n".$template['template']."\r\n\r\n".'|#*XMB TEMPLATE FILE*#|';
            }
        }
        header("Content-disposition: attachment; filename=templates.xmb");
        header("Content-Length: ".strlen($code));
        header("Content-type: unknown/unknown");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $code;
        exit();
    } elseif ($action == "themes" && isset($download)) {
        $contents = array();
        $query = $db->query("SELECT * FROM $table_themes WHERE themeid='$download'");
        $themebits = $db->fetch_array($query);
        foreach ($themebits as $key=>$val) {
            if (!is_integer($key) && $key != 'themeid' && $key != 'dummy') {
                $contents[] = $key.'='.$val;
            }
        }
        $name = str_replace(' ', '+', $themebits['name']);
        header("Content-Type: application/x-ms-download");
        header("Content-Disposition: filename=${name}-theme.xmb");
        echo implode("\r\n", $contents);
        exit();
    }
}
// End Download Templates and Theme Code

nav($lang['textcp']);

eval('echo "'.template('header').'";');
echo '<script language="JavaScript" type="text/javascript" src="./include/admin.js"></script>';

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

if ($action == 'restrictions') {
    if (!isset($restrictedsubmit)) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=restrictions">
        <table align="center" border="0" cellspacing="0" cellpadding="0" width="80%">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td><span class="smalltxt"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textdeleteques']?></font></strong></span></td>
        <td><span class="smalltxt"><strong><font color="<?php echo $cattext?>"><?php echo $lang['restrictedname']?></font></strong></span></td>
        <td><span class="smalltxt"><strong><font color="<?php echo $cattext?>">case-sensitive</font></strong></span></td>
        <td><span class="smalltxt"><strong><font color="<?php echo $cattext?>">partial-match</font></strong></span></td>
        </tr>
        <?php
        $query = $db->query("SELECT * FROM $table_restricted ORDER BY id");
        while ($restricted = $db->fetch_array($query)) {
            if ($restricted['case_sensitivity'] == 1) {
                $case_check = 'checked="checked"';
            } else {
                $case_check = '';
            }

            if ($restricted['partial'] == 1) {
                $partial_check = 'checked="checked"';
            } else {
                $partial_check = '';
            }
            ?>
            <tr class="tablerow">
            <td bgcolor="<?php echo $altbg2?>"><input type="checkbox" name="delete<?php echo $restricted['id']?>" value="<?php echo $restricted[id]?>" /></td>
            <td bgcolor="<?php echo $altbg2?>"><input type="text" size="30" name="name<?php echo $restricted['id']?>" value="<?php echo $restricted['name']?>" /></td>
            <td bgcolor="<?php echo $altbg2?>"><input type="checkbox" name="case<?php echo $restricted['id']?>" value="<?php echo $restricted[id]?>" <?php echo $case_check?> /></td>
            <td bgcolor="<?php echo $altbg2?>"><input type="checkbox" name="partial<?php echo $restricted['id']?>" value="<?php echo $restricted[id]?>" <?php echo $partial_check?> /></td>
            </tr>
            <?php
        }
        ?>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" colspan="4"><img src="./images/pixel.gif" alt="" /></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg2?>" colspan="4" align="left">
        <table border="0" width="100%">
        <tr class="category">
        <td colspan="2"><span class="smalltxt"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textnewcode']?></font></strong></span></td>
        </tr>
        <tr class="tablerow">
        <td colspan="2"><span class="smalltxt"><?php echo $lang['newrestriction']?></span></td>
        </tr>
        <tr>
        <td colspan="2"><span class="smalltxt"><?php echo $lang['newrestrictionwhy']?></span></td>
        </tr>
        <tr>
        <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
        <td><span class="smalltxt">name:</span></td>
        <td><input type="text" size="30" name="newname" /></td>
        </tr>
        <tr>
        <td><span class="smalltxt">case-sensitive:</span></td>
        <td><input type="checkbox" name="newcase" value="1" checked="unchecked" /></td>
        </tr>
        <tr>
        <td><span class="smalltxt">partial-match:</span></td>
        <td><input type="checkbox" name="newpartial" value="1" checked="checked" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </td>
        </tr>
        </table><br />
        <div align="center"><input class="submit" type="submit" name="restrictedsubmit" value="<?php echo $lang['textsubmitchanges']?>" /></div>
        </form>
        </td>
        </tr>
        <?php
    } else {
        $queryrestricted = $db->query("SELECT id FROM $table_restricted");
        while ($restricted = $db->fetch_array($queryrestricted)) {
            $name = isset($_POST['name'.$restricted['id']]) ? $_POST['name'.$restricted['id']] : '';
            $delete = isset($_POST['delete'.$restricted['id']]) ? $_POST['delete'.$restricted['id']] : '';
            $case = isset($_POST['case'.$restricted['id']]) ? intval($_POST['case'.$restricted['id']]) : 0;
            $partial = isset($_POST['partial'.$restricted['id']]) ? intval($_POST['partial'.$restricted['id']]) : 0;

            if ($partial > 0) {
                $partial = 1;
            }

            if ($case > 0) {
                $case = 1;
            }

            if ($delete > 0) {
                $db->query("DELETE FROM $table_restricted WHERE id='$delete'");
                continue;
            }
            $db->query("UPDATE `$table_restricted` SET `name`='$name', `case_sensitivity`='$case', `partial`='$partial' WHERE `id`='$restricted[id]'");
        }

        if ($newname != "") {
            if (!$newpartial || $newpartial != 1) {
                $newpartial = 0;
            } else {
                $newpartial = 1;
            }
            if (!$newcase || $newcase != 1) {
                $newcase = 0;
            } else {
                $newcase = 1;
            }
            $db->query("INSERT INTO $table_restricted (`name`, `id`, `case_sensitivity`, `partial`) VALUES ('$newname', '', '$newcase', '$newpartial')");
        }

        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['restrictedupdate'].'</td></tr>';

        redirect('cp2.php?action=restrictions', 2);
    }
} elseif ($action == 'themes') {
    if (!isset($themesubmit) && !isset($single) && !isset($importsubmit)) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td>
        <form method="POST" action="cp2.php?action=themes" name="theme_main">
        <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textdeleteques']?></font></strong></td>
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textthemename']?></font></strong></td>
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['numberusing']?></font></strong></td>
        </tr>
        <?php
        $themeMem = array(0=>0);
        $tq = $db->query("SELECT theme, count(theme) as cnt FROM $table_members GROUP BY theme");
        while($t = $db->fetch_array($tq)) {
            $themeMem[((int)$t['theme'])] = $t['cnt'];
        }
        $query = $db->query("SELECT name, themeid FROM $table_themes ORDER BY name ASC");
        while ($themeinfo = $db->fetch_array($query)) {
            $themeid = $themeinfo['themeid'];
            if (!isset($themeMem[$themeid])) {
                $themeMem[$themeid] = 0;
            }

            if ($themeinfo['themeid'] == $SETTINGS['theme']) {
                $members = ($themeMem[$themeid]+$themeMem[0]);
            } else{
                $members = $themeMem[$themeid];
            }

            if ($themeinfo['themeid'] == $theme) {
                $checked = 'checked="checked"';
            } else {
                $checked = 'checked="unchecked"';
            }
            ?>
            <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
            <td align="center"><input type="checkbox" name="theme_delete[]" value="<?php echo $themeinfo['themeid']?>" /></td>
            <td>
            <input type="text" name="theme_name[<?php echo $themeinfo['themeid']?>]" value="<?php echo $themeinfo['name']?>" />
            <a href="cp2.php?action=themes&amp;single=<?php echo $themeinfo['themeid']?>">
            <?php echo $lang['textdetails']?></a>
            -
            <a href="cp2.php?action=themes&amp;download=<?php echo $themeinfo['themeid']?>">
            <?php echo $lang['textdownload']?>
            </a>
            </td>
            <td><?php echo $members?></td>
            </tr>
            <?php
        }
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td colspan="3"><img src="./images/pixel.gif" alt="" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg1?>" class="tablerow">
        <td colspan="3">
        <a href="cp2.php?action=themes&amp;single=anewtheme1">
            <strong><?php echo $lang['textnewtheme']?></strong>
        </a>
         -
        <a href="#" onclick="setCheckboxes('theme_main', 'theme_delete[]', true); return false;">
            <?php echo $lang['checkall']?>
        </a>
         -
        <a href="#" onclick="setCheckboxes('theme_main', 'theme_delete[]', false); return false;">
            <?php echo $lang['uncheckall']?>
        </a>
         -
        <a href="#" onclick="invertSelection('theme_main', 'theme_delete[]'); return false;">
            <?php echo $lang['invertselection']?>
        </a>
        </td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow" colspan="3"><input type="submit" name="themesubmit" value="<?php echo $lang['textsubmitchanges']?>" class="submit" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        <br />
        <form method="post" action="cp2.php?action=themes" enctype="multipart/form-data">
        <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="header">
        <td colspan="2"><?php echo $lang['textimporttheme']?></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['textthemefile']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input name="themefile" type="file" /></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow" align="center" colspan="2"><input type="submit" class="submit" name="importsubmit" value="<?php echo $lang['textimportsubmit']?>" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        </td>
        </tr>
        <?php
    } elseif (isset($importsubmit) && isset($themefile['tmp_name'])) {
        $themebits = readFileAsINI($themefile['tmp_name']);
        $start = "INSERT INTO $table_themes";

        $keysql = array();
        $valsql = array();

        foreach ($themebits as $key=>$val) {
            if ($key == 'themeid') {
                $val = '';
            } elseif ($key == 'name') {
                $name = $val;
            }
            $keysql[] = $key;
            $valsql[] = "'$val'";
        }

        $keysql = implode(', ', $keysql);
        $valsql = implode(', ', $valsql);

        $query = $db->query("SELECT count(themeid) FROM $table_themes WHERE name='".addslashes($name)."'");
        if ($db->result($query, 0) > 0) {
            error($lang['theme_already_exists'], false, '</td></tr></table></td></tr></table>');
        }

        $sql = "INSERT INTO $table_themes ($keysql) VALUES ($valsql);";
        $query = $db->query($sql);

        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>';
        if (!$query) {
            echo $lang['textthemeimportfail'];
        } else {
            echo $lang['textthemeimportsuccess'];
        }
        echo '</td></tr>';
    } elseif (isset($themesubmit)) {
        $number_of_themes = $db->result($db->query("SELECT count(themeid) FROM $table_themes"), 0);

        if (isset($theme_delete) && count($theme_delete) >= $number_of_themes) {
            error($lang['delete_all_themes'], false, '</td></tr></table></td></tr></table>');
        }

        if (isset($theme_delete)) {
            foreach ($theme_delete as $themeid) {
                $otherid = $db->result($db->query("SELECT themeid FROM $table_themes WHERE themeid != '$themeid' ORDER BY rand() LIMIT 1"), 0);
                $db->query("UPDATE $table_members SET theme='$otherid' WHERE theme='$themeid'");
                $db->query("UPDATE $table_forums SET theme=0 WHERE theme='$themeid'");

                if ($SETTINGS['theme'] == $themeid) {
                    $db->query("UPDATE $table_settings SET theme='$otherid'");
                }

                $db->query("DELETE FROM $table_themes WHERE themeid='$themeid'");
            }
        }
        foreach ($theme_name as $themeid=>$name) {
            $db->query("UPDATE $table_themes SET name='$name' WHERE themeid='$themeid'");
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['themeupdate'].'</td></tr>';
    } elseif (isset($single) && $single != "submit" && $single != "anewtheme1") {
        $query = $db->query("SELECT * FROM $table_themes WHERE themeid='$single'");
        $themestuff = $db->fetch_array($query);
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td>
        <form method="post" action="cp2.php?action=themes&amp;single=submit">
        <table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['texthemename']?></td>
        <td colspan="2"><input type="text" name="namenew" value="<?php echo $themestuff['name']?>" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textbgcolor']?></td>
        <td><input type="text" name="bgcolornew" value="<?php echo $themestuff['bgcolor']?>" /></td>
        <td bgcolor="<?php echo $themestuff[bgcolor]?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textaltbg1']?></td>
        <td><input type="text" name="altbg1new" value="<?php echo $themestuff['altbg1']?>" /></td>
        <td bgcolor="<?php echo $themestuff[altbg1]?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textaltbg2']?></td>
        <td><input type="text" name="altbg2new" value="<?php echo $themestuff['altbg2']?>" /></td>
        <td bgcolor="<?php echo $themestuff[altbg2]?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textlink']?></td>
        <td><input type="text" name="linknew" value="<?php echo $themestuff['link']?>" /></td>
        <td bgcolor="<?php echo $themestuff[link]?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textborder']?></td>
        <td><input type="text" name="bordercolornew" value="<?php echo $themestuff['bordercolor']?>" /></td>
        <td bgcolor="<?php echo $themestuff['bordercolor']?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textheader']?></td>
        <td><input type="text" name="headernew" value="<?php echo $themestuff['header']?>" /></td>
        <td bgcolor="<?php echo $themestuff['header']?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textheadertext']?></td>
        <td><input type="text" name="headertextnew" value="<?php echo $themestuff['headertext']?>" /></td>
        <td bgcolor="<?php echo $themestuff['headertext']?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['texttop']?></td>
        <td><input type="text" name="topnew" value="<?php echo $themestuff['top']?>" /></td>
        <td bgcolor="<?php echo $themestuff['top']?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textcatcolor']?></td>
        <td><input type="text" name="catcolornew" value="<?php echo $themestuff['catcolor']?>" /></td>
        <td bgcolor="<?php echo $themestuff['catcolor']?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textcattextcolor']?></td>
        <td><input type="text" name="cattextnew" value="<?php echo $themestuff['cattext']?>" /></td>
        <td bgcolor="<?php echo $themestuff['cattext']?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['texttabletext']?></td>
        <td><input type="text" name="tabletextnew" value="<?php echo $themestuff['tabletext']?>" /></td>
        <td bgcolor="<?php echo $themestuff['tabletext']?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['texttext']?></td>
        <td><input type="text" name="textnew" value="<?php echo $themestuff['text']?>" /></td>
        <td bgcolor="<?php echo $themestuff['text']?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textborderwidth']?></td>
        <td colspan="2"><input type="text" name="borderwidthnew" value="<?php echo $themestuff['borderwidth']?>" size="2" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textwidth']?></td>
        <td colspan="2"><input type="text" name="tablewidthnew" value="<?php echo $themestuff['tablewidth']?>" size="3" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textspace']?></td>
        <td colspan="2"><input type="text" name="tablespacenew" value="<?php echo $themestuff['tablespace']?>" size="2" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textfont']?></td>
        <td colspan="2"><input type="text" name="fnew" value="<?php echo htmlspecialchars($themestuff['font'])?>" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textbigsize']?></td>
        <td colspan="2"><input type="text" name="fsizenew" value="<?php echo $themestuff['fontsize']?>" size="4" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textboardlogo']?></td>
        <td colspan="2"><input type="text"  value="<?php echo $themestuff['boardimg']?>" name="boardlogonew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['imgdir']?></td>
        <td colspan="2"><input type="text"  value="<?php echo $themestuff['imgdir']?>" name="imgdirnew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['smdir']?></td>
        <td colspan="2"><input type="text"  value="<?php echo $themestuff['smdir']?>" name="smdirnew" /></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow" colspan="3"><input type="submit" class="submit" value="<?php echo $lang['textsubmitchanges']?>" /><input type="hidden" name="orig" value="<?php echo $single?>" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        </td>
        </tr>
        <?php
    } elseif (isset($single) && $single == "anewtheme1") {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=themes&amp;single=submit">
        <table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['texthemename']?></td>
        <td><input type="text" name="namenew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textbgcolor']?></td>
        <td><input type="text" name="bgcolornew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textaltbg1']?></td>
        <td><input type="text" name="altbg1new" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textaltbg2']?></td>
        <td><input type="text" name="altbg2new" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textlink']?></td>
        <td><input type="text" name="linknew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textborder']?></td>
        <td><input type="text" name="bordercolornew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textheader']?></td>
        <td><input type="text" name="headernew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textheadertext']?></td>
        <td><input type="text" name="headertextnew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['texttop']?></td>
        <td><input type="text" name="topnew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textcatcolor']?></td>
        <td><input type="text" name="catcolornew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textcattextcolor']?></td>
        <td><input type="text" name="cattextnew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['texttabletext']?></td>
        <td><input type="text" name="tabletextnew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['texttext']?></td>
        <td><input type="text" name="textnew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textborderwidth']?></td>
        <td><input type="text" name="borderwidthnew" size="2" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textwidth']?></td>
        <td><input type="text" name="tablewidthnew" size="3" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textspace']?></td>
        <td><input type="text" name="tablespacenew" size="2" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textfont']?></td>
        <td><input type="text" name="fnew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textbigsize']?></td>
        <td><input type="text" name="fsizenew" size="4" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textboardlogo']?></td>
        <td><input type="text" name="boardlogonew" value="<?php echo $boardimg?>" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['imgdir']?></td>
        <td><input type="text" name="imgdirnew" value="images" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['smdir']?></td>
        <td><input type="text" name="smdirnew" value="images" /></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow" colspan="2"><input class="submit" type="submit" value="<?php echo $lang['textsubmitchanges']?>" /><input type="hidden" name="newtheme" value="<?php echo $single?>" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        </td>
        </tr>
        <?php
    } elseif (isset($single) && $single == "submit" && !$newtheme) {
        $db->query("UPDATE $table_themes SET name='$namenew', bgcolor='$bgcolornew', altbg1='$altbg1new', altbg2='$altbg2new', link='$linknew', bordercolor='$bordercolornew', header='$headernew', headertext='$headertextnew', top='$topnew', catcolor='$catcolornew', tabletext='$tabletextnew', text='$textnew', borderwidth='$borderwidthnew', tablewidth='$tablewidthnew', tablespace='$tablespacenew', fontsize='$fsizenew', font='$fnew', boardimg='$boardlogonew', imgdir='$imgdirnew', smdir='$smdirnew', cattext='$cattextnew' WHERE themeid='$orig'");
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['themeupdate'].'</td></tr>';
    } elseif (isset($single) && $single == "submit" && $newtheme) {
        $db->query("INSERT INTO $table_themes (themeid, name, bgcolor, altbg1, altbg2, link, bordercolor, header, headertext, top, catcolor, tabletext, text, borderwidth, tablewidth, tablespace, font, fontsize, boardimg, imgdir, smdir, cattext) VALUES('', '$namenew', '$bgcolornew', '$altbg1new', '$altbg2new', '$linknew', '$bordercolornew', '$headernew', '$headertextnew', '$topnew', '$catcolornew', '$tabletextnew', '$textnew', '$borderwidthnew', '$tablewidthnew', '$tablespacenew', '$fnew', '$fsizenew', '$boardlogonew', '$imgdirnew', '$smdirnew', '$cattextnew')");
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['themeupdate'].'</td></tr>';
    }
} elseif ($action == "smilies") {
    if (!isset($smiliesubmit)) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=smilies">
        <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category" colspan="4" align="left"><font color="<?php echo $cattext?>"><strong><?php echo $lang['smilies']?></strong></font></td>
        </tr>
        <tr class="header">
        <td align="center"><?php echo $lang['textdeleteques']?></td>
        <td><?php echo $lang['textsmiliecode']?></td>
        <td><?php echo $lang['textsmiliefile']?></td>
        <td align="center"><?php echo $lang['smilies']?></td>
        </tr>
        <?php
        $query = $db->query("SELECT code, id, url FROM $table_smilies WHERE type='smiley'");
        while ($smilie = $db->fetch_array($query)) {
            ?>
            <tr>
            <td bgcolor="<?php echo $altbg2?>" align="center" class="tablerow"><input type="checkbox" name="smdelete[<?php echo $smilie['id']?>]" value="1" /></td>
            <td bgcolor="<?php echo $altbg2?>" class="tablerow"><input type="text" name="smcode[<?php echo $smilie['id']?>]" value="<?php echo $smilie['code']?>" /></td>
            <td bgcolor="<?php echo $altbg2?>" class="tablerow"><input type="text" name="smurl[<?php echo $smilie['id']?>]" value="<?php echo $smilie['url']?>" /></td>
            <td bgcolor="<?php echo $altbg2?>" align="center" class="tablerow"><img src="<?php echo $smdir?>/<?php echo $smilie['url']?>" alt="<?php echo $smilie['code']?>" /></td>
            </tr>
            <?php
        }
        $db->free_result($query);
        ?>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" colspan="4"><img src="./images/pixel.gif" alt="" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg1?>" class="tablerow">
        <td><?php echo $lang['textnewsmilie']?></td>
        <td><input type="text" name="newcode" /></td>
        <td colspan="2"><input type="text" name="newurl1" /></td>
        </tr>
        <!-- Begin Auto Smiley Insert v1.0 Mod By Adam Clarke & John Briggs -->
        <tr class="ctrtablerow">
        <td bgcolor="<?php echo $altbg1?>"><input type="checkbox" name="autoinsertsmilies" value="1" /></td>
        <td bgcolor="<?php echo $altbg1?>" colspan="3"><?php echo $lang['autoinsertsmilies']?> (<?php echo $smdir?>)?</td>
        </tr>
        <!-- End Auto Smiley Insert v1.0 Mod By Adam Clarke & John Briggs -->
        <tr>
        <td bgcolor="<?php echo $altbg2?>" colspan="4" align="left"><img src="./images/pixel.gif" alt="" /></td>
        </tr>
        <tr>
        <td colspan="4" class="header"><?php echo $lang['picons']?></td>
        </tr>
        <tr class="header">
        <td align="center"><?php echo $lang['textdeleteques']?></td>
        <td colspan="2" align="left"><?php echo $lang['textsmiliefile']?></td>
        <td align="center"><?php echo $lang['picons']?></td>
        </tr>
        <?php
        $query = $db->query("SELECT * FROM $table_smilies WHERE type='picon' ORDER BY id");
        while ($smilie = $db->fetch_array($query)) {
            ?>
            <tr>
            <td bgcolor="<?php echo $altbg2?>" align="center" class="tablerow"><input type="checkbox" name="pidelete[<?php echo $smilie['id']?>]" value="1" /></td>
            <td colspan="2" align="left" bgcolor="<?php echo $altbg2?>" class="tablerow"><input type="text" name="piurl[<?php echo $smilie['id']?>]" value="<?php echo $smilie['url']?>" /></td>
            <td bgcolor="<?php echo $altbg2?>" align="center" class="tablerow"><img src="<?php echo $smdir?>/<?php echo $smilie['url']?>" alt="<?php echo $smilie['url']?>" /></td>
            </tr>
            <?php
        }
        $db->free_result($query);
        ?>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" colspan="4"><img src="./images/pixel.gif" alt="" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg1?>" class="tablerow">
        <td colspan="4" align="left"><?php echo $lang['textnewpicon']?>&nbsp;&nbsp;<input type="text" name="newurl2" /></td>
        </tr>
        <!-- Begin Auto Smiley Insert v1.0 Mod By Adam Clarke & John Briggs -->
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>" align="center"><input type="checkbox" name="autoinsertposticons" value="1" /></td>
        <td bgcolor="<?php echo $altbg1?>" colspan="3"><?php echo $lang['autoinsertposticons']?> (<?php echo $smdir?>)?</td>
        </tr>
        <!-- End Auto Smiley Insert v1.0 Mod By Adam Clarke & John Briggs -->
        <tr>
        <td class="ctrtablerow" bgcolor="<?php echo $altbg2?>" colspan="4"><input type="submit" class="submit" name="smiliesubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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
        if (is_array($smcode)) {
            foreach ($smcode as $key=>$val) {
                if (count(array_keys($smcode, $val)) > 1) {
                    error($lang['smilieexists'], false, '</td></tr></table></td></tr></table><br />');
                }
            }
        }

        $querysmilie = $db->query("SELECT id FROM $table_smilies WHERE type='smiley'");
        while ($smilie = $db->fetch_array($querysmilie)) {
            $id = $smilie['id'];
            if (isset($smdelete[$id]) && $smdelete[$id] == 1) {
                $query = $db->query("DELETE FROM $table_smilies WHERE id='$id'");
                continue;
            }
            $query = $db->query("UPDATE $table_smilies SET code='$smcode[$id]', url='$smurl[$id]' WHERE id='$smilie[id]' AND type='smiley'");
        }

        if (is_array($piurl)) foreach ($piurl as $key=>$val) {
            if (count(array_keys($piurl, $val)) > 1) {
                error($lang['piconexists'], false, '</td></tr></table></td></tr></table><br />');
            }
        }

        $querysmilie = $db->query("SELECT id FROM $table_smilies WHERE type='picon'");
        while ($picon = $db->fetch_array($querysmilie)) {
            $id = $picon['id'];
            if (isset($pidelete[$id]) && $pidelete[$id] == 1) {
                $query = $db->query("DELETE FROM $table_smilies WHERE id='$picon[id]'");
                continue;
            }
            $query = $db->query("UPDATE $table_smilies SET url='$piurl[$id]' WHERE id='$picon[id]' AND type='picon'");
        }

        if (isset($newcode) && $newcode != "") {
            // make sure we don't already have one like that
            if ($db->result($db->query("SELECT count(id) FROM $table_smilies WHERE code='$newcode'"), 0) > 0) {
                error($lang['smilieexists'], false, '</td></tr></table></td></tr></table><br />');
            }
            $query = $db->query("INSERT INTO $table_smilies ( type, code, url, id ) VALUES ('smiley', '$newcode', '$newurl1', '')");
        }

        // Begin Auto Smiley Insert v1.0 Mod By Adam Clarke & John Briggs
        if (isset($autoinsertsmilies) && $autoinsertsmilies == 1) {
            $smilies_count = $newsmilies_count = 0;
            // Load all existing smilies to ensure we don't insert a duplicate.
            $smiley_url = array();
            $smiley_code = array();
            $query = $db->query("SELECT * FROM $table_smilies WHERE type = 'smiley'");
            while ($smiley = $db->fetch_array($query)) {
                $smiley_url[] = $smiley['url'];
                $smiley_code[] = $smiley['code'];
            }
            $db->free_result($query);

            $dir = opendir($smdir);
            while ($smiley = readdir($dir)) {
                if ($smiley != '.' && $smiley != '..' && (strpos($smiley, '.gif') || strpos($smiley, '.jpg') || strpos($smiley, '.jpeg') || strpos($smiley, '.bmp') || strpos($smiley, '.png'))) {
                    $newsmiley_url = $smiley;
                    $newsmiley_code = $smiley;
                    $newsmiley_code = str_replace(array('.gif','.jpg','.jpeg','.bmp','.png','_'), array('','','','','',' '), $newsmiley_code);
                    $newsmiley_code = ':' . $newsmiley_code . ':';
                    if (!in_array($newsmiley_url, $smiley_url) && !in_array($newsmiley_code, $smiley_code)) {
                        $query = $db->query("INSERT INTO $table_smilies (type, code, url, id) VALUES ('smiley', '$newsmiley_code', '$newsmiley_url', '')");
                        $newsmilies_count++;
                    }
                    $smilies_count++;
                }
            }
            closedir($dir);
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$newsmilies_count.' / '.$smilies_count.' '.$lang['smiliesadded'].'</td></tr>';
        }
        // End Auto Smiley Insert v1.0 Mod By Adam Clarke & John Briggs

        if (isset($newurl2) && $newurl2 != "") {
            if ($db->result($db->query("SELECT count(id) FROM $table_smilies WHERE url='$newurl2' AND type='picon'"), 0) > 0) {
                error($lang['piconexists'], false, '</td></tr></table></td></tr></table><br />');
            }
            $query = $db->query("INSERT INTO $table_smilies ( type, code, url, id ) VALUES ('picon', '', '$newurl2', '')");
        }

        // Begin Auto Smiley Insert v1.0 Mod By Adam Clarke & John Briggs
        if (isset($autoinsertposticons) && $autoinsertposticons == 1) {
            $posticons_count = $newposticons_count = 0;
            // Load all existing post icons to ensure we don't insert a duplicate.
            $posticon_url = array();
            $query = $db->query("SELECT * FROM $table_smilies WHERE type='picon'");
            while ($picon = $db->fetch_array($query)) {
                $posticon_url[] = $picon['url'];
            }
            $db->free_result($query);

            $dir = opendir($smdir);
            while ($picon = readdir($dir)) {
                if ($picon != '.' && $picon != '..' && (strpos($picon, '.gif') || strpos($picon, '.jpg') || strpos($picon, '.jpeg') || strpos($picon, '.bmp') || strpos($picon, '.png'))) {
                    $newposticon_url = $picon;
                    $newposticon_url = str_replace(' ', '%20', $newposticon_url);
                    if (!in_array($newposticon_url, $posticon_url)) {
                        $query = $db->query("INSERT INTO $table_smilies (type, code, url, id) VALUES ('picon', '', '$newposticon_url', '')");
                        $newposticons_count++;
                    }
                    $posticons_count++;
                }
            }
            closedir($dir);
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$newposticons_count.' / '.$posticons_count.' '.$lang['posticonsadded'].'</td></tr>';
        }
        // End Auto Smiley Insert v1.0 Mod By Adam Clarke & John Briggs

        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['smilieupdate'].'</td></tr>';
    }
}

elseif ($action == "censor") {
    if (!isset($censorsubmit)) {
        ?>

        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=censor">
        <table cellspacing="0" cellpadding="0" border="0" width="450" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td width="4%" align="center"><font color="<?php echo $cattext?>"><strong><?php echo $lang['textdeleteques']?></strong></font></td>
        <td align="left"><font color="<?php echo $cattext?>"><strong><?php echo $lang['textcensorfind']?></strong></font></td>
        <td align="left"><font color="<?php echo $cattext?>"><strong><?php echo $lang['textcensorreplace']?></strong></font></td>
        </tr>

        <?php
        $query = $db->query("SELECT * FROM $table_words ORDER BY id");
        while ($censor = $db->fetch_array($query)) {
        ?>

        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td align="center"><input type="checkbox" name="delete<?php echo $censor['id']?>" value="<?php echo $censor['id']?>" /></td>
        <td align="left"><input type="text" size="20" name="find<?php echo $censor['id']?>" value="<?php echo $censor['find']?>" /></td>
        <td align="left"><input type="text" size="20" name="replace<?php echo $censor['id']?>" value="<?php echo $censor['replace1']?>" /></td>
        </tr>

        <?php
    }
    ?>

    <tr bgcolor="<?php echo $altbg2?>">
    <td colspan="3"><img src="./images/pixel.gif" alt="" /></td>
    </tr>
    <tr bgcolor="<?php echo $altbg1?>" class="tablerow">
    <td align="center"><strong><?php echo $lang['textnewcode']?></strong></td>
    <td align="left"><input type="text" size="20" name="newfind" /></td>
    <td align="left"><input type="text" size="20" name="newreplace" /></td>
    </tr>
    <tr>
    <td align="center" colspan="3" class="tablerow" bgcolor="<?php echo $altbg2?>"><input type="submit" class="submit" name="censorsubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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

if (isset($censorsubmit)) {
        $querycensor = $db->query("SELECT id FROM $table_words");

        while ($censor = $db->fetch_array($querycensor)) {
            $find = "find" . $censor['id'];
            $find = isset($_POST[$find]) ? $_POST[$find] : '';
            $replace = "replace" . $censor['id'];
            $replace = isset($_POST[$replace]) ? $_POST[$replace] : '';
            $delete = "delete" .$censor['id'];
            $delete = isset($_POST[$delete]) ? intval($_POST[$delete]) : 0;

            if ($delete > 0) {
                $db->query("DELETE FROM $table_words WHERE id='$delete'");
            }

            if (!empty($find)) {
                $db->query("UPDATE $table_words SET find='$find', replace1='$replace' WHERE id='$censor[id]'");
            }
        }

        $db->free_result($querycensor);

        if (isset($newfind) && $newfind != "") {
            $db->query("INSERT INTO $table_words ( find, replace1 ) VALUES ('$newfind', '$newreplace')");
        }

        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[censorupdate]</td></tr>";
    }

} elseif ($action == "ranks") {
    if (!isset($rankssubmit)) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=ranks">
        <table cellspacing="0" cellpadding="0" border="0" width="650" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textdeleteques']?></font></strong></td>
        <td class="category" align="left"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textcusstatus']?></font></strong></td>
        <td class="category"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textposts']?></font></strong></td>
        <td class="category"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textstars']?></font></strong></td>
        <td class="category"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textallowavatars']?></font></strong></td>
        <td class="category"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textavatar']?></font></strong></td>
        </tr>
        <?php
        $avatarno = $avataryes = '';
        $query = $db->query("SELECT * FROM $table_ranks ORDER BY stars");
        while ($rank = $db->fetch_array($query)) {
            if ($rank['title'] == 'Super Administrator' || $rank['title'] == 'Administrator' || $rank['title'] == 'Super Moderator' || $rank['title'] == 'Moderator') {
                $staff_disable = 'disabled';
            } else {
                $staff_disable = '';
            }

            if ($rank['allowavatars'] == 'yes') {
                $avataryes = "selected=\"selected\"";
            } else {
                $avatarno = "selected=\"selected\"";
            }
            ?>
            <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
            <td class="tablerow" align="center"><input type="checkbox" name="delete[<?php echo $rank['id']?>]" value="1" <?php echo $staff_disable?> /></td>
            <td class="tablerow" align="left"><input type="text" name="title[<?php echo $rank['id']?>]" value="<?php echo $rank['title']?>" <?php echo $staff_disable?>/></td>
            <td class="tablerow"><input type="text" name="posts[<?php echo $rank['id']?>]" value="<?php echo $rank['posts']?>" <?php echo $staff_disable?> size="5" /></td>
            <td class="tablerow"><input type="text" name="stars[<?php echo $rank['id']?>]" value="<?php echo $rank['stars']?>" size="4" /></td>
            <td class="tablerow"><select name="allowavatars[<?php echo $rank['id']?>]">
            <option value="yes" <?php echo $avataryes?>><?php echo $lang['texton']?></option>
            <option value="no" <?php echo $avatarno?>><?php echo $lang['textoff']?></option>
            </select><input type="hidden" name="id[<?php echo $rank['id']?>]" value="<?php echo $rank['id']?>" /></td>
            <td class="tablerow"><input type="text" name="avaurl[<?php echo $rank['id']?>]" value="<?php echo $rank['avatarrank']?>" size="20" /></td>
            </tr>
            <?php
            $avataryes = $avatarno = '';
        }
        ?>
        <tr bgcolor="<?php echo $altbg2?>"><td colspan="6"> </td></tr>
        <tr bgcolor="<?php echo $altbg1?>" class="tablerow">
        <td colspan="2"><?php echo $lang['textnewrank']?>&nbsp;&nbsp;<input type="text" name="newtitle" /></td>
        <td class="tablerow"><input type="text" name="newposts" size="5" /></td>
        <td class="tablerow"><input type="text" name="newstars" size="4" /></td>
        <td class="tablerow"><select name="newallowavatars"><option value="yes"><?php echo $lang['texton']?></option>
        <option value="no"><?php echo $lang['textoff']?></option></select></td>
        <td class="tablerow"><input type="text" name="newavaurl" size="20" /></td>
        </tr>
        <tr>
        <td align="center" colspan="6" class="tablerow" bgcolor="<?php echo $altbg2?>"><input type="submit" name="rankssubmit" class="submit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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
        $query = $db->query("SELECT * FROM $table_ranks");
        $staffranks = array();
        while ($ranks = $db->fetch_array($query)) {
            if ($ranks['title'] == 'Super Administrator' || $ranks['title'] == 'Administrator' || $ranks['title'] == 'Super Moderator' || $ranks['title'] == 'Moderator') {
                $title[$ranks['id']] = $ranks['title'];
                $posts[$ranks['id']] = 0;
                if ((int) $stars[$ranks['id']] == 0) {
                    $stars[$ranks['id']] = 1;
                }
            }
        }

        $i=0;
        foreach ($id as $key=>$val) {
            $delete[$key] = isset($delete[$key]) ? $delete[$key] : '';
            if ($delete[$key] == 1) {
                $db->query("DELETE FROM $table_ranks WHERE id='$key'");
                continue;
            }

            $posts[$key] = (in_array($title[$key], $staffranks)) ? (int) -1 : $posts[$key];
            $db->query("UPDATE $table_ranks SET title='$title[$key]', posts='$posts[$key]', stars='$stars[$key]', allowavatars='$allowavatars[$key]', avatarrank='$avaurl[$key]' WHERE id='$key'");
        }

        if (isset($newtitle) && $newtitle != '') {
            $db->query("INSERT INTO $table_ranks (title, posts, id, stars, allowavatars, avatarrank) VALUES ('$newtitle', '$newposts', '', '$newstars', '$newallowavatars', '$newavaurl')");
        }

        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['rankingsupdate'].'</td></tr>';
    }
} elseif ($action == "newsletter") {
    if (!isset($newslettersubmit)) {
        ?>

        <tr bgcolor="<?php echo $altbg2?>">
        <td>
        <form method="post" action="cp2.php?action=newsletter">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td colspan="2"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textnewsletter']?></font></strong></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" class="tablerow"><?php echo $lang['textsubject']?></td>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow"><input type="text" name="newssubject" size="80" bgcolor="<?php echo $altbg1?>" /></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" class="tablerow" valign="top"><?php echo $lang['textmessage']?></td>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow"><textarea cols="80" rows="10" name="newsmessage" bgcolor="<?php echo $altbg1?>" ></textarea></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" class="tablerow" valign="top"><?php echo $lang['textsendvia']?></td>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow"><input type="radio" value="email" name="sendvia" bgcolor="<?php echo $altbg1?>" /> <?php echo $lang['textemail']?><br /><input type="radio" value="u2u" checked="checked" name="sendvia" bgcolor="<?php echo $altbg1?>" /> <?php echo $lang['textu2u']?></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" class="tablerow" valign="top"><?php echo $lang['textsendto']?></td>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow"><input type="radio" value="all" checked="checked" name="to" /> <?php echo $lang['textsendall']?><br />
        <input type="radio" value="staff" name="to" /> <?php echo $lang['textsendstaff']?><br />
        <input type="radio" value="admin" name="to" /> <?php echo $lang['textsendadmin']?><br />
        <input type="radio" value="supermod" name="to" /> <?php echo $lang['textsendsupermod']?><br />
        <input type="radio" value="mod" name="to" /> <?php echo $lang['textsendmod']?></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" class="tablerow" valign="top"><?php echo $lang['textfaqextra']?></td>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow">
        <input type="checkbox" value="yes" checked="checked" name="newscopy" /> <?php echo $lang['newsreccopy']?><br />
        <select name="wait" bgcolor="<?php echo $altbg1?>">
        <option value="0">0</option>
        <option value="50">50</option>
        <option value="100">100</option>
        <option value="150">150</option>
        <option value="200">200</option>
        <option value="250">250</option>
        <option value="500">500</option>
        <option value="1000">1000</option>
        </select>
        <?php echo $lang['newswait']?><br />
        </td>
        </tr>
        <tr>
        <td align="center" colspan="2" class="tablerow" bgcolor="<?php echo $altbg2?>"><input type="submit" class="submit" name="newslettersubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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
        @set_time_limit(0);

        $newssubject = addslashes($newssubject);
        $newsmessage = addslashes($newsmessage);

        if ($newscopy != 'yes') {
            $tome = 'AND NOT username=\''.$xmbuser.'\'';
        } else {
            $tome = '';
        }

        if ($to == "all") {
            $query = $db->query("SELECT username, email FROM $table_members WHERE newsletter='yes' $tome ORDER BY uid");
        } elseif ($to == "staff") {
            $query = $db->query("SELECT username, email FROM $table_members WHERE (status='Super Administrator' OR status='Administrator' OR status='Super Moderator' OR status='Moderator') $tome ORDER BY uid");
        } elseif ($to == "admin") {
            $query = $db->query("SELECT username, email FROM $table_members WHERE (status='Administrator' OR status = 'Super Administrator') $tome ORDER BY uid");
        } elseif ($to == "supermod") {
            $query = $db->query("SELECT username, email FROM $table_members WHERE status='Super moderator' $tome ORDER by uid");
        } elseif ($to == "mod") {
            $query = $db->query("SELECT username, email FROM $table_members WHERE status='Moderator' ORDER BY uid");
        }

        $_xmbuser = addslashes($xmbuser);

        if ($sendvia == "u2u") {
            while ($memnews = $db->fetch_array($query)) {
                $db->query("INSERT INTO $table_u2u ( u2uid, msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus ) VALUES ('', '".addslashes($memnews['username'])."', '".$_xmbuser."', 'incoming', '".addslashes($memnews['username'])."', 'Inbox', '$newssubject', '$newsmessage', '" . time() . "', 'no', 'yes')");
            }
        } else {
            $newssubject = stripslashes(stripslashes($newssubject));
            $newsmessage = stripslashes(stripslashes($newsmessage));
            $headers[] = "From: $bbname <$adminemail>";
            $headers[] = "X-Sender: <$adminemail>";
            $headers[] = 'X-Mailer: PHP';
            $headers[] = 'X-AntiAbuse: Board servername - '.$bbname;
            $headers[] = 'X-AntiAbuse: Username - '.$xmbuser;
            $headers[] = 'X-Priority: 2';
            $headers[] = "Return-Path: <$adminemail>";
            $headers[] = 'Content-Type: text/plain; charset='.$charset;
            $headers = implode("\r\n", $headers);

            $i = 0;
            @ignore_user_abort(1);
            @set_time_limit(0);
            @ob_implicit_flush(1);

            while ($memnews = $db->fetch_array($query)) {
                if ($i > 0 && $i == $wait) {
                    sleep(3);
                    $i = 0;
                } else {
                    $i++;
                }

                altMail($memnews['email'], '['.$bbname.'] '.$newssubject, $newsmessage, $headers);
            }
        }
        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[newslettersubmit]</td></tr>";
    }
}

elseif ($action == "prune") {
    if (!isset($_POST['pruneSubmit'])) {
        $forumselect = forumList('pruneFromList[]', true, false);
        ?>

        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=prune">
        <table cellspacing="0" cellpadding="0" border="0" width="550">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%" style="vertical-align: top;">
        <tr>
        <td class="category" colspan="2">
        <strong>
        <span style="color: <?php echo $cattext?>">
        <?php echo $lang['textprune']?>
        </span>
        </strong>
        </td>
        </tr>
        <tr>
        <td class="tablerow" style="background-color: <?php echo $altbg1?>;">
        <?php echo $lang['pruneby']?>
        </td>
        <td class="tablerow" style="background-color: <?php echo $altbg2?>;">
        <table>
        <tr>
        <td>
        <input type="checkbox" name="pruneBy[date][check]" value="1" />
        </td>
        <td>
        <select name="pruneBy[date][type]">
        <option value="more"><?php echo $lang['prunemorethan']?></option>
        <option value="is"><?php echo $lang['pruneexactly']?></option>
        <option value="less"><?php echo $lang['prunelessthan']?></option>
        </select>
        <input type="text" name="pruneBy[date][date]" value="10" /> <?php echo $lang['daysold']?>
        </td>
        </tr>
        <tr>
        <td>
        <input type="checkbox" name="pruneBy[posts][check]" value="1" />
        </td>
        <td>
        <select name="pruneBy[posts][type]">
        <option value="more"><?php echo $lang['prunemorethan']?></option>
        <option value="is"><?php echo $lang['pruneexactly']?></option>
        <option value="less"><?php echo $lang['prunelessthan']?></option>
        </select>
        <input type="text" name="pruneBy[posts][posts]" value="10" /> <?php echo $lang['memposts']?>
        </td>
        </tr>
        </table>
        </td>
        </tr>
        <tr>
        <td class="tablerow" style="background-color: <?php echo $altbg1?>;">
        <?php echo $lang['prunefrom']?>
        </td>
        <td class="tablerow" style="background-color: <?php echo $altbg2?>;">
        <table>
        <tr>
        <td>
        <input type="radio" name="pruneFrom" value="all" checked="checked" />
        </td>
        <td>
        <?php echo $lang['textallforumsandsubs']?>
        </td>
        </tr>
        <tr>
        <td>
        <input type="radio" name="pruneFrom" value="list" />
        </td>
        <td>
        <?php echo $forumselect?>
        </td>
        </tr>
        <tr>
        <td>
        <input type="radio" name="pruneFrom" value="fid" />
        </td>
        <td>
        <?php echo $lang['prunefids']?> <input type="text" name="pruneFromFid" /> <span class="smalltxt">(<?php echo $lang['seperatebycomma']?>)</span>
        </td>
        </tr>
        </table>
        </td>
        </tr>
        <tr>
        <td class="tablerow" style="background-color: <?php echo $altbg1?>;">
        <?php echo $lang['pruneposttypes']?>
        </td>
        <td class="tablerow" style="background-color: <?php echo $altbg2?>;">
        <input type="checkbox" name="pruneType[normal]" value="1" checked="checked" /> <?php echo $lang['prunenormal']?><br />
        <input type="checkbox" name="pruneType[closed]" value="1" checked="checked" /> <?php echo $lang['pruneclosed']?><br />
        <input type="checkbox" name="pruneType[topped]" value="1" /> <?php echo $lang['prunetopped']?><br />
        </td>
        </tr>
        <tr>
        <td class="ctrtablerow" style="background-color: <?php echo $altbg2?>;" colspan="2"><input type="submit" name="pruneSubmit" value="<?php echo $lang['textprune']?>" /></td>
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
        $queryWhere = array();
        // let's check what to prune first
        switch($pruneFrom) {
            case 'all':
                break;

            case 'list':
                $fs = array();
                foreach ($pruneFromList as $fid) {
                    $fs[] = (int) trim($fid);
                }
                $fs = array_unique($fs);

                if (count($fs) < 1) {
                    error($lang['nopruneforums'], false, '</td></tr></table></td></tr></table><br />');
                }

                $queryWhere[] = 'fid IN ('.implode(',', $fs).')';
                break;

            case 'fid':
                $fs = array();

                $fids = explode(',', $pruneFromFid);

                foreach ($fids as $fid) {
                    $fs[] = (int) trim($fid);
                }
                $fs = array_unique($fs);

                if (count($fs) < 1) {
                    error($lang['nopruneforums'], false, '</td></tr></table></td></tr></table><br />');
                }

                $queryWhere[] = 'fid IN ('.implode(',', $fs).')';
                break;

            default:
                error($lang['nopruneforums'], false, '</td></tr></table></td></tr></table><br />');
        }

        // by!
        if (isset($pruneBy['posts']['check']) && $pruneBy['posts']['check'] == 1) {
            $sign = '';
            switch($pruneBy['posts']['type']) {
                case 'less':
                    $sign = '<';
                    break;

                case 'is':
                    $sign = '=';
                    break;

                case 'more':
                default:
                    $sign = '>';
                    break;
            }

            $queryWhere[] = 'replies '.$sign.' '.(int) ($pruneBy['posts']['posts']-1);
        }
        if (isset($pruneBy['date']['check']) && $pruneBy['date']['check'] == 1) {
            $sign = '';
            switch($pruneBy['date']['type']) {
                case 'less':
                    $queryWhere[] = 'lastpost >= '.(time()-(24*3600*$pruneBy['date']['date']));
                    break;

                case 'is':
                    $queryWhere[] = 'lastpost >= '.(time()-(24*3600*($pruneBy['date']['date']-1))).' AND lastpost <= '.(time()-(24*3600*($pruneBy['date']['date'])));
                    break;

                case 'more':

                default:
                    $queryWhere[] = 'lastpost <= '.(time()-(24*3600*$pruneBy['date']['date']));
                    break;
            }
        }

        if (!isset($pruneType['closed']) || $pruneType['closed'] != 1) {
            $queryWhere[] = "closed != 'yes'";
        }
        if (!isset($pruneType['topped']) || $pruneType['topped'] != 1) {
            $queryWhere[] = 'topped != 1';
        }
        if (!isset($pruneType['normal']) || $pruneType['normal'] != 1) {
            $queryWhere[] = "(topped == 1 OR closed == 'yes')";
        }

        if (count($queryWhere) > 0) {
            $tids = array();

            $queryWhere = implode(' AND ', $queryWhere);
            $q = $db->query("SELECT tid FROM $table_threads WHERE ".$queryWhere);
            if ( $db->num_rows($q) > 0) {
                while($t = $db->fetch_array($q)) {
                    $tids[] = $t['tid'];
                }
                $tids = implode(',', $tids);
                $db->query("DELETE FROM $table_threads WHERE tid IN($tids)");
                $db->query("DELETE FROM $table_posts WHERE tid IN($tids)");
                $db->query("DELETE FROM $table_attachments WHERE tid IN($tids)");
            }
        } else {
            $db->query("TRUNCATE TABLE $table_threads");
            $db->query("TRUNCATE TABLE $table_attachments");
            $db->query("TRUNCATE TABLE $table_posts");
            $db->query("UPDATE $table_members SET postnum=0");
        }

        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[forumpruned]</td></tr>";
    }
}

elseif ($action == "templates") {
    if (!isset($edit) && !isset($editsubmit) && !isset($delete) && !isset($deletesubmit) && !isset($new) && !isset($restore) && !isset($restoresubmit)) {
        ?>

        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=templates">
        <table cellspacing="0" cellpadding="0" border="0" width="80%" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['templates']?></font></strong></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow">
        <input type="text" name="newtemplatename" size="30" maxlength="50" />&nbsp;&nbsp;
        <input type="submit" class="submit" name="new" value="<?php echo $lang['newtemplate']?>" />
        </td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow">

        <?php
        $query = $db->query("SELECT * FROM $table_templates ORDER BY name");
        echo "<select name=\"tid\"><option value=\"default\">$lang[selecttemplate]</option>";
        while ($template = $db->fetch_array($query)) {
            if (!empty($template['name'])) {
                echo "<option value=\"$template[id]\">$template[name]</option>\r\n";
            }
        }
        echo "</select>&nbsp;&nbsp;";
        ?>

        </td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow">
        <input type="submit" class="submit"name="edit" value="<?php echo $lang['textedit']?>" />&nbsp;
        <input type="submit" class="submit"name="delete" value="<?php echo $lang['deletebutton']?>" />&nbsp;
        <input type="submit" class="submit" name="restore" value="<?php echo $lang['textrestoredeftemps']?>" />&nbsp;
        <input type="submit" class="submit" name="download" value="<?php echo $lang['textdownloadtemps']?>" />
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

    if (isset($restore)) {
        ?>

        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=templates">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['templates']?></font></strong></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" class="ctrtablerow"><?php echo $lang['templaterestoreconfirm']?></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow"><input type="submit" class="submit" name="restoresubmit" value="<?php echo $lang['textyes']?>" /></td>
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

    if (isset($restoresubmit)) {
        if (!file_exists('./templates.xmb')) {
            error($lang['no_templates'], false, '</td></tr></table></td></tr></table><br />');
        }
        $db->query("TRUNCATE $table_templates");

        $filesize=filesize('templates.xmb');
        $fp=fopen('templates.xmb','r');
        $templatesfile=fread($fp,$filesize);
        fclose($fp);

        $templates = explode("|#*XMB TEMPLATE FILE*#|", $templatesfile);
        while (list($key,$val) = each($templates)) {
            $template = explode("|#*XMB TEMPLATE*#|", $val);
            $template[1] = isset($template[1]) ? addslashes($template[1]) : '';
            $db->query("INSERT INTO $table_templates (id, name, template) VALUES ('', '".addslashes($template[0])."', '".addslashes($template[1])."')");
        }

        $db->query("DELETE FROM $table_templates WHERE name=''");
        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[templatesrestoredone]</td></tr>";
    }

    if (isset($edit) && !isset($editsubmit)) {
        if ($tid == "default") {
            error($lang['selecttemplate'], false, '</td></tr></table></td></tr></table><br />');
        }

        ?>

        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=templates&amp;tid=<?php echo $tid?>">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['templates']?></font></strong></td>
        </tr>

        <?php
        $query = $db->query("SELECT * FROM $table_templates WHERE id='$tid' ORDER BY name");
        $template = $db->fetch_array($query);
        $template['template'] = stripslashes($template['template']);
        $template['template'] = htmlspecialchars($template['template']);
        ?>

        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow"><?php echo $lang['templatename']?>&nbsp;<strong><?php echo $template['name']?></strong></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" class="ctrtablerow">
        <textarea cols="100" rows="30" name="templatenew"><?php echo $template['template']?></textarea></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow"><input type="submit" name="editsubmit" class="submit" value="<?php echo $lang['textsubmitchanges']?>" /></strong></td>
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

    if (isset($editsubmit)) {
        $templatenew = addslashes($templatenew);
        if ($tid == "new") {
            if (empty($namenew)) {
                error($lang['templateempty'], false, '</td></tr></table></td></tr></table><br />');
            } else {
                $check = $db->query("SELECT name FROM $table_templates WHERE name = '$namenew'");
                if ($check && $db->num_rows($check) != 0) {
                    error($lang['templateexists'], false, '</td></tr></table></td></tr></table><br />');
                } else {
                    $db->query("INSERT INTO $table_templates (id, name, template) VALUES ('', '$namenew', '$templatenew')");
                }
            }
        } else {
            $db->query("UPDATE $table_templates SET template='$templatenew' WHERE id='$tid'");
        }
        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[templatesupdate]</td></tr>";
    }

    if (isset($delete)) {
        if ($tid == "default") {
            error($lang['selecttemplate'], false, '</td></tr></table></td></tr></table><br />');
        }
        ?>

        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=templates&amp;tid=<?php echo $tid?>">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category"><strong><font color="<?php echo $cattext?>"><?php echo $lang['templates']?></font></strong></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" class="ctrtablerow"><?php echo $lang['templatedelconfirm']?></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow"><input type="submit" class="submit" name="deletesubmit" value="<?php echo $lang['textyes']?>" /></td>
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

    if (isset($deletesubmit)) {
        $db->query("DELETE FROM $table_templates WHERE id='$tid'");
        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[templatesdelete]</td></tr>";
    }

    if (isset($new)) {
        ?>

        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=templates&amp;tid=new">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category"><strong><font color="<?php echo $cattext?>"><?php echo $lang['templates']?></font></strong></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow"><?php echo $lang['templatename']?>&nbsp;<input type="text" name="namenew" size="30" value="<?php echo $newtemplatename?>" /></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg1?>" class="ctrtablerow"><textarea cols="100" rows="30" name="templatenew"></textarea></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow"><input type="submit" name="editsubmit" value="<?php echo $lang['textsubmitchanges']?>" class="submit" /></td>
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

elseif ($action == "attachments") {
    if (!isset($attachsubmit) && !isset($searchsubmit)) {
        $forumselect = forumList('forumprune', false, true);
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=attachments">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr><td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category" colspan="2"><font color="<?php echo $cattext?>"><strong><?php echo $lang['textsearch']?></font></strong></td>
        </tr>
        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwherename']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="filename" size="30" /></td>
        </tr>
        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwhereauthor']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="author" size="40" /></td>
        </tr>
        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwhereforum']?></td>
        <td bgcolor="<?php echo $altbg2?>"><?php echo $forumselect?></td>
        </tr>
        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwheresizesmaller']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="sizeless" size="20" /></td>
        </tr>
        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwheresizegreater']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="sizemore" size="20" /></td>
        </tr>
        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwheredlcountsmaller']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="dlcountless" size="20" /></td>
        </tr>
        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwheredlcountgreater']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="dlcountmore" size="20" /></td>
        </tr>
        <tr>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwheredaysold']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="daysold" size="20" /></td>
        </tr>
        <tr>
        <td align="center" class="tablerow" bgcolor="<?php echo $altbg2?>" colspan="2"><input type="submit" name="searchsubmit" class="submit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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

    if (isset($searchsubmit)) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=attachments">
        <table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category" colspan="6"><font color="<?php echo $cattext?>"><strong><?php echo $lang['textattachsearchresults']?></strong></font></td>
        </tr>
        <tr>
        <td class="header" width="4%" align="center">?</td>
        <td class="header" width="25%"><?php echo $lang['textfilename']?></td>
        <td class="header" width="29%"><?php echo $lang['textauthor']?></td>
        <td class="header" width="27%"><?php echo $lang['textinthread']?></td>
        <td class="header" width="10%"><?php echo $lang['textfilesize']?></td>
        <td class="header" width="5%"><?php echo $lang['textdownloads']?></td>
        </tr>
        <?php

        $restriction = '';
        $orderby = '';

        if (isset($forumprune) && is_numeric($forumprune)) {
            $forumprune = (int) $forumprune;
            $restriction .= "AND p.fid=$forumprune ";
        }

        if (isset($daysold) && is_numeric($daysold)) {
            $datethen = time() - (86400 * $daysold);
            $restriction .= "AND p.dateline <= $datethen ";
            $orderby = ' ORDER BY p.dateline ASC';
        }

        if (isset($author)) {
            $author = trim($author);
            if ( $author != '' ) {
                $restriction .= "AND p.author = '$author' ";
                $orderby = ' ORDER BY p.author ASC';
            }
        }

        if (isset($filename)) {
            $filename = trim($filename);
            if ( $filename != "" ) {
                $restriction .= "AND a.filename LIKE '%$filename%' ";
            }
        }

        if (isset($sizeless) && is_numeric($sizeless)) {
            $sizeless = (int) $sizeless;
            $restriction .= "AND a.filesize < $sizeless ";
            $orderby = ' ORDER BY a.filesize DESC';
        }

        if (isset($sizemore) && is_numeric($sizemore)) {
            $sizemore = (int) $sizemore;
            $restriction .= "AND a.filesize > $sizemore ";
            $orderby = ' ORDER BY a.filesize DESC';
        }

        if (isset($dlcountless) && is_numeric($dlcountless)) {
            $dlcountless = (int) $dlcountless;
            $restriction .= "AND a.downloads < $dlcountless ";
            $orderby = ' ORDER BY a.downloads DESC';
        }

        if (isset($dlcountmore) && is_numeric($dlcountmore)) {
            $dlcountmore = (int) $dlcountmore;
            $restriction .= "AND a.downloads > $dlcountmore ";
            $orderby = ' ORDER BY a.downloads DESC ';
        }
        $query = $db->query("SELECT a.*, p.*, t.tid, t.subject AS tsubject, f.name AS fname FROM $table_attachments a, $table_posts p, $table_threads t, $table_forums f WHERE a.pid=p.pid AND t.tid=a.tid AND f.fid=p.fid $restriction $orderby");
            while ($attachment = $db->fetch_array($query)) {
            $attachsize = strlen($attachment['attachment']);
            if ($attachsize >= 1073741824) {
                $attachsize = round($attachsize / 1073741824 * 100) / 100 . "gb";
            } elseif ($attachsize >= 1048576) {
                $attachsize = round($attachsize / 1048576 * 100) / 100 . "mb";
            } elseif ($attachsize >= 1024) {
                $attachsize = round($attachsize / 1024 * 100) / 100 . "kb";
            } else {
                $attachsize = $attachsize . "b";
            }
            $attachment['tsubject'] = stripslashes($attachment['tsubject']);
            $attachment['fname'] = stripslashes($attachment['fname']);
            $attachment['filename'] = stripslashes($attachment['filename']);
            ?>
            <tr>
            <td bgcolor="<?php echo $altbg1?>" class="tablerow" align="center" valign="middle"><a href="cp2.php?action=delete_attachment&amp;aid=<?php echo $attachment['aid']?>">Delete</a>
            <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top"><input type="text" name="filename<?php echo $attachment['aid']?>" value="<?php echo $attachment['filename']?>"><br /><span class="smalltxt"><a href="viewthread.php?action=attachment&amp;tid=<?php echo $attachment['tid']?>&amp;pid=<?php echo $attachment['pid']?>" target="_blank"><?php echo $lang['textdownload']?></a></td>
            <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top"><?php echo $attachment['author']?></td>
            <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top"><a href="viewthread.php?tid=<?php echo $attachment[tid]?>"><?php echo $attachment['tsubject']?></a><br /><span class="smalltxt"><?php echo $lang['textinforum']?> <a href="forumdisplay.php?fid=<?php echo $attachment['fid']?>"><?php echo $attachment['fname']?></a></span></td>
            <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top" align="center"><?php echo $attachsize?></td>
            <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top" align="center"><?php echo $attachment['downloads']?></td>
            </tr>
            <?php

        }
        ?>
        <tr>
        <td align="center" class="tablerow" bgcolor="<?php echo $altbg2?>" colspan="6"><input class="submit" type="submit" name="deletesubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        <input type="hidden" name="filename" value="<?php echo $filename?>" />
        <input type="hidden" name="author" value="<?php echo $author?>" />
        <input type="hidden" name="forumprune" value="<?php echo $forumprune?>" />
        <input type="hidden" name="sizeless" value="<?php echo $sizeless?>" />
        <input type="hidden" name="sizemore" value="<?php echo $sizemore?>" />
        <input type="hidden" name="dlcountless" value="<?php echo $dlcountless?>" />
        <input type="hidden" name="dlcountmore" value="<?php echo $dlcountmore?>" />
        <input type="hidden" name="daysold" value="<?php echo $daysold?>" />
        </form>
        </td>
        </tr>
        <?php
    }

    if (isset($deletesubmit)) {
        if ($forumprune != "" && $forumprune != $lang['textall']) {
            $queryforum = "AND p.fid='$forumprune' ";
        }

        if ($daysold != "") {
            $datethen = time() - (86400*$daysold);
            $querydate = "AND p.dateline <= '$datethen' ";
        }

        if ($author != "") {
            $queryauthor = "AND p.author = '$author' ";
        }

        if ($filename != "") {
            $queryname = "AND a.filename LIKE '%$filename%' ";
        }

        if ($sizeless != "") {
            $querysizeless = "AND a.filesize < '$sizeless' ";
        }

        if ($sizemore != "") {
            $querysizemore = "AND a.filesize > '$sizemore' ";
        }

        if ($dlcountless != "") {
            $querydlcountless = "AND a.downloads < '$dlcountless' ";
        }

        if ($dlcountmore != "") {
            $querydlcountmore = "AND a.downloads > '$dlcountmore' ";
        }

        $query = $db->query("SELECT a.*, p.*, t.tid, t.subject AS tsubject, f.name AS fname FROM $table_attachments a, $table_posts p, $table_threads t, $table_forums f WHERE a.pid=p.pid AND t.tid=a.tid AND f.fid=p.fid $queryforum $querydate $queryauthor $queryname $querysizeless $querysizemore");
        while ($attachment = $db->fetch_array($query)) {
            $afilename = "filename" . $attachment['aid'];
            $afilename = isset($_POST[$afilename]) ? $_POST[$afilename] : '';

            if ($attachment['filename'] != $afilename) {
                $db->query("UPDATE $table_attachments SET filename='$afilename' WHERE aid='$attachment[aid]'");
            }
        }
        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textattachmentsupdate]</td></tr>";
    }
}

elseif ($action == "modlog") {
    nav($lang['textmodlogs']);
    ?>
    <tr bgcolor="<?php echo $altbg2?>">
    <td align="center">
    <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
    <tr>
    <td bgcolor="<?php echo $bordercolor?>">
    <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
    <tr class="category">
    <td><strong><font color="<?php echo $cattext?>">Username:</font></strong></td>
    <td><strong><font color="<?php echo $cattext?>">Time:</font></strong></td>
    <td><strong><font color="<?php echo $cattext?>">URL:</font></strong></td>
    <td><strong><font color="<?php echo $cattext?>">Action:</font></strong></td>
    </tr>

    <?php
    $count = $db->result($db->query("SELECT count(fid) FROM $table_logs WHERE NOT (fid='0' AND tid='0')"), 0);

    if (!isset($page) || $page < 1) {
        $page = 1;
    }

    $old = (($page-1)*100);
    $current = ($page*100);

    $prevpage = '';
    $nextpage = '';
    $random_var = '';

    $query = $db->query("SELECT l.*, t.subject FROM $table_logs l LEFT JOIN $table_threads t ON l.tid=t.tid WHERE NOT (l.fid='0' AND l.tid='0') ORDER BY date ASC LIMIT $old, 100");

    $url = '';

    while ($recordinfo = $db->fetch_array($query)) {
        $date = gmdate($dateformat, $recordinfo['date']);
        $time = gmdate($timecode, $recordinfo['date']);
        if ($recordinfo['tid'] > 0 && $recordinfo['action'] != 'delete' && trim($recordinfo['subject']) != '') {
            $url = "<a href=\"./viewthread.php?tid=$recordinfo[tid]\" target=\"_blank\">$recordinfo[subject]</a>";
        } elseif ($recordinfo['action'] == 'delete') {
            $recordinfo['action'] = '<strong>'.$recordinfo['action'].'</strong>';
            $url = '&nbsp;';
        } else {
            $url = 'tid='.$recordinfo['tid'].' - fid:'.$recordinfo['fid'];
        }
        ?>
        <tr>

        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><a href="./member.php?action=viewpro&amp;member=<?php echo $recordinfo['username']?>"><?php echo $recordinfo['username']?></a></td>
        <td class="tablerow" bgcolor="<?php echo $altbg2?>"><?php echo $date?> at <?php echo $time?></td>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $url?></td>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $recordinfo['action']?></td>
        </tr>
        <?php
    }

    if ($count > $current) {
        $page = $current/100;
        if ($page > 1) {
            $prevpage = '<a href="./cp2.php?action=modlog&amp;page='.($page-1).'">&laquo; Previous Page</a>';
        }

        $nextpage = '<a href="./cp2.php?action=modlog&amp;page='.($page+1).'">Next Page &raquo;</a>';

        if ($prevpage == '' || $nextpage == '') {
            $random_var = '';
        } else {
            $random_var = '-';
        }

        $last = ceil($count/100);
        if ($last > $page) {
            $lastpage = '<a href="./cp2.php?action=modlog&amp;page='.$last.'">&nbsp;&raquo;&raquo;</a>';
        }

        $first = 1;
        if ($page > $first) {
            $firstpage = '<a href="./cp2.php?action=modlog&amp;page='.$first.'">&nbsp;&laquo;&laquo;</a>';
        }

        ?>
        <tr class="header">
        <td colspan="4"><?php echo $firstpage?> <?php echo $prevpage?> <?php echo $random_var?> <?php echo $nextpage?> <?php echo $lastpage?></td>
        </tr>

        <?php
    } else {
        if ($page > 1) {
            $prevpage = '<a href="./cp2.php?action=modlog&amp;page='.($page-1).'">&laquo; Previous Page</a>';
        }

        $first = 1;
        if ($page > $first) {
            $firstpage = '<a href="./cp2.php?action=mod&amp;page='.$first.'">&nbsp;&laquo;&laquo;</a>';
        } else {
            $firstpage = '';
        }
        if ($prevpage == '' || $nextpage == '') {
            $random_var = '';
        } else {
            $random_var = '-';
        }

        ?>
        <tr class="header">
        <td colspan="4"><?php echo $firstpage?> <?php echo $prevpage?> <?php echo $random_var?> <?php echo $nextpage?></td>
        </tr>
        <?php
    }

    if ($count == 0) {
        ?>
        <tr class="header">
        <td colspan="4">No logs present</td>
        </tr>
        <?php
    }
    ?>
    </table>
    </td></tr></table>
    </td>
    </tr>
    <?php
}

elseif ($action == "cplog") {
    nav($lang['textcplogs']);
    ?>
    <tr bgcolor="<?php echo $altbg2?>">
    <td align="center">
    <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
    <tr>
    <td bgcolor="<?php echo $bordercolor?>">
    <table border="0" cellspacing="<?php echo $borderwidth?>" cellpadding="<?php echo $tablespace?>" width="100%">
    <tr class="category">
    <td><strong><font color="<?php echo $cattext?>">Username:</font></strong></td>
    <td><strong><font color="<?php echo $cattext?>">Time:</font></strong></td>
    <td><strong><font color="<?php echo $cattext?>">URL:</font></strong></td>
    <td><strong><font color="<?php echo $cattext?>">Action:</font></strong></td>
    <td><strong><font color="<?php echo $cattext?>">Ip:</font></strong></td>
    </tr>

    <?php
    $count = $db->result($db->query("SELECT count(fid) FROM $table_logs WHERE (fid='0' AND tid='0')"), 0);

    if (!isset($page) || $page < 1) {
        $page = 1;
    }

    $old = (($page-1)*100);
    $current = ($page*100);
    $firstpage = '';
    $prevpage = '';
    $nextpage = '';
    $random_var = '';

    $query = $db->query("SELECT l.*, t.subject FROM $table_logs l LEFT JOIN $table_threads t ON l.tid=t.tid WHERE (l.fid='0' AND l.tid='0') ORDER BY date ASC LIMIT $old, 100");

    $url = '';

    while ($recordinfo = $db->fetch_array($query)) {
        $date = gmdate($dateformat, $recordinfo['date']);
        $time = gmdate($timecode, $recordinfo['date']);
        $action = explode('|#|', $recordinfo['action']);
        if (strpos($action[1], '/') === false) {
            $recordinfo['action'] = $action[1];
            $url = '&nbsp';
        } else {
            // an URL!
            $recordinfo['action'] = '&nbsp;';
            $url = $action[1];
        }

        ?>
        <tr>

        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><a href="./member.php?action=viewpro&amp;member=<?php echo $recordinfo['username']?>"><?php echo $recordinfo['username']?></a></td>
        <td class="tablerow" bgcolor="<?php echo $altbg2?>"><?php echo $date?> at <?php echo $time?></td>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $url?></td>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $recordinfo['action']?></td>
        <td class="tablerow" bgcolor="<?php echo $altbg1?>"><?php echo $action[0]?></td>
        </tr>
        <?php
    }

    if ($count > $current) {
        $page = $current/100;
        if ($page > 1) {
            $prevpage = '<a href="./cp2.php?action=cplog&amp;page='.($page-1).'">&laquo; Previous Page</a>';
        }

        $nextpage = '<a href="./cp2.php?action=cplog&amp;page='.($page+1).'">Next Page &raquo;</a>';

        if ($prevpage == '' || $nextpage == '') {
            $random_var = '';
        } else {
            $random_var = '-';
        }

        $last = ceil($count/100);
        if ($last > $page) {
            $lastpage = '<a href="./cp2.php?action=cplog&amp;page='.$last.'">&nbsp;&raquo;&raquo;</a>';
        }

        $first = 1;
        if ($page > $first) {
            $firstpage = '<a href="./cp2.php?action=cplog&amp;page='.$first.'">&nbsp;&laquo;&laquo;</a>';
        }


        ?>
        <tr class="header">
        <td colspan="5"><?php echo $firstpage?> <?php echo $prevpage?> <?php echo $random_var?> <?php echo $nextpage?> <?php echo $lastpage?></td>
        </tr>

        <?php
    } else {
        if ($page == 1) {
            $prevpage = '';
        } else {
            $prevpage = '<a href="./cp2.php?action=cplog&amp;page='.($page-1).'">&laquo; Previous Page</a>';
        }

        $first = 1;
        if ($page > $first) {
            $firstpage = '<a href="./cp2.php?action=cplog&amp;page='.$first.'">&nbsp;&laquo;&laquo;</a>';
        }

        ?>
        <tr class="header">
        <td colspan="5"><?php echo $firstpage?> <?php echo $prevpage?> <?php echo $random_var?> <?php echo $nextpage?></td>
        </tr>
        <?php
    }

    if ($count == 0) {
        ?>
        <tr class="header">
        <td colspan="5">No logs present</td>
        </tr>
        <?php
    }
    ?>
    </table>
    </td></tr></table>
    </td>
    </tr>
    <?php
}

elseif ($action == "delete_attachment") {
    $db->query("DELETE FROM $table_attachments WHERE aid='$aid'");
    echo "<p align=\"center\">Deleted ...</br>";
}

echo '</table></td></tr></table>';
end_time();
eval('echo "'.template('footer').'";');
?>