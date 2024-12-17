<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2024, The XMB Group
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
 */

declare(strict_types=1);

namespace XMB\Services;

use XMB\Attach;
use XMB\SQL;

/**
 * Get the shared file attachment service.
 *
 * @since 1.10.00
 * @param Attach $attach Required on first call, otherwise optional. Acts as the setter.
 * @return Attach
 */
function attach(?Attach $attach = null): Attach
{
    static $cache;
    
    if ($attach !== null) $cache = $attach;
    
    return $cache;
}

/**
 * Get the shared SQL service.
 *
 * @since 1.10.00
 * @param SQL $xmbsql Required on first call, otherwise optional. Acts as the setter.
 * @return SQL
 */
function sql(?SQL $xmbsql = null): SQL
{
    static $cache;
    
    if ($xmbsql !== null) $cache = $xmbsql;
    
    return $cache;
}
