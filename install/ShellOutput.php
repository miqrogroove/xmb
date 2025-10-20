<?php

/**
 * eXtreme Message Board
 * XMB 1.10.01
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
class ShellOutput implements UpgradeOutput
{
    /**
     * Output the upgrade progress at each step.
     *
     * This function is called by upgrade.lib.php with verbose status information.
     * You can change the output stream or suppress it completely.
     *
     * @param string $text Description of current progress.
     */
    public function progress(string $text)
    {
        if (UPGRADE_CLI) {
            // The upgrade script won't call the okay() method, so we need the line ending here.
            echo $text, "\n";
        } else {
            // For install, this can add a cosmetic separator such as ellipsis.
            echo $text, "...";
        }
    }

    /**
     * Output success of previously specified progress.
     */
    public function okay()
    {
        echo "OK\n";
    }

    /**
     * Output a warning message to the user.
     *
     * @param string $text
     */
    public function warning(string $text)
    {
        echo $text, "\n";
    }

    /**
     * Output an error message to the user.
     *
     * @param string $text Description of current progress.
     */
    public function error(string $text)
    {
        echo $text, "\n";
    }

    /**
     * Output final instructions to the user.
     *
     * @param string $text Description of current progress.
     */
    public function finished(string $text)
    {
        echo $text, "\n";
    }
}
