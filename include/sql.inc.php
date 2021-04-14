<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2021, The XMB Group
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
 * @param array $values Field name & value list.
 * @return int Member ID number.
 */
function addMember( array $values ): int {
    global $db;

    // Defaults:
    if ( ! isset( $values['bday'] ) ) $values['bday'] = '0000-00-00';
    if ( ! isset( $values['invisible'] ) ) $values['invisible'] = '0';
    if ( ! isset( $values['sub_each_post'] ) ) $values['sub_each_post'] = 'no';
    if ( ! isset( $values['waiting_for_mod'] ) ) $values['waiting_for_mod'] = 'no';

    // Required values:
    $req = [ 'username', 'password', 'email', 'status', 'regip', 'regdate' ];

    // Types:
    $ints = [ 'regdate', 'postnum', 'theme', 'tpp', 'ppp', 'timeformat', 'lastvisit', 'pwdate', 'u2ualert', 'bad_login_date',
    'bad_login_count', 'bad_session_date', 'bad_session_count' ];

    $numerics = [ 'timeoffset' ];

    $strings = [ 'username', 'password', 'email', 'site', 'aim', 'status', 'location', 'bio', 'sig', 'showemail', 'icq', 'avatar',
    'yahoo', 'customstatus', 'bday', 'langfile', 'newsletter', 'regip', 'msn', 'ban', 'dateformat', 'ignoreu2u', 'mood', 'invisible',
    'u2ufolders', 'saveogu2u', 'emailonu2u', 'useoldu2u', 'sub_each_post', 'waiting_for_mod' ];

    $sql = [];

    foreach( $req as $field ) if ( ! isset( $values[$field] ) ) trigger_error( "Missing value $field for \XMB\SQL\addMember()", E_USER_ERROR );
    foreach( $ints as $field ) {
        if ( isset( $values[$field] ) ) {
            if ( ! is_int( $values[$field] ) ) trigger_error( "Type mismatch in $field for \XMB\SQL\addMember()", E_USER_ERROR );
        } else {
            $values[$field] = 0;
        }
        $sql[] = "$field = {$values[$field]}";
    }
    foreach( $numerics as $field ) {
        if ( isset( $values[$field] ) ) {
            if ( ! is_numeric( $values[$field] ) ) trigger_error( "Type mismatch in $field for \XMB\SQL\addMember()", E_USER_ERROR );
        } else {
            $values[$field] = 0;
        }
        $sql[] = "$field = {$values[$field]}";
    }
    foreach( $strings as $field ) {
        if ( isset( $values[$field] ) ) {
            if ( ! is_string( $values[$field] ) ) trigger_error( "Type mismatch in $field for \XMB\SQL\addMember()", E_USER_ERROR );
            $db->escape_fast( $values[$field] );
        } else {
            $values[$field] = '';
        }
        $sql[] = "$field = '{$values[$field]}'";
    }
    
    $db->query("INSERT INTO ".X_PREFIX."members SET " . implode( ', ', $sql ));

    return $db->insert_id();
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
function countMembers(): int {
    global $db;

    $query = $db->query( "SELECT COUNT(*) FROM ".X_PREFIX."members" );
    $result = (int) $db->result( $query, 0 );
    $db->free_result( $query );

    return $result;
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
 * @return array List of row arrays.
 */
function getSuperEmails(): array {
    global $db;
    
    $query = $db->query("SELECT username, email, langfile FROM ".X_PREFIX."members WHERE status = 'Super Administrator'");
    
    $result = [];
    while ( $admin = $db->fetch_array( $query ) ) {
        $result[] = $admin;
    }
    $db->free_result( $query );
    
    return $result;
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
function startMemberQuarantine( int $uid ) {
    global $db;
    
    $db->query("UPDATE ".X_PREFIX."members SET waiting_for_mod = 'yes' WHERE uid = $uid");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function endMemberQuarantine( string $username ) {
    global $db;
    
    $sqluser = $db->escape( $username );

    $db->query("UPDATE ".X_PREFIX."members SET waiting_for_mod = 'no' WHERE username = '$sqluser'");
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
function setLastvisit( string $username, int $timestamp ) {
    global $db;
    
    $sqluser = $db->escape( $username );

    $db->query("UPDATE ".X_PREFIX."members SET lastvisit = $timestamp WHERE username = '$sqluser'");
}

/**
 * Increments the user's post total.
 *
 * Also resets the user's lastvisit timestamp because otherwise elevateUser() allows it to be 60 seconds old.
 *
 * @since 1.9.12
 */
function raisePostCount( string $username, int $timestamp ) {
    global $db;
    
    $sqluser = $db->escape( $username );

    $db->query("UPDATE ".X_PREFIX."members SET postnum = postnum + 1, lastvisit = $timestamp WHERE username = '$sqluser'");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function unlockMember( string $username ) {
    global $db;
    
    $sqluser = $db->escape( $username );

    $db->query("UPDATE ".X_PREFIX."members SET bad_login_count = 0 WHERE username = '$sqluser'");
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
 * @since 1.9.12
 * @param array $values Field name & value list. Passed by reference and modified, so don't assign references or re-use the same array.
 * @param bool $quarantine Save this record in a private table for later review?
 * @return int Thread ID number.
 */
function addThread( array &$values, bool $quarantine = false ): int {
    global $db;

    // Required values:
    $req = ['fid', 'author', 'lastpost', 'subject', 'icon'];

    // Optional values:
    // views, replies, topped, pollopts, closed

    // Types:
    $ints = ['fid', 'views', 'replies', 'topped', 'pollopts'];
    $strings = ['author', 'lastpost', 'subject', 'icon', 'closed'];

    foreach( $req as $field ) if ( ! isset( $values[$field] ) ) trigger_error( "Missing value $field for \XMB\SQL\addThread()", E_USER_ERROR );
    foreach( $ints as $field ) {
        if ( isset( $values[$field] ) ) {
            if ( ! is_int( $values[$field] ) ) trigger_error( "Type mismatch in $field for \XMB\SQL\addThread()", E_USER_ERROR );
        } else {
            $values[$field] = 0;
        }
    }
    foreach( $strings as $field ) {
        if ( isset( $values[$field] ) ) {
            if ( ! is_string( $values[$field] ) ) trigger_error( "Type mismatch in $field for \XMB\SQL\addThread()", E_USER_ERROR );
            $db->escape_fast( $values[$field] );
        } else {
            $values[$field] = '';
        }
    }
    
    $table = $quarantine ? X_PREFIX.'hold_threads' : X_PREFIX.'threads';

    $db->query("INSERT INTO $table SET
    fid = {$values['fid']},
    views = {$values['views']},
    replies = {$values['replies']},
    topped = {$values['topped']},
    pollopts = {$values['pollopts']},
    subject = '{$values['subject']}',
    icon = '{$values['icon']}',
    lastpost = '{$values['lastpost']}',
    author = '{$values['author']}',
    closed = '{$values['closed']}'
    ");

    return $db->insert_id();
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function setThreadLastpost( int $tid, string $lastpost, bool $quarantine = false ) {
    global $db;

    $sqllast = $db->escape( $lastpost );

    $table = $quarantine ? X_PREFIX.'hold_threads' : X_PREFIX.'threads';

    $db->query( "UPDATE $table SET lastpost = '$sqllast' WHERE tid = $tid" );
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function countThreadsByUser( string $username, int $fid, bool $quarantine = false ): int {
    global $db;

    $sqluser = $db->escape( $username );

    $table = $quarantine ? X_PREFIX.'hold_threads' : X_PREFIX.'threads';

    $query = $db->query( "SELECT COUNT(*) FROM $table WHERE author = '$sqluser' AND fid = $fid" );
    $result = (int) $db->result( $query, 0 );
    $db->free_result( $query );

    return $result;
}

/**
 * SQL command
 *
 * @since 1.9.12
 * @param array $values Field name & value list. Passed by reference and modified, so don't assign references or re-use the same array.
 * @param bool $quarantine Save this record in a private table for later review?
 * @param bool $qthread When starting a quarantined thread, we need to know not to use the tid field for the post to prevent ID collisions.
 * @return int Post ID number.
 */
function addPost( array &$values, bool $quarantine = false, bool $qthread = false ): int {
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
    $tid_field = $qthread ? 'newtid' : 'tid';

    $db->query("INSERT INTO $table SET
    fid = {$values['fid']},
    $tid_field = {$values['tid']},
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
function savePostBody( int $pid, string $body, bool $quarantine = false ) {
    global $db;

    $sqlbody = $db->escape( $body );

    $table = $quarantine ? X_PREFIX.'hold_posts' : X_PREFIX.'posts';

    $db->query("UPDATE $table SET message = '$sqlbody' WHERE pid = $pid");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function getPostBody( int $pid, bool $quarantine = false ): string {
    global $db;

    $table = $quarantine ? X_PREFIX.'hold_posts' : X_PREFIX.'posts';

    $query = $db->query("SELECT message FROM $table WHERE pid = $pid");
    if ( $db->num_rows( $query ) == 1 ) {
        $result = $db->result( $query, 0 );
    } else {
        $result = '';
    }
    return $result;
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function countPosts( bool $quarantine = false, int $tid = 0, string $username = '' ): int {
    global $db;

    $table = $quarantine ? X_PREFIX.'hold_posts' : X_PREFIX.'posts';

    $where = [];
    if ( $tid != 0 ) {
        $where[] = "tid = $tid";
    }
    if ( $username != '' ) {
        $sqluser = $db->escape( $username );
        $where[] = "author = '$sqluser'";
    }

    if ( empty( $where ) ) {
        $where = '';
    } else {
        $where = "WHERE " . implode( ' AND ', $where );
    }

    $query = $db->query( "SELECT COUNT(*) FROM $table $where" );
    $result = (int) $db->result( $query, 0 );
    $db->free_result( $query );

    return $result;
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
 * @param array $values Field name & value list. Passed by reference and modified. Expects 'attachment' to be assigned by reference for performance.
 * @param bool $quarantine Save this record in a private table for later review?
 * @return int Attachment ID number.
 */
function addAttachment( array &$values, bool $quarantine = false ): int {
    global $db;

    // Required values:
    $req = ['filename', 'filetype', 'filesize', 'subdir', 'uid'];

    // Optional values:
    // pid, attachment, img_size, parentid

    // Types:
    $ints = ['pid', 'parentid', 'uid'];
    $strings = ['filename', 'filetype', 'filesize', 'attachment', 'img_size', 'subdir'];

    foreach( $req as $field ) if ( ! isset( $values[$field] ) ) trigger_error( "Missing value $field for \XMB\SQL\addAttachment()", E_USER_ERROR );
    foreach( $ints as $field ) {
        if ( isset( $values[$field] ) ) {
            if ( ! is_int( $values[$field] ) ) trigger_error( "Type mismatch in $field for \XMB\SQL\addAttachment()", E_USER_ERROR );
        } else {
            $values[$field] = 0;
        }
    }
    foreach( $strings as $field ) {
        if ( isset( $values[$field] ) ) {
            if ( ! is_string( $values[$field] ) ) trigger_error( "Type mismatch in $field for \XMB\SQL\addAttachment()", E_USER_ERROR );
            $db->escape_fast( $values[$field] );
        } else {
            $values[$field] = '';
        }
    }

    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    $db->query("INSERT INTO $table SET
    pid = {$values['pid']},
    parentid = {$values['parentid']},
    uid = {$values['uid']},
    filename = '{$values['filename']}',
    filetype = '{$values['filetype']}',
    filesize = '{$values['filesize']}',
    attachment = '{$values['attachment']}',
    img_size = '{$values['img_size']}',
    subdir = '{$values['subdir']}'
    ");

    return $db->insert_id();
}

/**
 * Copy a quarantined attachment record to the public table.
 *
 * @since 1.9.12
 */
function approveAttachment( int $oldaid, int $newpid, int $newparent ): int {
    global $db;
    
    $db->query(
        "INSERT INTO ".X_PREFIX."attachments " .
        "      (    pid, filename, filetype, filesize, attachment, downloads,   parentid, uid, updatetime, img_size, subdir) " .
        "SELECT $newpid, filename, filetype, filesize, attachment, downloads, $newparent, uid, updatetime, img_size, subdir " .
        "FROM ".X_PREFIX."hold_attachments WHERE aid = $oldaid"
    );

    return $db->insert_id();
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function getAttachment( int $aid, bool $quarantine = false ): array {
    global $db;
    
    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    $query = $db->query("SELECT *, UNIX_TIMESTAMP(updatetime) AS updatestamp FROM $table WHERE aid = $aid");
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
function getAttachmentAndFID( int $aid, bool $quarantine = false, int $pid = 0, string $filename = '', int $uid = 0 ): array {
    global $db;
    
    $table1 = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';
    $table2 = $quarantine ? X_PREFIX.'hold_posts' : X_PREFIX.'posts';
    
    $where = "a.aid = $aid";
    
    if ( $pid != 0 ) {
        $where .= " AND a.pid = $pid";
    }

    if ( $uid != 0 ) {
        $where .= " AND a.uid = $uid";
    }
    
    if ( $filename != '' ) {
        $db->escape_fast( $filename );
        $where .= " AND a.filename = '$filename'";
    }

    $query = $db->query("SELECT a.*, UNIX_TIMESTAMP(a.updatetime) AS updatestamp, p.fid FROM $table1 AS a LEFT JOIN $table2 AS p USING (pid) WHERE $where");
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
function getOrphanedAttachments( int $uid, bool $quarantine = false ): array {
    global $db;
    
    $result = [];
    
    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    $query = $db->query("SELECT a.aid, a.pid, a.filename, a.filetype, a.filesize, a.downloads, a.img_size,
    thumbs.aid AS thumbid, thumbs.filename AS thumbname, thumbs.img_size AS thumbsize
    FROM $table AS a LEFT JOIN $table AS thumbs ON a.aid=thumbs.parentid WHERE a.uid = $uid AND a.pid = 0 AND a.parentid = 0");

    while ( $row = $db->fetch_array( $query ) ) {
        $result[] = $row;
    }
    $db->free_result($query);

    return $result;
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function deleteAttachmentsByID( array $aid_list, bool $quarantine = false ) {
    global $db;

    if ( empty( $aid_list ) ) return;

    $ids = array_map( 'intval', $aid_list );
    $ids = implode( ",", $ids );

    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    $db->query("DELETE FROM $table WHERE aid IN ($ids)");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function getAttachmentPaths( array $aid_list, bool $quarantine = false ) {
    global $db;

    if ( empty( $aid_list ) ) return;

    $ids = array_map( 'intval', $aid_list );
    $ids = implode( ",", $ids );

    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    return $db->query("SELECT aid, subdir FROM $table WHERE aid IN ($ids)");
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function getAttachmentParents( int $pid, bool $quarantine = false ): array {
    global $db;

    $results = [];

    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    $query = $db->query("SELECT aid, filesize, parentid FROM $table WHERE pid = $pid ORDER BY parentid");
    while( $row = $db->fetch_array( $query ) ) {
        $results[] = $row;
    }
    $db->free_result( $query );
    
    return $results;
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function claimOrphanedAttachments( int $pid, int $uid, bool $quarantine = false ) {
    global $db;

    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    $db->query("UPDATE $table SET pid = $pid WHERE pid = 0 AND uid = $uid");
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

/**
 * SQL command
 *
 * @since 1.9.12
 */
function countThumbnails( int $aid, bool $quarantine = false ): int {
    global $db;

    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    $query = $db->query( "SELECT COUNT(*) FROM $table WHERE parentid = $aid AND filename LIKE '%-thumb.jpg'" );
    $count = (int) $db->result( $query, 0 );
    $db->free_result($query);

    return $count;
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function getAttachmentChildIDs( int $aid, bool $thumbnails_only, bool $quarantine = false ): array {
    global $db;

    $result = [];

    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';
    
    if ( $thumbnails_only ) {
        $where = "AND filename LIKE '%-thumb.jpg'";
    } else {
        $where = '';
    }

    $query = $db->query( "SELECT aid FROM $table WHERE parentid = $aid $where" );
    while ( $row = $db->fetch_array( $query ) ) {
        $result[] = $row['aid'];
    }
    $db->free_result($query);

    return $result;
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function getOrphanedAttachmentIDs( int $uid, bool $quarantine = false ): array {
    global $db;

    $result = [];

    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    $query = $db->query( "SELECT aid FROM $table WHERE pid = 0 AND parentid = 0 AND uid = $uid" );
    while ( $row = $db->fetch_array( $query ) ) {
        $result[] = $row['aid'];
    }
    $db->free_result($query);

    return $result;
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function getAttachmentIDsByPost( int $pid, bool $include_children, bool $quarantine = false ): array {
    global $db;

    $result = [];

    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    if ( $include_children ) {
        $where = '';
    } else {
        $where = 'AND parentid = 0';
    }

    $query = $db->query( "SELECT aid FROM $table WHERE pid = $pid $where" );
    while ( $row = $db->fetch_array( $query ) ) {
        $result[] = $row['aid'];
    }
    $db->free_result($query);

    return $result;
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function getAttachmentIDsByThread( array $tid_list, bool $quarantine = false, int $notpid = 0 ): array {
    global $db;

    $result = [];

    if ( empty( $tid_list ) ) return $result;

    $ids = array_map( 'intval', $tid_list );
    $ids = implode( ",", $ids );

    $table1 = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';
    $table2 = $quarantine ? X_PREFIX.'hold_posts' : X_PREFIX.'posts';
    
    if ( 0 == $notpid ) {
        $where = '';
    } else {
        $where = "AND p.pid != $notpid";
    }

    $query = $db->query( "SELECT a.aid FROM $table1 AS a INNER JOIN $table2 AS p USING (pid) WHERE p.tid IN ($ids) $where" );
    while ( $row = $db->fetch_array( $query ) ) {
        $result[] = $row['aid'];
    }
    $db->free_result($query);

    return $result;
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function getAttachmentIDsByUser( string $username, bool $quarantine = false ): array {
    global $db;

    $sqluser = $db->escape( $username );

    $result = [];

    $table1 = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';
    $table2 = $quarantine ? X_PREFIX.'hold_posts' : X_PREFIX.'posts';
    
    $query = $db->query( "SELECT aid FROM $table1 INNER JOIN $table2 USING (pid) WHERE author = '$sqluser'" );
    while ( $row = $db->fetch_array( $query ) ) {
        $result[] = $row['aid'];
    }
    $db->free_result($query);

    $query = $db->query( "SELECT aid FROM $table1 INNER JOIN ".X_PREFIX."members USING (uid) WHERE username = '$sqluser'" );
    while ( $row = $db->fetch_array( $query ) ) {
        $result[] = $row['aid'];
    }
    $db->free_result($query);

    return array_unique( $result );
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function renameAttachment( int $aid, string $name, bool $quarantine = false ) {
    global $db;

    $sqlname = $db->escape( $name );

    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    $db->query( "UPDATE $table SET filename='$sqlname' WHERE aid = $aid" );
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function setImageDims( int $aid, string $img_size, bool $quarantine = false ) {
    global $db;

    $sqlsize = $db->escape( $img_size );

    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    $db->query( "UPDATE $table SET img_size='$sqlsize' WHERE aid = $aid" );
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function raiseDownloadCounter( int $aid, bool $quarantine = false ) {
    global $db;

    $table = $quarantine ? X_PREFIX.'hold_attachments' : X_PREFIX.'attachments';

    $db->query( "UPDATE $table SET downloads = downloads + 1 WHERE aid = $aid" );
}

/**
 * SQL command
 *
 * @since 1.9.12
 */
function addVoteDesc( int $tid, bool $quarantine = false ): int {
    global $db;

    $sqltext = $db->escape( $text );

    $table = $quarantine ? X_PREFIX.'hold_vote_desc' : X_PREFIX.'vote_desc';

    $db->query( "INSERT INTO $table SET topic_id = $tid" );

    return $db->insert_id();
}

/**
 * SQL command
 *
 * @since 1.9.12
 * @param array $rows Must be an array of arrays representing rows, then values associated to field names.
 * @param bool $quarantine Save these records in a private table for later review?
 */
function addVoteOptions( array $rows, bool $quarantine = false ) {
    global $db;

    if ( empty( $rows ) ) return;

    $sqlrows = [];

    // Required values:
    $req = ['vote_id', 'vote_option_id', 'vote_option_text'];

    // Optional values:
    // vote_result

    // Types:
    $ints = ['vote_id', 'vote_option_id', 'vote_result'];
    $strings = ['vote_option_text'];

    foreach( $rows as $values ) {
        foreach( $req as $field ) if ( ! isset( $values[$field] ) ) trigger_error( "Missing value $field for \XMB\SQL\addVoteOptions()", E_USER_ERROR );
        foreach( $ints as $field ) {
            if ( isset( $values[$field] ) ) {
                if ( ! is_int( $values[$field] ) ) trigger_error( "Type mismatch in $field for \XMB\SQL\addVoteOptions()", E_USER_ERROR );
            } else {
                $values[$field] = 0;
            }
        }
        foreach( $strings as $field ) {
            if ( isset( $values[$field] ) ) {
                if ( ! is_string( $values[$field] ) ) trigger_error( "Type mismatch in $field for \XMB\SQL\addVoteOptions()", E_USER_ERROR );
                $db->escape_fast( $values[$field] );
            } else {
                $values[$field] = '';
            }
        }
        $sqlrows[] = "( {$values['vote_id']}, {$values['vote_option_id']}, '{$values['vote_option_text']}', {$values['vote_result']} )";
    }
    $sqlrows = implode( ',', $sqlrows );
    
    $table = $quarantine ? X_PREFIX.'hold_vote_results' : X_PREFIX.'vote_results';

    $db->query("INSERT INTO $table (vote_id, vote_option_id, vote_option_text, vote_result) VALUES $sqlrows");
}

return;