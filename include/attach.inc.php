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

// attachUploadedFile() checks for the presence of $_FILES[$varname].
// If found, the file will be stored and attached to the specified $pid.
// The $pid can be omitted in post preview mode, thus creating
// orphaned attachments that the registered user will be allowed to manage.
// Storage responsibilities include subdirectory and thumbnail creation.
function attachUploadedFile($varname, $pid=0) {
    global $db, $self, $SETTINGS;
    
    $pid = intval($pid);
    $file = get_attached_file($varname, $filename, $filetype, $filesize);

    if ($file !== FALSE) {
        if ($pid == 0) {
            $sql = "SELECT COUNT(aid) AS atcount FROM ".X_PREFIX."attachments WHERE pid=0 AND parentid=0 AND uid={$self['uid']}";
        } else {
            $sql = "SELECT COUNT(aid) AS atcount FROM ".X_PREFIX."attachments WHERE pid=$pid AND parentid=0";
        }
        $query = $db->query($sql);
        $query = $db->fetch_array($query);
        if ($query['atcount'] < $SETTINGS['filesperpost']) {
            $type = $db->escape($_FILES['attach']['type']);
            $sqlsize = '';

            // Check if we can store image metadata
            $extention = get_extension($filename);
            if ($extention == 'jpg' || $extention == 'jpeg' || $extention == 'jpe' || $extention == 'gif' || $extention == 'png' || $extention == 'bmp') {
                $result = getimagesize($_FILES[$varname]['tmp_name']);
            } else {
                $result = FALSE;
            }

            if ($result !== FALSE) {
                $imgSize = new CartesianSize($result[0], $result[1]);
                $sqlsize = $result[0].'x'.$result[1];
            }

            // Store File
            $db->query("INSERT INTO ".X_PREFIX."attachments (pid, filename, filetype, filesize, attachment, uid, img_size) VALUES ($pid, '$filename', '$filetype', '$filesize', '$file', {$self['uid']}, '$sqlsize')");
            unset($file);

            // Make Thumbnail
            if ($result !== FALSE) {
                createThumbnail($_FILES[$varname]['name'], $_FILES[$varname]['tmp_name'], $imgSize, $db->insert_id(), $pid);
            }

            return TRUE;
        }
    }
    return FALSE;
}

function claimOrphanedAttachments($pid) {
    global $db, $self;
    $pid = intval($pid);
    $db->query("UPDATE ".X_PREFIX."attachments SET pid=$pid WHERE pid=0 AND uid={$self['uid']}");
}

function doAttachmentEdits($pid=0) {
    if (isset($_POST['attachment']) && is_array($_POST['attachment'])) {
        $pid = intval($pid);
        foreach($_POST['attachment'] as $aid => $attachment) {
            switch($attachment['action']) {
            case 'replace':
                if (attachUploadedFile('replace_'.$aid, $pid)) {
                    deleteAttachment($aid, $pid);
                }
                break;
            case 'rename':
                $rename = trim(postedVar('rename_'.$aid, '', FALSE, FALSE));
                renameAttachment($aid, $pid, $rename);
                break;
            case 'delete':
                deleteAttachment($aid, $pid);
                break;
            default:
                break;
            }
        }
    }
}

function renameAttachment($aid, $pid, $rawnewname) {
    global $db;
    if (isValidFilename($rawnewname)) {
        $aid = intval($aid);
        $dbrename = $db->escape($rawnewname);
        $pid = intval($pid);
        $db->query("UPDATE ".X_PREFIX."attachments SET filename='$dbrename' WHERE aid=$aid AND pid=$pid");
    }
}

function deleteAttachment($aid, $pid) {
    global $db;
    $aid = intval($aid);
    $pid = intval($pid);
    $db->query("DELETE FROM ".X_PREFIX."attachments WHERE (aid=$aid OR parentid=$aid) AND pid=$pid");
}

function deleteAllAttachments($pid) {
    global $db;
    $pid = intval($pid);
    $db->query("DELETE FROM ".X_PREFIX."attachments WHERE pid=$pid");
}

function get_attached_file($varname, &$filename, &$filetype, &$filesize, $dbescape=TRUE) {
    global $db, $lang, $SETTINGS;

    $filename = '';
    $filetype = '';
    $filesize = 0;

    if (isset($_FILES[$varname])) {
        $file =& $_FILES[$varname];
    } else {
        return false;
    }

    if ($file['name'] != 'none' && !empty($file['name']) && is_uploaded_file($file['tmp_name'])) {
        $file['name'] = trim($file['name']);
        if (!isValidFilename($file['name'])) {
            error($lang['invalidFilename'], false, '', '', false, false, false, false);
            return false;
        }

        $filesize = intval(filesize($file['tmp_name'])); // fix bad filesizes
        if ($file['size'] > $SETTINGS['maxattachsize']) {
            error($lang['attachtoobig'], false, '', '', false, false, false, false);
            return false;
        } else {
            if ($dbescape) {
                $attachment = $db->escape(fread(fopen($file['tmp_name'], 'rb'), filesize($file['tmp_name'])));
                $filename = $db->escape($file['name']);
                $filetype = $db->escape(preg_replace('#[\\x00\\r\\n%]#', '', $file['type']));
            } else {
                $attachment = fread(fopen($file['tmp_name'], 'rb'), filesize($file['tmp_name']));
                $filename = $file['name'];
                $filetype = preg_replace('#[\\x00\\r\\n%]#', '', $file['type']);
            }

            if ($filesize == 0) {
                return false;
            } else {
                return $attachment;
            }
        }
    } else {
        return false;
    }
}

function getAttachmentURL($aid, $pid, $filename, $htmlencode=TRUE) {
    global $full_url, $SETTINGS;
    
    $SETTINGS_virtual_path = '';
    
    if ($SETTINGS_virtual_path == '') {
        $virtual_path = $full_url;
    }

    switch($SETTINGS['file_url_format']) {
    case 1:
        if ($htmlencode) {
            $url = "{$virtual_path}files.php?pid=$pid&amp;aid=$aid";
        } else {
            $url = "{$virtual_path}files.php?pid=$pid&aid=$aid";
        }
        break;
    case 2:
        $url = "{$virtual_path}files/$pid/$aid/";
        break;
    case 3:
        $url = "{$virtual_path}files/$aid/".rawurlencode($filename);
        break;
    case 4:
        $url = "{$virtual_path}/$pid/$aid/";
        break;
    case 5:
        $url = "{$virtual_path}/$aid/".rawurlencode($filename);
        break;
    }

    return $url;
}

function createThumbnail($filename, $filepath, $imgSize, $aid, $pid) {
    global $db, $self, $SETTINGS;

    // Check if GD is available
    if (!function_exists('imagecreatetruecolor')) {
        return FALSE;
    }

    // Determine if a thumbnail is needed.
    $result = explode('x', $SETTINGS['max_thumb_size']);
    if ($result[0] > 0 And $result[1] > 0) {
        $thumbMaxSize = new CartesianSize($result[0], $result[1]);
    } else {
        return FALSE;
    }

    if ($imgSize->isSmallerThan($thumbMaxSize)) {
        return FALSE;
    }

    // Create a thumbnail for this attachment.
    if ($imgSize->aspect() > $thumbMaxSize->aspect()) {
        $thumbSize = new CartesianSize($thumbMaxSize->width, round($imgSize->aspect() * $thumbMaxSize->width));
    } else {
        $thumbSize = new CartesianSize(round($imgSize->aspect() * $thumbMaxSize->height), $thumbMaxSize->height);
    }
    
    $extention = get_extension($filename);
    switch($extension) {
    case 'png':
        $img = @imagecreatefrompng($filepath);
        break;
    case 'bmp':
        $img = @imagecreatefromwbmp($filepath);
        break;
    case 'gif':
        $img = @imagecreatefromgif($filepath);
        break;
    case 'jpeg':
    case 'jpg':
    case 'jpe':
    default:
        $img = @imagecreatefromjpeg($filepath);
        break;
    }

    if (!$img) {
        return FALSE;
    }
    
    $thumb = imagecreatetruecolor($thumbSize->width, $thumbSize->height);

    // Resize $img
    if (!imagecopyresampled($thumb, $img, 0, 0, 0, 0, $thumbSize->width, $thumbSize->height, $imgSize->width, $imgSize->height)) {
        return FALSE;
    }

    $filename = $filename.'-thumb.jpg';
    $filepath = $filepath.'-thumb.jpg';

    // Write to Disk
    imagejpeg($thumb, $filepath);

    $filesize = intval(filesize($filepath));
    $file = $db->escape(fread(fopen($filepath, 'rb'), $filesize));
    $filetype = 'image/jpeg';
    $sqlsize = $thumbSize->width.'x'.$thumbSize->height;

    $db->query("INSERT INTO ".X_PREFIX."attachments (pid, filename, filetype, filesize, attachment, uid, parentid, img_size) VALUES ($pid, '$filename', '$filetype', '$filesize', '$file', {$self['uid']}, $aid, '$sqlsize')");
    
    return TRUE;
}

class CartesianSize {
    var $height;
    var $width;
    
    function CartesianSize($width, $height) {
        $this->height = intval($height);
        $this->width = intval($width);
    }
    
    function aspect() {
        // Read-Only Property
        return $this->width / $this->height;
    }
    
    function isBiggerThan($otherSize) {
        // Would overload '>' operator
        return ($this->width > $otherSize->width Or $this->height > $otherSize->height);
    }

    function isSmallerThan($otherSize) {
        // Would overload '<=' operator
        return ($this->width <= $otherSize->width And $this->height <= $otherSize->height);
    }
}
?>
