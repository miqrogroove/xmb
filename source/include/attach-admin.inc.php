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

if (!defined('IN_CODE')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

require_once('attach.inc.php');

function moveAttachmentToDB($aid, $pid) {
    global $db;
    $aid = intval($aid);
    $pid = intval($pid);
    $query = $db->query("SELECT aid, filesize, subdir FROM ".X_PREFIX."attachments WHERE aid=$aid AND pid=$pid");
    if ($db->num_rows($query) != 1) {
        return FALSE;
    }
    $attach = $db->fetch_array($query);
    if ($attach['subdir'] == '') {
        return FALSE;
    }
    $path = getFullPathFromSubdir($attach['subdir']).$attach['aid'];
    if (intval(filesize($path)) != intval($attach['filesize'])) {
        return FALSE;
    }
    $attachment = file_get_contents($path);
    $db->escape_fast($attachment);
    $db->query("UPDATE ".X_PREFIX."attachments SET subdir='', attachment='$attachment' WHERE aid=$aid AND pid=$pid");
    if ($db->affected_rows() !== 1) {
        return FALSE;
    }
    unlink($path);
}

function moveAttachmentToDisk($aid, $pid) {
    global $db;
    $aid = intval($aid);
    $pid = intval($pid);
    $query = $db->query("SELECT a.*, UNIX_TIMESTAMP(a.updatetime) AS updatestamp, p.dateline "
                      . "FROM ".X_PREFIX."attachments AS a LEFT JOIN ".X_PREFIX."posts AS p USING (pid) "
                      . "WHERE a.aid=$aid AND a.pid=$pid");
    if ($db->num_rows($query) != 1) {
        return FALSE;
    }
    $attach = $db->fetch_array($query);
    if ($attach['subdir'] != '' Or strlen($attach['attachment']) != $attach['filesize']) {
        return FALSE;
    }
    if (intval($attach['updatestamp']) == 0 And intval($attach['dateline']) > 0) {
        $attach['updatestamp'] = $attach['dateline'];
    }
    $subdir = getNewSubdir($attach['updatestamp']);
    $path = getFullPathFromSubdir($subdir, TRUE);
    $newfilename = $aid;
    $path .= $newfilename;
    $file = fopen($path, 'wb');
    if ($file === FALSE) {
        return FALSE;
    }
    if (fwrite($file, $attach['attachment']) != $attach['filesize']) {
        return FALSE;
    }
    fclose($file);
    $db->query("UPDATE ".X_PREFIX."attachments SET subdir='$subdir', attachment='' WHERE aid=$aid AND pid=$pid");
}

function deleteOrphans() {
    global $db;
    $q = $db->query("SELECT a.aid, a.pid FROM ".X_PREFIX."attachments AS a "
                  . "LEFT JOIN ".X_PREFIX."posts AS p USING (pid) "
                  . "LEFT JOIN ".X_PREFIX."attachments AS b ON a.parentid=b.aid "
                  . "WHERE ((a.uid=0 OR a.pid > 0) AND p.pid IS NULL) OR (a.parentid > 0 AND b.aid IS NULL)");

    while($a = $db->fetch_array($q)) {
        deleteAttachment($a['aid'], $a['pid']);
    }
    
    return $db->num_rows($q);
}

function deleteMultiThreadAttachments($tids) {
    private_deleteAttachments("INNER JOIN ".X_PREFIX."posts USING (pid) WHERE tid IN ($tids)");
}

function deleteAttachmentsByUser($username) {
    private_deleteAttachments("INNER JOIN ".X_PREFIX."posts USING (pid) WHERE author='$username'");
    private_deleteAttachments("INNER JOIN ".X_PREFIX."members USING (uid) WHERE username='$username'");
}
?>
