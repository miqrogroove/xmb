<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12-alpha  Do not use this experimental software after 1 October 2020.
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2020, The XMB Group
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

namespace XMB\SQL;

if (!defined('IN_CODE')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

/**
 * SQL command
 *
 * @since 1.9.12
 * @param string $username Must be HTML encoded.
 * @return array Member record or empty array.
 */
function getMemberByName( string $username ): array {
    global $db;
    
    $sqluser = $db->escape( $username );

    $query = $db->query("SELECT * FROM ".X_PREFIX."members WHERE username = '$sqluser'");
    if ($db->num_rows($query) == 1) {
        $member = $db->fetch_array($query);
    } else {
        $member = [];
    }
    $db->free_result($query);
    return $member;
}

return;