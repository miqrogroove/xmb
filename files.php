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

require './header.php';

$core = Services\core();
$forums = Services\forums();
$observer = Services\observer();
$sql = Services\sql();
$vars = Services\vars();
$lang = &$vars->lang;

$aid = 0;
$pid = 0;
$filename = '';

// Moderation of new users
$quarantine = false;
$format = (int) $vars->settings['file_url_format'];
if ('off' == $vars->settings['quarantine_new_users'] && ! X_SMOD) {
    // Access to quarantined files is restricted when that system is not in use.
} elseif (getInt('newaid') > 0) {
    // Otherwise, the newaid variable indicates a request to read attachments for preview or moderation.
    $quarantine = true;
    $format = 99;
} else {
    // Most requests are not for quarantined files.
}

// Parse "Pretty" URLs
switch ($format) {
    case 1:
    //    $url = "{$virtual_path}files.php?pid=$pid&amp;aid=$aid";
        $aid = getInt('aid');
        $pid = getInt('pid');
        break;
    case 2:
    //    $url = "{$virtual_path}files/$pid/$aid/";
        $result = explode('/', $vars->url);
        if (count($result) < 4) break;
        if ($result[count($result) - 4] == 'files') { // Remember count() is 1-based
            $pid = intval($result[count($result) - 3]);
            $aid = intval($result[count($result) - 2]);
        }
        break;
    case 3:
    //    $url = "{$virtual_path}files/$aid/".rawurlencode($filename);
        $result = explode('/', $vars->url);
        if (count($result) < 3) break;
        if ($result[count($result) - 3] == 'files') {
            $aid = intval($result[count($result) - 2]);
            $filename = htmlEsc(urldecode($result[count($result) - 1]));
        }
        break;
    case 4:
    //    $url = "{$virtual_path}/$pid/$aid/";
        $result = explode('/', $vars->url);
        if (count($result) < 3) break;
        $pid = intval($result[count($result) - 3]);
        $aid = intval($result[count($result) - 2]);
        break;
    case 5:
    //    $url = "{$virtual_path}/$aid/".rawurlencode($filename);
        $result = explode('/', $vars->url);
        if (count($result) < 2) break;
        $aid = intval($result[count($result) - 2]);
        $filename = htmlEsc(urldecode($result[count($result) - 1]));
        break;
    case 99:
    //    $url = "{$virtual_path}files.php?newpid=$pid&amp;newaid=$aid";
        $aid = getInt('newaid');
        $pid = getInt('newpid');
        break;
    default:
        $aid = getInt('aid');
        $pid = getInt('pid');
}

// Sanity Checks
if ($aid <= 0 || $pid < 0 || ($pid == 0 && $filename == '' && '0' === $vars->self['uid'])) {
    header('HTTP/1.0 404 Not Found');
    $core->error($lang['textnothread']);
}

// Retrieve attachment metadata
if ($filename == '') {
    if ($pid == 0 && ! X_ADMIN) {
        // Allow preview of own attachments when the URL format requires a PID.
        $file = $sql->getAttachmentAndFID($aid, $quarantine, $pid, $filename, (int) $vars->self['uid']);
    } else {
        $file = $sql->getAttachmentAndFID($aid, $quarantine, $pid);
    }
} else {
    $file = $sql->getAttachmentAndFID($aid, $quarantine, 0, $filename);
}
if (empty($file)) {
    header('HTTP/1.0 404 Not Found');
    $core->error($lang['textnothread']);
}

if ($pid > 0 || $file['fid'] != '') {
    $forum = $forums->getForum((int) $file['fid']);

    if (null === $forum || ($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on' || ($forum['attachstatus'] != 'on' && !X_ADMIN)) {
        header('HTTP/1.0 404 Not Found');
        $core->error($lang['textnothread']);
    }

    // Check attachment permissions
    $core->assertForumPermissions($forum);
    unset($forum);
}

// Verify file is available
$path = '';
$size = 0;
if ($file['subdir'] == '') {
    $size = strlen($file['attachment']);
} else {
    $path = $vars->settings['files_storage_path'];
    if (substr($path, -1) != '/') {
        $path .= '/';
    }
    $path = $path.$file['subdir'].'/'.$file['aid'];
    if (!is_file($path)) {
        header('HTTP/1.0 500 Internal Server Error');
        $core->error($lang['filecorrupt']);
    }
    $size = intval(filesize($path));
}
if ($size != (int) $file['filesize']) {
    header('HTTP/1.0 500 Internal Server Error');
    $core->error($lang['filecorrupt']);
}

// Verify output stream is empty
$observer->assertEmptyOutputStream('files.php');

// Do not issue any errors below this line

// Check If-Modified-Since request header
// "If the requested variant has not been modified since the time specified in this field,
// an entity will not be returned from the server; instead, a 304 (not modified) response
// will be returned without any message-body."
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    date_default_timezone_set('UTC'); // Workaround for stupid PHP 5 problems.
    if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= (int) $file['updatestamp']) {
        header('HTTP/1.0 304 Not Modified');
        exit;
    }
}

// Increment hit counter
$sql->raiseDownloadCounter($aid, $quarantine);

// Set response headers
if ($file['img_size'] == '') {
    $type = 'application/binary';
    $dispositionType = 'attachment';
    header('X-Robots-Tag: nofollow, noimageindex');
} else {
    $type = strtolower(rawHTML($file['filetype']));
    $dispositionType = 'inline';
}

$rawfilename = rawHTML($file['filename']);

header("Content-type: $type");
header("Content-length: $size");
header("Content-Disposition: {$dispositionType}; filename=\"$rawfilename\"");
header("Content-Description: XMB Attachment");
header("Cache-Control: public, max-age=604800");
header("Expires: " . gmdate('D, d M Y H:i:s', time() + 604800) . " GMT");
header("Last-Modified: " . gmdate('D, d M Y H:i:s', (int) $file['updatestamp']) . " GMT");

// Send the response entity
if ($file['subdir'] == '') {
    echo $file['attachment'];
} else {
    readfile($path);
}
