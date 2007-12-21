<?php
/* $Id: install.inc.php,v 1.2 2006/02/03 20:40:03 Tularis Exp $ */
/*
    XMB 1.10.0
    © 2001 - 2005 Aventure Media & The XMB Developement Team

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

define('X_VERSION',		'1.10.0'		);
define('X_VERSION_EXT',	'1.10.0 Alpha'	);

define('X_INST_ERR',	0				);
define('X_INST_WARN',	1				);
define('X_INST_OK',		2				);
define('X_INST_SKIP',	3				);

define('X_T_OPEN',		0				);
define('X_T_CLOSE',		1				);
define('X_T_GET',		2				);
define('X_T_STORE',		3				);

define('COMMENTOUTPUT',	false			);
define('MAXATTACHSIZE',	256000			);
define('IPREG',			'on'			);
define('IPCHECK',		'off'			);
define('SPECQ',			false			);
define('SHOWFULLINFO',	false			);

function error($head, $msg, $die=true) {
    echo "\n";
    echo '<div class="progressErrorDiv">';
    echo '<span class="progressErr">'.$head.'<br /></span>';
    echo '<span class="progressWarn">'.$msg.'</span>';
    echo '</div>';
    echo "\n";
    if ( $die) {
        exit();
    }
}

function show_act($act) {
    $act .= str_repeat('.', (75-strlen($act)));
    echo '<span class="progress">'.$act.'</span>';
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
    echo "\n";
}

if (!function_exists('file_get_contents')) {
    function file_get_contents($file) {
        $stream = fopen($file, 'r');
        $contents = fread($stream, filesize($file));
        fclose($stream);

        return $contents;
    }
}

function printHeader($js=null) {
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
			<title>
				XMB Installer
			</title>
			<style type="text/css">
				@import url("./install.css");
			</style>
			<?php
			if(!is_null($js)) {
				echo $js;
			}
			?>
		</head>
		<body style="text-align: center;">
	<?php
}

function printFooter() {
		?>
		</body>
	</html>
	<?php
}

function rmFromDir($path) {
    if (is_dir($path)) {
        $stream = opendir($path);
        while(($file = readdir($stream)) !== false) {
            if ( $file == '.' || $file == '..') {
                continue;
            }
            rmFromDir($path.'/'.$file);
        }
        closedir($stream);
        @rmdir($path);
    } elseif (is_file($path)) {
        @unlink($path);
    }
}

function tmpStore($setting, $value) {
	tmpManage(X_T_STORE, $setting, $value);
	return true;
}

function tmpManage($method, $setting='', $value='') {
	static $contents;
	
	switch($method) {
		case X_T_OPEN:
			// (re)read from file
			$stream	= fopen('./install.tmp', 'r+');
			$data	= fread($stream, filesize('./install.tmp'));
					  fclose($stream);
			
			if(strlen($data) > 0) {
				$contents = unserialize($data);
			} else {
				$contents = array();
			}
			
			return true;
			break;
		
		case X_T_CLOSE:
			// write to file
			if(count($contents) > 0) {
				$data	= serialize($contents);
			} else {
				$data	= '';
			}
			$stream	= fopen('./install.tmp', 'w+');
					  fwrite($stream, $data, strlen($data));
					  fclose($stream);
			
			return true;
			break;
		
		case X_T_GET:
			// read from $contents
			if(isset($contents[$setting])) {
				return $contents[$setting];
			} else {
				return false;
			}
			break;
		
		case X_T_STORE:
			// write to $contents
			$contents[$setting] = $value;
			return true;
			break;
		default:
			return false;
	}
}

function tmpRead($setting) {
	return tmpManage(X_T_GET, $setting);
}
?>