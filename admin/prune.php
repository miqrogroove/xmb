<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-1
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

$attach = Services\attach();
$core = Services\core();
$db = Services\db();
$forums = Services\forums();
$sql = Services\sql();
$template = Services\template();
$token = Services\token();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textprune']);
$core->setCanonicalLink('admin/prune.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textprune'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

if (noSubmit('pruneSubmit')) {
    $template->token = $token->create('Control Panel/Prune', '', $vars::NONCE_FORM_EXP);
    $template->forumselect = $core->forumList('pruneFromList', multiple: true, allowall: false);
    $body = $template->process('admin_prune.php');
} else {
    $core->request_secure('Control Panel/Prune', '');
    $pruneByDate = $validate->postedArray('pruneByDate', keyType: 'string');
    $pruneByPosts = $validate->postedArray('pruneByPosts', keyType: 'string');
    $pruneFrom = getPhpInput('pruneFrom');
    $pruneFromList = $validate->postedArray('pruneFromList', 'int');
    $pruneFromFid = getPhpInput('pruneFromFid');
    $pruneType = $validate->postedArray(
        varname: 'pruneType',
        valueType: 'int',
        keyType: 'string',
    );

    $queryWhere = [];
    // let's check what to prune first
    switch ($pruneFrom) {
        case 'all':
            break;
        case 'list':
            $fs = [];
            foreach ($pruneFromList as $fid) {
                if ($fid > 0) {
                    $fs[] = $fid;
                }
            }
            $fs = array_unique($fs);
            if (count($fs) < 1) {
                $core->error($lang['nopruneforums']);
            }
            $queryWhere[] = 'fid IN ('.implode(',', $fs).')';
            break;
        case 'fid':
            $fs = [];
            $fids = explode(',', $pruneFromFid);
            foreach ($fids as $fid) {
                $fid = (int) $fid;
                if ($fid > 0) {
                    $fs[] = $fid;
                }
            }
            $fs = array_unique($fs);
            if (count($fs) < 1) {
                $core->error($lang['nopruneforums']);
            }
            $queryWhere[] = 'fid IN (' . implode(',', $fs) . ')';
            break;
        default:
            $core->error($lang['nopruneforums']);
    }

    $sign = '';
    if (isset($pruneByPosts['check']) && '1' === $pruneByPosts['check']) {
        switch ($pruneByPosts['type']) {
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

    if (isset($pruneByDate['check']) && '1' === $pruneByDate['check']) {
        switch ($pruneByDate['type']) {
            case 'less':
                $queryWhere[] = 'lastpost > "' . (time()-(24*3600*$pruneByDate['date'])) . '"';
                break;
            case 'is':
                $queryWhere[] = 'lastpost BETWEEN "' . (time()-(24*3600*($pruneByDate['date']-1))) . '" AND "' . (time()-(24*3600*$pruneByDate['date'])) . '"';
                break;
            case 'more':
            default:
                $queryWhere[] = 'lastpost < "' . (time()-(24*3600*$pruneByDate['date'])) . '"';
                break;
        }
    } elseif ($sign == '') {
        $core->error($lang['noprunelimit']);
    }

    $pruneClosed = (int) arrayCoalesce($pruneType, 'closed') === 1;
    $pruneNormal = (int) arrayCoalesce($pruneType, 'normal') === 1;
    $pruneTopped = (int) arrayCoalesce($pruneType, 'topped') === 1;
    $pruneGo = $pruneClosed || $pruneNormal || $pruneTopped;

    if (! $pruneGo) {
        $core->error($lang['noprunetype']);
    }

    if (! $pruneClosed) {
        $queryWhere[] = "closed != 'yes'";
    }

    if (! $pruneTopped) {
        $queryWhere[] = 'topped != 1';
    }

    if (! $pruneNormal) {
        $queryWhere[] = "(topped = 1 OR closed = 'yes')";
    }

    if (count($queryWhere) > 0) {
        $tids = [];
        $fids = [];
        $queryWhere = implode(' AND ', $queryWhere);
        $q = $db->query("SELECT tid, fid FROM " . $vars->tablepre . "threads WHERE ".$queryWhere);
        if ($db->num_rows($q) > 0) {
            while ($t = $db->fetch_array($q)) {
                $tids[] = (int) $t['tid'];
                $fids[] = (int) $t['fid'];
            }
            set_time_limit(30); // Potentially expensive operations coming up.
            $attach->deleteByThreads($tids); // Must delete attachments before posts!
            set_time_limit(30);
            $sql->deleteVotesByTID($tids);
            set_time_limit(30);
            $tids = implode(',', $tids);
            $db->query("DELETE FROM " . $vars->tablepre . "posts WHERE tid IN ($tids)");
            $db->query("DELETE FROM " . $vars->tablepre . "favorites WHERE tid IN ($tids)");
            $db->query("DELETE FROM " . $vars->tablepre . "threads WHERE tid IN ($tids)");

            // Update Forum Stats
            $fids = array_unique($fids);
            $fups = [];
            foreach ($fids as $fid) {
                $forum = $forums->getForum($fid);
                if ('sub' == $forum['type']) {
                    $fups[] = (int) $forum['fup'];
                }
            }
            $fids = array_unique(array_merge($fids, $fups));
            foreach ($fids as $fid) {
                $core->updateforumcount($fid);
            }
        }
    }
    $body = "<tr bgcolor='" . $vars->theme['altbg2'] . "' class='tablerow'><td align='center'>{$lang['forumpruned']}</td></tr>";
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
