<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
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

namespace XMB\Session;

use RuntimeException;
use XMB\Core;
use XMB\Password;
use XMB\SQL;
use XMB\Token;

use function XMB\formYesNo;
use function XMB\getPhpInput;
use function XMB\getRawString;

/**
 * Session Data objects are used to pass results between functions.
 *
 * @since 1.9.12
 */
class Data
{
    public array $member = [];      // Must be the member record array from the database, or an empty array.
    public bool $permanent = false; // True if the session should be saved by the client, otherwise false.
    public string $status = 'none'; // Session input level.  Must be 'good', 'bad', or 'none'.
}

/**
 * The Session Manager provides Session Data, and it coordinates authentication
 * and persistence of browser tokens.
 *
 * The actual mechanisms of authentication and session persistence are defined
 * in separate Mechanism classes to create a high degree of flexibility.
 *
 * @since 1.9.12
 */
class Manager
{
    private array $mechanisms;
    private string $status = ''; // See getters for details.
    private Data $saved;

    /**
     * @param string $mode Must be one of 'login', 'logout', 'resume', or 'disabled'.
     * @param string $serror Condition prior to authentication.
     */
    public function __construct(string $mode, private string $serror, private Core $core, SQL $sql, private Token $token)
    {
        $this->mechanisms = [new FormsAndCookies($core, $sql, $token)];

        switch ($mode) {
        case 'login':
            $this->login();
            break;
        case 'logout':
            $this->logout();
            break;
        case 'disabled':
            $this->status = 'session-no-input';
            $this->saved = new Data();
            break;
        case 'resume':
        default:
            $this->resume();
            break;
        }
    }

    /**
     * Session Status
     *
     * Returns 'good' when the login mode or resume mode is successful.
     * Otherwise, various error codes that must be handled by the caller.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Member Record
     *
     * Provides the member array from the database, or an empty array.
     */
    public function getMember(): array
    {
        return $this->saved->member;
    }

    /**
     * Pre-session Errors
     *
     * Any error condition, prior to authentication, that was caused by global
     * settings (ip ban, maintenance mode, members only, etc) is stored here.
     *
     * The Session Manager saves this value and later passes it back to the
     * authorization and escalation logic.  This class has no other knowledge
     * about the meaning of this value.
     *
     * @since 1.10.00
     */
    public function getSError(): string
    {
        return $this->serror;
    }

    /**
     * Session Lists
     *
     * Provides an array of arrays, indexed by the name of each Mechanism.
     */
    public function getSessionLists(): array
    {
        $lists = [];
        if ('good' == $this->status) {
            foreach ($this->mechanisms as $session) {
                $lists[get_class($session)] = $session->getSessionList($this->saved->member['username']);
            }
        }
        return $lists;
    }

    /**
     * Deletes tokens for specific sessions selected by the current user.
     *
     * @param array $selection Should be structured similar to the return of getSessionLists().
     */
    public function logoutByLists(array $selection)
    {
        if ('good' != $this->status) return;

        foreach ($this->mechanisms as $session) {
            $name = get_class($session);
            if (! empty($selection[$name])) {
                $session->logoutByList($this->saved->member['username'], $selection[$name]);
            }
        }
    }

    /**
     * Deletes all tokens for all sessions after the user sets new credentials.
     *
     * @param string $username
     * @param bool $isSelf Whether the client should be cleared also.  For example, not when the admin is editing someone else's password.
     */
    public function logoutAll(string $username = '', bool $isSelf = true)
    {
        if ('' == $username) {
            if ('good' == $this->status) {
                $username = $this->saved->member['username'];
            } else {
                return;
            }
        }
        foreach ($this->mechanisms as $session) {
            $session->logoutAll($username, $isSelf);
        }
    }

    /**
     * Forces creation of a session for a guest user who just completed registration.
     */
    public function newUser(array $member)
    {
        $data = new Data();
        $data->member =& $member;
        foreach ($this->mechanisms as $session) {
            if ($session->saveClientData($data)) {
                break;
            }
        }
    }

    /**
     * Initialize the Session Manager for a login action.
     */
    private function login()
    {
        $this->status = 'login-no-input';

        // First, check that all mechanisms are working and not already in a session.
        foreach ($this->mechanisms as $session) {
            $data = $session->checkSavedSession();
            if ($data->status == 'good') {
                $this->status = 'already-logged-in';
                $this->saved = $data;
                return;
            }
            if (! $session->checkClientEnabled()) {
                $this->status = 'login-client-disabled';
                $this->saved = new Data();
                return;
            }
        }

        // Next, authenticate the login.
        foreach ($this->mechanisms as $session) {
            // Fetch the user record
            $data = $session->checkUsername();

            // Check for errors
            if ('good' == $data->status) {
                if (! $session->checkOrigin()) {
                    $this->status = 'origin-check-fail';
                    $this->saved = new Data();
                    return;
                }

                // Before we even authenticate the user, check if the account is authorized for login.
                $this->status = $this->core->loginAuthorization($data->member, $this->serror);
                if ('good' != $this->status) {
                    $data->status = 'bad';
                }
            } elseif ('bad' == $data->status) {
                $this->status = 'bad-username';
            } else {
                // Nothing happened.
            }

            // Authenticate
            if ('good' == $data->status) {
                $data = $session->checkPassword($data);
                if ('good' != $data->status) {
                    $this->status = 'bad-password';
                }
            }

            // Update the Mechanism
            if ('good' == $data->status) {
                $session->saveClientData($data);
                $session->collectGarbage();
                break;
            } elseif ('bad' == $data->status) {
                $session->deleteClientData();
                break;
            } else {
                // Try any remaining Mechanisms.
                continue;
            }
        }

        // Save the results
        $this->saved = $data;
    }

    /**
     * Initialize the Session Manager for a logout action.
     */
    private function logout()
    {
		$this->saved = new Data();
        foreach ($this->mechanisms as $session) {
            $data = $session->logout();
            if ($data->status == 'none') {
                continue;
            } elseif ($data->status == 'logged-out') {
                $this->status = 'logged-out';
                $this->saved = $data;
                break;
            } else {
                // Still logged in.
                $this->status = 'good';
                $this->saved = $data;
                break;
            }
        }
    }

    /**
     * Initialize the Session Manager for a new or existing session.
     *
     * Assumes it is only possible to login one mechanism at a time.
     */
    private function resume()
    {
        $this->status = 'session-no-input';

        // Authenticate any session token.
        foreach ($this->mechanisms as $session) {
            $data = $session->checkSavedSession();

            // Check for errors
            if ('good' == $data->status) {
                // We have authentication, now check authorization.
                $this->status = $this->core->loginAuthorization($data->member, $this->serror);
                if ('good' != $this->status) {
                    $data->status = 'bad';
                }
            } elseif ('bad' == $data->status) {
                $this->status = 'invalid-session';
            }

            // Update the Mechanism
            if ('good' == $data->status) {
                // Current session found.  Done looping.
                break;
            } elseif ('bad' == $data->status) {
                $session->deleteClientData();
                continue;
            } else {
                // XMB does not actively track guest devices.  We just need to know if cookies are enabled.
                $session->checkClientEnabled();
                continue;
            }
        }

        // Save the results
        $this->saved = $data;
    }

    /**
     * This event occurs when the client visits the login page to get ready for a login.
     *
     * @since 1.10.00
     * @param string $newToken A copy of the CSRF token used in the login template.
     */
    public function preLogin(string $newToken)
    {
        foreach ($this->mechanisms as $session) {
            $session->preLogin($newToken);
        }
    }
}

/**
 * A Session Mechanism is an abstraction that handles the tasks of authenticating 
 * credentials and saving the client's session token.
 *
 * Historically, XMB always used Form Data and Cookies to do this.  For added
 * flexibility, this interface can be implemented by any number of technologies
 * that might be used separately or simultaneously.
 *
 * Note, however, the Session Manager assumes that a client may only login using
 * one Mechanism.  Carefully consider this point when extending the session system.
 *
 * @since 1.9.12
 */
interface Mechanism
{
    /**
     * Did the client provide a valid ID that matches an XMB member?
     *
     * Called only when a guest client tries to login from an XMB native authentication system.
     * Foreign account systems should always treat this as a user navigation error.
     *
     * @return Data
     */
    public function checkUsername(): Data;

    /**
     * Did the client provide a valid secret that matches the ID?
     *
     * Called only when a guest client tries to login from an XMB native authentication system.
     * Foreign account systems should always treat this as a user navigation error.
     *
     * @return Data
     */
    public function checkPassword(Data $data): Data;

    /**
     * Did the client respond to this mechanism in any previous visit?
     *
     * For example, does the client support cookies, or not?
     * If the mechanism is incapable of testing support
     * prior to login, then it should return true.
     *
     * @return bool
     */
    public function checkClientEnabled(): bool;

    /**
     * Did the client provide a session token, and is it valid?
     *
     * Any mechanism that is not tokenized will implement its own
     * logic to validate and retrieve the XMB user record.
     *
     * Foreign account systems will consider the need to
     * auto-register any user who is not yet an XMB member.
     *
     * @return Data
     */
    public function checkSavedSession(): Data;

    /**
     * Delete tokens from both client and server for logout.
     *
     * Most mechanisms should have a logout method.
     * One exception is client certificate authentication, where
     * both clients and servers tend to have poor facilities
     * for ending a session.  In that case, the website
     * should return the object from checkSavedSession()
     * and provide instructions to manually shut down the client.
     *
     * @return Data
     */
    public function logout(): Data;
    
    /**
     * Delete all tokens for all sessions after setting new credentials.
     *
     * The mechanism must clear all tokens associated with this member.
     *
     * @param string $username The member being deleted from the sessions table.
     * @param bool $current_client Is it safe to call deleteClientData() for the current session?
     */
    public function logoutAll(string $username, bool $current_client);

    /**
     * Delete tokens from client for logout.
     *
     * When a session is already expired or invalid, we only need to
     * clean up the client side of the session.
     *
     * Any mechanism that does not send XMB session data to the client
     * might have an empty implementation.
     */
    public function deleteClientData();

    /**
     * Create and send tokens to client for login.
     *
     * Any mechanism that does not send XMB session data to the client
     * must return false.
     *
     * @param Data
     * @return bool The mechanism must indicate whether it is capable of doing this.
     */
    public function saveClientData(Data $data): bool;
    
    /**
     * Delete all records of expired sessions for all users.
     */
    public function collectGarbage();
    
    /**
     * Retrieve list of all valid sessions for the current user.
     *
     * Each mechanism may customize the structure of its list.
     */
    public function getSessionList(string $username): array;

    /**
     * Delete all tokens for specified sessions.
     *
     * Each mechanism may customize the structure of its list.
     */
    public function logoutByList(string $username, array $selection);

    /**
     * This event occurs when the client visits the login page to get ready for a login.
     *
     * @since 1.10.00
     * @param string $newToken A copy of the CSRF token used in the login template.
     */
    public function preLogin(string $newToken);

    /**
     * Check the origin of the login request to verify it has not been injected by a different domain.
     *
     * @since 1.10.00
     * @return bool
     */
    public function checkOrigin(): bool;
}

/**
 * Cookies are the default client storage system for session tokens in all versions of XMB.
 *
 * @since 1.9.12
 */
class FormsAndCookies implements Mechanism
{
    // Mechanism configuration.
    const FORM_EXP = 3600;
    const REGEN_AFTER = 3600;
    const REGEN_ENABLED = true;
    const SESSION_LIFE_LONG = 86400 * 30;
    const SESSION_LIFE_SHORT = 3600 * 12;
    const TEST_DATA = 'xmb';
    const TOKEN_BYTES = 16;
    const USER_MIN_LEN = 3;

    // Cookie names.
    const FORM_COOKIE = 'login';
    const REGEN_COOKIE = 'id2';
    const SESSION_COOKIE = 'xmbpw';
    const TEST_COOKIE = 'test';
    const USER_COOKIE = 'xmbuser';

    public function __construct(private Core $core, private SQL $sql, private Token $token)
    {
        // Property promotion.
    }

    public function checkUsername(): Data
    {
        $data = new Data();
        $uinput = $this->core->postedVar('username', dbescape: false);

        if (strlen($uinput) < self::USER_MIN_LEN) {
            return $data;
        }

        $member = $this->sql->getMemberByName($uinput);

        if (empty($member)) {
            $data->status = 'bad';
            return $data;
        }

        $data->member = &$member;
        $data->status = 'good';
        $data->permanent = formYesNo('trust') == 'yes';
        return $data;
    }

    public function checkPassword(Data $data): Data
    {
        $pinput = getRawString('password');

        if (empty($pinput)) {
            return new Data();
        }

        $passMan = new Password($this->sql);
        $storedPass = $data->member['password'] !== '' ? $data->member['password'] : $data->member['password2'];

        if (! $passMan->checkInput($pinput, $storedPass)) {
            $this->core->auditBadLogin($data->member);
            $data = new Data();
            $data->status = 'bad';
            return $data;
        }

        return $data;
    }

    public function checkClientEnabled(): bool
    {
        $uinput = $this->get_cookie(self::USER_COOKIE);
        $test   = $this->get_cookie(self::TEST_COOKIE);

        if (strlen($uinput) >= self::USER_MIN_LEN || self::TEST_DATA == $test) {
            return true;
        } else {
            $this->core->put_cookie('test', self::TEST_DATA, time() + (86400*365));
            return false;
        }
    }

    public function checkSavedSession(): Data
    {
        $data = new Data();

        $pinput = $this->get_cookie(self::SESSION_COOKIE);
        $uinput = $this->get_cookie(self::USER_COOKIE);

        if (strlen($uinput) < self::USER_MIN_LEN || strlen($pinput) != self::TOKEN_BYTES * 2) {
            $data->status = 'none';
            return $data;
        }
        
        $member = $this->sql->getMemberByName($uinput);
        
        if (empty($member)) {
            $data->status = 'none';
            return $data;
        }
        
        $member['password'] = '';
        
        $details = $this->sql->getSession($pinput, $uinput);

        if (empty($details)) {
            $this->core->auditBadSession($member);
            $data->status = 'bad';
            return $data;
        }
        
        if (time() > (int) $details['expire']) {
            $this->core->auditBadSession($member);
            $data->status = 'bad';
            return $data;
        }
        
        // Token Regeneration
        if (self::REGEN_ENABLED) {
            // Figure out where we are in the regeneration cycle.
            $cookie2 = $this->get_cookie(self::REGEN_COOKIE);
            if ($cookie2 != '' && $cookie2 === $details['replaces']) {
                // Normal: Client responded with both the new token and the old token. Ready to delete old token.
                $this->sql->deleteSession($details['replaces']);
                $this->sql->clearSessionParent($details['token']);
                $details['replaces'] = '';
                $this->delete_cookie(self::REGEN_COOKIE);
            } elseif ($details['replaces'] != '') {
                // Abnormal: Client responded with the new token but doesn't posess the current (old) token.
                // Regeneration is compromised.  Both tokens must be destroyed.
                $this->sql->deleteSession($details['replaces']);
                $this->sql->deleteSession($details['token']);
                $this->core->auditBadSession($member);
                $data->status = 'bad';
                return $data;
            } elseif (time() > (int) $details['regenerate']) {
                // Current session needs to be regenerated.
                $newdetails = $this->sql->getSessionReplacement($pinput, $uinput);
                if (empty($newdetails)) {
                    // Normal: This is the first stale hit. New token is needed.
                    $this->regenerate($details);
                } else {
                    // Abnormal: Client responded with old token after new token was created.
                    // Caused by interruption or race conditions.
                    $this->recover($newdetails);
                }
            } elseif ($cookie2 != '') {
                // Abnormal: Client responded with both tokens after the old token was deleted.
                // Caused by interruption or race conditions.
                $this->delete_cookie(self::REGEN_COOKIE);
            } else {
                // Current session is stable.
            }
        }

        $data->member = &$member;
        $data->status = 'good';
        return $data;
    }

    public function logout(): Data
    {
        $data = $this->checkSavedSession();

        if ('good' == $data->status) {
            $token = $this->get_cookie(self::SESSION_COOKIE);
            $child = $this->sql->getSessionReplacement($token, $data->member['username']);

            $this->sql->deleteSession($token);
            if (! empty($child)) {
                $this->sql->deleteSession($child['token']);
            }
            $this->deleteClientData();
            $data->status = 'logged-out';
            // $data->member passes through so the manager knows who is logging out.
        } elseif ('bad' == $data->status) {
            $this->deleteClientData();
            $data = new Data();
        } else {
            // There was no session.
        }

        return $data;
    }

    public function logoutAll(string $username, bool $current_client)
    {
        $this->sql->deleteSessionsByName($username);
        if ($current_client) {
            $this->deleteClientData();
        }
    }

    public function deleteClientData()
    {
        $this->delete_cookie(self::REGEN_COOKIE);
        $this->delete_cookie(self::SESSION_COOKIE);
        $this->delete_cookie(self::USER_COOKIE);
        $this->delete_cookie('oldtopics');
        $this->delete_cookie('xmblva');
        $this->delete_cookie('xmblvb');

        foreach($_COOKIE as $key=>$val) {
            if (preg_match('#^fidpw([0-9]+)$#', $key)) {
                $this->delete_cookie($key);
            }
        }

        // Remember to check that these cookies will not be reset after initializing the session.
        // Maybe poison the function put_cookie() itself.
    }

    /**
     * Creates a new session token and cookies for a client who authenticated during this request.
     *
     * @param Data
     */
    public function saveClientData(Data $data): bool
    {
        // Create a new session here.
        $token = bin2hex(random_bytes(self::TOKEN_BYTES));

        if ($data->permanent) {
            $expires = time() + self::SESSION_LIFE_LONG;
        } else {
            $expires = time() + self::SESSION_LIFE_SHORT;
        }

        $regenerate = time() + self::REGEN_AFTER;

        $replaces = '';

        $agent = '';
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $agent = substr($_SERVER['HTTP_USER_AGENT'], 0, 255);
        }

        $success = $this->sql->saveSession($token, $data->member['username'], time(), $expires, $regenerate, $replaces, $agent);

        if (! $success) {
            // Retry once.
            $token = bin2hex(random_bytes(self::TOKEN_BYTES));
            $success = $this->sql->saveSession($token, $data->member['username'], time(), $expires, $regenerate, $replaces, $agent);
        }

        if (! $success) {
            throw new RuntimeException('XMB was unable to save a new session token.');
        }

        if (! $data->permanent) {
            $expires = 0;
        }

        $this->core->put_cookie(self::USER_COOKIE, $data->member['username'], $expires);
        $this->core->put_cookie(self::SESSION_COOKIE, $token, $expires);
        
        return true;
    }

    /**
     * Creates a new session token and cookies for a client whose session has become stale.
     *
     * @param array $oldsession
     */
    private function regenerate(array $oldsession)
    {
        $token = bin2hex(random_bytes(self::TOKEN_BYTES));

        $regenerate = time() + self::REGEN_AFTER;

        $replaces = $oldsession['token'];

        $agent = '';
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $agent = substr($_SERVER['HTTP_USER_AGENT'], 0, 255);
        }

        $success = $this->sql->saveSession($token, $oldsession['username'], (int) $oldsession['login_date'], (int) $oldsession['expire'], $regenerate, $replaces, $agent);

        if (! $success) {
            // Retry once.
            $token = bin2hex(random_bytes(self::TOKEN_BYTES));
            $success = $this->sql->saveSession($token, $oldsession['username'], (int) $oldsession['login_date'], (int) $oldsession['expire'], $regenerate, $replaces, $agent);
        }

        if (! $success) {
            throw new RuntimeException('XMB was unable to save a new session token.');
        }

        if ((int) $oldsession['expire'] > time() + self::SESSION_LIFE_SHORT) {
            $expires = (int) $oldsession['expire'];
        } else {
            $expires = 0;
        }

        $this->core->put_cookie(self::USER_COOKIE, $oldsession['username'], $expires);
        $this->core->put_cookie(self::SESSION_COOKIE, $token, $expires);
        $this->core->put_cookie(self::REGEN_COOKIE, $replaces, $expires);
    }

    /**
     * Resets session cookies for a client who has authenticated but lost the regeneration data.
     *
     * @param array $newsession
     */
    private function recover(array $newsession)
    {
        if ((int) $newsession['expire'] > time() + self::SESSION_LIFE_SHORT) {
            $expires = (int) $newsession['expire'];
        } else {
            $expires = 0;
        }

        $this->core->put_cookie(self::USER_COOKIE, $newsession['username'], $expires);
        $this->core->put_cookie(self::SESSION_COOKIE, $newsession['token'], $expires);
        $this->core->put_cookie(self::REGEN_COOKIE, $newsession['replaces'], $expires);
    }

    /**
     * Deletes all expired tokens in the sessions table.
     */
    public function collectGarbage()
    {
        $this->sql->deleteSessionsByDate(time());
    }

    /**
     * Retrieve list of all valid sessions for the current user.
     *
     * @param string $username
     * @return array
     */
    public function getSessionList(string $username): array
    {
        global $db;

        $sessions = [];
        $pinput = $this->get_cookie(self::SESSION_COOKIE);

        $result = $this->sql->getSessionsByName($username);
        while ($session = $db->fetch_array($result)) {
            if ((int) $session['expire'] < time()) {
                continue;
            }
            $session['current'] = ($pinput == $session['token'] || $pinput == $session['replaces']);
            $session['token'] = substr($session['token'], 0, 4);
            unset($session['replaces']);
            $sessions[] = $session;
        }
        $db->free_result($result);

        return $sessions;
    }

    public function logoutByList(string $username, array $selection)
    {
        $pinput = $this->get_cookie(self::SESSION_COOKIE);
        if (! empty($selection)) {
            $this->sql->deleteSessionsByList($username, $selection, $pinput);
        }
    }

    /**
     * This event occurs when the client visits the login page to get ready for a login.
     *
     * @since 1.10.00
     */
    public function preLogin(string $newToken)
    {
        $this->core->put_cookie(self::FORM_COOKIE, $newToken, expire: time() + self::FORM_EXP);
    }

    /**
     * Check the origin of the login request to verify it has not been injected by a different domain.
     *
     * @since 1.10.00
     * @return bool
     */
    public function checkOrigin(): bool
    {
        // Due to the anonymous nature of a login request, we need to check both the form integrity and the cookie integrity.
        $cookieToken = $this->get_cookie(self::FORM_COOKIE);
        $postToken = getPhpInput('token');
        $this->delete_cookie(self::FORM_COOKIE);
        
        if ($cookieToken != $postToken) return false;
        
        return $this->token->consume($postToken, 'Login', '');
    }

    private function get_cookie(string $name): string
    {
        return getPhpInput($name, sourcearray: 'c');
    }

    private function delete_cookie(string $name)
    {
        if ($this->get_cookie($name) != '') {
            $this->core->put_cookie($name);
        }
    }
}
