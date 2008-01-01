<?php
/**
* $Id: u2u.inc.php,v 1.1.2.14 2006/02/27 11:46:33 Tularis Exp $
*/

/**
* XMB 1.9.2
* © 2001 - 2005 Aventure Media & The XMB Development Team
* http://www.aventure-media.co.uk
* http://www.xmbforum.com
*
*
*    This program is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program; if not, write to the Free Software
*    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
*/

/**
* u2u_msg()
*
* @param  $msg - message to display to user
* @param  $redirect - where to go afterwards
* @return No return value, this functions stops page execution
*/
function u2u_msg( $msg, $redirect ) {
    global $u2uheader, $u2ufooter, $tablewidth, $bordercolor, $tablespace, $borderwidth, $altbg1;

    if ( !empty($redirect) ) {
        redirect($redirect);
    }
    eval('echo stripslashes("' . template('u2u_msg') . '");');
    exit;
}

/**
 * db_u2u_insert()
 *
 * @param $to       Message to
 * @param $from     Message from
 * @param $type     One of 'incoming', 'outgoing', 'draft'
 * @param $owner    Usually the same as $from
 * @param $folder   One of 'Inbox', 'Outbox', 'Drafts'
 * @param $subject  The subject
 * @param $message  the message body
 * @param $isRead   isRead
 * @param $isSent   isSent
 * @return No return variables
 **/
function db_u2u_insert($to, $from, $type, $owner, $folder, $subject, $message, $isRead, $isSent) {
    global $db, $table_u2u, $onlinetime;

    $subject = checkInput(censor(addslashes($subject)));
    $message = checkInput(censor(addslashes($message)));

    $db->query( "INSERT INTO $table_u2u ( msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus ) VALUES ('".addslashes($to)."', '".addslashes($from)."', '$type', '".addslashes($owner)."', '$folder', '$subject', '$message', '$onlinetime', '$isRead', '$isSent')" );
}

/**
 * u2u_send_multi_recp()
 *
 * @param $msgto Who is the u2u set going to?
 * @param $subject u2u subject
 * @param $message u2u message
 * @return errors (if any)
 **/
function u2u_send_multi_recp($msgto, $subject, $message, $u2uid=0) {
    global $db, $table_members, $self, $SETTINGS, $lang, $onlinetime, $bbname, $adminemail, $table_u2u;

    $errors = '';
    $recipients = array_unique( array_map( 'trim', explode( ",", $msgto ) ) );

    foreach ( $recipients as $value ) {
        $query = $db->query( "SELECT username, email, lastvisit, ignoreu2u, emailonu2u, status FROM $table_members WHERE username='" . trim( $value ) . "'" );
        $rcpt = $db->fetch_array( $query );
        if ( $rcpt ) {
            $ilist = array_map( 'trim', explode( ",", $rcpt['ignoreu2u'] ) );
            if ( !in_array( $self['username'], $ilist ) || X_ADMIN ) {
                $username = $rcpt['username'];
                db_u2u_insert( $username, $self['username'], 'incoming', $username, 'Inbox', $subject, $message, 'no', 'yes' );

                if ( $self['saveogu2u'] == 'yes' ) {
                    db_u2u_insert( $username, $self['username'], 'outgoing', $self['username'], 'Outbox', $subject, $message, 'no', 'yes' );
                }
                //u2u to trash ;)
                if($_GET['del'] == "yes" && $u2uid > 0){
                   $db->query( "UPDATE $table_u2u SET folder='Trash' WHERE u2uid='$u2uid' AND owner='$self[username]'" );
                }

                if ( $rcpt['emailonu2u'] == 'yes' && $rcpt['status'] != 'Banned') {
                    $lastvisitcheck = $onlinetime - 600;
                    if ( $lastvisitcheck > $rcpt['lastvisit'] ) {
                        $u2uurl = $SETTINGS['boardurl'] . 'u2u.php';
                        altMail( $rcpt['email'], "$lang[textnewu2uemail]", "$self[username] $lang[textnewu2ubody] \n$u2uurl", "From: $bbname <$adminemail>" );
                    }
                }
                $errors .= "<br />" . $value . " - " . $lang['imsentmsg'];
            } else {
                $errors .= "<br />" . $value . " - " . $lang['u2ublocked'];
            }
        } else {
            $errors .= "<br />" . $value . " - " . $lang['badrcpt'];
        }
        $db->free_result( $query );
    } // foreach recipient
    return $errors;
}

/**
 * u2u_send_recp()
 *
 * @param $msgto Who is the u2u set going to?
 * @param $subject u2u subject
 * @param $message u2u message
 * @return errors (if any)
 **/
function u2u_send_recp($msgto, $subject, $message, $u2uid=0) {
    global $db, $table_members, $self, $SETTINGS, $lang, $onlinetime, $bbname, $adminemail, $table_u2u, $del;

    $errors = '';

    $query = $db->query( "SELECT username, email, lastvisit, ignoreu2u, emailonu2u, status FROM $table_members WHERE username='" . trim( $msgto ) . "'" );
    if ( $rcpt = $db->fetch_array( $query ) ) {
        $ilist = array_map( 'trim', explode( ",", $rcpt['ignoreu2u'] ) );
        if ( !in_array( $self['username'], $ilist ) || X_ADMIN ) {
            $username = $rcpt['username'];
            db_u2u_insert( $username, $self['username'], 'incoming', $username, 'Inbox', $subject, $message, 'no', 'yes' );
            if ( $self['saveogu2u'] == 'yes' ) {
                db_u2u_insert( $username, $self['username'], 'outgoing', $self['username'], 'Outbox', $subject, $message, 'no', 'yes' );
            }
            //u2u to trash ;)
            if($del == "yes" && $u2uid > 0){
                   $db->query( "UPDATE $table_u2u SET folder='Trash' WHERE u2uid='$u2uid' AND owner='$self[username]'" );
            }

            if ( $rcpt['emailonu2u'] == 'yes' && $rcpt['status'] != 'Banned') {
                $lastvisitcheck = $onlinetime - 600;
                if ( $lastvisitcheck > $rcpt['lastvisit'] ) {
                    $u2uurl = $SETTINGS['boardurl'] . 'u2u.php';
                    altMail( $rcpt['email'], "$lang[textnewu2uemail]", "$self[username] $lang[textnewu2ubody] \n$u2uurl", "From: $bbname <$adminemail>" );
                }
            }
        } else {
            $errors = "<br />" . $lang['u2ublocked'];
        }
    } else {
        $errors = "<br />" . $lang['badrcpt'];
    }
    $db->free_result( $query );
    return $errors;
}

/**
 * u2u_send()
 *
 * @param $u2uid the proposed u2uID from the address bar
 * @param $subject u2u subject
 * @param $message u2u message body
 * @param $u2upreview (out by ref) designed to take a string back from the function. Expect it to be null too
 * @return $leftpane the left hand pane view
 **/
function u2u_send($u2uid, $msgto, $subject, $message, $u2upreview) {
    global $db, $self, $lang, $username, $SETTINGS, $table_u2u, $del;
    global $u2uheader, $u2ufooter, $u2ucount, $u2uquota;
    global $altbg1, $altbg2, $bordercolor, $borderwidth, $tablespace, $cattext, $thewidth;
    global $forward, $reply;

    global $sendsubmit, $savesubmit, $previewsubmit;

    if ( $self['ban'] == 'u2u' || $self['ban'] == 'both' ) {
        error( $lang['textbanfromu2u'], false, $u2uheader, $u2ufooter, false, true, false, false );
    }

    if ( $u2ucount >= $u2uquota && $u2uquota > 0 ) {
        error( $lang['u2ureachedquota'], false, $u2uheader, $u2ufooter, false, true, false, false );
    }

    if (isset($savesubmit)) {
        if (empty($subject) || empty($message)) {
            error( $lang['u2uempty'], false, $u2uheader, $u2ufooter, false, true, false, false );
        }

        db_u2u_insert( '', '', 'draft', $self['username'], 'Drafts', $subject, $message, 'yes', 'no' );
        u2u_msg($lang['imsavedmsg'], "u2u.php?folder=Drafts");
    }

    if ( isset( $sendsubmit ) ) {
        $errors = '';
        if ( empty( $subject ) || empty( $message ) ) {
            error( $lang['u2uempty'], false, $u2uheader, $u2ufooter, false, true, false, false );
        }
		// floodcontrol!
		// $SETTINGS['floodctrl']
		if($db->result($db->query("SELECT count(u2uid) FROM $table_u2u WHERE msgfrom='$self[username]' AND dateline > ".(time()-$SETTINGS['floodctrl'])), 0) > 0) {
			error($lang['floodprotect_u2u'], false, $u2uheader, $u2ufooter, false, true, false, false );
		}

        $u2uid = $_POST['u2uid'];

        if ( strstr( $msgto, "," ) && X_STAFF) {
            $errors = u2u_send_multi_recp($msgto, $subject, $message, $u2uid);
        } else {
            $errors = u2u_send_recp($msgto, $subject, $message, $u2uid);
        }

        if ( empty( $errors ) ) {
            u2u_msg( $lang['imsentmsg'], "u2u.php" );
        } else {
            u2u_msg( substr($errors, 6) , "u2u.php" );
        }
    }

    if ( $u2uid > 0 ) {
        $query = $db->query( "SELECT subject, msgfrom, message FROM $table_u2u WHERE u2uid='$u2uid' AND owner='$self[username]'" );
        $quote = $db->fetch_array( $query );
        if ( $quote ) {
            if ( !isset( $previewsubmit ) ) { // When replying to a U2U and previewing it, don't overwrite the message & subject
                $prefixes = array( $lang['textre'], $lang['textfwd'] );
                $subject = trim( stripslashes( str_replace( $prefixes, '', $quote['subject'] ) ) );
                $message = trim( stripslashes( $quote['message'] ) );
                if ( $forward == 'yes' ) {
                    $subject = $lang['textfwd'] . ' ' . $subject;
                    $message = '[quote][i]' . $lang['origpostedby'] . ' ' . $quote['msgfrom'] . "[/i]\n" . $message . '[/quote]';
                } elseif ( $reply == 'yes' ) {
                    $subject = $lang['textre'] . ' ' . $subject;
                    $message = '[quote]' . $message . '[/quote]';
                    $username = $quote['msgfrom'];
                }
            }
        }
        $db->free_result( $query );
    }
    if ( isset( $previewsubmit ) ) {
        $u2usubject = checkOutput( censor( checkInput( stripslashes( $subject ) ) ) );
        $u2umessage = checkOutput( checkInput( stripslashes( $message ) ) );
        $u2umessage = postify( $u2umessage, "no", "", "yes", "no" );
        $username = $msgto;
        $subject = htmlspecialchars( $subject );
        $message = htmlspecialchars( $message );
        eval( "\$u2upreview = \"" . template( "u2u_send_preview" ) . "\";" );
    }

    eval( "\$leftpane = \"" . template( "u2u_send" ) . "\";" );
    return $leftpane;
}

/**
 * u2u_view()
 *
 * @param $u2uid the u2u ID to view
 * @return the left pane after completing the view
 **/
function u2u_view($u2uid, $folders) {
    global $db, $dateformat, $timecode, $timeoffset, $addtime, $lang;
    global $table_u2u, $self;
    global $altbg1, $altbg2, $bordercolor, $borderwidth, $tablespace, $cattext, $thewidth;
    global $sendoptions, $u2uheader, $u2ufooter;

    $delchecked = '';

    if ( !($u2uid > 0) ) {
        error( $lang['textnonechosen'], false, $u2uheader, $u2ufooter, "u2u.php", true, false, false );
        return;
    }

    $query = $db->query( "SELECT * FROM $table_u2u WHERE u2uid='$u2uid' AND owner='$self[username]'" );
    $u2u = $db->fetch_array( $query );
    if ( $u2u ) {
        if ( $u2u['type'] == 'incoming' ) {
            $db->query( "UPDATE $table_u2u SET readstatus='yes' WHERE u2uid=$u2u[u2uid] OR (u2uid=$u2u[u2uid]+1 AND type='outgoing' AND msgto='$self[username]')" );
        } elseif ( $u2u['type'] == 'draft' ) {
            $db->query( "UPDATE $table_u2u SET readstatus='yes' WHERE u2uid=$u2u[u2uid]" );
        }

        $adjTime = ( $timeoffset * 3600 ) + ( $addtime * 3600 );

        $u2udate = gmdate( "$dateformat", $u2u['dateline'] + $adjTime );
        $u2utime = gmdate( "$timecode", $u2u['dateline'] + $adjTime );
        $u2udateline = "$u2udate $lang[textat] $u2utime";
        $u2usubject = checkOutput( censor( $u2u['subject'] ) );
        $u2umessage = checkOutput( postify( $u2u['message'], "no", "", "yes", "no" ));
        $u2ufolder = $u2u['folder'];
        $u2ufrom = "<a href=\"member.php?action=viewpro&amp;member=" . urlencode( $u2u['msgfrom'] ) . "\" target=\"mainwindow\">" . $u2u['msgfrom'] . "</a>";
        $u2uto = ( $u2u['type'] == 'draft' ) ? $lang['textu2unotsent'] : "<a href=\"member.php?action=viewpro&amp;member=" . urlencode( $u2u['msgto'] ) . "\" target=\"mainwindow\">" . $u2u['msgto'] . "</a>";
        if ( $u2u['type'] == 'draft' ) {
            $sendoptions = "<input type=\"radio\" name=\"mod\" value=\"send\" /> $lang[textu2u]<br />";
            $delchecked = " checked=\"checked\"";
        } elseif ( $u2u['msgfrom'] != $self['username'] ) {
            $sendoptions = "<input type=\"radio\" name=\"mod\" value=\"reply\" checked=\"checked\" /> $lang[textreply]<br /><input type=\"radio\" name=\"mod\" value=\"replydel\" /> $lang[textreplytrash]<br /><input type=\"radio\" name=\"mod\" value=\"forward\" /> $lang[textforward]<br />";
        } else {
            $delchecked = " checked=\"checked\"";
        }
        $mtofolder = "<select name=\"tofolder\"><option value=\"\">$lang[textpickfolder]</option>";
        foreach ( $folders as $key => $value ) {
            if ( is_numeric( $key ) ) {
                $key = $value;
            }
            $mtofolder .= "<option value=\"$key\">$value</option>";
        }
        $mtofolder .= "</select>";
    } else {
        error( $lang['u2uadmin_noperm'], false, $u2uheader, $u2ufooter, false, true, false, false );
    }
    $db->free_result( $query );
    eval( "\$leftpane = \"" . template( "u2u_view" ) . "\";" );

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
    global $SETTINGS, $css, $db, $self, $table_u2u, $timeoffset, $lang, $u2uheader, $u2ufooter, $dateformat, $timecode, $addtime, $charset, $bbname, $logo;

    if ( !($u2uid > 0) ) {
        error( $lang['textnonechosen'], false, $u2uheader, $u2ufooter, "u2u.php", true, false, false );
        return;
    }

    $query = $db->query( "SELECT * FROM $table_u2u WHERE u2uid='$u2uid' AND owner='$self[username]'" );
    $u2u = $db->fetch_array( $query );
    $db->free_result( $query );
    if ( $u2u ) {
        smcwcache();

        $adjTime = ( $timeoffset * 3600 ) + ( $addtime * 3600 );
        $u2udate = gmdate($dateformat, $u2u['dateline'] +  $adjTime );
        $u2utime = gmdate($timecode, $u2u['dateline'] + $adjTime );
        $u2udateline = "$u2udate $lang[textat] $u2utime";
        $u2usubject = stripslashes( checkOutput( censor( $u2u['subject'] ) ) );
        $u2umessage = postify(stripslashes($u2u['message']), 'no', 'no', 'yes', 'no', 'yes', 'yes', false, "no", "yes");;
        $u2ufolder = $u2u['folder'];
        $u2ufrom = $u2u['msgfrom'];
        $u2uto = ( $u2u['type'] == 'draft' ) ? $lang['textu2unotsent'] : $u2u['msgto'];

        if ($eMail) {
            eval('$mailHeader = "'.template('email_html_header').'";');
            eval('$mailFooter = "'.template('email_html_footer').'";');

            $email = $mailHeader.$lang['textsubject'] . " " . $u2usubject . "<br />\n" . $lang['textfrom'] . " " . $u2ufrom . "<br />\n" . $lang['textto'] . " " . $u2uto . "<br />\n" . $lang['textu2ufolder'] . " " . $u2ufolder . "<br />\n" . $lang['textsent'] . " " . $u2udateline . "<br />\n<br />\n".stripslashes($u2umessage).$mailFooter;
            altMail($self['email'], $lang['textu2utoemail'] . " " . $u2usubject, $email, 'From: '.$bbname.' <'.$self['email'].">\r\n".'Content-type: text/html');
            u2u_msg( $lang['textu2utoemailsent'], "u2u.php?action=view&u2uid=$u2uid" );
        } else {
            eval( 'echo stripslashes("' . template( 'u2u_printable' ) . '");' );
            exit;
        }
    } else {
        error( $lang['u2uadmin_noperm'], false, $u2uheader, $u2ufooter, false, true, false, false );
    }
}

/**
 * u2u_delete()
 *
 * Delete a message ... either by moving it to the Trash, or Emptying the Trash folder
 *
 * @param $u2uid u2u message to move or delete
 * @param $folder u2u parent folder
 * @return Nothing.
 **/
function u2u_delete($u2uid, $folder) {
    global $db, $self, $table_u2u, $lang;

    if ( !($u2uid > 0) ) {
        error( $lang['textnonechosen'], false, $u2uheader, $u2ufooter, "u2u.php", true, false, false );
        return;
    }

    if ( $folder == "Trash" ) {
        $db->query( "DELETE FROM $table_u2u WHERE u2uid='$u2uid' AND owner='$self[username]'" );
    } else {
        $db->query( "UPDATE $table_u2u SET folder='Trash' WHERE u2uid='$u2uid' AND owner='$self[username]'" );
    }
    u2u_msg( $lang['imdeletedmsg'], "u2u.php?folder=$folder" );
}

/**
 * u2u_mod_delete()
 *
 * Delete multiple U2U's at once, again by moving them to the Trash or if in the Trash, really delete
 *
 * @param $folder the u2u's which are to die live in here
 * @param $u2u_select checkbox array sourced from the HTML form
 * @return Nothing.
 **/
function u2u_mod_delete($folder, $u2u_select) {
    global $db, $self, $table_u2u, $lang;

    $in = '';
    foreach ( $u2u_select as $value ) {
        $in .= ( empty( $in ) ) ? "$value" : ", $value";
    }

    if ( $folder == "Trash" ) {
        $db->query( "DELETE FROM $table_u2u WHERE u2uid IN($in) AND owner='$self[username]'" );
    } else {
        $db->query( "UPDATE $table_u2u SET folder='Trash' WHERE u2uid IN($in) AND owner='$self[username]'" );
    }
    u2u_msg( $lang['imdeletedmsg'], "u2u.php?folder=$folder" );
}

function u2u_move($u2uid, $tofolder) {
    global $db, $self, $table_u2u, $lang, $u2uheader, $u2ufooter, $folders, $type, $folder;

    if ( !($u2uid > 0) ) {
        error( $lang['textnonechosen'], false, $u2uheader, $u2ufooter, "u2u.php", true, false, false );
        return;
    }

    if ( empty( $tofolder ) ) {
        error( $lang['textnofolder'], false, $u2uheader, $u2ufooter, "u2u.php?action=view&amp;u2uid=$u2uid", true, false, false );
    } else {
        if ( !( in_array( $tofolder, $folders )
                || $tofolder == 'Inbox' || $tofolder == 'Outbox' || $tofolder == 'Drafts' )
                || ( $tofolder == 'Inbox' && ( $type == 'draft' || $type == 'outgoing' ) )
                || ( $tofolder == 'Outbox' && ( $type == 'incoming' || $type == 'draft' ) )
                || ( $tofolder == 'Drafts' && ( $type == 'incoming' || $type == 'outgoing' ) ) ) {
            error( $lang['textcantmove'], false, $u2uheader, $u2ufooter, "u2u.php?action=view&amp;u2uid=$u2uid", true, false, false );
        }

        $db->query( "UPDATE $table_u2u SET folder='$tofolder' WHERE u2uid='$u2uid' AND owner='$self[username]'" );
        u2u_msg( $lang['textmovesucc'], "u2u.php?folder=$folder" );
    }
}

function u2u_mod_move($tofolder, $u2u_select) {
    global $db, $self, $table_u2u, $lang, $u2uheader, $u2ufooter, $folders;

    $in = '';
    foreach ( $u2u_select as $value ) {
        $type = $GLOBALS['type'.$value];
        if ( ( in_array( $tofolder, $folders ) || $tofolder == 'Inbox' || $tofolder == 'Outbox' || $tofolder == 'Drafts' )
                && !( $tofolder == 'Inbox' && ( $type == 'draft' || $type == 'outgoing' ) )
                && !( $tofolder == 'Outbox' && ( $type == 'incoming' || $type == 'draft' ) )
                && !( $tofolder == 'Drafts' && ( $type == 'incoming' || $type == 'outgoing' ) ) ) {
            $in .= ( empty( $in ) ) ? "$value" : ",$value";
        }
    }

    if ( empty( $in ) ) {
        error( $lang['textcantmove'], false, $u2uheader, $u2ufooter, "u2u.php?folder=$folder", true, false, false );
        return;
    }

    $db->query( "UPDATE $table_u2u SET folder='$tofolder' WHERE u2uid IN($in) AND owner='$self[username]'" );
    u2u_msg( $lang['textmovesucc'], "u2u.php?folder=$folder" );
}

/**
 * u2u_markUnread()
 *
 * Mark one U2U as unread
 *
 * @param $u2uid The u2uid to mark unread
 * @param $folder Folder to re-direct to if things don't work out
 * @param $type ingoing, outgoing, draft, etc
 * @return no return, function exits page
 **/
function u2u_markUnread($u2uid, $folder, $type) {
    global $db, $self, $table_u2u, $lang, $u2uheader, $u2ufooter;

    if ( !($u2uid > 0) ) {
        error( $lang['textnonechosen'], false, $u2uheader, $u2ufooter, "u2u.php", true, false, false );
        return;
    }

    if ( empty( $folder ) ) {
        error( $lang['textnofolder'], false, $u2uheader, $u2ufooter, "u2u.php?action=view&amp;u2uid=$u2uid", true, false, false );
        return;
    }

    if ( $type == 'outgoing' ) {
                    error( $lang['textnomur'], false, $u2uheader, $u2ufooter, "u2u.php?folder=$folder", true, false, false );
    }
    $db->query( "UPDATE $table_u2u SET readstatus='no' WHERE u2uid=$u2uid AND owner='$self[username]'" );
    u2u_msg( $lang['textmarkedunread'], "u2u.php?folder=$folder" );
}

/**
 * u2u_mod_markUnread()
 *
 * @param $folder Folder to re-direct to if things don't work out
 * @param $u2uselect the HTML select list built up by the form
 * @return no return, function exits page
 **/
function u2u_mod_markUnread($folder, $u2u_select) {
    global $db, $table_u2u, $lang, $u2uheader, $u2ufooter, $self;

    if ( empty( $folder ) ) {
        error( $lang['textnofolder'], false, $u2uheader, $u2ufooter, "u2u.php?action=view&amp;u2uid=$u2uid", true, false, false );
        return;
    }

    if ( empty( $u2u_select ) ) {
        error( $lang['textnonechosen'], false, $u2uheader, $u2ufooter, "u2u.php?folder=$folder", true, false, false );
        return;
    }

    $in = '';
    foreach ( $u2u_select as $value ) {
        if ( $GLOBALS['type'.$value] != 'outgoing' ) {
            $in .= ( empty( $in ) ) ? "$value" : ",$value";
        }
    }
    if ( empty( $in ) ) {
        error( $lang['textnonechosen'], false, $u2uheader, $u2ufooter, "u2u.php?folder=$folder", true, false, false );
    }
    $db->query( "UPDATE $table_u2u SET readstatus='no' WHERE u2uid IN($in) AND owner='$self[username]'" );
    u2u_msg( $lang['textmarkedunread'], "u2u.php?folder=$folder" );
}

function u2u_folderSubmit($u2ufolders, $folders) {
    global $db, $lang, $self, $table_members, $farray;

    $error = '';

    $newfolders = explode( ",", $u2ufolders );

    foreach ( $newfolders as $key => $value ) {
        $newfolders[$key] = trim( $value );
        if ( empty( $newfolders[$key] ) ) {
            unset( $newfolders[$key] );
        }
    }

    foreach ( $folders as $value ) {
        if ( $farray[$value] != 0
                && !in_array( $value, $newfolders )
                && !in_array( $value, array( 'Inbox', 'Outbox', 'Drafts', 'Trash' ) ) ) {
            $newfolders[] = $value;
            $error .= ( empty( $error ) ) ? '<br />'.$lang['foldersupdateerror'].' '.$value : ', '.$value;
        }
    }

    $u2ufolders = checkInput( implode( ", ", $newfolders ) );
    $db->query( "UPDATE $table_members SET u2ufolders='$u2ufolders' WHERE username='$self[username]'" );
    u2u_msg( $lang['foldersupdate'].$error, "u2u.php?folder=Inbox" );
}


/**
 * u2u_ignore()
 *
 * This function displays the ignore list from the form submission variables, and
 * attempts to update the ignore list if the Ignore Submit button has been pressed
 *
 * @return the left pane to render if the function returns (which it doesn't for changing the ignore list)
 **/
function u2u_ignore() {
    global $ignorelist, $ignoresubmit, $self, $lang, $db, $table_members;
    global $altbg1, $altbg2, $bordercolor, $borderwidth, $tablespace, $tablewidth, $cattext, $thewidth;

    $leftpane = '';

    if ( isset( $ignoresubmit ) && isset($ignorelist) ) {
        $self['ignoreu2u'] = htmlspecialchars( checkInput( $ignorelist ) );
        $db->query( "UPDATE $table_members SET ignoreu2u='" . $self['ignoreu2u'] . "' WHERE username='$self[username]'" );
        u2u_msg( $lang['ignoreupdate'], "u2u.php?action=ignore" );
    } else {
        $self['ignoreu2u'] = checkOutput( $self['ignoreu2u'] );
        eval("\$leftpane = \"" . template('u2u_ignore') . "\";" );
    }

    return $leftpane;
}

/**
 * u2u_display()
 *
 * This function is teh default action for u2u.php. It displays the u2u look n feel
 *
 * @param $folder display a sub-folder instead of the Inbox folder
 * @return nothing.
 **/
function u2u_display($folder, $folders) {
    global $db, $self, $table_u2u, $table_whosonline, $lang;
    global $altbg1, $altbg2, $bordercolor, $borderwidth, $tablespace, $tablewidth, $cattext, $thewidth;
    global $addtime, $timeoffset, $dateformat, $timecode;


    $u2usin = '';
    $u2usout = '';
    $u2usdraft = '';

    if ( empty( $folder ) ) {
        $folder = "Inbox";
    }

    $query = $db->query( "SELECT u.*, w.username, w.invisible FROM $table_u2u u LEFT JOIN $table_whosonline w ON (u.msgto=w.username OR u.msgfrom=w.username) AND w.username!='$self[username]' WHERE u.folder='$folder' AND u.owner='$self[username]' ORDER BY dateline DESC" );
    while ( $u2u = $db->fetch_array( $query ) ) {
        if ( $u2u['readstatus'] == 'yes' ) {
            $u2ureadstatus = $lang['textread'];
        } else {
            $u2ureadstatus = "<b>" . $lang['textunread'] . "</b>";
        }
        if ( empty( $u2u['subject'] ) ) {
            $u2u['subject'] = "&laquo;" . $lang['textnosub'] . "&raquo;";
        }
        $u2usubject = checkOutput( censor( $u2u['subject'] ) );
        if ( $u2u['type'] == 'incoming' ) {
            if ( $u2u['msgfrom'] == $u2u['username'] || $u2u['msgfrom'] == $self['username'] ) {
                if ( $u2u['invisible'] == 1 ) {
                    if ( X_ADMIN ) {
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
            $u2usent = "<a href=\"member.php?action=viewpro&amp;member=" . urlencode( $u2u['msgfrom'] ) . "\" target=\"_blank\">" . $u2u['msgfrom'] . "</a> ($online)";
        } elseif ( $u2u['type'] == 'outgoing' ) {
            if ( $u2u['msgto'] == $u2u['username'] || $u2u['msgto'] == $self['username'] ) {
                if ( $u2u['invisible'] == 1 ) {
                    if ( X_ADMIN ) {
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
            $u2usent = "<a href=\"member.php?action=viewpro&amp;member=" . urlencode( $u2u['msgto'] ) . "\" target=\"_blank\">" . $u2u['msgto'] . "</a> ($online)";
        } elseif ( $u2u['type'] == 'draft' ) {
            $u2usent = $lang['textu2unotsent'];
        }
        $adjTime = ( $timeoffset * 3600 ) + ( $addtime * 3600 );
        $u2udate = gmdate( "$dateformat", $u2u['dateline'] + $adjTime );
        $u2utime = gmdate( "$timecode", $u2u['dateline'] + $adjTime );
        $u2udateline = "$u2udate $lang[textat] $u2utime";
        switch ( $u2u['type'] ) {
            case 'outgoing':
                $u2us = 'u2usout';
                break;

            case 'draft':
                $u2us = 'u2usdraft';
                break;

            case 'incoming':

            default:
                $u2us = 'u2usin';
                break;
        }
        eval( "\$$u2us .= \"" . template( 'u2u_row' ) . "\";" );
    }
    $db->free_result( $query );
    if ( empty( $u2usin ) ) {
        eval( "\$u2usin = \"" . template( 'u2u_row_none' ) . "\";" );
    }
    if ( empty( $u2usout ) ) {
        eval( "\$u2usout = \"" . template( 'u2u_row_none' ) . "\";" );
    }
    if ( empty( $u2usdraft ) ) {
        eval( "\$u2usdraft = \"" . template( 'u2u_row_none' ) . "\";" );
    }

    switch ( $folder ) {
        case 'Outbox':
            eval( "\$u2ulist = \"" . template( 'u2u_outbox' ) . "\";" );
        break;

        case 'Drafts':
            eval( "\$u2ulist = \"" . template( 'u2u_drafts' ) . "\";" );
        break;

        case 'Inbox':
            eval( "\$u2ulist = \"" . template( 'u2u_inbox' ) . "\";" );
        break;

        default:
            eval( "\$u2ulist = \"" . template( 'u2u_inbox' ) . "<br />" . template( 'u2u_outbox' ) . "<br />" . template( 'u2u_drafts' ) . "\";" );
        break;
    }

    $mtofolder = "<select name=\"tofolder\"><option value=\"\">$lang[textpickfolder]</option>";
    foreach ( $folders as $key => $value ) {
        if ( is_numeric( $key ) ) {
            $key = $value;
        }
        $mtofolder .= "<option value=\"$key\">$value</option>";
    }
    $mtofolder .= "</select>";
    eval( "\$leftpane = \"" . template( 'u2u_main' ) . "\";" );
    return $leftpane;
}

/**
 * u2u_folderList()
 *
 *  Generate a folder list from the database and leave the details in
 *  various globals
 *
 * @return the number of u2u's in total the user has
 **/
function u2u_folderList() {
    global $db, $self, $lang, $table_u2u, $altbg1;
    global $folder, $folderlist, $folders, $farray; // <--- these are modified in here

    $u2ucount = 0;

    $folders = ( empty( $self['u2ufolders'] ) ) ? array() : explode( ",", $self['u2ufolders'] );

    foreach ( $folders as $key => $value ) {
        $folders[$key] = trim( $value );
    }
    sort( $folders );
    $folders = array_merge( array(  'Inbox' => $lang['textu2uinbox'],
                                    'Outbox' => $lang['textu2uoutbox'] ),
                            $folders,
                            array(  'Drafts' => $lang['textu2udrafts'],
                                    'Trash' => $lang['textu2utrash'] ) );

    $query = $db->query( "SELECT folder, count(u2uid) as count FROM $table_u2u WHERE owner='$self[username]' GROUP BY folder ORDER BY folder ASC" );
    $flist = array();
    while ( $flist = $db->fetch_array( $query ) ) {
        $farray[$flist['folder']] = $flist['count'];
        $u2ucount += $flist['count'];
    }
    $db->free_result( $query );
    $emptytrash = '';
    $folderlist = '';
    foreach ( $folders as $link => $value ) {
        echo ("<!-- $link = $value -->");

        if ( is_numeric( $link ) ) {
            $link = $value;
        }
        if ( $link == $folder ) {
            $value = '<b>' . $value . '</b>';
        }
        $count = ( empty( $farray[$link] ) ) ? 0 : $farray[$link];
        if ( $link == 'Trash' ) {
            if ( $count != 0 ) {
                $emptytrash = " (<a href=\"u2u.php?action=emptytrash\">" . $lang['textemptytrash'] . "</a>)";
            }
        }
        eval( "\$folderlist .= \"" . template( 'u2u_folderlink' ) . "\";" );
    }
    return $u2ucount;
}
?>
