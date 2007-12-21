<?php
/* $Id: sanitize.inc.php,v 1.7 2006/08/24 13:16:58 Tularis Exp $ */
/*
    XMB 1.10
    © 2001 - 2006 Aventure Media & The XMB Development Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

    This file incorporates / is based on software from OWASP (http://www.owasp.org/)

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

if(defined('CLEAN_GLOBALS') && CLEAN_GLOBALS === true) {
    function storeTmp() {
       static $static;
       if(func_num_args() == 0) {
          return $static;
       } else {
          $static = func_get_args();
       }
    }
    storeTmp(array('_POST'=>$_POST, '_GET'=>$_GET, '_COOKIE'=>$_COOKIE, '_SERVER'=>$_SERVER, '_ENV'=>$_ENV, '_SESSION'=>$_SESSION, '_FILES'=>$_FILES));
    unset($GLOBALS);
    list($ret) = storeTmp();
    foreach($ret as $key=>$val) {
            $$key = $val;
    }
}

define('X_POST', 1);
define('X_GET', 2);
define('X_COOKIE', 4);
define('X_SESSION', 8);
define('X_SERVER', 16);
define('X_ENV', 32);
define('X_FILES', 64);

if((bool) ini_get('register_gobals') == true) {
    define('REGISTER_GLOBALS', true);
} else {
    define('REGISTER_GLOBALS', false);
}

if((bool) ini_get('magic_quotes_gpc') == true) {
    define('MAGIC_QUOTES_GPC', true);
} else {
    define('MAGIC_QUOTES_GPC', false);
}

if((bool) ini_get('magic_quotes_runtime') == true) {
    define('MAGIC_QUOTES_RUNTIME', true);
} else {
    define('MAGIC_QUOTES_RUNTIME', false);
}
function fetchFromRequest($str, $type) {
    if(($type & X_SERVER) == X_SERVER) {
        if(isset($_SERVER[$str])) {
            return $_SERVER[$str];
        }
    }
    if(($type & X_ENV) == X_ENV) {
        if(isset($_ENV[$str])) {
            return $_ENV[$str];
        }
    }
    if(($type & X_SESSION) == X_SESSION) {
        if(isset($_SESSION[$str])) {
            return $_SESSION[$str];
        }
    }
    if(($type & X_COOKIE) == X_COOKIE) {
        if(isset($_COOKIE[$str])) {
            return $_COOKIE[$str];
        }
    }
    if(($type & X_POST) == X_POST) {
        if(isset($_POST[$str])) {
            return $_POST[$str];
        }
    }
    if(($type & X_GET) == X_GET) {
        if(isset($_GET[$str])) {
            return $_GET[$str];
        }
    }

    if(($type & X_FILES) == X_FILES) {
        if(isset($_FILES[$str])) {
            return $_FILES[$str];
        }
    }

    return null;
}

function safeAddslashes($str, $type=X_RAW) {
    if($type == X_RAW) {
        // worry about magic_quotes_runtime ?
        if(MAGIC_QUOTES_RUNTIME) {
            return $str;
        } else {
            return addslashes($str);
        }
    } else {
        if(MAGIC_QUOTES_GPC) {
            return fetchFromRequest($str, $type);
        } else {
            return addslashes(fetchFromRequest($str, $type));
        }
    }
}

function safeString($str, $allowHTML=false, $regexp=false) {
    if($allowHTML === false) {
        $pattern[0] = '/\&/';
        $pattern[1] = '/</';
        $pattern[2] = "/>/";
        $pattern[3] = '/\n/';
        $pattern[4] = '/"/';
        $pattern[5] = "/'/";
        $pattern[6] = "/%/";
        $pattern[7] = '/\(/';
        $pattern[8] = '/\)/';
        $pattern[9] = '/\+/';
        $pattern[10] = '/-/';
        $replacement[0] = '&amp;';
        $replacement[1] = '&lt;';
        $replacement[2] = '&gt;';
        $replacement[3] = '<br />';
        $replacement[4] = '&quot;';
        $replacement[5] = '&#39;';
        $replacement[6] = '&#37;';
        $replacement[7] = '&#40;';
        $replacement[8] = '&#41;';
        $replacement[9] = '&#43;';
        $replacement[10] = '&#45;';

        $str = preg_replace($pattern, $replacement, $str);
    }

    if($regexp !== false) {
        if(preg_match($str, $regexp)) {
            return $str;
        }
    } else {
        return $str;
    }

    return '';
}

function safeSQLString($str) {

}

function safeInt($value, $min=false, $max=false) {
    if(is_numeric($value)) {
        $value = (int) $value;
        if($max === false || $max >= $value) {
            if($min === false || $value >= $min) {
                return $value;
            }
        }
    }

    return 0;
}

function safeFloat($float, $min=false, $max=false) {
    if(is_numeric($value)) {
        $value = (float) $float;
        if($max === false || $max > $value) {
            if($min === false || $value > $min) {
                return $value;
            }
        }
    }

    return 0;
}

function safeYesNo($str, $default='no') {
    // expects a string
    if(safeString($str, false, '^[a-z]+$') === 'yes') {
        return 'yes';
    } else {
        return 'no';
    }
}

function safeBit($bit) {
    if($bit) {
        return 1;
    } else {
        return 0;
    }
}

function safeArray($arrayKey, $type=X_POST, $dataType=X_STRING, $flags=array()) {
    $request = fetchFromRequest($arrayKey, $type);
    switch($dataType) {
        case X_STRING:
            $return = array();
            foreach($request as $key=>$val) {
                $return[$key] = safeString($val, $flags);
            }
            break;

        case X_INT:
            if(isset($flags['min'])) {
                $min = $flags['min'];
            } else {
                $min = false;   // min. int size
            }
            if(isset($flags['max'])) {
                $min = $flags['max'];
            } else {
                $min = false;   // max. int size
            }
            $return = array();
            foreach($request as $key=>$val) {
                $return[$key] = safeInt($val, $min, $max);
            }
            break;

        case X_FLOAT:
            if(isset($flags['min'])) {
                $min = $flags['min'];
            } else {
                $min = false;   // min. int size
            }
            if(isset($flags['max'])) {
                $min = $flags['max'];
            } else {
                $min = false;   // max. int size
            }
            $return = array();
            foreach($request as $key=>$val) {
                $return[$key] = safeFloat($val, $min, $max);
            }
            break;
    }

    return $return;
}
?>