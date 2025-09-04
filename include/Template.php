<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-2
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

class Template
{
    private array $data = [];

    public function __construct(private Variables $vars)
    {
        // Property promotion.
    }

    public function __set($name, $value)
    {
        if ($name == 'data') {
            throw new LogicException("The Template service's data property is private.");
        }

        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        if (! array_key_exists($name, $this->data)) {
            $trace = debug_backtrace();
            trigger_error(
                'Undefined property via __get(): ' . $name .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
                E_USER_NOTICE);
            return null;
        } else if ($name == 'data') {
            throw new LogicException("The Template service's data property is private.");
        }

        return $this->data[$name];
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * These variables are almost always needed in the header and footer templates.
     *
     * In case the error template gets used early in the bootup, it's best to initialize those variables.
     *
     * This is not called by the constructor because the Template code can be re-used for custom scripts,
     * or for individual templates, to help eliminate variable name conflicts.
     */
    public function init()
    {
        $list = [
            'bbcodescript',
            'browser',
            'canonical_link',
            'copyright',
            'css',
            'lastvisittext',
            'links',
            'navigation',
            'newu2umsg',
            'notify',
            'pluglink',
            'quickjump',
            'searchlink',
            'threadSubject',
            'versionbuild',
            'versioncompany',
            'versionlong',
        ];

        foreach ($list as $name) {
            $this->data[$name] = '';
        }

        $this->data['footerstuff'] = [
            'load' => '',
            'phpsql' => '',
            'totaltime' => '',
            'querydump' => '',
            'querynum' => '',
        ];

        $this->addRefs();
    }

    /**
     * This can be used as often as needed to ensure the references are current.
     *
     * @param bool $translationOnly Skip the Theme and other references.
     */
    public function addRefs(bool $translationOnly = false)
    {
        $this->data['lang'] = &$this->vars->lang;

        if ($translationOnly) return;

        $this->data['full_url'] = &$this->vars->full_url;
        $this->data['SETTINGS'] = &$this->vars->settings;
        $this->data['THEME'] = &$this->vars->theme;
    }

    /**
     * XMB Template Processor
     *
     * @since 1.0 Formerly "template()".
     * @since 1.10.00 Now using disk-stored files and full PHP format.
     * @param string $filename The filename, including extension, of the PHP template.
     * @param bool $echo Optional. When true, the processed template will be sent to the output stream.
     * @return string When $echo is false, the processed template will be returned, otherwise an empty string.
     */
    public function process(string $filename, bool $echo = false): string
    {
        extract($this->data);

        if (! $echo) ob_start();
        
        if ($this->vars->comment_output) echo "<!--Begin Template: $filename -->\n";

        include ROOT . "templates/$filename";

        if ($this->vars->comment_output) echo "\n<!-- End Template: $filename -->";

        if ($echo) {
            return '';
        } else {
            return ob_get_clean();
        }
    }
}
