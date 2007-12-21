<?php
/* $Id: pdoMysql.php,v 1.2 2006/02/01 15:45:39 Tularis Exp $ */
/*
    XMB 1.10
    © 2001 - 2006 Aventure Media & The XMB Development Team
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
* MySQL experimental database access via the new PDO extension
*
* This class uses PHP's PDO and PDO-mysql extensions, which are available only in PHP 5.0.0 or higher
*/

require ROOT.'db/database.interface.php';

class dbstuff implements dbStruct {
    public $querynum	= 0;
    public $querylist	= array();
    public $querytimes	= array();
    protected $link		= '';
    public $duration	= 0;
    protected $timer	= 0;

    // to satisfy php, and make it not look for ANOTHER constructor function...
    function __construct() {

    }

    public function connect($dbhost="localhost", $dbuser, $dbpw, $dbname, $pconnect=0, $force_db=false) {
        $die = false;
        try {
        	$this->link = new PDO('mysql:host='.$dbhost.';dbname='.$dbname, $dbuser, $dbpw);
        } catch(PDOException $e) {
            echo '<h3>Database connection error!!!</h3>';

            echo 'A connection to the Database could not be established.<br />';
            echo 'Please check your username, password, database name and host.<br />';
            echo 'Also make sure <i>config.php</i> is rightly configured!<br /><br />';

            echo 'When connecting, the database returned:<br />';
            echo '<i><b>Error: </b>'.$e->getMessage().'</i>';
            exit();
        }
        $this->link->autocommit(true);

        unset($GLOBALS['dbhost'], $GLOBALS['dbuser'], $GLOBALS['dbpw']);
        return true;
    }

	private function parseErrorCode($code) {
		preg_match('#([0-9][0-9])([a-z][0-9][0-9])#i', $code, $ret);

		return array('class'=>$ret[1], 'subclass'=>$ret[2]);
	}

    public function error() {
        list($class, $subclass) = $this->parseErrorCode($this->link->errorCode());
		if((int) $class > 1) {
			$info = $this->link->errorInfo();
			return $info[2];
		} else {
			return null;
		}
    }

    public function free_result(&$query) {
        $query = null;
        unset($query);

        return true;
    }

    public function fetch_array($result, $type=SQL_ASSOC, $freeOnNull=true) {
        $return = $result->fetch($type);
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
        $f = $query->getColumnMeta($field);
        return $f[$field]->name;
    }

    private function implicitError($sql, $overwriteErrorPerms=false) {
        if (($error = $this->error()) !== false) {
            if ( $overwriteErrorPerms) {
                return $error.' (in: '.$sql.')';
            } else {
                if ( defined('X_SADMIN') && X_SADMIN && defined('DEBUG') && DEBUG ) {
                    return 'MySQL encountered the following error: '.$error."\n<br />".'In the following query: <em>'.$sql.'</em>';
                } else {
                    return 'MySQL has encountered an unknown error. To find out the exact problem, please set the DEBUG flag to true in header.php.';
                }
            }
        } else {
            return 'oops';
        }
    }

    public function query($sql, $overwriteErrorPerms=false) {
        $this->start_timer();

        $result = $this->link->query($sql) or die($this->implicitError($sql, $overwriteErrorPerms));

        $this->querynum++;
        $this->querylist[] = $sql;
        $this->querytimes[] = $this->stop_timer();

        return $result;
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
                return false;
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
        return $query->fetchColumn($field);
    }

    public function num_rows($query) {
        return $query->rowCount();
    }

    public function num_fields($query) {
        return $query->columnCount();
    }

    public function insert_id() {
        return $this->link->lastInsertId();
    }

    public function fetch_row($query) {
        return $this->fetch_array($query);
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
        $this->link = null;
        unset($this->link);
    }

    // implicitly close any open links at destruction
    function __destruct() {
        if($this->link !== '') {
            $this->close();
        }
    }
}

define('SQL_NUM', PDO::FETCH_NUM);
define('SQL_BOTH', PDO::FETCH_BOTH);
define('SQL_ASSOC', PDO::FETCH_ASSOC);