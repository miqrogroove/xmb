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

define('ROOT', '../');
require ROOT.'header.php';

if (!X_SADMIN) {
    die('This file is only for developers, who also happen to be Super Administrators.');
}

require 'upgrade.lib.php';

$upgrade = new Upgrade($db, 'XMB_1_9_9.xmb', $tablepre);
$tables = $upgrade->getTablesByTablepre($tablepre);
$upgrade->loadTables($tables);
$new = $upgrade->createUpgradeFile();

$filename = str_replace(array(' ', '.'), '_', $versionshort) . '.xmb';
if (($handle = @fopen($filename, 'w')) && @fwrite($handle, $new) && $_GET['action'] != 'download') { // If we can create the new upgrade file and or write to it and a download has not been requested close the handle and output success message...
    fclose($handle);
    echo $filename . ' has been sucessfully created in ' . substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')+1) . '. Click <a href="?action=download">here</a> to download it.';
} else { // ...otherwise force the download
    header('Content-Type: application/force-download');
    header('Content-Length: '. strlen($new));
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: public; max-age=0');
    echo $new;
}
?>
