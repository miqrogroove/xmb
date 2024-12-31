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

/* Front Matter */

if ('header.php' === basename($_SERVER['SCRIPT_NAME'])) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}
if (!defined('ROOT')) define('ROOT', './');
error_reporting(-1); // Report all errors until config.php loads successfully.


/* Global Constants and Initialized Values */

$mtime = explode(" ", microtime());

define('X_NONCE_AYS_EXP', 300); // Yes/no prompt expiration, in seconds.
define('X_NONCE_FORM_EXP', 3600); // Form expiration, in seconds.
define('X_NONCE_MAX_AGE', 86400); // CAPTCHA expiration, in seconds.
define('X_NONCE_KEY_LEN', 12); // Size of captchaimages.imagestring.
define('X_REDIRECT_HEADER', 1);
define('X_REDIRECT_JS', 2);
// permissions constants
define('X_PERMS_COUNT', 4); //Number of raw bit sets stored in postperm setting.
// indexes used in permissions arrays
define('X_PERMS_RAWPOLL', 0);
define('X_PERMS_RAWTHREAD', 1);
define('X_PERMS_RAWREPLY', 2);
define('X_PERMS_RAWVIEW', 3);
define('X_PERMS_POLL', 40);
define('X_PERMS_THREAD', 41);
define('X_PERMS_REPLY', 42);
define('X_PERMS_VIEW', 43); //View is now = Rawview || Userlist
define('X_PERMS_USERLIST', 44);
define('X_PERMS_PASSWORD', 45);


/* Load Common Files. None of them should produce any output. */

ob_start();

// Interfaces and base dependencies go first.
require ROOT.'db/DBStuff.php';
require ROOT.'include/CartesianSize.php';
require ROOT.'include/UploadResult.php';
require ROOT.'include/UploadStatus.php';
require ROOT.'include/Variables.php';

// Implementations
require ROOT.'include/admin.inc.php';
require ROOT.'include/attach.inc.php';
require ROOT.'include/BBCode.php';
require ROOT.'include/Bootup.php';
require ROOT.'include/BootupLoader.php';
require ROOT.'include/captcha.inc.php';
require ROOT.'include/debug.inc.php';
require ROOT.'include/format.php';
require ROOT.'include/Forums.php';
require ROOT.'include/functions.inc.php';
require ROOT.'include/Login.php';
require ROOT.'include/Observer.php';
require ROOT.'include/services.php';
require ROOT.'include/sessions.inc.php';
require ROOT.'include/sql.inc.php';
require ROOT.'include/Template.php';
require ROOT.'include/ThemeManager.php';
require ROOT.'include/tokens.inc.php';
require ROOT.'include/translation.inc.php';
require ROOT.'include/validate.inc.php';


/* Create base services */

vars(new \XMB\Variables());

observer(new \XMB\Observer(vars()));
template(new \XMB\Template(vars()));
translation(new \XMB\Translation(vars()));

template()->init();

$boot = new \XMB\Bootup(observer(), template(), vars());

observer()->testSuperGlobals();
observer()->assertEmptyOutputStream('the db/* and include/* files');

ob_end_clean();

vars()->onlinetime = (int) $mtime[1];
vars()->starttime = $mtime[1] + $mtime[0];
unset($mtime);


/* Load the Configuration Created by Install */

$boot->loadConfig();
$boot->setBrowser();
$boot->setIP();
$boot->setURL();
$boot->setVersion();

if (! vars()->debug) {
    error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR);
}


/* Create more services */

db($boot->connectDB());

debug(new \XMB\Debug(db()));
sql(new \XMB\SQL(db(), vars()->tablepre));

forums(new \XMB\Forums(sql()));
token(new \XMB\Token(sql(), vars()));

theme(new \XMB\Theme\Manager(forums(), sql(), template(), vars()));

bbcode(new \XMB\BBCode(theme(), vars()));

attach(new \XMB\Attach(bbcode(), db(), sql()));

core(new \XMB\Core(attach(), bbcode(), db(), debug(), forums(), sql(), template(), token(), translation(), vars()));

unset($boot);


/* Start 2nd Phase of Bootup */

$loader = new \XMB\BootupLoader(core(), db(), template(), vars());

$loader->loadSettings();
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
session(new \XMB\Session\Manager($params['mode'], $params['serror'], core(), sql()));
login(new \XMB\Login(core(), db(), session(), sql(), translation(), vars()));
login()->elevateUser($params['force_inv']);
unset($params);

if (defined('XMB_UPGRADE')) return;


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
