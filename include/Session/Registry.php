<?php

/**
 * eXtreme Message Board
 * XMB 1.10
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2026, The XMB Group
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

namespace XMB\Session;

use RuntimeException;

/**
 * The Session Registry holds instances of Session Mechanisms.
 *
 * This is the extension point for adding custom Session logic.
 *
 * @since 1.10.05
 */
class Registry
{
    private array $mechanisms = [];

    public function add(Mechanism $logic, int $priority)
    {
        $name = $logic->getServiceID();
        if (isset($mechanisms[$name])) {
            throw new RuntimeException('Duplicates are not allowed in the Session Registry');
        }
        $this->mechanisms[$name] = [
            'priority' => $priority,
            'service' => $logic,
        ];
    }

    public function remove(string $name)
    {
        unset($this->mechanisms[$name]);
    }

    public function get(string $name): ?Mechanism
    {
        return $this->mechanisms[$name] ?? null;
    }

    public function getAllSortedByPriority(): array
    {
        $priorities = array_column($this->mechanisms, 'priority');
        $services = array_column($this->mechanisms, 'service');
        array_multisort($priorities, $services);

        return $services;
    }
}
