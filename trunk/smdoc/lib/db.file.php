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
db.file.php
File System Connectivity
*/

/*
NOTE: This file is a hack. FOOWD was not designed to work from flat files
and as such I do not guarentee this to work particularly well. It is highly
recommended to use a database instead. This file will only read and write
FOOWD objects to files, it can not be used to write other data. Indexes above
the four access indexes will not work, and you can not use the group, join or
order inputs. The code tries to estimate the given SQL parameters to a
filename on the disk via the four access indexes, it may give bazaar and
unexpected results, this module is only recommended to be used to see FOOWD
up and running without having to install a database server. For anything
above just having a look, please use the database modules provided.
*/

/*** Settings ***/
define('DATABASE_DATE', 'Y-m-d H:i:s'); // date format for database
define('DATABASE_DATETIME_DATATYPE', 'DATETIME'); // name of database date time data type

/*** Core Database Functions ***/

/* open database connection */
function databaseOpen($host, $user, $pass, $name) {
	return $host;
}

/* close database connection */
function databaseClose(&$conn) {
	return TRUE;
}

/* select database */
function databaseSelect($dbname, &$conn) {
	return FALSE;
}

/* number of rows in returned recordset */
function returnedRows($query) {
	return count($query) - 1;
}

/* get the next record */
function getRecord(&$query) {
	$index = $query[0]++;
	$filename = $query[$index];
	if ($serializedObject = file_get_contents($filename)) {
		return array('object' => $serializedObject);
	}
	return FALSE;
}

/* get last identity value created */
function getIdentity(&$conn) {
	return FALSE;
}

/* get the field type of a field index */
function getFieldType($query, $index) {
	return FALSE;
}

/* get number of rows affected by last operation */
$DBFileAffectedRows = 0;
function getAffectedRows() {
	global $DBFileAffectedRows;
	return $DBFileAffectedRows;
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

/*** DB File Specific Functions ***/

function DBFile_doOrRecurse($conditions, &$filePattern) {
	$conjunction = array_shift($conditions);
	if ($conjunction == 'or' || $conjunction == 'OR') {
		$conjunction = 'OR';
	} elseif ($conjunction == 'and' || $conjunction == 'AND') {
		$conjunction = 'AND';
	}	elseif (count($conditions) == 0) {
		return array($conjunction);
	}	else {
		$conjunction = 'AND';
	}
	foreach ($conditions as $field => $condition) {
		if (is_array($condition)) {
			if ($conjunction == 'OR') {
				$filePattern[] = DBFile_doAndRecurse($condition);
			} else {
				DBFile_doOrRecurse($condition, $filePattern);
			}
		} else {
			if ($conjunction == 'AND') {
				$filePattern[] = $condition;
			} else {
				$filePattern[] = array($condition);
			}
		}
	}
	return $filePattern;
}

function DBFile_doAndRecurse($conditions) {
	$conjunction = array_shift($conditions);
	if ($conjunction == 'or' || $conjunction == 'OR') {
		$conjunction = 'OR';
	} elseif ($conjunction == 'and' || $conjunction == 'AND') {
		$conjunction = 'AND';
	}	elseif (count($conditions) == 0) {
		return array($conjunction);
	}	else {
		$conjunction = 'AND';
	}
	foreach ($conditions as $field => $condition) {
		if (is_array($condition)) {
			if ($conjunction == 'OR') {
				$filePattern[] = DBFile_doAndRecurse($condition);
			} else {
				DBFile_doOrRecurse($condition, $filePattern);
			}
		} else {
			if ($conjunction == 'AND') {
				$filePattern[] = $condition;
			} else {
				$filePattern[] = array($condition);
			}
		}
	}
	return $filePattern;
}

function DBFile_makeFilenames($filePattern, &$filenames, $name = '') {
	if (is_array($filePattern)) {
		$objectid = FALSE;
		$version = FALSE;
		$classid = FALSE;
		$workspaceid = FALSE;
		foreach ($filePattern as $node) {
			if (is_array($node)) {
				DBFile_makeFilenames($node, $filenames, $name);
			} else {
				$data = explode('=', $node);
				if (isset($data[1])) {
					if ($pos = strpos($data[0], '.')) {
						$field = substr($data[0], $pos + 1);
					} else {
						$field = $data[0];
					}
					switch (trim($field)) {
						case 'objectid':
							$objectid = trim($data[1]);
							break;
						case 'version':
							$version = trim($data[1]);
							break;
						case 'classid':
							$classid = trim($data[1]);
							break;
						case 'workspaceid':
							$workspaceid = trim($data[1]);
							break;
					}
				}
			}
		}
		
		if ($classid !== FALSE) $name .= 'classid'.$classid.'.'; else $name .= 'classid*.';
		if ($objectid !== FALSE) $name .= 'objectid'.$objectid.'.'; else $name .= 'objectid*.'; 
		if ($version !== FALSE) $name .= 'version'.$version.'.'; else $name .= 'version*.';
		if ($workspaceid !== FALSE) $name .= 'workspaceid'.$workspaceid.'.'; else $name .= 'workspaceid*.';
		
		if ($name != '') {
			$filenames[] = $name.'foowd';
//			echo $name.'foowd<br>';
		}
	} else {
		$filenames[] = '*.foowd';
	}
	return $filenames;
}

/*** Generic DB Functions ***/

/* select */
function DBSelect(&$conn, $table, $joins, $fields, $conditions, $groups, $orders, $limit) {

	global $DBFileAffectedRows;

// where
	if (isset($conditions) && $conditions != NULL) {
		$filePattern = DBFile_doOrRecurse($conditions, $filePattern);
		sort($filePattern);
	} else {
		$filePattern = NULL;
	}
	$filenames = DBFile_makeFilenames($filePattern, $filenames);

//echo '<pre>'; print_r($conditions); echo '</pre>';
//echo '<pre>'; print_r($filePattern); echo '</pre>';
//echo '<pre>'; print_r($filenames); echo '</pre>';
	if (DEBUG) DBTrack(show($filenames));
//echo '<hr />';
	
	$files = array();
	$foo = 0;
	foreach ($filenames as $filename) {
		if ($foo === $limit) break;
		$files = array_merge($files, glob($conn.$filename));
		$foo++;
	}
	
	$DBFileAffectedRows = count($files);

//echo '<pre>'; print_r($files); echo '</pre>';	
	rsort($files);
	array_unshift($files, 1);
//echo '<pre>'; print_r($files); echo '</pre>';
//echo $DBFileAffectedRows;

	if (isset($files) && is_array($files)) {
		return $files;
	} else {
		return FALSE;
	}

}

/* insert */
function DBInsert(&$conn, $table, $fields) {
	if (isset($fields['object'])) {
		$object = unserialize($fields['object']);
		$filename = $conn.'classid'.$object->classid.'.objectid'.$object->objectid.'.version'.$object->version.'.workspaceid'.$object->workspaceid.'.foowd';
		if ($fp = fopen($filename, 'wb')) {
			fwrite($fp, $fields['object']);
			return TRUE;
		} else {
			trigger_error('Could not write file to "'.$conn.'".', E_USER_ERROR);
		}
	}
	return FALSE;
}

/* update */
function DBUpdate(&$conn, $table, $fields, $conditions) {
	if (isset($fields['object'])) {
		$objectid = FALSE;
		$version = FALSE;
		$classid = FALSE;
		$workspaceid = FALSE;
		foreach ($conditions as $condition) {
			$sliced = explode(' ', $condition);
			switch ($sliced[0]) {
				case 'objectid':
					$objectid = $sliced[2];
					break;
				case 'version':
					$version = $sliced[2];
					break;
				case 'classid':
					$classid = $sliced[2];
					break;
				case 'workspaceid':
					$workspaceid = $sliced[2];
					break;
			}
		}
		if ($objectid && $version && $classid && $workspaceid) {
			$filename = $conn.'classid'.$classid.'.objectid'.$objectid.'.version'.$version.'.workspaceid'.$workspaceid.'.foowd';
			$object = unserialize($fields['object']);
			if ($fp = fopen($filename, 'wb')) {
				fwrite($fp, $fields['object']);
				return TRUE;
			} else {
				trigger_error('Could not write file to "'.$conn.'".', E_USER_ERROR);
			}
		}
	}
	return FALSE;
}

/* delete */
function DBDelete(&$conn, $table, $conditions) {
	$objectid = FALSE;
	$version = FALSE;
	$classid = FALSE;
	$workspaceid = FALSE;
	foreach ($conditions as $condition) {
		$sliced = explode(' ', $condition);
		switch ($sliced[0]) {
			case 'objectid':
				$objectid = $sliced[2];
				break;
			case 'version':
				$version = $sliced[2];
				break;
			case 'classid':
				$classid = $sliced[2];
				break;
			case 'workspaceid':
				$workspaceid = $sliced[2];
				break;
		}
	}
	if ($objectid && $version && $classid && $workspaceid) {
		$filename = $conn.'classid'.$classid.'.objectid'.$objectid.'.version'.$version.'.workspaceid'.$workspaceid.'.foowd';
		if (unlink($filename)) {
			return TRUE;
		}
	}
	return FALSE;
}

/* create table */
function DBCreateTable(&$conn, $name, $columns) {
	return TRUE;
}

/* alter table */
function DBAlterTable(&$conn, $name, $columns) {
	return TRUE;
}

?>