<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

// This makes XMB compatible with the latest PHP changes (4.2.*) (mainly 4.2.1 and 4.2.2)
	$patch[] = "PHP 4.2.* fix, due to 'superglobal'-registration being turned off by standard";
	$patch[] = "errors due to magic_quotes_gpc being off, fixed";
	
	$global = @array('0' => $_GET, '1' => $_POST, '2' => $_ENV, '3'=> $_COOKIE, '4'=> $_SESSION, '5'=> $_SERVER, '6' => $_FILES);
	$global_old = @array('0' => $HTTP_GET_VARS, '1' => $HTTP_POST_VARS, '2' => $HTTP_ENV_VARS, '3' => $HTTP_COOKIE_VARS, '4' => $HTTP_SESSION_VARS, '5' => $HTTP_SERVER_VARS, '6'=> $HTTP_POST_FILES);
	
	if (phpversion() <= '4.1.0') {
		$global = &$global_old;
	}
	reset($global);
	
	if (get_magic_quotes_gpc() === 0) {
		foreach ($global as $num => $array) {
			if (is_array($array)) {
				foreach ($array as $key => $val) {
					if (is_array($val)) {
						foreach ($val as $vkey => $vval) {
							${$key}[$vkey] = addslashes($vval);
						}
					}
					else {
						$$key = addslashes($val);
					}
				}
				reset($array);
			}
			
		}
		foreach ($global[6] as $key=>$var) {
			$name = $key;
			if (is_array($var)) {
				foreach ($var as $newkey=>$val) {
					${$name.'_'.$newkey} = addslashes($val);
				}
			}
			else {
				$$key = addslashes($var);
			}
		}
	}
	else {
		foreach ($global as $num => $array) {
			if (is_array($array)) {
				foreach ($array as $key => $val) {
					$$key = $val;
				}
				reset($array);
			}
		}
		foreach ($global[6] as $key=>$var) {
			$name = $key;
			if (is_array($var)) {
				foreach ($var as $newkey=>$val) {
					${$name.'_'.$newkey} = $val;
				}
			}
			else {
				$$key = $var;
			}
		}
	}
	
// Shows list of patches
if($list == "patches"){
	echo "<b><u>List of current patches applied:<br /></u></b>";
	
	for($i=0; $i < count($patch); $i++){
			echo "<hr /><b>$i</b> - $patch[$i]<br />";
	}
}
?>