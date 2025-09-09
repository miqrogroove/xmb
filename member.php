<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-3
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

namespace XMB;

use RuntimeException;

require './header.php';

$core = Services\core();
$db = Services\db();
$email = Services\email();
$forums = Services\forums();
$passMan = Services\password();
$session = Services\session();
$smile = Services\smile();
$sql = Services\sql();
$template = Services\template();
$theme = Services\theme();
$token = Services\token();
$tran = Services\translation();
$validate = Services\validate();
$vars = Services\vars();
$lang = &$vars->lang;
$SETTINGS = &$vars->settings;

$action = getPhpInput('action', sourcearray: 'g');
$softErrors = '';

switch ($action) {
    case 'reg':
        $core->nav($lang['textregister']);
        if ($vars->settings['subject_in_title'] == 'on') {
            $template->threadSubject = $vars->lang['textregister'] . ' - ';
        }
        break;
    case 'viewpro':
        $core->nav($lang['textviewpro']);
        if ($vars->settings['subject_in_title'] == 'on') {
            $template->threadSubject = $vars->lang['textviewpro'] . ' - ';
        }
        break;
    default:
        header('HTTP/1.0 404 Not Found');
        $core->error($lang['textnoaction']);
        break;
}

switch ($action) {
    case 'reg':
        $steps = [
            1 => 'intro',
            2 => 'captcha',
            3 => 'coppa',
            4 => 'rules',
            5 => 'profile',
            6 => 'done',
        ];
        $stepin = formInt('step');
        $stepout = $stepin + 1;
        $testname = 'regtest';
        $testval = 'xmb';
        $cookietest = getPhpInput($testname, sourcearray: 'c');
        $regvalid = true;

        $https_only = 'on' == $SETTINGS['images_https_only'];
        $js_https_only = $https_only ? 'true' : 'false';

        if ('off' == $SETTINGS['regstatus']) {
            header('HTTP/1.0 403 Forbidden');
            $memberpage = $template->process('misc_feature_notavailable.php');
            $regvalid = false;
        } elseif (X_MEMBER) {
            $memberpage = $template->process('misc_feature_not_while_loggedin.php');
            $regvalid = false;
        } elseif ($cookietest != $testval) {
            $core->put_cookie($testname, $testval);
            if ($stepin > 0) {
                $core->error($lang['cookies_disabled']);
            }
        } elseif (! $core->coppa_check()) {
            // User previously attempted registration with age < 13.
            $core->message($lang['coppa_fail']);
        }

        if ($regvalid) {
            // Validate step #
            switch ($stepin) {
                case 0:
                    // First hit should be a GET with no token expected.
                    break;
                case 1:
                    // Check if Javascript is working.
                    if ('on' == $SETTINGS['google_captcha']) {
                        $jsToken = getPhpInput('jscheck', sourcearray: 'c');
                        if (! $token->consume($jsToken, 'Registration', 'jscheck')) {
                            $core->error($lang['bad_token']);
                        }
                    }
                    // no-break
                case 2:
                case 3:
                case 4:
                case 5:
                    // Require validation of anonymous tokens starting with the intro page submission to guarantee the user didn't skip a step.

                    // Due to the anonymous nature of a registration request, we need to check both the form integrity and the cookie integrity.
                    $cookieToken = getPhpInput('register', sourcearray: 'c');
                    $postToken = getPhpInput('token');
                    
                    if ($cookieToken != $postToken) $core->error($lang['bad_token']);
                    
                    $core->request_secure('Registration', (string) $stepin);
                    break;
                default:
                    // Step value was invalid.
                    $core->error($lang['bad_request']);
            }

            // Validate inputs
            switch ($stepin) {
                case 0:
                case 1:
                    // First hit and intro page submission, nothing to validate yet.
                    break;
                case 2:
                    if ('on' == $SETTINGS['google_captcha']) {
                        // Check Google's results
                        $response = getPhpInput('g-recaptcha-response');
                        $ssl_lib = ROOT . 'trust.pem';
                        $installed = time() < 2097705600; // PEM expires 2036-06-21 and after that it won't be used until updated.
                        $curl = curl_init('https://www.google.com/recaptcha/api/siteverify');

                        curl_setopt_array($curl, [
                            CURLOPT_CAINFO => $ssl_lib,
                            CURLOPT_SSL_VERIFYPEER => $installed,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_TIMEOUT => 5,
                            CURLOPT_USERAGENT => ini_get('user_agent'),
                            CURLOPT_POST => 1,
                        ]);

                        $siteverify = [
                            'secret'   => $SETTINGS['google_captcha_secret'],
                            'response' => $response,
                            'remoteip' => $vars->onlineip,
                        ];

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
                            $decoded = json_decode($raw_result, associative: true);
                            if (arrayCoalesce($decoded, 'success') === true && arrayCoalesce($decoded, 'action') === 'register') {
                                if (isset($decoded['score'])) {
                                    // This is API v3.
                                    if ($decoded['score'] >= 0.5) {
                                        $success = true;
                                    }
                                } else {
                                    // This is API v2.
                                    $success = true;
                                }
                            }
                        }
                        if (! $success) {
                            $core->error($lang['google_captcha_fail']);
                        }
                    } elseif ('on' == $SETTINGS['captcha_status'] && 'on' == $SETTINGS['captcha_reg_status']) {
                        // Check XMB's results
                        $Captcha = new Captcha($core, $vars);
                        if (! $Captcha->bCompatible) throw new RuntimeException('XMB captcha is enabled but not working.');
                        $imghash = getPhpInput('imghash');
                        $imgcode = getPhpInput('imgcode');
                        if ($Captcha->ValidateCode($imgcode, $imghash) !== true) {
                            $core->error($lang['captchaimageinvalid']);
                        }
                    } else {
                        $core->error($lang['bad_request']);
                    }
                    break;
                case 3:
                    if ('on' == $SETTINGS['coppa']) {
                        // Check coppa results
                        $age = formInt('age');
                        if ($age <= 0) {
                            $softErrors .= $core->softerror($lang['input_missing']);
                        } elseif ($age < 13) {
                            $core->put_cookie('privacy', 'xmb');
                            $core->message($lang['coppa_fail']);
                        }
                    } else {
                        $core->error($lang['bad_request']);
                    }
                    break;
                case 4:
                    // Check rules results
                    if (noSubmit('rulesubmit')) {
                        $core->error($lang['bad_request']);
                    }
                    break;
                case 5:
                    // Check profile results
                    $self = [];
                    $form = new UserEditForm($self, $self, $core, $db, $sql, $theme, $tran, $validate, $vars);
                    $form->readBirthday();
                    $form->readCallables();
                    $form->readOptions();
                    $form->readNumericFields();
                    $form->readMiscFields();

                    if ('on' == $SETTINGS['regoptional']) {
                        $form->readOptionalFields();
                    }
                    
                    $self = $form->getEdits();

                    $self['username'] = trim($validate->postedVar('username', dbescape: false));

                    if (strlen($self['username']) < $vars::USERNAME_MIN_LENGTH || strlen($self['username']) > $vars::USERNAME_MAX_LENGTH) {
                        $softErrors .= $core->softerror($lang['username_length_invalid']);
                        $self['username'] = substr($self['username'], 0, $vars::USERNAME_MAX_LENGTH);
                    } elseif (! $core->usernameValidation(getRawString('username'))) {
                        $softErrors .= $core->softerror($lang['restricted']);
                    }

                    if ($SETTINGS['ipreg'] != 'off') {
                        $time = $vars->onlinetime - 86400;
                        if ($sql->countMembersByRegIP($vars->onlineip, $time) >= 1) {
                            $core->error($lang['reg_today']);
                        }
                    }

                    $self['email'] = $validate->postedVar('email', dbescape: false);
                    $sql_email = $db->escape($self['email']);
                    if ($SETTINGS['doublee'] == 'off' && false !== strpos($self['email'], "@")) {
                        $email2 = "OR email = '$sql_email'";
                    } else {
                        $email2 = '';
                    }

                    $sql_user = $db->escape($self['username']);
                    $query = $db->query("SELECT username FROM " . $vars->tablepre . "members WHERE username = '$sql_user' $email2");
                    if ($member = $db->fetch_array($query)) {
                        $softErrors .= $core->softerror($lang['alreadyreg']);
                    }
                    $db->free_result($query);

                    $postcount = $db->result($db->query("SELECT COUNT(*) FROM " . $vars->tablepre . "posts WHERE author = '$sql_user'"));
                    if (intval($postcount) > 0) {
                        $softErrors .= $core->softerror($lang['alreadyreg']);
                    }

                    if ($SETTINGS['emailcheck'] == 'on') {
                        $newPass = $passMan->generate();
                    } else {
                        $result = assertPasswordPolicy('password', 'password2', softError: true);
                        $newPass = $result['password'];
                        $softErrors .= $result['error'];
                        unset($result);
                    }
                    if ($newPass !== '') {
                        $self['password2'] = $passMan->hash($newPass);
                    }

                    if (! $core->checkNameRestrictions(rawHTML($self['email']))) {
                        $softErrors .= $core->softerror($lang['emailrestricted']);
                    }

                    $test = new EmailAddressValidator();
                    $rawemail = getPhpInput('email');
                    if (! $test->isValid($rawemail)) {
                        $softErrors .= $core->softerror($lang['bademail']);
                    }

                    if ($softErrors === '') {
                        $count1 = $sql->countMembers();
                        $self['status'] = ($count1 != 0) ? 'Member' : 'Super Administrator';

                        $self['regdate'] = $vars->onlinetime;
                        if (strlen($vars->onlineip) > 39) {
                            $self['regip'] = '';
                        } else {
                            $self['regip'] = $vars->onlineip;
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
                                    $sql->addU2U(
                                        to: $admin['username'],
                                        from: $SETTINGS['bbname'],
                                        type: 'incoming',
                                        owner: $admin['username'],
                                        folder: 'Inbox',
                                        subject: $translate['textnewmember'],
                                        message: $translate['textnewmember2'],
                                        isRead: 'no',
                                        isSent: 'yes',
                                        timestamp: $vars->onlinetime,
                                    );
                                } else {
                                    $adminemail = rawHTML($admin['email']);
                                    $body = $translate['textnewmember2'] . "\n\n" . $vars->full_url;
                                    $email->send($adminemail, $translate['textnewmember'], $body, $translate['charset']);
                                }
                            }
                        }

                        if ($SETTINGS['emailcheck'] == 'on') {
                            $translate = $lang2[$self['langfile']];
                            $username = trim(getPhpInput('username'));
                            $rawbbname = rawHTML($SETTINGS['bbname']);
                            $subject = decimalEntityStrip("[$rawbbname] {$translate['textyourpw']}");
                            $body = "{$translate['textyourpwis']} \n\n{$translate['textusername']} $username\n{$translate['textpassword']} $newPass\n\n" . $vars->full_url;
                            $email->send($rawemail, $subject, $body, $translate['charset']);
                        } else {
                            $session->newUser($self);
                        }
                    }
                    unset($newPass);
                    break;
            }

            // Generate form outputs
            if ($softErrors !== '') $stepout--;

            $template->stepout = $stepout;
            
            if (1 == $stepout) {
                // Every step except 'done' will require new tokens.
                $template->token = $token->create('Registration', (string) $stepout, $vars::NONCE_FORM_EXP, anonymous: true);
                $core->put_cookie('register', $template->token, time() + $vars::NONCE_FORM_EXP);

                // For step 1, also check if Javascript is working.
                if ('on' == $SETTINGS['google_captcha']) {
                    $jsToken = $token->create('Registration', 'jscheck', $vars::NONCE_FORM_EXP, anonymous: true);
                    $js = $core->jsCookie('jscheck', $jsToken);
                    $template->css .= "<script type='text/javascript'>$js</script>\n";
                }

                $memberpage = $template->process('member_reg_intro.php');
            }

            if (2 == $stepout) {
                if ('on' == $SETTINGS['google_captcha']) {
                    // Every step except 'done' will require new tokens.
                    $template->token = $token->create('Registration', (string) $stepout, $vars::NONCE_FORM_EXP, anonymous: true);
                    $core->put_cookie('register', $template->token, time() + $vars::NONCE_FORM_EXP);

                    $keyType = $SETTINGS['google_captcha_type'] ?? 'checkbox';
                    $template->invisible = $keyType !== 'checkbox';

                    $memberpage = $template->process('member_reg_gcaptcha.php');
                } elseif ('on' == $SETTINGS['captcha_status'] && 'on' == $SETTINGS['captcha_reg_status']) {
                    // Display XMB captcha.
                    $template->casesense = '';
                    $template->imghash = '';
                    if ('on' == $SETTINGS['captcha_code_casesensitive']) {
                        $template->casesense = "<p>{$lang['captchacaseon']}</p>";
                    }
                    $Captcha = new Captcha($core, $vars);
                    if (! $Captcha->bCompatible) throw new RuntimeException('XMB captcha is enabled but not working.');
                    $template->imghash = $Captcha->GenerateCode();

                    // Every step except 'done' will require new tokens.
                    $template->token = $token->create('Registration', (string) $stepout, $vars::NONCE_FORM_EXP, anonymous: true);
                    $core->put_cookie('register', $template->token, time() + $vars::NONCE_FORM_EXP);

                    $memberpage = $template->process('member_reg_captcha.php');
                } else {
                    // Skip the captcha step
                    $stepout++;
                    $template->stepout = $stepout;
                }
            }

            if (3 == $stepout) {
                if ((int) $SETTINGS['pruneusers'] > 0) {
                    $prunebefore = $vars->onlinetime - (60 * 60 * 24 * $SETTINGS['pruneusers']);
                    $db->query("DELETE FROM " . $vars->tablepre . "members WHERE lastvisit = 0 AND regdate < $prunebefore AND status = 'Member'");
                }

                if ((int) $SETTINGS['maxdayreg'] > 0) {
                    $time = $vars->onlinetime - 86400; // subtract 24 hours
                    $query = $db->query("SELECT COUNT(uid) FROM " . $vars->tablepre . "members WHERE regdate > $time");
                    if ((int) $db->result($query, 0) > (int) $SETTINGS['maxdayreg']) {
                        $core->error($lang['max_regs']);
                    }
                    $db->free_result($query);
                }

                if ('on' == $SETTINGS['coppa']) {
                    // Display COPPA
                    $template->optionlist = "<option value='0'></option>\n";
                    for ($i = 1; $i <= 120; $i++) {
                        $template->optionlist .= "<option value='$i'>$i</option>\n";
                    }

                    // Every step except 'done' will require new tokens.
                    $template->token = $token->create('Registration', (string) $stepout, $vars::NONCE_FORM_EXP, anonymous: true);
                    $core->put_cookie('register', $template->token, time() + $vars::NONCE_FORM_EXP);

                    $memberpage = $template->process('member_coppa.php');
                } else {
                    // Skip COPPA
                    $stepout++;
                    $template->stepout = $stepout;
                }
            }

            if (4 == $stepout) {
                if ('on' == $SETTINGS['bbrules']) {
                    // Display the rules form
                    $template->rules = nl2br(rawHTML($vars->settings['bbrulestxt']));

                    // Every step except 'done' will require new tokens.
                    $template->token = $token->create('Registration', (string) $stepout, $vars::NONCE_FORM_EXP, anonymous: true);
                    $core->put_cookie('register', $template->token, time() + $vars::NONCE_FORM_EXP);

                    $memberpage = $template->process('member_reg_rules.php');
                } else {
                    // Skip rules
                    $stepout++;
                    $template->stepout = $stepout;
                }
            }

            if (5 == $stepout) {
                // Display new user form
                if ($softErrors === '') {
                    $self = [];
                }
                $form = new UserEditForm($self, $self, $core, $db, $sql, $theme, $tran, $validate, $vars);
                $form->setOptions();
                $form->setCallables();
                $form->setBirthday();
                $form->setNumericFields();
                $form->setMiscFields();
                if ($SETTINGS['regoptional'] == 'on') {
                    $form->setOptionalFields();
                }

                $subTemplate = $form->getTemplate();

                if ($softErrors === '') {
                    $subTemplate->email = '';
                    $subTemplate->username = '';
                } else {
                    $subTemplate->email = $self['email'];
                    $subTemplate->username = $self['username'];
                }

                if ($SETTINGS['emailcheck'] == 'off') {
                    $subTemplate->pwmin = $passMan::MIN_LENGTH;
                    $subTemplate->pwmax = $passMan::MAX_LENGTH;
                    $subTemplate->pwtd = $subTemplate->process('member_reg_password.php');
                } else {
                    $subTemplate->pwtd = '';
                }

                if ($SETTINGS['sigbbcode'] == 'on') {
                    $subTemplate->bbcodeis = $lang['texton'];
                } else {
                    $subTemplate->bbcodeis = $lang['textoff'];
                }

                $subTemplate->htmlis = $lang['textoff'];

                if ($SETTINGS['regoptional'] == 'on') {
                    $subTemplate->regoptional = $subTemplate->process('member_reg_optional.php');
                } else {
                    $subTemplate->regoptional = '';
                }

                $currdate = gmdate($vars->timecode, $core->standardTime($vars->onlinetime));
                $subTemplate->textoffset = str_replace('$currdate', $currdate, $lang['evaloffset']);

                $subTemplate->stepout = $stepout;

                // Every step except 'done' will require new tokens.
                $subTemplate->token = $token->create('Registration', (string) $stepout, $vars::NONCE_FORM_EXP, anonymous: true);
                $core->put_cookie('register', $subTemplate->token, time() + $vars::NONCE_FORM_EXP);

                $memberpage = $subTemplate->process('member_reg.php');
            }

            if (6 == $stepout) {
                // Display success message
                $core->put_cookie('register');
                if ('on' == $SETTINGS['emailcheck']) {
                    $memberpage = $core->message($lang['emailpw']);
                } else {
                    $memberpage = $core->message($lang['regged'], redirect: $vars->full_url);
                }
            }
        }

        $header = $template->process('header.php');

        break;

    case 'viewpro':
        $member = $validate->postedVar('member', dbescape: false, sourcearray: 'g');
        if (strlen($member) < $vars::USERNAME_MIN_LENGTH || strlen($member) > $vars::USERNAME_MAX_LENGTH) {
            header('HTTP/1.0 404 Not Found');
            $core->error($lang['nomember']);
        }

        $memberinfo = $sql->getMemberByName($member);

        if (empty($memberinfo) || ('on' == $SETTINGS['hide_banned'] && 'Banned' == $memberinfo['status'] && ! X_ADMIN)) {
            header('HTTP/1.0 404 Not Found');
            $core->error($lang['nomember']);
        }

        $header = $template->process('header.php');

        $memberinfo['email'] = '';
        
        $template->username = $memberinfo['username'];
        $template->postnum = $memberinfo['postnum'];

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

            $rank = $db->fetch_array($db->query("SELECT * FROM " . $vars->tablepre . "ranks WHERE $limit ORDER BY posts DESC LIMIT 1"));
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

        $encodeuser = recodeOut($memberinfo['username']);
        if (X_GUEST) {
            $template->memberlinks = '';
        } else {
            $template->memberlinks = " <small>(<a href='" . $vars->full_url . "u2u.php?action=send&amp;username=$encodeuser' onclick='Popup(this.href, \"Window\", 700, 450); return false;'>{$lang['textu2u']}</a>)&nbsp;&nbsp;(<a href='" . $vars->full_url . "buddy.php?action=add&amp;buddys=$encodeuser' onclick='Popup(this.href, \"Window\", 450, 400); return false;'>{$lang['addtobuddies']}</a>)</small>";
        }

        $daysreg = ($vars->onlinetime - (int) $memberinfo['regdate']) / (24*3600);
        if ($daysreg > 1) {
            $template->ppd = round($memberinfo['postnum'] / $daysreg, 2);
        } else {
            $template->ppd = $memberinfo['postnum'];
        }

        $template->regdate = $core->printGmDate($core->timeKludge((int) $memberinfo['regdate']));

        $template->site = format_member_site($memberinfo['site']);

        $rank['avatarrank'] = trim($rank['avatarrank']);
        $memberinfo['avatar'] = trim($memberinfo['avatar']);

        if ($rank['avatarrank'] !== '') {
            $rank['avatarrank'] = "<img src='{$rank['avatarrank']}' alt='{$lang['altavatar']}' border='0' />";
        }
        
        $template->avatarrank = $rank['avatarrank'];

        if ('on' == $SETTINGS['images_https_only'] && strpos($memberinfo['avatar'], ':') !== false && substr($memberinfo['avatar'], 0, 6) !== 'https:') {
            $memberinfo['avatar'] = '';
        }

        if ($memberinfo['avatar'] !== '') {
            $memberinfo['avatar'] = '<img src="'.$memberinfo['avatar'].'" alt="'.$lang['altavatar'].'" border="0" />';
        }

        if (($rank['avatarrank'] || $memberinfo['avatar']) && $template->site != '') {
            if ($memberinfo['avatar'] !== '') {
                $template->newsitelink = "<a href='" . $template->site . "' onclick='window.open(this.href); return false;'>{$memberinfo['avatar']}</a></td>";
            } else {
                $template->newsitelink = '';
            }
        } else {
            $template->newsitelink = $memberinfo['avatar'];
        }

        $template->showtitle = rawHTML($rank['title']);
        $template->stars = str_repeat('<img src="' . $vars->full_url . $vars->theme['imgdir'] . '/star.gif" alt="*" border="0" />', (int) $rank['stars']);

        if ($memberinfo['customstatus'] != '') {
            $template->customstatus = '<br />' . rawHTML($memberinfo['customstatus']);
        } else {
            $template->customstatus = '';
        }

        if (! ((int) $memberinfo['lastvisit'] > 0)) {
            $template->lastmembervisittext = $lang['textpendinglogin'];
        } else {
            $adjStamp = $core->timeKludge((int) $memberinfo['lastvisit']);
            $lastvisitdate = $core->printGmDate($adjStamp);
            $lastvisittime = gmdate($vars->timecode, $adjStamp);
            $template->lastmembervisittext = "$lastvisitdate {$lang['textat']} $lastvisittime";
        }

        $posts = $sql->countPosts();

        $posttot = $posts;
        if ($posttot == 0) {
            $template->percent = '0';
        } else {
            $percent = $memberinfo['postnum'] * 100 / $posttot;
            $template->percent = round($percent, 2);
        }

        $template->bio = nl2br($core->rawHTMLsubject($memberinfo['bio']));

        if (X_SADMIN) {
            $template->admin_edit = "<br />{$lang['adminoption']} <a href='./editprofile.php?user=$encodeuser'>{$lang['admin_edituseraccount']}</a>";
        } else {
            $template->admin_edit = '';
        }

        if ($memberinfo['mood'] != '') {
            $template->mood = $core->postify(
                message: $memberinfo['mood'],
                allowimgcode: 'no',
                ignorespaces: true,
                ismood: 'yes',
            );
        } else {
            $template->mood = '';
        }

        $template->location = $core->rawHTMLsubject($memberinfo['location']);

        if ($memberinfo['bday'] === iso8601_date(0,0,0)) {
            $template->bday = $lang['textnone'];
        } else {
            $template->bday = $core->printGmDate(
                MakeTime(
                    12,
                    0,
                    0,
                    (int) substr($memberinfo['bday'], 5, 2),
                    (int) substr($memberinfo['bday'], 8, 2),
                    (int) substr($memberinfo['bday'], 0, 4),
                ),
            );
        }

        // Forum most active in
        $fids = implode(',', $core->permittedFIDsForThreadView());
        if (strlen($fids) > 0) {
            $query = $db->query(
                "SELECT fid, COUNT(*) AS posts
                 FROM " . $vars->tablepre . "posts
                 WHERE author = '$member' AND fid IN ($fids)
                 GROUP BY fid
                 ORDER BY posts DESC
                 LIMIT 1"
            );
            $found = ($db->num_rows($query) == 1);
        } else {
            $found = false;
        }

        if ($found) {
            $row = $db->fetch_array($query);
            $posts = $row['posts'];
            $forum = $forums->getForum((int) $row['fid']);
            $template->topforum = "<a href='" . $vars->full_url . "forumdisplay.php?fid={$forum['fid']}'>" . fnameOut($forum['name']) . "</a> ($posts {$lang['memposts']}) [" . round(($posts/$memberinfo['postnum'])*100, 1) . "% {$lang['textoftotposts']}]";
        } else {
            $template->topforum = $lang['textnopostsyet'];
        }

        // Last post
        if (strlen($fids) > 0) {
            $pq = $db->query(
                "SELECT p.tid, t.subject, p.dateline, p.pid
                 FROM " . $vars->tablepre . "posts AS p
                 INNER JOIN " . $vars->tablepre . "threads AS t USING (tid)
                 WHERE p.author='$member' AND p.fid IN ($fids)
                 ORDER BY p.dateline DESC
                 LIMIT 1"
            );
            $lpfound = ($db->num_rows($pq) == 1);
        } else {
            $lpfound = false;
        }
        if ($lpfound) {
            $post = $db->fetch_array($pq);

            $adjStamp = $core->timeKludge((int) $post['dateline']);
            $lastpostdate = $core->printGmDate($adjStamp);
            $lastposttime = gmdate($vars->timecode, $adjStamp);
            $lastposttext = $lastpostdate.' '.$lang['textat'].' '.$lastposttime;
            $lpsubject = $core->rawHTMLsubject($post['subject']);
            $template->lastpost = "<a href='" . $vars->full_url . "viewthread.php?tid={$post['tid']}&amp;goto=search&amp;pid={$post['pid']}'>$lpsubject</a> ($lastposttext)";
        } else {
            $template->lastpost = $lang['textnopostsyet'];
        }

        $url = $vars->full_url . 'search.php?srchuname=' . recodeOut($memberinfo['username']) . '&amp;searchsubmit=a';
        $template->postSearchLink = str_replace('$url', $url, $lang['searchusermsg']);
        
        $memberpage = $template->process('member_profile.php');
        break;

    default:
        $core->error($lang['textnoaction']);
        break;
}

$template->footerstuff = $core->end_time();
$footer = $template->process('footer.php');
echo $header, $softErrors, $memberpage, $footer;
