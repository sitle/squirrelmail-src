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
setPermission('smdoc_name_lookup', 'class',  'create', 'Nobody');
setPermission('smdoc_name_lookup', 'object', 'admin',  'Nobody');
setPermission('smdoc_name_lookup', 'object', 'revert', 'Nobody');
setPermission('smdoc_name_lookup', 'object', 'delete', 'Nobody');
setPermission('smdoc_name_lookup', 'object', 'clone',  'Nobody');
setPermission('smdoc_name_lookup', 'object', 'permissions', 'Nobody');
setPermission('smdoc_name_lookup', 'object', 'history','Nobody');
setPermission('smdoc_name_lookup', 'object', 'diff',   'Nobody');
setPermission('smdoc_name_lookup', 'object', 'view',   'Nobody');


/** CLASS DESCRIPTOR **/
setClassMeta('smdoc_name_lookup', 'Singleton class managing short names');
setConst('REGEX_SHORTNAME', '/^[a-z_-]{0,11}$/');
setConst('SHORTNAMES_ID', 1063068242);

/**
 * Singleton class provides a basic mapping for 
 * well-known simple names (faq, privacy..)
 * to objectid/className pairs.
 */
class smdoc_name_lookup extends foowd_object
{
  /**
   * Array mapping shortnames to objectid/classid pairs
   * @var array
   */
  var $shortNames;

  /**
   * Constructor
   * Initialize new instance of smdoc_internal_mapping. 
   */
  function smdoc_name_lookup(&$foowd) 
  {
    $this->foowd =& $foowd;

    // init meta arrays
    $this->__wakeup();

    $this->title = '__SHORTNAMES__';
    $this->objectid = SHORTNAMES_ID;
    $this->classid = META_SMDOC_NAME_LOOKUP_CLASS_ID;
    $this->workspaceid = 0;
    $this->version = 0;

    $last_modified = time();
    $this->creatorid = 0;
    $this->creatorName = 'System';
    $this->created = $last_modified;
    $this->updatorid = 0;
    $this->updatorName = 'System';
    $this->updated = $last_modified;

    $this->shortNames = array();
  }

  /**
   * Map given objectName to objectid/classid pair.
   * 
   * @param string objectName Name of object to locate.
   * @param int objectid ID of located object
   * @param int classid  ID of class for located object
   * @return TRUE if found, false if not.
   */
  function findObject($objectName, &$objectid, &$classid)
  {
    $this->foowd->track('smdoc_name_lookup->findObject', $objectName);
    global $EXTERNAL_RESOURCES;

    $result = FALSE;
    if ( isset($EXTERNAL_RESOURCES[$objectName]) )
    {
      $objectid = $EXTERNAL_RESOURCES[$objectName];
      $classid = EXTERNAL_CLASS_ID;
      $result = TRUE;
    }
    elseif ( isset($this->shortNames[$objectName]) )
    {
      $objectid = $INTERNAL_LOOKUP[$objectName]['objectid'];
      $classid  = $INTERNAL_LOOKUP[$objectName]['classid'];
      $result = TRUE;
    }

    $this->foowd->track();
    return $result;
  }

  /**
   * Clean up associated short name
   * 
   * @param object obj  Object to remove shortname for
   */
  function deleteShortName(&$obj)
  {
    $oid = intval($obj->objectid);

    if ( isset($this->shortNames[$oid]) )
    {
      $name = $this->shortNames[$oid];
      unset($this->shortNames[$oid]);
      unset($this->shortNames[$name]);
    }

    $this->foowd_changed = TRUE;
    return $this->save();
  }

  /**
   * Clean up associated short name when object is deleted.
   * 
   * @param object obj  Object to add shortname for
   * @param string name Short name for object
   */
  function addShortName(&$obj, $name)
  {
    if ( empty($name) || isset($this->shortNames[$name]) )
      return FALSE;

    $oid = intval($obj->objectid);
    if ( isset($this->shortNames[$oid]) )
    {   
      $oldName = $this->shortNames[$oid];
      unset($this->shortNames[$oid]);
      unset($this->shortNames[$oldName]);
    }

    $this->shortNames[$name]['objectid'] = $oid;
    $this->shortNames[$name]['classid'] = intval($obj->classid);
    $this->shortNames[$oid] = $name;
    
    $this->foowd_changed = TRUE;
    return $this->save();
  }

  /**
   * Add textbox to form for association of shortname
   * with object.
   *
   * @access static
   *
   * @param object     obj      Object to make shortname for
   * @param input_form form     Form to add element to
   */
  function addShortNameToForm(&$obj, &$form, &$error)
  {
    global $EXTERNAL_RESOURCES;

    include_once(INPUT_DIR.'input.textbox.php');
    $oid = intval($obj->objectid);

    // Get initial value for the shortname box
    if ( isset($this->shortNames[$oid]) )
      $name = $this->shortNames[$oid];
    else
      $name = '';

    $shortBox = new input_textbox('shortname', REGEX_SHORTNAME, $name, 'Short Name', FALSE);
    if ( $form->submitted() && $shortBox->wasValid && $shortBox->value != $name )
    {
      if ( isset($EXTERNAL_RESOURCES[$shortBox->value]) ||
           isset($this->shortNames[$shortBox->value])   )
      {
        $error[] = _("Object ShortName is already in use.");
        $shortBox->wasValid = FALSE;
      }
      elseif ( !$this->addShortName() )
        $error[] = _("Could not save ShortName.");
    } 

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

  //----------------- STATIC METHODS -------------------

  /**
   * Retrieve singleton instance of naming lookup object
   */
  function &getInstance(&$foowd)
  {
    // Where conditions for the singleton namelookup object instance
    $where['classid']  = META_SMDOC_NAME_LOOKUP_CLASS_ID;
    $where['objectid'] = SHORTNAMES_ID;
    $where['version']  = 0;
    $where['workspaceid'] = 0; 

    // get Object - use where clause, no special source, skip workspace check
    $obj =& $foowd->getObj($where, NULL, FALSE);

    // If object couldn't be found, build a new one
    if ( $obj == NULL )
    {
      $obj = new smdoc_name_lookup($foowd);

      // If save failed, try retrieve again (maybe someone beat you to it..)
      if ( !$obj->save() )
      {
        $obj =& $foowd->getObj($where, NULL, FALSE);
        if ( $obj == NULL )
          trigger_error('Unable to retrieve object from database', E_USER_ERROR);
      }
    }

    return $obj;
  }

}
