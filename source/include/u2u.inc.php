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

define('U2U_FOLDER_COL_SIZE', 32);

function u2u_msg($msg, $redirect) {
    global $u2uheader, $u2ufooter, $tablewidth, $bordercolor, $tablespace, $THEME, $altbg1;

    if (!empty($redirect)) {
        redirect($redirect);
    }
    eval('echo "'.template('u2u_msg').'";');
    exit;
}

function db_u2u_insert($to, $from, $type, $owner, $folder, $subject, $message, $isRead, $isSent) {
    global $db, $onlinetime;
    $db->query("INSERT INTO ".X_PREFIX."u2u (msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus) VALUES ('$to', '$from', '$type', '$owner', '$folder', '$subject', '$message', '$onlinetime', '$isRead', '$isSent')");
}

function u2u_send_multi_recp($msgto, $subject, $message, $u2uid=0) {
    $errors = '';
    $recipients = array_unique(array_map('trim', explode(',', $msgto)));

    foreach($recipients as $recp) {
        $errors .= u2u_send_recp($recp, $subject, $message, $u2uid);
    }

    return $errors;
}

/**
 * Sends a message from the current user to the specified username.
 *
 * Assumes the current user is already authenticated and not banned from U2U.
 *
 * @since 1.9.1
 * @param string $msgto XMB username, must be SQL safe.
 * @param string $subject Message subject line, must be double-slashed.
 * @param string $message Message body, must be double-slashed.
 * @param int $u2uid Optional.
 * @return string Empty string on success, HTML formatted messages on failure.
 */
function u2u_send_recp($msgto, $subject, $message, $u2uid=0) {
    global $db, $self, $SETTINGS, $lang, $onlinetime, $bbname, $adminemail, $cookiedomain, $del, $oToken, $xmbuser, $full_url;

    $del = ('yes' === $del) ? 'yes' : 'no';
    $errors = '';

    $query = $db->query("SELECT username, email, lastvisit, ignoreu2u, emailonu2u, status, langfile FROM ".X_PREFIX."members WHERE username='$msgto'");
    if ($rcpt = $db->fetch_array($query)) {
        $ilist = array_map('trim', explode(',', $rcpt['ignoreu2u']));
        if (!in_array($self['username'], $ilist) || X_ADMIN) {
            $db->escape_fast($rcpt['username']);
            db_u2u_insert($rcpt['username'], $xmbuser, 'incoming', $rcpt['username'], 'Inbox', $subject, $message, 'no', 'yes');
            if ($self['saveogu2u'] == 'yes') {
                db_u2u_insert($rcpt['username'], $xmbuser, 'outgoing', $xmbuser, 'Outbox', $subject, $message, 'no', 'yes');
            }

            $u2uid = (int) $u2uid;
            if ($del == 'yes' && $u2uid > 0) {
                $db->query("UPDATE ".X_PREFIX."u2u SET folder='Trash' WHERE u2uid='$u2uid' AND owner='$xmbuser'");
            }

            if ($rcpt['emailonu2u'] == 'yes' && $rcpt['status'] != 'Banned') {
                $lang2 = loadPhrases(array('charset','textnewu2uemail','textnewu2ubody'));
                $translate = $lang2[$rcpt['langfile']];
                $u2uurl = $full_url.'u2u.php';
                $rawusername = htmlspecialchars_decode($self['username'], ENT_QUOTES);
                $rawaddress = htmlspecialchars_decode($rcpt['email'], ENT_QUOTES);
                $rawbbname = htmlspecialchars_decode($bbname, ENT_NOQUOTES);
                $headers = array();
                $headers[] = smtpHeaderFrom($rawbbname, $adminemail);
                $headers[] = 'X-Mailer: PHP';
                $headers[] = 'X-AntiAbuse: Board servername - '.$cookiedomain;
                $headers[] = 'X-AntiAbuse: Username - '.$rawusername;
                $headers[] = 'Content-Type: text/plain; charset='.$translate['charset'];
                $headers = implode("\r\n", $headers);
                altMail($rawaddress, $translate['textnewu2uemail'], "$rawusername ".$translate['textnewu2ubody']." \n$u2uurl", $headers);
            }
        } else {
            $errors = '<br />'.$lang['u2ublocked'];
        }
    } else {
        $errors = '<br />'.$lang['badrcpt'];
    }
    $db->free_result($query);

    return $errors;
}

function u2u_send($u2uid, $msgto, $subject, $message, $u2upreview) {
    global $db, $self, $lang, $xmbuser, $SETTINGS, $del, $full_url;
    global $u2uheader, $u2ufooter, $u2ucount, $u2uquota, $oToken;
    global $altbg1, $altbg2, $bordercolor, $THEME, $tablespace, $cattext, $thewidth;
    global $forward, $reply, $previewsubmit;

    $dbsubject = addslashes($subject); //message and subject were historically double-slashed
    $dbmessage = addslashes($message);
    $db->escape_fast($dbsubject);
    $db->escape_fast($dbmessage);
    $dbto = $db->escape($msgto);

    $leftpane = '';
    $del = ($del == 'yes') ? 'yes' : 'no';
    $username = postedVar('username', 'javascript', TRUE, FALSE, TRUE, 'g'); //username is the param from u2u links on profiles.

    if ($self['ban'] == 'u2u' || $self['ban'] == 'both') {
        error($lang['textbanfromu2u'], false, $u2uheader, $u2ufooter, false, true, false, false);
    }

    if (!X_STAFF && $u2ucount >= $u2uquota && $u2uquota > 0) {
        error($lang['u2ureachedquota'], false, $u2uheader, $u2ufooter, false, true, false, false);
    }

    if (onSubmit('savesubmit')) {
        // fixed by John Briggs
        $dbsubject = (empty($dbsubject) ? $db->escape($lang['textnosub']) : $dbsubject);

        if (empty($message)) {
            error($lang['u2uempty'], false, $u2uheader, $u2ufooter, false, true, false, false);
        }
        db_u2u_insert('', '', 'draft', $xmbuser, 'Drafts', $dbsubject, $dbmessage, 'yes', 'no');
        u2u_msg($lang['imsavedmsg'], $full_url.'u2u.php?folder=Drafts');
    }

    if (onSubmit('sendsubmit')) {
        $errors = '';
        // fixed by John Briggs
        $dbsubject = (empty($dbsubject) ? $db->escape($lang['textnosub']) : $dbsubject);

        // fixed lang variable use by John Briggs
        if (empty($message)) {
            error($lang['u2umsgempty'], false, $u2uheader, $u2ufooter, false, true, false, false);
        }

        if ($db->result($db->query("SELECT count(u2uid) FROM ".X_PREFIX."u2u WHERE msgfrom='$xmbuser' AND dateline > ".(time()-$SETTINGS['floodctrl'])), 0) > 0) {
            error($lang['floodprotect_u2u'], false, $u2uheader, $u2ufooter, false, true, false, false);
        }

        $u2uid = (int) $_POST['u2uid'];

        if (strstr($msgto, ',') && X_STAFF) {
            $errors = u2u_send_multi_recp($dbto, $dbsubject, $dbmessage, $u2uid);
        } else {
            $errors = u2u_send_recp($dbto, $dbsubject, $dbmessage, $u2uid);
        }

        if (empty($errors)) {
            u2u_msg($lang['imsentmsg'], $full_url.'u2u.php');
        } else {
            u2u_msg(substr($errors, 6) , $full_url.'u2u.php');
        }
    }

    if ($u2uid > 0) {
        $query = $db->query("SELECT subject, msgfrom, message FROM ".X_PREFIX."u2u WHERE u2uid='$u2uid' AND owner='$xmbuser'");
        $quote = $db->fetch_array($query);
        if ($quote) {
            if (!isset($previewsubmit)) {
                $prefixes = array($lang['textre'].' ', $lang['textfwd'].' ');
                $subject = str_replace($prefixes, '', $quote['subject']);
                $message = rawHTMLmessage(stripslashes($quote['message']));  //message and subject were historically double-slashed
                if ($forward == 'yes') {
                    $subject = $lang['textfwd'].' '.$subject;
                    $message = '[quote][i]'.$lang['origpostedby'].' '.$quote['msgfrom']."[/i]\n".$message.'[/quote]';
                } else if ($reply == 'yes') {
                    $subject = $lang['textre'].' '.$subject;
                    $message = '[quote]'.$message.'[/quote]';
                    $username = $quote['msgfrom'];
                }
            }
        }
        $db->free_result($query);
    }

    if (isset($previewsubmit)) {
        $subject = rawHTMLsubject($subject);
        $u2usubject = $subject;
        $u2umessage = postify($message, "no", "", "yes", "no");
        $message = rawHTMLmessage($message);
        eval('$u2upreview = "'.template('u2u_send_preview').'";');
        $username = $msgto;
    }

    eval('$leftpane = "'.template('u2u_send').'";');
    return $leftpane;
}

function u2u_view($u2uid, $folders) {
    global $db, $dateformat, $timecode, $timeoffset, $addtime, $lang, $self, $oToken, $xmbuser;
    global $altbg1, $altbg2, $bordercolor, $THEME, $tablespace, $cattext, $thewidth, $full_url;
    global $sendoptions, $u2uheader, $u2ufooter, $SETTINGS;

    $delchecked = '';
    $leftpane = '';

    $u2uid = (int) $u2uid;

    if (!($u2uid > 0)) {
        error($lang['textnonechosen'], false, $u2uheader, $u2ufooter, $full_url.'u2u.php', true, false, false);
        return;
    }

    $query = $db->query("SELECT u.*, m.avatar FROM ".X_PREFIX."u2u AS u LEFT JOIN ".X_PREFIX."members AS m ON u.msgfrom=m.username WHERE u2uid='$u2uid' AND owner='$xmbuser'");
    $u2u = $db->fetch_array($query);
    if ($u2u) {
        $u2uavatar = '';
        if ($u2u['type'] == 'incoming') {
            $db->query("UPDATE ".X_PREFIX."u2u SET readstatus='yes' WHERE u2uid=$u2u[u2uid] OR (u2uid=$u2u[u2uid]+1 AND type='outgoing' AND msgto='$xmbuser')");
            if ($SETTINGS['avastatus'] != 'off' And $u2u['avatar'] != '') {
                $u2uavatar = '<br /><img src="'.$u2u['avatar'].'" />';
            }
        } else if ($u2u['type'] == 'draft') {
            $db->query("UPDATE ".X_PREFIX."u2u SET readstatus='yes' WHERE u2uid=$u2u[u2uid]");
            if ($SETTINGS['avastatus'] != 'off' And $self['avatar'] != '') {
                $u2uavatar = '<br /><img src="'.$self['avatar'].'" />';
            }
        } else {
            if ($SETTINGS['avastatus'] != 'off' And $self['avatar'] != '') {
                $u2uavatar = '<br /><img src="'.$self['avatar'].'" />';
            }
        }

        $adjTime = ($timeoffset * 3600) + ($addtime * 3600);
        $u2udate = gmdate($dateformat, $u2u['dateline'] + $adjTime);
        $u2utime = gmdate($timecode, $u2u['dateline'] + $adjTime);
        $u2udateline = $u2udate.' '.$lang['textat'].' '.$u2utime;
        $u2usubject = rawHTMLsubject(stripslashes($u2u['subject'])); //message and subject were historically double-slashed
        $u2umessage = postify(stripslashes($u2u['message']), 'no', '', 'yes', 'no');
        $u2ufolder = $u2u['folder'];
        $u2ufrom = '<a href="member.php?action=viewpro&amp;member='.recodeOut($u2u['msgfrom']).'" target="mainwindow">'.$u2u['msgfrom'].'</a>';
        $u2uto = ($u2u['type'] == 'draft') ? $lang['textu2unotsent'] : '<a href="member.php?action=viewpro&amp;member='.recodeOut($u2u['msgto']).'" target="mainwindow">'.$u2u['msgto'].'</a>';

        if ($u2u['type'] == 'draft') {
            $sendoptions = '<input type="radio" name="mod" value="send" /> '.$lang['textu2u'].'<br />';
            $delchecked = ' checked="checked"';
        } else if ($u2u['msgfrom'] != $self['username']) {
            $sendoptions = '<input type="radio" name="mod" value="reply" checked="checked" /> '.$lang['textreply'].'<br /><input type="radio" name="mod" value="replydel" /> '.$lang['textreplytrash'].'<br /><input type="radio" name="mod" value="forward" /> '.$lang['textforward'].'<br />';
        } else {
            $delchecked = ' checked="checked"';
        }

        $mtofolder = array();
        $mtofolder[] = '<select name="tofolder">';
        $mtofolder[] = '<option value="">'.$lang['textpickfolder'].'</option>';
        foreach($folders as $key => $value) {
            if (is_numeric($key)) {
                $key = $value;
            }
            $mtofolder[] = '<option value="'.$key.'">'.$value.'</option>';
        }
        $mtofolder[] = '</select>';
        $mtofolder = implode("\n", $mtofolder);
    } else {
        error($lang['u2uadmin_noperm'], false, $u2uheader, $u2ufooter, false, true, false, false);
    }
    $db->free_result($query);

    eval('$leftpane = "'.template('u2u_view').'";');
    return $leftpane;
}

function u2u_print($u2uid, $eMail = false) {
    global $SETTINGS, $css, $db, $self, $timeoffset, $lang, $u2uheader, $full_url, $cookiedomain, $adminemail,
           $u2ufooter, $dateformat, $timecode, $addtime, $charset, $bbname, $logo, $oToken, $xmbuser, $text;

    $mailHeader = '';
    $mailFooter = '';

    $u2uid = (int) $u2uid;

    if (!($u2uid > 0)) {
        error($lang['textnonechosen'], false, $u2uheader, $u2ufooter, $full_url.'u2u.php', true, false, false);
        return;
    }

    $query = $db->query("SELECT * FROM ".X_PREFIX."u2u WHERE u2uid='$u2uid' AND owner='$xmbuser'");
    $u2u = $db->fetch_array($query);
    $db->free_result($query);

    if ($u2u) {
        $adjTime = ($timeoffset * 3600) + ($addtime * 3600);
        $u2udate = gmdate($dateformat, $u2u['dateline'] +  $adjTime);
        $u2utime = gmdate($timecode, $u2u['dateline'] + $adjTime);
        $u2udateline = $u2udate.' '.$lang['textat'].' '.$u2utime;
        $u2usubject = rawHTMLsubject(stripslashes($u2u['subject']));  //message and subject were historically double-slashed
        $u2umessage = postify(stripslashes($u2u['message']), 'no', 'no', 'yes', 'no', 'yes', 'yes', false, "no", "yes");
        $u2ufolder = $u2u['folder'];
        $u2ufrom = $u2u['msgfrom'];
        $u2uto = ($u2u['type'] == 'draft') ? $lang['textu2unotsent'] : $u2u['msgto'];

        if ($eMail) {
            eval('$mailHeader = "'.template('email_html_header').'";');
            eval('$mailFooter = "'.template('email_html_footer').'";');
            $email = $mailHeader.$lang['textsubject']." ".$u2usubject."<br />\n".$lang['textfrom']." ".$u2ufrom."<br />\n".$lang['textto']." ".$u2uto."<br />\n".$lang['textu2ufolder']." ".$u2ufolder."<br />\n".$lang['textsent']." ".$u2udateline."<br />\n<br />\n".$u2umessage."<br />\n<br />\n".$full_url.$mailFooter;
            $rawemail = htmlspecialchars_decode($self['email'], ENT_QUOTES);
            $rawuser = htmlspecialchars_decode($self['username'], ENT_QUOTES);
            $rawbbname = htmlspecialchars_decode($bbname, ENT_NOQUOTES);
            $headers = array();
            $headers[] = smtpHeaderFrom($rawbbname, $adminemail);
            $headers[] = 'X-Mailer: PHP';
            $headers[] = 'X-AntiAbuse: Board servername - '.$cookiedomain;
            $headers[] = 'X-AntiAbuse: Username - '.$rawuser;
            $headers[] = 'Content-Type: text/html; charset='.$charset;
            $headers = implode("\r\n", $headers);
            $result = altMail($rawemail, $lang['textu2utoemail']." ".$u2usubject, $email, $headers);
            u2u_msg($lang['textu2utoemailsent'], $full_url.'u2u.php?action=view&u2uid='.$u2uid);
        } else {
            eval('echo "'.template('u2u_printable').'";');
            exit;
        }
    } else {
        error($lang['u2uadmin_noperm'], false, $u2uheader, $u2ufooter, false, true, false, false);
    }
}

function u2u_delete($u2uid, $folder) {
    global $db, $self, $lang, $xmbuser, $u2uheader, $u2ufooter, $oToken, $full_url;

    $u2uid = (int) $u2uid;

    if (!($u2uid > 0)) {
        error($lang['textnonechosen'], false, $u2uheader, $u2ufooter, $full_url.'u2u.php', true, false, false);
        return;
    }

    if ($folder == "Trash") {
        $db->query("DELETE FROM ".X_PREFIX."u2u WHERE u2uid='$u2uid' AND owner='$xmbuser'");
    } else {
        $db->query("UPDATE ".X_PREFIX."u2u SET folder='Trash' WHERE u2uid='$u2uid' AND owner='$xmbuser'");
    }

    u2u_msg($lang['imdeletedmsg'], $full_url.'u2u.php?folder='.recodeOut($folder));
}

function u2u_mod_delete($folder, $u2u_select) {
    global $db, $self, $lang, $oToken, $xmbuser, $full_url;

    $in = '';
    foreach($u2u_select as $value) {
        $value = (int) $value;
        $in .= ($value > 0 ? (empty($in) ? "$value" : ", $value") : '');
    }

    if ($folder == "Trash") {
        $db->query("DELETE FROM ".X_PREFIX."u2u WHERE u2uid IN($in) AND owner='$xmbuser'");
    } else {
        $db->query("UPDATE ".X_PREFIX."u2u SET folder='Trash' WHERE u2uid IN($in) AND owner='$xmbuser'");
    }

    u2u_msg($lang['imdeletedmsg'], $full_url.'u2u.php?folder='.recodeOut($folder));
}

function u2u_move($u2uid, $tofolder) {
    global $db, $self, $lang, $u2uheader, $u2ufooter, $folders, $type, $folder, $oToken, $xmbuser, $full_url;

    $u2uid = (int) $u2uid;

    if (!($u2uid > 0)) {
        error($lang['textnonechosen'], false, $u2uheader, $u2ufooter, $full_url.'u2u.php', true, false, false);
        return;
    }

    if (empty($tofolder)) {
        error($lang['textnofolder'], false, $u2uheader, $u2ufooter, $full_url."u2u.php?action=view&amp;u2uid=$u2uid", true, false, false);
    } else {
        if (!(in_array($tofolder, $folders) || $tofolder == 'Inbox' || $tofolder == 'Outbox' || $tofolder == 'Drafts') || ($tofolder == 'Inbox' && ($type == 'draft' || $type == 'outgoing')) || ($tofolder == 'Outbox' && ($type == 'incoming' || $type == 'draft')) || ($tofolder == 'Drafts' && ($type == 'incoming' || $type == 'outgoing'))) {
            error($lang['textcantmove'], false, $u2uheader, $u2ufooter, $full_url."u2u.php?action=view&amp;u2uid=$u2uid", true, false, false);
        }

        $db->escape_fast($tofolder);
        $db->query("UPDATE ".X_PREFIX."u2u SET folder='$tofolder' WHERE u2uid='$u2uid' AND owner='$xmbuser'");

        u2u_msg($lang['textmovesucc'], $full_url.'u2u.php?folder='.recodeOut($folder));
    }
}

function u2u_mod_move($tofolder, $u2u_select) {
    global $db, $self, $lang, $u2uheader, $u2ufooter, $folders, $oToken, $folder, $xmbuser, $full_url;

    $in = '';
    foreach($u2u_select as $value) {
        $value = (int) $value;
        if ($value > 0) {
            $type = $GLOBALS['type'.$value];
            if ((in_array($tofolder, $folders) || $tofolder == 'Inbox' || $tofolder == 'Outbox' || $tofolder == 'Drafts') && !($tofolder == 'Inbox' && ($type == 'draft' || $type == 'outgoing')) && !($tofolder == 'Outbox' && ($type == 'incoming' || $type == 'draft')) && !($tofolder == 'Drafts' && ($type == 'incoming' || $type == 'outgoing'))) {
                $in .= (empty($in)) ? "$value" : ",$value";
            }
        }
    }

    if (empty($in)) {
        error($lang['textcantmove'], false, $u2uheader, $u2ufooter, $full_url.'u2u.php?folder='.recodeOut($folder), true, false, false);
        return;
    }

    $db->escape_fast($tofolder);
    $db->query("UPDATE ".X_PREFIX."u2u SET folder='$tofolder' WHERE u2uid IN($in) AND owner='$xmbuser'");

    u2u_msg($lang['textmovesucc'], $full_url.'u2u.php?folder='.recodeOut($folder));
}

function u2u_markUnread($u2uid, $folder, $type) {
    global $db, $self, $lang, $u2uheader, $u2ufooter, $oToken, $xmbuser, $full_url;

    $u2uid = (int) $u2uid;

    if (!($u2uid > 0)) {
        error($lang['textnonechosen'], false, $u2uheader, $u2ufooter, $full_url."u2u.php", true, false, false);
        return;
    }

    if (empty($folder)) {
        error($lang['textnofolder'], false, $u2uheader, $u2ufooter, $full_url."u2u.php?action=view&amp;u2uid=$u2uid", true, false, false);
        return;
    }

    if ($type == 'outgoing') {
        error($lang['textnomur'], false, $u2uheader, $u2ufooter, $full_url.'u2u.php?folder='.recodeOut($folder), true, false, false);
    }

    $db->query("UPDATE ".X_PREFIX."u2u SET readstatus='no' WHERE u2uid=$u2uid AND owner='$xmbuser'");

    u2u_msg($lang['textmarkedunread'], $full_url.'u2u.php?folder='.recodeOut($folder));
}

function u2u_mod_markUnread($folder, $u2u_select) {
    global $db, $lang, $u2uheader, $u2ufooter, $self, $oToken, $xmbuser, $full_url;

    if (empty($folder)) {
        error($lang['textnofolder'], false, $u2uheader, $u2ufooter, $full_url."u2u.php?action=view&amp;u2uid=$u2uid", true, false, false);
        return;
    }

    if (empty($u2u_select)) {
        error($lang['textnonechosen'], false, $u2uheader, $u2ufooter, $full_url.'u2u.php?folder='.recodeOut($folder), true, false, false);
        return;
    }

    $in = '';
    foreach($u2u_select as $value) {
        $value = (int) $value;
        if ($value > 0) {
            if ($GLOBALS['type'.$value] != 'outgoing') {
                $value = intval($value);
                $in .= (empty($in)) ? "$value" : ",$value";
            }
        }
    }

    if (empty($in)) {
        error($lang['textnonechosen'], false, $u2uheader, $u2ufooter, $full_url.'u2u.php?folder='.recodeOut($folder), true, false, false);
    }

    $db->query("UPDATE ".X_PREFIX."u2u SET readstatus='no' WHERE u2uid IN($in) AND owner='$xmbuser'");

    u2u_msg($lang['textmarkedunread'], $full_url.'u2u.php?folder='.recodeOut($folder));
}

function u2u_folderSubmit($u2ufolders, $folders) {
    global $db, $lang, $self, $farray, $oToken, $xmbuser, $full_url;

    $error = '';

    //Trim all folder names, remove all duplicates, CI due to absence of explicit column collation.
    $newfolders = explode(',', $u2ufolders);
    $testarray = array();
    foreach($newfolders as $key => $value) {
        $newfolders[$key] = trim($value);
        if (empty($newfolders[$key]) Or in_array(strtolower($newfolders[$key]), $testarray) Or in_array(strtolower($newfolders[$key]), array('inbox', 'outbox', 'drafts', 'trash'))) {
            unset($newfolders[$key]);
        } else if (strlen($newfolders[$key]) > U2U_FOLDER_COL_SIZE) {
            $newfolders[$key] = substr($newfolders[$key], 0, U2U_FOLDER_COL_SIZE);
            $testarray[] = strtolower($newfolders[$key]);
        } else {
            $testarray[] = strtolower($newfolders[$key]);
        }
    }

    //Prevent deleting non-empty custom folders
    foreach($folders as $value) {
        if (isset($farray[$value]) && $farray[$value] != 0 && !in_array($value, $newfolders) && !in_array($value, array('Inbox', 'Outbox', 'Drafts', 'Trash'))) {
            $newfolders[] = $value;
            $error .= (empty($error)) ? '<br />'.$lang['foldersupdateerror'].' '.$value : ', '.$value;
        }
    }

    $u2ufolders = implode(', ', $newfolders);
    $db->escape_fast($u2ufolders);
    $db->query("UPDATE ".X_PREFIX."members SET u2ufolders='$u2ufolders' WHERE username='$xmbuser'");

    u2u_msg($lang['foldersupdate'].$error, $full_url.'u2u.php?folder=Inbox');
}

function u2u_ignore() {
    global $self, $lang, $db, $oToken, $xmbuser, $full_url;
    global $altbg1, $altbg2, $bordercolor, $THEME, $tablespace, $tablewidth, $cattext, $thewidth;

    $leftpane = '';
    if (onSubmit('ignoresubmit')) {
        $ignorelist = postedVar('ignorelist');
        $self['ignoreu2u'] = $ignorelist;
        $db->query("UPDATE ".X_PREFIX."members SET ignoreu2u='" . $self['ignoreu2u'] . "' WHERE username='$xmbuser'");
        u2u_msg($lang['ignoreupdate'], $full_url.'u2u.php?action=ignore');
    } else {
        eval('$leftpane = "'.template('u2u_ignore').'";');
    }

    return $leftpane;
}

function u2u_display($folder, $folders) {
    global $db, $self, $lang, $xmbuser, $onlinetime;
    global $altbg1, $altbg2, $bordercolor, $THEME, $tablespace, $tablewidth, $cattext, $thewidth;
    global $addtime, $timeoffset, $dateformat, $timecode, $oToken;

    $u2usin = '';
    $u2usout = '';
    $u2usdraft = '';
    $leftpane = '';
    $folderrecode = recodeOut($folder);
    $db->escape_fast($folder);

    if (empty($folder)) {
        $folder = "Inbox";
    }

    switch($folder) {
    case 'Inbox':
        $query = $db->query("SELECT u.u2uid, u.msgto, u.msgfrom, u.type, u.folder, u.subject, u.dateline, u.readstatus, m.username, m.invisible, m.lastvisit FROM ".X_PREFIX."u2u u LEFT JOIN ".X_PREFIX."members m ON u.msgfrom=m.username WHERE u.folder='$folder' AND u.owner='$xmbuser' ORDER BY dateline DESC");
        break;
    case 'Outbox':
    case 'Drafts':
        $query = $db->query("SELECT u.u2uid, u.msgto, u.msgfrom, u.type, u.folder, u.subject, u.dateline, u.readstatus, m.username, m.invisible, m.lastvisit FROM ".X_PREFIX."u2u u LEFT JOIN ".X_PREFIX."members m ON u.msgto=m.username WHERE u.folder='$folder' AND u.owner='$xmbuser' ORDER BY dateline DESC");
        break;
    default:
        $query = $db->query(
            "SELECT u.u2uid, u.msgto, u.msgfrom, u.type, u.folder, u.subject, u.dateline, u.readstatus, m.username, m.invisible, m.lastvisit FROM ".X_PREFIX."u2u u LEFT JOIN ".X_PREFIX."members m ON u.msgfrom=m.username WHERE u.folder='$folder' AND u.owner='$xmbuser' AND u.type='incoming' "
          . "UNION ALL "
          . "SELECT u.u2uid, u.msgto, u.msgfrom, u.type, u.folder, u.subject, u.dateline, u.readstatus, m.username, m.invisible, m.lastvisit FROM ".X_PREFIX."u2u u LEFT JOIN ".X_PREFIX."members m ON u.msgto=m.username WHERE u.folder='$folder' AND u.owner='$xmbuser' AND u.type IN ('outgoing','draft') "
          . "ORDER BY dateline DESC"
        );
        break;
    }

    while($u2u = $db->fetch_array($query)) {
        if ($u2u['readstatus'] == 'yes') {
            $u2ureadstatus = $lang['textread'];
        } else {
            $u2ureadstatus = '<strong>'.$lang['textunread'].'</strong>';
        }

        if (empty($u2u['subject'])) {
            $u2u['subject'] = '&laquo;'.$lang['textnosub'].'&raquo;';
        }

        $u2usubject = rawHTMLsubject(stripslashes($u2u['subject']));  //message and subject were historically double-slashed

        if ($u2u['type'] == 'incoming' or $u2u['type'] == 'outgoing') {

            if ($onlinetime - (int)$u2u['lastvisit'] <= X_ONLINE_TIMER) {
                if ($u2u['invisible'] == 1) {
                    if (!X_ADMIN) {
                        $online = $lang['textoffline'];
                    } else {
                        $online = $lang['hidden'];
                    }
                } else {
                    $online = $lang['textonline'];
                }
            } else {
                $online = $lang['textoffline'];
            }

            if ($u2u['type'] == 'incoming') {
                $u2uname = $u2u['msgfrom'];
            } else {
                $u2uname = $u2u['msgto'];
            }

            $u2usent = '<a href="member.php?action=viewpro&amp;member='.recodeOut($u2uname).'"target="_blank">'.$u2uname.'</a> ('.$online.')';
        } else if ($u2u['type'] == 'draft') {
            $u2usent = $lang['textu2unotsent'];
        }

        $adjTime = ($timeoffset * 3600) + ($addtime * 3600);
        $u2udate = gmdate($dateformat, $u2u['dateline'] + $adjTime);
        $u2utime = gmdate($timecode, $u2u['dateline'] + $adjTime);
        $u2udateline = "$u2udate $lang[textat] $u2utime";
        switch($u2u['type']) {
            case 'outgoing':
                eval('$u2usout .= "'.template('u2u_row').'";');
                break;
            case 'draft':
                eval('$u2usdraft .= "'.template('u2u_row').'";');
                break;
            case 'incoming':
            default:
                eval('$u2usin .= "'.template('u2u_row').'";');
                break;
        }
    }
    $db->free_result($query);

    if (empty($u2usin)) {
        eval('$u2usin = "'.template('u2u_row_none').'";');
    }

    if (empty($u2usout)) {
        eval('$u2usout = "'.template('u2u_row_none').'";');
    }

    if (empty($u2usdraft)) {
        eval('$u2usdraft = "'.template('u2u_row_none').'";');
    }

    switch($folder) {
        case 'Outbox':
            eval('$u2ulist = "'.template('u2u_outbox').'";');
            break;
        case 'Drafts':
            eval('$u2ulist = "'.template('u2u_drafts').'";');
            break;
        case 'Inbox':
            eval('$u2ulist = "'.template('u2u_inbox').'";');
            break;
        default:
            eval('$u2ulist = "'.template('u2u_inbox').'<br />'.template('u2u_outbox').'<br />'.template('u2u_drafts').'";');
            break;
    }

    $mtofolder = array();
    $mtofolder[] = '<select name="tofolder">';
    $mtofolder[] = '<option value="">'.$lang['textpickfolder'].'</option>';
    foreach($folders as $key => $value) {
        if (is_numeric($key)) {
            $key = $value;
        }
        $mtofolder[] = '<option value="'.$key.'">'.$value.'</option>';
    }
    $mtofolder[] = '</select>';
    $mtofolder = implode("\n", $mtofolder);

    eval('$leftpane = "'.template('u2u_main').'";');
    return $leftpane;
}

function u2u_folderList() {
    global $db, $self, $lang, $altbg1, $oToken, $xmbuser;
    global $folder, $folderlist, $folders, $farray; // <--- these are modified in here

    $u2ucount = 0;
    $folders = (empty($self['u2ufolders'])) ? array() : explode(",", $self['u2ufolders']);
    foreach($folders as $key => $value) {
        $folders[$key] = trim($value);
    }
    sort($folders);
    $folders = array_merge(array('Inbox' => $lang['textu2uinbox'], 'Outbox' => $lang['textu2uoutbox']), $folders, array('Drafts' => $lang['textu2udrafts'], 'Trash' => $lang['textu2utrash']));

    $query = $db->query("SELECT folder, count(u2uid) as count FROM ".X_PREFIX."u2u WHERE owner='$xmbuser' GROUP BY folder ORDER BY folder ASC");
    $flist = array();
    while($flist = $db->fetch_array($query)) {
        $farray[$flist['folder']] = $flist['count'];
        $u2ucount += $flist['count'];
    }
    $db->free_result($query);

    $emptytrash = $folderlist = '';
    foreach($folders as $link => $value) {
        if (is_numeric($link)) {
            $link = $value;
        }

        if ($link == $folder) {
            $value = '<strong>'.$value.'</strong>';
        }

        $count = (empty($farray[$link])) ? 0 : $farray[$link];
        if ($link == 'Trash') {
            if ($count != 0) {
                $emptytrash = ' (<a href="u2u.php?action=emptytrash">'.$lang['textemptytrash'].'</a>)';
            }
        }
        $link = recodeOut($link);
        eval('$folderlist .= "'.template('u2u_folderlink').'";');
    }

    return $u2ucount;
}
?>
