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
 */
function saveSession( string $token, string $username, int $date, int $expire, int $regenerate, string $replace, string $agent ): bool {
    global $db;

    $sqltoken = $db->escape( $token );
    $sqluser = $db->escape( $username );
    $sqlreplace = $db->escape( $replace );
    $sqlagent = $db->escape( $agent );

    $db->query("INSERT IGNORE INTO ".X_PREFIX."sessions SET token = '$sqltoken', username = '$sqluser', login_date = $date,
        expire = $expire, regenerate = $regenerate, replaces = '$sqlreplace', agent = '$sqlagent'");

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
function getSessionsByName( string $username ) {
    global $db;
    
    $sqluser = $db->escape( $username );

    return $db->query("SELECT * FROM ".X_PREFIX."sessions WHERE username = '$sqluser'");
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
function deleteSessionsByDate( int $expired ) {
    global $db;
    
    $db->query("DELETE FROM ".X_PREFIX."sessions WHERE expire < $expired");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function deleteSessionsByList( string $username, array $ids, string $current_token ) {
    global $db;
    
    if ( empty( $ids ) ) return;
    
    $sqluser = $db->escape( $username );
    $sqltoken = $db->escape( $current_token );
    $ids = array_map( [$db, 'escape'], $ids );
    $ids = "'" . implode( "','", $ids ) . "'";

    $db->query("DELETE FROM ".X_PREFIX."sessions WHERE username = '$sqluser' AND LEFT(token, 4) IN ($ids) AND token != '$sqltoken' AND replaces != '$sqltoken'");
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

/**
 * SQL command
 *
 * @since 1.9.12
 */
function checkUpgradeOldLogin( string $username, string $password ): bool {
    global $db;
    
    $sqlpass = $db->escape( $password );
    $sqluser = $db->escape( $username );

    $query = $db->query("SELECT COUNT(*) FROM ".X_PREFIX."members WHERE username = '$sqluser' AND password = '$sqlpass' AND status = 'Super Administrator'");
    $count = (int) $db->result( $query, 0 );
    $db->free_result($query);

    return $count == 1;
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function setLostPasswordDate( int $uid, int $date ) {
    global $db;
    
    $db->query("UPDATE ".X_PREFIX."members SET pwdate = $date WHERE uid = $uid");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function setNewPassword( string $username, string $password ) {
    global $db;
    
    $sqlpass = $db->escape( $password );
    $sqluser = $db->escape( $username );

    $db->query("UPDATE ".X_PREFIX."members SET password = '$sqlpass', bad_login_count = 0 WHERE username = '$sqluser'");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function addSetting( string $name, string $value ) {
    global $db;

    $sqlname = $db->escape( $name );
    $sqlvalue = $db->escape( $value );

    $db->query("INSERT INTO ".X_PREFIX."settings SET name = '$sqlname', value = '$sqlvalue' ON DUPLICATE KEY UPDATE value = '$sqlvalue' ");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function updateSetting( string $name, string $value ) {
    global $db;

    $sqlname = $db->escape( $name );
    $sqlvalue = $db->escape( $value );

    $db->query("UPDATE ".X_PREFIX."settings SET value = '$sqlvalue' WHERE name = '$sqlname'");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function deleteSetting( string $name ) {
    global $db;

    $sqlname = $db->escape( $name );

    $db->query("DELETE FROM ".X_PREFIX."settings WHERE name = '$sqlname'");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function getTemplateByID( int $id ): array {
    global $db;
    
    $query = $db->query("SELECT * FROM ".X_PREFIX."templates WHERE id = $id");
    if ($db->num_rows($query) == 1) {
        $result = $db->fetch_array($query);
    } else {
        $result = [];
    }
    $db->free_result($query);

    return $result;
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function getThemeByID( int $id ): array {
    global $db;
    
    $query = $db->query("SELECT * FROM ".X_PREFIX."themes WHERE themeid = $id");
    if ($db->num_rows($query) == 1) {
        $result = $db->fetch_array($query);
    } else {
        $result = [];
    }
    $db->free_result($query);

    return $result;
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function raiseThemeVersions() {
    global $db;

    $db->query("UPDATE ".X_PREFIX."themes SET version = version + 1");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function addToken( string $token, string $username, string $action, string $object, int $expire ): bool {
    global $db;

    $sqltoken = $db->escape( $token );
    $sqluser = $db->escape( $username );
    $sqlaction = $db->escape( $action );
    $sqlobject = $db->escape( $object );

    $db->query("INSERT IGNORE INTO ".X_PREFIX."tokens SET token = '$sqltoken', username = '$sqluser', action = '$sqlaction', object = '$sqlobject', expire = $expire ");

    return ($db->affected_rows() == 1);
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function deleteToken( string $token, string $username, string $action, string $object ): bool {
    global $db;

    $sqltoken = $db->escape( $token );
    $sqluser = $db->escape( $username );
    $sqlaction = $db->escape( $action );
    $sqlobject = $db->escape( $object );

    $db->query("DELETE FROM ".X_PREFIX."tokens WHERE token = '$sqltoken' AND username = '$sqluser' AND action = '$sqlaction' AND object = '$sqlobject'");

    return ($db->affected_rows() == 1);
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function deleteTokensByDate( int $expire ) {
    global $db;

    $db->query("DELETE FROM ".X_PREFIX."tokens WHERE expire < $expire");
}

/**
 * SQL command
 *
 * @param array $values Field name & value list.
 * @param bool $quarantine Save this record in a private table for later review?
 * @return int Post ID number.
 * @since 1.9.12
 */
function addPost( array $values, bool $quarantine = false ): int {
    global $db;

    // Required values:
    $ints = ['fid', 'tid', 'dateline'];
    $strings = ['author', 'message', 'subject', 'icon', 'usesig', 'useip', 'bbcodeoff', 'smileyoff'];

    $all = array_merge( $ints, $strings );
    foreach( $all as $field ) if ( ! isset( $values[$field] ) ) trigger_error( "Missing value $field for \XMB\SQL\addPost()", E_USER_ERROR );
    foreach( $ints as $field ) if ( ! is_int( $values[$field] ) ) trigger_error( "Type mismatch in $field for \XMB\SQL\addPost()", E_USER_ERROR );
    foreach( $strings as $field ) {
        if ( ! is_string( $values[$field] ) ) trigger_error( "Type mismatch in $field for \XMB\SQL\addPost()", E_USER_ERROR );
        $db->escape_fast( $values[$field] );
    }
    
    $table = $quarantine ? X_PREFIX.'hold_posts' : X_PREFIX.'posts';

    $db->query("INSERT INTO $table SET
    fid = {$values['fid']},
    tid = {$values['tid']},
    dateline = {$values['dateline']},
    author = '{$values['author']}',
    message = '{$values['message']}',
    subject = '{$values['subject']}',
    icon = '{$values['icon']}',
    usesig = '{$values['usesig']}',
    useip = '{$values['useip']}',
    bbcodeoff = '{$values['bbcodeoff']}',
    smileyoff = '{$values['smileyoff']}'
    ");

    return $db->insert_id();
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function addFavoriteIfMissing( int $tid, string $username, string $type, bool $quarantine = false ) {
    global $db;

    $sqluser = $db->escape( $username );
    $sqltype = $db->escape( $type );

    $table = $quarantine ? X_PREFIX.'hold_favorites' : X_PREFIX.'favorites';

    $query = $db->query("SELECT COUNT(*) FROM $table WHERE tid = $tid AND username = '$sqluser' AND type = '$sqltype'");
    if ( 0 == (int) $db->result($query, 0) ) {
        $db->query("INSERT INTO $table SET tid = $tid, username = '$sqluser', type = '$sqltype'");
    }
    $db->free_result($query);
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function countOrphanedAttachments( int $uid, bool $quarantine = false ): int {
    global $db;

    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    $query = $db->query( "SELECT COUNT(*) FROM $table WHERE pid = 0 AND parentid = 0 AND uid = $uid" );
    $count = (int) $db->result( $query, 0 );
    $db->free_result($query);

    return $count;
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function countAttachmentsByPost( int $pid, bool $quarantine = false ): int {
    global $db;

    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    $query = $db->query( "SELECT COUNT(*) FROM $table WHERE pid = $pid AND parentid = 0" );
    $count = (int) $db->result( $query, 0 );
    $db->free_result($query);

    return $count;
}

return;