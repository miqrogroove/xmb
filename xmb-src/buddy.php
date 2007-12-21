<?php
/* $Id: buddy.php,v 1.7 2006/02/01 15:42:24 Tularis Exp $ */
/*
    XMB 1.10
    © 2001 - 2006 Aventure Media & The XMB Development Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// get Global stuff
require 'header.php';

// Pre-load templates
loadtemplates('buddy_u2u', 'buddy_u2u_inv', 'buddy_u2u_off', 'buddy_u2u_on', 'buddylist', 'buddylist_buddy_offline', 'buddylist_buddy_online', 'buddylist_edit', 'buddylist_edit_buddy', 'buddylist_message');
eval('$css = "'.template('css').'";');

if (X_GUEST) {
    error($lang['u2unotloggedin']);
}

/**
* blistmsg() - buddy list message
*
* This function displays the contents of $message, which should be a list of buddies.
*
* @param  $message    message body
* @param  $redirect   (optional) URL to redirect to
* @param  $exit       (optional) if true, the script stops
* @return no return value, and potentially the script will not return
*/
function blistmsg($message, $redirect='', $exit=false) {
    global $bordercolor, $tablewidth, $borderwidth, $tablespace, $altbg1, $css, $bbname, $lang;
    global $charset, $text, $redirectjs;

    if ( $redirect != '' ) {
        redirect($redirect, 2);
    }
    eval('echo stripslashes("'.template('buddylist_message').'");');
    if ( $exit ) {
        exit();
    }
}

/**
* buddy_add() - add a buddy
*
* Add one or more buddies to your buddy list.
*
* @param  $buddys    either one name as a string, or an array of buddies as strings
* @return no return value
*/
function buddy_add($buddys)
{
    global $db, $table_buddys, $table_members, $lang, $xmbuser;

    if (!is_array($buddys)) {
        $buddys = array($buddys);
    }

    if (count($buddys) > 10) {
        $buddys = array_slice($buddys, 0, 10);
    }

    foreach ($buddys as $key=>$buddy) {
        if (empty($buddy) || (strlen(trim($buddy)) == 0) ) {
            blistmsg($lang['nobuddyselected'], '', true);
        } else {
            $buddy = safeAddslashes(checkInput($buddy));

            if ( $buddy == $xmbuser ) {
                blistmsg($lang['buddywarnaddself']);
            }

            $q = $db->query("SELECT count(username) FROM $table_buddys WHERE username='$xmbuser' AND buddyname='$buddy'");
            if ($db->result($q, 0) > 0) {
                blistmsg($buddy.' '.$lang['buddyalreadyonlist']);
            } else {
                // not yet on the list.
                $q = $db->query("SELECT count(username) FROM $table_members WHERE username='$buddy'");
                if ( $db->result($q, 0) < 1) {
                    blistmsg($lang['nomember']);
                } else {
                    $db->query("INSERT INTO $table_buddys (buddyname, username) VALUES ('$buddy', '$xmbuser')");
                    blistmsg($buddy.' '.$lang['buddyaddedmsg'], 'buddy.php');
                }
            }
        }
    } // foreach
} // add_buddy


/**
* buddy_edit() - Display the "Edit buddy" pop-up
*
* Display the Edit buddy list
*
* @return no return value, but will display a pop-up
*/
function buddy_edit()
{
    global $db, $table_buddys, $lang, $xmbuser;
    global $charset, $css, $bbname, $text, $bordercolor, $borderwidth, $tablespace, $tablewidth, $cattext, $altbg1, $altbg2;

    $buddys = array();

    $q = $db->query("SELECT buddyname FROM $table_buddys WHERE username='$xmbuser'") or die($db->error());
    while ($buddy = $db->fetch_array($q)) {
        eval("\$buddys[] = \"".template('buddylist_edit_buddy')."\";");
    }
    if (count($buddys) > 0) {
        $buddys = implode("\n", $buddys);
    } else {
        unset($buddys);
        $buddys = "";
    }
    eval('echo stripslashes("'.template('buddylist_edit').'");');
}

/**
* buddy_delete() - delete one or more buddies
*
* This function deletes buddies from the user's buddy entries
*
* @param    $delete    one string or an array of strings containing buddies to be deleted
* @return    no return value, but will display a status report
*/
function buddy_delete($delete)
{
    global $db, $table_buddys, $lang, $xmbuser;
    global $charset, $css, $bbname, $text, $bordercolor, $borderwidth, $tablespace, $tablewidth, $cattext, $altbg1, $altbg2;

    foreach ($delete as $key=>$buddy) {
        $buddy = safeAddslashes(checkInput($buddy));
        $db->query("DELETE FROM $table_buddys WHERE buddyname='$buddy' AND username='$xmbuser'");
    }

    blistmsg($lang['buddylistupdated'], 'buddy.php');
}

/**
* buddy_addu2u() - Display a list of buddies with their online status
*
* @param    none, but takes many globals
* @return    no return value, but will display a status report or a list of buddies and their online status
*/
function buddy_addu2u()
{
    global $db, $table_buddys, $table_whosonline, $lang, $xmbuser;
    global $charset, $css, $bbname, $text, $bordercolor, $borderwidth, $tablespace, $tablewidth, $cattext, $altbg1, $altbg2;

    $users  = array();
    $buddys = array();
    $buddys['offline'] = '';
    $buddys['online'] = '';

    $q = $db->query("SELECT b.buddyname, w.invisible, w.username FROM $table_buddys b LEFT JOIN $table_whosonline w ON (b.buddyname=w.username) WHERE b.username='$xmbuser'");

    while($buddy = $db->fetch_array($q)) {
        if ( $buddy['invisible'] == 1) {
            if (!X_ADMIN) {
                eval("\$buddys['offline'] .= \"".template('buddy_u2u_off')."\";");
            } else {
                eval("\$buddys['online'] .= \"".template('buddy_u2u_inv')."\";");
            }
        } elseif ( $buddy['username'] != '') {
            eval("\$buddys['online'] .= \"".template('buddy_u2u_on')."\";");
        } else {
            eval("\$buddys['offline']   .= \"".template('buddy_u2u_off')."\";");
        }
    }

    if (count($buddys) == 0) {
        blistmsg($lang['no_buddies']);
    } else {
        eval('echo stripslashes("'.template('buddy_u2u').'");');
    }
}

/**
* buddy_addu2u() - Display a list of buddies
*
* @param    none, but takes many globals
* @return    no return value, but will display a status report or a list of buddies
*/
function buddy_display()
{
    global $db, $table_buddys, $table_whosonline, $lang, $xmbuser;
    global $charset, $css, $bbname, $text, $bordercolor, $borderwidth, $tablespace, $tablewidth, $cattext, $altbg1, $altbg2;

    $q = $db->query("SELECT b.buddyname, w.invisible, w.username FROM $table_buddys b LEFT JOIN $table_whosonline w ON (b.buddyname=w.username) WHERE b.username='$xmbuser'");
    $buddys = array();
    $buddys['offline'] = '';
    $buddys['online'] = '';

    while($buddy = $db->fetch_array($q)) {
        if ( $buddy['username'] != '' ) {
            if ( $buddy['invisible'] == 1 ) {
                if ( !X_ADMIN ) {
                    eval("\$buddys['offline'] .= \"".template('buddylist_buddy_offline')."\";");
                    continue;
                } else {
                    $buddystatus = $lang['hidden'];
                }
            } else {
                $buddystatus =  $lang['textonline'];
            }
            eval("\$buddys['online'] .= \"".template('buddylist_buddy_online')."\";");
        } else {
            eval("\$buddys['offline'] .= \"".template('buddylist_buddy_offline')."\";");
        } // if
    } // while

    eval('echo stripslashes("'.template('buddylist').'");');
}

switch($action) {
    case 'add':
        buddy_add($buddys);
        break;

    case 'edit':
        buddy_edit();
        break;

    case 'delete':
        if (isset($delete) && is_array($delete) && count($delete) > 0) {
            buddy_delete($delete);
        } else {
            blistmsg($lang['nomember']);
        }
        break;

    case 'add2u2u':
        buddy_addu2u();
        break;

    default:
        buddy_display();
        break;
}
?>