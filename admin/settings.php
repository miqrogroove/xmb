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

namespace XMB;

define('ROOT', '../');
require ROOT . 'header.php';

$core = \XMB\Services\core();
$db = \XMB\Services\db();
$observer = \XMB\Services\observer();
$session = \XMB\Services\session();
$sql = \XMB\Services\sql();
$template = \XMB\Services\template();
$token = \XMB\Services\token();
$tran = \XMB\Services\translation();
$vars = \XMB\Services\vars();
$lang = &$vars->lang;
$SETTINGS = &$vars->settings;
$THEME = &$vars->theme;

header('X-Robots-Tag: noindex');
header('X-XSS-Protection: 0'); // Disables HTML input errors in Chrome.

$core->nav('<a href="' . $vars->full_url . 'admin/">' . $lang['textcp'] . '</a>');
$core->nav($lang['textsettings']);
$core->setCanonicalLink('admin/settings.php');

if ($vars->settings['subject_in_title'] == 'on') {
    $template->threadSubject = $vars->lang['textsettings'] . ' - ';
}

$core->assertAdminOnly();

$header = $template->process('header.php');

$auditaction = $vars->onlineip . '|#|' . $_SERVER['REQUEST_URI'];
$core->audit($vars->self['username'], $auditaction);

$table = $template->process('admin_table.php');

$admin = new \XMB\admin($core, $db, $session, $sql, $template, $vars);

if (
    noSubmit('settingsubmit1')
    && noSubmit('settingsubmit2')
    && noSubmit('settingsubmit3')
    && noSubmit('settingsubmit4')
    && noSubmit('settingsubmit5')
    && noSubmit('settingsubmit6')
    && noSubmit('settingsubmit7')
    && noSubmit('settingsubmit8')
    && noSubmit('settingsubmit9')
    && noSubmit('settingsubmit10')
) {
    $template->admin = $admin;
    $template->core = $core;

    $template->token = $token->create('Control Panel/settings', 'global', X_NONCE_FORM_EXP);

    $template->langfileselect = $tran->createLangFileSelect($SETTINGS['langfile']);

    $themelist = array();
    $themelist[] = '<select name="themenew">';
    $query = $db->query("SELECT themeid, name FROM " . $vars->tablepre . "themes ORDER BY name ASC");
    while($themeinfo = $db->fetch_array($query)) {
        if ($themeinfo['themeid'] == $SETTINGS['theme']) {
            $themelist[] = '<option value="'.intval($themeinfo['themeid']).'" ' . $vars::selHTML . '>'.$themeinfo['name'].'</option>';
        } else {
            $themelist[] = '<option value="'.intval($themeinfo['themeid']).'">'.$themeinfo['name'].'</option>';
        }
    }
    $db->free_result($query);
    $themelist[] = '</select>';
    $template->themelist = implode("\n", $themelist);

    if ('24' === $SETTINGS['timeformat']) {
        $template->check12 = '';
        $template->check24 = $vars::cheHTML;
    } else {
        $template->check12 = $vars::cheHTML;
        $template->check24 = '';
    }

    $template->indexShowBarCats = false;
    $template->indexShowBarTop = false;
    $template->indexShowBarNone = false;
    switch($SETTINGS['indexshowbar']) {
        case 1:
            $template->indexShowBarCats = true;
            break;
        case 3:
            $template->indexShowBarNone = true;
            break;
        default:
            $template->indexShowBarTop = true;
            break;
    }

    if (defined('PSPELL_FAST')) {
        $template->spell_off_reason = '';
    } else {
        $template->spell_off_reason = $lang['pspell_needed'];
        $SETTINGS['spellcheck'] = 'off';
    }

    $notifycheck = [
        0 => false,
        1 => false,
        2 => false,
    ];
    if ($SETTINGS['notifyonreg'] == 'off') {
        $notifycheck[0] = true;
    } else if ($SETTINGS['notifyonreg'] == 'u2u') {
        $notifycheck[1] = true;
    } else {
        $notifycheck[2] = true;
    }
    $template->notifycheck = $notifycheck;

    $allowipreg = [
        0 => false,
        1 => false,
    ];
    if ($SETTINGS['ipreg'] == 'on') {
        $allowipreg[0] = true;
    } else {
        $allowipreg[1] = true;
    }
    $template->allowipreg = $allowipreg;

    $avchecked = [
        0 => false,
        1 => false,
        2 => false,
    ];
    if ($SETTINGS['avastatus'] == 'list') {
        $avchecked[1] = true;
    } else if ($SETTINGS['avastatus'] == 'off') {
        $avchecked[2] = true;
    } else {
        $avchecked[0] = true;
    }
    $template->avchecked = $avchecked;
    
    $template->tickercodechecked = [ $SETTINGS['tickercode'] == 'plain', $SETTINGS['tickercode'] == 'bbcode', $SETTINGS['tickercode'] == 'html' ];

    $footer_options = explode('-', $SETTINGS['footer_options']);
    $sel_serverload = in_array('serverload', $footer_options);
    $sel_queries = in_array('queries', $footer_options);
    $sel_phpsql = in_array('phpsql', $footer_options);
    $sel_loadtimes = in_array('loadtimes', $footer_options);
    $template->values = array('serverload', 'queries', 'phpsql', 'loadtimes');
    $template->names = array($lang['Enable_Server_Load'], $lang['Enable_Queries'], $lang['Enable_PHP_SQL'], $lang['Enable_Page_load']);
    $template->checked = array($sel_serverload, $sel_queries, $sel_phpsql, $sel_loadtimes);

    $template->max_avatar_sizes = explode('x', $SETTINGS['max_avatar_size']);

    $captcha = new \XMB\Captcha($core, $observer, $vars);
    $template->goodCaptcha = $captcha->bCompatible;

    $body = $template->process('admin_settings.php');
} else {
    $core->request_secure('Control Panel/settings', 'global', error_header: true);

    $spellchecknew = ($_POST['spellchecknew'] == 'on' && defined('PSPELL_FAST')) ? 'on' : 'off';
    $notifyonregnew = ($_POST['notifyonregnew'] == 'off') ? 'off' : ($_POST['notifyonregnew'] == 'u2u' ? 'u2u' : 'email');
    $avastatusnew = $core->postedVar('avastatusnew');
    if ($avastatusnew != 'on' && $avastatusnew != 'list') {
        $avastatusnew = 'off';
    }
    $recaptchanew = $core->postedVar('recaptchanew');
    if ($recaptchanew != 'on' || trim($core->postedVar('recaptchasecretnew')) == '' || trim($core->postedVar('recaptchakeynew')) == '') {
        $recaptchanew = 'off';
    }

    $new_footer_options = $core->postedArray('new_footer_options');
    if (!empty($new_footer_options)) {
        $footer_options = implode('-', $new_footer_options);
    } else {
        $footer_options = '';
    }

    $maxAttachSize = (string) min(phpShorthandValue('upload_max_filesize'), formInt('maxAttachSize'));
    $def_tz_new = isset($_POST['timeoffset1']) && is_numeric($_POST['timeoffset1']) ? $_POST['timeoffset1'] : '0';
    $addtimenew = isset($_POST['addtimenew']) && is_numeric($_POST['addtimenew']) ? $_POST['addtimenew'] : '0';
    $max_avatar_size_w_new = formInt('max_avatar_size_w_new');
    $max_avatar_size_h_new = formInt('max_avatar_size_h_new');
    $max_avatar_size = $max_avatar_size_w_new.'x'.$max_avatar_size_h_new;

    $max_image_size_w_new = formInt('max_image_size_w_new');
    $max_image_size_h_new = formInt('max_image_size_h_new');
    $max_thumb_size_w_new = formInt('max_thumb_size_w_new');
    $max_thumb_size_h_new = formInt('max_thumb_size_h_new');
    $max_image_size = $max_image_size_w_new.'x'.$max_image_size_h_new;
    $max_thumb_size = $max_thumb_size_w_new.'x'.$max_thumb_size_h_new;

    $admin->input_custom_setting('addtime', $addtimenew);
    $admin->input_string_setting('adminemail', 'adminemailnew');
    $admin->input_onoff_setting('allowrankedit', 'allowrankeditnew');
    $admin->input_onoff_setting('attachimgpost', 'attachimgpostnew');
    $admin->input_onoff_setting('attach_remote_images', 'remoteimages');
    $admin->input_custom_setting('avastatus', $avastatusnew);
    $admin->input_onoff_setting('bbinsert', 'bbinsertnew');
    $admin->input_string_setting('bbname', 'bbnamenew');
    $admin->input_string_setting('bboffreason', 'bboffreasonnew');
    $admin->input_onoff_setting('bbrules', 'bbrulesnew');
    $admin->input_string_setting('bbrulestxt', 'bbrulestxtnew', false);
    $admin->input_onoff_setting('bbstatus', 'bbstatusnew');
    $admin->input_onoff_setting('captcha_code_casesensitive', 'captchacodecasenew');
    $admin->input_string_setting('captcha_code_charset', 'captchacharsetnew');
    $admin->input_int_setting('captcha_code_length', 'captchacodenew');
    $admin->input_onoff_setting('captcha_code_shadow', 'captchacodeshadownew');
    $admin->input_string_setting('captcha_image_bg', 'captchaimagebgnew');
    $admin->input_onoff_setting('captcha_image_color', 'captchaimagecolornew');
    $admin->input_int_setting('captcha_image_dots', 'captchaimagedotsnew');
    $admin->input_string_setting('captcha_image_fonts', 'captchaimagefontsnew');
    $admin->input_int_setting('captcha_image_height', 'captchaimageheightnew');
    $admin->input_int_setting('captcha_image_lines', 'captchaimagelinesnew');
    $admin->input_int_setting('captcha_image_maxfont', 'captchaimagemaxfontnew');
    $admin->input_int_setting('captcha_image_minfont', 'captchaimageminfontnew');
    $admin->input_string_setting('captcha_image_type', 'captchaimagetypenew');
    $admin->input_int_setting('captcha_image_width', 'captchaimagewidthnew');
    $admin->input_onoff_setting('captcha_post_status', 'captchapostnew');
    $admin->input_onoff_setting('captcha_reg_status', 'captcharegnew');
    $admin->input_onoff_setting('captcha_search_status', 'captchasearchnew');
    $admin->input_onoff_setting('captcha_status', 'captchanew');
    $admin->input_onoff_setting('catsonly', 'catsonlynew');
    $admin->input_onoff_setting('coppa', 'coppanew');
    $admin->input_string_setting('dateformat', 'dateformatnew');
    $admin->input_custom_setting('def_tz', $def_tz_new);
    $admin->input_onoff_setting('dotfolders', 'dotfoldersnew');
    $admin->input_onoff_setting('doublee', 'doubleenew');
    $admin->input_onoff_setting('editedby', 'editedbynew');
    $admin->input_onoff_setting('emailcheck', 'emailchecknew');
    $admin->input_onoff_setting('faqstatus', 'faqstatusnew');
    $admin->input_int_setting('filesperpost', 'filesperpostnew');
    $admin->input_int_setting('files_min_disk_size', 'filesminsizenew');
    $admin->input_string_setting('files_storage_path', 'filespathnew');
    $admin->input_int_setting('files_subdir_format', 'filessubdirnew');
    $admin->input_int_setting('file_url_format', 'filesurlpathnew');
    $admin->input_string_setting('files_virtual_url', 'filesbasenew');
    $admin->input_int_setting('floodctrl', 'floodctrlnew');
    $admin->input_custom_setting('footer_options', $footer_options);
    $admin->input_custom_setting('google_captcha', $recaptchanew);
    $admin->input_string_setting('google_captcha_secret', 'recaptchasecretnew');
    $admin->input_string_setting('google_captcha_sitekey', 'recaptchakeynew');
    $admin->input_onoff_setting('gzipcompress', 'gzipcompressnew');
    $admin->input_onoff_setting('hideprivate', 'hidepriv');
    $admin->input_onoff_setting('hide_banned', 'hidebannednew');
    $admin->input_int_setting('hottopic', 'hottopicnew');
    $admin->input_onoff_setting('images_https_only', 'imageshttpsnew');
    $admin->input_int_setting('indexshowbar', 'indexShowBarNew');
    $admin->input_onoff_setting('index_stats', 'index_statsnew');
    $admin->input_onoff_setting('ipreg', 'ipReg');
    $admin->input_string_setting('langfile', 'langfilenew');
    $admin->input_custom_setting('maxattachsize', $maxAttachSize);
    $admin->input_int_setting('maxdayreg', 'maxDayReg');
    $admin->input_custom_setting('max_avatar_size', $max_avatar_size);
    $admin->input_custom_setting('max_image_size', $max_image_size);
    $admin->input_custom_setting('max_thumb_size', $max_thumb_size);
    $admin->input_int_setting('memberperpage', 'memberperpagenew');
    $admin->input_onoff_setting('memliststatus', 'memliststatusnew');
    $admin->input_custom_setting('notifyonreg', $notifyonregnew);
    $admin->input_int_setting('onlinetodaycount', 'onlinetodaycountnew');
    $admin->input_onoff_setting('onlinetoday_status', 'onlinetoday_statusnew');
    $admin->input_int_setting('postperpage', 'postperpagenew');
    $admin->input_int_setting('pruneusers', 'pruneusersnew');
    $admin->input_onoff_setting('quarantine_new_users', 'quarantinenew');
    $admin->input_onoff_setting('quickjump_status', 'quickjump_statusnew');
    $admin->input_onoff_setting('quickreply_status', 'quickreply_statusnew');
    $admin->input_onoff_setting('regoptional', 'regoptionalnew');
    $admin->input_onoff_setting('regstatus', 'reg_on');
    $admin->input_onoff_setting('regviewonly', 'regviewnew');
    $admin->input_onoff_setting('reportpost', 'reportpostnew');
    $admin->input_onoff_setting('resetsigs', 'resetSigNew');
    $admin->input_onoff_setting('searchstatus', 'searchstatusnew');
    $admin->input_onoff_setting('showsubforums', 'showsubforumsnew');
    $admin->input_onoff_setting('show_logs_in_threads', 'showlogsnew');
    $admin->input_onoff_setting('sigbbcode', 'sigbbcodenew');
    $admin->input_string_setting('sitename', 'sitenamenew');
    $admin->input_string_setting('siteurl', 'siteurlnew');
    $admin->input_int_setting('smcols', 'smcolsnew');
    $admin->input_onoff_setting('smileyinsert', 'smileyinsertnew');
    $admin->input_int_setting('smtotal', 'smtotalnew');
    $admin->input_onoff_setting('space_cats', 'space_catsnew');
    $admin->input_custom_setting('spellcheck', $spellchecknew);
    $admin->input_onoff_setting('stats', 'statsstatusnew');
    $admin->input_onoff_setting('subject_in_title', 'subjectInTitleNew');
    $admin->input_int_setting('theme', 'themenew');
    $admin->input_string_setting('tickercode', 'tickercodenew');
    $admin->input_string_setting('tickercontents', 'tickercontentsnew');
    $admin->input_int_setting('tickerdelay', 'tickerdelaynew');
    $admin->input_onoff_setting('tickerstatus', 'tickerstatusnew');
    $admin->input_int_setting('timeformat', 'timeformatnew');
    $admin->input_onoff_setting('todaysposts', 'todaystatusnew');
    $admin->input_int_setting('topicperpage', 'topicperpagenew');
    $admin->input_int_setting('u2uquota', 'u2uquotanew');
    $admin->input_onoff_setting('whosonlinestatus', 'whos_on');

    $body = '<tr bgcolor="' . $THEME['altbg2'] . '" class="ctrtablerow"><td>' . $lang['textsettingsupdate'] . '</td></tr>';
}

$endTable = $template->process('admin_table_end.php');

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');

echo $header, $table, $body, $endTable, $footer;
