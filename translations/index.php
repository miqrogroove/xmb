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

//define('DEBUG', true);
define('IN_CODE', true);

header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$_act = (isset($_GET['act'])   ? $_GET['act']   : '');
$_slave = (isset($_GET['slave']) ? $_GET['slave'] : '');

define('LANG_INCLUDE_DIR', './');
define('IN_XMB', true);

$files = array();
if($stream = opendir(LANG_INCLUDE_DIR)) {
    while(false !== ($file = readdir($stream))) {
        if(strpos($file, '.lang.php') && $file <> 'English.lang.php') {  // why not !== false? because we don't accept '.lang.php' as a filename ;)
            $files[] = $file;
        }
    }
}

asort($files);
if ($_act == 'dl' && !empty($_slave) ) {
    if (in_array($_slave, $files) || $_slave == 'English.lang.php') {
        $content = file_get_contents(LANG_INCLUDE_DIR.$_slave);
        header("Content-disposition: attachment; filename=".$_slave);
        header("Content-Length: ".strlen($content));
        header("Content-type: unknown/unknown");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $content;
        exit;
    }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Language File Selection</title>
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
<tr><td colspan="3" class="category">Language Files:</td></tr>

<?php
foreach($files as $file){
    echo '
    <tr>
    <td class="sideSeperator">'.str_replace('.lang.php', '', $file).'</td>
    <td class="sideSeperator"><a href="languageFileCompare.php?slave='.$file.'" target="_blank">Compare "'.$file.'" to English.lang.php</a></td>
    <td class="sideSeperator"><a href="index.php?act=dl&slave='.$file.'" target="_blank">Download "'.$file.'"</a></td>
    </tr>';
}
?>
<tr>
<td colspan="3" class="sideSeperator">&nbsp;</td>
</tr>
<tr>
<td colspan="3" class="sideSeperator">
<a href="index.php?act=dl&slave=English.lang.php" target="_blank">Download "English.lang.php"</a></td>
</tr>
<tr>
<td colspan="3" class="sideSeperator">&nbsp;</td>
</tr>
<tr>
<td colspan="3" class="sideSeperator">
If the linebreaks seem missing or messedup after downloading the file ...
<br />
Copy the entire content of the text file into the TEXTAREA below, and then copy it back into the file.
<br />
Result, the linebreaks will be restored.
<br />
<textarea style="width:99%;" rows="10"></textarea>
</td>
</tr>
</table>
</body>
</html>