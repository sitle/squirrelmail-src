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

/**
 * The FOOWD base system class, this class is the base class for all 
 * other system classes. It has member functions for saving objects 
 * to the storage medium, administrating objects, etc. all the 
 * base FOOWD object functionality.
 * 
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage Foowd
 */

/** Method permissions */
setPermission('foowd_object', 'class',  'create', 'Nobody');
setPermission('foowd_object', 'object', 'admin',  'Gods');
setPermission('foowd_object', 'object', 'revert', 'Gods');
setPermission('foowd_object', 'object', 'delete', 'Gods');
setPermission('foowd_object', 'object', 'clone',  'Translator');
setPermission('foowd_object', 'object', 'xml',    'Nobody');

/** Class descriptor/Meta information */
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
class foowd_object 
{
  /**
   * Reference to the Foowd object.
   *
   * @var object
   */
  var $foowd;

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
   * @var string
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
   * @var string
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
  var $workspaceid;

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
   * @var string
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
   * @var string
   */
  var $updatorName;

  /**
   * Array of user groups allowed to access object methods.
   *
   * @var array
   */
  var $permissions;

  /**
   * Constructs a new Foowd objcct.
   *
   * @param smdoc $foowd Reference to the foowd environment object.
   * @param string $title The objects title.
   * @param string $viewGroup The user group for viewing the object.
   * @param string $adminGroup The user group for administrating the object.
   * @param string $deleteGroup The user group for deleting the object.
   * @param bool $allowDuplicateTitle Allow object to have the same title as another object.
   */
  function foowd_object( &$foowd,
                         $title = NULL,
                         $viewGroup = NULL,
                         $adminGroup = NULL,
                         $deleteGroup = NULL,
                         $uniqueTitle = TRUE) 
  {
    $foowd->track('foowd_object->constructor');

    $this->foowd = &$foowd; // create Foowd reference

    $this->__wakeup(); // init meta arrays

    $this->title = $title;
    $this->classid = crc32(strtolower(get_class($this)));

    if ( isset($foowd->user) ) 
      $this->workspaceid = $foowd->user->workspaceid;
    else 
      $this->workspaceid = 0;

    // Always check for unique title, because that assigns a value to $objectid.
    // Then, check that against uniqueTitle - to make sure that the title is legal.
    if ( !$this->isTitleUnique($this->title, $this->workspaceid, $objectid) &&
         $uniqueTitle )
    {
      $this->objectid = 0;
      return FALSE;
    }

    $this->objectid = $objectid;

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
    $this->permissions = array();

    if ($viewGroup != NULL) 
      $this->permissions['view'] = $viewGroup;

    if ($adminGroup != NULL) 
    {
      $this->permissions['admin'] = $adminGroup;
      $this->permissions['clone'] = $adminGroup;
    }

    if ($deleteGroup != NULL) 
      $this->permissions['admin'] = $deleteGroup;

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
  function __sleep() 
  {
    $returnArray = get_object_vars($this);
    unset($returnArray['foowd']); // Foowd object reference
    unset($returnArray['foowd_source']); // source object was loaded from
    unset($returnArray['foowd_changed']); // whether the object has changed since it was loaded
    unset($returnArray['foowd_update']); // whether to update the objects meta data uplon saving
    unset($returnArray['foowd_vars_meta']); // member variable meta data
    unset($returnArray['foowd_indexes']); // object indexes
    unset($returnArray['foowd_original_access_vars']); // indexes used to load object
    unset($returnArray['foowd_primary_key']); // indices used as primary key for save/update/delete
    return array_keys($returnArray);
  }

  /**
   * Serliaisation wakeup method. Re-create Foowd meta arrays not stored when
   * object was serialized.
   *
   * @access private
   */
  function __wakeup() 
  {
    $this->foowd_source = NULL;

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
    $this->foowd_indexes['permissions'] = array('name' => 'permissions', 'type' => 'MEDIUMBLOB');

    // Original access vars
    $this->foowd_original_access_vars['objectid'] = $this->objectid;
    $this->foowd_original_access_vars['version'] = $this->version;
    $this->foowd_original_access_vars['classid'] = $this->classid;
    $this->foowd_original_access_vars['workspaceid'] = $this->workspaceid;

    // Default primary key
    $this->foowd_primary_key = array('objectid','version','classid','workspaceid');
  }

  /**
   * Call an object method.
   *
   * @static
   * @param string $methodName Name of the method to call.
   * @return bool Success or failure
   */
  function method($methodName = NULL) 
  {
    $this->foowd->track('foowd_object->method', $methodName);
  
    $method = 'method_'.$methodName;

    // Check that method exists
    if ( $methodName == NULL || !method_exists($this, $method) )  
    {
      $_SESSION['error'] = INVALID_METHOD;
      $uri_arr['objectid'] = $this->objectid;
      $uri_arr['classid'] = $this->classid;
      $this->foowd->loc_forward(getURI($uri_arr, FALSE)); 
      exit;
    }

    // Ensure that user has Permission to invoke method
    $permission = $this->foowd->hasPermission(get_class($this), 
                                              $methodName, 
                                              'object', 
                                              $this->permissions,
                                              $this->creatorid);

    if ( !$permission )
    {
      $_SESSION['error'] = USER_NO_PERMISSION;
      $this->foowd->loc_forward(getURI(NULL, FALSE)); 
      exit;
    }

    $this->$method(); // call method

    if ($this->foowd->template) 
    {
      $this->foowd->template->assign('objectid', $this->objectid);
      $this->foowd->template->assign('classid', $this->classid);
      $this->foowd->template->assign('version', $this->version);
      $this->foowd->template->assign('workspaceid', $this->workspaceid);
      $this->foowd->template->assign('title', $this->getTitle());
      $this->foowd->template->assign('method', $methodName);
      $this->foowd->template->assign_by_ref('object', $this);
    }

    $this->foowd->track(); 
    return TRUE;
  }

  /**
   * Call a class method.
   *
   * @static
   * @param smdoc $foowd Reference to the foowd environment object.
   * @param string $className Name of the class to call the method upon.
   * @param string $methodName Name of the method to call.
   * @return bool Success or failure
   */
  function classMethod(&$foowd, $className, $methodName = NULL ) 
  {
    $foowd->track('foowd_object->classMethod', $className, $methodName);

    // make sure method exists
    $classMethods = get_class_methods($className);
    $classMethodName = 'class_'.$methodName;

    if ( $methodName == NULL || !in_array($classMethodName, $classMethods) ) 
    {
      $_SESSION['error'] = INVALID_METHOD;
      $foowd->loc_forward(getURI(NULL, FALSE)); 
      exit;
    }

    $permission = $foowd->hasPermission($className, 
                                        $methodName, 
                                        'class');

    if ( !$permission )
    {
      $_SESSION['error'] = USER_NO_PERMISSION;
      $foowd->loc_forward(getURI(NULL, FALSE)); 
      exit;
    }
    $foowd->template->assign('className', $className);
    $foowd->template->assign('method', $methodName);
    call_user_func(array($className, $classMethodName), &$foowd, $className); // call method

    $foowd->track(); 
    return TRUE;
  }

  /**
   * Get object title ready for outputting.
   *
   * @return string String containing the objects title.
   */
  function getTitle() 
  {
    return htmlspecialchars($this->title);
  }

  /**
   * Verify that title is unique. 
   * If it is, and the uniqueObjectid parameter is TRUE, 
   * a unique objectid is generated and assigned to the objectid parameter.
   *
   * @param string $title The proposed title
   * @param int $workspaceid The workspace to search in, FALSE to leave workspaceid out
   * @param int $objectid The object id generated from the title
   * @param mixed $in_source Source to get object from
   * @param bool $uniqueObjectid Generate unique object id for unique title
   * @return TRUE if Title is Unique.
   * @see smdoc_db::isTitleUnique()
   */
  function isTitleUnique($title, $workspaceid, &$objectid, 
                         $in_source = NULL, $uniqueObjectid = TRUE)
  {
    return $this->foowd->database->isTitleUnique($title, 
                                                 $workspaceid, 
                                                 $objectid, 
                                                 $in_source,
                                                 $uniqueObjectid);
  }

  /**
   * Get a member variable from this object.
   *
   * @param string $member The name of the member variable to get.
   * @return mixed The variable or FALSE on failure.
   */
  function get($member) 
  {
    if (isset($this->$member)) 
    {
      if ( is_string($this->$member) ) 
        return htmlspecialchars($this->$member);
      else 
        return $this->$member;
    } 
    return FALSE;
  }

  /**
   * Set a member variable.
   *
   * Checks the new value against the regular expression stored in
   * foowd_vars_meta to make sure the new value is valid.
   *
   * @param string $member The name of the member variable to set.
   * @param mixed $value The value to set the member variable to.
   * @return mixed Returns TRUE on success.
   */
  function set($member, $value = NULL) 
  {
    $this->foowd->track('foowd_object->set', $member, $value);
    $object_vars = get_object_vars($this);

    if ( !array_key_exists($member, $object_vars) )   // if member variable doesn't exist, return early
    {
      $this->foowd->track();
      return FALSE;
    }

    $okay = FALSE;
    $regex = isset($this->foowd_vars_meta[$member]) ?
                   $this->foowd_vars_meta[$member] : NULL;

    if ( $regex == NULL || $regex == '' || $regex == 'binary' )
      $okay = TRUE;
    elseif ( is_array($value) )                           
      $okay = $this->setArray($value, $regex);
    elseif ( preg_match($regex, $value) )  // data passes verification
        $okay = TRUE;

    if ($okay)
    { 
      $this->$member = $value;
      $this->foowd_changed = TRUE; // changed
    }

    $this->foowd->track(); 
    return $okay;
  }

  /**
   * Set a member variable to a complex array value.
   *
   * @param array $array The array value to set the member variable to.
   * @param mixed $regex The regular expression the values of the array must match for the assignment to be valid.
   * @return bool Returns TRUE on success.
   */
  function setArray($array, $regex = NULL ) 
  {
    if ( $regex == NULL || $regex == '' )
      return TRUE;

    foreach ($array as $index => $val) 
    {
      $cur_regex = is_array($regex) ? $regex[$index] : $regex;

      if (is_array($val)) 
      {
        if ( !$this->setArray($val, $cur_regex) )
          return FALSE;
      }
      elseif ( !preg_match($cur_regex, $val) )
        return FALSE;
    }

    return TRUE;
  }

  /**
   * Create a new version of this object. Set the objects version number to the
   * next available version number and queue the object for saving. This will
   * have the effect of creating a new object entry since the objects version
   * number has changed.
   */
  function newVersion() 
  {
    $this->foowd->track('foowd_object->newVersion');

    $object =& $this->foowd->getObj(array(
                 'objectid'    => $this->foowd_original_access_vars['objectid'],
                 'classid'     => $this->foowd_original_access_vars['classid'],
                 'workspaceid' => $this->foowd_original_access_vars['workspaceid']
                 ));

    if ($object) 
      $this->version = $object->version + 1;
    else
      return FALSE;

    $this->foowd_original_access_vars['version'] = $this->version;
    $this->foowd_changed = TRUE;

    $this->foowd->track();
    return TRUE;
  }

  /**
   * Update the objects meta data. Set the objects updated date/time to the
   * current time, set the objects updator to the current user, and queue the
   * object for saving.
   */
  function update() 
  {
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
  function save()  
  {
    $this->foowd->track('foowd_object->save');
    $result = $this->foowd->database->save($this);

    if ($result !== FALSE) 
      $this->foowd_changed = FALSE;
    
    $this->foowd->track(); 
    return $result;
  }

  /**
   * Delete the object.
   *
   * @return bool Returns TRUE on success.
   */
  function delete()
  {
    $this->foowd->track('foowd_object->delete');

    $result = $this->foowd->database->delete($this);
    $lookup =& smdoc_name_lookup::getInstance($this->foowd);
    $lookup->deleteShortName($this);
 
    $this->foowd->track(); 
    return $result ? TRUE : FALSE;
  }

  /**
   * Clean up the archive versions of the object.
   *
   * @return bool Returns TRUE on success.
   */
  function tidyArchive()
  {
    $this->foowd->track('foowd_object->tidyArchive');
    
    $result = $this->foowd->database->tidy($this);
    
    $this->foowd->track(); 
    return $result ? TRUE : FALSE;
  }


  /**
   * Create form elements for the admin form from the objects member variables.
   *
   * @param  object adminForm The form to add the form items to.
   * @return mixed array of error codes or 0 for success
   */
  function addFormItemsToAdminForm(&$adminForm, &$error) 
  {
    // Add regular elements to form
    include_once(INPUT_DIR.'input.textbox.php');
    include_once(INPUT_DIR.'input.dropdown.php');

    $reg = isset($this->foowd_vars_meta['title']) ?
                 $this->foowd_vars_meta['title'] : NULL;
    $titleBox = new input_textbox('title', $reg, $this->title, 'Title', FALSE);

    $reg = isset($this->foowd_vars_meta['version']) ?
                 $this->foowd_vars_meta['version'] : NULL;
    $versionBox = new input_textbox('version', $reg, $this->version, 'Version', FALSE);

    $workspaceBox = new input_dropdown('workspaceid', $this->workspaceid, 
                                       $this->getWorkspaceList(), 'Workspace');
    $error = NULL;
    if ( $adminForm->submitted() )
    {
      if ( $workspaceBox->value != $this->workspaceid ) 
        $this->set('workspaceid', $workspaceBox->value);
 
      if ( $titleBox->value != $this->title )
      {
        $unique = $this->isTitleUnique($titleBox->value, $this->workspaceid, $objectid, NULL, FALSE);
        if ( $unique )
          $this->set('title', $titleBox->value);
        else
        {
          $error[] = OBJECT_DUPLICATE_TITLE;
          $titleBox->wasValid = FALSE;
        }
      }
      if ( $versionBox->value != $this->version )
        $this->set('version', $versionBox->value);
    }

    $adminForm->addObject($titleBox);
    $adminForm->addObject($versionBox);    
    $adminForm->addObject($workspaceBox);

    $this->addPermissionDropdowns($adminForm);

    if ( $error != 0 )
    {
      $this->foowd_changed = FALSE;
      $this->foowd->template->assign('failure', $error);
    }
  }

  /**
   * Get object content.
   *
   * @return string The objects text contents processed for outputting.
   */
  function view() 
  {
    ob_start();
    show($this);
    $obj = ob_get_contents();
    ob_end_clean();
    return $obj;
  }

  /**
   * Clone the object.
   *
   * @param string $title The title of the new object clone.
   * @param string $workspaceid The workspace to place the object clone in.
   * @param bool   $keepObjectId Whether or not to preserve objectid association.
   * @return int 1 = success
   *            -1 = title already in use
   *            -2 = object could not be created
   *            -3 = must specify to a different workspace
   */
  function clone($title, $workspaceid, $keepObjectId = TRUE) 
  {
    // If assigning a new title, make sure it's unique
    // in target workspace
    if ( $title != $this->title &&
         !$this->isTitleUnique($title, $workspaceid, $objectid) )
      return -1;

    // In the case of a new translation, or a copy of an item into 
    // a different workspace, we may want to keep the objectid the 
    // same ( to preserve the association between the objects in 
    // different workspaces )
    if ( $keepObjectId )
    {
      // to keep the object id, we MUST be cloning to a different workspace
      if ( $this->workspaceid == $workspaceid )
        return -3;
      $objectid = $this->objectid;
    }

    $this->set('title', $title);
    $this->set('objectid', $objectid);
    $this->set('workspaceid', $workspaceid);
    $this->set('version', 1);

    // adjust original variables so as to create new object rather than overwrite old one.
    $this->foowd_original_access_vars['objectid'] = $this->objectid;
    $this->foowd_original_access_vars['workspaceid'] = $this->workspaceid;

    if ( $this->save() )
      return 1;

    return -2;
  }

  /**
   * Add permission selection dropdown lists for each object method to a form.
   *
   * @param object form The form to add the dropdown lists to.
   */
  function addPermissionDropdowns(&$form) 
  {
    $groups = $this->foowd->getUserGroups();
    $methods = get_class_methods($this);

    include_once(INPUT_DIR.'input.dropdown.php');

    foreach ($methods as $methodName) 
    {
      if ( substr($methodName, 0, 7) != 'method_' )
        continue;
  
      $methodName = substr($methodName, 7);

      // Get the default permission (Gods, Author, etc.)
      // If the DEFAULT permission is not a valid group, skip it.
      $defaultPermission = getPermission(get_class($this), $methodName, 'object');
      if ( !isset($groups[$defaultPermission]) )
        $defaultPermission = 'Nobody';

      // Get the currently set permission.
      // If the current group doesn't exist, revert to the default.
      $currentPermission = isset($this->permissions[$methodName]) ?
                                 $this->permissions[$methodName] : $defaultPermission;
      if ( !isset($groups[$currentPermission]) )
        $currentPermission = $defaultPermission;

      // Create the dropdown for this method, append text to the default
      $permissionBox = new input_dropdown($methodName, $currentPermission, $groups, $methodName);
      $permissionBox->items[$defaultPermission] .= ' ('._("Default").')';

      $form->addToGroup('permissions', $permissionBox);
      if ( $form->submitted() && $permissionBox->value != $currentPermission) 
      {
        $this->foowd_changed = TRUE;
        $this->permissions[$methodName] = $permissionBox->value;
      }
    }
  }

  /**
   * Get list of workspaces within system.
   *
   * @return array Returns an array of workspaces indexed by workspaceid.
   */
  function &getWorkspaceList() 
  {
    return $this->foowd->getWorkspaceList();
  }

  /**
   * Convert variable list to XML.
   *
   * @param array vars The variables to convert.
   * @param array goodVars List of variables to convert.
   */
  function vars2XML($vars, $goodVars) 
  {
    foreach ($vars as $memberName => $memberVar) 
    {
      if ($memberName !== '' && 
          (!$goodVars || in_array($memberName, $goodVars))) 
      {
        if (is_numeric(substr($memberName, 0, 1))) 
          $memberName = 'i'.$memberName;

        echo "\t\t", '<', $memberName, '>';
        if (is_array($memberVar)) 
        { // an array
          echo "\n\t";
          $this->vars2XML($memberVar, FALSE);
          echo "\t\t";
        } 
        elseif (isset($this->foowd_vars_meta[$memberName]) && 
                $this->foowd_vars_meta[$memberName] == 'binary') 
        { // binary data
          echo '<![CDATA['.utf8_encode($memberVar).']]>';
        } 
        else 
        { // yay, a var
          if (strstr($memberVar, '<') || 
              strstr($memberVar, '>') ||
              strstr($memberVar, '&')) 
          {
            echo '<![CDATA['.$memberVar.']]>';
          } 
          else
            echo $memberVar;
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
  function getMethods($classMethods = FALSE) 
  {
    $methods = get_class_methods(get_class($this));
    $results = array();
    foreach ($methods as $method) 
    {
      if ($classMethods) 
      {
        if (substr($method, 0, 6) == 'class_') 
          $results[] = substr($method, 6);
      } 
      else 
      {
        if (substr($method, 0, 7) == 'method_')
          $results[] = substr($method, 7);
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
   * @param smdoc $foowd Reference to the foowd environment object.
   * @param string className The name of the class.
   */
  function class_create(&$foowd, $className) 
  {
    $foowd->track('foowd_object->class_create');

    include_once(INPUT_DIR.'input.querystring.php');
    include_once(INPUT_DIR.'input.form.php');
    include_once(INPUT_DIR.'input.textbox.php');

    $queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
    $createForm = new input_form('createForm', NULL, SQ_POST, _("Create"), NULL);
    $createTitle = new input_textbox('createTitle', REGEX_TITLE, $queryTitle->value, 'Object Title');

    if ($createForm->submitted() && 
        $createTitle->wasSet && $createTitle->wasValid && $createTitle->value != '') 
    {
      // Ensure unique title
      $oid = NULL;
      if ( !$foowd->database->isTitleUnique($createTitle->value, $foowd->user->workspaceid, $oid, NULL, FALSE) )
        $result = 1;
      else
      {
        $object = &new $className($foowd, $createTitle->value);
        if ( $object->objectid != 0 && $object->save($foowd) ) 
          $result = 0; // created ok
        else
          $result = 2; // error
      }
    } 
    else
      $result = -1;

    switch ( $result )
    {
      case 0:
        $_SESSION['ok'] = OBJECT_CREATE_OK;
        $uri_arr['classid'] = $object->classid;
        $uri_arr['objectid'] = $object->objectid;
        $foowd->loc_forward(getURI($uri_arr, FALSE));
        exit;
      case 1:
        $foowd->template->assign('failure', OBJECT_DUPLICATE_TITLE);
        $createTitle->wasValid = FALSE;
        break;
      case 2:
        $foowd->template->assign('failure', OBJECT_CREATE_FAILED);
        break;
      default:
        $foowd->template->assign('failure', FORM_FILL_FIELDS);
    }
      
    $createForm->addObject($createTitle);
    $foowd->template->assign_by_ref('form', $createForm);

    $foowd->track();
  }

/* Object methods */

  /**
   * Output the object.
   *
   * @access protected
   */
  function method_view() 
  {
    $this->foowd->template->assign('body', $this->view());
  }

  /**
   * Output the objects history.
   *
   * @access protected
   */
  function method_history() 
  {
    $this->foowd->track('foowd_object->method_history');

    $this->foowd->template->assign('detailsTitle', $this->getTitle());
    $this->foowd->template->assign('detailsCreated', date(DATETIME_FORMAT, $this->created).' ('.timeSince($this->created).' ago)');
    $this->foowd->template->assign('detailsAuthor', htmlspecialchars($this->creatorName));
    $this->foowd->template->assign('detailsType', getClassDescription($this->classid));
    if ($this->workspaceid != 0) 
      $this->foowd->template->assign('detailsWorkspace', $this->workspaceid);

    $objArray = $this->foowd->getObjHistory(array('objectid' => $this->objectid, 'classid' => $this->classid));
    $latestVersion = $objArray[0]->version;
    unset($objArray[0]);
    $versions = array();
    foreach ($objArray as $object) 
    {
      $version = array();
      $version['updated'] = date(DATETIME_FORMAT, $object->updated).' ('.timeSince($object->updated).' ago)';
      $version['author'] = htmlspecialchars($object->updatorName);
      $version['version'] = $object->version;
      $version['objectid'] = $object->objectid;
      $version['classid'] = $object->classid;
      if ($object->version != $latestVersion)
        $version['revert'] = TRUE;

      $this->foowd->template->append('versions', $version);
    }

    $this->foowd->track();
  }

  /**
   * Output the object administration form and handle its input.
   *
   * @access protected
   */
  function method_admin() 
  {
    $this->foowd->track('foowd_object->method_admin');

    include_once(INPUT_DIR.'input.form.php');

    $error = NULL;

    $shortForm = new input_form('shortform');
    $lookup = smdoc_name_lookup::getInstance($this->foowd);
    $lookup->addShortNameToForm($this, $shortForm, $error);
 
    $adminForm = new input_form('adminForm');
    $this->addFormItemsToAdminForm($adminForm, $error);

    if ( $error != NULL )
      $this->foowd->template->assign('failure', $error);
    elseif ( $adminForm->submitted() && $this->foowd_changed )
    {
      if ( $this->save() )
        $this->foowd->template->assign('success', OBJECT_UPDATE_OK);
      else
        $this->foowd->template->assign('failure', OBJECT_UPDATE_FAILED);
    }

    $this->foowd->template->assign_by_ref('shortform', $shortForm);
    $this->foowd->template->assign_by_ref('form', $adminForm);
    
    $this->foowd->track();
  }

  /**
   * Output the object version reversion screen and handle its input.
   *
   * @access protected
   */
  function method_revert() 
  {
    $this->foowd->track('foowd_object->method_revert');

    include_once(INPUT_DIR.'input.form.php');
    include_once(INPUT_DIR.'input.querystring.php');

    $revertForm = new input_form('revertForm', NULL, SQ_POST, _("OK"), NULL);

    if ($revertForm->submitted() ) 
    {
      $this->newVersion();
      $this->update();
      $this->save();

      $_SESSION['ok']      = OBJECT_UPDATE_OK;  
      $uri_arr['objectid'] = $this->objectid;
      $uri_arr['classid'] = $this->classid;
      $this->foowd->loc_forward(getURI($uri_arr, FALSE));
      exit;
    }

    $this->foowd->template->assign_by_ref('form', $revertForm);

    $this->foowd->template->assign('objectid', $this->objectid);
    $this->foowd->template->assign('version', $this->version);
    $this->foowd->template->assign('classid', $this->classid);
    
    $this->foowd->track();
  }

  /**
   * Output the object deletion screen and handle its input.
   *
   * @access protected
   */
  function method_delete() 
  {
    $this->foowd->track('foowd_object->method_delete');

    include_once(INPUT_DIR.'input.form.php');
    $deleteForm = new input_form('deleteForm', NULL, SQ_POST, _("OK"), NULL);

    if ($deleteForm->submitted() ) 
    {
      if ($this->delete()) 
      {
        $_SESSION['ok'] = OBJECT_DELETE_OK;
        $this->foowd->loc_forward( getURI(NULL, FALSE) );
        exit;
      }

      $this->foowd->template->assign('failure', OBJECT_DELETE_FAILED);
    }

    $this->foowd->template->assign_by_ref('form', $deleteForm);

    $this->foowd->template->assign('objectid', $this->objectid);
    $this->foowd->template->assign('version', $this->version);

    $this->foowd->track();
  }

  /**
   * Output the object clone form and handle its input.
   *
   * @access protected
   */
  function method_clone() 
  {
    $this->foowd->track('foowd_object->method_clone');

    include_once(INPUT_DIR.'input.form.php');
    include_once(INPUT_DIR.'input.textbox.php');
    include_once(INPUT_DIR.'input.dropdown.php');
    include_once(INPUT_DIR.'input.checkbox.php');

    $cloneForm = new input_form('cloneForm', NULL, SQ_POST);
    $cloneTitle = new input_textbox('cloneTitle', REGEX_TITLE, $this->title, 'Clone Title');
    $cloneObjectId = new input_checkbox('cloneId', FALSE, 'New Object Translation');
    $cloneWorkspace = new input_dropdown('workspaceDropdown', NULL, $this->getWorkspaceList());
    $newWorkspace = $cloneWorkspace->value;

    if ($cloneForm->submitted()) 
    {
      $rc = $this->clone($cloneTitle->value, $newWorkspace, $cloneObjectId->checked);
      switch($rc)
      {
        case 1: 
          $_SESSION['ok'] = OBJECT_CREATE_OK;
          $uri_arr['objectid'] = $this->objectid;
          $uri_arr['classid'] = $this->classid;
          $this->foowd->loc_forward( getURI($uri_arr, FALSE) );
          exit;
        case -1:
          $this->foowd->template->assign('failure', OBJECT_DUPLICATE_TITLE);
          $cloneTitle->wasValid = 0;
          break;
        case -3:
          $this->foowd->template->assign('failure', OBJECT_DUPLICATE_TITLE);
        default:
        case -2:
          $this->foowd->template->assign('failure', OBJECT_CREATE_FAILED);
          break;
      }
    }

    $cloneForm->addObject($cloneTitle);
    $cloneForm->addObject($cloneWorkspace);
    $cloneForm->addObject($cloneObjectId);
    $this->foowd->template->assign_by_ref('form', $cloneForm);

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

}

?>
