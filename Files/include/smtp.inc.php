<?php
/**
 * XMB 1.9.5 Nexus Final SP1
 * � 2007 John Briggs
 * http://www.xmbmods.com
 * john@xmbmods.com
 *
 * Developed By The XMB Group
 * Copyright (c) 2001-2007, The XMB Group
 * http://www.xmbforum.com
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 **/

if (!defined('IN_CODE')) {
    exit("Not allowed to run this file directly.");
}

class socket_SMTP {
    function socket_SMTP($debug=false, $dbFile='') {
        $this->__construct($debug, $dbFile);
    }

    function __construct($debug=false, $dbFile='') {
        $this->connection   = null;
        if ($debug) {
            $this->debugStream = fopen($dbFile, 'a+');
            $l = 'SMTP loaded ('.date('r', time()).')'."\n";
            fwrite($this->debugStream, $l, strlen($l));
            $this->debug = true;
        }
    }

    function connect($host, $port, $username='', $password='') {
        $authAvailable = false;
        $loginAvailable = false;

        $this->doDebug('Attempting connection on '.$host.':'.$port);
        $this->connection = fsockopen($host, $port, $errno, $errstr, 10);
        if (false === $this->connection) {
            $this->doDebug('Connection failed');
            return false;
        } else {
            $this->doDebug('Connection succesfull');
            $this->doDebug('Starting handshake');
        }
        socket_set_blocking($this->connection, true);

        $this->get();
        $this->send('EHLO');

        $s = $this->get();
        if (!$this->isOk($s)) {
            return false;
        }
        $parts = explode("\r\n", $s);
        foreach ($parts as $ns) {
            if (substr($ns, 0, 8) == '250-AUTH') {
                $authAvailable = true;
                $methods = substr($ns, 8);
                $methods = explode(' ', trim($methods));
                if (in_array('LOGIN', $methods)) {
                    $loginAvailable = true;
                }
                break;
            }
        }

        if ($authAvailable && $loginAvailable && strlen($username) > 0) {
            $this->send('AUTH LOGIN');
            $this->get();
            $this->send(base64_encode($username));
            $this->get();
            $this->send(base64_encode($password));
            if ($this->fetchReturnCode($this->get()) != 235) {
                $this->disconnect();
                return false;
            }
        }

        return true;
    }

    function send($cmd) {
        $this->doDebug('[C] '.$cmd);
        fwrite($this->connection, $cmd."\r\n");
    }

    function get() {
        while (($line = fgets($this->connection, 515)) !== false) {
            $this->doDebug('[S] '.$line);
            $lines .= $line;

            if (substr($line, 3, 1) == ' ') {
                break;
            }
        }
        return $lines;
    }

    function isOk($ret) {
        if ($this->fetchReturnCode($ret) == 250) {
            return true;
        } else {
            return false;
        }
    }

    function fetchReturnCode($ret) {
        list ($r) = explode(' ', $ret);
        return (int) $r;
    }

    function safeData($data) {
        return str_replace(array("\n.", "\r."), array("\n..", "\r.."), $data);
    }

    function sendMessage($from, $to, $message, $headers) {
        $headers = $this->safeData($headers);
        $message = $this->safeData($message);

        $this->send('MAIL FROM: '.$from);
        if (!$this->isOk($ret = $this->get())) {
            $this->send('RSET');
            return false;
        }
        $this->send('RCPT TO: '.$to);
        if (!$this->isOk($ret = $this->get())) {
            $this->send('RSET');
            return false;
        }
        $this->send('DATA');
        if (354 != $this->fetchReturnCode($ret = $this->get())) {
            $this->send('RSET');
            return false;
        }
        $this->send($headers);
        $this->send('');
        $this->send($message);
        $this->send('.');
        if (!$this->isOk($this->get())) {
            return false;
        }

        return true;
    }

    function disconnect() {
        if ($this->connection !== null) {
            $this->doDebug('Disconnecting from server');
            $this->send('QUIT');
            $this->get();
            fclose($this->connection);
            $this->connection = null;
            return true;
        } else {
            $this->doDebug('No server connection found; disconnect failed');
            return false;
        }
    }

    function doDebug($msg) {
        if (isset($this->debug) && $this->debug === true) {
            $msg = rtrim($msg)."\n";
            fwrite($this->debugStream, $msg, strlen($msg));
        }
    }
}
?>