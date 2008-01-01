<?php
/* $Id: smtp.inc.php,v 1.1.2.2 2005/11/06 01:22:23 Tularis Exp $ */
/*
    XMB 1.9.2
    � 2001 - 2005 Aventure Media & The XMB Development Team
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

class socket_SMTP {
	function stream_SMTP() {
		$this->__construct();
	}
	
	function __construct() {
		$this->connection	= null;
	}
	
	function connect($host, $port, $username='', $password='') {
		$authAvailable = false;
		$loginAvailable = false;
		
		$this->connection = fsockopen($host, $port, $errno, $errstr, 10);
		if(false === $this->connection) {
			return false;
		}
		socket_set_blocking($this->connection, false);

		$this->get();
		$this->send('EHLO');
		$this->get();	// 250
		$this->get();	// hello ? (or 250-SIZE ?)
		while($s = $this->get()) {
			if(strlen($s) < 2) {
				// break on a single space or simply nothing at all
				break;
			}
			if(!$this->isOk($s)) {
				break;
			}
			if(substr($s, 0, 8) == '250-AUTH') {
				$authAvailable = true;
				$methods = substr($s, 8);
				$methods = explode(' ', trim($methods));
				if(in_array('LOGIN', $methods)) {
					$loginAvailable = true;
				}
				break;
			}
		}

		if($authAvailable && $loginAvailable && strlen($username) > 0) {
			$this->send('AUTH LOGIN');
			$this->get();	// some hash...
			$this->send(base64_encode($username));
			$this->get();	// some hash again I guess...
			$this->send(base64_encode($password));
			if($this->fetchReturnCode($this->get()) != 235) {
				$this->disconnect();
				return false;
			}
		}

		return true;
	}
	
	function send($cmd) {
		fwrite($this->connection, $cmd."\r\n");
	}

/*
	function get($eof=false) {
		$line = '';
		$timeout = time() + 2;	// 2 secs max
		while (!feof($this->connection) && (time() < $timeout)) {
			$line .= fgets($this->connection, 2048);
         	if (strlen($line) >= 2 && ($eof === false) && (substr($line, -2) == "\r\n" || substr($line, -1) == "\n")) {
				return rtrim($line);
			}
		}
		return $line;
	}
*/	
	function get() {
		while($line = fgets($this->connection, 515)) {
			$lines .= $line;
			// assume that when the server wants to continue output it will send a '-' as its
			// fourth character. Otherwise it sends a space.
			if(substr($line, 3, 1) == ' ') {
				break;
			}
		}
		return $lines;
	}

	function isOk($ret) {
		if($this->fetchReturnCode($ret) == 250) {
			return true;
		} else {
			return false;
		}
	}
	
	function fetchReturnCode($ret) {
		list($r) = explode(' ', $ret);
		return (int) $r;
	}
	
	function safeData($data) {
		return str_replace(array("\n.", "\r."), array("\n..", "\r.."), $data);
	}
	
	function sendMessage($from, $to, $message, $headers) {
		$headers = $this->safeData($headers);
		$message = $this->safeData($message);
		
		$this->send('MAIL FROM: '.$from);
		if(!$this->isOk($ret = $this->get())) {
			$this->send('RSET');
			return false;
		}
		$this->send('RCPT TO: '.$to);
		if(!$this->isOk($ret = $this->get())) {
			$this->send('RSET');
			return false;
		}
		$this->send('DATA');
		if(354 != $this->fetchReturnCode($ret = $this->get())) {
			$this->send('RSET');
			return false;
		}
		$this->send($headers);
		$this->send('');	// CRLF to distinguish between headers and message
		$this->send($message);
		$this->send('.');
		if(!$this->isOk($this->get())) {
			return false;
		}
		// sent message
		return true;
	}
	
	function disconnect() {
		if($this->connection !== null) {
			$this->send('QUIT');
			fclose($this->connection);
			$this->connection = null;
			return true;
		} else {
			return false;
		}
	}
}
/*
USAGE:
$mail = new socket_SMTP();
$mail->connect('mail.example.com', '25', 'username', 'pass');
$mail->sendMessage('you@example.com', 'me@example.net', 'This is a test message.', 'Subject: whatever-test-mail');
$mail->disconnect();
*/
?>