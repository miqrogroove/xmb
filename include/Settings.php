<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00
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
 * Provides some of the procedural logic formerly in header.php, plus getters and setters.
 *
 * @since 1.10.00
 */
class Settings
{
    public function __construct(private DBStuff $db, private SQL $sql, private Variables $vars)
    {
        $this->readToVars();
    }

    private function readToVars()
    {
        // This is normally the first query on the connection, so do not panic unless query logging is enabled.
        $panic = $this->vars->debug && $this->vars->log_mysql_errors;
        $squery = $this->db->query("SELECT * FROM " . $this->vars->tablepre . "settings", $panic);

        // Assume XMB is not installed if first query fails.
        if (false === $squery) {
            header('HTTP/1.0 500 Internal Server Error');
            if (file_exists(ROOT . 'install/')) {
                exit('XMB is not yet installed. Please do so at this time. Just <a href="./install/index.php">click here</a>.');
            }
            exit('Fatal Error: XMB is not installed. Please upload the /install/ directory to begin.');
        }
        if ($this->db->num_rows($squery) == 0) {
            header('HTTP/1.0 500 Internal Server Error');
            exit('Fatal Error: The XMB settings table is empty.');
        }
        // Check schema for upgrade compatibility back to 1.8 SP2.
        $row = $this->db->fetch_array($squery);
        if (isset($row['langfile'])) {
            // Schema version <= 4 has only one row.
            foreach ($row as $key => $val) {
                $this->vars->settings[$key] = $val;
            }
            if (! isset($this->vars->settings['schema_version'])) {
                $this->vars->settings['schema_version'] = '0';
            }
        } else {
            // Current schema uses a separate row for each setting.
            do {
                $this->vars->settings[$row['name']] = $row['value'];
            } while ($row = $this->db->fetch_array($squery));
        }
        $this->db->free_result($squery);
        unset($row);

        if ((int) $this->vars->settings['postperpage'] < 5) {
            $this->vars->settings['postperpage'] = '30';
        }

        if ((int) $this->vars->settings['topicperpage'] < 5) {
            $this->vars->settings['topicperpage'] = '30';
        }

        if ((int) $this->vars->settings['memberperpage'] < 5) {
            $this->vars->settings['memberperpage'] = '30';
        }

        if ((int) $this->vars->settings['smcols'] < 1) {
            $this->vars->settings['smcols'] = '4';
        }

        // The latest upgrade script advertises compatibility with v1.8 SP2.  These defaults might not exist yet.
        if (empty($this->vars->settings['onlinetodaycount']) || (int) $this->vars->settings['onlinetodaycount'] < 5) {
            $this->vars->settings['onlinetodaycount'] = '30';
        }

        if (
            empty($this->vars->settings['captcha_code_length'])
            || (int) $this->vars->settings['captcha_code_length'] < 3 
            || (int) $this->vars->settings['captcha_code_length'] >= $this->vars::NONCE_KEY_LEN
        ) {
            $this->vars->settings['captcha_code_length'] = '8';
        }

        if (empty($this->vars->settings['ip_banning'])) {
            $this->vars->settings['ip_banning'] == 'off';
        }

        if (empty($this->vars->settings['schema_version'])) {
            $this->vars->settings['schema_version'] == '0';
        }

        // Validate maxattachsize with PHP configuration.
        $inimax = phpShorthandValue('upload_max_filesize');
        if (empty($this->vars->settings['maxattachsize']) || $inimax < (int) $this->vars->settings['maxattachsize']) {
            $this->vars->settings['maxattachsize'] = $inimax;
        }
    }

    /**
     * Retrieve a setting
     */
    public function get(string $name): string
    {
        return $this->vars->settings[$name] ?? '';
    }

    /**
     * Set a setting
     */
    public function put(string $name, string $value)
    {
        if (! isset($this->vars->settings[$name])) {
            $this->sql->addSetting($name, $value);
        } elseif ($this->vars->settings[$name] === $value) {
            return;
        } else {
            $this->sql->updateSetting($name, $value);
        }
        $this->vars->settings[$name] = $value;
    }
}
