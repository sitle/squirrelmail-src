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

class GroupManager 
{
  /** STATIC
   * createUserGroups initializes an array in the session containing a list 
   * of user groups as 'internal name/objectid' => 'external name'.
   * 
   * @param array   $groups     - array of additional groups to include
   *                              in group list (for foowd object use).
   */
  function createUserGroups(&$foowd, $groups=NULL, $forceRefresh=FALSE ) 
  {
    $session_groups = new input_session('user_groups',REGEX_GROUP);

    if ( isset($session_groups->value) && !$forceRefresh ) 
      return;
      
    $allgroups = array();
  
    /*
     * Set Available User Groups:
     *  - start with basic four (Everyone, Author, Gods, Nobody)
     */
    $allgroups['Everyone'] = getConstOrDefault('GROUPNAME_EVERYONE', 'Everyone');
    $allgroups['Author']   = getConstOrDefault('GROUPNAME_AUTHOR', 'Author');
    $allgroups['Gods']     = getConstOrDefault('GROUPNAME_ADMIN', 'Gods');
    $allgroups['Nobody']   = getConstOrDefault('GROUPNAME_NOBODY', 'Nobody');
    
    /*
     *  - add groups passed in as parameter
     */
    if (isset($groups) && is_array($groups) ) { 
      $allgroups = array_merge($allgroups, $groups);
    }
  
    /*
     *  - add groups defined via constants
     */
    $foo = 1;
    while (defined('USERGROUP'.$foo)) { 
      $group = constant('USERGROUP'.$foo);
      $allgroups[$group] = $group;
      $foo++;
    }
  
    /*
     *  - add groups defined via Group class
     */
    if (defined('GROUP_CLASS_ID')) 
    {
      $userGroups = $this->retrieveObjects(
                            array('classid = '.GROUP_CLASS_ID),
                            NULL,
                            array('title'));
      if ($userGroups) 
      {
        while ($userGroup = $this->retrieveObject($userGroups))
          $allgroups[$userGroup->objectid] = $userGroup->getTitle();
      }
    }    
  
    /*
     * Sort list of groups by display name (value), and add to session
     */
    asort($allgroups);
    $session_groups->set($allgroups);
  }
  
  /** STATIC
   * getUserGroups returns an array containing a list 
   * of user groups as 'internal name/objectid' => 'external name'.
   * 
   * If userAssignOnly is TRUE, then only groups users can be assigned 
   * to will be returned - meaning that groups like Everyone, Nobody, and
   * Author, which are useful for defining permissions but are not assignable
   * to users, will be left out.
   */
  function getUserGroups(&$foowd,$userAssignOnly = FALSE) 
  {
    $session_groups = new input_session('user_groups',REGEX_GROUP);
    if ( !isset($session_groups->value) ) {
      GroupManager::createUserGroups($foowd);
      $session_groups->refresh();
    }
      
    if ( $userAssignOnly ) 
    {
      unset($session_groups->value['Everyone']);
      unset($session_groups->value['Author']);
      unset($session_groups->value['Nobody']);
    }
    
    return $session_groups->value;
  }
  
  /** STATIC
   * getDisplayGroupName returns a string containing the display
   * name for the specified group (or NULL if not found).
   */
  function getDisplayName(&$foowd, $group) 
  {
    $session_groups = new input_session('user_groups',REGEX_GROUP);
    if ( !isset($session_groups->value) )
      createUserGroups($foowd);

    if ( isset($session_groups->value[$group]) )
      return $session_groups->value[$group];
    else
      return NULL;            
  }


}

?>