<?php
/* $Id: mysql4.php,v 1.1.2.6 2007/01/22 09:59:04 Roxas Exp $ */
/*
    © 2001 - 2007 Aventure Media & The XMB Development Team
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

/**
* MySQL 4.1 -> 5.x experimental database access
*
* For specific access to new MySQL database features, use this class.
* Must be instantiated before first use.
*
* This class uses PHP's MySQLi extension, which is available only in PHP 5.0.0 or higher
*/

require ROOT.'db/database.interface.php';

class dbstuff implements dbStruct {
    public $querynum    = 0;
    public $querylist   = array();
    public $querytimes  = array();
    protected $link     = '';
    public $duration    = 0;
    protected $timer    = 0;
    public $version     = '';

    // to satisfy php, and make it not look for ANOTHER constructor function...
    function __construct() {

    }

    public function connect($dbhost="localhost", $dbuser, $dbpw, $dbname, $pconnect=0, $force_db=false) {
        $die = false;
        $this->link = mysqli_init();
        $this->link->real_connect($dbhost, $dbuser, $dbpw, $dbname, null, null, MYSQLI_CLIENT_COMPRESS) or ($die=true);
        $this->link->autocommit(true);

        if ( $die) {
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

        $version = $this->link->server_version;
        $this->version = (int)($main = ($version/10000)).'.'.(int)($minor = ($version-$main > 0 ? ($version-$main/100) : 0).'.'.(int)(($version-$main-$minor)));
        return true;
    }

    public function error() {
        if (isset($this->link->error) && strlen($this->link->error) > 0) {
            return $this->link->error;
        } else {
            return false;
        }
    }

    public function free_result($query) {
        return @$query->free();
    }

    public function fetch_array($result, $type=SQL_ASSOC, $freeOnNull=true) {
        $return = $result->fetch_array($type);
        if ( $return === NULL) {
            if($freeOnNull) {
                $result->free();    // implicitly free it
            }
            return false;
        } else {
            return $return;
        }
    }

    public function field_name($query, $field) {
        $f = $query->fetch_fields();
        return $f[$field]->name;
    }

    private function implicitError($sql, $overwriteErrorPerms=false) {
        if (($error = $this->error()) !== false) {
            if ( $overwriteErrorPerms) {
                return $error;
            } else {
                if ( defined('X_SADMIN') && X_SADMIN && defined('DEBUG') && DEBUG ) {
                    return 'MySQL encountered the following error: '.$error."\n<br />".'In the following query: <em>'.$sql.'</em>';
                } else {
                    return 'MySQL has encountered an unknown error. To find out the exact problem, please set the DEBUG flag to true in config.php.';
                }
            }
        } else {
            return 'oops';
        }
    }

    public function query($sql, $overwriteErrorPerms=false) {
        $this->start_timer();

        $this->link->real_query($sql) or die($this->implicitError($sql, $overwriteErrorPerms));

        $this->querynum++;
        $this->querylist[] = $sql;
        $this->querytimes[] = $this->stop_timer();

        return $this->link->store_result();
    }

    public function unbuffered_query($sql) {
        return $this->query($sql);
    }

    public function select_db($dbname, $force=true) {
        $this->query("USE `$dbname`");
        if ( $force) {
            if ( $this->error()) {
                echo 'Could not locate database "'.$database.'". Please make sure it exists before trying again!';
                return false;
                exit();
            } else {
                $this->db = $dbname;
                return true;
            }
        } elseif ( $this->error()) {
            if ( $this->find_database($tablepre)) {
                echo "Using $this->db. Please reconfigure your config.php asap, XMB having to search for a database costs a lot of time and heavily slows down your board!";
                return true;
            }else{
                exit('Could not find any database containing the needed tables. Please reconfigure the config.php');
            }
        } else {
            return true;
        }
    }

    private function find_database($tablepre) {
        $q = $this->query("SHOW DATABASES");
        while($db = $this->fetch_array($dbs)) {
            $q = $this->query("SHOW TABLES FROM `$db[Database]`");
            if (!($this->num_rows($q) > 0)) {
                continue;
            }

            if (strpos($this->result($q, 0), $tablepre.'settings') !== false) {
                $this->select_db($db['Database']);
                $this->db = $db['Database'];
                break;
                return true;
            }else{
                continue;
            }
        }
    }


    public function fetch_tables($dbname = NULL) {
        if ( $dbname == NULL) {
            $dbname = $this->db;
        }
        $this->select_db($dbname);

        $q = $this->query("SHOW TABLES");
        while($table = $this->fetch_array($q, SQL_NUM)) {
            $array[] = $table[0];
        }
        return $array;
    }

    public function result($query, $row, $field=0) {
        // row is not used at the moment, will add later
        $query->data_seek($row);
        $rows = $query->fetch_row();
        return $rows[$field];
    }

    public function num_rows($query) {
        return $query->num_rows;
    }

    public function num_fields($query) {
        return $query->field_count;
    }

    public function insert_id() {
        return $this->link->insert_id;
    }

    public function fetch_row($query) {
        return $query->fetch_row();
    }

    public function time($time=NULL) {
        if ( $time === NULL) {
            $time = time();
        }
        return "LPAD('".$time."', '15', '0')";
    }

    private function start_timer() {
        $mtime = explode(" ", microtime());
        $this->timer = $mtime[1] + $mtime[0];

        return true;
    }

    private function stop_timer() {
        $mtime = explode(" ", microtime());
        $endtime = $mtime[1] + $mtime[0];

        $taken = ($endtime - $this->timer);
        $this->duration += $taken;
        $this->timer = 0;
        return $taken;
    }

    public function close() {
        $this->link->close();
        $this->link = '';
    }

    // implicitly close any open links at destruction
    function __destruct() {
        if($this->link !== '') {
            $this->close();
        }
    }
}

define('SQL_NUM', MYSQLI_NUM);
define('SQL_BOTH', MYSQLI_BOTH);
define('SQL_ASSOC', MYSQLI_ASSOC);
?>