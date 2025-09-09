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

namespace XMB\Session;

use XMB\Core;
use XMB\Password;
use XMB\SQL;
use XMB\Token;
use XMB\Validation;

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
    public function __construct(
        string $mode,
        private string $serror,
        private Core $core,
        private Password $password,
        private SQL $sql,
        private Token $token,
        private Validation $validate
    ) {
        $this->mechanisms = [
            new FormsAndCookies($core, $password, $sql, $token, $validate),
        ];

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
            switch ($this->status) {
                case 'good':
                    $username = $this->saved->member['username'];
                    break;
                case 'user-must-change-password':
                    // In this case, the client data were already deleted by $this->login(), so we override the value of $isSelf.
                    $isSelf = false;
                    $username = $this->saved->member['username'];
                    break;
                default:
                    return;
            }
        }
        foreach ($this->mechanisms as $session) {
            $session->logoutAll($username, $isSelf);
        }
    }

    /**
     * Forces creation of a session for a guest user who just completed registration.
     *
     * This is also suitable for a user who just completed a forced password change.
     */
    public function newUser(array $member)
    {
        unset($member['password'], $member['password2']);

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

            // Unset the password member, which is no longer needed for this session.
            $data->password = '';

            // Update the Mechanism
            if ('good' == $data->status) {
                if ($data->pwReset) {
                    // User record will remain available for this request, but session will not be saved.
                    $this->status = 'user-must-change-password';
                    $session->deleteClientData();
                } else {
                    // Login and session are both valid once saved.
                    $session->saveClientData($data);
                    $session->collectGarbage();
                }
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
