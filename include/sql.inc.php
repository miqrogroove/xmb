<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2024, The XMB Group
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
    public function saveSession(string $token, string $username, int $date, int $expire, int $regenerate, string $replace, string $agent): bool
    {
        $sqltoken = $this->db->escape($token);
        $sqluser = $this->db->escape($username);
        $sqlreplace = $this->db->escape($replace);
        $sqlagent = $this->db->escape($agent);

        $this->db->query("INSERT IGNORE INTO " . $this->tablepre . "sessions SET token = '$sqltoken', username = '$sqluser', login_date = $date,
            expire = $expire, regenerate = $regenerate, replaces = '$sqlreplace', agent = '$sqlagent'");

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
     * SQL command
     *
     * @since 1.9.12
     * @return mysqli_result|bool
     */
    public function getSessionsByName(string $username)
    {
        $sqluser = $this->db->escape($username);

        return $this->db->query("SELECT * FROM " . $this->tablepre . "sessions WHERE username = '$sqluser'");
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
     * SQL command
     *
     * @since 1.9.12
     */
    public function getSessionReplacement(string $token, string $username): array
    {
        $sqltoken = $this->db->escape($token);
        $sqluser = $this->db->escape($username);

        $query = $this->db->query("SELECT * FROM " . $this->tablepre . "sessions WHERE replaces = '$sqltoken' AND username = '$sqluser'");
        if ($this->db->num_rows($query) == 1) {
            $session = $this->db->fetch_array($query);
        } else {
            $session = [];
        }
        $this->db->free_result($query);
        return $session;
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
        $req = ['username', 'password', 'email', 'status', 'regip', 'regdate'];

        // Types:
        $ints = ['regdate', 'postnum', 'theme', 'tpp', 'ppp', 'timeformat', 'lastvisit', 'pwdate', 'u2ualert', 'bad_login_date',
        'bad_login_count', 'bad_session_date', 'bad_session_count'];

        $numerics = ['timeoffset'];

        $strings = ['username', 'password', 'email', 'site', 'status', 'location', 'bio', 'sig', 'showemail', 'avatar',
        'customstatus', 'bday', 'langfile', 'newsletter', 'regip', 'ban', 'dateformat', 'ignoreu2u', 'mood', 'invisible',
        'u2ufolders', 'saveogu2u', 'emailonu2u', 'useoldu2u', 'sub_each_post', 'waiting_for_mod'];

        $sql = [];

        foreach($req as $field) if (! isset($values[$field])) throw new LogicException("Missing $field");
        foreach($ints as $field) {
            if (isset($values[$field])) {
                if (! is_int($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
            } else {
                $values[$field] = 0;
            }
            $sql[] = "$field = {$values[$field]}";
        }
        foreach($numerics as $field) {
            if (isset($values[$field])) {
                if (! is_numeric($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
            } else {
                $values[$field] = 0;
            }
            $sql[] = "$field = {$values[$field]}";
        }
        foreach($strings as $field) {
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
     * @since 1.9.12
     * @param string $username Must be HTML encoded.
     * @return array Member record or empty array.
     */
    public function getMemberByName(string $username): array
    {
        $sqluser = $this->db->escape($username);

        $query = $this->db->query("SELECT * FROM " . $this->tablepre . "members WHERE username = '$sqluser'");
        if ($this->db->num_rows($query) == 1) {
            $member = $this->db->fetch_array($query);
        } else {
            $member = [];
        }
        $this->db->free_result($query);
        return $member;
    }

    /**
     * SQL command
     *
     * @since 1.9.12.06
     * @param string $username Must be HTML encoded.
     * @param string $invisible Should be '0' for no or '1' for yes.
     */
    public function changeMemberVisibility(string $username, string $invisible)
    {
        $this->db->escape_fast($username);
        $this->db->escape_fast($invisible);

        // The members.invisible field is a SET type and must be sent as a string.
        // Otherwise, MySQL would coerce integer literals to a bit set value rather than a string value.

        $this->db->query("UPDATE " . $this->tablepre . "members SET invisible='$invisible' WHERE username='$username'");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function countMembers(): int
    {
        $query = $this->db->query("SELECT COUNT(*) FROM " . $this->tablepre . "members");
        $result = (int) $this->db->result($query, 0);
        $this->db->free_result($query);

        return $result;
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     * @param string $username
     * @return int The new value of bad_login_count for this member.
     */
    public function raiseLoginCounter(string $username): int
    {
        $sqluser = $this->db->escape($username);

        $this->db->query("UPDATE " . $this->tablepre . "members SET bad_login_count = LAST_INSERT_ID(bad_login_count + 1) WHERE username = '$sqluser'");

        return $this->db->insert_id();
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function resetLoginCounter(string $username, int $date)
    {
        $sqluser = $this->db->escape($username);

        $this->db->query("UPDATE " . $this->tablepre . "members SET bad_login_count = 1, bad_login_date = $date WHERE username = '$sqluser'");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     * @param string $username
     * @return int The new value of bad_session_count for this member.
     */
    public function raiseSessionCounter(string $username): int
    {
        $sqluser = $this->db->escape($username);

        $this->db->query("UPDATE " . $this->tablepre . "members SET bad_session_count = LAST_INSERT_ID(bad_session_count + 1) WHERE username = '$sqluser'");

        return $this->db->insert_id();
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function resetSessionCounter(string $username, int $date)
    {
        $sqluser = $this->db->escape($username);

        $this->db->query("UPDATE " . $this->tablepre . "members SET bad_session_count = 1, bad_session_date = $date WHERE username = '$sqluser'");
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

        $result = [];
        while ($admin = $this->db->fetch_array($query)) {
            $result[] = $admin;
        }
        $this->db->free_result($query);

        return $result;
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
        $count = (int) $this->db->result($query, 0);
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
     * @since 1.9.12
     */
    public function setNewPassword(string $username, string $password)
    {
        $sqlpass = $this->db->escape($password);
        $sqluser = $this->db->escape($username);

        $this->db->query("UPDATE " . $this->tablepre . "members SET password = '$sqlpass', bad_login_count = 0 WHERE username = '$sqluser'");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function setLastvisit(string $username, int $timestamp)
    {
        $sqluser = $this->db->escape($username);

        $this->db->query("UPDATE " . $this->tablepre . "members SET lastvisit = $timestamp WHERE username = '$sqluser'");
    }

    /**
     * Increments the user's post total.
     *
     * Also resets the user's lastvisit timestamp because otherwise elevateUser() allows it to be 60 seconds old.
     *
     * @since 1.9.12
     */
    public function raisePostCount(string $username, int $timestamp)
    {
        $sqluser = $this->db->escape($username);

        $this->db->query("UPDATE " . $this->tablepre . "members SET postnum = postnum + 1, lastvisit = $timestamp WHERE username = '$sqluser'");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function unlockMember(string $username)
    {
        $sqluser = $this->db->escape($username);

        $this->db->query("UPDATE " . $this->tablepre . "members SET bad_login_count = 0 WHERE username = '$sqluser'");
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
     * @param array $values Field name & value list. Passed by reference and modified, so don't assign references or re-use the same array.
     * @param bool $quarantine Save this record in a private table for later review?
     * @return int Thread ID number.
     */
    public function addThread(array &$values, bool $quarantine = false): int
    {
        // Required values:
        $req = ['fid', 'author', 'lastpost', 'subject', 'icon'];

        // Optional values:
        // views, replies, topped, pollopts, closed

        // Types:
        $ints = ['fid', 'views', 'replies', 'topped', 'pollopts'];
        $strings = ['author', 'lastpost', 'subject', 'icon', 'closed'];

        foreach($req as $field) if (! isset($values[$field])) throw new LogicException("Missing $field");
        foreach($ints as $field) {
            if (isset($values[$field])) {
                if (! is_int($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
            } else {
                $values[$field] = 0;
            }
        }
        foreach($strings as $field) {
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
     * SQL command
     *
     * @since 1.9.12
     */
    public function setThreadLastpost(int $tid, string $lastpost, bool $quarantine = false)
    {
        $sqllast = $this->db->escape($lastpost);

        $table = $quarantine ? $this->tablepre . 'hold_threads' : $this->tablepre . 'threads';

        $this->db->query("UPDATE $table SET lastpost = '$sqllast' WHERE tid = $tid");
    }

    /**
     * Find the most recent lastpost value among all threads in the given forum.
     *
     * @since 1.9.12.06
     */
    public function findLaspostByForum(int $fid): string
    {
        $query = $this->db->query("SELECT t.lastpost
        FROM " . $this->tablepre . "forums AS f
        LEFT JOIN " . $this->tablepre . "threads AS t USING (fid)
        WHERE f.fid = $fid OR f.fup = $fid
        ORDER BY t.lastpost DESC
        LIMIT 1");

        if ($this->db->num_rows($query) === 0) {
            $result = ''; // Forum not found.
        } else {
            $result = $this->db->result($query, 0);
            if (null === $result) $result = ''; // Forum is empty.
        }
        $this->db->free_result($query);

        return $result;
    }

    /**
     * SQL command
     *
     * @since 1.9.12.06
     */
    public function setForumCounts(int $fid, int $postcount, int $threadcount, string $lastpost)
    {
        $this->db->escape_fast($lastpost);

        $this->db->query("UPDATE " . $this->tablepre . "forums SET posts = $postcount, threads = $threadcount, lastpost = '$lastpost' WHERE fid = $fid");
    }

    /**
     * SQL command
     *
     * @since 1.9.12
     */
    public function countThreadsByUser(string $username, int $fid, bool $quarantine = false): int
    {
        return $this->countThreadsByForum($fid, $username, $quarantine);
    }

    /**
     * SQL command
     *
     * @since 1.10.00
     */
    public function countThreadsByForum(int $fid, ?string $username = null, bool $quarantine = false): int
    {
        if (is_null($username)) {
            $author = '';
        } else {
            $this->db->escape_fast($username);
            $author =  "AND author = '$username'";
        }

        $table = $quarantine ? $this->tablepre . 'hold_threads' : $this->tablepre . 'threads';

        $query = $this->db->query("SELECT COUNT(*) FROM $table WHERE fid = $fid $author");
        $result = (int) $this->db->result($query, 0);
        $this->db->free_result($query);

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
    public function addPost(array &$values, bool $quarantine = false, bool $qthread = false): int
    {
        // Required values:
        $ints = ['fid', 'tid', 'dateline'];
        $strings = ['author', 'message', 'subject', 'icon', 'usesig', 'useip', 'bbcodeoff', 'smileyoff'];

        $all = array_merge($ints, $strings);
        foreach($all as $field) if (! isset($values[$field])) throw new LogicException("Missing $field");
        foreach($ints as $field) if (! is_int($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
        foreach($strings as $field) {
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
            $result = $this->db->result($query, 0);
        } else {
            $result = '';
        }
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
        $result = (int) $this->db->result($query, 0);
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

        $db->query("UPDATE " . $this->tablepre . "posts SET usesig = '$yesno' WHERE author = '$username'");
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
        if (0 == (int) $this->db->result($query, 0)) {
            $this->db->query("INSERT INTO $table SET tid = $tid, username = '$sqluser', type = '$sqltype'");
        }
        $this->db->free_result($query);
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

        foreach($req as $field) if (! isset($values[$field])) throw new LogicException("Missing $field");
        foreach($ints as $field) {
            if (isset($values[$field])) {
                if (! is_int($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
            } else {
                $values[$field] = 0;
            }
        }
        foreach($strings as $field) {
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
    public function getOrphanedAttachments(bool $quarantine, int $pid = 0, ?int $uid = null): array
    {
        $result = [];

        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';
        
        $where = is_int($uid) ? "a.uid = $uid AND" : '';

        $query = $this->db->query("SELECT a.aid, a.pid, a.filename, a.filetype, a.filesize, a.downloads, a.img_size,
        thumbs.aid AS thumbid, thumbs.filename AS thumbname, thumbs.img_size AS thumbsize
        FROM $table AS a LEFT JOIN $table AS thumbs ON a.aid=thumbs.parentid WHERE $where a.pid = $pid AND a.parentid = 0");

        while ($row = $this->db->fetch_array($query)) {
            $result[] = $row;
        }
        $this->db->free_result($query);

        return $result;
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
        $results = [];

        $table = $quarantine ? $this->tablepre . 'hold_attachments' : $this->tablepre . 'attachments';

        $query = $this->db->query("SELECT aid, filesize, parentid FROM $table WHERE pid = $pid ORDER BY parentid");
        while($row = $this->db->fetch_array($query)) {
            $results[] = $row;
        }
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
        $count = (int) $this->db->result($query, 0);
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
        $count = (int) $this->db->result($query, 0);
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
        $count = (int) $this->db->result($query, 0);
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

        foreach($rows as $values) {
            foreach($req as $field) if (! isset($values[$field])) throw new LogicException("Missing $field");
            foreach($ints as $field) {
                if (isset($values[$field])) {
                    if (! is_int($values[$field])) throw new InvalidArgumentException("Type mismatch for $field");
                } else {
                    $values[$field] = 0;
                }
            }
            foreach($strings as $field) {
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
     * @since 1.9.12.07
     * @param int $vote_id The related vote_desc.vote_id value.
     * @param int $user_id The voter's numeric member ID.
     * @param string $user_ip The voter's IP address.
     * @return int Poll ID number.
     */
    public function addVoter(int $vote_id, int $user_id, string $user_ip)
    {
        $this->db->escape_fast($user_ip);

        $this->db->query("INSERT INTO " . $this->tablepre . "vote_voters (vote_id, vote_user_id, vote_user_ip) VALUES ($vote_id, $user_id, '$user_ip')");
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
            $id = (int) $this->db->result($query, 0);
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

        $ranks = [];
        while($row = $this->db->fetch_array($result)) {
            $ranks[] =& $row;
            unset($row);
        }

        $this->db->free_result($result);

        return $ranks;
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

        $smilies = [];
        while($row = $this->db->fetch_array($result)) {
            $smilies[] = &$row;
            unset($row);
        }

        $this->db->free_result($result);

        return $smilies;
    }

    /**
     * Retrieve the list of censors.
     *
     * @since 1.10.00
     * @return array of associative table rows.
    */
    public function getCensors(): array
    {
        $result = $this->db->query("SELECT * FROM " . $this->tablepre . "words");

        $words = [];
        while($row = $this->db->fetch_array($result)) {
            $words[] = &$row;
            unset($row);
        }

        $this->db->free_result($result);

        return $words;
    }

    /**
     * Retrieve the list of forums.
     *
     * Note the XMB class Forums service is used to cache this list and should be used instead of SQL in most situations.
     *
     * @since 1.10.00
     * @return array of associative table rows.
    */
    public function getForums(bool $activeOnly = true): array
    {
        if ($activeOnly) {
            $where = "WHERE status = 'on'";
        } else {
            $where = '';
        }
        
        $result = $this->db->query("SELECT * FROM " . $this->tablepre . "forums $where ORDER BY displayorder ASC");

        $forums = [];
        while($row = $this->db->fetch_array($result)) {
            $forums[(int) $row['fid']] = &$row;
            unset($row);
        }

        $this->db->free_result($result);

        return $forums;
    }

    /**
     * SQL command
     *
     * @since 1.10.00
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
            $addr = $this->db->result($query, 0);
        }
        $this->db->free_result($query);

        return $addr;
    }

    /**
     * Fetch the forum ID and theme based on a thread ID.
     *
     * @since 1.10.00
     * @param int $tid The thread ID number.
     * @return array The forum ID and theme.
     */
    public function getFIDFromTID(int $tid): array
    {
        $query = $this->db->query("SELECT f.fid, f.theme FROM " . $this->tablepre . "forums f RIGHT JOIN " . $this->tablepre . "threads t USING (fid) WHERE t.tid = $tid");

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
     * Count the unread U2U messages for a member.
     *
     * @since 1.10.00
     * @param string $username
     * @return int
     */
    public function countU2UInbox(string $username): int
    {
        $this->db->escape_fast($username);

        $query = $this->db->query("SELECT COUNT(*) FROM " . $this->tablepre . "u2u WHERE owner = '$username' AND folder = 'Inbox' AND readstatus = 'no'");

        $result = (int) $this->db->result($query, 0);
        $this->db->free_result($query);

        return $result;
    }
}
