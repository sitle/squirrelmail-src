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

/*
 * Modified by SquirrelMail Development Team
 *
 * $Id$
 */
 
/**
The FOOWD base system class, this class is the base class for all other system
classes. It has member functions for saving objects to the storage medium,
administrating objects, etc. all the base FOOWD object functionality.
**/

/** METHOD PERMISSIONS **/
setPermission('foowd_object', 'class',  'create', 'Gods');
setPermission('foowd_object', 'object', 'admin', 'Gods');
setPermission('foowd_object', 'object', 'revert', 'Gods');
setPermission('foowd_object', 'object', 'delete', 'Gods');
setPermission('foowd_object', 'object', 'clone', 'Gods');
setPermission('foowd_object', 'object', 'permissions', 'Gods');

/** CLASS DESCRIPTOR **/
setClassMeta('foowd_object', 'Base Object');

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
	var $permissions = array();

/*** CONSTRUCTOR ***/

	function foowd_object(
		&$foowd,
		$title = NULL,
		$viewGroup = NULL,
		$adminGroup = NULL,
		$deleteGroup = NULL,
		$allowDuplicateTitle = NULL
	) {
		$foowd->track('foowd_object->constructor', $title);
	
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

// check objectid, first in EXTERNAL elements
        global $EXTERNAL_RESOURCES;
        if ( isset($EXTERNAL_RESOURCES) ) {
            while ( array_key_exists($this->objectid, $EXTERNAL_RESOURCES) ) {
                if (!$allowDuplicateTitle) {
                    $this->objectid = 0;
                    $foowd->track();
                    return FALSE;
                }
                $this->objectid++;
            }
        }

// check objectid, now in DB objects.
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
		if ($viewGroup != NULL) $this->permissions['view'] = $viewGroup;
		if ($adminGroup != NULL) {
			$this->permissions['admin'] = $adminGroup;
			$this->permissions['clone'] = $adminGroup;
		}
		if ($deleteGroup != NULL) $this->permissions['admin'] = $deleteGroup;

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
        $foowd->track('foowd_object->set',$member, $value);
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
        $foowd->track();
		return $exitValue;
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
			$foowd->track(); 
            return TRUE;
		} else {
			$foowd->track(); 
            return FALSE;
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
			$foowd->track(); 
            return TRUE;
		} else {
			$foowd->track(); 
            return FALSE;
		}
	}

/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
 		$foowd->track('foowd_object->class_create', $className);
        $title = _("Create New Object");
    	if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this, $title);       
        echo "<h1>$title</h1>";

		$queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
		$createForm = new input_form('createForm', NULL, 'POST', _("Create"), NULL);
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, _("Object Title").':');
		if (!$createForm->submitted() || $createTitle->value == '') {
			$createForm->addObject($createTitle);
			$createForm->display();
		} else {
			$object = new $className(
				$foowd,
				$createTitle->value
			);
			if ($object->objectid != 0 && $object->save($foowd, FALSE)) {
				echo '<p>', _("Object created and saved."), '</p>';
				echo '<p>', sprintf(_('<a href="%s">Click here to view it now</a>.'), getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className))))), '</p>';
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

		$objArray = $foowd->getObject(array(
			'objectid' => $this->objectid,
			'classid' => $this->classid
		));
		
		echo '<h3>', _("Object Details"), '</h3>';
		echo '<p>';
		echo '<em>', _("Title"), ':</em> ', $this->getTitle(), '<br />';
		echo '<em>', _("Created"), ':</em> ', date(DATETIME_FORMAT, $this->created), '<br />';
		echo '<em>', _("Author"), ':</em> ', $this->creatorName, '<br />';
		echo '<em>', _("Object Type"), ':</em> ', getClassDescription($this->classid), '<br />';
		if ($this->workspaceid != 0) {
			echo '<em>', _("Workspace"), ':</em> ', $this->workspaceid, '<br />';
		}
		echo '</p>';
		
		echo '<h3>', _("Archived Versions"), '</h3>';
		echo '<table border="1">';
		echo '<tr>';
		echo '<th>', _("Date"), '</th>';
		echo '<th>', _("Author"), '</th>';
		echo '<th>', _("Version"), '</th>';
		echo '</tr>';
		$foo = FALSE;
		foreach ($objArray as $object) {
			echo '<tr>';
			echo '<td>', date(DATETIME_FORMAT, $object->updated), '</td>';
			echo '<td>', $object->updatorName, '</td>';
			echo '<td><a href="', getURI(array('method' => 'view', 'objectid' => $object->objectid, 'version' => $object->version, 'classid' => $object->classid)), '">', $object->version, '</a></td>';
			if ($foo) {
				echo '<td><a href="', getURI(array('method' => 'revert', 'objectid' => $object->objectid, 'version' => $object->version, 'classid' => $object->classid)), '">', _("Revert"), '</a></td>';
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

		echo '<p>', _("Use this form to administrate the values of this object."), '</p>';
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
				echo '<p>', _("Object updated and saved."), '</p>';
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
		echo '<h1>', sprintf(_('Revert "%s" back to version #%d'), $this->getTitle(), $this->version), '</h1>';
		$confirm = new input_querystring('confirm', '/^[y]$/', FALSE);
		if ($confirm->value) {
			if ($this->save($foowd, TRUE)) {
				echo '<p>', _("Object reverted."), '</p>';
			} else {
				trigger_error('Unable to revert object to previous version.');
			}
		} else {
			echo '<p>', _('Are you sure?'), '</p>';
			echo '<p>';
			printf(_('<a href="%s">YES, revert "%s" back to version %d</a><br />'), getURI(array('method' => 'revert', 'objectid' => $this->objectid, 'version' => $this->version, 'classid' => $this->classid, 'confirm' => 'y')), $this->getTitle(), $this->version);
			printf(_('<a href="%s">NO, I made a mistake, leave it as it is.</a>'), getURI(array('method' => 'history', 'objectid' => $this->objectid, 'version' => $this->version, 'classid' => $this->classid)));
			echo '</p>';
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* delete object */

	function method_delete(&$foowd) {
		$foowd->track('foowd_object->method_delete');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this, $this->getTitle());

		$confirm = new input_querystring('confirm', '/^[y]$/', FALSE);
		if ($confirm->value) {
			if ($this->delete($foowd)) {
				echo '<p>', _("Object deleted."), '</p>';
			} else {
				trigger_error('Unable to delete object.');
			}
		} else {
			echo '<p>', _('Are you sure?'), '</p>';
			echo '<p>';
			printf(_('<a href="%s">YES, delete "%s" and all of it\'s archived previous versions.</a><br />'), getURI(array('method' => 'delete', 'objectid' => $this->objectid, 'classid' => $this->classid, 'confirm' => 'y')), $this->getTitle());
			printf(_('<a href="%s">NO, I made a mistake, leave it as it is.</a>'), getURI(array('method' => 'view', 'objectid' => $this->objectid, 'classid' => $this->classid)));
			echo '</p>';
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* clone object */

	function method_clone(&$foowd) {
		$foowd->track('foowd_object->method_clone');
		$title = sprintf(_('Clone Version #%s Of "%s"'), $this->version, $this->getTitle());
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this, $title);

		$cloneForm = new input_form('cloneForm', NULL, 'POST', 'Clone Object', NULL);
		$cloneTitle = new input_textbox('cloneTitle', REGEX_TITLE, $this->getTitle(), 'Clone Title');
		if (defined('WORKSPACE_CLASS_ID')) {
			$workspaces = $foowd->retrieveObjects(array('classid = '.WORKSPACE_CLASS_ID), array('title'));
			$workspaceArray = array(0 => getConstOrDefault('OUTSIDE_WORKSPACE_NAME', 'Outside'));
			if ($workspaces) {
				while ($workspace = $foowd->retrieveObject($workspaces)) {
					if ($foowd->user->inGroup($workspace->permissions['fill']) && ($this->classid != WORKSPACE_CLASS_ID || $this->objectid != $workspace->objectid)) {
						$workspaceArray[$workspace->objectid] = htmlspecialchars($workspace->title);
					}
				}
			}
			$cloneWorkspace = new input_dropdown('workspaceDropdown', NULL, $workspaceArray, 'Workspace: ');
			$newWorkspace = $cloneWorkspace->value;
		} else {
			$newWorkspace = 0;
		}
		if ($cloneForm->submitted()) {
			if ($this->workspaceid == $newWorkspace && $this->title == $cloneTitle->value) {
				trigger_error('Can not clone object to the same title within the same workspace.');
			} else {
				$this->title = $cloneTitle->value;
				$this->objectid = crc32(strtolower($this->title));
				$this->workspaceid = $newWorkspace;
				 // adjust original workspace so as to create new object rather than overwrite old one.
				$this->foowd_original_access_vars['objectid'] = $this->objectid;
				$this->foowd_original_access_vars['workspaceid'] = $this->workspaceid;
				if ($this->save($foowd, FALSE)) {
					echo '<p>', _("Object cloned."), '</p>';
				} else {
					trigger_error('Could not clone object.');
				}
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
//						$xml_parser = xml_parser_create();
//						if (xml_parse($xml_parser, '<xml>'.$memberVar.'</xml>', TRUE)) {
//							echo $memberVar;
//						} else {
							echo '<![CDATA['.$memberVar.']]>';
//						}
//						xml_parser_free($xml_parser);
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

		$permissionForm = new input_form('permissionForm', NULL, 'POST');

		$items = $foowd->getUserGroups(TRUE);

		$permissionForm->display_start();
		$changed = FALSE;
		foreach (get_class_methods($this) as $methodName) {
			if (substr($methodName, 0, 7) == 'method_') {
				$methodName = substr($methodName, 7);
				if (isset($this->permissions[$methodName])) {
					$value = $this->permissions[$methodName];
				} else {
					$value = '';
				}
				$defaultPermission = getPermission(get_class($this), $methodName, 'object');
				if (isset($items[$defaultPermission])) {
					$items[''] = _("Default").' ('.$defaultPermission.')';
				} else {
					unset($items['']);
				}
				if (isset($items[$value])) {
					$permissionBox = new input_dropdown($methodName, $value, $items, ucwords($methodName).':');
					$permissionBox->display();
					echo '<br />';
					if ($permissionForm->submitted()) {
						$changed = TRUE;
						if ($permissionBox->value == '') {
							unset($this->permissions[$methodName]);
						} else {
							$this->permissions[$methodName] = $permissionBox->value;
						}
					}
				} else {
					echo ucwords($methodName).': ';
					if (isset($this->permissions[$methodName])) {
						echo $this->permissions[$methodName];
					} else {
						echo _("Default").' (', getPermission(get_class($this), $methodName, 'object'), ')';
					}
					echo '<br />';
				}
			}
		}
		$permissionForm->display_end();

		if ($permissionForm->submitted() && $changed) {
			if ($this->save($foowd, FALSE)) {
				echo '<p>', _("Object permissions updated."), '</p>';
			} else {
				trigger_error('Could not save object.');
			}
		}

		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

}

/*** ANONYMOUS USER ***/

/* Anonymous user class, used to instanciate bogus user for anoymous access where
   only basic user data is required and it would be a waste to pull a user from the
   database. */

class foowd_anonuser extends foowd_object {
    function foowd_anonuser(&$foowd) {
        $foowd->track('foowd_anonuser->constructor');
        if (defined('ANONYMOUS_USER_NAME')) {
            $this->title = ANONYMOUS_USER_NAME;
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $this->title = $_SERVER['REMOTE_ADDR'];
        } else {
            $this->title = 'Anonymous';
        }
        $this->objectid = NULL;
        $this->version = 1;
        $this->classid = -1063205124;
        $this->workspaceid = 0;
        $this->created = time();
        $this->creatorid = 0;
        $this->creatorName = 'System';
        $this->updated = time();
        $this->updatorid = 0;
        $this->updatorName = 'System';
        $this->email = NULL;
        $this->permissions = NULL;
		$foowd->track();
    }

    function inGroup($groupName, $creatorid = NULL) {
        if ($groupName == 'Everyone' || getConstOrDefault('ANON_GOD', FALSE)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
	
	function passwordCheck($password, $plainText = FALSE) {
		return TRUE;
	}
	
	function save(&$foowd) { // override save function since it's not a real Foowd object and is just instanciated as needed.
		return FALSE;
	}
}

?>
