<?php
/* $Id: mysql4.php,v 1.11.2.8 2004/09/24 19:09:59 Tularis Exp $ */
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
    var $querynum   = 0;
    var $querylist  = array();
    var $querytimes = array();
    var $link       = '';
    var $duration   = 0;
    var $timer      = 0;

    function connect($dbhost="localhost", $dbuser, $dbpw, $dbname, $pconnect=0, $force_db=false) {
        $die = false;
        $this->link = mysqli_init();
        $this->link->real_connect($dbhost, $dbuser, $dbpw, $dbname, null, null, MYSQLI_CLIENT_COMPRESS) or ($die=true);
        $this->link->autocommit(true);

        if($die){
            $num = $this->link->errno;
            $msg = $this->link->error;

            echo '<h3>Database connection error!!!</h3>';

            echo 'A connection to the Database could not be established.<br />';
            echo 'Please check your username, password, database name and host.<br />';
            echo 'Also make sure <i>config.php</i> is rightly configured!<br /><br />';

            echo 'When connecting, the database returned:<br />';
            echo '<i><b>Error '.$num.': </b>'.$msg.'</i>';
            exit();
        }
        unset($GLOBALS['dbhost'], $GLOBALS['dbuser'], $GLOBALS['dbpw']);
        return true;
    }

    function error() {
        if(isset($this->link->error) && strlen($this->link->error) > 0) {
            return $this->link->error;
        } else {
            return false;
        }
    }

    function free_result(&$query) {
        return $query->free();
    }

    function fetch_array(&$result, $type=SQL_ASSOC) {
        $return = $result->fetch_array($type);
        if($return === NULL) {
            return false;
        } else {
            return $return;
        }
    }

    function field_name(&$query, $field) {
        $f = $query->fetch_fields();
        return $f[$field]->name;
    }

    function implicitError($sql, $overwriteErrorPerms=false) {
        if(($error = $this->error()) !== false) {
            if($overwriteErrorPerms) {
                return $error;
            } else {
                if(defined('DEBUG') && DEBUG==1) {
                    return 'MySQL encountered the following error: '.$error."\n<br />".'In the following query: <em>'.$sql.'</em>';
                } else {
                    return 'MySQL has encountered an unknown error. To find out the exact problem, please set the DEBUG flag to 1 in header.php.';
                }
            }
        } else {
            return 'oops';
        }
    }

    function query($sql, $overwriteErrorPerms=false) {
        $this->start_timer();

        $this->link->real_query($sql) or die($this->implicitError($sql, $overwriteErrorPerms));

        $this->querynum++;
        $this->querylist[] = $sql;
        $this->querytimes[] = $this->stop_timer();

        return $this->link->store_result();
    }

    function unbuffered_query($sql) {
        return $this->query($sql);
    }

    function select_db($dbname, $force=true) {
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
            }else{
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

    function result(&$query, $row, $field=0) {
        // row is not used at the moment, will add later
        $query->data_seek($row);
        $rows = $query->fetch_row();
        return $rows[$field];
    }

    function num_rows(&$query) {
        return $query->num_rows;
    }

    function num_fields(&$query) {
        return $query->field_count;
    }

    function insert_id() {
        return $this->link->insert_id;
    }

    function fetch_row(&$query) {
        return $query->fetch_row();
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
        $this->link->close();
        $this->link = '';
    }
}

define('SQL_NUM', MYSQLI_NUM);
define('SQL_BOTH', MYSQLI_BOTH);
define('SQL_ASSOC', MYSQLI_ASSOC);
?>
