<?php
/**
 * eXtreme Message Board
 * XMB 1.9.11
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2012, The XMB Group
 * http://www.xmbforum2.com/
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

define('SQL_NUM', MYSQLI_NUM);
define('SQL_BOTH', MYSQLI_BOTH);
define('SQL_ASSOC', MYSQLI_ASSOC);

class dbstuff {
    var $querynum   = 0;
    var $querylist  = array();
    var $querytimes = array();
    var $link       = '';
    var $db         = '';
    var $duration   = 0;
    var $timer      = 0;
    var $errcallb   = 'xmb_mysql_error';
    var $last_id    = 0;
    var $last_rows  = 0;

    /**
     * Establishes a connection to the MySQL server.
     *
     * @param string $dbhost
     * @param string $dbuser
     * @param string $dbpw
     * @param string $dbname
     * @param bool   $pconnect Keep the connection open after the script ends.
     * @param bool   $force_db Generate a fatal error if the $dbname database doesn't exist on the server.
     * @param bool   $new_link Use to connect to a second database on the same server at the same time.
     */
    function connect($dbhost='localhost', $dbuser, $dbpw, $dbname, $pconnect=FALSE, $force_db=FALSE, $new_link=FALSE) {

        if ($pconnect) {
            $this->link = @mysqli_connect("p:$dbhost", $dbuser, $dbpw, $new_link);
        } else {
            $this->link = @mysqli_connect($dbhost, $dbuser, $dbpw, $new_link);
        }

        if (FALSE === $this->link) {
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

    /**
     * Sets the name of the database to be used on this connection.
     *
     * @param string $database The full name of the MySQL database.
     * @param bool $force Optional. Specifies error mode. Dies if true.
     * @return bool TRUE on success, FALSE on failure with !$force.
     */
    function select_db($database, $force = TRUE) {
        if (mysqli_select_db($this->link, $database)) {
            $this->db = $database;
            return TRUE;
        }
        if ($force) {
            $sql = "USE $database -- XMB couldn't find the database! Please reconfigure the config.php file.";
            $this->panic($sql);
        } else {
            return FALSE;
        }
    }

    /**
     * Searches for an accessible database containing the XMB settings table.
     *
     * @param string $tablepre The settings table name prefix.
     * @return bool
     */
    function find_database($tablepre) {
        $dbs = mysqli_query($this->link, 'SHOW DATABASES');
        while($db = $this->fetch_array($dbs)) {
            if ('information_schema' == $db['Database']) {
                continue;
            }
            $q = $this->query("SHOW TABLES FROM `{$db['Database']}`");

            while ($table = $this->fetch_array($q)) {
                if ($tablepre.'settings' == $table[0]) {
                    if (mysqli_select_db($this->link, $db['Database'])) {
                        $this->db = $db['Database'];
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
    }

    function error() {
        return mysqli_error($this->link);
    }

    function free_result($query) {
        set_error_handler($this->errcallb);
        $return = mysqli_free_result($query);
        restore_error_handler();
        return $return;
    }
	
    function fetch_array($query, $type=MYSQLI_ASSOC) {
        set_error_handler($this->errcallb);
		switch($type){
			case MYSQLI_NUM:
			case MYSQLI_BOTH:
				break;
			default:
				$type = MYSQLI_ASSOC;
				break;
		}
        $array = mysqli_fetch_array($query, $type);
        restore_error_handler();
        return $array;
    }

    function field_name($query, $field) {
        set_error_handler($this->errcallb);
        $fieldInfo = mysqli_fetch_field_direct($query, $field);
        $return = $fieldInfo->name;
        restore_error_handler();
        return $return;
    }

    /**
     * Returns the length of a field as specified in the database schema.
     *
     * @since 1.9.11.13
     * @param resource $query The result of a query.
     * @param int $field The field_offset starts at 0.
     * @return int
     */
    function field_len($query, $field) {
        set_error_handler($this->errcallb);
        $fieldInfo = mysqli_fetch_field_direct($query, $field);
        $return = $fieldInfo->length;
        restore_error_handler();
        return $return;
    }

    function panic(&$sql) {
        if (!headers_sent()) {
            header('HTTP/1.0 500 Internal Server Error');
        }

        // Check that we actually made a connection
        if ($this->link === FALSE) {
            $error = mysqli_error();
            $errno = mysqli_errno();
        } else {
            $error = mysqli_error($this->link);
            $errno = mysqli_errno($this->link);
        }

    	if (DEBUG And (!defined('X_SADMIN') Or X_SADMIN)) {
            require_once(ROOT.'include/validate.inc.php');
			echo '<pre>MySQL encountered the following error: '.cdataOut($error)."(errno = ".$errno.")\n<br />";
            if ($sql != '') {
                echo 'In the following query: <em>'.cdataOut($sql);
            }
            echo '</em></pre>';
        } else {
            echo "<pre>The system has failed to process your request. If you're an administrator, please set the DEBUG flag to true in config.php.</pre>";
    	}
        if (LOG_MYSQL_ERRORS) {
            $log = "MySQL encountered the following error:\n$error\n(errno = $errno)\n";
            if (strlen($sql) > 0) {
                if (1153 == $errno and strlen($sql) > 16000) {
                    $log .= "In the following query (log truncated):\n" . substr($sql, 0, 16000);
                } else {
                    $log .= "In the following query:\n$sql";
                }
            }
            if (!ini_get('log_errors')) {
                ini_set('log_errors', TRUE);
                ini_set('error_log', 'error_log');
            }
            error_log($log);
        }
        exit;
    }

    /**
     * Can be used to make any expression query-safe, but see next function.
     *
     * Example: $db->query('UPDATE a SET b = "'.$db->escape("Hello, my name is $rawinput").'"');
     *
     * @param string $rawstring
     * @return string
     */
    function escape($rawstring) {
        set_error_handler($this->errcallb);
        $return = mysqli_real_escape_string($this->link, $rawstring);
        restore_error_handler();
        return $return;
    }

    /**
     * Preferred for performance when escaping any string variable.
     *
     * Note this only works when the raw value can be discarded.
     *
     * Example:
     *  $db->escape_fast($rawinput);
     *  $db->query('UPDATE a SET b = "Hello, my name is '.$rawinput.'"');
     *
     * @since 1.9.11.12
     * @param string $sql Read/Write Variable
     */
    function escape_fast(&$sql) {
        set_error_handler($this->errcallb);
        $sql = mysqli_real_escape_string($this->link, $sql);
        restore_error_handler();
    }

    /**
     * DEPRECATED by version 1.9.11.12
     */
    function escape_var(&$rawstring) {
        set_error_handler($this->errcallb);
        $return = mysqli_real_escape_string($this->link, $rawstring);
        restore_error_handler();
        return $return;
    }

    function like_escape($rawstring) {
        set_error_handler($this->errcallb);
        $return = mysqli_real_escape_string($this->link, str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $rawstring));
        restore_error_handler();
        return $return;
    }

    function regexp_escape($rawstring) {
        set_error_handler($this->errcallb);
        $return = mysqli_real_escape_string($this->link, preg_quote($rawstring));
        restore_error_handler();
        return $return;
    }

    function mysqli_result($res,$row=0,$col=0){ 
        $numrows = mysqli_num_rows($res); 
        if ($numrows && $row <= ($numrows-1) && $row >=0){
            mysqli_data_seek($res,$row);
            $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
            if (isset($resrow[$col])){
                return $resrow[$col];
            }
        }
        return false;
    }
	
    /**
     * Executes a MySQL Query
     *
     * @param string $sql Unique MySQL query (multiple queries are not supported). The query string should not end with a semicolon.
     * @param bool $panic XMB will die and use dbstuff::panic() in case of any MySQL error unless this param is set to FALSE.
     * @return mixed Returns a MySQL resource or a bool, depending on the query type and error status.
     */
    function query($sql, $panic = TRUE) {
        $this->start_timer();
        $query = mysqli_query($this->link, $sql);
        if (FALSE === $query and $panic) {
            $this->panic($sql);
        }
        $this->querytimes[] = $this->stop_timer();
        $this->querynum++;
    	if (DEBUG) {
            if (LOG_MYSQL_ERRORS) {
                $this->last_id = mysqli_insert_id($this->link);
                $this->last_rows = mysqli_affected_rows($this->link);

                $query2 = mysqli_query($this->link, 'SHOW COUNT(*) WARNINGS');
                if (($warnings = mysqli_data_seek($query2, 0)) > 0) {
                    if (!ini_get('log_errors')) {
                        ini_set('log_errors', TRUE);
                        ini_set('error_log', 'error_log');
                    }
                    if (strlen($sql) > 16000) {
                        $output = "MySQL generated $warnings warnings in the following query (log truncated):\n" . substr($sql, 0, 16000) . "\n";
                    } else {
                        $output = "MySQL generated $warnings warnings in the following query:\n$sql\n";
                    }
                    $query3 = mysqli_query($this->link, 'SHOW WARNINGS');
                    while ($row = mysqli_fetch_array($query3, MYSQLI_ASSOC)) {
                        $output .= var_export($row, TRUE)."\n";
                    }
                    error_log($output);
                    mysqli_free_result($query3);
                }
                mysqli_free_result($query2);
            }
            if (!defined('X_SADMIN') or X_SADMIN) {
                $this->querylist[] = $sql;
            }
        }
        return $query;
    }

    /**
     * Sends a MySQL query without fetching the result rows.
     *
     * You cannot use mysql_num_rows() and mysql_data_seek() on a result set
     * returned from mysql_unbuffered_query(). You also have to fetch all result
     * rows from an unbuffered query before you can send a new query to MySQL.
     *
     * @param string $sql Unique MySQL query (multiple queries are not supported). The query string should not end with a semicolon.
     * @param bool $panic XMB will die and use dbstuff::panic() in case of any MySQL error unless this param is set to FALSE.
     * @return mixed Returns a MySQL resource or a bool, depending on the query type and error status.
     */
    function unbuffered_query($sql, $panic = TRUE) {
        $this->start_timer();
        $query = mysqli_query($this->link, $sql, MYSQLI_USE_RESULT);
        if (FALSE === $query and $panic) {
            $this->panic($sql);
        }
        $this->querynum++;
    	if (DEBUG and (!defined('X_SADMIN') or X_SADMIN)) {
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
        while($table = $this->fetch_array($q, MYSQLI_NUM)) {
            $array[] = $table[0];
        }
        return $array;
    }

    function result($query, $row, $field=NULL) {
        set_error_handler($this->errcallb);
		mysqli_data_seek($query, $row);
		if(!empty($field)) {
			while($finfo = mysqli_fetch_field($query)) {
				if($field == $finfo->name) {
					$f = mysqli_fetch_assoc($query);
					$query = $f[$field];
				}
			}
		} else {
			$f = mysqli_fetch_array($query);
			$query = $f[0];
		}
        restore_error_handler();
        return $query;
    }

    function num_rows($query) {
        set_error_handler($this->errcallb);
        $query = mysqli_num_rows($query);
        restore_error_handler();
        return $query;
    }

    function num_fields($query) {
        set_error_handler($this->errcallb);
        $return = mysqli_num_fields($query);
        restore_error_handler();
        return $return;
    }

    function insert_id() {
    	if (DEBUG and LOG_MYSQL_ERRORS) {
            $id = $this->last_id;
        } else {
            set_error_handler($this->errcallb);
            $id = mysqli_insert_id($this->link);
            restore_error_handler();
        }
        return $id;
    }

    function fetch_row($query) {
        set_error_handler($this->errcallb);
        $query = mysqli_fetch_row($query);
        restore_error_handler();
        return $query;
    }

    function data_seek($query, $row) {
        set_error_handler($this->errcallb);
        $return = mysqli_data_seek($query, $row);
        restore_error_handler();
        return $return;
    }

    function affected_rows() {
    	if (DEBUG and LOG_MYSQL_ERRORS) {
            $return = $this->last_rows;
        } else {
            set_error_handler($this->errcallb);
            $return = mysqli_affected_rows($this->link);
            restore_error_handler();
        }
        return $return;
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

    /**
     * Retrieve the MySQL server version number.
     *
     * @return string
     */
    function server_version(){
        return mysqli_get_server_info($this->link);
    }
}

/**
 * Proper error reporting for abstracted mysql_* function calls.
 *
 * @param int $errno
 * @param string $errstr
 * @author Robert Chapin (miqrogroove)
 */
function xmb_mysql_error($errno, $errstr) {
    $output = '';
    {
        $trace = debug_backtrace();
        if (isset($trace[2]['function'])) { // Catch MySQL error
            $depth = 2;
        } else { // Catch syntax error
            $depth = 1;
        }
        $functionname = $trace[$depth]['function'];
        $filename = $trace[$depth]['file'];
        $linenum = $trace[$depth]['line'];
        $output = "MySQL encountered the following error: $errstr in \$db->{$functionname}() called by {$filename} on line {$linenum}";
        unset($trace, $functionname, $filename, $linenum);
    }

    if (!headers_sent()) {
        header('HTTP/1.0 500 Internal Server Error');
    }
	if (DEBUG And (!defined('X_SADMIN') Or X_SADMIN)) {
        require_once(ROOT.'include/validate.inc.php');
		echo "<pre>".cdataOut($output)."</pre>";
    } else {
        echo "<pre>The system has failed to process your request. If you're an administrator, please set the DEBUG flag to true in config.php.</pre>";
	}
    if (LOG_MYSQL_ERRORS) {
        if (!ini_get('log_errors')) {
            ini_set('log_errors', TRUE);
            ini_set('error_log', 'error_log');
        }
        error_log($output);
    }
    exit;
}
?>
