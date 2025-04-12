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
 * Smilies and Censors processor.
 *
 * Lazy loading will occur when calling any of the non-constructor methods.
 *
 * @since 1.10.00
 */
class SmileAndCensor
{
    private bool $cacheStatus = false;

    private array $censorcache = [];
    private array $smiliecache = [];

    public function __construct(private SQL $sql)
    {
        // Property promotion.
    }
    
    /**
     * Sets up the Smilie and Censor caches for Core use.
     *
     * @since 1.5 Formerly named smcwcache()
     * @since 1.10.00
     */
    private function initCache()
    {
        if ($this->cacheStatus) return;

        $table = $this->sql->getSmilies();
        $this->smiliecache = array_combine(array_column($table, 'code'), array_column($table, 'url'));

        $table = $this->sql->getCensors();
        $this->censorcache = array_combine(array_column($table, 'find'), array_column($table, 'replace1'));

        $this->cacheStatus = true;
    }

    /**
     * Provides an array containing all smilies indexed by code.
     *
     * @since 1.9.11
     * @return array
     */
    public function smilieCache()
    {
        if (! $this->cacheStatus) $this->initCache();

        return $this->smiliecache;
    }

    /**
     * Check if the smilie list has anything.
     *
     * @since 1.10.00
     * @return bool
     */
    public function isAnySmilieInstalled(): bool
    {
        if (! $this->cacheStatus) $this->initCache();
        
        return count($this->smiliecache) > 0;
    }

    /**
     * Replaces all Smilie codes with images.
     *
     * @since 1.9.1
     * @param string $txt Variable required. Input and output text.
     * @param string $smiliesURL The full URL for the smilies directory.
     */
    public function smile(string &$txt, string $smiliesURL)
    {
        if (! $this->isAnySmilieInstalled()) return;

        // Parse the input for HTML tags
        $pattern = "/(<[^>]*+>)/";
        $parts = preg_split($pattern, $txt, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        // Loop through the parts and avoid the HTML tags
        foreach ($parts as &$part) {
            if (substr($part, 0, 1) == '<') continue;
            
            foreach ($this->smiliecache as $code => $filename) {
                // Most $part values won't contain any smilies, so optimize by writing new strings only when necessary.
                if (false === strpos($part, $code)) continue;

                $altcode = attrOut($code);
                $part = str_replace($code, "<img src='$smiliesURL$filename' style='border:none' alt='$altcode' />", $part);
            }
        }
        
        // Put the parts back together
        $txt = implode("", $parts);
    }

    /**
     * Applies predefined censors on given text.
     *
     *
     * @since 1.9.1
     * @param    $txt    string, the text to apply the censors to
     * @return   string, the censored version of the input string
     */
    function censor(string $txt): string
    {
        if (! $this->cacheStatus) $this->initCache();

        if (count($this->censorcache) == 0) return $txt;

        return str_ireplace(array_keys($this->censorcache), $this->censorcache, $txt);
    }
}
