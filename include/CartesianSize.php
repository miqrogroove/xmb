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

/**
 * Rectangluar dimension object for simple operations and properties.
 *
 * @since 1.9.11
 */
class CartesianSize
{
    public function __construct(private int $width = 0, private int $height = 0)
    {
        // Property promotion
    }

    public function aspect(): float
    {
        return $this->width / $this->height;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function isBiggerThan(CartesianSize $otherSize): bool
    {
        // Would overload '>' operator
        return ($this->width > $otherSize->getWidth() || $this->height > $otherSize->getHeight());
    }

    public function isSmallerThan(CartesianSize $otherSize): bool
    {
        // Would overload '<=' operator
        return ($this->width <= $otherSize->getWidth() && $this->height <= $otherSize->getHeight());
    }
    
    public function fromArray(array $input): bool
    {
        $this->width = (int) $input[0];
        $this->height = (int) $input[1];
        return ($this->width > 0 && $this->height > 0);
    }
    
    public function fromString(string $input): bool
    {
        return $this->fromArray(explode('x', $input));
    }
    
    public function __toString(): string
    {
        return $this->width . 'x' . $this->height;
    }
}
