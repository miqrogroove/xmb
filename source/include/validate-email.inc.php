<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2012, The XMB Group
 * http://www.xmbforum2.com/
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
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

/**
 * EmailAddressValidator Class
 * http://code.google.com/p/php-email-address-validation/
 *
 * Retrieved 4 November 2008
 * Ported to PHP 4 for use with XMB
 *
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD license
 * @version Revision 5 dated 15 July 2008
 */
class EmailAddressValidator {

    /**
     * Check email address validity
     * @param   strEmailAddress     Email address to be checked
     * @return  True if email is valid, false if not
     */
    function check_email_address(&$strEmailAddress) {
        
        // If magic quotes is "on", email addresses with quote marks will
        // fail validation because of added escape characters. Uncommenting
        // the next three lines will allow for this issue.
        //if (get_magic_quotes_gpc()) { 
        //    $strEmailAddress = stripslashes($strEmailAddress); 
        //}

        // Control characters are not allowed
        if (preg_match('/[\x00-\x1F\x7F-\xFF]/', $strEmailAddress)) {
            return false;
        }

        // Split it into sections using last instance of "@"
        $intAtSymbol = strrpos($strEmailAddress, '@');
        if ($intAtSymbol === false) {
            // No "@" symbol in email.
            return false;
        }
        $arrEmailAddress[0] = substr($strEmailAddress, 0, $intAtSymbol);
        $arrEmailAddress[1] = substr($strEmailAddress, $intAtSymbol + 1);

        // Count the "@" symbols. Only one is allowed, except where 
        // contained in quote marks in the local part. Quickest way to
        // check this is to remove anything in quotes.
        $arrTempAddress[0] = preg_replace('/"[^"]+"/'
                                         ,''
                                         ,$arrEmailAddress[0]);
        $arrTempAddress[1] = $arrEmailAddress[1];
        $strTempAddress = $arrTempAddress[0] . $arrTempAddress[1];
        // Then check - should be no "@" symbols.
        if (strrpos($strTempAddress, '@') !== false) {
            // "@" symbol found
            return false;
        }

        // Check local portion
        if (!$this->check_local_portion($arrEmailAddress[0])) {
            return false;
        }

        // Check domain portion
        if (!$this->check_domain_portion($arrEmailAddress[1])) {
            return false;
        }

        // If we're still here, all checks above passed. Email is valid.
        return true;
    }

    /**
     * Checks email section before "@" symbol for validity
     * @param   strLocalPortion     Text to be checked
     * @return  True if local portion is valid, false if not
     */
    function check_local_portion(&$strLocalPortion) {
        // Local portion can only be from 1 to 64 characters, inclusive.
        // Please note that servers are encouraged to accept longer local
        // parts than 64 characters.
        if (!$this->check_text_length($strLocalPortion, 1, 64)) {
            return false;
        }
        // Local portion must be:
        // 1) a dot-atom (strings separated by periods)
        // 2) a quoted string
        // 3) an obsolete format string (combination of the above)
        $arrLocalPortion = explode('.', $strLocalPortion);
        for ($i = 0, $max = sizeof($arrLocalPortion); $i < $max; $i++) {
             if (!preg_match('.^('
                            .    '([A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]' 
                            .    '[A-Za-z0-9!#$%&\'*+/=?^_`{|}~-]{0,63})'
                            .'|'
                            .    '("[^\\\"]{0,62}")'
                            .')$.'
                            ,$arrLocalPortion[$i])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks email section after "@" symbol for validity
     * @param   strDomainPortion     Text to be checked
     * @return  True if domain portion is valid, false if not
     */
    function check_domain_portion(&$strDomainPortion) {
        // Total domain can only be from 1 to 255 characters, inclusive
        if (!$this->check_text_length($strDomainPortion, 1, 255)) {
            return false;
        }
        // Check if domain is IP, possibly enclosed in square brackets.
        if (preg_match('/^(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'
           .'(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}$/'
           ,$strDomainPortion) || 
            preg_match('/^\[(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])'
           .'(\.(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])){3}\]$/'
           ,$strDomainPortion)) {
            return true;
        } else {
            $arrDomainPortion = explode('.', $strDomainPortion);
            if (sizeof($arrDomainPortion) < 2) {
                return false; // Not enough parts to domain
            }
            for ($i = 0, $max = sizeof($arrDomainPortion); $i < $max; $i++) {
                // Each portion must be between 1 and 63 characters, inclusive
                if (!$this->check_text_length($arrDomainPortion[$i], 1, 63)) {
                    return false;
                }
                if (!preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|'
                   .'([A-Za-z0-9]+))$/', $arrDomainPortion[$i])) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Check given text length is between defined bounds
     * @param   strText     Text to be checked
     * @param   intMinimum  Minimum acceptable length
     * @param   intMaximum  Maximum acceptable length
     * @return  True if string is within bounds (inclusive), false if not
     */
    function check_text_length(&$strText, $intMinimum, $intMaximum) {
        // Minimum and maximum are both inclusive
        $intTextLength = strlen($strText);
        if (($intTextLength < $intMinimum) || ($intTextLength > $intMaximum)) {
            return false;
        } else {
            return true;
        }
    }
}

?>
