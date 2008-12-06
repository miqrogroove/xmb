<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11 Beta 2 - This software should not be used for any purpose after 1 February 2009.
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2008, The XMB Group
 * http://www.xmbforum.com
 *
 * Sponsored By iEntry, Inc.
 * Copyright (c) 2007, iEntry, Inc.
 * http://www.ientry.com
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 **/

if (!defined('IN_CODE')) {
    header('HTTP/1.0 403 Forbidden');
    exit("Not allowed to run this file directly.");
}

class dbstuff {
    var $querynum   = 0;
    var $querylist  = array();
    var $querytimes = array();
    var $link       = '';
    var $db         = '';
    var $duration   = 0;
    var $timer      = 0;

    function connect($dbhost="localhost", $dbuser, $dbpw, $dbname, $pconnect=0, $force_db=false) {

        if ($pconnect) {
            $this->link = @mysql_pconnect($dbhost, $dbuser, $dbpw);
        } else {
            $this->link = @mysql_connect($dbhost, $dbuser, $dbpw);
        }

        if ($this->link == false) {
            echo '<h3>Database connection error!</h3>';
            echo 'A connection to the Database could not be established.<br />';
            echo 'Please check your username, password, database name and host.<br />';
            echo 'Also make sure <i>config.php</i> is rightly configured!<br /><br />';
            $sql = '';
            $this->panic($sql);
        }

        unset($GLOBALS['dbhost'], $GLOBALS['dbuser'], $GLOBALS['dbpw']);

        return $this->select_db($dbname, $force_db);
    }

    function select_db($database, $force=true) {
        if ($force) {
            if (!mysql_select_db($database, $this->link)) {
                header('HTTP/1.0 500 Internal Server Error');
                exit('Could not locate database "'.$database.'". Please make sure it exists before trying again!');
                return false;
            }
        } else {
            if (!mysql_select_db($database, $this->link)) {
                global $tablepre;
                echo mysql_error();
                echo '<br />';
                if ($this->find_database($tablepre)) {
                    echo "Using $this->db. Please reconfigure your config.php asap, XMB having to search for a database costs a lot of time and heavily slows down your board!";
                    return true;
                } else {
                    echo 'Could not find any database containing the needed tables. Please reconfigure the config.php';
                    return false;
                    exit();
                }
            } else {
                $this->db = $database;
                return true;
            }
        }
    }

    function find_database($tablepre) {
        $found = false;
        $dbs = mysql_list_dbs($this->link);
        while($db = mysql_fetch_array($dbs)) {
            $q = $this->query("SHOW TABLES FROM `$db[Database]`");
            if (!($this->num_rows($q) > 0)) {
                continue;
            }

            if (strpos(mysql_result($q, 0), $tablepre.'settings') !== false) {
                $this->select_db($db['Database']);
                $this->db = $db['Database'];
                $found = true;
                break;
            }
        }
        return $found;
    }

    function error() {
        return mysql_error($this->link);
    }

    function free_result($query) {
        return mysql_free_result($query);
    }

    function fetch_array($query, $type=SQL_ASSOC) {
        $array = mysql_fetch_array($query, $type);
        return $array;
    }

    function field_name($query, $field) {
        return mysql_field_name($query, $field);
    }

    function panic(&$sql) {
        header('HTTP/1.0 500 Internal Server Error');

        // Check that we actually made a connection
        if ($this->link === FALSE) {
            $error = mysql_error();
            $errno = mysql_errno();
        } else {
            $error = mysql_error($this->link);
            $errno = mysql_errno($this->link);
        }

    	if (DEBUG And (!defined('X_SADMIN') Or X_SADMIN)) {
            require_once(ROOT.'include/validate.inc.php');
			echo '<pre>MySQL encountered the following error: '.cdataOut($error)."(errno = ".$errno.")\n<br />";
            if ($sql != '') {
                echo 'In the following query: <em>'.cdataOut($sql).'</em></pre>';
            }
        } else {
            echo '<pre>The system has failed to process your request. If you\'re an administrator, please set the DEBUG flag to true in config.php.</pre>';
    	}
        if (LOG_MYSQL_ERRORS) {
            if (ini_get('display_errors') Or !ini_get('log_errors')) {
                ini_set('log_errors', TRUE);
                ini_set('error_log', 'error_log');
                ini_set('display_errors', FALSE);
            }
            if (!ini_get('display_errors')) {
                $log = "MySQL encountered the following error:\n$error\n(errno = $errno)\n";
                if ($sql != '') {
                    $log .= "In the following query:\n$sql";
                }
                trigger_error($log, E_USER_ERROR);
            }
        }
        exit;
    }

    // Can be used to make any expression query-safe, but see below.  Example:
    // $db->query('UPDATE a SET b = "'.$db->escape("Hello, my name is $rawinput").'"');
    function escape($rawstring) {
        return mysql_real_escape_string($rawstring, $this->link);
    }
    
    // Preferred for performance when escaping any string variable.  Example:
    // $db->query('UPDATE a SET b = "Hello, my name is '.$db->escape_var($rawinput).'"');
    function escape_var(&$rawstring) {
        return mysql_real_escape_string($rawstring, $this->link);
    }

    function like_escape($rawstring) {
        return str_replace(array('%', '_'), array('\%', '\_'), mysql_real_escape_string($rawstring, $this->link));
    }

    function regexp_escape($rawstring) {
        return mysql_real_escape_string(preg_quote($rawstring), $this->link);
    }

    function query($sql) {
        $this->start_timer();
        $query = mysql_query($sql, $this->link);
        if ($query == false) {
            $this->panic($sql);
        }
        $this->querynum++;
    	if (DEBUG And (!defined('X_SADMIN') Or X_SADMIN)) {
            $this->querylist[] = $sql;
        }
        $this->querytimes[] = $this->stop_timer();
        return $query;
    }

    function unbuffered_query($sql) {
        $this->start_timer();
        $query = mysql_unbuffered_query($sql, $this->link);
        if ($query == false) {
            $this->panic($sql);
        }
        $this->querynum++;
    	if (DEBUG And (!defined('X_SADMIN') Or X_SADMIN)) {
            $this->querylist[] = $sql;
        }
        $this->querytimes[] = $this->stop_timer();
        return $query;
    }

    function fetch_tables($dbname = NULL) {
        if ($dbname == NULL) {
            $dbname = $this->db;
        }
        $this->select_db($dbname);

        $q = $this->query("SHOW TABLES");
        while($table = $this->fetch_array($q, SQL_NUM)) {
            $array[] = $table[0];
        }
        return $array;
    }

    function result($query, $row, $field=NULL) {
        $query = mysql_result($query, $row, $field);
        return $query;
    }

    function num_rows($query) {
        $query = mysql_num_rows($query);
        return $query;
    }

    function num_fields($query) {
        return mysql_num_fields($query);
    }

    function insert_id() {
        $id = mysql_insert_id($this->link);
        return $id;
    }

    function fetch_row($query) {
        $query = mysql_fetch_row($query);
        return $query;
    }
    
    function data_seek($query, $row) {
        return mysql_data_seek($query, $row);
    }
    
    function affected_rows() {
        return mysql_affected_rows($this->link);
    }

    function time($time=NULL) {
        if ($time === NULL) {
            $time = time();
        }
        return "LPAD('".$time."', '15', '0')";
    }

    function start_timer() {
        $mtime = explode(" ", microtime());
        $this->timer = $mtime[1] + $mtime[0];
        return true;
    }

    function stop_timer() {
        $mtime = explode(" ", microtime());
        $endtime = $mtime[1] + $mtime[0];
        $taken = ($endtime - $this->timer);
        $this->duration += $taken;
        $this->timer = 0;
        return $taken;
    }
}

define('SQL_NUM', MYSQL_NUM);
define('SQL_BOTH', MYSQL_BOTH);
define('SQL_ASSOC', MYSQL_ASSOC);
?>
