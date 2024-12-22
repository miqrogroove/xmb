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

/**
 * Get the filename extension, if any.
 *
 * @since 1.9.1
 * @param string $filename
 * @return string
 */
function get_extension($filename)
{
    $a = explode('.', $filename);
    $count = count($a);
    if ($count == 1) {
        return '';
    } else {
        return $a[$count-1];
    }
}

/**
 * @since 1.9.8 SP2
 */
function MakeTime()
{
   $objArgs = func_get_args();
   $nCount = count($objArgs);
   if ($nCount < 7) {
       if ($nCount < 1) {
           $objArgs[] = intval(gmdate('H'));
       } else if ($nCount < 2) {
           $objArgs[] = intval(gmdate('i'));
       } else if ($nCount < 3) {
           $objArgs[] = intval(gmdate('s'));
       } else if ($nCount < 4) {
           $objArgs[] = intval(gmdate('n'));
       } else if ($nCount < 5) {
           $objArgs[] = intval(gmdate('j'));
       } else if ($nCount < 6) {
           $objArgs[] = intval(gmdate('Y'));
       }
   }

   $nYear = $objArgs[5];
   $nOffset = 0;
   if ($nYear < 1970) {
       if ($nYear < 1902) {
           return 0;
       } else if ($nYear < 1952) {
           $nOffset = -2650838400;
           $objArgs[5] += 84;
       } else {
           $nOffset = -883612800;
           $objArgs[5] += 28;
       }
   }

   return call_user_func_array("gmmktime", $objArgs) + $nOffset;
}

/**
 * @since 1.9.4
 */
function iso8601_date($year=0, $month=0, $day=0)
{
    $year = (int) $year;
    $month = (int) $month;
    $day = (int) $day;

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

    return str_pad($year, 4, '0', STR_PAD_LEFT).'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
}

/**
 * @since 1.9.11
 */
function phpShorthandValue($ininame)
{
    $rawstring = trim(ini_get($ininame));
    $rchr = strtoupper(substr($rawstring, -1));
    $value = (int) $rawstring;
    switch ($rchr) {
    case 'G':
        $value *= 1073741824;
        break;
    case 'M':
        $value *= 1048576;
        break;
    case 'K':
        $value *= 1024;
        break;
    default:
        break;
    }
    return $value;
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
function format_member_site($site)
{
    $site = trim($site);
    $length = strlen($site);

    if ($length < 4) {
        // Found some garbage value like 'a.b'
        $url = '';
    } else if (false === strpos($site, '.')) {
        // Found some garbage value like 'aaaa'
        $url = '';
    } else if (1 !== preg_match('@^https?://@i', $site)) {
        // Scheme missing, assume it starts with a domain name.
        $url = "http://$site";
    } else if ($length < 11) {
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
 * @param string|null $var Passed by reference for easier coding.
 */
function null_string(?string &$var)
{
    $var ??= '';
}
