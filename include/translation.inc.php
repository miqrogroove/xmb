<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-3
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

use InvalidArgumentException;
use RuntimeException;

class Translation
{
    private array $dirCache = [];
    private array $langCache = [];

    private bool $dirCacheStatus = false;

    public function __construct(private Variables $vars) {
        // Property promotion
    }

    /**
     * Uses the specified translation file to populate the $lang and $langfile variables.
     *
     * @since 1.9.11
     * @param string $devname Name specified by XMB for internal use (usually written in English).
     * @return bool
     */
    public function loadLang(string $devname = "English"): bool
    {
        $lang = [];

        include ROOT . "lang/$devname.lang.php";

        // Load the $lang array.
        if (count($lang) > 0) {
            $this->vars->langfile = $devname;
            $this->vars->lang = &$lang;
            $this->vars->charset = $this->vars->lang['charset'];
            return true;
        } else {
            return false;
        }
    }

    /**
     * Uses the set of translation files to retrieve specific phrases in all available languages.
     *
     * Needs to accommodate usage inside of loops.  Strings specified by $langkeys will be cached.
     * This will NOT cache entire files.
     *
     * @since 1.9.11
     * @param array $langkeys Array of strings, used as the $lang array key.
     * @return array Associative indexes lang_base.devname and lang_keys.langkey.
     */
    public function loadPhrases(array $langkeys = []): array
    {
        // Guarantee inclusion of the 'charset' and 'language' keys, which tend to be useful internally.
        $langkeys = array_unique(array_merge($langkeys, ['charset', 'language', 'iso639']));

        // First, cache the file list.
        if (! $this->dirCacheStatus) $this->initDirCache();

        // Second, cache the needed parts of each file.
        foreach ($this->dirCache as $filename) {
            if (substr($filename, -9) != '.lang.php') continue;

            $devname = substr($filename, 0, -9);

            $alreadyCached = true;
            foreach ($langkeys as $key) {
                if (! isset($this->langCache[$devname][$key])) {
                    $alreadyCached = false;
                    break;
                }
            }

            if ($alreadyCached) continue;

            $lang = [];
            include ROOT . "lang/$filename";

            foreach ($langkeys as $key) {
                $this->langCache[$devname][$key] = $lang[$key];
            }
        }

        // Lastly, return the file parts cache.
        return $this->langCache;
    }

    /**
     * Adds an array of new $lang values to the specified translation.
     *
     * @since 1.9.11
     * @param array $lang Associative array of new key/value pairs.  Values should be raw cdata.
     * @param string $langfile Devname of the translation to add to.
     * @return bool Returns true on success, false if the $langfile does not exist.
     */
    public function setManyLangValues(array $lang, string $langfile): bool
    {
        if (count($lang) == 0) throw new InvalidArgumentException('The lang array argument must not be empty.');
        if ($langfile === '') throw new InvalidArgumentException('The langfile string argument must not be empty.');

        // Get the current file
        $filepath = ROOT . "lang/$langfile.lang.php";
        if (! is_readable($filepath)) return false;
        if (! is_writable($filepath)) throw new RuntimeException("Wrong file permissions for the $langfile translation.");
        $text = file_get_contents($filepath);
        
        // Ensure the file has a newline ending.
        if (substr($text, -1) !== "\n") $text .= "\n";

        // Add data from $lang
        foreach ($lang as $key => $value) {
            if ($langfile === $this->vars->langfile) {
                $this->vars->lang[$key] = $value;
            }
            $pos = strpos($file, "\$lang['$key'] =");
            if ($pos !== false) {
                // Need to delete the old value.
                $end = strpos($file, "\n", $pos);
                $text = substr($file, 0, $pos) . substr($file, $end + 1);
            }
            $string = input_to_literal($value, style: 'double');
            $text .= "\$lang['$key'] = $string;\n";
        }
        
        // Save data
        file_put_contents($filepath, $text);
        unset($this->langCache[$langfile . '.lang.php']);
        return true;
    }

    /**
     * Handles any unexpected configuration that prevented the translation from loading.
     *
     * @since 1.9.11
     */
    public function langPanic()
    {
        if ($this->loadLang()) {
            return;
        }
        header('HTTP/1.0 500 Internal Server Error');
        echo 'Error: XMB failed to start because the default language is missing.  Please place English.lang.php in the lang subfolder to correct this.';
        throw new RuntimeException('The English.lang.php file is missing or unreadable.');
    }

    /**
     * Generate an HTML select element containing all available languages.
     *
     * @since 1.9.11
     * @param string $currentLangFile The devname currently in use by the subject (system/self/member/etc).
     * @return string
     */
    public function createLangFileSelect(string $currentLangFile): string
    {
        $phrases = $this->loadPhrases();

        $lfs = [];
        foreach ($phrases as $devname => $row) {
            if ($devname === $currentLangFile) {
                $lfs[] = "<option lang='{$row['iso639']}' value='$devname' selected='selected'>{$row['language']}</option>";
            } else {
                $lfs[] = "<option lang='{$row['iso639']}' value='$devname'>{$row['language']}</option>";
            }
        }
        return "<select name='langfilenew'>\n" . implode("\n", $lfs) . "\n</select>";
    }

    /**
     * Checks if the specified language is installed by confirming the file exists.
     *
     * @since 1.10.00
     */
    public function langFileExists(string $langfile): bool
    {
        if (! $this->dirCacheStatus) $this->initDirCache();

        return false !== array_search("$langfile.lang.php", $this->dirCache);
    }

    /**
     * Sets up the lang directory cache.
     *
     * @since 1.10.00
     */
    private function initDirCache()
    {
        if ($this->dirCacheStatus) return;

        $languages = scandir(ROOT . 'lang/');

        if (false === $languages) {
            $msg = 'Unable to read the /lang/ directory.  ';
            if ($this->vars->debug) {
                $msg .= 'See error log for details.';
            } else {
                $msg .= 'Enable debug mode in XMB conf.php for details.';
            }
            throw new RuntimeException($msg);
        }

        $this->dirCache = $languages;
        $this->dirCacheStatus = true;
    }
}
