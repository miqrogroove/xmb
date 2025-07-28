<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-2
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

use XMB\Session\Manager as SessionMgr;

/**
 * This class provides methods that depend on the Session service.
 *
 * These need to be separated from any class used by Session to avoid circular dependency.
 *
 * @since 1.10.00
 */
class Login
{
    public function __construct(
        private Core $core,
        private DBStuff $db,
        private SessionMgr $session,
        private SQL $sql,
        private Template $template,
        private Translation $tran,
        private Variables $vars
    ) {
        // Property promotion.
    }

    /**
     * Sets up some extra variables after a new login.
     *
     * @since 1.9.10
     * @param bool $invisible Optional. Result of the 'Browse the board invisible' option at login.
     */
    function loginUser(?bool $invisible = null)
    {
        if ($this->session->getStatus() !== 'good') return;

        if (! is_null($invisible)) {
            $old = $this->vars->self['invisible'];
            $new = $invisible ? '1' : '0';
            if ($old !== $new) {
                $uid = (int) $this->vars->self['uid'];
                $this->sql->changeMemberVisibility($uid, $new);            
                $this->vars->self['invisible'] = $new;
            }
        }

        // These cookies were already set in header.php, but PHP is smart enough to overwrite them.
        $this->core->put_cookie('xmblvb', $this->vars->self['lastvisit'], (time() + $this->vars::ONLINE_TIMER)); // lvb == last visit
        $this->vars->lastvisit = (int) $this->vars->self['lastvisit']; // Used in forumdisplay and a few other spots.
    }

    /**
     * Responsible for setting up session variables.
     *
     * @since 1.9.10
     * @param  bool   $force_inv Optional.
     * @return bool
     */
    function elevateUser(bool $force_inv = false)
    {
        $vars = $this->vars;

        $maxurl = 150; //Schema constant.

        $state = $this->session->getStatus();

        // Usernames are historically HTML encoded in the XMB database, as well as in cookies.
        // $xmbuser is often used as a raw value in queries and should be sql escaped.
        // $self['username'] had been a good alternative for template/HTML use when it was a global variable.
        // Now the best practice is to keep the $self array isolated and use a template alias of the $self['username'] value for output.

        if ('good' == $state || 'already-logged-in' == $state) {
            // 'good' means normal login or resumed session.
            // 'already-logged-in' is a soft error that might result from login races or multiple open tabs.
            $vars->self = $this->session->getMember();
            $vars->xmbuser = $this->db->escape($vars->self['username']);
        } else {
            $vars->self = ['status' => ''];
            $vars->xmbuser = '';
        }

        // Initialize the new translation system
        $success = false;
        if (! empty($vars->self['langfile'])) {
            $success = $this->tran->loadLang($vars->self['langfile']);
        }
        if (! $success && ! empty($vars->settings['langfile'])) {
            $success = $this->tran->loadLang($vars->settings['langfile']);
        }
        if (! $success) {
            $this->tran->langPanic();
        }
        $this->template->addRefs(); // Good idea to have this here, otherwise template translations can't be used until its called elsewhere.

        // Adjust any variables that require translation
        $this->template->versionlong = $this->vars->lang['textpoweredVer'] . ' ' . $this->vars->versiongeneral;
        if ($this->vars->debug) {
            $this->template->versionlong .= ' ' . $this->vars->lang['debugMode'];
        }

        // Set the user status constants.
        if ($vars->xmbuser != '') {
            if (! defined('XMB\X_GUEST')) {
                define('XMB\X_MEMBER', true);
                define('XMB\X_GUEST', false);
            }
            // Save some write locks by updating in 60-second intervals.
            if (abs(time() - (int) $vars->self['lastvisit']) > 60) {
                $this->sql->setLastvisit((int) $vars->self['uid'], $vars->onlinetime);
                // Important: Don't update $self['lastvisit'] until the next hit, otherwise we won't actually know when the last visit happened.
            }
        } else {
            if (! defined('XMB\X_GUEST')) {
                define('XMB\X_MEMBER', false);
                define('XMB\X_GUEST', true);
            }
        }

        // Enumerate status
        if (isset($this->vars->status_enum[$vars->self['status']])) {
            $int_status = $this->vars->status_enum[$vars->self['status']];
        } else {
            $int_status = $this->vars->status_enum['Member']; // If $self['status'] contains an unknown value, default to Member.
        }

        if (! defined('XMB\X_STAFF')) {
            define('XMB\X_SADMIN', ($vars->self['status'] == 'Super Administrator'));
            define('XMB\X_ADMIN', ($int_status <= $this->vars->status_enum['Administrator']));
            define('XMB\X_SMOD', ($int_status <= $this->vars->status_enum['Super Moderator']));
            define('XMB\X_MOD', ($int_status <= $this->vars->status_enum['Moderator']));
            define('XMB\X_STAFF', X_MOD);
        }

        if ($this->vars->debug && ! X_SADMIN) {
            $this->db->stopQueryLogging();
        }

        // Set variables
        $vars->dateformat = $vars->settings['dateformat'];

        if ($vars->xmbuser != '') {
            $vars->timeoffset = $vars->self['timeoffset'];
            $vars->tpp = (int) $vars->self['tpp'];
            $vars->ppp = (int) $vars->self['ppp'];
            $memtime = (int) $vars->self['timeformat'];
            if ($vars->self['dateformat'] != '') {
                $vars->dateformat = $vars->self['dateformat'];
            }
            $invisible = $vars->self['invisible'];
            $onlineuser = $vars->self['username'];
        } else {
            $vars->timeoffset = $vars->settings['def_tz'];
            $vars->tpp = (int) $vars->settings['topicperpage'];
            $vars->ppp = (int) $vars->settings['postperpage'];
            $memtime = (int) $vars->settings['timeformat'];
            $invisible = '0';
            $onlineuser = 'xguest123';
            $vars->self['ban'] = '';
            $vars->self['sig'] = '';
            $vars->self['uid'] = '0';
            $vars->self['username'] = '';
        }

        if ($force_inv) {
            $invisible = '1';
        }

        if ($memtime == 24) {
            $vars->timecode = "H:i";
        } else {
            $vars->timecode = "h:i A";
        }

        $vars->dateformat = str_replace(array('mm', 'MM', 'dd', 'DD', 'yyyy', 'YYYY', 'yy', 'YY'), array('n', 'n', 'j', 'j', 'Y', 'Y', 'y', 'y'), $vars->dateformat);

        // Save This Session
        $serror = $this->session->getSError();
        if (! defined('XMB\UPGRADE')
            && basename($_SERVER['SCRIPT_NAME']) != 'css.php'
            && basename($_SERVER['SCRIPT_NAME']) != 'files.php'
            && (X_ADMIN || $serror == '' || $serror == 'guest' && X_MEMBER)
        ) {
            if (strlen($vars->onlineip) > 15 && ((int) $vars->settings['schema_version'] < 9 || strlen($vars->onlineip) > 39)) {
                $useip = '';
            } else {
                $useip = $vars->onlineip;
            }
            $wollocation = htmlEsc(substr($vars->url, 0, $maxurl));
            $newtime = $vars->onlinetime - $vars::ONLINE_TIMER;
            $this->sql->deleteOldWhosonline($useip, $vars->self['username'], $newtime);
            $this->sql->addWhosonline($useip, $onlineuser, $vars->onlinetime, $wollocation, $invisible);
        }
    }

    /**
     * Display session startup errors.
     *
     * Formerly part of header.php.  If any errors are found, the script will end here.
     */
    public function sendErrors()
    {
        switch ($this->session->getSError()) {
            case 'ip':
                if (! X_ADMIN) {
                    header('HTTP/1.0 403 Forbidden');
                    $this->core->error($this->vars->lang['bannedmessage']);
                }
                break;
            case 'bstatus':
                if (! X_ADMIN) {
                    $this->core->unavailable('bstatus');
                }
                break;
            case 'guest':
                if (X_GUEST) {
                    $loginout = $this->core->getLoginLink();
                    $reglink = $this->core->getRegistrationLink();
                    if ($reglink !== '') {
                        $reglink = ' ' . $reglink . ' ' . $this->vars->lang['textor'];
                    }
                    $message = $this->vars->lang['reggedonly'] . $reglink . ' ' . $loginout;
                    $this->core->message($message);
                }
        }
    }
}
