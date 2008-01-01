<?php
/* $Id: createFile.php,v 1.1 2005/10/14 23:53:56 Tularis Exp $ */

require './header.php';

if ( $self['status'] != 'Super Administrator') {
	echo "This file is only for developers, who also happen to be Super Administrators.";
	exit(1);
}

require './upgrade.lib.php';
    
$u = new Upgrade(&$db, 'XMB_1_9_2.xmb', $tablepre);
$tbl = $u->getTablesByTablepre($tablepre);
$u->loadTables($tbl);

$o = $u->createUpgradeFile();

header('Content-type: text/plain');
header('Content-length: '.strlen($o));
header('Content-Disposition: attachment; filename='.str_replace(array(' ', '.'), '_', $versionshort).'.xmb');
header('Pragma: no-cache');
header('Expires: 0');

echo $o;
exit;
?>