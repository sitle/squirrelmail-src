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

setConst('DEBUG_CLASS', 'smdoc_debug');
setConst('FOOWD_CLASS_NAME', 'smdoc');

include_once(PATH . 'env.foowd.php');

class smdoc extends foowd {

  var $tpl;                       // output template object

  /*
   * Constructor
   * -------------------------------------------------------------
   * Initializes new instance of FOOWD environment
   *  $database      - Optional Array containing database settings
   *  $user          - Optional containing user details
   *  $groups        - Optional of additional user groups
   *  $debug_enabled - Optional Boolean indicating whether or not to enable debug
   * -------------------------------------------------------------
   */
  function smdoc($database = NULL,
                       $user = NULL, $groups = NULL,
                       $debug = NULL)
  {
    $this->path = getVarConstOrDefault($path, 'PATH', 'lib');

    $debugClass = getConstOrDefault('DEBUG_CLASS', 'foowd_debug');
    $tplClass   = getConstOrDefault('TEMPLATE_CLASS', 'smdoc_display');
    $dbClass    = getConstOrDefault('DB_CLASS', 'foowd_db');
    $userClass  = getConstOrDefault('USER_CLASS', 'smdoc_user');
    $groupClass  = getConstOrDefault('GROUP_CLASS', 'smdoc_group');

    if ( !class_exists($debugClass) )
      trigger_error('Could not find class "'.$debugClass,'"' , E_USER_ERROR);
    if ( !class_exists($tplClass) )
      trigger_error('Could not find class "'.$tplClass,'"' , E_USER_ERROR);
    if ( !class_exists($dbClass) )
      trigger_error('Could not find class "'.$dbClass,'"' , E_USER_ERROR);
    if ( !class_exists($userClass) )
      trigger_error('Could not find class "'.$userClass,'"' , E_USER_ERROR);
    if ( !class_exists($groupClass) )
      trigger_error('Could not find class "'.$groupClass,'"' , E_USER_ERROR);

    /*
     * Initialize Debug object
     */
    $this->debug = call_user_func(array($debugClass,'factory'), $debug);
    $this->track('foowd->constructor');

    /*
     * Initialize Database connection
     */
    $this->database = call_user_func(array($dbClass,'factory'), &$this, $database);
    $this->database->open();

    /*
     * Initialize template
     */
    $this->tpl = call_user_func(array($tplClass,'factory'), 'index.tpl');
    $this->tpl->assign_by_ref('FOOWD_OBJECT', $this);

    /* 
     * Cache settings
     */
    if ((isset($cache) || getConstOrDefault('CACHE_ON', FALSE)) && class_exists('foowd_cache')) {
      $this->cache = foowd_cache::factory($cache);
    }

    /*
     * User group initialization
     */
    $this->groups = call_user_func(array($groupClass,'factory'), &$this, $groups);  

    /*
     * Get current User
     */
    $this->user = call_user_func(array($userClass,'factory'), &$this, $user);
    $sessionTimeout = time() - getConstOrDefault('SESSION_LENGTH', 900);
    if ( $this->user->updated < $sessionTimeout )
    {
      if ( function_exists('foowd_session_start') )        // call session start
          foowd_session_start($this);
      if ( method_exists($this->user, 'session_start') )   // call user session start
          $this->user->session_start();
      $this->user->updated = time();
      $this->user->updatorid = $user->objectid;
      $this->user->updatorName = $user->title;
      $this->user->save($this, FALSE);
    }
    $this->tpl->assign_by_ref('CURRENT_USER', $this->user);

    $this->track();
  }

    /*
     * Destructor
     * -------------------------------------------------------------
     * Cleans up/Finalizes foowd object
     *   -- adds debug information to template
     *   -- prints template
     *   -- closes DB connection
     *   -- unsets $foowd
     * -------------------------------------------------------------
     */
    function destroy()
    {
        if ($this->debug) {         // display debug data
            $this->debug->display($this);
        }
        $this->tpl->display();      // display template
        if ( $this->database )
            $this->database->close(); // close DB
        unset($this);               // unset object
    }

	/**
	 * Get the current user from the database.
	 *
	 * Given the array of uesr details, fetch the corrisponding user object from
	 * the database, unserialise and return it.
	 *
	 * @class foowd
	 * @method fetchUser
	 * @param array userArray The user array passed into <code>{@link foowd::foowd}</code>.
	 * @return mixed The selected user object or FALSE on failure.
	 */
	function fetchUser($userid, $password = NULL) { // fetches a user into $this->user, should only be used by Foowd constructor, fetch users as objects using fetchObject() if required
        trigger_error('foowd::fetchUser method deprecated in smdoc' , E_USER_ERROR);
	}

	/**
	 * Get user details from an external mechanism.
	 *
	 * If not already set, populate the user array with the user classid and
	 * fetch the username and password of the current user from one of the input
	 * mechanisms
	 *
	 * @class foowd
	 * @method getUserDetails
	 * @param array user The user array passed into <code>foowd::foowd</code>.
	 * @return array The resulting user array.
	 */
    function getUserDetails(&$user) {
        trigger_error('foowd::getUserDetails method deprecated in smdoc', E_USER_ERROR);
    }

    /**
     * getUserGroups returns an array containing a list of user groups
     * as 'internal name/objectid' => 'external name'.
     *
     * This method caches the list of groups in the session, only
     * creating the list if it hasn't already been created during this session.
     *
     * @class smdoc
     * @method getUserGroups
     * @param optional boolean $includeSpecialGroups - whether or not to include all groups.
     * @param optional boolean $memberOnly - whether or not to restrict to only groups user is a member of
     * @return array An array of user groups.
     */
    function getUserGroups($includeAll = FALSE, $memberOnly = FALSE)
    {
        if ( $includeSpecialGroups )
        {
            $allgroups = $this->groups->getUserGroups($this, FALSE);
            return $allgroups;
        }

        $usergroups = $this->groups->getUserGroups($this, TRUE);

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
	 * Fetch one version of an object.
	 *
	 * Given an array containing an objectid and optionally a version number,
	 * classid and workspaceid, fetch the object from the database and return it.
	 *
	 * @class foowd
	 * @method fetchObject
	 * @param array obj An array of object access values.
	 * @return object The selected object or NULL on failure.
	 * @see foowd::getObject
	 */
	function fetchObject($obj = NULL) {
		$this->track('smdoc->fetchObject', $obj);

		if (isset($obj['objectid']) && is_numeric($obj['objectid'])) {
			$objectid = $obj['objectid'];
		} elseif (isset($obj['object'])) {
			$objectid = crc32(strtolower($obj['object']));
		} else {
			$objectid = getConst('DEFAULT_OBJECTID');
		}

        // @ELH - search for external items first
        $new_obj = smdoc_external::factory($this, $objectid);
        if ( $new_obj == NULL )
        {
            $new_obj = parent::fetchObject($obj);
        }

        $this->track();
        return $new_obj;
    }

	/**
	 * Get objects that match a SQL clause.
	 *
	 * Return an array of objects that match the given SQL clause. The SQL clause
	 * is given as a number of arrays which define the clause in sections.
	 *
	 * @class foowd
	 * @method getObjects
	 * @param array whereClause An array of where clause elements.
	 * @param optional array groupClause An array of group clause elements.
	 * @param optional array orderClause An array of order clause elements.
	 * @param optional int limit Limit the number of results returned.
	 * @param optional int workspaceid Workspace to retrieve objects from.
	 * @return array The array of selected objects.
	 * @see foowd::retrieveObjects
	 */
	function getObjects($whereClause, $groupClause = NULL, $orderClause = NULL, $limit = NULL, $workspaceid = NULL) {
        $objects = array(); //@ELH
		$query = $this->retrieveObjects($whereClause, $groupClause, $orderClause, $limit, $workspaceid);
		if ($query) {
			while ($object = $this->retrieveObject($query)) {
				$objects[] = $object;
			}
		}
        return $objects; // @ELH
	}

	/**
	 * Get next object from a database query resource.
	 *
	 * Return the next object from a database query resource generated by
	 * <code>retrieveObjects</code>.
	 *
	 * @class foowd
	 * @method retrieveObject
	 * @param object query A database query object generated by <code>foowd::retrieveObjects</code>.
	 * @return mixed The retrieved object or FALSE on failure.
	 * @see foowd::retrieveObjects
	 */
    function retrieveObject(&$query) {
        $this->track('smdoc->retrieveObject', $query);
        $obj = FALSE; // @ELH
        if ( $query != NULL )
            $obj = parent::retrieveObject($query);
        $this->track();
        return $obj; // @ELH
    }
}                                        /* END CLASS smdoc            */
