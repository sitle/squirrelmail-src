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
 * Two classes are defined in this file:
 * <UL>
 * <LI>smdoc_user_group is a class that manages user-group 
 * pairs in a special table for group queries.</LI>
 * <LI>smdoc_app_groups manages a singleton record in the main
 * table for tracking additional groups.</LI>
 * </UL>
 * 
 * @package smdoc
 * @subpackage group
 * @see smdoc_user_group
 * @see smdoc_app_groups
 */

/** Class descriptor/Meta information */
setClassMeta('smdoc_user_group','Mapping of user to group');

/** 
 * @global array $USER_GROUP_SOURCE
 */
$USER_GROUP_SOURCE = array('table' => 'smdoc_user_group',
                           'table_create' => array('smdoc_user_group','makeTable'));

/** 
 * Small group for managing user-group pairs in a special
 * associative table.
 *
 * @package smdoc
 * @subpackage group
 */
class smdoc_user_group extends smdoc_storage
{
  /** 
   * Constructor
   * @param smdoc foowd Reference to the foowd environment object.
   */
  function smdoc_user_groups(&$foowd, $group, $userid)
  {
    parent::smdoc_storage($foowd, $group, $userid);
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
    $returnArray[] = 'objectid';
    $returnArray[] = 'title';
    return $returnArray;
  }

  /**
   * Serialisation wakeup method.
   */
  function __wakeup() 
  {
    global $USER_GROUP_SOURCE;
    $this->foowd_source = $USER_GROUP_SOURCE;

    $this->foowd_indexes['objectid'] = array('name' => 'objectid', 'type' => 'INT', 'notnull' => TRUE);     
    $this->foowd_indexes['title'] = array('name' => 'title', 'type' => 'VARCHAR', 'length' => 32, 'notnull' => TRUE);
 
    // Original access vars
    $this->foowd_original_access_vars['objectid'] = $this->objectid;
    $this->foowd_original_access_vars['title'] = $this->title;

    // Default primary key
    $this->foowd_primary_key = array('title','objectid');
  }

  /**
   * Delete the object.
   *
   * @return bool Returns TRUE on success.
   */
  function delete()
  {
    $this->foowd->track('smdoc_user_group->delete');
    $result = $this->foowd->database->delete($this);
    $this->foowd->track(); 
    return $result ? TRUE : FALSE;
  }

  /**
   * Make a Foowd database table.
   *
   * When a database query fails due to a non-existant database table, this
   * method is envoked to create the missing table and execute the SQL
   * statement again.
   *
   * @param smdoc foowd Reference to the foowd environment object.
   * @param str SQLString The original SQL string that failed to execute due to missing database table.
   * @return mixed The resulting database query resource or FALSE on failure.
   */
  function makeTable(&$foowd) 
  {
    global $USER_GROUP_SOURCE;
    $foowd->track('base_user->makeTable');
    $sql = 'CREATE TABLE `'.$USER_GROUP_SOURCE['table'].'` (
              `objectid` int(11) NOT NULL default \'0\',
              `title` varchar(32) NOT NULL default \'\',
              `object` longblob,
              PRIMARY KEY  (`objectid`,`title`),
              KEY `idxuser_objectid` (`objectid`),
              KEY `idxuser_title` (`title`)
            );';
    $result = $foowd->database->query($sql);
    $foowd->track();
    return $result;
  }
}

setClassMeta('smdoc_app_groups', 'Management of additional non-system groups');
setConst('APP_GROUPS_ID',-1807156497);

include_once(SM_DIR . 'smdoc.class.storage.php');

/**
 * Small singleton class that stores additional groups in the DB
 * Managed by smdoc_group
 *
 * @package smdoc
 * @subpackage group
 */
class smdoc_app_groups extends smdoc_storage
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
  function smdoc_app_groups(&$foowd)
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
                               'smdoc_app_groups',
                                META_SMDOC_APP_GROUPS_CLASS_ID,
                                APP_GROUPS_ID);    
  }  
}

