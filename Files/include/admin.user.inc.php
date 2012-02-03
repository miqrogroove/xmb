<?php
/**
* $Id: admin.user.inc.php,v 1.1.2.3 2004/09/24 15:19:54 ajv Exp $
*/

/**
* XMB 1.9.1 RC2
* © 2001 - 2004 Aventure Media & The XMB Development Team
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

class admin {

	/**
	* rename_user()
	*
	* @param  $userfrom - message to display to user
	* @param  $userto	- new username
	* @return string to display to the admin once the operation has completed
	*/
	function rename_user( $userfrom, $userto ) {
		global $db, $lang;
		global $table_whosonline, $table_members, $table_posts, $table_threads;
		global $table_forums, $table_favorites, $table_buddys, $table_u2u, $table_logs;

		// can't do it if either username is blank
		if ( $userfrom == '' || $userto == '' ) {
			return $lang['admin_rename_fail'];
		}

		// user must currently exist and must not become anyone else
		$query	 = $db->query("SELECT username FROM $table_members WHERE username='$userfrom'");
		$cUsrFrm = $db->num_rows($query);
		$db->free_result($query);

		$query	= $db->query("SELECT username FROM $table_members WHERE username='$userto'");
		$cUsrTo = $db->num_rows($query);
		$db->free_result($query);

		// userfrom must only be 1 (row), and userto must not exist (ie 0 rows)
		if ( !($cUsrFrm == 1 && $cUsrTo == 0) ) {
			return $lang['admin_rename_fail'];
		}

		// userto must not obviate restricted username rules
		if ( ! $this->check_restricted($userto) ) {
			return $lang['restricted'];
		}

		// we're good to go, rename user
		@set_time_limit(180);
		$db->query("UPDATE $table_members SET username='$userto' WHERE username='$userfrom'");
		$db->query("UPDATE $table_buddys SET username='$userto' WHERE username='$userfrom'");
		$db->query("UPDATE $table_buddys SET buddyname='$userto' WHERE buddyname='$userfrom'");
		$db->query("UPDATE $table_favorites SET username='$userto' WHERE username='$userfrom'");
		$db->query("UPDATE $table_forums SET moderator='$userto' WHERE moderator='$userfrom'");
		$db->query("UPDATE $table_logs SET username='$userto' WHERE username='$userfrom'");
		$db->query("UPDATE $table_posts SET author='$userto' WHERE author='$userfrom'");
		$db->query("UPDATE $table_threads SET author='$userto' WHERE author='$userfrom'");
		$db->query("UPDATE $table_u2u SET msgto='$userto' WHERE msgto='$userfrom'");
		$db->query("UPDATE $table_u2u SET msgfrom='$userto' WHERE msgfrom='$userfrom'");
		$db->query("UPDATE $table_u2u SET owner='$userto' WHERE owner='$userfrom'");
		$db->query("UPDATE $table_whosonline SET username='$userto' WHERE username='$userfrom'");

		// update thread last posts
		$query = $db->query("SELECT tid, lastpost from $table_threads WHERE lastpost like '%$userfrom'");
		while ( $result = $db->fetch_array($query) ) {
			list($posttime, $lastauthor) = explode("|", $result['lastpost']);
			if ( $lastauthor == $userfrom ) {
				$newlastpost = $posttime . '|' . $userto;
				$db->query("UPDATE $table_threads SET lastpost='$newlastpost' WHERE tid='".$result['tid']."'");
			}
		}
		$db->free_result($query);

		// update forum last posts
		$query = $db->query("SELECT fid, lastpost from $table_forums WHERE lastpost like '%$userfrom'");
		while ( $result = $db->fetch_array($query) ) {
			list($posttime, $lastauthor) = explode("|", $result['lastpost']);
			if ( $lastauthor == $userfrom ) {
				$newlastpost = $posttime . '|' . $userto;
				$db->query("UPDATE $table_forums SET lastpost='$newlastpost' WHERE fid='".$result['fid']."'");
			}
		}
		$db->free_result($query);

		return $lang['admin_rename_success'];
	}

	/**
	* check_restricted()
	*
	* @param  $userto	username to check
	* @return true = username okay	false username bad
	*/
	function check_restricted( $userto ) {
		global $db, $table_restricted;

		$nameokay = true;

		$find = array('<', '>', '|', '"', '[', ']', '\\', ',', '@', '\''); 
		foreach ($find as $needle) { 
			if (false !== strpos($userto, $needle)) { 
				return false;
			}
		}

        $query = $db->query("SELECT * FROM $table_restricted");
        while ($restriction = $db->fetch_array($query)) {
            if ($restriction['case_sensitivity'] == 1) {
                if ($restriction['partial'] == 1) {
                    if (strpos($userto, $restriction['name']) !== false) {
                        $nameokay = false;
                    }
                } else {
                    if ($userto == $restriction['name']) {
                        $nameokay = false;
                    }
                }
            } else {
                $t_username = strtolower($userto);
                $restriction['name'] = strtolower($restriction['name']);

                if ($restriction['partial'] == 1) {
                    if (strpos($t_username, $restriction['name']) !== false) {
                        $nameokay = false;
                    }
                } else {
                    if ($t_username == $restriction['name']) {
                        $nameokay = false;
                    }
                }
            }
        }
        $db->free_result($query);
		return $nameokay;
	}
}
?>
