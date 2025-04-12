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
 * Forums table caching and retrieval.
 *
 * Lazy loading will occur when calling any of the non-constructor methods.
 *
 * @since 1.10.00
 */
class Forums
{
    private bool $cacheStatus = false;

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
    private function initCache()
    {
        if (! $this->cacheStatus) {
            $this->forumcache = $this->sql->getForums();
            $this->cacheStatus = true;
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
        if (! $this->cacheStatus) $this->initCache();

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
        if (! $this->cacheStatus) $this->initCache();

        if (isset($this->forumcache[$fid])) {
            return $this->forumcache[$fid];
        } else {
            return null;
        }
    }

    /**
     * Creates an array of the active child forums.
     *
     * @since 1.10.00
     * @param int $fid The parent forum ID.
     * @return array
     */
    public function getChildForums(int $fid): array
    {
        if (! $this->cacheStatus) $this->initCache();

        $children = [];

        foreach($this->forumcache as $forum) {
            if ((int) $forum['fup'] == $fid && $forum['type'] == 'sub') {
                $children[] = $forum;
            }
        }

        return $children;
    }
}
