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
 * Modified by SquirrelMail Development
 * $Id$
 */

/*
class.object.php
Foowd base object class
*/

/*
The FOOWD base system class, this class is the base class for all other system
classes. It has member functions for saving objects to the storage medium,
administrating objects, etc. all the base FOOWD object functionality.
*/

/* Method permissions */
setPermission('foowd_object', 'class', 'create', 'Gods');
setPermission('foowd_object', 'object', 'admin', 'Gods');
setPermission('foowd_object', 'object', 'revert', 'Gods');
setPermission('foowd_object', 'object', 'delete', 'Gods');
setPermission('foowd_object', 'object', 'clone', 'Gods');
setPermission('foowd_object', 'object', 'permissions', 'Gods');

/* Class descriptor */
setClassMeta('foowd_object', 'Base Object');

/**
 * The abstract Foowd base class.
 *
 * Implements methods for saving objects to the database, calling object
 * methods, and doing useful object housekeeping and management.
 *
 * @author Paul James
 * @package Foowd
 */
class foowd_object {

  /**
   * Object member variable meta array.
   *
   * @access private
   * @var array
   */
  var $foowd_vars_meta = array();

  /**
   * Object index meta array.
   *
   * @access private
   * @var array
   */
  var $foowd_indexes = array();

  /**
   * The objects original access variables.
   *
   * Just incase they change before saving.
   *
   * @access private
   * @var array
   */
  var $foowd_original_access_vars = array();

  /**
   * The source the object is stored in.
   *
   * @access private
   * @var str
   */
  var $foowd_source;

  /**
   * Whether the object has been changed and needs saving.
   *
   * @access private
   * @var bool
   */
  var $foowd_changed = FALSE;

  /**
   * Whether to update the objects meta data upon saving.
   *
   * @var bool
   */
  var $foowd_update;

  /**
   * The objects title.
   *
   * @var str
   */
  var $title;

  /**
   * The objects objectid.
   *
   * @var int
   */
  var $objectid;

  /**
   * The objects version.
   *
   * @var int
   */
  var $version = 1;

  /**
   * The objects classid
   *
   * @var int
   */
  var $classid;

  /**
   * The objects workspaceid
   *
   * @var int
   */
  var $workspaceid = 0;

  /**
   * Unix timestamp the object was created.
   *
   * @var int
   */
  var $created;

  /**
   * Objectid of the user that created the object.
   *
   * @var int
   */
  var $creatorid;

  /**
   * Name of the user that created the object.
   *
   * @var str
   */
  var $creatorName;

  /**
   * Unix timestamp the object was last updated.
   *
   * @var int
   */
  var $updated;

  /**
   * Objectid of the user that last updated the object.
   *
   * @var int
   */
  var $updatorid;

  /**
   * Name of the user that last updated the object.
   *
   * @var str
   */
  var $updatorName;

  /**
   * Array of user groups allowed to access object methods.
   *
   * @var array
   */
  var $permissions = array();

  /**
   * Constructs a new Foowd objcct.
   *
   * @param object foowd The foowd environment object.
   * @param str title The objects title.
   * @param str viewGroup The user group for viewing the object.
   * @param str adminGroup The user group for administrating the object.
   * @param str deleteGroup The user group for deleting the object.
   * @param bool allowDuplicateTitle Allow object to have the same title as another object.
   */
  function foowd_object(
    &$foowd,
    $title = NULL,
    $viewGroup = NULL,
    $adminGroup = NULL,
    $deleteGroup = NULL,
    $allowDuplicateTitle = NULL
  ) {
    $foowd->track('foowd_object->constructor');

    $this->foowd = &$foowd; // create Foowd reference

    $this->__wakeup(); // init meta arrays

    if (!isset($allowDuplicateTitle)) {
      $allowDuplicateTitle = $foowd->allow_duplicate_title;
    }

// set object vars

    $maxTitleLength = getRegexLength($this->foowd_vars_meta['title'], 32);
    if (strlen($title) > 0 &&  strlen($title) < $maxTitleLength && preg_match($this->foowd_vars_meta['title'], $title)) {
      $this->title = $title;
    } else {
      trigger_error('Could not create object "'.$title.'", title too long or does not match regular expression.');
      $this->objectid = 0;
      $foowd->track(); return FALSE;
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
    while ($object =& $foowd->getObj(array(
      'objectid' => $this->objectid,
      'classid' => $this->classid,
      'workspaceid' => $this->workspaceid
    ))) {
      if (!$allowDuplicateTitle) {
        trigger_error('Could not create object, duplicate title "'.htmlspecialchars($title).'".');
        $this->objectid = 0;
        $foowd->track(); return FALSE;
      }
      $this->objectid++;
    }

// set original access vars
    $this->foowd_original_access_vars['objectid'] = $this->objectid;
    $this->foowd_original_access_vars['version'] = $this->version;
    $this->foowd_original_access_vars['classid'] = $this->classid;
    $this->foowd_original_access_vars['workspaceid'] = $this->workspaceid;

// set user vars
    $this->creatorid = $foowd->user->objectid;
    $this->creatorName = $foowd->user->title;
    $this->created = time();
    $this->updatorid = $foowd->user->objectid;
    $this->updatorName = $foowd->user->title;
    $this->updated = time();

// set method permissions
    if ($viewGroup != NULL) $this->permissions['view'] = $viewGroup;
    if ($adminGroup != NULL) {
      $this->permissions['admin'] = $adminGroup;
      $this->permissions['clone'] = $adminGroup;
    }
    if ($deleteGroup != NULL) $this->permissions['admin'] = $deleteGroup;

// add to loaded object reference list
    $foowd->database->addToLoadedReference($this);

// object created successfuly, queue for saving
    $this->foowd_changed = TRUE;

    $foowd->track();
  }

  /**
   * Serliaisation sleep method. Do not include Foowd meta arrays when
   * serialising the object.
   *
   * @access private
   * @return array Array of the names of the member variables to keep when serialising.
   */
  function __sleep() {
    $returnArray = get_object_vars($this);
    unset($returnArray['foowd']); // Foowd object reference
    unset($returnArray['foowd_source']); // source object was loaded from
    unset($returnArray['foowd_changed']); // whether the object has changed since it was loaded
    unset($returnArray['foowd_update']); // whether to update the objects meta data uplon saving
    unset($returnArray['foowd_vars_meta']); // member variable meta data
    unset($returnArray['foowd_indexes']); // object indexes
    unset($returnArray['foowd_original_access_vars']); // indexes used to load object
    return array_keys($returnArray);
  }

  /**
   * Serliaisation wakeup method. Re-create Foowd meta arrays not stored when
   * object was serialized.
   *
   * @access private
   */
  function __wakeup() {
// Member var metadata
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
// Index metadata
    $this->foowd_indexes['objectid'] = array('name' => 'objectid', 'type' => 'INT', 'notnull' => TRUE);
    $this->foowd_indexes['version'] = array('name' => 'version', 'type' => 'INT', 'unsigned' => TRUE, 'notnull' => TRUE, 'default' => 1);
    $this->foowd_indexes['classid'] = array('name' => 'classid', 'type' => 'INT', 'notnull' => TRUE, 'default' => 0);
    $this->foowd_indexes['workspaceid'] = array('name' => 'workspaceid', 'type' => 'INT', 'notnull' => TRUE, 'default' => 0);
    $this->foowd_indexes['title'] = array('name' => 'title', 'type' => 'VARCHAR', 'length' => getRegexLength($this->foowd_vars_meta['title'], 32), 'notnull' => TRUE);
    $this->foowd_indexes['updated'] = array('name' => 'updated', 'type' => 'DATETIME', 'notnull' => TRUE);
// Original access vars
    $this->foowd_original_access_vars['objectid'] = $this->objectid;
    $this->foowd_original_access_vars['version'] = $this->version;
    $this->foowd_original_access_vars['classid'] = $this->classid;
    $this->foowd_original_access_vars['workspaceid'] = $this->workspaceid;
  }

  /**
   * Call an object method.
   *
   * @param str methodName Name of the method to call.
   * @return bool Success or failure
   */
  function method($methodName = NULL) {
    $this->foowd->track('foowd_object->method', $methodName);
    if (!isset($methodName)) {
      trigger_error('Method name not given for method call');
      $this->foowd->track(); return FALSE;
    }
    $method = 'method_'.$methodName;
    if (method_exists($this, $method)) { // check method exiss
      if (is_array($this->permissions) && isset($this->permissions[$methodName])) {
        $methodPermission = $this->permissions[$methodName];
      } else {
        $methodPermission = getPermission(get_class($this), $methodName, 'object');
      }
      if ($this->foowd->user->inGroup($methodPermission, $this->creatorid)) { // check user permission
        $this->$method(); // call method
        if ($this->foowd->template) {
          $this->foowd->template->assign('objectid', $this->objectid);
          $this->foowd->template->assign('classid', $this->classid);
          $this->foowd->template->assign('version', $this->version);
          $this->foowd->template->assign('workspaceid', $this->workspaceid);
          $this->foowd->template->assign('title', $this->getTitle());
          $this->foowd->template->assign('method', $methodName);
          $this->foowd->template->assign_by_ref('object', $this);
        }
        $this->foowd->track(); return TRUE;
      } else {
        trigger_error('Permission denied to access method "'.$methodName.'" for object "'.$this->getTitle().'"');
        $this->foowd->track(); return FALSE;
      }
    } else {
      trigger_error('Unknown method "'.$methodName.'" for object "'.$this->getTitle().'"');
      $this->foowd->track(); return FALSE;
    }
  }

  /**
   * Call a class method.
   *
   * @param object foowd The foowd environment object.
   * @param str className Name of the class to call the method upon.
   * @param str methodName Name of the method to call.
   * @return bool Success or failure
   */
  function classMethod(&$foowd, $className, $methodName = NULL) {
    $foowd->track('foowd_object->classMethod', $className, $methodName);
    if (!isset($methodName)) {
      trigger_error('Method name not given for method call');
      $foowd->track(); return FALSE;
    }
    if (in_array('class_'.$methodName, get_class_methods($className))) { // check method exists
      $methodPermission = getPermission($className, $methodName, 'class');
      if ($foowd->user->inGroup($methodPermission)) { // check user permission
        call_user_func(array($className, 'class_'.$methodName), &$foowd, $className); // call method
        $foowd->track(); return TRUE;
      } else {
        trigger_error('Permission denied to call class method "'.$methodName.'" of class "'.$className.'"');
        $foowd->track(); return FALSE;
      }
    } else {
      trigger_error('Unknown class method "'.$methodName.'" for class "'.$className.'"');
      $foowd->track(); return FALSE;
    }
  }

  /**
   * Get object title ready for outputting.
   *
   * @return str String containing the objects title.
   */
  function getTitle() {
    return htmlspecialchars($this->title);
  }

  /**
   * Get a member variable from this object.
   *
   * @param str member The name of the member variable to get.
   * @return mixed The variable or FALSE on failure.
   */
  function get($member) {
    if (isset($this->$member)) {
      if (is_string($this->$member)) {
        return htmlspecialchars($this->$member);
      } else {
        return $this->$member;
      }
    } else {
      return FALSE;
    }
  }

  /**
   * Set a member variable.
   *
   * Checks the new value against the regular expression stored in
   * foowd_vars_meta to make sure the new value is valid.
   *
   * @param str member The name of the member variable to set.
   * @param mixed value The value to set the member variable to.
   * @return mixed Returns TRUE on success.
   */
  function set($member, $value = NULL) {
    $this->foowd->track('foowd_object->set', $member, $value);
    $okay = FALSE;
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
        } elseif (is_array($value)) { // single depth array
          $okay = TRUE;
          foreach ($value as $val) {
            if ($this->foowd_vars_meta[$member] == NULL || !preg_match($this->foowd_vars_meta[$member], $val)) {
              $okay = FALSE;
            }
          }
          if ($okay) $this->$member = $value;
        } elseif ($this->foowd_vars_meta[$member] == 'binary') { // binary data
          $this->$member = $value;
          $okay = TRUE;
        } else { // non-complex type
          if ($this->foowd_vars_meta[$member] == '' || $this->foowd_vars_meta[$member] == NULL || preg_match($this->foowd_vars_meta[$member], $value)) {
            $this->$member = $value;
            $okay = TRUE;
          }
        }
      }
    }
    if ($okay) {
      $this->foowd_changed = TRUE; // changed
    }
    $this->foowd->track(); return $okay;
  }

  /**
   * Set a member variable to a complex array value.
   *
   * @param array array The array value to set the member variable to.
   * @param str regex The regular expression the values of the array must match for the assignment to be valid.
   * @return mixed Returns TRUE on success.
   */
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

  /**
   * Create a new version of this object. Set the objects version number to the
   * next available version number and queue the object for saving. This will
   * have the effect of creating a new object entry since the objects version
   * number has changed.
   */
  function newVersion() {
    $this->foowd->track('foowd_object->newVersion');

    $object =& $this->foowd->getObj(array(
      'objectid' => $this->foowd_original_access_vars['objectid'],
      'classid' => $this->foowd_original_access_vars['classid'],
      'workspaceid' => $this->foowd_original_access_vars['workspaceid']
    ));
    if ($object) {
      $this->version = ++$object->version;
    }
    $this->foowd_original_access_vars['version'] = $this->version;
    $this->foowd_changed = TRUE;

    $this->foowd->track();
  }

  /**
   * Update the objects meta data. Set the objects updated date/time to the
   * current time, set the objects updator to the current user, and queue the
   * object for saving.
   */
  function update() {
    $this->foowd->track('foowd_object->update');
    $this->updated = time();
    $this->updatorid = $this->foowd->user->objectid;
    $this->updatorName = $this->foowd->user->title;
    $this->foowd_changed = TRUE;
    $this->foowd->track();
  }

  /**
   * Save the object.
   *
   * @return mixed Returns an exit value on success or FALSE on failure.
   */
  function save() { // write object to database
    $this->foowd->track('foowd_object->save');
    $result = $this->foowd->database->save($this);
    if ($result) {
      $this->foowd_changed = FALSE;
    }
    $this->foowd->track(); return $result;
  }

  /**
   * Delete the object.
   *
   * @return bool Returns TRUE on success.
   */
  function delete() { // remove all versions of an object from the database
    $this->foowd->track('foowd_object->delete');
    if ($this->foowd->database->delete($this)) {
      $this->foowd->track(); return TRUE;
    } else {
      $this->foowd->track(); return FALSE;
    }
  }

  /**
   * Clean up the archive versions of the object.
   *
   * @return bool Returns TRUE on success.
   */
  function tidyArchive() { // clean up old archived versions
    $this->foowd->track('foowd_object->tidyArchive');
    if ($this->foowd->database->tidy($this)) {
      $this->foowd->track(); return TRUE;
    } else {
      $this->foowd->track(); return FALSE;
    }
  }

  /**
   * Create form elements for the admin form from the objects member variables.
   *
   * @param object adminForm The form to add the form items.to.
   */
  function addFormItemsToAdminForm(&$adminForm) {
    $obj = get_object_vars($this);
    unset($obj['foowd_vars_meta']);
    unset($obj['foowd_indexes']);
    unset($obj['foowd_original_access_vars']);

    include_once(FOOWD_DIR.'input.textarray.php');
    include_once(FOOWD_DIR.'input.textarea.php');
    include_once(FOOWD_DIR.'input.textbox.php');

    foreach ($obj as $memberName => $memberVar) {
      if (isset($this->foowd_vars_meta[$memberName])) {
        if (is_array($memberVar)) {
          $textarray = new input_textarray($memberName, $this->foowd_vars_meta[$memberName], $memberVar, ucwords($memberName).':');
          if ($adminForm->submitted()) { // form submitted, update object with new values
            if (!$this->set($memberName, $textarray->items)) {
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
            $this->set($memberName, $textbox->value);
          }
        }
      }
    }
  }

  /**
   * Get object content.
   *
   * @return str The objects text contents processed for outputting.
   */
  function view() {
    ob_start();
    show($this);
    $obj = ob_get_contents();
    ob_end_clean();
    return $obj;
  }

  /**
   * Clone the object.
   *
   * @param str title The title of the new object clone.
   * @param str workspaceid The workspace to place the object clone in.
   * @return bool Returns TRUE on success.
   */
  function clone($title, $workspaceid) {
    if ($this->workspaceid == $workspaceid && $this->title == $title) {
      trigger_error('Can not clone object to the same title within the same workspace.');
    } else {
      $this->set('title', $title);
      $this->set('objectid', crc32(strtolower($title)));
      $this->set('workspaceid', $workspaceid);
       // adjust original workspace so as to create new object rather than overwrite old one.
      $this->foowd_original_access_vars['objectid'] = $this->objectid;
      $this->foowd_original_access_vars['workspaceid'] = $this->workspaceid;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Add permission selection dropdown lists for each object method to a form.
   *
   * @param object form The form to add the dropdown lists to.
   * @return bool Returns TRUE if the dropdown values have changed.
   */
  function addPermissionDropdowns(&$form) {
    $groups = $this->foowd->getUserGroups();
    $changed = FALSE;

    include_once(FOOWD_DIR.'input.dropdown.php');

    foreach (get_class_methods($this) as $methodName) {
      if (substr($methodName, 0, 7) == 'method_') {
        $methodName = substr($methodName, 7);
        if (isset($this->permissions[$methodName])) {
          $currentPermission = $this->permissions[$methodName];
        } else {
          $currentPermission = '';
        }
        $defaultPermission = getPermission(get_class($this), $methodName, 'object');
        if (isset($groups[$defaultPermission])) {
          $groups[''] = _("Default").' ('.htmlspecialchars($defaultPermission).')';
        } else {
          unset($groups['']);
        }
        if (isset($groups[$currentPermission])) { // display dropdown, user is allowed to change permission
          $permissionBox = new input_dropdown($methodName, $currentPermission, $groups, ucwords($methodName).':');
          $form->addObject($permissionBox);
          if ($form->submitted()) {
            $changed = TRUE;
            if ($permissionBox->value == '') {
              unset($this->permissions[$methodName]);
            } else {
              $this->permissions[$methodName] = $permissionBox->value;
            }
          }
        }
      }
    }
    return $changed;
  }

  /**
   * Get list of workspaces within system.
   *
   * @return array Returns an array of workspaces indexed by workspaceid.
   */
  function getWorkspaceList() {
    $workspace_classid = crc32(strtolower($this->foowd->workspace_class));
    $workspaceArray = array(0 => $this->foowd->outside_workspace_name);
    if ($workspaces = $this->foowd->getObjList(array('classid' => $workspace_classid), NULL, 'title', NULL, NULL, NULL, TRUE)) {
      foreach ($workspaces as $workspace) {
        if ($this->foowd->user->inGroup(getPermission(get_class($workspace), 'fill', 'object')) && ($this->classid != $workspace_classid || $this->objectid != $workspace->objectid)) {
          $workspaceArray[$workspace->objectid] = htmlspecialchars($workspace->title);
        }
      }
    }
    return $workspaceArray;
  }

  /**
   * Convert variable list to XML.
   *
   * @param array vars The variables to convert.
   * @param array goodVars List of variables to convert.
   */
  function vars2XML($vars, $goodVars) {
    foreach ($vars as $memberName => $memberVar) {
      if ($memberName !== '' && (!$goodVars || in_array($memberName, $goodVars))) {
        if (is_numeric(substr($memberName, 0, 1))) {
          $memberName = 'i'.$memberName;
        }
        echo "\t\t", '<', $memberName, '>';
        if (is_array($memberVar)) { // an array
          echo "\n\t";
          $this->vars2XML($memberVar, FALSE);
          echo "\t\t";
        } elseif (isset($this->foowd_vars_meta[$memberName]) && $this->foowd_vars_meta[$memberName] == 'binary') { // binary data
          echo '<![CDATA['.utf8_encode($memberVar).']]>';
        } else { // yay, a var
          if (strstr($memberVar, '<') || strstr($memberVar, '>') || strstr($memberVar, '&')) {
            echo '<![CDATA['.$memberVar.']]>';
          } else {
            echo $memberVar;
          }
        }
        echo '</', $memberName, ">\n";
      }
    }
  }

  /**
   * Get list of methods available for this object.
   *
   * @param bool classMethods Get list of class methods rather than object methods.
   * @return array Returns an array of methods.
   */
  function getMethods($classMethods = FALSE) {
    $methods = get_class_methods(get_class($this));
    $results = array();
    foreach ($methods as $method) {
      if ($classMethods) {
        if (substr($method, 0, 6) == 'class_') {
          $results[] = substr($method, 6);
        }
      } else {
        if (substr($method, 0, 7) == 'method_') {
          $results[] = substr($method, 7);
        }
      }
    }
    return $results;
  }

/* Class methods */

  /**
   * Output an object creation form and process its input.
   *
   * @static
   * @access protected
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_create(&$foowd, $className) {
    $foowd->track('foowd_object->class_create');

    include_once(FOOWD_DIR.'input.querystring.php');
    include_once(FOOWD_DIR.'input.form.php');
    include_once(FOOWD_DIR.'input.textbox.php');

    $queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
    $createForm = new input_form('createForm', NULL, 'POST', _("Create"), NULL);
    $createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, _("Object Title").':');
    if ($createForm->submitted() && $createTitle->value != '') {
      $object = &new $className(
        $foowd,
        $createTitle->value
      );
      if ($object->objectid != 0) {
        $foowd->template->assign('success', TRUE);
        $foowd->template->assign('objectid', $object->objectid);
        $foowd->template->assign('classid', $object->classid);
      } else {
        $foowd->template->assign('success', FALSE);
      }
    } else {
      $createForm->addObject($createTitle);
      $foowd->template->assign_by_ref('form', $createForm);
    }

    $foowd->track();
  }

/* Object methods */

  /**
   * Output the object.
   *
   * @access protected
   */
  function method_view() {
    $this->foowd->template->assign('heading', $this->getTitle());
    $this->foowd->template->assign('body', $this->view());
  }

  /**
   * Output the objects history.
   *
   * @access protected
   */
  function method_history() {
    $this->foowd->track('foowd_object->method_history');

    $this->foowd->template->assign('detailsTitle', $this->getTitle());
    $this->foowd->template->assign('detailsCreated', date(DATETIME_FORMAT, $this->created).' ('.timeSince($this->created).' ago)');
    $this->foowd->template->assign('detailsAuthor', htmlspecialchars($this->creatorName));
    $this->foowd->template->assign('detailsType', getClassDescription($this->classid));
    if ($this->workspaceid != 0) {
      $this->foowd->template->assign('detailsWorkspace', $this->workspaceid);
    }

    $foo = FALSE;
    $objArray = $this->foowd->getObjHistory(array('objectid' => $this->objectid, 'classid' => $this->classid));
    unset($objArray[0]);
    $versions = array();
    foreach ($objArray as $key => $object) {
      $version['updated'] = date(DATETIME_FORMAT, $object->updated).' ('.timeSince($object->updated).' ago)';
      $version['author'] = htmlspecialchars($object->updatorName);
      $version['version'] = $object->version;
      $version['objectid'] = $object->objectid;
      $version['classid'] = $object->classid;
      if ($foo) {
        $version['revert'] = TRUE;
      }
      $foo = TRUE;
      $this->foowd->template->append('versions', $version);
    }

    $this->foowd->track();
  }

  /**
   * Output the object administration form and handle its input.
   *
   * @access protected
   */
  function method_admin() {
    $this->foowd->track('foowd_object->method_admin');

    include_once(FOOWD_DIR.'input.form.php');

    $adminForm = new input_form('adminForm', NULL, 'POST');
    $this->addFormItemsToAdminForm($adminForm);

    $this->foowd->template->assign_by_ref('form', $adminForm);

    $this->foowd->track();
  }

  /**
   * Output the object version reversion screen and handle its input.
   *
   * @access protected
   */
  function method_revert() {
    $this->foowd->track('foowd_object->method_revert');

    include_once(FOOWD_DIR.'input.querystring.php');

    $confirm = new input_querystring('confirm', '/^[y]$/', FALSE);
    if ($confirm->value) {
      $this->newVersion();
      $this->update();
      $this->foowd->template->assign('success', TRUE);
    } else {
      $this->foowd->template->assign('confirm', TRUE);
      $this->foowd->template->assign('objectid', $this->objectid);
      $this->foowd->template->assign('version', $this->version);
      $this->foowd->template->assign('classid', $this->classid);
    }

    $this->foowd->track();
  }

  /**
   * Output the object deletion screen and handle its input.
   *
   * @access protected
   */
  function method_delete() {
    $this->foowd->track('foowd_object->method_delete');

    include_once(FOOWD_DIR.'input.querystring.php');

    $confirm = new input_querystring('confirm', '/^[y]$/', FALSE);
    if ($confirm->value) {
      if ($this->delete()) {
        $this->foowd->template->assign('success', TRUE);
      } else {
        $this->foowd->template->assign('success', FALSE);
      }
    } else {
      $this->foowd->template->assign('confirm', FALSE);
      $this->foowd->template->assign('objectid', $this->objectid);
      $this->foowd->template->assign('version', $this->version);
      $this->foowd->template->assign('classid', $this->classid);
    }

    $this->foowd->track();
  }

  /**
   * Output the object clone form and handle its input.
   *
   * @access protected
   */
  function method_clone() {
    $this->foowd->track('foowd_object->method_clone');

    include_once(FOOWD_DIR.'input.form.php');
    include_once(FOOWD_DIR.'input.textbox.php');
    include_once(FOOWD_DIR.'input.dropdown.php');

    $cloneForm = new input_form('cloneForm', NULL, 'POST', 'Clone Object', NULL);
    $cloneTitle = new input_textbox('cloneTitle', REGEX_TITLE, $this->getTitle(), 'Clone Title');
    if (isset($this->foowd->template_class)) {
      $cloneWorkspace = new input_dropdown('workspaceDropdown', NULL, $this->getWorkspaceList(), 'Workspace: ');
      $newWorkspace = $cloneWorkspace->value;
    } else {
      $newWorkspace = 0;
    }
    if ($cloneForm->submitted()) {
      if ($this->clone($cloneTitle->value, $newWorkspace)) {
        $this->foowd->template->assign('success', TRUE);
      } else {
        $this->foowd->template->assign('success', FALSE);
      }
    } else {
      $cloneForm->addObject($cloneTitle);
      $cloneForm->addObject($cloneWorkspace);
      $this->foowd->template->assign_by_ref('form', $cloneForm);
    }

    $this->foowd->track();
  }

  /**
   * Output the object permissions form and handle its input.
   *
   * @access protected
   */
  function method_permissions() {
    $this->foowd->track('foowd_object->method_permissions');

    include_once(FOOWD_DIR.'input.form.php');

    $permissionForm = new input_form('permissionForm', NULL, 'POST');
    $changed = $this->addPermissionDropdowns($permissionForm);

    $this->foowd->template->assign_by_ref('form', $permissionForm);

    $this->foowd->track();
  }

  /**
   * Output the object as XML.
   *
   * @access protected
   */
  function method_xml() {
    $this->foowd->debug = FALSE;
    header("Content-type: text/xml");
    echo '<?xml version="1.0"?>', "\n";
    echo '<foowd version="', $this->foowd->version, '" generated="', time(), '">', "\n";
    echo "\t", '<', get_class($this), '>', "\n";
    $this->vars2XML(get_object_vars($this), $this->__sleep());
    echo "\t", '</', get_class($this), ">\n";
    echo "</foowd>\n";
  }

/*
  function method_wddx() {
    $this->foowd->debug = FALSE;
    header("Content-type: text/xml");
    $goodVars = $this->__sleep();
    $packet_id = wddx_packet_start($this->getTitle().' - FOOWD v'.VERSION);
    foreach (get_object_vars($this) as $key => $var) {
      if (in_array($key, $goodVars)) {
        $$key = $var;
        wddx_add_vars($packet_id, $key);
      }
    }
    echo wddx_packet_end($packet_id);
  }
*/
}

?>
