<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Alpha One - This software should not be used for any purpose after 30 September 2008.
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2008, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * Copyright (c) 2007, iEntry, Inc.
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

// Dev Alpha Notes:  Need to implement same URL parsing used for cookies and $full_url to evaluate $virtual_path

require 'header.php';

loadtemplates('');
eval('$css = "'.template('css').'";');

$aid = 0;
$pid = 0;
$filename = '';

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

if ($aid <= 0 Or ($pid <= 0 And $filename == '')) {
    fileError();
}

if ($pid > 0) {
    $query = $db->query("SELECT a.*, UNIX_TIMESTAMP(a.updatetime) AS updatestamp, p.fid FROM ".X_PREFIX."attachments AS a INNER JOIN ".X_PREFIX."posts AS p USING (pid) WHERE a.aid=$aid AND a.pid=$pid");
} else {
    $filename = $db->escape($filename);
    $query = $db->query("SELECT a.*, UNIX_TIMESTAMP(a.updatetime) AS updatestamp, p.fid FROM ".X_PREFIX."attachments AS a INNER JOIN ".X_PREFIX."posts AS p USING (pid) WHERE a.aid=$aid AND a.filename='$filename'");
}
if ($db->num_rows($query) != 1) {
    fileError();
}
$file = $db->fetch_array($query);
$db->free_result($query);

$forum = getForum($file['fid']);

if (($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on' || $forum['attachstatus'] != 'on') {
    fileError();
}

$perms = checkForumPermissions($forum);
if (!$perms[X_PERMS_VIEW]) {
    error($lang['privforummsg']);
} else if (!$perms[X_PERMS_PASSWORD]) {
    handlePasswordDialog($fid);
}

$fup = array();
if ($forum['type'] == 'sub') {
    $fup = getForum($forum['fup']);
    // prevent access to subforum when upper forum can't be viewed.
    $fupPerms = checkForumPermissions($fup);
    if (!$fupPerms[X_PERMS_VIEW]) {
        error($lang['privforummsg']);
    } else if (!$fupPerms[X_PERMS_PASSWORD]) {
        handlePasswordDialog($fup['fid']);
    }
    unset($fup);
}

if ($file['filesize'] != strlen($file['attachment'])) {
    error($lang['filecorrupt']);
}

$db->query("UPDATE ".X_PREFIX."attachments SET downloads=downloads+1 WHERE aid=$aid");

$type = strtolower($file['filetype']);
$size = (int) $file['filesize'];
$type = ($type == 'text/html') ? 'text/plain' : $type;

header("Content-type: $type");
header("Content-length: $size");
header("Content-Disposition: attachment; filename=\"{$file['filename']}\"");
header("Content-Description: XMB Attachment");
header("Cache-Control: public; max-age=604800");
header("Expires: ".gmdate('D, d M Y H:i:s', time() + 604800)." GMT");
header("Last-Modified: ".gmdate('D, d M Y H:i:s', $file['updatestamp'])." GMT");

echo $file['attachment'];
exit();

function fileError() {
    global $lang;
    header('HTTP/1.0 404 Not Found');
    error($lang['textnothread']);
}
?>
