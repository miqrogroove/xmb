<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2021, The XMB Group
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
 *
 **/

declare(strict_types=1);

namespace XMB\Token;

if (!defined('IN_CODE')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
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
function create( string $action, string $object, int $ttl, $anonymous = false ) {
    global $db, $self;

    if ( '' == $self['username'] && ! $anonymous ) {
        trigger_error( '\XMB\Token\create() was called for a guest user with the wrong arguments.', E_USER_ERROR );
    }
    if ( strlen( $action ) > 32 || strlen( $action ) < 5 || strlen( $object ) > 32 ) {
        trigger_error( 'Invalid argument for token creation.', E_USER_ERROR );
    }

    $token = bin2hex(random_bytes(16));
    $expires = time() + $ttl;

    $success = \XMB\SQL\addToken( $token, $self['username'], $action, $object, $expires );

    if ( ! $success ) {
        // Retry once.
        $token = bin2hex(random_bytes(16));
        $success = \XMB\SQL\addToken( $token, $self['username'], $action, $object, $expires );
    }

    if ( ! $success ) {
        trigger_error( 'XMB was unable to save a new token in the tokens table.', E_USER_ERROR );
    }

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
function consume( string $token, string $action, string $object ): bool {
    global $db, $self;

    \XMB\SQL\deleteTokensByDate( time() );

    return \XMB\SQL\deleteToken( $token, $self['username'], $action, $object );
}

return;