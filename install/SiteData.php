<?php

/**
 * eXtreme Message Board
 * XMB 1.10
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

/**
 * Holds the site-specific settings required during installation.
 *
 * @since 1.10.00
 */
class SiteData
{
    public bool $showVersion; // Allow public display of the XMB version number after installation.

    public string $adminEmail; // XMB Super Admin personal email address for notifications.
    public string $adminPass; // XMB Super Admin password.  Encoding should conform to the XMB language charset, e.g. ISO-8859-1 for English sites.
    public string $adminUser; // XMB Super Admin username.  This value must be HTML encoded and may not contain non-printing chars or any []'"<>\|,@
    public string $dbHost; // The name, address, or socket of the database server.
    public string $dbName; // The name of the specific database to use on the server.
    public string $dbPass; // The password for the database connection.
    public string $dbTablePrefix; // Any unique prefix for table names to allow this instance to coexist with other sites in the same database.
    public string $dbUser; // The username for the database connection.
    public string $fullURL; // Complete URL of the XMB root including the trailing slash.
}
