<?php
/**
* languageFileCompare
*
* A file that compares 2 language files and tells you what to
* add and what to remove from the slave file.
*
* @const LANG_INCLUDE_DIR (relative) path to the dir containing the master and slave langfiles
* @param string $master Provides filename for the master language file to use. Defaults to English.lang.php
* @param string $slave Provides filename for the slave language file to use.
*/

define('IN_CODE', TRUE);
define('LANG_INCLUDE_DIR', './');

function getAllLanguageFilenames() {
    $files = array();
    if($stream = opendir(LANG_INCLUDE_DIR)) {
        while(false !== ($file = readdir($stream))) {
            if(strpos($file, '.lang.php')) {  // why not !== false? because we don't accept '.lang.php' as a filename ;)
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

function fileGetContents($filename) {
    $stream = @fopen($filename, 'r');
    if (!$stream) {
        return false;
    } else {
        $c = fread($stream, filesize($filename));
        fclose($stream);
        return $c;
    }
}

function arrayduped($value) {
    return ($value > 1);
}

if(!isset($_GET['master']) || empty($_GET['master'])) {
    $master = 'English.lang.php';
} else {
    if(false === strpos($_GET['master'], '.lang.php')) {
        $master = $_GET['master'].'.lang.php';
    } else {
        $master = $_GET['master'];
    }
}

if(!isset($_GET['slave']) || empty($_GET['slave'])) {
    exit('No Slave selected');
} else {
    if(false === strpos($_GET['slave'], '.lang.php')) {
        $slave = $_GET['slave'].'.lang.php';
    } else {
        $slave = $_GET['slave'];
    }
}
$langFiles = getAllLanguageFilenames();

if(!in_array($master, $langFiles)) {
    exit('Selected Master copy could not be located.');
}

if(!in_array($slave, $langFiles)) {
    exit('Selected Slave could not be located.');
}

if($master === $slave or ($master === 'Base.lang.php' && $slave === 'English.lang.php') or ($slave === 'Base.lang.php' && $master === 'English.lang.php')) {
    exit('Why are you comparing the master file to itself?');
}

//Find file diffs
$masterl = getLanguageFileAsArray($master);
$slavel  = getLanguageFileAsArray($slave);
$masterf= array_keys($masterl);
$slavef = array_keys($slavel);

$remove = array_diff($slavef, $masterf);
$add    = array_diff($masterf, $slavef);

//Now find slave dupes
$slavel = fileGetContents($slave);
$currPos = 0;
$slavef = array();
while (($currPos = strpos($slavel, '$lang[\'', $currPos)) !== FALSE) {
    $currPos += 7;
    $tempPos = strpos($slavel, "'", $currPos);
    if ($tempPos !== FALSE) {
        $slavef[] = substr($slavel, $currPos, $tempPos - $currPos);
    }
}
$slavef = array_count_values($slavef);
$remove2 = array_filter($slavef, "arrayduped");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Language File Comparer :: Comparing <?php echo $slave; ?> (Slave) vs. <?php echo $master; ?> (Master)</title>
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
if(count($add) > 0) {
    foreach($add as $key=>$val) {
        ?>
        <tr>
        <td class="sideSeperator">&nbsp;</td>
        <td class="keyCol"><?php echo $val; ?></td>
        <td><?php /*echo htmlspecialchars($masterl[$val]);*/ ?></td>
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
if(count($remove) > 0) {
    foreach($remove as $key=>$val) {
        ?>
        <tr>
        <td class="sideSeperator">&nbsp;</td>
        <td colspan="2" class="keyCol"><?php echo $val; ?></td>
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
<tr>
<td colspan="2">&nbsp;</td>
</tr>
<tr><td class="category" colspan="3">Language Keys duplicated within Slave (<?php echo $slave;?>):</td></tr>
<?php
if(count($remove2) > 0) {
    foreach($remove2 as $key=>$val) {
        ?>
        <tr>
        <td class="sideSeperator">&nbsp;</td>
        <td colspan="2" class="keyCol"><?php echo $key; ?></td>
        </tr>
        <?php
    }
} else {
    ?>
    <tr>
    <td class="sideSeperator">&nbsp;</td>
    <td colspan="2" class="keyCol">No keys duplicated</td>
    </tr>
    <?php
}
?>
</table>
<div id="copyrightFooter">
&copy; 2008 The XMB Group
</div>
</body>
</html>
