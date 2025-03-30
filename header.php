<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
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

/* Front Matter */

if (count(get_included_files()) === 1) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}
error_reporting(-1); // Report all errors until config.php loads successfully.
$mtime = explode(" ", microtime());
if (! defined('XMB_ROOT')) define('XMB_ROOT', './');


/* Load Common Files. None of them should produce any output. */

ob_start();

// Interfaces and base dependencies go first.
require_once XMB_ROOT . 'db/DBStuff.php';
require XMB_ROOT . 'include/CartesianSize.php';
require XMB_ROOT . 'include/UploadResult.php';
require XMB_ROOT . 'include/UploadStatus.php';
require XMB_ROOT . 'include/Variables.php';
require_once XMB_ROOT . 'include/version.php';

// Implementations
require XMB_ROOT . 'include/admin.inc.php';
require XMB_ROOT . 'include/attach.inc.php';
require XMB_ROOT . 'include/BBCode.php';
require XMB_ROOT . 'include/Bootup.php';
require XMB_ROOT . 'include/BootupLoader.php';
require XMB_ROOT . 'include/buddy.inc.php';
require XMB_ROOT . 'include/captcha.inc.php';
require XMB_ROOT . 'include/debug.inc.php';
require XMB_ROOT . 'include/format.php';
require XMB_ROOT . 'include/Forums.php';
require XMB_ROOT . 'include/functions.inc.php';
require XMB_ROOT . 'include/Login.php';
require XMB_ROOT . 'include/Observer.php';
require XMB_ROOT . 'include/Password.php';
require XMB_ROOT . 'include/Ranks.php';
require XMB_ROOT . 'include/schema.inc.php';
require XMB_ROOT . 'include/services.php';
require XMB_ROOT . 'include/sessions.inc.php';
require XMB_ROOT . 'include/SmileAndCensor.php';
require XMB_ROOT . 'include/sql.inc.php';
require XMB_ROOT . 'include/SettingsLoader.php';
require XMB_ROOT . 'include/Template.php';
require XMB_ROOT . 'include/ThreadRender.php';
require XMB_ROOT . 'include/ThemeManager.php';
require XMB_ROOT . 'include/tokens.inc.php';
require XMB_ROOT . 'include/translation.inc.php';
require XMB_ROOT . 'include/UserEditForm.php';
require XMB_ROOT . 'include/validate.inc.php';


/* Create base services */

vars(new \XMB\Variables());

observer(new \XMB\Observer(vars()));
template(new \XMB\Template(vars()));
translation(new \XMB\Translation(vars()));

template()->init();

$boot = new \XMB\Bootup(template(), vars());

observer()->testSuperGlobals();
observer()->assertEmptyOutputStream('the db/* and include/* files', use_debug: false);

ob_end_clean();

vars()->onlinetime = (int) $mtime[1];
vars()->starttime = $mtime[1] + $mtime[0];
unset($mtime);


/* Load the Configuration Created by Install */

if (defined('XMB_INSTALL') && ! defined('XMB_INSTALL_P2')) {
    vars()->show_full_info = true;
    $boot->setVersion();
    unset($boot);
    return;
}

$boot->loadConfig();
observer()->assertEmptyOutputStream('config.php');
if (! vars()->debug) {
    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR);
}
$boot->setBrowser();
$boot->setIP();
$boot->setURL();
$boot->setVersion();
observer()->assertEmptyOutputStream('version.php');


/* Create more services */

db($boot->connectDB());

unset($boot);

(new \XMB\SettingsLoader(db(), vars()))->readToVars();

debug(new \XMB\Debug(db()));

sql(new \XMB\SQL(db(), vars()->tablepre));

forums(new \XMB\Forums(sql()));
smile(new \XMB\SmileAndCensor(sql()));
token(new \XMB\Token(sql(), vars()));

theme(new \XMB\Theme\Manager(forums(), sql(), template(), vars()));

bbcode(new \XMB\BBCode(theme(), vars()));

attach(new \XMB\Attach(bbcode(), db(), sql(), vars()));

core(new \XMB\Core(attach(), bbcode(), db(), debug(), forums(), smile(), sql(), template(), token(), translation(), vars()));


/* Start 2nd Phase of Bootup */

$loader = new \XMB\BootupLoader(core(), db(), template(), vars());

$loader->setHeaders();

if (defined('XMB_UPGRADE') && (int) vars()->settings['schema_version'] < 5) {
    $xmbuser = core()->postedVar(
        varname: 'xmbuser',
        dbescape: false,
        sourcearray: 'c',
    );
    $xmbpw = getPhpInput('xmbpw', 'c');
    define('X_SADMIN', sql()->checkUpgradeOldLogin($xmbuser, $xmbpw));
    unset($loader, $xmbuser, $xmbpw);
    return;
}

/* Authorize User, Set Up Session, and Load Language Translation */

$params = $loader->prepareSession();
session(new \XMB\Session\Manager($params['mode'], $params['serror'], core(), sql(), token()));
login(new \XMB\Login(core(), db(), session(), sql(), template(), translation(), vars()));
login()->elevateUser($params['force_inv']);
unset($params);

if (defined('XMB_UPGRADE')) {
    return;
} elseif (X_SADMIN && (int) vars()->settings['schema_version'] < \XMB\Schema::VER) {
    $core->redirect(vars()->full_url . 'install/', timeout: 0);
}


/* Set Up HTML Templates and Themes */

$loader->setCharset();
$loader->setBaseElement();
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
