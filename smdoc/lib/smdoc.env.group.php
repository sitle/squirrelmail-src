<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Singleton Group Manager.
 * 
 * $Id$
 * @package smdoc
 * @subpackage group
 */

/** Helper class that stores additional groups in the DB. */
include_once(SM_DIR . 'smdoc.class.group.appext.php');

/** Helper class that stores user/group pairs in the DB. */
include_once(SM_DIR . 'smdoc.class.group.user.php');

/**
 * Independent class that manages system and user-defined
 * groups.
 * 
 * @package smdoc
 * @subpackage group
 */
class smdoc_group
{
  /**
   * Reference to the Foowd object.
   * @var object
   */
  var $foowd;

  /**
   * smdoc_group Constructor
   * @param smdoc $foowd Reference to the foowd environment object.
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
   * @param bool forceRefresh If true, force refresh of list in session.
   */
  function initializeUserGroups($forceRefresh=FALSE)
  {
    $session_groups = new input_session('user_groups',REGEX_GROUP);

    if ( isset($session_groups->value) && !$forceRefresh )
      return;

    $allgroups = array();

    $cfg =& $this->foowd->config_settings['group'];

    /*
     * Set Available User Groups:
     *  - start with basic four (Everyone, Author, Gods, Nobody)
     */
    $allgroups['Everyone']  = isset($cfg['Everyone'])  ? $cfg['Everyone']  : 'Everybody';
    $allgroups['Author']    = isset($cfg['Author'])    ? $cfg['Author']    : 'Author';
    $allgroups['Gods']      = isset($cfg['Gods'])      ? $cfg['Gods']      : 'Admin';
    $allgroups['Nobody']    = isset($cfg['Nobody'])    ? $cfg['Nobody']    : 'Nobody';
    $allgroups['Registered']= isset($cfg['Registered'])? $cfg['Registered']: 'Registered';

    /*
     *  - add groups passed to foowd as parameter
     */
    if ( isset($cfg['more_groups']) )
      $groups = &$cfg['more_groups'];

    if (isset($groups) && is_array($groups) ) 
      $allgroups = array_merge($allgroups, $groups);

    /*
     * Additional Groups from DB
     */
    $app_defined = smdoc_group_appext::getInstance($this->foowd);
    $allgroups = array_merge($allgroups, $app_defined->groups);
    
    /*
     * Sort list of groups by display name (value), and add to session
     */
    asort($allgroups);
    $session_groups->set($allgroups);
  }

  /**
   * Adds Group to the list stored in the session
   * 
   * @param mixed group_arg String specifying group id/name,
   *                        or array containing list of groups to add
   */
  function addGroup($group_arg)
  {
    $this->foowd->track('smdoc_group->addGroup', $group_arg);
    $session_groups = new input_session('user_groups',REGEX_GROUP);

    $groups = $session_groups->value;
    $changed = $this->_addGroup($groups, $group_arg);

    /*
     * Sort list of groups by display name (value), and add to session
     */
    if ( $changed )
    {
      asort($groups);
      $session_groups->set($groups);
    }
    $this->foowd->track();
    return $changed;
  }

  /**
   * Removes Group from the list stored in the session
   *
   * @param mixed group_arg String containing group id,
   *                        or array containing list of groups to delete
   */
  function deleteGroup($group_arg)
  {
    $this->foowd->track('smdoc_group->deleteGroup', $group_arg);
    $session_groups = new input_session('user_groups',REGEX_GROUP);

    $changed = $this->_deleteGroup($session_groups->value, $group_arg);
    if ( $changed )
      $session_groups->set($session_groups->value);

    $this->foowd->track();
    return $changed;
  }

  /**
   * Adds single User to group (or list of groups)
   *
   * @param int userid Objectid of user
   * @param mixed groups array of group ids (strings)
   * @see base_user::addGroupsToForm
   */
  function addUser($userid, $groups)
  { 
    $add_groups = array();
    /*
     * Determine which groups user actually needs to be removed from.
     * Make sure group is not a special system group
     */
    if ( is_array($groups) && !empty($groups) )
    {
      foreach ($groups as $grp)
        if ( !smdoc_group::checkGroup($this->foowd, $grp, FALSE) )
          $add_groups[] = $grp;
    }
    elseif ( is_string($groups) && 
             !smdoc_group::checkGroup($this->foowd, $groups, FALSE) )
      $add_groups[] = $groups;

    if ( empty($add_groups) )
      return;

    smdoc_group_user::addUserToGroups($this->foowd, $userid, $add_groups); 
  }

  /**
   * Removes single User from Group (or list of groups)
   *
   * @global array Specifies table information for smdoc_group_user objects.
   * @param int userid Objectid of user
   * @param mixed groups array of group ids (strings)
   * @see base_user::addGroupsToForm
   */
  function removeUser($userid, $groups)
  {
    $rem_groups = array();
    /*
     * Determine which groups user actually needs to be removed from.
     * Make sure group is not a special system group
     */
    if ( is_array($groups) && !empty($groups) )
    {
      foreach ($groups as $grp)
        if ( !smdoc_group::checkGroup($this->foowd, $grp, FALSE) )
          $rem_groups[] = $grp;
    }
    elseif ( is_string($groups) && 
             !smdoc_group::checkGroup($this->foowd, $groups, FALSE) )
      $rem_groups[] = $groups;

    if ( empty($rem_groups) )
      return;

    smdoc_group_user::removeUserFromGroups($this->foowd, $userid, $rem_groups); 
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
   * Match given group id to display name for the specified group.
   * 
   * @param string group String containing group id
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

  /**
   * Check to see if given id specifies a system group.
   * 
   * @static
   * @param smdoc $foowd Reference to the foowd environment object.
   * @param string group String containing group id
   * @param bool   sysOthers   TRUE if should treat groups specified in
   *                           initial config files as system groups
   * @return TRUE if group is a system group, FALSE otherwise
   */
  function checkGroup(&$foowd, $groupId, $sysOthers = TRUE)
  {
    switch ($groupId)
    {
      case 'Author':
      case 'Nobody':
      case 'Everyone':
      case 'Registered':
      case 'System':
        return TRUE;  // group is a system group
      /*
       * 'Gods' is a special case. Yes, it is a system group, but unlike
       * the other system groups, users can be added and removed from it.
       * Membership in the other system groups is arbitrary.. 
       */
      case 'Gods':    
        return $sysOthers;
      default:
        if ( !isset($foowd->config_settings['group']['more_groups']) ||
             !$sysOthers )
          break;

        $otherGroups = $foowd->config_settings['group']['more_groups'];
        // Make sure group id is not in supplemental system groups
        if ( isset($otherGroups[$groupId]) )
          return TRUE;
    }

    return FALSE;
  }

  /**
   * Adds group (or set of groups) provided to both the 
   * provided list, and the set of application defined groups 
   * stored in the DB.
   *
   * @access private
   * @param mixed groupList List of groups (id => displayName) stored in session
   * @param mixed groupArg  Group to add to list, string or array of (id => displayName) pairs
   * @return TRUE if group successfully added
   */
  function _addGroup(&$groupList, $group_arg)
  {
    if ( is_string($group_arg) )
    {
      $group = $group_arg;
      $group_arg = array();
      $group_arg[$group] = $group;
    }
    elseif ( !is_array($group_arg) )
      return FALSE; // we don't know what this thing is, don't muck up the list.

    $app_defined =& smdoc_group_appext::getInstance($this->foowd);

    foreach ( $group_arg as $id => $name )
    { 
      if ( smdoc_group::checkGroup($this->foowd, $id) )
        continue; // can't reset/change system groups

      $app_defined->groups[$id] = $name;
      $app_defined->foowd_changed = TRUE;

      $groupList[$id] = $name;
    }

    return $app_defined->foowd_changed;
  }

  /**
   * Removes Group from the list stored in the session and from 
   * the list of application-defined groups.
   *
   * @access private
   * @param mixed groupList List of groups (id => displayName) stored in session
   * @param mixed group_arg Array or string containing id of group to remove
   * @return TRUE if group successfully deleted
   * @see smdoc_group_user::deleteAll
   * @see smdoc_group_appext::getInstance
   */
  function _deleteGroup(&$groupList, $group_arg)
  {
    if ( is_string($group_arg) )
    {
      $group = $group_arg;
      $group_arg = array();
      $group_arg[$group] = $group;
    }
    elseif ( !is_array($group_arg) )
      return FALSE; // we don't know what this thing is, don't muck up the list.

    $app_defined =& smdoc_group_appext::getInstance($this->foowd);

    foreach ( $group_arg as $id )
    {
      if ( smdoc_group::checkGroup($this->foowd, $id) )
        continue; // can't reset system groups

      unset($groupList[$id]);
      if ( isset($app_defined->groups[$id]) )
      {
        smdoc_group_user::deleteAll($this->foowd, $id);
        unset($app_defined->groups[$id]); 
        $app_defined->foowd_changed = TRUE;
      }
    }

    return $app_defined->foowd_changed;
  }
}

?>
