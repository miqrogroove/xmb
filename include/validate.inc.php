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

use DomainException;

/**
 * Has the named submit button been invoked?
 *
 * Looks in the form post data for a named submit
 *
 * @since 1.9.8
 * @return   boolean   true if the submit is present, false otherwise
 */
function onSubmit(string $submitname): bool
{
    $retval = ! empty($_POST[$submitname]);
    if (! $retval) {
        $retval = ! empty($_GET[$submitname]);
    }

    return($retval);
}

/**
 * Is the forum being viewed?
 *
 * Looks for pre-form post data for a named submit
 *
 * @since 1.9.8
 * @return   boolean   true if the no submit is present, false otherwise
 */
function noSubmit(string $submitname): bool
{
    return ! onSubmit($submitname);
}

/**
 * Retrieves user input and ensures compatiblity with non-binary-safe functions.
 *
 * @since 1.10.00
 */
function getPhpInput(string $varname, string $sourcearray = 'p'): string
{
    $retval = getRawInput($varname, $sourcearray);

    if (is_string($retval)) {
        $retval = str_replace("\x00", '', $retval);
    } else {
        $retval = '';
    }

    return $retval;
}

/**
 * Retrieves raw user input.
 *
 * This is rarely useful and should be limited to things like passwords and binary data.
 *
 * @since 1.10.00
 */
function getRawString(string $varname, string $sourcearray = 'p'): string
{
    $retval = getRawInput($varname, $sourcearray);

    if (! is_string($retval)) {
        $retval = '';
    }

    return $retval;
}

/**
 * Retrieve a string or array input without filtering.
 *
 * @since 1.10.00
 */
function getRawInput(string $varname, string $sourcearray = 'p'): string|array|null
{
    $retval = null;

    switch ($sourcearray) {
        case 'p':
            $sourcearray = &$_POST;
            break;
        case 'g':
            $sourcearray = &$_GET;
            break;
        case 'c':
            $sourcearray = &$_COOKIE;
            break;
        case 'r':
        default:
            $sourcearray = &$_REQUEST;
        break;
    }

    if (isset($sourcearray[$varname])) {
        if (is_string($sourcearray[$varname]) || is_array($sourcearray[$varname])) {
            $retval = $sourcearray[$varname];
        }
    }

    return $retval;
}

/**
 * @since 1.9.8 SP3
 */
function recodeOut(string $rawstring): string
{
    return rawurlencode(rawHTML($rawstring));
}

function recodeJavaOut(string $rawstring): string
{
    return rawurlencode(rawurlencode(rawHTML($rawstring)));
}

/**
 * Performs incomplete escaping of text for HTML usage, outside of elements only.
 *
 * @since 1.9.8 SP3 formerly cdataOut()
 * @since 1.10.00
 */
function lessThanEsc(string $rawstring): string
{
    return htmlspecialchars($rawstring, ENT_NOQUOTES);
}

/**
 * Escapes plain text for reasonable HTML usage.
 *
 * Never safe for STYLE attributes, CODE elements, etc.
 *
 * @since 1.10.00
 */
function htmlEsc(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_XHTML);
}

/**
 * @since 1.9.8 SP3
 */
function attrOut(string $rawstring, string $word = ''): string
{
    $retval = $rawstring;
    if ($word != '') {
        $retval = str_ireplace($word, "_".$word, $retval);
    }
    return htmlEsc($retval);
}

/**
 * @since 1.9.12.09
 */
function rawHTML(string $encodedText) {
    return htmlspecialchars_decode($encodedText, ENT_QUOTES | ENT_XHTML);
}

/**
 * Takes an HTML encoded value, converts to raw HTML, then strips any HTML character entity references.
 *
 * This is an alternative to decimalEntityDecode() for use in plaintext where HTML will not render correctly.
 *
 * @since 1.10.00
 */
function rawValueWithoutDecimalEntities(string $encodedText) {
    $html = rawHTML($encodedText);
    $html = decimalEntityStrip($html);

    return $html;
}

/**
 * Strips any HTML character entity references containing 3 to 6 digits.
 *
 * @since 1.10.00
 */
function decimalEntityStrip(string $encodedText) {
    return preg_replace('/&#\d{3,6};/', ' ', $encodedText);
}

/**
 * Decode any double-encoded &amp; in front of any decimal entity reference.
 *
 * This will undo the double encoding that results after a browser processes input from mismatched character sets.
 *
 * @since 1.9.10
 */
function decimalEntityDecode(string $rawstring): string
{
    $currPos = 0;
    while (($currPos = strpos($rawstring, '&amp;#', $currPos)) !== false) {
        $tempPos = strpos($rawstring, ';', $currPos + 6);
        $entLen = $tempPos - ($currPos + 6);
        if ($entLen >= 3 && $entLen <= 6) {
            $entNum = substr($rawstring, $currPos + 6, $entLen);
            if (is_numeric($entNum)) {
                if (intval($entNum) >= 160 && intval($entNum) <= 129759) {
                    $rawstring = str_replace("&amp;#$entNum;", "&#$entNum;", $rawstring);
                }
            }
        }
        $currPos++;
    }

    return $rawstring;
}

/**
 * Take the raw db value of a forum's name and convert it to the standard HTML version used throughout XMB.
 *
 * Starting with 1.10.00, this is a simple alias of rawHTML().
 *
 * @since 1.9.10
 */
function fnameOut(string $rawstring): string
{
    return rawHTML($rawstring);
}

/**
 * Take the raw db value of an admin setting and strip any HTML.
 *
 * This is needed when HTML is expected but the value needs to be displayed in a text-only element such as <option>.
 * This does not strip quotes and is not appropriate for attribute context or any non-admin inputs.
 *
 * @since 1.10.00
 */
function adminStripText(string $rawstring): string
{
    return strip_tags(rawHTML($rawstring));
}

/**
 * Retrieve a gpc integer and sanitize it
 *
 * @since 1.9.8
 * @param   string   $varname   name of the variable in a superglobal array such as $_GET
 * @param   string   $sourcearray   abbreviation of the superglobal name, g for $_GET by default
 * @return   integer   the "safe" integer if the variable is available, zero otherwise
*/
function getInt(string $varname, string $sourcearray = 'g'): int
{
    $retval = getRawInput($varname, $sourcearray);
    
    if (is_numeric($retval)) {
        return (int) $retval;
    } else {
        return 0;
    }
}

/**
 * Retrieve a REQUEST integer and sanitize it
 *
 * @since 1.9.8
 * @param   string   $varname   name of the variable in $_REQUEST
 * @return   integer   the "safe" integer if the variable is available, zero otherwise
 */
function getRequestInt(string $varname): int
{
    return getInt($varname, 'r');
}

/**
 * Retrieve a POST integer and sanitize it
 *
 * @since 1.9.8
 * @param   string   $varname   name of the variable in $_POST
 * @param   boolean   $setZero   should the return be set to zero if the variable doesnt exist?
 * @return   int|null   the "safe" integer or zero, null otherwise
 */
function formInt(string $varname, bool $setZero = true): ?int
{
    if ($setZero) {
        $retval = 0;
    } else {
        $retval = null;
    }

    if (isset($_POST[$varname]) && is_numeric($_POST[$varname])) {
        $retval = (int) $_POST[$varname];
    }
    return $retval;
}

/**
 * Return the array associated with varname
 *
 * This function interrogates the POST variable(form) for an
 * array of inputs submitted by the user. It checks that it exists
 * and returns false if no elements or not existent, and an array of
 * one or more integers if it does exist.
 *
 * @since 1.9.8
 * @param   string   $varname   the form field to find and sanitize
 * @return   mixed   false if not set or no elements, an array() of integers otherwise
 */
function getFormArrayInt(string $varname, bool $doCount = true): array|false
{
    if (!isset($_POST[$varname]) || empty($_POST[$varname])) {
        return false;
    }

    $retval = array();
    $formval = $_POST[$varname];

    if ($doCount) {
        if (count($retval) == 1) {
            $retval = array($retval);
        }
    }

    foreach ($formval as $value) {
        $retval[] = intval($value);
    }

    return $retval;
}

/**
 * Retrieve a POST variable and check it for on value
 *
 * @since 1.9.8
 * @param   string   $varname   name of the variable in $_POST
 * @return   string   on if set to on, off otherwise
 */
function formOnOff(string $varname): string
{
    $retval = getRawInput($varname);
    
    if ($retval !== 'on') {
        $retval = 'off';
    }
    return $retval;
}

/**
 * Retrieve a POST variable and check it for yes value
 *
 * @since 1.9.8
 * @param   string   $varname   name of the variable in $_POST
 * @return   string   yes if set to yes, no otherwise
 */
function formYesNo(string $varname): string
{
    $retval = getRawInput($varname);
    
    if ($retval !== 'yes') {
        $retval = 'no';
    }
    return $retval;
}

/**
 * Sanitizes a POST integer and checks it for 1 value
 *
 * @since 1.9.8
 * @param   string   $varname   name of the variable in $_POST
 * @return   integer   1 if set to 1, 0 otherwise
 */
function form10(string $varname): int
{
    return(formInt($varname) == 1) ? 1 : 0;
}

/**
 * Retrieve a POST boolean variable and check it for true value
 *
 * @since 1.9.8
 * @param   string   $varname   name of the variable in $_POST
 * @return   boolean   true if set to true, false otherwise
 */
function formBool(string $varname): bool
{
    return getRawInput($varname) === 'true';
}

/**
 * Check if a variable is checked
 *
 * @since 1.9.8
 * @param   string   $varname   name of the variable
 * @param   string   $compare   is $compare equal to $varname?
 * @return   string   checked html if $compare is equal to $varname, empty otherwise
 */
function isChecked(string $varname, string $compare = 'yes'): string
{
    return(($varname == $compare) ? 'checked="checked"' : '');
}

/**
 * @since 1.9.8
 * @param string $filename The original raw file name.
 * @return bool
 */
function isValidFilename(string $filename): bool
{
    return (bool) preg_match("#^[\\w\\^\\-\\#\\] `~!@$&()_+=[{};',.]+$#", trim($filename));
}

/**
 * Take a raw string and convert it to a PHP string literal.
 *
 * Useful for sanitizing PHP file modifications.  Binary safe.
 *
 * @since 1.9.12.06
 * @param string $value
 * @param string $style Optional.  Use 'double' for double-quoted output and to escape linefeeds.
 * @return string The PHP string literal version of the input.
 */
function input_to_literal(string $value, string $style = 'single'): string
{
    if ($style == 'single') {
        $value = str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
        return "'$value'";
    } else {
        $value = str_replace(['\\', '"', '$', "\n", "\r"], ['\\\\', '\\"', '\\$', '\\n', '\\r'], $value);
        return '"' . $value . '"';
    }
}

/**
 * Retrieve an array element whose existence is unknown.
 *
 * Though this looks trivial, it is helpful for overcoming order-of-operation problems related to the ?? operator.
 *
 * @since 1.10.00
 */
function arrayCoalesce(array $array, string $index): mixed
{
    return $array[$index] ?? null;
}

/**
 * Retrieve a constant whose existence is unknown.
 *
 * @since 1.10.00
 */
function constantCoalesce(string $name): mixed
{
    return defined("XMB\\$name") ? constant("XMB\\$name") : null;
}

/**
 * Convert a string so it can be included as literal text inside a DateTime format.
 *
 * @since 1.10.00
 */
function dateTimeFormatEscape(string $raw): string
{
    if ($raw == '') return '';

    $array = str_split($raw);
    
    return "\\" . implode("\\", $array);
}

/**
 * Replace part of a DateTime format with other text.
 *
 * @since 1.10.00
 * @param string $format
 * @param string $search
 * @param string $replace Raw text to be added in place of the $search. HTML will be rejected for simplicity.
 * @return string The revised format.
 */
function dateTimeFormatReplace(string $format, string $search, string $replace): string
{
    if (attrOut($replace) !== $replace) throw new DomainException('HTML is not allowed in the replacement string');

    $pos = 0;
    $newformat = $format;
    $newreplace = dateTimeFormatEscape($replace);
    while ($pos < strlen($newformat)) {
        $newpos = strpos($newformat, $search, $pos);
        if ($newpos === false) break;
        $pos = $newpos;
        if ($pos > 0) {
            if (substr($newformat, $pos - 1, 1) == "\\") {
                // Slashes preceding the search text are rejected because we haven't parsed those here.
                $pos++;
                continue;
            }
        }
        $newformat = substr($newformat, 0, $pos) . $newreplace . substr($newformat, $pos + strlen($search));
        $pos += strlen($newreplace);
    }
    return $newformat;
}
