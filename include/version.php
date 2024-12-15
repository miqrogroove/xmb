<?php

/**
 * eXtreme Message Board
 * XMB 1.9.12
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

if (!defined('IN_CODE')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

define('X_VERSION', '1.9.12');
define('X_VERSION_EXT', '1.9.12.07');
define('X_VERSION_DATE', '20241207');
define('MYSQL_MIN_VER', '4.1.7');
define('PHP_MIN_VER', '7.0.0');
define('COPY_YEAR', '2001-2024');

$versioncompany = 'The XMB Group';
$versionshort = X_VERSION;
$versiongeneral = 'XMB ' . X_VERSION;
$copyright = COPY_YEAR;
$alpha = '';
$beta = '';
$gamma = '';
$service_pack = '';
$versionbuild = X_VERSION_DATE;

return;
