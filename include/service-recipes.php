<?php

/**
 * eXtreme Message Board
 * XMB 1.10
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2026, The XMB Group
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
    BootupLoader,
    DBStuff,
    Install,
    SiteData,
    Upgrade,
    UpgradeOutput,
};

use const XMB\ROOT;

/**
 * Builds the Bootup service and base dependencies.
 *
 * @since 1.10.06
 */
function create_bootup()
{
    vars(new \XMB\Variables());

    observer(new \XMB\Observer(vars()));
    template(new \XMB\Template(vars()));
    translation(new \XMB\Translation(vars()));

    bootup(new \XMB\Bootup(template(), vars()));
}

/**
 * Builds the Core service and dependencies based on an existing DBStuff connection.
 *
 * @since 1.10.06
 */
function create_core(DBStuff $db)
{
    db($db);

    debug(new \XMB\Debug(db()));
    sql(new \XMB\SQL(db(), vars()->tablepre));
    validate(new \XMB\Validation(db()));

    forums(new \XMB\Forums(sql()));
    settings(new \XMB\Settings(db(), sql(), vars()));
    smile(new \XMB\SmileAndCensor(sql()));
    token(new \XMB\Token(sql(), vars()));

    email(new \XMB\Email(vars())); // Depends on settings and will likely use it in the future.
    features(new \XMB\Features(settings()));
    theme(new \XMB\ThemeManager(forums(), settings(), sql(), template(), vars()));

    bbcode(new \XMB\BBCode(theme(), vars()));
    password(new \XMB\Password(features(), sql()));

    attach(new \XMB\Attach(bbcode(), db(), sql(), vars()));

    core(new \XMB\Core(attach(), bbcode(), db(), debug(), email(), forums(), password(), settings(), smile(), sql(), template(), token(), translation(), vars()));
}

/**
 * Create an Install service and dependencies based on the existing DBStuff and Variables.
 *
 * @since 1.10.00 Formerly XMB\installer_factory()
 * @since 1.10.06
 */
function create_installer(SiteData $site, UpgradeOutput $show): Install
{
    require_once ROOT . 'install/cinst.php';

    $schema = new \XMB\Schema(db(), vars()->tablepre);

    sql(new \XMB\SQL(db(), vars()->tablepre));

    settings(new \XMB\Settings(db(), sql(), vars(), installMode: true));

    features(new \XMB\Features(settings()));

    password(new \XMB\Password(features(), sql()));

    return new Install(db(), password(), $schema, $site, sql(), $show, vars());
}

/**
 * Create an Upgrade service and dependencies based on the existing Core service.
 *
 * @since 1.10.06
 */
function create_upgrader(UpgradeOutput $show): Upgrade
{
    require_once ROOT . 'install/upgrade.lib.php';

    $schema = new \XMB\Schema(db(), vars()->tablepre);

    return new Upgrade(db(), $show, $schema, vars());
}

/**
 * Builds the BootupLoader service based on the existing Core service.
 *
 * @since 1.10.06
 */
function create_loader(): BootupLoader
{
    return new BootupLoader(core(), db(), features(), template(), vars());
}

/**
 * Builds the Login service and dependencies based on the existing Core service.
 *
 * @since 1.10.06
 */
function create_login(string $sessionMode, string $sessionError)
{
    session(new \XMB\Session\Manager($sessionMode, $sessionError, core(), features(), password(), sql(), token(), validate()));

    login(new \XMB\Login(core(), db(), features(), session(), sql(), template(), translation(), vars()));
}
