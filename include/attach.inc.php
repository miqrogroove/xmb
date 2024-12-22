<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2024, The XMB Group
 * https://www.xmbforum2.com/
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace XMB;

use LogicException;
use RuntimeException;

class Attach
{
    public function __construct(private BBCode $bbcode, private DBStuff $db, private SQL $sql)
    {
        // Property promotion.
    }

    /**
     * Attaches a single uploaded file to a specific forum post.
     *
     * uploadedFile() checks for the presence of $_FILES[$varname].
     * If found, the file will be stored and attached to the specified $pid.
     * The $pid can be omitted in post preview mode, thus creating
     * orphaned attachments that the registered user will be allowed to manage.
     * Storage responsibilities include subdirectory and thumbnail creation.
     *
     * @since 1.9.11
     * @param string $varname Form variable name, used in the $_FILES associative index.
     * @param int $pid Optional. PID of the related post. Attachment becomes orphaned if omitted.
     * @param bool $quarantine Save this record in a private table for later review?
     * @return UploadResult
     */
    public function uploadedFile(string $varname, int $pid = 0, bool $quarantine = false): UploadResult
    {
        global $self, $SETTINGS;

        $usedb = true;
        if (! $quarantine) {
            $path = $this->getFullPathFromSubdir('');

            if ($path != '') {
                if (is_dir($path)) {
                    $usedb = false;
                } else {
                    header('HTTP/1.0 500 Internal Server Error');
                    exit(uploadErrorMsg(UploadResult::BadStoragePath));
                }
            }
        }

        $result = $this->getUpload($varname, $usedb);
        if ($result->status !== UploadStatus::Success) {
            return $result;
        }

        // Sanity checks
        if ($pid == 0 && intval($self['uid']) <= 0) {
            return new UploadResult(UploadStatus::GenericError);
        }

        // Check maximum attachments per post
        if ($pid == 0) {
            $count = $this->sql->countOrphanedAttachments((int) $self['uid'], $quarantine);
        } else {
            $count = $this->sql->countAttachmentsByPost($pid, $quarantine);
        }
        if ($count >= (int) $SETTINGS['filesperpost']) {
            return new UploadResult(UploadStatus::CountExceeded);
        }

        // Check minimum file size for disk storage
        if ($result->filesize < (int) $SETTINGS['files_min_disk_size'] && !$usedb) {
            $usedb = true;
            $result = $this->getUpload($varname, $usedb);
        }

        return $this->genericFile($pid, $usedb, $_FILES[$varname]['tmp_name'], $quarantine, $result);
    }

    /**
     * Attaches a single remote file to a specific forum post.
     *
     * remoteFile() checks the validity of $url.
     * If found, the file will be stored and attached to the specified $pid.
     * The $pid can be omitted in post preview mode, thus creating
     * orphaned attachments that the registered user will be allowed to manage.
     * Storage responsibilities include subdirectory and thumbnail creation.
     *
     * @since 1.9.11
     * @param string $url Web address of the remote file.
     * @param int $pid Optional. PID of the related post. Attachment becomes orphaned if omitted.
     * @param bool $quarantine Save this record in a private table for later review?
     * @return UploadResult
     */
    private function remoteFile(string $url, int $pid = 0, bool $quarantine = false): UploadResult
    {
        global $self, $SETTINGS;

        $usedb = true;
        $path = '';
        if (! $quarantine) {
            $path = $this->getFullPathFromSubdir('');

            if ($path != '') {
                if (is_dir($path)) {
                    $usedb = false;
                } else {
                    header('HTTP/1.0 500 Internal Server Error');
                    exit($this->uploadErrorMsg(UploadResult::BadStoragePath));
                }
            }
        }
        $filepath = $this->getTempFile($path);

        // Sanity checks
        if (1 != preg_match('/^' . get_img_regexp() . '$/i', $url)) {
            return new UploadResult(UploadStatus::InvalidURL);
        }
        $urlparts = parse_url($url);
        if ($urlparts === FALSE) {
            return new UploadResult(UploadStatus::InvalidURL);
        }
        if (!isset($urlparts['path'])) { // Parse was successful but $url had no path
            return new UploadResult(UploadStatus::InvalidURL);
        }
        if ($urlparts['path'] == '/') {
            return new UploadResult(UploadStatus::InvalidURL);
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

        if ($pid == 0 && intval($self['uid']) <= 0) {
            return new UploadResult(UploadStatus::GenericError);
        }

        // Check maximum attachments per post
        if ($pid == 0) {
            $count = $this->sql->countOrphanedAttachments((int) $self['uid'], $quarantine);
        } else {
            $count = $this->sql->countAttachmentsByPost($pid, $quarantine);
        }
        if ($count >= (int) $SETTINGS['filesperpost']) {
            return new UploadResult(UploadStatus::CountExceeded);
        }

        // Now grab the remote file
        if (DEBUG) {
            $file = file_get_contents($url);
        } else {
            $file = @file_get_contents($url);
        }
        if ($file === false) {
            return new UploadResult(UploadStatus::InvalidURL);
        }

        $filesize = strlen($file);
        if ($filesize > (int) $SETTINGS['maxattachsize']) {
            return new UploadResult(UploadStatus::SizeExceeded);
        }

        // Write to disk
        $handle = fopen($filepath, 'wb');
        if ($handle === FALSE) {
            return new UploadResult(UploadStatus::NoTempFile);
        }
        fwrite($handle, $file);
        fclose($handle);

        // Verify that the file is actually an image.
        $result = getimagesize($filepath);
        if ($result === FALSE) {
            unlink($filepath);
            return new UploadResult(UploadStatus::NotAnImage);
        }
        $filetype = image_type_to_mime_type($result[2]);

        // Try to make sure the filename extension is okay
        $extension = strtolower(get_extension($filename));
        $img_extensions = array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'wbmp', 'wbm', 'bmp', 'ico');
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
            } elseif (strpos($filetypei, 'ico') !== FALSE) {
                $extension = '.ico';
            }
            $filename .= $extension;
        }

        // Check minimum file size for disk storage
        if (!$usedb) {
            if ($filesize < (int) $SETTINGS['files_min_disk_size']) {
                $usedb = true;
            } else {
                $file = '';
            }
        }

        $result = new UploadResult(UploadStatus::Success);
        $result->binaryFile = &$file;
        $result->filename = $filename;
        $result->filetype = $filetype;
        $result->filesize = $filesize;
        unset($file); // Avoid accidental re-use.

        $result = $this->genericFile($pid, $usedb, $filepath, $quarantine, $result);

        // Clean up disk if attachment failed.
        if ($result->status !== UploadStatus::Success) {
            unlink($filepath);
        }

        return $result;
    }

    private function genericFile(int $pid, bool $usedb, string &$filepath, bool $quarantine, UploadResult $result): UploadResult
    {
        global $self, $SETTINGS;

        // Check if we can store image metadata
        $extension = strtolower(get_extension($result->filename));
        $img_extensions = array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'wbmp', 'wbm', 'bmp', 'ico');
        if (in_array($extension, $img_extensions)) {
            $sizeArray = getimagesize($filepath);
        } else {
            $sizeArray = false;
        }

        $sqlsize = '';
        if ($sizeArray !== false) {
            $imgSize = new CartesianSize();
            $imgSize->fromArray($sizeArray);
            $sqlsize = (string) $imgSize;

            $maxImgSize = new CartesianSize();
            if ($maxImgSize->fromString($SETTINGS['max_image_size'])) {
                if ($imgSize->isBiggerThan($maxImgSize)) {
                    return new UploadResult(UploadStatus::DimsExceeded);
                }
            }

            // Coerce filename extension and mime type when they are incorrect.
            $filetypei = strtolower($result->filetype);
            switch($sizeArray[2]) {
            case IMAGETYPE_JPEG:
                if ($extension != 'jpg' && $extension != 'jpeg' && $extension != 'jpe') {
                    $result->filename .= '.jpg';
                }
                if (strpos($filetypei, 'jpeg') === FALSE) {
                    $result->filetype = 'image/jpeg';
                }
                break;
            case IMAGETYPE_GIF:
                if ($extension != 'gif') {
                    $result->filename .= '.gif';
                }
                if (strpos($filetypei, 'gif') === FALSE) {
                    $result->filetype = 'image/gif';
                }
                break;
            case IMAGETYPE_PNG:
                if ($extension != 'png') {
                    $result->filename .= '.png';
                }
                if (strpos($filetypei, 'png') === FALSE) {
                    $result->filetype = 'image/png';
                }
                break;
            case IMAGETYPE_BMP:
                if ($extension != 'bmp') {
                    $result->filename .= '.bmp';
                }
                if (strpos($filetypei, 'bmp') === FALSE) {
                    $result->filetype = 'image/bmp';
                }
                break;
            case IMAGETYPE_WBMP: // Added in PHP 4.4.
                if ($extension != 'wbmp' && $extension != 'wbm') {
                    $result->filename .= '.wbmp';
                }
                if (strpos($filetypei, 'wbmp') === FALSE) {
                    $result->filetype = 'image/vnd.wap.wbmp';
                }
                break;
            case IMAGETYPE_ICO:
                if ($extension != 'ico') {
                    $result->filename .= '.ico';
                }
                if (strpos($filetypei, 'ico') === FALSE) {
                    $result->filetype = 'image/vnd.microsoft.icon';
                }
                break;
            }
        }

        // Store File
        if ($usedb) {
            $subdir = '';
        } else {
            $file = '';
            $subdir = $this->getNewSubdir();
        }

        $values = [
            'pid' => $pid,
            'filename' => $result->filename,
            'filetype' => $result->filetype,
            'filesize' => (string) $result->filesize,
            'attachment' => &$result->binaryFile,
            'uid' => (int) $self['uid'],
            'img_size' => $sqlsize,
            'subdir' => $subdir,
        ];

        $result->aid = $this->sql->addAttachment($values, $quarantine);
        $result->binaryFile = '';

        if ($usedb) {
            $file = '';
            $path = $filepath;
        } else {
            $newfilename = $result->aid;
            $path = $this->getFullPathFromSubdir($subdir, TRUE) . $newfilename;
            rename($filepath, $path);
        }

        // Make Thumbnail
        if ($sizeArray !== false) {
            $this->createThumbnail($result->filename, $path, $result->filesize, $imgSize, $quarantine, $result->aid, $pid, $subdir);
        }

        // Remove temp upload file, getUpload() was checked in get_attached_file()
        if ($usedb) {
            unlink($path);
        }

        return $result;
    }

    /**
     * Handle user inputs related to post editing.
     *
     * @since 1.9.11
     * @param array $deletes    Returns a list of deleted attachment IDs, by reference.
     * @param array $aid_list   List of attachment IDs related to the current post.
     * @param int   $pid        Optional. PID of the related post. Attachment becomes orphaned if omitted.
     * @param bool  $quarantine Save this record in a private table for later review?
     * @return UploadStatus
     */
    public function doEdits(&$deletes, array $aid_list, int $pid = 0, bool $quarantine = false): UploadStatus
    {
        $return = UploadStatus::Success;
        $deletes = array();
        if (! isset($_POST['attachment'])) {
            return $return;
        }
        if (! is_array($_POST['attachment'])) {
            return $return;
        }
        foreach($_POST['attachment'] as $aid => $attachment) {
            if (false === array_search($aid, $aid_list)) {
                continue;
            }
            switch($attachment['action']) {
            case 'replace':
                $this->deleteByID($aid, $quarantine);
                $deletes[] = $aid;
                $result = $this->uploadedFile('replace_'.$aid, $pid, $quarantine);
                if ($result->status !== UploadStatus::Success && $result->status !== UploadStatus::EmptyUpload) {
                    $return = $result->status;
                }
                break;
            case 'rename':
                $rename = trim(postedVar('rename_'.$aid, '', FALSE, FALSE));
                $status = $this->changeName($aid, $pid, $rename, $quarantine);
                if ($status !== UploadStatus::Success) {
                    $return = $status;
                }
                break;
            case 'delete':
                $this->deleteByID($aid, $quarantine);
                $deletes[] = $aid;
                break;
            default:
                break;
            }
        }
        return $return;
    }

    public function changeName(int $aid, int $pid, string $newname, bool $quarantine = false): UploadStatus
    {
        if (isValidFilename($newname)) {
            $this->sql->renameAttachment($aid, $newname, $quarantine);

            $extension = strtolower(get_extension($newname));
            $img_extensions = array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'wbmp', 'wbm', 'bmp');
            if (in_array($extension, $img_extensions)) {
                if (0 == $this->sql->countThumbnails($aid, $quarantine)) {
                    $this->regenerateThumbnail($aid, $pid, $quarantine);
                }
            }
            return UploadStatus::Success;
        } else {
            return UploadStatus::InvalidFilename;
        }
    }

    public function copyByPost(int $frompid, int $topid)
    {
        $db = $this->db;

        if (! X_STAFF) throw new LogicException("Unprivileged access to function");

        // Find all primary attachments for $frompid
        $query = $db->query("SELECT aid, subdir FROM ".X_PREFIX."attachments WHERE pid=$frompid AND parentid=0");
        while($attach = $db->fetch_array($query)) {
            $db->query("INSERT INTO ".X_PREFIX."attachments (pid, filename, filetype, filesize, attachment, img_size, uid, updatetime, subdir) "
                     . "SELECT {$topid}, filename, filetype, filesize, attachment, img_size, uid, updatetime, subdir FROM ".X_PREFIX."attachments WHERE aid={$attach['aid']}");
            if ($db->affected_rows() == 1) {
                $aid = (string) $db->insert_id();
                if ($attach['subdir'] != '') {
                    $this->copyDiskFile($attach['aid'], $aid, $attach['subdir']);
                }
            }

            // Update any [file] object references in the new copy of the post messsage.
            $message = $db->query("SELECT message FROM ".X_PREFIX."posts WHERE pid=$topid");
            if ($message = $db->fetch_array($message)) {
                $newmessage = str_replace("[file]{$attach['aid']}[/file]", "[file]{$aid}[/file]", $message['message']);
                if ($newmessage !== $message['message']) {
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
                    $childaid = (string) $db->insert_id();
                    if ($childattach['subdir'] != '') {
                        $this->copyDiskFile($childattach['aid'], $childaid, $childattach['subdir']);
                    }
                }
            }
        }
    }

    private function copyDiskFile(string $fromaid, string $toaid, string $subdir)
    {
        $path = $this->getFullPathFromSubdir($subdir);
        if ($path != '') {
            if (is_file($path.$fromaid)) {
                copy($path.$fromaid, $path.$toaid);
            }
        }
    }

    public function moveToDB(int $aid, int $pid): bool
    {
        $db = $this->db;

        if (! X_ADMIN) throw new LogicException("Unprivileged access to function");

        $query = $db->query("SELECT aid, filesize, subdir FROM ".X_PREFIX."attachments WHERE aid=$aid AND pid=$pid");
        if ($db->num_rows($query) != 1) {
            return false;
        }
        $attach = $db->fetch_array($query);
        if ($attach['subdir'] == '') {
            return false;
        }
        $path = $this->getFullPathFromSubdir($attach['subdir']).$attach['aid'];
        if (intval(filesize($path)) != intval($attach['filesize'])) {
            return false;
        }
        $attachment = file_get_contents($path);
        $db->escape_fast($attachment);
        $db->query("UPDATE ".X_PREFIX."attachments SET subdir='', attachment='$attachment' WHERE aid=$aid AND pid=$pid");
        if ($db->affected_rows() !== 1) {
            return false;
        }
        unlink($path);
        return true;
    }

    public function moveToDisk(int $aid, int $pid): bool
    {
        $db = $this->db;

        if (! X_ADMIN) throw new LogicException("Unprivileged access to function");

        $query = $db->query("SELECT a.*, UNIX_TIMESTAMP(a.updatetime) AS updatestamp, p.dateline "
                          . "FROM ".X_PREFIX."attachments AS a LEFT JOIN ".X_PREFIX."posts AS p USING (pid) "
                          . "WHERE a.aid=$aid AND a.pid=$pid");
        if ($db->num_rows($query) != 1) {
            return false;
        }
        $attach = $db->fetch_array($query);
        if ($attach['subdir'] != '' || strlen($attach['attachment']) != (int) $attach['filesize']) {
            return false;
        }
        if (intval($attach['updatestamp']) == 0 && intval($attach['dateline']) > 0) {
            $attach['updatestamp'] = $attach['dateline'];
        }
        $subdir = $this->getNewSubdir($attach['updatestamp']);
        $path = $this->getFullPathFromSubdir($subdir, TRUE);
        $newfilename = $aid;
        $path .= $newfilename;
        $file = fopen($path, 'wb');
        if ($file === FALSE) {
            return false;
        }
        if (fwrite($file, $attach['attachment']) != (int) $attach['filesize']) {
            return false;
        }
        fclose($file);
        $db->query("UPDATE ".X_PREFIX."attachments SET subdir='$subdir', attachment='' WHERE aid=$aid AND pid=$pid");
        return true;
    }

    /**
     * Move uploaded files from the quarantine table to the public table.
     *
     * Handles disk-based storage logic and also updates the file tags for BBCode.
     *
     * @since 1.9.12
     * @param int $oldpid The PID number used in the quarantine table `hold_posts`.
     * @param int $newpid The PID number used in the public table `posts`.
     */
    public function approve(int $oldpid, int $newpid)
    {
        global $db, $SETTINGS;

        $aidmap = [];

        $path = $this->getFullPathFromSubdir('');
        $usedb = true;
        if ($path != '') {
            if (is_dir($path)) {
                $usedb = false;
            }
        }

        $quarantine = true;
        $result = $this->sql->getAttachmentParents($oldpid, $quarantine);
        if (count($result) == 0) {
            // Nothing to do.
            return;
        }
        foreach ($result as $attach) {
            $parent = (int) $attach['parentid'];
            if ($parent != 0) {
                // $result is sorted by parentid ascending, so the $aidmap gets filled by parents first before children.
                $newparentid = $aidmap[$parent];
            } else {
                $newparentid = 0;
            }
            $oldaid = (int) $attach['aid'];
            $newaid = $this->sql->approveAttachment($oldaid, $newpid, $newparentid);
            $aidmap[$oldaid] = $newaid;
            if ((int) $attach['filesize'] >= (int) $SETTINGS['files_min_disk_size'] && ! $usedb) {
                $this->moveToDisk($newaid, $newpid);
            }
        }

        $this->sql->deleteAttachmentsByID(array_keys($aidmap), $quarantine);

        $postbody = $this->sql->getPostBody($newpid);
        $search = array();
        $replace = array();
        $search[] = "[file]";
        $replace[] = "[oldfile]";
        $search[] = "[/file]";
        $replace[] = "[/oldfile]";
        foreach($aidmap as $oldid => $newid) {
            $search[] = "[oldfile]{$oldid}[/oldfile]";
            $replace[] = "[file]{$newid}[/file]";
        }
        $search[] = "[oldfile]";
        $replace[] = "[file]";
        $search[] = "[/oldfile]";
        $replace[] = "[/file]";
        $newpostbody = str_replace($search, $replace, $postbody);
        if ($newpostbody !== $postbody) {
            $this->sql->savePostBody($newpid, $newpostbody);
        }
    }

    public function deleteByID(int $aid, bool $quarantine = false)
    {
        $thumbs_only = false;
        $aid_list = $this->sql->getAttachmentChildIDs($aid, $thumbs_only, $quarantine);
        $aid_list[] = $aid;
        $this->deleteByIDs($aid_list, $quarantine);
    }

    public function deleteByPost(int $pid, bool $quarantine = false)
    {
        $children = true;
        $aid_list = $this->sql->getAttachmentIDsByPost($pid, $children, $quarantine);
        $this->deleteByIDs($aid_list, $quarantine);
    }

    // Important: Call this function BEFORE deleting posts, because it uses a multi-table query.
    public function deleteByThread(int $tid)
    {
        if (! X_STAFF) throw new LogicException("Unprivileged access to function");
        $tid_list = [$tid];
        $aid_list = $this->sql->getAttachmentIDsByThread($tid_list);
        $this->deleteByIDs($aid_list);
    }

    // Important: Call this function BEFORE deleting posts, because it uses a multi-table query.
    public function emptyThread(int $tid, int $notpid)
    {
        if (! X_STAFF) throw new LogicException("Unprivileged access to function");
        $tid_list = [$tid];
        $quarantine = false;
        $aid_list = $this->sql->getAttachmentIDsByThread($tid_list, $quarantine, $notpid);
        $this->deleteByIDs($aid_list, $quarantine);
    }

    // Important: Call this function BEFORE deleting posts, because it uses a multi-table query.
    public function deleteByThreads(array $tid_list, bool $quarantine = false)
    {
        if (! X_ADMIN) throw new LogicException("Unprivileged access to function");
        if (empty($tid_list)) return;
        $aid_list = $this->sql->getAttachmentIDsByThread($tid_list, $quarantine);
        $this->deleteByIDs($aid_list, $quarantine);
    }

    public function deleteByUser(string $username, bool $quarantine = false)
    {
        if (! X_ADMIN) throw new LogicException("Unprivileged access to function");
        $aid_list = $this->sql->getAttachmentIDsByUser($username, $quarantine);
        $this->deleteByIDs($aid_list, $quarantine);
    }

    public function deleteOrphans(): int
    {
        $db = $this->db;

        if (! X_ADMIN) throw new LogicException("Unprivileged access to function");

        $q = $db->query("SELECT a.aid FROM ".X_PREFIX."attachments AS a "
                      . "LEFT JOIN ".X_PREFIX."posts AS p USING (pid) "
                      . "LEFT JOIN ".X_PREFIX."attachments AS b ON a.parentid=b.aid "
                      . "WHERE ((a.uid=0 OR a.pid > 0) AND p.pid IS NULL) OR (a.parentid > 0 AND b.aid IS NULL)");

        $aid_list = [];
        while($a = $db->fetch_array($q)) {
            $aid_list[] = $a['aid'];
        }
        $db->free_result($q);
        
        $this->deleteByIDs($aid_list);

        return count($aid_list);
    }

    private function deleteByIDs(array $aid_list, bool $quarantine = false)
    {
        $db = $this->db;
        
        if (empty($aid_list)) return;

        if (! $quarantine) {
            $query = $this->sql->getAttachmentPaths($aid_list);
            while($attachment = $db->fetch_array($query)) {
                $path = $this->getFullPathFromSubdir($attachment['subdir']); // Returns FALSE if file stored in database.
                if ($path != '') {
                    $path .= $attachment['aid'];
                    if (is_file($path)) {
                        unlink($path);
                    }
                }
            }
            $db->free_result($query);
        }

        $this->sql->deleteAttachmentsByID($aid_list, $quarantine);
    }

    /**
     * Retrieves information about the specified file upload.
     *
     * This function sets appropriate error levels and returns several variables.
     * This function does not provide the upload path, which is $_FILES[$varname]['tmp_name']
     * All return values must be treated as invalid if the result status is not Success.
     *
     * @since 1.9.11
     * @param string $varname The name of the file input on the form.
     * @param bool   $loadfile Optional. When set to TRUE, the uploaded file will be loaded into memory and returned as a string value.
     * @return UploadResult
     */
    public function getUpload($varname, $loadfile = true): UploadResult
    {
        global $SETTINGS;

        /* Perform Sanity Checks */

        if (isset($_FILES[$varname])) {
            $file = $_FILES[$varname];
        } else {
            return new UploadResult(UploadStatus::EmptyUpload);
        }

        switch($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return new UploadResult(UploadStatus::SizeExceeded);
            case UPLOAD_ERR_NO_FILE:
            case UPLOAD_ERR_PARTIAL:
                return new UploadResult(UploadStatus::EmptyUpload);
            case UPLOAD_ERR_NO_TMP_DIR:
                header('HTTP/1.0 500 Internal Server Error');
                echo "Fatal Error: XMB can't find the upload_tmp_dir. This is a server configuration fault.";
                throw new RuntimeException('The FILES array says UPLOAD_ERR_NO_TMP_DIR', $file['error']);
            case UPLOAD_ERR_CANT_WRITE:
                header('HTTP/1.0 500 Internal Server Error');
                echo 'Fatal Error: PHP was unable to save the uploaded file. This is a server configuration fault.';
                throw new RuntimeException('The FILES array says UPLOAD_ERR_CANT_WRITE', $file['error']);
            case UPLOAD_ERR_EXTENSION:
                throw new RuntimeException('A PHP extension blocked a file upload', $file['error']);
            default:
                // See the PHP Manual for additional information.
                if (DEBUG && is_numeric($file['error'])) {
                    throw new RuntimeException("The FILES array says code {$file['error']} prevented an upload", $file['error']);
                }
                return new UploadResult(UploadStatus::GenericError);
        }

        if (! is_uploaded_file($file['tmp_name'])) {
            return new UploadResult(UploadStatus::EmptyUpload);
        }

        if (! is_readable($file['tmp_name'])) {
            header('HTTP/1.0 500 Internal Server Error');
            echo 'Fatal Error: XMB does not have read permission in the upload_tmp_dir. This is a PHP server security fault.';
            throw new RuntimeException('Unable to read uploaded file');
        }

        $file['name'] = trim($file['name']);
        if (! isValidFilename($file['name'])) {
            $file['name'] = basename($file['tmp_name']);
            if (! isValidFilename($file['name'])) {
                unlink($file['tmp_name']);
                return new UploadResult(UploadStatus::InvalidFilename);
            }
        }

        $filesize = intval(filesize($file['tmp_name'])); // fix bad filesizes (PHP Bug #45124, etc)
        if ($filesize > (int) $SETTINGS['maxattachsize']) {
            unlink($file['tmp_name']);
            return new UploadResult(UploadStatus::SizeExceeded);
        }
        if ($filesize == 0) {
            unlink($file['tmp_name']);
            return new UploadResult(UploadStatus::EmptyUpload);
        }


        /* Set Return Values */
        
        $result = new UploadResult(UploadStatus::Success);

        if ($loadfile) {
            $result->binaryFile = file_get_contents($file['tmp_name']);
        }
        $result->filename = $file['name'];
        $result->filetype = preg_replace('#[\\x00\\r\\n%]#', '', $file['type']);
        $result->filesize = $filesize;

        return $result;
    }

    public function getURL(int $aid, int $pid, string $filename, bool $htmlencode = true, bool $quarantine = false): string
    {
        global $full_url, $SETTINGS;

        if ($SETTINGS['files_virtual_url'] == '') {
            $virtual_path = $full_url;
        } else {
            $virtual_path = $SETTINGS['files_virtual_url'];
        }

        if ($quarantine) {
            $format = 99;
        } else {
            $format = (int) $SETTINGS['file_url_format'];
        }

        switch($format) {
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
        case 99:
            $url = "{$virtual_path}files.php?newpid=$pid&amp;newaid=$aid";
            break;
        default:
            $url = '';
        }

        return $url;
    }

    public function getSizeFormatted($attachsize)
    {
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
     * @since 1.9.11
     * @param string $date Optional. Unix timestamp of the attachment, if not now.
     * @return string
     */
    private function getNewSubdir(string $date = '')
    {
        global $SETTINGS;
        if ('' == $date) {
            $timestamp = time();
        } else {
            $timestamp = (int) $date;
        }
        if (1 == (int) $SETTINGS['files_subdir_format']) {
            $format = 'Y/m';
        } else {
            $format = 'Y/m/d';
        }
        return gmdate($format, $timestamp);
    }

    /**
     * Retrieve the file storage path given just a subdirectory name.
     *
     * getFullPathFromSubdir() returns the concatenation of
     * the file storage path and a specified subdir value.
     * A trailing forward-slash is guaranteed in the return value.
     *
     * @since 1.9.11
     * @param string $subdir The name typically has no leading or trailing slashes, e.g. 'dir1' or 'dir2/sub3'
     * @param bool   $mkdir  Optional.  TRUE causes specified subdirectory to be created.
     * @return string May be empty if file storage not enabled.
     */
    public function getFullPathFromSubdir(string $subdir, bool $mkdir = false): string
    {
        global $SETTINGS;

        $path = $SETTINGS['files_storage_path'];
        if ('' == $path) {
            return $path;
        }
        if (substr($path, -1) != '/') {
            $path .= '/';
        }

        $path .= $subdir;

        if (substr($path, -1) != '/') {
            $path .= '/';
        }

        if ($mkdir) {
            if (! is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }

        return $path;
    }

    /**
     * Creates a file appropriate for writing temporary data to disk.
     *
     * @since 1.9.11
     * @param string $path Optional. Directory of preferred temporary file location.
     * @return string Full path to the temporary file.
     */
    private function getTempFile(string $path = ''): string
    {
        $filepath = false;
        if ($path != '') {
            $filepath = @tempnam($path, 'xmb-');
        }
        if (false === $filepath || ! is_writable($filepath)) {
            if (DEBUG) {
                $filepath = tempnam('', 'xmb-');
            } else {
                $filepath = @tempnam('', 'xmb-');
            }
        }
        if (false === $filepath || ! is_writable($filepath)) {
            throw new RuntimeException('XMB was unable to create a temporary file.  Enable DEBUG for more info.');
        }
        return $filepath;
    }

    /**
     * Uses the path to an image to create a resized image based on global settings.
     *
     * The thumbnail will be attached to its corresponding parent image and post if the last three parameters are set.
     * Otherwise, the thumbnail will simply be saved to disk at $filepath.'-thumb.jpg'
     *
     * @since 1.9.11
     * @param string $filename   Original name of the input file.
     * @param string $filepath   Current name and location (full path) of the input file.
     * @param int    $filesize   The size, in bytes, that you want printed on the thumbnail.
     * @param object $imgSize    Caller must construct a CartesianSize object to specify the dimensions of the input image.
     * @param bool   $quarantine Save this record in a private table for later review?
     * @param int    $aid        Optional. AID to be used as the parentid if attaching the thumbnail to a post.
     * @param int    $pid        Optional. PID to attach the thumbnail to.
     * @param string $subdir     Optional. Subdirectory to use inside the file storage path, or null string to store it in the database.
     * @return bool
     */
    private function createThumbnail(string $filename, string $filepath, int $filesize, CartesianSize $imgSize, bool $quarantine = false, int $aid = 0, int $pid = 0, string $subdir = ''): bool
    {
        global $self, $SETTINGS;

        // Determine if a thumbnail is needed.
        $thumbSize = new CartesianSize();
        if (! $thumbSize->fromString($SETTINGS['max_thumb_size'])) {
            // This setting is invalid.
            return false;
        }

        $thumb = $this->load_and_resize_image($filepath, $thumbSize);

        if (FALSE === $thumb) {
            return false;
        }

        // Write full size and dimensions on thumbnail
        if (function_exists('imagefttext')) {
            $string = $this->getSizeFormatted($filesize).' '.$imgSize;
            $grey = imagecolorallocatealpha($thumb, 64, 64, 64, 80);
            imagefilledrectangle($thumb, 0, $thumbSize->getHeight() - 20, $thumbSize->getWidth(), $thumbSize->getHeight(), $grey);
            imagefttext($thumb, 10, 0, 5, $thumbSize->getHeight() - 5, imagecolorexact($thumb, 255,255,255), 'fonts/VeraMono.ttf', $string);
        }

        $filepath .= '-thumb.jpg';
        $filename .= '-thumb.jpg';

        // Write to Disk
        imagejpeg($thumb, $filepath, 85);
        imagedestroy($thumb);

        // Gather metadata
        $filesize = intval(filesize($filepath));
        $filetype = 'image/jpeg';
        $sqlsize = (string) $thumbSize;

        // Attach thumbnail to the post
        if ($aid != 0) {

            // Check minimum file size for disk storage
            if ($filesize < (int) $SETTINGS['files_min_disk_size']) {
                $subdir = '';
            }

            // Add database record
            if ($subdir == '') {
                $file = file_get_contents($filepath);
                unlink($filepath);
            } else {
                $file = '';
            }

            $values = [
                'pid' => $pid,
                'filename' => $filename,
                'filetype' => $filetype,
                'filesize' => (string) $filesize,
                'attachment' => &$file,
                'uid' => (int) $self['uid'],
                'parentid' => $aid,
                'img_size' => $sqlsize,
                'subdir' => $subdir,
            ];

            $aid = $this->sql->addAttachment($values, $quarantine);

            if ($subdir != '') {
                $newfilename = $aid;
                rename($filepath, $this->getFullPathFromSubdir($subdir) . $newfilename);
            }
        }
        return true;
    }

    /**
     * Uses the path to an image file to create a resized image resource in memory.
     *
     * @since 1.9.11.12
     * @param string $path Current name and location (full path) of the input file.
     * @param object $thumbMaxSize Takes the size limit.  Returns the actual size.
     * @param bool   $load_if_smaller Do you want to load the image if it's smaller than both $width and $height?
     * @param bool   $enlarge_if_smaller Do you want to resize the image if it's smaller than both $width and $height?
     * @return resource|bool The image GD resource on success.  FALSE when $path is not an image file, or if the image is larger than $SETTINGS['max_image_size'].
     */
    private function load_and_resize_image(string $path, CartesianSize &$thumbMaxSize, bool $load_if_smaller = FALSE, bool $enlarge_if_smaller = FALSE)
    {
        global $SETTINGS;

        // Check if GD is available
        if (!function_exists('imagecreatetruecolor')) {
            return false;
        }

        $result = getimagesize($path);

        if (FALSE === $result) {
            return false;
        }

        $imgSize = new CartesianSize();
        $imgSize->fromArray($result);

        $maxImgSize = new CartesianSize();
        if ($maxImgSize->fromString($SETTINGS['max_image_size'])) {
            if ($imgSize->isBiggerThan($maxImgSize)) {
                return false;
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
            return false;
        }

        if (!$img) {
            return false;
        }

        // Determine if a thumbnail is needed.
        if ($imgSize->isSmallerThan($thumbMaxSize)) {
            if (!$load_if_smaller) {
                return false;
            } elseif (!$enlarge_if_smaller) {
                $thumbMaxSize = $imgSize;
                return $img;
            }
        }

        // Create a thumbnail for this attachment.
        if ($imgSize->aspect() > $thumbMaxSize->aspect()) {
            $thumbSize = new CartesianSize($thumbMaxSize->getWidth(), (int) round($thumbMaxSize->getWidth() / $imgSize->aspect()));
        } else {
            $thumbSize = new CartesianSize((int) round($imgSize->aspect() * $thumbMaxSize->getHeight()), $thumbMaxSize->getHeight());
        }

        $thumb = imagecreatetruecolor($thumbSize->getWidth(), $thumbSize->getHeight());

        // Resize $img
        if (!imagecopyresampled($thumb, $img, 0, 0, 0, 0, $thumbSize->getWidth(), $thumbSize->getHeight(), $imgSize->getWidth(), $imgSize->getHeight())) {
            return false;
        }

        imagedestroy($img);

        $thumbMaxSize = $thumbSize;
        return $thumb;
    }

    public function regenerateThumbnail(int $aid, int $pid, bool $quarantine = false): UploadStatus
    {
        global $SETTINGS;

        // Write attachment to disk
        $attach = $this->sql->getAttachment($aid, $quarantine);
        if (empty($attach)) {
            return UploadStatus::GenericError;
        }
        if ($attach['subdir'] == '') {
            if (strlen($attach['attachment']) != (int) $attach['filesize']) {
                return UploadStatus::GenericError;
            }
            $path = '';
            // IDs in the two tables may collide, so keep quarantined files strictly outside of the normal path.
            if (! $quarantine) {
                $subdir = $this->getNewSubdir($attach['updatestamp']);
                $path = $this->getFullPathFromSubdir($subdir, true);
            }
            if ('' == $path) {
                $path = $this->getTempFile();
            } else {
                $newfilename = $aid;
                $path .= $newfilename;
            }
            $file = fopen($path, 'wb');
            if ($file === false) {
                return UploadStatus::BadStoragePath;
            }
            fwrite($file, $attach['attachment']);
            fclose($file);
            unset($attach['attachment']);
        } else {
            $path = $this->getFullPathFromSubdir($attach['subdir']);
            if ('' == $path) {
                return UploadStatus::BadStoragePath;
            }
            $path .= $aid;
            if (!is_file($path)) {
                return UploadStatus::GenericError;
            }
            if (filesize($path) != (int) $attach['filesize']) {
                return UploadStatus::GenericError;
            }
        }

        // Check if we can store image metadata
        $result = getimagesize($path);

        if ($result === false) {
            if ($attach['subdir'] == '') {
                unlink($path);
            }
            return UploadStatus::NotAnImage;
        }
        $imgSize = new CartesianSize();
        $imgSize->fromArray($result);
        $sqlsize = (string) $imgSize;

        $maxImgSize = new CartesianSize();
        if ($maxImgSize->fromString($SETTINGS['max_image_size'])) {
            if ($imgSize->isBiggerThan($maxImgSize)) {
                if ($attach['subdir'] == '') {
                    unlink($path);
                }
                return UploadStatus::DimsExceeded;
            }
        }

        if ($attach['img_size'] !== $sqlsize) {
            $this->sql->setImageDims($aid, $sqlsize);
        }

        $this->deleteThumbnail($aid, $quarantine);
        $this->createThumbnail($attach['filename'], $path, (int) $attach['filesize'], $imgSize, $quarantine, $aid, $pid, $attach['subdir']);

        // Clean up temp files
        if ($attach['subdir'] == '') {
            unlink($path);
        }
        return UploadStatus::Success;
    }

    private function deleteThumbnail(int $aid, bool $quarantine = false)
    {
        $thumbs = true;
        $aid_list = $this->sql->getAttachmentChildIDs($aid, $thumbs, $quarantine);
        $this->deleteByIDs($aid_list, $quarantine);
    }

    /**
     * Converts all [img] tags to attachments.
     *
     * @since 1.9.11
     * @param int $pid ID of the related post. Attachment becomes orphaned if set to zero.
     * @param string $message Post body, passed by reference and modified with new tags.
     * @param bool $quarantine Save this record in a private table for later review?
     * @return UploadStatus
     */
    public function remoteImages(int $pid, string &$message, bool $quarantine = false): UploadStatus
    {
        $return = UploadStatus::Success;

        // Sanity Checks
        if (! ini_get('allow_url_fopen')) {
            return $return;
        }

        // Remove the code block contents from $message.
        $messagearray = $this->bbcode->parseCodeBlocks($message);
        $message = array();
        for($i = 0; $i < count($messagearray); $i += 2) {
            $message[$i] = $messagearray[$i];
        }
        $message = implode("<!-- code -->", $message);

        // Extract img codes
        $results = array();
        $items = array();
        $pattern = '/\[img(=([0-9]*?){1}x([0-9]*?))?\](' . get_img_regexp() . ')\[\/img\]/i';
        preg_match_all($pattern, $message, $results, PREG_SET_ORDER);
        foreach($results as $result) {
            if (isset($result[4])) {
                $item['code'] = $result[0];
                $item['url'] = htmlspecialchars_decode($result[4], ENT_NOQUOTES);
                $items[] = $item;
            }
        }

        // Process URLs
        foreach($items as $item) {
            $result = $this->remoteFile($item['url'], $pid, $quarantine);
            if ($result->status !== UploadStatus::Success) {
                $return = $result->status;
                $replace = '[bad '.substr($item['code'], 1, -6).'[/bad img]';
            } else {
                $replace = "[file]{$aid}[/file]";
            }
            $temppos = strpos($message, $item['code']);
            $message = substr($message, 0, $temppos).$replace.substr($message, $temppos + strlen($item['code']));
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

    public function uploadErrorMsg(UploadStatus $status): string
    {
        global $lang;

        $index = [
            UploadStatus::Success         => '',
            UploadStatus::EmptyUpload     => '',
            UploadStatus::BadStoragePath  => 'fileuploaderror1',
            UploadStatus::CountExceeded   => 'fileuploaderror2',
            UploadStatus::InvalidURL      => 'fileuploaderror3',
            UploadStatus::NotAnImage      => 'fileuploaderror4',
            UploadStatus::DimsExceeded    => 'fileuploaderror5',
            UploadStatus::SizeExceeded    => 'fileuploaderror6',
            UploadStatus::NoTempFile      => 'fileuploaderror7',
            UploadStatus::GenericError    => 'fileuploaderror8',
            UploadStatus::InvalidFilename => 'invalidFilename',
        ];

        $key = $index[$status];
        
        if ($key === '') {
            return '';
        } else {
            return $lang[$key];
        }
    }
}
