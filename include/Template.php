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
     * This can be used as often as needed to ensure the references are current.
     */
    public function addRefs()
    {
        $this->data['lang'] = &$this->vars->lang;
        $this->data['SETTINGS'] = &$this->vars->settings;
        $this->data['THEME'] = &$this->vars->theme;
    }

    public function process(string $filename, bool $echo = false): string
    {
        $code = 'This is a test: $myvar $ovar';
        
        //var_dump(get_defined_vars());
        //exit;
        
        extract($this->data);
        
        eval('$output = "' . $code . '";');
        echo $output;
    }
}
