<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 *
 * $Id$
 */

/** 
 * ARRAY filled with external resources
 * Each static resource should supply the following:
 *   func - name of function to call to retrieve content
 *   title - title of external resource
 *   group - Group permitted to view external resource (Everyone by default)
 */
if ( !isset($EXTERNAL_RESOURCES) ) 
  $EXTERNAL_RESOURCES = array();

/** METHOD PERMISSIONS */
setPermission('smdoc_external', 'class',  'create', 'Nobody');
setPermission('smdoc_external', 'object', 'admin',  'Nobody');
setPermission('smdoc_external', 'object', 'revert', 'Nobody');
setPermission('smdoc_external', 'object', 'delete', 'Nobody');
setPermission('smdoc_external', 'object', 'clone',  'Nobody');
setPermission('smdoc_external', 'object', 'permissions', 'Nobody');
setPermission('smdoc_external', 'object', 'history','Nobody');
setPermission('smdoc_external', 'object', 'diff',   'Nobody');

/** CLASS DESCRIPTOR **/
setClassMeta('smdoc_external', 'Externally Defined Objects');
setConst('EXTERNAL_CLASS_ID', META_SMDOC_EXTERNAL_CLASS_ID);

/**
 * Class allowing external tools to be rendered within the
 * SM_doc/FOOWD framework.
 */
class smdoc_external extends foowd_object 
{
  /**
   * Constructor
   * Initialize new instance of smdoc_external. 
   */
  function smdoc_external(&$foowd, $objectid = NULL) 
  {
    global $EXTERNAL_RESOURCES;

    $foowd->track('smdoc_external->smdoc_external');

    if ( $objectid == NULL || !isset($EXTERNAL_RESOURCES[$objectid]) )
    {
      $this->objectid = 0;
      return FALSE;
    }
        
    $this->foowd =& $foowd;
    $this->objectid = $objectid;
    $this->title = htmlspecialchars($EXTERNAL_RESOURCES[$objectid]['title']);

    $this->classid = EXTERNAL_CLASS_ID;
    $this->workspaceid = 0;
    $this->version = 0;

    $last_modified = time();
    $this->creatorid = 0;
    $this->creatorName = 'System';
    $this->created = $last_modified;
    $this->updatorid = 0;
    $this->updatorName = 'System';
    $this->updated = $last_modified;

    // method permissions
    if ( isset($EXTERNAL_RESOURCES[$objectid]['group']) ) 
      $this->permissions['view'] = $EXTERNAL_RESOURCES[$objectid]['group'];

    $foowd->track();
  }

  /**
   * Factory method
   *
   * see if object id is present in list of external resources.
   * If so, create and return new smdoc_external object.
   * 
   * @access static
   *
   * @param smdoc Foowd environment object
   * @param int objectid Id of object to search for
   * @return new External object or NULL.
   */
  function &factory(&$foowd, $objectid)
  {
    global $EXTERNAL_RESOURCES;
    $ext_obj = NULL;
    $objectid = intval($objectid);
    if ( isset($EXTERNAL_RESOURCES[$objectid]) ) 
      $ext_obj = new smdoc_external($foowd, $objectid);
    return $ext_obj;
  }
  
  /**
   * Set a member variable.
   *
   * Checks the new value against the regular expression stored in
   * foowd_vars_meta to make sure the new value is valid.
   *
   * @param str member The name of the member variable to set.
   * @param optional mixed value The value to set the member variable to.
   * @return mixed always returns false
   */
  function set($member, $value = NULL) 
  {
    return FALSE;
  }

  /**
   * Set a member variable to a complex array value.
   *
   * @param array array The array value to set the member variable to.
   * @param str regex The regular expression the values of the array must match for the assignment to be valid.
   * @return mixed Always returns false.
   */
  function setArray($array, $regex) 
  {
    return FALSE;
  }

  /**
   * Save the object.
   *
   * @param optional bool incrementVersion Increment the object version.
   * @param optional bool doUpdate Update the objects details.
   * @return mixed Always returns false.
   */
  function save() 
  {
    return FALSE;
  }

  /**
   * Delete the object.
   *
   * @return bool Always returns false.
   */
  function delete() 
  {
    return FALSE;
  }

  /**
   * Clone the object.
   *
   * @return bool Always returns false.
   */
  function clone() 
  {
    return FALSE;
  }

  /**
   * Output the object.
   *
   * @param object foowd The foowd environment object.
   */
  function method_view() 
  {     
    global $EXTERNAL_RESOURCES;
    $this->foowd->track('smdoc_external->method_view');

    $methodName = $EXTERNAL_RESOURCES[$this->objectid]['func'];        
    $result['title'] = $this->title;
        
    if (function_exists($methodName)) 
      $methodName(&$this->foowd, &$result);
    else
      triggerError('Request for unknown method, '. $methodName 
                   . ', on external resource, ' . $this->title
                   . ' (object id = ' . $this->objectid . ')' );
        
    $this->foowd->track();
    return $result;
  }

} // end smdoc_external class

?>
