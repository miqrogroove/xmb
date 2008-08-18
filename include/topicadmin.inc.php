<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Alpha One - This software should not be used for any purpose after 30 September 2008.
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2008, The XMB Group
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 **/

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

class mod {
    function statuscheck($fid) {
        global $self;

        $forum = getForum($fid);
        if ($forum === FALSE) {
            return FALSE;
        }

        return (modcheck($self['username'], $forum['moderator']) == 'Moderator');
    }

    function log($user='', $action, $fid, $tid, $reason='') {
        global $xmbuser, $db, $oToken;

        if ($user == '') {
            $user = $xmbuser;
        }

        $db->query("INSERT INTO ".X_PREFIX."logs (tid, username, action, fid, date) VALUES ('$tid', '$user', '$action', '$fid', ".$db->time().")");

        return true;
    }

    function create_tid_string($tids=0) {
        if (!is_array($tids)) {
            $tidstr = (int)$tids;
        } else {
            $tidstr = '';
            foreach($tids as $value) {
                $value = (int) $value;
                if ($value > 0) {
                    $tidstr .= (empty($tidstr)) ? $value : ','.$value;
                }
            }
        }

        return $tidstr;
    }

    function create_tid_array($tids) {
        $tidArr = array();
        $tidP = explode(',', $tids);
        foreach($tidP AS $flip) {
            $flip = (int) $flip;
            if ($flip > 0) {
                $tidArr[] = $flip;
            }
        }

        return $tidArr;
    }
}
?>
