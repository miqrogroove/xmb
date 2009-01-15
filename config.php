<?php
/*

XMB 1.8 Partagium
© 2001 - 2002 Aventure Media & The XMB Developement Team
http://www.aventure-media.co.uk
http://www.xmbforum.com

For license information, please read the license file which came with this edition of XMB

*/

// Database connection settings
	$dbname		= '';		// Name of your database
	$dbuser 	= '';		// Username used to access it
	$dbpw		= '';		// Password used to access it
	$dbhost 	= 'localhost';	// Database host, usually 'localhost'
	$database 	= 'mysql';	// Database type, currently only mysql is supported.
	$pconnect 	= 0;		// Persistent connection, 1 = on, 0 = off, use if 'too many connections'-errors appear
	
// Table Settings
	$tablepre 	= 'xmb_';	// Table-pre

// Cookie-settings
	$cookiepath 	= '/forum';	// The path to your board, eg: IF IT IS: http://your-domain/forum/index.php, YOUR COOKIEPATH WILL BE: '/forum'
	$cookiedomain 	= '.domain.com';// The domain this forum is on, eg: IF IT IS: http://your-domain/forum/index.php, COOKIEDOMAIN WILL BE: 'your-domain'

// Plugin Settings
	$plugname[1] 	= '';		// Added plugin name, to create another plugin, copy and change [1] to [2] etc
	$plugurl[1] 	= '';		// This is the location, link, or URL to the plugin
	$plugadmin[1] 	= 'no';		// Is this plugin only for admins? Set to yes for admins, no for public

// Registration settings
	/*
	 * Registrations from the same IP to happen more than once per 24 hours,
	 * To allow
	 * turn the following option 'off'. Meaning instead of the default:
	 * $ipreg = 'on';
	 *
	 * change it to:
	 * $ipreg = 'off';
	 */

	$ipreg 			= 'on';
?>