<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-1
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2025, The XMB Group
 * https://www.xmbforum2.com/
 *
 * XMB is free software: you can redistribute it and/or modify it under the terms
 * of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * XMB is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XMB.
 * If not, see https://www.gnu.org/licenses/
 */

declare(strict_types=1);

namespace XMB;

header('Expires: 0');
header('X-Frame-Options: sameorigin');

$logfile = './upgrade.log';
$logfileName = 'upgrade.log';

$log = file_get_contents($logfile);
if ($log === false) {
    header('HTTP/1.0 403 Forbidden');
    exit('Not allowed to run this file directly.');
}
$check = substr($log, -14);
$done = '<!-- done. -->' == $check;
$error = '<!-- error -->' == $check;

?>
<html>
<head>
<?php if (! $done && ! $error) { ?>
<meta http-equiv="refresh" content="2" />
<?php } ?>
</head>
<body>
<?php
// Display the log file in reverse order, so latest message is first.
$lines = explode("\r\n", $log);
$counter = count($lines);
while (count($lines) > 0) {
	echo $counter--, ". ", array_pop($lines), "<br>\n";
}
?>
</body>
</html>
<?php

if ($done) {
	@unlink(dirname(__FILE__) . '/' . $logfileName);
}
