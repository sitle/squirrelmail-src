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

/** Helper classes that actually store group data in the DB. */
include_once(SM_DIR . 'smdoc.class.group.php');

/**
 * Independent class that manages list of groups
 * cached in the session
 */
class smdoc_group
{
  /**
   * Reference to the Foowd object.
   *
   * @var object
   */
  var $foowd;

  /**
   * smdoc_group Constructor
   * 
   * @param object foowd The foowd environment object.
   */
  function smdoc_group(&$foowd)
  {
    $this->foowd =& $foowd;
    $this->initializeUserGroups();
  }

  /**
   * initializeUserGroups initializes an array in the session containing a list
   * of user groups as 'internal name/objectid' => 'external name'.
   *
   * @param array   $groups     - array of additional groups to include
   *                              in group list (for foowd object use).
   */
  function initializeUserGroups($forceRefresh=FALSE)
  {
    $session_groups = new input_session('user_groups',REGEX_GROUP);

    if ( isset($session_groups->value) && !$forceRefresh )
      return;

    $allgroups = array();

    /*
     * Set Available User Groups:
     *  - start with basic four (Everyone, Author, Gods, Nobody)
     */
    $allgroups['Everyone']   = getConstOrDefault('GROUPNAME_EVERYONE', 'Everyone');
    $allgroups['Author']     = getConstOrDefault('GROUPNAME_AUTHOR', 'Author');
    $allgroups['Gods']       = getConstOrDefault('GROUPNAME_ADMIN', 'Admin');
    $allgroups['Nobody']     = getConstOrDefault('GROUPNAME_NOBODY', 'Nobody');
    $allgroups['Registered'] = getConstOrDefault('GROUPNAME_REGISTERED', 'Registered');


    /*
     *  - add groups passed to foowd as parameter
     */
    if ( isset($this->foowd->config_settings['group']['more_groups']) )
      $groups = &$this->foowd->config_settings['group']['more_groups'];

    if (isset($groups) && is_array($groups) ) 
      $allgroups = array_merge($allgroups, $groups);

    /*
     * Additional Groups from DB
     */
    $app_defined = smdoc_app_groups::getInstance($this->foowd);
    $allgroups = array_merge($allgroups, $app_defined->groups);
    
    /*
     * Sort list of groups by display name (value), and add to session
     */
    asort($allgroups);
    $session_groups->set($allgroups);
  }

  /**
   * addGroup
   * adds Group to the list stored in the session
   */
  function addGroup($group_arg)
  {
    $session_groups = new input_session('user_groups',REGEX_GROUP);

    $changed = _addGroup($session_groups->value, $group_arg);

    /*
     * Sort list of groups by display name (value), and add to session
     */
    if ( $changed )
    {
      asort($session_groups);
      $session_groups->set($session_groups);
    }
  }

  /**
   * deleteGroup
   * removes Group from the list stored in the session
   * @param mixed group_arg array of groups, or id of group to delete
   */
  function deleteGroup($group_arg)
  {
    $session_groups = new input_session('user_groups',REGEX_GROUP);

    $changed = _deleteGroup($session_groups->value, $group_arg);
    if ( $changed )
      $session_groups->set($session_groups->value);
  }

  /**
   * adds User to group (or list of groups)
   * @param int userid Objectid of user
   * @param mixed groups array of group ids (strings)
   */
  function addUser($userid, $groups)
  { 
    if ( is_array($groups) && !empty($groups) )
    {
      foreach ( $groups as $id )
      {
        $smgrp = new smdoc_user_group($this->foowd, $id, $userid);
        $smgrp->save();
      }
    }
  }

  /**
   * removes User from Group (or list of groups)
   * @param int userid Objectid of user
   * @param mixed groups array of group ids (strings)
   */
  function removeUser($userid, $groups)
  {
    global $USER_GROUP_SOURCE;
    $index = array('*');
    $where = array('objectid' => $userid);

    if ( is_array($groups) && !empty($groups) )
    {
      // Fetch user's current groups, no order, no limit,
      // get actual objects, and don't bother with workspaces.
      $current_groups = $this->foowd->getObjList($index, $USER_GROUP_SOURCE,
                                               $where, NULL, NULL, TRUE, FALSE);
      foreach ( $current_groups as $smgrp )
      {
        if ( in_array($smgrp->title, $groups) )
          $smgrp->delete();
      }
    }    
  }

  /**
   * getUserGroups returns an array containing a list
   * of user groups as 'internal name/objectid' => 'external name'.
   *
   * If userAssignOnly is TRUE, then only groups users can be assigned
   * to will be returned - meaning that groups like Everyone, Nobody, and
   * Author, which are useful for defining permissions but are not assignable
   * to users, will be left out.
   * 
   * @param bool userAssignOnly If true, leave out system groups
   * @return array of groups
   */
  function getUserGroups($userAssignOnly = FALSE)
  {
    $session_groups = new input_session('user_groups',REGEX_GROUP);

    if ( $userAssignOnly )
    {
      unset($session_groups->value['Everyone']);
      unset($session_groups->value['Author']);
      unset($session_groups->value['Nobody']);
      unset($session_groups->value['Registered']);
    }

    return $session_groups->value;
  }

  /**
   * getDisplayGroupName returns a string containing the display
   * name for the specified group (or NULL if not found).
   * 
   * @param str group String containing group id
   * @return String containing display name, or NULL if group not found
   */
  function getDisplayName($group)
  {
    $session_groups = new input_session('user_groups',REGEX_GROUP);

    if ( isset($session_groups->value[$group]) )
      return $session_groups->value[$group];
    else
      return NULL;
  }

  function _checkGroup($groupId)
  {
    switch ($groupId)
    {
      case 'Gods':
      case 'Author':
      case 'Nobody':
      case 'Everyone':
      case 'Registered':
      case 'System':
        return TRUE;  // group is a system group
      default:
        // Make sure group id is not in supplemental system groups
        if ( isset($this->foowd->config_settings['group']['more_groups']) &&
             in_array($this->foowd->config_settings['group']['more_groups'], $groupId) )
          return TRUE;

        return FALSE;
    }
  }

  /**
   * adds Group to the list stored in the session,
   * and to the list of application defined groups
   * @access private
   * @param mixed groupList List of groups (id => displayName) stored in session
   * @param mixed groupArg  Group to add to list, string or array of (id => displayName) pairs
   * @return TRUE if group successfully added
   */
  function _addGroup(&$groupList, $group_arg)
  {
    if ( is_string($group_arg) ) 
      $group[$group_arg] = $group_arg;
    elseif ( !is_array($group_arg) )
      return FALSE; // we don't know what this thing is, don't muck up the list.

    $changed = FALSE;
    $app_defined = smdoc_app_groups::getInstance($this->foowd);

    foreach ( $group_arg as $id => $name )
    { 
      if ( _checkGroup($id) )
        continue; // can't reset/change system groups

      $app_defined->groups[$id] = $name; 
      $groupList[$id] = $name;
      $changed = TRUE;
    }

    if ( $changed )
      $app_defined->save();

    return $changed;
  }

  /**
   * removes Group from the list stored in the session
   * @access private
   * @param mixed groupList List of groups (id => displayName) stored in session
   * @param mixed group_arg Array or string containing id of group to remove
   * @return TRUE if group successfully deleted
   */
  function _deleteGroup(&$groupList, $group_arg)
  {
    if ( is_string($group_arg) ) 
      $group[] = $group_arg;
    elseif ( !is_array($group_arg) )
      return FALSE; // we don't know what this thing is, don't muck up the list.

    $changed = FALSE;
    $app_defined = smdoc_app_groups::getInstance($this->foowd);

    foreach ( $group as $id )
    {
      if ( _checkGroup($id) )
        continue; // can't reset system groups

      unset($groupList[$groupId]);
      if ( isset($app_defined->groups[$id]) )
      {
        unset($app_defined->groups[$id]); 
        $changed = TRUE;
      }
    }

    if ( $changed )
      $app_defined->save();
  }
}

?>
