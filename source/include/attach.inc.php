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

define('X_EMPTY_UPLOAD', -1);
define('X_BAD_STORAGE_PATH', -2);
define('X_ATTACH_COUNT_EXCEEDED', -3);
define('X_ATTACH_SIZE_EXCEEDED', -4);
define('X_IMAGE_DIMS_EXCEEDED', -5);
define('X_INVALID_REMOTE_LINK', -6);
define('X_NOT_AN_IMAGE', -7);
define('X_NO_TEMP_FILE', -8);
define('X_GENERIC_ATTACH_ERROR', -9);
define('X_INVALID_FILENAME', -10);

$attachmentErrors = array(
X_BAD_STORAGE_PATH      => $lang['fileuploaderror1'],
X_ATTACH_COUNT_EXCEEDED => $lang['fileuploaderror2'],
X_INVALID_REMOTE_LINK   => $lang['fileuploaderror3'],
X_NOT_AN_IMAGE          => $lang['fileuploaderror4'],
X_IMAGE_DIMS_EXCEEDED   => $lang['fileuploaderror5'],
X_ATTACH_SIZE_EXCEEDED  => $lang['fileuploaderror6'],
X_NO_TEMP_FILE          => $lang['fileuploaderror7'],
X_GENERIC_ATTACH_ERROR  => $lang['fileuploaderror8'],
X_INVALID_FILENAME      => $lang['invalidFilename']);

/**
 * Attaches a single uploaded file to a specific forum post.
 *
 * attachUploadedFile() checks for the presence of $_FILES[$varname].
 * If found, the file will be stored and attached to the specified $pid.
 * The $pid can be omitted in post preview mode, thus creating
 * orphaned attachments that the registered user will be allowed to manage.
 * Storage responsibilities include subdirectory and thumbnail creation.
 *
 * @param string $varname Form variable name, used in the $_FILES associative index.
 * @param int $pid Optional. PID of the related post. Attachment becomes orphaned if omitted.
 * @return int AID of the new attachment on success.  Index into the $attachmentErrors array on failure.
 * @author Robert Chapin (miqrogroove)
 */
function attachUploadedFile($varname, $pid=0) {
    global $attachmentErrors, $db, $self, $SETTINGS;

    $path = getFullPathFromSubdir('');
    $pid = intval($pid);
    $usedb = TRUE;

    if ($path !== FALSE) {
        if (is_dir($path)) {
            $usedb = FALSE;
        } else {
            header('HTTP/1.0 500 Internal Server Error');
            exit($attachmentErrors[X_BAD_STORAGE_PATH]);
        }
    }

    $file = get_attached_file($varname, $filename, $filetype, $filesize, TRUE, $usedb);
    if ($file === FALSE) {
        return $filetype;
    }

    // Sanity checks
    if ($pid == 0 And intval($self['uid']) <= 0) {
        return X_GENERIC_ATTACH_ERROR;
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
        return X_ATTACH_COUNT_EXCEEDED;
    }

    // Check minimum file size for disk storage
    if ($filesize < $SETTINGS['files_min_disk_size'] And !$usedb) {
        $usedb = TRUE;
        $file = get_attached_file($varname, $filename, $filetype, $filesize, TRUE, $usedb);
    }

    return private_attachGenericFile($pid, $usedb, $file, $_FILES[$varname]['tmp_name'], $filename, $_FILES[$varname]['name'], $filetype, $filesize);
}

function attachRemoteFile($url, $pid=0) {
    global $attachmentErrors, $db, $self, $SETTINGS;

    $path = getFullPathFromSubdir('');
    $pid = intval($pid);
    $usedb = TRUE;

    if ($path !== FALSE) {
        if (is_dir($path)) {
            $usedb = FALSE;
        } else {
            header('HTTP/1.0 500 Internal Server Error');
            exit($attachmentErrors[X_BAD_STORAGE_PATH]);
        }
    }
    $filepath = getTempFile($path);

    // Sanity checks
    if (substr($url, 0, 7) != 'http://' And substr($url, 0, 6) != 'ftp://') {
        return X_INVALID_REMOTE_LINK;
    }
    $urlparts = parse_url($url);
    if ($urlparts === FALSE) {
        return X_INVALID_REMOTE_LINK;
    }
    if (!isset($urlparts['path'])) { // Parse was successful but $url had no path
        return X_INVALID_REMOTE_LINK;
    }
    if ($urlparts['path'] == '/') {
        return X_INVALID_REMOTE_LINK;
    }
    $filename = FALSE;
    $urlparts = explode('/', $urlparts['path']);
    for($i=count($urlparts)-1; $i>=0; $i--) {
        if (isValidFilename($urlparts[$i])) {
            $filename = $urlparts[$i];
            break;
        } elseif (isValidFilename(urldecode($urlparts[$i]))) {
            $filename = urldecode($urlparts[$i]);
            break;
        }
    }
    if ($filename === FALSE) { //Failed to find a usable filename in $url.
        $filename = explode('/', $filepath);
        $filename = array_pop($filename);
    }
    $dbfilename = $db->escape($filename);
    if ($pid == 0 And intval($self['uid']) <= 0) {
        return X_GENERIC_ATTACH_ERROR;
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
        return X_ATTACH_COUNT_EXCEEDED;
    }

    // Now grab the remote file
    if (DEBUG) {
        $file = file_get_contents($url);
    } else {
        $file = @file_get_contents($url);
    }
    if ($file === FALSE) {
        return X_INVALID_REMOTE_LINK;
    }

    $filesize = strlen($file);
    if ($filesize > $SETTINGS['maxattachsize']) {
        return X_ATTACH_SIZE_EXCEEDED;
    }

    // Write to disk
    $handle = fopen($filepath, 'wb');
    if ($handle === FALSE) {
        return X_NO_TEMP_FILE;
    }
    fwrite($handle, $file);
    fclose($handle);

    // Verify that the file is actually an image.
    $result = getimagesize($filepath);
    if ($result === FALSE) {
        unlink($filepath);
        return X_NOT_AN_IMAGE;
    }
    $filetype = image_type_to_mime_type($result[2]);
    $db->escape_fast($filetype);

    // Try to make sure the filename extension is okay
    $extension = strtolower(get_extension($filename));
    $img_extensions = array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'wbmp', 'wbm', 'bmp');
    if (!in_array($extension, $img_extensions)) {
        $extension = '';
        $filetypei = strtolower($filetype);
        if (strpos($filetypei, 'jpeg') !== FALSE) {
            $extension = '.jpg';
        } elseif (strpos($filetypei, 'gif') !== FALSE) {
            $extension = '.gif';
        } elseif (strpos($filetypei, 'wbmp') !== FALSE) {
            $extension = '.wbmp';
        } elseif (strpos($filetypei, 'bmp') !== FALSE) {
            $extension = '.bmp';
        } elseif (strpos($filetypei, 'png') !== FALSE) {
            $extension = '.png';
        }
        $filename .= $extension;
        $dbfilename .= $extension;
    }

    // Check minimum file size for disk storage
    if (!$usedb) {
        if ($filesize < $SETTINGS['files_min_disk_size']) {
            $usedb = TRUE;
        } else {
            $file = '';
        }
    }

    $db->escape_fast($file);
    $aid = private_attachGenericFile($pid, $usedb, $file, $filepath, $dbfilename, $filename, $filetype, $filesize);

    // Clean up disk if attachment failed.
    if ($aid <= 0) {
        unlink($filepath);
    }

    return $aid;
}

function private_attachGenericFile($pid, $usedb, &$dbfile, &$filepath, &$dbfilename, &$rawfilename, &$dbfiletype, $dbfilesize) {
    global $db, $self, $SETTINGS;

    // Check if we can store image metadata
    $extension = strtolower(get_extension($rawfilename));
    $img_extensions = array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'wbmp', 'wbm', 'bmp');
    if (in_array($extension, $img_extensions)) {
        $result = getimagesize($filepath);
    } else {
        $result = FALSE;
    }

    $sqlsize = '';
    if ($result !== FALSE) {
        $imgSize = new CartesianSize($result[0], $result[1]);
        $sqlsize = $result[0].'x'.$result[1];

        $maxsize = explode('x', $SETTINGS['max_image_size']);
        if ($maxsize[0] > 0 and $maxsize[1] > 0) {
            $maxImgSize = new CartesianSize($maxsize[0], $maxsize[1]);
            if ($imgSize->isBiggerThan($maxImgSize)) {
                return X_IMAGE_DIMS_EXCEEDED;
            }
        }

        // Coerce filename extension and mime type when they are incorrect.
        $filetypei = strtolower($dbfiletype);
        switch($result[2]) {
        case IMAGETYPE_JPEG:
            if ($extension != 'jpg' and $extension != 'jpeg' and $extension != 'jpe') {
                $dbfilename .= '.jpg';
                $rawfilename .= '.jpg';
            }
            if (strpos($filetypei, 'jpeg') === FALSE) {
                $dbfiletype = 'image/jpeg';
            }
            break;
        case IMAGETYPE_GIF:
            if ($extension != 'gif') {
                $dbfilename .= '.gif';
                $rawfilename .= '.gif';
            }
            if (strpos($filetypei, 'gif') === FALSE) {
                $dbfiletype = 'image/gif';
            }
            break;
        case IMAGETYPE_PNG:
            if ($extension != 'png') {
                $dbfilename .= '.png';
                $rawfilename .= '.png';
            }
            if (strpos($filetypei, 'png') === FALSE) {
                $dbfiletype = 'image/png';
            }
            break;
        case IMAGETYPE_BMP:
            if ($extension != 'bmp') {
                $dbfilename .= '.bmp';
                $rawfilename .= '.bmp';
            }
            if (strpos($filetypei, 'bmp') === FALSE) {
                $dbfiletype = 'image/bmp';
            }
            break;
        case 15: //IMAGETYPE_WBMP
            if ($extension != 'wbmp' and $extension != 'wbm') {
                $dbfilename .= '.wbmp';
                $rawfilename .= '.wbmp';
            }
            if (strpos($filetypei, 'wbmp') === FALSE) {
                $dbfiletype = 'image/vnd.wap.wbmp';
            }
            break;
        }
    }

    // Store File
    if ($usedb) {
        $subdir = '';
    } else {
        $dbfile = '';
        $subdir = getNewSubdir();
        $path = getFullPathFromSubdir($subdir, TRUE);
    }
    $db->query("INSERT INTO ".X_PREFIX."attachments (pid, filename, filetype, filesize, attachment, uid, img_size, subdir) VALUES ($pid, '$dbfilename', '$dbfiletype', $dbfilesize, '$dbfile', {$self['uid']}, '$sqlsize', '$subdir')");
    $dbfile = '';
    if ($db->affected_rows() == 1) {
        $aid = $db->insert_id();
    } else {
        return X_GENERIC_ATTACH_ERROR;
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
        createThumbnail($rawfilename, $path, $dbfilesize, $imgSize, '', $aid, $pid, $subdir);
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

function doAttachmentEdits(&$deletes, $pid=0) {
    $return = TRUE;
    $deletes = array();
    if (isset($_POST['attachment']) && is_array($_POST['attachment'])) {
        $pid = intval($pid);
        foreach($_POST['attachment'] as $aid => $attachment) {
            switch($attachment['action']) {
            case 'replace':
                deleteAttachment($aid, $pid);
                $deletes[] = $aid;
                $status = attachUploadedFile('replace_'.$aid, $pid);
                if ($status < 0 And $status != X_EMPTY_UPLOAD) {
                    $return = $status;
                }
                break;
            case 'rename':
                $rename = trim(postedVar('rename_'.$aid, '', FALSE, FALSE));
                $status = renameAttachment($aid, $pid, $rename);
                if ($status < 0) {
                    $return = $status;
                }
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
    return $return;
}

function renameAttachment($aid, $pid, $rawnewname) {
    global $db;
    if (isValidFilename($rawnewname)) {
        $aid = intval($aid);
        $pid = intval($pid);
        $extension = strtolower(get_extension($rawnewname));
        $db->escape_fast($rawnewname);
        $db->query("UPDATE ".X_PREFIX."attachments SET filename='$rawnewname' WHERE aid=$aid AND pid=$pid");
        $img_extensions = array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'wbmp', 'wbm', 'bmp');
        if (in_array($extension, $img_extensions)) {
            $query = $db->query("SELECT aid FROM ".X_PREFIX."attachments WHERE parentid=$aid AND pid=$pid AND filename LIKE '%-thumb.jpg'");
            if ($db->num_rows($query) == 0) {
                regenerateThumbnail($aid, $pid);
            }
        }
        return TRUE;
    } else {
        return X_INVALID_FILENAME;
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
                $db->escape_fast($newmessage);
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

// Important: call deleteThreadAttachments() BEFORE deleting posts, because it uses a multi-table query.
function deleteThreadAttachments($tid) {
    $tid = intval($tid);
    private_deleteAttachments("INNER JOIN ".X_PREFIX."posts USING (pid) WHERE tid=$tid");
}
function emptyThreadAttachments($tid, $pid) {
    $tid = intval($tid);
    $pid = intval($pid);
    private_deleteAttachments("INNER JOIN ".X_PREFIX."posts AS p USING (pid) WHERE p.tid=$tid AND p.pid!=$pid");
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

/**
 * Retrieves information about the specified file upload.
 *
 * This function sets appropriate error levels and returns several variables.
 * This function does not provide the upload path, which is $_FILES[$varname]['tmp_name']
 * All return values must be treated as invalid if (FALSE === get_attached_file(...)).
 *
 * @param string $varname The name of the file input on the form.
 * @param string $filename Variable Required. Returns the filename provided by the user. Uses param $dbescape.
 * @param string|int $filetype Variable Required. Returns the MIME type provided by the user on success. Returns one of the $attachmentErrors constants on failure. Uses param $dbescape.
 * @param int    $filesize Variable Required. Returns the actual byte size of the uploaded file.
 * @param bool   $dbescape Optional. When set to TRUE, the string parameters and the return value itself will all be SQL sanitized.
 * @param bool   $loadfile Optional. When set to TRUE, the uploaded file will be loaded into memory and returned as a string value.
 * @return string|bool The uploaded file or an empty string will be returned on success. FALSE on failure. Uses params $dbescape and $loadfile.
 */
function get_attached_file($varname, &$filename, &$filetype, &$filesize, $dbescape=TRUE, $loadfile=TRUE) {
    global $db, $SETTINGS;

    // Initialize Return Values
    $attachment = '';
    $filename = '';
    $filesize = 0;
    $filetype = '';


    /* Perform Sanity Checks */

    if (isset($_FILES[$varname])) {
        $file = $_FILES[$varname];
    } else {
        $filetype = X_EMPTY_UPLOAD;
        return FALSE;
    }

    if (UPLOAD_ERR_OK != $file['error']) {
        switch($file['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $filetype = X_ATTACH_SIZE_EXCEEDED;
            break;
        case UPLOAD_ERR_NO_FILE:
            $filetype = X_EMPTY_UPLOAD;
            break;
        case 6: //UPLOAD_ERR_NO_TMP_DIR
            header('HTTP/1.0 500 Internal Server Error');
            exit('Fatal Error: XMB can\'t find the upload_tmp_dir. This is a PHP server configuration fault.');
            break;
        default:
            // See the PHP Manual for additional information.
            if (DEBUG and is_numeric($file['error'])) {
                header('HTTP/1.0 500 Internal Server Error');
                exit('XMB Upload Haulted by PHP error code '.$file['error']);
            }
            $filetype = X_GENERIC_ATTACH_ERROR;
        }
        return FALSE;
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        $filetype = X_EMPTY_UPLOAD;
        return FALSE;
    }

    if (!is_readable($file['tmp_name'])) {
        header('HTTP/1.0 500 Internal Server Error');
        exit('Fatal Error: XMB does not have read permission in the upload_tmp_dir. This is a PHP server security fault.');
    }

    $file['name'] = trim($file['name']);
    if (!isValidFilename($file['name'])) {
        $file['name'] = basename($file['tmp_name']);
        if (!isValidFilename($file['name'])) {
            unlink($file['tmp_name']);
            $filetype = X_INVALID_FILENAME;
            return FALSE;
        }
    }

    $filesize = intval(filesize($file['tmp_name'])); // fix bad filesizes (PHP Bug #45124, etc)
    if ($filesize > $SETTINGS['maxattachsize']) {
        unlink($file['tmp_name']);
        $filetype = X_ATTACH_SIZE_EXCEEDED;
        return FALSE;
    }
    if ($filesize == 0) {
        unlink($file['tmp_name']);
        $filetype = X_EMPTY_UPLOAD;
        return FALSE;
    }


    /* Set Return Values */

    if ($loadfile) {
        $attachment = file_get_contents($file['tmp_name']);
    }
    $filename = $file['name'];
    $filetype = preg_replace('#[\\x00\\r\\n%]#', '', $file['type']);

    if ($dbescape) {
        $db->escape_fast($attachment);
        $db->escape_fast($filename);
        $db->escape_fast($filetype);
    }

    return $attachment;
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

/**
 * Generates the value that should be stored in the subdir column of a new row in the attachment table.
 *
 * @param string $date Optional. Unix timestamp of the attachment, if not now.
 * @return string
 */
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

/**
 * Retrieve the full path given just a subdirectory name.
 *
 * getFullPathFromSubdir() returns the concatenation of
 * the file storage path and a specified subdir value.
 * A trailing forward-slash is guaranteed in the return value.
 *
 * @param string $subdir The name typically has no leading or trailing slashes, e.g. 'dir1' or 'dir2/sub3'
 * @param bool   $mkdir  Optional.  TRUE causes specified subdirectory to be created in a PHP4-compatible manner.
 * @return string|bool FALSE if the file storage path is empty.
 */
function getFullPathFromSubdir($subdir, $mkdir = FALSE) {
    global $SETTINGS;
    $path = $SETTINGS['files_storage_path'];
    if (strlen($path) == 0) {
        return FALSE;
    }
    if (substr($path, -1) != '/') {
        $path .= '/';
    }
    if ($mkdir) {
        $dirs = explode('/', $subdir);
        foreach($dirs as $value) {
            if (strlen($value) != 0) {
                $path .= $value.'/';
                if (!is_dir($path)) {
                    mkdir($path, 0777);
                }
            }
        }
    } else {
        $path .= $subdir;
        if (substr($path, -1) != '/') {
            $path .= '/';
        }
    }
    return $path;
}

function getTempFile($path=FALSE) {
    global $attachmentErrors;

    $filepath = FALSE;
    if ($path !== FALSE) {
        $filepath = @tempnam($path, 'xmb-');
    }
    if (!is_writable($filepath)) {
        $filepath = @tempnam('', 'xmb-');
    }
    if (!is_writable($filepath)) {
        header('HTTP/1.0 500 Internal Server Error');
        exit($attachmentErrors[X_NO_TEMP_FILE]);
    }
    return $filepath;
}

/**
 * Uses the path to an image to create a resized image based on global settings.
 *
 * The thumbnail will be attached to its corresponding parent image and post if the last three parameters are set.
 * Otherwise, the thumbnail will simply be saved to disk at $filepath.'-thumb.jpg'
 *
 * @param string $filename Original name of the input file.
 * @param string $filepath Current name and location (full path) of the input file.
 * @param int    $filesize The size, in bytes, that you want printed on the thumbnail.
 * @param object $imgSize  Caller must construct a CartesianSize object to specify the dimensions of the input image.
 * @param string $dep1     This parameter is ignored.
 * @param int    $aid      Optional. AID to be used as the parentid if attaching the thumbnail to a post.
 * @param int    $pid      Optional. PID to attach the thumbnail to.
 * @param string $subdir   Optional. Subdirectory to use inside the file storage path, or null string to store it in the database.
 * @return bool
 */
function createThumbnail($filename, $filepath, $filesize, $imgSize, $dep1, $aid=0, $pid=0, $subdir='') {
    global $db, $self, $SETTINGS;

    // Determine if a thumbnail is needed.
    $result = explode('x', $SETTINGS['max_thumb_size']);
    if ($result[0] > 0 And $result[1] > 0) {
        $thumbSize = new CartesianSize($result[0], $result[1]);
    } else {
        return FALSE;
    }

    $thumb = load_and_resize_image($filepath, $thumbSize->width, $thumbSize->height);

    if (FALSE === $thumb) {
        return FALSE;
    }

    // Write full size and dimensions on thumbnail
    if (function_exists('imagefttext')) {
        $string = getSizeFormatted($filesize).' '.$imgSize->width.'x'.$imgSize->height;
        $grey = imagecolorallocatealpha($thumb, 64, 64, 64, 80);
        imagefilledrectangle($thumb, 0, $thumbSize->height - 20, $thumbSize->width, $thumbSize->height, $grey);
        imagefttext($thumb, 10, 0, 5, $thumbSize->height - 5, imagecolorexact($thumb, 255,255,255), 'fonts/VeraMono.ttf', $string);
    }

    $filepath .= '-thumb.jpg';
    $filename .= '-thumb.jpg';
    $db->escape_fast($filename);

    // Write to Disk
    imagejpeg($thumb, $filepath, 85);
    imagedestroy($thumb);

    // Gather metadata
    $filesize = intval(filesize($filepath));
    $filetype = 'image/jpeg';
    $sqlsize = $thumbSize->width.'x'.$thumbSize->height;

    // Attach thumbnail to the post
    if ($aid != 0) {

        // Check minimum file size for disk storage
        if ($filesize < $SETTINGS['files_min_disk_size']) {
            $subdir = '';
        }

        // Add database record
        if ($subdir == '') {
            $file = file_get_contents($filepath);
            $db->escape_fast($file);
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
    }
    return TRUE;
}

/**
 * Uses the path to an image file to create a resized image resource in memory.
 *
 * @since 1.9.11.12
 * @param string $path Current name and location (full path) of the input file.
 * @param int    $width Takes the width limit.  Returns the actual width.
 * @param int    $height Takes the height limit.  Returns the actual height.
 * @param bool   $load_if_smaller Do you want to load the image if it's smaller than both $width and $height?
 * @param bool   $enlarge_if_smaller Do you want to resize the image if it's smaller than both $width and $height?
 * @return resource|bool The image GD resource on success.  FALSE when $path is not an image file, or if the image is larger than $SETTINGS['max_image_size'].
 */
function load_and_resize_image($path, &$width, &$height, $load_if_smaller = FALSE, $enlarge_if_smaller = FALSE) {
    global $SETTINGS;

    // Check if GD is available
    if (!function_exists('imagecreatetruecolor')) {
        return FALSE;
    }

    $result = getimagesize($path);

    if (FALSE === $result) {
        return FALSE;
    }

    $imgSize = new CartesianSize($result[0], $result[1]);

    $maxsize = explode('x', $SETTINGS['max_image_size']);
    if ($maxsize[0] > 0 and $maxsize[1] > 0) {
        $maxImgSize = new CartesianSize($maxsize[0], $maxsize[1]);
        if ($imgSize->isBiggerThan($maxImgSize)) {
            return FALSE;
        }
    }

    // Load the image.
    switch($result[2]) {
    case IMAGETYPE_JPEG:
        $img = @imagecreatefromjpeg($path);
        break;
    case IMAGETYPE_GIF:
        $img = @imagecreatefromgif($path);
        break;
    case IMAGETYPE_PNG:
        $img = @imagecreatefrompng($path);
        break;
    case IMAGETYPE_BMP:
        // See our website for drop-in BMP support.
        if (!class_exists('phpthumb_bmp')) {
            if (is_file(ROOT.'include/phpthumb-bmp.php')) {
                require_once(ROOT.'include/phpthumb-bmp.php');
            }
        }
        if (class_exists('phpthumb_bmp')) {
            $ns = new phpthumb_bmp;
            $img = $ns->phpthumb_bmpfile2gd($path);
        } else {
            $img = FALSE;
        }
        break;
    case 15: //IMAGETYPE_WBMP
        $img = @imagecreatefromwbmp($path);
        break;
    default:
        return FALSE;
    }

    if (!$img) {
        return FALSE;
    }

    // Determine if a thumbnail is needed.
    $thumbMaxSize = new CartesianSize($width, $height);

    if ($imgSize->isSmallerThan($thumbMaxSize)) {
        if (!$load_if_smaller) {
            return FALSE;
        } elseif (!$enlarge_if_smaller) {
            $width  = $imgSize->width;
            $height = $imgSize->height;
            return $img;
        }
    }

    // Create a thumbnail for this attachment.
    if ($imgSize->aspect() > $thumbMaxSize->aspect()) {
        $thumbSize = new CartesianSize($thumbMaxSize->width, round($thumbMaxSize->width / $imgSize->aspect()));
    } else {
        $thumbSize = new CartesianSize(round($imgSize->aspect() * $thumbMaxSize->height), $thumbMaxSize->height);
    }

    $thumb = imagecreatetruecolor($thumbSize->width, $thumbSize->height);

    // Resize $img
    if (!imagecopyresampled($thumb, $img, 0, 0, 0, 0, $thumbSize->width, $thumbSize->height, $imgSize->width, $imgSize->height)) {
        return FALSE;
    }

    imagedestroy($img);

    $width  = $thumbSize->width;
    $height = $thumbSize->height;
    return $thumb;
}

function regenerateThumbnail($aid, $pid) {
    global $db, $SETTINGS;
    $aid = intval($aid);
    $pid = intval($pid);

    // Initialize
    $path = getFullPathFromSubdir('');
    $usedb = TRUE;

    // Write attachment to disk
    $query = $db->query("SELECT *, UNIX_TIMESTAMP(updatetime) AS updatestamp FROM ".X_PREFIX."attachments WHERE aid=$aid AND pid=$pid");
    if ($db->num_rows($query) != 1) {
        return FALSE;
    }
    $attach = $db->fetch_array($query);
    $db->free_result($query);
    if ($attach['subdir'] == '') {
        if (strlen($attach['attachment']) != $attach['filesize']) {
            return FALSE;
        }
        $subdir = getNewSubdir($attach['updatestamp']);
        $path = getFullPathFromSubdir($subdir, TRUE);
        if ($path === FALSE) {
            $path = getTempFile();
        } else {
            $newfilename = $aid;
            $path .= $newfilename;
        }
        $file = fopen($path, 'wb');
        if ($file === FALSE) {
            return FALSE;
        }
        fwrite($file, $attach['attachment']);
        fclose($file);
        unset($attach['attachment']);
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
        if ($attach['subdir'] == '') {
            unlink($path);
        }
        return FALSE;
    }
    $imgSize = new CartesianSize($result[0], $result[1]);
    $sqlsize = $result[0].'x'.$result[1];

    $result = explode('x', $SETTINGS['max_image_size']);
    if ($result[0] > 0 And $result[1] > 0) {
        $maxImgSize = new CartesianSize($result[0], $result[1]);
        if ($imgSize->isBiggerThan($maxImgSize)) {
            if ($attach['subdir'] == '') {
                unlink($path);
            }
            return X_IMAGE_DIMS_EXCEEDED;
        }
    }

    if ($attach['img_size'] != $sqlsize) {
        $db->query("UPDATE ".X_PREFIX."attachments SET img_size='$sqlsize' WHERE aid=$aid AND pid=$pid");
    }

    deleteThumbnail($aid, $pid);
    createThumbnail($attach['filename'], $path, $attach['filesize'], $imgSize, '', $aid, $pid, $attach['subdir']);

    // Clean up temp files
    if ($attach['subdir'] == '') {
        unlink($path);
    }
    return TRUE;
}

function deleteThumbnail($aid, $pid) {
    $aid = intval($aid);
    $pid = intval($pid);
    private_deleteAttachments("WHERE parentid=$aid AND pid=$pid AND filename LIKE '%-thumb.jpg'");
}

/**
 * Rectangluar dimension object for simple operations and properties.
 */
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

function extractRemoteImages($pid, &$message) {
    // Sanity Checks
    if (!ini_get('allow_url_fopen')) {
        return TRUE;
    }

    // Remove the code block contents from $message.
    $messagearray = bbcodeCode($message);
    $message = array();
    for($i = 0; $i < count($messagearray); $i += 2) {
        $message[$i] = $messagearray[$i];
    }
    $message = implode("<!-- code -->", $message);

    // Extract img codes
    $results = array();
    $items = array();
    $pattern = '#\[img(=([0-9]*?){1}x([0-9]*?))?\]((http|ftp){1}://([:a-z\\./_\-0-9%~]+){1}(\?[a-z=0-9&_\-;~]*)?)\[/img\]#Smi';
    preg_match_all($pattern, $message, $results, PREG_SET_ORDER);
    foreach($results as $result) {
        if (isset($result[4])) {
            $item['code'] = $result[0];
            $item['url'] = htmlspecialchars_decode($result[4], ENT_NOQUOTES);
            $items[] = $item;
        }
    }

    $return = TRUE;

    // Process URLs
    foreach($items as $result) {
        $aid = attachRemoteFile($result['url'], $pid);
        if ($aid <= 0) {
            $return = $aid;
            $replace = '[bad '.substr($result['code'], 1, -6).'[/bad img]';
        } else {
            $replace = "[file]{$aid}[/file]";
        }
        $temppos = strpos($message, $result['code']);
        $message = substr($message, 0, $temppos).$replace.substr($message, $temppos + strlen($result['code']));
    }

    // Replace the code block contents in $message.
    if (count($messagearray) > 1) {
        $message = explode("<!-- code -->", $message);
        for($i = 0; $i < count($message) - 1; $i++) {
            $message[$i] .= $messagearray[$i*2+1];
        }
        $message = implode("", $message);
    }

    return $return;
}
?>
