<?php
/* $Id: createFile.php,v 1.2 2004/07/30 12:47:55 ajv Exp $ */

require './header.php';

if ( $self['status'] != 'Super Administrator') {
	echo "This file is only for developers, who also happen to be Super Administrators.";
	exit(1);
}

require './upgrade.lib.php';
    
$u = new Upgrade(&$db, 'XMB_1_9-final.xmb', $tablepre);
$tbl = $u->getTablesByTablepre($tablepre);
$u->loadTables($tbl);

$o = $u->createUpgradeFile();

header('Content-type: text/plain');
header('Content-length: '.strlen($o));
header('Content-Disposition: attachment; filename=XMB19Final.xmb');
header('Pragma: no-cache');
header('Expires: 0');

echo $o;
exit;
?>