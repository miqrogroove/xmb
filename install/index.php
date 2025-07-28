<?php

/**
 * eXtreme Message Board
 * XMB 1.10.00-beta-2
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

// This file has been tested against PHP v4.4.6 for backward-compatible error reporting.

// Check location
if (! is_readable('./web-header.php')) {
    exit('Could not find the installer files! Please make sure the entire install folder contents are available.');
}

// If PHP is running, proceed to the version test.
include './web-header.php';
exit();

// If PHP isn't running, display an error message.
?>
<!DOCTYPE html>
<html lang="en">
 <head>
  <title>XMB Installer</title>
  <link rel="stylesheet" href="../images/install/install.css" type="text/css" media="screen">
 </head>
 <body>
  <div id="main">
   <div id="header">
    <img src="../images/install/logo.png" alt="XMB" title="XMB">
   </div>
   <div id="sidebar">
    <div class="top"><span></span></div>
    <div class="center-content">
     <ul>
      <li class="current">Welcome</li>
      <li>Version Check</li>
      <li>License Agreement</li>
      <li>Configuration</li>
      <li>Create Super Administrator Account</li>
      <li>Install</li>
     </ul>
    </div>
    <div class="bottom"><span></span></div>
   </div>
   <div id="content">
    <div class="top"><span></span></div>
    <div class="center-content">
     <h1>Welcome to the XMB Installer</h1>
     <p>PHP is Missing or Not Enabled. The XMB installer requires a working PHP environment before proceeding.</p>
    </div>
    <div class="bottom"><span></span></div>
   </div>
  </div>
 </body>
</html>
