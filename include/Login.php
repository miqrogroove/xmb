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
    public function __construct(private Core $core, private DBStuff $db, private SessionMgr $session, private SQL $sql, private Variables $vars)
    {
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
        $vars = $this->vars;

        if ($this->session->getStatus() !== 'good') return;

        if (!is_null($invisible)) {
            $old = $vars->self['invisible'];
            if ($invisible) {
                $vars->self['invisible'] = '1';
            } else {
                $vars->self['invisible'] = '0';
            }
            if ($old !== $vars->self['invisible']) {
                $this->sql->changeMemberVisibility($vars->self['username'], $vars->self['invisible']);            
            }
        }

        // These cookies were already set in header.php, but PHP is smart enough to overwrite them.
        $this->core->put_cookie('xmblvb', $vars->self['lastvisit'], (time() + $vars::ONLINE_TIMER)); // lvb == last visit
        $vars->lastvisit = (int) $vars->self['lastvisit']; // Used in forumdisplay and a few other spots.
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
        // $xmbpw was historically abused and will no longer contain a value.

        if ('good' == $state || 'already-logged-in' == $state) {
            // 'good' means normal login or resumed session.
            // 'already-logged-in' is a soft error that might result from login races or multiple open tabs.
            $vars->self = $this->session->getMember();
            $vars->xmbuser = $this->db->escape($vars->self['username']);
        } else {
            $vars->self = ['status' => ''];
            $vars->xmbuser = '';
        }
        $vars->self['password'] = '';

        // Initialize the new translation system
        if (! defined('XMB_UPGRADE')) {
            $success = false;
            if (!empty($vars->self['langfile'])) {
                $success = $this->core->loadLang($vars->self['langfile']);
            }
            if (!$success) {
                $success = $this->core->loadLang($vars->settings['langfile']);
            }
            if (!$success) {
                require_once(ROOT.'include/translation.inc.php');
                langPanic();
            }
        }

        // Set the user status constants.
        if ($vars->xmbuser != '') {
            if (!defined('X_GUEST')) {
                define('X_MEMBER', TRUE);
                define('X_GUEST', FALSE);
            }
            // Save some write locks by updating in 60-second intervals.
            if (abs(time() - (int) $vars->self['lastvisit']) > 60) {
                $this->sql->setLastvisit($vars->self['username'], $vars->onlinetime);
                // Important: Don't update $self['lastvisit'] until the next hit, otherwise we won't actually know when the last visit happened.
            }
        } else {
            if (!defined('X_GUEST')) {
                define('X_MEMBER', FALSE);
                define('X_GUEST', TRUE);
            }
        }

        // Enumerate status
        if (isset($this->vars->status_enum[$vars->self['status']])) {
            $int_status = $this->vars->status_enum[$vars->self['status']];
        } else {
            $int_status = $this->vars->status_enum['Member']; // If $self['status'] contains an unknown value, default to Member.
        }

        if (!defined('X_STAFF')) {
            define('X_SADMIN', ($vars->self['status'] == 'Super Administrator'));
            define('X_ADMIN', ($int_status <= $this->vars->status_enum['Administrator']));
            define('X_SMOD', ($int_status <= $this->vars->status_enum['Super Moderator']));
            define('X_MOD', ($int_status <= $this->vars->status_enum['Moderator']));
            define('X_STAFF', X_MOD);
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

        if ($vars->tpp < 5) $vars->tpp = 30;
        if ($vars->ppp < 5) $vars->ppp = 30;

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
        if (! defined('XMB_UPGRADE')
            && basename($_SERVER['SCRIPT_NAME']) != 'css.php'
            && basename($_SERVER['SCRIPT_NAME']) != 'files.php'
            && (X_ADMIN || $serror == '' || $serror == 'guest' && X_MEMBER)
        ) {
            if (strlen($vars->onlineip) > 15 && ((int) $vars->settings['schema_version'] < 9 || strlen($vars->onlineip) > 39)) {
                $useip = '';
            } else {
                $useip = $vars->onlineip;
            }
            $wollocation = substr($vars->url, 0, $maxurl);
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
                header('HTTP/1.0 503 Service Unavailable');
                header('Retry-After: 3600');
                if ($this->vars->settings['bboffreason'] != '') {
                    $this->core->message(nl2br($this->vars->settings['bboffreason']));
                } else {
                    $this->core->message($this->vars->lang['textbstatusdefault']);
                }
            }
            break;
        case 'guest':
            if (X_GUEST) {
                if ($this->vars->settings['bboffreason']['regstatus'] == 'on') {
                    $message = $this->vars->lang['reggedonly'].' '.$this->template->reglink.' '.$this->vars->lang['textor'].' <a href="misc.php?action=login">'.$this->vars->lang['textlogin'].'</a>';
                } else {
                    $message = $this->vars->lang['reggedonly'].' <a href="misc.php?action=login">'.$this->vars->lang['textlogin'].'</a>';
                }
                $this->core->message($message);
            }
            break;
        }
    }

}
