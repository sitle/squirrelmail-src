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
class.definition.php
Dynamic object class
*/

/*** IMPORTANT ***

This FOOWD class is special in the scheme of things. It allows you to define FOOWD
classes within objects, and FOOWD will handle the fetching of the class definition
automatically upon retrieval of an object instance of that class. For this to happen,
this class is tied to three elements within the FOOWD system core:

$FOOWD_LOADCLASSCALLBACK
	This global variable contains a reference to the active environment object. You
	need to define it within your lead-in document if you want to use dynamic classes.
	eg. $FOOWD_LOADCLASSCALLBACK = &$foowd;

loadClassCallback()
	This function is the callback function called when an object instance of a type
	that isn't defined is tried to be unserialized from the storage medium. It uses
	$FOOWD_LOADCLASSCALLBACK to know which environment it is supposed to be working
	with and calls foowd::loadClass() to load the class definition.

foowd::loadClass()
	This member function of the environment class is used to load classes from the
	database and make them live within the system.

*/

/** METHOD PERMISSIONS **/
if (!defined('PERMISSION_FOOWD_DEFINITION_OBJECT_EDIT')) define('PERMISSION_FOOWD_DEFINITION_OBJECT_EDIT', 'Gods');

/** CLASS DESCRIPTOR **/
if (!defined('META_-960802448_CLASSNAME')) define('META_-960802448_CLASSNAME', 'foowd_definition');
if (!defined('META_-960802448_DESCRIPTION')) define('META_-960802448_DESCRIPTION', 'Class Definition');

// set class id const used by foowd::loadClass()
if (!defined('DEFINITION_CLASS_ID')) define('DEFINITION_CLASS_ID', -960802448); // id of dynamic object class

/** CLASS DECLARATION **/
class foowd_definition extends foowd_object {

	var $description; // text description of class
	var $body = '';
	
/*** CONSTRUCTOR ***/

	function foowd_definition(
		&$foowd,
		$title = NULL,
		$description = NULL,
		$body = NULL,
		$viewGroup = NULL,
		$adminGroup = NULL,
		$deleteGroup = NULL,
		$editGroup = NULL
	) {
		$foowd->track('foowd_definition->constructor');
	
// base object constructor
		parent::foowd_object($foowd, $title, $viewGroup, $adminGroup, $deleteGroup);

/* set object vars */
		$this->description = $description;
		$this->body = $body;

/* set method permissions */
		$className = get_class($this);
		$this->permissions['edit'] = getPermission($className, 'edit', 'object'. $editGroup);

		$foowd->track();
	}

/*** SERIALIZE FUNCTIONS ***/

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['description'] = '/^.{1,1024}$/';
		$this->foowd_vars_meta['body'] = '';
	}
	
/*** MEMBER FUNCTIONS ***/

/* delete object */

	function delete(&$foowd) { // we have to clean up any objects of our type as well, so do that and then call foowd_object::delete to do the actual delete
		$foowd->track('foowd_definition->delete');
		$conditionArray = array(
			'AND',
			'classid = '.$this->objectid,
		);
		if ($this->workspaceid == 0) {
			$conditionArray[] = 'workspaceid = 0';
		} else {
			$conditionArray[] = array(
				'OR',
				'workspaceid = '.$this->workspaceid,
				'workspaceid = 0'
			);
		}
		if (DBDelete($foowd, $conditionArray)) {
			$foowd->track(); return parent::delete($foowd);
		} else {
			$foowd->track(); return FALSE;
		}
	}

/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		$foowd->track('foowd_definition->class_create');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new class</h1>';
		$queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, 'Class Title:');
		$createDescription = new input_textbox('createDescription', '/^.{1,1024}$/', NULL, 'Description:');
		$createClass = new input_textarea('createClass', '', NULL, NULL, 80, 20);

		if (!$createForm->submitted() || $createTitle->value == '') {
			$createForm->addObject($createTitle);
			$createForm->addObject($createDescription);
			$createForm->addObject($createClass);
			$createForm->display();
		} else {
			$object = new $className(
				$foowd,
				$createTitle->value,
				$createDescription->value,
				$createClass->value
			);
			if ($object->objectid != 0 && $object->save($foowd, FALSE)) {
				echo '<p>Class created and saved.</p>';
				echo '<p><a href="', getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className)), 'method' => 'edit')), '">Click here to edit it now</a>.</p>';
			} else {
				trigger_error('Could not create class.');
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/*** METHODS ***/

/* view object */
	function method_view(&$foowd) {
		$foowd->track('foowd_definition->method_view');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Class "', $this->getTitle(), '"</h1>';
		echo '<p>This object is a dynamic class definition and can not be viewed.</p>';
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* view source */
	function method_source(&$foowd) {
		$foowd->track('foowd_definition->method_source');
		$code = "<?php\n";
		$code .= "/** CLASS DESCRIPTOR **/\n";
		$code .= "if (!defined('META_".$this->objectid."_CLASSNAME')) define('META_".$this->objectid."_CLASSNAME', '".$this->title."');\n";
		$code .= "if (!defined('META_".$this->objectid."_DESCRIPTION')) define('META_".$this->objectid."_DESCRIPTION', '".$this->description."');\n";
		$code .= $this->body."\n";
		$code .= "?>\n";
		highlight_string($code);
		$foowd->track();
	}

/* edit object */
	function method_edit(&$foowd) {
		$foowd->track('foowd_definition->method_edit');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Editing version ', $this->version, ' of "', $this->getTitle(), '"</h1>';
		$editForm = new input_form('editForm', NULL, 'POST', 'Save', NULL, 'Syntax Check');
		$editCollision = new input_hiddenbox('editCollision', REGEX_DATETIME, time());
		if ($editCollision->value >= $this->updated && $editForm->submitted()) { // if we're going to update, reset collision detect
			$editCollision->set(time());		
		}
		$editForm->addObject($editCollision);
		
		$editDescription = new input_textbox('editDescription', $this->foowd_vars_meta['description'], $this->description, 'Description:');
		$editClass = new input_textarea('editClass', $this->foowd_vars_meta['body'], $this->body, NULL, 80, 20);
		
		$editForm->addObject($editDescription);
		$editForm->addObject($editClassPermissions);
		$editForm->addObject($editClass);
		
		if (isset($foowd->user->objectid) && $this->updatorid == $foowd->user->objectid) { // author is same as last author and not anonymous, so can just update
			$newVersion = new input_checkbox('newVersion', TRUE, 'Do not archive previous version?');
			$editForm->addObject($newVersion);
		}
		$editForm->display();

		if ($editForm->submitted()) {
			if ($editCollision->value >= $this->updated) { // has not been changed since form was loaded
				$this->body = $editClass->value;
				if (isset($newVersion)) {
					$createNewVersion = !$newVersion->checked;
				} else {
					$createNewVersion = TRUE;
				}
				if ($this->save($foowd, $createNewVersion)) {
					echo '<p>Class updated and saved.</p>';
				} else {
					trigger_error('Could not save class.');
				}
			} else { // edit collision!
				echo '<h3>Warning: This object has been updated by another user since you started editing, please reload the edit page and verify their changes before continuing to edit.</h3>';
			}
		} elseif ($editForm->previewed()) {
			echo '<h3>Syntax Check</h3>';
			if (eval($editClass->value) !== FALSE) {
				echo '<p>Code syntax is correct.</p>';
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* delete object */

	function method_delete(&$foowd) { // this is only here due to changed wording, otherwise it is identical to foowd_object::method_delete()
		$foowd->track('foowd_definition->method_delete');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Delete Class "', $this->getTitle(), '"</h1>';
		$confirm = new input_querystring('confirm', '/^[y]$/', FALSE);
		if ($confirm->value) {
			if ($this->delete($foowd)) {
				echo '<p>Class deleted.</p>';
			} else {
				trigger_error('Unable to delete class.');
			}
		} else {
			echo '<p>Are you sure? This will delete all object instances of this class type too.</p>';
			echo '<p>';
			echo '<a href="', getURI(array('method' => 'delete', 'objectid' => $this->objectid, 'classid' => $this->classid, 'confirm' => 'y')), '">YES, delete "', $this->getTitle(), '" and all object instances too.</a><br />';
			echo '<a href="', getURI(array('method' => 'view', 'objectid' => $this->objectid, 'classid' => $this->classid)), '">NO, I made a mistake, leave it as it is.</a>';
			echo '</p>';
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

}
?>