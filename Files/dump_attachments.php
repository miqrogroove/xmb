<?php
/* $Id: dump_attachments.php,v 1.1.2.1 2005/08/12 15:21:32 Tularis Exp $ */
/*
    XMB 1.9.6 SP1
    © 2001 - 2005 Aventure Media & The XMB Development Team
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

require './header.php';

if (!X_ADMIN) {
    nav($lang['error']);
    eval('echo stripslashes("'.template('error_nologinsession').'");');
    end_time();
    eval("echo (\"".template('footer')."\");");
    exit();
}

nav($lang['textcp']);

if ( $action == 'restore_attachments') {
    $i = 0;
    if (!is_readable('./attachments')) {
        exit('attachments directory ("./attachments") should be chmodded to 777 so XMB can write to it.');
    }

    $trans = array( 0=> 'aid',
            1=> 'tid',
            2=> 'pid',
            3=> 'filename',
            4=> 'filetype',
            5=> 'filesize',
            6=> 'downloads'
            );

    $mainstream = fopen('./attachments/index.inf', 'r');
    while(($line = fgets($mainstream)) !== false) {
        $attachment = array();

        $attachment = array_keys2keys(explode('//||//', $line), $trans);

        $stream = fopen('./attachments/'.$attachment['aid'].'.xmb', 'r');
        $attachment['attachment'] = fread($stream, filesize('./attachments/'.$attachment['aid'].'.xmb'));
        fclose($stream);

        $db->query("DELETE FROM $table_attachments WHERE aid='$attachment[aid]' AND tid='$attachment[tid]' AND pid='$attachment[pid]'");
        $db->query("INSERT INTO $table_attachments ( aid, tid, pid, filename, filetype, filesize, attachment, downloads ) VALUES ('$attachment[aid]', '$attachment[tid]', '$attachment[pid]', '$attachment[filename]', '$attachment[filetype]', '$attachment[filesize]', '$attachment[attachment]', '$attachment[downloads]')");

        $i++;
    }

    fclose($mainstream);

    echo $i.' attachments stored';
} elseif ( $action == 'dump_attachments') {
    $i = 0;

    if (!is_writable('./attachments')) {
        exit('attachments directory ("./attachments") should be chmodded to 777 so XMB can write to it.');
    }

    $query = $db->unbuffered_query("SELECT * FROM $table_attachments");
    while($attachment = $db->fetch_array($query)) {
        $stream = @fopen('./attachments/'.$attachment['aid'].'.xmb', 'w+');
        fwrite($stream, $attachment['attachment'], strlen($attachment['attachment']));
        fclose($stream);

        unset($attachment['attachment']);
        $info_string = implode('//||//', $attachment)."\n";

        $stream2 = @fopen('./attachments/index.inf', 'a+');
        fwrite($stream2, $info_string, strlen($info_string));
        fclose($stream2);

        $i++;
    }

    echo $i.' attachments stored';
}
?>