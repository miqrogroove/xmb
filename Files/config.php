<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

// Database connection settings
	$dbname		= 'Database Name';		// Name of your database
	$dbuser 	= 'Username';		// Username used to access it
	$dbpw		= 'password';		// Password used to access it
	$dbhost 	= 'localhost';	// Database host, usually 'localhost'
	$database 	= 'mysql';	// Database type, currently only mysql is upported.
	$pconnect 	= 0;		// Persistent connection, 1 = on, 0 = off, use if 'too many connections'-errors appear
	
// Table Settings
	$tablepre 	= 'xmb_';	// Table-pre

// Path-settings
	// In full_path, put the full URL you see when you go to your boards, WITHOUT the filename though!!
	// And please, don't forget the / at the end...
	$full_url	= 'http://www.xmbforum.com/community/board/';

// Plugin Settings
	$plugname[1] 	= '';		// Added plugin name, to create another plugin, copy and change [1] to [2] etc
	$plugurl[1] 	= '';		// This is the location, link, or URL to the plugin
	$plugadmin[1] 	= 'no';		// Is this plugin only for admins? Set to yes for admins, no for public

// Registration settings
	/***************
	 * Registrations from the same IP to happen more than once per 24 hours,
	 * To allow
	 * turn the following option 'off'. Meaning instead of the default:
	 * $ipreg = 'on';
	 *
	 * change it to:
	 * $ipreg = 'off';
	 *
	 ****************
	 * The ipcheck, checks if your IP is a valid IPv4 or IPv9 type, if none of these, it will kill.
	 * this might shut a few users out, so you can turn it off by changing the $ipcheck variable to 'off'
	 ****************/

	$ipreg 			= 'on';
	$ipcheck		= 'on';
?>