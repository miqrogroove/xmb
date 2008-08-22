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
    
    $path = getFullPathFromSubdir('');
    $pid = intval($pid);
    $usedb = TRUE;

    if ($path !== FALSE) {
        if (is_dir($path)) {
            $usedb = FALSE;
        }
    }

    $file = get_attached_file($varname, $filename, $filetype, $filesize, TRUE, $usedb);

    if ($file !== FALSE) {
        // Sanity checks
        if (intval($self['uid']) <= 0) {
            return FALSE;
        }

        // Check maximum attachments per post
        if ($pid == 0) {
            $sql = "SELECT COUNT(aid) AS atcount FROM ".X_PREFIX."attachments WHERE pid=0 AND parentid=0 AND uid={$self['uid']}";
        } else {
            $sql = "SELECT COUNT(aid) AS atcount FROM ".X_PREFIX."attachments WHERE pid=$pid AND parentid=0";
        }
        $query = $db->query($sql);
        $query = $db->fetch_array($query);
        if ($query['atcount'] >= $SETTINGS['filesperpost']) {
            return FALSE;
        }

        // Check minimum file size for disk storage
        if ($filesize < $SETTINGS['files_min_disk_size'] And !$usedb) {
            $usedb = TRUE;
            $file = get_attached_file($varname, $filename, $filetype, $filesize, TRUE, $usedb);
        }

        return private_attachGenericFile($pid, $usedb, $file, $_FILES[$varname]['tmp_name'], $filename, $_FILES[$varname]['name'], $filetype, $filesize);
    }
    return FALSE;
}

function attachRemoteFile($url, $pid=0) {
    global $db, $self, $SETTINGS;

    $path = getFullPathFromSubdir('');
    $pid = intval($pid);
    $usedb = TRUE;
    $filetype = '';

    $filepath = FALSE;
    if ($path !== FALSE) {
        if (is_dir($path)) {
            $usedb = FALSE;
        }
        $filepath = tempnam($path, 'xmb-');
    }
    if ($filepath === FALSE) {
        $filepath = tempnam('', 'xmb-');
        if ($filepath === FALSE) {
            return FALSE;
        }
    }

    // Sanity checks
    if (substr($url, 0, 7) != 'http://' And substr($url, 0, 6) != 'ftp://') {
        return FALSE;
    }
    $urlparts = parse_url($url);
    if ($urlparts === FALSE) {
        return FALSE;
    }
    $filename = FALSE;
    $urlparts = explode('/', $urlparts['path']);
    for($i=count($urlparts)-1; $i>=0; $i--) {
        if (isValidFilename($urlparts[$i])) {
            $filename = $urlparts[$i];
            break;
        }
    }
    if ($filename === FALSE) { //Failed to find a usable filename in $url.
        $filename = explode('/', $filepath);
        $filename = array_pop($filename);
    }
    $dbfilename = $db->escape($filename);
    if (intval($self['uid']) <= 0) {
        return FALSE;
    }

    // Check maximum attachments per post
    if ($pid == 0) {
        $sql = "SELECT COUNT(aid) AS atcount FROM ".X_PREFIX."attachments WHERE pid=0 AND parentid=0 AND uid={$self['uid']}";
    } else {
        $sql = "SELECT COUNT(aid) AS atcount FROM ".X_PREFIX."attachments WHERE pid=$pid AND parentid=0";
    }
    $query = $db->query($sql);
    $query = $db->fetch_array($query);
    if ($query['atcount'] >= $SETTINGS['filesperpost']) {
        return FALSE;
    }

    // Now grab the remote file
    $file = file_get_contents($url);

    if ($file !== FALSE) {
        $filesize = strlen($file);
        
        // Write to disk
        $handle = fopen($filepath, 'wb');
        fwrite($handle, $file);
        fclose($handle);

        // Verify that the file is actually an image.
        $result = getimagesize($filepath);
        if ($result === FALSE) {
            return FALSE;
        }
        $filetype = $db->escape(image_type_to_mime_type($result[2]));

        // Check minimum file size for disk storage
        if ($filesize < $SETTINGS['files_min_disk_size'] And !$usedb) {
            $usedb = TRUE;
        } else {
            $file = '';
        }

        $file = $db->escape($file);
        return private_attachGenericFile($pid, $usedb, $file, $filepath, $dbfilename, $filename, $filetype, $filesize);
    }
    return FALSE;
}

function private_attachGenericFile($pid, $usedb, $dbfile, $filepath, $dbfilename, $rawfilename, $dbfiletype, $dbfilesize) {
    global $db, $self, $SETTINGS;

    // Check if we can store image metadata
    $extention = get_extension($rawfilename);
    if ($extention == 'jpg' || $extention == 'jpeg' || $extention == 'jpe' || $extention == 'gif' || $extention == 'png' || $extention == 'bmp') {
        $result = getimagesize($filepath);
    } else {
        $result = FALSE;
    }

    $sqlsize = '';
    if ($result !== FALSE) {
        $imgSize = new CartesianSize($result[0], $result[1]);
        $sqlsize = $result[0].'x'.$result[1];

        $result = explode('x', $SETTINGS['max_image_size']);
        if ($result[0] > 0 And $result[1] > 0) {
            $maxImgSize = new CartesianSize($result[0], $result[1]);
            if ($imgSize->isBiggerThan($maxImgSize)) {
                return FALSE;
            }
        }
    }

    // Store File
    if ($usedb) {
        $subdir = '';
    } else {
        $file = '';
        $subdir = getNewSubdir();
        $path = getFullPathFromSubdir($subdir);
        if (!is_dir($path)) {
            mkdir($path, 0777, TRUE);
        }
    }
    $db->query("INSERT INTO ".X_PREFIX."attachments (pid, filename, filetype, filesize, attachment, uid, img_size, subdir) VALUES ($pid, '$dbfilename', '$dbfiletype', $dbfilesize, '$dbfile', {$self['uid']}, '$sqlsize', '$subdir')");
    unset($file);
    if ($db->affected_rows() == 1) {
        $aid = $db->insert_id();
    } else {
        return FALSE;
    }
    if ($usedb) {
        $path = $filepath;
    } else {
        $newfilename = $aid;
        $path .= $newfilename;
        rename($filepath, $path);
    }

    // Make Thumbnail
    if ($result !== FALSE) {
        createThumbnail($rawfilename, $path, $dbfilesize, $imgSize, $aid, $pid, $subdir);
    }

    // Remove temp upload file, is_uploaded_file was checked in get_attached_file()
    if ($usedb) {
        unlink($path);
    }

    return $aid;
}

function claimOrphanedAttachments($pid) {
    global $db, $self;
    $pid = intval($pid);
    $db->query("UPDATE ".X_PREFIX."attachments SET pid=$pid WHERE pid=0 AND uid={$self['uid']}");
}

function doAttachmentEdits($pid=0) {
    $deletes = array();
    if (isset($_POST['attachment']) && is_array($_POST['attachment'])) {
        $pid = intval($pid);
        foreach($_POST['attachment'] as $aid => $attachment) {
            switch($attachment['action']) {
            case 'replace':
                deleteAttachment($aid, $pid);
                $deletes[] = $aid;
                attachUploadedFile('replace_'.$aid, $pid);
                break;
            case 'rename':
                $rename = trim(postedVar('rename_'.$aid, '', FALSE, FALSE));
                renameAttachment($aid, $pid, $rename);
                break;
            case 'delete':
                deleteAttachment($aid, $pid);
                $deletes[] = $aid;
                break;
            default:
                break;
            }
        }
    }
    return $deletes;
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

function copyAllAttachments($frompid, $topid) {
    global $db;
    $frompid = intval($frompid);
    $topid = intval($topid);
    
    // Find all primary attachments for $frompid
    $query = $db->query("SELECT aid, subdir FROM ".X_PREFIX."attachments WHERE pid=$frompid AND parentid=0");
    while($attach = $db->fetch_array($query)) {
        $db->query("INSERT INTO ".X_PREFIX."attachments (pid, filename, filetype, filesize, attachment, img_size, uid, updatetime, subdir) "
                 . "SELECT {$topid}, filename, filetype, filesize, attachment, img_size, uid, updatetime, subdir FROM ".X_PREFIX."attachments WHERE aid={$attach['aid']}");
        if ($db->affected_rows() == 1) {
            $aid = $db->insert_id();
            if ($attach['subdir'] != '') {
                private_copyDiskAttachment($attach['aid'], $aid, $attach['subdir']);
            }
        }
        
        // Update any [file] object references in the new copy of the post messsage.
        $message = $db->query("SELECT message FROM ".X_PREFIX."posts WHERE pid=$topid");
        if ($message = $db->fetch_array($message)) {
            $newmessage = str_replace("[file]{$attach['aid']}[/file]", "[file]{$aid}[/file]", $message['message']);
            if ($newmessage != $message['message']) {
                $newmessage = $db->escape($newmessage);
                $db->query("UPDATE ".X_PREFIX."posts SET message='$newmessage' WHERE pid=$topid");
            }
        }
        
        // Find all children of this attachment and copy them too.
        $childquery = $db->query("SELECT aid, subdir FROM ".X_PREFIX."attachments WHERE pid=$frompid AND parentid={$attach['aid']}");
        while($childattach = $db->fetch_array($childquery)) {
            $db->query("INSERT INTO ".X_PREFIX."attachments (parentid, pid, filename, filetype, filesize, attachment, img_size, uid, updatetime, subdir) "
                     . "SELECT {$aid}, {$topid}, filename, filetype, filesize, attachment, img_size, uid, updatetime, subdir FROM ".X_PREFIX."attachments WHERE aid={$childattach['aid']}");
            if ($db->affected_rows() == 1) {
                $childaid = $db->insert_id();
                if ($childattach['subdir'] != '') {
                    private_copyDiskAttachment($childattach['aid'], $childaid, $childattach['subdir']);
                }
            }
        }
    }
}

function private_copyDiskAttachment($fromaid, $toaid, $subdir) {
    $path = getFullPathFromSubdir($subdir);
    if ($path !== FALSE) {
        if (is_file($path.$fromaid)) {
            copy($path.$fromaid, $path.$toaid);
        }
    }
}

function deleteAttachment($aid, $pid) {
    $aid = intval($aid);
    $pid = intval($pid);
    private_deleteAttachments("WHERE (aid=$aid OR parentid=$aid) AND pid=$pid");
}

function deleteAllAttachments($pid) {
    $pid = intval($pid);
    private_deleteAttachments("WHERE pid=$pid");
}

function deleteThreadAttachments($tid) {
    $tid = intval($tid);
    private_deleteAttachments("INNER JOIN ".X_PREFIX."posts USING (pid) WHERE tid=$tid");
}

function private_deleteAttachments($where) {
    global $db;
    $query = $db->query("SELECT aid, subdir FROM ".X_PREFIX."attachments $where");
    while($attachment = $db->fetch_array($query)) {
        $path = getFullPathFromSubdir($attachment['subdir']); // Returns FALSE if file stored in database.
        if ($path !== FALSE) {
            $path .= $attachment['aid'];
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    $db->query("DELETE ".X_PREFIX."attachments FROM ".X_PREFIX."attachments $where");
}

function get_attached_file($varname, &$filename, &$filetype, &$filesize, $dbescape=TRUE, $loadfile=TRUE) {
    global $db, $lang, $SETTINGS;

    $filename = '';
    $filetype = '';
    $filesize = 0;
    $attachment = '';

    if (isset($_FILES[$varname])) {
        $file = $_FILES[$varname];
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
                if ($loadfile) {
                    $attachment = $db->escape(file_get_contents($file['tmp_name']));
                }
                $filename = $db->escape($file['name']);
                $filetype = $db->escape(preg_replace('#[\\x00\\r\\n%]#', '', $file['type']));
            } else {
                if ($loadfile) {
                    $attachment = fread(fopen($file['tmp_name'], 'rb'), filesize($file['tmp_name']));
                }
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
    
    if ($SETTINGS['files_virtual_url'] == '') {
        $virtual_path = $full_url;
    } else {
        $virtual_path = $SETTINGS['files_virtual_url'];
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
        $url = "{$virtual_path}$pid/$aid/";
        break;
    case 5:
        $url = "{$virtual_path}$aid/".rawurlencode($filename);
        break;
    }

    return $url;
}

function getSizeFormatted($attachsize) {
    if ($attachsize >= 1073741824) {
        $attachsize = round($attachsize / 1073741824, 2)."GB";
    } else if ($attachsize >= 1048576) {
        $attachsize = round($attachsize / 1048576, 1)."MB";
    } else if ($attachsize >= 1024) {
        $attachsize = round($attachsize / 1024)."kB";
    } else {
        $attachsize = $attachsize."B";
    }
    return $attachsize;
}

// getNewSubdir returns the value that should be stored in the subdir column of a new row in the attachment table.
function getNewSubdir($date='') {
    global $SETTINGS;
    if ($date == '') {
        $date = time();
    }
    if ($SETTINGS['files_subdir_format'] == 1) {
        return gmdate('Y/m', $date);
    } else {
        return gmdate('Y/m/d', $date);
    }
}

// getFullPathFromSubdir() returns the concatenation of
// the file storage path and a specified subdir value.
// A trailing forward-slash is guaranteed in the return value.
// Returns FALSE if the file storage path is empty.
function getFullPathFromSubdir($subdir) {
    global $SETTINGS;
    $path = $SETTINGS['files_storage_path'];
    if (strlen($path) == 0) {
        return FALSE;
    }
    if (substr($path, -1) != '/') {
        $path .= '/';
    }
    $path .= $subdir;
    if (substr($path, -1) != '/') {
        $path .= '/';
    }
    return $path;
}

function createThumbnail($filename, $filepath, $filesize, $imgSize, $aid, $pid, $subdir) {
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
        $thumbSize = new CartesianSize($thumbMaxSize->width, round($thumbMaxSize->width / $imgSize->aspect()));
    } else {
        $thumbSize = new CartesianSize(round($imgSize->aspect() * $thumbMaxSize->height), $thumbMaxSize->height);
    }
    
    $extension = get_extension($filename);
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
    
    // Write full size and dimensions on thumbnail
    $string = getSizeFormatted($filesize).' '.$imgSize->width.'x'.$imgSize->height;
    $grey = imagecolorallocatealpha($thumb, 64, 64, 64, 96);
    imagefilledrectangle($thumb, 0, $thumbSize->height - 20, $thumbSize->width, $thumbSize->height, $grey);
    imagefttext($thumb, 10, 0, 5, $thumbSize->height - 5, imagecolorexact($thumb, 255,255,255), 'fonts/VeraMono.ttf', $string);

    $filename = $db->escape($filename.'-thumb.jpg');
    $filepath = $filepath.'-thumb.jpg';

    // Write to Disk
    imagejpeg($thumb, $filepath, 85);

    // Gather metadata
    $filesize = intval(filesize($filepath));
    $filetype = 'image/jpeg';
    $sqlsize = $thumbSize->width.'x'.$thumbSize->height;

    // Check minimum file size for disk storage
    if ($filesize < $SETTINGS['files_min_disk_size']) {
        $subdir = '';
    }

    // Add database record
    if ($subdir == '') {
        $file = $db->escape(fread(fopen($filepath, 'rb'), $filesize));
        unlink($filepath);
    } else {
        $file = '';
    }
    $db->query("INSERT INTO ".X_PREFIX."attachments (pid, filename, filetype, filesize, attachment, uid, parentid, img_size, subdir) VALUES ($pid, '$filename', '$filetype', $filesize, '$file', {$self['uid']}, $aid, '$sqlsize', '$subdir')");
    unset($file);
    if ($db->affected_rows() == 1) {
        $aid = $db->insert_id();
    } else {
        return FALSE;
    }
    if ($subdir != '') {
        $newfilename = $aid;
        rename($filepath, getFullPathFromSubdir($subdir).$newfilename);
    }
    
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

function extractRemoteImages($pid, &$message, &$message2) {
    // Sanity Checks
    if (!ini_get('allow_url_fopen')) {
        return FALSE;
    }

    // Extract img codes
    $results = array();
    $items = array();
    $pattern = '#\[img(=([0-9]*?){1}x([0-9]*?))?\]((http|ftp){1}://([:a-z\\./_\-0-9%~]+){1}(\?[a-z=0-9&_\-;~]*)?)\[/img\]#Smi';
    preg_match_all($pattern, $message, $results, PREG_SET_ORDER);
    foreach($results as $result) {
        if (isset($result[4])) {
            $item['code'] = $result[0];
            $item['url'] = $result[4];
            $items[] = $item;
        }
    }
    
    // Process URLs
    foreach($items as $result) {
        $aid = attachRemoteFile($result['url'], $pid);
        if ($aid === FALSE) {
            $replace = '[bad '.substr($item['code'], 4, -5).'[/bad img]';
        } else {
            $replace = "[file]{$aid}[/file]";
        }
        $temppos = strpos($message, $item['code']);
        $message = substr($message, 0, $temppos).$replace.substr($message, $temppos + strlen($item['code']));
        if ($message2 != '') {
            $temppos = strpos($message2, $item['code']);
            $message2 = substr($message2, 0, $temppos).$replace.substr($message2, $temppos + strlen($item['code']));
        }
    }
}
?>
