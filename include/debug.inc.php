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

class Debug
{
    public function __construct(private DBStuff $db)
    {
        // Property promotion.
    }
    
    public function printAllQueries(): string
    {
        $stuff = array();
        if (X_SADMIN) {
            $querytimes = $this->db->getQueryTimes();
            $stuff[] = '<table style="width: 97%;"><colgroup span="2" /><tr><td style="width: 2em;">#</td><td style="width: 8em;">Duration:</td><td>Query:</td></tr>';
            foreach($this->db->getQueryList() as $key => $val) {
                $val = mysql_syn_highlight(cdataOut($val));
                $stuff[] = '<tr><td><strong>'.++$key.'.</strong></td><td>'.number_format($querytimes[$key-1], 8).'</td><td>'.$val.'</td></tr>';
            }
            $stuff[] = '</table>';
        }
        return implode("\n", $stuff);
    }

    private function mysql_syn_highlight($query)
    {
        $find = array();
        $replace = array();

        $find[] = 'SELECT';
        $find[] = 'UPDATE ';
        $find[] = 'DELETE';
        $find[] = 'INSERT INTO ';
        $find[] = 'INSERT IGNORE INTO ';
        $find[] = ' DUPLICATE KEY ';
        $find[] = ' WHERE ';
        $find[] = ' ON ';
        $find[] = ' FROM ';
        $find[] = ' GROUP BY ';
        $find[] = 'ORDER BY ';
        $find[] = ' LEFT JOIN ';
        $find[] = ' RIGHT JOIN ';
        $find[] = ' INNER JOIN ';
        $find[] = ' IN ';
        $find[] = ' SET ';
        $find[] = ' AS ';
        $find[] = '(';
        $find[] = ')';
        $find[] = ' ASC';
        $find[] = ' DESC';
        $find[] = ' AND ';
        $find[] = ' OR ';
        $find[] = ' NOT';
        $find[] = ' USING';
        $find[] = ' VALUES ';
        $find[] = ' UNION ALL ';

        foreach($find as $key=>$val) {
            $replace[$key] = '</em><strong>'.$val.'</strong><em>';
        }

        return '<em>'.str_replace($find, $replace, $query).'</em>';
    }
}
