<?php

/**
 * XMB eXtreme Message Board
 *
 * Copyright (c) The XMB Group
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
    private array $piconcache = [];
    private array $smiliecache = [];

    public function __construct(private Cache $cache)
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

        $rows = $this->cache->tableRead('smilies');
        foreach ($rows as $row) {
            switch ($row['type']) {
                case 'smiley':
                    $this->smiliecache[$row['code']] = $row['url'];
                    break;
                case 'picon':
                    $this->piconcache[] = $row;
            }
        }

        $table = $this->cache->tableRead('words');
        $this->censorcache = array_combine(array_column($table, 'find'), array_column($table, 'replace1'));

        $this->cacheStatus = true;
    }

    /**
     * Clears the server cache (but not the copy used for this request)
     *
     * @since 1.12.00
     */
    public function invalidateSmilieCache()
    {
        $this->cache->tableDelete('smilies');
    }

    /**
     * Clears the server cache (but not the copy used for this request)
     *
     * @since 1.12.00
     */
    public function invalidateCensorCache()
    {
        $this->cache->tableDelete('words');
    }

    /**
     * Provides an array containing all smilies indexed by code.
     *
     * @since 1.9.11
     * @return array
     */
    public function smilieCache(): array
    {
        if (! $this->cacheStatus) $this->initCache();

        return $this->smiliecache;
    }

    /**
     * Provides an array containing all type='smiley' rows from the smilies table.
     *
     * This is rarely needed, intended for the admin panel.  An extra cache miss is acceptable here.
     *
     * @since 1.12.00
     * @return array
     */
    public function getSmilies(): array
    {
        $result = [];
        $rows = $this->cache->tableRead('smilies');
        foreach ($rows as $row) {
            if ($row['type'] == 'smiley') {
                $result[] = $row;
            }
        }

        return $result;
    }

    /**
     * Provides an array containing all type='picon' rows from the smilies table.
     *
     * @since 1.12.00
     * @return array
     */
    public function getPostIcons(): array
    {
        if (! $this->cacheStatus) $this->initCache();

        return $this->piconcache;
    }

    /**
     * Provides a copy of the words table.
     *
     * This is rarely needed, intended for the admin panel.  An extra cache miss is acceptable here.
     *
     * @since 1.12.00
     * @return array
     */
    public function getCensors(): array
    {
        return $this->cache->tableRead('words');
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
     * Check if the user-provided post icon string matches a configured icon.
     *
     * @since 1.12.00
     * @return bool
     */
    public function iconExists(string $filename): bool
    {
        if (! $this->cacheStatus) $this->initCache();
        
        return in_array($filename, array_column($this->piconcache, 'url'));
    }

    /**
     * Replaces all Smilie codes with images.
     *
     * @since 1.9.1
     * @param string $txt Variable required.  Input and output text.  Treated as HTML.
     * @param string $smiliesURL The full URL for the smilies directory.
     */
    public function smile(string &$txt, string $smiliesURL)
    {
        if (! $this->isAnySmilieInstalled()) return;

        // Parse the input for HTML elements and entity references.
        $pattern =
            "/(" . // Capture the split delimiters.  This is required for the PREG flag to work, regardless of pattern type.
                "<[^>]*+>" . // Find HTML elements.
                "|" .
                "&[^<;]*+;" . // Find entity references that do not contain HTML elements.
            ")/";
        $parts = preg_split($pattern, $txt, flags: PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        // Loop through the parts.
        foreach ($parts as &$part) {
            switch ($part[0]) {
                case '<':
                    // Skip all HTML elements, including BBCode output.
                    continue 2;
                case '&':
                    // Skip all HTML entity references.
                    continue 2;
            }
            
            // Add smilies to the text.
            foreach ($this->smiliecache as $code => $filename) {
                // Most $part values won't contain any smilies, so optimize by writing new strings only when necessary.
                if (false === strpos($part, $code)) continue;

                $altcode = htmlEsc($code, storedData: true);
                $part = str_replace($code, "<img src='$smiliesURL$filename' style='border:none' alt='$altcode' />", $part);
            }
        }
        
        // Put the parts back together
        $txt = implode('', $parts);
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
