<?php
/* $Id: index.php,v 1.8 2006/02/03 20:40:03 Tularis Exp $ */
/*
    XMB 1.10
    © 2001 - 2006 Aventure Media & The XMB Development Team
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
define('ROOT', '../');

define('X_VERSION', '1.10.0');
define('X_VERSION_EXT', '1.10.0 [secret] pre-Alpha');

define('X_INST_ERR', 0);
define('X_INST_WARN', 1);
define('X_INST_OK', 2);
define('X_INST_SKIP', 3);

define('COMMENTOUTPUT', false);
define('MAXATTACHSIZE', 1000000);
define('IPREG', 'on');
define('IPCHECK', 'off');
define('SPECQ', false);
define('SHOWFULLINFO', false);

@set_magic_quotes_runtime(0);
@ini_set('magic_quotes_gpc', '1');

function error($head, $msg, $die=true) {
    echo "\n";
    echo '<blockquote>';
    echo '<font class="progressErr">'.$head.'</font><br />';
    echo '<font class="progressWarn">'.$msg.'</font>';
    echo '</blockquote>';
    echo "\n";
    if ( $die) {
        exit();
    }
}

require_once ROOT.'install/install.inc.php';

tmpManage(X_T_OPEN);

if(isset($_GET['action']) && $_GET['action'] == 'verifyUrl') {
	echo md5($_SERVER['SCRIPT_FILENAME']);
	exit;
}

$step		= isset($_REQUEST['step'])		? $_REQUEST['step']			: 0;
$substep	= isset($_REQUEST['substep'])	? $_REQUEST['substep']		: 0;

switch($step) {
    case 1: // welcome
    	printHeader();
        ?>
		<form action="index.php?step=2" method="post">
			<div id="contentDiv">
				<img src="./splash.gif" title="Welcome to the installer for XMB <?php echo X_VERSION_EXT;?>" alt="Welcome to the installer for XMB <?php echo X_VERSION_EXT;?>" />
				<br />
				&nbsp;
				<br />
				<span id="title">
					XMB <?php echo X_VERSION;?> Install wizard
				</span>
				<span class="mediumText">
					Thank you for choosing XMB as your message board.<br />
					The next steps will guide you through the installation of your board.<br />
				</span>
				<br />
				<input type="submit" name="submit" value="Start installation &gt;" />
			</div>
		</form>        <?php
        printFooter();
        break;

    case 2: // versioncheck
        printHeader();
        ?>
        <p class="subTitle">
            Version Check Information
        </p>

        <br />
        This page displays your version of XMB, and the latest version available from XMB. If there is a later version, XMB strongly recommends you do not install this version, but choose the latest stable release.
        <br /><br />
        <table style="width: 75%;">
        <tr>
        <td style="width: 30%;">
        Your Version:
        </td>
        <td style="width:70%;">
            <b>XMB <?php echo X_VERSION_EXT;?></b>
        </td>
        </tr>
        <td style="width: 30%;">
        Current Version:</td>
        <td style="width:70%;">
        <img src="http://www.xmbforum.com/phpbin/xmbvc/vc.php?bg=e9edef&fg=000000" border="0">
        </td>
        </tr>
        </table>
        <br />
        <form action="./index.php?step=3" method="post">
        <INPUT TYPE="submit" VALUE="Install XMB <?php echo X_VERSION;?> &gt;" />
        </form>
        <br />&nbsp;
        </p>
        <?php
        printFooter();
        break;

    case 3: // agreement
        printHeader();
        ?>
		<form action="index.php?step=4" method="post">
			<div id="contentDiv">
				<img src="./splash.gif" title="Welcome to the installer for XMB <?php echo X_VERSION_EXT;?>" alt="Welcome to the installer for XMB <?php echo X_VERSION_EXT;?>" />
				<br />
				&nbsp;
				<br />
				<span id="title">
					XMB <?php echo X_VERSION;?> License Agreement
				</span>
				<span class="mediumText">
					You must accept the license agreement below to continue installation.
				</span>
				<hr class="separator" />
	        	<textarea rows="30" cols="10" name="agreement" style="white-space: pre-wrap; font-family: Verdana; font-size: 8pt; width: 100%;" readonly="readonly">
					XMB <?php echo X_VERSION;?> License (Updated August 2004)
					<?php echo htmlspecialchars(file_get_contents(ROOT.'install/license.txt'));	?>
	        	</textarea>
				<hr class="separator" />
				<input type="submit" name="submit" value="I agree to the Terms and Conditions of this Agreement" />
			</div>
		</form>
        <?php
        printFooter();
        break;

    case 4: // config.php set-up
        $vSubStep = isset($_REQUEST['substep']) ? trim($_REQUEST['substep']) : '';
        switch ($vSubStep) {
        case 'create':
            // Open config.php
            $configuration = file_get_contents('../config.php');

            // Now, replace the main text values with those given by user
            $find      = array('DB_NAME', 'DB_USER', 'DB_PW', 'DB_HOST', 'DB_TYPE', 'TABLEPRE', 'FULLURL', 'MAXATTACHSIZE', 'MAILER_TYPE', 'MAILER_USER', 'MAILER_PASS', 'MAILER_HOST', 'MAILER_PORT');
            $replace   = array($_REQUEST['db_name'], $_REQUEST['db_user'], $_REQUEST['db_pw'], $_REQUEST['db_host'], $_REQUEST['db_type'], $_REQUEST['table_pre'], $_REQUEST['fullurl'], $_REQUEST['maxattachsize'], $_REQUEST['MAILER_TYPE'], $_REQUEST['MAILER_USER'], $_REQUEST['MAILER_PASS'], $_REQUEST['MAILER_HOST'], $_REQUEST['MAILER_PORT']);
            $configuration = str_replace($find, $replace, $configuration);

            $new = array();
            foreach($find as $k=>$f) {
            	$new[$f] = $_REQUEST[$k];
            }

            tmpStore('config', $new);

            // Change Comment Output Option
            if (isset($_REQUEST['c_output'])) {
                $configuration = str_replace('COMMENTOUTPUT', 'true', $configuration);
            }else{
                $configuration = str_replace('COMMENTOUTPUT', 'false', $configuration);
            }

            // IP Reg
            if (isset($_REQUEST['ip_reg'])) {
                $configuration = str_replace('IPREG', 'on', $configuration);
            }else{
                $configuration = str_replace('IPREG', 'off', $configuration);
            }

            // IP Check
            if (isset($_REQUEST['ip_check'])) {
                $configuration = str_replace('IPCHECK', 'on', $configuration);
            }else{
                $configuration = str_replace('IPCHECK', 'off', $configuration);
            }

            // Allow Special Queries
            if (isset($_REQUEST['allowspecialq'])) {
                $configuration = str_replace('SPECQ', 'true', $configuration);
            }else{
                $configuration = str_replace('SPECQ', 'false', $configuration);
            }

            // Show Full Footer Info
            if (isset($_REQUEST['showfullinfo'])) {
                $configuration = str_replace('SHOWFULLINFO', 'true', $configuration);
            }else{
                $configuration = str_replace('SHOWFULLINFO', 'false', $configuration);
            }

            switch($_REQUEST['method']) {
                case 1: // Show configuration on screen
                	printHeader();
                    ?>
					<form action="index.php?step=5" method="post">
						<div id="contentDiv">
							<img src="./splash.gif" title="Welcome to the installer for XMB <?php echo X_VERSION_EXT;?>" alt="Welcome to the installer for XMB <?php echo X_VERSION_EXT;?>" />
							<br />
							&nbsp;
							<br />
							<span id="title">
								XMB <?php echo X_VERSION;?> Configuration (step 2)
							</span>
							<span class="mediumText">
								Copy the following into a new file, call it &quot;config.php&quot;. Then upload it to the forum directory on your server.
							</span>
							<hr class="separator" />
							<div id="configDiv">
								<?php highlight_string($configuration); ?>
							</div>
							<hr class="separator" />
							<input type="submit" name="submit" value="Continue &gt;" />
						</div>
					</form>
                    <?php
                    printFooter();
                    break;

                case 2:	// Open a new file
					printHeader();
					$handle = @fopen(ROOT.'config.php', "w");
                    ?>
					<form action="index.php?step=<?php echo (($handle) ? 5 : 4);?>" method="post">
						<div id="contentDiv">
						<?php
						if(!$handle) {
							?>
							<span class="subTitle">
								Invalid Permissions
							</span>
							<span class="mediumText">
								XMB cannot create your configuration file on the server as it does not have enough permissions to do so.  If you would like to try again, CHMOD the "config.php" file to <i>666</i> or select a different method
							</span>
							<input type="submit" name="submit" value="Retry Configuring" />
							<?php
						} else {
                    		// Write to file, then close file
                    		fwrite($handle, $configuration, strlen($configuration));
                    		fclose($handle);

                    		// Continue to next step
                    		?>
                    		<span class="mediumText">
                    			Your XMB configuration has been created correctly on the server.
                    		</span>
							<input type="submit" name="submit" value="Next Step &gt;" /><?php
						}
					  ?></div>
					</form>
					<?php
					printFooter();
                    break;

                case 3:
                	printHeader('<meta http-equiv="refresh" content="5;url=index.php?step=4&amp;substep=create&amp;method=4" />');
                	?>
                	<form action="index.php?step=5" method="post">
                		<div id="contentDiv">
							<span id="title">
								Download Config.php
							</span>
							<span class="mediumText">
								Download will commence in a few seconds. If it does not, <a href="index.php?step=4&amp;substep=create&amp;method=4">press here</a>.
								<br />
							</span>
							<input type="submit" value="Next Step &gt;"/>
						</div>
					</form>
                	<?php
                	printFooter();
                	break;
                case 4:
                    // Get size
                    $size = strlen($configuration);
                    // Put out headers for mime-type, filesize, forced-download, description and no-cache.
                    header("Content-type: application/octet-stream");
                    header("Content-length: $size");
                    header("Content-Disposition: attachment; filename=config.php");
                    header("Content-Description: XMB Configuration");
                    header("Pragma: no-cache");
                    header("Expires: 0");


                    // Start file download
                    echo $configuration;
                    exit();
                    break;

                case 6:
                	header('Location: index.php?step=5');
                	exit;
                	break;

                default:
                	printHeader();
                	?>
					<form action="index.php?step=4" method="post">
						<div id="contentDiv">
							<span class="subTitle">
								Configuration Method Missing
							</span>
							<span class="mediumText">
								You did not specify a method of configuration.  Please go back and do so now.
							</span>
							<input type="submit" name="submit" value="Retry Configuring" />
						</div>
					</form>
					<?php
					printFooter();
                    break;
                }
            break;

        default:
        // Get the DB types...
            $stream = opendir(ROOT.'db');
            while(false !== ($file = readdir($stream))) {
                if (strpos($file, '.php') && false === strpos($file, '.interface.php')) {
                    $dbs[] = $configuration = str_replace('.php', '', $file);
                }
            }
            $phpv = explode('.', phpversion());
            $types = array();
            foreach ($dbs as $db) {
                if ( $db == 'mysql4' && $phpv[0] != 5) {
                    continue;
                }
                if ($db == 'mysql') {
                    $types[] = '<option selected="selected" value="'.$db.'">'.$db.'</option>';
                } else {
                    $types[] = '<option value="'.$db.'">'.$db.'</option>';
                }
            }
            $types = '<select name="db_type">'.implode("\n", $types).'</select>';
            $full_url_default = 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')-7);

            $headerJs = '<script type="text/javascript" src="./config.js"></script>';

            printHeader($headerJs);
            ?>
			<form action="index.php?step=4&amp;substep=create" method="post">
				<div id="contentDiv">
					<img src="./splash.gif" title="Welcome to the installer for XMB <?php echo X_VERSION_EXT;?>" alt="Welcome to the installer for XMB <?php echo X_VERSION_EXT;?>" />
					<br />
					&nbsp;
					<br />
					<span id="title">
						XMB <?php echo X_VERSION;?> Configuration
					</span>
					<span class="mediumText">
						Please complete the form below and choose 'Next Step' to configure (or skip the configuration of) xmb.
						<br />
						Below you will find fields required for the correct configuration of XMB. If you wish to manually configure XMB,
						or simply do not wish to configure it right now, or using the installer, you may choose to skip this step.
					</span>

					<hr class="separator" />

					<span class="subTitle">
						Configuration Method
					</span>


					<table class="radioTable">
						<tr>
							<td class="radioButton">
								<input type="radio" name="method" value="1" onclick="hideConfig(false)"/>
							</td>
							<td class="radioDesc">
								This option will show the config.php information on screen, and you will need to copy it into your own configuration file
							</td>
						</tr>
						<tr>
							<td class="radioButton">
								<input type="radio" name="method" value="2" onclick="hideConfig(false)" checked="checked" />
							</td>
							<td class="radioDesc">
								This option will attempt to create config.php directly onto the server.&nbsp; For this to work, the current config.php must have a CHMOD Value of '<i>666</i>'.
							</td>
						</tr>
						<tr>
							<td class="radioButton">
								<input type="radio" name="method" value="3" onclick="hideConfig(false)" />
							</td>
							<td class="radioDesc">
								This option will let you download a complete config.php onto your computer based on the values below.
							</td>
						</tr>
						<tr>
							<td class="radioButton">
								<input type="radio" name="method" value="6" onclick="hideConfig(true)" />
							</td>
							<td class="radioDesc">
								Skip the creation of config.php; I will create my own.
							</td>
						</tr>
					</table>
					<span class="mediumText">
						&nbsp;
						<br />
						&nbsp;
					</span>

					<div id="configDiv" style="border: 1px solid #1C8BCB;">
						<span class="subTitle">
							Database Connection Settings
						</span>
						<table class="settingTable">
							<tr>
								<td class="settingDesc">
									<strong>Database Name</strong><br />
									&nbsp;&nbsp;Name of the database you're installing to.
								</td>
								<td class="settingInput">
									<input type="text" name="db_name" size="40" />
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>Database Username</strong><br />
									&nbsp;&nbsp;User used to access the database.
								</td>
								<td class="settingInput">
									<input type="text" name="db_user" size="40" />
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>Database Password</strong><br />
									&nbsp;&nbsp;Password used to access the database with the specified username.
								</td>
								<td class="settingInput">
									<input type="password" name="db_pw" size="40" />
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>Database Host</strong><br />
									&nbsp;&nbsp;Hostname your database-server can be reached by. Usually &quot;<i>localhost</i>&quot;.
								</td>
								<td class="settingInput">
									<input type="text" name="db_host" value="localhost" size="40" />
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>Database Type</strong><br />
									&nbsp;&nbsp;The extension XMB should use to access the database.
								</td>
								<td class="settingInput">
									<?php echo $types; ?>
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>Table Prefix</strong><br />
									&nbsp;&nbsp;This setting is for the table prefix, for every board you have installed, this should be different.
								</td>
								<td class="settingInput">
									<input type="text" name="table_pre" size="40" value="xmb_" />
								</td>
							</tr>
						</table>
						<hr class="separator" />
						<span class="subTitle">
							Forum Settings
						</span>
						<table class="settingTable">
							<tr>
								<td class="settingDesc">
									<strong>Full URL</strong><br />
									&nbsp;&nbsp;This is the full url (excluding the filename) to your boards; ( eg. http://www.example.com/forums/ )	Please remember the trailing / !
								</td>
								<td class="settingInput">
									<input type="text" name="fullurl" size="40" value="<?php echo $full_url_default;?>" />
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>Maximum Attachment Size</strong><br />
									&nbsp;&nbsp;This setting defines the maximum filesize allowed per attachment. This setting is also capped by PHP's max_upload_size directive; so if you are noticing avatars are too large whilst they should be allowed by this setting, then you'll need to update the PHP directive.<br />
									The size is given in bytes (default: 250KB).
								</td>
								<td class="settingInput">
									<input type="text" name="maxattachsize" size="40" value="256000" />
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>Comment Output</strong><br />
									&nbsp;&nbsp;Turning this On will add comments in the HTML source code, showing where templates start and stop. Turning this Off significantly reduces the size of output, and thus bandwidth.
								</td>
								<td class="settingInput">
									<select name="c_output">
										<option value="true">
											On
										</option>
										<option value="false" selected="selected">
											Off
										</option>
									</select>
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>IP Registration Quota </strong><br />
									&nbsp;&nbsp;Turning On this feature will allow only 1 registration per unique ip every 24 hours. This is mainly used to stop bots from flooding your board.
								</td>
								<td class="settingInput">
									<select name="ip_reg">
										<option value="on" selected="selected">
											On
										</option>
										<option value="off">
											Off
										</option>
									</select>
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>Check IP's</strong><br />
									&nbsp;&nbsp;Turning on this setting will force XMB to check if every user's IP is of an expected type (IPv4 or IPv6). If it's not, the user will not be allowed to view the board. This setting is usually used to help protect from bots.
								</td>
								<td class="settingInput">
									<select name="ip_check">
										<option value="on">
											On
										</option>
										<option value="off" selected="selected">
											Off
										</option>
									</select>
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>Allow &quot;Special&quot; Queries</strong><br />
									&nbsp;&nbsp;Turning Off this setting will disallow specific queries like &quot;<i>USE databaseName</i>&quot; and &quot;<i>SHOW DATABASES</i>&quot;.<br />
									Default: off.
								</td>
								<td class="settingInput">
									<select name="allowspecialq">
										<option value="on">
											On
										</option>
										<option value="off" selected="selected">
											Off
										</option>
									</select>
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>Show Full Version Info</strong><br />
									&nbsp;&nbsp;Turning On this feature will show the exact version of XMB you are using on its front page. Turning this off will make it a lot harder for hackers to find out what version you're using and hack your board (if any security-exploits are discovered).
								</td>
								<td class="settingInput">
									<select name="showfullinfo">
										<option value="on">
											On
										</option>
										<option value="off" selected="selected">
											Off
										</option>
									</select>
								</td>
							</tr>
						</table>
						<hr class="separator" />
						<span class="subTitle">
							Mail-handling
						</span>
						<table class="settingTable">
							<tr>
								<td class="settingDesc">
									<strong>Mail-Handler</strong><br />
									&nbsp;&nbsp;Some hosts prevent the direct use of sendmail, which PHP uses to send out emails by default. To get around this, we have included code which will contact a separate SMTP server of your choice, and will send the mail trough that. Use this option to select which one to use ("default" means using sendmail via PHP)
								</td>
								<td class="settingInput">
									<select name="MAILER_TYPE" onchange="switchEnableFields(this.selectedIndex, 'MAILER_PASS', 'MAILER_USER', 'MAILER_HOST', 'MAILER_PORT');"><option value="default">Default</option><option value="socket_SMTP">socket SMTP</option></select>
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>SMTP Username</strong><br />
									&nbsp;&nbsp;The username used to log on to your SMTP server.<br />
									Only required when the mail-handler is not set to 'default'
								</td>
								<td class="settingInput">
									<input type="text" name="MAILER_USER" value="username" disabled="disabled"/>
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>SMTP Password</strong><br />
									&nbsp;&nbsp;The password used to log on to your SMTP server with the specified username.<br />
									Only required when the mail-handler is not set to 'default'
								</td>
								<td class="settingInput">
									<input type="password" name="MAILER_PASS" value="password" disabled="disabled"/>
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>SMTP-server ip / hostname</strong><br />
									&nbsp;&nbsp;The host or ip your SMTP-server can be contacted trough.<br />
									Only required when the mail-handler is not set to 'default'
								</td>
								<td class="settingInput">
									<input type="text" name="MAILER_HOST" value="mail.example.com" disabled="disabled"/>
								</td>
							</tr>
							<tr>
								<td class="settingDesc">
									<strong>SMTP-server port</strong><br />
									&nbsp;&nbsp;The port via which your SMTP-server can be contacted. Usually this is 25<br />
									Only required when the mail-handler is not set to 'default'
								</td>
								<td class="settingInput">
									<input type="text" name="MAILER_PORT" value="25" disabled="disabled"/>
								</td>
							</tr>
						</table>
					</div>
					<br />
					<span class="mediumText">
						Press <input type="submit" title="Next Step" value="Next Step" style="text-align: center;" /> to continue.<br />
					</span>
				</div>
			</form>
            <?php
            printFooter();
         break;

        }

    break; // end case 4

    case 5: // Make the administrator set a username and password for the super admin user
        printHeader();
        ?>
		<form action="index.php?step=6" method="post">
			<div id="contentDiv">
				<img src="./splash.gif" title="Welcome to the installer for XMB <?php echo X_VERSION_EXT;?>" alt="Welcome to the installer for XMB <?php echo X_VERSION_EXT;?>" />
				<br />
				&nbsp;
				<br />
				<span id="title">
					Create (Super-)Administrator Account
				</span>
				<span class="mediumText">
					Please enter the username, password, and e-mail address for the initial Super Administrator.<br />
					You may add more Super Administrator accounts after the install finishes.
				</span>

				<div id="configDiv">
					<table class="settingTable">
						<tr>
							<td class="settingDesc">
								Username:
							</td>
							<td class="settingInput">
								<input type="text" name="frmUsername" size="32" />
							</td>
						</tr>
						<tr>
							<td class="settingDesc">
								Password:
							</td>
							<td class="settingInput">
								<input type="password" name="frmPassword" size="32" />
							</td>
						</tr>
						<tr>
							<td class="settingDesc">
								Confirm Password:
							</td>
							<td class="settingInput">
								<input type="password" name="frmPasswordCfm" size="32" />
							</td>
						</tr>
						<tr>
							<td class="settingDesc">
								E-mail:
							</td>
							<td class="settingInput">
								<input type="text" name="frmEmail" size="32" />
							</td>
						</tr>
					</table>
				</div>
                <input type="submit" value="Next Step &gt;" />
			</div>
		</form>
        <?php
        printFooter();
        break;

    case 6: // remaining parts
    	printHeader();
    	?>
    	<div id="contentDiv">
			<img src="./splash.gif" title="Welcome to the installer for XMB <?php echo X_VERSION_EXT;?>" alt="Welcome to the installer for XMB <?php echo X_VERSION_EXT;?>" />
			<br />
			&nbsp;
			<br />
    		<span id="title">
    			Finishing XMB Installation
    		</span>
    		<hr class="separator" />
    	<?php
        // first, let's check if we have right version of PHP
        show_act('Checking PHP version');
        $v = phpversion();
        $v = explode('.', $v);
        if ( $v[0] < 4 || ($v[0] == 4 && $v[1] < 1)) { // < 4.1.0
            show_result(X_INST_ERR);
            error('Minimal System Requirements mismatched', 'XMB noticed your system is using PHP version '.implode('.', $v).', the minimal required version to run XMB is PHP 4.1.0. Please upgrade your PHP install before continuing.', true);
        }
        show_result(X_INST_OK);

        // let's check if all files we need actually exist.
        $req['dirs'] = array('db', 'images', 'include');
        $req['files'] = array('buddy.php', 'config.php', 'cp.php', 'cp2.php', 'dump_attachments.php',
                                'editprofile.php', 'lang/English.lang.php', 'faq.php', 'forumdisplay.php',
                                'functions.php', 'header.php', 'index.php', 'member.php', 'memcp.php',
                                'misc.php', 'phpinfo.php', 'post.php',
                                'include/admin.user.inc.php', 'include/spelling.inc.php', 'include/u2u.inc.php',
                                'stats.php', 'templates.xmb', 'today.php', 'tools.php', 'topicadmin.php',
                                'u2u.php', 'u2uadmin.php', 'viewthread.php', 'xmb.php');

        show_act('Checking Directory Structure');
        foreach ($req['dirs'] as $dir) {
            if (!file_exists(ROOT.$dir)) {
                if ( $dir == 'images') {
                    show_result(X_INST_WARN);
                    error('Missing Directory', 'XMB could not locate the <i>/images</i> directory. Although this directory, and its contents are not vital to the functioning of XMB, we do recommend you upload it, so you can enjoy the full look and feel of each theme.', false);
                    show_act('Checking (remaining) Directory Structure');
                } else {
                    show_result(X_INST_ERR);
                    error('Missing Directory', 'XMB could not locate the <i>'.$dir.'</i> directory. Please upload this directory for XMB to proceed with the installation..', true);
                }
            } else {
                continue;
            }
        }
        show_result(X_INST_OK);

        show_act('Checking Required Files');
        foreach ($req['files'] as $file) {
            if (!file_exists(ROOT.$file)) {
                show_result(X_INST_ERR);
                error('Missing File', 'XMB could not locate the file <i>/'.$file.'</i>, this file is required for XMB to work properly. Please upload this file and restart installation.', true);
            }
        }
        show_result(X_INST_OK);

        // check db-connection.
        require_once ROOT.'xmb.php';
        require_once ROOT.'config.php';

        // double check all stuff here
        show_act('Checking Database Files');
        if (!file_exists(ROOT.'db/'.$database.'.php')) {
            show_result(X_INST_ERR);
            error('Database connection', 'XMB could not locate the <i>/db/'.$database.'.php</i> file, you have configured xmb to use this database-type. For it to work you will need to upload the file, or change the config.php file to reflect a different choice.', true);
        }
        show_result(X_INST_OK);

        show_act('Checking Database API');
        // let's check if the actual functionality exists...
        $err = false;
        switch($database) {
            case 'mysql':
                if (!defined('MYSQL_NUM')) {
                    show_result(X_INST_ERR);
                    $err = true;
                }
                break;

            case 'mysql4':
                if (!defined('MYSQLI_NUM')) {
                    show_result(X_INST_ERR);
                    $err = true;
                }
                break;

            default:
                show_result(X_INST_ERR);
                error('Database Handler', 'Unknown handler provided', true);
                break;
        }
        if ( $err === true) {
            error('Database Handler', 'XMB has determined that your php installation does not support the functions required to use <i>'.$database.'</i> to store all data.', true);
            unset($err);
        }
        show_result(X_INST_OK);

        // let's check the connection itself.
        show_act('Checking Database Connection Security');
        if ( $dbuser == 'root') {
            show_result(X_INST_WARN);
            error('Security hazard', 'You have configured XMB to use root access to the database, this is a security hazard. If your server gets hacked, or php itself crashes, the config.php file might be available freely to anyone looking at it, and thus reveal your root username/password. Please consider making a new user for XMB to run as.', false);
        } else {
            show_result(X_INST_OK);
        }

        show_act('Checking Database Connection');
        switch($database) {
            case 'mysql':
                $link = mysql_connect($dbhost, $dbuser, $dbpw);
                if (!$link) {
                    show_result(X_INST_ERR);
                    error('Database Connection', 'XMB could not connect to the specified database. The database returned "error '.mysql_errno().': '.mysql_error(), true);
                } else {
                    show_result(X_INST_OK);
                }
                $i = mysql_get_server_info($link);
                mysql_close();
                show_act('Checking Database Version');
                $i = explode('.', $i);
                // min = 3.0
                if ( $i[0] < 3 || ($i[0] == 3 && $i[1] < 20)) {
                    show_result(X_INST_ERR);
                    error('Version mismatch', 'XMB requires MySQL version 3.20 or higher to work properly.', true);
                } else {
                    show_result(X_INST_OK);
                }
                break;

            case 'mysql4':
                $link = new mysqli($dbhost, $dbuser, $dbpw, $dbname);
                if (mysqli_connect_errno()) {
                    show_result(X_INST_ERR);
                    error('Database Connection', 'XMB could not connect to the specified database. The database returned "error '.mysqli_connect_errno().': '.mysqli_connect_error(), true);
                } else {
                    show_result(X_INST_OK);
                }
                $i = $link->server_info;
                $link->close();
                $i = explode('.', $i);
                // min = 3.0
                show_act('Checking Database Version');
                if ( $i[0] < 4 || ($i[0] == 4 && $i[1] < 1)) {
                    show_result(X_INST_ERR);
                    error('Version mismatch', 'XMB requires MySQL version 4.1 or higher to work properly with this database API. We recommend using the "mysql" API instead.', true);
                } else {
                    show_result(X_INST_OK);
                }
                break;

            default:
                show_result(X_INST_SKIP);
                break;
        }

        show_act('Checking Full Url Compliance');
        // let's check the $full_url :)
        $urlP = parse_url($full_url);
        if (($stream = @fsockopen($urlP['host'], $_SERVER['SERVER_PORT'], $errno, $errstr, 5)) === false) {
            show_result(X_INST_SKIP);
            error('Configuration Notice', 'XMB could not verify that you have your $full_url correctly configured; the connection was aborted. This test will be skipped.', false);
        } else {
        	socket_set_timeout($stream, 5);
        	$request	= array();
        	$request[]	= 'GET '.$urlP['path'].'install/index.php?action=verifyUrl HTTP/1.0';
        	$request[]	= 'Host: '.$urlP['host'];
        	$request[]	= 'Connection: close';
        	$request	= implode("\r\n", $request);
        	@fwrite($stream, $request, strlen($request));
        	$return = @fread($stream, 1024);
        	fclose($stream);
        	if($return == md5($_SERVER['SCRIPT_FILENAME'])) {
            	show_result(X_INST_OK);
            } else {
            	error('Configuration Notice', 'XMB could not verify that you have your $full_url correctly configured. If this is configured wrong, it will silently prevent logging in later on in the process. However, if you\'re sure it\'s correct, then you can safely ignore this notice.', false);
            }

        }

        // throw in all stuff then :)
        require './cinst.php';
        ?>
            <hr class="separator" />
            <span class="subTitle">
            	Installation Complete
            </span>
            <br />
            Please click <a href="<? echo ROOT; ?>index.php">here</a> to go to your freshly installed forum.
            </div>
            <?php
            printFooter();
        break;

    default:
        header('Location: index.php?step=1');
        exit;
}
tmpManage(X_T_CLOSE);
?>