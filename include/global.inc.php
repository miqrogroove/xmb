<?php
/**
 * eXtreme Message Board
 * XMB 1.9.8 Engage Final SP2
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2008, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * Copyright (c) 2007, iEntry, Inc.
 * http://www.ientry.com
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 **/

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

// This makes XMB compatible with the latest PHP changes (4.2.*) (mainly 4.2.1 and 4.2.2)
if (!isset($_SERVER)) {
    $_GET = &$HTTP_GET_VARS;
    $_POST = &$HTTP_POST_VARS;
    $_ENV = &$HTTP_ENV_VARS;
    $_SERVER = &$HTTP_SERVER_VARS;
    $_COOKIE = &$HTTP_COOKIE_VARS;
    $_FILES = &$HTTP_POST_FILES;
    $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);
}

$global = @array(0 => $_GET, 1 => $_POST, 2 => $_ENV, 3=> $_COOKIE, 4=> $_SESSION, 5 => $_SERVER, 6 => $_FILES, 7 => &$_REQUEST);

// make sure magic_quotes_runtime doesn't kill XMB
@set_magic_quotes_runtime(0);
if (get_magic_quotes_gpc() === 0) {
    foreach($global as $keyg => $valg) {
        if (is_array($valg)) {
            foreach($valg as $keya => $vala) {
                if (is_array($vala)) {
                    foreach($vala as $keyv => $valv) {
                        if (gettype($valv) == "string") {
                            $global[$keyg][$keya][$keyv] = addslashes($valv);
                        }
                    }
                } else if (gettype($vala) == "string") {
                    $global[$keyg][$keya] = addslashes($vala);
                }
            }
        }
    }

    foreach($global as $num => $array) {
        if (is_array($array)) {
            extract($array, EXTR_OVERWRITE);
        }
    }
} else {
    foreach($global as $num => $array) {
        if (is_array($array)) {
            extract($array, EXTR_OVERWRITE);
        }
    }
}
?>