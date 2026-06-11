<?php

/**
 * eXtreme Message Board
 * XMB 1.10
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2026, The XMB Group
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

use XMB\SQL;

/**
 * Experimental.
 */
class Certificates implements Mechanism
{
    // Mechanism configuration.
    private const USER_MIN_LEN = 3;

    public function __construct(
        private SQL $sql,
    ) {
        // Property promotion.
    }

    public function getServiceID(): string
    {
        return 'certificates';
    }

    public function checkUsername(): Data
    {
        $data = new Data();
        $uinput = $this->certUsernameConversion();

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
        return $data;
    }

    public function checkPassword(Data $data): Data
    {
        return $data;
    }

    public function checkClientEnabled(): bool
    {
        return true;
    }

    public function checkSavedSession(): Data
    {
        $data = new Data();

        $uinput = $this->certUsernameConversion();

        $member = $this->sql->getMemberByName($uinput);
        
        if (empty($member)) {
            $data->status = 'none';
            return $data;
        }
        
        $data->member = &$member;
        $data->status = 'good';
        return $data;
    }

    public function logout(): Data
    {
        $data = $this->checkSavedSession();

        return $data;
    }

    public function logoutAll(string $username, bool $current_client)
    {
        return;
    }

    /**
     * Delete tokens from client.
     *
     * This is called directly by the Session Manager for login and resume modes when authentication fails.
     * Responsibility for calling this is delegated to the logout method for logout mode.
     */
    public function deleteClientData()
    {
        return;
    }

    /**
     * Creates a new session token and cookies for a client who authenticated during this request.
     *
     * @param Data
     */
    public function saveClientData(Data $data): bool
    {
        return true;
    }

    /**
     * Deletes all expired tokens in the sessions table.
     */
    public function collectGarbage()
    {
        return;
    }

    /**
     * Retrieve list of all valid sessions for the current user.
     *
     * @param string $username
     * @return array
     */
    public function getSessionList(string $username): array
    {
        $sessions = [];

        return $sessions;
    }

    public function logoutByList(string $username, array $selection)
    {
        return;
    }

    /**
     * This event occurs when the client visits the login page to get ready for a login.
     *
     * @since 1.10.00
     */
    public function preLogin(string $newToken)
    {
        return;
    }

    /**
     * Check the origin of the login request to verify it has not been injected by a different domain.
     *
     * @since 1.10.00
     * @return bool
     */
    public function checkOrigin(): bool
    {
        return true;
    }

    private function certUsernameConversion(): string
    {
        $uinput = $this->get_cookie(self::USER_COOKIE);

        return $uinput;
    }
}
