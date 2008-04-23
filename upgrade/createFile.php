<?php
/**
 * eXtreme Message Board
 * XMB 1.9.8 Engage Final SP3
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

define('ROOT', '../');
require ROOT.'header.php';

if (!X_SADMIN) {
    die('This file is only for developers, who also happen to be Super Administrators.');
}

require 'upgrade.lib.php';

$upgrade = new Upgrade(&$db, 'XMB_1_9_8.xmb', $tablepre);
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
