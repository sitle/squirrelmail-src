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

define('WORKSPACE_CLASS_ID', -679419151);

/** CLASS DESCRIPTOR **/
$foowd_class_meta[WORKSPACE_CLASS_ID]['className'] = 'foowd_workspace';
$foowd_class_meta[WORKSPACE_CLASS_ID]['description'] = 'Workspace';

/** CLASS METHOD PERMISSIONS **/
define('FOOWD_WORKSPACE_CREATE_PERMISSION', 'Gods');

/** CLASS METHOD PASSTHRU FUNCTION **/
function foowd_workspace_classmethod(&$foowd, $methodName) { 
    foowd_workspace::$methodName($foowd, 'foowd_workspace'); 
}

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
		$enterGroup = NULL
	) {

// base object constructor
		parent::foowd_object($foowd, $title, $viewGroup, $adminGroup, $deleteGroup);

/* set object vars */
		$this->description = $description;

/* set method permissions */
		$this->permissions['enter'] = setVarConstOrDefault($enterGroup, 'DEFAULT_WORKSPACE_GROUP', 'Gods');

	}

/*** SERIALIZE FUNCTIONS ***/

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['description'] = '/.{1024}/';
	}

/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new workspace</h1>';
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, NULL, 'Title:');
		$createDescription = new input_textarea('createDescription', '', NULL, 'Description', 80, 20);
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
			if ($object->save($foowd, FALSE)) {
				echo '<p>Workspace created and saved.</p>';
			} else {
				echo '<p>Could not create workspace.</p>';
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
	}

/*** METHODS ***/

/* view object */
	function method_view(&$foowd) {
		global $foowd_class_meta;
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
		$objects = $foowd->getObjects(array('workspaceid = '.$this->objectid), array('title'));
		echo '<p><table>';
		echo '<tr><th>Title</th><th>Created</th><th>Author</th><th>Object Type</th></tr>';
		foreach ($objects as $object) {
			echo '<tr>';
			echo '<td><a href="', getURI(array(
				'objectid' => $object->objectid,
				'classid' => $object->classid
			)), '">', $object->title, '</a></td>';
			echo '<td>', date(DATETIME_FORMAT, $object->created), '</td>';
			echo '<td>', $object->creatorName, '</td>';
			echo '<td>', $foowd_class_meta[(int)$object->classid]['description'], '</td>';
			echo '</tr>';
		}
		echo '</table>';
		
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
	}

/* enter workspace */
	function method_enter(&$foowd) {
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		
		if ($foowd->user->workspaceid == $this->objectid) { // leave workspace
			$foowd->user->workspaceid = 0;
			echo '<p>You have now left the ', $this->getTitle(), ' workspace and re-entered outside.</p>';
		} else { // enter workspace
			$foowd->user->workspaceid = $this->objectid;
			echo '<p>You have now entered the ', $this->getTitle(), ' workspace.</p>';
		}
		$foowd->user->save($foowd, FALSE);
		
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
	}

}

?>
