<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-1
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
 * Defines methods required for upgrade output.
 *
 * @since 1.10.00
 */
interface UpgradeOutput
{
    /**
     * Output the upgrade progress at each step.
     *
     * This function is intended to be overridden to support various output streams.
     *
     * @param string $text Description of current progress.
     */
    public function progress(string $text);

    /**
     * Output a warning message to the user.
     *
     * @param string $text
     */
    public function warning(string $text);

    /**
     * Output an error message to the user.
     *
     * @param string $text
     */
    public function error(string $text);

    /**
     * Output final instructions to the user.
     *
     * @param string $text Description of current progress.
     */
    public function finished(string $text);
}
