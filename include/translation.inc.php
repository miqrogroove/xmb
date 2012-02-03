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
 * Adds a new $lang value to the current translation and also adds a new key if not found.
 *
 * In other words, setNewLangValue('stats1', 'Statistics'); is equivalent to importing $lang['stats1'] = 'Statistics';
 *
 * @param string $langkey New translation key name.
 * @param string $cdata New value and it must be db-escaped!
 * @return bool TRUE on success, FALSE if no translation has been loaded.
 * @author Robert Chapin (miqrogroove)
 */
function setNewLangValue($langkey, $cdata) {
    global $db, $langfile;

    $db->escape_fast($langkey);

    $result = $db->query("SELECT phraseid FROM ".X_PREFIX."lang_keys WHERE langkey='$langkey'");
    if ($db->num_rows($result) == 0) {
        $newkey = TRUE;
        $db->query("INSERT INTO ".X_PREFIX."lang_keys SET langkey='$langkey'");
        $phraseid = $db->insert_id();
    } else {
        $newkey = FALSE;
        $row = $db->fetch_array($result);
        $db->free_result($result);
        $phraseid = $row['phraseid'];
    }

    $result = $db->query("SELECT langid FROM ".X_PREFIX."lang_base WHERE devname='$langfile'");
    if ($db->num_rows($result) == 0) {
        return FALSE;
    }
    $row = $db->fetch_array($result);
    $db->free_result($result);
    $langid = $row['langid'];

    if (!$newkey) {
        $db->query("DELETE FROM ".X_PREFIX."lang_text WHERE langid=$langid AND phraseid=$phraseid");
    }
    $db->query("INSERT INTO ".X_PREFIX."lang_text SET langid=$langid, phraseid=$phraseid, cdata='$cdata'");

    return TRUE;
}

/**
 * Sets a $lang value in the current translation for an existing key.
 *
 * @param int $phraseid is the primary key value of the lang_keys table.
 * @param string $cdata is the new value and it must be db-escaped!
 * @return bool TRUE on success.
 */
function setLangValue($phraseid, $cdata) {
    global $db, $langfile;

    $phraseid = intval($phraseid);

    $result = $db->query("SELECT phraseid FROM ".X_PREFIX."lang_keys WHERE phraseid=$phraseid");
    if ($db->num_rows($result) == 0) {
        return FALSE;
    }
    $db->free_result($result);
    $result = $db->query("SELECT langid FROM ".X_PREFIX."lang_base WHERE devname='$langfile'");
    if ($db->num_rows($result) == 0) {
        return FALSE;
    }
    $row = $db->fetch_array($result);
    $db->free_result($result);
    $langid = $row['langid'];

    $db->query("DELETE FROM ".X_PREFIX."lang_text WHERE langid=$langid AND phraseid=$phraseid");
    $db->query("INSERT INTO ".X_PREFIX."lang_text SET langid=$langid, phraseid=$phraseid, cdata='$cdata'");

    return TRUE;
}

/**
 * Adds an array of new $lang values to the specified translation.
 *
 * @param array $lang Read-Only Variable. Associative array of new key/value pairs.  Values should be raw cdata.
 * @param string $langfile Read-Only Variable. Devname of the translation to add to.
 * @return bool TRUE on success, FALSE if the devname does not exist.
 */
function setManyLangValues(&$lang, &$langfile) {
    global $db;

    // Ensure devname is present in the database.
    $result = $db->query("SELECT langid FROM ".X_PREFIX."lang_base WHERE devname='$langfile'");
    if ($db->num_rows($result) == 0) {
        return FALSE;
    }
    $row = $db->fetch_array($result);
    $db->free_result($result);
    $langid = $row['langid'];

    // Ensure all new keys are present in the database.
    $newkeys = array_keys($lang);
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
        $phraseids[$row['langkey']] = $row['phraseid'];
    }
    $db->free_result($result);

    // Save the new values
    $flag = FALSE;
    $sql = '';
    foreach($lang as $key=>$value) {
        $phraseid = $phraseids[$key];
        $db->escape_fast($value);
        if ($flag) {
            $sql .= ", ($langid, $phraseid, '$value')";
        } else {
            $sql .= "($langid, $phraseid, '$value')";
            $flag = TRUE;
        }
    }
    $query = $db->query("REPLACE ".X_PREFIX."lang_text (langid, phraseid, cdata) VALUES $sql");

    $db->query('OPTIMIZE TABLE '.X_PREFIX.'lang_text');

    return TRUE;
}

/**
 * Handles all logic necessary to install an XMB translation file.
 *
 * @param string $upload Read/Write Variable. Must contain the entire translation file.
 * @return bool TRUE on success.
 */
function installNewTranslation(&$upload) {
    global $db, $SETTINGS;

    // Perform sanity checks
    $upload = str_replace(array('<'.'?php', '?'.'>'), array('', ''), $upload);
    if (!eval('return true; '.$upload)) {
        if ($SETTINGS['bbstatus'] == 'off') { // Possible upgrade in progress
            header('HTTP/1.0 503 Service Unavailable');
            header('Retry-After: 3600');
        } else {
            header('HTTP/1.0 500 Internal Server Error');
        }
        exit('XMB failed to parse the translation file.  Valid PHP syntax is required.');
    }

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
    setManyLangValues($newlang, $devname);

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

    return TRUE;
}

/**
 * Creates a PHP file of a single translation.
 *
 * String literals are always expressed in double quotes because the original quoting was not saved during installation.
 *
 * @param int $langid Primary key value of the lang_base table.
 * @param string $devname Write-Only Variable. Returns the lang_base.devname value.
 * @return string|bool Entire file on success, FALSE otherwise.
 */
function exportTranslation($langid, &$devname) {
    global $db;

    $langid = intval($langid);

    $result = $db->query("SELECT devname FROM ".X_PREFIX."lang_base WHERE langid=$langid");
    if ($db->num_rows($result) == 0) {
        return FALSE;
    }
    $row = $db->fetch_array($result);
    $db->free_result($result);
    $devname = $row['devname'];

    $query = "SELECT k.langkey, t.cdata "
           . "FROM ".X_PREFIX."lang_keys AS k "
           . "LEFT JOIN ".X_PREFIX."lang_text AS t USING (phraseid) "
           . "WHERE t.langid=$langid "
           . "GROUP BY k.langkey ORDER BY k.langkey";
    $query = $db->query($query);
    $contents = '';
    $meta = '';
    while($row = $db->fetch_array($query)) {
        if (in_array($row['langkey'], array('charset','iso639','language'))) {
            $meta .= "\$lang['{$row['langkey']}'] = '{$row['cdata']}';\r\n";
        } else {
            $value = $row['cdata'];
            $value = str_replace("\\", "\\\\", $value);
            $value = str_replace('"', '\"', $value);
            $value = str_replace('$', '\$', $value);
            $value = str_replace("\n", '\n', $value);
            $contents .= "\$lang['{$row['langkey']}'] = \"$value\";\r\n";
        }
    }
    $contents = "\$devname = '$devname';\r\n".$meta.$contents;

    return $contents;
}

/**
 * Handles any unexpected configuration that prevented the translation database from loading.
 */
function langPanic() {
    global $SETTINGS;

    if (X_SCRIPT == 'upgrade.php') {
        return TRUE;
    }
    if (!loadLang()) {
        if (file_exists(ROOT.'Upgrade/') or file_exists(ROOT.'upgrade/') or file_exists(ROOT.'upgrade.php')) {
            header('HTTP/1.0 503 Service Unavailable');
            header('Retry-After: 3600');
            exit('We\'re sorry, a website upgrade is in progress at the moment.  Please try again in a few minutes.');
        }
        if (file_exists(ROOT.'lang/English.lang.php')) {
            $upload = file_get_contents(ROOT.'lang/English.lang.php');
            installNewTranslation($upload);
            if (loadLang()) {
                return TRUE;
            }
        }
        if ($SETTINGS['bbstatus'] == 'off') { // Possible upgrade in progress
            header('HTTP/1.0 503 Service Unavailable');
            header('Retry-After: 3600');
        } else {
            header('HTTP/1.0 500 Internal Server Error');
        }
        exit ('Error: XMB failed to start because the default language is missing.  Please place English.lang.php in the lang subfolder to correct this.');
    }
}
?>
