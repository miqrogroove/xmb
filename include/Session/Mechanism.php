<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
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
