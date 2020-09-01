<?php
/**
 * eXtreme Message Board
 * XMB 1.9.12-alpha  Do not use this experimental software after 1 October 2020.
 *
 * Developed And Maintained By The XMB Group
 * Copyright (c) 2001-2020, The XMB Group
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
     * @param bool   $new_link Ignored in mysqli and always TRUE.
     */
    function connect($dbhost='localhost', $dbuser, $dbpw, $dbname, $pconnect=FALSE, $force_db=FALSE, $new_link=TRUE) {

        if ( $pconnect ) {
            $dbhost = "p:$dbhost";
        }

        if ( $force_db ) {
            $database = $dbname;
        } else {
            $database = '';
        }

        $this->link = @new mysqli( $dbhost, $dbuser, $dbpw, $database );

        if ( mysqli_connect_error() ) {
            echo '<h3>Database connection error!</h3>';
            echo 'A connection to the Database could not be established.<br />';
            echo 'Please check your username, password, database name and host.<br />';
            echo 'Also make sure <i>config.php</i> is rightly configured!<br /><br />';
            $sql = '';
            $this->panic($sql);
        }
        
        unset($GLOBALS['dbhost'], $GLOBALS['dbuser'], $GLOBALS['dbpw']);

        // Always force single byte mode so the PHP mysql client doesn't throw non-UTF input errors.
        // Available in PHP 5.0.5.
        if ( method_exists( $this->link, 'set_charset' ) ) {
            $result = $this->link->set_charset( 'latin1' );
            if (FALSE === $result) {
                echo '<h3>Database connection error!</h3>';
                echo 'The database connection could not be configured for XMB.<br />';
                echo 'Please ensure the mysqli_set_charset function is working.<br /><br />';
                $sql = '';
                $this->panic($sql);
            }
        }
        
        if ( $force_db ) {
            $this->db = $dbname;
            return true;
        } else {
            return $this->select_db( $dbname, $force_db );
        }
    }

    /**
     * Sets the name of the database to be used on this connection.
     *
     * @param string $database The full name of the MySQL database.
     * @param bool $force Optional. Specifies error mode. Dies if true.
     * @return bool TRUE on success, FALSE on failure with !$force.
     */
    function select_db($database, $force = TRUE) {
        if ( $this->link->select_db( $database ) ) {
            $this->db = $database;
            return TRUE;
        }
        if ($force) {
            $sql = "USE $database -- XMB couldn't find the database or didn't have permission! Please reconfigure the config.php file.";
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
        $dbs = $this->query('SHOW DATABASES');
        while($db = $this->fetch_array($dbs)) {
            if ('information_schema' == $db['Database']) {
                continue;
            }
            $q = $this->query("SHOW TABLES FROM `{$db['Database']}`");

            while ($table = $this->fetch_array($q)) {
                if ($tablepre.'settings' == $table[0]) {
                    if ( $this->select_db( $db['Database'], false ) ) {
                        $dbs->free();
                        $q->free();
                        return TRUE;
                    }
                }
            }
            $q->free();
        }
        $dbs->free();
        return FALSE;
    }

    function error() {
        return $this->link->error;
    }

    function free_result($query) {
        set_error_handler($this->errcallb);
        $query->free();
        restore_error_handler();
        return true;
    }
	
    function fetch_array($query, $type=SQL_ASSOC) {
        set_error_handler($this->errcallb);
        $array = $query->fetch_array($type);
        restore_error_handler();
        return $array;
    }

    function field_name($query, $field) {
        set_error_handler($this->errcallb);
        $return = $query->fetch_field_direct( $field )->name;
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
        $return = $query->fetch_field_direct( $field )->length;
        restore_error_handler();
        return $return;
    }

    function panic($sql) {
        if (!headers_sent()) {
            header('HTTP/1.0 500 Internal Server Error');
        }

        // Check that we actually made a connection
        if ( mysqli_connect_error() ) {
            $error = mysqli_connect_error();
            $errno = mysqli_connect_errno();
        } else {
            $error = $this->link->error;
            $errno = $this->link->errno;
        }

    	if (DEBUG && (!defined('X_SADMIN') || X_SADMIN)) {
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
                if ( ( 1153 == $errno || 2006 == $errno ) && strlen( $sql ) > 16000) {
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
        $return = $this->link->real_escape_string( $rawstring );
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
        $sql = $this->link->real_escape_string( $sql );
        restore_error_handler();
    }

    function like_escape($rawstring) {
        set_error_handler($this->errcallb);
        $return = $this->link->real_escape_string( str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $rawstring) );
        restore_error_handler();
        return $return;
    }

    function regexp_escape($rawstring) {
        set_error_handler($this->errcallb);
        $return = $this->link->real_escape_string( preg_quote( $rawstring ) );
        restore_error_handler();
        return $return;
    }

    /**
     * Executes a MySQL Query
     *
     * @param string $sql Unique MySQL query (multiple queries are not supported). The query string should not end with a semicolon.
     * @param bool $panic XMB will die and use dbstuff::panic() in case of any MySQL error unless this param is set to FALSE.
     * @return mixed Returns a MySQL resource or a bool, depending on the query type and error status.
     */
    function query($sql, $panic = true) {
        $this->start_timer();
        $query = $this->link->query( $sql );
        if ( false === $query ) {
			if ( $panic ) {
				$this->panic($sql);
			} else {
				return false;
			}
        }
        $this->querytimes[] = $this->stop_timer();
        $this->querynum++;
    	if (DEBUG) {
            if (LOG_MYSQL_ERRORS) {
                $this->last_id = $this->link->insert_id;
                $this->last_rows = $this->link->affected_rows;

                $query2 = $this->link->query( 'SHOW COUNT(*) WARNINGS' );
                if ( ( $warnings = $query2->fetch_row()[0] ) > 0 ) {
                    if (!ini_get('log_errors')) {
                        ini_set('log_errors', TRUE);
                        ini_set('error_log', 'error_log');
                    }
                    if (strlen($sql) > 16000) {
                        $output = "MySQL generated $warnings warnings in the following query (log truncated):\n" . substr($sql, 0, 16000) . "\n";
                    } else {
                        $output = "MySQL generated $warnings warnings in the following query:\n$sql\n";
                    }
                    $query3 = $this->link->query( 'SHOW WARNINGS' );
                    while ( $row = $query3->fetch_assoc() ) {
                        $output .= var_export($row, TRUE)."\n";
                    }
                    error_log($output);
                    $query3->free();
                }
                $query2->free();
            }
            if (!defined('X_SADMIN') || X_SADMIN) {
                $this->querylist[] = $sql;
            }
        }
        return $query;
    }

    /**
     * Sends a MySQL query without fetching the result rows.
     *
     * You cannot use mysqli_num_rows() and mysqli_data_seek() on a result set
     * returned from mysqli_use_result(). You also have to call
     * mysqli_free_result() before you can send a new query to MySQL.
     *
     * @param string $sql Unique MySQL query (multiple queries are not supported). The query string should not end with a semicolon.
     * @param bool $panic XMB will die and use dbstuff::panic() in case of any MySQL error unless this param is set to FALSE.
     * @return mixed Returns a MySQL resource or a bool, depending on the query type and error status.
     */
    function unbuffered_query($sql, $panic = TRUE) {
        $this->start_timer();
        $query = $this->link->query( $sql, MYSQLI_USE_RESULT );
        if (FALSE === $query && $panic) {
            $this->panic($sql);
        }
        $this->querynum++;
    	if (DEBUG && (!defined('X_SADMIN') || X_SADMIN)) {
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

        $array = array();
        $q = $this->query("SHOW TABLES");
        while( $table = $this->fetch_row( $q ) ) {
            $array[] = $table[0];
        }
        return $array;
    }

    /**
     * Retrieves the contents of one cell from a MySQL result set.
     *
     * @param resource $query
     * @param int      $row   The row number from the result that's being retrieved.
     * @param mixed    $field The name or offset of the field being retrieved.
     * @return string
     */
    function result( $query, $row, $field = 0 ) {
        set_error_handler($this->errcallb);
		$query->data_seek( $row );
        $return = $query->fetch_array()[$field];
        restore_error_handler();
        return $return;
    }

    function num_rows($query) {
        set_error_handler($this->errcallb);
        $query = $query->num_rows;
        restore_error_handler();
        return $query;
    }

    function num_fields($query) {
        set_error_handler($this->errcallb);
        $return = $query->field_count;
        restore_error_handler();
        return $return;
    }

    function insert_id() {
    	if (DEBUG && LOG_MYSQL_ERRORS) {
            $id = $this->last_id;
        } else {
            set_error_handler($this->errcallb);
            $id = $this->link->insert_id;
            restore_error_handler();
        }
        return $id;
    }

    function fetch_row($query) {
        set_error_handler($this->errcallb);
        $query = $query->fetch_row();
        restore_error_handler();
        return $query;
    }

    function data_seek($query, $row) {
        set_error_handler($this->errcallb);
        $return = $query->data_seek( $row );
        restore_error_handler();
        return $return;
    }

    function affected_rows() {
    	if (DEBUG && LOG_MYSQL_ERRORS) {
            $return = $this->last_rows;
        } else {
            set_error_handler($this->errcallb);
            $return = $this->link->affected_rows;
            restore_error_handler();
        }
        return $return;
    }

    /**
     * DEPRECATED by XMB 1.9.12
     *
     * dbstuff::time() was totally unrelated to the MySQL data types named TIME and TIMESTAMP.
     * Its purpose was ambiguous and usage seemed fully unnecessary.
     */
    function time($time=NULL) {
        trigger_error( 'dbstuff:time() is deprecated in this version of XMB', E_USER_DEPRECATED );
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
        return $this->link->server_info;
    }
}

/**
 * Proper error reporting for abstracted mysqli_* function calls.
 *
 * @param int $errno
 * @param string $errstr
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
        $output = "MySQLi encountered the following error: $errstr in \$db->{$functionname}() called by {$filename} on line {$linenum}";
        unset($trace, $functionname, $filename, $linenum);
    }

    if (!headers_sent()) {
        header('HTTP/1.0 500 Internal Server Error');
    }
	if (DEBUG && (!defined('X_SADMIN') || X_SADMIN)) {
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

return;