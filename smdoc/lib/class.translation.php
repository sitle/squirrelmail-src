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
setPermission('foowd_translation', 'class', 'create', 'Translator');
setPermission('foowd_translation', 'object', 'enter', 'Everyone');
setPermission('foowd_translation', 'object', 'fill', 'Translator');
setPermission('foowd_translation', 'object', 'empty', 'Translator');
setPermission('foowd_translation', 'object', 'export', 'Gods');
setPermission('foowd_translation', 'object', 'import', 'Gods');

/** CLASS DESCRIPTOR **/
setClassMeta('foowd_translation', 'Site Translation');

setConst('WORKSPACE_CLASS_ID', META_FOOWD_TRANSLATION_CLASS_ID);
setConst('TRANSLATION_CLASS_ID', META_FOOWD_TRANSLATION_CLASS_ID);

/** CLASS DECLARATION **/
class foowd_translation extends foowd_workspace {

	var $language_icon;
	
/*** CONSTRUCTOR ***/

	function foowd_translation(
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
		$importGroup = NULL,
		$icon = NULL
	) {
		$foowd->track('foowd_translation->constructor');

// base object constructor
		parent::foowd_workspace($foowd, $title, $description, 
		            $viewGroup, $adminGroup, $deleteGroup,
		            $fillGroup, $emptyGroup, $exportGroup, $importGroup);

/* set object vars */
		$this->language_icon = $icon;

		$foowd->track();
	}

/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		$foowd->track('foowd_workspace->class_create');
        if ( $foowd->user->workspaceid != 0 ) {
            trigger_error('Can not create nested translations. Return to the main workspace to create another translation');
		}


		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this, _("Create New Translation"));
?>
<p>This site is translated using 
<a href="http://www.gnu.org/manual/gettext/html_mono/gettext.html">gettext</a>.</p>

<p>To create a new translation, first create a workspace identified by standard
   <a href="http://www.gnu.org/manual/gettext/html_mono/gettext.html#SEC221">Language</a> 
   and <a href="http://www.gnu.org/manual/gettext/html_mono/gettext.html#SEC222">Country Codes</a>
</p>
<?php
		$queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
		$createForm = new input_form('createForm', NULL, 'POST', _("Create"), NULL);
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value);
		$createDescription = new input_textbox('createDescription', '/^.{1,1024}$/', NULL, NULL, NULL, NULL, NULL, FALSE);
		if (!$createForm->submitted() || $createTitle->value == '') {
		    $table = new input_table();
			$table->addObject(_("Language/Country Code ") . '(de_DE, es_ES)', $createTitle);
			$table->addObject(_("Language/Country in English (German/Germany, Spanish/Spain)"), $createDescription);
			$createForm->addObject($table);
			$createForm->display();
		} else {
			$object = new $className(
				$foowd,
				$createTitle->value,
				$createDescription->value
			);
			if ($object->objectid != 0 && $object->save($foowd, FALSE)) {
				echo '<p>', _("Workspace created and saved."), '</p>';
				echo '<p>', sprintf(_('<a href="%s">Click here to view it now</a>.'), getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className))))), '</p>';
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

		echo '<table>';
		echo '<tr><th>', _("Title"), ':</th><td>', $this->getTitle(), '</td></tr>';
		echo '<tr><th>', _("Created"), ':</th><td>', date(DATETIME_FORMAT, $this->created), '</td></tr>';
		echo '<tr><th>', _("Author"), ':</th><td>', $this->creatorName, '</td></tr>';
		echo '<tr><th>', _("Access"), ':</th><td>', $this->permissions['enter'], '</td></tr>';
		echo '</table>';
		echo '<p>', htmlspecialchars($this->description), '</p>';

		if ($foowd->user->workspaceid == $this->objectid) {
			echo '<p>', sprintf(_('<a href="%s">Click here to leave this workspace</a>'), getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'method' => 'enter'))), '</p>';
		} else {
			echo '<p>', sprintf(_('<a href="%s">Click here to enter this workspace</a>'), getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'method' => 'enter'))), '</p>';
		}
		
		echo '<h3>', _("Objects Within Workspace"), '</h3>';
		$objects = $foowd->retrieveObjects(array('workspaceid = '.$this->objectid), array('objectid', 'classid'), array('title'));
		echo '<p>';
		if ($objects) {
			echo '<table>';
			echo '<tr><th>', _("Title"), '</th><th>', _("Created"), '</th><th>', _("Author"), '</th><th>', _("Object Type"), '</th></tr>';
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
			printf(_("There are no objects within %s."), $this->getTitle());
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
			if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this, $this->title);

			$uri = getURI(array(
				'objectid' => $this->objectid,
				'classid' => $this->classid,
				'method' => 'empty'
			));
			echo '<p>', sprintf(_('You can not delete this workspace since it still has objects within it. Either <a href="%s">delete</a> these objects or <a href="%s">move</a> them back into the base workspace.'), $uri, $uri), '</p>';
			if (function_exists('foowd_append')) foowd_append($foowd, $this);
		}
		$foowd->track();
	}

/* enter workspace */
	function method_enter(&$foowd) {
		$foowd->track('foowd_workspace->method_enter');
		if ($foowd->user->workspaceid == $this->objectid) { // leave workspace
			$foowd->user->workspaceid = 0;
			if ($foowd->user->save($foowd, FALSE)) {
				header('Location: '.getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version)));
			} else {
				trigger_error('Could not update user.');
			}
		} else { // enter workspace
			$foowd->user->workspaceid = $this->objectid;
			if ($foowd->user->save($foowd, FALSE)) {
				header('Location: '.getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'version' => $this->version)));
			} else {
				trigger_error('Could not update user.');
			}
		}
		$foowd->track();
	}

/* fill workspace */
	function method_fill(&$foowd) {
		$foowd->track('foowd_workspace->method_fill');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

		foreach (get_declared_classes() as $className) {
			if (substr($className, 0, 6) == 'foowd_' && $className != 'foowd_anonuser') {
				$items[strval(crc32(strtolower($className)))] = substr($className, 6);
			}
		}
		$limitForm = new input_form('limitForm', NULL, 'POST', _("Limit Selection"), NULL);
		$limitSelect = new input_dropdown('limitSelect', NULL, $items, _("Class Types").':', 6, TRUE);
		$beforeDay = new input_textbox('beforeDay', '/^[1-3]?[0-9]$/', NULL, NULL, 2, 2, NULL, FALSE);
		$beforeMonth = new input_textbox('beforeMonth', '/^[0|1]?[0-9]$/', NULL, NULL, 2, 2, NULL, FALSE);
		$beforeYear = new input_textbox('beforeYear', '/^[1|2][0-9]{3}$/', NULL, NULL, 4, 4, NULL, FALSE);
		$afterDay = new input_textbox('afterDay', '/^[1-3]?[0-9]$/', NULL, NULL, 2, 2, NULL, FALSE);
		$afterMonth = new input_textbox('afterMonth', '/^[0|1]?[0-9]$/', NULL, NULL, 2, 2, NULL, FALSE);
		$afterYear = new input_textbox('afterYear', '/^[1|2][0-9]{3}$/', NULL, NULL, 4, 4, NULL, FALSE);

		$whereClause = NULL;
		if ($limitForm->submitted()) {
			if (is_array($limitSelect->value)) {
				$classArray[] = 'OR';
				foreach ($limitSelect->value as $classid) {
					$classArray[] = 'classid = '.$classid;
				}
				$whereClause[] = $classArray;
			}
			if (checkdate($beforeMonth->value, $beforeDay->value, $beforeYear->value)) {
				$whereClause[] = 'updated > "'.date(DATABASE_DATE, mktime(0, 0, 0, $beforeMonth->value, $beforeDay->value, $beforeYear->value)).'"';
			}
			if (checkdate($afterMonth->value, $afterDay->value, $afterYear->value)) {
				$whereClause[] = 'updated < "'.date(DATABASE_DATE, mktime(0, 0, 0, $afterMonth->value, $afterDay->value, $afterYear->value)).'"';
			}
			if (count($whereClause) > 0) {
				array_unshift($whereClause, 'AND');
			}
		}
		
		$objectForm = new input_form('objectForm', NULL, 'POST', _("Clone Objects"), NULL, _("Move Objects"));

		$objects = array();
		$query = $foowd->retrieveObjects(
			$whereClause,
			NULL,
			array('title', 'classid')
		);
		if ($query) {
			while ($object = $foowd->retrieveObject($query)) {
				if (!isset($object->permissions['clone']) || $foowd->user->inGroup($object->permissions['clone']) && ($object->classid != $this->classid || $object->objectid != $this->objectid)) {
					$objects[$object->objectid.'_'.$object->classid.'_'.$object->version] = $object;
				}
			}
			$items = NULL;
			$error = FALSE;
			foreach ($objects as $object) {
				$items[$object->objectid.'_'.$object->classid.'_'.$object->version] = $object->getTitle().' ('.getClassName($object->classid).' v'.$object->version.')';
			}
			$objectSelect = new input_dropdown('objectSelect', NULL, $items, _("Select Objects").':', 10, TRUE);
			
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
					echo '<p>', sprintf(_("The selected objects have been cloned to workspace %s."), $this->getTitle()), '</p>';
				} else {
					trigger_error('Not all the objects could be cloned correctly.');
				}
			} elseif ($objectForm->previewed()) {
				if (!$error) {
					echo '<p>', sprintf(_("The selected objects have been moved to workspace %s."), $this->getTitle()), '</p>';
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
					echo '<p>', _("There are not any objects that you have permission to clone."), '</p>';
				}
			}
		} else {
			$limitForm->addObject($limitSelect);
			$limitForm->display();
			echo '<p>', _("There are not any objects that you have permission to clone."), '</p>';
		}
		
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* empty workspace */
	function method_empty(&$foowd) {
		$foowd->track('foowd_workspace->method_empty');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		
		$objectForm = new input_form('objectForm', NULL, 'POST', _("Move Objects"), NULL, _("Delete Objects"));

		$objects = array();
		$query = $foowd->retrieveObjects(
			array('AND', 'workspaceid = '.$this->objectid, 'classid != '.USER_CLASS_ID),
			NULL,
			array('title', 'classid')
		);
		if ($query) {
			while ($object = $foowd->retrieveObject($query)) {
				if (!isset($object->permissions['clone']) || $foowd->user->inGroup($object->permissions['clone']) && ($object->classid != $this->classid || $object->objectid != $this->objectid)) {
					$objects[$object->objectid.'_'.$object->classid.'_'.$object->version] = $object;
				}
			}
			$items = NULL;
			$error = FALSE;
			foreach ($objects as $object) {
				if (!isset($object->permissions['clone']) || $foowd->user->inGroup($object->permissions['clone'])) {
					$items[$object->objectid.'_'.$object->classid.'_'.$object->version] = $object->getTitle().' ('.getClassName($object->classid).' v'.$object->version.')';
				}
			}

			$objectSelect = new input_dropdown('objectSelect', NULL, $items, _("Select Objects").':', 10, TRUE);
			
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
					echo '<p>', _("The selected objects have been moved back to the base workspace."), '</p>';
				} else {
					trigger_error('Not all the objects could be moved correctly.');
				}
			} elseif ($objectForm->previewed()) {
				if (!$error) {
					echo '<p>', _("The selected objects have been deleted."), '</p>';
				} else {
					trigger_error('Not all the objects could be deleted.');
				}
			} elseif ($items) {
				$objectSelect = new input_dropdown('objectSelect', NULL, $items, _("Select Objects").':', 10, TRUE);
				$objectForm->addObject($objectSelect);
				$objectForm->display();
			} else {
				echo '<p>', _("You do not have permission to clone any objects within this workspace."), '</p>';
			}
		} else {
			echo '<p>', _("The workspace is already empty."), '</p>';
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
			echo '<p>', _('This workspace contains no objects, there is nothing to export.'), '</p>';
			if (function_exists('foowd_append')) foowd_append($foowd, $this);
			$foowd->track();
		}
	}

/* import workspace */
	function method_import(&$foowd) {
		$foowd->track('foowd_workspace->method_import');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);

		$importForm = new input_form('importForm', NULL, 'POST', _("Import"), NULL);
		$importFile = new input_file('importFile', _("Import file").':', NULL, getConstOrDefault('INPUT_FILE_SIZE_MAX', 2097152));
		
		if ($importForm->submitted()) {
			if ($importFile->isUploaded()) {
				if ($importFile->file['type'] == 'text/plain') {
					$fp = fopen($importFile->file['tmp_name'], 'r');
					if ($fp) {
						echo '<p>', _('Importing objects, this may take some time, please wait.'), '</p>';
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
										printf(_('Object "%s" imported into workspace.<br />'), $object->getTitle());
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
					echo '<p>', sprintf(_('Import complete. <a href="%s">Click here to view the workspace</a>.'), getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'method' => 'view'))), '</p>';
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