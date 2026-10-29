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

use UnexpectedValueException;

use const XMB\ROOT;
use const XMB\X_ADMIN;
use const XMB\X_MEMBER;
use const XMB\X_SADMIN;

use function XMB\getPhpInput;

/* Front Matter */

if (count(get_included_files()) === 1) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}
error_reporting(-1); // Report all errors until config.php loads successfully.
$mtime = explode(" ", microtime());
if (! defined('XMB\ROOT')) define('XMB\ROOT', './');


/* Load Common Files. None of them should produce any output. */

ob_start();

// Interfaces
require_once ROOT . 'db/DBStuff.php';
require ROOT . 'include/Session/Mechanism.php';
require ROOT . 'vendor/autoload.php';

// Classes
require_once ROOT . 'db/DBFactory.php';
require ROOT . 'include/admin.inc.php';
require ROOT . 'include/attach.inc.php';
require ROOT . 'include/BBCode.php';
require ROOT . 'include/Bootup.php';
require ROOT . 'include/BootupLoader.php';
require ROOT . 'include/buddy.inc.php';
require ROOT . 'include/captcha.inc.php';
require ROOT . 'include/CartesianSize.php';
require ROOT . 'include/debug.inc.php';
require ROOT . 'include/Email.php';
require ROOT . 'include/Enum/IndexListHeaderLocation.php';
require ROOT . 'include/Features.php';
require ROOT . 'include/format.php';
require ROOT . 'include/Forums.php';
require ROOT . 'include/functions.inc.php';
require ROOT . 'include/Login.php';
require ROOT . 'include/Observer.php';
require ROOT . 'include/online.inc.php';
require ROOT . 'include/Password.php';
require ROOT . 'include/Ranks.php';
require ROOT . 'include/schema.inc.php';
require ROOT . 'include/Services/Container.php';
require ROOT . 'include/Services/container-cache.php';
require ROOT . 'include/Services/NotFoundException.php';
require ROOT . 'include/Services/recipes.php';
require ROOT . 'include/Session/Data.php';
require ROOT . 'include/Session/FormsAndCookies.php';
require ROOT . 'include/Session/Manager.php';
require ROOT . 'include/Session/Registry.php';
require ROOT . 'include/Settings.php';
require ROOT . 'include/SmileAndCensor.php';
require ROOT . 'include/sql.inc.php';
require ROOT . 'include/Template.php';
require ROOT . 'include/ThreadRender.php';
require ROOT . 'include/ThemeManager.php';
require ROOT . 'include/tokens.inc.php';
require ROOT . 'include/translation.inc.php';
require ROOT . 'include/u2u.inc.php';
require ROOT . 'include/UploadResult.php';
require ROOT . 'include/UploadStatus.php';
require ROOT . 'include/UserEditForm.php';
require ROOT . 'include/validate.inc.php';
require ROOT . 'include/validate-email.inc.php';
require ROOT . 'include/Validation.php';
require ROOT . 'include/Variables.php';
require_once ROOT . 'include/version.php';


/* Create base services */

container($services = new Container);

$bootup = create_bootup();

$services->get('template')->init();
$services->get('observer')->testSuperGlobals();
$services->get('observer')->assertEmptyOutputStream('the include/* files', use_debug: false);

ob_end_clean();

$services->get('vars')->onlinetime = (int) $mtime[1];
$services->get('vars')->starttime = $mtime[1] + $mtime[0];
unset($mtime);


/* Load the Configuration Created by Install */

if (defined('XMB\INSTALL') && ! defined('XMB\INSTALL_P2')) {
    $services->get('vars')->show_full_info = true;
    $services->get('bootup')->setVersion();
    $services->get('translation')->langPanic();
    $services->get('template')->addRefs();
    $bootup->setCharset();
    return;
}

$services->get('bootup')->loadConfig();
$services->get('observer')->assertEmptyOutputStream('config.php');
if ($services->get('vars')->debug) {
    if (error_reporting() !== -1) {
        echo 'XMB was unable to set the error_reporting level. Your host might be using a proprietary PHP extension to block error reporting. Please disable the XMB debug mode and contact your hostmaster for more information.';
        throw new UnexpectedValueException('The PHP error_reporting() function failed to return the correct value after XMB set it to -1');
    }
} else {
    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR);
}
if (! defined('XMB\UPGRADE_CLI')) {
    $bootup->setBrowser();
    $bootup->setIP();
    $bootup->setURL();
}
$bootup->setVersion();
$services->get('observer')->assertEmptyOutputStream('version.php');


/* Create more services */

create_core($bootup->connectDB());


/* Start 2nd Phase of Bootup */

$loader = create_loader();

$loader->setHeaders();

if (! $services->get('features')->schemaHasSessions()) {
    $services->get('core')->loadLangWithoutSession();
    $bootup->setCharset();
    if (! defined('XMB\UPGRADE')) {
        $services->get('core')->unavailable('upgrade');
    }
    $xmbuser = $services->get('validate')->postedVar(
        varname: 'xmbuser',
        dbescape: false,
        sourcearray: 'c',
    );
    $xmbpw = getPhpInput('xmbpw', 'c');
    define('XMB\X_SADMIN', $services->get('sql')->checkUpgradeOldLogin($xmbuser, $xmbpw));
    unset($loader, $xmbuser, $xmbpw);
    return;
}


/* Authorize User, Set Up Session, and Load Language Translation */

$params = $loader->prepareSession();
create_session($params['serror']);
$login = create_login($params['mode']);
$login->elevateUser($params['force_inv']);
unset($params);

if (defined('XMB\UPGRADE')) {
    return;
} elseif ((int) $services->get('settings')->get('schema_version') < \XMB\Schema::VER) {
    if (X_SADMIN) {
        $services->get('core')->redirect($services->get('vars')->full_url . 'install/', timeout: 0);
    } else {
        $services->get('core')->unavailable('upgrade');
    }
}


/* Set Up HTML Templates and Themes */

$bootup->setCharset();
$login->setVisit();
$services->get('theme')->setTheme();


/* Theme Ready.  Make pretty errors. */

$login->sendErrors();


/* Finish HTML Templates */

if ((X_ADMIN || $services->get('settings')->get('bbstatus') == 'on') && (X_MEMBER || $services->get('settings')->get('regviewonly') == 'off')) {
    $loader->createNavbarLinks();
    $loader->makePlugLinks();
    $loader->makeQuickJump();
    if (X_MEMBER) $loader->checkU2U($services->get('sql'));
}


/* Perform HTTP Connection Maintenance */

$loader->startCompression();


/* Extra Security */

$loader->adminFirewall();

$services->get('observer')->assertEmptyOutputStream('header.php');

unset($bootup, $loader, $login, $services);
