<?php

/**
 * eXtreme Message Board
 * XMB 1.10.01
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

class BuddyManager
{
    public function __construct(private Core $core, private DBStuff $db, private SQL $sql, private Template $template, private Variables $vars)
    {
        // Property promotion.
    }

    /**
     * Display a message formatted for the buddy list popup window.
     *
     * @since 1.9.1
     * @param string $message Message HTML
     * @param string $redirect Send the user to this URL after the default timeout.
     * @param bool $exit Should the script end now?
     */
    public function blistmsg(string $message, string $redirect = '', bool $exit = false)
    {
        if ($redirect != '') {
            // Add redirect header, don't die yet.
            $this->core->redirect($redirect);
        }

        $this->template->message = $message;
        $this->template->process('buddylist_message.php', echo: true);

        if ($exit) exit();
    }

    /**
     * Add a buddy.
     *
     * @since 1.9.1 Formerly buddy_add()
     * @since 1.10.00
     * @param array $buddys Usernames, must be HTML and DB escaped.
     */
    public function add(array $buddys)
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
     * @since 1.9.1 Formerly buddy_edit()
     * @since 1.10.00
     */
    public function edit()
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
     * @since 1.9.1 Formerly buddy_delete()
     * @since 1.10.00
     * @param array $delete Usernames, must be HTML and DB escaped.
     */
    public function delete(array $delete)
    {
        $xmbuser = $this->vars->xmbuser;

        $list = "'" . implode("','", $delete) . "'";
        $this->db->query("DELETE FROM " . $this->vars->tablepre . "buddys WHERE buddyname IN ($list) AND username = '$xmbuser'");

        $this->blistmsg($this->vars->lang['buddylistupdated'], $this->vars->full_url . 'buddy.php');
    }

    /**
    * buddy_addu2u() - Display a list of buddies with their online status
    *
    * @since 1.9.1 Formerly buddy_addu2u()
    * @since 1.10.00
    */
    public function addu2u()
    {
        $buddys = [
            'offline' => '',
            'online' => '',
        ];

        $buddyList = $this->sql->getBuddyList($this->vars->self['username']);
        if (count($buddyList) == 0) {
            $this->blistmsg($this->vars->lang['no_buddies']);
        } else {
            foreach ($buddyList as $buddy) {
                $this->template->buddyout = $buddy['buddyname'];
                $this->template->recodename = recodeOut($buddy['buddyname']);
                if ($this->vars->onlinetime - (int) $buddy['lastvisit'] <= $this->vars::ONLINE_TIMER) {
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
     * Display the buddy list for the current user.
     *
     * @since 1.9.1 Formerly buddy_display()
     * @since 1.10.00
     */
    public function display()
    {
        $this->template->buddys = $this->list();
        $this->template->process('buddylist.php', echo: true);
    }

    /**
     * Generate the buddys HTML array for use as a list in other templates.
     *
     * @since 1.10.00
     */
    public function list(): array
    {
        $template = new Template($this->vars);
        $template->addRefs();

        $buddys = [
            'offline' => '',
            'online' => '',
        ];
        $buddyList = $this->sql->getBuddyList($this->vars->self['username']);
        foreach ($buddyList as $buddy) {
            $template->recodename = recodeOut($buddy['buddyname']);
            $template->buddy = $buddy;
            if ($this->vars->onlinetime - (int) $buddy['lastvisit'] <= $this->vars::ONLINE_TIMER) {
                if ('1' === $buddy['invisible']) {
                    if (! X_ADMIN) {
                        $buddys['offline'] .= $template->process('buddylist_buddy_offline.php');
                        continue;
                    } else {
                        $template->buddystatus = $this->vars->lang['hidden'];
                    }
                } else {
                    $template->buddystatus = $this->vars->lang['textonline'];
                }
                $buddys['online'] .= $template->process('buddylist_buddy_online.php');
            } else {
                $buddys['offline'] .= $template->process('buddylist_buddy_offline.php');
            }
        }
        return $buddys;
    }
}
