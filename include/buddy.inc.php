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

class BuddyManager
{
    public function __construct(private Core $core, private DBStuff $db, private Template $template, private Variables $vars)
    {
        // Property promotion.
    }

    /**
     * Display a message formatted for the buddy list popup window.
     *
     * @param string $message Message HTML
     * @param string $redirect Send the user to this URL after the default timeout.
     * @param bool $exit Should the script end now?
     */
    function blistmsg(string $message, string $redirect = '', bool $exit = false)
    {
        if ($redirect != '') {
            // Add redirect header, don't die yet.
            $core->redirect($redirect);
        }

        $this->template->message = $message;
        $this->template->process('buddylist_message.php', echo: true);

        if ($exit) exit();
    }

    /**
     * Add a buddy.
     *
     * @since ? Formerly known as buddy_add().
     * @since 1.10.00
     * @param array $buddys Usernames, must be HTML and DB escaped.
     */
    function add(array $buddys)
    {
        $lang = &$this->vars->lang;

        if (count($buddys) > 10) {
            $buddys = array_slice($buddys, 0, 10);
        }

        $xmbuser = $this->vars->xmbuser;
        foreach ($buddys as $buddy) {
            if (empty($buddy) || (strlen(trim($buddy)) == 0)) {
                $this->blistmsg($lang['nobuddyselected'], exit: true);
            } else {
                if ($buddy === $xmbuser) {
                    $this->blistmsg($lang['buddywarnaddself'], exit: true);
                }

                $q = $this->db->query("SELECT count(username) FROM " . $this->vars->tablepre . "buddys WHERE username = '$xmbuser' AND buddyname = '$buddy'");
                if ((int) $this->db->result($q) > 0) {
                    $this->blistmsg($buddy . ' ' . $lang['buddyalreadyonlist'], exit: true);
                } else {
                    $q = $this->db->query("SELECT count(username) FROM " . $this->vars->tablepre . "members WHERE username = '$buddy'");
                    if ((int) $this->db->result($q) < 1) {
                        $this->blistmsg($lang['nomember'], exit: true);
                    } else {
                        $this->db->query("INSERT INTO " . $this->vars->tablepre . "buddys (buddyname, username) VALUES ('$buddy', '$xmbuser')");
                        $this->blistmsg($buddy . ' ' . $lang['buddyaddedmsg'], $this->vars->full_url . 'buddy.php', exit: true);
                    }
                }
            }
        }
    }

    /**
     * Display the editing page.
     *
     * @since ? Formerly known as buddy_edit().
     * @since 1.10.00
     */
    function edit()
    {
        $xmbuser = $this->vars->xmbuser;

        $buddys = [];
        $q = $this->db->query("SELECT buddyname FROM " . $this->vars->tablepre . "buddys WHERE username = '$xmbuser'");
        while ($buddy = $this->db->fetch_array($q)) {
            $this->template->buddy = $buddy;
            $buddys[] = $this->template->process('buddylist_edit_buddy.php');
        }

        if (count($buddys) > 0) {
            $buddys = implode("\n", $buddys);
        } else {
            $buddys = '';
        }
        $this->template->buddys = $buddys;
        $this->template->process('buddylist_edit.php', echo: true);
    }

    /**
     * Delete a buddy.
     *
     * @since ? Formerly known as buddy_delete().
     * @since 1.10.00
     * @param array $delete Usernames, must be HTML and DB escaped.
     */
    function delete(array $delete)
    {
        $xmbuser = $this->vars->xmbuser;

        $list = "'" . implode("','", $delete) . "'";
        $this->db->query("DELETE FROM " . $this->vars->tablepre . "buddys WHERE buddyname IN ($list) AND username = '$xmbuser'");

        $this->blistmsg($this->vars->lang['buddylistupdated'], $this->vars->full_url . 'buddy.php');
    }

    /**
    * buddy_addu2u() - Display a list of buddies with their online status
    *
    * @since ? Formerly known as buddy_addu2u().
    * @since 1.10.00
    */
    function addu2u()
    {
        $xmbuser = $this->vars->xmbuser;

        $buddys = [
            'offline' => '',
            'online' => '',
        ];

        $q = $this->db->query("
            SELECT b.buddyname, m.invisible, m.username, m.lastvisit
            FROM " . $this->vars->tablepre . "buddys b
            LEFT JOIN " . $this->vars->tablepre . "members m ON (b.buddyname = m.username)
            WHERE b.username = '$xmbuser'
        ");
        if ($this->db->num_rows($q) == 0) {
            $this->blistmsg($this->vars->lang['no_buddies']);
        } else {
            while ($buddy = $this->db->fetch_array($q)) {
                $this->template->buddyout = $buddy['buddyname'];
                $this->template->recodename = recodeOut($buddy['buddyname']);
                if ($this->vars->onlinetime - (int) $buddy['lastvisit'] <= X_ONLINE_TIMER) {
                    if ('1' === $buddy['invisible']) {
                        if (! X_ADMIN) {
                            $buddys['offline'] .= $this->template->process('buddy_u2u_off.php');
                        } else {
                            $buddys['online'] .= $this->template->process('buddy_u2u_inv.php');
                        }
                    } else {
                        $buddys['online'] .= $this->template->process('buddy_u2u_on.php');
                    }
                } else {
                    $buddys['offline'] .= $this->template->process('buddy_u2u_off.php');
                }
            }
            $this->template->buddys = $buddys;
            $this->template->process('buddy_u2u.php', echo: true);
        }
    }

    /**
     * Display buddy list.
     *
     * @since ? Formerly known as buddy_display().
     * @since 1.10.00
     */
    function display()
    {
        $xmbuser = $this->vars->xmbuser;

        $q = $this->db->query("
            SELECT b.buddyname, m.invisible, m.username, m.lastvisit
            FROM " . $this->vars->tablepre . "buddys b
            LEFT JOIN " . $this->vars->tablepre . "members m ON (b.buddyname = m.username)
            WHERE b.username = '$xmbuser'
        ");
        $buddys = [
            'offline' => '',
            'online' => '',
        ];
        while ($buddy = $this->db->fetch_array($q)) {
            $this->template->recodename = recodeOut($buddy['buddyname']);
            $this->template->buddy = $buddy;
            if ($this->vars->onlinetime - (int) $buddy['lastvisit'] <= $vars::ONLINE_TIMER) {
                if ('1' === $buddy['invisible']) {
                    if (! X_ADMIN) {
                        $buddys['offline'] .= $this->template->process('buddylist_buddy_offline.php');
                        continue;
                    } else {
                        $this->template->buddystatus = $this->vars->lang['hidden'];
                    }
                } else {
                    $this->template->buddystatus = $this->vars->lang['textonline'];
                }
                $buddys['online'] .= $this->template->process('buddylist_buddy_online.php');
            } else {
                $buddys['offline'] .= $this->template->process('buddylist_buddy_offline.php');
            }
        }
        $this->template->buddys = $buddys;
        $this->template->process('buddylist.php', echo: true);
    }
}
