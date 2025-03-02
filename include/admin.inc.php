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

use XMB\Session\Manager as SessionMgr;

/**
 * Provides generic business logic for admin activities
 * 
 * @since 1.9.1
 */
class admin
{
    public function __construct(
        private Core $core,
        private DBStuff $db,
        private SessionMgr $session,
        private SQL $sql,
        private Template $template,
        private Variables $vars
    ) {
        // Property promotion.
    }

    /**
     * rename_user()
     *
     * @since 1.9.1
     * @param string $userfrom
     * @param string $userto new username
     * @return string to display to the admin once the operation has completed
     */
    public function rename_user(string $userfrom, string $userto): string
    {
        $db = $this->db;
        $lang = &$this->vars->lang;

        if (strlen($userto) < $vars::USERNAME_MIN_LENGTH || strlen($userto) > $vars::USERNAME_MAX_LENGTH) {
            return $lang['username_length_invalid'];
        }

        $dbuserfrom = $db->escape($userfrom);
        $dbuserto = $db->escape($userto);
        $dblikeuserfrom = $db->like_escape($userfrom);
        $dbregexuserfrom = $db->regexp_escape($userfrom);

        $query = $db->query("SELECT username FROM " . $this->vars->tablepre . "members WHERE username='$dbuserfrom'");
        $cUsrFrm = $db->num_rows($query);
        $db->free_result($query);

        $query = $db->query("SELECT username FROM " . $this->vars->tablepre . "members WHERE username='$dbuserto'");
        $cUsrTo = $db->num_rows($query);
        $db->free_result($query);

        if (! ($cUsrFrm == 1 && $cUsrTo == 0)) {
            return $lang['admin_rename_fail'];
        }

        if (! $core->usernameValidation($userto)) {
            return $lang['restricted'];
        }

        $this->session->logoutAll($userfrom);

        ignore_user_abort(true);
        set_time_limit(180);
        $db->query("UPDATE " . $this->vars->tablepre . "members SET username='$dbuserto' WHERE username='$dbuserfrom'");
        $db->query("UPDATE " . $this->vars->tablepre . "buddys SET username='$dbuserto' WHERE username='$dbuserfrom'");
        $db->query("UPDATE " . $this->vars->tablepre . "buddys SET buddyname='$dbuserto' WHERE buddyname='$dbuserfrom'");
        $db->query("UPDATE " . $this->vars->tablepre . "favorites SET username='$dbuserto' WHERE username='$dbuserfrom'");
        $db->query("UPDATE " . $this->vars->tablepre . "forums SET moderator='$dbuserto' WHERE moderator='$dbuserfrom'");
        $db->query("UPDATE " . $this->vars->tablepre . "logs SET username='$dbuserto' WHERE username='$dbuserfrom'");
        $db->query("UPDATE " . $this->vars->tablepre . "posts SET author='$dbuserto' WHERE author='$dbuserfrom'");
        $db->query("UPDATE " . $this->vars->tablepre . "threads SET author='$dbuserto' WHERE author='$dbuserfrom'");
        $db->query("UPDATE " . $this->vars->tablepre . "u2u SET msgto='$dbuserto' WHERE msgto='$dbuserfrom'");
        $db->query("UPDATE " . $this->vars->tablepre . "u2u SET msgfrom='$dbuserto' WHERE msgfrom='$dbuserfrom'");
        $db->query("UPDATE " . $this->vars->tablepre . "u2u SET owner='$dbuserto' WHERE owner='$dbuserfrom'");
        $db->query("UPDATE " . $this->vars->tablepre . "whosonline SET username='$dbuserto' WHERE username='$dbuserfrom'");

        $query = $db->query("SELECT ignoreu2u, uid FROM " . $this->vars->tablepre . "members WHERE (ignoreu2u REGEXP '(^|(,))()*$dbregexuserfrom()*((,)|$)')");
        while($usr = $db->fetch_array($query)) {
            $db->escape_fast($usr['ignoreu2u']);
            $parts = explode(',', $usr['ignoreu2u']);
            $index = array_search($dbuserfrom, $parts);
            $parts[$index] = $dbuserto;
            $parts = implode(',', $parts);
            $db->query("UPDATE " . $this->vars->tablepre . "members SET ignoreu2u='$parts' WHERE uid={$usr['uid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT moderator, fid FROM " . $this->vars->tablepre . "forums WHERE (moderator REGEXP '(^|(,))()*$dbregexuserfrom()*((,)|$)')");
        while($list = $db->fetch_array($query)) {
            $db->escape_fast($list['moderator']);
            $parts = explode(',', $list['moderator']);
            $index = array_search($dbuserfrom, $parts);
            $parts[$index] = $dbuserto;
            $parts = implode(', ', $parts);
            $db->query("UPDATE " . $this->vars->tablepre . "forums SET moderator='$parts' WHERE fid={$list['fid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT userlist, fid FROM " . $this->vars->tablepre . "forums WHERE (userlist REGEXP '(^|(,))()*$dbregexuserfrom()*((,)|$)')");
        while($list = $db->fetch_array($query)) {
            $db->escape_fast($list['userlist']);
            $parts = array_unique(array_map('trim', explode(',', $list['userlist'])));
            $index = array_search($dbuserfrom, $parts);
            $parts[$index] = $dbuserto;
            $parts = implode(', ', $parts);
            $db->query("UPDATE " . $this->vars->tablepre . "forums SET userlist='$parts' WHERE fid={$list['fid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT fid, lastpost FROM " . $this->vars->tablepre . "forums WHERE lastpost LIKE '%|$dblikeuserfrom|%'");
        while($result = $db->fetch_array($query)) {
            $db->escape_fast($result['lastpost']);
            $newlastpost = str_replace("|$dbuserfrom|", "|$dbuserto|", $result['lastpost']);
            $db->query("UPDATE " . $this->vars->tablepre . "forums SET lastpost='$newlastpost' WHERE fid={$result['fid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT tid, lastpost FROM " . $this->vars->tablepre . "threads WHERE lastpost LIKE '%|$dblikeuserfrom|%'");
        while($result = $db->fetch_array($query)) {
            $db->escape_fast($result['lastpost']);
            $newlastpost = str_replace("|$dbuserfrom|", "|$dbuserto|", $result['lastpost']);
            $db->query("UPDATE " . $this->vars->tablepre . "threads SET lastpost='$newlastpost' WHERE tid={$result['tid']}");
        }
        $db->free_result($query);

        return (($this->vars->self['username'] == $userfrom) ? $lang['admin_rename_warn_self'] : '') . $lang['admin_rename_success'];
    }

    /**
     * Provides HTML attributes for use with printsetting1().
     *
     * @since 1.9.8
     */
    public function settingHTML(string $setting, string &$on, string &$off)
    {
        $on = $off = '';
        switch($this->vars->settings[$setting]) {
            case 'on':
                $on = $this->vars::selHTML;
                break;
            default:
                $off = $this->vars::selHTML;
                break;
        }
    }

    /**
     * Single line text control.
     *
     * @since 1.5.0
     */
    public function printsetting2($setname, $varname, $value, $size)
    {
        $template = new Template($this->vars);
        $template->addRefs();

        $template->setname = $setname;
        $template->varname = $varname;
        $template->value = $value;
        $template->size = $size;

        $template->process('admin_printsetting2.php', echo: true);
    }

    /**
     * Drop down list or multi-select control.
     *
     * @since 1.9.1
     */
    public function printsetting3($setname, $boxname, $varnames, $values, $checked, $multi = true)
    {
        $template = new Template($this->vars);
        $template->addRefs();

        $template->setname = $setname;
        $template->boxname = $boxname;
        $template->multi = $multi;

        foreach($varnames as $key=>$val) {
            if (isset($checked[$key]) && $checked[$key] !== true) {
                $optionlist[] = '<option value="'.$values[$key].'">'.$varnames[$key].'</option>';
            } else {
                $optionlist[] = '<option value="'.$values[$key].'" '.$this->vars::selHTML.'>'.$varnames[$key].'</option>';
            }
        }
        $template->optionlist = implode("\n", $optionlist);

        $template->process('admin_printsetting3.php', echo: true);
    }

    /**
     * Multi-line text control.
     *
     * @since 1.9.4
     */
    public function printsetting4($settingDesc, $name, $value, $rows = 5, $cols = 50)
    {
        $template = new Template($this->vars);
        $template->addRefs();

        $template->settingDesc = $settingDesc;
        $template->name = $name;
        $template->value = $value;
        $template->rows = $rows;
        $template->cols = $cols;

        $template->process('admin_printsetting4.php', echo: true);
    }

    /**
     * Table row with plain text or raw HTML instead of a specific input control.
     *
     * @since 1.9.11
     */
    public function printsetting5($settingDesc, $errorMsg)
    {
        $template = new Template($this->vars);
        $template->addRefs();

        $template->settingDesc = $settingDesc;
        $template->errorMsg = $errorMsg;

        $template->process('admin_printsetting5.php', echo: true);
    }

    /**
     * Improved On/Off drop down control.
     *
     * @since 1.10.00
     * @param string $description The human-readable setting description.
     * @param string $htmlName The HTML name attribute.
     * @param string $xmbName The XMB settings array key.
     */
    public function printsetting6(string $description, string $htmlName, string $xmbName)
    {
        $template = new Template($this->vars);
        $template->addRefs();

        $template->check1 = '';
        $template->check2 = '';
        $template->description = $description;
        $template->htmlName = $htmlName;

        switch($this->vars->settings[$xmbName]) {
            case 'on':
                $template->check1 = $this->vars::selHTML;
                break;
            default:
                $template->check2 = $this->vars::selHTML;
                break;
        }

        $template->process('admin_printsetting6.php', echo: true);
    }

    /**
     * Take string input and save it to settings.
     *
     * @since 1.9.12
     * @param string $dbname     The name of the setting as saved in the database.
     * @param string $postname   The HTML input name.
     * @param bool   $htmlencode Optional. Whether to escape HTML special chars. Usually true.
     */
    public function input_string_setting(string $dbname, string $postname, bool $htmlencode = true)
    {
        $value = $this->core->postedVar($postname, '', $htmlencode, false);
        $this->input_custom_setting($dbname, $value);
    }

    /**
     * Take integer input and save it to settings.
     *
     * @since 1.9.12
     * @param string $dbname The name of the setting as saved in the database.
     * @param string $postname The HTML input name.
     */
    public function input_int_setting(string $dbname, string $postname)
    {
        $value = (string) formInt($postname);
        $this->input_custom_setting($dbname, $value);
    }

    /**
     * Take on/off input and save it to settings.
     *
     * @since 1.9.12
     * @param string $dbname The name of the setting as saved in the database.
     * @param string $postname The HTML input name.
     */
    public function input_onoff_setting(string $dbname, string $postname)
    {
        $value = formOnOff($postname);
        $this->input_custom_setting($dbname, $value);
    }

    /**
     * Take a string variable and save it to settings.
     *
     * @since 1.9.12
     * @param string $dbname The name of the setting as saved in the database.
     * @param string $value
     */
    public function input_custom_setting(string $dbname, string $value)
    {
        if (! isset($this->vars->settings[$dbname])) {
            $this->sql->addSetting($dbname, $value);
        } elseif ($this->vars->settings[$dbname] !== $value) {
            $this->sql->updateSetting($dbname, $value);
        }
    }

    /**
     * Read a theme / template file into an array
     *
     * Takes a theme or template and imports the contents into an array
     *
     * Function taken from a phpBB hack with permission
     *
     * @since 1.5
     * @param string $filename File to read, should be a sanitized name
     * @return array An array of (key,value) tuples
     */
    public function readFileAsINI(string $filename): array
    {
        $thefile = [];
        $lines = file($filename);
        foreach($lines as $line_num => $line) {
            $temp = explode("=", $line);
            if ($temp[0] != 'dummy') {
                $key = trim($temp[0]);
                $val = trim($temp[1]);
                $thefile[$key] = $val;
            }
        }
        return $thefile;
    }

    /**
     * Output an HTML table body representing the results of a database query.
     *
     * @since 1.9.1
     */
    public function dump_query($resource, $header = true): string
    {
        $THEME = &$this->vars->theme;

        if ($this->db->error()) {
            $error = '<tr bgcolor="' . $THEME['altbg1'] . '" class="ctrtablerow"><td align="left">';
            $error .= $this->core->error($this->db->error(), showheader: false, return_as_string: true, showfooter: false, die: false);
            $error .= '</td></tr>';
            return $error;
        } elseif ($resource === true) {
            // Success with no result.
            return '';
        } else {
            ob_start();
            $count = $this->db->num_fields($resource);
            if ($header) {
                ?>
                <tr class="category" bgcolor="<?= $THEME['altbg2'] ?>" align="center">
                <?php
                for($i=0;$i<$count;$i++) {
                    echo '<td align="left">';
                    echo '<strong><font color=' . $THEME['cattext'] . '>' . $this->db->field_name($resource, $i) . '</font></strong>';
                    echo '</td>';
                }
                echo '</tr>';
            }

            while($a = $this->db->fetch_array($resource, $this->db::SQL_NUM)) {
                ?>
                <tr bgcolor="<?= $THEME['altbg1'] ?>" class="ctrtablerow">
                <?php
                for($i=0;$i<$count;$i++) {
                    echo '<td align="left">';

                    if (null === $a[$i]) {
                        echo '<em>NULL</em>';
                    } elseif (trim($a[$i]) == '') {
                        echo '&nbsp;';
                    } else {
                        echo nl2br(cdataOut($a[$i]));
                    }
                    echo '</td>';
                }
                echo '</tr>';
            }
            return ob_get_clean();
        }
    }
}
