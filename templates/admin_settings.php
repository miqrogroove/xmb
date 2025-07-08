<?php

declare(strict_types=1);

namespace XMB;

?>
<tr bgcolor="<?= $THEME['altbg2'] ?>">
<td align="center">
<div id="tabs">
 <button onclick="switchTab(this, 'boardDetail')" class="active"><?= $lang['admin_main_settings1']; ?></button>
 <button onclick="switchTab(this, 'defaults')"><?= $lang['admin_main_settings2']; ?></button>
 <button onclick="switchTab(this, 'modules')"><?= $lang['admin_main_settings3']; ?></button>
 <button onclick="switchTab(this, 'cosmetic')"><?= $lang['admin_main_settings4']; ?></button>
 <button onclick="switchTab(this, 'front')"><?= $lang['admin_main_settings9']; ?></button>
 <button onclick="switchTab(this, 'users')"><?= $lang['admin_main_settings5']; ?></button>
 <button onclick="switchTab(this, 'attachments')"><?= $lang['admin_main_settings8']; ?></button>
 <button onclick="switchTab(this, 'other')"><?= $lang['admin_main_settings6']; ?></button>
 <button onclick="switchTab(this, 'captcha')"><?= $lang['admin_main_settings7']; ?></button>
 <button onclick="switchTab(this, 'thirdParty')"><?= $lang['admin_main_settings10']; ?></button>
</div>
<form method="post" action="<?= $full_url ?>admin/settings.php">
<input type="hidden" name="token" value="<?= $token ?>" />
<table cellspacing="0" cellpadding="0" border="0" width="<?= $THEME['tablewidth'] ?>" align="center" class="settings-wrap">
<tr>
<td bgcolor="<?= $THEME['bordercolor'] ?>">
<table border="0" cellspacing="<?= $THEME['borderwidth'] ?>" cellpadding="<?= $THEME['tablespace'] ?>" width="100%" id="settings">
<tbody id="boardDetail">
<tr class="category">
<td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="1" />&raquo;&nbsp;<?= $lang['admin_main_settings1'] ?></font></strong></td>
</tr>
<?php
$rulesDesc = $lang['textbbrulestxt'] . '<br /><br />' . $lang['texthtmlis'] . ' ' . $lang['texton'];
$admin->printsetting2($lang['textsitename'], 'sitenamenew', $SETTINGS['sitename'], 50);
$admin->printsetting2($lang['bbname'], 'bbnamenew', $SETTINGS['bbname'], 50);
$admin->printsetting2($lang['textsiteurl'], 'siteurlnew', $SETTINGS['siteurl'], 50);
$admin->printsetting6($lang['textbbrules'], 'bbrulesnew', 'bbrules');
$admin->printsetting4($rulesDesc, 'bbrulestxtnew', htmlEsc($SETTINGS['bbrulestxt']), 5, 50);
$admin->printsetting6($lang['textbstatus'], 'bbstatusnew', 'bbstatus');
$admin->printsetting4($lang['textbboffreason'], 'bboffreasonnew', $SETTINGS['bboffreason'], 5, 50);
$admin->printsetting6($lang['gzipcompression'], 'gzipcompressnew', 'gzipcompress');
?>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</tbody>
<tbody id="defaults">
<tr class="category">
<td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="2" />&raquo;&nbsp;<?= $lang['admin_main_settings2'] ?></font></strong></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['textlanguage'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $langfileselect ?></td>
</tr>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['texttheme'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><?= $themelist ?></td>
</tr>
<?php
$admin->printsetting2($lang['textppp'], 'postperpagenew', ((int) $SETTINGS['postperpage']), 3);
$admin->printsetting2($lang['texttpp'], 'topicperpagenew', ((int) $SETTINGS['topicperpage']), 3);
$admin->printsetting2($lang['textmpp'], 'memberperpagenew', ((int) $SETTINGS['memberperpage']), 3);
?>
<tr class="tablerow">
<td bgcolor="<?= $THEME['altbg1'] ?>"><?= $lang['texttimeformat'] ?></td>
<td bgcolor="<?= $THEME['altbg2'] ?>"><input type="radio" value="24" name="timeformatnew" <?= $check24 ?> />&nbsp;<?= $lang['text24hour'] ?>&nbsp;<input type="radio" value="12" name="timeformatnew" <?= $check12 ?> />&nbsp;<?= $lang['text12hour'] ?></td>
</tr>
<?php
$admin->printsetting2($lang['dateformat'], 'dateformatnew', $SETTINGS['dateformat'], 20);
?>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</tbody>
<tbody id="modules">
<tr class="category">
<td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="3" />&raquo;&nbsp;<?= $lang['admin_main_settings3'] ?></font></strong></td>
</tr>
<?php
$admin->printsetting6($lang['textsearchstatus'], 'searchstatusnew', 'searchstatus');
$admin->printsetting6($lang['textfaqstatus'], 'faqstatusnew', 'faqstatus');
$admin->printsetting6($lang['texttodaystatus'], 'todaystatusnew', 'todaysposts');
$admin->printsetting6($lang['textstatsstatus'], 'statsstatusnew', 'stats');
$admin->printsetting6($lang['textmemliststatus'], 'memliststatusnew', 'memliststatus');
$admin->printsetting6($lang['coppastatus'], 'coppanew', 'coppa');
$admin->printsetting6($lang['reportpoststatus'], 'reportpostnew', 'reportpost');
?>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</tbody>
<tbody id="cosmetic">
<tr class="category">
<td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="4" />&raquo;&nbsp;<?= $lang['admin_main_settings4'] ?></font></strong></td>
</tr>
<?php
$admin->printsetting6($lang['showsubforums'], 'showsubforumsnew', 'showsubforums');
$admin->printsetting6($lang['space_cats'], 'space_catsnew', 'space_cats');
$admin->printsetting3($lang['indexShowBarDesc'], 'indexShowBarNew', array($lang['indexShowBarCats'], $lang['indexShowBarTop'], $lang['indexShowBarNone']), array(1, 2, 3), array($indexShowBarCats, $indexShowBarTop, $indexShowBarNone), false);
$admin->printsetting6($lang['quickreply_status'], 'quickreply_statusnew', 'quickreply_status');
$admin->printsetting6($lang['quickjump_status'], 'quickjump_statusnew', 'quickjump_status');
$admin->printsetting6($lang['allowrankedit'], 'allowrankeditnew', 'allowrankedit');
$admin->printsetting6($lang['subjectInTitle'], 'subjectInTitleNew', 'subject_in_title');
$admin->printsetting2($lang['smtotal'], 'smtotalnew', ((int) $SETTINGS['smtotal']), 5);
$admin->printsetting2($lang['smcols'], 'smcolsnew', ((int) $SETTINGS['smcols']), 5);
$admin->printsetting6($lang['dotfolders'], 'dotfoldersnew', 'dotfolders');
$admin->printsetting6($lang['editedby'], 'editedbynew', 'editedby');
$admin->printsetting6($lang['show_logs_in_threads'], 'showlogsnew', 'show_logs_in_threads');
?>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</tbody>
<tbody id="front">
<tr class="category">
<td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="9" />&raquo;&nbsp;<?= $lang['admin_main_settings9'] ?></font></strong></td>
</tr>
<?php
$admin->printsetting6($lang['index_stats'], 'index_statsnew', 'index_stats');
$admin->printsetting6($lang['textcatsonly'], 'catsonlynew', 'catsonly');
$admin->printsetting6($lang['whosonline_on'], 'whos_on', 'whosonlinestatus');
$admin->printsetting6($lang['onlinetoday_status'], 'onlinetoday_statusnew', 'onlinetoday_status');
$admin->printsetting2($lang['max_onlinetodaycount'], 'onlinetodaycountnew', ((int) $SETTINGS['onlinetodaycount']), 5);
$admin->printsetting6($lang['what_tickerstatus'], 'tickerstatusnew', 'tickerstatus');
$admin->printsetting2($lang['what_tickerdelay'], 'tickerdelaynew', ((int) $SETTINGS['tickerdelay']), 5);
$admin->printsetting4($lang['tickercontents'], 'tickercontentsnew', $SETTINGS['tickercontents'], 5, 50);
$admin->printsetting3($lang['tickercode'], 'tickercodenew', array($lang['plaintext'], $lang['textbbcode'], $lang['texthtml']), array('plain', 'bbcode', 'html'), $tickercodechecked, false);
?>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</tbody>
<tbody id="users">
<tr class="category">
<td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="5" />&raquo;&nbsp;<?= $lang['admin_main_settings5'] ?></font></strong></td>
</tr>
<?php
$admin->printsetting6($lang['reg_on'], 'reg_on', 'regstatus');
$admin->printsetting3($lang['ipreg'], 'ipReg', array($lang['texton'], $lang['textoff']), array('on', 'off'), $allowipreg, false);
$admin->printsetting2($lang['max_daily_regs'], 'maxDayReg', ((int) $SETTINGS['maxdayreg']), 3);
$admin->printsetting3($lang['notifyonreg'], 'notifyonregnew', array($lang['textoff'], $lang['viau2u'], $lang['viaemail']), array('off', 'u2u', 'email'), $notifycheck, false);
$admin->printsetting6($lang['textreggedonly'], 'regviewnew', 'regviewonly');
$admin->printsetting6($lang['texthidepriv'], 'hidepriv', 'hideprivate');
$admin->printsetting6($lang['emailverify'], 'emailchecknew', 'emailcheck');
$admin->printsetting6($lang['regoptional'], 'regoptionalnew', 'regoptional');
$admin->printsetting2($lang['textflood'], 'floodctrlnew', ((int) $SETTINGS['floodctrl']), 3);
$admin->printsetting2($lang['u2uquota'], 'u2uquotanew', ((int) $SETTINGS['u2uquota']), 3);
$admin->printsetting3($lang['textavastatus'], 'avastatusnew', array($lang['texton'], $lang['textlist'], $lang['textoff']), array('on', 'list', 'off'), $avchecked, false);
$admin->printsetting6($lang['images_https_only'], 'imageshttpsnew', 'images_https_only');
$admin->printsetting6($lang['resetSigDesc'], 'resetSigNew', 'resetsigs');
$admin->printsetting6($lang['doublee'], 'doubleenew', 'doublee');
$admin->printsetting2($lang['pruneusers'], 'pruneusersnew', ((int) $SETTINGS['pruneusers']), 3);
$admin->printsetting6($lang['moderation_setting'], 'quarantinenew', 'quarantine_new_users');
$admin->printsetting6($lang['hide_banned_users'], 'hidebannednew', 'hide_banned');
?>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</tbody>
<tbody id="attachments">
<tr class="category">
<td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="8" />&raquo;&nbsp;<?= $lang['admin_main_settings8'] ?></font></strong></td>
</tr>
<?php
if (! ini_get('file_uploads')) {
    $admin->printsetting5($lang['status'], $lang['uploadDisabled']);
}
$max_image_sizes = explode('x', $SETTINGS['max_image_size']);
$max_thumb_sizes = explode('x', $SETTINGS['max_thumb_size']);
for ($i = 0; $i <= 4; $i++) {
    $urlformatchecked[$i] = ($SETTINGS['file_url_format'] == $i + 1);
}
for ($i = 0; $i <= 1; $i++) {
    $subdirchecked[$i] = ($SETTINGS['files_subdir_format'] == $i + 1);
}
$admin->printsetting2($lang['textfilesperpost'], 'filesperpostnew', ((int) $SETTINGS['filesperpost']), 3);
$admin->printsetting2($lang['max_attachment_size'], 'maxAttachSize', min(phpShorthandValue('upload_max_filesize'), (int) $SETTINGS['maxattachsize']), 12);
$admin->printsetting2($lang['textfilessizew'], 'max_image_size_w_new', $max_image_sizes[0], 5);
$admin->printsetting2($lang['textfilessizeh'], 'max_image_size_h_new', $max_image_sizes[1], 5);
$admin->printsetting2($lang['textfilesthumbw'], 'max_thumb_size_w_new', $max_thumb_sizes[0], 5);
$admin->printsetting2($lang['textfilesthumbh'], 'max_thumb_size_h_new', $max_thumb_sizes[1], 5);
if (! ini_get('allow_url_fopen')) {
    $admin->printsetting5($lang['attachimginpost'], $lang['no_url_fopen']);
} else {
    $admin->printsetting6($lang['attachimginpost'], 'attachimgpostnew', 'attachimgpost');
}
$admin->printsetting6($lang['textremoteimages'], 'remoteimages', 'attach_remote_images');
$admin->printsetting2($lang['textfilespath'], 'filespathnew', $SETTINGS['files_storage_path'], 50);
$admin->printsetting2($lang['textfilesminsize'], 'filesminsizenew', ((int) $SETTINGS['files_min_disk_size']), 7);
$admin->printsetting3($lang['textfilessubdir'], 'filessubdirnew', array($lang['textfilessubdir1'], $lang['textfilessubdir2']), array('1', '2'), $subdirchecked, false);
$admin->printsetting3($lang['textfilesurlpath'], 'filesurlpathnew', array($lang['textfilesurlpath1'], $lang['textfilesurlpath2'], $lang['textfilesurlpath3'], $lang['textfilesurlpath4'], $lang['textfilesurlpath5']), array('1', '2', '3', '4', '5'), $urlformatchecked, false);
$admin->printsetting2($lang['textfilesbase'], 'filesbasenew', $SETTINGS['files_virtual_url'], 50);
?>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</tbody>
<tbody id="other">
<tr class="category">
<td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="6" />&raquo;&nbsp;<?= $lang['admin_main_settings6'] ?></font></strong></td>
</tr>
<?php
$admin->printsetting2($lang['texthottopic'], 'hottopicnew', ((int) $SETTINGS['hottopic']), 3);
$admin->printsetting6($lang['bbinsert'], 'bbinsertnew', 'bbinsert');
$admin->printsetting6($lang['smileyinsert'], 'smileyinsertnew', 'smileyinsert');
$admin->printsetting3($lang['footer_options'], 'new_footer_options', $names, $values, $checked);
$admin->printsetting5($lang['defaultTimezoneDesc'], $core->timezone_control($SETTINGS['def_tz']));
$admin->printsetting2($lang['addtime'], 'addtimenew', $SETTINGS['addtime'], 3);
$admin->printsetting6($lang['sigbbcode'], 'sigbbcodenew', 'sigbbcode');
if (! ini_get('allow_url_fopen')) {
    $admin->printsetting5($lang['max_avatar_size_w'], $lang['no_url_fopen']);
} else {
    $admin->printsetting2($lang['max_avatar_size_w'], 'max_avatar_size_w_new', $max_avatar_sizes[0], 4);
    $admin->printsetting2($lang['max_avatar_size_h'], 'max_avatar_size_h_new', $max_avatar_sizes[1], 4);
}
?>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</tbody>
<tbody id="captcha">
<tr class="category">
<td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="7" />&raquo;&nbsp;<?= $lang['admin_main_settings7'] ?></font></strong></td>
</tr>
<?php
if (! $goodCaptcha) {
    $admin->printsetting5($lang['captchastatus'], $lang['captcha_not_working']);
    if ($SETTINGS['captcha_status'] == 'on') {
        $admin->printsetting6($lang['captchastatus'], 'captchanew', 'captcha_status');
    }
} else {
    $admin->printsetting6($lang['captchastatus'], 'captchanew', 'captcha_status');
    $admin->printsetting6($lang['captcharegstatus'], 'captcharegnew', 'captcha_reg_status');
    $admin->printsetting6($lang['captchapoststatus'], 'captchapostnew', 'captcha_post_status');
    $admin->printsetting6($lang['captchasearchstatus'], 'captchasearchnew', 'captcha_search_status');
    $admin->printsetting2($lang['captchacharset'], 'captchacharsetnew', $SETTINGS['captcha_code_charset'], 50);
    $admin->printsetting2($lang['captchacodelength'], 'captchacodenew', ((int) $SETTINGS['captcha_code_length']), 3);
    $admin->printsetting6($lang['captchacodecase'], 'captchacodecasenew', 'captcha_code_casesensitive');
    $admin->printsetting6($lang['captchacodeshadow'], 'captchacodeshadownew', 'captcha_code_shadow');
    $admin->printsetting2($lang['captchaimagetype'], 'captchaimagetypenew', $SETTINGS['captcha_image_type'], 5);
    $admin->printsetting2($lang['captchaimagewidth'], 'captchaimagewidthnew', ((int) $SETTINGS['captcha_image_width']), 5);
    $admin->printsetting2($lang['captchaimageheight'], 'captchaimageheightnew', ((int) $SETTINGS['captcha_image_height']), 5);
    $admin->printsetting2($lang['captchaimagebg'], 'captchaimagebgnew', $SETTINGS['captcha_image_bg'], 50);
    $admin->printsetting2($lang['captchaimagedots'], 'captchaimagedotsnew', ((int) $SETTINGS['captcha_image_dots']), 3);
    $admin->printsetting2($lang['captchaimagelines'], 'captchaimagelinesnew', ((int) $SETTINGS['captcha_image_lines']), 3);
    $admin->printsetting2($lang['captchaimagefonts'], 'captchaimagefontsnew', $SETTINGS['captcha_image_fonts'], 50);
    $admin->printsetting2($lang['captchaimageminfont'], 'captchaimageminfontnew', ((int) $SETTINGS['captcha_image_minfont']), 3);
    $admin->printsetting2($lang['captchaimagemaxfont'], 'captchaimagemaxfontnew', ((int) $SETTINGS['captcha_image_maxfont']), 3);
    $admin->printsetting6($lang['captchaimagecolor'], 'captchaimagecolornew', 'captcha_image_color');
}
?>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</tbody>
<tbody id="thirdParty">
<tr class="category">
<td colspan="2"><strong><font color="<?= $THEME['cattext'] ?>"><a name="10" />&raquo;&nbsp;<?= $lang['admin_main_settings10'] ?></font></strong></td>
</tr>
<?php
$recaptcha_link = '<br /><span class="smalltext">[ <a href="https://www.google.com/recaptcha/admin/" onclick="window.open(this.href); return false;">Setup</a> ]';
$admin->printsetting6($lang['google_captcha_onoff'], 'recaptchanew', 'google_captcha');
$admin->printsetting2($lang['google_captcha_sitekey'].$recaptcha_link, 'recaptchakeynew', $SETTINGS['google_captcha_sitekey'], 50);
$admin->printsetting2($lang['google_captcha_secretkey'], 'recaptchasecretnew', $SETTINGS['google_captcha_secret'], 50);
$admin->printsetting3($lang['google_captcha_type'], 'recaptchatypenew', $gcaptchaNames, $gcaptchaValues, $gcaptchaChecked, multi: false);
?>
<tr class="ctrtablerow">
<td bgcolor="<?= $THEME['altbg2'] ?>" colspan="2"><input class="submit" type="submit" name="settingsubmit" value="<?= $lang['textsubmitchanges'] ?>" /></td>
</tr>
</table>
</td>
</tr>
</table>
</form>
</td>
</tr>
