<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Name lookup implementation used to map easy object name like 'faq' to 
 * an objectid/classid pair for retrieval.
 *
 * $Id$
 *
 * @package smdoc
 */

/** Class descriptor/Meta information */
setClassMeta('smdoc_name_lookup', 'Singleton object lookup/name manager');
setConst('SHORTNAMES_ID', 1063068242);

/** Regex to validate shortname */
setConst('REGEX_SHORTNAME', '/^[a-z_-]{0,11}$/');

/** Base storage class */
include_once(SM_DIR . 'smdoc.class.storage.php');

/**
 * Singleton class provides a basic mapping for 
 * well-known simple names (faq, privacy..)
 * to objectid/className pairs.
 * 
 * @package smdoc
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
   * @param smdoc $foowd Reference to Foowd environment 
   */
  function smdoc_name_lookup(&$foowd) 
  {
    parent::smdoc_storage($foowd, '__SHORTNAMES__', SHORTNAMES_ID);
    $this->shortNames = array();
  }

  /**
   * Map given objectName to objectid/classid pair.
   * 
   * @param string $objectName Name of object to locate.
   * @param int $objectid ID of located object
   * @param int $classid  ID of class for located object
   * @return TRUE if found, false if not.
   */
  function findObject($objectName, &$objectid, &$classid)
  {
    $this->foowd->track('smdoc_name_lookup->findObject', $objectName);

    $result = FALSE;
    if ( isset($this->shortNames[$objectName]) )
    {
      $objectid = $this->shortNames[$objectName]['objectid'];
      $classid  = $this->shortNames[$objectName]['classid'];
      $result = TRUE;
    }

    $this->foowd->track();
    return $result;
  }

  /**
   * Clean up associated short name
   * 
   * @param object $obj  Object with shortname to be removed
   */
  function deleteShortName(&$obj)
  {
    $oid = intval($obj->objectid);
    if ( isset($this->shortNames[$oid]) )
    {
      $name = $this->shortNames[$oid];
      unset($this->shortNames[$oid]);
      unset($this->shortNames[$name]);
      $this->save();
    }
  }

  /**
   * Add short name to object
   * 
   * @param object $obj  Object to add shortname for
   * @param string $name Short name for object
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
    $this->shortNames[$name]['title'] = $obj->title;
    $this->shortNames[$oid] = $name;
    
    $this->save();
  }

  /**
   * Add textbox to form for association of shortname
   * with object.
   *
   * @static
   * @param object     $obj      Object to make shortname for
   * @param input_form $form     Form to add element to
   */
  function addShortNameToForm(&$obj, &$form, &$error)
  {
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
      if ( empty($shortBox->value) ) 
        $this->deleteShortName($obj);
      elseif ( isset($this->shortNames[$shortBox->value]) )
      {
        $error[] = _("Object ShortName is already in use.");
        $shortBox->wasValid = FALSE;
      }
      else
        $this->addShortName($obj, $shortBox->value);
    }
 
    $form->addObject($shortBox);
  }

  /**
   * Retrieve singleton instance of naming lookup object
   *
   * @static
   * @param smdoc $foowd Reference to the foowd environment object.
   * @return Reference to singleton lookup object
   */
  function &getInstance(&$foowd)
  {
    return parent::getInstance($foowd, 
                               'smdoc_name_lookup',
                                META_SMDOC_NAME_LOOKUP_CLASS_ID,
                                SHORTNAMES_ID);
  }

// ----------------------------- class methods --------------

  /**
   * Output a list of all known short names
   *
   * Values set in template:
   *  + shortlist       - below
   *  + addForm         - Form for adding a new shortname
   *  + deleteForm      - Form for deleting shortnames
   *
   * Sample contents of $t['shortlist']:
   * <pre>
   * array (
   *   shortname => array ( 
   *                 'objectid' => 8894324,
   *                 'classid' => 9321833,
   *                 'title' => 'Some page title',
   *                 'name_delete' => checkbox for deletion of shortname
   *                )
   * )
   * </pre>
   *
   * @static
   * @param smdoc  $foowd Reference to the foowd environment object.
   * @param string $className The name of the class.
   */
  function class_list(&$foowd, $className)
  {
    $foowd->track('smdoc_name_lookup->class_list');

    include_once(INPUT_DIR.'input.textbox.php');
    include_once(INPUT_DIR.'input.form.php');
    include_once(INPUT_DIR.'input.checkbox.php');

    $shortList = array();

    $lookup =& smdoc_name_lookup::getInstance($foowd);

    /*
     * Create form for clearing short names
     */ 
    $deleteForm = new input_form('deleteForm', NULL, SQ_POST, _("Delete Short Names"));
    if ( !empty($lookup->shortNames) )
    {
      foreach ( $lookup->shortNames as $idx => $value )
      {
        if ( is_string($idx) )
        {
          $elem = $value;
          $deleteBox = new input_checkbox($idx, FALSE, 'Delete');

          if ( $deleteForm->submitted() && $deleteBox->checked )
          {
            $lookup->deleteShortName($idx);
            unset($elem);
          }
          else
          {
            // Add box to form and array
            $deleteForm->addObject($deleteBox);
            $elem['name_delete'] =& $deleteForm->objects[$idx];
          }
           
          if ( isset($elem) )
            $shortList[$idx] = $elem;
        }
      }
    }

    $foowd->template->assign_by_ref('deleteForm', $deleteForm);
    $foowd->template->assign('shortList', $shortList);
    $foowd->track();
  }

}
