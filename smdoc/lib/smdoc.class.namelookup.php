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

setClassMeta('smdoc_name_lookup', 'Singleton object lookup/name manager');
setConst('REGEX_SHORTNAME', '/^[a-z_-]{0,11}$/');
setConst('SHORTNAMES_ID', 1063068242);

include_once(SM_DIR . 'smdoc.class.storage.php');

/**
 * Singleton class provides a basic mapping for 
 * well-known simple names (faq, privacy..)
 * to objectid/className pairs.
 */
class smdoc_name_lookup extends smdoc_storage
{
  /**
   * Array mapping shortnames to objectid/classid pairs
   * @var array
   */
  var $shortNames;

  /**
   * Constructor
   * Initialize new instance of smdoc_internal_mapping.
   * @param foowd foowd Reference to Foowd environment 
   */
  function smdoc_name_lookup(&$foowd) 
  {
    parent::smdoc_storage($foowd, '__SHORTNAMES__', SHORTNAMES_ID);
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

  //----------------- STATIC METHODS -------------------

  /**
   * Retrieve singleton instance of naming lookup object
   * @access static
   * @param foowd foowd Reference to Foowd Environment
   * @return Reference to singleton lookup object
   */
  function &getInstance(&$foowd)
  {
    return parent::getInstance($foowd, 
                               'smdoc_name_lookup',
                                META_SMDOC_NAME_LOOKUP_CLASS_ID,
                                SHORTNAMES_ID);
  }

}
