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
$settings = Services\settings();
$sql = Services\sql();
$template = Services\template();
$token = Services\token();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textipban']);
$core->setCanonicalLink('admin/ipban.php');

if ($settings->get('subject_in_title') == 'on') {
    $template->threadSubject = $vars->lang['textipban'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if ($settings->get('ip_banning') == 'on') {
    if (noSubmit('ipbansubmit') && noSubmit('ipbandisable')) {
        $template->token = $token->create('Control Panel/IP Banning', 'mass-edit', $vars::NONCE_FORM_EXP);
        
        $body = $template->process('admin_ipban_start.php');

        $query = $db->query("SELECT * FROM " . $vars->tablepre . "banned ORDER BY dateline");
        while ($ipaddress = $db->fetch_array($query)) {
            for ($i = 1; $i <= 4; ++$i) {
                $j = "ip" . $i;
                if ('-1' === $ipaddress[$j]) {
                    $ipaddress[$j] = "*";
                }
            }
            $adjStamp = $core->timeKludge((int) $ipaddress['dateline']);
            $template->ipdate = $core->printGmDate($adjStamp) . ' ' . $lang['textat'] . ' ' . gmdate($vars->timecode, $adjStamp);
            $template->theip = "$ipaddress[ip1].$ipaddress[ip2].$ipaddress[ip3].$ipaddress[ip4]";
            $template->id = $ipaddress['id'];

            $body .= $template->process('admin_ipban_row.php');
        }

        $template->warning = '';
        $template->onlineip = $vars->onlineip;
        $ips = explode(".", $vars->onlineip);
        if (count($ips) === 4) {
            $query = $db->query("SELECT id FROM " . $vars->tablepre . "banned WHERE (ip1='$ips[0]' OR ip1='-1') AND (ip2='$ips[1]' OR ip2='-1') AND (ip3='$ips[2]' OR ip3='-1') AND (ip4='$ips[3]' OR ip4='-1')");
            $result = $db->fetch_array($query);
            if ($result) {
                $template->warning = $lang['ipwarning'];
            }
            $db->free_result($query);
        }

        $body .= $template->process('admin_ipban_end.php');
    } elseif (onSubmit('ipbandisable')) {
        $core->request_secure('Control Panel/IP Banning', 'mass-edit');
        $settings->put('ip_banning', 'off');
        $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '"><td class="ctrtablerow">' . $lang['textipupdate'] . '</td></tr>';
    } else {
        $core->request_secure('Control Panel/IP Banning', 'mass-edit');
        $newip1 = getPhpInput('newip1');
        $newip2 = getPhpInput('newip2');
        $newip3 = getPhpInput('newip3');
        $newip4 = getPhpInput('newip4');
        $newip = [];
        $newip[] = (is_numeric($newip1) || $newip1 == '*') ? trim($newip1) : '0';
        $newip[] = (is_numeric($newip2) || $newip2 == '*') ? trim($newip2) : '0';
        $newip[] = (is_numeric($newip3) || $newip3 == '*') ? trim($newip3) : '0';
        $newip[] = (is_numeric($newip4) || $newip4 == '*') ? trim($newip4) : '0';
        $delete = $validate->postedArray('delete', 'int');

        if ($delete) {
            $dels = [];
            foreach ($delete as $id => $del) {
                if ($del == 1) {
                    $dels[] = $id;
                }
            }
            $sql->deleteIPBansByList($dels);
        }
        $status = $lang['textipupdate'];

        if ('0' !== $newip[0] || '0' !== $newip[1] || '0' !== $newip[2] || '0' !== $newip[3]) {
            $invalid = 0;
            for ($i = 0; $i <= 3 && ! $invalid; ++$i) {
                if ($newip[$i] == "*") {
                    $ip[$i+1] = -1;
                } elseif (intval($newip[$i]) >=0 && intval($newip[$i]) <= 255) {
                    $ip[$i+1] = intval($newip[$i]);
                } else {
                    $invalid = 1;
                }
            }

            if ($invalid) {
                $status = $lang['invalidip'];
            } else {
                if ('-1' === $ip[1] && '-1' === $ip[2] && '-1' === $ip[3] && '-1' === $ip[4]) {
                    $status = $lang['impossiblebanall'];
                } else {
                    $query = $db->query("SELECT id FROM " . $vars->tablepre . "banned WHERE (ip1='$ip[1]' OR ip1='-1') AND (ip2='$ip[2]' OR ip2='-1') AND (ip3='$ip[3]' OR ip3='-1') AND (ip4='$ip[4]' OR ip4='-1')");
                    $result = $db->fetch_array($query);
                    if ($result) {
                        $status = $lang['existingip'];
                    } else {
                        $query = $db->query("INSERT INTO " . $vars->tablepre . "banned (ip1, ip2, ip3, ip4, dateline) VALUES ('$ip[1]', '$ip[2]', '$ip[3]', '$ip[4]', " . $vars->onlinetime . ")");
                    }
                }
            }
        }
        $link = '</p><p><a href="' . $vars->full_url . 'admin/ipban.php">' . $lang['textipbanlink'] . '</a>';
        $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td><p>' . $status . $link . '</p></td></tr>';
    }
} else {
    if (noSubmit('ipbanenable')) {
        $template->token = $token->create('Control Panel/IP Banning', 'enable', $vars::NONCE_AYS_EXP);

        $body = $template->process('admin_ipban_enable.php');
    } else {
        $core->request_secure('Control Panel/IP Banning', 'enable');
        $settings->put('ip_banning', 'on');
        $link = '</p><p><a href="' . $vars->full_url . 'admin/ipban.php">' . $lang['textipbanlink'] . '</a>';
        $body = '<tr bgcolor="' . $vars->theme['altbg2'] . '" class="ctrtablerow"><td><p>' . $lang['textipupdate'] . $link . '</p></td></tr>';
    }
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
