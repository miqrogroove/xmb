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

use function XMB\Services\core;
use function XMB\Services\sql;
use function XMB\Services\vars;

/* Assert Additional Security */

if (X_SADMIN) {
    $x_error = '';

    //@todo translation needed
    if (file_exists(ROOT.'install/') && !@rmdir(ROOT.'install/')) {
        $x_error = 'The installation files ("./install/") have been found on the server, but could not be removed automatically. Please remove them as soon as possible.';
    }
    if (file_exists(ROOT.'Upgrade/') && !@rmdir(ROOT.'Upgrade/') || file_exists(ROOT.'upgrade/') && !@rmdir(ROOT.'upgrade/')) {
        $x_error = 'The upgrade tool ("./upgrade/") has been found on the server, but could not be removed automatically. Please remove it as soon as possible.';
    }
    if (file_exists(ROOT.'upgrade.php')) {
        $x_error = 'The upgrade tool ("./upgrade.php") has been found on the server. Please remove it as soon as possible.';
    }

    if (strlen($x_error) > 0) {
        header('HTTP/1.0 500 Internal Server Error');
        loadtemplates('error');
        error($x_error);
    }
    unset($x_error);
}


/**
 * Provides generic business logic for admin activities
 * 
 * @since 1.9.1
 */
class admin
{
    public function __construct(private SessionMgr $session)
    {
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
        global $db, $lang, $self;

        if (strlen($userto) < 3 || strlen($userto) > 32) {
            return $lang['username_length_invalid'];
        }

        $dbuserfrom = $db->escape($userfrom);
        $dbuserto = $db->escape($userto);
        $dblikeuserfrom = $db->like_escape($userfrom);
        $dbregexuserfrom = $db->regexp_escape($userfrom);

        $query = $db->query("SELECT username FROM ".X_PREFIX."members WHERE username='$dbuserfrom'");
        $cUsrFrm = $db->num_rows($query);
        $db->free_result($query);

        $query = $db->query("SELECT username FROM ".X_PREFIX."members WHERE username='$dbuserto'");
        $cUsrTo = $db->num_rows($query);
        $db->free_result($query);

        if (!($cUsrFrm == 1 && $cUsrTo == 0)) {
            return $lang['admin_rename_fail'];
        }

        if (!$this->check_restricted($dbuserto)) {
            return $lang['restricted'];
        }
        
        $this->session->logoutAll($userfrom);

        @set_time_limit(180);
        $db->query("UPDATE ".X_PREFIX."members SET username='$dbuserto' WHERE username='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."buddys SET username='$dbuserto' WHERE username='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."buddys SET buddyname='$dbuserto' WHERE buddyname='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."favorites SET username='$dbuserto' WHERE username='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."forums SET moderator='$dbuserto' WHERE moderator='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."logs SET username='$dbuserto' WHERE username='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."posts SET author='$dbuserto' WHERE author='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."threads SET author='$dbuserto' WHERE author='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."u2u SET msgto='$dbuserto' WHERE msgto='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."u2u SET msgfrom='$dbuserto' WHERE msgfrom='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."u2u SET owner='$dbuserto' WHERE owner='$dbuserfrom'");
        $db->query("UPDATE ".X_PREFIX."whosonline SET username='$dbuserto' WHERE username='$dbuserfrom'");

        $query = $db->query("SELECT ignoreu2u, uid FROM ".X_PREFIX."members WHERE (ignoreu2u REGEXP '(^|(,))()*$dbregexuserfrom()*((,)|$)')");
        while($usr = $db->fetch_array($query)) {
            $db->escape_fast($usr['ignoreu2u']);
            $parts = explode(',', $usr['ignoreu2u']);
            $index = array_search($dbuserfrom, $parts);
            $parts[$index] = $dbuserto;
            $parts = implode(',', $parts);
            $db->query("UPDATE ".X_PREFIX."members SET ignoreu2u='$parts' WHERE uid={$usr['uid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT moderator, fid FROM ".X_PREFIX."forums WHERE (moderator REGEXP '(^|(,))()*$dbregexuserfrom()*((,)|$)')");
        while($list = $db->fetch_array($query)) {
            $db->escape_fast($list['moderator']);
            $parts = explode(',', $list['moderator']);
            $index = array_search($dbuserfrom, $parts);
            $parts[$index] = $dbuserto;
            $parts = implode(', ', $parts);
            $db->query("UPDATE ".X_PREFIX."forums SET moderator='$parts' WHERE fid={$list['fid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT userlist, fid FROM ".X_PREFIX."forums WHERE (userlist REGEXP '(^|(,))()*$dbregexuserfrom()*((,)|$)')");
        while($list = $db->fetch_array($query)) {
            $db->escape_fast($list['userlist']);
            $parts = array_unique(array_map('trim', explode(',', $list['userlist'])));
            $index = array_search($dbuserfrom, $parts);
            $parts[$index] = $dbuserto;
            $parts = implode(', ', $parts);
            $db->query("UPDATE ".X_PREFIX."forums SET userlist='$parts' WHERE fid={$list['fid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT fid, lastpost FROM ".X_PREFIX."forums WHERE lastpost LIKE '%|$dblikeuserfrom|%'");
        while($result = $db->fetch_array($query)) {
            $db->escape_fast($result['lastpost']);
            $newlastpost = str_replace("|$dbuserfrom|", "|$dbuserto|", $result['lastpost']);
            $db->query("UPDATE ".X_PREFIX."forums SET lastpost='$newlastpost' WHERE fid={$result['fid']}");
        }
        $db->free_result($query);

        $query = $db->query("SELECT tid, lastpost FROM ".X_PREFIX."threads WHERE lastpost LIKE '%|$dblikeuserfrom|%'");
        while($result = $db->fetch_array($query)) {
            $db->escape_fast($result['lastpost']);
            $newlastpost = str_replace("|$dbuserfrom|", "|$dbuserto|", $result['lastpost']);
            $db->query("UPDATE ".X_PREFIX."threads SET lastpost='$newlastpost' WHERE tid={$result['tid']}");
        }
        $db->free_result($query);

        return (($self['username'] == $userfrom) ? $lang['admin_rename_warn_self'] : '') . $lang['admin_rename_success'];
    }

    /**
     * check_restricted()
     *
     * Duplicates some logic in member.php.
     *
     * @since 1.9.1
     * @param string $userto Username to check
     * @return bool Username validity.
     */
    private function check_restricted(string $userto): bool
    {
        global $db;

        $nameokay = true;

        if ($userto != preg_replace('#[\]\'\x00-\x1F\x7F<>\\\\|"[,@]|  #', '', $userto)) {
            return false;
        }

        $query = $db->query("SELECT * FROM ".X_PREFIX."restricted");
        while($restriction = $db->fetch_array($query)) {
            if ('0' === $restriction['case_sensitivity']) {
                $t_username = strtolower($userto);
                $restriction['name'] = strtolower($restriction['name']);
            }

            if ('1' === $restriction['partial']) {
                if (strpos($t_username, $restriction['name']) !== false) {
                    $nameokay = false;
                }
            } else {
                if ($t_username === $restriction['name']) {
                    $nameokay = false;
                }
            }
        }
        $db->free_result($query);

        return $nameokay;
    }
}

/**
 * The admin panel template
 *
 * @since 1.9.1
 */
function displayAdminPanel()
{
    // moved to templates/admin_panel.php
}

/**
 * Provides HTML attributes for use with printsetting1().
 *
 * @since 1.9.8
 */
function settingHTML(string $setting, string &$on, string &$off)
{
    $on = $off = '';
    switch(vars()->settings[$setting]) {
        case 'on':
            $on = vars()::selHTML;
            break;
        default:
            $off = vars()::selHTML;
            break;
    }
}

/**
 * On/Off drop down control.
 *
 * @since 1.5.0
 */
function printsetting1($setname, $varname, $check1, $check2)
{
    global $lang, $THEME;

    ?>
    <tr class="tablerow">
    <td bgcolor="<?php echo $THEME['altbg1']?>" valign="top"><?php echo $setname?></td>
    <td bgcolor="<?php echo $THEME['altbg2']?>">
    <select name="<?php echo $varname?>">
    <option value="on" <?php echo $check1?>><?php echo $lang['texton']?></option>
    <option value="off" <?php echo $check2?>><?php echo $lang['textoff']?></option>
    </select>
    </td>
    </tr>
    <?php
}

/**
 * Single line text control.
 *
 * @since 1.5.0
 */
function printsetting2($setname, $varname, $value, $size)
{
    global $THEME;

    ?>
    <tr class="tablerow">
    <td bgcolor="<?php echo $THEME['altbg1']?>" valign="top"><?php echo $setname?></td>
    <td bgcolor="<?php echo $THEME['altbg2']?>"><input type="text" size="<?php echo $size?>" value="<?php echo $value?>" name="<?php echo $varname?>" /></td>
    </tr>
    <?php
}

/**
 * Drop down list or multi-select control.
 *
 * @since 1.9.1
 */
function printsetting3($setname, $boxname, $varnames, $values, $checked, $multi = true)
{
    global $THEME, $selHTML;

    foreach($varnames as $key=>$val) {
        if (isset($checked[$key]) && $checked[$key] !== true) {
            $optionlist[] = '<option value="'.$values[$key].'">'.$varnames[$key].'</option>';
        } else {
            $optionlist[] = '<option value="'.$values[$key].'" '.$selHTML.'>'.$varnames[$key].'</option>';
        }
    }
    $optionlist = implode("\n", $optionlist);
    ?>
    <tr class="tablerow">
    <td bgcolor="<?php echo $THEME['altbg1']?>" valign="top"><?php echo $setname?></td>
    <td bgcolor="<?php echo $THEME['altbg2']?>"><select <?php echo ($multi ? 'multiple="multiple"' : '')?> name="<?php echo $boxname?><?php echo ($multi ? '[]' : '')?>"><?php echo $optionlist?></select></td>
    </tr>
    <?php
}

/**
 * Multi-line text control.
 *
 * @since 1.9.4
 */
function printsetting4($settingDesc, $name, $value, $rows = 5, $cols = 50)
{
    global $THEME;

    ?>
    <tr class="tablerow">
    <td bgcolor="<?php echo $THEME['altbg1']?>" valign="top"><?php echo $settingDesc?></td>
    <td bgcolor="<?php echo $THEME['altbg2']?>"><textarea rows="<?php echo $rows; ?>" name="<?php echo $name; ?>" cols="<?php echo $cols; ?>">
<?php // Linefeed required here - Do not edit!
    echo $value;
    ?></textarea></td>
    </tr>
    <?php
}

/**
 * Table row with plain text or raw HTML instead of a specific input control.
 *
 * @since 1.9.11
 */
function printsetting5($settingDesc, $errorMsg)
{
    global $THEME;

    ?>
    <tr class="tablerow">
    <td bgcolor="<?php echo $THEME['altbg1']?>" valign="top"><?php echo $settingDesc; ?></td>
    <td bgcolor="<?php echo $THEME['altbg2']?>"><?php echo $errorMsg; ?></td>
    </tr>
    <?php
}

/**
 * Take string input and save it to settings.
 *
 * @since 1.9.12
 * @param string $dbname     The name of the setting as saved in the database.
 * @param string $postname   The HTML input name.
 * @param bool   $htmlencode Optional. Whether to escape HTML special chars. Usually true.
 */
function input_string_setting(string $dbname, string $postname, bool $htmlencode = true)
{
    $value = core()->postedVar($postname, '', $htmlencode, false);
    input_custom_setting($dbname, $value);
}

/**
 * Take integer input and save it to settings.
 *
 * @since 1.9.12
 * @param string $dbname The name of the setting as saved in the database.
 * @param string $postname The HTML input name.
 */
function input_int_setting(string $dbname, string $postname)
{
    $value = (string) formInt($postname);
    input_custom_setting($dbname, $value);
}

/**
 * Take on/off input and save it to settings.
 *
 * @since 1.9.12
 * @param string $dbname The name of the setting as saved in the database.
 * @param string $postname The HTML input name.
 */
function input_onoff_setting(string $dbname, string $postname)
{
    $value = formOnOff($postname);
    input_custom_setting($dbname, $value);
}

/**
 * Take a string variable and save it to settings.
 *
 * @since 1.9.12
 * @param string $dbname The name of the setting as saved in the database.
 * @param string $value
 */
function input_custom_setting(string $dbname, string $value)
{
    if (! isset(vars()->settings[$dbname])) {
        sql()->addSetting($dbname, $value);
    } else if vars()->settings[$dbname] !== $value) {
        sql()->updateSetting($dbname, $value);
    }
}

function readFileAsINI($filename)
{
    $lines = file($filename);
    foreach($lines as $line_num => $line) {
        $temp = explode("=",$line);
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
function dump_query($resource, $header = true)
{
    global $altbg2, $altbg1, $db, $cattext;
    if (!$db->error()) {
        $count = $db->num_fields($resource);
        if ($header) {
            ?>
            <tr class="category" bgcolor="<?php echo $altbg2?>" align="center">
            <?php
            for($i=0;$i<$count;$i++) {
                echo '<td align="left">';
                echo '<strong><font color='.$cattext.'>'.$db->field_name($resource, $i).'</font></strong>';
                echo '</td>';
            }
            echo '</tr>';
        }

        while($a = $db->fetch_array($resource, $db::SQL_NUM)) {
            ?>
            <tr bgcolor="<?php echo $altbg1?>" class="ctrtablerow">
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
    } else {
        error($db->error());
    }
}

return;
