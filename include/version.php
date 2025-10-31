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

// This file has been tested against PHP v4.3.0 for backward-compatible error reporting.

/**
 * Provides version details for XMB.
 *
 * @since 1.10.00
 */
class XMBVersion
{
    /**
     * Get the version data.
     *
     * @return array
     */
    function get()
    {
        $data = array(
            'version' => '1.10.01',
            'versionStage' => '',
            'versionDate' => '20251030',
            'mysqlMinVer' => '5.5.8',
            'phpMinVer' => '8.2.0',
            'copyright' => '2001-2025',
            'company' => 'The XMB Group',
        );
        
        $data['versionGeneral'] = 'XMB ' . $data['version'];
        $data['versionExt'] = $data['version'];
        if ($data['versionStage'] != '') {
            $data['versionExt'] .= '-' . $data['versionStage'];
        }
        
        return $data;
    }

    /**
     * Assert the minimum PHP version requirement.
     */
    function assertPHP() {
        $data = $this->get();
        $minimum = $data['phpMinVer'];

        // Check Server Version
        if (version_compare(phpversion(), $minimum, '<')) {
            include constant('XMB\ROOT') . 'templates/install_php_version_error.php';
            exit;
        }
    }
}
