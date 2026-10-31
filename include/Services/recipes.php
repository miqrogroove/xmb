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

use Psr\Container\ContainerInterface;

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
function create_observer(ContainerInterface $container): \XMB\Observer
{
    return new \XMB\Observer(
        $container->get('vars'),
    );
}

/**
 * Builds the template service.
 *
 * @since 1.12.00
 */
function create_template(ContainerInterface $container): \XMB\Template
{
    return new \XMB\Template(
        $container->get('vars'),
    );
}

/**
 * Builds the translation service.
 *
 * @since 1.12.00
 */
function create_translation(ContainerInterface $container): \XMB\Translation
{
    return new \XMB\Translation(
        $container->get('vars'),
    );
}

/**
 * Builds the bootup service.
 *
 * @since 1.10.06
 */
function create_bootup(ContainerInterface $container): \XMB\Bootup
{
    return new \XMB\Bootup(
        $container->get('template'), 
        $container->get('vars'),
    );
}

/**
 * Builds the db service.
 *
 * Requires the config vars to be loaded already.
 *
 * @since 1.12.00
 */
function create_db(ContainerInterface $container): \XMB\DBStuff
{
    $vars = $container->get('vars');

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
function create_debug(ContainerInterface $container): \XMB\Debug
{
    return new \XMB\Debug(
        $container->get('db'),
        $container->get('vars'),
    );
}

/**
 * Builds the sql service.
 *
 * @since 1.12.00
 */
function create_sql(ContainerInterface $container): \XMB\SQL
{
    return new \XMB\SQL(
        $container->get('db'),
        $container->get('vars')->tablepre,
    );
}

/**
 * Builds the validate service.
 *
 * @since 1.12.00
 */
function create_validate(ContainerInterface $container): \XMB\Validation
{
    return new \XMB\Validation(
        $container->get('db'),
    );
}

/**
 * Builds the forums service.
 *
 * @since 1.12.00
 */
function create_forums(ContainerInterface $container): \XMB\Forums
{
    return new \XMB\Forums(
        $container->get('sql'),
    );
}

/**
 * Builds the settings service.
 *
 * @since 1.12.00
 */
function create_settings(ContainerInterface $container): \XMB\Settings
{
    return new \XMB\Settings(
        $container->get('db'),
        $container->get('sql'),
        $container->get('vars'),
    );
}

/**
 * Builds the smile service.
 *
 * @since 1.12.00
 */
function create_smile(ContainerInterface $container): \XMB\SmileAndCensor
{
    return new \XMB\SmileAndCensor(
        $container->get('sql'),
    );
}

/**
 * Builds the token service.
 *
 * @since 1.12.00
 */
function create_token(ContainerInterface $container): \XMB\Token
{
    return new \XMB\Token(
        $container->get('sql'),
        $container->get('vars'),
    );
}

/**
 * Builds the email service.
 *
 * @since 1.12.00
 */
function create_email(ContainerInterface $container): \XMB\Email
{
    return new \XMB\Email(
        $container->get('vars'),
    );
}

/**
 * Builds the features service.
 *
 * @since 1.12.00
 */
function create_features(ContainerInterface $container): \XMB\Features
{
    return new \XMB\Features(
        $container->get('settings'),
    );
}

/**
 * Builds the theme service.
 *
 * @since 1.12.00
 */
function create_theme(ContainerInterface $container): \XMB\ThemeManager
{
    return new \XMB\ThemeManager(
        $container->get('forums'),
        $container->get('settings'),
        $container->get('sql'),
        $container->get('template'),
        $container->get('vars'),
    );
}

/**
 * Builds the bbcode service.
 *
 * @since 1.12.00
 */
function create_bbcode(ContainerInterface $container): \XMB\BBCode
{
    return new \XMB\BBCode(
        $container->get('theme'),
        $container->get('vars'),
    );
}

/**
 * Builds the password service.
 *
 * @since 1.12.00
 */
function create_password(ContainerInterface $container): \XMB\Password
{
    return new \XMB\Password(
        $container->get('features'),
        $container->get('sql')
    );
}

/**
 * Builds the attach service.
 *
 * @since 1.12.00
 */
function create_attach(ContainerInterface $container): \XMB\Attach
{
    return new \XMB\Attach(
        $container->get('bbcode'),
        $container->get('db'),
        $container->get('sql'),
        $container->get('vars'),
    );
}

/**
 * Builds the core service.
 *
 * @since 1.10.06
 */
function create_core(ContainerInterface $container): \XMB\Core
{
    return new \XMB\Core(
        $container->get('attach'),
        $container->get('bbcode'),
        $container->get('db'),
        $container->get('debug'),
        $container->get('email'),
        $container->get('forums'),
        $container->get('password'),
        $container->get('settings'),
        $container->get('smile'),
        $container->get('sql'),
        $container->get('template'),
        $container->get('token'),
        $container->get('translation'),
        $container->get('vars'),
    );
}

/**
 * Builds the loader service.
 *
 * @since 1.10.06
 */
function create_loader(ContainerInterface $container): \XMB\BootupLoader
{
    return new \XMB\BootupLoader(
        $container->get('core'),
        $container->get('db'),
        $container->get('features'),
        $container->get('template'),
        $container->get('vars'),
    );
}

/**
 * Builds the session service.
 *
 * @since 1.10.07
 */
function create_session(ContainerInterface $container): \XMB\Session\Manager
{
    $session = new \XMB\Session\Manager(
        $container->get('core'),
    );

    $logic = new \XMB\Session\FormsAndCookies(
        $container->get('core'),
        $container->get('features'),
        $container->get('password'),
        $container->get('sql'),
        $container->get('token'),
        $container->get('validate'),
    );

    $session->addMechanism($logic, priority: 10);

    return $session;
}

/**
 * Builds the login service.
 *
 * @since 1.10.06
 */
function create_login(ContainerInterface $container): \XMB\Login
{
    return new \XMB\Login(
        $container->get('core'),
        $container->get('db'),
        $container->get('features'),
        $container->get('session'),
        $container->get('sql'),
        $container->get('template'),
        $container->get('translation'),
        $container->get('vars'),
    );
}

/**
 * Builds the schema service.
 *
 * @since 1.12.00
 */
function create_schema(ContainerInterface $container): \XMB\Schema
{
    return new \XMB\Schema(
        $container->get('db'),
        $container->get('vars'),
    );
}
