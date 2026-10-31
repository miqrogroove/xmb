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

namespace XMB\Services;

use LogicException;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private array $aliases = [];
    private array $factories = [];
    private array $services = [];

    public function set(string $class, callable $factory, array $aliases = [])
    {
        if (isset($this->services[$class])) {
            throw new LogicException("Attempted duplication of class '$class' in a Container is not allowed.");
        }
        foreach ($aliases as $alias) {
            if (isset($this->aliases[$alias])) {
                throw new LogicException("Attempted duplication of alias '$alias' in a Container is not allowed.");
            }
        }
        foreach ($aliases as $alias) {
            $this->aliases[$alias] = $class;
        }

        $this->factories[$class] = $factory;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get(string $id): object
    {
        $factory = $this->find($id);

        if ($factory === null) throw new NotFoundException();

        if (! isset($this->services[$id])) {
            // Invoke the factory and pass the container to allow resolution of dependencies.
            $this->services[$id] = $factory($this);
        }

        return $this->services[$id];
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        $service = $this->find($id);

        return $service !== null;
    }

    private function find(string $id): ?callable
    {
        $factory = null;

        if (isset($this->factories[$id])) {
            $factory = $this->factories[$id];
        } elseif (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
            $factory = $this->factories[$id];
        }

        return $factory;
    }
}
