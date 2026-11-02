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

use const XMB\ROOT;

/**
 * Builds and configures the XMB service container.
 *
 * @since 1.12.00
 */
function create_container(): Container
{
    $container = new Container();

    $container->set(\XMB\Attach::class, 'XMB\Services\create_attach', ['attach']);
    $container->set(\XMB\BBCode::class, 'XMB\Services\create_bbcode', ['bbcode']);
    $container->set(\XMB\Bootup::class, 'XMB\Services\create_bootup', ['bootup']);
    $container->set(\XMB\BootupLoader::class, 'XMB\Services\create_loader', ['loader']);
    $container->set(\XMB\Core::class, 'XMB\Services\create_core', ['core']);
    $container->set(\XMB\DBStuff::class, 'XMB\Services\create_db', ['db']);
    $container->set(\XMB\Debug::class, 'XMB\Services\create_debug', ['debug']);
    $container->set(\XMB\Email::class, 'XMB\Services\create_email', ['email']);
    $container->set(\XMB\Features::class, 'XMB\Services\create_features', ['features']);
    $container->set(\XMB\Forums::class, 'XMB\Services\create_forums', ['forums']);
    $container->set(\XMB\Login::class, 'XMB\Services\create_login', ['login']);
    $container->set(\XMB\Observer::class, 'XMB\Services\create_observer', ['observer']);
    $container->set(\XMB\Password::class, 'XMB\Services\create_password', ['password']);
    $container->set(\XMB\Session\Manager::class, 'XMB\Services\create_session', ['session']);
    $container->set(\XMB\Settings::class, 'XMB\Services\create_settings', ['settings']);
    $container->set(\XMB\SmileAndCensor::class, 'XMB\Services\create_smile', ['smile']);
    $container->set(\XMB\SQL::class, 'XMB\Services\create_sql', ['sql']);
    $container->set(\XMB\Template::class, 'XMB\Services\create_template', ['template']);
    $container->set(\XMB\Token::class, 'XMB\Services\create_token', ['token']);
    $container->set(\XMB\Translation::class, 'XMB\Services\create_translation', ['translation']);
    $container->set(\XMB\ThemeManager::class, 'XMB\Services\create_theme', ['theme']);
    $container->set(\XMB\Validation::class, 'XMB\Services\create_validate', ['validate']);
    $container->set(\XMB\Variables::class, 'XMB\Services\create_vars', ['vars']);

    return $container;
}

/**
 * Adds a factory to the container.
 *
 * @since 1.12.00
 */
function register_installer(Container $container, SiteData $site, UpgradeOutput $outputInterface)
{
    $factory = new \XMB\InstallFactory($site, $outputInterface);
    $container->set(\XMB\Install::class, $factory, ['install']);
}

/**
 * Adds a factory to the container.
 *
 * @since 1.12.00
 */
function register_upgrader(Container $container, UpgradeOutput $outputInterface)
{
    $factory = new \XMB\UpgradeFactory($outputInterface);
    $container->set(\XMB\Upgrade::class, $factory, ['upgrade']);
}

/**
 * Builds the vars service.
 *
 * @since 1.12.00
 */
function create_vars(): \XMB\Variables
{
    return new \XMB\Variables();
}

/**
 * Builds the observer service.
 *
 * @since 1.12.00
 */
function create_observer(callable $getService): \XMB\Observer
{
    return new \XMB\Observer(
        $getService('vars'),
    );
}

/**
 * Builds the template service.
 *
 * @since 1.12.00
 */
function create_template(callable $getService): \XMB\Template
{
    return new \XMB\Template(
        $getService('vars'),
    );
}

/**
 * Builds the translation service.
 *
 * @since 1.12.00
 */
function create_translation(callable $getService): \XMB\Translation
{
    return new \XMB\Translation(
        $getService('vars'),
    );
}

/**
 * Builds the bootup service.
 *
 * @since 1.10.06
 */
function create_bootup(callable $getService): \XMB\Bootup
{
    return new \XMB\Bootup(
        $getService('template'), 
        $getService('vars'),
    );
}

/**
 * Builds the db service.
 *
 * Requires the config vars to be loaded already.
 *
 * @since 1.12.00
 */
function create_db(callable $getService): \XMB\DBStuff
{
    $vars = $getService('vars');

    return \XMB\DBFactory::createFromFilename(
        $vars->database,
        $vars->debug,
        $vars->log_mysql_errors,
    );
}

/**
 * Builds the debug service.
 *
 * @since 1.12.00
 */
function create_debug(callable $getService): \XMB\Debug
{
    return new \XMB\Debug(
        $getService('db'),
        $getService('vars'),
    );
}

/**
 * Builds the sql service.
 *
 * @since 1.12.00
 */
function create_sql(callable $getService): \XMB\SQL
{
    return new \XMB\SQL(
        $getService('db'),
        $getService('vars')->tablepre,
    );
}

/**
 * Builds the validate service.
 *
 * @since 1.12.00
 */
function create_validate(callable $getService): \XMB\Validation
{
    return new \XMB\Validation(
        $getService('db'),
    );
}

/**
 * Builds the forums service.
 *
 * @since 1.12.00
 */
function create_forums(callable $getService): \XMB\Forums
{
    return new \XMB\Forums(
        $getService('sql'),
    );
}

/**
 * Builds the settings service.
 *
 * @since 1.12.00
 */
function create_settings(callable $getService): \XMB\Settings
{
    return new \XMB\Settings(
        $getService('db'),
        $getService('sql'),
        $getService('vars'),
    );
}

/**
 * Builds the smile service.
 *
 * @since 1.12.00
 */
function create_smile(callable $getService): \XMB\SmileAndCensor
{
    return new \XMB\SmileAndCensor(
        $getService('sql'),
    );
}

/**
 * Builds the token service.
 *
 * @since 1.12.00
 */
function create_token(callable $getService): \XMB\Token
{
    return new \XMB\Token(
        $getService('sql'),
        $getService('vars'),
    );
}

/**
 * Builds the email service.
 *
 * @since 1.12.00
 */
function create_email(callable $getService): \XMB\Email
{
    return new \XMB\Email(
        $getService('vars'),
    );
}

/**
 * Builds the features service.
 *
 * @since 1.12.00
 */
function create_features(callable $getService): \XMB\Features
{
    return new \XMB\Features(
        $getService('settings'),
    );
}

/**
 * Builds the theme service.
 *
 * @since 1.12.00
 */
function create_theme(callable $getService): \XMB\ThemeManager
{
    return new \XMB\ThemeManager(
        $getService('forums'),
        $getService('settings'),
        $getService('sql'),
        $getService('template'),
        $getService('vars'),
    );
}

/**
 * Builds the bbcode service.
 *
 * @since 1.12.00
 */
function create_bbcode(callable $getService): \XMB\BBCode
{
    return new \XMB\BBCode(
        $getService('theme'),
        $getService('vars'),
    );
}

/**
 * Builds the password service.
 *
 * @since 1.12.00
 */
function create_password(callable $getService): \XMB\Password
{
    return new \XMB\Password(
        $getService('features'),
        $getService('sql')
    );
}

/**
 * Builds the attach service.
 *
 * @since 1.12.00
 */
function create_attach(callable $getService): \XMB\Attach
{
    return new \XMB\Attach(
        $getService('bbcode'),
        $getService('db'),
        $getService('sql'),
        $getService('vars'),
    );
}

/**
 * Builds the core service.
 *
 * @since 1.10.06
 */
function create_core(callable $getService): \XMB\Core
{
    return new \XMB\Core(
        $getService('attach'),
        $getService('bbcode'),
        $getService('db'),
        $getService('debug'),
        $getService('email'),
        $getService('forums'),
        $getService('password'),
        $getService('settings'),
        $getService('smile'),
        $getService('sql'),
        $getService('template'),
        $getService('token'),
        $getService('translation'),
        $getService('vars'),
    );
}

/**
 * Builds the loader service.
 *
 * @since 1.10.06
 */
function create_loader(callable $getService): \XMB\BootupLoader
{
    return new \XMB\BootupLoader(
        $getService('core'),
        $getService('db'),
        $getService('features'),
        $getService('template'),
        $getService('vars'),
    );
}

/**
 * Builds the session service.
 *
 * @since 1.10.07
 */
function create_session(callable $getService): \XMB\Session\Manager
{
    $session = new \XMB\Session\Manager(
        $getService('core'),
    );

    $logic = new \XMB\Session\FormsAndCookies(
        $getService('core'),
        $getService('features'),
        $getService('password'),
        $getService('sql'),
        $getService('token'),
        $getService('validate'),
    );

    $session->addMechanism($logic, priority: 10);

    return $session;
}

/**
 * Builds the login service.
 *
 * @since 1.10.06
 */
function create_login(callable $getService): \XMB\Login
{
    return new \XMB\Login(
        $getService('core'),
        $getService('db'),
        $getService('features'),
        $getService('session'),
        $getService('sql'),
        $getService('template'),
        $getService('translation'),
        $getService('vars'),
    );
}

/**
 * Builds the schema service.
 *
 * @since 1.12.00
 */
function create_schema(callable $getService): \XMB\Schema
{
    return new \XMB\Schema(
        $getService('db'),
        $getService('vars'),
    );
}
