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
if (version_compare(phpversion(), "4.2.0", "<")) 
    error('You need PHP version 4.2.0 or greater to run FOOWD, please upgrade.');

// define system must have constants if not set in config file
if (!defined('REGEX_TITLE')) define('REGEX_TITLE', '/^[a-zA-Z0-9-_ ]{1,32}$/'); // object title
if (!defined('REGEX_ID')) define('REGEX_ID', '/^[0-9-]{1,10}$/'); // object id
if (!defined('REGEX_DATETIME')) define('REGEX_DATETIME', '/^[0-9-]{1,10}$/'); // datetime field
if (!defined('REGEX_PASSWORD')) define('REGEX_PASSWORD', '/^[A-Za-z0-9]{1,32}$/'); // user password
if (!defined('REGEX_EMAIL')) define('REGEX_EMAIL', '/^[A-Za-z0-9._-]+@[A-Za-z0-9._-]+\.[A-Za-z]{1,4}$/'); // email address
if (!defined('DATETIME_FORMAT')) define('DATETIME_FORMAT', 'D jS F Y \a\t h:ia'); // formatting string to format dates

class foowd {

	var $conn; // database connection object
	var $user; // loaded user object
	var $dbhost, $dbuser, $dbpass, $dbname, $dbtable; // database connection details

/*** init Foowd ***/
	function foowd($database, $user = NULL) {

        track('foowd::foowd');

// set database vars
		$this->dbhost = setVarConstOrDefault($database['host'], 'DB_HOST', '127.0.0.1');
		$this->dbname = setVarConstOrDefault($database['name'], 'DB_NAME', 'foowd');
		$this->dbuser = setVarConstOrDefault($database['user'], 'DB_USER', 'root');
		$this->dbpass = setVarConstOrDefault($database['password'], 'DB_PASS', '');
		$this->dbtable = setVarConstOrDefault($database['table'], 'DB_TABLE', 'tblObject');

// open database connection
		$this->conn = databaseOpen($this->dbhost, 
                                   $this->dbuser, $this->dbpass, 
                                   $this->dbname);

// load user
		if (!isset($user['loadUser']) || $user['loadUser']) {
			$authType = setConstOrDefault('AUTH_TYPE', 'http');
			if ($authType == 'cookie') { // use cookie to retrieve user details
				$username = new input_cookie('username', REGEX_TITLE);
				$user['username'] = $username->value;
				$password = new input_cookie('password', '/^[a-z0-9]{32}$/');
				$user['password'] = $password->value;
			} elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) { 
                // use http auth to retrieve user details
				$user['username'] = $_SERVER['PHP_AUTH_USER'];
				$salt = setConstOrDefault('PASSWORD_SALT', '');
				$user['password'] = md5($salt.strtolower($_SERVER['PHP_AUTH_PW']));
			}

			if (is_array($user) && isset($user['username']) && isset($user['password'])) { 
                // get user from db
				$userObject = $this->fetchUser(crc32(strtolower($user['username'])));
				if ($userObject->password == $user['password']) {
					$this->user = $userObject;
				}
			}
		}
		if (!$this->user) { // get anonymous user
			$anonUserid = setConstOrDefault('ANONYMOUS_USER_ID', FALSE);
			if ($anonUserid) {
				$this->user = $this->fetchUser($anonUserid);
			} else {
				$this->user = new foowd_anonuser();
			}
		}
        track();
	}

/*** destroy Foowd ***/
	function destroy() { // destructor, must be called explicitly
		databaseClose($this->conn); // close DB
		unset($this); // unset object
	}

/*** fetch user ***/
	function fetchUser($userid) {
        track('foowd::fetchUser', $userid);
     
        // should only be used by Foowd constructor, 
        // fetch users as objects using fetchObject() if required

// set object vars
		$whereClause[] = 'objectid = '.$userid;
		$whereClause[] = 'AND';
		$whereClause[] = 'classid = ' . USER_CLASS_ID;
// do DB request
		$query = DBSelect($this->conn, $this->dbtable, NULL, array('object'), 
                 $whereClause, NULL, array('version DESC'), 1);
// if retrieve successful, unserialize it
		if (getAffectedRows() > 0) {
			$record = getRecord($query);
			$serializedObj = $record['object'];
			$user = unserialize($serializedObj);
			if ($user->updated < time() - setConstOrDefault('SESSION_LENGTH', 900)) { 
                // session start
				$user->updated = time();

				if (function_exists('foowd_session_start')) { 
                    // call session start
					foowd_session_start($this);
				}
				$user->save($this, FALSE);
			}
            track();
			return $user;
		} else {
            track();
			return NULL;
		}
	}

/*** fetch one version of an object ***/
	function fetchObject($obj) {
        global $EXTERNAL_RESOURCES;

// set object vars
		$objectid = intval(setVarConstOrDefault($obj['objectid'], 
                              'DEFAULT_OBJECTID', 0));
		$version = intval(setVarConstOrDefault($obj['version'], NULL, 0));
		$classid = intval(setVarConstOrDefault($obj['classid'], 
                             'DEFAULT_CLASSID', OBJECT_CLASS_ID));

        track('foowd::fetchObject',$objectid, $version, $classid);

// check for external resource
        if ( isset($EXTERNAL_RESOURCES) &&
             array_key_exists(intval($objectid), $EXTERNAL_RESOURCES)) {
            $external_obj = new foowd_external($this, $objectid);
            track(); 
            return $external_obj;
        }

		if (isset($obj['workspaceid'])) {
			$workspaceid = $obj['workspaceid'];
		} elseif (isset($this->user->workspaceid)) {
			$workspaceid = $this->user->workspaceid;
		} else {
			$workspaceid = 0;
		}

		$whereClause[] = 'objectid = '.$objectid;
		$whereClause[] = 'AND';
		$whereClause[] = 'workspaceid = '.$workspaceid;
		if ($classid) {
			$whereClause[] = 'AND';
			$whereClause[] = 'classid = '.$classid;
		}

		if ($version == 0) { // get latest version
			$query = DBSelect($this->conn, $this->dbtable, NULL, array('object'), 
                              $whereClause, NULL, array('version DESC'), 1);
		} else { // get specified version
			$whereClause[] = 'AND';
			$whereClause[] = $this->dbtable.'.version = '.$version;
			$query = DBSelect($this->conn, $this->dbtable, NULL, array('object'), 
                              $whereClause, NULL, NULL, NULL);
		}
 
// if retrieve successful, unserialize it
		if (getAffectedRows() > 0) {
			$record = getRecord($query);
			$serializedObj = $record['object'];
            track();
			return unserialize($serializedObj);
		} elseif ($workspaceid != 0) { // if not already looking in main workspace
			$obj['workspaceid'] = 0;
            track();
			return $this->fetchObject($obj); /*** WARNING: recursion in action ***/
		} else {
            track();
			return NULL;
		}
	}

/*** returns an array of all object versions given an 
 * objectid, classid, and workspaceid 
***/
	function getObject($obj) {
        track('foowd::getObject',$obj);
// set object vars
		$objectid = setVarConstOrDefault($obj['objectid'], 'DEFAULT_OBJECTID', 0);
		$classid = setVarConstOrDefault($obj['classid'], 'DEFAULT_CLASSID', OBJECT_CLASS_ID);
		if (isset($obj['workspaceid'])) {
			$workspaceid = $obj['workspaceid'];
		} elseif (isset($this->user->workspaceid)) {
			$workspaceid = $this->user->workspaceid;
		} else {
			$workspaceid = 0;
		}
		
		$whereClause[] = 'objectid = '.$objectid;
		$whereClause[] = 'AND';
		$whereClause[] = 'workspaceid = '.$workspaceid;
		if ($classid) {
			$whereClause[] = 'AND';
			$whereClause[] = 'classid = '.$classid;
		}

		$query = DBSelect($this->conn, $this->dbtable, NULL, array('object'), 
                          $whereClause, NULL, array('version DESC'), NULL);
 
		if (getAffectedRows() > 0) {
			for ($foo = 0; $foo < returnedRows($query); $foo++) {
				$record = getRecord($query);
				$objects[] = unserialize($record['object']);
			}
            track();
			return $objects;
		} elseif ($workspaceid != 0) { // if not already looking in main workspace
			$obj['workspaceid'] = 0;
            track();
            /*** WARNING: recursion in action ***/
			return $this->getObject($obj); 
		} else {
            track();
			return NULL;
		}
	}

/*** returns an array of objects matching where array ***/
	function getObjects($whereClause, $orderClause = NULL, 
                        $limit = NULL, $workspaceid = NULL) {
        track('foowd::getObjects',$workspaceid);

		if ($workspaceid == NULL) {
			if (isset($this->user->workspaceid)) {
				$workspaceid = $this->user->workspaceid;
			} else {
				$workspaceid = 0;
			}
		}

		$query = DBSelect($this->conn, $this->dbtable, NULL, array('object'), 
                          $whereClause, array('objectid'), $orderClause, $limit);
 
		if (getAffectedRows() > 0) {
			for ($foo = 0; $foo < returnedRows($query); $foo++) {
				$record = getRecord($query);
				$serializedObj = $record['object'];
				$objects[] = unserialize($serializedObj);
			}
            track();
			return $objects;
		} elseif ($workspaceid != 0) { // if not already looking in main workspace
            track();
            /*** WARNING: recursion in action ***/
			return $this->getObjects($whereClause, $orderClause, $limit, $workspaceid); 
		} else {
            track();
			return NULL;
		}
	}

/*** call method ***/
	function callMethod(&$object, $methodName = NULL, $cacheName = FALSE) {
        track('foowd::callMethod',$methodName, $cacheName);

		if (is_object($object)) {
			$methodName = setVarConstOrDefault($methodName, 'DEFAULT_METHOD', 'view');
			$method = 'method_'.$methodName;
			if (method_exists($object, $method)) { // check method exiss
				$methodPermission = setVarConstOrDefault($object->permissions[$methodName],
                                           'DEFAULT_METHOD_PERMISSION', 'Everyone');
				if ( $this->user->inGroup($methodPermission) || 
                    !$methodPermission || 
                     ( $methodPermission == 'Author' && 
                       $object->creatorid == $this->user->objectid) ) { 
                    // check user permission
					if ($cacheName === FALSE) {
						$object->{$method}($this); // call method
					} else {
						ob_start();
						$object->{$method}($this); // call method
						writeCache($cacheName, ob_get_contents());
						ob_end_flush();
					}
				} else {
                    if (function_exists('foowd_prepend')) foowd_prepend($this, $object, _('Permission denied'));
					echo 'Permission denied to access method "', 
                         $method, '" for object "', $object->title, '".';
                    if (function_exists('foowd_append')) foowd_append($this, $object);
				}
			} else {
                if (function_exists('foowd_prepend')) foowd_prepend($this, $object, _('Unknown method'));
				echo 'Unknown method "', $method, '" for object "', $object->title, '".';
                if (function_exists('foowd_append')) foowd_append($this, $object);
			}
		} else {
            if (function_exists('foowd_prepend')) foowd_prepend($this, $object, _('Object Not Found'));
			echo _('Object not found.');
            if (function_exists('foowd_append')) foowd_append($this, $object);
		}
        track();
	}
	
/*** call class method ***/
	function callClassMethod($className, $methodName = NULL) {
        track('foowd::callClassMethod',$className, $methodName);

		if (!isset($methodName)) {
			$methodName = setConstOrDefault('DEFAULT_CLASS_METHOD', 'create');
		}

		$methodPermission = setConstOrDefault(
                        strtoupper($className).'_'.strtoupper($methodName).'_PERMISSION', 
                        'Everyone');

		if ( $this->user->inGroup($methodPermission) || 
             !$methodPermission) { // check user permission

			$class = $className.'_classmethod';
			if (function_exists($class)) {
				// due to being unable to use a variable classname we have to call a 
                // passthru function for the particular class that then calls the 
                // method on the class.
				$class($this, 'class_'.$methodName);
			} else {
                $object = NULL;
                if (function_exists('foowd_prepend')) foowd_prepend($this, $object, _('Unknown method'));
				echo 'Unknown class method "', $methodName, 
                     '" in class "', $className, '".';
                if (function_exists('foowd_append')) foowd_append($this, $object);
			}
		} else {
            $object = NULL;
            if (function_exists('foowd_prepend')) foowd_prepend($this, $object, _('Permission denied'));
			echo 'Permission denied to call class method "', $methodName, 
                 '" of class "', $className, '".';
            if (function_exists('foowd_append')) foowd_append($this, $object);
		}
        track();
	}
}

/*** Callback function for init class error ***/
ini_set('unserialize_callback_func', 'loadClassCallback');

function loadClassCallback($className) {
	if (foowd::loadClass($className)) {
		return TRUE;
	} else {
		error('Could not load class "'.$className.
              '" from database, class does not exist!');
	}
}

?>
