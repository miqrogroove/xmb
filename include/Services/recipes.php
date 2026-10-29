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

namespace XMB\Services;

use XMB\{
    Bootup,
    BootupLoader,
    DBStuff,
    Install,
    Login,
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
function create_bootup(): Bootup
{
    $services = container();

    $services->set('vars', $vars = new \XMB\Variables());

    $services->set('observer', new \XMB\Observer($vars));
    $services->set('template', $template = new \XMB\Template($vars));
    $services->set('translation', new \XMB\Translation($vars));

    $services->set('bootup', $bootup = new Bootup($template, $vars));

    return $bootup;
}

/**
 * Builds the Core service and dependencies based on an existing DBStuff connection.
 *
 * @since 1.10.06
 */
function create_core(DBStuff $db)
{
    $services = container();
    $template = $services->get('template');
    $translation = $services->get('translation');
    $vars = $services->get('vars');

    $services->set('db', $db);

    $services->set('debug', $debug = new \XMB\Debug($db, $vars));
    $services->set('sql', $sql = new \XMB\SQL($db, $vars->tablepre));
    $services->set('validate', new \XMB\Validation($db));

    $services->set('forums', $forums = new \XMB\Forums($sql));
    $services->set('settings', $settings = new \XMB\Settings($db, $sql, $vars));
    $services->set('smile', $smile = new \XMB\SmileAndCensor($sql));
    $services->set('token', $token = new \XMB\Token($sql, $vars));

    $services->set('email', $email = new \XMB\Email($vars)); // Depends on settings and will likely use it in the future.
    $services->set('features', $features = new \XMB\Features($settings));
    $services->set('theme', $theme = new \XMB\ThemeManager($forums, $settings, $sql, $template, $vars));

    $services->set('bbcode', $bbcode = new \XMB\BBCode($theme, $vars));
    $services->set('password', $password = new \XMB\Password($features, $sql));

    $services->set('attach', $attach = new \XMB\Attach($bbcode, $db, $sql, $vars));

    $services->set('core', new \XMB\Core($attach, $bbcode, $db, $debug, $email, $forums, $password, $settings, $smile, $sql, $template, $token, $translation, $vars));
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

    $services = container();
    $db = $services->get('db');
    $vars = $services->get('vars');

    $services->set('schema', $schema = new \XMB\Schema($db, $vars->tablepre));
    $services->set('sql', $sql = new \XMB\SQL($db, $vars->tablepre));

    $services->set('settings', $settings = new \XMB\Settings($db, $sql, $vars, installMode: true));

    $services->set('features', $features = new \XMB\Features($settings));

    $services->set('password', $password = new \XMB\Password($features, $sql));

    return new Install($db, $password, $schema, $site, $sql, $show, $vars);
}

/**
 * Create an Upgrade service and dependencies based on the existing Core service.
 *
 * @since 1.10.06
 */
function create_upgrader(UpgradeOutput $show): Upgrade
{
    require_once ROOT . 'install/upgrade.lib.php';

    $services = container();
    $db = $services->get('db');
    $vars = $services->get('vars');

    $services->set('schema', $schema = new \XMB\Schema($db, $vars->tablepre));

    return new Upgrade($db, $show, $schema, $vars);
}

/**
 * Builds the BootupLoader service based on the existing Core service.
 *
 * @since 1.10.06
 */
function create_loader(): BootupLoader
{
    $services = container();

    return new BootupLoader(
        $services->get('core'),
        $services->get('db'),
        $services->get('features'),
        $services->get('template'),
        $services->get('vars'),
    );
}

/**
 * Builds the Session service and dependencies based on the existing Core service.
 *
 * @since 1.10.07
 */
function create_session(string $sessionError)
{
    $services = container();
    $core = $services->get('core');

    $services->set('session', $session = new \XMB\Session\Manager($sessionError, $core));

    $logic = new \XMB\Session\FormsAndCookies(
        $core,
        $services->get('features'),
        $services->get('password'),
        $services->get('sql'),
        $services->get('token'),
        $services->get('validate'),
    );

    $session->addMechanism($logic, priority: 10);
}

/**
 * Builds the Login service and dependencies based on the existing Core service.
 *
 * @since 1.10.06
 */
function create_login(string $sessionMode): Login
{
    $services = container();
    $session = $services->get('session');

    $session->readAndUpdateClient($sessionMode);

    $login = new Login(
        $services->get('core'),
        $services->get('db'),
        $services->get('features'),
        $session,
        $services->get('sql'),
        $services->get('template'),
        $services->get('translation'),
        $services->get('vars'),
    );

    $services->set('login', $login);

    return $login;
}
