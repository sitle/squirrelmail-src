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

/** METHOD PERMISSIONS */
setPermission('smdoc_storage', 'class',  'create', 'Nobody');

setPermission('smdoc_storage', 'object', 'view',   'Nobody');
setPermission('smdoc_storage', 'object', 'history','Nobody');
setPermission('smdoc_storage', 'object', 'admin',  'Nobody');
setPermission('smdoc_storage', 'object', 'revert', 'Nobody');
setPermission('smdoc_storage', 'object', 'delete', 'Nobody');
setPermission('smdoc_storage', 'object', 'clone',  'Nobody');
setPermission('smdoc_storage', 'object', 'xml',    'Nobody');

/** CLASS DESCRIPTOR **/
setClassMeta('smdoc_storage', 'Storage class with no permission');

/**
 * Basic class that restricts certain operations,
 * and maintains no permissions for public access
 */
class smdoc_storage extends foowd_object
{
  /**
   * Retrieve singleton instance of naming lookup object
   * @access private static
   * @param foowd foowd Reference to Foowd Environment
   * @return Reference to singleton lookup object
   */
  function &getInstance(&$foowd, $className, $classid, $objectid)
  {
    $foowd->track('smdoc_storage::getInstance',$className,$classid,$objectid);
    // Where conditions for the singleton namelookup object instance
    $where['classid']  = $classid;
    $where['objectid'] = $objectid;
    $where['version']  = 0;
    $where['workspaceid'] = 0; 

    // get Object - use where clause, no special source, skip workspace check
    $obj =& $foowd->getObj($where, NULL, FALSE);
    // If object couldn't be found, build a new one
    if ( $obj == NULL )
    {
      $obj = new $className($foowd);

      // If save failed, try retrieve again (maybe someone beat you to it..)
      if ( !$obj->save() )
      {
        $obj =& $foowd->getObj($where, NULL, FALSE);
        if ( $obj == NULL )
          trigger_error('Unable to retrieve object from database: ' . $where['objectid'], E_USER_ERROR);
      }
    }

    $foowd->track();
    return $obj;
  }


  /**
   * Constructor
   * Initialize new instance of smdoc_internal_mapping. 
   */
  function smdoc_storage(&$foowd, $title, $objectid = NULL,
                         $stored_in_DB = TRUE) 
  {
    $this->foowd =& $foowd;

    $this->title = $title;
    $this->classid = crc32(strtolower(get_class($this)));
    $this->workspaceid = 0;
    $this->version = 0;

    if ( $stored_in_DB && $objectid == NULL &&
         !$this->isTitleUnique($this->title, $this->workspaceid, $objectid) )
    {
      $this->objectid = 0;
      return FALSE;
    }

    $this->objectid = $objectid;

    if ( $stored_in_DB ) // init meta arrays
      $this->__wakeup();

    $last_modified = time();
    $this->creatorid = 0;
    $this->creatorName = 'System';
    $this->created = $last_modified;
    $this->updatorid = 0;
    $this->updatorName = 'System';
    $this->updated = $last_modified;

    $this->permissions = NULL;
  }

  /**
   * Serialisation wakeup method.
   */
  function __wakeup() 
  {
    parent::__wakeup();
    $this->foowd_original_access_vars['version']  = 0;
    $this->foowd_original_access_vars['workspaceid']  = 0;
  }

  /**
   * Create a new version of this object. Set the objects version number to the
   * next available version number and queue the object for saving. This will
   * have the effect of creating a new object entry since the objects version
   * number has changed.
   */
  function newVersion() 
  {
    return FALSE;
  }

  /**
   * Delete the object.
   *
   * @return bool Always returns false.
   */
  function tidyArchive() 
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
   * Get object content.
   *
   * @return str The objects text contents processed for outputting.
   */
  function view() 
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

}
