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
     * @param DependencyContext $context Optional. Context for tracking resolver factory recursion.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get(string $id, ?DependencyContext $context = null): object
    {
        $class = $this->find($id);

        if ($class === null) throw new NotFoundException();

        $service = $this->services[$class] ?? null;

        if ($service === null) {
            // Start tracking recursion.
            if ($context === null) {
                $context = new DependencyContext();
            } elseif (in_array($class, $context->stack)) {
                throw new ContainerException("Circular dependency detected for service class $class");
            }
            $context->stack[] = $class;

            // Invoke the factory and pass a closure to allow resolution of dependencies.
            $factory = $this->factories[$class];
            $closure = function (string $id) use ($context): object
            {
                // Pass the context into the next get() call.
                return $this->get($id, $context);
            };
            try {
                $service = $factory($closure);
            } finally {
                // Due to the recursive nature of this method, we have to account for the possibility
                // that any Exception thrown by dependency resolution could be caught by a parent caller
                // and we would continue to use the context in that situation, so it must be cleaned up here.
                array_pop($context->stack);
            }
            $this->services[$class] = $service;
        }

        return $service;
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

    /**
     * Find the registered class name from an ID string.
     *
     * A registered class name ID will be returned as-is.
     * Any registered alias ID will be resolved to the class name.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return ?string
     */
    private function find(string $id): ?string
    {
        $class = $this->aliases[$id] ?? null;

        if ($class === null && isset($this->factories[$id])) {
            $class = $id;
        }

        return $class;
    }
}
