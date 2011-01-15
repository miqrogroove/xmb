<?php
/*
	XMB 1.8 Partagium
	© 2001 - 2003 Aventure Media & The XMB Developement Team
	http://www.aventure-media.co.uk
	http://www.xmbforum.com

	For license information, please read the license file which came with this edition of XMB
*/

class dbstuff {
	var $querynum = 0;
	var $querylist = '';

	function connect($dbhost="localhost", $dbuser, $dbpw, $dbname, $pconnect=0) {
		$die = false;
		
		if($pconnect) {
			@mysql_pconnect($dbhost, $dbuser, $dbpw) or ($die = true);
		} else {
			@mysql_connect($dbhost, $dbuser, $dbpw) or ($die = true);
		}
		
		if($die){
			$num = mysql_errno();
			$msg = mysql_error();
			
			echo '<h3>Database connection error!!!</h3>';
			
			echo 'A connection to the Database could not be established.<br />';
			echo 'Please check your username, password, database name and host.<br />';
			echo 'Also make sure <i>config.php</i> is rightly configured!<br /><br />';
			
			echo 'When connecting, the database returned:<br />';
			echo '<i><b>Error '.$num.': </b>'.$msg.'</i>';
			exit();
		}
		mysql_select_db($dbname) or die(mysql_error());
	}

	function fetch_array($query, $type=MYSQL_ASSOC) {
		$query = mysql_fetch_array($query, $type);
		return $query;
	} 

	function query($sql) {
		$query = mysql_query($sql) or die(mysql_error());
		$this->querynum++;
		$this->querylist .= "$sql <br />";
		return $query;
	}

	function result($query, $row) {
		$query = mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	function insert_id() {
		$id = mysql_insert_id();
		return $id;
	}

	function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}
}
?>