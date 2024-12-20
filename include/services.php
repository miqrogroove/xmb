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

namespace XMB\Services;

use XMB\Attach;
use XMB\Debug;
use XMB\DBStuff;
use XMB\Observer;
use XMB\Session\Manager as SessionMgr;
use XMB\SQL;
use XMB\Template;
use XMB\Theme\Manager as ThemeMgr;
use XMB\Variables;

/**
 * Get the shared file attachment service.
 *
 * @since 1.10.00
 * @param Attach $attach Required on first call, otherwise optional. Acts as the setter.
 * @return Attach
 */
function attach(?Attach $attach = null): Attach
{
    static $cache;
    
    if ($attach !== null) $cache = $attach;
    
    return $cache;
}

/**
 * Get the shared database service.
 *
 * @since 1.10.00
 * @param DBStuff $db Required on first call, otherwise optional. Acts as the setter.
 * @return DBStuff
 */
function db(?DBStuff $db = null): DBStuff
{
    static $cache;
    
    if ($db !== null) $cache = $db;
    
    return $cache;
}

/**
 * Get the shared debug service.
 *
 * @since 1.10.00
 * @param Debug $debug Required on first call, otherwise optional. Acts as the setter.
 * @return Debug
 */
function debug(?Debug $debug = null): Debug
{
    static $cache;
    
    if ($debug !== null) $cache = $debug;
    
    return $cache;
}

/**
 * Get the shared observer service.
 *
 * @since 1.10.00
 * @param Observer $observer Required on first call, otherwise optional. Acts as the setter.
 * @return Observer
 */
function observer(?Observer $observer = null): Observer
{
    static $cache;
    
    if ($observer !== null) $cache = $observer;
    
    return $cache;
}

/**
 * Get the shared session manager.
 *
 * @since 1.10.00
 * @param SessionMgr $session Required on first call, otherwise optional. Acts as the setter.
 * @return SessionMgr
 */
function session(?SessionMgr $session = null): SessionMgr
{
    static $cache;
    
    if ($session !== null) $cache = $session;
    
    return $cache;
}

/**
 * Get the shared SQL service.
 *
 * @since 1.10.00
 * @param SQL $xmbsql Required on first call, otherwise optional. Acts as the setter.
 * @return SQL
 */
function sql(?SQL $xmbsql = null): SQL
{
    static $cache;
    
    if ($xmbsql !== null) $cache = $xmbsql;
    
    return $cache;
}

/**
 * Get the shared template service.
 *
 * @since 1.10.00
 * @param Template $template Required on first call, otherwise optional. Acts as the setter.
 * @return Template
 */
function template(?Template $template = null): Template
{
    static $cache;
    
    if ($template !== null) $cache = $template;
    
    return $cache;
}

/**
 * Get the shared theme manager.
 *
 * @since 1.10.00
 * @param ThemeMgr $theme Required on first call, otherwise optional. Acts as the setter.
 * @return ThemeMgr
 */
function theme(?ThemeMgr $theme = null): ThemeMgr
{
    static $cache;
    
    if ($theme !== null) $cache = $theme;
    
    return $cache;
}

/**
 * Get the shared variables service.
 *
 * @since 1.10.00
 * @param Variables $vars Required on first call, otherwise optional. Acts as the setter.
 * @return Variables
 */
function vars(?Variables $vars = null): Variables
{
    static $cache;
    
    if ($vars !== null) $cache = $vars;
    
    return $cache;
}
