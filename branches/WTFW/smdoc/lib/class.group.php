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
class.group.php
User group class
*/

/** METHOD PERMISSIONS **/
if (!defined('PERMISSION_FOOWD_GROUP_OBJECT_ADD')) define('PERMISSION_FOOWD_GROUP_OBJECT_ADD', 'Author');
if (!defined('PERMISSION_FOOWD_GROUP_OBJECT_REMOVE')) define('PERMISSION_FOOWD_GROUP_OBJECT_REMOVE', 'Author');

/** CLASS DESCRIPTOR **/
if (!defined('META_-7993958_CLASSNAME')) define('META_-7993958_CLASSNAME', 'foowd_group');
if (!defined('META_-7993958_DESCRIPTION')) define('META_-7993958_DESCRIPTION', 'User Group');

if (!defined('GROUP_CLASS_ID')) define('GROUP_CLASS_ID', -7993958);

/** CLASS DECLARATION **/
class foowd_group extends foowd_object {

	var $description;
	
/*** CONSTRUCTOR ***/

	function foowd_group(
		&$foowd,
		$title = NULL,
		$description = NULL,
		$viewGroup = NULL,
		$adminGroup = NULL,
		$deleteGroup = NULL,
		$addGroup = NULL,
		$removeGroup = NULL
	) {
		$foowd->track('foowd_group->constructor');

// base object constructor
		parent::foowd_object($foowd, $title, $viewGroup, $adminGroup, $deleteGroup);

/* set object vars */
		$this->description = $description;

/* set method permissions */
		$className = get_class($this);
		$this->permissions['add'] = getPermission($className, 'add', 'object'. $addGroup);
		$this->permissions['remove'] = getPermission($className, 'remove', 'object'. $removeGroup);

		$foowd->track();	
	}

/*** SERIALIZE FUNCTIONS ***/

	function __wakeup() {
		parent::__wakeup();
		$this->foowd_vars_meta['description'] = '/^.{1,1024}$/';
	}

/*** MEMBER FUNCTIONS ***/

	function getUsersInGroup(&$foowd) {
		$users = NULL;
		$objects = $foowd->retrieveObjects(
			array('classid = '.USER_CLASS_ID),
			NULL,
			array('title')
		);
		if ($objects) {
			while ($object = $foowd->retrieveObject($objects)) {
				if (in_array($this->objectid, $object->groups)) {
					$users[$object->objectid] = $object;
				}
			}
		}
		return $users;
	}
	
	function getUsersNotInGroup(&$foowd) {
		$users = NULL;
		$objects = $foowd->retrieveObjects(
			array('classid = '.USER_CLASS_ID),
			NULL,
			array('title')
		);
		if ($objects) {
			while ($object = $foowd->retrieveObject($objects)) {
				if (!in_array($this->objectid, $object->groups)) {
					$users[$object->objectid] = $object;
				}
			}
		}
		return $users;
	}
	
/*** CLASS METHODS ***/

/* create object */

	function class_create(&$foowd, $className) {
		$foowd->track('foowd_group->class_create');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Create new user group</h1>';
		$queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
		$createForm = new input_form('createForm', NULL, 'POST', 'Create', NULL);
		$createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, 'Title:');
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
				echo '<p>User group created and saved.</p>';
				echo '<p><a href="', getURI(array('objectid' => $object->objectid, 'classid' => crc32(strtolower($className)))), '">Click here to view it now</a>.</p>';
			} else {
				trigger_error('Could not create user group.');
			}
		}
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/*** METHODS ***/

/* view object */
	function method_view(&$foowd) {
		$foowd->track('foowd_group->method_view');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Viewing User Group "', $this->getTitle(), '"</h1>';
		echo '<table>';
		echo '<tr><th>Title:</th><td>', $this->getTitle(), '</td></tr>';
		echo '<tr><th>Created:</th><td>', date(DATETIME_FORMAT, $this->created), '</td></tr>';
		echo '<tr><th>Author:</th><td><a href="', getURI(array('objectid' => $this->creatorid, 'classid' => USER_CLASS_ID)), '">', $this->creatorName, '</td></tr>';
		echo '<tr><th>Add:</th><td>', $this->permissions['add'], '</td></tr>';
		echo '<tr><th>Remove:</th><td>', $this->permissions['remove'], '</td></tr>';
		echo '<tr><th>Users:</th><td>';
		if ($users = $this->getUsersInGroup($foowd)) {
			foreach ($users as $user) {
				echo '<a href="', getURI(array('objectid' => $user->objectid, 'classid' => $user->classid)), '">', $user->getTitle(), '</a> ';
			}
			echo '</td>';
		} else {
			echo 'None</td>';
		}
		echo '</table>';
		
		echo '<p>', htmlspecialchars($this->description), '</p>';
		
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* add user to group */
	function method_add(&$foowd) {
		$foowd->track('foowd_group->method_add');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Add User To Group "', $this->getTitle(), '"</h1>';

		$userForm = new input_form('userForm', NULL, 'POST', 'Add', NULL);

		if ($users = $this->getUsersNotInGroup($foowd)) {
			$items = NULL;
			foreach ($users as $user) {
				$items[$user->objectid] = $user->getTitle();
			}
			$userSelect = new input_dropdown('userSelect', NULL, $items, 'Add Users:', 4, TRUE);
			
			if ($userForm->submitted() && is_array($userSelect->value)) {
				$error = FALSE;
				foreach ($users as $user) {
					if (!in_array($this->objectid, $user->groups) && in_array($user->objectid, $userSelect->value)) {
						$user->groups[] = $this->objectid;
						if ($user->save($foowd, FALSE)) {
							echo 'User "', $user->getTitle(), '" added to user group "', $this->getTitle(), '".<br />';
						} else {
							$error = TRUE;
							trigger_error('Could not remove user "'.$user->getTitle().'" from user group.');
						}
					}
				}
				if ($error) {
					trigger_error('Not all users could be added correctly.');
				} else {
					echo '<p>Users added successfully.</p>';
				}
			} else {
				$userForm->addObject($userSelect);
				$userForm->display();
			}
		} else {
			echo '<p>There are no users outside the group to add.</p>';
		}
		
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

/* remove user from group */
	function method_remove(&$foowd) {
		$foowd->track('foowd_group->method_remove');
		if (function_exists('foowd_prepend')) foowd_prepend($foowd, $this);
		echo '<h1>Remove User From Group "', $this->getTitle(), '"</h1>';
		
		$userForm = new input_form('userForm', NULL, 'POST', 'Remove', NULL);
		
		if ($users = $this->getUsersInGroup($foowd)) {
			$items = NULL;
			foreach ($users as $user) {
				$items[$user->objectid] = $user->getTitle();
			}
			$userSelect = new input_dropdown('userSelect', NULL, $items, 'Remove Users:', 4, TRUE);
			
			if ($userForm->submitted() && is_array($userSelect->value)) {
				$error = FALSE;
				foreach ($users as $user) {
					$key = array_search($this->objectid, $user->groups);
					if ($key !== FALSE && in_array($user->objectid, $userSelect->value)) {
						unset($user->groups[$key]);
						if ($user->save($foowd, FALSE)) {
							echo 'User "', $user->getTitle(), '" removed from user group "', $this->getTitle(), '".<br />';
						} else {
							$error = TRUE;
							trigger_error('Could not remove user "'.$user->getTitle().'" from user group.');
						}
					}
				}
				if ($error) {
					trigger_error('Not all users could be removed correctly.');
				} else {
					echo '<p>Users removed successfully.</p>';
				}
			} else {
				$userForm->addObject($userSelect);
				$userForm->display();
			}
		} else {
			echo '<p>There are no users in the group to remove.</p>';
		}
		
		if (function_exists('foowd_append')) foowd_append($foowd, $this);
		$foowd->track();
	}

}

?>