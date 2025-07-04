<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-alpha
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

/**
 * Provides common form logic for user self-registration, self-editing, and admin-editing.
 *
 * @since 1.10.00
 */
class UserEditForm
{
    private Template $template;

    private string $formMode;

    private array $edits = [];

    /**
     * @param array @targetUser The record being edited.  May be empty for new users.
     * @param array @editorUser The record of the user doing the editing.  May be empty for new users.
     */
    public function __construct(
        private array $targetUser,
        private array $editorUser,
        private Core $core,
        private DBStuff $db,
        private SQL $sql,
        private ThemeManager $theme,
        private Translation $tran,
        private Validation $validate,
        private Variables $vars,
    ) {
        $this->template = new Template($vars);
        $this->template->addRefs();
        
        if (! isset($targetUser['username'])) {
            $this->formMode = 'new';
        } elseif ($targetUser['username'] === $editorUser['username']) {
            $this->formMode = 'self';
        } else {
            $this->formMode = 'admin';
        }
    }

    public function getTemplate(): Template
    {
        return $this->template;
    }

    public function getEdits(): array
    {
        return $this->edits;
    }

    public function setOptions()
    {
        $template = $this->template;
        $member = &$this->targetUser;
        $vars = $this->vars;

        $template->check12 = '';
        $template->check24 = '';
        $template->u2uasel0 = '';
        $template->u2uasel1 = '';
        $template->u2uasel2 = '';

        if ($this->formMode == 'new') {
            // From template member_reg
            $template->subschecked = '';
            $template->newschecked = $vars::cheHTML;
            $template->ogu2uchecked = $vars::cheHTML;
            $template->eouchecked = '';
            $template->invchecked = '';
            if ('24' === $this->vars->settings['timeformat']) {
                $template->check24 = $vars::cheHTML;
            } else {
                $template->check12 = $vars::cheHTML;
            }

        } else {
            // These first two indicies are not included during registration.
            $subEachPost = $member['sub_each_post'] ?? '';
            $invisible = $member['invisible'] ?? '';
            // From memcp.php
            $template->subschecked = $subEachPost == 'yes' ? $vars::cheHTML : '';
            $template->newschecked = $member['newsletter'] == 'yes' ? $vars::cheHTML : '';
            $template->ogu2uchecked = $member['saveogu2u'] == 'yes' ? $vars::cheHTML : '';
            $template->eouchecked = $member['emailonu2u'] == 'yes' ? $vars::cheHTML : '';
            $template->invchecked = $invisible === '1' ? $vars::cheHTML : '';

            switch ($member['u2ualert']) {
                case '2':
                    $template->u2uasel2 = $vars::selHTML;
                    break;
                case '1':
                    $template->u2uasel1 = $vars::selHTML;
                    break;
                case '0':
                default:
                    $template->u2uasel0 = $vars::selHTML;
            }

            if ('24' === $member['timeformat']) {
                $template->check24 = $vars::cheHTML;
            } else {
                $template->check12 = $vars::cheHTML;
            }
        }
    }

    public function readOptions()
    {
        $timeformatnew = formInt('timeformatnew');
        if ($timeformatnew != 12 && $timeformatnew != 24) {
            $timeformatnew = $this->vars->settings['timeformat'];
        }
        if ($this->formMode == 'new' || $this->targetUser['timeformat'] != $timeformatnew) {
            $this->edits['timeformat'] = $timeformatnew;
        }

        $u2ualert = formInt('u2ualert');
        if ($this->formMode == 'new' || $this->targetUser['u2ualert'] != $u2ualert) {
            $this->edits['u2ualert'] = $u2ualert;
        }

        $newsletter = formYesNo('newsletter');
        if ($this->formMode == 'new' || $this->targetUser['newsletter'] != $newsletter) {
            $this->edits['newsletter'] = $newsletter;
        }

        $saveogu2u = formYesNo('saveogu2u');
        if ($this->formMode == 'new' || $this->targetUser['saveogu2u'] != $saveogu2u) {
            $this->edits['saveogu2u'] = $saveogu2u;
        }

        $emailonu2u = formYesNo('emailonu2u');
        if ($this->formMode == 'new' || $this->targetUser['emailonu2u'] != $emailonu2u) {
            $this->edits['emailonu2u'] = $emailonu2u;
        }


        if ($this->formMode != 'new') {
            $newsubs = formYesNo('newsubs');
            if ($this->targetUser['sub_each_post'] != $newsubs) {
                $this->edits['sub_each_post'] = $newsubs;
            }

            $invisible = getPhpInput('newinv') === '1' ? '1' : '0';
            if ($this->targetUser['invisible'] != $invisible) {
                $this->edits['invisible'] = $invisible;
            }
        }
    }

    public function setCallables()
    {
        $template = $this->template;
        $member = &$this->targetUser;
        
        if ($this->formMode == 'new') {
            $template->timeOffset = $this->vars->settings['def_tz'];
            $theme = null;
            $langfile = $this->vars->settings['langfile'];
        } else {
            $template->timeOffset = $member['timeoffset'];
            $theme = (int) $member['theme'];
            $langfile = $member['langfile'];
        }

        // Some callers will use timeOffset, others will need the timezone control.
        $template->timezones = $this->core->timezone_control($template->timeOffset);

        $template->themelist = $this->theme->selector(
            nameAttr: 'thememem',
            selection: $theme,
        );

        $template->langfileselect = $this->tran->createLangFileSelect($langfile);

        if ($this->formMode == 'admin') {
            $template->userStatus = $this->core->userStatusControl(
                statusField: 'status',
                currentStatus: $member['status'],
            );
        }
    }

    public function readCallables()
    {
        $timeoffset = getPhpInput('timeoffset1');
        if (! is_numeric($timeoffset)) $timeoffset = '0';
        if ($this->formMode == 'new' || $this->targetUser['timeoffset'] != $timeoffset) {
            $this->edits['timeoffset'] = $timeoffset;
        }

        $thememem = formInt('thememem');
        if ($this->formMode == 'new' || $this->targetUser['theme'] != $thememem) {
            $this->edits['theme'] = $thememem;
        }

        $langfilenew = getPhpInput('langfilenew');
        if (! $this->tran->langfileExists($langfilenew)) {
            $langfilenew = $this->vars->settings['langfile'];
        }
        if ($this->formMode == 'new' || $this->targetUser['langfile'] != $langfilenew) {
            $this->edits['langfile'] = $langfilenew;
        }

        if ($this->formMode == 'admin') {
            $status = $this->validate->postedVar('status', dbescape: false);
            $origstatus = $this->targetUser['status'];
            // Check permission.
            if ($this->editorUser['status'] != 'Super Administrator' && ($origstatus == "Super Administrator" || $status == "Super Administrator")) {
                // Unauthorized.  Only Super Admins may edit Super Admins.
            } elseif ($this->targetUser['status'] != $status) {
                if ($origstatus == 'Super Administrator') {
                    // Check if the last Super Admin is trying to change own status.
                    $query = $this->db->query("SELECT COUNT(uid) FROM " . $this->vars->tablepre . "members WHERE status = 'Super Administrator'");
                    $sa_count = (int) $this->db->result($query);
                    $this->db->free_result($query);
                    if ($sa_count == 1) {
                        $this->core->error($this->vars->lang['lastsadmin']);
                    }
                }
                $this->edits['status'] = $status;
            }
        }
    }

    private function setAvatar()
    {
        $template = $this->template;
        $member = &$this->targetUser;

        $httpsOnly = 'on' == $this->vars->settings['images_https_only'];
        $template->js_https_only = $httpsOnly ? 'true' : 'false';

        if ($this->vars->settings['avastatus'] == 'on') {
            if ($this->formMode == 'new') {
                $template->avatar = $template->process('member_reg_avatarurl.php');
            } else {
                null_string($member['avatar']);
                if ($httpsOnly && strpos($member['avatar'], ':') !== false && substr($member['avatar'], 0, 6) !== 'https:') {
                    $template->avatarValue = '';
                } else {
                    $template->avatarValue = $member['avatar'];
                }
                $template->avatar = $template->process('memcp_profile_avatarurl.php');
            }
        } elseif ($this->vars->settings['avastatus'] == 'list')  {
            $avatars = ['<option value="" />' . $this->vars->lang['textnone'] . '</option>'];
            $dir1 = opendir(ROOT . 'images/avatars');
            while ($avFile = readdir($dir1)) {
                if (is_file(ROOT . 'images/avatars/' . $avFile) && $avFile != '.' && $avFile != '..' && $avFile != 'index.html') {
                    $avatars[] = '<option value="' . $this->vars->full_url . 'images/avatars/' . $avFile . '" />' . $avFile . '</option>';
                }
            }
            closedir($dir1);
            if ($this->formMode != 'new') {
                null_string($member['avatar']);
                $avatars = str_replace('value="'.$member['avatar'].'"', 'value="'.$member['avatar'].'" selected="selected"', $avatars);
            }
            $template->avatars = implode("\n", $avatars);
            $template->avatar = $template->process('member_reg_avatarlist.php');
            unset($avatars, $template->avatars);
        } else {
            $template->avatar = '';
        }
    }

    private function readAvatar(): string
    {
        $httpsOnly = 'on' == $this->vars->settings['images_https_only'];

        if ($this->vars->settings['avastatus'] == 'on') {
            $avatar = $this->validate->postedVar('newavatar', 'javascript', dbescape: false);
            $rawavatar = getPhpInput('newavatar');
            $newavatarcheck = getPhpInput('newavatarcheck');

            $max_size = explode('x', $this->vars->settings['max_avatar_size']);

            if (preg_match('/^' . get_img_regexp($httpsOnly) . '$/i', $rawavatar) == 0) {
                $avatar = '';
            } elseif (ini_get('allow_url_fopen')) {
                if ((int) $max_size[0] > 0 && (int) $max_size[1] > 0 && strlen($rawavatar) > 0) {
                    $size = getimagesize($rawavatar);
                    if ($size === false) {
                        $avatar = '';
                    } elseif (($size[0] > (int) $max_size[0] || $size[1] > (int) $max_size[1]) && ! X_SADMIN) {
                        $this->core->error($this->vars->lang['avatar_too_big'] . $this->vars->settings['max_avatar_size'] . 'px');
                    }
                }
            } elseif ($newavatarcheck == 'no') {
                $avatar = '';
            }
        } elseif ($this->vars->settings['avastatus'] == 'list') {
            $rawavatar = getPhpInput('newavatar');
            $dirHandle = opendir(ROOT . 'images/avatars');
            $filefound = false;
            while ($avFile = readdir($dirHandle)) {
                if ($rawavatar == $this->vars->full_url . 'images/avatars/' . $avFile) {
                    if (is_file(ROOT . 'images/avatars/' . $avFile) && $avFile != '.' && $avFile != '..' && $avFile != 'index.html') {
                        $filefound = true;
                        break;
                    }
                }
            }
            closedir($dirHandle);
            $avatar = $filefound ? $this->validate->postedVar('newavatar', 'javascript', dbescape: false) : '';
        } else {
            $avatar = '';
        }

        return $avatar;
    }

    public function setBirthday()
    {
        $template = $this->template;
        $member = &$this->targetUser;

        if ($this->formMode == 'new') {
            $day = '';
            $month = 0;
            $template->year = '';
        } else {
            $day = intval(substr($member['bday'], 8, 2));
            $month = intval(substr($member['bday'], 5, 2));
            $template->year = substr($member['bday'], 0, 4);
        }

        $dayselect = [
            "<select name='day'>",
            "<option value=''>&nbsp;</option>",
        ];
        for ($num = 1; $num <= 31; $num++) {
            $selected = $day == $num ? $this->vars::selHTML : '';
            $dayselect[] = "<option value='$num' $selected>$num</option>";
        }
        $dayselect[] = '</select>';
        $template->dayselect = implode("\n", $dayselect);

        $sel = array_fill(start_index: 0, count: 13, value: '');
        $sel[$month] = $this->vars::selHTML;
        $template->sel = $sel;
    }

    public function readBirthday()
    {
        $year = formInt('year');
        $month = formInt('month');
        $day = formInt('day');
        // For year of birth, reject all integers from 100 through 1899.
        if ($year >= 100 && $year <= 1899) $year = 0;
        $bday = iso8601_date($year, $month, $day);   

        if ($this->formMode == 'new' || $this->targetUser['bday'] != $bday) {
            $this->edits['bday'] = $bday;
        }
    }

    public function setOptionalFields()
    {
        $member = &$this->targetUser;

        switch ($this->formMode) {
            case 'admin':
                $this->template->bio = decimalEntityDecode($member['bio']);
                $this->template->location = decimalEntityDecode($member['location']);
                $this->template->mood = decimalEntityDecode($member['mood']);
                $this->template->sig = decimalEntityDecode($member['sig']);
                $this->template->site = $member['site'];
                break;
            case 'self':
                $this->template->bio = $this->core->rawHTMLsubject($member['bio']);
                $this->template->location = $this->core->rawHTMLsubject($member['location']);
                $this->template->mood = $this->core->rawHTMLsubject($member['mood']);
                $this->template->sig = $this->core->rawHTMLsubject($member['sig']);
                $this->template->site = $member['site'];
                break;
            default:
                $this->template->bio = '';
                $this->template->location = '';
                $this->template->mood = '';
                $this->template->sig = '';
                $this->template->site = '';
        }

        $this->setAvatar();
    }

    public function readOptionalFields()
    {
        $anyEdit = 'on' == $this->vars->settings['regoptional'];
        
        $selfEdit = $this->formMode == 'self' && (
            'off' == $this->vars->settings['quarantine_new_users']
            || ((int) $this->vars->self['postnum'] > 0 && 'no' == $this->vars->self['waiting_for_mod'])
            || X_STAFF
        );
        
        $adminEdit = $this->formMode == 'admin';
        
        if ($anyEdit || $selfEdit || $adminEdit) {
            $location = $this->validate->postedVar('newlocation', dbescape: false);
            $site = $this->validate->postedVar('newsite', 'javascript', dbescape: false);
            $bio = $this->validate->postedVar('newbio', dbescape: false);
            $mood = $this->validate->postedVar('newmood', dbescape: false);
            $sig = $this->validate->postedVar('newsig', dbescape: false);
            $avatar = $this->readAvatar();
        } else {
            $location = '';
            $site = '';
            $bio = '';
            $mood = '';
            $sig = '';
            $avatar = '';
        }
        if ($this->formMode == 'new' || $this->targetUser['location'] != $location) {
            $this->edits['location'] = $location;
        }
        if ($this->formMode == 'new' || $this->targetUser['site'] != $site) {
            $this->edits['site'] = format_member_site($site);
        }
        if ($this->formMode == 'new' || $this->targetUser['bio'] != $bio) {
            $this->edits['bio'] = $bio;
        }
        if ($this->formMode == 'new' || $this->targetUser['mood'] != $mood) {
            $this->edits['mood'] = $mood;
        }
        if ($this->formMode == 'new' || $this->targetUser['sig'] != $sig) {
            $this->edits['sig'] = $sig;
            if ($this->formMode != 'new' && $this->vars->settings['resetsigs'] == 'on') {
                if (strlen(trim($this->targetUser['sig'])) == 0) {
                    if (strlen(trim($sig)) > 0) {
                        $this->sql->setPostSigsByAuthor(true, $this->vars->self['username']);
                    }
                } elseif (strlen(trim($sig)) == 0) {
                    $this->sql->setPostSigsByAuthor(false, $this->vars->self['username']);
                }
            }
        }
        if ($this->formMode == 'new' || $this->targetUser['avatar'] != $avatar) {
            $this->edits['avatar'] = $avatar;
        }
    }

    public function setNumericFields()
    {
        if ($this->formMode == 'new') {
            $this->template->tpp = $this->vars->settings['topicperpage'];
            $this->template->ppp = $this->vars->settings['postperpage'];
        } else {
            $this->template->tpp = $this->targetUser['tpp'];
            $this->template->ppp = $this->targetUser['ppp'];
        }
    }

    public function readNumericFields()
    {
        $tpp = formInt('tpp');
        $ppp = formInt('ppp');
        if ($tpp < $this->vars::PAGING_MIN || $tpp > $this->vars::PAGING_MAX) $tpp = (int) $this->vars->settings['topicperpage'];
        if ($ppp < $this->vars::PAGING_MIN || $ppp > $this->vars::PAGING_MAX) $ppp = (int) $this->vars->settings['postperpage'];
        if ($this->formMode == 'new' || $this->targetUser['tpp'] != $tpp) {
            $this->edits['tpp'] = $tpp;
        }
        if ($this->formMode == 'new' || $this->targetUser['ppp'] != $ppp) {
            $this->edits['ppp'] = $ppp;
        }
    }

    public function setMiscFields()
    {
        if ($this->formMode == 'new') {
            $this->template->dateformat = $this->vars->settings['dateformat'];
        } else {
            $this->template->dateformat = $this->targetUser['dateformat'];
        }
    }

    public function readMiscFields()
    {
        $dateformat = getPhpInput('dateformatnew');
        $dateformattest = attrOut($dateformat, 'javascript');
        // Never allow attribute-special data in the date format because it can be unescaped using the date() parser.
        if (empty($dateformat) || $dateformat !== $dateformattest) {
            $this->edits['dateformat'] = $this->vars->settings['dateformat'];
        } elseif ($this->formMode == 'new' || $this->targetUser['dateformat'] != $dateformat) {
            $this->edits['dateformat'] = $dateformat;
        }
    }
}
