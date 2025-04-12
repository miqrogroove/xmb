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

/**
 * Query printing logic, used only in debug mode.
 *
 * @since 1.10.00
 */
class Debug
{
    public function __construct(private DBStuff $db)
    {
        // Property promotion.
    }
    
    /**
     * Converts the DB query history to an HTML table.
     *
     * @since 1.9.11
     */
    public function printAllQueries(): string
    {
        $stuff = array();
        if (X_SADMIN) {
            $querytimes = $this->db->getQueryTimes();
            $stuff[] = '<table style="width: 97%;"><colgroup span="2" /><tr><td style="width: 2em;">#</td><td style="width: 8em;">Duration:</td><td>Query:</td></tr>';
            foreach($this->db->getQueryList() as $key => $val) {
                $val = $this->mysql_syn_highlight(cdataOut($val));
                $stuff[] = '<tr><td><strong>'.++$key.'.</strong></td><td>'.number_format($querytimes[$key-1], 8).'</td><td>'.$val.'</td></tr>';
            }
            $stuff[] = '</table>';
        }
        return implode("\n", $stuff);
    }

    /**
     * Tags MySQL keywords.
     *
     * @since 1.9.1
     */
    private function mysql_syn_highlight(string $query)
    {
        $find = [
            'SELECT',
            'UPDATE ',
            'DELETE',
            'INSERT INTO ',
            'INSERT IGNORE INTO ',
            'TRUNCATE ',
            ' DUPLICATE KEY ',
            ' WHERE ',
            ' ON ',
            ' FROM ',
            ' GROUP BY ',
            'ORDER BY ',
            ' LEFT JOIN ',
            ' RIGHT JOIN ',
            ' INNER JOIN ',
            ' IN ',
            ' SET ',
            ' AS ',
            '(',
            ')',
            ' ASC',
            ' DESC',
            ' AND ',
            ' OR ',
            ' NOT',
            ' USING',
            ' VALUES ',
            ' UNION ALL',
        ];

        $replace = [];
        foreach($find as $key => $val) {
            $replace[$key] = "</em><strong>$val</strong><em>";
        }

        return '<em>' . str_replace($find, $replace, $query) . '</em>';
    }
}
