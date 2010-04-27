<?php
/* $Id: upgrade.lib.php,v 1.3 2006/01/22 23:28:52 Tularis Exp $ */
/*
    XMB 1.x Upgrade Utility
    © 2001 - 2005 Aventure Media & The XMB Developement Team
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

class Upgrade {
    var $db;
    var $tablepre;
	var $tables;
    var $tc;

    var $xmb_tables = array('attachments', 'banned', 'buddys', 'favorites', 'forums',
						 'logs', 'members', 'posts', 'ranks', 'restricted', 'settings',
						 'smilies', 'templates', 'themes', 'threads', 'u2u', 'whosonline','words');

    function Upgrade(&$db, $file='', $tablepre) {
        if($file == '') {
            return null;
        }
        $this->__construct($db, $file, $tablepre);
        return 1;
    }

    function __construct(&$db, $file, $tablepre) {
        $this->db = &$db;
        $this->tablepre = $tablepre;

        $c = $this->fileGetContents($file) or die('Could not open '.$file);
        $this->tables = unserialize($c) or die('Syntax Error: Could not unserialize upgrade file');
    }

    function fileGetContents($filename) {
        $stream = @fopen($filename, 'r');
        if(!$stream) {
            return false;
        } else {
            $c = fread($stream, filesize($filename));
            fclose($stream);
            return $c;
        }
    }

    function getTablesByTablepre($tablepre=null) {

		if($tablepre === null) {
            $tablepre = $this->tablepre;
        }

        $tbl = array();

        $q = $this->db->query("SHOW TABLES LIKE '".str_replace('_', '\_', $tablepre)."%'");
        while($t = $this->db->fetch_array($q)) {
            $t = array_values($t);
            if ( in_array(str_replace($tablepre, '', $t[0]), $this->xmb_tables) ) {
				$tbl[] = $t[0];
			}
        }
        return $tbl;
    }

    function loadTables($tables) {
        foreach($tables as $k=>$t) {
            $this->loadTable($t);
        }
        return true;
    }


    function decode_keylen(& $keydata ) {
    // we take a piece of data that looks like this: (`foo` (num))

		// find the second (. Return if there isn't one

		$sp = strpos($keydata, "(", 1);
		if ( $sp === false ) {
			return '';
		}

		$keylen = str_replace(array('(', ')', ' ', ','), '', substr($keydata, $sp));
		$keydata = str_replace(array('(', ')', ' ', '`', ','), '', substr($keydata, 0, $sp));
		return $keylen;
    }

    function loadTable($table) {
        $q = $this->db->query("SHOW CREATE TABLE `$table`");
        $tbl = $this->db->fetch_array($q);
        $tbl = $tbl['Create Table'];

        // now, to extract all of this and save it :|
        $tbl = explode("\n", $tbl);
        $ct = count($tbl);
        unset($tbl[0]); // CREATE TABLE `table` (
        unset($tbl[$ct-1]); // TYPE = MyISAM

        $cols = array();
        $indices = array();

        foreach($tbl as $line=>$data) {
            $data = trim($data);
            if(strpos($data, 'PRIMARY KEY') !== 0 && strpos($data, 'KEY') !== 0) {
                // we have a column
                if(strpos($data, '`') === 0) {
                    // we have a col for sure
                    preg_match_all('#`([A-Za-z0-9_]+)` (VARCHAR|TINYINT|TEXT|DATE|SMALLINT|MEDIUMINT|INT|BIGINT|FLOAT|DOUBLE|DECIMAL|DATETIME|TIMESTAMP|TIME|YEAR|CHAR|TINYBLOB|TINYTEXT|BLOB|MEDIUMBLOB|MEDIUMTEXT|LONGBLOB|LONGTEXT|ENUM|SET){1}(\([\d]+\)|\([\'\d\w,\W]+\))?[\s]?(UNSIGNED ZEROFILL|UNSIGNED|BINARY)?[\s]?(NOT NULL|NULL)?[\s]?(default \'?(.*)\'?)?(auto_increment)?[\s]?(PRIMARY KEY|KEY)?#i', $data, $d);
                    /*
                    [1][0] = name
                    [2][0] = type
                    [3][0] = length
                    [4][0] = unsigned/signed/zerofill
                    [5][0] = null/not null
                    [6][0] = full default
                    [7][0] = default value (doesn't always work)
                    [8][0] = extra (auto_increment)
                    [9][0] = optional keys
                    */

                    if(substr($d[7][0], -1) == ',') {
                        $d[7][0] = substr($d[7][0], 1, -1); // strip quotes
                    }
                    $col['name']    = $d[1][0];
                    $col['type']    = $d[2][0].$d[3][0];
                    $col['null']    = $d[5][0];
                    $col['default'] = trim(substr($d[6][0], strlen('default')));
                    $col['extra']   = $d[8][0];
                    $col['keys']    = $d[9][0];

                    if(substr($col['default'], -1) == ',') {
                        $col['default'] = substr($col['default'], 0, -1);
                    }

                    $cols[] = $col;
                }
            } else {
				unset($index);		unset($d);
				$index = array();	$d = array();

                if(strpos(trim($data), 'PRIMARY KEY') === 0) {
                    // primary key :)

					$d = str_replace('  ', ' ', trim($data)); // XMB Issue #373
					$d = explode(' ', $d);

					if ( strpos($d[2], "))") ) {
						$index['keylen'] = $this->decode_keylen($d[2]);
					} else {
						$index['keylen'] = '';
						$d[2] = str_replace(array('(', ')', ' ', '`', ','), '', $d[2]);
					}

					$index['type'] = 'PRIMARY KEY';
                    $index['field'] = $d[2];
                    $index['name'] = '';
					$indices[] = $index;
                } elseif (strpos($data, 'KEY') !== false) {  // not primary
					// detect if index has a length ie KEY name ( field ( len ) )
					$d = explode(' ', trim($data));
					if ( strpos($d[2], "))") ) {
						$index['keylen'] = $this->decode_keylen($d[2]);
					} else {
						$index['keylen'] = '';
						$d[2] = str_replace(array('(', ')', ' ', '`', ','), '', $d[2]);
					}

                    $index['type'] = 'KEY';
                    $index['name'] = str_replace(array('(', ')', ' ', '`', ','), '', $d[1]);
                    $index['field'] = $d[2];
                    $indices[] = $index;
                }
            }
        }
        $table = str_replace($this->tablepre, '', $table);
        $this->tc[$table]['cols'] = $cols;
        $this->tc[$table]['indices'] = $indices;
    }

    function getMissingTables() {
        if(!isset($this->tc)) {
            $this->error('Load Tables first (Upgrade::loadTables())');
        }
        if(!isset($this->tables)) {
            $this->error('FATAL: Missing Upgrade-file');
        }
        $tc = array_keys($this->tc);
        $ts = array_keys($this->tables);

        $missing = array_diff($tc, $ts);
        $overhead = array_diff($ts, $tc);

        return array('+'=>$missing, '-'=>$overhead);
    }

    function createTableQueryByTablename($tbl) {
        $table = $this->tables[$tbl];
        $parts = array();

        foreach($table['cols'] as $col) {
            $p = array();
            $p[] = '`'.$col['name'].'`';
            $p[] = $col['type'];
            $p[] = $col['null'];
            if($col['default'] != '') {
                if($col['default'] == 'NULL') {
                    $p[] = 'default null';
                } else {
                    $p[] = 'default '.$col['default'];
                }
            }
            if($col['extra'] != '') {
                $p[] = $col['extra'];
            }
            if($col['keys'] != '') {
                $p[] = $col['keys'];
            }
            $parts[] = implode(' ', $p);
        }
        foreach($table['indices'] as $index) {
             if($index['type'] == 'KEY') {
				$keylen = $index['keylen'];
				if ( is_numeric($keylen) && $keylen > 0 ) {
					$parts[] = 'KEY `'.$index['name'].'` (`'.$index['field'].'` ('.$keylen.') )';
				} else {
					$parts[] = 'KEY `'.$index['name'].'` (`'.$index['field'].'`)';
				}
             } elseif($index['type'] == 'PRIMARY KEY') {
             	$keylen = $index['keylen'];
				if ( is_numeric($keylen) && $keylen > 0 ) {
					$parts[] =  'PRIMARY KEY (`'.$index['field'].'` ('.$keylen.') )';
				} else {
					$parts[] =  'PRIMARY KEY (`'.$index['field'].'`)';
				}
             }
        }
        $part = 'CREATE TABLE `'.$this->tablepre.$tbl.'` ('."\n";
        $part .= implode(",\n", $parts);
        $part .= "\n) TYPE=MyISAM;";

        return $part;
    }


    function getColsByTable($table) {
        if(!isset($this->tc[$table])) {
            $this->loadTable($this->tablepre.$table);
        }
        return $this->tc[$table]['cols'];
    }

    function getIndicesByTable($table) {
        if(!isset($this->tc[$table])) {
            $this->loadTable($table);
        }
        return $this->tc[$table]['indices'];
    }

    function makeDiff($table) {
        if(!isset($this->tc[$table])) {
            $this->error('Could not allocate table, please Load the Tables first');
        } elseif(!isset($this->tables[$table])) {
            // skip tables that don't belong in XMB (hacks!)
            return array('cols'=> array('+'=>null, '-'=>null), 'indices'=>array('+'=>null,'-'=>null));
        }

        $diff = array();

        $cols = $this->getColsByTable($table);

        foreach($cols as $c) {
            $col[] = implode('-', $c);
        }
        foreach($this->tables[$table]['cols'] as $c) {
            $mstr[] = implode('-', $c);
        }
        $p = array_diff($col, $mstr);
        $m = array_diff($mstr, $col);

        $diff['cols'] = array('+'=>$p, '-'=>$m);

        $mstr = array();

        $indices = $this->getIndicesByTable($table);
        $ind = array();
        $mstr = array();
        foreach($indices as $c) {
            $ind[] = implode('-', $c);
        }
        foreach($this->tables[$table]['indices'] as $c) {
            $mstr[] = implode('-', $c);
        }

        $p = array_diff($ind, $mstr);
        $m = array_diff($mstr, $ind);

        $diff['indices'] = array('+'=>$p, '-'=>$m);

        return $diff;
    }

    function makeLocationDiff($table) {
        if(!isset($this->tc[$table])) {
            $this->error('Could not allocate table, please Load the Tables first');
        } elseif(!isset($this->tables[$table])) {
            // skip tables that don't belong in XMB (hacks!)
            return array('cols'=> array('+'=>null, '-'=>null), 'indices'=>array('+'=>null,'-'=>null));
        }

        $diff = array();

        $cols = $this->getColsByTable($table);
        foreach($cols as $c) {
            $col[] = $c['name'];     // name
        }
        foreach($this->tables[$table]['cols'] as $c) {
            $mstr[] = $c['name'];    // name again
        }
        if($col !== $mstr) {
            // not the same locations it seems... :)
            // let's assume we DO have the right cols though
            // we return a list of the columns in the order we EXPECT them, so we can use them in a query
            return $mstr;
        } else {
            return null;
        }
        // indices are ok :) They can't be wrong
    }

    function createLocationChangeQuery($table, $temptbl, $def) {
		$defs = '`' . implode('`, `', $def) . '`';
		return "INSERT INTO `$table` ( $defs ) SELECT $defs FROM `$temptbl`";
    }

    function makeIntelligentDiff($d) {
        $newdiff = array();
        foreach($d as $t=>$diff) {
            if($diff['cols'] == null && $diff['indices'] == null) {
                continue;
            } else {
                if(isset($diff['indices']['-'])) {
                    foreach($diff['indices']['-'] as $min) {
                        $m = explode('-', $min);
                        $newdiff[$t]['indices']['add'][] = $m[2];
                    }
                }
                if(isset($diff['indices']['+'])) {
                    foreach($diff['indices']['+'] as $max) {
                        $m = explode('-', $max);
                        $newdiff[$t]['indices']['drop'][] = $m[2];
                    }
                }

                $drop = array();
                $add = array();

                if(isset($diff['cols']['-'])) {
                    foreach($diff['cols']['-'] as $min) {
                        $m = explode('-', $min);
                        $drop[] = $m[0];
                    }
                }
                if(isset($diff['cols']['+'])) {
                    foreach($diff['cols']['+'] as $max) {
                        $m = explode('-', $max);
                        $add[] = $m[0];
                    }
                }
                // only change drop/add the fields that are not in both, otherwise, we just need to MODIFY them
                $ad = array_diff($drop, $add);
                $dr = array_diff($add, $drop);

                foreach($dr as $k=>$name) {
                    // drop $name;
                    $newdiff[$t]['cols']['drop'][] = $name;
                }
                foreach($ad as $k=>$name) {
                    $newdiff[$t]['cols']['add'][] = $name;
                }

                $alter = array_diff($drop, $dr);    // this one should be exactly the same as the other one :)

                foreach($alter as $key=>$name) {
                    foreach($diff['cols']['+'] as $k=>$d) {
                        if(strpos($d, $name) === 0) {
                            // just change everything except name :P
                            $newdiff[$t]['cols']['alter'][] = $name;
                            break;
                        }
                    }
                }
            }
        }
        return $newdiff;
    }

    function getColInfoByName($table, $col) {
        foreach($this->tables[$table]['cols'] as $c) {
            if($c['name'] == $col) {
                return $c;
            }
         }
         $this->error('Could not locate column <i>'.$col.'</i> in table <i>'.$table.'</i>');
    }

    function getIndexInfoByName($table, $index) {
        foreach($this->tables[$table]['indices'] as $c) {
            if($c['name'] == $index) {
                return $c;
            }
         }
         return false;
    }

    function getIndexInfoByField($table, $index) {
        foreach($this->tables[$table]['indices'] as $c) {
            if($c['field'] == $index) {
                return $c;
            }
         }
         $this->error('Could not locate index <i>'.$index.'</i> in table <i>'.$table.'</i>');
         return false;
    }

    function getExistingIndexInfoByName($table, $index) {
		foreach($this->tc[$table]['indices'] as $c) {
            if($c['name'] == $index) {
		        return $c;
            }
         }
         return false;
    }


    function createQueryFromDiff($diff, $table) {
		$queries = array();
        $preface = "ALTER TABLE `" . $this->tablepre . $table . "` ";
        $ps = '';

        // indices will never be given here
        if(!isset($diff['cols'])) {
            $diff['cols'] = array();
        }
        if(!isset($diff['indices'])) {
			$diff['indices'] = array();
        }

        $query = '';

        if(isset($diff['indices']['drop'])) {
            foreach($diff['indices']['drop'] as $name) {
                // check that it is not a primary key!!
                foreach($this->tc[$table]['indices'] as $k=>$i) {
                    if($i['name'] == $name || $i['field'] == $name) {
                        $info = $i;
                        break;
                    }
                }
                if($info['type'] == 'PRIMARY KEY' ) {
                    $query .= 'DROP PRIMARY KEY, ';
                } else {
                    $query .= "DROP INDEX `".$name."`, ";
                }
			}
        }

        if ( $query != '' ) {
			if ( substr($query, -2) == ", " ) {
				$query = substr($query, 0, -2);
			}
			$queries[] = $preface . $query;
			$query = '';
		}

        if (isset($diff['cols']['add'])) {
            foreach($diff['cols']['add'] as $name) {
                // find the position of it first =/
                $info = $this->getColInfoByName($table, $name);
                $p = array();
                $p[] = '`'.$info['name'].'`';
                $p[] = $info['type'];
                $p[] = $info['null'];
                if($info['default'] != '') {
                    if($info['default'] == 'NULL') {
                        $p[] = 'default null';
                    } else {
                        $p[] = 'default '.$info['default'];
                    }
                }
                if($info['extra'] != '') {
                    $p[] = $info['extra'];
                }
                if(trim($info['keys']) != '') {
                    $p[] = $info['keys'];
                }
                if(isset($diff['indices']['add']) && in_array($name, $diff['indices']['add'])) {
                    if(($info = $this->getIndexInfoByName($table, $name)) === false) {
                        $info = $this->getIndexInfoByField($table, $name);
                    }
                    if($info['type'] == 'PRIMARY KEY') {
                        $ps = ', ADD PRIMARY KEY (`'.$info['field'].'`)';
                        unset($diff['indices']['add'][array_search($name, $diff['indices']['add'])]);
                    }
                }
                $parts = implode(' ', $p);
                if($this->tables[$table]['cols'][0]['name'] == $name) {
                    $query .= " ADD COLUMN ".$parts.' FIRST'.$ps . ", ";
                } else {
                    $c = count($this->tables[$table]['cols']);
                    for($i=0;$i<($c-1);$i++) {
                        if($this->tables[$table]['cols'][$i+1]['name'] == $name) {
                        $after = $this->tables[$table]['cols'][$i];
                        $query .= " ADD COLUMN ".$parts.' AFTER `'.$after['name'].'`'.$ps .", ";
                        break;
                        }
                    }
                }
            }
        }

		if ( $query != '' ) {
			if ( substr($query, -2) == ", " ) {
				$query = substr($query, 0, -2);
			}
			$queries[] = $preface . $query;
			$query = '';
		}

        if(isset($diff['cols']['alter'])) {
            foreach($diff['cols']['alter'] as $name) {
                $info = $this->getColInfoByName($table, $name);
                $p = array();
                $p[] = '`'.$info['name'].'`';
                $p[] = $info['type'];
                $p[] = $info['null'];
                if($info['default'] != '') {
                    if($info['default'] == 'NULL') {
                        $p[] = 'default null';
                    } else {
                        $p[] = 'default '.$info['default'];
                    }
                }
                if($info['extra'] != '') {
                    $p[] = $info['extra'];
                }
                if($info['keys'] != '') {
                    $p[] = $info['keys'];
                }
                $parts = implode(' ', $p);
                $query .= "MODIFY " . $parts . ", ";
            }
         }

		if ( $query != '' ) {
			if ( substr($query, -2) == ", " ) {
				$query = substr($query, 0, -2);
			}
			$queries[] = $preface . $query;
			$query = '';
		}

         if(isset($diff['cols']['drop'])) {
            foreach($diff['cols']['drop'] as $name) {
                $query .= "DROP COLUMN `".$name."`, ";
            }
		 }

		if ( $query != '' ) {
			if ( substr($query, -2) == ", " ) {
				$query = substr($query, 0, -2);
			}
			$queries[] = $preface . $query;
			$query = '';
		}

        if(isset($diff['indices']['add'])) {
             foreach($diff['indices']['add'] as $name) {
                 if(($info = $this->getIndexInfoByName($table, $name)) === false) {
                     $info = $this->getIndexInfoByField($table, $name);
                 }
                 if($info['type'] == 'PRIMARY KEY') {
					$keylen = $info['keylen'];
					if ( is_numeric($keylen) && $keylen > 0 ) {
						$query .= "ADD PRIMARY KEY (`".$info['field'].'` ('.$keylen.') ), ';
					} else {
						$query .= "ADD PRIMARY KEY (`".$info['field'].'`), ';
					}
                 } else {
					$keylen = $info['keylen'];
					if ( is_numeric($keylen) && $keylen > 0 ) {
						$query .= "ADD INDEX `".$info['field'].'` (`'.$info['name'].'` ('.$keylen.') ), ';
					} else {
						$query .= "ADD INDEX `".$info['field'].'` (`'.$info['name'].'`), ';
					}
                 }
             }
        }

		if ( $query != '' ) {
			if ( substr($query, -2) == ", " ) {
				$query = substr($query, 0, -2);
			}
			$queries[] = $preface . $query;
		}
		return $queries;
	}

    function createUpgradeFile($tablepre=null) {
        if($tablepre === null) {
            $tablepre = $this->tablepre;
        }
        foreach($this->tc as $key=>$val) {
            $tc[str_replace($tablepre, '', $key)] = $val;
        }
        return serialize($tc);
    }

    function error($msg) {
        exit($msg);
    }

    function findColumn(& $columns, $column) {
		foreach ( $columns as $col ) {
			if ( $col['name'] == $column )
				return $col;
		}
		return false;
    }

    function upgradeU2U() {
    	$this->db->query("DROP TABLE IF EXISTS `".$this->tablepre."u2u_new`");
		$this->db->query("CREATE TABLE `".$this->tablepre."u2u_new` (
					`u2uid` bigint(10) NOT NULL auto_increment,
					`msgto` varchar(32) NOT NULL default '',
					`msgfrom` varchar(32) NOT NULL default '',
					`type` set('incoming','outgoing','draft') NOT NULL default '',
					`owner` varchar(32) NOT NULL default '',
					`folder` varchar(32) NOT NULL default '',
					`subject` varchar(64) NOT NULL default '',
					`message` text NOT NULL,
					`dateline` int(10) NOT NULL default '0',
					`readstatus` set('yes','no') NOT NULL default '',
					`sentstatus` set('yes','no') NOT NULL default '',
					PRIMARY KEY  (`u2uid`),
					KEY `msgto` (`msgto`),
					KEY `msgfrom` (`msgfrom`),
					KEY `folder` (`folder`),
					KEY `readstatus` (`readstatus`),
					KEY `owner` (`owner`)
					) TYPE=MyISAM");

		$query = $this->db->query("SELECT * FROM `".$this->tablepre."u2u`");
		while ($u2u = $this->db->fetch_array($query)) {
			if ($u2u['folder'] == 'inbox') {
				$type = 'incoming';
				$owner = $u2u['msgto'];
			} elseif ($u2u['folder'] == 'outbox') {
				$type = 'outgoing';
				$owner = $u2u['msgfrom'];
			} else {
				$type = 'incoming';
				$owner = $u2u['msgfrom'];
			}

			if ( ! isset($u2u['readstatus']) || $u2u['readstatus'] == '') {
				$u2u['readstatus'] = 'no';
			}

			if ( ! isset($u2u['new']) || $u2u['new'] == '' ) {
				$u2u['new'] = 'yes';
			}

			$this->db->query("INSERT INTO `".$this->tablepre."u2u_new` VALUES('', '".$u2u['msgto']."', '".$u2u['msgfrom']."', '".$type."', '".$owner."', '".$u2u['folder']."', '".addslashes($u2u['subject'])."', '".addslashes($u2u['message'])."', '".$u2u['dateline']."', '".$u2u['readstatus']."', '".$u2u['new']."')");
		}

		$this->db->free_result($query);
		$this->db->query("DROP TABLE `".$this->tablepre."u2u`");
		$this->db->query("ALTER TABLE `".$this->tablepre."u2u_new` RENAME `".$this->tablepre."u2u`");
	}

    // this function gets rid of a corner case which the upgrade process has difficulty handling.
    function removeSid() {
		$tbl = 'settings';
		$cols = $this->getColsByTable($tbl);
		$sid = $this->findColumn($cols, 'sid');

		if ( $sid !== false ) {
			$this->db->query("ALTER TABLE `". $this->tablepre . $tbl ."` DROP COLUMN `sid`");
		}
	}

    function doU2U() {
		$tbl = 'u2u';
		$cols = $this->getColsByTable($tbl);
		$readStatus = $this->findColumn($cols, 'readstatus');
		$ownerCol = $this->findColumn($cols, 'owner');

		if ( $readStatus === false || $ownerCol === false || $readStatus['type'] == 'char(3)' ) {
			// 1.11 through 1.8 SP3
			$this->upgradeU2U();
			return true;
		} else {
			// 1.9.1 schema already.
			// let's do a quick check to see if the u2u table is okay and fix it if not
			$query = $this->db->query("SELECT u2uid, msgto, msgfrom, folder FROM `".$this->tablepre."u2u` where owner=''");

			if ( $this->db->num_rows($query) != 0 ) {
				while ($u2u = $this->db->fetch_array($query)) {
					if ($u2u['folder'] == 'inbox') {
						$type = 'incoming';
						$owner = $u2u['msgto'];
					} elseif ($u2u['folder'] == 'outbox') {
						$type = 'outgoing';
						$owner = $u2u['msgfrom'];
					} else {
						$type = 'incoming';
						$owner = $u2u['msgfrom'];
					}
					$this->db->query("UPDATE ".$this->tablepre."u2u SET type='".$type."', owner='".$owner."' WHERE u2uid = '".$u2u['u2uid']."'");
				}
			}
			$this->db->free_result($query);
		}

		return true;
    }

    function findThemeIDByName($themename) {
		$r = $this->db->query("SELECT themeid FROM " . $this->tablepre . "themes WHERE name='" . $themename . "'");
		if ( $this->db->num_rows($r) > 0 ) {
			$retval = $this->db->result($r, 0);
			$this->db->free_result($r);
			return $retval;
		} else {
			return false;
		}
    }

    function deleteThemeByName($themename) {
		$r = $this->db->query("SELECT themeid FROM " . $this->tablepre . "themes WHERE name='" . $themename . "'");
		if ( $this->db->num_rows($r) > 0 ) {
			$this->db->free_result($r);
			$this->db->query("DELETE FROM `". $this->tablepre ."themes` WHERE name='" . $themename . "'");
		}
    }

    function fixIndex() {
		$this->loadTable($this->tablepre . 'banned');

		if ( $this->getExistingIndexInfoByName('banned', 'ip1') !== false ) {
			$this->db->query("ALTER TABLE `" . $this->tablepre . "banned` DROP INDEX `ip1`");
		}
		if ( $this->getExistingIndexInfoByName('banned', 'ip4') !== false ) {
			$this->db->query("ALTER TABLE `" . $this->tablepre . "banned` DROP INDEX `ip4`");
		}

		$this->db->query("CREATE INDEX `ip1` ON `" . $this->tablepre . "banned` ( `ip1` ) ");
		$this->db->query("CREATE INDEX `ip4` ON `" . $this->tablepre . "banned` ( `ip4` ) ");

		$this->loadTable($this->tablepre . 'banned');
    }

    function fixPPP($mysqlver) {

		$tblmem = $this->tablepre . "members";
		$tblset = $this->tablepre . "settings";

		if ( ($mysqlver[0] == 4 && $mysqlver[1] > 3) || ($mysqlver[0] > 4) ) {
			$this->db->query("UPDATE `". $tblset. "`, `". $tblmem ."` SET `". $tblmem ."`.ppp=`". $tblset. "`.postperpage WHERE `". $tblmem ."`.ppp='0'");
			$this->db->query("UPDATE `". $tblset. "`, `". $tblmem ."` SET `". $tblmem ."`.tpp=`". $tblset. "`.topicperpage WHERE `". $tblmem ."`.tpp='0'");
		} else {
			$this->db->query("UPDATE `". $tblmem ."` SET ppp='30' WHERE ppp='0'");
			$this->db->query("UPDATE `". $tblmem ."` SET tpp='30' WHERE tpp='0'");
		}
    }

    function fixPostPerm() {
		$query = $this->db->query("SELECT fid, private, postperm, guestposting FROM `". $this->tablepre . "forums` WHERE NOT (type = 'group')");
		while ( $forum = $this->db->fetch_array($query) ) {
			$update = false;
			$pp = trim($forum['postperm']);
			if ( strlen($pp) > 0 && strpos($pp, '|') === false ) {
				$update = true;
				$forum['postperm'] = $pp . '|' . $pp;	// make the postperm the same for thread and reply
			}
			if ( $forum['guestposting'] != 'on' ) {
				$forum['guestposting'] = 'off';
				$update = true;
			}
			if ( $forum['private'] == '' ) {
				$forum['private'] = '1';	// by default, forums are not private.
				$update = true;
			}
			if ( $update ) {
				$this->db->query("UPDATE `". $this->tablepre . "forums` SET postperm='".$forum['postperm']."', guestposting='".$forum['guestposting']."', private='".$forum['private']."' WHERE fid='".$forum['fid']."'");
			}
		}
		$this->db->free_result($query);
    }

}

define('X_ALTER', 1);
define('X_DROP', 2);
define('X_ADD', 3);
?>
