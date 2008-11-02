<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Alpha Two - This software should not be used for any purpose after 30 November 2008.
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

// installNewTranslation() handles all logic necessary to install an XMB translation file.
// Parameter $upload must be a string containing the entire translation file.
// Returns TRUE on success.
function installNewTranslation($upload) {
    global $db;

    // Perform sanity checks
    $upload = str_replace(array('<'.'?php', '?'.'>'), array('', ''), $upload);
    eval('return true; '.$upload); //This will safely error out if file is not valid PHP.

    // Parse the uploaded code
    $devname = '';
    $newlang = array();
    $find = "$devname = '";
    $curpos = strpos($upload, $find);
    $tmppos = strpos($upload, "';", $curpos);
    if ($curpos === FALSE Or $tmppos === FALSE) {
        error($lang['langimportfail'], FALSE);
    }
    $curpos += strlen($find);
    $devname = substr($upload, $curpos, $tmppos - $curpos);

    // Match $lang['*'] = "*";
    preg_match_all("@\\\$lang\\['([_\\w]+)'] = (['\"]{1})(.*?)\\2;\\r?\\n@", $upload, $matches, PREG_SET_ORDER);

    // Load unparsed strings into $newlang array.
    foreach($matches as $match) {
        // Parse this string
        $key = $match[1];
        $quoting = $match[2];
        $phrase = $match[3];
        $curpos = 0;
        while(($curpos = strpos($phrase, "\\", $curpos)) !== FALSE) {
            switch ($phrase[$curpos + 1]) {
            case "\\":
                $phrase = substr($phrase, 0, $curpos).substr($phrase, $curpos + 1);
                break;
            case "'":
                if ($quoting == "'") {
                    $phrase = substr($phrase, 0, $curpos).substr($phrase, $curpos + 1);
                }
                break;
            case '"':
                if ($quoting == '"') {
                    $phrase = substr($phrase, 0, $curpos).substr($phrase, $curpos + 1);
                }
                break;
            case '$':
                if ($quoting == '"') {
                    $phrase = substr($phrase, 0, $curpos).substr($phrase, $curpos + 1);
                }
                break;
            case 'n':
                if ($quoting == '"') {
                    $phrase = substr($phrase, 0, $curpos)."\n".substr($phrase, $curpos + 2);
                }
                break;
            default:
                break;
            }
            $curpos++;
        }
        // Save parsed string.
        $newlang[$key] = $phrase;
    }

    // Ensure all new keys are present in the database.
    $newkeys = array_keys($newlang);
    $oldkeys = array();
    $phraseids = array();
    $result = $db->query("SELECT langkey FROM ".X_PREFIX."lang_keys");
    while ($row = $db->fetch_array($result)) {
        $oldkeys[] = $row['langkey'];
    }
    $db->free_result($result);
    $newkeys = array_diff($newkeys, $oldkeys);
    if (count($newkeys) > 0) {
        $sql = implode("'), ('", $newkeys);
        $sql = "INSERT INTO ".X_PREFIX."lang_keys (langkey) VALUES ('$sql')";
        $db->query($sql);
    }

    // Query Key IDs
    $result = $db->query("SELECT * FROM ".X_PREFIX."lang_keys");
    while ($row = $db->fetch_array($result)) {
        $oldkeys[] = $row['langkey'];
        $phraseids[$row['langkey']] = $row['phraseid'];
    }
    $db->free_result($result);

    // Ensure $devname is present in the database.
    $result = $db->query("SELECT langid FROM ".X_PREFIX."lang_base WHERE devname='$devname'");
    if ($db->num_rows($result) == 0) {
        $db->query("INSERT INTO ".X_PREFIX."lang_base SET devname='$devname'");
        $langid = $db->insert_id();
    } else {
        $row = $db->fetch_array($result);
        $langid = $row['langid'];
    }
    $db->free_result($result);

    // Install the new translation
    $db->query("DELETE FROM ".X_PREFIX."lang_text WHERE langid=$langid");
    $flag = FALSE;
    $sql = '';
    foreach($newlang as $key=>$value) {
        $phraseid = $phraseids[$key];
        $value = $db->escape($value);
        if ($flag) {
            $sql .= ", ($langid, $phraseid, '$value')";
        } else {
            $sql .= "($langid, $phraseid, '$value')";
            $flag = TRUE;
        }
    }
    $query = $db->query("INSERT INTO ".X_PREFIX."lang_text (langid, phraseid, cdata) VALUES $sql");

    // Cleanup unused keys.
    $oldids = array();
    $sql = ("SELECT k.phraseid "
          . "FROM ".X_PREFIX."lang_keys AS k "
          . "LEFT JOIN ".X_PREFIX."lang_text USING (phraseid) "
          . "GROUP BY k.phraseid "
          . "HAVING COUNT(langid) = 0");
    $result = $db->query($sql);
    while($row = $db->fetch_array($result)) {
        $oldids[] = $row['phraseid'];
    }
    if (count($oldids) > 0) {
        $oldids = implode(", ", $oldids);
        $db->query("DELETE FROM ".X_PREFIX."lang_keys WHERE phraseid IN ($oldids)");
    }
    
    return $query;
}

// langPanic() handles any unexpected configuration that prevented the translation database from loading.
function langPanic() {
    if (X_SCRIPT == 'upgrade.php') {
        return TRUE;
    }
    if (!loadLang()) {
        if (file_exists(ROOT.'lang/English.lang.php')) {
            $upload = file_get_contents(ROOT.'lang/English.lang.php');
            installNewTranslation($upload);
            if (loadLang()) {
                return TRUE;
            }
        }
        exit ('Error: XMB failed to start because the default language is missing.  Please place English.lang.php in the lang subfolder to correct this.');
    }
}

?>
