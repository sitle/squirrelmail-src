<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

setPermission('smdoc_group_user','class','list','Everyone');

/** 
 * Singleton Group Manager.
 * 
 * $Id$
 * @package smdoc
 * @subpackage group
 */

/** Base storage class. */
include_once(SM_DIR . 'smdoc.class.storage.php');

/** Class descriptor/Meta information */
setClassMeta('smdoc_group_user','Many-to-Many Mapping of users and groups');

/** 
 * @global array $GROUP_USER_SOURCE
 */
global $GROUP_USER_SOURCE;
$GROUP_USER_SOURCE = array('table' => 'smdoc_group_user',
                           'table_create' => array('smdoc_group_user','makeTable'));
/** 
 * Small group for managing user-group pairs in a special
 * associative table.
 *
 * @package smdoc
 * @subpackage group
 */
class smdoc_group_user extends smdoc_storage
{
  /** 
   * Constructor
   * @param smdoc  foowd  Reference to the foowd environment object.
   * @param string group  Name of group - acts as title
   * @param int    userid Objectid of user - acts as objectid
   */
  function smdoc_group_user(&$foowd, $group, $userid)
  {
    global $GROUP_USER_SOURCE;

    $foowd->track('smdoc_group_user', $group, $userid);
    parent::smdoc_storage($foowd, $group, $userid);

    // add to loaded object reference list
    $foowd->database->addToLoadedReference($this, $GROUP_USER_SOURCE);

    $foowd->track();
  }

  /**
   * Serliaisation sleep method. 
   * This is a VERY simple class. 
   * Only include the objectid and title fields.
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
   * Include just enough meta information to satisfy
   * the database and other elements of Foowd.
   * 
   * @global array Specifies table information for smdoc_group_user objects.
   */
  function __wakeup() 
  {
    global $GROUP_USER_SOURCE;
    $this->foowd_source = $GROUP_USER_SOURCE;

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
    $this->foowd->track('smdoc_group_user->delete');
    $result = $this->foowd->database->delete($this);
    $this->foowd->track(); 
    return $result ? TRUE : FALSE;
  }

  /**
   * Make a Foowd database table.
   *
   * When a database query fails due to a non-existant database table, this
   * method is invoked to create the missing table and execute the SQL
   * statement again.
   *
   * @static
   * @global array Specifies table information for smdoc_group_user objects.
   * @param smdoc foowd Reference to the foowd environment object.
   * @return mixed The resulting database query resource or FALSE on failure.
   */
  function makeTable(&$foowd) 
  {
    global $GROUP_USER_SOURCE;
    $foowd->track('smdoc_group_user->makeTable');

    $sql = 'CREATE TABLE `'.$GROUP_USER_SOURCE['table'].'` (
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

  /**
   * Adds User/Group pairs matching given userid and 
   * list of groups.
   *
   * @static
   * @param smdoc  foowd  Reference to the foowd environment object.
   * @param int userid Objectid of user
   * @param mixed groups array of group ids (strings)
   */
  function addUserToGroups(&$foowd, $userid, $groups)
  {
    if ( !is_array($groups) )
      return;
    if ( empty($groups) )
      return;

    foreach ( $groups as $grp )
    {
      $smgrp = new smdoc_group_user($foowd, $grp, $userid);
      $smgrp->save();
    }
  }

  /**
   * Removes User/Group pairs matching given userid and 
   * list of groups.
   *
   * @static
   * @global array Specifies table information for smdoc_group_user objects.
   * @param smdoc  foowd  Reference to the foowd environment object.
   * @param int userid Objectid of user
   * @param mixed groups array of group ids (strings)
   */
  function removeUserFromGroups(&$foowd, $userid, $groups)
  {
    if ( !is_array($groups) )
      return;
    if ( empty($groups) )
      return;

    global $GROUP_USER_SOURCE;
    $index = array('*');
    $where = array('objectid' => $userid);

    // Fetch user's current groups, no order, no limit,
    // get actual objects, and don't bother with workspaces.
    $current_groups = $this->foowd->getObjList($index, $GROUP_USER_SOURCE,
                                               $where, NULL, NULL, TRUE, FALSE);
    if ( empty($current_groups) )
      return;

    foreach ( $current_groups as $smgrp )
    {
      if ( in_array($smgrp->title, $groups) )
        $smgrp->delete();
    }
  }

// ----------------------------- class methods --------------

  /**
   * Output a list of all known groups.
   *
   * Values set in template:
   *  + grouplist     - below
   *
   * Sample contents of $t['grouplist']:
   * <pre>
   * array (
   *   'GroupId' => array ( 
   *                 'group_name' => 'GroupName',
   *                 'group_count' => 8,
   *                )
   * )
   * </pre>
   *
   * @static
   * @global array Specifies table information for user persistance.
   * @param smdoc foowd Reference to the foowd environment object.
   * @param string className The name of the class.
   */
  function class_list(&$foowd, $className)
  {
    $foowd->track('smdoc_group->class_list');

    global $GROUP_USER_SOURCE;
    $groupList = array();

    /*
     * Get list of groups that includes only those
     * that users can be assigned to
     */
    $groups = $foowd->getUserGroups(FALSE);
    if ( !empty($groups) )
    {
      foreach ( $groups as $id => $name )
      {
        $elem = array();
        $elem['group_name'] = $name;
        $elem['group_count'] = $foowd->database->count($GROUP_USER_SOURCE, 
                                                       array('title' => $id));
        $groupList[$id] = $elem; 
      }
    }

    $foowd->template->assign('grouplist', $groupList);
    $foowd->track();
  }
}

