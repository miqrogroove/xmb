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

namespace XMB;

/**
 * Member rank logic
 *
 * @since 1.10.00
 */
class Ranks
{
    private bool $initialized = false;

    private array $specialrank = []; // The records for built-in staff ranks.
    private array $rankposts = []; // The records for non-staff ranks.
    private array $staffranks = [
        'Super Administrator',
        'Administrator',
        'Super Moderator',
        'Moderator',
    ];

    public function __construct(private Cache $cache, private Variables $vars)
    {
        // Property promotion
    }

    /**
     * Lazy load logic
     *
     * @since 1.12.00
     */
    private function init()
    {
        $queryranks = $this->getAll();
        foreach ($queryranks as $query) {
            $posts = (int) $query['posts'];
            if (in_array($query['title'], $this->staffranks)) {
                $this->specialrank[$query['title']] = &$query;
            } else {
                $this->rankposts[$posts] = &$query;
            }
            unset($query);
        }
        $this->initialized = true;
    }

    /**
     * Retrieve the whole ranks table
     *
     * @since 1.12.00
     * @param bool $noStaff Optional. When true, none of the staff ranks will be included.
     */
    public function getAll(bool $noStaff = false): array
    {
        $rows = $this->cache->tableRead('ranks');
        if ($noStaff) {
            $rows = array_filter($rows, $this->noStaffFilter(...));
        }
        return $rows;
    }

    /**
     * Callback for use with array_filter()
     *
     * @since 1.12.00
     */
    private function noStaffFilter(array $value): bool
    {
        return ! in_array($value['title'], $this->staffranks);
    }

    /**
     * Clears the server cache (but not the copy for this request)
     *
     * @since 1.12.00
     */
    public function invalidateCache()
    {
        $this->cache->tableDelete('ranks');
    }

    /**
     * Find the rank for a member.
     *
     * @param string $status The members.status value.
     * @param int $postCount The members.postnum value.
     * @return array The rank record for the member.
     */
    public function find(string $status, int $postCount): array
    {
        if (! $this->initialized) $this->init();

        if (in_array($status, $this->staffranks)) {
            // Specify the staff rank.
            $rank = [
                'allowavatars' => $this->specialrank[$status]['allowavatars'],
                'title' => $this->vars->lang[$this->vars->status_translate[$this->vars->status_enum[$status]]],
                'stars' => $this->specialrank[$status]['stars'],
                'avatarrank' => $this->specialrank[$status]['avatarrank'],
            ];
        } elseif ($status == 'Banned') {
            // Specify no rank.
            $rank = [
                'allowavatars' => 'no',
                'title' => $this->vars->lang['textbanned'],
                'stars' => 0,
                'avatarrank' => '',
            ];
        } elseif (count($this->rankposts) === 0) {
            // Specify no rank.
            $rank = [
                'allowavatars' => 'no',
                'title' => '',
                'stars' => 0,
                'avatarrank' => '',
            ];
        } else {
            // Find the appropriate member rank.
            $max = -1;
            $keys = array_keys($this->rankposts);
            foreach ($keys as $key) {
                if ($postCount >= (int) $key && (int) $key > (int) $max) {
                    $max = $key;
                }
            }
            $rank = &$this->rankposts[$max];
        }
        return $rank;
    }
}
