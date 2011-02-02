<?php
/*

XMB 1.8 Partagium
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/

// Checks patches
if(!isset($patched)){
$patched = 0;
}


// This makes XMB compatible with the latest PHP changes (4.2.*) (mainly 4.2.1 and 4.2.2)
	$patched++;
	$patch[1] = "PHP 4.2.* fix, due to 'superglobal'-registration being turned off by standard";
if (isset($_SESSION)) {
	if(is_array($_SESSION)){
		extract($_SESSION, EXTR_PREFIX_SAME, "session");
	}
}
    if(is_array($_SERVER)){
		extract($_SERVER, EXTR_PREFIX_SAME, "server");
	}
	if(is_array($_COOKIE)){
		extract($_COOKIE, EXTR_PREFIX_SAME, "cookie");
	}
	if(is_array($_POST)){
		extract($_POST, EXTR_PREFIX_SAME, "post");
	}
	if(is_array($_GET)){
		extract($_GET, EXTR_PREFIX_SAME, "get");
	}
	if(is_array($_FILES)){
		extract($_FILES, EXTR_PREFIX_SAME, "file");
	}
	if(is_array($_ENV)){
		extract($_ENV, EXTR_PREFIX_SAME, "env");
	}

// Shows list of patches
if (isset($list)) {
if($list == "patches"){
	echo "<b><u>List of current patches applied:<br></u></b>";

	for($i=1; $i <= $patched; $i++){
			echo "<hr><b>$i</b> - $patch[$i]<br>";
	}
}
}
?>
