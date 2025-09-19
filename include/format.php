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

use LogicException;

/**
 * Get the filename extension, if any.
 *
 * @since 1.9.1
 * @param string $filename
 * @return string
 */
function get_extension(string $filename): string
{
    return pathinfo($filename, PATHINFO_EXTENSION);
}

/**
 * Alias of gmmktime() that always returns an int.
 *
 * @since 1.9.8 SP2
 * @param int 0...5 Values for hour, minute, second, day, month, and year.
 * @return int Timestamp, or zero on 32-bit systems where the year is out of range.
 */
function MakeTime(int ...$objArgs): int
{
    if (count($objArgs) != 6) throw new LogicException('Exactly 6 arguments required');

    return (int) call_user_func_array('gmmktime', $objArgs);
}

/**
 * @since 1.9.4
 */
function iso8601_date(int $year, int $month, int $day): string
{
    if ($year < 1 || $month < 1 || $day < 1) {
        return '0000-00-00';
    }

    if ($year < 100) {
        $year += 1900;
    }

    if ($month > 12 || $month < 1) {
        $month = 1;
    }

    if ($day > 31 || $day < 1) {
        $day = 1;
    }

    return str_pad((string) $year, 4, '0', STR_PAD_LEFT).'-'.str_pad((string) $month, 2, '0', STR_PAD_LEFT).'-'.str_pad((string) $day, 2, '0', STR_PAD_LEFT);
}

/**
 * Convert a shorthand INI number value to an int.
 *
 * @since 1.9.11
 * @param string $ininame The PHP INI directive name.
 * @return int The converted INI value.
 */
function phpShorthandValue(string $ininame): int
{
    $rawstring = trim(ini_get($ininame));

    return ini_parse_quantity($rawstring);
}

/**
 * Central place to get the image URL pattern.
 *
 * Remember, this is also duplicated in js/header.js
 *
 * @since 1.9.11.15
 * @return string Regular expression for a user-provided URL to an image.
 */
function get_img_regexp(bool $https_only = false): string
{
    if ($https_only) {
        return '(https):\/\/([:a-z\.\/_\-0-9%~]+)(\?[a-z=0-9&_\-;~]*)?';
    } else {
        return '(https?|ftp):\/\/([:a-z\.\/_\-0-9%~]+)(\?[a-z=0-9&_\-;~]*)?';
    }
}

/**
 * Convert user 'site' input to a reasonable URL.
 *
 * @since 1.9.11.15
 * @param string $site The members.site value retrieved from the database.
 * @return string A URL or an empty string.
 */
function format_member_site(string $site): string
{
    $site = trim($site);
    $length = strlen($site);

    if ($length < 4) {
        // Found some garbage value like 'a.b'
        $url = '';
    } elseif (false === strpos($site, '.')) {
        // Found some garbage value like 'aaaa'
        $url = '';
    } elseif (1 !== preg_match('@^https?://@i', $site)) {
        // Scheme missing, assume it starts with a domain name.
        $url = "http://$site";
    } elseif ($length < 11) {
        // Found some garbage value like 'http://a.b'
        $url = '';
    } else {
        $url = $site;
    }
    
    return $url;
}

/**
 * Determine which browser is in use, and return a human-friendly description.
 *
 * @since 1.9.12
 * @param string $raw
 * @return string
 */
function parse_user_agent(string $raw): string
{
    if     (strpos($raw, 'Opera'     ) || strpos($raw, 'OPR/')     ) return 'Opera'            ;
    elseif (strpos($raw, 'Edge'      )                             ) return 'Edge'             ;
    elseif (strpos($raw, 'Chromium'  )                             ) return 'Chromium'         ;
    elseif (strpos($raw, 'Chrome'    )                             ) return 'Chrome'           ;
    elseif (strpos($raw, 'Safari'    )                             ) return 'Safari'           ;
    elseif (strpos($raw, 'Seamonkey' )                             ) return 'Seamonkey'        ;
    elseif (strpos($raw, 'Firefox'   )                             ) return 'Firefox'          ;
    elseif (strpos($raw, 'MSIE'      ) || strpos($raw, 'Trident/7')) return 'Internet Explorer';
    else return $raw;
}

/**
 * Coerces any null value to an empty string.
 *
 * @since 1.9.12.05
 * @param ?string $var Passed by reference for easier coding.
 */
function null_string(?string &$var)
{
    $var ??= '';
}

/**
 * Truncate overlong strings
 *
 * @since 1.9.3
 * @param string $string
 * @param int $len
 * @param string $shortType When 'soft', truncates only if there are words shorter than $len. When 'hard', anything longer than $len will be truncated. Use 'both' when able.
 * @param string $ps This will be appended if the string is truncated.
 */
function shortenString(string $string, int $len = 125, string $shortType = 'both', string $ps = '...'): string
{
    if (strlen($string) > $len) {
        $modified = false;
        $newlen = $len - strlen($ps);

        if ($shortType == 'soft' || $shortType == 'both') {
            $matches = [];
            if (1 === preg_match('#^(.{0,' . $newlen . '})\\W#', $string, $matches)) {
                $string = $matches[1];
                $modified = true;
            }
        }

        if (! $modified && ($shortType == 'hard' || $shortType == 'both')) {
            $string = substr($string, 0, $newlen);
            $modified = true;
        }

        if ($modified) $string .= $ps;
    }
    return $string;
}
