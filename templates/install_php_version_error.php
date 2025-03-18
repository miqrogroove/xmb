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
     <p>Version mismatch.  XMB requires PHP version <?= $minimum ?> or higher to work properly.  Version <?= phpversion() ?> is running.</p>
    </div>
    <div class="bottom"><span></span></div>
   </div>
  </div>
 </body>
</html>
