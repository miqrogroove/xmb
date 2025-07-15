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

use LogicException;
use UConverter;

/**
 * Experimental string encoding logic for a future version.
 *
 * @since 1.10.00
 */
class ConvertToUTF8
{
    public function fromCharset(string $input, string $charset): string
    {
        switch ($charset) {
            case 'ISO-8859-10':
                $output = $this->latin6ToUTF8($input);
                break;
            default:
                throw new LogicException('Unknown charset');
        }

        return $output;
    }

    public function latin6ToUTF8(string $iso_8859_10_encoded): string
    {
        $library = new UConverter();
        
        return $library->transcode($iso_8859_10_encoded, 'UTF-8', 'iso-8859_10-1998');
    }
}
