<?php

/**
 * Persistor.inc.php
 *
 * Copyright (c) 2003 Marc Groot Koerkamp 
 * Copyright (c) 2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Persistor class.
 *
 * Author: Marc Groot Koerkamp (Sourceforce username: stekkel) 2003
 *
 * $Id$
 */

 /*
 
   This is an unfinished experiment, it never hit the php compiler so it could be full of bugs
 
 
 */
 

/*
INDEX: (OFFSET, INDEX MAXENTRIES/BLOCK,FIELDSTRUCTURE)
FIELDSTRUCTURE: (keyfield width field width ...)
OFFSET: ABSOLUTE = true, RELATIVE = false
PROPERTIES STATIC: PROPERTY VALUE PROPERTY VALUE
PROPERTIES DYNAMIC: (PROPERTY WIDTH PROPERTY WIDTH ....)

LAYOUT FILE:
statusline with INDEX and PROPERTIES\n
properties\n
index\n
\n
DATA

*/
define('I_OFFSET',0);
define('I_MAX_ENTRIES',1);
define('I_FIELDS',2);

/* define should be moved to Persistor.h */

define('SM_FT_CHAR',1);
define('SM_FT_VARCHAR',2);
define('SM_FT_INTEGER',3);
define('SM_FT_TIMESTAMP',4);
define('SM_FT_OBJECT',5);
define('SM_FT_BOOLEAN',6);
define('SM_FT_NUMERIC',7);
define('SM_FT_FLOAT',8);
define('SM_FT_SET',9);
define('SM_FT_SET_M',10);
define('SM_FT_BLOB',11);

class SMSQL {
	var $sDb = '',
		$sDbPath = '',
		$sBaseDir = '';

	function SMSQL($sUsername, $iHashLevel = 0, $sBaseDir)
		$this->sUser = $sUsername;
		$this->iHashLevel = $iHashLevel;
		/* Remove trailing slash from $sBaseDir if found */
		if (substr($sBaseDir, -1) != '/') {
			$sBaseDir .= '/';// substr($sBaseDir, 0, strlen($sBaseDir) - 1);
		}
		$this->sBaseDir = $sBaseDir;
	}

	function openDb($sDb) {
		$dir_hash_level = $this->hash_level;
		$sDbName = $this->sUser.'_'.$sDb;
		$this->sDbName = $sDbName;
		$this->DbPath = $this->_getHashedDir($DbName, $this->sBaseDir);
	}

	/*
	aDescriptor: array of fields
	fields : array
	aField[0]: fieldname
	aField[1]: fieldtype
	aField[2]: width / optional info
	aField[3]: indexname (not indexed if empty)

	fieldtype:
	SM_CHAR width = fixed
	SM_INTEGER width = x
	SM_TIMESTAMP  32 bit integer
	SM_OBJECT optional info = classname
	SM_BOOLEAN
	SM_NUMERIC width = array (width, decimals)
	SM_FLOAT width = 32
	SM_SET optional info = array of types
	SM_SET_M optional info array of types
	*/

	/*
	 * Something about index:
	 * If there is a primary key provided we create a primary index  which points to the block where the record is located
	 * If no primary key is provided we create our own primary key.
	 * If an index field is provided that is not a primary key field then we create a clustered index. Each record with a
	 * field that's equal to an existent clustered field is added to the block reserved for that value.
	 *

	*/


	function createTable($sTable, $aDescriptor, $aParams = array()) {
		if ($this->$this->sDbName) {
			include('Persistor_error.php');
			$this->errorHandler(SM_PERS_NO_DB);
			return false;
		}
		$sFilename = $this->sDbName.'.'.$sTable;
		if(!$rHandle = @fopen($sFilename, 'w')) {
			include('Persistor_error.php');
			$this->errorHandler(SM_PERS_CREATE_TABLE);
			return false;
		}
		$aInternalDescriptor = array();
		/* defaults */
		$aInternalDescriptor['SM_TABLE_GLOBALS']['BLOCKSIZE'] = 1024;
		$aInternalDescriptor['SM_TABLE_GLOBALS']['PRELOAD_FIELDS'] = array();
		$aInternalDescriptor['SM_TABLE_GLOBALS']['PRIMARY_KEY'] = '';
		$aInternalDescriptor['SM_TABLE_GLOBALS']['INDEX_FIELD'] = '';
		$aInternalDescriptor['SM_TABLE_GLOBALS']['AUTO_PACK_PERC'] = 60;

		/* map defined fieldtypes against stringformats documented in pack */
		/* Protected fieldnames: SM_TABLE_GLOBALS */
		foreach ($aDescriptor as $fieldname => $field) {
			if ($field == 'SM_TABLE_GLOBALS') {
				foreach($field as $tableGlobal => $value) {
					switch ($tableGlobal)
					{
					case 'BLOCKSIZE':
						if (is_integer($value) && $value>0) {
							$aInternalDescriptor['SM_TABLE_GLOBALS']['BLOCKSIZE'] = $value;
						}
						break;
					case 'PRELOAD_FIELDS':
						if (is_array($value) && count($value)>0) {
							$valid = true;
							foreach ($value as $fieldname) {
								if (!isset($aDesciptor[$fieldname]) {
									$valid = false;
								}
							}
							if ($valid) {
								$aInternalDescriptor['SM_TABLE_GLOBALS']['PRELOAD_FIEDS'] = $value;
							}
						} else {
						}
						break;
					case'AUTO_PACK_PERC':
						if (is_integer($value) && ($value>0 && $value<100)) {
							$aInternalDescriptor['SM_TABLE_GLOBALS']['AUTO_PACK_PERC'] = $value;
						} else {
						}
						break;
					case 'PRIMARY_KEY':
						if (is_string($value) && $aDescriptor[$value])) {
							$aInternalDescriptor['SM_TABLE_GLOBALS']['PRIMARY_KEY'] = $value;
						} else {
						}
						break;
					case 'INDEX_FIELD': /* for clustered index */
						if (is_string($value) && $aDescriptor[$value])) {
							$aInternalDescriptor['SM_TABLE_GLOBALS']['INDEX_FIELD'] = $value;
						} else {
						}
						break;
					}
					case 'TYPE':
						switch ($value)
						{
						case 'HEAP':
						case 'ORDERED':
							$aInternalDescriptor['SM_TABLE_GLOBALS']['INDEX_FIELD'] = $value;
							break;
						default:
							break;
						}
				}
			} else {
			switch ($field[1]) /* type */
			{
			case SM_FT_CHAR:
				$format ='a';
				if ($field[2] == '*') {
					$format .= '*';
				} else if ((int) $field[2] > 0 ) {
					$format .= (int) $field[2];
				} else {
					$format .= '*';
				}
				$aInternalDescriptor[$fieldname]['fmt'] = $format;
				$aInternalDescriptor[$fieldname]['type'] = $field[1];
				break;
			case SM_FT_OBJECT:
				$aInternalDescriptor[$fieldname]['fmt'] ='a*';
				$aInternalDescriptor[$fieldname]['type'] = $field[1];
				$aInternalDescriptor[$fieldname]['class'] = $field[2];
				break;
			case SM_FT_BOOLEAN:
				$aInternalDescriptor[$fieldname]['fmt'] ='a1';
				$aInternalDescriptor[$fieldname]['type'] = $field[1];
				break;
			case SM_FT_INTEGER:
			case SM_FT_TIMESTAMP:
				$aInternalDescriptor[$fieldname]['fmt'] = 'L';
				$aInternalDescriptor[$fieldname]['type'] = $field[1];
				break;
			case SM_FT_SHORTINT:
				$aInternalDescriptor[$fieldname]['fmt'] = 'S';
				$aInternalDescriptor[$fieldname]['type'] = $field[1];
				break;
			case SM_FT_CARDINAL:
				$aInternalDescriptor[$fieldname]['fmt'] = 'l';
				$aInternalDescriptor[$fieldname]['type'] = $field[1];
				break;
			case SM_FT_SHORTCARD:
				$aInternalDescriptor[$fieldname]['fmt'] = 's';
				$aInternalDescriptor[$fieldname]['type'] = $field[1];
				break;
			case SM_FT_SET:
				$iCnt = count($field[2]);
				if ($iCnt <17) {
					$aInternalDescriptor[$fieldname]['fmt'] = 'H1';
				} else { /* max set size = 16 bit */
					$aInternalDescriptor[$fieldname]['fmt'] = 'S';
				};
				$aInternalDescriptor[$fieldname]['type'] = $field[1];
				$aInternalDescriptor[$fieldname]['set'] = $field[2];
				break;
			case SM_FT_SET_M:
				$iCnt = count($field[2]);
				if ($iCnt <5) {
					$aInternalDescriptor[$fieldname]['fmt'] = 'H1';
				} else if ($iCnt <17) {
					$aInternalDescriptor[$fieldname]['fmt'] = 'S';
				} else if ($iCnt <33) {
					$aInternalDescriptor[$fieldname]['fmt'] = 'L';
				};
				$aInternalDescriptor[$fieldname]['type'] = $field[1];
				$aInternalDescriptor[$fieldname]['set'] = $field[2];
				break;
			case SM_FT_BLOB:

			default:
				break;
			/* float and numeric will follow, problem is the machine depent size */
			}
			}
		}
		$sData = serialize($aInternalDescriptor) . "\n" . serialize($aTuning) . "\n";
		if (!fwrite($rHandle, $sData)) {
			include('Persistor_error.php');
			$this->errorHandler(SM_PERS_INIT_TABLE);
			return false;
		}
		return true;
	}

	function _openTable($sTable) {
		/* open in binary mode. on unix platforms b is ignored */
		if(!$rHandle = @fopen($sTable, 'br+')) {
			include('Persistor_error.php');
			$this->errorHandler(SM_PERS_OPEN_TABLE);
			return false;
		}
		$this->Tables[$sTable]['HANDLE'] = $rHandle;
		$this->Tables[$sTable]['BLOCKSIZE'] = 1024;
		/* read Descriptor */
		$sTmp = $this->_readBlock($sTable,0);
		$aDescriptor = deserialise($sTmp);
		$this->Tables[$sTable];'DESCRIPTOR'] = $aDescriptor;
		$this->Tables[$sTable]['iOffset'] = ftell($rHandle);
		return true;
	}

	function appendRecord($sTable,$aRecord) {
		$aTable = $this->Tables[$sTable];
		$rFp = $aTable['HANDLE'];
		$aDescriptor = $aTable['DESCRIPTOR'];
		$sPackFormat = $sPackData '';
		$sKey = shift_array($aRecord);
		switch ($aTable['TYPE'])
		{
		case 'HEAP':
			/* set fp on eof */
			fseek($rFp,0,SEEK_END);
			$sRecord = '';
			$sPackDynFormat = '';
			$i = 0;
			foreach ($aDescriptor as $key => $value) {
				$sFormat = $value['fmt'];
				if ($value['dynamic']) {
					$sPackDynFormat .= $sFormat;
					if (!$aRecord[$i]) {
						switch ($sFormat{0})
						{
						}

					$aDynData[] = $aRecord[$i];
				} else {
					$sPackStatFormat .= $value['fmt'];
					$aStatData[] = $aRecord[$i];
				}
				++$i;
			}
			$sBinRecord = call_user_func_array('pack',array_unshift($aRecord,$sPackFormat));
			break;
		default:
			break;
		}
	}

	function _readBlock($sTable,$iNr) {
		$aTable = this->Tables[$sTable];
		$rFp = $aTable['HANDLE'];
		$iBlockSize = $aTable['BLOCKSIZE'];
		$iBlockPointer = $aTable[$iStart] + ($iNr * $iBlockSize);
		fseek($rFp,$iBlockPointer);
		$sBlock = fread($rFp,$iBlockSize);
		if (strlen($sBlock) != $iBlockSize) {
			return false;
		}
		$iBlockPointer = (unpack('l',substr($sBlock,-4)))[0];
		$sBlock = substr($sBlock,0,$iBlockSize-4);
		/* get linked Blocks */
		while ($iBlockPointer) {
			fseek($rFp,$iBlockPointer);
			$sBlockNext = fread($rFp,$iBlockSize);
			if (strlen($sBlockNext) != $iBlockSize) {
				return false;
			}
			$iBlockPointer = (unpack('l',substr($sBlockNext,-4)))[0];
			$sBlock .= substr($sBlockNext,0,$iBlockSize-4);
		}
		return $sBlock;
	}

	function _writeBlock($sTable,$iNr,$sData) {
		$aTable = this->Tables[$sTable];
		$rFp = $aTable['HANDLE'];
		$iBlockSize = $aTable['BLOCKSIZE'];
		$iBlockPointer = $aTable[$iStart] + ($iNr * $iBlockSize);
		fseek($rFp,$iBlockPointer);
		$ret = fwrite($sData,$iBlockSize);
		if ($ret !== -1) {
			return false;
		} else {
			return $ret;
		}
	}

	function sm_addRecord($aRecord) {
	}

	function sm_updateRecord($aRecord) {
	}

	function sm_deleteRecord($aRecord) {
	}



	/*
	  * readStatusLine
	  * The statusline contains information about:
	  *	-the index
	  *	-the structure of the stored data
	  *	-maintainance information
	  *
	  * number of elements in index block
	  * offset absolute / relative
	  * rewrite on n % waste
	  * rewrite on n adds
	  * structure:  key width otherfield_1 width otherfield_2 width
	  *
	  */

	function readIndex($rHandle, &$iSize) {
		$sIndex = '';
		while ($s != "\n") {
			$sIndex .= fgets($rHandle,1024);
		}
		$iSize = strlen($sIndex);
		$aIndexTmp = explode(';',$sIndex);
		unset($sIndex);
		$aIndex = array();
		foreach ($aIndexTmp as $v) {
			$aTmp = explode(' ',$v);
			$aIndex[$aTmp[0]] = array($aTmp[1],$aTmp[2]);
		}
		unset($aIndexTmp);
		return $aIndex;
	}

	function createIndex($aIndex, &$iSize) {
		$sIndex = '';
		foreach ($aIndex as $k => $v) {
			$sIndex .= $k. ' '.$v[0].' '.$v[1].';';
		}
		$sIndex = substr($sIndex,0,-1) ."\n\n";
		$iSize = strlen($sIndex);
		return $sIndex;
	}

	function writeIndex($sTable, $sUsername, $aIndex) {
		$filename = $this->_getHashedFile($sUsername, $this->db, $sUsername.'.'.$sTable);
		if(!$rHandle = @fopen($filename, 'r+')) {
			include_once(SM_PATH . 'functions/display_messages.php');
			logout_error( sprintf( _("Preference file, %s, could not be opened. Contact your system administrator to resolve this issue."), $filename.'.tmp') );
			exit;
		} else {
			$aIndexOld = $this->readIndex($rHandle, $iSize);
			/* set all entries to false */
			array_walk($aIndexOld, create_function('&$v,$k','$v[1] = "0";'));
			foreach ($aIndex as $k = $v) {
				$aIndexOld[$k][1] = "1";
			}
			$sIndex = $this->createIndex($aIndexOld,$iSizeNew);
			if ($iSizeNew != $iSize) {
				echo "Houston, we got a problem<BR>\n";
			} else {
				if (rewind($rHandle)) {
					fputs($rHandle,$sIndex);
				} else {
					echo "error rewind<BR>\n";
				}
			}
		}
	}

	function writeValues($sTable,$sUsername,$aValues) {
	}

	function saveValues($sTable) {
		global $prefs_cache;
		$filename = $this->_getHashedFile($username, $data_dir, "$username.pref");
		/* Open the file for writing, or else display an error to the user. */
		if(!$file = @fopen($filename.'.tmp', 'w')) {
			include_once(SM_PATH . 'functions/display_messages.php');
			logout_error( sprintf( _("Preference file, %s, could not be opened. Contact your system administrator to resolve this issue."), $filename.'.tmp') );
			exit;
		}
		foreach ($prefs_cache as $Key => $Value) {
			if (isset($Value)) {
				$tmpwrite = @fwrite($file, $Key . '=' . $Value . "\n");
				if ($tmpwrite == -1) {
					logout_error( sprintf( _("Preference file, %s, could not be written. Contact your system administrator to resolve this issue.") , $filename . '.tmp') );
					exit;
				}
			}
		}
		fclose($file);
		@copy($filename . '.tmp',$filename);
		@unlink($filename . '.tmp');
		chmod($filename, 0600);
	}

    /* internal functions to resolve directory locations */

	function getHashedFile($sDir, $sTableName, $hash_search = true) {
		$sLocation = "$sDir/$sTableName";

		/* Check for this file in the real hash directory. */
		if ($hash_search && !@file_exists($sLocation)) {
			/* First check the base directory, the most common location. */
			if (@file_exists($this->sBaseDir.'/'.$sTableName)) {
				rename($this->sBaseDir.'/'.$sTableName, $sLocation);
				/* Then check the full range of possible hash directories. */
			} else {
				$check_hash_dir = $this->sBaseDir;
				/* compute HashDirs */
				$hash = base_convert(crc32($this->sDbName), 10, 16);
				$aHashDirs = array();
				for ($h = 0; $h < 4; ++ $h) {
					$aHashDirs[] = substr($hash, $h, 1);
				}
				for ($h = 0; $h < 4; ++$h) {
					$check_hash_dir .= '/' . $aHashDirs[$h];
					if (@is_readable("$check_hash_dir/$sTableName")) {
						rename("$check_hash_dir/$sTableName", $sLocation);
						break;
					}
				}
			}
		}
		/* Return the full hashed datafile path. */
		return ($sLocation);
	}

	function _getHashedDir($sDbName, $sBaseDir) {
		$dir_hash_level = $this->iHashLevel;
		/* compute HashDirs */
		$hash = base_convert(crc32($sDbName), 10, 16);
		$aHashDirs = array();
		for ($h = 0; $h < 4; ++ $h) {
			$aHashDirs[] = substr($hash, $h, 1);
		}
		/* Make sure the full hash directory exists. */
		$real_hash_dir = $sBaseDir;
		for ($h = 0; $h < $dir_hash_level; ++$h) {
			$real_hash_dir .= $aHashDirs[$h] . '/';
			if (!@is_dir($real_hash_dir)) {
				if (!@mkdir($real_hash_dir, 0770)) {
					include('Persistor_error.php');
					$this->errorHandler(SM_PERS_CREATE_DB_HASH_DIR);
					return false;
					//$this->errorHandler(SM_
					//echo sprintf(_("Error creating directory %s."), $real_hash_dir) . '<br>' .
					//_("Could not create hashed directory structure!") . "<br>\n" .
					//_("Please contact your system administrator and report this error.") . "<br>\n";
					//exit;
				}
			}
		}
		/* And return that directory. */
		return ($real_hash_dir);
	}
}

?>
