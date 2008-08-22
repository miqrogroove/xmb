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

if (!defined('IN_CODE')) {
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
    $attachment = $db->escape(file_get_contents($path));
    $db->query("UPDATE ".X_PREFIX."attachments SET subdir='', attachment='$attachment'");
    unlink($path);
}

function moveAttachmentToDisk($aid, $pid) {
    global $db;
    $aid = intval($aid);
    $pid = intval($pid);
    $query = $db->query("SELECT *, UNIX_TIMESTAMP(updatetime) AS updatestamp FROM ".X_PREFIX."attachments WHERE aid=$aid AND pid=$pid");
    if ($db->num_rows($query) != 1) {
        return FALSE;
    }
    $attach = $db->fetch_array($query);
    if ($attach['subdir'] != '' Or strlen($attach['attachment']) != $attach['filesize']) {
        return FALSE;
    }
    $subdir = getNewSubdir($attach['updatestamp']);
    $path = getFullPathFromSubdir($subdir);
    if (!is_dir($path)) {
        mkdir($path, 0777, TRUE);
    }
    $newfilename = $aid;
    $path .= $newfilename;
    $file = fopen($path, 'wb');
    fwrite($file, $attach['attachment']);
    fclose($file);
    $db->query("UPDATE ".X_PREFIX."attachments SET subdir='$subdir', attachment='' WHERE aid=$aid AND pid=$pid");
}

function regenerateThumbnail($aid, $pid) {
    global $db, $SETTINGS;
    $aid = intval($aid);
    $pid = intval($pid);
    deleteThumbnail($aid, $pid);

    // Initialize
    $path = getFullPathFromSubdir('');
    $usedb = TRUE;

    // Write attachment to disk
    $query = $db->query("SELECT *, UNIX_TIMESTAMP(updatetime) AS updatestamp FROM ".X_PREFIX."attachments WHERE aid=$aid AND pid=$pid");
    if ($db->num_rows($query) != 1) {
        return FALSE;
    }
    $attach = $db->fetch_array($query);
    if ($attach['subdir'] == '') {
        if (strlen($attach['attachment']) != $attach['filesize']) {
            return FALSE;
        }
        $subdir = getNewSubdir($attach['updatestamp']);
        $path = getFullPathFromSubdir($subdir);
        if (!is_dir($path)) {
            mkdir($path, 0777, TRUE);
        }
        $newfilename = $aid;
        $path .= $newfilename;
        $file = fopen($path, 'wb');
        fwrite($file, $attach['attachment']);
        fclose($file);
    } else {
        $path = getFullPathFromSubdir($attach['subdir']);
        $path .= $aid;
        if (!is_file($path)) {
            return FALSE;
        }
        if (filesize($path) != $attach['filesize']) {
            return FALSE;
        }
    }

    // Check if we can store image metadata
    $result = getimagesize($path);

    if ($result === FALSE) {
        return FALSE;
    }
    $imgSize = new CartesianSize($result[0], $result[1]);
    $sqlsize = $result[0].'x'.$result[1];

    $result = explode('x', $SETTINGS['max_image_size']);
    if ($result[0] > 0 And $result[1] > 0) {
        $maxImgSize = new CartesianSize($result[0], $result[1]);
        if ($imgSize->isBiggerThan($maxImgSize)) {
            return FALSE;
        }
    }

    if ($attach['img_size'] != $sqlsize) {
        $db->query("UPDATE ".X_PREFIX."attachments SET img_size='$sqlsize' WHERE aid=$aid AND pid=$pid");
    }
    
    createThumbnail($attach['filename'], $path, $attach['filesize'], $imgSize, $db->escape($attach['filetype']), $aid, $pid, $attach['subdir']);

    // Clean up temp files
    if ($attach['subdir'] == '') {
        unlink($path);
    }
}

function deleteThumbnail($aid, $pid) {
    $aid = intval($aid);
    $pid = intval($pid);
    private_deleteAttachments("WHERE parentid=$aid AND pid=$pid AND filename LIKE '%-thumb.jpg'");
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
