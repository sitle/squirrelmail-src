<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Defines small class for storing additional groups
 * in a single DB record.
 * 
 * $Id$
 * @package smdoc
 * @subpackage group
 */

/** Class descriptor/Meta information */
setClassMeta('smdoc_group_appext', 'Management of additional non-system groups');
setConst('APP_GROUPS_ID',-1807156497);

/** Base Storage class */
include_once(SM_DIR . 'smdoc.class.storage.php');

/**
 * Small singleton class that stores additional groups in the DB
 * Managed by smdoc_group
 *
 * @package smdoc
 * @subpackage group
 */
class smdoc_group_appext extends smdoc_storage
{
  /**
   * Array containing application specific groups
   * @var array
   */
  var $groups;

  /** 
   * Constructor
   * @param smdoc foowd Reference to the foowd environment object.
   */
  function smdoc_group_appext(&$foowd)
  {
    parent::smdoc_storage($foowd, '__GROUPS__', APP_GROUPS_ID);
    $this->groups = array();
  }

  /**
   * Retrieve singleton instance of application group object
   * @static
   * @param smdoc foowd Reference to the foowd environment object.
   * @return Reference to singleton application group object
   */
  function &getInstance(&$foowd)
  {
    return parent::getInstance($foowd, 
                               'smdoc_group_appext',
                                META_SMDOC_GROUP_APPEXT_CLASS_ID,
                                APP_GROUPS_ID);    
  }  
}


