<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12-alpha  Do not use this experimental software after 1 October 2020.
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2020, The XMB Group
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
 *
 **/

define('IN_CODE', TRUE);
define('ROOT', '../');
define('X_SET_HEADER', 1);
define('X_SET_JS', 2);

require ROOT.'config.php';

require ROOT.'include/validate.inc.php';

$xmbuser = postedVar('xmbuser', '', TRUE, FALSE);

if (strlen($xmbuser) == 0) {
    ?>
    <form method="post" action="">
        <label>Username: <input type="text" name="xmbuser" /></label><br />
        <label>Password: <input type="password" name="xmbpw" /></label><br />
        <input type="submit" />
    </form>
    <?php
} else {

    $array = parse_url($full_url);

    $cookiesecure = ($array['scheme'] == 'https');

    $cookiedomain = $array['host'];
    if (strpos($cookiedomain, '.') === FALSE || preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $cookiedomain)) {
        $cookiedomain = '';
    } elseif (substr($cookiedomain, 0, 4) === 'www.') {
        $cookiedomain = substr($cookiedomain, 3);
    }

    if (!isset($array['path'])) {
        $array['path'] = '/';
    }
    $cookiepath = $array['path'];


    require ROOT.'include/functions.inc.php';

    $currtime = 0;
    put_cookie("xmbuser", $xmbuser, $currtime);
    put_cookie("xmbpw", md5($_POST['xmbpw']), $currtime);

    echo "Cookies set.  <a href='{$full_url}upgrade/'>Return to upgrade.</a>";
}

?>
