<?php
/*
 * Copyright 2003, Paul James
 * This file is part of the Framework for Object Orientated Web Development (Foowd).
 *
 * Foowd is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Foowd is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foowd; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/*
 * Verify php version
 */
if (version_compare(phpversion(), "4.2.0", "<"))
  error('You need PHP version 4.2.0 or greater to run FOOWD, please upgrade.');

/*
 * Define FOOWD version
 */
define('VERSION', '0.8.3');

/*
 * FOOWD environment class
 * -------------------------------------------------------------
 * Class containing methods/member variables for initialization
 * and maintenance of FOOWD environement
 * -------------------------------------------------------------
 */
class foowd {

  var $conn;                      // database connection object
  var $user;                      // loaded user object
  var $debug = NULL;              // debug output object
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
  function foowd($database = NULL, 
                 $user = NULL, $groups = NULL, 
                 $debug_enabled = NULL)
  {
    $this->debug =& smdoc_debug::new_smdoc_debug($debug_enabled);

    $this->track('foowd->constructor');

    $this->tpl = new smdoc_display('index.tpl');
    $this->tpl->assign_by_ref('FOOWD_OBJECT', $this);

    $this->conn = new database($this, $database, 'make_FOOWD_Table');
    $this->conn->open();

    /*
     * User group initialization
     */
    GroupManager::createUserGroups($this, $groups);

// load user
        if (!isset($user['loadUser']) || $user['loadUser']) {
            if (!isset($user['username']) && !isset($user['password'])) {
                if (isset($_SERVER['REMOTE_ADDR']) && defined('AUTH_IP_'.$_SERVER['REMOTE_ADDR'])) { // use IP to retrieve user details, whether IP Auth mode or not.
                    $user['username'] = constant('AUTH_IP_'.$_SERVER['REMOTE_ADDR']);
                    $user['password'] = TRUE;
                } else {
                    $authType = getConstOrDefault('AUTH_TYPE', 'http');
                    if ($authType == 'cookie') { // use cookie to retrieve user details
                        $username = new input_cookie('username', REGEX_TITLE);
                        $user['username'] = $username->value;
                        $password = new input_cookie('password', '/^[a-z0-9]{32}$/');
                        $user['password'] = $password->value;
                    } elseif ($authType == 'http' && isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) { // use http auth to retrieve user details
                        $user['username'] = $_SERVER['PHP_AUTH_USER'];
                        $user['password'] = md5(getConstOrDefault('PASSWORD_SALT', '').strtolower($_SERVER['PHP_AUTH_PW']));
                    }
                }
            }
            if (is_array($user) && isset($user['username']) && isset($user['password'])) { // get user from db
                $this->fetchUser(crc32(strtolower($user['username'])), $user['password']);
            }
        }
        if (!$this->user) { // get anonymous user
            $this->fetchUser(getConstOrDefault('ANONYMOUS_USER_ID', FALSE));
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
    if ($this->debug) {       // display debug data
      $this->debug->debugDisplay($this);
    }
    $this->tpl->display();
    $this->conn->close();     // close DB
    unset($this);             // unset object
  }

/*** fetch user ***/
    function fetchUser($userid, $password = NULL) { // fetches a user into $this->user, should only be used by Foowd constructor, fetch users as objects using fetchObject() if required
        $this->track('foowd->fetchUser');
        if ($userid && defined('USER_CLASS_ID')) { // load user from DB
// set object vars
            $whereClause[] = 'AND';
            $whereClause[] = 'objectid = '.$userid;
            $whereClause[] = 'classid = '.USER_CLASS_ID;
// do DB request
            $query = $this->conn->DBSelect(NULL, array('object'), $whereClause, NULL, array('version DESC'), 1);
// if retrieve successful, unserialize it
            //if ($this->conn->getAffectedRows() > 0) {
            if ($query) {
            $record = $this->conn->getRecord($query);
            $serializedObj = $record['object'];
            $user = unserialize($serializedObj);
                if (isset($password) && $user->passwordCheck($password)) {
                    $this->user = $user;
                    if ($user->updated < time() - getConstOrDefault('SESSION_LENGTH', 900)) { // session start
                        if (function_exists('foowd_session_start')) { // call session start
                            foowd_session_start($this);
                        }
                        if (method_exists($this->user, 'session_start')) { // call user session start
                            $this->user->session_start();
                        }
                        $this->user->updated = time();
                        $this->user->updatorid = $user->objectid;
                        $this->user->updatorName = $user->title;
                        $this->user->save($this, FALSE);
                    }
                    $this->track(); return TRUE;
                }
            }
        } else { // create anonymous user object
            $anonUserClass = getConstOrDefault('ANONYMOUS_USER_CLASS', 'foowd_anonuser');
            if (class_exists($anonUserClass)) {
                $this->user = new $anonUserClass($this);
            } else {
                trigger_error('Could not find anonymous user class.', E_USER_ERROR);
            }
        }
        $this->track(); return FALSE;
    }

/*** fetch one version of an object ***/
    function fetchObject($obj) {
// set object vars
        $objectid = getVarOrConst($obj['objectid'], 'DEFAULT_OBJECTID');
        $classid = getVarOrConst($obj['classid'], 'DEFAULT_CLASSID');
        $version = getVarOrDefault($obj['version'], 0);

        $this->track('foowd->fetchObject',$objectid, $version, $classid);

        global $EXTERNAL_RESOURCES;
// check for external resource
         if ( isset($EXTERNAL_RESOURCES) &&
              is_array($EXTERNAL_RESOURCES) &&
              array_key_exists(intval($objectid), $EXTERNAL_RESOURCES) ) {
             $external_obj = new foowd_external($this, intval($objectid));
             $this->track();
             return $external_obj;
         }
        if (isset($obj['workspaceid'])) {
            $workspaceid = $obj['workspaceid'];
        } elseif (isset($this->user->workspaceid)) {
            $workspaceid = $this->user->workspaceid;
        } else {
            $workspaceid = 0;
        }

        $whereClause[] = 'AND';
        $whereClause[] = 'objectid = '.$objectid;
        $whereClause[] = 'workspaceid = '.$workspaceid;
        if ($classid) {
            $whereClause[] = 'classid = '.$classid;
        }

        if ($version == 0) { // get latest version
            $query = $this->conn->DBSelect(NULL, array('object'), $whereClause, NULL, array('version DESC'), 1);
        } else { // get specified version
            $whereClause[] = 'version = '.$version;
            $query = $this->conn->DBSelect(NULL, array('object'), $whereClause, NULL, NULL, NULL);
        }

// if retrieve successful, unserialize it
        //if ($this->conn->getAffectedRows() > 0) {
        if ($query) {
            $record = $this->conn->getRecord($query);
            if (isset($record['object'])) {
                $serializedObj = $record['object'];
                $this->track(); return unserialize($serializedObj);
            } else {
                $this->track(); return NULL;
            }
        } elseif ($workspaceid != 0) { // if not already looking in main workspace
            $obj['workspaceid'] = 0;
            $this->track();
            return $this->fetchObject($obj); /*** WARNING: recursion in action ***/
        } else {
            $this->track();
            return NULL;
        }
    }

/*** returns an array of all object versions given an objectid, classid, and workspaceid ***/
    function getObject($obj) {
        $this->track('foowd->getObject', $obj);

// set object vars
        $objectid = getVarOrConst($obj['objectid'], 'DEFAULT_OBJECTID');
        $classid = getVarOrConst($obj['classid'], 'DEFAULT_CLASSID');
        if (isset($obj['workspaceid'])) {
            $workspaceid = $obj['workspaceid'];
        } elseif (isset($this->user->workspaceid)) {
            $workspaceid = $this->user->workspaceid;
        } else {
            $workspaceid = 0;
        }

        $whereClause[] = 'AND';
        $whereClause[] = 'objectid = '.$objectid;
        $whereClause[] = 'workspaceid = '.$workspaceid;
        if ($classid) {
            $whereClause[] = 'classid = '.$classid;
        }

        $query = $this->conn->DBSelect(NULL, array('object'), $whereClause, NULL, array('version DESC'), NULL);

        if ($this->conn->getAffectedRows() > 0) {
            for ($foo = 0; $foo < $this->conn->returnedRows($query); $foo++) {
                $record = $this->conn->getRecord($query);
                $objects[] = unserialize($record['object']);
            }
            $this->track(); 
            return $objects;
        } elseif ($workspaceid != 0) { // if not already looking in main workspace
            $obj['workspaceid'] = 0;
            $this->track();
            return $this->getObject($obj); /*** WARNING: recursion in action ***/
        } else {
            $this->track();
            return NULL;
        }
    }

/*** returns an array of objects matching where array ***/
    function getObjects($whereClause, $groupClause = NULL, $orderClause = NULL, $limit = NULL, $workspaceid = NULL) {
        $query = $this->retrieveObjects($whereClause, $groupClause, $orderClause, $limit, $workspaceid);
        if ($query) {
            while ($object = $this->retrieveObject($query)) {
                $objects[] = $object;
            }
            return $objects;
        }
    }

/*** returns a query result resource that can be stepped throuh using foowd::retrieveObject() ***/
    function retrieveObjects($whereClause, $groupClause = NULL, $orderClause = NULL, $limit = NULL, $workspaceid = NULL) {
        $this->track('foowd->retrieveObjects');

        if ($workspaceid == NULL) {
            if (isset($this->user->workspaceid)) {
                $workspaceid = $this->user->workspaceid;
            } else {
                $workspaceid = 0;
            }
        }

        if (!function_exists('findWorkspace')) {
            function findWorkspace($whereClause) {
                foreach ($whereClause as $where) { // look for workspace clause in where array
                    if (is_array($where)) {
                        return findWorkspace($where);
                    } elseif (substr($where, 0, 11) == 'workspaceid') {
                        return TRUE;
                    }
                }
                return FALSE;
            }
        }

        $found = FALSE;
        if ($whereClause) {
            $found = findWorkspace($whereClause);
        }
        if (!$found) {
            if ($workspaceid != 0) {
                $workspaceClause = array(
                    'OR',
                    'workspaceid = '.$workspaceid,
                    'workspaceid = 0'
                );
                if ($groupClause == NULL) $groupClause = array();
                if (!in_array('objectid', $groupClause)) $groupClause[] = 'objectid';
                if (!in_array('classid', $groupClause)) $groupClause[] = 'classid';
                if (!in_array('version', $groupClause)) $groupClause[] = 'version';
            } else {
                $workspaceClause = 'workspaceid = '.$workspaceid;
            }
            if ($whereClause) {
                $whereClause = array(
                    'AND',
                    $whereClause,
                    $workspaceClause
                );
            } else {
                $whereClause = array('AND', $workspaceClause);
            }
        }

        $query = $this->conn->DBSelect(NULL, array('object'), $whereClause, $groupClause, $orderClause, $limit);

        if ($query && $this->conn->returnedRows($query) > 0) {
            $this->track(); return $query;
        } else {
            $this->track(); return NULL;
        }
    }

/*** returns the next object from a query result resource as a live object ***/
    function retrieveObject($query) {
        $record = $this->conn->getRecord($query);
        if ($record) {
            $serializedObj = $record['object'];
            return unserialize($serializedObj);
        }
        return FALSE;
    }

/*** call method ***/
    function callMethod(&$object, $methodName = NULL, $cacheName = FALSE) {
        $this->track('foowd->callMethod', $object, $methodName);
        if (is_object($object)) {
            $methodName = getVarConstOrDefault($methodName, 'DEFAULT_METHOD', 'view');
            $method = 'method_'.$methodName;
            if (method_exists($object, $method)) { // check method exiss
                if (is_array($object->permissions) && isset($object->permissions[$methodName])) {
                    $methodPermission = $object->permissions[$methodName];
                } else {
                    $methodPermission = getPermission(get_class($object), $methodName, 'object');
                }
                if ($this->user->inGroup($methodPermission, $object->creatorid)) { // check user permission
                    if ($cacheName === FALSE) {
                        $object->{$method}($this); // call method
                    } else {
                        ob_start();
                        $object->{$method}($this); // call method
                        writeCache($cacheName, ob_get_contents());
                        ob_end_flush();
                    }
                    $this->track();
                    return FALSE;
                } else {
                    $this->track();
                    return 'Permission denied to access method "'.$methodName.'" for object "<a href="'.getURI(array('objectid' => $object->objectid, 'classid' => $object->classid)).'">'.$object->getTitle().'</a>".';
                }
            } else {
                $this->track();
                return 'Unknown method "'.$methodName.'" for object "<a href="'.getURI(array('objectid' => $object->objectid, 'classid' => $object->classid)).'">'.$object->getTitle().'</a>".';
            }
        } else {
            $this->track();
            return 'Object not found.';
        }
    }

/*** call class method ***/
    function callClassMethod($className, $methodName = NULL) {
        $this->track('foowd->callClassMethod',$className, $methodName);
        if (!isset($methodName)) {
            $methodName = getConstOrDefault('DEFAULT_CLASS_METHOD', 'create');
        }
        if (class_exists($className) || $this->loadClass($className)) { // check class exists (if it doesn't, try to load it from DB)
            if (in_array('class_'.$methodName, get_class_methods($className))) { // check method exists
                $methodPermission = getPermission($className, $methodName, 'class');
                if ($this->user->inGroup($methodPermission)) { // check user permission
                    call_user_func(array($className, 'class_'.$methodName), $this, $className); // call method
                    $this->track();
                    return FALSE;
                } else {
                    $this->track();
                    return 'Permission denied to call class method "'.$methodName.'" of class "'.$className.'".';
                }
            } else {
                $this->track();
                return 'Unknown class method "'.$methodName.'" for class "'.$className.'".';
            }
        } else {
            $this->track();
            return 'Unknown class "'.$className.'".';
        }
    }

/*** get user groups ***/

  /**
   * getUserGroups returns an array containing a list of user groups
   * as 'internal name/objectid' => 'external name'.
   *
   * This method caches the list of groups in the session, only
   * creating the list if it hasn't already been created during this session.
   *
   * @param boolean $includeAll - whether or not to include all groups, or
   *                              only groups current user is a member of.
   */
  function getUserGroups($includeAll=FALSE)
  {
    if ( $includeAll )
    {
      $allgroups = GroupManager::getUserGroups($this, FALSE);
      return $allgroups;
    }

    $usergroups = GroupManager::getUserGroups($this, TRUE);

    $items = array();
    foreach ($usergroups as $group => $name)
    {
      if ( $this->user->inGroup($group) )
        $items[$group] = $name;
    }
    return $items;
  }

/*** load dynamic class ***/
    function loadClass($className) {
        $this->track('foowd->loadClass', $className);
        if (defined('DEFINITION_CLASS_ID')) {
            $class = $this->fetchObject(array(
                'objectid' => crc32(strtolower($className)),
                'classid' => DEFINITION_CLASS_ID
            ));
            if (is_object($class)) {
    // if it inherits from another class, find out and load it now
                if (preg_match_all('|class ([-_a-zA-Z0-9]*) extends ([-_a-zA-Z0-9]*) ?{|', $class->body, $pregMatches)) { // i'd rather do this with catching errors on the eval, but that can't be done
                    if ($pregMatches[1] != $pregMatches[2]) {
                        foreach($pregMatches[2] as $className) {
                            if ($className != $class->title && !class_exists($className)) {
                                $this->loadClass($className); /*** WARNING: recursion in action ***/
                            }
                        }
                    }
                }
    // define class
                setClassMeta($class->title, $class->description);
                if (eval($class->body) === FALSE) {
                    $this->track(); return FALSE;
                } else {
                    $this->track(); return TRUE;
                }
            }
        } else {
            //trigger_error('Constant "DEFINITION_CLASS_ID" not defined.', E_USER_ERROR);
            loadDefaultClass($className);
        }
    }


  /*
   * track
   * -------------------------------------------------------------
   * Trace method execution.
   *   Pass function name to begin nested block,
   *   Pass NULL parameter list to complete block.
   *   Supply optional additional arguments with function name
   *   to print parameter list, see smdoc.class.debug.php
   * -------------------------------------------------------------
   */
  function track()
  {
    if ($this->debug)
    {
      $data = func_get_args();
      $function = array_shift ($data);
      $this->debug->track($function, $data);
    }
  }
}                                        /* END CLASS foowd                  */

/**
 * make table 
 * called on query failure due to missing table
 */
function make_FOOWD_Table(&$db_conn, $table) {
    $createString = 'CREATE TABLE `'.$table.'` (
    `objectid` int(11) NOT NULL default \'0\',
    `version` int(10) unsigned NOT NULL default \'1\',
    `classid` int(11) NOT NULL default \'0\',
    `workspaceid` int(11) NOT NULL default \'0\',
    `object` longblob,
    `title` varchar(255) NOT NULL default \'\',
    `updated` datetime NOT NULL default \'0000-00-00 00:00:00\',
    PRIMARY KEY (`objectid`,`version`,`classid`,`workspaceid`),
    KEY `idxtblObjectTitle`(`title`),
    KEY `idxtblObjectupdated`(`updated`),
    KEY `idxtblObjectObjectid`(`objectid`),
    KEY `idxtblObjectClassid`(`classid`),
    KEY `idxtblObjectVersion`(`version`),
    KEY `idxtblObjectWorkspaceid`(`workspaceid`)
    )';
    
    if ($db_conn->execQuery($createString, false, false)) 
    {
        if ($db_conn->debug) 
          $db_conn->debug->DBTrack($createString);
          
        $welcomeInsert = 'INSERT INTO `tblobject` VALUES("936075699","1","1158898744","0","O:15:\"foowd_text_html\":15:{s:5:\"title\";s:7:\"Welcome\";s:8:\"objectid\";s:9:\"936075699\";s:7:\"version\";s:1:\"1\";s:7:\"classid\";s:10:\"1158898744\";s:11:\"workspaceid\";s:1:\"0\";s:7:\"created\";s:10:\"1050232200\";s:9:\"creatorid\";s:11:\"-1316331025\";s:11:\"creatorName\";s:4:\"Peej\";s:7:\"updated\";i:1053681659;s:9:\"updatorid\";s:11:\"-1316331025\";s:11:\"updatorName\";s:4:\"Peej\";s:11:\"permissions\";a:5:{s:5:\"admin\";s:4:\"Gods\";s:6:\"delete\";s:4:\"Gods\";s:5:\"clone\";s:4:\"Gods\";s:4:\"edit\";s:4:\"Gods\";s:11:\"permissions\";s:4:\"Gods\";}s:4:\"body\";s:181:\"<h1>Congratulations!</h1>\r\n\r\n<h2>If you can see this page then FOOWD is working, well done.</h2>\r\n\r\n<p>Please follow the instructions in the README file to help get you started.</p>\";s:8:\"evalCode\";s:1:\"0\";s:14:\"processInclude\";s:1:\"0\";}","Welcome","2003-05-23 10:20:59");';
        
        if ($foowd->debug)
          $db_conn->debug->DBTrack($welcomeInsert);
        
        if ($db_conn->execQuery($welcomeInsert, false, false)) 
          return TRUE;
    }
    return FALSE;
}

