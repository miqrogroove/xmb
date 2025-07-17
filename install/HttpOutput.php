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

use RuntimeException;

/**
 * Defines methods required for upgrade output.
 *
 * @since 1.10.00
 */
class HttpOutput implements UpgradeOutput
{
    public function __construct(private Template $template, private Variables $vars)
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
        // 68 chars tested well in Firefox v136.
        $text .= str_repeat('.', (68 - strlen($text)));
        echo '<span class="progress">' . $text;
    }

    /**
     * Output success of previously specified progress.
     *
     * @since 1.10.00
     */
    public function okay()
    {
        echo "<span class='progressOk'>" . $this->vars->lang['okay'] . "</span><br />\n</span>\n";
    }

    /**
     * Output a warning message to the user.
     *
     * @since 1.9.11.11
     * @param string $text
     */
    public function warning(string $text)
    {
        $this->template->text = $text;
        $this->template->process('install_progress_warning.php', echo: true);
    }

    /**
     * Output an error message to the user.
     *
     * @since 1.9.11.11
     * @param string $text Description of current progress.
     */
    public function error(string $text)
    {
        $this->template->text = $text;
        $this->template->process('install_progress_error.php', echo: true);
        $this->template->process('install_footer.php', echo: true);
        
        exit;
    }

    public function wizardError(string $head, string $msg)
    {
        $this->template->head = $head;
        $this->template->msg = $msg;

        $this->template->process('install_header.php', echo: true);
        $this->template->process('install_error.php', echo: true);
        $this->template->process('install_footer.php', echo: true);

        exit;
    }

    /**
     * Output final instructions to the user.
     *
     * @since 1.9.12
     * @param string $text Description of current progress.
     */
    public function finished(string $text)
    {
        $this->template->text = $text;
        $this->template->process('install_progress_footer.php', echo: true);
    }
}
