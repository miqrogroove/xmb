<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2024, The XMB Group
 * https://www.xmbforum2.com/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 **/

// Script Parameters
$req['dirs'] = array('db', 'fonts', 'images', 'include', 'js', 'lang');
$req['files'] = array(
    'buddy.php',
    'config.php',
    'cp.php',
    'cp2.php',
    'css.php',
    'db/mysqli.php',
    'editprofile.php',
    'faq.php',
    'files.php',
    'forumdisplay.php',
    'header.php',
    'include/admin.inc.php',
    'include/attach.inc.php',
    'include/buddy.inc.php',
    'include/captcha.inc.php',
    'include/debug.inc.php',
    'include/functions.inc.php',
    'include/global.inc.php',
    'include/online.inc.php',
    'include/schema.inc.php',
    'include/sessions.inc.php',
    'include/smtp.inc.php',
    'include/spelling.inc.php',
    'include/tokens.inc.php',
    'include/translation.inc.php',
    'include/u2u.inc.php',
    'include/validate.inc.php',
    'include/validate-email.inc.php',
    'include/version.php',
    'index.php',
    'install/cinst.php',
    'lang/English.lang.php',
    'License.txt',
    'lost.php',
    'member.php',
    'memcp.php',
    'misc.php',
    'post.php',
    'search.php',
    'stats.php',
    'templates.xmb',
    'today.php',
    'tools.php',
    'topicadmin.php',
    'u2u.php',
    'viewthread.php',
    'vtmisc.php'
);

// Script Constants
define('ROOT', '../');
define('X_INST_ERR', 0);
define('X_INST_WARN', 1);
define('X_INST_OK', 2);
define('X_INST_SKIP', 3);
define('COMMENTOUTPUT', false);
define('MAXATTACHSIZE', 256000);
define('IPREG', 'on');
define('IPCHECK', 'off');
define('SPECQ', false);
define('SHOWFULLINFO', false);
define('IN_CODE', true);

require ROOT.'include/version.php';

function error($head, $msg, $die=true) {
    echo "\n";
    echo '<h1 class="progressErr">'.$head.'</h1>';
    echo '<span class="progressWarn">'.$msg.'</span><br />';
    echo "\n";
    if ($die) {
        echo '
            </div>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="footer">
        <div class="top"><span></span></div>
        <div class="center-content">
            <span><a href="https://www.xmbforum2.com/" onclick="window.open(this.href); return false;"><strong><abbr title="eXtreme Message Board">XMB</abbr>
            Forum Software</strong></a>&nbsp;&copy; '.COPY_YEAR.' The XMB Group</span>
        </div>
        <div class="bottom"><span></span></div>
    </div>
</div>';
        exit();
    }
}

function show_act($act) {
    $act .= str_repeat('.', (75-strlen($act)));
    echo '<span class="progress">'.$act;
}

function show_result($type) {
    switch($type) {
    case 0:
        echo '<span class="progressErr">ERROR</span><br />';
        break;
    case 1:
        echo '<span class="progressWarn">WARNING</span><br />';
        break;
    case 2:
        echo '<span class="progressOk">OK</span><br />';
        break;
    case 3:
        echo '<span class="progressSkip">SKIPPED</span><br />';
        break;
    }
    echo "</span>\n";
}

/**
 * Haults the script if XMB is already installed.
 *
 * @since 1.9.11.09
 * @param string $database
 * @param string $dbhost
 * @param string $dbuser
 * @param string $dbpw
 * @param string $dbname
 * @param bool   $pconnect
 * @param string $tablepre
 */
function already_installed($database, $dbhost, $dbuser, $dbpw, $dbname, $pconnect, $tablepre) {
    // When config.php has default values, XMB is not installed.
    $config_array = array(
        'dbname' => 'DB/NAME',
        'dbuser' => 'DB/USER',
        'dbpw' => 'DB/PW',
        'dbhost' => 'DB_HOST',
        'tablepre' => 'TABLE/PRE',
    );
    foreach($config_array as $key => $value) {
        if (${$key} === $value) {
            return;
        }
    }

    if (!defined('DEBUG')) define('DEBUG', FALSE);
    if (!defined('LOG_MYSQL_ERRORS')) define('LOG_MYSQL_ERRORS', FALSE);

    // Force upgrade to mysqli
    if ('mysql' === $database) $database = 'mysqli';

    if (!is_readable(ROOT."db/{$database}.php")) return;
    require_once ROOT."db/{$database}.php";

    $db = new dbstuff;
    $result = $db->test_connect($dbhost, $dbuser, $dbpw, $dbname);
    if (!$result) return;
    
    $like_name = $db->like_escape($tablepre . 'settings');
    $result = $db->query("SHOW TABLES LIKE '$like_name'");
    $count = $db->num_rows($result);
    $db->free_result($result);
    if (1 === $count) {
        error('XMB Already Installed', 'An existing installation of XMB has been detected. Please <a href="../index.php">click here to go to your forum.</a><br />If you wish to overwrite this installation, please drop your settings table. To install another forum on the same database, enter a different table prefix in config.php.');
    }
    $db->close();
}

//error_reporting(E_ALL&~E_NOTICE);
error_reporting(-1);

if (isset($_REQUEST['step']) && $_REQUEST['step'] < 7 && $_REQUEST['step'] != 4) {
    header("Content-type: text/html;charset=ISO-8859-1");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>XMB Installer</title>
    <meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" />
    <link rel="stylesheet" href="../images/install/install.css" type="text/css" media="screen"/>
</head>
<body>
<div id="main">
    <div id="header">
        <img src="../images/install/logo.png" alt="XMB" title="XMB" />
    </div>
<?php
}

if (is_readable(ROOT.'config.php')) {
    include ROOT.'config.php';
    if (isset($database, $dbhost, $dbuser, $dbpw, $dbname, $pconnect, $tablepre)) {
        already_installed( $database, $dbhost, $dbuser, $dbpw, $dbname, $pconnect, $tablepre );
    }
}

$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : 0;
$substep = isset($_REQUEST['substep']) ? $_REQUEST['substep'] : 0;
$vStep = isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : 0;

switch($vStep) {
    case 1: // welcome
?>
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
            <p>Welcome to the installer for XMB, the popular open-source lightweight message board software. Thank you for chosing XMB to foster your new community. The next steps will guide you through the installation of your XMB Powered Message Board.</p>
            <form action="./index.php?step=2" method="post">
                    <p class="button"><input type="submit" value="Start Installation &gt;" /></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
<?php
        break;

    case 2: // versioncheck
        ?>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li>Welcome</li>
                <li class="current">Version Check</li>
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
            <h1>Version Check Information</h1>
            <p>This page displays your version of XMB, and the latest version available from XMB. If there is a later version, XMB strongly recommends you do not install this version, but choose the latest stable release.</p>
            <ul>
                <li>Install This Version: XMB <?php echo X_VERSION_EXT;?></li>
                <li>Latest Available Version: <img src="https://www.xmbforum2.com/phpbin/xmbvc/vc.php?bg=f0f0f0&amp;fg=000000" alt="XMB Version Cant Be Found" style="position: relative; top: 8px;" /></li>
            </ul>
            <form action="./index.php?step=3" method="post">
                <p class="button"><input type="submit" value="Install XMB <?php echo X_VERSION;?> &gt;" /></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
        <?php
        break;

    case 3: // agreement
        ?>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li>Welcome</li>
                <li>Version Check</li>
                <li class="current">License Agreement</li>
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
            <h1>XMB <?php echo X_VERSION;?> License Agreement</h1>
            <p>Please read over the agreement below, and if you agree to it select the button at the very bottom. By selecting the button, you agree to the terms below.</p>
            <textarea style="width: 90%;" rows="30"  name="agreement" style= "font-family: Verdana; font-size: 8pt; margin-left: 4%;" readonly="readonly">
XMB <?php echo X_VERSION; ?>  License (Updated November 2007)
www.xmbforum2.com
----------------------------------------------

<?php readfile(ROOT.'License.txt'); ?>

            </textarea>
            <form action="index.php?step=4" method="post">
                <p class="button"><input type="submit" value="I Agree To These Terms &gt;" /></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
        <?php
        break;

    case 4: // config.php set-up
        $vSubStep = isset($_REQUEST['substep']) ? trim($_REQUEST['substep']) : '';
        switch($vSubStep) {
        case 'create':
            // Open config.php
            $configuration = file_get_contents(ROOT.'config.php');

            // Now, replace the main text values with those given by user
            $find = array('DB/NAME', 'DB/USER', 'DB/PW', "= 'localhost';", 'TABLE/PRE', 'FULLURL', "= 'default';", 'MAILER_USER', 'MAILER_PASS', 'MAILER_HOST', 'MAILER_PORT');
            $replace = array($_REQUEST['db_name'], $_REQUEST['db_user'], $_REQUEST['db_pw'], "= '{$_REQUEST['db_host']}';", $_REQUEST['table_pre'], $_REQUEST['fullurl'], "= '{$_REQUEST['MAILER_TYPE']}';", $_REQUEST['MAILER_USER'], $_REQUEST['MAILER_PASS'], $_REQUEST['MAILER_HOST'], $_REQUEST['MAILER_PORT']);
            $configuration = str_replace($find, $replace, $configuration);

            // Change Comment Output Option
            if (isset($_REQUEST['c_output'])) {
                $configuration = str_replace("comment_output = FALSE;", "comment_output = TRUE;", $configuration);
            }

            // IP Check
            if (isset($_REQUEST['ip_check'])) {
                $configuration = str_replace("ipcheck        = 'off';", "ipcheck        = 'on';", $configuration);
            }

            // Allow Special Queries
            if (isset($_REQUEST['allowspecialq'])) {
                $configuration = str_replace("allow_spec_q   = FALSE;", "allow_spec_q   = TRUE;", $configuration);
            }

            // Show Full Footer Info
            if (!isset($_REQUEST['showfullinfo'])) {
                $configuration = str_replace("show_full_info = TRUE;", "show_full_info = FALSE;", $configuration);
            }

            switch($_REQUEST['method']) {
                case 1: // Show configuration on screen
                    header("Content-type: text/html;charset=ISO-8859-1");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>XMB Installer</title>
    <meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <link rel="stylesheet" href="../images/install/install.css" type="text/css" media="screen"/>
</head>
<body>
<div id="main">
    <div id="header">
        <img src="../images/install/logo.png" alt="XMB" title="XMB" />
    </div>
    <div id="configure">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>XMB Configuration</h1>
            <p>Copy the following into to a new file, and call it &quot;config.php&quot;.&nbsp; Upload it to the root of your XMB directory. Then, click to continue to the next steps.</p>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="config">
        <div class="top"><span></span></div>
        <div class="center-content">
            <textarea readonly="readonly" style="width: 90%;" rows="100"><?php echo($configuration); ?></textarea>
            <form action="index.php?step=5" method="post">
                <p class="button"><input type="submit" value="Close Window" onclick="window.close()"></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
<?php
                    break;

                case 2:
                    header("Content-type: text/html;charset=ISO-8859-1");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>XMB Installer</title>
    <meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <link rel="stylesheet" href="../images/install/install.css" type="text/css" media="screen, projection" />
</head>
<body>
<div id="main">
    <div id="header">
        <img src="../images/install/logo.png" alt="XMB" title="XMB" />
    </div>
    <div id="configure">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>XMB Configuration</h1>
            <div>
                <p>
<?php
                    // Open a new file
                    $handle = @fopen(ROOT.'config.php', "w");
                    if (!$handle) {
                        error('Invalid Permissions', 'XMB cannot create your configuration file on the server as it does not have enough permissions to do so.  If you would like to try again, CHMOD the "config.php" file to <i>666</i> or select a different method', true);
                    }

                    // Write to file, then close file
                    fwrite($handle, $configuration);
                    fclose($handle);

                    // Continue to next step
                    echo 'Your XMB configuration has been created correctly on the server.';
?>
                </p>
            </div>
            <form action="index.php?step=5" method="post">
                <p class="button"><input type="submit" value="Close Window" onclick="window.close()"></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
<?php
                    break;

                case 3:
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
                default:
                    error('Configuration Method Missing', 'You did not specify a method of configuration.  Please go back and do so now. ', true);
                    break;
                } // for method
                break; // for substep4

        case 'continue':
            // show next button
            echo 'continue';
            break;

        default:
            header("Content-type: text/html;charset=ISO-8859-1");
            $scheme = 'http';
            if ( ! empty( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) {
                $scheme = 'https';
            }
            // Get the DB types...
            $types = '<select name="db_type"><option selected="selected" value="mysql">mysql</option></select>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
    <title>XMB Installer</title>
    <meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <link rel="stylesheet" href="../images/install/install.css" type="text/css" media="screen, projection" />
</head>
<body>
<div id="main">
    <div id="header">
        <img src="../images/install/logo.png" alt="XMB" title="XMB" />
    </div>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li>Welcome</li>
                <li>Version Check</li>
                <li>License Agreement</li>
                <li class="current">Configuration</li>
                <li>Create Super Administrator Account</li>
                <li>Install</li>
            </ul>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="content">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>XMB Configuration</h1>
            <p>If you have not configured your config.php file, complete the form below and select "Configure XMB". If you have already configured config.php correctly, you may skip this process and select "Next Step" below. If you select "Configure XMB", a new window will pop up. When you return, select "Next Step" to continue the installation process.</p>
            <form action="index.php?step=4&amp;substep=create" method="post" target="_blank">
                <table cellspacing="1px">
                    <tr>
                        <td colspan="2">
                            <h1>Configuration Method</h1>
                            <p>Please choose the Configuration Method you would like to use below.</p>
                            <ol>
                                <li>Show the configuration on screen: This option will show the config.php information on screen so that you can copy it into your own config.php file</li>
                                <li>Attempt to create config.php: This option will attempt to create config.php directly onto the server. For this to work, the current config.php must have a CHMOD Value of 666</li>
                                <li>Download config.php: This option will create config.php and allow you to download the complete file onto your computer. Once downloaded you will need to upload the file to the root of your XMB directory</li>
                            </ol>
                            <p>
                                <select size="1" name="method">
                                    <option value="1">1)&nbsp; Show the configuration on screen.</option>
                                    <option value="2">2)&nbsp; Attempt to create config.php for me.</option>
                                    <option value="3">3)&nbsp; Download config.php onto my computer.</option>
                                </select>
                            </p>
                        </td>
                    </tr>
                    <tr class="category">
                        <td colspan="2">Database Connection Settings</td>
                    </tr>
                    <tr>
                        <td>Database Name<br /><span>Name of your database</span></td>
                        <td><input type="text" name="db_name" size="40" /></td>
                    </tr>
                    <tr>
                        <td>Database Username<br /><span>User used to access database</span></td>
                        <td><input type="text" name="db_user" size="40" /></td>
                    </tr>
                    <tr>
                        <td>Database Password<br /><span>Password for the Database User</span></td>
                        <td><input type="password" name="db_pw" size="40" /></td>
                    </tr>
                    <tr>
                        <td>Database Host<br /><span>Database host location, usually "localhost"</span></td>
                        <td><input type="text" name="db_host" size="40" value="localhost" /></td>
                    </tr>
                    <tr>
                        <td>Database Type<br /><span>The type of database server. At this time, only mysql is supported</span></td>
                        <td><?php echo $types?></td>
                    </tr>
                    <tr>
                        <td>Database Table Prefix<br /><span>Specify a prefix for this board's database tables.</span></td>
                        <td><input type="text" name="table_pre" size="40" value="xmb_" /></td>
                    </tr>
                    <tr class="category">
                        <td colspan="2">Forum Settings</td>
                    </tr>
                    <tr>
                        <td>Full URL<br /><span>Put the full URL of your boards here, without any file names. Be sure to include a slash at the end.</span></td>
                        <td><input type="text" name="fullurl" size="40" value="<?php echo "$scheme://".$_SERVER['HTTP_HOST'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')-7);?>" /></td>
                    </tr>
                    <tr>
                        <td>Maximum Attachment Size<br /><span>Enter the maximum allowed attachment size for your board here. (250*1024) for example, would be 250KB</span></td>
                        <td><input name="maxattachsize" size="40" value="(250*1024)" /></td>
                    </tr>
                    <tr>
                        <td>Comment Output<br /><span>This setting will allow you to chose whether you want comments indicating templates. Default: Off</span></td>
                        <td><input type="checkbox" name="c_output" value="TRUE" /></td>
                    </tr>
                    <tr>
                        <td>Restrict User Registration Per IP<br /><span>This will restrict registration of users to one per IP address every 24 hours. Default: On</span></td>
                        <td><input type="checkbox" name="ip_reg" value="on" checked="checked" /></td>
                    </tr>
                    <tr>
                        <td>IP Validation Check<br /><span>This will check users' IP addresses are valid IPv4 or IPv6 type, if none of these. Default: Off</span></td>
                        <td><input type="checkbox" name="ip_check" value="off" /></td>
                    </tr>
                    <tr>
                        <td>Allow Special Queries<br /><span>This specifies whether special database queries such as USE database and SHOW database are allowed. Default: off</span></td>
                        <td><input type="checkbox" name="allowspecialq" value="off" /></td>
                    </tr>
                    <tr>
                        <td>Show Full XMB Version Info<br /><span>This will show the full version information of your XMB Board. Default: Off</span></td>
                        <td><input type="checkbox" name="showfullinfo" value="off" /></td>
                    </tr>
                    <tr class="category">
                        <td colspan="2">XMB E-Mail Settings</td>
                    </tr>
                    <tr>
                        <td colspan="2"><p>XMB by default uses sendmail to send email from the board. As some hosts don't allow direct use of sendmail, you may chose to configure XMB to use SMTP to send E-mail instead. Please choose your configuration below.</p></td>
                    </tr>
                    <tr>
                        <td>Mail Handler<br /><span>Chose your mail handler. If you wish to use the default sendmail, select "Default" and disregard the configuration options below. If you chose SMTP, please complete the options below. Default: "Default"</span></td>
                        <td><select name="MAILER_TYPE"><option value="default">Default</option><option value="socket_SMTP">socket SMTP</option></select></td>
                    </tr>
                    <tr>
                        <td>SMTP Username:</td>
                        <td><input type="text" name="MAILER_USER" value="username" /></td>
                    </tr>
                    <tr>
                        <td>SMTP Password:</td>
                        <td><input type="password" name="MAILER_PASS" value="password" /></td>
                    </tr>
                    <tr>
                        <td>SMTP Host:</td>
                        <td><input type="text" name="MAILER_HOST" value="mail.example.com" /></td>
                    </tr>
                    <tr>
                        <td>SMTP Port:</td>
                        <td><input type="text" name="MAILER_PORT" value="25" /></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="configure"><input type="submit" value="Configure" name="submit" /></td>
                    </tr>
                </table>
            </form>
            <form action="index.php?step=5" method="post">
                <p class="button"><input type="submit" value="Next Step" /></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>

<?php
            break;
        }
        break; // end case 4

    case 5: // Make the administrator set a username and password for the super admin user

        require_once(ROOT.'config.php');

        $config_array = array(
            'dbname' => 'DB/NAME',
            'dbuser' => 'DB/USER',
            'dbpw' => 'DB/PW',
            'dbhost' => 'DB_HOST',
            'database' => 'DB_TYPE',
            'tablepre' => 'TABLE/PRE',
            'full_url' => 'FULLURL',
            'ipcheck' => 'IPCHECK',
            'allow_spec_q' => 'SPECQ',
            'show_full_info' => 'SHOWFULLINFO',
            'comment_output' => 'COMMENTOUTPUT'
        );
        foreach($config_array as $key => $value) {
            if (${$key} === $value) {
                error('Incorrect Configuration', 'XMB noticed that your config.php file is not fully configured.<br />Please go back to the previous step and follow the instructions carefully.<br />Be sure to click the button labeled "Configure" before proceeding.', TRUE);
            }
        }

        $versionlong = '';
        require ROOT.'include/debug.inc.php';
        $array = parse_url($full_url);
        if (!isset($array['path'])) {
            $array['path'] = '/';
        }
        if (strpos($array['host'], '.') === FALSE || preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/", $array['host'])) {
            $array['host'] = '';
        } elseif (substr($array['host'], 0, 4) === 'www.') {
            $array['host'] = substr($array['host'], 3);
        }
        debugURLsettings(($array['scheme'] == 'https'), $array['host'], $array['path']);
        unset($array);


        ?>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li>Welcome</li>
                <li>Version Check</li>
                <li>License Agreement</li>
                <li>Configuration</li>
                <li class="current">Create Super Administrator Account</li>
                <li>Install</li>
            </ul>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="content">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>Create Super Administrator Account</h1>
            <p>Please fill out the Username, Password, and E-Mail account for the first Super Administrator account for your message board. This will be the account you use to first login to your board</p>
            <script type="text/javascript">
            <!--//--><![CDATA[//><!--
            function disableButton() {
                var newAttr = document.createAttribute("disabled");
                newAttr.nodeValue = "disabled";
                document.getElementById("submit1").setAttributeNode(newAttr);
                return true;
            }
            //--><!]]>
            </script>
            <form action="index.php?step=6" method="post" onsubmit="disableButton();">
                <table cellspacing="1px">
                    <tr>
                        <td>Username:</td>
                        <td><input type="text" name="frmUsername" size="32" /></td>
                    </tr>
                    <tr>
                        <td>Password:</td>
                        <td><input type="password" name="frmPassword" size="32" /></td>
                    </tr>
                    <tr>
                        <td>Confirm Password:</td>
                        <td><input type="password" name="frmPasswordCfm" size="32" /></td>
                    </tr>
                    <tr>
                        <td>E-Mail Address:</td>
                        <td><input type="text" name="frmEmail" size="32" /></td>
                    </tr>
                </table>
                <p class="button"><input type="submit" value="Begin Installation &gt;" id="submit1" /></p>
            </form>
        </div>
        <div class="bottom"><span></span></div>
    </div>
<?php
        break;

    case 6: // remaining parts
        ?>
    <div id="sidebar">
        <div class="top"><span></span></div>
        <div class="center-content">
            <ul>
                <li>Welcome</li>
                <li>Version Check</li>
                <li>License Agreement</li>
                <li>Configuration</li>
                <li>Create Super Administrator Account</li>
                <li class="current">Install</li>
            </ul>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="content">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>Installing XMB</h1>
            <div class="install">
<?php
        // first, let's check if we have right version of PHP
        show_act('Checking PHP version');
        if (version_compare(phpversion(), PHP_MIN_VER, '<')) {
            show_result(X_INST_ERR);
            error('Version mismatch', 'XMB requires PHP version '.PHP_MIN_VER.' or higher to work properly.  Version '.phpversion().' is running.', true);
        }
        show_result(X_INST_OK);

        // let's check if all files we need actually exist.
        show_act('Checking Directory Structure');
        foreach($req['dirs'] as $dir) {
            if (!file_exists(ROOT.$dir)) {
                if ($dir == 'images') {
                    show_result(X_INST_WARN);
                    error('Missing Directory', 'XMB could not locate the <i>/images</i> directory. Although this directory, and its contents are not vital to the functioning of XMB, we do recommend you upload it, so you can enjoy the full look and feel of each theme.', false);
                    show_act('Checking (remaining) Directory Structure');
                } else {
                    show_result(X_INST_ERR);
                    error('Missing Directory', 'XMB could not locate the <i>'.$dir.'</i> directory. Please upload this directory for XMB to proceed with the installation.', true);
                }
            } else {
                continue;
            }
        }
        show_result(X_INST_OK);

        show_act('Checking Required Files');
        foreach($req['files'] as $file) {
            if (!file_exists(ROOT.$file)) {
                show_result(X_INST_ERR);
                error('Missing File', 'XMB could not locate the file <i>/'.$file.'</i>, this file is required for XMB to work properly. Please upload this file and restart installation.', true);
            }
        }
        show_result(X_INST_OK);

        // check db-connection.
        require_once(ROOT.'config.php');

        // double check all stuff here
        show_act('Checking Database Files');

        // Force upgrade to mysqli
        if ('mysql' === $database) $database = 'mysqli';

        if (!file_exists(ROOT."db/{$database}.php")) {
            show_result(X_INST_ERR);
            error('Database connection', 'XMB could not locate the <i>/db/'.$database.'.php</i> file, you have configured xmb to use this database-type. For it to work you will need to upload the file, or change the config.php file to reflect a different choice.', true);
        }
        show_result(X_INST_OK);
        
        require_once ROOT."db/{$database}.php";

        $db = new dbstuff;
        
        show_act('Checking Database API');
        // let's check if the actual functionality exists...

        if (!$db->installed()) {
            error('Database Handler', 'XMB has determined that your php installation does not support the functions required to use <i>'.$database.'</i> to store all data.', true);
            unset($err);
        }
        show_result(X_INST_OK);

        // let's check the connection itself.
        show_act('Checking Database Username Security');
        if ($dbuser == 'root') {
            show_result(X_INST_WARN);
            error('Security hazard', 'You have configured XMB to use root access to the database, this is a security hazard. If your server gets hacked, or php itself crashes, the config.php file might be available freely to anyone looking at it, and thus reveal your root username/password. Please consider making a new user for XMB to run as.', false);
        } else {
            show_result(X_INST_OK);
        }

        show_act('Checking Database Connection');
        $result = $db->test_connect($dbhost, $dbuser, $dbpw, $dbname);
        if (!$result) {
            show_result( X_INST_ERR );
            error('Database Connection', 'XMB could not connect to the specified database. The database returned "error '.mysqli_connect_error().': '.mysqli_connect_errno(), true);
        } else {
            show_result( X_INST_OK );
        }
        $sqlver = $db->server_info;
        $db->close();
        show_act('Checking Database Version');
        if (version_compare($sqlver, MYSQL_MIN_VER, '<')) {
            show_result(X_INST_ERR);
            error('Version mismatch', 'XMB requires MySQL version '.MYSQL_MIN_VER.' or higher to work properly.  Version '.$sqlver.' is running.', true);
        } else {
            show_result(X_INST_OK);
        }

        // throw in all stuff then :)
        require './cinst.php';
?>
            </div>
        </div>
        <div class="bottom"><span></span></div>
    </div>
    <div id="complete">
        <div class="top"><span></span></div>
        <div class="center-content">
            <h1>Installation Complete</h1>
            <p>The installation process completed successfully, and your new XMB Powered Forum is now ready for you to use. Please click <a href="../index.php">here</a> to go to your forum.</p>
        </div>
        <div class="bottom"><span></span></div>
    </div>
<?php
        break;
    default:
        header('Location: index.php?step=1');
        exit;
}
?>
    <div id="footer">
        <div class="top"><span></span></div>
        <div class="center-content">
            <span><a href="https://www.xmbforum2.com/" onclick="window.open(this.href); return false;"><strong><abbr title="eXtreme Message Board">XMB</abbr>
            Forum Software</strong></a>&nbsp;&copy; <?php echo COPY_YEAR; ?> The XMB Group</span>
        </div>
        <div class="bottom"><span></span></div>
    </div>
</div>
</body>
</html>
