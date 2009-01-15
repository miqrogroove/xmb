<?php

class dbstuff {
	var $querynum = 0;

	function connect($dbhost="localhost", $dbuser, $dbpw, $dbname, $pconnect=0) {
		if($pconnect) {
			mysql_pconnect($dbhost, $dbuser, $dbpw);
		} else {
			mysql_connect($dbhost, $dbuser, $dbpw);
		}
		mysql_select_db($dbname);
	}

	function fetch_array($query) {
		$query = mysql_fetch_array($query);
		return $query;
	}

	function query($sql) {
		$query = mysql_query($sql) or die(mysql_error());
		$this->querynum++;
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