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
 * Modified by SquirrelMail Development Team
 *
 * $Id$
 */
 
/**
 * The FOOWD base system class, this class is the base class for all other system classes. 
 * It has member functions for saving objects to the storage medium, 
 * administrating objects, etc., all the base FOOWD object functionality.
 */

define('OBJECT_CLASS_ID', -1465013268);

/** CLASS DESCRIPTOR **/
$foowd_class_meta[OBJECT_CLASS_ID]['className'] = 'foowd_object';
$foowd_class_meta[OBJECT_CLASS_ID]['description'] = 'Base Object';

/** CLASS METHOD PERMISSIONS **/
define('FOOWD_OBJECT_CREATE_PERMISSION', 'Gods');

/** CLASS METHOD PASSTHRU FUNCTION **/
function foowd_object_classmethod(&$foowd, $methodName) { 
    foowd_object::$methodName($foowd, 'foowd_object'); 
}

/** CLASS DECLARATION **/
class foowd_object {

	var $foowd_vars_meta = array(); // object member variable meta array
	var $foowd_indexes = array(); // object index meta array

    // this object's original access variables,
    // just incase they change before saving.
	var $foowd_original_access_vars = array(); 
	
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
        track('foowd_object::foowd_object',$title);
	
		$this->__wakeup(); // init meta arrays
	
		$allowDuplicateTitle = setVarConstOrDefault($allowDuplicateTitle, 'ALLOW_DUPLICATE_TITLE', TRUE);
	
/* set object vars */

		$maxTitleLength = getRegexLength($this->foowd_vars_meta['title'], 32);
		if ( strlen($title) > 0 &&
             strlen($title) < $maxTitleLength && 
             preg_match($this->foowd_vars_meta['title'], $title)) {
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
                    track();
                    return FALSE;
                }
                $this->objectid++;
            }
        }

// check objectid, now in DB objects.
		while (DBSelect($foowd->conn, $foowd->dbtable, NULL, 
                        array('objectid'), 
                        array('objectid = '.$this->objectid, 'AND', 
                              'classid = '.$this->classid, 'AND', 
                              'workspaceid = '.$this->workspaceid), 
                        NULL, NULL, 1)) {
			if (!$allowDuplicateTitle) {
				$this->objectid = 0;
                track();
				return FALSE;
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
		$this->permissions['view'] = setVarConstOrDefault($viewGroup, 
                                                          'DEFAULT_VIEW_GROUP',
                                                          'Everyone');
		$this->permissions['admin'] = setVarConstOrDefault($adminGroup, 
                                                           'DEFAULT_ADMIN_GROUP', 
                                                           'Gods');
		$this->permissions['delete'] = setVarConstOrDefault($deleteGroup, 
                                                            'DEFAULT_DELETE_GROUP', 
                                                            'Gods');
		$this->permissions['clone'] = setVarConstOrDefault($adminGroup, 
                                                           'DEFAULT_ADMIN_GROUP', 
                                                           'Gods');
        track();
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
		$this->foowd_vars_meta['permissions'] = REGEX_TITLE;
/** INDEX METADATA **/
		$this->foowd_indexes['objectid'] = 'INT NOT NULL';
		$this->foowd_indexes['version'] = 'INT UNSIGNED NOT NULL DEFAULT 1';
		$this->foowd_indexes['classid'] = 'INT NOT NULL DEFAULT 0';
		$this->foowd_indexes['workspaceid'] = 'INT NOT NULL DEFAULT 0';
		$this->foowd_indexes['title'] = 
                    'VARCHAR('.getRegexLength($this->foowd_vars_meta['title'], 32).') NOT NULL';
		$this->foowd_indexes['updated'] = 'DATETIME NOT NULL';
        $this->foowd_indexes['sectionid'] = 'INT NOT NULL DEFAULT 0';
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
        track('foowd_object::set',$member, $value);
		if (isset($this->$member) || $this->$member == NULL) {
			if (isset($this->foowd_vars_meta[$member])) {
				if (is_array($value)) {
					foreach ($value as $val) {
						if ($this->foowd_vars_meta[$member] != '' && 
                            $this->foowd_vars_meta[$member] != NULL && 
                            !preg_match($this->foowd_vars_meta[$member], $val)) {
                            track();
							return FALSE;
						}
						$this->$member = $value;
                        track();
						return TRUE;
					}
				} else {
					if ($this->foowd_vars_meta[$member] == '' || 
                        $this->foowd_vars_meta[$member] == NULL || 
                        preg_match($this->foowd_vars_meta[$member], $value)) {
						$this->$member = $value;
                        track();
						return TRUE;
					}
				}
			}
		}
        track();
		return FALSE;
	}

/* save object */

	function save(&$foowd, $incrementVersion = TRUE) { // write object to database
        track('foowd_object::save',$incrementVersion);
        
// update values
		$this->updatorid = $foowd->user->objectid;
		$this->updatorName = $foowd->user->title;
		$this->updated = time();
		if ($incrementVersion) { // create new version if told to
			$object = $foowd->fetchObject(
                array('objectid' => $this->foowd_original_access_vars['objectid'],
                      'classid' => $this->foowd_original_access_vars['classid'],
                      'workspaceid' => $this->foowd_original_access_vars['workspaceid']));
			$this->version = ++$object->version;
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
					if (substr_count($definition, DATABASE_DATETIME_DATATYPE)) { 
                        // translate unixtime to db date format
						$fieldArray[$index] = date(DATABASE_DATE, $this->$index);
					} else {
						$fieldArray[$index] = $this->$index;
					}
				}
			}
		}
// set conditions
		$conditionArray = array(
			'objectid = '.$this->foowd_original_access_vars['objectid'],
			'AND',
			'version = '.$this->foowd_original_access_vars['version'],
			'AND',
			'classid = '.$this->foowd_original_access_vars['classid'],
			'AND',
			'workspaceid = '.$this->foowd_original_access_vars['workspaceid']
		);

		$exitValue = FALSE;
// try to update existing record
		if (DBUpdate($foowd->conn, $foowd->dbtable, $fieldArray, $conditionArray)) {
			$exitValue = 1;
		} else {
// if fail, write new record
			if (DBInsert($foowd->conn, $foowd->dbtable, $fieldArray)) {
				$exitValue = 2;
			} else {
// if fail, modify table to include indexes from class definition
				if ($query = DBSelect($foowd->conn, $foowd->dbtable, NULL,
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
					if ($missingFields != NULL && DBAlterTable($foowd->conn, $foowd->dbtable, $missingFields)) {
						if (DBUpdate($foowd->conn, $foowd->dbtable, $fieldArray, $conditionArray)) {
							$exitValue = 3;
						} elseif (DBInsert($foowd->conn, $foowd->dbtable, $fieldArray)) {
							$exitValue = 4;
						}
					}
				}
			}
		}
// tidy old archived versions
		if ($exitValue && $this->updated < time() - setConstOrDefault('TIDY_DELAY', 86400)) {
			$this->tidyArchive(&$foowd);
		}
        track();
		return $exitValue;
	}

/* delete object */
	function delete(&$foowd) { // remove all versions of an object from the database
        track('foowd_object::delete');
		$conditionArray = array(
			'objectid = '.$this->objectid,
			'AND',
			'classid = '.$this->classid,
			'AND',
		);
		if ($this->workspaceid == 0) {
			$conditionArray[] = 'workspaceid = 0';
		} else {
			$conditionArray[] = '(workspaceid = '.$this->workspaceid;
			$conditionArray[] = 'OR';
			$conditionArray[] = 'workspaceid = 0)';
		}
		if (DBDelete($foowd->conn, $foowd->dbtable, $conditionArray)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
/* tidy archive */
 
	function tidyArchive(&$foowd) { // clean up old archived versions	
		$conditionArray = array(
			'objectid = '.$this->objectid,
			'AND',
			'version < '.($this->version - setConstOrDefault('MINIMUM_NUMBER_OF_ARCHIVED_VERSIONS', 3)),
			'AND',
			'classid = '.$this->classid,
			'AND',
			'workspaceid = '.$this->workspaceid,
			'AND',
			'updated < "'.date(DATABASE_DATE, strtotime(setConstOrDefault('DESTROY_OLDER_THAN', '-1 Month'))).'"'
		);
		if (DBDelete($foowd->conn, $foowd->dbtable, $conditionArray)) {
            track();
			return TRUE;
		} else {
            track();
			return FALSE;
		}
	}

/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
        track('foowd_object::class_create', $className);
        $title = _("Create New Object");
    	if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this, $title);
        
        echo "<h1>$title</h1>";

		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, NULL, 'Object Title:');
		if (!$createForm->submitted() || $createTitle->value == '') {
			$createForm->addObject($createTitle);
			$createForm->display();
		} else {
			$object = new $className(
				$foowd,
				$createTitle->value
			);
			if ($object->save($foowd, FALSE)) {
				echo '<p>'._('Object created and saved.').'</p>';
			} else {
				echo '<p>'._('Could not create object.').'</p>';
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
        track();
	}

/*** METHODS ***/

/* view object */

	function method_view(&$foowd) {
        track('foowd_object::method_view');

		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

        echo '<h1>', _("Viewing Object"), ' "', $this->getTitle(), '"</h1>';

		echo '<p>This object is of the base class "foowd_object" which',
             ' is only intended to be extended and not instanciated.</p>';
		show($this);
		if (function_exists('foowd_append')) foowd_append($foowd, $this);

        track();
	}

/* object history */

	function method_history(&$foowd) {
		global $foowd_class_meta;
        track('foowd_object::method_history');

		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

        echo '<h1>',_("History of Object"), ' ', $this->getTitle(), '</h1>';

		$objArray = $foowd->getObject(array(
			'objectid' => $this->objectid,
			'classid' => $this->classid
		));
		
		echo '<h3>'._('Object Details').'</h3>';
		echo '<p>';
		echo '<em>Title:</em> ', $this->getTitle(), '<br />';
		echo '<em>Created:</em> ', date(DATETIME_FORMAT, $this->created), '<br />';
		echo '<em>Author:</em> ', $this->creatorName, '<br />';
		echo '<em>Object Type:</em> ', 
             $foowd_class_meta[(int)$this->classid]['description'], '<br />';
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
			echo '<td><a href="', getURI(array('method' => 'view', 
                                               'objectid' => $object->objectid, 
                                               'version' => $object->version, 
                                               'classid' => $object->classid)), 
                 '">', $object->version, '</a></td>';
			if ($foo) {
				echo '<td><a href="', getURI(array('method' => 'diff', 
                                                   'objectid' => $object->objectid, 
                                                   'version' => $object->version, 
                                                   'classid' => $object->classid)), 
                     '">Diff</a></td>';
				echo '<td><a href="', getURI(array('method' => 'revert', 
                                                   'objectid' => $object->objectid, 
                                                   'version' => $object->version, 
                                                   'classid' => $object->classid)), 
                     '">Revert</a></td>';
			}
			echo '</tr>';
			$foo = TRUE;
		}
		echo '</table>';
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
        track();
	}

/* administrate object */

	function method_admin(&$foowd) {
        track('foowd_object::method_admin');

		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Administrate "', $this->getTitle(), '"</h1>';
		echo '<p>Use this form to update the values of this object.</p>';
		$adminForm = new input_form('adminForm', NULL, 'POST');
		$this->displayMemberVars($adminForm, get_object_vars($this));
		$adminForm->display();
		if ($adminForm->submitted()) { // object changed, save
			if ($this->save($foowd, FALSE)) {
				echo '<p>Object updated and saved.</p>';
			} else {
				echo '<p>Could not save object.</p>';
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
        track();
	}

	function displayMemberVars(&$form, $obj) {
        track('foowd_object::displayMemberVars');

		unset($obj['foowd_vars_meta']);
		unset($obj['foowd_indexes']);
		unset($obj['foowd_original_access_vars']);
		foreach ($obj as $memberName => $memberVar) {
			if (is_array($memberVar)) {
				$form->addObject($textarray = new input_textarray($memberName, 
                                                                  $this->foowd_vars_meta[$memberName], 
                                                                  $memberVar, 
                                                                  ucwords($memberName)));
				if ($form->submitted()) { // form submitted, update object with new values
					$this->set($foowd, $memberName, $textarray->items);
				}
			} else {
				if (isset($this->foowd_vars_meta[$memberName])) {
					$reg = $this->foowd_vars_meta[$memberName];
				} else {
					$reg = '';
				}
				if ($reg == '') { // display textarea
					$form->addObject($textbox = new input_textarea($memberName, 
                                                                   NULL, 
                                                                   $memberVar, 
                                                                   ucwords(str_replace('$', ' ', $memberName))));
				} else { // display textbox
					$form->addObject($textbox = new input_textbox($memberName, 
                                                                  $reg, 
                                                                  $memberVar, 
                                                                  ucwords(str_replace('$', ' ', $memberName))));
				}
				if ($form->submitted()) { // form submitted, update object with new values
					$this->set($foowd, $memberName, $textbox->value);
				}
			}
		}
        track();
	}

/* revert object */

	function method_revert(&$foowd) {
        track('foowd_object::method_revert');

		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Revert "', $this->getTitle(), 
             '" Back To Version #', $this->version, '</h1>';

		$confirm = new input_querystring('confirm', '/^[y]$/', FALSE);

		if ($confirm->value) {
			$this->save($foowd, TRUE);
			echo '<p>Object reverted.</p>';
		} else {
			echo '<p>Are you sure?</p>';
			echo '<p>';
			echo '<a href="', getURI(array('method' => 'revert', 
                                           'objectid' => $this->objectid, 
                                           'version' => $this->version, 
                                           'classid' => $this->classid, 
                                           'confirm' => 'y')), 
                 '">YES, revert "', $this->getTitle(), 
                 '" back to version ', $this->version, '</a><br />';
			echo '<a href="', getURI(array('method' => 'history', 
                                           'objectid' => $this->objectid, 
                                           'version' => $this->version, 
                                           'classid' => $this->classid)), 
                 '">NO, I made a mistake, leave it as it is.</a>';
			echo '</p>';
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);

        track();
	}

/* delete object */

	function method_delete(&$foowd) {
        track('foowd_object::method_delete');        

        // Specify title to avoid link
		if (function_exists('foowd_prepend')) 
            foowd_prepend($foowd, $this, $this->getTitle());
		echo '<h1>Delete "', $this->getTitle(), '"</h1>';
		$confirm = new input_querystring('confirm', '/^[y]$/', FALSE);
		if ($confirm->value) {
			$this->delete($foowd);
			echo '<p>Object deleted.</p>';
		} else {
			echo '<p>Are you sure?</p>';
			echo '<p>';
			echo '<a href="', getURI(array('method' => 'delete', 
                                           'objectid' => $this->objectid, 
                                           'classid' => $this->classid, 
                                           'confirm' => 'y')), 
                 '">YES, delete "', $this->getTitle(), 
                 '" and all of it\'s archived previous versions.</a><br />';

			echo '<a href="', getURI(array('method' => 'view', 
                                           'objectid' => $this->objectid, 
                                           'classid' => $this->classid)), 
                 '">NO, I made a mistake, leave it as it is.</a>';
			echo '</p>';
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
        track();
	}

/* clone object */

	function method_clone(&$foowd) {
        track('foowd_object::method_clone');

		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Clone Version #', $this->version, ' Of "', $this->getTitle(), '"</h1>';
		$cloneForm = new input_form('cloneForm', NULL, 'POST', 'Clone Object', NULL);
		$cloneTitle = new input_textbox('cloneTitle', REGEX_TITLE, 
                                         $this->getTitle(), 'Clone Title');
		$workspaces = $foowd->getObjects(array('classid = -679419151'), array('title'));
		$workspaceArray = array(0 => 'Outside');
		foreach ($workspaces as $workspace) {
			if ($foowd->user->inGroup($workspace->permissions['enter'])) {
				$workspaceArray[$workspace->objectid] = htmlspecialchars($workspace->title);
			}
		}
		$cloneWorkspace = new input_dropdown('workspaceDropdown', NULL, 
                                             $workspaceArray, 'Workspace: ');
		if ($cloneForm->submitted()) {
			$this->title = $cloneTitle->value;
			$this->objectid = crc32(strtolower($this->title));
			$this->workspaceid = $cloneWorkspace->value;

            // adjust original workspace so as to create new object rather 
            // than overwrite old one.
			$this->foowd_original_access_vars['workspaceid'] = $cloneWorkspace->value;

			$this->save($foowd, FALSE);
			echo '<p>Object cloned.</p>';
		} else {
			$cloneForm->addObject($cloneTitle);
			$cloneForm->addObject($cloneWorkspace);
			$cloneForm->display();
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
        track();
	}
	
}

?>
