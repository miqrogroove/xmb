<?php

/**
 * eXtreme Message Board
 * XMB 1.10.01
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
$template = Services\template();
$token = Services\token();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;

header('X-Robots-Tag: noindex');

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textattachman']);
$core->setCanonicalLink('admin/attachments.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textattachman'] . ' - ';
}

$core->assertAdminOnly();

$auditaction = $_SERVER['REQUEST_URI'];
$aapos = strpos($auditaction, "?");
if ($aapos !== false) {
    $auditaction = substr($auditaction, $aapos + 1);
    $pidpos = strpos($auditaction, '&pid');
    if ($pidpos !== false && strpos($auditaction, 'regenerate') !== false) {
        // Remove the PID to avoid overflowing the action column.
        $auditaction = substr($auditaction, 0, $pidpos - strlen($auditaction));
    }
}
$auditaction = $vars->onlineip . '|#|' . $auditaction;
$core->audit($vars->self['username'], $auditaction);

$header = $template->process('header.php');

$table = $template->process('admin_table.php');

$action = getPhpInput('action', 'g');

if ($action == '' && noSubmit('attachsubmit') && noSubmit('searchsubmit')) {
    $template->forumselect = $core->forumList('forumprune');
    $body = $template->process('admin_attachments_search.php');
}

if ($action == '' && onSubmit('searchsubmit')) {
    $template->token = $token->create('Control Panel/Attachments', 'mass-edit', $vars::NONCE_FORM_EXP);
    $dblikefilename = $db->like_escape($validate->postedVar('filename', dbescape: false));
    $author = $validate->postedVar('author');
    $forumprune = getPhpInput('forumprune');
    $forumprune = $forumprune == 'all' ? '' : intval($forumprune);
    $sizeless = formInt('sizeless');
    $sizemore = formInt('sizemore', setZero: false);
    $dlcountless = formInt('dlcountless');
    $dlcountmore = formInt('dlcountmore', setZero: false);
    $daysold = formInt('daysold', setZero: false);

    $body = $template->process('admin_attachments_result_start.php');

    $restriction = '';
    $orderby = '';

    if ($dblikefilename != '') {
        $restriction .= "AND a.filename LIKE '%$dblikefilename%' ";
    }

    if ($sizeless > 0) {
        $restriction .= "AND a.filesize < $sizeless ";
        $orderby = ' ORDER BY a.filesize DESC';
    }

    if ($sizemore !== null) {
        $restriction .= "AND a.filesize > $sizemore ";
        $orderby = ' ORDER BY a.filesize DESC';
    }

    if ($dlcountless > 0) {
        $restriction .= "AND a.downloads < $dlcountless ";
        $orderby = ' ORDER BY a.downloads DESC';
    }

    if ($dlcountmore !== null) {
        $restriction .= "AND a.downloads > $dlcountmore ";
        $orderby = ' ORDER BY a.downloads DESC ';
    }

    $restriction2 = 'WHERE b.parentid!=0 '.$restriction;

    if ($forumprune) {
        $restriction .= "AND t.fid=$forumprune ";
    }

    if ($daysold !== null) {
        $datethen = $vars->onlinetime - (86400 * $daysold);
        $restriction .= "AND p.dateline <= $datethen ";
        $orderby = ' ORDER BY p.dateline ASC';
    }

    if ($author) {
        $restriction .= "AND p.author = '$author' ";
        $orderby = ' ORDER BY p.author ASC';
    }

    $restriction1 = 'WHERE a.parentid=0 '.$restriction;

    $query2 = $db->query("SELECT b.aid, b.pid, b.parentid, b.filename, b.filesize, b.downloads, b.subdir FROM " . $vars->tablepre . "attachments AS b "
                       . "LEFT JOIN " . $vars->tablepre . "attachments AS a ON a.aid=b.parentid $restriction2");

    $query = $db->query("SELECT a.aid, a.pid, a.filename, a.filesize, a.downloads, a.subdir, p.author, p.tid, t.fid, t.subject AS tsubject, f.name AS fname, m.username "
                      . "FROM " . $vars->tablepre . "attachments a "
                      . "LEFT JOIN " . $vars->tablepre . "posts p USING (pid) "
                      . "LEFT JOIN " . $vars->tablepre . "threads t ON t.tid=p.tid "
                      . "LEFT JOIN " . $vars->tablepre . "forums f ON f.fid=t.fid "
                      . "LEFT JOIN " . $vars->tablepre . "members m ON a.uid=m.uid $restriction1 $orderby");
    $diskpath = $attach->getFullPathFromSubdir('');
    if ($diskpath !== false) {
        $diskpath = is_dir($diskpath);
    }
    while ($attachment = $db->fetch_array($query)) {
        $template->attachsize = $attach->getSizeFormatted($attachment['filesize']);

        $attachment['fname'] = fnameOut($attachment['fname'] ?? '');
        $template->movelink = '';
        $template->newthumblink = '';
        if ($attachment['subdir'] == '') {
            $attachment['subdir'] = 'DB';
            if ($diskpath) {
                $template->movelink = "<a href='" . $vars->full_url . "admin/attachments.php?action=movetodisk&amp;aid={$attachment['aid']}&amp;pid={$attachment['pid']}'>{$lang['movetodisk']}</a>";
            }
        } else {
            $attachment['subdir'] = '/'.$attachment['subdir'].'/';
            if ($diskpath) {
                $template->movelink = "<a href='" . $vars->full_url . "admin/attachments.php?action=movetodb&amp;aid={$attachment['aid']}&amp;pid={$attachment['pid']}'>{$lang['movetodb']}</a>";
            }
        }
        if ('0' === $attachment['pid']) {
            $attachment['author'] = $attachment['username'];
            $template->downloadlink = '';
        } else {
            $template->downloadlink = "<a href='" . $attach->getURL((int) $attachment['aid'], (int) $attachment['pid'], $attachment['filename']) . "' target='_blank'>{$lang['textdownload']}</a>";
            if (function_exists('imagecreatetruecolor')) {
                $template->newthumblink = "<a href='" . $vars->full_url . "admin/attachments.php?action=regeneratethumbnail&amp;aid={$attachment['aid']}&amp;pid={$attachment['pid']}'>{$lang['regeneratethumbnail']}</a>";
            }
        }
        $template->deletelink = "<a href='" . $vars->full_url . "admin/attachments.php?action=delete&amp;aid={$attachment['aid']}&amp;pid={$attachment['pid']}'>{$lang['deletebutton']}</a>";

        $template->attachment = $attachment;
        $body .= $template->process('admin_attachments_result_row.php');

        if ($db->num_rows($query2) > 0) {
            $db->data_seek($query2, 0);
        }
        while ($child = $db->fetch_array($query2)) {
            if ($child['parentid'] == $attachment['aid'] && substr($child['filename'], -10) == '-thumb.jpg') {
                $template->attachsize = $attach->getSizeFormatted($child['filesize']);
                $template->movelink = '';
                if ($child['subdir'] == '') {
                    $child['subdir'] = 'DB';
                    if ($diskpath) {
                        $template->movelink = "<a href='" . $vars->full_url . "admin/attachments.php?action=movetodisk&amp;aid={$child['aid']}&amp;pid={$child['pid']}'>{$lang['movetodisk']}</a>";
                    }
                } else {
                    $child['subdir'] = '/'.$child['subdir'].'/';
                    if ($diskpath) {
                        $template->movelink = "<a href='" . $vars->full_url . "admin/attachments.php?action=movetodb&amp;aid={$child['aid']}&amp;pid={$child['pid']}'>{$lang['movetodb']}</a>";
                    }
                }
                if ('0' === $child['pid']) {
                    $template->downloadlink = $lang['thumbnail'];
                } else {
                    $template->downloadlink = '<a href="'.$attach->getURL((int) $child['aid'], (int) $child['pid'], $child['filename']).'" target="_blank">'.$lang['thumbnail'].'</a>';
                }
                $template->child = $child;
                $body .= $template->process('admin_attachments_result_child.php');
            }
        }
    }
    $body .= $template->process('admin_attachments_result_end.php');
}

if ($action == '' && onSubmit('attachsubmit')) {
    $core->request_secure('Control Panel/Attachments', 'mass-edit');
    $filelist = [];
    foreach ($_POST as $postedname => $rawvalue) {
        if (substr($postedname, 0, 8) == 'filename' && is_numeric($fileaid = substr($postedname, 8))) {
            $filelist[] = $fileaid;
        }
    }
    $filelist = implode(', ', $filelist);

    $query = $db->query("SELECT aid, pid, filename FROM " . $vars->tablepre . "attachments WHERE aid IN ($filelist)");
    while ($attachment = $db->fetch_array($query)) {
        $afilename = "filename" . $attachment['aid'];
        $postedvalue = trim($validate->postedVar($afilename, dbescape: false));
        if ($attachment['filename'] !== $postedvalue) {
            $attach->changeName((int) $attachment['aid'], (int) $attachment['pid'], $postedvalue);
        }
    }
    $body = "<tr bgcolor='" . $vars->theme['altbg2'] . "' class='tablerow'><td align='center'>{$lang['textattachmentsupdate']}</td></tr>";
}

if ($action == "delete") {
    $aid = getInt('aid');
    if (noSubmit('yessubmit')) {
        $template->token = $token->create('Control Panel/Attachments/Delete', (string) $aid, $vars::NONCE_AYS_EXP);
        ?>
        <tr bgcolor="<?= $altbg2; ?>" class="ctrtablerow"><td><?= $lang['attach_delete_ays']; ?><br />
        <form action="<?= $full_url ?>admin/attachments.php?action=delete&amp;aid=<?= $aid; ?>" method="post">
          <input type="hidden" name="token" value="<?= $token ?>" />
          <input type="submit" name="yessubmit" value="<?= $lang['textyes']; ?>" /> -
          <input type="submit" name="yessubmit" value="<?= $lang['textno']; ?>" />
        </form></td></tr>
        <?php
    } elseif ($lang['textyes'] === $yessubmit) {
        $core->request_secure('Control Panel/Attachments/Delete', (string) $aid);
        $attach->deleteByID($aid);
        $body = "<tr bgcolor='" . $vars->theme['altbg2'] . "' class='ctrtablerow'><td>{$lang['attach_delete_done']}</td></tr>";
    }
}

if ($action == "movetodb") {
    $aid = getInt('aid');
    $pid = getInt('pid');
    $attach->moveToDB($aid, $pid);
    $body = "<tr bgcolor='" . $vars->theme['altbg2'] . "' class='ctrtablerow'><td>{$lang['movetodb_done']}</td></tr>";
}

if ($action == "movetodisk") {
    $aid = getInt('aid');
    $pid = getInt('pid');
    $attach->moveToDisk($aid, $pid);
    $body = "<tr bgcolor='" . $vars->theme['altbg2'] . "' class='ctrtablerow'><td>{$lang['movetodisk_done']}</td></tr>";
}

if ($action == "regeneratethumbnail") {
    $aid = getInt('aid');
    $pid = getInt('pid');
    $status = $attach->regenerateThumbnail($aid, $pid);
    if ($status === UploadStatus::Success) {
        $msg = $lang['tool_completed'];
    } else {
        $msg = $attach->uploadErrorMsg($status);
    }

    $body = "<tr bgcolor='" . $vars->theme['altbg2'] . "' class='ctrtablerow'><td>$msg</td></tr>";
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
