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

$core = \XMB\Services\core();
$forums = \XMB\Services\forums();
$session = \XMB\Services\session();
$smile = \XMB\Services\smile();
$sql = \XMB\Services\sql();
$theme = \XMB\Services\theme();
$tran = \XMB\Services\translation();
$vars = \XMB\Services\vars();

define('X_SCRIPT', 'member.php');

require 'header.php';

loadtemplates(
'member_coppa',
'member_reg_rules',
'member_reg_password',
'member_reg_avatarurl',
'member_reg_avatarlist',
'member_reg',
'member_reg_optional',
'member_reg_captcha',
'member_reg_gcaptcha',
'member_profile_email',
'member_profile',
'misc_feature_not_while_loggedin',
'misc_feature_notavailable',
'timezone_control'
);

$action = postedVar('action', '', FALSE, FALSE, FALSE, 'g');
switch($action) {
    case 'reg':
        nav($lang['textregister']);
        break;
    case 'viewpro':
        nav($lang['textviewpro']);
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        error($lang['textnoaction']);
        break;
}

switch($action) {
    case 'reg':
        $steps = [
            1 => 'captcha',
            2 => 'coppa',
            3 => 'rules',
            4 => 'profile',
            5 => 'done',
        ];
        $stepin = formInt('step');
        $stepout = $stepin + 1;
        $testname = 'regtest';
        $testval = 'xmb';
        $cookietest = postedVar($testname, '', false, false, false, 'c');
        $regvalid = true;

        $https_only = 'on' == $SETTINGS['images_https_only'];
        $js_https_only = $https_only ? 'true' : 'false';

        if ('off' == $SETTINGS['regstatus']) {
            header('HTTP/1.0 403 Forbidden');
            eval('$memberpage = "'.template('misc_feature_notavailable').'";');
            $regvalid = false;
        } elseif (X_MEMBER) {
            eval('$memberpage = "'.template('misc_feature_not_while_loggedin').'";');
            $regvalid = false;
        } elseif ($cookietest != $testval) {
            put_cookie($testname, $testval);
            if ($stepin > 0) {
                error($lang['cookies_disabled']);
            }
        } elseif (! coppa_check()) {
            // User previously attempted registration with age < 13.
            message($lang['coppa_fail']);
        }

        if ($regvalid) {
            // Validate step #
            switch ($stepin) {
                case 0:
                case 1:
                    // First step and captcha step don't require a token.
                    break;
                case 2:
                case 3:
                case 4:
                    // Other steps will always include a nonce in their forms to guarantee the user didn't skip a step.
                    request_secure('Registration', (string) $stepin, 0, true);
                    break;
                default:
                    // Step value was invalid.
                    error($lang['bad_request']);
            }

            // Validate inputs
            switch ($stepin) {
                case 0:
                    // First hit, nothing to validate yet.
                    break;
                case 1:
                    if ('on' == $SETTINGS['google_captcha']) {
                        // Check Google's results
                        $response = postedVar('g-recaptcha-response', '', false, false);
                        $ssl_lib = XMB_ROOT.'trust.pem';
                        $installed = time() < 2097705600; // Expires 2036-06-21 and won't be used until updated.
                        $curl = curl_init('https://www.google.com/recaptcha/api/siteverify');
                        $agent = $versionshort == '' ? 'XMB' : "XMB/$versionshort";

                        curl_setopt_array($curl, array(
                            CURLOPT_CAINFO => $ssl_lib,
                            CURLOPT_SSL_VERIFYPEER => $installed,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_TIMEOUT => 5,
                            CURLOPT_USERAGENT => ini_get('user_agent'),
                            CURLOPT_POST => 1
                        ));

                        $siteverify = array(
                            'secret'   => $SETTINGS['google_captcha_secret'],
                            'response' => $response,
                            'remoteip' => $onlineip,
                        );

                        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($siteverify));

                        // Fetch the confirmation.
                        $count = 1;
                        $limit = 2;
                        $raw_result = curl_exec($curl);
                        while (false === $raw_result && $count <= $limit) {
                            // Some transient errors tend to occur.
                            if ($count >= $limit) {
                                // This should be rare.
                                $errorno = curl_errno($curl);
                                $errormsg = curl_error($curl);
                                trigger_error("Unable to contact reCAPTCHA API after $limit attempts.  cURL error $errorno: $errormsg", E_USER_WARNING);
                                break;
                            }

                            sleep(2);
                            $count++;
                            $raw_result = curl_exec($curl);
                        }
                        $success = false;
                        if (false !== $raw_result) {
                            $decoded = json_decode($raw_result, true);
                            if (! empty($decoded['success'])) {
                                if (true === $decoded['success']) {
                                    $success = true;
                                }
                            }
                        }
                        if (! $success) {
                            error($lang['google_captcha_fail']);
                        }
                    } elseif ('on' == $SETTINGS['captcha_status'] && 'on' == $SETTINGS['captcha_reg_status']) {
                        // Check XMB's results
                        require XMB_ROOT.'include/captcha.inc.php';
                        $Captcha = new Captcha();
                        if (! $Captcha->bCompatible) throw new RuntimeException('XMB captcha is enabled but not working.');
                        $imghash = postedVar('imghash', '', FALSE, TRUE);
                        $imgcode = postedVar('imgcode', '', FALSE, FALSE);
                        if ($Captcha->ValidateCode($imgcode, $imghash) !== true) {
                            error($lang['captchaimageinvalid']);
                        }
                    } else {
                        error($lang['bad_request']);
                    }
                    break;
                case 2:
                    if ('on' == $SETTINGS['coppa']) {
                        // Check coppa results
                        $age = formInt('age');
                        if ($age <= 0) {
                            // Input was invalid, try again.
                            $stepout = 2;
                        } elseif ($age < 13) {
                            put_cookie('privacy', 'xmb');
                            message($lang['coppa_fail']);
                        }
                    } else {
                        error($lang['bad_request']);
                    }
                    break;
                case 3:
                    // Check rules results
                    if (noSubmit('rulesubmit')) {
                        error($lang['bad_request']);
                    }
                    break;
                case 4:
                    // Check profile results
                    $self = [];
                    $self['username'] = trim(postedVar('username', '', TRUE, FALSE));

                    if (strlen($self['username']) < $vars::USERNAME_MIN_LENGTH || strlen($self['username']) > $vars::USERNAME_MAX_LENGTH) {
                        error($lang['username_length_invalid']);
                    }

                    // Duplicates some logic in admin.inc.php.
                    $nonprinting = '\\x00-\\x1F\\x7F';  //Universal chars that are invalid.
                    $specials = '\\]\'<>\\\\|"[,@';  //Other universal chars disallowed by XMB: []'"<>\|,@
                    $sequences = '|  ';  //Phrases disallowed, each separated by '|'
                    $icharset = strtoupper($charset);
                    if (substr($icharset, 0, 8) == 'ISO-8859') {
                        if ($icharset == 'ISO-8859-11') {
                            $nonprinting .= '-\\x9F\\xDB-\\xDE\\xFC-\\xFF';  //More chars invalid for the Thai set.
                        } else {
                            $nonprinting .= '-\\x9F\\xAD';  //More chars invalid for all ISO 8859 sets except Part 11 (Thai).
                        }
                    } elseif (substr($icharset, 0, 11) == 'WINDOWS-125') {
                        $nonprinting .= '\\xAD';  //More chars invalid for all Windows code pages.
                    }

                    if ($_POST['username'] !== preg_replace("#[{$nonprinting}{$specials}]{$sequences}#", '', $_POST['username'])) {
                        error($lang['restricted']);
                    }

                    if ($SETTINGS['ipreg'] != 'off') {
                        $time = $vars->onlinetime - 86400;
                        $query = $db->query("SELECT uid FROM ".X_PREFIX."members WHERE regip='$onlineip' AND regdate >= $time");
                        if ($db->num_rows($query) >= 1) {
                            error($lang['reg_today']);
                        }
                        $db->free_result($query);
                    }

                    $self['email'] = postedVar('email', 'javascript', true, false, true);
                    $sql_email = $db->escape($self['email']);
                    if ($SETTINGS['doublee'] == 'off' && false !== strpos($self['email'], "@")) {
                        $email1 = ", email";
                        $email2 = "OR email='$sql_email'";
                    } else {
                        $email1 = '';
                        $email2 = '';
                    }

                    $sql_user = $db->escape($self['username']);
                    $query = $db->query("SELECT username$email1 FROM ".X_PREFIX."members WHERE username='$sql_user' $email2");
                    if ($member = $db->fetch_array($query)) {
                        $db->free_result($query);
                        error($lang['alreadyreg']);
                    }

                    $postcount = $db->result($db->query("SELECT COUNT(*) FROM ".X_PREFIX."posts WHERE author='$sql_user'"), 0);
                    if (intval($postcount) > 0) {
                        error($lang['alreadyreg']);
                    }

                    if ($SETTINGS['emailcheck'] == 'on') {
                        $newPass = '';
                        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
                        $get = strlen($chars) - 1;
                        for($i = 0; $i < 10; $i++) {
                            $newPass .= $chars[random_int(0, $get)];
                        }
                    } else {
                        $newPass = $core->assertPasswordPolicy('password', 'password2');
                    }
                    $passMan = new \XMB\Password($sql);
                    $self['password2'] = $passMan->hashPassword($newPass);

                    $fail = false;
                    $efail = false;
                    $query = $db->query("SELECT * FROM ".X_PREFIX."restricted");
                    while($restriction = $db->fetch_array($query)) {
                        $t_username = $self['username'];
                        $t_email = $self['email'];
                        if ('0' === $restriction['case_sensitivity']) {
                            $t_username = strtolower($t_username);
                            $t_email = strtolower($t_email);
                            $restriction['name'] = strtolower($restriction['name']);
                        }

                        if ('1' === $restriction['partial']) {
                            if (strpos($t_username, $restriction['name']) !== false) {
                                $fail = true;
                            }

                            if (strpos($t_email, $restriction['name']) !== false) {
                                $efail = true;
                            }
                        } else {
                            if ($t_username === $restriction['name']) {
                                $fail = true;
                            }

                            if ($t_email === $restriction['name']) {
                                $efail = true;
                            }
                        }
                    }
                    $db->free_result($query);

                    if ($fail) {
                        error($lang['restricted']);
                    }

                    if ($efail) {
                        error($lang['emailrestricted']);
                    }

                    require XMB_ROOT.'include/validate-email.inc.php';
                    $test = new EmailAddressValidator();
                    $rawemail = postedVar('email', '', FALSE, FALSE);
                    if (false === $test->check_email_address($rawemail)) {
                        error($lang['bademail']);
                    }

                    $form = new \XMB\UserEditForm([], [], $core, $theme, $tran, $vars);
                    $form->readBirthday();
                    $form->readCallables();
                    $form->readOptions();
                    $form->readNumericFields();
                    $form->readMiscFields();

                    $count1 = $sql->countMembers();
                    $self['status'] = ($count1 != 0) ? 'Member' : 'Super Administrator';

                    $self['regdate'] = $vars->onlinetime;
                    if (strlen($onlineip) > 15 && ((int) $SETTINGS['schema_version'] < 9 || strlen($onlineip) > 39)) {
                        $self['regip'] = '';
                    } else {
                        $self['regip'] = $onlineip;
                    }

                    if ('on' == $SETTINGS['regoptional']) {
                        $form->readOptionalFields();
                    }

                    $sql->addMember($self);

                    $lang2 = $tran->loadPhrases([
                        'charset',
                        'textnewmember',
                        'textnewmember2',
                        'textyourpw',
                        'textyourpwis',
                        'textusername',
                        'textpassword',
                    ]);

                    if ($SETTINGS['notifyonreg'] != 'off') {
                        $mailquery = $sql->getSuperEmails();
                        foreach ($mailquery as $admin) {
                            $translate = $lang2[$admin['langfile']];
                            if ($SETTINGS['notifyonreg'] == 'u2u') {
                                $db->query("INSERT INTO ".X_PREFIX."u2u (u2uid, msgto, msgfrom, type, owner, folder, subject, message, dateline, readstatus, sentstatus) VALUES ('', '$admin[username]', '".$db->escape($bbname)."', 'incoming', '$admin[username]', 'Inbox', '$translate[textnewmember]', '$translate[textnewmember2]', '".$vars->onlinetime."', 'no', 'yes')");
                            } else {
                                $adminemail = htmlspecialchars_decode($admin['email'], ENT_QUOTES);
                                $body = "{$translate['textnewmember2']}\n\n$full_url";
                                $core->xmb_mail($adminemail, $translate['textnewmember'], $body, $translate['charset']);
                            }
                        }
                    }

                    if ($SETTINGS['emailcheck'] == 'on') {
                        $translate = $lang2[$langfilenew];
                        $username = trim(postedVar('username', '', FALSE, FALSE));
                        $rawbbname = htmlspecialchars_decode($bbname, ENT_NOQUOTES);
                        $subject = "[$rawbbname] {$translate['textyourpw']}";
                        $body = "{$translate['textyourpwis']} \n\n{$translate['textusername']} $username\n{$translate['textpassword']} $newPass\n\n$full_url";
                        xmb_mail($rawemail, $subject, $body, $translate['charset']);
                    } else {
                        $session->newUser($self);
                    }

                    unset($newPass, $passMan);
                    break;
            }

            // Generate form outputs
            if (1 == $stepout) {
                if ('on' == $SETTINGS['google_captcha']) {
                    // Display reCAPTCHA
                    $css .= "\n<script src='https://www.google.com/recaptcha/api.js' async defer></script>";
                    eval('$memberpage = "'.template('member_reg_gcaptcha').'";');
                } elseif ('on' == $SETTINGS['captcha_status'] && 'on' == $SETTINGS['captcha_reg_status']) {
                    // Display XMB captcha.
                    $casesense = '';
                    $imghash = '';
                    if ('on' == $SETTINGS['captcha_code_casesensitive']) {
                        $casesense = "<p>{$lang['captchacaseon']}</p>";
                    }
                    require_once XMB_ROOT.'include/captcha.inc.php';
                    $Captcha = new Captcha();
                    if (! $Captcha->bCompatible) throw new RuntimeException('XMB captcha is enabled but not working.');
                    $imghash = $Captcha->GenerateCode();
                    eval('$memberpage = "'.template('member_reg_captcha').'";');
                } else {
                    // Skip the captcha step
                    $stepout = 2;
                }
            }

            if (2 == $stepout) {
                if ((int) $SETTINGS['pruneusers'] > 0) {
                    $prunebefore = $vars->onlinetime - (60 * 60 * 24 * $SETTINGS['pruneusers']);
                    $db->query("DELETE FROM ".X_PREFIX."members WHERE lastvisit=0 AND regdate < $prunebefore AND status='Member'");
                }

                if ((int) $SETTINGS['maxdayreg'] > 0) {
                    $time = $vars->onlinetime - 86400; // subtract 24 hours
                    $query = $db->query("SELECT COUNT(uid) FROM ".X_PREFIX."members WHERE regdate > $time");
                    if ((int) $db->result($query, 0) > (int) $SETTINGS['maxdayreg']) {
                        error($lang['max_regs']);
                    }
                    $db->free_result($query);
                }

                if ('on' == $SETTINGS['coppa']) {
                    // Display COPPA
                    $optionlist = "<option value='0'></option>\n";
                    for ($i = 1; $i <= 120; $i++) {
                        $optionlist .= "<option value='$i'>$i</option>\n";
                    }
                    $token = \XMB\Token\create('Registration', (string) $stepout, $vars::NONCE_AYS_EXP, true);
                    eval('$memberpage = "'.template('member_coppa').'";');
                } else {
                    // Skip COPPA
                    $stepout = 3;
                }
            }

            if (3 == $stepout) {
                if ('on' == $SETTINGS['bbrules']) {
                    // Display the rules form
                    $token = \XMB\Token\create('Registration', (string) $stepout, $vars::NONCE_FORM_EXP, true);
                    $SETTINGS['bbrulestxt'] = nl2br($SETTINGS['bbrulestxt']);
                    eval('$memberpage = "'.template('member_reg_rules').'";');
                } else {
                    // Skip rules
                    $stepout = 4;
                }
            }

            if (4 == $stepout) {
                // Display new user form
                $form = new \XMB\UserEditForm([], [], $core, $theme, $tran, $vars);
                $form->setOptionSelectors();
                $form->setCallables();
                $form->setBirthday();
                $form->setNumericFields();
                $form->setMiscFields();

                $subTemplate = $form->getTemplate();

                $token = \XMB\Token\create('Registration', (string) $stepout, $vars::NONCE_FORM_EXP, true);

                $currdate = gmdate($vars->timecode, $core->standardTime($vars->onlinetime));
                $textoffset = str_replace('$currdate', $currdate, $lang['evaloffset']);

                if ($SETTINGS['sigbbcode'] == 'on') {
                    $bbcodeis = $lang['texton'];
                } else {
                    $bbcodeis = $lang['textoff'];
                }

                $htmlis = $lang['textoff'];

                $pwtd = '';
                if ($SETTINGS['emailcheck'] == 'off') {
                    eval('$pwtd = "'.template('member_reg_password').'";');
                }

                $regoptional = '';
                if ($SETTINGS['regoptional'] == 'on') {
                    $form->setOptionalFields();
                    eval('$regoptional = "'.template('member_reg_optional').'";');
                }

                eval('$memberpage = "'.template('member_reg').'";');
            }

            if (5 == $stepout) {
                // Display success message
                if ('on' == $SETTINGS['emailcheck']) {
                    $memberpage = message($lang['emailpw'], false, '', '', false, false, true, false);
                } else {
                    $memberpage = message($lang['regged'], false, '', '', $full_url, false, true, false);
                }
            }
        }

        eval('$header = "'.template('header').'";');

        break;

    case 'viewpro':
        $member = postedVar('member', '', TRUE, FALSE, FALSE, 'g');
        if (strlen($member) < 3 || strlen($member) > 32) {
            header('HTTP/1.0 404 Not Found');
            error($lang['nomember']);
        }

        $memberinfo = $sql->getMemberByName($member);

        if (empty($memberinfo) || ('on' == $SETTINGS['hide_banned'] && 'Banned' == $memberinfo['status'] && ! X_ADMIN)) {
            header('HTTP/1.0 404 Not Found');
            error($lang['nomember']);
        }

        $memberinfo['password'] = '';

        null_string($memberinfo['avatar']);

        $member = $db->escape($member);

        if ($memberinfo['status'] == 'Banned') {
            $memberinfo['avatar'] = '';
            $rank = [
                'title' => 'Banned',
                'posts' => 0,
                'id' => 0,
                'stars' => 0,
                'allowavatars' => 'no',
                'avatarrank' => '',
            ];
        } else {
            if ($memberinfo['status'] == 'Administrator' || $memberinfo['status'] == 'Super Administrator' || $memberinfo['status'] == 'Super Moderator' || $memberinfo['status'] == 'Moderator') {
                $limit = "title = '$memberinfo[status]'";
            } else {
                $limit = "posts <= '$memberinfo[postnum]' AND title != 'Super Administrator' AND title != 'Administrator' AND title != 'Super Moderator' AND title != 'Moderator'";
            }

            $rank = $db->fetch_array($db->query("SELECT * FROM ".X_PREFIX."ranks WHERE $limit ORDER BY posts DESC LIMIT 1"));
            if (null === $rank) {
                $memberinfo['avatar'] = '';
                $rank = [
                    'title' => '',
                    'posts' => 0,
                    'id' => 0,
                    'stars' => 0,
                    'allowavatars' => 'no',
                    'avatarrank' => '',
                ];
            } else {
                null_string($rank['avatarrank']);
            }
        }

        eval('$header = "'.template('header').'";');

        $encodeuser = recodeOut($memberinfo['username']);
        if (X_GUEST) {
            $memberlinks = '';
        } else {
            $memberlinks = " <small>(<a href='u2u.php?action=send&amp;username=$encodeuser' onclick='Popup(this.href, \"Window\", 700, 450); return false;'>{$lang['textu2u']}</a>)&nbsp;&nbsp;(<a href='buddy.php?action=add&amp;buddys=$encodeuser' onclick='Popup(this.href, \"Window\", 450, 400); return false;'>{$lang['addtobuddies']}</a>)</small>";
        }

        $daysreg = ($vars->onlinetime - (int) $memberinfo['regdate']) / (24*3600);
        if ($daysreg > 1) {
            $ppd = $memberinfo['postnum'] / $daysreg;
            $ppd = round($ppd, 2);
        } else {
            $ppd = $memberinfo['postnum'];
        }

        $memberinfo['regdate'] = gmdate($dateformat, core()->timeKludge($memberinfo['regdate']));

        $memberinfo['site'] = format_member_site($memberinfo['site']);
        $site = $memberinfo['site'];

        if (X_MEMBER && $memberinfo['email'] != '' && $memberinfo['showemail'] == 'yes') {
            $email = $memberinfo['email'];
        } else {
            $email = '';
        }

        $rank['avatarrank'] = trim($rank['avatarrank']);
        $memberinfo['avatar'] = trim($memberinfo['avatar']);

        if ($rank['avatarrank'] !== '') {
            $rank['avatarrank'] = '<img src="'.$rank['avatarrank'].'" alt="'.$lang['altavatar'].'" border="0" />';
        }

        if ('on' == $SETTINGS['images_https_only'] && strpos($memberinfo['avatar'], ':') !== false && substr($memberinfo['avatar'], 0, 6) !== 'https:') {
            $memberinfo['avatar'] = '';
        }

        if ($memberinfo['avatar'] !== '') {
            $memberinfo['avatar'] = '<img src="'.$memberinfo['avatar'].'" alt="'.$lang['altavatar'].'" border="0" />';
        }

        if (($rank['avatarrank'] || $memberinfo['avatar']) && $site != '') {
            $sitelink = $site;
            if ($memberinfo['avatar'] !== '') {
                $newsitelink = "<a href='$sitelink' onclick='window.open(this.href); return false;'>{$memberinfo['avatar']}</a></td>";
            } else {
                $newsitelink = '';
            }
        } else {
            $sitelink = "about:blank";
            $newsitelink = $memberinfo['avatar'];
        }

        $showtitle = $rank['title'];
        $stars = str_repeat('<img src="'.$imgdir.'/star.gif" alt="*" border="0" />', $rank['stars']);

        if ($memberinfo['customstatus'] != '') {
            $showtitle = $rank['title'];
            $customstatus = '<br />' . $smile->censor($memberinfo['customstatus']);
        } else {
            $showtitle = $rank['title'];
            $customstatus = '';
        }

        if (! ((int) $memberinfo['lastvisit'] > 0)) {
            $lastmembervisittext = $lang['textpendinglogin'];
        } else {
            $lastvisitdate = gmdate($dateformat, core()->timeKludge($memberinfo['lastvisit']));
            $lastvisittime = gmdate($timecode, core()->timeKludge($memberinfo['lastvisit']));
            $lastmembervisittext = $lastvisitdate.' '.$lang['textat'].' '.$lastvisittime;
        }

        $query = $db->query("SELECT COUNT(*) FROM ".X_PREFIX."posts");
        $posts = (int) $db->result($query, 0);
        $db->free_result($query);

        $posttot = $posts;
        if ($posttot == 0) {
            $percent = '0';
        } else {
            $percent = $memberinfo['postnum']*100/$posttot;
            $percent = round($percent, 2);
        }

        $memberinfo['bio'] = nl2br(rawHTMLsubject($memberinfo['bio']));

        $emailblock = '';
        if ($memberinfo['showemail'] == 'yes') {
            eval('$emailblock = "'.template('member_profile_email').'";');
        }

        if (X_SADMIN) {
            $admin_edit = "<br />{$lang['adminoption']} <a href='./editprofile.php?user=$encodeuser'>{$lang['admin_edituseraccount']}</a>";
        } else {
            $admin_edit = '';
        }

        if ($memberinfo['mood'] != '') {
            $memberinfo['mood'] = postify($memberinfo['mood'], 'no', 'no', 'yes', 'no', 'yes', 'no', true, 'yes');
        } else {
            $memberinfo['mood'] = '';
        }

        $memberinfo['location'] = rawHTMLsubject($memberinfo['location']);

        if ($memberinfo['bday'] === iso8601_date(0,0,0)) {
            $memberinfo['bday'] = $lang['textnone'];
        } else {
            $memberinfo['bday'] = core()->printGmDate(MakeTime(12,0,0,substr($memberinfo['bday'],5,2),substr($memberinfo['bday'],8,2),substr($memberinfo['bday'],0,4)), $dateformat, -$timeoffset);
        }

        // Forum most active in
        $fids = implode(',', $core->permittedFIDsForThreadView());
        if (strlen($fids) > 0) {
            $query = $db->query(
                "SELECT fid, COUNT(*) AS posts
                 FROM ".X_PREFIX."posts
                 WHERE author='$member' AND fid IN ($fids)
                 GROUP BY fid
                 ORDER BY posts DESC
                 LIMIT 1"
            );
            $found = ($db->num_rows($query) == 1);
        } else {
            $found = FALSE;
        }

        if ($found) {
            $row = $db->fetch_array($query);
            $posts = $row['posts'];
            $forum = $forums->getForum((int) $row['fid']);
            $topforum = "<a href='./forumdisplay.php?fid={$forum['fid']}'>".fnameOut($forum['name'])."</a> ($posts {$lang['memposts']}) [".round(($posts/$memberinfo['postnum'])*100, 1)."% {$lang['textoftotposts']}]";
        } else {
            $topforum = $lang['textnopostsyet'];
        }

        // Last post
        if (strlen($fids) > 0) {
            $pq = $db->query(
                "SELECT p.tid, t.subject, p.dateline, p.pid
                 FROM ".X_PREFIX."posts AS p
                 INNER JOIN ".X_PREFIX."threads AS t USING (tid)
                 WHERE p.author='$member' AND p.fid IN ($fids)
                 ORDER BY p.dateline DESC
                 LIMIT 1"
            );
            $lpfound = ($db->num_rows($pq) == 1);
        } else {
            $lpfound = FALSE;
        }
        if ($lpfound) {
            $post = $db->fetch_array($pq);

            $lastpostdate = gmdate($dateformat, core()->timeKludge((int) $post['dateline']));
            $lastposttime = gmdate($timecode, core()->timeKludge((int) $post['dateline']));
            $lastposttext = $lastpostdate.' '.$lang['textat'].' '.$lastposttime;
            $lpsubject = rawHTMLsubject(stripslashes($post['subject']));
            $lastpost = "<a href='./viewthread.php?tid={$post['tid']}&amp;goto=search&amp;pid={$post['pid']}'>$lpsubject</a> ($lastposttext)";
        } else {
            $lastpost = $lang['textnopostsyet'];
        }

        if (X_GUEST && $SETTINGS['captcha_status'] == 'on' && $SETTINGS['captcha_search_status'] == 'on') {
            $lang['searchusermsg'] = '';
        } else {
            $lang['searchusermsg'] = str_replace('*USER*', recodeOut($memberinfo['username']), $lang['searchusermsg']);
        }
        eval('$memberpage = "'.template('member_profile').'";');
        break;

    default:
        error($lang['textnoaction']);
        break;
}

end_time();
eval('$footer = "'.template('footer').'";');
echo $header, $memberpage, $footer;
