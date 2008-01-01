<?php
/* $Id: database.interface.php,v 1.1.2.2 2007/01/22 09:59:04 Roxas Exp $ */
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
* Database Class interface
* 
* Don't use this in pre-PHP5 environements. You can use this interface as a design for new database
* connection classes.
*
*/

interface dbStruct {
    public function connect($dbhost="localhost", $dbuser, $dbpw, $dbname, $pconnect=0, $force_db=false);
    public function error();
    public function free_result($query);
    public function fetch_array($result);
    public function field_name($query, $field);
    public function query($sql, $overwriteErrorPerms=false);
    public function unbuffered_query($sql);
    public function select_db($dbname, $force=true);
    public function fetch_tables($dbname=NULL);
    public function result($query, $row, $field=0);
    public function num_rows($query);
    public function num_fields($query);
    public function insert_id();
    public function fetch_row($query);
    public function time($time=NULL);
    public function close();
}
?>