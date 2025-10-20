<?php

/**
 * eXtreme Message Board
 * XMB 1.10.01
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
use RuntimeException;

class Token
{
    public function __construct(private SQL $sql, private Variables $vars)
    {
        // Property promotion
    }

    /**
     * Generate a nonce for the current user.
     *
     * Offers user uniqueness and better purpose matching.
     * Replaces \nonce_create() for everything other than the captcha system.
     *
     * @since 1.9.12
     * @param string $action The known value or purpose, such as what the nonce may be used for.  Verbose string between 5 and 32 chars required.
     * @param string $object Detailed ID of the specific item that may be used.  Empty string allowed, e.g. for object creation.
     * @param int    $ttl    Validity time in seconds.
     * @param bool   $anonymous Optional. Must be true if intentionally setting a token for a guest user.  Useful for lost passwords.
     * @return string
     */
    function create(string $action, string $object, int $ttl, bool $anonymous = false): string
    {
        if ('' == $this->vars->self['username'] && ! $anonymous) throw new LogicException('Username missing');

        if (strlen($action) > 32 || strlen($action) < 5 || strlen($object) > 32) throw new InvalidArgumentException('String length out of limit');

        $token = bin2hex(random_bytes(16));
        $expires = time() + $ttl;

        $success = $this->sql->addToken($token, $this->vars->self['username'], $action, $object, $expires);

        if (! $success) {
            // Retry once.
            $token = bin2hex(random_bytes(16));
            $success = $this->sql->addToken($token, $this->vars->self['username'], $action, $object, $expires);
        }

        if (! $success) throw new RuntimeException('XMB was unable to save a new session token');

        return $token;
    }

    /**
     * Test a nonce for the current user.
     *
     * Offers user uniqueness and better purpose matching.
     * Replaces \nonce_use() for everything other than the captcha system.
     *
     * @since 1.9.12
     * @param string $token  The user input.
     * @param string $action The same value used in create().
     * @param string $object The same value used in create().
     * @return bool True only if the user provided a unique nonce for the action/object pair.
     */
    function consume(string $token, string $action, string $object): bool
    {
        $username = $this->vars->self['username'] ?? '';

        $this->sql->deleteTokensByDate(time());

        return $this->sql->deleteToken($token, $username, $action, $object);
    }
}
