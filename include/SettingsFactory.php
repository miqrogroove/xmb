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

/**
 * Responsible for creating the Settings instances.
 *
 * This allows some service configuration to be passed to the Settings constructor from outside the container. 
 *
 * @since 1.12.00
 */
class SettingsFactory
{
    public function __construct(
        private bool $installMode,
    ) {
        // Property promotion
    }

    /**
     * Fetch dependencies, configure a new instance, and return it.
     */
    public function __invoke(callable $getService): Settings
    {
        return new Settings(
            $getService('cache'),
            $getService('sql'),
            $getService('vars'),
            installMode: $this->installMode,
        );
    }
}
