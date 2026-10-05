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
 * Provides details about the installed environment.
 *
 * @since 1.10.05
 */
class Features
{
    public function __construct(private Settings $settings)
    {
        // Property promotion.
    }
    
    /**
     * @since 1.10.00 Formerly Core::schemaHasSessions()
     * @since 1.10.05
     */
    public function schemaHasSessions(): bool
    {
        return (int) $this->settings->get('schema_version') >= 5;
    }

    /**
     * @since 1.10.00 Formerly Core::schemaHasSessionNames()
     * @since 1.10.05
     */
    public function schemaHasSessionNames(): bool
    {
        return (int) $this->settings->get('schema_version') >= 13;
    }

    /**
     * @since 1.10.00 Formerly Core::schemaHasTokens()
     * @since 1.10.05
     */
    public function schemaHasTokens(): bool
    {
        return (int) $this->settings->get('schema_version') >= 5;
    }

    /**
     * @since 1.10.00 Formerly Core::schemaHasPasswordV2()
     * @since 1.10.05
     */
    public function schemaHasPasswordV2(): bool
    {
        return (int) $this->settings->get('schema_version') >= 10;
    }

    /**
     * Whether the members and vote_voters tables can hold IPv6 addresses.
     *
     * Also note the vote_voters table could not hold human-readable IPv4 addresses prior to this.
     */
    public function schemaHasIPv6(): bool
    {
        return (int) $this->settings->get('schema_version') >= 9;
    }
}
