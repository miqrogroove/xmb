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

namespace XMB\Session;

/**
 * Session Data objects are used to pass results between methods.
 *
 * @since 1.9.12
 */
class Data
{
    public array $member = [];      // Must be the member record array from the database, or an empty array, but without any password.
    public string $password = '';   // The member password hash from the database, or an empty string. Used during a new login only.
    public bool $pwReset = false;   // True if the user is required to set a different password to comply with password policy.
    public string $comment = '';    // The session name provided by the user. Used during a new login only.
    public bool $permanent = false; // True if the session should be saved by the client, otherwise false.
    public string $status = 'none'; // Session input level.  Must be 'good', 'bad', or 'none'.
}
