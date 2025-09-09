<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-3
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

namespace XMB\Services;

use XMB\{
    Attach,
    BBCode,
    Core,
    Debug,
    DBStuff,
    Email,
    Forums,
    Login,
    Observer,
    Password,
    Session\Manager as SessionMgr,
    Settings,
    SmileAndCensor,
    SQL,
    Template,
    ThemeManager,
    Token,
    Translation,
    Validation,
    Variables,
};

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
 * Get the shared bbcode service.
 *
 * @since 1.10.00
 * @param BBCode $bbcode Required on first call, otherwise optional. Acts as the setter.
 * @return BBCode
 */
function bbcode(?BBCode $bbcode = null): BBCode
{
    static $cache;
    
    if ($bbcode !== null) $cache = $bbcode;
    
    return $cache;
}

/**
 * Get the shared core service.
 *
 * @since 1.10.00
 * @param Core $core Required on first call, otherwise optional. Acts as the setter.
 * @return Core
 */
function core(?Core $core = null): Core
{
    static $cache;
    
    if ($core !== null) $cache = $core;
    
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
 * Get the shared email service.
 *
 * @since 1.10.00
 * @param Email $email Required on first call, otherwise optional. Acts as the setter.
 * @return Email
 */
function email(?Email $email = null): Email
{
    static $cache;
    
    if ($email !== null) $cache = $email;
    
    return $cache;
}

/**
 * Get the shared forums service.
 *
 * @since 1.10.00
 * @param Forums $forums Required on first call, otherwise optional. Acts as the setter.
 * @return Forums
 */
function forums(?Forums $forums = null): Forums
{
    static $cache;
    
    if ($forums !== null) $cache = $forums;
    
    return $cache;
}

/**
 * Get the shared login service.
 *
 * @since 1.10.00
 * @param Login $login Required on first call, otherwise optional. Acts as the setter.
 * @return Login
 */
function login(?Login $login = null): Login
{
    static $cache;
    
    if ($login !== null) $cache = $login;
    
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
 * Get the shared password service.
 *
 * @since 1.10.00
 * @param Password $password Required on first call, otherwise optional. Acts as the setter.
 * @return Password
 */
function password(?Password $password = null): Password
{
    static $cache;
    
    if ($password !== null) $cache = $password;
    
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
 * Get the shared settings service.
 *
 * @since 1.10.00
 * @param Settings $settings Required on first call, otherwise optional. Acts as the setter.
 * @return Settings
 */
function settings(?Settings $settings = null): Settings
{
    static $cache;
    
    if ($settings !== null) $cache = $settings;
    
    return $cache;
}

/**
 * Get the shared smilie and censor service.
 *
 * @since 1.10.00
 * @param SmileAndCensor $smile Required on first call, otherwise optional. Acts as the setter.
 * @return SmileAndCensor
 */
function smile(?SmileAndCensor $smile = null): SmileAndCensor
{
    static $cache;
    
    if ($smile !== null) $cache = $smile;
    
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
 * @param ThemeManager $theme Required on first call, otherwise optional. Acts as the setter.
 * @return ThemeManager
 */
function theme(?ThemeManager $theme = null): ThemeManager
{
    static $cache;
    
    if ($theme !== null) $cache = $theme;
    
    return $cache;
}

/**
 * Get the shared token service.
 *
 * @since 1.10.00
 * @param Token $token Required on first call, otherwise optional. Acts as the setter.
 * @return Token
 */
function token(?Token $token = null): Token
{
    static $cache;
    
    if ($token !== null) $cache = $token;
    
    return $cache;
}

/**
 * Get the shared translation service.
 *
 * @since 1.10.00
 * @param Translation $translation Required on first call, otherwise optional. Acts as the setter.
 * @return Translation
 */
function translation(?Translation $translation = null): Translation
{
    static $cache;
    
    if ($translation !== null) $cache = $translation;
    
    return $cache;
}

/**
 * Get the shared validation service.
 *
 * @since 1.10.00
 * @param Validation $validate Required on first call, otherwise optional. Acts as the setter.
 * @return Validation
 */
function validate(?Validation $validate = null): Validation
{
    static $cache;
    
    if ($validate !== null) $cache = $validate;
    
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
