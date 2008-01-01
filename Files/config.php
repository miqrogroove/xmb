<?php
/* $Id: config.php,v 1.3.2.2 2005/09/04 20:30:51 Tularis Exp $ */
/*
    XMB 1.9.2
    © 2001 - 2005 Aventure Media & The XMB Development Team
    http://www.aventure-media.co.uk
    http://www.xmbforum.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Database connection settings
    $dbname         = 'DB_NAME';       // Name of your database
    $dbuser         = 'DB_USER';       // Username used to access it
    $dbpw           = 'DB_PW';      // Password used to access it
    $dbhost         = 'DB_HOST';  // Database host, usually 'localhost'
    $database       = 'DB_TYPE';      // Database type, currently only mysql is supported.
    $pconnect       = 0;            // Persistent connection, 1 = on, 0 = off, use if 'too many connections'-errors appear

// Table Settings
    $tablepre       = 'TABLEPRE';   // Table-pre

// Path-settings
    // In full_path, put the full URL you see when you go to your boards, WITHOUT the filename though!!
    // And please, don't forget the / at the end...
    $full_url       = 'FULLURL';

// Other settings
    // the Maximum attachment-filesize, in bytes!
    // 1MB = 1024*1024 bytes (default is set to 1MB)
    // A good size for 640x480 images is 50 kb, which is 50 * 1024 = 51200.
    // $max_attach_size = 51200;

    $max_attach_size = MAXATTACHSIZE;

    // There are situations where you don't want to see the <!-- template start: index -->...<!-- template end: index -->
    // tags around each template. In those cases, change the following to false, or true to turn it back on.
    // Default value: false;
    $comment_output = COMMENTOUTPUT;
    
    // Alternative mailer
    // some hosts prevent the direct use of sendmail, which php uses to send out emails by default.
    // To get around this, we have included code which will contact a separate SMTP server of your
    // choice, and will send the mail trough that. The following mailer-options are available:
    // 'default'		=> php's internal mail() function. No additional values need to be set:
    //					   (does not require a username/password/host/port)
    // 'socket_SMTP'	=> a connection to the SMTP server trough sockets. Requires the username,
    //					   password, host and port values to be entered correctly to work.
    $mailer['type']		= 'MAILER_TYPE';
    
    // mailer-options (for socket_SMTP only, currently)
    $mailer['username']	= 'MAILER_USER';
    $mailer['password']	= 'MAILER_PASS';
    $mailer['host']		= 'MAILER_HOST';
    $mailer['port']		= 'MAILER_PORT';
    
    

// Plugin Settings
    $i = 1;
    // Plugins are the links in the navigation part of the Header. Plugins built-in by default include Search, FAQ, Member List, Today's Posts, Stats and Board Rules.
    // To add extra plugins (links of your own), just edit the code between Start Plugin Code and End Plugin Code. If you with to add more than one, simply copy that block, paste it and add the second one.
    
    // Start Plugin code
    $plugname[$i]    = '';       // This is the name of your plugin. eg. Avatar Gallery, TeddyBear, etc.
    $plugurl[$i]     = '';       // This is the location, link, or URL to the plugin
    $plugadmin[$i]   = false;    // Is this plugin only for admins? Set to true if the plugin can only be seen/used by (super-)admins, false when it's can be used by anyone
    $plugimg[$i]     = '';       // This is the path (full URL) to the image to show in front of the text.
    $i++;
    // End plugin code.
    
    // Start Plugin code for plugin #2
    $plugname[$i]    = '';       // This is the name of your plugin. eg. Avatar Gallery, TeddyBear, etc.
    $plugurl[$i]     = '';       // This is the location, link, or URL to the plugin
    $plugadmin[$i]   = false;    // Is this plugin only for admins? Set to true if the plugin can only be seen/used by (super-)admins, false when it's can be used by anyone
    $plugimg[$i]     = '';       // This is the path (full URL) to the image to show in front of the text.
    $i++;
    // End plugin code for plugin #2
    
    // To make multiple plugins, copy and paste this plugin-code, so you have multiple entries.
    

// Registration settings
    /***************
     * To allow:
     * registrations from the same IP to happen more than once per 24 hours,
     * turn the following option 'off'. Meaning instead of the default:
     * $ipreg = 'on';
     *
     * change it to:
     * $ipreg = 'off';
     *
     ****************
     * The ipcheck, checks if your IP is a valid IPv4 or IPv6 type, if none of these, it will kill.
     * this might shut a few users out, so you can turn it off by changing the $ipcheck variable to 'off'
     ****************
     * The allow_spec_q variable specifies if Special queries (eg. USE database and SHOW DATABASES) are allowed.
     * By default, they are not, meaning $allow_spec_q = false;
     * To allow them, change $allow_spec_q to true ($allow_spec_q = true;)
     ****************
     * The show_full_info variable lets you decide wether to show the Build and Alpha/Beta/SP markings in the HTML or not.
     * Change the value to true to show them, or false to turn them off.
     * Default = true;
     ****************/

    $ipreg          = 'IPREG';
    $ipcheck        = 'IPCHECK';
    $allow_spec_q   = SPECQ;
    $show_full_info = SHOWFULLINFO;
    $max_reg_day    = 25;               // raise this figure if you run a very busy board.
?>