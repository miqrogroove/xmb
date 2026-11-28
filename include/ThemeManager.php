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

use RuntimeException;

/**
 * Responsible for loading, caching, and general logic needed by all themes.
 *
 * @since 1.10.00
 */
class ThemeManager
{
    public function __construct(
        private Cache $cache,
        private Forums $forums,
        private Settings $settings,
        private SQL $sql,
        private Template $template,
        private Variables $vars, 
    ) {
        // Property promotion.
    }
    
    /**
     * Determines which theme to use for the current request.
     */
    public function setTheme()
    {
        // Get themes, [fid, [tid]]
        $action = getPhpInput('action', 'g');
        $forumtheme = 0;
        $fid = getInt('fid', 'r');
        $tid = getInt('tid', 'r');
        if ($tid > 0) {
            $forum = $this->sql->getFIDFromTID($tid, getThemeIDToo: true);
            if (count($forum) == 0) {
                $tid = 0;
                $fid = 0;
            } else {
                $fid = (int) $forum['fid'];
                $forumtheme = (int) $forum['theme'];
            }
        } elseif ($fid > 0) {
            $forum = $this->forums->getForum($fid);
            if (null === $forum || ($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on') {
                $forumtheme = 0;
            } else {
                $forumtheme = (int) $forum['theme'];
            }
        }

        // Check which theme to use
        $validtheme = false;
        $themeuser = (int) arrayCoalesce($this->vars->self, 'theme');
        if (! $validtheme && $themeuser > 0) {
            $theme = $themeuser;
            $row = $this->getCachedTheme($theme);
            if (! ($validtheme = (! empty($row)))) {
                $this->sql->resetUserTheme((int) $this->vars->self['uid']);
            }
        }
        if (! $validtheme && $forumtheme > 0) {
            $theme = $forumtheme;
            $row = $this->getCachedTheme($theme);
            if (! ($validtheme = (! empty($row)))) {
                $this->sql->resetForumTheme($fid);
                $this->forums->invalidateCache();
            }
        }
        if (! $validtheme) {
            $theme = (int) $this->settings->get('theme');
            $row = $this->getCachedTheme($theme);
            $validtheme = (! empty($row));
        }
        if (! $validtheme) {
            $row = $this->sql->getFirstTheme();
            if ($validtheme = (count($row) > 0)) {
                $this->settings->put('theme', $row['themeid']);
            }
        }
        if (! $validtheme) {
            header('HTTP/1.0 500 Internal Server Error');
            exit('Fatal Error: The XMB themes table is empty.');
        }

        // Set the theme vars.
        $this->vars->theme = &$row;
        $this->template->addRefs();
        unset($row);
        $this->more_theme_vars();

        $this->template->css = "<link rel='stylesheet' type='text/css' href='"
            . $this->vars->full_url . "css.php"
            . "?id=" . $this->vars->theme['themeid']
            . "&amp;v=" . $this->vars->theme['version']
            . "' />\n";

        // additional CSS to load?
        if (file_exists(ROOT . $this->vars->theme['imgdir'] . '/theme.css')) {
            $this->template->css .= "<link rel='stylesheet' type='text/css' href='"
                . $this->vars->full_url . $this->vars->theme['imgdir']
                . "/theme.css' />\n";
        }
    }

    /**
     * Calculates extra theme strings that are dynamically generated for every request.
     *
     * @since 1.9.12
     */
    public function more_theme_vars()
    {
        // Correct any missing unit names.
        if (is_numeric($this->vars->theme['borderwidth'])) {
            $this->vars->theme['borderwidth'] = trim($this->vars->theme['borderwidth']) . 'px';
        }
        if (is_numeric($this->vars->theme['tablespace'])) {
            $this->vars->theme['tablespace'] = trim($this->vars->theme['tablespace']) . 'px';
        }
        if (is_numeric($this->vars->theme['tablewidth'])) {
            if ($this->vars->theme['tablewidth'] > 100) {
                $unit = 'px';
            } else {
                $unit = '%';
            }
            $this->vars->theme['tablewidth'] = trim($this->vars->theme['tablewidth']) . $unit;
        }

        $this->vars->theme['bgcode'] = $this->makeBackgroundStyle($this->vars->theme['bgcolor'], $this->vars->theme['imgdir']);
        $this->vars->theme['catbgcode'] = $this->makeBackgroundAttr($this->vars->theme['catcolor'], $this->vars->theme['imgdir']);
        $this->vars->theme['catcss'] = $this->makeBackgroundStyle($this->vars->theme['catcolor'], $this->vars->theme['imgdir']);
        $this->vars->theme['topbgcode'] = $this->makeBackgroundStyle($this->vars->theme['top'], $this->vars->theme['imgdir']);

        null_string($this->vars->theme['boardimg']);
        $l = parse_url($this->vars->theme['boardimg']);
        if (! isset($l['scheme'])) {
            $this->vars->theme['boardimg'] = $this->vars->full_url . $this->vars->theme['imgdir'].'/'.$this->vars->theme['boardimg'];
        }
        $this->vars->theme['logo'] = "<a href='" . $this->vars->full_url . "'><img src='" . $this->vars->theme['boardimg'] . "' alt='" . $this->settings->get('bbname') . "' border='0' /></a>";

        // Font stuff...
        $this->vars->theme['font-smaller-2'] = $this->fontSize(-2);
        $this->vars->theme['font-smaller-1'] = $this->fontSize(-1);
        $this->vars->theme['font-larger-1'] = $this->fontSize(1);
        $this->vars->theme['font-larger-2'] = $this->fontSize(2);

        // Legacy variable names
        $this->vars->theme['font1'] = $this->vars->theme['font-smaller-1'];
        $this->vars->theme['font3'] = $this->vars->theme['font-larger-2'];
    }

    /**
     * Create an HTML attribute to implement a background color or filename.
     *
     * @since 1.10.07
     */
    public function makeBackgroundAttr(string $background, string $imgdir): string
    {
        if (false === strpos($background, '.')) {
            return "bgcolor='$setting'";
        } else {
            return "style='background-image: url(" . $this->vars->full_url . $imgdir . "/" . $background . ");'";
        }
    }

    /**
     * Create a CSS property to implement a background color or filename.
     *
     * @since 1.10.07
     */
    public function makeBackgroundStyle(string $background, string $imgdir): string
    {
        if (false === strpos($background, '.')) {
            return "background-color: $setting;\n";
        } else {
            return "background-image: url(" . $this->vars->full_url . $imgdir . "/" . $background . ");\n";
        }
    }

    /**
     * Create HTML attributes to represent any HiDPI image resources in the active theme.
     *
     * Resources are expected to be named like reply.gif and reply-2x.jpg to match the logic here.
     *
     * @since 1.12.00
     */
    public function makeImgSrc(string $name, array $fileList): string
    {
        $resources = $this->getResourcesFromFileList($name, $fileList);
        $parts = [];
        $srcset = [];
        foreach ($resources as $filename) {
            if (str_starts_with($filename, $name . '.')) {
                $parts[] = 'src="' . $this->vars->full_url . $this->vars->theme['imgdir'] . '/' . $filename . '"';
            } elseif (str_starts_with($filename, $name . '-')) {
                // Find the x-descriptor.
                $pos = strlen($name) + 1; // Advance past the '-'.
                $endpos = strpos($filename, 'x.', $pos);
                if ($endpos !== false) {
                    $xValue = (float) substr($filename, $pos, $endpos - $pos);
                    if ($xValue > 1) {
                        $srcset[] = $this->vars->full_url . $this->vars->theme['imgdir'] . '/' . $filename . ' ' . $xValue . 'x';
                    }
                }
            }
        }
        if (! empty($srcset)) {
            $parts[] = 'srcset="' . implode(' ', $srcset) . '"';
        };
        return implode(' ', $parts);
    }

    /**
     * Adds relative font size values to the theme's font size.
     *
     * @since 1.9.12.07
     * @param int $add Change applied to the theme font size.
     * @return string CSS font size, like '12px'.
     */
    public function fontSize(int $add): string
    {
        static $cachedFs;

        // Cache the theme font size in an array.
        if (! isset($cachedFs)) {
            preg_match('#([0-9]+)([a-z]*)#i', $this->vars->theme['fontsize'], $result);
            if (empty($result[1])) {
                $result[1] = '12';
            }
            if (empty($result[2])) {
                $result[2] = 'px';
            }
            $cachedFs = [
                'qty'  => (int) $result[1],
                'unit' => $result[2],
            ];
        }

        $css = ($cachedFs['qty'] + $add) . $cachedFs['unit'];

        return $css;
    }

    /**
     * Generates the HTML for a theme select control based on all available themes.
     *
     * @param string $nameAttr The HTML name attribute for the selector element. Must be HTML encoded.
     * @param ?int $selection The previously selected value, or null.
     * @param bool $allowDefault Optional. When true, an extra value is provided to represent the default theme.
     */
    public function selector(string $nameAttr, ?int $selection, bool $allowDefault = true): string
    {
        $themelist = [
            "<select name='$nameAttr'>",
        ];
        if ($allowDefault) $themelist[] = "<option value='0'>" . $this->vars->lang['textusedefault'] . "</option>";

        $themes = $this->sql->getThemeNames();
        foreach ($themes as $themeinfo) {
            $selected = (int) $themeinfo['themeid'] === $selection ? $this->vars::selHTML : '';
            $themelist[] = "<option value='{$themeinfo['themeid']}' $selected>{$themeinfo['name']}</option>";
        }
        $themelist[] = '</select>';

        return implode("\n", $themelist);
    }

    /**
     * Prevent use of font size values like '1px'
     *
     * @since 1.10.07
     */
    public function clampFontSize(string $fontSize): string
    {
        $min = 6;
        $max = 32;

        $fontSize = trim($fontSize);
        if (substr($fontSize, -2) == 'px' || is_numeric($fontSize)) {
            $px = (int) $fontSize;
            if ($px < $min) {
                $fontSize = $min . 'px';
            } elseif ($px > $max) {
                $fontSize = $max . 'px';
            }
        }
        return $fontSize;
    }

    /**
     * Get a theme by ID, and try the server cache
     *
     * @since 1.12.00
     */
    public function getCachedTheme(int $id): array
    {
        $key = $this->sql->tablepre . 'themes_' . $id;
        $value = $this->cache->read($key);
        if ($value === null) {
            $value = $this->sql->getThemeByID($id);
            if (! empty($value)) {
                $this->cache->write($key, $value);
            }
        }

        return $value;
    }

    /**
     * Clear one theme from the server cache
     *
     * @since 1.12.00
     */
    public function invalidateCachedTheme(int $id)
    {
        $key = $this->sql->tablepre . 'themes_' . $id;
        $this->cache->delete($key);
        $this->invalidateFileList($id);
    }

    /**
     * Clear all themes from all caches
     *
     * @since 1.12.00
     */
    public function invalidateCache()
    {
        // Client cache
        $this->sql->raiseThemeVersions();

        // Server cache
        $regex = '/^' . preg_quote($this->sql->tablepre . 'themes_') . '\\d++$/';
        $this->cache->deleteByRegex($regex);
        $this->invalidateAllFileLists();
    }

    /**
     * Retrieve the list of files in the active theme.
     *
     * Each list for each theme will be cached separately in Settings.
     *
     * @since 1.12.00
     */
    public function getFileList(): array
    {
        $id = (int) $this->vars->theme['themeid'];
        $key = $this->fileListKey($id);
        $text = $this->settings->get($key);
        if ('' === $text) {
            $list = $this->generateFileList();
        } else {
            $text = base64_decode($text, strict: true);
            if (is_string($text)) {
                $list = unserialize($text);
            } else {
                $list = false;
            }
            if (empty($list) || ! is_array($list)) {
                $text = '';
                $this->invalidateFileList((int) $id);
                $list = $this->generateFileList();
            }
        }
        if ('' === $text && ! empty($list)) {
            $value = base64_encode(serialize($list));
            if (strlen($value) > $this->settings::VALUE_BYTE_LIMIT) throw new RuntimeException('There are too many files in ' . $this->vars->theme['imgdir']);
            $this->settings->put($key, $value);
        }
        return $list;
    }

    /**
     * Retrieve the list of files matching a specific name, from a file list.
     *
     * The caller should first grab the result of getFileList() to help avoid repeated filesystem operations.
     *
     * @since 1.12.00
     */
    public function getResourcesFromFileList(string $name, array $list): array
    {
        $result = [];
        foreach ($list as $filename) {
            if (str_starts_with($filename, $name)) {
                $result[] = $filename;
            }
        }
        return $result;
    }

    /**
     * Create the key string used for storing a file list as a setting.
     *
     * @since 1.12.00
     */
    private function fileListKey(int $id): string
    {
        return 'theme_file_list_' . $id;
    }

    /**
     * Remove the cached copy of a theme's file list.
     *
     * @since 1.12.00
     */
    public function invalidateFileList(int $id)
    {
        $key = $this->fileListKey($id);
        $this->settings->delete($key);
    }

    /**
     * Remove all cached file lists.
     *
     * @since 1.12.00
     */
    public function invalidateAllFileLists()
    {
        $this->settings->deleteByPrefix('theme_file_list_');
    }

    /**
     * Create a list of files in the active theme.
     *
     * @since 1.12.00
     */
    private function generateFileList(): array
    {
        $path = ROOT . $this->vars->theme['imgdir'];
        $list = glob($path . '/*.*');
        if (false === $list) $list = [];
        return array_map('basename', $list);
    }
}
