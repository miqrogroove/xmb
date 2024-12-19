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

use function XMB\Services\attach;
use function XMB\Services\db;
use function XMB\Services\session;
use function XMB\Services\sql;
use function XMB\Services\template;
use function XMB\Services\theme;
use function XMB\Services\vars;

/* Front Matter */

if ('header.php' === basename($_SERVER['SCRIPT_NAME'])) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}
if (!defined('ROOT')) define('ROOT', './');
error_reporting(-1); // Report all errors until config.php loads successfully.
define('IN_CODE', TRUE);
require ROOT.'include/global.inc.php';


/* Global Constants and Initialized Values */

$mtime = explode(" ", microtime());
$starttime = $mtime[1] + $mtime[0];
$onlinetime = time();
$time = $onlinetime;
$selHTML = 'selected="selected"';
$cheHTML = 'checked="checked"';
$server = substr($_SERVER['SERVER_SOFTWARE'], 0, 3);

$canonical_link = '';
$bbcodescript = '';
$cssInclude = '';
$threadSubject = '';
$filesize = 0;
$filename = '';
$filetype = '';
$full_url = '';
$navigation = '';
$onlineuser = '';
$othertid = '';
$password = '';
$smiliesnum = 0;
$status = '';
$wordsnum = 0;

define('X_CACHE_GET', 1);
define('X_CACHE_PUT', 2);
define('X_NONCE_AYS_EXP', 300); // Yes/no prompt expiration, in seconds.
define('X_NONCE_FORM_EXP', 3600); // Form expiration, in seconds.
define('X_NONCE_MAX_AGE', 86400); // CAPTCHA expiration, in seconds.
define('X_NONCE_KEY_LEN', 12); // Size of captchaimages.imagestring.
define('X_REDIRECT_HEADER', 1);
define('X_REDIRECT_JS', 2);
define('X_SHORTEN_SOFT', 1);
define('X_SHORTEN_HARD', 2);
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
// status string to bit field assignments
$status_enum = array(
'Super Administrator' => 1,
'Administrator'       => 2,
'Super Moderator'     => 4,
'Moderator'           => 8,
'Member'              => 16,
'Guest'               => 32,
''                    => 32,
'Reserved-Future-Use' => 64,
'Banned'              => (1 << 30)
); //$status['Banned'] == 2^30
// status bit to $lang key assignments
$status_translate = array(
1         => 'superadmin',
2         => 'textadmin',
4         => 'textsupermod',
8         => 'textmod',
16        => 'textmem',
32        => 'textguest1',
(1 << 30) => 'textbanned'
);

assertEmptyOutputStream('header.php or global.inc.php');


/* Load Common Files. None of them should produce any output. */

ob_start();

// Interfaces and base dependencies go first.
require ROOT.'db/DBStuff.php';
require ROOT.'include/CartesianSize.php';
require ROOT.'include/UploadResult.php';
require ROOT.'include/UploadStatus.php';
require ROOT.'include/Variables.php';

// Implementations
require ROOT.'include/attach.inc.php';
require ROOT.'include/Bootup.php';
require ROOT.'include/debug.inc.php';
require ROOT.'include/functions.inc.php';
require ROOT.'include/services.php';
require ROOT.'include/sessions.inc.php';
require ROOT.'include/sql.inc.php';
require ROOT.'include/Template.php';
require ROOT.'include/ThemeManager.php';
require ROOT.'include/tokens.inc.php';
require ROOT.'include/validate.inc.php';

assertEmptyOutputStream('the db/* and include/* files');
ob_end_clean();

/* Create base services */

vars(new \XMB\Variables());
template(new \XMB\Template(vars()));
$boot = new \XMB\Bootup(template(), vars());

/* Load the Configuration Created by Install */

require ROOT.'config.php';
assertEmptyOutputStream('config.php');

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

debug(new \XMB\Debug(db());
sql(new \XMB\SQL(db(), vars()->tablepre));

attach(new \XMB\Attach(sql()));
theme(new \XMB\Theme\Manager(sql(), template(), vars()));

$boot->loadSettings();
$boot->setHeaders();

if (defined('XMB_UPGRADE') && (int) vars()->settings['schema_version'] < 5) {
    $xmbuser = postedVar(
        varname: 'xmbuser',
        dbescape: false,
        sourcearray: 'c',
    );
    $xmbpw = postedVar(
        varname: 'xmbpw',
        htmlencode: false,
        dbescape: false,
        sourcearray: 'c',
    );
    define('X_SADMIN', sql()->checkUpgradeOldLogin($xmbuser, $xmbpw));
    unset($xmbuser, $xmbpw);
    return;
}

/* Authorize User, Set Up Session, and Load Language Translation */

$boot->createSession();

if (defined('XMB_UPGRADE')) return;


/* Set Up HTML Templates and Themes */

$boot->setCharset();
$boot->setBaseElement();
$boot->setVisit();
theme()->setTheme();

/* Theme Ready.  Make pretty errors. */

$boot->sendErrors();


/* Finish HTML Templates */
if ((X_ADMIN || vars()->settings['bbstatus'] == 'on') && (X_MEMBER || vars()->settings['regviewonly'] == 'off')) {

    $boot->createNavbarLinks();
    $boot->makePlugLinks();
    $boot->makeQuickJump();

    // check for new u2u's
    if (X_MEMBER) {
        $query = $db->query("SELECT COUNT(*) FROM ".X_PREFIX."u2u WHERE owner='$xmbuser' AND folder='Inbox' AND readstatus='no'");
        $newu2unum = (int) $db->result($query, 0);
        $db->free_result($query);
        if ($newu2unum > 0) {
            $newu2umsg = "<a href=\"u2u.php\" onclick=\"Popup(this.href, 'Window', 700, 450); return false;\">{$lang['newu2u1']} $newu2unum {$lang['newu2u2']}</a>";
            // Popup Alert
            if ('2' === $self['u2ualert'] || ('1' === $self['u2ualert'] && X_SCRIPT == 'index.php')) {
                $newu2umsg .= '<script language="JavaScript" type="text/javascript">function u2uAlert() { ';
                if ($newu2unum == 1) {
                    $newu2umsg .= 'u2uAlertMsg = "'.$lang['newu2u1'].' '.$newu2unum.$lang['u2ualert5'].'"; ';
                } else {
                    $newu2umsg .= 'u2uAlertMsg = "'.$lang['newu2u1'].' '.$newu2unum.$lang['u2ualert6'].'"; ';
                }
                $newu2umsg .= "if (confirm(u2uAlertMsg)) { Popup('u2u.php', 'testWindow', 700, 450); } } setTimeout('u2uAlert();', 10);</script>";
            }
        }
    }
} else {
    template()->links = '';
    template()->newu2umsg = '';
    template()->pluglink = '';
    template()->searchlink = '';
    template()->quickjump = '';
}


/* Perform HTTP Connection Maintenance */

assertEmptyOutputStream('header.php');

// Gzip-compression
if ($SETTINGS['gzipcompress'] == 'on'
 && $action != 'captchaimage'
 && X_SCRIPT != 'files.php'
 && !DEBUG) {
    if (($res = @ini_get('zlib.output_compression')) > 0) {
        // leave it
    } else if ($res === false) {
        // ini_get not supported. So let's just leave it
    } else {
        if (function_exists('gzopen')) {
            $r = @ini_set('zlib.output_compression', 4096);
            $r2 = @ini_set('zlib.output_compression_level', '3');
            if (FALSE === $r || FALSE === $r2) {
                ob_start('ob_gzhandler');
            }
        } else {
            ob_start('ob_gzhandler');
        }
    }
}

unset($boot);

assertEmptyOutputStream('header.php');
