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

use XMB\Services\Container;

/**
 * Responsible for the XMB container's default service factories.
 *
 * The namespace is set to XMB because the recipes mostly reference classes in that namespace.
 *
 * @since 1.12.00
 */
class ServiceRecipes
{
    /**
     * Builds and configures the XMB service container.
     *
     * @param bool $installMode In case XMB installation is in progress, some services may need to be informed.
     */
    public function create_container(bool $installMode): Container
    {
        $container = new Container();

        $container->set(admin::class, $this->create_admin(...), ['admin']);
        $container->set(Attach::class, $this->create_attach(...), ['attach']);
        $container->set(BBCode::class, $this->create_bbcode(...), ['bbcode']);
        $container->set(Bootup::class, $this->create_bootup(...), ['bootup']);
        $container->set(BootupLoader::class, $this->create_loader(...), ['loader']);
        $container->set(Cache::class, $this->create_cache(...), ['cache']);
        $container->set(Core::class, $this->create_core(...), ['core']);
        $container->set(DBStuff::class, $this->create_db(...), ['db']);
        $container->set(Debug::class, $this->create_debug(...), ['debug']);
        $container->set(Email::class, $this->create_email(...), ['email']);
        $container->set(Features::class, $this->create_features(...), ['features']);
        $container->set(Forums::class, $this->create_forums(...), ['forums']);
        $container->set(Login::class, $this->create_login(...), ['login']);
        $container->set(Observer::class, $this->create_observer(...), ['observer']);
        $container->set(Password::class, $this->create_password(...), ['password']);
        $container->set(Ranks::class, $this->create_ranks(...), ['ranks']);
        $container->set(Schema::class, $this->create_schema(...), ['schema']);
        $container->set(Session\Manager::class, $this->create_session(...), ['session']);
        $container->set(SmileAndCensor::class, $this->create_smile(...), ['smile']);
        $container->set(SQL::class, $this->create_sql(...), ['sql']);
        $container->set(Template::class, $this->create_template(...), ['template']);
        $container->set(Token::class, $this->create_token(...), ['token']);
        $container->set(Translation::class, $this->create_translation(...), ['translation']);
        $container->set(ThemeManager::class, $this->create_theme(...), ['theme']);
        $container->set(ThreadRender::class, $this->create_thread_render(...), ['threadRender']);
        $container->set(Validation::class, $this->create_validate(...), ['validate']);
        $container->set(Variables::class, $this->create_vars(...), ['vars']);

        $factory = new SettingsFactory($installMode);
        $container->set(Settings::class, $factory, ['settings']);

        return $container;
    }

    /**
     * Adds a factory to the container.
     */
    public function register_installer(Container $container, SiteData $site, UpgradeOutput $outputInterface)
    {
        $factory = new InstallFactory($site, $outputInterface);
        $container->set(Install::class, $factory, ['install']);
    }

    /**
     * Adds a factory to the container.
     */
    public function register_upgrader(Container $container, UpgradeOutput $outputInterface)
    {
        $factory = new UpgradeFactory($outputInterface);
        $container->set(Upgrade::class, $factory, ['upgrade']);
    }

    /**
     * Builds the vars service.
     */
    public function create_vars(): Variables
    {
        return new Variables();
    }

    /**
     * Builds the observer service.
     */
    public function create_observer(callable $getService): Observer
    {
        return new Observer(
            $getService('vars'),
        );
    }

    /**
     * Builds the template service.
     */
    public function create_template(callable $getService): Template
    {
        return new Template(
            $getService('vars'),
        );
    }

    /**
     * Builds the translation service.
     */
    public function create_translation(callable $getService): Translation
    {
        return new Translation(
            $getService('vars'),
        );
    }

    /**
     * Builds the bootup service.
     *
     * @since 1.10.06
     */
    public function create_bootup(callable $getService): Bootup
    {
        return new Bootup(
            $getService('template'), 
            $getService('vars'),
        );
    }

    /**
     * Builds the db service.
     *
     * Requires the config vars to be loaded already.
     */
    public function create_db(callable $getService): DBStuff
    {
        $vars = $getService('vars');

        return DBFactory::createFromFilename(
            $vars->database,
            $vars->debug,
            $vars->log_mysql_errors,
        );
    }

    /**
     * Builds the debug service.
     */
    public function create_debug(callable $getService): Debug
    {
        return new Debug(
            $getService('db'),
            $getService('vars'),
        );
    }

    /**
     * Builds the sql service.
     */
    public function create_sql(callable $getService): SQL
    {
        return new SQL(
            $getService('db'),
            $getService('vars')->tablepre,
        );
    }

    /**
     * Builds the validate service.
     */
    public function create_validate(callable $getService): Validation
    {
        return new Validation(
            $getService('db'),
        );
    }

    /**
     * Builds the forums service.
     */
    public function create_forums(callable $getService): Forums
    {
        return new Forums(
            $getService('cache'),
        );
    }

    /**
     * Builds the smile service.
     */
    public function create_smile(callable $getService): SmileAndCensor
    {
        return new SmileAndCensor(
            $getService('cache'),
        );
    }

    /**
     * Builds the token service.
     */
    public function create_token(callable $getService): Token
    {
        return new Token(
            $getService('sql'),
            $getService('vars'),
        );
    }

    /**
     * Builds the email service.
     */
    public function create_email(callable $getService): Email
    {
        return new Email(
            $getService('vars'),
        );
    }

    /**
     * Builds the features service.
     */
    public function create_features(callable $getService): Features
    {
        return new Features(
            $getService('settings'),
        );
    }

    /**
     * Builds the theme service.
     */
    public function create_theme(callable $getService): ThemeManager
    {
        return new ThemeManager(
            $getService('cache'),
            $getService('forums'),
            $getService('settings'),
            $getService('sql'),
            $getService('template'),
            $getService('vars'),
        );
    }

    /**
     * Builds the bbcode service.
     */
    public function create_bbcode(callable $getService): BBCode
    {
        return new BBCode(
            $getService('theme'),
            $getService('vars'),
        );
    }

    /**
     * Builds the password service.
     */
    public function create_password(callable $getService): Password
    {
        return new Password(
            $getService('core'),
            $getService('features'),
            $getService('sql'),
            $getService('vars'),
        );
    }

    /**
     * Builds the attach service.
     */
    public function create_attach(callable $getService): Attach
    {
        return new Attach(
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
    public function create_core(callable $getService): Core
    {
        return new Core(
            $getService('attach'),
            $getService('bbcode'),
            $getService('db'),
            $getService('debug'),
            $getService('email'),
            $getService('forums'),
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
    public function create_loader(callable $getService): BootupLoader
    {
        return new BootupLoader(
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
    public function create_session(callable $getService): Session\Manager
    {
        $session = new Session\Manager(
            $getService('core'),
        );

        $logic = new Session\FormsAndCookies(
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
    public function create_login(callable $getService): Login
    {
        return new Login(
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
     */
    public function create_schema(callable $getService): Schema
    {
        return new Schema(
            $getService('db'),
            $getService('vars')->tablepre,
        );
    }

    /**
     * Builds the admin service.
     */
    public function create_admin(callable $getService): admin
    {
        return new admin(
            $getService('core'),
            $getService('db'),
            $getService('forums'),
            $getService('session'),
            $getService('settings'),
            $getService('sql'),
            $getService('validate'),
            $getService('vars'),
        );
    }

    /**
     * Builds the cache service.
     */
    public function create_cache(callable $getService): Cache
    {
        return new Cache(
            $getService('sql'),
        );
    }

    /**
     * Builds the ranks service.
     */
    public function create_ranks(callable $getService): Ranks
    {
        return new Ranks(
            $getService('cache'),
            $getService('vars'),
        );
    }

    /**
     * Builds the threadRender service.
     */
    public function create_thread_render(callable $getService): ThreadRender
    {
        return new ThreadRender(
            $getService('core'),
            $getService('ranks'),
            $getService('sql'),
            $getService('vars'),
        );
    }
}
