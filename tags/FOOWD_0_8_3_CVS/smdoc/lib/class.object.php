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
class.object.php
Foowd base object class
*/

/**
The FOOWD base system class, this class is the base class for all other system
classes. It has member functions for saving objects to the storage medium,
administrating objects, etc. all the base FOOWD object functionality.
**/

/** METHOD PERMISSIONS **/
if (!defined('PERMISSION_FOOWD_OBJECT_CLASS_CREATE')) define('PERMISSION_FOOWD_OBJECT_CLASS_CREATE', 'Gods');
if (!defined('PERMISSION_FOOWD_OBJECT_OBJECT_ADMIN')) define('PERMISSION_FOOWD_OBJECT_OBJECT_ADMIN', 'Gods');
if (!defined('PERMISSION_FOOWD_OBJECT_OBJECT_REVERT')) define('PERMISSION_FOOWD_OBJECT_OBJECT_REVERT', 'Gods');
if (!defined('PERMISSION_FOOWD_OBJECT_OBJECT_DELETE')) define('PERMISSION_FOOWD_OBJECT_OBJECT_DELETE', 'Gods');
if (!defined('PERMISSION_FOOWD_OBJECT_OBJECT_CLONE')) define('PERMISSION_FOOWD_OBJECT_OBJECT_CLONE', 'Gods');
if (!defined('PERMISSION_FOOWD_OBJECT_OBJECT_PERMISSIONS')) define('PERMISSION_FOOWD_OBJECT_OBJECT_PERMISSIONS', 'Gods');

/** CLASS DESCRIPTOR **/
if (!defined('META_1407951304_CLASSNAME')) define('META_1407951304_CLASSNAME', 'foowd_object');
if (!defined('META_1407951304_DESCRIPTION')) define('META_1407951304_DESCRIPTION', 'Base Object');

/** CLASS DECLARATION **/
class foowd_object {

	var $foowd_vars_meta = array(); // object member variable meta array
	var $foowd_indexes = array(); // object index meta array
	var $foowd_original_access_vars = array(); // this objects original access variables, just incase they change before saving.
	
	var $title;
	var $objectid;
	var $version = 1;
	var $classid;
	var $workspaceid = 0;
	var $created, $creatorid, $creatorName;
	var $updated, $updatorid, $updatorName;
	var $permissions;

/*** CONSTRUCTOR ***/

	function foowd_object(
		&$foowd,
		$title = NULL,
		$viewGroup = NULL,
		$adminGroup = NULL,
		$deleteGroup = NULL,
		$allowDuplicateTitle = NULL
	) {
		$foowd->track('foowd_object->constructor');
	
		$this->__wakeup(); // init meta arrays

		$allowDuplicateTitle = getVarConstOrDefault($allowDuplicateTitle, 'ALLOW_DUPLICATE_TITLE', TRUE);
	
/* set object vars */

		$maxTitleLength = getRegexLength($this->foowd_vars_meta['title'], 32);
		if (strlen($title) > 0 &&	strlen($title) < $maxTitleLength && preg_match($this->foowd_vars_meta['title'], $title)) {
			$this->title = $title;
		} else {
			return FALSE;
		}

		if (isset($foowd->user)) {
			$this->workspaceid = $foowd->user->workspaceid;
		} else {
			$this->workspaceid = 0;
		}
		
		$this->classid = crc32(strtolower(get_class($this)));

// set objectid, loop incrementing id until unique id is found (just in case, crc32 is not collision proof)
		$this->objectid = crc32(strtolower($title));
		
// check objectid
		while (DBSelect($foowd, NULL, array('objectid'), array('AND', 'objectid = '.$this->objectid, 'classid = '.$this->classid, 'workspaceid = '.$this->workspaceid), NULL, NULL, 1)) {
			if (!$allowDuplicateTitle) {
				trigger_error('Could not create object, duplicate title "'.htmlspecialchars($title).'".');
				$this->objectid = 0;
				$foowd->track(); return FALSE;
			}
			$this->objectid++;
		}

// set user vars
		$this->creatorid = $foowd->user->objectid;
		$this->creatorName = $foowd->user->title;
		$this->created = time();
		$this->updatorid = $foowd->user->objectid;
		$this->updatorName = $foowd->user->title;
		$this->updated = time();

/* set method permissions */
		$className = get_class($this);
		$this->permissions['view'] = getPermission($className, 'view', 'object', $viewGroup);
		$this->permissions['history'] = getPermission($className, 'history', 'object');
		$this->permissions['admin'] = getPermission($className, 'admin', 'object', $adminGroup);
		$this->permissions['revert'] = getPermission($className, 'revert', 'object');
		$this->permissions['delete'] = getPermission($className, 'delete', 'object', $deleteGroup);
		$this->permissions['clone'] = getPermission($className, 'clone', 'object', $adminGroup);
		$this->permissions['xml'] = getPermission($className, 'xml', 'object');
		$this->permissions['permissions'] = getPermission($className, 'permissions', 'object');

		$foowd->track();
	}
	
/*** SERIALIZE FUNCTIONS ***/

	function __sleep() {
		$returnArray = get_object_vars($this);
		unset($returnArray['foowd_vars_meta']);
		unset($returnArray['foowd_indexes']);
		unset($returnArray['foowd_original_access_vars']);
		return array_keys($returnArray);
	}

	function __wakeup() {
/** MEMBER VAR METADATA **/
		$this->foowd_vars_meta['title'] = REGEX_TITLE;
		$this->foowd_vars_meta['objectid'] = REGEX_ID;
		$this->foowd_vars_meta['version'] = '/^[0-9]*$/';
		$this->foowd_vars_meta['classid'] = REGEX_ID;
		$this->foowd_vars_meta['workspaceid'] = REGEX_ID;
		$this->foowd_vars_meta['created'] = REGEX_DATETIME;
		$this->foowd_vars_meta['creatorid'] = REGEX_ID;
		$this->foowd_vars_meta['creatorName'] = REGEX_TITLE;
		$this->foowd_vars_meta['updated'] = REGEX_DATETIME;
		$this->foowd_vars_meta['updatorid'] = REGEX_ID;
		$this->foowd_vars_meta['updatorName'] = REGEX_TITLE;
		$this->foowd_vars_meta['permissions'] = REGEX_GROUP;
/** INDEX METADATA **/
		$this->foowd_indexes['objectid'] = 'INT NOT NULL';
		$this->foowd_indexes['version'] = 'INT UNSIGNED NOT NULL DEFAULT 1';
		$this->foowd_indexes['classid'] = 'INT NOT NULL DEFAULT 0';
		$this->foowd_indexes['workspaceid'] = 'INT NOT NULL DEFAULT 0';
		$this->foowd_indexes['title'] = 'VARCHAR('.getRegexLength($this->foowd_vars_meta['title'], 32).') NOT NULL';
		$this->foowd_indexes['updated'] = DATABASE_DATETIME_DATATYPE.' NOT NULL';
/** ORIGINAL ACCESS VARS **/
		$this->foowd_original_access_vars['objectid'] = $this->objectid;
		$this->foowd_original_access_vars['version'] = $this->version;
		$this->foowd_original_access_vars['classid'] = $this->classid;
		$this->foowd_original_access_vars['workspaceid'] = $this->workspaceid;
	}

/*** MEMBER FUNCTIONS ***/

	function getTitle() {
		return htmlspecialchars($this->title);
	}

/* set member variable */

	function set(&$foowd, $member, $value = NULL) {
		$foowd->track('foowd_object->set');
		if (isset($this->$member) || $this->$member == NULL) {
			if (isset($this->foowd_vars_meta[$member])) {
				if (is_array($this->foowd_vars_meta[$member])) { // multi-depth array
					$okay = TRUE;
					foreach ($value as $val) {
						if (is_array($val)) {
							if (!$this->setArray($val, $this->foowd_vars_meta[$member])) {
								$okay = FALSE;
							}
						}
					}
					if ($okay) $this->$member = $value;
					$foowd->track(); return $okay;
				} elseif (is_array($value)) { // single depth array
					$okay = TRUE;
					foreach ($value as $val) {
						if ($this->foowd_vars_meta[$member] == NULL || !preg_match($this->foowd_vars_meta[$member], $val)) {
							$okay = FALSE;
						}
					}
					if ($okay) $this->$member = $value;
					$foowd->track(); return $okay;
				} elseif ($this->foowd_vars_meta[$member] == 'binary') { // binary data
					$this->$member = $value;
					$foowd->track(); return TRUE;
				} else { // non-complex type
					if ($this->foowd_vars_meta[$member] == '' || $this->foowd_vars_meta[$member] == NULL || preg_match($this->foowd_vars_meta[$member], $value)) {
						$this->$member = $value;
						$foowd->track(); return TRUE;
					}
				}
			}
		}
		$foowd->track(); return FALSE;
	}
	
	function setArray($array, $regex) {
		$okay = TRUE;
		foreach ($array as $index => $val) {
			if (is_array($val)) {
				$okay = $this->setArray($index, $val, $regex[$index]);
			} elseif (
				$regex == NULL ||
				!preg_match($regex[$index], $val)
			) {
				$okay = FALSE;
			}
		}
		return $okay;
	}

/* save object */

	function save(&$foowd, $incrementVersion = TRUE, $doUpdate = TRUE) { // write object to database
		$foowd->track('foowd_object->save');

		if ($doUpdate) { // update values
			$this->updatorid = $foowd->user->objectid;
			$this->updatorName = $foowd->user->title;
			$this->updated = time();
		}
		
		if ($incrementVersion) { // create new version if told to
			$object = $foowd->fetchObject(array('objectid' => $this->foowd_original_access_vars['objectid'], 'classid' => $this->foowd_original_access_vars['classid'], 'workspaceid' => $this->foowd_original_access_vars['workspaceid']));
			if ($object) {
				$this->version = ++$object->version;
			}
			$this->foowd_original_access_vars['version'] = $this->version;
		}

// serialize object
		$serializedObj = serialize($this);
// create field array from object
		$fieldArray['object'] = $serializedObj;
		foreach ($this->foowd_indexes as $index => $definition) {
			if (isset($this->$index)) {
				if ($this->$index == FALSE) {
					$fieldArray[$index] = 0;
				} else {
					if (substr_count($definition, DATABASE_DATETIME_DATATYPE)) { // translate unixtime to db date format
						$fieldArray[$index] = date(DATABASE_DATE, $this->$index);
					} else {
						$fieldArray[$index] = $this->$index;
					}
				}
			}
		}
// set conditions
		$conditionArray = array(
			'AND',
			'objectid = '.$this->foowd_original_access_vars['objectid'],
			'version = '.$this->foowd_original_access_vars['version'],
			'classid = '.$this->foowd_original_access_vars['classid'],
			'workspaceid = '.$this->foowd_original_access_vars['workspaceid']
		);

		$exitValue = FALSE;
// try to update existing record
		if (DBUpdate($foowd, $fieldArray, $conditionArray)) {
			$exitValue = 1;
		} else {
// if fail, write new record
			if (DBInsert($foowd, $fieldArray)) {
				$exitValue = 2;
			} else {
// if fail, modify table to include indexes from class definition
				if ($query = DBSelect($foowd, NULL,
					array('*'),
					NULL,
					NULL,
					NULL,
					1)
				) {
					$record = getRecord($query);
					$missingFields = array();
					foreach ($fieldArray as $field => $value) {
						if (!isset($record[$field]) && $field != 'object') {
							$missingFields[] = array(
								'name' => $field,
								'type' => $this->foowd_indexes[$field],
								'index' => $field
							);
						}
					}
					if ($missingFields != NULL && DBAlterTable($foowd, $missingFields)) {
						if (DBUpdate($foowd, $fieldArray, $conditionArray)) {
							$exitValue = 3;
						} elseif (DBInsert($foowd, $fieldArray)) {
							$exitValue = 4;
						}
					}
				}
			}
		}
// tidy old archived versions
		if ($exitValue && $this->updated < time() - getConstOrDefault('TIDY_DELAY', 86400)) {
			$this->tidyArchive(&$foowd);
		}
		$foowd->track(); return $exitValue;
	}

/* delete object */

	function delete(&$foowd) { // remove all versions of an object from the database
		$foowd->track('foowd_object->delete');
		$conditionArray = array(
			'AND',
			'objectid = '.$this->objectid,
			'classid = '.$this->classid,
		);
		$conditionArray[] = 'workspaceid = '.$this->workspaceid;
		if (DBDelete($foowd, $conditionArray)) {
			$foowd->track(); return TRUE;
		} else {
			$foowd->track(); return FALSE;
		}
	}
	
/* tidy archive */
 
	function tidyArchive(&$foowd) { // clean up old archived versions	
		$foowd->track('foowd_object->tidyArchive');
		$conditionArray = array(
			'AND',
			'objectid = '.$this->objectid,
			'version < '.($this->version - getConstOrDefault('MINIMUM_NUMBER_OF_ARCHIVED_VERSIONS', 3)),
			'classid = '.$this->classid,
			'workspaceid = '.$this->workspaceid,
			'updated < "'.date(DATABASE_DATE, strtotime(getConstOrDefault('DESTROY_OLDER_THAN', '-1 Month'))).'"'
		);
		if (DBDelete($foowd, $conditionArray)) {
			$foowd->track(); return TRUE;
		} else {
			$foowd->track(); return FALSE;
		}
	}

/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		$foowd->track('foowd_object->class_create');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new object</h1>';
		$queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, 'Object Title:');
		if (!$createForm->submitted() || $createTitle->value == '') {
			$createForm->addObject($createTitle);
			$createForm->display();
		} else {
			$object = new $className(
				$foowd,
				$createTitle->value
			);
			if ($object->objectid != 0 && $object->save($foowd, FALSE)) {
				echo '<p>Object created and saved.</p>';
				echo '<p><a href="', getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className)))), '">Click here to view it now</a>.</p>';
			} else {
				trigger_error('Could not create object.');
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/*** METHODS ***/

/* view object */

	function method_view(&$foowd) {
		$foowd->track('foowd_object->method_view');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Viewing Object "', $this->getTitle(), '"</h1>';
		echo '<p>This object is of the base class "foowd_object" which is only intended to be extended and not instanciated.</p>';
		show($this);
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* object history */

	function method_history(&$foowd) {
		$foowd->track('foowd_object->method_history');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>History of Object "', $this->getTitle(), '"</h1>';
		$objArray = $foowd->getObject(array(
			'objectid' => $this->objectid,
			'classid' => $this->classid
		));
		
		echo '<h3>Object Details</h3>';
		echo '<p>';
		echo '<em>Title:</em> ', $this->getTitle(), '<br />';
		echo '<em>Created:</em> ', date(DATETIME_FORMAT, $this->created), '<br />';
		echo '<em>Author:</em> ', $this->creatorName, '<br />';
		echo '<em>Object Type:</em> ', getClassDescription($this->classid), '<br />';
		if ($this->workspaceid != 0) {
			echo '<em>Workspace:</em> ', $this->workspaceid, '<br />';
		}
		echo '</p>';
		
		echo '<h3>Archived Versions</h3>';
		echo '<table border="1">';
		echo '<tr>';
		echo '<th>Date</th>';
		echo '<th>Author</th>';
		echo '<th>Page Version</th>';
		echo '</tr>';
		$foo = FALSE;
		foreach ($objArray as $object) {
			echo '<tr>';
			echo '<td>', date(DATETIME_FORMAT, $object->updated), '</td>';
			echo '<td>', $object->updatorName, '</td>';
			echo '<td><a href="', getURI(array('method' => 'view', 'objectid' => $object->objectid, 'version' => $object->version, 'classid' => $object->classid)), '">', $object->version, '</a></td>';
			if ($foo) {
				echo '<td><a href="', getURI(array('method' => 'revert', 'objectid' => $object->objectid, 'version' => $object->version, 'classid' => $object->classid)), '">Revert</a></td>';
			}
			echo '</tr>';
			$foo = TRUE;
		}
		echo '</table>';
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* administrate object */

	function method_admin(&$foowd) {
		$foowd->track('foowd_object->method_admin');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Administrate "', $this->getTitle(), '"</h1>';
		echo '<p>Use this form to update the values of this object.</p>';
		$adminForm = new input_form('adminForm', NULL, 'POST');
		
		$obj = get_object_vars($this);
		unset($obj['foowd_vars_meta']);
		unset($obj['foowd_indexes']);
		unset($obj['foowd_original_access_vars']);
		foreach ($obj as $memberName => $memberVar) {
			if (is_array($memberVar)) {
				$textarray = new input_textarray($memberName, $this->foowd_vars_meta[$memberName], $memberVar, ucwords($memberName).':');
				if ($adminForm->submitted()) { // form submitted, update object with new values
					if (!$this->set($foowd, $memberName, $textarray->items)) {
						$textarray->items = $this->$memberName;
					}
				}
				$adminForm->addObject($textarray);
			} else {
				if (isset($this->foowd_vars_meta[$memberName])) {
					$reg = $this->foowd_vars_meta[$memberName];
				} else {
					$reg = '';
				}
				if ($reg == '') { // display textarea
					$adminForm->addObject($textbox = new input_textarea($memberName, NULL, $memberVar, ucwords($memberName).':'));
				} elseif ($reg == 'binary') { // display nothing
					$bin = ucwords($memberName).': Binary data';
					$adminForm->addObject($bin);
				} else { // display textbox
					$adminForm->addObject($textbox = new input_textbox($memberName, $reg, $memberVar, ucwords($memberName).':'));
				}
				if ($adminForm->submitted()) { // form submitted, update object with new values
					$this->set($foowd, $memberName, $textbox->value);
				}
			}
		}
		
		$adminForm->display();
		if ($adminForm->submitted()) { // object changed, save
			if ($this->save($foowd, FALSE)) {
				echo '<p>Object updated and saved.</p>';
			} else {
				trigger_error('Could not save object.');
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* revert object */

	function method_revert(&$foowd) {
		$foowd->track('foowd_object->method_revert');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Revert "', $this->getTitle(), '" Back To Version #', $this->version, '</h1>';
		$confirm = new input_querystring('confirm', '/^[y]$/', FALSE);
		if ($confirm->value) {
			if ($this->save($foowd, TRUE)) {
				echo '<p>Object reverted.</p>';
			} else {
				trigger_error('Unable to revert object to previous version.');
			}
		} else {
			echo '<p>Are you sure?</p>';
			echo '<p>';
			echo '<a href="', getURI(array('method' => 'revert', 'objectid' => $this->objectid, 'version' => $this->version, 'classid' => $this->classid, 'confirm' => 'y')), '">YES, revert "', $this->getTitle(), '" back to version ', $this->version, '</a><br />';
			echo '<a href="', getURI(array('method' => 'history', 'objectid' => $this->objectid, 'version' => $this->version, 'classid' => $this->classid)), '">NO, I made a mistake, leave it as it is.</a>';
			echo '</p>';
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* delete object */

	function method_delete(&$foowd) {
		$foowd->track('foowd_object->method_delete');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Delete "', $this->getTitle(), '"</h1>';
		$confirm = new input_querystring('confirm', '/^[y]$/', FALSE);
		if ($confirm->value) {
			if ($this->delete($foowd)) {
				echo '<p>Object deleted.</p>';
			} else {
				trigger_error('Unable to delete object.');
			}
		} else {
			echo '<p>Are you sure?</p>';
			echo '<p>';
			echo '<a href="', getURI(array('method' => 'delete', 'objectid' => $this->objectid, 'classid' => $this->classid, 'confirm' => 'y')), '">YES, delete "', $this->getTitle(), '" and all of it\'s archived previous versions.</a><br />';
			echo '<a href="', getURI(array('method' => 'view', 'objectid' => $this->objectid, 'classid' => $this->classid)), '">NO, I made a mistake, leave it as it is.</a>';
			echo '</p>';
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* clone object */

	function method_clone(&$foowd) {
		$foowd->track('foowd_object->method_clone');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Clone Version #', $this->version, ' Of "', $this->getTitle(), '"</h1>';
		$cloneForm = new input_form('cloneForm', NULL, 'POST', 'Clone Object', NULL);
		$cloneTitle = new input_textbox('cloneTitle', REGEX_TITLE, $this->getTitle(), 'Clone Title');
		$workspaceClassid = WORKSPACE_CLASS_ID;
		$workspaces = $foowd->retrieveObjects(array('classid = '.$workspaceClassid), array('title'));
		$workspaceArray = array(0 => 'Outside');
		if ($workspaces) {
			while ($workspace = $foowd->retrieveObject($workspaces)) {
				if ($foowd->user->inGroup($workspace->permissions['fill']) && ($this->classid != $workspaceClassid || $this->objectid != $workspace->objectid)) {
					$workspaceArray[$workspace->objectid] = htmlspecialchars($workspace->title);
				}
			}
		}
		$cloneWorkspace = new input_dropdown('workspaceDropdown', NULL, $workspaceArray, 'Workspace: ');
		if ($cloneForm->submitted()) {
			$this->title = $cloneTitle->value;
			$this->objectid = crc32(strtolower($this->title));
			$this->workspaceid = $cloneWorkspace->value;
			 // adjust original workspace so as to create new object rather than overwrite old one.
			$this->foowd_original_access_vars['objectid'] = $this->objectid;
			$this->foowd_original_access_vars['workspaceid'] = $this->workspaceid;
			if ($this->save($foowd, FALSE)) {
				echo '<p>Object cloned.</p>';
			} else {
				trigger_error('Could not clone object.');
			}
		} else {
			$cloneForm->addObject($cloneTitle);
			$cloneForm->addObject($cloneWorkspace);
			$cloneForm->display();
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* object 2 XML */

	function method_xml(&$foowd) {
		$foowd->debug = FALSE;
		header("Content-type: text/xml");
		echo '<?xml version="1.0"?>';
		echo '<foowd version="', VERSION, '" generated="', time(), '">';
		echo '<', get_class($this), '>';
		$this->vars2XML(get_object_vars($this), $this->__sleep());
		echo '</', get_class($this), '>';
		echo '</foowd>';
	}
	
	function vars2XML($vars, $goodVars) {
		foreach ($vars as $memberName => $memberVar) {
			if ($memberName !== '' && (!$goodVars || in_array($memberName, $goodVars))) {
				if (is_numeric(substr($memberName, 0, 1))) {
					$memberName = 'i'.$memberName;
				}
				echo '<', $memberName, '>';
				if (is_array($memberVar)) { // an array
					$this->vars2XML($memberVar, FALSE);
				} elseif (isset($this->foowd_vars_meta[$memberName]) && $this->foowd_vars_meta[$memberName] == 'binary') { // binary data
					echo '<![CDATA['.utf8_encode($memberVar).']]>';
				} else { // yay, a var
					if (strstr($memberVar, '<') || strstr($memberVar, '>') || strstr($memberVar, '&')) {
						$xml_parser = xml_parser_create();
						if (xml_parse($xml_parser, '<xml>'.$memberVar.'</xml>', TRUE)) {
							echo $memberVar;
						} else {
							echo '<![CDATA['.$memberVar.']]>';
						}
						xml_parser_free($xml_parser);
					} else {
						echo $memberVar;
					}
				}
				echo '</', $memberName, '>';
			}
		}
	}
	
/* user group support for permissions */

	function method_permissions(&$foowd) {
		$foowd->track('foowd_object->method_permissions');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Set permissions for "', $this->getTitle(), '"</h1>';

		$permissionForm = new input_form('permissionForm', NULL, 'POST');

		if (defined('GROUP_CLASS_ID')) {
		
			$userGroups = $foowd->retrieveObjects(
				array('classid = '.GROUP_CLASS_ID),
				NULL,
				array('title')
			);
			$items = array('' => '[Everyone]');
			if ($this->creatorid = $foowd->user->objectid) {
				$items['Author'] = '[Author]';
			}
			if ($userGroups) {
				while ($userGroup = $foowd->retrieveObject($userGroups)) {
					if ($foowd->user->inGroup($userGroup->permissions['add']) || ($userGroup->permissions['add'] == 'Author' && $userGroup->creatorid = $foowd->user->objectid)) {
						$items[$userGroup->objectid] = $userGroup->getTitle();
					}
				}
			}

			$permissionForm->display_start();
			foreach (get_class_methods($this) as $methodName) {
				if (substr($methodName, 0, 7) == 'method_') {
					$methodName = substr($methodName, 7);
					if (isset($this->permissions[$methodName])) {
						$value = $this->permissions[$methodName];
					} else {
						$value = '';
					}
					if (isset($items[$value])) {
						$permissionBox = new input_dropdown($methodName, $value, $items, ucwords($methodName).':');
						$permissionBox->display();
						echo '<br />';
						if ($permissionForm->submitted()) {
							if ($permissionBox->value == '') {
								unset($this->permissions[$methodName]);
							} else {
								$this->permissions[$methodName] = $permissionBox->value;
							}
						}
					} else {
						echo ucwords($methodName).': Other<br />';
					}
				}
			}
			$permissionForm->display_end();
			
		} else {
		
			foreach (get_class_methods($this) as $methodName) {
				if (substr($methodName, 0, 7) == 'method_') {
					$methodName = substr($methodName, 7);
					if (isset($this->permissions[$methodName])) {
						$value = $this->permissions[$methodName];
					} else {
						$value = '';
					}
					$permissionBox = new input_textbox($methodName, $this->foowd_vars_meta['permissions'], $value, ucwords($methodName).':', 20);
					$permissionForm->addObject($permissionBox);
					if ($permissionForm->submitted()) {
						if ($permissionBox->value == '') {
							unset($this->permissions[$methodName]);
						} else {
							$this->permissions[$methodName] = $permissionBox->value;
						}
					}
				}
			}
			$permissionForm->display();

		}

		if ($permissionForm->submitted()) {
			if ($this->save($foowd, FALSE)) {
				echo '<p>Object permissions updated.</p>';
			} else {
				trigger_error('Could not save object.');
			}
		}

		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

}

?>