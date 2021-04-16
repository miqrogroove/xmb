<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2021, The XMB Group
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

if (!defined('IN_CODE')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

/**
 * Sets up some extra variables after a new login.
 *
 * @param  bool   $invisible    Optional.
 */
function loginUser($invisible = null) {
    global $self, $session, $db, $lastvisit;

    if ( $session->getStatus() == 'good' ) {
        $dbname = $db->escape($self['username']);

        if (!is_null($invisible)) {
            if ( $invisible && '0' === $self['invisible'] ) {
                $db->query("UPDATE ".X_PREFIX."members SET invisible='1' WHERE username='$dbname'");
                $self['invisible'] = 1;
            } elseif ( !$invisible && '1' === $self['invisible'] ) {
                $db->query("UPDATE ".X_PREFIX."members SET invisible='0' WHERE username='$dbname'");
                $self['invisible'] = 0;
            }
        }

        // These cookies were already set in header.php, but PHP is smart enough to overwrite them.
        put_cookie('xmblvb', $self['lastvisit'], (time() + X_ONLINE_TIMER)); // lvb == last visit
        $lastvisit = $self['lastvisit']; // Used by forumdisplay
    }
}

/**
 * Responsible for setting up session variables.
 *
 * @param  int    $force_inv Optional.
 * @param  string $serror Optional. Informs this function if any session errors occurred before authenticating.
 * @return bool
 */
function elevateUser($force_inv = false, $serror = '') {
    global $xmbuser, $self, $session, $db, $SETTINGS, $status_enum, $onlinetime;

    $maxurl = 150; //Schema constant.

    $state = $session->getStatus();

    //Usernames are historically html encoded in the XMB database, as well as in cookies.
    //$xmbuser is often used as a raw value in queries and should be sql escaped.
    //$self['username'] is a good alternative for future template use.
    //$xmbpw was historically abused and will no longer contain a value.

    if ( 'good' == $state || 'already-logged-in' == $state ) {
        // 'good' means normal login or resumed session.
        // 'already-logged-in' is a soft error that might result from login races or multiple open tabs.
        $self = $session->getMember();
        $xmbuser = $db->escape( $self['username'] );
    } else {
        $self = array();
        $self['status'] = '';
        $xmbuser = '';
    }

    $self['password'] = '';

    // Initialize the new translation system
    if (X_SCRIPT != 'upgrade.php') {
        $success = false;
        if (!empty($self['langfile'])) {
            $success = loadLang($self['langfile']);
        }
        if (!$success) {
            $success = loadLang($SETTINGS['langfile']);
        }
        if (!$success) {
            require_once(ROOT.'include/translation.inc.php');
            langPanic();
        }
    }

    // Set the user status constants.
    if ($xmbuser != '') {
        if (!defined('X_GUEST')) {
            define('X_MEMBER', TRUE);
            define('X_GUEST', FALSE);
        }
        // Save some write locks by updating in 60-second intervals.
        if (abs(time() - (int)$self['lastvisit']) > 60) {
            \XMB\SQL\setLastvisit( $self['username'], $onlinetime );
            // Important: Don't update $self['lastvisit'] until the next hit, otherwise we won't actually know when the last visit happened.
        }
    } else {
        if (!defined('X_GUEST')) {
            define('X_MEMBER', FALSE);
            define('X_GUEST', TRUE);
        }
    }

    // Enumerate status
    if (isset($status_enum[$self['status']])) {
        $int_status = $status_enum[$self['status']];
    } else {
        $int_status = $status_enum['Member']; // If $self['status'] contains an unknown value, default to Member.
    }

    if (!defined('X_STAFF')) {
        define('X_SADMIN', ($self['status'] == 'Super Administrator'));
        define('X_ADMIN', ($int_status <= $status_enum['Administrator']));
        define('X_SMOD', ($int_status <= $status_enum['Super Moderator']));
        define('X_MOD', ($int_status <= $status_enum['Moderator']));
        define('X_STAFF', X_MOD);
    }

    // Set more globals
    global $timeoffset, $themeuser, $status, $tpp, $ppp, $memtime, $dateformat,
           $sig, $invisible, $timecode, $dformatorig, $onlineuser;

    if ($xmbuser != '') {
        $timeoffset = $self['timeoffset'];
        $themeuser = $self['theme'];
        $status = $self['status'];
        $tpp = (int) $self['tpp'];
        $ppp = (int) $self['ppp'];
        $memtime = (int) $self['timeformat'];
        if ($self['dateformat'] != '') {
            $dateformat = $self['dateformat'];
        }
        $sig = $self['sig'];
        $invisible = $self['invisible'];
        $onlineuser = $xmbuser;
    } else {
        $timeoffset = $SETTINGS['def_tz'];
        $themeuser = '';
        $status = 'member';
        $tpp = (int) $SETTINGS['topicperpage'];
        $ppp = (int) $SETTINGS['postperpage'];
        $memtime = (int) $SETTINGS['timeformat'];
        $sig = '';
        $invisible = 0;
        $onlineuser = 'xguest123';
        $self['ban'] = '';
        $self['sig'] = '';
        $self['uid'] = '0';
        $self['username'] = '';
    }

    if ($force_inv === TRUE) {
        $invisible = 1;
    }

    if ($memtime == 24) {
        $timecode = "H:i";
    } else {
        $timecode = "h:i A";
    }

    $dformatorig = $dateformat;
    $dateformat = str_replace(array('mm', 'MM', 'dd', 'DD', 'yyyy', 'YYYY', 'yy', 'YY'), array('n', 'n', 'j', 'j', 'Y', 'Y', 'y', 'y'), $dateformat);

    // Save This Session
    if ( X_SCRIPT != 'upgrade.php' && X_SCRIPT != 'css.php' && X_SCRIPT != 'files.php' && ( X_ADMIN || $serror == '' || $serror == 'guest' && X_MEMBER ) ) {
        global $onlineip, $url;

        $wollocation = substr($url, 0, $maxurl);
        $db->escape_fast($wollocation);
        $newtime = $onlinetime - X_ONLINE_TIMER;
        $db->query("DELETE FROM ".X_PREFIX."whosonline WHERE ((ip='$onlineip' && username='xguest123') OR (username='$xmbuser') OR (time < '$newtime'))");
        $db->query("INSERT INTO ".X_PREFIX."whosonline (username, ip, time, location, invisible) VALUES ('$onlineuser', '$onlineip', $onlinetime, '$wollocation', '$invisible')");
    }
}

/**
 * Determine if the authenticated user is allowed to access this website.
 *
 * @since 1.9.12
 * @param array $member The member's database record.
 * @return string Specific error codes, otherwise 'good'.
 */
function loginAuthorization( array $member ): string {
    global $serror;
    
    $guess_limit = 10;
    $admin_limit = 1000;
    $lockout_timer = 3600 * 2;
    
    if ($serror == 'ip' && $member['status'] != 'Super Administrator' && $member['status'] != 'Administrator') {
        return 'ip-banned';
    } else if ($member['status'] == 'Banned') {
        return 'member-banned';
    } else if ( (int) $member['bad_login_count'] >= $guess_limit && time() < (int) $member['bad_login_date'] + $lockout_timer ) {
        auditBadLogin( $member );
        if ( $member['status'] != 'Super Administrator' ) {
            return 'password-locked';
        } else if ( (int) $member['bad_login_count'] >= $admin_limit ) {
            return 'password-locked';
        } else {
            // Super Admin has partial immunity to mitigate denial of service.
            return 'good';
        }
    } else {
        return 'good';
    }
}

/**
 * Record a failed login attempt.
 *
 * @since 1.9.12
 * @param array $member The member's database record.
 */
function auditBadLogin( array $member ) {
    $guess_limit = 10;
    $lockout_timer = 3600 * 2;
    $reset_timer = 86400;

    if ( time() >= (int) $member['bad_login_date'] + $reset_timer ) {
        // Allowed less than 10 failures.  After 24 hours, reset.
        \XMB\SQL\resetLoginCounter( $member['username'], time() );
    } elseif ( (int) $member['bad_login_count'] >= $guess_limit && time() >= (int) $member['bad_login_date'] + $lockout_timer ) {
        // User had more than 10 failures and should be locked out.  After 2 hours, reset.
        \XMB\SQL\resetLoginCounter( $member['username'], time() );
    } else {
        $count = \XMB\SQL\raiseLoginCounter( $member['username'] );
        if ( $count == $guess_limit ) {
            // Email the Super Administrators about this.
            $lang2 = loadPhrases(array('charset','security_subject','login_audit_mail'));

            $mailquery = \XMB\SQL\getSuperEmails();
            foreach ( $mailquery as $admin ) {
                $translate = $lang2[$admin['langfile']];
                $adminemail = htmlspecialchars_decode($admin['email'], ENT_QUOTES);
                $name = htmlspecialchars_decode($member['username'], ENT_QUOTES);
                $body = "{$translate['login_audit_mail']}\n\n$name";
                xmb_mail( $adminemail, $translate['security_subject'], $body, $translate['charset'] );
            }
        }
    }
}

/**
 * Record a failed session hijack attempt.
 *
 * @since 1.9.12
 * @param array $member The member's database record.
 */
function auditBadSession( array $member ) {
    $reset_timer = 86400;
    
    if ( time() > (int) $member['bad_login_date'] + $reset_timer ) {
        \XMB\SQL\resetSessionCounter( $member['username'], time() );
    } else {
        $count = \XMB\SQL\raiseSessionCounter( $member['username'] );
    }
}

/**
 * Uses the new translation database to populate the old $lang and $langfile variables.
 *
 * @param string $devname Name specified by XMB for internal use (usually written in English).
 * @return bool
 */
function loadLang($devname = "English") {
    global $charset, $db, $lang, $langfile;

    $db->escape_fast($devname);

    // Query The Translation Database
    $sql = 'SELECT k.langkey, t.cdata '
         . 'FROM '.X_PREFIX.'lang_keys AS k '
         . 'LEFT JOIN '.X_PREFIX.'lang_text AS t USING (phraseid) '
         . 'INNER JOIN '.X_PREFIX.'lang_base AS b USING (langid) '
         . "WHERE b.devname = '$devname'";
    $result = $db->query($sql);

    // Load the $lang array.
    if ($db->num_rows($result) > 0) {
        $langfile = $devname;
        $lang = array();
        while($row = $db->fetch_array($result)) {
            $lang[$row['langkey']] = $row['cdata'];
        }
        $db->free_result($result);
        $charset = $lang['charset'];
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * Uses the new translation database to retrieve a single phrase in all available languages.
 *
 * @param array $langkeys Of strings, used as the $lang array key.
 * @return array Associative indexes lang_base.devname and lang_keys.langkey.
 */
function loadPhrases($langkeys) {
    global $db;

    $csv = "'".implode("', '", $langkeys)."'";

    // Query The Translation Database
    $sql = 'SELECT b.devname, t.cdata, k.langkey '
         . 'FROM '.X_PREFIX.'lang_base AS b'
         . ' LEFT JOIN '.X_PREFIX.'lang_text AS t USING (langid)'
         . ' INNER JOIN '.X_PREFIX.'lang_keys AS k USING (phraseid)'
         . "WHERE k.langkey IN ($csv)";
    $result = $db->query($sql);

    // Load the $lang array.
    if ($db->num_rows($result) > 0) {
        $phrases = array();
        while($row = $db->fetch_array($result)) {
            $phrases[$row['devname']][$row['langkey']] = $row['cdata'];
        }
        $db->free_result($result);
        return $phrases;
    } else {
        return FALSE;
    }
}

function nav($add=false, $raquo=true) {
    global $navigation;

    if (!$add) {
        $navigation = '';
    } else {
        $navigation .= ($raquo ? ' &raquo; ' : ''). $add;
    }
}

function template($name) {
    global $db, $comment_output;

    $db->escape_fast($name);

    if (($template = templatecache(X_CACHE_GET, $name)) === false) {
        $query = $db->query("SELECT template FROM ".X_PREFIX."templates WHERE name='$name'");
        if ($db->num_rows($query) == 1) {
            if (X_SADMIN && DEBUG) {
                trigger_error('Efficiency Notice: The template "'.$name.'" was not preloaded.', E_USER_NOTICE);
            }
            $gettemplate = $db->fetch_array($query);
            templatecache(X_CACHE_PUT, $name, $gettemplate['template']);
            $template = $gettemplate['template'];
        } else {
            if (X_SADMIN && DEBUG) {
                trigger_error('Efficiency Warning: The template "'.$name.'" could not be found.', E_USER_WARNING);
            }
        }
        $db->free_result($query);
    }

    // PHP will not strip slashes from mismatched quotes so we have to do it here first e.g. "Not \' Good"
    $template = str_replace("\\'","'", $template);

    if ($name != 'phpinclude' && $comment_output === true) {
        return "<!--Begin Template: $name -->\n$template\n<!-- End Template: $name -->";
    } else {
        return $template;
    }
}

function templatecache( int $type, string $name, string $data = '' ) {
    static $cache;

    switch($type) {
        case X_CACHE_GET:
            if (!isset($cache[$name])) {
                return false;
            } else {
                return $cache[$name];
            }
            break;
        case X_CACHE_PUT:
            $cache[$name] = $data;
            return true;
            break;
    }
}

function loadtemplates() {
    global $db;

    if ( func_num_args() < 1 ) {
        trigger_error( 'Not enough arguments given to loadtemplates()', E_USER_WARNING );
    } else {
        $namesarray = array_unique(array_merge(func_get_args(), array('header','error','message','footer','footer_querynum','footer_phpsql','footer_totaltime','footer_load')));
        $sql = "'".implode("', '", $namesarray)."'";
        $query = $db->query("SELECT name, template FROM ".X_PREFIX."templates WHERE name IN ($sql)");
        while($template = $db->fetch_array($query)) {
            templatecache(X_CACHE_PUT, $template['name'], $template['template']);
        }
        $db->free_result($query);
    }
}

/**
 * Get a template with the token filled in.
 *
 * @since 1.9.11.11
 * @param string $name   The template name.
 * @param string $action The action for which the token is valid.
 * @param string $id     The object for which the token is valid.
 * @param int    $ttl    Validity time in seconds.
 * @return string
 */
function template_secure( string $name, string $action, string $id, int $ttl ) {
    $token = \XMB\Token\create( $action, $id, $ttl );
    $placeholder = '<input type="hidden" name="token" value="" />';
    $replace = "<input type='hidden' name='token' value='$token' />";
    return str_replace( addslashes( $placeholder ), $replace, template( $name ) );
}

/**
 * Assert token validity for a user request.
 *
 * @since 1.9.11.11
 * @param string $action The action for which the token is valid.
 * @param string $id     The object for which the token is valid.
 * @param int    $expire Deprecated.
 * @param bool   $error_header Display header template on errors?
 */
function request_secure( string $action, string $id, int $expire = 0, bool $error_header = false ) {
    global $lang;

    if ( 0 != $expire ) {
        trigger_error( 'The $expire parameter of request_secure() does not work in this version of XMB.', E_USER_DEPRECATED );
    }

    $token = postedVar( 'token', '', false, false );

    if ( ! \XMB\Token\consume( $token, $action, $id ) ) {
        error( $lang['bad_token'], $error_header );
    }
}

/**
 * DEPRECATED by XMB 1.9.12
 *
 * template_key() is no longer needed because we can now store more information in the tokens table.
 */
function template_key($action, $id) {
    trigger_error( 'template_key() is deprecated in this version of XMB.', E_USER_DEPRECATED );

    $id_len = X_NONCE_KEY_LEN - strlen($action);
    if (strlen($id) > $id_len) {
        $id = substr($id, -$id_len);
    } else {
        $id = str_pad($id, $id_len, '0', STR_PAD_LEFT);
    }
    return $action . $id;
}

function censor($txt) {
    global $censorcache;

    $ignorespaces = TRUE;
    if (is_array($censorcache)) {
        if (count($censorcache) > 0) {
            $prevfind = '';
            foreach($censorcache as $find=>$replace) {
                if ($ignorespaces === true) {
                    $txt = str_ireplace($find, $replace, $txt);
                } else {
                    if ($prevfind == '') {
                        $prevfind = $find;
                    }
                    $txt = preg_replace("#(^|[^a-z])(".preg_quote($find)."|".preg_quote($prevfind).")($|[^a-z])#si", '\1'.$replace.'\3', $txt);
                    $prevfind = $find;
                }
            }
            if ($ignorespaces !== true) {
                $txt = preg_replace("#(^|[^a-z])(".preg_quote($find).")($|[^a-z])#si", '\1'.$replace.'\3', $txt);
            }
        }
    }

    return $txt;
}

/**
 * @since 1.9.1
 */
function smile(&$txt) {
    global $smiliesnum, $smiliecache, $THEME;

    if ( 0 == $smiliesnum ) {
        return true;
    }

    // Parse the input for HTML tags
    $pattern = "/(<[^>]*+>)/";
    $parts = preg_split( $pattern, $txt, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

    // Loop through the parts and avoid the HTML tags
    foreach ( $parts as &$part ) {
        if ( substr( $part, 0, 1 ) == '<' ) {
            continue;
        }
        
        foreach ( $smiliecache as $code => $url ) {
            // Most $part values won't contain any smilies, so optimize by writing new strings only when necessary.
            if ( false === strpos( $part, $code ) ) {
                continue;
            }
            $altcode = attrOut( $code );
            $part = str_replace( $code, "<img src='./{$THEME['smdir']}/$url' style='border:none' alt='$altcode' />", $part );
        }
    }
    
    // Put the parts back together
    $txt = implode( "", $parts );
    
    return true;
}

function postify($message, $smileyoff='no', $bbcodeoff='no', $allowsmilies='yes', $allowhtml='no', $allowbbcode='yes', $allowimgcode='yes', $ignorespaces=false, $ismood="no", $wrap="yes") {
    if ( 'yes' == $allowhtml ) {
        trigger_error( 'The allowhtml parameter to function postify() is deprecated in this version of XMB', E_USER_DEPRECATED );
    }

    $bballow = ($allowbbcode == 'yes' || $allowbbcode == 'on') ? (($bbcodeoff != 'off' && $bbcodeoff != 'yes') ? true : false) : false;
    $smiliesallow = ($allowsmilies == 'yes' || $allowsmilies == 'on') ? (($smileyoff != 'off' && $smileyoff != 'yes') ? true : false) : false;
    $allowurlcode = ($ismood != 'yes');

    if ($bballow) {
        if ($ismood == 'yes') {
            $message = str_replace(array('[rquote=', '[quote]', '[/quote]', '[code]', '[/code]', '[list]', '[/list]', '[list=1]', '[list=a]', '[list=A]', '[/list=1]', '[/list=a]', '[/list=A]', '[*]'), '_', $message);
        }

        //Remove the code block contents from $message.
        $messagearray = bbcodeCode($message);
        $message = array();
        for($i = 0; $i < count($messagearray); $i += 2) {
            $message[$i] = $messagearray[$i];
        }
        $message = implode("<!-- code -->", $message);

        // Do BBCode
        $message = rawHTMLmessage($message, $allowhtml);
        bbcode($message, $allowimgcode, $allowurlcode);
        if ($smiliesallow) {
            smile($message);
        }
        $message = nl2br($message);

        // Replace the code block contents in $message.
        if (count($messagearray) > 1) {
            $message = explode("<!-- code -->", $message);
            for($i = 0; $i < count($message) - 1; $i++) {
                $message[$i] .= censor($messagearray[$i*2+1]);
            }
            $message = implode("", $message);
        }

        if ('yes' == $wrap) {
            xmb_wordwrap($message);
        } else {
            $message = str_replace(array('<!-- nobr -->', '<!-- /nobr -->'), array('', ''), $message);
        }
    } else {
        $message = rawHTMLmessage($message, $allowhtml);
        if ($smiliesallow) {
            smile($message);
        }
        $message = nl2br($message);
        if ('yes' == $wrap) {
            xmb_wordwrap($message);
        }
    }

    $message = preg_replace('#(script|about|applet|activex|chrome):#is',"\\1 &#058;",$message);

    return $message;
}

function bbcode(&$message, $allowimgcode, $allowurlcode) {
    global $lang, $THEME, $SETTINGS;

    //Balance simple tags.
    $begin = array(
        0 => '[b]',
        1 => '[i]',
        2 => '[u]',
        3 => '[marquee]',
        4 => '[blink]',
        5 => '[strike]',
        6 => '[quote]',
        8 => '[list]',
        9 => '[list=1]',
        10 => '[list=a]',
        11 => '[list=A]',
    );

    $end = array(
        0 => '[/b]',
        1 => '[/i]',
        2 => '[/u]',
        3 => '[/marquee]',
        4 => '[/blink]',
        5 => '[/strike]',
        6 => '[/quote]',
        8 => '[/list]',
        9 => '[/list=1]',
        10 => '[/list=a]',
        11 => '[/list=A]',
    );

    foreach($begin as $key=>$value) {
        $check = substr_count($message, $value) - substr_count($message, $end[$key]);
        if ($check > 0) {
            $message .= str_repeat($end[$key], $check);
        } else if ($check < 0) {
            $message = str_repeat($value, abs($check)).$message;
        }
    }

    // Balance regex tags.
    $regex = array();
    $regex['align']  = "@\\[align=(left|center|right|justify)\\]@i";
    $regex['font']   = "@\\[font=([a-z\\r\\n\\t 0-9]+)\\]@i";
    $regex['rquote'] = "@\\[rquote=(\\d+)&(?:amp;)?tid=(\\d+)&(?:amp;)?author=([^\\[\\]<>]+)\\]@s";
    $regex['size']   = "@\\[size=([+-]?[0-9]{1,2})\\]@";
    $regex['color'] = array();
    $regex['color']['named'] = "@\\[color=(White|Black|Red|Yellow|Pink|Green|Orange|Purple|Blue|Beige|Brown|Teal|Navy|Maroon|LimeGreen|aqua|fuchsia|gray|silver|lime|olive)\\]@i";
    $regex['color']['hex']   = "@\\[color=#([\\da-f]{3,6})\\]@i";
    $regex['color']['rgb']   = "@\\[color=rgb\\(([\\s]*[\\d]{1,3}%?[\\s]*,[\\s]*[\\d]{1,3}%?[\\s]*,[\\s]*[\\d]{1,3}%?[\\s]*)\\)\\]@i";

    bbcodeBalanceTags($message, $regex);

    // Replace simple tags.
    $find = array(
        0 => '[b]',
        1 => '[/b]',
        2 => '[i]',
        3 => '[/i]',
        4 => '[u]',
        5 => '[/u]',
        6 => '[marquee]',
        7 => '[/marquee]',
        8 => '[blink]',
        9 => '[/blink]',
        10 => '[strike]',
        11 => '[/strike]',
        12 => '[quote]',
        13 => '[/quote]',
        14 => '[code]',
        15 => '[/code]',
        16 => '[list]',
        17 => '[/list]',
        18 => '[list=1]',
        19 => '[list=a]',
        20 => '[list=A]',
        21 => '[/list=1]',
        22 => '[/list=a]',
        23 => '[/list=A]',
        24 => '[*]',
        25 => '[/color]',
        26 => '[/font]',
        27 => '[/size]',
        28 => '[/align]',
        29 => '[/rquote]'
    );

    $replace = array(
        0 => '<strong>',
        1 => '</strong>',
        2 => '<em>',
        3 => '</em>',
        4 => '<u>',
        5 => '</u>',
        6 => '<marquee>',
        7 => '</marquee>',
        8 => '<blink>',
        9 => '</blink>',
        10 => '<strike>',
        11 => '</strike>',
        12 => ' <!-- nobr --><table align="center" class="quote" cellspacing="0" cellpadding="0"><tr><td class="quote">'.$lang['textquote'].'</td></tr><tr><td class="quotemessage"><!-- /nobr -->',
        13 => ' </td></tr></table>',
        14 => ' <!-- nobr --><table align="center" class="code" cellspacing="0" cellpadding="0"><tr><td class="code">'.$lang['textcode'].'</td></tr><tr><td class="codemessage"><code>',
        15 => '</code></td></tr></table><!-- /nobr -->',
        16 => '<ul type="square">',
        17 => '</ul>',
        18 => '<ol type="1">',
        19 => '<ol type="A">',
        20 => '<ol type="A">',
        21 => '</ol>',
        22 => '</ol>',
        23 => '</ol>',
        24 => '<li />',
        25 => '</span>',
        26 => '</span>',
        27 => '</span>',
        28 => '</div>',
        29 => ' </td></tr></table>'
    );

    $message = str_replace($find, $replace, $message);

    // Replace regex tags.
    $patterns = array();
    $replacements = array();

    $patterns[] = $regex['rquote'];
    $replacements[] = ' <!-- nobr --><table align="center" class="quote" cellspacing="0" cellpadding="0"><tr><td class="quote">'.$lang['textquote'].' <a href="viewthread.php?tid=$2&amp;goto=search&amp;pid=$1" rel="nofollow">'.$lang['origpostedby'].' $3 &nbsp;<img src="'.$THEME['imgdir'].'/lastpost.gif" border="0" alt="" style="vertical-align: middle;" /></a></td></tr><tr><td class="quotemessage"><!-- /nobr -->';
    $patterns[] = $regex['color']['named'];
    $replacements[] = '<span style="color: $1;">';
    $patterns[] = $regex['color']['hex'];
    $replacements[] = '<span style="color: #$1;">';
    $patterns[] = $regex['color']['rgb'];
    $replacements[] = '<span style="color: rgb($1);">';
    $patterns[] = $regex['font'];
    $replacements[] = '<span style="font-family: $1;">';
    $patterns[] = $regex['align'];
    $replacements[] = '<div style="text-align: $1;">';

    $patterns[] = "@\\[pid=(\\d+)&amp;tid=(\\d+)](.*?)\\[/pid]@si";
    $replacements[] = '<!-- nobr --><a href="viewthread.php?tid=$2&amp;goto=search&amp;pid=$1"><strong><!-- /nobr -->$3</strong> &nbsp;<img src="'.$THEME['imgdir'].'/lastpost.gif" border="0" alt="" style="vertical-align: middle;" /></a>';

    if ($allowimgcode != 'no' && $allowimgcode != 'off') {
        $patterns[] = '/\[youtube\]([a-z0-9_-]+)\[\/youtube\]/i';
        $replacements[] = '<!-- nobr --><iframe class="video" src="https://www.youtube.com/embed/\1" allowfullscreen></iframe><!-- /nobr -->';
    }

    $message = preg_replace($patterns, $replacements, $message);

    $message = preg_replace_callback($regex['size'], 'bbcodeSizeTags', $message);

    if ($allowimgcode != 'no' && $allowimgcode != 'off') {
        $https_only = 'on' == $SETTINGS['images_https_only'];
        $base_pattern = get_img_regexp( $https_only );

        $patterns = array();
        $patterns[] = '/\[img\]' . $base_pattern . '\[\/img\]/i';
        $patterns[] = '/\[img=([0-9]*?){1}x([0-9]*?)\]' . $base_pattern . '\[\/img\]/i';
        $message = preg_replace_callback( $patterns, 'bbcode_imgs', $message );
    }

    if ($allowurlcode) {
        /*
          This block positioned last so that bare URLs may appear adjacent to BBCodes without matching on square braces.
          Regexp explanation: match strings surrounded by whitespace or () or ><.  Do not include the surrounding chars.
            Group 1 will be identical to the full match so that the callback function can be reused for [url] codes.
        */
        $regexp = '(?<=^|\s|>|\()'
                . '('
                . '(?:(?:http|ftp)s?://|www)'
                . '[-a-z0-9.]+\.[a-z]{2,4}'
                . '[^\s()"\'<>\[\]]*'
                . ')'
                . '(?=$|\s|<|\))';
        $message = preg_replace_callback("#$regexp#i", 'bbcodeLongURLs', $message);

        //[url]https://www.example.com/[/url]
        //[url]www.example.com[/url]
        $message = preg_replace_callback("#\[url\]([^\"'<>]+?)\[/url\]#i", 'bbcodeLongURLs', $message);

        //[url=https://www.example.com/]Lorem Ipsum[/url]
        //[url=www.example.com]Lorem Ipsum[/url]
        $message = preg_replace_callback("#\[url=([^\"'<>\[\]]+)\](.*?)\[/url\]#i", 'bbcodeLongURLs', $message);
    }

    $patterns = array();
    $patterns[] = "#\\[email\\]([^\"'<>]+?)\\[/email\\]#i";
    $patterns[] = "#\\[email=([^\"'<>\\[\\]]+)\\](.+?)\\[/email\\]#i";
    $message = preg_replace_callback( $patterns, 'bbcode_emails', $message );

    return TRUE;
}

/**
 * Full parsing of [code] tags.
 *
 * @param string $message
 * @return array Odd number indexes contain the code block contents.
 */
function bbcodeCode($message){
    $counter = 0;
    $offset = 0;
    $done = FALSE;
    $messagearray = array();
    while(!$done){
        $pos = strpos($message, '[code]', $offset);
        if (FALSE === $pos) {
            $messagearray[$counter] = substr($message, $offset);
            $messagearray[$counter] = str_replace('[/code]', '&#091;/code]', $messagearray[$counter]);
            if ($counter > 1) {
                $messagearray[$counter] = '[/code]'.$messagearray[$counter];
            }
            $done = TRUE;
        } else {
            $pos += strlen('[code]');
            $messagearray[$counter] = substr($message, $offset, $pos - $offset);
            $messagearray[$counter] = str_replace('[/code]', '&#091;/code]', $messagearray[$counter]);
            if ($counter > 1) {
                $messagearray[$counter] = '[/code]'.$messagearray[$counter];
            }
            $counter++;
            $offset = $pos;
            $pos = strpos($message, '[/code]', $offset);
            if (FALSE === $pos) {
                $messagearray[$counter] = substr($message, $offset);
                $counter++;
                $messagearray[$counter] = '[/code]';
                $done = TRUE;
            } else {
                $messagearray[$counter] = substr($message, $offset, $pos - $offset);
                $counter++;
                $offset = $pos + strlen('[/code]');
            }
        }
    }
    return $messagearray;
}

/**
 * Wraps long lines but avoids certain elements.
 *
 * @since 1.9.11.12
 * @param string $input Read/Write Variable
 */
function xmb_wordwrap(&$input) {
    $br = trim(nl2br("\n"));
    $messagearray = preg_split("#<!-- nobr -->|<!-- /nobr -->#", $input);
    for($i = 0; $i < sizeof($messagearray); $i++) {
        if ($i % 2 == 0) {
            $messagearray[$i] = explode($br, $messagearray[$i]);
            foreach($messagearray[$i] as $key => $val) {
                $messagearray[$i][$key] = wordwrap($val, 150, "\n", TRUE);
            }
            $messagearray[$i] = implode($br, $messagearray[$i]);
        } // else inside nobr block
    }
    $input = implode('', $messagearray);
}

/**
 * Guarantees each BBCode has an equal number of open and close tags.
 *
 * @since 1.9.11.12
 * @param string $message Read/Write Variable
 * @param array $regex Indexed by code name
 */
function bbcodeBalanceTags(&$message, $regex){
    foreach($regex as $code => $pattern) {
        if (is_array($pattern)) {
            $open = 0;
            foreach($pattern as $subpattern) {
                $open += preg_match_all($subpattern, $message, $matches);
            }
        } else {
            $open = preg_match_all($pattern, $message, $matches);
        }
        $close = substr_count($message, "[/$code]");
        $open -= $close;
        if ($open > 0) {
            $message .= str_repeat("[/$code]", $open);
        } elseif ($open < 0) {
            $message = preg_replace("@\\[/$code]@", "&#091;/$code]", $message, -$open);
        }
    }
}

/**
 * Handles the [url] BBCode.
 *
 * This helper function is algorithmically required in order to fully support
 * unencoded square braces in BBCode URLs.  Encoding of the RFC 1738 Unsafe
 * character set thus remains optional at the BBCode and HTML layers.
 *
 * Credit for the value used in $scheme_whitelist goes to the WordPress project.
 *
 * @since 1.9.11.12
 * @param array $url Expects $url[0] to be the raw BBCode, $url[1] to be the URL only, and optionally $url[2] to be the display text.
 * @return string The HTML replacement for $url[0] if the code was valid, else the code is unchaged.
 */
function bbcodeLongURLs( array $url ): string {
    $url_max_display_len = 60;
    $scheme_whitelist = array('http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn');

    $colon = strpos($url[1], ':');
    if (FALSE !== $colon) {
        $scheme = substr($url[1], 0, $colon);
        if (in_array($scheme, $scheme_whitelist)) {
            $href = $url[1];
        } else {
            return $url[0];
        }
    } else {
        $href = 'http://'.$url[1];
    }
    if (!empty($url[2])) {
        $text = $url[2];
    } elseif (strlen($url[1]) <= $url_max_display_len) {
        $text = $url[1];
    } else {
        $text = substr($url[1], 0, $url_max_display_len).'...';
    }

    $href = bbcode_out( $href );

    return "<!-- nobr --><a href='$href' onclick='window.open(this.href); return false;'><!-- /nobr -->$text</a>";
}

/**
 * Adds relative font size values to the theme's font size.
 *
 * @since 1.9.11
 */
function bbcodeSizeTags( array $matches ): string {
    global $THEME;
    static $cachedFs;

    // Cache the theme font size in an array.
    if (!is_array($cachedFs) || count($cachedFs) != 2) {
        preg_match('#([0-9]+)([a-z]+)?#i', $THEME['fontsize'], $res);
        $cachedFs[0] = $res[1];
        $cachedFs[1] = $res[2];

        if (empty($cachedFs[1])) {
            $cachedFs[1] = 'px';
        }
    }

    $relative = (int) $matches[1];
    $o = ( $relative + $cachedFs[0] ) . $cachedFs[1];

    $html = "<span style='font-size: $o;'>";

    return $html;
}

/**
 * Processes tags like [file]1234[/file]
 *
 * Caller must include attach.inc.php, query the attachments table,
 * and load the needed templates.
 *
 * @since 1.9.11
 * @param string $message Read/Write Variable.  Returns the processed HTML.
 * @param array  $files   Read-Only Variable.  Contains the result rows from an attachment query.
 * @param int    $pid     Pass zero when in newthread or reply preview.
 * @param bool   $bBBcodeOnForThisPost
 * @param bool   $quarantine Are these files in a private table for later review?
 */
function bbcodeFileTags( string &$message, array &$files, int $pid, bool $bBBcodeOnForThisPost, bool $quarantine = false ) {
    global $lang, $SETTINGS;

    $count = 0;
    $separator = '';
    $htmlencode = true;
    foreach($files as $attach) {
        $post = array();
        $post['filename'] = attrOut($attach['filename']);
        $post['filetype'] = attrOut($attach['filetype']);
        $post['fileurl'] = \XMB\Attach\getURL( (int) $attach['aid'], $pid, $attach['filename'], $htmlencode, $quarantine );
        $attachsize = \XMB\Attach\getSizeFormatted($attach['filesize']);

        $post['filedims'] = '';
        $output = '';
        $prefix = '';
        $extension = strtolower(get_extension($post['filename']));
        $img_extensions = array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'wbmp', 'wbm', 'bmp');
        if ($SETTINGS['attachimgpost'] == 'on' && in_array($extension, $img_extensions)) {
            if ( (int) $attach['thumbid'] > 0 ) {
                $post['thumburl'] = \XMB\Attach\getURL( (int) $attach['thumbid'], $pid, $attach['thumbname'], $htmlencode, $quarantine );
                $result = explode('x', $attach['thumbsize']);
                $post['filedims'] = 'width="'.$result[0].'px" height="'.$result[1].'px"';
                eval('$output = "'.template('viewthread_post_attachmentthumb').'";');
            } else {
                if ($attach['img_size'] != '') {
                    $result = explode('x', $attach['img_size']);
                    $post['filedims'] = 'width="'.$result[0].'px" height="'.$result[1].'px"';
                }
                eval('$output = "'.template('viewthread_post_attachmentimage').'";');
            }
            $separator = '';
        } else {
            $downloadcount = $attach['downloads'];
            if ($downloadcount == '') {
                $downloadcount = 0;
            }
            eval('$output = "'.template('viewthread_post_attachment').'";');
            if ($separator == '') {
                $prefix = "<br /><br />";
            }
            $separator = "<br /><br />";
        }
        $output = '<!-- nobr -->'.trim(str_replace(array("\n","\r"), array('',''), $output)).'<!-- /nobr -->'; // Avoid nl2br, trailing space, wordwrap.
        if ($count == 0) {
            $prefix = "<br /><br />";
        }
        $matches = 0;
        if ($bBBcodeOnForThisPost) {
            $find = "[file]{$attach['aid']}[/file]";
            $pos = strpos($message, $find);
            if ($pos !== FALSE) {
                $matches = 1;
                $message = substr($message, 0, $pos).$output.substr($message, $pos + strlen($find));
            }
        }
        if ($matches == 0) {
            $message .= $prefix.$output.$separator; // Do we need some sort of a separator template here?
            $count++;
        }
    }
}

function modcheck($username, $mods, $override=X_SMOD) {

    $retval = '';
    if ($override) {
        $retval = 'Moderator';
    } else if (X_MOD) {
        $username = strtoupper($username);
        $mods = explode(',', $mods);
        foreach($mods as $key=>$moderator) {
            if ( strtoupper(trim($moderator)) === $username ) {
                $retval = 'Moderator';
                break;
            }
        }
    }

    return $retval;
}

function modcheckPost(&$username, &$mods, &$origstatus) {
    global $SETTINGS;
    $retval = modcheck($username, $mods);

    if ($retval != '' && $SETTINGS['allowrankedit'] != 'off') {
        switch($origstatus) {
            case 'Super Administrator':
                if (!X_SADMIN) {
                    $retval = '';
                }
                break;
            case 'Administrator':
                if (!X_ADMIN) {
                    $retval = '';
                }
                break;
            case 'Super Moderator':
                if (!X_SMOD) {
                    $retval = '';
                }
                break;
            //If member does not have X_MOD then modcheck() returned a null string.  No reason to continue testing.
        }
    }

    return $retval;
}

// As of version 1.9.11, function forum() is not responsible for any permissions checking.
// Caller should use permittedForums() or getStructuredForums() instead of querying for the parameters.
function forum($forum, $template, $index_subforums) {
    global $timecode, $dateformat, $lang, $timeoffset, $oldtopics, $lastvisit, $THEME, $SETTINGS;

    $forum['name'] = fnameOut($forum['name']);
    $forum['description'] = html_entity_decode($forum['description']);

    if ( ! empty( $forum['lastpost'] ) ) {
        $lastpost = explode('|', $forum['lastpost']);
        $dalast = $lastpost[0];

        // Translate "Anonymous" author.
        $lastpostname = trim( $lastpost[1] );
        if ( 'Anonymous' == $lastpostname ) {
            $lastpostname = $lang['textanonymous'];
        }

        $lastPid = isset($lastpost[2]) ? $lastpost[2] : 0;

        $lastpostdate = gmdate($dateformat, $lastpost[0] + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600));
        $lastposttime = gmdate($timecode, $lastpost[0] + ($timeoffset * 3600) + ($SETTINGS['addtime'] * 3600));
        $lastpost = "$lastpostdate {$lang['textat']} $lastposttime<br />{$lang['textby']} $lastpostname";
        eval('$lastpostrow = "'.template($template.'_lastpost').'";');
    } else {
        $dalast = 0;
        $lastPid = 0;
        $lastpost = $lang['textnever'];
        eval('$lastpostrow = "'.template($template.'_nolastpost').'";');
    }

    $oT = strpos( $oldtopics, "|$lastPid|" );
    if ( $lastvisit < $dalast && $oT === false ) {
        $folder = '<img src="'.$THEME['imgdir'].'/red_folder.gif" alt="'.$lang['altredfolder'].'" border="0" />';
    } else {
        $folder = '<img src="'.$THEME['imgdir'].'/folder.gif" alt="'.$lang['altfolder'].'" border="0" />';
    }

    if ($dalast == '') {
        $folder = '<img src="'.$THEME['imgdir'].'/folder.gif" alt="'.$lang['altfolder'].'" border="0" />';
    }

    $foruminfo = '';

    if ( ! empty( $forum['moderator'] ) ) {
        $list = [];
        $moderators = explode(', ', $forum['moderator']);
        foreach ( $moderators as $moderator ) {
            $list[] = '<a href="member.php?action=viewpro&amp;member='.recodeOut( $moderator ).'">'.$moderator.'</a>';
        }
        $moderators = implode( ', ', $list );
        $forum['moderator'] = "{$lang['textmodby']} $moderators";
        if ( '' != $forum['description'] ) {
            $forum['moderator'] = '<br />' . $forum['moderator'];
        }
    }

    $subforums = array();
    if (count($index_subforums) > 0) {
        for($i=0; $i < count($index_subforums); $i++) {
            $sub = $index_subforums[$i];
            if ( $sub['fup'] === $forum['fid'] ) {
                $subforums[] = '<a href="forumdisplay.php?fid='.intval($sub['fid']).'">'.fnameOut($sub['name']).'</a>';
            }
        }
    }

    if (!empty($subforums)) {
        $subforums = implode(', ', $subforums);
        $subforums = "{$lang['textsubforums']} <span class='plainlinks'>$subforums</span>";
        if ( '' != $forum['description'] || '' != $forum['moderator'] ) {
            $subforums = '<br />' . $subforums;
        }
    } else {
        $subforums = '';
    }
    eval('$foruminfo = "'.template($template).'";');

    $dalast = '';

    return $foruminfo;
}

/**
 * Handles most of the I/O tasks to create a collection of numbered pages
 * from an ordered collection of items.
 *
 * Caller must echo the returned html directly or in a template variable.
 *
 * @param int $num Total number of items in the collection.
 * @param int $perpage Number of items to display on each page.
 * @param string $baseurl Relative URL of the first page in the collection.
 * @param mixed $canonical Optional. Specify FALSE if the $baseurl param is not a canonical URL. Specify a Relative URL string to override $baseurl.
 * @return array Associative indexes: 'html' the link bar string, 'start' the LIMIT int used in queries.
 */
function multipage($num, $perpage, $baseurl, $canonical = TRUE) {
    global $cookiepath, $full_url, $lang, $url;

    // Initialize
    $return = array();
    $page = getInt('page');
    $max_page = quickpage(intval($num), intval($perpage));
    if ($canonical === TRUE) $canonical =& $baseurl;

    // Calculate the LIMIT start number for queries
    if ($page > 1 && $page <= $max_page) {
        $return['start'] = ($page-1) * $perpage;
        if ($canonical !== FALSE) setCanonicalLink($canonical.((strpos($baseurl, '?') !== FALSE) ? '&amp;' : '?').'page='.$page);
    } elseif ($page == 0 && !isset($_GET['page'])) {
        $return['start'] = 0;
        $page = 1;
        if ($canonical !== FALSE) setCanonicalLink($canonical);
    } elseif ($page == 1) {
        $newurl = preg_replace('/[^\x20-\x7e]/', '', $url);
        $newurl = str_replace('&page=1', '', $newurl);
        $newurl = substr($full_url, 0, -strlen($cookiepath)).$newurl;
        header('HTTP/1.0 301 Moved Permanently');
        header('Location: '.$newurl);
        exit;
    } else {
        header('HTTP/1.0 404 Not Found');
        error($lang['generic_missing']);
    }

    // Generate the multipage link bar.
    $return['html'] = multi($page, $max_page, $baseurl);

    return $return;
}

/**
 * Generates an HTML page-selection bar for any collection of numbered pages.
 *
 * The link to each page in the collection will have the "page" variable added
 * to its query string, except for page number one.
 *
 * @param int $page Current page number, must be >= 1.
 * @param int $lastpage Total number of pages in the collection.
 * @param string $mpurl Read-Only Variable. Relative URL of the first page in the collection.
 * @param bool $isself FALSE indicates the page bar will be displayed on a page that is not part of the collection.
 * @return string Null string if the $lastpage parameter was <= 1 or $page was invalid.
 */
function multi($page, $lastpage, &$mpurl, $isself = TRUE) {
    global $lang;

    $multipage = $lang['textpages'];

    if ($page >= 1 && $lastpage > 1 && $page <= $lastpage) {
        if ($page >= $lastpage - 3) {
            $to = $lastpage;
        } else {
            $to = $page + 3;
        }

        if ($page <= 4) {
            $from = 1;
        } else {
            $from = $page - 3;
        }

        $to--;
        $from++;

        $string = (strpos($mpurl, '?') !== false) ? '&amp;' : '?';

        // Link to first page
        $multipage .= "\n";
        if ($page != 1 || !$isself) {
            $extra = '';
            if ($isself) {
                if (2 == $page) {
                    $extra = ' rel="prev start"';
                } else {
                    $extra = ' rel="start"';
                }
            }
            $multipage .= '&nbsp;<u><a href="'.$mpurl.'"'.$extra.'>1</a></u>';
            if ($from > 2) {
                $multipage .= "\n&nbsp;..";
            }
        } else {
            $multipage .= '&nbsp;<strong>1</strong>';
        }

        // Link to current page and up to 2 prev and 2 next pages.
        $multipage .= "\n";
        for($i = $from; $i <= $to; $i++) {
            if ($i != $page) {
                $extra = '';
                if ($isself) {
                    if ($i == $page - 1) {
                        $extra = ' rel="prev"';
                    } else if ($i == $page + 1) {
                        $extra = ' rel="next"';
                    }
                    if ($page == 1) {
                        $extra .= ' rev="start"';
                    }
                }
                $multipage .= '&nbsp;<u><a href="'.$mpurl.$string.'page='.$i.'"'.$extra.'>'.$i.'</a></u>';
            } else {
                $multipage .= '&nbsp;<strong>'.$i.'</strong>';
            }
            $multipage .= "\n";
        }

        // Link to last page
        if ($lastpage != $page) {
            if (($lastpage - 1) > $to) {
                $multipage .= "&nbsp;..\n";
            }
            $extra = '';
            if ($isself) {
                if ($page == $lastpage - 1) {
                    $extra = ' rel="next"';
                }
                if ($page == 1) {
                    $extra .= ' rev="start"';
                }
            }
            $multipage .= '&nbsp;<u><a href="'.$mpurl.$string.'page='.$lastpage.'"'.$extra.'>'.$lastpage.'</a></u>';
        } else {
            $multipage .= '&nbsp;<strong>'.$lastpage.'</strong>';
        }
    } else {
        $multipage = '';
    }

    return $multipage;
}

function quickpage($things, $thingsperpage) {
    return ((($things > 0) && ($thingsperpage > 0) && ($things > $thingsperpage)) ? ceil($things / $thingsperpage) : 1);
}

function smilieinsert($type='normal') {
    global $db, $SETTINGS, $THEME, $smiliesnum, $smiliecache;

    $counter = 0;
    $sms = array();
    $smilies = '';
    $smilieinsert = '';

    if ($type == 'normal') {
        $smcols = intval($SETTINGS['smcols']);
        $smtotal = intval($SETTINGS['smtotal']);
    } elseif ($type == 'quick') {
        $smcols = 4;
        $smtotal = 16;
    } elseif ($type == 'full') {
        $smcols = intval($SETTINGS['smcols']);
        $smtotal = 0;
    }

    if ($SETTINGS['smileyinsert'] == 'on' && $smcols > 0 && $smiliesnum > 0) {
        foreach($smiliecache as $key=>$val) {
            $smilie['code'] = $key;
            $smilie['url'] = $val;
            eval('$sms[] = "'.template('functions_smilieinsert_smilie').'";');
            if ($smtotal > 0) {
                $counter++;
                if ($counter >= $smtotal) {
                    break;
                }
            }
        }

        $smilies = '<tr>';
        for($i=0;$i<count($sms);$i++) {
            $smilies .= $sms[$i];
            if (($i+1)%$smcols == 0) {
                $smilies .= '</tr>';
                if (($i+1) < count($sms)) {
                    $smilies .= '<tr>';
                }
            }
        }

        if (count($sms)%$smcols > 0) {
            $left = $smcols-(count($sms)%$smcols);
            for($i=0;$i<$left;$i++) {
                $smilies .= '<td />';
            }
            $smilies .= '</tr>';
        }
        eval('$smilieinsert = "'.template('functions_smilieinsert').'";');
    }

    return $smilieinsert;
}

function updateforumcount($fid) {
    global $db;
    $fid = intval($fid);

    $query = $db->query("SELECT COUNT(*) FROM ".X_PREFIX."forums AS f INNER JOIN ".X_PREFIX."posts USING(fid) WHERE f.fid=$fid OR f.fup=$fid");
    $postcount = $db->result($query, 0);
    $db->free_result($query);

    $query = $db->query("SELECT COUNT(*) FROM ".X_PREFIX."forums AS f INNER JOIN ".X_PREFIX."threads USING(fid) WHERE f.fid=$fid OR f.fup=$fid");
    $threadcount = $db->result($query, 0);
    $db->free_result($query);

    $query = $db->query("SELECT t.lastpost FROM ".X_PREFIX."forums AS f LEFT JOIN ".X_PREFIX."threads AS t USING(fid) WHERE f.fid=$fid OR f.fup=$fid ORDER BY t.lastpost DESC LIMIT 0, 1");
    $lp = $db->fetch_array($query);
    $db->escape_fast($lp['lastpost']);
    $db->query("UPDATE ".X_PREFIX."forums SET posts='$postcount', threads='$threadcount', lastpost='{$lp['lastpost']}' WHERE fid='$fid'");
    $db->free_result($query);
}

function updatethreadcount($tid) {
    global $db;
    $tid = intval($tid);

    $query = $db->query("SELECT tid FROM ".X_PREFIX."posts WHERE tid='$tid'");
    $replycount = $db->num_rows($query);
    $db->free_result($query);
    $replycount--;
    $query = $db->query("SELECT dateline, author, pid FROM ".X_PREFIX."posts WHERE tid='$tid' ORDER BY dateline DESC, pid DESC LIMIT 1");
    $lp = $db->fetch_array($query);
    $db->free_result($query);
    $query = $db->query("SELECT date, username FROM ".X_PREFIX."logs WHERE tid='$tid' AND action='bump' ORDER BY date DESC LIMIT 1");
    if ($db->num_rows($query) == 1) {
        $lb = $db->fetch_array($query);
        $lp['dateline'] = $lb['date'];
        $lp['author'] = $lb['username'];
    }
    $db->free_result($query);
    $lastpost = $lp['dateline'].'|'.$lp['author'].'|'.$lp['pid'];
    $db->escape_fast($lastpost);

    $db->query("UPDATE ".X_PREFIX."threads SET replies='$replycount', lastpost='$lastpost' WHERE tid='$tid'");
}

function smcwcache() {
    global $db, $smiliecache, $censorcache, $smiliesnum, $wordsnum;
    static $cached;

    if (!$cached) {
        $smiliecache = array();
        $censorcache = array();

        $query = $db->query("SELECT code, url FROM ".X_PREFIX."smilies WHERE type='smiley'");
        $smiliesnum = $db->num_rows($query);

        if ($smiliesnum > 0) {
            while($smilie = $db->fetch_array($query)) {
                $code = $smilie['code'];
                $smiliecache[$code] = $smilie['url'];
            }
        }
        $db->free_result($query);

        $query = $db->query("SELECT find, replace1 FROM ".X_PREFIX."words");
        $wordsnum = $db->num_rows($query);
        if ($wordsnum > 0) {
            while($word = $db->fetch_array($query)) {
                $find = $word['find'];
                $censorcache[$find] = $word['replace1'];
            }
        }
        $db->free_result($query);

        $cached = true;
        return true;
    }

    return false;
}

/**
 * Generates sub-templates in the $footerstuff global array.
 */
function end_time() {
    global $db, $footerstuff, $lang, $starttime, $SETTINGS;

    $mtime2 = explode(' ', microtime());
    $endtime = $mtime2[1] + $mtime2[0];

    $totaltime = ($endtime - $starttime);

    $footer_options = explode('-', $SETTINGS['footer_options']);

    if (X_ADMIN && in_array('serverload', $footer_options)) {
        $load = ServerLoad();
        if (!empty($load)) {
            eval('$footerstuff["load"] = "'.template('footer_load').'";');
        } else {
            $footerstuff['load'] = '';
        }
    } else {
        $footerstuff['load'] = '';
    }

    if (in_array('queries', $footer_options)) {
        $querynum = $db->getQueryCount();
        eval('$footerstuff["querynum"] = "'.template('footer_querynum').'";');
    } else {
        $footerstuff['querynum'] = '';
    }

    if (in_array('phpsql', $footer_options)) {
        $db_duration = number_format( ( $db->getDuration() / $totaltime ) * 100, 1 );
        $php_duration = number_format( ( 1 - ( $db->getDuration() / $totaltime ) ) * 100, 1);
        eval('$footerstuff["phpsql"] = "'.template('footer_phpsql').'";');
    } else {
        $footerstuff['phpsql'] = '';
    }

    if (in_array('loadtimes', $footer_options) && X_ADMIN) {
        $totaltime = number_format($totaltime, 7);
        eval('$footerstuff["totaltime"] = "'.template('footer_totaltime').'";');
    } else {
        $footerstuff['totaltime'] = '';
    }

    if (X_SADMIN && DEBUG) {
        $footerstuff['querydump'] = printAllQueries();
    } else {
        $footerstuff['querydump'] = '';
    }
}

function redirect($path, $timeout=2, $type=X_REDIRECT_HEADER) {
    if (strpos(urldecode($path), "\n") !== false || strpos(urldecode($path), "\r") !== false) {
        error('Tried to redirect to potentially insecure url.');
    }

    if (headers_sent() || $type == X_REDIRECT_JS) {
        ?>
        <script language="javascript" type="text/javascript">
        function redirect() {
            window.location.replace("<?php echo $path?>");
        }
        setTimeout("redirect();", <?php echo ($timeout*1000)?>);
        </script>
        <?php
    } else {
        if ($timeout == 0) {
            header('HTTP/1.0 302 Found');
            header("Location: $path");
            exit;
        } else {
            header("Refresh: $timeout; URL=$path");
        }
    }

    return true;
}

function get_extension(&$filename) {
    $a = explode('.', $filename);
    $count = count($a);
    if ($count == 1) {
        return '';
    } else {
        return $a[$count-1];
    }
}

function ServerLoad() {
    if ($stats = @exec('uptime')) {
        $parts = explode(',', $stats);
        $count = count($parts);
        $first = explode(' ', $parts[$count-3]);
        $c = count($first);
        $first = $first[$c-1];
        return array($first, $parts[$count-2], $parts[$count-1]);
    } else {
        return array();
    }
}

function error($msg, $showheader=true, $prepend='', $append='', $redirect=false, $die=true, $return_as_string=false, $showfooter=true) {
    global $footerstuff, $navigation; // Used by nav() and end_time()

    if (isset($GLOBALS)) {
        extract($GLOBALS, EXTR_SKIP);
    }

    $args = func_get_args();

    $message = (isset($args[0]) ? $args[0] : '');
    $showheader = (isset($args[1]) ? $args[1] : true);
    $prepend = (isset($args[2]) ? $args[2] : '');
    $append = (isset($args[3]) ? $args[3] : '');
    $redirect = (isset($args[4]) ? $args[4] : false);
    $die = (isset($args[5]) ? $args[5] : true);
    $return_str = (isset($args[6]) ? $args[6] : false);
    $showfooter = (isset($args[7]) ? $args[7] : true);

    $header = $footer = $return = '';

    if ($showheader) {
        nav($lang['error']);
    }

    end_time();

    if ($redirect !== false) {
        redirect($redirect, 3);
    }

    if ($showheader === false) {
        $header = '';
    } else {
        eval('$header = "'.template('header').'";');
    }

    $error = '';
    eval('$error = "'.template('error').'";');

    if ($showfooter === true) {
        eval('$footer = "'.template('footer').'";');
    } else {
        $footer = '';
    }

    if ($return_str !== false) {
        $return = $prepend . $error . $append . $footer;
    } else {
        echo $prepend . $error . $append . $footer;
        $return = '';
    }

    if ($die) {
        exit();
    }

    return $return;
}

function message($msg, $showheader=true, $prepend='', $append='', $redirect=false, $die=true, $return_as_string=false, $showfooter=true) {
    global $footerstuff, $navigation; // Used by nav() and end_time()

    if (isset($GLOBALS)) {
        extract($GLOBALS, EXTR_SKIP);
    }

    $args = func_get_args();

    $message = (isset($args[0]) ? $args[0] : '');
    $showheader = (isset($args[1]) ? $args[1] : true);
    $prepend = (isset($args[2]) ? $args[2] : '');
    $append = (isset($args[3]) ? $args[3] : '');
    $redirect = (isset($args[4]) ? $args[4] : false);
    $die = (isset($args[5]) ? $args[5] : true);
    $return_str = (isset($args[6]) ? $args[6] : false);
    $showfooter = (isset($args[7]) ? $args[7] : true);

    $header = $footer = $return = '';

    if ($showheader) {
        nav($lang['message']);
    }

    end_time();

    if ($redirect !== false) {
        redirect($redirect, 3);
    }

    if ($showheader === false) {
        $header = '';
    } else {
        eval('$header = "'.template('header').'";');
    }

    $success = '';
    eval('$success = "'.template('message').'";');

    if ($showfooter === true) {
        eval('$footer = "'.template('footer').'";');
    } else {
        $footer = '';
    }

    if ($return_str !== false) {
        $return = $prepend . $success . $append . $footer;
    } else {
        echo $prepend . $success . $append . $footer;
        $return = '';
    }

    if ($die) {
        exit();
    }

    return $return;
}

/**
 * XMB's Cookie helper.
 *
 * @since 1.9.1
 */
function put_cookie($name, $value=false, $expire=0, $path=null, $domain=null, $secure=false) {
    global $cookiepath, $cookiedomain, $cookiesecure;

    // Make sure the output stream is still empty.  Otherwise, someone called this function at the wrong time.
    if (headers_sent()) {
        trigger_error( 'Attempted use of put_cookie() after headers already sent.', E_USER_WARNING );
        return false;
    }

    // Default arguments were poorly chosen, so let's try to fill them in now.
    if (is_null($path)) $path = $cookiepath;
    if (is_null($domain)) $domain = $cookiedomain;
    if (!$secure) $secure = $cookiesecure;
    $httponly = true;
    $samesite = 'Lax';

    if (version_compare(PHP_VERSION, '7.3.0', '<')) {
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    } else {
        $options = [
            'expires' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite,
        ];
        return setcookie($name, $value, $options);
    }
}

function audit( string $user, string $action, int $fid, int $tid ) {
    global $db, $onlinetime;

    $action = cdataOut($action);
    $user = cdataOut($user);

    $db->query("INSERT INTO ".X_PREFIX."logs (tid, username, action, fid, date) VALUES ('$tid', '$user', '$action', '$fid', $onlinetime)");
    return true;
}

function validatePpp() {
    global $ppp, $postperpage;

    if ( empty( $ppp ) || ! is_numeric( $ppp ) ) {
        $ppp = (int) $postperpage;
    } else {
        $ppp = (int) $ppp;
    }

    if ($ppp < 5) {
        $ppp = 30;
    }
}

function validateTpp() {
    global $tpp, $topicperpage;

    if ( empty( $tpp ) || ! is_numeric( $tpp ) ) {
        $tpp = (int) $topicperpage;
    } else {
        $tpp = (int) $tpp;
    }

    if ($tpp < 5) {
        $tpp = 30;
    }
}

function altMail($to, $subject, $message, $additional_headers='', $additional_parameters=null) {
    global $mailer, $SETTINGS;
    static $handlers;

    $message = str_replace(array("\r\n", "\r", "\n"), array("\n", "\n", "\r\n"), $message);
    $subject = str_replace(array("\r", "\n"), array('', ''), $subject);

    if ($mailer['type'] == 'socket_SMTP') {
        require_once(ROOT.'include/smtp.inc.php');

        if (!isset($handlers['socket_SMTP'])) {
            if (DEBUG) {
                $mail = new socket_SMTP(true, './smtp-log.txt');
            } else {
                $mail = new socket_SMTP;
            }
            $handlers['socket_SMTP'] = &$mail;
            if (!$mail->connect($mailer['host'], $mailer['port'], $mailer['username'], $mailer['password'])) {
                return FALSE;
            }
            register_shutdown_function(array(&$mail, 'disconnect'));
        } else {
            $mail = &$handlers['socket_SMTP'];
            if (FALSE === $mail->connection) {
                return FALSE;
            }
        }

        $subjectInHeader = false;
        $toInHeader = false;
        $additional_headers = explode("\r\n", $additional_headers);
        foreach($additional_headers as $k=>$h) {
            if (strpos(trim($h), 'ubject:') === 1) {
                $additional_headers[$k] = 'Subject: '.$subject."\r\n";
                $subjectInHeader = true;
                continue;
            }

            if (strpos(trim(strtolower($h)), 'to:') === 0) {
                $toInHeader = true;
            }
        }

        if (!$subjectInHeader) {
            $additional_headers[] = 'Subject: '.$subject;
        }

        if (!$toInHeader) {
            $additional_headers[] = 'To: '.$to;
        }

        $additional_headers = implode("\r\n", $additional_headers);

        return $mail->sendMessage($SETTINGS['adminemail'], $to, $message, $additional_headers);
    } else {
        if (ini_get('safe_mode') == "1") {
            $return = mail($to, $subject, $message, $additional_headers);
        } else {
            $return = mail($to, $subject, $message, $additional_headers, $additional_parameters);
        }
        if (!$return) {
            $msg = 'XMB failed to send an e-mail because the PHP mail() function returned FALSE!  This might be caused by using an invalid address in XMB\'s Administrator E-Mail setting.';
            trigger_error($msg, E_USER_WARNING);
        }
        return $return;
    }
}

function shortenString($string, $len=100, $shortType=X_SHORTEN_SOFT, $ps='...') {
    if (strlen($string) > $len) {
        if (($shortType & X_SHORTEN_SOFT) === X_SHORTEN_SOFT) {
            $string = preg_replace('#^(.{0,'.$len.'})([\W].*)#', '\1'.$ps, $string);
        }

        if ((strlen($string) > $len+strlen($ps)) && (($shortType & X_SHORTEN_HARD) === X_SHORTEN_HARD)) {
            $string = substr($string, 0, $len).$ps;
        }
        return $string;
    } else {
        return $string;
    }
}

function printGmDate($timestamp=null, $altFormat=null, $altOffset=0) {
    global $dateformat, $SETTINGS, $timeoffset;

    if ($timestamp === null) {
        $timestamp = time();
    }

    if ($altFormat === null) {
        $altFormat = $dateformat;
    }

    $f = false;
    if ((($pos = strpos($altFormat, 'F')) !== false && $f = true) || ($pos2 = strpos($altFormat, 'M')) !== false) {
        $startStr = substr($altFormat, 0, $pos);
        $endStr = substr($altFormat, $pos+1);
        $month = gmdate('m', $timestamp + ($timeoffset*3600)+(($altOffset+$SETTINGS['addtime'])*3600));
        $textM = month2text($month);
        return printGmDate($timestamp, $startStr, $altOffset).substr($textM,0, ($f ? strlen($textM) : 3)).printGmDate($timestamp, $endStr, $altOffset);
    } else {
        return gmdate($altFormat, $timestamp + ($timeoffset * 3600) + (($altOffset+$SETTINGS['addtime']) * 3600));
    }
}

function printGmTime($timestamp=null, $altFormat=null, $altOffset=0) {
    global $self, $SETTINGS, $timeoffset, $timecode;

    if ($timestamp === null) {
        $timestamp = time();
    }

    if ($altFormat !== null) {
        return gmdate($altFormat, $timestamp + ($timeoffset * 3600) + (($altOffset+$SETTINGS['addtime']) * 3600));
    } else {
        return gmdate($timecode, $timestamp + ($timeoffset * 3600) + (($altOffset+$SETTINGS['addtime']) * 3600));
    }
}

function MakeTime() {
   $objArgs = func_get_args();
   $nCount = count($objArgs);
   if ($nCount < 7) {
       if ($nCount < 1) {
           $objArgs[] = intval(gmdate('H'));
       } else if ($nCount < 2) {
           $objArgs[] = intval(gmdate('i'));
       } else if ($nCount < 3) {
           $objArgs[] = intval(gmdate('s'));
       } else if ($nCount < 4) {
           $objArgs[] = intval(gmdate('n'));
       } else if ($nCount < 5) {
           $objArgs[] = intval(gmdate('j'));
       } else if ($nCount < 6) {
           $objArgs[] = intval(gmdate('Y'));
       }
   }

   $nYear = $objArgs[5];
   $nOffset = 0;
   if ($nYear < 1970) {
       if ($nYear < 1902) {
           return 0;
       } else if ($nYear < 1952) {
           $nOffset = -2650838400;
           $objArgs[5] += 84;
       } else {
           $nOffset = -883612800;
           $objArgs[5] += 28;
       }
   }

   return call_user_func_array("gmmktime", $objArgs) + $nOffset;
}

function iso8601_date($year=0, $month=0, $day=0) {
    $year = (int) $year;
    $month = (int) $month;
    $day = (int) $day;

    if ($year < 1 || $month < 1 || $day < 1) {
        return '0000-00-00';
    }

    if ($year < 100) {
        $year += 1900;
    }

    if ($month > 12 || $month < 1) {
        $month = 1;
    }

    if ($day > 31 || $day < 1) {
        $day = 1;
    }

    return str_pad($year, 4, '0', STR_PAD_LEFT).'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
}

function month2text($num) {
    global $lang;

    $num = (int) $num;
    if ($num < 1 || $num > 12) {
        $num = 1;
    }

    $months = array(
        $lang['textjan'],
        $lang['textfeb'],
        $lang['textmar'],
        $lang['textapr'],
        $lang['textmay'],
        $lang['textjun'],
        $lang['textjul'],
        $lang['textaug'],
        $lang['textsep'],
        $lang['textoct'],
        $lang['textnov'],
        $lang['textdec']
    );

    return $months[$num-1];
}

/**
 * Creates a db query result containing all active forums and forum categories.
 *
 * Important: The return value is passed by reference.  There is only one query object.  This cannot be used in nested functions.
 *
 * @return object
 */
function forumCache() {
    global $db;
    static $cache = FALSE;

    if ($cache === FALSE) {
        $cache = $db->query("SELECT f.* FROM ".X_PREFIX."forums f WHERE f.status='on' ORDER BY f.displayorder ASC");
    }

    if ($cache !== FALSE) {
        if ($db->num_rows($cache) > 0) {
            $db->data_seek($cache, 0);  // Restores the pointer for fetch_array().
        }
    }

    return $cache;
}

/**
 * Creates an associative array for the specified forum.
 */
function getForum($fid) {
    global $db;

    $forums = forumCache();
    while($forum = $db->fetch_array($forums)) {
        if (intval($forum['fid']) == intval($fid)) {
            return $forum;
        }
    }
    return FALSE;
}

/**
 * Creates a multi-dimensional array of forums.
 *
 * The array uses the following associative subscripts:
 *  0:forums.type
 *  1:forums.fup (always '0' for groups)
 *  2:forums.fid
 *  3:forums.*
 * Usage example:
 *  $forums = getStructuredForums();
 *  echo fnameOut($forums['forum']['9']['14']['name']);
 *
 * @param bool $usePerms If TRUE then not all forums are returned, only visible forums.
 * @return array
 */
function getStructuredForums($usePerms=FALSE) {
    global $db;

    if ($usePerms) {
        $forums = permittedForums(forumCache(), 'forum');
    } else {
        $forums = array();
        $query = forumCache();
        while($forum = $db->fetch_array($query)) {
            $forums[] = $forum;
        }
    }

    // This function guarantees the following subscripts exist, regardless of forum count.
    $structured['group'] = array();
    $structured['forum'] = array();
    $structured['sub'] = array();
    $structured['group']['0'] = array();
    $structured['forum']['0'] = array();

    foreach($forums as $forum) {
        $structured[$forum['type']][$forum['fup']][$forum['fid']] = $forum;
    }

    return $structured;
}

/**
 * Creates an array of permitted forum arrays.
 *
 * @param object $forums DB query result, preferably from forumCache().
 * @param string $mode Whether to check for 'forum' listing permissions or 'thread' listing permissions.
 * @param string $output If set to 'csv' causes the return value to be a CSV string of permitted forum IDs instead of an 'array' of arrays.
 * @param bool $check_parents Indicates whether each forum's permissions depend on the parent forum also being permitted.
 * @param bool $user_status Optional masquerade value passed to checkForumPermissions().
 * @return array
 */
function permittedForums($forums, $mode='thread', $output='array', $check_parents=TRUE, $user_status=FALSE) {
    global $db, $SETTINGS;

    $permitted = array();
    $fids['group'] = array();
    $fids['forum'] = array();
    $fids['sub'] = array();

    while($forum = $db->fetch_array($forums)) {
        $perms = checkForumPermissions($forum, $user_status);
        if ($mode == 'thread') {
            if ($forum['type'] == 'group' || ($perms[X_PERMS_VIEW] && $perms[X_PERMS_PASSWORD])) {
                $permitted[] = $forum;
                $fids[$forum['type']][] = $forum['fid'];
            }
        } elseif ($mode == 'forum') {
            if ($SETTINGS['hideprivate'] == 'off' || $forum['type'] == 'group' || $perms[X_PERMS_VIEW]) {
                $permitted[] = $forum;
                $fids[$forum['type']][] = $forum['fid'];
            }
        }
    }

    if ($check_parents) { // Use the $fids array to see if each forum's parent is permitted.
        $filtered = array();
        $fids['forum'] = array();
        $fids['sub'] = array();
        foreach($permitted as $forum) {
            if ($forum['type'] == 'group') {
                $filtered[] = $forum;
            } elseif ($forum['type'] == 'forum') {
                if (intval($forum['fup']) == 0) {
                    $filtered[] = $forum;
                    $fids['forum'][] = $forum['fid'];
                } elseif (array_search($forum['fup'], $fids['group']) !== FALSE) {
                    $filtered[] = $forum;
                    $fids['forum'][] = $forum['fid'];
                }
            }
        }

        foreach($permitted as $forum) {
            if ($forum['type'] == 'sub') {
                if (intval($forum['fup']) == 0) {
                    $filtered[] = $forum;
                    $fids['sub'][] = $forum['fid'];
                } elseif (array_search($forum['fup'], $fids['forum']) !== FALSE) {
                    $filtered[] = $forum;
                    $fids['sub'][] = $forum['fid'];
                }
            }
        }

        $permitted = $filtered;
    }

    if ($output == 'csv') {
        $permitted = implode(', ', array_merge($fids['group'], $fids['forum'], $fids['sub']));
    }

    return $permitted;
}

function forumList($selectname='srchfid', $multiple=false, $allowall=true, $currentfid=0) {
    global $lang;

    // Initialize $forumselect
    $forumselect = array();
    if (!$multiple) {
        $forumselect[] = '<select name="'.$selectname.'">';
    } else {
        $forumselect[] = '<select name="'.$selectname.'[]" size="10" multiple="multiple">';
    }

    if ($allowall) {
        if ($currentfid == 0) {
            $forumselect[] = '<option value="all" selected="selected">'.$lang['textallforumsandsubs'].'</option>';
        } else {
            $forumselect[] = '<option value="all">'.$lang['textallforumsandsubs'].'</option>';
        }
    } else if (!$allowall && !$multiple) {
        $forumselect[] = '<option value="" disabled="disabled" selected="selected">'.$lang['textforum'].'</option>';
    }

    // Populate $forumselect
    $permitted = getStructuredForums(TRUE);

    foreach($permitted['forum']['0'] as $forum) {
        $forumselect[] = '<option value="'.intval($forum['fid']).'"'.($forum['fid'] == $currentfid ? ' selected="selected"' : '').'> &nbsp; &raquo; '.fnameOut($forum['name']).'</option>';
        if (isset($permitted['sub'][$forum['fid']])) {
            foreach($permitted['sub'][$forum['fid']] as $sub) {
                $forumselect[] = '<option value="'.intval($sub['fid']).'"'.($sub['fid'] == $currentfid ? ' selected="selected"' : '').'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.fnameOut($sub['name']).'</option>';
            }
        }
    }

    $forumselect[] = '<option value="0" disabled="disabled">&nbsp;</option>';
    foreach($permitted['group']['0'] as $group) {
        if (isset($permitted['forum'][$group['fid']]) && count($permitted['forum'][$group['fid']]) > 0) {
            $forumselect[] = '<option value="'.intval($group['fid']).'" disabled="disabled">'.fnameOut($group['name']).'</option>';
            foreach($permitted['forum'][$group['fid']] as $forum) {
                $forumselect[] = '<option value="'.intval($forum['fid']).'"'.($forum['fid'] == $currentfid ? ' selected="selected"' : '').'> &nbsp; &raquo; '.fnameOut($forum['name']).'</option>';
                if (isset($permitted['sub'][$forum['fid']])) {
                    foreach($permitted['sub'][$forum['fid']] as $sub) {
                        $forumselect[] = '<option value="'.intval($sub['fid']).'"'.($sub['fid'] == $currentfid ? ' selected="selected"' : '').'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &raquo; '.fnameOut($sub['name']).'</option>';
                    }
                }
            }
        }
        $forumselect[] = '<option value="" disabled="disabled">&nbsp;</option>';
    }
    $forumselect[] = '</select>';
    return implode("\n", $forumselect);
}

function forumJump() {
    global $fid, $lang, $selHTML;

    // Initialize $forumselect
    $forumselect = array();
    $checkid = max($fid, getInt('gid', 'r'));

    $forumselect[] = "<select onchange=\"if (this.options[this.selectedIndex].value) {window.location=(''+this.options[this.selectedIndex].value)}\">";
    $forumselect[] = '<option value="">'.$lang['forumjumpselect'].'</option>';

    // Populate $forumselect
    $permitted = getStructuredForums(TRUE);

    if (0 == count($permitted['group']['0']) && 0 == count($permitted['forum']['0'])) {
        return '';
    }

    foreach($permitted['forum']['0'] as $forum) {
        $dropselc1 = ( $checkid == $forum['fid'] ) ? $selHTML : '';
        $forumselect[] = '<option value="forumdisplay.php?fid='.intval($forum['fid']).'" '.$dropselc1.'> &nbsp; &raquo; '.fnameOut($forum['name']).'</option>';
        if (isset($permitted['sub'][$forum['fid']])) {
            foreach($permitted['sub'][$forum['fid']] as $sub) {
                $dropselc2 = ( $checkid == $sub['fid'] ) ? $selHTML : '';
                $forumselect[] = '<option value="forumdisplay.php?fid='.intval($sub['fid']).'" '.$dropselc2.'>&nbsp; &nbsp; &raquo; '.fnameOut($sub['name']).'</option>';
            }
        }
    }

    foreach($permitted['group']['0'] as $group) {
        if (isset($permitted['forum'][$group['fid']])) {
            $dropselc3 = ( $checkid == $group['fid'] ) ? $selHTML : '';
            $forumselect[] = '<option value=""></option>';
            $forumselect[] = '<option value="index.php?gid='.intval($group['fid']).'" '.$dropselc3.'>'.fnameOut($group['name']).'</option>';
            foreach($permitted['forum'][$group['fid']] as $forum) {
                $dropselc4 = ( $checkid == $forum['fid'] ) ? $selHTML : '';
                $forumselect[] = '<option value="forumdisplay.php?fid='.intval($forum['fid']).'" '.$dropselc4.'> &nbsp; &raquo; '.fnameOut($forum['name']).'</option>';
                if (isset($permitted['sub'][$forum['fid']])) {
                    foreach($permitted['sub'][$forum['fid']] as $sub) {
                        $dropselc5 = ( $checkid == $sub['fid'] ) ? $selHTML : '';
                        $forumselect[] = '<option value="forumdisplay.php?fid='.intval($sub['fid']).'" '.$dropselc5.'>&nbsp; &nbsp; &raquo; '.fnameOut($sub['name']).'</option>';
                    }
                }
            }
        }
    }
    $forumselect[] = '</select>';
    return implode("\n", $forumselect);
}

/**
 * Creates a set of boolean permissions for a specific forum.
 *
 * Normal Usage Example
 *  $fid = 1;
 *  $forum = getForum($fid);
 *  $perms = checkForumPermissions($forum);
 *  if ($perms[X_PERMS_VIEW]) { //$self is allowed to view $forum }
 * Masquerade Example
 *  $result = $db->query('SELECT * FROM '.X_PREFIX.'members WHERE uid=1');
 *  $user = $db->fetch_array($result);
 *  $perms = checkForumPermissions($forum, $user['status']);
 *  if ($perms[X_PERMS_VIEW]) { //$user is allowed to view $forum }
 * Masquerade Example 2
 *  $perms = checkForumPermissions($forum, 'Moderator');
 *  if ($perms[X_PERMS_VIEW]) { //Moderators are allowed to view $forum }
 *
 * @param array $forum One query row from the forums table, preferably provided by getForum().
 * @param string $user_status_in Optional. Masquerade as this user status, e.g. 'Guest'
 * @return array Of bools, indexed by X_PERMS_* constants.
 */
function checkForumPermissions($forum, $user_status_in=FALSE) {
    global $self, $status_enum;

    if (is_string($user_status_in)) {
        $user_status = $status_enum[$user_status_in];
    } else {
        $user_status = $status_enum[$self['status']];
    }

    // 1. Initialize $ret with zero permissions
    $ret = array_fill(0, X_PERMS_COUNT, FALSE);
    $ret[X_PERMS_POLL] = FALSE;
    $ret[X_PERMS_THREAD] = FALSE;
    $ret[X_PERMS_REPLY] = FALSE;
    $ret[X_PERMS_VIEW] = FALSE;
    $ret[X_PERMS_USERLIST] = FALSE;
    $ret[X_PERMS_PASSWORD] = FALSE;

    // 2. Check Forum Postperm
    $pp = explode(',', $forum['postperm']);
    foreach($pp as $key=>$val) {
        if ((intval($val) & $user_status) != 0) {
            $ret[$key] = TRUE;
        }
    }

    // 3. Check Forum Userlist
    if ($user_status_in === FALSE) {
        $userlist = $forum['userlist'];

        if (modcheck($self['username'], $forum['moderator'], FALSE) == "Moderator") {
            $ret[X_PERMS_USERLIST] = TRUE;
            $ret[X_PERMS_VIEW] = TRUE;
        } elseif (!X_GUEST) {
            $users = explode(',', $userlist);
            foreach($users as $user) {
                if ( strtolower(trim($user)) === strtolower($self['username']) ) {
                    $ret[X_PERMS_USERLIST] = TRUE;
                    $ret[X_PERMS_VIEW] = TRUE;
                    break;
                }
            }
        }
    }

    // 4. Check COPPA Flag
    $coppa = coppa_check();

    // 5. Set Effective Permissions
    $ret[X_PERMS_POLL]   = $ret[X_PERMS_RAWPOLL]   && $coppa;
    $ret[X_PERMS_THREAD] = $ret[X_PERMS_RAWTHREAD] && $coppa;
    $ret[X_PERMS_REPLY]  = $ret[X_PERMS_RAWREPLY]  && $coppa;
    $ret[X_PERMS_VIEW]   = $ret[X_PERMS_RAWVIEW] || $ret[X_PERMS_USERLIST];

    // 6. Check Forum Password
    $pwinput = postedVar('fidpw'.$forum['fid'], '', FALSE, FALSE, FALSE, 'c');
    if ( $forum['password'] == '' || $pwinput === $forum['password'] ) {
        $ret[X_PERMS_PASSWORD] = TRUE;
    }

    return $ret;
}

/**
 * Enables you to do complex comparisons without string parsing.
 *
 * Normal Usage Example
 *  $fid = 1;
 *  $forum = getForum($fid);
 *  $viewperms = getOneForumPerm($forum, X_PERMS_RAWVIEW);
 *  if ($viewperms >= $status_enum['Member']) { //Some non-staff status has perms to view $forum }
 *  if ($viewperms == $status_enum['Guest']) { //$forum is guest-only }
 *  if ($viewperms == $status_enum['Member'] - 1) { //$forum is staff-only }
 *
 * @param array $forum
 * @param int $bitfield Enumerated by X_PERMS_RAW* constants.  Other X_PERMS_* values will not work!
 * @return bool
 */
function getOneForumPerm($forum, $bitfield) {
    $pp = explode(',', $forum['postperm']);
    return $pp[$bitfield];
}

function handlePasswordDialog($fid) {
    global $db, $full_url, $url, $THEME, $lang;

    $fid = intval($fid);
    $pwinput = postedVar('pw', '', FALSE, FALSE);

    $forum = getForum($fid);
    if (strlen($pwinput) != 0 && $forum !== FALSE) {
        if ( $pwinput === $forum['password'] ) {
            put_cookie('fidpw'.$fid, $forum['password'], (time() + (86400*30)));
            $newurl = preg_replace('/[^\x20-\x7e]/', '', $url);
            redirect($full_url.substr($newurl, strlen($cookiepath)), 0);
        } else {
            eval('$pwform = "'.template('forumdisplay_password').'";');
            error($lang['invalidforumpw'], true, '', $pwform, false, true, false, true);
        }
    } else {
        eval('$pwform = "'.template('forumdisplay_password').'";');
        error($lang['forumpwinfo'], true, '', $pwform, false, true, false, true);
    }
}

function createLangFileSelect($currentLangFile) {
    global $db;

    $lfs = array();

    $query = $db->query("SELECT b.devname, t.cdata "
                      . "FROM ".X_PREFIX."lang_base AS b "
                      . "LEFT JOIN ".X_PREFIX."lang_text AS t USING (langid) "
                      . "INNER JOIN ".X_PREFIX."lang_keys AS k USING (phraseid) "
                      . "WHERE k.langkey='language' "
                      . "ORDER BY t.cdata ASC");
    while ($row = $db->fetch_array($query)) {
        if ( $row['devname'] === $currentLangFile ) {
            $lfs[] = '<option value="'.$row['devname'].'" selected="selected">'.$row['cdata'].'</option>';
        } else {
            $lfs[] = '<option value="'.$row['devname'].'">'.$row['cdata'].'</option>';
        }
    }
    return '<select name="langfilenew">'.implode("\n", $lfs).'</select>';
}

/**
 * Creates an XHTML link to the forum search page.
 *
 * @param int $fid Optional. Current FID number used to create a context-sensitive search.
 * @return string Empty string if the forum search page is disabled.
 */
function makeSearchLink($fid=0) {
    global $THEME, $lang, $SETTINGS;

    $fid = intval($fid);

    if ($SETTINGS['searchstatus'] == 'on') {
        $fid = intval($fid);
        if ($fid == 0) {
            $fid = '';
        } else {
            $fid = "?fid=$fid";
        }
        return '<img src="'.$THEME['imgdir'].'/top_search.gif" alt="'.$lang['altsearch'].'" border="0" /> <a href="search.php'.$fid.'"><font class="navtd">'.$lang['textsearch'].'</font></a> &nbsp; ';
    } else {
        return '';
    }
}

/**
 * Sets an SEO variable used in the header template to indicate the proper current relative URI.
 *
 * @param string $relURI Path to the current page, relative to the base href (see header.php).
 */
function setCanonicalLink($relURI) {
    global $canonical_link, $cookiepath, $url;

    $testurl = $cookiepath;
    if ($relURI != './') {
        $testurl .= str_replace('&amp;', '&', $relURI);
    }
    if ( $url !== $testurl ) {
        $canonical_link = '<link rel="canonical" href="'.$relURI.'" />';
    }
}

function phpShorthandValue($ininame) {
    $rawstring = trim(ini_get($ininame));
    $rchr = strtoupper(substr($rawstring, -1));
    $value = (int) $rawstring;
    switch ($rchr) {
    case 'G':
        $value *= 1073741824;
        break;
    case 'M':
        $value *= 1048576;
        break;
    case 'K':
        $value *= 1024;
        break;
    default:
        break;
    }
    return $value;
}

/**
 * Simple SMTP message From header formation.
 *
 * @since 1.9.11.08
 * @param string $fromname Will be converted to an SMTP quoted string.
 * @param string $fromaddress Must be a fully validated e-mail address.
 * @return string
 */
function smtpHeaderFrom($fromname, $fromaddress) {
    $fromname = preg_replace('@([^\\t !\\x23-\\x5b\\x5d-\\x7e])@', '\\\\$1', $fromname);
    return 'From: "'.$fromname.'" <'.$fromaddress.'>';
}

/**
 * Generate a nonce.
 *
 * The XMB schema is currently limited to a 12-byte key length, and as such
 * does not offer user uniqueness beyond simple randomization.
 *
 * \XMB\Token\create() replaces this function for all purposes other than anonymous captcha.
 *
 * @since 1.9.11.11
 * @param string $key The known value, such as what the nonce may be used for.
 * @return string
 */
function nonce_create($key) {
    global $db, $self;

    $key = substr($key, 0, X_NONCE_KEY_LEN);
    $db->escape_fast($key);
    $nonce = bin2hex(random_bytes(16));
    $time = time();
    $db->query("INSERT INTO ".X_PREFIX."captchaimages (imagehash, imagestring, dateline) VALUES ('$nonce', '$key', '$time')");

    return $nonce;
}

/**
 * Reveal the nonce/key pair to the user, as in CAPTCHA.
 *
 * @since 1.9.11.11
 * @param  string $nonce The user input.
 * @param  int    $key_length The known length of the key.
 * @return string The key value.
 */
function nonce_peek($nonce, $key_length) {
    global $db;

    $key_length = (int) $key_length;
    if ($key_length >= X_NONCE_KEY_LEN) return '';  //Since the schema is so constrained, keep all the 12-byte keys secure.

    $db->escape_fast($nonce);
    $time = time() - X_NONCE_MAX_AGE;
    $result = $db->query(
        "SELECT imagestring
         FROM ".X_PREFIX."captchaimages
         WHERE imagehash='$nonce' AND dateline >= $time AND LENGTH(imagestring) = $key_length"
    );
    if ($db->num_rows($result) === 1) {
        return $db->result($result, 0);
    }
    return '';
}

/**
 * Test a nonce.
 *
 * @since 1.9.11.11
 * @param string $key The same value used in nonce_create().
 * @param string $nonce The user input.
 * @param int    $expire Optional. Number of seconds for which any nonce having the same $key will be valid.
 * @return bool True only if the user provided a unique nonce for the key/nonce pair.
 */
function nonce_use($key, $nonce, $expire = 0) {
    global $db;

    $key = substr($key, 0, X_NONCE_KEY_LEN);
    $db->escape_fast($key);
    $db->escape_fast($nonce);
    $time = time() - X_NONCE_MAX_AGE;
    $sql_expire = "dateline < $time";
    if ($expire > 0 && $expire < X_NONCE_MAX_AGE) {
        $time = time() - $expire;
        $sql_expire .= " OR imagestring='$key' AND dateline < $time";
    }
    $db->query("DELETE FROM ".X_PREFIX."captchaimages WHERE $sql_expire");
    $db->query("DELETE FROM ".X_PREFIX."captchaimages WHERE imagehash='$nonce' AND imagestring='$key'");

    return ($db->affected_rows() === 1);
}

/**
 * Central place to get the image URL pattern.
 *
 * Remember, this is also duplicated in js/header.js
 *
 * @since 1.9.11.15
 * @return string Regular expression for a user-provided URL to an image.
 */
function get_img_regexp( bool $https_only = false ): string {
    if ( $https_only ) {
        return '(https):\/\/([:a-z\.\/_\-0-9%~]+)(\?[a-z=0-9&_\-;~]*)?';
    } else {
        return '(https?|ftp):\/\/([:a-z\.\/_\-0-9%~]+)(\?[a-z=0-9&_\-;~]*)?';
    }
}

/**
 * Convert user 'site' input to a reasonable URL.
 *
 * @since 1.9.11.15
 * @param string $site The members.site value retrieved from the database.
 * @return string A URL or an empty string.
 */
function format_member_site($site) {
    $site = trim($site);
    $length = strlen($site);

    if ( $length < 4 ) {
        // Found some garbage value like 'a.b'
        $url = '';
    } else if ( false === strpos( $site, '.' ) ) {
        // Found some garbage value like 'aaaa'
        $url = '';
    } else if ( 1 !== preg_match( '@^https?://@i', $site ) ) {
        // Scheme missing, assume it starts with a domain name.
        $url = "http://$site";
    } else if ( $length < 11 ) {
        // Found some garbage value like 'http://a.b'
        $url = '';
    } else {
        $url = $site;
    }
    
    return $url;
}

/**
 * Send email with default headers.
 *
 * @since 1.9.11.15
 * @param string $to      Pass through to altMail()
 * @param string $subject Pass through to altMail()
 * @param string $message Pass through to altMail()
 * @param string $charset The character set used in $message param.
 * @param bool   $html    Optional. Set to true if the $message param is HTML formatted.
 * @return bool
 */
function xmb_mail( string $to, string $subject, string $message, string $charset, bool $html = false ) {
    global $self, $bbname, $adminemail, $cookiedomain;

    if ( PHP_OS == 'WINNT' || PHP_OS == 'WIN32' ) {  // Official XMB hack for PHP bug #45305 a.k.a. #28038
        ini_set( 'sendmail_from', $adminemail );
    }

    $rawbbname = htmlspecialchars_decode( $bbname, ENT_NOQUOTES );
    if ( ! empty( $self ) ) {
        $rawusername = htmlspecialchars_decode( $self['username'], ENT_QUOTES );
    }

    if ( $html ) {
        $content_type = 'text/html';
    } else {
        $content_type = 'text/plain';
    }

    $headers = array();
    $headers[] = smtpHeaderFrom( $rawbbname, $adminemail );
    $headers[] = "X-Mailer: PHP";
    $headers[] = "X-AntiAbuse: Board servername - $cookiedomain";
    if ( ! empty( $self ) ) {
        $headers[] = "X-AntiAbuse: Username - $rawusername";
    }
    $headers[] = "Content-Type: $content_type; charset=$charset";
    $headers = implode( "\r\n", $headers );

    $params = "-f $adminemail";

    return altMail( $to, $subject, $message, $headers, $params );
}

/**
 * Generates the HTML for the timezone dropdown list.
 *
 * Caller needs to pre-load the timezone_control template for efficiency.
 *
 * @since 1.9.12
 * @param string $offset Must be in the MySQL Decimal format with 2 places after the decimal.
 * @return string HTML
 */
function timezone_control( string $offset ): string {
    global $lang, $selHTML;
    
    $total = 37;
    
    $sel = [];
    for ($i = 1; $i <= $total; $i++) {
        $sel[$i] = '';
    }
    
    $offset = number_format( (float) $offset, 2 );

    switch( $offset ) {
    case '-12.00':
        $sel[1] = $selHTML;
        break;
    case '-11.00':
        $sel[2] = $selHTML;
        break;
    case '-10.00':
        $sel[3] = $selHTML;
        break;
    case '-9.50':
        $sel[37] = $selHTML;
        break;
    case '-9.00':
        $sel[4] = $selHTML;
        break;
    case '-8.00':
        $sel[5] = $selHTML;
        break;
    case '-7.00':
        $sel[6] = $selHTML;
        break;
    case '-6.00':
        $sel[7] = $selHTML;
        break;
    case '-5.00':
        $sel[8] = $selHTML;
        break;
    case '-4.00':
        $sel[9] = $selHTML;
        break;
    case '-3.50':
        $sel[10] = $selHTML;
        break;
    case '-3.00':
        $sel[11] = $selHTML;
        break;
    case '-2.00':
        $sel[12] = $selHTML;
        break;
    case '-1.00':
        $sel[13] = $selHTML;
        break;
    case '1.00':
        $sel[15] = $selHTML;
        break;
    case '2.00':
        $sel[16] = $selHTML;
        break;
    case '3.00':
        $sel[17] = $selHTML;
        break;
    case '3.50':
        $sel[18] = $selHTML;
        break;
    case '4.00':
        $sel[19] = $selHTML;
        break;
    case '4.50':
        $sel[20] = $selHTML;
        break;
    case '5.00':
        $sel[21] = $selHTML;
        break;
    case '5.50':
        $sel[22] = $selHTML;
        break;
    case '5.75':
        $sel[23] = $selHTML;
        break;
    case '6.00':
        $sel[24] = $selHTML;
        break;
    case '6.50':
        $sel[25] = $selHTML;
        break;
    case '7.00':
        $sel[26] = $selHTML;
        break;
    case '8.00':
        $sel[27] = $selHTML;
        break;
    case '9.00':
        $sel[28] = $selHTML;
        break;
    case '9.50':
        $sel[29] = $selHTML;
        break;
    case '10.00':
        $sel[30] = $selHTML;
        break;
    case '10.50':
        $sel[36] = $selHTML;
        break;
    case '11.00':
        $sel[31] = $selHTML;
        break;
    case '12.00':
        $sel[32] = $selHTML;
        break;
    case '12.75':
        $sel[35] = $selHTML;
        break;
    case '13.00':
        $sel[33] = $selHTML;
        break;
    case '14.00':
        $sel[34] = $selHTML;
        break;
    case '0.00':
    default:
        $sel[14] = $selHTML;
    }

    eval('$control = "'.template('timezone_control').'";');
    return $control;
}

/**
 * Determine which browser is in use, and return a human-friendly description.
 *
 * @since 1.9.12
 * @param string $raw
 * @return string
 */
function parse_user_agent( string $raw ): string {
    if     (strpos($raw, 'Opera'     ) || strpos($raw, 'OPR/')     ) return 'Opera'            ;
    elseif (strpos($raw, 'Edge'      )                             ) return 'Edge'             ;
    elseif (strpos($raw, 'Chromium'  )                             ) return 'Chromium'         ;
    elseif (strpos($raw, 'Chrome'    )                             ) return 'Chrome'           ;
    elseif (strpos($raw, 'Safari'    )                             ) return 'Safari'           ;
    elseif (strpos($raw, 'Seamonkey' )                             ) return 'Seamonkey'        ;
    elseif (strpos($raw, 'Firefox'   )                             ) return 'Firefox'          ;
    elseif (strpos($raw, 'MSIE'      ) || strpos($raw, 'Trident/7')) return 'Internet Explorer';
    else return $raw;
}

/**
 * Calculates extra theme strings that are dynamically generated for every hit.
 *
 * @since 1.9.12
 */
function more_theme_vars() {
    global $THEME, $SETTINGS;

    // Alters certain visibility-variables
    if (false === strpos($THEME['bgcolor'], '.')) {
        $THEME['bgcode'] = "background-color: {$THEME['bgcolor']};";
    } else {
        $THEME['bgcode'] = "background-image: url({$THEME['imgdir']}/{$THEME['bgcolor']});";
    }

    if (false === strpos($THEME['catcolor'], '.')) {
        $THEME['catbgcode'] = "bgcolor='{$THEME['catcolor']}'";
        $THEME['catcss'] = "background-color: {$THEME['catcolor']};";
    } else {
        $THEME['catbgcode'] = "style='background-image: url({$THEME['imgdir']}/{$THEME['catcolor']})'";
        $THEME['catcss'] = "background-image: url({$THEME['imgdir']}/{$THEME['catcolor']});";
    }

    if (false === strpos($THEME['top'], '.')) {
        $THEME['topbgcode'] = "bgcolor='{$THEME['top']}'";
    } else {
        $THEME['topbgcode'] = "style='background-image: url({$THEME['imgdir']}/{$THEME['top']})'";
    }

    $l = parse_url($THEME['boardimg']);
    if (!isset($l['scheme']) || !isset($l['host'])) {
        $boardimg = $THEME['imgdir'].'/'.$THEME['boardimg'];
    }
    $THEME['logo'] = '<a href="./"><img src="'.$boardimg.'" alt="'.$SETTINGS['bbname'].'" border="0" /></a>';

    // Font stuff...
    $fontedit = preg_replace('#(\D)#', '', $THEME['fontsize']);
    $fontsuf = preg_replace('#(\d)#', '', $THEME['fontsize']);
    $THEME['font1'] = $fontedit-1 . $fontsuf;
    $THEME['font3'] = $fontedit+2 . $fontsuf;
}

/**
 * Checks if guest recently tried to register and disclosed age < 13
 *
 * @since 1.9.12
 * @return bool When false the website must not collect any information from the guest.
 */
function coppa_check(): bool {
    $privacy =  postedVar( 'privacy', '', false, false, false, 'c' );
    return 'xmb' != $privacy;
}

/**
 * Handles the [email] BBCode.
 *
 * @since 1.9.12.03
 * @param array $matches Expects $matches[0] to be the raw BBCode, $matches[1] to be the URL only, and optionally $matches[2] to be the display text.
 * @return string The HTML replacement for $matches[0].
 */
function bbcode_emails( array $matches ): string {
    $text = $matches[2] ?? $matches[1];
    $address = bbcode_out( $matches[1] );

    return "<a href='mailto:$address'>$text</a>";
}

/**
 * Handles the [img] BBCode.
 *
 * @since 1.9.12.03
 * @param array $matches Expects different elements depending on the pattern.
 * @return string The HTML replacement for $matches[0].
 */
function bbcode_imgs( array $matches ): string {
    if ( count( $matches ) < 5 ) {
        $width = 0;
        $height = 0;
        $scheme = $matches[1];
        $path = $matches[2];
        $query = $matches[3] ?? '';
    } else {
        $width = (int) $matches[1];
        $height = (int) $matches[2];
        $scheme = $matches[3];
        $path = $matches[4];
        $query = $matches[5] ?? '';
    }

    if ( $width < 1 || $height < 1 ) {
        $size = '';
    } else {
        $size = "width='$width' height='$height'";
    }

    $address = bbcode_out( "$scheme://$path$query" );

    return "<!-- nobr --><img $size src='$address' alt='' border='0' /><!-- /nobr -->";
}

return;