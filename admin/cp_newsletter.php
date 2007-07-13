<?php
/* $Id: cp_newsletter.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
/*
    © 2001 - 2007 The XMB Development Team
    http://www.xmbforum.com

    Financial and other support 2007- iEntry Inc
    http://www.ientry.com

    Financial and other support 2002-2007 Aventure Media 
    http://www.aventure-media.co.uk

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

if (!defined('IN_CODE') && (defined('DEBUG') && DEBUG == false)) {
    exit ("Not allowed to run this file directly.");
}

function displayNewsletterPanel() {
    global $THEME, $oToken, $lang, $oToken;
?>

    <tr class="altbg2">
    <td>
    <form method="post" action="cp2.php?action=newsletter">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr class="category">
    <td colspan="2"><strong><font style="color: <?php echo $THEME['cattext']?>"><?php echo $lang['textnewsletter']?></font></strong></td>
    </tr>
    <tr>
    <td class="altbg1 tablerow"><?php echo $lang['textsubject']?></td>
    <td class="altbg2 tablerow"><input type="text" name="newssubject" size="80" class="altbg1" /></td>
    </tr>
    <tr>
    <td class="altbg1 tablerow" valign="top"><?php echo $lang['textmessage']?></td>
    <td class="altbg2 tablerow"><textarea cols="80" rows="10" name="newsmessage" class="altbg1" ></textarea></td>
    </tr>
    <tr>
    <td class="altbg1 tablerow" valign="top"><?php echo $lang['textsendvia']?></td>
    <td class="altbg2 tablerow"><input type="radio" value="email" name="sendvia" class="altbg1" /> <?php echo $lang['textemail']?><br /><input type="radio" value="u2u" checked="checked" name="sendvia" class="altbg1" /> <?php echo $lang['textu2u']?></td>
    </tr>
    <tr>
    <td class="altbg1 tablerow" valign="top"><?php echo $lang['textsendto']?></td>
    <td class="altbg2 tablerow"><input type="radio" value="all" checked="checked" name="to" /> <?php echo $lang['textsendall']?><br />
    <input type="radio" value="staff" name="to" /> <?php echo $lang['textsendstaff']?><br />
    <input type="radio" value="admin" name="to" /> <?php echo $lang['textsendadmin']?><br />
    <input type="radio" value="supermod" name="to" /> <?php echo $lang['textsendsupermod']?><br />
    <input type="radio" value="mod" name="to" /> <?php echo $lang['textsendmod']?></td>
    </tr>
    <tr>
    <td class="altbg1 tablerow" valign="top"><?php echo $lang['textfaqextra']?></td>
    <td class="altbg2 tablerow">
    <input type="checkbox" value="yes" checked="checked" name="newscopy" /> <?php echo $lang['newsreccopy']?><br />
    <select name="wait" class="altbg1">
    <option value="0">0</option>
    <option value="50">50</option>
    <option value="100">100</option>
    <option value="150">150</option>
    <option value="200">200</option>
    <option value="250">250</option>
    <option value="500">500</option>
    <option value="1000">1000</option>
    </select>
    <?php echo $lang['newswait']?><br />
    </td>
    </tr>
    <tr>
    <td align="center" colspan="2" class="tablerow altbg2"><input type="submit" class="submit" name="newslettersubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    </form>
    </td>
    </tr>

    <?php

}

function processNewsletter() {
    global $db, $table_members, $xmbuser, $SETTINGS, $charset, $lang, $oToken, $table_u2u;

    $oToken->isValidToken();

    @set_time_limit(0);

    $newssubject = $db->escape(formVar('newssubject'));
    $newsmessage = $db->escape(formVar('newsmessage'));
    $newscopy = $db->escape(formVar('newscopy'));

    $_xmbuser = $db->escape($xmbuser);

    if ($newscopy != 'yes') {
        $tome = 'AND NOT username=\'' . $_xmbuser . '\'';
    } else {
        $tome = '';
    }

    $to = $db->escape(formVar('to'));

    if ($to == "all") {
        $query = $db->query("SELECT username, email FROM $table_members WHERE newsletter='yes' $tome ORDER BY uid");
    }
    elseif ($to == "staff") {
        $query = $db->query("SELECT username, email FROM $table_members WHERE (status='Super Administrator' OR status='Administrator' OR status='Super Moderator' OR status='Moderator') $tome ORDER BY uid");
    }
    elseif ($to == "admin") {
        $query = $db->query("SELECT username, email FROM $table_members WHERE (status='Administrator' OR status = 'Super Administrator') $tome ORDER BY uid");
    }
    elseif ($to == "supermod") {
        $query = $db->query("SELECT username, email FROM $table_members WHERE status='Super moderator' $tome ORDER by uid");
    }
    elseif ($to == "mod") {
        $query = $db->query("SELECT username, email FROM $table_members WHERE status='Moderator' ORDER BY uid");
    }

    $sendvia = formVar('sendvia');
    if ($sendvia == "u2u") {
        while ($memnews = $db->fetch_array($query)) {
            $db->query("INSERT INTO $table_u2u (msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus) VALUES ('" . $db->escape($memnews['username']) . "', '" . $_xmbuser . "', 'incoming', '" . $db->escape($memnews['username']) . "', 'Inbox', '$newssubject', '$newsmessage', '" . time() . "', 'no', 'yes')");
        }
    } else {
        $newssubject = stripslashes(stripslashes($newssubject));
        $newsmessage = stripslashes(stripslashes($newsmessage));
        $headers[] = 'From: ' . $SETTINGS['bbname'] . ' <' . $SETTINGS['adminemail'] . '>';
        $headers[] = 'X-Sender: <' . $SETTINGS['adminemail'] . '>';
        $headers[] = 'X-Mailer: PHP';
        $headers[] = 'X-AntiAbuse: Board servername - ' . $SETTINGS['bbname'];
        $headers[] = 'X-AntiAbuse: Username - ' . $xmbuser;
        $headers[] = 'X-Priority: 2';
        $headers[] = 'Return-Path: <' . $SETTINGS['adminemail'] . '>';
        $headers[] = 'Content-Type: text/plain; charset=' . $charset;
        $headers = implode("\r\n", $headers);

        $i = 0;
        @ ignore_user_abort(1);
        @ set_time_limit(0);
        @ ob_implicit_flush(1);

        $wait = formInt('wait');

        while ($memnews = $db->fetch_array($query)) {
            if ($i > 0 && $i == $wait) {
                sleep(3);
                $i = 0;
            } else {
                $i++;
            }

            altMail($memnews['email'], '[' . $SETTINGS['bbname'] . '] ' . $newssubject, $newsmessage, $headers);
        }
    }
    echo '<tr class="tablerow altbg2"><td align="center">' . $lang['newslettersubmit'] . '</td></tr>';
    message($lang['censorupdate'], false, '', '', 'cp2.php', false, false, false); // TODO
}

?>
