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
 * Responsible for creating the Install instances.
 *
 * This allows some service configuration to be passed to the Install constructor from outside the container. 
 *
 * @since 1.10.00 Formerly XMB\installer_factory()
 * @since 1.10.06 Formerly XMB\create_installer()
 * @since 1.12.00
 */
class InstallFactory
{
    public function __construct(
        private SiteData $site,
        private UpgradeOutput $outputInterface,
    ) {
        // Property promotion
    }

    /**
     * Fetch dependencies, configure a new instance, and return it.
     */
    public function __invoke(callable $getService): Install
    {
        require_once ROOT . 'install/cinst.php';

        return new Install(
            $getService('db'),
            $getService('password'),
            $getService('schema'),
            $this->site,
            $getService('sql'),
            $this->outputInterface,
            $getService('vars'),
        );
    }
}
