<?php
/* $Id: cp_attachment.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
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

function displayAttachmentsPanel() {
    global $THEME, $lang, $table_forums, $db, $oToken;

    $forumselect = "<select name=\"forumprune\">\n";
    $forumselect .= "<option value=\"$lang[textall]\">$lang[textall]</option>\n";
    $querycat = $db->query("SELECT * FROM $table_forums WHERE type='forum' OR type='sub' ORDER BY displayorder");
    while ($forum = $db->fetch_array($querycat)) {
        $forumselect .= "<option value=\"$forum[fid]\">$forum[name]</option>\n";
    }
    $forumselect .= "</select>";
?>
    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp2.php?action=attachments">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="550" align="center">
    <tr><td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr>
    <td class="category" colspan="2"><font style="color: <?php echo $THEME['cattext']?>"><strong><?php echo $lang['textsearch']?></font></strong></td>
    </tr>
    <tr>
    <td class="tablerow altbg1"><?php echo $lang['attachmanwherename']?></td>
    <td class="altbg2"><input type="text" name="filename" size="30" /></td>
    </tr>
    <tr>
    <td class="tablerow altbg1"><?php echo $lang['attachmanwhereauthor']?></td>
    <td class="altbg2"><input type="text" name="author" size="40" /></td>
    </tr>
    <tr>
    <td class="tablerow altbg1"><?php echo $lang['attachmanwhereforum']?></td>
    <td class="altbg2"><?php echo $forumselect?></td>
    </tr>
    <tr>
    <td class="tablerow altbg1"><?php echo $lang['attachmanwheresizesmaller']?></td>
    <td class="altbg2"><input type="text" name="sizeless" size="20" /></td>
    </tr>
    <tr>
    <td class="tablerow altbg1"><?php echo $lang['attachmanwheresizegreater']?></td>
    <td class="altbg2"><input type="text" name="sizemore" size="20" /></td>
    </tr>
    <tr>
    <td class="tablerow altbg1"><?php echo $lang['attachmanwheredlcountsmaller']?></td>
    <td class="altbg2"><input type="text" name="dlcountless" size="20" /></td>
    </tr>
    <tr>
    <td class="tablerow altbg1"><?php echo $lang['attachmanwheredlcountgreater']?></td>
    <td class="altbg2"><input type="text" name="dlcountmore" size="20" /></td>
    </tr>
    <tr>
    <td class="tablerow altbg1"><?php echo $lang['attachmanwheredaysold']?></td>
    <td class="altbg2"><input type="text" name="daysold" size="20" /></td>
    </tr>
    <tr>
    <td align="center" class="tablerow altbg2" colspan="2"><input type="submit" name="searchsubmit" class="submit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
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

function processAttachmentSearch() {
    global $THEME, $lang, $oToken;
    global $db, $table_attachments, $table_forums, $table_posts, $table_threads;
    
    ?>
    <tr class="altbg2">
    <td align="center">
    <form method="post" action="cp2.php?action=attachments">
    <?php echo $oToken->getToken(1); ?>
    <table cellspacing="0" cellpadding="0" border="0" width="93%" align="center">
    <tr>
    <td style="background-color: <?php echo $THEME['bordercolor']?>">
    <table border="0" cellspacing="<?php echo $THEME['borderwidth']?>" cellpadding="<?php echo $THEME['tablespace']?>" width="100%">
    <tr>
    <td class="category" colspan="6"><font style="color: <?php echo $THEME['cattext']?>"><strong><?php echo $lang['textattachsearchresults']?></strong></font></td>
    </tr>
    <tr>
    <td class="header" width="4%" align="center">?</td>
    <td class="header" width="25%"><?php echo $lang['textfilename']?></td>
    <td class="header" width="29%"><?php echo $lang['textauthor']?></td>
    <td class="header" width="27%"><?php echo $lang['textinthread']?></td>
    <td class="header" width="10%"><?php echo $lang['textfilesize']?></td>
    <td class="header" width="5%"><?php echo $lang['textdownloads']?></td>
    </tr>
    <?php


    $restriction = '';
    $orderby = '';

    $forumprune = formInt('forumprune');
    if ($forumprune > 0) {
        $restriction .= "AND p.fid=$forumprune ";
    }

    $daysold = formInt('daysold');
    if ($daysold > 0) {
        $datethen = time() - (86400 * $daysold);
        $restriction .= "AND p.dateline <= $datethen ";
        $orderby = ' ORDER BY p.dateline ASC';
    }

    $author = $db->escape(formVar('author'));
    if (!empty ($author)) {
        $author = trim($author);
        if ($author != '') {
            $restriction .= "AND p.author = '$author' ";
            $orderby = ' ORDER BY p.author ASC';
        }
    }

    $filename = $db->escape(formVar('filename'));
    if (!empty ($filename)) {
        $filename = trim($filename);
        if ($filename != "") {
            $restriction .= "AND a.filename LIKE '%$filename%' ";
        }
    }

    $sizeless = formInt('sizeless');
    if ($sizeless > 0) {
        $restriction .= "AND a.filesize < $sizeless ";
        $orderby = ' ORDER BY a.filesize DESC';
    }

    $sizemore = formInt('sizemore');
    if ($sizemore > 0) {
        $restriction .= "AND a.filesize > $sizemore ";
        $orderby = ' ORDER BY a.filesize DESC';
    }

    $dlcountless = formInt('dlcountless');
    if ($dlcountless > 0) {
        $restriction .= "AND a.downloads < $dlcountless ";
        $orderby = ' ORDER BY a.downloads DESC';
    }

    $dlcountmore = formInt('dlcountmore');
    if ($dlcountmore > 0) {
        $restriction .= "AND a.downloads > $dlcountmore ";
        $orderby = ' ORDER BY a.downloads DESC ';
    }

    $query = $db->query("SELECT a.*, p.*, t.tid, t.subject AS tsubject, f.name AS fname FROM $table_attachments a, $table_posts p, $table_threads t, $table_forums f WHERE a.pid=p.pid AND t.tid=a.tid AND f.fid=p.fid $restriction $orderby");
    while ($attachment = $db->fetch_array($query)) {
        $attachsize = strlen($attachment['attachment']);
        if ($attachsize >= 1073741824) {
            $attachsize = round($attachsize / 1073741824 * 100) / 100 . "gb";
        }
        elseif ($attachsize >= 1048576) {
            $attachsize = round($attachsize / 1048576 * 100) / 100 . "mb";
        }
        elseif ($attachsize >= 1024) {
            $attachsize = round($attachsize / 1024 * 100) / 100 . "kb";
        } else {
            $attachsize = $attachsize . "b";
        }
        $attachment['tsubject'] = stripslashes($attachment['tsubject']);
        $attachment['fname'] = stripslashes($attachment['fname']);
        $attachment['filename'] = stripslashes($attachment['filename']);
        ?>
        <tr>
        <td class="altbg1 tablerow" align="center" valign="middle">
        <a href="cp2.php?action=delete_attachment&amp;aid=<?php echo $attachment['aid']?>">Delete</a>
        <td class="altbg2 tablerow" valign="top"><input type="text" name="filename<?php echo $attachment['aid']?>" value="<?php echo $attachment['filename']?>"><br /><span class="smalltxt"><a href="viewthread.php?action=attachment&amp;tid=<?php echo $attachment['tid']?>&amp;pid=<?php echo $attachment['pid']?>" target="_blank"><?php echo $lang['textdownload']?></a></td>
        <td class="altbg2 tablerow" valign="top"><?php echo $attachment['author']?></td>
        <td class="altbg2 tablerow" valign="top"><a href="viewthread.php?tid=<?php echo $attachment['tid']?>"><?php echo $attachment['tsubject']?></a><br /><span class="smalltxt"><?php echo $lang['textinforum']?> <a href="forumdisplay.php?fid=<?php echo $attachment['fid']?>"><?php echo $attachment['fname']?></a></span></td>
        <td class="altbg2 tablerow" valign="top" align="center"><?php echo $attachsize?></td>
        <td class="altbg2 tablerow" valign="top" align="center"><?php echo $attachment['downloads']?></td>
        </tr>
        <?php

    }
    ?>
    <tr>
    <td align="center" class="tablerow altbg2" colspan="6"><input class="submit" type="submit" name="deletesubmit" value="<?php echo $lang['textsubmitchanges']?>" /></td>
    </tr>
    </table>
    </td>
    </tr>
    </table>
    <input type="hidden" name="filename" value="<?php echo $filename?>" />
    <input type="hidden" name="author" value="<?php echo $author?>" />
    <input type="hidden" name="forumprune" value="<?php echo $forumprune?>" />
    <input type="hidden" name="sizeless" value="<?php echo $sizeless?>" />
    <input type="hidden" name="sizemore" value="<?php echo $sizemore?>" />
    <input type="hidden" name="dlcountless" value="<?php echo $dlcountless?>" />
    <input type="hidden" name="dlcountmore" value="<?php echo $dlcountmore?>" />
    <input type="hidden" name="daysold" value="<?php echo $daysold?>" />
    </form>
    </td>
    </tr>
    <?php
}

function processAttachmentDelete() {
    global $db, $lang, $oToken, $table_attachments, $table_posts, $table_threads, $table_forums;

    $oToken->isValidToken();

    $forumprune = formVar('forumprune');
    $queryforum = '';
    if (!empty ($forumprune) && is_numeric($forumprune)) {
        $forumprune = intval($forumprune);
        $queryforum = "AND p.fid='$forumprune' ";
    }

    $daysold = formInt('daysold');
    $querydate = '';
    if ($daysold > 0) {
        $datethen = time() - (86400 * $daysold);
        $querydate = "AND p.dateline <= '$datethen' ";
    }

    $author = $db->escape(formVar('author'));
    $queryauthor = '';
    if ($author != "") {
        $queryauthor = "AND p.author = '$author' ";
    }

    $filename = $db->escape(formVar('filename'));
    $queryname = '';
    if ($filename != "") {
        $queryname = "AND a.filename LIKE '%$filename%' ";
    }

    $sizeless = formInt('sizeless');
    $querysizeless = '';
    if ($sizeless > 0) {
        $querysizeless = "AND a.filesize < '$sizeless' ";
    }

    $sizemore = formInt('sizemore');
    $querysizemore = '';
    if ($sizemore > 0) {
        $querysizemore = "AND a.filesize > '$sizemore' ";
    }

    $dlcountless = formInt('dlcountless');
    $querydlcountless = '';
    if ($dlcountless > 0) {
        $querydlcountless = "AND a.downloads < '$dlcountless' ";
    }

    $dlcountmore = formInt('dlcountmore');
    $querydlcountmore = '';
    if ($dlcountmore > 0) {
        $querydlcountmore = "AND a.downloads > '$dlcountmore' ";
    }

    $query = $db->query("SELECT a.*, p.*, t.tid, t.subject AS tsubject, f.name AS fname FROM $table_attachments a, $table_posts p, $table_threads t, $table_forums f WHERE a.pid=p.pid AND t.tid=a.tid AND f.fid=p.fid $queryforum $querydate $queryauthor $queryname $querysizeless $querysizemore");
    while ($attachment = $db->fetch_array($query)) {
        $afilename = formVar("filename" . $attachment['aid']);

        if ($attachment['filename'] != $afilename) {
            $db->query("UPDATE $table_attachments SET filename='$afilename' WHERE aid='$attachment[aid]'");
        }
    }
    
    message($lang['textattachmentsupdate'], false, '</td></tr></table></td></tr></table><br />', '', 'cp2.php', false, false, false);
}

function downloadAttachments() {
    $code = '';
    $templates = $db->query("SELECT * FROM $table_templates");
    while ($template = $db->fetch_array($templates)) {
        $template['template'] = trim($template['template']);
        $template['name'] = trim($template['name']);

        if ($template['name'] != '') {
            $template['template'] = stripslashes($template['template']);

            $code .= $template['name'] . '|#*XMB TEMPLATE*#|' . "\r\n" . $template['template'] . "\r\n\r\n" . '|#*XMB TEMPLATE FILE*#|';
        }
    }
    header("Content-disposition: attachment; filename=templates.xmb");
    header("Content-Length: " . strlen($code));
    header("Content-type: unknown/unknown");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo $code;
    exit ();
}

function dumpAttachments() {
    global $db, $lang, $table_attachments;

    $i = 0;

    if (!is_writable('./attachments')) {
        error($lang['error_chmod_attach_dir']);
    }

    $query = $db->unbuffered_query("SELECT * FROM $table_attachments");
    while ($attachment = $db->fetch_array($query)) {
        $stream = @ fopen('./attachments/' . $attachment['aid'] . '.xmb', 'w+');
        fwrite($stream, $attachment['attachment'], strlen($attachment['attachment']));
        fclose($stream);

        unset ($attachment['attachment']);
        $info_string = implode('//||//', $attachment) . "\n";

        $stream2 = @ fopen('./attachments/index.inf', 'a+');
        fwrite($stream2, $info_string, strlen($info_string));
        fclose($stream2);

        $i++;
    }

    message($lang['attachments_num_stored'], false, '</td></tr></table></td></tr></table><br />', '', 'cp2.php', false, false, false);
}

function deleteAttachment() {
    global $db, $table_attachments, $aid, $lang;

    $db->query("DELETE FROM $table_attachments WHERE aid='$aid'");
    
    message($lang['attachmentsdeleted'], false, '</td></tr></table></td></tr></table><br />', '', 'cp2.php', false, false, false);
}

function restoreAttachments() {
    global $db, $lang, $table_attachments;

    $i = 0;
    if (!is_readable('./attachments')) {
        echo '<tr class="tablerow altbg2"><td align="center">' . $lang['error_chmod_attach_dir'] . '</td></tr>';
    } else {
        $trans = array (
            0 => 'aid',
            1 => 'tid',
            2 => 'pid',
            3 => 'filename',
            4 => 'filetype',
            5 => 'filesize',
            6 => 'downloads'
        );

        $mainstream = fopen('./attachments/index.inf', 'r');
        while (($line = fgets($mainstream)) !== false) {
            $attachment = array ();

            $attachment = array_keys2keys(explode('//||//', $line), $trans);

            $stream = fopen('./attachments/' . $attachment['aid'] . '.xmb', 'r');
            $attachment['attachment'] = fread($stream, filesize('./attachments/' . $attachment['aid'] . '.xmb'));
            fclose($stream);

            $db->query("INSERT INTO $table_attachments ( aid, tid, pid, filename, filetype, filesize, attachment, downloads ) VALUES ('$attachment[aid]', '$attachment[tid]', '$attachment[pid]', '$attachment[filename]', '$attachment[filetype]', '$attachment[filesize]', '$attachment[attachment]', '$attachment[downloads]')");

            $i++;
        }

        fclose($mainstream);
        
        message($i . ' ' . $lang['attachments_num_restored'], false, '</td></tr></table></td></tr></table><br />', '', 'cp2.php', false, false, false);
    }
}

?>
