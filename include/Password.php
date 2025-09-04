<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-2
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
 * User password handler and abstractions.
 *
 * @since 1.10.00
 */
class Password
{
    public const DEFAULT_LENGTH = 20; // For password generation.
    public const MIN_LENGTH = 8; // New passwords may not be shorter than this.
    public const MAX_LENGTH = 72; // Hash input maximum to avoid truncation.

    private const ALGO = PASSWORD_DEFAULT;

    public function __construct(private SQL $sql)
    {
        // Property promotion
    }

    /**
     * Determine if the provided hash is not of the type currently produced by the hashPassword() method.
     */
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
     * @param string $storedHash Must be the members.password or members.password2 value.
     * @param string $username Must be the members.username value.
     * @param bool $changeCapable Whether the schema is ready for the change() method.
     * @return string Values 'good', 'bad', or 'must-change'.
     */
    public function checkLogin(
        #[\SensitiveParameter]
        string $rawPass,
        string $storedHash,
        string $username,
        bool $changeCapable,
    ): string {
        if (strlen($rawPass) == 0) throw new InvalidArgumentException('The XMB Password class does not accept empty inputs.');

        if ($this->isObsolete($storedHash)) {
            if (strlen($storedHash) == 32) {
                // Use MD5.
                $result = $storedHash === md5($rawPass);
            } else {
                // Use the modern system.
                $result = password_verify($rawPass, $storedHash);
            }
            if ($result && strlen($rawPass) <= $this::MAX_LENGTH && $changeCapable) {
                // Automatically regenerate the hash when the hash is obsolete and the schema is not obsolete.
                $this->change($username, $rawPass);
            }
        } else {
            $result = password_verify($rawPass, $storedHash);
        }
        if ($result) {
            if ($changeCapable && (strlen($rawPass) < $this::MIN_LENGTH || strlen($rawPass) > $this::MAX_LENGTH)) {
                $status = 'must-change';
            } else {
                $status = 'good';
            }
        } else {
            $status = 'bad';
        }
        return $status;
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
    public function change(
        string $username,
        #[\SensitiveParameter]
        string $rawPass,
    ) {
        if (strlen($username) == 0) throw new InvalidArgumentException('The XMB Password class does not accept empty inputs.');
        if (strlen($rawPass) == 0) throw new InvalidArgumentException('The XMB Password class does not accept empty inputs.');
        if (strlen($rawPass) > $this::MAX_LENGTH) throw new InvalidArgumentException('The password input was invalid due to excessive length.');

        // Save modern hash.
        $newHash = $this->hash($rawPass);
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
    public function hash(
        #[\SensitiveParameter]
        string $rawPass,
    ): string {
        if (strlen($rawPass) == 0) throw new InvalidArgumentException('The XMB Password class does not accept empty inputs.');
        if (strlen($rawPass) > $this::MAX_LENGTH) throw new InvalidArgumentException('The password input was invalid due to excessive length.');

        // Compute modern hash and return.
        return password_hash($rawPass, $this::ALGO);
    }

    /**
     * Checks a new password against requirements and returns an error code or an empty string.
     *
     * @since 1.10.00
     * @param string $password1
     * @param string $password2
     * @return string Error code, or empty string on success.
     */
    public function checkPolicy(
        #[\SensitiveParameter]
        string $password1,
        #[\SensitiveParameter]
        string $password2,
    ): string {
        $error = '';
        if ('' == $password1) {
            $error = 'textnopassword';
        } elseif (strlen($password1) < $this::MIN_LENGTH) {
            $error = 'pwtooshort';
        } elseif (strlen($password1) > $this::MAX_LENGTH) {
            $error = 'pwtoolong';
        } elseif ($password1 !== $password2) {
            $error = 'pwnomatch';
        }

        return $error;
    }

    /**
     * Generates a random password.
     */
    public function generate(): string
    {
        $newPass = '';
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz~!@#$%^&*()+";
        $maxIndex = strlen($chars) - 1;
        for ($i = 0; $i < $this::DEFAULT_LENGTH; $i++) {
            $newPass .= $chars[random_int(0, $maxIndex)];
        }

        return $newPass;
    }
}
