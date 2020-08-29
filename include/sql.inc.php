<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12-alpha  Do not use this experimental software after 1 October 2020.
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2020, The XMB Group
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
 *
 **/

declare(strict_types=1);

namespace XMB\SQL;

if (!defined('IN_CODE')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

/**
 * SQL command
 *
 * @since 1.9.12
 * @param string $username Must be HTML encoded.
 * @return array Member record or empty array.
 */
function getMemberByName( string $username ): array {
    global $db;
    
    $sqluser = $db->escape( $username );

    $query = $db->query("SELECT * FROM ".X_PREFIX."members WHERE username = '$sqluser'");
    if ($db->num_rows($query) == 1) {
        $member = $db->fetch_array($query);
    } else {
        $member = [];
    }
    $db->free_result($query);
    return $member;
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function saveSession( string $token, string $username, int $date, int $expire, int $regenerate, string $replace, string $agent ): bool {
    global $db;
    
    $sqltoken = $db->escape( $token );
    $sqluser = $db->escape( $username );
    $sqlreplace = $db->escape( $replace );
    $sqlagent = $db->escape( $agent );

    $db->query("INSERT IGNORE INTO ".X_PREFIX."sessions SET token = '$sqltoken', username = '$sqluser', login_date = $date,
        expires = $expire, regenerate = $regenerate, replaces = '$sqlreplace', agent = '$sqlagent'");

    return ($db->affected_rows() == 1);
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function getSession( string $token, string $username ): array {
    global $db;
    
    $sqltoken = $db->escape( $token );
    $sqluser = $db->escape( $username );

    $query = $db->query("SELECT * FROM ".X_PREFIX."sessions WHERE token = '$sqltoken' AND username = '$sqluser'");
    if ($db->num_rows($query) == 1) {
        $session = $db->fetch_array($query);
    } else {
        $session = [];
    }
    $db->free_result($query);
    return $session;
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function deleteSession( string $token ) {
    global $db;
    
    $sqltoken = $db->escape( $token );

    $db->query("DELETE FROM ".X_PREFIX."sessions WHERE token = '$sqltoken'");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function deleteSessionsByName( string $username ) {
    global $db;
    
    $sqluser = $db->escape( $username );

    $db->query("DELETE FROM ".X_PREFIX."sessions WHERE username = '$sqluser'");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function clearSessionParent( string $token ) {
    global $db;

    $sqltoken = $db->escape( $token );

    $db->query("UPDATE ".X_PREFIX."sessions SET replaces = '' WHERE token = '$sqltoken'");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function getSessionReplacement( string $token, string $username ): array {
    global $db;
    
    $sqltoken = $db->escape( $token );
    $sqluser = $db->escape( $username );

    $query = $db->query("SELECT * FROM ".X_PREFIX."sessions WHERE replaces = '$sqltoken' AND username = '$sqluser'");
    if ($db->num_rows($query) == 1) {
        $session = $db->fetch_array($query);
    } else {
        $session = [];
    }
    $db->free_result($query);
    return $session;
}

/**
 * SQL command
 *
 * @since 1.9.12
 * @param string $username
 * @return int The new value of bad_login_count for this member.
 */
function raiseLoginCounter( string $username ): int {
    global $db;

    $sqluser = $db->escape( $username );

    $db->query("UPDATE ".X_PREFIX."members SET bad_login_count = LAST_INSERT_ID(bad_login_count + 1) WHERE username = '$sqluser'");

    return $db->insert_id();
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function resetLoginCounter( string $username, int $date ) {
    global $db;

    $sqluser = $db->escape( $username );

    $db->query("UPDATE ".X_PREFIX."members SET bad_login_count = 1, bad_login_date = $date WHERE username = '$sqluser'");
}

/**
 * SQL command
 *
 * @since 1.9.12
 * @param string $username
 * @return int The new value of bad_session_count for this member.
 */
function raiseSessionCounter( string $username ): int {
    global $db;

    $sqluser = $db->escape( $username );

    $db->query("UPDATE ".X_PREFIX."members SET bad_session_count = LAST_INSERT_ID(bad_session_count + 1) WHERE username = '$sqluser'");

    return $db->insert_id();
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function resetSessionCounter( string $username, int $date ) {
    global $db;

    $sqluser = $db->escape( $username );

    $db->query("UPDATE ".X_PREFIX."members SET bad_session_count = 1, bad_session_date = $date WHERE username = '$sqluser'");
}

/**
 * SQL command
 *
 * @since 1.9.12
 * @return mixed Query result.
 */
function getSuperEmails() {
    global $db;
    
    return $db->query("SELECT username, email, langfile FROM ".X_PREFIX."members WHERE status = 'Super Administrator'");
}



return;