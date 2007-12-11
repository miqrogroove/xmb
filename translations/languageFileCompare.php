<?php
/**
 * eXtreme Message Board
 * XMB 1.9.8 Engage Final
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

define('LANG_INCLUDE_DIR', './');
define('IN_CODE', true);

function getAllLanguageFilenames() {
    $files = array();
    if ($stream = opendir(LANG_INCLUDE_DIR)) {
        while (false !== ($file = readdir($stream))) {
            if (strpos($file, '.lang.php')) {  // why not !== false? because we don't accept '.lang.php' as a filename ;)
                $files[] = $file;
            }
        }
    }
    return $files;
}

function getLanguageFileAsArray($langfile) {
    require LANG_INCLUDE_DIR.$langfile;
    return $lang;
}

if (!isset($_GET['master']) || empty($_GET['master'])) {
    $master = 'English.lang.php';
} else {
    if (false === strpos($_GET['master'], '.lang.php')) {
        $master = $_GET['master'].'.lang.php';
    } else {
        $master = $_GET['master'];
    }
}

if (!isset($_GET['slave']) || empty($_GET['slave'])) {
    exit('No Slave selected');
} else {
    if (false === strpos($_GET['slave'], '.lang.php')) {
        $slave = $_GET['slave'].'.lang.php';
    } else {
        $slave = $_GET['slave'];
    }
}
$langFiles = getAllLanguageFilenames();

if (!in_array($master, $langFiles)) {
    exit('Selected Master copy could not be located.');
}

if (!in_array($slave, $langFiles)) {
    exit('Selected Slave could not be located.');
}

if ($master === $slave or ($master === 'Base.lang.php' && $slave === 'English.lang.php') or ($slave === 'Base.lang.php' && $master === 'English.lang.php')) {
    exit('Why are you comparing the master file to itself?');
}

$masterl = getLanguageFileAsArray($master);
$slavel = getLanguageFileAsArray($slave);
$masterf = array_keys($masterl);
$slavef = array_keys($slavel);

$remove = array_diff($slavef, $masterf);
$add = array_diff($masterf, $slavef);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Language File Comparer :: Comparing <?=$slave?> (Slave) vs. <?=$master?> (Master)</title>
<style type="text/css">
    body {
        font-family: Verdana, Times New Roman, sans-serif;
        font-size: 10pt;
        color: #000000;
        background-color: #FFFFFF;
    }
    #mainTbl {
        width: 100%;
        height: 100%;
        border: 1px solid #000000;
    }
    .category {
        font-weight: bold;
        border-bottom: 1px solid #000000;
    }

    .sideSeperator {
        width: 5px;
    }

    .keyCol {
        width: 25em;
        vertical-align: top;
    }

    #copyrightFooter {
        font-size: 8pt;
        color: #000000;
        background-color: #FFFFFF;
        text-align:right;
        width: 100%;
    }
</style>
</head>
<body>
<table id="mainTbl">
<tr><td class="category" colspan="3">Language Keys missing from Slave (<?php echo $slave;?>):</td></tr>
<?php
if (count($add) > 0) {
    foreach ($add as $key=>$val) {
        ?>
        <tr>
        <td class="sideSeperator">&nbsp;</td>
        <td class="keyCol"><?=$val?></td>
        <td><?=htmlspecialchars($masterl[$val])?></td>
        </tr>
        <?php
    }
} else {
    ?>
    <tr>
    <td class="sideSeperator">&nbsp;</td>
    <td class="keyCol">No keys need to be added</td>
    <td>&nbsp;</td>
    </tr>
    <?php
}
?>
<tr>
<td colspan="2">&nbsp;</td>
</tr>
<tr><td class="category" colspan="3">Language Keys to be removed from Slave (<?php echo $slave;?>):</td></tr>
<?php
if (count($remove) > 0) {
    foreach ($remove as $key=>$val) {
        ?>
        <tr>
        <td class="sideSeperator">&nbsp;</td>
        <td colspan="2" class="keyCol"><?=$val?></td>
        </tr>
        <?php
    }
} else {
    ?>
    <tr>
    <td class="sideSeperator">&nbsp;</td>
    <td colspan="2" class="keyCol">No keys need to be removed</td>
    </tr>
    <?php
}
?>
</table>
<div id="copyrightFooter">
&copy; 2007 The XMB Group. Developed By Tularis.
</div>
</body>
</html>