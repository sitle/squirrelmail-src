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
class.text.plain.php
Foowd plain text class
*/

/** METHOD PERMISSIONS **/
if (!defined('PERMISSION_FOOWD_WORKSPACE_OBJECT_ENTER')) define('PERMISSION_FOOWD_WORKSPACE_OBJECT_ENTER', 'Gods');
if (!defined('PERMISSION_FOOWD_WORKSPACE_OBJECT_FILL')) define('PERMISSION_FOOWD_WORKSPACE_OBJECT_FILL', 'Gods');
if (!defined('PERMISSION_FOOWD_WORKSPACE_OBJECT_EMPTY')) define('PERMISSION_FOOWD_WORKSPACE_OBJECT_EMPTY', 'Gods');
if (!defined('PERMISSION_FOOWD_WORKSPACE_OBJECT_EXPORT')) define('PERMISSION_FOOWD_WORKSPACE_OBJECT_EXPORT', 'Gods');
if (!defined('PERMISSION_FOOWD_WORKSPACE_OBJECT_IMPORT')) define('PERMISSION_FOOWD_WORKSPACE_OBJECT_IMPORT', 'Gods');

/** CLASS DESCRIPTOR **/
if (!defined('META_-679419151_CLASSNAME')) define('META_-679419151_CLASSNAME', 'foowd_workspace');
if (!defined('META_-679419151_DESCRIPTION')) define('META_-679419151_DESCRIPTION', 'Workspace');

if (!defined('WORKSPACE_CLASS_ID')) define('WORKSPACE_CLASS_ID', -679419151);

/** CLASS DECLARATION **/
class foowd_workspace extends foowd_object {

	var $description;
	
/*** CONSTRUCTOR ***/

	function foowd_workspace(
		&$foowd,
		$title = NULL,
		$description = NULL,
		$viewGroup = NULL,
		$adminGroup = NULL,
		$deleteGroup = NULL,
		$enterGroup = NULL,
		$fillGroup = NULL,
		$emptyGroup = NULL,
		$exportGroup = NULL,
		$importGroup = NULL
	) {
		$foowd->track('foowd_workspace->constructor');

// base object constructor
		parent::foowd_object($foowd, $title, $viewGroup, $adminGroup, $deleteGroup);

/* set object vars */
		$this->description = $description;

/* set method permissions */
		$className = get_class($this);
		$this->permissions['enter'] = getPermission($className, 'enter', 'object'. $enterGroup);
		$this->permissions['fill'] = getPermission($className, 'fill', 'object'. $fillGroup);
		$this->permissions['empty'] = getPermission($className, 'empty', 'object'. $emptyGroup);
		$this->permissions['export'] = getPermission($className, 'export', 'object'. $exportGroup);
		$this->permissions['import'] = getPermission($className, 'import', 'object'. $importGroup);

		$foowd->track();
	}

/*** SERIALIZE FUNCTIONS ***/

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['description'] = '/^.{1,1024}$/';
	}

/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		$foowd->track('foowd_workspace->class_create');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new workspace</h1>';
		$queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, 'Title:');
		//$createDescription = new input_textarea('createDescription', '', NULL, 'Description', 80, 20);
		$createDescription = new input_textbox('createDescription', '/^.{1,1024}$/', NULL, 'Description:');
		if (!$createForm->submitted() || $createTitle->value == '') {
			$createForm->addObject($createTitle);
			$createForm->addObject($createDescription);
			$createForm->display();
		} else {
			$object = new $className(
				$foowd,
				$createTitle->value,
				$createDescription->value
			);
			if ($object->objectid != 0 && $object->save($foowd, FALSE)) {
				echo '<p>Workspace created and saved.</p>';
				echo '<p><a href="', getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className)))), '">Click here to view it now</a>.</p>';
			} else {
				trigger_error('Could not create workspace.');
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/*** METHODS ***/

/* view object */
	function method_view(&$foowd) {
		$foowd->track('foowd_workspace->method_view');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Viewing Workspace "', $this->getTitle(), '"</h1>';
		echo '<table>';
		echo '<tr><th>Title:</th><td>', $this->getTitle(), '</td></tr>';
		echo '<tr><th>Created:</th><td>', date(DATETIME_FORMAT, $this->created), '</td></tr>';
		echo '<tr><th>Author:</th><td>', $this->creatorName, '</td></tr>';
		echo '<tr><th>Access:</th><td>', $this->permissions['enter'], '</td></tr>';
		echo '</table>';
		echo '<p>', htmlspecialchars($this->description), '</p>';

		if ($foowd->user->workspaceid == $this->objectid) {
			echo '<p><a href="', getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'method' => 'enter')), '">Click here to leave this workspace</a></p>';
		} else {
			echo '<p><a href="', getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'method' => 'enter')), '">Click here to enter this workspace</a></p>';
		}
		
		echo '<h3>Objects Within Workspace</h3>';
		$objects = $foowd->retrieveObjects(array('workspaceid = '.$this->objectid), array('objectid', 'classid'), array('title'));
		echo '<p>';
		if ($objects) {
			echo '<table>';
			echo '<tr><th>Title</th><th>Created</th><th>Author</th><th>Object Type</th></tr>';
			while ($object = $foowd->retrieveObject($objects)) {
				echo '<tr>';
				echo '<td><a href="', getURI(array(
					'objectid' => $object->objectid,
					'classid' => $object->classid
				)), '">', $object->title, '</a></td>';
				echo '<td>', date(DATETIME_FORMAT, $object->created), '</td>';
				echo '<td>', $object->creatorName, '</td>';
				echo '<td>', getClassDescription($object->classid), '</td>';
				echo '</tr>';
			}
			echo '</table>';
		} else {
			echo 'There are no objects within "', $this->getTitle(), '".';
		}
		echo '</p>';
		
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}
	
/* delete object */
	function method_delete(&$foowd) {
		$foowd->track('foowd_workspace->method_delete');
		$objects = $foowd->retrieveObjects(
			array('workspaceid = '.$this->objectid)
		);
		if (!$objects) {
			parent::method_delete($foowd);
		} else {
			if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
			echo '<h1>Delete Workspace "', $this->getTitle(), '"</h1>';
			$uri = getURI(array(
				'objectid' => $this->objectid,
				'classid' => $this->classid,
				'method' => 'empty'
			));
			echo '<p>You can not delete this workspace since it still has objects within it. Either <a href="', $uri, '">delete</a> these objects or <a href="', $uri, '">move</a> them back into the base workspace.</p>';
			if (function_exists('foowd_append')) foowd_append($foowd, $this);
		}
		$foowd->track();
	}

/* enter workspace */
	function method_enter(&$foowd) {
		$foowd->track('foowd_workspace->method_enter');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		
		if ($foowd->user->workspaceid == $this->objectid) { // leave workspace
			$foowd->user->workspaceid = 0;
			if ($foowd->user->save($foowd, FALSE)) {
				echo '<p>You have now left the ', $this->getTitle(), ' workspace and re-entered outside.</p>';
			} else {
				trigger_error('Could not update user.');
			}
		} else { // enter workspace
			$foowd->user->workspaceid = $this->objectid;
			if ($foowd->user->save($foowd, FALSE)) {
				echo '<p>You have now entered the ', $this->getTitle(), ' workspace.</p>';
			} else {
				trigger_error('Could not update user.');
			}
		}
		
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* fill workspace */
	function method_fill(&$foowd) {
		$foowd->track('foowd_workspace->method_fill');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

		echo '<h1>Fill Workspace "', $this->getTitle(), '"</h1>';

		foreach (get_declared_classes() as $className) {
			if (substr($className, 0, 6) == 'foowd_' && $className != 'foowd_anonuser') {
				$items[strval(crc32(strtolower($className)))] = substr($className, 6);
			}
		}
		$limitForm = new input_form('limitForm', NULL, 'POST', 'Limit Selection', NULL);
		$limitSelect = new input_dropdown('limitSelect', NULL, $items, 'Class Types:', 6, TRUE);
		$beforeDay = new input_textbox('beforeDay', '/^[1-3]?[0-9]$/', NULL, NULL, 2, 2);
		$beforeMonth = new input_textbox('beforeMonth', '/^[0|1]?[0-9]$/', NULL, NULL, 2, 2);
		$beforeYear = new input_textbox('beforeYear', '/^[1|2][0-9]{3}$/', NULL, NULL, 4, 4);
		$afterDay = new input_textbox('afterDay', '/^[1-3]?[0-9]$/', NULL, NULL, 2, 2);
		$afterMonth = new input_textbox('afterMonth', '/^[0|1]?[0-9]$/', NULL, NULL, 2, 2);
		$afterYear = new input_textbox('afterYear', '/^[1|2][0-9]{3}$/', NULL, NULL, 4, 4);

		$whereClause = NULL;
		if ($limitForm->submitted()) {
			if (is_array($limitSelect->value)) {
				$classArray[] = 'OR';
				foreach ($limitSelect->value as $classid) {
					$classArray[] = 'classid = '.$classid;
				}
				$whereClause[] = $classArray;
			}
			//if (isset($beforeDay->value) || isset($beforeMonth->value) || isset($beforeYear->value)) {
			if (checkdate($beforeMonth->value, $beforeDay->value, $beforeYear->value)) {
				$whereClause[] = 'updated > "'.date(DATABASE_DATE, mktime(0, 0, 0, $beforeMonth->value, $beforeDay->value, $beforeYear->value)).'"';
			}
			//if (isset($afterDay->value) || isset($afterMonth->value) || isset($afterYear->value)) {
			if (checkdate($afterMonth->value, $afterDay->value, $afterYear->value)) {
				$whereClause[] = 'updated < "'.date(DATABASE_DATE, mktime(0, 0, 0, $afterMonth->value, $afterDay->value, $afterYear->value)).'"';
			}
			if (count($whereClause) > 0) {
				array_unshift($whereClause, 'AND');
			}
		}
		
		$objectForm = new input_form('objectForm', NULL, 'POST', 'Clone Objects', NULL, 'Move Objects');

		$objects = array();
		$query = $foowd->retrieveObjects(
			$whereClause,
			NULL,
			array('title', 'classid')
		);
		if ($query) {
			while ($object = $foowd->retrieveObject($query)) {
				if (isset($object->permissions['clone']) && $foowd->user->inGroup($object->permissions['clone']) && ($object->classid != $this->classid || $object->objectid != $this->objectid)) {
					$objects[$object->objectid.'_'.$object->classid.'_'.$object->version] = $object;
				}
			}
			$items = NULL;
			$error = FALSE;
			foreach ($objects as $object) {
				$items[$object->objectid.'_'.$object->classid.'_'.$object->version] = $object->getTitle().' ('.getClassName($object->classid).' v'.$object->version.')';
			}
			$objectSelect = new input_dropdown('objectSelect', NULL, $items, 'Select Objects:', 10, TRUE);
			
			if ($objectForm->submitted() || $objectForm->previewed()) {
				$selectedObjects = NULL;
				foreach ($objectSelect->value as $value) {
					$objectDetails = explode('_', $value);
					$selectedObjects[intval($objectDetails[0])][intval($objectDetails[1])][intval($objectDetails[2])] = TRUE;
				}
				foreach ($objects as $object) {
					if (isset($selectedObjects[intval($object->objectid)][intval($object->classid)][intval($object->version)])) {
						$object->workspaceid = $this->objectid;
						if ($objectForm->submitted()) { // adjust original workspace so as to create new object rather than overwrite old one.
							$object->foowd_original_access_vars['objectid'] = $object->objectid;
							$object->foowd_original_access_vars['workspaceid'] = $object->workspaceid;
						}
						if (!$object->save($foowd, FALSE)) {
							$error = TRUE;
							trigger_error('Could not clone/move object "', $object->getTitle(), '".');
						}
					}
				}
			}
			if ($objectForm->submitted()) {
				if (!$error) {
					echo '<p>The selected objects have been cloned to workspace "', $this->getTitle(), '".</p>';
				} else {
					trigger_error('Not all the objects could be cloned correctly.');
				}
			} elseif ($objectForm->previewed()) {
				if (!$error) {
					echo '<p>The selected objects have been moved to workspace "', $this->getTitle(), '".</p>';
				} else {
					trigger_error('Not all the objects could be moved correctly.');
				}
			} else {
				$limitForm->display_start();
				$limitSelect->display();
				echo ' Show between ';
				$beforeDay->display();
				$beforeMonth->display();
				$beforeYear->display();
				echo ' and ';
				$afterDay->display();
				$afterMonth->display();
				$afterYear->display();
				echo ' (dd/mm/yyyy)<br />';
				$limitForm->display_end();
				if ($items) {
					$objectForm->addObject($objectSelect);
					$objectForm->display();
				} else {
					echo '<p>There are not any objects that you have permission to clone.</p>';
				}
			}
		} else {
			$limitForm->addObject($limitSelect);
			$limitForm->display();
			echo '<p>There are not any objects that you have permission to clone.</p>';
		}
		
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* empty workspace */
	function method_empty(&$foowd) {
		$foowd->track('foowd_workspace->method_empty');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		
		echo '<h1>Empty Workspace "', $this->getTitle(), '"</h1>';

		$objectForm = new input_form('objectForm', NULL, 'POST', 'Move Objects', NULL, 'Delete Objects');

		$objects = array();
		$query = $foowd->retrieveObjects(
			array('AND', 'workspaceid = '.$this->objectid, 'classid != '.USER_CLASS_ID),
			NULL,
			array('title', 'classid')
		);
		if ($query) {
			while ($object = $foowd->retrieveObject($query)) {
				if (isset($object->permissions['clone']) && $foowd->user->inGroup($object->permissions['clone']) && ($object->classid != $this->classid || $object->objectid != $this->objectid)) {
					$objects[$object->objectid.'_'.$object->classid.'_'.$object->version] = $object;
				}
			}
			$items = NULL;
			$error = FALSE;
			foreach ($objects as $object) {
				if (isset($object->permissions['clone']) && $foowd->user->inGroup($object->permissions['clone'])) {
					$items[$object->objectid.'_'.$object->classid.'_'.$object->version] = $object->getTitle().' ('.getClassName($object->classid).' v'.$object->version.')';
				}
			}

			$objectSelect = new input_dropdown('objectSelect', NULL, $items, 'Select Objects:', 10, TRUE);
			
			if ($objectForm->submitted() || $objectForm->previewed()) {
				if (is_array($objectSelect->value)) {
					$selectedObjects = NULL;
					foreach ($objectSelect->value as $value) {
						$objectDetails = explode('_', $value);
						$selectedObjects[intval($objectDetails[0])][intval($objectDetails[1])][intval($objectDetails[2])] = TRUE;
					}
					foreach ($objects as $object) {
						if (isset($selectedObjects[intval($object->objectid)][intval($object->classid)][intval($object->version)])) {
							if ($objectForm->submitted()) {
								$object->workspaceid = 0;
								$object->foowd_original_access_vars['workspaceid'] = 0;
								if ($object->save($foowd, TRUE)) {
									$object->workspaceid = $this->objectid;
									$object->delete($foowd);
								} else {
									$error = TRUE;
									trigger_error('Could not move object "'.$object->getTitle().'".');
								}
							} elseif ($objectForm->previewed()) {
								if (!$object->delete($foowd)) {
									$error = TRUE;
									trigger_error('Could not delete object "'.$object->getTitle().'".');
								}
							}
						}
					}
				} else {
					$error = TRUE;
					trigger_error('You must select at least one object.');
				}
			}
			
			if ($objectForm->submitted()) {
				if (!$error) {
					echo '<p>The selected objects have been moved back to the base workspace.</p>';
				} else {
					trigger_error('Not all the objects could be moved correctly.');
				}
			} elseif ($objectForm->previewed()) {
				if (!$error) {
					echo '<p>The selected objects have been deleted.</p>';
				} else {
					trigger_error('Not all the objects could be deleted.');
				}
			} elseif ($items) {
				$objectSelect = new input_dropdown('objectSelect', NULL, $items, 'Select Objects:', 10, TRUE);
				$objectForm->addObject($objectSelect);
				$objectForm->display();
			} else {
				echo '<p>You do not have permission to clone any objects within this workspace.</p>';
			}
		} else {
			echo '<p>The workspace is already empty.</p>';
		}
				
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* export workspace */
	function method_export(&$foowd) {
		$objects = $foowd->retrieveObjects(array('workspaceid = '.$this->objectid));
		if ($objects) {
			$foowd->debug = FALSE;
			header('Content-type: text/plain');
			header('Content-Disposition: attachment; filename='.$this->getTitle().'.txt');
			while ($record = getRecord($objects)) {
				if ($record) {
					echo base64_encode($record['object']), "\n";
				}
			}
		} else {
			$foowd->track('foowd_workspace->method_export');
			if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
			echo '<p>This workspace contains no objects, there is nothing to export.</p>';
			if (function_exists('foowd_append')) foowd_append($foowd, $this);
			$foowd->track();
		}
	}

/* import workspace */
	function method_import(&$foowd) {
		$foowd->track('foowd_workspace->method_import');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

		echo '<h1>Import Into Workspace "', $this->getTitle(), '"</h1>';

		$importForm = new input_form('importForm', NULL, 'POST', 'Import', NULL);
		$importFile = new input_file('importFile', 'Import file:', NULL, getConstOrDefault('INPUT_FILE_SIZE_MAX', 2097152));
		
		if ($importForm->submitted()) {
			if ($importFile->isUploaded()) {
				if ($importFile->file['type'] == 'text/plain') {
					$fp = fopen($importFile->file['tmp_name'], 'r');
					if ($fp) {
						echo '<p>Importing objects, this may take some time, please wait.</p>';
						flush();
						$buffer = '';
						$foo = 0;
						while (!feof($fp)) {
							$buffer .= fgets($fp, 4096);
							if ((substr($buffer, -1) == "\n" || feof($fp)) && $buffer != '') {
								$foo++;
								$object = unserialize(base64_decode($buffer));
								if (is_object($object)) {
									$object->workspaceid = $this->objectid;
									$object->foowd_original_access_vars['workspaceid'] = $this->objectid;
									if ($object->save($foowd, TRUE, FALSE)) {
										echo 'Object "', $object->getTitle(), '" imported into workspace.<br />';
										flush();
									} else {
										trigger_error('Could not save object "'.$object->getTitle().'" from line '.$foo.' of import file.');
									}
								} else {
									trigger_error('Could not create object from line '.$foo.' of import file.');
								}
								$buffer = '';
							}
						}
						fclose($fp);
					}
					echo '<p>Import complete. <a href="', getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'method' => 'view')), '">Click here to view the workspace</a>.</p>';
				} else {
					trigger_error('Incorrect file type.');
				}
			} else {
				trigger_error($importFile->getError());
			}
		} else {
			$importForm->addObject($importFile);
			$importForm->display();
		}

		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

}

?>