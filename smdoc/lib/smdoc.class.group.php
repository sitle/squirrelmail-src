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

class smdoc_group
{
  /**
   * smdoc_group Constructor
   * 
   * @param object foowd The foowd environment object.
   */
  function smdoc_group(&$foowd)
  {
    $this->initializeUserGroups($foowd);
  }

  /**
   * initializeUserGroups initializes an array in the session containing a list
   * of user groups as 'internal name/objectid' => 'external name'.
   *
   * @param array   $groups     - array of additional groups to include
   *                              in group list (for foowd object use).
   */
  function initializeUserGroups(&$foowd, $forceRefresh=FALSE )
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
    $allgroups['Gods']       = getConstOrDefault('GROUPNAME_ADMIN', 'Gods');
    $allgroups['Nobody']     = getConstOrDefault('GROUPNAME_NOBODY', 'Nobody');
    $allgroups['Registered'] = getConstOrDefault('GROUPNAME_REGISTERED', 'Registered');


    /*
     *  - add groups passed to foowd as parameter
     */
    if ( isset($foowd->config_settings['group']['more_groups']) )
      $groups = &$foowd->config_settings['group']['more_groups'];

    if (isset($groups) && is_array($groups) ) 
      $allgroups = array_merge($allgroups, $groups);
    
    /*
     *  - add groups defined in DB
     */

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
  function addGroup(&$foowd, $group_arg)
  {
    $session_groups = new input_session('user_groups',REGEX_GROUP);
    if ( !isset($session_groups->value) ) {
      initializeUserGroups($foowd);
      $session_groups->refresh();
    }

    if ( is_array($group_arg) ) {
      foreach ($group_arg as $group) {
        _addGroup($session_groups->value, $group);
      }
    } else {
      _addGroup($session_groups->value, $group_arg);
    }

    /*
     * Sort list of groups by display name (value), and add to session
     */
    asort($session_groups);
    $session_groups->set($session_groups);
  }

  /**
   * deleteGroup
   * removes Group from the list stored in the session
   */
  function deleteGroup(&$foowd, $group_arg)
  {
    $session_groups = new input_session('user_groups',REGEX_GROUP);
    if ( !isset($session_groups->value) ) {
      initializeUserGroups($foowd);
      $session_groups->refresh();
    }

    if ( is_array($group_arg) ) {
      foreach ($group_arg as $group) {
        _deleteGroup($session_groups->value, $group);
      }
    } else {
      _deleteGroup($session_groups->value, $group_arg);
    }

    $session_groups->set($session_groups->value);
  }

  /**
   * getUserGroups returns an array containing a list
   * of user groups as 'internal name/objectid' => 'external name'.
   *
   * If userAssignOnly is TRUE, then only groups users can be assigned
   * to will be returned - meaning that groups like Everyone, Nobody, and
   * Author, which are useful for defining permissions but are not assignable
   * to users, will be left out.
   */
  function getUserGroups(&$foowd, $userAssignOnly = FALSE)
  {
    $session_groups = new input_session('user_groups',REGEX_GROUP);
    if ( !isset($session_groups->value) ) {
      initializeUserGroups($foowd);
      $session_groups->refresh();
    }

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
   */
  function getDisplayName(&$foowd, $group)
  {
    $session_groups = new input_session('user_groups',REGEX_GROUP);
    if ( !isset($session_groups->value) ) {
      initializeUserGroups($foowd);
      $session_groups->refresh();
    }

    if ( isset($session_groups->value[$group]) )
      return $session_groups->value[$group];
    else
      return NULL;
  }

  /**
   * checkGroup
   * checks for System group
   */
  function _checkGroup($groupID)
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
        return FALSE;
    }
  }

  /**
   * addGroup
   * adds Group to the list stored in the session
   */
  function _addGroup(&$groupList, $group_arg)
  {
    if ( is_object($group_arg) ) {
      $groupName = $group_arg->getTitle();
      $groupId   = $group_arg->objectid;
    } elseif ( is_string($group_arg) ) {
      $groupName = $group_arg;
      $groupId   = $group_arg;
    } else {
      return; // we don't know what this thing is, don't muck up the list.
    }

    if ( _checkGroup($groupId) )
      return; // can't reset system groups

    $groupList[$groupId] = $groupName;
  }

  /**
   * _deleteGroup
   * removes Group from the list stored in the session
   */
  function _deleteGroup(&$groupList, $group_arg)
  {
    if ( is_object($group_arg) ) {
      $groupId   = $group_arg->objectid;
    } elseif ( is_string($group_arg) ) {
      $groupId   = $group_arg;
    } else {
      return; // we don't know what this thing is, don't muck up the list.
    }

    if ( _checkGroup($groupId) )
      return; // can't reset system groups

    unset($groupList[$groupId]);
  }

}

?>
