<?php
/* $Id: cp_backup.php,v 1.1.2.1 2007/06/13 23:37:21 ajv Exp $ */
/*
    © 2001 - 2007 The XMB Development Team
    http://www.xmbforum.com

    Financial and other support 2007- iEntry Inc
    http://www.ientry.com

    Financial and other support 2002-2007 Aventure Media 
    http://www.aventure-media.co.uk

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

if (!defined('IN_CODE') && (defined('DEBUG') && DEBUG == false)) {
    exit ("Not allowed to run this file directly.");
}


function downloadDb() {
    global $SETTINGS, $time, $db, $dbname;
    
    $oToken->isValidToken();

    $time = time();
    header("Content-disposition: attachment; filename=" . urlencode($SETTINGS['bbname']) . "-$time.sql");
    header("Content-type: text/sql");
    header("Pragma: no-cache");
    header("Expires: 0");

    @ ob_implicit_flush(1);

    echo "-- --------------------------------------\n";
    echo "-- XMB 1.9.7 Database Dump function\n";
    echo "-- Created by: Tularis\n";
    echo "-- Version 1.00\n";
    echo "-- Copyright (c) 2001-2005 \n";
    echo "-- by The XMB Group &amp; aventure-media \n";
    echo "--\n";
    echo "--\n";
    echo "-- Start of Dump\n";
    echo "-- --------------------------------------\n\n";

    foreach ($db->fetch_tables($dbname) as $table) {
        echo "\n\n-- Dumping: $table\n";
        if (strpos($table, 'attachments') === false) {
            echo create_table_backup($table, true, true);
        } else {
            echo create_table_backup($table, false, true);
        }
    }

    echo "\n\n-- --------------------------------------\n";
    echo "-- End of Dump\n";
    echo "-- --------------------------------------\n\n";
    exit ();
}

/**
* create_table_backup() - Backup a named table
*
* Backup a table and stick it into a string.
*
* @param    $table        filename to read, should be a sanitized name
* @param    $full         (optional, true) Create a backup with each row fully specified (big!)
* @param    $dropIfExists (optional, false) Add a "Drop Table if Exists" line to each CREATE TABLE
* @return   will return a blank string for the most part, but can return the show create table if $full=false
*/
function create_table_backup($table, $full = true, $dropIfExists = false) {
    global $db;

    $content = '';

    $query = $db->query("SHOW CREATE TABLE $table");
    $create = $db->fetch_array($query, SQL_NUM);
    $create = $create[1];

    $create = ($dropIfExists == true) ? "DROP TABLE IF EXISTS $table;\n" . $create . ';' : $create;
    if (!$full) {
        return $create;
    }

    echo $create;

    $query = $db->query("SELECT * FROM $table");

    if ($db->num_rows($query) == 0)
        return '';

    $numFields = $db->num_fields($query);
    $cnt = 0;

    echo "\n\nINSERT INTO $table VALUES \n";

    while ($row = $db->fetch_row($query)) {
        $insert = "(";

        $cnt++;
        if ($cnt == 5000) {
            echo $content;
            $cnt = 0;
            $content = '';
        }

        for ($j = 0; $j < $numFields; $j++) {
            if (!isset ($row[$j])) {
                $insert .= "NULL,";
            }
            elseif ($row[$j] != "") {
                $insert .= "'" . $db->escape($row[$j]) . "',";
            } else {
                $insert .= "'',";
            }
        }
        $insert = ereg_replace(",$", "", $insert);
        $insert .= "),";
        $content .= $insert;
    }

    $content = ereg_replace(",$", ";", $content);
    echo $content;

    return '';
}

?>
