<?php
/*
Copyright 2003, Paul James

This file is part of the Framework for Object Orientated Web Development (Foowd).

Foowd is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Foowd is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foowd; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
class.foowd.php
Foowd program class
*/

// check PHP version
if (version_compare(phpversion(), "4.2.0", "<")) error('You need PHP version 4.2.0 or greater to run FOOWD, please upgrade.');

// define system "must have" constants if not set in config file
define('VERSION', '0.8.3');
setConst('DEBUG', FALSE); // turn off debugging by default
setConst('REGEX_TITLE', '/^[a-zA-Z0-9-_ ]{1,32}$/'); // object title regular expression
setConst('REGEX_ID', '/^[0-9-]{1,11}$/'); // object id regular expression
setConst('REGEX_DATETIME', '/^[0-9-]{1,10}$/'); // datetime field regular expression
setConst('REGEX_PASSWORD','/^[A-Za-z0-9]{1,32}$/'); // user password regular expression
setConst('REGEX_EMAIL', '/^[A-Za-z0-9._-]+@[A-Za-z0-9._-]+\.[A-Za-z]{1,4}$/'); // email address regular expression
setConst('REGEX_GROUP', '/^[a-zA-Z0-9-]{0,32}$/'); // user group regular expression
setConst('DATETIME_FORMAT', 'D jS F Y \a\t h:ia'); // formatting string to format dates
setConst('DEFAULT_OBJECTID', 936075699); // id of default object to load if no objectid given to foowd::fetchobject()

class foowd {

  var $conn;                      // database connection object
  var $user;                      // loaded user object
  var $dbhost, 
      $dbuser, 
      $dbpass, 
      $dbname, 
      $dbtable;                   // database connection details
  var $debug, 
      $debugTrackString = '', 
      $debugTrackDepth = 0, 
      $debugDBAccessNumber = 0, 
      $debugStartTime;            // debugging data
  var $tpl;

  /**
   * Constructor
   * 
   */
  function foowd($database = NULL, $user = NULL, $groups = NULL, $debug = NULL) 
  {
    /*
     * Debug parameter initialization     
     */
    $this->debug = getVarConstOrDefault($debug, 'DEBUG', FALSE);
    $this->debugStartTime = getTime();
    $this->track('foowd->constructor');

    /*
     * Database connection initialization
     */
    $this->dbhost  = getVarOrConst($database['host'], 'DB_HOST');
    $this->dbname  = getVarOrConst($database['name'], 'DB_NAME');
    $this->dbuser  = getVarOrConst($database['user'], 'DB_USER');
    $this->dbpass  = getVarOrConst($database['password'], 'DB_PASS');
    $this->dbtable = getVarOrConst($database['table'], 'DB_TABLE');
    $this->conn    = databaseOpen($this->dbhost, 
                                  $this->dbuser, 
                                  $this->dbpass, 
                                  $this->dbname);

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
		
		$this->track();
	}

/*** destroy Foowd ***/
	function destroy() { // destructor, must be called explicitly
		if ($this->debug) { // display debug data
			$this->debugDisplay();
		}
		databaseClose($this->conn); // close DB
		unset($this); // unset object
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
			$query = DBSelect($this, NULL, array('object'), $whereClause, NULL, array('version DESC'), 1);
// if retrieve successful, unserialize it
			//if (getAffectedRows() > 0) {
			if ($query) {
			$record = getRecord($query);
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
			$query = DBSelect($this, NULL, array('object'), $whereClause, NULL, array('version DESC'), 1);
		} else { // get specified version
			$whereClause[] = 'version = '.$version;
			$query = DBSelect($this, NULL, array('object'), $whereClause, NULL, NULL, NULL);
		}
 
// if retrieve successful, unserialize it
		//if (getAffectedRows() > 0) {
		if ($query) {
			$record = getRecord($query);
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

		$query = DBSelect($this, NULL, array('object'), $whereClause, NULL, array('version DESC'), NULL);
 
		if (getAffectedRows() > 0) {
			for ($foo = 0; $foo < returnedRows($query); $foo++) {
				$record = getRecord($query);
				$objects[] = unserialize($record['object']);
			}
			$this->track(); return $objects;
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
		
		$query = DBSelect($this, NULL, array('object'), $whereClause, $groupClause, $orderClause, $limit);

		if ($query && returnedRows($query) > 0) {
			$this->track(); return $query;
		} else {
			$this->track(); return NULL;
		}
	}

/*** returns the next object from a query result resource as a live object ***/
	function retrieveObject($query) {
		$record = getRecord($query);
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
	
/*** debugging functions ***/
	function debugDisplay() {
        echo '<div class="debug_output">';

        echo '<div class="debug_output_heading">Debug Information</div>';
		echo '<pre>';
		echo 'Total DB Executions: ';
		echo $this->debugDBAccessNumber, '<br />';
		echo 'Total Execution Time: ';
		echo round(executionTime($this->debugStartTime), 3), ' seconds<br />';
		echo '</pre>';
		echo '<div class="debug_output_heading">Execution History</div>';
		echo '<pre>';
		echo $this->debugTrackString;
		echo '</pre>';

        if ( DEBUG_EXT ) {
            echo '<div class="debug_output_heading">External Resources</div>';
            global $EXTERNAL_RESOURCES;
            show($EXTERNAL_RESOURCES);
        }

        if ( DEBUG_VAR ) {
            if ( isset($_REQUEST) && !empty($_REQUEST) ) {
                echo '<div class="debug_output_heading">Request</div>';
                show($_REQUEST);
            }

            if ( isset($_SESSION) && !empty($_SESSION) ) {
                echo '<div class="debug_output_heading">$_SESSION</div>';
                show($_SESSION);
            }
        }        
        echo '</div><br />';
	}

	function track($function = NULL) {
		if ($this->debug) {
			if ($function) {
				$this->debugTrackDepth++;
				$this->debugTrackString .= str_repeat('|', $this->debugTrackDepth - 1);
                $this->debugTrackString .= '/- '.round(executionTime($this->debugStartTime), 3);
                if (func_num_args() > 1) {
                  $args = func_get_args();
                  array_shift($args);  // shift off $function
                  $parameters = '';
                  foreach ($args as $key => $arg) {
                    if ($arg == NULL) {
                      $args[$key] = 'NULL';
                    } elseif ($arg === TRUE) {
                      $args[$key] = 'TRUE';
                    } elseif ($arg === FALSE) {
                      $args[$key] = 'FALSE';
                    }
                    $parameters .= $args[$key].', ';               
                  }
                  $parameters = substr($parameters, 0, -2);
                } else {
                  $parameters = '';
                }
                $this->debugTrackString .= ' '.$function.'('.$parameters.')<br />';
			} else {
				$this->debugTrackString .= str_repeat('|', $this->debugTrackDepth - 1);
                $this->debugTrackString .= '\- '.round(executionTime($this->debugStartTime), 3);
                $this->debugTrackString .= '<br />';
				$this->debugTrackDepth--;
			}
		}
	}
	
	function debugDBTrack($SQLString) {
		$this->debugDBAccessNumber++;
		$this->debugTrackString .= str_repeat('|', $this->debugTrackDepth);
        $this->debugTrackString .= '  '.round(executionTime($this->debugStartTime), 3);
        $this->debugTrackString .= ' '.htmlspecialchars($SQLString).'<br />';
	}

}

/*** Callback function for init class error ***/
ini_set('unserialize_callback_func', 'loadClassCallback');

function loadClassCallback($className) {
	global $FOOWD_LOADCLASSCALLBACK;
	if (defined('DEFINITION_CLASS_ID') && is_object($FOOWD_LOADCLASSCALLBACK) && method_exists($FOOWD_LOADCLASSCALLBACK, 'loadClass')) {
		if ($FOOWD_LOADCLASSCALLBACK->loadClass($className)) {
			return TRUE;
		} else {
			loadDefaultClass($className);
		}
	} else {
		loadDefaultClass($className);
	}
}

function loadDefaultClass($className) { // load an incomplete class, it is just a foowd_object clone to enable loading of objects whose class definitions can not be found.
	setClassMeta($className, _("Incomplete Class"));
	eval('class '.$className.' extends foowd_object {}');
}

/*** Standard FOOWD error handling ***/
set_error_handler('foowdErrorCatch');

setConst('DEFAULT_ERROR_TITLE', _("Page Error"));

function foowdErrorCatch($errorNumber, $errorString, $filename, $lineNumber, $context) {
	if (isset($context['foowd'])) {
		$foowd = $context['foowd'];
	} elseif (isset($context['this'])) {
		$foowd = $context['this'];
	}
	if (DEBUG) {
		switch ($errorNumber) {
		case E_USER_ERROR:
			$errorName = 'Error';
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$errorName = 'Warning';
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
			$errorName = 'Notice';
			break;
		default:
			$errorName = '#'.$errorNumber;
			break;
		}
		$errorString = '<strong>'. $errorName. ':</strong> ' . $errorString;
        $errorString .= ' in <strong>'. $filename. '</strong> on line <strong>'. $lineNumber. '</strong>';
	}

    if (headers_sent()) {
		echo '<p>', $errorString, '</p>';
	} else {
		if (isset($foowd) && is_object($foowd) && get_class($foowd) == 'foowd') {
			$errorObject = new foowd_object($foowd, DEFAULT_ERROR_TITLE);
			if (function_exists('foowd_prepend')) foowd_prepend($foowd, $errorObject, DEFAULT_ERROR_TITLE );
			echo '<p>', $errorString, '</p>';
			if (function_exists('foowd_append')) foowd_append($foowd, $errorObject);
            // destroy Foowd
            $foowd->destroy();
			exit(); // self contained error, halt
		} else {
			echo '<h1>',DEFAULT_ERROR_TITLE,'</h1>';
			echo '<p>', $errorString, '</p>';
		}
	}
	if ($errorNumber == E_USER_ERROR) { // fatal error, halt
		if (isset($foowd) && is_object($foowd) && get_class($foowd) == 'foowd' && $foowd->debug) {
			$foowd->debugDisplay();
		}
		exit();
	}
}

?>
