<?php
/**
 * XMB 1.9.9 Saigo
 *
 * Developed by the XMB Group Copyright (c) 2001-2008
 * Sponsored by iEntry Inc. Copyright (c) 2007
 *
 * http://xmbgroup.com , http://ientry.com
 *
 * This software is released under the GPL License, you should
 * have received a copy of this license with the download of this
 * software. If not, you can obtain a copy by visiting the GNU
 * General Public License website <http://www.gnu.org/licenses/>.
 *
 **/

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

class mod {
    function mod() {
        global $self, $xmbuser, $xmbpw, $lang, $action, $oToken;

        if (!X_STAFF && $action != 'votepoll' && $action != 'report') {
            extract($GLOBALS);
            error($lang['notpermitted'], false);
        }
    }

    function statuscheck($fid) {
        global $self, $xmbuser, $lang, $db, $oToken;

        $query = $db->query("SELECT moderator FROM ".X_PREFIX."forums WHERE fid='$fid'");
        $mods = $db->result($query, 0);
        $status1 = modcheck($self['status'], $xmbuser, $mods);

        if (X_SMOD || X_ADMIN) {
            $status1 = 'Moderator';
        }

        if ($status1 != 'Moderator') {
            extract($GLOBALS);
            error($lang['textnoaction'], false);
        }
    }

    function log($user='', $action, $fid, $tid, $reason='') {
        global $xmbuser, $db, $oToken;

        if ($user == '') {
            $user = $xmbuser;
        }
        $db->query("REPLACE ".X_PREFIX."logs (tid, username, action, fid, date) VALUES ('$tid', '$user', '$action', '$fid', ".$db->time().")");
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
