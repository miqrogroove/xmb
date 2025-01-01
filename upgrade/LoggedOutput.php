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

namespace XMB;

use RuntimeException;

/**
 * Defines methods required for upgrade output.
 *
 * @since 1.10.00
 */
class LoggedOutput implements UpgradeOutput
{
    public function __construct(private string $logfile)
    {
        // Property promotion.
    }
    
    /**
     * Output the upgrade progress at each step.
     *
     * This function is intended to be overridden by other upgrade scripts
     * that don't use this exact file, to support various output streams.
     *
     * @since 1.9.11.11
     * @param string $text Description of current progress.
     */
    public function progress(string $text)
    {
        $result = file_put_contents($this->logfile, "\r\n$text...", FILE_APPEND);
        if (false === $result) {
            echo 'Unable to write to file ' . $this->logfile;
            throw new RuntimeException('Unable to write to file ' . $this->logfile);
        }
    }

    /**
     * Output a warning message to the user.
     *
     * @since 1.9.11.11
     * @param string $text
     */
    public function warning(string $text)
    {
        $result = file_put_contents($this->logfile, "\r\n<b>$text</b>", FILE_APPEND);
        if (false === $result) {
            echo 'Unable to write to file ' . $this->logfile;
            throw new RuntimeException('Unable to write to file ' . $this->logfile);
        }
    }

    /**
     * Output an error message to the user.
     *
     * @since 1.9.11.11
     * @param string $text Description of current progress.
     */
    public function error(string $text)
    {
        file_put_contents($this->logfile, "\r\n$text<!-- error -->", FILE_APPEND);
    }

    /**
     * Output final instructions to the user.
     *
     * @since 1.9.12
     * @param string $text Description of current progress.
     */
    public function finished(string $text)
    {
        file_put_contents($this->logfile, "\r\n$text<!-- done. -->", FILE_APPEND);
    }
}
