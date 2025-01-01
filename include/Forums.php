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
 * Forums table caching and retrieval.
 *
 * @since 1.10.00
 */
class Forums
{
    private bool $forumCacheStatus = false;

    private array $forumcache = [];

    public function __construct(private SQL $sql)
    {
        // Property promotion.
    }
    
    /**
     * Sets up the forum cache for Core use.
     *
     * @since 1.10.00
     */
    private function initForumCache()
    {
        if (! $this->forumCacheStatus) {
            $this->forumcache = $this->sql->getForums();
            $this->forumCacheStatus = true;
        }
    }

    /**
     * Provides an array containing all active forums and forum categories.
     *
     * @since 1.9.11
     * @return array
     */
    public function forumCache()
    {
        if (! $this->forumCacheStatus) $this->initForumCache();

        return $this->forumcache;
    }

    /**
     * Creates an associative array for the specified forum.
     *
     * @since 1.9.11
     * @param int $fid
     * @return ?array
     */
    public function getForum(int $fid): ?array
    {
        if (! $this->forumCacheStatus) $this->initForumCache();

        if (isset($this->forumcache[$fid])) {
            return $this->forumcache[$fid];
        } else {
            return null;
        }
    }

    /**
     * Creates an array of the child forums.
     *
     * @since 1.10.00
     * @param int $fid
     * @return array
     */
    public function getChildForums(int $fid): array
    {
        if (! $this->forumCacheStatus) $this->initForumCache();

        $children = [];

        foreach($this->forumcache as $forum) {
            if ((int) $forum['fup'] == $fid && $forum['type'] == 'sub') {
                $children[] = $forum;
            }
        }

        return $children;
    }
}
