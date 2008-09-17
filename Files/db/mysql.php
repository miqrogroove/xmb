<?php
/**
 * XMB 1.9.5 Nexus Final SP1
 * © 2007 John Briggs
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

class dbstuff {
    var $querynum   = 0;
    var $querylist  = array();
    var $querytimes = array();
    var $link       = '';
    var $db         = '';
    var $duration   = 0;
    var $timer      = 0;

    function connect($dbhost="localhost", $dbuser, $dbpw, $dbname, $pconnect=0, $force_db=false) {
        $die = false;

        if ($pconnect) {
            $this->link = @mysql_pconnect($dbhost, $dbuser, $dbpw) or ($die = true);
        } else {
            $this->link = @mysql_connect($dbhost, $dbuser, $dbpw) or ($die = true);
        }

        if ($die) {
            echo '<h3>Database connection error!</h3>';
            echo 'A connection to the Database could not be established.<br />';
            echo 'Please check your username, password, database name and host.<br />';
            echo 'Also make sure <i>config.php</i> is rightly configured!<br /><br />';
            if (defined('X_SADMIN') && X_SADMIN && defined('DEBUG') && DEBUG) {
                $num = mysql_errno();
                $msg = mysql_error();
                echo 'When connecting, the database returned:<br />';
                echo '<i><b>Error '.$num.': </b>'.$msg.'</i>';
            }
            exit();
        }

        unset($GLOBALS['dbhost'], $GLOBALS['dbuser'], $GLOBALS['dbpw']);

        return $this->select_db($dbname, $force_db);
    }

    function select_db($database, $force=true) {
        if ($force) {
            if (!mysql_select_db($database, $this->link)) {
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
        while ($db = mysql_fetch_array($dbs)) {
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

    function implicitError($sql, $overwriteErrorPerms=false) {
        if (($error = $this->error()) !== false) {
            if ($overwriteErrorPerms) {
                return $error;
            } else {
                if ((defined('X_SADMIN') && X_SADMIN && defined('DEBUG') && DEBUG) || (!defined('X_MEMBER') && !defined('X_GUEST'))) {
                    return 'MySQL encountered the following error: '.$error."\n<br />".'In the following query: <em>'.$sql.'</em>';
                } else {
                    return 'MySQL has encountered an unknown error. To find out the exact problem, please set the DEBUG flag to true in header.php.';
                }
            }
        } else {
            return 'oops';
        }
    }

    function query($sql, $overwriteErrorPerms=false) {
        $this->start_timer();
        $query = mysql_query($sql, $this->link) or die($this->implicitError($sql, $overwriteErrorPerms));
        $this->querynum++;
        $this->querylist[] = $sql;
        $this->querytimes[] = $this->stop_timer();
        return $query;
    }

    function unbuffered_query($sql) {
        $this->start_timer();
        $query = mysql_unbuffered_query($sql, $this->link) or die(mysql_error());
        $this->querynum++;
        $this->querylist[] = $sql;
        $this->querytimes[] = $this->stop_timer();
        return $query;
    }

    function fetch_tables($dbname = NULL) {
        if ($dbname == NULL) {
            $dbname = $this->db;
        }
        $this->select_db($dbname);

        $q = $this->query("SHOW TABLES");
        while ($table = $this->fetch_array($q, SQL_NUM)) {
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