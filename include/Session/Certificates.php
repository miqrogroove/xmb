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

use RuntimeException;
use XMB\Core;
use XMB\SQL;
use XMB\Validation;

use function XMB\getPhpInput;

/**
 * Certificate-based authentication for use with Apache mod_ssl.
 *
 * @since 1.10.07
 */
class Certificates implements Mechanism
{
    public function __construct(
        private Core $core,
        private readonly bool $debug,
        private SQL $sql,
        private Validation $validate,
    ) {
        // Property promotion.
    }

    public function getServiceID(): string
    {
        return 'certificates';
    }

    public function isLoginSupported(): bool
    {
        // Form login is not enabled for certificate sessions.
        return false;
    }

    public function checkUsername(): Data
    {
        // Form login is not enabled for certificate sessions.
        $data = new Data();
        return $data;
    }

    public function checkPassword(Data $data): Data
    {
        // Form login is not enabled for certificate sessions.
        return $data;
    }

    public function checkClientEnabled(): bool
    {
        // There are no checks available for client certificate sessions.
        return true;
    }

    public function checkSavedSession(): Data
    {
        if ($this->debug) {
            $this->assertServerEnabled();
        }

        $data = new Data();
        $uinput = $this->certUsernameConversion();

        if (! $this->core->checkUsernameLength($uinput)) {
            $data->status = 'none';
            return $data;
        }

        $member = $this->sql->getMemberByName($uinput);

        if (empty($member)) {
            $data->status = 'none';
            return $data;
        }

        $data->member = &$member;
        $data->status = 'good';
        $data->canLogout = false;

        return $data;
    }

    public function logout(): Data
    {
        // Logout is not enabled for certificate sessions.
        $data = $this->checkSavedSession();

        return $data;
    }

    public function logoutAll(string $username, bool $current_client)
    {
        // Logout is not enabled for certificate sessions.
        return;
    }

    public function deleteClientData()
    {
        // Client updates are not enabled for certificate sessions.
        return;
    }

    public function saveClientData(Data $data): bool
    {
        // Client updates are not enabled for certificate sessions.
        return false;
    }

    public function collectGarbage()
    {
        // There is nothing to clean up for certificate sessions.
        return;
    }

    public function getSessionList(string $username): array
    {
        // Only the current session is available for certificate sessions.
        $agent = $this->validate->postedVar(
            varname: 'HTTP_USER_AGENT',
            dbescape: false,
            sourcearray: 's',
        );
        if (strlen($agent) > 255) {
            $agent = substr($agent, 0, 255);
        }

        $session = [
            'token' => '',
            'login_date' => (string) time(),
            'agent' => $agent,
            'name' => '',
            'current' => true,
        ];

        return [$session];
    }

    public function logoutByList(string $username, array $selection)
    {
        // Logout is not enabled for certificate sessions.
        return;
    }

    public function preLogin(string $newToken)
    {
        // Form login is not enabled for certificate sessions.
        return;
    }

    public function checkOrigin(): bool
    {
        // Form login is not enabled for certificate sessions.
        return false;
    }

    /**
     * Retrieve the username from the certificate and perform any conversion needed.
     */
    private function certUsernameConversion(): string
    {
        if (getPhpInput('SSL_CLIENT_VERIFY', 's') !== 'SUCCESS') {
            return '';
        }
        $uinput = $this->validate->postedVar(
            varname: 'SSL_CLIENT_S_DN_CN',
            dbescape: false,
            sourcearray: 's',
        );

        return $uinput;
    }

    /**
     * Generate errors if Apache isn't configured for client certificates.
     */
    private function assertServerEnabled()
    {
        if (! isset($_SERVER['SSL_PROTOCOL'])) {
            echo 'XMB session configuration is incorrect. Check the PHP error log for details.';
            throw new RuntimeException('The mod_ssl StdEnvVars option is not enabled.');
        }
    }
}
