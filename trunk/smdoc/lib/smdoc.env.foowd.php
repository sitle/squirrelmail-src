<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Modified Foowd Environment object.
 * 
 * $Id$
 * 
 * @package smdoc
 * @author Erin Schnabel
 * @author Paul James
 */

/** Define constant for FOOWD class name before including parent */
define('FOOWD_CLASS_NAME', 'smdoc');

/** Include base foowd object implementation */
include_once(SM_DIR . 'env.foowd.php');

/**
 * The smdoc Foowd environment class.
 *
 * Sets up the Foowd environment, including database connection, user group
 * management and user initialization, and provides methods for accessing
 * objects within the system.
 *
 * @package smdoc
 * @author Erin Schnabel
 * @author Paul James
 */
class smdoc extends foowd
{
  /**
   * Array of foowd configuration settings
   *
   * @var array
   * @access public
   */
  var $config_settings;

  /**
   * Constructs a new environment object.
   *
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
    require_once(SM_DIR . 'smdoc.env.group.php');
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
   */
  function __destruct()
  {
    if ( $this->database )
      $this->database->__destruct(); // close DB
    if ( $this->debug )
      $this->debug->display();  // dump debug output
    unset($this);               // unset object
  }

  /**
   * Clean up foowd object, then set Location header for redirect.
   * @param string $new_loc Forwarding URL
   */
  function loc_forward($new_loc)
  {
    unset($this->debug);
    $this->debug = FALSE;
    $this->__destruct();
    session_write_close();
    header('Location: ' . $new_loc);
    exit;
  }

  /**
   * Wrapper function to fetch an instance of the specified user.
   *
   * @param array $userArray Array containing the username or objectid of the user
   *                         to be fetched.
   * @return mixed|FALSE The selected user object or FALSE on failure.
   * @see base_user::fetchUser()
   */
  function fetchUser($userArray = NULL) {
    return smdoc_user::fetchUser($this, $userArray);
  }

  /**
   * getUserGroups returns an array containing a list of user groups
   * as 'internal name/objectid' => 'external name'.
   *
   * @param bool $includeSpecialGroups If TRUE, include all groups.
   * @param bool $memberOnly If TRUE, include only those groups the current member belongs to.
   * @return array An array of user groups.
   * @see foowd::$groups
   * @see smdoc_group::getUserGroups()
   * @see base_user::inGroup()
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
   * @param string $className Name of the class the method belongs to.
   * @param string $methodName Name of the method.
   * @param string $type  Type of method, 'CLASS' or 'OBJECT'.
   * @param mixed $objPerms array of custom permissions from object being checked (may be NULL)
   * @param int   $creatorid id of object creator (may be NULL)
   * @return bool TRUE if user has access to method
   * @see base_user::inGroup()
   */
  function hasPermission($className, $methodName, $type, $objPerms = NULL, $creatorid = NULL)
  {
    if ( isset($objectPerms) && is_array($objectPerms) && isset($objPerms[$methodName]) ) 
      $methodPermission = $objectPerms[$methodName];

    if ( !isset($methodPermission) )
      $methodPermission = getPermission($className, $methodName, $type);

    return $this->user->inGroup($methodPermission, $creatorid);
  }

  /**
   * Fetch one version of an object.
   * $where array should contain at least the objectid of 
   * the object to be loaded.
   * If the classid is also included, it will be used to retrieve
   * the object from the appropriate source, etc.
   *
   * @param  array $where Array of values to find object by
   * @param  mixed $in_source Source to get object from
   * @param  bool  $setWorkspace If TRUE, restrict to a certain workspace, if FALSE, workspace is not used.
   * @param  bool  $useVersion   If TRUE, get most recent version, if FALSE, version is not used.
   * @return mixed The retrieved object.
   * @see    base_user::fetchUser()
   * @see    smdoc_db::getObj()
   */
  function &getObj($where = NULL, $in_source = NULL, $setWorkspace = TRUE, $useVersion = TRUE)
  {
    $this->track('smdoc->getObj', $where, $in_source, $setWorkspace, $useVersion);

    $new_obj = NULL;
    if ( $in_source == NULL && isset($where['classid']) )
    {
      switch($where['classid'])
      {
        case USER_CLASS_ID:
          $new_obj =& smdoc_user::fetchUser($this, $where);
          break;
        case NEWS_CLASS_ID:
          $new_obj =& smdoc_news::fetchNews($this, $where);
          break;
        case WORKSPACE_CLASS_ID:
        case TRANSLATION_CLASS_ID:
          $setWorkspace = FALSE;
          break;
      }
    }

    if ( $new_obj == NULL )
    {
      $new_obj =& $this->database->getObj($where, $in_source, $setWorkspace, $useVersion);
      if ( $new_obj == NULL && $setWorkspace &&
           isset($where['workspaceid']) && $where['workspaceid'] != 0 )
      {
        $where['workspaceid'] = 0;
        $new_obj =& $this->database->getObj($where, $in_source, $setWorkspace, $useVersion);
      }
    }

    $this->track();
    return $new_obj;
  }


  /**
   * Get a list of objects.
   *
   * @param array  $indexes       Array of indexes to be returned
   * @param string $source        The source to fetch the object from
   * @param array  $where         Array of indexes and values to find object by
   * @param mixed  $order         The index to sort the list on, or array of indices
   * @param mixed  $limit         The length of the list to return, or a LIMIT string
   * @param bool   $returnObjects If TRUE, return the actual objects not just the object meta data; otherwise, return just the meta data as an array.
   * @param bool   $setWorkspace  If TRUE, restrict to a certain workspace, if FALSE, workspace does not apply or does not matter.
   * @return array An array of objects or object meta data.
   * @see smdoc_db::getObjList()
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
