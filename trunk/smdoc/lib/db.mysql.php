<?php
/*
Copyright 2003, Paul James

This file is part of the Framework for Object Orientated Web Development (Foowd).

Foowd is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Foowd is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foowd; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
db.mysql.php
MySQL Database Connectivity
*/

/*** Settings ***/
define('DATABASE_DATE', 'Y-m-d H:i:s'); // date format for database
define('DATABASE_DATETIME_DATATYPE', 'DATETIME'); // name of database date time data type

/*** Core Database Functions ***/

/* open database connection */
function databaseOpen($host, $user, $pass, $name) {
	if ($conn = mysql_connect($host, $user, $pass)) {
		if (databaseSelect($name, $conn)) {
			return $conn;
		} else {
			//trigger_error('Could not select database "'.htmlspecialchars($name).'".', E_USER_ERROR);
			if (execQuery('CREATE DATABASE '.$name, $conn) && databaseSelect($name, $conn)) {
				return $conn;
			} else {
				trigger_error('Could not select database "'.htmlspecialchars($name).'".', E_USER_ERROR);
			}
		}
	} else {
		trigger_error('Could not connect to host "'.htmlspecialchars($host).'".', E_USER_ERROR);
	}
}

/* close database connection */
function databaseClose(&$conn) {
	return mysql_close($conn);
}

/* select database */
function databaseSelect($dbname, &$conn) {
	return mysql_select_db($dbname, $conn);
}

/* execute database query */
function execQuery($SQLString, &$conn) {
	return mysql_query($SQLString, $conn);
}

/* number of rows in returned recordset */
function returnedRows(&$query) {
	return mysql_num_rows($query);
}

/* get the next record */
function getRecord($query) {
	return mysql_fetch_assoc($query);
}

/* get last identity value created */
function getIdentity(&$conn) {
	return mysql_insert_id($conn);
}

/* get the field type of a field index */
function getFieldType($query, $index) {
	return mysql_field_type($query, $index);
}

/* get number of rows affected by last operation */
function getAffectedRows() {
	return mysql_affected_rows();
}

/*** Datetime Functions ***/

/* turns a database datetime into a formatted date string */
function dbdate2string($datetime, $format = DATEFORMAT) {
	if ($datetime = dbdate2unixtime($datetime)) {
		return date($format, $datetime);
	} else {
		return FALSE;
	}
}

/* turns a database datetime into a unix timestamp */
function dbdate2unixtime($datetime) {
	if (!isemptydbdate($datetime)) {
		return mktime(substr($datetime, 11, 2), substr($datetime, 14, 2), substr($datetime, 17, 2), substr($datetime, 5, 2), substr($datetime, 8, 2), substr($datetime, 0, 4));
	} else {
		return FALSE;
	}
}

/* turns a unix timestamp into a database datetime */
function unixtime2dbdate($datetime) {
	return date( 'Y-m-d H:i:s', $datetime);
}

/* returns if a database datetime is empty */
function isemptydbdate($datetime) {
	if ($datetime == 0) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/*** DB MySQL Specific Functions ***/

function DBMySQL_doWhereRecurse($conditions) {
	$SQL = '(';
	$conjunction = array_shift($conditions);
	if ($conjunction == 'or' || $conjunction == 'OR') {
		$conjunction = 'OR';
	} elseif ($conjunction == 'and' || $conjunction == 'AND') {
		$conjunction = 'AND';
	}	elseif (count($conditions) == 0) {
		return $conjunction;
	}	else {
		$conjunction = 'AND';
	}
	foreach ($conditions as $condition) {
		if (is_array($condition)) {
			$SQL .= DBMySQL_doWhereRecurse($condition).' '.$conjunction.' ';
		} else {
			$SQL .= $condition.' '.$conjunction.' ';
		}
	}
	return substr($SQL, 0, -(strlen($conjunction) + 2)).')';
}

/*** Generic DB Functions ***/

/* select */
function DBSelect(&$foowd, $joins, $fields, $conditions, $groups, $orders, $limit) {
	$SQLString = '';
	$table = $foowd->dbtable;
	if ($table && $fields) {
		$SQLString = 'SELECT';
		foreach ($fields as $field) {
			$SQLString .= ' '.$field.',';
		}
		$SQLString = substr($SQLString, 0, -1);
		$SQLString .= ' FROM '.$table;
		if ($joins) {
			foreach ($joins as $table => $condition) {
				if (is_numeric($table)) {
					$SQLString .= ' '.$condition;
				} else {
					$SQLString .= ' '.$table.' ON '.$condition;
				}
			}
		}
		if ($conditions) {
			$SQLString .= ' WHERE ';
			$SQLString .= DBMySQL_doWhereRecurse($conditions);
		}
		if ($groups) {
			$SQLString .= ' GROUP BY';
			foreach ($groups as $group) {
				$SQLString .= ' '.$group.',';
			}
			$SQLString = substr($SQLString, 0, -1);
		}
		if ($orders) {
			$SQLString .= ' ORDER BY';
			foreach ($orders as $order) {
				$SQLString .= ' '.$order.',';
			}
			$SQLString = substr($SQLString, 0, -1);
		}
		if ($limit) {
			$SQLString .= ' LIMIT '.$limit;
		}
		if ($foowd->debug) $foowd->debugDBTrack($SQLString);
		//$query = execQuery($SQLString, $foowd->conn) or trigger_error('Database query failed', E_USER_ERROR);
		$query = execQuery($SQLString, $foowd->conn) or $query = makeTable($foowd, $SQLString);
	} else {
		$query = FALSE;
	}
	if ($query && mysql_affected_rows($foowd->conn) > 0) {
		return $query;
	} else {
		return FALSE;
	}
}

/* insert */
function DBInsert(&$foowd, $fields) {
	$SQLString = '';
	$table = $foowd->dbtable;
	if ($table && $fields) {
		$SQLString = 'INSERT INTO '.$table.'(';
		foreach ($fields as $key => $field) {
			$SQLString .= ' '.$key.',';
		}
		$SQLString = substr($SQLString, 0, -1);
		$SQLString .= ') VALUES (';
		foreach ($fields as $field) {
			if (is_string($field)) {
				$SQLString .= ' "'.addslashes($field).'",';
			} else {
				$SQLString .= ' '.$field.',';
			}
		}
		$SQLString = substr($SQLString, 0, -1);
		$SQLString .= ')';
		if ($foowd->debug) $foowd->debugDBTrack($SQLString);
		//if ($query = execQuery($SQLString, $foowd->conn)) {
		$query = execQuery($SQLString, $foowd->conn) or $query = makeTable($foowd, $SQLString);
		if ($query) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

/* update */
function DBUpdate(&$foowd, $fields, $conditions) {
	$SQLString = '';
	$table = $foowd->dbtable;
	if ($table && $fields) {
		$SQLString = 'UPDATE '.$table.' SET ';
		foreach ($fields as $key => $field) {
			if (is_int($field)) {
				$SQLString .= ' '.$key.' = '.$field.',';
			} elseif (is_string($field)) {
				$SQLString .= ' '.$key.' = "'.addslashes($field).'",';
			}
		}
		$SQLString = substr($SQLString, 0, -1);
		if ($conditions) {
			$SQLString .= ' WHERE ';
			$SQLString .= DBMySQL_doWhereRecurse($conditions);
		}
		if ($foowd->debug) $foowd->debugDBTrack($SQLString);
		//if ($query = execQuery($SQLString, $foowd->conn) && getAffectedRows() > 0) {
		$query = execQuery($SQLString, $foowd->conn) or $query = makeTable($foowd, $SQLString);
		if ($query && getAffectedRows() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

/* delete */
function DBDelete(&$foowd, $conditions) {
	$SQLString = '';
	$table = $foowd->dbtable;
	if ($table) {
		$SQLString = 'DELETE FROM '.$table;
		if ($conditions) {
			$SQLString .= ' WHERE ';
			$SQLString .= DBMySQL_doWhereRecurse($conditions);
		}
		if ($foowd->debug) $foowd->debugDBTrack($SQLString);
		if (execQuery($SQLString, $foowd->conn) or trigger_error('Database query failed', E_USER_ERROR)) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

/* create table */
function DBCreateTable(&$foowd, $columns) {
	$name = $foowd->dbtable;
	if (isset($columns) && is_array($columns)) {
		$SQLString = 'CREATE TABLE '.$name.' (';
		$PrimaryKeyString = '';
		foreach($columns as $column) {
			if ($column['name'] != '' && $column['type'] != '') {
				$SQLString .= $column['name'].' '.$column['type'];
				if (isset($column['notnull']) && $column['notnull'] == 'true') {
					$SQLString .= ' NOT NULL';
				}
				if (isset($column['default']) && is_numeric($column['default'])) {
					$SQLString .= ' DEFAULT '.$column['default'];
				} elseif (isset($column['default']) && $column['default'] != 'false' && $column['default'] != '') {
					$SQLString .= ' DEFAULT "'.$column['default'].'"';
				}
				if (isset($column['identity']) && $column['identity'] == 'true') {
					$SQLString .= ' AUTO_INCREMENT';
				}
				if (isset($column['primary']) && $column['primary']) {
					$PrimaryKeyString .= $column['name'].', ';
					if (!isset($column['notnull'])) {
						$SQLString .= ' NOT NULL';
					}
				}
				if (isset($column['index']) && $column['index'] != 'false' && $column['index'] != '') {
					$SQLString .= ' INDEX '.$column['index'];
				}
				$SQLString .= ', ';
			}
		}
		if ($PrimaryKeyString != '') {
			$PrimaryKeyString = 'PRIMARY KEY ('.substr($PrimaryKeyString, 0, -2).'), ';
			$SQLString .= $PrimaryKeyString;
		}
		$SQLString = substr($SQLString, 0, -2).')';
		if ($foowd->debug) $foowd->debugDBTrack($SQLString);
		if (execQuery($SQLString, $foowd->conn)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}	
	return FALSE;
}

/* alter table */
function DBAlterTable(&$foowd, $columns) {
	$name = $foowd->dbtable;
	if (isset($columns) && is_array($columns)) {
		$SQLString = 'ALTER TABLE '.$name.' ADD COLUMN (';
		$PrimaryKeyString = '';
		$IndexString = '';
		foreach($columns as $column) {
			if ($column['name'] != '' && $column['type'] != '') {
				$SQLString .= $column['name'].' '.$column['type'];
				if (isset($column['notnull']) && $column['notnull'] == 'true') {
					$SQLString .= ' NOT NULL';
				}
				if (isset($column['default']) && is_numeric($column['default'])) {
					$SQLString .= ' DEFAULT '.$column['default'];
				} elseif (isset($column['default']) && $column['default'] != 'false' && $column['default'] != '') {
					$SQLString .= ' DEFAULT "'.$column['default'].'"';
				}
				if (isset($column['identity']) && $column['identity'] == 'true') {
					$SQLString .= ' AUTO_INCREMENT';
				}
				if (isset($column['primary']) && $column['primary']) {
					$PrimaryKeyString .= $column['name'].', ';
					if (!isset($column['notnull'])) {
						$SQLString .= ' NOT NULL';
					}
				}
				if (isset($column['index']) && $column['index'] != 'false' && $column['index'] != '') {
					$IndexString .= ', ADD INDEX idx'.$name.$column['index'].' ('.$column['index'].')';
				}
				$SQLString .= ', ';
			}
		}
		if ($PrimaryKeyString != '') {
			$PrimaryKeyString = 'PRIMARY KEY ('.substr($PrimaryKeyString, 0, -2).'), ';
			$SQLString .= $PrimaryKeyString;
		}
		$SQLString = substr($SQLString, 0, -2);
		$SQLString .= ')';
		if ($IndexString != '') {
			$SQLString .= $IndexString;
		}
		if ($foowd->debug) $foowd->debugDBTrack($SQLString);
		if (execQuery($SQLString, $foowd->conn)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}	
	return FALSE;
}

/* make table */
// called on query failure due to missing table
function makeTable(&$foowd, $SQLString) {
	$createString = 'CREATE TABLE `'.$foowd->dbtable.'` (
	`objectid` int(11) NOT NULL default \'0\',
	`version` int(10) unsigned NOT NULL default \'1\',
	`classid` int(11) NOT NULL default \'0\',
	`workspaceid` int(11) NOT NULL default \'0\',
	`object` longblob,
	`title` varchar(255) NOT NULL default \'\',
	`updated` datetime NOT NULL default \'0000-00-00 00:00:00\',
	PRIMARY KEY (`objectid`,`version`,`classid`,`workspaceid`),
	KEY `idxtblObjectTitle`(`title`),
	KEY `idxtblObjectupdated`(`updated`),
	KEY `idxtblObjectObjectid`(`objectid`),
	KEY `idxtblObjectClassid`(`classid`),
	KEY `idxtblObjectVersion`(`version`),
	KEY `idxtblObjectWorkspaceid`(`workspaceid`)
	)';
	if (execQuery($createString, $foowd->conn)) {
		if ($foowd->debug) $foowd->debugDBTrack($createString);
		$welcomeInsert = 'INSERT INTO `tblobject` VALUES("936075699","1","1158898744","0","O:15:\"foowd_text_html\":15:{s:5:\"title\";s:7:\"Welcome\";s:8:\"objectid\";s:9:\"936075699\";s:7:\"version\";s:1:\"1\";s:7:\"classid\";s:10:\"1158898744\";s:11:\"workspaceid\";s:1:\"0\";s:7:\"created\";s:10:\"1050232200\";s:9:\"creatorid\";s:11:\"-1316331025\";s:11:\"creatorName\";s:4:\"Peej\";s:7:\"updated\";i:1053681659;s:9:\"updatorid\";s:11:\"-1316331025\";s:11:\"updatorName\";s:4:\"Peej\";s:11:\"permissions\";a:5:{s:5:\"admin\";s:4:\"Gods\";s:6:\"delete\";s:4:\"Gods\";s:5:\"clone\";s:4:\"Gods\";s:4:\"edit\";s:4:\"Gods\";s:11:\"permissions\";s:4:\"Gods\";}s:4:\"body\";s:181:\"<h1>Congratulations!</h1>\r\n\r\n<h2>If you can see this page then FOOWD is working, well done.</h2>\r\n\r\n<p>Please follow the instructions in the README file to help get you started.</p>\";s:8:\"evalCode\";s:1:\"0\";s:14:\"processInclude\";s:1:\"0\";}","Welcome","2003-05-23 10:20:59");';
		if (execQuery($welcomeInsert, $foowd->conn)) {
			if ($foowd->debug) $foowd->debugDBTrack($welcomeInsert);
			if ($foowd->debug) $foowd->debugDBTrack($SQLString);
			return execQuery($SQLString, $foowd->conn);
		}
	}
	return FALSE;
}

?>