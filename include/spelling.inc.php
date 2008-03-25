<?php
/**
 * XMB 1.9.9 Saigo
 *
 * Developed by the XMB Group Copyright (c) 2001-2008
 * Sponsored by iEntry Inc. Copyright (c) 2007
 *
 * http://xmbgroup.com , http://ientry.com
 *
 * This software is released under the GPL License, you should
 * have received a copy of this license with the download of this
 * software. If not, you can obtain a copy by visiting the GNU
 * General Public License website <http://www.gnu.org/licenses/>.
 *
 **/

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

class spelling {
    var $language = '';
    var $link = 0;
    var $mode = 0;

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
        foreach($words as $word) {
            if (!$this->check_word($word)) {
                $return[$word] = pspell_suggest($this->link, $word);
            }
        }
        return $return;
    }
}
?>
