<?php

/**
 * eXtreme Message Board
 * XMB 1.10
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

use UnexpectedValueException;

use const XMB\ROOT;
use const XMB\X_ADMIN;
use const XMB\X_MEMBER;
use const XMB\X_SADMIN;

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

// Classes
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
require ROOT . 'include/format.php';
require ROOT . 'include/Forums.php';
require ROOT . 'include/functions.inc.php';
require ROOT . 'include/Login.php';
require ROOT . 'include/Observer.php';
require ROOT . 'include/online.inc.php';
require ROOT . 'include/Password.php';
require ROOT . 'include/Ranks.php';
require ROOT . 'include/schema.inc.php';
require ROOT . 'include/services.php';
require ROOT . 'include/Session/Data.php';
require ROOT . 'include/Session/FormsAndCookies.php';
require ROOT . 'include/Session/Manager.php';
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
require ROOT . 'vendor/autoload.php';


/* Create base services */

vars(new \XMB\Variables());

observer(new \XMB\Observer(vars()));
template(new \XMB\Template(vars()));
translation(new \XMB\Translation(vars()));

template()->init();

$boot = new \XMB\Bootup(template(), vars());

observer()->testSuperGlobals();
observer()->assertEmptyOutputStream('the include/* files', use_debug: false);

ob_end_clean();

vars()->onlinetime = (int) $mtime[1];
vars()->starttime = $mtime[1] + $mtime[0];
unset($mtime);


/* Load the Configuration Created by Install */

if (defined('XMB\INSTALL') && ! defined('XMB\INSTALL_P2')) {
    vars()->show_full_info = true;
    $boot->setVersion();
    translation()->langPanic();
    template()->addRefs();
    unset($boot);
    return;
}

$boot->loadConfig();
observer()->assertEmptyOutputStream('config.php');
if (vars()->debug) {
    if (error_reporting() !== -1) {
        echo 'XMB was unable to set the error_reporting level. Your host might be using a proprietary PHP extension to block error reporting. Please disable the XMB debug mode and contact your hostmaster for more information.';
        throw new UnexpectedValueException('The PHP error_reporting() function failed to return the correct value after XMB set it to -1');
    }
} else {
    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR);
}
if (! defined('XMB\UPGRADE_CLI')) {
    $boot->setBrowser();
    $boot->setIP();
    $boot->setURL();
}
$boot->setVersion();
observer()->assertEmptyOutputStream('version.php');


/* Create more services */

db($boot->connectDB());

unset($boot);

debug(new \XMB\Debug(db()));
sql(new \XMB\SQL(db(), vars()->tablepre));
validate(new \XMB\Validation(db()));

forums(new \XMB\Forums(sql()));
password(new \XMB\Password(sql()));
settings(new \XMB\Settings(db(), sql(), vars()));
smile(new \XMB\SmileAndCensor(sql()));
token(new \XMB\Token(sql(), vars()));

email(new \XMB\Email(vars())); // Depends on settings and will likely use it in the future.
theme(new \XMB\ThemeManager(forums(), settings(), sql(), template(), vars()));

bbcode(new \XMB\BBCode(theme(), vars()));

attach(new \XMB\Attach(bbcode(), db(), sql(), vars()));

core(new \XMB\Core(attach(), bbcode(), db(), debug(), email(), forums(), password(), smile(), sql(), template(), token(), translation(), vars()));


/* Start 2nd Phase of Bootup */

$loader = new \XMB\BootupLoader(core(), db(), template(), vars());

$loader->setHeaders();

if (! core()->schemaHasSessions()) {
    if (defined('XMB\UPGRADE')) {
        $xmbuser = validate()->postedVar(
            varname: 'xmbuser',
            dbescape: false,
            sourcearray: 'c',
        );
        $xmbpw = getPhpInput('xmbpw', 'c');
        define('XMB\X_SADMIN', sql()->checkUpgradeOldLogin($xmbuser, $xmbpw));
        unset($loader, $xmbuser, $xmbpw);
    } else {
        core()->unavailable('upgrade');
    }
    return;
}

/* Authorize User, Set Up Session, and Load Language Translation */

$params = $loader->prepareSession();
session(new \XMB\Session\Manager($params['mode'], $params['serror'], core(), password(), sql(), token(), validate()));
login(new \XMB\Login(core(), db(), session(), sql(), template(), translation(), vars()));
login()->elevateUser($params['force_inv']);
unset($params);

if (defined('XMB\UPGRADE')) {
    return;
} elseif ((int) vars()->settings['schema_version'] < \XMB\Schema::VER) {
    if (X_SADMIN) {
        core()->redirect(vars()->full_url . 'install/', timeout: 0);
    } else {
        core()->unavailable('upgrade');
    }
}


/* Set Up HTML Templates and Themes */

$loader->setCharset();
$loader->setVisit();
theme()->setTheme();

/* Theme Ready.  Make pretty errors. */

login()->sendErrors();


/* Finish HTML Templates */

if ((X_ADMIN || vars()->settings['bbstatus'] == 'on') && (X_MEMBER || vars()->settings['regviewonly'] == 'off')) {
    $loader->createNavbarLinks();
    $loader->makePlugLinks();
    $loader->makeQuickJump();
    if (X_MEMBER) $loader->checkU2U(sql());
}


/* Perform HTTP Connection Maintenance */

$loader->startCompression();


/* Extra Security */

$loader->adminFirewall();

unset($loader);

observer()->assertEmptyOutputStream('header.php');
