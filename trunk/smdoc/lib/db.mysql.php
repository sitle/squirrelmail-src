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
			die('Could not select database.');
		}
	} else {
		die('Could not open database.');
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
		return false;
	}
}

/* turns a database datetime into a unix timestamp */
function dbdate2unixtime($datetime) {
	if (!isemptydbdate($datetime)) {
		return mktime(substr($datetime, 11, 2), substr($datetime, 14, 2), substr($datetime, 17, 2), substr($datetime, 5, 2), substr($datetime, 8, 2), substr($datetime, 0, 4));
	} else {
		return false;
	}
}

/* turns a unix timestamp into a database datetime */
function unixtime2dbdate($datetime) {
	return date( 'Y-m-d H:i:s', $datetime);
}

/* returns if a database datetime is empty */
function isemptydbdate($datetime) {
	if ($datetime == 0) {
		return true;
	} else {
		return false;
	}
}

/*** Generic DB Functions ***/

/* select */
function DBSelect(&$conn, $table, $joins, $fields, $conditions, $groups, $orders, $limit) {
	$SQLString = '';
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
			$SQLString .= ' WHERE';
			foreach ($conditions as $condition) {
				$SQLString .= ' '.$condition;
			}
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
		if (DEBUG) DBTrack($SQLString);
		$query = mysql_query($SQLString, $conn) or error('Database query failed');		
	} else {
		$query = false;
	}
	if ($query && mysql_affected_rows($conn) > 0) {
		return $query;
	} else {
		return false;
	}
}

/* insert */
function DBInsert(&$conn, $table, $fields) {
	$SQLString = '';
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
		if (DEBUG) DBTrack($SQLString);
		if ($query = mysql_query($SQLString, $conn)) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/* update */
function DBUpdate(&$conn, $table, $fields, $conditions) {
	$SQLString = '';
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
			$SQLString .= ' WHERE';
			foreach ($conditions as $condition) {
				$SQLString .= ' '.$condition;
			}
		}
		if (DEBUG) DBTrack($SQLString);
		if ($query = mysql_query($SQLString, $conn) && getAffectedRows() > 0) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/* delete */
function DBDelete(&$conn, $table, $conditions) {
	$SQLString = '';
	if ($table) {
		$SQLString = 'DELETE FROM '.$table;
		if ($conditions) {
			$SQLString .= ' WHERE';
			if (is_array($conditions)) {
				foreach ($conditions as $condition) {
					$SQLString .= ' '.$condition;
				}
			} else {
				$SQLString .= ' '.$conditions;
			}
		}
		if (DEBUG) DBTrack($SQLString);
		if (mysql_query($SQLString, $conn) or error('Database query failed')) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/* create table */
function DBCreateTable(&$conn, $name, $columns) {
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
		if (DEBUG) DBTrack($SQLString);
		if (mysql_query($SQLString)) {
			return true;
		} else {
			return false;
		}
	}	
	return false;
}

/* alter table */
function DBAlterTable(&$conn, $name, $columns) {
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
		if (DEBUG) DBTrack($SQLString);
		if (mysql_query($SQLString)) {
			return true;
		} else {
			return false;
		}
	}	
	return false;
}

?>