<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-2
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

use RuntimeException;

/**
 * Defines methods required for upgrade output.
 *
 * @since 1.10.00
 */
class LoggedOutput implements UpgradeOutput
{
    public const LOG_FILE = './upgrade.log';

    /**
     * Output the upgrade progress at each step.
     *
     * @since 1.9.11.11 formerly show_progress()
     * @param string $text Description of current progress.
     */
    public function progress(string $text)
    {
        $result = file_put_contents($this::LOG_FILE, "\r\n$text...", FILE_APPEND);
        if (false === $result) {
            echo 'Unable to write to file ' . $this::LOG_FILE;
            throw new RuntimeException('Unable to write to file ' . $this::LOG_FILE);
        }
    }

    /**
     * Output success of previously specified progress.
     */
    public function okay()
    {
        // Currently unused.
    }

    /**
     * Output a warning message to the user.
     *
     * @since 1.9.11.11 formerly show_warning()
     * @param string $text
     */
    public function warning(string $text)
    {
        $result = file_put_contents($this::LOG_FILE, "\r\n<b>$text</b>", FILE_APPEND);
        if (false === $result) {
            echo 'Unable to write to file ' . $this::LOG_FILE;
            throw new RuntimeException('Unable to write to file ' . $this::LOG_FILE);
        }
    }

    /**
     * Output an error message to the user.
     *
     * @since 1.9.11.11 formerly show_error()
     * @param string $text Description of current progress.
     */
    public function error(string $text)
    {
        file_put_contents($this::LOG_FILE, "\r\n$text<!-- error -->", FILE_APPEND);
    }

    /**
     * Custom error handler.
     *
     * Note the PHP documentation states this will receive non-fatal warnings and lower messages only.
     */
    public function error_handler(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Forward to the upgrade output file.
        $this->warning("$errstr\n$errfile ($errline)");
        
        // Allow the PHP standard error handler to proceed, followed by resuming the next statement after the one that caused the error.
        return false;
    }

    /**
     * Output final instructions to the user.
     *
     * @since 1.9.12 formerly show_finished()
     * @param string $text Description of current progress.
     */
    public function finished(string $text)
    {
        file_put_contents($this::LOG_FILE, "\r\n$text<!-- done. -->", FILE_APPEND);
    }
}
