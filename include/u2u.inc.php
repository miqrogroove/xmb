<?php
/* $Id: u2u.inc.php,v 1.1.2.31 2007/06/14 00:02:22 ajv Exp $ */
/**
 * Changes to port UltimaBB 1.0's U2U classes to XMB 1.9.7
 * 
 * © 2007 The XMB Development Team
 *        http://www.xmbforum.com
 *
 * This code is from UltimaBB. The (C) notice is as follows:
 * 
 * UltimaBB
 * Copyright (c) 2004 - 2007 The UltimaBB Group
 * http://www.ultimabb.com
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
 **/

if (!defined('IN_CODE') && (defined('DEBUG') && DEBUG == false)) {
    exit ("Not allowed to run this file directly.");
}

/**
* u2uDAO() - U2U Data Access Object
*
* In a perfect world, this class provides an API abstraction to
* common U2U functions. Theoretically, if you use this API, you
* don't need to use any SQL in your model code
*/
class u2uDAO {
    /**
    * class() - short description of function
    *
    * Long description of function
    *
    * @param    $varname    type, what it does
    * @return   type, what the return does
    */
    function u2uDAO() {

    }
    /**
     * create()
     *
     * @param $to       Message to
     * @param $from     Message from
     * @param $to_uid       Message to (UID)
     * @param $from_uid     Message from (UID)
     * @param $type     One of 'incoming', 'outgoing', 'draft'
     * @param $owner    Usually the same as $from
     * @param $folder   One of 'Inbox', 'Outbox', 'Drafts'
     * @param $subject  The subject
     * @param $message  the message body
     * @param $isRead   isRead
     * @param $isSent   isSent
     * @return No return variables
     **/
    function create($to, $from, $to_uid, $from_uid, $type, $owner, $folder, $subject, $message, $isRead, $isSent) {
        global $db, $onlinetime, $table_u2u;

        $to = $db->escape($to, -1, true);
        $from = $db->escape($from, -1, true);
        $type = $db->escape($type);
        $owner = $db->escape($owner, -1, true);
        $folder = $db->escape($folder);
        $subject = $db->escape($subject);
        $message = $db->escape($message);
        $isRead = $db->escape($isRead);
        $isSent = $db->escape($isSent);
    
        $result = $db->query("INSERT INTO " . $table_u2u . " (u2uid, msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus) VALUES ('', '$to', '$from', '$type', '$owner', '$folder', '$subject', '$message', '$onlinetime', '$isRead', '$isSent')");
        
        if ($result == false) {
            return false;
        }
        return $db->insert_id();
    }
    
    function read()
    {
        
    }
    
    function update()
    {
        
    }
    
    function delete()
    {
        
    }
}

/**
* class() - short description of function
*
* Long description of function
*
* @param    $varname    type, what it does
* @return   type, what the return does
*/
class u2uModel {
    /**
    * function() - short description of function
    *
    * Long description of function
    *
    * @param    $varname    type, what it does
    * @return   type, what the return does
    */
    function u2uModel() {
    }

    /**
    * Get the file being attached
    *
    * @param   array   $file   the 'attach' array from the $_FILES superglobal
    * @param   string   $attachstatus   are attachments allowed for this forum?
    * @return   mixed   the attachment if valid, false otherwise
    */
    function getAttachment($file, $u2uattachstatus) {
        global $db, $lang, $filename, $filetype, $filesize, $SETTINGS, $fileheight, $filewidth;
        global $attachedfile;

        $filename = $filetype = $fileheight = $filewidth = '';
        $filesize = 0;

        if (is_array($file) && $file['name'] != 'none' && !empty ($file['name']) && $SETTINGS['u2uattachstatus'] != 'off' && is_uploaded_file($file['tmp_name'])) {
            if (!isValidFilename($file['name'])) {
                error($lang['invalidFilename'], false, '', '', false, true, false, true);
                return false;
            }
            $filesize = intval(filesize($file['tmp_name']));
            if ($filesize > ($SETTINGS['max_attach_size'])) {
                error($lang['attachtoobig'], false, '', '', false, true, false, true);
                return false;
            }
            $attachment = addslashes(fread(fopen($file['tmp_name'], 'rb'), $filesize));
            $filename = checkInput($file['name']);
            $filetype = checkInput($file['type']);
            $extention = strtolower(substr(strrchr($file['name'], '.'), 1));
            if ($extention == 'jpg' || $extention == 'jpeg' || $extention == 'gif' || $extention == 'png' || $extention == 'bmp') {
                $exsize = getimagesize($file['tmp_name']);
                $fileheight = $exsize[1];
                $filewidth = $exsize[0];
            }
            if ($filesize !== 0) {
                return $attachment;
            }
        }
        return false;
    }

    /**
     * send_multi_recp()
     *
     * @param $msgto Who is the u2u set going to?
     * @param $subject u2u subject
     * @param $message u2u message
     * @return errors (if any)
     **/
    function send_multi_recp($msgto, $subject, $message, $useu2usig) {
        $errors = '';
        $recipients = array_unique(array_map('trim', explode(',', $msgto)));
        foreach ($recipients as $recp) {
            $errors .= $this->send_recp($recp, $subject, $message, $useu2usig);
        }
        return $errors;
    }

    /**
     * send_recp()
     *
     * @param $msgto Who is the u2u set going to?
     * @param $subject u2u subject
     * @param $message u2u message
     * @return errors (if any)
     **/
    function send_recp($msgto, $subject, $message) {
        global $db, $mailsys, $self, $SETTINGS, $lang, $onlinetime, $username;
        global $attachedfile, $filetype, $filesize, $filename, $table_u2u;
        global $fileheight, $filewidth, $table_members;

        $errors = '';

        $u2u_dao = new u2uDao();

        $msgto = $db->escape(checkInput($msgto), -1, true);
        $query = $db->query("SELECT username, uid, email, ignoreu2u, emailonu2u, langfile, status FROM " . $table_members . " WHERE username = '$msgto'");
        if ($rcpt = $db->fetch_array($query)) {
            $ilist = array_map('trim', explode(',', $rcpt['ignoreu2u']));
            if (!in_array($self['username'], $ilist) || X_ADMIN) {
                $username = $rcpt['username'];
                $usr_uid = $rcpt['uid'];
                $thislangfile = $rcpt['langfile'];

                $u2u_dao->create($username, $self['username'], $usr_uid, $self['uid'], 'incoming', $username, 'Inbox', $subject, $message, 'no', 'yes');

                if (isset ($self['saveogu2u']) && $self['saveogu2u'] == 'yes') {
                    $u2u1 = $u2u_dao->create($username, $self['username'], $usr_uid, $self['uid'], 'outgoing', $self['username'], 'Outbox', $subject, $message, 'no', 'yes');
                    if (isset ($_FILES['attach']) && ($attachedfile = $this->getAttachment($_FILES['attach'], $SETTINGS['u2uattachstatus'], $SETTINGS['max_attach_size'])) !== false) {
                        $db->query("INSERT INTO " . $table_u2u . "_attachments (aid, u2uid, filename, fcreatee, filesize, fileheight, filewidth, attachment, owner) VALUES ('', '$u2u1', '$filename', '$filetype', '$filesize', '$fileheight', '$filewidth', '$attachedfile', '$self[username]')");
                    }
                }
                if ($rcpt['emailonu2u'] == 'yes' && $rcpt['status'] != 'Banned') {
                    $u2uurl = $SETTINGS['boardurl'] . 'u2u.php';
                    altMail($rcpt['email'], $lang['textnewu2uemail'], "$self[username] $lang[textnewu2ubody] \n$u2uurl", $additional_headers='', $additional_parameters=null);
                }
            } else {
                $errors = '<br />' . $lang['u2ublocked'];
            }
        } else {
            $errors = '<br />' . $lang['badrcpt'];
        }
        $db->free_result($query);
        return $errors;
    }

    /**
     * send()
     *
     * @param $u2uid the proposed u2uID from the address bar
     * @param $subject u2u subject
     * @param $message u2u message body
     * @param $u2upreview (out by ref) designed to take a string back from the function. Expect it to be null too
     * @return $leftpane the left hand pane view
     **/
    function send($u2uid, $msgto, $subject, $message, $u2upreview) {
        global $db, $self, $lang, $SETTINGS, $THEME, $username, $u2ucount, $cheHTML;
        global $shadow, $shadow2, $onlinetime, $fileheight, $filewidth, $table_u2u;
        global $attachedfile, $filetype, $filesize, $filename;
        global $selHTML, $timecode;
        global $oToken, $table_buddys;

        if (isset ($self['ban']) && $self['ban'] == 'u2u' || $self['ban'] == 'both') {
            error($lang['textbanfromu2u'], false, '', '', false, true, false, true);
        }
        // do u2u quota
        if (!X_STAFF && $u2ucount >= $SETTINGS['u2uquota'] && $SETTINGS['u2uquota'] > 0) {
            error($lang['u2ureachedquota'], false, '', '', false, true, false, true);
        }

        if (onSubmit('savesubmit')) {
            if (empty ($message) || empty ($subject)) {
                error($lang['u2uempty'], false, '', '', 'u2u.php', true, false, true);
            }
            $u2u_dao = new u2uDAO();
            $u2u_dao->create('', '', '', '', 'draft', $self['username'], 'Drafts', $subject, $message, 'yes', 'no');
            error($lang['imsavedmsg'], false, '', '', 'u2u.php?folder=Drafts', true, false, true);
        }

        if (onSubmit('sendsubmit')) {
            $errors = '';
            if (empty ($message) || empty ($subject)) {
                error($lang['u2uempty'], false, '', '', 'u2u.php', true, false, true);
            }

            // create flood control
            // ported from xmb 1.9.2
            if ($db->result($db->query("SELECT COUNT(u2uid) FROM " . $table_u2u . " WHERE msgfrom = '$self[username]' AND dateline > " . ($onlinetime - $SETTINGS['floodctrl'])), 0) > 0) {
                error($lang['floodprotect_u2u'], false, '', '', 'u2u.php', true, false, true);
            }

            if (strstr($msgto, ',') && X_STAFF) {
                $errors = $this->send_multi_recp($msgto, $subject, $message);
            } else {
                $errors = $this->send_recp($msgto, $subject, $message);
            }

            if (empty ($errors)) {
                error($lang['imsentmsg'], false, '', '', 'u2u.php', true, false, true);
            } else {
                error(substr($errors, 6), false, '', '', 'u2u.php', true, false, true);
            }
        } else {
            // create address book drop down
            $addresses = array ();
            $query = $db->query("SELECT * FROM " . $table_buddys . " WHERE username = '$self[username]' ORDER BY buddyname ASC");
            while ($address = $db->fetch_array($query)) {
                $addresses[] = '<option value="' . $address['buddyname'] . '">' . stripslashes($address['buddyname']) . '</option>';
            }
            $addresses = implode("\n", $addresses);
        }

        // XMB 1.9.x will not support image attachments
        $attachfile = '';
        // if ($SETTINGS['u2uattachstatus'] == 'on')
        // {
        //    eval('$attachfile = "'.template('u2u_attachmentbox').'";');
        // }

        if ($u2uid > 0 && noSubmit('previewsubmit')) {
            $query = $db->query("SELECT subject, msgfrom, message FROM " . $table_u2u . " WHERE u2uid = '$u2uid' AND owner = '$self[username]'");
            $quote = $db->fetch_array($query);

            $reply = getVar('reply');
            $forward = getVar('forward');

            if ($quote) {
                $prefixes = array (
                    $lang['textre'],
                    $lang['textfwd']
                );
                $subject = checkOutput(str_replace($prefixes, '', $quote['subject']));
                $message = checkOutput($quote['message']);
                if ($forward == 'yes') {
                    $subject = $lang['textfwd'] . ' ' . $subject;
                    $message = '[quote][i]' . $lang['origpostedby'] . ' ' . $quote['msgfrom'] . "[/i]\n" . $message . '[/quote]';
                } else
                    if ($reply == 'yes') {
                        $subject = $lang['textre'] . ' ' . $subject;
                        $message = '[quote]' . $message . '[/quote]';
                        $username = $quote['msgfrom'];
                    }
            }
            $db->free_result($query);
        } else
            if (onSubmit('previewsubmit')) {
                if (empty ($message)) {
                    error($lang['u2uempty'], false, '', '', false, true, false, true);
                }
                $u2usubject = checkOutput(censor(checkInput($subject)));
                $u2umessage = postify(checkInput($message));
                $username = checkOutput(checkInput($msgto));
                
                eval ('$u2upreview = "' . template('u2u_send_preview') . '";');
            }
        $smilieinsert = smilieinsert();
        $bbcodeinsert = bbcodeinsert();
        $leftpane = '';
        eval ('$leftpane = "' . template('u2u_send') . '";');
        return $leftpane;
    }

    /**
     * view()
     *
     * @param $u2uid the u2u ID to view
     * @return the left pane after completing the view
     **/
    function view($u2uid, $folders) {
        global $db, $THEME, $timecode, $timeoffset, $lang, $self, $daylightsavings;
        global $cheHTML, $sendoptions, $SETTINGS, $fileheight, $filewidth;
        global $attachedfile, $filetype, $filesize, $filename, $lang_align, $lang_nalign;
        global $n_height, $n_width, $oToken, $table_u2u, $table_members;

        $delchecked = '';

        if (!($u2uid > 0)) {
            error($lang['textnonechosen'], false, '', '', 'u2u.php', true, false, true);
        }

        $query = $db->query("SELECT * FROM " . $table_u2u . " WHERE u2uid = '$u2uid' AND owner = '$self[username]'");
        $u2u = $db->fetch_array($query);
        if ($u2u) {
            // $query = $db->query("SELECT * FROM ".$table_u2u."_attachments WHERE u2uid = '$u2uid' AND owner = '$self[username]'");
            // if ($db->num_rows($query) > 0)
            // {
            //     $u2u = array_merge($u2u, $db->fetch_array($query));
            // }

            if ($u2u['type'] == 'incoming') {
                $db->query("UPDATE " . $table_u2u . " SET readstatus = 'yes' WHERE u2uid = $u2u[u2uid] OR (u2uid = $u2u[u2uid]+1 AND type = 'outgoing' AND msgto = '$self[username]')");
            } else
                if ($u2u['type'] == 'draft') {
                    $db->query("UPDATE " . $table_u2u . " SET readstatus = 'yes' WHERE u2uid = $u2u[u2uid]");
                }

            if (empty ($u2u['subject'])) {
                $u2u['subject'] = $lang['textnosub'];
            }

            $u2udate = printGmDate($u2u['dateline']);
            $u2utime = printGmTime($u2u['dateline']);
            $u2udateline = $u2udate . ' ' . $lang['textat'] . ' ' . $u2utime;

            $u2usubject = checkOutput(censor($u2u['subject']));
            $u2umessage = u2uTempAmp(postify(checkOutput($u2u['message'])));
            $u2ufolder = $u2u['folder'];
            $u2ufrom = '<a href="member.php?action=viewpro&amp;member=' . rawurlencode($u2u['msgfrom']) . '" target="mainwindow">' . $u2u['msgfrom'] . '</a>';
            $u2uto = ($u2u['type'] == 'draft') ? $lang['textu2unotsent'] : '<a href="member.php?action=viewpro&amp;member=' . rawurlencode($u2u['msgto']) . '" target="mainwindow">' . $u2u['msgto'] . '</a>';

            if ($u2u['type'] == 'draft') {
                $sendoptions = '<input type="radio" name="mod" value="send" /> ' . $lang['textu2u'] . '<br />';
                $delchecked = $cheHTML;
            } else
                if ($u2u['msgfrom'] != $self['username']) {
                    $sendoptions = '<input type="radio" name="mod" value="reply" ' . $cheHTML . ' /> ' . $lang['textreply'] . '<br /><input type="radio" name="mod" value="forward" /> ' . $lang['textforward'] . '<br />';
                } else {
                    $delchecked = $cheHTML;
                }

            $mtofolder = array ();
            $mtofolder[] = '<select name="tofolder"><option value="">' . $lang['textpickfolder'] . '</option>';
            foreach ($folders as $key => $value) {
                if (is_numeric($key)) {
                    $key = $value;
                }
                $mtofolder[] = '<option value="' . $key . '">' . $value . '</option>';
            }
            $mtofolder[] = '</select>';
            $mtofolder = implode("\n", $mtofolder);
        } else {
            error($lang['u2uadmin_noperm'], false, '', '', false, true, false, true);
        }
        $db->free_result($query);

        $leftpane = '';
        eval ('$leftpane = "' . template('u2u_view') . '";');
        return $leftpane;
    }

    /**
     * u2u_print()
     *
     * @param $u2uid the u2uid to print or e-mail
     * @param boolean $eMail
     * @return No return
     **/
    function u2u_print($u2uid, $eMail = false) {
        global $db, $self, $timeoffset, $daylightsavings, $lang_code, $lang_dir, $versionpowered, $lang, $dateformat, $timecode, $charset, $THEME, $SETTINGS, $logo;
        global $table_u2u;

        if (!($u2uid > 0)) {
            error($lang['textnonechosen'], false, '', '', 'u2u.php', true, false, true);
        }

        $query = $db->query("SELECT * FROM " . $table_u2u . " WHERE u2uid = '$u2uid' AND owner = '$self[username]'");
        $u2u = $db->fetch_array($query);
        $db->free_result($query);
        if ($u2u) {
            $u2udate = printGmDate($u2u['dateline']);
            $u2utime = printGmTime($u2u['dateline']);
            $u2udateline = $u2udate . ' ' . $lang['textat'] . ' ' . $u2utime;

            $u2usubject = checkOutput(censor($u2u['subject']));
            $u2umessage = postify($u2u['message']);
            $u2ufolder = $u2u['folder'];
            $u2ufrom = $u2u['msgfrom'];
            $u2uto = ($u2u['type'] == 'draft') ? $lang['textu2unotsent'] : $u2u['msgto'];

            if ($eMail) {
                $email = $lang['textsubject'] . " " . $u2usubject . "<br />" . $lang['textfrom'] . " " . $u2ufrom . "<br />" . $lang['textto'] . " " . $u2uto . "<br />" . $lang['textu2ufolder'] . " " . $u2ufolder . "<br />" . $lang['textsent'] . " " . $u2udateline . "<br /><br />" . $u2umessage;

                $mailsys->RequireTo($self['email'], $self['username']);
                $mailsys->RequireFrom($SETTINGS['adminemail'], $SETTINGS['bbname']);
                $mailsys->RequireBody($lang['textu2utoemail'] . ' ' . $u2usubject, $email);
                $mailsys->Send();
                error($lang['contactsubmitted'], false, '', '', 'index.php', true, false, true);
            } else {
                eval ('echo stripslashes("' . template('u2u_printable') . '");');
                exit;
            }
        } else {
            error($lang['u2uadmin_noperm'], false, '', '', false, true, false, true);
        }
    }

    /**
     * delete()
     *
     * Delete a message ... either by moving it to the Trash, or Emptying the Trash folder
     *
     * @param $u2uid u2u message to move or delete
     * @return Nothing.
     **/
    function delete($u2uid) {
        global $db, $self, $lang, $THEME, $table_u2u;

        $folder = $_SESSION['folder'];

        if (!($u2uid > 0)) {
            error($lang['textnonechosen'], false, '', '', 'u2u.php', true, false, true);
        }

        if ($folder == 'Trash') {
            $db->query("DELETE FROM " . $table_u2u . " WHERE u2uid = '$u2uid' AND owner = '$self[username]'");
            $db->query("DELETE FROM " . $table_u2u . "_attachments WHERE u2uid = '$u2uid' AND owner = '$self[username]'");
        } else {
            $db->query("UPDATE " . $table_u2u . " SET folder = 'Trash' WHERE u2uid = '$u2uid' AND owner = '$self[username]'");
        }

        error($lang['imdeletedmsg'], false, '', '', 'u2u.php?folder=' . $folder, true, false, true);
    }

    /**
     * mod_delete()
     *
     * Delete multiple U2U's at once, again by moving them to the Trash or if in the Trash, really delete
     *
     * @param $u2u_select checkbox array sourced from the HTML form
     * @return Nothing.
     **/
    function mod_delete($u2u_select) {
        global $db, $self, $lang, $SETTINGS, $THEME, $table_u2u;
        global $attachedfile, $filetype, $filesize, $filename;
        global $fileheight, $filewidth;

        $folder = $_SESSION['folder'];

        $in = '';
        foreach ($u2u_select as $key => $value) {
            $value = valInt($value);
            $in .= (empty ($in)) ? "$value" : ", $value";
        }

        if ($folder == 'Trash') {
            $db->query("DELETE FROM " . $table_u2u . " WHERE u2uid IN($in) AND owner = '$self[username]'");
            $db->query("DELETE FROM " . $table_u2u . "_attachments WHERE u2uid IN($in) AND owner = '$self[username]'");
        } else {
            $db->query("UPDATE " . $table_u2u . " SET folder = 'Trash' WHERE u2uid IN($in) AND owner = '$self[username]'");
        }

        error($lang['imdeletedmsg'], false, '', '', 'u2u.php?folder=' . $folder, true, false, true);
    }

    /**
    * function() - short description of function
    *
    * Long description of function
    *
    * @param    $varname    type, what it does
    * @return   type, what the return does
    */
    function move($u2uid, $tofolder) {
        global $db, $self, $lang, $folders, $type, $SETTINGS, $THEME, $table_u2u;
        global $attachedfile, $filetype, $filesize, $filename;
        global $fileheight, $filewidth;

        $folder = $_SESSION['folder'];

        if (!($u2uid > 0)) {
            error($lang['textnonechosen'], false, '', '', "u2u.php", true, false, true);
        }

        if (empty ($tofolder)) {
            error($lang['textnofolder'], false, '', '', "u2u.php?action=view&amp;u2uid=$u2uid", true, false, true);
        } else {
            if (!(in_array($tofolder, $folders) || $tofolder == 'Inbox' || $tofolder == 'Outbox' || $tofolder == 'Drafts') || ($tofolder == 'Inbox' && ($type == 'draft' || $type == 'outgoing')) || ($tofolder == 'Outbox' && ($type == 'incoming' || $type == 'draft')) || ($tofolder == 'Drafts' && ($type == 'incoming' || $type == 'outgoing'))) {
                error($lang['textcantmove'], false, '', '', 'u2u.php?action=view&amp;u2uid=' . $u2uid . '', true, false, true);
            }

            $db->query("UPDATE " . $table_u2u . " SET folder = '$tofolder' WHERE u2uid = '$u2uid' AND owner = '$self[username]'");

            error($lang['textmovesucc'], false, '', '', 'u2u.php?folder=' . $folder, true, false, true);
        }
    }

    /**
    * function() - short description of function
    *
    * Long description of function
    *
    * @param    $varname    type, what it does
    * @return   type, what the return does
    */
    function mod_move($tofolder, $u2u_select) {
        global $db, $self, $lang, $folders, $SETTINGS, $THEME, $table_u2u;
        global $attachedfile, $filetype, $filesize, $filename;
        global $fileheight, $filewidth;

        $folder = $_SESSION['folder'];

        $in = '';
        foreach ($u2u_select as $value) {
            $value = valInt($value);
            $type = formVar('type' . $value);
            if ((in_array($tofolder, $folders) || $tofolder == 'Inbox' || $tofolder == 'Outbox' || $tofolder == 'Drafts') && !($tofolder == 'Inbox' && ($type == 'draft' || $type == 'outgoing')) && !($tofolder == 'Outbox' && ($type == 'incoming' || $type == 'draft')) && !($tofolder == 'Drafts' && ($type == 'incoming' || $type == 'outgoing'))) {
                $in .= (empty ($in)) ? "$value" : ",$value";
            }
        }

        if (empty ($in)) {
            error($lang['textcantmove'], false, '', '', 'u2u.php', true, false, true);
        }

        $db->query("UPDATE " . $table_u2u . " SET folder = '$tofolder' WHERE u2uid IN($in) AND owner = '$self[username]'");

        error($lang['textmovesucc'], false, '', '', 'u2u.php?folder=' . $folder, true, false, true);
    }

    /**
     * markUnread()
     *
     * Mark one U2U as unread
     *
     * @param $u2uid The u2uid to mark unread
     * @param $type ingoing, outgoing, draft, etc
     * @return no return, function exits page
     **/
    function markUnread($u2uid, $type) {
        global $db, $self, $lang, $SETTINGS, $THEME, $table_u2u;

        $folder = $_SESSION['folder'];

        if (!($u2uid > 0)) {
            error($lang['textnonechosen'], false, '', '', 'u2u.php', true, false, true);
        }

        if ($type == 'outgoing') {
            error($lang['textnomur'], false, '', '', 'u2u.php', true, false, true);
        }

        $db->query("UPDATE " . $table_u2u . " SET readstatus = 'no' WHERE u2uid = $u2uid AND owner = '$self[username]'");

        error($lang['textmarkedunread'], false, '', '', 'u2u.php?folder=' . $folder, true, false, true);
    }

    /**
     * mod_markUnread()
     *
     * @param $u2uselect the HTML select list built up by the form
     * @return no return, function exits page
     **/
    function mod_markUnread($u2u_select) {
        global $db, $lang, $self, $SETTINGS, $THEME, $table_u2u;
        global $attachedfile, $filetype, $filesize, $filename;
        global $fileheight, $filewidth, $u2uid;

        $u2uid = intval($u2uid);
        $folder = $_SESSION['folder'];

        if (empty ($folder)) {
            error($lang['textnofolder'], false, '', '', 'u2u.php?action=view&amp;u2uid=' . $u2uid . '', true, false, true);
        }

        if ($u2u_select == '') {
            error($lang['textnonechosen'], false, '', '', 'u2u.php', true, false, true);
        }

        $in = '';

        foreach ($u2u_select as $value) {
            if (formVar('type' . $value) != 'outgoing') {
                $value = valInt($value);
                $in .= (empty ($in)) ? "$value" : ",$value";
            }
        }

        if (empty ($in)) {
            error($lang['textnonechosen'], false, '', '', 'u2u.php', true, false, true);
        }

        $db->query("UPDATE " . $table_u2u . " SET readstatus = 'no' WHERE u2uid IN($in) AND owner = '$self[username]'");

        error($lang['textmarkedunread'], false, '', '', 'u2u.php?folder=' . $folder, true, false, true);
    }

    /**
    * function() - short description of function
    *
    * Long description of function
    *
    * @param    $varname    type, what it does
    * @return   type, what the return does
    */
    function updateFolders($u2ufolders, $folders) {
        global $db, $lang, $self, $farray, $SETTINGS, $THEME;
        global $attachedfile, $filetype, $filesize, $filename;
        global $fileheight, $filewidth, $table_members;

        $error = '';
        $newfolders = explode(',', $u2ufolders);
        foreach ($newfolders as $key => $value) {
            $newfolders[$key] = checkInput($value);
            if (empty ($newfolders[$key])) {
                unset ($newfolders[$key]);
            }
        }

        foreach ($folders as $value) {
            if (isset ($farray[$value]) && $farray[$value] != 0 && !in_array($value, $newfolders) && !in_array($value, array (
                    'Inbox',
                    'Outbox',
                    'Drafts',
                    'Trash'
                ))) {
                $newfolders[] = checkInput($value);
                $error .= (empty ($error)) ? '<br />' . $lang['foldersupdateerror'] . ' ' . $value : ', ' . $value;
            }
        }

        $u2ufolders = $db->escape(implode(', ', $newfolders));

        $db->query("UPDATE " . $table_members . " SET u2ufolders = '$u2ufolders' WHERE username = '$self[username]'");

        error($lang['foldersupdate'] . $error, false, '', '', 'u2u.php?folder=Inbox', true, false, true);
    }

    /**
     * viewIgnoreList()
     *
     * This function displays the ignore list from the form submission variables, and
     * attempts to update the ignore list if the Ignore Submit button has been pressed
     *
     * @return the left pane to render if the function returns (which it doesn't for changing the ignore list)
     **/
    function viewIgnoreList() {
        global $self, $lang, $db;
        global $THEME, $table_members, $oToken;

        $leftpane = '';
        $ignorelist = formVar('ignorelist');
        if (onSubmit('ignoresubmit')) {
            $self['ignoreu2u'] = $db->escape(checkInput($ignorelist));
            $db->query("UPDATE " . $table_members . " SET ignoreu2u = '" . $self['ignoreu2u'] . "' WHERE username = '$self[username]'");
            error($lang['ignoreupdate'], false, '', '', 'u2u.php?action=ignore', true, false, true);
        } else {
            $self['ignoreu2u'] = checkOutput($self['ignoreu2u']);
            eval ('$leftpane = "' . template('u2u_ignore') . '";');
        }

        return $leftpane;
    }

    /**
     * viewFolders()
     *
     * Displays the current folder's u2u list, otherwise none shown.
     *
     * @param $folders display a named folder instead of the Inbox folder
     * @return nothing.
     **/
    function viewFolders($folders) {
        global $db, $self, $lang, $SETTINGS, $THEME, $table_u2u;
        global $timeoffset, $dateformat, $timecode, $shadow, $shadow2, $daylightsavings;
        global $attachedfile, $filetype, $filesize, $filename, $lang_align, $lang_nalign;
        global $oToken, $fileheight, $filewidth, $table_whosonline;

        $u2usin = $u2usout = $u2usdraft = $u2usent = '';

        $folder = $_SESSION['folder'];

        if (empty ($folder)) {
            $folder = "Inbox";
        }
        $folder = $db->escape($folder);

        $query = $db->query("SELECT u.*, w.username, w.invisible FROM " . $table_u2u . " u LEFT JOIN " . $table_whosonline . " w ON (u.msgto = w.username OR u.msgfrom = w.username) AND w.username != '$self[username]' WHERE u.folder = '$folder' AND u.owner = '$self[username]' ORDER BY dateline DESC");
        while ($u2u = $db->fetch_array($query)) {
            if ($u2u['readstatus'] == 'yes') {
                $u2ureadstatus = $lang['textread'];
            } else {
                $u2ureadstatus = '<strong>' . $lang['textunread'] . '</strong>';
            }

            if (empty ($u2u['subject'])) {
                $u2u['subject'] = $lang['textnosub'];
            }

            $u2usubject = checkOutput(censor($u2u['subject']));

            if ($u2u['type'] == 'incoming') {
                if ($u2u['msgfrom'] == $u2u['username'] || $u2u['msgfrom'] == $self['username']) {
                    if ($u2u['invisible'] == 1) {
                        if (X_ADMIN) {
                            $online = $lang['hidden'];
                        } else {
                            $online = $lang['textoffline'];
                        }
                    } else {
                        $online = $lang['textonline'];
                    }
                } else {
                    $online = $lang['textoffline'];
                }
                $u2usent = '<a href="member.php?action=viewpro&amp;member=' . rawurlencode($u2u['msgfrom']) . '" target="_blank">' . $u2u['msgfrom'] . '</a> (' . $online . ')';
            } else
                if ($u2u['type'] == 'outgoing') {
                    if ($u2u['msgto'] == $u2u['username'] || $u2u['msgto'] == $self['username']) {
                        if ($u2u['invisible'] == 1) {
                            if (X_ADMIN) {
                                $online = $lang['hidden'];
                            } else {
                                $online = $lang['textoffline'];
                            }
                        } else {
                            $online = $lang['textonline'];
                        }
                    } else {
                        $online = $lang['textoffline'];
                    }
                    $u2usent = '<a href="member.php?action=viewpro&amp;member=' . rawurlencode($u2u['msgto']) . '" target="_blank">' . $u2u['msgto'] . '</a> (' . $online . ')';
                } else
                    if ($u2u['type'] == 'draft') {
                        $u2usent = $lang['textu2unotsent'];
                    }

            $u2udate = printGmDate($u2u['dateline']);
            $u2utime = printGmTime($u2u['dateline']);            

            $u2udateline = $lang['lastreply1'] . ' ' . $u2udate . ' ' . $lang['textat'] . ' ' . $u2utime;

            switch ($u2u['type']) {
                case 'outgoing' :
                    $u2us = 'u2usout';
                    break;
                case 'draft' :
                    $u2us = 'u2usdraft';
                    break;
                case 'incoming' :
                default :
                    $u2us = 'u2usin';
                    break;
            }

            eval ('$$u2us .= "' . template('u2u_row') . '";');
        }
        $db->free_result($query);

        $u2utrash = '';
        if ($u2usin == '') {
            eval ('$u2usin = "' . template('u2u_row_none') . '";');
        } else {
            if ($folder == 'Trash') {
                $u2utrash .= $u2usin;
            }
        }

        if ($u2usout == '') {
            eval ('$u2usout = "' . template('u2u_row_none') . '";');
        } else {
            if ($folder == 'Trash') {
                $u2utrash .= $u2usout;
            }
        }

        if ($u2usdraft == '') {
            eval ('$u2usdraft = "' . template('u2u_row_none') . '";');
        } else {
            if ($folder == 'Trash') {
                $u2utrash .= $u2usdraft;
            }
        }

        if ($u2utrash == '') {
            eval ('$u2utrash = "' . template('u2u_row_none') . '";');
        }

        switch ($folder) {
            case 'Outbox' :
                eval ('$u2ulist = "' . template('u2u_outbox') . '";');
                break;
            case 'Drafts' :
                eval ('$u2ulist = "' . template('u2u_drafts') . '";');
                break;
            case 'Trash' :
                eval ('$u2ulist = "' . template('u2u_trash') . '";');
                break;
            case 'Inbox' :
            default :
                eval ('$u2ulist = "' . template('u2u_inbox') . '";');
                break;
        }

        $mtofolder = array ();
        $mtofolder[] = '<select name="tofolder"><option value="">' . $lang['textpickfolder'] . '</option>';
        foreach ($folders as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
            }
            $mtofolder[] = '<option value="' . $key . '">' . $value . '</option>';
        }
        $mtofolder[] = '</select>';
        $mtofolder = implode("\n", $mtofolder);

        $leftpane = '';
        eval ('$leftpane = "' . template('u2u_main') . '";');
        return $leftpane;
    }

    /**
     * viewFolderList()
     *
     *  Generate a folder list from the database and leave the details in
     *  various globals
     *
     * @return a string containing the formatted folder list in HTML format
     **/
    function viewFolderList() {
        global $db, $self, $lang, $SETTINGS, $THEME;
        global $folderlist, $folders, $farray, $table_u2u;

        if (isset ($_SESSION['folder'])) {
            $folder = $_SESSION['folder'];
        } else {
            $folder = '';
        }

        $u2ucount = 0;
        $folders = (empty ($self['u2ufolders'])) ? array () : explode(',', $self['u2ufolders']);
        foreach ($folders as $key => $value) {
            $folders[$key] = checkInput($value);
        }

        sort($folders);
        $folders = array_merge(array (
            'Inbox' => $lang['textu2uinbox'],
            'Outbox' => $lang['textu2uoutbox']
        ), $folders, array (
            'Drafts' => $lang['textu2udrafts'],
            'Trash' => $lang['textu2utrash']
        ));

        $query = $db->query("SELECT folder, count(u2uid) as count FROM " . $table_u2u . " WHERE owner = '$self[username]' GROUP BY folder ORDER BY folder ASC");
        $farray = array ();
        while ($flist = $db->fetch_array($query)) {
            $farray[$flist['folder']] = $flist['count'];
            $u2ucount += $flist['count'];
        }
        $db->free_result($query);

        $emptytrash = $folderlist = '';

        foreach ($folders as $link => $value) {
            if (is_numeric($link)) {
                $link = $value;
            }
            if (empty ($folder) && isset ($u2uid)) {
                $query = $db->query("SELECT folder FROM " . $table_u2u . " WHERE owner = '$self[username]' AND u2uid = '$u2uid'");
                $viewfolder = $db->result($query);
                $db->free_result($query);

                if ($link == $viewfolder) {
                    $value = '<strong>' . $value . '</strong>';
                }
            }

            if ($link == $folder) {
                $value = '<strong>' . $value . '</strong>';
            }

            $count = (empty ($farray[$link])) ? 0 : $farray[$link];
            if ($link == 'Trash') {
                if ($count != 0) {
                    $emptytrash = ' (<a href="u2u.php?action=emptytrash">' . $lang['textemptytrash'] . '</a>)';
                }
            }
            $link = rawurlencode($link);
            eval ('$folderlist .= "' . template('u2u_folderlink') . '";');
        }

        return $u2ucount;
    }
}
?>
