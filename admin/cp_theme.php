<?php
/* $Id: cp_theme.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
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

function downloadThemes() {
    $contents = array ();
    $query = $db->query("SELECT * FROM $table_themes WHERE themeid='$download'");
    $themebits = $db->fetch_array($query);
    foreach ($themebits as $key => $val) {
        if (!is_integer($key) && $key != 'themeid' && $key != 'dummy') {
            $contents[] = $key . '=' . $val;
        }
    }
    $name = str_replace(' ', '+', $themebits['name']);
    header("Content-Type: application/x-ms-download");
    header("Content-Disposition: filename=".$name."-theme.xmb");
    echo implode("\r\n", $contents);
    exit ();
}

function displayThemePanel() {
    global $THEME, $SETTINGS, $lang, $oToken;
    ?>

    <tr class="altbg2">
    <td>
    <form method="post" action="cp2.php?action=themes" name="theme_main">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td align="center"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textdeleteques']?></font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textthemename']?></font></strong></td>
    <td><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['numberusing']?></font></strong></td>
    </tr>

    <?php

    $themeMem = array (
        0 => 0
    );
    $tq = $db->query("SELECT theme, count(theme) as cnt FROM $table_members GROUP BY theme");
    while ($t = $db->fetch_array($tq)) {
        $themeMem[((int) $t['theme'])] = $t['cnt'];
    }
    $query = $db->query("SELECT name, themeid FROM $table_themes ORDER BY name ASC");
    while ($themeinfo = $db->fetch_array($query)) {
        $themeid = $themeinfo['themeid'];
        if (!isset ($themeMem[$themeid])) {
            $themeMem[$themeid] = 0;
        }
        if ($themeinfo['themeid'] == $SETTINGS['theme']) {
            $members = ($themeMem[$themeid] + $themeMem[0]);
        } else {
            $members = $themeMem[$themeid];
        }

        if ($themeinfo['themeid'] == $SETTINGS['theme']) {
            $checked = 'checked="checked"';
        } else {
            $checked = 'checked="unchecked"';
        }
?>

        <tr class="altbg2 tablerow">
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

    <tr class="altbg2">
    <td colspan="3"><img src="<?php echo ROOT?>images/pixel.gif" alt="" /></td>
    </tr>
    <tr class="altbg1 tablerow">
    <td colspan="3">
    <a href="cp2.php?action=themes&amp;single=anewtheme1">
        <strong><?php echo $lang['textnewtheme']?></strong>
    </a>
     -
    <a href="#" onclick="setCheckboxes('theme_main', 'theme_delete[]', true); return false;">
        Check All
    </a>
     -
    <a href="#" onclick="setCheckboxes('theme_main', 'theme_delete[]', false); return false;">
        Uncheck All
    </a>
     -
    <a href="#" onclick="invertSelection('theme_main', 'theme_delete[]'); return false;">
        Invert Selection
    </a>
    </td>
    </tr>
    <tr>
    <td class="altbg2 tablerow" align="center" colspan="3"><input type="submit" name="themesubmit" value="<?php echo $lang['textsubmitchanges']?>" class="submit" /></td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </form>
    <br />
    <form method="post" action="cp2.php?action=themes" enctype="multipart/form-data">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="header">
    <td colspan="2"><?php echo $lang['textimporttheme']?></td>
    </tr>
    <tr class="tablerow">
    <td class="altbg1"><?php echo $lang['textthemefile']?></td>
    <td class="altbg2"><input name="themefile" type="file" /></td>
    </tr>
    <tr>
    <td class="altbg2 tablerow" align="center" colspan="2"><input type="submit" class="submit" name="importsubmit" value="<?php echo $lang['textimportsubmit']?>" /></td>
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

function processThemeImportFile() {
    global $db, $lang, $oToken;

    $oToken->isValidToken();

    $themefile = formArray('themefile');

    $keys = array ();
    $query = $db->query("SELECT * FROM $table_themes limit 0, 1");
    foreach ($db->fetch_array($query) as $key => $val) {
        if ($key != 'themeid') {
            $keys[] = $key;
        }
    }
    $db->free_result($query);

    $themebits = readFileAsINI($themefile['tmp_name']);

    $keysql = array ();
    $valsql = array ();

    foreach ($themebits as $key => $val) {
        if (in_array($key, $keys)) {
            if ($key == 'name') {
                $name = $val;
            }
            $keysql[] = $key;
            $valsql[] = "'$val'";
        }
    }

    $keysql = implode(', ', $keysql);
    $valsql = implode(', ', $valsql);

    $query = $db->query("SELECT count(themeid) FROM $table_themes WHERE name='" . $db->escape($name) . "'");
    if ($db->result($query, 0) > 0) {
        error($lang['theme_already_exists'], false, '</td></tr></table></td></tr></table>');
    }

    $sql = "INSERT INTO $table_themes ($keysql) VALUES ($valsql);";
    $query = $db->query($sql);

    echo '<tr class="tablerow altbg2"><td align="center">';
    if (!$query) {
        echo $lang['textthemeimportfail'];
    } else {
        echo $lang['textthemeimportsuccess'];
    }
    echo '</td></tr>';
    message($lang['censorupdate'], false, '', '', 'cp2.php', false, false, false); // TODO
}

function processThemeImport() {
    global $db, $SETTINGS, $table_themes, $lang, $oToken;
    
    $oToken->isValidToken();

    $number_of_themes = $db->result($db->query("SELECT count(themeid) FROM $table_themes"), 0);

    $theme_delete = getFormArrayInt('theme_delete');
    if (count($theme_delete) >= $number_of_themes) {
        error($lang['delete_all_themes'], false, '</td></tr></table></td></tr></table>');
    }

    
    foreach ($theme_delete as $themeid) {
        $otherid = $db->result($db->query("SELECT themeid FROM $table_themes WHERE themeid != '$themeid' ORDER BY rand() LIMIT 1"), 0);
        $db->query("UPDATE $table_members SET theme='$otherid' WHERE theme='$themeid'");
        $db->query("UPDATE $table_forums SET theme=0 WHERE theme='$themeid'");

        if ($SETTINGS['theme'] == $themeid) {
            $db->query("UPDATE $table_settings SET theme='$otherid'");
        }

        $db->query("DELETE FROM $table_themes WHERE themeid='$themeid'");
    }
    
    $theme_name = formArray('theme_name');
    foreach ($theme_name as $themeid => $name) {
        $db->query("UPDATE $table_themes SET name='$name' WHERE themeid='$themeid'");
    }

    echo '<tr class="tablerow altbg2"><td align="center">' . $lang['themeupdate'] . '</td></tr>';
    message($lang['censorupdate'], false, '', '', 'cp2.php', false, false, false); // TODO
}

function displayThemeSingle() {
    global $db, $lang, $THEME;

    $single = $db->escape(getRequestVar('single'));

    $query = $db->query("SELECT * FROM $table_themes WHERE themeid='$single'");
    $themestuff = $db->fetch_array($query);
?>

    <tr class="altbg2">
    <td>
    <form method="post" action="cp2.php">
    <input type="hidden" name="action" value="themes">
    <input type="hidden" name="single" value="submit">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['texthemename']?></td>
    <td colspan="2"><input type="text" name="namenew" value="<?php echo $themestuff['name']?>" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textbgcolor']?></td>
    <td><input type="text" name="bgcolornew" value="<?php echo $themestuff['bgcolor']?>" /></td>
    <td style="background-color: <?php echo $themestuff['bgcolor']?>">&nbsp;</td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textaltbg1']?></td>
    <td><input type="text" name="altbg1new" value="<?php echo $themestuff['altbg1']?>" /></td>
    <td style="background-color: <?php echo $themestuff['altbg1']?>">&nbsp;</td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textaltbg2']?></td>
    <td><input type="text" name="altbg2new" value="<?php echo $themestuff['altbg2']?>" /></td>
    <td style="background-color: <?php echo $themestuff['altbg2']?>">&nbsp;</td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textlink']?></td>
    <td><input type="text" name="linknew" value="<?php echo $themestuff['link']?>" /></td>
    <td style="background-color: <?php echo $themestuff['link']?>">&nbsp;</td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textborder']?></td>
    <td><input type="text" name="bordercolornew" value="<?php echo $themestuff['bordercolor']?>" /></td>
    <td style="background-color: <?php echo $themestuff['bordercolor']?>">&nbsp;</td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textheader']?></td>
    <td><input type="text" name="headernew" value="<?php echo $themestuff['header']?>" /></td>
    <td style="background-color: <?php echo $themestuff['header']?>">&nbsp;</td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textheadertext']?></td>
    <td><input type="text" name="headertextnew" value="<?php echo $themestuff['headertext']?>" /></td>
    <td style="background-color: <?php echo $themestuff['headertext']?>">&nbsp;</td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['texttop']?></td>
    <td><input type="text" name="topnew" value="<?php echo $themestuff['top']?>" /></td>
    <td style="background-color: <?php echo $themestuff['top']?>">&nbsp;</td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textcatcolor']?></td>
    <td><input type="text" name="catcolornew" value="<?php echo $themestuff['catcolor']?>" /></td>
    <td style="background-color: <?php echo $themestuff['catcolor']?>">&nbsp;</td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textcattextcolor']?></td>
    <td><input type="text" name="cattextnew" value="<?php echo $themestuff['cattext']?>" /></td>
    <td style="background-color: <?php echo $themestuff['cattext']?>">&nbsp;</td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['texttabletext']?></td>
    <td><input type="text" name="tabletextnew" value="<?php echo $themestuff['tabletext']?>" /></td>
    <td style="background-color: <?php echo $themestuff['tabletext']?>">&nbsp;</td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['texttext']?></td>
    <td><input type="text" name="textnew" value="<?php echo $themestuff['text']?>" /></td>
    <td style="background-color: <?php echo $themestuff['text']?>">&nbsp;</td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textborderwidth']?></td>
    <td colspan="2"><input type="text" name="borderwidthnew" value="<?php echo $themestuff['borderwidth']?>" size="2" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textwidth']?></td>
    <td colspan="2"><input type="text" name="tablewidthnew" value="<?php echo $themestuff['tablewidth']?>" size="3" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textspace']?></td>
    <td colspan="2"><input type="text" name="tablespacenew" value="<?php echo $themestuff['tablespace']?>" size="2" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textfont']?></td>
    <td colspan="2"><input type="text" name="fnew" value="<?php echo htmlspecialchars($themestuff['font'])?>" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textbigsize']?></td>
    <td colspan="2"><input type="text" name="fsizenew" value="<?php echo $themestuff['fontsize']?>" size="4" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textboardlogo']?></td>
    <td colspan="2"><input type="text"  value="<?php echo $themestuff['boardimg']?>" name="boardlogonew" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['imgdir']?></td>
    <td colspan="2"><input type="text"  value="<?php echo $themestuff['imgdir']?>" name="imgdirnew" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['smdir']?></td>
    <td colspan="2"><input type="text"  value="<?php echo $themestuff['smdir']?>" name="smdirnew" /></td>
    </tr>
    <tr>
    <td class="altbg2 tablerow" align="center" colspan="3">
    <input type="submit" class="submit" value="<?php echo $lang['textsubmitchanges']?>" />
    <input type="hidden" name="orig" value="<?php echo $single?>" /></td>
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

function displayNewTheme() {
    global $db, $lang, $THEME, $SETTINGS;

    $single = htmlentities(getRequestVar('single'));
?>
    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp2.php?action=themes&amp;single=submit">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
    <tr>
    <td bgcolor="<?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['texthemename']?></td>
    <td><input type="text" name="namenew" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textbgcolor']?></td>
    <td><input type="text" name="bgcolornew" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textaltbg1']?></td>
    <td><input type="text" name="altbg1new" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textaltbg2']?></td>
    <td><input type="text" name="altbg2new" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textlink']?></td>
    <td><input type="text" name="linknew" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textborder']?></td>
    <td><input type="text" name="bordercolornew" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textheader']?></td>
    <td><input type="text" name="headernew" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textheadertext']?></td>
    <td><input type="text" name="headertextnew" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['texttop']?></td>
    <td><input type="text" name="topnew" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textcatcolor']?></td>
    <td><input type="text" name="catcolornew" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textcattextcolor']?></td>
    <td><input type="text" name="cattextnew" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['texttabletext']?></td>
    <td><input type="text" name="tabletextnew" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['texttext']?></td>
    <td><input type="text" name="textnew" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textborderwidth']?></td>
    <td><input type="text" name="borderwidthnew" size="2" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textwidth']?></td>
    <td><input type="text" name="tablewidthnew" size="3" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textspace']?></td>
    <td><input type="text" name="tablespacenew" size="2" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textfont']?></td>
    <td><input type="text" name="fnew" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textbigsize']?></td>
    <td><input type="text" name="fsizenew" size="4" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['textboardlogo']?></td>
    <td><input type="text" name="boardlogonew" value="<?php echo $SETTINGS['boardimg']?>" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['imgdir']?></td>
    <td><input type="text" name="imgdirnew" value="images" /></td>
    </tr>
    <tr class="altbg2 tablerow">
    <td><?php echo $lang['smdir']?></td>
    <td><input type="text" name="smdirnew" value="images" /></td>
    </tr>
    <tr>
    <td class="altbg2 tablerow" align="center" colspan="2">
    <input class="submit" type="submit" value="<?php echo $lang['textsubmitchanges']?>" />
    <input type="hidden" name="newtheme" value="<?php echo $single?>" />
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

function processThemeSingle() {
    global $lang, $table_themes, $db, $oToken;
    
    $oToken->isValidToken();

    $field_list = array (
        'bgcolor',
        'altbg1',
        'altbg2',
        'link',
        'bordercolor',
        'header',
        'headertext',
        'catcolor',
        'tabletext',
        'text',
        'cattext'
    );
    foreach ($field_list as $field) {
        $f = formVar($field . 'new');
        ${$f} = preg_replace('#^[0-9a-f]{3,6}$#i', '#$0', $f);
    }

    $db->query("UPDATE $table_themes SET name='$namenew', bgcolor='$bgcolornew', altbg1='$altbg1new', altbg2='$altbg2new', link='$linknew', bordercolor='$bordercolornew', header='$headernew', headertext='$headertextnew', top='$topnew', catcolor='$catcolornew', tabletext='$tabletextnew', text='$textnew', borderwidth='$borderwidthnew', tablewidth='$tablewidthnew', tablespace='$tablespacenew', fontsize='$fsizenew', font='$fnew', boardimg='$boardlogonew', imgdir='$imgdirnew', smdir='$smdirnew', cattext='$cattextnew' WHERE themeid='$orig'");
    echo '<tr class="tablerow altbg2"><td align="center">' . $lang['themeupdate'] . '</td></tr>';
    message($lang['censorupdate'], false, '', '', 'cp2.php', false, false, false); // TODO
}

function processNewTheme() {
    global $lang, $table_themes, $db, $oToken;
    
    $oToken->isValidToken();

    $field_list = array (
        'bgcolor',
        'altbg1',
        'altbg2',
        'link',
        'bordercolor',
        'header',
        'headertext',
        'catcolor',
        'tabletext',
        'text',
        'cattext'
    );
    foreach ($field_list as $field) {
        $f = formVar($field . 'new');
        ${$f} = preg_replace('#^[0-9a-f]{3,6}$#i', '#$0', $f);
    }

    $db->query("INSERT INTO $table_themes (name, bgcolor, altbg1, altbg2, link, bordercolor, header, headertext, top, catcolor, tabletext, text, borderwidth, tablewidth, tablespace, font, fontsize, boardimg, imgdir, smdir, cattext) VALUES('$namenew', '$bgcolornew', '$altbg1new', '$altbg2new', '$linknew', '$bordercolornew', '$headernew', '$headertextnew', '$topnew', '$catcolornew', '$tabletextnew', '$textnew', '$borderwidthnew', '$tablewidthnew', '$tablespacenew', '$fnew', '$fsizenew', '$boardlogonew', '$imgdirnew', '$smdirnew', '$cattextnew')");
    echo '<tr class="tablerow altbg2"><td align="center">' . $lang['themeupdate'] . '</td></tr>';
    message($lang['censorupdate'], false, '', '', 'cp2.php', false, false, false); // TODO
}

?>
