<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition to the Framework for Object Orientated Web Development (Foowd).
 *
 * It provides methods for managing groups and tracking permissions to 
 * consolidate operations using groups without using the groups class.
 *
 * $Id$
 */

class i18nManager 
{
  /** 
   * STATIC
   * Initializes array containing objectid, icon, and language code 
   * for each defined translation
   */
  function initialize(&$foowd, $forceRefresh = FALSE) 
  {
    $session_langs = new input_session('translations', NULL);
    if ( isset($session_langs->value) && !$forceRefresh ) 
      return;
      
    $translations = array();
  
    /*
     *  - add groups defined via Group class
     */
    if (defined('TRANSLATION_CLASS_ID')) 
    {
      $t_objects = $foowd->retrieveObjects(
                            array('classid = '.TRANSLATION_CLASS_ID),
                            NULL,
                            array('title'));

      while ($trans_obj = $foowd->retrieveObject($t_objects))
      {
        $translations[$trans_obj->objectid]['icon'] = $trans_obj->language_icon;
        $translations[$trans_obj->objectid]['title'] = $trans_obj->title;
        unset($trans_obj);
      }
    }    
    $session_langs->set($translations);
  }
  
  /** STATIC
   * Retrieve array for given object id.
   */
  function getDisplayInfo(&$foowd, $objectid = NULL)
  {
    $session_langs = new input_session('translations', NULL);
    if ( !isset($session_langs->value) ) {
      i18nManager::initialize($foowd);
      $session_langs->refresh();
    }
    
    if ($objectid == NULL)
      return $session_langs->value;
   
    if ( isset($session_langs->value[$objectid]) )
      return $session_langs->value[$objectid];
    else
      return NULL;
  }
}

?>