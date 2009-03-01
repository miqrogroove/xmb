<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2009, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * http://www.ientry.com
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

define('X_SCRIPT', 'files.php');

require 'header.php';

header('X-Robots-Tag: nofollow');

loadtemplates('');
eval('$css = "'.template('css').'";');

$aid = 0;
$pid = 0;
$filename = '';

// Parse "Pretty" URLs
switch(intval($SETTINGS['file_url_format'])) {
case 1:
//    $url = "{$virtual_path}files.php?pid=$pid&amp;aid=$aid";
    $aid = getInt('aid');
    $pid = getInt('pid');
    break;
case 2:
//    $url = "{$virtual_path}files/$pid/$aid/";
    $result = explode('/', $url);
    if ($result[count($result) - 4] == 'files') { // Remember count() is 1-based
        $pid = intval($result[count($result) - 3]);
        $aid = intval($result[count($result) - 2]);
    }
    break;
case 3:
//    $url = "{$virtual_path}files/$aid/".rawurlencode($filename);
    $result = explode('/', $url);
    if ($result[count($result) - 3] == 'files') {
        $aid = intval($result[count($result) - 2]);
        $filename = urldecode($result[count($result) - 1]);
    }
    break;
case 4:
//    $url = "{$virtual_path}/$pid/$aid/";
    $result = explode('/', $url);
    $pid = intval($result[count($result) - 3]);
    $aid = intval($result[count($result) - 2]);
    break;
case 5:
//    $url = "{$virtual_path}/$aid/".rawurlencode($filename);
    $result = explode('/', $url);
    $aid = intval($result[count($result) - 2]);
    $filename = urldecode($result[count($result) - 1]);
    break;
default:
    $aid = getInt('aid');
    $pid = getInt('pid');
    break;
}

// Sanity Checks
if ($aid <= 0 Or $pid < 0 Or ($pid == 0 And $filename == '' And $self['uid'] == 0)) {
    fileError();
}

// Retrieve attachment metadata
if ($filename == '') {
    $where = "WHERE a.aid=$aid AND a.pid=$pid";
    if ($pid == 0 And !X_ADMIN) {
        $where .= " AND a.uid={$self['uid']}"; // Allow preview of own attachments when URL format requires a PID.
    }
} else {
    $filename = $db->escape_var($filename);
    $where = "WHERE a.aid=$aid AND a.filename='$filename'";
}
$query = $db->query("SELECT a.*, UNIX_TIMESTAMP(a.updatetime) AS updatestamp, p.fid FROM ".X_PREFIX."attachments AS a LEFT JOIN ".X_PREFIX."posts AS p USING (pid) $where");
if ($db->num_rows($query) != 1) {
    fileError();
}
$file = $db->fetch_array($query);
$db->free_result($query);

if ($pid > 0 Or $file['fid'] != '') {
    $forum = getForum($file['fid']);

    if (($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on' || ($forum['attachstatus'] != 'on' And !X_ADMIN)) {
        fileError();
    }

    // Check attachment permissions
    $perms = checkForumPermissions($forum);
    if (!$perms[X_PERMS_VIEW]) {
        if (X_GUEST) {
            redirect("{$full_url}misc.php?action=login", 0);
            exit;
        } else {
            error($lang['privforummsg']);
        }
    } else if (!$perms[X_PERMS_PASSWORD]) {
        handlePasswordDialog($forum['fid']);
    }

    $fup = array();
    if ($forum['type'] == 'sub') {
        $fup = getForum($forum['fup']);
        // prevent access to subforum when upper forum can't be viewed.
        $fupPerms = checkForumPermissions($fup);
        if (!$fupPerms[X_PERMS_VIEW]) {
            if (X_GUEST) {
                redirect("{$full_url}misc.php?action=login", 0);
                exit;
            } else {
                error($lang['privforummsg']);
            }
        } else if (!$fupPerms[X_PERMS_PASSWORD]) {
            handlePasswordDialog($fup['fid']);
        }
        unset($fup);
    }
}

// Verify file is available
$path = '';
$size = 0;
if ($file['subdir'] == '') {
    $size = strlen($file['attachment']);
} else {
    $path = $SETTINGS['files_storage_path'];
    if (substr($path, -1) != '/') {
        $path .= '/';
    }
    $path = $path.$file['subdir'].'/'.$file['aid'];
    if (!is_file($path)) {
        header('HTTP/1.0 500 Internal Server Error');
        error($lang['filecorrupt']);
    }
    $size = intval(filesize($path));
}
if ($size != $file['filesize']) {
    header('HTTP/1.0 500 Internal Server Error');
    error($lang['filecorrupt']);
}

// Verify output stream is empty
assertEmptyOutputStream('files.php');

// Do not issue any errors below this line

// Check If-Modified-Since request header
// "If the requested variant has not been modified since the time specified in this field,
// an entity will not be returned from the server; instead, a 304 (not modified) response
// will be returned without any message-body."
if ($_SERVER['REQUEST_METHOD'] == 'GET' And isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    if (function_exists('date_default_timezone_set')) {
        date_default_timezone_set('UTC'); // Workaround for stupid PHP 5 problems.
    }
    if (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $file['updatestamp']) {
        header('HTTP/1.0 304 Not Modified');
        exit;
    }
}

// Increment hit counter
$db->query("UPDATE ".X_PREFIX."attachments SET downloads=downloads+1 WHERE aid=$aid");

// Set response headers
$type = strtolower($file['filetype']);
$type = ($type == 'text/html') ? 'text/plain' : $type;
if ($file['img_size'] == '') {
    $dispositionType = 'attachment';
} else {
    $dispositionType = 'inline';
}

header("Content-type: $type");
header("Content-length: $size");
header("Content-Disposition: {$dispositionType}; filename=\"{$file['filename']}\"");
header("Content-Description: XMB Attachment");
header("Cache-Control: public; max-age=604800");
header("Expires: ".gmdate('D, d M Y H:i:s', time() + 604800)." GMT");
header("Last-Modified: ".gmdate('D, d M Y H:i:s', $file['updatestamp'])." GMT");

// Send the response entity
if ($file['subdir'] == '') {
    echo $file['attachment'];
} else {
    readfile($path);
}
exit();

function fileError() {
    global $lang;
    header('HTTP/1.0 404 Not Found');
    error($lang['textnothread']);
}
?>
