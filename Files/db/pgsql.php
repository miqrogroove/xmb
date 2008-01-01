<?php
/* $Id: pgsql.php,v 1.1 2004/11/03 18:21:10 tularis Exp $ */
/*
    XMB 1.9
    © 2001 - 2004 Aventure Media & The XMB Development Team
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

class dbstuff {
    var $errorCodes = array('result'=> array(0 => 'PGSQL_EMPTY_QUERY', 1 => 'PGSQL_COMMAND_OK', 2 => 'PGSQL_TUPLES_OK', 3 => 'PGSQL_COPY_TO', 4 => 'PGSQL_COPY_FROM', 5 => 'PGSQL_BAD_RESPONSE', 6 => 'PGSQL_NONFATAL_ERROR', 7 => 'PGSQL_FATAL_ERROR'));
    var $querynum   = 0;
    var $querylist  = array();
    var $querytimes = array();
    var $link       = '';
    var $duration   = 0;
    var $timer      = 0;

    function connect($dbhost="localhost", $dbuser, $dbpw, $dbname, $pconnect=0, $force_db=false) {
        $parts  = array();
        
        $parts[] = 'host='.$dbhost;
        $parts[] = 'user='.$dbuser;
        $parts[] = 'password='.$dbpw;
        $parts[] = 'dbname='.$dbname;
        
        $cstring = implode(' ', $parts);
        
        if($pconnect) {
            $this->link = pg_pconnect($cstring);
        } else {
            $this->link = pg_pconnect($cstring);
        }

        if(pg_connection_status() !== 0){
            echo '<h3>Database connection error!!!</h3>';

            echo 'A connection to the Database could not be established.<br />';
            echo 'Please check your username, password, database name and host.<br />';
            echo 'Also make sure <i>config.php</i> is correctly configured!<br /><br />';

            echo 'When connecting, the database returned:<br />';
            echo '<i><b>Error : </b>'.pg_last_error().'</i>';
            exit();
        }
        unset($GLOBALS['dbhost'], $GLOBALS['dbuser'], $GLOBALS['dbpw']);
        return true;
    }

    function error($resource=false) {
        if($resource === false) {
            if(($err = pg_last_error()) == '') {
                return false;
            } else {
                return $err;
            }
        } else {
            $err = pg_result_status();
            if($err == 1) {
                return false;
            } else {
                return $this->errorCodes['result'][$err];
            }
        }
    }

    function free_result($query) {
        return pg_free_result($query);
    }

    function fetch_array($result, $type=SQL_ASSOC) {
        return pg_fetch_array($result, null, $type);
    }

    function field_name($query, $field) {
        return pg_field_name($query, $field);
    }

    function implicitError($sql) {
        if($error = $this->error()) {
            if(defined(DEBUG) && DEBUG==1) {
                return 'PgSQL encountered the following error: '.$error."\n<br />".'In the following query: <em>'.$sql.'</em>';
            } else {
                return 'PgSQL has encountered an unknown error. If you are an administrator, please use the DEBUG flag to find out the exact problem.';
            }
        } else {
            return 'oops';
        }
    }

    function query($sql) {
        $this->start_timer();

        $result = pg_query($this->link, $sql) or die($this->implicitError($sql));

        $this->querynum++;
        $this->querylist[] = $sql;
        $this->querytimes[] = $this->stop_timer();

        return $result;
    }

    function unbuffered_query($sql) {
        return $this->query($sql);
    }

    function select_db($dbname, $force=true) {
        // help?
        $this->query("USE `$dbname`");
        if($force) {
            if($this->error()) {
                echo 'Could not locate database "'.$database.'". Please make sure it exists before trying again!';
                return false;
                exit();
            } else {
                $this->db = $dbname;
                return true;
            }
        } elseif($this->error()) {
            if($this->find_database($tablepre)){
                echo "Using $this->db. Please reconfigure your config.php asap, XMB having to search for a database costs a lot of time and heavily slows down your board!";
                return true;
            }else{
                echo 'Could not find any database containing the needed tables. Please reconfigure the config.php';
                return false;
                exit();
            }
        } else {
            return true;
        }
    }

    function find_database($tablepre){
    // help (2)?
        $q = $this->query("SHOW DATABASES");
        while($db = $this->fetch_array($dbs)){
            $q = $this->query("SHOW TABLES FROM `$db[Database]`");
            if(!($this->num_rows($q) > 0)){
                continue;
            }

            if(strpos($this->result($q, 0), $tablepre.'settings') !== false){
                $this->select_db($db['Database']);
                $this->db = $db['Database'];
                break;
                return true;
            } else {
                continue;
            }
        }
    }


    function fetch_tables($dbname = NULL) {
        if($dbname == NULL) {
            $dbname = $this->db;
        }
        $this->select_db($dbname);

        $q = $this->query("SHOW TABLES");
        while($table = $this->fetch_array($q, SQL_NUM)){
            $array[] = $table[0];
        }
        return $array;
    }

    function result($query, $row, $field=0) {
        return pg_fetch_result($query, $row, $field);
    }

    function num_rows($query) {
        return pg_num_rows($query);
    }

    function num_fields($query) {
        return pg_num_fields($query);
    }

    function insert_id() {
        //return $this->link->insert_id;
        return 0;
        // we'll see later on...
    }

    function fetch_row($query) {
        //return pg_fetch_row($query);
        return false
        // how aswell?
    }

    function time($time=NULL){
        if($time === NULL){
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

    function close() {
        pg_close($this->link)
        $this->link = null;
    }
}

define('SQL_NUM', PGSQL_NUM);
define('SQL_BOTH', PGSQL_BOTH);
define('SQL_ASSOC', PGSQL_ASSOC);
?>