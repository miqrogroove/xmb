<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
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

function blistmsg($message, $redirect = '', $exit = false)
{
    global $bordercolor, $tablewidth, $THEME, $tablespace, $altbg1, $css, $bbname, $lang;
    global $charset, $text, $redirectjs;

    if ($redirect != '') {
        redirect($redirect, 2);
    }

    eval('echo "'.template('buddylist_message').'";');

    if ($exit) {
        exit();
    }
}

function buddy_add($buddys)
{
    global $db, $lang, $xmbuser, $oToken, $full_url;

    if (!is_array($buddys)) {
        $buddys = array($buddys);
    }

    if (count($buddys) > 10) {
        $buddys = array_slice($buddys, 0, 10);
    }

    foreach($buddys as $buddy) {
        if (empty($buddy) || (strlen(trim($buddy)) == 0)) {
            blistmsg($lang['nobuddyselected'], '', true);
        } else {
            if ($buddy === $xmbuser) {
                blistmsg($lang['buddywarnaddself']);
            }

            $q = $db->query("SELECT count(username) FROM ".X_PREFIX."buddys WHERE username='$xmbuser' AND buddyname='$buddy'");
            if ((int) $db->result($q, 0) > 0) {
                blistmsg($buddy.' '.$lang['buddyalreadyonlist']);
            } else {
                $q = $db->query("SELECT count(username) FROM ".X_PREFIX."members WHERE username='$buddy'");
                if ((int) $db->result($q, 0) < 1) {
                    blistmsg($lang['nomember']);
                } else {
                    $db->query("INSERT INTO ".X_PREFIX."buddys (buddyname, username) VALUES ('$buddy', '$xmbuser')");
                    blistmsg($buddy.' '.$lang['buddyaddedmsg'], $full_url.'buddy.php');
                }
            }
        }
    }
}

function buddy_edit()
{
    global $db, $lang, $xmbuser, $oToken;
    global $charset, $css, $bbname, $text, $bordercolor, $THEME, $tablespace, $tablewidth, $cattext, $altbg1, $altbg2;

    $buddys = array();
    $q = $db->query("SELECT buddyname FROM ".X_PREFIX."buddys WHERE username='$xmbuser'");
    while($buddy = $db->fetch_array($q)) {
        eval('$buddys[] = "'.template('buddylist_edit_buddy').'";');
    }

    if (count($buddys) > 0) {
        $buddys = implode("\n", $buddys);
    } else {
        unset($buddys);
        $buddys = '';
    }
    eval('echo "'.template('buddylist_edit').'";');
}

function buddy_delete($delete)
{
    global $db, $lang, $xmbuser, $oToken, $full_url;
    global $charset, $css, $bbname, $text, $bordercolor, $THEME, $tablespace, $tablewidth, $cattext, $altbg1, $altbg2;

    foreach($delete as $buddy) {
        $db->query("DELETE FROM ".X_PREFIX."buddys WHERE buddyname='$buddy' AND username='$xmbuser'");
    }

    blistmsg($lang['buddylistupdated'], $full_url.'buddy.php');
}

/**
* buddy_addu2u() - Display a list of buddies with their online status
*
* @param    none, but takes many globals
* @return    no return value, but will display a status report or a list of buddies and their online status
*/
function buddy_addu2u()
{
    global $db, $lang, $xmbuser, $oToken;
    global $charset, $css, $bbname, $text, $bordercolor, $THEME, $tablespace, $tablewidth, $cattext, $altbg1, $altbg2;

    $buddys = array();
    $buddys['offline'] = '';
    $buddys['online'] = '';

    $q = $db->query("SELECT b.buddyname, m.invisible, m.username, m.lastvisit FROM ".X_PREFIX."buddys b LEFT JOIN ".X_PREFIX."members m ON (b.buddyname=m.username) WHERE b.username='$xmbuser'");
    if ($db->num_rows($q) == 0) {
        blistmsg($lang['no_buddies']);
    } else {
        while($buddy = $db->fetch_array($q)) {
            $buddyout = $buddy['buddyname'];
            $recodename = recodeOut($buddy['buddyname']);
            if (vars()->onlinetime - (int)$buddy['lastvisit'] <= X_ONLINE_TIMER) {
                if ('1' === $buddy['invisible']) {
                    if (!X_ADMIN) {
                        eval('$buddys["offline"] .= "'.template('buddy_u2u_off').'";');
                    } else {
                        eval('$buddys["online"] .= "'.template('buddy_u2u_inv').'";');
                    }
                } else {
                    eval('$buddys["online"] .= "'.template('buddy_u2u_on').'";');
                }
            } else {
                eval('$buddys["offline"] .= "'.template('buddy_u2u_off').'";');
            }
        }
        eval('echo "'.template('buddy_u2u').'";');
    }
}

function buddy_display()
{
    global $db, $lang, $xmbuser, $oToken;
    global $charset, $css, $bbname, $text, $bordercolor, $THEME, $tablespace, $tablewidth, $cattext, $altbg1, $altbg2;

    $q = $db->query("SELECT b.buddyname, m.invisible, m.username, m.lastvisit FROM ".X_PREFIX."buddys b LEFT JOIN ".X_PREFIX."members m ON (b.buddyname=m.username) WHERE b.username='$xmbuser'");
    $buddys = array();
    $buddys['offline'] = '';
    $buddys['online'] = '';
    while ($buddy = $db->fetch_array($q)) {
        $recodename = recodeOut($buddy['buddyname']);
        if (vars()->onlinetime - (int) $buddy['lastvisit'] <= X_ONLINE_TIMER) {
            if ('1' === $buddy['invisible']) {
                if (!X_ADMIN) {
                    eval('$buddys["offline"] .= "'.template('buddylist_buddy_offline').'";');
                    continue;
                } else {
                    $buddystatus = $lang['hidden'];
                }
            } else {
                $buddystatus = $lang['textonline'];
            }
            eval('$buddys["online"] .= "'.template('buddylist_buddy_online').'";');
        } else {
            eval('$buddys["offline"] .= "'.template('buddylist_buddy_offline').'";');
        }
    }
    eval('echo "'.template('buddylist').'";');
}
