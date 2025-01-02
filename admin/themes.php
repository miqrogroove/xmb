<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
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

$core = \XMB\Services\core();
$db = \XMB\Services\db();
$session = \XMB\Services\session();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['themes']);
$core->setCanonicalLink('admin/themes.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['themes'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$getThemeId = getInt('download');
if ($getThemeId) {
    $contents = [];
    $query = $db->query("SELECT * FROM " . $vars->tablepre . "themes WHERE themeid = $getThemeId");
    $themebits = $db->fetch_array($query);
    foreach($themebits as $key => $val) {
        if (! is_integer($key) && $key != 'themeid' && $key != 'dummy') {
            $contents[] = "$key=$val";
        }
    }
    $name = str_replace(' ', '+', $themebits['name']);
    header("Content-Type: application/x-ms-download");
    header("Content-Disposition: filename=\"$name-theme.xmb\"");
    echo implode("\r\n", $contents);
    exit();
}

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

$admin = new \XMB\admin($core, $db, $session, $sql, $template, $vars);

$single = '';
$single_str = getPhpInput('single', 'g');
$single_int = getInt('single');
$newtheme = $core->postedVar('newtheme');

if (noSubmit('themesubmit') && $single_str == '' && noSubmit('importsubmit')) {
    $template->themenonce = $token->create('Control Panel/Themes', 'mass-edit', X_NONCE_FORM_EXP);
    $body = $template->process('admin_themes_start.php');

    $themeMem = [0 => 0];
    $tq = $db->query("SELECT theme, count(theme) as cnt FROM " . $vars->tablepre . "members GROUP BY theme");
    while ($t = $db->fetch_array($tq)) {
        $themeMem[(int) $t['theme']] = (int) $t['cnt'];
    }

    $query = $db->query("SELECT name, themeid FROM " . $vars->tablepre . "themes ORDER BY name ASC");
    while ($themeinfo = $db->fetch_array($query)) {
        $themeid = $themeinfo['themeid'];
        if (! isset($themeMem[$themeid])) {
            $themeMem[$themeid] = 0;
        }

        if ($themeinfo['themeid'] == $vars->settings['theme']) {
            $template->members = $themeMem[$themeid] + $themeMem[0];
            $template->disable = 'disabled="disabled"';
        } else {
            $template->members = $themeMem[$themeid];
            $template->disable = '';
        }
        $template->themeinfo = $themeinfo;
        $body .= $template->process('admin_themes_row.php');
    }
    $body .= $template->process('admin_themes_end.php');
}

if (onSubmit('importsubmit') && isset($_FILES['themefile']['tmp_name'])) {
    $core->request_secure('Control Panel/Themes', 'mass-edit');
    if (!is_uploaded_file($_FILES['themefile']['tmp_name'])) {
        $core->error($lang['textthemeimportfail']);
    }
    $themebits = readFileAsINI($_FILES['themefile']['tmp_name']);
    $start = "INSERT INTO " . $vars->tablepre . "themes";

    $keysql = [];
    $valsql = [];
    foreach($themebits as $key => $val) {
        if ($key == 'themeid') {
            $val = '';
        } else if ($key == 'name') {
            $dbname = $db->escape($val);
        }
        $keysql[] = $db->escape($key);
        $valsql[] = "'" . $db->escape($val) . "'";
    }

    $keysql = implode(', ', $keysql);
    $valsql = implode(', ', $valsql);

    $query = $db->query("SELECT COUNT(themeid) FROM " . $vars->tablepre . "themes WHERE name = '$dbname'");
    if ((int) $db->result($query, 0) > 0) {
        $core->error($lang['theme_already_exists']);
    }

    $sql = "INSERT INTO " . $vars->tablepre . "themes ($keysql) VALUES ($valsql);";
    $query = $db->query($sql);

    if (! $query) {
        $core->error($lang['textthemeimportfail']);
    }
    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>'
        . '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>'
        . $lang['textthemeimportsuccess'] . '</td></tr></td></tr>';
} else if (onSubmit('themesubmit')) {
    $core->request_secure('Control Panel/Themes', 'mass-edit');
    $theme_delete = $core->postedArray('theme_delete', 'int');
    $theme_name = $core->postedArray('theme_name', word: 'javascript', quoteencode: true);

    $number_of_themes = (int) $db->result($db->query("SELECT count(themeid) FROM " . $vars->tablepre . "themes"), 0);

    if ($theme_delete && count($theme_delete) >= $number_of_themes) {
        $core->error($lang['delete_all_themes']);
    }

    foreach($theme_delete as $themeid) {
        if ($themeid != (int) $vars->settings['theme']) {
            $db->query("UPDATE " . $vars->tablepre . "members SET theme = 0 WHERE theme = $themeid");
            $db->query("UPDATE " . $vars->tablepre . "forums SET theme = 0 WHERE theme = $themeid");
            $db->query("DELETE FROM " . $vars->tablepre . "themes WHERE themeid = $themeid");
            unset($theme_name[$themeid]);
        }
    }

    foreach($theme_name as $themeid => $name) {
        $sql->setThemeName((int) $themeid, $name);
    }
    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>' . $lang['themeupdate'] . '</td></tr>';
}

if ($single_int > 0) {
    $template->token = $token->create('Control Panel/Themes', (string) $single_int, X_NONCE_FORM_EXP);
    $template->single_int = $single_int;

    $query = $db->query("SELECT * FROM " . $vars->tablepre . "themes WHERE themeid = $single_int");
    $template->themestuff = $db->fetch_array($query);
    $db->free_result($query);

    $body = $template->process('admin_themes_single.php');
} else if ($single_str == "anewtheme1") {
    $template->token = $token->create('Control Panel/Themes', 'New Theme', X_NONCE_FORM_EXP);

    $body = $template->process('admin_themes_new.php');
} else if ($single_str == "submit" && !$newtheme) {
    $orig = formInt('orig');
    $core->request_secure('Control Panel/Themes', (string) $orig);

    $namenew = $core->postedVar('namenew');
    $bgcolornew = $core->postedVar('bgcolornew');
    $altbg1new = $core->postedVar('altbg1new');
    $altbg2new = $core->postedVar('altbg2new');
    $linknew = $core->postedVar('linknew');
    $bordercolornew = $core->postedVar('bordercolornew');
    $headernew = $core->postedVar('headernew');
    $headertextnew = $core->postedVar('headertextnew');
    $topnew = $core->postedVar('topnew');
    $catcolornew = $core->postedVar('catcolornew');
    $cattextnew = $core->postedVar('cattextnew');
    $tabletextnew = $core->postedVar('tabletextnew');
    $textnew = $core->postedVar('textnew');
    $borderwidthnew = $core->postedVar('borderwidthnew');
    $tablewidthnew = $core->postedVar('tablewidthnew');
    $tablespacenew = $core->postedVar('tablespacenew');
    $fnew = $core->postedVar('fnew');
    $fsizenew = $core->postedVar('fsizenew');
    $boardlogonew = $core->postedVar('boardlogonew');
    $imgdirnew = $core->postedVar('imgdirnew');
    $admdirnew = $core->postedVar('admdirnew');
    $smdirnew = $core->postedVar('smdirnew');

    $db->query("UPDATE " . $vars->tablepre . "themes SET name='$namenew', bgcolor='$bgcolornew', altbg1='$altbg1new', altbg2='$altbg2new', link='$linknew', bordercolor='$bordercolornew', header='$headernew', headertext='$headertextnew', top='$topnew', catcolor='$catcolornew', tabletext='$tabletextnew', text='$textnew', borderwidth='$borderwidthnew', tablewidth='$tablewidthnew', tablespace='$tablespacenew', fontsize='$fsizenew', font='$fnew', boardimg='$boardlogonew', imgdir='$imgdirnew', smdir='$smdirnew', cattext='$cattextnew', admdir='$admdirnew', version = version + 1 WHERE themeid='$orig'");

    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>' . $lang['themeupdate'] . '</td></tr>';
} else if ($single_str == "submit" && $newtheme) {
    $core->request_secure('Control Panel/Themes', 'New Theme');

    $namenew = $core->postedVar('namenew');
    $bgcolornew = $core->postedVar('bgcolornew');
    $altbg1new = $core->postedVar('altbg1new');
    $altbg2new = $core->postedVar('altbg2new');
    $linknew = $core->postedVar('linknew');
    $bordercolornew = $core->postedVar('bordercolornew');
    $headernew = $core->postedVar('headernew');
    $headertextnew = $core->postedVar('headertextnew');
    $topnew = $core->postedVar('topnew');
    $catcolornew = $core->postedVar('catcolornew');
    $cattextnew = $core->postedVar('cattextnew');
    $tabletextnew = $core->postedVar('tabletextnew');
    $textnew = $core->postedVar('textnew');
    $borderwidthnew = $core->postedVar('borderwidthnew');
    $tablewidthnew = $core->postedVar('tablewidthnew');
    $tablespacenew = $core->postedVar('tablespacenew');
    $fnew = $core->postedVar('fnew');
    $fsizenew = $core->postedVar('fsizenew');
    $boardlogonew = $core->postedVar('boardlogonew');
    $imgdirnew = $core->postedVar('imgdirnew');
    $admdirnew = $core->postedVar('admdirnew');
    $smdirnew = $core->postedVar('smdirnew');

    $db->query("INSERT INTO " . $vars->tablepre . "themes (name, bgcolor, altbg1, altbg2, link, bordercolor, header, headertext, top, catcolor, tabletext, text, borderwidth, tablewidth, tablespace, font, fontsize, boardimg, imgdir, smdir, cattext, admdir) VALUES ('$namenew', '$bgcolornew', '$altbg1new', '$altbg2new', '$linknew', '$bordercolornew', '$headernew', '$headertextnew', '$topnew', '$catcolornew', '$tabletextnew', '$textnew', '$borderwidthnew', '$tablewidthnew', '$tablespacenew', '$fnew', '$fsizenew', '$boardlogonew', '$imgdirnew', '$smdirnew', '$cattextnew', '$admdirnew')");

    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>' . $lang['themeupdate'] . '</td></tr>';
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
