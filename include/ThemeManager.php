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

namespace XMB\Theme;

use XMB\SQL;
use XMB\Template;
use XMB\Variables;

use function XMB\getInt;
use function XMB\PostedVar;

class Manager
{
    public function __construct(private SQL $sql, private Template $template, private Variables $vars)
    {
        // Property promotion.
    }
    
    public function setTheme()
    {
        // Get themes, [fid, [tid]]
        $action = postedVar('action', '', false, false, false, 'g');
        $forumtheme = 0;
        $fid = getInt('fid', 'r');
        $tid = getInt('tid', 'r');
        if ($tid > 0 && $action != 'templates') {
            $forum = $this->sql->getFIDFromTID($tid);
            if (count($forum) == 0) {
                $tid = 0;
                $fid = 0;
            } else {
                $fid = $forum['fid'];
                $forumtheme = (int) $forum['theme'];
            }
        } else if ($fid > 0) {
            $forum = getForum($fid);
            if (false === $forum || ($forum['type'] != 'forum' && $forum['type'] != 'sub') || $forum['status'] != 'on') {
                $forumtheme = 0;
            } else {
                $forumtheme = (int) $forum['theme'];
            }
        }

        // Check which theme to use
        $validtheme = false;
        $themeuser = (int) ($this->vars->self['theme'] ?? 0);
        if (!$validtheme && $themeuser > 0) {
            $theme = $themeuser;
            $row = sql()->getThemeByID($theme);
            if (! ($validtheme = (! empty($row)))) {
                $this->sql->resetUserTheme((int) $self['uid']);
            }
        }
        if (!$validtheme && $forumtheme > 0) {
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
                sql()->updateSetting('theme', $row['themeid']);
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
                . "' />";
        }

        // additional CSS to load?
        if (file_exists(ROOT . $this->vars->theme['imgdir'] . '/theme.css')) {
            $this->template->css .= "\n<link rel='stylesheet' type='text/css' href='"
                . $this->vars->full_url . $this->vars->theme['imgdir']
                . "/theme.css' />";
        }
    }

    /**
     * Calculates extra theme strings that are dynamically generated for every hit.
     *
     * @since 1.9.12
     */
    public function more_theme_vars()
    {
        // Alters certain visibility-variables
        if (false === strpos($this->vars->theme['bgcolor'], '.')) {
            $this->vars->theme['bgcode'] = 'background-color: ' . $this->vars->theme['bgcolor'] . ';';
        } else {
            $this->vars->theme['bgcode'] = 'background-image: url(' . $this->vars->theme['imgdir'] . '/' . $this->vars->theme['bgcolor'] . ');';
        }

        if (false === strpos($this->vars->theme['catcolor'], '.')) {
            $this->vars->theme['catbgcode'] = "bgcolor='" . $this->vars->theme['catcolor'] . "'";
            $this->vars->theme['catcss'] = "background-color: " . $this->vars->theme['catcolor'] . ";";
        } else {
            $this->vars->theme['catbgcode'] = "style='background-image: url(" . $this->vars->theme['imgdir'] . "/" . $this->vars->theme['catcolor'] . ")'";
            $this->vars->theme['catcss'] = "background-image: url(" . $this->vars->theme['imgdir'] . "/" . $this->vars->theme['catcolor'] . ");";
        }

        if (false === strpos($this->vars->theme['top'], '.')) {
            $this->vars->theme['topbgcode'] = "bgcolor='" . $this->vars->theme['top'] . "'";
        } else {
            $this->vars->theme['topbgcode'] = "style='background-image: url(" . $this->vars->theme['imgdir'] . "/" . $this->vars->theme['top'] . ")'";
        }

        null_string($this->vars->theme['boardimg']);
        $l = parse_url($this->vars->theme['boardimg']);
        if (!isset($l['scheme'])) {
            $this->vars->theme['boardimg'] = $this->vars->theme['imgdir'].'/'.$this->vars->theme['boardimg'];
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
}
