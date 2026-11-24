<?php

/**
 * XMB eXtreme Message Board
 *
 * Copyright (c) The XMB Group
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

use APCUIterator;
use LogicException;

/**
 * Responsible for temporary data storage between requests.
 *
 * APCu is the preferred storage method.  The XMB database is the fallback storage method.
 *
 * @since 1.12.00
 */
class Cache
{
    private const DURATION_SECONDS = 3600;

    private bool $apcEnabled = false;

    public function __construct(private SQL $sql)
    {
        // Cache invalidation is somewhat impossible for the Windows implementation of apcu, so we support non-Windows environments only.
        if (function_exists('apcu_enabled') && PHP_OS_FAMILY !== 'Windows') {
            $this->apcEnabled = apcu_enabled();
        }
    }

    /**
     * Get a value from the cache.
     */
    public function read(string $key): mixed
    {
        $value = null;

        if ($this->apcEnabled) {
            $value = apcu_fetch($key);
            if ($value === false) {
                $value = null;
            }
        }

        return $value;
    }

    /**
     * Save a value to the cache.
     */
    public function write(string $key, object|array|string|float|int $value)
    {
        if ($this->apcEnabled) {
            apcu_store($key, $value, self::DURATION_SECONDS);
        }
    }

    /**
     * Delete a value from the cache.
     */
    public function delete(string $key)
    {
        if ($this->apcEnabled) {
            apcu_delete($key);
        }
    }

    /**
     * Delete multiple cache values.
     *
     * @param string $regex The key search pattern, using PCRE syntax.
     */
    public function deleteByRegex(string $regex)
    {
        if ($this->apcEnabled) {
            apcu_delete(new APCUIterator($regex, APC_ITER_KEY));
        }
    }

    /**
     * Fetch a whole table from the XMB database and cache it.
     *
     * The forums table will be filtered for efficiency.
     */
    public function tableRead(string $name): array
    {
        $allowedTables = [
            'forums' => 'getForums',
            'settings' => 'getSettingsTable',
        ];

        if (! isset($allowedTables[$name])) throw new LogicException('Arbitrary table names are not supported here.');

        $key = 'table_' . $this->sql->tablepre . $name;
        $value = $this->read($key);
        if ($value === null) {
            $method = $allowedTables[$name];
            $value = $this->sql->$method();
            $this->write($key, $value);
        }

        return $value;
    }

    /**
     * Invalidate a cached database table.
     */
    public function tableDelete(string $name)
    {
        $key = 'table_' . $this->sql->tablepre . $name;
        $this->delete($key);
    }
}
