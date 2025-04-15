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

namespace XMB;

use InvalidArgumentException;
use SensitiveParameter;

/**
 * User password handler and abstraction.
 *
 * @since 1.10.00
 */
class Password
{
    public const MAX_LENGTH = 72;

    private const ALGO = PASSWORD_DEFAULT;

    /**
     * When creating a Password object, the stored credentials are required.
     *
     * The $storedHash value may be empty for the sake of new users and lost password situations only.
     *
     * @param string $storedHash Must be the members.password value.
     * @param SQL $sql
     */
    public function __construct(private SQL $sql)
    {
        // Property promotion
    }

    private function isObsolete(string $storedHash): bool
    {
        return password_needs_rehash($storedHash, $this::ALGO);
    }

    /**
     * Determine if password input was valid for a new session.
     *
     * Automatically regenerates the stored hash of an existing password as needed.
     * This method is binary-safe.
     *
     * @param string $rawPass Must be the raw input.
     * @return bool
     */
    public function checkInput(
        #[\SensitiveParameter]
        string $rawPass,
        string $storedHash,
    ): bool {
        if (strlen($rawPass) == 0) throw new InvalidArgumentException('The XMB Password class does not accept empty inputs.');

        if ($this->isObsolete($storedHash)) {
            if (strlen($storedHash) == 32) {
                // Use MD5.
                $result = $storedHash === md5($rawPass);
            } else {
                // Use the modern system.
                $result = password_verify($rawPass, $storedHash);
            }
            if ($result && strlen($rawPass) <= $this::MAX_LENGTH) {
                // Automatically regenerate the hash.
                $this->changePassword($rawPass);
            }
        } else {
            $result = password_verify($rawPass, $storedHash);
        }
        if ($result && strlen($rawPass) > $this::MAX_LENGTH) {
            // TODO: Force a manual password change.
        }
        return $result;
    }
    
    /**
     * Save a new password for the specified user.
     *
     * The caller is responsible for checking user or admin authorization to make this change.
     * This method is binary-safe.
     *
     * @param string $username Must be HTMl encoded, as in members.username.
     * @param string $rawPass Must be the raw input.
     */
    public function changePassword(
        string $username,
        #[\SensitiveParameter]
        string $rawPass,
    ) {
        if (strlen($username) == 0) throw new InvalidArgumentException('The XMB Password class does not accept empty inputs.');
        if (strlen($rawPass) == 0) throw new InvalidArgumentException('The XMB Password class does not accept empty inputs.');
        if (strlen($rawPass) > $this::MAX_LENGTH) throw new InvalidArgumentException('The password input was invalid due to excessive length.');

        // Save modern hash.
        $newHash = $this->hashPassword($rawPass);
        $this->sql->setNewPassword($username, $newHash);
    }

    /**
     * Hash a raw password.
     *
     * This method is binary-safe.
     *
     * @param string $rawPass Must be the raw input.
     * @return string The hash, expected to be 60 to 128 chars in length.
     */
    public function hashPassword(
        #[\SensitiveParameter]
        string $rawPass,
    ): string {
        if (strlen($rawPass) == 0) throw new InvalidArgumentException('The XMB Password class does not accept empty inputs.');
        if (strlen($rawPass) > $this::MAX_LENGTH) throw new InvalidArgumentException('The password input was invalid due to excessive length.');

        // Compute modern hash and return.
        return password_hash($rawPass, $this::ALGO);
    }
}
