<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-3
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

use InvalidArgumentException;
use LogicException;

class SQL
{
    public function __construct(public readonly DBStuff $db, private string $tablepre)
    {
        // Property promotion.
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function saveSession(string $token, string $username, int $date, int $expire, int $regenerate, string $replace, string $agent, ?string $comment = null): bool
    {
        $this->db->escape_fast($token);
        $this->db->escape_fast($username);
        $this->db->escape_fast($replace);
        $this->db->escape_fast($agent);
        $extra = '';
        if (! is_null($comment)) {
            $this->db->escape_fast($comment);
            $extra .= ", name = '$comment'";
        }

        $this->db->query("INSERT IGNORE INTO " . $this->tablepre . "sessions SET token = '$token', username = '$username', login_date = $date,
            expire = $expire, regenerate = $regenerate, replaces = '$replace', agent = '$agent' $extra");

        return ($this->db->affected_rows() == 1);
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function getSession(string $token, string $username): array
    {
        $sqltoken = $this->db->escape($token);
        $sqluser = $this->db->escape($username);

        $query = $this->db->query("SELECT * FROM " . $this->tablepre . "sessions WHERE token = '$sqltoken' AND username = '$sqluser'");
        if ($this->db->num_rows($query) == 1) {
            $session = $this->db->fetch_array($query);
        } else {
            $session = [];
        }
        $this->db->free_result($query);
        return $session;
    }

    /**
     * Retrieves all sessions stored for the specified user.
     *
     * Results are filtered to tokens that are not in a state of regeneration.
     * This avoids duplicates for display purposes, but could omit a session if this query races the last update of the regeneration cycle.
     *
     * @since 1.9.12
     * @return array
     */
    public function getSessionsByName(string $username): array
    {
        $this->db->escape_fast($username);

        $result = $this->db->query("SELECT * FROM " . $this->tablepre . "sessions WHERE username = '$username' AND replaces = ''");

        $rows = $this->db->fetch_all($result);
        $this->db->free_result($result);

        return $rows;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function deleteSession(string $token)
    {
        $sqltoken = $this->db->escape($token);

        $this->db->query("DELETE FROM " . $this->tablepre . "sessions WHERE token = '$sqltoken'");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function deleteSessionsByName(string $username)
    {
        $sqluser = $this->db->escape($username);

        $this->db->query("DELETE FROM " . $this->tablepre . "sessions WHERE username = '$sqluser'");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function deleteSessionsByDate(int $expired)
    {
        $this->db->query("DELETE FROM " . $this->tablepre . "sessions WHERE expire < $expired");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function deleteSessionsByList(string $username, array $ids, string $current_token)
    {
        if (empty($ids)) return;

        $sqluser = $this->db->escape($username);
        $sqltoken = $this->db->escape($current_token);
        $ids = array_map([$this->db, 'escape'], $ids);
        $ids = "'" . implode("','", $ids) . "'";

        $this->db->query("DELETE FROM " . $this->tablepre . "sessions WHERE username = '$sqluser' AND LEFT(token, 4) IN ($ids) AND token != '$sqltoken' AND replaces != '$sqltoken'");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function clearSessionParent(string $token)
    {
        $sqltoken = $this->db->escape($token);

        $this->db->query("UPDATE " . $this->tablepre . "sessions SET replaces = '' WHERE token = '$sqltoken'");
    }

    /**
     * Same as calling all three methods: deleteSession() and clearSessionParent() and deleteSessionReplacements()
     *
     * This method will send the queries at the same time to reduce the chance of races with other requests.
     *
     * @since 1.10.00
     */
    public function deleteParentSession(string $oldToken, string $newToken)
    {
        $this->db->escape_fast($oldToken);
        $this->db->escape_fast($newToken);

        $sql = [
            "DELETE FROM " . $this->tablepre . "sessions WHERE token = '$oldToken'", // Delete old token
            "UPDATE " . $this->tablepre . "sessions SET replaces = '' WHERE token = '$newToken'", // Make replacement permanent
            "DELETE FROM " . $this->tablepre . "sessions WHERE replaces = '$oldToken'", // Cleanup any orphans
        ];

        $this->db->multi_query($sql);
        $this->db->next_query();
        $this->db->next_query();
    }

    /**
     * SQL command
     *
     * @since 1.9.12 Formerly getSessionReplacement()
     * @since 1.10.00
     * @return array Records of all available replacement tokens.
     */
    public function getSessionReplacements(string $token, string $username): array
    {
        $this->db->escape_fast($token);
        $this->db->escape_fast($username);

        $result = $this->db->query("SELECT * FROM " . $this->tablepre . "sessions WHERE replaces = '$token' AND username = '$username'");
        $rows = $this->db->fetch_all($result);
        $this->db->free_result($result);

        return $rows;
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     */
    public function deleteSessionReplacements(string $token, ?string $except = null)
    {
        $this->db->escape_fast($token);

        $extra = '';
        if (! is_null($except)) {
            $this->db->escape_fast($except);
            $extra = "AND token != '$except'";
        }

        $this->db->query("DELETE FROM " . $this->tablepre . "sessions WHERE replaces = '$token' $extra");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     * @param array $values Field name & value list.
     * @return int Member ID number.
     */
    public function addMember(array $values): int
    {
        // Defaults:
        if (! isset($values['bday'])) $values['bday'] = '0000-00-00';
        if (! isset($values['invisible'])) $values['invisible'] = '0';
        if (! isset($values['sub_each_post'])) $values['sub_each_post'] = 'no';
        if (! isset($values['waiting_for_mod'])) $values['waiting_for_mod'] = 'no';

        // Required values:
        $req = ['username', 'password2', 'email', 'status', 'regip', 'regdate'];

        // Types:
        $ints = ['regdate', 'postnum', 'theme', 'tpp', 'ppp', 'timeformat', 'lastvisit', 'pwdate', 'u2ualert', 'bad_login_date',
        'bad_login_count', 'bad_session_date', 'bad_session_count'];

        $numerics = ['timeoffset'];

        $strings = ['username', 'password', 'email', 'site', 'status', 'location', 'bio', 'sig', 'avatar',
        'customstatus', 'bday', 'langfile', 'newsletter', 'regip', 'ban', 'dateformat', 'ignoreu2u', 'mood', 'invisible',
        'u2ufolders', 'saveogu2u', 'emailonu2u', 'sub_each_post', 'waiting_for_mod', 'password2'];

        $sql = [];

        foreach ($req as $field) if (! isset($values[$field])) throw new LogicException("Missing $field");
        foreach ($ints as $field) {
            if (isset($values[$field])) {
                if (! is_int($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
            } else {
                $values[$field] = 0;
            }
            $sql[] = "$field = {$values[$field]}";
        }
        foreach ($numerics as $field) {
            if (isset($values[$field])) {
                if (! is_numeric($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
            } else {
                $values[$field] = 0;
            }
            $sql[] = "$field = {$values[$field]}";
        }
        foreach ($strings as $field) {
            if (isset($values[$field])) {
                if (! is_string($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
                $this->db->escape_fast($values[$field]);
            } else {
                $values[$field] = '';
            }
            $sql[] = "$field = '{$values[$field]}'";
        }

        $this->db->query("INSERT INTO " . $this->tablepre . "members SET " . implode(', ', $sql));

        return $this->db->insert_id();
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     * @param int $uid
     * @param array $values Field name & value list.
     */
    public function updateMember(int $uid, array $values)
    {
        // Types:
        $ints = ['regdate', 'postnum', 'theme', 'tpp', 'ppp', 'timeformat', 'lastvisit', 'pwdate', 'u2ualert', 'bad_login_date',
        'bad_login_count', 'bad_session_date', 'bad_session_count'];

        $numerics = ['timeoffset'];

        $strings = ['password', 'email', 'site', 'status', 'location', 'bio', 'sig', 'avatar',
        'customstatus', 'bday', 'langfile', 'newsletter', 'regip', 'ban', 'dateformat', 'ignoreu2u', 'mood', 'invisible',
        'u2ufolders', 'saveogu2u', 'emailonu2u', 'sub_each_post', 'waiting_for_mod', 'password2'];

        $sql = [];

        foreach ($ints as $field) {
            if (isset($values[$field])) {
                if (! is_int($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
                $sql[] = "$field = {$values[$field]}";
            }
        }
        foreach ($numerics as $field) {
            if (isset($values[$field])) {
                if (! is_numeric($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
                $sql[] = "$field = {$values[$field]}";
            }
        }
        foreach ($strings as $field) {
            if (isset($values[$field])) {
                if (! is_string($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
                $this->db->escape_fast($values[$field]);
                $sql[] = "$field = '{$values[$field]}'";
            }
        }

        if (count($sql) == 0) throw new InvalidArgumentException('No valid values were provided');

        $this->db->query("UPDATE " . $this->tablepre . "members SET " . implode(', ', $sql) . " WHERE uid = $uid");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     * @param string $username Must be HTML encoded.
     * @param string $email Optional.
     * @param bool $includePassword Optional. Passwords are not returned unless set to true.
     * @return array Member record or empty array.
     */
    public function getMemberByName(string $username, ?string $email = null, bool $includePassword = false): array
    {
        $this->db->escape_fast($username);

        if (! is_null($email)) {
            $this->db->escape_fast($email);
            $extra = "AND email = '$email'";
        } else {
            $extra = '';
        }

        $query = $this->db->query("SELECT * FROM " . $this->tablepre . "members WHERE username = '$username' $extra");
        if ($this->db->num_rows($query) == 1) {
            $member = $this->db->fetch_array($query);
        } else {
            $member = [];
        }
        $this->db->free_result($query);

        if (! $includePassword) {
            unset($member['password'], $member['password2']);
        }

        return $member;
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     * @param int $uid
     * @param bool $includePassword Optional. Passwords are not returned unless set to true.
     * @return array Member record or empty array.
     */
    public function getMemberByID(int $uid, bool $includePassword = false): array
    {
        $query = $this->db->query("SELECT * FROM " . $this->tablepre . "members WHERE uid = $uid");
        $member = $this->db->fetch_array($query);
        $this->db->free_result($query);

        if (is_null($member)) {
            $member = [];
        } elseif (! $includePassword) {
            unset($member['password'], $member['password2']);
        }

        return $member;
    }

    /**
     * SQL command
     *
     * @since 1.9.12.06
     * @param int $uid
     * @param string $invisible Should be '0' for no or '1' for yes.
     */
    public function changeMemberVisibility(int $uid, string $invisible)
    {
        $this->db->escape_fast($invisible);

        // The members.invisible field is a SET type and must be sent as a string.
        // Otherwise, MySQL would coerce integer literals to a bit set value rather than a string value.

        $this->db->query("UPDATE " . $this->tablepre . "members SET invisible = '$invisible' WHERE uid = $uid");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function countMembers(): int
    {
        $query = $this->db->query("SELECT COUNT(*) FROM " . $this->tablepre . "members");
        $result = (int) $this->db->result($query);
        $this->db->free_result($query);

        return $result;
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     */
    public function countMembersByRegIP(string $address, int $time = 0): int
    {
        $this->db->escape_fast($address);
        if ($time != 0) {
            $extra = " AND regdate >= $time";
        } else {
            $extra = '';
        }

        $query = $this->db->query("SELECT COUNT(*) FROM " . $this->tablepre . "members WHERE regip = '$address' $extra");
        $result = (int) $this->db->result($query);
        $this->db->free_result($query);

        return $result;
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     */
    public function countSuperAdmins(): int
    {
        $result = $this->db->query("SELECT COUNT(*) FROM " . $this->tablepre . "members WHERE status = 'Super Administrator'");
        $count = (int) $this->db->result($result);
        $this->db->free_result($result);

        return $count;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     * @param int $uid
     * @return int The new value of bad_login_count for this member.
     */
    public function raiseLoginCounter(int $uid): int
    {
        $this->db->query("UPDATE " . $this->tablepre . "members SET bad_login_count = LAST_INSERT_ID(bad_login_count + 1) WHERE uid = $uid");

        return $this->db->insert_id();
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function resetLoginCounter(int $uid, int $date)
    {
        $this->db->query("UPDATE " . $this->tablepre . "members SET bad_login_count = 1, bad_login_date = $date WHERE uid = $uid");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     * @param int $uid
     * @return int The new value of bad_session_count for this member.
     */
    public function raiseSessionCounter(int $uid): int
    {
        $this->db->query("UPDATE " . $this->tablepre . "members SET bad_session_count = LAST_INSERT_ID(bad_session_count + 1) WHERE uid = $uid");

        return $this->db->insert_id();
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function resetSessionCounter(int $uid, int $date)
    {
        $this->db->query("UPDATE " . $this->tablepre . "members SET bad_session_count = 1, bad_session_date = $date WHERE uid = $uid");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     * @return array List of row arrays.
     */
    public function getSuperEmails(): array
    {
        $query = $this->db->query("SELECT username, email, langfile FROM " . $this->tablepre . "members WHERE status = 'Super Administrator'");

        $result = $this->db->fetch_all($query);
        $this->db->free_result($query);

        return $result;
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     * @return array List of usernames.
     */
    public function getStaffNames(): array
    {
        $result = $this->db->query("SELECT username FROM " . $this->tablepre . "members WHERE status IN ('Moderator', 'Super Moderator', 'Super Administrator', 'Administrator')");

        $rows = $this->db->fetch_all($result);
        $this->db->free_result($result);

        return array_column($rows, 'username');
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function checkUpgradeOldLogin(string $username, string $password): bool
    {
        $sqlpass = $this->db->escape($password);
        $sqluser = $this->db->escape($username);

        $query = $this->db->query("SELECT COUNT(*) FROM " . $this->tablepre . "members WHERE username = '$sqluser' AND password = '$sqlpass' AND status = 'Super Administrator'");
        $count = (int) $this->db->result($query);
        $this->db->free_result($query);

        return $count == 1;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function setLostPasswordDate(int $uid, int $date)
    {
        $this->db->query("UPDATE " . $this->tablepre . "members SET pwdate = $date WHERE uid = $uid");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function startMemberQuarantine(int $uid)
    {
        $this->db->query("UPDATE " . $this->tablepre . "members SET waiting_for_mod = 'yes' WHERE uid = $uid");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function endMemberQuarantine(string $username)
    {
        $sqluser = $this->db->escape($username);

        $this->db->query("UPDATE " . $this->tablepre . "members SET waiting_for_mod = 'no' WHERE username = '$sqluser'");
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     */
    public function getMemberPassword(int $uid): string
    {
        $result = $this->db->query("SELECT password, password2 FROM " . $this->tablepre . "members WHERE uid = $uid");

        if ($this->db->num_rows($result) != 1) throw new LogicException('Attempted to read password for a non-existent member');

        $record = $this->db->fetch_array($result);
        $this->db->free_result($result);
        
        $password = $record['password'] !== '' ? $record['password'] : $record['password2'];
        
        return $password;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function setNewPassword(string $username, string $password)
    {
        $this->db->escape_fast($password);
        $this->db->escape_fast($username);

        $this->db->query("UPDATE " . $this->tablepre . "members SET password2 = '$password', password = '', bad_login_count = 0 WHERE username = '$username'");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function setLastvisit(int $uid, int $timestamp)
    {
        $this->db->query("UPDATE " . $this->tablepre . "members SET lastvisit = $timestamp WHERE uid = $uid");
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     */
    public function setMemberPostDate(int $uid, int $timestamp)
    {
        $this->db->query("UPDATE " . $this->tablepre . "members SET post_date = $timestamp, lastvisit = $timestamp WHERE uid = $uid");
    }

    /**
     * Increments the user's post total.
     *
     * Also resets the user's lastvisit timestamp because otherwise elevateUser() allows it to be 60 seconds old.
     *
     * @since 1.9.12
     */
    public function raisePostCount(int $uid, int $timestamp)
    {
        $this->db->query("UPDATE " . $this->tablepre . "members SET postnum = postnum + 1, lastvisit = $timestamp, post_date = $timestamp WHERE uid = $uid");
    }

    /**
     * Adjust the user's post total.
     *
     * @since 1.10.00
     */
    public function adjustPostCount(string $username, int $change)
    {
        $this->db->escape_fast($username);
        $this->db->query("UPDATE " . $this->tablepre . "members SET postnum = postnum + $change WHERE username = '$username'");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function unlockMember(int $uid)
    {
        $this->db->query("UPDATE " . $this->tablepre . "members SET bad_login_count = 0 WHERE uid = $uid");
    }

    /**
     * Update all members using as few queries as possible.
     *
     * @since 1.10.00
     */
    public function fixAllMemberCounts()
    {
        $this->db->query("UPDATE " . $this->tablepre . "members AS m
            LEFT JOIN (SELECT author, COUNT(*) as pcount FROM " . $this->tablepre . "posts GROUP BY author) AS query2 ON m.username = query2.author
            SET m.postnum = IFNULL(query2.pcount, 0)
        ");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function addSetting(string $name, string $value)
    {
        $sqlname = $this->db->escape($name);
        $sqlvalue = $this->db->escape($value);

        $this->db->query("INSERT INTO " . $this->tablepre . "settings SET name = '$sqlname', value = '$sqlvalue' ON DUPLICATE KEY UPDATE value = '$sqlvalue' ");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function updateSetting(string $name, string $value)
    {
        $sqlname = $this->db->escape($name);
        $sqlvalue = $this->db->escape($value);

        $this->db->query("UPDATE " . $this->tablepre . "settings SET value = '$sqlvalue' WHERE name = '$sqlname'");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function deleteSetting(string $name)
    {
        $sqlname = $this->db->escape($name);

        $this->db->query("DELETE FROM " . $this->tablepre . "settings WHERE name = '$sqlname'");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function getTemplateByID(int $id): array
    {
        $query = $this->db->query("SELECT * FROM " . $this->tablepre . "templates WHERE id = $id");
        if ($this->db->num_rows($query) == 1) {
            $result = $this->db->fetch_array($query);
        } else {
            $result = [];
        }
        $this->db->free_result($query);

        return $result;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function getThemeByID(int $id): array
    {
        $query = $this->db->query("SELECT * FROM " . $this->tablepre . "themes WHERE themeid = $id");
        if ($this->db->num_rows($query) == 1) {
            $result = $this->db->fetch_array($query);
        } else {
            $result = [];
        }
        $this->db->free_result($query);

        return $result;
    }

    /**
     * Retrieve the list of all themes.
     *
     * @since 1.10.00
     * @return array
     */
    public function getThemeNames(): array
    {
        $query = $this->db->query("SELECT themeid, name FROM " . $this->tablepre . "themes ORDER BY name ASC");

        $result = $this->db->fetch_all($query);
        $this->db->free_result($query);

        return $result;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function raiseThemeVersions()
    {
        $this->db->query("UPDATE " . $this->tablepre . "themes SET version = version + 1");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function addToken(string $token, string $username, string $action, string $object, int $expire): bool
    {
        $sqltoken = $this->db->escape($token);
        $sqluser = $this->db->escape($username);
        $sqlaction = $this->db->escape($action);
        $sqlobject = $this->db->escape($object);

        $this->db->query("INSERT IGNORE INTO " . $this->tablepre . "tokens SET token = '$sqltoken', username = '$sqluser', action = '$sqlaction', object = '$sqlobject', expire = $expire ");

        return ($this->db->affected_rows() == 1);
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function deleteToken(string $token, string $username, string $action, string $object): bool
    {
        $sqltoken = $this->db->escape($token);
        $sqluser = $this->db->escape($username);
        $sqlaction = $this->db->escape($action);
        $sqlobject = $this->db->escape($object);

        $this->db->query("DELETE FROM " . $this->tablepre . "tokens WHERE token = '$sqltoken' AND username = '$sqluser' AND action = '$sqlaction' AND object = '$sqlobject'");

        return ($this->db->affected_rows() == 1);
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function deleteTokensByDate(int $expire)
    {
        $this->db->query("DELETE FROM " . $this->tablepre . "tokens WHERE expire < $expire");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     * @param array $values Field name & value list.
     * @param bool $quarantine Save this record in a private table for later review?
     * @return int Thread ID number.
     */
    public function addThread(array $values, bool $quarantine = false): int
    {
        // Required values:
        $req = ['fid', 'author', 'lastpost', 'subject', 'icon'];

        // Optional values:
        // views, replies, topped, pollopts, closed

        // Types:
        $ints = ['fid', 'views', 'replies', 'topped', 'pollopts'];
        $strings = ['author', 'lastpost', 'subject', 'icon', 'closed'];

        foreach ($req as $field) if (! isset($values[$field])) throw new LogicException("Missing $field");
        foreach ($ints as $field) {
            if (isset($values[$field])) {
                if (! is_int($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
            } else {
                $values[$field] = 0;
            }
        }
        foreach ($strings as $field) {
            if (isset($values[$field])) {
                if (! is_string($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
                $this->db->escape_fast($values[$field]);
            } else {
                $values[$field] = '';
            }
        }

        $table = $quarantine ? $this->tablepre . 'hold_threads' : $this->tablepre . 'threads';

        $this->db->query("INSERT INTO $table SET
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

        return $this->db->insert_id();
    }

    /**
     * Update the lastpost value for a thread.
     *
     * @since 1.9.12
     * @param int $tid The existing thread ID.
     * @param string $lastpost The new value for lastpost.
     * @param bool $quarantine Save this record in a private table for later review?
     * @param bool $newReply Need to increment the reply count?  Not intended for use with the quarantine system.
     * @param bool $close Set the thread status to closed while updating.
     */
    public function setThreadLastpost(int $tid, string $lastpost, bool $quarantine = false, bool $newReply = false, bool $close = false)
    {
        $this->db->escape_fast($lastpost);

        $table = $quarantine ? $this->tablepre . 'hold_threads' : $this->tablepre . 'threads';

        $more = '';
        $more .= $newReply ? 'replies = replies + 1, ' : '';
        $more .= $close ? 'closed = "yes", ' : '';

        $this->db->query("UPDATE $table SET $more lastpost = '$lastpost' WHERE tid = $tid");
    }

    /**
     * Find the most recent lastpost value among all threads in the given forum.
     *
     * @since 1.9.12.06 Previously named findLaspostByForum
     * @since 1.10.00
     * @param int $fid
     */
    public function findLastpostByForum(int $fid): string
    {
        $query = $this->db->query("SELECT t.lastpost
            FROM " . $this->tablepre . "forums AS f
            LEFT JOIN " . $this->tablepre . "threads AS t USING (fid)
            WHERE f.fid = $fid OR f.fup = $fid
            ORDER BY t.lastpost DESC
            LIMIT 1
        ");

        if ($this->db->num_rows($query) === 0) {
            $result = ''; // Forum not found.
        } else {
            $result = $this->db->result($query);
            if (null === $result) $result = ''; // Forum is empty.
        }
        $this->db->free_result($query);

        return $result;
    }

    /**
     * Check the thread and post totals of a forum using as few queries as possible.
     *
     * @since 1.10.00
     */
    public function getForumCounts(int $fid): array
    {
        $query = $this->db->query("
            SELECT COUNT(*) FROM " . $this->tablepre . "forums AS f
            INNER JOIN " . $this->tablepre . "posts USING(fid)
            WHERE f.fid = $fid OR f.fup = $fid
            UNION ALL
            SELECT COUNT(*) FROM " . $this->tablepre . "forums AS f
            INNER JOIN " . $this->tablepre . "threads USING(fid)
            WHERE f.fid = $fid OR f.fup = $fid
        ");
        
        $results = [
            'posts' => (int) $this->db->result($query, 0),
            'threads' => (int) $this->db->result($query, 1),
        ];
        $this->db->free_result($query);

        return $results;
    }

    /**
     * SQL command
     *
     * @since 1.9.12.06
     * @param int $fid
     * @param string $lastpost
     * @param int $postcount Optional.
     * @param int $threadcount Optional.
     * @param int $oldThreadCount Optional.  Specify the last-seen value of forums.threads to help avoid update races.
     * @param int $fup Optional.  Specify a 2nd fid value, usually when the forum's parent needs to be updated at the same time.
     * @param bool $newReply Optional.  Need to increment the posts count?  Ignored if postcount arg supplied.
     * @param bool $newThread Optional.  Need to increment the threads count?  Ignored if threadcount arg supplied.
     */
    public function setForumCounts(
        int $fid,
        string $lastpost,
        ?int $postcount = null,
        ?int $threadcount = null,
        ?int $oldThreadCount = null,
        ?int $fup = null,
        ?bool $newReply = false,
        ?bool $newThread = false,
    ) {
        $this->db->escape_fast($lastpost);

        $counts = '';
        $where = "WHERE fid = $fid";

        if (is_null($postcount)) {
            if ($newReply) $counts .= 'posts = posts + 1, ';
        } else {
            $counts .= "posts = $postcount, ";
        }
        if (is_null($threadcount)) {
            if ($newThread) $counts .= 'threads = threads + 1, ';
        } else {
            $counts .= "threads = $threadcount, ";
        }
        if (! is_null($oldThreadCount)) $where .= " AND threads = $oldThreadCount";
        if (! is_null($fup)) $where .= " OR fid = $fup";

        $this->db->query("UPDATE " . $this->tablepre . "forums SET $counts lastpost = '$lastpost' $where");
    }

    /**
     * Update all forum stats using as few queries as possible.
     *
     * @since 1.10.00
     */
    public function fixAllForumCounts()
    {
        // Update the subforums
        $this->db->query("UPDATE " . $this->tablepre . "forums AS f
            LEFT JOIN (SELECT fid, COUNT(*) AS tcount FROM " . $this->tablepre . "threads GROUP BY fid) AS query2 ON f.fid=query2.fid
            LEFT JOIN (SELECT fid, COUNT(*) AS pcount FROM " . $this->tablepre . "posts GROUP BY fid) AS query3 ON f.fid=query3.fid
            SET f.threads = IFNULL(query2.tcount, 0), f.posts = IFNULL(query3.pcount, 0)
            WHERE f.type = 'sub'
        ");

        // Update the primary forums
        $this->db->query("UPDATE " . $this->tablepre . "forums AS f
            LEFT JOIN (SELECT fup, SUM(threads) AS tcount, SUM(posts) AS pcount FROM " . $this->tablepre . "forums GROUP BY fup) AS query2 ON f.fid=query2.fup
            LEFT JOIN (SELECT fid, COUNT(*) AS tcount FROM " . $this->tablepre . "threads GROUP BY fid) AS query3 ON f.fid=query3.fid
            LEFT JOIN (SELECT fid, COUNT(*) AS pcount FROM " . $this->tablepre . "posts GROUP BY fid) AS query4 ON f.fid=query4.fid
            SET f.threads = IFNULL(query2.tcount, 0) + IFNULL(query3.tcount, 0),
                f.posts   = IFNULL(query2.pcount, 0) + IFNULL(query4.pcount, 0)
            WHERE f.type = 'forum'
        ");
    }

    /**
     * Gather pid and date from the latest or bumped post in each forum using as few queries as possible.
     *
     * @since 1.10.00
     */
    public function getLatestPostForAllForums(): array
    {
        $sql = 'SELECT f.fid, f.fup, f.type, f.lastpost, p.author, p.dateline, p.pid, log.username, log.date '
             . 'FROM ' . $this->tablepre . 'forums AS f '
             . 'LEFT JOIN ( '
             . '    SELECT pid, p3.fid, author, dateline FROM ' . $this->tablepre . 'posts AS p3 '
             . '    INNER JOIN ( '
             . '        SELECT p2.fid, MAX(pid) AS lastpid '
             . '        FROM ' . $this->tablepre . 'posts AS p2 '
             . '        INNER JOIN ( '
             . '            SELECT fid, MAX(dateline) AS lastdate '
             . '            FROM ' . $this->tablepre . 'posts '
             . '            GROUP BY fid '
             . '        ) AS query3 ON p2.fid=query3.fid AND p2.dateline=query3.lastdate '
             . '        GROUP BY p2.fid '
             . '    ) AS query2 ON p3.pid=query2.lastpid '
             . ') AS p ON f.fid=p.fid '
             . 'LEFT JOIN ( /* Self-join order is critical with no unique key available */ '
             . '    SELECT log2.fid, log2.date, log2.username '
             . '    FROM ' . $this->tablepre . 'logs AS log2 '
             . '    INNER JOIN ( '
             . '        SELECT fid, MAX(`date`) AS lastdate '
             . '        FROM ' . $this->tablepre . 'logs '
             . '        WHERE `action` = "bump" '
             . '        GROUP BY fid '
             . '    ) AS query4 ON log2.fid=query4.fid AND log2.date=query4.lastdate '
             . ') AS log ON f.fid=log.fid '
             . 'WHERE f.type="forum" OR f.type="sub"';

        $q = $this->db->query($sql);

        // Structure results to accommodate a nested loop strategy.
        $data = [
            'forums' => [],
            'subs' => [],
        ];
        while ($row = $this->db->fetch_array($q)) {
            if ($row['type'] == 'forum') {
                $data['forums'][] = $row;
            } else {
                $data['subs'][] = $row;
            }
        }

        $this->db->free_result($q);

        return $data;
    }

    /**
     * Refresh the date, author, and pid "lastpost" stats for every thread using as few queries as possible.
     *
     * @since 1.10.00
     */
    public function fixLastPostForAllThreads()
    {
        $this->db->query('UPDATE ' . $this->tablepre . 'threads AS t
            LEFT JOIN ' . $this->tablepre . 'posts AS p ON t.tid = p.tid
            INNER JOIN (
                SELECT p2.tid, MAX(pid) AS lastpid
                FROM ' . $this->tablepre . 'posts AS p2
                INNER JOIN (
                    SELECT tid, MAX(dateline) AS lastdate
                    FROM ' . $this->tablepre . 'posts
                    GROUP BY tid
                ) AS query3 ON p2.tid = query3.tid AND p2.dateline = query3.lastdate
                GROUP BY p2.tid
            ) AS query2 ON p.pid = query2.lastpid
            LEFT JOIN ( /* Self-join order is critical with no unique key available */
                SELECT log2.tid, log2.date, log2.username
                FROM ' . $this->tablepre . 'logs AS log2
                INNER JOIN (
                    SELECT tid, MAX(`date`) AS lastdate
                    FROM ' . $this->tablepre . 'logs
                    WHERE `action` = "bump"
                    GROUP BY tid
                ) AS query4 ON log2.tid = query4.tid AND log2.date = query4.lastdate
            ) AS log ON t.tid = log.tid
            SET t.lastpost = IF (p.pid IS NULL,
                "",
                IF (log.date IS NOT NULL AND log.date > p.dateline,
                    CONCAT (log.date, "|", log.username, "|", p.pid),
                    CONCAT (p.dateline, "|", p.author, "|", p.pid)
                )
            )
        ');
    }

    /**
     * Change the fid for every thread having an invalid fid, using as few queries as possible.
     *
     * @since 1.10.00
     */
    public function fixOrphanedThreads(int $newFid): int
    {
        $this->db->query('UPDATE ' . $this->tablepre . 'threads AS t
            LEFT JOIN ' . $this->tablepre . 'forums AS f USING (fid)
            LEFT JOIN ' . $this->tablepre . 'posts AS p USING (tid)
            SET t.fid = ' . $newFid . ', p.fid = ' . $newFid . '
            WHERE f.fid IS NULL
        ');
        
        return $this->db->affected_rows();
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function countThreadsByUser(string $username, int $fid, bool $quarantine = false): int
    {
        return $this->countThreadsByFIDs(
            fids: [$fid],
            username: $username,
            quarantine: $quarantine,
        );
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     */
    public function countThreadsByForum(int $fid): int
    {
        return $this->countThreadsByFIDs([$fid]);
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     */
    public function countThreadsByFIDs(array $fids, ?int $after = null, ?string $username = null, bool $quarantine = false): int
    {
        $fids = implode(',', array_map('intval', $fids));

        $extra = '';

        if (! is_null($after)) {
            $extra .= " AND lastpost > '$after'"; // Remember, lastpost is a varchar field.
        }

        if (! is_null($username)) {
            $this->db->escape_fast($username);
            $extra .= " AND author = '$username'";
        }

        $table = $quarantine ? $this->tablepre . 'hold_threads' : $this->tablepre . 'threads';

        $query = $this->db->query("SELECT COUNT(*) FROM $table WHERE fid IN ($fids) $extra");
        $result = (int) $this->db->result($query);
        $this->db->free_result($query);

        return $result;
    }

    /**
     * Update all thread stats using as few queries as possible.
     *
     * @since 1.10.00
     */
    public function fixAllThreadCounts()
    {
        $this->db->query("UPDATE " . $this->tablepre . "threads AS t
            INNER JOIN (SELECT tid, COUNT(*) as pcount FROM " . $this->tablepre . "posts GROUP BY tid) AS query2 USING (tid)
            SET t.replies = query2.pcount - 1
        ");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     * @param array $values Field name & value list.
     * @param bool $quarantine Save this record in a private table for later review?
     * @param bool $qthread When starting a quarantined thread, we need to know not to use the tid field for the post to prevent ID collisions.
     * @return int Post ID number.
     */
    public function addPost(array $values, bool $quarantine = false, bool $qthread = false): int
    {
        // Required values:
        $ints = ['fid', 'tid', 'dateline'];
        $strings = ['author', 'message', 'subject', 'icon', 'usesig', 'useip', 'bbcodeoff', 'smileyoff'];

        $all = array_merge($ints, $strings);
        foreach ($all as $field) if (! isset($values[$field])) throw new LogicException("Missing $field");
        foreach ($ints as $field) if (! is_int($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
        foreach ($strings as $field) {
            if (! is_string($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
            $this->db->escape_fast($values[$field]);
        }

        $table = $quarantine ? $this->tablepre . 'hold_posts' : $this->tablepre . 'posts';
        $tid_field = $qthread ? 'newtid' : 'tid';

        $this->db->query("INSERT INTO $table SET
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

        return $this->db->insert_id();
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function savePostBody(int $pid, string $body, bool $quarantine = false)
    {
        $sqlbody = $this->db->escape($body);

        $table = $quarantine ? $this->tablepre . 'hold_posts' : $this->tablepre . 'posts';

        $this->db->query("UPDATE $table SET message = '$sqlbody' WHERE pid = $pid");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function getPostBody(int $pid, bool $quarantine = false): string
    {
        $table = $quarantine ? $this->tablepre . 'hold_posts' : $this->tablepre . 'posts';

        $query = $this->db->query("SELECT message FROM $table WHERE pid = $pid");
        if ($this->db->num_rows($query) == 1) {
            $result = $this->db->result($query);
        } else {
            $result = '';
        }
        return $result;
    }

    /**
     * Retrieve posts from a specific thread.
     *
     * @since 1.10.00
     */
    public function getPostsByTID(int $tid, ?int $ppp = null, bool $ascending = true): array
    {
        $order = $ascending ? 'ASC' : 'DESC';
        if (is_null($ppp)) {
            $limit = '';
        } elseif ($ppp < 0) {
            throw new InvalidArgumentException('The ppp argument must not be negative.');
        } else {
            $limit = "LIMIT $ppp";
        }
            
        $query = $this->db->query("SELECT * FROM " . $this->tablepre . "posts WHERE tid = $tid ORDER BY dateline $order, pid $order $limit");
        $result = $this->db->fetch_all($query);
        $this->db->free_result($query);

        return $result;
    }

    /**
     * Retrieve posts from a specific thread.
     *
     * @since 1.10.00
     */
    public function getPostsForThreadPage(int $tid, int $startdate, int $startpid, int $count): array
    {
        $result = $this->db->query("
            SELECT 'post' AS type, p.*, m.username, m.postnum, m.site, m.status, m.location, m.sig, m.avatar, m.customstatus, m.lastvisit, m.invisible, m.mood, m.regdate
            FROM " . $this->tablepre . "posts AS p
            LEFT JOIN " . $this->tablepre . "members AS m ON m.username=p.author
            WHERE tid = $tid AND (dateline > $startdate OR dateline = $startdate AND pid >= $startpid)
            ORDER BY dateline ASC, pid ASC
            LIMIT $count
        ");

        $all = $this->db->fetch_all($result);
        $this->db->free_result($result);

        return $all;
    }

    /**
     * Retrieve posts from a specific thread.
     *
     * @since 1.10.00
     */
    public function getPostsAndLogsForThreadPage(int $tid, int $startdate, int $enddate, int $startpid, int $count): array
    {
        $result = $this->db->query("
            SELECT p.*, m.username, m.postnum, m.site, m.status, m.location, m.sig, m.avatar, m.customstatus, m.lastvisit, m.invisible, m.mood, m.regdate
            FROM
            (
              (
                SELECT 'post' AS type, fid, tid, author, subject, dateline, pid, message, icon, usesig, useip, bbcodeoff, smileyoff
                FROM " . $this->tablepre . "posts
                WHERE tid = $tid AND (dateline > $startdate OR dateline = $startdate AND pid >= $startpid)
                ORDER BY dateline ASC, pid ASC
                LIMIT $count
              )
              UNION ALL
              (
                SELECT 'modlog' AS type, fid, tid, username AS author, action AS subject, date AS dateline, '', '', '', '', '', '', ''
                FROM " . $this->tablepre . "logs
                WHERE tid = $tid AND date >= $startdate AND date < $enddate
              )
            ) AS p
            LEFT JOIN " . $this->tablepre . "members m ON m.username = p.author
            ORDER BY p.dateline ASC, p.type DESC, p.pid ASC
        ");

        $all = $this->db->fetch_all($result);
        $this->db->free_result($result);

        return $result;
    }

    /**
     * Count posts using various filters.
     *
     * @since 1.9.12
     * @param bool   $quarantine Optional. Set to true when counting quarantined posts.
     * @param int    $tid        Optional. Filter result for a single thread.
     * @param string $username   Optional. Filter result for a single user.
     * @param int    $before     Optional. Timestamp of latest post to include in the count. Since 1.9.12.07.
     * @return int
     */
    public function countPosts(bool $quarantine = false, int $tid = 0, string $username = '', int $before = 0): int
    {
        $table = $quarantine ? $this->tablepre . 'hold_posts' : $this->tablepre . 'posts';

        $where = [];
        if ($tid != 0) {
            $where[] = "tid = $tid";
        }
        if ($username != '') {
            $sqluser = $this->db->escape($username);
            $where[] = "author = '$sqluser'";
        }
        if ($before != 0) {
            $where[] = "dateline <= $before";
        }

        if (empty($where)) {
            $where = '';
        } else {
            $where = "WHERE " . implode(' AND ', $where);
        }

        $query = $this->db->query("SELECT COUNT(*) FROM $table $where");
        $result = (int) $this->db->result($query);
        $this->db->free_result($query);

        return $result;
    }

    /**
     * Reset signatures on a member's posts.
     *
     * @since 1.10.00
     * @param bool $usesig
     * @param string username
     */
    public function setPostSigsByAuthor(bool $usesig, string $username)
    {
        $this->db->escape_fast($username);
        $yesno = $usesig ? 'yes' : 'no';

        $this->db->query("UPDATE " . $this->tablepre . "posts SET usesig = '$yesno' WHERE author = '$username'");
    }

    /**
     * Gets the PID of the oldest post in the specified thread.
     *
     * @since 1.10.00
     */
    public function getFirstPostInThread(int $tid): int
    {
        $result = $this->db->query("SELECT pid FROM " . $this->tablepre . "posts WHERE tid = $tid ORDER BY dateline LIMIT 1");
        $pid = (int) $this->db->result($result);
        $this->db->free_result($result);

        return $pid;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function addFavoriteIfMissing(int $tid, string $username, string $type, bool $quarantine = false)
    {
        $sqluser = $this->db->escape($username);
        $sqltype = $this->db->escape($type);

        $table = $quarantine ? $this->tablepre . 'hold_favorites' : $this->tablepre . 'favorites';

        $query = $this->db->query("SELECT COUNT(*) FROM $table WHERE tid = $tid AND username = '$sqluser' AND type = '$sqltype'");
        if (0 == (int) $this->db->result($query)) {
            $this->db->query("INSERT INTO $table SET tid = $tid, username = '$sqluser', type = '$sqltype'");
        }
        $this->db->free_result($query);
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     */
    public function deleteFavorites(array $tids, string $username, string $type, bool $quarantine = false)
    {
        $sqluser = $this->db->escape($username);
        $sqltype = $this->db->escape($type);
        $tids = array_map('intval', $tids);
        $tids = implode(',', $tids);

        $table = $quarantine ? $this->tablepre . 'hold_favorites' : $this->tablepre . 'favorites';

        $query = $this->db->query("DELETE FROM $table WHERE tid IN ($tids) AND username = '$sqluser' AND type = '$sqltype'");
    }

    /**
     * Retrieve the list of favorite threads for a user, sorted by recent posts.
     *
     * @since 1.10.00
     * @param string $username
     * @param array $fids Must be an array of numeric values representing the permitted forum FIDs.
     * @param ?int $limit The maximum number of records to return, or null.
     * @return array
     */
    public function getFavorites(string $username, array $fids, ?int $limit): array
    {
        $this->db->escape_fast($username);
        $fids = array_map('intval', $fids);
        $fids = implode(',', $fids);
        
        $limitSQL = is_null($limit) ? '' : "LIMIT $limit";

        $query = $this->db->query("
             SELECT t.tid, t.fid, t.lastpost, t.subject, t.icon, t.replies
             FROM " . $this->tablepre . "favorites f
             INNER JOIN " . $this->tablepre . "threads t USING (tid)
             WHERE f.username = '$username' AND f.type = 'favorite' AND t.fid IN ($fids)
             ORDER BY t.lastpost DESC
             $limitSQL
        ");

        $result = $this->db->fetch_all($query);
        $this->db->free_result($query);

        return $result;
    }

    /**
     * Retrieve the list of subscribed threads for a user, sorted by recent posts.
     *
     * @since 1.10.00
     * @param string $username
     * @param array $fids Must be an array of numeric values representing the permitted forum FIDs.
     * @param int $start The first record to return from the matched records.
     * @param int $limit The maximum number of records to return.
     * @return array
     */
    public function getSubscriptions(string $username, array $fids, int $start, int $limit): array
    {
        $this->db->escape_fast($username);
        $fids = array_map('intval', $fids);
        $fids = implode(',', $fids);
        
        $limitSQL = is_null($limit) ? '' : "LIMIT $limit";

        $query = $this->db->query("
             SELECT t.tid, t.fid, t.lastpost, t.subject, t.icon, t.replies
             FROM " . $this->tablepre . "favorites f
             INNER JOIN " . $this->tablepre . "threads t USING (tid)
             WHERE f.username = '$username' AND f.type = 'subscription' AND t.fid IN ($fids)
             ORDER BY t.lastpost DESC
             LIMIT $start, $limit
        ");

        $result = $this->db->fetch_all($query);
        $this->db->free_result($query);

        return $result;
    }
    
    /**
     * Count the number of valid subscriptions a user has.
     *
     * @since 1.10.00
     * @param string $username
     * @param array $fids Must be an array of numeric values representing the permitted forum FIDs.
     * @return array
     */
    public function countSubscriptionsByUser(string $username, array $fids): int
    {
        $this->db->escape_fast($username);
        $fids = array_map('intval', $fids);
        $fids = implode(',', $fids);

        $query = $this->db->query("
             SELECT COUNT(*)
             FROM " . $this->tablepre . "favorites f
             INNER JOIN " . $this->tablepre . "threads t USING (tid)
             WHERE f.username = '$username' AND f.type = 'subscription' AND t.fid IN ($fids)
        ");

        $result = (int) $this->db->result($query);
        $this->db->free_result($query);

        return $result;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     * @param array $values Field name & value list. Passed by reference and modified. Expects 'attachment' to be assigned by reference for performance.
     * @param bool $quarantine Save this record in a private table for later review?
     * @return int Attachment ID number.
     */
    public function addAttachment(array &$values, bool $quarantine = false): int
    {
        // Required values:
        $req = ['filename', 'filetype', 'filesize', 'subdir', 'uid'];

        // Optional values:
        // pid, attachment, img_size, parentid

        // Types:
        $ints = ['pid', 'parentid', 'uid'];
        $strings = ['filename', 'filetype', 'filesize', 'attachment', 'img_size', 'subdir'];

        foreach ($req as $field) if (! isset($values[$field])) throw new LogicException("Missing $field");
        foreach ($ints as $field) {
            if (isset($values[$field])) {
                if (! is_int($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
            } else {
                $values[$field] = 0;
            }
        }
        foreach ($strings as $field) {
            if (isset($values[$field])) {
                if (! is_string($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
                $this->db->escape_fast($values[$field]);
            } else {
                $values[$field] = '';
            }
        }

        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        $this->db->query("INSERT INTO $table SET
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

        return $this->db->insert_id();
    }

    /**
     * Copy a quarantined attachment record to the public table.
     *
     * @since 1.9.12
     */
    public function approveAttachment(int $oldaid, int $newpid, int $newparent): int
    {
        $this->db->query(
            "INSERT INTO " . $this->tablepre . "attachments " .
            "      (    pid, filename, filetype, filesize, attachment, downloads,   parentid, uid, updatetime, img_size, subdir) " .
            "SELECT $newpid, filename, filetype, filesize, attachment, downloads, $newparent, uid, updatetime, img_size, subdir " .
            "FROM " . $this->tablepre . "hold_attachments WHERE aid = $oldaid"
        );

        return $this->db->insert_id();
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function getAttachment(int $aid, bool $quarantine = false): array
    {
        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        $query = $this->db->query("SELECT *, UNIX_TIMESTAMP(updatetime) AS updatestamp FROM $table WHERE aid = $aid");
        if ($this->db->num_rows($query) == 1) {
            $result = $this->db->fetch_array($query);
        } else {
            $result = [];
        }
        $this->db->free_result($query);

        return $result;
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     */
    public function getAttachmentsByPIDs(array $pids, bool $quarantine = false): array
    {
        return $this->getAttachmentsByPIDsOrUID($quarantine, $pids);
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function getAttachmentAndFID(int $aid, bool $quarantine = false, int $pid = 0, string $filename = '', int $uid = 0): array
    {
        $table1 = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';
        $table2 = $quarantine ? $this->tablepre . 'hold_posts' : $this->tablepre . 'posts';

        $where = "a.aid = $aid";

        if ($pid != 0) {
            $where .= " AND a.pid = $pid";
        }

        if ($uid != 0) {
            $where .= " AND a.uid = $uid";
        }

        if ($filename != '') {
            $this->db->escape_fast($filename);
            $where .= " AND a.filename = '$filename'";
        }

        $query = $this->db->query("SELECT a.*, UNIX_TIMESTAMP(a.updatetime) AS updatestamp, p.fid FROM $table1 AS a LEFT JOIN $table2 AS p USING (pid) WHERE $where");
        if ($this->db->num_rows($query) == 1) {
            $result = $this->db->fetch_array($query);
        } else {
            $result = [];
        }
        $this->db->free_result($query);

        return $result;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function getOrphanedAttachments(bool $quarantine, int $uid): array
    {
        $pids = [0];

        return $this->getAttachmentsByPIDsOrUID($quarantine, $pids, $uid);
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     */
    public function getAttachmentsByPIDsOrUID(bool $quarantine, array $pids = [], ?int $uid = null): array
    {
        if (empty($pids) && is_null($uid)) throw new InvalidArgumentException('Either the pids or the uid argument must be provided.');

        $where = ['a.parentid = 0'];
        if (! empty($pids)) {
            $pids = array_map('intval', $pids);
            $csv = implode(',', $pids);
            $where[] = "a.pid IN ($csv)";
        }
        if (is_int($uid)) {
            $where[] = "a.uid = $uid";
        }
        $where = implode(' AND ', $where);
        
        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        $result = $this->db->query("
            SELECT a.aid, a.pid, a.filename, a.filetype, a.filesize, a.downloads, a.img_size,
                thumbs.aid AS thumbid, thumbs.filename AS thumbname, thumbs.img_size AS thumbsize
            FROM $table AS a
            LEFT JOIN $table AS thumbs ON a.aid = thumbs.parentid
            WHERE $where
        ");

        $all = $this->db->fetch_all($result);
        $this->db->free_result($result);

        return $all;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function deleteAttachmentsByID(array $aid_list, bool $quarantine = false)
    {
        if (empty($aid_list)) return;

        $ids = array_map('intval', $aid_list);
        $ids = implode(",", $ids);

        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        $this->db->query("DELETE FROM $table WHERE aid IN ($ids)");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     * @return mysqli_result|bool
     */
    public function getAttachmentPaths(array $aid_list, bool $quarantine = false)
    {
        if (empty($aid_list)) return;

        $ids = array_map('intval', $aid_list);
        $ids = implode(",", $ids);

        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        return $this->db->query("SELECT aid, subdir FROM $table WHERE aid IN ($ids)");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function getAttachmentParents(int $pid, bool $quarantine = false): array
    {
        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        $query = $this->db->query("SELECT aid, filesize, parentid FROM $table WHERE pid = $pid ORDER BY parentid");

        $results = $this->db->fetch_all($query);
        $this->db->free_result($query);

        return $results;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function claimOrphanedAttachments(int $pid, int $uid, bool $quarantine = false)
    {
        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        $this->db->query("UPDATE $table SET pid = $pid WHERE pid = 0 AND uid = $uid");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function countOrphanedAttachments(int $uid, bool $quarantine = false): int
    {
        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        $query = $this->db->query("SELECT COUNT(*) FROM $table WHERE pid = 0 AND parentid = 0 AND uid = $uid");
        $count = (int) $this->db->result($query);
        $this->db->free_result($query);

        return $count;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function countAttachmentsByPost(int $pid, bool $quarantine = false): int
    {
        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        $query = $this->db->query("SELECT COUNT(*) FROM $table WHERE pid = $pid AND parentid = 0");
        $count = (int) $this->db->result($query);
        $this->db->free_result($query);

        return $count;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function countThumbnails(int $aid, bool $quarantine = false): int
    {
        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        $query = $this->db->query("SELECT COUNT(*) FROM $table WHERE parentid = $aid AND filename LIKE '%-thumb.jpg'");
        $count = (int) $this->db->result($query);
        $this->db->free_result($query);

        return $count;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function getAttachmentChildIDs(int $aid, bool $thumbnails_only, bool $quarantine = false): array
    {
        $result = [];

        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        if ($thumbnails_only) {
            $where = "AND filename LIKE '%-thumb.jpg'";
        } else {
            $where = '';
        }

        $query = $this->db->query("SELECT aid FROM $table WHERE parentid = $aid $where");
        while ($row = $this->db->fetch_array($query)) {
            $result[] = $row['aid'];
        }
        $this->db->free_result($query);

        return $result;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function getOrphanedAttachmentIDs(int $uid, bool $quarantine = false): array
    {
        $result = [];

        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        $query = $this->db->query("SELECT aid FROM $table WHERE pid = 0 AND parentid = 0 AND uid = $uid");
        while ($row = $this->db->fetch_array($query)) {
            $result[] = $row['aid'];
        }
        $this->db->free_result($query);

        return $result;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function getAttachmentIDsByPost(int $pid, bool $include_children, bool $quarantine = false): array
    {
        $result = [];

        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        if ($include_children) {
            $where = '';
        } else {
            $where = 'AND parentid = 0';
        }

        $query = $this->db->query("SELECT aid FROM $table WHERE pid = $pid $where");
        while ($row = $this->db->fetch_array($query)) {
            $result[] = $row['aid'];
        }
        $this->db->free_result($query);

        return $result;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function getAttachmentIDsByThread(array $tid_list, bool $quarantine = false, int $notpid = 0): array
    {
        $result = [];

        if (empty($tid_list)) return $result;

        $ids = array_map('intval', $tid_list);
        $ids = implode(",", $ids);

        $table1 = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';
        $table2 = $quarantine ? $this->tablepre . 'hold_posts' : $this->tablepre . 'posts';

        if (0 == $notpid) {
            $where = '';
        } else {
            $where = "AND p.pid != $notpid";
        }

        $query = $this->db->query("SELECT a.aid FROM $table1 AS a INNER JOIN $table2 AS p USING (pid) WHERE p.tid IN ($ids) $where");
        while ($row = $this->db->fetch_array($query)) {
            $result[] = $row['aid'];
        }
        $this->db->free_result($query);

        return $result;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function getAttachmentIDsByUser(string $username, bool $quarantine = false): array
    {
        $sqluser = $this->db->escape($username);

        $result = [];

        $table1 = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';
        $table2 = $quarantine ? $this->tablepre . 'hold_posts' : $this->tablepre . 'posts';

        $query = $this->db->query("SELECT aid FROM $table1 INNER JOIN $table2 USING (pid) WHERE author = '$sqluser'");
        while ($row = $this->db->fetch_array($query)) {
            $result[] = $row['aid'];
        }
        $this->db->free_result($query);

        $query = $this->db->query("SELECT aid FROM $table1 INNER JOIN " . $this->tablepre . "members USING (uid) WHERE username = '$sqluser'");
        while ($row = $this->db->fetch_array($query)) {
            $result[] = $row['aid'];
        }
        $this->db->free_result($query);

        return array_unique($result);
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function renameAttachment(int $aid, string $name, bool $quarantine = false)
    {
        $sqlname = $this->db->escape($name);

        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        $this->db->query("UPDATE $table SET filename='$sqlname' WHERE aid = $aid");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function setImageDims(int $aid, string $img_size, bool $quarantine = false)
    {
        $sqlsize = $this->db->escape($img_size);

        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        $this->db->query("UPDATE $table SET img_size='$sqlsize' WHERE aid = $aid");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function raiseDownloadCounter(int $aid, bool $quarantine = false)
    {
        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        $this->db->query("UPDATE $table SET downloads = downloads + 1 WHERE aid = $aid");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     * @param int $tid Thread ID number.
     * @param bool $quarantine Save this record in a private table for later review?
     * @return int Poll ID number.
     */
    public function addVoteDesc(int $tid, bool $quarantine = false): int
    {
        $table = $quarantine ? $this->tablepre . 'hold_vote_desc' : $this->tablepre . 'vote_desc';

        $this->db->query("INSERT INTO $table SET topic_id = $tid");

        return $this->db->insert_id();
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     * @param array $rows Must be an array of arrays representing rows, then values associated to field names.
     * @param bool $quarantine Save these records in a private table for later review?
     */
    public function addVoteOptions(array $rows, bool $quarantine = false)
    {
        if (empty($rows)) return;

        $sqlrows = [];

        // Required values:
        $req = ['vote_id', 'vote_option_id', 'vote_option_text'];

        // Optional values:
        // vote_result

        // Types:
        $ints = ['vote_id', 'vote_option_id', 'vote_result'];
        $strings = ['vote_option_text'];

        foreach ($rows as $values) {
            foreach ($req as $field) if (! isset($values[$field])) throw new LogicException("Missing $field");
            foreach ($ints as $field) {
                if (isset($values[$field])) {
                    if (! is_int($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
                } else {
                    $values[$field] = 0;
                }
            }
            foreach ($strings as $field) {
                if (isset($values[$field])) {
                    if (! is_string($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
                    $this->db->escape_fast($values[$field]);
                } else {
                    $values[$field] = '';
                }
            }
            $sqlrows[] = "( {$values['vote_id']}, {$values['vote_option_id']}, '{$values['vote_option_text']}', {$values['vote_result']} )";
        }
        $sqlrows = implode(',', $sqlrows);

        $table = $quarantine ? $this->tablepre . 'hold_vote_results' : $this->tablepre . 'vote_results';

        $this->db->query("INSERT INTO $table (vote_id, vote_option_id, vote_option_text, vote_result) VALUES $sqlrows");
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     * @param int $voteID
     * @param bool $quarantine Get these records from the private review table?
     * @return array
     */
    public function getVoteOptions(int $voteID, bool $quarantine = false): array
    {
        $table = $quarantine ? $this->tablepre . 'hold_vote_results' : $this->tablepre . 'vote_results';

        $result = $this->db->query("SELECT * FROM $table WHERE vote_id = $voteID");

        $rows = $this->db->fetch_all($result);
        $this->db->free_result($result);
        
        return $rows;
    }

    /**
     * SQL command
     *
     * @since 1.9.12.07
     * @param int $vote_id The related vote_desc.vote_id value.
     * @param int $user_id The voter's numeric member ID.
     * @param string $user_ip The voter's IP address.
     * @return bool Whether or not a record was added.
     */
    public function addVoter(int $vote_id, int $user_id, string $user_ip)
    {
        $this->db->escape_fast($user_ip);

        $this->db->query("INSERT INTO " . $this->tablepre . "vote_voters (vote_id, vote_user_id, vote_user_ip) VALUES ($vote_id, $user_id, '$user_ip')");

        return ($this->db->affected_rows() == 1);
    }

    /**
     * Remove a poll (or multiple polls) and all of its records.
     *
     * @since 1.10.00
     * @param array $tids Array of thread ID numbers.
     * @param bool $quarantine Delete this record from the private review tables?
     */
    public function deleteVotesByTID(array $tids, bool $quarantine = false)
    {
        $tids = array_map('intval', $tids);
        $tids = implode(',', $tids);

        $tabled = $this->tablepre . ($quarantine ? 'hold_vote_desc' : 'vote_desc');
        $tabler = $this->tablepre . ($quarantine ? 'hold_vote_results' : 'vote_results');
        $tablev = $this->tablepre . 'vote_voters';

        $deletes = $quarantine ? 'd, r' : 'd, r, v';

        $extraJoin = $quarantine ? '' : "LEFT JOIN $tablev AS v ON v.vote_id = d.vote_id";

        $this->db->query("
            DELETE $deletes
            FROM $tabled AS d
            LEFT JOIN $tabler AS r ON r.vote_id = d.vote_id
            $extraJoin
            WHERE d.topic_id IN ($tids)
        ");
    }

    /**
     * Remove a user's guest record and any other stale records.
     *
     * @since 1.9.12.04
     * @param string $address Current remote IP address.
     * @param string $username Current user.
     * @param int $timelimit The timestamp before which all records may be purged.
     */
    public function deleteOldWhosonline(string $address, string $username, int $timelimit)
    {
        $this->db->escape_fast($address);
        $this->db->escape_fast($username);

        $this->db->query("DELETE FROM " . $this->tablepre . "whosonline WHERE ((ip='$address' AND username='xguest123') OR (username='$username') OR (time < $timelimit))");
    }

    /**
     * Remove a user's records upon logout.
     *
     * @since 1.10.00
     * @param string $username Current user.
     */
    public function deleteWhosonline(string $username)
    {
        $this->db->escape_fast($username);

        $this->db->query("DELETE FROM " . $this->tablepre . "whosonline WHERE username = '$username'");
    }

    /**
     * Add a Who's Online Record
     *
     * @since 1.9.12.04
     * @param string $username Current user.
     * @param string $address Current remote IP address.
     * @param int $time The session start timestamp.
     * @param string $url The relative URL.
     * @param string $invisible Should be '0' for no or '1' for yes.
     */
    public function addWhosonline(string $address, string $username, int $time, string $url, string $invisible)
    {
        $this->db->escape_fast($address);
        $this->db->escape_fast($username);
        $this->db->escape_fast($url);
        $this->db->escape_fast($invisible);

        // The members.invisible field is a SET type and must be sent as a string.
        // Otherwise, MySQL would coerce integer literals to a bit set value rather than a string value.

        $this->db->query("INSERT INTO " . $this->tablepre . "whosonline (username, ip, time, location, invisible) VALUES ('$username', '$address', $time, '$url', '$invisible')");
    }

    /**
     * Retrieve the vote_id value based on a tid.
     *
     * @since 1.9.12.04
     * @param int $time The session start timestamp.
     * @param bool $quarantine Was this record in a private table for later review?
     * @return int Poll ID number or zero if not found.
     */
    public function getPollId(int $tid, bool $quarantine = false): int
    {
        $table = $quarantine ? $this->tablepre . 'hold_vote_desc' : $this->tablepre . 'vote_desc';

        $query = $this->db->query("SELECT vote_id FROM $table WHERE topic_id = $tid");

        if ($this->db->num_rows($query) === 0) {
            $id = 0;
        } else {
            $id = (int) $this->db->result($query);
        }
        $this->db->free_result($query);

        return $id;
    }

    /**
     * Retrieve the entire ranks table.
     *
     * @since 1.9.12.05
     * @param bool $noStaff Optional. When true, none of the staff ranks will be included.
     * @return array of associative table rows.
     */
    public function getRanks(bool $noStaff = false): array
    {
        if ($noStaff) {
            $where = "WHERE title NOT IN ('Moderator', 'Super Moderator', 'Super Administrator', 'Administrator')";
        } else {
            $where = '';
        }

        $result = $this->db->query("SELECT * FROM " . $this->tablepre . "ranks $where ORDER BY stars");

        $ranks = $this->db->fetch_all($result);
        $this->db->free_result($result);

        return $ranks;
    }

    /**
     * Create or update a rank record.
     *
     * @since 1.9.12.08
     */
    function saveRank(string $title, int $posts, int $stars, bool $allowavatars, string $avatarrank, ?int $id = null)
    {
        $this->db->escape_fast($title);
        $this->db->escape_fast($avatarrank);
        $yesno = $allowavatars ? 'yes' : 'no';

        if (is_null($id)) {
            $verb = 'INSERT INTO ';
            $where = '';
        } else {
            $verb = 'UPDATE ';
            $where = "WHERE id = $id";
        }
        $this->db->query($verb . $this->tablepre . "ranks SET title = '$title', posts = $posts, stars = $stars, allowavatars = '$yesno', avatarrank = '$avatarrank' $where");
    }

    /**
     * Delete user rank records by a list of IDs.
     *
     * @since 1.10.00
     * @param array $ids
     */
    public function deleteRanksByList(array $ids)
    {
        if (empty($ids)) return;

        $ids = array_map('intval', $ids);
        $ids = implode(',', $ids);

        $this->db->query("DELETE FROM " . $this->tablepre . "ranks WHERE id IN ($ids)");
    }

    /**
     * Retrieve the list of smilies.
     *
     * @since 1.10.00
     * @return array of associative table rows.
     */
    public function getSmilies(): array
    {
        $result = $this->db->query("SELECT * FROM " . $this->tablepre . "smilies WHERE type = 'smiley'");

        $smilies = $this->db->fetch_all($result);
        $this->db->free_result($result);

        return $smilies;
    }

    /**
     * Add multiple smilies records.
     *
     * @since 1.10.00
     */
    public function addSmilies(array $rows)
    {
        if (count($rows) == 0) return;

        $values = [];
        foreach ($rows as $row) {
            if (empty($row['code']) || empty($row['url'])) throw new InvalidArgumentException('A required value is missing');

            $values[] = "('smiley', '" . $this->db->escape($row['code']) . "', '" . $this->db->escape($row['url']) . "')";
        }
        
        $this->db->query("INSERT INTO " . $this->tablepre . "smilies (type, code, url) VALUES " . implode(',', $values));
    }

    /**
     * Retrieve the list of post icons.
     *
     * @since 1.10.00
     * @return array of associative table rows.
     */
    public function getPostIcons(): array
    {
        $result = $this->db->query("SELECT * FROM " . $this->tablepre . "smilies WHERE type = 'picon' ORDER BY id");

        $icons = $this->db->fetch_all($result);
        $this->db->free_result($result);

        return $icons;
    }

    /**
     * Check existence of a post icon record.
     *
     * @since 1.10.00
     */
    public function iconExists(string $url): bool
    {
        $this->db->escape_fast($url);

        $result = $this->db->query("SELECT id FROM " . $this->tablepre . "smilies WHERE type = 'picon' AND url = '$url'");

        $exists = $this->db->num_rows($result) !== 0;
        $this->db->free_result($result);

        return $exists;

    }

    /**
     * Retrieve the list of censors.
     *
     * @since 1.10.00
     * @return array of associative table rows.
     */
    public function getCensors(): array
    {
        $result = $this->db->query("SELECT * FROM " . $this->tablepre . "words ORDER BY id");

        $words = $this->db->fetch_all($result);
        $this->db->free_result($result);

        return $words;
    }

    /**
     * Retrieve the list of username restrictions.
     *
     * @since 1.10.00
     * @return array of associative table rows.
     */
    public function getRestrictions(): array
    {
        $result = $this->db->query("SELECT * FROM " . $this->tablepre . "restricted ORDER BY id");

        $restrictions = $this->db->fetch_all($result);
        $this->db->free_result($result);

        return $restrictions;
    }

    /**
     * Delete a username restriction.
     *
     * @since 1.10.00
     */
    public function deleteRestriction(int $id)
    {
        $this->db->query("DELETE FROM " . $this->tablepre . "restricted WHERE id = $id");
    }

    /**
     * Add a username restriction.
     *
     * @since 1.10.00
     */
    public function addRestriction(string $name, bool $caseSensitive, bool $partial)
    {
        $this->db->escape_fast($name);
        $caseEnum = $caseSensitive ? '1' : '0';
        $partialEnum = $partial ? '1' : '0';

        $this->db->query("INSERT INTO " . $this->tablepre . "restricted (`name`, `case_sensitivity`, `partial`) VALUES ('$name', '$caseEnum', '$partialEnum')");
    }

    /**
     * Update a username restriction.
     *
     * @since 1.10.00
     */
    public function updateRestriction(int $id, string $name, bool $caseSensitive, bool $partial)
    {
        $this->db->escape_fast($name);
        $caseEnum = $caseSensitive ? '1' : '0';
        $partialEnum = $partial ? '1' : '0';

        $this->db->query("UPDATE " . $this->tablepre . "restricted SET name = '$name', case_sensitivity = '$caseEnum', partial = '$partialEnum' WHERE id = $id");
    }

    /**
     * Retrieve the list of forums.
     *
     * Note the XMB class Forums service is used to cache this list and should be used instead of SQL in most situations.
     *
     * @since 1.10.00
     * @return array of associative table rows.  The top level is keyed by fid values.
     */
    public function getForums(bool $activeOnly = true): array
    {
        if ($activeOnly) {
            $where = "WHERE status = 'on'";
        } else {
            $where = '';
        }
        
        $result = $this->db->query("SELECT * FROM " . $this->tablepre . "forums $where ORDER BY displayorder ASC");

        // Fetch all records from the result.
        $forums = $this->db->fetch_all($result);
        $this->db->free_result($result);

        // Re-key the forums array by the fid numbers.
        $keys = array_column($forums, 'fid');
        $keys = array_map('intval', $keys);
        $forums = array_combine($keys, $forums);

        return $forums;
    }

    /**
     * SQL command
     *
     * @since 1.9.12.08
     */
    public function setForumMods(int $fid, string $mods)
    {
        $this->db->escape_fast($mods);

        $this->db->query("UPDATE " . $this->tablepre . "forums SET moderator = '$mods' WHERE fid = $fid");
    }

    /**
     * Save a new entry to the forum log for auditing.
     *
     * @since 1.9.12.07
     * @param string $user The HTML version of the username.
     * @param string $action The script or query used.
     * @param int $fid The forum ID used.
     * @param int $tid The thread ID used.
     * @param int $timestamp The time of the log entry.
     */
    public function addLog(string $user, string $action, int $fid, int $tid, int $timestamp)
    {
        $this->db->escape_fast($action);
        $this->db->escape_fast($user);

        $this->db->query("INSERT INTO " . $this->tablepre . "logs (tid, username, action, fid, date) VALUES ($tid, '$user', '$action', $fid, $timestamp)");
    }

    /**
     * Delete all records from the admin panel log.
     *
     * @since 1.10.00
     */
    public function clearAdminLog()
    {
        $this->db->query("DELETE FROM " . $this->tablepre . "logs WHERE fid = 0");
    }

    /**
     * Fetch the saved IP Address from a post
     *
     * @since 1.9.12.07
     * @param int $pid The post ID number.
     * @return string The IP Address.
     */
    public function getIPFromPost(int $pid): string
    {
        $query = $this->db->query("SELECT useip FROM " . $this->tablepre . "posts WHERE pid = $pid");
        
        if ($this->db->num_rows($query) === 0) {
            $addr = ''; // Post not found.
        } else {
            $addr = $this->db->result($query);
        }
        $this->db->free_result($query);

        return $addr;
    }

    /**
     * Fetch the forum ID and theme based on a thread ID.
     *
     * @since 1.10.00
     * @param int $tid The thread ID number.
     * @param boool $getThemeIDToo Should the result contain the theme field or not?
     * @return array The forum ID and theme.
     */
    public function getFIDFromTID(int $tid, bool $getThemeIDToo = false): array
    {
        if ($getThemeIDToo) {
            $query = $this->db->query("SELECT f.fid, f.theme FROM " . $this->tablepre . "forums f RIGHT JOIN " . $this->tablepre . "threads t USING (fid) WHERE t.tid = $tid");
        } else {
            $query = $this->db->query("SELECT fid FROM " . $this->tablepre . "threads WHERE tid = $tid");
        }

        if ($this->db->num_rows($query) === 0) {
            $forum = []; // Post not found.
        } else {
            $forum = $this->db->fetch_array($query);
        }
        $this->db->free_result($query);

        return $forum;
    }
    
    /**
     * Reset a user's theme to default.
     *
     * @since 1.10.00
     * @param int $uid The member ID number.
     */
    public function resetUserTheme(int $uid)
    {
        $this->db->query("UPDATE " . $this->tablepre . "members SET theme = 0 WHERE uid = $uid");
    }    

    /**
     * Reset a forum's theme to default.
     *
     * @since 1.10.00
     * @param int $uid The member ID number.
     */
    public function resetForumTheme(int $fid)
    {
        $this->db->query("UPDATE " . $this->tablepre . "forums SET theme = 0 WHERE fid = $fid");
    }    

    /**
     * Fetch the first theme in the themes table.
     *
     * @since 1.10.00
     * @return array The theme record.
     */
    public function getFirstTheme(): array
    {
        $query = $this->db->query("SELECT * FROM " . $this->tablepre . "themes LIMIT 1");

        if ($this->db->num_rows($query) === 0) {
            $theme = []; // No themes found.
        } else {
            $theme = $this->db->fetch_array($query);
        }
        $this->db->free_result($query);

        return $theme;
    }

    /**
     * Set the name of a theme.
     *
     * @since 1.9.12.08
     * @param int $id
     * @param string $name
     */
    public function setThemeName(int $id, string $name)
    {
        $this->db->escape_fast($name);
        $this->db->query("UPDATE " . $this->tablepre . "themes SET `name` = '$name' WHERE themeid = $id");
    }

    /**
     * Delete IP Banning records by a list of IDs.
     *
     * @since 1.9.12.08
     * @param array $ids
     */
    public function deleteIPBansByList(array $ids)
    {
        if (empty($ids)) return;

        $ids = array_map('intval', $ids);
        $ids = implode(',', $ids);

        $this->db->query("DELETE FROM " . $this->tablepre . "banned WHERE id IN ($ids)");
    }

    /**
     * Count the unread U2U messages for a user.
     *
     * @since 1.10.00
     * @param string $username
     * @return int
     */
    public function countU2UInbox(string $username): int
    {
        $this->db->escape_fast($username);

        $query = $this->db->query("SELECT COUNT(*) FROM " . $this->tablepre . "u2u WHERE owner = '$username' AND folder = 'Inbox' AND readstatus = 'no'");

        $result = (int) $this->db->result($query);
        $this->db->free_result($query);

        return $result;
    }

    /**
     * Get the U2U Inbox messages for a user.
     *
     * @since 1.10.00
     * @param string $username
     * @param int $limit The maximum number of records to return.
     * @return array
     */
    public function getU2UInbox(string $username, int $limit = 5): array
    {
        $this->db->escape_fast($username);

        $query = $this->db->query("SELECT * FROM " . $this->tablepre . "u2u WHERE owner = '$username' AND folder = 'Inbox' ORDER BY dateline DESC LIMIT $limit");

        $result = $this->db->fetch_all($query);
        $this->db->free_result($query);

        return $result;
    }

    /**
     * Add a U2U message record.
     *
     * @since 1.10.00
     */
    public function addU2U(string $to, string $from, string $type, string $owner, string $folder, string $subject, string $message, string $isRead, string $isSent, int $timestamp)
    {
        $this->db->escape_fast($to);
        $this->db->escape_fast($from);
        $this->db->escape_fast($owner);
        $this->db->escape_fast($folder);
        $this->db->escape_fast($subject);
        $this->db->escape_fast($message);
        if (false === array_search($type, ['incoming', 'outgoing', 'draft'])) throw new InvalidArgumentException('Unexpected value for the $type parameter.');
        if ($isRead !== 'yes' && $isRead !== 'no') throw new InvalidArgumentException('Unexpected value for the $isRead parameter.');
        if ($isSent !== 'yes' && $isSent !== 'no') throw new InvalidArgumentException('Unexpected value for the $isSent parameter.');

        $this->db->query("INSERT INTO " . $this->tablepre . "u2u (msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus) VALUES ('$to', '$from', '$type', '$owner', '$folder', '$subject', '$message', $timestamp, '$isRead', '$isSent')");
    }

    /**
     * Get the folder name based on a list of message IDs.
     *
     * @since 1.10.00
     */
    public function getU2UFolder(array $msgIDs): string
    {
        $msgIDs = array_map('intval', $msgIDs);
        $in = implode(',', $msgIDs);
        
        $result = $this->db->query("SELECT folder FROM " . $this->tablepre . "u2u WHERE u2uid IN ($in) GROUP BY folder");
        
        if ($this->db->num_rows($result) === 1) {
            $folder = $this->db->result($result);
        } else {
            $folder = '';
        }
        $this->db->free_result($result);

        return $folder;
    }

    /**
     * Retrieve the list of buddys for the specified user.
     *
     * @since 1.10.00
     * @param string $username
     * @return array
     */
    public function getBuddyList(string $username): array
    {
        $this->db->escape_fast($username);

        $query = $this->db->query("
            SELECT b.buddyname, m.invisible, m.username, m.lastvisit
            FROM " . $this->tablepre . "buddys b
            LEFT JOIN " . $this->tablepre . "members m ON (b.buddyname = m.username)
            WHERE b.username = '$username'
        ");

        $result = $this->db->fetch_all($query);
        $this->db->free_result($query);

        return $result;
    }
}
