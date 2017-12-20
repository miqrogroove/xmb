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

class spelling {
    var $language = '';
    var $link     = 0;
    var $mode     = 0;

    function spelling($language='en', $mode=PSPELL_NORMAL) {
        global $charset;

        if (!extension_loaded('pspell')) {
            error('The pspell/aspell extension is not currently loaded/built into PHP, the spellchecker will not work');
        }

        $charset = '';
        $this->language = $language;
        @$this->link = pspell_new($language, '', '', $charset, $mode);
        if ($this->link === FALSE) {
            error('Failed to open the spelling dictionary for language "'.htmlspecialchars($language, ENT_QUOTES).'"');
        }
        $this->mode = $mode;
        return true;
    }

    function check_word(&$word) {
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

        preg_match_all("/(?i)\\b['a-z]+\\b/", $text, $words);
        $words = $words[0];
        foreach($words as $word) {
            if (!$this->check_word($word)) {
                $return[$word] = pspell_suggest($this->link, $word);
            }
        }
        return $return;
    }
}
?>
