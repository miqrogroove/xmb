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

define('X_SCRIPT', 'cp2.php');

require 'header.php';
require ROOT.'include/admin.inc.php';

header('X-Robots-Tag: noindex');

loadtemplates('error_nologinsession');
eval('$css = "'.template('css').'";');

$action = postedVar('action', '', FALSE, FALSE, FALSE, 'g');

if (X_ADMIN) {
    if ($action == 'templates' && onSubmit('download')) {
        $code = '';
        $templates = $db->query("SELECT * FROM ".X_PREFIX."templates ORDER BY name ASC");
        while($template = $db->fetch_array($templates)) {
            $template['template'] = trim($template['template']);
            $template['name'] = trim($template['name']);

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
    }

    $download = getInt('download');
    if ($action == "themes" && $download) {
        $contents = array();
        $query = $db->query("SELECT * FROM ".X_PREFIX."themes WHERE themeid='$download'");
        $themebits = $db->fetch_array($query);
        foreach($themebits as $key=>$val) {
            if (!is_integer($key) && $key != 'themeid' && $key != 'dummy') {
                $contents[] = $key.'='.$val;
            }
        }
        $name = str_replace(' ', '+', $themebits['name']);
        header("Content-Type: application/x-ms-download");
        header("Content-Disposition: filename=\"$name-theme.xmb\"");
        echo implode("\r\n", $contents);
        exit();
    }
    if ($action == "lang" && $download) {
        require('include/translation.inc.php');
        $devname = '';
        $contents = exportTranslation($download, $devname);
        if ($contents === FALSE) {
            error($lang['generic_missing']);
        }
        header("Content-Type: application/x-ms-download");
        header("Content-Disposition: filename=\"$devname.lang.php\"");
        echo $contents;
        exit();
    }
}

nav($lang['textcp']);

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

if ($action == 'restrictions') {
    if (noSubmit('restrictedsubmit')) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=restrictions">
        <table align="center" border="0" cellspacing="0" cellpadding="0" width="80%">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td><span class="smalltxt"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textdeleteques']?></font></strong></span></td>
        <td><span class="smalltxt"><strong><font color="<?php echo $cattext?>"><?php echo $lang['restrictedname']?></font></strong></span></td>
        <td><span class="smalltxt"><strong><font color="<?php echo $cattext?>">case-sensitive</font></strong></span></td>
        <td><span class="smalltxt"><strong><font color="<?php echo $cattext?>">partial-match</font></strong></span></td>
        </tr>
        <?php
        $query = $db->query("SELECT * FROM ".X_PREFIX."restricted ORDER BY id");
        while($restricted = $db->fetch_array($query)) {
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
            <td bgcolor="<?php echo $altbg2?>"><input type="checkbox" name="delete<?php echo $restricted['id']?>" value="<?php echo $restricted['id']?>" /></td>
            <td bgcolor="<?php echo $altbg2?>"><input type="text" size="30" name="name<?php echo $restricted['id']?>" value="<?php echo $restricted['name']?>" /></td>
            <td bgcolor="<?php echo $altbg2?>"><input type="checkbox" name="case<?php echo $restricted['id']?>" value="<?php echo $restricted['id']?>" <?php echo $case_check?> /></td>
            <td bgcolor="<?php echo $altbg2?>"><input type="checkbox" name="partial<?php echo $restricted['id']?>" value="<?php echo $restricted['id']?>" <?php echo $partial_check?> /></td>
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
        <td><input type="checkbox" name="newcase" value="1" /></td>
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
        $queryrestricted = $db->query("SELECT id FROM ".X_PREFIX."restricted");
        while($restricted = $db->fetch_array($queryrestricted)) {
            $name = postedVar('name'.$restricted['id'], '', FALSE, TRUE);
            $delete = getInt('delete'.$restricted['id'], 'p');
            $case = getInt('case'.$restricted['id'], 'p');
            $partial = getInt('partial'.$restricted['id'], 'p');
            if ($partial) {
                $partial = 1;
            }
            if ($case) {
                $case = 1;
            }
            if ($delete) {
                $db->query("DELETE FROM ".X_PREFIX."restricted WHERE id=$delete");
            } else {
                $db->query("UPDATE ".X_PREFIX."restricted SET name='$name', case_sensitivity='$case', partial='$partial' WHERE id=".$restricted['id']);
            }
        }

        $newname = postedVar('newname', '', FALSE, TRUE);
        $newcase = getInt('newcase', 'p');
        $newpartial = getInt('newpartial', 'p');
        if (!empty($newname)) {
            if ($newpartial) {
                $newpartial = 1;
            }
            if ($newcase) {
                $newcase = 1;
            }
            $db->query("INSERT INTO ".X_PREFIX."restricted (`name`, `case_sensitivity`, `partial`) VALUES ('$newname', '$newcase', '$newpartial')");
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['restrictedupdate'].'</td></tr>';
        redirect($full_url.'cp2.php?action=restrictions', 2);
    }
}

// Management for Translation Database
if ($action == 'lang') {
    if (noSubmit('importsubmit') And noSubmit('edit') And noSubmit('editsubmit') And noSubmit('detail') And noSubmit('deletesubmit')) {
        // Default screen: Language List, Options to Install, Uninstall, and Export.
        $langnonce = nonce_create('massedtrnsls');
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=lang" name="theme_main">
        <input type="hidden" name="token" value="<?php echo $langnonce; ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textdeleteques']?></font></strong></td>
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textlanguage']; ?></font></strong></td>
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['translation_name']; ?></font></strong></td>
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['numberusing']?></font></strong></td>
        </tr>
        <?php

        $query = $db->query("SELECT b.devname, b.langid, t.cdata, COUNT(m.uid) AS cnt "
                          . "FROM ".X_PREFIX."lang_base AS b "
                          . "LEFT JOIN ".X_PREFIX."lang_text AS t USING (langid) "
                          . "INNER JOIN ".X_PREFIX."lang_keys AS k USING (phraseid) "
                          . "LEFT JOIN ".X_PREFIX."members AS m ON m.langfile = b.devname "
                          . "WHERE k.langkey='language' "
                          . "GROUP BY b.langid, b.devname, t.cdata ORDER BY b.devname ASC");

        while($themeinfo = $db->fetch_array($query)) {
            $themeid = $themeinfo['langid'];
            $members = $themeinfo['cnt'];
            if ($themeinfo['devname'] == $langfile Or $themeinfo['devname'] == $SETTINGS['langfile']) {
                $disabledelete = ' disabled="disabled"';
            } else {
                $disabledelete = '';
            }

            ?>
            <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
            <td align="center"><input type="checkbox" name="lang_delete[]" value="<?php echo $themeinfo['langid']?>"<?php echo $disabledelete; ?> /></td>
            <td><input type="text" name="langname[<?php echo $themeinfo['langid']?>]" value="<?php echo $themeinfo['cdata']?>" readonly="readonly" /></td>
            <td>
            <input type="text" name="devname[<?php echo $themeinfo['langid']?>]" value="<?php echo $themeinfo['devname']?>" readonly="readonly" />
            <a href="cp2.php?action=lang&amp;detail=<?php echo $themeinfo['langid']?>">
            <?php echo $lang['textdetails']?></a>
            -
            <a href="cp2.php?action=lang&amp;download=<?php echo $themeinfo['langid']?>">
            <?php echo $lang['textdownload']?>
            </a>
            </td>
            <td><?php echo $members?></td>
            </tr>
            <?php
        }
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td colspan="4"><img src="./images/pixel.gif" alt="" /></td>
        </tr>
        <tr>
        <td colspan="4" bgcolor="<?php echo $altbg2?>" class="ctrtablerow"><input type="submit" name="deletesubmit" value="<?php echo $lang['textsubmitchanges']?>" class="submit" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        <br />


        <form method="post" action="cp2.php?action=lang">
        <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td align="center"><strong><font color="<?php echo $cattext?>"><?php echo "{$lang['textedit']} - $langfile"?></font></strong></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow">
        <?php
        $query = $db->query("SELECT * FROM ".X_PREFIX."lang_keys ORDER BY langkey");
        echo '<select name="phraseid"><option value="default">'.$lang['translation_select'].'</option>';
        while($template = $db->fetch_array($query)) {
            echo '<option value="'.$template['phraseid'].'">'.$template['langkey']."</option>\r\n";
        }
        echo '</select>&nbsp;&nbsp;';
        $db->free_result($query);
        ?>
        </td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow">
        <input type="submit" class="submit" name="edit" value="<?php echo $lang['textedit']?>" />&nbsp;
        </td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>


        <br />
        <form method="post" action="cp2.php?action=lang" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?php echo $langnonce; ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td colspan="2" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['translation_import']; ?></font></strong></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['generic_file']; ?></td>
        <td bgcolor="<?php echo $altbg2?>"><input name="themefile" type="file" /></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow" align="center" colspan="2"><input type="submit" class="submit" name="importsubmit" value="<?php echo $lang['translation_import']; ?>" /></td>
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

    if (onSubmit('importsubmit') && isset($_FILES['themefile']['tmp_name'])) { // Handle upload of new translation file.

        request_secure('massedtrnsls', '', X_NONCE_FORM_EXP, FALSE);

        // Retrieve uploaded file
        require('include/attach.inc.php');
        $filename = '';
        $filetype = '';
        $filesize = 0;
        $upload = get_attached_file('themefile', $filename, $filetype, $filesize, FALSE);
        if ($upload === FALSE) {
            $message = $lang['langimportfail'];
            if ($filetype != X_EMPTY_UPLOAD) {
                $message .= ' '.$attachmentErrors[$filetype];
            }
            error($message, FALSE);
        }
        unlink($_FILES['themefile']['tmp_name']);

        // Install uploaded file
        require('include/translation.inc.php');
        $result = installNewTranslation($upload);

        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>';
        if ($result) {
            echo $lang['langimportsuccess'];
        } else {
            echo $lang['langimportfail'];
        }
        echo '</td></tr>';
    }

    if (onSubmit('edit') && noSubmit('editsubmit')) {
        $phraseid = getInt('phraseid', 'r');
        $result = $db->query("SELECT * FROM ".X_PREFIX."lang_keys WHERE phraseid=$phraseid");
        if ($db->num_rows($result) == 0) {
            error($lang['generic_missing'], FALSE);
        }
        $row = $db->fetch_array($result);
        $langkey = $row['langkey'];

        $result = $db->query("SELECT t.cdata "
                           . "FROM ".X_PREFIX."lang_text AS t "
                           . "LEFT JOIN ".X_PREFIX."lang_base AS b USING (langid) "
                           . "WHERE t.phraseid=$phraseid AND b.devname='$langfile'");
        if ($db->num_rows($result) == 1) {
            $row = $db->fetch_array($result);
            $value = cdataOut($row['cdata']); //Escape for use in the form field.
        } else {
            $value = '';
        }

        $key = template_key('edphrz', $phraseid);
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=lang">
        <input type="hidden" name="token" value="<?php echo nonce_create($key); ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['translations']; ?></font></strong></td>
        </tr>
        <tr class="ctrtablerow" bgcolor="<?php echo $altbg2?>">
        <td><?php echo $lang['translation_phrase'].':'; ?>&nbsp;<strong><?php echo "$langkey ($langfile)"; ?></strong></td>
        </tr>
        <tr class="ctrtablerow" bgcolor="<?php echo $altbg1?>">
        <td><textarea cols="100" rows="15" name="templatenew">
<?php // Linefeed required here - Do not edit!
        echo $value;
        ?></textarea></td>
        </tr>
        <tr class="ctrtablerow" bgcolor="<?php echo $altbg2?>">
        <td>
         <input type="submit" name="editsubmit" class="submit" value="<?php echo $lang['textsubmitchanges']?>" />
         <input type="hidden" name="phraseid" value="<?php echo $phraseid; ?>">
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

    if (onSubmit('editsubmit')) {
        require('include/translation.inc.php');

        $phraseid = getInt('phraseid', 'p');
        $newvalue = postedVar('templatenew', '', FALSE); // HTML is always allowed in translations.

        request_secure('edphrz', $phraseid, X_NONCE_FORM_EXP, FALSE);

        if (!setLangValue($phraseid, $newvalue)) {
            error($lang['generic_missing'], FALSE);
        }

        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['translation_update'].'</td></tr>';
        redirect($full_url.'cp2.php?action=lang', 2, X_REDIRECT_JS);
    }

    if (onSubmit('detail')) {
        $langid = getInt('detail');

        $result = $db->query("SELECT devname FROM ".X_PREFIX."lang_base WHERE langid='$langid'");
        if ($db->num_rows($result) == 0) {
            error($lang['generic_missing'], FALSE);
        }
        $row = $db->fetch_array($result);
        $db->free_result($result);
        $devname = $row['devname'];

        $db->query("UPDATE ".X_PREFIX."members SET langfile='$devname' WHERE username='$xmbuser'");

        $query = "SELECT k.langkey, k.phraseid, COUNT(t.cdata) AS phrasecount "
               . "FROM ".X_PREFIX."lang_keys AS k "
               . "CROSS JOIN ".X_PREFIX."lang_base AS b "
               . "LEFT JOIN ".X_PREFIX."lang_text AS t USING (phraseid, langid) "
               . "WHERE b.langid=$langid "
               . "GROUP BY k.phraseid, k.langkey ORDER BY k.langkey";
        $query = $db->query($query);

        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['translation_phrase']; ?></font></strong></td>
        <td colspan="2" align="center"><strong><font color="<?php echo $cattext?>"><?php echo $devname; ?></font></strong></td>
        </tr>
        <?php

        while($row = $db->fetch_array($query)) {
            $langkey = $row['langkey'];

            ?>
            <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
            <td><?php echo $langkey; ?></td>
            <?php if ($row['phrasecount'] == 0) { ?>
            <td></td>
            <td><a href="cp2.php?action=lang&amp;edit=edit&amp;phraseid=<?php echo $row['phraseid']; ?>"><?php echo $lang['textnewcode']; ?></a></td>
            </tr>
            <?php } else { ?>
            <td><a href="cp2.php?action=lang&amp;edit=edit&amp;phraseid=<?php echo $row['phraseid']; ?>"><?php echo $lang['textedit']; ?></a></td>
            <td></td>
            </tr>
            <?php }
        }
        ?>
        </table>
        </td>
        </tr>
        </table>
        </td>
        </tr>
        <?php
    }

    if (onSubmit('deletesubmit')) {
        request_secure('massedtrnsls', '', X_NONCE_FORM_EXP, FALSE);
        $theme_delete = postedArray('lang_delete', 'int');
        $result = $db->query("SELECT langid FROM ".X_PREFIX."lang_base WHERE devname='$langfile' OR devname='{$SETTINGS['langfile']}'");
        $lockIDs = array();
        while($row = $db->fetch_array($result)) {
            $lockIDs[] = $row['langid'];
        }

        if ($theme_delete) {
            foreach($theme_delete as $deleteid) {
                if (!in_array($deleteid, $lockIDs)) {
                    $db->query("DELETE FROM ".X_PREFIX."lang_text WHERE langid=$deleteid");
                    $db->query("DELETE FROM ".X_PREFIX."lang_base WHERE langid=$deleteid");
                }
            }
            $db->query('OPTIMIZE TABLE '.X_PREFIX.'lang_text');
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['translation_delete'].'</td></tr>';
    }
}

if ($action == 'themes') {
    $single = '';
    $single_str = postedVar('single', '', FALSE, FALSE, FALSE, 'g');
    $single_int = getInt('single');
    $newtheme = postedVar('newtheme');

    $themenonce = nonce_create('massedthemes');

    if (noSubmit('themesubmit') && $single_str == '' && noSubmit('importsubmit')) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td>
        <form method="post" action="cp2.php?action=themes" name="theme_main">
        <input type="hidden" name="token" value="<?php echo $themenonce; ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td align="center"><strong><font color="<?php echo $cattext?>"><?php echo $lang['textdeleteques']?></font></strong></td>
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textthemename']?></font></strong></td>
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['numberusing']?></font></strong></td>
        </tr>
        <?php
        $themeMem = array(0=>0);
        $tq = $db->query("SELECT theme, count(theme) as cnt FROM ".X_PREFIX."members GROUP BY theme");
        while($t = $db->fetch_array($tq)) {
            $themeMem[((int)$t['theme'])] = $t['cnt'];
        }

        $query = $db->query("SELECT name, themeid FROM ".X_PREFIX."themes ORDER BY name ASC");
        while($themeinfo = $db->fetch_array($query)) {
            $themeid = $themeinfo['themeid'];
            if (!isset($themeMem[$themeid])) {
                $themeMem[$themeid] = 0;
            }

            if ($themeinfo['themeid'] == $SETTINGS['theme']) {
                $members = ($themeMem[$themeid]+$themeMem[0]);
                $disable = 'disabled="disabled"';
            } else {
                $members = $themeMem[$themeid];
                $disable = '';
            }

            ?>
            <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
            <td align="center"><input type="checkbox" name="theme_delete[]" value="<?php echo $themeinfo['themeid']?>" <?php echo $disable; ?> /></td>
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
        <input type="hidden" name="token" value="<?php echo $themenonce; ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="header">
        <td colspan="2"><?php echo $lang['textimporttheme']?></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['textthemefile']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input name="themefile" type="file" /></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow" align="center" colspan="2"><input type="submit" class="submit" name="importsubmit" value="<?php echo $lang['textimporttheme']; ?>" /></td>
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

    if (onSubmit('importsubmit') && isset($_FILES['themefile']['tmp_name'])) {
        request_secure('massedthemes', '', X_NONCE_FORM_EXP, FALSE);
        if (!is_uploaded_file($_FILES['themefile']['tmp_name'])) {
            error($lang['textthemeimportfail'], FALSE);
        }
        $themebits = readFileAsINI($_FILES['themefile']['tmp_name']);
        $start = "INSERT INTO ".X_PREFIX."themes";

        $keysql = array();
        $valsql = array();
        foreach($themebits as $key=>$val) {
            if ($key == 'themeid') {
                $val = '';
            } else if ($key == 'name') {
                $dbname = $db->escape($val);
            }
            $keysql[] = $db->escape($key);
            $valsql[] = "'".$db->escape($val)."'";
        }

        $keysql = implode(', ', $keysql);
        $valsql = implode(', ', $valsql);

        $query = $db->query("SELECT COUNT(themeid) FROM ".X_PREFIX."themes WHERE name='$dbname'");
        if ($db->result($query, 0) > 0) {
            error($lang['theme_already_exists'], false, '</td></tr></table></td></tr></table>');
        }

        $sql = "INSERT INTO ".X_PREFIX."themes ($keysql) VALUES ($valsql);";
        $query = $db->query($sql);

        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>';
        if (!$query) {
            echo $lang['textthemeimportfail'];
        } else {
            echo $lang['textthemeimportsuccess'];
        }
        echo '</td></tr>';
    } else if (onSubmit('themesubmit')) {
        request_secure('massedthemes', '', X_NONCE_FORM_EXP, FALSE);
        $theme_delete = postedArray('theme_delete', 'int');
        $theme_name = postedArray('theme_name', 'string', 'javascript', TRUE, TRUE, TRUE);

        $number_of_themes = $db->result($db->query("SELECT count(themeid) FROM ".X_PREFIX."themes"), 0);

        if ($theme_delete && count($theme_delete) >= $number_of_themes) {
            error($lang['delete_all_themes'], false, '</td></tr></table></td></tr></table>');
        }

        if ($theme_delete) {
            foreach($theme_delete as $themeid) {
                if ($themeid != $SETTINGS['theme']) {
                    $db->query("UPDATE ".X_PREFIX."members SET theme=0 WHERE theme='$themeid'");
                    $db->query("UPDATE ".X_PREFIX."forums SET theme=0 WHERE theme='$themeid'");
                    $db->query("DELETE FROM ".X_PREFIX."themes WHERE themeid='$themeid'");
                }
            }
        }

        foreach($theme_name as $themeid=>$name) {
            $db->query("UPDATE ".X_PREFIX."themes SET name='$name' WHERE themeid='$themeid'");
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['themeupdate'].'</td></tr>';
    }

    if ($single_int > 0) {
        $query = $db->query("SELECT * FROM ".X_PREFIX."themes WHERE themeid='$single_int'");
        $themestuff = $db->fetch_array($query);
        $db->free_result($query);
        $key = template_key('theme', $single_int);
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td>
        <form method="post" action="cp2.php?action=themes&amp;single=submit">
        <input type="hidden" name="token" value="<?php echo nonce_create($key); ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['texthemename']?></td>
        <td colspan="2"><input type="text" name="namenew" value="<?php echo $themestuff['name']?>" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textbgcolor']?></td>
        <td><input type="text" name="bgcolornew" value="<?php echo $themestuff['bgcolor']?>" /></td>
        <td bgcolor="<?php echo $themestuff['bgcolor']?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textaltbg1']?></td>
        <td><input type="text" name="altbg1new" value="<?php echo $themestuff['altbg1']?>" /></td>
        <td bgcolor="<?php echo $themestuff['altbg1']?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textaltbg2']?></td>
        <td><input type="text" name="altbg2new" value="<?php echo $themestuff['altbg2']?>" /></td>
        <td bgcolor="<?php echo $themestuff['altbg2']?>">&nbsp;</td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['textlink']?></td>
        <td><input type="text" name="linknew" value="<?php echo $themestuff['link']?>" /></td>
        <td bgcolor="<?php echo $themestuff['link']?>">&nbsp;</td>
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
        <td><?php echo $lang['imgdiradm']?></td>
        <td colspan="2"><input type="text"  value="<?php echo $themestuff['admdir']?>" name="admdirnew" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['smdir']?></td>
        <td colspan="2"><input type="text"  value="<?php echo $themestuff['smdir']?>" name="smdirnew" /></td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="ctrtablerow" colspan="3"><input type="submit" class="submit" value="<?php echo $lang['textsubmitchanges']?>" /><input type="hidden" name="orig" value="<?php echo $single_int?>" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        </td>
        </tr>
        <?php
    } else if ($single_str == "anewtheme1") {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=themes&amp;single=submit">
        <input type="hidden" name="token" value="<?php echo nonce_create('makenewtheme'); ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
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
        <td><?php echo $lang['imgdiradm']?></td>
        <td><input type="text" name="admdirnew" value="images/admin" /></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="tablerow">
        <td><?php echo $lang['smdir']?></td>
        <td><input type="text" name="smdirnew" value="images/smilies" /></td>
        </tr>
        <tr class="ctrtablerow">
        <td bgcolor="<?php echo $altbg2?>" colspan="2"><input class="submit" type="submit" value="<?php echo $lang['textsubmitchanges']?>" /><input type="hidden" name="newtheme" value="true" /></td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        </td>
        </tr>
        <?php
    } else if ($single_str == "submit" && !$newtheme) {
        $orig = formInt('orig');
        request_secure('theme', $orig, X_NONCE_FORM_EXP, FALSE);
        $namenew = postedVar('namenew');
        $bgcolornew = postedVar('bgcolornew');
        $altbg1new = postedVar('altbg1new');
        $altbg2new = postedVar('altbg2new');
        $linknew = postedVar('linknew');
        $bordercolornew = postedVar('bordercolornew');
        $headernew = postedVar('headernew');
        $headertextnew = postedVar('headertextnew');
        $topnew = postedVar('topnew');
        $catcolornew = postedVar('catcolornew');
        $cattextnew = postedVar('cattextnew');
        $tabletextnew = postedVar('tabletextnew');
        $textnew = postedVar('textnew');
        $borderwidthnew = postedVar('borderwidthnew');
        $tablewidthnew = postedVar('tablewidthnew');
        $tablespacenew = postedVar('tablespacenew');
        $fnew = postedVar('fnew');
        $fsizenew = postedVar('fsizenew');
        $boardlogonew = postedVar('boardlogonew');
        $imgdirnew = postedVar('imgdirnew');
        $admdirnew = postedVar('admdirnew');
        $smdirnew = postedVar('smdirnew');

        $db->query("UPDATE ".X_PREFIX."themes SET name='$namenew', bgcolor='$bgcolornew', altbg1='$altbg1new', altbg2='$altbg2new', link='$linknew', bordercolor='$bordercolornew', header='$headernew', headertext='$headertextnew', top='$topnew', catcolor='$catcolornew', tabletext='$tabletextnew', text='$textnew', borderwidth='$borderwidthnew', tablewidth='$tablewidthnew', tablespace='$tablespacenew', fontsize='$fsizenew', font='$fnew', boardimg='$boardlogonew', imgdir='$imgdirnew', smdir='$smdirnew', cattext='$cattextnew', admdir='$admdirnew' WHERE themeid='$orig'");
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['themeupdate'].'</td></tr>';
    } else if ($single_str == "submit" && $newtheme) {
        request_secure('makenewtheme', '', X_NONCE_FORM_EXP, FALSE);
        $namenew = postedVar('namenew');
        $bgcolornew = postedVar('bgcolornew');
        $altbg1new = postedVar('altbg1new');
        $altbg2new = postedVar('altbg2new');
        $linknew = postedVar('linknew');
        $bordercolornew = postedVar('bordercolornew');
        $headernew = postedVar('headernew');
        $headertextnew = postedVar('headertextnew');
        $topnew = postedVar('topnew');
        $catcolornew = postedVar('catcolornew');
        $cattextnew = postedVar('cattextnew');
        $tabletextnew = postedVar('tabletextnew');
        $textnew = postedVar('textnew');
        $borderwidthnew = postedVar('borderwidthnew');
        $tablewidthnew = postedVar('tablewidthnew');
        $tablespacenew = postedVar('tablespacenew');
        $fnew = postedVar('fnew');
        $fsizenew = postedVar('fsizenew');
        $boardlogonew = postedVar('boardlogonew');
        $imgdirnew = postedVar('imgdirnew');
        $admdirnew = postedVar('admdirnew');
        $smdirnew = postedVar('smdirnew');

        $db->query("INSERT INTO ".X_PREFIX."themes (name, bgcolor, altbg1, altbg2, link, bordercolor, header, headertext, top, catcolor, tabletext, text, borderwidth, tablewidth, tablespace, font, fontsize, boardimg, imgdir, smdir, cattext, admdir) VALUES('$namenew', '$bgcolornew', '$altbg1new', '$altbg2new', '$linknew', '$bordercolornew', '$headernew', '$headertextnew', '$topnew', '$catcolornew', '$tabletextnew', '$textnew', '$borderwidthnew', '$tablewidthnew', '$tablespacenew', '$fnew', '$fsizenew', '$boardlogonew', '$imgdirnew', '$smdirnew', '$cattextnew', '$admdirnew')");
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['themeupdate'].'</td></tr>';
    }
}

if ($action == "smilies") {
    if (noSubmit('smiliesubmit')) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=smilies">
        <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
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
        $query = $db->query("SELECT code, id, url FROM ".X_PREFIX."smilies WHERE type='smiley'");
        while($smilie = $db->fetch_array($query)) {
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
        <tr class="ctrtablerow">
        <td bgcolor="<?php echo $altbg1?>"><input type="checkbox" name="autoinsertsmilies" value="1" /></td>
        <td bgcolor="<?php echo $altbg1?>" colspan="3"><?php echo $lang['autoinsertsmilies']?> (<?php echo $smdir?>)?</td>
        </tr>
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
        $query = $db->query("SELECT * FROM ".X_PREFIX."smilies WHERE type='picon' ORDER BY id");
        while($smilie = $db->fetch_array($query)) {
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
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>" align="center"><input type="checkbox" name="autoinsertposticons" value="1" /></td>
        <td bgcolor="<?php echo $altbg1?>" colspan="3"><?php echo $lang['autoinsertposticons']?> (<?php echo $smdir?>)?</td>
        </tr>
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
        $smdelete = postedArray('smdelete', 'int');
        $smcode = postedArray('smcode', 'string', 'javascript', TRUE, TRUE, TRUE);
        $smurl = postedArray('smurl', 'string', 'javascript', TRUE, TRUE, TRUE);

        $newcode = postedVar('newcode');
        $newurl1 = postedVar('newurl1');
        $autoinsertsmilies = formInt('autoinsertsmilies');

        $pidelete = postedArray('pidelete', 'int');
        $piurl = postedArray('piurl', 'string', 'javascript', TRUE, TRUE, TRUE);

        $newurl2 = postedVar('newurl2');
        $autoinsertposticons = formInt('autoinsertposticons');

        if ($smcode) {
            foreach($smcode as $key=>$val) {
                if (count(array_keys($smcode, $val)) > 1) {
                    error($lang['smilieexists'], false, '</td></tr></table></td></tr></table><br />');
                }
            }
        }

        $querysmilie = $db->query("SELECT id FROM ".X_PREFIX."smilies WHERE type='smiley'");
        while($smilie = $db->fetch_array($querysmilie)) {
            $id = $smilie['id'];
            if (isset($smdelete[$id]) && $smdelete[$id] == 1) {
                $query = $db->query("DELETE FROM ".X_PREFIX."smilies WHERE id='$id'");
                continue;
            }
            $query = $db->query("UPDATE ".X_PREFIX."smilies SET code='$smcode[$id]', url='$smurl[$id]' WHERE id='$smilie[id]' AND type='smiley'");
        }

        if ($piurl) {
            foreach($piurl as $key=>$val) {
                if (count(array_keys($piurl, $val)) > 1) {
                    error($lang['piconexists'], false, '</td></tr></table></td></tr></table><br />');
                }
            }
        }

        $querysmilie = $db->query("SELECT id FROM ".X_PREFIX."smilies WHERE type='picon'");
        while($picon = $db->fetch_array($querysmilie)) {
            $id = $picon['id'];
            if (isset($pidelete[$id]) && $pidelete[$id] == 1) {
                $query = $db->query("DELETE FROM ".X_PREFIX."smilies WHERE id='$picon[id]'");
                continue;
            }
            $query = $db->query("UPDATE ".X_PREFIX."smilies SET url='$piurl[$id]' WHERE id='$picon[id]' AND type='picon'");
        }

        if ($newcode) {
            if ($db->result($db->query("SELECT count(id) FROM ".X_PREFIX."smilies WHERE code='$newcode'"), 0) > 0) {
                error($lang['smilieexists'], false, '</td></tr></table></td></tr></table><br />');
            }
            $query = $db->query("INSERT INTO ".X_PREFIX."smilies (type, code, url) VALUES ('smiley', '$newcode', '$newurl1')");
        }

        if ($autoinsertsmilies) {
            $smilies_count = $newsmilies_count = 0;
            $smiley_url = array();
            $smiley_code = array();
            $query = $db->query("SELECT * FROM ".X_PREFIX."smilies WHERE type = 'smiley'");
            while($smiley = $db->fetch_array($query)) {
                $smiley_url[] = $smiley['url'];
                $smiley_code[] = $smiley['code'];
            }
            $db->free_result($query);

            $dir = opendir($smdir);
            while($smiley = readdir($dir)) {
                if ($smiley != '.' && $smiley != '..' && (strpos($smiley, '.gif') || strpos($smiley, '.jpg') || strpos($smiley, '.jpeg') || strpos($smiley, '.bmp') || strpos($smiley, '.png'))) {
                    $newsmiley_url = $smiley;
                    $newsmiley_code = $smiley;
                    $newsmiley_code = str_replace(array('.gif','.jpg','.jpeg','.bmp','.png','_'), array('','','','','',' '), $newsmiley_code);
                    $newsmiley_code = ':' . $newsmiley_code . ':';
                    if (!in_array($newsmiley_url, $smiley_url) && !in_array($newsmiley_code, $smiley_code)) {
                        $query = $db->query("INSERT INTO ".X_PREFIX."smilies (type, code, url) VALUES ('smiley', '$newsmiley_code', '$newsmiley_url')");
                        $newsmilies_count++;
                    }
                    $smilies_count++;
                }
            }
            closedir($dir);
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$newsmilies_count.' / '.$smilies_count.' '.$lang['smiliesadded'].'</td></tr>';
        }

        if ($newurl2) {
            if ($db->result($db->query("SELECT count(id) FROM ".X_PREFIX."smilies WHERE url='$newurl2' AND type='picon'"), 0) > 0) {
                error($lang['piconexists'], false, '</td></tr></table></td></tr></table><br />');
            }
            $query = $db->query("INSERT INTO ".X_PREFIX."smilies (type, code, url) VALUES ('picon', '', '$newurl2')");
        }

        if ($autoinsertposticons) {
            $posticons_count = $newposticons_count = 0;
            $posticon_url = array();
            $query = $db->query("SELECT * FROM ".X_PREFIX."smilies WHERE type='picon'");
            while($picon = $db->fetch_array($query)) {
                $posticon_url[] = $picon['url'];
            }
            $db->free_result($query);

            $dir = opendir($smdir);
            while($picon = readdir($dir)) {
                if ($picon != '.' && $picon != '..' && (strpos($picon, '.gif') || strpos($picon, '.jpg') || strpos($picon, '.jpeg') || strpos($picon, '.bmp') || strpos($picon, '.png'))) {
                    $newposticon_url = $picon;
                    $newposticon_url = str_replace(' ', '%20', $newposticon_url);
                    if (!in_array($newposticon_url, $posticon_url)) {
                        $query = $db->query("INSERT INTO ".X_PREFIX."smilies (type, code, url) VALUES ('picon', '', '$newposticon_url')");
                        $newposticons_count++;
                    }
                    $posticons_count++;
                }
            }
            closedir($dir);
            echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$newposticons_count.' / '.$posticons_count.' '.$lang['posticonsadded'].'</td></tr>';
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['smilieupdate'].'</td></tr>';
    }
}

if ($action == 'censor') {
    if (noSubmit('censorsubmit')) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=censor">
        <table cellspacing="0" cellpadding="0" border="0" width="450" align="center">
        <tr>
        <td style="background-color: <?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td width="4%" align="center"><font style="color: <?php echo $cattext?>"><strong><?php echo $lang['textdeleteques']?></strong></font></td>
        <td align="left"><font style="color: <?php echo $cattext?>"><strong><?php echo $lang['textcensorfind']?></strong></font></td>
        <td align="left"><font style="color: <?php echo $cattext?>"><strong><?php echo $lang['textcensorreplace']?></strong></font></td>
        </tr>
        <?php
        $query = $db->query("SELECT * FROM ".X_PREFIX."words ORDER BY id");
        while($censor = $db->fetch_array($query)) {
            ?>
            <tr class="tablerow" bgcolor="<?php echo $altbg2?>">
            <td align="center"><input type="checkbox" name="delete<?php echo $censor['id']?>" value="<?php echo $censor['id']?>" /></td>
            <td align="left"><input type="text" size="20" name="find<?php echo $censor['id']?>" value="<?php echo $censor['find']?>" /></td>
            <td align="left"><input type="text" size="20" name="replace<?php echo $censor['id']?>" value="<?php echo $censor['replace1']?>" /></td>
            </tr>
            <?php
        }
        $db->free_result($query);
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
        <td colspan="3" class="ctrtablerow" bgcolor="<?php echo $altbg2?>"><input type="submit" class="submit" name="censorsubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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

    if (onSubmit('censorsubmit')) {
        $newfind = postedVar('newfind', 'javascript');
        $newreplace = postedVar('newreplace', 'javascript');
        $querycensor = $db->query("SELECT id FROM ".X_PREFIX."words");
        while($censor = $db->fetch_array($querycensor)) {
            $find = postedVar('find'.$censor['id']);
            $replace = postedVar('replace'.$censor['id']);
            $delete = formInt('delete'.$censor['id']);

            if ($delete) {
                $db->query("DELETE FROM ".X_PREFIX."words WHERE id=$delete");
            }

            if ($find) {
                $db->query("UPDATE ".X_PREFIX."words SET find='$find', replace1='$replace' WHERE id='$censor[id]'");
            }
        }
        $db->free_result($querycensor);

        if ($newfind) {
            $db->query("INSERT INTO ".X_PREFIX."words (find, replace1) VALUES ('$newfind', '$newreplace')");
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['censorupdate'].'</td></tr>';
    }
}

if ($action == "ranks") {
    if (noSubmit('rankssubmit')) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=ranks">
        <input type="hidden" name="token" value="<?php echo nonce_create('editusrranks'); ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="650" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
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
        $query = $db->query("SELECT * FROM ".X_PREFIX."ranks ORDER BY stars");
        while($rank = $db->fetch_array($query)) {
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
            <td class="tablerow" align="center"><input type="checkbox" name="delete[<?php echo $rank['id']?>]" value="<?php echo $rank['id']?>" <?php echo $staff_disable?> /></td>
            <td class="tablerow" align="left"><input type="text" name="title[<?php echo $rank['id']?>]" value="<?php echo attrOut($rank['title']); ?>" <?php echo $staff_disable?>/></td>
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
        request_secure('editusrranks', '', X_NONCE_FORM_EXP, FALSE);
        $id = postedArray('id', 'int');
        $delete = postedArray('delete', 'int');
        $title = postedArray('title', 'string', '', FALSE);
        $posts = postedArray('posts', 'int');
        $stars = postedArray('stars', 'int');
        $allowavatars = postedArray('allowavatars', 'yesno');
        $avaurl = postedArray('avaurl', 'string', 'javascript', TRUE, TRUE, TRUE);
        $newtitle = postedVar('newtitle', '', FALSE);
        $newposts = formInt('newposts');
        $newstars = formInt('newstars');
        $newallowavatars = formYesNo('newallowavatars');
        $newavaurl = postedVar('newavaurl', 'javascript', TRUE, TRUE, TRUE);

        $query = $db->query("SELECT * FROM ".X_PREFIX."ranks");
        $staffranks = array();
        while($ranks = $db->fetch_array($query)) {
            if ($ranks['title'] == 'Super Administrator' || $ranks['title'] == 'Administrator' || $ranks['title'] == 'Super Moderator' || $ranks['title'] == 'Moderator') {
                $title[$ranks['id']] = $ranks['title'];
                $posts[$ranks['id']] = 0;
                if ((int) $stars[$ranks['id']] == 0) {
                    $stars[$ranks['id']] = 1;
                }
                $staffranks[] = $ranks['title'];
            }
        }

        $i = 0;

        if ($delete) {
            $del = implode(', ', $delete);
            $db->query("DELETE FROM ".X_PREFIX."ranks WHERE id IN ($del)");
        }

        foreach($id as $key=>$val) {
            $posts[$key] = (in_array($title[$key], $staffranks)) ? (int) -1 : $posts[$key];
            $db->query("UPDATE ".X_PREFIX."ranks SET title='$title[$key]', posts='$posts[$key]', stars='$stars[$key]', allowavatars='$allowavatars[$key]', avatarrank='$avaurl[$key]' WHERE id='$key'");
        }

        if ($newtitle) {
            $db->query("INSERT INTO ".X_PREFIX."ranks (title, posts, stars, allowavatars, avatarrank) VALUES ('$newtitle', '$newposts', '$newstars', '$newallowavatars', '$newavaurl')");
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['rankingsupdate'].'</td></tr>';
    }
}

if ($action == "newsletter") {
    if (noSubmit('newslettersubmit')) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td>
        <form method="post" action="cp2.php?action=newsletter">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <input type="hidden" name="token" value="<?php echo nonce_create('sendnewslttr'); ?>" />
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
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
        request_secure('sendnewslttr', '', X_NONCE_FORM_EXP, FALSE);
        @set_time_limit(0);
        $newssubject = postedVar('newssubject');
        $newsmessage = postedVar('newsmessage');
        $sendvia = postedVar('sendvia', '', FALSE, FALSE);
        $to = postedVar('to', '', FALSE, FALSE);
        $newscopy = formYesNo('newscopy');
        $wait = formInt('wait');

        if ($newscopy != 'yes') {
            $tome = "AND NOT username='$xmbuser'";
        } else {
            $tome = "OR username='$xmbuser'";
        }

        if ($to == "all") {
            $query = $db->query("SELECT username, email FROM ".X_PREFIX."members WHERE newsletter='yes' $tome ORDER BY uid");
        } else if ($to == "staff") {
            $query = $db->query("SELECT username, email FROM ".X_PREFIX."members WHERE (status='Super Administrator' OR status='Administrator' OR status='Super Moderator' OR status='Moderator') $tome ORDER BY uid");
        } else if ($to == "admin") {
            $query = $db->query("SELECT username, email FROM ".X_PREFIX."members WHERE (status='Administrator' OR status = 'Super Administrator') $tome ORDER BY uid");
        } else if ($to == "supermod") {
            $query = $db->query("SELECT username, email FROM ".X_PREFIX."members WHERE status='Super moderator' $tome ORDER by uid");
        } else if ($to == "mod") {
            $query = $db->query("SELECT username, email FROM ".X_PREFIX."members WHERE status='Moderator' $tome ORDER BY uid");
        }

        if ($sendvia == "u2u") {
            while($memnews = $db->fetch_array($query)) {
                $db->escape_fast($memnews['username']);
                $db->query("INSERT INTO ".X_PREFIX."u2u (msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus) VALUES ('{$memnews['username']}', '$xmbuser', 'incoming', '{$memnews['username']}', 'Inbox', '$newssubject', '$newsmessage', '" . time() . "', 'no', 'yes')");
            }
            echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[newslettersubmit]</td></tr>";
        } else {
            $rawnewssubject = postedVar('newssubject', '', FALSE, FALSE);
            $rawnewsmessage = postedVar('newsmessage', '', FALSE, FALSE);
            $rawuser = htmlspecialchars_decode($self['username'], ENT_QUOTES);
            $rawbbname = htmlspecialchars_decode($bbname, ENT_NOQUOTES);
            $headers = array();
            $headers[] = smtpHeaderFrom($rawbbname, $adminemail);
            $headers[] = 'X-Mailer: PHP';
            $headers[] = 'X-AntiAbuse: Board servername - '.$cookiedomain;
            $headers[] = 'X-AntiAbuse: Username - '.$rawuser;
            $headers[] = 'Content-Type: text/plain; charset='.$charset;
            $headers = implode("\r\n", $headers);

            $i = 0;
            $total = 0;
            @ignore_user_abort(1);
            @set_time_limit(0);
            @ob_implicit_flush(1);

            while($memnews = $db->fetch_array($query)) {
                if ($i > 0 && $i == $wait) {
                    sleep(3);
                    $i = 0;
                } else {
                    if ($total % 250 == 0) {
                        error_log("XMB Notice: $total newsletter e-mails transmitted by $rawuser");
                    }
                    $i++;
                }

                $rawemail = htmlspecialchars_decode($memnews['email'], ENT_QUOTES);
                altMail($rawemail, '['.$rawbbname.'] '.$rawnewssubject, $rawnewsmessage, $headers);
                $total++;
            }
            error_log("XMB Notice: $total newsletter e-mails transmitted by $rawuser");
            echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[newslettersubmit] {$lang['textsent']} $total</td></tr>";
        }
    }
}

if ($action == "prune") {
    if (noSubmit('pruneSubmit')) {
        $forumselect = forumList('pruneFromList', true, false);
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=prune">
        <input type="hidden" name="token" value="<?php echo nonce_create('admmassprune'); ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="550">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%" style="vertical-align: top;">
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
        <input type="checkbox" name="pruneByDate[check]" value="1" checked="checked" />
        </td>
        <td>
        <select name="pruneByDate[type]">
        <option value="more"><?php echo $lang['prunemorethan']?></option>
        <option value="is"><?php echo $lang['pruneexactly']?></option>
        <option value="less"><?php echo $lang['prunelessthan']?></option>
        </select>
        <input type="text" name="pruneByDate[date]" value="100" /> <?php echo $lang['daysold']?>
        </td>
        </tr>
        <tr>
        <td>
        <input type="checkbox" name="pruneByPosts[check]" value="1" />
        </td>
        <td>
        <select name="pruneByPosts[type]">
        <option value="more"><?php echo $lang['prunemorethan']?></option>
        <option value="is"><?php echo $lang['pruneexactly']?></option>
        <option value="less"><?php echo $lang['prunelessthan']?></option>
        </select>
        <input type="text" name="pruneByPosts[posts]" value="10" /> <?php echo $lang['memposts']?>
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
        <input type="radio" name="pruneFrom" value="all" />
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
        <input type="radio" name="pruneFrom" value="fid" checked="checked" />
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
        request_secure('admmassprune', '', X_NONCE_FORM_EXP, FALSE);
        $pruneByDate = postedArray('pruneByDate');
        $pruneByPosts = postedArray('pruneByPosts');
        $pruneFrom = postedVar('pruneFrom', '', FALSE, FALSE);
        $pruneFromList = postedArray('pruneFromList', 'int');
        $pruneFromFid = postedVar('pruneFromFid', '', FALSE, FALSE);
        $pruneType = postedArray('pruneType', 'int');

        $queryWhere = array();
        // let's check what to prune first
        switch($pruneFrom) {
            case 'all':
                break;
            case 'list':
                $fs = array();
                foreach($pruneFromList as $fid) {
                    if ($fid > 0) {
                        $fs[] = $fid;
                    }
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
                foreach($fids as $fid) {
                    if ($fid > 0) {
                        $fs[] = $fid;
                    }
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

        $sign = '';
        if (isset($pruneByPosts['check']) && $pruneByPosts['check'] == "1") {
            switch($pruneByPosts['type']) {
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
            $queryWhere[] = 'replies '.$sign.' '.(int) ($pruneByPosts['posts']-1);
        }

        if (isset($pruneByDate['check']) && $pruneByDate['check'] == 1) {
            switch($pruneByDate['type']) {
                case 'less':
                    $queryWhere[] = 'lastpost >= '.(time()-(24*3600*$pruneByDate['date']));
                    break;
                case 'is':
                    $queryWhere[] = 'lastpost >= '.(time()-(24*3600*($pruneByDate['date']-1))).' AND lastpost <= '.(time()-(24*3600*($pruneByDate['date'])));
                    break;
                case 'more':
                default:
                    $queryWhere[] = 'lastpost <= '.(time()-(24*3600*$pruneByDate['date']));
                    break;
            }
        } else if ($sign == '') {
            $queryWhere[] = '1=0'; //Neither 'prune by' option was set, prune should abort.
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
            require('include/attach-admin.inc.php');
            $tids = array();
            $fids = array();
            $queryWhere = implode(' AND ', $queryWhere);
            $q = $db->query("SELECT tid, fid FROM ".X_PREFIX."threads WHERE ".$queryWhere);
            if ($db->num_rows($q) > 0) {
                while($t = $db->fetch_array($q)) {
                    $tids[] = $t['tid'];
                    $fids[] = $t['fid'];
                }
                $tids = implode(',', $tids);
                set_time_limit(30); // Potentially expensive operations coming up.
                deleteMultiThreadAttachments($tids); // Must delete attachments before posts!
                set_time_limit(30);
                $db->query("DELETE FROM ".X_PREFIX."posts WHERE tid IN ($tids)");
                $db->query("DELETE FROM ".X_PREFIX."favorites WHERE tid IN ($tids)");
                set_time_limit(30);

                $db->query("DELETE FROM d, r, v "
                         . "USING ".X_PREFIX."vote_desc AS d "
                         . "LEFT JOIN ".X_PREFIX."vote_results AS r ON r.vote_id = d.vote_id "
                         . "LEFT JOIN ".X_PREFIX."vote_voters AS v  ON v.vote_id = d.vote_id "
                         . "WHERE d.topic_id IN ($tids)");

                $db->query("DELETE FROM ".X_PREFIX."threads WHERE tid IN ($tids)");

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
            }
        } else {
            $db->query("TRUNCATE TABLE ".X_PREFIX."attachments");
            $db->query("TRUNCATE TABLE ".X_PREFIX."posts");
            $db->query("TRUNCATE TABLE ".X_PREFIX."favorites");
            $db->query("TRUNCATE TABLE ".X_PREFIX."vote_results");
            $db->query("TRUNCATE TABLE ".X_PREFIX."vote_voters");
            $db->query("TRUNCATE TABLE ".X_PREFIX."vote_desc");
            $db->query("TRUNCATE TABLE ".X_PREFIX."threads");
            $db->query("UPDATE ".X_PREFIX."members SET postnum=0");
            $db->query("UPDATE ".X_PREFIX."forums SET posts=0, threads=0, lastpost=''");
        }
        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[forumpruned]</td></tr>";
    }
}

if ($action == "templates") {
    if (noSubmit('edit') && noSubmit('editsubmit') && noSubmit('delete') && noSubmit('deletesubmit') && noSubmit('new') && noSubmit('restore') && noSubmit('restoresubmit')) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=templates">
        <table cellspacing="0" cellpadding="0" border="0" width="80%" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
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
        $query = $db->query("SELECT id, name FROM ".X_PREFIX."templates ORDER BY name");
        echo '<select name="tid"><option value="default">'.$lang['selecttemplate'].'</option>';
        while($template = $db->fetch_array($query)) {
            if (!empty($template['name'])) {
                echo '<option value="'.intval($template['id']).'">'.$template['name']."</option>\r\n";
            }
        }
        echo '</select>&nbsp;&nbsp;';
        $db->free_result($query);
        ?>
        </td>
        </tr>
        <tr>
        <td bgcolor="<?php echo $altbg2?>" class="tablerow">
        <input type="submit" class="submit" name="edit" value="<?php echo $lang['textedit']?>" />&nbsp;
        <input type="submit" class="submit" name="delete" value="<?php echo $lang['deletebutton']?>" />&nbsp;
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

    if (onSubmit('restore')) {
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=templates">
        <input type="hidden" name="token" value="<?php echo nonce_create('rsttemplates'); ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
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

    if (onSubmit('restoresubmit')) {
        request_secure('rsttemplates', '', X_NONCE_AYS_EXP, FALSE);
        if (!file_exists('./templates.xmb')) {
            error($lang['no_templates'], false, '</td></tr></table></td></tr></table><br />');
        }

        $templates = explode("|#*XMB TEMPLATE FILE*#|", file_get_contents(ROOT.'templates.xmb'));

        $db->query("TRUNCATE ".X_PREFIX."templates");

        $values = array();
        foreach($templates as $val) {
            $template = explode("|#*XMB TEMPLATE*#|", $val);
            $template[1] = isset($template[1]) ? addslashes(ltrim($template[1])) : '';
            $db->escape_fast($template[0]);
            $db->escape_fast($template[1]);
            $values[] = "('{$template[0]}', '{$template[1]}')";
        }
        unset($templates);
        if (count($values) > 0) {
            $values = implode(', ', $values);
            $db->query("INSERT INTO ".X_PREFIX."templates (name, template) VALUES $values");
        }

        $db->query("DELETE FROM ".X_PREFIX."templates WHERE name=''");
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['templatesrestoredone'].'</td></tr>';
        redirect($full_url.'cp2.php?action=templates', 2, X_REDIRECT_JS);
    }

    if (onSubmit('edit') && noSubmit('editsubmit')) {
        $tid = postedVar('tid', '', FALSE, FALSE);
        if ($tid == 'default') {
            error($lang['selecttemplate'], false, '</td></tr></table></td></tr></table><br />');
        }
        $tid = formInt('tid');
        $key = template_key('tmplt', $tid);
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=templates&amp;tid=<?php echo $tid?>">
        <input type="hidden" name="token" value="<?php echo nonce_create($key); ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr class="category">
        <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['templates']?></font></strong></td>
        </tr>
        <?php
        $query = $db->query("SELECT * FROM ".X_PREFIX."templates WHERE id=$tid ORDER BY name");
        $template = $db->fetch_array($query);
        $db->free_result($query);
        ?>
        <tr class="ctrtablerow" bgcolor="<?php echo $altbg2?>">
        <td><?php echo $lang['templatename']?>&nbsp;<strong><?php echo $template['name']; ?></strong></td>
        </tr>
        <tr class="ctrtablerow" bgcolor="<?php echo $altbg1?>">
        <td><textarea cols="100" rows="30" name="templatenew">
<?php // Linefeed required here - Do not edit!
        echo cdataOut(stripslashes($template['template']));
        ?></textarea></td>
        </tr>
        <tr class="ctrtablerow" bgcolor="<?php echo $altbg2?>">
        <td><input type="submit" name="editsubmit" class="submit" value="<?php echo $lang['textsubmitchanges']?>" /></strong></td>
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

    if (onSubmit('editsubmit')) {
        $tid = postedVar('tid', '', FALSE, FALSE, FALSE, 'g');
        request_secure('tmplt', $tid, X_NONCE_FORM_EXP, FALSE);
        $namenew = postedVar('namenew');
        //Templates are historically double-slashed.
        $templatenew = addslashes(postedVar('templatenew', '', FALSE, FALSE));
        $db->escape_fast($templatenew);

        if ($tid == 'new') {
            if (!$namenew) {
                error($lang['templateempty'], false, '</td></tr></table></td></tr></table><br />');
            } else {
                $check = $db->query("SELECT name FROM ".X_PREFIX."templates WHERE name='$namenew'");
                if ($db->num_rows($check) != 0) {
                    error($lang['templateexists'], false, '</td></tr></table></td></tr></table><br />');
                } else {
                    $db->query("INSERT INTO ".X_PREFIX."templates (name, template) VALUES ('$namenew', '$templatenew')");
                }
            }
        } else {
            $tid = getInt('tid');
            $db->query("UPDATE ".X_PREFIX."templates SET template='$templatenew' WHERE id=$tid");
        }
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['templatesupdate'].'</td></tr>';
        redirect($full_url.'cp2.php?action=templates', 2, X_REDIRECT_JS);
    }

    if (onSubmit('delete')) {
        if ($tid == 'default') {
            error($lang['selecttemplate'], false, '</td></tr></table></td></tr></table><br />');
        }
        $tid = getInt('tid', 'r');
        $key = template_key('dtmpl', $tid);
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=templates&amp;tid=<?php echo $tid?>">
        <input type="hidden" name="token" value="<?php echo nonce_create($key); ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category"><strong><font color="<?php echo $cattext?>"><?php echo $lang['templates']?></font></strong></td>
        </tr>
        <tr bgcolor="<?php echo $altbg1?>" class="ctrtablerow">
        <td><?php echo $lang['templatedelconfirm']?></td>
        </tr>
        <tr bgcolor="<?php echo $altbg2?>" class="ctrtablerow">
        <td><input type="submit" class="submit" name="deletesubmit" value="<?php echo $lang['textyes']?>" /></td>
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

    if (onSubmit('deletesubmit')) {
        $tid = getInt('tid', 'r');
        request_secure('dtmpl', $tid, X_NONCE_AYS_EXP, FALSE);
        $db->query("DELETE FROM ".X_PREFIX."templates WHERE id=$tid");
        echo '<tr bgcolor="'.$altbg2.'" class="ctrtablerow"><td>'.$lang['templatesdelete'].'</td></tr>';
        redirect($full_url.'cp2.php?action=templates', 2, X_REDIRECT_JS);
    }

    if (onSubmit('new')) {
        $newtemplatename = postedVar('newtemplatename', 'javascript', TRUE, FALSE, TRUE);
        $key = template_key('tmplt', 'new');
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=templates&amp;tid=new">
        <input type="hidden" name="token" value="<?php echo nonce_create($key); ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
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

if ($action == "attachments") {
    if (noSubmit('attachsubmit') && noSubmit('searchsubmit')) {
        $forumselect = forumList('forumprune', false, true);
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=attachments">
        <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
        <tr><td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category" colspan="2"><font color="<?php echo $cattext?>"><strong><?php echo $lang['textsearch']?></font></strong></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwherename']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="filename" size="30" /></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwhereauthor']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="author" size="40" /></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwhereforum']?></td>
        <td bgcolor="<?php echo $altbg2?>"><?php echo $forumselect?></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwheresizesmaller']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="sizeless" size="20" /></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwheresizegreater']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="sizemore" size="20" /></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwheredlcountsmaller']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="dlcountless" size="20" /></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwheredlcountgreater']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="dlcountmore" size="20" /></td>
        </tr>
        <tr class="tablerow">
        <td bgcolor="<?php echo $altbg1?>"><?php echo $lang['attachmanwheredaysold']?></td>
        <td bgcolor="<?php echo $altbg2?>"><input type="text" name="daysold" size="20" /></td>
        </tr>
        <tr class="ctrtablerow">
        <td bgcolor="<?php echo $altbg2?>" colspan="2"><input type="submit" name="searchsubmit" class="submit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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

    if (onSubmit('searchsubmit')) {
        require('include/attach.inc.php');
        $dblikefilename = $db->like_escape(postedVar('filename', '', FALSE, FALSE));
        $author = postedVar('author');
        $forumprune = postedVar('forumprune');
        $forumprune = $forumprune == 'all' ? '' : intval($forumprune);
        $sizeless = formInt('sizeless', FALSE);
        $sizemore = formInt('sizemore', FALSE);
        $dlcountless = formInt('dlcountless', FALSE);
        $dlcountmore = formInt('dlcountmore', FALSE);
        $daysold = formInt('daysold', FALSE);
        ?>
        <tr bgcolor="<?php echo $altbg2?>">
        <td align="center">
        <form method="post" action="cp2.php?action=attachments">
        <input type="hidden" name="token" value="<?php echo nonce_create('massedattach'); ?>" />
        <table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
        <tr>
        <td bgcolor="<?php echo $bordercolor?>">
        <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
        <tr>
        <td class="category" colspan="6"><font color="<?php echo $cattext?>"><strong><?php echo $lang['textattachsearchresults']?></strong></font></td>
        </tr>
        <tr>
        <td class="header" width="25%"><?php echo $lang['textfilename']?></td>
        <td class="header" width="19%"><?php echo $lang['textauthor']?></td>
        <td class="header" width="27%"><?php echo $lang['textinthread']?></td>
        <td class="header" width="10%"><?php echo $lang['textlocation']?></td>
        <td class="header" width="10%"><?php echo $lang['textfilesize']?></td>
        <td class="header" width="5%"><?php echo $lang['textdownloads']?></td>
        </tr>
        <?php
        $restriction = '';
        $orderby = '';

        if ($dblikefilename != '') {
            $restriction .= "AND a.filename LIKE '%$dblikefilename%' ";
        }

        if ($sizeless !== '') {
            $restriction .= "AND a.filesize < $sizeless ";
            $orderby = ' ORDER BY a.filesize DESC';
        }

        if ($sizemore !== '') {
            $restriction .= "AND a.filesize > $sizemore ";
            $orderby = ' ORDER BY a.filesize DESC';
        }

        if ($dlcountless !== '') {
            $restriction .= "AND a.downloads < $dlcountless ";
            $orderby = ' ORDER BY a.downloads DESC';
        }

        if ($dlcountmore !== '') {
            $restriction .= "AND a.downloads > $dlcountmore ";
            $orderby = ' ORDER BY a.downloads DESC ';
        }

        $restriction2 = 'WHERE b.parentid!=0 '.$restriction;

        if ($forumprune) {
            $restriction .= "AND t.fid=$forumprune ";
        }

        if ($daysold !== '') {
            $datethen = $onlinetime - (86400 * $daysold);
            $restriction .= "AND p.dateline <= $datethen ";
            $orderby = ' ORDER BY p.dateline ASC';
        }

        if ($author) {
            $restriction .= "AND p.author = '$author' ";
            $orderby = ' ORDER BY p.author ASC';
        }

        $restriction1 = 'WHERE a.parentid=0 '.$restriction;

        $query2 = $db->query("SELECT b.aid, b.pid, b.parentid, b.filename, b.filesize, b.downloads, b.subdir FROM ".X_PREFIX."attachments AS b "
                           . "LEFT JOIN ".X_PREFIX."attachments AS a ON a.aid=b.parentid $restriction2");

        $query = $db->query("SELECT a.aid, a.pid, a.filename, a.filesize, a.downloads, a.subdir, p.author, p.tid, t.fid, t.subject AS tsubject, f.name AS fname, m.username "
                          . "FROM ".X_PREFIX."attachments a "
                          . "LEFT JOIN ".X_PREFIX."posts p USING (pid) "
                          . "LEFT JOIN ".X_PREFIX."threads t ON t.tid=p.tid "
                          . "LEFT JOIN ".X_PREFIX."forums f ON f.fid=t.fid "
                          . "LEFT JOIN ".X_PREFIX."members m ON a.uid=m.uid $restriction1 $orderby");
        $diskpath = getFullPathFromSubdir('');
        if ($diskpath !== FALSE) {
            $diskpath = is_dir($diskpath);
        }
        while($attachment = $db->fetch_array($query)) {
            $attachsize = getSizeFormatted($attachment['filesize']);

            $attachment['tsubject'] = stripslashes($attachment['tsubject']); //old databases were double-slashed
            $attachment['fname'] = fnameOut($attachment['fname']);
            $attachment['filename'] = attrOut($attachment['filename'], 'javascript');
            $movelink = '';
            $newthumblink = '';
            if ($attachment['subdir'] == '') {
                $attachment['subdir'] = 'DB';
                if ($diskpath) {
                    $movelink = '<a href="cp2.php?action=movetodisk_attachment&amp;aid='.$attachment['aid'].'&amp;pid='.$attachment['pid'].'">'.$lang['movetodisk'].'</a>';
                }
            } else {
                $attachment['subdir'] = '/'.$attachment['subdir'].'/';
                if ($diskpath) {
                    $movelink = '<a href="cp2.php?action=movetodb_attachment&amp;aid='.$attachment['aid'].'&amp;pid='.$attachment['pid'].'">'.$lang['movetodb'].'</a>';
                }
            }
            if ($attachment['pid'] == 0) {
                $attachment['author'] = $attachment['username'];
                $downloadlink = '';
            } else {
                $downloadlink = '<a href="'.getAttachmentURL($attachment['aid'], $attachment['pid'], $attachment['filename']).'" target="_blank">'.$lang['textdownload'].'</a>';
                if (function_exists('imagecreatetruecolor')) {
                    $newthumblink = '<a href="cp2.php?action=regeneratethumbnail&amp;aid='.$attachment['aid'].'&amp;pid='.$attachment['pid'].'">'.$lang['regeneratethumbnail'].'</a>';
                }
            }
            $deletelink = '<a href="cp2.php?action=delete_attachment&amp;aid='.$attachment['aid'].'&amp;pid='.$attachment['pid'].'">'.$lang['deletebutton'].'</a>';
            ?>
            <tr>
            <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top"><input type="text" name="filename<?php echo $attachment['aid']?>" value="<?php echo $attachment['filename']?>">
                <br /><span class="smalltxt"><?php echo $downloadlink; ?> - <?php echo $movelink; ?> - <?php echo $newthumblink; ?> - <?php echo $deletelink; ?></span></td>
            <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top"><?php echo $attachment['author']?></td>
            <?php if ($attachment['pid'] == 0) { ?>
                <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top"></td>
            <?php } else { ?>
                <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top"><a href="viewthread.php?tid=<?php echo $attachment['tid']?>"><?php echo $attachment['tsubject']?></a><br /><span class="smalltxt"><?php echo $lang['textinforum']?> <a href="forumdisplay.php?fid=<?php echo $attachment['fid']?>"><?php echo $attachment['fname']?></a></span></td>
            <?php } ?>
            <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top" align="center"><?php echo $attachment['subdir']?></td>
            <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top" align="center"><?php echo $attachsize?></td>
            <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top" align="center"><?php echo $attachment['downloads']?></td>
            </tr>
            <?php
            if ($db->num_rows($query2) > 0) {
                $db->data_seek($query2, 0);
            }
            while($child = $db->fetch_array($query2)) {
                if ($child['parentid'] == $attachment['aid'] And substr($child['filename'], -10) == '-thumb.jpg') {
                    $attachsize = getSizeFormatted($child['filesize']);
                    $movelink = '';
                    if ($child['subdir'] == '') {
                        $child['subdir'] = 'DB';
                        if ($diskpath) {
                            $movelink = '<a href="cp2.php?action=movetodisk_attachment&amp;aid='.$child['aid'].'&amp;pid='.$child['pid'].'">'.$lang['movetodisk'].'</a>';
                        }
                    } else {
                        $child['subdir'] = '/'.$child['subdir'].'/';
                        if ($diskpath) {
                            $movelink = '<a href="cp2.php?action=movetodb_attachment&amp;aid='.$child['aid'].'&amp;pid='.$child['pid'].'">'.$lang['movetodb'].'</a>';
                        }
                    }
                    if ($child['pid'] == 0) {
                        $downloadlink = $lang['thumbnail'];
                    } else {
                        $downloadlink = '<a href="'.getAttachmentURL($child['aid'], $child['pid'], $child['filename']).'" target="_blank">'.$lang['thumbnail'].'</a>';
                    }
                    ?>
                        <tr>
                        <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top"><span class="smalltxt"><?php echo $downloadlink; ?> - <?php echo $movelink; ?></span></td>
                        <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top"></td>
                        <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top"></td>
                        <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top" align="center"><?php echo $child['subdir']?></td>
                        <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top" align="center"><?php echo $attachsize?></td>
                        <td bgcolor="<?php echo $altbg2?>" class="tablerow" valign="top" align="center"><?php echo $child['downloads']?></td>
                        </tr>
                    <?php
                }
            }
        }
        ?>
        <tr>
        <td align="center" class="tablerow" bgcolor="<?php echo $altbg2?>" colspan="6"><input class="submit" type="submit" name="deletesubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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

    if (onSubmit('deletesubmit')) {
        request_secure('massedattach', '', X_NONCE_FORM_EXP, FALSE);
        require('include/attach.inc.php');
        $filelist = array();
        foreach($_POST as $postedname => $rawvalue) {
            if (substr($postedname, 0, 8) == 'filename' And is_numeric($fileaid = substr($postedname, 8))) {
                $filelist[] = $fileaid;
            }
        }
        $filelist = implode(', ', $filelist);

        $query = $db->query("SELECT aid, pid, filename FROM ".X_PREFIX."attachments WHERE aid IN ($filelist)");
        while($attachment = $db->fetch_array($query)) {
            $afilename = "filename" . $attachment['aid'];
            $postedvalue = trim(postedVar($afilename, '', FALSE, FALSE));
            if ($attachment['filename'] != $postedvalue) {
                renameAttachment($attachment['aid'], $attachment['pid'], $postedvalue);
            }
        }
        echo "<tr bgcolor=\"$altbg2\" class=\"tablerow\"><td align=\"center\">$lang[textattachmentsupdate]</td></tr>";
    }
}

if ($action == "modlog") {
    nav($lang['textmodlogs']);
    $page = getInt('page');
    ?>
    <tr bgcolor="<?php echo $altbg2?>">
    <td align="center">
    <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
    <tr>
    <td bgcolor="<?php echo $bordercolor?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
    <tr class="category">
    <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textusername']; ?>:</font></strong></td>
    <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['texttime']; ?></font></strong></td>
    <td><strong><font color="<?php echo $cattext?>">URL:</font></strong></td>
    <td><strong><font color="<?php echo $cattext?>">Action:</font></strong></td>
    </tr>
    <?php
    $count = $db->result($db->query("SELECT count(fid) FROM ".X_PREFIX."logs WHERE NOT (fid='0' AND tid='0')"), 0);

    if (!$page) {
        $page = 1;
    }

    $old = (($page-1)*100);
    $current = ($page*100);

    $firstpage = '';
    $lastpage = '';
    $prevpage = '';
    $nextpage = '';
    $random_var = '';

    $query = $db->query("SELECT l.*, t.subject FROM ".X_PREFIX."logs l LEFT JOIN ".X_PREFIX."threads t ON l.tid=t.tid WHERE NOT (l.fid='0' AND l.tid='0') ORDER BY date ASC LIMIT $old, 100");
    $url = '';
    while($recordinfo = $db->fetch_array($query)) {
        $date = gmdate($dateformat, $recordinfo['date']);
        $time = gmdate($timecode, $recordinfo['date']);
        if ($recordinfo['tid'] > 0 && $recordinfo['action'] != 'delete' && trim($recordinfo['subject']) != '') {
            $url = "<a href=\"./viewthread.php?tid=$recordinfo[tid]\" target=\"_blank\">$recordinfo[subject]</a>";
        } else if ($recordinfo['action'] == 'delete') {
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
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <?php
}

if ($action == "cplog") {
    nav($lang['textcplogs']);
    $page = getInt('page');
    ?>
    <tr bgcolor="<?php echo $altbg2?>">
    <td align="center">
    <table cellspacing="0" cellpadding="0" border="0" width="500" align="center">
    <tr>
    <td bgcolor="<?php echo $bordercolor?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $tablespace?>" width="100%">
    <tr class="category">
    <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textusername']; ?>:</font></strong></td>
    <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['texttime']; ?></font></strong></td>
    <td><strong><font color="<?php echo $cattext?>">URL:</font></strong></td>
    <td><strong><font color="<?php echo $cattext?>">Action:</font></strong></td>
    <td><strong><font color="<?php echo $cattext?>"><?php echo $lang['textip']; ?>:</font></strong></td>
    </tr>
    <?php
    $count = $db->result($db->query("SELECT count(fid) FROM ".X_PREFIX."logs WHERE (fid='0' AND tid='0')"), 0);

    if (!$page) {
        $page = 1;
    }

    $old = (($page-1)*100);
    $current = ($page*100);

    $firstpage = '';
    $lastpage = '';
    $prevpage = '';
    $nextpage = '';
    $random_var = '';

    $query = $db->query("SELECT l.*, t.subject FROM ".X_PREFIX."logs l LEFT JOIN ".X_PREFIX."threads t ON l.tid=t.tid WHERE (l.fid='0' AND l.tid='0') ORDER BY date ASC LIMIT $old, 100");
    $url = '';
    while($recordinfo = $db->fetch_array($query)) {
        $date = gmdate($dateformat, $recordinfo['date']);
        $time = gmdate($timecode, $recordinfo['date']);
        $action = explode('|#|', $recordinfo['action']);
        if (strpos($action[1], '/') === false) {
            $recordinfo['action'] = $action[1];
            $url = '&nbsp';
        } else {
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
    </td>
    </tr>
    </table>
    </td>
    </tr>
    <?php
}

if ($action == "delete_attachment") {
    $aid = getInt('aid');
    $pid = getInt('pid');
    if (noSubmit('yessubmit')) {
        $key = template_key('delat', $aid);
        ?>
        <tr bgcolor="<?php echo $altbg2; ?>" class="ctrtablerow"><td>Are you sure you want to delete this attachment?<br />
        <form action="cp2.php?action=delete_attachment&amp;aid=<?php echo $aid; ?>&amp;pid=<?php echo $pid; ?>" method="post">
          <input type="hidden" name="token" value="<?php echo nonce_create($key); ?>" />
          <input type="submit" name="yessubmit" value="<?php echo $lang['textyes']; ?>" /> -
          <input type="submit" name="yessubmit" value="<?php echo $lang['textno']; ?>" />
        </form></td></tr>
        <?php
    } elseif ($lang['textyes'] == $yessubmit) {
        request_secure('delat', $aid, X_NONCE_AYS_EXP, FALSE);
        require('include/attach.inc.php');
        deleteAttachment($aid, $pid);
        echo "<p align=\"center\">Deleted ...</br>";
    }
}

if ($action == "movetodb_attachment") {
    require('include/attach-admin.inc.php');
    $aid = getInt('aid');
    $pid = getInt('pid');
    moveAttachmentToDB($aid, $pid);
    echo "<p align=\"center\">Moved ...</br>";
}

if ($action == "movetodisk_attachment") {
    require('include/attach-admin.inc.php');
    $aid = getInt('aid');
    $pid = getInt('pid');
    moveAttachmentToDisk($aid, $pid);
    echo "<p align=\"center\">Moved ...</br>";
}

if ($action == "regeneratethumbnail") {
    require('include/attach-admin.inc.php');
    $aid = getInt('aid');
    $pid = getInt('pid');
    $result = regenerateThumbnail($aid, $pid);
    if ($result < 0) {
        $msg = $attachmentErrors[$result];
    } elseif (FALSE === $result) {
        $msg = $lang['error'];
    } else {
        $msg = $lang['tool_completed'];
    }

    echo "<p align=\"center\">$msg</br>";
}

echo '</table></td></tr></table>';
end_time();
eval('echo "'.template('footer').'";');
?>
