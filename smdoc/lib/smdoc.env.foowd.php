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

define('FOOWD_CLASS_NAME', 'smdoc');
include_once(SM_DIR . 'env.foowd.php');

/**
 * The SMDoc Foowd environment class.
 *
 * Sets up the Foowd environment, including database connection, user group
 * management and user initialisation, and provides methods for accessing
 * objects within the system.
 *
 * @package smdoc
 * @version 0.8.4
 */
class smdoc extends foowd
{

  /**
   * Constructs a new environment object.
   * smdoc: Simplified for smdoc objects, different group implementation.
   * @param array $settings Array of settings for this Foowd environment.
   */
  function smdoc($settings)
  {
    $this->config_settings =& $settings;

    /*
     * initialize debugging
     */
    $this->debug = FALSE;
    if ( $settings['debug']['debug_enabled'] )
    {
      require_once(SM_DIR . 'smdoc.env.debug.php');
      $this->debug = new smdoc_debug($this);
    }

    $this->track('smdoc->constructor');

    /*
     * Initialize Database connection
     */
    require_once(SM_DIR . 'smdoc.env.database.php');
    $this->database = new smdoc_db($this);

    /*
     * Initialize template
     */
    require_once(SM_DIR . 'env.template.php');
    $this->template = new foowd_template($this);
    $this->template->template_dir = $settings['template']['template_dir'];
    $this->template->assign_by_ref('foowd', $this);

    /*
     * User group initialization
     */
    require_once(SM_DIR . 'smdoc.class.group.php');
    $this->groups = new smdoc_group($this);

    /*
     * Get current User
     */
    require_once(SM_DIR . 'smdoc.class.user.php');
    $this->user = smdoc_user::factory($this);

    $this->track();
  }

  /**
   * Class destructor.
   *
   * Destroys the environment object outputting debugging information and
   * closing the database connection.
   * smdoc: removed debug display, handled by template
   */
  function destroy()
  {
    if ( $this->database )
      $this->database->destroy(); // close DB
    unset($this);               // unset object
  }

  /**
   * Clean up foowd object, then set Location header for redirect.
   * @param string new_loc Forwarding URL
   */
  function loc_forward($new_loc)
  {
    $this->destroy();
    header('Location: ' . $new_loc);
    exit;
  }

  /**
   * Get the current user from the database.
   *
   * Given the array of uesr details, fetch the corrisponding user object from
   * the database, unserialise and return it.
   *
   * @param array userArray The user array passed into <code>{@link foowd::foowd}</code>.
   * @return mixed The selected user object or FALSE on failure.
   */
  function fetchUser($userArray = NULL) {
    return smdoc_user::fetchUser($this, $userArray);
  }

  /**
   * getUserGroups returns an array containing a list of user groups
   * as 'internal name/objectid' => 'external name'.
   *
   * This method caches the list of groups in the session, only
   * creating the list if it hasn't already been created during this session.
   *
   * @param optional boolean $includeSpecialGroups - whether or not to include all groups.
   * @param optional boolean $memberOnly - whether or not to restrict to only groups user is a member of
   * @return array An array of user groups.
   */
  function getUserGroups( $includeSpecialGroups = TRUE, $memberOnly = FALSE)
  {
    if ( $includeSpecialGroups )
    {
      $allgroups = $this->groups->getUserGroups(FALSE);
      return $allgroups;
    }

    $usergroups = $this->groups->getUserGroups(TRUE);

    if ( $memberOnly )
    {
      $items = array();
      foreach ($usergroups as $group => $name)
      {
        if ( $this->user->inGroup($group) )
          $items[$group] = $name;
      }
      return $items;
    }
    return $usergroups;
  }

  /**
   * Returns true if user has permission
   *
   * @param str className Name of the class the method belongs to.
   * @param str methodName Name of the method.
   * @param string type class/object method
   * @param object objectReference to current object being checked (may be NULL)
   * @return bool TRUE if user has access to method
   */
  function hasPermission($className, $methodName, $type, &$object)
  {
    if ( isset($object) && is_object($object) ) {
      $creatorid = isset($object->creatorid) ? $object->creatorid : NULL;
      if ( isset($object->permissions[$methodName]) )
        $methodPermission = $object->permissions[$methodName];
    } else {
      $creatorid = NULL;
    }

    if ( !isset($methodPermission) )
      $methodPermission = getPermission($className, $methodName, $type);

    return $this->user->inGroup($methodPermission, $creatorid);
  }

  /**
   * Fetch one version of an object.
   *
   * @param  array where Array of values to find object by
   * @param  mixed in_source Source to get object from
   * @param  array indexes Array of indexes to fetch
   * @param  bool  setWorkspace get specific workspace id (or any workspace ok)
   * @return mixed The retrieved object or an array containing the retrieved object and the joined objects.
   */
  function &getObj($where = NULL, $in_source = NULL, $indexes = NULL, 
                   $setWorkspace = TRUE)
  {
    $this->track('smdoc->getObj', $where, $in_source);

    if ( isset($where['objectid']) )
      $oid = $where['objectid'];
    else
      $oid = $this->config_settings['site']['default_objectid'];

    // @ELH - search for external items first
    $new_obj =& smdoc_external::factory($this, $oid);
    if ( $new_obj == NULL )
    {
      if ( $in_source == NULL && isset($where['classid']) )
      {
        switch($where['classid'])
        {
          case USER_CLASS_ID: 
            global $USER_SOURCE;
            $in_source = $USER_SOURCE;
            unset($where['classid']);
            break;
        }
      }

      $new_obj = &$this->database->getObj($where, $in_source, $indexes, $setWorkspace);
      if ( $new_obj == NULL && $setWorkspace &&
           isset($where['workspaceid']) && $where['workspaceid'] != 0 )
      {
        $where['workspaceid'] = 0;
        $new_obj = &$this->database->getObj($where, $in_source, $indexes, $setWorkspace);
      }
    }

    $this->track();
    return $new_obj;
  }


  /**
   * Get a list of objects.
   *
   * @param array indexes Array of indexes to be returned
   * @param str source The source to fetch the object from
   * @param array where Array of indexes and values to find object by
   * @param mixed order The index to sort the list on, or array of indices
   * @param mixed limit The length of the list to return, or a LIMIT string
   * @param bool returnObjects Return the actual objects not just the object meta data
   * @param bool setWorkspace get specific workspace id (or any workspace ok)
   * @return array An array of object meta data or of objects.
   */   
  function &getObjList($indexes = NULL, $source = NULL, 
                       $where = NULL, $order = NULL, $limit = NULL,
                       $returnObjects = FALSE, $setWorkspace = TRUE) 
  {
    $this->track('foowd->getObjList', $indexes);
    $objects = &$this->database->getObjList($indexes, $source,
                                            $where, $order, $limit, 
                                            $returnObjects, $setWorkspace);
    $this->track();
    return $objects;
  }

}                                        /* END CLASS smdoc            */
