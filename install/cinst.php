<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2021, The XMB Group
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
 *
 **/

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

if (!defined('ROOT')) {
    define('ROOT', './');
}

if (!defined('X_INST_ERR')) {
    define('X_INST_ERR', 0);
    define('X_INST_WARN', 1);
    define('X_INST_OK', 2);
    define('X_INST_SKIP', 3);
}

if (!function_exists('show_act')) {
    function show_act($act) {
        $act .= str_repeat('.', (75-strlen($act)));
        echo '<span class="progress">'.$act;
    }
}

if (!function_exists('show_result')) {
    function show_result($type) {
        switch($type) {
            case 0:
                echo '<span class="progressErr">ERROR</span><br />';
                break;
            case 1:
                echo '<span class="progressWarn">WARNING</span><br />';
                break;
             case 2:
                echo '<span class="progressOk">OK</span><br />';
                break;
             case 3:
                echo '<span class="progressSkip">SKIPPED</span><br />';
                break;
        }
        echo "</span>\n";
    }
}

function rmFromDir($path) {
    if (is_dir($path)) {
        $stream = opendir($path);
        while(($file = readdir($stream)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            rmFromDir($path.'/'.$file);
        }
        closedir($stream);
        @rmdir($path);
    } else if (is_file($path)) {
        @unlink($path);
    }
}

while(ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(1);

require(ROOT.'include/global.inc.php');
require_once(ROOT.'config.php');
require(ROOT.'db/'.$database.'.php');
require(ROOT.'include/schema.inc.php');

define('X_PREFIX', $tablepre);

$db = new dbstuff;
$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect, true);

show_act("Checking Super Administrator Account");
$vUsername = trim($frmUsername);
$iUsername = strtolower($vUsername);
$frmPassword = trim($frmPassword);
$vEmail = trim($frmEmail);

if ($vUsername == '' || $frmPassword == '' || $vEmail == '') {
    show_result(X_INST_ERR);
    $errStr = 'The username, password or e-mail address cannot be blank or malformed. Please press back and try again.';
    error('Bad super administrator credentials', $errStr);
    exit();
}

if ($iUsername == 'anonymous' || $iUsername == 'xguest123' || strlen($vUsername) > 32 || strlen($vUsername) < 3) {
    show_result(X_INST_ERR);
    $errStr = 'The username you provided is not valid for XMB. Please press back and create a different username.';
    error('Bad super administrator credentials', $errStr);
    exit();
}

if ( $frmPassword !== $frmPasswordCfm ) {
    show_result(X_INST_ERR);
    $errStr = 'The passwords do not match. Please press back and try again.';
    error('Bad super administrator credentials', $errStr);
    exit();
}

$nonprinting = '\\x00-\\x1F\\x7F-\\x9F\\xAD';
$specials = '\\]\'<>\\\\|"[,@';  //Other universal chars disallowed by XMB: []'"<>\|,@
$sequences = '|  ';  //Phrases disallowed, each separated by '|'
if ( $vUsername !== preg_replace( "#[{$nonprinting}{$specials}]{$sequences}#", '', $vUsername ) ) {
    show_result(X_INST_ERR);
    $errStr = 'The username may not contain special characters. Please press back and try again.';
    error('Bad super administrator credentials', $errStr);
    exit();
}

// these two are used waaaaay down below.
$vPassword = md5($frmPassword);
$myDate = time();
show_result(X_INST_OK);

// is XMB already installed?
show_act('Checking for previous XMB Installations');
if (xmb_schema_table_exists('settings')) {
    show_result(X_INST_WARN);
    $errStr = 'An existing installation of XMB has been detected in the "'
    . $dbname . '" database located on "'
    . $dbhost . '". <br />If you wish to overwrite this installation, please drop your "'
    . X_PREFIX . 'settings" table by using <pre>DROP TABLE `'
    . X_PREFIX . 'settings`;</pre>To install another forum on the same database, go back and enter a different table prefix.';
    error('XMB Already Installed', $errStr);
    exit();
}
show_result(X_INST_OK);

// Create all tables.
foreach(xmb_schema_list() as $table) {
    show_act("Creating ".X_PREFIX.$table);
    xmb_schema_table('overwrite', $table);
    show_result(X_INST_OK);
}


// -- Insert Data -- //
// Reminder: Columns without explicit default values must be set on insert for STRICT_ALL_TABLES mode compatibility.
show_act("Inserting data into ".X_PREFIX."restricted");
$db->query(
    "INSERT INTO ".X_PREFIX."restricted
    (`name`, `case_sensitivity`, `partial`)
    VALUES
    ('Anonymous', '0', '0'),
    ('xguest123', '0', '0')"
);
show_result(X_INST_OK);

show_act("Inserting data into ".X_PREFIX."forums");
$db->query("INSERT INTO ".X_PREFIX."forums VALUES ('forum', 1, 'Default Forum', 'on', '', '', 0, 'This is the default forum created during installation<br />To create or modify forums go to the forum section of the administration panel', 'yes', 'yes', '', 0, 0, 0, 0, '31,31,31,63', 'yes', 'on', '');");
show_result(X_INST_OK);

show_act("Inserting data into ".X_PREFIX."ranks");
$db->query(
    "INSERT INTO ".X_PREFIX."ranks
    VALUES
    ('Newbie',               0, 1, 1, 'yes', ''),
    ('Junior Member',        2, 2, 2, 'yes', ''),
    ('Member',             100, 3, 3, 'yes', ''),
    ('Senior Member',      500, 4, 4, 'yes', ''),
    ('Posting Freak',     1000, 5, 5, 'yes', ''),
    ('Moderator',           -1, 6, 6, 'yes', ''),
    ('Super Moderator',     -1, 7, 7, 'yes', ''),
    ('Administrator',       -1, 8, 8, 'yes', ''),
    ('Super Administrator', -1, 9, 9, 'yes', '')"
);
show_result(X_INST_OK);

show_act("Inserting data into ".X_PREFIX."settings");
$db->query( "INSERT INTO ".X_PREFIX."settings
(name, value) VALUES
('addtime', '0'),
('adminemail', 'webmaster@domain.ext'),
('allowrankedit', 'on'),
('attachimgpost', 'on'),
('attach_remote_images', 'off'),
('authorstatus', 'on'),
('avastatus', 'on'),
('bbinsert', 'on'),
('bbname', 'Your Forums'),
('bboffreason', ''),
('bbrules', 'off'),
('bbrulestxt', ''),
('bbstatus', 'on'),
('captcha_status', 'on'),
('captcha_reg_status', 'on'),
('captcha_post_status', 'on'),
('captcha_search_status', 'off'),
('captcha_code_charset', 'A-Z'),
('captcha_code_length', '8'),
('captcha_code_casesensitive', 'off'),
('captcha_code_shadow', 'off'),
('captcha_image_type', 'png'),
('captcha_image_width', '250'),
('captcha_image_height', '50'),
('captcha_image_bg', ''),
('captcha_image_dots', '0'),
('captcha_image_lines', '70'),
('captcha_image_fonts', ''),
('captcha_image_minfont', '16'),
('captcha_image_maxfont', '25'),
('captcha_image_color', 'off'),
('catsonly', 'off'),
('coppa', 'off'),
('dateformat', 'dd-mm-yyyy'),
('def_tz', '0.00'),
('dotfolders', 'on'),
('doublee', 'off'),
('editedby', 'off'),
('emailcheck', 'off'),
('faqstatus', 'on'),
('filesperpost', '10'),
('files_min_disk_size', '9216'),
('files_storage_path', ''),
('files_subdir_format', '1'),
('file_url_format', '1'),
('files_virtual_url', ''),
('floodctrl', '5'),
('footer_options', 'queries-phpsql-loadtimes-totaltime'),
('google_captcha', 'off'),
('google_captcha_sitekey', ''),
('google_captcha_secret', ''),
('gzipcompress', 'on'),
('hideprivate', 'on'),
('hide_banned', 'off'),
('hottopic', '20'),
('images_https_only', 'off'),
('indexshowbar', '2'),
('index_stats', 'on'),
('ipreg', 'on'),
('ip_banning', 'off'),
('langfile', 'English'),
('maxattachsize', '256000'),
('maxdayreg', '25'),
('max_avatar_size', '100x100'),
('max_image_size', '1000x1000'),
('max_thumb_size', '200x200'),
('memberperpage', '45'),
('memliststatus', 'on'),
('notifyonreg', 'off'),
('onlinetodaycount', '50'),
('onlinetoday_status', 'on'),
('postperpage', '25'),
('pruneusers', '0'),
('quarantine_new_users', 'off'),
('quickjump_status', 'on'),
('quickreply_status', 'on'),
('regoptional', 'off'),
('regstatus', 'on'),
('regviewonly', 'off'),
('reportpost', 'on'),
('resetsigs', 'off'),
('schema_version', '".XMB_SCHEMA_VER."'),
('searchstatus', 'on'),
('showsubforums', 'off'),
('show_logs_in_threads', 'off'),
('sigbbcode', 'on'),
('sitename', 'YourDomain.com'),
('siteurl', '$full_url'),
('smcols', '4'),
('smileyinsert', 'on'),
('smtotal', '16'),
('space_cats', 'off'),
('spellcheck', 'off'),
('stats', 'on'),
('subject_in_title', 'off'),
('theme', '1'),
('tickercode', 'html'),
('tickercontents', '<strong>Welcome to your new XMB Forum!</strong>\nWe recommend changing your forums <a href=\"{$full_url}cp.php?action=settings\">settings</a> first.'),
('tickerdelay', '4000'),
('tickerstatus', 'on'),
('timeformat', '12'),
('todaysposts', 'on'),
('topicperpage', '30'),
('u2uquota', '600'),
('whosonlinestatus', 'on')"
);
show_result(X_INST_OK);

show_act("Inserting data into ".X_PREFIX."smilies");
$db->query(
    "INSERT INTO ".X_PREFIX."smilies
    VALUES
    ('smiley', ':)',             'smile.gif', 1),
    ('smiley', ':(',             'sad.gif', 2),
    ('smiley', ':thumbdown:',    'thumbdown.gif', 3),
    ('smiley', ';)',             'wink.gif', 4),
    ('smiley', ':cool:',         'cool.gif', 5),
    ('smiley', ':mad:',          'mad.gif', 6),
    ('smiley', ':punk:',         'punk.gif', 7),
    ('smiley', ':blush:',        'blush.gif', 8),
    ('smiley', ':love:',         'love.gif', 9),
    ('smiley', ':ninja:',        'ninja.gif', 10),
    ('smiley', ':fake sniffle:', 'fake_sniffle.gif', 11),
    ('smiley', ':smilegrin:',    'smilegrin.gif', 12),
    ('smiley', ':kiss:',         'kiss.gif', 13),
    ('smiley', ':no:',           'no.gif', 14),
    ('smiley', ':post:',         'post.gif', 15),
    ('smiley', ':lol:',          'lol.gif', 16),
    ('smiley', ':sniffle:',      'sniffle.gif', 17),
    ('smiley', ':starhit:',      'starhit.gif', 18),
    ('smiley', ':yes:',          'yes.gif', 19),
    ('smiley', ':grind:',        'grind.gif', 20),
    ('smiley', ':crazy:',        'crazy.gif', 21),
    ('smiley', ':spin:',         'spin.gif', 22),
    ('smiley', ':exclamation:',  'exclamation.gif', 23),
    ('smiley', ':bigsmile:',     'bigsmile.gif', 24),
    ('smiley', ':smirk:',        'smirk.gif', 25),
    ('smiley', ':borg:',         'borg.gif', 26),
    ('smiley', ':rolleyes:',     'rolleyes.gif', 27),
    ('smiley', ':info:',         'info.gif', 28),
    ('smiley', ':question:',     'question.gif', 29),
    ('smiley', ':thumbup:',      'thumbup.gif', 30),
    ('smiley', ':dork:',         'dork.gif', 31),
    ('picon',  '',               'cool.gif', 32),
    ('picon',  '',               'mad.gif', 33),
    ('picon',  '',               'thumbup.gif', 34),
    ('picon',  '',               'thumbdown.gif', 35),
    ('picon',  '',               'post.gif', 36),
    ('picon',  '',               'exclamation.gif', 37),
    ('picon',  '',               'info.gif', 38),
    ('picon',  '',               'question.gif', 39)"
);
show_result(X_INST_OK);

show_act("Inserting data into ".X_PREFIX."templates");
$templates = explode("|#*XMB TEMPLATE FILE*#|", file_get_contents(ROOT.'templates.xmb'));
$values = array();
foreach($templates as $val) {
    $template = explode("|#*XMB TEMPLATE*#|", $val);
    $template[1] = isset($template[1]) ? addslashes(ltrim($template[1])) : '';
    $db->escape_fast($template[0]);
    $db->escape_fast($template[1]);
    $values[] = "('{$template[0]}', '{$template[1]}')";
}
unset($templates);
if (count($values) > 0) {
    $values = implode(', ', $values);
    $db->query("INSERT INTO ".X_PREFIX."templates (name, template) VALUES $values");
}
unset($values);
$db->query("DELETE FROM ".X_PREFIX."templates WHERE name=''");
show_result(X_INST_OK);

show_act("Inserting data into ".X_PREFIX."themes");
$db->query("INSERT INTO ".X_PREFIX."themes (`name`,      `bgcolor`, `altbg1`,  `altbg2`,  `link`,    `bordercolor`, `header`,  `headertext`, `top`,       `catcolor`,   `tabletext`, `text`,    `borderwidth`, `tablewidth`, `tablespace`, `font`,                              `fontsize`, `boardimg`, `imgdir`,       `smdir`,          `cattext`) "
                                   ."VALUES ('XMB Davis', 'bg.gif',  '#FFFFFF', '#f4f7f8', '#24404b', '#86a9b6',     '#d3dfe4', '#24404b',    'topbg.gif', 'catbar.gif', '#000000',   '#000000', '1px',         '97%',        '5px',        'Tahoma, Arial, Helvetica, Verdana', '11px',     'logo.gif', 'images/davis', 'images/smilies', '#163c4b');");
show_result(X_INST_OK);

show_act("Inserting data into ".X_PREFIX."words");
$db->query(
    "INSERT INTO ".X_PREFIX."words (`find`, `replace1`)
    VALUES
    ('cock',         '[b]****[/b]'),
    ('dick',         '[b]****[/b]'),
    ('fuck',         '[b][Censored][/b]'),
    ('shit',         '[b][Censored][/b]'),
    ('faggot',       '[b][Censored][/b]'),
    ('bitch',        '[b][Censored][/b]'),
    ('whore',        '[b][Censored][/b]'),
    ('mofo',         '[b][Censored][/b]'),
    ('shite',        '[b][Censored][/b]'),
    ('asshole',      '[b][Censored][/b]'),
    ('dumbass',      '[b][Censored][/b]'),
    ('blowjob',      '[b][Censored][/b]'),
    ('porn',         '[b][Censored][/b]'),
    ('masturbate',   '[b][Censored][/b]'),
    ('masturbation', '[b][Censored][/b]'),
    ('jackoff',      '[b][Censored][/b]'),
    ('jack off',     '[b][Censored][/b]'),
    ('s h i t',      '[b][Censored][/b]'),
    ('f u c k',      '[b][Censored][/b]'),
    ('f a g g o t',  '[b][Censored][/b]'),
    ('b i t c h',    '[b][Censored][/b]'),
    ('cunt',         '[b][Censored][/b]'),
    ('c u n t',      '[b][Censored][/b]'),
    ('damn',         'dang')"
);
show_result(X_INST_OK);

show_act("Creating Super Administrator Account");
$db->query("INSERT INTO ".X_PREFIX."members (`username`, `password`, `regdate`, `email`, `status`, `bio`, `sig`, `showemail`, `theme`, `langfile`, `timeformat`, `dateformat`, `mood`, `pwdate`, `tpp`, `ppp`, `ignoreu2u`, `u2ufolders`, `saveogu2u`, `emailonu2u`, `useoldu2u`) VALUES ('$vUsername', '$vPassword', $myDate, '$vEmail', 'Super Administrator', '', '', 'no', 0, 'English', 12, 'dd-mm-yyyy', '', $myDate, 30, 30, '', '', 'yes', 'no', 'no')");
show_result(X_INST_OK);

show_act("Inserting data into translation tables");
require ROOT.'include/translation.inc.php';
$upload = file_get_contents(ROOT.'lang/English.lang.php');
installNewTranslation($upload);
show_result(X_INST_OK);

// Try to remove all files now
show_act('Removing installer files');
chdir('..');
rmFromDir('install');
clearstatcache();
if (file_exists('./install')) {
    show_result(X_INST_SKIP);
    error('Permission Error', 'XMB could not remove the installer because of wrong permissions. Please remove it manually via eg. FTP', false);
} else {
    show_result(X_INST_OK);
}
?>
