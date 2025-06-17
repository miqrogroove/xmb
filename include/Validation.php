<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
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
 * General-purpose variable validation methods which depend on a database service.
 *
 * @since 1.10.00
 */
class Validation
{
    public function __construct(private DBStuff $db)
    {
        // Property promotion.
    }

    /**
     * All-purpose function for retrieving and sanitizing user input.
     *
     * @since 1.9.8 SP3
     */
    public function postedVar(string $varname, string $word = '', bool $htmlencode = true, bool $dbescape = true, bool $quoteencode = false, string $sourcearray = 'p'): string
    {
        $retval = getPhpInput($varname, $sourcearray);

        return $this->sanitizeString($retval, $word, $htmlencode, $dbescape, $quoteencode);
    }

    public function postedArray(
        string $varname,
        string $valueType = 'string',
        string $keyType = 'int',
        string $word = '',
        bool $htmlencode = true,
        bool $dbescape = true,
        bool $quoteencode = false,
        string $source = 'p',
    ): array {
        $input = getRawInput($varname, $source);

        // Convert a single or comma delimited list to an array
        if (is_string($input)) {
            if (strpos($input, ',') !== false) {
                $input = explode(',', $input);
            } else {
                $input = [$input];
            }
        } elseif (is_null($input)) {
            $input = [];
        }
        
        $keys = array_keys($input);
        if ($keyType == 'int') {
            $keys = array_map('intval', $keys);
        } else {
            foreach ($keys as &$key) {
                $key = str_replace("\x00", '', $key);
                $key = $this->sanitizeString($key, $word, $htmlencode, $dbescape, $quoteencode);
            }
        }

        foreach ($input as &$theObject) {
            switch ($valueType) {
                case 'onoff':
                    if (strtolower($theObject) !== 'on') {
                        $theObject = 'off';
                    }
                    break;
                case 'yesno':
                    if (strtolower($theObject) !== 'yes') {
                        $theObject = 'no';
                    }
                    break;
                case 'int':
                    $theObject = (int) $theObject;
                    break;
                case 'string':
                default:
                    if (is_string($theObject)) {
                        $theObject = str_replace("\x00", '', $theObject);
                        $theObject = $this->sanitizeString($theObject, $word, $htmlencode, $dbescape, $quoteencode);
                    } else {
                        $theObject = '';
                    }
                    break;
            }
        }

        return array_combine($keys, $input);
    }

    /**
     * Reuseable function for sanitizing user input.
     *
     * @since 1.10.00
     */
    private function sanitizeString(string $input, string $word = '', bool $htmlencode = true, bool $dbescape = true, bool $quoteencode = false): string
    {
        $retval = $input;

        if ($word != '') {
            $retval = str_ireplace($word, "_".$word, $retval);
        }

        if ($htmlencode) {
            if ($quoteencode) {
                $retval = htmlspecialchars($retval, ENT_QUOTES);
            } else {
                $retval = htmlspecialchars($retval, ENT_NOQUOTES);
            }
        }

        if ($dbescape) {
            $this->db->escape_fast($retval);
        }

        return $retval;
    }
}
