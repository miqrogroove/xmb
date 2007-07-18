<?php
/* $Id: xmb.php,v 1.3.2.3 2005/09/21 17:11:26 Tularis Exp $ */
/*
    XMB 1.9.2
    © 2001 - 2005 Aventure Media & The XMB Development Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// This makes XMB compatible with the latest PHP changes (4.2.*) (mainly 4.2.1 and 4.2.2)
if (!isset($_SERVER)) {
    $_GET        = &$HTTP_GET_VARS;
    $_POST        = &$HTTP_POST_VARS;
    $_ENV        = &$HTTP_ENV_VARS;
    $_SERVER    = &$HTTP_SERVER_VARS;
    $_COOKIE    = &$HTTP_COOKIE_VARS;
    $_FILES        = &$HTTP_POST_FILES;
    $_REQUEST    = array_merge($_GET, $_POST, $_COOKIE);
}

$global = @array(0 => $_GET, 1 => $_POST, 2 => $_ENV, 3=> $_COOKIE, 4=> $_SESSION, 5 => $_SERVER, 6 => $_FILES);

// make sure magic_quotes_runtime doesn't kill XMB
@set_magic_quotes_runtime(0);

if (get_magic_quotes_gpc() === 0) {
    foreach ($global as $keyg => $valg) {
        if (is_array($valg)) {
            foreach ($valg as $keya => $vala) {
                if (is_array($vala)) {
                    foreach ($vala as $keyv => $valv) {
                        if (gettype($valv) == "string") {
                            $global[$keyg][$keya][$keyv] = addslashes($valv);
                        }
                    }
                }
                elseif (gettype($vala) == "string") {
                    $global[$keyg][$keya] = addslashes($vala);
                }
            }
        }
    }
    foreach ($global as $num => $array) {
        if (is_array($array)) {
            extract($array, EXTR_OVERWRITE);
        }
    }
}
else {
    foreach ($global as $num => $array) {
        if (is_array($array)) {
            extract($array, EXTR_OVERWRITE);
        }
    }
}
?>