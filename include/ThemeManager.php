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

class ThemeManager
{
    public function __construct(private Forums $forums, private SQL $sql, private Template $template, private Variables $vars)
    {
        // Property promotion.
    }
    
    /**
     * Determines which theme to use for the current request.
     *
     * @since 1.10.00
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
        $themeuser = (int) ($this->vars->self['theme'] ?? 0);
        if (! $validtheme && $themeuser > 0) {
            $theme = $themeuser;
            $row = $this->sql->getThemeByID($theme);
            if (! ($validtheme = (! empty($row)))) {
                $this->sql->resetUserTheme((int) $this->vars->self['uid']);
            }
        }
        if (! $validtheme && $forumtheme > 0) {
            $theme = $forumtheme;
            $row = $this->sql->getThemeByID($theme);
            if (! ($validtheme = (! empty($row)))) {
                $this->sql->resetForumTheme($fid);
            }
        }
        if (! $validtheme) {
            $theme = (int) $this->vars->settings['theme'];
            $row = $this->sql->getThemeByID($theme);
            $validtheme = (! empty($row));
        }
        if (! $validtheme) {
            $row = $this->sql->getFirstTheme();
            if ($validtheme = (count($row) > 0)) {
                $this->vars->settings['theme'] = $row['themeid'];
                $this->sql->updateSetting('theme', $row['themeid']);
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

        $this->template->css = '';
        if ((int) $this->vars->settings['schema_version'] >= 6) {
            $this->template->css = "<link rel='stylesheet' type='text/css' href='"
                . $this->vars->full_url . "css.php"
                . "?id=" . $this->vars->theme['themeid']
                . "&amp;v=" . $this->vars->theme['version']
                . "' />\n";
        }

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
        // Alters certain visibility-variables
        if (false === strpos($this->vars->theme['bgcolor'], '.')) {
            $this->vars->theme['bgcode'] = 'background-color: ' . $this->vars->theme['bgcolor'] . ';';
        } else {
            $this->vars->theme['bgcode'] = 'background-image: url(' . $this->vars->full_url . $this->vars->theme['imgdir'] . '/' . $this->vars->theme['bgcolor'] . ');';
        }

        if (false === strpos($this->vars->theme['catcolor'], '.')) {
            $this->vars->theme['catbgcode'] = "bgcolor='" . $this->vars->theme['catcolor'] . "'";
            $this->vars->theme['catcss'] = "background-color: " . $this->vars->theme['catcolor'] . ";\n";
        } else {
            $this->vars->theme['catbgcode'] = "style='background-image: url(" . $this->vars->theme['imgdir'] . "/" . $this->vars->theme['catcolor'] . ")'";
            $this->vars->theme['catcss'] = "background-image: url(" . $this->vars->full_url . $this->vars->theme['imgdir'] . "/" . $this->vars->theme['catcolor'] . ");\n";
        }

        if (false === strpos($this->vars->theme['top'], '.')) {
            $this->vars->theme['topbgcode'] = "bgcolor='" . $this->vars->theme['top'] . "'";
        } else {
            $this->vars->theme['topbgcode'] = "style='background-image: url(" . $this->vars->full_url . $this->vars->theme['imgdir'] . "/" . $this->vars->theme['top'] . ")'";
        }

        null_string($this->vars->theme['boardimg']);
        $l = parse_url($this->vars->theme['boardimg']);
        if (! isset($l['scheme'])) {
            $this->vars->theme['boardimg'] = $this->vars->full_url . $this->vars->theme['imgdir'].'/'.$this->vars->theme['boardimg'];
        }
        $this->vars->theme['logo'] = "<a href='./'><img src='" . $this->vars->theme['boardimg'] . "' alt='" . $this->vars->settings['bbname'] . "' border='0' /></a>";

        // Font stuff...
        $this->vars->theme['font1'] = $this->fontSize(-1);
        $this->vars->theme['font3'] = $this->fontSize(2);
    }

    /**
     * Adds relative font size values to the theme's font size.
     *
     * @since 1.9.12.07
     * @param int $add Change applied to the theme font size.
     * @return string CSS font size, like '12px'.
     */
    function fontSize(int $add): string
    {
        static $cachedFs;

        // Cache the theme font size in an array.
        if (!isset($cachedFs)) {
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
     * @since 1.10.00
     * @param string $nameAttr The HTML name attribute for the selector element. Must be HTML encoded.
     * @param ?int $selection The previously selected value, or null.
     * @param bool $allowDefault Optional. When true, an extra value is provided to represent the default theme.
     */
    function selector(string $nameAttr, ?int $selection, bool $allowDefault = true)
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
}
