<?php
/* $Id: spelling.inc.php,v 1.1.2.3 2007/03/19 21:53:30 ajv Exp $ */
/*
    © 2001 - 2007 Aventure Media & The XMB Development Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
    02111-1307, USA.

    For full license information, please see License.txt in the docs folder.
*/

if (!defined('IN_CODE') && (defined('DEBUG') && DEBUG == false)) {
    exit ("Not allowed to run this file directly.");
}

class spelling {
    var $language   = '';
    var $link   = 0;
    var $mode   = 0;
    
    function spelling($language='en', $mode=PSPELL_NORMAL) {
        global $charset;
        
        if (!extension_loaded('pspell')) {
            error('The pspell/aspell extension is not currently loaded/built into PHP, the spellchecker will not work');
        }
        
        $charset = '';
        
        $this->language = $language;
        $this->link = pspell_new($language, '', '', $charset, $mode);
        $this->mode = $mode;
        
        return true;
    }
    
    function check_word($word) {
        if (pspell_check($this->link, $word)) {
            return true;
        } else {
            return false;
        }
    }
    
    function set_mode($mode=PSPELL_NORMAL) {
        $this->mode = $mode;
        return pspell_config_mode($mode);
    }
    
    function get_mode() {
        return $this->mode;
    }
    
    function check_text($text) {
        $return = array();
        
        $words = preg_split("/[\W]+/", $text);
        
        foreach ($words as $word) {
            if (!$this->check_word($word)) {
                $return[$word] = pspell_suggest($this->link, $word);
            }
        }
        
        return $return;
    }
}
?>
