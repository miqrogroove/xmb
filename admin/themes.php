<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

const ROOT = '../';
require ROOT . 'header.php';

$core = Services\core();
$db = Services\db();
$session = Services\session();
$settings = Services\settings();
$sql = Services\sql();
$template = Services\template();
$token = Services\token();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav('<a href="' . $vars->full_url . 'admin/themes.php">' . $lang['themes'] . '</a>');
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
    foreach ($themebits as $key => $val) {
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

$table = $template->process('admin_table.php');

$admin = new admin($core, $db, $session, $settings, $sql, $validate, $vars);

$single = '';
$single_str = getPhpInput('single', 'g');
$single_int = getInt('single');
$newtheme = $validate->postedVar('newtheme');

if (noSubmit('themesubmit') && $single_str == '' && noSubmit('importsubmit')) {
    $template->themenonce = $token->create('Control Panel/Themes', 'mass-edit', $vars::NONCE_FORM_EXP);
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
    if (! is_uploaded_file($_FILES['themefile']['tmp_name'])) {
        $core->error($lang['textthemeimportfail']);
    }
    $themebits = readFileAsINI($_FILES['themefile']['tmp_name']);
    $start = "INSERT INTO " . $vars->tablepre . "themes";

    $keysql = [];
    $valsql = [];
    foreach ($themebits as $key => $val) {
        if ($key == 'themeid') {
            $val = '';
        } elseif ($key == 'name') {
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
} elseif (onSubmit('themesubmit')) {
    $core->request_secure('Control Panel/Themes', 'mass-edit');
    $theme_delete = $validate->postedArray('theme_delete', 'int');
    $theme_name = $validate->postedArray('theme_name');

    $number_of_themes = (int) $db->result($db->query("SELECT COUNT(themeid) FROM " . $vars->tablepre . "themes"), 0);

    if ($theme_delete && count($theme_delete) >= $number_of_themes) {
        $core->error($lang['delete_all_themes']);
    }

    foreach ($theme_delete as $themeid) {
        if ($themeid != (int) $vars->settings['theme']) {
            $db->query("UPDATE " . $vars->tablepre . "members SET theme = 0 WHERE theme = $themeid");
            $db->query("UPDATE " . $vars->tablepre . "forums SET theme = 0 WHERE theme = $themeid");
            $db->query("DELETE FROM " . $vars->tablepre . "themes WHERE themeid = $themeid");
            unset($theme_name[$themeid]);
        }
    }

    foreach ($theme_name as $themeid => $name) {
        $sql->setThemeName((int) $themeid, $name);
    }
    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>' . $lang['themeupdate'] . '</td></tr>';
}

if ($single_int > 0) {
    $template->token = $token->create('Control Panel/Themes', (string) $single_int, $vars::NONCE_FORM_EXP);
    $template->single_int = $single_int;

    $query = $db->query("SELECT * FROM " . $vars->tablepre . "themes WHERE themeid = $single_int");
    $template->themestuff = $db->fetch_array($query);
    $db->free_result($query);

    $core->nav($template->themestuff['name']);

    $body = $template->process('admin_themes_single.php');
} elseif ($single_str == "bump") {
    $sql->raiseThemeVersions();
    $core->message($lang['themes_bump_done']);
} elseif ($single_str == "anewtheme1") {
    $template->token = $token->create('Control Panel/Themes', 'New Theme', $vars::NONCE_FORM_EXP);

    $body = $template->process('admin_themes_new.php');
} elseif ($single_str == "submit" && ! $newtheme) {
    $orig = formInt('orig');
    $core->request_secure('Control Panel/Themes', (string) $orig);

    $namenew = $validate->postedVar('namenew');
    $bgcolornew = $validate->postedVar('bgcolornew');
    $altbg1new = $validate->postedVar('altbg1new');
    $altbg2new = $validate->postedVar('altbg2new');
    $linknew = $validate->postedVar('linknew');
    $bordercolornew = $validate->postedVar('bordercolornew');
    $headernew = $validate->postedVar('headernew');
    $headertextnew = $validate->postedVar('headertextnew');
    $topnew = $validate->postedVar('topnew');
    $catcolornew = $validate->postedVar('catcolornew');
    $cattextnew = $validate->postedVar('cattextnew');
    $tabletextnew = $validate->postedVar('tabletextnew');
    $textnew = $validate->postedVar('textnew');
    $borderwidthnew = $validate->postedVar('borderwidthnew');
    $tablewidthnew = $validate->postedVar('tablewidthnew');
    $tablespacenew = $validate->postedVar('tablespacenew');
    $fnew = $validate->postedVar('fnew');
    $fsizenew = $validate->postedVar('fsizenew');
    $boardlogonew = $validate->postedVar('boardlogonew');
    $imgdirnew = $validate->postedVar('imgdirnew');
    $admdirnew = $validate->postedVar('admdirnew');
    $smdirnew = $validate->postedVar('smdirnew');

    $db->query("UPDATE " . $vars->tablepre . "themes SET name='$namenew', bgcolor='$bgcolornew', altbg1='$altbg1new', altbg2='$altbg2new', link='$linknew', bordercolor='$bordercolornew', header='$headernew', headertext='$headertextnew', top='$topnew', catcolor='$catcolornew', tabletext='$tabletextnew', text='$textnew', borderwidth='$borderwidthnew', tablewidth='$tablewidthnew', tablespace='$tablespacenew', fontsize='$fsizenew', font='$fnew', boardimg='$boardlogonew', imgdir='$imgdirnew', smdir='$smdirnew', cattext='$cattextnew', admdir='$admdirnew', version = version + 1 WHERE themeid='$orig'");

    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>' . $lang['themeupdate'] . '</td></tr>';
} elseif ($single_str == "submit" && $newtheme) {
    $core->request_secure('Control Panel/Themes', 'New Theme');

    $namenew = $validate->postedVar('namenew');
    $bgcolornew = $validate->postedVar('bgcolornew');
    $altbg1new = $validate->postedVar('altbg1new');
    $altbg2new = $validate->postedVar('altbg2new');
    $linknew = $validate->postedVar('linknew');
    $bordercolornew = $validate->postedVar('bordercolornew');
    $headernew = $validate->postedVar('headernew');
    $headertextnew = $validate->postedVar('headertextnew');
    $topnew = $validate->postedVar('topnew');
    $catcolornew = $validate->postedVar('catcolornew');
    $cattextnew = $validate->postedVar('cattextnew');
    $tabletextnew = $validate->postedVar('tabletextnew');
    $textnew = $validate->postedVar('textnew');
    $borderwidthnew = $validate->postedVar('borderwidthnew');
    $tablewidthnew = $validate->postedVar('tablewidthnew');
    $tablespacenew = $validate->postedVar('tablespacenew');
    $fnew = $validate->postedVar('fnew');
    $fsizenew = $validate->postedVar('fsizenew');
    $boardlogonew = $validate->postedVar('boardlogonew');
    $imgdirnew = $validate->postedVar('imgdirnew');
    $admdirnew = $validate->postedVar('admdirnew');
    $smdirnew = $validate->postedVar('smdirnew');

    $db->query("INSERT INTO " . $vars->tablepre . "themes (name, bgcolor, altbg1, altbg2, link, bordercolor, header, headertext, top, catcolor, tabletext, text, borderwidth, tablewidth, tablespace, font, fontsize, boardimg, imgdir, smdir, cattext, admdir) VALUES ('$namenew', '$bgcolornew', '$altbg1new', '$altbg2new', '$linknew', '$bordercolornew', '$headernew', '$headertextnew', '$topnew', '$catcolornew', '$tabletextnew', '$textnew', '$borderwidthnew', '$tablewidthnew', '$tablespacenew', '$fnew', '$fsizenew', '$boardlogonew', '$imgdirnew', '$smdirnew', '$cattextnew', '$admdirnew')");

    $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td>' . $lang['themeupdate'] . '</td></tr>';
}

$header = $template->process('header.php');

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
