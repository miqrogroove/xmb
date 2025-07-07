<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
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
$sql = Services\sql();
$template = Services\template();
$token = Services\token();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['smilies']);
$core->setCanonicalLink('admin/smilies.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['smilies'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (noSubmit('smiliesubmit')) {
    $template->token = $token->create('Control Panel/Smilies', 'mass-edit', $vars::NONCE_FORM_EXP);
    $body = $template->process('admin_smilies_start.php');

    $rows = $sql->getSmilies();
    foreach ($rows as $template->smilie) {
        $body .= $template->process('admin_smilies_srow.php');
    }

    $body .= $template->process('admin_smilies_middle.php');

    $rows = $sql->getPostIcons();
    foreach ($rows as $template->smilie) {
        $body .= $template->process('admin_smilies_prow.php');
    }

    $body .= $template->process('admin_smilies_end.php');
} else {
    $core->request_secure('Control Panel/Smilies', 'mass-edit');

    $smdelete = $validate->postedArray('smdelete', 'int');
    $smcode = $validate->postedArray('smcode');
    $smurl = $validate->postedArray('smurl', word: 'javascript');

    $newcode = $validate->postedVar('newcode');
    $newurl1 = $validate->postedVar('newurl1');
    $autoinsertsmilies = formInt('autoinsertsmilies');

    $pidelete = $validate->postedArray('pidelete', 'int');
    $piurl = $validate->postedArray('piurl', word: 'javascript');

    $newurl2 = $validate->postedVar('newurl2');
    $autoinsertposticons = formInt('autoinsertposticons');

    if ($smcode) {
        foreach ($smcode as $val) {
            if (count(array_keys($smcode, $val)) > 1) {
                $core->error($lang['smilieexists']);
            }
        }
    }

    $allsmilies = $sql->getSmilies();
    foreach ($allsmilies as $smilie) {
        $id = (int) $smilie['id'];
        if (isset($smdelete[$id]) && $smdelete[$id] == 1) {
            $query = $db->query("DELETE FROM " . $vars->tablepre . "smilies WHERE id = $id");
        } elseif ($smcode[$id] !== $smilie['code'] || $smurl[$id] !== $smilie['url']) {
            $query = $db->query("UPDATE " . $vars->tablepre . "smilies SET code = '$smcode[$id]', url = '$smurl[$id]' WHERE id = $id AND type = 'smiley'");
        }
    }

    if ($piurl) {
        foreach ($piurl as $val) {
            if (count(array_keys($piurl, $val)) > 1) {
                $core->error($lang['piconexists']);
            }
        }
    }

    $allicons = $sql->getPostIcons();
    foreach ($allicons as $picon) {
        $id = (int) $picon['id'];
        if (isset($pidelete[$id]) && $pidelete[$id] == 1) {
            $query = $db->query("DELETE FROM " . $vars->tablepre . "smilies WHERE id='$picon[id]'");
        } elseif ($piurl[$id] !== $picon['url']) {
            $query = $db->query("UPDATE " . $vars->tablepre . "smilies SET url = '$piurl[$id]' WHERE id = $id AND type = 'picon'");
        }
    }

    if ($newcode) {
        if ((int) $db->result($db->query("SELECT count(id) FROM " . $vars->tablepre . "smilies WHERE code = '$newcode'"), 0) > 0) {
            $core->error($lang['smilieexists']);
        }
        $query = $db->query("INSERT INTO " . $vars->tablepre . "smilies (type, code, url) VALUES ('smiley', '$newcode', '$newurl1')");
    }

    $body = '';

    if ($autoinsertsmilies) {
        $smilies_count = $newsmilies_count = 0;
        $smiley_url = [];
        $smiley_code = [];
        foreach ($allsmilies as $smiley) {
            $smiley_url[] = $smiley['url'];
            $smiley_code[] = $smiley['code'];
        }

        $dir = opendir($smdir);
        while ($smiley = readdir($dir)) {
            if ($smiley != '.' && $smiley != '..' && (strpos($smiley, '.gif') || strpos($smiley, '.jpg') || strpos($smiley, '.jpeg') || strpos($smiley, '.bmp') || strpos($smiley, '.png'))) {
                $newsmiley_url = $smiley;
                $newsmiley_code = $smiley;
                $newsmiley_code = str_replace(array('.gif','.jpg','.jpeg','.bmp','.png','_'), array('','','','','',' '), $newsmiley_code);
                $newsmiley_code = ':' . $newsmiley_code . ':';
                if (!in_array($newsmiley_url, $smiley_url) && !in_array($newsmiley_code, $smiley_code)) {
                    $query = $db->query("INSERT INTO " . $vars->tablepre . "smilies (type, code, url) VALUES ('smiley', '$newsmiley_code', '$newsmiley_url')");
                    $newsmilies_count++;
                }
                $smilies_count++;
            }
        }
        closedir($dir);
        $body .= '<tr bgcolor="'.$vars->theme['altbg2'].'" class="ctrtablerow"><td>'.$newsmilies_count.' / '.$smilies_count.' '.$lang['smiliesadded'].'</td></tr>';
    }

    if ($newurl2) {
        if ((int) $db->result($db->query("SELECT count(id) FROM " . $vars->tablepre . "smilies WHERE url='$newurl2' AND type='picon'"), 0) > 0) {
            $core->error($lang['piconexists']);
        }
        $query = $db->query("INSERT INTO " . $vars->tablepre . "smilies (type, code, url) VALUES ('picon', '', '$newurl2')");
    }

    if ($autoinsertposticons) {
        $posticons_count = $newposticons_count = 0;
        $posticon_url = [];
        foreach ($allicons as $picon) {
            $posticon_url[] = $picon['url'];
        }

        $dir = opendir($smdir);
        while ($picon = readdir($dir)) {
            if ($picon != '.' && $picon != '..' && (strpos($picon, '.gif') || strpos($picon, '.jpg') || strpos($picon, '.jpeg') || strpos($picon, '.bmp') || strpos($picon, '.png'))) {
                $newposticon_url = $picon;
                $newposticon_url = str_replace(' ', '%20', $newposticon_url);
                if (!in_array($newposticon_url, $posticon_url)) {
                    $query = $db->query("INSERT INTO " . $vars->tablepre . "smilies (type, code, url) VALUES ('picon', '', '$newposticon_url')");
                    $newposticons_count++;
                }
                $posticons_count++;
            }
        }
        closedir($dir);
        $body .= '<tr bgcolor="'.$vars->theme['altbg2'].'" class="ctrtablerow"><td>'.$newposticons_count.' / '.$posticons_count.' '.$lang['posticonsadded'].'</td></tr>';
    }
    $body .= '<tr bgcolor="'.$vars->theme['altbg2'].'" class="ctrtablerow"><td>'.$lang['smilieupdate'].'</td></tr>';
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
