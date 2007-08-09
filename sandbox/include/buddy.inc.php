<?php
/**
 * XMB 1.9.8 Engage Final
 *
 * Developed By The XMB Group
 * Copyright (c) 2001-2007, The XMB Group
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 **/

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

function blistmsg($message, $redirect='', $exit=false) {
    global $bordercolor, $tablewidth, $borderwidth, $tablespace, $altbg1, $css, $bbname, $lang;
    global $charset, $text, $redirectjs;

    if ($redirect != '') {
        redirect($redirect, 2);
    }
    eval('echo stripslashes("'.template('buddylist_message').'");');
    if ($exit) {
        exit();
    }
}

function buddy_add($buddys) {
    global $db, $lang, $xmbuser, $oToken;

    if (!is_array($buddys)) {
        $buddys = array($buddys);
    }

    if (count($buddys) > 10) {
        $buddys = array_slice($buddys, 0, 10);
    }

    foreach ($buddys as $key=>$buddy) {
        if (empty($buddy) || (strlen(trim($buddy)) == 0)) {
            blistmsg($lang['nobuddyselected'], '', true);
        } else {
            $buddy = addslashes(checkInput($buddy));

            if ($buddy == $xmbuser) {
                blistmsg($lang['buddywarnaddself']);
            }

            $q = $db->query("SELECT count(username) FROM ".X_PREFIX."buddys WHERE username='$xmbuser' AND buddyname='$buddy'");
            if ($db->result($q, 0) > 0) {
                blistmsg($buddy.' '.$lang['buddyalreadyonlist']);
            } else {
                $q = $db->query("SELECT count(username) FROM ".X_PREFIX."members WHERE username='$buddy'");
                if ($db->result($q, 0) < 1) {
                    blistmsg($lang['nomember']);
                } else {
                    $db->query("INSERT INTO ".X_PREFIX."buddys (buddyname, username) VALUES ('$buddy', '$xmbuser')");
                    blistmsg($buddy.' '.$lang['buddyaddedmsg'], 'buddy.php');
                }
            }
        }
    }
}

function buddy_edit() {
    global $db, $lang, $xmbuser, $oToken;
    global $charset, $css, $bbname, $text, $bordercolor, $borderwidth, $tablespace, $tablewidth, $cattext, $altbg1, $altbg2;

    $buddys = array();

    $q = $db->query("SELECT buddyname FROM ".X_PREFIX."buddys WHERE username='$xmbuser'") or die($db->error());
    while ($buddy = $db->fetch_array($q)) {
        eval('$buddys[] = "'.template('buddylist_edit_buddy').'";');
    }

    if (count($buddys) > 0) {
        $buddys = implode("\n", $buddys);
    } else {
        unset($buddys);
        $buddys = '';
    }
    eval('echo stripslashes("'.template('buddylist_edit').'");');
}

function buddy_delete($delete) {
    global $db, $lang, $xmbuser, $oToken;
    global $charset, $css, $bbname, $text, $bordercolor, $borderwidth, $tablespace, $tablewidth, $cattext, $altbg1, $altbg2;

    foreach ($delete as $key=>$buddy) {
        $buddy = addslashes(checkInput($buddy));
        $db->query("DELETE FROM ".X_PREFIX."buddys WHERE buddyname='$buddy' AND username='$xmbuser'");
    }

    blistmsg($lang['buddylistupdated'], 'buddy.php');
}

function buddy_addu2u() {
    global $db, $lang, $xmbuser, $oToken;
    global $charset, $css, $bbname, $text, $bordercolor, $borderwidth, $tablespace, $tablewidth, $cattext, $altbg1, $altbg2;

    $users = array();
    $buddys = array();
    $buddys['offline'] = '';
    $buddys['online'] = '';

    $q = $db->query("SELECT b.buddyname, w.invisible, w.username FROM ".X_PREFIX."buddys b LEFT JOIN ".X_PREFIX."whosonline w ON (b.buddyname=w.username) WHERE b.username='$xmbuser'");
    while ($buddy = $db->fetch_array($q)) {
        if ($buddy['invisible'] == 1) {
            if (!X_ADMIN) {
                eval("\$buddys['offline'] .= \"".template('buddy_u2u_off')."\";");
            } else {
                eval("\$buddys['online'] .= \"".template('buddy_u2u_inv')."\";");
            }
        } else if ($buddy['username'] != '') {
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

function buddy_display() {
    global $db, $lang, $xmbuser, $oToken;
    global $charset, $css, $bbname, $text, $bordercolor, $borderwidth, $tablespace, $tablewidth, $cattext, $altbg1, $altbg2;

    $q = $db->query("SELECT b.buddyname, w.invisible, w.username FROM ".X_PREFIX."buddys b LEFT JOIN ".X_PREFIX."whosonline w ON (b.buddyname=w.username) WHERE b.username='$xmbuser'");
    $buddys = array();
    $buddys['offline'] = '';
    $buddys['online'] = '';
    while ($buddy = $db->fetch_array($q)) {
        if ($buddy['username'] != '') {
            if ($buddy['invisible'] == 1) {
                if (!X_ADMIN) {
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
        }
    }
    eval('echo stripslashes("'.template('buddylist').'");');
}
?>