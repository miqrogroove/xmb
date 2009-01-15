<?
/*

XMB 1.6 v2c Magic Lantern
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/
// Don't Touch!
if(!isset($patched)){
$patched = 0;
}



// This makes XMB compatible with PHP-version 4.2.0
	$patched++;
	$patch[1] = "PHP 4.2.0 fix, due to global variables array being turned off automatically";

	if(is_array($_SERVER)){
		extract($_SERVER, EXTR_PREFIX_SAME, "server");
	}
	if(is_array($_GET)){
		extract($_GET, EXTR_PREFIX_SAME, "get");
	}
	if(is_array($_POST)){
		extract($_POST, EXTR_PREFIX_SAME, "post");
	}
	if(is_array($_COOKIE)){
		extract($_COOKIE, EXTR_PREFIX_SAME, "cookie");
	}
	if(is_array($_FILES)){
		extract($_FILES, EXTR_PREFIX_SAME, "file");
	}
	if(is_array($_ENV)){
		extract($_ENV, EXTR_PREFIX_SAME, "env");
	}
	if(is_array($_REQUEST)){
		extract($_REQUEST, EXTR_PREFIX_SAME, "request");
	}
	if(is_array($_SESSION)){
		extract($_SESSION, EXTR_PREFIX_SAME, "session");
	}



// Shows list of patches
if($list == "patches"){
	echo "<b><u>List of current patches applied:<br></u></b>";
	
	for($i=1; $i <= $patched; $i++){
			echo "<hr><b>$i</b> - $patch[$i]<br>";
	}
}
?>