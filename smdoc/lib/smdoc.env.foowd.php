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
setConst('DB_CLASS', 'smdoc_db_mysql');
setConst('REGEX_PASSWORD','/^[A-Za-z0-9]{6,32}$/'); 

include_once(PATH . 'env.foowd.php');

class smdoc extends foowd {

  /**
   * Constructs a new environment object.
   *
   * @constructor foowd
   * @param optional array database An array of database connection parameters.
   * @param optional array user An array of identifiers of the user to load.
   * @param optional array groups An array of user groups to define.
   * @param optional mixed debug Track execution, can be a boolean value or a string containing the debugging class to use.
   */
  function smdoc($database = NULL,
                 $user = NULL, $groups = NULL,
                 $debug = NULL, $path = NULL, $cache = NULL, $template = NULL)
  {
    $this->path = getVarConstOrDefault($path, 'PATH', 'lib');

    $debugClass = getConstOrDefault('DEBUG_CLASS', 'foowd_debug');
    $dbClass    = getConstOrDefault('DB_CLASS', 'foowd_db');
    $groupClass  = getConstOrDefault('GROUP_CLASS', 'smdoc_group');
    $userClass  = getVarConstOrDefault($user['class'],  'USER_CLASS',  'smdoc_user');
    $cacheClass = getVarConstOrDefault($cache['class'], 'CACHE_CLASS', 'foowd_cache');

    if ( !class_exists($debugClass) )
      trigger_error('Could not find class "'.$debugClass,'"' , E_USER_ERROR);
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
    $this->track('smdoc->constructor');

    /*
     * Initialize Database connection
     */
    $this->database = call_user_func(array($dbClass,'factory'), &$this, $database);
    $this->database->open();

    /* 
     * Cache settings
     */
    if ( (isset($cache) || getConstOrDefault('CACHE_ON', FALSE)) && class_exists($cacheClass) ) {
        $this->cache = new $cacheClass(
		    getVarOrDefault($cache['dir'], NULL),
		    getVarOrDefault($cache['objects'], NULL)
        );
    } else {
        $this->cache = FALSE;
    }

    /*
     * Initialize template
     */
    $this->template = getVarConstOrDefault($template, 'TEMPLATE_PATH', 'templates/default');

    /*
     * User group initialization
     */
    $this->groups = call_user_func(array($groupClass,'factory'), &$this, $groups);  

    /*
     * Get current User
     */
    $this->user = call_user_func(array($userClass,'factory'), &$this, $user);

    $this->track();
  }

  /**
   * Class destructor.
   *
   * Destroys the environment object outputting debugging information and
   * closing the database connection.
   */
  function destroy()
  {
    // @ELH remove debug display.. 
    if ( $this->database )
      $this->database->close(); // close DB
    unset($this);               // unset object
  }

	/**
	 * Set database table.
	 *
	 * Switch the table used for data storage and retrieval in this environment
	 * object.
	 *
	 * @param mixed table Array containing table name, and create function.
	 * @return str  The name of the old table.
	 */
	function setTable($table) {
        if ( is_Array($table) && method_exists($this->database, 'setTable') )
		    return $this->database->setTable($table);
        else
            return parent::setTable($table);
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
	 * @param optional int objectid Object ID of object to fetch.
	 * @param optional int classid Class ID of object to fetch.
	 * @param optional int version Version number of object to fetch.
	 * @param optional str method Name of method that will be called upon object.
	 * @param optional int workspaceid Workspace ID of object to fetch.
	 * @return object The selected object or NULL on failure.
	 * @see foowd::getObject
	 */
	function fetchObject($objectid = NULL, $classid = NULL,
                         $version = 0, $method = NULL, $workspaceid = NULL) {
        $this->track('smdoc->fetchObject', $objectid, $classid, $version, $method, $workspaceid);

        if ( is_array($objectid) && isset($objectid['objectid']) )
            $objectid = $obj['objectid'];
        elseif (!isset($objectid)) 
            $objectid = getConst('DEFAULT_OBJECTID');       
 
        // @ELH - search for external items first
        $new_obj = smdoc_external::factory($this, $objectid);
        if ( $new_obj == NULL )
        {
            if ( $classid == USER_CLASS_ID )
              $new_obj = $this->fetchUser(array('userid' => $objectid));
            else
              $new_obj = parent::fetchObject($objectid, $classid,
                                             $version, $method, $workspaceid);
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
