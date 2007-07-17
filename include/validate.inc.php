<?php
/**
 * XMB 1.9.8 Engage Final
 *
 * Developed By The XMB Group
 * Copyright (c) 2001-2007, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * Copyright (c) 2007, iEntry, Inc.
 * http://www.ientry.com
 *
 * Changes to port UltimaBB 1.0's validation routines
 *
 * This code is from UltimaBB. The (C) notice is as follows:
 *
 * UltimaBB
 * Copyright (c) 2004 - 2007 The UltimaBB Group
 * http://www.ultimabb.com
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 **/

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

/**
* CSRF protection class. Call this to obtain and test a page token.
*
* In UltimaBB 1.0, each user has a single token per page no matter which destination
* action. These should be used for all actions. UltimaBB 1.1 will extend this to include
* unique tokens per action, making it much harder for attackers to spoof any particular
* action.
*
* As each page has many old and new, and only one token slot in the session,
* there is a way to re-seed the session.
* @author   ajv
* @package UltimaBB
* @license  GPL
*/
class page_token {
    /**
    * @access private
    * @var mixed
    */
    var $pageToken;

    /**
    * @access private
    * @var mixed
    */
    var $sessionToken;

    /**
    * @access public
    * @var string
    */
    var $newToken;

    /**
    * Initialization of the class
    *
    * Sets all the class variables to their needed values
    */
    function init() {
        $this->pageToken = $this->get_page_token();
        $this->sessionToken = $this->get_session_token();
        $this->newToken = md5(sha1(uniqid(rand(), true)));
        $this->set_session_token($this->newToken);
    }

    /**
    * Sets the 'token' SESSION variable
    *
    * @param   string   $token   the token to set the 'token' variable as
    * @return   string   the token that was retrieved
    */
    function set_session_token($token) {
        $_SESSION['token'] = $token;
        return $token;
    }

    /**
    * Retrieves the 'token' REQUEST variable
    *
    * @return   string   the token that was retrieved
    */
    function get_page_token() {
        return getRequestVar('token');
    }

    /**
    * Retrieves the 'token' SESSION variable
    *
    * @return   mixed   the token that was retrieved if it's set, false otherwise
    */
    function get_session_token() {
        return (isset($_SESSION['token'])) ? $_SESSION['token'] : false;
    }

    /**
    * Retrieves the a new token generated at initialization
    *
    * @return   string   the new token
    */
    function get_new_token() {
        return $this->newToken;
    }

    /**
    * Checks for valid token. Error's if there is not one.
    *
    * @return   boolean   true no matter what
    */
    function assert_token() {
        global $lang;

        if ($this->sessionToken === false || $this->pageToken === false || $this->sessionToken !== $this->pageToken) {
            error($lang['textnoaction'], false);
        }
        // This old token has been used - prevent reuse
        $this->sessionToken = false;
        $this->pageToken = false;
        return true;
    }
}

/**
* Checks if the supplied email address is valid
*
* @return   boolean   true if the e-mail address is valid, false otherwise
*/
function isValidEmail($addr) {
    $emailPattern = "^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";
    $emailValid = false;

    if (eregi($emailPattern, $addr)) {
        // Under Windows, PHP does not possess getmxrr(), so we skip it
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $emailValid = true;
            break;
        }

        $user = '';
        $domain = '';
        list($user, $domain) = split('@', $addr);

        // Check if the site has an MX record. We can't send unless there is.
        $mxrecords = '';
        if (getmxrr($domain, $mxrecords)) {
            $emailValid = true;
        }
    }
    return $emailValid;
}

/**
* Has the named submit button been invoked?
*
* Looks in the form post data for a named submit
*
* @return   boolean   true if the submit is present, false otherwise
*/
function onSubmit($submitname) {
    $retval = (isset($_POST[$submitname]) && !empty($_POST[$submitname]));

    if (!$retval) {
        $retval = (isset($_GET[$submitname]) && !empty($_GET[$submitname]));
    }

    return($retval);
}

/**
* Is the forum being viewed?
*
* Looks for pre-form post data for a named submit
*
* @return   boolean   true if the no submit is present, false otherwise
*/
function noSubmit($submitname) {
    return (!onSubmit($submitname));
}

/**
* Retrieve a POST variable and sanitizes it
*
* @param   string   $varname   name of the variable in $_POST
* @param   boolean   $striptags   do a striptags to remove HTML tags
* @param   boolean   $quotes   do a htmlspecialchars to sanitize input for XSS
* @return   string   the "safe" string if the variable is available, empty otherwise
*/
function formVar($varname, $striptags = true, $quotes = false) {
    $retval = '';
    if (isset($_POST[$varname]) && $_POST[$varname] !== '') {
        $retval = trim($_POST[$varname]);
        if ($striptags) {
            $retval = strip_tags($retval);
        }

        if ($quotes) {
            $retval = htmlspecialchars($retval, ENT_QUOTES);
        } else {
            $retval = htmlspecialchars($retval, ENT_NOQUOTES);
        }
    }
    return $retval;
}

/**
* Retrieve the contents of an array from a POST
*
* This function will attempt to retrieve a named array($varname)
* and sanitize it based upon type($type). If a string, $striptags indicates
* if striptags should be used, and $quotes is only enabled when you want it
*
* This function always returns an array. It will be an empty array if there's
* no data or variable to be returned.
*
* @param   string   $varname   name of the variable in $_POST
* @param   boolean   $striptags   strings only: do a striptags to remove HTML tags
* @param   boolean   $quotes   strings only: do a htmlspecialchars to sanitize input for XSS
* @param   string   $type   'string' or 'int' to specify what needs to be done to the values
* @return   array   the array found for $varname, empty otherwise
*/
function formArray($varname, $striptags = true, $quotes = false, $type = 'string') {
    $arrayItems = array();
    // Convert a single or comma delimited list to an array
    if (isset($_POST[$varname]) && !is_array($_POST[$varname])) {
        if (strpos($_POST[$varname], ',') !== false) {
            $_POST[$varname] = explode(',', $_POST[$varname]);
        } else {
            $_POST[$varname] = array($_POST[$varname]);
        }
    }

    if (isset($_POST[$varname]) && is_array($_POST[$varname]) && count($_POST[$varname]) > 0) {
        $arrayItems = $_POST[$varname];
        foreach ($arrayItems as $item => $theObject) {
            $theObject = & $arrayItems[$item];
            switch ($type) {
                case 'int':
                    $theObject = intval($theObject);
                    break;
                case 'string':
                default:
                    if ($striptags) {
                        $theObject = strip_tags($theObject);
                    }
                    if ($quotes) {
                        $theObject = htmlspecialchars($theObject, ENT_QUOTES);
                    } else {
                        $theObject = htmlspecialchars($theObject, ENT_NOQUOTES);
                    }
                    break;
            }
            unset($theObject);
        }
    }
    return $arrayItems;
}

/**
* Retrieve a GET variable and sanitize it
*
* @param   string   $varname   name of the variable in $_GET
* @param   boolean   $striptags   do a striptags to remove HTML tags
* @param   boolean   $quotes   do a htmlspecialchars to sanitize input for XSS
* @return   string   the "safe" string if the variable is available, empty otherwise
*/
function getVar($varname, $striptags = true, $quotes = true) {
    $retval = '';
    if (isset($_GET[$varname]) && $_GET[$varname] !== '') {
        $retval = urldecode(trim($_GET[$varname]));
        if ($striptags) {
            $retval = strip_tags($retval);
        }

        if ($quotes) {
            $retval = htmlspecialchars($retval, ENT_QUOTES);
        }
    }
    return $retval;
}

/**
* Retrieve a GET integer and sanitize it
*
* @param   string   $varname   name of the variable in $_GET
* @return   integer   the "safe" integer if the variable is available, zero otherwise
*/
function getInt($varname) {
    $retval = 0;
    if (isset($_GET[$varname]) && is_numeric($_GET[$varname])) {
        $retval = (int) $_GET[$varname];
    }
    return $retval;
}

/**
* Retrieve a REQUEST integer and sanitize it
*
* @param   string   $varname   name of the variable in $_REQUEST
* @return   integer   the "safe" integer if the variable is available, zero otherwise
*/
function getRequestInt($varname) {
    $retval = 0;
    if (isset($_REQUEST[$varname]) && is_numeric($_REQUEST[$varname])) {
        $retval = intval($_REQUEST[$varname]);
    }
    return $retval;
}

/**
* Retrieve a REQUEST variable
*
* @param   string   $varname   name of the variable in $_REQUEST
* @return   mixed   the value of $varname, false otherwise
*/
function getRequestVar($varname) {
    return (isset($_REQUEST[$varname])) ? $_REQUEST[$varname] : false;
}

/**
* Retrieve a POST integer and sanitize it
*
* @param   string   $varname   name of the variable in $_POST
* @param   boolean   $setZero   should the return be set to zero if the variable doesnt exist?
* @return   mixed   the "safe" integer or zero, empty string otherwise
*/
function formInt($varname, $setZero = true) {
    if ($setZero) {
        $retval = 0;
    } else {
        $retval = '';
    }

    if (isset($_POST[$varname]) && is_numeric($_POST[$varname])) {
        $retval = (int) $_POST[$varname];
    }
    return $retval;
}

/**
* Return the array associated with varname
*
* This function interrogates the POST variable(form) for an
* array of inputs submitted by the user. It checks that it exists
* and returns false if no elements or not existent, and an array of
* one or more integers if it does exist.
*
* @param   string   $varname   the form field to find and sanitize
* @return   mixed   false if not set or no elements, an array() of integers otherwise
*/
function getFormArrayInt($varname, $doCount = true) {
    if (!isset($_POST[$varname]) || empty($_POST[$varname])) {
        return false;
    }
    $retval = $_POST[$varname];

    if ($doCount) {
        if (count($retval) == 1) {
            $retval = array($retval);
        }
    }

    foreach ($retval as $bits => $value) {
        $value = intval($value);
    }
    return $retval;
}

/**
* Sanitizes a integer
*
* @param   string   $varname   name of the variable
* @return   integer   the "safe" integer if available, zero otherwise
*/
function valInt($varname) {
    $retval = 0;
    if (isset($varname) && is_numeric($varname)) {
        $retval = (int) $varname;
    }
    return $retval;
}

/**
* Retrieve a POST variable and check it for on value
*
* @param   string   $varname   name of the variable in $_POST
* @return   string   on if set to on, off otherwise
*/
function formOnOff($varname) {
    if (isset($_POST[$varname]) && strtolower($_POST[$varname]) == 'on') {
        return 'on';
    }
    return 'off';
}

/**
* Retrieve a POST variable and check it for yes value
*
* @param   string   $varname   name of the variable in $_POST
* @return   string   yes if set to yes, no otherwise
*/
function formYesNo($varname) {
    if (isset($_POST[$varname]) && strtolower($_POST[$varname]) == 'yes') {
        return 'yes';
    }
    return 'no';
}

/**
* Retrieve a POST variable and check it for yes value
*
* @param   string   $varname   name of the variable
* @param   boolean   $glob   is this variable also a global?
* @return   string   yes if set to yes, no otherwise
*/
function valYesNo($varname, $glob = true) {
    if ($glob) {
        global $varname;
    }

    if (isset($varname) && strtolower($varname) == 'yes') {
        return 'yes';
    }
    return 'no';
}

/**
* Sanitizes a POST integer and checks it for 1 value
*
* @param   string   $varname   name of the variable in $_POST
* @return   integer   1 if set to 1, 0 otherwise
*/
function form10($varname) {
    return(formInt($varname) == 1) ? 1 : 0;
}

/**
* Retrieve a POST boolean variable and check it for true value
*
* @param   string   $varname   name of the variable in $_POST
* @return   boolean   true if set to true, false otherwise
*/
function formBool($varname) {
    if (isset($_POST[$varname]) && strtolower($_POST[$varname]) == "true") {
        return 'true';
    }
    return 'false';
}

/**
* Check if a variable is checked
*
* @param   string   $varname   name of the variable
* @param   string   $compare   is $compare equal to $varname?
* @return   string   checked html if $compare is equal to $varname, empty otherwise
*/
function isChecked($varname, $compare = 'yes') {
    return(($varname == $compare) ? 'checked="checked"' : '');
}

/**
* Clean up some HTML
*
* @param   array   $matches   matches from preg_replace_callback in checkInput()
* @return   string   the "safe" string
*/
function cleanHtml($matches) {
    $string = htmlentities($matches[0], ENT_QUOTES);
    return $string;
}


function encode_ip($dotquad_ip) {
    $ip_sep = explode('.', $dotquad_ip);
    return sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
}

function createLangFileSelect($currentLangFile) {
    $lfs = array();
    $dir = opendir(ROOT.'lang/');
    while ($file = readdir($dir)) {
        if (is_file(ROOT.'lang/'.$file) && false !== strpos($file, '.lang.php')) {
            $file = str_replace('.lang.php', '', $file);
            if ($file == $currentLangFile) {
                $lfs[] = '<option value="'.md5($file).'" selected="selected">'.$file.'</option>';
            } else {
                $lfs[] = '<option value="'.md5($file).'">'.$file.'</option>';
            }
        }
    }
    natcasesort($lfs);
    return '<select name="langfilenew">'.implode("\n", $lfs).'</select>';
}

function getLangFileNameFromHash($ordinal) {
    global $member;

    $dir = opendir(ROOT.'lang/');
    while ($file = readdir($dir)) {
        if (is_file(ROOT.'lang/'.$file) && false !== strpos($file, '.lang.php')) {
            $file = str_replace('.lang.php', '', $file);
            if (md5($file) == $ordinal) {
                return $file;
            }
        }
    }
    return false;
}

function isValidFilename($filename) {
    return preg_match('#^[^:\\/?*<>|]+$#', trim($filename));
}
?>