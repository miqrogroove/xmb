<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

require "./header.php";

// Load templates which will be used in this script
loadtemplates('u2u_header_admin,u2u_footer_admin,u2u_row_admin,u2u_admin,u2u_view_refwdlinks_admin,u2u_view_admin,u2u_message');

// Set strings for templates to be used
eval("\$notadmin = \"".template("error_nologinsession")."\";");
eval("\$u2uheader = \"".template("u2u_header_admin")."\";");
eval("\$header = \"".template("header")."\";");

eval("\$u2ufooter = \"".template("u2u_footer_admin")."\";");
eval("\$footer = \"".template("footer")."\";");

// Check to see whether the user trying to access the page has admin status.
// If there is no administration status the user will be displayed with a login box.

if($status != "Super Administrator") {
	echo $header;
	echo $notadmin;
	echo $footer;
	exit();
}

// Check to see if the user is logged in, if not display login message
if($xmbuser == "") {
	u2umsg("$lang_u2unotloggedin");
}

// Cache Smilies and Censored Words
smcwcache();

if(!$action || $action == "") {
	if(!$folder) {
		$folder = "inbox";
		$query = $db->query("SELECT * FROM $table_u2u WHERE msgto='$uid' AND folder='$folder' ORDER BY dateline DESC");
	} else {
		$lang_textu2uinbox = $lang_textu2uoutbox;
		$lang_textfrom = $lang_textto;
		$query = $db->query("SELECT * FROM $table_u2u WHERE msgfrom='$uid' AND folder='$folder' ORDER BY dateline DESC");
	}

	while($message = $db->fetch_array($query)) {
		$postdate = gmdate($dateformat, $message[dateline] + ($timeoffset * 3600) + ($addtime * 3600));
		$posttime = gmdate($timecode, $message[dateline] + ($timeoffset * 3600) + ($addtime * 3600));

		$senton = "$postdate $lang_textat $posttime";

		if($message[subject] == "") {
			$message[subject] = "&lt;$lang_textnosub&raquo;";
		}
		
		if ($message[readstatus] == "yes") {
			$read = "$lang_textread";
		} else {
			$read = "$lang_textunread";
		}
	
		if ($folder=="outbox") {
			$message[msgfrom] = $message[msgto];
		}
	
		eval("\$messages .= \"".template("u2u_row_admin")."\";");
	}
	eval("\$u2u = \"".template("u2u_admin")."\";");
	$u2u = stripslashes($u2u);
	echo $u2u;
}

// Administrators do not have permission to send U2Us.
// We have not allowed this as it could cause privacy issues as well as abuse on some boards.
if($action == "send") {
	u2umsg($lang_u2uadmin_noperm);
}

if($action == "delete") {
	if($folder=="outbox") {
		$msg_field = "msgfrom";
	} else {
		$msg_field = "msgto";
	}
	
	$query = $db->query("SELECT * FROM $table_u2u WHERE ".$msg_field."='$uid' AND folder='$folder' ORDER BY dateline DESC");
	while($u2u = $db->fetch_array($query)) {
		$delete = "delete$u2u[u2uid]";
		$delete = "${$delete}";
		$db->query("DELETE FROM $table_u2u WHERE ".$msg_field."='$uid' AND u2uid='$delete'");
	}

	if($folder == 'outbox') {
		u2umsg($lang_imdeletedmsg, "u2uadmin.php?folder=outbox&uid=$uid");
	} else {
		u2umsg($lang_imdeletedmsg, "u2uadmin.php?uid=$uid");
	}
}

// Administrators do not have permission to use this feature.
// We have not allowed this as it could cause privacy issues as well as abuse on some boards.
if($action == "ignore") {
	u2umsg($lang_u2uadmin_noperm);
}

// Administrators do not have permission to use this feature.
// We have not allowed this as it could cause privacy issues as well as abuse on some boards.
if($action == "ignoresubmit") {
	u2umsg($lang_u2uadmin_noperm);
}

if($action == "view") {
	$query = $db->query("SELECT * FROM $table_u2u WHERE u2uid='$u2uid' LIMIT 1");
	$u2u = $db->fetch_array($query);
	$u2u[message] = stripslashes($u2u[message]);
	$u2u[subject] = stripslashes($u2u[subject]);
	$u2udate = gmdate("$dateformat",$u2u[dateline] + ($timeoffset * 3600) + ($addtime * 3600));
	$u2utime = gmdate("$timecode",$u2u[dateline] + ($timeoffset * 3600) + ($addtime * 3600));
	$dateline = "$u2udate $lang_textat $u2utime";
	$u2u[subject] = "$lang_textsubject $u2u[subject]";

// u2u Security Patch
	$u2u['message'] = postify($u2u['message'], "no", "", "yes", "no");

//end


	eval("\$view = \"".template("u2u_view_admin")."\";");
	
	echo stripslashes($view);
}

function u2umsg($message, $redirect='') {
	global $bordercolor, $tablewidth, $borderwidth, $tablespace, $altbg1, $css, $bbname, $lang_textpowered, $u2uheader, $u2ufooter;
	if($redirect != '') {
		$redirectjs = "<script> function redirect() { window.location.replace(\"$redirect\"); } setTimeout(\"redirect();\", 1250); </script>";
	}
	
	eval("\$msg = \"".template("u2u_message")."\";");
	$u2umsg = stripslashes($u2umsg);
	echo $msg;
	exit();
}
?>